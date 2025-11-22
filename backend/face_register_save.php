<?php
// backend/face_register_save.php

// Show PHP errors while we debug (you can turn display_errors off later)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Always send JSON
header('Content-Type: application/json; charset=utf-8');

// Include DB config
require_once '../linya_db/config.php';

// Make sure we have a valid mysqli connection
if (!isset($conn) || !$conn instanceof mysqli) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'DB connection object not set'
    ]);
    exit;
}

if ($conn->connect_error) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'DB connection failed: ' . $conn->connect_error
    ]);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Only POST allowed']);
    exit;
}

// Read input
$name   = isset($_POST['name'])   ? trim($_POST['name'])   : '';
$email  = isset($_POST['email'])  ? trim($_POST['email'])  : '';
$phone  = isset($_POST['phone'])  ? trim($_POST['phone'])  : '';
$folder = isset($_POST['folder']) ? trim($_POST['folder']) : '';
$encoding_b64 = isset($_POST['encoding']) ? trim($_POST['encoding']) : '';

if ($name === '') {
    echo json_encode(['status' => 'error', 'message' => 'Missing name']);
    exit;
}

// compute safe_name like the Python code: lower, spaces -> underscore
$safe_name = strtolower(str_replace(' ', '_', $name));

$face_blob    = null;
$face_path_db = null;

// If encoding provided (from Python API response), decode and use it
if ($encoding_b64) {
    $face_blob = base64_decode($encoding_b64, true);
    if ($face_blob === false) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid base64 encoding']);
        exit;
    }
    // Store reference path (works for both local paths and GCS URLs)
    $face_path_db = $folder ? rtrim($folder, "/") . '/encoding.npy' : null;
}

// ---- CHECK IF CLIENT ALREADY EXISTS ----
// NOTE: your PK is client_id, not id → use client_id AS id
$checkSql = 'SELECT client_id AS id, name, phone, email 
             FROM clients 
             WHERE name = ? AND phone = ? AND email = ? 
             LIMIT 1';

$chk = $conn->prepare($checkSql);
if (!$chk) {
    echo json_encode(['status' => 'error', 'message' => 'DB prepare error (check): ' . $conn->error]);
    exit;
}

$chk->bind_param('sss', $name, $phone, $email);
if (!$chk->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'DB execute error (check): ' . $chk->error]);
    $chk->close();
    exit;
}

$res = $chk->get_result();
if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    // already exists - return a validation response; do NOT insert again
    echo json_encode([
        'status'         => 'already_registered',
        'message'        => 'A client with the same name, phone and email already exists.',
        'existing_id'    => $row['id'],          // comes from client_id AS id
        'existing_name'  => $row['name'],
        'existing_phone' => $row['phone'],
        'existing_email' => $row['email']
    ]);
    $chk->close();
    exit;
}
$chk->close();

// ---- INSERT NEW CLIENT ----
$stmt = $conn->prepare(
    'INSERT INTO clients (name, safe_name, phone, email, face_data, face_path) 
     VALUES (?, ?, ?, ?, ?, ?)'
);
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'DB prepare error (insert): ' . $conn->error]);
    exit;
}

$null = null;

// bind as blob if available
if ($face_blob !== null) {
    // Use 'b' for blob and send_long_data
    $stmt->bind_param('ssssbs', $name, $safe_name, $phone, $email, $null, $face_path_db);
    $stmt->send_long_data(4, $face_blob);
} else {
    // pass NULL for blob (face_blob and face_path_db can be null)
    $stmt->bind_param('ssssss', $name, $safe_name, $phone, $email, $face_blob, $face_path_db);
}

if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'DB execute error (insert): ' . $stmt->error]);
    $stmt->close();
    exit;
}

$insertId = $stmt->insert_id;
$stmt->close();

echo json_encode([
    'status'    => 'ok',
    'client_id' => $insertId,
    'name'      => $name
]);
exit;
?>
