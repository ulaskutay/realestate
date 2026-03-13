<?php
/**
 * Komisyon Sözleşmeleri Model
 * commission_contracts ve commission_contract_items tabloları
 */
if (!class_exists('CommissionContractsModel')) {
class CommissionContractsModel {
    private $db;
    private $table = 'commission_contracts';
    private $itemsTable = 'commission_contract_items';

    public function __construct() {
        $this->db = Database::getInstance();
        // Tablo yoksa oluştur (modül activate edilmemiş veya DB sıfırlanmış olabilir)
        $this->ensureTablesExist();
        // Mevcut kurulumlarda yeni kolonları eklemek için (modül reactivate edilmeden)
        $this->ensureContractTrackingColumns();
        $this->ensureSignatureParty2Column();
        $this->ensureSignTokenColumn();
        $this->ensureSignedAtParty2Column();
        $this->ensureParty3Columns();
    }

    /**
     * Tabloları oluştur
     */
    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `contract_number` varchar(50) DEFAULT NULL,
            `contract_name` varchar(255) DEFAULT NULL,
            `client_type` enum('individual','legal') NOT NULL DEFAULT 'individual',
            `client_name` varchar(255) NOT NULL,
            `client_tax_number` varchar(50) DEFAULT NULL,
            `client_tax_office` varchar(100) DEFAULT NULL,
            `client_address` text DEFAULT NULL,
            `client_phone` varchar(50) DEFAULT NULL,
            `client_email` varchar(100) DEFAULT NULL,
            `status` enum('draft','signed') NOT NULL DEFAULT 'draft',
            `signed_at` datetime DEFAULT NULL,
            `signature_data` longtext DEFAULT NULL,
            `created_by` int(11) DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `status` (`status`),
            KEY `created_by` (`created_by`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $this->db->query($sql);

        $sqlItems = "CREATE TABLE IF NOT EXISTS `{$this->itemsTable}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `contract_id` int(11) NOT NULL,
            `description` text NOT NULL,
            `listing_type` enum('sale','rent') NOT NULL DEFAULT 'sale',
            `price` decimal(15,2) NOT NULL DEFAULT 0.00,
            `commission_type` enum('percent','one_month','custom') NOT NULL DEFAULT 'percent',
            `commission_value` decimal(8,2) NOT NULL DEFAULT 0.00,
            `vat_rate` decimal(5,2) NOT NULL DEFAULT 20.00,
            `commission_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
            `sort_order` int(11) NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `contract_id` (`contract_id`),
            KEY `sort_order` (`sort_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $this->db->query($sqlItems);

        $this->ensureTemplateColumns();
        $this->ensureContractTrackingColumns();
        $this->ensureSignatureParty2Column();
        $this->ensureSignTokenColumn();
        $this->ensureSignedAtParty2Column();
        $this->ensureParty3Columns();
    }

    /**
     * commission_contracts tablosu yoksa createTable() çağır (modül activate edilmeden kullanıldığında)
     */
    private function ensureTablesExist() {
        $row = $this->db->fetch(
            "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?",
            [$this->table]
        );
        if (empty($row)) {
            $this->createTable();
        }
    }

    /**
     * 3. taraf imzası için party3 kolonları (sign_token_party3, signature_data_party3, signed_at_party3)
     */
    private function ensureParty3Columns() {
        foreach (
            [
                ['sign_token_party3', 'varchar(64) DEFAULT NULL', 'signed_at_party2', 'ADD UNIQUE KEY `sign_token_party3` (`sign_token_party3`)'],
                ['signature_data_party3', 'longtext DEFAULT NULL', 'sign_token_party3', ''],
                ['signed_at_party3', 'datetime DEFAULT NULL', 'signature_data_party3', ''],
            ] as $def
        ) {
            list($col, $type, $after, $extra) = $def;
            $check = $this->db->fetch(
                "SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
                [$this->table, $col]
            );
            if (!empty($check) && (int) $check['cnt'] === 0) {
                $sql = "ALTER TABLE `{$this->table}` ADD COLUMN `{$col}` {$type} AFTER `{$after}`";
                if ($extra !== '') $sql .= ', ' . $extra;
                $this->db->query($sql);
            }
        }
    }

    /**
     * Sözleşme takip alanları: contract_number ve contract_name (PDF'te görünmez, sadece panel takibi için)
     */
    private function ensureContractTrackingColumns() {
        foreach (['contract_number', 'contract_name'] as $col) {
            $check = $this->db->fetch(
                "SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
                [$this->table, $col]
            );
            if (!empty($check) && (int) $check['cnt'] === 0) {
                $after = $col === 'contract_number' ? 'id' : 'contract_number';
                $type = $col === 'contract_number' ? 'varchar(50)' : 'varchar(255)';
                $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `{$col}` {$type} DEFAULT NULL AFTER `{$after}`");
            }
        }
    }

    /**
     * commission_contracts tablosuna template_id ve form_data kolonları yoksa ekle
     */
    private function ensureTemplateColumns() {
        $check = $this->db->fetch(
            "SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'template_id'",
            [$this->table]
        );
        if (!empty($check) && (int) $check['cnt'] === 0) {
            $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `template_id` int(11) DEFAULT NULL AFTER `client_email`, ADD COLUMN `form_data` json DEFAULT NULL AFTER `template_id`, ADD KEY `template_id` (`template_id`)");
        }
    }

    /**
     * Karşı taraf imzası için signature_data_party2 kolonu yoksa ekle
     */
    private function ensureSignatureParty2Column() {
        $check = $this->db->fetch(
            "SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'signature_data_party2'",
            [$this->table]
        );
        if (!empty($check) && (int) $check['cnt'] === 0) {
            $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `signature_data_party2` longtext DEFAULT NULL AFTER `signature_data`");
        }
    }

    /**
     * Karşı taraf imza linki için sign_token kolonu yoksa ekle
     */
    private function ensureSignTokenColumn() {
        $check = $this->db->fetch(
            "SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'sign_token'",
            [$this->table]
        );
        if (!empty($check) && (int) $check['cnt'] === 0) {
            $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `sign_token` varchar(64) DEFAULT NULL AFTER `signature_data_party2`, ADD UNIQUE KEY `sign_token` (`sign_token`)");
        }
    }

    /**
     * Karşı taraf imza tarihi için signed_at_party2 kolonu yoksa ekle
     */
    private function ensureSignedAtParty2Column() {
        $check = $this->db->fetch(
            "SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'signed_at_party2'",
            [$this->table]
        );
        if (!empty($check) && (int) $check['cnt'] === 0) {
            $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `signed_at_party2` datetime DEFAULT NULL AFTER `sign_token`");
        }
    }

    /**
     * Komisyon tutarını hesapla (KDV dahil)
     * Satış: price * (commission_value/100) * (1 + vat_rate/100)
     * Kiralık one_month: price * 1 * (1 + vat_rate/100)
     * Kiralık custom: price * (commission_value/100) * (1 + vat_rate/100)
     */
    public static function calculateCommissionAmount($listingType, $commissionType, $price, $commissionValue, $vatRate) {
        $price = (float) $price;
        $commissionValue = (float) $commissionValue;
        $vatRate = (float) $vatRate;
        if ($price <= 0) {
            return 0.0;
        }
        $mult = 1 + ($vatRate / 100);
        if ($listingType === 'sale') {
            return round($price * ($commissionValue / 100) * $mult, 2);
        }
        if ($commissionType === 'one_month') {
            return round($price * 1 * $mult, 2);
        }
        return round($price * ($commissionValue / 100) * $mult, 2);
    }

    /**
     * Sözleşme bul
     */
    public function find($id) {
        return $this->db->fetch("SELECT * FROM `{$this->table}` WHERE `id` = ?", [(int) $id]);
    }

    /**
     * Sözleşmeye ait taşınmaz kalemlerini getir
     */
    public function getItems($contractId) {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->itemsTable}` WHERE `contract_id` = ? ORDER BY `sort_order` ASC, `id` ASC",
            [(int) $contractId]
        );
    }

    /**
     * Belirtilen şablonu kullanan sözleşme sayısı
     */
    public function getCountByTemplateId($templateId) {
        $row = $this->db->fetch(
            "SELECT COUNT(*) AS cnt FROM `{$this->table}` WHERE `template_id` = ?",
            [(int) $templateId]
        );
        return isset($row['cnt']) ? (int) $row['cnt'] : 0;
    }

    /**
     * Admin listesi (filtre: status) – şablon adı LEFT JOIN ile
     */
    public function getAllForAdmin(array $filters = []) {
        $templatesTable = 'contract_templates';
        $sql = "SELECT c.*, t.`name` AS template_name,
                (SELECT COUNT(*) FROM `{$this->itemsTable}` i WHERE i.contract_id = c.id) AS item_count,
                (SELECT COALESCE(SUM(i.commission_amount), 0) FROM `{$this->itemsTable}` i WHERE i.contract_id = c.id) AS total_commission
                FROM `{$this->table}` c
                LEFT JOIN `{$templatesTable}` t ON t.`id` = c.`template_id`
                WHERE 1=1";
        $params = [];
        $status = isset($filters['status']) ? trim($filters['status']) : '';
        if ($status !== '' && in_array($status, ['draft', 'signed'], true)) {
            $sql .= " AND c.`status` = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY c.`created_at` DESC";
        if (!empty($params)) {
            return $this->db->fetchAll($sql, $params);
        }
        return $this->db->fetchAll($sql);
    }

    /**
     * Yeni sözleşme oluştur
     */
    public function create(array $data) {
        $formDataJson = null;
        if (isset($data['form_data'])) {
            $formDataJson = is_string($data['form_data']) ? $data['form_data'] : json_encode($data['form_data']);
        }
        $sql = "INSERT INTO `{$this->table}` 
            (`contract_number`, `contract_name`, `client_type`, `client_name`, `client_tax_number`, `client_tax_office`, `client_address`, `client_phone`, `client_email`, `template_id`, `form_data`, `status`, `created_by`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?)";
        $this->db->query($sql, [
            isset($data['contract_number']) ? trim((string) $data['contract_number']) : null,
            isset($data['contract_name']) ? trim((string) $data['contract_name']) : null,
            $data['client_type'] ?? 'individual',
            $data['client_name'] ?? '',
            $data['client_tax_number'] ?? null,
            $data['client_tax_office'] ?? null,
            $data['client_address'] ?? null,
            $data['client_phone'] ?? null,
            $data['client_email'] ?? null,
            isset($data['template_id']) ? (int) $data['template_id'] : null,
            $formDataJson,
            isset($data['created_by']) ? (int) $data['created_by'] : null
        ]);
        return (int) $this->db->getConnection()->lastInsertId();
    }

    /**
     * Sözleşme güncelle (sadece draft iken anlamlı)
     */
    public function update($id, array $data) {
        $base = [
            isset($data['contract_number']) ? trim((string) $data['contract_number']) : null,
            isset($data['contract_name']) ? trim((string) $data['contract_name']) : null,
            $data['client_type'] ?? 'individual',
            $data['client_name'] ?? '',
            $data['client_tax_number'] ?? null,
            $data['client_tax_office'] ?? null,
            $data['client_address'] ?? null,
            $data['client_phone'] ?? null,
            $data['client_email'] ?? null,
            isset($data['template_id']) ? (int) $data['template_id'] : null,
        ];
        if (array_key_exists('form_data', $data)) {
            $formDataJson = $data['form_data'] === null ? null : (is_string($data['form_data']) ? $data['form_data'] : json_encode($data['form_data']));
            $sql = "UPDATE `{$this->table}` SET 
                `contract_number` = ?, `contract_name` = ?,
                `client_type` = ?, `client_name` = ?, `client_tax_number` = ?, `client_tax_office` = ?, 
                `client_address` = ?, `client_phone` = ?, `client_email` = ?,
                `template_id` = ?, `form_data` = ?
                WHERE `id` = ?";
            return $this->db->query($sql, array_merge($base, [$formDataJson, (int) $id]));
        }
        $sql = "UPDATE `{$this->table}` SET 
            `contract_number` = ?, `contract_name` = ?,
            `client_type` = ?, `client_name` = ?, `client_tax_number` = ?, `client_tax_office` = ?, 
            `client_address` = ?, `client_phone` = ?, `client_email` = ?,
            `template_id` = ?
            WHERE `id` = ?";
        return $this->db->query($sql, array_merge($base, [(int) $id]));
    }

    /**
     * Taşınmaz kalemi ekle (komisyon hesaplama kullanılmıyor, commission_amount = 0)
     */
    public function addItem($contractId, array $item) {
        $sql = "INSERT INTO `{$this->itemsTable}` 
            (`contract_id`, `description`, `listing_type`, `price`, `commission_type`, `commission_value`, `vat_rate`, `commission_amount`, `sort_order`) 
            VALUES (?, ?, ?, ?, 'percent', 0, 20, 0, ?)";
        $this->db->query($sql, [
            (int) $contractId,
            $item['description'] ?? '',
            $item['listing_type'] ?? 'sale',
            (float) ($item['price'] ?? 0),
            (int) ($item['sort_order'] ?? 0)
        ]);
        return (int) $this->db->getConnection()->lastInsertId();
    }

    /**
     * Sözleşmenin tüm kalemlerini silip yeniden eklemek yerine: sil ve toplu ekle
     */
    public function setItems($contractId, array $items) {
        $this->db->query("DELETE FROM `{$this->itemsTable}` WHERE `contract_id` = ?", [(int) $contractId]);
        $sortOrder = 0;
        foreach ($items as $item) {
            $item['sort_order'] = $sortOrder++;
            $this->addItem($contractId, $item);
        }
    }

    /**
     * İmza at ve durumu güncelle (emlak işletmesi imzası + karşı taraf imzaları).
     * signatureCount: şablondaki toplam imza sayısı (2 = biz + 1 link, 3 = biz + 2 link, vb.)
     * Sadece firma imzası atılıyorsa sign_token (ve gerekiyorsa sign_token_party3) üretilir.
     */
    public function sign($id, $signatureData, $signatureDataParty2 = null, $signatureCount = 2) {
        $id = (int) $id;
        $signatureCount = max(2, min(5, (int) $signatureCount));
        if ($signatureDataParty2 !== null && $signatureDataParty2 !== '') {
            $sql = "UPDATE `{$this->table}` SET `status` = 'signed', `signed_at` = NOW(), `signature_data` = ?, `signature_data_party2` = ? WHERE `id` = ?";
            return $this->db->query($sql, [$signatureData, $signatureDataParty2, $id]);
        }
        $contract = $this->find($id);
        $signToken = null;
        $signTokenParty3 = null;
        if ($contract) {
            if (empty($contract['sign_token']) || $contract['sign_token'] === null) {
                $signToken = bin2hex(random_bytes(32));
            } else {
                $signToken = $contract['sign_token'];
            }
            if ($signatureCount >= 3 && (empty($contract['sign_token_party3']) || $contract['sign_token_party3'] === null)) {
                $signTokenParty3 = bin2hex(random_bytes(32));
            } elseif ($signatureCount >= 3 && $contract) {
                $signTokenParty3 = $contract['sign_token_party3'];
            }
        }
        if ($signToken !== null && $signTokenParty3 !== null) {
            $sql = "UPDATE `{$this->table}` SET `status` = 'signed', `signed_at` = NOW(), `signature_data` = ?, `sign_token` = ?, `sign_token_party3` = ? WHERE `id` = ?";
            return $this->db->query($sql, [$signatureData, $signToken, $signTokenParty3, $id]);
        }
        if ($signToken !== null) {
            $sql = "UPDATE `{$this->table}` SET `status` = 'signed', `signed_at` = NOW(), `signature_data` = ?, `sign_token` = ? WHERE `id` = ?";
            return $this->db->query($sql, [$signatureData, $signToken, $id]);
        }
        $sql = "UPDATE `{$this->table}` SET `status` = 'signed', `signed_at` = NOW(), `signature_data` = ? WHERE `id` = ?";
        return $this->db->query($sql, [$signatureData, $id]);
    }

    /**
     * Token ile sözleşme bul (karşı taraf imza sayfası için). Hangi tarafın (2 veya 3) imza atacağını döndürür.
     * Dönen dizide sign_party_index (2 veya 3) anahtarı eklenir.
     */
    public function findBySignToken($token) {
        $token = trim((string) $token);
        if ($token === '') {
            return null;
        }
        $row = $this->db->fetch("SELECT * FROM `{$this->table}` WHERE `sign_token` = ?", [$token]);
        if ($row) {
            $row['sign_party_index'] = 2;
            return $row;
        }
        $row = $this->db->fetch("SELECT * FROM `{$this->table}` WHERE `sign_token_party3` = ?", [$token]);
        if ($row) {
            $row['sign_party_index'] = 3;
            return $row;
        }
        return null;
    }

    /**
     * 2. taraf (ilk link) imzasını kaydet
     */
    public function signParty2($id, $signatureData) {
        $id = (int) $id;
        $sql = "UPDATE `{$this->table}` SET `signature_data_party2` = ?, `signed_at_party2` = NOW() WHERE `id` = ?";
        return $this->db->query($sql, [$signatureData, $id]);
    }

    /**
     * 3. taraf (ikinci link) imzasını kaydet
     */
    public function signParty3($id, $signatureData) {
        $id = (int) $id;
        $sql = "UPDATE `{$this->table}` SET `signature_data_party3` = ?, `signed_at_party3` = NOW() WHERE `id` = ?";
        return $this->db->query($sql, [$signatureData, $id]);
    }

    /**
     * Sadece form_data kolonunu güncelle (imzalanmış sözleşmede karşı taraf imzasını tüm imza alanlarına yazmak için)
     */
    public function updateFormData($id, $formData) {
        $id = (int) $id;
        $formDataJson = $formData === null ? null : (is_string($formData) ? $formData : json_encode($formData));
        $sql = "UPDATE `{$this->table}` SET `form_data` = ? WHERE `id` = ?";
        return $this->db->query($sql, [$formDataJson, $id]);
    }

    /**
     * Sözleşme sil (kalemler de silinmeli)
     */
    public function delete($id) {
        $id = (int) $id;
        $this->db->query("DELETE FROM `{$this->itemsTable}` WHERE `contract_id` = ?", [$id]);
        $this->db->query("DELETE FROM `{$this->table}` WHERE `id` = ?", [$id]);
        return true;
    }
}
}
