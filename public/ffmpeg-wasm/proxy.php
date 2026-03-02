<?php
/**
 * FFmpeg.wasm dosyaları için proxy + disk önbelleği
 * İlk istekte CDN'den indirir ve cache'e yazar; sonraki istekler cache'den anında sunar.
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

$allowed = [
    'ffmpeg-core.js' => ['type' => 'text/javascript', 'url' => 'https://cdn.jsdelivr.net/npm/@ffmpeg/core@0.12.10/dist/umd/ffmpeg-core.js'],
    'ffmpeg-core.wasm' => ['type' => 'application/wasm', 'url' => 'https://cdn.jsdelivr.net/npm/@ffmpeg/core@0.12.10/dist/umd/ffmpeg-core.wasm'],
    '814.ffmpeg.js' => ['type' => 'text/javascript', 'url' => 'https://cdn.jsdelivr.net/npm/@ffmpeg/ffmpeg@0.12.10/dist/umd/814.ffmpeg.js']
];

$file = isset($_GET['file']) ? basename($_GET['file']) : '';

if (!isset($allowed[$file])) {
    http_response_code(400);
    header('Content-Type: text/plain');
    echo 'Invalid file requested';
    exit;
}

$config = $allowed[$file];
$cacheDir = __DIR__ . '/cache';
$cacheFile = $cacheDir . '/' . $file;

// Önbellek varsa doğrudan sun (çok hızlı)
if (is_file($cacheFile)) {
    $body = file_get_contents($cacheFile);
    if ($body !== false) {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: ' . $config['type']);
        header('Cache-Control: public, max-age=86400');
        header('Content-Length: ' . strlen($body));
        echo $body;
        exit;
    }
}

// Önbellek yok veya okunamadı → CDN'den indir (ilk seferde yavaş, 5 dk timeout)
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}

$url = $config['url'];
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 300);        // 5 dakika (30 MB wasm için)
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; FFmpegWasmProxy/1.0)');

$body = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($body === false || $httpCode !== 200) {
    http_response_code(502);
    header('Content-Type: text/plain');
    echo 'Upstream fetch failed: HTTP ' . $httpCode . ' ' . $error;
    if (function_exists('error_log')) {
        error_log('[FFmpeg Proxy] Failed to fetch ' . $url . ': ' . $error);
    }
    exit;
}

// Önbelleğe yaz (sonraki istekler anında gelsin)
@file_put_contents($cacheFile, $body);

while (ob_get_level()) ob_end_clean();
header('Content-Type: ' . $config['type']);
header('Cache-Control: public, max-age=86400');
header('Content-Length: ' . strlen($body));
echo $body;
exit;
