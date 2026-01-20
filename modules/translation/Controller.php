<?php
/**
 * Translation Modül Controller
 * 
 * Minimal controller - handles module lifecycle and route delegation
 * URL yapısı: /en/page-slug, /de/page-slug, /tr/page-slug
 */

require_once __DIR__ . '/models/TranslationModel.php';
require_once __DIR__ . '/services/TranslationService.php';
require_once __DIR__ . '/services/LanguageService.php';
require_once __DIR__ . '/services/BulkTranslateService.php';
require_once __DIR__ . '/Handlers/FrontendHandler.php';
require_once __DIR__ . '/Handlers/AdminHandler.php';

class TranslationModuleController {
    
    private $moduleInfo;
    private $settings;
    private $model;
    private $translationService;
    private $languageService;
    private $bulkTranslateService;
    private $frontendHandler;
    private $adminHandler;
    
    // Cache flags
    private $initialized = false;
    private $settingsLoaded = false;
    
    public function __construct() {
        if (class_exists('Database')) {
            $this->model = new TranslationModel();
        }
    }
    
    public function setModuleInfo($info) {
        $this->moduleInfo = $info;
    }
    
    /**
     * Module load - Initialize services and register hooks
     */
    public function onLoad() {
            $this->ensureInitialized();
            $this->loadSettings();
        
        // Initialize services - Model'i geçir
        $this->languageService = new LanguageService($this->settings, $this->model);
        $this->translationService = new TranslationService($this->languageService, $this->settings, $this->model);
        $this->bulkTranslateService = new BulkTranslateService($this->translationService, $this->settings);
        
        // Initialize handlers
        $this->frontendHandler = new FrontendHandler(
            $this->translationService,
            $this->languageService,
            $this->model,
            $this->settings
        );
        
        // ThemeStringExtractor'ı oluştur
        $themeStringExtractor = new ThemeStringExtractor();
        
        $this->adminHandler = new AdminHandler(
            $this->translationService,
            $this->languageService,
            $this->bulkTranslateService,
            $this->model,
            $this->settings,
            $themeStringExtractor
        );
        
        // Detect language
        $this->languageService->detectLanguage();
        
        // Register actions
        if (function_exists('add_action')) {
            add_action('init', [$this->languageService, 'initLanguage']);
            add_action('wp_head', [$this->frontendHandler, 'outputLanguageMeta']);
            
            // Language switcher için multiple hook pozisyonları
            // Sadece ilk tetiklenen çalışacak (FrontendHandler içinde flag kontrolü var)
            add_action('theme_navigation_after_menu', [$this->frontendHandler, 'renderLanguageSwitcher']);
            add_action('theme_navigation_before_menu', [$this->frontendHandler, 'renderLanguageSwitcher']);
            add_action('theme_header_after_menu', [$this->frontendHandler, 'renderLanguageSwitcher']);
            add_action('theme_header_after_navigation', [$this->frontendHandler, 'renderLanguageSwitcher']);
            add_action('theme_header_before_navigation', [$this->frontendHandler, 'renderLanguageSwitcher']);
        }
        
        // Register filters
        if (function_exists('add_filter')) {
            add_filter('the_content', [$this->frontendHandler, 'filterContent']);
            add_filter('document_title', [$this->frontendHandler, 'filterTitle']);
            add_filter('page_content', [$this->frontendHandler, 'filterContent']);
            add_filter('post_content', [$this->frontendHandler, 'filterContent']);
            add_filter('page_title', [$this->frontendHandler, 'filterTitle']);
            add_filter('post_title', [$this->frontendHandler, 'filterTitle']);
            add_filter('page_excerpt', [$this->frontendHandler, 'filterExcerpt']);
            add_filter('post_excerpt', [$this->frontendHandler, 'filterExcerpt']);
            
            // Tema section'ları için filter'lar
            add_filter('theme_section_title', [$this->frontendHandler, 'filterTitle']);
            add_filter('theme_section_subtitle', [$this->frontendHandler, 'filterTitle']);
            add_filter('theme_section_content', [$this->frontendHandler, 'filterContent']);
            add_filter('theme_section_settings', [$this->frontendHandler, 'filterSectionSettings'], 10, 2);
            add_filter('theme_section_items', [$this->frontendHandler, 'filterSectionItems'], 10, 2);
            add_filter('theme_section_tabs', [$this->frontendHandler, 'filterSectionItems'], 10, 2);
            add_filter('theme_section_packages', [$this->frontendHandler, 'filterSectionItems'], 10, 2);
            add_filter('theme_option_value', [$this->frontendHandler, 'filterTitle']);
            
            // Eski filter'lar (geriye dönük uyumluluk için)
            add_filter('section_title', [$this->frontendHandler, 'filterTitle']);
            add_filter('section_subtitle', [$this->frontendHandler, 'filterTitle']);
            add_filter('section_content', [$this->frontendHandler, 'filterContent']);
            add_filter('section_item_title', [$this->frontendHandler, 'filterTitle']);
            add_filter('section_item_description', [$this->frontendHandler, 'filterContent']);
            add_filter('section_setting_text', [$this->frontendHandler, 'filterTitle']);
        }
        
        // Register shortcode
        if (function_exists('add_shortcode')) {
            add_shortcode('language_switcher', [$this->frontendHandler, 'shortcodeLanguageSwitcher']);
        }
    }
    
