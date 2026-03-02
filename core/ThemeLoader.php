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
        
        // Çeviri filter'larını uygula
        $title = $section['title'] ?? '';
        $subtitle = $section['subtitle'] ?? '';
        $content = $section['content'] ?? '';
        $description = $section['description'] ?? '';
        $settings = $section['settings'] ?? [];
        $items = $section['items'] ?? [];
        $tabs = $section['tabs'] ?? [];
        $packages = $section['packages'] ?? [];
        $features = $section['features'] ?? [];
        
        if (function_exists('apply_filters')) {
            if (!empty($title)) {
                $title = apply_filters('page_title', $title);
            }
            if (!empty($subtitle)) {
                $subtitle = apply_filters('page_title', $subtitle);
            }
            if (!empty($content)) {
                $content = apply_filters('page_content', $content);
            }
            if (!empty($description)) {
                $description = apply_filters('page_content', $description);
            }
            
            // Settings içindeki metin alanlarını çevir
            if (is_array($settings)) {
                $settings = $this->translateSettings($settings);
            }
            
            // Items içindeki metin alanlarını çevir (glowing-features vb.)
            if (is_array($items)) {
                $items = $this->translateItems($items);
            }
            
            // Tabs içindeki metin alanlarını çevir (feature-tabs için)
            if (is_array($tabs)) {
                $tabs = $this->translateItems($tabs);
            }
            
            // Packages içindeki metin alanlarını çevir (pricing için)
            if (is_array($packages)) {
                $packages = $this->translateItems($packages);
            }
            
            // Features içindeki metin alanlarını çevir (dashboard-showcase için)
            if (is_array($features)) {
                foreach ($features as &$feature) {
                    if (is_string($feature) && !empty($feature) && strlen($feature) > 2) {
                        $feature = apply_filters('page_title', $feature);
                    }
                }
                unset($feature);
            }
        }
        
        // Section'ı güncellenmiş değerlerle güncelle
        $section['title'] = $title;
        $section['subtitle'] = $subtitle;
        $section['content'] = $content;
        $section['description'] = $description;
        $section['settings'] = $settings;
        $section['items'] = $items;
        $section['tabs'] = $tabs;
        $section['packages'] = $packages;
        $section['features'] = $features;
        
        return $this->renderComponent($componentName, [
            'section' => $section,
            'title' => $title,
            'subtitle' => $subtitle,
            'content' => $content,
            'description' => $description,
            'settings' => $settings,
            'items' => $items,
            'tabs' => $tabs,
            'packages' => $packages,
            'features' => $features
        ]);
    }
    
    /**
     * Settings array'indeki metin alanlarını çevir
     * Tüm section component'lerinde kullanılan metin alanlarını kapsar
     */
    private function translateSettings(array $settings): array {
        if (!function_exists('apply_filters')) {
            return $settings;
        }
        
        // Çevirilecek metin key'leri - TÜM SECTION COMPONENT'LERİNDEN
        $textKeys = [
            // Genel
            'title', 'subtitle', 'description', 'content', 'text', 'name', 'label',
            'heading', 'subheading', 'badge', 'caption', 'message',
            // Butonlar
            'button_text', 'buttonText', 'secondary_button_text', 'top_button_text', 
            'link_text', 'submit_button_text', 'back_text',
            // Form alanları
            'placeholder', 'help_text', 'success_message', 'error_message',
            // Hero özel
            'title_prefix',
            // Tabs özel
            'imageAlt',
            // Footer
            'copyright_text', 'copyright_company', 'copyright_custom', 'back_to_top_text'
        ];
        
        // Çevirilmeyecek key'ler
        // NOT: animated_words artık çevriliyor (virgülle ayrılmış kelimeler)
        $noTranslateKeys = [
            'icon', 'image', 'link', 'url', 'href', 'src', 'color', 'gradient',
            'style', 'class', 'id', 'enabled', 'active', 'visible', 'show',
            'button_link', 'secondary_button_link', 'top_button_link',
            'period', 'price', 'popular', // 'value' artık çevriliyor (uzun metinler için)
            'font', 'size', 'width', 'height', 'padding', 'margin',
            'type', 'format', 'default', 'options', 'required',
            'top_button_style', 'top_button_icon', 'reverse_layout'
        ];
        
        foreach ($settings as $key => $value) {
            // Özel durum: animated_words - virgülle ayrılmış kelimeleri çevir
            if ($key === 'animated_words' && is_string($value) && !empty($value)) {
                $words = array_map('trim', explode(',', $value));
                $translatedWords = array_map(function($word) {
                    if (function_exists('apply_filters') && !empty($word) && strlen($word) > 2) {
                        return apply_filters('page_title', $word);
                    }
                    return $word;
                }, $words);
                $settings[$key] = implode(',', $translatedWords);
                continue;
            }
            
            // Çevirilmeyecek key'leri atla
            $shouldSkip = false;
            foreach ($noTranslateKeys as $noTransKey) {
                if (strpos($key, $noTransKey) !== false || $key === $noTransKey) {
                    $shouldSkip = true;
                    break;
                }
            }
            if ($shouldSkip) continue;
            
            if (is_string($value) && !empty($value) && strlen($value) > 2) {
                // Metin key'i mi kontrol et
                $isTextKey = false;
                foreach ($textKeys as $textKey) {
                    if ($key === $textKey || strpos($key, $textKey) !== false ||
                        strpos($key, 'Text') !== false || strpos($key, '_text') !== false ||
                        strpos($key, 'title') !== false || strpos($key, 'description') !== false ||
                        strpos($key, 'label') !== false || strpos($key, 'badge') !== false ||
                        strpos($key, 'heading') !== false || strpos($key, 'subtitle') !== false) {
                        $isTextKey = true;
                        break;
                    }
                }
                
                if ($isTextKey) {
                    if (strlen($value) > 100) {
                        $settings[$key] = apply_filters('page_content', $value);
                    } else {
                        $settings[$key] = apply_filters('page_title', $value);
                    }
                }
            } elseif (is_array($value)) {
                // Nested array için recursive çağrı
                $settings[$key] = $this->translateSettings($value);
            }
        }
        
        return $settings;
    }
    
    /**
     * Items array'indeki metin alanlarını çevir
     * Pricing packages, feature tabs, glowing features items vb. için
     */
    private function translateItems(array $items): array {
        if (!function_exists('apply_filters')) {
            return $items;
        }
        
        foreach ($items as &$item) {
            if (is_array($item)) {
                // Her item için settings çevirisini kullan
                $item = $this->translateSettings($item);
                
                // Features array'i varsa (pricing packages için)
                if (isset($item['features']) && is_array($item['features'])) {
                    foreach ($item['features'] as &$feature) {
                        if (is_string($feature) && !empty($feature) && strlen($feature) > 2) {
                            $feature = apply_filters('page_content', $feature);
                        }
                    }
                    unset($feature);
                }
                
                // Content objesi varsa (feature tabs için)
                if (isset($item['content']) && is_array($item['content'])) {
                    $item['content'] = $this->translateSettings($item['content']);
                }
                
                // Options array'i varsa (form fields için)
                if (isset($item['options']) && is_array($item['options'])) {
                    foreach ($item['options'] as &$option) {
                        if (is_array($option)) {
                            if (!empty($option['label']) && is_string($option['label'])) {
                                $option['label'] = apply_filters('page_title', $option['label']);
                            }
                        } elseif (is_string($option) && !empty($option) && strlen($option) > 2) {
                            $option = apply_filters('page_title', $option);
                        }
                    }
                    unset($option);
                }
                
                // Tabs array'i varsa (feature tabs için)
                if (isset($item['tabs']) && is_array($item['tabs'])) {
                    $item['tabs'] = $this->translateItems($item['tabs']);
                }
                
                // Packages array'i varsa (pricing için)
                if (isset($item['packages']) && is_array($item['packages'])) {
                    $item['packages'] = $this->translateItems($item['packages']);
                }
            } elseif (is_string($item) && !empty($item) && strlen($item) > 2) {
                $item = apply_filters('page_title', $item);
            }
        }
        unset($item);
        
        return $items;
    }
    
    // ==========================================
    // ASSET YÖNETİMİ
    // ==========================================
    
    /**
     * Tema asset URL'si
     * Asset URL'leri (CSS, JS, images) her zaman root'tan servis edilir, dil prefix'i olmadan
     */
    public function getAssetUrl(string $path): string {
        if (!$this->activeTheme) {
            return '';
        }
        
        // Asset URL'leri için dil prefix'i olmadan base URL al
        $baseUrl = $this->getSiteUrl(false);
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
     * 
     * @param bool $includeLangPrefix Asset URL'leri için false, sayfa URL'leri için true
     */
    private function getSiteUrl(bool $includeLangPrefix = true): string {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $base = str_replace(basename($scriptName), '', $scriptName);
        if (strpos($scriptName, 'public/') !== false) {
            $base = str_replace('public/', '', $base);
        }
        
        // Translation modülü aktifse ve dil prefix'i isteniyorsa ekle
        // Asset URL'leri (CSS, JS, images) için dil prefix'i eklenmez
        $langPrefix = '';
        if ($includeLangPrefix && class_exists('ModuleLoader')) {
            $moduleLoader = ModuleLoader::getInstance();
            // Modülün aktif olup olmadığını kontrol et
            $translationModule = $moduleLoader->getModule('translation');
            if ($translationModule && ($translationModule['is_active'] ?? false)) {
                $translationController = $moduleLoader->getModuleController('translation');
                if ($translationController) {
                    $currentLanguage = $translationController->getCurrentLanguage();
                    $defaultLanguage = get_module_setting('translation', 'default_language', 'tr');
                    if ($currentLanguage !== $defaultLanguage) {
                        $langPrefix = '/' . $currentLanguage;
                    }
                }
            }
        }
        
        return $protocol . $host . $base . $langPrefix;
    }
    
    /**
     * URL'i dil prefix'i ile döndür
     * Component'lerde link oluştururken kullanılır
     * 
     * @param string $path URL path'i (örn: '/contact', '/blog')
     * @param string|null $lang Hedef dil kodu (null ise mevcut dil kullanılır)
     * @return string Dil prefix'li URL
     */
    public function getLocalizedUrl(string $path = '', ?string $lang = null): string {
        // localized_url fonksiyonu varsa kullan
        if (function_exists('localized_url')) {
            return localized_url($path, $lang);
        }
        
        // Fallback: getSiteUrl kullan
        return $this->getSiteUrl(true) . ($path ? '/' . ltrim($path, '/') : '');
    }
    
    // ==========================================
    // CSS DEĞİŞKENLERİ
    // ==========================================
    
    /**
     * CSS değişkenlerini oluştur
     */
    private function buildCssVariables($forceReload = false): void {
        // Ayarları yeniden yükle (güncel değerleri almak için)
        if ($forceReload && $this->activeTheme && $this->activeTheme['slug']) {
            $this->themeSettings = $this->themeManager->getThemeSettings($this->activeTheme['slug']);
        }
        
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
        // Her çağrıda ayarları yeniden yükle (güncel font/renk değerleri için)
        $this->buildCssVariables(true);
        
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
     * Tema ayarlarını yeniden yükle (customize'dan sonra kullanılır)
     */
    public function refreshSettings(): void {
        if ($this->activeTheme) {
            $this->themeSettings = $this->themeManager->getThemeSettings($this->activeTheme['slug']);
            $this->buildCssVariables();
        }
    }
    
    /**
     * Tema ayarını getir
     */
    public function getSetting(string $key, $default = null, ?string $group = null) {
        // Her çağrıda güncel ayarları kontrol et (cache sorunlarını önlemek için)
        // Ancak performans için sadece aktif temayı kontrol et
        if ($this->activeTheme) {
            // Eğer ayar bulunamazsa, veritabanından direkt oku
            $value = null;
            
            if ($group && isset($this->themeSettings[$group][$key])) {
                $value = $this->themeSettings[$group][$key]['value'] ?? null;
            } else {
                // Tüm grupları tara
                foreach ($this->themeSettings as $g => $settings) {
                    if (isset($settings[$key])) {
                        $value = $settings[$key]['value'] ?? null;
                        break;
                    }
                }
            }
            
            // Eğer hala bulunamadıysa, veritabanından direkt oku
            if ($value === null) {
                $value = $this->themeManager->getThemeOption($key, null, $group);
            }
            
            if ($value === null) {
                $value = $default;
            }
        } else {
            $value = $default;
        }
        
        // Test iletişim sayfası linkini iletişim linkine yönlendir (talep_link, cta_link vb.)
        if (is_string($value) && strpos($value, 'test-iletisim-sayfasi') !== false) {
            $value = '/contact';
        }
        
        // Çevirilmemesi gereken ayarlar (URL'ler, teknik değerler, virgüllü listeler vb.)
        // NOT: animated_words artık çevriliyor (virgülle ayrılmış kelimeler)
        $noTranslateKeys = [
            'button_link', 'secondary_button_link', 'top_button_link',
            'link', 'url', 'href', 'src', 'image', 'logo', 'favicon', 'icon',
            'color', 'font', 'size', 'width', 'height', 'padding', 'margin',
            'style', 'class', 'id', 'enabled', 'active', 'visible', 'show',
            'top_button_style', 'top_button_icon', 'reverse_layout', 'gradient',
            'period', 'price', 'popular' // Fiyatlandırma için (features çevriliyor)
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
        // filter_title kullanarak çeviri modülünün veritabanından çeviriyi almasını sağla
        // NOT: strlen($value) > 2 kontrolü kaldırıldı - tüm metinler çevrilmeli
        if ($shouldTranslate && function_exists('apply_filters') && is_string($value) && !empty($value) && strlen($value) > 1) {
            // Kısa veya uzun metne göre uygun filter kullan
            $originalValue = $value;
            
            // Filter'ların kayıtlı olup olmadığını kontrol et
            $hasTitleFilter = function_exists('has_filter') ? has_filter('page_title') : false;
            $hasContentFilter = function_exists('has_filter') ? has_filter('page_content') : false;
            
            if ($hasTitleFilter || $hasContentFilter) {
                // animated_words için özel işlem: virgülle ayrılmış kelimeleri ayrı ayrı çevir
                if ($key === 'animated_words' && strpos($value, ',') !== false) {
                    $words = array_map('trim', explode(',', $value));
                    $translatedWords = [];
                    foreach ($words as $word) {
                        if (!empty($word)) {
                            $translatedWord = apply_filters('page_title', $word);
                            $translatedWords[] = $translatedWord;
                        }
                    }
                    $value = implode(',', $translatedWords);
                } else {
                    if (strlen($value) > 100) {
                        $value = apply_filters('page_content', $value);
                    } else {
                        $value = apply_filters('page_title', $value);
                    }
                }
            }
        }
        
        return $value;
    }
    
    /**
     * Renk ayarını getir
     */
    public function getColor(string $key, string $default = '#000000'): string {
        return $this->getSetting($key, $default, 'colors');
    }
    
    /**
     * Ana rengi getir
     */
    public function getPrimaryColor(): ?string {
        $color = $this->getColor('primary', '#137fec');
        return !empty($color) ? $color : null;
    }
    
    /**
     * İkincil rengi getir
     */
    public function getSecondaryColor(): ?string {
        $color = $this->getColor('secondary', '#6366f1');
        return !empty($color) ? $color : null;
    }
    
    /**
     * Font ayarını getir
     */
    public function getFont(string $key, string $default = 'Zalando Sans SemiExpanded'): string {
        // Ayarları yeniden yükle (güncel font değerleri için)
        if ($this->activeTheme && $this->activeTheme['slug']) {
            $this->themeSettings = $this->themeManager->getThemeSettings($this->activeTheme['slug']);
        }
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
     * Aktif tema varken sadece o temanın ayarları kullanılır; global fallback yapılmaz
     * böylece bir temanın favicon/logo'su diğer temalara taşmaz.
     */
    public function getBranding(string $key, $default = null) {
        // Önce önizleme ayarlarından kontrol et (preview modunda)
        if (isset($this->previewSettings['branding'][$key]) && $this->previewSettings['branding'][$key] !== '') {
            return $this->previewSettings['branding'][$key];
        }
        
        // Önce aktif temanın ayarlarından al (ThemeManager üzerinden - doğru tema ID ile)
        $value = $this->themeManager->getThemeOption($key, null, 'branding');
        
        // Tema ayarlarında yoksa themeSettings'den kontrol et
        if (empty($value)) {
            $value = $this->getSetting($key, null, 'branding');
        }
        
        // Logo ve favicon: tema ayarında yoksa global ayara düş (footer/header aynı logoyu göstersin)
        $themeOnlyKeys = ['site_favicon', 'site_logo', 'logo_width', 'logo_height'];
        if (in_array($key, $themeOnlyKeys, true) && $this->activeTheme !== null && !empty($value)) {
            return $value;
        }
        if (in_array($key, $themeOnlyKeys, true) && $this->activeTheme !== null) {
            $global = get_option($key, $default);
            if ($global !== '' && $global !== null) {
                return $global;
            }
            return $default;
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
     * Logo genişliğini getir (CLS için)
     */
    public function getLogoWidth(): ?int {
        $width = $this->getBranding('logo_width');
        return $width ? (int)$width : null;
    }
    
    /**
     * Logo yüksekliğini getir (CLS için)
     */
    public function getLogoHeight(): ?int {
        $height = $this->getBranding('logo_height');
        return $height ? (int)$height : null;
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
        // Aktif temanın ID'sini al
        $themeId = $this->activeTheme['id'] ?? null;
        
        // Veritabanından section'ları al (theme_id ile)
        $dbSections = $this->themeManager->getPageSections($pageType, $themeId);
        
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

