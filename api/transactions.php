<?php
require_once '../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$input = json_decode(file_get_contents('php://input'), true);

header('Content-Type: application/json');

switch($method) {
    case 'GET':
        // Join with accounts to show the account name in the table[cite: 1]
        $sql = "SELECT t.*, a.name as account_name 
                FROM transactions t 
                LEFT JOIN accounts a ON t.account_id = a.account_id 
                ORDER BY t.trans_date DESC, t.transaction_id DESC";
        $stmt = $pdo->query($sql);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        break;

    case 'POST':
        $sql = "INSERT INTO transactions (account_id, trans_date, amount, type, description) 
                VALUES (?, ?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([
            $input['account_id'],
            $input['trans_date'],
            $input['amount'],
            $input['type'],
            $input['description']
        ]);
        echo json_encode(['success' => true, 'message' => 'Transaction recorded']);
        break;

    case 'PUT':
        $sql = "UPDATE transactions SET account_id = ?, trans_date = ?, amount = ?, 
                type = ?, description = ? WHERE transaction_id = ?";
        $pdo->prepare($sql)->execute([
            $input['account_id'],
            $input['trans_date'],
            $input['amount'],
            $input['type'],
            $input['description'],
            $id
        ]);
        echo json_encode(['success' => true, 'message' => 'Transaction updated']);
        break;

    case 'DELETE':
        $pdo->prepare("DELETE FROM transactions WHERE transaction_id = ?")->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Transaction deleted']);
        break;
}