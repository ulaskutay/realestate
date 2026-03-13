<?php
/**
 * Çizgi Aks - İlan detay sayfası (sahibinden.com mantığı)
 * Veri: $listing (zorunlu)
 */
if (!isset($listing) || empty($listing)) {
    die('İlan verisi bulunamadı');
}

get_header([
    'title' => ($listing['title'] ?? __('İlan Detayı')) . ' - ' . get_option('site_name', ''),
    'meta_description' => !empty($listing['description']) ? substr(strip_tags($listing['description']), 0, 160) : ''
]);

$propertyTypeLabels = [
    'house' => __('Müstakil Ev'),
    'apartment' => __('Daire'),
    'villa' => __('Villa'),
    'commercial' => __('Ticari'),
    'land' => __('Arsa')
];
$propertyType = $listing['property_type'] ?? 'house';
$propertyTypeLabel = $propertyTypeLabels[$propertyType] ?? ucfirst($propertyType);
$listingStatus = $listing['listing_status'] ?? 'sale';
$statusLabel = $listingStatus === 'rent' ? __('Kiralık') : __('Satılık');
$formattedPrice = number_format($listing['price'] ?? 0, 0, ',', '.') . ' TL';
$formattedArea = !empty($listing['area']) ? number_format($listing['area'], 0, ',', '.') . ' m²' : '';

// Emlak tipi + kiralık/satılık (sahibinden: "Kiralık Daire")
$emlakTipiLabel = $statusLabel . ' ' . $propertyTypeLabel;

// Oda sayısı: oda + salon (örn. 3+1 = 3 oda, 1 salon)
$odaSayisi = '';
$oda = (int)($listing['rooms'] ?? 0);
$salon = (int)($listing['living_rooms'] ?? 0);
if ($oda > 0 || $salon > 0) {
    $odaSayisi = $oda . '+' . $salon;
} else {
    $bed = (int)($listing['bedrooms'] ?? 0);
    if ($bed > 0) $odaSayisi = $bed . '+0';
}

// Sahibinden.com uyumlu özellik değeri: boşsa "Belirtilmemiş"
$attr = function($val) {
    if ($val === null || $val === '') return __('Belirtilmemiş');
    return trim((string)$val);
};
$attrArea = function($val) {
    if ($val === null || $val === '' || (float)$val <= 0) return __('Belirtilmemiş');
    return number_format((float)$val, 0, ',', '.');
};
$attrMoney = function($val) {
    if ($val === null || $val === '' || (float)$val < 0) return __('Belirtilmemiş');
    return number_format((float)$val, 0, ',', '.');
};

$realtorName = $realtorEmail = $realtorPhone = $realtorPhoto = $realtorBio = $realtorSlug = '';
if (!empty($listing['realtor_id']) && !empty($listing['realtor_first_name'])) {
    $realtorName = trim(($listing['realtor_first_name'] ?? '') . ' ' . ($listing['realtor_last_name'] ?? ''));
    $realtorEmail = $listing['realtor_email'] ?? '';
    $realtorPhone = $listing['realtor_phone'] ?? '';
    $realtorPhoto = function_exists('normalize_image_url') ? normalize_image_url($listing['realtor_photo'] ?? '') : ($listing['realtor_photo'] ?? '');
    $realtorBio = $listing['realtor_bio'] ?? '';
    $realtorSlug = $listing['realtor_slug'] ?? '';
}

if (!function_exists('normalize_image_url')) {
    function normalize_image_url($url) {
        if (empty($url)) return '';
        if (preg_match('/^https?:\/\//', $url)) return $url;
        $path = ltrim($url, '/');
        if (strpos($path, 'uploads/') !== 0) $path = 'uploads/' . $path;
        return function_exists('site_url') ? site_url($path) : '/' . $path;
    }
}

$galleryImages = [];
if (!empty($listing['gallery'])) {
    $galleryData = is_string($listing['gallery']) ? json_decode($listing['gallery'], true) : $listing['gallery'];
    if (is_array($galleryData)) $galleryImages = $galleryData;
}
$allImages = [];
if (!empty($listing['featured_image'])) {
    $allImages[] = normalize_image_url($listing['featured_image']);
}
foreach ($galleryImages as $img) {
    if (!empty($img)) {
        $n = normalize_image_url($img);
        if (!empty($n) && !in_array($n, $allImages)) $allImages[] = $n;
    }
}

$similarListings = [];
if (class_exists('RealEstateListingsModel')) {
    try {
        $model = new RealEstateListingsModel();
        $allPublished = $model->getPublished('', $propertyType, '', 4, 0);
        foreach ($allPublished as $similar) {
            if ($similar['id'] != $listing['id'] && count($similarListings) < 3) $similarListings[] = $similar;
        }
    } catch (Exception $e) { /* ignore */ }
}
if (class_exists('RealEstateListingsModel')) {
    try {
        (new RealEstateListingsModel())->incrementViews($listing['id']);
    } catch (Exception $e) { /* ignore */ }
}

$themeLoader = class_exists('ThemeLoader') ? ThemeLoader::getInstance() : null;
$primaryColor = $themeLoader ? $themeLoader->getColor('primary', '#bc1a1a') : '#bc1a1a';
$baseUrl = function_exists('localized_url') ? rtrim(localized_url(''), '/') : rtrim(site_url(''), '/');
$ilanlarUrl = function_exists('localized_url') ? localized_url('/ilanlar') : site_url('/ilanlar');
$contactUrl = function_exists('localized_url') ? localized_url('/contact') : site_url('/contact');

