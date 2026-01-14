<?php
/**
 * Emlak Danışmanları Model
 */

class RealEstateAgentsModel {
    private $db;
    private $table = 'realestate_agents';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Danışmanlar tablosunu oluştur
     */
    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `first_name` varchar(100) NOT NULL,
            `last_name` varchar(100) NOT NULL,
            `slug` varchar(255) NOT NULL,
            `photo` varchar(500) DEFAULT NULL,
            `phone` varchar(50) DEFAULT NULL,
            `email` varchar(255) DEFAULT NULL,
            `specializations` text DEFAULT NULL,
            `experience_years` int(11) DEFAULT 0,
            `bio` text DEFAULT NULL,
            `facebook` varchar(500) DEFAULT NULL,
            `twitter` varchar(500) DEFAULT NULL,
            `instagram` varchar(500) DEFAULT NULL,
            `linkedin` varchar(500) DEFAULT NULL,
            `status` enum('active','inactive') DEFAULT 'active',
            `is_featured` tinyint(1) DEFAULT 0,
            `display_order` int(11) DEFAULT 0,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`),
            KEY `status` (`status`),
            KEY `is_featured` (`is_featured`),
            KEY `display_order` (`display_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->query($sql);
    }
    
    /**
     * Tüm danışmanları getir
     */
    public function getAll($orderBy = 'display_order ASC, created_at DESC') {
        $sql = "SELECT * FROM `{$this->table}` ORDER BY {$orderBy}";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Aktif danışmanları getir
     */
    public function getActive($limit = null, $offset = 0) {
        $sql = "SELECT * FROM `{$this->table}` WHERE `status` = 'active' ORDER BY `display_order` ASC, `is_featured` DESC, `created_at` DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$offset}, {$limit}";
        }
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Öne çıkan danışmanları getir
     */
    public function getFeatured($limit = 6) {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `status` = 'active' AND `is_featured` = 1 
                ORDER BY `display_order` ASC, `created_at` DESC 
                LIMIT {$limit}";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * ID'ye göre danışman bul
     */
    public function find($id) {
        $sql = "SELECT * FROM `{$this->table}` WHERE `id` = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Slug'a göre danışman bul
     */
    public function findBySlug($slug) {
        $sql = "SELECT * FROM `{$this->table}` WHERE `slug` = ? AND `status` = 'active'";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Yeni danışman oluştur
     */
    public function create($data) {
        $sql = "INSERT INTO `{$this->table}` 
                (`first_name`, `last_name`, `slug`, `photo`, `phone`, `email`, `specializations`, `experience_years`, `bio`, `facebook`, `twitter`, `instagram`, `linkedin`, `status`, `is_featured`, `display_order`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $result = $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['slug'],
            $data['photo'] ?? null,
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['specializations'] ?? null,
            $data['experience_years'] ?? 0,
            $data['bio'] ?? null,
            $data['facebook'] ?? null,
            $data['twitter'] ?? null,
            $data['instagram'] ?? null,
            $data['linkedin'] ?? null,
            $data['status'] ?? 'active',
            $data['is_featured'] ?? 0,
            $data['display_order'] ?? 0
        ]);
        
        return $result ? $this->db->getConnection()->lastInsertId() : false;
    }
    
    /**
     * Danışman güncelle
     */
    public function update($id, $data) {
        $sql = "UPDATE `{$this->table}` SET 
                `first_name` = ?, `last_name` = ?, `slug` = ?, `photo` = ?, `phone` = ?, `email` = ?, 
                `specializations` = ?, `experience_years` = ?, `bio` = ?, `facebook` = ?, `twitter` = ?, 
                `instagram` = ?, `linkedin` = ?, `status` = ?, `is_featured` = ?, `display_order` = ?
                WHERE `id` = ?";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        return $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['slug'],
            $data['photo'] ?? null,
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['specializations'] ?? null,
            $data['experience_years'] ?? 0,
            $data['bio'] ?? null,
            $data['facebook'] ?? null,
            $data['twitter'] ?? null,
            $data['instagram'] ?? null,
            $data['linkedin'] ?? null,
            $data['status'] ?? 'active',
            $data['is_featured'] ?? 0,
            $data['display_order'] ?? 0,
            $id
        ]);
    }
    
    /**
     * Danışman sil
     */
    public function delete($id) {
        $sql = "DELETE FROM `{$this->table}` WHERE `id` = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        return $stmt->execute([$id]);
    }
}
