<?php
/**
 * CRM Modül Controller
 * 
 * Emlak sektörü için CRM yönetimi
 */

require_once __DIR__ . '/models/CrmModel.php';
require_once __DIR__ . '/models/LeadNote.php';
require_once __DIR__ . '/models/LeadTask.php';
require_once __DIR__ . '/models/LeadConversion.php';

class CrmModuleController {
    
    private $moduleInfo;
    private $settings;
    private $db;
    private $leadModel;
    private $noteModel;
    private $taskModel;
    private $conversionModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        if (class_exists('Database')) {
            $this->db = Database::getInstance();
            $this->leadModel = new CrmModel();
            $this->noteModel = new LeadNote();
            $this->taskModel = new LeadTask();
            $this->conversionModel = new LeadConversion();
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
        
        // Hook'ları kaydet
        if (function_exists('add_action')) {
            add_action('form_submitted', [$this, 'handleFormSubmission'], 10, 2);
        }
    }
    
    /**
     * Modül aktif edildiğinde
     */
    public function onActivate() {
        // Tabloları oluştur
        $this->leadModel->createTables();
        $this->noteModel->createTables();
        $this->taskModel->createTables();
        $this->conversionModel->createTables();
        
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
        // Tabloları sil (opsiyonel - yorum satırında bırakılabilir)
        // $this->leadModel->dropTables();
    }
    
    /**
     * Ayarları yükle
     */
    private function loadSettings() {
        if (function_exists('get_module_settings')) {
            $this->settings = get_module_settings('crm');
        }
        
        if (empty($this->settings)) {
            $this->settings = $this->getDefaultSettings();
        }
    }
    
