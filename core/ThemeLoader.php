<?php
/**
 * ThemeLoader - Tema Dosya Yükleyici
 * Tema dosyalarını yükler, render eder ve asset URL'lerini yönetir
 */

class ThemeLoader {
    private static $instance = null;
    private $themeManager;
    private $activeTheme = null;
    private $themePath = null;
    private $themeSettings = [];
    private $cssVariables = [];
    private $previewSections = []; // Önizleme için bekleyen section değişiklikleri
    private $previewSettings = []; // Önizleme için ham ayarlar (branding vb.)
    
    private function __construct() {
        if (!class_exists('ThemeManager')) {
            require_once __DIR__ . '/ThemeManager.php';
        }
        $this->themeManager = ThemeManager::getInstance();
        $this->loadActiveTheme();
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
     * Aktif temayı yükle
     */
    private function loadActiveTheme(): void {
        $this->activeTheme = $this->themeManager->getActiveTheme();
        
        if ($this->activeTheme) {
            $this->themePath = $this->themeManager->getThemesPath() . '/' . $this->activeTheme['slug'];
            $this->themeSettings = $this->themeManager->getThemeSettings($this->activeTheme['slug']);
            $this->buildCssVariables();
            
            // functions.php varsa yükle
            $functionsFile = $this->themePath . '/functions.php';
            if (file_exists($functionsFile)) {
                require_once $functionsFile;
            }
            
            // Tema modüllerini yükle
            $this->loadThemeModules();
        }
    }
    
    /**
     * Tema modüllerini yükle
     */
    private function loadThemeModules(): void {
        if (!$this->themePath) {
            return;
        }
        
        // ModuleLoader'ı al
        if (!class_exists('ModuleLoader')) {
            require_once __DIR__ . '/ModuleLoader.php';
        }
        
        $moduleLoader = ModuleLoader::getInstance();
        $moduleLoader->loadThemeModules($this->themePath);
    }
    
    /**
     * Temayı manuel olarak yükle (önizleme için)
     */
    public function loadTheme(string $slug, array $previewSettings = []): void {
        $theme = $this->themeManager->getTheme($slug);
        
        if ($theme) {
            $this->activeTheme = $theme;
            $this->themePath = $this->themeManager->getThemesPath() . '/' . $slug;
            
            // Önizleme ayarlarını sakla (branding vb. için)
            $this->previewSettings = $previewSettings;
            
            // Önizleme ayarları varsa kullan, yoksa normal ayarları al
            if (!empty($previewSettings)) {
                $this->themeSettings = $this->mergePreviewSettings($slug, $previewSettings);
            } else {
                $this->themeSettings = $this->themeManager->getThemeSettings($slug);
            }
            
            $this->buildCssVariables();
        }
    }
    
    /**
     * Önizleme ayarlarını mevcut ayarlarla birleştir
     */
    private function mergePreviewSettings(string $slug, array $previewSettings): array {
        $settings = $this->themeManager->getThemeSettings($slug);
        
        foreach ($previewSettings as $group => $groupSettings) {
            if (is_array($groupSettings)) {
                foreach ($groupSettings as $key => $value) {
                    if (isset($settings[$group][$key])) {
                        $settings[$group][$key]['value'] = $value;
                    }
                }
            }
        }
        
        return $settings;
    }
    
    /**
     * Aktif tema var mı?
     */
    public function hasActiveTheme(): bool {
        return $this->activeTheme !== null && is_dir($this->themePath);
    }
    
    /**
     * Aktif temayı getir
     */
    public function getActiveTheme(): ?array {
        return $this->activeTheme;
    }
    
    /**
     * Tema path'ini getir
     */
    public function getThemePath(): ?string {
        return $this->themePath;
    }
    
    // ==========================================
    // DOSYA YÜKLEME
    // ==========================================
    
    /**
     * View dosyasının tema path'ini getir
     */
    public function getViewPath(string $viewName): ?string {
        if (!$this->themePath) {
            return null;
        }
        
        // frontend/ prefix'ini kaldır (controller'lar frontend/home, frontend/blog/index gibi çağırıyor)
        $cleanViewName = preg_replace('#^frontend/#', '', $viewName);
        
        // Alt klasör yapısını da düzleştir (blog/index -> blog-index veya blog.php)
        $flatViewName = str_replace('/', '-', $cleanViewName);
        $lastPart = basename($cleanViewName); // index, single, category gibi
        $parentPart = dirname($cleanViewName); // blog, agreements gibi
        
        // View adından dosya yolunu oluştur - çeşitli yapıları destekle
        $possiblePaths = [
            // 1. Temizlenmiş isim direkt olarak (home.php)
            $this->themePath . '/' . $cleanViewName . '.php',
            
            // 2. Alt klasör yapısı korunmuş (blog/index.php)
            $this->themePath . '/views/' . $cleanViewName . '.php',
            $this->themePath . '/pages/' . $cleanViewName . '.php',
            
            // 3. Düzleştirilmiş isim (blog-index.php)
            $this->themePath . '/' . $flatViewName . '.php',
            $this->themePath . '/views/' . $flatViewName . '.php',
            $this->themePath . '/pages/' . $flatViewName . '.php',
            
            // 4. Üst klasör ismi (blog.php when blog/index requested)
            $this->themePath . '/' . $parentPart . '.php',
            $this->themePath . '/views/' . $parentPart . '.php',
            $this->themePath . '/pages/' . $parentPart . '.php',
        ];
        
        // . ile başlayan yolları filtrele (dirname '.' döndürebilir)
        $possiblePaths = array_filter($possiblePaths, fn($p) => strpos($p, '/.php') === false);
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
     * Layout dosyasını getir
     */
    public function getLayout(string $name): ?string {
        if (!$this->themePath) {
            return null;
        }
        
        $layoutPath = $this->themePath . '/layouts/' . $name . '.php';
        
        if (file_exists($layoutPath)) {
            return $layoutPath;
        }
        
        return null;
    }
    
    /**
     * Temanın desteklediği layout'ları getir
     */
    public function getAvailableLayouts(): array {
        if (!$this->themePath) {
            return [];
        }
        
        $layoutsDir = $this->themePath . '/layouts';
        if (!is_dir($layoutsDir)) {
            return [];
        }
        
        $layouts = [];
        $files = scandir($layoutsDir);
        
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $name = pathinfo($file, PATHINFO_FILENAME);
                $layouts[$name] = ucfirst(str_replace(['-', '_'], ' ', $name));
            }
        }
        
        return $layouts;
    }
    
