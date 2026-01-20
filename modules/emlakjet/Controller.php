<?php
/**
 * Emlakjet Modül Controller
 * 
 * Emlakjet platformu ile ilan senkronizasyonu
 */

require_once __DIR__ . '/models/EmlakjetListing.php';
require_once __DIR__ . '/services/EmlakjetApiService.php';
require_once __DIR__ . '/services/ListingMapper.php';
require_once __DIR__ . '/services/SyncService.php';

class EmlakjetModuleController {
    
    private $moduleInfo;
    private $settings;
    private $db;
    private $emlakjetListingModel;
    private $apiService;
    private $mapper;
    private $syncService;
    
    /**
     * Constructor
     */
    public function __construct() {
        if (class_exists('Database')) {
            $this->db = Database::getInstance();
            $this->emlakjetListingModel = new EmlakjetListing();
        }
    }
    
    /**
     * Modül bilgilerini ayarla
     */
    public function setModuleInfo($info) {
        $this->moduleInfo = $info;
    }
    
    /**
     * Modül yüklendiğinde
     */
    public function onLoad() {
        $this->loadSettings();
        
        // Servisleri başlat
        $this->initializeServices();
        
        // Hook'ları kaydet
        if (function_exists('add_action')) {
            add_action('listing_created', [$this, 'onListingCreated'], 10, 1);
            add_action('listing_updated', [$this, 'onListingUpdated'], 10, 1);
            add_action('listing_deleted', [$this, 'onListingDeleted'], 10, 1);
            add_action('listing_published', [$this, 'onListingPublished'], 10, 1);
        }
    }
    
    /**
     * Modül aktif edildiğinde
     */
    public function onActivate() {
        // Tabloyu oluştur
        $this->emlakjetListingModel->createTables();
        
        // Varsayılan ayarları kaydet
        $this->saveDefaultSettings();
    }
    
    /**
     * Modül deaktif edildiğinde
     */
    public function onDeactivate() {
        // Geçici cache temizliği yapılabilir
    }
    
    /**
     * Modül silindiğinde
     */
    public function onUninstall() {
        // Tabloyu sil (opsiyonel - yorum satırında bırakılabilir)
        // $this->emlakjetListingModel->dropTables();
    }
    
    /**
     * Ayarları yükle
     */
    private function loadSettings() {
        if (function_exists('get_module_settings')) {
            $this->settings = get_module_settings('emlakjet');
        }
        
        if (empty($this->settings)) {
            $this->settings = $this->getDefaultSettings();
        }
    }
    
    /**
     * Servisleri başlat
     */
    private function initializeServices() {
        $this->apiService = new EmlakjetApiService($this->settings);
        $this->mapper = new ListingMapper($this->settings);
        $this->syncService = new SyncService(
            $this->apiService,
            $this->mapper,
            $this->emlakjetListingModel
        );
    }
    
    /**
     * Varsayılan ayarlar
     */
    private function getDefaultSettings() {
        return [
            'api_key' => '',
            'api_secret' => '',
            'api_url' => 'https://api.emlakjet.com/v1',
            'test_mode' => true, // Varsayılan olarak test modu aktif
            'auto_sync_enabled' => false,
            'auto_sync_interval' => 60, // dakika
            'sync_on_publish' => true,
            'sync_on_update' => true,
            'retry_attempts' => 3,
            'retry_delay' => 5, // saniye
            'default_sync_direction' => 'push',
            'property_type_mapping' => [],
            'area_unit_conversion' => true
        ];
    }
    
    /**
     * Varsayılan ayarları kaydet
     */
    private function saveDefaultSettings() {
        if (!class_exists('ModuleLoader')) {
            return;
        }
        
        $defaults = $this->getDefaultSettings();
        ModuleLoader::getInstance()->saveModuleSettings('emlakjet', $defaults);
    }
    
    // ==================== HOOK HANDLERS ====================
    
    /**
     * İlan oluşturulduğunda
     */
    public function onListingCreated($listingId) {
        if (!($this->settings['sync_on_publish'] ?? true)) {
            return;
        }
        
        // Otomatik senkronizasyon aktifse ve ilan published ise
        $listing = $this->getListing($listingId);
        if ($listing && $listing['status'] === 'published') {
            $this->syncService->syncListingToEmlakjet($listingId);
        }
    }
    
    /**
     * İlan güncellendiğinde
     */
    public function onListingUpdated($listingId) {
        if (!($this->settings['sync_on_update'] ?? true)) {
            return;
        }
        
        $listing = $this->getListing($listingId);
        if ($listing && $listing['status'] === 'published') {
            $this->syncService->syncListingToEmlakjet($listingId);
        }
    }
    
