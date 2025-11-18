<?php
// Include configuration for Python API base URL
require_once __DIR__ . '/../config.php';

// Note: No longer starting Python process from PHP.
// In development, run: python linya_python_api/app.py
// In production, Python API is deployed to Google Cloud Run.
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Face Registration</title>
<style>
    body { 
        font-family: Arial; 
        text-align: center; 
        margin-top: 40px;
        background: #f0f0f0;
    }
    .camera-container {
        position: relative;
        width: 480px;
        height: 360px;
        margin: 0 auto;
        overflow: hidden;
    }
    video, canvas { 
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: 2px solid #555; 
        border-radius: 10px;
        background: #fff;
        object-fit: cover;
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
        z-index: 10;
    }
    #progress { 
        font-size: 18px; 
        margin-top: 15px; 
    }
    .form-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 20px;
    }
    button {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        background: #007bff;
        color: white;
        cursor: pointer;
        font-size: 16px;
    }
    button:hover {
        background: #0056b3;
    }
    button[disabled] {
        background: #999;
        cursor: not-allowed;
    }
    .small-muted {
        color: #666;
        font-size: 13px;
    }
</style>
</head>
<body>

<h1>Register Your Face</h1>

<div class="camera-container">
    <div class="instruction-panel" style="
        background: #e9ecef;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 8px;
        border: 2px solid #007bff;
    ">
        <h3 id="currentAngle" style="color: #007bff; margin: 0 0 10px 0;">Front View</h3>
        <p id="angleInstruction" style="margin: 0; color: #495057;">Please look straight at the camera for a clear front view of your face.</p>
    </div>
    <video id="video" autoplay playsinline></video>
    <canvas id="canvas" style="display:none;"></canvas>
    <div id="faceOutline" aria-hidden="true"></div>
</div>

<div class="capture-guide" style="max-width:480px;margin:12px auto 0; text-align:left;">
    <div style="background:#fff;border:1px solid #ddd;padding:12px;border-radius:8px;">
        <h4 style="margin:0 0 8px 0;color:#007bff;">
            <span id="guideCounter" style="color:#999;">1 / 5</span> - <span id="guideTitle">Front View</span>
        </h4>
        <p id="guideTip" style="margin:0;color:#333;line-height:1.6;"></p>
        <p class="small-muted" style="margin:8px 0 0 0;">Tip: Remove hats and glasses; ensure good lighting and hold still when capturing.</p>
    </div>
</div>

<div class="form-container" style="margin-top: 20px;">
    <div class="input-group" style="margin-bottom: 15px;">
     <input type="text" 
         id="nameInput" 
         placeholder="Enter your name" 
         required 
         style="padding: 8px 15px; 
             border: 1px solid #ccc; 
             border-radius: 5px; 
             font-size: 16px; 
             width: 250px;">
     <div style="height:8px"></div>
     <input type="email" id="emailInput" placeholder="Email (optional)" style="padding: 8px 15px; border:1px solid #ccc; border-radius:5px; width:250px; margin-top:6px;" />
     <div style="height:8px"></div>
     <input type="tel" id="phoneInput" placeholder="Phone (optional)" style="padding: 8px 15px; border:1px solid #ccc; border-radius:5px; width:250px; margin-top:6px;" />
    </div>
    <button type="button" id="captureBtn" onclick="captureImage()" style="margin-bottom: 15px;">📸 Capture Face</button>
    <div class="small-muted">Capture 5 angles. The app will submit automatically when done.</div>
</div>

<p id="progress">Images captured: <span id="count">0</span> / 5</p>
<div id="statusMessage" role="status" aria-live="polite"></div>

<script>
/* =========== Config / state =========== */
// API base URL injected from PHP config (now using Cloud Run in production)
const apiBase = <?= json_encode(PYTHON_API_BASE) ?>;

