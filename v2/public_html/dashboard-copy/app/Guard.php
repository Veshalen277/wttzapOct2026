<?php
namespace Portal;

/**
 * Small request guards shared by action endpoints (POST handlers, exports, cron scripts).
 * Keeps the same checks from being re-implemented (and forgotten) in every file.
 */
final class Guard
{
    /**
     * Require a POST request carrying a valid CSRF token. Otherwise flash an error and
     * redirect to $redirectTo (a local absolute path such as "/dashboard/employee/x.php").
     */
    public static function postWithCsrf(string $redirectTo): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Allow: POST');
            http_response_code(405);
            exit('Method not allowed.');
        }
        if (!Csrf::valid($_POST['_csrf'] ?? null)) {
            Flash::set('Your session expired. Please try again.', 'error');
            Http\Response::redirect($redirectTo);
        }
    }

    /** Positive integer from untrusted input, or 0 when it is not one. */
    public static function id($value): int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT);
        return ($id !== false && $id > 0) ? $id : 0;
    }

    /** Valid Y-m-d date string, or null. */
    public static function date($value): ?string
    {
        if (!is_string($value) || $value === '') return null;
        $d = \DateTime::createFromFormat('!Y-m-d', $value);
        return ($d && $d->format('Y-m-d') === $value) ? $value : null;
    }

    /**
     * Protect scheduled-job scripts. Allowed from the command line; over HTTP only when the
     * request presents the CRON_TOKEN (env var or config.local.php). Unset token => HTTP denied.
     */
    public static function cron(): void
    {
        if (PHP_SAPI === 'cli') return;
        $expected = getenv('CRON_TOKEN');
        if (($expected === false || $expected === '') && defined('CRON_TOKEN')) $expected = CRON_TOKEN;
        $given = $_SERVER['HTTP_X_CRON_TOKEN'] ?? ($_GET['token'] ?? '');
        if (is_string($expected) && $expected !== '' && is_string($given) && hash_equals($expected, $given)) return;
        http_response_code(403);
        exit('Forbidden.');
    }
}
