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
        } catch (Exception $e) {
            error_log("SEO tables creation error (redirects): " . $e->getMessage());
            return false;
        }
        
        // Sayfa meta tablosu (path_pattern '' = varsayılan sayfa, dolu = özel path override)
        $sqlPageMeta = "CREATE TABLE IF NOT EXISTS `seo_page_meta` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `page_key` varchar(100) NOT NULL,
            `path_pattern` varchar(500) DEFAULT '',
            `meta_title` varchar(255) DEFAULT NULL,
            `meta_description` varchar(500) DEFAULT NULL,
            `meta_robots` varchar(100) DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `page_key_path` (`page_key`, `path_pattern`(191)),
            KEY `page_key` (`page_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        try {
            $this->db->query($sqlPageMeta);
        } catch (Exception $e) {
            error_log("SEO tables creation error (page_meta): " . $e->getMessage());
            return false;
        }
        
        // Kırık bağlantılar tablosu
        $sqlBroken = "CREATE TABLE IF NOT EXISTS `seo_broken_links` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `url` varchar(1000) NOT NULL,
            `source` varchar(50) DEFAULT NULL,
            `http_code` int(11) DEFAULT NULL,
            `checked_at` datetime DEFAULT NULL,
            `link_text` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `http_code` (`http_code`),
            KEY `checked_at` (`checked_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        try {
            $this->db->query($sqlBroken);
        } catch (Exception $e) {
            error_log("SEO tables creation error (broken_links): " . $e->getMessage());
            return false;
        }
        
        return true;
    }
    
    /**
     * Tabloları sil
     */
    public function dropTables() {
        try {
            $this->db->query("DROP TABLE IF EXISTS `seo_redirects`");
            $this->db->query("DROP TABLE IF EXISTS `seo_page_meta`");
            $this->db->query("DROP TABLE IF EXISTS `seo_broken_links`");
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
    
    // ==================== SAYFA META ====================
    
    /**
     * Path veya page_key için meta getir. Önce path_pattern eşleşen, yoksa page_key ile kayıt döner.
     */
    public function getPageMeta($pageKey, $path = null) {
        try {
            if ($path !== null && $path !== '') {
                $path = '/' . trim($path, '/');
                $all = $this->db->fetchAll("SELECT * FROM seo_page_meta WHERE page_key = ? AND path_pattern != '' AND path_pattern IS NOT NULL", [$pageKey]);
                foreach ($all as $row) {
                    $pattern = trim($row['path_pattern'], '/');
                    if ($pattern !== '' && (strpos($path, $pattern) !== false || $path === '/' . $pattern)) {
                        return $row;
                    }
                }
            }
            $row = $this->db->fetch(
                "SELECT * FROM seo_page_meta WHERE page_key = ? AND (path_pattern = '' OR path_pattern IS NULL)",
                [$pageKey]
            );
            return $row ?: null;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Mevcut path'e göre sayfa meta override'ı getir (frontend'de kullanım). Path'ten page_key türetir.
     */
    public function getPageMetaForPath($path) {
        $path = trim($path, '/');
        $pathParts = $path ? explode('/', $path) : [];
        $pageKey = 'home';
        if (isset($pathParts[0])) {
            $first = $pathParts[0];
            if ($first === 'blog') {
                $pageKey = isset($pathParts[1]) ? (strpos($pathParts[1], 'kategori') !== false ? 'blog_category' : 'blog_post') : 'blog';
            } elseif (in_array($first, ['contact', 'iletisim'])) {
                $pageKey = 'contact';
            } elseif ($first === 'teklif-al' || $first === 'quote-request') {
                $pageKey = 'teklif-al';
            } elseif ($first === 'rezervasyon') {
                $pageKey = 'rezervasyon';
            } elseif ($first === 'search') {
                $pageKey = 'search';
            } elseif ($first === 'ilanlar') {
                $pageKey = (isset($pathParts[1]) && $pathParts[1] === 'kategori') ? 'ilan_kategori' : 'ilanlar';
            } elseif ($first === 'ilan') {
                $pageKey = 'ilan_detay';
            } elseif ($first === 'danismanlar') {
                $pageKey = 'danismanlar';
            } elseif ($first === 'danisman') {
                $pageKey = 'danisman_detay';
            } elseif ($first === 'harita-ilanlar') {
                $pageKey = 'harita-ilanlar';
            } elseif ($first === 'sozlesmeler') {
                $pageKey = 'sozlesmeler';
            } else {
                $pageKey = 'page_slug';
            }
        }
        $row = $this->getPageMeta($pageKey, $path);
        if ($row && (isset($row['meta_title']) && $row['meta_title'] !== '' || isset($row['meta_description']) && $row['meta_description'] !== '')) {
            return $row;
        }
        return null;
    }
    
    /**
     * Tüm sayfa meta kayıtlarını getir (admin listesi için)
     */
    public function getAllPageMeta() {
        try {
            return $this->db->fetchAll("SELECT * FROM seo_page_meta ORDER BY page_key ASC, path_pattern ASC");
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Sayfa meta kaydet / güncelle
     */
    public function savePageMeta($pageKey, $data, $pathPattern = '') {
        try {
            $pathPattern = $pathPattern === null ? '' : trim($pathPattern);
            $existing = $this->db->fetch(
                "SELECT id FROM seo_page_meta WHERE page_key = ? AND (path_pattern = ? OR (path_pattern IS NULL AND ? = ''))",
                [$pageKey, $pathPattern, $pathPattern]
            );
            $metaTitle = $data['meta_title'] ?? null;
            $metaDescription = $data['meta_description'] ?? null;
            $metaRobots = $data['meta_robots'] ?? null;
            if ($existing) {
                $this->db->query(
                    "UPDATE seo_page_meta SET meta_title = ?, meta_description = ?, meta_robots = ? WHERE id = ?",
                    [$metaTitle, $metaDescription, $metaRobots, $existing['id']]
                );
                return true;
            }
            $this->db->query(
                "INSERT INTO seo_page_meta (page_key, path_pattern, meta_title, meta_description, meta_robots) VALUES (?, ?, ?, ?, ?)",
                [$pageKey, $pathPattern, $metaTitle, $metaDescription, $metaRobots]
            );
            return true;
        } catch (Exception $e) {
            error_log("savePageMeta error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Tarama için dahili URL listesi: sitemap + menü linkleri + sabit sayfalar.
     * Aynı URL tekrar etmez (deduplicate); kaynaklar birleştirilir.
     * @param string $baseUrl Site base URL
     * @param string|null $activeThemeSlug Aktif tema slug (ileride tema bazlı filtre için kullanılabilir)
     */
    public function getInternalUrlsForScan($baseUrl, $activeThemeSlug = null) {
        $baseUrl = rtrim($baseUrl, '/');
        $byUrl = [];
        
        $add = function ($url, $source, $linkText = '') use (&$byUrl) {
            $norm = rtrim($url, '/') ?: $url;
            if (isset($byUrl[$norm])) {
                if (strpos($byUrl[$norm]['source'], $source) === false) {
                    $byUrl[$norm]['source'] .= ', ' . $source;
                }
                if ($linkText && strpos($byUrl[$norm]['link_text'], $linkText) === false) {
                    $byUrl[$norm]['link_text'] = trim($byUrl[$norm]['link_text'] . ', ' . $linkText, ', ');
                }
                return;
            }
            $byUrl[$norm] = ['url' => $url, 'source' => $source, 'link_text' => $linkText];
        };
        
        // Ana sayfa
        $add($baseUrl . '/', 'sitemap', 'Ana Sayfa');
        
        try {
            $pages = $this->getPagesForSitemap();
            foreach ($pages as $p) {
                $add($baseUrl . '/' . $p['slug'], 'sitemap', $p['slug']);
            }
        } catch (Exception $e) {}
        
        try {
            $posts = $this->getPostsForSitemap();
            foreach ($posts as $p) {
                $add($baseUrl . '/blog/' . $p['slug'], 'sitemap', $p['slug']);
            }
        } catch (Exception $e) {}
        
        try {
            $categories = $this->getCategoriesForSitemap();
            foreach ($categories as $c) {
                $add($baseUrl . '/blog/kategori/' . $c['slug'], 'sitemap', $c['slug']);
            }
        } catch (Exception $e) {}
        
        try {
            if ($this->hasListingCategories()) {
                $listingCats = $this->getListingCategoriesForSitemap();
                foreach ($listingCats as $c) {
                    $add($baseUrl . '/ilanlar/kategori/' . $c['slug'], 'sitemap', $c['slug']);
                }
            }
        } catch (Exception $e) {}
        
        $static = ['blog', 'contact', 'iletisim', 'teklif-al', 'quote-request', 'rezervasyon', 'search', 'ilanlar', 'danismanlar', 'harita-ilanlar'];
        foreach ($static as $path) {
            $add($baseUrl . '/' . $path, 'sitemap', $path);
        }
        
        try {
            $items = $this->db->fetchAll("SELECT url, title FROM menu_items WHERE status = 'active' AND url IS NOT NULL AND url != ''");
            foreach ($items as $item) {
                $url = $item['url'];
                if (strpos($url, 'http') !== 0) {
                    $url = $baseUrl . '/' . ltrim($url, '/');
                }
                if (strpos($url, $baseUrl) === 0) {
                    $add($url, 'menu', $item['title'] ?? '');
                }
            }
        } catch (Exception $e) {}
        
        return array_values($byUrl);
    }
    
    /**
     * Kırık link tarama sonuçlarını kaydet (önce eski sonuçları silip yeni yaz)
     */
    public function saveBrokenLinkScanResult($results) {
        try {
            $this->db->query("DELETE FROM seo_broken_links");
            $now = date('Y-m-d H:i:s');
            foreach ($results as $r) {
                $this->db->query(
                    "INSERT INTO seo_broken_links (url, source, http_code, checked_at, link_text) VALUES (?, ?, ?, ?, ?)",
                    [
                        substr($r['url'], 0, 1000),
                        $r['source'] ?? 'manual',
                        $r['http_code'] ?? null,
                        $now,
                        isset($r['link_text']) ? substr($r['link_text'], 0, 255) : null
                    ]
                );
            }
            return true;
        } catch (Exception $e) {
            error_log("saveBrokenLinkScanResult error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Son kırık link tarama sonuçlarını getir (404/410/500 vb. hatalı olanları veya hepsini)
     */
    public function getLastBrokenLinks($onlyErrors = true) {
        try {
            $sql = "SELECT * FROM seo_broken_links ORDER BY checked_at DESC, id ASC";
            if ($onlyErrors) {
                $sql = "SELECT * FROM seo_broken_links WHERE http_code >= 400 OR http_code IS NULL ORDER BY checked_at DESC, id ASC";
            }
            return $this->db->fetchAll($sql);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Tüm son tarama sonuçlarını getir (özet için)
     */
    public function getAllLastScanResults() {
        try {
            return $this->db->fetchAll("SELECT * FROM seo_broken_links ORDER BY http_code ASC, url ASC");
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
        
        // Kategori sayısı: ilan modülü aktifse ilan kategorileri (tema bazlı), yoksa blog kategorileri
        try {
            $stmt = $this->db->getConnection()->query("SHOW TABLES LIKE 'listing_categories'");
            if ($stmt && $stmt->rowCount() > 0) {
                $result = $this->db->fetch("SELECT COUNT(*) as cnt FROM listing_categories");
                $stats['categories_count'] = $result ? (int)$result['cnt'] : 0;
            } else {
                $result = $this->db->fetch("SELECT COUNT(*) as cnt FROM post_categories WHERE status = 'active'");
                $stats['categories_count'] = $result ? (int)$result['cnt'] : 0;
            }
        } catch (Exception $e) {
            try {
                $result = $this->db->fetch("SELECT COUNT(*) as cnt FROM post_categories WHERE status = 'active'");
                $stats['categories_count'] = $result ? (int)$result['cnt'] : 0;
            } catch (Exception $e2) {
                $stats['categories_count'] = 0;
            }
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

    /**
     * Sitemap ve tarama için ilan kategorilerini getir (listing_categories tablosu varsa)
     */
    public function getListingCategoriesForSitemap() {
        try {
            $stmt = $this->db->getConnection()->query("SHOW TABLES LIKE 'listing_categories'");
            if (!$stmt || $stmt->rowCount() === 0) {
                return [];
            }
            return $this->db->fetchAll(
                "SELECT slug, updated_at FROM listing_categories ORDER BY kind ASC, display_order ASC, name ASC"
            );
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * İlan kategorileri tablosu mevcut mu (ilan modülü aktif)
     */
    public function hasListingCategories() {
        try {
            $stmt = $this->db->getConnection()->query("SHOW TABLES LIKE 'listing_categories'");
            return $stmt && $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}

