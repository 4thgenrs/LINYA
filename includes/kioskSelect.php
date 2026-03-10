<?php
// Simple kiosk selection page - no session or database required
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LINYA - Kiosk Selection</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/kioskSelect.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-container">
                <img src="../resources/linya_logo.png" alt="LINYA Logo" class="logo-image">
                <div class="logo">LINYA</div>
            </div>
            <div class="subtitle">Queue Management System</div>
            <div class="description">Choose how you would like to access the kiosk</div>
        </div>

        <div class="button-group">
            <a href="../kiosk/kioskLogin.php" class="kiosk-btn btn-login">
                <span class="btn-icon"><i class="fas fa-user-circle"></i></span>
                <span class="btn-text">
                    Login with Face
                    <span class="btn-subtitle">Use face recognition to login</span>
                </span>
            </a>

            <a href="../kiosk/kioskRegister.php" class="kiosk-btn btn-register">
                <span class="btn-icon"><i class="fas fa-camera"></i></span>
                <span class="btn-text">
                    Register Face
                    <span class="btn-subtitle">Register your face for quick access</span>
                </span>
            </a>

            <a href="../backend/guestStart.php" class="kiosk-btn btn-guest">
                <span class="btn-icon"><i class="fas fa-user"></i></span>
                <span class="btn-text">
                    Continue as Guest
                    <span class="btn-subtitle">Enter your details manually</span>
                </span>
            </a>
        </div>

        <!-- Need Support Section -->
        <div class="support-section">
            <p class="support-text">
                Need support? Please contact <a href="mailto:support@linya.com" class="support-link">support@linya.com</a>
            </p>
        </div>
    </div>

    <script src="../js/kioskSelect.js"></script>
</body>
</html>