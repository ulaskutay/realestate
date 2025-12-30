<?php
/**
 * Hızlı Veritabanı Kurulumu
 * Sadece veritabanını kurmak için (config varsa)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Config dosyası kontrolü
$configFile = __DIR__ . '/../config/database.php';
if (!file_exists($configFile)) {
    die("❌ Config dosyası bulunamadı! Önce tam kurulumu yapın: /install.php");
}

echo "<h1>🚀 Hızlı Veritabanı Kurulumu</h1>";
echo "<p>Config dosyası mevcut, veritabanı tabloları oluşturuluyor...</p>";

try {
    // Database'e bağlan
    require_once __DIR__ . '/../core/Database.php';
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "<p>✅ Veritabanı bağlantısı başarılı</p>";
    
    // Ana schema dosyası - Tüm tablolar burada
    $schemaFile = __DIR__ . '/complete_schema.sql';
    
    if (!file_exists($schemaFile)) {
        die("❌ Schema dosyası bulunamadı: complete_schema.sql");
    }
    
    echo "<p>📄 Schema dosyası yükleniyor...</p>";
    $sqlContent = file_get_contents($schemaFile);
    echo "<p>📄 Schema dosyası yükleniyor...</p>";
    $sqlContent = file_get_contents($schemaFile);
    
    // SET komutlarını çalıştır
    $connection->exec("SET NAMES utf8mb4");
    $connection->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // SQL'i parçalara ayır
    $queries = array_filter(
        array_map('trim', explode(';', $sqlContent)),
        function($query) {
            $query = trim($query);
            if (empty($query)) return false;
            if (preg_match('/^--/', $query)) return false;
            if (preg_match('/^SET\s+(NAMES|FOREIGN_KEY_CHECKS)/i', $query)) return false;
            return true;
        }
    );
    
    echo "<p>🔧 " . count($queries) . " sorgu çalıştırılıyor...</p>";
    
    $successCount = 0;
    $errorCount = 0;
    $createdTables = [];
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (strlen($query) < 10) continue;
        
        try {
            $connection->exec($query);
            
            // Tablo adını çıkar
            if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`"]?(\w+)[`"]?/i', $query, $matches)) {
                $tableName = $matches[1];
                if (!in_array($tableName, $createdTables)) {
                    $createdTables[] = $tableName;
                    echo "<p>✅ Tablo: <strong>{$tableName}</strong></p>";
                }
            }
            
            $successCount++;
        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            
            // Normal hatalar
            if (strpos($errorMsg, 'already exists') !== false) {
                if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`"]?(\w+)[`"]?/i', $query, $matches)) {
                    if (!in_array($matches[1], $createdTables)) {
                        $createdTables[] = $matches[1];
                    }
                    echo "<p>ℹ️ Zaten mevcut: <strong>{$matches[1]}</strong></p>";
                }
            } elseif (strpos($errorMsg, 'Duplicate') === false &&
                      strpos($errorMsg, 'Cannot add or update') === false) {
                echo "<p>⚠️ Hata: " . htmlspecialchars(substr($errorMsg, 0, 150)) . "</p>";
                $errorCount++;
            }
        }
    }
    
    // FOREIGN_KEY_CHECKS'i geri aç
    $connection->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "<hr>";
    echo "<h2>✅ Kurulum Tamamlandı!</h2>";
    echo "<p><strong>{$successCount}</strong> sorgu çalıştırıldı</p>";
    echo "<p><strong>" . count($createdTables) . "</strong> tablo hazır</p>";
    
    if (count($createdTables) > 0) {
        echo "<details><summary>Hazır Tablolar (" . count($createdTables) . " adet)</summary>";
        echo "<ul>";
        foreach ($createdTables as $table) {
            echo "<li>{$table}</li>";
        }
        echo "</ul></details>";
    }
    
    if ($errorCount > 0) {
        echo "<p style='color: orange;'>⚠️ {$errorCount} hata oluştu (çoğu normal)</p>";
    }
    
    echo "<hr>";
    echo "<p><a href='/public/admin.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Admin Paneline Git →</a></p>";
    echo "<p><a href='/' style='color: #007bff;'>Ana Sayfa →</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Hata:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
h1, h2 { color: #333; }
p { line-height: 1.6; }
ul { list-style: none; padding: 0; }
li { padding: 5px 0; }
</style>";
?>

