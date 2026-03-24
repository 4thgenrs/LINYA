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
    <link rel="icon" href="../resources/linya_logo.png" type="image/png">
    <link rel="stylesheet" href="../css/kioskRegister.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- ===== FLOATING KEYBOARD STYLES (same as kioskMenu.php) ===== -->
    <style>
        #floatingKeyboardContainer {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: linear-gradient(180deg, #ffffff 0%, #f0faf4 100%);
            border-top: 3px solid #2ada8d;
            border-radius: 20px 20px 0 0;
            padding: 14px 10px 22px;
            box-shadow: 0 -6px 32px rgba(9, 133, 37, 0.18), 0 -2px 8px rgba(9,133,37,0.08);
            z-index: 99999;
            transform: translateY(100%);
            transition: transform 0.3s ease;
            font-family: 'Segoe UI', system-ui, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            width: 100%;
            overflow: hidden;
        }

        #floatingKeyboardContainer.active { transform: translateY(0); }

        /* Drag pill */
        #floatingKeyboardContainer::before {
            content: '';
            display: block;
            width: 44px; height: 5px;
            background: linear-gradient(90deg, #098525, #2ada8d);
            border-radius: 3px;
            margin: 0 auto 12px;
            opacity: 0.5;
            position: relative;
            z-index: 1;
        }

        /* ── Watermark logo behind all keys ── */
        .kb-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 260px;
            height: 260px;
            opacity: 0.50;
            pointer-events: none;
            z-index: 0;
        }
        .kb-watermark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .keyboard-section { width: 100%; }

        .keyboard-row {
            display: flex;
            justify-content: center;
            margin: 6px 0;
            gap: 6px;
            position: relative;
            z-index: 1;
            align-items: center;
        }

        .keyboard-key {
            background: #ffffff;
            border: 1.5px solid rgba(9,133,37,0.20);
            border-radius: 12px;
            color: #0b1230;
            font-size: 24px;
            font-weight: 600;
            padding: 15px 0;
            min-width: 60px;
            text-align: center;
            cursor: pointer;
            user-select: none;
            transition: background 0.12s ease, transform 0.08s ease, box-shadow 0.12s ease;
            box-shadow: 0 3px 0 rgba(9,133,37,0.18), 0 1px 3px rgba(0,0,0,0.05);
            flex: 1 1 auto;
            max-width: 80px;
            display: flex;
            opacity: 0.8;
            align-items: center;
            justify-content: center;
        }

        .keyboard-key:hover {
            background: linear-gradient(135deg, #edfff5, #d4f9e6);
            border-color: rgba(9,133,37,0.40);
            box-shadow: 0 4px 0 rgba(9,133,37,0.25), 0 2px 6px rgba(9,133,37,0.12);
        }
        .keyboard-key:active {
            transform: translateY(4px);
            box-shadow: 0 0 0 rgba(9,133,37,0.18);
            background: #d0f5e3;
        }

        .keyboard-key.wide       { min-width: 100px; max-width: 120px; flex: 1.5; }
        .keyboard-key.extra-wide { min-width: 200px; max-width: 250px; flex: 3; font-size: 18px; }

        .keyboard-key.space {
            background: linear-gradient(135deg, #f0faf4, #e3f8ee);
            color: #098525;
            font-weight: 700;
            letter-spacing: 0.04em;
            border-color: rgba(9,133,37,0.25);
        }
        .keyboard-key.space:hover { background: linear-gradient(135deg, #d4f9e6, #b8f0d4); }

        .keyboard-key.done {
            background: linear-gradient(135deg, #098525, #2ada8d);
            color: white;
            border-color: #0fb8a0;
            font-weight: 700;
            box-shadow: 0 3px 0 rgba(7,93,20,0.35), 0 2px 8px rgba(9,133,37,0.25);
        }
        .keyboard-key.done:hover  { background: linear-gradient(135deg, #0a9c2d, #36e89a); }
        .keyboard-key.done:active { transform: translateY(4px); box-shadow: 0 0 0; }

        /* Shift highlighted state */
        .keyboard-key.shift-active {
            background: linear-gradient(135deg, #098525, #2ada8d) !important;
            color: white !important;
            border-color: #2ada8d !important;
        }

        .keyboard-key.symbol { font-size: 20px; }

        /* Backspace / Clear */
        .keyboard-key[data-action="backspace"],
        .keyboard-key[data-action="clear"] {
            background: linear-gradient(135deg, #fff5f5, #ffe8e8);
            color: #e53935;
            border-color: rgba(229,57,53,0.22);
            box-shadow: 0 3px 0 rgba(229,57,53,0.15);
        }
        .keyboard-key[data-action="backspace"]:hover,
        .keyboard-key[data-action="clear"]:hover {
            background: linear-gradient(135deg, #ffe8e8, #ffd0d0);
            border-color: rgba(229,57,53,0.38);
        }

        /* Symbols / ABC toggle */
        .keyboard-key[data-action="symbols"],
        .keyboard-key[data-action="letters"] {
            background: linear-gradient(135deg, #f0faf4, #e0f5eb);
            color: #098525;
            font-weight: 700;
            border-color: rgba(9,133,37,0.22);
        }
        .keyboard-key[data-action="symbols"]:hover,
        .keyboard-key[data-action="letters"]:hover {
            background: linear-gradient(135deg, #d4f9e6, #b8f0d4);
        }

        #symbolKeyboard { display: none; }

        @media (max-width: 600px) {
            .keyboard-key { font-size: 20px; padding: 12px 0; min-width: 45px; }
            .keyboard-key.extra-wide { min-width: 150px; font-size: 16px; }
            .kb-watermark { width: 200px; height: 200px; }
        }

        @media (max-width: 400px) {
            .keyboard-key { font-size: 18px; padding: 10px 0; min-width: 35px; }
            .keyboard-row { gap: 4px; }
            .kb-watermark { width: 160px; height: 160px; }
        }

        input, textarea { margin-bottom: 10px; }
        body.keyboard-visible { padding-bottom: 350px; }
        .vf-modal { z-index: 10001; }
    </style>
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

                <!-- Student checkbox -->
                <label class="vf-field">
                    <div class="student-checkbox-row">
                        <input type="checkbox" id="visitorIsStudent" />
                        <div class="vf-label">Are you a student?</div>
                    </div>
                    <!-- Badge shown after student modal is confirmed -->
                    <div id="studentBadge" class="student-badge" style="display:none;">
                        <span class="badge-icon">🎓</span>
                        <div class="badge-info">
                            <div class="badge-name" id="badgeStudentNumber"></div>
                            <div class="badge-course" id="badgeCourse"></div>
                        </div>
                        <button type="button" class="badge-edit" id="studentBadgeEdit" title="Edit student info">✏️</button>
                    </div>
                </label>

                <!-- Hidden fields populated by student modal -->
                <input type="hidden" id="visitorStudentNumber" />
                <input type="hidden" id="visitorCourse" />

                <div class="vf-actions">
                    <button id="visitorProceed" class="primary-btn" type="button" disabled>Proceed to Face Capture</button>
                    <a href="../includes/kioskSelect.php" class="back-btn" title="Go back to selection">Go Back</a>
                </div>
            </form>
        </div>

        <!-- ===== STUDENT DETAILS MODAL ===== -->
        <div id="studentModal" class="vf-modal" style="display:none">
            <div class="vf-modal-card">
                <h2>STUDENT DETAILS</h2>
                <p class="student-modal-subtitle">Please enter your student information</p>

                <label class="vf-field">
                    <div class="vf-label">Student Number</div>
                    <input type="text" id="modalStudentNumber" placeholder="20001234-S" maxlength="20" />
                    <div id="modalStudentNumberHint" class="vf-hint" style="display:none;color:#ff0000">Format: 20001234-S (year 2000–2026)</div>
                </label>

                <label class="vf-field">
                    <div class="vf-label">Course</div>
                    <select id="modalCourse">
                        <option value="">-- Select Course --</option>
                        <option>BA COMM</option>
                        <option>BA POS</option>
                        <option>BPA</option>
                        <option>BPA ECGE</option>
                        <option>BECED</option>
                        <option>BSA</option>
                        <option>BSAIS</option>
                        <option>BSBA FMGT</option>
                        <option>BSBA HRM</option>
                        <option>BSBA MKTG</option>
                        <option>BSCS</option>
                        <option>BS CRIM</option>
                        <option>BSEMC</option>
                        <option>BS ENTREP</option>
                        <option>BSHM</option>
                        <option>BSISM</option>
                        <option>BSIS</option>
                        <option>BSIT</option>
                        <option>BS MATH</option>
                        <option>BSOAD</option>
                        <option>BS PSY</option>
                        <option>BSSW</option>
                        <option>BSTM</option>
                        <option>BSE ENG</option>
                        <option>BSE ENG-CHI</option>
                        <option>BSE SCI</option>
                        <option>BTLED HE</option>
                    </select>
                </label>

                <div class="student-modal-actions">
                    <button id="studentModalConfirm" class="vf-modal-confirm" type="button">Confirm</button>
                    <button id="studentModalCancel" class="vf-modal-ghost" type="button">Cancel</button>
                </div>
            </div>
        </div>

        <!-- CONFIRMATION MODAL -->
        <div id="visitorConfirmModal" class="vf-modal" style="display:none">
            <div class="vf-modal-card">
                <h2>CONFIRM DETAILS</h2>
                <div class="confirm-instruction">
                    Please review your information carefully before proceeding
                </div>
                <div class="vf-confirm-row"><strong>Name:</strong>  <span id="confirmName"></span></div>
                <div class="vf-confirm-row"><strong>Phone:</strong> <span id="confirmPhone"></span></div>
                <div class="vf-confirm-row"><strong>Email:</strong> <span id="confirmEmail"></span></div>
                <div class="vf-confirm-row" id="confirmStudentRow" style="display:none;"><strong>Student:</strong> <span id="confirmStudent"></span></div>

                <div class="vf-actions" style="margin-top:20px">
                    <button id="visitorConfirm" class="confirm-btn" type="button">Confirm &amp; Start Face Capture</button>
                    <button id="visitorCancel"  class="ghost-btn"   type="button" style="margin-top:10px;">Go Back</button>
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

    <!-- ===== STUDENT DETAILS MODAL ===== -->
    <div id="studentModal" style="display:none;">
        <div class="vf-modal-card">
            <h2>STUDENT DETAILS</h2>
            <p class="student-modal-subtitle">Please enter your student information</p>

            <label class="vf-field">
                <div class="vf-label">Student Number</div>
                <input type="text" id="modalStudentNumber" placeholder="20001234-S" maxlength="20" />
                <div id="modalStudentNumberHint" class="vf-hint" style="display:none;color:#ff0000">
                    Format: 20001234-S (year 2000–2026)
                </div>
            </label>

            <label class="vf-field">
                <div class="vf-label">Course</div>
                <select id="modalCourse">
                    <option value="">-- Select Course --</option>
                    <option>BA COMM</option>
                    <option>BA POS</option>
                    <option>BPA</option>
                    <option>BPA ECGE</option>
                    <option>BECED</option>
                    <option>BSA</option>
                    <option>BSAIS</option>
                    <option>BSBA FMGT</option>
                    <option>BSBA HRM</option>
                    <option>BSBA MKTG</option>
                    <option>BSCS</option>
                    <option>BS CRIM</option>
                    <option>BSEMC</option>
                    <option>BS ENTREP</option>
                    <option>BSHM</option>
                    <option>BSISM</option>
                    <option>BSIS</option>
                    <option>BSIT</option>
                    <option>BS MATH</option>
                    <option>BSOAD</option>
                    <option>BS PSY</option>
                    <option>BSSW</option>
                    <option>BSTM</option>
                    <option>BSE ENG</option>
                    <option>BSE ENG-CHI</option>
                    <option>BSE SCI</option>
                    <option>BTLED HE</option>
                </select>
            </label>

            <div class="student-modal-actions">
                <button id="studentModalConfirm" class="vf-modal-confirm" type="button">Confirm</button>
                <button id="studentModalCancel"  class="vf-modal-ghost"   type="button">Cancel</button>
            </div>
        </div>
    </div>

    <!-- ===== FLOATING KEYBOARD (exact same as kioskMenu.php) ===== -->
    <div id="floatingKeyboardContainer">

        <!-- Big watermark logo behind all keys -->
        <div class="kb-watermark">
            <img src="../resources/linya_logo.png" alt="" />
        </div>

        <!-- Main Letters Keyboard -->
        <div id="mainKeyboard" class="keyboard-section">
            <div class="keyboard-row">
                <div class="keyboard-key" data-key="q">q</div><div class="keyboard-key" data-key="w">w</div>
                <div class="keyboard-key" data-key="e">e</div><div class="keyboard-key" data-key="r">r</div>
                <div class="keyboard-key" data-key="t">t</div><div class="keyboard-key" data-key="y">y</div>
                <div class="keyboard-key" data-key="u">u</div><div class="keyboard-key" data-key="i">i</div>
                <div class="keyboard-key" data-key="o">o</div><div class="keyboard-key" data-key="p">p</div>
            </div>
            <div class="keyboard-row">
                <div class="keyboard-key" data-key="a">a</div><div class="keyboard-key" data-key="s">s</div>
                <div class="keyboard-key" data-key="d">d</div><div class="keyboard-key" data-key="f">f</div>
                <div class="keyboard-key" data-key="g">g</div><div class="keyboard-key" data-key="h">h</div>
                <div class="keyboard-key" data-key="j">j</div><div class="keyboard-key" data-key="k">k</div>
                <div class="keyboard-key" data-key="l">l</div>
            </div>
            <div class="keyboard-row">
                <div class="keyboard-key wide" data-action="shift" id="shiftBtn">⬆️</div>
                <div class="keyboard-key" data-key="z">z</div><div class="keyboard-key" data-key="x">x</div>
                <div class="keyboard-key" data-key="c">c</div><div class="keyboard-key" data-key="v">v</div>
                <div class="keyboard-key" data-key="b">b</div><div class="keyboard-key" data-key="n">n</div>
                <div class="keyboard-key" data-key="m">m</div>
                <div class="keyboard-key wide" data-action="backspace">⌫</div>
            </div>
            <div class="keyboard-row">
                <div class="keyboard-key wide"       data-action="symbols">!?#</div>
                <div class="keyboard-key extra-wide space" data-action="space">Space</div>
                <div class="keyboard-key wide"       data-action="clear">Clear</div>
                <div class="keyboard-key wide done"  data-action="done">Done</div>
            </div>
        </div>

        <!-- Symbols Keyboard -->
        <div id="symbolKeyboard" class="keyboard-section">
            <div class="keyboard-row">
                <div class="keyboard-key" data-key="1">1</div><div class="keyboard-key" data-key="2">2</div>
                <div class="keyboard-key" data-key="3">3</div><div class="keyboard-key" data-key="4">4</div>
                <div class="keyboard-key" data-key="5">5</div><div class="keyboard-key" data-key="6">6</div>
                <div class="keyboard-key" data-key="7">7</div><div class="keyboard-key" data-key="8">8</div>
                <div class="keyboard-key" data-key="9">9</div><div class="keyboard-key" data-key="0">0</div>
            </div>
            <div class="keyboard-row">
                <div class="keyboard-key" data-key="!">!</div><div class="keyboard-key" data-key="@">@</div>
                <div class="keyboard-key" data-key="#">#</div><div class="keyboard-key" data-key="$">$</div>
                <div class="keyboard-key" data-key="%">%</div><div class="keyboard-key" data-key="^">^</div>
                <div class="keyboard-key" data-key="&">&amp;</div><div class="keyboard-key" data-key="*">*</div>
                <div class="keyboard-key" data-key="(">(</div><div class="keyboard-key" data-key=")">)</div>
            </div>
            <div class="keyboard-row">
                <div class="keyboard-key" data-key="-">-</div><div class="keyboard-key" data-key="_">_</div>
                <div class="keyboard-key" data-key="=">=</div><div class="keyboard-key" data-key="+">+</div>
                <div class="keyboard-key" data-key="[">[</div><div class="keyboard-key" data-key="]">]</div>
                <div class="keyboard-key" data-key="{">{</div><div class="keyboard-key" data-key="}">}</div>
                <div class="keyboard-key" data-key="\">\</div><div class="keyboard-key" data-key="|">|</div>
            </div>
            <div class="keyboard-row">
                <div class="keyboard-key" data-key=";">;</div><div class="keyboard-key" data-key=":">:</div>
                <div class="keyboard-key" data-key="'">'</div><div class="keyboard-key" data-key='"'>"</div>
                <div class="keyboard-key" data-key=",">,</div><div class="keyboard-key" data-key=".">.</div>
                <div class="keyboard-key" data-key="?">?</div><div class="keyboard-key" data-key="/">/</div>
                <div class="keyboard-key wide" data-action="backspace">⌫</div>
            </div>
            <div class="keyboard-row">
                <div class="keyboard-key wide"       data-action="letters">ABC</div>
                <div class="keyboard-key extra-wide space" data-action="space">Space</div>
                <div class="keyboard-key wide"       data-action="clear">Clear</div>
                <div class="keyboard-key wide done"  data-action="done">Done</div>
            </div>
        </div>
    </div>

    <!-- ===== FLOATING KEYBOARD JS (exact same as kioskMenu.php) ===== -->
    <script>
    (function () {
        'use strict';
        let activeInput = null, isShifted = false, currentKeyboard = 'letters', isKeyboardVisible = false;

        function initUniversalKeyboard() {
            const container      = document.getElementById('floatingKeyboardContainer');
            const mainKeyboard   = document.getElementById('mainKeyboard');
            const symbolKeyboard = document.getElementById('symbolKeyboard');
            if (!container || !mainKeyboard || !symbolKeyboard) return;
            setupInputListeners();
            setupKeyboardButtons();
            document.addEventListener('click', function (e) {
                if (!isKeyboardVisible) return;
                if (!container.contains(e.target) && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') hideKeyboard();
            });
            container.addEventListener('click', e => e.stopPropagation());
        }

        function setupInputListeners() {
            document.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]), textarea').forEach(input => {
                if (input.dataset.keyboardAttached) return;
                input.dataset.keyboardAttached = 'true';
                input.addEventListener('mousedown', function () { setTimeout(() => { this.dataset.cursorPos = this.selectionStart; }, 10); });
                input.addEventListener('keyup',     function () { this.dataset.cursorPos = this.selectionStart; });
                input.addEventListener('click',     function () { this.dataset.cursorPos = this.selectionStart; activeInput = this; showKeyboard(); });
                input.addEventListener('focus',     function () { this.dataset.cursorPos = this.selectionStart; activeInput = this; showKeyboard(); });
                input.addEventListener('input',     function () { this.dataset.cursorPos = this.selectionStart; });
            });
        }

        function setupKeyboardButtons() {
            document.querySelectorAll('[data-key]').forEach(btn => {
                btn.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); if (activeInput) insertText(this.dataset.key); });
            });
            document.querySelector('[data-action="shift"]')?.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); toggleShift(); });
            document.querySelectorAll('[data-action="backspace"]').forEach(btn => { btn.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); backspace(); }); });
            document.querySelector('[data-action="space"]')?.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); insertText(' '); });
            document.querySelector('[data-action="clear"]')?.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); if (activeInput) { activeInput.value = ''; activeInput.dataset.cursorPos = 0; activeInput.dispatchEvent(new Event('input')); } });
            document.querySelectorAll('[data-action="symbols"]').forEach(btn => { btn.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); document.getElementById('mainKeyboard').style.display = 'none'; document.getElementById('symbolKeyboard').style.display = 'block'; currentKeyboard = 'symbols'; }); });
            document.querySelectorAll('[data-action="letters"]').forEach(btn => { btn.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); document.getElementById('symbolKeyboard').style.display = 'none'; document.getElementById('mainKeyboard').style.display = 'block'; currentKeyboard = 'letters'; }); });
            document.querySelectorAll('[data-action="done"]').forEach(btn => { btn.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); hideKeyboard(); }); });
        }

        function insertText(text) {
            if (!activeInput) return;
            let cur = activeInput.selectionStart;
            const savedPos = Number.parseInt(activeInput.dataset.cursorPos, 10);
            if (!Number.isInteger(cur) || cur < 0) cur = Number.isInteger(savedPos) ? savedPos : activeInput.value.length;
            if (cur > activeInput.value.length) cur = activeInput.value.length;
            const val = activeInput.value;
            if (isShifted && /[a-z]/.test(text)) text = text.toUpperCase();
            activeInput.value = val.substring(0, cur) + text + val.substring(cur);
            const np = cur + text.length;
            activeInput.selectionStart = activeInput.selectionEnd = np;
            activeInput.dataset.cursorPos = np;
            activeInput.dispatchEvent(new Event('input', { bubbles: true }));
            activeInput.focus();
            if (isShifted) toggleShift();
        }

        function backspace() {
            if (!activeInput) return;
            let cur = activeInput.selectionStart;
            const savedPos = Number.parseInt(activeInput.dataset.cursorPos, 10);
            if (!Number.isInteger(cur) || cur < 0) cur = Number.isInteger(savedPos) ? savedPos : activeInput.value.length;
            if (cur > activeInput.value.length) cur = activeInput.value.length;
            const val = activeInput.value;
            if (cur > 0) {
                activeInput.value = val.substring(0, cur - 1) + val.substring(cur);
                const np = cur - 1;
                activeInput.selectionStart = activeInput.selectionEnd = np;
                activeInput.dataset.cursorPos = np;
                activeInput.dispatchEvent(new Event('input', { bubbles: true }));
                activeInput.focus();
            }
        }

        function toggleShift() {
            isShifted = !isShifted;
            document.querySelectorAll('#mainKeyboard .keyboard-key[data-key]').forEach(btn => {
                const k = btn.dataset.key;
                if (k && k.length === 1 && /[a-z]/i.test(k)) { const nk = isShifted ? k.toUpperCase() : k.toLowerCase(); btn.textContent = nk; btn.dataset.key = nk; }
            });
            const sb = document.getElementById('shiftBtn');
            if (sb) { sb.classList.toggle('shift-active', isShifted); sb.style.background = ''; }
        }

        function showKeyboard() {
            const c = document.getElementById('floatingKeyboardContainer'); if (!c) return;
            document.getElementById('mainKeyboard').style.display   = currentKeyboard === 'letters' ? 'block' : 'none';
            document.getElementById('symbolKeyboard').style.display = currentKeyboard === 'symbols' ? 'block' : 'none';
            c.classList.add('active'); isKeyboardVisible = true; document.body.classList.add('keyboard-visible');
        }

        function hideKeyboard() {
            const c = document.getElementById('floatingKeyboardContainer');
            if (c) c.classList.remove('active');
            isKeyboardVisible = false; document.body.classList.remove('keyboard-visible');
        }

        document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', initUniversalKeyboard) : setTimeout(initUniversalKeyboard, 50);
    })();
    </script>

    <script>
        // Expose API base for JavaScript
        window.PYTHON_API_BASE = <?= json_encode(PYTHON_API_BASE) ?>;
    </script>
    <script src="../js/kioskRegister.js"></script>

</body>

</html>