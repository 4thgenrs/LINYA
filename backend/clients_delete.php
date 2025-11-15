<?php
header('Content-Type: application/json; charset=utf-8');
include '../linya_db/config.php';
session_start();

// Require POST JSON with { client_id }
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data) || !isset($data['client_id'])) {
    echo json_encode(['status'=>'error','message'=>'Missing client_id']);
    exit;
}

$client_id = intval($data['client_id']);
if ($client_id <= 0) {
    echo json_encode(['status'=>'error','message'=>'Invalid client_id']);
    exit;
}

// Delete client row
$stmt = $conn->prepare('DELETE FROM clients WHERE client_id = ?');
if (!$stmt) { echo json_encode(['status'=>'error','message'=>'DB prepare failed']); exit; }
$stmt->bind_param('i', $client_id);
if (!$stmt->execute()) {
    echo json_encode(['status'=>'error','message'=>'DB execute failed: '.$stmt->error]);
    $stmt->close();
    exit;
}
$affected = $stmt->affected_rows;
$stmt->close();

if ($affected > 0) echo json_encode(['status'=>'ok']); else echo json_encode(['status'=>'error','message'=>'Not found']);
exit;

?>
