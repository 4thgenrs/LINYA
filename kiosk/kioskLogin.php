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

<!-- Back Button -->
<a href="../includes/kioskSelect.php" class="back-btn" title="Go back to selection">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
        <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.42-1.41L7.83 13H20v-2z"/>
    </svg>
    Back
</a>

<h1>Face Recognition Login</h1>

<div class="camera-container">
    <video id="video" autoplay muted playsinline></video>
    <canvas id="canvas" style="display:none;"></canvas>
    <div id="faceOutline"></div>
</div>

<form method="POST" action="kioskLogin.php" id="loginForm">
    <input type="hidden" name="image" id="imageInput">
    <input type="hidden" name="check_glasses" id="checkGlasses" value="true">
    <button type="button" onclick="captureImage()" aria-label="Capture face"> 
        <!-- Camera SVG -->
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="vertical-align:middle;">
            <path d="M12 5c-1.1 0-2 .9-2 2H7l-2 3v7c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2v-7l-2-3h-3c0-1.1-.9-2-2-2zM12 17a4 4 0 100-8 4 4 0 000 8z"/>
        </svg>
        <span style="margin-left:6px;vertical-align:middle;">Capture Face</span>
    </button>

    <span class="btn-wrapper disabled" id="loginWrapper">
        <button type="button" id="loginBtn" disabled aria-disabled="true" aria-describedby="loginTooltip" onclick="handleLogin()">
            <!-- Lock SVG -->
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="vertical-align:middle;">
                <path d="M12 17a2 2 0 110-4 2 2 0 010 4zm6-7h-1V7a5 5 0 00-10 0v3H6a2 2 0 00-2 2v7a2 2 0 002 2h12a2 2 0 002-2v-7a2 2 0 00-2-2zm-7-3a3 3 0 116 0v3H11V7z"/>
            </svg>
            <span style="margin-left:6px;vertical-align:middle;">Login</span>
            <!-- Stop SVG hidden by default, shown on hover via wrapper -->
            <svg class="stop-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M6 6h12v12H6z"/>
            </svg>
        </button>
        <div class="btn-tooltip" role="tooltip" id="loginTooltip">Capture a photo first to enable Login</div>
    </span>

    <button type="button" onclick="resetCapture()" style="background: #6c757d; margin-left: 10px;" aria-label="Reset"> 
        <!-- Reset/refresh SVG -->
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="vertical-align:middle;">
            <path d="M12 6V4l4 4-4 4V8c-3.31 0-6 2.69-6 6 0 1.31.42 2.52 1.13 3.5l-1.46 1.46C5.16 18.03 4 16.14 4 14c0-4.42 3.58-8 8-8zM18.87 6.5L20.33 5.04C18.84 3.24 15.81 2 12 2 7.58 2 4 5.58 4 10c0 1.86.68 3.57 1.81 4.9L7 13.71C6.44 12.95 6 11.99 6 11c0-3.31 2.69-6 6-6 2.76 0 5.08 1.78 5.87 4.5z"/>
        </svg>
        <span style="margin-left:6px;vertical-align:middle;">Reset</span>
    </button>
</form>

<div id="statusMessage"></div>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $image = $_POST['image'] ?? null;
    $check_glasses = $_POST['check_glasses'] ?? 'false';
    $ajax_check = $_POST['ajax_check'] ?? 'false';

    if ($image) {
        // STEP 1: If this is an initial capture, do a quick face-presence check
        // The Python API exposes /check_angle which validates that a face exists.
        if ($check_glasses === 'true') {
            $api_url = PYTHON_API_BASE . "/check_angle";
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
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($result === false) {
                // Inform the client JS to reset camera and show message
                $client_message = 'Error checking face presence: ' . ($curl_error ?: 'unknown');
                echo "<script>window.serverCaptureReset = " . json_encode([
                    'message' => $client_message,
                    'disableLogin' => true
                ]) . ";</script>";
                // Also render a simple notice for users without relying solely on JS
                echo "<div id='faceCheckError'><p style='color:red;'>❌ Error checking face presence.</p><p style='font-size:12px; color:#666;'><em>Please try capturing your face again.</em></p></div>";
                return; // Don't proceed to recognition, but don't exit the page
            }

            $response = json_decode($result, true);

            if ($response === null) {
                $client_message = 'Invalid JSON response from face-check API';
                echo "<script>window.serverCaptureReset = " . json_encode([
                    'message' => $client_message,
                    'disableLogin' => true
                ]) . ";</script>";
                echo "<div id='faceCheckError'><p style='color:red;'>❌ Invalid JSON response from face-check API</p><p style='font-size:12px; color:#666;'><em>Please try capturing your face again.</em></p></div>";
                return; // Don't proceed to recognition
            }

            // Expected successful response: { "status": "ok" }
            if (isset($response['status']) && $response['status'] === 'ok') {
                // Face present — proceed to recognition
            } else {
                // Show an informative error to the user and instruct client to reset
                $msg = $response['message'] ?? 'No face detected. Please ensure your face is centered and try again.';
                echo "<script>window.serverCaptureReset = " . json_encode([
                    'message' => $msg,
                    'disableLogin' => true
                ]) . ";</script>";
                echo "<div id='faceCheckError'><p style='color:red;'>❌ " . htmlspecialchars($msg) . "</p><p style='font-size:12px; color:#666;'><em>Make sure your face is clearly visible and try capturing again.</em></p></div>";
                return; // Don't proceed to recognition
            }
        }

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

        // NEW / CHANGED:
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);  // time to establish connection
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);        // total time allowed for the request

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
                        // Normal form submission - proceed with full login flow
                        $dispName = htmlspecialchars($response['name']);
                        echo "<h3 style='color:green;'>Welcome, " . $dispName . "!</h3>";
                        // Ask the browser to create a session via backend and redirect to kiosk
                        $name_json = isset($response['name']) ? json_encode($response['name']) : 'null';
                        $safe_json = isset($response['safe_name']) ? json_encode($response['safe_name']) : 'null';
                        echo "<script>
                            (async function(){
                                try {
                                    const payload = {};
                                    if ($safe_json !== null) payload.safe_name = $safe_json; else payload.name = $name_json;
                                    const resp = await fetch('../backend/face_login.php', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json' },
                                        body: JSON.stringify(payload)
                                    });
                                    const j = await resp.json();
                                    if (j && j.status === 'ok') {
                                        window.location.href = '../includes/kioskMenu.php';
                                    } else {
                                        alert('Login failed: ' + (j && j.message ? j.message : 'unknown'));
                                    }
                                } catch (e) {
                                    alert('Login request failed: ' + e);
                                }
                            })();
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
