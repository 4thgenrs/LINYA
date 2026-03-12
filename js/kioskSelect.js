// Add click feedback and smooth transitions
document.querySelectorAll('.kiosk-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        // Add a small delay for visual feedback
        e.preventDefault();
        const href = this.getAttribute('href');
        
        // Visual feedback
        this.style.transform = 'scale(0.98)';
        
        setTimeout(() => {
            window.location.href = href;
        }, 150);
    });
});

// Preload hover states for better performance
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