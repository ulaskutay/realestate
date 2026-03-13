<?php
/**
 * role_modules tablosunu oluşturur ve Admin (role_id=2) için çekirdek modülleri ekler.
 * Mevcut kurulumlarda bir kez çalıştırın: tarayıcıdan /install/migrate_role_modules.php veya CLI.
 */

require_once __DIR__ . '/../core/Database.php';

$db = Database::getInstance();

// Tablo var mı kontrol et
$exists = $db->fetch("SHOW TABLES LIKE 'role_modules'");
if ($exists) {
    echo "role_modules tablosu zaten mevcut.\n";
    exit(0);
}

$db->query("
CREATE TABLE IF NOT EXISTS `role_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `module_slug` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_module_slug` (`role_id`, `module_slug`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `role_modules_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$core = ['posts', 'pages', 'agreements', 'forms', 'media', 'sliders', 'menus', 'users', 'themes', 'settings', 'modules', 'smtp', 'roles'];
foreach ($core as $slug) {
    try {
        $db->query("INSERT IGNORE INTO role_modules (role_id, module_slug) VALUES (2, ?)", [$slug]);
    } catch (Exception $e) {
        // ignore duplicate
    }
}

echo "role_modules tablosu oluşturuldu ve Admin rolüne çekirdek modüller atandı.\n";
