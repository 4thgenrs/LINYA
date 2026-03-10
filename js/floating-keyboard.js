/**
 * UNIVERSAL FLOATING KEYBOARD - COMPLETE FIX
 * Always inserts at correct cursor position
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
        // Watch for dynamically added inputs
        const observeNewInputs = function() {
            const inputs = document.querySelectorAll('input:not([type="hidden"]), textarea');
            
            inputs.forEach(input => {
                if (input.dataset.keyboardAttached) return;
                input.dataset.keyboardAttached = 'true';
                
                // Track cursor position on any interaction
                input.addEventListener('mousedown', function() {
                    this.dataset.cursorPos = this.selectionStart;
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
                
                // Handle input events to update cursor position
                input.addEventListener('input', function() {
                    setTimeout(() => {
                        this.dataset.cursorPos = this.selectionStart;
                    }, 0);
                });
                
                // For email inputs specifically
                input.addEventListener('blur', function() {
                    // Save cursor position before blur
                    this.dataset.cursorPos = this.selectionStart;
                });
            });
        };
        
        // Initial setup
        observeNewInputs();
        
        // Watch for new inputs (if using dynamic forms)
        const observer = new MutationObserver(observeNewInputs);
        observer.observe(document.body, { childList: true, subtree: true });
    }
    
    function setupKeyboardButtons() {
        // Use mousedown to capture cursor position BEFORE click loses focus
        document.querySelectorAll('[data-key]').forEach(btn => {
            btn.addEventListener('mousedown', function(e) {
                e.preventDefault(); // Prevent default behavior
                
                // Store cursor position from the active input
                if (activeInput) {
                    activeInput.dataset.cursorPos = activeInput.selectionStart;
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
                    activeInput.dataset.cursorPos = activeInput.selectionStart;
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
                activeInput.dataset.cursorPos = activeInput.selectionStart;
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
    
    // IMPROVED INSERT FUNCTION - Works with all input types
    function insertText(text) {
        if (!activeInput) return;
        
        // Get the stored cursor position or current selection
        let cursorPos = activeInput.dataset.cursorPos;
        
        if (cursorPos === undefined || cursorPos === null || cursorPos === '') {
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
        
        // Insert at cursor position
        const newValue = 
            currentValue.substring(0, cursorPos) + 
            text + 
            currentValue.substring(cursorPos);
        
        activeInput.value = newValue;
        
        // Set cursor after inserted text
        const newCursorPos = cursorPos + text.length;
        
        // For email inputs, we need to set cursor position after a tiny delay
        // because the browser might reset it during validation
        setTimeout(() => {
            if (activeInput) {
                activeInput.selectionStart = newCursorPos;
                activeInput.selectionEnd = newCursorPos;
                activeInput.dataset.cursorPos = newCursorPos;
            }
        }, 0);
        
        // Trigger input event for validation
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
        
        // Get the stored cursor position
        let cursorPos = activeInput.dataset.cursorPos;
        
        if (cursorPos === undefined || cursorPos === null || cursorPos === '') {
            cursorPos = activeInput.selectionStart;
        } else {
            cursorPos = parseInt(cursorPos, 10);
        }
        
        if (isNaN(cursorPos) || cursorPos < 0) {
            cursorPos = activeInput.value.length;
        }
        
        const currentValue = activeInput.value;
        
        if (cursorPos > 0) {
            const newValue = 
                currentValue.substring(0, cursorPos - 1) + 
                currentValue.substring(cursorPos);
            
            activeInput.value = newValue;
            
            const newCursorPos = cursorPos - 1;
            
            // Set cursor after delay (important for email inputs)
            setTimeout(() => {
                if (activeInput) {
                    activeInput.selectionStart = newCursorPos;
                    activeInput.selectionEnd = newCursorPos;
                    activeInput.dataset.cursorPos = newCursorPos;
                }
            }, 0);
            
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