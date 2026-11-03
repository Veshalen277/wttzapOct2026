<?php
// Monthly archive of daily reports. Run from cron (CLI), e.g.:  php /path/to/dashboard/auto_archive.php
// Over HTTP it requires the CRON_TOKEN header; it never runs from a normal page visit.
require_once __DIR__ . '/app/autoload.php';
\Portal\Guard::cron();
chdir(__DIR__);
require_once __DIR__ . '/config.php';

$logFile = __DIR__ . '/reports/archive_reports.log';

function archiveLog(string $message): void
{
    global $logFile;
    @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n", FILE_APPEND | LOCK_EX);
}

/** Moves rows up to the end of last month from $main to $archive, atomically. Table names are fixed constants below. */
function archiveReports(mysqli $con, string $main, string $archive): void
{
    $cutoff = date('Y-m-t', strtotime('last day of previous month'));
    try {
        $con->begin_transaction();
        $ins = $con->prepare("INSERT INTO `$archive` SELECT *, NOW() AS archived_at FROM `$main` WHERE report_date <= ?");
        $ins->bind_param('s', $cutoff);
        $ins->execute();
        $copied = $ins->affected_rows;
        $ins->close();

        $del = $con->prepare("DELETE FROM `$main` WHERE report_date <= ?");
        $del->bind_param('s', $cutoff);
        $del->execute();
        $removed = $del->affected_rows;
        $del->close();

        if ($copied !== $removed) {
            throw new RuntimeException("copied $copied but removed $removed rows");
        }
        $con->commit();
        archiveLog("Archived $copied rows from $main (up to $cutoff).");
    } catch (Throwable $e) {
        @$con->rollback();
        archiveLog("FAILED archiving $main: " . $e->getMessage());
        error_log("auto_archive $main failed: " . $e->getMessage());
    }
}

archiveReports($con, 'smoking_coffee_daily_reports', 'smoking_coffee_daily_reports_archive');
archiveReports($con, 'laletsa_daily_reports', 'laletsa_daily_reports_archive');
$con->close();
