<?php
/**
 * Page Model - Statik Sayfalar (Theme Module)
 * Posts tablosunu kullanır (type='page' ile)
 */

class PageModel extends Model {
    protected $table = 'posts';
    
    /**
     * Özel alan tanımları (yapılandırılmış alanlar)
     */
    private static $customFieldDefinitions = [
        // Temel Alanlar
        'hero_subtitle' => [
            'type' => 'text',
            'label' => 'Hero Alt Başlık',
            'placeholder' => 'Örn: Profesyonel Çözümler',
            'group' => 'hero',
            'description' => 'Sayfa başlığının üstünde görünecek etiket'
        ],
        'hero_image' => [
            'type' => 'image',
            'label' => 'Hero Görsel',
            'description' => 'Sayfa üst kısmında gösterilecek ana görsel',
            'group' => 'hero'
        ],

        // Hakkımızda Özel Alanları
        'about_sections' => [
            'type' => 'repeater',
            'label' => 'Hikâye / Bölümler',
            'description' => 'Öykü, misyon, vizyon vb. içerik blokları',
            'group' => 'about',
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'Başlık'],
                'content' => ['type' => 'textarea', 'label' => 'İçerik'],
                'image' => ['type' => 'image', 'label' => 'Görsel']
            ]
        ],
        'team_members' => [
            'type' => 'repeater',
            'label' => 'Ekip Üyeleri',
            'description' => 'Ekip foto ve pozisyonları',
            'group' => 'team',
            'fields' => [
                'photo' => ['type' => 'image', 'label' => 'Fotoğraf'],
                'name' => ['type' => 'text', 'label' => 'İsim'],
                'position' => ['type' => 'text', 'label' => 'Pozisyon']
            ]
        ],
        'stats' => [
            'type' => 'repeater',
            'label' => 'İstatistikler',
            'description' => 'Yıllar, proje sayısı vb.',
            'group' => 'stats',
            'fields' => [
                'number' => ['type' => 'text', 'label' => 'Sayı'],
                'label' => ['type' => 'text', 'label' => 'Etiket']
            ]
        ],
        
        // Hizmet Özellikleri
        'service_features' => [
            'type' => 'repeater',
            'label' => 'Hizmet Özellikleri',
            'description' => 'Hizmetin öne çıkan özellikleri',
            'group' => 'features',
            'fields' => [
                'icon' => ['type' => 'text', 'label' => 'İkon', 'placeholder' => 'check_circle'],
                'title' => ['type' => 'text', 'label' => 'Başlık'],
                'description' => ['type' => 'textarea', 'label' => 'Açıklama']
            ]
        ],
        
        // Süreç Adımları
        'process_steps' => [
            'type' => 'repeater',
            'label' => 'Süreç Adımları',
            'description' => 'Çalışma sürecinizin adımları',
            'group' => 'process',
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'Başlık'],
                'description' => ['type' => 'textarea', 'label' => 'Açıklama']
            ]
        ],
        
        // Avantajlar
        'advantages' => [
            'type' => 'repeater',
            'label' => 'Avantajlar',
            'description' => 'İstatistik veya avantaj kartları',
            'group' => 'advantages',
            'fields' => [
                'icon' => ['type' => 'text', 'label' => 'İkon', 'placeholder' => 'verified'],
                'value' => ['type' => 'text', 'label' => 'Değer', 'placeholder' => '100+'],
                'label' => ['type' => 'text', 'label' => 'Etiket', 'placeholder' => 'Mutlu Müşteri']
            ]
        ],
        
        // SSS
        'faqs' => [
            'type' => 'repeater',
            'label' => 'Sıkça Sorulan Sorular',
            'description' => 'Soru-cevap şeklinde SSS bölümü',
            'group' => 'faq',
            'fields' => [
                'question' => ['type' => 'text', 'label' => 'Soru'],
                'answer' => ['type' => 'textarea', 'label' => 'Cevap']
            ]
        ],
        
        // İlgili Hizmetler
        'related_services' => [
            'type' => 'repeater',
            'label' => 'İlgili Hizmetler',
            'description' => 'Sayfa altında gösterilecek diğer hizmetler',
            'group' => 'related',
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'Başlık'],
                'description' => ['type' => 'text', 'label' => 'Açıklama'],
                'link' => ['type' => 'text', 'label' => 'Link'],
                'image' => ['type' => 'image', 'label' => 'Görsel']
            ]
        ],
        
        // CTA Ayarları
        'cta_title' => [
            'type' => 'text',
            'label' => 'CTA Başlık',
            'placeholder' => 'Projenizi Başlatalım',
            'group' => 'cta',
            'default' => 'Projenizi Başlatalım'
        ],
        'cta_description' => [
            'type' => 'textarea',
            'label' => 'CTA Açıklama',
            'placeholder' => 'Hemen iletişime geçin...',
            'group' => 'cta'
        ],
        'cta_button_text' => [
            'type' => 'text',
            'label' => 'CTA Buton Metni',
            'placeholder' => 'Teklif Al',
            'group' => 'cta',
            'default' => 'Teklif Al'
        ],
        'cta_button_link' => [
            'type' => 'text',
            'label' => 'CTA Buton Linki',
            'placeholder' => '/contact',
            'group' => 'cta',
            'default' => '/contact'
        ],
        
        // İletişim Şablonu Alanları
        'contact_email' => [
            'type' => 'text',
            'label' => 'E-posta Adresi',
            'placeholder' => 'info@example.com',
            'group' => 'contact_info'
        ],
        'contact_phone' => [
            'type' => 'text',
            'label' => 'Telefon',
            'placeholder' => '+90 555 123 4567',
            'group' => 'contact_info'
        ],
        'contact_address' => [
            'type' => 'textarea',
            'label' => 'Adres',
            'placeholder' => 'Şirket adresi...',
            'group' => 'contact_info'
        ],
        'contact_hours' => [
            'type' => 'text',
            'label' => 'Çalışma Saatleri',
            'placeholder' => 'Pzt-Cum: 09:00-18:00',
            'group' => 'contact_info'
        ],
        'map_embed' => [
            'type' => 'textarea',
            'label' => 'Google Maps Embed Kodu',
            'placeholder' => '<iframe src="..." />',
            'group' => 'map',
            'description' => 'Google Maps\'ten embed kodunu buraya yapıştırın'
        ],
        'map_latitude' => [
            'type' => 'text',
            'label' => 'Enlem (Latitude)',
            'placeholder' => '41.0082',
            'group' => 'map'
        ],
        'map_longitude' => [
            'type' => 'text',
            'label' => 'Boylam (Longitude)',
            'placeholder' => '28.9784',
            'group' => 'map'
        ],
        'form_id' => [
            'type' => 'select',
            'label' => 'İletişim Formu',
            'options' => [],
            'group' => 'form',
            'description' => 'Kullanılacak form (Form modülünden)'
        ],
        'form_title' => [
            'type' => 'text',
            'label' => 'Form Başlığı',
            'placeholder' => 'Bize Ulaşın',
            'group' => 'form',
            'default' => 'Bize Ulaşın'
        ],
        'form_description' => [
            'type' => 'textarea',
            'label' => 'Form Açıklaması',
            'placeholder' => 'Size en kısa sürede dönüş yapacağız.',
            'group' => 'form'
        ],
        'social_facebook' => [
            'type' => 'text',
            'label' => 'Facebook URL',
            'placeholder' => 'https://facebook.com/...',
            'group' => 'social'
        ],
        'social_twitter' => [
            'type' => 'text',
            'label' => 'Twitter/X URL',
            'placeholder' => 'https://twitter.com/...',
            'group' => 'social'
        ],
        'social_instagram' => [
            'type' => 'text',
            'label' => 'Instagram URL',
            'placeholder' => 'https://instagram.com/...',
            'group' => 'social'
        ],
        'social_linkedin' => [
            'type' => 'text',
            'label' => 'LinkedIn URL',
            'placeholder' => 'https://linkedin.com/...',
            'group' => 'social'
        ],
        
        // Sayfa Ayarları (gizli - sadece form submission için)
        'page_template' => [
            'type' => 'hidden',
            'default' => 'default',
            'group' => 'hidden'
        ]
    ];
    
    /**
     * Özel alan tanımlarını getirir
     */
    public static function getCustomFieldDefinitions() {
        return self::$customFieldDefinitions;
    }
    
    /**
     * Tüm sayfaları getirir (yazar bilgisi ile)
     */
    public function getAll($orderBy = 'created_at DESC') {
        $sql = "SELECT p.*, 
                       u.username as author_name
                FROM `{$this->table}` p
                LEFT JOIN `users` u ON p.author_id = u.id
                WHERE p.type = 'page'
                ORDER BY {$orderBy}";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Yayınlanmış sayfaları getirir
     */
    public function getPublished($limit = null, $offset = 0) {
        $sql = "SELECT p.*, 
                       u.username as author_name
                FROM `{$this->table}` p
                LEFT JOIN `users` u ON p.author_id = u.id
                WHERE p.type = 'page'
                AND p.status = 'published' 
                AND p.visibility = 'public'
                AND (p.published_at IS NULL OR p.published_at <= NOW())
                ORDER BY p.published_at DESC, p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$offset}, {$limit}";
        }
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Slug'a göre sayfa getirir
     */
    public function findBySlug($slug) {
        $sql = "SELECT p.*, 
                       u.username as author_name
                FROM `{$this->table}` p
                LEFT JOIN `users` u ON p.author_id = u.id
                WHERE p.slug = ? 
                AND p.type = 'page'";
        return $this->db->fetch($sql, [$slug]);
    }
    
    /**
     * ID'ye göre sayfa getirir (detaylı)
     */
    public function findWithDetails($id) {
        $sql = "SELECT p.*, 
                       u.username as author_name
                FROM `{$this->table}` p
                LEFT JOIN `users` u ON p.author_id = u.id
                WHERE p.id = ? 
                AND p.type = 'page'";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Sayfa oluşturur
     */
    public function createPage($data) {
        // Özel alanları ayır (posts tablosuna yazılmamalı)
        $customFields = $data['custom_fields'] ?? [];
        unset($data['custom_fields']);
        
        // Type'ı page olarak ayarla
        $data['type'] = 'page';
        
        // Slug oluştur
        if (empty($data['slug'])) {
            $data['slug'] = $this->createSlug($data['title']);
        }
        
        // Yayın tarihi
        if (isset($data['status']) && $data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        
        $pageId = $this->create($data);
        
        // Özel alanları kaydet
        if ($pageId && !empty($customFields)) {
            $this->saveCustomFields($pageId, $customFields);
        }
        
        return $pageId;
    }
    
    /**
     * Sayfa günceller
     */
    public function updatePage($id, $data) {
        // Özel alanları ayır (posts tablosuna yazılmamalı)
        $customFields = $data['custom_fields'] ?? [];
        unset($data['custom_fields']);
        
        // Type'ı koru
        $data['type'] = 'page';
        
        // Slug kontrolü ve güncelleme
        $currentPage = $this->find($id);
        if (isset($data['slug']) && !empty($data['slug'])) {
            // Slug değişmemişse data'dan çıkar (tekrar güncelleme)
            if ($data['slug'] === $currentPage['slug']) {
                unset($data['slug']);
            } else {
                // Slug değiştirilmişse benzersizlik kontrolü yap
                if ($this->slugExists($data['slug'], $id)) {
                    $data['slug'] = $this->createSlug($data['slug'], $id);
                }
            }
        } elseif (isset($data['title']) && empty($data['slug'])) {
            // Slug boşsa title'dan oluştur
            $data['slug'] = $this->createSlug($data['title'], $id);
        }
        
        // Yayınlandıysa ve tarih yoksa ekle
        if (isset($data['status']) && $data['status'] === 'published') {
            if (empty($currentPage['published_at']) && empty($data['published_at'])) {
                $data['published_at'] = date('Y-m-d H:i:s');
            }
        }
        
        $result = $this->update($id, $data);
        
        // Özel alanları kaydet
        if ($result && !empty($customFields)) {
            $this->saveCustomFields($id, $customFields);
        }
        
        return $result;
    }
    
    /**
     * Duruma göre sayı getirir
     */
    public function getCountByStatus($status = null) {
        if ($status) {
            $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `type` = 'page' AND `status` = ?";
            $result = $this->db->fetch($sql, [$status]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `type` = 'page'";
            $result = $this->db->fetch($sql);
        }
        return $result['count'] ?? 0;
    }
    
    /**
     * Görüntülenme sayısını artırır
     */
    public function incrementViews($id) {
        $sql = "UPDATE `{$this->table}` SET `views` = `views` + 1 WHERE `id` = ? AND `type` = 'page'";
        return $this->db->query($sql, [$id]);
    }
    
    // ==================== ÖZEL ALAN YÖNETİMİ ====================
    
    /**
     * Sayfanın özel alanlarını getirir
     */
    public function getCustomFields($pageId) {
        $sql = "SELECT meta_key, meta_value FROM `page_meta` WHERE page_id = ?";
        $results = $this->db->fetchAll($sql, [$pageId]);
        
        $fields = [];
        foreach ($results as $row) {
            $fields[$row['meta_key']] = $row['meta_value'];
        }
        
        return $fields;
    }
    
    /**
     * Tek bir özel alan değerini getirir
     */
    public function getCustomField($pageId, $key, $default = null) {
        $sql = "SELECT meta_value FROM `page_meta` WHERE page_id = ? AND meta_key = ?";
        $result = $this->db->fetch($sql, [$pageId, $key]);
        return $result ? $result['meta_value'] : $default;
    }
    
    /**
     * Özel alanları kaydeder
     */
    public function saveCustomFields($pageId, $fields) {
        // Önce mevcut alanları sil
        $this->deleteCustomFields($pageId);
        
        // Yeni alanları ekle
        foreach ($fields as $key => $value) {
            // Checkbox için '0' değeri de kaydedilmeli
            if ($value !== null && $value !== '') {
                $this->setCustomField($pageId, $key, $value);
            } elseif (isset($fields[$key]) && $value === '0') {
                // Checkbox için açıkça '0' değeri kaydet
                $this->setCustomField($pageId, $key, '0');
            }
        }
    }
    
    /**
     * Tek bir özel alan kaydeder
     */
    public function setCustomField($pageId, $key, $value) {
        $sql = "INSERT INTO `page_meta` (page_id, meta_key, meta_value) 
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE meta_value = ?";
        return $this->db->query($sql, [$pageId, $key, $value, $value]);
    }
    
    /**
     * Özel alanları siler
     */
    public function deleteCustomFields($pageId) {
        $sql = "DELETE FROM `page_meta` WHERE page_id = ?";
        return $this->db->query($sql, [$pageId]);
    }
    
    /**
     * Tek bir özel alan siler
     */
    public function deleteCustomField($pageId, $key) {
        $sql = "DELETE FROM `page_meta` WHERE page_id = ? AND meta_key = ?";
        return $this->db->query($sql, [$pageId, $key]);
    }
    
    // ==================== VERSİYON YÖNETİMİ ====================
    
    /**
     * Sayfa günceller (versiyon kaydıyla birlikte)
     */
    public function updateWithVersion($id, $data, $userId = null) {
        // Mevcut sayfayı al
        $currentPage = $this->find($id);
        
        if (!$currentPage) {
            return false;
        }
        
        try {
            // Versiyon modeli
            if (!class_exists('PostVersion')) {
                require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/PostVersion.php';
            }
            $versionModel = new PostVersion();
            
            // İçerik değişmiş mi kontrol et
            $contentChanged = (
                $currentPage['title'] !== ($data['title'] ?? '') ||
                $currentPage['content'] !== ($data['content'] ?? '') ||
                $currentPage['excerpt'] !== ($data['excerpt'] ?? '')
            );
            
            // Eğer içerik değiştiyse, mevcut versiyonu kaydet
            if ($contentChanged) {
                $versionModel->createVersion($id, $currentPage, $userId);
                
                // Versiyon numarasını artır
                $data['version'] = ($currentPage['version'] ?? 1) + 1;
                
                // Eski versiyonları temizle (20'den fazla tutma)
                $versionModel->cleanOldVersions($id, 20);
            }
        } catch (Exception $e) {
            // Versiyon hatası logla ama güncellemeye devam et
            error_log('Page version save error: ' . $e->getMessage());
        }
        
        // Sayfayı güncelle (versiyon hatası olsa bile)
        return $this->updatePage($id, $data);
    }
    
    /**
     * Sayfanın versiyon geçmişini getirir
     */
    public function getVersions($pageId) {
        if (!class_exists('PostVersion')) {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/PostVersion.php';
        }
        $versionModel = new PostVersion();
        return $versionModel->getByPostId($pageId);
    }
    
    /**
     * Eski versiyona geri döner
     */
    public function restoreVersion($versionId, $userId = null) {
        if (!class_exists('PostVersion')) {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/PostVersion.php';
        }
        $versionModel = new PostVersion();
        
        $version = $versionModel->findWithDetails($versionId);
        
        if (!$version) {
            return false;
        }
        
        $pageId = $version['post_id'];
        $currentPage = $this->find($pageId);
        
        if (!$currentPage) {
            return false;
        }
        
        // Mevcut versiyonu kaydet
        $versionModel->createVersion($pageId, $currentPage, $userId);
        
        // Eski versiyonu geri yükle
        $restoreData = [
            'title' => $version['title'],
            'slug' => $version['slug'],
            'excerpt' => $version['excerpt'],
            'content' => $version['content'],
            'meta_title' => $version['meta_title'],
            'meta_description' => $version['meta_description'],
            'meta_keywords' => $version['meta_keywords'],
            'version' => ($currentPage['version'] ?? 1) + 1
        ];
        
        return $this->update($pageId, $restoreData);
    }
    
    // ==================== YARDIMCI METODLAR ====================
    
    /**
     * Benzersiz slug oluşturur
     */
    private function createSlug($title, $excludeId = null) {
        $slug = $this->slugify($title);
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Slug var mı kontrol eder
     */
    private function slugExists($slug, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `slug` = ? AND `type` = 'page' AND `id` != ?";
            $result = $this->db->fetch($sql, [$slug, $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `slug` = ? AND `type` = 'page'";
            $result = $this->db->fetch($sql, [$slug]);
        }
        
        return ($result['count'] ?? 0) > 0;
    }
    
    /**
     * Metni slug'a çevirir
     */
    private function slugify($text) {
        // Türkçe karakterleri dönüştür
        $tr = ['ş', 'Ş', 'ı', 'İ', 'ğ', 'Ğ', 'ü', 'Ü', 'ö', 'Ö', 'ç', 'Ç'];
        $en = ['s', 's', 'i', 'i', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c'];
        $text = str_replace($tr, $en, $text);
        
        // Küçük harfe çevir
        $text = mb_strtolower($text, 'UTF-8');
        
        // Alfanumerik olmayan karakterleri tire ile değiştir
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        
        // Baş ve sondaki tireleri kaldır
        $text = trim($text, '-');
        
        // Maksimum uzunluk
        if (strlen($text) > 200) {
            $text = substr($text, 0, 200);
            $text = rtrim($text, '-');
        }
        
        return $text;
    }
}

