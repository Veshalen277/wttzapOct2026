<?php
// Save as UTF-8 without BOM.
error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('display_errors', '0');

ob_start();

$workReportFailure = static function ($message) {
    error_log('assigned_work.php: ' . $message);
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo '<div role="alert" style="padding:20px;margin:20px;background:#fff3f3;color:#8b0000;border:1px solid #b42318">'
        . 'This page could not load. Check the latest PHP error log for assigned_work.php.'
        . '</div>';
};

register_shutdown_function(static function () use ($workReportFailure) {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        $workReportFailure($error['message'] . ' in ' . $error['file'] . ':' . $error['line']);
    }
});

function safe($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Helper function to auto-detect the best matching column name from a table
 */
function findBestColumn(array $existingCols, array $candidates): ?string
{
    foreach ($candidates as $candidate) {
        if (in_array($candidate, $existingCols, true)) {
            return $candidate;
        }
    }
    return null;
}

try {
    require_once __DIR__ . '/../header.php';

    if (!isset($role)) {
        throw new RuntimeException('header.php did not set $role.');
    }
    if (!in_array((string)$role, ['2', '5', '7'], true)) {
        if (!headers_sent()) {
            http_response_code(403);
        }
        echo '<div role="alert" style="padding:20px;margin:20px;color:#8b0000">'
            . 'You do not have permission to view this page.</div>';
        exit;
    }
    if (!isset($con) || !($con instanceof mysqli)) {
        throw new RuntimeException('header.php did not provide a mysqli connection in $con.');
    }
    
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // Helper for non-mysqlnd environments
    $workFetchRows = static function ($statement) {
        if (method_exists($statement, 'get_result')) {
            $res = $statement->get_result();
            return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        }
        $metadata = $statement->result_metadata();
        if (!$metadata) {
            return [];
        }
        $values = [];
        $references = [];
        while ($field = $metadata->fetch_field()) {
            $values[$field->name] = null;
            $references[] = &$values[$field->name];
        }
        $metadata->free();
        $statement->bind_result(...$references);
        $records = [];
        while ($statement->fetch()) {
            $record = [];
            foreach ($values as $key => $value) {
                $record[$key] = $value;
            }
            $records[] = $record;
        }
        return $records;
    };

    /* =========================================================
       1. DYNAMIC COLUMN DETECTION (PREVENTS "UNKNOWN COLUMN" ERRORS)
    ========================================================= */
    
    // Fetch all real columns in work_tbl
    $workColsRes = $con->query("SHOW COLUMNS FROM work_tbl");
    $workCols = [];
    while ($row = $workColsRes->fetch_assoc()) {
        $workCols[] = strtolower($row['Field']);
    }
    $workColsRes->free();

    // Match candidate column names against real database schema
    $empCol    = findBestColumn($workCols, ['user_id', 'emp_id', 'employee_id', 'user_des_id', 'uid']) ?? 'id';
    $taskCol   = findBestColumn($workCols, ['task_name', 'task', 'title', 'work_title', 'work', 'subject', 'name']);
    $descCol   = findBestColumn($workCols, ['description', 'detail', 'details', 'work_description', 'remark', 'notes', 'msg', 'message']);
    $statusCol = findBestColumn($workCols, ['status', 'work_status', 'state', 'condition']);
    $dateCol   = findBestColumn($workCols, ['created_at', 'date', 'assigned_date', 'create_date', 'added_on', 'timestamp', 'date_created']);

    // Fetch all real columns in users_tbl
    $userColsRes = $con->query("SHOW COLUMNS FROM users_tbl");
    $userCols = [];
    while ($row = $userColsRes->fetch_assoc()) {
        $userCols[] = strtolower($row['Field']);
    }
    $userColsRes->free();

    $userNameCol = findBestColumn($userCols, ['fullname', 'full_name', 'name', 'username', 'user_name', 'emp_name', 'first_name']);

    /* =========================================================
       2. POST HANDLER (DELETE)
    ========================================================= */
    $date_filter = trim((string)($_GET['date'] ?? ''));
    $task_filter = trim((string)($_GET['task'] ?? ''));

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
        $delete_id = (int)$_POST['delete_id'];
        if ($delete_id <= 0) {
            $_SESSION['msg'] = '<div class="alert alert-danger">Invalid ID for deletion.</div>';
        } else {
            $deleteStmt = $con->prepare('DELETE FROM work_tbl WHERE id = ?');
            $deleteStmt->bind_param('i', $delete_id);
            $deleteStmt->execute();
            $deleted = $deleteStmt->affected_rows > 0;
            $deleteStmt->close();
            $_SESSION['msg'] = $deleted
                ? '<div class="alert alert-success">Assigned work deleted successfully.</div>'
                : '<div class="alert alert-warning">Record was not found or already removed.</div>';
        }
        header('Location: assigned_work.php?' . http_build_query([
            'date' => $date_filter,
            'task' => $task_filter,
        ]));
        exit;
    }

    /* =========================================================
       3. QUERY BUILDING & FILTERS
    ========================================================= */
    $records_per_page = 20;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $offset = ($page - 1) * $records_per_page;

    $whereConditions = [];
    $params = [];
    $types = '';

    // Only filter by date if date column exists
    if ($date_filter !== '' && $dateCol !== null) {
        $whereConditions[] = "DATE(w.{$dateCol}) = ?";
        $params[] = $date_filter;
        $types .= 's';
    }

    // Filter by task name or employee name if columns exist
    if ($task_filter !== '') {
        $searchConds = [];
        if ($taskCol !== null) {
            $searchConds[] = "w.{$taskCol} LIKE ?";
            $params[] = '%' . $task_filter . '%';
            $types .= 's';
        }
        if ($userNameCol !== null) {
            $searchConds[] = "u.{$userNameCol} LIKE ?";
            $params[] = '%' . $task_filter . '%';
            $types .= 's';
        }
        if (!empty($searchConds)) {
            $whereConditions[] = '(' . implode(' OR ', $searchConds) . ')';
        }
    }

    $whereSql = !empty($whereConditions) ? " WHERE " . implode(" AND ", $whereConditions) : "";

    /* =========================================================
       4. COUNT TOTAL RECORDS
    ========================================================= */
    $countSql = "
        SELECT COUNT(*) AS total 
        FROM work_tbl w
        LEFT JOIN users_tbl u ON w.{$empCol} = u.id
        {$whereSql}
    ";
    $countStmt = $con->prepare($countSql);
    if (!empty($params)) {
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $countResult = $workFetchRows($countStmt);
    $total_records = (int)($countResult[0]['total'] ?? 0);
    $countStmt->close();

    $total_pages = max(1, (int)ceil($total_records / $records_per_page));
    if ($page > $total_pages) {
        $page = $total_pages;
        $offset = ($page - 1) * $records_per_page;
    }

    /* =========================================================
       5. BUILD SELECT CLAUSE DYNAMICALLY BASED ON EXISTING COLUMNS
    ========================================================= */
    $selectFields = ["w.id"];
    $selectFields[] = "w.{$empCol} AS user_id";
    $selectFields[] = $taskCol ? "w.{$taskCol} AS task_name" : "'' AS task_name";
    $selectFields[] = $descCol ? "w.{$descCol} AS description" : "'' AS description";
    $selectFields[] = $statusCol ? "w.{$statusCol} AS status" : "'pending' AS status";
    $selectFields[] = $dateCol ? "w.{$dateCol} AS created_at" : "'' AS created_at";
    $selectFields[] = $userNameCol ? "u.{$userNameCol} AS assigned_to" : "'N/A' AS assigned_to";

    $selectClause = implode(", ", $selectFields);

    $sql = "
        SELECT {$selectClause}
        FROM work_tbl w
        LEFT JOIN users_tbl u ON w.{$empCol} = u.id
        {$whereSql}
        ORDER BY w.id DESC
        LIMIT ? OFFSET ?
    ";

    $queryStmt = $con->prepare($sql);
    $queryParams = $params;
    $queryTypes = $types . 'ii';
    $queryParams[] = $records_per_page;
    $queryParams[] = $offset;

    $queryStmt->bind_param($queryTypes, ...$queryParams);
    $queryStmt->execute();
    $assigned_works = $workFetchRows($queryStmt);
    $queryStmt->close();

    $queryString = http_build_query([
        'date' => $date_filter,
        'task' => $task_filter
    ]);

} catch (Throwable $e) {
    $workReportFailure($e->getMessage());
    exit;
}
?>

<style>
.office-page {
    background: #f3f5f7;
    padding: 22px;
    min-height: calc(100vh - 80px);
}
.office-panel {
    background: #ffffff;
    border: 1px solid #d9dde3;
    padding: 22px;
    margin-bottom: 18px;
}
.office-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 18px;
}
.office-title {
    margin: 0;
    color: #202124;
    font-size: 24px;
    font-weight: 700;
}
.office-subtitle {
    margin: 4px 0 0;
    color: #666666;
    font-size: 14px;
}
.office-filter {
    background: #f8f9fb;
    border: 1px solid #d9dde3;
    padding: 16px;
    margin-bottom: 18px;
}
.office-filter label {
    color: #444444;
    font-size: 13px;
    font-weight: 700;
}
.office-page .form-control,
.office-page .btn,
.office-page .page-link {
    border-radius: 0 !important;
    box-shadow: none !important;
}
.office-page .form-control {
    border: 1px solid #bfc5cc;
}
.btn-office-primary {
    border: 1px solid #0f6cbd;
    background: #0f6cbd;
    color: #ffffff;
    padding: 7px 14px;
    font-size: 13px;
    text-decoration: none;
}
.btn-office-primary:hover {
    background: #115ea3;
    border-color: #115ea3;
    color: #ffffff;
}
.btn-office {
    border: 1px solid #bfc5cc;
    background: #ffffff;
    color: #242424;
    padding: 7px 14px;
    font-size: 13px;
    text-decoration: none;
}
.btn-office:hover {
    background: #f3f5f7;
    color: #242424;
}
.btn-office-danger {
    border: 1px solid #b42318;
    background: #ffffff;
    color: #b42318;
    padding: 4px 8px;
    font-size: 12px;
    cursor: pointer;
}
.btn-office-danger:hover {
    background: #b42318;
    color: #ffffff;
}
.office-table {
    margin-bottom: 0;
    font-size: 13px;
}
.office-table thead th {
    background: #f1f3f5;
    border-bottom: 2px solid #c7ccd1;
    color: #333333;
    font-weight: 700;
    white-space: nowrap;
}
.office-table td {
    vertical-align: middle;
}
.badge-status {
    padding: 4px 8px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
}
.badge-pending { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
.badge-completed { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.badge-progress { background: #cce5ff; color: #004085; border: 1px solid #b8daff; }
</style>

<div class="office-page">
    <div class="office-panel">

        <div class="office-header">
            <div>
                <h4 class="office-title">Assigned Work List</h4>
                <p class="office-subtitle">View, filter, and manage tasks assigned to employees.</p>
            </div>
        </div>

        <?php if (isset($_SESSION['msg'])): ?>
            <?= $_SESSION['msg']; ?>
            <?php unset($_SESSION['msg']); ?>
        <?php endif; ?>

        <form method="get" class="office-filter">
            <div class="row g-2">
                <div class="col-md-4">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" class="form-control" id="date" name="date" value="<?= safe($date_filter); ?>">
                </div>

                <div class="col-md-5">
                    <label for="task" class="form-label">Search Task / Employee</label>
                    <input type="text" class="form-control" id="task" name="task" value="<?= safe($task_filter); ?>" placeholder="Task title or employee name">
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn-office-primary w-100">Filter Work</button>
                </div>

                <?php if ($date_filter !== '' || $task_filter !== ''): ?>
                    <div class="col-12 mt-2">
                        <a href="<?= safe(basename($_SERVER['PHP_SELF'])); ?>" class="btn-office">Clear Filters</a>
                    </div>
                <?php endif; ?>
            </div>
        </form>

        <div class="mb-2 text-muted" style="font-size: 13px;">
            Showing <?= count($assigned_works); ?> of <?= number_format($total_records); ?> task records
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-bordered office-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Assigned To</th>
                        <th>Task Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($assigned_works)): ?>
                        <?php $count = $offset + 1; ?>
                        <?php foreach ($assigned_works as $work): ?>
                            <?php
                                $status = strtolower((string)($work['status'] ?? 'pending'));
                                $statusClass = 'badge-pending';
                                if ($status === 'completed') {
                                    $statusClass = 'badge-completed';
                                } elseif ($status === 'in progress' || $status === 'in_progress') {
                                    $statusClass = 'badge-progress';
                                }
                            ?>
                            <tr>
                                <td><?= $count; ?></td>
                                <td><strong><?= safe($work['assigned_to'] ?? 'Unassigned'); ?></strong></td>
                                <td><?= safe($work['task_name'] ?? '-'); ?></td>
                                <td><?= safe($work['description'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge-status <?= $statusClass; ?>">
                                        <?= safe(ucwords($status)); ?>
                                    </span>
                                </td>
                                <td><?= safe($work['created_at'] ?? '-'); ?></td>
                                <td>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this assigned task?');">
                                        <input type="hidden" name="delete_id" value="<?= (int)$work['id']; ?>">
                                        <button type="submit" class="btn-office-danger" title="Delete Task">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php $count++; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted p-4">
                                No assigned work records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <nav class="mt-4" aria-label="Work pagination">
                <ul class="pagination justify-content-center flex-wrap">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?= max(1, $page - 1); ?>&<?= safe($queryString); ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $page === $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?= $i; ?>&<?= safe($queryString); ?>"><?= $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?= min($total_pages, $page + 1); ?>&<?= safe($queryString); ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>

    </div>
</div>

<?php
include __DIR__ . '/../footer.php';
?>