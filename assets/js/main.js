document.addEventListener('DOMContentLoaded', function() {
    // Fade out alert messages after 3 seconds
    const alerts = document.querySelectorAll('.alert');
    
    if(alerts.length > 0) {
        setTimeout(function() {
            alerts.forEach(function(alert) {
                alert.style.opacity = '1';
                
                // Fade out animation
                let opacity = 1;
                const timer = setInterval(function() {
                    if(opacity <= 0.1) {
                        clearInterval(timer);
                        alert.style.display = 'none';
                    }
                    
                    alert.style.opacity = opacity;
                    opacity -= 0.1;
                }, 50);
            });
        }, 3000);
    }
    
    // Textarea auto-resize
    const textareas = document.querySelectorAll('textarea');
    
    textareas.forEach(function(textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });
    
    // Mobile menu toggle
    const mobileMenuButton = document.querySelector('.mobile-menu-toggle');
    
    if(mobileMenuButton) {
        const navMenu = document.querySelector('nav ul');
        
        mobileMenuButton.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }
});