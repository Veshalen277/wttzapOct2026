<?php
require_once __DIR__ . '/../app/bootstrap.php';   // authentication + $con
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../vendor/autoload.php'; // PHPMailer
\Portal\Auth::requireRoles([2, 7]);
\Portal\Guard::postWithCsrf('/dashboard/users/assigned_work.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get filter values
$date_filter = isset($_POST['date']) ? trim((string) $_POST['date']) : date('Y-m-d', strtotime('-1 day'));
$task_filter = isset($_POST['task']) ? trim((string) $_POST['task']) : '';

// Fetch work records for the email report
$result = fetchWorkRecords(0, 999, $date_filter, $task_filter, $con); // Adjust pagination as needed

// Generate report content
$report = "Batch Work Report\n\n";
$report .= "Date: " . $date_filter . "\n";
$report .= "Task: " . $task_filter . "\n\n";

while ($row = mysqli_fetch_assoc($result)) {
    $report .= "Employee ID: " . $row['employee_id'] . "\n";
    $report .= "Work Description: " . $row['work_desc'] . "\n";
    $report .= "Work Date: " . $row['work_date'] . "\n";
    $report .= "Assigned To: " . $row['assigned_to'] . "\n";
    $report .= "Department: " . $row['department'] . "\n";
    $report .= "Task: " . $row['task'] . "\n\n";
}

// Send email
$mail = new PHPMailer(true);
try {
    // SMTP configuration
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    // Sender
    $mail->setFrom('info@wttzap.co.za', 'WTTZAP Batch Work Report');
    
    // Add recipient (you can customize this)
    $mail->addAddress('admin@timefliesza.co.za');

    // Content
    $mail->isHTML(false);
    $mail->Subject = 'Batch Work Report for ' . date('Y-m-d', strtotime('-1 day'));
    $mail->Body    = $report;

    $mail->send();
    \Portal\Flash::set('Report emailed successfully.');
} catch (Exception $e) {
    error_log('Batch report email failed: ' . $mail->ErrorInfo);
    \Portal\Flash::set('The email could not be sent. Please try again later.', 'error');
}

// Redirect back to the assigned work page
\Portal\Http\Response::redirect("/dashboard/users/assigned_work.php?date=" . urlencode($date_filter) . "&task=" . urlencode($task_filter));
