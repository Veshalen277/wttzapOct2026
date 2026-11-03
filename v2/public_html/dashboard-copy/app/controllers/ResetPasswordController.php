<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/helpers.php';

final class ResetPasswordController
{
    public static function show(array $ENV): void
    {
        $token = (string)($_GET['token'] ?? '');
        $error = (string)($_GET['err'] ?? '');

        // If no token in the URL, there is nothing to show — send to forgot-password.
        if ($token === '' && $error === '') {
            redirect('forgot-password&err=' . urlencode('No reset token found. Please request a new reset link.'));
        }

        require __DIR__ . '/../views/auth/password_reset.php';
    }

    public static function submit(array $ENV): void
    {
        $pdo = db($ENV);

        $token = (string)($_POST['token'] ?? '');
        $pass  = (string)($_POST['password'] ?? '');
        $pass2 = (string)($_POST['password_confirm'] ?? ($_POST['password2'] ?? ''));

        if ($token === '' || $pass === '' || $pass2 === '') {
            redirect('reset-password&token=' . urlencode($token) . '&err=' . urlencode('All fields are required'));
        }

        if ($pass !== $pass2) {
            redirect('reset-password&token=' . urlencode($token) . '&err=' . urlencode('Passwords do not match'));
        }

        if (strlen($pass) < 8) {
            redirect('reset-password&token=' . urlencode($token) . '&err=' . urlencode('Password must be at least 8 characters'));
        }

        $tokenHash = hash('sha256', $token);

        $stmt = $pdo->prepare(
            'SELECT id, user_id
             FROM password_resets
             WHERE token_hash = ?
               AND expires_at > NOW()
               AND used_at IS NULL
             ORDER BY id DESC
             LIMIT 1'
        );
        $stmt->execute([$tokenHash]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            // Do NOT pass the token back — it's invalid/expired so there's nothing to retry.
            // Send the user to the forgot-password page so they can request a fresh link.
            redirect('forgot-password&err=' . urlencode('Your reset link has expired or is invalid. Please request a new one.'));
        }

        $newHash = password_hash($pass, PASSWORD_DEFAULT);

        $pdo->beginTransaction();

        try {
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ? LIMIT 1')
                ->execute([$newHash, (int)$row['user_id']]);

            $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ? LIMIT 1')
                ->execute([(int)$row['id']]);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            redirect('reset-password&token=' . urlencode($token) . '&err=' . urlencode('Could not reset password'));
        }

        redirect('login&err=' . urlencode('Password updated. Please log in'));
    }
}