<?php
include __DIR__ . '/../header.php';
require __DIR__ . '/../vendor/autoload.php';
\Portal\Auth::requireRoles([2, 7]);   // leave administration

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to send email
function sendEmail($email, $subject, $message) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('info@wttzap.co.za', 'Mailer');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
    } catch (Exception $e) {
        error_log('leave-neg sendEmail failed: ' . $mail->ErrorInfo);
    }
}

const LEAVE_LIST = '/dashboard/users/leave-neg.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    \Portal\Guard::postWithCsrf(LEAVE_LIST);
    $action  = (string) $_POST['action'];
    $leaveId = \Portal\Guard::id($_POST['leave_id'] ?? null);

    if ($leaveId === 0 || !in_array($action, ['approve', 'disapprove', 'delete'], true)) {
        \Portal\Flash::set('Invalid request.', 'error');
        \Portal\Http\Response::redirect(LEAVE_LIST);
    }

    $notify = null; // email to send after the transaction commits
    try {
        $con->begin_transaction();

        $q = $con->prepare(
            'SELECT la.user_id, la.status, DATEDIFF(la.end_date, la.start_date) AS days, u.email, u.fullname, u.allocated_leave_days
               FROM leave_applications la
               INNER JOIN users_tbl u ON u.id = la.user_id
              WHERE la.id = ? FOR UPDATE'
        );
        $q->bind_param('i', $leaveId);
        $q->execute();
        $leave = $q->get_result()->fetch_assoc();
        $q->close();

        if (!$leave) {
            $con->rollback();
            \Portal\Flash::set('That leave application no longer exists.', 'error');
            \Portal\Http\Response::redirect(LEAVE_LIST);
        }

        $previous = (string) $leave['status'];
        $days     = max(0, (int) $leave['days']);
        $balance  = (float) $leave['allocated_leave_days'];
        $newBalance = $balance;

        if ($action === 'delete') {
            // Deleting an approved application gives the days back.
            if ($previous === 'Approved') $newBalance = $balance + $days;
            $st = $con->prepare('DELETE FROM leave_applications WHERE id = ?');
            $st->bind_param('i', $leaveId);
            $st->execute();
            $st->close();
            $message = 'Leave application deleted.';
        } else {
            $status = $action === 'approve' ? 'Approved' : 'Disapproved';
            if ($status === $previous) {
                $con->rollback();
                \Portal\Flash::set("This application is already $status.", 'error');
                \Portal\Http\Response::redirect(LEAVE_LIST);
            }
            // Balance moves only when crossing the Approved boundary (no double deduction,
            // and a Pending -> Disapproved decision never "refunds" days that were never taken).
            if ($status === 'Approved') {
                if ($balance - $days < 0) {
                    $con->rollback();
                    \Portal\Flash::set('Insufficient leave balance for this application.', 'error');
                    \Portal\Http\Response::redirect(LEAVE_LIST);
                }
                $newBalance = $balance - $days;
            } elseif ($previous === 'Approved') {
                $newBalance = $balance + $days;
            }
            $st = $con->prepare('UPDATE leave_applications SET status = ? WHERE id = ?');
            $st->bind_param('si', $status, $leaveId);
            $st->execute();
            $st->close();
            $message = 'Leave application ' . strtolower($status) . '.';
            $notify = ['email' => $leave['email'], 'name' => $leave['fullname'], 'status' => $status, 'user_id' => (int) $leave['user_id']];
        }

        if ($newBalance !== $balance) {
            $st = $con->prepare('UPDATE users_tbl SET allocated_leave_days = ? WHERE id = ?');
            $uid = (int) $leave['user_id'];
            $st->bind_param('di', $newBalance, $uid);
            $st->execute();
            $st->close();
        }
        $con->commit();
    } catch (\Throwable $e) {
        @$con->rollback();
        error_log('leave-neg action failed: ' . $e->getMessage());
        \Portal\Flash::set('The change could not be saved. Please try again.', 'error');
        \Portal\Http\Response::redirect(LEAVE_LIST);
    }

    // Emails go out only after the data change is safely committed.
    if ($notify) {
        $subject = 'Leave Application Status Update';
        $name = htmlspecialchars((string) $notify['name'], ENT_QUOTES, 'UTF-8');
        sendEmail($notify['email'], $subject, "Dear $name,<br><br>Your leave application has been " . strtolower($notify['status']) . '.');
        sendEmail('admin@timefliesza.co.za', $subject, 'The leave application for user ID ' . $notify['user_id'] . ' has been ' . strtolower($notify['status']) . '.');
    }
    \Portal\Flash::set($message);
    \Portal\Http\Response::redirect(LEAVE_LIST);
}

