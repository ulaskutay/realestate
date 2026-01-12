<?php
/**
 * Codetic Theme - Pricing/Packages Component
 * Modern paketler ve fiyatlandırma bölümü
 */

$section = $section ?? [];
$settings = $section['settings'] ?? [];

// Section ayarları
$sectionTitle = $section['title'] ?? 'Paketlerimiz';
$sectionSubtitle = $section['subtitle'] ?? 'İhtiyacınıza uygun paketi seçin ve dijital dönüşümünüze başlayın.';
$badge = $settings['badge'] ?? 'Fiyatlandırma';

// Varsayılan paketler
$defaultPackages = [
    [
        'name' => 'Başlangıç',
        'price' => '₺2.500',
        'period' => '/ay',
        'description' => 'Küçük işletmeler ve kişisel projeler için ideal başlangıç paketi.',
        'features' => [
            '5 Sayfa',
            'Temel SEO',
            'E-posta Desteği',
            'SSL Sertifikası',
            'Mobil Uyumlu Tasarım'
        ],
        'button_text' => 'Başla',
        'button_link' => '/contact',
        'popular' => false,
        'gradient' => 'from-slate-500 to-slate-600'
    ],
    [
        'name' => 'Profesyonel',
        'price' => '₺5.000',
        'period' => '/ay',
        'description' => 'Büyüyen işletmeler için gelişmiş özellikler ve destek.',
        'features' => [
            '15 Sayfa',
            'Gelişmiş SEO',
            'Öncelikli Destek',
            'SSL Sertifikası',
            'Mobil Uyumlu Tasarım',
            'Sosyal Medya Entegrasyonu',
            'Analytics Entegrasyonu'
        ],
        'button_text' => 'Başla',
        'button_link' => '/contact',
        'popular' => true,
        'gradient' => 'from-blue-500 to-purple-600'
    ],
    [
        'name' => 'Kurumsal',
        'price' => '₺10.000',
        'period' => '/ay',
        'description' => 'Büyük işletmeler için özel çözümler ve özel destek.',
        'features' => [
            'Sınırsız Sayfa',
            'Premium SEO',
            '7/24 Öncelikli Destek',
            'SSL Sertifikası',
            'Mobil Uyumlu Tasarım',
            'Sosyal Medya Entegrasyonu',
            'Analytics Entegrasyonu',
            'Özel Tasarım',
            'API Entegrasyonları'
        ],
        'button_text' => 'Başla',
        'button_link' => '/contact',
        'popular' => false,
        'gradient' => 'from-violet-500 to-purple-600'
    ],
    [
        'name' => 'Özel Çözüm',
        'price' => 'Özel Fiyat',
        'period' => '',
        'description' => 'Özel ihtiyaçlarınız için özelleştirilmiş çözümler.',
        'features' => [
            'Tam Özelleştirme',
            'Özel Geliştirme',
            'Dedike Destek',
            'Tüm Özellikler',
            'Özel Entegrasyonlar',
            'Danışmanlık Hizmeti',
            'Öncelikli Güncellemeler'
        ],
        'button_text' => 'İletişime Geç',
        'button_link' => '/contact',
        'popular' => false,
        'gradient' => 'from-amber-500 to-orange-600'
    ]
];

$packages = !empty($section['packages']) ? $section['packages'] : $defaultPackages;

$sectionId = 'pricing-' . uniqid();
?>

