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
    <link rel="stylesheet" href="../css/kioskRegister.css">
    <!-- Floating Keyboard CSS -->
    <link rel="stylesheet" href="../css/floating-keyboard.css">
    <!-- <link rel="stylesheet" href="../css/backButton.css"> -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div class="register-container">
        <!-- VISITOR FORM (shown first) -->
        <div id="visitorForm" class="visitor-screen">

            <h1 class="title">Face Registration</h1>
            <p class="muted small">Please enter your details before proceeding with face registration</p>

            <form id="visitorDetails" onsubmit="return false;">
                <label class="vf-field">
                    <div class="vf-label">Full Name</div>
                    <input type="text" id="visitorName" placeholder="Enter your full name" autocomplete="name" required />
                </label>

                <label class="vf-field">
                    <div class="vf-label">Phone Number</div>
                    <input type="tel" id="visitorPhone" placeholder="09XXXXXXXXX" inputmode="numeric" required />
                    <div id="visitorPhoneHint" class="vf-hint" style="display:none;">Phone must be 11 digits and start with 09</div>
                </label>

                <label class="vf-field">
                    <div class="vf-label">Email Address</div>
                    <input type="email" id="visitorEmail" placeholder="you@example.com" autocomplete="email" required />
                    <div id="visitorEmailHint" class="vf-hint" style="display:none;">Please enter a valid email address</div>
                </label>

                <div class="vf-actions">
                    <button id="visitorProceed" class="primary-btn" type="button" disabled>Proceed to Face Capture</button>
                    <!-- Back Button -->
                    <a href="../includes/kioskSelect.php" class="back-btn" title="Go back to selection">
                        <!-- <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.42-1.41L7.83 13H20v-2z"/>
                        </svg> -->
                        Go Back
                    </a>
                </div>
            </form>
        </div>

        <!-- CONFIRMATION MODAL -->
        <div id="visitorConfirmModal" class="vf-modal" style="display:none">
            <div class="vf-modal-card">
                <h2>CONFIRM DETAILS</h2>
                <div class="confirm-instruction">
                Please review your information carefully before proceeding
            </div>
                <div class="vf-confirm-row"><strong>Name:</strong> <span id="confirmName"></span></div>
                <div class="vf-confirm-row"><strong>Phone:</strong> <span id="confirmPhone"></span></div>
                <div class="vf-confirm-row"><strong>Email:</strong> <span id="confirmEmail"></span></div>

                <div class="vf-actions" style="margin-top:20px">
                    <button id="visitorConfirm" class="confirm-btn" type="button">Confirm & Start Face Capture</button>
                    <button id="visitorCancel" class="ghost-btn" type="button" style="margin-top:10px;">Go Back</button>
                </div>
            </div>
        </div>

        <!-- CAMERA SCREEN (shown after confirmation) -->
        <div id="cameraScreen" class="camera-screen">
            <h1 class="title">Capture Photos</h1>

            <div class="instruction-panel">
                <h3 id="currentAngle">Front View</h3>
                <p id="angleInstruction">Please look straight at the camera for a clear front view of your face.</p>
            </div>
            <div class="camera-container">
                <video id="video" autoplay playsinline></video>
                <canvas id="canvas" style="display:none;"></canvas>
                <div id="faceOutline" aria-hidden="true"></div>
            </div>

            <div class="capture-guide" hidden>
                <div>
                    <h4>
                        <span id="guideCounter">1 / 5</span> - <span id="guideTitle">Front View</span>
                    </h4>
                    <p id="guideTip">Look straight ahead, keep your face centered and well-lit. This is your primary reference image.</p>
                    <p class="small-muted">Tip: Remove hats and glasses; ensure good lighting and hold still when capturing.</p>
                </div>
            </div>

            <div class="form-container">
                <button type="button" id="captureBtn">📸 Capture Face</button>
                <div class="small-muted">Capture 5 angles. The app will submit automatically when done.</div>
            </div>

            <p id="progress">Images captured: <span id="count">0</span> / 5</p>
            <div id="statusMessage" role="status" aria-live="polite"></div>
        </div>
    </div>

    <!-- FLOATING KEYBOARD - Always present but hidden by default -->
    <div id="floatingKeyboardContainer" class="floating-keyboard-container">
        <!-- Main keyboard (letters and numbers) -->
        <div id="mainKeyboard" class="keyboard-wrapper">
            <!-- Row 1 -->
            <div class="keyboard-row">
                <button class="keyboard-key" data-key="q">q</button>
                <button class="keyboard-key" data-key="w">w</button>
                <button class="keyboard-key" data-key="e">e</button>
                <button class="keyboard-key" data-key="r">r</button>
                <button class="keyboard-key" data-key="t">t</button>
                <button class="keyboard-key" data-key="y">y</button>
                <button class="keyboard-key" data-key="u">u</button>
                <button class="keyboard-key" data-key="i">i</button>
                <button class="keyboard-key" data-key="o">o</button>
                <button class="keyboard-key" data-key="p">p</button>
            </div>
            <!-- Row 2 -->
            <div class="keyboard-row">
                <button class="keyboard-key" data-key="a">a</button>
                <button class="keyboard-key" data-key="s">s</button>
                <button class="keyboard-key" data-key="d">d</button>
                <button class="keyboard-key" data-key="f">f</button>
                <button class="keyboard-key" data-key="g">g</button>
                <button class="keyboard-key" data-key="h">h</button>
                <button class="keyboard-key" data-key="j">j</button>
                <button class="keyboard-key" data-key="k">k</button>
                <button class="keyboard-key" data-key="l">l</button>
                <button class="keyboard-key keyboard-action" data-action="backspace">⌫</button>
            </div>
            <!-- Row 3 -->
            <div class="keyboard-row">
                <button class="keyboard-key keyboard-action" id="shiftBtn" data-action="shift">⇧</button>
                <button class="keyboard-key" data-key="z">z</button>
                <button class="keyboard-key" data-key="x">x</button>
                <button class="keyboard-key" data-key="c">c</button>
                <button class="keyboard-key" data-key="v">v</button>
                <button class="keyboard-key" data-key="b">b</button>
                <button class="keyboard-key" data-key="n">n</button>
                <button class="keyboard-key" data-key="m">m</button>
                <button class="keyboard-key" data-key=",">,</button>
                <button class="keyboard-key" data-key=".">.</button>
            </div>
            <!-- Row 4 - Numbers and symbols -->
            <div class="keyboard-row">
                <button class="keyboard-key" data-key="1">1</button>
                <button class="keyboard-key" data-key="2">2</button>
                <button class="keyboard-key" data-key="3">3</button>
                <button class="keyboard-key" data-key="4">4</button>
                <button class="keyboard-key" data-key="5">5</button>
                <button class="keyboard-key" data-key="6">6</button>
                <button class="keyboard-key" data-key="7">7</button>
                <button class="keyboard-key" data-key="8">8</button>
                <button class="keyboard-key" data-key="9">9</button>
                <button class="keyboard-key" data-key="0">0</button>
            </div>
            <!-- Row 5 - Space and specials -->
            <div class="keyboard-row">
                <button class="keyboard-key keyboard-action" data-action="symbols">#+=</button>
                <button class="keyboard-key keyboard-space" data-action="space">Space</button>
                <button class="keyboard-key keyboard-action" data-action="clear">Clear</button>
                <button class="keyboard-key keyboard-action" data-action="done">Done</button>
            </div>
        </div>

        <!-- Symbol keyboard (special characters) -->
        <div id="symbolKeyboard" class="keyboard-wrapper" style="display: none;">
            <!-- Row 1 -->
            <div class="keyboard-row">
                <button class="keyboard-key" data-key="!">!</button>
                <button class="keyboard-key" data-key="@">@</button>
                <button class="keyboard-key" data-key="#">#</button>
                <button class="keyboard-key" data-key="$">$</button>
                <button class="keyboard-key" data-key="%">%</button>
                <button class="keyboard-key" data-key="^">^</button>
                <button class="keyboard-key" data-key="&">&</button>
                <button class="keyboard-key" data-key="*">*</button>
                <button class="keyboard-key" data-key="(">(</button>
                <button class="keyboard-key" data-key=")">)</button>
            </div>
            <!-- Row 2 -->
            <div class="keyboard-row">
                <button class="keyboard-key" data-key="-">-</button>
                <button class="keyboard-key" data-key="_">_</button>
                <button class="keyboard-key" data-key="=">=</button>
                <button class="keyboard-key" data-key="+">+</button>
                <button class="keyboard-key" data-key="[">[</button>
                <button class="keyboard-key" data-key="]">]</button>
                <button class="keyboard-key" data-key="{">{</button>
                <button class="keyboard-key" data-key="}">}</button>
                <button class="keyboard-key" data-key="\">\</button>
                <button class="keyboard-key keyboard-action" data-action="backspace">⌫</button>
            </div>
            <!-- Row 3 -->
            <div class="keyboard-row">
                <button class="keyboard-key" data-key="|">|</button>
                <button class="keyboard-key" data-key=";">;</button>
                <button class="keyboard-key" data-key=":">:</button>
                <button class="keyboard-key" data-key="'">'</button>
                <button class="keyboard-key" data-key='"'>"</button>
                <button class="keyboard-key" data-key=",">,</button>
                <button class="keyboard-key" data-key="<"><</button>
                <button class="keyboard-key" data-key=".">.</button>
                <button class="keyboard-key" data-key=">">></button>
                <button class="keyboard-key" data-key="/">/</button>
            </div>
            <!-- Row 4 -->
            <div class="keyboard-row">
                <button class="keyboard-key" data-key="?">?</button>
                <button class="keyboard-key" data-key="`">`</button>
                <button class="keyboard-key" data-key="~">~</button>
                <button class="keyboard-key keyboard-action" data-action="letters">ABC</button>
                <button class="keyboard-key keyboard-space" data-action="space">Space</button>
                <button class="keyboard-key keyboard-action" data-action="clear">Clear</button>
                <button class="keyboard-key keyboard-action" data-action="done">Done</button>
            </div>
        </div>
    </div>

    <!-- Include the floating keyboard JavaScript -->
    <script src="../js/floating-keyboard.js"></script>

    <script>
        // Expose API base for JavaScript
        window.PYTHON_API_BASE = <?= json_encode(PYTHON_API_BASE) ?>;
    </script>
    <script src="../js/kioskRegister.js"></script>

</body>

</html>