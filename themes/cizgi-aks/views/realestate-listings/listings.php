<?php
/**
 * Çizgi Aks - İlanlar listesi sayfası (tema override)
 * Aynı veri: $listings, $filters, $title
 */
get_header([
    'title' => (isset($title) ? $title : __('Emlak İlanları')) . ' - ' . get_option('site_name', ''),
    'meta_description' => __('Aradığınız emlağı kolayca bulun. Daireler, villalar, müstakil evler ve daha fazlası.')
]);

$propertyTypeLabels = [
    'house' => __('Müstakil Ev'),
    'apartment' => __('Daire'),
    'villa' => __('Villa'),
    'commercial' => __('Ticari'),
    'land' => __('Arsa')
];
$statusLabels = ['sale' => __('Satılık'), 'rent' => __('Kiralık')];
if (!function_exists('selected')) {
    function selected($value1, $value2) { return ($value1 == $value2) ? 'selected' : ''; }
}

$listings = $listings ?? [];
$filters = $filters ?? [
    'location' => '', 'type' => '', 'price_range' => '', 'status' => '', 'category_slug' => '', 'realtor' => null, 'sort' => '',
    'search' => '', 'city' => '', 'district' => '', 'min_rooms' => 0, 'min_bathrooms' => 0, 'min_bedrooms' => 0, 'area_min' => '', 'area_max' => ''
];
$iller = $iller ?? [];
$ilceByIlName = $ilceByIlName ?? [];
$all_categories = $all_categories ?? [];
$current_category = $current_category ?? null;
$categorySlug = $filters['category_slug'] ?? '';
$sortOptions = [
    'newest' => __('En Yeni'),
    'oldest' => __('En Eski'),
    'price_asc' => __('Fiyat (Düşük → Yüksek)'),
    'price_desc' => __('Fiyat (Yüksek → Düşük)'),
    'title_asc' => __('Başlık (A-Z)'),
    'title_desc' => __('Başlık (Z-A)')
];
$totalListings = count($listings);
$listingsPageUrl = function_exists('localized_url') ? localized_url('/ilanlar') : site_url('/ilanlar');
$listingsFormAction = $categorySlug !== '' ? (function_exists('localized_url') ? localized_url('/ilanlar/kategori/' . $categorySlug) : site_url('/ilanlar/kategori/' . $categorySlug)) : $listingsPageUrl;
$ilanBase = function_exists('localized_url') ? rtrim(localized_url('/ilan'), '/') : rtrim(site_url('/ilan'), '/');
$danismanBase = function_exists('localized_url') ? rtrim(localized_url('/danismanlar'), '/') : rtrim(site_url('/danismanlar'), '/');
$themeLoader = class_exists('ThemeLoader') ? ThemeLoader::getInstance() : null;
$primaryColor = $themeLoader ? $themeLoader->getColor('primary', '#bc1a1a') : '#bc1a1a';
$currentType = $filters['type'] ?? '';
function cizgiaks_listings_cat_url($listingsPageUrl, $type, $filters) {
    $params = [];
    if (!empty($filters['location'])) $params['location'] = $filters['location'];
    if (!empty($filters['status'])) $params['status'] = $filters['status'];
    if (!empty($filters['price_range'])) $params['price_range'] = $filters['price_range'];
    if (!empty($filters['realtor'])) $params['realtor'] = $filters['realtor'];
    if (!empty($filters['sort'])) $params['sort'] = $filters['sort'];
    if (!empty($filters['search'])) $params['search'] = $filters['search'];
    if (!empty($filters['city'])) $params['city'] = $filters['city'];
    if (!empty($filters['district'])) $params['district'] = $filters['district'];
    if (!empty($filters['min_rooms'])) $params['min_rooms'] = $filters['min_rooms'];
    if (!empty($filters['min_bathrooms'])) $params['min_bathrooms'] = $filters['min_bathrooms'];
    if (!empty($filters['min_bedrooms'])) $params['min_bedrooms'] = $filters['min_bedrooms'];
    if (isset($filters['area_min']) && $filters['area_min'] !== '' && $filters['area_min'] !== null) $params['area_min'] = $filters['area_min'];
    if (isset($filters['area_max']) && $filters['area_max'] !== '' && $filters['area_max'] !== null) $params['area_max'] = $filters['area_max'];
    if ($type !== '') $params['type'] = $type;
    $q = http_build_query($params);
    return $listingsPageUrl . ($q ? '?' . $q : '');
}
// Embed URL: header/footer olmadan sadece harita (iframe'de çift header önlenir)
$haritaIframeUrl = function_exists('localized_url') ? localized_url('/harita-ilanlar/embed') : (function_exists('site_url') ? rtrim(site_url('/harita-ilanlar/embed'), '/') : '/harita-ilanlar/embed');
?>
<?php
/* Filtreler sol sidebar'da – aşağıda view--katalog içinde */
?>

