<?php
/**
 * Çizgi Aks Gayrimenkul - Hero
 * Sol: İlan slider (ilanlardan otomatik), Sağ: İlan Ara formu + Biz Sizi Arayalım
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

// İlan slider verisi: özel modül modules/realestate-listings Model kullan
$sliderListings = [];
$rootPath = dirname(dirname(dirname(__DIR__)));
$modelPath = $rootPath . '/modules/realestate-listings/Model.php';
if (file_exists($modelPath)) {
    if (!class_exists('RealEstateListingsModel')) {
        require_once $modelPath;
    }
    if (class_exists('RealEstateListingsModel')) {
        try {
            $model = new RealEstateListingsModel();
            $sliderListings = $model->getFeatured(8);
            if (empty($sliderListings) || count($sliderListings) < 2) {
                $sliderListings = $model->getPublished('', '', '', 8, 0, '', null);
            }
        } catch (Exception $e) {
            error_log('Hero slider listings: ' . $e->getMessage());
        }
    }
}

$ilanBaseUrl = function_exists('localized_url') ? localized_url('/ilanlar') : (function_exists('site_url') ? site_url('/ilanlar') : '/ilanlar');
$ilanDetailBase = function_exists('localized_url') ? rtrim(localized_url('/ilan'), '/') : (function_exists('site_url') ? rtrim(site_url('/ilan'), '/') : '/ilan');

$unified = !empty($cizgiaks_unified_home);
if ($unified) {
    // Tek sağ blok layout: sadece slider (sol sütunda)
?>
        <div class="cizgiaks-hero-slider-wrap cizgiaks-hero-slider-wrap--unified">
            <div class="cizgiaks-hero-slider" id="cizgiaks-hero-slider">
                <?php if (!empty($sliderListings)): ?>
                    <?php foreach ($sliderListings as $idx => $item): ?>
                        <?php
                        $img = !empty($item['featured_image']) ? $item['featured_image'] : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800';
                        $price = function_exists('realestate_format_price') ? realestate_format_price($item['price']) : '₺' . number_format((float)$item['price'], 0, '.', '.');
                        $slug = $item['slug'] ?? '';
                        $detailUrl = $ilanDetailBase . '/' . $slug;
                        ?>
                        <div class="cizgiaks-hero-slide <?php echo $idx === 0 ? 'active' : ''; ?>" data-index="<?php echo (int)$idx; ?>">
                            <a href="<?php echo esc_url($detailUrl); ?>" class="cizgiaks-hero-slide-link">
                                <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($item['title'] ?? ''); ?>">
                                <div class="cizgiaks-hero-slide-overlay">
                                    <span class="cizgiaks-hero-slide-price"><?php echo esc_html($price); ?></span>
                                    <span class="cizgiaks-hero-slide-title"><?php echo esc_html($item['title'] ?? ''); ?></span>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="cizgiaks-hero-slide active">
                        <div class="cizgiaks-hero-slide-link">
                            <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800" alt="">
                            <div class="cizgiaks-hero-slide-overlay">
                                <span class="cizgiaks-hero-slide-title"><?php echo esc_html(__('İlanlar yükleniyor...')); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (count($sliderListings) > 1): ?>
            <button type="button" class="cizgiaks-hero-slider-prev" aria-label="<?php echo esc_attr(__('Önceki')); ?>"><i class="fas fa-chevron-left"></i></button>
            <button type="button" class="cizgiaks-hero-slider-next" aria-label="<?php echo esc_attr(__('Sonraki')); ?>"><i class="fas fa-chevron-right"></i></button>
            <div class="cizgiaks-hero-slider-dots" id="cizgiaks-hero-dots"></div>
            <?php endif; ?>
        </div>
<?php
    return;
}
?>
<section class="cizgiaks-hero">
    <div class="cizgiaks-hero-grid">
        <div class="cizgiaks-hero-slider-wrap">
            <div class="cizgiaks-hero-slider" id="cizgiaks-hero-slider">
                <?php if (!empty($sliderListings)): ?>
                    <?php foreach ($sliderListings as $idx => $item): ?>
                        <?php
                        $img = !empty($item['featured_image']) ? $item['featured_image'] : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800';
                        $price = function_exists('realestate_format_price') ? realestate_format_price($item['price']) : '₺' . number_format((float)$item['price'], 0, '.', '.');
                        $slug = $item['slug'] ?? '';
                        $detailUrl = $ilanDetailBase . '/' . $slug;
                        ?>
                        <div class="cizgiaks-hero-slide <?php echo $idx === 0 ? 'active' : ''; ?>" data-index="<?php echo (int)$idx; ?>">
                            <a href="<?php echo esc_url($detailUrl); ?>" class="cizgiaks-hero-slide-link">
                                <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($item['title'] ?? ''); ?>">
                                <div class="cizgiaks-hero-slide-overlay">
                                    <span class="cizgiaks-hero-slide-price"><?php echo esc_html($price); ?></span>
                                    <span class="cizgiaks-hero-slide-title"><?php echo esc_html($item['title'] ?? ''); ?></span>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="cizgiaks-hero-slide active">
                        <div class="cizgiaks-hero-slide-link">
                            <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800" alt="">
                            <div class="cizgiaks-hero-slide-overlay">
                                <span class="cizgiaks-hero-slide-title"><?php echo esc_html(__('İlanlar yükleniyor...')); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (count($sliderListings) > 1): ?>
            <button type="button" class="cizgiaks-hero-slider-prev" aria-label="<?php echo esc_attr(__('Önceki')); ?>"><i class="fas fa-chevron-left"></i></button>
            <button type="button" class="cizgiaks-hero-slider-next" aria-label="<?php echo esc_attr(__('Sonraki')); ?>"><i class="fas fa-chevron-right"></i></button>
            <div class="cizgiaks-hero-slider-dots" id="cizgiaks-hero-dots"></div>
            <?php endif; ?>
        </div>
        <div class="cizgiaks-hero-right">
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
                <a href="<?php echo esc_url($themeLoader->getSetting('talep_link', '/contact', 'header')); ?>" class="cizgiaks-hero-callback-btn" style="background-color: <?php echo esc_attr($primaryColor); ?>;">
                    <?php echo esc_html(__('Talep Gönder')); ?>
                </a>
            </div>
        </div>
    </div>
</section>
