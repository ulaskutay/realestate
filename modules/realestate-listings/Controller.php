<?php
/**
 * Real Estate Listings Module Controller (standalone)
 * Tema bağımsız özel modül.
 */

class RealEstateListingsController {

    private $model;
    private $moduleInfo;
    private $settings = [];

    public function __construct() {
        require_once __DIR__ . '/Model.php';
        $this->model = new RealEstateListingsModel();
    }

    private function loadSettings() {
        if (function_exists('get_module_settings')) {
            $this->settings = get_module_settings('realestate-listings') ?: [];
        }
        $this->settings = array_merge($this->getDefaultSettings(), $this->settings);
    }

    private function getDefaultSettings() {
        return [
            'property_types' => [
                'house' => 'Müstakil Ev',
                'apartment' => 'Daire',
                'villa' => 'Villa',
                'commercial' => 'Ticari',
                'land' => 'Arsa',
            ],
            'listing_statuses' => [
                'sale' => 'Satılık',
                'rent' => 'Kiralık',
            ],
        ];
    }

    /** Emlak tipi ve ilan durumu seçenekleri (ayarlardan veya varsayılan) */
    private function getListingOptions() {
        $this->loadSettings();
        return [
            'property_types' => $this->settings['property_types'] ?? $this->getDefaultSettings()['property_types'],
            'listing_statuses' => $this->settings['listing_statuses'] ?? $this->getDefaultSettings()['listing_statuses'],
        ];
    }

    public function setModuleInfo($info) {
        $this->moduleInfo = $info;
    }

    public function onLoad() {}

    public function onActivate() {
        $this->model->createTable();
    }

    public function onDeactivate() {}

    private function requireLogin() {
        if (!is_user_logged_in()) {
            header('Location: ' . admin_url('login'));
            exit;
        }
    }

    private function checkPermission($permission) {
        $this->requireLogin();
        if (!current_user_can($permission)) {
            $_SESSION['error_message'] = 'Bu işlem için yetkiniz bulunmamaktadır!';
            header('Location: ' . admin_url('dashboard'));
            exit;
        }
    }

    private function adminView($view, $data = []) {
        $data['rootPath'] = dirname(dirname(__DIR__));
        extract($data);
        $viewPath = __DIR__ . '/views/admin/' . $view . '.php';
        if (!file_exists($viewPath)) {
            die("View not found: $viewPath");
        }
        include $viewPath;
    }

    private function getAgentsForForm() {
        $agents = [];
        $agentsPath = dirname(__DIR__) . '/realestate-agents/Model.php';
        if (file_exists($agentsPath)) {
            require_once $agentsPath;
            if (class_exists('RealEstateAgentsModel')) {
                $agentsModel = new RealEstateAgentsModel();
                $agents = $agentsModel->getActive();
            }
        }
        return $agents;
    }

    public function admin_index() {
        $this->checkPermission('realestate-listings.view');
        $filters = [
            'search'         => isset($_GET['search']) ? trim($_GET['search']) : '',
            'status'         => isset($_GET['status']) ? trim($_GET['status']) : '',
            'property_type'  => isset($_GET['property_type']) ? trim($_GET['property_type']) : '',
            'listing_status' => isset($_GET['listing_status']) ? trim($_GET['listing_status']) : '',
            'city'           => isset($_GET['city']) ? trim($_GET['city']) : '',
            'district'       => isset($_GET['district']) ? trim($_GET['district']) : '',
            'price_min'      => isset($_GET['price_min']) ? trim($_GET['price_min']) : '',
            'price_max'      => isset($_GET['price_max']) ? trim($_GET['price_max']) : '',
            'order'          => isset($_GET['order']) ? trim($_GET['order']) : 'created_at DESC',
        ];
        $listings = $this->model->getAllForAdmin($filters);
        $opts = $this->getListingOptions();
        $data = [
            'title' => 'Emlak İlanları',
            'user' => get_logged_in_user(),
            'listings' => $listings,
            'property_types' => $opts['property_types'],
            'listing_statuses' => $opts['listing_statuses'],
            'filters' => $filters,
            'message' => $_SESSION['listings_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['listings_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null)
        ];
        unset($_SESSION['listings_message'], $_SESSION['error_message']);
        $this->adminView('index', $data);
    }