<section class="cizgiaks-listings-page">
    <div class="cizgiaks-container">
        <header class="cizgiaks-page-header">
            <h1 class="cizgiaks-page-title"><?php echo esc_html($current_category ? $current_category['name'] . ' - ' . __('Emlak İlanları') : __('Emlak İlanları')); ?></h1>
            <p class="cizgiaks-page-subtitle"><?php echo esc_html(__('Hayalinizdeki emlağı bulun')); ?></p>
            <?php if (!empty($all_categories)): ?>
            <div class="cizgiaks-category-pills flex flex-wrap gap-2 mt-3">
                <a href="<?php echo esc_url($listingsPageUrl); ?>" class="cizgiaks-pill <?php echo $categorySlug === '' ? 'cizgiaks-pill--active' : ''; ?>" style="--cizgiaks-primary:<?php echo esc_attr($primaryColor); ?>"><?php echo esc_html(__('Tümü')); ?></a>
                <?php foreach ($all_categories as $cat): ?>
                <a href="<?php echo esc_url(function_exists('localized_url') ? localized_url('/ilanlar/kategori/' . $cat['slug']) : site_url('/ilanlar/kategori/' . $cat['slug'])); ?>" class="cizgiaks-pill <?php echo $categorySlug === $cat['slug'] ? 'cizgiaks-pill--active' : ''; ?>" style="--cizgiaks-primary:<?php echo esc_attr($primaryColor); ?>"><?php echo esc_html($cat['name']); ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </header>
        <div class="cizgiaks-listings-view cizgiaks-listings-view--katalog" id="view-katalog" role="tabpanel" aria-labelledby="tab-katalog">
        <form action="<?php echo esc_url($listingsFormAction); ?>" method="get" id="listings-filter-form">
            <?php if (!empty($filters['realtor'])): ?>
            <input type="hidden" name="realtor" value="<?php echo (int) $filters['realtor']; ?>">
            <?php endif; ?>
            <!-- Üst yatay filtre barı -->
            <div class="cizgiaks-listings-topbar">
                <div class="cizgiaks-listings-topbar-inner">
                    <input type="text" name="location" value="<?php echo esc_attr($filters['location'] ?? ''); ?>" placeholder="<?php echo esc_attr(__('Konum / Semt')); ?>" class="cizgiaks-topbar-input">
                    <select name="status" class="cizgiaks-topbar-select">
                        <option value=""><?php echo esc_html(__('Durum')); ?></option>
                        <option value="sale" <?php echo selected($filters['status'] ?? '', 'sale'); ?>><?php echo esc_html(__('Satılık')); ?></option>
                        <option value="rent" <?php echo selected($filters['status'] ?? '', 'rent'); ?>><?php echo esc_html(__('Kiralık')); ?></option>
                    </select>
                    <select name="type" class="cizgiaks-topbar-select">
                        <option value=""><?php echo esc_html(__('Emlak Tipi')); ?></option>
                        <?php foreach ($propertyTypeLabels as $key => $label): ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php echo selected($filters['type'] ?? '', $key); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="price_range" class="cizgiaks-topbar-select">
                        <option value=""><?php echo esc_html(__('Fiyat')); ?></option>
                        <option value="0-500000" <?php echo selected($filters['price_range'] ?? '', '0-500000'); ?>>₺0-500K</option>
                        <option value="500000-1000000" <?php echo selected($filters['price_range'] ?? '', '500000-1000000'); ?>>₺500K-1M</option>
                        <option value="1000000-2000000" <?php echo selected($filters['price_range'] ?? '', '1000000-2000000'); ?>>₺1M-2M</option>
                        <option value="2000000-5000000" <?php echo selected($filters['price_range'] ?? '', '2000000-5000000'); ?>>₺2M-5M</option>
                        <option value="5000000+" <?php echo selected($filters['price_range'] ?? '', '5000000+'); ?>>₺5M+</option>
                    </select>
                    <button type="submit" class="cizgiaks-topbar-btn" style="background:<?php echo esc_attr($primaryColor); ?>"><?php echo esc_html(__('Ara')); ?></button>
                </div>
            </div>
        </form>
        <button type="button" class="cizgiaks-listings-filters-mobile-btn" id="listings-filters-mobile-btn" aria-label="<?php echo esc_attr(__('Filtreleri aç')); ?>">
            <span aria-hidden="true">⚙️</span> <?php echo esc_html(__('Filtreler')); ?>
        </button>
        <div class="cizgiaks-listings-filters-modal" id="listings-filters-modal" role="dialog" aria-modal="true" aria-labelledby="listings-filters-modal-title" hidden>
            <div class="cizgiaks-listings-filters-modal-backdrop" id="listings-filters-modal-backdrop"></div>
            <div class="cizgiaks-listings-filters-modal-panel">
                <div class="cizgiaks-listings-filters-modal-header">
                    <h2 id="listings-filters-modal-title" class="cizgiaks-listings-filters-modal-title"><?php echo esc_html(__('Filtreler')); ?></h2>
                    <button type="button" class="cizgiaks-listings-filters-modal-close" id="listings-filters-modal-close" aria-label="<?php echo esc_attr(__('Kapat')); ?>">&times;</button>
                </div>
                <div class="cizgiaks-listings-filters-modal-body" id="listings-filters-modal-body">
                    <?php /* İçerik aşağıda listings-filters-sidebar içeriğiyle senkron - mobilde modalda gösterilir */ ?>
                    <form action="<?php echo esc_url($listingsPageUrl); ?>" method="get" class="cizgiaks-listings-filter-form">
                        <?php if (!empty($filters['realtor'])): ?><input type="hidden" name="realtor" value="<?php echo (int) $filters['realtor']; ?>"><?php endif; ?>
                        <input type="hidden" name="location" value="<?php echo esc_attr($filters['location'] ?? ''); ?>">
                        <input type="hidden" name="status" value="<?php echo esc_attr($filters['status'] ?? ''); ?>">
                        <input type="hidden" name="type" value="<?php echo esc_attr($filters['type'] ?? ''); ?>">
                        <input type="hidden" name="price_range" value="<?php echo esc_attr($filters['price_range'] ?? ''); ?>">
                        <input type="hidden" name="sort" value="<?php echo esc_attr($filters['sort'] ?? 'newest'); ?>">
                        <div class="cizgiaks-filters-card">
                            <h3 class="cizgiaks-filters-section-title"><?php echo esc_html(__('Anahtar kelime')); ?></h3>
                            <input type="text" name="search" value="<?php echo esc_attr($filters['search'] ?? ''); ?>" placeholder="<?php echo esc_attr(__('Başlık veya açıklama...')); ?>" class="cizgiaks-hero-input" style="margin-bottom:1rem;">
                            <h3 class="cizgiaks-filters-section-title"><?php echo esc_html(__('Konum')); ?></h3>
                            <select name="city" class="cizgiaks-hero-select cizgiaks-modal-city" data-ilceler="<?php echo esc_attr(json_encode($ilceByIlName)); ?>" style="margin-bottom:0.5rem;">
                                <option value=""><?php echo esc_html(__('İl')); ?></option>
                                <?php foreach ($iller as $ilCode => $ilName): ?>
                                <option value="<?php echo esc_attr($ilName); ?>" <?php echo selected($filters['city'] ?? '', $ilName); ?>><?php echo esc_html($ilName); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="district" class="cizgiaks-hero-select cizgiaks-modal-district" style="margin-bottom:1rem;">
                                <option value=""><?php echo esc_html(__('İlçe')); ?></option>
                                <?php $currentIlceler = !empty($filters['city']) && !empty($ilceByIlName[$filters['city']]) ? $ilceByIlName[$filters['city']] : []; foreach ($currentIlceler as $ilceName): ?>
                                <option value="<?php echo esc_attr($ilceName); ?>" <?php echo selected($filters['district'] ?? '', $ilceName); ?>><?php echo esc_html($ilceName); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <h3 class="cizgiaks-filters-section-title"><?php echo esc_html(__('m²')); ?></h3>
                            <div class="cizgiaks-filters-row cizgiaks-filters-row--inline" style="margin-bottom:1rem;">
                                <input type="number" name="area_min" value="<?php echo esc_attr($filters['area_min'] ?? ''); ?>" min="0" placeholder="<?php echo esc_attr(__('Min')); ?>" class="cizgiaks-hero-input">
                                <span class="cizgiaks-filters-sep">–</span>
                                <input type="number" name="area_max" value="<?php echo esc_attr($filters['area_max'] ?? ''); ?>" min="0" placeholder="<?php echo esc_attr(__('Max')); ?>" class="cizgiaks-hero-input">
                            </div>
                            <h3 class="cizgiaks-filters-section-title"><?php echo esc_html(__('Oda / Banyo')); ?></h3>
                            <div class="cizgiaks-filters-row cizgiaks-filters-row--inline" style="margin-bottom:1rem;">
                                <select name="min_rooms" class="cizgiaks-hero-select">
                                    <option value="0"><?php echo esc_html(__('Oda')); ?></option>
                                    <?php for ($r = 1; $r <= 6; $r++): ?><option value="<?php echo $r; ?>" <?php echo selected((int)($filters['min_rooms'] ?? 0), $r); ?>><?php echo $r; ?>+</option><?php endfor; ?>
                                </select>
                                <select name="min_bathrooms" class="cizgiaks-hero-select">
                                    <option value="0"><?php echo esc_html(__('Banyo')); ?></option>
                                    <?php for ($b = 1; $b <= 4; $b++): ?><option value="<?php echo $b; ?>" <?php echo selected((int)($filters['min_bathrooms'] ?? 0), $b); ?>><?php echo $b; ?>+</option><?php endfor; ?>
                                </select>
                            </div>
                            <button type="submit" class="cizgiaks-hero-search-btn cizgiaks-btn cizgiaks-btn-primary" style="width:100%;margin-top:0.5rem;background:<?php echo esc_attr($primaryColor); ?>"><?php echo esc_html(__('Uygula')); ?></button>
                            <a href="<?php echo esc_url(!empty($filters['realtor']) ? $listingsPageUrl . '?realtor=' . (int)$filters['realtor'] : $listingsPageUrl); ?>" class="cizgiaks-btn cizgiaks-btn-outline" style="display:block;text-align:center;margin-top:0.5rem;"><?php echo esc_html(__('Temizle')); ?></a>
                        </div>
                    </form>
                    <div class="cizgiaks-sidebar-block" style="margin-top:1rem;">
                        <h3 class="cizgiaks-sidebar-block-title"><?php echo esc_html(__('Kategoriler')); ?></h3>
                        <ul class="cizgiaks-listings-categories-list cizgiaks-listings-categories-list--sidebar">
                            <li><a href="<?php echo esc_url(cizgiaks_listings_cat_url($listingsPageUrl, '', $filters)); ?>" class="cizgiaks-listings-cat-link<?php echo $currentType === '' ? ' active' : ''; ?>"><?php echo esc_html(__('Tümü')); ?></a></li>
                            <?php foreach ($propertyTypeLabels as $key => $label): ?>
                            <li><a href="<?php echo esc_url(cizgiaks_listings_cat_url($listingsPageUrl, $key, $filters)); ?>" class="cizgiaks-listings-cat-link<?php echo $currentType === $key ? ' active' : ''; ?>"><?php echo esc_html($label); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="cizgiaks-sidebar-block">
                        <h3 class="cizgiaks-sidebar-block-title"><?php echo esc_html(__('Durum')); ?></h3>
                        <ul class="cizgiaks-listings-categories-list cizgiaks-listings-categories-list--sidebar">
                            <li><a href="<?php echo esc_url(cizgiaks_listings_cat_url($listingsPageUrl, $currentType, array_merge($filters, ['status' => '']))); ?>" class="cizgiaks-listings-cat-link<?php echo ($filters['status'] ?? '') === '' ? ' active' : ''; ?>"><?php echo esc_html(__('Hepsi')); ?></a></li>
                            <li><a href="<?php echo esc_url(cizgiaks_listings_cat_url($listingsPageUrl, $currentType, array_merge($filters, ['status' => 'sale']))); ?>" class="cizgiaks-listings-cat-link<?php echo ($filters['status'] ?? '') === 'sale' ? ' active' : ''; ?>"><?php echo esc_html(__('Satılık')); ?></a></li>
                            <li><a href="<?php echo esc_url(cizgiaks_listings_cat_url($listingsPageUrl, $currentType, array_merge($filters, ['status' => 'rent']))); ?>" class="cizgiaks-listings-cat-link<?php echo ($filters['status'] ?? '') === 'rent' ? ' active' : ''; ?>"><?php echo esc_html(__('Kiralık')); ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="cizgiaks-listings-with-filters">
            <aside class="cizgiaks-listings-filters-sidebar" id="listings-filters-sidebar">
                <form action="<?php echo esc_url($listingsPageUrl); ?>" method="get" class="cizgiaks-listings-filter-form">
                    <?php if (!empty($filters['realtor'])): ?>
                    <input type="hidden" name="realtor" value="<?php echo (int) $filters['realtor']; ?>">
                    <?php endif; ?>
                    <input type="hidden" name="location" value="<?php echo esc_attr($filters['location'] ?? ''); ?>">
                    <input type="hidden" name="status" value="<?php echo esc_attr($filters['status'] ?? ''); ?>">
                    <input type="hidden" name="type" value="<?php echo esc_attr($filters['type'] ?? ''); ?>">
                    <input type="hidden" name="price_range" value="<?php echo esc_attr($filters['price_range'] ?? ''); ?>">
                    <input type="hidden" name="sort" value="<?php echo esc_attr($filters['sort'] ?? 'newest'); ?>">
                    <div class="cizgiaks-filters-card">
                        <h2 class="cizgiaks-filters-card-title"><?php echo esc_html(__('Detaylı Filtre')); ?></h2>
                        <div class="cizgiaks-filters-section">
                            <label for="search" class="cizgiaks-filters-section-title"><?php echo esc_html(__('Anahtar kelime')); ?></label>
                            <input type="text" id="search" name="search" value="<?php echo esc_attr($filters['search'] ?? ''); ?>" placeholder="<?php echo esc_attr(__('Başlık veya açıklama...')); ?>" class="cizgiaks-hero-input">
                        </div>
                        <div class="cizgiaks-filters-section">
                            <h3 class="cizgiaks-filters-section-title"><?php echo esc_html(__('Konum')); ?></h3>
                            <select id="city" name="city" class="cizgiaks-hero-select" data-ilceler="<?php echo esc_attr(json_encode($ilceByIlName)); ?>">
                                <option value=""><?php echo esc_html(__('İl')); ?></option>
                                <?php foreach ($iller as $ilCode => $ilName): ?>
                                <option value="<?php echo esc_attr($ilName); ?>" <?php echo selected($filters['city'] ?? '', $ilName); ?>><?php echo esc_html($ilName); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select id="district" name="district" class="cizgiaks-hero-select" style="margin-top:0.5rem;">
                                <option value=""><?php echo esc_html(__('İlçe')); ?></option>
                                <?php
                                $currentIlceler = !empty($filters['city']) && !empty($ilceByIlName[$filters['city']]) ? $ilceByIlName[$filters['city']] : [];
                                foreach ($currentIlceler as $ilceName):
                                ?>
                                <option value="<?php echo esc_attr($ilceName); ?>" <?php echo selected($filters['district'] ?? '', $ilceName); ?>><?php echo esc_html($ilceName); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="cizgiaks-filters-section">
                            <h3 class="cizgiaks-filters-section-title"><?php echo esc_html(__('m²')); ?></h3>
                            <div class="cizgiaks-filters-row cizgiaks-filters-row--inline">
                                <input type="number" name="area_min" value="<?php echo esc_attr($filters['area_min'] ?? ''); ?>" min="0" placeholder="<?php echo esc_attr(__('Min')); ?>" class="cizgiaks-hero-input">
                                <span class="cizgiaks-filters-sep">–</span>
                                <input type="number" name="area_max" value="<?php echo esc_attr($filters['area_max'] ?? ''); ?>" min="0" placeholder="<?php echo esc_attr(__('Max')); ?>" class="cizgiaks-hero-input">
                            </div>
                        </div>
                        <div class="cizgiaks-filters-section">
                            <h3 class="cizgiaks-filters-section-title"><?php echo esc_html(__('Oda / Banyo')); ?></h3>
                            <div class="cizgiaks-filters-row cizgiaks-filters-row--inline">
                                <select name="min_rooms" class="cizgiaks-hero-select">
                                    <option value="0"><?php echo esc_html(__('Oda')); ?></option>
                                    <?php for ($r = 1; $r <= 6; $r++): ?>
                                    <option value="<?php echo $r; ?>" <?php echo selected((int)($filters['min_rooms'] ?? 0), $r); ?>><?php echo $r; ?>+</option>
                                    <?php endfor; ?>
                                </select>
                                <select name="min_bathrooms" class="cizgiaks-hero-select">
                                    <option value="0"><?php echo esc_html(__('Banyo')); ?></option>
                                    <?php for ($b = 1; $b <= 4; $b++): ?>
                                    <option value="<?php echo $b; ?>" <?php echo selected((int)($filters['min_bathrooms'] ?? 0), $b); ?>><?php echo $b; ?>+</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="cizgiaks-hero-search-btn cizgiaks-btn cizgiaks-btn-primary" style="width:100%;margin-top:0.75rem;background:<?php echo esc_attr($primaryColor); ?>"><?php echo esc_html(__('Uygula')); ?></button>
                        <a href="<?php echo esc_url(!empty($filters['realtor']) ? $listingsPageUrl . '?realtor=' . (int)$filters['realtor'] : $listingsPageUrl); ?>" class="cizgiaks-btn cizgiaks-btn-outline" style="display:block;text-align:center;margin-top:0.5rem;"><?php echo esc_html(__('Temizle')); ?></a>
                    </div>
                </form>
                <div class="cizgiaks-sidebar-block">
                    <h3 class="cizgiaks-sidebar-block-title"><?php echo esc_html(__('Kategoriler')); ?></h3>
                    <ul class="cizgiaks-listings-categories-list cizgiaks-listings-categories-list--sidebar">
                        <li><a href="<?php echo esc_url(cizgiaks_listings_cat_url($listingsPageUrl, '', $filters)); ?>" class="cizgiaks-listings-cat-link<?php echo $currentType === '' ? ' active' : ''; ?>"><?php echo esc_html(__('Tümü')); ?></a></li>
                        <?php foreach ($propertyTypeLabels as $key => $label): ?>
                        <li><a href="<?php echo esc_url(cizgiaks_listings_cat_url($listingsPageUrl, $key, $filters)); ?>" class="cizgiaks-listings-cat-link<?php echo $currentType === $key ? ' active' : ''; ?>"><?php echo esc_html($label); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="cizgiaks-sidebar-block">
                    <h3 class="cizgiaks-sidebar-block-title"><?php echo esc_html(__('Durum')); ?></h3>
                    <ul class="cizgiaks-listings-categories-list cizgiaks-listings-categories-list--sidebar">
                        <li><a href="<?php echo esc_url(cizgiaks_listings_cat_url($listingsPageUrl, $currentType, array_merge($filters, ['status' => '']))); ?>" class="cizgiaks-listings-cat-link<?php echo ($filters['status'] ?? '') === '' ? ' active' : ''; ?>"><?php echo esc_html(__('Hepsi')); ?></a></li>
                        <li><a href="<?php echo esc_url(cizgiaks_listings_cat_url($listingsPageUrl, $currentType, array_merge($filters, ['status' => 'sale']))); ?>" class="cizgiaks-listings-cat-link<?php echo ($filters['status'] ?? '') === 'sale' ? ' active' : ''; ?>"><?php echo esc_html(__('Satılık')); ?></a></li>
                        <li><a href="<?php echo esc_url(cizgiaks_listings_cat_url($listingsPageUrl, $currentType, array_merge($filters, ['status' => 'rent']))); ?>" class="cizgiaks-listings-cat-link<?php echo ($filters['status'] ?? '') === 'rent' ? ' active' : ''; ?>"><?php echo esc_html(__('Kiralık')); ?></a></li>
                    </ul>
                </div>
            </aside>
            <div class="cizgiaks-listings-content">
                <div class="cizgiaks-listings-main">
