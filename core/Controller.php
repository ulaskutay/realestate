<?php
/**
 * Base Controller Sınıfı
 * Tüm controller'lar bu sınıftan türeyecek
 */

class Controller {
    /**
     * View dosyasını render eder (PHP component system)
     */
    protected function view($viewName, $data = []) {
        require_once __DIR__ . '/ViewRenderer.php';
        $renderer = ViewRenderer::getInstance();
        $renderer->render($viewName, $data);
    }
    
    /**
     * Component render eder
     */
    protected function component($componentName, $data = []) {
        require_once __DIR__ . '/ViewRenderer.php';
        $renderer = ViewRenderer::getInstance();
        $renderer->component($componentName, $data);
    }
    
    /**
     * Snippet render eder
     */
    protected function snippet($snippetName, $data = []) {
        require_once __DIR__ . '/ViewRenderer.php';
        $renderer = ViewRenderer::getInstance();
        $renderer->snippet($snippetName, $data);
    }
    
    /**
     * JSON response döndürür
     */
    protected function json($data, $statusCode = 200) {
        // Tüm output buffer'ları temizle (JSON'dan önce hiçbir şey yazdırılmamalı)
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Önceki çıktıları temizle
        if (ob_get_length() > 0) {
            ob_clean();
        }
        
        // Hata gösterimini kapat (JSON yanıtında hata göstermemek için)
        $oldDisplayErrors = ini_get('display_errors');
        ini_set('display_errors', 0);
        
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        // JSON encode işlemini yap
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        // JSON encode hatası kontrolü
        if ($json === false) {
            $json = json_encode([
                'success' => false,
                'message' => 'JSON encoding error: ' . json_last_error_msg()
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        
        echo $json;
        
        // Eski ayarı geri yükle
        ini_set('display_errors', $oldDisplayErrors);
        
        exit;
    }
    
    /**
     * Yönlendirme yapar
     */
    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }
    
    /**
     * XSS koruması için string temizler
     */
    protected function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