// ---- List with filters + pagination (all values bound) ----
$limit  = 15;
$page   = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$where  = [];
$types  = '';
$params = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filter'])) {
    if (($uid = \Portal\Guard::id($_POST['user_id'] ?? null)) > 0) { $where[] = 'la.user_id = ?'; $types .= 'i'; $params[] = $uid; }
    $lt = trim((string) ($_POST['leave_type'] ?? ''));
    if ($lt !== '') { $where[] = 'la.leave_type LIKE ?'; $types .= 's'; $params[] = '%' . $lt . '%'; }
    $stt = (string) ($_POST['status'] ?? '');
    if (in_array($stt, ['Approved', 'Disapproved', 'Pending'], true)) { $where[] = 'la.status = ?'; $types .= 's'; $params[] = $stt; }
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$stmt = $con->prepare('SELECT COUNT(*) AS total FROM leave_applications la' . $whereSql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$rowCount = (int) $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();
$totalPages = max(1, (int) ceil($rowCount / $limit));

$stmt = $con->prepare(
    'SELECT la.*, u.fullname FROM leave_applications la
       INNER JOIN users_tbl u ON la.user_id = u.id' . $whereSql . '
      ORDER BY la.id DESC LIMIT ? OFFSET ?'
);
$listTypes  = $types . 'ii';
$listParams = array_merge($params, [$limit, $offset]);
$stmt->bind_param($listTypes, ...$listParams);
$stmt->execute();
$result = $stmt->get_result();

?>

<div class="container-fluid mt-2 table-responsive bg-white">
    <h4 class="text-center mb-3">Employee Leave Applications List</h4>
    <hr>

    <!-- Filter Form -->
    <form method="post" class="mb-3">
        <div class="row">
            <div class="col-md-3 mb-2">
                <label class="form-label">User ID:</label>
                <input type="text" class="form-control" name="user_id" 
                       value="<?= isset($_POST['user_id']) ? htmlspecialchars($_POST['user_id']) : '' ?>" />
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Leave Type:</label>
                <input type="text" class="form-control" name="leave_type" 
                       value="<?= isset($_POST['leave_type']) ? htmlspecialchars($_POST['leave_type']) : '' ?>" />
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Status:</label>
                <select class="form-control" name="status">
                    <option value="">Select Status</option>
                    <option value="Approved" <?= isset($_POST['status']) && $_POST['status'] === 'Approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="Disapproved" <?= isset($_POST['status']) && $_POST['status'] === 'Disapproved' ? 'selected' : '' ?>>Disapproved</option>
                    <option value="Pending" <?= isset($_POST['status']) && $_POST['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                </select>
            </div>
            <div class="col-md-3 align-self-end mb-2">
                <button type="submit" name="filter" class="btn btn-primary w-100">Apply Filters</button>
            </div>
        </div>
    </form>

    <!-- Leave Applications Table -->
    <table class="table table-hover table-sm bg-white">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Full Name</th>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row["id"] ?></td>
                        <td><?= $row["user_id"] ?></td>
                        <td><?= htmlspecialchars($row["fullname"]) ?></td>
                        <td><?= htmlspecialchars($row["leave_type"]) ?></td>
                        <td><?= htmlspecialchars($row["start_date"]) ?></td>
                        <td><?= htmlspecialchars($row["end_date"]) ?></td>
                        <td><?= htmlspecialchars($row["reason"]) ?></td>
                        <td><?= htmlspecialchars($row["status"]) ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Are you sure?');">
                                <?= \Portal\Csrf::field() ?>
                                <input type="hidden" name="leave_id" value="<?= $row["id"] ?>">
                                <div class="d-flex flex-wrap gap-1">
                                    <button type="submit" class="btn btn-light btn-sm text-success" name="action" value="approve">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                    <button type="submit" class="btn btn-dark btn-sm text-white" name="action" value="disapprove">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                    <button type="submit" class="btn btn-danger btn-sm text-white" name="action" value="delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="9" class="text-center">No results found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<!-- FontAwesome -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<?php include __DIR__ . '/../footer.php'; ?>
