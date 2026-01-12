<?php
/**
 * Codetic Theme - Animated Hero Component
 * React component'inin PHP/JavaScript versiyonu
 */

// Admin sayfasında render etme
if (defined('IS_ADMIN') && IS_ADMIN) {
    return;
}
if (isset($_SERVER['REQUEST_URI']) && (
    strpos($_SERVER['REQUEST_URI'], '/admin') !== false ||
    strpos($_SERVER['REQUEST_URI'], 'admin.php') !== false ||
    (isset($_GET['page']) && strpos($_GET['page'], 'admin') !== false)
)) {
    return;
}

// Duplicate render kontrolü - Hero sadece bir kez render edilmeli
if (!isset($GLOBALS['codetic_hero_rendered'])) {
    $GLOBALS['codetic_hero_rendered'] = false;
}

if ($GLOBALS['codetic_hero_rendered']) {
    return; // Zaten render edilmişse, hiçbir şey yapma
}

$GLOBALS['codetic_hero_rendered'] = true; // Render başladı, flag'i set et

$section = $section ?? [];
$settings = $section['settings'] ?? [];

// Hero ayarları
$heroTitlePrefix = $themeLoader->getSetting('title_prefix', 'This is something', 'hero');
$animatedWords = $themeLoader->getSetting('animated_words', 'amazing,new,wonderful,beautiful,smart', 'hero');
$animatedWordsArray = array_map('trim', explode(',', $animatedWords));
if (empty($animatedWordsArray) || count($animatedWordsArray) < 2) {
    $animatedWordsArray = ['amazing', 'new', 'wonderful', 'beautiful', 'smart'];
}
$heroSubtitle = $themeLoader->getSetting('subtitle', 'Managing a small business today is already tough. Avoid further complications by ditching outdated, tedious trade methods. Our goal is to streamline SMB trade, making it easier and faster than ever.', 'hero');
$primaryButtonText = $themeLoader->getSetting('button_text', 'Hemen Başla', 'hero');
$primaryButtonLink = $themeLoader->getSetting('button_link', '/contact', 'hero');
$secondaryButtonText = $themeLoader->getSetting('secondary_button_text', 'Demo Paneli Gör', 'hero');
$secondaryButtonLink = $themeLoader->getSetting('secondary_button_link', '/admin', 'hero');

// Top Button Özelleştirme
$topButtonEnabled = $themeLoader->getSetting('top_button_enabled', true, 'hero');
$topButtonEnabled = !($topButtonEnabled === '0' || $topButtonEnabled === false || $topButtonEnabled === 'false');
$topButtonText = $themeLoader->getSetting('top_button_text', 'Read our launch article', 'hero');
$topButtonLink = $themeLoader->getSetting('top_button_link', '/blog', 'hero');
$topButtonStyle = $themeLoader->getSetting('top_button_style', 'secondary', 'hero'); // secondary, outline, primary
$topButtonIcon = $themeLoader->getSetting('top_button_icon', 'arrow', 'hero'); // arrow, external, none

$heroId = 'animated-hero-' . uniqid();
?>

