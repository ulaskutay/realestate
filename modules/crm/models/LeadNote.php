<?php
/**
 * Lead Note Model
 * Lead notları için model sınıfı
 */

class LeadNote extends Model {
    protected $table = 'crm_lead_notes';
    
    /**
     * Tabloyu oluştur
     */
    public function createTables() {
        $sql = "CREATE TABLE IF NOT EXISTS `crm_lead_notes` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `lead_id` int(11) NOT NULL,
            `note` text NOT NULL,
            `user_id` int(11) DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `lead_id` (`lead_id`),
            KEY `user_id` (`user_id`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        try {
            $this->db->query($sql);
            return true;
        } catch (Exception $e) {
            error_log("CRM lead notes table creation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Tabloyu sil
     */
    public function dropTables() {
        try {
            $this->db->query("DROP TABLE IF EXISTS `crm_lead_notes`");
            return true;
        } catch (Exception $e) {
            error_log("CRM lead notes table drop error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lead ID'ye göre notları getir
     */
    public function getByLeadId($leadId) {
        try {
            return $this->db->fetchAll(
                "SELECT n.*, u.name as user_name, u.email as user_email 
                 FROM `{$this->table}` n 
                 LEFT JOIN `users` u ON n.user_id = u.id 
                 WHERE n.lead_id = ? 
                 ORDER BY n.created_at DESC",
                [$leadId]
            );
        } catch (Exception $e) {
            // Users tablosu yoksa sadece notları getir
            return $this->where('lead_id', $leadId);
        }
    }
}
