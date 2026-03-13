// Function to show camera instructions modal
function showKioskLoginInstructions(href) {
    console.log('Showing instructions modal'); // Debug log
    
    // Remove existing modal if any
    const existingModal = document.getElementById('kioskLoginInstructionsModal');
    if (existingModal) {
        existingModal.remove();
    }

    const modalHTML = `
      <div id="kioskLoginInstructionsModal" class="kiosk-instructions-modal">
        <div class="kiosk-instructions-content">
          <h2>PLEASE LOOK DIRECTLY AT THE CAMERA</h2>
          <ul>
            <li>ENSURE GOOD LIGHTING</li>
            <li>REMOVE SUNGLASSES OR HATS</li>
            <li>KEEP A NEUTRAL EXPRESSION</li>
          </ul>
          <div class="kiosk-instructions-actions">
            <button id="kioskInstructionsCancel" class="kiosk-instructions-btn cancel">Cancel</button>
            <button id="kioskInstructionsOk" class="kiosk-instructions-btn ok">Continue</button>
          </div>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Prevent scrolling on body when modal is open
    document.body.style.overflow = 'hidden';

    const modal = document.getElementById('kioskLoginInstructionsModal');
    
    // Close modal function
    const closeModal = () => {
        if (modal) {
            modal.remove();
            document.body.style.overflow = 'auto';
        }
    };

    // Cancel button click
    const cancelBtn = document.getElementById('kioskInstructionsCancel');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeModal();
        });
    }

    // OK button click
    const okBtn = document.getElementById('kioskInstructionsOk');
    if (okBtn) {
        okBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeModal();
            window.location.href = href;
        });
    }

    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    // Handle escape key
    const handleEsc = function(e) {
        if (e.key === 'Escape') {
            closeModal();
            document.removeEventListener('keydown', handleEsc);
        }
    };
    document.addEventListener('keydown', handleEsc);
}

// Initialize kiosk select page
function initKioskSelect() {
    console.log('Initializing kiosk select'); // Debug log
    
    // Get all kiosk buttons
    const buttons = document.querySelectorAll('.kiosk-btn');
    console.log('Found buttons:', buttons.length); // Debug log
    
    buttons.forEach(btn => {
        // Remove any existing listeners
        btn.removeEventListener('click', handleButtonClick);
        
        // Add new listener
        btn.addEventListener('click', handleButtonClick);
    });

    // Preload hover states for better performance
    buttons.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.willChange = 'transform, box-shadow';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.willChange = 'auto';
            this.style.transform = 'scale(1)';
        });
    });
}

// Separate click handler function
function handleButtonClick(e) {
    e.preventDefault();
    
    const btn = this;
    const href = btn.getAttribute('href');
    
    console.log('Button clicked:', href); // Debug log
    
    // Visual feedback - scale down
    btn.style.transform = 'scale(0.98)';
    btn.style.transition = 'transform 0.15s ease';
    
    // Check if it's the login button
    if (href && href.includes('kioskLogin.php')) {
        console.log('Login button detected, showing modal'); // Debug log
        setTimeout(() => {
            btn.style.transform = 'scale(1)';
            showKioskLoginInstructions(href);
        }, 150);
    } else {
        // For other buttons, redirect after visual feedback
        console.log('Other button, redirecting'); // Debug log
        setTimeout(() => {
            window.location.href = href;
        }, 150);
    }
}

// Make sure DOM is fully loaded before initializing
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        initKioskSelect();
        // Re-run after a short delay to catch dynamically loaded content
        setTimeout(initKioskSelect, 100);
    });
} else {
    initKioskSelect();
    setTimeout(initKioskSelect, 100);
}

// Also run after window load for any late-loading elements
window.addEventListener('load', function() {
    initKioskSelect();
});

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    document.body.style.overflow = 'auto';
});