<?php
/**
 * Listings Map Module Controller
 * Harita üzerinde emlak ilanlarını gösterir (Google Maps).
 */

class ListingsMapModuleController {

    private $moduleInfo;
    private $settings;
    private $db;
    private $table = 'realestate_listings';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function setModuleInfo($info) {
        $this->moduleInfo = $info;
    }

    public function onLoad() {
        $this->loadSettings();
        $this->runMigration();
    }

    public function onActivate() {
        $this->loadSettings();
        $defaults = $this->getDefaultSettings();
        if (class_exists('ModuleLoader')) {
            ModuleLoader::getInstance()->saveModuleSettings('listings-map', $defaults);
        }
        $this->runMigration();
    }

    public function onDeactivate() {
        // optional
    }

    public function onUninstall() {
        // optional: do not drop listing columns so data is preserved
    }

    private function loadSettings() {
        if (function_exists('get_module_settings')) {
            $this->settings = get_module_settings('listings-map');
        }
        if (empty($this->settings)) {
            $this->settings = $this->getDefaultSettings();
        }
    }

    private function getDefaultSettings() {
        return [
            'google_maps_api_key' => '',
            'use_tkgm_key' => 0,
            'default_lat' => 39.0,
            'default_lng' => 35.0,
            'default_zoom' => 6,
        ];
    }

    /**
     * Add latitude, longitude, city, district, neighborhood to realestate_listings if missing.
     */
    private function runMigration() {
        try {
            $stmt = $this->db->getConnection()->query("SHOW TABLES LIKE '{$this->table}'");
            if (!$stmt || $stmt->rowCount() === 0) {
                return;
            }
            $columns = ['latitude', 'longitude', 'city', 'district', 'neighborhood'];
            foreach ($columns as $col) {
                $check = $this->db->getConnection()->query("SHOW COLUMNS FROM `{$this->table}` LIKE '{$col}'");
                if ($check && $check->rowCount() > 0) {
                    continue;
                }
                $afterMap = ['latitude' => 'location', 'longitude' => 'latitude', 'city' => 'longitude', 'district' => 'city', 'neighborhood' => 'district'];
                $after = isset($afterMap[$col]) ? $afterMap[$col] : 'location';
                $typeMap = [
                    'latitude' => 'DECIMAL(10,8) NULL',
                    'longitude' => 'DECIMAL(11,8) NULL',
                    'city' => 'VARCHAR(100) NULL',
                    'district' => 'VARCHAR(100) NULL',
                    'neighborhood' => 'VARCHAR(150) NULL',
                ];
                $type = isset($typeMap[$col]) ? $typeMap[$col] : 'VARCHAR(255) NULL';
                $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `{$col}` {$type} AFTER `{$after}`");
            }
        } catch (Exception $e) {
            error_log('ListingsMap migration: ' . $e->getMessage());
        }
    }

    private function requireLogin() {
        if (!function_exists('is_user_logged_in') || !is_user_logged_in()) {
            header('Location: ' . admin_url('login'));
            exit;
        }
    }

    /**
     * Google Maps API key (modül ayarlarından).
     */
    public function getGoogleMapsApiKey() {
        return trim($this->settings['google_maps_api_key'] ?? '');
    }

