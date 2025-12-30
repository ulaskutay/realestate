/**
 * Starter Theme - Main JavaScript
 */

(function() {
    'use strict';

    // DOM Ready
    document.addEventListener('DOMContentLoaded', function() {
        initMobileMenu();
        initSmoothScroll();
        initScrollAnimations();
        initLazyLoading();
    });

    /**
     * Mobile Menu Toggle
     */
    function initMobileMenu() {
        const menuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (!menuBtn || !mobileMenu) return;
        
        menuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
            
            // Update button icon
            const icon = menuBtn.querySelector('.material-symbols-outlined');
            if (icon) {
                icon.textContent = mobileMenu.classList.contains('hidden') ? 'menu' : 'close';
            }
        });
        
        // Close menu on outside click
        document.addEventListener('click', function(e) {
            if (!menuBtn.contains(e.target) && !mobileMenu.contains(e.target)) {
                mobileMenu.classList.add('hidden');
                const icon = menuBtn.querySelector('.material-symbols-outlined');
                if (icon) icon.textContent = 'menu';
            }
        });
    }

    /**
     * Smooth Scroll for Anchor Links
     */
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const target = document.querySelector(targetId);
                if (target) {
                    e.preventDefault();
                    
                    const headerHeight = document.querySelector('header')?.offsetHeight || 0;
                    const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - headerHeight;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                    
                    // Close mobile menu if open
                    const mobileMenu = document.getElementById('mobile-menu');
                    if (mobileMenu) {
                        mobileMenu.classList.add('hidden');
                    }
                }
            });
        });
    }

    /**
     * Scroll-triggered Animations
     */
    function initScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in-up');
                    entry.target.style.opacity = '1';
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        // Observe elements with data-animate attribute
        document.querySelectorAll('[data-animate]').forEach(el => {
            el.style.opacity = '0';
            observer.observe(el);
        });
        
        // Auto-animate section elements
        document.querySelectorAll('section > div > *').forEach((el, index) => {
            if (!el.hasAttribute('data-animate') && el.children.length > 0) {
                el.style.animationDelay = `${index * 0.1}s`;
            }
        });
    }

    /**
     * Lazy Loading Images
     */
    function initLazyLoading() {
        if ('loading' in HTMLImageElement.prototype) {
            // Native lazy loading supported
            document.querySelectorAll('img[data-src]').forEach(img => {
                img.src = img.dataset.src;
                img.loading = 'lazy';
            });
        } else {
            // Fallback for older browsers
            const lazyImages = document.querySelectorAll('img[data-src]');
            
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.add('loaded');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            lazyImages.forEach(img => imageObserver.observe(img));
        }
    }

    /**
     * Header Scroll Effect
     */
    window.addEventListener('scroll', function() {
        const header = document.getElementById('main-header');
        if (!header) return;
        
        if (window.scrollY > 100) {
            header.classList.add('bg-white', 'shadow-md');
            header.classList.remove('bg-transparent');
        } else {
            if (header.dataset.transparent === 'true') {
                header.classList.remove('bg-white', 'shadow-md');
                header.classList.add('bg-transparent');
            }
        }
    });

    /**
     * Back to Top Button
     */
    const backToTop = document.getElementById('back-to-top');
    if (backToTop) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 500) {
                backToTop.classList.remove('opacity-0', 'invisible');
                backToTop.classList.add('opacity-100', 'visible');
            } else {
                backToTop.classList.add('opacity-0', 'invisible');
                backToTop.classList.remove('opacity-100', 'visible');
            }
        });
        
        backToTop.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /**
     * Form Validation Helper
     */
    window.validateForm = function(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('border-red-500');
                
                // Show error message
                let errorMsg = input.nextElementSibling;
                if (!errorMsg || !errorMsg.classList.contains('error-message')) {
                    errorMsg = document.createElement('span');
                    errorMsg.className = 'error-message text-red-500 text-sm mt-1';
                    input.parentNode.insertBefore(errorMsg, input.nextSibling);
                }
                errorMsg.textContent = 'Bu alan zorunludur';
            } else {
                input.classList.remove('border-red-500');
                const errorMsg = input.nextElementSibling;
                if (errorMsg && errorMsg.classList.contains('error-message')) {
                    errorMsg.remove();
                }
            }
        });
        
        // Email validation
        const emailInputs = form.querySelectorAll('input[type="email"]');
        emailInputs.forEach(input => {
            if (input.value && !isValidEmail(input.value)) {
                isValid = false;
                input.classList.add('border-red-500');
            }
        });
        
        return isValid;
    };

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    /**
     * Toast Notification Helper
     */
    window.showToast = function(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-50 transform transition-all duration-300 translate-y-20 opacity-0`;
        
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };
        
        toast.classList.add(colors[type] || colors.info);
        toast.textContent = message;
        document.body.appendChild(toast);
        
        // Animate in
        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-20', 'opacity-0');
        });
        
        // Auto remove
        setTimeout(() => {
            toast.classList.add('translate-y-20', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    };

})();