<section class="relative py-24 md:py-32 lg:py-40 bg-[#0a0a0f] overflow-hidden" id="<?php echo esc_attr($sectionId); ?>">
    <!-- Top Mask -->
    <div class="absolute top-0 left-0 right-0 h-32 md:h-48 pointer-events-none z-[5]" style="background: linear-gradient(180deg, rgba(10,10,15,0.6) 0%, rgba(10,10,15,0.35) 50%, rgba(10,10,15,0.15) 80%, transparent 100%);"></div>
    
    <!-- Animated Background -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-[600px] h-[600px] bg-violet-600/10 rounded-full blur-[120px] animate-pulse-slow" style="transform: translateZ(0);"></div>
        <div class="absolute bottom-1/4 right-1/4 w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1s; transform: translateZ(0);"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-purple-600/5 rounded-full blur-[150px]" style="transform: translateZ(0);"></div>
        
        <!-- Subtle Grid Pattern -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(59,130,246,0.02)_1px,transparent_1px),linear-gradient(90deg,rgba(59,130,246,0.02)_1px,transparent_1px)] bg-[size:64px_64px]" style="mask-image: linear-gradient(180deg, transparent 0%, black 20%, black 80%, transparent 100%); -webkit-mask-image: linear-gradient(180deg, transparent 0%, black 20%, black 80%, transparent 100%); transform: translateZ(0);"></div>
    </div>
    
    <div class="container mx-auto px-4 md:px-6 relative z-10">
        <!-- Header -->
        <div class="flex flex-col items-center gap-6 text-center mb-12 md:mb-16">
            <?php if (!empty($badge)): ?>
            <div class="inline-flex items-center gap-2 px-3 py-1.5 sm:px-4 sm:py-2 rounded-full bg-blue-500/10 border border-blue-500/20 backdrop-blur-sm">
                <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-gradient-to-r from-blue-400 to-cyan-400 animate-pulse"></span>
                <span class="text-xs sm:text-sm font-medium text-blue-300"><?php echo esc_html($badge); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($sectionTitle)): ?>
            <h2 class="max-w-4xl text-3xl md:text-4xl lg:text-5xl font-bold text-white leading-tight">
                <span class="bg-gradient-to-r from-white via-blue-100 to-cyan-200 bg-clip-text text-transparent">
                    <?php echo esc_html($sectionTitle); ?>
                </span>
            </h2>
            <?php endif; ?>
            
            <?php if (!empty($sectionSubtitle)): ?>
            <p class="max-w-2xl text-base md:text-lg text-slate-400 leading-relaxed">
                <?php echo esc_html($sectionSubtitle); ?>
            </p>
            <?php endif; ?>
        </div>
        
        <!-- Pricing Cards - Mobile: Horizontal scroll, Desktop: Grid -->
        
        <!-- Mobile: Horizontal Scrollable Cards -->
        <div class="md:hidden relative">
            <div class="flex overflow-x-auto gap-4 pb-4 px-1 -mx-1 snap-x snap-mandatory scrollbar-hide" style="scroll-behavior: smooth; -webkit-overflow-scrolling: touch;">
                <?php foreach ($packages as $index => $package): 
                    $packageName = $package['name'] ?? '';
                    $price = $package['price'] ?? '';
                    $period = $package['period'] ?? '';
                    $description = $package['description'] ?? '';
                    $features = $package['features'] ?? [];
                    $buttonText = $package['button_text'] ?? 'Başla';
                    $buttonLink = $package['button_link'] ?? '/contact';
                    $isPopular = !empty($package['popular']);
                    $gradient = $package['gradient'] ?? 'from-blue-500 to-purple-600';
                ?>
                <div class="pricing-card-mobile flex-shrink-0 w-[280px] snap-center relative pt-6">
                    <!-- Popular Badge -->
                    <?php if ($isPopular): ?>
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 z-20">
                        <div class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 text-white text-[10px] font-semibold shadow-lg shadow-blue-500/50">
                            <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span>Popüler</span>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Card -->
                    <div class="min-h-[380px] rounded-xl <?php echo $isPopular ? 'bg-gradient-to-br from-blue-500/20 to-purple-600/20 border-blue-500/50' : 'bg-slate-900/80 border-slate-700/50'; ?> border backdrop-blur-xl overflow-hidden">
                        <div class="p-5 flex flex-col h-full min-h-[380px]">
                            <!-- Header -->
                            <div class="mb-3">
                                <h3 class="text-lg font-bold text-white mb-1">
                                    <?php echo esc_html($packageName); ?>
                                </h3>
                                <p class="text-xs text-slate-400 line-clamp-2 min-h-[32px]">
                                    <?php echo esc_html($description); ?>
                                </p>
                            </div>
                            
                            <!-- Price -->
                            <div class="mb-4 pb-4 border-b border-slate-700/50">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-2xl font-bold text-white">
                                        <?php echo esc_html($price); ?>
                                    </span>
                                    <?php if (!empty($period)): ?>
                                    <span class="text-sm text-slate-400">
                                        <?php echo esc_html($period); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Features - Show first 4 -->
                            <ul class="flex-1 space-y-2 mb-4 min-h-[120px]">
                                <?php 
                                $displayFeatures = array_slice($features, 0, 4);
                                $remainingCount = count($features) - 4;
                                foreach ($displayFeatures as $feature): 
                                ?>
                                <li class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-green-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span class="text-xs text-slate-300">
                                        <?php echo esc_html($feature); ?>
                                    </span>
                                </li>
                                <?php endforeach; ?>
                                <?php if ($remainingCount > 0): ?>
                                <li class="text-xs text-slate-500 pl-6">
                                    +<?php echo $remainingCount; ?> daha fazla özellik
                                </li>
                                <?php endif; ?>
                            </ul>
                            
                            <!-- CTA Button -->
                            <a 
                                href="<?php echo esc_url($buttonLink); ?>"
                                class="pricing-button-mobile w-full inline-flex items-center justify-center gap-1.5 rounded-lg px-4 py-2.5 text-xs font-semibold transition-all duration-200 mt-auto <?php echo $isPopular ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg shadow-blue-500/30' : 'bg-slate-800/50 text-white border border-slate-700/50'; ?>"
                            >
                                <span><?php echo esc_html($buttonText); ?></span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Scroll Indicator -->
            <div class="flex justify-center gap-1.5 mt-4">
                <?php foreach ($packages as $index => $package): ?>
                <div class="w-1.5 h-1.5 rounded-full bg-slate-600 pricing-dot" data-index="<?php echo $index; ?>"></div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Desktop: Grid Layout -->
        <div class="hidden md:grid md:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8 max-w-7xl mx-auto">
            <?php foreach ($packages as $index => $package): 
                $packageName = $package['name'] ?? '';
                $price = $package['price'] ?? '';
                $period = $package['period'] ?? '';
                $description = $package['description'] ?? '';
                $features = $package['features'] ?? [];
                $buttonText = $package['button_text'] ?? 'Başla';
                $buttonLink = $package['button_link'] ?? '/contact';
                $isPopular = !empty($package['popular']);
                $gradient = $package['gradient'] ?? 'from-blue-500 to-purple-600';
                $delay = $index * 0.1;
            ?>
            <div class="pricing-card group relative <?php echo $isPopular ? 'md:-mt-4 md:mb-4' : ''; ?>" 
                 style="animation-delay: <?php echo $delay; ?>s;">
                <!-- Popular Badge -->
                <?php if ($isPopular): ?>
                <div class="absolute -top-4 left-1/2 -translate-x-1/2 z-20">
                    <div class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 text-white text-xs font-semibold shadow-lg shadow-blue-500/50">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span>En Popüler</span>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Card Container -->
                <div class="pricing-card-container relative h-full rounded-2xl overflow-hidden transition-transform duration-300 ease-out hover:scale-[1.02] hover:-translate-y-2">
                    <!-- Gradient Border -->
                    <?php if ($isPopular): ?>
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-br <?php echo esc_attr($gradient); ?> p-[2px] opacity-100"></div>
                    <?php else: ?>
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-br <?php echo esc_attr($gradient); ?> p-[2px] opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <?php endif; ?>
                    
                    <!-- Card Background -->
                    <div class="pricing-card-bg relative h-full rounded-[15px] bg-gradient-to-br from-slate-900/90 to-slate-800/90 backdrop-blur-xl border border-slate-700/50 overflow-hidden transition-all duration-300 group-hover:border-blue-500/50 group-hover:shadow-2xl group-hover:shadow-blue-500/20">
                        <!-- Gradient Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-br <?php echo esc_attr($gradient); ?> opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                        
                        <!-- Content -->
                        <div class="relative p-6 md:p-8 flex flex-col h-full">
                            <!-- Package Name -->
                            <div class="mb-4">
                                <h3 class="text-2xl font-bold text-white mb-2 group-hover:bg-gradient-to-r group-hover:from-blue-200 group-hover:via-purple-200 group-hover:to-cyan-200 group-hover:bg-clip-text group-hover:text-transparent transition-all duration-500">
                                    <?php echo esc_html($packageName); ?>
                                </h3>
                                <?php if (!empty($description)): ?>
                                <p class="text-sm text-slate-400 leading-relaxed">
                                    <?php echo esc_html($description); ?>
                                </p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Price -->
                            <div class="mb-6 pb-6 border-b border-slate-700/50">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-4xl md:text-5xl font-bold text-white">
                                        <?php echo esc_html($price); ?>
                                    </span>
                                    <?php if (!empty($period)): ?>
                                    <span class="text-lg text-slate-400">
                                        <?php echo esc_html($period); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Features List -->
                            <ul class="flex-1 space-y-3 mb-8">
                                <?php foreach ($features as $feature): ?>
                                <li class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-green-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span class="text-sm text-slate-300 leading-relaxed">
                                        <?php echo esc_html($feature); ?>
                                    </span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            
                            <!-- CTA Button -->
                            <a 
                                href="<?php echo esc_url($buttonLink); ?>"
                                class="pricing-button w-full inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3.5 text-sm font-semibold transition-all duration-200 <?php echo $isPopular ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white hover:opacity-90 shadow-lg shadow-blue-500/30' : 'bg-slate-800/50 text-white border border-slate-700/50 hover:bg-gradient-to-r hover:from-blue-500 hover:to-purple-600 hover:border-transparent hover:shadow-lg hover:shadow-blue-500/30'; ?>"
                            >
                                <span><?php echo esc_html($buttonText); ?></span>
                                <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                            </a>
                        </div>
                        
                        <!-- Decorative Glow -->
                        <div class="pricing-glow pricing-glow-top absolute top-0 right-0 w-32 h-32 bg-gradient-to-br <?php echo esc_attr($gradient); ?> opacity-0 group-hover:opacity-20 blur-3xl transition-opacity duration-300 pointer-events-none"></div>
                        <div class="pricing-glow pricing-glow-bottom absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr <?php echo esc_attr($gradient); ?> opacity-0 group-hover:opacity-15 blur-2xl transition-opacity duration-300 pointer-events-none"></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Bottom Gradient -->
    <div class="absolute w-full h-px bottom-0 left-0 z-0" style="background: radial-gradient(50% 50% at 50% 50%, rgba(59,130,246,0.3) 0%, rgba(59,130,246,0) 100%);"></div>
