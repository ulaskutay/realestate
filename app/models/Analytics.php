<?php
/**
 * Analytics Model
 * Privacy-friendly page view tracking sistemi
 */

class Analytics extends Model {
    protected $table = 'page_views';
    
    /**
     * Yeni sayfa görüntülenmesi kaydet
     */
    public function trackPageView($data) {
        error_log('========== ANALYTICS MODEL trackPageView ==========');
        error_log('Input data: ' . print_r($data, true));
        
        // Session hash oluştur (IP + User-Agent hash - kişisel veri saklanmaz)
        $sessionHash = $this->generateSessionHash();
        error_log('Session hash: ' . $sessionHash);
        
        // Bot kontrolü
        $isBot = $this->detectBot($data['user_agent'] ?? '');
        error_log('Is bot: ' . ($isBot ? 'YES' : 'NO'));
        
        // Cihaz tipi tespiti
        $deviceType = $this->detectDeviceType($data['user_agent'] ?? '');
        error_log('Device type: ' . $deviceType);
        
        $viewData = [
            'session_hash' => $sessionHash,
            'page_url' => $this->sanitizeUrl($data['page_url'] ?? ''),
            'page_title' => $data['page_title'] ?? null,
            'referrer' => $this->sanitizeUrl($data['referrer'] ?? ''),
            'user_agent' => substr($data['user_agent'] ?? '', 0, 500),
            'device_type' => $deviceType,
            'is_bot' => $isBot ? 1 : 0,
            'visit_duration' => 0
        ];
        
        error_log('View data to save: ' . print_r($viewData, true));
        
        try {
            $result = $this->create($viewData);
            error_log('Create result: ' . ($result ? $result : 'FAILED'));
            error_log('========== ANALYTICS MODEL trackPageView END ==========');
            return $result;
        } catch (Exception $e) {
            error_log('ERROR in trackPageView: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }
    
    /**
     * Ziyaret süresini güncelle
     */
    public function updateVisitDuration($id, $duration) {
        return $this->update($id, ['visit_duration' => (int)$duration]);
    }
    
    /**
     * Bugünkü toplam görüntülenme
     */
    public function getTodayViews() {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE DATE(created_at) = CURDATE() 
                AND is_bot = 0";
        $result = $this->db->fetch($sql);
        return $result['count'] ?? 0;
    }
    
    /**
     * Bugünkü benzersiz ziyaretçiler
     */
    public function getTodayUniqueVisitors() {
        $sql = "SELECT COUNT(DISTINCT session_hash) as count 
                FROM {$this->table} 
                WHERE DATE(created_at) = CURDATE() 
                AND is_bot = 0";
        $result = $this->db->fetch($sql);
        return $result['count'] ?? 0;
    }
    
    /**
     * Bu ayki toplam görüntülenme
     */
    public function getMonthViews() {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE YEAR(created_at) = YEAR(CURDATE()) 
                AND MONTH(created_at) = MONTH(CURDATE()) 
                AND is_bot = 0";
        $result = $this->db->fetch($sql);
        return $result['count'] ?? 0;
    }
    
    /**
     * Bu ayki benzersiz ziyaretçiler
     */
    public function getMonthUniqueVisitors() {
        $sql = "SELECT COUNT(DISTINCT session_hash) as count 
                FROM {$this->table} 
                WHERE YEAR(created_at) = YEAR(CURDATE()) 
                AND MONTH(created_at) = MONTH(CURDATE()) 
                AND is_bot = 0";
        $result = $this->db->fetch($sql);
        return $result['count'] ?? 0;
    }
    
    /**
     * En çok ziyaret edilen sayfalar
     */
    public function getTopPages($limit = 10, $days = 30) {
        // URL bazında group by yap, en son kullanılan title'ı al
        $sql = "SELECT 
                    page_url, 
                    MAX(page_title) as page_title,
                    COUNT(*) as views 
                FROM {$this->table} 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) 
                AND is_bot = 0 
                AND page_url != '' 
                GROUP BY page_url 
                ORDER BY views DESC 
                LIMIT ?";
        $results = $this->db->fetchAll($sql, [$days, (int)$limit]);
        
        // Boş title'ları temizle
        foreach ($results as &$result) {
            if (empty($result['page_title']) || $result['page_title'] === 'null') {
                // URL'den path al ve güzelleştir
                $path = parse_url($result['page_url'], PHP_URL_PATH);
                $path = trim($path, '/');
                $result['page_title'] = !empty($path) ? ucfirst(str_replace(['-', '_'], ' ', $path)) : 'Ana Sayfa';
            }
        }
        
        return $results;
    }
    
    /**
     * Referrer istatistikleri
     */
    public function getTopReferrers($limit = 10, $days = 30) {
        $sql = "SELECT referrer, COUNT(*) as count 
                FROM {$this->table} 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) 
                AND referrer IS NOT NULL 
                AND referrer != '' 
                AND is_bot = 0 
                GROUP BY referrer 
                ORDER BY count DESC 
                LIMIT ?";
        return $this->db->fetchAll($sql, [$days, (int)$limit]);
    }
    
    /**
     * Cihaz dağılımı
     */
    public function getDeviceDistribution($days = 30) {
        $sql = "SELECT device_type, COUNT(*) as count 
                FROM {$this->table} 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) 
                AND is_bot = 0 
                GROUP BY device_type 
                ORDER BY count DESC";
        return $this->db->fetchAll($sql, [$days]);
    }
    
