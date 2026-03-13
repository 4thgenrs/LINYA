// ../js/kioskLogin.js
// Face Login JavaScript - Client-side functionality (Debug + Stable AJAX)

const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const context = canvas.getContext('2d');

// Global variables for camera management
window.videoStream = null;
window.autoCaptureCountdown = null;
window.autoScanActive = false;

/* ============================================
   DEBUG HELPERS
   ============================================ */

function dbg(...args) {
  // Change to false if you want to mute logs later
  const ENABLE_DEBUG = true;
  if (ENABLE_DEBUG) console.log(...args);
}

/* ============================================
   PROCESSING MODAL FUNCTIONS
   ============================================ */

function createProcessingModal() {
  const modalHTML = `
    <div id="processingModal" class="processing-modal-overlay">
      <div class="processing-modal">
        <div class="spinner-container">
          <div class="spinner"></div>
          <div class="spinner"></div>
          <div class="spinner"></div>
        </div>
        <h3>Processing Login<span class="processing-dots"></span></h3>
        <p>Please wait while we verify your identity</p>
        <div class="progress-bar-container">
          <div class="progress-bar"></div>
        </div>
      </div>
    </div>
  `;

  if (!document.getElementById('processingModal')) {
    document.body.insertAdjacentHTML('beforeend', modalHTML);
  }
}

function showProcessingModal() {
  createProcessingModal();
  const modal = document.getElementById('processingModal');
  if (modal) {
    modal.offsetHeight; // force reflow
    modal.classList.add('show');
  }
}

function hideProcessingModal() {
  const modal = document.getElementById('processingModal');
  if (modal) {
    modal.classList.remove('show');
    setTimeout(() => modal.remove(), 300);
  }
}

function showErrorModal(message, durationSeconds = 2) {
  const existingModal = document.getElementById('errorModal');
  if (existingModal) existingModal.remove();

  const modalHTML = `
    <div id="errorModal" class="processing-modal-overlay show">
      <div class="processing-modal" style="border-color: rgba(239, 68, 68, 0.5);">
        <div style="
          width: 80px;
          height: 80px;
          margin: 0 auto 20px;
          background: rgba(239, 68, 68, 0.1);
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          border: 2px solid #ef4444;
        ">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="#ef4444">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
          </svg>
        </div>
        <h3 style="color: #ef4444;">Error</h3>
        <p style="color: #666; margin-top: 15px;">${message}</p>
      </div>
    </div>
  `;

  document.body.insertAdjacentHTML('beforeend', modalHTML);

  setTimeout(() => {
    const modal = document.getElementById('errorModal');
    if (modal) {
      modal.classList.remove('show');
      setTimeout(() => modal.remove(), 300);
    }
  }, durationSeconds * 1000);
}

/* ============================================
   CAMERA FUNCTIONS
   ============================================ */

window.startCamera = function () {
  // Stop existing stream if any
  if (window.videoStream) {
    window.videoStream.getTracks().forEach(track => track.stop());
    window.videoStream = null;
  }

  navigator.mediaDevices.getUserMedia({
    video: {
      width: { ideal: 1280 },
      height: { ideal: 720 },
      facingMode: 'user'
    },
    audio: false
  })
    .then(stream => {
      window.videoStream = stream;
      video.srcObject = stream;

      // Ensure video is visible and canvas hidden
      video.style.display = 'block';
      canvas.style.display = 'none';

      const playPromise = video.play();
      if (playPromise !== undefined) {
        playPromise.catch(() => {
          setTimeout(() => video.play().catch(() => { }), 100);
        });
      }

      video.addEventListener('loadedmetadata', () => {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        // Start scanning once camera is ready
        if (typeof startCaptureFace === 'function') {
          startCaptureFace();
        }
      });
    })
    .catch(err => {
      console.error('Camera error:', err);
      showErrorModal("Error accessing webcam: " + err.name + " - " + err.message, 3);
    });
};

