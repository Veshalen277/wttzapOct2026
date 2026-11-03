<?php
// Include necessary files
include __DIR__ . '/../header.php';
include __DIR__ . '/../functions.php';

if ($role != 2 && $role != 5 && $role != 7) {
    header("Location: /dashboard/404.php");
    exit();
}

// Check if ID is provided via GET
if (!isset($_GET['id'])) {
    header("Location: users_list.php");
    exit;
}

// Fetch user details based on ID
$id = \Portal\Guard::id($_GET['id'] ?? null);
$result = $id ? get_user_by_id($con, $id) : null;
if (!$result) {
    http_response_code(404);
    exit('Employee not found.');
}
$allDepartments = \Portal\Departments::all($con);
$extraDepartments = array_map('strtolower', \Portal\Departments::extrasFor($con, $id));
$canGrantDepartments = ((int) $role === 7);

// Fetch distinct user scales
$scales = get_distinct_user_scales($con);

// Handle form submission
if (isset($_POST['update_emp'])) {
    if (!\Portal\Csrf::valid($_POST['_csrf'] ?? null)) {
        $_SESSION['error'] = 'Your session expired. Please try again.';
        header("Location: edit_emp.php?id=$id");
        exit;
    }
    // Only the system owner (role 7) may touch role-7 accounts or hand out roles 6/7.
    if ((int) $role !== 7 && ((int) $result['user_role'] === 7 || (int) ($_POST['user_role'] ?? -1) >= 6)) {
        http_response_code(403);
        exit('Access denied.');
    }
    $post_data = $_POST;
    $post_data['id'] = $id;

    // Hash password if provided, otherwise remove from update data
    if (!empty(trim($post_data['user_pass']))) {
        $post_data['user_pass'] = sha1($post_data['user_pass']); // SHA-1 hashing to match existing format
    } else {
        unset($post_data['user_pass']);
    }

    // Send user_role as selected
    $query = update_user_details($con, $post_data);

    if ($query && $canGrantDepartments) {
        try {
            \Portal\Departments::replaceExtras($con, $id, (array) ($_POST['extra_depts'] ?? []), (int) $portalUser->id);
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = 'Saved, but departments were not changed: ' . $e->getMessage();
            header("Location: edit_emp.php?id=$id");
            exit;
        } catch (\Throwable $e) {
            error_log('edit_emp departments: ' . $e->getMessage());
            $_SESSION['error'] = 'Saved, but the extra departments could not be updated (has the database update been applied?).';
            header("Location: edit_emp.php?id=$id");
            exit;
        }
    }

    if ($query) {
        $_SESSION['update'] = "Record updated!";
        header("Location: users_list.php");
        exit;
    } else {
        error_log('edit_emp update failed: ' . mysqli_error($con));
        $_SESSION['error'] = 'The record could not be updated. Please try again.';
        header("Location: edit_emp.php?id=$id");
        exit;
    }
}
?>

