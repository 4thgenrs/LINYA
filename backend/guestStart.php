<?php
// Guest session cleanup - ensures clean visitor form for guest users
session_start();

// Clear only face-login session data, preserve other session data if any
unset($_SESSION['client']);

// Redirect to kioskMenu.php for guest flow
header('Location: ../includes/kioskMenu.php');
exit;
?>