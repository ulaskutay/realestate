<?php
/**
 * Pages Module Controller - Sayfa Yönetimi (Theme Module)
 */

class PagesModuleController extends Controller {
    private $pageModel;
    private $moduleInfo;
    
    public function __construct() {
        // Model'i yükle
        require_once __DIR__ . '/models/PageModel.php';
        $this->pageModel = new PageModel();
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
     * Sayfa listesi
     */
    public function admin_index() {
        $this->checkPermission('pages.view');
        
        $user = get_logged_in_user();
        $statusFilter = $_GET['status'] ?? 'all';
        
        // Sayfaları getir
        if ($statusFilter === 'all') {
            $pages = $this->pageModel->getAll();
        } else {
            $allPages = $this->pageModel->getAll();
            $pages = array_filter($allPages, function($page) use ($statusFilter) {
                return isset($page['type']) && $page['type'] === 'page' && 
                       isset($page['status']) && $page['status'] === $statusFilter;
            });
        }
        
        // İstatistikler
        $stats = [
            'all' => $this->pageModel->getCountByStatus(),
            'published' => $this->pageModel->getCountByStatus('published'),
            'draft' => $this->pageModel->getCountByStatus('draft'),
            'trash' => $this->pageModel->getCountByStatus('trash')
        ];
        
        $data = [
            'title' => 'Sayfalar',
            'user' => $user,
            'pages' => $pages,
            'statusFilter' => $statusFilter,
            'stats' => $stats,
            'message' => $_SESSION['page_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['page_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null)
        ];
        
        unset($_SESSION['page_message'], $_SESSION['error_message']);
        
        $this->renderModuleView('admin/index', $data);
    }
    
    /**
     * Yeni sayfa formu
     */
    public function admin_create() {
        $this->checkPermission('pages.create');
        
        $customFieldDefinitions = PageModel::getCustomFieldDefinitions();
        
        // Form listesini yükle (İletişim sayfası için)
        $forms = $this->getAvailableForms();
        
        // form_id alanına formları ekle
        if (isset($customFieldDefinitions['form_id'])) {
            $formOptions = ['' => 'Form Seçin...'];
            if (!empty($forms) && is_array($forms)) {
                foreach ($forms as $form) {
                    if (isset($form['id']) && isset($form['name'])) {
                        $formOptions[$form['id']] = $form['name'];
                    }
                }
            }
            $customFieldDefinitions['form_id']['options'] = $formOptions;
        }
        
        $data = [
            'title' => 'Yeni Sayfa',
            'user' => get_logged_in_user(),
            'customFieldDefinitions' => $customFieldDefinitions,
            'forms' => $forms
        ];
        
        $this->renderModuleView('admin/create', $data);
    }
    
    /**
     * Sayfa kaydet
     */
    public function admin_store() {
        $this->checkPermission('pages.create');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('module/pages'));
            return;
        }
        
