<?php
/**
 * Real Estate Listings Module Controller
 */

class RealEstateListingsController extends Controller {
    private $model;
    private $moduleInfo;
    
    public function __construct() {
        require_once __DIR__ . '/Model.php';
        $this->model = new RealEstateListingsModel();
    }
    
    public function setModuleInfo($info) {
        $this->moduleInfo = $info;
    }
    
    public function onLoad() {
        // Module loaded
    }
    
    public function onActivate() {
        $this->model->createTable();
    }
    
    public function onDeactivate() {
        // Deactivation logic
    }
    
    private function requireLogin() {
        if (!is_user_logged_in()) {
            $this->redirect(admin_url('login'));
            exit;
        }
    }
    
    private function checkPermission($permission) {
        $this->requireLogin();
        if (!current_user_can($permission)) {
            $_SESSION['error_message'] = 'Bu işlem için yetkiniz bulunmamaktadır!';
            $this->redirect(admin_url('dashboard'));
            exit;
        }
    }
    
    /**
     * Admin: Tüm ilanları listele
     */
    public function admin_index() {
        $this->checkPermission('realestate-listings.view');
        
        $listings = $this->model->getAll();
        
        $data = [
            'title' => 'Emlak İlanları',
            'user' => get_logged_in_user(),
            'listings' => $listings,
            'message' => $_SESSION['listings_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['listings_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null)
        ];
        
        unset($_SESSION['listings_message'], $_SESSION['error_message']);
        $this->renderModuleView('admin/index', $data);
    }
    
    /**
     * Admin: Yeni ilan ekleme formu
     */
    public function admin_create() {
        $this->checkPermission('realestate-listings.create');
        
        // Emlakçıları getir (emlakçılar modülünden)
        $agents = [];
        if (class_exists('RealEstateAgentsModel')) {
            try {
                require_once __DIR__ . '/../realestate-agents/Model.php';
                $agentsModel = new RealEstateAgentsModel();
                $agents = $agentsModel->getActive();
            } catch (Exception $e) {
                error_log('RealEstateAgentsModel not found: ' . $e->getMessage());
            }
        }
        
        $data = [
            'title' => 'Yeni İlan Ekle',
            'user' => get_logged_in_user(),
            'listing' => null,
            'agents' => $agents
        ];
        
        $this->renderModuleView('admin/create', $data);
    }
    
