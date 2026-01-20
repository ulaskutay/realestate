<?php
/**
 * Theme String Extractor Service
 * 
 * Tema dosyalarındaki hardcoded çeviri metinlerini extract eder
 */

class ThemeStringExtractor {
    
    private $basePath;
    private $extractedStrings = [];
    
    // Çeviri fonksiyonları
    private $translationFunctions = [
        '__',
        '_e',
        'esc_html__',
        'esc_attr__',
        'esc_html_e',
        'esc_attr_e'
    ];
    
    public function __construct($themePath = null) {
        if ($themePath === null) {
            // Aktif temayı al
            $activeTheme = null;
            
            // ThemeManager'dan aktif temayı al
            if (class_exists('ThemeManager')) {
                try {
                    $themeManager = ThemeManager::getInstance();
                    $activeTheme = $themeManager->getActiveTheme();
                } catch (Exception $e) {
                    error_log("ThemeStringExtractor: ThemeManager error: " . $e->getMessage());
                }
            }
            
            // ThemeManager çalışmıyorsa option'dan al
            if (!$activeTheme && function_exists('get_option')) {
                $themeSlug = get_option('active_theme', 'realestate');
            } else {
                $themeSlug = $activeTheme['slug'] ?? 'realestate';
            }
            
            // Tema yolu oluştur
            // __DIR__ = /home/codeticc/public_html/modules/translation/services
            // dirname(__DIR__) = /home/codeticc/public_html/modules/translation
            // dirname(dirname(__DIR__)) = /home/codeticc/public_html/modules
            // dirname(dirname(dirname(__DIR__))) = /home/codeticc/public_html
            // dirname(dirname(dirname(__DIR__))) . '/themes/' . $themeSlug = /home/codeticc/public_html/themes/realestate
            $this->basePath = dirname(dirname(dirname(__DIR__))) . '/themes/' . $themeSlug;
        } else {
            $this->basePath = $themePath;
        }
    }
    
    /**
     * Tema dosyalarını tara ve hardcoded metinleri extract et
     * 
     * @param string|null $themePath Tema yolu (null ise aktif tema kullanılır)
     * @return array Extract edilen metinler ['text' => count]
     */
    public function extractFromTheme($themePath = null) {
        if ($themePath !== null) {
            $this->basePath = $themePath;
        }
        
        if (!is_dir($this->basePath)) {
            error_log("Theme path not found: " . $this->basePath);
            return [];
        }
        
        $this->extractedStrings = [];
        
        // PHP dosyalarını recursive olarak tara
        $this->scanDirectory($this->basePath);
        
        return $this->extractedStrings;
    }
    
