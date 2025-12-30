<?php
/**
 * Codetic CMS Kurulum Sistemi
 * install.php - Ana kurulum giriş sayfası
 */

// Eğer kurulum zaten yapılmışsa ana sayfaya yönlendir
$configFile = __DIR__ . '/config/database.php';
if (file_exists($configFile)) {
    // Hataları yakalayıp kuruluma devam et - Database sınıfını kullanmadan direkt PDO ile
    try {
        // Config dosyasını yükle
        $config = require $configFile;
        
        // Direkt PDO ile bağlantı kur (Database sınıfını kullanmadan)
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT, // Hataları sessizce handle et
        ]);
        
        // Tabloları kontrol et (nazikçe - SHOW TABLES kullan)
        $result = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($result && $result->rowCount() > 0) {
            // Tablo var, kurulum tamamlanmış, ana sayfaya yönlendir (kök dizine)
            header("Location: /");
            exit;
        }
        // Tablo yok, kuruluma devam et
    } catch (Throwable $e) {
        // Herhangi bir hata, kuruluma devam et (hata gösterme)
    }
}

// Kurulum başlangıç sayfası
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codetic Kurulum</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .install-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            max-width: 480px;
            width: 100%;
            padding: 48px 40px;
            text-align: center;
        }
        .logo {
            width: 56px;
            height: 56px;
            margin: 0 auto 24px;
            background: #2563eb;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #fff;
        }
        h1 {
            color: #1a1a1a;
            margin-bottom: 8px;
            font-size: 28px;
            font-weight: 600;
        }
        .subtitle {
            color: #666;
            margin-bottom: 32px;
            font-size: 15px;
            line-height: 1.5;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: #2563eb;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 500;
            transition: background 0.2s;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: #1d4ed8;
        }
        .info {
            margin-top: 32px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 6px;
            text-align: left;
        }
        .info-title {
            color: #1a1a1a;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 12px;
            display: block;
        }
        .info ul {
            list-style: none;
            display: grid;
            gap: 8px;
        }
        .info li {
            color: #555;
            font-size: 14px;
            padding-left: 20px;
            position: relative;
            line-height: 1.5;
        }
        .info li::before {
            content: '•';
            position: absolute;
            left: 0;
            color: #2563eb;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="logo">C</div>
        <h1>Kurulum</h1>
        <p class="subtitle">Sistemi kurmak için başlayın</p>
        
        <a href="install/step1.php" class="btn">
            <span>Başla</span>
            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
        
        <div class="info">
            <span class="info-title">Kurulum için gerekli bilgiler</span>
            <ul>
                <li>Veritabanı adı</li>
                <li>Veritabanı kullanıcı adı</li>
                <li>Veritabanı şifresi</li>
                <li>Site adı</li>
                <li>Admin kullanıcı bilgileri</li>
            </ul>
        </div>
    </div>
</body>
</html>
