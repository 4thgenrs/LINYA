<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Allow POST requests from any session (removed admin-only check)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../linya_db/config.php';

try {
    $conn->begin_transaction();
    // clear queues and queue_services (destructive)
    $conn->query("DELETE FROM queue_services");
    $conn->query("DELETE FROM queues");
    // reset counters
    $conn->query("UPDATE queue_counters SET last_number = 0");
    $conn->commit();
    echo json_encode(['success'=>true]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}