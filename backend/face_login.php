<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
include '../linya_db/config.php';

// Expect JSON body or form POST with name or safe_name
$raw = file_get_contents('php://input');
$data = [];
if ($raw) {
    $d = json_decode($raw, true);
    if (is_array($d)) $data = $d;
}

// Accept client_id, safe_name or name in request body
$client_id = isset($data['client_id']) ? intval($data['client_id']) : (isset($_POST['client_id']) ? intval($_POST['client_id']) : 0);
$safe_name = trim($data['safe_name'] ?? $_POST['safe_name'] ?? '');
$name = trim($data['name'] ?? $_POST['name'] ?? '');

// search priority: client_id > safe_name > name
$client = null;
if ($client_id) {
    $stmt = $conn->prepare('SELECT client_id, name, safe_name, phone, email, face_path FROM clients WHERE client_id = ? LIMIT 1');
    $stmt->bind_param('i', $client_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $client = $res->fetch_assoc();
    $stmt->close();
}

if (!$client && $safe_name !== '') {
    $stmt = $conn->prepare('SELECT client_id, name, safe_name, phone, email, face_path FROM clients WHERE safe_name = ? LIMIT 1');
    $stmt->bind_param('s', $safe_name);
    $stmt->execute();
    $res = $stmt->get_result();
    $client = $res->fetch_assoc();
    $stmt->close();
}

if (!$client && $name !== '') {
    // try matching safe_name derived from display name
    $derived_safe = strtolower(preg_replace('/[^0-9a-zA-Z]+/', '_', trim($name)));
    $stmt = $conn->prepare('SELECT client_id, name, safe_name, phone, email, face_path FROM clients WHERE safe_name = ? OR name = ? LIMIT 1');
    $stmt->bind_param('ss', $derived_safe, $name);
    $stmt->execute();
    $res = $stmt->get_result();
    $client = $res->fetch_assoc();
    $stmt->close();
}

if (!$client) {
    echo json_encode(['status'=>'error','message'=>'Client not found']);
    exit;
}

// store client data in session
$_SESSION['client'] = [
    'client_id' => $client['client_id'],
    'name' => $client['name'],
    'safe_name' => $client['safe_name'],
    'phone' => $client['phone'],
    'email' => $client['email']
];

echo json_encode(['status'=>'ok','message'=>'Session created','client'=>$_SESSION['client']]);
exit;

?>
