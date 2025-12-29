<?php
/**
 * Analytics URL Normalizasyon Script'i
 * Mevcut veritabanındaki URL'leri normalize eder ve duplikaları birleştirir
 */

require_once __DIR__ . '/../core/Database.php';

// HTML başlat
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics URL Normalizasyonu</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #dc3545;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #17a2b8;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border: 2px solid #e9ecef;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #4CAF50;
        }
        .stat-label {
            color: #6c757d;
            font-size: 14px;
            margin-top: 5px;
        }
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .example {
            background: #fff3cd;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Analytics URL Normalizasyonu</h1>
        <p>Bu script, <code>page_views</code> tablosundaki URL'leri normalize eder ve duplikaları birleştirir.</p>
        
        <div class="info">
            <strong>Ne yapılacak?</strong><br>
            • Trailing slash'leri kaldırılacak (<code>/contact/</code> → <code>/contact</code>)<br>
            • www. prefix'leri kaldırılacak (<code>www.example.com</code> → <code>example.com</code>)<br>
            • Protocol lowercase yapılacak (<code>HTTPS://</code> → <code>https://</code>)<br>
            • Query parametreleri alfabetik sıralanacak<br>
        </div>
        
        <?php
        try {
            $db = Database::getInstance();
            
            // İstatistikler
            echo "<h2>📊 Mevcut Durum</h2>";
            
            $totalRecords = $db->fetch("SELECT COUNT(*) as count FROM page_views");
            $uniqueUrls = $db->fetch("SELECT COUNT(DISTINCT page_url) as count FROM page_views");
            
            echo "<div class='stats'>";
            echo "<div class='stat-card'>";
            echo "<div class='stat-number'>" . number_format($totalRecords['count']) . "</div>";
            echo "<div class='stat-label'>Toplam Kayıt</div>";
            echo "</div>";
            
            echo "<div class='stat-card'>";
            echo "<div class='stat-number'>" . number_format($uniqueUrls['count']) . "</div>";
            echo "<div class='stat-label'>Benzersiz URL</div>";
            echo "</div>";
            echo "</div>";
            
            // Örnek URL'leri göster
            echo "<h3>🔍 Örnek URL'ler (Normalizasyon Öncesi)</h3>";
            $sampleUrls = $db->fetchAll("SELECT DISTINCT page_url FROM page_views LIMIT 10");
            foreach ($sampleUrls as $url) {
                echo "<div class='example'>➜ " . htmlspecialchars($url['page_url']) . "</div>";
            }
            
            // Normalizasyon başlat
            echo "<h2>⚙️ Normalizasyon İşlemi</h2>";
            
            function normalizeUrl($url) {
                if (empty($url)) return null;
                
                $parsed = parse_url($url);
                if (!$parsed || !isset($parsed['host'])) {
                    return $url;
                }
                
                $normalizedUrl = '';
                
                if (isset($parsed['scheme'])) {
                    $normalizedUrl .= strtolower($parsed['scheme']) . '://';
                }
                
                if (isset($parsed['host'])) {
                    $host = strtolower($parsed['host']);
                    $host = preg_replace('/^www\./', '', $host);
                    $normalizedUrl .= $host;
                }
                
                if (isset($parsed['port'])) {
                    $scheme = $parsed['scheme'] ?? 'http';
                    $defaultPort = ($scheme === 'https') ? 443 : 80;
                    if ($parsed['port'] != $defaultPort) {
                        $normalizedUrl .= ':' . $parsed['port'];
                    }
                }
                
                $path = $parsed['path'] ?? '/';
                $path = rtrim($path, '/');
                if (empty($path)) $path = '/';
                $normalizedUrl .= $path;
                
                if (isset($parsed['query'])) {
                    parse_str($parsed['query'], $queryParams);
                    ksort($queryParams);
                    $normalizedUrl .= '?' . http_build_query($queryParams);
                }
                
                return substr($normalizedUrl, 0, 500);
            }
            
            // Tüm URL'leri al ve normalize et
            $allUrls = $db->fetchAll("SELECT DISTINCT page_url FROM page_views WHERE page_url IS NOT NULL AND page_url != ''");
            
            $updates = 0;
            $errors = 0;
            
            foreach ($allUrls as $urlRow) {
                $originalUrl = $urlRow['page_url'];
                $normalizedUrl = normalizeUrl($originalUrl);
                
                if ($originalUrl !== $normalizedUrl) {
                    try {
                        // URL'i güncelle
                        $stmt = $db->query(
                            "UPDATE page_views SET page_url = ? WHERE page_url = ?",
                            [$normalizedUrl, $originalUrl]
                        );
                        $updates++;
                        
                        echo "<div class='example'>";
                        echo "✅ " . htmlspecialchars($originalUrl) . "<br>";
                        echo "➜ " . htmlspecialchars($normalizedUrl);
                        echo "</div>";
                    } catch (Exception $e) {
                        $errors++;
                        echo "<div class='error'>❌ Hata: " . htmlspecialchars($originalUrl) . " - " . $e->getMessage() . "</div>";
                    }
                }
            }
            
            echo "<div class='success'>";
            echo "<strong>✅ Normalizasyon Tamamlandı!</strong><br>";
            echo "• <strong>{$updates}</strong> URL güncellendi<br>";
            if ($errors > 0) {
                echo "• <strong>{$errors}</strong> hata oluştu<br>";
            }
            echo "</div>";
            
            // Yeni istatistikler
            echo "<h2>📊 Yeni Durum</h2>";
            
            $newTotalRecords = $db->fetch("SELECT COUNT(*) as count FROM page_views");
            $newUniqueUrls = $db->fetch("SELECT COUNT(DISTINCT page_url) as count FROM page_views");
            
            echo "<div class='stats'>";
            echo "<div class='stat-card'>";
            echo "<div class='stat-number'>" . number_format($newTotalRecords['count']) . "</div>";
            echo "<div class='stat-label'>Toplam Kayıt</div>";
            echo "</div>";
            
            echo "<div class='stat-card'>";
            echo "<div class='stat-number'>" . number_format($newUniqueUrls['count']) . "</div>";
            echo "<div class='stat-label'>Benzersiz URL</div>";
            echo "</div>";
            
            $saved = $uniqueUrls['count'] - $newUniqueUrls['count'];
            echo "<div class='stat-card'>";
            echo "<div class='stat-number' style='color: #dc3545;'>" . number_format($saved) . "</div>";
            echo "<div class='stat-label'>Duplikat Temizlendi</div>";
            echo "</div>";
            echo "</div>";
            
            echo "<div class='info'>";
            echo "<strong>✨ İşlem Başarılı!</strong><br>";
            echo "Artık aynı sayfa farklı URL varyasyonlarıyla kaydedilmeyecek.<br>";
            echo "<a href='../admin/dashboard' style='color: #007bff;'>← Dashboard'a Dön</a>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='error'>";
            echo "<strong>❌ Hata:</strong> " . htmlspecialchars($e->getMessage());
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>

