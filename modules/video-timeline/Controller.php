<?php
/**
 * Video Timeline Modül Controller
 * Timeline veri modeli: composition, tracks, clips (text, overlay, animation, keyframes).
 */

class VideoTimelineModuleController {

    private $moduleInfo;
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function setModuleInfo($info) {
        $this->moduleInfo = $info;
    }

    public function onLoad() {
        // Tablolar yoksa oluştur (install/schema.sql)
        $this->ensureTables();
    }

    public function onActivate() {
        $this->ensureTables();
    }

    public function onDeactivate() {}

    public function onUninstall() {}

    private function ensureTables() {
        $schemaPath = __DIR__ . '/install/schema.sql';
        if (!is_readable($schemaPath)) {
            return;
        }
        $sql = file_get_contents($schemaPath);
        if (empty($sql)) {
            return;
        }
        try {
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($statements as $stmt) {
                if (preg_match('/^\s*CREATE\s+TABLE/i', $stmt)) {
                    $this->db->query($stmt);
                }
            }
        } catch (Exception $e) {
            // Tablolar zaten var olabilir
        }
    }

    private function requireLogin() {
        if (!function_exists('is_user_logged_in') || !is_user_logged_in()) {
            header('Location: ' . admin_url('login'));
            exit;
        }
    }

    private function getUserId() {
        $user = function_exists('get_logged_in_user') ? get_logged_in_user() : null;
        return $user && isset($user['id']) ? (int) $user['id'] : 0;
    }

    private function adminView($view, $data = []) {
        $viewPath = __DIR__ . '/views/admin/' . $view . '.php';
        $basePath = dirname(dirname(__DIR__));

        if (!file_exists($viewPath)) {
            echo "View not found: " . $view;
            return;
        }

        extract($data);
        $currentPage = 'module/video-timeline';

        include $basePath . '/app/views/admin/snippets/header.php';
        ?>
        <div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
            <div class="flex min-h-screen">
                <?php include $basePath . '/app/views/admin/snippets/sidebar.php'; ?>
                <div class="flex-1 flex flex-col lg:ml-64">
                    <?php include $basePath . '/app/views/admin/snippets/top-header.php'; ?>
                    <main class="flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b] overflow-y-auto w-full">
                        <div class="w-full max-w-none">
                            <?php include $viewPath; ?>
                        </div>
                    </main>
                </div>
            </div>
        </div>
        <?php
        include $basePath . '/app/views/admin/snippets/footer.php';
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Liste sayfası
     */
    public function admin_index() {
        $this->requireLogin();
        $timelines = $this->db->fetchAll(
            "SELECT * FROM video_timelines ORDER BY updated_at DESC"
        );
        $this->adminView('index', [
            'title' => 'Video Timeline - Liste',
            'timelines' => $timelines,
        ]);
    }

    /**
     * Yeni timeline oluştur, sonra editöre yönlendir
     */
    public function admin_create() {
        $this->requireLogin();
        $this->ensureTables();
        $userId = $this->getUserId();
        $name = trim($_POST['name'] ?? $_GET['name'] ?? 'Yeni Proje');
        if ($name === '') {
            $name = 'Yeni Proje';
        }
        try {
            $this->db->query(
                "INSERT INTO video_timelines (user_id, name, width, height, fps, duration_sec, background_color) VALUES (?, ?, 1920, 1080, 25, 10.00, '#000000')",
                [$userId, $name]
            );
            $id = $this->db->lastInsertId();
            $id = $id ? (int) $id : 0;
            if ($id > 0) {
                $this->db->query(
                    "INSERT INTO video_timeline_tracks (timeline_id, name, sort_order) VALUES (?, 'Track 1', 0)",
                    [$id]
                );
                header('Location: ' . admin_url('module/video-timeline/editor', ['id' => $id]));
                exit;
            }
        } catch (Exception $e) {
            error_log('VideoTimeline admin_create: ' . $e->getMessage());
        }
        $_SESSION['flash_message'] = 'Timeline oluşturulamadı. Veritabanı tabloları mevcut mu kontrol edin.';
        $_SESSION['flash_type'] = 'error';
        header('Location: ' . admin_url('module/video-timeline/index'));
        exit;
    }

    /**
     * Editör sayfası
     */
    public function admin_editor() {
        $this->requireLogin();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: ' . admin_url('module/video-timeline/index'));
            exit;
        }
        $timeline = $this->db->fetch("SELECT * FROM video_timelines WHERE id = ?", [$id]);
        if (!$timeline) {
            header('Location: ' . admin_url('module/video-timeline/index'));
            exit;
        }
        $tracks = $this->db->fetchAll("SELECT * FROM video_timeline_tracks WHERE timeline_id = ? ORDER BY sort_order ASC", [$id]);
        $clipsByTrack = [];
        foreach ($tracks as $track) {
            $clips = $this->db->fetchAll(
                "SELECT * FROM video_timeline_clips WHERE track_id = ? ORDER BY sort_order ASC, start_time ASC",
                [$track['id']]
            );
            foreach ($clips as &$c) {
                if (!empty($c['content'])) {
                    $c['content'] = is_string($c['content']) ? json_decode($c['content'], true) : $c['content'];
                }
            }
            $clipsByTrack[$track['id']] = $clips;
        }
        $baseUrl = rtrim(admin_url(''), '/');
        $moduleAssetBase = $baseUrl . (strpos($baseUrl, '?') !== false ? '&' : '?') . 'page=module-asset&module=video-timeline&file=';
        $this->adminView('editor', [
            'title' => 'Timeline: ' . htmlspecialchars($timeline['name']),
            'timeline' => $timeline,
            'tracks' => $tracks,
            'clipsByTrack' => $clipsByTrack,
            'moduleAssetBase' => $moduleAssetBase,
            'adminUrl' => admin_url(''),
        ]);
    }

