<?php
$title = $section['title'] ?? 'Neden Bizi Tercih Etmelisiniz?';
$subtitle = $section['subtitle'] ?? 'Müşterilerimize en iyi deneyimi sunmak için çalışıyoruz.';
$settings = $section['settings'] ?? [];
$columns = isset($settings['columns']) ? (string)$settings['columns'] : '3';
// Kolon sayısını integer'a çevir (güvenlik için)
$columnsInt = in_array($columns, ['2', '3', '4']) ? (int)$columns : 3;
$items = $section['items'] ?? [
    ['icon' => 'rocket_launch', 'title' => 'Hızlı Performans', 'description' => 'Optimize edilmiş kod yapısı ile yüksek performans.'],
    ['icon' => 'palette', 'title' => 'Modern Tasarım', 'description' => 'Güncel trendlere uygun şık ve modern görünüm.'],
    ['icon' => 'devices', 'title' => 'Responsive', 'description' => 'Tüm cihazlarda mükemmel görünüm.']
];
?>

<section id="features" class="py-24 bg-surface">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center max-w-3xl mx-auto mb-16">
            <?php if (!empty($title)): ?>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                <?php echo htmlspecialchars($title); ?>
            </h2>
            <?php endif; ?>
            <?php if (!empty($subtitle)): ?>
            <p class="text-lg text-muted">
                <?php echo htmlspecialchars($subtitle); ?>
            </p>
            <?php endif; ?>
        </div>
        
        <!-- Features Grid -->
        <style>
            #features-grid-<?php echo $columnsInt; ?> {
                display: grid;
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            @media (min-width: 768px) {
                #features-grid-<?php echo $columnsInt; ?> {
                    grid-template-columns: repeat(<?php echo min($columnsInt, 2); ?>, 1fr);
                }
            }
            @media (min-width: 1024px) {
                #features-grid-<?php echo $columnsInt; ?> {
                    grid-template-columns: repeat(<?php echo $columnsInt; ?>, 1fr);
                }
            }
        </style>
        <div id="features-grid-<?php echo $columnsInt; ?>">
            <?php foreach ($items as $item): 
                $hasLink = !empty($item['link']);
            ?>
            <?php if ($hasLink): ?>
            <a href="<?php echo esc_url($item['link']); ?>" class="group block">
            <?php else: ?>
            <div class="group">
            <?php endif; ?>
                <div class="p-8 bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 h-full">
                    <?php if (!empty($item['icon'])): ?>
                    <div class="w-14 h-14 gradient-primary rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-white text-2xl"><?php echo htmlspecialchars($item['icon']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($item['title'])): ?>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">
                        <?php echo htmlspecialchars($item['title']); ?>
                    </h3>
                    <?php endif; ?>
                    <?php if (!empty($item['description'])): ?>
                    <p class="text-muted leading-relaxed">
                        <?php echo htmlspecialchars($item['description']); ?>
                    </p>
                    <?php endif; ?>
                    <?php if ($hasLink): ?>
                    <div class="mt-4 flex items-center text-primary font-medium group-hover:gap-2 transition-all">
                        <span>Daha Fazla</span>
                        <span class="material-symbols-outlined text-lg opacity-0 group-hover:opacity-100 transition-opacity">arrow_forward</span>
                    </div>
                    <?php endif; ?>
                </div>
            <?php if ($hasLink): ?>
            </a>
            <?php else: ?>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

