<?php
require_once '../config/db.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

try {
    switch($action) {
        case 'fetch_all':
            $status = $_POST['status'] ?? '';
            $supplier_id = $_POST['supplier_id'] ?? '';
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;
            
            $sql = "SELECT po.*, s.name as supplier_name
                    FROM purchase_orders po
                    JOIN suppliers s ON po.supplier_id = s.supplier_id
                    WHERE 1=1";
            $params = [];
            
            if ($status) {
                $sql .= " AND po.status = ?";
                $params[] = $status;
            }
            if ($supplier_id) {
                $sql .= " AND po.supplier_id = ?";
                $params[] = $supplier_id;
            }
            
            $countSql = str_replace("po.*, s.name as supplier_name", "COUNT(*) as total", $sql);
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];
            
            $sql .= " ORDER BY po.order_date DESC LIMIT $limit OFFSET $offset";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $orders = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true, 
                'data' => $orders,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]);
            break;

        case 'add':
            $supplier_id = $_POST['supplier_id'];
            $order_date = $_POST['order_date'];
            $items = json_decode($_POST['items'], true);
            
            // Calculate total amount
            $total_amount = 0;
            foreach ($items as $item) {
                $total_amount += $item['quantity'] * $item['unit_price'];
            }
            
            // Insert PO
            $stmt = $pdo->prepare("INSERT INTO purchase_orders (supplier_id, order_date, total_amount, status) VALUES (?, ?, ?, 'Draft')");
            $stmt->execute([$supplier_id, $order_date, $total_amount]);
            $po_id = $pdo->lastInsertId();
            
            // Insert line items
            $stmt = $pdo->prepare("INSERT INTO purchase_items (po_id, inventory_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
            foreach ($items as $item) {
                $stmt->execute([$po_id, $item['inventory_id'], $item['quantity'], $item['unit_price']]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Purchase order created successfully']);
            break;

        case 'edit_status':
            $po_id = $_POST['po_id'];
            $status = $_POST['status'];
            
            $pdo->beginTransaction();
            
            // Update PO status
            $stmt = $pdo->prepare("UPDATE purchase_orders SET status = ? WHERE po_id = ?");
            $stmt->execute([$status, $po_id]);
            
            // If status is Received, auto-create vendor payment
            if ($status === 'Received') {
                $stmt = $pdo->prepare("SELECT * FROM purchase_orders WHERE po_id = ?");
                $stmt->execute([$po_id]);
                $po = $stmt->fetch();
                
                $stmt = $pdo->prepare("INSERT INTO vendor_payments (supplier_id, po_id, pay_date, amount) VALUES (?, ?, CURDATE(), ?)");
                $stmt->execute([$po['supplier_id'], $po_id, $po['total_amount']]);
            }
            
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
            break;

        case 'delete':
            $po_id = $_POST['po_id'];
            
            $pdo->beginTransaction();
            
            // Delete line items first
            $stmt = $pdo->prepare("DELETE FROM purchase_items WHERE po_id = ?");
            $stmt->execute([$po_id]);
            
            // Delete PO
            $stmt = $pdo->prepare("DELETE FROM purchase_orders WHERE po_id = ?");
            $stmt->execute([$po_id]);
            
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Purchase order deleted successfully']);
            break;

        case 'fetch_items':
            $po_id = $_POST['po_id'];
            $stmt = $pdo->prepare("SELECT pi.*, ii.name as item_name 
                                  FROM purchase_items pi
                                  JOIN inventory_items ii ON pi.inventory_id = ii.inventory_id
                                  WHERE pi.po_id = ?");
            $stmt->execute([$po_id]);
            $items = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $items]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch(PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>