    /**
     * AJAX: Timeline + tracks + clips tam payload kaydet
     */
    public function admin_save_timeline() {
        $this->requireLogin();
        header('Content-Type: application/json; charset=utf-8');
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);
        if (!is_array($payload) || empty($payload['timeline'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Geçersiz payload']);
        }
        $timelineData = $payload['timeline'];
        $tracksData = $payload['tracks'] ?? [];
        $clipsData = $payload['clips'] ?? [];
        $id = isset($timelineData['id']) ? (int) $timelineData['id'] : 0;
        if ($id <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Timeline id gerekli']);
        }
        try {
            $this->db->getConnection()->beginTransaction();
            $this->db->query(
                "UPDATE video_timelines SET name = ?, width = ?, height = ?, fps = ?, duration_sec = ?, background_color = ?, settings = ? WHERE id = ?",
                [
                    $timelineData['name'] ?? 'Proje',
                    (int) ($timelineData['width'] ?? 1920),
                    (int) ($timelineData['height'] ?? 1080),
                    (int) ($timelineData['fps'] ?? 25),
                    (float) ($timelineData['duration_sec'] ?? 10),
                    $timelineData['background_color'] ?? '#000000',
                    isset($timelineData['settings']) ? json_encode($timelineData['settings']) : null,
                    $id,
                ]
            );
            $existingTrackIds = $this->db->fetchAll("SELECT id FROM video_timeline_tracks WHERE timeline_id = ?", [$id]);
            $existingTrackIds = array_column($existingTrackIds, 'id');
            $incomingTrackIds = [];
            foreach ($tracksData as $i => $tr) {
                $trackId = isset($tr['id']) ? (int) $tr['id'] : 0;
                $name = $tr['name'] ?? 'Track';
                $sortOrder = (int) ($tr['sort_order'] ?? $i);
                $isLocked = !empty($tr['is_locked']) ? 1 : 0;
                $isMuted = !empty($tr['is_muted']) ? 1 : 0;
                if ($trackId > 0 && in_array($trackId, $existingTrackIds)) {
                    $this->db->query(
                        "UPDATE video_timeline_tracks SET name = ?, sort_order = ?, is_locked = ?, is_muted = ? WHERE id = ?",
                        [$name, $sortOrder, $isLocked, $isMuted, $trackId]
                    );
                    $incomingTrackIds[] = $trackId;
                } else {
                    $this->db->query(
                        "INSERT INTO video_timeline_tracks (timeline_id, name, sort_order, is_locked, is_muted) VALUES (?, ?, ?, ?, ?)",
                        [$id, $name, $sortOrder, $isLocked, $isMuted]
                    );
                    $incomingTrackIds[] = (int) $this->db->lastInsertId();
                }
            }
            $trackIdMap = [];
            foreach ($tracksData as $i => $tr) {
                $tid = isset($tr['id']) && (int) $tr['id'] > 0 ? (int) $tr['id'] : null;
                if ($tid === null && isset($incomingTrackIds[$i])) {
                    $tid = $incomingTrackIds[$i];
                }
                if ($tid !== null) {
                    $trackIdMap[$i] = $tid;
                }
            }
            foreach ($existingTrackIds as $existingId) {
                if (!in_array($existingId, $incomingTrackIds)) {
                    $this->db->query("DELETE FROM video_timeline_clips WHERE track_id = ?", [$existingId]);
                    $this->db->query("DELETE FROM video_timeline_tracks WHERE id = ?", [$existingId]);
                }
            }
            foreach ($clipsData as $ci => $clip) {
                $trackRef = $clip['track_id'] ?? null;
                if ($trackRef === null && $trackRef !== 0) {
                    continue;
                }
                $trackId = null;
                if (isset($trackIdMap[$trackRef])) {
                    $trackId = $trackIdMap[$trackRef];
                } elseif (in_array((int) $trackRef, $incomingTrackIds)) {
                    $trackId = (int) $trackRef;
                }
                if ($trackId === null) {
                    continue;
                }
                $clipId = isset($clip['id']) ? (int) $clip['id'] : 0;
                $type = in_array($clip['type'] ?? '', ['video', 'image', 'text', 'shape', 'audio']) ? $clip['type'] : 'text';
                $startTime = (float) ($clip['start_time'] ?? 0);
                $duration = (float) ($clip['duration'] ?? 5);
                $sortOrder = (int) ($clip['sort_order'] ?? $ci);
                $source = $clip['source'] ?? null;
                $content = isset($clip['content']) ? (is_array($clip['content']) ? json_encode($clip['content']) : $clip['content']) : null;
                if ($clipId > 0) {
                    $this->db->query(
                        "UPDATE video_timeline_clips SET track_id = ?, type = ?, start_time = ?, duration = ?, sort_order = ?, source = ?, content = ? WHERE id = ?",
                        [$trackId, $type, $startTime, $duration, $sortOrder, $source, $content, $clipId]
                    );
                } else {
                    $this->db->query(
                        "INSERT INTO video_timeline_clips (track_id, type, start_time, duration, sort_order, source, content) VALUES (?, ?, ?, ?, ?, ?, ?)",
                        [$trackId, $type, $startTime, $duration, $sortOrder, $source, $content]
                    );
                }
            }
            $this->db->getConnection()->commit();
            $this->jsonResponse(['success' => true, 'message' => 'Kaydedildi']);
        } catch (Exception $e) {
            if ($this->db->getConnection()->inTransaction()) {
                $this->db->getConnection()->rollBack();
            }
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Tek timeline JSON (tracks + clips dahil) getir
     */
    public function admin_get_timeline() {
        $this->requireLogin();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'id gerekli']);
        }
        $timeline = $this->db->fetch("SELECT * FROM video_timelines WHERE id = ?", [$id]);
        if (!$timeline) {
            $this->jsonResponse(['success' => false, 'message' => 'Timeline bulunamadı']);
        }
        $tracks = $this->db->fetchAll("SELECT * FROM video_timeline_tracks WHERE timeline_id = ? ORDER BY sort_order ASC", [$id]);
        $clipsByTrack = [];
        foreach ($tracks as $track) {
            $clips = $this->db->fetchAll(
                "SELECT * FROM video_timeline_clips WHERE track_id = ? ORDER BY sort_order ASC, start_time ASC",
                [$track['id']]
            );
            foreach ($clips as &$c) {
                if (!empty($c['content'])) {
                    $c['content'] = is_string($c['content']) ? json_decode($c['content'], true) : $c['content'];
                }
            }
            $clipsByTrack[$track['id']] = $clips;
        }
        $this->jsonResponse([
            'success' => true,
            'timeline' => $timeline,
            'tracks' => $tracks,
            'clipsByTrack' => $clipsByTrack,
        ]);
    }

