<?php
require_once '../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$input = json_decode(file_get_contents('php://input'), true);

header('Content-Type: application/json');

switch($method) {
    case 'GET':
        // Join with students to display the full name instead of ID
        $sql = "SELECT p.*, CONCAT(s.last_name, ', ', s.first_name) as student_name 
                FROM student_payments p 
                JOIN students s ON p.student_id = s.student_id 
                ORDER BY p.pay_date DESC, p.payment_id DESC";
        $stmt = $pdo->query($sql);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        break;

    case 'POST':
        $sql = "INSERT INTO student_payments (student_id, amount, pay_date, description) 
                VALUES (?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([
            $input['student_id'],
            $input['amount'],
            $input['pay_date'],
            $input['description']
        ]);
        echo json_encode(['success' => true, 'message' => 'Payment recorded successfully']);
        break;

    case 'PUT':
        $sql = "UPDATE student_payments SET student_id = ?, amount = ?, pay_date = ?, 
                description = ? WHERE payment_id = ?";
        $pdo->prepare($sql)->execute([
            $input['student_id'],
            $input['amount'],
            $input['pay_date'],
            $input['description'],
            $id
        ]);
        echo json_encode(['success' => true, 'message' => 'Payment record updated']);
        break;

    case 'DELETE':
        $pdo->prepare("DELETE FROM student_payments WHERE payment_id = ?")->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Payment record deleted']);
        break;
}