// İlan tarihi – Türkçe ay adı (Ocak, Şubat, ...)
$aylar = [1 => __('Ocak'), 2 => __('Şubat'), 3 => __('Mart'), 4 => __('Nisan'), 5 => __('Mayıs'), 6 => __('Haziran'), 7 => __('Temmuz'), 8 => __('Ağustos'), 9 => __('Eylül'), 10 => __('Ekim'), 11 => __('Kasım'), 12 => __('Aralık')];
$ilanTarihi = '';
if (!empty($listing['created_at'])) {
    $ts = strtotime($listing['created_at']);
    $ilanTarihi = date('j', $ts) . ' ' . ($aylar[(int)date('n', $ts)] ?? date('F', $ts)) . ' ' . date('Y', $ts);
}
// Konum: Sadece il + ilçe (tekrarsız, örn. İstanbul Kartal)
$locCity = trim($listing['city'] ?? '');
$locDistrict = trim($listing['district'] ?? '');
if ($locDistrict && $locDistrict !== $locCity) {
    $locationLine = $locCity . ' ' . $locDistrict;
} else {
    $locationLine = $locCity;
}
if ($locationLine === '') {
    $locationLine = trim($listing['location'] ?? '');
}

// WhatsApp link (wa.me için normalleştirilmiş numara)
$sitePhone = get_option('site_phone', '') ?: get_option('company_phone', '');
$contactPhone = $realtorPhone ?: $sitePhone;
$whatsappNumber = function_exists('normalize_phone_for_whatsapp') ? normalize_phone_for_whatsapp($contactPhone) : '';
$whatsappMessage = __('Merhaba,') . ' ' . ($listing['title'] ?? '') . ' ' . __('ilanı hakkında bilgi almak istiyorum.');
$whatsappUrl = $whatsappNumber ? ('https://wa.me/' . $whatsappNumber . '?text=' . urlencode($whatsappMessage)) : '';

// Harita: sitemizin harita sayfası; Sokak Görünümü: Google (enlem/boylam gerekir)
$haritaUrl = function_exists('localized_url') ? localized_url('/harita-ilanlar') : (function_exists('site_url') ? rtrim(site_url('/harita-ilanlar'), '/') : '/harita-ilanlar');
$lat = isset($listing['latitude']) && $listing['latitude'] !== '' ? (float)$listing['latitude'] : null;
$lng = isset($listing['longitude']) && $listing['longitude'] !== '' ? (float)$listing['longitude'] : null;
$streetViewUrl = ($lat !== null && $lng !== null) ? ('https://www.google.com/maps?layer=c&cbll=' . $lat . ',' . $lng . '&cbp=0,0,0,0,0') : '';

