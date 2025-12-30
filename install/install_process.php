<?php
/**
 * Kurulum İşlem Dosyası
 * Veritabanı config oluşturur, tabloları oluşturur, admin kullanıcı ekler
 */

session_start();

// Session kontrolü
if (!isset($_SESSION['db_host']) || !isset($_SESSION['site_name'])) {
    header("Location: step1.php");
    exit;
}

// Hata raporlamayı aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codetic Kurulum - İşlem</title>
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
            max-width: 720px;
            width: 100%;
            max-height: 90vh;
            padding: 48px 40px;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
            overflow-y: auto;
        }
        .install-container::-webkit-scrollbar {
            width: 8px;
        }
        .install-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }
        .install-container::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            border-radius: 10px;
        }
        .install-container::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
        }
        h1 {
            color: #0f172a;
            margin-bottom: 32px;
            font-size: 32px;
            text-align: center;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .status-item {
            padding: 16px 20px;
            margin-bottom: 12px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 14px;
            line-height: 1.6;
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .status-item.success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border-left: 4px solid #10b981;
            color: #065f46;
        }
        .status-item.error {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-left: 4px solid #dc2626;
            color: #991b1b;
        }
        .status-item.info {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-left: 4px solid #3b82f6;
            color: #1e40af;
        }
        .status-icon {
            font-size: 20px;
            font-weight: 700;
            min-width: 24px;
            text-align: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 16px 24px;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            color: #fff;
            text-decoration: none;
            border-radius: 12px;
            text-align: center;
            font-size: 15px;
            font-weight: 600;
            margin-top: 32px;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.5);
        }
        .btn:active {
            transform: translateY(0);
        }
        .loading {
            text-align: center;
            padding: 40px 20px;
        }
        .spinner {
            width: 48px;
            height: 48px;
            margin: 0 auto 24px;
            border: 4px solid #e2e8f0;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="install-container">
        <h1>⚙️ Kurulum İşlemi</h1>
        <div id="status-container">
            <?php
            // Kurulum işlemini başlat
            require_once __DIR__ . '/install_process_action.php';
            ?>
        </div>
    </div>
</body>
</html>
