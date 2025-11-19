/* =========== Config / state =========== */
let apiBase = '';
let capturedImages = [];
const maxImages = 5;
let currentAngleIndex = 0;
let submitting = false;

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

/* =========== Visitor Form Validation =========== */
function isValidPhone(p) {
    return typeof p === 'string' && /^\s*09\d{9}\s*$/.test(p);
}

function isValidEmail(e) {
    if (!e) return false;
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e);
}

function visitorSyncState() {
    const nameEl = document.getElementById('visitorName');
    const phoneEl = document.getElementById('visitorPhone');
    const emailEl = document.getElementById('visitorEmail');
    const proceedBtn = document.getElementById('visitorProceed');

    const name = nameEl ? nameEl.value.trim() : '';
    const phone = phoneEl ? phoneEl.value.trim() : '';
    const email = emailEl ? emailEl.value.trim() : '';

    const phoneOk = isValidPhone(phone);
    const emailOk = isValidEmail(email);

    // Show inline hints
    const phoneHint = document.getElementById('visitorPhoneHint');
    if (phoneHint) phoneHint.style.display = phone === '' ? 'none' : (phoneOk ? 'none' : 'block');

    const emailHint = document.getElementById('visitorEmailHint');
    if (emailHint) emailHint.style.display = email === '' ? 'none' : (emailOk ? 'none' : 'block');

    proceedBtn.disabled = !(name.length > 0 && phoneOk && emailOk);
}

function openVisitorConfirm() {
    const name = document.getElementById('visitorName').value.trim();
    const phone = document.getElementById('visitorPhone').value.trim();
    const email = document.getElementById('visitorEmail').value.trim();

    document.getElementById('confirmName').textContent = name || '-';
    document.getElementById('confirmPhone').textContent = phone || '-';
    document.getElementById('confirmEmail').textContent = email || '-';

    const modal = document.getElementById('visitorConfirmModal');
    if (modal) modal.style.display = 'flex';
}

function closeVisitorConfirm() {
    const modal = document.getElementById('visitorConfirmModal');
    if (modal) modal.style.display = 'none';
}

function acceptVisitorAndProceed() {
    closeVisitorConfirm();

    // Hide visitor form and show camera screen
    const visitorForm = document.getElementById('visitorForm');
    const cameraScreen = document.getElementById('cameraScreen');
    
    if (visitorForm) visitorForm.style.display = 'none';
    if (cameraScreen) {
        cameraScreen.style.display = 'block';
        initializeCamera();
    }
}

/* =========== Camera Functions =========== */
function initializeCamera() {
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    
    console.log('Initializing camera...');
    
    if (!video || !canvas) {
        console.error('Video or canvas element not found');
        return;
    }
    
    navigator.mediaDevices.getUserMedia({ 
        video: { width: 1280, height: 720, facingMode: "user" } 
    })
    .then(stream => {
        console.log('Camera stream obtained');
        video.srcObject = stream;
        video.addEventListener('loadedmetadata', () => {
            canvas.width = video.videoWidth || 1280;
            canvas.height = video.videoHeight || 720;
            console.log('Camera initialized with resolution:', canvas.width, 'x', canvas.height);
        });
    })
    .catch(err => {
        console.error("getUserMedia error:", err);
        alert("Error accessing webcam: " + (err && err.message ? err.message : err));
    });
}