    /**
     * Dizini recursive olarak tara
     * 
     * @param string $dir Dizin yolu
     */
    private function scanDirectory($dir) {
        if (!is_dir($dir)) {
            error_log("ThemeStringExtractor: Directory not found: " . $dir);
            return;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY  // Sadece dosyaları al, dizinleri değil
        );
        
        $fileCount = 0;
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $filePath = $file->getPathname();
                // Admin ve module dosyalarını atla (sadece frontend component'leri)
                $relativePath = str_replace($this->basePath . DIRECTORY_SEPARATOR, '', $filePath);
                $relativePath = str_replace('\\', '/', $relativePath); // Windows path'leri normalize et
                
                if (strpos($relativePath, 'views/admin/') === false && 
                    strpos($relativePath, 'modules/') === false) {
                    $this->extractFromFile($filePath);
                    $fileCount++;
                }
            }
        }
        
        error_log("ThemeStringExtractor: Scanned $fileCount PHP files from " . $dir);
    }
    
    /**
     * Tek bir dosyadan metinleri extract et
     * 
     * @param string $filePath Dosya yolu
     * @return array Extract edilen metinler
     */
    public function extractFromFile($filePath) {
        // Basit ve güvenilir metodu kullan
        return $this->extractFromFileSimple($filePath);
    }
    
    /**
     * Regex pattern oluştur
     * 
     * @param string $funcName Fonksiyon adı
     * @return string Regex pattern
     */
    private function buildPattern($funcName) {
        // Fonksiyon adı, açılış parantezi, opsiyonel whitespace, string başlangıcı
        // Pattern: function_name\s*\(\s*['"]([^'"]+)['"]
        // Ama dikkat: escape edilmiş tırnakları da handle etmeliyiz
        $escapedFunc = preg_quote($funcName, '/');
        
        // Tek tırnak veya çift tırnak içindeki string'leri yakala
        // Escape edilmiş tırnakları da handle et: 'It\'s' veya "He said \"Hello\""
        // Basit yaklaşım: escape edilmemiş tırnak bulana kadar devam et
        $pattern = '/' . $escapedFunc . '\s*\(\s*(["\'])((?:(?<!\\\\)(?:\\\\\\\\)*\\\\' . '\1' . '|(?:(?!\1).))*?)\1/s';
        
        return $pattern;
    }
    
    /**
     * String literal'ı extract et
     * 
     * @param string $quote Tırnak tipi (' veya ")
     * @param string $content Dosya içeriği
     * @param string $match Tam eşleşme
     * @return string|null Extract edilen string veya null
     */
    private function extractString($quote, $content, $match) {
        // Match'ten string'i çıkar
        // Pattern'den gelen $match[2] zaten string içeriği
        // Ama escape karakterlerini decode etmeliyiz
        
        // Basit yaklaşım: regex'ten gelen string'i kullan
        // Ama önce escape karakterlerini decode et
        $text = stripslashes($match);
        
        // Fonksiyon adını ve parantezleri kaldır
        // Örnek: __('text') -> 'text'
        preg_match('/\([' . $quote . '"](.+?)[' . $quote . '"]\)/s', $match, $stringMatch);
        if (isset($stringMatch[1])) {
            $text = $stringMatch[1];
            // Escape karakterlerini decode et
            if ($quote === "'") {
                // Tek tırnak: sadece \' ve \\ escape edilir
                $text = str_replace(["\\'", "\\\\"], ["'", "\\"], $text);
            } else {
                // Çift tırnak: tüm escape karakterleri
                $text = stripcslashes($text);
            }
            return $text;
        }
        
        return null;
    }
    
    /**
     * Daha basit ve güvenilir extract metodu
     * Regex ile direkt string literal'ları yakala
     * 
     * @param string $filePath Dosya yolu
     * @return array Extract edilen metinler
     */
    public function extractFromFileSimple($filePath) {
        if (!file_exists($filePath)) {
            return [];
        }
        
        $content = file_get_contents($filePath);
        if ($content === false) {
            return [];
        }
        
        $strings = [];
        
        // Her çeviri fonksiyonu için
        foreach ($this->translationFunctions as $func) {
            // Pattern: function_name( 'string' ) veya function_name( "string" )
            // Escape edilmiş tırnakları handle et
            $escapedFunc = preg_quote($func, '/');
            
            // Tek tırnak string'ler
            // Pattern: __( 'text' ) - en basit ve güvenilir pattern
            $pattern1 = '/' . $escapedFunc . '\s*\(\s*\'([^\']+)\'\s*\)/';
            if (preg_match_all($pattern1, $content, $matches1)) {
                foreach ($matches1[1] as $match) {
                    $decoded = stripcslashes($match);
                    $trimmed = trim($decoded);
                    if (!empty($trimmed)) {
                        if (!isset($strings[$trimmed])) {
                            $strings[$trimmed] = 0;
                        }
                        $strings[$trimmed]++;
                    }
                }
            }
            
            // Çift tırnak string'ler
            // Pattern: __( "text" ) - en basit ve güvenilir pattern
            $pattern2 = '/' . $escapedFunc . '\s*\(\s*"([^"]+)"\s*\)/';
            if (preg_match_all($pattern2, $content, $matches2)) {
                foreach ($matches2[1] as $match) {
                    $decoded = stripcslashes($match);
                    $trimmed = trim($decoded);
                    if (!empty($trimmed)) {
                        if (!isset($strings[$trimmed])) {
                            $strings[$trimmed] = 0;
                        }
                        $strings[$trimmed]++;
                    }
                }
            }
        }
        
        // Global extractedStrings'e ekle
        foreach ($strings as $text => $count) {
            if (!isset($this->extractedStrings[$text])) {
                $this->extractedStrings[$text] = 0;
            }
            $this->extractedStrings[$text] += $count;
        }
        
        return $strings;
    }
    
    /**
     * Extract edilen metinleri döndür
     * 
     * @return array ['text' => count]
     */
    public function getExtractedStrings() {
        return $this->extractedStrings;
    }
    
    /**
     * Extract edilen metinleri temizle
     */
    public function clear() {
        $this->extractedStrings = [];
    }
}