// Konum haritası: sadece bu ilanın konumu (harita modülünden bağımsız). API key opsiyonel olarak modül ayarından alınır.
$detailMapApiKey = '';
if (function_exists('get_module_settings')) {
    $mapSettings = get_module_settings('listings-map');
    $detailMapApiKey = trim($mapSettings['google_maps_api_key'] ?? '');
}
$detailMapTitle = $listing['title'] ?? __('İlan konumu');
?>
<style>
@media print {
    .cizgiaks-detail-actions, .cizgiaks-detail-gallery-bar button, .cizgiaks-detail-security-tips { display: none !important; }
    .cizgiaks-detail-page .cizgiaks-detail-right { position: static !important; }
}
/* Mobil: ilan başlığı ile paylaşım butonları üst üste binmesin */
@media (max-width: 768px) {
    .cizgiaks-detail-header {
        flex-direction: column !important;
        align-items: stretch !important;
    }
    .cizgiaks-detail-title-wrap {
        flex: none !important;
        width: 100% !important;
        min-width: 0 !important;
        padding-right: 0 !important;
    }
    .cizgiaks-detail-price-actions {
        width: 100% !important;
        flex-wrap: wrap !important;
        gap: 0.75rem !important;
    }
    .cizgiaks-detail-price {
        width: 100% !important;
    }
    .cizgiaks-detail-actions {
        width: 100% !important;
        flex-wrap: wrap !important;
    }
}
</style>
<section class="cizgiaks-listings-page cizgiaks-detail-page" style="padding-top:1rem; padding-bottom:2rem;">
    <div class="cizgiaks-container">
        <nav class="cizgiaks-agent-detail-breadcrumb" style="color:var(--cizgiaks-text-muted); font-size:0.875rem; margin-bottom:1rem;">
            <a href="<?php echo esc_url($baseUrl); ?>"><?php echo esc_html(__('Ana Sayfa')); ?></a> /
            <a href="<?php echo esc_url($ilanlarUrl); ?>"><?php echo esc_html(__('İlanlar')); ?></a> /
            <span><?php echo esc_html($listing['title']); ?></span>
        </nav>

        <!-- Üst: Başlık + İlan No | Fiyat + Aksiyonlar (sahibinden tarzı) -->
        <div class="cizgiaks-detail-header" style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:flex-start; gap:1rem; margin-bottom:1.5rem;">
            <div class="cizgiaks-detail-title-wrap" style="flex:1; min-width:0;">
                <h1 class="cizgiaks-detail-title" style="margin:0; font-size:1.35rem; font-weight:700; color:var(--cizgiaks-text); line-height:1.3;">
                    <?php echo esc_html($listing['title']); ?>
                </h1>
            </div>
            <div class="cizgiaks-detail-price-actions" style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
                <div class="cizgiaks-detail-price" style="font-size:1.5rem; font-weight:700; color:var(--cizgiaks-primary); white-space:nowrap;"><?php echo esc_html($formattedPrice); ?></div>
                <div class="cizgiaks-detail-actions" style="display:flex; align-items:center; gap:0.5rem; flex-shrink:0;">
                    <button type="button" onclick="window.print()" class="cizgiaks-detail-action-btn" title="<?php echo esc_attr(__('Yazdır')); ?>" style="display:inline-flex; align-items:center; gap:0.35rem; padding:0.4rem 0.6rem; border:1px solid #e5e7eb; background:#fff; border-radius:6px; cursor:pointer; font-size:0.8125rem; color:var(--cizgiaks-text-muted);">🖨 <?php echo esc_html(__('Yazdır')); ?></button>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($baseUrl . '/ilan/' . ($listing['slug'] ?? $listing['id'])); ?>" target="_blank" rel="noopener" class="cizgiaks-detail-action-btn" style="padding:0.4rem 0.5rem; border:1px solid #e5e7eb; background:#fff; border-radius:6px; font-size:0.875rem; color:#1877f2;">f</a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($baseUrl . '/ilan/' . ($listing['slug'] ?? $listing['id'])); ?>&text=<?php echo urlencode($listing['title']); ?>" target="_blank" rel="noopener" class="cizgiaks-detail-action-btn" style="padding:0.4rem 0.5rem; border:1px solid #e5e7eb; background:#fff; border-radius:6px; font-size:0.875rem; color:#000;">𝕏</a>
                    <?php
                    $shareUrl = $baseUrl . '/ilan/' . ($listing['slug'] ?? $listing['id']);
                    $shareLocation = $locationLine ?: ($listing['location'] ?? '');
                    $shareDetailsLine = $formattedPrice . ($shareLocation ? ' · ' . $shareLocation : '') . ' · ' . $emlakTipiLabel;
                    $shareTextWithDetails = ($listing['title'] ?? '') . "\n" . $shareDetailsLine . "\n" . $shareUrl;
                    $shareText = ($listing['title'] ?? '') . ' ' . $shareUrl;
                    $shareTextOneLine = ($listing['title'] ?? '') . ' · ' . $shareDetailsLine . ' ' . $shareUrl;
                    ?>
                    <a href="https://wa.me/?text=<?php echo urlencode($shareTextWithDetails); ?>" target="_blank" rel="noopener" class="cizgiaks-detail-action-btn" title="<?php echo esc_attr(__("WhatsApp'ta Paylaş")); ?>" style="padding:0.4rem 0.5rem; border:1px solid #e5e7eb; background:#fff; border-radius:6px; font-size:0.875rem; color:#25d366;" aria-label="<?php echo esc_attr(__("WhatsApp'ta Paylaş")); ?>"><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" style="display:block;"><path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg></a>
                    <button type="button" class="cizgiaks-detail-action-btn cizgiaks-detail-share-instagram" title="<?php echo esc_attr(__("Instagram'da Paylaş")); ?>" aria-label="<?php echo esc_attr(__("Instagram'da Paylaş")); ?>" data-share-url="<?php echo esc_attr($shareUrl); ?>" data-share-text="<?php echo esc_attr($shareTextOneLine); ?>" data-share-title="<?php echo esc_attr($listing['title'] ?? ''); ?>" data-share-price="<?php echo esc_attr($formattedPrice); ?>" data-share-location="<?php echo esc_attr($shareLocation); ?>" data-share-type="<?php echo esc_attr($emlakTipiLabel); ?>" data-share-image="<?php echo esc_attr(!empty($allImages[0]) ? $allImages[0] : ''); ?>" style="padding:0.4rem 0.5rem; border:1px solid #e5e7eb; background:#fff; border-radius:6px; font-size:0.875rem; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;"><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" style="display:block;"><defs><linearGradient id="ig-gradient" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#f9ed32"/><stop offset="50%" style="stop-color:#ee2a7b"/><stop offset="100%" style="stop-color:#002aff"/></linearGradient></defs><path fill="url(#ig-gradient)" d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg></button>
                    <a href="mailto:?subject=<?php echo urlencode($listing['title']); ?>&body=<?php echo urlencode($shareTextWithDetails); ?>" class="cizgiaks-detail-action-btn" style="padding:0.4rem 0.5rem; border:1px solid #e5e7eb; background:#fff; border-radius:6px; font-size:0.875rem; color:var(--cizgiaks-text-muted);">✉</a>
                </div>
            </div>
        </div>

        <!-- Ana iki sütun: Sol görsel | Sağ sidebar (emlakçı + ilan özellikleri) -->
        <div class="cizgiaks-detail-layout">
            <?php if ($locationLine): ?>
            <p class="cizgiaks-detail-location cizgiaks-detail-location-main" style="margin:0 0 1rem; font-size:0.9375rem; color:var(--cizgiaks-text-muted); grid-column:1 / -1;">📍 <?php echo esc_html($locationLine); ?></p>
            <?php endif; ?>
            <?php if (count($allImages) > 0): ?>
            <div class="cizgiaks-detail-left" style="min-width:0;">
                <!-- Büyük fotoğraf alanı -->
                <div class="cizgiaks-detail-gallery" style="margin-bottom:0.75rem;">
                    <div class="cizgiaks-detail-main-image-wrap" style="position:relative; aspect-ratio:16/10; background:#f3f4f6; border-radius:8px; overflow:hidden;">
                        <div class="cizgiaks-detail-image-actions">
                            <a href="#detail-konum-harita" class="cizgiaks-detail-image-action-btn cizgiaks-detail-scroll-to-map" title="<?php echo esc_attr(__('Harita')); ?>">🗺️ <?php echo esc_html(__('Harita')); ?></a>
                            <?php if ($streetViewUrl): ?>
                            <a href="#detail-konum-harita" class="cizgiaks-detail-image-action-btn cizgiaks-detail-scroll-to-map" data-view="sokak" title="<?php echo esc_attr(__('Sokak Görünümü')); ?>">👁️ <?php echo esc_html(__('Sokak Görünümü')); ?></a>
                            <?php endif; ?>
                        </div>
                        <img id="detail-main-img" src="<?php echo esc_url($allImages[0]); ?>" alt="<?php echo esc_attr($listing['title']); ?>" style="width:100%; height:100%; object-fit:cover;">
                        <?php if (count($allImages) > 1): ?>
                        <button type="button" onclick="detailPrev()" aria-label="<?php echo esc_attr(__('Önceki')); ?>" style="position:absolute; left:0.5rem; top:50%; transform:translateY(-50%); width:2.5rem; height:2.5rem; border-radius:50%; border:none; background:rgba(0,0,0,0.5); color:#fff; cursor:pointer; font-size:1.25rem;">‹</button>
                        <button type="button" onclick="detailNext()" aria-label="<?php echo esc_attr(__('Sonraki')); ?>" style="position:absolute; right:0.5rem; top:50%; transform:translateY(-50%); width:2.5rem; height:2.5rem; border-radius:50%; border:none; background:rgba(0,0,0,0.5); color:#fff; cursor:pointer; font-size:1.25rem;">›</button>
                        <span id="detail-counter" style="position:absolute; bottom:0.5rem; right:0.5rem; background:rgba(0,0,0,0.7); color:#fff; padding:0.25rem 0.5rem; border-radius:4px; font-size:0.8125rem;">1 / <?php echo count($allImages); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="cizgiaks-detail-gallery-bar" style="display:flex; align-items:center; gap:0.75rem; margin-top:0.5rem; flex-wrap:wrap;">
                        <button type="button" onclick="detailOpenLightbox()" style="display:inline-flex; align-items:center; gap:0.35rem; padding:0.35rem 0.6rem; border:1px solid #e5e7eb; background:#fff; border-radius:6px; cursor:pointer; font-size:0.8125rem;">🔍 <?php echo esc_html(__('Büyük Fotoğraf')); ?></button>
                        <span id="detail-photo-count" style="font-size:0.8125rem; color:var(--cizgiaks-text-muted);">1 / <?php echo count($allImages); ?> <?php echo esc_html(__('Fotoğraf')); ?></span>
                        <?php if (count($allImages) > 1): ?>
                        <div class="cizgiaks-detail-thumbs" style="display:flex; align-items:center; gap:0.25rem; overflow-x:auto; padding:0.25rem 0; flex:1; min-width:0;">
                            <button type="button" onclick="detailThumbPrev()" style="flex-shrink:0; width:1.75rem; height:1.75rem; border-radius:4px; border:1px solid #e5e7eb; background:#fff; cursor:pointer;">‹</button>
                            <div id="detail-thumb-strip" style="display:flex; gap:0.25rem; overflow:hidden;">
                                <?php foreach (array_slice($allImages, 0, 12) as $idx => $img): ?>
                                <button type="button" onclick="detailGo(<?php echo $idx; ?>)" class="detail-thumb <?php echo $idx === 0 ? 'active' : ''; ?>" style="flex-shrink:0; width:48px; height:36px; border-radius:4px; overflow:hidden; border:2px solid transparent; padding:0;"><img src="<?php echo esc_url($img); ?>" alt="" style="width:100%; height:100%; object-fit:cover;"></button>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" onclick="detailThumbNext()" style="flex-shrink:0; width:1.75rem; height:1.75rem; border-radius:4px; border:1px solid #e5e7eb; background:#fff; cursor:pointer;">›</button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="cizgiaks-detail-right cizgiaks-detail-sidebar" style="<?php echo count($allImages) > 0 ? '' : 'grid-column:1;'; ?>">
                <!-- Emlakçı kartı üstte: iletişim her zaman görünür -->
                <div class="cizgiaks-detail-agent-card">
                    <div class="cizgiaks-detail-agent-card-inner">
                        <div class="cizgiaks-detail-agent-photo-wrap">
                            <?php if (!empty($realtorPhoto)): ?>
                                <img src="<?php echo esc_url($realtorPhoto); ?>" alt="" class="cizgiaks-detail-agent-photo">
                            <?php else: ?>
                                <span class="cizgiaks-detail-agent-initials"><?php echo esc_html(mb_substr($realtorName ?: get_option('site_name', 'E'), 0, 1)); ?></span>
                            <?php endif; ?>
                        </div>
                        <h3 class="cizgiaks-detail-agent-name"><?php echo $realtorName ? esc_html($realtorName) : esc_html(get_option('site_name', __('İletişim'))); ?></h3>
                        <?php if (!empty($realtorBio)): ?>
                            <p class="cizgiaks-detail-agent-bio"><?php echo esc_html(mb_strlen($realtorBio) > 80 ? mb_substr($realtorBio, 0, 80) . '…' : $realtorBio); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($contactPhone)): ?>
                            <a href="tel:<?php echo esc_attr($contactPhone); ?>" class="cizgiaks-detail-agent-phone"><i class="fas fa-phone-alt" aria-hidden="true"></i> <?php echo esc_html($contactPhone); ?></a>
                        <?php endif; ?>
                        <?php if ($whatsappUrl): ?>
                            <a href="<?php echo esc_url($whatsappUrl); ?>" target="_blank" rel="noopener noreferrer" class="cizgiaks-detail-whatsapp-btn">
                                <svg class="cizgiaks-whatsapp-icon" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                <?php echo esc_html(__("WhatsApp'tan Yaz")); ?>
                            </a>
                        <?php else: ?>
                            <a href="<?php echo esc_url($contactUrl); ?>?ilan=<?php echo esc_attr($listing['id']); ?>" class="cizgiaks-detail-contact-btn" style="background:<?php echo esc_attr($primaryColor); ?>"><?php echo esc_html(__('İletişime Geç')); ?></a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- İlan özellikleri (sadece dolu alanlar gösterilir) -->
                <?php
                $belirtilmemis = __('Belirtilmemiş');
                $detailRows = [
                    ['label' => __('İlan No'), 'value' => !empty($listing['listing_no']) ? $listing['listing_no'] : $listing['id']],
                    ['label' => __('İlan Tarihi'), 'value' => $ilanTarihi ?: null],
                    ['label' => __('Emlak Tipi'), 'value' => $emlakTipiLabel],
                    ['label' => __('m² (Brüt)'), 'value' => $attrArea($listing['area'] ?? null) !== $belirtilmemis ? $attrArea($listing['area'] ?? null) : null],
                    ['label' => __('m² (Net)'), 'value' => $attrArea($listing['area_net'] ?? null) !== $belirtilmemis ? $attrArea($listing['area_net'] ?? null) : null],
                    ['label' => __('Oda Sayısı'), 'value' => $odaSayisi ?: null],
                    ['label' => __('Bina Yaşı'), 'value' => trim((string)($listing['building_age'] ?? '')) !== '' ? $attr($listing['building_age'] ?? null) : null],
                    ['label' => __('Bulunduğu Kat'), 'value' => isset($listing['floor']) && $listing['floor'] !== '' && $listing['floor'] !== null ? $attr($listing['floor']) : null],
                    ['label' => __('Kat Sayısı'), 'value' => isset($listing['total_floors']) && $listing['total_floors'] !== '' && $listing['total_floors'] !== null ? $attr($listing['total_floors']) : null],
                    ['label' => __('Isıtma'), 'value' => trim((string)($listing['heating'] ?? '')) !== '' ? $attr($listing['heating'] ?? null) : null],
                    ['label' => __('Banyo Sayısı'), 'value' => $listing['bathrooms'] !== null && $listing['bathrooms'] !== '' ? (string)$listing['bathrooms'] : null],
                    ['label' => __('Mutfak'), 'value' => trim((string)($listing['kitchen'] ?? '')) !== '' ? $attr($listing['kitchen'] ?? null) : null],
                    ['label' => __('Balkon'), 'value' => trim((string)($listing['balcony'] ?? '')) !== '' ? $attr($listing['balcony'] ?? null) : null],
                    ['label' => __('Asansör'), 'value' => trim((string)($listing['elevator'] ?? '')) !== '' ? $attr($listing['elevator'] ?? null) : null],
                    ['label' => __('Otopark'), 'value' => trim((string)($listing['parking'] ?? '')) !== '' ? $attr($listing['parking'] ?? null) : null],
                    ['label' => __('Eşyalı'), 'value' => trim((string)($listing['furnished'] ?? '')) !== '' ? $attr($listing['furnished'] ?? null) : null],
                    ['label' => __('Kullanım Durumu'), 'value' => trim((string)($listing['usage_status'] ?? '')) !== '' ? $attr($listing['usage_status'] ?? null) : null],
                    ['label' => __('Site İçerisinde'), 'value' => trim((string)($listing['in_complex'] ?? '')) !== '' ? $attr($listing['in_complex'] ?? null) : null],
                    ['label' => __('Site Adı'), 'value' => trim((string)($listing['complex_name'] ?? '')) !== '' ? $attr($listing['complex_name'] ?? null) : null],
                    ['label' => __('Aidat (TL)'), 'value' => $attrMoney($listing['monthly_dues'] ?? null) !== $belirtilmemis ? $attrMoney($listing['monthly_dues'] ?? null) : null],
                    ['label' => __('Krediye Uygun'), 'value' => trim((string)($listing['loan_eligible'] ?? '')) !== '' ? $attr($listing['loan_eligible'] ?? null) : null],
                    ['label' => __('Tapu Durumu'), 'value' => trim((string)($listing['deed_status'] ?? '')) !== '' ? $attr($listing['deed_status'] ?? null) : null],
                    ['label' => __('Kimden'), 'value' => trim((string)($listing['listed_by'] ?? '')) !== '' ? $attr($listing['listed_by'] ?? null) : null],
                    ['label' => __('Takas'), 'value' => trim((string)($listing['takas'] ?? '')) !== '' ? $attr($listing['takas'] ?? null) : null],
                ];
                $detailRows = array_filter($detailRows, function ($r) { return $r['value'] !== null && $r['value'] !== ''; });
                ?>
                <div class="cizgiaks-detail-attributes cizgiaks-detail-sidebar-attributes">
                    <h4 class="cizgiaks-detail-attributes-title"><?php echo esc_html(__('İlan Özellikleri')); ?></h4>
                    <div class="cizgiaks-detail-attributes-body">
                    <?php if (!empty($detailRows)): ?>
                    <table class="cizgiaks-detail-table">
                        <tbody>
                            <?php foreach ($detailRows as $row): ?>
                            <tr><td class="cizgiaks-detail-td-label"><?php echo esc_html($row['label']); ?></td><td class="cizgiaks-detail-td-value"><?php echo esc_html($row['value']); ?></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p class="cizgiaks-detail-no-attributes text-sm text-gray-500 dark:text-gray-400"><?php echo esc_html(__('Henüz özellik eklenmemiş.')); ?></p>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Açıklama -->
        <?php if (!empty($listing['description'])): ?>
        <div class="cizgiaks-agent-detail-block cizgiaks-detail-description-block">
            <h2 class="cizgiaks-detail-section-title"><?php echo esc_html(__('Açıklama')); ?></h2>
            <div class="cizgiaks-detail-description-prose">
                <?php
                $desc = trim($listing['description']);
                $paras = array_filter(array_map('trim', preg_split('/\n\s*\n/', $desc, -1, PREG_SPLIT_NO_EMPTY)));
                if (count($paras) > 1): foreach ($paras as $p): ?>
                    <p><?php echo nl2br(esc_html($p)); ?></p>
                <?php endforeach; else: ?>
                    <p><?php echo nl2br(esc_html($desc)); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Konum haritası: Harita / Uydu / Sokak Görünümü sekmeleri -->
        <?php if ($lat !== null && $lng !== null): ?>
        <div id="detail-konum-harita" class="cizgiaks-detail-map-embed cizgiaks-agent-detail-block" style="scroll-margin-top: 1rem;">
            <h2 class="cizgiaks-detail-section-title"><?php echo esc_html(__('Konum')); ?></h2>
            <div class="cizgiaks-detail-map-tabs" style="display:flex; gap:0; margin-bottom:0; border-bottom:1px solid #e5e7eb;">
                <button type="button" class="cizgiaks-detail-map-tab active" data-view="harita" style="padding:0.5rem 1rem; border:none; background:#f3f4f6; color:var(--cizgiaks-text-muted); font-size:0.875rem; cursor:pointer; border-radius:6px 6px 0 0; margin-bottom:-1px; border-bottom:2px solid transparent;"><?php echo esc_html(__('Harita')); ?></button>
                <button type="button" class="cizgiaks-detail-map-tab" data-view="uydu" style="padding:0.5rem 1rem; border:none; background:transparent; color:var(--cizgiaks-text-muted); font-size:0.875rem; cursor:pointer; border-radius:6px 6px 0 0; margin-bottom:-1px; border-bottom:2px solid transparent;"><?php echo esc_html(__('Uydu')); ?></button>
                <button type="button" class="cizgiaks-detail-map-tab" data-view="sokak" style="padding:0.5rem 1rem; border:none; background:transparent; color:var(--cizgiaks-text-muted); font-size:0.875rem; cursor:pointer; border-radius:6px 6px 0 0; margin-bottom:-1px; border-bottom:2px solid transparent;"><?php echo esc_html(__('Sokak Görünümü')); ?></button>
            </div>
            <div class="cizgiaks-detail-map-inline" style="width:100%; height:400px; border-radius:0 8px 8px 8px; overflow:hidden; border:1px solid #e5e7eb; border-top:none; background:#f3f4f6;">
                <div id="detail-listing-map-wrap" style="width:100%; height:100%; position:relative;">
                    <div id="detail-listing-map-canvas" style="width:100%; height:100%;"></div>
                    <div id="detail-listing-streetview" style="display:none; width:100%; height:100%; position:absolute; top:0; left:0; pointer-events:auto;"></div>
                    <div id="detail-listing-streetview-unavailable" style="display:none; width:100%; height:100%; position:absolute; top:0; left:0; align-items:center; justify-content:center; background:#f3f4f6; color:var(--cizgiaks-text-muted); font-size:0.9375rem; padding:1.5rem; text-align:center; box-sizing:border-box;"><?php echo esc_html(__('Bu konum için sokak görünümü mevcut değil.')); ?></div>
                </div>
            </div>
            <p class="cizgiaks-detail-map-hint" style="margin:0.35rem 0 0; font-size:0.8125rem; color:var(--cizgiaks-text-muted);"><?php echo esc_html(__('Sokak Görünümü sekmesinde sürükleyerek bakabilir, mavi noktalara tıklayarak ilerleyebilirsiniz. Haritada sarı adam ikonunu sokağa bırakarak da sokak görünümüne geçebilirsiniz.')); ?></p>
            <?php if (!empty($detailMapApiKey)): ?>
            <script>
            (function() {
                var apiKey = <?php echo json_encode($detailMapApiKey); ?>;
                var lat = <?php echo json_encode($lat); ?>;
                var lng = <?php echo json_encode($lng); ?>;
                var title = <?php echo json_encode($detailMapTitle, JSON_UNESCAPED_UNICODE); ?>;
                var mapEl = document.getElementById('detail-listing-map-canvas');
                var svEl = document.getElementById('detail-listing-streetview');
                var svUnavailEl = document.getElementById('detail-listing-streetview-unavailable');
                if (!mapEl || !apiKey) return;
                var map = null;
                var mapPanorama = null;
                var tabPanorama = null;
                var streetViewAvailable = false;
                var streetViewPosition = null;
                function setTabActive(view) {
                    document.querySelectorAll('.cizgiaks-detail-map-tab').forEach(function(t) {
                        var isActive = t.getAttribute('data-view') === view;
                        t.classList.toggle('active', isActive);
                        t.style.background = isActive ? '#f3f4f6' : 'transparent';
                        t.style.color = isActive ? 'var(--cizgiaks-primary)' : 'var(--cizgiaks-text-muted)';
                        t.style.borderBottomColor = isActive ? 'var(--cizgiaks-primary)' : 'transparent';
                        t.style.fontWeight = isActive ? '600' : '400';
                    });
                    mapEl.style.display = (view === 'harita' || view === 'uydu') ? 'block' : 'none';
                    if (svEl) svEl.style.display = (view === 'sokak' && streetViewAvailable) ? 'block' : 'none';
                    if (svUnavailEl) svUnavailEl.style.display = (view === 'sokak' && !streetViewAvailable) ? 'flex' : 'none';
                    if (map) map.setMapTypeId(view === 'uydu' ? 'hybrid' : 'roadmap');
                    if (view === 'sokak' && streetViewAvailable && streetViewPosition && svEl) {
                        if (!tabPanorama) {
                            tabPanorama = new google.maps.StreetViewPanorama(svEl, {
                                position: streetViewPosition,
                                pov: { heading: 0, pitch: 0 },
                                zoom: 1,
                                addressControl: true,
                                linksControl: true,
                                fullscreenControl: true,
                                enableCloseButton: false,
                                scrollwheel: true,
                                clickToGo: true,
                                gestureHandling: 'greedy',
                                motionTracking: true,
                                motionTrackingControl: true
                            });
                        } else {
                            tabPanorama.setPosition(streetViewPosition);
                            tabPanorama.setPov({ heading: 0, pitch: 0 });
                            tabPanorama.setZoom(1);
                        }
                    }
                }
                function init() {
                    if (typeof google === 'undefined' || !google.maps) return;
                    var center = { lat: parseFloat(lat), lng: parseFloat(lng) };
                    mapPanorama = new google.maps.StreetViewPanorama(null, {
                        linksControl: true,
                        fullscreenControl: true,
                        addressControl: true,
                        enableCloseButton: true,
                        scrollwheel: true,
                        clickToGo: true,
                        gestureHandling: 'greedy',
                        motionTracking: true,
                        motionTrackingControl: true
                    });
                    map = new google.maps.Map(mapEl, {
                        center: center,
                        zoom: 16,
                        mapTypeControl: false,
                        fullscreenControl: true,
                        zoomControl: true,
                        streetViewControl: true,
                        gestureHandling: 'greedy'
                    });
                    map.setStreetView(mapPanorama);
                    new google.maps.Marker({
                        position: center,
                        map: map,
                        title: title
                    });
                    var svService = new google.maps.StreetViewService();
                    var radii = [200, 500, 1000, 2000];
                    function tryNextRadius(i) {
                        if (streetViewAvailable || i >= radii.length) return;
                        svService.getPanorama({ location: center, radius: radii[i] }, function(data, status) {
                            if (status === google.maps.StreetViewStatus.OK && data && data.location && data.location.latLng) {
                                streetViewAvailable = true;
                                streetViewPosition = data.location.latLng;
                                var activeView = document.querySelector('.cizgiaks-detail-map-tab.active');
                                if (activeView && activeView.getAttribute('data-view') === 'sokak') setTabActive('sokak');
                            } else {
                                tryNextRadius(i + 1);
                            }
                        });
                    }
                    tryNextRadius(0);
                    document.querySelectorAll('.cizgiaks-detail-map-tab').forEach(function(btn) {
                        btn.addEventListener('click', function() {
                            setTabActive(btn.getAttribute('data-view'));
                        });
                    });
                }
                if (window.google && window.google.maps) {
                    init();
                    return;
                }
                window._detailListingMapInit = init;
                var s = document.createElement('script');
                s.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(apiKey) + '&callback=_detailListingMapInit&gestureHandling=greedy';
                s.async = true;
                s.defer = true;
                document.head.appendChild(s);
            })();
            </script>
            <?php else: ?>
            <p style="margin:0.5rem 0 0; font-size:0.875rem; color:var(--cizgiaks-text-muted);"><?php echo esc_html(__('Harita görüntülemek için yönetici panelinde Harita İlanlar modülü ayarlarına Google Maps API key girin.')); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Benzer İlanlar -->
        <?php if (!empty($similarListings)): ?>
        <div class="cizgiaks-agent-detail-block">
            <h2 class="cizgiaks-detail-section-title"><?php echo esc_html(__('Benzer İlanlar')); ?></h2>
            <div class="cizgiaks-listings-grid cizgiaks-listings-grid--3" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:1rem;">
                <?php foreach ($similarListings as $similar):
                    $similarSlug = !empty($similar['slug']) ? $similar['slug'] : $similar['id'];
                    $similarUrl = $baseUrl . '/ilan/' . $similarSlug;
                    $similarPrice = number_format($similar['price'] ?? 0, 0, ',', '.') . ' TL';
                    $simImg = !empty($similar['featured_image']) ? normalize_image_url($similar['featured_image']) : '';
                ?>
                <a href="<?php echo esc_url($similarUrl); ?>" class="cizgiaks-listing-card cizgiaks-listing-card--vitrin" style="text-decoration:none; color:inherit; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;">
                    <div class="cizgiaks-listing-card-image" style="aspect-ratio:16/10;"><img src="<?php echo esc_url($simImg ?: 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=400'); ?>" alt="<?php echo esc_attr($similar['title']); ?>" loading="lazy" style="width:100%; height:100%; object-fit:cover;"></div>
                    <div class="cizgiaks-listing-card-body" style="padding:0.75rem;">
                        <div class="cizgiaks-listing-price" style="font-weight:700; color:var(--cizgiaks-primary);"><?php echo esc_html($similarPrice); ?></div>
                        <h3 class="cizgiaks-listing-title" style="margin:0.25rem 0 0; font-size:0.9375rem;"><?php echo esc_html($similar['title']); ?></h3>
                        <div class="cizgiaks-listing-location" style="font-size:0.8125rem; color:var(--cizgiaks-text-muted);"><?php echo esc_html($similar['location'] ?? ''); ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php if (count($allImages) > 1): ?>
