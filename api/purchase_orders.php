<?php
require_once '../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$supplier_id = $_GET['supplier_id'] ?? null;
$input = json_decode(file_get_contents('php://input'), true);

header('Content-Type: application/json');

switch($method) {
    case 'GET':
        if ($supplier_id) {
            // Filtered list for the Vendor Payments module
            $stmt = $pdo->prepare("SELECT po_id, total_amount FROM purchase_orders WHERE supplier_id = ? AND status = 'Received'");
            $stmt->execute([$supplier_id]);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        } else {
            // Updated query: MUST JOIN with suppliers to get 'supplier_name'
            $sql = "SELECT po.*, s.name as supplier_name 
                    FROM purchase_orders po 
                    JOIN suppliers s ON po.supplier_id = s.supplier_id 
                    ORDER BY po.order_date DESC";
            $stmt = $pdo->query($sql);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        }
        break;

    case 'POST':
        // New orders are created with a 'Draft' status by default
        $sql = "INSERT INTO purchase_orders (supplier_id, order_date, total_amount, status) VALUES (?, ?, ?, 'Draft')";
        $pdo->prepare($sql)->execute([
            $input['supplier_id'],
            $input['order_date'],
            $input['total_amount']
        ]);
        echo json_encode(['success' => true, 'message' => 'Order created as Draft']);
        break;

    case 'PUT':
        if (isset($input['action'])) {
            // Handles Approve (Ordered) or Cancel actions[cite: 1]
            $newStatus = ($input['action'] === 'approve') ? 'Ordered' : 'Cancelled';
            $sql = "UPDATE purchase_orders SET status = ? WHERE po_id = ?";
            $pdo->prepare($sql)->execute([$newStatus, $id]);
            echo json_encode(['success' => true, 'message' => "Order $newStatus"]);
        } else {
            // Standard update for order details[cite: 1]
            $sql = "UPDATE purchase_orders SET supplier_id = ?, order_date = ?, total_amount = ? WHERE po_id = ?";
            $pdo->prepare($sql)->execute([
                $input['supplier_id'],
                $input['order_date'],
                $input['total_amount'],
                $id
            ]);
            echo json_encode(['success' => true, 'message' => 'Order updated']);
        }
        break;

    case 'DELETE':
        $pdo->prepare("DELETE FROM purchase_orders WHERE po_id = ?")->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Order deleted']);
        break;
}