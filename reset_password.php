<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'dashboard/config.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $password = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Verify token and expiration
        $stmt = $con->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $reset = $stmt->get_result()->fetch_assoc();

        if ($reset) {
            $user_id = $reset['user_id'];

            // ✅ Use SHA1 (to match existing login system)
            $hashed = sha1($password);

            $update = $con->prepare("UPDATE users_tbl SET user_pass = ? WHERE id = ?");
            $update->bind_param("si", $hashed, $user_id);
            $update->execute();

            // Clean up token
            $con->query("DELETE FROM password_resets WHERE user_id = $user_id");

            $success = "Password updated successfully. <a href='login.php'>Log in</a>";
        } else {
            $error = "Token is invalid or expired.";
        }
    }
}
?>

<!-- Reset Password Form -->
<form method="POST" class="p-4">
  <h4>Reset Password</h4>
  <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
  <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

  <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

  <div class="mb-3">
    <label>New Password</label>
    <input type="password" name="new_password" class="form-control" required>
  </div>

  <div class="mb-3">
    <label>Confirm Password</label>
    <input type="password" name="confirm_password" class="form-control" required>
  </div>

  <button type="submit" class="btn btn-success">Reset</button>
</form>
