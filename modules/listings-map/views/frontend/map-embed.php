<?php
/**
 * Harita modülü – Sadece harita (embed: iframe için)
 * Aynı $listings, $default_lat, $default_lng, $default_zoom, $google_maps_api_key, $detail_url_base, $theme_colors
 */
$listings = $listings ?? [];
$google_maps_api_key = $google_maps_api_key ?? '';
$default_lat = isset($default_lat) ? (float) $default_lat : 39.0;
$default_lng = isset($default_lng) ? (float) $default_lng : 35.0;
$default_zoom = isset($default_zoom) ? (int) $default_zoom : 6;
$map_type = isset($map_type) && $map_type === 'satellite' ? 'satellite' : 'roadmap';
$detail_url_base = $detail_url_base ?? '/ilan/';
$theme_colors = $theme_colors ?? ['primary' => '#bc1a1a', 'secondary' => '#1f2937', 'accent' => '#9a1615', 'text' => '#1f2937', 'text_muted' => '#6b7280'];

$property_type_labels = ['house' => 'Müstakil Ev', 'apartment' => 'Daire', 'villa' => 'Villa', 'commercial' => 'Ticari', 'land' => 'Arsa'];
$status_labels = ['sale' => 'Satılık', 'rent' => 'Kiralık'];

$listings_for_map = [];
$used_positions = [];
foreach ($listings as $row) {
    $lat = isset($row['latitude']) ? (float) $row['latitude'] : null;
    $lng = isset($row['longitude']) ? (float) $row['longitude'] : null;
    if ($lat === null || $lng === null || ($lat == 0 && $lng == 0)) continue;
    $key = round($lat, 5) . ',' . round($lng, 5);
    if (isset($used_positions[$key])) {
        $used_positions[$key]++;
        $offset = $used_positions[$key];
        // Aynı noktadaki iğneleri dağıt (çakışma azalsın) – daha belirgin açılım
        $lat += ($offset * 0.00035 * cos($offset * 0.7));
        $lng += ($offset * 0.00045 * sin($offset * 0.5));
    } else {
        $used_positions[$key] = 0;
    }
    $typeLabel = $property_type_labels[$row['property_type'] ?? ''] ?? $row['property_type'];
    $statusLabel = $status_labels[$row['listing_status'] ?? ''] ?? '';
    $listings_for_map[] = [
        'id' => (int) $row['id'],
        'title' => $row['title'] ?? '',
        'slug' => $row['slug'] ?? '',
        'lat' => $lat,
        'lng' => $lng,
        'price' => isset($row['price']) ? number_format((float) $row['price'], 0, ',', '.') . ' ₺' : '',
        'label' => trim($statusLabel . ' ' . $typeLabel),
        'listing_status' => $row['listing_status'] ?? 'sale',
        'detail_url' => $detail_url_base . (isset($row['slug']) ? rawurlencode($row['slug']) : (int) $row['id']),
    ];
}
$map_listings_json = json_encode($listings_for_map, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Harita</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { width: 100%; height: 100%; }
        .listings-map-container { position: relative; width: 100%; height: 100%; min-height: 360px; }
        .listings-map-canvas { width: 100%; height: 100%; }
    </style>
</head>
<body>
    <div class="listings-map-container">
        <div id="listings-map-canvas" class="listings-map-canvas"></div>
    </div>
    <script>
    window.listingsMapConfig = {
        apiKey: <?php echo json_encode($google_maps_api_key); ?>,
        defaultLat: <?php echo json_encode($default_lat); ?>,
        defaultLng: <?php echo json_encode($default_lng); ?>,
        defaultZoom: <?php echo json_encode($default_zoom); ?>,
        mapType: <?php echo json_encode($map_type); ?>,
        listings: <?php echo $map_listings_json; ?>,
        colors: <?php echo json_encode($theme_colors); ?>,
        mapId: <?php echo json_encode($map_id ?? 'DEMO_MAP_ID'); ?>
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
    (function(){ var el = document.getElementById('listings-map-canvas'); if (el) el.innerHTML = '<div style="padding:2rem;text-align:center;color:#666;">Harita için API key gerekir.</div>'; })();
    </script>
    <?php endif; ?>
</body>
</html>
