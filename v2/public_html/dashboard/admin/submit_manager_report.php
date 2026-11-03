<?php
// Submits a manager report. Administrators/managers only (roles 7 and 8).
// The author is the signed-in user; attachments are validated by real content type and
// stored under a random name.
require_once __DIR__ . '/../app/bootstrap.php';

use Portal\Flash;
use Portal\Guard;
use Portal\Http\Response;

const BACK = '/dashboard/admin/admin_profile.php';

\Portal\Auth::requireRoles([7, 8]);
Guard::postWithCsrf(BACK);

$title      = trim((string) ($_POST['report_title'] ?? ''));
$body       = trim((string) ($_POST['report_body'] ?? ''));
$dept       = trim((string) ($_POST['department'] ?? ''));
$visibility = filter_var($_POST['visibility_level'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 9]]);

if ($title === '' || mb_strlen($title) > 255 || $body === '' || mb_strlen($body) > 20000 || mb_strlen($dept) > 100 || $visibility === false) {
    Flash::set('Please complete the title, body and visibility (title up to 255 characters).', 'error');
    Response::redirect(BACK);
}

$attachmentPath = '';
$savedFile = null;
$upload = $_FILES['attachment'] ?? null;
if ($upload && ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    $allowed = [
        'application/pdf' => 'pdf',
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png',
        'text/plain'      => 'txt',
    ];
    $ok = ($upload['error'] === UPLOAD_ERR_OK) && is_uploaded_file($upload['tmp_name']) && $upload['size'] > 0 && $upload['size'] <= 5 * 1024 * 1024;
    $mime = $ok ? (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']) : null;
    if (!$ok || !isset($allowed[$mime])) {
        Flash::set('The attachment must be a PDF, JPG, PNG or text file under 5 MB.', 'error');
        Response::redirect(BACK);
    }
    $dir = dirname(__DIR__) . '/uploads/reports';
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        error_log('submit_manager_report: cannot create ' . $dir);
        Flash::set('The attachment could not be stored.', 'error');
        Response::redirect(BACK);
    }
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($upload['tmp_name'], $dir . '/' . $name)) {
        Flash::set('The attachment could not be stored.', 'error');
        Response::redirect(BACK);
    }
    $savedFile = $dir . '/' . $name;
    $attachmentPath = 'uploads/reports/' . $name;
}

try {
    $managerId = $portalUser->id;
    $stmt = $con->prepare(
        'INSERT INTO manager_report_tbl (manager_id, report_title, report_body, department, attachment_path, visibility_level)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('issssi', $managerId, $title, $body, $dept, $attachmentPath, $visibility);
    $stmt->execute();
    $stmt->close();
    Flash::set('Report submitted.');
} catch (\Throwable $e) {
    if ($savedFile && is_file($savedFile)) @unlink($savedFile);
    error_log('submit_manager_report failed: ' . $e->getMessage());
    Flash::set('The report could not be saved. Please try again.', 'error');
}

Response::redirect(BACK);
