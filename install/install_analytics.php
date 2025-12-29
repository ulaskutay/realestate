<?php
/**
 * Analytics Tablosu Kurulum Scripti
 * Bu dosyayı tarayıcıdan çalıştırın: /install/install_analytics.php
 */

// Core Database sınıfını yükle
require_once __DIR__ . '/../core/Database.php';

try {
    $db = Database::getInstance();
    
    echo "<h2>Analytics Tablosu Kuruluyor...</h2>";
    
    // Tablo zaten var mı kontrol et
    $check = $db->fetch("SHOW TABLES LIKE 'page_views'");
    if ($check) {
        echo "<p style='color: orange;'>⚠️ page_views tablosu zaten mevcut!</p>";
        
        // Kaydları göster
        $count = $db->fetch("SELECT COUNT(*) as count FROM page_views");
        echo "<p>Mevcut kayıt sayısı: <strong>" . ($count['count'] ?? 0) . "</strong></p>";
    } else {
        // Tabloyu oluştur
        $sql = "CREATE TABLE IF NOT EXISTS `page_views` (
          `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `session_hash` VARCHAR(64) NOT NULL COMMENT 'Anonim session hash (IP + User-Agent hash)',
          `page_url` VARCHAR(500) NOT NULL COMMENT 'Ziyaret edilen sayfa URL',
          `page_title` VARCHAR(255) DEFAULT NULL COMMENT 'Sayfa başlığı',
          `referrer` VARCHAR(500) DEFAULT NULL COMMENT 'Yönlendiren URL',
          `user_agent` TEXT DEFAULT NULL COMMENT 'Tarayıcı bilgisi',
          `device_type` ENUM('desktop', 'mobile', 'tablet') DEFAULT 'desktop' COMMENT 'Cihaz tipi',
          `is_bot` TINYINT(1) DEFAULT 0 COMMENT 'Bot olup olmadığı',
          `visit_duration` INT(11) DEFAULT 0 COMMENT 'Sayfada kalma süresi (saniye)',
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Ziyaret zamanı',
          PRIMARY KEY (`id`),
          KEY `idx_session_hash` (`session_hash`),
          KEY `idx_page_url` (`page_url`(191)),
          KEY `idx_created_at` (`created_at`),
          KEY `idx_device_type` (`device_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db->query($sql);
        echo "<p style='color: green;'>✅ page_views tablosu başarıyla oluşturuldu!</p>";
    }
    
    // Test verisi ekle (opsiyonel)
    echo "<h3>Test Verisi</h3>";
    echo "<p>Analytics sistemi aktif. Şimdi sitenizi ziyaret edin ve veriler otomatik toplanacak.</p>";
    
    echo "<hr>";
    echo "<h3>Sonraki Adımlar:</h3>";
    echo "<ol>";
    echo "<li>Frontend'e gidin ve birkaç sayfa ziyaret edin</li>";
    echo "<li>Admin Dashboard'a gidin</li>";
    echo "<li>'Ziyaretçi İstatistikleri' bölümünü kontrol edin</li>";
    echo "</ol>";
    
    echo "<p><a href='/admin' style='padding: 10px 20px; background: #137fec; color: white; text-decoration: none; border-radius: 5px;'>Admin Panel'e Git</a></p>";
    echo "<p><a href='/' style='padding: 10px 20px; background: #10b981; color: white; text-decoration: none; border-radius: 5px;'>Ana Sayfaya Git</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Hata: " . $e->getMessage() . "</p>";
    echo "<p>Veritabanı bağlantısı kurulamadı. Lütfen veritabanı ayarlarınızı kontrol edin.</p>";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Kurulum</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h2, h3 {
            color: #333;
        }
        p {
            line-height: 1.6;
            color: #666;
        }
        ol {
            line-height: 2;
        }
    </style>
</head>
<body>
</body>
</html>

