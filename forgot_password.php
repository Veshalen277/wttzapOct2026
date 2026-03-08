<?php
include 'dashboard/config.php';
include 'dashboard/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ✅ Set timezone so expires_at matches your DB timestamp
date_default_timezone_set('Africa/Johannesburg');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Look up user
    $stmt = $con->prepare("SELECT id FROM users_tbl WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry

        // Remove old tokens
        $con->query("DELETE FROM password_resets WHERE user_id = $user_id");

        // Store new token
        $insert = $con->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        $insert->bind_param("iss", $user_id, $token, $expires);
        $insert->execute();

        // Build reset link
        $reset_link = "http://wttzap.co.za/reset_password.php?token=$token";

        // Send email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'mail.wttzap.co.za';
            $mail->SMTPAuth = true;
            $mail->Username = 'info@wttzap.co.za';
            $mail->Password = '!Mv130369$';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            $mail->setFrom('info@wttzap.co.za', 'WTTZAP Support');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Link';
            $mail->Body = "
                <p>Hello,</p>
                <p>You requested to reset your password. Click below to continue:</p>
                <p><a href='$reset_link'>Reset Password</a></p>
                <p>This link will expire in 1 hour.</p>
            ";

            $mail->send();
            $success = "Reset link sent. Please check your email.";
        } catch (Exception $e) {
            $error = "Could not send email: " . $mail->ErrorInfo;
        }
    } else {
        // Optional: Hide specific error to prevent email probing
        $success = "If the email is registered, a reset link has been sent.";
    }
}
?>

<!-- Simple UI -->
<form method="POST" class="p-4">
  <h4>Forgot Password</h4>
  <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
  <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
  <div class="mb-3">
    <label>Email</label>
    <input type="email" name="email" class="form-control" required>
  </div>
  <button class="btn btn-primary">Send Reset Link</button>
  <a href="/">Go back</a>
</form>
