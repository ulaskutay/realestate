<?php
/**
 * Frontend Giriş Noktası
 * Tüm frontend istekleri buradan yönlendirilir
 */

// Session başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hata raporlamayı aç (geliştirme için)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kurulum kontrolü - ÖNCE config dosyasını kontrol et
$configFile = __DIR__ . '/config/database.php';
$needsInstall = false;

if (!file_exists($configFile)) {
    // Config dosyası yok, kurulum gerekli
    $needsInstall = true;
} else {
    // Config var, veritabanı bağlantısını test et - Database sınıfını kullanmadan direkt PDO ile
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
            // Tablo var, kurulum tamamlanmış, devam et
        } else {
            // Tablo yok, kurulum gerekli
            $needsInstall = true;
        }
    } catch (Throwable $e) {
        // Herhangi bir hata, kurulum gerekli
        $needsInstall = true;
    }
}

// Kurulum gerekliyse yönlendir
if ($needsInstall) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || 
                 (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) 
                 ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $installUrl = $protocol . "://" . $host . "/install.php";
    
    header("Location: " . $installUrl);
    exit;
}

// Core dosyalarını yükle (kurulum tamamlandıysa)
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/Model.php';
require_once __DIR__ . '/core/ViewRenderer.php';
require_once __DIR__ . '/core/Role.php';

// Hook Sistemi ve Modül Yükleyiciyi yükle
require_once __DIR__ . '/core/HookSystem.php';
require_once __DIR__ . '/core/ModuleLoader.php';
require_once __DIR__ . '/core/ShortcodeParser.php';

// Yardımcı fonksiyonları yükle
require_once __DIR__ . '/includes/functions.php';

// Modül sistemini başlat (ThemeLoader'dan ÖNCE!)
try {
    $moduleLoader = ModuleLoader::getInstance();
    $moduleLoader->init();
    do_action('init');
} catch (Exception $e) {
    error_log("Module loader error: " . $e->getMessage());
}

// ThemeLoader'ı yükle (tema modüllerinin yüklenmesi için)
require_once __DIR__ . '/core/ThemeLoader.php';
ThemeLoader::getInstance(); // Tema modüllerini yüklemek için

// Modül frontend route'larını kontrol et
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestPath = trim($requestPath, '/');

// Analytics API routes
if (strpos($requestPath, 'api/track') === 0) {
    require_once __DIR__ . '/app/controllers/AnalyticsController.php';
    $analyticsController = new AnalyticsController();
    
    if ($requestPath === 'api/track') {
        $analyticsController->track();
        exit;
    } elseif ($requestPath === 'api/track/duration') {
        $analyticsController->trackDuration();
        exit;
    }
}

// Public statik dosyalarını servis et (fallback - .htaccess çalışmazsa)
if (strpos($requestPath, 'public/') === 0) {
    $filePath = __DIR__ . '/' . $requestPath;
    if (file_exists($filePath) && is_file($filePath)) {
        // MIME type belirle
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'ogg' => 'video/ogg',
        ];
        
        $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $mimeType);
        
        // Cache headers - CSS ve JS için uzun süreli cache
        if (in_array($ext, ['css', 'js', 'woff', 'woff2', 'ttf', 'eot', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'ico', 'mp4', 'webm', 'ogg'])) {
            // ETag oluştur (dosya boyutu + değişiklik zamanı)
            $filemtime = filemtime($filePath);
            $filesize = filesize($filePath);
            $etag = md5($filePath . $filemtime . $filesize);
            
            header('ETag: "' . $etag . '"');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $filemtime) . ' GMT');
            
            // If-None-Match kontrolü (304 Not Modified)
            if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $etag . '"') {
                http_response_code(304);
                exit;
            }
            
            // If-Modified-Since kontrolü
            if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
                $ifModifiedSince = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
                if ($ifModifiedSince >= $filemtime) {
                    http_response_code(304);
                    exit;
                }
            }
            
            // Cache-Control: CSS/JS/Fonts için 1 yıl, görseller için 6 ay, videolar için 1 ay
            if (in_array($ext, ['css', 'js', 'woff', 'woff2', 'ttf', 'eot'])) {
                header('Cache-Control: public, max-age=31536000, immutable'); // 1 yıl, immutable
            } elseif (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'ico'])) {
                header('Cache-Control: public, max-age=15552000'); // 6 ay
            } elseif (in_array($ext, ['mp4', 'webm', 'ogg'])) {
                header('Cache-Control: public, max-age=2592000'); // 1 ay
            }
        } else {
            // Diğer dosyalar için kısa cache
            header('Cache-Control: public, max-age=3600'); // 1 saat
        }
        
        readfile($filePath);
        exit;
    }
}

