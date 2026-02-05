<?php
header('Content-Type: application/json; charset=utf-8');

// Use your existing DB connection
require_once __DIR__ . '/../linya_db/config.php';

function respond(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// Read JSON input
$raw = file_get_contents('php://input');
$data = json_decode($raw ?? '', true);

if (!is_array($data)) {
    respond(['status' => 'error', 'message' => 'Invalid request body'], 400);
}

$name  = trim($data['name']  ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');

if ($name === '') {
    respond(['status' => 'error', 'message' => 'Name is required'], 400);
}

/* ========= EMAIL CHECK ========= */
if ($email !== '') {
    $stmt = $conn->prepare("SELECT client_id FROM clients WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        respond([
            'status' => 'duplicate',
            'field'  => 'email',
            'message'=> 'This email is already registered.'
        ]);
    }
    $stmt->close();
}

/* ========= PHONE CHECK ========= */
if ($phone !== '') {
    $stmt = $conn->prepare("SELECT client_id FROM clients WHERE phone = ? LIMIT 1");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        respond([
            'status' => 'duplicate',
            'field'  => 'phone',
            'message'=> 'This phone number is already registered.'
        ]);
    }
    $stmt->close();
}

/* ========= NAME CHECK ========= */
$stmt = $conn->prepare("SELECT client_id FROM clients WHERE name = ? LIMIT 1");
$stmt->bind_param("s", $name);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    respond([
        'status' => 'duplicate',
        'field'  => 'name',
        'message'=> 'This name is already registered.'
    ]);
}

$stmt->close();

/* ========= NO DUPLICATES ========= */
respond(['status' => 'ok']);
