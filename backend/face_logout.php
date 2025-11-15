<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
// Clear client session data only
if (isset($_SESSION['client'])) unset($_SESSION['client']);
// Optionally destroy entire session
session_destroy();
echo json_encode(['status'=>'ok','message'=>'Logged out']);
exit;

?>
