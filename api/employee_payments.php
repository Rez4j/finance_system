<?php
require_once '../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$input = json_decode(file_get_contents('php://input'), true);

header('Content-Type: application/json');

switch($method) {
    case 'GET':
        // Joining with employees table to retrieve names
        $sql = "SELECT p.*, CONCAT(e.last_name, ', ', e.first_name) as employee_name 
                FROM employee_payments p 
                JOIN employees e ON p.employee_id = e.employee_id 
                ORDER BY p.pay_date DESC";
        $stmt = $pdo->query($sql);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        break;

    case 'POST':
        $sql = "INSERT INTO employee_payments (employee_id, pay_date, gross_amount, deductions, net_amount) 
                VALUES (?, ?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([
            $input['employee_id'],
            $input['pay_date'],
            $input['gross_amount'],
            $input['deductions'],
            $input['net_amount']
        ]);
        echo json_encode(['success' => true, 'message' => 'Salary processed successfully']);
        break;

    case 'PUT':
        $sql = "UPDATE employee_payments SET employee_id = ?, pay_date = ?, gross_amount = ?, 
                deductions = ?, net_amount = ? WHERE pay_id = ?";
        $pdo->prepare($sql)->execute([
            $input['employee_id'],
            $input['pay_date'],
            $input['gross_amount'],
            $input['deductions'],
            $input['net_amount'],
            $id
        ]);
        echo json_encode(['success' => true, 'message' => 'Payment record updated']);
        break;

    case 'DELETE':
        $pdo->prepare("DELETE FROM employee_payments WHERE pay_id = ?")->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Record removed']);
        break;
}
