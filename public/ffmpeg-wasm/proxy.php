<?php
/**
 * FFmpeg.wasm dosyaları için basit proxy
 * Same-origin policy'yi aşmak için CDN dosyalarını kendi domain'den serve eder
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

// Buffer'ları temizle
while (ob_get_level()) {
    ob_end_clean();
}

$config = $allowed[$file];
$url = $config['url'];

// cURL ile fetch (file_get_contents yerine daha güvenilir)
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
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
    error_log('[FFmpeg Proxy] Failed to fetch ' . $url . ': ' . $error);
    exit;
}

// Header'ları ayarla
header('Content-Type: ' . $config['type']);
header('Cache-Control: public, max-age=86400');
header('Content-Length: ' . strlen($body));

echo $body;
exit;
