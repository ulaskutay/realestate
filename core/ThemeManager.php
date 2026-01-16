<?php
/**
 * ThemeManager - Tema Yönetim Sınıfı
 * Tema kurulum, aktifleştirme ve ayarlar yönetimi
 */

class ThemeManager {
    private static $instance = null;
    private $db;
    private $themesPath;
    private $activeTheme = null;
    private $themeCache = [];
    
    private function __construct() {
        $this->themesPath = dirname(__DIR__) . '/themes';
        $this->initDatabase();
    }
    
    /**
     * Singleton instance
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Veritabanı bağlantısını başlat
     */
    private function initDatabase(): void {
        if (!class_exists('Database')) {
            require_once dirname(__DIR__) . '/core/Database.php';
        }
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Themes klasörünün yolunu döndür
     */
    public function getThemesPath(): string {
        return $this->themesPath;
    }
    
    // ==========================================
    // TEMA İŞLEMLERİ
    // ==========================================
    
    /**
     * Tüm yüklü temaları getir
     */
    public function getInstalledThemes(): array {
        $themes = [];
        
        // Veritabanından yüklü temaları al
        try {
            $stmt = $this->db->query("SELECT * FROM themes ORDER BY is_active DESC, name ASC");
            $dbThemes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($dbThemes as $theme) {
                $theme['settings_schema'] = json_decode($theme['settings_schema'] ?? '{}', true);
                $theme['screenshot'] = $this->getScreenshotUrl($theme['slug'], $theme['screenshot'] ?? null);
                $themes[$theme['slug']] = $theme;
            }
        } catch (PDOException $e) {
            // Tablo yoksa sessizce geç (yeni kurulumlar için)
            error_log("Themes table error: " . $e->getMessage());
        }
        
        // Klasördeki temaları da kontrol et (yüklü olmayan)
        $this->scanThemesDirectory($themes);
        
        return $themes;
    }
    
    /**
     * Tema klasörlerini tara ve yüklü olmayan temaları bul
     */
    private function scanThemesDirectory(array &$themes): void {
        if (!is_dir($this->themesPath)) {
            return;
        }
        
        $dirs = scandir($this->themesPath);
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') continue;
            
            $themePath = $this->themesPath . '/' . $dir;
            $themeJson = $themePath . '/theme.json';
            
            if (is_dir($themePath) && file_exists($themeJson)) {
                if (!isset($themes[$dir])) {
                    // Tema yüklü değil, sadece klasörde var
                    $manifest = $this->parseThemeManifest($themeJson);
                    if ($manifest) {
                        $themes[$dir] = [
                            'id' => null,
                            'slug' => $dir,
                            'name' => $manifest['name'] ?? $dir,
                            'version' => $manifest['version'] ?? '1.0.0',
                            'author' => $manifest['author'] ?? '',
                            'description' => $manifest['description'] ?? '',
                            'screenshot' => $this->getScreenshotUrl($dir, $manifest['screenshot'] ?? null),
                            'is_active' => 0,
                            'is_installed' => false,
                            'settings_schema' => $manifest
                        ];
                    }
                } else {
                    // Tema yüklü, is_installed işaretle ve screenshot'ı güncelle
                    $themes[$dir]['is_installed'] = true;
                    $themes[$dir]['screenshot'] = $this->getScreenshotUrl($dir, $themes[$dir]['screenshot'] ?? null);
                }
            }
        }
    }
    
    /**
     * Tema screenshot URL'ini oluştur
     */
    private function getScreenshotUrl(string $slug, ?string $screenshot): ?string {
        $themePath = $this->themesPath . '/' . $slug;
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Screenshot yolu belirtilmişse önce onu dene
        if ($screenshot) {
            $fullPath = $themePath . '/' . $screenshot;
            if (file_exists($fullPath)) {
                return $protocol . '://' . $host . '/themes/' . $slug . '/' . $screenshot;
            }
        }
        
        // Varsayılan dosya isimleri
        $possibleFiles = ['screenshot.png', 'screenshot.jpg', 'screenshot.svg', 'screenshot.webp'];
        
        foreach ($possibleFiles as $file) {
            $fullPath = $themePath . '/' . $file;
            if (file_exists($fullPath)) {
                return $protocol . '://' . $host . '/themes/' . $slug . '/' . $file;
            }
        }
        
        return null;
    }
    
    /**
     * theme.json dosyasını parse et
     */
    public function parseThemeManifest(string $path): ?array {
        if (!file_exists($path)) {
            return null;
        }
        
        $content = file_get_contents($path);
        $manifest = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Theme manifest parse error: " . json_last_error_msg() . " in $path");
            return null;
        }
        