    /**
     * Varsayılan ayarlar
     */
    private function getDefaultSettings() {
        return [
            'meta_webhook_secret' => '',
            'meta_webhook_verify_token' => '',
            'auto_create_from_forms' => true,
            'default_lead_status' => 'new',
            'email_templates' => [],
            'whatsapp_message_template' => 'Merhaba {name}, size nasıl yardımcı olabilirim?'
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
        ModuleLoader::getInstance()->saveModuleSettings('crm', $defaults);
    }
    
    /**
     * Form gönderiminden lead oluştur
     */
    public function handleFormSubmission($formId, $submissionData) {
        if (!($this->settings['auto_create_from_forms'] ?? true)) {
            return;
        }
        
        // Form field mapping yapılacak
        $leadData = $this->mapFormDataToLead($formId, $submissionData);
        
        if (!empty($leadData)) {
            $leadData['source'] = 'form';
            $leadData['form_submission_id'] = $submissionData['id'] ?? null;
            $this->leadModel->create($leadData);
        }
    }
    
    /**
     * Form verilerini lead verilerine map et
     */
    private function mapFormDataToLead($formId, $submissionData) {
        $data = $submissionData['data'] ?? [];
        $leadData = [];
        
        // Standart field mapping
        $fieldMapping = [
            'name' => ['name', 'isim', 'ad', 'ad_soyad', 'full_name'],
            'phone' => ['phone', 'telefon', 'tel', 'phone_number', 'mobile'],
            'email' => ['email', 'e-posta', 'eposta', 'mail'],
            'property_type' => ['property_type', 'emlak_tipi', 'tip', 'type'],
            'location' => ['location', 'lokasyon', 'ilce', 'district', 'city'],
            'budget' => ['budget', 'butce', 'fiyat', 'price'],
            'room_count' => ['room_count', 'oda_sayisi', 'rooms', 'bedrooms']
        ];
        
        foreach ($fieldMapping as $leadField => $possibleFields) {
            foreach ($possibleFields as $formField) {
                if (isset($data[$formField]) && !empty($data[$formField])) {
                    $leadData[$leadField] = $data[$formField];
                    break;
                }
            }
        }
        
        return $leadData;
    }
    
    // ==================== FRONTEND METHODS ====================
    
    /**
     * Meta Lead Ads Webhook
     */
    public function metaWebhook() {
        // Webhook doğrulama ve işleme
        require_once __DIR__ . '/services/MetaWebhookService.php';
        $service = new MetaWebhookService($this->settings);
        $service->handleWebhook();
    }
    
    // ==================== ADMIN METHODS ====================
    
    /**
     * Admin ana sayfa (Dashboard)
     */
    public function admin_index() {
        $this->ensureInitialized();
        $this->requireLogin();
        
        $stats = $this->leadModel->getStats();
        $recentLeads = $this->leadModel->getRecent(10);
        $sourceStats = $this->leadModel->getSourceStats();
        $statusStats = $this->leadModel->getStatusStats();
        
        $this->adminView('index', [
            'title' => 'CRM Dashboard',
            'stats' => $stats,
            'recentLeads' => $recentLeads,
            'sourceStats' => $sourceStats,
            'statusStats' => $statusStats,
            'settings' => $this->settings
        ]);
    }
    
    /**
     * Lead listesi
     */
    public function admin_leads() {
        try {
            $this->ensureInitialized();
            $this->requireLogin();
            
            $page = (int)($_GET['page'] ?? 1);
            $perPage = 20;
            $offset = ($page - 1) * $perPage;
            
            // Arama ve filtreleme
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $source = $_GET['source'] ?? '';
            $dateFrom = $_GET['date_from'] ?? '';
            $dateTo = $_GET['date_to'] ?? '';
            
            try {
                $leads = $this->leadModel->search($search, [
                    'status' => $status,
                    'source' => $source,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo
                ], $perPage, $offset);
                
                // Eğer null veya false dönerse boş array yap
                if ($leads === null || $leads === false) {
                    $leads = [];
                }
                
                // Array değilse boş array yap
                if (!is_array($leads)) {
                    $leads = [];
                }
                
                $total = $this->leadModel->searchCount($search, [
                    'status' => $status,
                    'source' => $source,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo
                ]);
                
                // Total değeri de kontrol et
                if (!is_numeric($total)) {
                    $total = 0;
                }
            } catch (Exception $e) {
                error_log("CRM Leads search error: " . $e->getMessage() . " - Trace: " . $e->getTraceAsString());
                $leads = [];
                $total = 0;
            }
            
            $totalPages = ceil($total / $perPage);
            
            $this->adminView('leads', [
                'title' => 'Leadler',
                'leads' => $leads ?: [],
                'page' => $page,
                'totalPages' => $totalPages,
                'total' => $total,
                'search' => $search,
                'filters' => [
                    'status' => $status,
                    'source' => $source,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo
                ]
            ]);
        } catch (Exception $e) {
            error_log("CRM admin_leads error: " . $e->getMessage() . " - Trace: " . $e->getTraceAsString());
            // Hata durumunda bile sayfayı göster, boş liste ile
            $this->adminView('leads', [
                'title' => 'Leadler',
                'leads' => [],
                'page' => 1,
                'totalPages' => 1,
                'total' => 0,
                'search' => $_GET['search'] ?? '',
                'filters' => [
                    'status' => $_GET['status'] ?? '',
                    'source' => $_GET['source'] ?? '',
                    'date_from' => $_GET['date_from'] ?? '',
                    'date_to' => $_GET['date_to'] ?? ''
                ]
            ]);
        }
    }
    
    /**
     * Lead detay
     */
    public function admin_lead_view($id) {
        $this->ensureInitialized();
        $this->requireLogin();
        
        $lead = $this->leadModel->find($id);
        
        if (!$lead) {
            $_SESSION['flash_message'] = 'Lead bulunamadı';
            $_SESSION['flash_type'] = 'error';
            $this->redirect('');
            return;
        }
        
        $notes = $this->noteModel->getByLeadId($id);
        $tasks = $this->taskModel->getByLeadId($id);
        $conversions = $this->conversionModel->getByLeadId($id);
        
        // Seçilen ilanı getir
        $selectedPost = null;
        if (!empty($lead['post_id'])) {
            try {
                require_once dirname(dirname(__DIR__)) . '/app/models/Post.php';
                $postModel = new Post();
                $selectedPost = $postModel->find($lead['post_id']);
            } catch (Exception $e) {
                error_log("CRM: Error fetching post: " . $e->getMessage());
            }
        }
        
        // WhatsApp mesaj şablonu
        $whatsappTemplate = $this->settings['whatsapp_message_template'] ?? 'Merhaba {name}, size nasıl yardımcı olabilirim?';
        $whatsappMessage = str_replace('{name}', $lead['name'], $whatsappTemplate);
        
        $this->adminView('lead-view', [
            'title' => 'Lead Detayı',
            'lead' => $lead,
            'notes' => $notes,
            'tasks' => $tasks,
            'conversions' => $conversions,
            'selectedPost' => $selectedPost,
            'whatsappMessage' => $whatsappMessage
        ]);
    }
    
    /**
     * Yeni lead formu
     */
    public function admin_lead_create() {
        $this->ensureInitialized();
        $this->requireLogin();
        
        // Yayınlanmış ilanları getir
        $posts = $this->getPublishedPosts();
        
        $this->adminView('lead-form', [
            'title' => 'Yeni Lead',
            'lead' => null,
            'action' => 'lead_store',
            'posts' => $posts
        ]);
    }
    
    /**
     * Lead kaydet
     */
    public function admin_lead_store() {
        $this->ensureInitialized();
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('lead_create');
            return;
        }
        
        $data = $this->prepareLeadData($_POST);
        
        $result = $this->leadModel->create($data);
        
        if ($result) {
            $_SESSION['flash_message'] = 'Lead başarıyla oluşturuldu';
            $_SESSION['flash_type'] = 'success';
            // Ana sayfaya yönlendir ve sayfayı yenile
            header("Location: " . admin_url('module/crm') . "?refresh=" . time());
            exit;
        } else {
            $_SESSION['flash_message'] = 'Lead oluşturulurken hata oluştu';
            $_SESSION['flash_type'] = 'error';
            $this->redirect('lead_create');
        }
    }
    