<!-- HTML Form for editing employee details -->
<div class="container mt-2">
    <form action="" method="POST">
        <?= \Portal\Csrf::field() ?>
        <div class="row m-2 p-3 register_form border border-secondary">
            <h5 class="text-center">Edit Employee</h5>
            <div class="col-md-5 m-auto">
                <div class="mb-2">
                    <label>Fullname</label>
                    <input type="text" name="user_name" placeholder="Fullname" class="form-control" required value="<?= isset($result['fullname']) ? htmlspecialchars($result['fullname']) : '' ?>" maxlength="30" minlength="3">
                </div>

                <div class="mb-2">
                    <label>Designation</label>
                    <input type="text" name="user_des" placeholder="Designation" class="form-control" required value="<?= isset($result['user_des']) ? htmlspecialchars($result['user_des']) : '' ?>" maxlength="30" minlength="3">
                </div>

                <div class="mb-2 border border-danger border-3 p-2">
                    <label><?= isset($result['fullname']) ? htmlspecialchars($result['fullname']) : '' ?> has been assigned these tasks:</label>
                    <textarea class="form-control" rows="6" name="user_res" maxlength="23000" minlength="10"><?= isset($result['user_res']) ? htmlspecialchars($result['user_res']) : '' ?></textarea>
                </div>

                <div class="mb-2">
                    <label>Job Functions</label>
                    <textarea class="form-control" rows="4" name="job_functions" placeholder="Enter job functions (one per line)"><?= isset($result['job_functions']) ? htmlspecialchars($result['job_functions']) : '' ?></textarea>
                </div>

                <div class="mb-2">
                    <label>Date Started</label>
                    <input type="date" name="date_started" class="form-control" required value="<?= isset($result['date_started']) ? htmlspecialchars($result['date_started']) : '' ?>">
                </div>

                <div class="mb-2">
                    <label>ID Number</label>
                    <input type="text" name="id_number" class="form-control" maxlength="13" minlength="13" required value="<?= isset($result['id_number']) ? htmlspecialchars($result['id_number']) : '' ?>">
                </div>

                <div class="mb-2">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" maxlength="100" minlength="5" required value="<?= isset($result['email']) ? htmlspecialchars($result['email']) : '' ?>">
                </div>
            </div>

            <div class="col-md-5">
                <div class="mb-2">
                    <label>Department</label>
                    <select class="form-control" name="user_scale" required>
                        <?php foreach ($scales as $scale): ?>
                            <option value="<?= htmlspecialchars($scale['user_scale']) ?>" <?= (isset($result['user_scale']) && $result['user_scale'] == $scale['user_scale']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($scale['user_scale']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($canGrantDepartments): ?>
                <div class="mb-2 border rounded p-2">
                    <label class="fw-bold">Additional departments</label>
                    <div class="form-text mb-1">
                        For management roles only (<?= htmlspecialchars(implode(', ', \Portal\Departments::eligibleRoles())) ?>).
                        The main department above is always kept. Ignored if the role is changed to a non-management role.
                    </div>
                    <div class="row row-cols-3 g-1">
                        <?php foreach ($allDepartments as $dept):
                              $isPrimary = isset($result['user_scale']) && strcasecmp(trim((string) $result['user_scale']), $dept) === 0; ?>
                            <div class="col">
                                <label class="form-check-label small <?= $isPrimary ? 'text-muted' : '' ?>">
                                    <input type="checkbox" class="form-check-input me-1" name="extra_depts[]"
                                           value="<?= htmlspecialchars($dept) ?>"
                                           <?= in_array(strtolower($dept), $extraDepartments, true) ? 'checked' : '' ?>
                                           <?= $isPrimary ? 'disabled' : '' ?>>
                                    <?= htmlspecialchars($dept) ?><?= $isPrimary ? ' (main)' : '' ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="mb-2">
                    <label>User ID</label>
                    <input type="text" name="user_id" class="form-control" maxlength="30" minlength="4" value="<?= isset($result['user_id']) ? htmlspecialchars($result['user_id']) : '' ?>">
                </div>

                <div class="mb-2">
                    <label>Password (leave blank to keep unchanged)</label>
                    <input type="password" name="user_pass" class="form-control" minlength="5" placeholder="Enter new password">
                </div>

                <div class="mb-2">
                    <label>User Role</label>
                    <select name="user_role" class="form-control" required>
                        <option value="0" <?= ($result['user_role'] == 0) ? 'selected' : '' ?>>Admin</option>
                        <option value="1" <?= ($result['user_role'] == 1) ? 'selected' : '' ?>>Normal User</option>
                        <option value="2" <?= ($result['user_role'] == 2) ? 'selected' : '' ?>>Super Admin</option>
                        <option value="3" <?= ($result['user_role'] == 3) ? 'selected' : '' ?>>SC Admin</option>
                        <option value="4" <?= ($result['user_role'] == 4) ? 'selected' : '' ?>>SC Super Admin</option>
                        <option value="5" <?= ($result['user_role'] == 5) ? 'selected' : '' ?>>K D</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label>Address</label>
                    <input type="text" name="address" class="form-control" maxlength="100" required value="<?= isset($result['address']) ? htmlspecialchars($result['address']) : '' ?>">
                </div>

                <div class="mb-2">
                    <label>Contact Number</label>
                    <input type="text" name="contact_number" class="form-control" maxlength="10" minlength="10" required value="<?= isset($result['contact_number']) ? htmlspecialchars($result['contact_number']) : '' ?>">
                </div>

                <div class="mb-2">
                    <label>Next of Kin</label>
                    <input type="text" name="next_of_kin" class="form-control" maxlength="50" value="<?= isset($result['next_of_kin']) ? htmlspecialchars($result['next_of_kin']) : '' ?>">
                </div>

                <div class="mb-2">
                    <label>Next of Kin Number</label>
                    <input type="text" name="next_of_kin_number" class="form-control" maxlength="10" minlength="10" value="<?= isset($result['next_of_kin_number']) ? htmlspecialchars($result['next_of_kin_number']) : '' ?>">
                </div>

                <div class="row">
                    <div class="col"><button class="btn btn-danger btn-sm text-white" name="update_emp">Update</button></div>
                    <div class="col"><a href="users_list.php" class="btn btn-primary btn-sm text-white">Back</a></div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../footer.php'; ?>