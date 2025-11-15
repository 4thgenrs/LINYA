
<?php
require_once 'automatic_run_api.php';

$faceAPI = new automatic_run_api();
$faceAPI->start(); // This automatically runs main.py (the new Flask entry point) if not running yet
?>
<!DOCTYPE html>
<html>
<head>
    <title>Face Login</title>
    <style>
        body {
            font-family: Arial;
            text-align: center;
            background: #f0f0f0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .camera-container {
            position: relative;
            width: 480px;
            height: 360px;
            margin-top: 20px;
            overflow: hidden; /* Prevent overflow from HD feed */
        }

        video, canvas {
             position: absolute;
                top: 0;
                left: 0;
                width: 100%;   /* Scale down to fit container width */
                height: 100%;  /* Scale down proportionally */
                border: 2px solid #333;
                border-radius: 10px;
                object-fit: cover; /* Ensures full coverage without distortion */
            
        }

        button {
            margin-top: 15px;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background: #007bff;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        #faceOutline {
            position: absolute;
            top: 30px;
            left: 140px;
            width: 200px;
            height: 300px;
            border: 3px solid #00ff00;
            border-radius: 50%;
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.5);
            pointer-events: none;
        }
        /* Disabled login button styling (match Reset look and show stop icon) */
        #loginBtn[disabled] {
            background: #6c757d;
            cursor: not-allowed;
            color: #fff;
        }
        /* Wrapper for buttons to show tooltip even when the inner button is disabled */
        .btn-wrapper { display: inline-block; position: relative; margin: 0 6px; }
        .btn-wrapper .btn-tooltip {
            position: absolute; bottom: 125%; left: 50%; transform: translateX(-50%) translateY(0);
            background: rgba(0,0,0,0.85); color: #fff; padding: 6px 8px; border-radius: 4px; font-size: 12px;
            white-space: nowrap; opacity: 0; pointer-events: none; transition: opacity .12s ease, transform .12s ease;
            z-index: 40;
        }
        .btn-wrapper.disabled:hover .btn-tooltip { opacity: 1; transform: translateX(-50%) translateY(-6px); }

        /* Stop icon appears only on hover when disabled (keeps UI clean) */
        .stop-icon { display: none; width: 16px; height: 16px; vertical-align: middle; margin-left: 6px; }
        .btn-wrapper.disabled:hover button[disabled] .stop-icon { display: inline-block; }
    </style>
</head>
<body>

<h1>Face Recognition Login</h1>

<div class="camera-container">
    <video id="video" autoplay></video>
    <canvas id="canvas" style="display:none;"></canvas>
    <div id="faceOutline"></div>
</div>

<form method="POST" action="login.php" id="loginForm">
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
        <button type="submit" id="loginBtn" disabled aria-disabled="true" aria-describedby="loginTooltip">
            <!-- Lock SVG -->
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="vertical-align:middle;">
                <path d="M12 17a2 2 0 110-4 2 2 0 010 4zm6-7h-1V7a5 5 0 00-10 0v3H6a2 2 0 00-2 2v7a2 2 0 002 2h12a2 2 0 002-2v-7a2 2 0 00-2-2zm-7-3a3 3 0 016 0v3H11V7z"/>
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

    if ($image) {
        // STEP 1: If this is an initial capture, check for glasses FIRST
        if ($check_glasses === 'true') {
            $api_url = "http://127.0.0.1:5000/debug_glasses";
            $payload = json_encode([
                "image" => str_replace("data:image/jpeg;base64,", "", $image)
            ]);

            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $result = curl_exec($ch);
            $curl_error = curl_error($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($result === false) {
                echo "<p style='color:red;'>❌ Error checking accessories: $curl_error</p>";
                exit;
            }
            
            $response = json_decode($result, true);
            
            if ($response === null) {
                echo "<p style='color:red;'>❌ Invalid JSON response from glasses API</p>";
                echo "<p style='color:red; font-size:12px;'>Raw: " . htmlspecialchars($result) . "</p>";
                exit;
            }
            
            // Check if the endpoint returned an error (e.g., no face detected)
            if (isset($response['status']) && $response['status'] === 'error') {
                echo "<p style='color:red;'>❌ Could not detect face: " . htmlspecialchars($response['message'] ?? 'Unknown error') . "</p>";
                echo "<p style='font-size:12px; color:#666;'><em>Make sure your face is clearly visible in the center and try again.</em></p>";
                exit;
            }
            
            // Check for glasses
            if (isset($response['glasses_type'])) {
                if ($response['glasses_type'] !== 'none') {
                    $accessoryType = htmlspecialchars($response['glasses_type']);
                    echo "<p style='color:orange; font-weight:bold;'>⚠️ " . ucfirst($accessoryType) . " detected!</p>";
                    echo "<p>Please remove your $accessoryType and try again.</p>";
                    echo "<p style='font-size:12px; color:#666;'><em>Reset the page and capture again without accessories.</em></p>";
                    exit; // IMPORTANT: Stop here and do NOT proceed to recognition
                }
                // No glasses, proceed to recognition
            } else {
                echo "<p style='color:red;'>❌ Unexpected response: missing glasses_type field</p>";
                echo "<p style='color:red; font-size:12px;'>Response: " . htmlspecialchars(json_encode($response)) . "</p>";
                exit;
            }
        }

        // STEP 2: Proceed with recognition (only if no glasses or user is retrying)
        $api_url = "http://127.0.0.1:5000/recognize";
        $payload = json_encode([
            "image" => str_replace("data:image/jpeg;base64,", "", $image)
        ]);

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $result = curl_exec($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);

        echo "<div>";

        // Check if the API returned anything
        if ($result === false) {
            echo "<p style='color:red;'>Error: Could not connect to Python API. $curl_error</p>";
        } else {
            $response = json_decode($result, true);

            // Check if JSON decoding failed
            if ($response === null) {
                echo "<p style='color:red;'>Error: Invalid response from API.<br>Response was:<br><pre>"
                    . htmlspecialchars($result) . "</pre></p>";
            } elseif (isset($response['status'])) {
                // Then show the recognition result
                    if ($response['status'] === 'recognized') {
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
                    } elseif ($response['status'] === 'unknown') {
                    echo "<p style='color:orange;'>Unknown user. <a href='register.php'>Register here</a>.</p>";
                } elseif ($response['status'] === 'accessory_detected') {
                    $accessory = htmlspecialchars($response['message']);
                    echo "<p style='color:red; font-weight:bold;'>⚠️ $accessory</p>";
                } elseif ($response['status'] === 'no_person_found') {
                    echo "<p style='color:red;'>No person detected. Please try again.</p>";
                } else {
                    echo "<p style='color:red;'>Error: " . htmlspecialchars($response['message'] ?? 'Unknown error') . "</p>";
                }
            } else {
                echo "<p style='color:red;'>Error: Unexpected response structure.<br><pre>"
                    . htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT)) . "</pre></p>";
            }
        }

        echo "</div>";
    }
}
?>

