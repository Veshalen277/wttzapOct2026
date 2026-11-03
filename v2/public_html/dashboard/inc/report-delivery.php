<?php
// No sending or database access happens merely by including this file.
require_once __DIR__ . '/../app/autoload.php';

function reportSettings(): array {
    $path = getenv('PORTAL_CONFIG') ?: dirname(__DIR__) . '/config.local.php';
    $local = is_file($path) ? require $path : [];
    if (!is_array($local)) $local = [];
    $settings = [];
    foreach (['DB_HOST'=>'localhost','DB_USER'=>'','DB_PASS'=>'','DB_NAME'=>'',
              'SMTP_HOST'=>'','SMTP_USER'=>'','SMTP_PASS'=>'','SMTP_PORT'=>465,
              'SMTP_SECURE'=>'','CRON_TOKEN'=>''] as $key=>$default) {
        $env = getenv($key);
        $settings[$key] = ($env !== false && $env !== '') ? $env :
            (defined($key) ? constant($key) : ($local[$key] ?? $default));
    }
    if ($settings['SMTP_SECURE'] === '') $settings['SMTP_SECURE'] = (int)$settings['SMTP_PORT'] === 587 ? 'tls' : 'ssl';
    return $settings;
}

function reportDate(string $which = 'yesterday'): string {
    return (new DateTimeImmutable($which, new DateTimeZone('Africa/Johannesburg')))->format('Y-m-d');
}

function reportRun(string $kind, string $date, bool $send, bool $snapshot = false): array {
    $stage = 'configuration';
    $db = null;
    $cfg = [];
    try {
        if (!in_array($kind, ['work','cash'], true) || \Portal\Guard::date($date) !== $date) {
            throw new RuntimeException('Invalid report type or date.');
        }
        $cfg = reportSettings();
        foreach (['DB_HOST','DB_USER','DB_NAME','SMTP_HOST','SMTP_USER','SMTP_PASS'] as $key) {
            if ((string)$cfg[$key] === '') throw new RuntimeException($key . ' is not configured.');
        }
        if (!filter_var($cfg['SMTP_PORT'], FILTER_VALIDATE_INT, ['options'=>['min_range'=>1,'max_range'=>65535]])) {
            throw new RuntimeException('SMTP_PORT must be between 1 and 65535.');
        }
        if (!in_array($cfg['SMTP_SECURE'], ['ssl','tls'], true)) {
            throw new RuntimeException('SMTP_SECURE must be ssl or tls.');
        }
        $stage = 'dependencies';
        require_once __DIR__ . '/../vendor/autoload.php';
        require_once __DIR__ . '/report-generators.php';
        if (!class_exists('mysqli') || !class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            throw new RuntimeException('PHP mysqli or PHPMailer is unavailable.');
        }
        $stage = 'database connection';
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $db = mysqli_init();
        $db->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
        $db->real_connect((string)$cfg['DB_HOST'], (string)$cfg['DB_USER'], (string)$cfg['DB_PASS'], (string)$cfg['DB_NAME']);
        $db->set_charset('utf8mb4');
        $stage = 'report generation';
        $generator = $kind === 'work' ? new DailyWorkReport($db, $date) : new DailyReport($db, $date);
        $content = $generator->generateReport();
        if (!$send) return ['ok'=>true, 'message'=>ucfirst($kind).": report generated for $date; mail settings present. SMTP delivery has not been tested."];
        $stage = 'SMTP delivery';
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = (string)$cfg['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = (string)$cfg['SMTP_USER'];
        $mail->Password = (string)$cfg['SMTP_PASS'];
        $mail->Port = (int)$cfg['SMTP_PORT'];
        $mail->SMTPSecure = $cfg['SMTP_SECURE'];
        $mail->Timeout = 15;
        $mail->Timelimit = 20;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom((string)$cfg['SMTP_USER'], 'Workplace Reports');
        foreach (['admin@timefliesza.co.za','altaafs@wtt.co.za','accounts@wtt.co.za'] as $address) $mail->addAddress($address);
        $title = $kind === 'work' ? 'Daily Work Report' : 'Daily Financial Report';
        $mail->Subject = ($snapshot ? '[Snapshot] ' : '') . $title . ' - ' . $date;
        $mail->isHTML(false);
        $mail->Body = $content; // Full report inline, as in the batch-email feature, plus a downloadable copy.
        if ($snapshot) $mail->Body .= "\nSnapshot generated at " . (new DateTimeImmutable('now', new DateTimeZone('Africa/Johannesburg')))->format('Y-m-d H:i:s T');
        $mail->addStringAttachment($content, $kind . '_report_' . $date . '.txt', 'base64', 'text/plain');
        $mail->send();
        $message = ucfirst($kind).": SMTP server accepted the report for $date. Inbox delivery is not confirmed.";
        error_log('[daily-reports] ' . $message);
        return ['ok'=>true, 'message'=>$message];
    } catch (Throwable $e) {
        // Do not put credentials or full report contents in diagnostics.
        $detail = $e->getMessage();
        foreach (['DB_PASS','SMTP_PASS','CRON_TOKEN'] as $key) {
            if (!empty($cfg[$key])) $detail = str_replace((string)$cfg[$key], '[redacted]', $detail);
        }
        if ($stage === 'database connection') $detail = 'Cannot connect to database. Check DB settings and the server database service.';
        $message = ucfirst($kind) . ' failed at ' . $stage . ': ' . $detail;
        error_log('[daily-reports] ' . $message);
        return ['ok'=>false, 'message'=>$message];
    } finally {
        if ($db instanceof mysqli) { try { $db->close(); } catch (Throwable $ignored) {} }
    }
}

function reportCron(string $kind): void {
    ini_set('display_errors', '0');
    try {
        $cfg = reportSettings();
        // Load a file-based token before invoking the existing HTTP guard.
        if (!defined('CRON_TOKEN')) define('CRON_TOKEN', (string)$cfg['CRON_TOKEN']);
        \Portal\Guard::cron();
        $check = PHP_SAPI === 'cli' && in_array('--check', $_SERVER['argv'] ?? [], true);
        $result = reportRun($kind, reportDate(), !$check);
    } catch (Throwable $e) {
        $result = ['ok'=>false, 'message'=>'Report setup failed. Check the private configuration file.'];
    }
    if (PHP_SAPI !== 'cli') {
        header('Content-Type: text/plain; charset=UTF-8');
        http_response_code($result['ok'] ? 200 : 500);
    }
    echo $result['message'] . "\n";
    exit($result['ok'] ? 0 : 1);
}
