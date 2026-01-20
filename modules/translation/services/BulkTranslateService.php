<?php
/**
 * Bulk Translate Service
 * 
 * Handles bulk translation of all website content with flexible filtering
 */

require_once __DIR__ . '/TranslationService.php';
require_once __DIR__ . '/DeepLService.php';

class BulkTranslateService {
    
    private $db;
    private $translationService;
    private $deepLService;
    private $settings;
    
    public function __construct($translationService, $settings) {
        $this->db = Database::getInstance();
        $this->translationService = $translationService;
        $this->deepLService = new DeepLService();
        $this->settings = $settings;
    }
    
    /**
     * Check if text should be translated
     * Sadece gerçekten teknik değerleri filtreler (class, id, style, url, renk vb.)
     * Diğer tüm metinler çevrilir
     * 
     * @param string $value Text to check
     * @return bool True if should NOT translate, false if should translate
     */
    public function shouldNotTranslate($value) {
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
        
        // URL kontrolü - sadece gerçek URL'ler (http/https/mailto/tel ile başlayan)
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
        
        // Sayısal değerler (sadece sayılar, float dahil)
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
        
        // CSS class/id pattern - SADECE boşluksuz ve çok kısa olanlar (class="flex", id="nav")
        // Uzun metinler gerçek içerik olabilir
        if (strlen($value) <= 50 && !preg_match('/\s/', $value)) {
            // CSS class/id benzeri pattern (sadece harf, sayı, tire, underscore)
            // Ama anlamlı kelimeler içermiyorsa
            if (preg_match('/^[a-z0-9_-]+$/i', $value)) {
                // Eğer boşluk varsa, muhtemelen gerçek içerik
                // Eğer çok kısa ve sadece teknik karakterler ise, class/id olabilir
                // Ama anlamlı kelimeler içeriyorsa (örn: "Ana Sayfa", "Contact") çevrilmeli
                // Bu kontrolü atlıyoruz - sadece boşluksuz ve çok kısa olanlar zaten yukarıda kontrol edildi
            }
        }
        
        // Sadece özel karakterler (hiç harf/sayı yok)
        if (preg_match('/^[^a-zA-Z0-9]+$/', $value)) {
            return true;
        }
        
        // Diğer tüm durumlarda çevir
        return false;
    }
    
