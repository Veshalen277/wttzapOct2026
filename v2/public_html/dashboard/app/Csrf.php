<?php
namespace Portal;

final class Csrf
{
    public static function token(): string
    {
        Auth::start();
        if (!isset($_SESSION['_portal_csrf'])) $_SESSION['_portal_csrf'] = bin2hex(random_bytes(32));
        return $_SESSION['_portal_csrf'];
    }

    public static function valid($token): bool
    {
        Auth::start();
        $stored = $_SESSION['_portal_csrf'] ?? null;
        return is_string($stored) && is_string($token) && $stored !== '' && hash_equals($stored, $token);
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . Html::escape(self::token()) . '">';
    }

    /**
     * Defence in depth for every state-changing request, including legacy handlers that do not
     * carry a token: browsers always send Origin (or at least Referer) on cross-site POSTs, so a
     * request whose origin host differs from this server's host is rejected. Requests with neither
     * header (non-browser clients, cron) are left to the session cookie's SameSite protection.
     */
    public static function requireSameOrigin(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) return;

        $source = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
        if ($source === '' || $source === 'null' && !isset($_SERVER['HTTP_ORIGIN'])) return;

        $sourceHost = parse_url((string) $source, PHP_URL_HOST);
        $ownHost    = parse_url('//' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST);
        if (is_string($sourceHost) && is_string($ownHost) && strcasecmp($sourceHost, $ownHost) === 0) return;

        http_response_code(403);
        exit('Cross-site request blocked.');
    }
}
