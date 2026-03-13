<?php
/**
 * Çizgi Aks Gayrimenkul - İletişim sayfası şablonu
 * Sol: Sitedeki firma bilgileri (isim, e-posta, telefon, sosyal medya).
 * Sağ: [form slug="crm-lead-formu"] shortcode ile form.
 */

$themeLoader = $themeLoader ?? null;
$themeSettings = $themeLoader ? $themeLoader->getAllSettings() : [];
$primaryColor = $themeLoader ? $themeLoader->getColor('primary', '#bc1a1a') : '#bc1a1a';
$getSetting = function($key, $default = '', $group = null) use ($themeLoader, $themeSettings) {
    if ($themeLoader) {
        return $themeLoader->getSetting($key, $default, $group);
    }
    if ($group && isset($themeSettings[$group][$key]['value'])) {
        return $themeSettings[$group][$key]['value'];
    }
    return $default;
};

$customFields = $customFields ?? [];
$mapEmbed = $customFields['map_embed'] ?? '';
$formTitle = $customFields['form_title'] ?? __('Bize Mesaj Gönderin');
$formDescription = $customFields['form_description'] ?? '';

// Sol taraf: Sitedeki firma bilgileri (Site Ayarları > Firma / Sosyal Medya)
$companyName = function_exists('get_option') ? get_option('company_name', '') : '';
$companyEmail = function_exists('get_option') ? get_option('company_email', get_option('contact_email', get_option('admin_email', ''))) : '';
$companyPhone = function_exists('get_option') ? get_option('company_phone', '') : '';
$companyAddress = function_exists('get_option') ? get_option('company_address', '') : '';
$contactHours = $getSetting('working_hours', '09:00 - 18:00', 'header') ?: '09:00 - 18:00';
$socialFacebook = function_exists('get_option') ? get_option('social_facebook', '') : '';
$socialInstagram = function_exists('get_option') ? get_option('social_instagram', '') : '';
$socialTwitter = function_exists('get_option') ? get_option('social_twitter', '') : '';
$socialLinkedin = function_exists('get_option') ? get_option('social_linkedin', '') : '';
$socialYoutube = function_exists('get_option') ? get_option('social_youtube', '') : '';
$socialTiktok = function_exists('get_option') ? get_option('social_tiktok', '') : '';
$socialPinterest = function_exists('get_option') ? get_option('social_pinterest', '') : '';
$hasSocial = $socialFacebook || $socialInstagram || $socialTwitter || $socialLinkedin || $socialYoutube || $socialTiktok || $socialPinterest;

if (empty($mapEmbed)) {
    $mapEmbed = function_exists('get_option') ? get_option('google_maps_embed', '') : '';
}

$pageTitle = isset($page) ? ($page['title'] ?? __('İletişim')) : __('İletişim');
$pageExcerpt = isset($page) ? ($page['excerpt'] ?? '') : '';