function captureImage() {
  dbg("📸 captureImage() clicked");

  // Ensure canvas has correct dimensions
  if (canvas.width === 0 || canvas.height === 0) {
    canvas.width = video.videoWidth || 1280;
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

  // Offscreen canvas for upload
  const maxUploadWidth = 1280;
  const aspect = (canvas.width && canvas.height) ? (canvas.width / canvas.height) : (1280 / 720);
  const uploadWidth = Math.min(maxUploadWidth, canvas.width || maxUploadWidth);
  const uploadHeight = Math.round(uploadWidth / aspect);

  const tmpCanvas = document.createElement('canvas');
  tmpCanvas.width = uploadWidth;
  tmpCanvas.height = uploadHeight;

  const tmpCtx = tmpCanvas.getContext('2d');
  tmpCtx.imageSmoothingEnabled = true;
  tmpCtx.drawImage(video, 0, 0, tmpCanvas.width, tmpCanvas.height);

  const dataURL = tmpCanvas.toDataURL('image/jpeg', 0.85);
  document.getElementById('imageInput').value = dataURL;

  // Keep the live camera feed visible (scanning effect)
  // The captured image is kept in the hidden input and used for verification.
  const statusEl = document.getElementById('statusMessage');
  if (statusEl) {
    statusEl.innerHTML = '<p style="color:green;">✅ Face captured! Logging in...</p>';
  }

  // Run the EXACT same verification flow the old Login button used to trigger
  if (typeof handleLogin === 'function') {
    handleLogin();
  }
}

/* ============================================
   AUTO-CAPTURE FLOW (START CAPTURE FACE)
   ============================================ */

function startCaptureFace() {
  dbg("▶️ startCaptureFace()");

  // Already scanning
  if (window.autoScanActive) {
    return;
  }
  window.autoScanActive = true;

  const statusEl = document.getElementById('statusMessage');

  let remaining = 4; // seconds
  const updateUI = () => {
    if (statusEl) {
      statusEl.innerHTML = `<p style="color:blue;">Scanning in ${remaining}...</p>`;
    }
  };

  updateUI();

  window.autoCaptureCountdown = setInterval(() => {
    remaining -= 1;

    if (remaining <= 0) {
      clearInterval(window.autoCaptureCountdown);
      window.autoCaptureCountdown = null;
      window.autoScanActive = false;

      if (statusEl) {
        statusEl.innerHTML = '<p style="color:blue;">Scanning...</p>'; 
      }

      captureImage();
      return;
    }

    updateUI();
  }, 1000);
}

function resetCapture() {
  dbg("🔄 resetCapture()");

  const imgEl = document.getElementById('imageInput');
  const glassesEl = document.getElementById('checkGlasses');

  if (imgEl) imgEl.value = '';
  if (glassesEl) glassesEl.value = 'true';

  video.style.display = 'block';
  canvas.style.display = 'none';

  window.startCamera();

  const statusEl = document.getElementById('statusMessage');
  if (statusEl) statusEl.innerHTML = '';

  const faceCheckError = document.getElementById('faceCheckError');
  if (faceCheckError) faceCheckError.remove();

  // Cancel any in-progress auto-capture countdown
  if (window.autoCaptureCountdown) {
    clearInterval(window.autoCaptureCountdown);
    window.autoCaptureCountdown = null;
  }

  // Ensure scanning restarts after a reset
  window.autoScanActive = false;
  if (typeof startCaptureFace === 'function') {
    setTimeout(startCaptureFace, 500);
  }

}

/* ============================================
   LOGIN HANDLER (AJAX CHECK → kioskLogin.php)
   ============================================ */

async function handleLogin() {
  dbg("🔐 handleLogin() clicked");

  const imageValue = document.getElementById('imageInput')?.value || '';
  const checkGlassesValue = document.getElementById('checkGlasses')?.value || 'true';

  if (!imageValue) {
    showErrorModal('Please capture an image first!', 2);
    return;
  }

  showProcessingModal();

  const statusEl = document.getElementById('statusMessage');
  if (statusEl) statusEl.innerHTML = '<p style="color:blue;">🔄 Processing login...</p>';

  try {
    const formData = new FormData();
    formData.append('image', imageValue);
    formData.append('check_glasses', checkGlassesValue);
    formData.append('ajax_check', 'true');

    dbg("➡️ Sending AJAX to kioskLogin.php", {
      imageLen: imageValue.length,
      check_glasses: checkGlassesValue
    });

    const response = await fetch('kioskLogin.php', {
      method: 'POST',
      body: formData
    });

    const contentType = response.headers.get('content-type') || '';
    const bodyText = await response.text();

    dbg("⬅️ kioskLogin.php HTTP:", response.status, "content-type:", contentType);
    dbg("📦 kioskLogin.php raw response head:", bodyText.slice(0, 300));

    // Try to parse JSON even if status is 400/500
    let data = null;
    const looksJson = contentType.includes('application/json') || bodyText.trim().startsWith('{');

    if (looksJson) {
      try {
        data = JSON.parse(bodyText);
      } catch (e) {
        console.error("❌ JSON parse failed. Response looked JSON but isn't valid:", e);
      }
    }

    hideProcessingModal();
    if (statusEl) statusEl.innerHTML = '';

    // If NOT JSON, show a helpful error immediately
    if (!data) {
      // This is the key debug: if HTML is returned, we will see it in console already.
      showErrorModal('Server did not return JSON (check console: raw response).', 3);
      return;
    }

    // Handle standard statuses
    if (data.status === 'recognized' || data.status === 'success') {
      dbg("✅ Recognized via AJAX:", data);

      // Important: submit as normal POST (non-ajax) so kioskLogin.php can show modal flow
      const form = document.getElementById('loginForm');
      if (!form) {
        showErrorModal('Login form not found.', 2);
        return;
      }

      // Remove ajax_check before submit (so server renders modal/redirect flow)
      const ajaxInput = form.querySelector('input[name="ajax_check"]');
      if (ajaxInput) ajaxInput.remove();

      form.submit();
      return;
    }

    if (data.status === 'accessory_detected') {
      dbg("⚠️ Accessory detected:", data);
      resetCapture();
      showErrorModal(data.message || 'Accessory detected. Please remove it and try again.', 2);
      return;
    }

    if (data.status === 'unknown') {
      dbg("❓ Unknown user:", data);
      resetCapture();
      showErrorModal(data.message || 'Unknown user. Please register first.', 2);
      return;
    }

    if (data.status === 'no_person_found') {
      dbg("👤 No person found:", data);
      resetCapture();
      showErrorModal(data.message || 'No person detected. Please try again.', 2);
      return;
    }

    if (data.status === 'error') {
      dbg("❌ Server error:", data);
      resetCapture();
      showErrorModal(data.message || 'Login failed. Please try again.', 2);
      return;
    }

    // Anything else
    dbg("❌ Unexpected status:", data);
    resetCapture();
    showErrorModal(data.message || 'Unexpected server response. Please try again.', 2);

  } catch (error) {
    console.error('Login check failed:', error);
    hideProcessingModal();
    if (statusEl) statusEl.innerHTML = '';
    showErrorModal('Request failed: ' + error.message, 2);
  }
}

/* ============================================
   SERVER RESET HANDLER
   ============================================ */

function applyServerCaptureReset() {
  try {
    const cfg = window.serverCaptureReset;
    if (!cfg) return;

    dbg("🧹 applyServerCaptureReset()", cfg);

    const imageInputEl = document.getElementById('imageInput');
    const checkGlassesEl = document.getElementById('checkGlasses');

    if (imageInputEl) imageInputEl.value = '';
    if (checkGlassesEl) checkGlassesEl.value = 'true';

    video.style.display = 'block';
    canvas.style.display = 'none';

    if (typeof window.startCamera === 'function') {
      window.startCamera();
    }


    const faceCheckErrorEl = document.getElementById('faceCheckError');
    if (faceCheckErrorEl) faceCheckErrorEl.remove();

    if (cfg.message) {
      const cleanMessage = String(cfg.message).replace(/<[^>]*>/g, ' ').trim();
      showErrorModal(cleanMessage, 2);
    }

    delete window.serverCaptureReset;
  } catch (e) {
    console.error('applyServerCaptureReset error:', e);
    showErrorModal('An error occurred while resetting: ' + e.message, 2);
  }
}

/* ============================================
   MAKE FUNCTIONS GLOBAL (IMPORTANT for onclick="")
   ============================================ */

window.captureImage = captureImage;
window.handleLogin = handleLogin;
window.resetCapture = resetCapture;

/* ============================================
   INITIALIZATION
   ============================================ */

document.addEventListener('DOMContentLoaded', function () {
  dbg("✅ kioskLogin.js loaded");
  window.startCamera();
  applyServerCaptureReset();
});