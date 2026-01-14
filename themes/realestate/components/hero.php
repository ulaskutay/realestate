<?php
/**
 * Real Estate Theme - Hero Section
 * Modern hero with integrated search bar
 */

$section = $section ?? [];

// Settings'i decode et (JSON string ise)
$rawSettings = $section['settings'] ?? [];
if (is_string($rawSettings)) {
    $decoded = json_decode($rawSettings, true);
    $settings = is_array($decoded) ? $decoded : [];
} else {
    $settings = is_array($rawSettings) ? $rawSettings : [];
}

// Tüm değerleri sadece section'dan al (tamamen özelleştirme paneline bağlı)
$heroTitle = !empty($section['title']) ? $section['title'] : __('Hayalinizdeki Evini Bugün Bulun');
$heroSubtitle = !empty($section['subtitle']) ? $section['subtitle'] : __('Geniş gayrimenkul koleksiyonumuzdan premium evler, daireler ve ticari alanlar arasından mükemmel mülkü keşfedin.');
$primaryButtonText = !empty($settings['button_text']) ? $settings['button_text'] : __('Mülkleri İncele');
$primaryButtonLinkRaw = !empty($settings['button_link']) ? $settings['button_link'] : '/ilanlar';
$primaryButtonLink = function_exists('localized_url') ? localized_url($primaryButtonLinkRaw) : $primaryButtonLinkRaw;

$secondaryButtonText = !empty($settings['secondary_button_text']) ? $settings['secondary_button_text'] : __('Tur Planla');
$secondaryButtonLinkRaw = !empty($settings['secondary_button_link']) ? $settings['secondary_button_link'] : '/contact';
$secondaryButtonLink = function_exists('localized_url') ? localized_url($secondaryButtonLinkRaw) : $secondaryButtonLinkRaw;

$heroImage = !empty($settings['hero_image']) ? $settings['hero_image'] : '';

// Buton renkleri - Tema renk paletinden al (birincil ve ikincil renkler)
$primaryButtonBg = '#1e40af'; // Varsayılan
$primaryButtonTextColor = '#ffffff';
$secondaryButtonBg = 'rgba(30, 41, 59, 0.8)'; // Varsayılan secondary (şeffaf)
$secondaryButtonTextColor = '#ffffff';

// Hex rengi rgba'ya çeviren helper fonksiyon
$hexToRgba = function($hex, $opacity = 0.8) {
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return "rgba($r, $g, $b, $opacity)";
};

// ThemeLoader'dan tema renklerini al
if (class_exists('ThemeLoader')) {
    $themeLoaderInstance = ThemeLoader::getInstance();
    // Birincil buton -> Tema primary rengi (Tema Renkleri > Birincil Renk)
    $primaryColor = $themeLoaderInstance->getColor('primary', '#1e40af');
    $primaryButtonBg = $primaryColor;
    $primaryButtonTextColor = '#ffffff'; // Birincil buton metin rengi her zaman beyaz
    
    // İkincil buton -> Tema secondary rengi (Tema Renkleri > İkincil Renk)
    $secondaryColor = $themeLoaderInstance->getColor('secondary', '#1e293b');
    // Secondary rengi rgba formatına çevir (koyu arka plan üzerinde görünür olması için %80 opacity)
    $secondaryButtonBg = $hexToRgba($secondaryColor, 0.8);
    $secondaryButtonTextColor = '#ffffff'; // İkincil buton metin rengi her zaman beyaz
}
?>

<section class="relative min-h-[85vh] lg:min-h-[95vh] flex items-center justify-center overflow-hidden">
    <!-- Background Image -->
    <?php if ($heroImage): ?>
        <div class="absolute inset-0 z-0">
            <img src="<?php echo esc_url($heroImage); ?>" alt="<?php echo esc_attr($heroTitle); ?>" class="w-full h-full object-cover">
        </div>
    <?php else: ?>
        <!-- Default gradient background if no image -->
        <div class="absolute inset-0 z-0 bg-gradient-to-br from-secondary/80 via-secondary/60 to-secondary/80"></div>
    <?php endif; ?>
    
    <!-- Dark Overlay - Daha koyu overlay -->
    <div class="absolute inset-0 z-0 bg-black/60"></div>

    <!-- Content -->
    <div class="container mx-auto px-4 lg:px-8 relative z-10 w-full py-16 lg:py-24">
        <div class="max-w-6xl">
            <!-- Headline and Description -->
            <div class="text-white mb-8 lg:mb-12">
                <h1 class="text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-bold mb-5 lg:mb-6 leading-tight">
                    <?php echo esc_html($heroTitle); ?>
                </h1>
                <p class="text-base sm:text-lg lg:text-xl text-white/95 max-w-3xl leading-relaxed">
                    <?php echo esc_html($heroSubtitle); ?>
                </p>
            </div>

            <!-- Search Bar -->
            <div class="bg-white rounded-xl shadow-xl p-4 lg:p-5 max-w-6xl mb-4">
                <form action="<?php echo function_exists('localized_url') ? localized_url('/ilanlar') : site_url('/ilanlar'); ?>" method="get" class="flex flex-col lg:flex-row gap-3">
                    <!-- Location -->
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <?php echo esc_html(__('Konum')); ?>
                        </label>
                        <input type="text" 
                               name="location" 
                               placeholder="<?php echo esc_attr(__('Şehir, mahalle')); ?>" 
                               class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-gray-900 text-sm">
                    </div>

                    <!-- Price Range -->
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <?php echo esc_html(__('Fiyat Aralığı')); ?>
                        </label>
                        <select name="price_range" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-gray-900 bg-white text-sm">
                            <option value=""><?php echo esc_html(__('Herhangi Bir Fiyat')); ?></option>
                            <option value="0-100000">₺0 - ₺1.000.000</option>
                            <option value="100000-250000">₺1.000.000 - ₺2.500.000</option>
                            <option value="250000-500000">₺2.500.000 - ₺5.000.000</option>
                            <option value="500000-1000000">₺5.000.000 - ₺10.000.000</option>
                            <option value="1000000+">₺10.000.000+</option>
                        </select>
                    </div>

                    <!-- Property Type -->
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <?php echo esc_html(__('Mülk Tipi')); ?>
                        </label>
                        <select name="type" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-gray-900 bg-white text-sm">
                            <option value=""><?php echo esc_html(__('Tüm Tipler')); ?></option>
                            <option value="house"><?php echo esc_html(__('Villa')); ?></option>
                            <option value="apartment"><?php echo esc_html(__('Daire')); ?></option>
                            <option value="villa"><?php echo esc_html(__('Müstakil')); ?></option>
                            <option value="commercial"><?php echo esc_html(__('Ticari')); ?></option>
                            <option value="land"><?php echo esc_html(__('Arsa')); ?></option>
                        </select>
                    </div>

                    <!-- Search Button -->
                    <div class="flex items-end">
                        <button type="submit" style="background-color: <?php echo esc_attr($primaryButtonBg); ?>; color: <?php echo esc_attr($primaryButtonTextColor); ?>;" class="w-full lg:w-auto px-6 lg:px-8 py-3 lg:py-3.5 rounded-lg font-semibold hover:opacity-90 transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2 text-base">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <span><?php echo esc_html(__('Ara')); ?></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