$flashMessage = $_SESSION['contact_message'] ?? '';
$flashType = $_SESSION['contact_message_type'] ?? '';
if ($flashMessage) {
    unset($_SESSION['contact_message'], $_SESSION['contact_message_type']);
}
?>
<div class="cizgiaks-contact-page">
    <!-- Hero -->
    <section class="cizgiaks-contact-hero cizgiaks-contact-hero--pro">
        <div class="cizgiaks-contact-hero-bg"></div>
        <div class="cizgiaks-container cizgiaks-contact-hero-inner">
            <nav class="cizgiaks-contact-breadcrumb" aria-label="Breadcrumb">
                <a href="<?php echo esc_url(function_exists('site_url') ? site_url('/') : '/'); ?>"><?php echo esc_html(__('Ana Sayfa')); ?></a>
                <span class="cizgiaks-contact-breadcrumb-sep">/</span>
                <span><?php echo esc_html($pageTitle); ?></span>
            </nav>
            <h1 class="cizgiaks-contact-hero-title"><?php echo esc_html($pageTitle); ?></h1>
            <p class="cizgiaks-contact-hero-desc"><?php echo !empty($pageExcerpt) ? esc_html($pageExcerpt) : __('Sorularınız ve talepleriniz için bize ulaşın. En kısa sürede size dönüş yapacağız.'); ?></p>
            <div class="cizgiaks-contact-hero-badges">
                <span class="cizgiaks-contact-badge"><i class="fas fa-clock"></i> <?php echo esc_html(__('Hızlı yanıt')); ?></span>
                <span class="cizgiaks-contact-badge"><i class="fas fa-headset"></i> <?php echo esc_html(__('Profesyonel destek')); ?></span>
            </div>
        </div>
    </section>

    <div class="cizgiaks-container cizgiaks-contact-content">
        <?php if ($flashMessage): ?>
        <div class="cizgiaks-contact-alert cizgiaks-contact-alert--<?php echo $flashType === 'success' ? 'success' : 'error'; ?>">
            <?php if ($flashType === 'success'): ?><i class="fas fa-check-circle"></i><?php else: ?><i class="fas fa-exclamation-circle"></i><?php endif; ?>
            <?php echo esc_html($flashMessage); ?>
        </div>
        <?php endif; ?>

        <div class="cizgiaks-contact-grid">
            <!-- Sol: Sitedeki firma bilgileri (isim, e-posta, telefon, adres, sosyal medya) -->
            <div class="cizgiaks-contact-cards">
                <?php if ($companyName): ?>
                <div class="cizgiaks-contact-card cizgiaks-contact-card--pro">
                    <div class="cizgiaks-contact-card-icon"><i class="fas fa-building"></i></div>
                    <div class="cizgiaks-contact-card-body">
                        <h3><?php echo esc_html(__('Firma')); ?></h3>
                        <p><?php echo esc_html($companyName); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($companyEmail): ?>
                <div class="cizgiaks-contact-card cizgiaks-contact-card--pro">
                    <div class="cizgiaks-contact-card-icon"><i class="fas fa-envelope"></i></div>
                    <div class="cizgiaks-contact-card-body">
                        <h3><?php echo esc_html(__('E-posta')); ?></h3>
                        <a href="mailto:<?php echo esc_attr($companyEmail); ?>"><?php echo esc_html($companyEmail); ?></a>
                        <p class="cizgiaks-contact-card-meta"><?php echo esc_html(__('24 saat içinde yanıt')); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($companyPhone): ?>
                <div class="cizgiaks-contact-card cizgiaks-contact-card--pro">
                    <div class="cizgiaks-contact-card-icon"><i class="fas fa-phone-alt"></i></div>
                    <div class="cizgiaks-contact-card-body">
                        <h3><?php echo esc_html(__('Telefon')); ?></h3>
                        <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $companyPhone)); ?>"><?php echo esc_html($companyPhone); ?></a>
                        <?php if ($contactHours): ?>
                        <p class="cizgiaks-contact-card-meta"><i class="fas fa-business-time"></i> <?php echo esc_html($contactHours); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($companyAddress): ?>
                <div class="cizgiaks-contact-card cizgiaks-contact-card--pro">
                    <div class="cizgiaks-contact-card-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="cizgiaks-contact-card-body">
                        <h3><?php echo esc_html(__('Adres')); ?></h3>
                        <p><?php echo nl2br(esc_html($companyAddress)); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($hasSocial): ?>
                <div class="cizgiaks-contact-card cizgiaks-contact-card--pro">
                    <div class="cizgiaks-contact-card-icon"><i class="fas fa-share-alt"></i></div>
                    <div class="cizgiaks-contact-card-body">
                        <h3><?php echo esc_html(__('Sosyal Medya')); ?></h3>
                        <div class="cizgiaks-contact-social">
                            <?php if ($socialFacebook): ?><a href="<?php echo esc_url($socialFacebook); ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
                            <?php if ($socialInstagram): ?><a href="<?php echo esc_url($socialInstagram); ?>" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a><?php endif; ?>
                            <?php if ($socialTwitter): ?><a href="<?php echo esc_url($socialTwitter); ?>" target="_blank" rel="noopener" aria-label="X"><i class="fab fa-twitter"></i></a><?php endif; ?>
                            <?php if ($socialLinkedin): ?><a href="<?php echo esc_url($socialLinkedin); ?>" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
                            <?php if ($socialYoutube): ?><a href="<?php echo esc_url($socialYoutube); ?>" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a><?php endif; ?>
                            <?php if ($socialTiktok): ?><a href="<?php echo esc_url($socialTiktok); ?>" target="_blank" rel="noopener" aria-label="TikTok"><i class="fab fa-tiktok"></i></a><?php endif; ?>
                            <?php if ($socialPinterest): ?><a href="<?php echo esc_url($socialPinterest); ?>" target="_blank" rel="noopener" aria-label="Pinterest"><i class="fab fa-pinterest-p"></i></a><?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($companyName || $companyEmail || $companyPhone || $companyAddress || $hasSocial): ?>
                <div class="cizgiaks-contact-cta-box">
                    <p><strong><?php echo esc_html(__('Acil mi?')); ?></strong> <?php echo esc_html(__('Doğrudan arayın veya WhatsApp üzerinden yazın.')); ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sağ: [form slug="crm-lead-formu"] shortcode ile form -->
            <div class="cizgiaks-contact-form-wrap cizgiaks-contact-form-wrap--pro">
                <div class="cizgiaks-contact-form-header">
                    <h2><?php echo esc_html($formTitle); ?></h2>
                    <?php if ($formDescription): ?>
                    <p class="cizgiaks-contact-form-desc"><?php echo esc_html($formDescription); ?></p>
                    <?php else: ?>
                    <p class="cizgiaks-contact-form-desc"><?php echo esc_html(__('Formu doldurun, en kısa sürede sizinle iletişime geçelim.')); ?></p>
                    <?php endif; ?>
                </div>
                <?php
                if (function_exists('do_shortcode')) {
                    echo do_shortcode('[form slug="crm-lead-formu"]');
                } else {
                    echo '<!-- Form shortcode kullanılamıyor -->';
                }
                ?>
            </div>
        </div>

        <?php if (!empty(trim(strip_tags($mapEmbed)))): ?>
        <section id="harita" class="cizgiaks-contact-map-section">
            <h2 class="cizgiaks-contact-map-title"><?php echo esc_html(__('Bizi Ziyaret Edin')); ?></h2>
            <div class="cizgiaks-contact-map cizgiaks-contact-map--pro">
                <div class="cizgiaks-contact-map-inner">
                    <?php echo $mapEmbed; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </div>
</div>