<!-- Lightbox -->
<div id="detail-lightbox" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.9); z-index:9999; align-items:center; justify-content:center;" onclick="detailCloseLightbox(event)">
    <button type="button" onclick="detailCloseLightbox()" style="position:absolute; top:1rem; right:1rem; width:2.5rem; height:2.5rem; border:none; background:rgba(255,255,255,0.2); color:#fff; border-radius:50%; cursor:pointer; font-size:1.25rem;">×</button>
    <button type="button" onclick="event.stopPropagation(); detailPrev(); detailUpdateLightbox();" style="position:absolute; left:1rem; top:50%; transform:translateY(-50%); width:3rem; height:3rem; border:none; background:rgba(255,255,255,0.2); color:#fff; border-radius:50%; cursor:pointer; font-size:1.5rem;">‹</button>
    <img id="detail-lightbox-img" src="" alt="" style="max-width:90%; max-height:90%; object-fit:contain;" onclick="event.stopPropagation()">
    <button type="button" onclick="event.stopPropagation(); detailNext(); detailUpdateLightbox();" style="position:absolute; right:1rem; top:50%; transform:translateY(-50%); width:3rem; height:3rem; border:none; background:rgba(255,255,255,0.2); color:#fff; border-radius:50%; cursor:pointer; font-size:1.5rem;">›</button>
    <span id="detail-lightbox-counter" style="position:absolute; bottom:1rem; left:50%; transform:translateX(-50%); background:rgba(0,0,0,0.7); color:#fff; padding:0.35rem 0.75rem; border-radius:6px; font-size:0.875rem;">1 / <?php echo count($allImages); ?></span>
