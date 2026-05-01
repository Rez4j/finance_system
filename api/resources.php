<?php
// GET /api/resources - List all resources
// GET /api/resources/{id} - Get single resource

if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

if ($id) {
    // GET /api/resources/{id}
    $stmt = $pdo->prepare("SELECT 
                            resource_id as resourceId,
                            name as resourceName,
                            location as resourceLocation,
                            capacity,
                            type
                           FROM resources 
                           WHERE resource_id = ?");
    $stmt->execute([$id]);
    $resource = $stmt->fetch();
    
    if ($resource) {
        // Ensure capacity is integer
        $resource['capacity'] = (int)$resource['capacity'];
        
        echo json_encode([
            'success' => true, 
            'data' => $resource
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Resource not found']);
    }
} else {
    // GET /api/resources - List all
    $type = $_GET['type'] ?? '';
    
    $sql = "SELECT 
                resource_id as resourceId,
                name as resourceName,
                location as resourceLocation,
                capacity,
                type
            FROM resources";
    $params = [];
    
    if ($type && in_array($type, ['establishment', 'equipment'])) {
        // Map API types to DB types
        $typeMap = [
            'establishment' => 'Room',
            'equipment' => 'Equipment'
        ];
        $dbType = $typeMap[$type] ?? $type;
        $sql .= " WHERE type = ?";
        $params[] = $dbType;
    }
    
    $sql .= " ORDER BY type, name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $resources = $stmt->fetchAll();
    
    // Ensure capacity is integer for each resource
    foreach ($resources as &$resource) {
        $resource['capacity'] = (int)$resource['capacity'];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $resources,
        'total' => count($resources)
    ]);
}
?>