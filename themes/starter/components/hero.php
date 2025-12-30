<?php
$title = $section['title'] ?? 'Modern & Minimal Tasarım';
$subtitle = $section['subtitle'] ?? 'Web sitenizi profesyonel bir görünüme kavuşturun. Starter Theme ile kolayca özelleştirin.';
$settings = $section['settings'] ?? [];
$buttonText = $settings['button_text'] ?? 'Hemen Başla';
$buttonLink = $settings['button_link'] ?? '/contact';
$secondaryButtonText = $settings['secondary_button_text'] ?? 'Daha Fazla';
$secondaryButtonLink = $settings['secondary_button_link'] ?? '#features';
$backgroundImage = $settings['background_image'] ?? '';
$overlayColor = $settings['overlay_color'] ?? '#111827';
$overlayOpacity = $settings['overlay_opacity'] ?? '80';

// İstatistikler - items'dan veya varsayılandan al
$stats = $section['items'] ?? [
    ['value' => '500+', 'label' => 'Mutlu Müşteri'],
    ['value' => '50+', 'label' => 'Tamamlanan Proje'],
    ['value' => '10+', 'label' => 'Yıllık Deneyim']
];

// Overlay opaklığını hesapla (0-100 arası değeri 0-1 arasına çevir)
$overlayOpacityDecimal = intval($overlayOpacity) / 100;
?>

<section class="relative min-h-[90vh] flex items-center overflow-hidden">
    <!-- Background -->
    <?php if ($backgroundImage): ?>
    <div class="absolute inset-0 z-0">
        <img src="<?php echo htmlspecialchars($backgroundImage); ?>" alt="" class="w-full h-full object-cover">
        <!-- Overlay -->
        <div class="absolute inset-0" style="background-color: <?php echo htmlspecialchars($overlayColor); ?>; opacity: <?php echo $overlayOpacityDecimal; ?>;"></div>
    </div>
    <?php else: ?>
    <div class="absolute inset-0 z-0 bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
        <!-- Decorative Elements -->
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-primary/20 rounded-full blur-3xl"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-secondary/20 rounded-full blur-3xl"></div>
    </div>
    <?php endif; ?>
    
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="max-w-3xl">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-tight mb-6">
                <?php echo htmlspecialchars($title); ?>
            </h1>
            <p class="text-xl text-gray-300 mb-10 leading-relaxed">
                <?php echo htmlspecialchars($subtitle); ?>
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="<?php echo htmlspecialchars($buttonLink); ?>" class="btn-primary px-8 py-4 rounded-xl font-semibold text-lg shadow-lg shadow-primary/30">
                    <?php echo htmlspecialchars($buttonText); ?>
                </a>
                <a href="<?php echo htmlspecialchars($secondaryButtonLink); ?>" class="btn-secondary px-8 py-4 rounded-xl font-semibold text-lg border-white/30 text-white hover:bg-white hover:text-gray-900">
                    <?php echo htmlspecialchars($secondaryButtonText); ?>
                </a>
            </div>
            
            <!-- Stats -->
            <?php if (!empty($stats)): ?>
            <div class="mt-16 grid grid-cols-<?php echo count($stats); ?> gap-8">
                <?php foreach ($stats as $stat): ?>
                <div>
                    <div class="text-3xl md:text-4xl font-bold text-white"><?php echo htmlspecialchars($stat['value'] ?? ''); ?></div>
                    <div class="text-gray-400 mt-1"><?php echo htmlspecialchars($stat['label'] ?? ''); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Scroll Indicator -->
    <div class="absolute bottom-10 left-1/2 -translate-x-1/2 animate-bounce">
        <a href="#features" class="text-white/50 hover:text-white transition-colors">
            <span class="material-symbols-outlined text-3xl">keyboard_arrow_down</span>
        </a>
    </div>
</section>
