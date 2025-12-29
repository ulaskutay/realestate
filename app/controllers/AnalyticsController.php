<?php
/**
 * Analytics API Controller
 * Frontend tracking verilerini alan API endpoint
 */

class AnalyticsController extends Controller {
    
    private $analytics;
    
    public function __construct() {
        require_once __DIR__ . '/../models/Analytics.php';
        $this->analytics = new Analytics();
    }
    
    /**
     * Sayfa görüntülenmesini kaydet
     * POST /api/track
     */
    public function track() {
        // Debug log
        error_log('========== ANALYTICS TRACK START ==========');
        error_log('Method: ' . $_SERVER['REQUEST_METHOD']);
        error_log('URL: ' . $_SERVER['REQUEST_URI']);
        
        // CORS headers
        $this->setCorsHeaders();
        
        // POST isteği kontrolü
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log('Error: Method not POST');
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        // JSON veriyi al
        $input = file_get_contents('php://input');
        error_log('Raw input: ' . $input);
        
        $data = json_decode($input, true);
        error_log('Decoded data: ' . print_r($data, true));
        
        if (!$data) {
            error_log('Error: Invalid JSON');
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            return;
        }
        
        // Gerekli alanları kontrol et
        if (empty($data['page_url'])) {
            error_log('Error: Missing page_url');
            http_response_code(400);
            echo json_encode(['error' => 'Missing page_url']);
            return;
        }
        
        // Tracking verisini kaydet
        try {
            $result = $this->analytics->trackPageView([
                'page_url' => $data['page_url'],
                'page_title' => $data['page_title'] ?? null,
                'referrer' => $data['referrer'] ?? null,
                'user_agent' => $data['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
            error_log('Track result: ' . ($result ? 'Success (ID: ' . $result . ')' : 'Failed'));
            
            if ($result) {
                http_response_code(200);
                echo json_encode(['success' => true, 'id' => $result]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to save tracking data']);
            }
        } catch (Exception $e) {
            error_log('Analytics error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
        
        error_log('========== ANALYTICS TRACK END ==========');
    }
    
    /**
     * Ziyaret süresini güncelle
     * POST /api/track/duration
     */
    public function trackDuration() {
        // CORS headers
        $this->setCorsHeaders();
        
        // POST isteği kontrolü
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        // JSON veriyi al
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!$data || empty($data['duration'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data']);
            return;
        }
        
        // Son kaydı bul ve güncelle
        try {
            // Analytics modelini kullan
            $db = Database::getInstance();
            $lastView = $db->fetch(
                "SELECT id FROM page_views 
                 WHERE page_url = ? 
                 ORDER BY created_at DESC 
                 LIMIT 1",
                [$data['page_url'] ?? '']
            );
            
            if ($lastView) {
                $this->analytics->updateVisitDuration($lastView['id'], $data['duration']);
                http_response_code(200);
                echo json_encode(['success' => true]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Record not found']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * CORS headers ayarla
     */
    private function setCorsHeaders() {
        // Sadece kendi domain'inden izin ver
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowedOrigin = $_SERVER['HTTP_HOST'] ?? '';
        
        if (strpos($origin, $allowedOrigin) !== false) {
            header('Access-Control-Allow-Origin: ' . $origin);
        }
        
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Content-Type: application/json');
        
        // OPTIONS isteği için
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}

