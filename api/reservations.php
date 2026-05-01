<?php
// GET /api/reservations - List all reservations
// GET /api/reservations/{id} - Get single reservation
// POST /api/reservations - Create reservation
// PUT /api/reservations/{id}/status - Update reservation status
// DELETE /api/reservations/{id} - Cancel reservation

switch($method) {
    case 'GET':
        if ($id) {
            // GET /api/reservations/{id}
            $stmt = $pdo->prepare("SELECT 
                                    r.reservation_id as reservationId,
                                    r.resource_id as resourceId,
                                    res.name as resourceName,
                                    res.type as resourceType,
                                    r.start_time as startTime,
                                    r.end_time as endTime,
                                    r.purpose,
                                    r.status,
                                    CASE 
                                        WHEN r.reserved_by_employee IS NOT NULL THEN CONCAT(e.first_name, ' ', e.last_name)
                                        WHEN r.reserved_by_student IS NOT NULL THEN CONCAT(s.first_name, ' ', s.last_name)
                                        ELSE 'Unknown'
                                    END as reservedBy
                                   FROM reservations r
                                   JOIN resources res ON r.resource_id = res.resource_id
                                   LEFT JOIN employees e ON r.reserved_by_employee = e.employee_id
                                   LEFT JOIN students s ON r.reserved_by_student = s.student_id
                                   WHERE r.reservation_id = ?");
            $stmt->execute([$id]);
            $reservation = $stmt->fetch();
            
            if ($reservation) {
                echo json_encode(['success' => true, 'data' => $reservation]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Reservation not found']);
            }
        } else {
            // GET /api/reservations - List all
            $status = $_GET['status'] ?? '';
            $resource_id = $_GET['resourceId'] ?? '';
            $date_from = $_GET['dateFrom'] ?? '';
            $date_to = $_GET['dateTo'] ?? '';
            
            $sql = "SELECT 
                        r.reservation_id as reservationId,
                        r.resource_id as resourceId,
                        res.name as resourceName,
                        res.type as resourceType,
                        r.start_time as startTime,
                        r.end_time as endTime,
                        r.purpose,
                        r.status,
                        CASE 
                            WHEN r.reserved_by_employee IS NOT NULL THEN CONCAT(e.first_name, ' ', e.last_name)
                            WHEN r.reserved_by_student IS NOT NULL THEN CONCAT(s.first_name, ' ', s.last_name)
                            ELSE 'Unknown'
                        END as reservedBy
                    FROM reservations r
                    JOIN resources res ON r.resource_id = res.resource_id
                    LEFT JOIN employees e ON r.reserved_by_employee = e.employee_id
                    LEFT JOIN students s ON r.reserved_by_student = s.student_id
                    WHERE 1=1";
            $params = [];
            
            if ($status) {
                $sql .= " AND r.status = ?";
                $params[] = $status;
            }
            if ($resource_id) {
                $sql .= " AND r.resource_id = ?";
                $params[] = $resource_id;
            }
            if ($date_from) {
                $sql .= " AND r.start_time >= ?";
                $params[] = $date_from;
            }
            if ($date_to) {
                $sql .= " AND r.end_time <= ?";
                $params[] = $date_to;
            }
            
            $sql .= " ORDER BY r.start_time DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $reservations = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'data' => $reservations,
                'total' => count($reservations)
            ]);
        }
        break;
        
    case 'POST':
        // Create reservation
        $resource_id = $input['resourceId'] ?? '';
        $reserved_by_employee = $input['reservedByEmployee'] ?? null;
        $reserved_by_student = $input['reservedByStudent'] ?? null;
        $start_time = $input['startTime'] ?? '';
        $end_time = $input['endTime'] ?? '';
        $purpose = $input['purpose'] ?? '';
        
        if (empty($resource_id) || empty($start_time) || empty($end_time)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'resourceId, startTime, and endTime are required']);
            exit();
        }
        
        // Check for conflicts
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reservations 
                               WHERE resource_id = ? 
                               AND status != 'Cancelled'
                               AND ((start_time BETWEEN ? AND ?) 
                               OR (end_time BETWEEN ? AND ?)
                               OR (? BETWEEN start_time AND end_time))");
        $stmt->execute([$resource_id, $start_time, $end_time, $start_time, $end_time, $start_time]);
        $conflict = $stmt->fetch();
        
        if ($conflict['count'] > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Time slot conflicts with existing reservation']);
            exit();
        }
        
        $stmt = $pdo->prepare("INSERT INTO reservations 
                               (resource_id, reserved_by_employee, reserved_by_student, start_time, end_time, purpose, status) 
                               VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
        $stmt->execute([$resource_id, $reserved_by_employee, $reserved_by_student, $start_time, $end_time, $purpose]);
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Reservation created',
            'data' => ['reservationId' => $pdo->lastInsertId()]
        ]);
        break;
        
    case 'PUT':
        // Update reservation status
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Reservation ID required']);
            exit();
        }
        
        // Check if it's a status update
        if (isset($input['status'])) {
            $status = $input['status'];
            $validStatuses = ['Pending', 'Confirmed', 'Cancelled'];
            
            if (!in_array($status, $validStatuses)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid status. Valid: Pending, Confirmed, Cancelled']);
                exit();
            }
            
            $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
            $stmt->execute([$status, $id]);
            
            echo json_encode(['success' => true, 'message' => 'Reservation status updated to ' . $status]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No update data provided']);
        }
        break;
        
    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Reservation ID required']);
            exit();
        }
        
        // Soft delete - set status to Cancelled
        $stmt = $pdo->prepare("UPDATE reservations SET status = 'Cancelled' WHERE reservation_id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true, 'message' => 'Reservation cancelled']);
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>