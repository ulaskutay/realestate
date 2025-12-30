<?php
/**
 * View Renderer
 * PHP-based component system - Tema yapısına uygun
 */

class ViewRenderer {
    private static $instance = null;
    private $basePath;
    private $layout = null;
    private $sections = [];
    private $currentSection = null;
    private $themeLoader = null;
    private $useTheme = true;
    
    private function __construct() {
        $this->basePath = __DIR__ . '/../app/views';
        $this->initThemeLoader();
    }
    
    /**
     * Singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Theme loader'ı başlat
     */
    private function initThemeLoader(): void {
        try {
            if (!class_exists('ThemeLoader')) {
                $loaderFile = __DIR__ . '/ThemeLoader.php';
                if (file_exists($loaderFile)) {
                    require_once $loaderFile;
                }
            }
            
            if (class_exists('ThemeLoader')) {
                $this->themeLoader = ThemeLoader::getInstance();
            }
        } catch (Exception $e) {
            error_log("ThemeLoader init error: " . $e->getMessage());
            $this->themeLoader = null;
        }
    }
    
    /**
     * Theme loader'ı getir
     */
    public function getThemeLoader(): ?ThemeLoader {
        return $this->themeLoader;
    }
    
    /**
     * Tema kullanımını aç/kapat
     */
    public function setUseTheme(bool $use): void {
        $this->useTheme = $use;
    }
    
    /**
     * Aktif tema var mı?
     */
    public function hasActiveTheme(): bool {
        return $this->useTheme && $this->themeLoader && $this->themeLoader->hasActiveTheme();
    }
    
    /**
     * View render eder
     */
    public function render($viewName, $data = []) {
        // Tema aktifse ve view tema'da varsa, tema dosyasını kullan
        $viewPath = $this->resolveViewPath($viewName);
        
        if (!$viewPath || !file_exists($viewPath)) {
            die("View dosyası bulunamadı: {$viewName}");
        }
        
        // Data'yı extract et
        extract($data);
        
        // ViewRenderer instance'ını data'ya ekle (helper functions için)
        $renderer = $this;
        
        // ThemeLoader'ı da ekle
        $themeLoader = $this->themeLoader;
        
        // Layout varsa, content'i yakala
        if ($this->layout) {
            // View'dan content'i yakala
            ob_start();
            require $viewPath;
            $viewOutput = ob_get_clean();
            
            // Shortcode'ları parse et
            $viewOutput = $this->parseShortcodes($viewOutput);
            
            // Sections'ı varsayılan değerlerle başlat
            if (!isset($sections)) {
                $sections = [];
            }
            
            // ViewRenderer'ın kendi sections'ını birleştir (view'dan gelen sections ile)
            if (!empty($this->sections)) {
                $sections = array_merge($sections, $this->sections);
            }
            
            // Tema styles section'ını ekle
            if ($this->hasActiveTheme()) {
                $themeStyles = $this->themeLoader->getHeadOutput();
                if (!empty($themeStyles)) {
                    $sections['styles'] = ($sections['styles'] ?? '') . $themeStyles;
                }
                
                $themeScripts = $this->themeLoader->getFooterOutput();
                if (!empty($themeScripts)) {
                    $sections['scripts'] = ($sections['scripts'] ?? '') . $themeScripts;
                }
            }
            
            // View içinde $content veya $sections tanımlanmış olabilir
            if (!isset($sections['content'])) {
                if (isset($content)) {
                    $sections['content'] = $content;
                } else {
                    $sections['content'] = $viewOutput;
                }
            }
            
            // $content değişkenini de ayarla (layout'ta kullanılabilir)
            $content = $sections['content'] ?? $viewOutput;
            
            // Hook: İçerik render öncesi
            if (function_exists('apply_filters')) {
                $content = apply_filters('the_content', $content);
            }
            
            // Layout'u render et
            $layoutPath = $this->resolveLayoutPath($this->layout);
            
            if ($layoutPath && file_exists($layoutPath)) {
                // Layout'a tüm değişkenleri aktar (data, sections, content, themeLoader, renderer)
                $layoutData = array_merge($data, [
                    'sections' => $sections, 
                    'content' => $content ?? '',
                    'themeLoader' => $this->themeLoader,
                    'renderer' => $this,
                    'current_page' => $data['current_page'] ?? ''
                ]);
                extract($layoutData);
                require $layoutPath;
            } else {
                echo $content ?? $viewOutput;
            }
        } else {
            ob_start();
            require $viewPath;
            $output = ob_get_clean();
            
            // Shortcode'ları parse et
            $output = $this->parseShortcodes($output);
            
            echo $output;
        }
    }
    
