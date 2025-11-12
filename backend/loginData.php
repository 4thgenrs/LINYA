<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
// Correct path to the database config (one level up from backend)
require_once __DIR__ . '/../linya_db/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'error'=>'Invalid method']);
    exit;
}

$user = isset($_POST['user_name']) ? trim($_POST['user_name']) : '';
$pass = isset($_POST['password']) ? trim($_POST['password']) : '';

if ($user === '' || $pass === '') {
    echo json_encode(['success'=>false,'error'=>'Username and password are required']);
    exit;
}

// Case-sensitive comparison using BINARY
$stmt = $conn->prepare("SELECT user_id, user_name, password, user_type, office FROM accounts WHERE BINARY user_name = ? AND BINARY password = ? LIMIT 1");
$stmt->bind_param('ss', $user, $pass);
$stmt->execute();
$res = $stmt->get_result();
$userRow = $res->fetch_assoc();
$stmt->close();

if (!$userRow) {
    echo json_encode(['success'=>false,'error'=>'Invalid credentials']);
    exit;
}

// Check role: staff expected to be user_type == 2
if (intval($userRow['user_type']) !== 2) {
    echo json_encode(['success'=>false,'error'=>'Only staff accounts can access this area']);
    exit;
}

// find assigned window (if any)
$window_id = 0;
$window_no = 0;
$office_id = intval($userRow['office']);
$wstmt = $conn->prepare("SELECT window_id, window_no, office_id FROM office_windows WHERE assigned_staff_id = ? AND enabled = 1 LIMIT 1");
$wstmt->bind_param('i', $userRow['user_id']);
$wstmt->execute();
$wr = $wstmt->get_result();
if ($wrow = $wr->fetch_assoc()) {
    $window_id = intval($wrow['window_id']);
    $window_no = intval($wrow['window_no']);
    $office_id = intval($wrow['office_id']); // prefer office from assignment
}
$wstmt->close();

// set session (unique per browser)
$_SESSION['user_id'] = intval($userRow['user_id']);
$_SESSION['user_name'] = $userRow['user_name'];
$_SESSION['user_type'] = intval($userRow['user_type']);
$_SESSION['office_id'] = $office_id;
$_SESSION['window_id'] = $window_id;
$_SESSION['window_no'] = $window_no;

echo json_encode([
    'success'=>true,
    'user' => [
        'user_id'=>$_SESSION['user_id'],
        'user_name'=>$_SESSION['user_name'],
        'office_id'=>$_SESSION['office_id'],
        'window_id'=>$_SESSION['window_id'],
        'window_no'=>$_SESSION['window_no']
    ]
]);
exit;