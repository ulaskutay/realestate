/**
 * Çizgi Aks Gayrimenkul - Tema scriptleri
 * Hero slider, mobil menü, Diğer Ayrıntılar toggle
 */
(function() {
    'use strict';

    function init() {
    // Hero slider: dot sayısı = gösterilen ilan (slide) sayısı; geçişler yavaş ve düzgün
    var slider = document.getElementById('cizgiaks-hero-slider');
    if (slider) {
        var slides = slider.querySelectorAll('.cizgiaks-hero-slide');
        var total = slides.length;
        var current = 0;
        var prevBtn = document.querySelector('.cizgiaks-hero-slider-prev');
        var nextBtn = document.querySelector('.cizgiaks-hero-slider-next');
        var dotsEl = document.getElementById('cizgiaks-hero-dots');
        var intervalId = null;
        var AUTO_ADVANCE_MS = 7000;  // 7 saniyede bir (daha sakin)
        var isTransitioning = false;

        function goTo(idx) {
            if (total === 0 || isTransitioning) return;
            var nextIdx = (idx % total + total) % total;
            if (nextIdx === current) return;
            isTransitioning = true;
            current = nextIdx;
            slides.forEach(function(s, i) {
                s.classList.toggle('active', i === current);
            });
            if (dotsEl) {
                var dots = dotsEl.querySelectorAll('button');
                if (dots.length === total) {
                    dots.forEach(function(d, i) {
                        d.classList.toggle('active', i === current);
                    });
                }
            }
            setTimeout(function() {
                isTransitioning = false;
            }, 320);
        }

        function next() {
            goTo(current + 1);
        }

        function prev() {
            goTo(current - 1);
        }

        if (prevBtn) prevBtn.addEventListener('click', function() {
            prev();
            resetInterval();
        });
        if (nextBtn) nextBtn.addEventListener('click', function() {
            next();
            resetInterval();
        });

        // Dot'lar: sadece gerçek slide sayısı kadar (gösterilen ilan sayısı)
        if (dotsEl && total > 1) {
            dotsEl.innerHTML = '';
            for (var i = 0; i < total; i++) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.setAttribute('aria-label', 'İlan ' + (i + 1));
                btn.classList.toggle('active', i === 0);
                (function(j) {
                    btn.addEventListener('click', function() {
                        goTo(j);
                        resetInterval();
                    });
                })(i);
                dotsEl.appendChild(btn);
            }
        }

        function resetInterval() {
            if (intervalId) clearInterval(intervalId);
            intervalId = null;
            if (total > 1) {
                intervalId = setInterval(function() {
                    next();
                }, AUTO_ADVANCE_MS);
            }
        }
        // Otomatik geçişi biraz geciktir (sayfa yüklendikten sonra)
        setTimeout(resetInterval, 800);
    }

    // Masaüstünde footer sütunları görünsün; mobilde accordion kapalı kalsın
    function footerDesktopOpen() {
        var isDesktop = window.matchMedia && window.matchMedia('(min-width: 768px)').matches;
        var accordions = document.querySelectorAll('.cizgiaks-footer-modern .cizgiaks-footer-accordion');
        accordions.forEach(function(d) {
            if (isDesktop) d.setAttribute('open', 'open');
            else d.removeAttribute('open');
        });
    }
    footerDesktopOpen();
    window.addEventListener('resize', footerDesktopOpen);

    // Diğer Ayrıntılar toggle
    var detailsToggle = document.getElementById('hero-details-toggle');
    var extraDetails = document.getElementById('hero-extra-details');
    if (detailsToggle && extraDetails) {
        detailsToggle.addEventListener('click', function() {
            var isOpen = !extraDetails.hidden;
            extraDetails.hidden = isOpen;
            detailsToggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            var icon = detailsToggle.querySelector('i.fa-chevron-down');
            if (icon) {
                icon.style.transform = isOpen ? 'none' : 'rotate(180deg)';
            }
        });
    }

    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
