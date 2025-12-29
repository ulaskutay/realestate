<?php
/**
 * Kurulum Adım 1: Veritabanı Bağlantı Bilgileri
 */

// Hata raporlamayı aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Output buffering başlat (header redirect için gerekli)
ob_start();

session_start();

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['db_host'] = trim($_POST['db_host'] ?? 'localhost');
    $_SESSION['db_name'] = trim($_POST['db_name'] ?? '');
    $_SESSION['db_user'] = trim($_POST['db_user'] ?? '');
    $_SESSION['db_password'] = $_POST['db_password'] ?? '';
    
    // Boş alan kontrolü
    if (empty($_SESSION['db_name'])) {
        $error = "Veritabanı adı boş olamaz";
    } elseif (empty($_SESSION['db_user'])) {
        $error = "Kullanıcı adı boş olamaz";
    } else {
        // Veritabanı bağlantısını test et
        try {
            $dsn = "mysql:host={$_SESSION['db_host']};dbname={$_SESSION['db_name']};charset=utf8mb4";
            $pdo = new PDO($dsn, $_SESSION['db_user'], $_SESSION['db_password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            
            // Bağlantı başarılı, session'a kaydet
            $_SESSION['db_connected'] = true;
            
            // Başarılı, bir sonraki adıma geç
            header("Location: step2.php");
            ob_end_flush();
            exit;
        } catch (PDOException $e) {
            $error = "Veritabanı bağlantısı başarısız: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codetic Kurulum - Adım 1</title>
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
            max-width: 640px;
            width: 100%;
            padding: 48px 40px;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }
        .step {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 2;
        }
        .step-number {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #e2e8f0;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-weight: 700;
            font-size: 18px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .step.completed .step-number {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        .step.active .step-number {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }
        .step-label {
            font-size: 13px;
            font-weight: 500;
            color: #94a3b8;
            transition: color 0.3s;
        }
        .step.active .step-label {
            color: #3b82f6;
        }
        .step.completed .step-label {
            color: #10b981;
        }
        .step-line {
            position: absolute;
            top: 24px;
            left: 25%;
            right: 25%;
            height: 3px;
            background: #e2e8f0;
            z-index: 1;
            border-radius: 2px;
        }
        h1 {
            color: #0f172a;
            margin-bottom: 12px;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .subtitle {
            color: #64748b;
            margin-bottom: 32px;
            font-size: 16px;
            line-height: 1.6;
        }
        .form-group {
            margin-bottom: 24px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #0f172a;
            font-weight: 600;
            font-size: 14px;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #f8fafc;
        }
        input[type="text"]:hover,
        input[type="password"]:hover {
            border-color: #cbd5e1;
            background: #fff;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #3b82f6;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .form-help {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 6px;
        }
        .error {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            color: #dc2626;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            border-left: 4px solid #dc2626;
            font-size: 14px;
            font-weight: 500;
        }
        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 32px;
        }
        .btn {
            flex: 1;
            padding: 16px 24px;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            color: #fff;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.5);
        }
        .btn-primary:active {
            transform: translateY(0);
        }
        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
            text-decoration: none;
            border: 2px solid #e2e8f0;
        }
        .btn-secondary:hover {
            background: #e2e8f0;
            border-color: #cbd5e1;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="step-indicator">
            <div class="step-line"></div>
            <div class="step active">
                <div class="step-number">1</div>
                <div class="step-label">Veritabanı</div>
            </div>
            <div class="step inactive">
                <div class="step-number">2</div>
                <div class="step-label">Site Bilgileri</div>
            </div>
            <div class="step inactive">
                <div class="step-number">3</div>
                <div class="step-label">Tamamlandı</div>
            </div>
        </div>

        <h1>Veritabanı Bağlantısı</h1>
        <p class="subtitle">Lütfen veritabanı bağlantı bilgilerinizi girin</p>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="db_host">Veritabanı Sunucusu</label>
                <input type="text" id="db_host" name="db_host" value="<?php echo htmlspecialchars($_SESSION['db_host'] ?? 'localhost'); ?>" required>
                <div class="form-help">Genellikle "localhost" olarak kalabilir</div>
            </div>

            <div class="form-group">
                <label for="db_name">Veritabanı Adı</label>
                <input type="text" id="db_name" name="db_name" value="<?php echo htmlspecialchars($_SESSION['db_name'] ?? ''); ?>" required>
                <div class="form-help">Oluşturduğunuz veritabanının adı</div>
            </div>

            <div class="form-group">
                <label for="db_user">Kullanıcı Adı</label>
                <input type="text" id="db_user" name="db_user" value="<?php echo htmlspecialchars($_SESSION['db_user'] ?? ''); ?>" required>
                <div class="form-help">Veritabanı kullanıcı adı</div>
            </div>

            <div class="form-group">
                <label for="db_password">Şifre</label>
                <input type="password" id="db_password" name="db_password" value="<?php echo htmlspecialchars($_SESSION['db_password'] ?? ''); ?>" required>
                <div class="form-help">Veritabanı şifresi</div>
            </div>

            <div class="btn-group">
                <a href="../install.php" class="btn btn-secondary">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Geri</span>
                </a>
                <button type="submit" class="btn btn-primary">
                    <span>Devam Et</span>
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</body>
</html>
