<?php
/**
 * Quote Request Module Controller - Teklif Al Sayfası Yönetimi
 * Codetic tema için özelleştirilmiş
 */

class QuoteRequestModuleController extends Controller {
    private $pageModel;
    private $moduleInfo;
    
    public function __construct() {
        // Page model'ini yükle (app/models'den)
        require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/Page.php';
        $this->pageModel = new Page();
    }
    
    /**
     * Modül bilgilerini ayarla
     */
    public function setModuleInfo($info) {
        $this->moduleInfo = $info;
    }
    
    /**
     * Modül yüklendiğinde çalışır
     */
    public function onLoad() {
        // Modül yüklendiğinde yapılacak işlemler
    }
    
    /**
     * Modül aktif edildiğinde çalışır
     */
    public function onActivate() {
        // Aktivasyon işlemleri
    }
    
    /**
     * Modül devre dışı bırakıldığında çalışır
     */
    public function onDeactivate() {
        // Deaktivasyon işlemleri
    }
    
    /**
     * Giriş kontrolü
     */
    private function requireLogin() {
        if (!is_user_logged_in()) {
            $this->redirect(admin_url('login'));
            exit;
        }
    }
    
    /**
     * Yetki kontrolü
     */
    private function checkPermission($permission) {
        $this->requireLogin();
        
        if (!current_user_can($permission)) {
            $_SESSION['error_message'] = 'Bu modülde yetkiniz yoktur!';
            $this->redirect(admin_url('dashboard'));
            exit;
        }
    }
    
    /**
     * Teklif al sayfaları listesi
     */
    public function admin_index() {
        $this->checkPermission('quote-request.view');
        
        $user = get_logged_in_user();
        
        // Sadece teklif al sayfalarını getir (page_template = 'teklif-al' veya 'quote-request')
        $allPages = $this->pageModel->getAll();
        $quotePages = [];
        
        foreach ($allPages as $page) {
            if (isset($page['type']) && $page['type'] === 'page') {
                $customFields = $this->pageModel->getCustomFields($page['id']);
                $pageTemplate = $customFields['page_template'] ?? 'default';
                
                if (in_array($pageTemplate, ['teklif-al', 'quote-request'])) {
                    $page['custom_fields'] = $customFields;
                    $quotePages[] = $page;
                }
            }
        }
        
        $data = [
            'title' => 'Teklif Al Sayfaları',
            'user' => $user,
            'pages' => $quotePages,
            'message' => $_SESSION['quote_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['quote_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null)
        ];
        
        unset($_SESSION['quote_message'], $_SESSION['error_message']);
        
        $this->renderModuleView('admin/index', $data);
    }
    
    /**
     * Yeni teklif al sayfası formu
     */
    public function admin_create() {
        $this->checkPermission('quote-request.create');
        
        // Form listesini yükle
        $forms = $this->getAvailableForms();
        
        $data = [
            'title' => 'Yeni Teklif Al Sayfası',
            'user' => get_logged_in_user(),
            'forms' => $forms
        ];
        
        $this->renderModuleView('admin/create', $data);
    }
    