    /**
     * İlan silindiğinde
     */
    public function onListingDeleted($listingId) {
        $emlakjetListing = $this->emlakjetListingModel->findByListingId($listingId);
        if ($emlakjetListing && $emlakjetListing['emlakjet_id']) {
            $this->syncService->deleteListingFromEmlakjet($emlakjetListing['emlakjet_id'], $listingId);
        }
    }
    
    /**
     * İlan yayınlandığında
     */
    public function onListingPublished($listingId) {
        if ($this->settings['sync_on_publish'] ?? true) {
            $this->syncService->syncListingToEmlakjet($listingId);
        }
    }
    
    // ==================== FRONTEND METHODS ====================
    
    /**
     * Cron senkronizasyon endpoint
     */
    public function cronSync() {
        // Basit güvenlik kontrolü (cron secret)
        $secret = $_GET['secret'] ?? '';
        $expectedSecret = $this->settings['cron_secret'] ?? '';
        
        if (!empty($expectedSecret) && $secret !== $expectedSecret) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        try {
            $result = $this->syncService->syncAllPending();
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'synced' => $result['synced'] ?? 0,
                'failed' => $result['failed'] ?? 0,
                'message' => 'Sync completed'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * Webhook endpoint (Emlakjet'ten gelen güncellemeler)
     */
    public function webhook() {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            exit;
        }
        
        // Webhook doğrulama (signature kontrolü)
        if (!$this->verifyWebhookSignature($input)) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid signature']);
            exit;
        }
        
        // Webhook event'ini işle
        $event = $data['event'] ?? '';
        $listingData = $data['listing'] ?? [];
        
        try {
            switch ($event) {
                case 'listing.updated':
                case 'listing.created':
                    $emlakjetId = $listingData['id'] ?? '';
                    if ($emlakjetId) {
                        $this->syncService->syncListingFromEmlakjet($emlakjetId);
                    }
                    break;
                case 'listing.deleted':
                    $emlakjetId = $listingData['id'] ?? '';
                    if ($emlakjetId) {
                        $this->syncService->handleEmlakjetDeletion($emlakjetId);
                    }
                    break;
            }
            
            http_response_code(200);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Webhook signature doğrulama
     */
    private function verifyWebhookSignature($payload) {
        $secret = $this->settings['webhook_secret'] ?? '';
        
        if (empty($secret)) {
            // Secret yoksa doğrulama yapma (geliştirme için)
            return true;
        }
        
        $signature = $_SERVER['HTTP_X_EMLAKJET_SIGNATURE'] ?? '';
        
        if (empty($signature)) {
            return false;
        }
        
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        
        return hash_equals($expectedSignature, $signature);
    }
    
    // ==================== ADMIN METHODS ====================
    
    /**
     * Admin ana sayfa (Dashboard)
     */
    public function admin_index() {
        $this->ensureInitialized();
        $this->requireLogin();
        
        $stats = $this->emlakjetListingModel->getStats();
        $recentSyncs = $this->emlakjetListingModel->getRecentSyncs(10);
        $failedSyncs = $this->emlakjetListingModel->getFailedSyncs(5);
        
        $this->adminView('index', [
            'title' => 'Emlakjet Dashboard',
            'stats' => $stats,
            'recentSyncs' => $recentSyncs,
            'failedSyncs' => $failedSyncs,
            'settings' => $this->settings
        ]);
    }
    
    /**
     * İlan listesi
     */
    public function admin_listings() {
        $this->ensureInitialized();
        $this->requireLogin();
        
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Filtreleme
        $status = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';
        
        $listings = $this->getListingsWithSyncStatus($status, $search, $perPage, $offset);
        $total = $this->getListingsCount($status, $search);
        $totalPages = ceil($total / $perPage);
        
        $this->adminView('listings', [
            'title' => 'İlan Yönetimi',
            'listings' => $listings,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'status' => $status,
            'search' => $search
        ]);
    }
    
    /**
     * Manuel senkronizasyon
     */
    public function admin_sync() {
        $this->ensureInitialized();
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $listingIds = $_POST['listing_ids'] ?? [];
            $direction = $_POST['direction'] ?? 'push';
            
            if ($action === 'sync') {
                $results = [];
                
                if (empty($listingIds)) {
                    // Tüm bekleyen ilanları senkronize et
                    $results = $this->syncService->syncAllPending();
                } else {
                    // Seçili ilanları senkronize et
                    $synced = 0;
                    $failed = 0;
                    foreach ($listingIds as $listingId) {
                        if ($direction === 'push') {
                            $result = $this->syncService->syncListingToEmlakjet($listingId);
                        } else {
                            $emlakjetListing = $this->emlakjetListingModel->findByListingId($listingId);
                            if ($emlakjetListing && $emlakjetListing['emlakjet_id']) {
                                $result = $this->syncService->syncListingFromEmlakjet($emlakjetListing['emlakjet_id']);
                            } else {
                                $result = ['success' => false, 'error' => 'Emlakjet ID bulunamadı'];
                            }
                        }
                        
                        if ($result['success'] ?? false) {
                            $synced++;
                        } else {
                            $failed++;
                        }
                        $results[] = $result;
                    }
                    
                    $results = [
                        'synced' => $synced,
                        'failed' => $failed,
                        'total' => count($listingIds),
                        'results' => $results
                    ];
                }
                
                // AJAX isteği ise JSON döndür
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => 'Senkronizasyon tamamlandı',
                        'data' => $results
                    ]);
                    exit;
                }
                
                $_SESSION['flash_message'] = 'Senkronizasyon tamamlandı';
                $_SESSION['flash_type'] = 'success';
                $this->redirect('sync');
                return;
            }
        }
        