<section class="relative w-full min-h-[70vh] md:min-h-screen flex items-center justify-center overflow-hidden bg-[#0a0a0f]" id="hero-section">
    <!-- Shader Canvas -->
    <canvas id="hero-shader-canvas" class="absolute top-0 left-0 w-full pointer-events-none z-0"></canvas>
    
    <!-- Sade Arka Plan Efektleri -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <!-- Minimal Gradient Overlay -->
        <div class="absolute inset-0 penta-gradient-overlay-simple"></div>
        
        <!-- Sade Glow Orbs - Sadece 2 Adet -->
        <div class="absolute bottom-0 right-0 w-[700px] h-[700px] rounded-full blur-3xl penta-glow-orb-simple orb-1"></div>
        <div class="absolute top-1/3 left-1/4 w-[500px] h-[500px] rounded-full blur-3xl penta-glow-orb-simple orb-2"></div>
        
        <!-- Çok Hafif Grid Pattern -->
        <div class="absolute inset-0 penta-subtle-texture opacity-5"></div>
    </div>
    
    <div class="container mx-auto relative z-20">
        <div class="flex gap-6 py-12 md:py-20 lg:py-40 items-center justify-center flex-col">
            <!-- Top Button - Özelleştirilebilir -->
            <?php if ($topButtonEnabled && !empty($topButtonText)): ?>
            <div class="hero-top-button-wrapper">
                <a href="<?php echo esc_url($topButtonLink); ?>" class="btn-top-<?php echo esc_attr($topButtonStyle); ?> btn-sm inline-flex items-center gap-2">
                    <?php echo esc_html($topButtonText); ?>
                    <?php if ($topButtonIcon === 'arrow'): ?>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                    <?php elseif ($topButtonIcon === 'external'): ?>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    <?php endif; ?>
                </a>
            </div>
            <?php endif; ?>
            
            <!-- Simple Animated Title - React Component Style -->
            <div class="flex gap-4 flex-col max-w-4xl mx-auto px-4">
                <h1 class="text-4xl md:text-6xl lg:text-7xl max-w-4xl tracking-tight text-center text-white hero-title-main">
                    <span class="hero-title-prefix block mb-2"><?php echo esc_html($heroTitlePrefix); ?></span>
                    <span class="relative flex w-full justify-center overflow-hidden text-center md:pb-4 md:pt-1" id="<?php echo esc_attr($heroId); ?>-words">
                        &nbsp;
                        <?php foreach ($animatedWordsArray as $index => $word): ?>
                        <span class="absolute hero-animated-word-bold" data-index="<?php echo esc_attr($index); ?>">
                            <?php echo esc_html($word); ?>
                        </span>
                        <?php endforeach; ?>
                    </span>
                </h1>

                <p class="text-base md:text-lg leading-relaxed tracking-tight text-muted-foreground max-w-4xl text-center text-slate-300 font-light mb-8">
                    <?php echo esc_html($heroSubtitle); ?>
                </p>
                
                <!-- Hero Butonları -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mt-8">
                    <?php if (!empty($primaryButtonText)): ?>
                    <a href="<?php echo esc_url($primaryButtonLink); ?>" class="btn-primary px-8 py-3.5 rounded-lg font-semibold text-base inline-flex items-center gap-2 transition-all duration-300">
                        <?php echo esc_html($primaryButtonText); ?>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($secondaryButtonText)): ?>
                    <a href="<?php echo esc_url($secondaryButtonLink); ?>" class="btn-secondary px-8 py-3.5 rounded-lg font-semibold text-base inline-flex items-center gap-2 transition-all duration-300">
                        <?php echo esc_html($secondaryButtonText); ?>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
    </div>
    
    <!-- Scroll Down Button -->
    <a href="#" class="hero-scroll-link hero-scroll-indicator" role="button" aria-label="Aşağı kaydır">
        <span class="scroll-text text-xs uppercase tracking-wider text-white/60 opacity-0 transition-opacity duration-300">Aşağı Kaydır</span>
        <div class="scroll-mouse"></div>
    </a>
    
    <!-- Bottom Mask - Lamp'e geçiş -->
    <div class="hero-bottom-mask"></div>
</section>

<script>
(function() {
    'use strict';
    
    // Sadece frontend'de çalış (admin panelinde değil)
    // Check multiple conditions to ensure we're not in admin
    if (window.location.pathname.includes('/admin') || 
        window.location.pathname.includes('admin.php') ||
        document.body.classList.contains('admin-page') ||
        window.location.search.includes('page=themes') ||
        window.location.search.includes('page=admin')) {
        return;
    }
    
    // Check if we're in an iframe that might be admin preview
    try {
        if (window.self !== window.top && window.top.location.pathname.includes('/admin')) {
            return;
        }
    } catch(e) {
        // Cross-origin iframe, assume it's safe
    }
    
    const heroId = '<?php echo esc_js($heroId); ?>';
    const wordsContainer = document.getElementById(heroId + '-words');
    if (!wordsContainer) return;
    
    const words = wordsContainer.querySelectorAll('.hero-animated-word-bold');
    if (words.length === 0) return;
    
    let titleNumber = 0;
    const totalWords = words.length;
    let animationTimeout = null;
    
    // Tüm kelimeleri başlangıçta gizle
    words.forEach((word, index) => {
        word.style.opacity = '0';
        word.style.transform = 'translateY(-100px)';
        word.style.transition = 'none';
    });
    
    // İlk kelimeyi göster
    if (words[0]) {
        words[0].style.transition = 'all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
        words[0].style.opacity = '1';
        words[0].style.transform = 'translateY(0)';
    }
    
    function animateWord() {
        // Mevcut kelimeyi gizle
        const currentWord = words[titleNumber];
        if (currentWord) {
            currentWord.style.transition = 'all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
            currentWord.style.opacity = '0';
            currentWord.style.transform = titleNumber === totalWords - 1 ? 'translateY(150px)' : 'translateY(-150px)';
        }
        
        // Sonraki kelimeyi hesapla
        if (titleNumber === totalWords - 1) {
            titleNumber = 0;
        } else {
            titleNumber = titleNumber + 1;
        }
        
        // Yeni kelimeyi göster
        const nextWord = words[titleNumber];
        if (nextWord) {
            // Önce pozisyonu ayarla
            nextWord.style.transition = 'none';
            nextWord.style.opacity = '0';
            nextWord.style.transform = titleNumber === 0 ? 'translateY(150px)' : 'translateY(-100px)';
            
            // Sonra animasyonu başlat
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    nextWord.style.transition = 'all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
                    nextWord.style.opacity = '1';
                    nextWord.style.transform = 'translateY(0)';
                });
            });
        }
        
        // 2 saniye sonra tekrarla
        if (animationTimeout) {
            clearTimeout(animationTimeout);
        }
        animationTimeout = setTimeout(animateWord, 2000);
    }
    
    // İlk animasyonu başlat
    setTimeout(() => {
        animateWord();
    }, 2000);
    
    // Cleanup function
    window.addEventListener('beforeunload', () => {
        if (animationTimeout) {
            clearTimeout(animationTimeout);
        }
    });
})();