const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const context = canvas.getContext('2d');
const countDisplay = document.getElementById('count');
const statusMessage = document.getElementById('statusMessage');
const currentAngleElement = document.getElementById('currentAngle');
const angleInstructionElement = document.getElementById('angleInstruction');
const captureBtn = document.getElementById('captureBtn');
let capturedImages = [];
const maxImages = 5;
let currentAngleIndex = 0;
let submitting = false; // prevents double submits

const angles = [
    { 
        name: "Front View", 
        instruction: "Please look straight at the camera for a clear front view of your face.",
        guideTip: "Look straight ahead, keep your face centered and well-lit. This is your primary reference image."
    },
    { 
        name: "Left Angle", 
        instruction: "Turn your head slightly to the left side (about 45 degrees).",
        guideTip: "Turn your head ~45° to the left while keeping your eyes toward the camera. This captures the left profile."
    },
    { 
        name: "Right Angle", 
        instruction: "Turn your head slightly to the right side (about 45 degrees).",
        guideTip: "Turn your head ~45° to the right while keeping your eyes toward the camera. This captures the right profile."
    },
    { 
        name: "Upper Angle", 
        instruction: "Tilt your head slightly upward while looking at the camera.",
        guideTip: "Tilt your chin up slightly while looking at the camera. This helps with recognition from above."
    },
    { 
        name: "Lower Angle", 
        instruction: "Tilt your head slightly downward while looking at the camera.",
        guideTip: "Tilt your chin down slightly while looking at the camera. This helps with recognition from below."
    }
];

/* =========== Camera init =========== */
navigator.mediaDevices.getUserMedia({ video: { width: 1280, height: 720, facingMode: "user" } })
    .then(stream => {
        video.srcObject = stream;
        video.addEventListener('loadedmetadata', () => {
            // set canvas to real video resolution
            canvas.width = video.videoWidth || 1280;
            canvas.height = video.videoHeight || 720;
        });
    })
    .catch(err => {
        console.error("getUserMedia error:", err);
        alert("Error accessing webcam: " + (err && err.message ? err.message : err));
    });

/* =========== Helpers =========== */
function updateAngleInstructions() {
    if (currentAngleIndex < angles.length) {
        currentAngleElement.textContent = angles[currentAngleIndex].name;
        angleInstructionElement.textContent = angles[currentAngleIndex].instruction;
        // Update the dynamic guide card
        document.getElementById('guideCounter').textContent = `${currentAngleIndex + 1} / ${angles.length}`;
        document.getElementById('guideTitle').textContent = angles[currentAngleIndex].name;
        document.getElementById('guideTip').textContent = angles[currentAngleIndex].guideTip;
    } else {
        currentAngleElement.textContent = "Done";
        angleInstructionElement.textContent = "Submitting images...";
        document.getElementById('guideCounter').textContent = "✅";
        document.getElementById('guideTitle').textContent = "Complete";
        document.getElementById('guideTip').textContent = "Your registration is being processed. Thank you!";
    }
}

function resetCaptureState() {
    capturedImages = [];
    countDisplay.textContent = "0";
    currentAngleIndex = 0;
    updateAngleInstructions();
    statusMessage.innerHTML = '';
    captureBtn.disabled = false;
}

