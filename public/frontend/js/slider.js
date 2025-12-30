/**
 * CMS Slider Engine
 * Vanilla JavaScript ile çoklu animasyon destekli slider sistemi
 */

(function() {
    'use strict';

    class CMSSlider {
        constructor(element) {
            this.container = element;
            this.slides = element.querySelectorAll('.cms-slide');
            this.currentIndex = 0;
            this.totalSlides = this.slides.length;
            
            // Slider ayarları
            this.config = {
                animation: element.dataset.animation || 'fade',
                autoplay: element.dataset.autoplay === 'true',
                autoplayDelay: parseInt(element.dataset.autoplayDelay) || 5000,
                navigation: element.dataset.navigation === 'true',
                pagination: element.dataset.pagination === 'true',
                loop: element.dataset.loop === 'true'
            };
            
            // Touch/swipe için
            this.touchStartX = 0;
            this.touchEndX = 0;
            this.isTransitioning = false;
            
            // Autoplay timer
            this.autoplayTimer = null;
            
            this.init();
        }
        
        init() {
            if (this.totalSlides <= 1) {
                return;
            }
            
            // Event listener'ları ekle
            this.setupNavigation();
            this.setupPagination();
            this.setupTouch();
            this.setupKeyboard();
            
            // Autoplay başlat
            if (this.config.autoplay) {
                this.startAutoplay();
            }
            
            // Pause on hover
            this.container.addEventListener('mouseenter', () => this.pauseAutoplay());
            this.container.addEventListener('mouseleave', () => this.startAutoplay());
        }
        
        setupNavigation() {
            const prevBtn = this.container.querySelector('.cms-slider-prev');
            const nextBtn = this.container.querySelector('.cms-slider-next');
            
            if (prevBtn) {
                prevBtn.addEventListener('click', () => this.prev());
            }
            
            if (nextBtn) {
                nextBtn.addEventListener('click', () => this.next());
            }
        }
        
        setupPagination() {
            const dots = this.container.querySelectorAll('.cms-pagination-dot');
            
            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => this.goToSlide(index));
            });
        }
        
        setupTouch() {
            this.container.addEventListener('touchstart', (e) => {
                this.touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });
            
            this.container.addEventListener('touchend', (e) => {
                this.touchEndX = e.changedTouches[0].screenX;
                this.handleSwipe();
            }, { passive: true });
            
            // Mouse drag support
            let isDragging = false;
            let startX = 0;
            
            this.container.addEventListener('mousedown', (e) => {
                isDragging = true;
                startX = e.pageX;
                this.container.style.cursor = 'grabbing';
            });
            
            document.addEventListener('mousemove', (e) => {
                if (!isDragging) return;
                e.preventDefault();
            });
            
            document.addEventListener('mouseup', (e) => {
                if (!isDragging) return;
                isDragging = false;
                this.container.style.cursor = 'grab';
                
                const diffX = startX - e.pageX;
                if (Math.abs(diffX) > 50) {
                    if (diffX > 0) {
                        this.next();
                    } else {
                        this.prev();
                    }
                }
            });
        }
        
        setupKeyboard() {
            document.addEventListener('keydown', (e) => {
                if (!this.container.contains(document.activeElement) && 
                    document.activeElement !== document.body) {
                    return;
                }
                
                if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    this.prev();
                } else if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    this.next();
                }
            });
        }
        
        handleSwipe() {
            const diffX = this.touchStartX - this.touchEndX;
            
            if (Math.abs(diffX) > 50) {
                if (diffX > 0) {
                    this.next();
                } else {
                    this.prev();
                }
            }
        }
        
        goToSlide(index) {
            if (this.isTransitioning || index === this.currentIndex) {
                return;
            }
            
            this.isTransitioning = true;
            const prevIndex = this.currentIndex;
            this.currentIndex = index;
            
            this.transition(prevIndex, this.currentIndex);
            
            // Autoplay'i sıfırla
            if (this.config.autoplay) {
                this.resetAutoplay();
            }
        }
        
        next() {
            if (this.config.loop) {
                const nextIndex = (this.currentIndex + 1) % this.totalSlides;
                this.goToSlide(nextIndex);
            } else if (this.currentIndex < this.totalSlides - 1) {
                this.goToSlide(this.currentIndex + 1);
            }
        }
        
        prev() {
            if (this.config.loop) {
                const prevIndex = (this.currentIndex - 1 + this.totalSlides) % this.totalSlides;
                this.goToSlide(prevIndex);
            } else if (this.currentIndex > 0) {
                this.goToSlide(this.currentIndex - 1);
            }
        }
        
        transition(fromIndex, toIndex) {
            const fromSlide = this.slides[fromIndex];
            const toSlide = this.slides[toIndex];
            
            if (!fromSlide || !toSlide) {
                this.isTransitioning = false;
                return;
            }
            
            // Aktif sınıfını güncelle
            fromSlide.classList.remove('active');
            toSlide.classList.add('active');
            
            // Animasyon tipine göre geçiş
            this.applyAnimation(fromSlide, toSlide, fromIndex, toIndex);
            
            // Pagination güncelle
            this.updatePagination();
        }
        
        applyAnimation(fromSlide, toSlide, fromIndex, toIndex) {
            const animation = this.config.animation;
            
            // Tüm animasyonlar için ortak hazırlık
            toSlide.style.display = 'block';
            fromSlide.style.display = 'block';
            
            // Animasyon tipine göre işlem
            switch (animation) {
                case 'fade':
                    this.animateFade(fromSlide, toSlide);
                    break;
                case 'slide':
                    this.animateSlide(fromSlide, toSlide, fromIndex, toIndex);
                    break;
                case 'zoom':
                    this.animateZoom(fromSlide, toSlide);
                    break;
                case 'cube':
                    this.animateCube(fromSlide, toSlide, fromIndex, toIndex);
                    break;
                case 'flip':
                    this.animateFlip(fromSlide, toSlide, fromIndex, toIndex);
                    break;
                case 'coverflow':
                    this.animateCoverflow(fromSlide, toSlide, fromIndex, toIndex);
                    break;
                case 'cards':
                    this.animateCards(fromSlide, toSlide, fromIndex, toIndex);
                    break;
                default:
                    this.animateFade(fromSlide, toSlide);
            }
        }
        
        animateFade(fromSlide, toSlide) {
            toSlide.style.opacity = '0';
            toSlide.style.zIndex = '2';
            fromSlide.style.zIndex = '1';
            
            requestAnimationFrame(() => {
                toSlide.style.transition = 'opacity 0.6s ease';
                toSlide.style.opacity = '1';
                fromSlide.style.transition = 'opacity 0.6s ease';
                fromSlide.style.opacity = '0';
                
                setTimeout(() => {
                    fromSlide.style.opacity = '';
                    fromSlide.style.transition = '';
                    fromSlide.style.zIndex = '';
                    toSlide.style.transition = '';
                    toSlide.style.zIndex = '';
                    this.isTransitioning = false;
                }, 600);
            });
        }
        
        animateSlide(fromSlide, toSlide, fromIndex, toIndex) {
            const direction = toIndex > fromIndex ? 1 : -1;
            
            toSlide.style.transform = `translateX(${direction * 100}%)`;
            toSlide.style.zIndex = '2';
            fromSlide.style.zIndex = '1';
            
            requestAnimationFrame(() => {
                toSlide.style.transition = 'transform 0.6s ease';
                fromSlide.style.transition = 'transform 0.6s ease';
                toSlide.style.transform = 'translateX(0)';
                fromSlide.style.transform = `translateX(${-direction * 100}%)`;
                
                setTimeout(() => {
                    fromSlide.style.transform = '';
                    fromSlide.style.transition = '';
                    fromSlide.style.zIndex = '';
                    toSlide.style.transition = '';
                    toSlide.style.zIndex = '';
                    this.isTransitioning = false;
                }, 600);
            });
        }
        
        animateZoom(fromSlide, toSlide) {
            toSlide.style.opacity = '0';
            toSlide.style.transform = 'scale(1.2)';
            toSlide.style.zIndex = '2';
            fromSlide.style.zIndex = '1';
            
            requestAnimationFrame(() => {
                toSlide.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                fromSlide.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                toSlide.style.opacity = '1';
                toSlide.style.transform = 'scale(1)';
                fromSlide.style.opacity = '0';
                fromSlide.style.transform = 'scale(0.9)';
                
                setTimeout(() => {
                    fromSlide.style.opacity = '';
                    fromSlide.style.transform = '';
                    fromSlide.style.transition = '';
                    fromSlide.style.zIndex = '';
                    toSlide.style.transition = '';
                    toSlide.style.zIndex = '';
                    this.isTransitioning = false;
                }, 600);
            });
        }
        
        animateCube(fromSlide, toSlide, fromIndex, toIndex) {
            const direction = toIndex > fromIndex ? 1 : -1;
            
            this.container.style.perspective = '1000px';
            toSlide.style.transform = `rotateY(${direction * 90}deg)`;
            toSlide.style.transformOrigin = direction > 0 ? 'left' : 'right';
            toSlide.style.zIndex = '2';
            fromSlide.style.zIndex = '1';
            
            requestAnimationFrame(() => {
                toSlide.style.transition = 'transform 0.8s ease';
                fromSlide.style.transition = 'transform 0.8s ease';
                toSlide.style.transform = 'rotateY(0deg)';
                fromSlide.style.transform = `rotateY(${-direction * 90}deg)`;
                
                setTimeout(() => {
                    fromSlide.style.transform = '';
                    fromSlide.style.transition = '';
                    fromSlide.style.zIndex = '';
                    toSlide.style.transition = '';
                    toSlide.style.zIndex = '';
                    toSlide.style.transformOrigin = '';
                    this.isTransitioning = false;
                }, 800);
            });
        }
        
        animateFlip(fromSlide, toSlide, fromIndex, toIndex) {
            const direction = toIndex > fromIndex ? 1 : -1;
            
            this.container.style.perspective = '1000px';
            toSlide.style.transform = `rotateX(${direction * 90}deg)`;
            toSlide.style.zIndex = '2';
            fromSlide.style.zIndex = '1';
            
            requestAnimationFrame(() => {
                toSlide.style.transition = 'transform 0.8s ease';
                fromSlide.style.transition = 'transform 0.8s ease';
                toSlide.style.transform = 'rotateX(0deg)';
                fromSlide.style.transform = `rotateX(${-direction * 90}deg)`;
                
                setTimeout(() => {
                    fromSlide.style.transform = '';
                    fromSlide.style.transition = '';
                    fromSlide.style.zIndex = '';
                    toSlide.style.transition = '';
                    toSlide.style.zIndex = '';
                    this.isTransitioning = false;
                }, 800);
            });
        }
        
        animateCoverflow(fromSlide, toSlide, fromIndex, toIndex) {
            this.container.style.perspective = '1200px';
            
            // Tüm slide'ları konumlandır
            this.slides.forEach((slide, index) => {
                const diff = index - toIndex;
                const absDiff = Math.abs(diff);
                
                if (absDiff <= 2) {
                    slide.style.transform = `translateX(${diff * 150}px) translateZ(${-absDiff * 200}px) rotateY(${diff * 30}deg)`;
                    slide.style.opacity = absDiff <= 1 ? '1' : '0.5';
                    slide.style.zIndex = this.totalSlides - absDiff;
                } else {
                    slide.style.opacity = '0';
                    slide.style.zIndex = '0';
                }
            });
            
            setTimeout(() => {
                this.isTransitioning = false;
            }, 800);
        }
        
        animateCards(fromSlide, toSlide, fromIndex, toIndex) {
            const direction = toIndex > fromIndex ? 1 : -1;
            
            toSlide.style.transform = `translateX(${direction * 100}%) scale(0.8)`;
            toSlide.style.zIndex = '2';
            fromSlide.style.zIndex = '1';
            
            requestAnimationFrame(() => {
                toSlide.style.transition = 'transform 0.6s ease';
                fromSlide.style.transition = 'transform 0.6s ease, opacity 0.6s ease';
                toSlide.style.transform = 'translateX(0) scale(1)';
                fromSlide.style.transform = `translateX(${-direction * 50}%) scale(0.9)`;
                fromSlide.style.opacity = '0.5';
                
                setTimeout(() => {
                    fromSlide.style.transform = '';
                    fromSlide.style.opacity = '';
                    fromSlide.style.transition = '';
                    fromSlide.style.zIndex = '';
                    toSlide.style.transition = '';
                    toSlide.style.zIndex = '';
                    this.isTransitioning = false;
                }, 600);
            });
        }
        
        updatePagination() {
            const dots = this.container.querySelectorAll('.cms-pagination-dot');
            dots.forEach((dot, index) => {
                if (index === this.currentIndex) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }
        
        startAutoplay() {
            if (!this.config.autoplay) return;
            
            this.autoplayTimer = setInterval(() => {
                this.next();
            }, this.config.autoplayDelay);
        }
        
        pauseAutoplay() {
            if (this.autoplayTimer) {
                clearInterval(this.autoplayTimer);
                this.autoplayTimer = null;
            }
        }
        
        resetAutoplay() {
            this.pauseAutoplay();
            this.startAutoplay();
        }
    }
    
    // DOM yüklendiğinde tüm slider'ları başlat
    document.addEventListener('DOMContentLoaded', function() {
        const sliders = document.querySelectorAll('.cms-slider');
        sliders.forEach(element => {
            new CMSSlider(element);
        });
    });
})();
