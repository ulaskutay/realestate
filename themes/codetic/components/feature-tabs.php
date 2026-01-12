<?php
/**
 * Codetic Theme - Feature Tabs Component
 * Modern teknolojik tab component - temaya uygun
 */

$section = $section ?? [];
$settings = $section['settings'] ?? [];

// Section ayarları
$badge = $settings['badge'] ?? 'codetic.co';
$heading = $section['title'] ?? 'Yapay Zeka Destekli, Ölçeklenebilir Web Altyapısı';
$description = $section['subtitle'] ?? 'Codetic altyapısı; yapay zeka destekli optimizasyon, yüksek performanslı kod yapısı ve esnek mimarisiyle uzun vadeli dijital çözümler sunar.';

// Varsayılan tabs verileri
$defaultTabs = [
    [
        'value' => 'tab-1',
        'icon' => 'zap',
        'label' => 'Yapay Zeka Destekli',
        'content' => [
            'badge' => 'Modern Web Tasarım Altyapısı',
            'title' => 'Tema ve Modül Ekleme-Geliştirme Özelliği',
            'description' => 'SEO ve performans süreçleri akıllı sistemlerle optimize edilir.',
            'buttonText' => 'Planları Gör',
            'buttonLink' => '#',
            'imageSrc' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800&h=600&fit=crop&q=80',
            'imageAlt' => 'Yapay Zeka Destekli Modül ve Tema Geliştirme'
        ]
    ],
    [
        'value' => 'tab-2',
        'icon' => 'pointer',
        'label' => '100% Responsive',
        'content' => [
            'badge' => 'Mobil Uyumlu Panel ve Web Sitesi',
            'title' => 'Tüm cihazlarda yüksek performanslı şekilde kullanın',
            'description' => 'Tüm cihazlarda kusursuz deneyim: mobil, tablet ve masaüstü.',
            'buttonText' => 'Detayları İncele',
            'buttonLink' => '#',
            'imageSrc' => 'https://images.unsplash.com/photo-1616469829581-73993eb86b02?w=800&h=600&fit=crop&q=80',
            'imageAlt' => 'Responsive Web Altyapıları - Mobil, Tablet ve Masaüstü Uyumluluk'
        ]
    ],
    [
        'value' => 'tab-3',
        'icon' => 'layout',
        'label' => 'Hafif & Geliştirilebilir',
        'content' => [
            'badge' => 'Temiz Mimari',
            'title' => 'Dilediğiniz şekilde geliştirilebilir ve özelleştirilebilir.',
            'description' => 'Modül ve tema yapısı ile her sektöre uygun şekilde geliştirilebilir.',
            'buttonText' => 'Detayları İncele',
            'buttonLink' => '#',
            'imageSrc' => 'https://images.unsplash.com/photo-1618477388954-7852f32655ec?w=800&h=600&fit=crop&q=80',
            'imageAlt' => 'Geliştirilebilir ve Özelleştirilebilir Web Altyapısı'
        ]
    ]
];

$tabs = !empty($section['tabs']) ? $section['tabs'] : $defaultTabs;

// Icon mapping
$iconMap = [
    'zap' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',
    'pointer' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg>',
    'layout' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 13a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6z"/></svg>'
];

$sectionId = 'feature-tabs-' . uniqid();
$defaultTabValue = !empty($tabs[0]['value']) ? $tabs[0]['value'] : 'tab-1';
?>

