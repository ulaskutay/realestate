<?php
/**
 * Router Sınıfı
 * URL routing ve controller yönlendirme işlemlerini yönetir
 */

class Router {
    private $routes = [];
    private $basePath = '';
    
    public function __construct($basePath = '') {
        $this->basePath = $basePath;
    }
    
    /**
     * GET route ekler
     */
    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
    }
    
    /**
     * POST route ekler
     */
    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
    }
    
    /**
     * Route ekler
     */
    private function addRoute($method, $path, $handler) {
        $this->routes[] = [
            'method' => $method,
            'path' => $this->basePath . $path,
            'handler' => $handler
        ];
    }
    
    /**
     * Mevcut URL'yi parse eder
     */
    private function getCurrentPath() {
        $path = $_SERVER['REQUEST_URI'];
        
        // Query string'i kaldır
        if (($pos = strpos($path, '?')) !== false) {
            $path = substr($path, 0, $pos);
        }
        
        // Base path'i kaldır
        if ($this->basePath && strpos($path, $this->basePath) === 0) {
            $path = substr($path, strlen($this->basePath));
        }
        
        // Başındaki ve sonundaki slash'leri temizle
        $path = trim($path, '/');
        
        return $path ?: '/';
    }
    
    /**
     * Route'u controller ve action'a çevirir
     */
    private function parseHandler($handler) {
        if (is_string($handler) && strpos($handler, '@') !== false) {
            list($controller, $action) = explode('@', $handler);
            return [
                'controller' => $controller,
                'action' => $action
            ];
        }
        
        return null;
    }
    
    /**
     * Path'i route pattern ile eşleştirir
     */
    private function matchRoute($routePath, $currentPath) {
        // Path'leri normalize et (baştaki ve sondaki slash'leri kaldır)
        $routePath = trim($routePath, '/');
        $currentPath = trim($currentPath, '/');
        
        // Ana sayfa kontrolü
        if (($routePath === '' || $routePath === '/') && ($currentPath === '' || $currentPath === '/')) {
            return true;
        }
        
        // Basit eşleştirme
        if ($routePath === $currentPath) {
            return true;
        }
        
        // Parametreli route desteği (örn: blog/{slug})
        $routePattern = preg_replace('/\{(\w+)\}/', '([^/]+)', $routePath);
        $routePattern = '#^' . $routePattern . '$#';
        
        if (preg_match($routePattern, $currentPath, $matches)) {
            return $matches;
        }
        
        return false;
    }
    
    /**
     * Routing işlemini başlatır
     */
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $currentPath = $this->getCurrentPath();
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            $match = $this->matchRoute($route['path'], $currentPath);
            
            if ($match) {
                $handler = $this->parseHandler($route['handler']);
                
                if ($handler) {
                    $controllerName = $handler['controller'];
                    $actionName = $handler['action'];
                    
                    // Controller dosyasını yükle
                    $controllerFile = __DIR__ . '/../app/controllers/' . $controllerName . '.php';
                    
                    if (!file_exists($controllerFile)) {
                        die("Controller bulunamadı: {$controllerName}");
                    }
                    
                    // FormController için model'leri yükle
                    if ($controllerName === 'FormController') {
                        require_once __DIR__ . '/../app/models/Form.php';
                        require_once __DIR__ . '/../app/models/FormField.php';
                        require_once __DIR__ . '/../app/models/FormSubmission.php';
                    }
                    
                    require $controllerFile;
                    
                    // Controller sınıfını kontrol et
                    if (!class_exists($controllerName)) {
                        die("Controller sınıfı bulunamadı: {$controllerName}");
                    }
                    
                    // Controller instance oluştur ve action'ı çalıştır
                    $controller = new $controllerName();
                    
                    if (!method_exists($controller, $actionName)) {
                        die("Action bulunamadı: {$controllerName}::{$actionName}");
                    }
                    
                    // Parametreleri hazırla
                    $params = is_array($match) ? array_slice($match, 1) : [];
                    
                    call_user_func_array([$controller, $actionName], $params);
                    return;
                }
            }
        }
        
        // Route bulunamadı - 404 sayfası göster
        http_response_code(404);
        
        // ViewRenderer ve Layout kullan
        require_once __DIR__ . '/ViewRenderer.php';
        $renderer = ViewRenderer::getInstance();
        $renderer->setLayout('default');
        $renderer->render('frontend/404', ['title' => 'Sayfa Bulunamadı', 'current_page' => '404']);
    }
}

