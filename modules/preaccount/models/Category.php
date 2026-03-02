<?php
/**
 * Ön Muhasebe - Kategori (Gelir/Gider) Model
 */

class PreaccountCategory extends Model {
    protected $table = 'preaccount_categories';

    public function createTables() {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `type` enum('income','expense') NOT NULL,
            `account_code` varchar(20) DEFAULT NULL COMMENT 'Muhasebe hesap kodu',
            `parent_id` int(11) DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `type` (`type`),
            KEY `parent_id` (`parent_id`),
            KEY `account_code` (`account_code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        try {
            $this->db->query($sql);
            $this->ensureAccountCodeColumn();
            return true;
        } catch (Exception $e) {
            error_log("PreaccountCategory createTables: " . $e->getMessage());
            return false;
        }
    }

    /** Mevcut tabloya account_code sütunu ekler (migration) */
    public function ensureAccountCodeColumn() {
        try {
            $cols = $this->db->fetchAll("SHOW COLUMNS FROM `{$this->table}` LIKE 'account_code'");
            if (empty($cols)) {
                $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `account_code` varchar(20) DEFAULT NULL COMMENT 'Muhasebe hesap kodu' AFTER `type`, ADD KEY `account_code` (`account_code`)");
            }
        } catch (Exception $e) {
            error_log("PreaccountCategory ensureAccountCodeColumn: " . $e->getMessage());
        }
    }

    public function getByType($type) {
        $sql = "SELECT * FROM `{$this->table}` WHERE `type` = ? ORDER BY `name`";
        return $this->db->fetchAll($sql, [$type]);
    }

    public function getAllGroupedByType() {
        $all = $this->all('name');
        $income = [];
        $expense = [];
        foreach ($all as $row) {
            if ($row['type'] === 'income') $income[] = $row;
            else $expense[] = $row;
        }
        return ['income' => $income, 'expense' => $expense];
    }

    public function dropTables() {
        try {
            $this->db->query("DROP TABLE IF EXISTS `{$this->table}`");
            return true;
        } catch (Exception $e) {
            error_log("PreaccountCategory dropTables: " . $e->getMessage());
            return false;
        }
    }
}
