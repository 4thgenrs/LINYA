// ../js/kioskLogin.js
// Face Login JavaScript - Client-side functionality with Enhanced Status UI

const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const context = canvas.getContext('2d');

window.videoStream = null;
window.autoCaptureCountdown = null;
window.autoScanActive = false;

/* ============================================
   DEBUG HELPER
   ============================================ */
function dbg(...args) {
    const ENABLE_DEBUG = true;
    if (ENABLE_DEBUG) console.log(...args);
}

/* ============================================
   STATUS MESSAGE CARD
   Only used for: scanning + initializing states
   ============================================ */
function setStatusMessage(type, title, desc, countdown) {
    desc      = (desc      !== undefined) ? desc      : '';
    countdown = (countdown !== undefined) ? countdown : null;

    var el = document.getElementById('statusMessage');
    if (!el) return;

    el.className = '';
    el.classList.add('state-scanning');

    var icon = '<svg width="20" height="20" viewBox="0 0 42 42" fill="none">'
        + '<circle cx="21" cy="21" r="18" stroke="#dcfce7" stroke-width="3"/>'
        + '<circle cx="21" cy="21" r="18" stroke="#22c55e" stroke-width="3"'
        + ' stroke-dasharray="28 85" stroke-linecap="round"'
        + ' transform="rotate(-90 21 21)" class="status-ring-pulse"/>'
        + '<circle cx="21" cy="21" r="8" fill="#dcfce7"/>'
        + '</svg>';

    var html = '<div class="status-icon">' + icon + '</div>'
        + '<div class="status-body">'
        + '<span class="status-title status-dots">' + title + '</span>'
        + (desc ? '<span class="status-desc">' + desc + '</span>' : '')
        + '<div class="status-bar-wrap"><div class="status-bar"></div></div>'
        + '</div>';

    if (countdown !== null && countdown > 0) {
        html += '<div class="status-countdown">'
            + '<span class="cd-num">' + countdown + '</span>'
            + '<span class="cd-label">SEC</span>'
            + '</div>';
    }

    el.innerHTML = html;
}

window.setStatusMessage = setStatusMessage;

function showScanningStatus(title, desc, countdown) {
    setStatusMessage('scanning', title, desc, countdown);
}

/* ============================================
   PROCESSING MODAL FUNCTIONS
   ============================================ */
function createProcessingModal() {
    if (document.getElementById('processingModal')) return;
    document.body.insertAdjacentHTML('beforeend',
        '<div id="processingModal" class="processing-modal-overlay">'
        + '<div class="processing-modal">'
        + '<div class="spinner-container">'
        + '<div class="spinner"></div><div class="spinner"></div><div class="spinner"></div>'
        + '</div>'
        + '<h3>Processing Login<span class="processing-dots"></span></h3>'
        + '<p>Please wait while we verify your identity</p>'
        + '<div class="progress-bar-container"><div class="progress-bar"></div></div>'
        + '</div></div>'
    );
}

function showProcessingModal() {
    createProcessingModal();
    var modal = document.getElementById('processingModal');
    if (modal) { modal.offsetHeight; modal.classList.add('show'); }
}

function hideProcessingModal() {
    var modal = document.getElementById('processingModal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(function() { modal.remove(); }, 300);
    }
}

function showErrorModal(message, durationSeconds) {
    durationSeconds = (durationSeconds !== undefined) ? durationSeconds : 2;
    var existing = document.getElementById('errorModal');
    if (existing) existing.remove();

    document.body.insertAdjacentHTML('beforeend',
        '<div id="errorModal" class="processing-modal-overlay show">'
        + '<div class="processing-modal" style="border-color:rgba(239,68,68,0.5);">'
        + '<div style="width:80px;height:80px;margin:0 auto 20px;background:rgba(239,68,68,0.1);'
        + 'border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid #ef4444;">'
        + '<svg width="40" height="40" viewBox="0 0 24 24" fill="#ef4444">'
        + '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>'
        + '</svg></div>'
        + '<h3 style="color:#ef4444;">Error</h3>'
        + '<p style="color:#666;margin-top:15px;">' + message + '</p>'
        + '</div></div>'
    );

    setTimeout(function() {
        var modal = document.getElementById('errorModal');
        if (modal) {
            modal.classList.remove('show');
            setTimeout(function() { modal.remove(); }, 300);
        }
    }, durationSeconds * 1000);
}

/* ============================================
   CAMERA FUNCTIONS
   ============================================ */
