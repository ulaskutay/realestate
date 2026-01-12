/**
 * Codetic Theme - Custom JavaScript
 */

(function() {
    'use strict';
    
    // DOM yüklendiğinde çalış
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    function init() {
        // Smooth scroll için anchor linkler
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && href.length > 1) {
                    const target = document.querySelector(href);
                    if (target) {
                        e.preventDefault();
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });
        
        // Initialize particle animation
        initParticleAnimation();
    }
    
    /**
     * Teknoloji Temalı Parçacık Animasyonu
     * Canvas-based parçacık sistemi ile interaktif teknoloji animasyonu
     */
    function initParticleAnimation() {
        const containers = document.querySelectorAll('.particle-animation-container');
        
        if (containers.length === 0) {
            return;
        }
        
        containers.forEach(container => {
            const canvas = container.querySelector('.particle-canvas');
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            if (!ctx) return;
            
            // Ayarları al
            const particleCount = parseInt(container.getAttribute('data-particle-count')) || 100;
            const particleSpeed = parseFloat(container.getAttribute('data-particle-speed')) || 2;
            const particleColor = container.getAttribute('data-particle-color') || '#60a5fa';
            const connectionDistance = parseInt(container.getAttribute('data-connection-distance')) || 120;
            const interactionEnabled = container.getAttribute('data-interaction-enabled') === 'true';
            
            // Canvas boyutunu ayarla - layout sorgularını batch'le
            let pendingResize = false;
            function resizeCanvas() {
                if (pendingResize) return;
                pendingResize = true;
                
                requestAnimationFrame(() => {
                    // Tüm layout sorgularını aynı frame'de yap
                    const rect = container.getBoundingClientRect();
                    canvas.width = rect.width;
                    canvas.height = rect.height;
                    pendingResize = false;
                });
            }
            
            resizeCanvas();
            window.addEventListener('resize', resizeCanvas);
            
            // Teknoloji symbol'leri
            const techSymbols = ['</>', '{}', '[]', '()', '#', '*', '+', '-', '=', '|', '&', '%', '$', '@'];
            
            // Parçacık sınıfı
            class Particle {
                constructor() {
                    this.reset();
                    this.y = Math.random() * canvas.height;
                    this.symbol = techSymbols[Math.floor(Math.random() * techSymbols.length)];
                    this.size = Math.random() * 2 + 1.5;
                    this.opacity = Math.random() * 0.4 + 0.6; // Dark tema için daha parlak
                }
                
                reset() {
                    this.x = Math.random() * canvas.width;
                    this.y = Math.random() * canvas.height;
                    this.vx = (Math.random() - 0.5) * particleSpeed;
                    this.vy = (Math.random() - 0.5) * particleSpeed;
                }
                
                update(mouse) {
                    // Mouse etkileşimi
                    if (interactionEnabled && mouse.x !== null && mouse.y !== null) {
                        const dx = mouse.x - this.x;
                        const dy = mouse.y - this.y;
                        const distance = Math.sqrt(dx * dx + dy * dy);
                        const maxDistance = 100;
                        
                        if (distance < maxDistance) {
                            const force = (maxDistance - distance) / maxDistance;
                            const angle = Math.atan2(dy, dx);
                            this.vx -= Math.cos(angle) * force * 0.5;
                            this.vy -= Math.sin(angle) * force * 0.5;
                        }
                    }
                    
                    // Hareket
                    this.x += this.vx;
                    this.y += this.vy;
                    
                    // Sınır kontrolü
                    if (this.x < 0 || this.x > canvas.width) {
                        this.vx *= -1;
                        this.x = Math.max(0, Math.min(canvas.width, this.x));
                    }
                    if (this.y < 0 || this.y > canvas.height) {
                        this.vy *= -1;
                        this.y = Math.max(0, Math.min(canvas.height, this.y));
                    }
                    
                    // Hız sınırlaması
                    const maxSpeed = particleSpeed * 2;
                    const speed = Math.sqrt(this.vx * this.vx + this.vy * this.vy);
                    if (speed > maxSpeed) {
                        this.vx = (this.vx / speed) * maxSpeed;
                        this.vy = (this.vy / speed) * maxSpeed;
                    }
                    
                    // Sürtünme
                    this.vx *= 0.98;
                    this.vy *= 0.98;
                }
                
                draw() {
                    ctx.save();
                    ctx.globalAlpha = this.opacity;
                    ctx.fillStyle = particleColor;
                    ctx.font = `${this.size * 10}px monospace`;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(this.symbol, this.x, this.y);
                    ctx.restore();
                }
            }
            
            // Parçacıkları oluştur
            const particles = [];
            const actualCount = window.innerWidth < 768 ? Math.floor(particleCount * 0.6) : particleCount;
            
            for (let i = 0; i < actualCount; i++) {
                particles.push(new Particle());
            }
            
            // Mouse pozisyonu
            const mouse = { x: null, y: null };
            
            if (interactionEnabled) {
                // Mouse pozisyonunu cache'le ve requestAnimationFrame ile güncelle
                let cachedRect = null;
                let rectUpdateFrame = null;
                let pendingMousePos = { x: null, y: null };
                
                function updateMousePosition(e) {
                    // Event koordinatlarını sakla
                    pendingMousePos.x = e.clientX;
                    pendingMousePos.y = e.clientY;
                    
                    // getBoundingClientRect'i batch'le
                    if (!rectUpdateFrame) {
                        rectUpdateFrame = requestAnimationFrame(() => {
                            cachedRect = container.getBoundingClientRect();
                            mouse.x = pendingMousePos.x - cachedRect.left;
                            mouse.y = pendingMousePos.y - cachedRect.top;
                            rectUpdateFrame = null;
                        });
                    } else if (cachedRect) {
                        // Eğer zaten bir frame bekliyorsa, cached rect kullan
                        mouse.x = pendingMousePos.x - cachedRect.left;
                        mouse.y = pendingMousePos.y - cachedRect.top;
                    }
                }
                
                container.addEventListener('mousemove', updateMousePosition);
                
                container.addEventListener('mouseleave', () => {
                    mouse.x = null;
                    mouse.y = null;
                    cachedRect = null;
                    pendingMousePos.x = null;
                    pendingMousePos.y = null;
                    if (rectUpdateFrame) {
                        cancelAnimationFrame(rectUpdateFrame);
                        rectUpdateFrame = null;
                    }
                });
            }
            
            // Animasyon döngüsü
            let animationId;
            
            function animate() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                // Parçacıkları güncelle ve çiz
                particles.forEach(particle => {
                    particle.update(mouse);
                    particle.draw();
                });
                
                // Bağlantı çizgileri
                ctx.strokeStyle = particleColor;
                ctx.lineWidth = 0.5;
                
                for (let i = 0; i < particles.length; i++) {
                    for (let j = i + 1; j < particles.length; j++) {
                        const dx = particles[i].x - particles[j].x;
                        const dy = particles[i].y - particles[j].y;
                        const distance = Math.sqrt(dx * dx + dy * dy);
                        
                        if (distance < connectionDistance) {
                            const opacity = (1 - distance / connectionDistance) * 0.5; // Dark tema için daha görünür
                            ctx.globalAlpha = opacity;
                            ctx.beginPath();
                            ctx.moveTo(particles[i].x, particles[i].y);
                            ctx.lineTo(particles[j].x, particles[j].y);
                            ctx.stroke();
                        }
                    }
                }
                
                ctx.globalAlpha = 1;
                animationId = requestAnimationFrame(animate);
            }
            
            // Animasyonu başlat
            animate();
            
            // Cleanup için container'a referans ekle
            container._particleAnimation = {
                stop: () => {
                    if (animationId) {
                        cancelAnimationFrame(animationId);
                    }
                    window.removeEventListener('resize', resizeCanvas);
                }
            };
        });
    }
})();