    /**
     * Get published listings with coordinates, optionally filtered by city, district, neighborhood or location (text).
     */
    public function getListingsForMap($city = '', $district = '', $neighborhood = '', $location = '') {
        $sql = "SELECT id, title, slug, location, latitude, longitude, price, property_type, listing_status, status
                FROM `{$this->table}`
                WHERE status = 'published'
                  AND latitude IS NOT NULL AND longitude IS NOT NULL
                  AND CAST(latitude AS DECIMAL(10,8)) != 0 AND CAST(longitude AS DECIMAL(11,8)) != 0";
        $params = [];
        if ($city !== '') {
            $sql .= " AND city = ?";
            $params[] = $city;
        }
        if ($district !== '') {
            $sql .= " AND district = ?";
            $params[] = $district;
        }
        if ($neighborhood !== '') {
            $sql .= " AND neighborhood = ?";
            $params[] = $neighborhood;
        }
        if ($location !== '') {
            $sql .= " AND location LIKE ?";
            $params[] = '%' . $location . '%';
        }
        $sql .= " ORDER BY created_at DESC";
        try {
            if (empty($params)) {
                return $this->db->fetchAll($sql) ?: [];
            }
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('ListingsMap getListingsForMap: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get distinct city, district, neighborhood and location for filter dropdowns.
     * Falls back to location (metin) when city/district/neighborhood columns are empty.
     */
    public function getFilterOptions() {
        try {
            $cities = $this->db->fetchAll("SELECT DISTINCT city AS value FROM `{$this->table}` WHERE status = 'published' AND city IS NOT NULL AND city != '' ORDER BY city");
            $districts = $this->db->fetchAll("SELECT DISTINCT district AS value FROM `{$this->table}` WHERE status = 'published' AND district IS NOT NULL AND district != '' ORDER BY district");
            $neighborhoods = $this->db->fetchAll("SELECT DISTINCT neighborhood AS value FROM `{$this->table}` WHERE status = 'published' AND neighborhood IS NOT NULL AND neighborhood != '' ORDER BY neighborhood");
            $locations = $this->db->fetchAll("SELECT DISTINCT location AS value FROM `{$this->table}` WHERE status = 'published' AND location IS NOT NULL AND TRIM(location) != '' ORDER BY location");
            return [
                'cities' => array_column($cities ?: [], 'value'),
                'districts' => array_column($districts ?: [], 'value'),
                'neighborhoods' => array_column($neighborhoods ?: [], 'value'),
                'locations' => array_column($locations ?: [], 'value'),
            ];
        } catch (Exception $e) {
            error_log('ListingsMap getFilterOptions: ' . $e->getMessage());
            return ['cities' => [], 'districts' => [], 'neighborhoods' => [], 'locations' => []];
        }
    }

    /**
     * İlanlarda enlem/boylam yoksa adres (location) alanından toplu geocode ile doldurur.
     */
    public function admin_bulk_geocode() {
        $this->requireLogin();
        $this->loadSettings();
        $apiKey = $this->getGoogleMapsApiKey();
        if ($apiKey === '') {
            $_SESSION['listings_map_message'] = 'Google Maps API key ayarlanmamış. Önce ayarlardan key girin.';
            header('Location: ' . admin_url('module/listings-map/settings'));
            exit;
        }
        $rows = $this->db->fetchAll("SELECT id, location FROM `{$this->table}` WHERE status = 'published' AND (latitude IS NULL OR longitude IS NULL OR (CAST(latitude AS DECIMAL(10,8)) = 0 AND CAST(longitude AS DECIMAL(11,8)) = 0)) AND location IS NOT NULL AND TRIM(location) != '' ORDER BY id LIMIT 50");
        $updated = 0;
        foreach ($rows ?: [] as $row) {
            $address = trim($row['location'] ?? '');
            if ($address === '') continue;
            $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . rawurlencode($address) . '&key=' . rawurlencode($apiKey);
            $ctx = stream_context_create(['http' => ['timeout' => 8]]);
            $json = @file_get_contents($url, false, $ctx);
            if ($json === false) continue;
            $data = json_decode($json, true);
            if (!$data || ($data['status'] ?? '') !== 'OK' || empty($data['results'][0])) continue;
            $r = $data['results'][0];
            $loc = $r['geometry']['location'] ?? [];
            $lat = isset($loc['lat']) ? (float) $loc['lat'] : null;
            $lng = isset($loc['lng']) ? (float) $loc['lng'] : null;
            if ($lat === null || $lng === null) continue;
            $city = $district = $neighborhood = '';
            foreach ($r['address_components'] ?? [] as $c) {
                $types = $c['types'] ?? [];
                $name = $c['long_name'] ?? '';
                if (in_array('administrative_area_level_1', $types)) { if ($city === '') $city = $name; }
                elseif (in_array('administrative_area_level_2', $types)) { $district = $name; }
                elseif (in_array('locality', $types) && $city === '') { $city = $name; }
                elseif (in_array('sublocality', $types) || in_array('sublocality_level_1', $types) || in_array('neighborhood', $types)) { if ($neighborhood === '') $neighborhood = $name; }
            }
            $stmt = $this->db->getConnection()->prepare("UPDATE `{$this->table}` SET latitude = ?, longitude = ?, city = ?, district = ?, neighborhood = ? WHERE id = ?");
            if ($stmt && $stmt->execute([$lat, $lng, $city, $district, $neighborhood, (int) $row['id']])) {
                $updated++;
            }
            usleep(250000);
        }
        $_SESSION['listings_map_message'] = $updated > 0 ? $updated . ' ilanın konumu güncellendi. Haritada görünecektir.' : 'Güncellenecek ilan bulunamadı veya adres bulunamadı. İlanlarda "Konum" alanı dolu ve enlem/boylam boş olanlar işlenir.';
        header('Location: ' . admin_url('module/listings-map/settings'));
        exit;
    }

    /**
     * Konumu eksik (enlem/boylam boş) yayınlı ilan sayısı.
     */
    public function getListingsWithoutCoordinatesCount() {
        try {
            $r = $this->db->fetch("SELECT COUNT(*) AS n FROM `{$this->table}` WHERE status = 'published' AND (latitude IS NULL OR longitude IS NULL OR (CAST(latitude AS DECIMAL(10,8)) = 0 AND CAST(longitude AS DECIMAL(11,8)) = 0)) AND location IS NOT NULL AND TRIM(location) != ''");
            return $r ? (int) $r['n'] : 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    public function admin_index() {
        $this->requireLogin();
        header('Location: ' . admin_url('module/listings-map/settings'));
        exit;
    }

    /**
     * Admin AJAX: Geocode address to lat/lng and city/district/neighborhood (for listing form).
     */
    public function admin_geocode() {
        $this->requireLogin();
        $this->loadSettings();
        $address = trim($_GET['address'] ?? $_POST['address'] ?? '');
        if ($address === '') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Adres boş']);
            exit;
        }
        $apiKey = $this->getGoogleMapsApiKey();
        if ($apiKey === '') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Google Maps API key ayarlanmamış']);
            exit;
        }
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . rawurlencode($address) . '&key=' . rawurlencode($apiKey);
        $ctx = stream_context_create(['http' => ['timeout' => 10]]);
        $json = @file_get_contents($url, false, $ctx);
        if ($json === false) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Geocoding isteği başarısız']);
            exit;
        }
        $data = json_decode($json, true);
        $status = $data['status'] ?? '';
        $errorMessage = $data['error_message'] ?? '';

        if (!$data || empty($data['results'][0])) {
            header('Content-Type: application/json; charset=utf-8');
            if ($status === 'ZERO_RESULTS') {
                echo json_encode(['success' => false, 'message' => 'Adres bulunamadı. Daha açıklayıcı bir adres yazın (örn. il, ilçe, mahalle veya tam adres).']);
            } elseif ($status === 'REQUEST_DENIED') {
                $msg = 'Geocoding isteği reddedildi. "Adresten konum al" isteği sunucudan yapıldığı için API anahtarının sunucu kullanımına izin vermesi gerekir. ';
                $msg .= 'Google Cloud Console → API ve Hizmetler → Kimlik Bilgileri → ilgili key → Uygulama kısıtlamaları: "Yok" seçin veya "IP adresleri" ile sunucu IP\'nizi ekleyin. ';
                $msg .= 'Sadece "HTTP referansları (web siteleri)" kullanıyorsanız sunucu isteği engellenir.';
                $payload = ['success' => false, 'message' => $msg];
                if ($errorMessage !== '') {
                    $payload['error_detail'] = $errorMessage;
                }
                echo json_encode($payload);
            } elseif ($status === 'OVER_QUERY_LIMIT') {
                echo json_encode(['success' => false, 'message' => 'Geocoding kotası aşıldı. Lütfen daha sonra tekrar deneyin.']);
            } elseif ($status === 'INVALID_REQUEST') {
                echo json_encode(['success' => false, 'message' => 'Geçersiz adres. Lütfen konum alanına geçerli bir adres yazın.']);
            } else {
                $msg = 'Adres bulunamadı.';
                if ($errorMessage !== '') {
                    $msg .= ' (' . $errorMessage . ')';
                }
                echo json_encode(['success' => false, 'message' => $msg]);
            }
            exit;
        }
        $r = $data['results'][0];
        $loc = $r['geometry']['location'] ?? [];
        $lat = isset($loc['lat']) ? (float) $loc['lat'] : null;
        $lng = isset($loc['lng']) ? (float) $loc['lng'] : null;
        $city = $district = $neighborhood = '';
        foreach ($r['address_components'] ?? [] as $c) {
            $types = $c['types'] ?? [];
            $name = $c['long_name'] ?? '';
            if (in_array('administrative_area_level_1', $types)) {
                if ($city === '') $city = $name;
            } elseif (in_array('administrative_area_level_2', $types)) {
                $district = $name;
            } elseif (in_array('locality', $types) && $city === '') {
                $city = $name;
            } elseif (in_array('sublocality', $types) || in_array('sublocality_level_1', $types) || in_array('neighborhood', $types)) {
                if ($neighborhood === '') $neighborhood = $name;
            }
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'latitude' => $lat,
            'longitude' => $lng,
            'city' => $city,
            'district' => $district,
            'neighborhood' => $neighborhood,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function admin_settings() {
        $this->requireLogin();
        $this->loadSettings();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->settings['google_maps_api_key'] = trim($_POST['google_maps_api_key'] ?? '');
            $this->settings['default_lat'] = (float)($_POST['default_lat'] ?? 39.0);
            $this->settings['default_lng'] = (float)($_POST['default_lng'] ?? 35.0);
            $this->settings['default_zoom'] = (int)($_POST['default_zoom'] ?? 6);
            if (class_exists('ModuleLoader')) {
                ModuleLoader::getInstance()->saveModuleSettings('listings-map', $this->settings);
            }
            $_SESSION['listings_map_message'] = 'Ayarlar kaydedildi.';
            header('Location: ' . admin_url('module/listings-map/settings'));
            exit;
        }

        $this->adminView('settings', [
            'settings' => $this->settings,
            'message' => $_SESSION['listings_map_message'] ?? null,
            'listings_without_coords_count' => $this->getListingsWithoutCoordinatesCount(),
        ]);
        unset($_SESSION['listings_map_message']);
    }

    /**
     * Frontend: Sadece harita (iframe için – başlık/filtre yok)
     * URL: /harita-ilanlar/embed
     */
    public function frontend_map_embed() {
        $this->loadSettings();
        $listings = $this->getListingsForMap('', '', '', '');
        $apiKey = $this->getGoogleMapsApiKey();
        $detailUrlBase = function_exists('localized_url') ? rtrim(localized_url('/ilan'), '/') . '/' : (function_exists('site_url') ? rtrim(site_url('/ilan'), '/') . '/' : '/ilan/');
        $themeLoader = class_exists('ThemeLoader') ? ThemeLoader::getInstance() : null;
        $themeColors = [
            'primary' => $themeLoader ? $themeLoader->getColor('primary', '#bc1a1a') : '#bc1a1a',
            'secondary' => $themeLoader ? $themeLoader->getColor('secondary', '#1f2937') : '#1f2937',
            'accent' => $themeLoader ? $themeLoader->getColor('accent', '#9a1615') : '#9a1615',
            'text' => $themeLoader ? $themeLoader->getColor('text', '#1f2937') : '#1f2937',
            'text_muted' => $themeLoader ? $themeLoader->getColor('text_muted', '#6b7280') : '#6b7280',
        ];
        $data = [
            'listings' => $listings,
            'google_maps_api_key' => $apiKey,
            'default_lat' => (float)($this->settings['default_lat'] ?? 39.0),
            'default_lng' => (float)($this->settings['default_lng'] ?? 35.0),
            'default_zoom' => (int)($this->settings['default_zoom'] ?? 6),
            'detail_url_base' => $detailUrlBase,
            'theme_colors' => $themeColors,
        ];
        $embedPath = __DIR__ . '/views/frontend/map-embed.php';
        if (!file_exists($embedPath)) {
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Harita embed görünümü bulunamadı.';
            return;
        }
        extract($data);
        include $embedPath;
    }

    /**
     * Frontend: Harita sayfası (tam sayfa, filtrelerle)
     */
    public function frontend_map() {
        $this->loadSettings();
        $city = isset($_GET['city']) ? trim((string) $_GET['city']) : '';
        $district = isset($_GET['district']) ? trim((string) $_GET['district']) : '';
        $neighborhood = isset($_GET['neighborhood']) ? trim((string) $_GET['neighborhood']) : '';
        $location = isset($_GET['location']) ? trim((string) $_GET['location']) : '';

        $listings = $this->getListingsForMap($city, $district, $neighborhood, $location);
        $apiKey = $this->getGoogleMapsApiKey();
        $detailUrlBase = function_exists('localized_url') ? rtrim(localized_url('/ilan'), '/') . '/' : (function_exists('site_url') ? rtrim(site_url('/ilan'), '/') . '/' : '/ilan/');

        $themeLoader = class_exists('ThemeLoader') ? ThemeLoader::getInstance() : null;
        $themeColors = [
            'primary' => $themeLoader ? $themeLoader->getColor('primary', '#bc1a1a') : '#bc1a1a',
            'secondary' => $themeLoader ? $themeLoader->getColor('secondary', '#1f2937') : '#1f2937',
            'accent' => $themeLoader ? $themeLoader->getColor('accent', '#9a1615') : '#9a1615',
            'text' => $themeLoader ? $themeLoader->getColor('text', '#1f2937') : '#1f2937',
            'text_muted' => $themeLoader ? $themeLoader->getColor('text_muted', '#6b7280') : '#6b7280',
        ];

        $data = [
            'title' => 'Harita Üzerinde Tüm İlanlar',
            'listings' => $listings,
            'filter_options' => $this->getFilterOptions(),
            'filters' => ['city' => $city, 'district' => $district, 'neighborhood' => $neighborhood, 'location' => $location],
            'google_maps_api_key' => $apiKey,
            'default_lat' => (float)($this->settings['default_lat'] ?? 39.0),
            'default_lng' => (float)($this->settings['default_lng'] ?? 35.0),
            'default_zoom' => (int)($this->settings['default_zoom'] ?? 6),
            'detail_url_base' => $detailUrlBase,
            'theme_colors' => $themeColors,
            'asset_url' => function_exists('module_asset_url') ? module_asset_url('listings-map', 'map.js') : admin_url('module-asset') . '&module=listings-map&file=map.js',
        ];

        $viewPath = __DIR__ . '/views/frontend/map.php';
        if (!file_exists($viewPath)) {
            echo 'Harita görünümü bulunamadı.';
            return;
        }
        extract($data);
        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        $pageTitle = $data['title'] . (function_exists('get_option') && get_option('site_name') ? ' - ' . get_option('site_name', '') : '');
        $layoutData = [
            'title' => $pageTitle,
            'current_page' => 'harita-ilanlar',
        ];
        if (class_exists('ViewRenderer')) {
            $renderer = ViewRenderer::getInstance();
            if (method_exists($renderer, 'renderWithContent')) {
                $renderer->renderWithContent('default', $content, $layoutData);
            } else {
                $this->renderContentWithLayoutFallback($content, $layoutData);
            }
        } else {
            echo $content;
        }
    }

    /**
     * renderWithContent yoksa (eski ViewRenderer) layout ile içeriği gösterir.
     */
    private function renderContentWithLayoutFallback(string $content, array $layoutData): void {
        $themeLoader = class_exists('ThemeLoader') ? ThemeLoader::getInstance() : null;
        $layoutPath = null;
        if ($themeLoader && method_exists($themeLoader, 'getLayout')) {
            $layoutPath = $themeLoader->getLayout('default');
        }
        $basePath = dirname(dirname(__DIR__));
        if (!$layoutPath || !file_exists($layoutPath)) {
            $layoutPath = $basePath . '/app/views/frontend/layouts/default.php';
        }
        if (!$layoutPath || !file_exists($layoutPath)) {
            $layoutPath = $basePath . '/app/views/layouts/default.php';
        }
        if ($layoutPath && file_exists($layoutPath)) {
            $sections = ['content' => $content, 'styles' => '', 'scripts' => ''];
            if ($themeLoader && method_exists($themeLoader, 'getHeadOutput')) {
                $sections['styles'] = (string) $themeLoader->getHeadOutput();
            }
            if ($themeLoader && method_exists($themeLoader, 'getFooterOutput')) {
                $sections['scripts'] = (string) $themeLoader->getFooterOutput();
            }
            extract(array_merge($layoutData, ['sections' => $sections, 'themeLoader' => $themeLoader]));
            require $layoutPath;
        } else {
            echo $content;
        }
    }

    private function adminView($view, $data = []) {
        $viewPath = __DIR__ . '/views/admin/' . $view . '.php';
        $basePath = dirname(dirname(__DIR__));

        if (!file_exists($viewPath)) {
            echo "View not found: " . $view;
            return;
        }

        extract($data);
        $currentPage = 'module/listings-map';

        include $basePath . '/app/views/admin/snippets/header.php';
        ?>
        <div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
            <div class="flex min-h-screen">
                <?php include $basePath . '/app/views/admin/snippets/sidebar.php'; ?>
                <div class="flex-1 flex flex-col lg:ml-64">
                    <?php include $basePath . '/app/views/admin/snippets/top-header.php'; ?>
                    <main class="flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b] overflow-y-auto w-full">
                        <div class="w-full max-w-none">
                            <?php include $viewPath; ?>
                        </div>
                    </main>
                </div>
            </div>
        </div>
        <?php
        include $basePath . '/app/views/admin/snippets/footer.php';
    }
}