        $user = get_logged_in_user();
        
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'excerpt' => trim($_POST['excerpt'] ?? ''),
            'content' => $_POST['content'] ?? '',
            'author_id' => $user['id'],
            'status' => $_POST['status'] ?? 'draft',
            'visibility' => $_POST['visibility'] ?? 'public',
            'meta_title' => trim($_POST['meta_title'] ?? ''),
            'meta_description' => trim($_POST['meta_description'] ?? ''),
            'meta_keywords' => trim($_POST['meta_keywords'] ?? '')
        ];
        
        // Şifreli sayfa
        if ($data['visibility'] === 'password' && !empty($_POST['page_password'])) {
            $data['password'] = $_POST['page_password'];
        }
        
        // Zamanlanmış yayın
        if (!empty($_POST['published_at'])) {
            $data['published_at'] = $_POST['published_at'];
            if ($data['status'] === 'published' && strtotime($data['published_at']) > time()) {
                $data['status'] = 'scheduled';
            }
        }
        
        // Özel alanları topla
        $customFields = [];
        $customFieldDefinitions = PageModel::getCustomFieldDefinitions();
        
        foreach ($customFieldDefinitions as $key => $definition) {
            if (isset($_POST['custom_fields'][$key])) {
                $value = $_POST['custom_fields'][$key];
                
                if ($definition['type'] === 'checkbox') {
                    $customFields[$key] = isset($_POST['custom_fields'][$key]) ? '1' : '0';
                } elseif ($definition['type'] === 'repeater') {
                    $customFields[$key] = is_string($value) ? $value : json_encode($value);
                } else {
                    $customFields[$key] = $value;
                }
            } else {
                if ($definition['type'] === 'checkbox') {
                    $customFields[$key] = '0';
                }
            }
        }
        
        $data['custom_fields'] = $customFields;
        
        // Validasyon
        if (empty($data['title'])) {
            $_SESSION['error_message'] = 'Başlık zorunludur!';
            $this->redirect(admin_url('module/pages/create'));
            return;
        }
        
        $pageId = $this->pageModel->createPage($data);
        
        if ($pageId) {
            $_SESSION['page_message'] = 'Sayfa başarıyla oluşturuldu!';
            $this->redirect(admin_url('module/pages/edit/' . $pageId));
        } else {
            $_SESSION['error_message'] = 'Sayfa oluşturulurken hata oluştu!';
            $this->redirect(admin_url('module/pages/create'));
        }
    }
    
    /**
     * Sayfa düzenleme formu
     */
    public function admin_edit($id) {
        $this->checkPermission('pages.edit');
        
        $page = $this->pageModel->findWithDetails($id);
        
        if (!$page || (isset($page['type']) && $page['type'] !== 'page')) {
            $_SESSION['error_message'] = 'Sayfa bulunamadı!';
            $this->redirect(admin_url('module/pages'));
            return;
        }
        
        // Özel alanları getir
        $customFields = $this->pageModel->getCustomFields($id);
        $customFieldDefinitions = PageModel::getCustomFieldDefinitions();
        
        // Form listesini yükle (İletişim sayfası için)
        $forms = $this->getAvailableForms();
        
        // form_id alanına formları ekle
        if (isset($customFieldDefinitions['form_id'])) {
            $formOptions = ['' => 'Form Seçin...'];
            if (!empty($forms) && is_array($forms)) {
                foreach ($forms as $form) {
                    if (isset($form['id']) && isset($form['name'])) {
                        $formOptions[$form['id']] = $form['name'];
                    }
                }
            }
            $customFieldDefinitions['form_id']['options'] = $formOptions;
        }
        
        // Versiyon geçmişini al
        $versions = $this->pageModel->getVersions($id);
        
        $data = [
            'title' => 'Sayfa Düzenle',
            'user' => get_logged_in_user(),
            'page' => $page,
            'customFields' => $customFields,
            'customFieldDefinitions' => $customFieldDefinitions,
            'versions' => $versions,
            'forms' => $forms,
            'message' => $_SESSION['page_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['page_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null)
        ];
        
        unset($_SESSION['page_message'], $_SESSION['error_message']);
        
        $this->renderModuleView('admin/edit', $data);
    }
    
    /**
     * Mevcut formları getirir
     */
    private function getAvailableForms() {
        try {
            // Doğrudan veritabanından formları çek (Database sınıfı zaten yüklü)
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
     * Sayfa güncelle
     */
    public function admin_update($id) {
        $this->checkPermission('pages.edit');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('module/pages'));
            return;
        }
        
        $page = $this->pageModel->find($id);
        
        if (!$page || (isset($page['type']) && $page['type'] !== 'page')) {
            $_SESSION['error_message'] = 'Sayfa bulunamadı!';
            $this->redirect(admin_url('module/pages'));
            return;
        }
        
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'excerpt' => trim($_POST['excerpt'] ?? ''),
            'content' => $_POST['content'] ?? '',
            'status' => $_POST['status'] ?? 'draft',
            'visibility' => $_POST['visibility'] ?? 'public',
            'meta_title' => trim($_POST['meta_title'] ?? ''),
            'meta_description' => trim($_POST['meta_description'] ?? ''),
            'meta_keywords' => trim($_POST['meta_keywords'] ?? '')
        ];
        
        // Şifreli sayfa
        if ($data['visibility'] === 'password' && !empty($_POST['page_password'])) {
            $data['password'] = $_POST['page_password'];
        } else {
            $data['password'] = null;
        }
        
        // Zamanlanmış yayın
        if (!empty($_POST['published_at'])) {
            $data['published_at'] = $_POST['published_at'];
            if ($data['status'] === 'published' && strtotime($data['published_at']) > time()) {
                $data['status'] = 'scheduled';
            }
        }
        
        // Özel alanları topla
        $customFields = [];
        $customFieldDefinitions = PageModel::getCustomFieldDefinitions();
        
        foreach ($customFieldDefinitions as $key => $definition) {
            if (isset($_POST['custom_fields'][$key])) {
                $value = $_POST['custom_fields'][$key];
                
                if ($definition['type'] === 'checkbox') {
                    $customFields[$key] = isset($_POST['custom_fields'][$key]) ? '1' : '0';
                } elseif ($definition['type'] === 'repeater') {
                    $customFields[$key] = is_string($value) ? $value : json_encode($value);
                } else {
                    $customFields[$key] = $value;
                }
            } else {
                if ($definition['type'] === 'checkbox') {
                    $customFields[$key] = '0';
                }
            }
        }
        
        $data['custom_fields'] = $customFields;
        
        // Validasyon
        if (empty($data['title'])) {
            $_SESSION['error_message'] = 'Başlık zorunludur!';
            $this->redirect(admin_url('module/pages/edit/' . $id));
            return;
        }
        
        $user = get_logged_in_user();
        try {
            $result = $this->pageModel->updateWithVersion($id, $data, $user['id']);
            
            if ($result) {
                $_SESSION['page_message'] = 'Sayfa başarıyla güncellendi!';
            } else {
                $_SESSION['error_message'] = 'Sayfa güncellenirken hata oluştu!';
            }
        } catch (Exception $e) {
            error_log('Page version error: ' . $e->getMessage());
            $_SESSION['error_message'] = 'Versiyon kaydedilirken hata: ' . $e->getMessage();
        }
        
        $this->redirect(admin_url('module/pages/edit/' . $id));
    }
    
    /**
     * Sayfa sil
     */
    public function admin_delete($id) {
        $this->checkPermission('pages.delete');
        
        $page = $this->pageModel->find($id);
        
        if (!$page || (isset($page['type']) && $page['type'] !== 'page')) {
            $_SESSION['error_message'] = 'Sayfa bulunamadı!';
            $this->redirect(admin_url('module/pages'));
            return;
        }
        
        // Çöpe taşı veya tamamen sil
        if ($page['status'] === 'trash') {
            $this->pageModel->delete($id);
            $this->pageModel->deleteCustomFields($id);
            $_SESSION['page_message'] = 'Sayfa kalıcı olarak silindi!';
        } else {
            $this->pageModel->update($id, ['status' => 'trash']);
            $_SESSION['page_message'] = 'Sayfa çöpe taşındı!';
        }
        
        $this->redirect(admin_url('module/pages'));
    }
    
    /**
     * Sayfayı geri yükle
     */
    public function admin_restore($id) {
        $this->checkPermission('pages.edit');
        
        $page = $this->pageModel->find($id);
        
        if (!$page || $page['status'] !== 'trash' || (isset($page['type']) && $page['type'] !== 'page')) {
            $_SESSION['error_message'] = 'Sayfa bulunamadı!';
            $this->redirect(admin_url('module/pages'));
            return;
        }
        
        $this->pageModel->update($id, ['status' => 'draft']);
        $_SESSION['page_message'] = 'Sayfa geri yüklendi!';
        
        $this->redirect(admin_url('module/pages'));
    }
    
    /**
     * Sayfa durumunu değiştir
     */
    public function admin_toggleStatus($id) {
        $this->checkPermission('pages.edit');
        
        $page = $this->pageModel->find($id);
        
        if (!$page || (isset($page['type']) && $page['type'] !== 'page')) {
            $_SESSION['error_message'] = 'Sayfa bulunamadı!';
            $this->redirect(admin_url('module/pages'));
            return;
        }
        
        $newStatus = $page['status'] === 'published' ? 'draft' : 'published';
        
        $updateData = ['status' => $newStatus];
        
        if ($newStatus === 'published' && empty($page['published_at'])) {
            $updateData['published_at'] = date('Y-m-d H:i:s');
        }
        
        $this->pageModel->update($id, $updateData);
        
        $_SESSION['page_message'] = $newStatus === 'published' ? 'Sayfa yayınlandı!' : 'Sayfa taslak olarak kaydedildi!';
        $this->redirect(admin_url('module/pages'));
    }
    
    /**
     * Versiyon geçmişi sayfası
     */
    public function admin_versions($id) {
        $this->checkPermission('pages.view');
        
        $page = $this->pageModel->findWithDetails($id);
        
        if (!$page || (isset($page['type']) && $page['type'] !== 'page')) {
            $_SESSION['error_message'] = 'Sayfa bulunamadı!';
            $this->redirect(admin_url('module/pages'));
            return;
        }
        
        $versions = $this->pageModel->getVersions($id);
        
        $data = [
            'title' => 'Versiyon Geçmişi: ' . $page['title'],
            'user' => get_logged_in_user(),
            'page' => $page,
            'versions' => $versions,
            'message' => $_SESSION['page_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['page_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null)
        ];
        
        unset($_SESSION['page_message'], $_SESSION['error_message']);
        
        $this->renderModuleView('admin/versions', $data);
    }
    
    /**
     * Sayfayı kopyala
     */
    public function admin_duplicate($id) {
        $this->checkPermission('pages.create');
        
        $user = get_logged_in_user();
        $page = $this->pageModel->findWithDetails($id);
        
        if (!$page || (isset($page['type']) && $page['type'] !== 'page')) {
            $_SESSION['error_message'] = 'Sayfa bulunamadı!';
            $this->redirect(admin_url('module/pages'));
            return;
        }
        
        $customFields = $this->pageModel->getCustomFields($id);
        
        $newData = [
            'title' => $page['title'] . ' (Kopya)',
            'slug' => $this->generateUniqueSlug($page['slug']),
            'excerpt' => $page['excerpt'],
            'content' => $page['content'],
            'author_id' => $user['id'],
            'status' => 'draft',
            'visibility' => $page['visibility'],
            'password' => $page['password'],
            'meta_title' => $page['meta_title'],
            'meta_description' => $page['meta_description'],
            'meta_keywords' => $page['meta_keywords'],
            'custom_fields' => $customFields
        ];
        
        $newPageId = $this->pageModel->createPage($newData);
        
        if ($newPageId) {
            $_SESSION['page_message'] = 'Sayfa başarıyla kopyalandı!';
            $this->redirect(admin_url('module/pages/edit/' . $newPageId));
        } else {
            $_SESSION['error_message'] = 'Sayfa kopyalanırken hata oluştu!';
            $this->redirect(admin_url('module/pages'));
        }
    }
    
    /**
     * Unique slug oluştur
     */
    private function generateUniqueSlug($baseSlug) {
        $slug = $baseSlug . '-kopya';
        $counter = 1;
        
        while ($this->pageModel->findBySlug($slug)) {
            $slug = $baseSlug . '-kopya-' . $counter;
            $counter++;
        }
        
        return $slug;
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

