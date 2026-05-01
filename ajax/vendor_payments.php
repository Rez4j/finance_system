<?php
require_once '../config/db.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

try {
    switch($action) {
        case 'fetch_all':
            $supplier_id = $_POST['supplier_id'] ?? '';
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;
            
            $sql = "SELECT vp.*, s.name as supplier_name
                    FROM vendor_payments vp
                    JOIN suppliers s ON vp.supplier_id = s.supplier_id
                    WHERE 1=1";
            $params = [];
            
            if ($supplier_id) {
                $sql .= " AND vp.supplier_id = ?";
                $params[] = $supplier_id;
            }
            
            $countSql = str_replace("vp.*, s.name as supplier_name", "COUNT(*) as total", $sql);
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];
            
            $sql .= " ORDER BY vp.pay_date DESC LIMIT $limit OFFSET $offset";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $payments = $stmt->fetchAll();
            
            $sumSql = "SELECT SUM(vp.amount) as total_paid FROM vendor_payments vp WHERE 1=1";
            if ($supplier_id) {
                $sumSql .= " AND vp.supplier_id = ?";
            }
            $sumStmt = $pdo->prepare($sumSql);
            $sumParams = $supplier_id ? [$supplier_id] : [];
            $sumStmt->execute($sumParams);
            $totalPaid = $sumStmt->fetch()['total_paid'] ?? 0;
            
            echo json_encode([
                'success' => true, 
                'data' => $payments,
                'total' => $total,
                'total_paid' => $totalPaid,
                'pages' => ceil($total / $limit)
            ]);
            break;

        case 'add':
            $supplier_id = $_POST['supplier_id'];
            $po_id = !empty($_POST['po_id']) ? $_POST['po_id'] : null;
            $pay_date = $_POST['pay_date'];
            $amount = $_POST['amount'];
            
            // If po_id is provided, check if it exists
            if ($po_id) {
                $checkStmt = $pdo->prepare("SELECT po_id FROM purchase_orders WHERE po_id = ?");
                $checkStmt->execute([$po_id]);
                if (!$checkStmt->fetch()) {
                    $po_id = null; // Set to null if PO doesn't exist
                }
            }
            
            $stmt = $pdo->prepare("INSERT INTO vendor_payments (supplier_id, po_id, pay_date, amount) VALUES (?, ?, ?, ?)");
            $stmt->execute([$supplier_id, $po_id, $pay_date, $amount]);
            echo json_encode(['success' => true, 'message' => 'Payment added successfully']);
            break;

        case 'edit':
            $id = $_POST['payment_id'];
            $supplier_id = $_POST['supplier_id'];
            $po_id = !empty($_POST['po_id']) ? $_POST['po_id'] : null;
            $pay_date = $_POST['pay_date'];
            $amount = $_POST['amount'];
            
            // If po_id is provided, check if it exists
            if ($po_id) {
                $checkStmt = $pdo->prepare("SELECT po_id FROM purchase_orders WHERE po_id = ?");
                $checkStmt->execute([$po_id]);
                if (!$checkStmt->fetch()) {
                    $po_id = null; // Set to null if PO doesn't exist
                }
            }
            
            $stmt = $pdo->prepare("UPDATE vendor_payments SET supplier_id = ?, po_id = ?, pay_date = ?, amount = ? WHERE payment_id = ?");
            $stmt->execute([$supplier_id, $po_id, $pay_date, $amount, $id]);
            echo json_encode(['success' => true, 'message' => 'Payment updated successfully']);
            break;

        case 'delete':
            $id = $_POST['payment_id'];
            $stmt = $pdo->prepare("DELETE FROM vendor_payments WHERE payment_id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Payment deleted successfully']);
            break;

        case 'fetch_pos_by_supplier':
            $supplier_id = $_POST['supplier_id'];
            $stmt = $pdo->prepare("SELECT po_id, order_date, total_amount FROM purchase_orders WHERE supplier_id = ? AND status IN ('Ordered', 'Received') ORDER BY order_date DESC");
            $stmt->execute([$supplier_id]);
            $pos = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $pos]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>