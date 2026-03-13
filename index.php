<?php
/**
 * Frontend Giriş Noktası
 * Tüm frontend istekleri buradan yönlendirilir
 */

// HTTP → HTTPS yönlendirme (.htaccess çalışmazsa yedek; mobil dahil)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
if (!$isHttps) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    if ($host !== '') {
        header('Location: https://' . $host . $uri, true, 301);
        exit;
    }
}

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

// Composer autoload (vendor)
$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

// Core dosyalarını yükle (kurulum tamamlandıysa)
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/Model.php';
require_once __DIR__ . '/core/ViewRenderer.php';

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
// index.php ile biten veya içeren path'ten script adını kaldır (bazı sunucularda REQUEST_URI farklı gelebilir)
if (strpos($requestPath, 'index.php') === 0) {
    $requestPath = trim(substr($requestPath, strlen('index.php')), '/');
}

// Çeviri modülü aktifse URL'deki dil önekini kaldır (örn: en/ilanlar -> ilanlar) böylece modül route'u eşleşir
$pathForModule = $requestPath;
if (class_exists('ModuleLoader')) {
    $ml = ModuleLoader::getInstance();
    $translationModule = $ml->getModule('translation');
    if ($translationModule && !empty($translationModule['is_active'])) {
        $pathParts = explode('/', $requestPath);
        if (!empty($pathParts[0]) && strlen($pathParts[0]) === 2) {
            require_once __DIR__ . '/modules/translation/models/TranslationModel.php';
            $translationModel = new TranslationModel();
            if ($translationModel->isValidLanguage($pathParts[0])) {
                $langCode = strtolower($pathParts[0]);
                $translationController = $ml->getModuleController('translation');
                if ($translationController && method_exists($translationController, 'getLanguageService')) {
                    $languageService = $translationController->getLanguageService();
                    if ($languageService && method_exists($languageService, 'setCurrentLanguage')) {
                        $languageService->setCurrentLanguage($langCode);
                    }
                }
                array_shift($pathParts);
                $pathForModule = implode('/', $pathParts);
            }
        }
    }
}

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
        // CORS: Video dosyaları embed için
        if (strpos($requestPath, 'public/uploads/') === 0) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, HEAD, OPTIONS');
            header('Access-Control-Expose-Headers: Content-Length, Content-Range, Accept-Ranges');
            if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(204);
                exit;
            }
        }
        // MIME type belirle
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'avif' => 'image/avif',
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
        $isVideo = in_array($ext, ['mp4', 'webm', 'ogg'], true);
        $filesize = filesize($filePath);
        $filemtime = filemtime($filePath);

        // Video: Range (byte-range) desteği
        if ($isVideo && $filesize > 0) {
            $rangeHeader = isset($_SERVER['HTTP_RANGE']) ? trim($_SERVER['HTTP_RANGE']) : '';
            if ($rangeHeader !== '' && preg_match('/^bytes=(\d*)-(\d*)$/', $rangeHeader, $m)) {
                $start = $m[1] === '' ? 0 : (int) $m[1];
                $end = $m[2] === '' ? $filesize - 1 : min((int) $m[2], $filesize - 1);
                if ($start <= $end) {
                    $length = $end - $start + 1;
                    header('HTTP/1.1 206 Partial Content');
                    header('Content-Type: ' . $mimeType);
                    header('Content-Length: ' . $length);
                    header('Content-Range: bytes ' . $start . '-' . $end . '/' . $filesize);
                    header('Accept-Ranges: bytes');
                    if (strpos($requestPath, 'public/uploads/') === 0) {
                        header('Access-Control-Allow-Origin: *');
                        header('Access-Control-Expose-Headers: Content-Length, Content-Range, Accept-Ranges');
                    }
                    header('Cache-Control: public, max-age=2592000');
                    $fp = fopen($filePath, 'rb');
                    if ($fp) {
                        fseek($fp, $start, SEEK_SET);
                        $bufferSize = 8192;
                        $remaining = $length;
                        while ($remaining > 0 && !feof($fp)) {
                            $read = (int) min($bufferSize, $remaining);
                            echo fread($fp, $read);
                            $remaining -= $read;
                        }
                        fclose($fp);
                    }
                    exit;
                }
            }
        }

        header('Content-Type: ' . $mimeType);
        
        // Cache headers - CSS ve JS için uzun süreli cache
        if (in_array($ext, ['css', 'js', 'woff', 'woff2', 'ttf', 'eot', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'avif', 'svg', 'ico', 'mp4', 'webm', 'ogg'])) {
            // ETag oluştur (dosya boyutu + değişiklik zamanı)
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
            } elseif (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'avif', 'svg', 'ico'])) {
                header('Cache-Control: public, max-age=15552000'); // 6 ay
            } elseif (in_array($ext, ['mp4', 'webm', 'ogg'])) {
                header('Cache-Control: public, max-age=2592000'); // 1 ay
            }
        } else {
            // Diğer dosyalar için kısa cache
            header('Cache-Control: public, max-age=3600'); // 1 saat
        }
        
        if ($isVideo) {
            header('Accept-Ranges: bytes');
        }
        
        readfile($filePath);
        exit;
    }
}

