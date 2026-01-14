<?php
/**
 * Emlak İlanları Model
 */

class RealEstateListingsModel {
    private $db;
    private $table = 'realestate_listings';
    
    public function __construct() {
        $this->db = Database::getInstance();
        // Mevcut tablolar için migration'ları çalıştır
        $this->migrateListingStatus();
        $this->migrateLivingRoomsAndRooms();
        $this->migrateRealtorId();
    }
    
    /**
     * İlanlar tablosunu oluştur
     */
    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `location` varchar(255) DEFAULT NULL,
            `price` decimal(15,2) DEFAULT 0.00,
            `property_type` varchar(50) DEFAULT 'house',
            `listing_status` enum('sale','rent') DEFAULT 'sale',
            `bedrooms` int(11) DEFAULT 0,
            `bathrooms` int(11) DEFAULT 0,
            `living_rooms` int(11) DEFAULT 0,
            `rooms` int(11) DEFAULT 0,
            `area` decimal(10,2) DEFAULT 0.00,
            `area_unit` varchar(10) DEFAULT 'sqft',
            `featured_image` varchar(500) DEFAULT NULL,
            `gallery` text DEFAULT NULL,
            `status` enum('draft','published') DEFAULT 'draft',
            `is_featured` tinyint(1) DEFAULT 0,
            `author_id` int(11) DEFAULT NULL,
            `realtor_id` int(11) DEFAULT NULL,
            `views` int(11) DEFAULT 0,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`),
            KEY `status` (`status`),
            KEY `listing_status` (`listing_status`),
            KEY `is_featured` (`is_featured`),
            KEY `property_type` (`property_type`),
            KEY `author_id` (`author_id`),
            KEY `realtor_id` (`realtor_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->query($sql);
        
