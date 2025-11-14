// Combined Video Ad and Get Ticket Landing Page JavaScript

// Inactivity timeout for returning to video ad
let inactivityTimeout;
const INACTIVITY_DELAY = 30000; // 30 seconds

// Start inactivity timer
function startInactivityTimer() {
    clearTimeout(inactivityTimeout);
    inactivityTimeout = setTimeout(() => {
        resetToAdScreen();
    }, INACTIVITY_DELAY);
}

// Reset to video ad screen
function resetToAdScreen() {
    const adScreen = document.getElementById('adScreen');
    const getTicketScreen = document.getElementById('getTicketScreen');
    const getTicketBtn = document.getElementById('getTicketBtn');
    const options = document.getElementById('options');
    
    if (adScreen) adScreen.style.display = 'flex';
    if (getTicketScreen) getTicketScreen.style.display = 'none';
    
    // Reset get ticket screen state
    if (getTicketBtn) getTicketBtn.style.display = 'block';
    if (options) options.style.display = 'none';
    
    clearTimeout(inactivityTimeout);
}

// Move from video ad to get ticket screen
function showGetTicketScreen() {
    const adScreen = document.getElementById('adScreen');
    const getTicketScreen = document.getElementById('getTicketScreen');
    
    if (adScreen) adScreen.style.display = 'none';
    if (getTicketScreen) getTicketScreen.style.display = 'flex';
    
    startInactivityTimer();
}

// Show options when Get Ticket button is clicked
function showOptions() {
    const getTicketBtn = document.getElementById('getTicketBtn');
    const options = document.getElementById('options');
    
    if (getTicketBtn) getTicketBtn.style.display = 'none';
    if (options) options.style.display = 'block';
    
    startInactivityTimer();
}

// Navigate to New User (kiosk menu)
function goToNewUser() {
    clearTimeout(inactivityTimeout);
    window.location.href = 'includes/kioskMenu.php'; // Adjust path as needed
}

// Navigate to Existing User
function goToExistingUser() {
    clearTimeout(inactivityTimeout);
    window.location.href = 'existing_user.php'; // Adjust path as needed
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const adVideo = document.getElementById('adVideo');
    const adScreen = document.getElementById('adScreen');
    const getTicketBtn = document.getElementById('getTicketBtn');
    const newUserBtn = document.getElementById('newUserBtn');
    const existingUserBtn = document.getElementById('existingUserBtn');
    
    // Video ad click handler
    if (adVideo) {
        adVideo.addEventListener('click', showGetTicketScreen);
    }
    
    // Ad screen click handler (click anywhere on screen)
    if (adScreen) {
        adScreen.addEventListener('click', (e) => {
            if (e.target === adScreen || e.target.closest('.ad-container')) {
                showGetTicketScreen();
            }
        });
    }
    
    // Get Ticket button handler
    if (getTicketBtn) {
        getTicketBtn.addEventListener('click', showOptions);
    }
    
    // New User button handler
    if (newUserBtn) {
        newUserBtn.addEventListener('click', goToNewUser);
    }
    
    // Existing User button handler
    if (existingUserBtn) {
        existingUserBtn.addEventListener('click', goToExistingUser);
    }
    
    // Reset inactivity timer on any user interaction
    document.addEventListener('click', startInactivityTimer);
    document.addEventListener('touchstart', startInactivityTimer);
    document.addEventListener('keypress', startInactivityTimer);
    
    // Optional: Keyboard shortcuts (only when options are visible)
    document.addEventListener('keypress', (e) => {
        const options = document.getElementById('options');
        if (options && options.style.display === 'block') {
            if (e.key === 'n' || e.key === 'N') {
                goToNewUser();
            } else if (e.key === 'e' || e.key === 'E') {
                goToExistingUser();
            }
        }
    });
});

// Expose functions globally if needed
window.showGetTicketScreen = showGetTicketScreen;
window.resetToAdScreen = resetToAdScreen;