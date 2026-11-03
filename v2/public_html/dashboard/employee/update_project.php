<?php
// Updates a project and its associated users. Requires sign-in, CSRF, and that the user is
// the project's creator or an administrator (role 7). No page output happens before redirects.
require_once __DIR__ . '/../app/bootstrap.php'; // authentication + $con + $portalUser

use Portal\Flash;
use Portal\Guard;
use Portal\Http\Response;

const PROJECT_LIST = '/dashboard/employee/project_dashboard.php';

Guard::postWithCsrf(PROJECT_LIST);

$projectId = Guard::id($_POST['project_id'] ?? null);
$back = '/dashboard/employee/edit_project.php?project_id=' . $projectId;
if ($projectId === 0) {
    Flash::set('Invalid project.', 'error');
    Response::redirect(PROJECT_LIST);
}

// Validate input.
$name        = trim((string) ($_POST['project_name'] ?? ''));
$description = trim((string) ($_POST['project_description'] ?? ''));
$startDate   = Guard::date($_POST['start_date'] ?? null);
$endDate     = Guard::date($_POST['end_date'] ?? null);
$status      = (string) ($_POST['status'] ?? '');
$priority    = (string) ($_POST['priority'] ?? '');
$budgetRaw   = trim((string) ($_POST['budget'] ?? '0'));

$errors = [];
if ($name === '' || mb_strlen($name) > 255)                         $errors[] = 'Enter a project name (up to 255 characters).';
if (mb_strlen($description) > 5000)                                 $errors[] = 'The description is too long.';
if (!in_array($status, ['Pending', 'Active', 'Completed', 'On Hold', 'Cancelled'], true)) $errors[] = 'Choose a valid status.';
if (!in_array($priority, ['Low', 'Medium', 'High'], true))          $errors[] = 'Choose a valid priority.';
if (!is_numeric($budgetRaw) || (float) $budgetRaw < 0)              $errors[] = 'Budget must be zero or more.';
if ($startDate && $endDate && $endDate < $startDate)                $errors[] = 'The end date cannot be before the start date.';
if ($errors) {
    Flash::set(implode(' ', $errors), 'error');
    Response::redirect($back);
}
$budget = round((float) $budgetRaw, 2);

$userIds = [];
foreach ((array) ($_POST['users'] ?? []) as $candidate) {
    $uid = Guard::id($candidate);
    if ($uid) $userIds[$uid] = $uid;
}

try {
    $stmt = $con->prepare('SELECT created_by FROM projects_tbl WHERE project_id = ?');
    $stmt->bind_param('i', $projectId);
    $stmt->execute();
    $project = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$project) {
        Flash::set('Project not found.', 'error');
        Response::redirect(PROJECT_LIST);
    }
    if ((int) $project['created_by'] !== $portalUser->id && !$portalUser->hasRole([7])) {
        http_response_code(403);
        Flash::set('You do not have permission to edit this project.', 'error');
        Response::redirect(PROJECT_LIST);
    }

    $con->begin_transaction();

    $upd = $con->prepare(
        'UPDATE projects_tbl
            SET project_name = ?, project_description = ?, start_date = ?, end_date = ?,
                status = ?, priority = ?, budget = ?
          WHERE project_id = ?'
    );
    $upd->bind_param('ssssssdi', $name, $description, $startDate, $endDate, $status, $priority, $budget, $projectId);
    $upd->execute();
    $upd->close();

    $clear = $con->prepare('DELETE FROM project_users_tbl WHERE project_id = ?');
    $clear->bind_param('i', $projectId);
    $clear->execute();
    $clear->close();

    if ($userIds) {
        $ins = $con->prepare(
            'INSERT INTO project_users_tbl (project_id, user_id)
             SELECT ?, id FROM users_tbl WHERE id = ?'   // silently skips ids that do not exist
        );
        foreach ($userIds as $uid) {
            $ins->bind_param('ii', $projectId, $uid);
            $ins->execute();
        }
        $ins->close();
    }

    $con->commit();
    Flash::set('Project updated.');
} catch (\Throwable $e) {
    @$con->rollback();
    error_log('update_project failed: ' . $e->getMessage());
    Flash::set('The project could not be saved. Please try again.', 'error');
    Response::redirect($back);
}

Response::redirect(PROJECT_LIST);