        // Mevcut tablolar için migration (constructor'da da çalışıyor ama burada da çalıştırıyoruz)
        $this->migrateListingStatus();
        $this->migrateLivingRoomsAndRooms();
        $this->migrateRealtorId();
    }
    
    /**
     * Mevcut tablolara listing_status alanını ekle
     */
    private function migrateListingStatus() {
        try {
            // Sütunun var olup olmadığını kontrol et
            $checkSql = "SHOW COLUMNS FROM `{$this->table}` LIKE 'listing_status'";
            $stmt = $this->db->getConnection()->query($checkSql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                // Sütun yoksa ekle
                $alterSql = "ALTER TABLE `{$this->table}` ADD COLUMN `listing_status` enum('sale','rent') DEFAULT 'sale' AFTER `property_type`";
                $this->db->query($alterSql);
                
                // Index ekle (varsa hata vermemesi için IF NOT EXISTS kullanılamıyor, try-catch ile yakalıyoruz)
                try {
                    $indexSql = "ALTER TABLE `{$this->table}` ADD INDEX `listing_status` (`listing_status`)";
                    $this->db->query($indexSql);
                } catch (Exception $e) {
                    // Index zaten varsa hata vermemesi için
                    error_log('Listing status index already exists or error: ' . $e->getMessage());
                }
            }
        } catch (Exception $e) {
            // Hata durumunda sessizce devam et
            error_log('Listing status migration error: ' . $e->getMessage());
        }
    }
    
    /**
     * Mevcut tablolara living_rooms ve rooms alanlarını ekle
     */
    private function migrateLivingRoomsAndRooms() {
        try {
            // living_rooms sütununu kontrol et
            $checkSql = "SHOW COLUMNS FROM `{$this->table}` LIKE 'living_rooms'";
            $stmt = $this->db->getConnection()->query($checkSql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                // living_rooms sütunu yoksa ekle
                $alterSql = "ALTER TABLE `{$this->table}` ADD COLUMN `living_rooms` int(11) DEFAULT 0 AFTER `bathrooms`";
                $this->db->query($alterSql);
            }
            
            // rooms sütununu kontrol et
            $checkSql = "SHOW COLUMNS FROM `{$this->table}` LIKE 'rooms'";
            $stmt = $this->db->getConnection()->query($checkSql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                // rooms sütunu yoksa ekle
                $alterSql = "ALTER TABLE `{$this->table}` ADD COLUMN `rooms` int(11) DEFAULT 0 AFTER `living_rooms`";
                $this->db->query($alterSql);
            }
        } catch (Exception $e) {
            // Hata durumunda sessizce devam et
            error_log('Living rooms and rooms migration error: ' . $e->getMessage());
        }
    }
    
    /**
     * Mevcut tablolara realtor_id alanını ekle
     */
    private function migrateRealtorId() {
        try {
            // realtor_id sütununu kontrol et
            $checkSql = "SHOW COLUMNS FROM `{$this->table}` LIKE 'realtor_id'";
            $stmt = $this->db->getConnection()->query($checkSql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                // realtor_id sütunu yoksa ekle
                $alterSql = "ALTER TABLE `{$this->table}` ADD COLUMN `realtor_id` int(11) DEFAULT NULL AFTER `author_id`";
                $this->db->query($alterSql);
                
                // Index ekle
                try {
                    $indexSql = "ALTER TABLE `{$this->table}` ADD INDEX `realtor_id` (`realtor_id`)";
                    $this->db->query($indexSql);
                } catch (Exception $e) {
                    // Index zaten varsa hata vermemesi için
                    error_log('Realtor ID index already exists or error: ' . $e->getMessage());
                }
            }
        } catch (Exception $e) {
            // Hata durumunda sessizce devam et
            error_log('Realtor ID migration error: ' . $e->getMessage());
        }
    }
    
    /**
     * Tüm ilanları getir
     */
    public function getAll($orderBy = 'created_at DESC') {
        $sql = "SELECT l.*, u.username as author_name, 
                r.id as realtor_id, r.first_name as realtor_first_name, r.last_name as realtor_last_name, 
                r.email as realtor_email, r.phone as realtor_phone, r.photo as realtor_photo, r.bio as realtor_bio,
                r.slug as realtor_slug
                FROM `{$this->table}` l
                LEFT JOIN `users` u ON l.author_id = u.id
                LEFT JOIN `realestate_agents` r ON l.realtor_id = r.id
                ORDER BY {$orderBy}";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Yayınlanmış ilanları filtrelerle getir
     */
    public function getPublished($location = '', $type = '', $priceRange = '', $limit = null, $offset = 0) {
        $sql = "SELECT l.*, 
                r.id as realtor_id, r.first_name as realtor_first_name, r.last_name as realtor_last_name, 
                r.email as realtor_email, r.phone as realtor_phone, r.photo as realtor_photo, r.bio as realtor_bio,
                r.slug as realtor_slug
                FROM `{$this->table}` l
                LEFT JOIN `realestate_agents` r ON l.realtor_id = r.id
                WHERE l.`status` = 'published'";
        $params = [];
        
        if (!empty($location)) {
            $sql .= " AND l.`location` LIKE ?";
            $params[] = "%{$location}%";
        }
        
        if (!empty($type)) {
            $sql .= " AND l.`property_type` = ?";
            $params[] = $type;
        }
        
        if (!empty($priceRange)) {
            if (strpos($priceRange, '-') !== false) {
                list($min, $max) = explode('-', $priceRange);
                $sql .= " AND l.`price` >= ? AND l.`price` <= ?";
                $params[] = floatval($min);
                $params[] = floatval($max);
            } else {
                $sql .= " AND l.`price` >= ?";
                $params[] = floatval(str_replace('+', '', $priceRange));
            }
        }
        
        $sql .= " ORDER BY l.`is_featured` DESC, l.`created_at` DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$offset}, {$limit}";
        }
        
        if (!empty($params)) {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Öne çıkan ilanları getir
     */
    public function getFeatured($limit = 6) {
        $sql = "SELECT l.*, 
                r.id as realtor_id, r.first_name as realtor_first_name, r.last_name as realtor_last_name, 
                r.email as realtor_email, r.phone as realtor_phone, r.photo as realtor_photo, r.bio as realtor_bio,
                r.slug as realtor_slug
                FROM `{$this->table}` l
                LEFT JOIN `realestate_agents` r ON l.realtor_id = r.id
                WHERE l.`status` = 'published' AND l.`is_featured` = 1 
                ORDER BY l.`created_at` DESC 
                LIMIT {$limit}";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * ID'ye göre ilan bul
     */
    public function find($id) {
        $sql = "SELECT l.*, u.username as author_name,
                r.id as realtor_id, r.first_name as realtor_first_name, r.last_name as realtor_last_name, 
                r.email as realtor_email, r.phone as realtor_phone, r.photo as realtor_photo, r.bio as realtor_bio,
                r.slug as realtor_slug
                FROM `{$this->table}` l
                LEFT JOIN `users` u ON l.author_id = u.id
                LEFT JOIN `realestate_agents` r ON l.realtor_id = r.id
                WHERE l.id = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Slug'a göre ilan bul
     */
    public function findBySlug($slug) {
        $sql = "SELECT l.*, 
                r.id as realtor_id, r.first_name as realtor_first_name, r.last_name as realtor_last_name, 
                r.email as realtor_email, r.phone as realtor_phone, r.photo as realtor_photo, r.bio as realtor_bio,
                r.slug as realtor_slug
                FROM `{$this->table}` l
                LEFT JOIN `realestate_agents` r ON l.realtor_id = r.id
                WHERE l.`slug` = ? AND l.`status` = 'published'";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Yeni ilan oluştur
     */
    public function create($data) {
        $sql = "INSERT INTO `{$this->table}` 
                (`title`, `slug`, `description`, `location`, `price`, `property_type`, `listing_status`, `bedrooms`, `bathrooms`, `living_rooms`, `rooms`, `area`, `area_unit`, `featured_image`, `gallery`, `status`, `is_featured`, `author_id`, `realtor_id`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $result = $stmt->execute([
            $data['title'],
            $data['slug'],
            $data['description'] ?? null,
            $data['location'] ?? null,
            $data['price'] ?? 0,
            $data['property_type'] ?? 'house',
            $data['listing_status'] ?? 'sale',
            $data['bedrooms'] ?? 0,
            $data['bathrooms'] ?? 0,
            $data['living_rooms'] ?? 0,
            $data['rooms'] ?? 0,
            $data['area'] ?? 0,
            $data['area_unit'] ?? 'sqft',
            $data['featured_image'] ?? null,
            $data['gallery'] ?? '[]',
            $data['status'] ?? 'draft',
            $data['is_featured'] ?? 0,
            $data['author_id'] ?? null,
            $data['realtor_id'] ?? null
        ]);
        
        return $result ? $this->db->getConnection()->lastInsertId() : false;
    }
    
    /**
     * İlan güncelle
     */
    public function update($id, $data) {
        $sql = "UPDATE `{$this->table}` SET 
                `title` = ?, `slug` = ?, `description` = ?, `location` = ?, `price` = ?, 
                `property_type` = ?, `listing_status` = ?, `bedrooms` = ?, `bathrooms` = ?, `living_rooms` = ?, `rooms` = ?, `area` = ?, `area_unit` = ?, 
                `featured_image` = ?, `gallery` = ?, `status` = ?, `is_featured` = ?, `realtor_id` = ?
                WHERE `id` = ?";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $data['slug'],
            $data['description'] ?? null,
            $data['location'] ?? null,
            $data['price'] ?? 0,
            $data['property_type'] ?? 'house',
            $data['listing_status'] ?? 'sale',
            $data['bedrooms'] ?? 0,
            $data['bathrooms'] ?? 0,
            $data['living_rooms'] ?? 0,
            $data['rooms'] ?? 0,
            $data['area'] ?? 0,
            $data['area_unit'] ?? 'sqft',
            $data['featured_image'] ?? null,
            $data['gallery'] ?? '[]',
            $data['status'] ?? 'draft',
            $data['is_featured'] ?? 0,
            $data['realtor_id'] ?? null,
            $id
        ]);
    }
    
    /**
     * İlan sil
     */
    public function delete($id) {
        $sql = "DELETE FROM `{$this->table}` WHERE `id` = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Görüntülenme sayısını artır
     */
    public function incrementViews($id) {
        $sql = "UPDATE `{$this->table}` SET `views` = `views` + 1 WHERE `id` = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Emlakçıya göre ilanları getir
     */
    public function getByRealtor($realtorId, $limit = null, $offset = 0) {
        $sql = "SELECT l.*, 
                r.id as realtor_id, r.first_name as realtor_first_name, r.last_name as realtor_last_name, 
                r.email as realtor_email, r.phone as realtor_phone, r.photo as realtor_photo, r.bio as realtor_bio,
                r.slug as realtor_slug
                FROM `{$this->table}` l
                LEFT JOIN `realestate_agents` r ON l.realtor_id = r.id
                WHERE l.`realtor_id` = ? AND l.`status` = 'published'
                ORDER BY l.`is_featured` DESC, l.`created_at` DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$offset}, {$limit}";
        }
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$realtorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Emlakçının toplam ilan sayısını getir
     */
    public function countByRealtor($realtorId) {
        $sql = "SELECT COUNT(*) as total FROM `{$this->table}` WHERE `realtor_id` = ? AND `status` = 'published'";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$realtorId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}
