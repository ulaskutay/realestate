<?php
/**
 * Real Estate Theme - Call to Action Section
 */

$section = $section ?? [];
$settings = $section['settings'] ?? [];

$ctaTitle = !empty($section['title']) ? $section['title'] : __('Ready to Find Your Dream Home?');
$ctaSubtitle = !empty($section['subtitle']) ? $section['subtitle'] : __('Let us help you find the perfect property. Contact us today for a free consultation.');
$ctaButtonText = !empty($settings['button_text']) ? $settings['button_text'] : __('Başla');
$ctaButtonLinkRaw = !empty($settings['button_link']) ? $settings['button_link'] :'iletisim';
$ctaButtonLink = function_exists('localized_url') ? localized_url($ctaButtonLinkRaw) : site_url($ctaButtonLinkRaw);
$ctaSecondaryButtonText = !empty($settings['secondary_button_text']) ? $settings['secondary_button_text'] : __('İlanları İncele');
$ctaSecondaryButtonLinkRaw = !empty($settings['secondary_button_link']) ? $settings['secondary_button_link'] : 'ilanlar';
$ctaSecondaryButtonLink = function_exists('localized_url') ? localized_url($ctaSecondaryButtonLinkRaw) : site_url($ctaSecondaryButtonLinkRaw);

// Form seçimi
$selectedFormId = !empty($settings['form_id']) ? intval($settings['form_id']) : null;
$showForm = !empty($selectedFormId);

// Özelleştirme ayarları
$bgGradientFrom = !empty($settings['bg_gradient_from']) ? $settings['bg_gradient_from'] : '#1e40af';
$bgGradientVia = !empty($settings['bg_gradient_via']) ? $settings['bg_gradient_via'] : '#2563eb';
$bgGradientTo = !empty($settings['bg_gradient_to']) ? $settings['bg_gradient_to'] : '#1e3a8a';
$paddingY = !empty($settings['padding_y']) ? $settings['padding_y'] : 'py-20 lg:py-32';
$cardBgOpacity = !empty($settings['card_bg_opacity']) ? floatval($settings['card_bg_opacity']) : 0.1;
$cardBlur = !empty($settings['card_blur']) ? $settings['card_blur'] : 'md';
$showTrustIndicators = isset($settings['show_trust_indicators']) ? (bool)$settings['show_trust_indicators'] : true;
$trustIndicators = !empty($settings['trust_indicators']) && is_array($settings['trust_indicators']) ? $settings['trust_indicators'] : [
    ['text' => 'Ücretsiz Danışmanlık', 'icon' => 'check'],
    ['text' => 'Profesyonel Hizmet', 'icon' => 'check'],
    ['text' => '7/24 Destek', 'icon' => 'check']
];

// Buton renkleri - Tema renk paletinden al
$primaryButtonBg = !empty($settings['primary_button_bg']) ? $settings['primary_button_bg'] : '#ffffff';
$primaryButtonTextColor = !empty($settings['primary_button_text']) ? $settings['primary_button_text'] : '#1e40af';
$secondaryButtonBg = !empty($settings['secondary_button_bg']) ? $settings['secondary_button_bg'] : 'transparent';
$secondaryButtonTextColor = !empty($settings['secondary_button_text_color']) ? $settings['secondary_button_text_color'] : '#ffffff';

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

// ThemeLoader'dan tema renklerini al (sadece özelleştirme yapılmamışsa)
if (class_exists('ThemeLoader') && empty($settings['bg_gradient_from'])) {
    $themeLoaderInstance = ThemeLoader::getInstance();
    $primaryColor = $themeLoaderInstance->getColor('primary', '#1e40af');
    $secondaryColor = $themeLoaderInstance->getColor('secondary', '#1e293b');
    
    if (empty($settings['primary_button_bg'])) {
        $primaryButtonBg = '#ffffff';
        $primaryButtonTextColor = $primaryColor;
    }
    if (empty($settings['secondary_button_bg'])) {
        $secondaryButtonBg = 'transparent';
        $secondaryButtonTextColor = '#ffffff';
    }
}
?>