window.startCamera = function() {
    showScanningStatus('Initializing camera', 'Please wait...');

    if (window.videoStream) {
        window.videoStream.getTracks().forEach(function(track) { track.stop(); });
        window.videoStream = null;
    }

    navigator.mediaDevices.getUserMedia({
        video: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: 'user' },
        audio: false
    })
    .then(function(stream) {
        window.videoStream   = stream;
        video.srcObject      = stream;
        video.style.display  = 'block';
        canvas.style.display = 'none';

        var playPromise = video.play();
        if (playPromise !== undefined) {
            playPromise.catch(function() {
                setTimeout(function() { video.play().catch(function() {}); }, 100);
            });
        }

        video.addEventListener('loadedmetadata', function() {
            canvas.width  = video.videoWidth;
            canvas.height = video.videoHeight;
            if (typeof startCaptureFace === 'function') startCaptureFace();
        });
    })
    .catch(function(err) {
        console.error('Camera error:', err);
        showErrorModal('Error accessing webcam: ' + err.name + ' - ' + err.message, 3);
    });
};

function captureImage() {
    dbg('📸 captureImage()');

    if (canvas.width === 0 || canvas.height === 0) {
        canvas.width  = video.videoWidth  || 1280;
        canvas.height = video.videoHeight || 720;
    }

    context.imageSmoothingEnabled = false;

    try {
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
    } catch (e) {
        console.error('Error drawing video to canvas:', e);
        showErrorModal('Unable to capture from camera. Please try again.', 2);
        return;
    }

    var maxUploadWidth = 1280;
    var aspect         = (canvas.width && canvas.height) ? (canvas.width / canvas.height) : (1280 / 720);
    var uploadWidth    = Math.min(maxUploadWidth, canvas.width || maxUploadWidth);
    var uploadHeight   = Math.round(uploadWidth / aspect);

    var tmpCanvas      = document.createElement('canvas');
    tmpCanvas.width    = uploadWidth;
    tmpCanvas.height   = uploadHeight;

    var tmpCtx = tmpCanvas.getContext('2d');
    tmpCtx.imageSmoothingEnabled = true;
    tmpCtx.drawImage(video, 0, 0, tmpCanvas.width, tmpCanvas.height);

    document.getElementById('imageInput').value = tmpCanvas.toDataURL('image/jpeg', 0.85);

    showScanningStatus('Face captured', 'Logging in, please wait');

    if (typeof handleLogin === 'function') handleLogin();
}

/* ============================================
   AUTO-CAPTURE FLOW
   ============================================ */
function startCaptureFace() {
    dbg('▶️ startCaptureFace()');

    if (window.autoCaptureCountdown) return;
    if (window.autoScanActive) return;
    window.autoScanActive = true;

    var remaining = 3;
    showScanningStatus('Scanning face', 'Please look directly at the camera', remaining);

    window.autoCaptureCountdown = setInterval(function() {
        remaining -= 1;

        if (remaining <= 0) {
            clearInterval(window.autoCaptureCountdown);
            window.autoCaptureCountdown = null;
            window.autoScanActive       = false;
            showScanningStatus('Scanning face', 'Capturing image...');
            captureImage();
            return;
        }

        showScanningStatus('Scanning face', 'Please look directly at the camera', remaining);
    }, 1000);
}

function resetCapture() {
    dbg('🔄 resetCapture()');

    var imgEl     = document.getElementById('imageInput');
    var glassesEl = document.getElementById('checkGlasses');
    if (imgEl)     imgEl.value     = '';
    if (glassesEl) glassesEl.value = 'true';

    video.style.display  = 'block';
    canvas.style.display = 'none';

    if (window.autoCaptureCountdown) {
        clearInterval(window.autoCaptureCountdown);
        window.autoCaptureCountdown = null;
    }
    window.autoScanActive = false;

    var faceCheckError = document.getElementById('faceCheckError');
    if (faceCheckError) faceCheckError.remove();

    window.startCamera();
}

/* ============================================
   LOGIN HANDLER (AJAX → kioskLogin.php)
   ============================================ */