    /**
     * Translate and save text
     * 
     * @param string $text Text to translate
     * @param string $type Translation type
     * @param string $targetLang Target language
     * @param string $sourceLang Source language
     * @return array ['translated' => bool, 'skipped' => bool, 'translated_text' => string|null]
     */
    public function translateAndSave($text, $type, $targetLang, $sourceLang) {
        try {
            // Önce trim yap - frontend ile aynı hash'i kullanmak için
            $text = is_string($text) ? trim($text) : $text;
            
            if (empty($text)) {
                return ['translated' => false, 'skipped' => true, 'reason' => 'empty'];
            }
            
            // Teknik değerleri çevirme
            if ($this->shouldNotTranslate($text)) {
                return ['translated' => false, 'skipped' => true, 'reason' => 'technical'];
            }
            
            // Her zaman metnin kendisinin hash'ini kullan (aynı metin = aynı çeviri)
            $textHash = md5($text);
            
            // Type'ı normalize et - frontend'de tutarlı lookup için
            $normalizedType = $this->translationService->normalizeTranslationType($type);
            
            // ÖNEMLİ: Veritabanında zaten çeviri var mı kontrol et - varsa API çağrısı yapma (token tasarrufu)
            $existingTranslation = $this->translationService->getTranslation($normalizedType, $textHash, $targetLang, $text);
            if ($existingTranslation && !empty($existingTranslation['translated_text'])) {
                // Çeviri zaten var, API çağrısı yapma
                return ['translated' => true, 'skipped' => false, 'translated_text' => $existingTranslation['translated_text'], 'cached' => true];
            }
            
            // HTML içerik tespiti
            $isHtml = $this->translationService->isHtmlContent($text);
            
            // Çeviri yap - HTML ise özel işlem
            if ($isHtml) {
                $translatedText = $this->translateHtmlContentForBulk($text, $sourceLang, $targetLang);
            } else {
                $translatedText = $this->deepLService->translate($text, $sourceLang, $targetLang, false);
            }
            
            // DeepL API çeviri yapamazsa (false dönerse) atla
            if ($translatedText === false || empty($translatedText)) {
                error_log("DeepL translation failed for text: " . substr($text, 0, 50) . " (type: $type)");
                return ['translated' => false, 'skipped' => true, 'reason' => 'api_failed'];
            }
            
            // Çeviri başarılı ve boş değilse kaydet
            if ($translatedText && !empty(trim($translatedText))) {
                // Çeviriyi kaydet
                $this->translationService->saveTranslation([
                    'type' => $normalizedType,
                    'source_id' => $textHash,
                    'source_text' => $text, // Zaten trim edilmiş
                    'target_language' => $targetLang,
                    'translated_text' => trim($translatedText),
                    'auto_translated' => 1
                ]);
                return ['translated' => true, 'skipped' => false, 'translated_text' => trim($translatedText)];
            }
            
            return ['translated' => false, 'skipped' => true, 'reason' => 'translation_failed', 'translated_text' => null];
        } catch (Exception $e) {
            error_log("Translation error for type '$type': " . $e->getMessage());
            return ['translated' => false, 'skipped' => true, 'reason' => 'error', 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Translate HTML content for bulk translation
     * 
     * @param string $htmlContent HTML content
     * @param string $sourceLang Source language
     * @param string $targetLang Target language
     * @return string Translated HTML content
     */
    private function translateHtmlContentForBulk($htmlContent, $sourceLang, $targetLang) {
        if (empty($htmlContent) || !is_string($htmlContent)) {
            return $htmlContent;
        }
        
        // HTML içerik değilse direkt çevir
        if (!$this->translationService->isHtmlContent($htmlContent)) {
            $trimmed = trim($htmlContent);
            if (empty($trimmed)) {
                return $htmlContent;
            }
            return $this->deepLService->translate($trimmed, $sourceLang, $targetLang, false);
        }
        
        try {
            // DOMDocument ile HTML'i parse et
            libxml_use_internal_errors(true);
            
            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->substituteEntities = false;
            
            // HTML içeriğini yükle
            $htmlContent = mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8');
            
            @$dom->loadHTML('<?xml encoding="UTF-8">' . $htmlContent, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            
            libxml_clear_errors();
            
            // Tüm metin node'larını bul ve çevir
            $xpath = new DOMXPath($dom);
            
            // Script ve style tag'lerini atla
            $textNodes = $xpath->query('//text()[not(parent::script) and not(parent::style) and not(parent::noscript)]');
            
            foreach ($textNodes as $textNode) {
                $text = trim($textNode->nodeValue);
                
                // Boş veya sadece whitespace ise atla
                if (empty($text)) {
                    continue;
                }
                
                // Çok kısa metinler (1 karakter) veya sadece özel karakterler ise atla
                // 2+ karakter metinler çevrilmeli (örn: "OK", "test", "Başla")
                if (strlen($text) <= 1 || preg_match('/^[\s\-_\.\/\\\\:;,!@#$%^&*()+=\[\]{}|<>?~`]+$/', $text)) {
                    continue;
                }
                
                // Metni DeepL ile çevir (HTML modu ile değil, plain text olarak)
                $translatedText = $this->deepLService->translate($text, $sourceLang, $targetLang, false);
                
                // DeepL API çeviri yapamazsa (false dönerse) orijinal metni kullan
                if ($translatedText === false || empty($translatedText)) {
                    error_log("DeepL translation failed for text: " . substr($text, 0, 50));
                    continue; // Çeviri yapılamadı, atla
                }
                
                if ($translatedText && $translatedText !== $text) {
                    $textNode->nodeValue = $translatedText;
                }
            }
            
            // HTML'i string'e çevir
            $result = $dom->saveHTML();
            
            // XML encoding declaration'ı kaldır (DOMDocument tarafından eklenen)
            $result = preg_replace('/<\?xml encoding="UTF-8"\?>/', '', $result);
            
            // Boşlukları temizle
            $result = trim($result);
            
            return $result;
            
        } catch (Exception $e) {
            // Hata durumunda DeepL'in HTML modunu kullan (fallback)
            error_log("HTML translation error (fallback to DeepL HTML mode): " . $e->getMessage());
            return $this->deepLService->translate($htmlContent, $sourceLang, $targetLang, true);
        }
    }
    
    /**
     * Get content count for a category
     * 
     * @param string $category Category name
     * @return int Content count
     */
    public function getContentCount($category) {
        $count = 0;
        
        switch ($category) {
            case 'pages':
                $pages = $this->db->fetchAll("SELECT * FROM posts WHERE type = 'page' AND status = 'published'");
                foreach ($pages as $page) {
                    if (!empty($page['title'])) $count++;
                    if (!empty($page['content'])) $count++;
                    if (!empty($page['excerpt'])) $count++;
                }
                break;
            case 'posts':
                $posts = $this->db->fetchAll("SELECT * FROM posts WHERE type = 'post' AND status = 'published'");
                foreach ($posts as $post) {
                    if (!empty($post['title'])) $count++;
                    if (!empty($post['content'])) $count++;
                    if (!empty($post['excerpt'])) $count++;
                }
                break;
            case 'theme_options':
                $themeOptions = $this->db->fetchAll("SELECT * FROM theme_options");
                foreach ($themeOptions as $option) {
                    $value = $option['option_value'];
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || is_object($decoded))) {
                        // JSON array/object - recursive count
                        $count += $this->countTranslatableInArray($decoded);
                    } else if (is_string($value) && !empty(trim($value))) {
                        // String value
                        $key = $option['option_key'];
                        $noTranslateKeys = ['link', 'url', 'href', 'src', 'image', 'logo', 'favicon', 'icon', 'color', 'font', 'size', 'width', 'height', 'padding', 'margin', 'style', 'class', 'id', 'enabled', 'active', 'visible', 'show'];
                        $shouldTranslate = true;
                        foreach ($noTranslateKeys as $noTransKey) {
                            if (stripos($key, $noTransKey) !== false) {
                                $shouldTranslate = false;
                                break;
                            }
                        }
                        if ($shouldTranslate && !$this->shouldNotTranslate($value)) {
                            $count++;
                        }
                    }
                }
                break;
            case 'page_sections':
                // page_sections tablosundaki JSON verilerini say
                $pageSections = $this->db->fetchAll("SELECT * FROM page_sections WHERE is_active = 1");
                foreach ($pageSections as $section) {
                    // title, subtitle, description, content alanlarını say
                    if (!empty($section['title'])) $count++;
                    if (!empty($section['subtitle'])) $count++;
                    if (!empty($section['description'])) $count++;
                    if (!empty($section['content'])) $count++;
                    
                    // items JSON alanını recursive say (packages, tabs, vb.)
                    if (!empty($section['items'])) {
                        $itemsDecoded = json_decode($section['items'], true);
                        if (json_last_error() === JSON_ERROR_NONE && (is_array($itemsDecoded) || is_object($itemsDecoded))) {
                            $count += $this->countTranslatableInArray($itemsDecoded);
                        }
                    }
                    
                    // settings JSON alanını recursive say
                    if (!empty($section['settings'])) {
                        $settingsDecoded = json_decode($section['settings'], true);
                        if (json_last_error() === JSON_ERROR_NONE && (is_array($settingsDecoded) || is_object($settingsDecoded))) {
                            $count += $this->countTranslatableInArray($settingsDecoded);
                        }
                    }
                }
                break;
            case 'options':
                // options tablosundaki çevrilebilir değerleri say
                $options = $this->db->fetchAll("SELECT * FROM options");
                foreach ($options as $option) {
                    $value = $option['option_value'];
                    $key = $option['option_name'];
                    
                    // Teknik değerleri atla
                    $noTranslateKeys = ['link', 'url', 'href', 'src', 'image', 'logo', 'favicon', 'icon', 'color', 'font', 'size', 'width', 'height', 'padding', 'margin', 'style', 'class', 'id', 'enabled', 'active', 'visible', 'show', 'email', 'phone', 'address', 'api', 'key', 'secret', 'token', 'password', 'hash'];
                    $shouldTranslate = true;
                    foreach ($noTranslateKeys as $noTransKey) {
                        if (stripos($key, $noTransKey) !== false) {
                            $shouldTranslate = false;
                            break;
                        }
                    }
                    
                    if ($shouldTranslate && is_string($value) && !empty(trim($value)) && !$this->shouldNotTranslate($value)) {
                        $count++;
                    }
                }
                break;
            // Hardcoded kategorisi kaldırıldı - extract edilen metinler artık title/content olarak kaydediliyor
            // Bu case artık gerekli değil
            // Diğer kategoriler için benzer mantık...
            default:
                // Tüm kategoriler için toplam sayı
                $count = $this->getTotalContentCount();
                break;
        }
        
        return $count;
    }
    
    /**
     * Get total content count across all categories
     * 
     * @return int Total count
     */
    private function getTotalContentCount() {
        $total = 0;
        
        $categories = [
            'pages', 'posts', 'agreements', 'menus', 'menu_items',
            'sliders', 'slider_items', 'slider_layers', 'forms', 'form_fields',
            'categories', 'tags', 'page_sections', 'theme_options', 'themes',
            'site_options', 'options'
        ];
        
        foreach ($categories as $category) {
            $total += $this->getContentCount($category);
        }
        
        return $total;
    }
    
    /**
     * Translate content from database
     * 
     * @param string $sql SQL query
     * @param array $fields Field mapping ['field' => 'type']
     * @param string $category Category name
     * @param string $targetLang Target language
     * @param string $sourceLang Source language
     * @return array ['translated' => int, 'skipped' => int]
     */
    public function translateFromDatabase($sql, $fields, $category, $targetLang, $sourceLang) {
        $translated = 0;
        $skipped = 0;
        
        $items = $this->db->fetchAll($sql);
        foreach ($items as $item) {
            foreach ($fields as $field => $type) {
                if (!empty($item[$field])) {
                    $result = $this->translateAndSave($item[$field], $type, $targetLang, $sourceLang);
                    if ($result['translated']) {
                        $translated++;
                    } else {
                        $skipped++;
                    }
                }
            }
        }
        
        return ['translated' => $translated, 'skipped' => $skipped];
    }
    
    /**
     * Recursively translate settings array/object
     * 
     * @param mixed $data Data to translate (array, object, or string)
     * @param string $targetLang Target language
     * @param string $sourceLang Source language
     * @param int &$translationCount Reference to translation count (output parameter)
     * @return mixed Translated data
     */
    public function translateSettingsRecursive($data, $targetLang, $sourceLang, &$translationCount = 0) {
        if (is_array($data)) {
            $translated = [];
            foreach ($data as $key => $value) {
                // Key'i çevirme (teknik key'ler)
                $translatedKey = $key;
                
                // Value'yu recursive olarak çevir
                if (is_array($value) || is_object($value)) {
                    $translated[$translatedKey] = $this->translateSettingsRecursive($value, $targetLang, $sourceLang, $translationCount);
                } else if (is_string($value) && !empty(trim($value))) {
                    // String değer - teknik değerleri atla
                    if (!$this->shouldNotTranslate($value)) {
                        // Virgülle ayrılmış değerleri kontrol et (animated_words gibi)
                        if (strpos($value, ',') !== false && strpos($key, 'words') !== false) {
                            // Virgülle ayrılmış her kelimeyi ayrı ayrı çevir
                            $words = array_map('trim', explode(',', $value));
                            $translatedWords = [];
                            foreach ($words as $word) {
                                if (!empty($word) && !$this->shouldNotTranslate($word)) {
                                    $wordResult = $this->translateAndSave($word, 'title', $targetLang, $sourceLang);
                                    if ($wordResult['translated'] && !empty($wordResult['translated_text'])) {
                                        $translatedWords[] = $wordResult['translated_text'];
                                        $translationCount++; // Çeviri sayısını artır
                                    } else {
                                        $translatedWords[] = $word;
                                    }
                                } else {
                                    $translatedWords[] = $word;
                                }
                            }
                            $translated[$translatedKey] = implode(',', $translatedWords);
                        } else {
                            $result = $this->translateAndSave($value, 'content', $targetLang, $sourceLang);
                            if ($result['translated'] && !empty($result['translated_text'])) {
                                $translated[$translatedKey] = $result['translated_text'];
                                $translationCount++; // Çeviri sayısını artır
                            } else {
                                $translated[$translatedKey] = $value; // Çevrilemediyse orijinal
                            }
                        }
                    } else {
                        $translated[$translatedKey] = $value; // Teknik değer, çevirme
                    }
                } else {
                    $translated[$translatedKey] = $value; // String değil veya boş
                }
            }
            return $translated;
        } else if (is_object($data)) {
            // Object'i array'e çevir, çevir, sonra tekrar object yap
            $array = (array) $data;
            $translated = $this->translateSettingsRecursive($array, $targetLang, $sourceLang, $translationCount);
            return (object) $translated;
        } else if (is_string($data) && !empty(trim($data))) {
            // Tekil string
            if (!$this->shouldNotTranslate($data)) {
                $result = $this->translateAndSave($data, 'content', $targetLang, $sourceLang);
                if ($result['translated'] && !empty($result['translated_text'])) {
                    $translationCount++; // Çeviri sayısını artır
                    return $result['translated_text'];
                }
            }
            return $data; // Çevrilemediyse orijinal
        }
        
        return $data; // Değiştirilemez tip
    }
    
    /**
     * Count translatable items in array/object recursively
     * 
     * @param mixed $data Data to count
     * @return int Count of translatable items
     */
    private function countTranslatableInArray($data) {
        $count = 0;
        
        if (is_array($data) || is_object($data)) {
            foreach ($data as $value) {
                if (is_array($value) || is_object($value)) {
                    $count += $this->countTranslatableInArray($value);
                } else if (is_string($value) && !empty(trim($value)) && !$this->shouldNotTranslate($value)) {
                    $count++;
                }
            }
        } else if (is_string($data) && !empty(trim($data)) && !$this->shouldNotTranslate($data)) {
            $count = 1;
        }
        
        return $count;
    }
}
