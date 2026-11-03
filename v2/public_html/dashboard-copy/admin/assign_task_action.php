<?php
// Assigns an existing advanced task. Administrators only (roles 7 and 8).
// The assigning user always comes from the session, never from the form.
require_once __DIR__ . '/../app/bootstrap.php';

use Portal\Flash;
use Portal\Guard;
use Portal\Http\Response;

const BACK = '/dashboard/admin/admin_tasks.php';

\Portal\Auth::requireRoles([7, 8]);
Guard::postWithCsrf(BACK);

$taskId     = Guard::id($_POST['task_id'] ?? null);
$assignedTo = Guard::id($_POST['assigned_to'] ?? null);
$dueDate    = Guard::date($_POST['due_date'] ?? null);

if ($taskId === 0 || $assignedTo === 0 || $dueDate === null) {
    Flash::set('Choose a task, an assignee and a valid due date.', 'error');
    Response::redirect(BACK);
}

try {
    $stmt = $con->prepare(
        "UPDATE advanced_tasks_tbl
            SET assigned_to = ?, assigned_by = ?, due_date = ?, status = 'assigned'
          WHERE id = ?
            AND EXISTS (SELECT 1 FROM users_tbl WHERE id = ?)"
    );
    $assignedBy = $portalUser->id;
    $stmt->bind_param('iisii', $assignedTo, $assignedBy, $dueDate, $taskId, $assignedTo);
    $stmt->execute();
    Flash::set($stmt->affected_rows > 0 ? 'Task successfully assigned.' : 'No task was changed (check the task and user).', $stmt->affected_rows > 0 ? 'success' : 'error');
    $stmt->close();
} catch (\Throwable $e) {
    error_log('assign_task_action failed: ' . $e->getMessage());
    Flash::set('The task could not be assigned. Please try again.', 'error');
}

Response::redirect(BACK);
