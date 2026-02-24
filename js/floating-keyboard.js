/**
 * UNIVERSAL FLOATING KEYBOARD - EMAIL FIX
 * Special handling for email inputs
 */

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
            
            // Track cursor position on any interaction
            input.addEventListener('mousedown', function() {
                this.dataset.cursorPos = this.selectionStart;
                this.dataset.lastCursorPos = this.selectionStart;
            });
            
            input.addEventListener('keyup', function() {
                this.dataset.cursorPos = this.selectionStart;
                this.dataset.lastCursorPos = this.selectionStart;
            });
            
            input.addEventListener('click', function() {
                this.dataset.cursorPos = this.selectionStart;
                this.dataset.lastCursorPos = this.selectionStart;
                activeInput = this;
                showKeyboard();
            });
            
            input.addEventListener('focus', function() {
                this.dataset.cursorPos = this.selectionStart;
                this.dataset.lastCursorPos = this.selectionStart;
                activeInput = this;
                showKeyboard();
            });
            
            // Special handling for email inputs
            if (input.type === 'email') {
                console.log('Email input detected - applying special handling');
                
                // Store value before any browser interference
                input.addEventListener('input', function(e) {
                    // Save the cursor position that was set by our keyboard
                    if (this.dataset.ourInsert) {
                        const pos = parseInt(this.dataset.ourInsert, 10);
                        setTimeout(() => {
                            this.selectionStart = pos;
                            this.selectionEnd = pos;
                            this.dataset.cursorPos = pos;
                            this.dataset.lastCursorPos = pos;
                            delete this.dataset.ourInsert;
                        }, 0);
                    }
                });
            }
        });
    }
    
    function setupKeyboardButtons() {
        // Use mousedown to capture cursor position BEFORE click loses focus
        document.querySelectorAll('[data-key]').forEach(btn => {
            btn.addEventListener('mousedown', function(e) {
                e.preventDefault();
                
                if (activeInput) {
                    // Store the current cursor position
                    const currentPos = activeInput.selectionStart;
                    activeInput.dataset.cursorPos = currentPos;
                    activeInput.dataset.lastCursorPos = currentPos;
                    console.log('Stored cursor position:', currentPos);
                }
            });
            
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
            btn.addEventListener('mousedown', function(e) {
                e.preventDefault();
                if (activeInput) {
                    const currentPos = activeInput.selectionStart;
                    activeInput.dataset.cursorPos = currentPos;
                    activeInput.dataset.lastCursorPos = currentPos;
                }
            });
            
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                backspace();
            });
        });
        
        document.querySelector('[data-action="space"]')?.addEventListener('mousedown', function(e) {
            e.preventDefault();
            if (activeInput) {
                const currentPos = activeInput.selectionStart;
                activeInput.dataset.cursorPos = currentPos;
                activeInput.dataset.lastCursorPos = currentPos;
            }
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
                activeInput.dataset.lastCursorPos = 0;
                activeInput.dispatchEvent(new Event('input', { bubbles: true }));
                activeInput.focus();
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
    
    // Special insert function for email inputs
    function insertText(text) {
        if (!activeInput) return;
        
        // Get cursor position - try multiple sources
        let cursorPos = activeInput.dataset.lastCursorPos;
        
        if (cursorPos === undefined || cursorPos === null) {
            cursorPos = activeInput.selectionStart;
        } else {
            cursorPos = parseInt(cursorPos, 10);
        }
        
        // Validate cursor position
        if (isNaN(cursorPos) || cursorPos < 0) {
            cursorPos = activeInput.value.length;
        }
        
        const currentValue = activeInput.value;
        
        // Apply shift to letters
        if (isShifted && /[a-z]/.test(text)) {
            text = text.toUpperCase();
        }
        
        // For email inputs, we need to be extra careful
        if (activeInput.type === 'email') {
            // Manually construct the new value
            const beforeCursor = currentValue.substring(0, cursorPos);
            const afterCursor = currentValue.substring(cursorPos);
            
            activeInput.value = beforeCursor + text + afterCursor;
            
            // Calculate new cursor position
            const newCursorPos = cursorPos + text.length;
            
            // Set a flag that this was our insertion
            activeInput.dataset.ourInsert = newCursorPos;
            activeInput.dataset.cursorPos = newCursorPos;
            activeInput.dataset.lastCursorPos = newCursorPos;
            
            // Force set cursor position
            setTimeout(() => {
                if (activeInput) {
                    activeInput.selectionStart = newCursorPos;
                    activeInput.selectionEnd = newCursorPos;
                }
            }, 0);
            
            // Trigger input event
            activeInput.dispatchEvent(new Event('input', { bubbles: true }));
            activeInput.focus();
        } else {
            // Regular input handling
            const newValue = 
                currentValue.substring(0, cursorPos) + 
                text + 
                currentValue.substring(cursorPos);
            
            activeInput.value = newValue;
            
            const newCursorPos = cursorPos + text.length;
            activeInput.selectionStart = newCursorPos;
            activeInput.selectionEnd = newCursorPos;
            activeInput.dataset.cursorPos = newCursorPos;
            activeInput.dataset.lastCursorPos = newCursorPos;
            
            activeInput.dispatchEvent(new Event('input', { bubbles: true }));
            activeInput.focus();
        }
        
        // Turn off shift after one character
        if (isShifted) {
            toggleShift();
        }
    }
    
    function backspace() {
        if (!activeInput) return;
        
        // Get cursor position
        let cursorPos = activeInput.dataset.lastCursorPos;
        
        if (cursorPos === undefined || cursorPos === null) {
            cursorPos = activeInput.selectionStart;
        } else {
            cursorPos = parseInt(cursorPos, 10);
        }
        
        if (isNaN(cursorPos) || cursorPos < 0) {
            cursorPos = activeInput.value.length;
        }
        
        const currentValue = activeInput.value;
        
        if (cursorPos > 0) {
            // For email inputs, special handling
            if (activeInput.type === 'email') {
                const beforeCursor = currentValue.substring(0, cursorPos - 1);
                const afterCursor = currentValue.substring(cursorPos);
                
                activeInput.value = beforeCursor + afterCursor;
                
                const newCursorPos = cursorPos - 1;
                
                activeInput.dataset.ourInsert = newCursorPos;
                activeInput.dataset.cursorPos = newCursorPos;
                activeInput.dataset.lastCursorPos = newCursorPos;
                
                setTimeout(() => {
                    if (activeInput) {
                        activeInput.selectionStart = newCursorPos;
                        activeInput.selectionEnd = newCursorPos;
                    }
                }, 0);
            } else {
                // Regular input handling
                const newValue = 
                    currentValue.substring(0, cursorPos - 1) + 
                    currentValue.substring(cursorPos);
                
                activeInput.value = newValue;
                
                const newCursorPos = cursorPos - 1;
                activeInput.selectionStart = newCursorPos;
                activeInput.selectionEnd = newCursorPos;
                activeInput.dataset.cursorPos = newCursorPos;
                activeInput.dataset.lastCursorPos = newCursorPos;
            }
            
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
    }
    
    function hideKeyboard() {
        const container = document.getElementById('floatingKeyboardContainer');
        if (container) {
            container.classList.remove('active');
        }
        isKeyboardVisible = false;
    }
    
    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initUniversalKeyboard);
    } else {
        setTimeout(initUniversalKeyboard, 50);
    }
    
})();