function updateAngleInstructions() {
    const currentAngleElement = document.getElementById('currentAngle');
    const angleInstructionElement = document.getElementById('angleInstruction');
    
    if (currentAngleIndex < angles.length) {
        currentAngleElement.textContent = angles[currentAngleIndex].name;
        angleInstructionElement.textContent = angles[currentAngleIndex].instruction;
        
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
    const countDisplay = document.getElementById('count');
    if (countDisplay) countDisplay.textContent = "0";
    currentAngleIndex = 0;
    updateAngleInstructions();
    const statusMessage = document.getElementById('statusMessage');
    if (statusMessage) statusMessage.innerHTML = '';
    const captureBtn = document.getElementById('captureBtn');
    if (captureBtn) captureBtn.disabled = false;
}

function captureImage() {
    if (capturedImages.length >= maxImages) {
        alert("You've already captured all required angles.");
        return;
    }

    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const context = canvas.getContext('2d');

    // Draw visible preview
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    // Create offscreen canvas for upload
    const maxUploadWidth = 1280;
    const aspect = canvas.width && canvas.height ? canvas.width / canvas.height : (1280 / 720);
    const uploadWidth = Math.min(maxUploadWidth, canvas.width || maxUploadWidth);
    const uploadHeight = Math.round(uploadWidth / aspect);
    const tmpCanvas = document.createElement('canvas');
    tmpCanvas.width = uploadWidth;
    tmpCanvas.height = uploadHeight;
    const tmpCtx = tmpCanvas.getContext('2d');
    tmpCtx.imageSmoothingEnabled = true;
    tmpCtx.drawImage(video, 0, 0, tmpCanvas.width, tmpCanvas.height);

    const dataURL = tmpCanvas.toDataURL('image/jpeg', 0.85);
    const currentAngle = angles[currentAngleIndex].name;
    capturedImages.push(dataURL);
    
    const countDisplay = document.getElementById('count');
    if (countDisplay) countDisplay.textContent = capturedImages.length;

    const statusMessage = document.getElementById('statusMessage');
    if (statusMessage) {
        statusMessage.innerHTML = `<p style="color:green;">✅ Captured: <strong>${currentAngle}</strong></p>`;
    }

    currentAngleIndex++;
    updateAngleInstructions();

    if (capturedImages.length === maxImages) {
        setTimeout(() => {
            submitRegistration();
        }, 300);
    }
}

async function submitRegistration(retryCount = 0) {
    if (submitting) return;
    submitting = true;
    
    const captureBtn = document.getElementById('captureBtn');
    if (captureBtn) captureBtn.disabled = true;

    if (capturedImages.length < maxImages) {
        const statusMessage = document.getElementById('statusMessage');
        if (statusMessage) {
            statusMessage.innerHTML = `<p style='color:orange;'>Please capture all ${maxImages} angles before submitting.</p>`;
        }
        submitting = false;
        if (captureBtn) captureBtn.disabled = false;
        return;
    }

    const statusMessage = document.getElementById('statusMessage');
    if (statusMessage) {
        statusMessage.innerHTML = `<p>Submitting images... <span class="small-muted">Please wait.</span></p>`;
    }

    try {
        const name = document.getElementById('visitorName').value.trim();
        const email = document.getElementById('visitorEmail').value.trim();
        const phone = document.getElementById('visitorPhone').value.trim();
        
        console.log('Submitting registration:', { name, email, phone, imageCount: capturedImages.length, apiBase });
        
        const formData = new FormData();
        formData.append("name", name);
        formData.append("email", email);
        formData.append("phone", phone);
        capturedImages.forEach(img => formData.append("images[]", img));

        const resp = await fetch(`${apiBase}/register`, {
            method: "POST",
            body: formData,
            mode: 'cors'
        });

        console.log('API Response status:', resp.status, resp.statusText);
        
        const respText = await resp.text();
        console.log('API Response text:', respText);
        
        let json;
        try {
            json = JSON.parse(respText || '{}');
            console.log('Parsed JSON:', json);
        } catch (e) {
            console.error('JSON parse error:', e);
            throw new Error(`Invalid server response: ${resp.status} ${resp.statusText}`);
        }

        if (!resp.ok) {
            console.log('Response not OK, checking error conditions...');
            
            if (json && json.status === 'already_registered') {
                console.log('Already registered detected:', json);
                if (statusMessage) {
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
                }
                resetCaptureState();
                submitting = false;
                if (captureBtn) captureBtn.disabled = false;
                return;
            }

            if (json && json.status === 'glasses') {
                console.log('Glasses detected:', json);
                if (statusMessage) {
                    statusMessage.innerHTML = `
                        <div style="text-align:center;">
                            <p style='color:orange;'>⚠️ ${json.message}</p>
                            <p>Please remove your ${json.type} and retake the Front View.</p>
                        </div>
                    `;
                }
                resetCaptureState();
                submitting = false;
                if (captureBtn) captureBtn.disabled = false;
                return;
            }

            console.log('Other server error:', json);
            if (statusMessage) {
                statusMessage.innerHTML = `<p style='color:red;'>❌ Server error: ${json.message || resp.statusText}</p>`;
            }
            submitting = false;
            if (captureBtn) captureBtn.disabled = false;
            return;
        }

        // Success flow
        if (json.status === 'success') {
            const folder = json.folder || '';
            const encoding = json.encoding || '';

            try {
                const fd = new FormData();
                fd.append('name', name);
                fd.append('email', email);
                fd.append('phone', phone);
                fd.append('folder', folder);
                fd.append('encoding', encoding);

                const r = await fetch('../backend/face_register_save.php', { method: 'POST', body: fd });
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
                    if (statusMessage) {
                        statusMessage.innerHTML = `
                            <div style="text-align:center;">
                                <p style='color:green;'>✅ ${json.message} (saved to clients)</p>
                                <p>You can now log in using face recognition.</p>
                                <a href="../kiosk/kioskLogin.php">
                                    <button style="padding:10px 20px; border:none; background:#007bff; color:white; border-radius:5px; cursor:pointer; margin-top:10px;">
                                        Go to Login
                                    </button>
                                </a>
                            </div>
                        `;
                    }
                } else if (jr && jr.status === 'already_registered') {
                    if (statusMessage) {
                        statusMessage.innerHTML = `
                            <div style="text-align:center;">
                                <p style='color:orange;'>⚠️ ${jr.message}</p>
                                <p>Existing record: <strong>${jr.existing_name || 'Unknown'}</strong></p>
                                <p style='color:gray;'>If this is you, proceed to <a href="../kiosk/kioskLogin.php">Login</a>. Otherwise change the name/phone/email and try again.</p>
                            </div>
                        `;
                    }
                } else {
                    if (statusMessage) {
                        statusMessage.innerHTML = `
                            <div style="text-align:center;">
                                <p style='color:orange;'>⚠️ ${json.message} (registered on file system)</p>
                                <p style='color:gray;'>Could not save to DB: ${jr && jr.message ? jr.message : 'unknown'}</p>
                                <a href="../kiosk/kioskLogin.php">
                                    <button style="padding:10px 20px; border:none; background:#007bff; color:white; border-radius:5px; cursor:pointer; margin-top:10px;">
                                        Go to Login
                                    </button>
                                </a>
                            </div>
                        `;
                    }
                }
            } catch (e) {
                if (statusMessage) {
                    statusMessage.innerHTML = `<p style='color:orange;'>Registered on file system but failed to save DB: ${e.message || e}</p>`;
                }
            }

            resetCaptureState();
            submitting = false;
            if (captureBtn) captureBtn.disabled = false;
            return;
        }

        if (statusMessage) {
            statusMessage.innerHTML = `<p style='color:orange;'>Unexpected response: ${JSON.stringify(json)}</p>`;
        }
        submitting = false;
        if (captureBtn) captureBtn.disabled = false;

    } catch (err) {
        console.error("Registration submit error:", err);

        if (retryCount < 1) {
            if (statusMessage) {
                statusMessage.innerHTML = `<p style='color:orange;'>Network error. Retrying...</p>`;
            }
            setTimeout(() => submitRegistration(retryCount + 1), 1000);
            submitting = false;
            return;
        }

        if (statusMessage) {
            statusMessage.innerHTML = `<p style='color:red;'>❌ Connection error: ${err.message || err}</p>`;
        }
        submitting = false;
        if (captureBtn) captureBtn.disabled = false;
    }
}