    /**
     * View dosya yolunu çözümle (tema öncelikli)
     */
    private function resolveViewPath(string $viewName): ?string {
        // Tema aktifse, önce tema dosyalarında ara
        if ($this->hasActiveTheme()) {
            $themePath = $this->themeLoader->getViewPath($viewName);
            if ($themePath && file_exists($themePath)) {
                // Tema view'ı bulundu
                return $themePath;
            }
        }
        
        // Fallback: varsayılan dizinler
        // viewName "frontend/home" gibi geliyorsa
        if (strpos($viewName, 'frontend/') === 0) {
            // frontend/ ile başlıyorsa, direkt yolu kontrol et
            $frontendPath = $this->basePath . '/' . $viewName . '.php';
            if (file_exists($frontendPath)) {
                return $frontendPath;
            }
        }
        
        // Öncelikle frontend altında ara (viewName "home" gibi ise)
        $frontendPath = $this->basePath . '/frontend/' . $viewName . '.php';
        if (file_exists($frontendPath)) {
            return $frontendPath;
        }
        
        // Son olarak basePath altında ara
        $defaultPath = $this->basePath . '/' . $viewName . '.php';
        if (file_exists($defaultPath)) {
            return $defaultPath;
        }
        
        return null;
    }
    
    /**
     * Layout dosya yolunu çözümle (tema öncelikli)
     */
    private function resolveLayoutPath(string $layoutName): ?string {
        // Tema aktifse, önce tema layout'larında ara
        if ($this->hasActiveTheme()) {
            $themeLayout = $this->themeLoader->getLayout($layoutName);
            if ($themeLayout && file_exists($themeLayout)) {
                return $themeLayout;
            }
        }
        
        // Fallback: varsayılan dizinler
        $layoutPath = $this->basePath . '/frontend/layouts/' . $layoutName . '.php';
        if (file_exists($layoutPath)) {
            return $layoutPath;
        }
        
        $layoutPath = $this->basePath . '/layouts/' . $layoutName . '.php';
        if (file_exists($layoutPath)) {
            return $layoutPath;
        }
        
        return null;
    }
    
    /**
     * Component include eder
     */
    public function component($componentName, $data = []) {
        // Tema aktifse, önce tema component'lerinde ara
        if ($this->hasActiveTheme()) {
            $output = $this->themeLoader->renderComponent($componentName, $data);
            if (strpos($output, '<!-- Component not found') === false) {
                echo $output;
                return;
            }
        }
        
        // Fallback: varsayılan dizinler
        $componentPath = $this->basePath . '/frontend/components/' . $componentName . '.php';
        if (!file_exists($componentPath)) {
            $componentPath = $this->basePath . '/components/' . $componentName . '.php';
        }
        
        if (!file_exists($componentPath)) {
            echo "<!-- Component bulunamadı: {$componentName} -->";
            return;
        }
        
        // Mevcut değişkenleri koru
        $currentVars = get_defined_vars();
        extract($data);
        // ViewRenderer instance'ını ekle
        $renderer = $this;
        $themeLoader = $this->themeLoader;
        require $componentPath;
    }
    
    /**
     * Snippet include eder
     */
    public function snippet($snippetName, $data = []) {
        // Tema aktifse, önce tema snippet'lerinde ara
        if ($this->hasActiveTheme()) {
            $output = $this->themeLoader->renderSnippet($snippetName, $data);
            if (strpos($output, '<!-- Snippet not found') === false) {
                echo $output;
                return;
            }
        }
        
        // Fallback: varsayılan dizinler
        $snippetPath = $this->basePath . '/frontend/snippets/' . $snippetName . '.php';
        if (!file_exists($snippetPath)) {
            $snippetPath = $this->basePath . '/snippets/' . $snippetName . '.php';
        }
        
        if (!file_exists($snippetPath)) {
            echo "<!-- Snippet bulunamadı: {$snippetName} -->";
            return;
        }
        
        // Mevcut değişkenleri koru
        extract($data);
        // ViewRenderer instance'ını ekle
        $renderer = $this;
        $themeLoader = $this->themeLoader;
        require $snippetPath;
    }
    
