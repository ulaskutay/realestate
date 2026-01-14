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
     * Property Card Interactions
     */
    document.querySelectorAll('.property-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.classList.add('hover');
        });
        
        card.addEventListener('mouseleave', function() {
            this.classList.remove('hover');
        });
    });

    /**
     * Mobile Menu Toggle (if not handled by header snippet)
     */
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
            const isOpen = !mobileMenu.classList.contains('hidden');
            this.setAttribute('aria-expanded', isOpen);
        });
    }

    /**
     * Search Overlay Toggle
     */
    const searchToggle = document.getElementById('search-toggle');
    const searchOverlay = document.getElementById('search-overlay');
    const searchClose = document.getElementById('search-close');
    
    if (searchToggle && searchOverlay) {
        searchToggle.addEventListener('click', function() {
            searchOverlay.classList.remove('hidden');
            const searchInput = searchOverlay.querySelector('input[type="text"]');
            if (searchInput) {
                setTimeout(() => searchInput.focus(), 100);
            }
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

    /**
     * Header Scroll Effect
     */
    let lastScroll = 0;
    const header = document.getElementById('main-header');
    
    if (header) {
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
