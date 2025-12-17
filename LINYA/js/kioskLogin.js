// Face Login JavaScript - Client-side functionality

// Compute API base URL dynamically to avoid calling client-localhost by mistake
const _apiHost = window.location.hostname;
const apiBase = (_apiHost === 'localhost' || _apiHost === '127.0.0.1')
    ? 'http://127.0.0.1:5000'
    : `${window.location.protocol}//${_apiHost}:5000`;

const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const context = canvas.getContext('2d');
const loginBtn = document.getElementById('loginBtn');
const loginWrapper = document.getElementById('loginWrapper');

// Global variables for camera management
window.videoStream = null;

/* ============================================
   PROCESSING MODAL FUNCTIONS - NEW
   ============================================ */

// Create Processing Modal
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
    
    // Insert modal into body if it doesn't exist
    if (!document.getElementById('processingModal')) {
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }
}

// Show Processing Modal
function showProcessingModal() {
    createProcessingModal();
    const modal = document.getElementById('processingModal');
    if (modal) {
        // Force reflow to ensure animation plays
        modal.offsetHeight;
        modal.classList.add('show');
    }
}

// Hide Processing Modal
function hideProcessingModal() {
    const modal = document.getElementById('processingModal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.remove();
        }, 300); // Wait for fade-out animation
    }
}