/* =========== Capture logic =========== */
function captureImage() {
    const name = document.getElementById('nameInput').value.trim();
    if (!name) {
        alert("Please enter your name before capturing images.");
        return;
    }
    if (capturedImages.length >= maxImages) {
        alert("You've already captured all required angles.");
        return;
    }

    // Draw visible preview (not used for upload except for feedback)
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    // Offscreen canvas to create upload-friendly size to avoid huge payloads
    const maxUploadWidth = 1280; // keep reasonably high for detection accuracy
    const aspect = canvas.width && canvas.height ? canvas.width / canvas.height : (1280 / 720);
    const uploadWidth = Math.min(maxUploadWidth, canvas.width || maxUploadWidth);
    const uploadHeight = Math.round(uploadWidth / aspect);
    const tmpCanvas = document.createElement('canvas');
    tmpCanvas.width = uploadWidth;
    tmpCanvas.height = uploadHeight;
    const tmpCtx = tmpCanvas.getContext('2d');
    tmpCtx.imageSmoothingEnabled = true;
    tmpCtx.drawImage(video, 0, 0, tmpCanvas.width, tmpCanvas.height);

    // High JPEG quality to give Python enough detail
    const dataURL = tmpCanvas.toDataURL('image/jpeg', 0.85);

    const currentAngle = angles[currentAngleIndex].name;
    capturedImages.push(dataURL);
    countDisplay.textContent = capturedImages.length;

    // small visual feedback
    statusMessage.innerHTML = `<p style="color:green;">✅ Captured: <strong>${currentAngle}</strong></p>`;

    currentAngleIndex++;
    updateAngleInstructions();

    if (capturedImages.length === maxImages) {
        // submit after short delay so UI can update
        setTimeout(() => {
            submitRegistration();
        }, 300);
    }
}

