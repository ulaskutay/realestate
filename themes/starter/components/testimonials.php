<?php
$title = $section['title'] ?? 'Müşterilerimiz Ne Diyor?';
$subtitle = $section['subtitle'] ?? 'Birlikte çalıştığımız müşterilerimizden geri bildirimler.';
$settings = $section['settings'] ?? [];
$columns = isset($settings['columns']) ? (string)$settings['columns'] : '3';
// Kolon sayısını integer'a çevir (güvenlik için)
$columnsInt = in_array($columns, ['2', '3', '4']) ? (int)$columns : 3;
$items = $section['items'] ?? [
    [
        'name' => 'Ahmet Yılmaz',
        'role' => 'CEO, TechCorp',
        'content' => 'Harika bir deneyimdi. Profesyonel yaklaşımları ve kaliteli işleri ile beklentilerimizi aştılar.',
        'rating' => 5,
        'avatar' => ''
    ],
    [
        'name' => 'Elif Demir',
        'role' => 'Marketing Manager',
        'content' => 'Projemiz zamanında ve bütçe dahilinde tamamlandı. Kesinlikle tekrar çalışmak isteriz.',
        'rating' => 5,
        'avatar' => ''
    ],
    [
        'name' => 'Mehmet Kara',
        'role' => 'Founder, StartupX',
        'content' => 'İletişimleri çok güçlü. Her adımda bilgilendirildik ve sonuç mükemmel oldu.',
        'rating' => 5,
        'avatar' => ''
    ]
];
?>

<section class="py-24 bg-surface">
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
        
        <!-- Testimonials Grid -->
        <style>
            #testimonials-grid-<?php echo $columnsInt; ?> {
                display: grid;
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            @media (min-width: 768px) {
                #testimonials-grid-<?php echo $columnsInt; ?> {
                    grid-template-columns: repeat(<?php echo min($columnsInt, 2); ?>, 1fr);
                }
            }
            @media (min-width: 1024px) {
                #testimonials-grid-<?php echo $columnsInt; ?> {
                    grid-template-columns: repeat(<?php echo $columnsInt; ?>, 1fr);
                }
            }
        </style>
        <div id="testimonials-grid-<?php echo $columnsInt; ?>">
            <?php foreach ($items as $item): ?>
            <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-lg transition-shadow">
                <!-- Rating -->
                <div class="flex items-center gap-1 mb-4">
                    <?php 
                    $rating = isset($item['rating']) ? (int)$item['rating'] : 5;
                    for ($i = 0; $i < 5; $i++): 
                    ?>
                    <span class="material-symbols-outlined text-xl <?php echo $i < $rating ? 'text-yellow-400' : 'text-gray-300'; ?>" style="font-variation-settings: 'FILL' <?php echo $i < $rating ? 1 : 0; ?>;">star</span>
                    <?php endfor; ?>
                </div>
                
                <!-- Quote -->
                <?php if (!empty($item['content'])): ?>
                <p class="text-gray-700 leading-relaxed mb-6">
                    "<?php echo htmlspecialchars($item['content']); ?>"
                </p>
                <?php endif; ?>
                
                <!-- Author -->
                <div class="flex items-center gap-4">
                    <?php if (!empty($item['avatar'])): ?>
                    <div class="w-12 h-12 rounded-full overflow-hidden flex-shrink-0">
                        <img src="<?php echo esc_url($item['avatar']); ?>" alt="<?php echo htmlspecialchars($item['name'] ?? ''); ?>" class="w-full h-full object-cover">
                    </div>
                    <?php else: ?>
                    <div class="w-12 h-12 bg-gradient-to-br from-primary to-secondary rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-white font-semibold text-lg">
                            <?php echo strtoupper(substr($item['name'] ?? 'A', 0, 1)); ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    <div>
                        <?php if (!empty($item['name'])): ?>
                        <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($item['name']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($item['role'])): ?>
                        <div class="text-sm text-muted"><?php echo htmlspecialchars($item['role']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

