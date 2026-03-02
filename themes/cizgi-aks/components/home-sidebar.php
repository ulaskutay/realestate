<?php
/**
 * Çizgi Aks - Ana sayfa tek sağ blok (İlan Ara + Biz Sizi Arayalım + Talep + Harita)
 * Hero ile vitrin section aynı sağ sütunu paylaşır.
 */
$section = $section ?? [];
$themeLoader = $themeLoader ?? ThemeLoader::getInstance();
$settings = $section['settings'] ?? [];
if (is_string($settings)) {
    $settings = json_decode($settings, true) ?: [];
}
$searchTitle = !empty($settings['search_title']) ? $settings['search_title'] : __($themeLoader->getSetting('search_title', 'İlan Ara', 'hero'));
$callbackTitle = !empty($settings['callback_title']) ? $settings['callback_title'] : __($themeLoader->getSetting('callback_title', 'Biz Sizi Arayalım', 'hero'));
$callbackText = !empty($settings['callback_text']) ? $settings['callback_text'] : __($themeLoader->getSetting('callback_text', 'İhtiyacınıza uygun ilanları sizin için araştıralım.', 'hero'));
$primaryColor = $themeLoader->getColor('primary', '#bc1a1a');
$ilanBaseUrl = function_exists('localized_url') ? localized_url('/ilanlar') : (function_exists('site_url') ? site_url('/ilanlar') : '/ilanlar');
$talepLink = $themeLoader->getSetting('talep_link', '/contact', 'header');
$talepLinkUrl = function_exists('localized_url') ? localized_url($talepLink) : $talepLink;
$listingsPageUrl = function_exists('localized_url') ? localized_url('/ilanlar') : (function_exists('site_url') ? site_url('/ilanlar') : '/ilanlar');
// Embed: iframe'de sadece harita (header/footer yok); tam sayfa: buton/overlay tıklanınca
$haritaPageUrl = function_exists('localized_url') ? localized_url('/harita-ilanlar') : (function_exists('site_url') ? rtrim(site_url('/harita-ilanlar'), '/') : '/harita-ilanlar');
$haritaEmbedUrl = function_exists('localized_url') ? localized_url('/harita-ilanlar/embed') : (function_exists('site_url') ? rtrim(site_url('/harita-ilanlar/embed'), '/') : '/harita-ilanlar/embed');
?>
<aside class="cizgiaks-home-unified-sidebar">
    <div class="cizgiaks-home-sidebar-inner">
        <div class="cizgiaks-hero-search-card">
            <h2 class="cizgiaks-hero-search-title">
                <i class="fas fa-search" style="color: <?php echo esc_attr($primaryColor); ?>;"></i>
                <?php echo esc_html($searchTitle); ?>
            </h2>
            <form action="<?php echo esc_url($ilanBaseUrl); ?>" method="get" class="cizgiaks-hero-search-form">
                <div class="cizgiaks-hero-form-row">
                    <label class="cizgiaks-sr-only" for="hero-status"><?php echo esc_html(__('İlan Tipi')); ?></label>
                    <select name="status" id="hero-status" class="cizgiaks-hero-select">
                        <option value=""><?php echo esc_html(__('Seçiniz')); ?></option>
                        <option value="sale"><?php echo esc_html(__('Satılık')); ?></option>
                        <option value="rent"><?php echo esc_html(__('Kiralık')); ?></option>
                    </select>
                </div>
                <div class="cizgiaks-hero-form-row">
                    <label class="cizgiaks-sr-only" for="hero-type"><?php echo esc_html(__('İlan Türü')); ?></label>
                    <select name="type" id="hero-type" class="cizgiaks-hero-select">
                        <option value=""><?php echo esc_html(__('Seçiniz')); ?></option>
                        <option value="apartment"><?php echo esc_html(__('Daire')); ?></option>
                        <option value="house"><?php echo esc_html(__('Müstakil Ev')); ?></option>
                        <option value="villa"><?php echo esc_html(__('Villa')); ?></option>
                        <option value="commercial"><?php echo esc_html(__('Ticari')); ?></option>
                        <option value="land"><?php echo esc_html(__('Arsa')); ?></option>
                    </select>
                </div>
                <div class="cizgiaks-hero-form-row">
                    <label class="cizgiaks-sr-only" for="hero-location"><?php echo esc_html(__('Konum')); ?></label>
                    <input type="text" name="location" id="hero-location" class="cizgiaks-hero-input" placeholder="<?php echo esc_attr(__('Şehir / İlçe / Mahalle')); ?>">
                </div>
                <div class="cizgiaks-hero-form-row cizgiaks-hero-details-toggle-wrap">
                    <button type="button" class="cizgiaks-hero-details-toggle" id="hero-details-toggle" aria-expanded="false">
                        <?php echo esc_html(__('Diğer Ayrıntılar')); ?> <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
                <div class="cizgiaks-hero-extra-details" id="hero-extra-details" hidden>
                    <div class="cizgiaks-hero-form-row">
                        <label class="cizgiaks-sr-only" for="hero-min-area"><?php echo esc_html(__('Min m²')); ?></label>
                        <input type="number" name="min_area" id="hero-min-area" class="cizgiaks-hero-input" placeholder="<?php echo esc_attr(__('Min m²')); ?>" min="0" step="1">
                    </div>
                    <div class="cizgiaks-hero-form-row">
                        <label class="cizgiaks-sr-only" for="hero-max-area"><?php echo esc_html(__('Max m²')); ?></label>
                        <input type="number" name="max_area" id="hero-max-area" class="cizgiaks-hero-input" placeholder="<?php echo esc_attr(__('Max m²')); ?>" min="0" step="1">
                    </div>
                    <div class="cizgiaks-hero-form-row">
                        <label class="cizgiaks-sr-only" for="hero-min-price"><?php echo esc_html(__('Min Fiyat')); ?></label>
                        <input type="number" name="min_price" id="hero-min-price" class="cizgiaks-hero-input" placeholder="<?php echo esc_attr(__('Min Fiyat')); ?>" min="0">
                    </div>
                    <div class="cizgiaks-hero-form-row">
                        <label class="cizgiaks-sr-only" for="hero-max-price"><?php echo esc_html(__('Max Fiyat')); ?></label>
                        <input type="number" name="max_price" id="hero-max-price" class="cizgiaks-hero-input" placeholder="<?php echo esc_attr(__('Max Fiyat')); ?>" min="0">
                    </div>
                </div>
                <button type="submit" class="cizgiaks-hero-search-btn" style="background-color: <?php echo esc_attr($primaryColor); ?>;">
                    <i class="fas fa-search"></i> <?php echo esc_html(__('Ara')); ?>
                </button>
            </form>
        </div>
        <div class="cizgiaks-hero-callback">
            <h3 class="cizgiaks-hero-callback-title"><?php echo esc_html($callbackTitle); ?></h3>
            <p class="cizgiaks-hero-callback-text"><?php echo esc_html($callbackText); ?></p>
            <a href="<?php echo esc_url($talepLinkUrl); ?>" class="cizgiaks-hero-callback-btn" style="background-color: <?php echo esc_attr($primaryColor); ?>;">
                <?php echo esc_html(__('Talep Gönder')); ?>
            </a>
        </div>
        <div class="cizgiaks-sidebar-block cizgiaks-sidebar-block--talep">
            <h3 class="cizgiaks-sidebar-block-title"><?php echo esc_html(__('Satılık ve Kiralık Gayrimenkullerinizi Değerlendirelim')); ?></h3>
            <p class="cizgiaks-sidebar-block-text"><?php echo esc_html(__('Talep formunu doldurun, sizinle iletişime geçelim.')); ?></p>
            <a href="<?php echo esc_url($talepLinkUrl); ?>" class="cizgiaks-btn cizgiaks-btn-primary cizgiaks-btn--block" style="background-color:<?php echo esc_attr($primaryColor); ?>"><?php echo esc_html(__('EMLAK TALEP FORMU')); ?></a>
        </div>
        <div class="cizgiaks-sidebar-block cizgiaks-sidebar-block--map">
            <div class="cizgiaks-sidebar-map-mini">
                <iframe src="<?php echo esc_url($haritaEmbedUrl); ?>" class="cizgiaks-sidebar-map-mini-iframe" title="<?php echo esc_attr(__('İlanlar haritası')); ?>"></iframe>
                <a href="<?php echo esc_url($haritaPageUrl); ?>" class="cizgiaks-sidebar-map-mini-overlay" aria-label="<?php echo esc_attr(__('Haritada ara - harita sayfasına git')); ?>"></a>
            </div>
            <a href="<?php echo esc_url($haritaPageUrl); ?>" class="cizgiaks-btn cizgiaks-btn-primary cizgiaks-btn--block cizgiaks-sidebar-map-ara-btn" style="background-color:<?php echo esc_attr($primaryColor); ?>">
                <span aria-hidden="true">🗺️</span> <?php echo esc_html(__('Haritada Ara')); ?>
            </a>
        </div>
    </div>
</aside>
