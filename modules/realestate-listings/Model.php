<?php
/**
 * Emlak İlanları Model (standalone modül)
 * Çift yüklemede Fatal önlemek için class_exists ile sarıldı.
 */
if (!class_exists('RealEstateListingsModel')) {
class RealEstateListingsModel {
    private $db;
    private $table = 'realestate_listings';
    
    public function __construct() {
        $this->db = Database::getInstance();
        // Tablo yoksa oluştur (tema modülü ilk kullanıldığında onActivate çağrılmamış olabilir)
        $this->ensureTableExists();
        // Mevcut tablolar için migration'ları çalıştır
        $this->migrateListingStatus();
        $this->migrateLivingRoomsAndRooms();
        $this->migrateRealtorId();
        $this->migrateAdaParsel();
        $this->migrateLatLngAndLocation();
        $this->migrateListingStatusToVarchar();
        $this->ensureListingCategoriesTables();
        $this->syncListingCategoriesFromSettings();
        $this->migrateExistingListingsToCategories();
    }
    
    /**
     * İlanlar tablosunu oluştur
     */
    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `location` varchar(255) DEFAULT NULL,
            `price` decimal(15,2) DEFAULT 0.00,
            `property_type` varchar(50) DEFAULT 'house',
            `listing_status` varchar(50) DEFAULT 'sale',
            `bedrooms` int(11) DEFAULT 0,
            `bathrooms` int(11) DEFAULT 0,
            `living_rooms` int(11) DEFAULT 0,
            `rooms` int(11) DEFAULT 0,
            `area` decimal(10,2) DEFAULT 0.00,
            `area_unit` varchar(10) DEFAULT 'sqft',
            `featured_image` varchar(500) DEFAULT NULL,
            `gallery` text DEFAULT NULL,
            `status` enum('draft','published') DEFAULT 'draft',
            `is_featured` tinyint(1) DEFAULT 0,
            `author_id` int(11) DEFAULT NULL,
            `realtor_id` int(11) DEFAULT NULL,
            `views` int(11) DEFAULT 0,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`),
            KEY `status` (`status`),
            KEY `listing_status` (`listing_status`),
            KEY `is_featured` (`is_featured`),
            KEY `property_type` (`property_type`),
            KEY `author_id` (`author_id`),
            KEY `realtor_id` (`realtor_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->query($sql);
        
        // Mevcut tablolar için migration (constructor'da da çalışıyor ama burada da çalıştırıyoruz)
        $this->migrateListingStatus();
        $this->migrateLivingRoomsAndRooms();
        $this->migrateRealtorId();
        $this->migrateAdaParsel();
    }
    
    /**
     * Tablo yoksa oluştur (ilk kullanımda güvence). Hata olursa fırlatır.
     */
    private function ensureTableExists() {
        try {
            $stmt = $this->db->getConnection()->query("SHOW TABLES LIKE '{$this->table}'");
            if (!$stmt || $stmt->rowCount() === 0) {
                $this->createTable();
            }
        } catch (Exception $e) {
            error_log('RealEstateListingsModel ensureTableExists: ' . $e->getMessage());
            throw new RuntimeException('realestate_listings tablosu oluşturulamadı: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Mevcut tablolara ada ve parsel alanlarını ekle (Sanal Drone entegrasyonu)
     */
    private function migrateAdaParsel() {
        try {
            foreach (['ada' => 'varchar(50)', 'parsel' => 'varchar(50)'] as $col => $type) {
                $checkSql = "SHOW COLUMNS FROM `{$this->table}` LIKE '{$col}'";
                $stmt = $this->db->getConnection()->query($checkSql);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$result) {
                    $after = $col === 'ada' ? 'location' : 'ada';
                    $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `{$col}` {$type} DEFAULT NULL AFTER `{$after}`");
                }
            }
        } catch (Exception $e) {
            error_log('Ada/Parsel migration error: ' . $e->getMessage());
        }
    }

    /**
     * Harita modülü için enlem, boylam ve il/ilçe/mahalle alanlarını ekle
     */
    private function migrateLatLngAndLocation() {
        try {
            $cols = [
                'latitude' => ['type' => 'DECIMAL(10,8) NULL', 'after' => 'location'],
                'longitude' => ['type' => 'DECIMAL(11,8) NULL', 'after' => 'latitude'],
                'city' => ['type' => 'VARCHAR(100) NULL', 'after' => 'longitude'],
                'district' => ['type' => 'VARCHAR(100) NULL', 'after' => 'city'],
                'neighborhood' => ['type' => 'VARCHAR(150) NULL', 'after' => 'district'],
            ];
            foreach ($cols as $col => $def) {
                $checkSql = "SHOW COLUMNS FROM `{$this->table}` LIKE '{$col}'";
                $stmt = $this->db->getConnection()->query($checkSql);
                if ($stmt && $stmt->rowCount() > 0) continue;
                $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `{$col}` {$def['type']} AFTER `{$def['after']}`");
            }
        } catch (Exception $e) {
            error_log('LatLng/Location migration error: ' . $e->getMessage());
        }
    }
    
    /**
     * Mevcut tablolara listing_status alanını ekle
     */
    private function migrateListingStatus() {
        try {
            // Sütunun var olup olmadığını kontrol et
            $checkSql = "SHOW COLUMNS FROM `{$this->table}` LIKE 'listing_status'";
            $stmt = $this->db->getConnection()->query($checkSql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                // Sütun yoksa ekle (varchar ile ekleme yapılabilir)
                $alterSql = "ALTER TABLE `{$this->table}` ADD COLUMN `listing_status` varchar(50) DEFAULT 'sale' AFTER `property_type`";
                $this->db->query($alterSql);
                
                // Index ekle (varsa hata vermemesi için IF NOT EXISTS kullanılamıyor, try-catch ile yakalıyoruz)
                try {
                    $indexSql = "ALTER TABLE `{$this->table}` ADD INDEX `listing_status` (`listing_status`)";
                    $this->db->query($indexSql);
                } catch (Exception $e) {
                    // Index zaten varsa hata vermemesi için
                    error_log('Listing status index already exists or error: ' . $e->getMessage());
                }
            }
        } catch (Exception $e) {
            // Hata durumunda sessizce devam et
            error_log('Listing status migration error: ' . $e->getMessage());
        }
    }

    /**
     * listing_status sütununu enum'dan varchar(50) yap (yeni durumlar eklenebilsin)
     */
    private function migrateListingStatusToVarchar() {
        try {
            $stmt = $this->db->getConnection()->query("SHOW COLUMNS FROM `{$this->table}` LIKE 'listing_status'");
            if (!$stmt || $stmt->rowCount() === 0) return;
            $col = $stmt->fetch(PDO::FETCH_ASSOC);
            $type = $col['Type'] ?? '';
            if (stripos($type, 'enum') !== false) {
                $this->db->query("ALTER TABLE `{$this->table}` MODIFY COLUMN `listing_status` varchar(50) DEFAULT 'sale'");
            }
        } catch (Exception $e) {
            error_log('Listing status varchar migration: ' . $e->getMessage());
        }
    }

    /**
     * realestate_listing_categories pivot tablosunun listing_id ve category_id kolonlarına sahip olup olmadığını kontrol eder
     */
    public function hasValidListingCategoryPivot() {
        try {
            $stmt = $this->db->getConnection()->query("SHOW TABLES LIKE 'realestate_listing_categories'");
            if (!$stmt || $stmt->rowCount() === 0) {
                return false;
            }
            $cols = $this->db->fetchAll("SHOW COLUMNS FROM `realestate_listing_categories`");
            $names = array_column($cols, 'Field');
            return in_array('listing_id', $names, true) && in_array('category_id', $names, true);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * listing_categories ve realestate_listing_categories tablolarını oluştur
     * Pivot tablo varsa ama yanlış yapıdaysa (listing_id/category_id yok) yeniden oluşturulur
     */
    private function ensureListingCategoriesTables() {
        try {
            $stmt = $this->db->getConnection()->query("SHOW TABLES LIKE 'listing_categories'");
            if (!$stmt || $stmt->rowCount() === 0) {
                $this->db->query("CREATE TABLE IF NOT EXISTS `listing_categories` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `slug` varchar(100) NOT NULL,
                    `name` varchar(255) NOT NULL,
                    `kind` varchar(20) NOT NULL DEFAULT 'type',
                    `display_order` int(11) DEFAULT 0,
                    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `slug` (`slug`),
                    KEY `kind` (`kind`),
                    KEY `display_order` (`display_order`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            }
            $stmt = $this->db->getConnection()->query("SHOW TABLES LIKE 'realestate_listing_categories'");
            if (!$stmt || $stmt->rowCount() === 0) {
                $this->createListingCategoryPivotTable();
            } else {
                $cols = $this->db->fetchAll("SHOW COLUMNS FROM `realestate_listing_categories`");
                $names = array_column($cols, 'Field');
                if (!in_array('listing_id', $names, true) || !in_array('category_id', $names, true)) {
                    $this->db->query("DROP TABLE IF EXISTS `realestate_listing_categories`");
                    $this->createListingCategoryPivotTable();
                }
            }
        } catch (Exception $e) {
            error_log('ensureListingCategoriesTables: ' . $e->getMessage());
        }
    }

    private function createListingCategoryPivotTable() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `realestate_listing_categories` (
            `listing_id` int(11) NOT NULL,
            `category_id` int(11) NOT NULL,
            PRIMARY KEY (`listing_id`, `category_id`),
            KEY `category_id` (`category_id`),
            CONSTRAINT `rlc_listing` FOREIGN KEY (`listing_id`) REFERENCES `{$this->table}` (`id`) ON DELETE CASCADE,
            CONSTRAINT `rlc_category` FOREIGN KEY (`category_id`) REFERENCES `listing_categories` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    /**
     * Modül ayarlarındaki property_types ve listing_statuses ile listing_categories senkronize et
     */
    private function syncListingCategoriesFromSettings() {
        $defaultStatuses = ['sale' => 'Satılık', 'rent' => 'Kiralık'];
        $defaultTypes = ['house' => 'Müstakil Ev', 'apartment' => 'Daire', 'villa' => 'Villa', 'commercial' => 'Ticari', 'land' => 'Arsa'];
        $settings = function_exists('get_module_settings') ? get_module_settings('realestate-listings') : [];
        $listingStatuses = $settings['listing_statuses'] ?? $defaultStatuses;
        $propertyTypes = $settings['property_types'] ?? $defaultTypes;
        $this->migrateListingCategorySlugsToTurkish();
        $order = 0;
        foreach ($listingStatuses as $key => $name) {
            if (trim((string) $name) === '') continue;
            $slug = $this->slugFromLabel($name);
            if ($slug === '') $slug = 'cat-' . (++$order);
            $this->db->query(
                "INSERT INTO `listing_categories` (`slug`, `name`, `kind`, `display_order`, `updated_at`) VALUES (?, ?, 'status', ?, NOW()) ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `kind` = 'status', `display_order` = VALUES(`display_order`), `updated_at` = NOW()",
                [$slug, $name, $order]
            );
            $order++;
        }
        foreach ($propertyTypes as $key => $name) {
            if (trim((string) $name) === '') continue;
            $slug = $this->slugFromLabel($name);
            if ($slug === '') $slug = 'type-' . (++$order);
            $this->db->query(
                "INSERT INTO `listing_categories` (`slug`, `name`, `kind`, `display_order`, `updated_at`) VALUES (?, ?, 'type', ?, NOW()) ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `kind` = 'type', `display_order` = VALUES(`display_order`), `updated_at` = NOW()",
                [$slug, $name, $order]
            );
            $order++;
        }
    }

    /**
     * Mevcut kategorilerdeki İngilizce slug'ları (sale, rent, house...) Türkçe slug'a (satilik, kiralik, mustakil-ev...) günceller
     */
    private function migrateListingCategorySlugsToTurkish() {
        try {
            $stmt = $this->db->getConnection()->query("SHOW TABLES LIKE 'listing_categories'");
            if (!$stmt || $stmt->rowCount() === 0) return;
            $rows = $this->db->fetchAll("SELECT id, slug, name FROM listing_categories");
            $englishToSlug = ['sale' => 'satilik', 'rent' => 'kiralik', 'house' => 'mustakil-ev', 'apartment' => 'daire', 'villa' => 'villa', 'commercial' => 'ticari', 'land' => 'arsa'];
            foreach ($rows as $row) {
                $newSlug = $this->slugFromLabel($row['name']);
                if ($newSlug === '') continue;
                $cur = $row['slug'];
                if ($cur === $newSlug) continue;
                $targetSlug = isset($englishToSlug[$cur]) ? $englishToSlug[$cur] : $newSlug;
                $existing = $this->db->fetch("SELECT id FROM listing_categories WHERE slug = ? AND id != ?", [$targetSlug, (int)$row['id']]);
                if ($existing) {
                    $pdo = $this->db->getConnection();
                    $pdo->prepare("UPDATE realestate_listing_categories SET category_id = ? WHERE category_id = ?")->execute([(int)$existing['id'], (int)$row['id']]);
                    $pdo->prepare("DELETE FROM realestate_listing_categories WHERE category_id = ?")->execute([(int)$row['id']]);
                    $pdo->prepare("DELETE FROM listing_categories WHERE id = ?")->execute([(int)$row['id']]);
                    continue;
                }
                $this->db->getConnection()->prepare("UPDATE listing_categories SET slug = ? WHERE id = ?")->execute([$targetSlug, (int)$row['id']]);
            }
        } catch (Exception $e) {
            error_log('migrateListingCategorySlugsToTurkish: ' . $e->getMessage());
        }
    }

    private function slugFromLabel($label) {
        $t = mb_strtolower(trim((string) $label), 'UTF-8');
        $map = ['ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u'];
        $t = strtr($t, $map);
        $t = preg_replace('/[^a-z0-9]+/', '-', $t);
        return trim($t, '-');
    }

    /**
     * Mevcut ilanların property_type ve listing_status değerlerini pivot'a yaz.
     * Kategori slug'ı hem Türkçe (satilik, kiralik) hem İngilizce (sale, rent) ile aranır.
     */
    private function migrateExistingListingsToCategories() {
        try {
            $stmt = $this->db->getConnection()->query("SELECT `id`, `listing_status`, `property_type` FROM `{$this->table}`");
            if (!$stmt) return;
            $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $defaultStatuses = ['sale' => 'Satılık', 'rent' => 'Kiralık'];
            $defaultTypes = ['house' => 'Müstakil Ev', 'apartment' => 'Daire', 'villa' => 'Villa', 'commercial' => 'Ticari', 'land' => 'Arsa'];
            $settings = function_exists('get_module_settings') ? get_module_settings('realestate-listings') : [];
            $statusLabels = $settings['listing_statuses'] ?? $defaultStatuses;
            $typeLabels = $settings['property_types'] ?? $defaultTypes;
            foreach ($listings as $row) {
                $listingId = (int) $row['id'];
                $statusKey = $row['listing_status'] ?? 'sale';
                $typeKey = $row['property_type'] ?? 'house';
                $statusName = $statusLabels[$statusKey] ?? $defaultStatuses[$statusKey] ?? $statusKey;
                $typeName = $typeLabels[$typeKey] ?? $defaultTypes[$typeKey] ?? $typeKey;
                $statusSlugTr = $this->slugFromLabel($statusName);
                $typeSlugTr = $this->slugFromLabel($typeName);
                $statusSlugEn = $this->slugFromLabel($statusKey);
                $typeSlugEn = $this->slugFromLabel($typeKey);
                if ($statusSlugTr !== '' || $statusSlugEn !== '') {
                    $cat = $this->db->fetch("SELECT id FROM listing_categories WHERE slug IN (?, ?) LIMIT 1", [$statusSlugTr, $statusSlugEn]);
                    if ($cat) {
                        $this->db->getConnection()->prepare("INSERT IGNORE INTO realestate_listing_categories (listing_id, category_id) VALUES (?, ?)")->execute([$listingId, (int)$cat['id']]);
                    }
                }
                if (($typeSlugTr !== '' || $typeSlugEn !== '') && $typeSlugTr !== $statusSlugTr && $typeSlugEn !== $statusSlugEn) {
                    $cat = $this->db->fetch("SELECT id FROM listing_categories WHERE slug IN (?, ?) LIMIT 1", [$typeSlugTr, $typeSlugEn]);
                    if ($cat) {
                        $this->db->getConnection()->prepare("INSERT IGNORE INTO realestate_listing_categories (listing_id, category_id) VALUES (?, ?)")->execute([$listingId, (int)$cat['id']]);
                    }
                }
            }
        } catch (Exception $e) {
            error_log('migrateExistingListingsToCategories: ' . $e->getMessage());
        }
    }
    
    /**
     * Mevcut tablolara living_rooms ve rooms alanlarını ekle
     */
    private function migrateLivingRoomsAndRooms() {
        try {
            // living_rooms sütununu kontrol et
            $checkSql = "SHOW COLUMNS FROM `{$this->table}` LIKE 'living_rooms'";
            $stmt = $this->db->getConnection()->query($checkSql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                // living_rooms sütunu yoksa ekle
                $alterSql = "ALTER TABLE `{$this->table}` ADD COLUMN `living_rooms` int(11) DEFAULT 0 AFTER `bathrooms`";
                $this->db->query($alterSql);
            }
            
            // rooms sütununu kontrol et
            $checkSql = "SHOW COLUMNS FROM `{$this->table}` LIKE 'rooms'";
            $stmt = $this->db->getConnection()->query($checkSql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                // rooms sütunu yoksa ekle
                $alterSql = "ALTER TABLE `{$this->table}` ADD COLUMN `rooms` int(11) DEFAULT 0 AFTER `living_rooms`";
                $this->db->query($alterSql);
            }
        } catch (Exception $e) {
            // Hata durumunda sessizce devam et
            error_log('Living rooms and rooms migration error: ' . $e->getMessage());
        }
    }
    
    /**
     * Mevcut tablolara realtor_id alanını ekle
     */
    private function migrateRealtorId() {
        try {
            // realtor_id sütununu kontrol et
            $checkSql = "SHOW COLUMNS FROM `{$this->table}` LIKE 'realtor_id'";
            $stmt = $this->db->getConnection()->query($checkSql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                // realtor_id sütunu yoksa ekle
                $alterSql = "ALTER TABLE `{$this->table}` ADD COLUMN `realtor_id` int(11) DEFAULT NULL AFTER `author_id`";
                $this->db->query($alterSql);
                
                // Index ekle
                try {
                    $indexSql = "ALTER TABLE `{$this->table}` ADD INDEX `realtor_id` (`realtor_id`)";
                    $this->db->query($indexSql);
                } catch (Exception $e) {
                    // Index zaten varsa hata vermemesi için
                    error_log('Realtor ID index already exists or error: ' . $e->getMessage());
                }
            }
        } catch (Exception $e) {
            // Hata durumunda sessizce devam et
            error_log('Realtor ID migration error: ' . $e->getMessage());
        }
    }
    
    /**
     * Tüm ilanları getir
     */
    public function getAll($orderBy = 'created_at DESC') {
        $sql = "SELECT l.*, u.username as author_name, 
                r.id as realtor_id, r.first_name as realtor_first_name, r.last_name as realtor_last_name, 
                r.email as realtor_email, r.phone as realtor_phone, r.photo as realtor_photo, r.bio as realtor_bio,
                r.slug as realtor_slug
                FROM `{$this->table}` l
                LEFT JOIN `users` u ON l.author_id = u.id
                LEFT JOIN `realestate_agents` r ON l.realtor_id = r.id
                ORDER BY {$orderBy}";
        return $this->db->fetchAll($sql);
    }

    /**
     * Panel için tüm ilanları filtrelerle getir (arama + durum + tip + konum + fiyat)
     * @param array $filters ['search'=>'', 'status'=>'', 'property_type'=>'', 'listing_status'=>'', 'city'=>'', 'district'=>'', 'price_min'=>'', 'price_max'=>'', 'order'=>'created_at DESC']
     */
    public function getAllForAdmin(array $filters = []) {
        $sql = "SELECT l.*, u.username as author_name, 
                r.id as realtor_id, r.first_name as realtor_first_name, r.last_name as realtor_last_name, 
                r.email as realtor_email, r.phone as realtor_phone, r.photo as realtor_photo, r.bio as realtor_bio,
                r.slug as realtor_slug
                FROM `{$this->table}` l
                LEFT JOIN `users` u ON l.author_id = u.id
                LEFT JOIN `realestate_agents` r ON l.realtor_id = r.id
                WHERE 1=1";
        $params = [];

        $search = isset($filters['search']) ? trim($filters['search']) : '';
        if ($search !== '') {
            $sql .= " AND (l.`title` LIKE ? OR l.`description` LIKE ? OR l.`location` LIKE ? OR l.`city` LIKE ? OR l.`district` LIKE ? OR l.`neighborhood` LIKE ?)";
            $p = '%' . $search . '%';
            $params = array_merge($params, [$p, $p, $p, $p, $p, $p]);
        }

        $status = isset($filters['status']) ? trim($filters['status']) : '';
        if ($status !== '' && in_array($status, ['draft', 'published'], true)) {
            $sql .= " AND l.`status` = ?";
            $params[] = $status;
        }

        $propertyType = isset($filters['property_type']) ? trim($filters['property_type']) : '';
        if ($propertyType !== '') {
            $sql .= " AND l.`property_type` = ?";
            $params[] = $propertyType;
        }

        $listingStatus = isset($filters['listing_status']) ? trim($filters['listing_status']) : '';
        if ($listingStatus !== '') {
            $sql .= " AND l.`listing_status` = ?";
            $params[] = $listingStatus;
        }

        $city = isset($filters['city']) ? trim($filters['city']) : '';
        if ($city !== '') {
            $sql .= " AND (l.`city` = ? OR l.`location` LIKE ?)";
            $params[] = $city;
            $params[] = '%' . $city . '%';
        }

        $district = isset($filters['district']) ? trim($filters['district']) : '';
        if ($district !== '') {
            $sql .= " AND (l.`district` = ? OR l.`location` LIKE ?)";
            $params[] = $district;
            $params[] = '%' . $district . '%';
        }

        if (isset($filters['price_min']) && $filters['price_min'] !== '' && is_numeric($filters['price_min'])) {
            $sql .= " AND l.`price` >= ?";
            $params[] = (float) $filters['price_min'];
        }
        if (isset($filters['price_max']) && $filters['price_max'] !== '' && is_numeric($filters['price_max'])) {
            $sql .= " AND l.`price` <= ?";
            $params[] = (float) $filters['price_max'];
        }

        $order = isset($filters['order']) ? trim($filters['order']) : 'created_at DESC';
        $orderMap = [
            'created_at DESC' => 'l.`created_at` DESC',
            'created_at ASC'  => 'l.`created_at` ASC',
            'price ASC'       => 'l.`price` ASC',
            'price DESC'      => 'l.`price` DESC',
            'title ASC'       => 'l.`title` ASC',
            'title DESC'      => 'l.`title` DESC',
            'updated_at DESC' => 'l.`updated_at` DESC',
        ];
        $orderClause = $orderMap[$order] ?? 'l.`created_at` DESC';
        $sql .= " ORDER BY " . $orderClause;

        if (!empty($params)) {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Yayınlanmış ilanları filtrelerle getir
     * @param string $orderBy Sıralama: newest, oldest, price_asc, price_desc, title_asc, title_desc
     * @param string $search Başlık/açıklama arama
     * @param string $city İl adı
     * @param string $district İlçe adı
     * @param int $minRooms En az oda
     * @param int $minBathrooms En az banyo
     * @param int $minBedrooms En az yatak odası
     * @param float $areaMin Min m²
     * @param float $areaMax Max m²
     */
    public function getPublished($location = '', $type = '', $priceRange = '', $limit = null, $offset = 0, $listingStatus = '', $realtorId = null, $orderBy = '', $search = '', $city = '', $district = '', $minRooms = 0, $minBathrooms = 0, $minBedrooms = 0, $areaMin = 0, $areaMax = 0, $categorySlug = '') {
        $sql = "SELECT DISTINCT l.*, 
                r.id as realtor_id, r.first_name as realtor_first_name, r.last_name as realtor_last_name, 
                r.email as realtor_email, r.phone as realtor_phone, r.photo as realtor_photo, r.bio as realtor_bio,
                r.slug as realtor_slug
                FROM `{$this->table}` l
                LEFT JOIN `realestate_agents` r ON l.realtor_id = r.id";
        $useCategoryFilter = ($categorySlug !== '' && $this->hasValidListingCategoryPivot());
        if ($useCategoryFilter) {
            $sql .= " INNER JOIN `realestate_listing_categories` rlc ON l.`id` = rlc.`listing_id`
                INNER JOIN `listing_categories` lc ON rlc.`category_id` = lc.`id` AND lc.`slug` = ?";
        }
        $sql .= " WHERE l.`status` = 'published'";
        $params = [];
        if ($useCategoryFilter) {
            $params[] = $categorySlug;
        }
        
        if (!empty($location)) {
            $sql .= " AND (l.`location` LIKE ? OR l.`neighborhood` LIKE ? OR l.`district` LIKE ?)";
            $p = "%{$location}%";
            $params[] = $p;
            $params[] = $p;
            $params[] = $p;
        }
        
        if (!empty($type)) {
            $sql .= " AND l.`property_type` = ?";
            $params[] = $type;
        }
        
        if (!empty($listingStatus)) {
            $sql .= " AND l.`listing_status` = ?";
            $params[] = $listingStatus;
        }
        
        if ($realtorId !== null && $realtorId > 0) {
            $sql .= " AND l.`realtor_id` = ?";
            $params[] = $realtorId;
        }
        
        if (!empty($priceRange)) {
            if (strpos($priceRange, '-') !== false) {
                list($min, $max) = explode('-', $priceRange);
                $sql .= " AND l.`price` >= ? AND l.`price` <= ?";
                $params[] = floatval($min);
                $params[] = floatval($max);
            } else {
                $sql .= " AND l.`price` >= ?";
                $params[] = floatval(str_replace('+', '', $priceRange));
            }
        }
        
        if (!empty($search)) {
            $sql .= " AND (l.`title` LIKE ? OR l.`description` LIKE ?)";
            $p = "%{$search}%";
            $params[] = $p;
            $params[] = $p;
        }
        
        if (!empty($city)) {
            $sql .= " AND l.`city` = ?";
            $params[] = $city;
        }
        
        if (!empty($district)) {
            $sql .= " AND l.`district` = ?";
            $params[] = $district;
        }
        
        if ($minRooms > 0) {
            $sql .= " AND (l.`rooms` >= ? OR (l.`rooms` = 0 AND (COALESCE(l.`bedrooms`,0) + COALESCE(l.`living_rooms`,0)) >= ?))";
            $params[] = $minRooms;
            $params[] = $minRooms;
        }
        
        if ($minBathrooms > 0) {
            $sql .= " AND l.`bathrooms` >= ?";
            $params[] = $minBathrooms;
        }
        
        if ($minBedrooms > 0) {
            $sql .= " AND l.`bedrooms` >= ?";
            $params[] = $minBedrooms;
        }
        
        if ($areaMin > 0) {
            $sql .= " AND l.`area` >= ?";
            $params[] = floatval($areaMin);
        }
        
        if ($areaMax > 0) {
            $sql .= " AND l.`area` <= ?";
            $params[] = floatval($areaMax);
        }
        
        $orderBy = trim((string) $orderBy);
        switch ($orderBy) {
            case 'oldest':
                $sql .= " ORDER BY l.`is_featured` DESC, l.`created_at` ASC";
                break;
            case 'price_asc':
                $sql .= " ORDER BY l.`is_featured` DESC, l.`price` ASC, l.`created_at` DESC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY l.`is_featured` DESC, l.`price` DESC, l.`created_at` DESC";
                break;
            case 'title_asc':
                $sql .= " ORDER BY l.`is_featured` DESC, l.`title` ASC, l.`created_at` DESC";
                break;
            case 'title_desc':
                $sql .= " ORDER BY l.`is_featured` DESC, l.`title` DESC, l.`created_at` DESC";
                break;
            case 'newest':
            default:
                $sql .= " ORDER BY l.`is_featured` DESC, l.`created_at` DESC";
                break;
        }
        
        if ($limit) {
            $sql .= " LIMIT " . (int) $offset . ", " . (int) $limit;
        }
        
        if (!empty($params)) {
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Öne çıkan ilanları getir
     */
    public function getFeatured($limit = 6) {
        $sql = "SELECT l.*, 
                r.id as realtor_id, r.first_name as realtor_first_name, r.last_name as realtor_last_name, 
                r.email as realtor_email, r.phone as realtor_phone, r.photo as realtor_photo, r.bio as realtor_bio,
                r.slug as realtor_slug
                FROM `{$this->table}` l
                LEFT JOIN `realestate_agents` r ON l.realtor_id = r.id
                WHERE l.`status` = 'published' AND l.`is_featured` = 1 
                ORDER BY l.`created_at` DESC 
                LIMIT {$limit}";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Yayınlanmış ilanlarda geçen benzersiz il listesini getirir (filtre dropdown için).
     * @return array [il_adı => il_adı] (view ile uyum için key = value)
     */
    public function getDistinctCitiesFromListings() {
        $sql = "SELECT DISTINCT `city` FROM `{$this->table}` 
                WHERE `status` = 'published' AND `city` IS NOT NULL AND TRIM(`city`) != '' 
                ORDER BY `city` ASC";
        $rows = $this->db->fetchAll($sql);
        $out = [];
        foreach ($rows as $row) {
            $name = trim($row['city']);
            if ($name !== '') {
                $out[$name] = $name;
            }
        }
        return $out;
    }
    
    /**
     * Yayınlanmış ilanlarda geçen ilçeleri il adına göre gruplar (filtre dropdown için).
     * @return array [il_adı => [ilçe1, ilçe2, ...]]
     */
    public function getDistrictsByCityFromListings() {
        $sql = "SELECT DISTINCT `city`, `district` FROM `{$this->table}` 
                WHERE `status` = 'published' 
                AND `city` IS NOT NULL AND TRIM(`city`) != '' 
                AND `district` IS NOT NULL AND TRIM(`district`) != '' 
                ORDER BY `city` ASC, `district` ASC";
        $rows = $this->db->fetchAll($sql);
        $out = [];
        foreach ($rows as $row) {
            $city = trim($row['city']);
            $district = trim($row['district']);
            if ($city !== '' && $district !== '') {
                if (!isset($out[$city])) {
                    $out[$city] = [];
                }
                if (!in_array($district, $out[$city], true)) {
                    $out[$city][] = $district;
                }
            }
        }
        return $out;
    }
    
    /**
     * ID'ye göre ilan bul
     */
    public function find($id) {
        $sql = "SELECT l.*, u.username as author_name,
                r.id as realtor_id, r.first_name as realtor_first_name, r.last_name as realtor_last_name, 
                r.email as realtor_email, r.phone as realtor_phone, r.photo as realtor_photo, r.bio as realtor_bio,
                r.slug as realtor_slug
                FROM `{$this->table}` l
                LEFT JOIN `users` u ON l.author_id = u.id
                LEFT JOIN `realestate_agents` r ON l.realtor_id = r.id
                WHERE l.id = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Slug'a göre ilan bul
     */
    public function findBySlug($slug) {
        $sql = "SELECT l.*, 
                r.id as realtor_id, r.first_name as realtor_first_name, r.last_name as realtor_last_name, 
                r.email as realtor_email, r.phone as realtor_phone, r.photo as realtor_photo, r.bio as realtor_bio,
                r.slug as realtor_slug
                FROM `{$this->table}` l
                LEFT JOIN `realestate_agents` r ON l.realtor_id = r.id
                WHERE l.`slug` = ? AND l.`status` = 'published'";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Yeni ilan oluştur
     */
    public function create($data) {
        $sql = "INSERT INTO `{$this->table}` 
                (`title`, `slug`, `description`, `location`, `latitude`, `longitude`, `city`, `district`, `neighborhood`, `ada`, `parsel`, `price`, `property_type`, `listing_status`, `bedrooms`, `bathrooms`, `living_rooms`, `rooms`, `area`, `area_unit`, `featured_image`, `gallery`, `status`, `is_featured`, `author_id`, `realtor_id`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $result = $stmt->execute([
            $data['title'],
            $data['slug'],
            $data['description'] ?? null,
            $data['location'] ?? null,
            isset($data['latitude']) && $data['latitude'] !== '' ? (float) $data['latitude'] : null,
            isset($data['longitude']) && $data['longitude'] !== '' ? (float) $data['longitude'] : null,
            $data['city'] ?? null,
            $data['district'] ?? null,
            $data['neighborhood'] ?? null,
            $data['ada'] ?? null,
            $data['parsel'] ?? null,
            $data['price'] ?? 0,
            $data['property_type'] ?? 'house',
            $data['listing_status'] ?? 'sale',
            $data['bedrooms'] ?? 0,
            $data['bathrooms'] ?? 0,
            $data['living_rooms'] ?? 0,
            $data['rooms'] ?? 0,
            $data['area'] ?? 0,
            $data['area_unit'] ?? 'sqft',
            $data['featured_image'] ?? null,
            $data['gallery'] ?? '[]',
            $data['status'] ?? 'draft',
            $data['is_featured'] ?? 0,
            $data['author_id'] ?? null,
            $data['realtor_id'] ?? null
        ]);
        
        return $result ? $this->db->getConnection()->lastInsertId() : false;
    }
    
    /**
     * İlan güncelle
     */
    public function update($id, $data) {
        $sql = "UPDATE `{$this->table}` SET 
                `title` = ?, `slug` = ?, `description` = ?, `location` = ?, `latitude` = ?, `longitude` = ?, `city` = ?, `district` = ?, `neighborhood` = ?, `ada` = ?, `parsel` = ?, `price` = ?, 
                `property_type` = ?, `listing_status` = ?, `bedrooms` = ?, `bathrooms` = ?, `living_rooms` = ?, `rooms` = ?, `area` = ?, `area_unit` = ?, 
                `featured_image` = ?, `gallery` = ?, `status` = ?, `is_featured` = ?, `realtor_id` = ?
                WHERE `id` = ?";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $data['slug'],
            $data['description'] ?? null,
            $data['location'] ?? null,
            isset($data['latitude']) && $data['latitude'] !== '' ? (float) $data['latitude'] : null,
            isset($data['longitude']) && $data['longitude'] !== '' ? (float) $data['longitude'] : null,
            $data['city'] ?? null,
            $data['district'] ?? null,
            $data['neighborhood'] ?? null,
            $data['ada'] ?? null,
            $data['parsel'] ?? null,
            $data['price'] ?? 0,
            $data['property_type'] ?? 'house',
            $data['listing_status'] ?? 'sale',
            $data['bedrooms'] ?? 0,
            $data['bathrooms'] ?? 0,
            $data['living_rooms'] ?? 0,
            $data['rooms'] ?? 0,
            $data['area'] ?? 0,
            $data['area_unit'] ?? 'sqft',
            $data['featured_image'] ?? null,
            $data['gallery'] ?? '[]',
            $data['status'] ?? 'draft',
            $data['is_featured'] ?? 0,
            $data['realtor_id'] ?? null,
            $id
        ]);
    }
    
    /**
     * İlan sil
     */
    public function delete($id) {
        $sql = "DELETE FROM `{$this->table}` WHERE `id` = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Görüntülenme sayısını artır
     */
    public function incrementViews($id) {
        $sql = "UPDATE `{$this->table}` SET `views` = `views` + 1 WHERE `id` = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Emlakçıya göre ilanları getir
     */
    public function getByRealtor($realtorId, $limit = null, $offset = 0) {
        $sql = "SELECT l.*, 
                r.id as realtor_id, r.first_name as realtor_first_name, r.last_name as realtor_last_name, 
                r.email as realtor_email, r.phone as realtor_phone, r.photo as realtor_photo, r.bio as realtor_bio,
                r.slug as realtor_slug
                FROM `{$this->table}` l
                LEFT JOIN `realestate_agents` r ON l.realtor_id = r.id
                WHERE l.`realtor_id` = ? AND l.`status` = 'published'
                ORDER BY l.`is_featured` DESC, l.`created_at` DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$offset}, {$limit}";
        }
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$realtorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Emlakçının toplam ilan sayısını getir
     */
    public function countByRealtor($realtorId) {
        $sql = "SELECT COUNT(*) as total FROM `{$this->table}` WHERE `realtor_id` = ? AND `status` = 'published'";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$realtorId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    // ==================== LİSTİNG CATEGORİES ====================

    /**
     * Tüm ilan kategorilerini getir
     */
    public function getAllCategories() {
        try {
            return $this->db->fetchAll("SELECT * FROM `listing_categories` ORDER BY `kind` ASC, `display_order` ASC, `name` ASC");
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Slug'a göre kategori getir
     */
    public function getCategoryBySlug($slug) {
        try {
            $stmt = $this->db->getConnection()->prepare("SELECT * FROM `listing_categories` WHERE `slug` = ?");
            $stmt->execute([$slug]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Bir ilana bağlı kategori ID'lerini getir
     */
    public function getCategoriesForListing($listingId) {
        try {
            $stmt = $this->db->getConnection()->prepare(
                "SELECT lc.* FROM `listing_categories` lc 
                 INNER JOIN `realestate_listing_categories` rlc ON lc.`id` = rlc.`category_id` 
                 WHERE rlc.`listing_id` = ? ORDER BY lc.`kind` ASC, lc.`display_order` ASC, lc.`name` ASC"
            );
            $stmt->execute([(int) $listingId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * İlanın kategorilerini güncelle (pivot'u silip yeniden yazar)
     */
    public function setListingCategories($listingId, array $categoryIds) {
        $listingId = (int) $listingId;
        try {
            $this->db->getConnection()->prepare("DELETE FROM `realestate_listing_categories` WHERE `listing_id` = ?")->execute([$listingId]);
            if (empty($categoryIds)) return true;
            $stmt = $this->db->getConnection()->prepare("INSERT IGNORE INTO `realestate_listing_categories` (`listing_id`, `category_id`) VALUES (?, ?)");
            foreach ($categoryIds as $cid) {
                $stmt->execute([$listingId, (int) $cid]);
            }
            return true;
        } catch (Exception $e) {
            error_log('setListingCategories: ' . $e->getMessage());
            return false;
        }
    }
}
}
