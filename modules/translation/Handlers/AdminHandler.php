<?php
/**
 * Admin Handler
 * 
 * Handles all admin panel operations for translation module
 */

require_once __DIR__ . '/../services/TranslationService.php';
require_once __DIR__ . '/../services/LanguageService.php';
require_once __DIR__ . '/../services/BulkTranslateService.php';
require_once __DIR__ . '/../services/ThemeStringExtractor.php';
require_once __DIR__ . '/../models/TranslationModel.php';

class AdminHandler {
    
    private $db;
    private $model;
    private $translationService;
    private $languageService;
    private $bulkTranslateService;
    private $themeStringExtractor;
    private $settings;
    
    public function __construct($translationService, $languageService, $bulkTranslateService, $model, $settings, $themeStringExtractor = null) {
        $this->db = Database::getInstance();
        $this->model = $model;
        $this->translationService = $translationService;
        $this->languageService = $languageService;
        $this->bulkTranslateService = $bulkTranslateService;
        $this->settings = $settings;
        // ThemeStringExtractor'ı lazy load et
        if ($themeStringExtractor === null) {
            $this->themeStringExtractor = new ThemeStringExtractor();
        } else {
            $this->themeStringExtractor = $themeStringExtractor;
        }
    }
    
    /**
     * Admin index page
     */
    public function index() {
        $this->requireLogin();
        
        $languages = $this->model->getAllLanguages();
        $stats = $this->model->getStats();
        
        $this->adminView('index', [
            'title' => 'Dil Yönetimi',
            'languages' => $languages,
            'stats' => $stats,
            'settings' => $this->settings
        ]);
    }
    
