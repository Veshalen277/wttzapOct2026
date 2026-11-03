<?php
// Formatting shared by scheduled and administrator-triggered reports.

class DailyWorkReport {
    private $con;
    private $reportDate;
    private $reportFilePath;

    public function __construct($dbConnection, string $date) {
        $this->con = $dbConnection;
        $this->reportDate = $date;

    }

    public function generateReport() {


        $report = "📋 DAILY WORK REPORT - " . $this->reportDate . "\n\n";
        $report .= str_repeat("=", 60) . "\n\n";

        $query = "SELECT w.*, u.fullname, u.user_scale
                  FROM work_tbl w
                  LEFT JOIN users_tbl u ON w.employee_id = u.id
                  WHERE w.work_date >= ? AND w.work_date < ?";

        $stmt = mysqli_prepare($this->con, $query);
        $dayStart = $this->reportDate;
        $dayEnd = (new \DateTimeImmutable($this->reportDate, new \DateTimeZone('Africa/Johannesburg')))->modify('+1 day')->format('Y-m-d');
        mysqli_stmt_bind_param($stmt, "ss", $dayStart, $dayEnd);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result)) {
            $employeeCount = 0;
            $taskCount = 0;

            while ($row = mysqli_fetch_assoc($result)) {
                $employeeCount++;
                $report .= "👤 EMPLOYEE #{$employeeCount}\n";
                $report .= "   ID: " . htmlspecialchars($row['employee_id']) . "\n";
                $report .= "   Name: " . htmlspecialchars($row['fullname']) . "\n";
                $report .= "   Department: " . htmlspecialchars($row['department']) . "\n";
                $report .= str_repeat("-", 60) . "\n";

                $report .= "📌 TASKS:\n";
                $report .= "   " . wordwrap(htmlspecialchars($row['task']), 58, "\n   ") . "\n";
                $report .= str_repeat("-", 60) . "\n";

                $report .= "📝 WORK DESCRIPTION:\n";
                $report .= "   " . wordwrap(htmlspecialchars($row['work_desc']), 58, "\n   ") . "\n";
                $report .= "\n";
                $report .= "🕒 WORK DATE: " . htmlspecialchars($row['work_date']) . "\n";
                $report .= "👥 ASSIGNED TO: " . htmlspecialchars($row['assigned_to']) . "\n";
                $report .= str_repeat("=", 60) . "\n\n";

                $taskCount++;
            }

            $report .= "\n📊 SUMMARY:\n";
            $report .= "   Total Employees: {$employeeCount}\n";
            $report .= "   Total Tasks: {$taskCount}\n";
            $report .= str_repeat("=", 60) . "\n";
        } else {
            $report .= "⚠️ No work records found for " . $this->reportDate . "\n";
        }


        mysqli_stmt_close($stmt);
        return $report;
    }

}

class DailyReport {
    private $con;
    private $currentDate;
    
    public function __construct($dbConnection, string $date) {
        $this->con = $dbConnection;
        $this->currentDate = $date;
    }
    
    public function generateReport() {
        $report = "DAILY FINANCIAL REPORT - " . $this->currentDate . "\n\n";
        $report .= str_repeat("=", 50) . "\n\n";
        
        $query = "SELECT r.*, u.fullname, u.user_scale
                  FROM reports r
                  LEFT JOIN users_tbl u ON r.user_id = u.id
                  WHERE r.report_date = ?";
        
        $stmt = mysqli_prepare($this->con, $query);
        mysqli_stmt_bind_param($stmt, "s", $this->currentDate);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result)) {
            $grandTotals = [
                'income' => 0,
                'expenses' => 0,
                'net' => 0
            ];
            
            while ($row = mysqli_fetch_assoc($result)) {
                $report .= "USER: " . htmlspecialchars($row['fullname']) . "\n";
                $report .= "DEPARTMENT: " . htmlspecialchars($row['user_dept']) . "\n";
                $report .= str_repeat("-", 50) . "\n";
                
                // Income section
                $report .= "INCOME\n";
                $report .= sprintf("  Cash: R %10.2f\n", $row['income_cash']);
                $report .= sprintf("  Card: R %10.2f\n", $row['income_card']);
                $report .= sprintf("  Other: R %10.2f\n", $row['income_other']);
                $report .= sprintf("  TOTAL INCOME: R %10.2f\n", $row['total_income']);
                $report .= "\n";
                
                // Expenses section
                $report .= "EXPENSES\n";
                $report .= sprintf("  Cash: R %10.2f\n", $row['expense_cash']);
                $report .= sprintf("  Card: R %10.2f\n", $row['expense_card']);
                $report .= sprintf("  Airtime: R %10.2f\n", $row['airtime']);
                $report .= sprintf("  TOTAL EXPENSES: R %10.2f\n", $row['total_expenses']);
                $report .= "\n";
                
                // Net total
                $report .= "NET TOTAL: R " . sprintf("%10.2f", $row['net_total']) . "\n";
                $report .= "\n";
                
                // Notes
                $report .= "NOTES:\n" . wordwrap(htmlspecialchars($row['notes']), 50, "\n") . "\n";
                $report .= str_repeat("=", 50) . "\n\n";
                
                // Add to grand totals
                $grandTotals['income'] += $row['total_income'];
                $grandTotals['expenses'] += $row['total_expenses'];
                $grandTotals['net'] += $row['net_total'];
            }
            
            // Add grand totals
            $report .= "GRAND TOTALS\n";
            $report .= sprintf("  TOTAL INCOME: R %10.2f\n", $grandTotals['income']);
            $report .= sprintf("  TOTAL EXPENSES: R %10.2f\n", $grandTotals['expenses']);
            $report .= sprintf("  NET TOTAL: R %10.2f\n", $grandTotals['net']);
            $report .= str_repeat("=", 50) . "\n";
        } else {
            $report .= "No reports found for the report date.\n";
        }
        
        mysqli_stmt_close($stmt);
        return $report;
    }
    
}
