<?php
require_once '../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$input = json_decode(file_get_contents('php://input'), true);

header('Content-Type: application/json');

switch($method) {
    case 'GET':
        // Join with departments to display the department name
        $sql = "SELECT b.*, d.name as department_name 
                FROM budgets b 
                JOIN departments d ON b.department_id = d.department_id 
                ORDER BY b.year DESC, d.name ASC";
        $stmt = $pdo->query($sql);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        break;

    case 'POST':
        $sql = "INSERT INTO budgets (department_id, year, allocated_amount) VALUES (?, ?, ?)";
        $pdo->prepare($sql)->execute([
            $input['department_id'],
            $input['year'],
            $input['allocated_amount']
        ]);
        echo json_encode(['success' => true, 'message' => 'Budget allocated successfully']);
        break;

    case 'PUT':
        $sql = "UPDATE budgets SET department_id = ?, year = ?, allocated_amount = ? WHERE budget_id = ?";
        $pdo->prepare($sql)->execute([
            $input['department_id'],
            $input['year'],
            $input['allocated_amount'],
            $id
        ]);
        echo json_encode(['success' => true, 'message' => 'Budget updated successfully']);
        break;

    case 'DELETE':
        $pdo->prepare("DELETE FROM budgets WHERE budget_id = ?")->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Budget record removed']);
        break;
}