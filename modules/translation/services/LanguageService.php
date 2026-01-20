<?php
/**
 * Language Service
 * 
 * Handles language detection, session management, and language-related operations
 */

require_once __DIR__ . '/../models/TranslationModel.php';

class LanguageService {
    
    private $model;
    private $settings;
    private $currentLanguage;
    private $languageDetected = false;
    
    public function __construct($settings, $model = null) {
        $this->settings = $settings;
        $this->model = $model; // Model'i direkt al (eğer verilmişse)
        // Model yoksa lazy load edilecek (ensureModel'de)
    }
    
    /**
     * Ensure model is initialized
     */
    private function ensureModel() {
        if (!$this->model) {
            $this->model = new TranslationModel();
        }
    }
    
    /**
     * Detect language from URL
     * URL structure: /en/page-slug, /de/page-slug, /tr/page-slug
     */
    public function detectLanguage() {
        // Cache kontrolü - ama eğer session'da farklı bir dil varsa, yeniden kontrol et
        if ($this->languageDetected && !empty($this->currentLanguage)) {
            // Session'dan kontrol et - eğer farklıysa güncelle
            if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['current_language'])) {
                if ($_SESSION['current_language'] !== $this->currentLanguage) {
                    $this->currentLanguage = $_SESSION['current_language'];
                    return;
                }
            }
            return;
        }
        
        $this->ensureModel();
        
        $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $pathParts = explode('/', trim($requestPath, '/'));
        
        // Varsayılan dil (AYARLARDAN GELEN)
        $defaultLang = $this->settings['default_language'] ?? 'tr';
        if (!$this->model->isValidLanguage($defaultLang)) {
            $defaultLang = 'tr'; // Fallback
        }
        
        // 1. URL'den dil kodu kontrol et (örn: /en/page-slug) - EN ÖNCELİKLİ
        if (!empty($pathParts[0]) && strlen($pathParts[0]) === 2) {
            $langCode = strtolower($pathParts[0]);
            if ($this->model->isValidLanguage($langCode)) {
                $this->currentLanguage = $langCode;
                if (session_status() === PHP_SESSION_ACTIVE) {
                    $_SESSION['current_language'] = $langCode;
                }
                $this->languageDetected = true;
                
                // Debug
                error_log("LanguageService: Language detected from URL: '$langCode'");
                return;
            }
        }
        
        // 2. Session'dan kontrol et
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['current_language'])) {
            $sessionLang = $_SESSION['current_language'];
            if ($this->model->isValidLanguage($sessionLang)) {
                $this->currentLanguage = $sessionLang;
                $this->languageDetected = true;
                error_log("LanguageService: Language from session: '$sessionLang'");
                return;
            }
        }
        
        // 3. URL'de dil yoksa -> HER ZAMAN Varsayılan dil kullan
        // IP, browser veya session'a bakmadan direkt varsayılan dil
        $this->currentLanguage = $defaultLang;
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['current_language'] = $defaultLang;
        }
        
        // Cache flag'i set et
        $this->languageDetected = true;
        error_log("LanguageService: Using default language: '$defaultLang'");
    }
    
    /**
     * Get current language code
     * 
     * @return string Current language code
     */
    public function getCurrentLanguage() {
        // Önce $this->currentLanguage'ı kontrol et
        if (!empty($this->currentLanguage)) {
            return $this->currentLanguage;
        }
        
        // Session'dan kontrol et
        if (isset($_SESSION['current_language'])) {
            $this->currentLanguage = $_SESSION['current_language'];
            $this->languageDetected = true; // Cache flag'i set et
            return $this->currentLanguage;
        }
        
        // Dil algılanmamışsa algıla (cache kontrolü detectLanguage içinde)
        if (!$this->languageDetected) {
            $this->detectLanguage();
        }
        
        return $this->currentLanguage ?? ($this->settings['default_language'] ?? 'tr');
    }
    
    /**
     * Get default language code
     * 
     * @return string Default language code
     */
    public function getDefaultLanguage() {
        return $this->settings['default_language'] ?? 'tr';
    }
    
    /**
     * Get available languages
     * 
     * @return array List of active languages
     */
    public function getAvailableLanguages() {
        $this->ensureModel();
        return $this->model->getActiveLanguages();
    }
    
    /**
     * Check if language code is valid
     * 
     * @param string $code Language code
     * @return bool True if valid
     */
    public function isValidLanguage($code) {
        $this->ensureModel();
        return $this->model->isValidLanguage($code);
    }
    
    /**
     * Set current language (for manual override)
     * 
     * @param string $code Language code
     * @return bool True if set successfully
     */
    public function setCurrentLanguage($code) {
        $this->ensureModel();
        if (!$this->isValidLanguage($code)) {
            return false;
        }
        
        $this->currentLanguage = $code;
        $this->languageDetected = true;
        
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['current_language'] = $code;
        }
        
        return true;
    }
    
    /**
     * Get browser language (if available)
     * 
     * @return string|null Browser language code or null
     */
    public function getBrowserLanguage() {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return null;
        }
        
        $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        if (!empty($langs[0])) {
            $lang = substr(trim($langs[0]), 0, 2);
            return strtolower($lang);
        }
        
        return null;
    }
    
    /**
     * Initialize language from GET parameter
     * Used for language switching via ?lang=xx
     */
    public function initLanguage() {
        $this->ensureModel();
        // Dil değiştirme isteği
        if (isset($_GET['lang']) && $this->isValidLanguage($_GET['lang'])) {
            $this->setCurrentLanguage($_GET['lang']);
            
            $url = strtok($_SERVER['REQUEST_URI'], '?');
            header("Location: " . $url);
            exit;
        }
    }
    
    /**
     * Reset language detection (for testing)
     */
    public function reset() {
        $this->currentLanguage = null;
        $this->languageDetected = false;
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['current_language'])) {
            unset($_SESSION['current_language']);
        }
    }
}
