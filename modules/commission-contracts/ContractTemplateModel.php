<?php
/**
 * Sözleşme Şablonları Model
 * contract_templates tablosu – header_config, table_config, footer_config (JSON)
 */
if (!class_exists('ContractTemplateModel')) {
class ContractTemplateModel {
    private $db;
    private $table = 'contract_templates';

    public function __construct() {
        $this->db = Database::getInstance();
        $this->ensureSlugColumn();
        $this->ensureTemplateColumns();
    }

    /**
     * Tabloyu oluştur
     */
    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `slug` varchar(100) NOT NULL,
            `header_config` json DEFAULT NULL,
            `table_config` json DEFAULT NULL,
            `footer_config` json DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $this->db->query($sql);
        $this->ensureSlugColumn();
        $this->ensureTemplateColumns();
    }

    /**
     * header_config, table_config, footer_config sütunları yoksa ekle (eski kurulumlar için)
     */
    private function ensureTemplateColumns() {
        $tableExists = $this->db->fetch(
            "SELECT COUNT(*) AS cnt FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?",
            [$this->table]
        );
        if (empty($tableExists) || (int) $tableExists['cnt'] === 0) {
            return;
        }
        $cols = [
            'header_config' => ['def' => 'json DEFAULT NULL', 'after' => 'slug'],
            'table_config'  => ['def' => 'json DEFAULT NULL', 'after' => 'header_config'],
            'footer_config' => ['def' => 'json DEFAULT NULL', 'after' => 'table_config']
        ];
        foreach ($cols as $name => $opts) {
            $has = $this->db->fetch(
                "SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
                [$this->table, $name]
            );
            if (!empty($has) && (int) $has['cnt'] === 0) {
                $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `{$name}` {$opts['def']} AFTER `{$opts['after']}`");
            }
        }
    }

    /**
     * slug sütunu yoksa ekle (eski kurulumlar için). Tablo yoksa hiçbir şey yapma.
     */
    private function ensureSlugColumn() {
        $tableExists = $this->db->fetch(
            "SELECT COUNT(*) AS cnt FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?",
            [$this->table]
        );
        if (empty($tableExists) || (int) $tableExists['cnt'] === 0) {
            return;
        }
        $hasSlug = $this->db->fetch(
            "SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'slug'",
            [$this->table]
        );
        if (!empty($hasSlug) && (int) $hasSlug['cnt'] === 0) {
            $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `slug` varchar(100) NOT NULL DEFAULT '' AFTER `name`");
            $this->db->query("UPDATE `{$this->table}` SET `slug` = CONCAT('template-', `id`) WHERE `slug` = ''");
            $this->db->query("ALTER TABLE `{$this->table}` ADD UNIQUE KEY `slug` (`slug`)");
            return;
        }
        // Sütun var; boş slug'ları benzersiz yap (UNIQUE ihlalini giderir)
        $this->db->query("UPDATE `{$this->table}` SET `slug` = CONCAT('template-', `id`) WHERE `slug` = ''");
    }

    /**
     * Tüm şablonları getir
     */
    public function getAll() {
        return $this->db->fetchAll("SELECT * FROM `{$this->table}` ORDER BY `id` ASC");
    }

    /**
     * Select/dropdown için id ve name
     */
    public function getAllForSelect() {
        return $this->db->fetchAll("SELECT `id`, `name`, `slug` FROM `{$this->table}` ORDER BY `id` ASC");
    }

    /**
     * ID ile bul
     */
    public function find($id) {
        return $this->db->fetch("SELECT * FROM `{$this->table}` WHERE `id` = ?", [(int) $id]);
    }

    /**
     * Slug ile bul (slug sütunu yoksa null döner)
     */
    public function findBySlug($slug) {
        try {
            return $this->db->fetch("SELECT * FROM `{$this->table}` WHERE `slug` = ?", [trim((string) $slug)]);
        } catch (\PDOException $e) {
            if (strpos($e->getMessage(), 'Unknown column \'slug\'') !== false) {
                return null;
            }
            throw $e;
        }
    }

    /**
     * Slug boşsa benzersiz üret (duplicate key hatasını önler)
     */
    private function ensureSlug($slug, $name, $excludeId = null) {
        $slug = trim((string) ($slug ?? ''));
        if ($slug !== '') {
            return $this->slugify($slug);
        }
        $base = $this->slugify(trim($name ?? 'sablon')) ?: 'sablon';
        $slug = $base;
        $suffix = 0;
        while (true) {
            $candidate = $suffix === 0 ? $slug : $slug . '-' . $suffix;
            if ($excludeId !== null && $excludeId !== '') {
                $existing = $this->db->fetch("SELECT 1 FROM `{$this->table}` WHERE `slug` = ? AND `id` != ?", [$candidate, (int) $excludeId]);
            } else {
                $existing = $this->db->fetch("SELECT 1 FROM `{$this->table}` WHERE `slug` = ?", [$candidate]);
            }
            if (empty($existing)) {
                return $candidate;
            }
            $suffix++;
        }
    }
    
    /**
     * Türkçe karakterleri destekleyen slug oluşturucu
     */
    private function slugify($text) {
        // Türkçe karakterleri Latin karşılıklarına dönüştür
        $turkishChars = [
            'ş' => 's', 'Ş' => 's',
            'ı' => 'i', 'İ' => 'i',
            'ğ' => 'g', 'Ğ' => 'g',
            'ü' => 'u', 'Ü' => 'u',
            'ö' => 'o', 'Ö' => 'o',
            'ç' => 'c', 'Ç' => 'c',
            // Ek karakterler
            'ä' => 'a', 'Ä' => 'a',
            'é' => 'e', 'É' => 'e',
            'ë' => 'e', 'Ë' => 'e',
            'ï' => 'i', 'Ï' => 'i',
            'ô' => 'o', 'Ô' => 'o',
            'û' => 'u', 'Û' => 'u',
            'â' => 'a', 'Â' => 'a',
            'î' => 'i', 'Î' => 'i',
            'û' => 'u', 'Û' => 'u',
        ];
        
        $text = str_replace(array_keys($turkishChars), array_values($turkishChars), $text);
        
        // Küçük harfe çevir
        $text = mb_strtolower($text, 'UTF-8');
        
        // Alfanumerik olmayan karakterleri tire ile değiştir
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        
        // Baş ve sondaki tireleri temizle
        $text = trim($text, '-');
        
        // Birden fazla tireyi teke indir
        $text = preg_replace('/-+/', '-', $text);
        
        return $text;
    }

    /**
     * Yeni şablon oluştur
     */
    public function create(array $data) {
        $slug = $this->ensureSlug($data['slug'] ?? '', $data['name'] ?? '', null);
        $sql = "INSERT INTO `{$this->table}` (`name`, `slug`, `header_config`, `table_config`, `footer_config`) VALUES (?, ?, ?, ?, ?)";
        $this->db->query($sql, [
            trim($data['name'] ?? ''),
            $slug,
            isset($data['header_config']) ? json_encode($data['header_config']) : null,
            isset($data['table_config']) ? json_encode($data['table_config']) : null,
            isset($data['footer_config']) ? json_encode($data['footer_config']) : null
        ]);
        return (int) $this->db->getConnection()->lastInsertId();
    }

    /**
     * Şablon güncelle
     */
    public function update($id, array $data) {
        $slug = $this->ensureSlug($data['slug'] ?? '', $data['name'] ?? '', (int) $id);
        $sql = "UPDATE `{$this->table}` SET 
            `name` = ?, `slug` = ?, `header_config` = ?, `table_config` = ?, `footer_config` = ?
            WHERE `id` = ?";
        return $this->db->query($sql, [
            trim($data['name'] ?? ''),
            $slug,
            isset($data['header_config']) ? json_encode($data['header_config']) : null,
            isset($data['table_config']) ? json_encode($data['table_config']) : null,
            isset($data['footer_config']) ? json_encode($data['footer_config']) : null,
            (int) $id
        ]);
    }

    /**
     * Şablon sil
     */
    public function delete($id) {
        return $this->db->query("DELETE FROM `{$this->table}` WHERE `id` = ?", [(int) $id]);
    }
}
}