<section class="relative py-24 md:py-48 bg-[#0a0a0f] overflow-hidden" id="<?php echo esc_attr($sectionId); ?>">
    <!-- Top Mask -->
    <div class="absolute top-0 left-0 right-0 h-32 md:h-48 pointer-events-none z-[5]" style="background: linear-gradient(180deg, rgba(10,10,15,0.6) 0%, rgba(10,10,15,0.35) 50%, rgba(10,10,15,0.15) 80%, transparent 100%);"></div>
    
    <!-- Animated Background -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-[600px] h-[600px] bg-violet-600/10 rounded-full blur-[120px] animate-pulse-slow"></div>
        <div class="absolute bottom-1/4 right-1/4 w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-purple-600/5 rounded-full blur-[150px]"></div>
        
        <!-- Subtle Grid Pattern -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(59,130,246,0.02)_1px,transparent_1px),linear-gradient(90deg,rgba(59,130,246,0.02)_1px,transparent_1px)] bg-[size:64px_64px]" style="mask-image: linear-gradient(180deg, transparent 0%, black 20%, black 80%, transparent 100%); -webkit-mask-image: linear-gradient(180deg, transparent 0%, black 20%, black 80%, transparent 100%);"></div>
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
            
            <?php if (!empty($heading)): ?>
            <h1 class="max-w-4xl text-3xl md:text-4xl lg:text-5xl font-bold text-white leading-tight">
                <span class="bg-gradient-to-r from-white via-blue-100 to-cyan-200 bg-clip-text text-transparent">
                    <?php echo esc_html($heading); ?>
                </span>
            </h1>
            <?php endif; ?>
            
            <?php if (!empty($description)): ?>
            <p class="max-w-2xl text-base md:text-lg text-slate-400 leading-relaxed">
                <?php echo esc_html($description); ?>
            </p>
            <?php endif; ?>
        </div>
        
        <!-- Tabs -->
        <div class="feature-tabs-container mt-8" data-section-id="<?php echo esc_attr($sectionId); ?>" data-default-tab="<?php echo esc_attr($defaultTabValue); ?>">
            <!-- Tabs List - Mobile: horizontal scroll, Desktop: centered -->
            <div class="relative mb-8 md:mb-12">
                <!-- Mobile: Scrollable tabs -->
                <div class="flex md:hidden overflow-x-auto scrollbar-hide gap-2 pb-2 px-1 -mx-1">
                    <?php foreach ($tabs as $index => $tab): 
                        $tabValue = $tab['value'] ?? '';
                        $tabIcon = $tab['icon'] ?? '';
                        $tabLabel = $tab['label'] ?? '';
                        $iconSvg = isset($iconMap[$tabIcon]) ? $iconMap[$tabIcon] : '';
                        $isFirstTab = ($index === 0);
                    ?>
                    <button 
                        type="button"
                        class="feature-tab-trigger flex-shrink-0 flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-300 whitespace-nowrap <?php echo $isFirstTab ? 'active' : ''; ?>"
                        data-tab-value="<?php echo esc_attr($tabValue); ?>"
                        aria-selected="<?php echo $isFirstTab ? 'true' : 'false'; ?>"
                        role="tab"
                        aria-controls="tab-content-<?php echo esc_attr($tabValue); ?>"
                        id="tab-trigger-mobile-<?php echo esc_attr($tabValue); ?>"
                    >
                        <?php if ($iconSvg): ?>
                            <span class="tab-icon"><?php echo $iconSvg; ?></span>
                        <?php endif; ?>
                        <span><?php echo esc_html($tabLabel); ?></span>
                    </button>
                    <?php endforeach; ?>
                </div>
                
                <!-- Desktop: Centered tabs -->
                <div class="hidden md:flex items-center justify-center gap-4 lg:gap-6">
                    <?php foreach ($tabs as $index => $tab): 
                        $tabValue = $tab['value'] ?? '';
                        $tabIcon = $tab['icon'] ?? '';
                        $tabLabel = $tab['label'] ?? '';
                        $iconSvg = isset($iconMap[$tabIcon]) ? $iconMap[$tabIcon] : '';
                        $isFirstTab = ($index === 0);
                    ?>
                    <button 
                        type="button"
                        class="feature-tab-trigger flex items-center gap-2.5 rounded-xl px-5 py-3 text-base font-semibold transition-all duration-300 <?php echo $isFirstTab ? 'active' : ''; ?>"
                        data-tab-value="<?php echo esc_attr($tabValue); ?>"
                        aria-selected="<?php echo $isFirstTab ? 'true' : 'false'; ?>"
                        role="tab"
                        aria-controls="tab-content-<?php echo esc_attr($tabValue); ?>"
                        id="tab-trigger-<?php echo esc_attr($tabValue); ?>"
                    >
                        <?php if ($iconSvg): ?>
                            <span class="tab-icon"><?php echo $iconSvg; ?></span>
                        <?php endif; ?>
                        <span><?php echo esc_html($tabLabel); ?></span>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Tabs Content -->
            <div class="mx-auto max-w-7xl rounded-xl md:rounded-2xl bg-gradient-to-br from-slate-900/90 to-slate-800/90 backdrop-blur-xl p-4 sm:p-6 lg:p-12 border border-slate-700/50 shadow-2xl">
                <?php foreach ($tabs as $index => $tab): 
                    $tabValue = $tab['value'] ?? '';
                    $content = $tab['content'] ?? [];
                    $contentBadge = $content['badge'] ?? '';
                    $contentTitle = $content['title'] ?? '';
                    $contentDescription = $content['description'] ?? '';
                    $buttonText = $content['buttonText'] ?? '';
                    $buttonLink = $content['buttonLink'] ?? '#';
                    $imageSrc = $content['imageSrc'] ?? '';
                    $imageAlt = $content['imageAlt'] ?? $contentTitle;
                    $isActive = ($index === 0);
                ?>
                <div 
                    class="feature-tab-content flex flex-col lg:grid lg:grid-cols-2 gap-6 sm:gap-8 lg:gap-16 items-center <?php echo $isActive ? 'active' : ''; ?>"
                    id="tab-content-<?php echo esc_attr($tabValue); ?>"
                    role="tabpanel"
                    aria-labelledby="tab-trigger-<?php echo esc_attr($tabValue); ?>"
                    <?php if (!$isActive): ?>hidden<?php endif; ?>
                >
                    <!-- Content - Text (Mobile: second, Desktop: first) -->
                    <div class="flex flex-col gap-4 sm:gap-5 order-2 lg:order-1">
                        <?php if (!empty($contentBadge)): ?>
                        <div class="inline-flex items-center rounded-full border px-2.5 py-1 sm:px-3 sm:py-1.5 text-[11px] sm:text-xs font-semibold border-white/10 bg-white/5 w-fit">
                            <span class="text-slate-300"><?php echo esc_html($contentBadge); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($contentTitle)): ?>
                        <h3 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold text-white leading-tight">
                            <span class="bg-gradient-to-r from-white via-blue-100 to-cyan-200 bg-clip-text text-transparent">
                                <?php echo esc_html($contentTitle); ?>
                            </span>
                        </h3>
                        <?php endif; ?>
                        
                        <?php if (!empty($contentDescription)): ?>
                        <p class="text-sm sm:text-base md:text-lg text-slate-400 leading-relaxed">
                            <?php echo esc_html($contentDescription); ?>
                        </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($buttonText)): ?>
                        <a 
                            href="<?php echo esc_url($buttonLink); ?>"
                            class="mt-1 sm:mt-2 w-fit inline-flex items-center justify-center rounded-lg sm:rounded-xl px-4 sm:px-6 py-2.5 sm:py-3 text-xs sm:text-sm font-semibold bg-gradient-to-r from-blue-500 to-purple-600 text-white hover:opacity-90 transition-opacity shadow-lg shadow-blue-500/30"
                        >
                            <?php echo esc_html($buttonText); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Content - Image (Mobile: first, Desktop: second) -->
                    <div class="order-1 lg:order-2 w-full">
                        <div class="relative w-full h-[200px] sm:h-[280px] md:h-[350px] lg:h-[400px] rounded-xl md:rounded-2xl overflow-hidden shadow-xl md:shadow-2xl group">
                            <?php if (!empty($imageSrc)): ?>
                            <!-- Image -->
                            <img 
                                src="<?php echo esc_url($imageSrc); ?>" 
                                alt="<?php echo esc_attr($imageAlt); ?>"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                loading="lazy"
                            />
                            <!-- Overlay Gradient -->
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent pointer-events-none"></div>
                            <!-- Border Glow -->
                            <div class="absolute inset-0 rounded-xl md:rounded-2xl border border-slate-700/50 pointer-events-none"></div>
                            <?php else: ?>
                            <!-- Placeholder when no image -->
                            <div class="w-full h-full bg-gradient-to-br from-slate-800/80 to-slate-900/80 flex items-center justify-center">
                                <div class="text-center">
                                    <svg class="w-10 h-10 sm:w-16 sm:h-16 mx-auto text-slate-600 mb-2 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="text-slate-500 text-xs sm:text-sm">Görsel ekleyin</p>
                                </div>
                            </div>
                            <div class="absolute inset-0 rounded-xl md:rounded-2xl border border-slate-700/50 pointer-events-none"></div>
                            <?php endif; ?>
                            <!-- Glowing Accent -->
                            <div class="absolute bottom-0 left-0 right-0 h-16 sm:h-24 bg-gradient-to-t from-blue-600/20 via-transparent to-transparent pointer-events-none"></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Decorative bottom gradient -->
    <div class="absolute w-full h-px bottom-0 left-0 z-0" style="background: radial-gradient(50% 50% at 50% 50%, rgba(59,130,246,0.3) 0%, rgba(59,130,246,0) 100%);"></div>
