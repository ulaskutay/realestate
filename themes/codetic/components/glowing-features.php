<?php
/**
 * Codetic Theme - Features Component
 * Temiz ve profesyonel özellikler bölümü - Tema bütünlüğüne uygun
 */

$section = $section ?? [];
$settings = $section['settings'] ?? [];

// Section ayarları
$sectionTitle = $section['title'] ?? 'Özelliklerimiz';
$sectionSubtitle = $section['subtitle'] ?? 'Yenilikçi çözümlerimizle işletmenizi dijital dünyada bir adım öne taşıyın.';
$badge = $settings['badge'] ?? 'Neden Biz?';

// Grid items - varsayılan değerler
$defaultItems = [
    [
        'icon' => 'rocket',
        'title' => 'Hızlı Geliştirme',
        'description' => 'Modern araçlar ve metodolojilerle projelerinizi hızla hayata geçiriyoruz. Agile yaklaşımımızla sürekli değer üretiyoruz.',
        'gradient' => 'from-indigo-500 via-purple-500 to-pink-500'
    ],
    [
        'icon' => 'shield',
        'title' => 'Güvenli Altyapı',
        'description' => 'En güncel güvenlik standartları ve best practice\'ler ile verilerinizi koruyoruz. SSL, şifreleme ve düzenli güvenlik taramaları.',
        'gradient' => 'from-emerald-400 via-teal-500 to-cyan-500'
    ],
    [
        'icon' => 'code',
        'title' => 'Temiz Kod',
        'description' => 'Okunabilir, sürdürülebilir ve ölçeklenebilir kod yazıyoruz. SOLID prensipleri ve modern mimari desenler kullanıyoruz.',
        'gradient' => 'from-blue-400 via-cyan-500 to-blue-600'
    ],
    [
        'icon' => 'zap',
        'title' => 'Yüksek Performans',
        'description' => 'Optimize edilmiş kod, CDN entegrasyonu ve caching stratejileri ile maksimum hız sağlıyoruz.',
        'gradient' => 'from-amber-400 via-orange-500 to-red-500'
    ],
    [
        'icon' => 'users',
        'title' => '7/24 Destek',
        'description' => 'Uzman ekibimiz her zaman yanınızda. Teknik destek, danışmanlık ve eğitim hizmetleri sunuyoruz.',
        'gradient' => 'from-pink-400 via-rose-500 to-fuchsia-600'
    ]
];

$items = !empty($section['items']) ? $section['items'] : $defaultItems;

// Icon mapping - Daha fazla icon
$iconMap = [
    'rocket' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/></svg>',
    'shield' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>',
    'code' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"/></svg>',
    'zap' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>',
    'users' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>',
    'box' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
    'settings' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
    'lock' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>',
    'sparkles' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/></svg>',
    'search' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>',
    'chart' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>',
    'globe' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/></svg>'
];

$sectionId = 'features-' . uniqid();
?>