<script>
(function(){
    var citySelect = document.getElementById('city');
    var districtSelect = document.getElementById('district');
    var ilceByIlName = {};
    if (citySelect && citySelect.getAttribute('data-ilceler')) {
        try { ilceByIlName = JSON.parse(citySelect.getAttribute('data-ilceler') || '{}'); } catch(e) {}
    }
    function updateDistricts(cityEl, districtEl) {
        if (!cityEl || !districtEl) return;
        var city = cityEl.value;
        var ilceler = ilceByIlName[city] || [];
        var currentVal = districtEl.value;
        districtEl.innerHTML = '<option value=""><?php echo esc_js(__('Tümü')); ?></option>';
        ilceler.forEach(function(name) {
            var opt = document.createElement('option');
            opt.value = name;
            opt.textContent = name;
            if (name === currentVal) opt.selected = true;
            districtEl.appendChild(opt);
        });
    }
    if (citySelect && districtSelect) {
        citySelect.addEventListener('change', function() { updateDistricts(citySelect, districtSelect); });
    }
    var modalCity = document.querySelector('.cizgiaks-modal-city');
    var modalDistrict = document.querySelector('.cizgiaks-modal-district');
    if (modalCity && modalDistrict) {
        if (Object.keys(ilceByIlName).length === 0 && citySelect) try { ilceByIlName = JSON.parse(citySelect.getAttribute('data-ilceler') || '{}'); } catch(e) {}
        modalCity.addEventListener('change', function() { updateDistricts(modalCity, modalDistrict); });
    }
    var modal = document.getElementById('listings-filters-modal');
    var openBtn = document.getElementById('listings-filters-mobile-btn');
    var closeBtn = document.getElementById('listings-filters-modal-close');
    var backdrop = document.getElementById('listings-filters-modal-backdrop');
    function openModal() {
        if (modal) { modal.removeAttribute('hidden'); modal.classList.add('is-open'); document.body.style.overflow = 'hidden'; }
    }
    function closeModal() {
        if (modal) { modal.setAttribute('hidden', ''); modal.classList.remove('is-open'); document.body.style.overflow = ''; }
    }
    if (openBtn) openBtn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (backdrop) backdrop.addEventListener('click', closeModal);
    if (modal) {
        modal.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeModal(); });
    }
})();
</script>

        <?php if (!empty($listings)): ?>
        <div class="cizgiaks-listings-toolbar">
            <p class="cizgiaks-listings-toolbar-count">
                <strong><?php echo number_format($totalListings, 0, ',', '.'); ?></strong> <?php echo esc_html(__('ilan bulundu')); ?>
            </p>
            <form method="get" action="<?php echo esc_url($listingsPageUrl); ?>" class="cizgiaks-listings-toolbar-sort" onchange="this.submit()">
                <?php if (!empty($filters['location'])): ?><input type="hidden" name="location" value="<?php echo esc_attr($filters['location']); ?>"><?php endif; ?>
                <?php if (!empty($filters['status'])): ?><input type="hidden" name="status" value="<?php echo esc_attr($filters['status']); ?>"><?php endif; ?>
                <?php if (!empty($filters['type'])): ?><input type="hidden" name="type" value="<?php echo esc_attr($filters['type']); ?>"><?php endif; ?>
                <?php if (!empty($filters['price_range'])): ?><input type="hidden" name="price_range" value="<?php echo esc_attr($filters['price_range']); ?>"><?php endif; ?>
                <?php if (!empty($filters['realtor'])): ?><input type="hidden" name="realtor" value="<?php echo (int) $filters['realtor']; ?>"><?php endif; ?>
                <?php if (!empty($filters['search'])): ?><input type="hidden" name="search" value="<?php echo esc_attr($filters['search']); ?>"><?php endif; ?>
                <?php if (!empty($filters['city'])): ?><input type="hidden" name="city" value="<?php echo esc_attr($filters['city']); ?>"><?php endif; ?>
                <?php if (!empty($filters['district'])): ?><input type="hidden" name="district" value="<?php echo esc_attr($filters['district']); ?>"><?php endif; ?>
                <?php if (!empty($filters['min_rooms'])): ?><input type="hidden" name="min_rooms" value="<?php echo (int) $filters['min_rooms']; ?>"><?php endif; ?>
                <?php if (!empty($filters['min_bathrooms'])): ?><input type="hidden" name="min_bathrooms" value="<?php echo (int) $filters['min_bathrooms']; ?>"><?php endif; ?>
                <?php if (!empty($filters['min_bedrooms'])): ?><input type="hidden" name="min_bedrooms" value="<?php echo (int) $filters['min_bedrooms']; ?>"><?php endif; ?>
                <?php if (isset($filters['area_min']) && $filters['area_min'] !== ''): ?><input type="hidden" name="area_min" value="<?php echo esc_attr($filters['area_min']); ?>"><?php endif; ?>
                <?php if (isset($filters['area_max']) && $filters['area_max'] !== ''): ?><input type="hidden" name="area_max" value="<?php echo esc_attr($filters['area_max']); ?>"><?php endif; ?>
                <label for="sort-toolbar" class="cizgiaks-listings-toolbar-sort-label"><?php echo esc_html(__('Sırala')); ?>:</label>
                <select id="sort-toolbar" name="sort" class="cizgiaks-hero-select cizgiaks-toolbar-select">
                    <?php foreach ($sortOptions as $value => $label): ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php echo selected($filters['sort'] ?? 'newest', $value); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <div class="cizgiaks-listings-view-switch" role="tablist" aria-label="<?php echo esc_attr(__('Görünüm')); ?>">
                <button type="button" class="cizgiaks-listings-view-switch-btn is-active" data-view="katalog" role="tab" aria-selected="true">
                    <span aria-hidden="true">📋</span> <?php echo esc_html(__('Katalog')); ?>
                </button>
                <button type="button" class="cizgiaks-listings-view-switch-btn" data-view="harita" role="tab" aria-selected="false">
                    <span aria-hidden="true">🗺️</span> <?php echo esc_html(__('Haritada Gör')); ?>
                </button>
            </div>
        </div>
        <div class="cizgiaks-listings-grid cizgiaks-listings-grid--3">
            <?php foreach ($listings as $listing):
                $slug = !empty($listing['slug']) ? $listing['slug'] : $listing['id'];
                $detailUrl = $ilanBase . '/' . $slug;
                $listingStatus = $listing['listing_status'] ?? 'sale';
                $statusLabel = $listingStatus === 'rent' ? __('Kiralık') : __('Satılık');
                $propertyType = $listing['property_type'] ?? 'house';
                $propertyTypeLabel = $propertyTypeLabels[$propertyType] ?? $propertyType;
                $price = function_exists('realestate_format_price') ? realestate_format_price($listing['price']) : '₺' . number_format((float)($listing['price'] ?? 0), 0, ',', '.');
                $roomDisplay = '';
                $oda = (int)($listing['rooms'] ?? 0);
                $salon = (int)($listing['living_rooms'] ?? 0);
                if ($oda > 0 || $salon > 0) $roomDisplay = $oda . '+' . $salon;
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
        <?php else: ?>
        <div class="cizgiaks-home-listings-empty">
            <div class="cizgiaks-listings-view-switch cizgiaks-listings-view-switch--empty" role="tablist" aria-label="<?php echo esc_attr(__('Görünüm')); ?>">
                <button type="button" class="cizgiaks-listings-view-switch-btn is-active" data-view="katalog" role="tab" aria-selected="true">
                    <span aria-hidden="true">📋</span> <?php echo esc_html(__('Katalog')); ?>
                </button>
                <button type="button" class="cizgiaks-listings-view-switch-btn" data-view="harita" role="tab" aria-selected="false">
                    <span aria-hidden="true">🗺️</span> <?php echo esc_html(__('Haritada Gör')); ?>
                </button>
            </div>
            <h3 class="cizgiaks-agents-empty-title"><?php echo esc_html(__('İlan Bulunamadı')); ?></h3>
            <p class="cizgiaks-agents-empty-text"><?php echo esc_html(__('Aradığınız kriterlere uygun ilan bulunamadı. Filtrelerinizi değiştirerek tekrar deneyebilirsiniz.')); ?></p>
            <p style="margin-top:1rem;">
                <a href="<?php echo esc_url($listingsPageUrl); ?>" class="cizgiaks-btn cizgiaks-btn-primary" style="background:<?php echo esc_attr($primaryColor); ?>"><?php echo esc_html(__('Tüm İlanları Gör')); ?></a>
            </p>
        </div>
        <?php endif; ?>
                </div>
            </div><!-- .cizgiaks-listings-content -->
        </div><!-- .cizgiaks-listings-with-filters -->
        </div><!-- .cizgiaks-listings-view--katalog -->

        <!-- Harita görünümü: sadece harita (tam genişlik) -->
        <div class="cizgiaks-listings-view cizgiaks-listings-view--harita" id="view-harita" role="tabpanel" hidden>
            <div class="cizgiaks-listings-view-switch cizgiaks-listings-view-switch--map" role="tablist" aria-label="<?php echo esc_attr(__('Görünüm')); ?>">
                <button type="button" class="cizgiaks-listings-view-switch-btn" data-view="katalog" role="tab" aria-selected="false">
                    <span aria-hidden="true">📋</span> <?php echo esc_html(__('Katalog')); ?>
                </button>
                <button type="button" class="cizgiaks-listings-view-switch-btn is-active" data-view="harita" role="tab" aria-selected="true">
                    <span aria-hidden="true">🗺️</span> <?php echo esc_html(__('Haritada Gör')); ?>
                </button>
            </div>
            <div class="cizgiaks-listings-split cizgiaks-listings-split--map-only">
                <div class="cizgiaks-listings-split-map">
                    <div class="cizgiaks-listings-map-iframe-wrap">
                        <iframe src="<?php echo esc_url($haritaIframeUrl); ?>" class="cizgiaks-listings-map-iframe" title="<?php echo esc_attr(__('İlanlar haritası')); ?>"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.cizgiaks-listings-tabs { display: flex; gap: 0.25rem; margin-bottom: 1.25rem; border-bottom: 1px solid rgba(0,0,0,0.08); }
