<?php
/**
 * Theme Controller
 * Admin panelinde tema yönetimi için controller
 */

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/ThemeManager.php';
require_once __DIR__ . '/../../core/ThemeLoader.php';
require_once __DIR__ . '/../../core/ViewRenderer.php';

class ThemeController extends Controller {
    
    private $db;
    private $themeManager;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->themeManager = ThemeManager::getInstance();
    }
    
    /**
     * Tema listesi sayfası
     */
    public function index() {
        $this->requireLogin();
        
        $message = $_SESSION['theme_message'] ?? null;
        $messageType = $_SESSION['theme_message_type'] ?? 'info';
        unset($_SESSION['theme_message'], $_SESSION['theme_message_type']);
        
        $themes = $this->themeManager->getInstalledThemes();
        $activeTheme = $this->themeManager->getActiveTheme();
        
        $this->view('admin/themes/index', [
            'title' => 'Temalar',
            'currentPage' => 'themes',
            'themes' => $themes,
            'activeTheme' => $activeTheme,
            'message' => $message,
            'messageType' => $messageType
        ]);
    }
    
    /**
     * Tema özelleştirici sayfası
     */
    public function customize($slug = null) {
        $this->requireLogin();
        
        // Slug verilmemişse aktif temayı kullan
        if (!$slug) {
            $activeTheme = $this->themeManager->getActiveTheme();
            if ($activeTheme) {
                $slug = $activeTheme['slug'];
            } else {
                $_SESSION['theme_message'] = 'Aktif tema bulunamadı.';
                $_SESSION['theme_message_type'] = 'error';
                header("Location: " . admin_url('themes'));
                exit;
            }
        }
        
        $theme = $this->themeManager->getTheme($slug);
        if (!$theme) {
            $_SESSION['theme_message'] = 'Tema bulunamadı.';
            $_SESSION['theme_message_type'] = 'error';
            header("Location: " . admin_url('themes'));
            exit;
        }
        
        $settings = $this->themeManager->getThemeSettings($slug);
        // Sections hem settings_schema içinde hem de ayrı key olarak olabilir
        $sections = $theme['settings_schema']['sections'] ?? $theme['sections'] ?? [];
        $customCss = $this->themeManager->getCustomCode('css');
        $customJs = $this->themeManager->getCustomCode('js');
        $fonts = $this->themeManager->getAvailableFonts();
        
        // Debug için
        error_log('Theme customize - Theme: ' . print_r($theme, true));
        error_log('Theme customize - Settings: ' . print_r($settings, true));
        error_log('Theme customize - Sections: ' . print_r($sections, true));
        
        $this->view('admin/themes/customize', [
            'title' => 'Tema Özelleştirici - ' . $theme['name'],
            'currentPage' => 'themes',
            'theme' => $theme,
            'settings' => $settings,
            'sections' => $sections,
            'customCss' => $customCss,
            'customJs' => $customJs,
            'availableFonts' => $fonts,
            'themeManager' => $this->themeManager
        ]);
    }
    
    /**
     * Tema ayarlarını kaydet (AJAX)
     */
    public function saveSettings() {
        $this->requireLogin();
        header('Content-Type: application/json');
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                $data = $_POST;
            }
            
            $slug = $data['theme_slug'] ?? null;
            $settings = $data['settings'] ?? [];
            
            // Debug: Gelen footer_bottom_links'i logla
            if (isset($settings['custom']['footer_bottom_links'])) {
                error_log("ThemeController - footer_bottom_links received: " . json_encode($settings['custom']['footer_bottom_links']));
            } else {
                error_log("ThemeController - footer_bottom_links NOT in settings. Custom keys: " . json_encode(array_keys($settings['custom'] ?? [])));
            }
            
            if (!$slug) {
                throw new Exception('Tema slug gerekli');
            }
            
            // About page verilerini ayır ve kaydet
            $aboutPageData = $settings['about_page'] ?? null;
            if ($aboutPageData) {
                unset($settings['about_page']);
                $this->saveAboutPageData($aboutPageData);
            }
            
            $result = $this->themeManager->saveThemeSettings($settings, $slug);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Ayarlar kaydedildi']);
            } else {
                throw new Exception('Ayarlar kaydedilemedi');
            }
        } catch (Exception $e) {
            http_response_code(400);
            error_log("ThemeController saveSettings error: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Hakkımızda sayfası verilerini kaydet
     */
    private function saveAboutPageData($data) {
        require_once __DIR__ . '/../models/Page.php';
        $pageModel = new Page();
        
        // Hakkımızda sayfasını bul
        $aboutPage = $pageModel->findBySlug('about');
        if (!$aboutPage) {
            $aboutPage = $pageModel->findBySlug('hakkimizda');
        }
        
        if (!$aboutPage) {
            error_log('About page not found');
            return false;
        }
        
        // Custom fields'ları hazırla
        $customFields = [];
        
        // Hero subtitle
        if (isset($data['hero_subtitle'])) {
            $customFields['hero_subtitle'] = $data['hero_subtitle'];
        }
        
        // About sections
        if (isset($data['about_sections']) && is_array($data['about_sections'])) {
            $customFields['about_sections'] = json_encode($data['about_sections']);
        }
        
        // Core values (service_features olarak kaydet)
        if (isset($data['core_values']) && is_array($data['core_values'])) {
            $customFields['service_features'] = json_encode($data['core_values']);
        }
        
        // Team members
        if (isset($data['team_members']) && is_array($data['team_members'])) {
            $customFields['team_members'] = json_encode($data['team_members']);
        }
        
        // Stats
        if (isset($data['stats'])) {
            if (is_array($data['stats']) && !empty($data['stats'])) {
                $customFields['stats'] = json_encode($data['stats']);
            } elseif (is_array($data['stats']) && empty($data['stats'])) {
                // Boş array ise de kaydet (temizleme için)
                $customFields['stats'] = json_encode([]);
            }
        }
        
        // CTA
        if (isset($data['cta_title'])) {
            $customFields['cta_title'] = $data['cta_title'];
        }
        if (isset($data['cta_description'])) {
            $customFields['cta_description'] = $data['cta_description'];
        }
        if (isset($data['cta_button_text'])) {
            $customFields['cta_button_text'] = $data['cta_button_text'];
        }
        if (isset($data['cta_button_link'])) {
            $customFields['cta_button_link'] = $data['cta_button_link'];
        }
        if (isset($data['cta_button2_text'])) {
            $customFields['cta_button2_text'] = $data['cta_button2_text'];
        }
        if (isset($data['cta_button2_link'])) {
            $customFields['cta_button2_link'] = $data['cta_button2_link'];
        }
        
        // Custom fields'ları kaydet
        if (!empty($customFields)) {
            $pageModel->saveCustomFields($aboutPage['id'], $customFields);
        }
        
        return true;
    }
    
    /**
     * Özel CSS/JS kaydet (AJAX)
     */
    public function saveCustomCode() {
        $this->requireLogin();
        header('Content-Type: application/json');
        
        try {
            $type = $_POST['type'] ?? 'css';
            $content = $_POST['content'] ?? '';
            
            if (!in_array($type, ['css', 'js', 'head', 'footer'])) {
                throw new Exception('Geçersiz kod türü');
            }
            
            $result = $this->themeManager->saveCustomCode($type, $content);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => ucfirst($type) . ' kodu kaydedildi']);
            } else {
                throw new Exception('Kod kaydedilemedi');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Temayı aktifleştir
     */
    public function activate($slug) {
        $this->requireLogin();
        
        try {
            $result = $this->themeManager->activateTheme($slug);
            
            if ($result) {
                $_SESSION['theme_message'] = 'Tema başarıyla aktifleştirildi.';
                $_SESSION['theme_message_type'] = 'success';
            } else {
                $_SESSION['theme_message'] = 'Tema aktifleştirilemedi.';
                $_SESSION['theme_message_type'] = 'error';
            }
        } catch (Exception $e) {
            $_SESSION['theme_message'] = 'Hata: ' . $e->getMessage();
            $_SESSION['theme_message_type'] = 'error';
        }
        
        header("Location: " . admin_url('themes'));
        exit;
    }
    
    /**
     * Tema kur (klasörden)
     */
    public function install($slug) {
        $this->requireLogin();
        
        try {
            $result = $this->themeManager->installThemeFromDirectory($slug);
            
            if ($result) {
                $_SESSION['theme_message'] = 'Tema başarıyla kuruldu.';
                $_SESSION['theme_message_type'] = 'success';
            } else {
                $_SESSION['theme_message'] = 'Tema kurulamadı.';
                $_SESSION['theme_message_type'] = 'error';
            }
        } catch (Exception $e) {
            $_SESSION['theme_message'] = 'Hata: ' . $e->getMessage();
            $_SESSION['theme_message_type'] = 'error';
        }
        
        header("Location: " . admin_url('themes'));
        exit;
    }
    
    /**
     * ZIP'ten tema yükle
     */
    public function upload() {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . admin_url('themes'));
            exit;
        }
        
        try {
            if (!isset($_FILES['theme_zip']) || $_FILES['theme_zip']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Dosya yükleme hatası');
            }
            
            $file = $_FILES['theme_zip'];
            
            // Dosya türü kontrolü
            $allowedTypes = ['application/zip', 'application/x-zip-compressed'];
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('Sadece ZIP dosyası yüklenebilir');
            }
            
            // Boyut kontrolü (max 50MB)
            if ($file['size'] > 50 * 1024 * 1024) {
                throw new Exception('Dosya boyutu 50MB\'dan büyük olamaz');
            }
            
            $result = $this->themeManager->installThemeFromZip($file['tmp_name']);
            
            if ($result['success']) {
                $slug = $result['slug'] ?? null;
                if ($slug) {
                    $_SESSION['theme_message'] = $result['message'] . ' <a href="' . admin_url('themes/activate/' . $slug) . '" class="underline font-semibold">Şimdi Aktifleştir</a>';
                } else {
                    $_SESSION['theme_message'] = $result['message'];
                }
                $_SESSION['theme_message_type'] = 'success';
            } else {
                throw new Exception($result['message']);
            }
        } catch (Exception $e) {
            $_SESSION['theme_message'] = 'Hata: ' . $e->getMessage();
            $_SESSION['theme_message_type'] = 'error';
        }
        
        header("Location: " . admin_url('themes'));
        exit;
    }
    
    /**
     * Tema kaldır
     */
    public function uninstall($slug) {
        $this->requireLogin();
        
        try {
            $result = $this->themeManager->uninstallTheme($slug);
            
            if ($result) {
                $_SESSION['theme_message'] = 'Tema başarıyla kaldırıldı.';
                $_SESSION['theme_message_type'] = 'success';
            } else {
                $_SESSION['theme_message'] = 'Tema kaldırılamadı.';
                $_SESSION['theme_message_type'] = 'error';
            }
        } catch (Exception $e) {
            $_SESSION['theme_message'] = 'Hata: ' . $e->getMessage();
            $_SESSION['theme_message_type'] = 'error';
        }
        
        header("Location: " . admin_url('themes'));
        exit;
    }
    
    /**
     * Temayı ZIP dosyası olarak indir
     */
    public function download($slug) {
        $this->requireLogin();
        
        try {
            // Temayı export et
            $result = $this->themeManager->exportThemeAsZip($slug);
            
            if (!$result['success']) {
                $_SESSION['theme_message'] = $result['message'];
                $_SESSION['theme_message_type'] = 'error';
                header("Location: " . admin_url('themes'));
                exit;
            }
            
            $zipFile = $result['file'];
            $filename = $result['filename'];
            
            // ZIP dosyasının var olduğundan emin ol
            if (!file_exists($zipFile)) {
                $_SESSION['theme_message'] = 'Export dosyası oluşturulamadı.';
                $_SESSION['theme_message_type'] = 'error';
                header("Location: " . admin_url('themes'));
                exit;
            }
            
            // Download header'larını ayarla
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($zipFile));
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: public');
            
            // Dosyayı gönder
            readfile($zipFile);
            
            // Geçici dosyayı sil
            @unlink($zipFile);
            
            exit;
            
        } catch (Exception $e) {
            $_SESSION['theme_message'] = 'Hata: ' . $e->getMessage();
            $_SESSION['theme_message_type'] = 'error';
            header("Location: " . admin_url('themes'));
            exit;
        }
    }
    
    // ==========================================
    // SECTION YÖNETİMİ
    // ==========================================
    
    /**
     * Section kaydet (AJAX)
     */
    public function saveSection() {
        $this->requireLogin();
        
        // Önceki output'u temizle
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        
        try {
            $data = [
                'id' => !empty($_POST['id']) ? (int)$_POST['id'] : null,
                'page_type' => $_POST['page_type'] ?? 'home',
                'section_id' => $_POST['section_id'] ?? '',
                'section_component' => $_POST['section_component'] ?? null,
                'title' => $_POST['title'] ?? '',
                'subtitle' => $_POST['subtitle'] ?? '',
                'content' => $_POST['content'] ?? '',
                'settings' => json_decode($_POST['settings'] ?? '{}', true),
                'items' => json_decode($_POST['items'] ?? '[]', true),
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
                'is_active' => isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1
            ];
            
            if (empty($data['section_id'])) {
                throw new Exception('Section ID gerekli');
            }
            
            $sectionId = $this->themeManager->saveSection($data);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Section kaydedildi',
                'section_id' => $sectionId
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Section sil (AJAX)
     */
    public function deleteSection($id) {
        $this->requireLogin();
        header('Content-Type: application/json');
        
        try {
            $result = $this->themeManager->deleteSection((int)$id);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Section silindi']);
            } else {
                throw new Exception('Section silinemedi');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Section sıralamasını güncelle (AJAX)
     */
    public function updateSectionOrder() {
        $this->requireLogin();
        header('Content-Type: application/json');
        
        try {
            $pageType = $_POST['page_type'] ?? 'home';
            $order = json_decode($_POST['order'] ?? '[]', true);
            
            if (empty($order)) {
                throw new Exception('Sıralama verisi gerekli');
            }
            
            $result = $this->themeManager->updateSectionOrder($pageType, $order);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Sıralama güncellendi']);
            } else {
                throw new Exception('Sıralama güncellenemedi');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Sayfa section'larını getir (AJAX)
     */
    public function getSections($pageType = 'home') {
        $this->requireLogin();
        header('Content-Type: application/json');
        
        try {
            $sections = $this->themeManager->getPageSections($pageType);
            echo json_encode(['success' => true, 'sections' => $sections]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Tek section verisini getir (AJAX)
     */
    public function getSectionData() {
        $this->requireLogin();
        
        // Önceki output'u temizle
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        
        try {
            $pageType = $_GET['page_type'] ?? 'home';
            $sectionId = $_GET['section_id'] ?? '';
            
            if (empty($sectionId)) {
                throw new Exception('Section ID gerekli');
            }
            
            $section = $this->themeManager->getSection($pageType, $sectionId);
            
            echo json_encode([
                'success' => true, 
                'section' => $section
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    // ==========================================
    // ÖNİZLEME
    // ==========================================
    
    /**
     * Tema önizleme
     */
    public function preview($slug = null) {
        // Debug: Hata raporlamayı aç
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        // Slug verilmemişse aktif temayı kullan
        if (!$slug) {
            $activeTheme = $this->themeManager->getActiveTheme();
            $slug = $activeTheme ? $activeTheme['slug'] : null;
        }
        
        if (!$slug) {
            die('Tema bulunamadı');
        }
        
        // Tema var mı kontrol et
        $theme = $this->themeManager->getTheme($slug);
        if (!$theme) {
            die('Tema bulunamadı: ' . htmlspecialchars($slug));
        }
        
        // Önizleme ayarlarını al
        $previewSettings = [];
        $previewSections = [];
        if (isset($_GET['settings'])) {
            // URL decode edilmiş base64 string'i decode et
            $settingsBase64 = urldecode($_GET['settings']);
            $decodedJson = base64_decode($settingsBase64, true);
            
            if ($decodedJson !== false) {
                $decodedSettings = json_decode($decodedJson, true) ?? [];
                
                // Pending sections'ı ayır
                if (isset($decodedSettings['pending_sections'])) {
                    $previewSections = $decodedSettings['pending_sections'];
                    unset($decodedSettings['pending_sections']);
                }
                
                $previewSettings = $decodedSettings;
            }
        }
        
        // ThemeLoader'ı önizleme modunda başlat
        $themeLoader = ThemeLoader::getInstance();
        $themeLoader->loadTheme($slug, $previewSettings);
        
        // Önizleme sections'larını ThemeLoader'a aktar
        if (!empty($previewSections)) {
            $themeLoader->setPreviewSections($previewSections);
        }
        
        // Önizleme sayfası (preview_page parametresinden al, varsayılan home)
        $previewPage = $_GET['preview_page'] ?? 'home';
        
        // Tema dizininde sayfa dosyası var mı kontrol et
        $themePath = $themeLoader->getThemePath();
        $pageFile = $themePath . '/' . $previewPage . '.php';
        
        if (!file_exists($pageFile)) {
            // Varsayılan home sayfasını dene
            $pageFile = $themePath . '/home.php';
            if (!file_exists($pageFile)) {
                die('Tema sayfası bulunamadı: ' . htmlspecialchars($previewPage));
            }
            $previewPage = 'home';
        }
        
        // Tema layout'unu ve sayfayı doğrudan render et
        $layoutFile = $themePath . '/layouts/default.php';
        
        if (!file_exists($layoutFile)) {
            die('Tema layout dosyası bulunamadı');
        }
        
        // Sayfa içeriğini yakala
        ob_start();
        
        // Sayfa değişkenlerini hazırla
        $title = $theme['name'] . ' - Önizleme';
        $current_page = $previewPage;
        $is_preview = true;
        
        // ThemeLoader'ı sayfa dosyasına geçir (home.php, about.php vb. için gerekli)
        // $themeLoader zaten satır 531'de tanımlandı
        
        // Sayfa dosyasını include et
        include $pageFile;
        $content = ob_get_clean();
        
        // Layout'u render et
        $sections = ['content' => $content];
        include $layoutFile;
    }
    
    /**
     * Giriş kontrolü
     */
    private function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            // AJAX isteği mi kontrol et
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Oturum süresi dolmuş. Lütfen sayfayı yenileyin.']);
                exit;
            }
            header("Location: " . admin_url('login'));
            exit;
        }
    }
    
    /**
     * AJAX isteği kontrolü
     */
    private function isAjaxRequest(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' ||
               strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false ||
               strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false ||
               strpos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') !== false;
    }
}

