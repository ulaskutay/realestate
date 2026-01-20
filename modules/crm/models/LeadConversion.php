<?php
/**
 * Lead Conversion Model
 * Dönüşüm takibi için model sınıfı
 */

class LeadConversion extends Model {
    protected $table = 'crm_lead_conversions';
    
    /**
     * Tabloyu oluştur
     */
    public function createTables() {
        $sql = "CREATE TABLE IF NOT EXISTS `crm_lead_conversions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `lead_id` int(11) NOT NULL,
            `type` enum('sale','rental') DEFAULT 'sale' COMMENT 'Dönüşüm tipi',
            `value` decimal(15,2) DEFAULT 0.00 COMMENT 'Dönüşüm değeri',
            `notes` text DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `lead_id` (`lead_id`),
            KEY `type` (`type`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        try {
            $this->db->query($sql);
            return true;
        } catch (Exception $e) {
            error_log("CRM lead conversions table creation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Tabloyu sil
     */
    public function dropTables() {
        try {
            $this->db->query("DROP TABLE IF EXISTS `crm_lead_conversions`");
            return true;
        } catch (Exception $e) {
            error_log("CRM lead conversions table drop error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lead ID'ye göre dönüşümleri getir
     */
    public function getByLeadId($leadId) {
        return $this->where('lead_id', $leadId);
    }
    
    /**
     * Dönüşüm istatistikleri
     */
    public function getStats() {
        try {
            $stats = [
                'total_conversions' => 0,
                'total_value' => 0,
                'sales_count' => 0,
                'rentals_count' => 0,
                'sales_value' => 0,
                'rentals_value' => 0
            ];
            
            $result = $this->db->fetch("SELECT COUNT(*) as count, SUM(`value`) as total FROM `{$this->table}`");
            $stats['total_conversions'] = $result['count'] ?? 0;
            $stats['total_value'] = $result['total'] ?? 0;
            
            $typeStats = $this->db->fetchAll(
                "SELECT `type`, COUNT(*) as count, SUM(`value`) as total FROM `{$this->table}` GROUP BY `type`"
            );
            
            foreach ($typeStats as $row) {
                if ($row['type'] === 'sale') {
                    $stats['sales_count'] = $row['count'];
                    $stats['sales_value'] = $row['total'] ?? 0;
                } elseif ($row['type'] === 'rental') {
                    $stats['rentals_count'] = $row['count'];
                    $stats['rentals_value'] = $row['total'] ?? 0;
                }
            }
            
            return $stats;
        } catch (Exception $e) {
            return [
                'total_conversions' => 0,
                'total_value' => 0,
                'sales_count' => 0,
                'rentals_count' => 0,
                'sales_value' => 0,
                'rentals_value' => 0
            ];
        }
    }
}
