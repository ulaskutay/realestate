<?php
/**
 * Ön Muhasebe - Hareket (Gelir/Gider) Model
 */

class PreaccountTransaction extends Model {
    protected $table = 'preaccount_transactions';

    public function createTables() {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `account_id` int(11) NOT NULL,
            `category_id` int(11) DEFAULT NULL,
            `type` enum('income','expense') NOT NULL,
            `amount` decimal(15,2) NOT NULL,
            `date` date NOT NULL,
            `description` text DEFAULT NULL,
            `reference_type` varchar(50) DEFAULT NULL,
            `reference_id` int(11) DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `account_id` (`account_id`),
            KEY `category_id` (`category_id`),
            KEY `type` (`type`),
            KEY `date` (`date`),
            KEY `reference_type` (`reference_type`),
            KEY `reference_id` (`reference_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        try {
            $this->db->query($sql);
            return true;
        } catch (Exception $e) {
            error_log("PreaccountTransaction createTables: " . $e->getMessage());
            return false;
        }
    }

    public function getList($filters = [], $limit = 20, $offset = 0) {
        $sql = "SELECT t.*, a.name as account_name, c.name as category_name, c.account_code as category_account_code 
            FROM `{$this->table}` t 
            LEFT JOIN `preaccount_accounts` a ON t.account_id = a.id 
            LEFT JOIN `preaccount_categories` c ON t.category_id = c.id 
            WHERE 1=1";
        $params = [];
        if (!empty($filters['account_id'])) {
            $sql .= " AND t.account_id = ?";
            $params[] = $filters['account_id'];
        }
        if (!empty($filters['category_id'])) {
            $sql .= " AND t.category_id = ?";
            $params[] = $filters['category_id'];
        }
        if (!empty($filters['type'])) {
            $sql .= " AND t.type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND t.date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND t.date <= ?";
            $params[] = $filters['date_to'];
        }
        $sql .= " ORDER BY t.date DESC, t.id DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        return $this->db->fetchAll($sql, $params);
    }

    public function getCount($filters = []) {
        $sql = "SELECT COUNT(*) as cnt FROM `{$this->table}` t WHERE 1=1";
        $params = [];
        if (!empty($filters['account_id'])) { $sql .= " AND t.account_id = ?"; $params[] = $filters['account_id']; }
        if (!empty($filters['category_id'])) { $sql .= " AND t.category_id = ?"; $params[] = $filters['category_id']; }
        if (!empty($filters['type'])) { $sql .= " AND t.type = ?"; $params[] = $filters['type']; }
        if (!empty($filters['date_from'])) { $sql .= " AND t.date >= ?"; $params[] = $filters['date_from']; }
        if (!empty($filters['date_to'])) { $sql .= " AND t.date <= ?"; $params[] = $filters['date_to']; }
        $row = $this->db->fetch($sql, $params);
        return (int) ($row['cnt'] ?? 0);
    }

    public function getSummaryByPeriod($dateFrom, $dateTo) {
        $sql = "SELECT 
            type,
            SUM(amount) as total 
            FROM `{$this->table}` 
            WHERE date >= ? AND date <= ? 
            GROUP BY type";
        $rows = $this->db->fetchAll($sql, [$dateFrom, $dateTo]);
        $out = ['income' => 0, 'expense' => 0];
        foreach ($rows as $r) {
            $out[$r['type']] = (float) $r['total'];
        }
        return $out;
    }

    /** Filtreye göre gelir/gider toplamı (liste sayfası özeti için) */
    public function getSummaryByFilters($filters = []) {
        $sql = "SELECT type, SUM(amount) as total FROM `{$this->table}` t WHERE 1=1";
        $params = [];
        if (!empty($filters['account_id'])) { $sql .= " AND t.account_id = ?"; $params[] = $filters['account_id']; }
        if (!empty($filters['category_id'])) { $sql .= " AND t.category_id = ?"; $params[] = $filters['category_id']; }
        if (!empty($filters['type'])) { $sql .= " AND t.type = ?"; $params[] = $filters['type']; }
        if (!empty($filters['date_from'])) { $sql .= " AND t.date >= ?"; $params[] = $filters['date_from']; }
        if (!empty($filters['date_to'])) { $sql .= " AND t.date <= ?"; $params[] = $filters['date_to']; }
        $sql .= " GROUP BY type";
        $rows = $this->db->fetchAll($sql, $params);
        $out = ['income' => 0, 'expense' => 0];
        foreach ($rows as $r) {
            $out[$r['type']] = (float) $r['total'];
        }
        return $out;
    }

    public function getRecent($limit = 10) {
        $sql = "SELECT t.*, a.name as account_name, c.name as category_name 
            FROM `{$this->table}` t 
            LEFT JOIN `preaccount_accounts` a ON t.account_id = a.id 
            LEFT JOIN `preaccount_categories` c ON t.category_id = c.id 
            ORDER BY t.date DESC, t.id DESC LIMIT " . (int)$limit;
        return $this->db->fetchAll($sql);
    }

    /** reference_type: crm_lead, listing, invoice vb. */
    public function getByReference($referenceType, $referenceId) {
        $sql = "SELECT t.*, a.name as account_name, c.name as category_name, c.account_code 
            FROM `{$this->table}` t 
            LEFT JOIN `preaccount_accounts` a ON t.account_id = a.id 
            LEFT JOIN `preaccount_categories` c ON t.category_id = c.id 
            WHERE t.reference_type = ? AND t.reference_id = ? 
            ORDER BY t.date DESC, t.id DESC";
        return $this->db->fetchAll($sql, [$referenceType, $referenceId]);
    }

    public function dropTables() {
        try {
            $this->db->query("DROP TABLE IF EXISTS `{$this->table}`");
            return true;
        } catch (Exception $e) {
            error_log("PreaccountTransaction dropTables: " . $e->getMessage());
            return false;
        }
    }
}
