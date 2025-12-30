<?php
/**
 * SEO Model
 * Veritabanı işlemleri ve CRUD operasyonları
 */

class SeoModel {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // ==================== TABLO OLUŞTURMA ====================
    
    /**
     * Gerekli tabloları oluştur
     */
    public function createTables() {
        // Yönlendirmeler tablosu
        $sql = "CREATE TABLE IF NOT EXISTS `seo_redirects` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `source_url` varchar(500) NOT NULL COMMENT 'Kaynak URL (/ ile başlamalı)',
            `target_url` varchar(500) NOT NULL COMMENT 'Hedef URL',
            `type` enum('301','302') DEFAULT '301' COMMENT 'Yönlendirme tipi',
            `hits` int(11) DEFAULT 0 COMMENT 'Kullanım sayısı',
            `status` enum('active','inactive') DEFAULT 'active',
            `note` varchar(255) DEFAULT NULL COMMENT 'Açıklama notu',
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `source_url` (`source_url`),
            KEY `status` (`status`),
            KEY `type` (`type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        try {
            $this->db->query($sql);
            return true;
        } catch (Exception $e) {
            error_log("SEO tables creation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Tabloları sil
     */
    public function dropTables() {
        try {
            $this->db->query("DROP TABLE IF EXISTS `seo_redirects`");
            return true;
        } catch (Exception $e) {
            error_log("SEO tables drop error: " . $e->getMessage());
            return false;
        }
    }
    
    // ==================== YÖNLENDİRMELER ====================
    
    /**
     * Tüm yönlendirmeleri getir
     */
    public function getRedirects($status = null, $limit = 100, $offset = 0) {
        try {
            $sql = "SELECT * FROM seo_redirects";
            $params = [];
            
            if ($status) {
                $sql .= " WHERE status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Aktif yönlendirmeleri getir (cache için)
     */
    public function getActiveRedirects() {
        try {
            return $this->db->fetchAll(
                "SELECT source_url, target_url, type FROM seo_redirects WHERE status = 'active'"
            );
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Tek yönlendirme getir
     */
    public function getRedirect($id) {
        try {
            return $this->db->fetch("SELECT * FROM seo_redirects WHERE id = ?", [$id]);
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Kaynak URL ile yönlendirme bul
     */
    public function findRedirectBySource($sourceUrl) {
        try {
            return $this->db->fetch(
                "SELECT * FROM seo_redirects WHERE source_url = ? AND status = 'active'",
                [$sourceUrl]
            );
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Yönlendirme ekle
     */
    public function addRedirect($data) {
        try {
            // URL'yi normalize et
            $sourceUrl = '/' . ltrim($data['source_url'], '/');
            
            $this->db->query(
                "INSERT INTO seo_redirects (source_url, target_url, type, status, note) VALUES (?, ?, ?, ?, ?)",
                [
                    $sourceUrl,
                    $data['target_url'],
                    $data['type'] ?? '301',
                    $data['status'] ?? 'active',
                    $data['note'] ?? null
                ]
            );
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Add redirect error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Yönlendirme güncelle
     */
    public function updateRedirect($id, $data) {
        try {
            $sourceUrl = '/' . ltrim($data['source_url'], '/');
            
            $this->db->query(
                "UPDATE seo_redirects SET source_url = ?, target_url = ?, type = ?, status = ?, note = ? WHERE id = ?",
                [
                    $sourceUrl,
                    $data['target_url'],
                    $data['type'] ?? '301',
                    $data['status'] ?? 'active',
                    $data['note'] ?? null,
                    $id
                ]
            );
            
            return true;
        } catch (Exception $e) {
            error_log("Update redirect error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Yönlendirme sil
     */
    public function deleteRedirect($id) {
        try {
            $this->db->query("DELETE FROM seo_redirects WHERE id = ?", [$id]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Hit sayısını artır
     */
    public function incrementHit($id) {
        try {
            $this->db->query("UPDATE seo_redirects SET hits = hits + 1 WHERE id = ?", [$id]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Yönlendirme istatistikleri
     */
    public function getRedirectStats() {
        $stats = [
            'total' => 0,
            'active' => 0,
            'inactive' => 0,
            'total_hits' => 0
        ];
        
        try {
            // Önce tablo var mı kontrol et
            $tableCheck = $this->db->fetch("SHOW TABLES LIKE 'seo_redirects'");
            if (!$tableCheck) {
                return $stats;
            }
            
            $result = $this->db->fetch(
                "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                    COALESCE(SUM(hits), 0) as total_hits
                 FROM seo_redirects"
            );
            
            if ($result) {
                $stats = [
                    'total' => (int)($result['total'] ?? 0),
                    'active' => (int)($result['active'] ?? 0),
                    'inactive' => (int)($result['inactive'] ?? 0),
                    'total_hits' => (int)($result['total_hits'] ?? 0)
                ];
            }
        } catch (Exception $e) {
            // Tablo yoksa varsayılan değerler döner
        }
        
        return $stats;
    }
    
    // ==================== SİTEMAP İÇERİKLERİ ====================
    
    /**
     * Sitemap için yayınlanmış yazıları getir
     */
    public function getPostsForSitemap() {
        try {
            return $this->db->fetchAll(
                "SELECT slug, updated_at, published_at 
                 FROM posts 
                 WHERE status = 'published' AND visibility = 'public' AND type = 'post'
                 ORDER BY published_at DESC"
            );
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Sitemap için yayınlanmış sayfaları getir
     */
    public function getPagesForSitemap() {
        try {
            return $this->db->fetchAll(
                "SELECT slug, updated_at, published_at 
                 FROM posts 
                 WHERE status = 'published' AND visibility = 'public' AND type = 'page'
                 ORDER BY published_at DESC"
            );
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Sitemap için kategorileri getir
     */
    public function getCategoriesForSitemap() {
        try {
            return $this->db->fetchAll(
                "SELECT slug, updated_at 
                 FROM post_categories 
                 WHERE status = 'active'
                 ORDER BY name ASC"
            );
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Sitemap için etiketleri getir
     */
    public function getTagsForSitemap() {
        try {
            return $this->db->fetchAll(
                "SELECT slug, created_at as updated_at 
                 FROM post_tags
                 ORDER BY name ASC"
            );
        } catch (Exception $e) {
            return [];
        }
    }
    
    // ==================== İSTATİSTİKLER ====================
    
    /**
     * Genel SEO istatistikleri
     */
    public function getStats() {
        $stats = [
            'posts_count' => 0,
            'categories_count' => 0,
            'tags_count' => 0,
            'redirects_count' => 0
        ];
        
        // Yayınlanmış yazı sayısı
        try {
            $result = $this->db->fetch("SELECT COUNT(*) as cnt FROM posts WHERE status = 'published'");
            $stats['posts_count'] = $result ? (int)$result['cnt'] : 0;
        } catch (Exception $e) {
            // Tablo yoksa 0 döner
        }
        
        // Aktif kategori sayısı
        try {
            $result = $this->db->fetch("SELECT COUNT(*) as cnt FROM post_categories WHERE status = 'active'");
            $stats['categories_count'] = $result ? (int)$result['cnt'] : 0;
        } catch (Exception $e) {
            // Tablo yoksa 0 döner
        }
        
        // Etiket sayısı
        try {
            $result = $this->db->fetch("SELECT COUNT(*) as cnt FROM post_tags");
            $stats['tags_count'] = $result ? (int)$result['cnt'] : 0;
        } catch (Exception $e) {
            // Tablo yoksa 0 döner
        }
        
        // Yönlendirme sayısı
        try {
            $result = $this->db->fetch("SELECT COUNT(*) as cnt FROM seo_redirects WHERE status = 'active'");
            $stats['redirects_count'] = $result ? (int)$result['cnt'] : 0;
        } catch (Exception $e) {
            // Tablo yoksa 0 döner
        }
        
        return $stats;
    }
}