// Minimal WebGL Shader Background
(function() {
    'use strict';
    
    // Sadece frontend'de çalış (admin panelinde değil)
    if (window.location.pathname.includes('/admin') || 
        window.location.pathname.includes('admin.php') ||
        document.body.classList.contains('admin-page') ||
        window.location.search.includes('page=themes') ||
        window.location.search.includes('page=admin')) {
        return;
    }
    
    // Check if we're in an iframe that might be admin preview
    try {
        if (window.self !== window.top && window.top.location.pathname.includes('/admin')) {
            return;
        }
    } catch(e) {
        // Cross-origin iframe, assume it's safe
    }
    
    const canvas = document.getElementById('hero-shader-canvas');
    if (!canvas) return;
    
    const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
    if (!gl) {
        console.warn('WebGL not supported, falling back to CSS background');
        return;
    }
    
    // Vertex Shader
    const vertexShaderSource = `
        attribute vec2 a_position;
        void main() {
            gl_Position = vec4(a_position, 0.0, 1.0);
        }
    `;
    
    // Fragment Shader - Minimal flowing gradient with noise
    const fragmentShaderSource = `
        precision highp float;
        
        uniform vec2 u_resolution;
        uniform float u_time;
        uniform vec2 u_mouse;
        
        // Simplex noise functions
        vec3 mod289(vec3 x) { return x - floor(x * (1.0 / 289.0)) * 289.0; }
        vec2 mod289(vec2 x) { return x - floor(x * (1.0 / 289.0)) * 289.0; }
        vec3 permute(vec3 x) { return mod289(((x*34.0)+1.0)*x); }
        
        float snoise(vec2 v) {
            const vec4 C = vec4(0.211324865405187, 0.366025403784439,
                               -0.577350269189626, 0.024390243902439);
            vec2 i  = floor(v + dot(v, C.yy));
            vec2 x0 = v - i + dot(i, C.xx);
            vec2 i1;
            i1 = (x0.x > x0.y) ? vec2(1.0, 0.0) : vec2(0.0, 1.0);
            vec4 x12 = x0.xyxy + C.xxzz;
            x12.xy -= i1;
            i = mod289(i);
            vec3 p = permute(permute(i.y + vec3(0.0, i1.y, 1.0))
                + i.x + vec3(0.0, i1.x, 1.0));
            vec3 m = max(0.5 - vec3(dot(x0,x0), dot(x12.xy,x12.xy),
                dot(x12.zw,x12.zw)), 0.0);
            m = m*m;
            m = m*m;
            vec3 x = 2.0 * fract(p * C.www) - 1.0;
            vec3 h = abs(x) - 0.5;
            vec3 ox = floor(x + 0.5);
            vec3 a0 = x - ox;
            m *= 1.79284291400159 - 0.85373472095314 * (a0*a0 + h*h);
            vec3 g;
            g.x  = a0.x  * x0.x  + h.x  * x0.y;
            g.yz = a0.yz * x12.xz + h.yz * x12.yw;
            return 130.0 * dot(m, g);
        }
        
        // Fractal Brownian Motion - Reduced octaves for performance (3 instead of 4)
        float fbm(vec2 p) {
            float value = 0.0;
            float amplitude = 0.5;
            float frequency = 1.0;
            for(int i = 0; i < 3; i++) { // Reduced from 4 to 3 octaves
                value += amplitude * snoise(p * frequency);
                amplitude *= 0.5;
                frequency *= 2.0;
            }
            return value;
        }
        
        void main() {
            vec2 uv = gl_FragCoord.xy / u_resolution.xy;
            vec2 p = uv * 2.0 - 1.0;
            p.x *= u_resolution.x / u_resolution.y;
            
            // Very slow time for subtle movement
            float slowTime = u_time * 0.08;
            
            // Mouse influence (subtle)
            vec2 mouseInfluence = (u_mouse - 0.5) * 0.15;
            
            // Create flowing noise layers - Reduced to 2 layers for performance
            float noise1 = fbm(p * 0.8 + slowTime * 0.3 + mouseInfluence);
            float noise2 = fbm(p * 1.2 - slowTime * 0.2 + vec2(5.0, 3.0));
            
            // Combine noise layers - Simplified combination
            float combinedNoise = noise1 * 0.6 + noise2 * 0.4;
            
            // Color palette - Deep space blues and purples
            vec3 color1 = vec3(0.039, 0.039, 0.059); // Base dark #0a0a0f
            vec3 color2 = vec3(0.145, 0.255, 0.478); // Blue #2541a7
            vec3 color3 = vec3(0.345, 0.227, 0.612); // Purple #583a9c
            vec3 color4 = vec3(0.231, 0.510, 0.965); // Bright blue #3b82f6
            
            // Create gradient based on noise - Simplified
            float gradient = smoothstep(-0.5, 0.8, combinedNoise);
            float gradient2 = smoothstep(-0.3, 0.6, noise2);
            
            // Mix colors - Simplified blending
            vec3 finalColor = mix(color1, color2, gradient * 0.3);
            finalColor = mix(finalColor, color3, gradient2 * 0.2);
            
            // Add subtle bright spots - Removed noise3 dependency
            float brightSpots = smoothstep(0.4, 0.7, noise1) * smoothstep(0.3, 0.6, noise2);
            finalColor = mix(finalColor, color4, brightSpots * 0.15);
            
            // Vignette effect
            float vignette = 1.0 - length(uv - 0.5) * 0.8;
            vignette = smoothstep(0.0, 1.0, vignette);
            finalColor *= vignette;
            
            // Add very subtle grain - Reduced frequency for performance
            float grain = snoise(gl_FragCoord.xy * 0.3 + u_time * 50.0) * 0.01;
            finalColor += grain;
            
            // Output with low opacity for subtlety
            gl_FragColor = vec4(finalColor, 0.6);
        }
    `;
    
    // Compile shader
    function compileShader(gl, source, type) {
        const shader = gl.createShader(type);
        gl.shaderSource(shader, source);
        gl.compileShader(shader);
        if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
            console.error('Shader compile error:', gl.getShaderInfoLog(shader));
            gl.deleteShader(shader);
            return null;
        }
        return shader;
    }
    
    // Create program
    const vertexShader = compileShader(gl, vertexShaderSource, gl.VERTEX_SHADER);
    const fragmentShader = compileShader(gl, fragmentShaderSource, gl.FRAGMENT_SHADER);
    
    if (!vertexShader || !fragmentShader) return;
    
    const program = gl.createProgram();
    gl.attachShader(program, vertexShader);
    gl.attachShader(program, fragmentShader);
    gl.linkProgram(program);
    
    if (!gl.getProgramParameter(program, gl.LINK_STATUS)) {
        console.error('Program link error:', gl.getProgramInfoLog(program));
        return;
    }
    
    gl.useProgram(program);
    
    // Set up geometry (fullscreen quad)
    const positionBuffer = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, positionBuffer);
    gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([
        -1, -1,
         1, -1,
        -1,  1,
        -1,  1,
         1, -1,
         1,  1
    ]), gl.STATIC_DRAW);
    
    const positionLocation = gl.getAttribLocation(program, 'a_position');
    gl.enableVertexAttribArray(positionLocation);
    gl.vertexAttribPointer(positionLocation, 2, gl.FLOAT, false, 0, 0);
    
    // Get uniform locations
    const resolutionLocation = gl.getUniformLocation(program, 'u_resolution');
    const timeLocation = gl.getUniformLocation(program, 'u_time');
    const mouseLocation = gl.getUniformLocation(program, 'u_mouse');
    
    // Mouse tracking - Throttled for performance
    let mouseX = 0.5, mouseY = 0.5;
    let targetMouseX = 0.5, targetMouseY = 0.5;
    let lastMouseUpdate = 0;
    const MOUSE_UPDATE_INTERVAL = 100; // Update max every 100ms
    
    function updateMousePosition(e) {
        const now = performance.now();
        if (now - lastMouseUpdate >= MOUSE_UPDATE_INTERVAL) {
            targetMouseX = e.clientX / window.innerWidth;
            targetMouseY = 1.0 - (e.clientY / window.innerHeight);
            lastMouseUpdate = now;
        }
    }
    
    // Use passive listener for better scroll performance
    document.addEventListener('mousemove', updateMousePosition, { passive: true });
    
    // Canvas yüksekliğini hero section yüksekliğine göre ayarla
    // Layout sorgularını batch'lemek için cached değerler kullan
    let cachedHeroHeight = null;
    let cachedCanvasSize = { width: 0, height: 0 };
    
    function updateCanvasHeight() {
        const heroSection = document.getElementById('hero-section');
        
        if (heroSection) {
            // offsetHeight sorgusunu batch'le
            cachedHeroHeight = heroSection.offsetHeight;
            canvas.style.height = cachedHeroHeight + 'px';
        } else {
            // Fallback: Viewport yüksekliği
            cachedHeroHeight = window.innerHeight;
            canvas.style.height = cachedHeroHeight + 'px';
        }
    }
    
    // İlk yüksekliği ayarla
    updateCanvasHeight();
    
    // Resize handler - Throttled and optimized
    let resizeTimeout;
    let pendingResize = false;
    
    function resize() {
        // Throttle resize operations
        if (pendingResize) return;
        pendingResize = true;
        
        // Tüm layout sorgularını aynı frame'de yap
        updateCanvasHeight();
        
        const dpr = Math.min(window.devicePixelRatio, 2);
        // clientWidth ve clientHeight sorgularını batch'le
        const width = canvas.clientWidth * dpr;
        const height = canvas.clientHeight * dpr;
        
        // Sadece değiştiyse güncelle
        if (cachedCanvasSize.width !== width || cachedCanvasSize.height !== height) {
            canvas.width = width;
            canvas.height = height;
            gl.viewport(0, 0, width, height);
            cachedCanvasSize.width = width;
            cachedCanvasSize.height = height;
        }
        
        pendingResize = false;
    }
    
    // Debounced window resize handler
    window.addEventListener('resize', () => {
        if (resizeTimeout) {
            clearTimeout(resizeTimeout);
        }
        resizeTimeout = setTimeout(() => {
            resize();
        }, 150);
    }, { passive: true });
    
    // Hero section yüksekliği değiştiğinde canvas'ı güncelle
    // ResizeObserver callback'lerini throttled ve optimized
    let resizeObserver;
    let observerResizeTimeout;
    let lastObserverResize = 0;
    const OBSERVER_RESIZE_THROTTLE = 200; // Throttle ResizeObserver callbacks
    
    function scheduleResize() {
        const now = performance.now();
        if (now - lastObserverResize < OBSERVER_RESIZE_THROTTLE) {
            if (observerResizeTimeout) {
                clearTimeout(observerResizeTimeout);
            }
            observerResizeTimeout = setTimeout(() => {
                updateCanvasHeight();
                resize();
                lastObserverResize = performance.now();
            }, OBSERVER_RESIZE_THROTTLE - (now - lastObserverResize));
            return;
        }
        
        lastObserverResize = now;
        requestAnimationFrame(() => {
            updateCanvasHeight();
            resize();
        });
    }
    
    if (window.ResizeObserver) {
        resizeObserver = new ResizeObserver(scheduleResize);
        
        const heroSection = document.getElementById('hero-section');
        if (heroSection) {
            resizeObserver.observe(heroSection);
        }
    }
    
    // Cleanup resize timeout
    window.addEventListener('beforeunload', () => {
        if (resizeTimeout) {
            clearTimeout(resizeTimeout);
        }
        if (observerResizeTimeout) {
            clearTimeout(observerResizeTimeout);
        }
    });
    
    // Animation loop with frame limiting and performance optimization
    let animationId;
    let startTime = performance.now();
    let lastFrameTime = 0;
    let frameSkip = 0;
    const TARGET_FPS = 30; // Limit to 30 FPS instead of 60
    const FRAME_INTERVAL = 1000 / TARGET_FPS;
    let isPaused = false;
    
    function render(currentTime) {
        if (isPaused) return;
        
        // Frame limiting - skip frames if too fast
        const deltaTime = currentTime - lastFrameTime;
        if (deltaTime < FRAME_INTERVAL) {
            animationId = requestAnimationFrame(render);
            return;
        }
        lastFrameTime = currentTime - (deltaTime % FRAME_INTERVAL);
        
        // Resize only every few frames to reduce layout work
        frameSkip++;
        if (frameSkip % 10 === 0) {
            resize();
        }
        
        // Smooth mouse movement - reduce frequency
        mouseX += (targetMouseX - mouseX) * 0.03; // Slower interpolation
        mouseY += (targetMouseY - mouseY) * 0.03;
        
        const time = (performance.now() - startTime) * 0.001;
        
        gl.uniform2f(resolutionLocation, canvas.width, canvas.height);
        gl.uniform1f(timeLocation, time);
        gl.uniform2f(mouseLocation, mouseX, mouseY);
        
        gl.enable(gl.BLEND);
        gl.blendFunc(gl.SRC_ALPHA, gl.ONE_MINUS_SRC_ALPHA);
        
        gl.drawArrays(gl.TRIANGLES, 0, 6);
        
        animationId = requestAnimationFrame(render);
    }
    
    // Start animation with initial timestamp
    lastFrameTime = performance.now();
    render(lastFrameTime);
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (animationId) {
            cancelAnimationFrame(animationId);
            animationId = null;
        }
        if (resizeObserver) {
            resizeObserver.disconnect();
        }
    });
    
    // Pause when not visible or scrolled out of view
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            isPaused = true;
            if (animationId) {
                cancelAnimationFrame(animationId);
                animationId = null;
            }
        } else {
            isPaused = false;
            if (!animationId) {
                startTime = performance.now();
                lastFrameTime = performance.now();
                render(lastFrameTime);
            }
        }
    });
    
    // Pause when section is out of view using IntersectionObserver
    const heroSection = document.getElementById('hero-section');
    if (heroSection && window.IntersectionObserver) {
        const sectionObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting && !document.hidden) {
                    isPaused = true;
                    if (animationId) {
                        cancelAnimationFrame(animationId);
                        animationId = null;
                    }
                } else if (entry.isIntersecting && !document.hidden && !animationId) {
                    isPaused = false;
                    startTime = performance.now();
                    lastFrameTime = performance.now();
                    render(lastFrameTime);
                }
            });
        }, {
            rootMargin: '100px' // Pause 100px before leaving viewport
        });
        
        sectionObserver.observe(heroSection);
        
        window.addEventListener('beforeunload', () => {
            sectionObserver.disconnect();
        });
    }
})();


