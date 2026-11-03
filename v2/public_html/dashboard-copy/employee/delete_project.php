<?php
// Deletes a project. Requires a signed-in user, a valid CSRF token, and that the user is the
// project's creator or an administrator (role 7).
require_once __DIR__ . '/../app/bootstrap.php'; // authentication + $con + $portalUser

use Portal\Flash;
use Portal\Guard;
use Portal\Http\Response;

const PROJECT_LIST = '/dashboard/employee/project_dashboard.php';

Guard::postWithCsrf(PROJECT_LIST);

$projectId = Guard::id($_POST['project_id'] ?? null);
if ($projectId === 0) {
    Flash::set('Invalid project.', 'error');
    Response::redirect(PROJECT_LIST);
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
        Flash::set('You do not have permission to delete this project.', 'error');
        Response::redirect(PROJECT_LIST);
    }

    $con->begin_transaction();
    foreach ([
        'DELETE FROM project_messages WHERE project_id = ?',
        'DELETE FROM project_users_tbl WHERE project_id = ?',
        'DELETE FROM projects_tbl WHERE project_id = ?',
    ] as $sql) {
        $del = $con->prepare($sql);
        $del->bind_param('i', $projectId);
        $del->execute();
        $del->close();
    }
    $con->commit();
    Flash::set('Project deleted.');
} catch (\Throwable $e) {
    @$con->rollback();
    error_log('delete_project failed: ' . $e->getMessage());
    Flash::set('The project could not be deleted. Please try again.', 'error');
}

Response::redirect(PROJECT_LIST);