    /**
     * Component render et
     */
    public function renderComponent(string $name, array $data = []): string {
        if (!$this->themePath) {
            return "<!-- Component not available: $name -->";
        }
        
        $componentPath = $this->themePath . '/components/' . $name . '.php';
        
        if (!file_exists($componentPath)) {
            return "<!-- Component not found: $name -->";
        }
        
        // Data'yı extract et
        extract($data);
        
        // Theme settings ve helper'ları ekle
        $theme = $this->activeTheme;
        $themeSettings = $this->themeSettings;
        $themeLoader = $this;
        
        ob_start();
        include $componentPath;
        return ob_get_clean();
    }
    
    /**
     * Snippet render et
     */
    public function renderSnippet(string $name, array $data = []): string {
        if (!$this->themePath) {
            return '';
        }
        
        $snippetPath = $this->themePath . '/snippets/' . $name . '.php';
        
        if (!file_exists($snippetPath)) {
            return "<!-- Snippet not found: $name -->";
        }
        
        // Data'yı extract et
        extract($data);
        
        // Theme settings ve helper'ları ekle
        $theme = $this->activeTheme;
        $themeSettings = $this->themeSettings;
        $themeLoader = $this;
        
        ob_start();
        include $snippetPath;
        return ob_get_clean();
    }
    
    /**
     * Section render et
     */
    public function renderSection(array $section): string {
        $componentName = $section['section_component'] ?? $section['section_id'];
        
        return $this->renderComponent($componentName, [
            'section' => $section,
            'title' => $section['title'] ?? '',
            'subtitle' => $section['subtitle'] ?? '',
            'content' => $section['content'] ?? '',
            'settings' => $section['settings'] ?? [],
            'items' => $section['items'] ?? []
        ]);
    }
    
    // ==========================================
    // ASSET YÖNETİMİ
    // ==========================================
    
    /**
     * Tema asset URL'si
     */
    public function getAssetUrl(string $path): string {
        if (!$this->activeTheme) {
            return '';
        }
        
        $baseUrl = $this->getSiteUrl();
        return $baseUrl . 'themes/' . $this->activeTheme['slug'] . '/assets/' . ltrim($path, '/');
    }
    
    /**
     * Tema CSS dosyası URL'si
     */
    public function getCssUrl(string $filename = 'theme.css'): string {
        return $this->getAssetUrl('css/' . $filename);
    }
    
    /**
     * Tema JS dosyası URL'si
     */
    public function getJsUrl(string $filename = 'theme.js'): string {
        return $this->getAssetUrl('js/' . $filename);
    }
    
    /**
     * Tema görseli URL'si
     */
    public function getImageUrl(string $filename): string {
        return $this->getAssetUrl('images/' . $filename);
    }
    