    /**
     * Page section'larını render et
     */
    public function pageSections(string $pageType): void {
        if (!$this->themeLoader || !class_exists('ThemeManager')) {
            return;
        }
        
        $themeManager = ThemeManager::getInstance();
        $sections = $themeManager->getPageSections($pageType);
        
        foreach ($sections as $section) {
            if (!($section['is_active'] ?? true)) continue;
            
            echo $this->themeLoader->renderSection($section);
        }
    }
    
    /**
     * Layout ayarlar
     */
    public function setLayout($layoutName) {
        $this->layout = $layoutName;
    }
    
    /**
     * Section başlatır
     */
    public function startSection($name) {
        $this->currentSection = $name;
        ob_start();
    }
    
    /**
     * Section'ı bitirir
     */
    public function endSection() {
        if ($this->currentSection) {
            $this->sections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }
    
    /**
     * Section'ı render eder
     */
    public function yieldSection($name, $default = '') {
        echo isset($this->sections[$name]) ? $this->sections[$name] : $default;
    }
    
    // ==========================================
    // TEMA HELPER METODLARI
    // ==========================================
    
    /**
     * Tema CSS değişkenlerini style tag olarak getir
     */
    public function getThemeStyles(): string {
        if (!$this->hasActiveTheme()) {
            return '';
        }
        return $this->themeLoader->getCssVariablesTag();
    }
    
    /**
     * Tema ayarını getir
     */
    public function themeSetting(string $key, $default = null, ?string $group = null) {
        if (!$this->hasActiveTheme()) {
            return $default;
        }
        return $this->themeLoader->getSetting($key, $default, $group);
    }
    
    /**
     * Tema rengini getir
     */
    public function themeColor(string $key, string $default = '#000000'): string {
        if (!$this->hasActiveTheme()) {
            return $default;
        }
        return $this->themeLoader->getColor($key, $default);
    }
    
    /**
     * Tema fontunu getir
     */
    public function themeFont(string $key, string $default = 'Inter'): string {
        if (!$this->hasActiveTheme()) {
            return $default;
        }
        return $this->themeLoader->getFont($key, $default);
    }
    
    /**
     * Tema asset URL'si
     */
    public function themeAsset(string $path): string {
        if (!$this->hasActiveTheme()) {
            return self::assetUrl($path);
        }
        return $this->themeLoader->getAssetUrl($path);
    }
    
    // ==========================================
    // STATIC HELPER FUNCTIONS
    // ==========================================
    
    public static function siteUrl($path = '') {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $base = str_replace(basename($scriptName), '', $scriptName);
        if (strpos($scriptName, 'public/') !== false) {
            $base = str_replace('public/', '', $base);
        }
        return $protocol . $host . $base . ltrim($path, '/');
    }
    
    public static function adminUrl($path = '') {
        $baseUrl = self::siteUrl('public/admin.php');
        if ($path) {
            return $baseUrl . '?page=' . ltrim($path, '/');
        }
        return $baseUrl;
    }
    
    public static function assetUrl($path) {
        return self::siteUrl('public/' . ltrim($path, '/'));
    }
    
    public static function escHtml($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
    
    public static function escAttr($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
    
    public static function escUrl($url) {
        return filter_var($url ?? '', FILTER_SANITIZE_URL);
    }
    
    /**
     * Shortcode'ları parse eder
     */
    public function parseShortcodes($content) {
        // ShortcodeParser yüklü mü kontrol et
        if (!class_exists('ShortcodeParser')) {
            $shortcodeFile = __DIR__ . '/ShortcodeParser.php';
            if (file_exists($shortcodeFile)) {
                require_once $shortcodeFile;
            } else {
                return $content;
            }
        }
        
        try {
            return ShortcodeParser::getInstance()->parse($content);
        } catch (Exception $e) {
            error_log("Shortcode parse error: " . $e->getMessage());
            return $content;
        }
    }
    
    /**
     * Tek bir shortcode'u render eder
     */
    public function shortcode($tag, $atts = [], $content = '') {
        if (!class_exists('ShortcodeParser')) {
            return '';
        }
        
        // Shortcode string'i oluştur
        $attsString = '';
        foreach ($atts as $key => $value) {
            $attsString .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }
        
        $shortcodeString = '[' . $tag . $attsString . ']';
        if ($content) {
            $shortcodeString .= $content . '[/' . $tag . ']';
        }
        
        return $this->parseShortcodes($shortcodeString);
    }
}
