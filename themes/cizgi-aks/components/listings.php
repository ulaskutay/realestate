<?php
/**
 * Çizgi Aks - Anasayfa İlanlar Bölümü (slider altı)
 * Sol: 4 sütun ilan grid + emlakçı bilgisi | Sağ: bloklar (Talep, vb.)
 */
$section = $section ?? [];
$themeLoader = $themeLoader ?? ThemeLoader::getInstance();
$settings = $section['settings'] ?? [];
if (is_string($settings)) {
    $settings = json_decode($settings, true) ?: [];
}

$sectionTitle = !empty($settings['title']) ? $settings['title'] : __($themeLoader->getSetting('listings_title', 'Anasayfa Vitrin', 'listings'));
$sectionSubtitle = !empty($settings['subtitle']) ? $settings['subtitle'] : __($themeLoader->getSetting('listings_subtitle', 'Sizin için seçtiğimiz vitrin ilanları', 'listings'));
$limit = isset($settings['limit']) ? (int) $settings['limit'] : 8;

$listings = [];
$rootPath = dirname(dirname(dirname(__DIR__)));
$modelPath = $rootPath . '/modules/realestate-listings/Model.php';
if (file_exists($modelPath)) {
    if (!class_exists('RealEstateListingsModel')) {
        require_once $modelPath;
    }
    if (class_exists('RealEstateListingsModel')) {
        try {
            $model = new RealEstateListingsModel();
            $listings = $model->getFeatured($limit);
            if (empty($listings) || count($listings) < 2) {
                $listings = $model->getPublished('', '', '', $limit, 0, '', null);
            }
        } catch (Exception $e) {
            error_log('Home listings component: ' . $e->getMessage());
        }
    }
}
if (!is_array($listings)) {
    $listings = [];
}