    /**
     * Konum seçenekleri (il/ilçe dropdown) – JSON API.
     */
    public function admin_location_options() {
        $this->requireLogin();
        header('Content-Type: application/json; charset=utf-8');
        $dataPath = __DIR__ . '/data/turkey_locations.php';
        if (!is_file($dataPath)) {
            echo json_encode(['items' => []]);
            return;
        }
        require_once $dataPath;
        $iller = $GLOBALS['TURKEY_ILLER'] ?? [];
        $ilceler = $GLOBALS['TURKEY_ILCELER'] ?? [];
        $type = isset($_GET['type']) ? trim($_GET['type']) : '';
        if ($type === 'iller') {
            $items = [];
            foreach ($iller as $code => $name) {
                $items[] = ['code' => $code, 'name' => $name];
            }
            echo json_encode(['items' => $items]);
            return;
        }
        if ($type === 'ilceler') {
            $il = isset($_GET['il']) ? trim($_GET['il']) : '';
            if ($il === '') {
                echo json_encode(['items' => []]);
                return;
            }
            $code = $il;
            if (!preg_match('/^\d{1,2}$/', $il)) {
                $code = array_search($il, $iller);
                if ($code === false) {
                    echo json_encode(['items' => []]);
                    return;
                }
                $code = str_pad((string) $code, 2, '0', STR_PAD_LEFT);
            } else {
                $code = str_pad($il, 2, '0', STR_PAD_LEFT);
            }
            $list = $ilceler[$code] ?? [];
            $items = array_map(function ($name) { return ['name' => $name]; }, $list);
            echo json_encode(['items' => $items]);
            return;
        }
        echo json_encode(['items' => []]);
    }

    public function admin_settings() {
        $this->checkPermission('realestate-listings.view');
        $this->loadSettings();
        $opts = $this->getListingOptions();
        $data = [
            'title' => 'İlanlar Modülü Ayarları',
            'user' => get_logged_in_user(),
            'property_types' => $opts['property_types'],
            'listing_statuses' => $opts['listing_statuses'],
            'message' => $_SESSION['listings_settings_message'] ?? null,
        ];
        unset($_SESSION['listings_settings_message']);
        $this->adminView('settings', $data);
    }

    public function admin_settings_save() {
        $this->checkPermission('realestate-listings.edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . admin_url('module/realestate-listings/settings'));
            exit;
        }
        $this->loadSettings();
        $property_types = [];
        $listing_statuses = [];
        if (!empty($_POST['property_type_key']) && is_array($_POST['property_type_key'])) {
            foreach ($_POST['property_type_key'] as $i => $key) {
                $key = trim((string) $key);
                $label = isset($_POST['property_type_label'][$i]) ? trim((string) $_POST['property_type_label'][$i]) : '';
                if ($key !== '' || $label !== '') {
                    $key = $key !== '' ? $key : 'opt_' . $i;
                    $property_types[$key] = $label !== '' ? $label : $key;
                }
            }
        }
        if (!empty($_POST['listing_status_key']) && is_array($_POST['listing_status_key'])) {
            foreach ($_POST['listing_status_key'] as $i => $key) {
                $key = trim((string) $key);
                $label = isset($_POST['listing_status_label'][$i]) ? trim((string) $_POST['listing_status_label'][$i]) : '';
                if ($key !== '' || $label !== '') {
                    $key = $key !== '' ? $key : 'status_' . $i;
                    $listing_statuses[$key] = $label !== '' ? $label : $key;
                }
            }
        }
        if (empty($property_types)) {
            $property_types = $this->getDefaultSettings()['property_types'];
        }
        if (empty($listing_statuses)) {
            $listing_statuses = $this->getDefaultSettings()['listing_statuses'];
        }
        $this->settings['property_types'] = $property_types;
        $this->settings['listing_statuses'] = $listing_statuses;
        if (class_exists('ModuleLoader')) {
            ModuleLoader::getInstance()->saveModuleSettings('realestate-listings', $this->settings);
        }
        $_SESSION['listings_settings_message'] = 'Ayarlar kaydedildi.';
        header('Location: ' . admin_url('module/realestate-listings/settings'));
        exit;
    }