    /**
     * Admin: Yeni ilan kaydet
     */
    public function admin_store() {
        $this->checkPermission('realestate-listings.create');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('module/realestate-listings'));
            return;
        }
        
        $user = get_logged_in_user();
        
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'slug' => $this->generateSlug(trim($_POST['title'] ?? '')),
            'description' => $_POST['description'] ?? '',
            'location' => trim($_POST['location'] ?? ''),
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
            $this->redirect(admin_url('module/realestate-listings/create'));
            return;
        }
        
        $id = $this->model->create($data);
        
        if ($id) {
            $_SESSION['listings_message'] = 'İlan başarıyla oluşturuldu!';
            $this->redirect(admin_url('module/realestate-listings/edit/' . $id));
        } else {
            $_SESSION['error_message'] = 'İlan oluşturulurken bir hata oluştu!';
            $this->redirect(admin_url('module/realestate-listings/create'));
        }
    }
    
    /**
     * Admin: İlan düzenleme formu
     */
    public function admin_edit($id) {
        $this->checkPermission('realestate-listings.edit');
        
        $listing = $this->model->find($id);
        
        if (!$listing) {
            $_SESSION['error_message'] = 'İlan bulunamadı!';
            $this->redirect(admin_url('module/realestate-listings'));
            return;
        }
        
        // Emlakçıları getir (emlakçılar modülünden)
        $agents = [];
        if (class_exists('RealEstateAgentsModel')) {
            try {
                require_once __DIR__ . '/../realestate-agents/Model.php';
                $agentsModel = new RealEstateAgentsModel();
                $agents = $agentsModel->getActive();
            } catch (Exception $e) {
                error_log('RealEstateAgentsModel not found: ' . $e->getMessage());
            }
        }
        
        $data = [
            'title' => 'İlan Düzenle',
            'user' => get_logged_in_user(),
            'listing' => $listing,
            'agents' => $agents,
            'message' => $_SESSION['listings_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['listings_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null)
        ];
        
        unset($_SESSION['listings_message'], $_SESSION['error_message']);
        $this->renderModuleView('admin/edit', $data);
    }
    
    /**
     * Admin: İlan güncelle
     */
    public function admin_update($id) {
        $this->checkPermission('realestate-listings.edit');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('module/realestate-listings'));
            return;
        }
        
        $listing = $this->model->find($id);
        
        if (!$listing) {
            $_SESSION['error_message'] = 'İlan bulunamadı!';
            $this->redirect(admin_url('module/realestate-listings'));
            return;
        }
        
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'slug' => $this->generateSlug(trim($_POST['slug'] ?? trim($_POST['title'] ?? '')), $id),
            'description' => $_POST['description'] ?? '',
            'location' => trim($_POST['location'] ?? ''),
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
            $this->redirect(admin_url('module/realestate-listings/edit/' . $id));
            return;
        }
        
        $result = $this->model->update($id, $data);
        
        if ($result) {
            $_SESSION['listings_message'] = 'İlan başarıyla güncellendi!';
        } else {
            $_SESSION['error_message'] = 'İlan güncellenirken bir hata oluştu!';
        }
        
        $this->redirect(admin_url('module/realestate-listings/edit/' . $id));
    }
    
    /**
     * Admin: İlan sil
     */
    public function admin_delete($id) {
        $this->checkPermission('realestate-listings.delete');
        
        $result = $this->model->delete($id);
        
        if ($result) {
            $_SESSION['listings_message'] = 'İlan başarıyla silindi!';
        } else {
            $_SESSION['error_message'] = 'İlan silinirken bir hata oluştu!';
        }
        
        $this->redirect(admin_url('module/realestate-listings'));
    }
    
    /**
     * Frontend: İlanlar sayfası
     */
    public function frontend_index() {
        $location = $_GET['location'] ?? '';
        $type = $_GET['type'] ?? '';
        $priceRange = $_GET['price_range'] ?? '';
        
        $listings = $this->model->getPublished($location, $type, $priceRange);
        
        $data = [
            'title' => 'Emlak İlanları',
            'listings' => $listings,
            'filters' => [
                'location' => $location,
                'type' => $type,
                'price_range' => $priceRange
            ]
        ];
        
        $this->renderModuleView('frontend/listings', $data);
    }
    
    /**
     * Frontend: İlan detay sayfası (slug ile)
     */
    public function frontend_detail($slug) {
        // Önce slug ile ara
        $listing = $this->model->findBySlug($slug);
        
        // Slug ile bulunamazsa, ID ile dene (geriye dönük uyumluluk için)
        if (!$listing && is_numeric($slug)) {
            $listing = $this->model->find($slug);
        }
        
        if (!$listing || $listing['status'] !== 'published') {
            header('HTTP/1.0 404 Not Found');
            die('İlan bulunamadı');
        }
        
        // Eğer slug yoksa veya boşsa, slug oluştur ve güncelle
        if (empty($listing['slug'])) {
            $newSlug = $this->generateSlug($listing['title'], $listing['id']);
            $this->model->update($listing['id'], ['slug' => $newSlug]);
            $listing['slug'] = $newSlug;
        }
        
        $data = [
            'title' => $listing['title'],
            'listing' => $listing
        ];
        
        $this->renderModuleView('frontend/detail', $data);
    }
    
    /**
     * Öne çıkan ilanları getir (bileşenler için)
     */
    public function getFeaturedListings($limit = 6) {
        return $this->model->getFeatured($limit);
    }
    
    private function generateSlug($text, $excludeId = null) {
        if (empty($text)) {
            return '';
        }
        
        // Türkçe karakterleri dönüştür
        $tr = ['ş', 'Ş', 'ı', 'İ', 'ğ', 'Ğ', 'ü', 'Ü', 'ö', 'Ö', 'ç', 'Ç'];
        $en = ['s', 's', 'i', 'i', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c'];
        $text = str_replace($tr, $en, $text);
        
        // Küçük harfe çevir (UTF-8 desteği ile)
        $text = mb_strtolower($text, 'UTF-8');
        
        // Alfanumerik olmayan karakterleri tire ile değiştir
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        
        // Baş ve sondaki tireleri kaldır
        $text = trim($text, '-');
        
        // Maksimum uzunluk kontrolü
        if (strlen($text) > 200) {
            $text = substr($text, 0, 200);
            $text = rtrim($text, '-');
        }
        
        // Boşsa varsayılan slug oluştur
        if (empty($text)) {
            $text = 'ilan-' . time();
        }
        
        // Benzersizlik kontrolü (güncelleme sırasında mevcut kaydı hariç tut)
        $originalSlug = $text;
        $counter = 1;
        while (true) {
            $existing = $this->model->findBySlug($text);
            // Slug yoksa veya bulunan kayıt kendisiyse OK
            if (!$existing || ($excludeId && $existing['id'] == $excludeId)) {
                break;
            }
            $text = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $text;
    }
    
    private function renderModuleView($viewName, $data = []) {
        $viewPath = $this->moduleInfo['path'] . '/views/' . $viewName . '.php';
        
        if (!file_exists($viewPath)) {
            die("View not found: $viewPath");
        }
        
        // ThemeLoader'ı ekle
        if (!isset($data['themeLoader']) && class_exists('ThemeLoader')) {
            $data['themeLoader'] = ThemeLoader::getInstance();
        }
        
        // Data'yı extract et
        extract($data);
        
        // View'ı direkt include et
        include $viewPath;
    }
}