// Tema statik dosyalarını servis et (screenshot, css, js, images)
if (strpos($requestPath, 'themes/') === 0) {
    $filePath = __DIR__ . '/' . $requestPath;
    if (file_exists($filePath) && is_file($filePath)) {
        // MIME type belirle
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
        ];
        
        $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $mimeType);
        
        // Cache headers - CSS ve JS için uzun süreli cache
        if (in_array($ext, ['css', 'js', 'woff', 'woff2', 'ttf', 'eot', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'ico'])) {
            // ETag oluştur (dosya boyutu + değişiklik zamanı)
            $filemtime = filemtime($filePath);
            $filesize = filesize($filePath);
            $etag = md5($filePath . $filemtime . $filesize);
            
            header('ETag: "' . $etag . '"');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $filemtime) . ' GMT');
            
            // If-None-Match kontrolü (304 Not Modified)
            if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $etag . '"') {
                http_response_code(304);
                exit;
            }
            
            // If-Modified-Since kontrolü
            if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
                $ifModifiedSince = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
                if ($ifModifiedSince >= $filemtime) {
                    http_response_code(304);
                    exit;
                }
            }
            
            // Cache-Control: CSS/JS için 1 yıl, görseller için 6 ay
            if (in_array($ext, ['css', 'js', 'woff', 'woff2', 'ttf', 'eot'])) {
                header('Cache-Control: public, max-age=31536000, immutable'); // 1 yıl, immutable
            } else {
                header('Cache-Control: public, max-age=15552000'); // 6 ay
            }
        } else {
            // Diğer dosyalar için kısa cache
            header('Cache-Control: public, max-age=3600'); // 1 saat
        }
        
        readfile($filePath);
        exit;
    }
}

// Önce modül route'larını dene (contact sayfası hariç - direkt HomeController kullanılacak)
if ($requestPath !== 'contact' && $requestPath !== 'iletisim') {
    try {
        if ($moduleLoader->handleFrontendRoute($requestPath)) {
            exit;
        }
    } catch (Exception $e) {
        error_log("Module frontend route error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
    }
}

// Router oluştur
$router = new Router();

// Frontend route'larını tanımla
$router->get('/', 'HomeController@index');

// Blog route'ları (özel route'lar önce gelsin)
$router->get('/blog', 'HomeController@blog');
$router->get('/blog/kategori/{slug}', 'HomeController@blogCategory');
$router->get('/blog/{slug}', 'HomeController@blogPost');

// Sözleşme route'ları
$router->get('/sozlesmeler/{slug}', 'AgreementController@show');

// İletişim sayfası (fallback - modül route'u çalışmazsa)
$router->get('/contact', 'HomeController@contact');
$router->get('/iletisim', 'HomeController@contact');

// Teklif alma sayfası (özel route)
$router->get('/teklif-al', 'HomeController@quoteRequest');
$router->get('/quote-request', 'HomeController@quoteRequest');

// Rezervasyon sayfası
$router->get('/rezervasyon', 'HomeController@reservation');

// Form gönderimi (frontend)
$router->post('/forms/submit', 'FormController@submit');

// Sayfa route'ları (slug bazlı - en son kontrol edilmeli, diğer route'ları engellememesi için)
$router->get('/{slug}', 'HomeController@page');

// Hook: Modüller ek route ekleyebilir
do_action('register_routes', $router);

// Routing'i başlat
$router->dispatch();