// Modül asset'lerini frontend üzerinden servis et (admin.php'ye gerek kalmaz, MIME/404 sorunu olmaz)
$moduleAssetPos = strpos($requestPath, 'module-asset/');
if ($moduleAssetPos !== false) {
    $suffix = substr($requestPath, $moduleAssetPos + strlen('module-asset/'));
    $parts = explode('/', $suffix, 2);
    $moduleName = isset($parts[0]) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $parts[0]) : '';
    $file = isset($parts[1]) ? $parts[1] : '';
    if ($moduleName !== '' && $file !== '' && strpos($file, '..') === false && preg_match('/^[a-zA-Z0-9_.-]+$/', $file)) {
        $filePath = __DIR__ . '/modules/' . $moduleName . '/assets/' . $file;
        if (is_file($filePath)) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $mimeTypes = ['css' => 'text/css', 'js' => 'application/javascript', 'json' => 'application/json', 'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'webp' => 'image/webp', 'avif' => 'image/avif', 'svg' => 'image/svg+xml', 'woff' => 'font/woff', 'woff2' => 'font/woff2'];
            header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
            header('Cache-Control: public, max-age=86400');
            readfile($filePath);
            exit;
        }
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
            'avif' => 'image/avif',
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
        if (in_array($ext, ['css', 'js', 'woff', 'woff2', 'ttf', 'eot', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'avif', 'svg', 'ico'])) {
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

// public/ önekli URL'ler (statik dosya değilse) modül route eşleşmesi için öneki kaldır
// Örn: public/sozlesme-imza/{token} -> sozlesme-imza/{token} (karşı taraf imza linki)
if (strpos($pathForModule, 'public/') === 0) {
    $pathForModule = substr($pathForModule, strlen('public/'));
}

// About/contact/iletisim/hakkimizda: Doğrudan HomeController'a git (modül ve Router'ı atla - her zaman Çizgi Aks)
$staticPageSlugs = ['about', 'about.php', 'hakkimizda', 'hakkimizda.php', 'contact', 'contact.php', 'iletisim', 'iletisim.php'];
$pathForStaticCheck = $pathForModule;
if (strlen($pathForStaticCheck) > 4 && substr($pathForStaticCheck, -4) === '.php') {
    $pathForStaticCheck = substr($pathForStaticCheck, 0, -4);
}
$isStaticPage = in_array($pathForModule, $staticPageSlugs, true) || in_array($pathForStaticCheck, ['about', 'hakkimizda', 'contact', 'iletisim'], true);

if ($isStaticPage) {
    $staticAction = [
        'about' => 'about',
        'hakkimizda' => 'hakkimizda',
        'contact' => 'contact',
        'iletisim' => 'iletisim',
    ][$pathForStaticCheck] ?? null;
    if ($staticAction) {
        require_once __DIR__ . '/app/controllers/HomeController.php';
        $home = new HomeController();
        $home->$staticAction();
        exit;
    }
}

try {
    if ($moduleLoader->handleFrontendRoute($pathForModule)) {
        exit;
    }
} catch (Exception $e) {
    error_log("Module frontend route error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
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

// İletişim formu gönderimi
$router->post('/contact/submit', 'HomeController@contactSubmit');
$router->post('/iletisim/submit', 'HomeController@contactSubmit');

// Teklif alma sayfası (özel route)
$router->get('/teklif-al', 'HomeController@quoteRequest');
$router->get('/quote-request', 'HomeController@quoteRequest');

// Rezervasyon sayfası
$router->get('/rezervasyon', 'HomeController@reservation');

// Arama sayfası
$router->get('/search', 'HomeController@search');

// İlanlar modülü fallback (handleFrontendRoute eşleşmezse Router üzerinden çalışır)
$router->get('/ilanlar', 'ModuleProxyController@listingsIndex');
$router->get('/ilanlar/kategori/{slug}', 'ModuleProxyController@listingsCategory');
$router->get('/ilan/{slug}', 'ModuleProxyController@listingDetail');

// Form gönderimi (frontend)
$router->post('/forms/submit', 'FormController@submit');

// Firmaya özel sayfalar (sayfa yapıcı/modülden bağımsız)
$router->get('/contact', 'HomeController@contact');
$router->get('/iletisim', 'HomeController@iletisim');
$router->get('/hakkimizda', 'HomeController@hakkimizda');
$router->get('/about', 'HomeController@about');
// .php uzantılı URL'ler (bazı sunucularda Router normalizasyonu farklı çalışabiliyor)
$router->get('/about.php', 'HomeController@about');
$router->get('/hakkimizda.php', 'HomeController@hakkimizda');
$router->get('/contact.php', 'HomeController@contact');
$router->get('/iletisim.php', 'HomeController@iletisim');

// Sayfa route'ları (tema modülü theme_pages üzerinden gösterilir)
$router->get('/{slug}', 'HomeController@pageProxy');

// Hook: Modüller ek route ekleyebilir
do_action('register_routes', $router);

// Routing'i başlat
$router->dispatch();
