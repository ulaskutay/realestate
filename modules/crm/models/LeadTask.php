<?php
/**
 * Lead Task Model
 * Lead görevleri için model sınıfı
 */

class LeadTask extends Model {
    protected $table = 'crm_lead_tasks';
    
    /**
     * Tabloyu oluştur
     */
    public function createTables() {
        $sql = "CREATE TABLE IF NOT EXISTS `crm_lead_tasks` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `lead_id` int(11) NOT NULL,
            `title` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `status` enum('pending','completed') DEFAULT 'pending',
            `due_date` datetime DEFAULT NULL,
            `assigned_to` int(11) DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `lead_id` (`lead_id`),
            KEY `status` (`status`),
            KEY `assigned_to` (`assigned_to`),
            KEY `due_date` (`due_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        try {
            $this->db->query($sql);
            return true;
        } catch (Exception $e) {
            error_log("CRM lead tasks table creation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Tabloyu sil
     */
    public function dropTables() {
        try {
            $this->db->query("DROP TABLE IF EXISTS `crm_lead_tasks`");
            return true;
        } catch (Exception $e) {
            error_log("CRM lead tasks table drop error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lead ID'ye göre görevleri getir
     */
    public function getByLeadId($leadId) {
        try {
            return $this->db->fetchAll(
                "SELECT t.*, u.name as assigned_name, u.email as assigned_email 
                 FROM `{$this->table}` t 
                 LEFT JOIN `users` u ON t.assigned_to = u.id 
                 WHERE t.lead_id = ? 
                 ORDER BY t.due_date ASC, t.created_at DESC",
                [$leadId]
            );
        } catch (Exception $e) {
            // Users tablosu yoksa sadece görevleri getir
            return $this->where('lead_id', $leadId);
        }
    }
    
    /**
     * Görev durumunu güncelle
     */
    public function updateStatus($id, $status) {
        return $this->update($id, ['status' => $status]);
    }
    
    /**
     * Bekleyen görevleri getir
     */
    public function getPending($userId = null) {
        $sql = "SELECT * FROM `{$this->table}` WHERE `status` = 'pending'";
        $params = [];
        
        if ($userId) {
            $sql .= " AND `assigned_to` = ?";
            $params[] = $userId;
        }
        
        $sql .= " ORDER BY `due_date` ASC, `created_at` DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
}
