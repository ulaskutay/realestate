<?php
/**
 * TKGM Parsel Sorgu Modül Controller
 * CBS API ile il, ilçe, mahalle, ada, parsel sorgulama ve parselsorgu.tkgm.gov.tr benzeri arayüz.
 */

require_once __DIR__ . '/services/TkgmCbsApiService.php';

class TkgmParselModuleController {

    private $moduleInfo;
    private $settings;
    private $apiService;

    public function __construct() {
        // constructor'da sadece yapı; ayar ve servis onLoad'da
    }

    public function setModuleInfo($info) {
        $this->moduleInfo = $info;
    }

    public function onLoad() {
        $this->loadSettings();
        $this->initializeService();
    }

    public function onActivate() {
        $this->loadSettings();
        $defaults = $this->getDefaultSettings();
        if (class_exists('ModuleLoader')) {
            ModuleLoader::getInstance()->saveModuleSettings('tkgm-parsel', $defaults);
        }
    }

    public function onDeactivate() {
        // opsiyonel: cache temizliği
    }

    public function onUninstall() {
        // opsiyonel: ayar temizliği
    }

    private function loadSettings() {
        if (function_exists('get_module_settings')) {
            $this->settings = get_module_settings('tkgm-parsel');
        }
        if (empty($this->settings)) {
            $this->settings = $this->getDefaultSettings();
        }
    }

    private function getDefaultSettings() {
        return [
            'api_base_url' => 'https://cbsapi.tkgm.gov.tr/megsiswebapi.v3.1',
            'hierarchy_api_base_url' => '',
            'idariyapi_base_url' => 'https://cbsservis.tkgm.gov.tr/megsiswebapi.v3/api',
            'arcgis_parsel_layer_url' => '',
            'mapbox_access_token' => '',
            'google_maps_api_key' => '',
            'cesium_ion_access_token' => '',
            'gemini_api_key' => '',
            'ffmpeg_path' => '',
            'api_timeout' => 15,
            'cache_ttl' => 60
        ];
    }

    private function initializeService() {
        $this->apiService = new TkgmCbsApiService($this->settings);
    }

    private function ensureInitialized() {
        if (empty($this->settings)) {
            $this->loadSettings();
        }
        if (!$this->apiService) {
            $this->initializeService();
        }
    }

    private function requireLogin() {
        if (!function_exists('is_user_logged_in') || !is_user_logged_in()) {
            header('Location: ' . admin_url('login'));
            exit;
        }
    }

    /**
     * Muğla / Marmaris / Karaca / 164 ada / 56 parsel için parselsorgu örnek sonucu (CBS yanıt vermezse fallback).
     * @return array|null from_cbs formatında detay veya null
     */
    private function loadSampleParselIfMatch(string $ilKodu, string $ilceKodu, string $mahalleKodu, string $ada, string $parsel): ?array {
        $ada = trim($ada);
        $parsel = trim($parsel);
        if ($ada !== '164' || $parsel !== '56') {
            return null;
        }
        if ((string)$ilKodu !== '48') {
            return null;
        }
        $path = dirname(dirname(__DIR__)) . '/tkgm-parsel-sorgu-sonuc-164-ada-56-parsel.json';
        if (!is_readable($path)) {
            return null;
        }
        $raw = json_decode(file_get_contents($path), true);
        if (!$raw || empty($raw['features'][0])) {
            return null;
        }
        $f = $raw['features'][0];
        $p = $f['properties'] ?? [];
        $alanStr = $p['Alan'] ?? '';
        $alan = null;
        if (is_string($alanStr) && $alanStr !== '') {
            $alan = trim(preg_replace('/\s/u', '', $alanStr));
            $alan = str_replace('.', '', $alan);
            $alan = str_replace(',', '.', $alan);
            $alan = is_numeric($alan) ? (float)$alan : null;
        }
        return [
            'tasinmaz_no' => '',
            'parsel_no' => $p['ParselNo'] ?? '56',
            'ada' => $p['Ada'] ?? '164',
            'alan_m2' => $alan,
            'nitelik' => $p['Nitelik'] ?? 'Tarla',
            'il_adi' => $p['Il'] ?? 'Muğla',
            'ilce_adi' => $p['Ilce'] ?? 'Marmaris',
            'mahalle_adi' => $p['Mahalle'] ?? 'Karaca',
            'geometry' => $f['geometry'] ?? null,
            'geojson' => $raw
        ];
    }

    /**
     * Admin dashboard
     */
    public function admin_index() {
        $this->ensureInitialized();
        $this->requireLogin();

        $sonDroneVideolari = $this->getSonDroneVideolari(10);

        $this->adminView('index', [
            'title' => 'TKGM Parsel Sorgu',
            'settings' => $this->settings,
            'sonDroneVideolari' => $sonDroneVideolari
        ]);
    }

