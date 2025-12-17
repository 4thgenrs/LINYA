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

/* =========== Helpers =========== */

/**
 * Ensure #statusMessage exists and return it.
 * If it doesn't exist, create it and insert after the #progress element.
 */
function ensureStatusMessage() {
    let el = document.getElementById('statusMessage');
    if (!el) {
        const prog = document.getElementById('progress');
        el = document.createElement('div');
        el.id = 'statusMessage';
        el.setAttribute('role', 'status');
        el.setAttribute('aria-live', 'polite');
        el.style.display = 'block';
        el.style.minHeight = '2.2rem';
        el.style.marginTop = '12px';
        if (prog && prog.parentNode) {
            prog.parentNode.insertBefore(el, prog.nextSibling);
        } else {
            const container = document.querySelector('.register-container') || document.body;
            container.appendChild(el);
        }
    }
    return el;
}

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

async function acceptVisitorAndProceed() {
    closeVisitorConfirm();

    const name = document.getElementById('visitorName').value.trim();
    const email = document.getElementById('visitorEmail').value.trim();
    const phone = document.getElementById('visitorPhone').value.trim();

    // Validate against existing clients before proceeding
    console.log('Checking for duplicate name/email...');
    
    try {
        const checkResponse = await fetch('../backend/checkClientDuplicate.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, email })
        });

        const checkResult = await checkResponse.json();
        console.log('Duplicate check result:', checkResult);

        if (checkResult.status === 'duplicate') {
            // Show error message to user
            const fieldName = checkResult.field === 'email' ? 'Email' : 'Name';
            alert(`❌ Registration Error\n\n${checkResult.message}`);
            
            // Keep modal closed and return to form
            return;
        }

        if (checkResult.status !== 'ok') {
            console.error('Validation error:', checkResult);
            alert('Unable to validate registration. Please try again.');
            return;
        }

        // No duplicates - proceed to camera
        console.log('✅ No duplicates found, proceeding to face capture');
        
        // Hide visitor form and show camera screen
        const visitorForm = document.getElementById('visitorForm');
        const cameraScreen = document.getElementById('cameraScreen');
        
        if (visitorForm) visitorForm.style.display = 'none';
        if (cameraScreen) {
            cameraScreen.style.display = 'block';
            initializeCamera();
        }

    } catch (err) {
        console.error('Duplicate check failed:', err);
        alert('Connection error. Please check your internet connection and try again.');
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

/**
 * Reset capture internal state.
 * @param {boolean} keepStatus - if true, don't clear #statusMessage (so messages remain visible)
 */
function resetCaptureState(keepStatus = false) {
    capturedImages = [];
    const countDisplay = document.getElementById('count');
    if (countDisplay) countDisplay.textContent = "0";
    currentAngleIndex = 0;
    updateAngleInstructions();
    if (!keepStatus) {
        const statusMessage = document.getElementById('statusMessage');
        if (statusMessage) statusMessage.innerHTML = '';
    }
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

    const statusMessage = ensureStatusMessage();
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
        showModal('warning', 'Incomplete Registration', `Please capture all ${maxImages} angles before submitting.`);
        submitting = false;
        if (captureBtn) captureBtn.disabled = false;
        return;
    }

    showModal('loading', 'Submitting Registration', 'Processing your images... Please wait.');

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

        console.log('API Response type:', resp.type, 'status:', resp.status);
        
        const respText = await resp.text();
        console.log('API Response text:', respText);
        
        // Quick CORS/opaque diagnostic
        if (resp.type === 'opaque' || resp.status === 0) {
            showModal('error', 'Network Error', 'Network/CORS blocked the request. Check server CORS headers and that API_BASE is correct.');
            submitting = false;
            if (captureBtn) captureBtn.disabled = false;
            return;
        }

        let json;
        try {
            json = JSON.parse(respText || '{}');
            console.log('Parsed JSON:', json);
        } catch (e) {
            console.error('JSON parse error:', e);
            throw new Error(`Invalid server response: ${resp.status} ${resp.statusText}`);
        }

        if (!resp.ok) {
            // Handle 409 Conflict (already registered)
            if (resp.status === 409 || (json && json.status === 'already_registered')) {
                const existingName = json?.existing_name || json?.name || 'Unknown User';
                const displayMessage = json?.message || 'This face is already registered in the system.';
                
                showModal('already_registered', 'User Already Registered', displayMessage, {
                    existingName: existingName,
                    showLoginButton: true
                });
                
                resetCaptureState(true);
                submitting = false;
                if (captureBtn) captureBtn.disabled = false;
                return;
            }

            if (json && json.status === 'glasses') {
                showModal('warning', 'Glasses Detected', `${json.message}. Please remove your ${json.type} and retake the Front View.`);
                resetCaptureState();
                submitting = false;
                if (captureBtn) captureBtn.disabled = false;
                return;
            }

            showModal('error', 'Server Error', json.message || resp.statusText);
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
                    showModal('success', 'Registration Successful', `${json.message} (saved to clients). You can now log in using face recognition.`, {
                        showLoginButton: true
                    });
                } else if (jr && jr.status === 'already_registered') {
                    showModal('warning', 'Already Registered', `${jr.message}. Existing record: ${jr.existing_name || 'Unknown'}. If this is you, proceed to Login. Otherwise change the name/phone/email and try again.`, {
                        showLoginButton: true
                    });
                } else {
                    showModal('warning', 'Partial Success', `${json.message} (registered on file system). Could not save to DB: ${jr && jr.message ? jr.message : 'unknown'}`, {
                        showLoginButton: true
                    });
                }
            } catch (e) {
                showModal('warning', 'Partial Success', `Registered on file system but failed to save DB: ${e.message || e}`);
            }

            resetCaptureState(true);
            submitting = false;
            if (captureBtn) captureBtn.disabled = false;
            return;
        }

        showModal('error', 'Unexpected Response', JSON.stringify(json));
        submitting = false;
        if (captureBtn) captureBtn.disabled = false;

    } catch (err) {
        console.error("Registration submit error:", err);

        if (retryCount < 1) {
            showModal('warning', 'Network Error', 'Network error. Retrying...');
            setTimeout(() => submitRegistration(retryCount + 1), 1000);
            submitting = false;
            return;
        }

        showModal('error', 'Connection Error', err.message || err);
        submitting = false;
        if (captureBtn) captureBtn.disabled = false;
    }
}

