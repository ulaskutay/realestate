<?php
/**
 * Ön Muhasebe - Fatura Kalemi Model
 */

class PreaccountInvoiceItem extends Model {
    protected $table = 'preaccount_invoice_items';

    public function createTables() {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `invoice_id` int(11) NOT NULL,
            `description` varchar(500) DEFAULT NULL,
            `quantity` decimal(12,2) DEFAULT 1.00,
            `unit_price` decimal(15,2) DEFAULT 0.00,
            `tax_rate` decimal(5,2) DEFAULT 0.00,
            `amount` decimal(15,2) DEFAULT 0.00,
            `sort_order` int(11) DEFAULT 0,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `invoice_id` (`invoice_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        try {
            $this->db->query($sql);
            return true;
        } catch (Exception $e) {
            error_log("PreaccountInvoiceItem createTables: " . $e->getMessage());
            return false;
        }
    }

    public function getByInvoiceId($invoiceId) {
        $sql = "SELECT * FROM `{$this->table}` WHERE invoice_id = ? ORDER BY sort_order, id";
        return $this->db->fetchAll($sql, [$invoiceId]);
    }

    public function deleteByInvoiceId($invoiceId) {
        $sql = "DELETE FROM `{$this->table}` WHERE invoice_id = ?";
        $this->db->query($sql, [$invoiceId]);
    }

    public function dropTables() {
        try {
            $this->db->query("DROP TABLE IF EXISTS `{$this->table}`");
            return true;
        } catch (Exception $e) {
            error_log("PreaccountInvoiceItem dropTables: " . $e->getMessage());
            return false;
        }
    }
}
