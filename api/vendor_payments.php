<?php
require_once '../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$input = json_decode(file_get_contents('php://input'), true);

header('Content-Type: application/json');

switch($method) {
    case 'GET':
        // Joining with suppliers to show the vendor name
        $sql = "SELECT vp.*, s.name as supplier_name 
                FROM vendor_payments vp 
                JOIN suppliers s ON vp.supplier_id = s.supplier_id 
                ORDER BY vp.pay_date DESC";
        $stmt = $pdo->query($sql);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        break;

    case 'POST':
        $sql = "INSERT INTO vendor_payments (supplier_id, po_id, pay_date, amount) 
                VALUES (?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([
            $input['supplier_id'],
            $input['po_id'], // Can be null per schema
            $input['pay_date'],
            $input['amount']
        ]);
        echo json_encode(['success' => true, 'message' => 'Disbursement recorded']);
        break;

    case 'PUT':
        $sql = "UPDATE vendor_payments SET supplier_id = ?, po_id = ?, pay_date = ?, 
                amount = ? WHERE payment_id = ?";
        $pdo->prepare($sql)->execute([
            $input['supplier_id'],
            $input['po_id'],
            $input['pay_date'],
            $input['amount'],
            $id
        ]);
        echo json_encode(['success' => true, 'message' => 'Payment record updated']);
        break;

    case 'DELETE':
        $pdo->prepare("DELETE FROM vendor_payments WHERE payment_id = ?")->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Record deleted']);
        break;
}