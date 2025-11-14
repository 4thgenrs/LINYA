
<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../linya_db/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || intval($_SESSION['user_type']) !== 1) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'Unauthorized']);
    exit;
}

echo json_encode([
    'success'=>true,
    'user'=>[
        'id'=>$_SESSION['user_id'],
        'name'=>$_SESSION['user_name']
    ]
]);
exit;