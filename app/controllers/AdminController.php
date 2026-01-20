<?php
/**
 * Admin Controller
 * Admin paneli controller'ı
 */

class AdminController extends Controller {
    
    /**
     * Login sayfası
     */
    public function login() {
        // Eğer zaten giriş yapılmışsa dashboard'a yönlendir
        if (is_user_logged_in()) {
            $this->redirect(admin_url('dashboard'));
        }
        
        $error = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if ($username && $password) {
                $db = Database::getInstance();
                $user = $db->fetch(
                    "SELECT * FROM users WHERE username = ? OR email = ?",
                    [$username, $username]
                );
                
                if ($user && password_verify($password, $user['password'])) {
                    // Session başlat
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'] ?? 'user';
                    $_SESSION['role_slug'] = strtolower(trim($user['role'] ?? 'user'));
                    
                    $this->redirect(admin_url('dashboard'));
                } else {
                    $error = 'Kullanıcı adı veya şifre hatalı!';
                }
            } else {
                $error = 'Lütfen tüm alanları doldurun!';
            }
        }
        
        $data = [
            'title' => 'Admin Giriş',
            'error' => $error
        ];
        
        $this->view('admin/login', $data);
    }
    
    /**
     * Logout işlemi
     */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        session_destroy();
        $this->redirect(admin_url('login'));
    }
    
    /**
     * Admin dashboard
     */
    public function dashboard() {
        // Giriş kontrolü
        if (!is_user_logged_in()) {
            $this->redirect(admin_url('login'));
        }
        
        $db = Database::getInstance();
        
        // İstatistikler çek
        $stats = [
            'posts_count' => $db->fetch("SELECT COUNT(*) as count FROM posts WHERE type = 'post' AND status != 'trash'")['count'] ?? 0,
            'pages_count' => $db->fetch("SELECT COUNT(*) as count FROM posts WHERE type = 'page' AND status != 'trash'")['count'] ?? 0,
            'media_count' => $db->fetch("SELECT COUNT(*) as count FROM media")['count'] ?? 0,
            'users_count' => $db->fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0,
            'published_posts' => $db->fetch("SELECT COUNT(*) as count FROM posts WHERE type = 'post' AND status = 'published'")['count'] ?? 0,
            'draft_posts' => $db->fetch("SELECT COUNT(*) as count FROM posts WHERE type = 'post' AND status = 'draft'")['count'] ?? 0,
        ];
        
        // Form istatistikleri
        try {
            $stats['forms_count'] = $db->fetch("SELECT COUNT(*) as count FROM forms WHERE status = 'active'")['count'] ?? 0;
            $stats['new_submissions_count'] = $db->fetch("SELECT COUNT(*) as count FROM form_submissions WHERE status = 'new'")['count'] ?? 0;
            $stats['total_submissions_count'] = $db->fetch("SELECT COUNT(*) as count FROM form_submissions")['count'] ?? 0;
        } catch (Exception $e) {
            // Form tabloları yoksa
            $stats['forms_count'] = 0;
            $stats['new_submissions_count'] = 0;
            $stats['total_submissions_count'] = 0;
        }
        
        // Analytics verileri
        $analyticsStats = null;
        try {
            require_once __DIR__ . '/../models/Analytics.php';
            $analytics = new Analytics();
            $analyticsStats = $analytics->getDashboardStats();
        } catch (Exception $e) {
            // Analytics tablosu yoksa veya hata varsa
            $analyticsStats = null;
        }
        
        // Son içerikler
        $recentPosts = $db->fetchAll("
            SELECT p.*, u.username as author_name 
            FROM posts p 
            LEFT JOIN users u ON p.author_id = u.id 
            WHERE p.type = 'post' AND p.status != 'trash'
            ORDER BY p.created_at DESC 
            LIMIT 5
        ");
        
        // Son medya
        $recentMedia = $db->fetchAll("
            SELECT * FROM media 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        
        // Son form gönderimleri
        $recentSubmissions = [];
        try {
            $recentSubmissions = $db->fetchAll("
                SELECT s.*, f.name as form_name, f.id as form_id
                FROM form_submissions s 
                LEFT JOIN forms f ON s.form_id = f.id 
                ORDER BY s.created_at DESC 
                LIMIT 5
            ");
            
            // JSON verisini decode et
            foreach ($recentSubmissions as &$submission) {
                if (!empty($submission['data'])) {
                    $decoded = json_decode($submission['data'], true);
                    $submission['data'] = $decoded !== null ? $decoded : [];
                } else {
                    $submission['data'] = [];
                }
            }
        } catch (Exception $e) {
            // Form tabloları yoksa
            $recentSubmissions = [];
        }
        
        // Aktif tema
        $activeTheme = null;
        try {
            $activeTheme = $db->fetch("SELECT * FROM themes WHERE is_active = 1");
        } catch (Exception $e) {
            // Tema tablosu yoksa
        }
        
        $data = [
            'title' => 'Dashboard',
            'user' => get_logged_in_user(),
            'stats' => $stats,
            'analyticsStats' => $analyticsStats,
            'recentPosts' => $recentPosts,
            'recentMedia' => $recentMedia,
            'recentSubmissions' => $recentSubmissions,
            'activeTheme' => $activeTheme,
            'message' => $_SESSION['error_message'] ?? null
        ];
        
        unset($_SESSION['error_message']);
        
        $this->view('admin/dashboard', $data);
    }
    
    /**
     * Logo yükleme endpoint'i
     */
    public function upload_logo() {
        // Giriş kontrolü
        if (!is_user_logged_in()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['logo'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Dosya bulunamadı']);
            exit;
        }
        
        $file = $_FILES['logo'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Dosya tipi kontrolü
        if (!in_array($file['type'], $allowedTypes)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Geçersiz dosya tipi. Sadece JPG, PNG, GIF, SVG veya WebP yükleyebilirsiniz.']);
            exit;
        }
        
        // Dosya boyutu kontrolü
        if ($file['size'] > $maxSize) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Dosya boyutu çok büyük. Maksimum 5MB olabilir.']);
            exit;
        }
        
        // Upload klasörünü oluştur
        $uploadDir = __DIR__ . '/../../public/uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Dosya adını oluştur
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Dosyayı yükle
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $fileUrl = site_url('uploads/' . $filename);
            
            // Veritabanına kaydet
            update_option('site_logo', $fileUrl);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => 'Logo başarıyla yüklendi.',
                'url' => $fileUrl
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Dosya yüklenirken bir hata oluştu.']);
        }
        exit;
    }
    
    /**
     * Favicon yükleme endpoint'i
     */
    public function upload_favicon() {
        // Giriş kontrolü
        if (!is_user_logged_in()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['favicon'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Dosya bulunamadı']);
            exit;
        }
        
        $file = $_FILES['favicon'];
        $allowedTypes = ['image/x-icon', 'image/vnd.microsoft.icon', 'image/png', 'image/svg+xml', 'image/gif'];
        $maxSize = 1 * 1024 * 1024; // 1MB (favicon için daha küçük)
        
        // Dosya tipi kontrolü
        if (!in_array($file['type'], $allowedTypes)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Geçersiz dosya tipi. Sadece ICO, PNG, SVG veya GIF yükleyebilirsiniz.']);
            exit;
        }
        
        // Dosya boyutu kontrolü
        if ($file['size'] > $maxSize) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Dosya boyutu çok büyük. Maksimum 1MB olabilir.']);
            exit;
        }
        
        // Upload klasörünü oluştur
        $uploadDir = __DIR__ . '/../../public/uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Dosya adını oluştur
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'favicon_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Dosyayı yükle
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $fileUrl = site_url('uploads/' . $filename);
            
            // Veritabanına kaydet
            update_option('site_favicon', $fileUrl);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => 'Favicon başarıyla yüklendi.',
                'url' => $fileUrl
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Dosya yüklenirken bir hata oluştu.']);
        }
        exit;
    }
    
    /**
     * Ayarlar sayfası
     */
    public function settings() {
        // Giriş kontrolü
        if (!is_user_logged_in()) {
            $this->redirect(admin_url('login'));
        }
        
        // Yetki kontrolü
        if (!current_user_can('settings.edit')) {
            $_SESSION['error_message'] = 'Bu modülde yetkiniz yoktur!';
            $this->redirect(admin_url('dashboard'));
        }
        
        $message = null;
        $messageType = null;
        
        // POST isteği geldiğinde ayarları kaydet
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Genel Ayarlar
            if (isset($_POST['save_general'])) {
                // Logo ve favicon URL'lerini kaydet (İçerik Kütüphanesi'nden seçilen)
                if (isset($_POST['site_logo_url'])) {
                    update_option('site_logo', $_POST['site_logo_url']);
                }
                if (isset($_POST['site_favicon_url'])) {
                    update_option('site_favicon', $_POST['site_favicon_url']);
                }
                update_option('google_analytics', $_POST['google_analytics'] ?? '');
                update_option('google_tag_manager', $_POST['google_tag_manager'] ?? '');
                update_option('google_ads', $_POST['google_ads'] ?? '');
                $message = 'Genel ayarlar başarıyla kaydedildi.';
                $messageType = 'success';
            }
            
            // SEO Ayarları
            if (isset($_POST['save_seo'])) {
                update_option('seo_title', $_POST['seo_title'] ?? '');
                update_option('seo_description', $_POST['seo_description'] ?? '');
                update_option('seo_author', $_POST['seo_author'] ?? '');
                $message = 'SEO ayarları başarıyla kaydedildi.';
                $messageType = 'success';
            }
            
            // Sosyal Medya Ayarları
            if (isset($_POST['save_social'])) {
                update_option('social_facebook', $_POST['social_facebook'] ?? '');
                update_option('social_instagram', $_POST['social_instagram'] ?? '');
                update_option('social_twitter', $_POST['social_twitter'] ?? '');
                update_option('social_linkedin', $_POST['social_linkedin'] ?? '');
                update_option('social_youtube', $_POST['social_youtube'] ?? '');
                update_option('social_tiktok', $_POST['social_tiktok'] ?? '');
                update_option('social_pinterest', $_POST['social_pinterest'] ?? '');
                $message = 'Sosyal medya ayarları başarıyla kaydedildi.';
                $messageType = 'success';
            }
            
            // Şirket Bilgileri
            if (isset($_POST['save_company'])) {
                update_option('company_name', $_POST['company_name'] ?? '');
                update_option('company_tax_number', $_POST['company_tax_number'] ?? '');
                update_option('company_email', $_POST['company_email'] ?? '');
                update_option('company_phone', $_POST['company_phone'] ?? '');
                update_option('company_address', $_POST['company_address'] ?? '');
                update_option('company_city', $_POST['company_city'] ?? '');
                update_option('company_kep', $_POST['company_kep'] ?? '');
                $message = 'Şirket bilgileri başarıyla kaydedildi.';
                $messageType = 'success';
            }
            
            // Spam Koruma Ayarları (Honeypot)
            if (isset($_POST['save_spam_protection'])) {
                update_option('honeypot_enabled', isset($_POST['honeypot_enabled']) ? 1 : 0);
                $message = 'Spam koruma ayarları başarıyla kaydedildi.';
                $messageType = 'success';
            }
            
            // AI Ayarları (Groq API)
            if (isset($_POST['save_ai_settings'])) {
                update_option('groq_api_key', trim($_POST['groq_api_key'] ?? ''));
                $message = 'AI ayarları başarıyla kaydedildi.';
                $messageType = 'success';
            }
        }
        
        // Mevcut ayarları getir
        $data = [
            'title' => 'Ayarlar',
            'user' => get_logged_in_user(),
            'message' => $message,
            'messageType' => $messageType,
            'settings' => [
                'site_logo' => get_option('site_logo', ''),
                'site_favicon' => get_option('site_favicon', ''),
                'google_analytics' => get_option('google_analytics', ''),
                'google_tag_manager' => get_option('google_tag_manager', ''),
                'google_ads' => get_option('google_ads', ''),
                'seo_title' => get_option('seo_title', ''),
                'seo_description' => get_option('seo_description', ''),
                'seo_author' => get_option('seo_author', ''),
                'social_facebook' => get_option('social_facebook', ''),
                'social_instagram' => get_option('social_instagram', ''),
                'social_twitter' => get_option('social_twitter', ''),
                'social_linkedin' => get_option('social_linkedin', ''),
                'social_youtube' => get_option('social_youtube', ''),
                'social_tiktok' => get_option('social_tiktok', ''),
                'social_pinterest' => get_option('social_pinterest', ''),
                // Şirket Bilgileri
                'company_name' => get_option('company_name', ''),
                'company_tax_number' => get_option('company_tax_number', ''),
                'company_email' => get_option('company_email', ''),
                'company_phone' => get_option('company_phone', ''),
                'company_address' => get_option('company_address', ''),
                'company_city' => get_option('company_city', ''),
                'company_kep' => get_option('company_kep', ''),
                // Honeypot Ayarları
                'honeypot_enabled' => get_option('honeypot_enabled', 1),
                // AI Ayarları
                'groq_api_key' => get_option('groq_api_key', ''),
            ]
        ];
        
        $this->view('admin/settings', $data);
    }
    
    /**
     * Tasarım Düzenleme - Tema kod editörü
     */
    public function design() {
        // Giriş kontrolü
        if (!is_user_logged_in()) {
            $this->redirect(admin_url('login'));
        }
        
        // Yetki kontrolü
        if (!current_user_can('themes.edit_code')) {
            $_SESSION['error_message'] = 'Bu modülde yetkiniz yoktur!';
            $this->redirect(admin_url('dashboard'));
        }
        
        // ThemeManager'ı yükle
        require_once __DIR__ . '/../../core/ThemeManager.php';
        $themeManager = ThemeManager::getInstance();
        
        $message = null;
        $messageType = null;
        $activeTab = $_REQUEST['tab'] ?? 'files';
        $selectedFile = $_GET['file'] ?? '';
        $fileContent = '';
        
        // Aktif temayı al
        $activeTheme = $themeManager->getActiveTheme();
        $themeFiles = [];
        $customCss = '';
        $customJs = '';
        $customHead = '';
        
        if ($activeTheme) {
            $themePath = __DIR__ . '/../../themes/' . $activeTheme['slug'];
            
            // POST işlemleri
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = $_POST['action'] ?? '';
                
                if ($action === 'save_file') {
                    // Tema dosyası kaydetme
                    $filePath = $_POST['file_path'] ?? '';
                    $content = $_POST['content'] ?? '';
                    
                    $result = $this->saveThemeFile($activeTheme['slug'], $filePath, $content);
                    
                    if ($result['success']) {
                        $redirectUrl = admin_url('design', ['tab' => 'files', 'file' => $filePath, 'saved' => 1]);
                        header("Location: " . $redirectUrl);
                        exit;
                    } else {
                        $message = $result['message'];
                        $messageType = 'error';
                    }
                } elseif ($action === 'save_custom_code') {
                    // Özel kod kaydetme
                    $codeType = $_POST['code_type'] ?? '';
                    $content = $_POST['content'] ?? '';
                    
                    $result = $this->saveThemeCustomCode($activeTheme['id'], $codeType, $content);
                    
                    if ($result['success']) {
                        $redirectUrl = admin_url('design', ['tab' => $codeType, 'saved' => 1]);
                        header("Location: " . $redirectUrl);
                        exit;
                    } else {
                        $message = $result['message'];
                        $messageType = 'error';
                    }
                }
            }
            
            // Başarı mesajı
            if (isset($_GET['saved']) && $_GET['saved'] == '1') {
                $message = 'Değişiklikler başarıyla kaydedildi.';
                $messageType = 'success';
            }
            
            // Tema dosya ağacını oluştur
            $themeFiles = $this->buildThemeFileTree($themePath);
            
            // Seçili dosya içeriğini oku
            if ($selectedFile && $activeTab === 'files') {
                $fullPath = $themePath . '/' . $selectedFile;
                // Güvenlik kontrolü - path traversal engelle
                $realPath = realpath($fullPath);
                $realThemePath = realpath($themePath);
                
                if ($realPath && strpos($realPath, $realThemePath) === 0 && file_exists($realPath)) {
                    $fileContent = file_get_contents($realPath);
                } else {
                    $selectedFile = '';
                }
            }
            
            // Özel kodları veritabanından al
            $customCss = $this->getThemeCustomCode($activeTheme['id'], 'css');
            $customJs = $this->getThemeCustomCode($activeTheme['id'], 'js');
            $customHead = $this->getThemeCustomCode($activeTheme['id'], 'head');
        }
        
        $data = [
            'title' => 'Tasarım Düzenleme',
            'user' => get_logged_in_user(),
            'message' => $message,
            'messageType' => $messageType,
            'activeTab' => $activeTab,
            'activeTheme' => $activeTheme,
            'themeFiles' => $themeFiles,
            'selectedFile' => $selectedFile,
            'fileContent' => $fileContent,
            'customCss' => $customCss,
            'customJs' => $customJs,
            'customHead' => $customHead
        ];
        
        $this->view('admin/design', $data);
    }
    
    /**
     * Tema dosya ağacını oluştur
     */
    private function buildThemeFileTree($themePath) {
        $tree = [];
        $allowedExtensions = ['php', 'css', 'js', 'json', 'html', 'htm'];
        $ignoreFolders = ['node_modules', 'vendor', '.git', 'images'];
        
        if (!is_dir($themePath)) {
            return $tree;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($themePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            $relativePath = str_replace($themePath . '/', '', $file->getPathname());
            $parts = explode('/', $relativePath);
            
            // İzin verilmeyen klasörleri atla
            $skip = false;
            foreach ($ignoreFolders as $ignore) {
                if (in_array($ignore, $parts)) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) continue;
            
            // Backup dosyalarını atla
            if (strpos($file->getFilename(), '.backup') !== false) continue;
            
            $current = &$tree;
            
            if ($file->isDir()) {
                foreach ($parts as $part) {
                    if (!isset($current[$part])) {
                        $current[$part] = [];
                    }
                    $current = &$current[$part];
                }
            } else {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, $allowedExtensions)) {
                    // Dosyayı ağaca ekle
                    $fileName = array_pop($parts);
                    foreach ($parts as $part) {
                        if (!isset($current[$part])) {
                            $current[$part] = [];
                        }
                        $current = &$current[$part];
                    }
                    $current[$fileName] = $relativePath;
                }
            }
        }
        
        // Boş klasörleri temizle ve sırala
        $this->cleanAndSortTree($tree);
        
        return $tree;
    }
    
    /**
     * Dosya ağacını temizle ve sırala
     */
    private function cleanAndSortTree(&$tree) {
        foreach ($tree as $key => &$value) {
            if (is_array($value)) {
                if (empty($value)) {
                    unset($tree[$key]);
                } else {
                    $this->cleanAndSortTree($value);
                    if (empty($value)) {
                        unset($tree[$key]);
                    }
                }
            }
        }
        
        // Klasörleri önce, dosyaları sonra sırala
        uksort($tree, function($a, $b) use ($tree) {
            $aIsDir = is_array($tree[$a]);
            $bIsDir = is_array($tree[$b]);
            
            if ($aIsDir && !$bIsDir) return -1;
            if (!$aIsDir && $bIsDir) return 1;
            return strcasecmp($a, $b);
        });
    }
    
    /**
     * Tema dosyasını kaydet
     */
    private function saveThemeFile($themeSlug, $relativePath, $content) {
        $themePath = __DIR__ . '/../../themes/' . $themeSlug;
        $fullPath = $themePath . '/' . $relativePath;
        
        // Güvenlik kontrolü - path traversal engelle
        $realPath = realpath(dirname($fullPath));
        $realThemePath = realpath($themePath);
        
        if (!$realPath || strpos($realPath, $realThemePath) !== 0) {
            return ['success' => false, 'message' => 'Geçersiz dosya yolu.'];
        }
        
        // Sadece izin verilen uzantılar
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $allowedExtensions = ['php', 'css', 'js', 'json', 'html', 'htm'];
        
        if (!in_array($ext, $allowedExtensions)) {
            return ['success' => false, 'message' => 'Bu dosya türü düzenlenemez.'];
        }
        
        // Dosya var mı kontrol et
        if (!file_exists($fullPath)) {
            return ['success' => false, 'message' => 'Dosya bulunamadı.'];
        }
        
        // Yazılabilir mi kontrol et
        if (!is_writable($fullPath)) {
            return ['success' => false, 'message' => 'Dosya yazılabilir değil. İzinleri kontrol edin.'];
        }
        
        // Backup oluştur
        $backupPath = $fullPath . '.backup.' . date('Y-m-d_H-i-s');
        @copy($fullPath, $backupPath);
        
        // Dosyayı kaydet
        $result = @file_put_contents($fullPath, $content);
        
        if ($result !== false) {
            // Opcache'i temizle
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($fullPath, true);
            }
            return ['success' => true, 'message' => 'Dosya başarıyla kaydedildi.'];
        }
        
        $error = error_get_last();
        return ['success' => false, 'message' => 'Dosya kaydedilirken hata: ' . ($error['message'] ?? 'Bilinmeyen hata')];
    }
    
    /**
     * Tema özel kodunu kaydet
     */
    private function saveThemeCustomCode($themeId, $codeType, $content) {
        if (!$themeId) {
            return ['success' => false, 'message' => 'Tema ID bulunamadı.'];
        }
        
        $allowedTypes = ['css', 'js', 'head', 'footer'];
        if (!in_array($codeType, $allowedTypes)) {
            return ['success' => false, 'message' => 'Geçersiz kod türü.'];
        }
        
        try {
            $db = Database::getInstance();
            
            // Mevcut kayıt var mı kontrol et
            $existing = $db->fetch(
                "SELECT id FROM theme_custom_code WHERE theme_id = ? AND code_type = ?",
                [$themeId, $codeType]
            );
            
            if ($existing) {
                // Güncelle
                $db->query(
                    "UPDATE theme_custom_code SET code_content = ?, updated_at = NOW() WHERE theme_id = ? AND code_type = ?",
                    [$content, $themeId, $codeType]
                );
            } else {
                // Yeni kayıt ekle
                $db->query(
                    "INSERT INTO theme_custom_code (theme_id, code_type, code_content, is_active, created_at) VALUES (?, ?, ?, 1, NOW())",
                    [$themeId, $codeType, $content]
                );
            }
            
            return ['success' => true, 'message' => 'Özel kod başarıyla kaydedildi.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()];
        }
    }
    
    /**
     * Tema özel kodunu getir
     */
    private function getThemeCustomCode($themeId, $codeType) {
        if (!$themeId) {
            return '';
        }
        
        try {
            $db = Database::getInstance();
            $result = $db->fetch(
                "SELECT code_content FROM theme_custom_code WHERE theme_id = ? AND code_type = ? AND is_active = 1",
                [$themeId, $codeType]
            );
            
            return $result['code_content'] ?? '';
        } catch (Exception $e) {
            return '';
        }
    }
    
    /**
     * CSS/JS düzenleme sayfası
     */
    public function design_assets() {
        // Giriş kontrolü
        if (!is_user_logged_in()) {
            $this->redirect(admin_url('login'));
        }
        
        // Yetki kontrolü
        if (!current_user_can('themes.edit_code')) {
            $_SESSION['error_message'] = 'Bu modülde yetkiniz yoktur!';
            $this->redirect(admin_url('dashboard'));
        }
        
        $message = null;
        $messageType = null;
        
        // Frontend CSS dosyaları (admin ve slider CSS'leri hariç)
        $frontendCssFiles = [];
        $cssDir = __DIR__ . '/../../public/frontend/css/';
        if (is_dir($cssDir)) {
            $files = scandir($cssDir);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'css') {
                    // Admin ve slider CSS'lerini hariç tut
                    if (strpos($file, 'admin-') === false && strpos($file, 'slider') === false) {
                        $frontendCssFiles[] = $file;
                    }
                }
            }
        }
        
        // Eğer frontend CSS dosyası yoksa, boş bir dosya oluştur
        if (empty($frontendCssFiles)) {
            $defaultCssFile = 'frontend.css';
            $defaultCssPath = $cssDir . $defaultCssFile;
            if (!file_exists($defaultCssPath)) {
                file_put_contents($defaultCssPath, "/* Frontend CSS */\n");
            }
            $frontendCssFiles[] = $defaultCssFile;
        }
        
        $activeFile = $_GET['file'] ?? $frontendCssFiles[0];
        
        // POST isteği geldiğinde dosyayı kaydet
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fileName = $_POST['file_name'] ?? '';
            $content = $_POST['content'] ?? '';
            
            if ($fileName && $content !== '') {
                $filePath = __DIR__ . '/../../public/frontend/css/' . basename($fileName);
                
                // Sadece frontend CSS dosyalarına izin ver
                if (pathinfo($fileName, PATHINFO_EXTENSION) === 'css' && 
                    in_array(basename($fileName), $frontendCssFiles) && 
                    file_exists($filePath)) {
                    // Backup oluştur
                    $backupPath = $filePath . '.backup.' . date('Y-m-d_H-i-s');
                    @copy($filePath, $backupPath);
                    
                    // Dosyayı kaydet
                    $result = @file_put_contents($filePath, $content);
                    
                    if ($result !== false) {
                        // Opcache'i temizle
                        if (function_exists('opcache_invalidate')) {
                            opcache_invalidate($filePath, true);
                        }
                        
                        // POST-REDIRECT-GET pattern: Başarılı kayıttan sonra redirect yap
                        $redirectUrl = admin_url('design-assets') . '&file=' . urlencode(basename($fileName)) . '&saved=1';
                        $this->redirect($redirectUrl);
                        return;
                    } else {
                        $error = error_get_last();
                        $message = 'Dosya kaydedilirken bir hata oluştu: ' . ($error['message'] ?? 'Bilinmeyen hata');
                        $messageType = 'error';
                    }
                } else {
                    $message = 'Geçersiz dosya veya dosya bulunamadı. Sadece frontend CSS dosyaları düzenlenebilir.';
                    $messageType = 'error';
                }
            } else {
                $message = 'Dosya adı veya içerik boş olamaz.';
                $messageType = 'error';
            }
        }
        
        // Başarı mesajını URL parametresinden al
        if (isset($_GET['saved']) && $_GET['saved'] == '1') {
            $message = 'Dosya başarıyla güncellendi.';
            $messageType = 'success';
        }
        
        // Seçili dosyanın içeriğini getir
        $fileContent = '';
        if (in_array($activeFile, $frontendCssFiles)) {
            $filePath = __DIR__ . '/../../public/frontend/css/' . $activeFile;
            $fileContent = file_exists($filePath) ? file_get_contents($filePath) : '';
        }
        
        $data = [
            'title' => 'CSS/JS Düzenleme',
            'user' => get_logged_in_user(),
            'message' => $message,
            'messageType' => $messageType,
            'activeFile' => $activeFile,
            'cssFiles' => $frontendCssFiles,
            'fileContent' => $fileContent
        ];
        
        $this->view('admin/design-assets', $data);
    }
    
    /**
     * Component dosya yolunu döndürür
     */
    private function getComponentPath($componentType) {
        $basePath = __DIR__ . '/../views/';
        
        $paths = [
            'header' => $basePath . 'frontend/snippets/header.php',
            'footer' => $basePath . 'frontend/snippets/footer.php',
            'layout' => $basePath . 'frontend/layouts/default.php',
            'home' => $basePath . 'frontend/home.php',
            'contact' => $basePath . 'frontend/contact.php',
        ];
        
        return $paths[$componentType] ?? null;
    }
    
    /**
     * Component içeriğini döndürür
     */
    private function getComponentContent($componentPath) {
        // AdminController app/controllers/ dizininde, views app/views/ dizininde
        $basePath = realpath(__DIR__ . '/../views/');
        $filePath = $basePath . '/' . $componentPath . '.php';
        
        // Normalize path
        $filePath = realpath($filePath);
        
        if (!$filePath || !file_exists($filePath)) {
            // Fallback: Direkt path dene
            $filePath = __DIR__ . '/../views/' . $componentPath . '.php';
            if (!file_exists($filePath)) {
                return '';
            }
        }
        
        $content = @file_get_contents($filePath);
        
        if ($content === false) {
            return '';
        }
        
        return $content;
    }
    
    /**
     * SMTP Ayarları sayfası
     */
    public function smtp_settings() {
        // Giriş kontrolü
        if (!is_user_logged_in()) {
            $this->redirect(admin_url('login'));
        }
        
        // Yetki kontrolü
        if (!current_user_can('settings.edit')) {
            $_SESSION['error_message'] = 'Bu modülde yetkiniz yoktur!';
            $this->redirect(admin_url('dashboard'));
        }
        
        $message = null;
        $messageType = null;
        
        // POST isteği geldiğinde ayarları kaydet
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // SMTP Ayarları kaydetme
            if (isset($_POST['save_smtp'])) {
                update_option('smtp_host', trim($_POST['smtp_host'] ?? ''));
                update_option('smtp_port', (int)($_POST['smtp_port'] ?? 587));
                update_option('smtp_username', trim($_POST['smtp_username'] ?? ''));
                
                // Şifre sadece girilmişse güncelle
                if (!empty($_POST['smtp_password'])) {
                    update_option('smtp_password', $_POST['smtp_password']);
                }
                
                update_option('smtp_encryption', $_POST['smtp_encryption'] ?? 'tls');
                update_option('smtp_from_email', trim($_POST['smtp_from_email'] ?? ''));
                update_option('smtp_from_name', trim($_POST['smtp_from_name'] ?? ''));
                
                $message = 'SMTP ayarları başarıyla kaydedildi.';
                $messageType = 'success';
            }
        }
        
        // Mevcut ayarları getir
        $data = [
            'title' => 'SMTP Ayarları',
            'user' => get_logged_in_user(),
            'message' => $message,
            'messageType' => $messageType,
            'settings' => [
                'smtp_host' => get_option('smtp_host', ''),
                'smtp_port' => get_option('smtp_port', 587),
                'smtp_username' => get_option('smtp_username', ''),
                'smtp_password' => get_option('smtp_password', ''),
                'smtp_encryption' => get_option('smtp_encryption', 'tls'),
                'smtp_from_email' => get_option('smtp_from_email', ''),
                'smtp_from_name' => get_option('smtp_from_name', ''),
            ]
        ];
        
        $this->view('admin/smtp-settings', $data);
    }
    
    /**
     * SMTP Bağlantı Testi (AJAX)
     */
    public function test_smtp_connection() {
        // Giriş kontrolü
        if (!is_user_logged_in()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
            exit;
        }
        
        // Yetki kontrolü
        if (!current_user_can('settings.edit')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Bu işlem için yetkiniz yok']);
            exit;
        }
        
        header('Content-Type: application/json');
        
        // Mailer sınıfını yükle
        require_once __DIR__ . '/../../core/Mailer.php';
        
        // POST'tan gelen verilerle test et (form kaydedilmeden önce test için)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $config = [
                'host' => trim($_POST['smtp_host'] ?? ''),
                'port' => (int)($_POST['smtp_port'] ?? 587),
                'username' => trim($_POST['smtp_username'] ?? ''),
                'password' => $_POST['smtp_password'] ?? '',
                'encryption' => $_POST['smtp_encryption'] ?? 'tls',
                'from_email' => trim($_POST['smtp_from_email'] ?? ''),
                'from_name' => trim($_POST['smtp_from_name'] ?? ''),
            ];
            
            // Şifre boşsa veritabanından al
            if (empty($config['password'])) {
                $config['password'] = get_option('smtp_password', '');
            }
            
            $mailer = new Mailer($config);
        } else {
            $mailer = new Mailer();
        }
        
        $result = $mailer->testConnection();
        
        echo json_encode($result);
        exit;
    }
    
    /**
     * Test E-postası Gönder (AJAX)
     */
    public function send_test_email() {
        // Giriş kontrolü
        if (!is_user_logged_in()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
            exit;
        }
        
        // Yetki kontrolü
        if (!current_user_can('settings.edit')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Bu işlem için yetkiniz yok']);
            exit;
        }
        
        header('Content-Type: application/json');
        
        // Test e-posta adresi
        $testEmail = trim($_POST['test_email'] ?? '');
        
        if (empty($testEmail)) {
            echo json_encode(['success' => false, 'message' => 'Lütfen bir e-posta adresi girin.']);
            exit;
        }
        
        if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Geçersiz e-posta adresi.']);
            exit;
        }
        
        // Mailer sınıfını yükle
        require_once __DIR__ . '/../../core/Mailer.php';
        
        // POST'tan gelen verilerle test et
        $config = [
            'host' => trim($_POST['smtp_host'] ?? ''),
            'port' => (int)($_POST['smtp_port'] ?? 587),
            'username' => trim($_POST['smtp_username'] ?? ''),
            'password' => $_POST['smtp_password'] ?? '',
            'encryption' => $_POST['smtp_encryption'] ?? 'tls',
            'from_email' => trim($_POST['smtp_from_email'] ?? ''),
            'from_name' => trim($_POST['smtp_from_name'] ?? ''),
        ];
        
        // Şifre boşsa veritabanından al
        if (empty($config['password'])) {
            $config['password'] = get_option('smtp_password', '');
        }
        
        $mailer = new Mailer($config);
        
        // Önce SMTP yapılandırmasını kontrol et
        if (!$mailer->isConfigured()) {
            echo json_encode([
                'success' => false,
                'message' => 'SMTP ayarları eksik. Lütfen tüm gerekli alanları doldurun.'
            ]);
            exit;
        }
        
        // Önce bağlantıyı test et
        $connectionTest = $mailer->testConnection();
        if (!$connectionTest['success']) {
            echo json_encode([
                'success' => false,
                'message' => 'SMTP bağlantı hatası: ' . $connectionTest['message']
            ]);
            exit;
        }
        
        // Test e-postası gönder
        $siteName = get_option('seo_title', 'CMS');
        $subject = "{$siteName} - SMTP Test";
        $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #333; margin-bottom: 20px;'>SMTP Test E-postası</h2>
                <p style='color: #555; line-height: 1.6;'>Bu e-postayı görüyorsanız, SMTP ayarlarınız doğru çalışıyor demektir.</p>
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <tr>
                        <td style='padding: 8px; border-bottom: 1px solid #eee; color: #888;'>Sunucu</td>
                        <td style='padding: 8px; border-bottom: 1px solid #eee; color: #333;'>{$config['host']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px; border-bottom: 1px solid #eee; color: #888;'>Port</td>
                        <td style='padding: 8px; border-bottom: 1px solid #eee; color: #333;'>{$config['port']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px; border-bottom: 1px solid #eee; color: #888;'>Şifreleme</td>
                        <td style='padding: 8px; border-bottom: 1px solid #eee; color: #333;'>" . strtoupper($config['encryption']) . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px; border-bottom: 1px solid #eee; color: #888;'>Gönderen</td>
                        <td style='padding: 8px; border-bottom: 1px solid #eee; color: #333;'>{$config['from_name']} &lt;{$config['from_email']}&gt;</td>
                    </tr>
                </table>
                <p style='color: #999; font-size: 12px; margin-top: 30px;'>Gönderim tarihi: " . date('d.m.Y H:i:s') . "</p>
            </div>
        ";
        
        $result = $mailer->send($testEmail, $subject, $body);
        
        if ($result) {
            echo json_encode([
                'success' => true, 
                'message' => "Test e-postası başarıyla gönderildi! Lütfen {$testEmail} adresini ve spam klasörünü kontrol edin."
            ]);
        } else {
            $error = $mailer->getLastError();
            if (empty($error)) {
                $error = 'Bilinmeyen hata. Lütfen SMTP ayarlarınızı kontrol edin.';
            }
            echo json_encode([
                'success' => false, 
                'message' => 'E-posta gönderilemedi: ' . $error
            ]);
        }
        exit;
    }
    
}

