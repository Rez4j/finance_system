<?php
require_once '../config/db.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

try {
    switch($action) {
        case 'fetch_all':
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;
            
            $sql = "SELECT sp.*, CONCAT(s.first_name, ' ', s.last_name) as student_name 
                    FROM student_payments sp
                    JOIN students s ON sp.student_id = s.student_id
                    ORDER BY sp.pay_date DESC
                    LIMIT $limit OFFSET $offset";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $payments = $stmt->fetchAll();
            
            $countStmt = $pdo->query("SELECT COUNT(*) as total FROM student_payments");
            $total = $countStmt->fetch()['total'];
            
            $sumStmt = $pdo->query("SELECT SUM(amount) as total_collected FROM student_payments");
            $totalCollected = $sumStmt->fetch()['total_collected'] ?? 0;
            
            echo json_encode([
                'success' => true, 
                'data' => $payments,
                'total' => $total,
                'total_collected' => $totalCollected,
                'pages' => ceil($total / $limit)
            ]);
            break;

        case 'add':
            $student_id = $_POST['student_id'];
            $amount = $_POST['amount'];
            $pay_date = $_POST['pay_date'];
            $description = $_POST['description'];
            
            $stmt = $pdo->prepare("INSERT INTO student_payments (student_id, amount, pay_date, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$student_id, $amount, $pay_date, $description]);
            echo json_encode(['success' => true, 'message' => 'Payment added successfully']);
            break;

        case 'edit':
            $id = $_POST['payment_id'];
            $student_id = $_POST['student_id'];
            $amount = $_POST['amount'];
            $pay_date = $_POST['pay_date'];
            $description = $_POST['description'];
            
            $stmt = $pdo->prepare("UPDATE student_payments SET student_id = ?, amount = ?, pay_date = ?, description = ? WHERE payment_id = ?");
            $stmt->execute([$student_id, $amount, $pay_date, $description, $id]);
            echo json_encode(['success' => true, 'message' => 'Payment updated successfully']);
            break;

        case 'delete':
            $id = $_POST['payment_id'];
            $stmt = $pdo->prepare("DELETE FROM student_payments WHERE payment_id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Payment deleted successfully']);
            break;

        case 'search':
            $search = $_POST['search'] ?? '';
            $sql = "SELECT sp.*, CONCAT(s.first_name, ' ', s.last_name) as student_name 
                    FROM student_payments sp
                    JOIN students s ON sp.student_id = s.student_id
                    WHERE CONCAT(s.first_name, ' ', s.last_name) LIKE ? 
                    OR sp.payment_id LIKE ?
                    ORDER BY sp.pay_date DESC";
            $searchTerm = "%$search%";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$searchTerm, $searchTerm]);
            $payments = $stmt->fetchAll();
            
            $sumStmt = $pdo->query("SELECT SUM(amount) as total_collected FROM student_payments");
            $totalCollected = $sumStmt->fetch()['total_collected'] ?? 0;
            
            echo json_encode([
                'success' => true, 
                'data' => $payments,
                'total_collected' => $totalCollected
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>