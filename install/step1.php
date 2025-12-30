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
            max-width: 560px;
            width: 100%;
            padding: 40px;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 32px;
            position: relative;
        }
        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e5e5e5;
            color: #999;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-weight: 500;
            font-size: 14px;
        }
        .step.completed .step-number {
            background: #22c55e;
            color: white;
        }
        .step.active .step-number {
            background: #2563eb;
            color: white;
        }
        .step-label {
            font-size: 12px;
            color: #999;
        }
        .step.active .step-label {
            color: #2563eb;
        }
        .step.completed .step-label {
            color: #22c55e;
        }
        .step-line {
            position: absolute;
            top: 16px;
            left: 20%;
            right: 20%;
            height: 2px;
            background: #e5e5e5;
            z-index: 0;
        }
        h1 {
            color: #1a1a1a;
            margin-bottom: 8px;
            font-size: 24px;
            font-weight: 600;
        }
        .subtitle {
            color: #666;
            margin-bottom: 24px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            color: #1a1a1a;
            font-weight: 500;
            font-size: 14px;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #2563eb;
        }
        .form-help {
            font-size: 12px;
            color: #999;
            margin-top: 4px;
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }
        .btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
        }
        .btn-primary {
            background: #2563eb;
            color: #fff;
        }
        .btn-primary:hover {
            background: #1d4ed8;
        }
        .btn-secondary {
            background: #f5f5f5;
            color: #555;
            border: 1px solid #ddd;
        }
        .btn-secondary:hover {
            background: #e5e5e5;
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

        <h1>Veritabanı</h1>
        <p class="subtitle">Bağlantı bilgilerinizi girin</p>

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
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Geri</span>
                </a>
                <button type="submit" class="btn btn-primary">
                    <span>Devam</span>
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</body>
</html>