// Modal management functions
function createModal() {
    // Remove existing modal if any
    const existingModal = document.getElementById('registrationModal');
    if (existingModal) {
        existingModal.remove();
    }

    const modal = document.createElement('div');
    modal.id = 'registrationModal';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 10000;
        font-family: Arial, sans-serif;
    `;

    modal.innerHTML = `
        <div class="modal-content" style="
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 90%;
            text-align: center;
            position: relative;
        ">
            <div class="modal-icon" style="font-size: 48px; margin-bottom: 20px;"></div>
            <h2 class="modal-title" style="margin: 0 0 15px 0; font-size: 24px; color: #333;"></h2>
            <p class="modal-message" style="margin: 0 0 25px 0; font-size: 16px; color: #666; line-height: 1.5;"></p>
            <div class="modal-actions" style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;"></div>
            <button class="modal-close" style="
                position: absolute;
                top: 15px;
                right: 15px;
                background: none;
                border: none;
                font-size: 24px;
                cursor: pointer;
                color: #999;
                padding: 5px;
            ">×</button>
        </div>
    `;

    document.body.appendChild(modal);

    // Close modal when clicking X or backdrop
    modal.querySelector('.modal-close').addEventListener('click', hideModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) hideModal();
    });

    return modal;
}

function showModal(type, title, message, options = {}) {
    let modal = document.getElementById('registrationModal');
    if (!modal) {
        modal = createModal();
    }

    const iconEl = modal.querySelector('.modal-icon');
    const titleEl = modal.querySelector('.modal-title');
    const messageEl = modal.querySelector('.modal-message');
    const actionsEl = modal.querySelector('.modal-actions');
    const contentEl = modal.querySelector('.modal-content');

    // Set icon and colors based on type
    let icon = '', color = '';
    switch (type) {
        case 'success':
            icon = '✅';
            color = '#28a745';
            break;
        case 'error':
            icon = '❌';
            color = '#ff0019ff';
            break;
        case 'warning':
            icon = '⚠️';
            color = '#ffc107';
            break;
        case 'loading':
            icon = '⏳';
            color = '#17a2b8';
            break;
        case 'already_registered':
            icon = '⚠️';
            color = '#fd7e14';
            break;
        default:
            icon = 'ℹ️';
            color = '#6c757d';
    }

    iconEl.textContent = icon;
    titleEl.textContent = title;
    messageEl.textContent = message;
    contentEl.style.borderTop = `4px solid ${color}`;

    // Clear previous actions
    actionsEl.innerHTML = '';

    // Add actions based on type and options
    if (type === 'loading') {
        // No buttons for loading state
    } else if (type === 'already_registered') {
        actionsEl.innerHTML = `
            <a href="kioskLogin.php" style="text-decoration: none;">
                <button style="
                    padding: 12px 24px;
                    border: none;
                    background: #dc3545;
                    color: white;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 16px;
                    font-weight: 600;
                ">GO TO LOGIN</button>
            </a>
            <button onclick="hideModal(); location.reload()" style="
                padding: 12px 24px;
                border: 2px solid #6c757d;
                background: white;
                color: #6c757d;
                border-radius: 6px;
                cursor: pointer;
                font-size: 16px;
                font-weight: 600;
            ">Try Again</button>
        `;
    } else if (options.showLoginButton) {
        actionsEl.innerHTML = `
            <a href="kioskLogin.php" style="text-decoration: none;">
                <button style="
                    padding: 12px 24px;
                    border: none;
                    background: #007bff;
                    color: white;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 16px;
                    font-weight: 600;
                ">Go to Login</button>
            </a>
            <button onclick="hideModal()" style="
                padding: 12px 24px;
                border: 2px solid #6c757d;
                background: white;
                color: #6c757d;
                border-radius: 6px;
                cursor: pointer;
                font-size: 16px;
                font-weight: 600;
            ">Close</button>
        `;
    } else {
        actionsEl.innerHTML = `
            <button onclick="hideModal()" style="
                padding: 12px 24px;
                border: none;
                background: #6c757d;
                color: white;
                border-radius: 6px;
                cursor: pointer;
                font-size: 16px;
                font-weight: 600;
            ">OK</button>
        `;
    }

    // Show modal
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden'; // Prevent scrolling
}

function hideModal() {
    const modal = document.getElementById('registrationModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = ''; // Restore scrolling
    }
}

// Optional: Add CSS for smooth animations
const style = document.createElement('style');
style.textContent = `
    #registrationModal {
        transition: opacity 0.3s ease;
    }
    #registrationModal .modal-content {
        animation: modalSlideIn 0.3s ease-out;
    }
    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-50px) scale(0.9);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
`;
document.head.appendChild(style);

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