    /**
     * Module activate - Create tables and default settings
     */
    public function onActivate() {
        $this->ensureInitialized();
        $this->model->createTables();
        $this->saveDefaultSettings();
        $this->addDefaultLanguages();
    }
    
    /**
     * Module deactivate - Cleanup
     */
    public function onDeactivate() {
        // Cache temizliği
        if ($this->translationService) {
            $this->translationService->clearCache();
        }
    }
    
    /**
     * Module uninstall - Optional table removal
     */
    public function onUninstall() {
        // Tabloları sil (opsiyonel)
        // $this->model->dropTables();
    }
    
    /**
     * Translate method for helper functions (__(), _e(), etc.)
     * Public method for backward compatibility
     */
    public function translate($text, $domain = 'default') {
        if (!$this->translationService) {
        $this->ensureInitialized();
            $this->loadSettings();
        if (!$this->model) {
                $this->model = new TranslationModel();
            }
            $this->languageService = new LanguageService($this->settings, $this->model);
            $this->languageService->detectLanguage(); // Dil algılamayı başlat
            $this->translationService = new TranslationService($this->languageService, $this->settings);
        }
        return $this->translationService->translate($text, $domain);
    }
    
    /**
     * Get current language for backward compatibility
     */
    public function getCurrentLanguage() {
        if (!$this->languageService) {
            $this->ensureInitialized();
            $this->loadSettings();
            if (!$this->model) {
                $this->model = new TranslationModel();
            }
            $this->languageService = new LanguageService($this->settings, $this->model);
            $this->languageService->detectLanguage(); // Dil algılamayı başlat
        }
        return $this->languageService->getCurrentLanguage();
    }
    
    /**
     * Get LanguageService instance (for Router to set language directly)
     */
    public function getLanguageService() {
        if (!$this->languageService) {
            $this->ensureInitialized();
            $this->loadSettings();
            if (!$this->model) {
                $this->model = new TranslationModel();
            }
            $this->languageService = new LanguageService($this->settings, $this->model);
        }
        return $this->languageService;
    }
    
    /**
     * Admin methods - Public methods for ModuleLoader compatibility
     */
    public function admin_index() {
        $this->ensureAdminHandler();
        return $this->adminHandler->index();
    }
    
    public function admin_languages() {
        $this->ensureAdminHandler();
        return $this->adminHandler->languages();
    }
    
    public function admin_translations() {
        $this->ensureAdminHandler();
        return $this->adminHandler->translations();
    }
    
    public function admin_translation_edit($id) {
        $this->ensureAdminHandler();
        return $this->adminHandler->translationEdit($id);
    }
    
    public function admin_bulk_translate_init() {
        $this->ensureAdminHandler();
        return $this->adminHandler->bulkTranslateInit();
    }
    
    public function admin_bulk_translate() {
        $this->ensureAdminHandler();
        return $this->adminHandler->bulkTranslate();
    }
    
    public function admin_settings() {
        $this->ensureAdminHandler();
        return $this->adminHandler->settings();
    }
    
    public function admin_test_translations() {
        $this->ensureAdminHandler();
        return $this->adminHandler->testTranslations();
    }
    
    public function admin_cleanup_translations() {
        $this->ensureAdminHandler();
        return $this->adminHandler->cleanupTranslations();
    }
    