        return $manifest;
    }
    
    /**
     * Aktif temayı getir
     */
    public function getActiveTheme(): ?array {
        if ($this->activeTheme !== null) {
            return $this->activeTheme;
        }
        
        try {
            $stmt = $this->db->query("SELECT * FROM themes WHERE is_active = 1 LIMIT 1");
            $theme = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($theme) {
                // settings_schema'yı parse et
                $parsedSchema = json_decode($theme['settings_schema'] ?? '{}', true);
                
                // Eğer settings_schema boş veya eksikse, theme.json'dan yükle
                if (empty($parsedSchema) || !isset($parsedSchema['settings'])) {
                    $themeJson = $this->themesPath . '/' . $theme['slug'] . '/theme.json';
                    if (file_exists($themeJson)) {
                        $manifest = $this->parseThemeManifest($themeJson);
                        if ($manifest) {
                            $parsedSchema = $manifest;
                            
                            // Veritabanını da güncelle
                            try {
                                $updateStmt = $this->db->prepare("UPDATE themes SET settings_schema = ? WHERE slug = ?");
                                $updateStmt->execute([json_encode($manifest), $theme['slug']]);
                            } catch (Exception $e) {
                                // Güncelleme başarısız olsa da devam et
                            }
                        }
                    }
                }
                
                $theme['settings_schema'] = $parsedSchema;
                $theme['screenshot'] = $this->getScreenshotUrl($theme['slug'], $theme['screenshot'] ?? null);
                $this->activeTheme = $theme;
            }
        } catch (PDOException $e) {
            error_log("Active theme error: " . $e->getMessage());
            return null;
        }
        
        return $this->activeTheme;
    }
    
    /**
     * Tek bir temayı slug ile getir
     */
    public function getTheme(string $slug): ?array {
        // Cache'i bypass et - her zaman güncel veri al
        // if (isset($this->themeCache[$slug])) {
        //     return $this->themeCache[$slug];
        // }
        
        $stmt = $this->db->prepare("SELECT * FROM themes WHERE slug = ?");
        $stmt->execute([$slug]);
        $theme = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($theme) {
            // settings_schema'yı parse et
            $parsedSchema = json_decode($theme['settings_schema'] ?? '{}', true);
            
            // Eğer settings_schema boş veya eksikse, theme.json'dan yükle
            if (empty($parsedSchema) || !isset($parsedSchema['settings'])) {
                $themeJson = $this->themesPath . '/' . $slug . '/theme.json';
                error_log("ThemeManager::getTheme - Loading theme.json from: " . $themeJson);
                if (file_exists($themeJson)) {
                    $manifest = $this->parseThemeManifest($themeJson);
                    error_log("ThemeManager::getTheme - Manifest loaded: " . ($manifest ? 'yes' : 'no'));
                    if ($manifest) {
                        $parsedSchema = $manifest;
                        
                        // Veritabanını da güncelle
                        try {
                            $updateStmt = $this->db->prepare("UPDATE themes SET settings_schema = ? WHERE slug = ?");
                            $updateStmt->execute([json_encode($manifest), $slug]);
                            error_log("ThemeManager::getTheme - Database updated with settings_schema");
                        } catch (Exception $e) {
                            error_log("ThemeManager::getTheme - DB update error: " . $e->getMessage());
                        }
                    }
                } else {
                    error_log("ThemeManager::getTheme - theme.json NOT FOUND at: " . $themeJson);
                }
            }
            
            $theme['settings_schema'] = $parsedSchema;
            $this->themeCache[$slug] = $theme;
            return $theme;
        }
        
        // Veritabanında yok, klasörde var mı kontrol et
        $themeJson = $this->themesPath . '/' . $slug . '/theme.json';
        if (file_exists($themeJson)) {
            $manifest = $this->parseThemeManifest($themeJson);
            if ($manifest) {
                return [
                    'id' => null,
                    'slug' => $slug,
                    'name' => $manifest['name'] ?? $slug,
                    'version' => $manifest['version'] ?? '1.0.0',
                    'author' => $manifest['author'] ?? '',
                    'description' => $manifest['description'] ?? '',
                    'screenshot' => $manifest['screenshot'] ?? null,
                    'is_active' => 0,
                    'is_installed' => false,
                    'settings_schema' => $manifest
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Temayı aktifleştir
     */
    public function activateTheme(string $slug): bool {
        try {
            // Önce tema klasörde var mı kontrol et
            $themePath = $this->themesPath . '/' . $slug;
            if (!is_dir($themePath)) {
                throw new Exception("Tema klasörü bulunamadı: $slug");
            }
            
            // Eski aktif temayı al
            $oldTheme = $this->getActiveTheme();
            
            // Tema veritabanında kayıtlı mı?
            $theme = $this->getTheme($slug);
            if (!$theme || $theme['id'] === null) {
                // Temayı önce yükle
                $this->installThemeFromDirectory($slug);
            }
            
            // Eski temanın modüllerini deaktive et
            if ($oldTheme && $oldTheme['slug'] !== $slug) {
                $this->unloadOldThemeModules($oldTheme['slug']);
            }
            
            // Tüm temaları deaktif et
            $this->db->exec("UPDATE themes SET is_active = 0");
            
            // Seçilen temayı aktif et
            $stmt = $this->db->prepare("UPDATE themes SET is_active = 1 WHERE slug = ?");
            $stmt->execute([$slug]);
            
            // Yeni temanın modüllerini yükle
            $this->loadNewThemeModules($slug);
            
            // Cache'i temizle
            $this->activeTheme = null;
            $this->themeCache = [];
            
            // PHP opcode cache'i temizle
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            
            // APCu cache'i temizle
            if (function_exists('apcu_clear_cache')) {
                apcu_clear_cache();
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Theme activation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eski temanın modüllerini deaktive et
     */
    private function unloadOldThemeModules(string $oldSlug): void {
        $oldThemePath = $this->themesPath . '/' . $oldSlug;
        
        if (!class_exists('ModuleLoader')) {
            require_once dirname(__DIR__) . '/core/ModuleLoader.php';
        }
        
        $moduleLoader = ModuleLoader::getInstance();
        $moduleLoader->unloadThemeModules($oldThemePath);
    }
    
    /**
     * Yeni temanın modüllerini yükle
     */
    private function loadNewThemeModules(string $newSlug): void {
        $newThemePath = $this->themesPath . '/' . $newSlug;
        
        if (!class_exists('ModuleLoader')) {
            require_once dirname(__DIR__) . '/core/ModuleLoader.php';
        }
        
        $moduleLoader = ModuleLoader::getInstance();
        $moduleLoader->loadThemeModules($newThemePath);
    }
    
    /**
     * Klasördeki temayı veritabanına yükle
     */
    public function installThemeFromDirectory(string $slug): bool {
        $themePath = $this->themesPath . '/' . $slug;
        $themeJson = $themePath . '/theme.json';
        
        if (!file_exists($themeJson)) {
            throw new Exception("theme.json bulunamadı: $slug");
        }
        
        $manifest = $this->parseThemeManifest($themeJson);
        if (!$manifest) {
            throw new Exception("theme.json parse edilemedi: $slug");
        }
        
        // Screenshot URL'sini ayarla
        $screenshot = null;
        if (!empty($manifest['screenshot'])) {
            $screenshot = '/themes/' . $slug . '/' . $manifest['screenshot'];
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO themes (slug, name, version, author, author_url, description, screenshot, settings_schema)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                name = VALUES(name),
                version = VALUES(version),
                author = VALUES(author),
                author_url = VALUES(author_url),
                description = VALUES(description),
                screenshot = VALUES(screenshot),
                settings_schema = VALUES(settings_schema)
        ");
        
        return $stmt->execute([
            $slug,
            $manifest['name'] ?? $slug,
            $manifest['version'] ?? '1.0.0',
            $manifest['author'] ?? null,
            $manifest['author_url'] ?? null,
            $manifest['description'] ?? null,
            $screenshot,
            json_encode($manifest)
        ]);
    }
    
    /**
     * ZIP dosyasından tema yükle
     */
    public function installThemeFromZip(string $zipPath): array {
        if (!file_exists($zipPath)) {
            return ['success' => false, 'message' => 'ZIP dosyası bulunamadı'];
        }
        
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return ['success' => false, 'message' => 'ZIP dosyası açılamadı'];
        }
        
        // İlk dizini bul (tema klasörü adı)
        $themeSlug = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (strpos($name, '/') !== false) {
                $themeSlug = explode('/', $name)[0];
                break;
            }
        }
        
        if (!$themeSlug) {
            $zip->close();
            return ['success' => false, 'message' => 'Geçersiz tema yapısı'];
        }
        
        // theme.json var mı kontrol et
        $hasManifest = false;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === $themeSlug . '/theme.json') {
                $hasManifest = true;
                break;
            }
        }
        
        if (!$hasManifest) {
            $zip->close();
            return ['success' => false, 'message' => 'theme.json dosyası bulunamadı'];
        }
        
        // Tema zaten yüklü mü?
        $targetPath = $this->themesPath . '/' . $themeSlug;
        if (is_dir($targetPath)) {
            // Mevcut temayı yedekle
            $backupPath = $targetPath . '_backup_' . date('YmdHis');
            rename($targetPath, $backupPath);
        }
        
        // ZIP'i çıkar
        $zip->extractTo($this->themesPath);
        $zip->close();
        
        // Veritabanına kaydet
        try {
            $this->installThemeFromDirectory($themeSlug);
            
            // Yedek varsa sil
            if (isset($backupPath) && is_dir($backupPath)) {
                $this->deleteDirectory($backupPath);
            }
            
            // Cache'i temizle
            $this->themeCache = [];
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            
            return ['success' => true, 'message' => 'Tema başarıyla yüklendi', 'slug' => $themeSlug];
        } catch (Exception $e) {
            // Hata durumunda yedeği geri yükle
            if (isset($backupPath) && is_dir($backupPath)) {
                if (is_dir($targetPath)) {
                    $this->deleteDirectory($targetPath);
                }
                rename($backupPath, $targetPath);
            }
            return ['success' => false, 'message' => 'Tema yüklenirken hata: ' . $e->getMessage()];
        }
    }
    
    /**
     * Temayı kaldır
     */
    public function uninstallTheme(string $slug): bool {
        // Aktif tema kaldırılamaz
        $theme = $this->getTheme($slug);
        if ($theme && $theme['is_active']) {
            throw new Exception("Aktif tema kaldırılamaz");
        }
        
        // Veritabanından sil
        $stmt = $this->db->prepare("DELETE FROM themes WHERE slug = ?");
        $stmt->execute([$slug]);
        
        // Klasörü sil (opsiyonel)
        $themePath = $this->themesPath . '/' . $slug;
        if (is_dir($themePath)) {
            $this->deleteDirectory($themePath);
        }
        
        return true;
    }
    
    /**
     * Klasörü recursive olarak sil
     */
    private function deleteDirectory(string $dir): bool {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        
        return rmdir($dir);
    }
    
    // ==========================================
    // TEMA AYARLARI
    // ==========================================
    
    /**
     * Tema ayarlarını getir (varsayılan + kullanıcı değerleri)
     */
    public function getThemeSettings(string $slug): array {
        $theme = $this->getTheme($slug);
        if (!$theme) {
            return [];
        }
        
        $schema = $theme['settings_schema']['settings'] ?? [];
        $settings = [];
        
        // Schema'dan varsayılan değerleri al
        foreach ($schema as $group => $groupSettings) {
            $settings[$group] = [];
            foreach ($groupSettings as $key => $config) {
                $settings[$group][$key] = [
                    'label' => $config['label'] ?? $key,
                    'type' => $config['type'] ?? 'text',
                    'default' => $config['default'] ?? '',
                    'options' => $config['options'] ?? [],
                    'value' => $config['default'] ?? ''
                ];
            }
        }
        
        // Kullanıcı değerlerini üzerine yaz
        if ($theme['id']) {
            $stmt = $this->db->prepare("SELECT option_group, option_key, option_value FROM theme_options WHERE theme_id = ?");
            $stmt->execute([$theme['id']]);
            $userOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($userOptions as $option) {
                $group = $option['option_group'];
                $key = $option['option_key'];
                
                // Eğer grup schema'da yoksa, oluştur
                if (!isset($settings[$group])) {
                    $settings[$group] = [];
                }
                
                // Eğer key schema'da yoksa, oluştur
                if (!isset($settings[$group][$key])) {
                    $settings[$group][$key] = [
                        'label' => $key,
                        'type' => 'text',
                        'default' => '',
                        'options' => [],
                        'value' => ''
                    ];
                }
                
                // Değeri ayarla - JSON ise decode et
                $value = $option['option_value'];
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $value = $decoded;
                }
                $settings[$group][$key]['value'] = $value;
            }
        }
        
        return $settings;
    }
    
    /**
     * Tek bir tema ayarını getir
     */
    public function getThemeOption(string $key, $default = null, ?string $group = null) {
        $theme = $this->getActiveTheme();
        if (!$theme || !$theme['id']) {
            return $default;
        }
        
        $sql = "SELECT option_value FROM theme_options WHERE theme_id = ? AND option_key = ?";
        $params = [$theme['id'], $key];
        
        if ($group) {
            $sql .= " AND option_group = ?";
            $params[] = $group;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $value = null;
        if ($result) {
            $value = $result['option_value'];
            // JSON ise decode et
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || is_object($decoded))) {
                $value = $decoded;
            }
        } else {
            // Varsayılan değeri schema'dan al
            $schema = $theme['settings_schema']['settings'] ?? [];
            if ($group && isset($schema[$group][$key]['default'])) {
                $value = $schema[$group][$key]['default'];
            } else {
                // Tüm grupları tara
                foreach ($schema as $g => $settings) {
                    if (isset($settings[$key]['default'])) {
                        $value = $settings[$key]['default'];
                        break;
                    }
                }
            }
            
            if ($value === null) {
                $value = $default;
            }
        }
        
        // Çevirilmemesi gereken ayarlar (URL'ler, teknik değerler, virgüllü listeler vb.)
        $noTranslateKeys = [
            'animated_words', 'button_link', 'secondary_button_link', 'top_button_link',
            'link', 'url', 'href', 'src', 'image', 'logo', 'favicon', 'icon',
            'color', 'font', 'size', 'width', 'height', 'padding', 'margin',
            'style', 'class', 'id', 'enabled', 'active', 'visible', 'show',
            'top_button_style', 'top_button_icon', 'reverse_layout'
        ];
        
        // Key çevirilmemesi gereken listede mi kontrol et
        $shouldTranslate = true;
        foreach ($noTranslateKeys as $noTransKey) {
            if (strpos($key, $noTransKey) !== false || $key === $noTransKey) {
                $shouldTranslate = false;
                break;
            }
        }
        
        // Çeviri filter'ını uygula (sadece çevirilmesi gereken string değerler için)
        if ($shouldTranslate && function_exists('apply_filters') && is_string($value) && !empty($value)) {
            $value = apply_filters('theme_option_value', $value, $key, $group);
        }
        
        return $value;
    }
    
    /**
     * Tema ayarını kaydet
     */
    public function saveThemeSetting(string $key, $value, string $group = 'custom'): bool {
        $theme = $this->getActiveTheme();
        if (!$theme || !$theme['id']) {
            return false;
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO theme_options (theme_id, option_group, option_key, option_value)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE option_value = VALUES(option_value)
        ");
        
        return $stmt->execute([$theme['id'], $group, $key, $value]);
    }
    
    /**
     * Birden fazla tema ayarını kaydet
     */
    public function saveThemeSettings(array $settings, string $themeSlug = null): bool {
        $theme = $themeSlug ? $this->getTheme($themeSlug) : $this->getActiveTheme();
        if (!$theme || !$theme['id']) {
            return false;
        }
        
        $this->db->beginTransaction();
        
        try {
            // Sections'ları ayrı işle
            $sections = [];
            if (isset($settings['sections'])) {
                $sections = $settings['sections'];
                unset($settings['sections']);
            }
            
            // Custom CSS'i ayrı işle
            $customCss = null;
            if (isset($settings['custom_css'])) {
                $customCss = $settings['custom_css'];
                unset($settings['custom_css']);
            }
            
            // Normal ayarları kaydet
            // UNIQUE KEY (theme_id, option_key) olduğu için, aynı key için sadece bir kayıt olabilir
            // Bu yüzden her key için kontrol edip INSERT veya UPDATE yapıyoruz
            $stmtInsertOrUpdate = $this->db->prepare("
                INSERT INTO theme_options (theme_id, option_group, option_key, option_value)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE option_value = VALUES(option_value), option_group = VALUES(option_group)
            ");
            
            foreach ($settings as $group => $groupSettings) {
                if (is_array($groupSettings)) {
                    foreach ($groupSettings as $key => $value) {
                        $valueToSave = is_array($value) ? json_encode($value) : (string)$value;
                        
                        // INSERT ... ON DUPLICATE KEY UPDATE kullanarak tek sorguda hem ekleme hem güncelleme yap
                        $stmtInsertOrUpdate->execute([$theme['id'], $group, $key, $valueToSave]);
                    }
                }
            }
            
            // Sections'ları kaydet (page_sections tablosuna)
            if (!empty($sections)) {
                $this->saveHomepageSections($sections, $theme['id']);
            }
            
            // Contact page sections'ları kaydet
            $contactSections = $settings['contact_sections'] ?? null;
            if ($contactSections !== null) {
                unset($settings['contact_sections']); // Settings'den çıkar
                $this->saveContactSections($contactSections, $theme['id']);
            }
            
            // Custom CSS'i kaydet
            if ($customCss !== null) {
                $this->saveCustomCode('css', $customCss);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Theme settings save error: " . $e->getMessage());
            error_log("Theme settings save error trace: " . $e->getTraceAsString());
            throw $e; // Exception'ı üst katmana ileterek daha iyi hata mesajı gösterilmesini sağla
        }
    }
    
    /**
     * Ana sayfa bölümlerini kaydet
     */
    private function saveHomepageSections(array $sections, int $themeId): void {
        foreach ($sections as $sectionId => $sectionData) {
            // Section var mı kontrol et (theme_id ile birlikte) - mevcut veriyi de al
            $stmt = $this->db->prepare("SELECT id, settings, items FROM page_sections WHERE theme_id = ? AND page_type = 'home' AND section_id = ? LIMIT 1");
            $stmt->execute([$themeId, $sectionId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Mevcut settings'i yükle (varsa)
            $existingSettings = [];
            if ($existing && !empty($existing['settings'])) {
                $existingSettings = json_decode($existing['settings'], true) ?? [];
            }
            
            // Mevcut items'ı yükle (varsa)
            $existingItems = [];
            if ($existing && !empty($existing['items'])) {
                $existingItems = json_decode($existing['items'], true) ?? [];
            }
            
            // Title ve subtitle: Sadece boş değilse güncelle, boşsa mevcut değeri koru
            $stmt = $this->db->prepare("SELECT title, subtitle, content FROM page_sections WHERE theme_id = ? AND page_type = 'home' AND section_id = ? LIMIT 1");
            $stmt->execute([$themeId, $sectionId]);
            $existingData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $title = !empty($sectionData['title']) ? $sectionData['title'] : ($existingData['title'] ?? '');
            $subtitle = isset($sectionData['subtitle']) ? $sectionData['subtitle'] : ($existingData['subtitle'] ?? '');
            $content = isset($sectionData['content']) ? $sectionData['content'] : ($existingData['content'] ?? '');
            $isActive = isset($sectionData['enabled']) ? ($sectionData['enabled'] == '1' ? 1 : 0) : ($existing ? null : 1);
            
            // Items'ı al (varsa) - tabs verisi de items içinde saklanabilir
            $items = isset($sectionData['items']) && is_array($sectionData['items']) && !empty($sectionData['items']) 
                ? $sectionData['items'] 
                : (isset($sectionData['items']) && is_array($sectionData['items']) && empty($sectionData['items'])
                    ? []  // Boş array olarak gönderilmişse temizle
                    : $existingItems);
            
            // Tabs verisi varsa items yerine tabs kullan (feature-tabs için)
            if (isset($sectionData['tabs']) && is_array($sectionData['tabs'])) {
                $items = $sectionData['tabs'];
            }
            
            // Pricing section için packages verisini items olarak kaydet
            if ($sectionId === 'pricing' && isset($sectionData['packages']) && is_array($sectionData['packages'])) {
                $items = $sectionData['packages'];
            }
            
            $itemsJson = json_encode($items, JSON_UNESCAPED_UNICODE);
            
            // Settings'i al - mevcut settings ile birleştir
            $newSettingsData = [];
            if (isset($sectionData['settings']) && is_array($sectionData['settings'])) {
                $newSettingsData = $sectionData['settings'];
            } else {
                // Eğer settings key'i yoksa, diğer alanları settings olarak kaydet
                $excludeKeys = ['title', 'subtitle', 'content', 'enabled', 'items', 'tabs', 'packages'];
                $newSettingsData = array_diff_key($sectionData, array_flip($excludeKeys));
            }
            
            // Mevcut settings ile birleştir: 
            // Yeni değerler varsa kullan, yoksa mevcut değeri koru
            // Sadece protected fields için boş değer gönderildiğinde mevcut değeri koru
            $settingsData = $existingSettings;
            
            // Korunması gereken alanlar (form_id gibi - boş string olarak gönderilse bile mevcut değeri koru)
            // Bu alanlar genellikle seçim yapılmadığında boş string olarak gönderilir ama mevcut değer korunmalı
            $protectedFields = ['form_id'];
            
            foreach ($newSettingsData as $key => $value) {
                // Korunması gereken alanlar için: Boş string veya null ise mevcut değeri koru
                if (in_array($key, $protectedFields) && ($value === '' || $value === null)) {
                    // Mevcut değeri koru, güncelleme yapma
                    continue;
                }
                
                // Null değer için mevcut değeri koru (bu alan gönderilmemiş demektir)
                if ($value === null) {
                    continue;
                }
                
                // Checkbox veya boolean alanlar için özel işleme
                if (strpos($key, 'show_') === 0 || strpos($key, 'enable_') === 0 || strpos($key, 'is_') === 0) {
                    // Boolean alanlar için false olarak kaydet (boş string veya '0' ise)
                    $settingsData[$key] = ($value === '' || $value === '0' || $value === false || $value === 'false') ? false : true;
                } else {
                    // Diğer alanlar için: Yeni değeri kaydet (boş string bile olsa)
                    // Form üzerinden gönderilen tüm değerleri kaydetmeliyiz
                    // Eğer kullanıcı form alanına değer girdiyse, o değer gönderiliyor demektir
                    $settingsData[$key] = $value;
                }
            }
            
            $settingsJson = json_encode($settingsData, JSON_UNESCAPED_UNICODE);
            
            if ($existing) {
                // Güncelle - is_active değeri null ise mevcut değeri koru
                if ($isActive === null) {
                    $stmt = $this->db->prepare("SELECT is_active FROM page_sections WHERE id = ? LIMIT 1");
                    $stmt->execute([$existing['id']]);
                    $existingActive = $stmt->fetch(PDO::FETCH_ASSOC);
                    $isActive = $existingActive['is_active'] ?? 1;
                }
                
                $stmt = $this->db->prepare("
                    UPDATE page_sections SET
                        title = ?,
                        subtitle = ?,
                        content = ?,
                        settings = ?,
                        items = ?,
                        is_active = ?
                    WHERE id = ?
                ");
                $stmt->execute([$title, $subtitle, $content, $settingsJson, $itemsJson, $isActive, $existing['id']]);
            } else {
                // Yeni ekle
                if ($isActive === null) {
                    $isActive = 1;
                }
                $stmt = $this->db->prepare("
                    INSERT INTO page_sections (theme_id, page_type, section_id, title, subtitle, content, settings, items, is_active, sort_order)
                    VALUES (?, 'home', ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $sortOrder = $this->getSectionSortOrder($sectionId);
                $stmt->execute([$themeId, $sectionId, $title, $subtitle, $content, $settingsJson, $itemsJson, $isActive, $sortOrder]);
            }
        }
    }
    
    /**
     * İletişim sayfası bölümlerini kaydet
     */
    private function saveContactSections(array $sections, int $themeId): void {
        foreach ($sections as $sectionId => $sectionData) {
            // Section var mı kontrol et (theme_id ile birlikte) - mevcut veriyi de al
            $stmt = $this->db->prepare("SELECT id, settings, items FROM page_sections WHERE theme_id = ? AND page_type = 'contact' AND section_id = ? LIMIT 1");
            $stmt->execute([$themeId, $sectionId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Mevcut settings'i yükle (varsa)
            $existingSettings = [];
            if ($existing && !empty($existing['settings'])) {
                $existingSettings = json_decode($existing['settings'], true) ?? [];
            }
            
            // Mevcut items'ı yükle (varsa)
            $existingItems = [];
            if ($existing && !empty($existing['items'])) {
                $existingItems = json_decode($existing['items'], true) ?? [];
            }
            
            // Title ve subtitle: Sadece boş değilse güncelle, boşsa mevcut değeri koru
            $stmt = $this->db->prepare("SELECT title, subtitle, content FROM page_sections WHERE theme_id = ? AND page_type = 'contact' AND section_id = ? LIMIT 1");
            $stmt->execute([$themeId, $sectionId]);
            $existingData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $title = !empty($sectionData['title']) ? $sectionData['title'] : ($existingData['title'] ?? '');
            $subtitle = isset($sectionData['subtitle']) ? $sectionData['subtitle'] : ($existingData['subtitle'] ?? '');
            $content = isset($sectionData['content']) ? $sectionData['content'] : ($existingData['content'] ?? '');
            $isActive = isset($sectionData['enabled']) ? ($sectionData['enabled'] == '1' ? 1 : 0) : ($existing ? null : 1);
            
            // Items'ı al (varsa)
            $items = isset($sectionData['items']) && is_array($sectionData['items']) && !empty($sectionData['items']) 
                ? $sectionData['items'] 
                : (isset($sectionData['items']) && is_array($sectionData['items']) && empty($sectionData['items'])
                    ? []  // Boş array olarak gönderilmişse temizle
                    : $existingItems);
            $itemsJson = json_encode($items, JSON_UNESCAPED_UNICODE);
            
            // Settings'i al - mevcut settings ile birleştir
            $newSettingsData = [];
            if (isset($sectionData['settings']) && is_array($sectionData['settings'])) {
                $newSettingsData = $sectionData['settings'];
            } else {
                $excludeKeys = ['title', 'subtitle', 'content', 'enabled', 'items'];
                $newSettingsData = array_diff_key($sectionData, array_flip($excludeKeys));
            }
            
            // Mevcut settings ile birleştir
            $settingsData = $existingSettings;
            
            // Korunması gereken alanlar (form_id gibi - boş string olarak gönderilse bile mevcut değeri koru)
            $protectedFields = ['form_id'];
            
            foreach ($newSettingsData as $key => $value) {
                // Korunması gereken alanlar için: Boş string veya null ise mevcut değeri koru
                if (in_array($key, $protectedFields) && ($value === '' || $value === null)) {
                    // Mevcut değeri koru, güncelleme yapma
                    continue;
                }
                
                // Null değer için mevcut değeri koru (bu alan gönderilmemiş demektir)
                if ($value === null) {
                    continue;
                }
                
                // Checkbox veya boolean alanlar için özel işleme
                if (strpos($key, 'show_') === 0 || strpos($key, 'enable_') === 0 || strpos($key, 'is_') === 0) {
                    // Boolean alanlar için false olarak kaydet (boş string veya '0' ise)
                    $settingsData[$key] = ($value === '' || $value === '0' || $value === false || $value === 'false') ? false : true;
                } else {
                    // Diğer alanlar için: Yeni değeri kaydet (boş string bile olsa)
                    // Form üzerinden gönderilen tüm değerleri kaydetmeliyiz
                    // Eğer kullanıcı form alanına değer girdiyse, o değer gönderiliyor demektir
                    $settingsData[$key] = $value;
                }
            }
            
            $settingsJson = json_encode($settingsData, JSON_UNESCAPED_UNICODE);
            
            if ($existing) {
                // Güncelle - is_active değeri null ise mevcut değeri koru
                if ($isActive === null) {
                    $stmt = $this->db->prepare("SELECT is_active FROM page_sections WHERE id = ? LIMIT 1");
                    $stmt->execute([$existing['id']]);
                    $existingActive = $stmt->fetch(PDO::FETCH_ASSOC);
                    $isActive = $existingActive['is_active'] ?? 1;
                }
                
                $stmt = $this->db->prepare("
                    UPDATE page_sections SET
                        title = ?,
                        subtitle = ?,
                        content = ?,
                        settings = ?,
                        items = ?,
                        is_active = ?
                    WHERE id = ?
                ");
                $stmt->execute([$title, $subtitle, $content, $settingsJson, $itemsJson, $isActive, $existing['id']]);
            } else {
                // Yeni ekle
                if ($isActive === null) {
                    $isActive = 1;
                }
                $stmt = $this->db->prepare("
                    INSERT INTO page_sections (theme_id, page_type, section_id, title, subtitle, content, settings, items, is_active, sort_order)
                    VALUES (?, 'contact', ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $sortOrder = $this->getContactSectionSortOrder($sectionId);
                $stmt->execute([$themeId, $sectionId, $title, $subtitle, $content, $settingsJson, $itemsJson, $isActive, $sortOrder]);
            }
        }
    }
    
    /**
     * İletişim sayfası section için varsayılan sıralama
     */
    private function getContactSectionSortOrder(string $sectionId): int {
        $order = [
            'hero' => 1,
            'form' => 2,
            'services' => 3,
            'why-choose-us' => 4,
            'map' => 5,
        ];
        return $order[$sectionId] ?? 99;
    }
    
    /**
     * Section için varsayılan sıralama
     */
    private function getSectionSortOrder(string $sectionId): int {
        $order = [
            'hero' => 1,
            'featured-listings' => 2,
            'consultants' => 3,
            'why-choose-us' => 4,
            'agent-profile' => 5,
            'blog-preview' => 6,
            'testimonials' => 7,
            'cta' => 8,
            'features' => 9,
            'about' => 10,
            'services' => 11,
            'pricing' => 12,
            'contact' => 13
        ];
        return $order[$sectionId] ?? 99;
    }
    
    // ==========================================
    // SECTION YÖNETİMİ
    // ==========================================
    
    /**
     * Sayfa section'larını getir
     */
    public function getPageSections(string $pageType, ?int $themeId = null): array {
        // Theme ID belirtilmemişse aktif temanın ID'sini kullan
        if ($themeId === null) {
            $activeTheme = $this->getActiveTheme();
            $themeId = $activeTheme['id'] ?? null;
        }
        
        // Theme ID yoksa boş array döndür
        if ($themeId === null) {
            return [];
        }
        
        $stmt = $this->db->prepare("
            SELECT * FROM page_sections 
            WHERE page_type = ? AND theme_id = ?
            ORDER BY sort_order ASC
        ");
        $stmt->execute([$pageType, $themeId]);
        $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($sections as &$section) {
            $section['settings'] = json_decode($section['settings'] ?? '{}', true);
            $itemsDecoded = json_decode($section['items'] ?? '[]', true);
            
            // Çeviri filter'larını uygula - module.json'da tanımlı filter isimlerini kullan
            if (function_exists('apply_filters')) {
                $sectionId = $section['section_id'] ?? '';
                
                if (!empty($section['title'])) {
                    $section['title'] = apply_filters('theme_section_title', $section['title'], $sectionId);
                }
                if (!empty($section['subtitle'])) {
                    $section['subtitle'] = apply_filters('theme_section_subtitle', $section['subtitle'], $sectionId);
                }
                if (!empty($section['content'])) {
                    $section['content'] = apply_filters('theme_section_content', $section['content'], $sectionId);
                }
                
                // Settings içindeki metinleri çevir
                if (is_array($section['settings'])) {
                    $section['settings'] = apply_filters('theme_section_settings', $section['settings'], $sectionId);
                }
                
                // Items içindeki metinleri çevir
                if (is_array($itemsDecoded)) {
                    $itemsDecoded = apply_filters('theme_section_items', $itemsDecoded, $sectionId);
                }
            }
            
            // Eğer section_id'ye göre özel işlemler
            if (($section['section_id'] ?? '') === 'feature-tabs') {
                // Tabs için özel filter
                $section['tabs'] = apply_filters('theme_section_tabs', $itemsDecoded, $sectionId);
            } elseif (($section['section_id'] ?? '') === 'pricing') {
                // Pricing section için packages olarak map et
                $section['packages'] = apply_filters('theme_section_packages', $itemsDecoded, $sectionId);
            } else {
                $section['items'] = $itemsDecoded;
            }
        }
        
        return $sections;
    }
    
    /**
     * Section kaydet veya güncelle
     */
    public function saveSection(array $data): int {
        $settings = isset($data['settings']) ? json_encode($data['settings']) : '{}';
        $items = isset($data['items']) ? json_encode($data['items']) : '[]';
        
        $theme = $this->getActiveTheme();
        $themeId = $theme ? $theme['id'] : null;
        
        if (!empty($data['id'])) {
            $stmt = $this->db->prepare("
                UPDATE page_sections SET
                    page_type = ?,
                    section_id = ?,
                    section_component = ?,
                    title = ?,
                    subtitle = ?,
                    content = ?,
                    settings = ?,
                    items = ?,
                    sort_order = ?,
                    is_active = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['page_type'],
                $data['section_id'],
                $data['section_component'] ?? null,
                $data['title'] ?? null,
                $data['subtitle'] ?? null,
                $data['content'] ?? null,
                $settings,
                $items,
                $data['sort_order'] ?? 0,
                $data['is_active'] ?? 1,
                $data['id']
            ]);
            return $data['id'];
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO page_sections (theme_id, page_type, section_id, section_component, title, subtitle, content, settings, items, sort_order, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $themeId,
                $data['page_type'],
                $data['section_id'],
                $data['section_component'] ?? null,
                $data['title'] ?? null,
                $data['subtitle'] ?? null,
                $data['content'] ?? null,
                $settings,
                $items,
                $data['sort_order'] ?? 0,
                $data['is_active'] ?? 1
            ]);
            return $this->db->lastInsertId();
        }
    }
    
    /**
     * Section sil
     */
    public function deleteSection(int $sectionId): bool {
        $stmt = $this->db->prepare("DELETE FROM page_sections WHERE id = ?");
        return $stmt->execute([$sectionId]);
    }
    
    /**
     * Section sıralamasını güncelle
     */
    public function updateSectionOrder(string $pageType, array $order): bool {
        $stmt = $this->db->prepare("UPDATE page_sections SET sort_order = ? WHERE id = ? AND page_type = ?");
        
        foreach ($order as $index => $sectionId) {
            $stmt->execute([$index, $sectionId, $pageType]);
        }
        
        return true;
    }
    
    /**
     * Sayfa ayarını getir (page_contact, page_blog vb.)
     */
    public function getPageSetting(string $pageType, string $key, $default = null) {
        $theme = $this->getActiveTheme();
        if (!$theme || !$theme['id']) {
            return $default;
        }
        
        $group = 'page_' . $pageType;
        $stmt = $this->db->prepare("SELECT option_value FROM theme_options WHERE theme_id = ? AND option_group = ? AND option_key = ?");
        $stmt->execute([$theme['id'], $group, $key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['option_value'] : $default;
    }
    
    /**
     * Sayfa tüm ayarlarını getir
     */
    public function getAllPageSettings(string $pageType): array {
        $theme = $this->getActiveTheme();
        if (!$theme || !$theme['id']) {
            return [];
        }
        
        $group = 'page_' . $pageType;
        $stmt = $this->db->prepare("SELECT option_key, option_value FROM theme_options WHERE theme_id = ? AND option_group = ?");
        $stmt->execute([$theme['id'], $group]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['option_key']] = $row['option_value'];
        }
        
        return $settings;
    }
    
    /**
     * Section'ı ID veya section_id ile getir
     */
    public function getSection(string $pageType, string $sectionId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM page_sections WHERE page_type = ? AND section_id = ? LIMIT 1");
        $stmt->execute([$pageType, $sectionId]);
        $section = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($section) {
            $section['settings'] = json_decode($section['settings'] ?? '{}', true);
            $section['items'] = json_decode($section['items'] ?? '[]', true);
        }
        
        return $section ?: null;
    }
    
    /**
     * Section görünürlüğünü güncelle
     */
    public function updateSectionVisibility(int $id, bool $isActive): bool {
        $stmt = $this->db->prepare("UPDATE page_sections SET is_active = ? WHERE id = ?");
        return $stmt->execute([$isActive ? 1 : 0, $id]);
    }
    
    // ==========================================
    // ÖZEL CSS/JS
    // ==========================================
    
    /**
     * Tema özel kodunu getir
     */
    public function getCustomCode(string $type = 'css'): string {
        $theme = $this->getActiveTheme();
        if (!$theme || !$theme['id']) {
            return '';
        }
        
        $stmt = $this->db->prepare("
            SELECT code_content FROM theme_custom_code 
            WHERE theme_id = ? AND code_type = ? AND is_active = 1
        ");
        $stmt->execute([$theme['id'], $type]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['code_content'] : '';
    }
    
    /**
     * Tema özel kodunu kaydet
     */
    public function saveCustomCode(string $type, string $content): bool {
        $theme = $this->getActiveTheme();
        if (!$theme || !$theme['id']) {
            return false;
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO theme_custom_code (theme_id, code_type, code_content)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE code_content = VALUES(code_content)
        ");
        
        return $stmt->execute([$theme['id'], $type, $content]);
    }
    
    // ==========================================
    // YARDIMCI METODLAR
    // ==========================================
    
    /**
     * Kullanılabilir fontları getir
     */
    public function getAvailableFonts(): array {
        return [
            'Zalando Sans SemiExpanded' => 'Zalando Sans SemiExpanded',
            'Inter' => 'Inter',
            'Roboto' => 'Roboto',
            'Open Sans' => 'Open Sans',
            'Lato' => 'Lato',
            'Poppins' => 'Poppins',
            'Montserrat' => 'Montserrat',
            'Nunito' => 'Nunito',
            'Source Sans Pro' => 'Source Sans Pro',
            'Raleway' => 'Raleway',
            'Playfair Display' => 'Playfair Display',
            'Merriweather' => 'Merriweather',
            'Cormorant' => 'Cormorant',
            'DM Sans' => 'DM Sans',
            'Space Grotesk' => 'Space Grotesk'
        ];
    }
    
    // ==========================================
    // TEMA EXPORT
    // ==========================================
    
    /**
     * Temayı ZIP dosyası olarak export et
     * Tema dosyaları + ayarlar + sections + özel kodlar dahil
     */
    public function exportThemeAsZip(string $slug): array {
        try {
            // Tema klasörü var mı kontrol et
            $themePath = $this->themesPath . '/' . $slug;
            if (!is_dir($themePath)) {
                return ['success' => false, 'message' => 'Tema klasörü bulunamadı'];
            }
            
            // Tema bilgilerini al
            $theme = $this->getTheme($slug);
            if (!$theme) {
                return ['success' => false, 'message' => 'Tema bilgileri alınamadı'];
            }
            
            // Geçici ZIP dosyası oluştur
            $tempFile = sys_get_temp_dir() . '/' . $slug . '_' . time() . '.zip';
            $zip = new ZipArchive();
            
            if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                return ['success' => false, 'message' => 'ZIP dosyası oluşturulamadı'];
            }
            
            // Tema klasörünü ZIP'e ekle
            $this->addDirectoryToZip($zip, $themePath, $slug);
            
            // Tema veritabanında kayıtlı mı ve ayarları var mı?
            if ($theme['id']) {
                // Tema ayarlarını export et
                $themeOptions = $this->exportThemeOptions($theme['id'], $slug);
                if (!empty($themeOptions)) {
                    $zip->addFromString($slug . '/theme_options.json', json_encode($themeOptions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
                
                // Page sections'ları export et
                $pageSections = $this->exportPageSections($theme['id']);
                if (!empty($pageSections)) {
                    $zip->addFromString($slug . '/page_sections.json', json_encode($pageSections, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
                
                // Özel kodları export et
                $customCode = $this->exportCustomCode($theme['id']);
                if (!empty($customCode)) {
                    $zip->addFromString($slug . '/custom_code.json', json_encode($customCode, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
            }
            
            // Import README dosyası ekle
            $readme = $this->generateImportReadme($slug);
            $zip->addFromString($slug . '/IMPORT_README.txt', $readme);
            
            $zip->close();
            
            return [
                'success' => true,
                'file' => $tempFile,
                'filename' => $slug . '.zip'
            ];
            
        } catch (Exception $e) {
            error_log("Theme export error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Export hatası: ' . $e->getMessage()];
        }
    }
    
    /**
     * Klasörü recursive olarak ZIP'e ekle
     */
    private function addDirectoryToZip(ZipArchive $zip, string $path, string $zipPath): void {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relativePath = $zipPath . '/' . substr($filePath, strlen($path) + 1);
            
            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($filePath, $relativePath);
            }
        }
    }
    
    /**
     * Tema ayarlarını export et
     */
    private function exportThemeOptions(int $themeId, string $slug): array {
        $stmt = $this->db->prepare("SELECT option_group, option_key, option_value FROM theme_options WHERE theme_id = ?");
        $stmt->execute([$themeId]);
        $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($options)) {
            return [];
        }
        
        return [
            'theme_id' => $themeId,
            'slug' => $slug,
            'exported_at' => date('Y-m-d H:i:s'),
            'options' => $options
        ];
    }
    
    /**
     * Page sections'ları export et
     */
    private function exportPageSections(int $themeId): array {
        $stmt = $this->db->prepare("
            SELECT page_type, section_id, section_component, title, subtitle, content, 
                   settings, items, sort_order, is_active
            FROM page_sections 
            WHERE theme_id = ?
            ORDER BY page_type, sort_order
        ");
        $stmt->execute([$themeId]);
        $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($sections)) {
            return [];
        }
        
        // JSON alanlarını decode et
        foreach ($sections as &$section) {
            $section['settings'] = json_decode($section['settings'] ?? '{}', true);
            $section['items'] = json_decode($section['items'] ?? '[]', true);
        }
        
        return [
            'theme_id' => $themeId,
            'exported_at' => date('Y-m-d H:i:s'),
            'sections' => $sections
        ];
    }
    
    /**
     * Özel kodları export et
     */
    private function exportCustomCode(int $themeId): array {
        $stmt = $this->db->prepare("
            SELECT code_type, code_content 
            FROM theme_custom_code 
            WHERE theme_id = ? AND is_active = 1
        ");
        $stmt->execute([$themeId]);
        $codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($codes)) {
            return [];
        }
        
        $result = [
            'theme_id' => $themeId,
            'exported_at' => date('Y-m-d H:i:s')
        ];
        
        foreach ($codes as $code) {
            $result[$code['code_type']] = $code['code_content'];
        }
        
        return $result;
    }
    
    /**
     * Import README dosyası oluştur
     */
    private function generateImportReadme(string $slug): string {
        $readme = "TEMA EXPORT DOSYASI\n";
        $readme .= "==================\n\n";
        $readme .= "Tema: {$slug}\n";
        $readme .= "Export Tarihi: " . date('d.m.Y H:i:s') . "\n\n";
        $readme .= "Bu ZIP dosyası aşağıdaki içerikleri barındırır:\n\n";
        $readme .= "1. Tema Dosyaları:\n";
        $readme .= "   - theme.json (tema yapılandırması)\n";
        $readme .= "   - layouts/, components/, snippets/, assets/ (tema dosyaları)\n\n";
        $readme .= "2. Veritabanı Export'ları (varsa):\n";
        $readme .= "   - theme_options.json (tema ayarları)\n";
        $readme .= "   - page_sections.json (sayfa bölümleri)\n";
        $readme .= "   - custom_code.json (özel CSS/JS kodları)\n\n";
        $readme .= "İÇE AKTARMA TALİMATLARI:\n";
        $readme .= "========================\n\n";
        $readme .= "1. Admin panelinde Temalar > Tema Yükle bölümüne gidin\n";
        $readme .= "2. Bu ZIP dosyasını yükleyin\n";
        $readme .= "3. Tema otomatik olarak kurulacaktır\n\n";
        $readme .= "NOT: Veritabanı export dosyaları (theme_options.json, page_sections.json, custom_code.json)\n";
        $readme .= "şu anda otomatik olarak içe aktarılmamaktadır. Bu dosyalar yedek amaçlıdır.\n";
        $readme .= "Gelecek güncellemelerde otomatik import özelliği eklenecektir.\n\n";
        
        return $readme;
    }
}

