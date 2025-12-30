<?php
/**
 * Post Model - Blog Yazıları
 */

class Post extends Model {
    protected $table = 'posts';
    
    /**
     * Tüm yazıları getirir (yazar ve kategori bilgisi ile) - Sadece type='post' olanlar
     */
    public function getAll($orderBy = 'created_at DESC') {
        $sql = "SELECT p.*, 
                       u.username as author_name, 
                       c.name as category_name
                FROM `{$this->table}` p
                LEFT JOIN `users` u ON p.author_id = u.id
                LEFT JOIN `post_categories` c ON p.category_id = c.id
                WHERE (p.type = 'post' OR p.type IS NULL)
                ORDER BY {$orderBy}";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Yayınlanmış yazıları getirir - Sadece type='post' olanlar
     */
    public function getPublished($limit = null, $offset = 0) {
        $sql = "SELECT p.*, 
                       u.username as author_name, 
                       c.name as category_name,
                       c.slug as category_slug
                FROM `{$this->table}` p
                LEFT JOIN `users` u ON p.author_id = u.id
                LEFT JOIN `post_categories` c ON p.category_id = c.id
                WHERE (p.type = 'post' OR p.type IS NULL)
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
     * Kategoriye göre yazıları getirir - Sadece type='post' olanlar
     */
    public function getByCategory($categoryId, $limit = null) {
        $sql = "SELECT p.*, 
                       u.username as author_name, 
                       c.name as category_name
                FROM `{$this->table}` p
                LEFT JOIN `users` u ON p.author_id = u.id
                LEFT JOIN `post_categories` c ON p.category_id = c.id
                WHERE (p.type = 'post' OR p.type IS NULL)
                AND p.category_id = ? 
                AND p.status = 'published'
                ORDER BY p.published_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->fetchAll($sql, [$categoryId]);
    }
    
    /**
     * Yazara göre yazıları getirir - Sadece type='post' olanlar
     */
    public function getByAuthor($authorId, $limit = null) {
        $sql = "SELECT p.*, 
                       c.name as category_name
                FROM `{$this->table}` p
                LEFT JOIN `post_categories` c ON p.category_id = c.id
                WHERE (p.type = 'post' OR p.type IS NULL)
                AND p.author_id = ?
                ORDER BY p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->fetchAll($sql, [$authorId]);
    }
    
    /**
     * Slug'a göre yazı getirir
     */
    public function findBySlug($slug) {
        $sql = "SELECT p.*, 
                       u.username as author_name, 
                       c.name as category_name,
                       c.slug as category_slug
                FROM `{$this->table}` p
                LEFT JOIN `users` u ON p.author_id = u.id
                LEFT JOIN `post_categories` c ON p.category_id = c.id
                WHERE p.slug = ?";
        return $this->db->fetch($sql, [$slug]);
    }
    
    /**
     * ID'ye göre yazı getirir (detaylı)
     */
    public function findWithDetails($id) {
        $sql = "SELECT p.*, 
                       u.username as author_name, 
                       c.name as category_name
                FROM `{$this->table}` p
                LEFT JOIN `users` u ON p.author_id = u.id
                LEFT JOIN `post_categories` c ON p.category_id = c.id
                WHERE p.id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Yazı oluşturur
     */
    public function createPost($data) {
        // Slug oluştur
        if (empty($data['slug'])) {
            $data['slug'] = $this->createSlug($data['title']);
        }
        
        // Kategori seçilmediyse varsayılan "Genel" kategorisine ata
        if (empty($data['category_id'])) {
            $data['category_id'] = $this->getDefaultCategoryId();
        }
        
        // Yayın tarihi
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->create($data);
    }
    
    /**
     * Varsayılan kategori ID'sini getirir (Genel kategorisi)
     */
    private function getDefaultCategoryId() {
        // Önce "genel" slug'lı kategoriyi ara
        $sql = "SELECT id FROM `post_categories` WHERE `slug` = 'genel' AND `status` = 'active' LIMIT 1";
        $result = $this->db->fetch($sql);
        
        if ($result) {
            return $result['id'];
        }
        
        // Yoksa ilk aktif kategoriyi al
        $sql = "SELECT id FROM `post_categories` WHERE `status` = 'active' ORDER BY id ASC LIMIT 1";
        $result = $this->db->fetch($sql);
        
        if ($result) {
            return $result['id'];
        }
        
        // Hiç kategori yoksa null döndür
        return null;
    }
    
    /**
     * Yazı günceller
     */
    public function updatePost($id, $data) {
        // Slug güncelle
        if (isset($data['title']) && empty($data['slug'])) {
            $data['slug'] = $this->createSlug($data['title'], $id);
        }
        
        // Kategori seçilmediyse varsayılan "Genel" kategorisine ata
        if (array_key_exists('category_id', $data) && empty($data['category_id'])) {
            $data['category_id'] = $this->getDefaultCategoryId();
        }
        
        // Yayınlandıysa ve tarih yoksa ekle
        if (isset($data['status']) && $data['status'] === 'published') {
            $post = $this->find($id);
            if (empty($post['published_at']) && empty($data['published_at'])) {
                $data['published_at'] = date('Y-m-d H:i:s');
            }
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Görüntülenme sayısını artırır
     */
    public function incrementViews($id) {
        $sql = "UPDATE `{$this->table}` SET `views` = `views` + 1 WHERE `id` = ?";
        return $this->db->query($sql, [$id]);
    }
    
    /**
     * Duruma göre sayı getirir
     */
    public function getCountByStatus($status = null) {
        if ($status) {
            $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE (type = 'post' OR type IS NULL) AND `status` = ?";
            $result = $this->db->fetch($sql, [$status]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE (type = 'post' OR type IS NULL)";
            $result = $this->db->fetch($sql);
        }
        return $result['count'] ?? 0;
    }
    
    /**
     * Duruma göre yazıları getirir - Sadece type='post' olanlar
     */
    public function getByStatus($status) {
        $sql = "SELECT p.*, 
                       u.username as author_name, 
                       c.name as category_name
                FROM `{$this->table}` p
                LEFT JOIN `users` u ON p.author_id = u.id
                LEFT JOIN `post_categories` c ON p.category_id = c.id
                WHERE (p.type = 'post' OR p.type IS NULL)
                AND p.status = ?
                ORDER BY p.created_at DESC";
        return $this->db->fetchAll($sql, [$status]);
    }
    
    /**
     * Arama yapar
     */
    public function search($keyword, $limit = 20) {
        $keyword = '%' . $keyword . '%';
        $sql = "SELECT p.*, 
                       u.username as author_name, 
                       c.name as category_name
                FROM `{$this->table}` p
                LEFT JOIN `users` u ON p.author_id = u.id
                LEFT JOIN `post_categories` c ON p.category_id = c.id
                WHERE (p.type = 'post' OR p.type IS NULL)
                AND (p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ?)
                AND p.status = 'published'
                ORDER BY p.published_at DESC
                LIMIT {$limit}";
        return $this->db->fetchAll($sql, [$keyword, $keyword, $keyword]);
    }
    
    /**
     * İlgili yazıları getirir
     */
    public function getRelated($postId, $categoryId, $limit = 4) {
        $sql = "SELECT p.*, 
                       c.name as category_name
                FROM `{$this->table}` p
                LEFT JOIN `post_categories` c ON p.category_id = c.id
                WHERE (p.type = 'post' OR p.type IS NULL)
                AND p.id != ? 
                AND p.category_id = ?
                AND p.status = 'published'
                ORDER BY p.published_at DESC
                LIMIT {$limit}";
        return $this->db->fetchAll($sql, [$postId, $categoryId]);
    }
    
    /**
     * Son yazıları getirir - Sadece type='post' olanlar
     */
    public function getRecent($limit = 5) {
        return $this->getPublished($limit);
    }
    
    /**
     * Popüler yazıları getirir (görüntülenmeye göre) - Sadece type='post' olanlar
     */
    public function getPopular($limit = 5) {
        $sql = "SELECT p.*, 
                       c.name as category_name
                FROM `{$this->table}` p
                LEFT JOIN `post_categories` c ON p.category_id = c.id
                WHERE (p.type = 'post' OR p.type IS NULL)
                AND p.status = 'published'
                ORDER BY p.views DESC
                LIMIT {$limit}";
        return $this->db->fetchAll($sql);
    }
    
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
            $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `slug` = ? AND `id` != ?";
            $result = $this->db->fetch($sql, [$slug, $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `slug` = ?";
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
    
    /**
     * Yazının etiketlerini getirir
     */
    public function getTags($postId) {
        $sql = "SELECT t.* FROM `post_tags` t
                INNER JOIN `post_tag_relations` ptr ON t.id = ptr.tag_id
                WHERE ptr.post_id = ?
                ORDER BY t.name ASC";
        return $this->db->fetchAll($sql, [$postId]);
    }
    
    /**
     * Yazıya etiket ekler
     */
    public function addTag($postId, $tagId) {
        $sql = "INSERT IGNORE INTO `post_tag_relations` (`post_id`, `tag_id`) VALUES (?, ?)";
        return $this->db->query($sql, [$postId, $tagId]);
    }
    
    /**
     * Yazıdan etiket siler
     */
    public function removeTag($postId, $tagId) {
        $sql = "DELETE FROM `post_tag_relations` WHERE `post_id` = ? AND `tag_id` = ?";
        return $this->db->query($sql, [$postId, $tagId]);
    }
    
    /**
     * Yazının tüm etiketlerini siler
     */
    public function clearTags($postId) {
        $sql = "DELETE FROM `post_tag_relations` WHERE `post_id` = ?";
        return $this->db->query($sql, [$postId]);
    }
    
    /**
     * Yazının etiketlerini günceller
     */
    public function syncTags($postId, $tagIds) {
        $this->clearTags($postId);
        
        foreach ($tagIds as $tagId) {
            $this->addTag($postId, $tagId);
        }
    }
    
    // ==================== VERSİYON YÖNETİMİ ====================
    
    /**
     * Yazı günceller (versiyon kaydıyla birlikte)
     */
    public function updateWithVersion($id, $data, $userId = null) {
        // Mevcut yazıyı al
        $currentPost = $this->find($id);
        
        if (!$currentPost) {
            return false;
        }
        
        try {
            // Versiyon modeli
            require_once __DIR__ . '/PostVersion.php';
            $versionModel = new PostVersion();
            
            // İçerik değişmiş mi kontrol et
            $contentChanged = (
                $currentPost['title'] !== ($data['title'] ?? '') ||
                $currentPost['content'] !== ($data['content'] ?? '') ||
                $currentPost['excerpt'] !== ($data['excerpt'] ?? '')
            );
            
            // Eğer içerik değiştiyse, mevcut versiyonu kaydet
            if ($contentChanged) {
                $versionModel->createVersion($id, $currentPost, $userId);
                
                // Versiyon numarasını artır (sadece version sütunu varsa)
                if (array_key_exists('version', $currentPost)) {
                    $data['version'] = ($currentPost['version'] ?? 1) + 1;
                }
                
                // Eski versiyonları temizle (20'den fazla tutma)
                $versionModel->cleanOldVersions($id, 20);
            }
        } catch (Exception $e) {
            // Versiyon hatası logla ama güncellemeye devam et
            error_log('Post version save error: ' . $e->getMessage());
        }
        
        // Yazıyı güncelle
        return $this->updatePost($id, $data);
    }
    
    /**
     * Yazının versiyon geçmişini getirir
     */
    public function getVersions($postId) {
        require_once __DIR__ . '/PostVersion.php';
        $versionModel = new PostVersion();
        return $versionModel->getByPostId($postId);
    }
    
    /**
     * Eski versiyona geri döner
     */
    public function restoreVersion($versionId, $userId = null) {
        try {
            require_once __DIR__ . '/PostVersion.php';
            $versionModel = new PostVersion();
            
            $version = $versionModel->findWithDetails($versionId);
            
            if (!$version) {
                return false;
            }
            
            $postId = $version['post_id'];
            $currentPost = $this->find($postId);
            
            if (!$currentPost) {
                return false;
            }
            
            // Mevcut versiyonu kaydet
            $versionModel->createVersion($postId, $currentPost, $userId);
            
            // Eski versiyonu geri yükle
            $restoreData = [
                'title' => $version['title'] ?? $currentPost['title'],
                'excerpt' => $version['excerpt'] ?? '',
                'content' => $version['content'] ?? '',
            ];
            
            // Opsiyonel alanları ekle (varsa)
            if (!empty($version['slug'])) {
                $restoreData['slug'] = $version['slug'];
            }
            if (isset($version['featured_image'])) {
                $restoreData['featured_image'] = $version['featured_image'];
            }
            if (isset($version['meta_title'])) {
                $restoreData['meta_title'] = $version['meta_title'];
            }
            if (isset($version['meta_description'])) {
                $restoreData['meta_description'] = $version['meta_description'];
            }
            if (isset($version['meta_keywords'])) {
                $restoreData['meta_keywords'] = $version['meta_keywords'];
            }
            
            // Versiyon numarasını artır (sadece version sütunu varsa)
            if (array_key_exists('version', $currentPost)) {
                $restoreData['version'] = ($currentPost['version'] ?? 1) + 1;
            }
            
            return $this->update($postId, $restoreData);
        } catch (Exception $e) {
            error_log('Post restore version error: ' . $e->getMessage());
            return false;
        }
    }
}

