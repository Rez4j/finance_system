<?php
if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$year = $_GET['year'] ?? date('Y');

// Summary data
$summary = [];

$stmt = $pdo->prepare("SELECT COALESCE(SUM(allocated_amount), 0) as total FROM budgets WHERE year = ?");
$stmt->execute([$year]);
$summary['total_budget'] = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'Debit' AND YEAR(trans_date) = ?");
$stmt->execute([$year]);
$summary['total_expenses'] = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'Credit' AND YEAR(trans_date) = ?");
$stmt->execute([$year]);
$summary['total_revenue'] = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM student_payments WHERE YEAR(pay_date) = ?");
$stmt->execute([$year]);
$summary['total_student_payments'] = $stmt->fetch()['total'];

// Budget by department
$stmt = $pdo->prepare("SELECT d.name, COALESCE(SUM(b.allocated_amount), 0) as allocated 
                       FROM departments d 
                       LEFT JOIN budgets b ON d.department_id = b.department_id AND b.year = ? 
                       GROUP BY d.department_id ORDER BY d.name");
$stmt->execute([$year]);
$budget_data = $stmt->fetchAll();

// Monthly data
$stmt = $pdo->prepare("SELECT MONTH(trans_date) as month,
                       SUM(CASE WHEN type = 'Credit' THEN amount ELSE 0 END) as revenue,
                       SUM(CASE WHEN type = 'Debit' THEN amount ELSE 0 END) as expenses
                       FROM transactions WHERE YEAR(trans_date) = ?
                       GROUP BY MONTH(trans_date) ORDER BY MONTH(trans_date)");
$stmt->execute([$year]);
$monthly_data = $stmt->fetchAll();

// Recent transactions
$stmt = $pdo->prepare("SELECT t.*, a.name as account_name 
                       FROM transactions t JOIN accounts a ON t.account_id = a.account_id 
                       WHERE YEAR(t.trans_date) = ? 
                       ORDER BY t.trans_date DESC LIMIT 10");
$stmt->execute([$year]);
$recent = $stmt->fetchAll();

echo json_encode([
    'success' => true,
    'year' => $year,
    'summary' => $summary,
    'budget_by_department' => $budget_data,
    'monthly_data' => $monthly_data,
    'recent_transactions' => $recent
]);
?>