$propertyTypeLabels = [
    'house' => __('Müstakil Ev'),
    'apartment' => __('Daire'),
    'villa' => __('Villa'),
    'commercial' => __('Ticari'),
    'land' => __('Arsa')
];
$ilanBase = function_exists('localized_url') ? rtrim(localized_url('/ilan'), '/') : (function_exists('site_url') ? rtrim(site_url('/ilan'), '/') : '/ilan');
$listingsPageUrl = function_exists('localized_url') ? localized_url('/ilanlar') : (function_exists('site_url') ? site_url('/ilanlar') : '/ilanlar');
$haritaIframeUrl = function_exists('localized_url') ? localized_url('/harita-ilanlar') : (function_exists('site_url') ? rtrim(site_url('/harita-ilanlar'), '/') : '/harita-ilanlar');
$danismanBase = function_exists('localized_url') ? rtrim(localized_url('/danismanlar'), '/') : (function_exists('site_url') ? rtrim(site_url('/danismanlar'), '/') : '/danismanlar');
$talepLink = $themeLoader->getSetting('talep_link', '/contact', 'header');
$talepLinkUrl = function_exists('localized_url') ? localized_url($talepLink) : $talepLink;
$primaryColor = $themeLoader->getColor('primary', '#bc1a1a');
$unified = !empty($cizgiaks_unified_home);
if (!$unified) {
?>
<section class="cizgiaks-home-listings">
    <div class="cizgiaks-container">
<?php } ?>
        <header class="cizgiaks-home-listings-header">
            <h2 class="cizgiaks-home-listings-title"><?php echo esc_html($sectionTitle); ?></h2>
            <p class="cizgiaks-home-listings-subtitle"><?php echo esc_html($sectionSubtitle); ?></p>
        </header>
        <?php if (!$unified): ?>
        <div class="cizgiaks-home-listings-layout">
            <div class="cizgiaks-home-listings-main">
        <?php endif; ?>
                <?php if (!empty($listings)): ?>
                    <div class="cizgiaks-listings-grid cizgiaks-listings-grid--3">
                        <?php foreach ($listings as $listing):
                            $slug = $listing['slug'] ?? $listing['id'];
                            $detailUrl = $ilanBase . '/' . $slug;
                            $listingStatus = $listing['listing_status'] ?? 'sale';
                            $statusLabel = $listingStatus === 'rent' ? __('Kiralık') : __('Satılık');
                            $propertyType = $listing['property_type'] ?? 'house';
                            $propertyTypeLabel = $propertyTypeLabels[$propertyType] ?? $propertyType;
                            $price = function_exists('realestate_format_price') ? realestate_format_price($listing['price']) : '₺' . number_format((float)($listing['price'] ?? 0), 0, ',', '.');
                            $roomDisplay = '';
                            $oda = (int)($listing['rooms'] ?? 0);
                            $salon = (int)($listing['living_rooms'] ?? 0);
                            if ($oda > 0 || $salon > 0) {
                                $roomDisplay = $oda . '+' . $salon;
                            }
                            $metaLine = $roomDisplay ? $roomDisplay . ' | ' : '';
                            $metaLine .= $statusLabel . ' ' . $propertyTypeLabel;
                            $listingNo = !empty($listing['listing_no']) ? $listing['listing_no'] : $listing['id'];
                            $img = !empty($listing['featured_image']) ? $listing['featured_image'] : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800';
                            $realtorName = trim(($listing['realtor_first_name'] ?? '') . ' ' . ($listing['realtor_last_name'] ?? ''));
                            $realtorSlug = $listing['realtor_slug'] ?? '';
                            $realtorUrl = $realtorSlug ? $danismanBase . '/' . $realtorSlug : $danismanBase;
                            $realtorPhoto = $listing['realtor_photo'] ?? '';
                        ?>
                            <article class="cizgiaks-listing-card cizgiaks-listing-card--vitrin">
                                <div class="cizgiaks-listing-card-image">
                                    <a href="<?php echo esc_url($detailUrl); ?>">
                                        <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($listing['title']); ?>" loading="lazy">
                                    </a>
                                    <div class="cizgiaks-listing-ribbons">
                                        <span class="cizgiaks-ribbon cizgiaks-ribbon-<?php echo $listingStatus === 'rent' ? 'kiralik' : 'satilik'; ?>"><?php echo esc_html($statusLabel); ?></span>
                                        <?php if (!empty($listing['label_yeni'])): ?><span class="cizgiaks-ribbon cizgiaks-ribbon-yeni"><?php echo esc_html(__('Yeni')); ?></span><?php endif; ?>
                                        <?php if (!empty($listing['label_firsat'])): ?><span class="cizgiaks-ribbon cizgiaks-ribbon-firsat"><?php echo esc_html(__('Fırsat')); ?></span><?php endif; ?>
                                        <?php if (!empty($listing['label_yatirimlik'])): ?><span class="cizgiaks-ribbon cizgiaks-ribbon-yatirimlik"><?php echo esc_html(__('Yatırımlık')); ?></span><?php endif; ?>
                                        <?php if (!empty($listing['label_acil'])): ?><span class="cizgiaks-ribbon cizgiaks-ribbon-acil"><?php echo esc_html(__('Acil')); ?></span><?php endif; ?>
                                    </div>
                                </div>
                                <div class="cizgiaks-listing-card-body">
                                    <div class="cizgiaks-listing-price"><?php echo esc_html($price); ?></div>
                                    <h3 class="cizgiaks-listing-title">
                                        <a href="<?php echo esc_url($detailUrl); ?>"><?php echo esc_html($listing['title']); ?></a>
                                    </h3>
                                    <div class="cizgiaks-listing-location"><?php echo esc_html($listing['location'] ?? __('Konum belirtilmemiş')); ?></div>
                                    <div class="cizgiaks-listing-no"><?php echo esc_html(__('İlan No')); ?> : <?php echo esc_html($listingNo); ?></div>
                                    <div class="cizgiaks-listing-meta"><?php echo esc_html($metaLine); ?></div>
                                </div>
                                <?php if ($realtorName || $realtorPhoto): ?>
                                <div class="cizgiaks-listing-card-footer">
                                    <a href="<?php echo esc_url($realtorUrl); ?>" class="cizgiaks-listing-realtor">
                                        <?php if ($realtorPhoto): ?>
                                            <img src="<?php echo esc_url($realtorPhoto); ?>" alt="" class="cizgiaks-listing-realtor-photo" width="32" height="32">
                                        <?php else: ?>
                                            <span class="cizgiaks-listing-realtor-avatar"><i class="fas fa-user"></i></span>
                                        <?php endif; ?>
                                        <span class="cizgiaks-listing-realtor-name"><?php echo esc_html($realtorName ?: __('Danışman')); ?></span>
                                    </a>
                                </div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <p class="cizgiaks-home-listings-more">
                        <a href="<?php echo esc_url($listingsPageUrl); ?>" class="cizgiaks-btn cizgiaks-btn-primary"><?php echo esc_html(__('Tüm İlanlar')); ?></a>
                    </p>
                <?php else: ?>
                    <p class="cizgiaks-home-listings-empty"><?php echo esc_html(__('Henüz vitrin ilanı bulunmuyor.')); ?></p>
                <?php endif; ?>
            <?php if ($unified) { return; } ?>
            <?php if (!$unified): ?>
            </div>
            <aside class="cizgiaks-home-listings-sidebar">
                <div class="cizgiaks-sidebar-block cizgiaks-sidebar-block--talep">
                    <h3 class="cizgiaks-sidebar-block-title"><?php echo esc_html(__('Satılık ve Kiralık Gayrimenkullerinizi Değerlendirelim')); ?></h3>
                    <p class="cizgiaks-sidebar-block-text"><?php echo esc_html(__('Talep formunu doldurun, sizinle iletişime geçelim.')); ?></p>
                    <a href="<?php echo esc_url($talepLinkUrl); ?>" class="cizgiaks-btn cizgiaks-btn-primary cizgiaks-btn--block" style="background-color:<?php echo esc_attr($primaryColor); ?>"><?php echo esc_html(__('EMLAK TALEP FORMU')); ?></a>
                </div>
                <div class="cizgiaks-sidebar-block cizgiaks-sidebar-block--map">
                    <div class="cizgiaks-sidebar-map-mini">
                        <iframe src="<?php echo esc_url($haritaIframeUrl); ?>" class="cizgiaks-sidebar-map-mini-iframe" title="<?php echo esc_attr(__('İlanlar haritası')); ?>"></iframe>
                        <a href="<?php echo esc_url($haritaIframeUrl); ?>" class="cizgiaks-sidebar-map-mini-overlay" aria-label="<?php echo esc_attr(__('Haritada ara - harita sayfasına git')); ?>"></a>
                    </div>
                    <a href="<?php echo esc_url($haritaIframeUrl); ?>" class="cizgiaks-btn cizgiaks-btn-primary cizgiaks-btn--block cizgiaks-sidebar-map-ara-btn" style="background-color:<?php echo esc_attr($primaryColor); ?>">
                        <span aria-hidden="true">🗺️</span> <?php echo esc_html(__('Haritada Ara')); ?>
                    </a>
                </div>
            </aside>
        </div>
    </div>
</section>
<?php endif; ?>
