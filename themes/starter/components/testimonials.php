<?php
$title = $section['title'] ?? 'Müşterilerimiz Ne Diyor?';
$subtitle = $section['subtitle'] ?? 'Birlikte çalıştığımız müşterilerimizden geri bildirimler.';
$items = $section['items'] ?? [
    [
        'name' => 'Ahmet Yılmaz',
        'role' => 'CEO, TechCorp',
        'content' => 'Harika bir deneyimdi. Profesyonel yaklaşımları ve kaliteli işleri ile beklentilerimizi aştılar.',
        'rating' => 5
    ],
    [
        'name' => 'Elif Demir',
        'role' => 'Marketing Manager',
        'content' => 'Projemiz zamanında ve bütçe dahilinde tamamlandı. Kesinlikle tekrar çalışmak isteriz.',
        'rating' => 5
    ],
    [
        'name' => 'Mehmet Kara',
        'role' => 'Founder, StartupX',
        'content' => 'İletişimleri çok güçlü. Her adımda bilgilendirildik ve sonuç mükemmel oldu.',
        'rating' => 5
    ]
];
?>

<section class="py-24 bg-surface">
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
        
        <!-- Testimonials Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($items as $item): ?>
            <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-lg transition-shadow">
                <!-- Rating -->
                <div class="flex items-center gap-1 mb-4">
                    <?php for ($i = 0; $i < ($item['rating'] ?? 5); $i++): ?>
                    <span class="material-symbols-outlined text-yellow-400 text-xl" style="font-variation-settings: 'FILL' 1;">star</span>
                    <?php endfor; ?>
                </div>
                
                <!-- Quote -->
                <p class="text-gray-700 leading-relaxed mb-6">
                    "<?php echo htmlspecialchars($item['content'] ?? ''); ?>"
                </p>
                
                <!-- Author -->
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-primary to-secondary rounded-full flex items-center justify-center">
                        <span class="text-white font-semibold text-lg">
                            <?php echo strtoupper(substr($item['name'] ?? 'A', 0, 1)); ?>
                        </span>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($item['name'] ?? ''); ?></div>
                        <div class="text-sm text-muted"><?php echo htmlspecialchars($item['role'] ?? ''); ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