</section>

<style>
/* Hide scrollbar for mobile */
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
    scroll-behavior: smooth;
    /* Removed will-change: scroll-position - only needed during active scrolling */
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

/* Pricing Section Styles */
#<?php echo esc_attr($sectionId); ?> .pricing-card {
    animation: fadeInUp 0.6s ease-out backwards;
    /* Removed will-change - only needed during animation */
}

/* Reset will-change after animation completes */
#<?php echo esc_attr($sectionId); ?> .pricing-card {
    animation-fill-mode: both;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Pulse Animation - Optimized */
@keyframes pulse-slow {
    0%, 100% {
        opacity: 0.4;
    }
    50% {
        opacity: 0.8;
    }
}

.animate-pulse-slow {
    animation: pulse-slow 4s ease-in-out infinite;
    /* Removed will-change - browser handles animation optimization */
}

/* Mobile scroll dots */
#<?php echo esc_attr($sectionId); ?> .pricing-dot.active {
    background-color: #3b82f6;
    width: 1.5rem;
    border-radius: 9999px;
    transition: all 0.3s ease;
}

/* Line clamp for mobile */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Mobile card equal heights */
#<?php echo esc_attr($sectionId); ?> .pricing-card-mobile {
    display: flex;
    flex-direction: column;
    transform: translateZ(0);
    /* Removed will-change - only set on scroll/active states */
}

