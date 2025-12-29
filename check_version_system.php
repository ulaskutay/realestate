<?php
/**
 * Version Sistemi Kontrol Scripti
 * Bu dosyayı tarayıcıda çalıştırarak version sisteminin durumunu kontrol edin
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/core/Database.php';

$db = Database::getInstance();

echo "<h1>Version Sistemi Kontrol</h1>";
echo "<style>body{font-family:sans-serif;padding:20px;} .success{color:green;} .error{color:red;} pre{background:#f5f5f5;padding:10px;}</style>";

// 1. post_versions tablosu var mı?
echo "<h2>1. post_versions Tablosu</h2>";
try {
    $result = $db->query("SHOW TABLES LIKE 'post_versions'");
    if ($result) {
        echo "<p class='success'>✓ post_versions tablosu mevcut</p>";
        
        // Tablo yapısını göster
        $columns = $db->query("DESCRIBE post_versions");
        echo "<pre>";
        print_r($columns);
        echo "</pre>";
    } else {
        echo "<p class='error'>✗ post_versions tablosu YOK!</p>";
        echo "<p>Çözüm: install/migrations/check_post_versions.sql dosyasını çalıştırın</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Hata: " . $e->getMessage() . "</p>";
}

// 2. posts tablosunda version kolonu var mı?
echo "<h2>2. posts.version Kolonu</h2>";
try {
    $result = $db->query("SHOW COLUMNS FROM posts LIKE 'version'");
    if ($result && count($result) > 0) {
        echo "<p class='success'>✓ posts.version kolonu mevcut</p>";
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    } else {
        echo "<p class='error'>✗ posts.version kolonu YOK!</p>";
        echo "<p>Çözüm: Aşağıdaki SQL'i çalıştırın:</p>";
        echo "<pre>ALTER TABLE posts ADD COLUMN version INT(11) NOT NULL DEFAULT 1 AFTER views;</pre>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Hata: " . $e->getMessage() . "</p>";
}

// 3. Örnek bir sayfa var mı ve version değeri nedir?
echo "<h2>3. Örnek Sayfa Kontrol</h2>";
try {
    $page = $db->fetch("SELECT id, title, version FROM posts WHERE type='page' LIMIT 1");
    if ($page) {
        echo "<p class='success'>✓ Örnek sayfa bulundu</p>";
        echo "<pre>";
        print_r($page);
        echo "</pre>";
    } else {
        echo "<p>Henüz sayfa oluşturulmamış</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Hata: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Çözüm Adımları</h2>";
echo "<ol>";
echo "<li>Eğer tablolar yoksa: <code>install/migrations/check_post_versions.sql</code> dosyasını phpMyAdmin'de çalıştırın</li>";
echo "<li>Veya aşağıdaki SQL'leri manuel çalıştırın:</li>";
echo "</ol>";

echo "<h3>SQL Komutları:</h3>";
echo "<pre>";
echo "-- 1. post_versions tablosu oluştur
CREATE TABLE IF NOT EXISTS `post_versions` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `post_id` bigint(20) NOT NULL,
    `version_number` INT(11) NOT NULL DEFAULT 1,
    `title` VARCHAR(500) NOT NULL,
    `slug` VARCHAR(500) NOT NULL,
    `excerpt` TEXT,
    `content` LONGTEXT,
    `featured_image` VARCHAR(500),
    `meta_title` VARCHAR(255),
    `meta_description` TEXT,
    `meta_keywords` VARCHAR(500),
    `created_by` int(11) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_post_id` (`post_id`),
    INDEX `idx_version_number` (`version_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. posts tablosuna version kolonu ekle
ALTER TABLE `posts` ADD COLUMN `version` INT(11) NOT NULL DEFAULT 1 AFTER `views`;
";
echo "</pre>";

echo "<p><a href='admin.php?page=pages'>Admin Panele Dön</a></p>";


