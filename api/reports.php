<?php
require_once '../config/db.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? 'income_statement';
$year = $_GET['year'] ?? date('Y');

// 1. Calculate General Summary (Revenue vs Expenses)
$revenue_sql = "SELECT SUM(amount) FROM transactions WHERE type = 'Credit' AND YEAR(trans_date) = ?";
$expense_sql = "SELECT SUM(amount) FROM transactions WHERE type = 'Debit' AND YEAR(trans_date) = ?";

$stmt = $pdo->prepare($revenue_sql);
$stmt->execute([$year]);
$rev = $stmt->fetchColumn() ?? 0;

$stmt = $pdo->prepare($expense_sql);
$stmt->execute([$year]);
$exp = $stmt->fetchColumn() ?? 0;

$summary = [
    'revenue' => $rev,
    'expenses' => $exp,
    'net' => $rev - $exp
];

// 2. Fetch Data based on Report Type
$tableData = [];
if ($type === 'income_statement') {
    // Group by Account Category
    $sql = "SELECT a.type as label, SUM(t.amount) as amount 
            FROM transactions t 
            JOIN accounts a ON t.account_id = a.account_id 
            WHERE YEAR(t.trans_date) = ? 
            GROUP BY a.type";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$year]);
    $tableData = $stmt->fetchAll();
} elseif ($type === 'budget_utilization') {
    // Budget vs Actual Join
    $sql = "SELECT d.name as dept_name, b.allocated_amount as allocated, 
            (SELECT IFNULL(SUM(t.amount), 0) FROM transactions t 
             JOIN accounts a ON t.account_id = a.account_id 
             WHERE a.type = 'Expense' AND YEAR(t.trans_date) = b.year) as spent
            FROM budgets b
            JOIN departments d ON b.department_id = d.department_id
            WHERE b.year = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$year]);
    $tableData = $stmt->fetchAll();
}

// 3. Mock Chart Data (Monthly Breakdown)
// In a full implementation, you would loop 1-12 and query each month[cite: 1]
$chartData = [
    'months' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    'revenue' => [50000, 30000, 45000, 20000, 35000, 40000],
    'expenses' => [15000, 10000, 12000, 18000, 14000, 15000]
];

echo json_encode([
    'success' => true,
    'summary' => $summary,
    'chartData' => $chartData,
    'tableData' => $tableData
]);