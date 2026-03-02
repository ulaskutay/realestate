<?php
/**
 * Ön Muhasebe - Fatura Model
 */

class PreaccountInvoice extends Model {
    protected $table = 'preaccount_invoices';

    public function createTables() {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `number` varchar(100) NOT NULL,
            `date` date NOT NULL,
            `due_date` date DEFAULT NULL,
            `client_name` varchar(255) DEFAULT NULL,
            `client_tax_office` varchar(255) DEFAULT NULL,
            `client_tax_number` varchar(50) DEFAULT NULL,
            `client_address` text DEFAULT NULL,
            `status` enum('draft','sent','paid','cancelled') DEFAULT 'draft',
            `subtotal` decimal(15,2) DEFAULT 0.00,
            `tax_total` decimal(15,2) DEFAULT 0.00,
            `total` decimal(15,2) DEFAULT 0.00,
            `currency` varchar(10) DEFAULT 'TRY',
            `notes` text DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `number` (`number`),
            KEY `status` (`status`),
            KEY `date` (`date`),
            KEY `due_date` (`due_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        try {
            $this->db->query($sql);
            $this->ensureLeadListingColumns();
            return true;
        } catch (Exception $e) {
            error_log("PreaccountInvoice createTables: " . $e->getMessage());
            return false;
        }
    }

    /** CRM/İlan eşleme sütunları (migration) */
    public function ensureLeadListingColumns() {
        try {
            foreach (['lead_id' => 'int(11) DEFAULT NULL COMMENT \'CRM lead id\'', 'listing_id' => 'int(11) DEFAULT NULL COMMENT \'realestate_listings id\''] as $col => $def) {
                $cols = $this->db->fetchAll("SHOW COLUMNS FROM `{$this->table}` LIKE '$col'");
                if (empty($cols)) {
                    $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `$col` $def");
                    $this->db->query("ALTER TABLE `{$this->table}` ADD KEY `$col` (`$col`)");
                }
            }
        } catch (Exception $e) {
            error_log("PreaccountInvoice ensureLeadListingColumns: " . $e->getMessage());
        }
    }

    public function getByLeadId($leadId) {
        $sql = "SELECT * FROM `{$this->table}` WHERE lead_id = ? ORDER BY date DESC, id DESC";
        return $this->db->fetchAll($sql, [$leadId]);
    }

    public function getByListingId($listingId) {
        $sql = "SELECT * FROM `{$this->table}` WHERE listing_id = ? ORDER BY date DESC, id DESC";
        return $this->db->fetchAll($sql, [$listingId]);
    }

    public function getNextNumber($prefix = 'FTR', $year = null) {
        if ($year === null) $year = date('Y');
        $pattern = $prefix . '-' . $year . '-%';
        $sql = "SELECT number FROM `{$this->table}` WHERE number LIKE ? ORDER BY id DESC LIMIT 1";
        $row = $this->db->fetch($sql, [$pattern]);
        if (!$row) return $prefix . '-' . $year . '-0001';
        $num = preg_replace('/^.*-(\d+)$/', '$1', $row['number']);
        $next = (int) $num + 1;
        return $prefix . '-' . $year . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function getUnpaid() {
        $sql = "SELECT * FROM `{$this->table}` WHERE status IN ('draft','sent') AND total > 0 ORDER BY due_date ASC, id ASC";
        return $this->db->fetchAll($sql);
    }

    public function getOverdue() {
        $sql = "SELECT * FROM `{$this->table}` WHERE status IN ('draft','sent') AND due_date < CURDATE() ORDER BY due_date ASC";
        return $this->db->fetchAll($sql);
    }

    public function getList($filters = [], $limit = 20, $offset = 0) {
        $sql = "SELECT * FROM `{$this->table}` WHERE 1=1";
        $params = [];
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND date <= ?";
            $params[] = $filters['date_to'];
        }
        $sql .= " ORDER BY date DESC, id DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        return $this->db->fetchAll($sql, $params);
    }

    public function getCount($filters = []) {
        $sql = "SELECT COUNT(*) as cnt FROM `{$this->table}` WHERE 1=1";
        $params = [];
        if (!empty($filters['status'])) { $sql .= " AND status = ?"; $params[] = $filters['status']; }
        if (!empty($filters['date_from'])) { $sql .= " AND date >= ?"; $params[] = $filters['date_from']; }
        if (!empty($filters['date_to'])) { $sql .= " AND date <= ?"; $params[] = $filters['date_to']; }
        $row = $this->db->fetch($sql, $params);
        return (int) ($row['cnt'] ?? 0);
    }

    public function getTotalPaid($invoiceId) {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM `preaccount_payments` WHERE invoice_id = ?";
        $row = $this->db->fetch($sql, [$invoiceId]);
        return (float) ($row['total'] ?? 0);
    }

    public function dropTables() {
        try {
            $this->db->query("DROP TABLE IF EXISTS `{$this->table}`");
            return true;
        } catch (Exception $e) {
            error_log("PreaccountInvoice dropTables: " . $e->getMessage());
            return false;
        }
    }
}
