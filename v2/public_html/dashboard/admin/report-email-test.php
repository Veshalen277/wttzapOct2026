<?php
require_once __DIR__ . '/../app/autoload.php';
\Portal\Auth::requireRoles([7]);
require_once __DIR__ . '/../inc/report-delivery.php';
header('Cache-Control: no-store');
$results = $_SESSION['report_test_results'] ?? [];
unset($_SESSION['report_test_results']);
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!\Portal\Csrf::valid($_POST['_csrf'] ?? null)) {
        http_response_code(403); exit('Session expired. Reload this page.');
    }
    $nonce = $_POST['request_key'] ?? '';
    if (!is_string($nonce) || !hash_equals($_SESSION['report_test_nonce'] ?? '', $nonce) || $nonce === '') {
        http_response_code(409); exit('This request was already used. Reload the test page.');
    }
    unset($_SESSION['report_test_nonce']);
    $date = \Portal\Guard::date($_POST['date'] ?? null);
    $action = $_POST['action'] ?? '';
    if (!$date || $date > reportDate('today') || !in_array($action, ['check','send'], true)) {
        $results = [['ok'=>false, 'message'=>'Choose a valid report date up to today and a valid action.']];
    } else {
        $results = [];
        foreach (['work','cash'] as $kind) $results[] = reportRun($kind, $date, $action === 'send', true);
    }
    $_SESSION['report_test_results'] = $results;
    header('Location: /dashboard/admin/report-email-test.php', true, 303); exit;
}
$_SESSION['report_test_nonce'] = bin2hex(random_bytes(32));
function reportEscape($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Send report snapshot</title>
<style>body{font:16px system-ui;background:#f3f5f8;color:#172334;margin:0}main{max-width:800px;margin:40px auto;background:white;padding:32px;border-radius:12px}button,input{font:inherit;padding:12px;margin:8px 8px 8px 0}button{cursor:pointer}li{padding:12px;margin:8px 0}.ok{background:#e8f6ed}.error{background:#ffe8e8}code{overflow-wrap:anywhere}</style>
<main><a href="/dashboard/admin/admin-stats-report/">Back to admin</a><h1>Send report snapshot</h1>
<p>Send the complete work and cash reports for a selected date, including every matching record and the financial totals. Both full reports appear in the email bodies and as text attachments. Today includes only records saved so far. Scheduled reports use yesterday in South African time.</p>
<p><strong>Send recipients:</strong> admin@timefliesza.co.za, altaafs@wtt.co.za, accounts@wtt.co.za. Sending creates two real emails labelled “Snapshot”.</p>
<?php if ($results): ?><h2>Last result</h2><ul><?php foreach ($results as $result): ?><li class="<?= $result['ok'] ? 'ok' : 'error' ?>"><?= reportEscape($result['message']) ?></li><?php endforeach; ?></ul><?php endif; ?>
<form method="post">
<?= \Portal\Csrf::field() ?>
<input type="hidden" name="request_key" value="<?= reportEscape($_SESSION['report_test_nonce']) ?>">
<label>Report date <input type="date" name="date" required value="<?= reportEscape(reportDate('today')) ?>" max="<?= reportEscape(reportDate('today')) ?>"></label><br>
<button name="action" value="check">Check without sending</button>
<button name="action" value="send">Send full reports to stakeholders</button>
</form>
<p>The check reads the database and generates both reports. Only sending tests SMTP. If one report fails, the other is still attempted. Repeating a send can create duplicate emails.</p>
<h2>Automatic midnight delivery</h2><p>This page does not install or verify the hosting scheduler. The host must run both scripts daily at 00:00 Africa/Johannesburg:</p>
<pre><code>php /absolute/path/dashboard/send_daily_report.php
php /absolute/path/dashboard/send_income_report.php</code></pre>
<p>Add <code>--check</code> to either command to diagnose without sending. A failed job returns exit code 1. A URL-based scheduler still requires the configured cron token.</p></main></html>
