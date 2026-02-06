<?php
/**
 * Module Controller
 * Admin panelinde modül yönetimi için controller
 */

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/ModuleLoader.php';

class ModuleController extends Controller {
    
    private $db;
    private $moduleLoader;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->moduleLoader = ModuleLoader::getInstance();
    }
    
    /**
     * Modül listesi sayfası
     */
    public function index() {
        // Giriş kontrolü - yetki kontrolü geçici olarak devre dışı
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . admin_url('login'));
            exit;
        }
        
        // Modülleri tara ve listele
        $modules = $this->moduleLoader->scanModules();
        
        // Veritabanındaki kayıtları al
        $dbModules = [];
        try {
            $result = $this->db->fetchAll("SELECT * FROM modules ORDER BY menu_position ASC");
            foreach ($result as $row) {
                $dbModules[$row['slug']] = $row;
            }
        } catch (Exception $e) {
            // Tablo yoksa sessizce devam et
        }
        
        // Modülleri birleştir
        foreach ($modules as $name => &$module) {
            // Tema modülleri için özel işlem
            if (!empty($module['is_theme_module'])) {
                // Tema modülleri her zaman aktif sayılır
                $module['is_active'] = true;
                $module['installed'] = false; // Tema modülleri veritabanında değil
                if (isset($dbModules[$name])) {
                    $module['db'] = $dbModules[$name];
                }
            } elseif (isset($dbModules[$name])) {
                $module['db'] = $dbModules[$name];
                $module['is_active'] = (bool)$dbModules[$name]['is_active'];
                $module['is_system'] = (bool)$dbModules[$name]['is_system'];
                $module['installed'] = true;
            } else {
                $module['installed'] = false;
                $module['is_active'] = false;
                $module['is_system'] = false;
            }
        }
        
        // Kategorize et (tema modüllerini de ayır)
        $systemModules = array_filter($modules, fn($m) => $m['is_system'] ?? false);
        $themeModules = array_filter($modules, fn($m) => !empty($m['is_theme_module']));
        $installedModules = array_filter($modules, fn($m) => ($m['installed'] ?? false) && !($m['is_system'] ?? false) && empty($m['is_theme_module']));
        $availableModules = array_filter($modules, fn($m) => !($m['installed'] ?? false) && empty($m['is_theme_module']));
        
        $this->view('admin/modules/index', [
            'title' => 'Modül Yönetimi',
            'currentPage' => 'modules',
            'systemModules' => $systemModules,
            'themeModules' => $themeModules,
            'installedModules' => $installedModules,
            'availableModules' => $availableModules,
            'totalModules' => count($modules),
            'activeCount' => count(array_filter($modules, fn($m) => $m['is_active']))
        ]);
    }
    
    /**
     * Modülü aktif et
     */
    public function activate($name) {
        $this->requireLogin();
        
        $result = $this->moduleLoader->activateModule($name);
        
        if ($result['success']) {
            // Log kaydı
            $this->logAction($name, 'activated', 'Modül aktif edildi');
            
            $_SESSION['flash_message'] = $result['message'];
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = $result['message'];
            $_SESSION['flash_type'] = 'error';
        }
        
        $this->redirect(admin_url('modules'));
    }
    
    /**
     * Modülü kur ve aktif et (yüklü olmayan modüller için)
     */
    public function install($name) {
        $this->requireLogin();
        
        // Modül bilgilerini al
        $module = $this->moduleLoader->getModule($name);
        
        if (!$module) {
            $_SESSION['flash_message'] = 'Modül bulunamadı';
            $_SESSION['flash_type'] = 'error';
            $this->redirect(admin_url('modules'));
            return;
        }
        
        // Zaten kurulu mu kontrol et
        if ($module['installed'] ?? false) {
            $_SESSION['flash_message'] = 'Bu modül zaten kurulu';
            $_SESSION['flash_type'] = 'warning';
            $this->redirect(admin_url('modules'));
            return;
        }
        
        // Modülü kur (activate ile aynı işlemi yapar ama kurulum için)
        $result = $this->moduleLoader->activateModule($name);
        
        if ($result['success']) {
            // Log kaydı
            $this->logAction($name, 'installed', 'Modül kuruldu ve aktif edildi');
            
            $_SESSION['flash_message'] = 'Modül başarıyla kuruldu ve aktif edildi';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = $result['message'];
            $_SESSION['flash_type'] = 'error';
        }
        
        $this->redirect(admin_url('modules'));
    }
    
    /**
     * Modülü devre dışı bırak
     */
    public function deactivate($name) {
        $this->requireLogin();
        
        // Sistem modülü kontrolü
        $module = $this->moduleLoader->getModule($name);
        if ($module && ($module['is_system'] ?? false)) {
            $_SESSION['flash_message'] = 'Sistem modülleri devre dışı bırakılamaz';
            $_SESSION['flash_type'] = 'error';
            $this->redirect(admin_url('modules'));
            return;
        }
        
        $result = $this->moduleLoader->deactivateModule($name);
        
        if ($result['success']) {
            $this->logAction($name, 'deactivated', 'Modül devre dışı bırakıldı');
            
            $_SESSION['flash_message'] = $result['message'];
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = $result['message'];
            $_SESSION['flash_type'] = 'error';
        }
        
        $this->redirect(admin_url('modules'));
    }
    
    /**
     * Modülü sil
     */
    public function delete($name) {
        $this->requireLogin();
        
        // Sistem modülü kontrolü
        $module = $this->moduleLoader->getModule($name);
        if ($module && ($module['is_system'] ?? false)) {
            $_SESSION['flash_message'] = 'Sistem modülleri silinemez';
            $_SESSION['flash_type'] = 'error';
            $this->redirect(admin_url('modules'));
            return;
        }
        
        $result = $this->moduleLoader->deleteModule($name);
        
        if ($result['success']) {
            $this->logAction($name, 'deleted', 'Modül silindi');
            
            $_SESSION['flash_message'] = $result['message'];
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = $result['message'];
            $_SESSION['flash_type'] = 'error';
        }
        
        $this->redirect(admin_url('modules'));
    }
    
    /**
     * ZIP dosyasından modül yükle
     */
    public function upload() {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('modules'));
            return;
        }
        
        // Dosya yükleme kontrolü
        if (!isset($_FILES['module_zip']) || $_FILES['module_zip']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_message'] = 'Dosya yükleme hatası';
            $_SESSION['flash_type'] = 'error';
            $this->redirect(admin_url('modules'));
            return;
        }
        
        $file = $_FILES['module_zip'];
        
        // ZIP dosyası kontrolü
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedTypes = ['application/zip', 'application/x-zip-compressed', 'application/octet-stream'];
        if (!in_array($mimeType, $allowedTypes)) {
            $_SESSION['flash_message'] = 'Sadece ZIP dosyaları yüklenebilir';
            $_SESSION['flash_type'] = 'error';
            $this->redirect(admin_url('modules'));
            return;
        }
        
        // Modülü yükle
        $result = $this->moduleLoader->installFromZip($file['tmp_name']);
        
        if ($result['success']) {
            $this->logAction($result['module']['name'] ?? 'unknown', 'installed', 'Modül ZIP\'den yüklendi');
            
            $_SESSION['flash_message'] = $result['message'];
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = $result['message'];
            $_SESSION['flash_type'] = 'error';
        }
        
        $this->redirect(admin_url('modules'));
    }
    
    /**
     * Modül ayarları sayfası
     */
    public function settings($name) {
        $this->requireLogin();
        
        $module = $this->moduleLoader->getModule($name);
        
        if (!$module) {
            $_SESSION['flash_message'] = 'Modül bulunamadı';
            $_SESSION['flash_type'] = 'error';
            $this->redirect(admin_url('modules'));
            return;
        }
        
        // Modülün ayarlar sayfası var mı?
        if (!($module['settings'] ?? false)) {
            $_SESSION['flash_message'] = 'Bu modülün ayarlar sayfası yok';
            $_SESSION['flash_type'] = 'warning';
            $this->redirect(admin_url('modules'));
            return;
        }
        
        // Özel modülün (modules/) kendi admin sayfaları varsa oraya yönlendir; tema modülü değilse
        $isThemeModule = !empty($module['is_theme_module']);
        $adminSettingsView = $module['path'] . '/views/admin/settings.php';
        $adminIndexView = $module['path'] . '/views/admin/index.php';
        if (!$isThemeModule && file_exists($adminSettingsView)) {
            $this->redirect(admin_url('module/' . $name . '/settings'));
            return;
        }
        if (file_exists($adminIndexView)) {
            $this->redirect(admin_url('module/' . $name));
            return;
        }
        
        // POST işlemi
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $settings = $_POST['settings'] ?? [];
            
            // Ayarları kaydet
            $result = $this->moduleLoader->saveModuleSettings($name, $settings);
            
            if ($result['success']) {
                $_SESSION['flash_message'] = 'Ayarlar kaydedildi';
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'error';
            }
        }
        
        // Mevcut ayarları al
        $currentSettings = $this->moduleLoader->getModuleSettings($name);
        
        // Modülün özel ayar view'ı var mı kontrol et
        $settingsView = $module['path'] . '/views/settings.php';
        $hasCustomView = file_exists($settingsView);
        
        $this->view('admin/modules/settings', [
            'title' => $module['title'] . ' - Ayarlar',
            'currentPage' => 'modules',
            'module' => $module,
            'settings' => $currentSettings,
            'hasCustomView' => $hasCustomView,
            'customViewPath' => $settingsView
        ]);
    }
    
    /**
     * Modül detay sayfası (AJAX)
     */
    public function detail($name) {
        $this->requireLogin();
        
        $module = $this->moduleLoader->getModule($name);
        
        if (!$module) {
            $this->json(['success' => false, 'message' => 'Modül bulunamadı'], 404);
            return;
        }
        
        // Veritabanı bilgilerini ekle
        try {
            $dbInfo = $this->db->fetch("SELECT * FROM modules WHERE slug = ?", [$name]);
            $module['db'] = $dbInfo;
        } catch (Exception $e) {
            $module['db'] = null;
        }
        
        // Dosya listesini al
        $files = $this->getModuleFiles($module['path']);
        
        $this->json([
            'success' => true,
            'module' => $module,
            'files' => $files
        ]);
    }
    
    /**
     * Modül loglarını getir (AJAX)
     */
    public function logs($name = null) {
        $this->requireLogin();
        
        $limit = (int)($_GET['limit'] ?? 50);
        $offset = (int)($_GET['offset'] ?? 0);
        
        try {
            if ($name) {
                $module = $this->db->fetch("SELECT id FROM modules WHERE slug = ?", [$name]);
                if (!$module) {
                    $this->json(['success' => false, 'message' => 'Modül bulunamadı'], 404);
                    return;
                }
                
                $logs = $this->db->fetchAll(
                    "SELECT ml.*, m.label as module_label 
                     FROM module_logs ml 
                     LEFT JOIN modules m ON ml.module_id = m.id 
                     WHERE ml.module_id = ? 
                     ORDER BY ml.created_at DESC 
                     LIMIT ? OFFSET ?",
                    [$module['id'], $limit, $offset]
                );
            } else {
                $logs = $this->db->fetchAll(
                    "SELECT ml.*, m.label as module_label 
                     FROM module_logs ml 
                     LEFT JOIN modules m ON ml.module_id = m.id 
                     ORDER BY ml.created_at DESC 
                     LIMIT ? OFFSET ?",
                    [$limit, $offset]
                );
            }
            
            $this->json(['success' => true, 'logs' => $logs]);
            
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    // ==================== HELPER METHODS ====================
    
    /**
     * Yetki kontrolü
     */
    private function requireLogin() {
        // Giriş yapılmamışsa login'e yönlendir
        if (!isset($_SESSION['user_id'])) {
            $this->redirect(admin_url('login'));
            exit;
        }
        return true;
    }
    
    private function requirePermission($permission) {
        $this->requireLogin();
        
        // Admin veya super_admin her zaman erişebilir
        if (isset($_SESSION['role_slug']) && in_array($_SESSION['role_slug'], ['super_admin', 'admin'])) {
            return true;
        }
        
        // Role ID ile kontrol (1 = super_admin, 2 = admin varsayılan)
        if (isset($_SESSION['role_id']) && in_array($_SESSION['role_id'], [1, 2])) {
            return true;
        }
        
        // Detaylı yetki kontrolü
        if (function_exists('check_permission') && check_permission($permission)) {
            return true;
        }
        
        $_SESSION['flash_message'] = 'Bu işlem için yetkiniz yok';
        $_SESSION['flash_type'] = 'error';
        $this->redirect(admin_url('dashboard'));
        exit;
    }
    
    /**
     * Log kaydı oluştur
     */
    private function logAction($moduleName, $action, $message, $data = null) {
        try {
            $moduleId = null;
            $moduleResult = $this->db->fetch("SELECT id FROM modules WHERE slug = ?", [$moduleName]);
            if ($moduleResult) {
                $moduleId = $moduleResult['id'];
            }
            
            $this->db->query(
                "INSERT INTO module_logs (module_id, action, message, data, user_id, ip_address, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, NOW())",
                [
                    $moduleId,
                    $action,
                    $message,
                    $data ? json_encode($data) : null,
                    $_SESSION['user_id'] ?? null,
                    $_SERVER['REMOTE_ADDR'] ?? null
                ]
            );
        } catch (Exception $e) {
            error_log("Module log error: " . $e->getMessage());
        }
    }
    
    /**
     * Modül dosyalarını listele
     */
    private function getModuleFiles($path, $prefix = '') {
        $files = [];
        
        if (!is_dir($path)) {
            return $files;
        }
        
        $items = scandir($path);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $fullPath = $path . '/' . $item;
            $relativePath = $prefix ? $prefix . '/' . $item : $item;
            
            if (is_dir($fullPath)) {
                $files[] = [
                    'name' => $item,
                    'path' => $relativePath,
                    'type' => 'directory',
                    'children' => $this->getModuleFiles($fullPath, $relativePath)
                ];
            } else {
                $files[] = [
                    'name' => $item,
                    'path' => $relativePath,
                    'type' => 'file',
                    'size' => filesize($fullPath),
                    'extension' => pathinfo($item, PATHINFO_EXTENSION)
                ];
            }
        }
        
        return $files;
    }
}

