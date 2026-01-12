<?php
/**
 * Codetic Theme - Dashboard Showcase Section
 * Yönetim paneli mockup gösterimi
 */

$section = $section ?? [];
$settings = $section['settings'] ?? [];

// Section ayarları
$sectionTitle = $section['title'] ?? 'Güçlü Yönetim Paneli';
$sectionSubtitle = $section['subtitle'] ?? 'Tek bir yerden tüm içeriklerinizi yönetin';
$sectionDescription = $section['description'] ?? 'Modern ve kullanıcı dostu arayüzümüz ile web sitenizi, içeriklerinizi ve müşterilerinizi kolayca yönetin. Gerçek zamanlı istatistikler, kolay içerik düzenleme ve güçlü SEO araçları.';
$badge = $settings['badge'] ?? 'Yönetim Paneli';

// Özellik listesi
$features = $section['features'] ?? [
    'Sürükle-bırak içerik düzenleme',
    'Gerçek zamanlı analitik',
    'SEO optimizasyon araçları',
    'Çoklu dil desteği',
    'Otomatik yedekleme'
];

$sectionId = 'dashboard-showcase-' . uniqid();
?>

<section class="dashboard-showcase-section relative w-full py-12 sm:py-16 md:py-20 lg:py-28 xl:py-36 overflow-hidden" id="<?php echo esc_attr($sectionId); ?>">
    <!-- Top Mask - Features'tan geçiş -->
    <div class="dashboard-top-mask"></div>
    
    <!-- Animated Background with Blue Shaders -->
    <div class="absolute inset-0 bg-[#0a0a0f]">
        <!-- Blue Gradient Orbs -->
        <div class="absolute top-0 left-1/4 w-[800px] h-[800px] bg-blue-600/8 rounded-full blur-[150px] animate-pulse-slow"></div>
        <div class="absolute bottom-1/4 right-0 w-[600px] h-[600px] bg-cyan-500/6 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[1000px] h-[600px] bg-blue-500/5 rounded-full blur-[180px]"></div>
        <div class="absolute bottom-0 left-1/3 w-[500px] h-[500px] bg-indigo-600/6 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 2s;"></div>
        
        <!-- Subtle Grid Pattern -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(59,130,246,0.02)_1px,transparent_1px),linear-gradient(90deg,rgba(59,130,246,0.02)_1px,transparent_1px)] bg-[size:64px_64px]" style="mask-image: linear-gradient(180deg, transparent 0%, black 20%, black 80%, transparent 100%); -webkit-mask-image: linear-gradient(180deg, transparent 0%, black 20%, black 80%, transparent 100%);"></div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 relative z-10">
        <!-- Header -->
        <div class="text-center mb-10 sm:mb-12 md:mb-16 lg:mb-20">
            <!-- Badge -->
            <?php if (!empty($badge)): ?>
            <div class="inline-flex items-center gap-2 px-3 py-1.5 sm:px-4 sm:py-2 rounded-full bg-blue-500/10 border border-blue-500/20 backdrop-blur-sm mb-4 sm:mb-6">
                <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-gradient-to-r from-blue-400 to-cyan-400 animate-pulse"></span>
                <span class="text-xs sm:text-sm font-medium text-blue-300"><?php echo esc_html($badge); ?></span>
            </div>
            <?php endif; ?>
            
            <!-- Title -->
            <?php if (!empty($sectionTitle)): ?>
            <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-bold text-white mb-4 sm:mb-6 tracking-tight px-2">
                <span class="bg-gradient-to-r from-white via-blue-100 to-cyan-200 bg-clip-text text-transparent">
                    <?php echo esc_html($sectionTitle); ?>
                </span>
            </h2>
            <?php endif; ?>
            
            <!-- Subtitle -->
            <?php if (!empty($sectionSubtitle)): ?>
            <p class="text-lg sm:text-xl md:text-2xl text-blue-200/80 max-w-2xl mx-auto mb-3 sm:mb-4 px-2">
                <?php echo esc_html($sectionSubtitle); ?>
            </p>
            <?php endif; ?>
            
            <!-- Description -->
            <?php if (!empty($sectionDescription)): ?>
            <p class="text-sm sm:text-base md:text-lg text-slate-400 max-w-3xl mx-auto leading-relaxed px-2">
                <?php echo esc_html($sectionDescription); ?>
            </p>
            <?php endif; ?>
        </div>

        <!-- Dashboard Mockup Container -->
        <div class="relative max-w-6xl mx-auto px-2 sm:px-0">
            <!-- Glow Effect Behind Mockup -->
            <div class="absolute inset-0 bg-gradient-to-b from-blue-500/20 via-cyan-500/10 to-transparent blur-3xl transform scale-110 -z-10"></div>
            
            <!-- Browser Frame -->
            <div class="relative rounded-xl sm:rounded-2xl md:rounded-3xl overflow-hidden shadow-2xl shadow-blue-500/10 border border-white/10 backdrop-blur-sm bg-[#0d0d14]/80">
                <!-- Browser Header -->
                <div class="flex items-center gap-1.5 sm:gap-2 px-2 sm:px-4 py-2 sm:py-3 bg-[#1a1a24]/90 border-b border-white/5">
                    <div class="flex gap-1.5 sm:gap-2">
                        <div class="w-2 h-2 sm:w-3 sm:h-3 rounded-full bg-red-500/80"></div>
                        <div class="w-2 h-2 sm:w-3 sm:h-3 rounded-full bg-yellow-500/80"></div>
                        <div class="w-2 h-2 sm:w-3 sm:h-3 rounded-full bg-green-500/80"></div>
                    </div>
                    <div class="flex-1 mx-2 sm:mx-4">
                        <div class="flex items-center gap-1.5 sm:gap-2 px-2 sm:px-4 py-1 sm:py-1.5 rounded-lg bg-[#0a0a0f]/60 border border-white/5 max-w-md mx-auto">
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span class="text-[10px] sm:text-xs text-slate-400 truncate">admin.yoursite.com/dashboard</span>
                        </div>
                    </div>
                </div>
                
                <!-- Dashboard Content - Mockup Image -->
                <div class="relative aspect-[4/3] sm:aspect-[16/10] bg-gradient-to-br from-[#0f0f18] to-[#0a0a12]">
                    <!-- Placeholder Dashboard UI -->
                    <div class="absolute inset-0 p-2 sm:p-3 md:p-4 lg:p-6">
                        <div class="flex h-full gap-2 sm:gap-3 md:gap-4 lg:gap-6">
                            <!-- Sidebar -->
                            <div class="hidden md:flex flex-col w-48 lg:w-56 bg-[#12121a]/80 rounded-lg lg:rounded-xl p-3 lg:p-4 border border-white/5">
                                <div class="flex items-center gap-3 mb-8">
                                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                    </div>
                                    <span class="font-semibold text-white">Codetic</span>
                                </div>
                                <nav class="space-y-2">
                                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-blue-500/20 text-blue-300">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                        <span class="text-sm">Dashboard</span>
                                    </div>
                                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:bg-white/5">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        <span class="text-sm">İçerikler</span>
                                    </div>
                                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:bg-white/5">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                        <span class="text-sm">Analitik</span>
                                    </div>
                                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:bg-white/5">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        <span class="text-sm">Kullanıcılar</span>
                                    </div>
                                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:bg-white/5">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span class="text-sm">Ayarlar</span>
                                    </div>
                                </nav>
                            </div>
                            
                            <!-- Main Content -->
                            <div class="flex-1 flex flex-col gap-2 sm:gap-3 md:gap-4 lg:gap-6">
                                <!-- Stats Row -->
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-2.5 md:gap-3 lg:gap-4">
                                    <div class="bg-[#12121a]/80 rounded-lg sm:rounded-xl p-2 sm:p-2.5 md:p-3 lg:p-4 border border-white/5">
                                        <div class="text-[10px] sm:text-xs text-slate-500 mb-0.5 sm:mb-1">Toplam Ziyaretçi</div>
                                        <div class="text-sm sm:text-base md:text-lg lg:text-2xl font-bold text-white">24,521</div>
                                        <div class="text-[10px] sm:text-xs text-green-400 mt-0.5 sm:mt-1">+12.5%</div>
                                    </div>
                                    <div class="bg-[#12121a]/80 rounded-lg sm:rounded-xl p-2 sm:p-2.5 md:p-3 lg:p-4 border border-white/5">
                                        <div class="text-[10px] sm:text-xs text-slate-500 mb-0.5 sm:mb-1">Sayfa Görüntüleme</div>
                                        <div class="text-sm sm:text-base md:text-lg lg:text-2xl font-bold text-white">89,432</div>
                                        <div class="text-[10px] sm:text-xs text-green-400 mt-0.5 sm:mt-1">+8.2%</div>
                                    </div>
                                    <div class="bg-[#12121a]/80 rounded-lg sm:rounded-xl p-2 sm:p-2.5 md:p-3 lg:p-4 border border-white/5">
                                        <div class="text-[10px] sm:text-xs text-slate-500 mb-0.5 sm:mb-1">Dönüşüm Oranı</div>
                                        <div class="text-sm sm:text-base md:text-lg lg:text-2xl font-bold text-white">3.24%</div>
                                        <div class="text-[10px] sm:text-xs text-green-400 mt-0.5 sm:mt-1">+2.1%</div>
                                    </div>
                                    <div class="bg-[#12121a]/80 rounded-lg sm:rounded-xl p-2 sm:p-2.5 md:p-3 lg:p-4 border border-white/5">
                                        <div class="text-[10px] sm:text-xs text-slate-500 mb-0.5 sm:mb-1">Aktif Kullanıcı</div>
                                        <div class="text-sm sm:text-base md:text-lg lg:text-2xl font-bold text-white">1,842</div>
                                        <div class="text-[10px] sm:text-xs text-blue-400 mt-0.5 sm:mt-1">Şu an</div>
                                    </div>
                                </div>
                                
                                <!-- Chart Area -->
                                <div class="flex-1 bg-[#12121a]/80 rounded-lg sm:rounded-xl p-2.5 sm:p-3 md:p-4 lg:p-6 border border-white/5 min-h-[120px] sm:min-h-[140px] md:min-h-[160px]">
                                    <div class="flex items-center justify-between mb-2 sm:mb-3 md:mb-4">
                                        <h3 class="text-xs sm:text-sm font-medium text-white">Ziyaretçi İstatistikleri</h3>
                                        <div class="flex gap-1 sm:gap-2">
                                            <span class="px-1.5 sm:px-2 py-0.5 sm:py-1 text-[10px] sm:text-xs rounded bg-blue-500/20 text-blue-300">7 Gün</span>
                                            <span class="px-1.5 sm:px-2 py-0.5 sm:py-1 text-[10px] sm:text-xs rounded text-slate-500">30 Gün</span>
                                        </div>
                                    </div>
                                    <!-- Chart Visualization -->
                                    <div class="relative h-24 sm:h-32 md:h-40 lg:h-48">
                                        <svg class="w-full h-full" viewBox="0 0 400 150" preserveAspectRatio="none">
                                            <defs>
                                                <linearGradient id="chartGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                                    <stop offset="0%" style="stop-color:rgb(59,130,246);stop-opacity:0.3" />
                                                    <stop offset="100%" style="stop-color:rgb(59,130,246);stop-opacity:0" />
                                                </linearGradient>
                                            </defs>
                                            <path d="M0,120 Q50,100 100,80 T200,60 T300,90 T400,40 L400,150 L0,150 Z" fill="url(#chartGradient)"/>
                                            <path d="M0,120 Q50,100 100,80 T200,60 T300,90 T400,40" fill="none" stroke="rgb(59,130,246)" stroke-width="2"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reflection/Glow at bottom -->
                    <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-blue-500/5 to-transparent pointer-events-none"></div>
                </div>
            </div>
            
            <!-- Bottom Reflection -->
            <div class="absolute -bottom-20 left-1/2 -translate-x-1/2 w-3/4 h-40 bg-gradient-to-b from-blue-500/10 to-transparent blur-2xl rounded-full"></div>
        </div>

        <!-- Features List -->
        <?php if (!empty($features)): ?>
        <div class="flex flex-wrap justify-center gap-2 sm:gap-3 md:gap-4 lg:gap-6 mt-8 sm:mt-10 md:mt-12 lg:mt-16 px-2">
            <?php foreach ($features as $feature): ?>
            <div class="flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-1.5 sm:py-2 rounded-full bg-white/5 border border-white/10 backdrop-blur-sm">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="text-xs sm:text-sm text-slate-300 whitespace-nowrap"><?php echo esc_html($feature); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Bottom Mask -->
    <div class="dashboard-bottom-mask"></div>
</section>

<style>
/* Dashboard Showcase Section Styles */
#<?php echo esc_attr($sectionId); ?> {
    --dashboard-glow-color: rgba(59, 130, 246, 0.15);
}

@keyframes pulse-slow {
    0%, 100% {
        opacity: 0.4;
        transform: scale(1);
    }
    50% {
        opacity: 0.6;
        transform: scale(1.05);
    }
}

#<?php echo esc_attr($sectionId); ?> .animate-pulse-slow {
    animation: pulse-slow 8s ease-in-out infinite;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    
    #<?php echo esc_attr($sectionId); ?> .animate-pulse-slow {
        animation: none !important;
    }
    
    /* Mobilde glow efektlerini azalt */
    #<?php echo esc_attr($sectionId); ?> .absolute.inset-0.bg-gradient-to-b {
        opacity: 0.5;
    }
}

@media (max-width: 768px) {
    /* Tablet ve mobil için ekstra ayarlar */
    #<?php echo esc_attr($sectionId); ?> {
        padding-top: 3rem;
        padding-bottom: 3rem;
    }
}
</style>

