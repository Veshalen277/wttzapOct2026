<?php
/**
 * Central configuration. NO SECRETS LIVE IN THIS FILE.
 *
 * Provide credentials through environment variables (preferred) or a private
 * config.local.php that returns an array (see config.local.php.example).
 * Set PORTAL_CONFIG to an absolute path to keep that file outside the web root.
 *
 * IMPORTANT: this file must start with "<?php" on byte 0 and emit no output;
 * stray whitespace here causes "headers already sent" on every page.
 */

$__portalCfgFile = getenv('PORTAL_CONFIG') ?: __DIR__ . '/config.local.php';
$__portalCfg = is_file($__portalCfgFile) ? require $__portalCfgFile : [];
if (!is_array($__portalCfg)) {
    $__portalCfg = [];
}

$__cfg = static function (string $key, $default = null) use ($__portalCfg) {
    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return $value;
    }
    return $__portalCfg[$key] ?? $default;
};

// Mail settings (guarded so repeated includes never trigger "already defined").
if (!defined('SMTP_HOST')) define('SMTP_HOST', (string) $__cfg('SMTP_HOST', ''));
if (!defined('SMTP_USER')) define('SMTP_USER', (string) $__cfg('SMTP_USER', ''));
if (!defined('SMTP_PASS')) define('SMTP_PASS', (string) $__cfg('SMTP_PASS', ''));
if (!defined('SMTP_PORT')) define('SMTP_PORT', (int) $__cfg('SMTP_PORT', 465));

// Roles that may belong to more than one department (comma-separated role numbers).
// 0 Admin, 2 Super Admin, 3 SC Admin, 4 SC Super Admin, 5 K D, 6 TFM manager, 7 System owner.
// Role 1 (Normal User) is excluded. Override in config.local.php: 'MULTI_DEPT_ROLES' => '2,6,7'
if (!defined('MULTI_DEPT_ROLES')) define('MULTI_DEPT_ROLES', (string) $__cfg('MULTI_DEPT_ROLES', '0,2,3,4,5,6,7'));

// Database connection.
try {
    $con = mysqli_connect(
        (string) $__cfg('DB_HOST', 'localhost'),
        (string) $__cfg('DB_USER', ''),
        (string) $__cfg('DB_PASS', ''),
        (string) $__cfg('DB_NAME', '')
    );
    mysqli_set_charset($con, 'utf8mb4');
    mysqli_query($con, "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
} catch (\Throwable $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, "Database connection failed. Check config.local.php / DB_* environment variables.\n");
        exit(1); // non-zero so cron jobs and release checks do not report success
    }
    if (!headers_sent()) {
        http_response_code(503);
    }
    exit('The service is temporarily unavailable. Please try again shortly.');
}

$db = $con;

unset($__portalCfgFile, $__portalCfg, $__cfg);