    /**
     * İlan ekleme/düzenleme sayfasından tek seçenek eklemek için AJAX (emlak tipi veya ilan durumu).
     * JSON döner.
     */
    public function admin_add_option() {
        $this->checkPermission('realestate-listings.edit');
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Geçersiz istek.']);
            return;
        }
        $type = trim((string) ($_POST['type'] ?? ''));
        $label = trim((string) ($_POST['label'] ?? ''));
        if ($label === '') {
            echo json_encode(['success' => false, 'message' => 'Etiket boş olamaz.']);
            return;
        }
        $key = $this->slugFromLabel($label);
        if ($key === '') {
            $key = 'opt_' . substr(md5($label), 0, 8);
        }
        $this->loadSettings();
        if ($type === 'property_type') {
            $current = $this->settings['property_types'] ?? $this->getDefaultSettings()['property_types'];
            $current[$key] = $label;
            $this->settings['property_types'] = $current;
        } elseif ($type === 'listing_status') {
            $current = $this->settings['listing_statuses'] ?? $this->getDefaultSettings()['listing_statuses'];
            $current[$key] = $label;
            $this->settings['listing_statuses'] = $current;
        } else {
            echo json_encode(['success' => false, 'message' => 'Geçersiz tip.']);
            return;
        }
        if (class_exists('ModuleLoader')) {
            ModuleLoader::getInstance()->saveModuleSettings('realestate-listings', $this->settings);
        }
        echo json_encode(['success' => true, 'key' => $key, 'label' => $label]);
    }

    private function slugFromLabel($label) {
        $t = mb_strtolower($label, 'UTF-8');
        $map = ['ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u'];
        $t = strtr($t, $map);
        $t = preg_replace('/[^a-z0-9\s\-_]/u', '', $t);
        $t = preg_replace('/[\s\-]+/', '_', $t);
        return trim($t, '_');
    }

    public function admin_create() {
        $this->checkPermission('realestate-listings.create');
        $iller = $this->getIllerForForm();
        $opts = $this->getListingOptions();
        $data = [
            'title' => 'Yeni İlan Ekle',
            'user' => get_logged_in_user(),
            'listing' => null,
            'agents' => $this->getAgentsForForm(),
            'iller' => $iller,
            'property_types' => $opts['property_types'],
            'listing_statuses' => $opts['listing_statuses'],
            'all_categories' => $this->model->getAllCategories(),
        ];
        $this->adminView('create', $data);
    }

    private function getIllerForForm() {
        $dataPath = __DIR__ . '/data/turkey_locations.php';
        if (!is_file($dataPath)) {
            return [];
        }
        require_once $dataPath;
        return $GLOBALS['TURKEY_ILLER'] ?? [];
    }

    public function admin_store() {
        $this->checkPermission('realestate-listings.create');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . admin_url('module/realestate-listings'));
            exit;
        }
        $user = get_logged_in_user();
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'slug' => $this->generateSlug(trim($_POST['title'] ?? '')),
            'description' => $_POST['description'] ?? '',
            'location' => trim($_POST['location'] ?? ''),
            'latitude' => trim($_POST['latitude'] ?? ''),
            'longitude' => trim($_POST['longitude'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'district' => trim($_POST['district'] ?? ''),
            'neighborhood' => trim($_POST['neighborhood'] ?? ''),
            'ada' => trim($_POST['ada'] ?? ''),
            'parsel' => trim($_POST['parsel'] ?? ''),
            'price' => floatval($_POST['price'] ?? 0),
            'property_type' => trim($_POST['property_type'] ?? 'house'),
            'listing_status' => trim($_POST['listing_status'] ?? 'sale'),
            'bedrooms' => intval($_POST['bedrooms'] ?? 0),
            'bathrooms' => intval($_POST['bathrooms'] ?? 0),
            'living_rooms' => intval($_POST['living_rooms'] ?? 0),
            'rooms' => intval($_POST['rooms'] ?? 0),
            'area' => floatval($_POST['area'] ?? 0),
            'area_unit' => trim($_POST['area_unit'] ?? 'sqm'),
            'featured_image' => trim($_POST['featured_image'] ?? ''),
            'gallery' => !empty($_POST['gallery']) ? $_POST['gallery'] : '[]',
            'status' => trim($_POST['status'] ?? 'published'),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'author_id' => $user['id'],
            'realtor_id' => !empty($_POST['realtor_id']) ? intval($_POST['realtor_id']) : null
        ];
        if (empty($data['title'])) {
            $_SESSION['error_message'] = 'İlan başlığı zorunludur!';
            header('Location: ' . admin_url('module/realestate-listings/create'));
            exit;
        }
        $id = $this->model->create($data);
        if ($id) {
            $categoryIds = isset($_POST['category_ids']) && is_array($_POST['category_ids']) ? array_map('intval', $_POST['category_ids']) : [];
            $this->model->setListingCategories($id, $categoryIds);
            $_SESSION['listings_message'] = 'İlan başarıyla oluşturuldu!';
            header('Location: ' . admin_url('module/realestate-listings/edit/' . $id));
        } else {
            $_SESSION['error_message'] = 'İlan oluşturulurken bir hata oluştu!';
            header('Location: ' . admin_url('module/realestate-listings/create'));
        }
        exit;
    }

    public function admin_edit($id) {
        $this->checkPermission('realestate-listings.edit');
        $listing = $this->model->find($id);
        if (!$listing) {
            $_SESSION['error_message'] = 'İlan bulunamadı!';
            header('Location: ' . admin_url('module/realestate-listings'));
            exit;
        }
        $opts = $this->getListingOptions();
        $listingCategories = $this->model->getCategoriesForListing($id);
        $allCategories = $this->model->getAllCategories();
        $data = [
            'title' => 'İlan Düzenle',
            'user' => get_logged_in_user(),
            'listing' => $listing,
            'agents' => $this->getAgentsForForm(),
            'iller' => $this->getIllerForForm(),
            'property_types' => $opts['property_types'],
            'listing_statuses' => $opts['listing_statuses'],
            'listing_categories' => $listingCategories,
            'all_categories' => $allCategories,
            'message' => $_SESSION['listings_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['listings_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null)
        ];
        unset($_SESSION['listings_message'], $_SESSION['error_message']);
        $this->adminView('edit', $data);
    }

    public function admin_update($id) {
        $this->checkPermission('realestate-listings.edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . admin_url('module/realestate-listings'));
            exit;
        }
        $listing = $this->model->find($id);
        if (!$listing) {
            $_SESSION['error_message'] = 'İlan bulunamadı!';
            header('Location: ' . admin_url('module/realestate-listings'));
            exit;
        }
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'slug' => $this->generateSlug(trim($_POST['slug'] ?? trim($_POST['title'] ?? '')), $id),
            'description' => $_POST['description'] ?? '',
            'location' => trim($_POST['location'] ?? ''),
            'latitude' => trim($_POST['latitude'] ?? ''),
            'longitude' => trim($_POST['longitude'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'district' => trim($_POST['district'] ?? ''),
            'neighborhood' => trim($_POST['neighborhood'] ?? ''),
            'ada' => trim($_POST['ada'] ?? ''),
            'parsel' => trim($_POST['parsel'] ?? ''),
            'price' => floatval($_POST['price'] ?? 0),
            'property_type' => trim($_POST['property_type'] ?? 'house'),
            'listing_status' => trim($_POST['listing_status'] ?? 'sale'),
            'bedrooms' => intval($_POST['bedrooms'] ?? 0),
            'bathrooms' => intval($_POST['bathrooms'] ?? 0),
            'living_rooms' => intval($_POST['living_rooms'] ?? 0),
            'rooms' => intval($_POST['rooms'] ?? 0),
            'area' => floatval($_POST['area'] ?? 0),
            'area_unit' => trim($_POST['area_unit'] ?? 'sqm'),
            'featured_image' => trim($_POST['featured_image'] ?? ''),
            'gallery' => !empty($_POST['gallery']) ? $_POST['gallery'] : '[]',
            'status' => trim($_POST['status'] ?? 'published'),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'realtor_id' => !empty($_POST['realtor_id']) ? intval($_POST['realtor_id']) : null
        ];
        if (empty($data['title'])) {
            $_SESSION['error_message'] = 'İlan başlığı zorunludur!';
            header('Location: ' . admin_url('module/realestate-listings/edit/' . $id));
            exit;
        }
        $result = $this->model->update($id, $data);
        if ($result) {
            $categoryIds = isset($_POST['category_ids']) && is_array($_POST['category_ids']) ? array_map('intval', $_POST['category_ids']) : [];
            $this->model->setListingCategories($id, $categoryIds);
            $_SESSION['listings_message'] = 'İlan başarıyla güncellendi!';
        } else {
            $_SESSION['error_message'] = 'İlan güncellenirken bir hata oluştu!';
        }
        header('Location: ' . admin_url('module/realestate-listings/edit/' . $id));
        exit;
    }

    public function admin_delete($id) {
        $this->checkPermission('realestate-listings.delete');
        $result = $this->model->delete($id);
        if ($result) {
            $_SESSION['listings_message'] = 'İlan başarıyla silindi!';
        } else {
            $_SESSION['error_message'] = 'İlan silinirken bir hata oluştu!';
        }
        header('Location: ' . admin_url('module/realestate-listings'));
        exit;
    }

    public function admin_generate_description() {
        $this->checkPermission('realestate-listings.create');
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Sadece POST istekleri kabul edilir']);
            return;
        }
        $basePath = dirname(dirname(__DIR__));
        $aiPath = $basePath . '/app/services/AIService.php';
        if (!file_exists($aiPath)) {
            echo json_encode(['success' => false, 'error' => 'AIService bulunamadı']);
            return;
        }
        require_once $aiPath;
        $listingData = [
            'title' => trim($_POST['title'] ?? ''),
            'location' => trim($_POST['location'] ?? ''),
            'ada' => trim($_POST['ada'] ?? ''),
            'parsel' => trim($_POST['parsel'] ?? ''),
            'price' => floatval($_POST['price'] ?? 0),
            'property_type' => trim($_POST['property_type'] ?? 'house'),
            'listing_status' => trim($_POST['listing_status'] ?? 'sale'),
            'bedrooms' => intval($_POST['bedrooms'] ?? 0),
            'bathrooms' => intval($_POST['bathrooms'] ?? 0),
            'living_rooms' => intval($_POST['living_rooms'] ?? 0),
            'rooms' => intval($_POST['rooms'] ?? 0),
            'area' => floatval($_POST['area'] ?? 0),
            'area_unit' => trim($_POST['area_unit'] ?? 'sqm')
        ];
        if (empty($listingData['title'])) {
            echo json_encode(['success' => false, 'error' => 'İlan başlığı zorunludur']);
            return;
        }
        $aiService = new AIService();
        $result = $aiService->generateListingDescription($listingData);
        echo json_encode($result);
    }

    public function frontend_index() {
        $location = isset($_GET['location']) ? trim($_GET['location']) : '';
        $type = isset($_GET['type']) ? trim($_GET['type']) : '';
        $priceRange = isset($_GET['price_range']) ? trim($_GET['price_range']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';
        $realtor = isset($_GET['realtor']) ? (int) $_GET['realtor'] : null;
        $sort = isset($_GET['sort']) ? trim($_GET['sort']) : '';
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $city = isset($_GET['city']) ? trim($_GET['city']) : '';
        $district = isset($_GET['district']) ? trim($_GET['district']) : '';
        $minRooms = isset($_GET['min_rooms']) ? max(0, (int) $_GET['min_rooms']) : 0;
        $minBathrooms = isset($_GET['min_bathrooms']) ? max(0, (int) $_GET['min_bathrooms']) : 0;
        $minBedrooms = isset($_GET['min_bedrooms']) ? max(0, (int) $_GET['min_bedrooms']) : 0;
        $areaMin = isset($_GET['area_min']) ? max(0, (float) $_GET['area_min']) : 0;
        $areaMax = isset($_GET['area_max']) ? max(0, (float) $_GET['area_max']) : 0;

        $listings = $this->model->getPublished(
            $location, $type, $priceRange, null, 0, $status, $realtor, $sort,
            $search, $city, $district, $minRooms, $minBathrooms, $minBedrooms, $areaMin, $areaMax
        );
        $opts = $this->getListingOptions();

        // Filtre il/ilçe seçenekleri sadece ilanlarda bulunan verilerden gelir
        $iller = $this->model->getDistinctCitiesFromListings();
        $ilceByIlName = $this->model->getDistrictsByCityFromListings();
        $allCategories = $this->model->getAllCategories();

        $data = [
            'title' => 'Emlak İlanları',
            'listings' => $listings,
            'current_category' => null,
            'filters' => [
                'location' => $location,
                'type' => $type,
                'price_range' => $priceRange,
                'status' => $status,
                'category_slug' => '',
                'realtor' => $realtor,
                'sort' => $sort,
                'search' => $search,
                'city' => $city,
                'district' => $district,
                'min_rooms' => $minRooms,
                'min_bathrooms' => $minBathrooms,
                'min_bedrooms' => $minBedrooms,
                'area_min' => $areaMin,
                'area_max' => $areaMax,
            ],
            'property_type_labels' => $opts['property_types'],
            'listing_status_labels' => $opts['listing_statuses'],
            'iller' => $iller,
            'ilceByIlName' => $ilceByIlName,
            'all_categories' => $allCategories,
        ];
        extract($data);
        $viewPath = get_module_frontend_view('realestate-listings', 'listings.php');
        if (!$viewPath) {
            $viewPath = __DIR__ . '/views/frontend/listings.php';
        }
        include $viewPath;
    }

    /**
     * Frontend: Kategori slug'ına göre ilanlar listesi (/ilanlar/kategori/{slug})
     */
    public function frontend_category($slug) {
        $category = $this->model->getCategoryBySlug($slug);
        if (!$category) {
            header('HTTP/1.0 404 Not Found');
            die('Kategori bulunamadı');
        }
        $location = isset($_GET['location']) ? trim($_GET['location']) : '';
        $priceRange = isset($_GET['price_range']) ? trim($_GET['price_range']) : '';
        $realtor = isset($_GET['realtor']) ? (int) $_GET['realtor'] : null;
        $sort = isset($_GET['sort']) ? trim($_GET['sort']) : '';
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $city = isset($_GET['city']) ? trim($_GET['city']) : '';
        $district = isset($_GET['district']) ? trim($_GET['district']) : '';
        $minRooms = isset($_GET['min_rooms']) ? max(0, (int) $_GET['min_rooms']) : 0;
        $minBathrooms = isset($_GET['min_bathrooms']) ? max(0, (int) $_GET['min_bathrooms']) : 0;
        $minBedrooms = isset($_GET['min_bedrooms']) ? max(0, (int) $_GET['min_bedrooms']) : 0;
        $areaMin = isset($_GET['area_min']) ? max(0, (float) $_GET['area_min']) : 0;
        $areaMax = isset($_GET['area_max']) ? max(0, (float) $_GET['area_max']) : 0;

        $listings = $this->model->getPublished(
            $location, '', $priceRange, null, 0, '', $realtor, $sort,
            $search, $city, $district, $minRooms, $minBathrooms, $minBedrooms, $areaMin, $areaMax, $slug
        );
        $opts = $this->getListingOptions();
        $iller = $this->model->getDistinctCitiesFromListings();
        $ilceByIlName = $this->model->getDistrictsByCityFromListings();
        $allCategories = $this->model->getAllCategories();

        $data = [
            'title' => $category['name'] . ' - Emlak İlanları',
            'listings' => $listings,
            'current_category' => $category,
            'filters' => [
                'location' => $location,
                'type' => '',
                'price_range' => $priceRange,
                'status' => '',
                'category_slug' => $slug,
                'realtor' => $realtor,
                'sort' => $sort,
                'search' => $search,
                'city' => $city,
                'district' => $district,
                'min_rooms' => $minRooms,
                'min_bathrooms' => $minBathrooms,
                'min_bedrooms' => $minBedrooms,
                'area_min' => $areaMin,
                'area_max' => $areaMax,
            ],
            'property_type_labels' => $opts['property_types'],
            'listing_status_labels' => $opts['listing_statuses'],
            'iller' => $iller,
            'ilceByIlName' => $ilceByIlName,
            'all_categories' => $allCategories,
        ];
        extract($data);
        $viewPath = get_module_frontend_view('realestate-listings', 'listings.php');
        if (!$viewPath) {
            $viewPath = __DIR__ . '/views/frontend/listings.php';
        }
        include $viewPath;
    }

    public function frontend_detail($slug) {
        $listing = $this->model->findBySlug($slug);
        if (!$listing && is_numeric($slug)) {
            $listing = $this->model->find($slug);
        }
        if (!$listing || $listing['status'] !== 'published') {
            header('HTTP/1.0 404 Not Found');
            die('İlan bulunamadı');
        }
        if (empty($listing['slug'])) {
            $newSlug = $this->generateSlug($listing['title'], $listing['id']);
            $this->model->update($listing['id'], ['slug' => $newSlug]);
            $listing['slug'] = $newSlug;
        }
        $opts = $this->getListingOptions();
        $data = [
            'title' => $listing['title'],
            'listing' => $listing,
            'property_type_labels' => $opts['property_types'],
            'listing_status_labels' => $opts['listing_statuses'],
        ];
        extract($data);
        $viewPath = get_module_frontend_view('realestate-listings', 'detail.php');
        if (!$viewPath) {
            $viewPath = __DIR__ . '/views/frontend/detail.php';
        }
        include $viewPath;
    }

    public function getFeaturedListings($limit = 6) {
        return $this->model->getFeatured($limit);
    }

    private function generateSlug($text, $excludeId = null) {
        if (empty($text)) return '';
        $tr = ['ş', 'Ş', 'ı', 'İ', 'ğ', 'Ğ', 'ü', 'Ü', 'ö', 'Ö', 'ç', 'Ç'];
        $en = ['s', 's', 'i', 'i', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c'];
        $text = str_replace($tr, $en, $text);
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');
        if (strlen($text) > 200) {
            $text = substr($text, 0, 200);
            $text = rtrim($text, '-');
        }
        if (empty($text)) $text = 'ilan-' . time();
        $originalSlug = $text;
        $counter = 1;
        while (true) {
            $existing = $this->model->findBySlug($text);
            if (!$existing || ($excludeId && (int)$existing['id'] === (int)$excludeId)) break;
            $text = $originalSlug . '-' . $counter;
            $counter++;
        }
        return $text;
    }
}
