<?php
/**
 * Ön Muhasebe - Hesap (Kasa/Banka) Model
 */

class PreaccountAccount extends Model {
    protected $table = 'preaccount_accounts';

    public function createTables() {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `type` enum('bank','cash') DEFAULT 'cash',
            `opening_balance` decimal(15,2) DEFAULT 0.00,
            `currency` varchar(10) DEFAULT 'TRY',
            `is_active` tinyint(1) DEFAULT 1,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `type` (`type`),
            KEY `is_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        try {
            $this->db->query($sql);
            return true;
        } catch (Exception $e) {
            error_log("PreaccountAccount createTables: " . $e->getMessage());
            return false;
        }
    }

    public function getActive() {
        $sql = "SELECT * FROM `{$this->table}` WHERE `is_active` = 1 ORDER BY `name`";
        return $this->db->fetchAll($sql);
    }

    /**
     * Hesap bakiyesi = açılış bakiyesi + gelir hareketleri - gider hareketleri
     */
    public function getBalance($accountId) {
        $acc = $this->find($accountId);
        if (!$acc) return 0;
        $opening = (float) $acc['opening_balance'];
        $sql = "SELECT 
            COALESCE(SUM(CASE WHEN `type` = 'income' THEN `amount` ELSE 0 END), 0) -
            COALESCE(SUM(CASE WHEN `type` = 'expense' THEN `amount` ELSE 0 END), 0) as net
            FROM `preaccount_transactions` WHERE `account_id` = ?";
        $row = $this->db->fetch($sql, [$accountId]);
        $net = (float) ($row['net'] ?? 0);
        return $opening + $net;
    }

    public function dropTables() {
        try {
            $this->db->query("DROP TABLE IF EXISTS `{$this->table}`");
            return true;
        } catch (Exception $e) {
            error_log("PreaccountAccount dropTables: " . $e->getMessage());
            return false;
        }
    }
}
