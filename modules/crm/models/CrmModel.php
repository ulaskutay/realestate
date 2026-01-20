<?php
/**
 * CRM Model
 * Lead yönetimi için model sınıfı
 */

class CrmModel extends Model {
    protected $table = 'crm_leads';
    
    /**
     * Tabloyu oluştur
     */
    public function createTables() {
        $sql = "CREATE TABLE IF NOT EXISTS `crm_leads` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `phone` varchar(50) DEFAULT NULL,
            `email` varchar(255) DEFAULT NULL,
            `property_type` varchar(100) DEFAULT NULL COMMENT 'Satılık/Kiralık, Daire/Villa vb.',
            `post_id` int(11) DEFAULT NULL COMMENT 'Seçilen ilan ID (posts tablosundan)',
            `location` varchar(255) DEFAULT NULL COMMENT 'Lokasyon/İlçe',
            `budget` varchar(100) DEFAULT NULL COMMENT 'Bütçe aralığı',
            `room_count` varchar(50) DEFAULT NULL COMMENT 'Oda sayısı',
            `source` enum('meta','form','manual') DEFAULT 'manual' COMMENT 'Lead kaynağı',
            `status` enum('new','contacted','quoted','closed','cancelled') DEFAULT 'new',
            `meta_lead_id` varchar(100) DEFAULT NULL COMMENT 'Meta Lead Ads ID',
            `form_submission_id` int(11) DEFAULT NULL COMMENT 'Form gönderim ID',
            `notes` text DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `status` (`status`),
            KEY `source` (`source`),
            KEY `meta_lead_id` (`meta_lead_id`),
            KEY `form_submission_id` (`form_submission_id`),
            KEY `post_id` (`post_id`),
            KEY `created_at` (`created_at`),
            KEY `phone` (`phone`),
            KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        try {
            $this->db->query($sql);
            
            // Mevcut tablolara post_id sütunu ekle (eğer yoksa)
            try {
                $columns = $this->db->fetchAll("SHOW COLUMNS FROM `{$this->table}` LIKE 'post_id'");
                if (empty($columns)) {
                    $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `post_id` int(11) DEFAULT NULL COMMENT 'Seçilen ilan ID (posts tablosundan)' AFTER `property_type`");
                    $this->db->query("ALTER TABLE `{$this->table}` ADD KEY `post_id` (`post_id`)");
                }
            } catch (Exception $e) {
                // Sütun zaten varsa veya hata oluşursa sessizce devam et
                error_log("CRM post_id column check: " . $e->getMessage());
            }
            
            return true;
        } catch (Exception $e) {
            error_log("CRM tables creation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Tabloyu sil
     */
    public function dropTables() {
        try {
            $this->db->query("DROP TABLE IF EXISTS `crm_leads`");
            return true;
        } catch (Exception $e) {
            error_log("CRM tables drop error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Gelişmiş arama
     */
    public function search($searchTerm = '', $filters = [], $limit = 20, $offset = 0) {
        $sql = "SELECT * FROM `{$this->table}` WHERE 1=1";
        $params = [];
        
        // Arama terimi
        if (!empty($searchTerm)) {
            $sql .= " AND (
                `name` LIKE ? OR 
                `phone` LIKE ? OR 
                `email` LIKE ? OR 
                `location` LIKE ? OR
                `property_type` LIKE ?
            )";
            $searchPattern = '%' . $searchTerm . '%';
            $params = array_merge($params, [$searchPattern, $searchPattern, $searchPattern, $searchPattern, $searchPattern]);
        }
        
        // Filtreler
        if (!empty($filters['status'])) {
            $sql .= " AND `status` = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['source'])) {
            $sql .= " AND `source` = ?";
            $params[] = $filters['source'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(`created_at`) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(`created_at`) <= ?";
            $params[] = $filters['date_to'];
        }
        
        // LIMIT ve OFFSET için güvenli integer değerler kullan
        $limit = (int)$limit;
        $offset = (int)$offset;
        $sql .= " ORDER BY `created_at` DESC LIMIT {$limit} OFFSET {$offset}";
        
        try {
            $result = $this->db->fetchAll($sql, $params);
            // Eğer null veya false dönerse boş array döndür
            return $result === null || $result === false ? [] : $result;
        } catch (Exception $e) {
            error_log("CRM search error: " . $e->getMessage() . " - SQL: " . $sql . " - Params: " . print_r($params, true));
            return [];
        }
    }
    
    /**
     * Arama sonuç sayısı
     */
    public function searchCount($searchTerm = '', $filters = []) {
        $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE 1=1";
        $params = [];
        
        // Arama terimi
        if (!empty($searchTerm)) {
            $sql .= " AND (
                `name` LIKE ? OR 
                `phone` LIKE ? OR 
                `email` LIKE ? OR 
                `location` LIKE ? OR
                `property_type` LIKE ?
            )";
            $searchPattern = '%' . $searchTerm . '%';
            $params = array_merge($params, [$searchPattern, $searchPattern, $searchPattern, $searchPattern, $searchPattern]);
        }
        
        // Filtreler
        if (!empty($filters['status'])) {
            $sql .= " AND `status` = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['source'])) {
            $sql .= " AND `source` = ?";
            $params[] = $filters['source'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(`created_at`) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(`created_at`) <= ?";
            $params[] = $filters['date_to'];
        }
        
        try {
            $result = $this->db->fetch($sql, $params);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("CRM searchCount error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Durum güncelle
     */
    public function updateStatus($id, $status) {
        return $this->update($id, ['status' => $status]);
    }
    
    /**
     * İstatistikler
     */
    public function getStats() {
        $stats = [
            'total' => 0,
            'new' => 0,
            'contacted' => 0,
            'quoted' => 0,
            'closed' => 0,
            'cancelled' => 0
        ];
        
        try {
            $result = $this->db->fetch("SELECT COUNT(*) as count FROM `{$this->table}`");
            $stats['total'] = $result['count'] ?? 0;
            
            $statusCounts = $this->db->fetchAll(
                "SELECT `status`, COUNT(*) as count FROM `{$this->table}` GROUP BY `status`"
            );
            
            foreach ($statusCounts as $row) {
                if (isset($stats[$row['status']])) {
                    $stats[$row['status']] = $row['count'];
                }
            }
        } catch (Exception $e) {
            error_log("CRM stats error: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * Kaynak istatistikleri
     */
    public function getSourceStats() {
        try {
            return $this->db->fetchAll(
                "SELECT `source`, COUNT(*) as count FROM `{$this->table}` GROUP BY `source`"
            );
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Durum istatistikleri
     */
    public function getStatusStats() {
        try {
            return $this->db->fetchAll(
                "SELECT `status`, COUNT(*) as count FROM `{$this->table}` GROUP BY `status`"
            );
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Son leadler
     */
    public function getRecent($limit = 10) {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM `{$this->table}` ORDER BY `created_at` DESC LIMIT ?",
                [$limit]
            );
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Kanban için tüm leadler (durum bazlı gruplanmış)
     */
    public function getAllForKanban() {
        try {
            $leads = $this->db->fetchAll(
                "SELECT * FROM `{$this->table}` ORDER BY `created_at` DESC"
            );
            
            // Durum bazlı grupla
            $grouped = [
                'new' => [],
                'contacted' => [],
                'quoted' => [],
                'closed' => [],
                'cancelled' => []
            ];
            
            foreach ($leads as $lead) {
                $status = $lead['status'] ?? 'new';
                if (isset($grouped[$status])) {
                    $grouped[$status][] = $lead;
                }
            }
            
            return $grouped;
        } catch (Exception $e) {
            return [
                'new' => [],
                'contacted' => [],
                'quoted' => [],
                'closed' => [],
                'cancelled' => []
            ];
        }
    }
    
    /**
     * Meta Lead ID ile lead bul
     */
    public function findByMetaLeadId($metaLeadId) {
        return $this->findOne('meta_lead_id', $metaLeadId);
    }
    
    /**
     * Telefon numarası ile lead bul
     */
    public function findByPhone($phone) {
        // Telefon numarasını normalize et
        $normalized = $this->normalizePhone($phone);
        $leads = $this->where('phone', $normalized);
        
        if (!empty($leads)) {
            return $leads[0];
        }
        
        // Normalize edilmemiş versiyonu da dene
        return $this->findOne('phone', $phone);
    }
    
    /**
     * Telefon numarasını normalize et
     */
    private function normalizePhone($phone) {
        // Sadece rakamları al
        return preg_replace('/[^0-9]/', '', $phone);
    }
}