<script>
// Compute API base URL dynamically to avoid calling client-localhost by mistake
const _apiHost = window.location.hostname;
const apiBase = (_apiHost === 'localhost' || _apiHost === '127.0.0.1')
    ? 'http://127.0.0.1:5000'
    : `${window.location.protocol}//${_apiHost}:5000`;

const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const context = canvas.getContext('2d');
const loginBtn = document.getElementById('loginBtn');

// Start webcam (HD capture, scaled down visually)
navigator.mediaDevices.getUserMedia({ 
    video: { 
        width: { ideal: 1280 },
        height: { ideal: 720 },
        facingMode: 'user'
    },
    audio: false
})
    .then(stream => {
        video.srcObject = stream;

        // Ensure canvas dimensions match actual video feed
        video.addEventListener('loadedmetadata', () => {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            console.log(`Video dimensions set: ${canvas.width}x${canvas.height}`);
        });
    })
    .catch(err => alert("Error accessing webcam: " + err.name + " - " + err.message));

function captureImage() {
    // Ensure canvas has the correct dimensions
    if (canvas.width === 0 || canvas.height === 0) {
        canvas.width = video.videoWidth || 1280;
        canvas.height = video.videoHeight || 720;
    }
    
    context.imageSmoothingEnabled = false;

    // Draw the current video frame to the visible canvas for preview
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    // Create an offscreen canvas for high-quality upload image
    // Use higher resolution (up to 1280px) and better JPEG quality (0.85)
    const maxUploadWidth = 1280;  // Increased from 800 for better face detection
    const aspect = canvas.width && canvas.height ? canvas.width / canvas.height : (1280 / 720);
    const uploadWidth = Math.min(maxUploadWidth, canvas.width || maxUploadWidth);
    const uploadHeight = Math.round(uploadWidth / aspect);
    const tmpCanvas = document.createElement('canvas');
    tmpCanvas.width = uploadWidth;
    tmpCanvas.height = uploadHeight;
    const tmpCtx = tmpCanvas.getContext('2d');
    tmpCtx.imageSmoothingEnabled = true;
    tmpCtx.drawImage(video, 0, 0, tmpCanvas.width, tmpCanvas.height);

    // Use higher JPEG quality (0.85 instead of 0.7) for better face detection accuracy
    const dataURL = tmpCanvas.toDataURL('image/jpeg', 0.85);
    document.getElementById('imageInput').value = dataURL;

    // Enable the Login button once an image is captured
    if (loginBtn) {
           loginBtn.disabled = false;
           // Remove disabled wrapper styling so tooltip won't show and ARIA updates
           if (loginWrapper) loginWrapper.classList.remove('disabled');
           loginBtn.setAttribute('aria-disabled', 'false');
    }

    video.style.display = 'none';
    canvas.style.display = 'block';

    alert("✅ Face captured! Checking for glasses... then click 'Login' to authenticate.");
}

function resetCapture() {
    // Clear the form
    document.getElementById('imageInput').value = '';
    document.getElementById('checkGlasses').value = 'true';
    
    // Show video again, hide canvas
    video.style.display = 'block';
    canvas.style.display = 'none';
    
    // Clear status message
    document.getElementById('statusMessage').innerHTML = '';
    
    alert("✅ Reset! You can now capture again.");

    // Disable the Login button again since there's no image
    if (loginBtn) {
        loginBtn.disabled = true;
    
        // Re-enable wrapper disabled styling for tooltip and update ARIA
        if (loginWrapper) loginWrapper.classList.add('disabled');
        loginBtn.setAttribute('aria-disabled', 'true');
    }
}
</script>

</body>
</html>
