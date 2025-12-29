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
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        body::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            top: -250px;
            right: -250px;
            animation: pulse 8s ease-in-out infinite;
        }
        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(147, 51, 234, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            bottom: -200px;
            left: -200px;
            animation: pulse 6s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.2); opacity: 0.8; }
        }
        .install-container {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            max-width: 520px;
            width: 100%;
            padding: 48px 40px;
            text-align: center;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
        }
        .logo {
            width: 72px;
            height: 72px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            box-shadow: 0 8px 16px rgba(59, 130, 246, 0.3);
        }
        h1 {
            color: #0f172a;
            margin-bottom: 12px;
            font-size: 36px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .subtitle {
            color: #64748b;
            margin-bottom: 36px;
            font-size: 16px;
            line-height: 1.6;
            font-weight: 400;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 16px 32px;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            color: #fff;
            text-decoration: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.5);
        }
        .btn:active {
            transform: translateY(0);
        }
        .info {
            margin-top: 36px;
            padding: 24px;
            background: #f8fafc;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            text-align: left;
        }
        .info-title {
            color: #0f172a;
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 16px;
            display: block;
        }
        .info ul {
            list-style: none;
            display: grid;
            gap: 12px;
        }
        .info li {
            color: #475569;
            font-size: 14px;
            padding-left: 28px;
            position: relative;
            line-height: 1.5;
        }
        .info li::before {
            content: '✓';
            position: absolute;
            left: 0;
            top: 0;
            width: 20px;
            height: 20px;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            color: white;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="logo">⚡</div>
        <h1>Codetic Kurulumu</h1>
        <p class="subtitle">Modern içerik yönetim sisteminizi kurmak için hazırsınız</p>
        
        <a href="install/step1.php" class="btn">
            <span>Kuruluma Başla</span>
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
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
