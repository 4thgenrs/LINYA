<?php include '../linya_db/config.php';
session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk Menu</title>
    <link rel="stylesheet" href="../css/kioskMenu.css">
    
    <!-- Floating Keyboard Styles -->
    <style>
        /* Floating Keyboard Container */
        #floatingKeyboardContainer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #1e1e1e;
            border-top: 2px solid #333;
            border-radius: 20px 20px 0 0;
            padding: 15px 10px 20px;
            box-shadow: 0 -5px 20px rgba(0,0,0,0.5);
            z-index: 10000;
            transform: translateY(100%);
            transition: transform 0.3s ease;
            font-family: 'Segoe UI', system-ui, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            width: 100%;
        }

        #floatingKeyboardContainer.active {
            transform: translateY(0);
        }

        /* Keyboard Sections */
        .keyboard-section {
            width: 100%;
        }

        /* Keyboard Rows */
        .keyboard-row {
            display: flex;
            justify-content: center;
            margin: 6px 0;
            gap: 6px;
        }

        /* Keyboard Keys */
        .keyboard-key {
            background: linear-gradient(135deg, #333, #222);
            border: 1px solid #444;
            border-radius: 12px;
            color: white;
            font-size: 24px;
            font-weight: 500;
            padding: 15px 0;
            min-width: 60px;
            text-align: center;
            cursor: pointer;
            user-select: none;
            transition: all 0.1s ease;
            box-shadow: 0 4px 0 #0a0a0a;
            flex: 1 1 auto;
            max-width: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .keyboard-key:active {
            transform: translateY(4px);
            box-shadow: 0 0 0 #0a0a0a;
        }

        /* Special keys */
        .keyboard-key.wide {
            min-width: 100px;
            max-width: 120px;
            flex: 1.5;
        }

        .keyboard-key.extra-wide {
            min-width: 200px;
            max-width: 250px;
            flex: 3;
            font-size: 18px;
        }

        .keyboard-key.space {
            background: linear-gradient(135deg, #444, #333);
        }

        .keyboard-key.done {
            background: linear-gradient(135deg, #07bf85, #0c9e3d);
            border-color: #0fb8a0;
        }

        .keyboard-key.symbol {
            font-size: 20px;
        }

        /* Hide symbol keyboard initially */
        #symbolKeyboard {
            display: none;
        }

        /* Responsive adjustments */
        @media (max-width: 600px) {
            .keyboard-key {
                font-size: 20px;
                padding: 12px 0;
                min-width: 45px;
            }
            
            .keyboard-key.extra-wide {
                min-width: 150px;
                font-size: 16px;
            }
        }

        @media (max-width: 400px) {
            .keyboard-key {
                font-size: 18px;
                padding: 10px 0;
                min-width: 35px;
            }
            
            .keyboard-row {
                gap: 4px;
            }
        }

        /* Make sure input fields don't get hidden behind keyboard */
        input, textarea {
            margin-bottom: 10px;
        }

        /* Add padding to bottom when keyboard is visible */
        body.keyboard-visible {
            padding-bottom: 350px;
        }

        /* Ensure modal is above keyboard */
        .vf-modal {
            z-index: 10001;
        }
    </style>
</head>

<body>
    <!-- Visitor Form Section -->
    <div class="kiosk-container">
        <div id="visitorForm" class="visitor-screen">
            <div class="visitor-Title">
                <h1 class="title">Visitor Details</h1>
                <p class="muted small">Please enter your details before proceeding</p>
            </div>

            <form class="visitorDetails" id="visitorDetails" onsubmit="return false;">
                <label class="vf-field">
                    <div class="vf-label">Name:</div>
                    <input type="text" id="visitorName" placeholder="Full name" autocomplete="name" required />
                </label>

                <label class="vf-field">
                    <div class="vf-label">Phone:</div>
                    <input type="tel" id="visitorPhone" placeholder="09XXXXXXXXX" inputmode="numeric" required />
                    <div id="visitorPhoneHint" class="vf-hint" style="display:none;color:#ff0000">Phone must be 11 digits and start with 09</div>
                </label>

                <label class="vf-field">
                    <div class="vf-label">Email (optional):</div>
                    <input type="email" id="visitorEmail" placeholder="you@example.com" autocomplete="email" />
                    <div id="visitorEmailHint" class="vf-hint" style="display:none;color:#ff0000">Invalid email address</div>
                </label>

                <label class="vf-field">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <input type="checkbox" id="visitorIsStudent" onclick="console.log('inline visitorIsStudent click ->', this.checked)" />
                        <div class="vf-label" style="margin:0">Are you a student?</div>
                    </div>
                </label>

                <label class="vf-field" id="visitorStudentNumberField" style="display:none;">
                    <div class="vf-label">Student Number</div>
                    <input type="text" id="visitorStudentNumber" placeholder="20001234-S" maxlength="20" disabled />
                    <div id="visitorStudentNumberHint" class="vf-hint" style="display:none;color:#ff0000">Format: 20001234-S (year 2000–2026)</div>
                </label>

                <label class="vf-field" id="visitorCourseField" style="display:none;">
                    <div class="vf-label">Course</div>
                    <select id="visitorCourse" disabled>
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

                <div class="vf-actions">
                    <button id="visitorProceed" class="primary-btn" type="button" disabled>Proceed</button>
                    <a href="kioskSelect.php" class="back-btn" title="Go back to selection">
                        Go Back
                    </a>
                </div>
            </form>
            <script>
                (function(){
                    const chk = document.getElementById('visitorIsStudent');
                    const snWrap = document.getElementById('visitorStudentNumberField');
                    const sn = document.getElementById('visitorStudentNumber');
                    const courseWrap = document.getElementById('visitorCourseField');
                    const course = document.getElementById('visitorCourse');
                    const proceed = document.getElementById('visitorProceed');
                    const nameEl = document.getElementById('visitorName');
                    const phoneEl = document.getElementById('visitorPhone');
                    const emailEl = document.getElementById('visitorEmail');

                    function isValidPhone(p){ return typeof p==='string' && /^\s*09\d{9}\s*$/.test(p); }
                    function isValidEmail(e){ if(!e) return false; return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e); }
                    function isValidStudentNumber(s){ if(!s) return false; const re = /^(?:2000|2001|2002|2003|2004|2005|2006|2007|2008|2009|2010|2011|2012|2013|2014|2015|2016|2017|2018|2019|2020|2021|2022|2023|2024|2025|2026)\d{4}-[A-Z]$/; return re.test((s||'').trim().toUpperCase()); }

                    function update() {
                        const name = nameEl ? nameEl.value.trim() : '';
                        const phone = phoneEl ? phoneEl.value.trim() : '';
                        const email = emailEl ? emailEl.value.trim() : '';
                        const isStudent = chk ? chk.checked : false;
                        const studentNum = sn ? sn.value.trim() : '';
                        const courseVal = course ? course.value : '';

                        // show/hide wrappers
                        if (snWrap) snWrap.style.display = isStudent ? '' : 'none';
                        if (courseWrap) courseWrap.style.display = isStudent ? '' : 'none';
                        if (sn) sn.disabled = !isStudent;
                        if (course) course.disabled = !isStudent;

                        // basic enable/disable proceed button
                        const phoneOk = isValidPhone(phone);
                        const emailOk = (email === '') ? true : isValidEmail(email);
                        const studentOk = !isStudent || (isStudent && isValidStudentNumber(studentNum) && courseVal);
                        if (proceed) proceed.disabled = !(name.length>0 && phoneOk && emailOk && studentOk);
                    }

                    if (chk) chk.addEventListener('change', function(){ console.log('inline handler change', this.checked); update(); });
                    if (sn) sn.addEventListener('input', function(){ this.value = this.value.toUpperCase().replace(/[^0-9A-Z\-]/g,''); update(); });
                    if (course) course.addEventListener('change', update);
                    ['visitorName','visitorPhone','visitorEmail'].forEach(id=>{ const el=document.getElementById(id); if(el) el.addEventListener('input', update); });

                    // init
                    setTimeout(update, 50);
                })();
            </script>
        </div>
    </div>

    <!-- Visitor Confirmation Modal - MOVED OUTSIDE KIOSK CONTAINER -->
    <div id="visitorConfirmModal" class="vf-modal" style="display:none">
        <div class="vf-modal-card">
            <h2>CONFIRM DETAILS</h2>

            <div class="confirm-instruction">
                Please review your information carefully before proceeding
            </div>

            <div class="vf-confirm-row"><strong>Name:</strong> <span id="confirmName"></span></div>
            <div class="vf-confirm-row"><strong>Phone:</strong> <span id="confirmPhone"></span></div>
            <div class="vf-confirm-row"><strong>Email:</strong> <span id="confirmEmail"></span></div>

            <div class="vf-actions" style="margin-top:12px">
                <button id="visitorConfirm" class="vf-modal-confirm" type="button">Confirm & Enter Kiosk</button>
                <button id="visitorCancel" class="vf-modal-ghost" type="button">Go back</button>
            </div>
        </div>
    </div>

    <!-- Office Section -->
    <div id="officeSection" class="screen" style="display: none;">
        <div class="office-section-container">
            <div class="officeTitle">
                <h1 class="title">Select an Office</h1>
                <div class="office-instruction">
                    Please choose the office you wish to visit from the options below
                </div>
            </div>

            <div class="grid" id="officeGrid">
                <?php
                $sql = "SELECT * FROM offices WHERE enabled = 1 ORDER BY office_description ASC";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $id = $row['office_id'];
                        $code = htmlspecialchars($row['office_code']);
                        $desc = htmlspecialchars($row['office_description']);
                        echo "
                            <button class='office-btn' onclick='loadServices($id, " . json_encode($row['office_description']) . ")'>
                                <span class='office-code'>$desc</span>
                                <span class='office-desc'>( $code )</span>
                            </button>";
                    }
                } else {
                    echo "<p class='muted'>No offices found.</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Service Selection Section -->
    <div class="service-section-container" style="display: none;">
        <div id="serviceSection" class="screen">
            <div class="header-row">
                <h6 class="title" id="serviceTitle">Services</h6>
            </div>
            <div class="mute small" id="hint">
                Tap a service to select and press Confirm to continue.
                Toggle the PDF switch after selecting a service to request a soft copy.
            </div>
            <div class="controls-row">
                <div class="controls-btn">
                    <button id="multiSelectToggle" class="secondary-btn" type="button">Select multiple</button>
                    <button id="backBtn" class="ghost-btn" type="button">Back</button>
                </div>
            </div>

            <div class="grid cards" id="serviceGrid"></div>

            <div id="serviceActions" class="service-actions">
                <div id="selectedCount" class="muted">0 selected</div>
                <button id="confirmBtn" class="primary-btn" type="button" disabled>Confirm</button>
            </div>
        </div>
    </div>

    <!-- Floating Keyboard HTML -->
    <div id="floatingKeyboardContainer">
        <!-- Main Letters Keyboard -->
        <div id="mainKeyboard" class="keyboard-section">
            <div class="keyboard-row">
                <div class="keyboard-key" data-key="q">q</div>
                <div class="keyboard-key" data-key="w">w</div>
                <div class="keyboard-key" data-key="e">e</div>
                <div class="keyboard-key" data-key="r">r</div>
                <div class="keyboard-key" data-key="t">t</div>
                <div class="keyboard-key" data-key="y">y</div>
                <div class="keyboard-key" data-key="u">u</div>
                <div class="keyboard-key" data-key="i">i</div>
                <div class="keyboard-key" data-key="o">o</div>
                <div class="keyboard-key" data-key="p">p</div>
            </div>
            <div class="keyboard-row">
                <div class="keyboard-key" data-key="a">a</div>
                <div class="keyboard-key" data-key="s">s</div>
                <div class="keyboard-key" data-key="d">d</div>
                <div class="keyboard-key" data-key="f">f</div>
                <div class="keyboard-key" data-key="g">g</div>
                <div class="keyboard-key" data-key="h">h</div>
                <div class="keyboard-key" data-key="j">j</div>
                <div class="keyboard-key" data-key="k">k</div>
                <div class="keyboard-key" data-key="l">l</div>
            </div>
            <div class="keyboard-row">
                <div class="keyboard-key wide" data-action="shift" id="shiftBtn">⬆️</div>
                <div class="keyboard-key" data-key="z">z</div>
                <div class="keyboard-key" data-key="x">x</div>
                <div class="keyboard-key" data-key="c">c</div>
                <div class="keyboard-key" data-key="v">v</div>
                <div class="keyboard-key" data-key="b">b</div>
                <div class="keyboard-key" data-key="n">n</div>
                <div class="keyboard-key" data-key="m">m</div>
                <div class="keyboard-key wide" data-action="backspace">⌫</div>
            </div>
            <div class="keyboard-row">
                <div class="keyboard-key wide" data-action="symbols">!?#</div>
                <div class="keyboard-key extra-wide space" data-action="space">Space</div>
                <div class="keyboard-key wide" data-action="clear">Clear</div>
                <div class="keyboard-key wide done" data-action="done">Done</div>
            </div>
        </div>

        <!-- Symbols Keyboard -->
        <div id="symbolKeyboard" class="keyboard-section">
            <div class="keyboard-row">
                <div class="keyboard-key" data-key="1">1</div>
                <div class="keyboard-key" data-key="2">2</div>
                <div class="keyboard-key" data-key="3">3</div>
                <div class="keyboard-key" data-key="4">4</div>
                <div class="keyboard-key" data-key="5">5</div>
                <div class="keyboard-key" data-key="6">6</div>
                <div class="keyboard-key" data-key="7">7</div>
                <div class="keyboard-key" data-key="8">8</div>
                <div class="keyboard-key" data-key="9">9</div>
                <div class="keyboard-key" data-key="0">0</div>
            </div>
            <div class="keyboard-row">
                <div class="keyboard-key" data-key="!">!</div>
                <div class="keyboard-key" data-key="@">@</div>
                <div class="keyboard-key" data-key="#">#</div>
                <div class="keyboard-key" data-key="$">$</div>
                <div class="keyboard-key" data-key="%">%</div>
                <div class="keyboard-key" data-key="^">^</div>
                <div class="keyboard-key" data-key="&">&</div>
                <div class="keyboard-key" data-key="*">*</div>
                <div class="keyboard-key" data-key="(">(</div>
                <div class="keyboard-key" data-key=")">)</div>
            </div>
            <div class="keyboard-row">
                <div class="keyboard-key" data-key="-">-</div>
                <div class="keyboard-key" data-key="_">_</div>
                <div class="keyboard-key" data-key="=">=</div>
                <div class="keyboard-key" data-key="+">+</div>
                <div class="keyboard-key" data-key="[">[</div>
                <div class="keyboard-key" data-key="]">]</div>
                <div class="keyboard-key" data-key="{">{</div>
                <div class="keyboard-key" data-key="}">}</div>
                <div class="keyboard-key" data-key="\">\</div>
                <div class="keyboard-key" data-key="|">|</div>
            </div>
            <div class="keyboard-row">
                <div class="keyboard-key" data-key=";">;</div>
                <div class="keyboard-key" data-key=":">:</div>
                <div class="keyboard-key" data-key="'">'</div>
                <div class="keyboard-key" data-key='"'>"</div>
                <div class="keyboard-key" data-key=",">,</div>
                <div class="keyboard-key" data-key=".">.</div>
                <div class="keyboard-key" data-key="?">?</div>
                <div class="keyboard-key" data-key="/">/</div>
                <div class="keyboard-key wide" data-action="backspace">⌫</div>
            </div>
            <div class="keyboard-row">
                <div class="keyboard-key wide" data-action="letters">ABC</div>
                <div class="keyboard-key extra-wide space" data-action="space">Space</div>
                <div class="keyboard-key wide" data-action="clear">Clear</div>
                <div class="keyboard-key wide done" data-action="done">Done</div>
            </div>
        </div>
    </div>

    <!-- Floating Keyboard JavaScript -->
    <script>
        (function() {
            'use strict';
            
            // Keyboard state
            let activeInput = null;
            let isShifted = false;
            let currentKeyboard = 'letters';
            let isKeyboardVisible = false;
            
            function initUniversalKeyboard() {
                console.log('🔤 Universal Floating Keyboard initialized');
                
                const container = document.getElementById('floatingKeyboardContainer');
                const mainKeyboard = document.getElementById('mainKeyboard');
                const symbolKeyboard = document.getElementById('symbolKeyboard');
                
                if (!container || !mainKeyboard || !symbolKeyboard) {
                    console.warn('Keyboard elements not found');
                    return;
                }
                
                setupInputListeners();
                setupKeyboardButtons();
                
                // Hide keyboard when clicking outside
                document.addEventListener('click', function(e) {
                    if (!isKeyboardVisible) return;
                    
                    const isClickOnKeyboard = container.contains(e.target);
                    const isClickOnInput = e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA';
                    
                    if (!isClickOnKeyboard && !isClickOnInput) {
                        hideKeyboard();
                    }
                });
                
                container.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
            
            function setupInputListeners() {
                const inputs = document.querySelectorAll('input:not([type="hidden"]), textarea');
                
                inputs.forEach(input => {
                    if (input.dataset.keyboardAttached) return;
                    input.dataset.keyboardAttached = 'true';
                    
                    // Store cursor position on any interaction
                    input.addEventListener('mousedown', function() {
                        setTimeout(() => {
                            this.dataset.cursorPos = this.selectionStart;
                        }, 10);
                    });
                    
                    input.addEventListener('keyup', function() {
                        this.dataset.cursorPos = this.selectionStart;
                    });
                    
                    input.addEventListener('click', function() {
                        this.dataset.cursorPos = this.selectionStart;
                        activeInput = this;
                        showKeyboard();
                    });
                    
                    input.addEventListener('focus', function() {
                        this.dataset.cursorPos = this.selectionStart;
                        activeInput = this;
                        showKeyboard();
                    });
                });
            }
            
            function setupKeyboardButtons() {
                // Letter and number keys
                document.querySelectorAll('[data-key]').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (activeInput) {
                            insertText(this.dataset.key);
                        }
                    });
                });
                
                // Action buttons
                document.querySelector('[data-action="shift"]')?.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleShift();
                });
                
                document.querySelectorAll('[data-action="backspace"]').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        backspace();
                    });
                });
                
                document.querySelector('[data-action="space"]')?.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    insertText(' ');
                });
                
                document.querySelector('[data-action="clear"]')?.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (activeInput) {
                        activeInput.value = '';
                        activeInput.dataset.cursorPos = 0;
                        activeInput.dispatchEvent(new Event('input'));
                    }
                });
                
                document.querySelectorAll('[data-action="symbols"]').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        document.getElementById('mainKeyboard').style.display = 'none';
                        document.getElementById('symbolKeyboard').style.display = 'block';
                        currentKeyboard = 'symbols';
                    });
                });
                
                document.querySelectorAll('[data-action="letters"]').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        document.getElementById('symbolKeyboard').style.display = 'none';
                        document.getElementById('mainKeyboard').style.display = 'block';
                        currentKeyboard = 'letters';
                    });
                });
                
                document.querySelectorAll('[data-action="done"]').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        hideKeyboard();
                        const form = activeInput?.closest('form');
                        if (form && form.querySelector('[type="submit"]')) {
                            form.querySelector('[type="submit"]').click();
                        }
                    });
                });
            }
            
            function insertText(text) {
                if (!activeInput) return;
                
                // Get current cursor position
                const cursorPos = activeInput.selectionStart;
                const currentValue = activeInput.value;
                
                // Apply shift to letters
                if (isShifted && /[a-z]/.test(text)) {
                    text = text.toUpperCase();
                }
                
                // Insert at cursor position
                const newValue = 
                    currentValue.substring(0, cursorPos) + 
                    text + 
                    currentValue.substring(cursorPos);
                
                activeInput.value = newValue;
                
                // Set cursor after inserted text
                const newCursorPos = cursorPos + text.length;
                activeInput.selectionStart = newCursorPos;
                activeInput.selectionEnd = newCursorPos;
                activeInput.dataset.cursorPos = newCursorPos;
                
                // Trigger validation
                activeInput.dispatchEvent(new Event('input', { bubbles: true }));
                
                // Keep focus
                activeInput.focus();
                
                // Turn off shift after one character
                if (isShifted) {
                    toggleShift();
                }
            }
            
            function backspace() {
                if (!activeInput) return;
                
                const cursorPos = activeInput.selectionStart;
                const currentValue = activeInput.value;
                
                if (cursorPos > 0) {
                    const newValue = 
                        currentValue.substring(0, cursorPos - 1) + 
                        currentValue.substring(cursorPos);
                    
                    activeInput.value = newValue;
                    
                    const newCursorPos = cursorPos - 1;
                    activeInput.selectionStart = newCursorPos;
                    activeInput.selectionEnd = newCursorPos;
                    activeInput.dataset.cursorPos = newCursorPos;
                    
                    activeInput.dispatchEvent(new Event('input', { bubbles: true }));
                    activeInput.focus();
                }
            }
            
            function toggleShift() {
                isShifted = !isShifted;
                
                document.querySelectorAll('#mainKeyboard .keyboard-key[data-key]').forEach(btn => {
                    const key = btn.dataset.key;
                    if (key && key.length === 1 && /[a-z]/i.test(key)) {
                        const newKey = isShifted ? key.toUpperCase() : key.toLowerCase();
                        btn.textContent = newKey;
                        btn.dataset.key = newKey;
                    }
                });
                
                const shiftBtn = document.getElementById('shiftBtn');
                if (shiftBtn) {
                    shiftBtn.style.background = isShifted ? 
                        'linear-gradient(135deg, #07bf85, #0c9e3d)' : 
                        'linear-gradient(135deg, #444, #333)';
                }
            }
            
            function showKeyboard() {
                const container = document.getElementById('floatingKeyboardContainer');
                if (!container) return;
                
                if (currentKeyboard === 'letters') {
                    document.getElementById('mainKeyboard').style.display = 'block';
                    document.getElementById('symbolKeyboard').style.display = 'none';
                } else {
                    document.getElementById('mainKeyboard').style.display = 'none';
                    document.getElementById('symbolKeyboard').style.display = 'block';
                }
                
                container.classList.add('active');
                isKeyboardVisible = true;
                
                // Add class to body for padding
                document.body.classList.add('keyboard-visible');
            }
            
            function hideKeyboard() {
                const container = document.getElementById('floatingKeyboardContainer');
                if (container) {
                    container.classList.remove('active');
                }
                isKeyboardVisible = false;
                document.body.classList.remove('keyboard-visible');
            }
            
            // Initialize when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initUniversalKeyboard);
            } else {
                setTimeout(initUniversalKeyboard, 50);
            }
            
        })();
    </script>

    <?php
    if (isset($_SESSION['client']) && is_array($_SESSION['client'])) {
        $client_js = json_encode($_SESSION['client']);
        echo "<script>window.SKIP_VISITOR_FORM = true; window.__CLIENT_SESSION = $client_js;</script>";
    }
    ?>
    <script src="../js/kioskMenu.js"></script>
</body>

</html>