.cizgiaks-listings-view-switch { display: inline-flex; align-items: center; border: 1px solid var(--cizgiaks-border, #e5e7eb); border-radius: 0.5rem; overflow: hidden; background: #fff; }
.cizgiaks-listings-view-switch-btn { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; border: none; background: transparent; color: var(--cizgiaks-text-muted, #6b7280); font-size: 0.9375rem; font-weight: 500; cursor: pointer; transition: color 0.2s, background 0.2s; }
.cizgiaks-listings-view-switch-btn:hover { color: var(--cizgiaks-text, #1f2937); }
.cizgiaks-listings-view-switch-btn.is-active { color: #fff; background: var(--cizgiaks-primary, #bc1a1a); }
.cizgiaks-listings-view-switch--map { margin-bottom: 0.75rem; }
.cizgiaks-listings-view-switch--empty { margin-bottom: 1rem; }
.cizgiaks-listings-view--harita { display: block !important; }
.cizgiaks-listings-view--harita[hidden] { display: none !important; }
.cizgiaks-listings-split { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; min-height: 70vh; }
.cizgiaks-listings-split--map-only { grid-template-columns: 1fr; min-height: 75vh; }
@media (max-width: 900px) { .cizgiaks-listings-split { grid-template-columns: 1fr; } }
.cizgiaks-listings-split-catalog { overflow-y: auto; max-height: 75vh; padding-right: 0.5rem; }
.cizgiaks-listings-split-catalog .cizgiaks-listings-grid--compact { grid-template-columns: 1fr; }
.cizgiaks-listings-split-title { font-size: 1.125rem; font-weight: 600; margin: 0 0 0.75rem; color: var(--cizgiaks-text, #1f2937); }
.cizgiaks-listings-split-count { font-size: 0.875rem; color: var(--cizgiaks-text-muted, #6b7280); margin: 0 0 1rem; }
.cizgiaks-listings-split--map-only .cizgiaks-listings-map-iframe-wrap { height: 78vh; min-height: 500px; }
.cizgiaks-listings-map-iframe-wrap { position: relative; width: 100%; height: 65vh; min-height: 400px; border-radius: 0.5rem; overflow: hidden; border: 1px solid rgba(0,0,0,0.08); background: #f5f5f5; }
.cizgiaks-listings-map-iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; }
</style>

<script>
(function() {
    var viewKatalog = document.getElementById('view-katalog');
    var viewHarita = document.getElementById('view-harita');
    if (!viewKatalog || !viewHarita) return;
    function showView(view) {
        var isKatalog = (view === 'katalog');
        viewKatalog.hidden = !isKatalog;
        viewHarita.hidden = isKatalog;
        document.querySelectorAll('.cizgiaks-listings-view-switch-btn[data-view="katalog"]').forEach(function(btn) {
            btn.classList.toggle('is-active', isKatalog);
            btn.setAttribute('aria-selected', isKatalog ? 'true' : 'false');
        });
        document.querySelectorAll('.cizgiaks-listings-view-switch-btn[data-view="harita"]').forEach(function(btn) {
            btn.classList.toggle('is-active', !isKatalog);
            btn.setAttribute('aria-selected', !isKatalog ? 'true' : 'false');
        });
        if (!isKatalog && viewHarita.querySelector('.cizgiaks-listings-map-iframe')) {
            var src = viewHarita.querySelector('.cizgiaks-listings-map-iframe').src;
            if (src && src.indexOf('harita-ilanlar') !== -1 && src.indexOf('http') === 0) { /* iframe zaten yüklü */ }
        }
        try { window.history.replaceState(null, '', (window.location.pathname || '') + (isKatalog ? '' : '#harita')); } catch (e) {}
    }
    document.querySelectorAll('.cizgiaks-listings-view-switch-btn[data-view="katalog"]').forEach(function(btn) {
        btn.addEventListener('click', function() { showView('katalog'); });
    });
    document.querySelectorAll('.cizgiaks-listings-view-switch-btn[data-view="harita"]').forEach(function(btn) {
        btn.addEventListener('click', function() { showView('harita'); });
    });
    if (window.location.hash === '#harita') showView('harita');
})();
</script>

<?php get_footer(); ?>
