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
        // Path'i normalize et (baştaki ve sondaki slash'leri kaldır, matchRoute ile uyumlu olması için)
        $normalizedPath = trim($path, '/');
        
        $this->routes[] = [
            'method' => $method,
            'path' => $normalizedPath,
            'handler' => $handler
        ];
    }
    
    /**
     * Mevcut URL'yi parse eder
     */
    private function getCurrentPath() {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Query string'i kaldır
        if (($pos = strpos($path, '?')) !== false) {
            $path = substr($path, 0, $pos);
        }
        
        // Base path'i kaldır
        if ($this->basePath && strpos($path, $this->basePath) === 0) {
            $path = substr($path, strlen($this->basePath));
        }
        
        // /public/ kısmını kaldır (eğer varsa) - daha güvenilir yöntem
        $path = preg_replace('#^/public/#', '/', $path);
        $path = preg_replace('#^public/#', '', $path);
        
        // Başındaki ve sonundaki slash'leri temizle
        $path = trim($path, '/');
        
        // Debug: Orijinal path
        $debugMode = (defined('DEBUG_MODE') && DEBUG_MODE) || (ini_get('display_errors') == 1);
        if ($debugMode && (strpos($path, 'ilanlar') !== false || strpos($path, 'danismanlar') !== false)) {
            error_log("Router getCurrentPath: Original path after trim: '$path'");
        }
        
        // Translation modülü aktifse, URL'deki dil kodunu çıkar (örn: /en/page-slug -> /page-slug)
        if (class_exists('ModuleLoader')) {
            $moduleLoader = ModuleLoader::getInstance();
            $translationModule = $moduleLoader->getModuleController('translation');
            if ($translationModule) {
                $pathParts = explode('/', $path);
                if (!empty($pathParts[0]) && strlen($pathParts[0]) === 2) {
                    // İlk segment dil kodu olabilir, kontrol et
                    require_once __DIR__ . '/../modules/translation/models/TranslationModel.php';
                    $translationModel = new TranslationModel();
                    if ($translationModel->isValidLanguage($pathParts[0])) {
                        $langCode = strtolower($pathParts[0]);
                        
                        // DİL TESPİTİNİ YAP - Router dil kodunu çıkarmadan ÖNCE!
                        // Translation modülünün dil tespitini tetikle ve dil kodunu MANUEL SET ET
                        if (method_exists($translationModule, 'getLanguageService')) {
                            // LanguageService'i al ve dil kodunu set et
                            $languageService = $translationModule->getLanguageService();
                            if ($languageService && method_exists($languageService, 'setCurrentLanguage')) {
                                // Dil kodunu set et - bu cache'i bypass eder
                                $languageService->setCurrentLanguage($langCode);
                                
                                // Debug: Dil set edildi
                                $debugMode = (defined('DEBUG_MODE') && DEBUG_MODE) || (ini_get('display_errors') == 1);
                                if ($debugMode) {
                                    error_log("Router: Language set to '$langCode' via setCurrentLanguage()");
                                }
                            }
                        } elseif (method_exists($translationModule, 'getCurrentLanguage')) {
                            // Fallback: getCurrentLanguage detectLanguage'ı çağıracak
                            $translationModule->getCurrentLanguage();
                        }
                        
                        // Varsayılan dil için redirect yap (örn: /tr/about -> /about)
                        $defaultLang = get_module_setting('translation', 'default_language', 'tr');
                        if ($langCode === $defaultLang) {
                            array_shift($pathParts);
                            $newPath = '/' . implode('/', $pathParts);
                            $newPath = rtrim($newPath, '/') ?: '/';
                            header("Location: " . $newPath, true, 301);
                            exit;
                        }
                        
                        // Dil kodunu çıkar (dil tespiti yapıldıktan SONRA)
                        array_shift($pathParts);
                        
                        // Path'i yeniden oluştur - eğer pathParts boşsa boş string, değilse birleştir
                        if (empty($pathParts)) {
                            $path = '';
                        } else {
                            $path = implode('/', array_filter($pathParts)); // Boş elemanları filtrele
                        }
                        $path = trim($path, '/');
                        
                        // Debug: Dil kodu çıkarıldıktan sonra path (her zaman log'la)
                        error_log("Router: Language code '$langCode' removed, pathParts: " . json_encode($pathParts) . ", new path: '$path'");
                    }
                }
            }
        }
        
        // Path boşsa '/' döndür (ana sayfa için)
        // Ama eğer path boş değilse, path'i döndür
        $finalPath = $path ?: '/';
        
        // Debug: Final path
        $debugMode = (defined('DEBUG_MODE') && DEBUG_MODE) || (ini_get('display_errors') == 1);
        if ($debugMode && (strpos($finalPath, 'ilanlar') !== false || strpos($finalPath, 'danismanlar') !== false || $finalPath === '/')) {
            error_log("Router getCurrentPath: Final path: '$finalPath'");
        }
        
        return $finalPath;
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
        
        // Basit eşleştirme - önce tam eşleşme kontrolü
        if ($routePath === $currentPath) {
            return true;
        }
        
        // Parametreli route desteği (örn: blog/{slug}) - sadece parametreli route'lar için
        if (strpos($routePath, '{') !== false) {
            $routePattern = preg_replace('/\{(\w+)\}/', '([^/]+)', $routePath);
            $routePattern = '#^' . $routePattern . '$#';
            
            if (preg_match($routePattern, $currentPath, $matches)) {
                // Debug: Parametreli route eşleşti
                if (defined('DEBUG_MODE') && DEBUG_MODE) {
                    error_log("Router matchRoute: Pattern '$routePattern' matched path '$currentPath', matches: " . json_encode($matches));
                }
                return $matches;
            }
        }
        
        return false;
    }
    
    /**
     * Routing işlemini başlatır
     */
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $currentPath = $this->getCurrentPath();
        
        // Debug modu kontrolü (sadece geliştirme ortamında)
        $debugMode = (defined('DEBUG_MODE') && DEBUG_MODE) || (ini_get('display_errors') == 1);
        
        // Özel debug: ilanlar ve danismanlar için
        $specialDebug = (strpos($currentPath, 'ilanlar') !== false || strpos($currentPath, 'danismanlar') !== false);
        
        if ($debugMode || $specialDebug) {
            error_log("Router dispatch - Method: $method, Current Path: '$currentPath'");
            error_log("Total routes: " . count($this->routes));
            if ($specialDebug) {
                error_log("Special debug for: $currentPath");
            }
        }
        
        foreach ($this->routes as $index => $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            if ($debugMode) {
                error_log("Checking route #$index: {$route['method']} '{$route['path']}'");
            }
            
            $match = $this->matchRoute($route['path'], $currentPath);
            
            if ($specialDebug || ($debugMode && ($currentPath === 'rezervasyon' || $currentPath === 'ilanlar' || $currentPath === 'danismanlar'))) {
                error_log("  Route #$index: path='" . trim($route['path'], '/') . "', currentPath='" . trim($currentPath, '/') . "'");
                error_log("  Match result: " . ($match ? 'YES' : 'NO'));
                if ($match && is_array($match)) {
                    error_log("  Match params: " . json_encode($match));
                }
            }
            
            if ($match) {
                if ($debugMode || $specialDebug) {
                    error_log("Route matched! Handler: {$route['handler']}");
                }
                
                $handler = $this->parseHandler($route['handler']);
                
                if ($handler) {
                    $controllerName = $handler['controller'];
                    $actionName = $handler['action'];
                    
                    // Controller dosyasını yükle
                    $controllerFile = __DIR__ . '/../app/controllers/' . $controllerName . '.php';
                    
                    if (!file_exists($controllerFile)) {
                        if ($debugMode) {
                            error_log("Controller file not found: $controllerFile");
                        }
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
                        if ($debugMode) {
                            error_log("Controller class not found: $controllerName");
                        }
                        die("Controller sınıfı bulunamadı: {$controllerName}");
                    }
                    
                    // Controller instance oluştur ve action'ı çalıştır
                    $controller = new $controllerName();
                    
                    if (!method_exists($controller, $actionName)) {
                        if ($debugMode) {
                            error_log("Action method not found: $controllerName::$actionName");
                        }
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
        if ($debugMode) {
            error_log("No route matched for: $method $currentPath");
        }
        http_response_code(404);
        
        // ViewRenderer ve Layout kullan
        require_once __DIR__ . '/ViewRenderer.php';
        $renderer = ViewRenderer::getInstance();
        $renderer->setLayout('default');
        $renderer->render('frontend/404', ['title' => 'Sayfa Bulunamadı', 'current_page' => '404']);
    }
}

