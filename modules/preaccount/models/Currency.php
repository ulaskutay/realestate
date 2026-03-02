<?php
/**
 * Ön Muhasebe - Para Birimi Model (Çoklu para birimi)
 */

class PreaccountCurrency extends Model {
    protected $table = 'preaccount_currencies';

    public function createTables() {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `code` varchar(10) NOT NULL,
            `name` varchar(100) NOT NULL,
            `symbol` varchar(10) DEFAULT NULL,
            `exchange_rate` decimal(18,6) DEFAULT 1.000000 COMMENT 'Varsayılan para birimine göre kur',
            `is_default` tinyint(1) DEFAULT 0,
            `decimal_places` tinyint(1) DEFAULT 2,
            `is_active` tinyint(1) DEFAULT 1,
            `sort_order` int(11) DEFAULT 0,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `code` (`code`),
            KEY `is_default` (`is_default`),
            KEY `is_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        try {
            $this->db->query($sql);
            $this->seedDefaultCurrencies();
            return true;
        } catch (Exception $e) {
            error_log("PreaccountCurrency createTables: " . $e->getMessage());
            return false;
        }
    }

    private function seedDefaultCurrencies() {
        $count = $this->db->fetch("SELECT COUNT(*) as c FROM `{$this->table}`");
        if ((int)($count['c'] ?? 0) > 0) return;
        $dollar = '$';
        $this->db->query("INSERT INTO `{$this->table}` (`code`, `name`, `symbol`, `exchange_rate`, `is_default`, `decimal_places`, `is_active`, `sort_order`) VALUES
            ('TRY', 'Türk Lirası', '₺', 1.000000, 1, 2, 1, 0),
            ('USD', 'Amerikan Doları', '{$dollar}', 34.000000, 0, 2, 1, 1),
            ('EUR', 'Euro', '€', 36.000000, 0, 2, 1, 2)");
    }

    public function getActive() {
        $sql = "SELECT * FROM `{$this->table}` WHERE `is_active` = 1 ORDER BY `sort_order`, `code`";
        return $this->db->fetchAll($sql);
    }

    public function getDefault() {
        $row = $this->db->fetch("SELECT * FROM `{$this->table}` WHERE `is_default` = 1 LIMIT 1");
        if ($row) return $row;
        return $this->db->fetch("SELECT * FROM `{$this->table}` WHERE `is_active` = 1 ORDER BY id LIMIT 1");
    }

    public function getByCode($code) {
        return $this->findOne('code', $code);
    }

    /**
     * Tutarı varsayılan para birimine çevirir (exchange_rate ile çarpar)
     */
    public function convertToDefault($amount, $fromCurrencyCode) {
        $from = $this->getByCode($fromCurrencyCode);
        if (!$from) return (float) $amount;
        $default = $this->getDefault();
        if (!$default || $from['code'] === $default['code']) return (float) $amount;
        return (float) $amount * (float) $from['exchange_rate'];
    }

    /**
     * Tutarı formatlar (symbol + number)
     */
    public function format($amount, $currencyCode) {
        $cur = $this->getByCode($currencyCode);
        $dec = $cur ? (int) $cur['decimal_places'] : 2;
        $sym = $cur ? $cur['symbol'] : $currencyCode . ' ';
        return $sym . number_format((float) $amount, $dec, ',', '.');
    }

    public function dropTables() {
        try {
            $this->db->query("DROP TABLE IF EXISTS `{$this->table}`");
            return true;
        } catch (Exception $e) {
            error_log("PreaccountCurrency dropTables: " . $e->getMessage());
            return false;
        }
    }
}