    /**
     * Lead düzenle
     */
    public function admin_lead_edit($id) {
        $this->ensureInitialized();
        $this->requireLogin();
        
        $lead = $this->leadModel->find($id);
        
        if (!$lead) {
            $_SESSION['flash_message'] = 'Lead bulunamadı';
            $_SESSION['flash_type'] = 'error';
            $this->redirect('');
            return;
        }
        
        // Yayınlanmış ilanları getir
        $posts = $this->getPublishedPosts();
        
        $this->adminView('lead-form', [
            'title' => 'Lead Düzenle',
            'lead' => $lead,
            'action' => 'lead_update/' . $id,
            'posts' => $posts
        ]);
    }
    
    /**
     * Lead güncelle
     */
    public function admin_lead_update($id) {
        $this->ensureInitialized();
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('lead_edit/' . $id);
            return;
        }
        
        $data = $this->prepareLeadData($_POST);
        
        $result = $this->leadModel->update($id, $data);
        
        if ($result) {
            $_SESSION['flash_message'] = 'Lead güncellendi';
            $_SESSION['flash_type'] = 'success';
            // Ana sayfaya yönlendir ve sayfayı yenile
            header("Location: " . admin_url('module/crm') . "?refresh=" . time());
            exit;
        } else {
            $_SESSION['flash_message'] = 'Güncelleme sırasında hata oluştu';
            $_SESSION['flash_type'] = 'error';
            $this->redirect('lead_edit/' . $id);
        }
    }
    
    /**
     * Lead sil
     */
    public function admin_lead_delete($id) {
        $this->ensureInitialized();
        $this->requireLogin();
        
        $result = $this->leadModel->delete($id);
        
        if ($result) {
            $_SESSION['flash_message'] = 'Lead silindi';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Silme işlemi başarısız';
            $_SESSION['flash_type'] = 'error';
        }
        
        $this->redirect('leads');
    }
    
    /**
     * Lead durum güncelle
     */
    public function admin_lead_update_status() {
        $this->ensureInitialized();
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $status = $_POST['status'] ?? '';
        
        $result = $this->leadModel->updateStatus($id, $status);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Durum güncellendi' : 'Hata oluştu'
        ]);
        exit;
    }
    
    /**
     * Kanban görünümü
     */
    public function admin_leads_kanban() {
        $this->ensureInitialized();
        $this->requireLogin();
        
        $leads = $this->leadModel->getAllForKanban();
        
        $this->adminView('kanban', [
            'title' => 'Kanban Görünümü',
            'leads' => $leads
        ]);
    }
    
    /**
     * Not ekle
     */
    public function admin_note_add() {
        $this->ensureInitialized();
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }
        
        $leadId = $_POST['lead_id'] ?? 0;
        $note = $_POST['note'] ?? '';
        $userId = get_logged_in_user()['id'] ?? 0;
        
        $result = $this->noteModel->create([
            'lead_id' => $leadId,
            'note' => $note,
            'user_id' => $userId
        ]);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $result !== false,
            'message' => $result ? 'Not eklendi' : 'Hata oluştu',
            'note_id' => $result
        ]);
        exit;
    }
    
    /**
     * Görev ekle
     */
    public function admin_task_add() {
        $this->ensureInitialized();
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }
        
        $data = [
            'lead_id' => $_POST['lead_id'] ?? 0,
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'due_date' => $_POST['due_date'] ?? null,
            'assigned_to' => $_POST['assigned_to'] ?? get_logged_in_user()['id'] ?? 0,
            'status' => 'pending'
        ];
        
        $result = $this->taskModel->create($data);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $result !== false,
            'message' => $result ? 'Görev eklendi' : 'Hata oluştu',
            'task_id' => $result
        ]);
        exit;
    }
    
    /**
     * E-posta gönder
     */
    public function admin_send_email() {
        $this->ensureInitialized();
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }
        
        $leadId = $_POST['lead_id'] ?? 0;
        $subject = $_POST['subject'] ?? '';
        $body = $_POST['body'] ?? '';
        
        $lead = $this->leadModel->find($leadId);
        
        if (!$lead || empty($lead['email'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Lead bulunamadı veya e-posta adresi yok']);
            exit;
        }
        
        require_once dirname(dirname(__DIR__)) . '/core/Mailer.php';
        $mailer = Mailer::getInstance();
        
        // Şablon değişkenlerini değiştir
        $body = str_replace('{name}', $lead['name'], $body);
        $body = str_replace('{phone}', $lead['phone'], $body);
        
        $result = $mailer->send($lead['email'], $subject, $body, ['isHtml' => true]);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'E-posta gönderildi' : 'E-posta gönderilemedi: ' . $mailer->getLastError()
        ]);
        exit;
    }
    
    /**
     * Dönüşüm kaydet
     */
    public function admin_conversion_add() {
        $this->ensureInitialized();
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }
        
        $data = [
            'lead_id' => $_POST['lead_id'] ?? 0,
            'type' => $_POST['type'] ?? 'sale',
            'value' => $_POST['value'] ?? 0,
            'notes' => $_POST['notes'] ?? ''
        ];
        
        $result = $this->conversionModel->create($data);
        
        if ($result) {
            // Lead durumunu "kapandı" olarak güncelle
            $this->leadModel->updateStatus($data['lead_id'], 'closed');
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $result !== false,
            'message' => $result ? 'Dönüşüm kaydedildi' : 'Hata oluştu',
            'conversion_id' => $result
        ]);
        exit;
    }
    
    /**
     * Ayarlar
     */
    public function admin_settings() {
        $this->ensureInitialized();
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->settings['meta_webhook_secret'] = $_POST['meta_webhook_secret'] ?? '';
            $this->settings['meta_webhook_verify_token'] = $_POST['meta_webhook_verify_token'] ?? '';
            $this->settings['auto_create_from_forms'] = isset($_POST['auto_create_from_forms']);
            $this->settings['default_lead_status'] = $_POST['default_lead_status'] ?? 'new';
            $this->settings['whatsapp_message_template'] = $_POST['whatsapp_message_template'] ?? '';
            
            ModuleLoader::getInstance()->saveModuleSettings('crm', $this->settings);
            
            $_SESSION['flash_message'] = 'Ayarlar kaydedildi';
            $_SESSION['flash_type'] = 'success';
            $this->redirect('settings');
            return;
        }
        
        $this->adminView('settings', [
            'title' => 'CRM Ayarları',
            'settings' => $this->settings
        ]);
    }
    
    /**
     * Lead verilerini hazırla
     */
    private function prepareLeadData($postData) {
        return [
            'name' => $postData['name'] ?? '',
            'phone' => $postData['phone'] ?? '',
            'email' => $postData['email'] ?? '',
            'property_type' => $postData['property_type'] ?? '',
            'post_id' => !empty($postData['post_id']) ? (int)$postData['post_id'] : null,
            'location' => $postData['location'] ?? '',
            'budget' => $postData['budget'] ?? '',
            'room_count' => $postData['room_count'] ?? '',
            'source' => $postData['source'] ?? 'manual',
            'status' => $postData['status'] ?? ($this->settings['default_lead_status'] ?? 'new'),
            'notes' => $postData['notes'] ?? '',
            'meta_lead_id' => $postData['meta_lead_id'] ?? null,
            'form_submission_id' => $postData['form_submission_id'] ?? null
        ];
    }
    
    /**
     * Yayınlanmış ilanları getir
     */
    private function getPublishedPosts() {
        try {
            require_once dirname(dirname(__DIR__)) . '/app/models/Post.php';
            $postModel = new Post();
            return $postModel->getPublished(100); // Son 100 ilan
        } catch (Exception $e) {
            error_log("CRM: Error fetching posts: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Model ve ayarların yüklendiğinden emin ol
     */
    private function ensureInitialized() {
        if (!$this->db && class_exists('Database')) {
            $this->db = Database::getInstance();
        }
        if (!$this->leadModel) {
            $this->leadModel = new CrmModel();
        }
        if (!$this->noteModel) {
            $this->noteModel = new LeadNote();
        }
        if (!$this->taskModel) {
            $this->taskModel = new LeadTask();
        }
        if (!$this->conversionModel) {
            $this->conversionModel = new LeadConversion();
        }
        if (empty($this->settings)) {
            $this->loadSettings();
        }
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
        $currentPage = 'module/crm';
        
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
        // Boş action ise ana sayfaya git
        if (empty($action)) {
            $url = admin_url('module/crm');
        } else {
            $url = admin_url('module/crm/' . $action);
        }
        header("Location: " . $url);
        exit;
    }
}
