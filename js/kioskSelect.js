// Add click feedback and smooth transitions
function showKioskLoginInstructions(href) {
    // Create modal if it doesn't exist
    if (document.getElementById('kioskLoginInstructionsModal')) {
        return;
    }

    const modalHTML = `
      <div id="kioskLoginInstructionsModal" class="kiosk-instructions-modal">
        <div class="kiosk-instructions-content">
          <h2>PLEASE LOOK DIRECTLY AT THE CAMERA</h2>
          <ul>
            <li>• ENSURE GOOD LIGHTING</li>
            <li>• REMOVE SUNGLASSES OR HATS</li>
            <li>• KEEP A NEUTRAL EXPRESSION</li>
          </ul>
          <div class="kiosk-instructions-actions">
            <button id="kioskInstructionsCancel" class="kiosk-instructions-btn cancel">Cancel</button>
            <button id="kioskInstructionsOk" class="kiosk-instructions-btn ok">Continue</button>
          </div>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);

    const closeModal = () => {
        const modal = document.getElementById('kioskLoginInstructionsModal');
        if (modal) modal.remove();
    };

    document.getElementById('kioskInstructionsCancel').addEventListener('click', () => {
        closeModal();
    });
    document.getElementById('kioskInstructionsOk').addEventListener('click', () => {
        closeModal();
        window.location.href = href;
    });
}

function initKioskSelect() {
    document.querySelectorAll('.kiosk-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            // Add a small delay for visual feedback
            e.preventDefault();
            const href = this.getAttribute('href');
            
            // Visual feedback
            this.style.transform = 'scale(0.98)';
            
            setTimeout(() => {
                if (href && href.includes('kioskLogin.php')) {
                    showKioskLoginInstructions(href);
                } else {
                    window.location.href = href;
                }
            }, 150);
        });
    });

    // Preload hover states for better performance
    const buttons = document.querySelectorAll('.kiosk-btn');
    buttons.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.willChange = 'transform, box-shadow';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.willChange = 'auto';
        });
    });
}

document.addEventListener('DOMContentLoaded', initKioskSelect);
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.kiosk-btn');
    buttons.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.willChange = 'transform, box-shadow';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.willChange = 'auto';
        });
    });
});