    /**
     * Teklif al sayfası kaydet
     */
    public function admin_store() {
        $this->checkPermission('quote-request.create');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('module/quote-request'));
            return;
        }
        
        $user = get_logged_in_user();
        
        $data = [
            'title' => trim($_POST['title'] ?? 'Teklif Al'),
            'slug' => trim($_POST['slug'] ?? 'teklif-al'),
            'excerpt' => trim($_POST['excerpt'] ?? 'Projeniz için detaylı teklif alın'),
            'content' => $_POST['content'] ?? '',
            'author_id' => $user['id'],
            'status' => $_POST['status'] ?? 'published',
            'visibility' => 'public',
            'meta_title' => trim($_POST['meta_title'] ?? 'Teklif Al'),
            'meta_description' => trim($_POST['meta_description'] ?? 'Projeniz için detaylı teklif alın'),
            'meta_keywords' => trim($_POST['meta_keywords'] ?? 'teklif, fiyat, proje')
        ];
        
        // Form seçimi zorunlu
        $formId = trim($_POST['quote_form_id'] ?? '');
        if (empty($formId)) {
            $_SESSION['error_message'] = 'Form seçimi zorunludur!';
            $this->redirect(admin_url('module/quote-request/create'));
            return;
        }
        
        // Form'u kontrol et
        require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/Form.php';
        $formModel = new Form();
        $selectedForm = $formModel->find($formId);
        
        if (!$selectedForm || $selectedForm['status'] !== 'active') {
            $_SESSION['error_message'] = 'Seçilen form bulunamadı veya aktif değil!';
            $this->redirect(admin_url('module/quote-request/create'));
            return;
        }
        
        // Özel alanları ayarla
        $customFields = [
            'page_template' => 'teklif-al',
            'quote_form_id' => $formId,
            'quote_form_slug' => $selectedForm['slug']
        ];
        
        $data['custom_fields'] = $customFields;
        
        // Validasyon
        if (empty($data['title'])) {
            $_SESSION['error_message'] = 'Başlık zorunludur!';
            $this->redirect(admin_url('module/quote-request/create'));
            return;
        }
        
        // Slug boşsa title'dan oluştur
        if (empty($data['slug'])) {
            $data['slug'] = 'teklif-al';
        }
        
        try {
            $pageId = $this->pageModel->createPage($data);
            
            // Custom field'ların kaydedildiğinden emin ol
            if ($pageId) {
                $savedFields = $this->pageModel->getCustomFields($pageId);
                error_log("Quote Request - Page created with ID: $pageId");
                error_log("Quote Request - Saved custom fields: " . print_r($savedFields, true));
                
                if (($savedFields['page_template'] ?? '') !== 'teklif-al') {
                    // Tekrar kaydet
                    error_log("Quote Request - Re-saving custom fields...");
                    $this->pageModel->saveCustomFields($pageId, $customFields);
                    $savedFields = $this->pageModel->getCustomFields($pageId);
                    error_log("Quote Request - After re-save: " . print_r($savedFields, true));
                }
            } else {
                error_log("Quote Request - Page creation failed!");
            }
        } catch (Exception $e) {
            error_log("Quote Request - Error creating page: " . $e->getMessage());
            error_log("Quote Request - Stack trace: " . $e->getTraceAsString());
        }
        
        if ($pageId) {
            $_SESSION['quote_message'] = 'Teklif al sayfası başarıyla oluşturuldu!';
            $this->redirect(admin_url('module/quote-request/edit/' . $pageId));
        } else {
            $_SESSION['error_message'] = 'Sayfa oluşturulurken hata oluştu!';
            $this->redirect(admin_url('module/quote-request/create'));
        }
    }
    
    /**
     * Teklif al sayfası düzenleme formu
     */
    public function admin_edit($id) {
        $this->checkPermission('quote-request.edit');
        
        $page = $this->pageModel->findWithDetails($id);
        
        if (!$page || (isset($page['type']) && $page['type'] !== 'page')) {
            $_SESSION['error_message'] = 'Sayfa bulunamadı!';
            $this->redirect(admin_url('module/quote-request'));
            return;
        }
        
        // Özel alanları getir
        $customFields = $this->pageModel->getCustomFields($id);
        $pageTemplate = $customFields['page_template'] ?? 'default';
        
        // Sadece teklif al sayfalarını düzenle
        if (!in_array($pageTemplate, ['teklif-al', 'quote-request'])) {
            $_SESSION['error_message'] = 'Bu sayfa teklif al sayfası değil!';
            $this->redirect(admin_url('module/quote-request'));
            return;
        }
        
        // Form listesini yükle
        $forms = $this->getAvailableForms();
        
        $data = [
            'title' => 'Teklif Al Sayfası Düzenle',
            'user' => get_logged_in_user(),
            'page' => $page,
            'customFields' => $customFields,
            'forms' => $forms,
            'message' => $_SESSION['quote_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['quote_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null)
        ];
        
        unset($_SESSION['quote_message'], $_SESSION['error_message']);
        
        $this->renderModuleView('admin/edit', $data);
    }
    
    /**
     * Mevcut formları getirir
     */
    private function getAvailableForms() {
        try {
            $db = Database::getInstance();
            
            // Aktif formları getir
            $sql = "SELECT id, name, slug, status FROM `forms` WHERE `status` = 'active' ORDER BY `name` ASC";
            $forms = $db->fetchAll($sql);
            
            if (!$forms || !is_array($forms)) {
                // Tüm formları dene (status kontrolü olmadan)
                $sql = "SELECT id, name, slug, status FROM `forms` ORDER BY `name` ASC";
                $forms = $db->fetchAll($sql);
            }
            
            return $forms ?: [];
        } catch (Exception $e) {
            error_log('Forms load error: ' . $e->getMessage());
        } catch (Error $e) {
            error_log('Forms load fatal error: ' . $e->getMessage());
        }
        return [];
    }
    
    /**
     * Teklif al sayfası güncelle
     */
    public function admin_update($id) {
        $this->checkPermission('quote-request.edit');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('module/quote-request'));
            return;
        }
        
        $page = $this->pageModel->find($id);
        
        if (!$page || (isset($page['type']) && $page['type'] !== 'page')) {
            $_SESSION['error_message'] = 'Sayfa bulunamadı!';
            $this->redirect(admin_url('module/quote-request'));
            return;
        }
        
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'excerpt' => trim($_POST['excerpt'] ?? ''),
            'content' => $_POST['content'] ?? '',
            'status' => $_POST['status'] ?? 'published',
            'meta_title' => trim($_POST['meta_title'] ?? ''),
            'meta_description' => trim($_POST['meta_description'] ?? ''),
            'meta_keywords' => trim($_POST['meta_keywords'] ?? '')
        ];
        
        // Form seçimi zorunlu
        $formId = trim($_POST['quote_form_id'] ?? '');
        if (empty($formId)) {
            $_SESSION['error_message'] = 'Form seçimi zorunludur!';
            $this->redirect(admin_url('module/quote-request/edit/' . $id));
            return;
        }
        
        // Form'u kontrol et
        require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/Form.php';
        $formModel = new Form();
        $selectedForm = $formModel->find($formId);
        
        if (!$selectedForm || $selectedForm['status'] !== 'active') {
            $_SESSION['error_message'] = 'Seçilen form bulunamadı veya aktif değil!';
            $this->redirect(admin_url('module/quote-request/edit/' . $id));
            return;
        }
        
        // Özel alanları güncelle
        $customFields = [
            'page_template' => 'teklif-al',
            'quote_form_id' => $formId,
            'quote_form_slug' => $selectedForm['slug']
        ];
        
        $data['custom_fields'] = $customFields;
        
        // Validasyon
        if (empty($data['title'])) {
            $_SESSION['error_message'] = 'Başlık zorunludur!';
            $this->redirect(admin_url('module/quote-request/edit/' . $id));
            return;
        }
        
        // Slug boşsa title'dan oluştur
        if (empty($data['slug'])) {
            $data['slug'] = $page['slug'] ?? 'teklif-al';
        }
        
        $user = get_logged_in_user();
        try {
            $result = $this->pageModel->updateWithVersion($id, $data, $user['id']);
            
            // Custom field'ların kaydedildiğinden emin ol
            if ($result) {
                $savedFields = $this->pageModel->getCustomFields($id);
                error_log("Quote Request - Page updated with ID: $id");
                error_log("Quote Request - Saved custom fields: " . print_r($savedFields, true));
                
                if (($savedFields['page_template'] ?? '') !== 'teklif-al') {
                    // Tekrar kaydet
                    error_log("Quote Request - Re-saving custom fields for page ID: $id");
                    $this->pageModel->saveCustomFields($id, $customFields);
                    $savedFields = $this->pageModel->getCustomFields($id);
                    error_log("Quote Request - After re-save: " . print_r($savedFields, true));
                }
            } else {
                error_log("Quote Request - Page update failed for ID: $id");
            }
            
            if ($result) {
                $_SESSION['quote_message'] = 'Teklif al sayfası başarıyla güncellendi!';
            } else {
                $_SESSION['error_message'] = 'Sayfa güncellenirken hata oluştu!';
            }
        } catch (Exception $e) {
            error_log('Quote page update error: ' . $e->getMessage());
            $_SESSION['error_message'] = 'Sayfa güncellenirken hata: ' . $e->getMessage();
        }
        
        $this->redirect(admin_url('module/quote-request/edit/' . $id));
    }
    
    /**
     * Teklif al sayfası sil
     */
    public function admin_delete($id) {
        $this->checkPermission('quote-request.delete');
        
        $page = $this->pageModel->find($id);
        
        if (!$page || (isset($page['type']) && $page['type'] !== 'page')) {
            $_SESSION['error_message'] = 'Sayfa bulunamadı!';
            $this->redirect(admin_url('module/quote-request'));
            return;
        }
        
        // Çöpe taşı veya tamamen sil
        if ($page['status'] === 'trash') {
            $this->pageModel->delete($id);
            $this->pageModel->deleteCustomFields($id);
            $_SESSION['quote_message'] = 'Sayfa kalıcı olarak silindi!';
        } else {
            $this->pageModel->update($id, ['status' => 'trash']);
            $_SESSION['quote_message'] = 'Sayfa çöpe taşındı!';
        }
        
        $this->redirect(admin_url('module/quote-request'));
    }
    
    /**
     * Modül view'ını render et
     */
    private function renderModuleView($viewName, $data = []) {
        $viewPath = $this->moduleInfo['path'] . '/views/' . $viewName . '.php';
        
        if (!file_exists($viewPath)) {
            die("View not found: $viewPath");
        }
        
        // Data'yı extract et
        extract($data);
        
        // View'ı direkt include et (view kendi header/footer'ını çağırıyor)
        include $viewPath;
    }
}
