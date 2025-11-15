<?php
header('Content-Type: application/json; charset=utf-8');
include '../linya_db/config.php';

// Expect POST form data: name, email, phone, folder (optional)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status'=>'error','message'=>'Only POST allowed']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$folder = trim($_POST['folder'] ?? '');

if ($name === '') {
    echo json_encode(['status'=>'error','message'=>'Missing name']);
    exit;
}

// compute safe_name like the Python code: lower, spaces -> underscore
$safe_name = strtolower(str_replace(' ', '_', $name));

$face_blob = null;
$face_path_db = null;

// If folder provided and encoding.npy exists, try to read it
if ($folder) {
    $encpath = rtrim($folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'encoding.npy';
    if (file_exists($encpath) && is_readable($encpath)) {
        $face_blob = file_get_contents($encpath);
        $face_path_db = $encpath;
    }
}

// Insert into clients table
$stmt = $conn->prepare('INSERT INTO clients (name, safe_name, phone, email, face_data, face_path) VALUES (?, ?, ?, ?, ?, ?)');
if (!$stmt) {
    echo json_encode(['status'=>'error','message'=>'DB prepare error: '.$conn->error]);
    exit;
}

$null = null;
// bind as blob if available
if ($face_blob !== null) {
    $stmt->bind_param('ssssbs', $name, $safe_name, $phone, $email, $null, $face_path_db);
    // Send blob using send_long_data (parameter index is 0-based)
    $stmt->send_long_data(4, $face_blob);
} else {
    // pass NULL for blob
    $stmt->bind_param('ssssss', $name, $safe_name, $phone, $email, $face_blob, $face_path_db);
}

if (!$stmt->execute()) {
    echo json_encode(['status'=>'error','message'=>'DB execute error: '.$stmt->error]);
    $stmt->close();
    exit;
}

$insertId = $stmt->insert_id;
$stmt->close();

echo json_encode(['status'=>'ok','client_id'=>$insertId, 'name'=>$name]);
exit;

?>