/* =========== Submit registration =========== */
async function submitRegistration(retryCount = 0) {
    if (submitting) return;
    submitting = true;
    captureBtn.disabled = true;

    const name = document.getElementById('nameInput').value.trim();
    if (!name) {
        statusMessage.innerHTML = `<p style='color:red;'>Please enter your name.</p>`;
        submitting = false;
        captureBtn.disabled = false;
        return;
    }
    if (capturedImages.length < maxImages) {
        statusMessage.innerHTML = `<p style='color:orange;'>Please capture all ${maxImages} angles before submitting.</p>`;
        submitting = false;
        captureBtn.disabled = false;
        return;
    }

    statusMessage.innerHTML = `<p>Submitting images... <span class="small-muted">Please wait.</span></p>`;

    try {
        const formData = new FormData();
        formData.append("name", name);
        capturedImages.forEach(img => formData.append("images[]", img));

        const resp = await fetch(`${apiBase}/register`, {
            method: "POST",
            body: formData,
            mode: 'cors' // leave CORS mode; Flask configured for dev origins
        });

        // Try to parse JSON regardless of status; server returns JSON for errors as well
        const respText = await resp.text();
        let json;
        try {
            json = JSON.parse(respText || '{}');
        } catch (e) {
            // Non-JSON response
            throw new Error(`Invalid server response: ${resp.status} ${resp.statusText}`);
        }

        if (!resp.ok) {
            // Handle well-formed JSON errors (e.g., already_registered, glasses)
                if (json && json.status === 'already_registered') {
                statusMessage.innerHTML = `
                    <div style="text-align:center;">
                        <p style='color:orange;'>⚠️ ${json.message}</p>
                        <p>Registered as: <strong>${json.existing_name || 'Unknown'}</strong></p>
                        <a href="kioskLogin.php">
                            <button style="padding:10px 20px; border:none; background:#007bff; color:white; border-radius:5px; cursor:pointer; margin-top:10px;">
                                Go to Login
                            </button>
                        </a>
                    </div>
                `;
                // Reset local captured images
                capturedImages = [];
                countDisplay.textContent = "0";
                currentAngleIndex = 0;
                updateAngleInstructions();
                submitting = false;
                captureBtn.disabled = false;
                return;
            }

            if (json && json.status === 'glasses') {
                statusMessage.innerHTML = `
                    <div style="text-align:center;">
                        <p style='color:orange;'>⚠️ ${json.message}</p>
                        <p>Please remove your ${json.type} and retake the Front View.</p>
                    </div>
                `;
                // Reset to let user recapture
                capturedImages = [];
                countDisplay.textContent = "0";
                currentAngleIndex = 0;
                updateAngleInstructions();
                submitting = false;
                captureBtn.disabled = false;
                return;
            }

            // Other server-side error
            statusMessage.innerHTML = `<p style='color:red;'>❌ Server error: ${json.message || resp.statusText}</p>`;
            submitting = false;
            captureBtn.disabled = false;
            return;
        }

             
            // Success flow
        if (json.status === 'success') {
            // attempt to save client record in the PHP clients table
            const email = document.getElementById('emailInput').value.trim();
            const phone = document.getElementById('phoneInput').value.trim();
            const folder = json.folder || '';
            const encoding = json.encoding || '';

            try {
                const fd = new FormData();
                fd.append('name', name);
                fd.append('email', email);
                fd.append('phone', phone);
                fd.append('folder', folder);
                fd.append('encoding', encoding);

                // fire-and-wait to backend PHP that will store the encoding blob into DB
                const r = await fetch('../backend/face_register_save.php', { method: 'POST', body: fd });

                // Read the response text first, to avoid JSON parsing errors
                const respText = await r.text();
                let jr = null;

                if (respText) {
                    try {
                        jr = JSON.parse(respText);
                    } catch (parseErr) {
                        throw new Error(`Non-JSON response from face_register_save.php: ${respText}`);
                    }
                } else {
                    throw new Error('Empty response from face_register_save.php');
                }

                if (jr && jr.status === 'ok') {
                    statusMessage.innerHTML = `
                        <div style="text-align:center;">
                            <p style='color:green;'>✅ ${json.message} (saved to clients)</p>
                            <p>You can now log in using face recognition.</p>
                            <a href="kioskLogin.php">
                                <button style="padding:10px 20px; border:none; background:#007bff; color:white; border-radius:5px; cursor:pointer; margin-top:10px;">
                                    Go to Login
                                </button>
                            </a>
                        </div>
                    `;
                } else if (jr && jr.status === 'already_registered') {
                    statusMessage.innerHTML = `
                        <div style="text-align:center;">
                            <p style='color:orange;'>⚠️ ${jr.message}</p>
                            <p>Existing record: <strong>${jr.existing_name || 'Unknown'}</strong></p>
                            <p style='color:gray;'>If this is you, proceed to <a href="kioskLogin.php">Login</a>. Otherwise change the name/phone/email and try again.</p>
                        </div>
                    `;
                } else {
                    statusMessage.innerHTML = `
                        <div style="text-align:center;">
                            <p style='color:orange;'>⚠️ ${json.message} (registered on file system)</p>
                            <p style='color:gray;'>Could not save to DB: ${jr && jr.message ? jr.message : 'unknown'}</p>
                            <a href="kioskLogin.php">
                                <button style="padding:10px 20px; border:none; background:#007bff; color:white; border-radius:5px; cursor:pointer; margin-top:10px;">
                                    Go to Login
                                </button>
                            </a>
                        </div>
                    `;
                }
            } catch (e) {
                statusMessage.innerHTML = `<p style='color:orange;'>Registered on file system but failed to save DB: ${e.message || e}</p>`;
            }

            // clear local state
            capturedImages = [];
            countDisplay.textContent = "0";
            currentAngleIndex = 0;
            updateAngleInstructions();
            submitting = false;
            captureBtn.disabled = false;
            return;
        }


        // Unexpected but valid JSON
        statusMessage.innerHTML = `<p style='color:orange;'>Unexpected response: ${JSON.stringify(json)}</p>`;
        submitting = false;
        captureBtn.disabled = false;

    } catch (err) {
        console.error("Registration submit error:", err);

        // Basic retry/backoff: retry once after 1s for transient network errors
        if (retryCount < 1) {
            statusMessage.innerHTML = `<p style='color:orange;'>Network error. Retrying...</p>`;
            setTimeout(() => submitRegistration(retryCount + 1), 1000);
            submitting = false; // allow the retry to set it again
            return;
        }

        statusMessage.innerHTML = `<p style='color:red;'>❌ Connection error: ${err.message || err}</p>`;
        submitting = false;
        captureBtn.disabled = false;
    }
}

/* =========== Init UI =========== */
updateAngleInstructions();
resetCaptureState();
</script>

</body>
</html>