    /**
     * Languages management page
     */
    public function languages() {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] === 'create') {
                $data = [
                    'code' => strtolower($_POST['code'] ?? ''),
                    'name' => $_POST['name'] ?? '',
                    'native_name' => $_POST['native_name'] ?? '',
                    'flag' => $_POST['flag'] ?? '',
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ];
                
                if (empty($data['code']) || empty($data['name'])) {
                    $_SESSION['flash_message'] = 'Dil kodu ve adı gerekli';
                    $_SESSION['flash_type'] = 'error';
                } else {
                    $result = $this->model->addLanguage($data);
                    if ($result) {
                        $_SESSION['flash_message'] = 'Dil başarıyla eklendi';
                        $_SESSION['flash_type'] = 'success';
                    } else {
                        $_SESSION['flash_message'] = 'Dil eklenirken hata oluştu';
                        $_SESSION['flash_type'] = 'error';
                    }
                }
            } elseif ($_POST['action'] === 'delete' && isset($_POST['id'])) {
                $result = $this->model->deleteLanguage($_POST['id']);
                if ($result) {
                    $_SESSION['flash_message'] = 'Dil silindi';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Silme işlemi başarısız';
                    $_SESSION['flash_type'] = 'error';
                }
            }
            
            $this->redirect('languages');
            return;
        }
        
        $languages = $this->model->getAllLanguages();
        
        $this->adminView('languages', [
            'title' => 'Diller',
            'languages' => $languages
        ]);
    }
    
    /**
     * Translations list page
     */
    public function translations() {
        $this->requireLogin();
        
        // Pagination için 'p' parametresini kullan
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        if ($page < 1) $page = 1;
        
        $perPage = 20;
        $search = $_GET['search'] ?? '';
        $language = $_GET['language'] ?? '';
        $type = $_GET['type'] ?? '';
        
        $translations = $this->model->getTranslations($page, $perPage, $search, $language, $type);
        $total = $this->model->countTranslations($search, $language, $type);
        $languages = $this->model->getActiveLanguages();
        
        $this->adminView('translations', [
            'title' => 'Çeviriler',
            'translations' => $translations,
            'languages' => $languages,
            'currentPage' => $page,
            'totalPages' => ceil($total / $perPage),
            'search' => $search,
            'selectedLanguage' => $language,
            'selectedType' => $type
        ]);
    }
    
    /**
     * Edit translation page
     */
    public function translationEdit($id) {
        $this->requireLogin();
        
        $translation = $this->model->getTranslationById($id);
        
        if (!$translation) {
            $_SESSION['flash_message'] = 'Çeviri bulunamadı';
            $_SESSION['flash_type'] = 'error';
            $this->redirect('translations');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'translated_text' => $_POST['translated_text'] ?? '',
                'auto_translated' => isset($_POST['auto_translated']) ? 0 : 1
            ];
            
            $result = $this->model->updateTranslation($id, $data);
            
            if ($result) {
                $_SESSION['flash_message'] = 'Çeviri güncellendi';
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = 'Güncelleme sırasında hata oluştu';
                $_SESSION['flash_type'] = 'error';
            }
            
            $this->redirect('translations');
            return;
        }
        
        $this->adminView('translation-form', [
            'title' => 'Çeviri Düzenle',
            'translation' => $translation
        ]);
    }
    
    /**
     * Bulk translate init - Get content count
     */
    public function bulkTranslateInit() {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (ob_get_level()) {
                ob_clean();
            }
            
            header('Content-Type: application/json');
            
            try {
                $targetLang = $_POST['target_language'] ?? '';
                
                if (empty($targetLang)) {
                    echo json_encode(['success' => false, 'message' => 'Hedef dil seçilmedi']);
                    exit;
                }
                
                // Toplam içerik sayısını hesapla
                $totalItems = 0;
                
                // Her kategori için sayı hesapla
                $categories = [
                    'pages', 'posts', 'agreements', 'menus', 'menu_items',
                    'sliders', 'slider_items', 'slider_layers', 'forms', 'form_fields',
                    'categories', 'tags', 'page_sections', 'theme_options', 'themes',
                    'site_options'
                ];
                
                // Hardcoded metinleri extract et (eğer henüz extract edilmemişse)
                // NOT: Bu işlem sadece bir kez yapılmalı, ama her bulk translate'te kontrol ediyoruz
                // Daha iyi bir yaklaşım: extractThemeStrings() metodunu ayrı bir endpoint olarak sunmak
                
                foreach ($categories as $category) {
                    $totalItems += $this->bulkTranslateService->getContentCount($category);
                }
                
                echo json_encode([
                    'success' => true,
                    'total_items' => $totalItems,
                    'chunk_size' => 10 // Her chunk'ta 10 item çevir
                ]);
            } catch (Exception $e) {
                error_log("Bulk translate init error: " . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => 'Hata: ' . htmlspecialchars($e->getMessage())
                ]);
            }
            exit;
        }
    }
    
    /**
     * Bulk translate - Translate all content
     */
    public function bulkTranslate() {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Execution time limit'ini kaldır
            set_time_limit(0);
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '512M');
            
            // Output buffering'i temizle
            if (ob_get_level()) {
                ob_clean();
            }
            
            header('Content-Type: application/json');
            
            try {
                $targetLang = $_POST['target_language'] ?? '';
                $category = $_POST['category'] ?? 'all';
                
                if (empty($targetLang)) {
                    echo json_encode(['success' => false, 'message' => 'Hedef dil seçilmedi']);
                    exit;
                }
                
                // Hardcoded metinleri extract et (eğer henüz extract edilmemişse)
                // Sadece ilk kategori işlenirken (pages) extract yap, tekrar tekrar yapılmasın
                if ($category === 'pages' || $category === 'all') {
                    try {
                        $extractedStrings = $this->themeStringExtractor->extractFromTheme();
                        $extractedCount = 0;
                        // extractFromTheme() array döndürür: ['text' => count] formatında
                        // foreach ile key'leri (metinleri) alıyoruz
                        foreach ($extractedStrings as $text => $count) {
                            if (empty(trim($text))) {
                                continue;
                            }
                            $trimmedText = trim($text);
                            $textHash = md5($trimmedText);
                            
                            // Type belirle: kısa metinler title, uzun metinler content
                            $type = (strlen($trimmedText) <= 100) ? 'title' : 'content';
                            
                            // HTML içeriyorsa content
                            if (preg_match('/<[^>]+>/', $trimmedText)) {
                                $type = 'content';
                            }
                            
                            // Sadece henüz veritabanında olmayanları ekle
                            $existing = $this->model->getTranslation($type, $textHash, '', $trimmedText);
                            if (!$existing) {
                                $result = $this->model->saveTranslation([
                                    'type' => $type,  // hardcoded yerine title/content
                                    'source_id' => $textHash,
                                    'source_text' => $trimmedText,
                                    'target_language' => '', // Henüz çevrilmedi
                                    'translated_text' => '',
                                    'auto_translated' => 0
                                ]);
                                if ($result) {
                                    $extractedCount++;
                                } else {
                                    error_log("Failed to save extracted string: " . substr($trimmedText, 0, 50));
                                }
                            }
                        }
                        if ($extractedCount > 0) {
                            error_log("Extracted $extractedCount new theme strings before bulk translate");
                        } else {
                            error_log("No new theme strings extracted (total found: " . count($extractedStrings) . ")");
                        }
                    } catch (Exception $e) {
                        error_log("Theme string extraction error during bulk translate: " . $e->getMessage());
                        error_log("Stack trace: " . $e->getTraceAsString());
                        // Extract hatası olsa bile çeviriye devam et
                    }
                }
                
                $translated = 0;
                $skipped = 0;
                $sourceLang = $this->settings['default_language'] ?? 'tr';
                
                // Helper: Translate and save
                $translateAndSave = function($text, $type) use ($targetLang, $sourceLang, &$translated, &$skipped) {
                    $result = $this->bulkTranslateService->translateAndSave($text, $type, $targetLang, $sourceLang);
                    if ($result['translated']) {
                        $translated++;
                    } else {
                        $skipped++;
                    }
                };
                
                // Helper: Translate from database
                $translateFromDatabase = function($sql, $fields, $categoryName) use ($category, $translateAndSave, $targetLang, $sourceLang) {
                    if ($category !== 'all' && $category !== $categoryName) {
                        return;
                    }
                    $result = $this->bulkTranslateService->translateFromDatabase($sql, $fields, $categoryName, $targetLang, $sourceLang);
                    return $result;
                };
                
                // Translate all categories
                $totalTranslated = 0;
                $totalSkipped = 0;
                
                // 1. Pages
                $result = $translateFromDatabase(
                    "SELECT * FROM posts WHERE type = 'page' AND status = 'published'",
                    ['title' => 'title', 'content' => 'content', 'excerpt' => 'content'],
                    'pages'
                );
                $totalTranslated += $result['translated'] ?? 0;
                $totalSkipped += $result['skipped'] ?? 0;
                
                // 2. Posts
                $result = $translateFromDatabase(
                    "SELECT * FROM posts WHERE type = 'post' AND status = 'published'",
                    ['title' => 'title', 'content' => 'content', 'excerpt' => 'content'],
                    'posts'
                );
                $totalTranslated += $result['translated'] ?? 0;
                $totalSkipped += $result['skipped'] ?? 0;
                
                // 3. Agreements
                $result = $translateFromDatabase(
                    "SELECT * FROM agreements WHERE status = 'published'",
                    ['title' => 'title', 'content' => 'content'],
                    'agreements'
                );
                $totalTranslated += $result['translated'] ?? 0;
                $totalSkipped += $result['skipped'] ?? 0;
                
                // 4. Menus
                $result = $translateFromDatabase(
                    "SELECT * FROM menus WHERE status = 'active'",
                    ['name' => 'title', 'description' => 'content'],
                    'menus'
                );
                $totalTranslated += $result['translated'] ?? 0;
                $totalSkipped += $result['skipped'] ?? 0;
                
                // 5. Menu Items
                $result = $translateFromDatabase(
                    "SELECT * FROM menu_items WHERE status = 'active'",
                    ['title' => 'title'],
                    'menu_items'
                );
                $totalTranslated += $result['translated'] ?? 0;
                $totalSkipped += $result['skipped'] ?? 0;
                
                // 6. Sliders
                $result = $translateFromDatabase(
                    "SELECT * FROM sliders WHERE status = 'active'",
                    ['name' => 'title', 'description' => 'content'],
                    'sliders'
                );
                $totalTranslated += $result['translated'] ?? 0;
                $totalSkipped += $result['skipped'] ?? 0;
                
                // 7. Slider Items
                $result = $translateFromDatabase(
                    "SELECT * FROM slider_items WHERE is_active = 1",
                    ['title' => 'title', 'subtitle' => 'title', 'description' => 'content', 'button_text' => 'title'],
                    'slider_items'
                );
                $totalTranslated += $result['translated'] ?? 0;
                $totalSkipped += $result['skipped'] ?? 0;
                
                // 8. Slider Layers
                $result = $translateFromDatabase(
                    "SELECT * FROM slider_layers WHERE is_active = 1",
                    ['content' => 'content'],
                    'slider_layers'
                );
                $totalTranslated += $result['translated'] ?? 0;
                $totalSkipped += $result['skipped'] ?? 0;
                
                // 9. Forms
                $result = $translateFromDatabase(
                    "SELECT * FROM forms WHERE status = 'active'",
                    ['name' => 'title', 'description' => 'content', 'submit_button_text' => 'title', 'success_message' => 'content', 'error_message' => 'content'],
                    'forms'
                );
                $totalTranslated += $result['translated'] ?? 0;
                $totalSkipped += $result['skipped'] ?? 0;
                
                // 10. Form Fields
                $result = $translateFromDatabase(
                    "SELECT * FROM form_fields",
                    ['label' => 'title', 'placeholder' => 'title', 'help_text' => 'content'],
                    'form_fields'
                );
                $totalTranslated += $result['translated'] ?? 0;
                $totalSkipped += $result['skipped'] ?? 0;
                
                // 11. Categories
                $result = $translateFromDatabase(
                    "SELECT * FROM post_categories",
                    ['name' => 'title', 'description' => 'content'],
                    'categories'
                );
                $totalTranslated += $result['translated'] ?? 0;
                $totalSkipped += $result['skipped'] ?? 0;
                
                // 12. Tags
                $result = $translateFromDatabase(
                    "SELECT * FROM post_tags",
                    ['name' => 'title'],
                    'tags'
                );
                $totalTranslated += $result['translated'] ?? 0;
                $totalSkipped += $result['skipped'] ?? 0;
                
                // 13. Theme Options - Tema ayarları
                if ($category === 'all' || $category === 'theme_options') {
                    $themeOptions = $this->db->fetchAll("SELECT * FROM theme_options");
                    foreach ($themeOptions as $option) {
                        $value = $option['option_value'];
                        
                        // JSON ise decode et
                        $decoded = json_decode($value, true);
                        if (json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || is_object($decoded))) {
                            // Recursive olarak çevir
                            $optionTranslationCount = 0;
                            $translatedValue = $this->bulkTranslateService->translateSettingsRecursive($decoded, $targetLang, $sourceLang, $optionTranslationCount);
                            if ($translatedValue !== $decoded) {
                                // Çeviri yapıldı, kaydet
                                $this->db->execute(
                                    "UPDATE theme_options SET option_value = ? WHERE id = ?",
                                    [json_encode($translatedValue, JSON_UNESCAPED_UNICODE), $option['id']]
                                );
                                // Her bir çeviriyi sayacak ekle
                                $totalTranslated += $optionTranslationCount;
                            }
                        } else if (is_string($value) && !empty(trim($value))) {
                            // Tekil string değer
                            // Teknik değerleri atla (URL, renk, class vb.)
                            $key = $option['option_key'];
                            $noTranslateKeys = ['link', 'url', 'href', 'src', 'image', 'logo', 'favicon', 'icon', 'color', 'font', 'size', 'width', 'height', 'padding', 'margin', 'style', 'class', 'id', 'enabled', 'active', 'visible', 'show'];
                            $shouldTranslate = true;
                            foreach ($noTranslateKeys as $noTransKey) {
                                if (stripos($key, $noTransKey) !== false) {
                                    $shouldTranslate = false;
                                    break;
                                }
                            }
                            
                            if ($shouldTranslate && !$this->bulkTranslateService->shouldNotTranslate($value)) {
                                $result = $this->bulkTranslateService->translateAndSave($value, 'content', $targetLang, $sourceLang);
                                if ($result['translated']) {
                                    $totalTranslated++;
                                } else {
                                    $totalSkipped++;
                                }
                            }
                        }
                    }
                }
                
                // 14. Options - Sistem ayarları (site_name, site_description, company_name, vb.)
                if ($category === 'all' || $category === 'options') {
                    $options = $this->db->fetchAll("SELECT * FROM options");
                    foreach ($options as $option) {
                        $value = $option['option_value'];
                        $key = $option['option_name'];
                        
                        // Teknik değerleri atla
                        $noTranslateKeys = ['link', 'url', 'href', 'src', 'image', 'logo', 'favicon', 'icon', 'color', 'font', 'size', 'width', 'height', 'padding', 'margin', 'style', 'class', 'id', 'enabled', 'active', 'visible', 'show', 'email', 'phone', 'address', 'api', 'key', 'secret', 'token', 'password', 'hash', 'db_', 'database', 'host', 'port', 'charset'];
                        $shouldTranslate = true;
                        foreach ($noTranslateKeys as $noTransKey) {
                            if (stripos($key, $noTransKey) !== false) {
                                $shouldTranslate = false;
                                break;
                            }
                        }
                        
                        // Çevrilebilir option'lar: site_name, site_description, company_name, company_address, vb.
                        $translatableKeys = ['site_name', 'site_description', 'company_name', 'company_address', 'footer_text', 'header_text', 'copyright_text'];
                        $isTranslatableKey = false;
                        foreach ($translatableKeys as $transKey) {
                            if (stripos($key, $transKey) !== false) {
                                $isTranslatableKey = true;
                                break;
                            }
                        }
                        
                        if ($shouldTranslate && $isTranslatableKey && is_string($value) && !empty(trim($value)) && !$this->bulkTranslateService->shouldNotTranslate($value)) {
                            // Type belirle: kısa metinler title, uzun metinler content
                            $type = (strlen(trim($value)) <= 100) ? 'title' : 'content';
                            
                            $result = $this->bulkTranslateService->translateAndSave(trim($value), $type, $targetLang, $sourceLang);
                            if ($result['translated']) {
                                $totalTranslated++;
                            } else {
                                $totalSkipped++;
                            }
                        }
                    }
                }
                
                // 15. Page Sections - Sayfa section'ları (hero, pricing, vb.)
                if ($category === 'all' || $category === 'page_sections') {
                    $pageSections = $this->db->fetchAll("SELECT * FROM page_sections WHERE is_active = 1");
                    foreach ($pageSections as $section) {
                        // title, subtitle, description, content alanlarını çevir
                        $fieldsToTranslate = [
                            'title' => 'title',
                            'subtitle' => 'title',
                            'description' => 'content',
                            'content' => 'content'
                        ];
                        
                        foreach ($fieldsToTranslate as $field => $type) {
                            if (!empty($section[$field])) {
                                $result = $this->bulkTranslateService->translateAndSave($section[$field], $type, $targetLang, $sourceLang);
                                if ($result['translated']) {
                                    $totalTranslated++;
                                } else {
                                    $totalSkipped++;
                                }
                            }
                        }
                        
                        // items JSON alanını recursive çevir (packages, tabs, vb.)
                        if (!empty($section['items'])) {
                            $itemsDecoded = json_decode($section['items'], true);
                            if (json_last_error() === JSON_ERROR_NONE && (is_array($itemsDecoded) || is_object($itemsDecoded))) {
                                $itemsTranslationCount = 0;
                                $translatedItems = $this->bulkTranslateService->translateSettingsRecursive($itemsDecoded, $targetLang, $sourceLang, $itemsTranslationCount);
                                if ($translatedItems !== $itemsDecoded) {
                                    // Çeviri yapıldı, kaydet
                                    $this->db->query(
                                        "UPDATE page_sections SET items = ? WHERE id = ?",
                                        [json_encode($translatedItems, JSON_UNESCAPED_UNICODE), $section['id']]
                                    );
                                    // Her bir çeviriyi sayacak ekle (recursive içinde yapılan tüm çeviriler)
                                    $totalTranslated += $itemsTranslationCount;
                                }
                            }
                        }
                        
                        // settings JSON alanını recursive çevir
                        if (!empty($section['settings'])) {
                            $settingsDecoded = json_decode($section['settings'], true);
                            if (json_last_error() === JSON_ERROR_NONE && (is_array($settingsDecoded) || is_object($settingsDecoded))) {
                                $settingsTranslationCount = 0;
                                $translatedSettings = $this->bulkTranslateService->translateSettingsRecursive($settingsDecoded, $targetLang, $sourceLang, $settingsTranslationCount);
                                if ($translatedSettings !== $settingsDecoded) {
                                    // Çeviri yapıldı, kaydet
                                    $this->db->query(
                                        "UPDATE page_sections SET settings = ? WHERE id = ?",
                                        [json_encode($translatedSettings, JSON_UNESCAPED_UNICODE), $section['id']]
                                    );
                                    // Her bir çeviriyi sayacak ekle (recursive içinde yapılan tüm çeviriler)
                                    $totalTranslated += $settingsTranslationCount;
                                }
                            }
                        }
                    }
                }
                
                // Extract edilen metinleri çevir (title/content type'ları ile kaydedilmiş olanlar)
                // Sadece target_language boş olanları çevir (henüz çevrilmemiş olanlar)
                // Bu işlemi sadece 'all' veya 'pages' kategorisi işlenirken yap
                if ($category === 'all' || $category === 'pages') {
                    // Extract edilen metinleri bul (target_language boş ve translated_text boş olanlar)
                    $extractedStrings = $this->db->fetchAll(
                        "SELECT * FROM translations WHERE (target_language = '' OR target_language IS NULL) AND type IN ('title', 'content') AND source_text != '' AND (translated_text = '' OR translated_text IS NULL) LIMIT 1000"
                    );
                    
                    $extractedTranslated = 0;
                    $extractedSkipped = 0;
                    error_log("Found " . count($extractedStrings) . " extracted strings to translate");
                    
                    foreach ($extractedStrings as $extracted) {
                        $text = $extracted['source_text'];
                        $type = $extracted['type'];
                        
                        // Çeviri yap - saveTranslation artık bekleyen kaydı güncelliyor
                        $result = $this->bulkTranslateService->translateAndSave($text, $type, $targetLang, $sourceLang);
                        
                        if ($result['translated'] && !empty($result['translated_text'])) {
                            $totalTranslated++;
                            $extractedTranslated++;
                        } else {
                            $totalSkipped++;
                            $extractedSkipped++;
                        }
                    }
                    
                    error_log("Translated $extractedTranslated extracted strings, skipped $extractedSkipped");
                }
                
                // Note: page_sections, themes, site_options
                // would need recursive translation logic - simplified for now
                
                echo json_encode([
                    'success' => true,
                    'translated' => $totalTranslated,
                    'skipped' => $totalSkipped,
                    'message' => "$totalTranslated içerik çevrildi, $totalSkipped içerik atlandı"
                ]);
            } catch (Exception $e) {
                error_log("Bulk translate error: " . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => 'Çeviri işlemi sırasında bir hata oluştu: ' . htmlspecialchars($e->getMessage())
                ]);
            }
            exit;
        }
        
        // GET isteği - View render et
        $languages = $this->model->getActiveLanguages();
        
        $this->adminView('bulk-translate', [
            'title' => 'Toplu Çeviri',
            'languages' => $languages
        ]);
    }
    
    /**
     * Settings page
     */
    public function settings() {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->settings['default_language'] = $_POST['default_language'] ?? 'tr';
            $this->settings['auto_translate'] = isset($_POST['auto_translate']);
            $this->settings['deepl_api_key'] = $_POST['deepl_api_key'] ?? '';
            $this->settings['deepl_api_url'] = $_POST['deepl_api_url'] ?? 'https://api-free.deepl.com/v2/translate';
            
            ModuleLoader::getInstance()->saveModuleSettings('translation', $this->settings);
            
            $_SESSION['flash_message'] = 'Ayarlar kaydedildi';
            $_SESSION['flash_type'] = 'success';
            $this->redirect('settings');
            return;
        }
        
        $languages = $this->model->getActiveLanguages();
        
        $this->adminView('settings', [
            'title' => 'Dil Ayarları',
            'settings' => $this->settings,
            'languages' => $languages
        ]);
    }
    
    /**
     * Test translations endpoint
     */
    public function testTranslations() {
        $this->requireLogin();
        
        header('Content-Type: application/json');
        
        try {
            $testText = $_GET['text'] ?? 'Codetic Paketleri ve Fiyatları';
            $testLang = $_GET['lang'] ?? 'en';
            $action = $_GET['action'] ?? 'test';
            
            $textHash = md5(trim($testText));
            
            // Eğer action=fix ise eksik çevirileri ekle
            if ($action === 'fix') {
                $fixed = $this->fixMissingPageSectionTranslations($testLang);
                echo json_encode([
                    'success' => true,
                    'action' => 'fix',
                    'language' => $testLang,
                    'fixed_count' => $fixed,
                    'message' => "$testLang için $fixed adet eksik çeviri eklendi"
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // Eğer action=fix_all ise tüm aktif diller için eksik çevirileri ekle
            if ($action === 'fix_all') {
                $languages = $this->model->getActiveLanguages();
                $defaultLang = $this->settings['default_language'] ?? 'tr';
                $totalFixed = 0;
                $results = [];
                
                foreach ($languages as $lang) {
                    if ($lang['code'] !== $defaultLang) {
                        $fixed = $this->fixMissingPageSectionTranslations($lang['code']);
                        $totalFixed += $fixed;
                        $results[$lang['code']] = $fixed;
                    }
                }
                
                echo json_encode([
                    'success' => true,
                    'action' => 'fix_all',
                    'total_fixed' => $totalFixed,
                    'per_language' => $results,
                    'message' => "Tüm diller için toplam $totalFixed adet eksik çeviri eklendi"
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // Tüm type'lar ile dene
            $results = [
                'title' => $this->model->getTranslation('title', $textHash, $testLang, $testText),
                'content' => $this->model->getTranslation('content', $textHash, $testLang, $testText)
            ];
            
            // page_sections tablosundan pricing section'ı al
            $pricingSection = $this->db->fetch("SELECT id, section_id, title, subtitle FROM page_sections WHERE section_id = 'pricing' LIMIT 1");
            
            // Bu hash ile tüm çevirileri al
            $allTranslationsForHash = $this->db->fetchAll("SELECT id, type, target_language, translated_text FROM translations WHERE source_id = ?", [$textHash]);
            
            // source_text ile ara
            $bySourceText = $this->db->fetchAll("SELECT id, type, source_id, source_text, target_language, translated_text FROM translations WHERE source_text LIKE ? LIMIT 10", ['%Paket%']);
            
            // Son 20 EN çeviri
            $recentEn = $this->db->fetchAll("SELECT id, type, source_text, translated_text FROM translations WHERE target_language = 'en' ORDER BY id DESC LIMIT 20");
            
            // Toplam çeviri sayısı
            $totalCount = $this->db->fetch("SELECT COUNT(*) as cnt FROM translations WHERE target_language = 'en'");
            
            echo json_encode([
                'success' => true,
                'test_text' => $testText,
                'test_hash' => $textHash,
                'test_lang' => $testLang,
                'translation_results' => $results,
                'pricing_section' => $pricingSection,
                'translations_for_hash' => $allTranslationsForHash,
                'translations_by_source_text' => $bySourceText,
                'recent_en_translations' => $recentEn,
                'total_en_count' => $totalCount['cnt'] ?? 0,
                'fix_url' => '/admin/module/translation/test_translations?action=fix&lang=' . $testLang
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * Fix missing page_sections translations
     */
    private function fixMissingPageSectionTranslations($targetLang) {
        $fixed = 0;
        $sourceLang = $this->settings['default_language'] ?? 'tr';
        
        // 1. page_sections tablosundaki tüm aktif section'ları al
        $sections = $this->db->fetchAll("SELECT * FROM page_sections WHERE is_active = 1");
        
        foreach ($sections as $section) {
            // title, subtitle, description alanlarını çevir
            $fieldsToTranslate = [
                'title' => 'title',
                'subtitle' => 'title', 
                'description' => 'content',
                'content' => 'content'
            ];
            
            foreach ($fieldsToTranslate as $field => $type) {
                if (!empty($section[$field])) {
                    $text = trim($section[$field]);
                    $textHash = md5($text);
                    
                    // Bu çeviri zaten var mı?
                    $existing = $this->db->fetch(
                        "SELECT id FROM translations WHERE source_id = ? AND target_language = ?",
                        [$textHash, $targetLang]
                    );
                    
                    if (!$existing) {
                        // Çeviri yok, DeepL ile çevir
                        $result = $this->bulkTranslateService->translateAndSave($text, $type, $targetLang, $sourceLang);
                        if ($result['translated']) {
                            $fixed++;
                            error_log("Fixed translation for: $text");
                        }
                    }
                }
            }
            
            // items JSON alanını recursive çevir (packages, tabs, vb.)
            if (!empty($section['items'])) {
                $itemsDecoded = json_decode($section['items'], true);
                if (json_last_error() === JSON_ERROR_NONE && (is_array($itemsDecoded) || is_object($itemsDecoded))) {
                    $translatedItems = $this->translateAndSaveRecursive($itemsDecoded, $targetLang, $sourceLang, $fixed);
                    $fixed = $translatedItems['fixed'];
                }
            }
            
            // settings JSON alanını recursive çevir
            if (!empty($section['settings'])) {
                $settingsDecoded = json_decode($section['settings'], true);
                if (json_last_error() === JSON_ERROR_NONE && (is_array($settingsDecoded) || is_object($settingsDecoded))) {
                    $translatedSettings = $this->translateAndSaveRecursive($settingsDecoded, $targetLang, $sourceLang, $fixed);
                    $fixed = $translatedSettings['fixed'];
                }
            }
        }
        
        // 2. theme_options tablosundaki animated_words'ü çevir
        $themeOptions = $this->db->fetchAll("SELECT * FROM theme_options");
        foreach ($themeOptions as $option) {
            $key = $option['option_key'] ?? '';
            $value = $option['option_value'] ?? '';
            
            // animated_words gibi virgülle ayrılmış değerleri çevir
            if (strpos($key, 'animated_words') !== false && !empty($value)) {
                $words = array_map('trim', explode(',', $value));
                foreach ($words as $word) {
                    if (!empty($word)) {
                        $wordHash = md5($word);
                        $existing = $this->db->fetch(
                            "SELECT id FROM translations WHERE source_id = ? AND target_language = ?",
                            [$wordHash, $targetLang]
                        );
                        
                        if (!$existing) {
                            $result = $this->bulkTranslateService->translateAndSave($word, 'title', $targetLang, $sourceLang);
                            if ($result['translated']) {
                                $fixed++;
                                error_log("Fixed animated word translation: $word");
                            }
                        }
                    }
                }
            }
            
            // Diğer çevrilebilir theme option değerleri
            if (!empty($value) && is_string($value) && strlen($value) > 2 && strlen($value) < 500) {
                // Teknik değerleri atla
                $skipKeys = ['color', 'font', 'size', 'width', 'height', 'padding', 'margin', 'url', 'link', 'image', 'logo', 'icon', 'class', 'style', 'enabled', 'active'];
                $shouldSkip = false;
                foreach ($skipKeys as $skipKey) {
                    if (stripos($key, $skipKey) !== false) {
                        $shouldSkip = true;
                        break;
                    }
                }
                
                if (!$shouldSkip && !$this->bulkTranslateService->shouldNotTranslate($value)) {
                    $valueHash = md5(trim($value));
                    $existing = $this->db->fetch(
                        "SELECT id FROM translations WHERE source_id = ? AND target_language = ?",
                        [$valueHash, $targetLang]
                    );
                    
                    if (!$existing) {
                        $type = (strlen($value) <= 100) ? 'title' : 'content';
                        $result = $this->bulkTranslateService->translateAndSave(trim($value), $type, $targetLang, $sourceLang);
                        if ($result['translated']) {
                            $fixed++;
                            error_log("Fixed theme option translation ($key): $value");
                        }
                    }
                }
            }
        }
        
        return $fixed;
    }
    
    /**
     * Recursively translate and save array/object values
     */
    private function translateAndSaveRecursive($data, $targetLang, $sourceLang, $fixed = 0) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $result = $this->translateAndSaveRecursive($value, $targetLang, $sourceLang, $fixed);
                    $fixed = $result['fixed'];
                } else if (is_string($value) && !empty(trim($value))) {
                    // Teknik değerleri atla
                    if (!$this->bulkTranslateService->shouldNotTranslate($value)) {
                        $valueHash = md5(trim($value));
                        $existing = $this->db->fetch(
                            "SELECT id FROM translations WHERE source_id = ? AND target_language = ?",
                            [$valueHash, $targetLang]
                        );
                        
                        if (!$existing) {
                            $type = (strlen(trim($value)) <= 100) ? 'title' : 'content';
                            $result = $this->bulkTranslateService->translateAndSave(trim($value), $type, $targetLang, $sourceLang);
                            if ($result['translated']) {
                                $fixed++;
                                error_log("Fixed recursive translation: " . substr($value, 0, 50));
                            }
                        }
                    }
                }
            }
        } else if (is_object($data)) {
            $array = (array) $data;
            $result = $this->translateAndSaveRecursive($array, $targetLang, $sourceLang, $fixed);
            $fixed = $result['fixed'];
        } else if (is_string($data) && !empty(trim($data))) {
            if (!$this->bulkTranslateService->shouldNotTranslate($data)) {
                $dataHash = md5(trim($data));
                $existing = $this->db->fetch(
                    "SELECT id FROM translations WHERE source_id = ? AND target_language = ?",
                    [$dataHash, $targetLang]
                );
                
                if (!$existing) {
                    $type = (strlen(trim($data)) <= 100) ? 'title' : 'content';
                    $result = $this->bulkTranslateService->translateAndSave(trim($data), $type, $targetLang, $sourceLang);
                    if ($result['translated']) {
                        $fixed++;
                        error_log("Fixed recursive translation: " . substr($data, 0, 50));
                    }
                }
            }
        }
        
        return ['fixed' => $fixed];
    }
    
    /**
     * Cleanup translations
     */
    public function cleanupTranslations() {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            try {
                $deleteAll = isset($_POST['delete_all']) && $_POST['delete_all'] === '1';
                $action = $_POST['action'] ?? '';
                
                // Tüm çevirileri sil
                if ($deleteAll) {
                    // Önce kayıt sayısını al
                    $countResult = $this->db->fetch("SELECT COUNT(*) as total FROM translations");
                    $totalRecords = $countResult['total'] ?? 0;
                    
                    // Sonra tümünü sil
                    $this->db->query("DELETE FROM translations");
                    
                    echo json_encode([
                        'success' => true,
                        'message' => "Tüm çeviriler silindi. ($totalRecords kayıt)"
                    ]);
                    exit;
                }
                
                // Bozuk çevirileri temizle (teknik değerler, URL'ler, class isimleri vb.)
                // BulkTranslateService'deki shouldNotTranslate() mantığını kullan
                $deleted = 0;
                $translations = $this->db->fetchAll("SELECT * FROM translations WHERE target_language != '' AND target_language IS NOT NULL");
                
                foreach ($translations as $translation) {
                    $translatedText = $translation['translated_text'] ?? '';
                    $sourceText = $translation['source_text'] ?? '';
                    
                    // BulkTranslateService'in shouldNotTranslate mantığını kullan
                    // Eğer kaynak metin çevrilmemeli ise ve çevrilmiş ise, bu bozuk bir çeviridir
                    if ($this->bulkTranslateService->shouldNotTranslate($sourceText)) {
                        // Kaynak metin teknik bir değer, çevrilmemeli - sil
                        $this->model->deleteTranslation($translation['id']);
                        $deleted++;
                        continue;
                    }
                    
                    // Çevrilmiş metin de teknik bir değer görünüyorsa sil
                    if ($this->bulkTranslateService->shouldNotTranslate($translatedText)) {
                        $this->model->deleteTranslation($translation['id']);
                        $deleted++;
                        continue;
                    }
                    
                    // Çok kısa veya boş çevirileri sil
                    $translatedTextTrimmed = trim($translatedText);
                    if (empty($translatedTextTrimmed) || strlen($translatedTextTrimmed) <= 1) {
                        $this->model->deleteTranslation($translation['id']);
                        $deleted++;
                        continue;
                    }
                    
                    // Sadece özel karakter içeren çevirileri sil
                    if (preg_match('/^[\s\-_\.\/\\\\:;,!@#$%^&*()+=\[\]{}|<>?~`]+$/', $translatedTextTrimmed)) {
                        $this->model->deleteTranslation($translation['id']);
                        $deleted++;
                    }
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => "$deleted bozuk çeviri temizlendi. Yeniden toplu çeviri yapabilirsiniz."
                ]);
                exit;
                
            } catch (Exception $e) {
                error_log("Cleanup translations error: " . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => 'Hata: ' . htmlspecialchars($e->getMessage()),
                    'error_details' => $e->getTraceAsString()
                ]);
            }
            exit;
        }
        
        // GET request için form göster
        $this->adminView('bulk-translate', [
            'title' => 'Toplu Çeviri',
            'languages' => $this->model->getActiveLanguages()
        ]);
    }
    
    /**
     * Extract theme strings and save to database
     * 
     * @return array ['success' => bool, 'count' => int, 'message' => string]
     */
    public function extractThemeStrings() {
        $this->requireLogin();
        
        try {
            $extractor = new ThemeStringExtractor();
            $strings = $extractor->extractFromTheme();
            
            if (empty($strings)) {
                return [
                    'success' => false,
                    'count' => 0,
                    'message' => 'Tema dosyalarında çeviri metni bulunamadı'
                ];
            }
            
            $saved = 0;
            $skipped = 0;
            
            // Her metni translations tablosuna ekle
            foreach ($strings as $text => $count) {
                $textHash = md5(trim($text));
                $trimmedText = trim($text);
                
                // Metin tipini belirle (kısa metinler title, uzun metinler content)
                $type = (strlen($trimmedText) <= 100) ? 'title' : 'content';
                
                // HTML içerik kontrolü
                if (preg_match('/<[^>]+>/', $trimmedText)) {
                    $type = 'content';
                }
                
                // Zaten var mı kontrol et (source_id ve type ile)
                $existing = $this->model->getTranslation($type, $textHash, '', $trimmedText);
                
                if (!$existing) {
                    // Yeni ekle (henüz çevrilmedi, target_language boş)
                    // NOT: target_language boş olamaz, bu yüzden varsayılan dilde kaydet
                    // Ama aslında source_text olarak kaydetmek yeterli, bulk translate'te çevrilecek
                    // Şimdilik source_text olarak kaydet, bulk translate'te target_language ile çevrilecek
                    $result = $this->model->saveTranslation([
                        'type' => $type,
                        'source_id' => $textHash,
                        'source_text' => $trimmedText,
                        'target_language' => '', // Henüz çevrilmedi, bulk translate'te doldurulacak
                        'translated_text' => '', // Henüz çevrilmedi
                        'auto_translated' => 0
                    ]);
                    
                    if ($result) {
                        $saved++;
                    } else {
                        $skipped++;
                    }
                } else {
                    $skipped++;
                }
            }
            
            return [
                'success' => true,
                'count' => $saved,
                'skipped' => $skipped,
                'total' => count($strings),
                'message' => "$saved metin kaydedildi, $skipped metin zaten mevcut (toplam " . count($strings) . " metin bulundu)"
            ];
            
        } catch (Exception $e) {
            error_log("Extract theme strings error: " . $e->getMessage());
            return [
                'success' => false,
                'count' => 0,
                'message' => 'Hata: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Require login
     */
    private function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . admin_url('login'));
            exit;
        }
    }
    
    /**
     * Render admin view
     */
    private function adminView($view, $data = []) {
        $viewPath = __DIR__ . '/../views/admin/' . $view . '.php';
        // __DIR__ = modules/translation/Handlers/
        // dirname(__DIR__) = modules/translation/
        // dirname(dirname(__DIR__)) = modules/
        // dirname(dirname(dirname(__DIR__))) = root (public_html)
        $basePath = dirname(dirname(dirname(__DIR__)));
        
        if (!file_exists($viewPath)) {
            echo "View not found: " . $view;
            return;
        }
        
        extract($data);
        $currentPage = 'module/translation';
        
        // Admin layout'u yükle (mevcut Controller.php'deki gibi)
        include $basePath . '/app/views/admin/snippets/header.php';
        ?>
        <div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
            <div class="flex min-h-screen">
                <?php include $basePath . '/app/views/admin/snippets/sidebar.php'; ?>
                <div class="flex-1 flex flex-col lg:ml-64">
                    <?php include $basePath . '/app/views/admin/snippets/top-header.php'; ?>
                    <main class="flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b] overflow-y-auto">
                        <div class="max-w-7xl mx-auto">
                            <?php include $viewPath; ?>
                        </div>
                    </main>
                </div>
            </div>
        </div>
        <?php
        include $basePath . '/app/views/admin/snippets/footer.php';
    }
    
    /**
     * Redirect to admin action
     */
    private function redirect($action) {
        $url = admin_url('module/translation/' . $action);
        header("Location: " . $url);
        exit;
    }
}