// Show Error Modal (for displaying errors)
function showErrorModal(message, durationSeconds = 2) {
    // Remove existing error modal if present
    const existingModal = document.getElementById('errorModal');
    if (existingModal) {
        existingModal.remove();
    }

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

    // Auto-hide after specified duration
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

window.startCamera = function() {
    console.log('🎥 startCamera() called');
    console.log('🎥 Current videoStream state:', window.videoStream ? 'exists' : 'null');
    console.log('🎥 Current videoStream active:', window.videoStream ? window.videoStream.active : 'N/A');
    console.log('🎥 Current video.srcObject:', video.srcObject ? 'exists' : 'null');
    console.log('🎥 Current video display:', video.style.display);
    console.log('🎥 Current canvas display:', canvas.style.display);
    
    // Stop existing stream if any
    if (window.videoStream) {
        console.log('🎥 Stopping existing video stream');
        window.videoStream.getTracks().forEach(track => {
            console.log('🎥 Stopping track:', track.kind, track.readyState);
            track.stop();
        });
        window.videoStream = null;
    }
    
    console.log('🎥 Requesting new getUserMedia stream...');
    navigator.mediaDevices.getUserMedia({ 
        video: { 
            width: { ideal: 1280 },
            height: { ideal: 720 },
            facingMode: 'user'
        },
        audio: false
    })
        .then(stream => {
            console.log('🎥 ✅ getUserMedia success, got stream:', stream.id);
            console.log('🎥 Stream active:', stream.active);
            console.log('🎥 Stream tracks:', stream.getTracks().map(t => t.kind + ':' + t.readyState));
            
            window.videoStream = stream;
            video.srcObject = stream;
            
            console.log('🎥 Set video.srcObject, now ensuring visibility...');
            // Ensure video is visible and canvas is hidden
            video.style.display = 'block';
            canvas.style.display = 'none';
            console.log('🎥 Set video display: block, canvas display: none');
            
            // Force play the video
            console.log('🎥 Attempting to play video...');
            const playPromise = video.play();
            if (playPromise !== undefined) {
                playPromise
                    .then(() => {
                        console.log('🎥 ✅ Video playing successfully');
                        console.log('🎥 Video paused state:', video.paused);
                        console.log('🎥 Video readyState:', video.readyState);
                    })
                    .catch(err => {
                        console.log('🎥 ❌ Video play error:', err.name, err.message);
                        // Try playing again after a short delay
                        setTimeout(() => {
                            console.log('🎥 Retrying video play...');
                            video.play()
                                .then(() => console.log('🎥 ✅ Retry play success'))
                                .catch(e => console.log('🎥 ❌ Retry play error:', e.name, e.message));
                        }, 100);
                    });
            }

            // Ensure canvas dimensions match actual video feed
            video.addEventListener('loadedmetadata', () => {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                console.log(`🎥 ✅ Video metadata loaded - dimensions: ${canvas.width}x${canvas.height}`);
                console.log('🎥 Video videoWidth:', video.videoWidth, 'videoHeight:', video.videoHeight);
            });
            
            console.log('🎥 ✅ Camera started successfully - setup complete');
        })
        .catch(err => {
            console.error('🎥 ❌ Camera error:', err.name, err.message);
            showErrorModal("Error accessing webcam: " + err.name + " - " + err.message, 3);
        });
}

function captureImage() {
    console.log('📸 captureImage() called');
    console.log('📸 Video state:', {
        videoWidth: video.videoWidth,
        videoHeight: video.videoHeight,
        readyState: video.readyState,
        paused: video.paused,
        srcObject: !!video.srcObject
    });
    console.log('📸 Canvas state:', {
        width: canvas.width,
        height: canvas.height,
        display: canvas.style.display
    });
    
    // Ensure canvas has the correct dimensions
    if (canvas.width === 0 || canvas.height === 0) {
        console.log('📸 Setting canvas dimensions from video');
        canvas.width = video.videoWidth || 1280;
        canvas.height = video.videoHeight || 720;
        console.log('📸 Canvas dimensions set to:', canvas.width, 'x', canvas.height);
    }
    
    context.imageSmoothingEnabled = false;

    // Draw the current video frame to the visible canvas for preview
    console.log('📸 Drawing video frame to canvas...');
    try {
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        console.log('📸 ✅ Video frame drawn to canvas successfully');
    } catch (e) {
        console.log('📸 ❌ Error drawing video to canvas:', e.message);
        return;
    }

    // Create an offscreen canvas for high-quality upload image
    // Use higher resolution (up to 1280px) and better JPEG quality (0.85)
    const maxUploadWidth = 1280;  // Increased from 800 for better face detection
    const aspect = canvas.width && canvas.height ? canvas.width / canvas.height : (1280 / 720);
    const uploadWidth = Math.min(maxUploadWidth, canvas.width || maxUploadWidth);
    const uploadHeight = Math.round(uploadWidth / aspect);
    console.log('📸 Creating upload canvas:', uploadWidth, 'x', uploadHeight);
    
    const tmpCanvas = document.createElement('canvas');
    tmpCanvas.width = uploadWidth;
    tmpCanvas.height = uploadHeight;
    const tmpCtx = tmpCanvas.getContext('2d');
    tmpCtx.imageSmoothingEnabled = true;
    tmpCtx.drawImage(video, 0, 0, tmpCanvas.width, tmpCanvas.height);

    // Use higher JPEG quality (0.85 instead of 0.7) for better face detection accuracy
    const dataURL = tmpCanvas.toDataURL('image/jpeg', 0.85);
    console.log('📸 Generated dataURL length:', dataURL.length);
    document.getElementById('imageInput').value = dataURL;

    // Enable the Login button once an image is captured
    if (loginBtn) {
        console.log('📸 Enabling login button');
        loginBtn.disabled = false;
        // Remove disabled wrapper styling so tooltip won't show and ARIA updates
        if (loginWrapper) loginWrapper.classList.remove('disabled');
        loginBtn.setAttribute('aria-disabled', 'false');
    }

    console.log('📸 Switching display: hiding video, showing canvas');
    video.style.display = 'none';
    canvas.style.display = 'block';
    console.log('📸 Final display states - video:', video.style.display, 'canvas:', canvas.style.display);

    alert("✅ Face captured! Checking for a clear face, then click 'Login' to authenticate.");
    console.log('📸 ✅ captureImage() completed successfully');
}

function resetCapture() {
    console.log('🔄 resetCapture() called');
    
    // Clear the form
    console.log('🔄 Clearing form inputs');
    document.getElementById('imageInput').value = '';
    document.getElementById('checkGlasses').value = 'true';
    
    // Show video again, hide canvas
    console.log('🔄 Setting display - video: block, canvas: none');
    video.style.display = 'block';
    canvas.style.display = 'none';
    console.log('🔄 Display set - video:', video.style.display, 'canvas:', canvas.style.display);
    
    // Always restart the camera to ensure fresh stream
    console.log('🔄 Restarting camera to ensure fresh stream');
    window.startCamera();
    
    // Clear status message and face check errors
    const statusEl = document.getElementById('statusMessage');
    if (statusEl) {
        console.log('🔄 Clearing status message');
        statusEl.innerHTML = '';
    }
    const faceCheckError = document.getElementById('faceCheckError');
    if (faceCheckError) {
        console.log('🔄 Removing faceCheckError element');
        faceCheckError.remove();
    }
    
    // Disable the Login button again since there's no image
    if (loginBtn) {
        console.log('🔄 Disabling login button');
        loginBtn.disabled = true;
    
        // Re-enable wrapper disabled styling for tooltip and update ARIA
        if (loginWrapper) loginWrapper.classList.add('disabled');
        loginBtn.setAttribute('aria-disabled', 'true');
    }
    
    console.log('🔄 ✅ resetCapture() completed');
}

/* ============================================
   LOGIN HANDLER WITH PROCESSING MODAL
   ============================================ */

async function handleLogin() {
    console.log('🔐 handleLogin() called');
    
    const imageValue = document.getElementById('imageInput').value;
    const checkGlassesValue = document.getElementById('checkGlasses').value;
    
    if (!imageValue) {
        console.log('🔐 ❌ No image captured');
        showErrorModal('Please capture an image first!', 2);
        return;
    }
    
    console.log('🔐 Sending AJAX request to check for errors...');
    
    // Show processing modal
    showProcessingModal();
    
    // Also show temporary status in status message
    const statusEl = document.getElementById('statusMessage');
    if (statusEl) {
        statusEl.innerHTML = '<p style="color:blue;">🔄 Processing login...</p>';
    }
    
    try {
        const formData = new FormData();
        formData.append('image', imageValue);
        formData.append('check_glasses', checkGlassesValue);
        formData.append('ajax_check', 'true');
        
        const response = await fetch('kioskLogin.php', {
            method: 'POST',
            body: formData
        });
        
        console.log('🔐 Response status:', response.status);
        
        if (!response.ok) {
            hideProcessingModal();
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const responseText = await response.text();
        console.log('🔐 Response length:', responseText.length);
        
        // Check if response indicates success
        const isSuccess = responseText.trim() === 'SUCCESS_RECOGNIZED';
        
        // Check if response contains error indicators
        const hasError = !isSuccess && (responseText.includes('window.serverCaptureReset') || 
                        responseText.includes('❌') || 
                        responseText.includes('Error:') ||
                        responseText.includes('Unknown user') ||
                        responseText.includes('accessory_detected') ||
                        responseText.includes('no_person_found'));
        
        console.log('🔐 Success detected:', isSuccess);
        console.log('🔐 Error detected in response:', hasError);
        
        if (isSuccess) {
            console.log('🔐 ✅ Recognition successful - proceeding with normal form submission');
            // Keep processing modal visible, clear status message
            if (statusEl) {
                statusEl.innerHTML = '';
            }
            // Remove the ajax_check flag and submit normally (welcome modal will show from PHP)
            const form = document.getElementById('loginForm');
            const ajaxCheckInput = form.querySelector('input[name="ajax_check"]');
            if (ajaxCheckInput) {
                ajaxCheckInput.remove();
            }
            form.submit();
        } else if (hasError) {
            console.log('🔐 Handling error response with modal');
            
            // Hide processing modal first
            hideProcessingModal();
            
            // Parse and extract error information
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = responseText;
            
            // Look for serverCaptureReset script
            const scripts = tempDiv.querySelectorAll('script');
            scripts.forEach(script => {
                if (script.textContent.includes('window.serverCaptureReset')) {
                    console.log('🔐 Executing serverCaptureReset script');
                    eval(script.textContent);
                    // Apply the reset immediately
                    if (window.serverCaptureReset) {
                        applyServerCaptureReset();
                    }
                }
            });
            
            // Extract error message for modal display
            let errorMessage = '';
            
            // Try different selectors for error messages
            const errorElements = tempDiv.querySelectorAll('p[style*="color:red"], p[style*="color:orange"], #faceCheckError p');
            
            if (errorElements.length > 0) {
                // Get the error HTML (preserve links)
                const errorHTML = errorElements[0].innerHTML;
                // Remove the ❌ emoji if present
                errorMessage = errorHTML.replace('❌ ', '').trim();
            } else if (tempDiv.querySelector('#faceCheckError')) {
                // Extract from faceCheckError div
                const faceCheckDiv = tempDiv.querySelector('#faceCheckError');
                errorMessage = faceCheckDiv.textContent.trim();
            } else if (responseText.includes('Unknown user')) {
                errorMessage = 'Unknown user. Please register first.';
            } else if (responseText.includes('accessory_detected')) {
                errorMessage = 'Accessory detected. Please remove glasses, hats, or masks.';
            } else if (responseText.includes('no_person_found')) {
                errorMessage = 'No person detected. Please try again.';
            } else if (responseText.includes('Error:')) {
                // Extract error message from generic error
                const errorMatch = responseText.match(/Error:([^<]+)/);
                errorMessage = errorMatch ? errorMatch[1].trim() : 'An error occurred. Please try again.';
            } else {
                errorMessage = 'An error occurred during login. Please try again.';
            }
            
            // Show error modal for 2 seconds
            showErrorModal(errorMessage, 2);
            
            // Clear processing status
            if (statusEl) {
                statusEl.innerHTML = '';
            }
            
        } else {
            console.log('🔐 ✅ No errors detected - proceeding with normal form submission');
            // Keep processing modal, clear status
            if (statusEl) {
                statusEl.innerHTML = '';
            }
            // Submit the form normally for successful cases
            document.getElementById('loginForm').submit();
        }
        
    } catch (error) {
        console.error('🔐 ❌ Login check failed:', error);
        
        // Hide processing modal
        hideProcessingModal();
        
        // Show error modal
        showErrorModal('Request failed: ' + error.message, 2);
        
        // Clear processing status
        if (statusEl) {
            statusEl.innerHTML = '';
        }
        
        // On network errors, still try normal form submission as fallback
        setTimeout(() => {
            document.getElementById('loginForm').submit();
        }, 2000);
    }
}

/* ============================================
   SERVER RESET HANDLER
   ============================================ */

function applyServerCaptureReset() {
    try {
        const cfg = window.serverCaptureReset;
        console.log('🔄 applyServerCaptureReset called, config:', cfg);
        if (!cfg) {
            console.log('🔄 No serverCaptureReset config found, exiting');
            return;
        }

        console.log('🔄 Processing server capture reset with message:', cfg.message);

        // Make sure DOM elements exist
        const videoEl = document.getElementById('video');
        const canvasEl = document.getElementById('canvas');
        const loginBtnEl = document.getElementById('loginBtn');
        const loginWrapperEl = document.getElementById('loginWrapper');
        const imageInputEl = document.getElementById('imageInput');
        const checkGlassesEl = document.getElementById('checkGlasses');

        console.log('🔄 DOM elements found:', {
            video: !!videoEl,
            canvas: !!canvasEl,
            loginBtn: !!loginBtnEl,
            loginWrapper: !!loginWrapperEl,
            imageInput: !!imageInputEl,
            checkGlasses: !!checkGlassesEl
        });

        // Clear captured image and flags
        if (imageInputEl) {
            console.log('🔄 Clearing imageInput value');
            imageInputEl.value = '';
        }
        if (checkGlassesEl) {
            console.log('🔄 Resetting checkGlasses to true');
            checkGlassesEl.value = 'true';
        }

        // Show video, hide canvas
        console.log('🔄 Current video display:', videoEl ? videoEl.style.display : 'N/A');
        console.log('🔄 Current canvas display:', canvasEl ? canvasEl.style.display : 'N/A');
        
        if (videoEl) {
            videoEl.style.display = 'block';
            console.log('🔄 Set video display to block');
        }
        if (canvasEl) {
            canvasEl.style.display = 'none';
            console.log('🔄 Set canvas display to none');
        }

        // Always ensure video is playing and visible
        if (videoEl) {
            console.log('🔄 Video element state:', {
                paused: videoEl.paused,
                readyState: videoEl.readyState,
                srcObject: !!videoEl.srcObject,
                display: videoEl.style.display
            });
            
            if (videoEl.paused) {
                console.log('🔄 Video is paused, attempting to play...');
                videoEl.play()
                    .then(() => console.log('🔄 ✅ Video play success'))
                    .catch(e => console.log('🔄 ❌ Video play error:', e.name, e.message));
            }
        }

        // Always restart the camera when server requests reset
        console.log('🔄 Always restarting camera due to server error');
        console.log('🔄 Current videoStream state before restart:', {
            exists: !!window.videoStream,
            active: window.videoStream ? window.videoStream.active : 'N/A'
        });
        
        if (typeof window.startCamera === 'function') {
            console.log('🔄 Calling startCamera() immediately');
            window.startCamera();
        } else {
            console.log('🔄 startCamera not ready, scheduling retry in 200ms');
            // If startCamera isn't ready yet, try again shortly
            setTimeout(() => {
                if (typeof window.startCamera === 'function') {
                    console.log('🔄 Calling startCamera() after delay');
                    window.startCamera();
                } else {
                    console.log('🔄 ❌ startCamera still not available after delay');
                }
            }, 200);
        }

        // Disable login UI if server requested it
        if (cfg.disableLogin && loginBtnEl) {
            console.log('🔄 Disabling login button');
            loginBtnEl.disabled = true;
            loginBtnEl.setAttribute('aria-disabled', 'true');
            if (loginWrapperEl) loginWrapperEl.classList.add('disabled');
        }

        // Remove server-side face check block if present
        const faceCheckErrorEl = document.getElementById('faceCheckError');
        if (faceCheckErrorEl) {
            console.log('🔄 Removing faceCheckError element');
            faceCheckErrorEl.remove();
        }

        // Show error message in modal instead of status message
        if (cfg.message) {
            console.log('🔄 Showing error in modal:', cfg.message);
            // Clean up the message (remove HTML tags, keep text)
            const cleanMessage = cfg.message.replace(/<[^>]*>/g, ' ').trim();
            // Show modal for 2 seconds
            showErrorModal(cleanMessage, 2);
        }

        // Clear the global so it does not run more than once
        console.log('🔄 Clearing serverCaptureReset flag');
        delete window.serverCaptureReset;
        
        console.log('🔄 ✅ applyServerCaptureReset completed');
    } catch (e) {
        console.error('🔄 ❌ applyServerCaptureReset error:', e);
        // Show error in modal
        showErrorModal('An error occurred while resetting: ' + e.message, 2);
    }
}

/* ============================================
   INITIALIZATION
   ============================================ */

// Initialize camera and apply any server-sent reset instructions on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Start camera initially
    window.startCamera();
    
    // Run the correction after DOM is ready (and after startCamera declared)
    applyServerCaptureReset();
});