<?php
$title = $section['title'] ?? 'Hakkımızda';
$subtitle = $section['subtitle'] ?? 'Biz kimiz ve ne yapıyoruz?';
$content = $section['content'] ?? 'Yılların deneyimi ile müşterilerimize en kaliteli hizmeti sunuyoruz. Profesyonel ekibimiz ile projelerinizi hayata geçiriyoruz.';
$settings = $section['settings'] ?? [];
$image = $settings['image'] ?? '';
$badgeValue = $settings['badge_value'] ?? '10+';
$badgeLabel = $settings['badge_label'] ?? 'Yıllık Deneyim';
$buttonText = $settings['button_text'] ?? 'Daha Fazla Bilgi';
$buttonLink = $settings['button_link'] ?? '/about';

// Özellik listesi
$features = $section['items'] ?? [
    ['text' => 'Profesyonel Ekip'],
    ['text' => 'Müşteri Odaklı Yaklaşım'],
    ['text' => 'Kaliteli ve Hızlı Hizmet']
];
?>

<section class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <!-- Image -->
            <div class="relative">
                <?php if ($image): ?>
                <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($title); ?>" class="rounded-2xl shadow-2xl w-full" loading="lazy">
                <?php else: ?>
                <div class="aspect-[4/3] rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                    <span class="material-symbols-outlined text-8xl text-gray-400">photo</span>
                </div>
                <?php endif; ?>
                
                <!-- Floating Card -->
                <?php if ($badgeValue || $badgeLabel): ?>
                <div class="absolute -bottom-6 -right-6 bg-white p-6 rounded-xl shadow-xl max-w-[200px]">
                    <div class="text-4xl font-bold text-primary mb-1"><?php echo htmlspecialchars($badgeValue); ?></div>
                    <div class="text-sm text-muted"><?php echo htmlspecialchars($badgeLabel); ?></div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Content -->
            <div>
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-primary/10 text-primary rounded-full text-sm font-medium mb-6">
                    <span class="material-symbols-outlined text-lg">info</span>
                    <?php echo htmlspecialchars($subtitle); ?>
                </div>
                
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                    <?php echo htmlspecialchars($title); ?>
                </h2>
                
                <div class="prose prose-lg text-muted mb-8">
                    <?php echo nl2br(htmlspecialchars($content)); ?>
                </div>
                
                <!-- Features List -->
                <?php if (!empty($features)): ?>
                <div class="space-y-4 mb-8">
                    <?php foreach ($features as $feature): ?>
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 bg-accent/20 text-accent rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-base">check</span>
                        </span>
                        <span class="text-gray-700"><?php echo htmlspecialchars($feature['text'] ?? ''); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($buttonText): ?>
                <a href="<?php echo htmlspecialchars($buttonLink); ?>" class="btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-lg font-medium">
                    <?php echo htmlspecialchars($buttonText); ?>
                    <span class="material-symbols-outlined">arrow_forward</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
