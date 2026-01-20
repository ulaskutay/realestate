<?php
/**
 * Emlakjet API Service
 * Emlakjet API ile iletişim servisi
 */

class EmlakjetApiService {
    private $apiKey;
    private $apiSecret;
    private $apiUrl;
    private $timeout = 30;
    private $retryAttempts = 3;
    private $retryDelay = 5;
    private $logFile;
    private $testMode = false;
    private $mockService = null;
    
    public function __construct($settings) {
        $this->apiKey = $settings['api_key'] ?? '';
        $this->apiSecret = $settings['api_secret'] ?? '';
        $this->apiUrl = rtrim($settings['api_url'] ?? 'https://api.emlakjet.com/v1', '/');
        $this->retryAttempts = $settings['retry_attempts'] ?? 3;
        $this->retryDelay = $settings['retry_delay'] ?? 5;
        $this->testMode = $settings['test_mode'] ?? false;
        
        // Log dosyası yolu
        $logDir = dirname(dirname(dirname(__DIR__))) . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $this->logFile = $logDir . '/emlakjet.log';
        
        // Test modu aktifse mock servisi yükle
        if ($this->testMode) {
            require_once __DIR__ . '/MockEmlakjetApiService.php';
            $this->mockService = new MockEmlakjetApiService();
            $this->log("Test mode enabled - Using Mock API Service");
        }
    }
    
    /**
     * API bağlantısını doğrula
     */
    public function authenticate() {
        // Test modu aktifse mock servisi kullan
        if ($this->testMode && $this->mockService) {
            return $this->mockService->authenticate();
        }
        
        if (empty($this->apiKey) || empty($this->apiSecret)) {
            throw new Exception('API anahtarları yapılandırılmamış');
        }
        
        // Basit bir endpoint ile bağlantıyı test et
        $response = $this->makeRequest('GET', '/auth/verify');
        
        return isset($response['success']) && $response['success'] === true;
    }
    
    /**
     * Yeni ilan oluştur
     */
    public function createListing($listingData) {
        // Test modu aktifse mock servisi kullan
        if ($this->testMode && $this->mockService) {
            return $this->mockService->createListing($listingData);
        }
        
        return $this->makeRequestWithRetry('POST', '/listings', $listingData);
    }
    
    /**
     * İlan güncelle
     */
    public function updateListing($emlakjetId, $listingData) {
        // Test modu aktifse mock servisi kullan
        if ($this->testMode && $this->mockService) {
            return $this->mockService->updateListing($emlakjetId, $listingData);
        }
        
        return $this->makeRequestWithRetry('PUT', "/listings/{$emlakjetId}", $listingData);
    }
    
    /**
     * İlan sil
     */
    public function deleteListing($emlakjetId) {
        // Test modu aktifse mock servisi kullan
        if ($this->testMode && $this->mockService) {
            return $this->mockService->deleteListing($emlakjetId);
        }
        
        return $this->makeRequestWithRetry('DELETE', "/listings/{$emlakjetId}");
    }
    
    /**
     * İlanları getir
     */
    public function getListings($filters = []) {
        // Test modu aktifse mock servisi kullan
        if ($this->testMode && $this->mockService) {
            return $this->mockService->getListings($filters);
        }
        
        $queryString = '';
        if (!empty($filters)) {
            $queryString = '?' . http_build_query($filters);
        }
        
        return $this->makeRequestWithRetry('GET', "/listings{$queryString}");
    }
    
    /**
     * Tek ilan detayı
     */
    public function getListing($emlakjetId) {
        // Test modu aktifse mock servisi kullan
        if ($this->testMode && $this->mockService) {
            return $this->mockService->getListing($emlakjetId);
        }
        
        return $this->makeRequestWithRetry('GET', "/listings/{$emlakjetId}");
    }
    
