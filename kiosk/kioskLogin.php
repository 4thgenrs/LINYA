<?php
// kiosk/kioskLogin.php
// Include configuration for Python API base URL
require_once __DIR__ . '/../config.php';

// Note: No longer starting Python process from PHP.
// In development, run: python linya_python_api/app.py
// In production, Python API is deployed to Google Cloud Run.

// --- helpers (minimal, safe logging + consistent JSON for AJAX) ---
function kiosk_req_id(): string {
    // short correlation id for logs (no PII)
    return date('YmdHis') . '-' . substr(bin2hex(random_bytes(4)), 0, 8);
}

function kiosk_log(string $reqId, string $msg, array $ctx = []): void {
    // Never log base64 images or large payloads. Keep ctx small/safe.
    $line = "[kioskLogin][$reqId] " . $msg;
    if (!empty($ctx)) {
        $line .= " " . json_encode($ctx, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    error_log($line);
}

function kiosk_json_response(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Build a correct absolute URL to /LINYA/backend/face_login.php regardless of where this file lives.
 * This fixes the common "../backend/..." path issue when your site is hosted under /LINYA/.
 */
function kiosk_face_login_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    // Your project is hosted under /LINYA/
    return $scheme . '://' . $host . '/LINYA/backend/face_login.php';
}

/* ============================================================
   ✅ FIX (IMPORTANT): Handle AJAX POST BEFORE outputting HTML
   Why: If we output HTML first, fetch() receives text/html and
   your JS cannot parse JSON.
   ============================================================ */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ajax_check = $_POST['ajax_check'] ?? 'false';
    $isAjax = ($ajax_check === 'true');

    if ($isAjax) {
        $reqId = kiosk_req_id();

        $image = $_POST['image'] ?? null;
        $check_glasses = $_POST['check_glasses'] ?? 'false'; // kept for compatibility

        if (!$image) {
            kiosk_log($reqId, "AJAX missing image in POST", ['ajax' => true]);
            kiosk_json_response([
                'status' => 'error',
                'code' => 'missing_image',
                'message' => 'No image provided.',
                'request_id' => $reqId
            ], 400);
        }

        // STEP 2: Proceed with recognition
        $api_url = rtrim(PYTHON_API_BASE, "/") . "/recognize";

        // Do NOT log base64 image.
        $payloadArr = [
            "image" => str_replace("data:image/jpeg;base64,", "", $image)
        ];
        $payload = json_encode($payloadArr);

        kiosk_log($reqId, "AJAX calling Python /recognize", [
            'ajax' => true,
            'api' => $api_url,
            'check_glasses' => ($check_glasses === 'true') ? 'true' : 'false',
            'payload_bytes' => strlen((string)$payload)
        ]);

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $result = curl_exec($ch);
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);
        $http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($result === false) {
            kiosk_log($reqId, "AJAX Python API curl failure", [
                'errno' => $curl_errno,
                'error' => $curl_error ?: 'unknown'
            ]);

            kiosk_json_response([
                'status' => 'error',
                'code' => 'python_unreachable',
                'message' => 'Could not connect to Python API.',
                'request_id' => $reqId
            ], 502);
        }

        $response = json_decode($result, true);
        if (!is_array($response)) {
            kiosk_log($reqId, "AJAX invalid JSON from Python", [
                'http' => $http_code,
                'json_error' => json_last_error_msg(),
                'body_head' => substr($result, 0, 200)
            ]);

            kiosk_json_response([
                'status' => 'error',
                'code' => 'invalid_python_response',
                'message' => 'Invalid response from API.',
                'request_id' => $reqId
            ], 502);
        }

        $status = $response['status'] ?? null;
        $msg = $response['message'] ?? null;

        kiosk_log($reqId, "AJAX Python /recognize result", [
            'http' => $http_code,
            'status' => $status ?: 'missing',
            'has_name' => isset($response['name']) ? 'yes' : 'no',
            'has_safe_name' => isset($response['safe_name']) ? 'yes' : 'no'
        ]);

        // Return consistent JSON schema
        if ($status === 'recognized') {
            kiosk_json_response([
                'status' => 'recognized',
                'message' => 'Face recognized.',
                'name' => $response['name'] ?? null,
                'safe_name' => $response['safe_name'] ?? null,
                'request_id' => $reqId
            ], 200);
        }

        if ($status === 'unknown') {
            kiosk_json_response([
                'status' => 'unknown',
                'message' => $msg ?: 'Unknown user. Please register first.',
                'request_id' => $reqId
            ], 200);
        }

        if ($status === 'accessory_detected') {
            kiosk_json_response([
                'status' => 'accessory_detected',
                'message' => $msg ?: 'Accessory detected. Please remove it and try again.',
                'accessories' => $response['accessories'] ?? null,
                'request_id' => $reqId
            ], 400);
        }

        if ($status === 'no_person_found') {
            kiosk_json_response([
                'status' => 'no_person_found',
                'message' => $msg ?: 'No person detected. Please try again.',
                'request_id' => $reqId
            ], 200);
        }

        kiosk_json_response([
            'status' => 'error',
            'code' => 'unexpected_status',
            'message' => $msg ?: 'Unexpected response from recognition API.',
            'raw_status' => $status,
            'request_id' => $reqId
        ], 502);
    }
}
/* ============================================================ */
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

            <div id="statusMessage" role="status" aria-live="polite" style="margin-bottom: 12px;"></div>

            <div class="buttons-container">
                <button type="button" id="startCaptureBtn" onclick="startCaptureFace()" aria-label="Start capture face">
                    <!-- Camera SVG -->
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 5c-1.1 0-2 .9-2 2H7l-2 3v7c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2v-7l-2-3h-3c0-1.1-.9-2-2-2zM12 17a4 4 0 100-8 4 4 0 000 8z" />
                    </svg>
                    <span class="btn-label">Start Capture Face</span>
                </button>
            </div>
        </form>
    </div>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $reqId = kiosk_req_id();

        // For kiosk AJAX check
        $image = $_POST['image'] ?? null;
        $check_glasses = $_POST['check_glasses'] ?? 'false'; // kept for compatibility
        $ajax_check = $_POST['ajax_check'] ?? 'false';

        // If ajax_check requested, ensure we only output JSON (never HTML)
        $isAjax = ($ajax_check === 'true');

        if (!$image) {
            kiosk_log($reqId, "Missing image in POST", ['ajax' => $isAjax]);
            if ($isAjax) {
                kiosk_json_response([
                    'status' => 'error',
                    'code' => 'missing_image',
                    'message' => 'No image provided.'
                ], 400);
            }
        }

        if ($image) {
            // STEP 2: Proceed with recognition (only if face-check passed)
            $api_url = PYTHON_API_BASE . "/recognize";

            // Do NOT log base64 image.
            $payloadArr = [
                "image" => str_replace("data:image/jpeg;base64,", "", $image)
                // accessory detection is always enforced server-side in Python;
                // we keep check_glasses for compatibility but Python currently ignores it.
            ];
            $payload = json_encode($payloadArr);

            kiosk_log($reqId, "Calling Python /recognize", [
                'ajax' => $isAjax,
                'api' => $api_url,
                'check_glasses' => ($check_glasses === 'true') ? 'true' : 'false',
                'payload_bytes' => strlen((string)$payload)
            ]);

            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $result = curl_exec($ch);
            $curl_errno = curl_errno($ch);
            $curl_error = curl_error($ch);
            $http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // If Python unreachable
            if ($result === false) {
                kiosk_log($reqId, "Python API curl failure", [
                    'errno' => $curl_errno,
                    'error' => $curl_error ?: 'unknown'
                ]);

                $client_message = 'Could not connect to Python API.';
                if ($isAjax) {
                    kiosk_json_response([
                        'status' => 'error',
                        'code' => 'python_unreachable',
                        'message' => $client_message,
                        'request_id' => $reqId
                    ], 502);
                }

                echo "<div>";
                echo "<script>window.serverCaptureReset = " . json_encode([
                    'message' => $client_message,
                    'disableLogin' => true
                ]) . ";</script>";
                echo "<p style='color:red;'>Error: Could not connect to Python API.</p>";
                echo "</div>";
                return;
            }

            // Decode JSON response
            $response = json_decode($result, true);
            if (!is_array($response)) {
                kiosk_log($reqId, "Invalid JSON from Python", [
                    'http' => $http_code,
                    'json_error' => json_last_error_msg(),
                    'body_head' => substr($result, 0, 200)
                ]);

                $client_message = 'Invalid response from API.';
                if ($isAjax) {
                    kiosk_json_response([
                        'status' => 'error',
                        'code' => 'invalid_python_response',
                        'message' => $client_message,
                        'request_id' => $reqId
                    ], 502);
                }

                echo "<div>";
                echo "<script>window.serverCaptureReset = " . json_encode([
                    'message' => $client_message,
                    'disableLogin' => true
                ]) . ";</script>";
                echo "<p style='color:red;'>Error: Invalid response from API.<br>Response was:<br><pre>"
                    . htmlspecialchars($result) . "</pre></p>";
                echo "</div>";
                return;
            }

            $status = $response['status'] ?? null;

            kiosk_log($reqId, "Python /recognize result", [
                'http' => $http_code,
                'status' => $status ?: 'missing',
                'has_name' => isset($response['name']) ? 'yes' : 'no',
                'has_safe_name' => isset($response['safe_name']) ? 'yes' : 'no'
            ]);

            // --- AJAX mode: always return JSON, consistent schema ---
            if ($isAjax) {
                // Normalize messages
                $msg = $response['message'] ?? null;

                if ($status === 'recognized') {
                    kiosk_json_response([
                        'status' => 'recognized',
                        'message' => 'Face recognized.',
                        'name' => $response['name'] ?? null,
                        'safe_name' => $response['safe_name'] ?? null,
                        'request_id' => $reqId
                    ], 200);
                }

                if ($status === 'unknown') {
                    kiosk_json_response([
                        'status' => 'unknown',
                        'message' => $msg ?: 'Unknown user. Please register first.',
                        'request_id' => $reqId
                    ], 200);
                }

                if ($status === 'accessory_detected') {
                    kiosk_json_response([
                        'status' => 'accessory_detected',
                        'message' => $msg ?: 'Accessory detected. Please remove it and try again.',
                        'accessories' => $response['accessories'] ?? null,
                        'request_id' => $reqId
                    ], 400);
                }

                if ($status === 'no_person_found') {
                    kiosk_json_response([
                        'status' => 'no_person_found',
                        'message' => $msg ?: 'No person detected. Please try again.',
                        'request_id' => $reqId
                    ], 200);
                }

                // Fallback for any other status
                kiosk_json_response([
                    'status' => 'error',
                    'code' => 'unexpected_status',
                    'message' => $msg ?: 'Unexpected response from recognition API.',
                    'raw_status' => $status,
                    'request_id' => $reqId
                ], 502);
            }

            // --- Non-AJAX mode: keep existing UI/modal behavior ---
            echo "<div>";

            if ($status === 'recognized') {
                // Normal form submission - show CONFIRMATION MODAL (NO COUNTDOWN)
                $dispName = htmlspecialchars($response['name']);

                // Build a safe absolute URL so the browser always hits the correct endpoint
                $faceLoginUrl = kiosk_face_login_url();

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

                // Absolute endpoint (fixes ../backend path issues under /LINYA/)
                const FACE_LOGIN_URL = " . json_encode($faceLoginUrl) . ";

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

                        console.log('🔐 Creating session with payload:', payload, 'url:', FACE_LOGIN_URL);

                        const resp = await fetch(FACE_LOGIN_URL, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(payload),
                            cache: 'no-store',
                            credentials: 'same-origin'
                        });

                        // Robust JSON parsing (prevents silent failures when server returns HTML/warnings)
                        const raw = await resp.text();
                        let j = null;
                        try { j = JSON.parse(raw); } catch (_) {}

                        console.log('🔐 face_login raw response:', raw);

                        if (!resp.ok) {
                            throw new Error('HTTP ' + resp.status + ' ' + resp.statusText);
                        }

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
                            alert('Login failed: ' + (j && j.message ? j.message : (raw || 'unknown')));
                            const modal = document.getElementById('welcomeModal');
                            if (modal) modal.remove();
                            // Reset to allow user to try again
                            if (typeof resetCapture === 'function') {
                                resetCapture();
                            }
                        }
                    } catch (e) {
                        console.error('🔐 ❌ Login request failed:', e);
                        alert('Login request failed: ' + e.message);
                        const modal = document.getElementById('welcomeModal');
                        if (modal) modal.remove();
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
            } elseif ($status === 'unknown') {
                $client_message = 'Unknown user. <a href="kioskRegister.php" style="color:inherit;text-decoration:underline;">Register here</a>.';
                echo "<script>window.serverCaptureReset = " . json_encode([
                    'message' => $client_message,
                    'disableLogin' => true
                ]) . ";</script>";
                echo "<p style='color:orange;'>Unknown user. <a href='kioskRegister.php'>Register here</a>.</p>";
            } elseif ($status === 'accessory_detected') {
                $accessory = htmlspecialchars($response['message'] ?? 'Accessory detected. Please remove it and try again.');
                $client_message = $accessory;
                echo "<script>window.serverCaptureReset = " . json_encode([
                    'message' => $client_message,
                    'disableLogin' => true
                ]) . ";</script>";
                echo "<p style='color:red; font-weight:bold;'>⚠️ $accessory</p>";
            } elseif ($status === 'no_person_found') {
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

            echo "</div>";
        }
    }
    ?>

    <script src="../js/kioskLogin.js" defer></script>

</body>

</html>