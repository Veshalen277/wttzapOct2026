<?php
// Assigns a free-text task to a user. Administrators only (roles 7 and 8, matching the admin task pages).
require_once __DIR__ . '/../app/bootstrap.php';

use Portal\Flash;
use Portal\Guard;
use Portal\Http\Response;

const BACK = '/dashboard/admin/admin_tasks.php';

\Portal\Auth::requireRoles([7, 8]);
Guard::postWithCsrf(BACK);

$userId = Guard::id($_POST['user_id'] ?? null);
$task   = trim((string) ($_POST['task_description'] ?? ''));

if ($userId === 0 || $task === '' || mb_strlen($task) > 2000) {
    Flash::set('Choose a user and enter a task (up to 2000 characters).', 'error');
    Response::redirect(BACK);
}

try {
    $stmt = $con->prepare(
        'INSERT INTO adv_user_tasks_tbl (user_id, task_description)
         SELECT id, ? FROM users_tbl WHERE id = ?'
    );
    $stmt->bind_param('si', $task, $userId);
    $stmt->execute();
    if ($stmt->affected_rows === 1) {
        Flash::set('Task assigned successfully.');
    } else {
        Flash::set('That user does not exist.', 'error');
    }
    $stmt->close();
} catch (\Throwable $e) {
    error_log('save_user_task failed: ' . $e->getMessage());
    Flash::set('The task could not be saved. Please try again.', 'error');
}

Response::redirect(BACK);
