<?php
/**
 * Page Controller - Sayfa Yönetimi
 */

class PageController extends Controller {
    private $pageModel;
    
    public function __construct() {
        $this->pageModel = new Page();
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
    public function index() {
        $this->checkPermission('pages.view');
        
        $user = get_logged_in_user();
        $statusFilter = $_GET['status'] ?? 'all';
        
        // Sayfaları getir
        if ($statusFilter === 'all') {
            $pages = $this->pageModel->getAll();
        } else {
            // Tüm sayfaları getir ve status'e göre filtrele
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
        
        $this->view('admin/pages/index', $data);
    }
    
    /**
     * Yeni sayfa formu
     */
    public function create() {
        $this->checkPermission('pages.create');
        
        $customFieldDefinitions = Page::getCustomFieldDefinitions();
        
        $data = [
            'title' => 'Yeni Sayfa',
            'user' => get_logged_in_user(),
            'customFieldDefinitions' => $customFieldDefinitions
        ];
        
        $this->view('admin/pages/create', $data);
    }
    
    /**
     * Sayfa kaydet
     */
    public function store() {
        $this->checkPermission('pages.create');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('pages'));
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
        $customFieldDefinitions = Page::getCustomFieldDefinitions();
        
        foreach ($customFieldDefinitions as $key => $definition) {
            if (isset($_POST['custom_fields'][$key])) {
                $value = $_POST['custom_fields'][$key];
                
                if ($definition['type'] === 'checkbox') {
                    $customFields[$key] = isset($_POST['custom_fields'][$key]) ? '1' : '0';
                } elseif ($definition['type'] === 'repeater') {
                    // Repeater alanları zaten JSON olarak geliyor
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
            $this->redirect(admin_url('pages/create'));
            return;
        }
        
        $pageId = $this->pageModel->createPage($data);
        
        if ($pageId) {
            $_SESSION['page_message'] = 'Sayfa başarıyla oluşturuldu!';
            $this->redirect(admin_url('pages/edit/' . $pageId));
        } else {
            $_SESSION['error_message'] = 'Sayfa oluşturulurken hata oluştu!';
            $this->redirect(admin_url('pages/create'));
        }
    }
    
    /**
     * Sayfa düzenleme formu
     */
    public function edit($id) {
        $this->checkPermission('pages.edit');
        
        $page = $this->pageModel->findWithDetails($id);
        
        if (!$page || (isset($page['type']) && $page['type'] !== 'page')) {
            $_SESSION['error_message'] = 'Sayfa bulunamadı!';
            $this->redirect(admin_url('pages'));
            return;
        }
        
        // Özel alanları getir
        $customFields = $this->pageModel->getCustomFields($id);
        $customFieldDefinitions = Page::getCustomFieldDefinitions();
        
        // Versiyon geçmişini al
        $versions = $this->pageModel->getVersions($id);
        
        $data = [
            'title' => 'Sayfa Düzenle',
            'user' => get_logged_in_user(),
            'page' => $page,
            'customFields' => $customFields,
            'customFieldDefinitions' => $customFieldDefinitions,
            'versions' => $versions,
            'message' => $_SESSION['page_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['page_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null)
        ];
        
        unset($_SESSION['page_message'], $_SESSION['error_message']);
        
        $this->view('admin/pages/edit', $data);
    }
    
    /**
     * Sayfa güncelle
     */
    public function update($id) {
        $this->checkPermission('pages.edit');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('pages'));
            return;
        }
        
        $page = $this->pageModel->find($id);
        
        if (!$page || (isset($page['type']) && $page['type'] !== 'page')) {
            $_SESSION['error_message'] = 'Sayfa bulunamadı!';
            $this->redirect(admin_url('pages'));
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
        $customFieldDefinitions = Page::getCustomFieldDefinitions();
        
        foreach ($customFieldDefinitions as $key => $definition) {
            if (isset($_POST['custom_fields'][$key])) {
                $value = $_POST['custom_fields'][$key];
                
                if ($definition['type'] === 'checkbox') {
                    $customFields[$key] = isset($_POST['custom_fields'][$key]) ? '1' : '0';
                } elseif ($definition['type'] === 'repeater') {
                    // Repeater alanları zaten JSON olarak geliyor
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
            $this->redirect(admin_url('pages/edit/' . $id));
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
        
        $this->redirect(admin_url('pages/edit/' . $id));
    }
    
    /**
     * Sayfa sil
     */
    public function delete($id) {
        $this->checkPermission('pages.delete');
        
        $page = $this->pageModel->find($id);
        
        if (!$page || (isset($page['type']) && $page['type'] !== 'page')) {
            $_SESSION['error_message'] = 'Sayfa bulunamadı!';
            $this->redirect(admin_url('pages'));
            return;
        }
        
        // Çöpe taşı veya tamamen sil
        if ($page['status'] === 'trash') {
            // Tamamen sil
            $this->pageModel->delete($id);
            // Özel alanları da sil
            $this->pageModel->deleteCustomFields($id);
            $_SESSION['page_message'] = 'Sayfa kalıcı olarak silindi!';
        } else {
            // Çöpe taşı
            $this->pageModel->update($id, ['status' => 'trash']);
            $_SESSION['page_message'] = 'Sayfa çöpe taşındı!';
        }
        
        $this->redirect(admin_url('pages'));
    }
    
    /**
     * Sayfayı geri yükle
     */
    public function restore($id) {
        $this->checkPermission('pages.edit');
        
        $page = $this->pageModel->find($id);
        
        if (!$page || $page['status'] !== 'trash' || (isset($page['type']) && $page['type'] !== 'page')) {
            $_SESSION['error_message'] = 'Sayfa bulunamadı!';
            $this->redirect(admin_url('pages'));
            return;
        }
        
        $this->pageModel->update($id, ['status' => 'draft']);
        $_SESSION['page_message'] = 'Sayfa geri yüklendi!';
        
        $this->redirect(admin_url('pages'));
    }
    
    /**
     * Sayfa durumunu değiştir
     */
    public function toggleStatus($id) {
        $this->checkPermission('pages.edit');
        
        $page = $this->pageModel->find($id);
        
        if (!$page || (isset($page['type']) && $page['type'] !== 'page')) {
            $_SESSION['error_message'] = 'Sayfa bulunamadı!';
            $this->redirect(admin_url('pages'));
            return;
        }
        
        $newStatus = $page['status'] === 'published' ? 'draft' : 'published';
        
        $updateData = ['status' => $newStatus];
        
        // Yayınlanıyorsa tarih ekle
        if ($newStatus === 'published' && empty($page['published_at'])) {
            $updateData['published_at'] = date('Y-m-d H:i:s');
        }
        
        $this->pageModel->update($id, $updateData);
        
        $_SESSION['page_message'] = $newStatus === 'published' ? 'Sayfa yayınlandı!' : 'Sayfa taslak olarak kaydedildi!';
        $this->redirect(admin_url('pages'));
    }
    
    // ==================== VERSİYON İŞLEMLERİ ====================
    
    /**
     * Versiyon geçmişi sayfası
     */
    public function versions($id) {
        $this->checkPermission('pages.view');
        
        $page = $this->pageModel->findWithDetails($id);
        
        if (!$page || (isset($page['type']) && $page['type'] !== 'page')) {
            $_SESSION['error_message'] = 'Sayfa bulunamadı!';
            $this->redirect(admin_url('pages'));
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
        
        $this->view('admin/pages/versions', $data);
    }
    
    /**
     * Versiyon detayı (AJAX)
     */
    public function viewVersion($versionId) {
        $this->checkPermission('pages.view');
        
        header('Content-Type: application/json');
        
        require_once __DIR__ . '/../models/PostVersion.php';
        $versionModel = new PostVersion();
        $version = $versionModel->findWithDetails($versionId);
        
        if (!$version) {
            echo json_encode(['success' => false, 'message' => 'Versiyon bulunamadı!']);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'version' => $version
        ]);
    }
    
    /**
     * Eski versiyona geri dön
     */
    public function restoreVersion($versionId) {
        $this->checkPermission('pages.edit');
        
        require_once __DIR__ . '/../models/PostVersion.php';
        $versionModel = new PostVersion();
        $version = $versionModel->findWithDetails($versionId);
        
        if (!$version) {
            $_SESSION['error_message'] = 'Versiyon bulunamadı!';
            $this->redirect(admin_url('pages'));
            return;
        }
        
        $user = get_logged_in_user();
        $result = $this->pageModel->restoreVersion($versionId, $user['id']);
        
        if ($result) {
            $_SESSION['page_message'] = 'Versiyon ' . $version['version_number'] . ' başarıyla geri yüklendi!';
        } else {
            $_SESSION['error_message'] = 'Versiyon geri yüklenirken hata oluştu!';
        }
        
        $this->redirect(admin_url('pages/edit/' . $version['post_id']));
    }
    
    /**
     * Sayfayı kopyala (Duplicate)
     */
    public function duplicate($id) {
        $this->checkPermission('pages.create');
        
        $user = get_logged_in_user();
        
        // Mevcut sayfayı getir
        $page = $this->pageModel->findWithDetails($id);
        
        if (!$page || (isset($page['type']) && $page['type'] !== 'page')) {
            $_SESSION['error_message'] = 'Sayfa bulunamadı!';
            $this->redirect(admin_url('pages'));
            return;
        }
        
        // Özel alanları getir
        $customFields = $this->pageModel->getCustomFields($id);
        
        // Yeni sayfa verilerini hazırla
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
            'custom_fields' => $customFields // Özel alanlar da kopyalanacak
        ];
        
        // Yeni sayfayı oluştur
        $newPageId = $this->pageModel->createPage($newData);
        
        if ($newPageId) {
            $_SESSION['page_message'] = 'Sayfa başarıyla kopyalandı!';
            $this->redirect(admin_url('pages/edit/' . $newPageId));
        } else {
            $_SESSION['error_message'] = 'Sayfa kopyalanırken hata oluştu!';
            $this->redirect(admin_url('pages'));
        }
    }
    
    /**
     * Unique slug oluştur
     */
    private function generateUniqueSlug($baseSlug) {
        $slug = $baseSlug . '-kopya';
        $counter = 1;
        
        // Slug'ın unique olduğundan emin ol
        while ($this->pageModel->findBySlug($slug)) {
            $slug = $baseSlug . '-kopya-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}

