
<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include '../linya_db/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'error'=>'Method not allowed']);
    exit;
}

// require staff session for security
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || intval($_SESSION['user_type']) !== 2) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'Unauthorized']);
    exit;
}

$queue_id = isset($_POST['queue_id']) ? intval($_POST['queue_id']) : 0;
if (!$queue_id) {
    echo json_encode(['success'=>false,'error'=>'Missing queue_id']);
    exit;
}

if (!isset($_FILES['attachment'])) {
    echo json_encode(['success'=>false,'error'=>'No file uploaded']);
    exit;
}

$file = $_FILES['attachment'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success'=>false,'error'=>'Upload error code: '.$file['error']]);
    exit;
}

$maxBytes = 10 * 1024 * 1024; // 10 MB
if ($file['size'] > $maxBytes) {
    echo json_encode(['success'=>false,'error'=>'File exceeds 10 MB limit']);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowed = ['application/pdf','image/jpeg','image/png'];
if (!in_array($mime, $allowed, true)) {
    echo json_encode(['success'=>false,'error'=>'Invalid file type. Allowed: pdf, jpg, png']);
    exit;
}

// ensure uploads folder exists
$uploadDir = __DIR__ . '/../uploads/attachments';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$basename = bin2hex(random_bytes(8));
$filename = $basename . '.' . $ext;
$target = $uploadDir . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $target)) {
    echo json_encode(['success'=>false,'error'=>'Failed to move uploaded file']);
    exit;
}

// optional: record attachment in DB (attachments table) — create simple table if desired
// For now return path relative to project
$relative = 'uploads/attachments/' . $filename;

echo json_encode(['success'=>true,'file' => $relative, 'mime' => $mime]);
exit;