    /**
     * Site URL helper
     */
    private function getSiteUrl(): string {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $base = str_replace(basename($scriptName), '', $scriptName);
        if (strpos($scriptName, 'public/') !== false) {
            $base = str_replace('public/', '', $base);
        }
        return $protocol . $host . $base;
    }
    
    // ==========================================
    // CSS DEĞİŞKENLERİ
    // ==========================================
    
    /**
     * CSS değişkenlerini oluştur
     */
    private function buildCssVariables(): void {
        $this->cssVariables = [];
        
        // Renk değişkenleri
        if (isset($this->themeSettings['colors'])) {
            foreach ($this->themeSettings['colors'] as $key => $config) {
                $value = $config['value'] ?? $config['default'] ?? '';
                if ($value) {
                    $this->cssVariables["--color-{$key}"] = $value;
                    
                    // RGB versiyonunu da ekle (opacity için)
                    $rgb = $this->hexToRgb($value);
                    if ($rgb) {
                        $this->cssVariables["--color-{$key}-rgb"] = "{$rgb['r']}, {$rgb['g']}, {$rgb['b']}";
                    }
                }
            }
        }
        
        // Font değişkenleri
        if (isset($this->themeSettings['fonts'])) {
            foreach ($this->themeSettings['fonts'] as $key => $config) {
                $value = $config['value'] ?? $config['default'] ?? '';
                if ($value) {
                    $this->cssVariables["--font-{$key}"] = "'{$value}', sans-serif";
                }
            }
        }
    }
    
    /**
     * CSS değişkenlerini string olarak getir
     */
    public function getCssVariables(): string {
        if (empty($this->cssVariables)) {
            return '';
        }
        
        $css = ":root {\n";
        foreach ($this->cssVariables as $var => $value) {
            $css .= "    {$var}: {$value};\n";
        }
        $css .= "}\n";
        
        return $css;
    }
    
    /**
     * CSS değişkenlerini style tag içinde getir
     */
    public function getCssVariablesTag(): string {
        $css = $this->getCssVariables();
        if (!$css) {
            return '';
        }
        
        return "<style id=\"theme-variables\">\n{$css}</style>\n";
    }
    
    /**
     * Özel CSS'i getir
     */
    public function getCustomCss(): string {
        return $this->themeManager->getCustomCode('css');
    }
    
    /**
     * Özel JS'i getir
     */
    public function getCustomJs(): string {
        return $this->themeManager->getCustomCode('js');
    }
    