// Hero Scroll Down Button
(function() {
    'use strict';
    
    const scrollLink = document.querySelector('.hero-scroll-link');
    if (!scrollLink) return;
    
    scrollLink.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Layout queries'i requestAnimationFrame içinde batch'le
        requestAnimationFrame(() => {
            // Glowing Features (Özelliklerimiz) section'ı bul - class veya ID ile
            const featuresSection = document.querySelector('section[id*="features-"]') ||
                                   document.querySelector('.glowing-features-section') || 
                                   document.querySelector('section[id*="glowing-features"]') ||
                                   document.querySelector('section[id*="features"]') ||
                                   document.querySelector('[class*="glowing-features"]');
            
            if (featuresSection) {
                // Tüm layout sorgularını aynı frame'de yap
                const header = document.querySelector('header');
                const headerHeight = header ? header.offsetHeight : 0;
                const featuresRect = featuresSection.getBoundingClientRect();
                const targetPosition = featuresRect.top + window.pageYOffset - headerHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            } else {
                // Features section bulunamazsa, bir sonraki section'a scroll yap
                const heroSection = document.getElementById('hero-section');
                if (heroSection) {
                    const nextSection = heroSection.nextElementSibling;
                    if (nextSection) {
                        // Tüm layout sorgularını aynı frame'de yap
                        const header = document.querySelector('header');
                        const headerHeight = header ? header.offsetHeight : 0;
                        const nextRect = nextSection.getBoundingClientRect();
                        const targetPosition = nextRect.top + window.pageYOffset - headerHeight;
                        
                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });
                    }
                }
            }
        });
    });
})();
</script>

