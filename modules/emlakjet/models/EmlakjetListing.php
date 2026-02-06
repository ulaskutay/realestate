<?php
/**
 * Emlakjet Listing Model
 * Senkronizasyon durumu yönetimi
 */

class EmlakjetListing extends Model {
    protected $table = 'emlakjet_listings';
    
    /**
     * Tabloyu oluştur
     */
    public function createTables() {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `listing_id` int(11) NOT NULL COMMENT 'realestate_listings.id',
            `emlakjet_id` varchar(100) DEFAULT NULL COMMENT 'Emlakjet ilan ID',
            `sync_status` enum('pending','synced','failed','deleted') DEFAULT 'pending',
            `sync_direction` enum('push','pull','both') DEFAULT 'push',
            `last_sync_at` datetime DEFAULT NULL,
            `last_error` text DEFAULT NULL,
            `sync_data` text DEFAULT NULL COMMENT 'JSON: Son senkronize edilen veri',
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `listing_id` (`listing_id`),
            KEY `emlakjet_id` (`emlakjet_id`),
            KEY `sync_status` (`sync_status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        try {
            $this->db->query($sql);
            return true;
        } catch (Exception $e) {
            error_log("Emlakjet listings table creation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Tabloyu sil
     */
    public function dropTables() {
        try {
            $this->db->query("DROP TABLE IF EXISTS `{$this->table}`");
            return true;
        } catch (Exception $e) {
            error_log("Emlakjet listings table drop error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Listing ID'ye göre bul
     */
    public function findByListingId($listingId) {
        return $this->findOne('listing_id', $listingId);
    }
    
    /**
     * Emlakjet ID'ye göre bul
     */
    public function findByEmlakjetId($emlakjetId) {
        return $this->findOne('emlakjet_id', $emlakjetId);
    }
    
    /**
     * Senkronizasyon durumunu güncelle
     */
    public function updateSyncStatus($listingId, $status, $emlakjetId = null, $error = null, $syncData = null) {
        $existing = $this->findByListingId($listingId);
        
        $data = [
            'sync_status' => $status,
            'last_sync_at' => date('Y-m-d H:i:s')
        ];
        
        if ($emlakjetId !== null) {
            $data['emlakjet_id'] = $emlakjetId;
        }
        
        if ($error !== null) {
            $data['last_error'] = $error;
        }
        
        if ($syncData !== null) {
            $data['sync_data'] = is_array($syncData) ? json_encode($syncData, JSON_UNESCAPED_UNICODE) : $syncData;
        }
        
        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            $data['listing_id'] = $listingId;
            return $this->create($data);
        }
    }
    
    /**
     * İstatistikler
     */
    public function getStats() {
        $stats = [
            'total' => 0,
            'synced' => 0,
            'pending' => 0,
            'failed' => 0,
            'deleted' => 0
        ];
        
        try {
            $result = $this->db->fetch("SELECT COUNT(*) as count FROM `{$this->table}`");
            $stats['total'] = $result['count'] ?? 0;
            
            $statusCounts = $this->db->fetchAll(
                "SELECT `sync_status`, COUNT(*) as count FROM `{$this->table}` GROUP BY `sync_status`"
            );
            
            foreach ($statusCounts as $row) {
                if (isset($stats[$row['sync_status']])) {
                    $stats[$row['sync_status']] = $row['count'];
                }
            }
        } catch (Exception $e) {
            error_log("Emlakjet stats error: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * Son senkronizasyonlar
     */
    public function getRecentSyncs($limit = 10) {
        try {
            return $this->db->fetchAll(
                "SELECT ej.*, l.title, l.location, l.price
                 FROM `{$this->table}` ej
                 LEFT JOIN `realestate_listings` l ON ej.listing_id = l.id
                 ORDER BY ej.last_sync_at DESC
                 LIMIT ?",
                [$limit]
            );
        } catch (Exception $e) {
            error_log("Emlakjet recent syncs error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Başarısız senkronizasyonlar
     */
    public function getFailedSyncs($limit = 10) {
        try {
            return $this->db->fetchAll(
                "SELECT ej.*, l.title, l.location, l.price
                 FROM `{$this->table}` ej
                 LEFT JOIN `realestate_listings` l ON ej.listing_id = l.id
                 WHERE ej.sync_status = 'failed'
                 ORDER BY ej.updated_at DESC
                 LIMIT ?",
                [$limit]
            );
        } catch (Exception $e) {
            error_log("Emlakjet failed syncs error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Bekleyen ilanları getir
     */
    public function getPendingListings($limit = 100) {
        try {
            // Önce realestate_listings tablosunun var olup olmadığını kontrol et
            $checkTable = "SHOW TABLES LIKE 'realestate_listings'";
            $tableExists = $this->db->fetch($checkTable);
            
            if (!$tableExists) {
                error_log("Emlakjet: realestate_listings table does not exist");
                return [];
            }
            
            // Tüm yayınlanmış ilanları getir ve emlakjet_listings tablosunda kaydı olmayanları veya pending/failed olanları dahil et
            $sql = "SELECT l.id as listing_id, l.title, l.location, l.price, l.status,
                           ej.id as emlakjet_sync_id, ej.sync_status, ej.last_error, ej.listing_id as ej_listing_id
                    FROM `realestate_listings` l
                    LEFT JOIN `{$this->table}` ej ON l.id = ej.listing_id
                    WHERE l.status = 'published'
                    AND (ej.sync_status IS NULL OR ej.sync_status = 'pending' OR ej.sync_status = 'failed')
                    ORDER BY l.created_at DESC
                    LIMIT ?";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Emlakjet pending listings error: " . $e->getMessage());
            return [];
        }
    }
}
