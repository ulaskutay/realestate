<?php
/**
 * Real Estate Theme - Why Choose Us Section
 * Modern and simplified design
 */

$section = $section ?? [];
$items = $section['items'] ?? [];

// Default items if not provided
if (empty($items)) {
    $items = [
        [
            'icon' => 'shield',
            'title' => __('Güvenilir & Deneyimli'),
            'description' => __('Gayrimenkul piyasasında yılların deneyimi ve başarılı işlemlerle kanıtlanmış bir geçmiş.')
        ],
        [
            'icon' => 'home',
            'title' => __('Geniş Seçenek'),
            'description' => __('Tüm fiyat aralıkları ve lokasyonlarda binlerce mülke erişim.')
        ],
        [
            'icon' => 'users',
            'title' => __('Uzman Ekip'),
            'description' => __('Profesyonel ekibimiz, mükemmel mülkünüzü bulmanız için size adanmış.')
        ],
        [
            'icon' => 'clock',
            'title' => __('7/24 Destek'),
            'description' => __('Sorularınızı yanıtlamak ve süreçte size rehberlik etmek için kesintisiz yardım.')
        ],
        [
            'icon' => 'dollar',
            'title' => __('En İyi Fiyatlar'),
            'description' => __('Rekabetçi fiyatlandırma ve gizli maliyet olmadan şeffaf ücretler.')
        ],
        [
            'icon' => 'document',
            'title' => __('Kolay Süreç'),
            'description' => __('Gayrimenkul yolculuğunuzu sorunsuz hale getirmek için sadeleştirilmiş prosedürler.')
        ]
    ];
}

$sectionTitle = !empty($section['title']) ? $section['title'] : __('Neden Bizi Seçmelisiniz');
$sectionSubtitle = !empty($section['subtitle']) ? $section['subtitle'] : __('Hayalinizdeki mülkü bulmanızı kolay ve stressiz hale getiriyoruz');
?>

<section class="py-20 lg:py-28 bg-white">
    <div class="container mx-auto px-4 lg:px-6">
        <div class="text-center mb-16">
            <h2 class="text-4xl lg:text-5xl font-bold text-secondary mb-4"><?php echo esc_html($sectionTitle); ?></h2>
            <?php if (!empty($sectionSubtitle)): ?>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto"><?php echo esc_html($sectionSubtitle); ?></p>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            <?php foreach ($items as $index => $item): ?>
                <div class="group relative bg-white rounded-2xl p-8 border border-gray-100 hover:border-primary/20 hover:shadow-xl transition-all duration-300">
                    <!-- Modern Icon -->
                    <div class="mb-6">
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-primary/10 to-primary/5 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <?php
                            $icon = $item['icon'] ?? 'home';
                            $iconSvg = '';
                            switch ($icon) {
                                case 'shield':
                                    $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>';
                                    break;
                                case 'users':
                                    $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>';
                                    break;
                                case 'clock':
                                    $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>';
                                    break;
                                case 'dollar':
                                    $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>';
                                    break;
                                case 'document':
                                    $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>';
                                    break;
                                default: // home
                                    $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>';
                            }
                            ?>
                            <svg class="w-7 h-7 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <?php echo $iconSvg; ?>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <h3 class="text-xl font-bold text-secondary mb-3 group-hover:text-primary transition-colors">
                        <?php echo esc_html(__($item['title'])); ?>
                    </h3>
                    <p class="text-gray-600 leading-relaxed">
                        <?php echo esc_html(__($item['description'])); ?>
                    </p>
                    
                    <!-- Hover Effect Line -->
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-primary to-primary/50 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 rounded-b-2xl"></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