    public function admin_extract_theme_strings() {
        $this->ensureAdminHandler();
            header('Content-Type: application/json');
        $result = $this->adminHandler->extractThemeStrings();
        echo json_encode($result);
                    exit;
    }
    
    /**
     * Ensure admin handler is initialized
     */
    private function ensureAdminHandler() {
        if (!$this->adminHandler) {
        $this->ensureInitialized();
            $this->loadSettings();
            $this->languageService = new LanguageService($this->settings);
            $this->translationService = new TranslationService($this->languageService, $this->settings);
            $this->bulkTranslateService = new BulkTranslateService($this->translationService, $this->settings);
            $this->adminHandler = new AdminHandler(
                $this->translationService,
                $this->languageService,
                $this->bulkTranslateService,
                $this->model,
                $this->settings
            );
        }
    }
    
    /**
     * Frontend methods - Public methods for backward compatibility
     */
    public function filterContent($content) {
        $this->ensureFrontendHandler();
        return $this->frontendHandler->filterContent($content);
    }
    
    public function filterTitle($title) {
        $this->ensureFrontendHandler();
        return $this->frontendHandler->filterTitle($title);
    }
    
    public function filterExcerpt($excerpt) {
        $this->ensureFrontendHandler();
        return $this->frontendHandler->filterExcerpt($excerpt);
    }
    
    public function outputLanguageMeta() {
        $this->ensureFrontendHandler();
        return $this->frontendHandler->outputLanguageMeta();
    }
    
    public function renderLanguageSwitcher() {
        $this->ensureFrontendHandler();
        return $this->frontendHandler->renderLanguageSwitcher();
    }
    
    /**
     * Ensure frontend handler is initialized
     */
    private function ensureFrontendHandler() {
        if (!$this->frontendHandler) {
        $this->ensureInitialized();
            $this->loadSettings();
            $this->languageService = new LanguageService($this->settings);
            $this->translationService = new TranslationService($this->languageService, $this->settings);
            $this->frontendHandler = new FrontendHandler(
                $this->translationService,
                $this->languageService,
                $this->model,
                $this->settings
            );
        }
    }
    
    /**
     * Route delegation (fallback for other methods)
     */
    public function __call($method, $args) {
        // Shortcode support
        if ($method === 'shortcode_language_switcher') {
            $this->ensureFrontendHandler();
            return $this->frontendHandler->shortcodeLanguageSwitcher($args[0] ?? []);
        }
    }
    
    /**
     * Ensure module is initialized
     */
    private function ensureInitialized() {
        if ($this->initialized) {
            return;
        }
        
        if (!$this->model) {
            $this->model = new TranslationModel();
        }
        
        if (!$this->settingsLoaded) {
            $this->loadSettings();
        }
        
        $this->initialized = true;
    }
    
    /**
     * Load module settings
     */
    private function loadSettings() {
        if ($this->settingsLoaded && !empty($this->settings)) {
            return;
        }
        
        if (function_exists('get_module_settings')) {
            $this->settings = get_module_settings('translation');
        }
        
        if (empty($this->settings)) {
            $this->settings = $this->getDefaultSettings();
        }
        
        $this->settingsLoaded = true;
    }
    
    /**
     * Get default settings
     */
    private function getDefaultSettings() {
        return [
            'default_language' => 'tr',
            'auto_translate' => false,
            'deepl_api_key' => '',
            'deepl_api_url' => 'https://api-free.deepl.com/v2/translate'
        ];
    }
    
    /**
     * Save default settings
     */
    private function saveDefaultSettings() {
        if (!class_exists('ModuleLoader')) {
            return;
        }
        
        $defaults = $this->getDefaultSettings();
        ModuleLoader::getInstance()->saveModuleSettings('translation', $defaults);
    }
    
    /**
     * Add default languages
     */
    private function addDefaultLanguages() {
        $defaultLangs = [
            ['code' => 'tr', 'name' => 'Türkçe', 'native_name' => 'Türkçe', 'flag' => '🇹🇷', 'is_active' => 1],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'flag' => '🇬🇧', 'is_active' => 1],
            ['code' => 'de', 'name' => 'Deutsch', 'native_name' => 'Deutsch', 'flag' => '🇩🇪', 'is_active' => 1]
        ];
        
        foreach ($defaultLangs as $lang) {
            $existing = $this->model->getLanguageByCode($lang['code']);
            if (!$existing) {
                $this->model->addLanguage($lang);
            }
        }
    }
}