#<?php echo esc_attr($sectionId); ?> .pricing-card-mobile > div {
    flex: 1;
    display: flex;
    flex-direction: column;
    transform: translateZ(0);
}

/* Optimize will-change - only set on hover/interaction */
#<?php echo esc_attr($sectionId); ?> .pricing-card-container:hover {
    will-change: transform;
}

#<?php echo esc_attr($sectionId); ?> .pricing-card-bg:hover {
    will-change: border-color, box-shadow;
}

#<?php echo esc_attr($sectionId); ?> .pricing-button:hover,
#<?php echo esc_attr($sectionId); ?> .pricing-button-mobile:hover {
    will-change: background, border-color, box-shadow, transform;
}

#<?php echo esc_attr($sectionId); ?> .pricing-glow:hover {
    will-change: opacity;
}

/* Reset will-change when not hovering */
#<?php echo esc_attr($sectionId); ?> .pricing-card-container,
#<?php echo esc_attr($sectionId); ?> .pricing-card-bg,
#<?php echo esc_attr($sectionId); ?> .pricing-button,
#<?php echo esc_attr($sectionId); ?> .pricing-button-mobile,
#<?php echo esc_attr($sectionId); ?> .pricing-glow {
    will-change: auto;
}

/* Responsive adjustments */
@media (max-width: 767px) {
    #<?php echo esc_attr($sectionId); ?> .pricing-card {
        animation: none;
    }
    
    #<?php echo esc_attr($sectionId); ?> .animate-pulse-slow {
        animation: none !important;
    }
    
    /* Optimize mobile scrolling */
    #<?php echo esc_attr($sectionId); ?> .scrollbar-hide {
        transform: translateZ(0);
        -webkit-transform: translateZ(0);
    }
}

