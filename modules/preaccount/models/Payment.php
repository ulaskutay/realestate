<?php
/**
 * Ön Muhasebe - Ödeme Model
 */

class PreaccountPayment extends Model {
    protected $table = 'preaccount_payments';

    public function createTables() {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `invoice_id` int(11) DEFAULT NULL,
            `account_id` int(11) NOT NULL,
            `amount` decimal(15,2) NOT NULL,
            `payment_date` date NOT NULL,
            `description` text DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `invoice_id` (`invoice_id`),
            KEY `account_id` (`account_id`),
            KEY `payment_date` (`payment_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        try {
            $this->db->query($sql);
            return true;
        } catch (Exception $e) {
            error_log("PreaccountPayment createTables: " . $e->getMessage());
            return false;
        }
    }

    public function getList($filters = [], $limit = 20, $offset = 0) {
        $sql = "SELECT p.*, a.name as account_name 
            FROM `{$this->table}` p 
            LEFT JOIN `preaccount_accounts` a ON p.account_id = a.id 
            WHERE 1=1";
        $params = [];
        if (!empty($filters['account_id'])) {
            $sql .= " AND p.account_id = ?";
            $params[] = $filters['account_id'];
        }
        if (!empty($filters['invoice_id'])) {
            $sql .= " AND p.invoice_id = ?";
            $params[] = $filters['invoice_id'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND p.payment_date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND p.payment_date <= ?";
            $params[] = $filters['date_to'];
        }
        $sql .= " ORDER BY p.payment_date DESC, p.id DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        return $this->db->fetchAll($sql, $params);
    }

    public function getCount($filters = []) {
        $sql = "SELECT COUNT(*) as cnt FROM `{$this->table}` p WHERE 1=1";
        $params = [];
        if (!empty($filters['account_id'])) { $sql .= " AND p.account_id = ?"; $params[] = $filters['account_id']; }
        if (!empty($filters['invoice_id'])) { $sql .= " AND p.invoice_id = ?"; $params[] = $filters['invoice_id']; }
        if (!empty($filters['date_from'])) { $sql .= " AND p.payment_date >= ?"; $params[] = $filters['date_from']; }
        if (!empty($filters['date_to'])) { $sql .= " AND p.payment_date <= ?"; $params[] = $filters['date_to']; }
        $row = $this->db->fetch($sql, $params);
        return (int) ($row['cnt'] ?? 0);
    }

    public function getByInvoiceId($invoiceId) {
        $sql = "SELECT p.*, a.name as account_name FROM `{$this->table}` p 
            LEFT JOIN `preaccount_accounts` a ON p.account_id = a.id 
            WHERE p.invoice_id = ? ORDER BY p.payment_date DESC";
        return $this->db->fetchAll($sql, [$invoiceId]);
    }

    public function dropTables() {
        try {
            $this->db->query("DROP TABLE IF EXISTS `{$this->table}`");
            return true;
        } catch (Exception $e) {
            error_log("PreaccountPayment dropTables: " . $e->getMessage());
            return false;
        }
    }
}