async function handleLogin() {
    dbg('🔐 handleLogin()');

    var imageValue        = document.getElementById('imageInput')?.value  || '';
    var checkGlassesValue = document.getElementById('checkGlasses')?.value || 'true';

    if (!imageValue) {
        showErrorModal('Please capture an image first!', 2);
        return;
    }

    showProcessingModal();
    showScanningStatus('Processing login', 'Verifying your identity');

    try {
        var formData = new FormData();
        formData.append('image',         imageValue);
        formData.append('check_glasses', checkGlassesValue);
        formData.append('ajax_check',    'true');

        dbg('➡️ Sending AJAX to kioskLogin.php', { imageLen: imageValue.length });

        var response    = await fetch('kioskLogin.php', { method: 'POST', body: formData });
        var contentType = response.headers.get('content-type') || '';
        var bodyText    = await response.text();

        dbg('⬅️ HTTP:', response.status, 'content-type:', contentType);
        dbg('📦 raw response head:', bodyText.slice(0, 300));

        var data      = null;
        var looksJson = contentType.includes('application/json') || bodyText.trim().startsWith('{');
        if (looksJson) {
            try { data = JSON.parse(bodyText); } catch (e) {
                console.error('❌ JSON parse failed:', e);
            }
        }

        hideProcessingModal();

        if (!data) {
            showErrorModal('Server did not return JSON (check console).', 3);
            resetCapture();
            return;
        }

        /* recognized */
        if (data.status === 'recognized' || data.status === 'success') {
            dbg('✅ Recognized:', data);
            showScanningStatus('Face recognized', 'Redirecting...');
            var form = document.getElementById('loginForm');
            if (!form) { showErrorModal('Login form not found.', 2); return; }
            var ajaxInput = form.querySelector('input[name="ajax_check"]');
            if (ajaxInput) ajaxInput.remove();
            setTimeout(function() { form.submit(); }, 800);
            return;
        }

        /* accessory detected */
        if (data.status === 'accessory_detected') {
            dbg('⚠️ Accessory:', data);
            setTimeout(function() {
                resetCapture();
                showErrorModal(data.message || 'Accessory detected. Please remove it and try again.', 2);
            }, 300);
            return;
        }

        /* unknown user */
        if (data.status === 'unknown') {
            dbg('❓ Unknown:', data);
            setTimeout(function() {
                resetCapture();
                showErrorModal(data.message || 'Unknown user. Please register first.', 2);
            }, 300);
            return;
        }

        /* no person */
        if (data.status === 'no_person_found') {
            dbg('👤 No person:', data);
            setTimeout(function() {
                resetCapture();
                showErrorModal(data.message || 'No person detected. Please try again.', 2);
            }, 300);
            return;
        }

        /* server error */
        if (data.status === 'error') {
            dbg('❌ Error:', data);
            setTimeout(function() {
                resetCapture();
                showErrorModal(data.message || 'Login failed. Please try again.', 2);
            }, 300);
            return;
        }

        /* fallback */
        dbg('❌ Unexpected:', data);
        setTimeout(function() {
            resetCapture();
            showErrorModal(data.message || 'Unexpected server response.', 2);
        }, 300);

    } catch (error) {
        console.error('Login check failed:', error);
        hideProcessingModal();
        showErrorModal('Request failed: ' + error.message, 2);
        resetCapture();
    }
}

/* ============================================
   SERVER RESET HANDLER
   ============================================ */
function applyServerCaptureReset() {
    try {
        var cfg = window.serverCaptureReset;
        if (!cfg) return;

        dbg('🧹 applyServerCaptureReset()', cfg);

        var imageInputEl   = document.getElementById('imageInput');
        var checkGlassesEl = document.getElementById('checkGlasses');
        if (imageInputEl)   imageInputEl.value   = '';
        if (checkGlassesEl) checkGlassesEl.value = 'true';

        video.style.display  = 'block';
        canvas.style.display = 'none';

        if (typeof window.startCamera === 'function') window.startCamera();

        var faceCheckErrorEl = document.getElementById('faceCheckError');
        if (faceCheckErrorEl) faceCheckErrorEl.remove();

        if (cfg.message) {
            var cleanMessage = String(cfg.message).replace(/<[^>]*>/g, ' ').trim();
            showErrorModal(cleanMessage, 2);
        }

        delete window.serverCaptureReset;
    } catch (e) {
        console.error('applyServerCaptureReset error:', e);
        showErrorModal('An error occurred while resetting: ' + e.message, 2);
    }
}

/* ============================================
   GLOBALS
   ============================================ */
window.captureImage       = captureImage;
window.handleLogin        = handleLogin;
window.resetCapture       = resetCapture;
window.setStatusMessage   = setStatusMessage;
window.showScanningStatus = showScanningStatus;

/* ============================================
   INIT
   ============================================ */
document.addEventListener('DOMContentLoaded', function() {
    dbg('✅ kioskLogin.js loaded');
    window.startCamera();
    applyServerCaptureReset();
});

window.addEventListener('beforeunload', function() {
    if (window.videoStream) {
        window.videoStream.getTracks().forEach(function(track) { track.stop(); });
    }
    if (window.autoCaptureCountdown) {
        clearInterval(window.autoCaptureCountdown);
    }
});