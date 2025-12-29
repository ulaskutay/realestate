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
            padding: 56px 40px;
            text-align: center;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
        }
        .success-icon {
            width: 96px;
            height: 96px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
            animation: scaleIn 0.5s ease-out;
        }
        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }
        h1 {
            color: #0f172a;
            margin-bottom: 16px;
            font-size: 36px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .subtitle {
            color: #64748b;
            margin-bottom: 32px;
            font-size: 16px;
            line-height: 1.7;
            max-width: 480px;
            margin-left: auto;
            margin-right: auto;
        }
        .credentials {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 16px;
            padding: 28px;
            margin: 32px 0;
            text-align: left;
            border: 2px solid #e2e8f0;
        }
        .credentials h3 {
            color: #0f172a;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .credential-item {
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 16px;
            border-bottom: 1px solid #e2e8f0;
        }
        .credential-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        .credential-label {
            font-weight: 600;
            color: #475569;
            font-size: 14px;
        }
        .credential-value {
            color: #3b82f6;
            font-family: 'SF Mono', Monaco, 'Cascadia Code', monospace;
            background: #fff;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 14px;
            border: 1px solid #e2e8f0;
            font-weight: 600;
        }
        .warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-left: 4px solid #f59e0b;
            padding: 18px 20px;
            margin: 24px 0;
            border-radius: 12px;
            color: #92400e;
            text-align: left;
            font-size: 14px;
            line-height: 1.6;
        }
        .warning strong {
            display: block;
            margin-bottom: 6px;
            font-size: 15px;
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
            text-decoration: none;
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
            border: 2px solid #e2e8f0;
        }
        .btn-secondary:hover {
            background: #e2e8f0;
            border-color: #cbd5e1;
        }
        .note {
            margin-top: 32px;
            padding: 16px;
            background: #f8fafc;
            border-radius: 12px;
            font-size: 13px;
            color: #64748b;
            line-height: 1.6;
        }
        .note strong {
            color: #0f172a;
        }
        .note code {
            background: #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'SF Mono', Monaco, monospace;
            font-size: 12px;
            color: #0f172a;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="success-icon">✓</div>
        <h1>Kurulum Tamamlandı!</h1>
        <p class="subtitle">
            Codetic sisteminiz başarıyla kuruldu ve kullanıma hazır. 
            Aşağıdaki bilgilerle admin paneline giriş yapabilirsiniz.
        </p>

        <div class="credentials">
            <h3>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 11C11.6569 11 13 9.65685 13 8C13 6.34315 11.6569 5 10 5C8.34315 5 7 6.34315 7 8C7 9.65685 8.34315 11 10 11Z" stroke="#3b82f6" stroke-width="2"/>
                    <path d="M10 14C7.33333 14 3 15.3333 3 18H17C17 15.3333 12.6667 14 10 14Z" stroke="#3b82f6" stroke-width="2"/>
                </svg>
                Giriş Bilgileri
            </h3>
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
            <strong>🔒 Güvenlik Uyarısı</strong>
            İlk girişinizden sonra mutlaka şifrenizi değiştirin ve güçlü bir parola kullanın.
        </div>

        <div class="btn-group">
            <a href="../public/" class="btn btn-secondary">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 10L10 3L17 10M4 9V16C4 16.5523 4.44772 17 5 17H8V13C8 12.4477 8.44772 12 9 12H11C11.5523 12 12 12.4477 12 13V17H15C15.5523 17 16 16.5523 16 16V9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span>Ana Sayfa</span>
            </a>
            <a href="../public/admin.php?page=login" class="btn btn-primary">
                <span>Admin Paneli</span>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>

        <div class="note">
            <strong>💡 Güvenlik Notu:</strong> Kurulum tamamlandıktan sonra <code>install.php</code> dosyasını ve <code>install/</code> klasörünü sunucunuzdan silmenizi öneririz.
        </div>
    </div>
</body>
</html>