    /**
     * Retry mekanizması ile istek yap
     */
    private function makeRequestWithRetry($method, $endpoint, $data = null) {
        $lastError = null;
        
        for ($attempt = 1; $attempt <= $this->retryAttempts; $attempt++) {
            try {
                $response = $this->makeRequest($method, $endpoint, $data);
                
                // Başarılı yanıt
                if ($response !== false && !isset($response['error'])) {
                    return $response;
                }
                
                // Retry edilebilir hatalar
                $error = $response['error'] ?? 'Unknown error';
                $httpCode = $response['http_code'] ?? 0;
                
                // 5xx hataları retry edilebilir
                if ($httpCode >= 500 && $httpCode < 600) {
                    $lastError = $error;
                    if ($attempt < $this->retryAttempts) {
                        $this->log("Retry attempt {$attempt} for {$method} {$endpoint}: {$error}");
                        sleep($this->retryDelay);
                        continue;
                    }
                } else {
                    // 4xx hataları retry edilmez
                    return $response;
                }
                
            } catch (Exception $e) {
                $lastError = $e->getMessage();
                
                // Network hataları retry edilebilir
                if ($attempt < $this->retryAttempts) {
                    $this->log("Retry attempt {$attempt} for {$method} {$endpoint}: {$lastError}");
                    sleep($this->retryDelay);
                    continue;
                }
            }
        }
        
        // Tüm denemeler başarısız
        return [
            'success' => false,
            'error' => $lastError ?? 'Request failed after ' . $this->retryAttempts . ' attempts',
            'http_code' => 0
        ];
    }
    
    /**
     * API isteği yap
     */
    private function makeRequest($method, $endpoint, $data = null) {
        if (empty($this->apiKey) || empty($this->apiSecret)) {
            throw new Exception('API anahtarları yapılandırılmamış');
        }
        
        $url = $this->apiUrl . $endpoint;
        
        $ch = curl_init($url);
        
        // Temel curl ayarları
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->getAuthToken(),
                'X-API-Key: ' . $this->apiKey
            ]
        ]);
        
        // Method'a göre ayarlar
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data !== null) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
                }
                break;
                
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data !== null) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
                }
                break;
                
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
                
            case 'GET':
            default:
                // GET için ek ayar gerekmez
                break;
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // CURL hatası
        if ($error) {
            $this->log("CURL Error for {$method} {$endpoint}: {$error}");
            throw new Exception("API bağlantı hatası: {$error}");
        }
        
        // HTTP hata kodu kontrolü
        if ($httpCode < 200 || $httpCode >= 300) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? $errorData['message'] ?? "HTTP {$httpCode}";
            
            $this->log("API Error for {$method} {$endpoint}: HTTP {$httpCode} - {$errorMessage}");
            
            return [
                'success' => false,
                'error' => $errorMessage,
                'http_code' => $httpCode,
                'data' => $errorData
            ];
        }
        
        // Başarılı yanıt
        $responseData = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->log("JSON Parse Error for {$method} {$endpoint}: " . json_last_error_msg());
            throw new Exception("API yanıtı parse edilemedi");
        }
        
        return $responseData;
    }
    
    /**
     * Auth token al (API Secret ile imza oluştur)
     */
    private function getAuthToken() {
        // Basit token oluşturma (Emlakjet API dokümantasyonuna göre güncellenebilir)
        $timestamp = time();
        $nonce = uniqid();
        $signature = hash_hmac('sha256', $timestamp . $nonce, $this->apiSecret);
        
        return base64_encode(json_encode([
            'api_key' => $this->apiKey,
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'signature' => $signature
        ]));
    }
    
    /**
     * Log yaz
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";
        @file_put_contents($this->logFile, $logMessage, FILE_APPEND);
        error_log("Emlakjet API: {$message}");
    }
    
    /**
     * Rate limiting kontrolü (basit implementasyon)
     */
    public function checkRateLimit() {
        // Bu metod, gerekirse rate limiting kontrolü yapabilir
        // Şimdilik boş bırakıyoruz, gelecekte geliştirilebilir
        return true;
    }
}