    /**
     * Timeline sil. AJAX ise JSON döner; form POST ise listeye yönlendirir.
     */
    public function admin_delete_timeline() {
        $this->requireLogin();
        $id = isset($_POST['id']) ? (int) $_POST['id'] : (isset($_GET['id']) ? (int) $_GET['id'] : 0);
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($id <= 0) {
            if ($isAjax) {
                $this->jsonResponse(['success' => false, 'message' => 'id gerekli']);
            }
            $_SESSION['flash_message'] = 'Geçersiz istek.';
            $_SESSION['flash_type'] = 'error';
            header('Location: ' . admin_url('module/video-timeline/index'));
            exit;
        }
        try {
            $tracks = $this->db->fetchAll("SELECT id FROM video_timeline_tracks WHERE timeline_id = ?", [$id]);
            foreach ($tracks as $t) {
                $this->db->query("DELETE FROM video_timeline_clips WHERE track_id = ?", [$t['id']]);
            }
            $this->db->query("DELETE FROM video_timeline_tracks WHERE timeline_id = ?", [$id]);
            $this->db->query("DELETE FROM video_timelines WHERE id = ?", [$id]);
            if ($isAjax) {
                $this->jsonResponse(['success' => true, 'message' => 'Silindi']);
            }
            $_SESSION['flash_message'] = 'Timeline silindi.';
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . admin_url('module/video-timeline/index'));
            exit;
        } catch (Exception $e) {
            if ($isAjax) {
                $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
            }
            $_SESSION['flash_message'] = 'Silinirken hata: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'error';
            header('Location: ' . admin_url('module/video-timeline/index'));
            exit;
        }
    }
}
