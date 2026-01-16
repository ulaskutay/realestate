/**
 * Real Estate Theme - Custom JavaScript
 */

(function() {
    'use strict';

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        initPropertySearch();
        initImageLazyLoading();
        initSmoothScroll();
        initFormValidation();
        initMobileMenu(); // Enabled - works as fallback for pages that don't load header.php inline script
        initPropertyCards();
        initSearchOverlay();
        initHeaderScroll();
    }

    /**
     * Property Search Enhancement
     */
    function initPropertySearch() {
        const searchForm = document.querySelector('.property-search-form');
        if (!searchForm) return;

        // Add loading state on submit
        searchForm.addEventListener('submit', function(e) {
            const submitBtn = searchForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
                submitBtn.textContent = 'Searching...';
            }
        });
    }

    /**
     * Lazy Loading for Images
     */
    function initImageLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                            img.classList.add('loaded');
                        }
                        observer.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }

    /**
     * Smooth Scroll for Anchor Links
     */
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href === '#') return;
                
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    /**
     * Form Validation
     */
    function initFormValidation() {
        const forms = document.querySelectorAll('form[data-validate]');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    }

    /**
     * Mobile Menu Toggle
     * Fallback for pages that don't load header.php inline script
     */
    function initMobileMenu() {
        // Check if already initialized by inline script
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (!mobileMenuToggle || !mobileMenu) {
            // Retry if elements not found yet
            setTimeout(initMobileMenu, 100);
            return;
        }
        
        // Check if already has event listeners (from inline script)
        // If the button already has a data attribute set by inline script, skip
        if (mobileMenuToggle.dataset.menuInitialized === 'true') {
            return;
        }
        
        // Mark as initialized
        mobileMenuToggle.dataset.menuInitialized = 'true';
        
        const menuIcon = document.getElementById('menu-icon');
        const closeIcon = document.getElementById('close-icon');
        
        function closeMenu() {
            mobileMenu.classList.add('hidden');
            mobileMenuToggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
            if (menuIcon && closeIcon) {
                menuIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
            }
        }
        
        function openMenu() {
            mobileMenu.classList.remove('hidden');
            mobileMenuToggle.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
            if (menuIcon && closeIcon) {
                menuIcon.classList.add('hidden');
                closeIcon.classList.remove('hidden');
            }
        }
        
        mobileMenuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (mobileMenu.classList.contains('hidden')) {
                openMenu();
            } else {
                closeMenu();
            }
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                if (!mobileMenu.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                    closeMenu();
                }
            }
        });
        
        // Close menu when clicking on a menu link
        const menuLinks = mobileMenu.querySelectorAll('a');
        menuLinks.forEach(link => {
            link.addEventListener('click', function() {
                closeMenu();
            });
        });
        
        // Close menu on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mobileMenu && !mobileMenu.classList.contains('hidden')) {
                closeMenu();
            }
        });
    }

    /**
     * Property Card Interactions
     */
    function initPropertyCards() {
        document.querySelectorAll('.property-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.classList.add('hover');
            });
            
            card.addEventListener('mouseleave', function() {
                this.classList.remove('hover');
            });
        });
    }

    /**
     * Search Overlay Toggle
     */
    function initSearchOverlay() {
        const searchToggle = document.getElementById('search-toggle');
        const searchToggleDesktop = document.getElementById('search-toggle-desktop');
        const searchOverlay = document.getElementById('search-overlay');
        const searchClose = document.getElementById('search-close');
        
        function openSearch() {
            if (searchOverlay) {
                searchOverlay.classList.remove('hidden');
                const searchInput = searchOverlay.querySelector('input[type="text"]');
                if (searchInput) {
                    setTimeout(() => searchInput.focus(), 100);
                }
            }
        }
        
        if (searchToggle && searchOverlay) {
            searchToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                openSearch();
            });
        }
        
        if (searchToggleDesktop && searchOverlay) {
            searchToggleDesktop.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                openSearch();
            });
        }
        
        if (searchClose && searchOverlay) {
            searchClose.addEventListener('click', function() {
                searchOverlay.classList.add('hidden');
            });
        }
        
        if (searchOverlay) {
            searchOverlay.addEventListener('click', function(e) {
                if (e.target === searchOverlay) {
                    searchOverlay.classList.add('hidden');
                }
            });
        }
        
        // Close search overlay on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && searchOverlay && !searchOverlay.classList.contains('hidden')) {
                searchOverlay.classList.add('hidden');
            }
        });
    }

    /**
     * Header Scroll Effect
     */
    function initHeaderScroll() {
        let lastScroll = 0;
        const header = document.getElementById('main-header');
        
        if (header && header.dataset.isFixed === 'true') {
            window.addEventListener('scroll', function() {
                const currentScroll = window.pageYOffset;
                
                if (currentScroll > 50) {
                    header.style.backgroundColor = header.dataset.bgColor || '#ffffff';
                    header.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
                } else {
                    if (header.dataset.transparent === 'true') {
                        header.style.backgroundColor = 'transparent';
                    }
                    header.style.boxShadow = 'none';
                }
                
                lastScroll = currentScroll;
            });
        }
    }

    /**
     * Price Formatting
     */
    function formatPrice(price) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(price);
    }

    // Export for use in other scripts
    window.RealEstateTheme = {
        formatPrice: formatPrice
    };

})();