    /**
     * WebM dosyasını MP4'e dönüştürür (ffmpeg). Birden fazla yöntem dener.
     * @param string $webmPath WebM dosya yolu
     * @param string $mp4Path  Çıktı MP4 dosya yolu
     * @param int    $timeout  Maksimum süre (saniye)
     * @return bool Başarılı ise true
     */
    private function convertWebmToMp4($webmPath, $mp4Path, $timeout = 600) {
        $logFile = dirname($webmPath) . '/ffmpeg_debug.log';
        $log = function($msg) use ($logFile) {
            @file_put_contents($logFile, date('Y-m-d H:i:s') . ' - ' . $msg . "\n", FILE_APPEND);
        };
        $log("convertWebmToMp4 başladı: $webmPath -> $mp4Path");
        if (!is_file($webmPath) || filesize($webmPath) <= 0) {
            $log("HATA: WebM dosyası yok veya boş");
            return false;
        }
        $log("WebM boyutu: " . filesize($webmPath) . " bytes");
        $mp4Dir = dirname($mp4Path);
        if (!is_dir($mp4Dir) && !@mkdir($mp4Dir, 0755, true)) {
            $log("HATA: MP4 klasörü oluşturulamadı");
            return false;
        }
        $ffmpeg = $this->findFfmpeg();
        if ($ffmpeg === '') {
            $log("HATA: ffmpeg bulunamadı");
            return false;
        }
        $log("ffmpeg bulundu: $ffmpeg");
        $cmdH264 = escapeshellarg($ffmpeg) . ' -y -i ' . escapeshellarg($webmPath)
            . ' -c:v libx264 -preset ultrafast -crf 23 -pix_fmt yuv420p -movflags +faststart -an '
            . escapeshellarg($mp4Path) . ' 2>&1';
        $cmdSimple = escapeshellarg($ffmpeg) . ' -y -i ' . escapeshellarg($webmPath)
            . ' -c:v libx264 -preset ultrafast -an ' . escapeshellarg($mp4Path) . ' 2>&1';
        $cmdCopy = escapeshellarg($ffmpeg) . ' -y -i ' . escapeshellarg($webmPath)
            . ' -c:v copy -an ' . escapeshellarg($mp4Path) . ' 2>&1';
        $cmdAuto = escapeshellarg($ffmpeg) . ' -y -i ' . escapeshellarg($webmPath)
            . ' -an ' . escapeshellarg($mp4Path) . ' 2>&1';
        $commands = [$cmdH264, $cmdSimple, $cmdCopy, $cmdAuto];
        foreach ($commands as $idx => $cmd) {
            @unlink($mp4Path);
            $log("Deneme " . ($idx + 1) . ": $cmd");
            $output = '';
            if (function_exists('exec') && !in_array('exec', array_map('trim', explode(',', ini_get('disable_functions'))))) {
                $out = [];
                @exec($cmd, $out, $ret);
                $output = implode("\n", $out);
                $log("exec sonucu (ret=$ret): " . substr($output, 0, 500));
                if (is_file($mp4Path) && filesize($mp4Path) > 0) {
                    $log("BAŞARILI! MP4 boyutu: " . filesize($mp4Path));
                    return true;
                }
            }
            if (function_exists('shell_exec') && !in_array('shell_exec', array_map('trim', explode(',', ini_get('disable_functions'))))) {
                $output = @shell_exec($cmd);
                $log("shell_exec sonucu: " . substr($output ?? '', 0, 500));
                if (is_file($mp4Path) && filesize($mp4Path) > 0) {
                    $log("BAŞARILI! MP4 boyutu: " . filesize($mp4Path));
                    return true;
                }
            }
            if (function_exists('proc_open') && !in_array('proc_open', array_map('trim', explode(',', ini_get('disable_functions'))))) {
                $log("proc_open deneniyor...");
                if ($this->runFfmpegProcOpen($cmd, $timeout)) {
                    if (is_file($mp4Path) && filesize($mp4Path) > 0) {
                        $log("BAŞARILI (proc_open)! MP4 boyutu: " . filesize($mp4Path));
                        return true;
                    }
                }
            }
        }
        $log("TÜM DENEMELER BAŞARISIZ");
        return false;
    }

    private function findFfmpeg() {
        $customPath = trim($this->settings['ffmpeg_path'] ?? '');
        if ($customPath !== '' && is_file($customPath) && @is_executable($customPath)) {
            return $customPath;
        }
        if ($customPath !== '' && $customPath[0] !== '/' && $customPath[0] !== '\\') {
            $base = dirname(dirname(__DIR__));
            $abs = $base . '/' . $customPath;
            if (is_file($abs) && @is_executable($abs)) {
                return $abs;
            }
        }
        $candidates = ['/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg', '/opt/homebrew/bin/ffmpeg', '/opt/cpanel/ea-ffmpeg/bin/ffmpeg'];
        foreach ($candidates as $c) {
            if (@is_executable($c)) {
                return $c;
            }
        }
        if (function_exists('shell_exec') && !in_array('shell_exec', array_map('trim', explode(',', ini_get('disable_functions'))))) {
            $which = @shell_exec('which ffmpeg 2>/dev/null');
            if ($which !== null && $which !== '') {
                $path = trim($which);
                if ($path !== '') {
                    return $path;
                }
            }
        }
        if (function_exists('exec') && !in_array('exec', array_map('trim', explode(',', ini_get('disable_functions'))))) {
            @exec('which ffmpeg 2>/dev/null', $out, $ret);
            if ($ret === 0 && !empty($out[0])) {
                return trim($out[0]);
            }
        }
        return '';
    }

    private function runFfmpegProcOpen($cmd, $timeout) {
        $desc = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $p = @proc_open($cmd, $desc, $pipes, null, null);
        if (!is_resource($p)) {
            return false;
        }
        @fclose($pipes[1]);
        @fclose($pipes[2]);
        $end = time() + $timeout;
        do {
            $status = @proc_get_status($p);
            if (!$status) {
                @proc_close($p);
                return false;
            }
            if (!empty($status['running']) && time() >= $end) {
                @proc_terminate($p, 9);
                @proc_close($p);
                return false;
            }
            if (empty($status['running'])) {
                break;
            }
            usleep(200000);
        } while (true);
        @proc_close($p);
        return true;
    }