/* =========== Initialize =========== */
document.addEventListener('DOMContentLoaded', function() {
    // Set API base from global variable
    if (typeof window.PYTHON_API_BASE !== 'undefined') {
        apiBase = window.PYTHON_API_BASE;
        console.log('API Base set to:', apiBase);
    } else {
        console.error('PYTHON_API_BASE not found in window object');
    }

    // Visitor form event listeners
    ['visitorName', 'visitorPhone', 'visitorEmail'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', visitorSyncState, {passive: true});
    });

    // Phone input formatting
    const phoneInput = document.getElementById('visitorPhone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }

    const proceedBtn = document.getElementById('visitorProceed');
    if (proceedBtn) {
        proceedBtn.addEventListener('click', (e) => {
            e.preventDefault();
            openVisitorConfirm();
        });
    }

    const cancelConfirm = document.getElementById('visitorCancel');
    if (cancelConfirm) {
        cancelConfirm.addEventListener('click', (e) => {
            e.preventDefault();
            closeVisitorConfirm();
        });
    }

    const confirmBtn = document.getElementById('visitorConfirm');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', (e) => {
            e.preventDefault();
            acceptVisitorAndProceed();
        });
    }

    // Camera capture button
    const captureBtn = document.getElementById('captureBtn');
    if (captureBtn) {
        captureBtn.addEventListener('click', captureImage);
    }

    // Initialize visitor form state
    visitorSyncState();
    
    // Initialize camera instructions
    updateAngleInstructions();
    resetCaptureState();
});

// Expose functions globally if needed
window.captureImage = captureImage;