</div>
<?php endif; ?>

<script>
(function() {
    var detailImages = <?php echo json_encode($allImages); ?>;
    var detailIdx = 0;

    function detailShow() {
        var img = document.getElementById('detail-main-img');
        var c = document.getElementById('detail-counter');
        var pc = document.getElementById('detail-photo-count');
        if (img && detailImages.length) img.src = detailImages[detailIdx];
        if (c) c.textContent = (detailIdx + 1) + ' / ' + detailImages.length;
        if (pc) pc.textContent = (detailIdx + 1) + ' / ' + detailImages.length + ' <?php echo esc_js(__('Fotoğraf')); ?>';
        document.querySelectorAll('.detail-thumb').forEach(function(el, i) {
            el.classList.toggle('active', i === detailIdx);
            el.style.borderColor = i === detailIdx ? '<?php echo esc_js($primaryColor); ?>' : 'transparent';
        });
    }
    window.detailNext = function() { detailIdx = (detailIdx + 1) % detailImages.length; detailShow(); };
    window.detailPrev = function() { detailIdx = (detailIdx - 1 + detailImages.length) % detailImages.length; detailShow(); };
    window.detailGo = function(i) { detailIdx = i; detailShow(); };
    window.detailThumbPrev = function() { detailPrev(); };
    window.detailThumbNext = function() { detailNext(); };

    window.detailOpenLightbox = function() {
        if (detailImages.length === 0) return;
        var lb = document.getElementById('detail-lightbox');
        if (lb) { lb.style.display = 'flex'; detailUpdateLightbox(); }
    };
    window.detailCloseLightbox = function(e) {
        if (e && e.target !== e.currentTarget) return;
        var lb = document.getElementById('detail-lightbox');
        if (lb) lb.style.display = 'none';
    };
    window.detailUpdateLightbox = function() {
        var limg = document.getElementById('detail-lightbox-img');
        var lc = document.getElementById('detail-lightbox-counter');
        if (limg && detailImages.length) limg.src = detailImages[detailIdx];
        if (lc) lc.textContent = (detailIdx + 1) + ' / ' + detailImages.length;
    };

    document.querySelectorAll('.detail-thumb').forEach(function(el, i) {
        el.addEventListener('click', function() { detailGo(i); });
    });
})();
</script>

