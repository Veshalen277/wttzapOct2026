<?php
// CSV export of work records. Restricted to role 2 (original intent) and administrators (7).
require_once __DIR__ . '/app/bootstrap.php';   // authentication + $con
require_once __DIR__ . '/functions.php';

\Portal\Auth::requireRoles([2, 7]);

$dateFilter = trim((string) ($_POST['date'] ?? $_GET['date'] ?? ''));
$taskFilter = trim((string) ($_POST['task'] ?? $_GET['task'] ?? ''));

$result = fetchWorkRecords(0, 100000, $dateFilter, $taskFilter, $con);
if (!$result) {
    http_response_code(500);
    exit('The export could not be generated. Please try again.');
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="work_records_' . date('Ymd_His') . '.csv"');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel reads accents correctly
fputcsv($out, ['Employee ID', 'Work Description', 'Work Date', 'Assigned To', 'Department', 'Task', 'Task Date', 'Time Taken']);

while ($row = mysqli_fetch_assoc($result)) {
    $timeTaken = 'N/A';
    if (!empty($row['work_date']) && !empty($row['user_task_datetime'])) {
        try {
            $interval = (new DateTime($row['work_date']))->diff(new DateTime($row['user_task_datetime']));
            $timeTaken = $interval->format('%d days, %h hours, %i minutes');
        } catch (\Throwable $e) {
            // leave as N/A for unparseable dates
        }
    }
    fputcsv($out, \Portal\Csv::row([
        $row['employee_id'] ?? '',
        $row['work_desc'] ?? '',
        $row['work_date'] ?? '',
        $row['assigned_to'] ?? '',
        $row['department'] ?? '',
        $row['task'] ?? '',
        $row['user_task_datetime'] ?? '',
        $timeTaken,
    ]));
}
fclose($out);