    /**
     * Son 7 günlük günlük istatistikler
     */
    public function getLast7DaysStats() {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as views,
                    COUNT(DISTINCT session_hash) as unique_visitors
                FROM {$this->table} 
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                AND is_bot = 0 
                GROUP BY DATE(created_at) 
                ORDER BY date DESC";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Ortalama sayfa kalma süresi
     */
    public function getAverageVisitDuration($days = 30) {
        $sql = "SELECT AVG(visit_duration) as avg_duration 
                FROM {$this->table} 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) 
                AND visit_duration > 0 
                AND is_bot = 0";
        $result = $this->db->fetch($sql, [$days]);
        return round($result['avg_duration'] ?? 0);
    }
    
    /**
     * Canlı ziyaretçiler (son 5 dakika)
     */
    public function getLiveVisitors() {
        $sql = "SELECT COUNT(DISTINCT session_hash) as count 
                FROM {$this->table} 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) 
                AND is_bot = 0";
        $result = $this->db->fetch($sql);
        
        // Debug için kayıt sayısını da kontrol et
        $totalRecent = $this->db->fetch("SELECT COUNT(*) as count FROM {$this->table} WHERE created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
        error_log("Live visitors: " . ($result['count'] ?? 0) . " unique out of " . ($totalRecent['count'] ?? 0) . " total");
        
        return $result['count'] ?? 0;
    }
    
    /**
     * Dashboard için özet istatistikler
     */
    public function getDashboardStats() {
        return [
            'today_views' => $this->getTodayViews(),
            'today_unique' => $this->getTodayUniqueVisitors(),
            'month_views' => $this->getMonthViews(),
            'month_unique' => $this->getMonthUniqueVisitors(),
            'live_visitors' => $this->getLiveVisitors(),
            'avg_duration' => $this->getAverageVisitDuration(30),
            'top_pages' => $this->getTopPages(5, 7),
            'device_distribution' => $this->getDeviceDistribution(30)
        ];
    }
    
    // ==================== HELPER METHODS ====================
    
    /**
     * Anonim session hash oluştur (Privacy-friendly)
     */
    public function generateSessionHash() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $date = date('Y-m-d'); // Günlük değişir
        $hour = date('H'); // Saatlik unique olsun
        
        // IP'yi hash'le (orijinal IP saklanmaz)
        $ipHash = hash('sha256', $ip . $date . $hour);
        
        // Her saat için farklı hash (daha gerçekçi unique visitor)
        return hash('sha256', $ipHash . $userAgent . $hour);
    }
    
    /**
     * Bot tespiti
     */
    private function detectBot($userAgent) {
        $botPatterns = [
            'bot', 'crawl', 'spider', 'slurp', 'wordpress', 'mediapartners',
            'adsbot', 'googlebot', 'bingbot', 'yandex', 'baidu', 'duckduck',
            'facebook', 'twitter', 'linkedin', 'pinterest', 'slack', 'telegram'
        ];
        
        $userAgent = strtolower($userAgent);
        foreach ($botPatterns as $pattern) {
            if (strpos($userAgent, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Cihaz tipi tespiti
     */
    private function detectDeviceType($userAgent) {
        $userAgent = strtolower($userAgent);
        
        // Tablet kontrolü
        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobile))/i', $userAgent)) {
            return 'tablet';
        }
        
        // Mobil kontrolü
        if (preg_match('/(mobile|phone|ipod|blackberry|android.*mobile|windows.*phone)/i', $userAgent)) {
            return 'mobile';
        }
        
        return 'desktop';
    }
    
    /**
     * URL'yi temizle, normalize et ve kısalt
     * Aynı sayfanın farklı varyasyonlarını birleştir
     */
    private function sanitizeUrl($url) {
        if (empty($url)) {
            return null;
        }
        
        // URL'yi temizle
        $url = filter_var($url, FILTER_SANITIZE_URL);
        
        // Parse URL
        $parsed = parse_url($url);
        
        if (!$parsed || !isset($parsed['host'])) {
            // Geçersiz URL, olduğu gibi döndür
            return substr($url, 0, 500);
        }
        
        // Normalize et
        $normalizedUrl = '';
        
        // Scheme (her zaman lowercase)
        if (isset($parsed['scheme'])) {
            $normalizedUrl .= strtolower($parsed['scheme']) . '://';
        }
        
        // Host (her zaman lowercase, www. kaldır)
        if (isset($parsed['host'])) {
            $host = strtolower($parsed['host']);
            // www. prefix'ini kaldır (www.example.com = example.com)
            $host = preg_replace('/^www\./', '', $host);
            $normalizedUrl .= $host;
        }
        
        // Port (sadece standart değilse ekle)
        if (isset($parsed['port'])) {
            $scheme = $parsed['scheme'] ?? 'http';
            $defaultPort = ($scheme === 'https') ? 443 : 80;
            if ($parsed['port'] != $defaultPort) {
                $normalizedUrl .= ':' . $parsed['port'];
            }
        }
        
        // Path (trailing slash kaldır, boş path'i / yap)
        $path = $parsed['path'] ?? '/';
        $path = rtrim($path, '/'); // Trailing slash kaldır
        if (empty($path)) {
            $path = '/'; // Ana sayfa için
        }
        $normalizedUrl .= $path;
        
        // Query string (alfabetik sırala)
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $queryParams);
            ksort($queryParams);
            $normalizedUrl .= '?' . http_build_query($queryParams);
        }
        
        // Fragment (#hash) tracking için gereksiz, eklemiyoruz
        
        // Maksimum 500 karakter
        return substr($normalizedUrl, 0, 500);
    }
    
    /**
     * Eski kayıtları temizle (Cron job ile çalıştırılabilir)
     */
    public function cleanupOldRecords($days = 90) {
        $sql = "DELETE FROM {$this->table} WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        return $this->db->query($sql, [$days]);
    }
}

