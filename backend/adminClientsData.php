<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../linya_db/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || intval($_SESSION['user_type']) !== 1) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'Unauthorized']);
    exit;
}

try {
    $stmt = $conn->prepare('SELECT client_id, name, email, phone, safe_name, created_at FROM clients ORDER BY created_at DESC');
    if (!$stmt) {
        throw new Exception('DB prepare failed: ' . $conn->error);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $clients = [];
    
    while ($row = $result->fetch_assoc()) {
        $clients[] = $row;
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'clients' => $clients
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
exit;
?>