<script>
(function() {
    function showTip(message) {
        var tip = document.createElement('span');
        tip.textContent = message;
        tip.style.cssText = 'position:fixed; bottom:1.5rem; left:50%; transform:translateX(-50%); background:#333; color:#fff; padding:0.5rem 1rem; border-radius:8px; font-size:0.875rem; z-index:9999; white-space:nowrap; box-shadow:0 4px 12px rgba(0,0,0,0.2);';
        document.body.appendChild(tip);
        setTimeout(function() { tip.remove(); }, 2500);
    }
    function copyFallback(url, message) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function() { showTip(message); });
        } else {
            var inp = document.createElement('input');
            inp.value = url;
            inp.setAttribute('readonly', '');
            inp.style.cssText = 'position:absolute; left:-9999px;';
            document.body.appendChild(inp);
            inp.select();
            try { document.execCommand('copy'); } catch (e) {}
            inp.remove();
            showTip(message);
        }
    }
    var msgCopied = <?php echo json_encode(__("Link kopyalandı! Instagram'da yapıştırabilirsiniz.")); ?>;
    document.querySelectorAll('.cizgiaks-detail-share-instagram').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var url = btn.getAttribute('data-share-url') || '';
            var title = btn.getAttribute('data-share-title') || '';
            var price = btn.getAttribute('data-share-price') || '';
            var location = btn.getAttribute('data-share-location') || '';
            var typeLabel = btn.getAttribute('data-share-type') || '';
            var imageUrl = btn.getAttribute('data-share-image') || '';
            var text = btn.getAttribute('data-share-text') || '';
            var detailsLine = [price, location, typeLabel].filter(Boolean).join(' · ');
            var fullText = (title ? title + '\n' : '') + (detailsLine ? detailsLine + '\n' : '') + url;
            if (!url) return;
            function doShare(shareData) {
                if (!navigator.share || typeof navigator.share !== 'function') {
                    copyFallback(url, msgCopied);
                    return;
                }
                navigator.share(shareData).then(function() {}).catch(function(err) {
                    if (err.name !== 'AbortError') copyFallback(url, msgCopied);
                });
            }
            if (imageUrl) {
                fetch(imageUrl, { mode: 'cors' }).then(function(r) { return r.blob(); }).then(function(blob) {
                    var file = new File([blob], 'ilan.jpg', { type: blob.type || 'image/jpeg' });
                    var shareWithImage = { title: title, text: fullText, files: [file] };
                    if (navigator.canShare && navigator.canShare(shareWithImage)) {
                        doShare(shareWithImage);
                    } else {
                        doShare({ title: title, text: fullText, url: url });
                    }
                }).catch(function() {
                    doShare({ title: title, text: fullText, url: url });
                });
            } else {
                doShare({ title: title, text: fullText, url: url });
            }
        });
    });
})();
</script>

<?php if ($lat !== null && $lng !== null): ?>
<script>
(function() {
    document.querySelectorAll('.cizgiaks-detail-scroll-to-map').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var el = document.getElementById('detail-konum-harita');
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            var view = link.getAttribute('data-view');
            if (view) setTimeout(function() {
                var tab = document.querySelector('.cizgiaks-detail-map-tab[data-view="' + view + '"]');
                if (tab) tab.click();
            }, 500);
        });
    });
})();
</script>
<?php endif; ?>

<?php get_footer(); ?>
