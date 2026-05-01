<?php
require_once '../config/db.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

try {
    switch($action) {
        case 'balance_sheet':
            // Assets
            $stmt = $pdo->query("SELECT a.*, 
                                COALESCE(SUM(CASE WHEN t.type = 'Credit' THEN t.amount ELSE 0 END), 0) -
                                COALESCE(SUM(CASE WHEN t.type = 'Debit' THEN t.amount ELSE 0 END), 0) as net_balance
                                FROM accounts a
                                LEFT JOIN transactions t ON a.account_id = t.account_id
                                WHERE a.type = 'Asset'
                                GROUP BY a.account_id");
            $assets = $stmt->fetchAll();
            
            // Liabilities
            $stmt = $pdo->query("SELECT a.*, 
                                COALESCE(SUM(CASE WHEN t.type = 'Credit' THEN t.amount ELSE 0 END), 0) -
                                COALESCE(SUM(CASE WHEN t.type = 'Debit' THEN t.amount ELSE 0 END), 0) as net_balance
                                FROM accounts a
                                LEFT JOIN transactions t ON a.account_id = t.account_id
                                WHERE a.type = 'Liability'
                                GROUP BY a.account_id");
            $liabilities = $stmt->fetchAll();
            
            $total_assets = array_sum(array_column($assets, 'net_balance'));
            $total_liabilities = array_sum(array_column($liabilities, 'net_balance'));
            
            echo json_encode([
                'success' => true,
                'assets' => $assets,
                'liabilities' => $liabilities,
                'total_assets' => $total_assets,
                'total_liabilities' => $total_liabilities
            ]);
            break;

        case 'income_statement':
            $year = $_POST['year'] ?? date('Y');
            
            // Revenue
            $stmt = $pdo->prepare("SELECT a.*, 
                                  COALESCE(SUM(t.amount), 0) as total_amount
                                  FROM accounts a
                                  LEFT JOIN transactions t ON a.account_id = t.account_id 
                                  AND t.type = 'Credit' AND YEAR(t.trans_date) = ?
                                  WHERE a.type = 'Revenue'
                                  GROUP BY a.account_id");
            $stmt->execute([$year]);
            $revenue = $stmt->fetchAll();
            
            // Expenses
            $stmt = $pdo->prepare("SELECT a.*, 
                                  COALESCE(SUM(t.amount), 0) as total_amount
                                  FROM accounts a
                                  LEFT JOIN transactions t ON a.account_id = t.account_id 
                                  AND t.type = 'Debit' AND YEAR(t.trans_date) = ?
                                  WHERE a.type = 'Expense'
                                  GROUP BY a.account_id");
            $stmt->execute([$year]);
            $expenses = $stmt->fetchAll();
            
            $total_revenue = array_sum(array_column($revenue, 'total_amount'));
            $total_expenses = array_sum(array_column($expenses, 'total_amount'));
            $net_income = $total_revenue - $total_expenses;
            
            echo json_encode([
                'success' => true,
                'revenue' => $revenue,
                'expenses' => $expenses,
                'total_revenue' => $total_revenue,
                'total_expenses' => $total_expenses,
                'net_income' => $net_income
            ]);
            break;

        case 'cash_flow':
            $year = $_POST['year'] ?? date('Y');
            
            $stmt = $pdo->prepare("SELECT 
                                  MONTH(trans_date) as month,
                                  SUM(CASE WHEN type = 'Debit' THEN amount ELSE 0 END) as debits,
                                  SUM(CASE WHEN type = 'Credit' THEN amount ELSE 0 END) as credits
                                  FROM transactions
                                  WHERE YEAR(trans_date) = ?
                                  GROUP BY MONTH(trans_date)
                                  ORDER BY MONTH(trans_date)");
            $stmt->execute([$year]);
            $cashFlow = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'data' => $cashFlow
            ]);
            break;

        case 'dashboard_charts':
            // Budget per department
            $stmt = $pdo->query("SELECT d.name as department_name, 
                                COALESCE(SUM(b.allocated_amount), 0) as allocated_amount
                                FROM departments d
                                LEFT JOIN budgets b ON d.department_id = b.department_id AND b.year = YEAR(CURDATE())
                                GROUP BY d.department_id");
            $budgets = $stmt->fetchAll();
            
            // Monthly revenue vs expenses for current year
            $stmt = $pdo->query("SELECT 
                                MONTH(trans_date) as month,
                                MONTHNAME(trans_date) as month_name,
                                SUM(CASE WHEN type = 'Credit' THEN amount ELSE 0 END) as revenue,
                                SUM(CASE WHEN type = 'Debit' THEN amount ELSE 0 END) as expenses
                                FROM transactions
                                WHERE YEAR(trans_date) = YEAR(CURDATE())
                                GROUP BY MONTH(trans_date), MONTHNAME(trans_date)
                                ORDER BY MONTH(trans_date)");
            $monthly = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'budgets' => $budgets,
                    'monthly' => $monthly
                ]
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
