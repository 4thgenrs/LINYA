<?php
// Include configuration for Python API base URL
require_once __DIR__ . '/../config.php';

// Note: No longer starting Python process from PHP.
// In development, run: python linya_python_api/app.py
// In production, Python API is deployed to Google Cloud Run.
?>
<!DOCTYPE html>
<html>

<head>
    <title>Face Login</title>
    <link rel="stylesheet" href="../css/backButton.css">
    <link rel="stylesheet" href="../css/kioskLogin.css">
</head>

<body>

    <!-- In your PHP file, wrap the main content like this: -->
    <div class="main-container">
        <div>
            <!-- Back Button -->
            <a href="../includes/kioskSelect.php" class="back-btn" title="Go back to selection">
                <svg width="30" height="30" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <style>
                            .cls-1 {
                                fill: none;
                                stroke: currentColor;
                                stroke-linecap: round;
                                stroke-linejoin: round;
                                stroke-width: 20px;
                            }
                        </style>
                    </defs>
                    <g data-name="Layer 2" id="Layer_2">
                        <g data-name="E421, Back, buttons, multimedia, play, stop" id="E421_Back_buttons_multimedia_play_stop">
                            <circle class="cls-1" cx="256" cy="256" r="246" />
                            <line class="cls-1" x1="352.26" x2="170.43" y1="256" y2="256" />
                            <polyline class="cls-1" points="223.91 202.52 170.44 256 223.91 309.48" />
                        </g>
                    </g>
                </svg>
            </a>
            <h1 class="page-title">Face Recognition Login</h1>
        </div>

        <div class="camera-container">
            <video id="video" autoplay muted playsinline></video>
            <canvas id="canvas" style="display:none;"></canvas>
            <div id="faceOutline"></div>
        </div>

        <div class="verification-text">
            <p>PLEASE LOOK DIRECTLY AT THE CAMERA</p>
            <div class="instructions">
                <p>• ENSURE GOOD LIGHTING</p>
                <p>• REMOVE SUNGLASSES OR HATS</p>
                <p>• KEEP A NEUTRAL EXPRESSION</p>
            </div>
        </div>

        <form method="POST" action="kioskLogin.php" id="loginForm">
            <input type="hidden" name="image" id="imageInput">
            <input type="hidden" name="check_glasses" id="checkGlasses" value="true">

            <div class="buttons-container">
                <button type="button" onclick="captureImage()" aria-label="Capture face">
                    <!-- Camera SVG -->
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 5c-1.1 0-2 .9-2 2H7l-2 3v7c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2v-7l-2-3h-3c0-1.1-.9-2-2-2zM12 17a4 4 0 100-8 4 4 0 000 8z" />
                    </svg>
                    Capture Face
                </button>

                <div class="btn-wrapper disabled" id="loginWrapper">
                    <button type="button" id="loginBtn" disabled aria-disabled="true" onclick="handleLogin()">
                        <!-- Lock SVG -->
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12 17a2 2 0 110-4 2 2 0 010 4zm6-7h-1V7a5 5 0 00-10 0v3H6a2 2 0 00-2 2v7a2 2 0 002 2h12a2 2 0 002-2v-7a2 2 0 00-2-2zm-7-3a3 3 0 116 0v3H11V7z" />
                        </svg>
                        Login
                        <!-- Stop SVG hidden by default, shown on hover via wrapper -->
                        <svg class="stop-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M6 6h12v12H6z" />
                        </svg>
                    </button>
                    <div class="btn-tooltip" role="tooltip">Capture a photo first to enable Login</div>
                </div>

                <button type="button" onclick="resetCapture()" aria-label="Reset">
                    <!-- Reset/refresh SVG -->
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 6V4l4 4-4 4V8c-3.31 0-6 2.69-6 6 0 1.31.42 2.52 1.13 3.5l-1.46 1.46C5.16 18.03 4 16.14 4 14c0-4.42 3.58-8 8-8zM18.87 6.5L20.33 5.04C18.84 3.24 15.81 2 12 2 7.58 2 4 5.58 4 10c0 1.86.68 3.57 1.81 4.9L7 13.71C6.44 12.95 6 11.99 6 11c0-3.31 2.69-6 6-6 2.76 0 5.08 1.78 5.87 4.5z" />
                    </svg>
                    Reset
                </button>
            </div>
        </form>
    </div>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $image = $_POST['image'] ?? null;
    $check_glasses = $_POST['check_glasses'] ?? 'false';
    $ajax_check = $_POST['ajax_check'] ?? 'false';

    if ($image) {
        // ... (keep all the existing face-check code the same until recognition section)

        // STEP 2: Proceed with recognition (only if face-check passed)
        $api_url = PYTHON_API_BASE . "/recognize";
        $payload = json_encode([
            "image" => str_replace("data:image/jpeg;base64,", "", $image)
        ]);

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $result = curl_exec($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);

        echo "<div>";

        // Check if the API returned anything
        if ($result === false) {
            $client_message = 'Could not connect to Python API: ' . ($curl_error ?: 'unknown');
            echo "<script>window.serverCaptureReset = " . json_encode([
                'message' => $client_message,
                'disableLogin' => true
            ]) . ";</script>";
            echo "<p style='color:red;'>Error: Could not connect to Python API.</p>";
        } else {
            $response = json_decode($result, true);

            // Check if JSON decoding failed
            if ($response === null) {
                $client_message = 'Invalid response from API';
                echo "<script>window.serverCaptureReset = " . json_encode([
                    'message' => $client_message,
                    'disableLogin' => true
                ]) . ";</script>";
                echo "<p style='color:red;'>Error: Invalid response from API.<br>Response was:<br><pre>"
                    . htmlspecialchars($result) . "</pre></p>";
            } elseif (isset($response['status'])) {
                // Then show the recognition result
                if ($response['status'] === 'recognized') {
                    if ($ajax_check === 'true') {
                        // For AJAX requests, just return success indicator (no HTML)
                        echo "SUCCESS_RECOGNIZED";
                        exit;
                    } else {
                        // Normal form submission - show CONFIRMATION MODAL (NO COUNTDOWN)
                        $dispName = htmlspecialchars($response['name']);
                        
                        // Display welcome CONFIRMATION modal with YES/NO buttons
                        echo "
                        <!-- Welcome Confirmation Modal (NO COUNTDOWN) -->
                        <div id='welcomeModal' class='welcome-modal-overlay'>
                            <div class='welcome-modal'>
                                <!-- Animated Background -->
                                <div style='
                                    position: absolute;
                                    top: 0;
                                    left: 0;
                                    width: 100%;
                                    height: 100%;
                                    background: radial-gradient(circle at center, rgba(0, 255, 0, 0.1) 0%, transparent 70%);
                                    z-index: -1;
                                    animation: pulseBackground 2s infinite;
                                '></div>
                                
                                <!-- Success Icon -->
                                <div class='success-icon'>
                                    <svg width='40' height='40' viewBox='0 0 24 24' fill='limegreen'>
                                        <path d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z'/>
                                    </svg>
                                </div>
                                
                                <h2>Welcome Back!</h2>
                                <h3>" . $dispName . "</h3>
                                <p>Face recognition successful. Would you like to proceed to the kiosk menu?</p>
                                
                                <!-- Confirmation Buttons -->
                                <div class='welcome-modal-buttons'>
                                    <button id='confirmLoginBtn' class='confirm-btn' onclick='confirmLogin()'>
                                        <svg width='18' height='18' viewBox='0 0 24 24' fill='currentColor'>
                                            <path d='M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z'/>
                                        </svg>
                                        Yes, Continue
                                    </button>
                                    <button id='cancelLoginBtn' class='cancel-btn' onclick='cancelLogin()'>
                                        <svg width='18' height='18' viewBox='0 0 24 24' fill='currentColor'>
                                            <path d='M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z'/>
                                        </svg>
                                        No, Go Back
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <script>
                        // Store user data for confirmation
                        window.confirmedUserData = {
                            safe_name: " . json_encode($response['safe_name'] ?? null) . ",
                            name: " . json_encode($response['name']) . "
                        };
                        
                        // Function to handle CONFIRM button click
                        async function confirmLogin() {
                            console.log('🔐 User confirmed login');
                            
                            const confirmBtn = document.getElementById('confirmLoginBtn');
                            if (confirmBtn) {
                                confirmBtn.disabled = true;
                                confirmBtn.innerHTML = `
                                    <svg width='18' height='18' viewBox='0 0 24 24' fill='currentColor' style='animation: spin 1s linear infinite;'>
                                        <circle cx='12' cy='12' r='10' fill='none' stroke='currentColor' stroke-width='3' opacity='0.3'/>
                                        <path d='M12 2a10 10 0 0 1 10 10' stroke='currentColor' stroke-width='3' fill='none'/>
                                    </svg>
                                    Processing...
                                `;
                            }
                            
                            try {
                                const payload = {};
                                const userData = window.confirmedUserData;
                                
                                if (userData && userData.safe_name !== null) {
                                    payload.safe_name = userData.safe_name;
                                } else if (userData && userData.name) {
                                    payload.name = userData.name;
                                }
                                
                                console.log('🔐 Creating session with payload:', payload);
                                
                                const resp = await fetch('../backend/face_login.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify(payload)
                                });
                                
                                const j = await resp.json();
                                
                                if (j && j.status === 'ok') {
                                    console.log('🔐 ✅ Session created successfully, redirecting...');
                                    
                                    // Smooth fade out before redirect
                                    const modal = document.getElementById('welcomeModal');
                                    if (modal) {
                                        modal.style.opacity = '0';
                                        modal.style.transition = 'opacity 0.5s ease';
                                    }
                                    
                                    setTimeout(() => {
                                        window.location.href = '../includes/kioskMenu.php';
                                    }, 500);
                                } else {
                                    console.error('🔐 ❌ Login failed:', j && j.message ? j.message : 'unknown');
                                    alert('Login failed: ' + (j && j.message ? j.message : 'unknown'));
                                    document.getElementById('welcomeModal').remove();
                                    // Reset to allow user to try again
                                    if (typeof resetCapture === 'function') {
                                        resetCapture();
                                    }
                                }
                            } catch (e) {
                                console.error('🔐 ❌ Login request failed:', e);
                                alert('Login request failed: ' + e.message);
                                document.getElementById('welcomeModal').remove();
                                // Reset to allow user to try again
                                if (typeof resetCapture === 'function') {
                                    resetCapture();
                                }
                            }
                        }
                        
                        // Function to handle CANCEL button click
                        function cancelLogin() {
                            console.log('🔐 User cancelled login, returning to scanning');
                            
                            const modal = document.getElementById('welcomeModal');
                            if (modal) {
                                modal.style.opacity = '0';
                                modal.style.transition = 'opacity 0.3s ease';
                                setTimeout(() => {
                                    modal.remove();
                                }, 300);
                            }
                            
                            // Clean up user data
                            delete window.confirmedUserData;
                            
                            // Reset capture to go back to scanning
                            if (typeof resetCapture === 'function') {
                                resetCapture();
                            }
                        }
                        </script>";
                    }
                } elseif ($response['status'] === 'unknown') {
                    $client_message = 'Unknown user. <a href="kioskRegister.php" style="color:inherit;text-decoration:underline;">Register here</a>.';
                    echo "<script>window.serverCaptureReset = " . json_encode([
                        'message' => $client_message,
                        'disableLogin' => true
                    ]) . ";</script>";
                    echo "<p style='color:orange;'>Unknown user. <a href='kioskRegister.php'>Register here</a>.</p>";
                } elseif ($response['status'] === 'accessory_detected') {
                    $accessory = htmlspecialchars($response['message']);
                    $client_message = $accessory;
                    echo "<script>window.serverCaptureReset = " . json_encode([
                        'message' => $client_message,
                        'disableLogin' => true
                    ]) . ";</script>";
                    echo "<p style='color:red; font-weight:bold;'>⚠️ $accessory</p>";
                } elseif ($response['status'] === 'no_person_found') {
                    $client_message = $response['message'] ?? 'No person detected. Please try again.';
                    echo "<script>window.serverCaptureReset = " . json_encode([
                        'message' => $client_message,
                        'disableLogin' => true
                    ]) . ";</script>";
                    echo "<p style='color:red;'>No person detected. Please try again.</p>";
                } else {
                    $client_message = $response['message'] ?? 'Unknown error from recognition API';
                    echo "<script>window.serverCaptureReset = " . json_encode([
                        'message' => $client_message,
                        'disableLogin' => true
                    ]) . ";</script>";
                    echo "<p style='color:red;'>Error: " . htmlspecialchars($response['message'] ?? 'Unknown error') . "</p>";
                }
            } else {
                $client_message = 'Unexpected response structure from API';
                echo "<script>window.serverCaptureReset = " . json_encode([
                    'message' => $client_message,
                    'disableLogin' => true
                ]) . ";</script>";
                echo "<p style='color:red;'>Error: Unexpected response structure.<br><pre>"
                    . htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT)) . "</pre></p>";
            }
        }

        echo "</div>";
    }
    }
    ?>

    <script src="../js/kioskLogin.js" defer></script>

</body>

</html>