@media (min-width: 768px) and (max-width: 1023px) {
    #<?php echo esc_attr($sectionId); ?> .pricing-card {
        max-width: 100%;
    }
}
</style>

<script>
(function() {
    'use strict';
    
    const sectionId = '<?php echo esc_js($sectionId); ?>';
    const section = document.getElementById(sectionId);
    if (!section) return;
    
    // Mobile scroll indicator
    const scrollContainer = section.querySelector('.scrollbar-hide');
    const dots = section.querySelectorAll('.pricing-dot');
    
    if (scrollContainer && dots.length > 0) {
        // Set first dot as active
        dots[0].classList.add('active');
        
        // Throttle function for better performance
        let ticking = false;
        const cardWidth = 280 + 16; // card width + gap
        
        function updateDots() {
            const scrollLeft = scrollContainer.scrollLeft;
            const activeIndex = Math.round(scrollLeft / cardWidth);
            
            dots.forEach((dot, index) => {
                if (index === activeIndex) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
            
            ticking = false;
        }
        
        // Update dots on scroll with throttling
        scrollContainer.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(updateDots);
                ticking = true;
            }
        }, { passive: true });
        
        // Click on dots to scroll
        dots.forEach((dot, index) => {
            dot.addEventListener('click', function() {
                scrollContainer.scrollTo({
                    left: index * cardWidth,
                    behavior: 'smooth'
                });
            });
            dot.style.cursor = 'pointer';
        });
    }
})();
</script>
