<?php
/**
 * Kurulum Adım 3: Tamamlandı
 */

session_start();

// Session temizle
$admin_username = $_SESSION['admin_username'] ?? 'admin';
$admin_email = $_SESSION['admin_email'] ?? '';
session_destroy();

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codetic Kurulum Tamamlandı!</title>
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
            text-align: center;
        }
        .success-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 20px;
            background: #22c55e;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: #fff;
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
        .credentials {
            background: #f9f9f9;
            border-radius: 6px;
            padding: 20px;
            margin: 24px 0;
            text-align: left;
        }
        .credentials h3 {
            color: #1a1a1a;
            margin-bottom: 16px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .credential-item {
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e5e5;
        }
        .credential-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        .credential-label {
            font-weight: 500;
            color: #555;
            font-size: 14px;
        }
        .credential-value {
            color: #2563eb;
            font-family: 'SF Mono', Monaco, monospace;
            background: #fff;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 13px;
            border: 1px solid #ddd;
        }
        .warning {
            background: #fff3cd;
            border-left: 3px solid #ffc107;
            padding: 12px;
            margin: 20px 0;
            border-radius: 6px;
            color: #856404;
            text-align: left;
            font-size: 13px;
        }
        .warning strong {
            display: block;
            margin-bottom: 4px;
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
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
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
        .note {
            margin-top: 24px;
            padding: 12px;
            background: #f9f9f9;
            border-radius: 6px;
            font-size: 12px;
            color: #666;
            line-height: 1.5;
        }
        .note strong {
            color: #1a1a1a;
        }
        .note code {
            background: #e5e5e5;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'SF Mono', Monaco, monospace;
            font-size: 11px;
            color: #1a1a1a;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="success-icon">✓</div>
        <h1>Tamamlandı</h1>
        <p class="subtitle">
            Kurulum başarıyla tamamlandı. Aşağıdaki bilgilerle giriş yapabilirsiniz.
        </p>

        <div class="credentials">
            <h3>Giriş Bilgileri</h3>
            <div class="credential-item">
                <span class="credential-label">Kullanıcı Adı</span>
                <span class="credential-value"><?php echo htmlspecialchars($admin_username); ?></span>
            </div>
            <?php if ($admin_email): ?>
            <div class="credential-item">
                <span class="credential-label">E-posta</span>
                <span class="credential-value"><?php echo htmlspecialchars($admin_email); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="warning">
            <strong>Güvenlik</strong>
            İlk girişten sonra şifrenizi değiştirin.
        </div>

        <div class="btn-group">
            <a href="../public/" class="btn btn-secondary">
                <span>Ana Sayfa</span>
            </a>
            <a href="../public/admin.php?page=login" class="btn btn-primary">
                <span>Admin</span>
                <svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>

        <div class="note">
            <strong>Not:</strong> Kurulum dosyalarını sunucudan silin.
        </div>
    </div>
</body>
</html>
