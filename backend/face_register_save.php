<?php
// backend/face_register_save.php

// Log PHP errors (do not print into JSON)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Always send JSON
header('Content-Type: application/json; charset=utf-8');

// Include DB config
require_once '../linya_db/config.php';

function slog(string $msg, array $ctx = []): void {
    $line = "[face_register_save] " . $msg;
    if (!empty($ctx)) $line .= " " . json_encode($ctx, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    error_log($line);
}

function normalize_safe_name(string $s): string {
    $s = trim($s);
    $s = preg_replace('/[^0-9a-zA-Z]+/', '_', $s);
    $s = strtolower((string)$s);
    $s = preg_replace('/_+/', '_', $s);
    $s = trim($s, '_');
    return $s;
}

// Make sure we have a valid mysqli connection
if (!isset($conn) || !$conn instanceof mysqli) {
    slog("DB connection object not set");
    echo json_encode([
        'status'  => 'error',
        'message' => 'DB connection object not set'
    ]);
    exit;
}

if ($conn->connect_error) {
    slog("DB connection failed", ['err' => $conn->connect_error]);
    echo json_encode([
        'status'  => 'error',
        'message' => 'DB connection failed'
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
$student_number = isset($_POST['student_number']) ? trim($_POST['student_number']) : '';
$course = isset($_POST['course']) ? trim($_POST['course']) : '';

if ($name === '') {
    echo json_encode(['status' => 'error', 'message' => 'Missing name']);
    exit;
}

// ✅ FIX: compute safe_name exactly like Python + face_login.php (regex-based)
$safe_name = normalize_safe_name($name);

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
    slog("DB prepare error (check)", ['err' => $conn->error]);
    echo json_encode(['status' => 'error', 'message' => 'DB prepare error (check)']);
    exit;
}

$chk->bind_param('sss', $name, $phone, $email);
if (!$chk->execute()) {
    slog("DB execute error (check)", ['err' => $chk->error]);
    echo json_encode(['status' => 'error', 'message' => 'DB execute error (check)']);
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
    'INSERT INTO clients (name, safe_name, phone, email, student_number, course, face_data, face_path)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
);
if (!$stmt) {
    slog("DB prepare error (insert)", ['err' => $conn->error]);
    echo json_encode(['status' => 'error', 'message' => 'DB prepare error (insert)']);
    exit;
}

$null = null;

// bind as blob if available
if ($face_blob !== null) {
    // Use 'b' for blob and send_long_data
    // param order: name(0), safe_name(1), phone(2), email(3), student_number(4), course(5), face_data(6), face_path(7)
    $stmt->bind_param('ssssssbs', $name, $safe_name, $phone, $email, $student_number, $course, $null, $face_path_db);
    // send_long_data expects zero-based param index for the blob (face_data is param index 6)
    $stmt->send_long_data(6, $face_blob);
} else {
    // pass NULL for blob (face_blob and face_path_db can be null)
    $stmt->bind_param('ssssssss', $name, $safe_name, $phone, $email, $student_number, $course, $face_blob, $face_path_db);
}

if (!$stmt->execute()) {
    slog("DB execute error (insert)", ['err' => $stmt->error]);
    echo json_encode(['status' => 'error', 'message' => 'DB execute error (insert)']);
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