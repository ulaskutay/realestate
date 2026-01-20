<?php
/**
 * Frontend Handler
 * 
 * Handles frontend translation filters, language switcher, and meta tags
 */

require_once __DIR__ . '/../services/TranslationService.php';
require_once __DIR__ . '/../services/LanguageService.php';
require_once __DIR__ . '/../models/TranslationModel.php';

class FrontendHandler {
    
    private $translationService;
    private $languageService;
    private $model;
    private $settings;
    
    // Static flag: Language switcher sadece bir kez render edilmeli
    private static $switcherRendered = false;
    
    public function __construct($translationService, $languageService, $model, $settings) {
        $this->translationService = $translationService;
        $this->languageService = $languageService;
        $this->model = $model;
        $this->settings = $settings;
    }
    
    /**
     * Filter content - translates content
     * Handles HTML content by parsing and translating text nodes only
     * 
     * @param string $content Content to translate
     * @return string Translated content
     */
    public function filterContent($content) {
        if (empty($content) || !is_string($content)) {
            return $content;
        }
        
        // HTML içerik kontrolü - HTML ise özel işlem yap
        if ($this->translationService->isHtmlContent($content)) {
            return $this->translationService->translateHtmlContent($content);
        }
        
        // Normal text içerik için standart çeviri
        return $this->translationService->translate($content);
    }
    
    /**
     * Filter title - translates title
     * 
     * @param string $title Title to translate
     * @return string Translated title
     */
    public function filterTitle($title) {
        return $this->translationService->translate($title);
    }
    
    /**
     * Filter excerpt - translates excerpt
     * 
     * @param string $excerpt Excerpt to translate
     * @return string Translated excerpt
     */
    public function filterExcerpt($excerpt) {
        return $this->translationService->translate($excerpt);
    }
    
    /**
     * Output language meta tag
     */
    public function outputLanguageMeta() {
        $currentLang = $this->languageService->getCurrentLanguage();
        echo '<meta http-equiv="content-language" content="' . htmlspecialchars($currentLang) . '">' . "\n";
    }
    
    /**
     * Render language switcher
     * Sadece bir kez render edilir (birden fazla hook'tan koruma)
     */
    public function renderLanguageSwitcher() {
        // Eğer zaten render edildiyse, tekrar render etme
        if (self::$switcherRendered) {
            return;
        }
        
        $languages = $this->model->getActiveLanguages();
        if (empty($languages) || count($languages) < 2) {
            return; // Sadece 1 dil varsa gösterme
        }
        
        $currentUrl = $this->getCurrentUrl();
        $currentPath = parse_url($currentUrl, PHP_URL_PATH);
        $pathParts = explode('/', trim($currentPath, '/'));
        
        // Mevcut dil kodunu URL'den çıkar
        $basePath = $currentPath;
        if (!empty($pathParts[0]) && strlen($pathParts[0]) === 2 && $this->model->isValidLanguage($pathParts[0])) {
            array_shift($pathParts);
            // PathParts'ı yeniden birleştir - eğer boşsa boş string
            if (empty($pathParts)) {
                $basePath = '';
            } else {
                $basePath = '/' . implode('/', array_filter($pathParts)); // Boş elemanları filtrele
            }
        }
        
        // basePath'i normalize et - baştaki ve sondaki slash'leri temizle
        $basePath = trim($basePath, '/');
        
        $defaultLang = $this->settings['default_language'] ?? 'tr';
        $currentLang = $this->languageService->getCurrentLanguage();
        
        // Debug: Language switcher path hesaplama
        error_log("LanguageSwitcher: currentPath='$currentPath', basePath='$basePath', currentLang='$currentLang', defaultLang='$defaultLang'");
        
        // Unique ID oluştur (desktop ve mobile için farklı olmalı)
        $uniqueId = 'lang-switcher-' . uniqid();
        
        ?>
        <div class="language-switcher-wrapper relative group" data-lang-switcher-id="<?php echo esc_attr($uniqueId); ?>">
            <button type="button" class="language-switcher-btn flex items-center gap-1.5 px-3 py-2 rounded-lg transition-colors duration-300 text-[#A1A1AA] hover:text-white hover:bg-white/5" data-lang-switcher-btn="<?php echo esc_attr($uniqueId); ?>">
                <span class="text-sm font-medium"><?php echo strtoupper($currentLang); ?></span>
                <svg class="w-4 h-4 transition-transform duration-300 group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            
            <div class="language-switcher-dropdown lg:absolute lg:right-0 lg:top-full lg:mt-2 relative lg:opacity-0 lg:invisible lg:group-hover:opacity-100 lg:group-hover:visible mt-2 bg-[#0B0B0B]/90 backdrop-blur-2xl border border-white/8 rounded-2xl shadow-xl py-2 min-w-[160px] opacity-0 invisible transition-all duration-300 ease-out z-50" data-lang-switcher-dropdown="<?php echo esc_attr($uniqueId); ?>">
                <?php foreach ($languages as $lang): ?>
                    <?php
                    // Varsayılan dil için prefix kullanma, diğerleri için /lang/path şeklinde
                    if ($lang['code'] === $defaultLang) {
                        // Varsayılan dil için prefix yok
                        $langUrl = $basePath ? '/' . $basePath : '/';
                    } else {
                        // Diğer diller için /lang/path şeklinde
                        $langUrl = '/' . $lang['code'];
                        if (!empty($basePath)) {
                            $langUrl .= '/' . $basePath;
                        } else {
                            $langUrl .= '/';
                        }
                    }
                    // URL'i normalize et - sondaki slash'i kaldır (ana sayfa hariç)
                    if ($langUrl !== '/') {
                        $langUrl = rtrim($langUrl, '/');
                    }
                    $isActive = $lang['code'] === $currentLang;
                    ?>
                    <a href="<?php echo htmlspecialchars($langUrl); ?>" 
                       class="flex items-center gap-2.5 px-4 py-2.5 text-[#A1A1AA] hover:text-white hover:bg-white/5 transition-colors duration-300 <?php echo $isActive ? 'bg-white/5 text-white font-medium' : ''; ?>"
                       data-lang="<?php echo $lang['code']; ?>">
                        <span class="text-lg"><?php echo htmlspecialchars($lang['flag'] ?? ''); ?></span>
                        <span class="text-sm"><?php echo htmlspecialchars($lang['native_name'] ?? $lang['name']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <script>
        (function() {
            'use strict';
            
            // Unique ID ile switcher'ı bul
            const switcherId = '<?php echo esc_js($uniqueId); ?>';
            const wrapper = document.querySelector('[data-lang-switcher-id="' + switcherId + '"]');
            if (!wrapper) return;
            
            const btn = wrapper.querySelector('[data-lang-switcher-btn="' + switcherId + '"]');
            const dropdown = wrapper.querySelector('[data-lang-switcher-dropdown="' + switcherId + '"]');
            
            if (!btn || !dropdown) return;
            
            // Mobile için dropdown toggle
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const isHidden = dropdown.classList.contains('opacity-0') || dropdown.classList.contains('invisible');
                
                if (isHidden) {
                    dropdown.classList.remove('opacity-0', 'invisible');
                } else {
                    dropdown.classList.add('opacity-0', 'invisible');
                }
            });
            
            // Dışarı tıklandığında kapat
            document.addEventListener('click', function(e) {
                if (wrapper && !wrapper.contains(e.target)) {
                    dropdown.classList.add('opacity-0', 'invisible');
                }
            });
        })();
        </script>
        <?php
        