<section class="relative <?php echo esc_attr($paddingY); ?> overflow-hidden" style="background: linear-gradient(to bottom right, <?php echo esc_attr($bgGradientFrom); ?>, <?php echo esc_attr($bgGradientVia); ?>, <?php echo esc_attr($bgGradientTo); ?>);">
    <!-- Animated Background Pattern -->
    <div class="absolute inset-0 opacity-5">
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\"100\" height=\"100\" viewBox=\"0 0 100 100\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cpath d=\"M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z\" fill=\"%23ffffff\" fill-opacity=\"1\" fill-rule=\"evenodd\"/%3E%3C/svg%3E');"></div>
    </div>
    
    <!-- Decorative Blobs -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
    <div class="absolute top-1/2 left-1/2 w-96 h-96 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-4xl mx-auto">
            <!-- Content Card with Glass Effect -->
            <div class="rounded-3xl p-8 sm:p-12 lg:p-16 shadow-2xl border border-white/20" style="background: rgba(255, 255, 255, <?php echo esc_attr($cardBgOpacity); ?>); backdrop-filter: blur(<?php echo esc_attr($cardBlur); ?>);">
                <div class="text-center">
                    <!-- Title with Better Typography -->
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-extrabold text-white mb-6 leading-tight">
                        <?php echo esc_html($ctaTitle); ?>
                    </h2>
                    
                    <?php if (!empty($ctaSubtitle)): ?>
                        <p class="text-lg sm:text-xl lg:text-2xl text-white/95 mb-10 max-w-2xl mx-auto leading-relaxed">
                            <?php echo esc_html($ctaSubtitle); ?>
                        </p>
                    <?php endif; ?>
                    
                    <?php if ($showForm): ?>
                        <!-- Form Display -->
                        <div class="mt-10 max-w-2xl mx-auto">
                            <?php
                            // Load form render function
                            $basePath = dirname(dirname(dirname(dirname(__DIR__))));
                            $formComponentPath = $basePath . '/app/views/frontend/components/form.php';
                            
                            if (file_exists($formComponentPath)) {
                                require_once $formComponentPath;
                            }
                            
                            // Render form
                            if (function_exists('render_form_by_id')) {
                                echo '<div class="cta-form-wrapper bg-white rounded-2xl p-6 lg:p-8 shadow-2xl">';
                                echo render_form_by_id($selectedFormId);
                                echo '</div>';
                            } else {
                                echo '<p class="text-white/90 bg-white/10 p-4 rounded-xl border border-white/20">Form yüklenemedi. Lütfen form ID\'sini kontrol edin.</p>';
                            }
                            ?>
                        </div>
                    <?php else: ?>
                        <!-- Modern Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 justify-center items-center">
                            <a href="<?php echo esc_url($ctaButtonLink); ?>" 
                               class="group relative inline-flex items-center justify-center px-8 sm:px-10 py-4 sm:py-5 text-base sm:text-lg font-bold rounded-2xl overflow-hidden transition-all duration-300 hover:scale-105 hover:shadow-2xl min-w-[200px] backdrop-blur-sm border-2 border-white/40"
                               style="background-color: <?php echo esc_attr($primaryButtonBg); ?>; color: <?php echo esc_attr($primaryButtonTextColor); ?>;">
                                <span class="relative z-10 flex items-center gap-2">
                                    <?php echo esc_html($ctaButtonText); ?>
                                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                </span>
                                <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 transform -skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                            </a>
                            
                            <?php if (!empty($ctaSecondaryButtonText)): ?>
                                <a href="<?php echo esc_url($ctaSecondaryButtonLink); ?>" 
                                   class="group inline-flex items-center justify-center px-8 sm:px-10 py-4 sm:py-5 text-base sm:text-lg font-bold rounded-2xl border-3 border-white/60 hover:bg-white transition-all duration-300 hover:scale-105 hover:shadow-2xl min-w-[200px]"
                                   style="background-color: <?php echo esc_attr($secondaryButtonBg); ?>; color: <?php echo esc_attr($secondaryButtonTextColor); ?>; border-color: rgba(255, 255, 255, 0.6);">
                                    <span class="flex items-center gap-2">
                                        <?php echo esc_html($ctaSecondaryButtonText); ?>
                                        <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </span>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Trust Indicators (Optional) -->
            <?php if ($showTrustIndicators && !empty($trustIndicators)): ?>
                <div class="mt-12 flex flex-wrap justify-center items-center gap-8 text-white/80 text-sm">
                    <?php foreach ($trustIndicators as $indicator): ?>
                        <?php if (!empty($indicator['text'])): ?>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><?php echo esc_html($indicator['text']); ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}

.border-3 {
    border-width: 3px;
}

/* CTA Form Styles */
.cta-form-wrapper {
    color: #1e293b;
}

.cta-form-wrapper .cms-form {
    margin: 0;
}

.cta-form-wrapper .form-field {
    margin-bottom: 1.5rem;
}

.cta-form-wrapper .field-label {
    display: block;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    text-align: left;
}

.cta-form-wrapper .field-input input,
.cta-form-wrapper .field-input textarea,
.cta-form-wrapper .field-input select {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    transition: all 0.2s;
    background-color: #ffffff;
    color: #1e293b;
}

.cta-form-wrapper .field-input input:focus,
.cta-form-wrapper .field-input textarea:focus,
.cta-form-wrapper .field-input select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.cta-form-wrapper .submit-button {
    width: 100%;
    padding: 0.875rem 1.5rem;
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 1rem;
    color: #ffffff;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.cta-form-wrapper .submit-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.cta-form-wrapper .submit-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.cta-form-wrapper .required-mark {
    color: #ef4444;
    margin-left: 0.25rem;
}

.cta-form-wrapper .field-error input,
.cta-form-wrapper .field-error textarea,
.cta-form-wrapper .field-error select {
    border-color: #ef4444;
}

.cta-form-wrapper .field-error-message {
    color: #ef4444;
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

.cta-form-wrapper .form-success {
    text-align: center;
    padding: 2rem;
}

.cta-form-wrapper .success-icon {
    width: 48px;
    height: 48px;
    margin: 0 auto 1rem;
    color: #10b981;
}

.cta-form-wrapper .success-message {
    font-size: 1rem;
    color: #1e293b;
    font-weight: 500;
}

.cta-form-wrapper .form-error-message {
    background-color: #fee2e2;
    color: #991b1b;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    margin-top: 1rem;
    font-size: 0.875rem;
}
</style>
