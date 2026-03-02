<?php
/**
 * Harita Üzerinde Tüm İlanlar – Frontend view
 * $listings, $filter_options, $filters, $google_maps_api_key, $default_lat, $default_lng, $default_zoom, $detail_url_base
 */
$listings = $listings ?? [];
$filter_options = $filter_options ?? ['cities' => [], 'districts' => [], 'neighborhoods' => [], 'locations' => []];
$filters = $filters ?? ['city' => '', 'district' => '', 'neighborhood' => '', 'location' => ''];
$google_maps_api_key = $google_maps_api_key ?? '';
$default_lat = isset($default_lat) ? (float) $default_lat : 39.0;
$default_lng = isset($default_lng) ? (float) $default_lng : 35.0;
$default_zoom = isset($default_zoom) ? (int) $default_zoom : 6;
$detail_url_base = $detail_url_base ?? '/ilan/';
$theme_colors = $theme_colors ?? ['primary' => '#bc1a1a', 'secondary' => '#1f2937', 'accent' => '#9a1615', 'text' => '#1f2937', 'text_muted' => '#6b7280'];
$primary = $theme_colors['primary'] ?? '#bc1a1a';
$accent = $theme_colors['accent'] ?? '#9a1615';

$property_type_labels = [
    'house' => 'Müstakil Ev',
    'apartment' => 'Daire',
    'villa' => 'Villa',
    'commercial' => 'Ticari',
    'land' => 'Arsa',
];
$status_labels = ['sale' => 'Satılık', 'rent' => 'Kiralık'];

$listings_for_map = [];
$used_positions = [];
foreach ($listings as $row) {
    $lat = isset($row['latitude']) ? (float) $row['latitude'] : null;
    $lng = isset($row['longitude']) ? (float) $row['longitude'] : null;
    if ($lat === null || $lng === null || ($lat == 0 && $lng == 0)) {
        continue;
    }
    $key = round($lat, 5) . ',' . round($lng, 5);
    if (isset($used_positions[$key])) {
        $used_positions[$key]++;
        $offset = $used_positions[$key];
        $lat += ($offset * 0.00015 * cos($offset * 0.7));
        $lng += ($offset * 0.0002 * sin($offset * 0.5));
    } else {
        $used_positions[$key] = 0;
    }
    $typeLabel = $property_type_labels[$row['property_type'] ?? ''] ?? $row['property_type'];
    $statusLabel = $status_labels[$row['listing_status'] ?? ''] ?? '';
    $label = trim($statusLabel . ' ' . $typeLabel);
    $price = isset($row['price']) ? number_format((float) $row['price'], 0, ',', '.') . ' ₺' : '';
    $listings_for_map[] = [
        'id' => (int) $row['id'],
        'title' => $row['title'] ?? '',
        'slug' => $row['slug'] ?? '',
        'lat' => $lat,
        'lng' => $lng,
        'price' => $price,
        'label' => $label,
        'listing_status' => $row['listing_status'] ?? 'sale',
        'detail_url' => $detail_url_base . (isset($row['slug']) ? rawurlencode($row['slug']) : (int) $row['id']),
    ];
}
$map_listings_json = json_encode($listings_for_map, JSON_UNESCAPED_UNICODE);

$harita_url = function_exists('localized_url') ? localized_url('/harita-ilanlar') : (function_exists('site_url') ? rtrim(site_url('/harita-ilanlar'), '/') : '/harita-ilanlar');
?>
<section class="listings-map-page">
    <div class="cizgiaks-container">
        <div class="listings-map-header">
            <h1 class="listings-map-title">
                <span class="listings-map-title-icon" aria-hidden="true">📍</span>
                Harita Üzerinde Tüm İlanlar
            </h1>

            <div class="listings-map-filters">
                <form action="<?php echo esc_url($harita_url); ?>" method="get" class="listings-map-filter-form">
                    <select name="city" id="listings-map-city" class="listings-map-select">
                        <option value="">İl Seçiniz</option>
                        <?php foreach ($filter_options['cities'] as $c): ?>
                            <option value="<?php echo esc_attr($c); ?>" <?php echo ($filters['city'] ?? '') === $c ? 'selected' : ''; ?>><?php echo esc_html($c); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="district" id="listings-map-district" class="listings-map-select">
                        <option value="">İlçe Seçiniz</option>
                        <?php foreach ($filter_options['districts'] as $d): ?>
                            <option value="<?php echo esc_attr($d); ?>" <?php echo ($filters['district'] ?? '') === $d ? 'selected' : ''; ?>><?php echo esc_html($d); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="neighborhood" id="listings-map-neighborhood" class="listings-map-select">
                        <option value="">Mahalle Seçiniz</option>
                        <?php foreach ($filter_options['neighborhoods'] as $n): ?>
                            <option value="<?php echo esc_attr($n); ?>" <?php echo ($filters['neighborhood'] ?? '') === $n ? 'selected' : ''; ?>><?php echo esc_html($n); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="listings-map-filter-btn">Uygula</button>
                </form>
            </div>
        </div>

        <?php if (empty($listings_for_map)): ?>
        <div class="listings-map-empty-notice">
            <p>Haritada görüntülenecek ilan yok. İlanların haritada görünmesi için enlem/boylam gerekir. Yönetici panelinde <strong>Harita İlanlar → Ayarlar</strong> sayfasından &quot;Eksik konumları adresten doldur&quot; ile Konum alanı dolu ilanları işaretleyebilir veya her ilanı düzenleyip &quot;Adresten konum al&quot; kullanabilirsiniz.</p>
        </div>
        <?php endif; ?>

        <div class="listings-map-search-row">
            <div class="listings-map-search-wrap">
                <input type="text" id="listings-map-address" class="listings-map-search-input" placeholder="Adres ya da yer" aria-label="Adres ya da yer">
                <button type="button" id="listings-map-search-btn" class="listings-map-search-btn">Bul</button>
            </div>
        </div>

        <div class="listings-map-container">
            <div id="listings-map-canvas" class="listings-map-canvas"></div>
        </div>

        <div class="listings-map-footer-note">
            <a href="https://www.google.com/maps" target="_blank" rel="noopener noreferrer" class="listings-map-google-link">Google Haritalar'da aç</a>
        </div>
    </div>
