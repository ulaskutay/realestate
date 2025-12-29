<?php
$title = $section['title'] ?? 'Neden Bizi Tercih Etmelisiniz?';
$subtitle = $section['subtitle'] ?? 'Müşterilerimize en iyi deneyimi sunmak için çalışıyoruz.';
$items = $section['items'] ?? [
    ['icon' => 'rocket_launch', 'title' => 'Hızlı Performans', 'description' => 'Optimize edilmiş kod yapısı ile yüksek performans.'],
    ['icon' => 'palette', 'title' => 'Modern Tasarım', 'description' => 'Güncel trendlere uygun şık ve modern görünüm.'],
    ['icon' => 'devices', 'title' => 'Responsive', 'description' => 'Tüm cihazlarda mükemmel görünüm.'],
    ['icon' => 'security', 'title' => 'Güvenli', 'description' => 'En güncel güvenlik standartları.'],
    ['icon' => 'support_agent', 'title' => '7/24 Destek', 'description' => 'Her zaman yanınızda olan destek ekibi.'],
    ['icon' => 'settings', 'title' => 'Kolay Yönetim', 'description' => 'Kullanıcı dostu admin paneli.']
];
?>

<section id="features" class="py-24 bg-surface">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                <?php echo htmlspecialchars($title); ?>
            </h2>
            <p class="text-lg text-muted">
                <?php echo htmlspecialchars($subtitle); ?>
            </p>
        </div>
        
        <!-- Features Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($items as $item): ?>
            <div class="group p-8 bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <div class="w-14 h-14 gradient-primary rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-white text-2xl"><?php echo htmlspecialchars($item['icon'] ?? 'star'); ?></span>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">
                    <?php echo htmlspecialchars($item['title'] ?? ''); ?>
                </h3>
                <p class="text-muted leading-relaxed">
                    <?php echo htmlspecialchars($item['description'] ?? ''); ?>
                </p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