</section>

<style>
/* Hide scrollbar for mobile tabs */
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

/* Feature Tabs Styles */
#<?php echo esc_attr($sectionId); ?> .feature-tab-trigger {
    position: relative;
    background-color: rgba(255, 255, 255, 0.05);
    color: #94a3b8;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

#<?php echo esc_attr($sectionId); ?> .feature-tab-trigger:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: #ffffff;
    border-color: rgba(59, 130, 246, 0.3);
}

#<?php echo esc_attr($sectionId); ?> .feature-tab-trigger[aria-selected="true"],
#<?php echo esc_attr($sectionId); ?> .feature-tab-trigger.active {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(139, 92, 246, 0.2));
    color: #60a5fa;
    border-color: rgba(59, 130, 246, 0.5);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

#<?php echo esc_attr($sectionId); ?> .feature-tab-trigger.active .tab-icon {
    color: #60a5fa;
}

#<?php echo esc_attr($sectionId); ?> .feature-tab-content {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.4s ease, transform 0.4s ease;
}

#<?php echo esc_attr($sectionId); ?> .feature-tab-content[hidden] {
    display: none !important;
}

#<?php echo esc_attr($sectionId); ?> .feature-tab-content:not([hidden]) {
    display: flex;
}

@media (min-width: 1024px) {
    #<?php echo esc_attr($sectionId); ?> .feature-tab-content:not([hidden]) {
        display: grid;
    }
}

