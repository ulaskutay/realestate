<?php
/**
 * Real Estate Theme - Call to Action Section
 */

$section = $section ?? [];
$settings = $section['settings'] ?? [];

$ctaTitle = !empty($section['title']) ? $section['title'] : __('Ready to Find Your Dream Home?');
$ctaSubtitle = !empty($section['subtitle']) ? $section['subtitle'] : __('Let us help you find the perfect property. Contact us today for a free consultation.');
$ctaButtonText = !empty($settings['button_text']) ? $settings['button_text'] : __('Get Started Today');
$ctaButtonLinkRaw = !empty($settings['button_link']) ? $settings['button_link'] : '/contact';
$ctaButtonLink = function_exists('localized_url') ? localized_url($ctaButtonLinkRaw) : site_url($ctaButtonLinkRaw);
$ctaSecondaryButtonText = !empty($settings['secondary_button_text']) ? $settings['secondary_button_text'] : __('Browse Properties');
$ctaSecondaryButtonLinkRaw = !empty($settings['secondary_button_link']) ? $settings['secondary_button_link'] : '/ilanlar';
$ctaSecondaryButtonLink = function_exists('localized_url') ? localized_url($ctaSecondaryButtonLinkRaw) : site_url($ctaSecondaryButtonLinkRaw);

// Form seçimi
$selectedFormId = !empty($settings['form_id']) ? intval($settings['form_id']) : null;
$showForm = !empty($selectedFormId);

// Buton renkleri - Tema renk paletinden al
$primaryButtonBg = '#1e40af';
$primaryButtonTextColor = '#ffffff';
$secondaryButtonBg = '#1e293b';
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
    $primaryColor = $themeLoaderInstance->getColor('primary', '#1e40af');
    $secondaryColor = $themeLoaderInstance->getColor('secondary', '#1e293b');
    
    $primaryButtonBg = $primaryColor;
    $primaryButtonTextColor = '#ffffff';
    $secondaryButtonBg = $hexToRgba($secondaryColor, 0.8);
    $secondaryButtonTextColor = '#ffffff';
}
?>

<section class="py-16 lg:py-24 bg-gradient-to-r from-primary to-secondary text-white relative overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"1\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    </div>

    <div class="container mx-auto px-4 lg:px-6 relative z-10">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-3xl lg:text-5xl font-bold mb-6"><?php echo esc_html($ctaTitle); ?></h2>
            <?php if (!empty($ctaSubtitle)): ?>
                <p class="text-lg lg:text-xl mb-8 text-white/90"><?php echo esc_html($ctaSubtitle); ?></p>
            <?php endif; ?>
            
            <?php if ($showForm): ?>
                <!-- Form Gösterimi -->
                <div class="mt-8 max-w-2xl mx-auto">
                    <?php
                    // Form render fonksiyonunu yükle
                    $basePath = dirname(dirname(dirname(dirname(__DIR__))));
                    $formComponentPath = $basePath . '/app/views/frontend/components/form.php';
                    
                    if (file_exists($formComponentPath)) {
                        require_once $formComponentPath;
                    }
                    
                    // Form'u render et
                    if (function_exists('render_form_by_id')) {
                        echo '<div class="cta-form-wrapper bg-white/95 backdrop-blur-sm rounded-2xl p-6 lg:p-8 shadow-xl">';
                        echo render_form_by_id($selectedFormId);
                        echo '</div>';
                    } else {
                        echo '<p class="text-white/80 bg-white/10 p-4 rounded-lg">Form yüklenemedi. Lütfen form ID\'sini kontrol edin.</p>';
                    }
                    ?>
                </div>
            <?php else: ?>
                <!-- Butonlar -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="<?php echo esc_url($ctaButtonLink); ?>" 
                       style="background-color: <?php echo esc_attr($primaryButtonBg); ?>; color: <?php echo esc_attr($primaryButtonTextColor); ?>;"
                       class="px-8 py-4 rounded-lg font-semibold hover:opacity-90 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <?php echo esc_html($ctaButtonText); ?>
                    </a>
                    <?php if (!empty($ctaSecondaryButtonText)): ?>
                        <a href="<?php echo esc_url($ctaSecondaryButtonLink); ?>" 
                           style="background-color: <?php echo esc_attr($secondaryButtonBg); ?>; color: <?php echo esc_attr($secondaryButtonTextColor); ?>;"
                           class="px-8 py-4 border-2 border-white rounded-lg font-semibold hover:opacity-90 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            <?php echo esc_html($ctaSecondaryButtonText); ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