<section class="features-section relative w-full py-24 md:py-32 lg:py-40 overflow-hidden" id="<?php echo esc_attr($sectionId); ?>">
    <!-- Top Gradient Blend -->
    <div class="absolute top-0 left-0 right-0 h-32 md:h-48 pointer-events-none z-[5]" style="background: linear-gradient(180deg, rgba(10,10,15,0.6) 0%, rgba(10,10,15,0.35) 50%, rgba(10,10,15,0.15) 80%, transparent 100%);"></div>
    
    <!-- Background -->
    <div class="absolute inset-0 bg-[#0a0a0f]">
        <!-- Subtle Gradient Orbs -->
        <div class="absolute top-1/4 left-1/4 w-[600px] h-[600px] bg-violet-600/8 rounded-full blur-[120px] animate-pulse-slow"></div>
        <div class="absolute bottom-1/4 right-1/4 w-[500px] h-[500px] bg-blue-600/8 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1s;"></div>
        
        <!-- Subtle Grid Pattern -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(59,130,246,0.02)_1px,transparent_1px),linear-gradient(90deg,rgba(59,130,246,0.02)_1px,transparent_1px)] bg-[size:64px_64px]" style="mask-image: linear-gradient(180deg, transparent 0%, black 20%, black 80%, transparent 100%); -webkit-mask-image: linear-gradient(180deg, transparent 0%, black 20%, black 80%, transparent 100%);"></div>
    </div>

    <div class="container mx-auto px-4 md:px-6 relative z-10">
        <!-- Header -->
        <div class="text-center mb-12 md:mb-16">
            <!-- Badge -->
            <?php if (!empty($badge)): ?>
            <div class="inline-flex items-center gap-2 px-3 py-1.5 sm:px-4 sm:py-2 rounded-full bg-blue-500/10 border border-blue-500/20 backdrop-blur-sm mb-6">
                <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-gradient-to-r from-blue-400 to-cyan-400 animate-pulse"></span>
                <span class="text-xs sm:text-sm font-medium text-blue-300"><?php echo esc_html($badge); ?></span>
            </div>
            <?php endif; ?>
            
            <!-- Title -->
            <?php if (!empty($sectionTitle)): ?>
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6 tracking-tight">
                <span class="bg-gradient-to-r from-white via-blue-100 to-cyan-200 bg-clip-text text-transparent">
                    <?php echo esc_html($sectionTitle); ?>
                </span>
            </h2>
            <?php endif; ?>
            
            <!-- Subtitle -->
            <?php if (!empty($sectionSubtitle)): ?>
            <p class="text-base md:text-lg text-slate-400 max-w-2xl mx-auto leading-relaxed">
                <?php echo esc_html($sectionSubtitle); ?>
            </p>
            <?php endif; ?>
        </div>

        <!-- Features Grid - Clean and Simple -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
            <?php 
            $defaultGradients = [
                'from-indigo-500 via-purple-500 to-pink-500',
                'from-emerald-400 via-teal-500 to-cyan-500',
                'from-blue-400 via-cyan-500 to-blue-600',
                'from-amber-400 via-orange-500 to-red-500',
                'from-pink-400 via-rose-500 to-fuchsia-600'
            ];
            
            foreach ($items as $index => $item): 
                $icon = $item['icon'] ?? 'rocket';
                $iconSvg = $iconMap[$icon] ?? $iconMap['rocket'];
                $itemTitle = $item['title'] ?? '';
                $description = $item['description'] ?? '';
                $gradient = $item['gradient'] ?? ($defaultGradients[$index] ?? $defaultGradients[0]);
            ?>
            <div class="feature-card group relative rounded-2xl bg-gradient-to-br from-slate-900/95 to-slate-800/95 backdrop-blur-xl p-6 md:p-8 border border-slate-700/40 transition-all duration-500 hover:border-slate-600/60 hover:shadow-2xl hover:-translate-y-2">
                <!-- Animated gradient glow on hover -->
                <div class="absolute -inset-0.5 rounded-2xl bg-gradient-to-br <?php echo esc_attr($gradient); ?> opacity-0 group-hover:opacity-30 transition-opacity duration-500 blur-xl -z-10"></div>
                <!-- Gradient border on hover -->
                <div class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none">
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-br <?php echo esc_attr($gradient); ?> p-[1px]">
                        <div class="w-full h-full rounded-2xl bg-slate-900/95"></div>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="relative z-10">
                    <!-- Icon with enhanced design -->
                    <div class="mb-6">
                        <div class="relative">
                            <!-- Glowing background effect -->
                            <div class="absolute inset-0 rounded-2xl bg-gradient-to-br <?php echo esc_attr($gradient); ?> opacity-30 blur-2xl group-hover:opacity-50 transition-opacity duration-500"></div>
                            <?php 
                                    // Gradient color mapping - Updated with vibrant colors
                                    $gradientColors = [
                                        'from-indigo-500 via-purple-500 to-pink-500' => ['#6366f1', '#ec4899', 'rgba(99, 102, 241, 0.4)'],
                                        'from-emerald-400 via-teal-500 to-cyan-500' => ['#34d399', '#06b6d4', 'rgba(52, 211, 153, 0.4)'],
                                        'from-blue-400 via-cyan-500 to-blue-600' => ['#60a5fa', '#2563eb', 'rgba(96, 165, 250, 0.4)'],
                                        'from-amber-400 via-orange-500 to-red-500' => ['#fbbf24', '#ef4444', 'rgba(251, 191, 36, 0.4)'],
                                        'from-pink-400 via-rose-500 to-fuchsia-600' => ['#f472b6', '#c026d3', 'rgba(244, 114, 182, 0.4)'],
                                        // Fallback for old gradients
                                        'from-violet-500 to-purple-600' => ['#8b5cf6', '#9333ea', 'rgba(139, 92, 246, 0.4)'],
                                        'from-emerald-500 to-teal-600' => ['#10b981', '#0d9488', 'rgba(16, 185, 129, 0.4)'],
                                        'from-blue-500 to-cyan-600' => ['#3b82f6', '#0891b2', 'rgba(59, 130, 246, 0.4)'],
                                        'from-amber-500 to-orange-600' => ['#f59e0b', '#ea580c', 'rgba(245, 158, 11, 0.4)'],
                                        'from-pink-500 to-rose-600' => ['#ec4899', '#e11d48', 'rgba(236, 72, 153, 0.4)']
                                    ];
                                    $colors = $gradientColors[$gradient] ?? ['#3b82f6', '#0891b2', 'rgba(59, 130, 246, 0.4)'];
                                    $shadowColor = $colors[2] ?? 'rgba(59, 130, 246, 0.4)';
                            ?>
                            <!-- Icon container with gradient border -->
                            <div class="relative w-16 h-16 rounded-2xl bg-gradient-to-br <?php echo esc_attr($gradient); ?> p-[3px] shadow-lg group-hover:shadow-2xl transition-all duration-500 group-hover:scale-110" style="box-shadow: 0 10px 30px -5px <?php echo esc_attr($shadowColor); ?>, 0 0 20px <?php echo esc_attr($shadowColor); ?>;">
                                <div class="w-full h-full rounded-[13px] bg-slate-900/95 backdrop-blur-sm flex items-center justify-center">
                                    <?php
                                    $gradientId = 'icon-gradient-' . $sectionId . '-' . $index;
                                    // Wrap icon SVG with gradient definition
                                    $iconSvgWithGradient = '<svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <defs>
                                            <linearGradient id="' . $gradientId . '" x1="0%" y1="0%" x2="100%" y2="100%">
                                                <stop offset="0%" style="stop-color:' . $colors[0] . ';stop-opacity:1" />
                                                <stop offset="100%" style="stop-color:' . $colors[1] . ';stop-opacity:1" />
                                            </linearGradient>
                                        </defs>' . 
                                        str_replace(['stroke="currentColor"', 'fill="currentColor"', 'stroke=\'currentColor\'', 'fill=\'currentColor\''], 
                                        ['stroke="url(#' . $gradientId . ')"', 'fill="url(#' . $gradientId . ')"', 'stroke="url(#' . $gradientId . ')"', 'fill="url(#' . $gradientId . ')"'], 
                                        $iconSvg) . '</svg>';
                                    echo $iconSvgWithGradient;
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Text Content -->
                    <div class="space-y-3">
                        <h3 class="text-xl md:text-2xl font-bold text-white tracking-tight group-hover:text-transparent group-hover:bg-clip-text group-hover:bg-gradient-to-r <?php echo esc_attr($gradient); ?> transition-all duration-500">
                            <?php echo esc_html($itemTitle); ?>
                        </h3>
                        <p class="text-sm md:text-base text-slate-300 leading-relaxed group-hover:text-slate-200 transition-colors duration-500">
                            <?php echo esc_html($description); ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Bottom Gradient -->
    <div class="absolute bottom-0 left-0 right-0 h-32 pointer-events-none z-[5]" style="background: linear-gradient(0deg, rgba(10,10,15,0.6) 0%, rgba(10,10,15,0.35) 50%, rgba(10,10,15,0.15) 80%, transparent 100%);"></div>
