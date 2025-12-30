<?php
/**
 * Admin Footer Snippet
 */
?>
    <!-- Tailwind CSS - Load at end of body to prevent render-blocking -->
    <script src="<?php echo ViewRenderer::assetUrl('assets/js/tailwind-admin.min.js'); ?>"></script>
    
    <!-- Footer Scripts -->
    <script>
        // Dark mode toggle
        if (localStorage.getItem('darkMode') === 'true' || 
            (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
        
        // Flash message auto-hide
        setTimeout(function() {
            const flashMessages = document.querySelectorAll('.flash-message');
            flashMessages.forEach(function(msg) {
                msg.style.transition = 'opacity 0.5s';
                msg.style.opacity = '0';
                setTimeout(function() {
                    msg.remove();
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html>