#<?php echo esc_attr($sectionId); ?> .feature-tab-content.active:not([hidden]) {
    opacity: 1;
    transform: translateY(0);
}

/* Custom Scrollbar */
#<?php echo esc_attr($sectionId); ?> .custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

#<?php echo esc_attr($sectionId); ?> .custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(15, 23, 42, 0.5);
    border-radius: 3px;
}

#<?php echo esc_attr($sectionId); ?> .custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(59, 130, 246, 0.5);
    border-radius: 3px;
}

#<?php echo esc_attr($sectionId); ?> .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(59, 130, 246, 0.7);
}

/* Pulse Animation */
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
}

/* Responsive adjustments */
@media (max-width: 1023px) {
    #<?php echo esc_attr($sectionId); ?> .feature-tab-content {
        gap: 2rem;
    }
    
    #<?php echo esc_attr($sectionId); ?> .feature-tab-content h3 {
        font-size: 1.875rem;
    }
}
</style>

<script>
(function() {
    'use strict';
    
    const sectionId = '<?php echo esc_js($sectionId); ?>';
    const container = document.querySelector(`#${sectionId} .feature-tabs-container`);
    if (!container) return;
    
    const defaultTab = container.dataset.defaultTab || '';
    const triggers = container.querySelectorAll('.feature-tab-trigger');
    const contents = container.querySelectorAll('.feature-tab-content');
    
    if (triggers.length === 0 || contents.length === 0) return;
    
    // İlk tab'ı aktif et
    let currentTab = defaultTab;
    
    // İlk tab'ın content'ini ve trigger'larını manuel olarak aktif et
    const firstContent = contents[0];
    
    if (firstContent) {
        // İlk tab value'yu al
        const firstTabValue = triggers[0]?.dataset.tabValue || defaultTab;
        currentTab = firstTabValue;
        
        // Tüm aynı tabValue'ya sahip trigger'ları aktif et (mobil ve masaüstü)
        const firstTriggers = container.querySelectorAll(`[data-tab-value="${firstTabValue}"]`);
        firstTriggers.forEach(trigger => {
            trigger.setAttribute('aria-selected', 'true');
            trigger.classList.add('active');
        });
        
        // İlk content'i göster
        firstContent.removeAttribute('hidden');
        requestAnimationFrame(() => {
            firstContent.classList.add('active');
        });
    }
    
    function activateTab(tabValue) {
        // Tüm trigger'ları deaktif et (hem mobil hem masaüstü)
        triggers.forEach(trigger => {
            trigger.setAttribute('aria-selected', 'false');
            trigger.classList.remove('active');
        });
        
        // Tüm content'leri gizle
        contents.forEach(content => {
            content.classList.remove('active');
            content.setAttribute('hidden', '');
        });
        
        // Seçilen trigger'ları aktif et (hem mobil hem masaüstü - aynı tabValue'ya sahip olanlar)
        const activeTriggers = container.querySelectorAll(`[data-tab-value="${tabValue}"]`);
        activeTriggers.forEach(trigger => {
            trigger.setAttribute('aria-selected', 'true');
            trigger.classList.add('active');
        });
        
        // Seçilen content'i göster - Batch DOM updates
        const activeContent = container.querySelector(`#tab-content-${tabValue}`);
        if (activeContent) {
            // Batch DOM updates in single frame
            requestAnimationFrame(() => {
                activeContent.removeAttribute('hidden');
                // Use double RAF for smooth transition
                requestAnimationFrame(() => {
                    activeContent.classList.add('active');
                });
            });
        }
        
        currentTab = tabValue;
    }
    
    // Tab trigger'lara click event'i ekle
    triggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const tabValue = this.dataset.tabValue;
            if (tabValue && tabValue !== currentTab) {
                activateTab(tabValue);
            }
        });
    });
    
    // Keyboard navigation
    triggers.forEach(trigger => {
        trigger.addEventListener('keydown', function(e) {
            const currentIndex = Array.from(triggers).indexOf(this);
            let targetIndex = currentIndex;
            
            if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                e.preventDefault();
                targetIndex = (currentIndex + 1) % triggers.length;
            } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                e.preventDefault();
                targetIndex = (currentIndex - 1 + triggers.length) % triggers.length;
            } else if (e.key === 'Home') {
                e.preventDefault();
                targetIndex = 0;
            } else if (e.key === 'End') {
                e.preventDefault();
                targetIndex = triggers.length - 1;
            }
            
            if (targetIndex !== currentIndex) {
                const targetTrigger = triggers[targetIndex];
                if (targetTrigger) {
                    targetTrigger.focus();
                    const tabValue = targetTrigger.dataset.tabValue;
                    if (tabValue) {
                        activateTab(tabValue);
                    }
                }
            }
        });
    });
})();
</script>
