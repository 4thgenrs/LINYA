<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
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

$user_type = intval($userRow['user_type']);

// allow admins (1) and staff (2)
if (!in_array($user_type, [1,2], true)) {
    echo json_encode(['success'=>false,'error'=>'Account type not allowed']);
    exit;
}

// find assigned window for staff only (admin may not have window)
$window_id = 0;
$window_no = 0;
$office_id = intval($userRow['office']);
if ($user_type === 2) {
    $wstmt = $conn->prepare("SELECT window_id, window_no, office_id FROM office_windows WHERE assigned_staff_id = ? AND enabled = 1 LIMIT 1");
    $wstmt->bind_param('i', $userRow['user_id']);
    $wstmt->execute();
    $wr = $wstmt->get_result();
    if ($wrow = $wr->fetch_assoc()) {
        $window_id = intval($wrow['window_id']);
        $window_no = intval($wrow['window_no']);
        $office_id = intval($wrow['office_id']);
    }
    $wstmt->close();
}

// regenerate session id to prevent fixation and set session vars
session_regenerate_id(true);
$_SESSION['user_id'] = intval($userRow['user_id']);
$_SESSION['user_name'] = $userRow['user_name'];
$_SESSION['user_type'] = $user_type;
$_SESSION['office_id'] = $office_id;
$_SESSION['window_id'] = $window_id;
$_SESSION['window_no'] = $window_no;
// track last login time to remember the active session
$_SESSION['last_login'] = time();
$_SESSION['last_active'] = time();

echo json_encode([
    'success'=>true,
    'user' => [
        'user_id'=>$_SESSION['user_id'],
        'user_name'=>$_SESSION['user_name'],
        'user_type'=>$user_type,
        'office_id'=>$_SESSION['office_id'],
        'window_id'=>$_SESSION['window_id'],
        'window_no'=>$_SESSION['window_no']
    ]
]);
exit;