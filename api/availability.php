<?php
// GET /api/availability?resourceId={id}&date={YYYY-MM-DD}

if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$resource_id = $_GET['resourceId'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');

if (empty($resource_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'resourceId is required']);
    exit();
}

// Get all reservations for this resource on this date
$stmt = $pdo->prepare("SELECT start_time, end_time FROM reservations 
                       WHERE resource_id = ? 
                       AND DATE(start_time) = ?
                       AND status != 'Cancelled'
                       ORDER BY start_time");
$stmt->execute([$resource_id, $date]);
$booked = $stmt->fetchAll();

// Generate time slots (e.g., 8 AM to 6 PM, 1-hour slots)
$slots = [];
$startHour = 8;
$endHour = 18;

for ($hour = $startHour; $hour < $endHour; $hour++) {
    $slotStart = sprintf("%s %02d:00:00", $date, $hour);
    $slotEnd = sprintf("%s %02d:00:00", $date, $hour + 1);
    
    $isBooked = false;
    foreach ($booked as $booking) {
        if (($slotStart >= $booking['start_time'] && $slotStart < $booking['end_time']) ||
            ($slotEnd > $booking['start_time'] && $slotEnd <= $booking['end_time'])) {
            $isBooked = true;
            break;
        }
    }
    
    $slots[] = [
        'startTime' => sprintf("%02d:00", $hour),
        'endTime' => sprintf("%02d:00", $hour + 1),
        'available' => !$isBooked
    ];
}

echo json_encode([
    'success' => true,
    'resourceId' => $resource_id,
    'date' => $date,
    'data' => $slots
]);
?>