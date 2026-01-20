<?php
/**
 * Translation Service
 * 
 * Core translation logic - handles text translation, caching, and database operations
 */

require_once __DIR__ . '/../models/TranslationModel.php';

class TranslationService {
    
    private $model;
    private $languageService;
    private $settings;
    private $cache = [];
    
    public function __construct($languageService, $settings, $model = null) {
        $this->languageService = $languageService;
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
     * Translate text - Main entry point for translation
     * 
     * @param string $text Text to translate
     * @param string $domain Text domain (for future use, currently not used)
     * @return string Translated text or original if translation not found
     */
    public function translate($text, $domain = 'default') {
        if (empty($text) || !is_string($text)) {
            return $text;
        }
        
        // LanguageService'in detectLanguage() çağrıldığından emin ol
        if (!$this->languageService) {
            return $text;
        }
        
        $currentLang = $this->languageService->getCurrentLanguage();
        $defaultLang = $this->languageService->getDefaultLanguage();
        
        // Debug: Dil tespiti kontrolü (sadece ilk birkaç çağrıda log'la)
        static $debugCount = 0;
        static $lastLoggedLang = '';
        if ($debugCount < 10 || $lastLoggedLang !== $currentLang) {
            error_log("TranslationService: translate() called - currentLang='$currentLang', defaultLang='$defaultLang', text='" . substr($text, 0, 50) . "'");
            $debugCount++;
            $lastLoggedLang = $currentLang;
        }
        
        // Varsayılan dil ise çevirme - bu normal
        if ($currentLang === $defaultLang) {
            // Varsayılan dilde çeviri yok, orijinal metni döndür
            return $text;
        }
        
        // İçeriği normalize et
        $normalizedText = $this->normalizeText($text);
        if (empty($normalizedText)) {
            return $text;
        }
        
        // Cache kontrolü
        $cacheKey = md5($normalizedText . $currentLang);
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        // Metin tipini belirle (title veya content)
        // Kısa metinler (100 karakterden az) title, uzun metinler content olarak işaretlenir
        $textType = (strlen($normalizedText) <= 100) ? 'title' : 'content';
        
        // HTML içerik kontrolü - HTML içeriyorsa özel işlem yap
        if ($this->isHtmlContent($normalizedText)) {
            $textType = 'content';
            // HTML içerikleri parse ederek çevir
            return $this->translateHtmlContent($text);
        }
        
        // Hash oluştur (bulk translate ile aynı - trim edilmiş metinden)
        $textHash = md5($normalizedText);
        
        // Veritabanından çeviriyi getir
        $translation = $this->getTranslation($textType, $textHash, $currentLang, $normalizedText);
        
        if ($translation && !empty($translation['translated_text'])) {
            $this->cache[$cacheKey] = $translation['translated_text'];
            return $translation['translated_text'];
        }
        
        // DEBUG: Çeviri bulunamadı - detaylı log
        $this->ensureModel();
        $db = Database::getInstance();
        
        // Veritabanında bu hash ile kaç çeviri var?
        $allTranslations = $db->fetchAll(
            "SELECT id, type, source_text, target_language, translated_text FROM translations WHERE source_id = ? LIMIT 5",
            [$textHash]
        );
        
        if (!empty($allTranslations)) {
            $langs = array_unique(array_column($allTranslations, 'target_language'));
            $langsStr = implode(', ', array_filter($langs));
            error_log("TranslationService: Translation not found - text='$normalizedText' (first 50 chars), type='$textType', currentLang='$currentLang', hash='$textHash'. Found translations for languages: [$langsStr]");
        } else {
            error_log("TranslationService: Translation not found - text='$normalizedText' (first 50 chars), type='$textType', currentLang='$currentLang', hash='$textHash'. No translations found in database for this hash.");
        }
        
        // Veritabanında bu hash ile çeviri var mı kontrol et (debug için)
        $this->ensureModel();
        $anyTranslation = $this->model->getTranslation($textType, $textHash, $currentLang, $normalizedText);
        if (!$anyTranslation) {
            // Farklı type ile dene
            $altType = ($textType === 'title') ? 'content' : 'title';
            $anyTranslation = $this->model->getTranslation($altType, $textHash, $currentLang, $normalizedText);
            if ($anyTranslation) {
                error_log("TranslationService: Found translation with different type ($altType instead of $textType)");
            } else {
                // Veritabanında bu hash ile hiç çeviri var mı?
                $db = Database::getInstance();
                $checkHash = $db->fetch("SELECT COUNT(*) as cnt FROM translations WHERE source_id = ?", [$textHash]);
                if ($checkHash && $checkHash['cnt'] > 0) {
                    error_log("TranslationService: Hash exists in DB but not for language '$currentLang' (found " . $checkHash['cnt'] . " translations with this hash)");
                    // Hangi dillerde var?
                    $langs = $db->fetchAll("SELECT DISTINCT target_language FROM translations WHERE source_id = ?", [$textHash]);
                    $langList = array_column($langs, 'target_language');
                    error_log("TranslationService: Available languages for this hash: " . implode(', ', $langList));
                } else {
                    error_log("TranslationService: Hash '$textHash' does not exist in database at all");
                }
            }
        }
        
        // Çeviri yoksa orijinal metni döndür
        $this->cache[$cacheKey] = $text;
        return $text;
    }
    
    /**
     * Get translation from database
     * 
     * @param string $type Translation type (title/content)
     * @param string $sourceId Source text hash
     * @param string $targetLanguage Target language code
     * @param string|null $sourceText Optional source text for fallback lookup
     * @return array|null Translation data or null
     */
    public function getTranslation($type, $sourceId, $targetLanguage, $sourceText = null) {
        $this->ensureModel();
        return $this->model->getTranslation($type, $sourceId, $targetLanguage, $sourceText);
    }
    
    /**
     * Save translation to database
     * 
     * @param array $data Translation data
     * @return int|false Translation ID or false on error
     */
    public function saveTranslation($data) {
        $this->ensureModel();
        return $this->model->saveTranslation($data);
    }
    
    /**
     * Normalize text - trim and clean
     * 
     * @param string $text Text to normalize
     * @return string Normalized text
     */
    public function normalizeText($text) {
        return trim($text);
    }
    
    /**
     * Check if content is HTML
     * 
     * @param string $content Content to check
     * @return bool True if HTML content
     */
    public function isHtmlContent($content) {
        if (empty($content)) {
            return false;
        }
        
        // HTML tag'leri içeriyor mu kontrol et (<tag> veya </tag>)
        if (preg_match('/<[^>]+>/', $content)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Normalize translation type - converts various types to 'title' or 'content'
     * 
     * @param string $type Original type
     * @return string Normalized type ('title' or 'content')
     */
    public function normalizeTranslationType($type) {
        // Title grubu - kısa metinler, başlıklar
        $titleTypes = [
            'title', 'subtitle', 'label', 'name', 'badge', 'button', 'heading',
            'menu_name', 'menu_item_title', 'slider_name', 'slider_item_title',
            'slider_item_subtitle', 'slider_item_button', 'form_name', 'form_button',
            'form_field_label', 'form_field_placeholder', 'category_name', 'tag_name',
            'section_title', 'section_subtitle', 'section_setting', 'section_item',
            'theme_option'
        ];
        
        // Content grubu - uzun metinler, açıklamalar
        $contentTypes = [
            'content', 'description', 'excerpt', 'text', 'body',
            'menu_description', 'slider_description', 'slider_item_description',
            'slider_layer_content', 'form_description', 'form_success', 'form_error',
            'form_field_help', 'category_description', 'section_content'
        ];
        
        // Exact match kontrolü
        if (in_array($type, $titleTypes)) {
            return 'title';
        }
        if (in_array($type, $contentTypes)) {
            return 'content';
        }
        
        // Partial match - title içeren type'lar
        if (strpos($type, 'title') !== false || strpos($type, 'name') !== false || 
            strpos($type, 'label') !== false || strpos($type, 'button') !== false ||
            strpos($type, 'badge') !== false || strpos($type, 'heading') !== false) {
            return 'title';
        }
        
        // Partial match - content içeren type'lar
        if (strpos($type, 'content') !== false || strpos($type, 'description') !== false || 
            strpos($type, 'excerpt') !== false || strpos($type, 'text') !== false ||
            strpos($type, 'body') !== false) {
            return 'content';
        }
        
        // Varsayılan olarak title döndür (kısa metinler için)
        return 'title';
    }
    
    /**
     * Translate HTML content - parses HTML and translates text nodes only
     * Preserves HTML structure and attributes (style, class, id, etc.)
     * 
     * @param string $htmlContent HTML content to translate
     * @return string Translated HTML content
     */
    public function translateHtmlContent($htmlContent) {
        if (empty($htmlContent) || !is_string($htmlContent)) {
            return $htmlContent;
        }
        
        // HTML içerik değilse normal çeviri yap
        if (!$this->isHtmlContent($htmlContent)) {
            return $this->translate($htmlContent);
        }
        
        $currentLang = $this->languageService->getCurrentLanguage();
        $defaultLang = $this->languageService->getDefaultLanguage();
        
        // Varsayılan dil ise çevirme
        if ($currentLang === $defaultLang) {
            return $htmlContent;
        }
        
        // Cache kontrolü
        $cacheKey = md5('html:' . trim($htmlContent) . $currentLang);
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        try {
            // DOMDocument ile HTML'i parse et
            libxml_use_internal_errors(true);
            
            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->substituteEntities = false;
            
            // HTML içeriğini yükle
            $htmlContentEncoded = mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8');
            
            // XML encoding declaration ile yükle
            @$dom->loadHTML('<?xml encoding="UTF-8">' . $htmlContentEncoded, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            
            libxml_clear_errors();
            
            // Tüm metin node'larını bul ve çevir
            $xpath = new DOMXPath($dom);
            
            // Script ve style tag'lerini atla (sadece text node'ları al)
            $textNodes = $xpath->query('//text()[not(parent::script) and not(parent::style) and not(parent::noscript)]');
            
            foreach ($textNodes as $textNode) {
                $text = trim($textNode->nodeValue);
                
                // Boş veya sadece whitespace ise atla
                if (empty($text)) {
                    continue;
                }
                
                // Çok kısa metinler (1 karakter) veya sadece özel karakterler ise atla
                if (strlen($text) <= 1 || preg_match('/^[\s\-_\.\/\\\\:;,!@#$%^&*()+=\[\]{}|<>?~`]+$/', $text)) {
                    continue;
                }
                
                // Metni çevir (normalize edilmiş metin ile)
                $normalizedText = $this->normalizeText($text);
                $textHash = md5($normalizedText);
                $textType = (strlen($normalizedText) <= 100) ? 'title' : 'content';
                
                // Veritabanından çeviriyi getir
                $translation = $this->getTranslation($textType, $textHash, $currentLang, $normalizedText);
                
                if ($translation && !empty($translation['translated_text'])) {
                    $translatedText = $translation['translated_text'];
                    if ($translatedText !== $text) {
                        $textNode->nodeValue = $translatedText;
                    }
                }
            }
            
            // HTML'i string'e çevir
            $result = $dom->saveHTML();
            
            // XML encoding declaration'ı kaldır (DOMDocument tarafından eklenen)
            $result = preg_replace('/<\?xml encoding="UTF-8"\?>/', '', $result);
            
            // Boşlukları temizle
            $result = trim($result);
            
            // Cache'e kaydet
            $this->cache[$cacheKey] = $result;
            
            return $result;
            
        } catch (Exception $e) {
            // Hata durumunda orijinal içeriği döndür
            error_log("TranslationService::translateHtmlContent error: " . $e->getMessage());
            return $htmlContent;
        }
    }
    
    /**
     * Check if value should not be translated (technical values only)
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
     * Clear cache
     */
    public function clearCache() {
        $this->cache = [];
    }
}