        // Bekleyen ilanları getir
        $pendingListings = $this->getPendingListings();
        
        $this->adminView('sync', [
            'title' => 'Manuel Senkronizasyon',
            'pendingListings' => $pendingListings
        ]);
    }
    
    /**
     * Ayarlar
     */
    public function admin_settings() {
        $this->ensureInitialized();
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->settings['api_key'] = $_POST['api_key'] ?? '';
            $this->settings['api_secret'] = $_POST['api_secret'] ?? '';
            $this->settings['api_url'] = $_POST['api_url'] ?? 'https://api.emlakjet.com/v1';
            $this->settings['test_mode'] = isset($_POST['test_mode']);
            $this->settings['auto_sync_enabled'] = isset($_POST['auto_sync_enabled']);
            $this->settings['auto_sync_interval'] = (int)($_POST['auto_sync_interval'] ?? 60);
            $this->settings['sync_on_publish'] = isset($_POST['sync_on_publish']);
            $this->settings['sync_on_update'] = isset($_POST['sync_on_update']);
            $this->settings['retry_attempts'] = (int)($_POST['retry_attempts'] ?? 3);
            $this->settings['default_sync_direction'] = $_POST['default_sync_direction'] ?? 'push';
            $this->settings['cron_secret'] = $_POST['cron_secret'] ?? '';
            $this->settings['webhook_secret'] = $_POST['webhook_secret'] ?? '';
            
            ModuleLoader::getInstance()->saveModuleSettings('emlakjet', $this->settings);
            
            $_SESSION['flash_message'] = 'Ayarlar kaydedildi';
            $_SESSION['flash_type'] = 'success';
            $this->redirect('settings');
            return;
        }
        
        // API test sonucu
        $testResult = null;
        if (isset($_GET['test_api'])) {
            $testResult = $this->testApiConnection();
        }
        
        $this->adminView('settings', [
            'title' => 'Emlakjet Ayarları',
            'settings' => $this->settings,
            'testResult' => $testResult
        ]);
    }
    
    /**
     * API bağlantı testi
     */
    private function testApiConnection() {
        try {
            $this->initializeServices();
            $result = $this->apiService->authenticate();
            
            $testMode = $this->settings['test_mode'] ?? false;
            $message = $result 
                ? ($testMode ? 'Test modu: Mock API bağlantısı başarılı' : 'API bağlantısı başarılı')
                : 'API bağlantısı başarısız';
            
            return [
                'success' => $result,
                'message' => $message
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Hata: ' . $e->getMessage()
            ];
        }
    }
    
    // ==================== HELPER METHODS ====================
    
    /**
     * İlan getir
     */
    private function getListing($listingId) {
        require_once dirname(dirname(__DIR__)) . '/themes/realestate/modules/realestate-listings/Model.php';
        $listingModel = new RealEstateListingsModel();
        return $listingModel->find($listingId);
    }
    
    /**
     * Senkronizasyon durumu ile ilanları getir
     */
    private function getListingsWithSyncStatus($status = '', $search = '', $limit = 20, $offset = 0) {
        require_once dirname(dirname(__DIR__)) . '/themes/realestate/modules/realestate-listings/Model.php';
        $listingModel = new RealEstateListingsModel();
        
        $sql = "SELECT l.*, 
                ej.id as emlakjet_sync_id,
                ej.emlakjet_id,
                ej.sync_status,
                ej.last_sync_at,
                ej.last_error
                FROM `realestate_listings` l
                LEFT JOIN `emlakjet_listings` ej ON l.id = ej.listing_id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($status)) {
            if ($status === 'synced') {
                $sql .= " AND ej.sync_status = 'synced'";
            } elseif ($status === 'pending') {
                $sql .= " AND (ej.sync_status = 'pending' OR ej.sync_status IS NULL)";
            } elseif ($status === 'failed') {
                $sql .= " AND ej.sync_status = 'failed'";
            }
        }
        
        if (!empty($search)) {
            $sql .= " AND (l.title LIKE ? OR l.location LIKE ?)";
            $searchPattern = '%' . $search . '%';
            $params[] = $searchPattern;
            $params[] = $searchPattern;
        }
        
        $sql .= " ORDER BY l.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * İlan sayısı
     */
    private function getListingsCount($status = '', $search = '') {
        $sql = "SELECT COUNT(*) as count
                FROM `realestate_listings` l
                LEFT JOIN `emlakjet_listings` ej ON l.id = ej.listing_id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($status)) {
            if ($status === 'synced') {
                $sql .= " AND ej.sync_status = 'synced'";
            } elseif ($status === 'pending') {
                $sql .= " AND (ej.sync_status = 'pending' OR ej.sync_status IS NULL)";
            } elseif ($status === 'failed') {
                $sql .= " AND ej.sync_status = 'failed'";
            }
        }
        
        if (!empty($search)) {
            $sql .= " AND (l.title LIKE ? OR l.location LIKE ?)";
            $searchPattern = '%' . $search . '%';
            $params[] = $searchPattern;
            $params[] = $searchPattern;
        }
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
    
    /**
     * Bekleyen ilanları getir
     */
    private function getPendingListings() {
        $sql = "SELECT l.*, ej.sync_status, ej.last_error
                FROM `realestate_listings` l
                LEFT JOIN `emlakjet_listings` ej ON l.id = ej.listing_id
                WHERE l.status = 'published'
                AND (ej.sync_status = 'pending' OR ej.sync_status IS NULL OR ej.sync_status = 'failed')
                ORDER BY l.created_at DESC
                LIMIT 50";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Model ve ayarların yüklendiğinden emin ol
     */
    private function ensureInitialized() {
        if (!$this->db && class_exists('Database')) {
            $this->db = Database::getInstance();
        }
        if (!$this->emlakjetListingModel) {
            $this->emlakjetListingModel = new EmlakjetListing();
        }
        if (empty($this->settings)) {
            $this->loadSettings();
        }
        $this->initializeServices();
    }
    
    /**
     * Giriş kontrolü
     */
    private function requireLogin() {
        if (!function_exists('is_user_logged_in') || !is_user_logged_in()) {
            header('Location: ' . admin_url('login'));
            exit;
        }
    }
    
    /**
     * Admin view render
     */
    private function adminView($view, $data = []) {
        $viewPath = __DIR__ . '/views/admin/' . $view . '.php';
        $basePath = dirname(dirname(__DIR__));
        
        if (!file_exists($viewPath)) {
            echo "View not found: " . $view;
            return;
        }
        
        extract($data);
        $currentPage = 'module/emlakjet';
        
        include $basePath . '/app/views/admin/snippets/header.php';
        ?>
        <div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
            <div class="flex min-h-screen">
                <!-- SideNavBar -->
                <?php include $basePath . '/app/views/admin/snippets/sidebar.php'; ?>

                <!-- Content Area with Header -->
                <div class="flex-1 flex flex-col lg:ml-64">
                    <!-- Top Header -->
                    <?php include $basePath . '/app/views/admin/snippets/top-header.php'; ?>

                    <!-- Main Content -->
                    <main class="flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b] overflow-y-auto">
                        <div class="max-w-7xl mx-auto">
                            <?php include $viewPath; ?>
                        </div>
                    </main>
                </div>
            </div>
        </div>
        <?php
        include $basePath . '/app/views/admin/snippets/footer.php';
    }
    
    /**
     * Yönlendirme
     */
    private function redirect($action) {
        if (empty($action)) {
            $url = admin_url('module/emlakjet');
        } else {
            $url = admin_url('module/emlakjet/' . $action);
        }
        header("Location: " . $url);
        exit;
    }
}