    /**
     * Hex rengi RGB'ye çevir
     */
    private function hexToRgb(string $hex): ?array {
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        if (strlen($hex) !== 6) {
            return null;
        }
        
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        ];
    }
    
    // ==========================================
    // TEMA AYARLARI ERİŞİMİ
    // ==========================================
    
    /**
     * Tema ayarını getir
     */
    public function getSetting(string $key, $default = null, ?string $group = null) {
        if ($group && isset($this->themeSettings[$group][$key])) {
            return $this->themeSettings[$group][$key]['value'] ?? $default;
        }
        
        // Tüm grupları tara
        foreach ($this->themeSettings as $g => $settings) {
            if (isset($settings[$key])) {
                return $settings[$key]['value'] ?? $default;
            }
        }
        
        return $default;
    }
    
    /**
     * Renk ayarını getir
     */
    public function getColor(string $key, string $default = '#000000'): string {
        return $this->getSetting($key, $default, 'colors');
    }
    
    /**
     * Font ayarını getir
     */
    public function getFont(string $key, string $default = 'Inter'): string {
        return $this->getSetting($key, $default, 'fonts');
    }
    
    /**
     * Özel ayarı getir
     */
    public function getCustomSetting(string $key, $default = null) {
        // Footer ayarları için footer grubunu da kontrol et
        if (strpos($key, 'footer_') === 0) {
            $value = $this->getSetting($key, null, 'footer');
            if ($value !== null) {
                return $value;
            }
        }
        return $this->getSetting($key, $default, 'custom');
    }
    
    /**
     * Branding ayarını getir (logo, favicon)
     */
    public function getBranding(string $key, $default = null) {
        // Önce önizleme ayarlarından kontrol et (preview modunda)
        if (isset($this->previewSettings['branding'][$key]) && $this->previewSettings['branding'][$key] !== '') {
            return $this->previewSettings['branding'][$key];
        }
        
        // Sonra tema ayarlarından kontrol et
        $value = $this->getSetting($key, null, 'branding');
        
        // Tema ayarlarında yoksa ThemeManager'dan al
        if (empty($value)) {
            $value = $this->themeManager->getThemeOption($key, null, 'branding');
        }
        
        // Hala yoksa global ayarlardan al
        if (empty($value)) {
            $value = get_option($key, $default);
        }
        
        return $value;
    }
    
    /**
     * Site logosu URL'sini getir
     */
    public function getLogo(): ?string {
        return $this->getBranding('site_logo');
    }
    
    /**
     * Favicon URL'sini getir  
     */
    public function getFavicon(): ?string {
        return $this->getBranding('site_favicon');
    }
    
    /**
     * Tüm tema ayarlarını getir
     */
    public function getAllSettings(): array {
        return $this->themeSettings;
    }
    
    /**
     * Sayfa ayarını getir (contact, blog vb. sayfalar için)
     */
    public function getPageSetting(string $pageType, string $key, $default = null) {
        $group = 'page_' . $pageType;
        
        // Önce önizleme ayarlarından kontrol et
        if (isset($this->previewSettings[$group][$key])) {
            return $this->previewSettings[$group][$key];
        }
        
        // Sonra veritabanından al
        return $this->themeManager->getPageSetting($pageType, $key, $default);
    }
    
    /**
     * Sayfa tüm ayarlarını getir
     */
    public function getAllPageSettings(string $pageType): array {
        $group = 'page_' . $pageType;
        
        // Önce veritabanından al
        $settings = $this->themeManager->getAllPageSettings($pageType);
        
        // Önizleme ayarlarını üzerine yaz
        if (isset($this->previewSettings[$group]) && is_array($this->previewSettings[$group])) {
            foreach ($this->previewSettings[$group] as $key => $value) {
                $settings[$key] = $value;
            }
        }
        
        return $settings;
    }
    
    /**
     * Önizleme section'larını ayarla
     */
    public function setPreviewSections(array $sections): void {
        $this->previewSections = $sections;
    }
    
    /**
     * Önizleme section'larını getir
     */
    public function getPreviewSections(): array {
        return $this->previewSections;
    }
    
    /**
     * Section verisini getir (önizleme varsa onu kullan)
     */
    public function getSection(string $pageType, string $sectionId): ?array {
        // Önce önizleme sections'ında ara
        $key = $pageType . '_' . $sectionId;
        if (isset($this->previewSections[$key])) {
            return $this->previewSections[$key];
        }
        
        return $this->themeManager->getSection($pageType, $sectionId);
    }
    
    /**
     * Sayfa section'larını getir (veritabanından + önizleme)
     */
    public function getPageSections(string $pageType): array {
        // Veritabanından section'ları al
        $dbSections = $this->themeManager->getPageSections($pageType);
        
        // Önizleme sections yoksa direkt döndür
        if (empty($this->previewSections)) {
            return $dbSections;
        }
        
        // Veritabanı section'larını map'e çevir
        $sectionsMap = [];
        foreach ($dbSections as $section) {
            $sectionsMap[$section['section_id']] = $section;
        }
        
        // Önizleme sections'larını üzerine yaz
        foreach ($this->previewSections as $key => $previewSection) {
            // Key format: pageType_sectionId
            if (strpos($key, $pageType . '_') === 0) {
                $sectionId = $previewSection['section_id'];
                $sectionsMap[$sectionId] = $previewSection;
            }
        }
        
        return array_values($sectionsMap);
    }
    
    // ==========================================
    // HEAD VE FOOTER ÇIKTILARI
    // ==========================================
    
    /**
     * Head için gerekli tüm tema çıktılarını getir
     */
    public function getHeadOutput(): string {
        $output = '';
        
        // CSS Variables
        $output .= $this->getCssVariablesTag();
        
        // Theme CSS
        $themeCss = $this->themePath . '/assets/css/theme.css';
        if (file_exists($themeCss)) {
            $output .= '<link rel="stylesheet" href="' . $this->getCssUrl() . '">' . "\n";
        }
        
        // Custom CSS
        $customCss = $this->getCustomCss();
        if ($customCss) {
            $output .= "<style id=\"custom-css\">\n{$customCss}\n</style>\n";
        }
        
        // Head code
        $headCode = $this->themeManager->getCustomCode('head');
        if ($headCode) {
            $output .= $headCode . "\n";
        }
        
        return $output;
    }
    
    /**
     * Footer için gerekli tüm tema çıktılarını getir
     */
    public function getFooterOutput(): string {
        $output = '';
        
        // Theme JS
        $themeJs = $this->themePath . '/assets/js/theme.js';
        if (file_exists($themeJs)) {
            $output .= '<script src="' . $this->getJsUrl() . '"></script>' . "\n";
        }
        
        // Custom JS
        $customJs = $this->getCustomJs();
        if ($customJs) {
            $output .= "<script>\n{$customJs}\n</script>\n";
        }
        
        // Footer code
        $footerCode = $this->themeManager->getCustomCode('footer');
        if ($footerCode) {
            $output .= $footerCode . "\n";
        }
        
        return $output;
    }
    
}