        // Render edildi olarak işaretle
        self::$switcherRendered = true;
    }
    
    /**
     * Shortcode: Language switcher
     * 
     * @param array $atts Shortcode attributes
     * @return string Language switcher HTML
     */
    public function shortcodeLanguageSwitcher($atts = []) {
        $languages = $this->model->getActiveLanguages();
        $currentUrl = $this->getCurrentUrl();
        $currentPath = parse_url($currentUrl, PHP_URL_PATH);
        $pathParts = explode('/', trim($currentPath, '/'));
        
        // Mevcut dil kodunu URL'den çıkar
        $basePath = $currentPath;
        if (!empty($pathParts[0]) && strlen($pathParts[0]) === 2 && $this->model->isValidLanguage($pathParts[0])) {
            array_shift($pathParts);
            $basePath = '/' . implode('/', $pathParts);
        }
        
        ob_start();
        ?>
        <div class="language-switcher flex gap-2">
            <?php foreach ($languages as $lang): ?>
                <?php
                $langUrl = ($lang['code'] === ($this->settings['default_language'] ?? 'tr')) 
                    ? $basePath 
                    : '/' . $lang['code'] . $basePath;
                ?>
                <a href="<?php echo htmlspecialchars($langUrl); ?>" 
                   class="lang-link px-3 py-1 rounded <?php echo $lang['code'] === $this->languageService->getCurrentLanguage() ? 'bg-blue-500 text-white' : 'bg-gray-200'; ?>"
                   data-lang="<?php echo $lang['code']; ?>">
                    <?php echo htmlspecialchars($lang['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get current URL
     * 
     * @return string Current URL
     */
    private function getCurrentUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        return $protocol . '://' . $host . $path;
    }
    
    /**
     * Filter section settings recursively
     * Only skips truly technical values (URLs, colors, numbers, etc.)
     * All other text values are translated
     * 
     * @param array $settings Settings array
     * @param string $sectionId Section ID
     * @return array Translated settings
     */
    public function filterSectionSettings($settings, $sectionId) {
        if (!is_array($settings)) {
            return $settings;
        }
        
        $currentLang = $this->languageService->getCurrentLanguage();
        $defaultLang = $this->languageService->getDefaultLanguage();
        
        // Varsayılan dil ise çevirme
        if ($currentLang === $defaultLang) {
            return $settings;
        }
        
        foreach ($settings as $key => $value) {
            if (is_string($value) && !empty($value)) {
                // Teknik değer kontrolü (shouldNotTranslate metodunu kullan)
                // BulkTranslateService'teki mantık ile aynı
                if ($this->shouldNotTranslate($value)) {
                    continue; // Teknik değer, çevirme
                }
                
                // HTML içerik ise özel işlem yap
                if ($this->translationService->isHtmlContent($value)) {
                    $settings[$key] = $this->translationService->translateHtmlContent($value);
                } else {
                    $settings[$key] = $this->translationService->translate($value);
                }
            } elseif (is_array($value)) {
                $settings[$key] = $this->filterSectionSettings($value, $sectionId);
            }
        }
        
        return $settings;
    }
    
    /**
     * Check if value should not be translated (technical values only)
     * Same logic as BulkTranslateService::shouldNotTranslate()
     * 
     * @param string $value Value to check
     * @return bool True if should NOT translate
     */
    private function shouldNotTranslate($value) {
        if ($value === null || !is_string($value)) {
            return true;
        }
        
        $value = trim($value);
        
        // Boş değer
        if (empty($value)) {
            return true;
        }
        
        // Çok kısa metinler (1 karakter)
        if (strlen($value) <= 1) {
            return true;
        }
        
        // URL kontrolü - sadece gerçek URL'ler
        if (preg_match('/^(https?:\/\/|mailto:|tel:)/', $value)) {
            return true;
        }
        
        // Renk kodları - Hex (#ffffff, #fff)
        if (preg_match('/^#[0-9a-fA-F]{3,8}$/i', $value)) {
            return true;
        }
        
        // Renk kodları - RGB/RGBA/HSL/HSLA
        if (preg_match('/^(rgb|rgba|hsl|hsla)\s*\(/i', $value)) {
            return true;
        }
        
        // CSS değerleri (10px, 1.5rem, 100%, 50vh, 50deg)
        if (preg_match('/^[\d.]+(px|rem|em|%|vh|vw|ch|ex|cm|mm|in|pt|pc|deg|rad|turn|s|ms)$/', $value)) {
            return true;
        }
        
        // Sayısal değerler
        if (is_numeric($value) && preg_match('/^-?[\d.]+$/', $value)) {
            return true;
        }
        
        // Boolean/Null değerler
        if (in_array(strtolower($value), ['true', 'false', 'yes', 'no', 'null', 'undefined', 'none'])) {
            return true;
        }
        
        // JSON benzeri (tek satır JSON)
        if ((substr($value, 0, 1) === '{' && substr($value, -1) === '}') ||
            (substr($value, 0, 1) === '[' && substr($value, -1) === ']')) {
            return true;
        }
        
        // Email adresi
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        
        // Dosya uzantıları (.jpg, .pdf, .css, .js vb.)
        if (preg_match('/^\.?[a-z0-9]{1,5}$/i', $value) && preg_match('/\.(jpg|jpeg|png|gif|svg|webp|ico|pdf|doc|docx|xls|xlsx|zip|mp4|mp3|wav|css|js|php|html|woff|woff2|ttf|eot)$/i', $value)) {
            return true;
        }
        
        // Sadece özel karakterler (hiç harf/sayı yok)
        if (preg_match('/^[^a-zA-Z0-9]+$/', $value)) {
            return true;
        }
        
        // Diğer tüm durumlarda çevir
        return false;
    }
    
    /**
     * Filter section items recursively
     * Only skips truly technical values (URLs, colors, numbers, etc.)
     * All other text values are translated
     * 
     * @param array $items Items array
     * @param string $sectionId Section ID
     * @return array Translated items
     */
    public function filterSectionItems($items, $sectionId) {
        if (!is_array($items)) {
            return $items;
        }
        
        $currentLang = $this->languageService->getCurrentLanguage();
        $defaultLang = $this->languageService->getDefaultLanguage();
        
        // Varsayılan dil ise çevirme
        if ($currentLang === $defaultLang) {
            return $items;
        }
        
        foreach ($items as &$item) {
            if (is_array($item)) {
                foreach ($item as $key => $value) {
                    if (is_string($value) && !empty($value)) {
                        // Teknik değer kontrolü
                        if ($this->shouldNotTranslate($value)) {
                            continue; // Teknik değer, çevirme
                        }
                        
                        // HTML içerik ise özel işlem yap
                        if ($this->translationService->isHtmlContent($value)) {
                            $item[$key] = $this->translationService->translateHtmlContent($value);
                        } else {
                            $item[$key] = $this->translationService->translate($value);
                        }
                    } elseif (is_array($value)) {
                        $item[$key] = $this->filterSectionItems($value, $sectionId);
                    }
                }
            }
        }
        
        return $items;
    }
}