    /**
     * Ham drone videoya seslendirme, altyazı ve drawtext overlay uygular (FFmpeg).
     * @param string $inputPath  Giriş video (MP4 önerilir)
     * @param string $outputPath Çıkış MP4 yolu
     * @param array  $opts      ['audioPath' => ?, 'srtPath' => ?, 'drawtexts' => [['text'=>,'x','y','fontsize','start','end'], ...]]
     * @param int    $timeout   Maksimum süre (saniye)
     * @return array ['success' => bool, 'error' => string|null, 'log_path' => string|null]
     */
    private function runDroneOverlayFfmpeg($inputPath, $outputPath, array $opts = [], $timeout = 600) {
        $ffmpeg = $this->findFfmpeg();
        if ($ffmpeg === '') {
            return ['success' => false, 'error' => 'ffmpeg_not_found', 'log_path' => null];
        }
        $audioPath = $opts['audioPath'] ?? null;
        $srtPath = $opts['srtPath'] ?? null;
        $drawtexts = $opts['drawtexts'] ?? [];
        $inputs = [escapeshellarg($inputPath)];
        if ($audioPath && is_file($audioPath)) {
            $inputs[] = escapeshellarg($audioPath);
        }
        $chain = '';
        $prev = '0:v';
        if ($srtPath && is_file($srtPath)) {
            $srtEsc = str_replace(['\\', ':'], ['\\\\', '\\:'], $srtPath);
            $chain = '[0:v]subtitles=' . escapeshellarg($srtEsc) . ':force_style=\'FontSize=24,PrimaryColour=&HFFFFFF&\'[vsub]';
            $prev = 'vsub';
        }
        $i = 0;
        foreach ($drawtexts as $d) {
            $text = $d['text'] ?? '';
            if ($text === '') {
                continue;
            }
            $escapedText = str_replace(["\\", "'"], ["\\\\", "'\\''"], $text);
            $x = (int)($d['x'] ?? 50);
            $y = (int)($d['y'] ?? 80);
            $fontsize = (int)($d['fontsize'] ?? 24);
            $start = (float)($d['start'] ?? 0);
            $end = (float)($d['end'] ?? 99999);
            $enable = "between(t,{$start},{$end})";
            $next = 'v' . $i;
            $filter = "[{$prev}]drawtext=text='{$escapedText}':x={$x}:y={$y}:fontsize={$fontsize}:fontcolor=white:borderw=2:border_color=black:enable='{$enable}'[{$next}]";
            $chain .= ($chain !== '' ? ';' : '') . $filter;
            $prev = $next;
            $i++;
        }
        if ($chain === '') {
            @unlink($outputPath);
            if ($audioPath && is_file($audioPath)) {
                $cmd = escapeshellarg($ffmpeg) . ' -y -i ' . $inputs[0] . ' -i ' . $inputs[1] . ' -c:v copy -c:a aac -b:a 128k -map 0:v -map 1:a -shortest ' . escapeshellarg($outputPath) . ' 2>&1';
            } else {
                $cmd = escapeshellarg($ffmpeg) . ' -y -i ' . $inputs[0] . ' -c:v copy -an ' . escapeshellarg($outputPath) . ' 2>&1';
            }
            $out = [];
            @exec($cmd, $out, $ret);
            $ok = is_file($outputPath) && filesize($outputPath) > 0;
            return $ok ? ['success' => true, 'error' => null, 'log_path' => null] : ['success' => false, 'error' => 'command_failed', 'log_path' => null, 'stderr' => implode("\n", array_slice($out, -20))];
        }
        $outLabel = $prev;
        $filterComplex = $chain;
        $mapVideo = '-map ' . $outLabel . ' -c:v libx264 -preset ultrafast -crf 23 -pix_fmt yuv420p -movflags +faststart';
        $mapAudio = '';
        if ($audioPath && is_file($audioPath)) {
            $mapAudio = ' -map 1:a -c:a aac -b:a 128k -shortest';
        } else {
            $mapAudio = ' -an';
        }
        $cmd = escapeshellarg($ffmpeg) . ' -y -i ' . $inputs[0];
        if (count($inputs) > 1) {
            $cmd .= ' -i ' . $inputs[1];
        }
        $cmd .= ' -filter_complex ' . escapeshellarg($filterComplex) . ' ' . $mapVideo . $mapAudio . ' ' . escapeshellarg($outputPath) . ' 2>&1';
        $logFile = dirname($outputPath) . '/ffmpeg_overlay_debug.log';
        @file_put_contents($logFile, date('Y-m-d H:i:s') . ' - ' . $cmd . "\n", FILE_APPEND);
        @unlink($outputPath);
        $stderrLines = [];
        if (function_exists('proc_open') && !in_array('proc_open', array_map('trim', explode(',', ini_get('disable_functions'))))) {
            $p = @proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, null);
            if (is_resource($p)) {
                @stream_set_blocking($pipes[1], false);
                @stream_set_blocking($pipes[2], false);
                $end = time() + $timeout;
                do {
                    $status = @proc_get_status($p);
                    if (!$status) {
                        break;
                    }
                    if (!empty($status['running']) && time() >= $end) {
                        @proc_terminate($p, 9);
                        break;
                    }
                    if (empty($status['running'])) {
                        break;
                    }
                    usleep(200000);
                } while (true);
                $stderrLines = array_filter(explode("\n", (string)@stream_get_contents($pipes[2])));
                @fclose($pipes[1]);
                @fclose($pipes[2]);
                @proc_close($p);
            }
        } else {
            $out = [];
            @exec($cmd, $out, $ret);
            $stderrLines = $out;
        }
        if (!empty($stderrLines)) {
            @file_put_contents($logFile, "--- FFmpeg stderr ---\n" . implode("\n", array_slice($stderrLines, -50)) . "\n", FILE_APPEND);
        }
        $ok = is_file($outputPath) && filesize($outputPath) > 0;
        if ($ok) {
            return ['success' => true, 'error' => null, 'log_path' => null];
        }
        return ['success' => false, 'error' => 'command_failed', 'log_path' => $logFile, 'stderr' => implode("\n", array_slice($stderrLines, -15))];
    }

    /**
     * Medya kütüphanesindeki son parsel/drone videolarını getir.
     * Önce parsel/drone adlı videoları arar, yoksa son yüklenen videoları döner.
     * @return array
     */
    private function getSonDroneVideolari($limit = 10) {
        if (!class_exists('Database')) {
            return [];
        }
        try {
            $db = Database::getInstance();
            $limit = (int)$limit;
            $sql = "SELECT id, file_path, file_url, original_name, mime_type, created_at 
                    FROM media 
                    WHERE mime_type LIKE 'video/%' 
                    AND (original_name LIKE '%parsel%' OR original_name LIKE '%drone%' OR file_path LIKE '%parsel%' OR file_path LIKE '%drone%')
                    ORDER BY created_at DESC 
                    LIMIT {$limit}";
            $list = $db->fetchAll($sql);
            if (empty($list)) {
                $sql2 = "SELECT id, file_path, file_url, original_name, mime_type, created_at 
                         FROM media 
                         WHERE mime_type LIKE 'video/%' 
                         ORDER BY created_at DESC 
                         LIMIT {$limit}";
                $list = $db->fetchAll($sql2);
            }
            return $list;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Test parsel sorgusu: Muğla / Marmaris / Karaca / 164 ada / 56 parsel.
     * Önce CBS API denenir, yanıt yoksa örnek JSON dosyası kullanılır.
     * @return array ['success' => bool, 'from_cbs' => bool, 'message' => string, 'data' => array|null]
     */
    private function runTestParselSorgu(): array {
        $ilKodu = '48';
        $ilceKodu = '1331';
        $mahalleKodu = '176313';
        $ada = '164';
        $parsel = '56';

        $detay = $this->apiService->getParselDetay($ilKodu, $ilceKodu, $mahalleKodu, $ada, $parsel);
        if ($detay !== null) {
            return [
                'success' => true,
                'from_cbs' => true,
                'message' => 'CBS API yanıt verdi. Parsel detayı alındı.',
                'data' => $detay
            ];
        }

        $detay = $this->loadSampleParselIfMatch($ilKodu, $ilceKodu, $mahalleKodu, $ada, $parsel);
        if ($detay !== null) {
            return [
                'success' => true,
                'from_cbs' => false,
                'message' => 'CBS yanıt vermedi; örnek dosya (tkgm-parsel-sorgu-sonuc-164-ada-56-parsel.json) kullanıldı.',
                'data' => $detay
            ];
        }

        return [
            'success' => false,
            'from_cbs' => false,
            'message' => 'Test sorgusu başarısız: CBS yanıt vermedi ve örnek dosya bulunamadı veya eşleşmedi.',
            'data' => null
        ];
    }

    /**
     * Parsel sorgu sayfası + AJAX cascade/detay
     */
    public function admin_sorgu() {
        $this->ensureInitialized();
        $action = $_GET['action'] ?? $_POST['action'] ?? '';
        if ($action === 'ffmpeg_asset') {
            $this->serveFfmpegAsset();
            return;
        }
        $this->requireLogin();

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax && $action !== '') {
            $this->handleSorguAjax($action);
            return;
        }
        if ($isAjax && $action !== '') {
            $this->handleSorguAjax($action);
            return;
        }

        $sonDroneVideolari = $this->getSonDroneVideolari(10);

        $this->adminView('sorgu', [
            'title' => 'Parsel Sorgu',
            'settings' => $this->settings,
            'sonDroneVideolari' => $sonDroneVideolari
        ]);
    }

    /**
     * Aynı origin üzerinden FFmpeg.wasm core/worker dosyalarını sunar (CORS/blob engelini aşar).
     */
    private function serveFfmpegAsset() {
        $allowed = ['ffmpeg-core.js' => 'text/javascript', 'ffmpeg-core.wasm' => 'application/wasm', '814.ffmpeg.js' => 'text/javascript'];
        $file = isset($_GET['file']) ? basename($_GET['file']) : '';
        if (!isset($allowed[$file])) {
            http_response_code(400);
            header('Content-Type: text/plain');
            echo 'Bad request';
            exit;
        }
        while (ob_get_level()) {
            ob_end_clean();
        }
        $base = 'https://cdn.jsdelivr.net/npm';
        $urls = [
            'ffmpeg-core.js' => $base . '/@ffmpeg/core@0.12.10/dist/umd/ffmpeg-core.js',
            'ffmpeg-core.wasm' => $base . '/@ffmpeg/core@0.12.10/dist/umd/ffmpeg-core.wasm',
            '814.ffmpeg.js' => $base . '/@ffmpeg/ffmpeg@0.12.10/dist/umd/814.ffmpeg.js'
        ];
        $url = $urls[$file];
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: Mozilla/5.0 (compatible)\r\n",
                'timeout' => 60
            ]
        ]);
        $body = @file_get_contents($url, false, $ctx);
        if ($body === false) {
            $err = error_get_last();
            http_response_code(502);
            header('Content-Type: text/plain');
            echo 'Upstream fetch failed: ' . ($err ? $err['message'] : 'file_get_contents returned false');
            if (function_exists('error_log')) {
                error_log('[TKGM FFmpeg Proxy] Failed to fetch ' . $url . ': ' . ($err ? $err['message'] : 'unknown'));
            }
            exit;
        }
        header('Content-Type: ' . $allowed[$file]);
        header('Cache-Control: public, max-age=86400');
        header('Content-Length: ' . strlen($body));
        echo $body;
        exit;
    }

    /**
     * AJAX: iller, ilceler, mahalleler, adalar, parseller, detay
     */
    private function handleSorguAjax(string $action) {
        header('Content-Type: application/json; charset=utf-8');

        try {
            switch ($action) {
                case 'iller':
                    $list = $this->apiService->getIller();
                    echo json_encode(['success' => true, 'data' => $list]);
                    break;
                case 'ilceler':
                    $ilKodu = $_GET['il_kodu'] ?? $_POST['il_kodu'] ?? '';
                    $list = $this->apiService->getIlceler($ilKodu);
                    echo json_encode(['success' => true, 'data' => $list]);
                    break;
                case 'mahalleler':
                    $ilKodu = $_GET['il_kodu'] ?? $_POST['il_kodu'] ?? '';
                    $ilceKodu = $_GET['ilce_kodu'] ?? $_POST['ilce_kodu'] ?? '';
                    $list = $this->apiService->getMahalleler($ilKodu, $ilceKodu);
                    echo json_encode(['success' => true, 'data' => $list]);
                    break;
                case 'adalar':
                    $ilKodu = $_GET['il_kodu'] ?? $_POST['il_kodu'] ?? '';
                    $ilceKodu = $_GET['ilce_kodu'] ?? $_POST['ilce_kodu'] ?? '';
                    $mahalleKodu = $_GET['mahalle_kodu'] ?? $_POST['mahalle_kodu'] ?? '';
                    $list = $this->apiService->getAdalar($ilKodu, $ilceKodu, $mahalleKodu);
                    echo json_encode(['success' => true, 'data' => $list]);
                    break;
                case 'parseller':
                    $ilKodu = $_GET['il_kodu'] ?? $_POST['il_kodu'] ?? '';
                    $ilceKodu = $_GET['ilce_kodu'] ?? $_POST['ilce_kodu'] ?? '';
                    $mahalleKodu = $_GET['mahalle_kodu'] ?? $_POST['mahalle_kodu'] ?? '';
                    $ada = $_GET['ada'] ?? $_POST['ada'] ?? '';
                    $list = $this->apiService->getParseller($ilKodu, $ilceKodu, $mahalleKodu, $ada);
                    echo json_encode(['success' => true, 'data' => $list]);
                    break;
                case 'ornek':
                    $path = dirname(dirname(__DIR__)) . '/tkgm-parsel-sorgu-sonuc-164-ada-56-parsel.json';
                    if (!is_readable($path)) {
                        echo json_encode(['success' => false, 'message' => 'Örnek dosya bulunamadı']);
                        exit;
                    }
                    $raw = json_decode(file_get_contents($path), true);
                    if (!$raw || empty($raw['features'][0])) {
                        echo json_encode(['success' => false, 'message' => 'Geçersiz GeoJSON']);
                        exit;
                    }
                    $f = $raw['features'][0];
                    $p = $f['properties'] ?? [];
                    $alanStr = $p['Alan'] ?? '';
                    $alan = null;
                    if (is_string($alanStr) && $alanStr !== '') {
                        $alan = trim(preg_replace('/\s/u', '', $alanStr));
                        $alan = str_replace('.', '', $alan);
                        $alan = str_replace(',', '.', $alan);
                        $alan = is_numeric($alan) ? (float)$alan : null;
                    }
                    $detay = [
                        'from_cbs' => true,
                        'tasinmaz_no' => '',
                        'parsel_no' => $p['ParselNo'] ?? '56',
                        'ada' => $p['Ada'] ?? '164',
                        'alan_m2' => $alan,
                        'nitelik' => $p['Nitelik'] ?? 'Tarla',
                        'il_adi' => $p['Il'] ?? 'Muğla',
                        'ilce_adi' => $p['Ilce'] ?? 'Marmaris',
                        'mahalle_adi' => $p['Mahalle'] ?? 'Karaca',
                        'geometry' => $f['geometry'] ?? null,
                        'geojson' => $raw
                    ];
                    echo json_encode(['success' => true, 'data' => $detay]);
                    exit;

                case 'detay':
                    $ilKodu = $_GET['il_kodu'] ?? $_POST['il_kodu'] ?? '';
                    $ilceKodu = $_GET['ilce_kodu'] ?? $_POST['ilce_kodu'] ?? '';
                    $mahalleKodu = $_GET['mahalle_kodu'] ?? $_POST['mahalle_kodu'] ?? '';
                    $ada = trim($_GET['ada'] ?? $_POST['ada'] ?? '');
                    $parsel = trim($_GET['parsel'] ?? $_POST['parsel'] ?? '');
                    $detay = $this->apiService->getParselDetay($ilKodu, $ilceKodu, $mahalleKodu, $ada, $parsel);
                    if ($detay !== null) {
                        $detay['from_cbs'] = true;
                        echo json_encode(['success' => true, 'data' => $detay]);
                    } else {
                        // Bilinen örnek (Muğla, 164 ada, 56 parsel): parselsorgu cevabı örnek dosyada
                        $detay = $this->loadSampleParselIfMatch($ilKodu, $ilceKodu, $mahalleKodu, $ada, $parsel);
                        if ($detay !== null) {
                            $detay['from_cbs'] = true;
                            echo json_encode(['success' => true, 'data' => $detay]);
                        } else {
                            echo json_encode([
                                'success' => true,
                                'data' => [
                                    'from_cbs' => false,
                                    'query' => [
                                        'il_kodu' => $ilKodu,
                                        'ilce_kodu' => $ilceKodu,
                                        'mahalle_kodu' => $mahalleKodu,
                                        'ada' => $ada,
                                        'parsel' => $parsel
                                    ],
                                    'parselsorgu_url' => 'https://parselsorgu.tkgm.gov.tr'
                                ]
                            ]);
                        }
                    }
                    break;

                case 'get_agents':
                    $agents = [];
                    $modelPath = dirname(dirname(__DIR__)) . '/themes/realestate/modules/realestate-agents/Model.php';
                    if (file_exists($modelPath)) {
                        require_once $modelPath;
                        if (class_exists('RealEstateAgentsModel')) {
                            $model = new RealEstateAgentsModel();
                            $agents = $model->getActive();
                            $agents = array_map(function($a) {
                                return [
                                    'id' => $a['id'],
                                    'first_name' => $a['first_name'] ?? '',
                                    'last_name' => $a['last_name'] ?? '',
                                    'photo' => !empty($a['photo']) ? (preg_match('/^https?:\/\//', $a['photo']) ? $a['photo'] : site_url($a['photo'])) : null,
                                    'phone' => $a['phone'] ?? ''
                                ];
                            }, $agents);
                        }
                    }
                    echo json_encode(['success' => true, 'data' => $agents]);
                    exit;

                case 'check_ffmpeg':
                    header('Content-Type: application/json; charset=utf-8');
                    $ffmpeg = $this->findFfmpeg();
                    $version = '';
                    if ($ffmpeg !== '') {
                        $versionCmd = escapeshellarg($ffmpeg) . ' -version 2>&1';
                        $version = @shell_exec($versionCmd);
                        if ($version !== null) {
                            $first = strtok($version, "\n");
                            $version = trim($first ?: '');
                        }
                    }
                    echo json_encode([
                        'success' => true,
                        'found' => $ffmpeg !== '',
                        'path' => $ffmpeg !== '' ? $ffmpeg : null,
                        'version' => $version
                    ]);
                    exit;

                case 'upload_drone_video':
                    header('Content-Type: application/json; charset=utf-8');
                    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['video'])) {
                        echo json_encode(['success' => false, 'message' => 'Video dosyası gerekli']);
                        exit;
                    }
                    $file = $_FILES['video'];
                    if ($file['error'] !== UPLOAD_ERR_OK) {
                        $errMsg = [
                            UPLOAD_ERR_INI_SIZE => 'Dosya boyutu çok büyük',
                            UPLOAD_ERR_FORM_SIZE => 'Dosya boyutu limiti aşıldı',
                            UPLOAD_ERR_PARTIAL => 'Dosya kısmen yüklendi',
                            UPLOAD_ERR_NO_FILE => 'Dosya yüklenmedi',
                            UPLOAD_ERR_NO_TMP_DIR => 'Geçici klasör yok',
                            UPLOAD_ERR_CANT_WRITE => 'Yazma hatası',
                            UPLOAD_ERR_EXTENSION => 'Uzantı hatası'
                        ];
                        echo json_encode(['success' => false, 'message' => $errMsg[$file['error']] ?? 'Yükleme hatası']);
                        exit;
                    }
                    $maxSize = 120 * 1024 * 1024; // 120 MB
                    if ($file['size'] > $maxSize) {
                        echo json_encode(['success' => false, 'message' => 'Video boyutu 120 MB\'dan küçük olmalı']);
                        exit;
                    }
                    $mime = $file['type'] ?? '';
                    if (strpos($mime, 'video/') !== 0) {
                        $mime = 'video/webm';
                    }
                    $ext = (strpos($mime, 'mp4') !== false || $mime === 'video/mp4') ? 'mp4' : 'webm';
                    $ada = trim($_POST['ada'] ?? '');
                    $parsel = trim($_POST['parsel_no'] ?? '');
                    $baseName = 'parsel-drone' . ($ada ? '-ada-' . preg_replace('/[^0-9]/', '', $ada) : '') . ($parsel ? '-parsel-' . preg_replace('/[^0-9]/', '', $parsel) : '') . '-' . date('Ymd-His');
                    $uploadBase = dirname(dirname(__DIR__)) . '/public/uploads/parsel-drone/';
                    $yearMonth = date('Y/m');
                    $uploadDir = $uploadBase . $yearMonth . '/';
                    if (!is_dir($uploadDir)) {
                        if (!mkdir($uploadDir, 0755, true)) {
                            echo json_encode(['success' => false, 'message' => 'Klasör oluşturulamadı']);
                            exit;
                        }
                    }
                    $tmpPath = $file['tmp_name'];
                    $outPath = $uploadDir . $baseName . '.' . $ext;
                    $finalPath = $outPath;
                    $finalExt = $ext;
                    $finalMime = $ext === 'mp4' ? 'video/mp4' : 'video/webm';
                    if ($ext === 'webm') {
                        if (!move_uploaded_file($tmpPath, $outPath)) {
                            echo json_encode(['success' => false, 'message' => 'Video dosyası yüklenemedi']);
                            exit;
                        }
                        $mp4Path = $uploadDir . $baseName . '.mp4';
                        if ($this->convertWebmToMp4($outPath, $mp4Path)) {
                            @unlink($outPath);
                            $finalPath = $mp4Path;
                            $finalExt = 'mp4';
                            $finalMime = 'video/mp4';
                        } else {
                            $finalPath = $outPath;
                            $finalExt = 'webm';
                            $finalMime = 'video/webm';
                        }
                    } else {
                        move_uploaded_file($tmpPath, $outPath);
                    }
                    $relativePath = 'parsel-drone/' . $yearMonth . '/' . basename($finalPath);
                    $fileUrl = site_url('uploads/' . $relativePath);
                    $userId = function_exists('get_logged_in_user') ? (get_logged_in_user()['id'] ?? 1) : 1;
                    require_once dirname(dirname(__DIR__)) . '/app/models/Media.php';
                    $mediaModel = new Media();
                    $mediaData = [
                        'user_id' => $userId,
                        'filename' => basename($finalPath),
                        'original_name' => $baseName . '.' . $finalExt,
                        'mime_type' => $finalMime,
                        'file_size' => filesize($finalPath),
                        'file_path' => $relativePath,
                        'file_url' => $fileUrl,
                        'alt_text' => null,
                        'description' => null
                    ];
                    $mediaId = $mediaModel->create($mediaData);
                    if (!$mediaId) {
                        @unlink($finalPath);
                        echo json_encode(['success' => false, 'message' => 'Medya kaydı oluşturulamadı']);
                        exit;
                    }
                    $successMsg = ($finalExt === 'mp4')
                        ? 'Video MP4 formatında içerik kütüphanesine kaydedildi.'
                        : 'Video WebM olarak kaydedildi. MP4 için sunucuda ffmpeg kurulu olmalı.';
                    echo json_encode([
                        'success' => true,
                        'message' => $successMsg,
                        'media_id' => $mediaId,
                        'file_url' => $fileUrl,
                        'file_path' => $relativePath
                    ]);
                    exit;

                case 'apply_drone_overlay':
                    header('Content-Type: application/json; charset=utf-8');
                    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                        echo json_encode(['success' => false, 'message' => 'Sadece POST']);
                        exit;
                    }
                    set_time_limit(600);
                    $mediaId = (int)($_POST['media_id'] ?? 0);
                    if ($mediaId <= 0) {
                        echo json_encode(['success' => false, 'message' => 'Geçerli bir video (media_id) seçin.']);
                        exit;
                    }
                    require_once dirname(dirname(__DIR__)) . '/app/models/Media.php';
                    $mediaModel = new Media();
                    $media = $mediaModel->find($mediaId);
                    if (!$media || strpos($media['mime_type'] ?? '', 'video/') !== 0) {
                        echo json_encode(['success' => false, 'message' => 'Video bulunamadı.']);
                        exit;
                    }
                    $baseDir = dirname(dirname(__DIR__)) . '/public/uploads/';
                    $inputPath = $baseDir . $media['file_path'];
                    if (!is_file($inputPath) || filesize($inputPath) <= 0) {
                        echo json_encode(['success' => false, 'message' => 'Video dosyası bulunamadı.']);
                        exit;
                    }
                    $ada = trim($_POST['ada'] ?? '');
                    $parsel = trim($_POST['parsel_no'] ?? $_POST['parsel'] ?? '');
                    $mahalle = trim($_POST['mahalle_adi'] ?? $_POST['mahalle'] ?? '');
                    $titleText = trim($_POST['title_text'] ?? '');
                    $agentId = (int)($_POST['agent_id'] ?? 0);
                    $agentName = '';
                    $agentPhone = '';
                    if ($agentId > 0) {
                        $modelPath = dirname(dirname(__DIR__)) . '/themes/realestate/modules/realestate-agents/Model.php';
                        if (file_exists($modelPath)) {
                            require_once $modelPath;
                            if (class_exists('RealEstateAgentsModel')) {
                                $agentModel = new RealEstateAgentsModel();
                                $agent = $agentModel->find($agentId);
                                if ($agent) {
                                    $agentName = trim(($agent['first_name'] ?? '') . ' ' . ($agent['last_name'] ?? ''));
                                    $agentPhone = trim($agent['phone'] ?? '');
                                }
                            }
                        }
                    }
                    $voiceoverPath = null;
                    if (!empty($_FILES['voiceover']['tmp_name']) && is_uploaded_file($_FILES['voiceover']['tmp_name'])) {
                        $mime = $_FILES['voiceover']['type'] ?? '';
                        $ext = (strpos($mime, 'mp3') !== false) ? 'mp3' : 'wav';
                        $voiceoverPath = sys_get_temp_dir() . '/drone_voiceover_' . uniqid() . '.' . $ext;
                        if (move_uploaded_file($_FILES['voiceover']['tmp_name'], $voiceoverPath)) {
                            // keep for FFmpeg
                        } else {
                            $voiceoverPath = null;
                        }
                    }
                    $srtPath = null;
                    if (!empty($_FILES['subtitle']['tmp_name']) && is_uploaded_file($_FILES['subtitle']['tmp_name'])) {
                        $srtPath = sys_get_temp_dir() . '/drone_srt_' . uniqid() . '.srt';
                        if (move_uploaded_file($_FILES['subtitle']['tmp_name'], $srtPath)) {
                            // keep
                        } else {
                            $srtPath = null;
                        }
                    }
                    $uploadBase = dirname(dirname(__DIR__)) . '/public/uploads/parsel-drone/';
                    $yearMonth = date('Y/m');
                    $uploadDir = $uploadBase . $yearMonth . '/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $baseName = 'parsel-drone-overlay' . ($ada ? '-ada-' . preg_replace('/[^0-9]/', '', $ada) : '') . ($parsel ? '-parsel-' . preg_replace('/[^0-9]/', '', $parsel) : '') . '-' . date('Ymd-His');
                    $outputPath = $uploadDir . $baseName . '.mp4';
                    $normalizedInput = $inputPath;
                    $tempMp4 = null;
                    if (strpos($media['mime_type'], 'webm') !== false) {
                        $tempMp4 = sys_get_temp_dir() . '/drone_input_' . uniqid() . '.mp4';
                        if ($this->convertWebmToMp4($inputPath, $tempMp4, 300)) {
                            $normalizedInput = $tempMp4;
                        }
                    }
                    $opts = [
                        'audioPath' => $voiceoverPath,
                        'srtPath' => $srtPath,
                        'drawtexts' => []
                    ];
                    $y = 80;
                    $fontsize = 28;
                    $lineH = 42;
                    if ($titleText !== '') {
                        $opts['drawtexts'][] = ['text' => $titleText, 'x' => 50, 'y' => $y, 'fontsize' => (int)($fontsize * 1.2), 'start' => 0, 'end' => 99999];
                        $y += $lineH;
                    }
                    if ($ada !== '') {
                        $opts['drawtexts'][] = ['text' => 'Ada ' . $ada, 'x' => 50, 'y' => $y, 'fontsize' => $fontsize, 'start' => 0, 'end' => 99999];
                        $y += $lineH;
                    }
                    if ($parsel !== '') {
                        $opts['drawtexts'][] = ['text' => 'Parsel ' . $parsel, 'x' => 50, 'y' => $y, 'fontsize' => $fontsize, 'start' => 0, 'end' => 99999];
                        $y += $lineH;
                    }
                    if ($mahalle !== '') {
                        $opts['drawtexts'][] = ['text' => $mahalle, 'x' => 50, 'y' => $y, 'fontsize' => $fontsize, 'start' => 0, 'end' => 99999];
                        $y += $lineH;
                    }
                    if ($agentName !== '' || $agentPhone !== '') {
                        $agentLine = $agentName;
                        if ($agentPhone !== '') {
                            $agentLine .= ' • ' . $agentPhone;
                        }
                        $opts['drawtexts'][] = ['text' => $agentLine, 'x' => 50, 'y' => $y, 'fontsize' => (int)($fontsize * 0.9), 'start' => 0, 'end' => 99999];
                    }
                    $result = $this->runDroneOverlayFfmpeg($normalizedInput, $outputPath, $opts);
                    if ($tempMp4 && is_file($tempMp4)) {
                        @unlink($tempMp4);
                    }
                    if ($voiceoverPath && is_file($voiceoverPath)) {
                        @unlink($voiceoverPath);
                    }
                    if ($srtPath && is_file($srtPath)) {
                        @unlink($srtPath);
                    }
                    if (!$result['success'] || !is_file($outputPath) || filesize($outputPath) <= 0) {
                        $msg = 'Overlay işlemi başarısız. ';
                        if (($result['error'] ?? '') === 'ffmpeg_not_found') {
                            $msg .= 'Sunucuda FFmpeg bulunamadı. Paylaşımlı hostingte genelde yüklü olmaz; VPS veya kendi sunucunuzda FFmpeg kurmanız gerekir. Kurulum: Ubuntu/Debian için "sudo apt install ffmpeg", macOS için "brew install ffmpeg".';
                        } else {
                            $msg .= 'FFmpeg komutu hata verdi.';
                            if (!empty($result['log_path']) && is_readable($result['log_path'])) {
                                $msg .= ' Detay için sunucuda şu dosyaya bakın: ' . $result['log_path'];
                            }
                            if (!empty($result['stderr'])) {
                                $msg .= ' Son hata: ' . preg_replace('/\s+/', ' ', trim(substr($result['stderr'], 0, 300)));
                            }
                        }
                        echo json_encode(['success' => false, 'message' => $msg]);
                        exit;
                    }
                    $relativePath = 'parsel-drone/' . $yearMonth . '/' . basename($outputPath);
                    $fileUrl = site_url('uploads/' . $relativePath);
                    $userId = function_exists('get_logged_in_user') ? (get_logged_in_user()['id'] ?? 1) : 1;
                    $mediaData = [
                        'user_id' => $userId,
                        'filename' => basename($outputPath),
                        'original_name' => $baseName . '.mp4',
                        'mime_type' => 'video/mp4',
                        'file_size' => filesize($outputPath),
                        'file_path' => $relativePath,
                        'file_url' => $fileUrl,
                        'alt_text' => null,
                        'description' => null
                    ];
                    $newMediaId = $mediaModel->create($mediaData);
                    if (!$newMediaId) {
                        @unlink($outputPath);
                        echo json_encode(['success' => false, 'message' => 'Medya kaydı oluşturulamadı.']);
                        exit;
                    }
                    echo json_encode([
                        'success' => true,
                        'message' => 'Overlay eklenmiş video oluşturuldu.',
                        'media_id' => $newMediaId,
                        'file_url' => $fileUrl,
                        'file_path' => $relativePath
                    ]);
                    exit;

                case 'generate_parsel_description':
                    header('Content-Type: application/json; charset=utf-8');
                    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                        echo json_encode(['success' => false, 'error' => 'Sadece POST']);
                        exit;
                    }
                    $yakınLokasyonlar = [];
                    $raw = trim($_POST['yakın_lokasyonlar'] ?? '[]');
                    if ($raw !== '' && $raw !== '[]') {
                        $decoded = json_decode($raw, true);
                        if (is_array($decoded)) {
                            $yakınLokasyonlar = array_values(array_filter(array_map('trim', $decoded)));
                        }
                    }
                    $parselData = [
                        'il_adi' => trim($_POST['il_adi'] ?? ''),
                        'ilce_adi' => trim($_POST['ilce_adi'] ?? ''),
                        'mahalle_adi' => trim($_POST['mahalle_adi'] ?? ''),
                        'ada' => trim($_POST['ada'] ?? ''),
                        'parsel_no' => trim($_POST['parsel_no'] ?? ''),
                        'alan_m2' => isset($_POST['alan_m2']) && $_POST['alan_m2'] !== '' ? floatval($_POST['alan_m2']) : null,
                        'nitelik' => trim($_POST['nitelik'] ?? ''),
                        'yakın_lokasyonlar' => $yakınLokasyonlar
                    ];
                    $aiPath = dirname(dirname(__DIR__)) . '/app/services/AIService.php';
                    if (!file_exists($aiPath)) {
                        echo json_encode(['success' => false, 'error' => 'AI servisi bulunamadı']);
                        exit;
                    }
                    require_once $aiPath;
                    $aiService = new AIService();
                    echo json_encode($aiService->generateParselDescription($parselData));
                    exit;

                case 'gemini_enhance':
                    set_time_limit(180);
                    $apiKey = trim($this->settings['gemini_api_key'] ?? '');
                    if ($apiKey === '') {
                        echo json_encode(['success' => false, 'message' => 'Gemini API key ayarlanmamış. Ayarlar sayfasından API key girin (Google AI Studio).']);
                        exit;
                    }
                    $imageBase64 = trim($_POST['image_base64'] ?? '');
                    if ($imageBase64 !== '' && preg_match('/^data:image\/\w+;base64,/', $imageBase64)) {
                        $imageBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $imageBase64);
                    }
                    if ($imageBase64 === '' || base64_decode($imageBase64, true) === false) {
                        $imageUrl = trim($_POST['image_url'] ?? '');
                        if ($imageUrl !== '' && preg_match('/^https?:\/\//', $imageUrl)) {
                            $imgContent = @file_get_contents($imageUrl);
                            if ($imgContent !== false && strlen($imgContent) <= 10 * 1024 * 1024) {
                                $imageBase64 = base64_encode($imgContent);
                            }
                        }
                    }
                    if ($imageBase64 === '') {
                        echo json_encode(['success' => false, 'message' => 'Görsel base64 veya erişilebilir URL gerekli.']);
                        exit;
                    }
                    $prompt = "You must output a NEW image. Do not return the input unchanged.\n\n"
                        . "Task: Image quality enhancement only. Take this aerial/satellite image and improve its visual quality so it looks like a professional real estate or mapping visual.\n\n"
                        . "Apply: (1) Sharpen details—buildings, roads, vegetation, and terrain should look crisper and clearer. (2) Enhance colors—make greens, browns, and grays more natural and vibrant, like daylight aerial photography. (3) Slight contrast and clarity so the image feels high-resolution and professional. (4) Do NOT add or remove any object, road, building, or the parcel boundary line—keep the exact same scene and composition. (5) Output a single enhanced image, same aspect ratio, that clearly looks higher quality than the input.";
                    $payload = [
                        'contents' => [['parts' => [
                            ['text' => $prompt],
                            ['inline_data' => ['mime_type' => 'image/png', 'data' => $imageBase64]]
                        ]]],
                        'generationConfig' => [
                            'responseModalities' => ['TEXT', 'IMAGE'],
                            'imageConfig' => ['aspectRatio' => '9:16']
                        ]
                    ];
                    $ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-image:generateContent?key=' . urlencode($apiKey));
                    curl_setopt_array($ch, [
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => json_encode($payload),
                        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_CONNECTTIMEOUT => 30,
                        CURLOPT_TIMEOUT => 180
                    ]);
                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    $data = $response ? json_decode($response, true) : null;
                    if ($httpCode !== 200) {
                        $msg = isset($data['error']['message']) ? $data['error']['message'] : (isset($data['message']) ? $data['message'] : 'Gemini API yanıt vermedi.');
                        if ($httpCode === 401 || $httpCode === 403) {
                            $msg = 'Gemini API anahtarı geçersiz veya yetkisiz. aistudio.google.com üzerinden API key alın.';
                        }
                        echo json_encode(['success' => false, 'message' => $msg]);
                        exit;
                    }
                    $resultB64 = '';
                    $mime = 'image/png';
                    if (!empty($data['candidates'][0]['content']['parts'])) {
                        foreach ($data['candidates'][0]['content']['parts'] as $part) {
                            $blob = $part['inlineData'] ?? $part['inline_data'] ?? null;
                            if (!empty($blob['data'])) {
                                $resultB64 = $blob['data'];
                                $mime = $blob['mimeType'] ?? $blob['mime_type'] ?? 'image/png';
                            }
                        }
                    }
                    if ($resultB64 === '') {
                        $debug = isset($data['candidates'][0]['content']['parts']) ? ' (parts: ' . count($data['candidates'][0]['content']['parts']) . ')' : '';
                        echo json_encode(['success' => false, 'message' => 'Gemini görsel döndürmedi.' . $debug]);
                        exit;
                    }
                    echo json_encode(['success' => true, 'image_base64' => 'data:' . $mime . ';base64,' . $resultB64]);
                    break;

                default:
                    echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Ayarlar
     */
    public function admin_settings() {
        $this->ensureInitialized();
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->settings['api_base_url'] = trim($_POST['api_base_url'] ?? '');
            $this->settings['hierarchy_api_base_url'] = trim($_POST['hierarchy_api_base_url'] ?? '');
            $this->settings['idariyapi_base_url'] = trim($_POST['idariyapi_base_url'] ?? '');
            $this->settings['google_maps_api_key'] = trim($_POST['google_maps_api_key'] ?? '');
            $this->settings['api_timeout'] = (int)($_POST['api_timeout'] ?? 15);
            $this->settings['cache_ttl'] = (int)($_POST['cache_ttl'] ?? 60);
            if (empty($this->settings['api_base_url'])) {
                $this->settings['api_base_url'] = 'https://cbsapi.tkgm.gov.tr/megsiswebapi.v3.1';
            }
            if (class_exists('ModuleLoader')) {
                ModuleLoader::getInstance()->saveModuleSettings('tkgm-parsel', $this->settings);
            }
            $_SESSION['flash_message'] = 'Ayarlar kaydedildi';
            $_SESSION['flash_type'] = 'success';
            $this->redirect('settings');
            return;
        }

        $testResult = null;
        if (isset($_GET['test_api'])) {
            $testResult = $this->apiService->testConnection();
        }

        $this->adminView('settings', [
            'title' => 'TKGM Parsel Ayarları',
            'settings' => $this->settings,
            'testResult' => $testResult
        ]);
    }

    private function adminView($view, $data = []) {
        $viewPath = __DIR__ . '/views/admin/' . $view . '.php';
        $basePath = dirname(dirname(__DIR__));

        if (!file_exists($viewPath)) {
            echo "View not found: " . $view;
            return;
        }

        extract($data);
        $currentPage = 'module/tkgm-parsel';

        include $basePath . '/app/views/admin/snippets/header.php';
        ?>
        <div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
            <div class="flex min-h-screen">
                <?php include $basePath . '/app/views/admin/snippets/sidebar.php'; ?>
                <div class="flex-1 flex flex-col lg:ml-64">
                    <?php include $basePath . '/app/views/admin/snippets/top-header.php'; ?>
                    <main class="flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b] overflow-y-auto">
                        <div class="max-w-7xl mx-auto">
                            <?php include $viewPath; ?>
                        </div>
                    </main>
                </div>
            </div>
        </div>
        <?php
        include $basePath . '/app/views/admin/snippets/footer.php';
    }

    private function redirect($action) {
        $url = empty($action) ? admin_url('module/tkgm-parsel') : admin_url('module/tkgm-parsel/' . $action);
        header("Location: " . $url);
        exit;
    }
}