</section>

<style>
body.listings-map-standalone { margin: 0; font-family: system-ui, -apple-system, sans-serif; }
.listings-map-page { padding: 1rem; max-width: 100%; box-sizing: border-box; }
.listings-map-header { margin-bottom: 1rem; }
.listings-map-title { font-size: 1.5rem; font-weight: 700; margin: 0 0 0.75rem; display: flex; align-items: center; gap: 0.5rem; }
.listings-map-title-icon { font-size: 1.25rem; }
.listings-map-filters { margin-bottom: 0.75rem; }
.listings-map-filter-form { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; }
.listings-map-filter-label { font-weight: 500; margin-right: 0.25rem; }
.listings-map-select { padding: 0.5rem 0.75rem; border: 1px solid #ccc; border-radius: 0.375rem; min-width: 140px; background: #fff; }
.listings-map-filter-btn { padding: 0.5rem 1rem; background: <?php echo esc_attr($primary); ?>; color: #fff; border: none; border-radius: 0.375rem; cursor: pointer; font-weight: 500; }
.listings-map-filter-btn:hover { background: <?php echo esc_attr($accent); ?>; }
.listings-map-search-row { margin-bottom: 0.75rem; }
.listings-map-search-wrap { display: flex; max-width: 400px; }
.listings-map-search-input { flex: 1; padding: 0.5rem 0.75rem; border: 1px solid #ccc; border-radius: 0.375rem 0 0 0.375rem; border-right: 0; }
.listings-map-search-btn { padding: 0.5rem 1rem; background: <?php echo esc_attr($primary); ?>; color: #fff; border: 1px solid <?php echo esc_attr($primary); ?>; border-radius: 0 0.375rem 0.375rem 0; cursor: pointer; }
.listings-map-search-btn:hover { background: <?php echo esc_attr($accent); ?>; }
.listings-map-container { position: relative; width: 100%; height: 70vh; min-height: 400px; border-radius: 0.5rem; overflow: hidden; border: 1px solid #ddd; }
.listings-map-canvas { width: 100%; height: 100%; }
.listings-map-footer-note { margin-top: 0.75rem; font-size: 0.875rem; }
.listings-map-google-link { color: <?php echo esc_attr($primary); ?>; text-decoration: none; }
.listings-map-google-link:hover { text-decoration: underline; }
.listings-map-empty-notice { margin-bottom: 1rem; padding: 1rem; background: #fef3c7; border: 1px solid #f59e0b; border-radius: 0.5rem; font-size: 0.875rem; color: #92400e; }
.listings-map-empty-notice p { margin: 0; }
</style>

<script>
window.listingsMapConfig = {
    apiKey: <?php echo json_encode($google_maps_api_key); ?>,
    defaultLat: <?php echo json_encode($default_lat); ?>,
    defaultLng: <?php echo json_encode($default_lng); ?>,
    defaultZoom: <?php echo json_encode($default_zoom); ?>,
    listings: <?php echo $map_listings_json; ?>,
    colors: <?php echo json_encode($theme_colors); ?>
};
</script>
<?php if (!empty($google_maps_api_key)): ?>
<?php
$map_js_path = dirname(__DIR__, 2) . '/assets/map.js';
if (is_file($map_js_path)) {
    echo '<script>' . "\n" . file_get_contents($map_js_path) . "\n" . '</script>';
} else {
    echo '<script src="' . esc_url(function_exists('site_url') ? rtrim(site_url(), '/') . '/module-asset/listings-map/map.js' : '/module-asset/listings-map/map.js') . '"></script>';
}
?>
<?php else: ?>
<script>
(function(){ var el = document.getElementById('listings-map-canvas'); if (el) el.innerHTML = '<div style="padding:2rem;text-align:center;color:#666;">Harita için yönetici panelinden Harita İlanlar modülü ayarlarına Google Maps API key girin.</div>'; })();
</script>
<?php endif; ?>