</section>

<style>
/* Features Section Styles */
#<?php echo esc_attr($sectionId); ?> {
    position: relative;
}

/* Feature Card Styles */
#<?php echo esc_attr($sectionId); ?> .feature-card {
    min-height: 280px;
    /* Removed will-change: transform - only use when actively animating */
}

#<?php echo esc_attr($sectionId); ?> .feature-card:hover {
    transform: translateY(-8px);
    will-change: transform; /* Only set will-change on hover */
}

#<?php echo esc_attr($sectionId); ?> .feature-card {
    will-change: auto; /* Reset when not hovering */
}

/* Pulse Animation for Background Orbs */
@keyframes pulse-slow {
    0%, 100% {
        opacity: 0.4;
    }
    50% {
        opacity: 0.6;
    }
}

#<?php echo esc_attr($sectionId); ?> .animate-pulse-slow {
    animation: pulse-slow 8s ease-in-out infinite;
}

/* Enhanced hover effects */
#<?php echo esc_attr($sectionId); ?> .feature-card:hover .tab-icon,
#<?php echo esc_attr($sectionId); ?> .feature-card:hover svg {
    filter: drop-shadow(0 0 8px rgba(59, 130, 246, 0.5));
}

/* Responsive adjustments */
@media (max-width: 767px) {
    #<?php echo esc_attr($sectionId); ?> {
        padding-top: 3rem;
        padding-bottom: 3rem;
    }
    
    #<?php echo esc_attr($sectionId); ?> .feature-card {
        min-height: auto;
    }
    
    #<?php echo esc_attr($sectionId); ?> .animate-pulse-slow {
        animation: none;
    }
    
    #<?php echo esc_attr($sectionId); ?> .feature-card:hover {
        transform: translateY(-4px);
    }
}

@media (min-width: 768px) and (max-width: 1023px) {
    #<?php echo esc_attr($sectionId); ?> .feature-card {
        min-height: 260px;
    }
}
</style>

