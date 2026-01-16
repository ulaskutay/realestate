<?php
/**
 * Module Loader - Modül Yükleyici ve Yönetici
 * 
 * WordPress tarzı modül sistemi için ana yükleyici sınıf.
 * Modülleri tarar, yükler ve yönetir.
 */

class ModuleLoader {
    private static $instance = null;
    
    /**
     * Modüller dizini yolu
     * @var string
     */
    private $modules_dir;
    
    /**
     * Yüklenen modüller
     * @var array
     */
    private $loaded_modules = [];
    
    /**
     * Aktif modüller (veritabanından)
     * @var array
     */
    private $active_modules = [];
    
    /**
     * Tüm bulunan modüller
     * @var array
     */
    private $all_modules = [];
    
    /**
     * Tema modülleri
     * @var array
     */
    private $theme_modules = [];
    
    /**
     * Modül route'ları
     * @var array
     */
    private $routes = [
        'admin' => [],
        'frontend' => []
    ];
    
    /**
     * Admin menü öğeleri
     * @var array
     */
    private $admin_menus = [];
    
    /**
     * Widget'lar
     * @var array
     */
    private $widgets = [];
    
    /**
     * Veritabanı bağlantısı
     * @var Database
     */
    private $db;
    
    /**
     * Singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Modül dizini: core/ dizininden bir seviye yukarı çık (core -> public_html), sonra /modules ekle
        // __DIR__ = /home/codeticc/public_html/core
        // __DIR__ . '/../modules' = /home/codeticc/public_html/modules
        $modulesPath = __DIR__ . '/../modules';
        
        // Real path ile doğrula (symlink'ler için)
        $realPath = realpath($modulesPath);
        if ($realPath && is_dir($realPath)) {
            $this->modules_dir = $realPath;
        } else {
            // Real path bulunamazsa, normalize et (../ işaretlerini çöz)
            $normalized = realpath(dirname(__DIR__)) . '/modules';
            if ($normalized && is_dir($normalized)) {
                $this->modules_dir = $normalized;
            } else {
                // Son çare: dirname ile dene
                $this->modules_dir = dirname(__DIR__) . '/modules';
            }
        }
        
        // Modül dizini yoksa oluştur
        if (!is_dir($this->modules_dir)) {
            mkdir($this->modules_dir, 0755, true);
        }
        
        // Veritabanı bağlantısını al
        require_once __DIR__ . '/Database.php';
        $this->db = Database::getInstance();
    }
    
    private function __clone() {}
    
    /**
     * Tüm modülleri başlat
     */
    public function init() {
        // Hook sistemini yükle
        require_once __DIR__ . '/HookSystem.php';
        
        // Aktif modülleri veritabanından al
        $this->loadActiveModulesFromDB();
        
        // Modülleri tara
        $this->scanModules();
        
        // Aktif modülleri yükle
        $this->loadActiveModules();
        
        // init hook'unu tetikle
        do_action('modules_loaded');
    }
    
    /**
     * Tema modüllerini yükle
     */
    public function loadThemeModules($themePath) {
        // Önce eski tema modüllerini temizle (sadece tema modülleri)
        $modulesToUnload = [];
        foreach ($this->loaded_modules as $name => $loadedModule) {
            if (isset($loadedModule['info']['is_theme_module'])) {
                $modulesToUnload[] = $name;
            }
        }
        
        // Eski tema modüllerini deaktive et ve temizle
        foreach ($modulesToUnload as $name) {
            // Route'ları temizle
            foreach ($this->routes as $type => &$routes) {
                $this->routes[$type] = array_filter($routes, function($route) use ($name) {
                    return $route['module'] !== $name;
                });
            }
            
            // Yüklenen modüllerden kaldır
            unset($this->loaded_modules[$name]);
        }
        
        // Tema modüllerini tara
        $this->theme_modules = $this->scanThemeModules($themePath);
        
        // Tema modüllerini all_modules'e ekle (öncelikli - override eder)
        foreach ($this->theme_modules as $name => $module) {
            $this->all_modules[$name] = $module;
        }
        
        // Aktif tema modüllerini yükle
        foreach ($this->theme_modules as $name => $module) {
            // Tema modülü otomatik aktif edilir
            // Önce veritabanında aktif olarak işaretle
            try {
                $existing = $this->db->fetch(
                    "SELECT id FROM modules WHERE slug = ?",
                    [$name]
                );
                
                $isSystem = isset($module['is_system']) ? (int)$module['is_system'] : 0;
                
                if ($existing) {
                    $this->db->query(
                        "UPDATE modules SET is_active = 1, is_system = ?, updated_at = NOW() WHERE slug = ?",
                        [$isSystem, $name]
                    );
                } else {
                    $this->db->query(
                        "INSERT INTO modules (name, slug, label, description, icon, version, author, path, is_active, is_system, installed_at, created_at) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?, NOW(), NOW())",
                        [
                            $module['name'],
                            $module['name'],
                            $module['title'],
                            $module['description'] ?? '',
                            $module['admin_menu']['icon'] ?? 'extension',
                            $module['version'],
                            $module['author'] ?? '',
                            $module['path'],
                            $isSystem
                        ]
                    );
                }
                
                // active_modules dizisine ekle
                $this->active_modules[$name] = [
                    'slug' => $name,
                    'is_active' => 1
                ];
            } catch (Exception $e) {
                error_log("Error activating theme module {$name}: " . $e->getMessage());
            }
            
            // Modülü yükle
            $result = $this->loadModule($module);
            if (!$result) {
                error_log("Failed to load theme module: {$name} from {$module['path']}");
            } else {
                error_log("Successfully loaded theme module: {$name}");
            }
        }
    }
    
    /**
     * Eski temanın modüllerini deaktive et
     */
    public function unloadThemeModules($oldThemePath) {
        $oldThemeModules = $this->scanThemeModules($oldThemePath);
        
        foreach ($oldThemeModules as $name => $module) {
            // Sadece tema modülü ise deaktive et
            if (isset($this->loaded_modules[$name]) && 
                isset($this->loaded_modules[$name]['info']['is_theme_module'])) {
                $this->deactivateModule($name);
            }
        }
    }
    
    /**
     * Veritabanından aktif modülleri yükle
     */
    private function loadActiveModulesFromDB() {
        try {
            $result = $this->db->fetchAll("SELECT * FROM modules WHERE is_active = 1");
            foreach ($result as $module) {
                $this->active_modules[$module['slug']] = $module;
            }
        } catch (Exception $e) {
            // Tablo yoksa sessizce devam et
            $this->active_modules = [];
        }
    }
    
    /**
     * Tema modüllerini tarar
     */
    public function scanThemeModules($themePath) {
        $themeModulesDir = $themePath . '/modules';
        
        if (!is_dir($themeModulesDir)) {
            return [];
        }
        
        $themeModules = [];
        $directories = scandir($themeModulesDir);
        
        foreach ($directories as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }
            
            $module_path = $themeModulesDir . '/' . $dir;
            $manifest_file = $module_path . '/module.json';
            
            if (!is_dir($module_path) || !file_exists($manifest_file)) {
                continue;
            }
            
            // Manifest'i oku
            $manifest = $this->readManifest($manifest_file);
            
            if ($manifest) {
                $manifest['path'] = $module_path;
                $manifest['dir'] = $dir;
                $manifest['is_theme_module'] = true;
                $manifest['theme_path'] = $themePath;
                
                // Veritabanındaki bilgilerle birleştir
                if (isset($this->active_modules[$manifest['name']])) {
                    $manifest['db_info'] = $this->active_modules[$manifest['name']];
                    $manifest['is_active'] = true;
                } else {
                    $manifest['is_active'] = $this->isModuleActiveInDB($manifest['name']);
                }
                
                $themeModules[$manifest['name']] = $manifest;
            }
        }
        
        return $themeModules;
    }
    
    /**
     * Modül dizinini tarar ve modülleri bulur
     */
    public function scanModules() {
        $this->all_modules = [];
        
        if (!is_dir($this->modules_dir)) {
            return;
        }
        
        $directories = scandir($this->modules_dir);
        
        foreach ($directories as $dir) {
            // . ve .. atla, _ ile başlayanları atla (örnek modül vb.)
            if ($dir === '.' || $dir === '..') {
                continue;
            }
            
            $module_path = $this->modules_dir . '/' . $dir;
            $manifest_file = $module_path . '/module.json';
            
            // Dizin değilse veya manifest yoksa atla
            if (!is_dir($module_path) || !file_exists($manifest_file)) {
                continue;
            }
            
            // Manifest'i oku
            $manifest = $this->readManifest($manifest_file);
            
            if ($manifest) {
                $manifest['path'] = $module_path;
                $manifest['dir'] = $dir;
                
                // Veritabanındaki bilgilerle birleştir
                if (isset($this->active_modules[$manifest['name']])) {
                    $manifest['db_info'] = $this->active_modules[$manifest['name']];
                    $manifest['is_active'] = true;
                } else {
                    $manifest['is_active'] = $this->isModuleActiveInDB($manifest['name']);
                }
                
                $this->all_modules[$manifest['name']] = $manifest;
            }
        }
        
        // Tema modüllerini de ekle (eğer varsa)
        $this->addThemeModulesToScan();
        
        return $this->all_modules;
    }
    
    /**
     * Aktif temanın modüllerini scan listesine ekle
     */
    private function addThemeModulesToScan() {
        // Önce all_modules içindeki tüm tema modüllerini temizle
        foreach ($this->all_modules as $name => $module) {
            if (isset($module['is_theme_module']) && $module['is_theme_module']) {
                unset($this->all_modules[$name]);
            }
        }
        
        // ThemeManager'dan aktif temayı al
        if (!class_exists('ThemeManager')) {
            require_once __DIR__ . '/ThemeManager.php';
        }
        
        $themeManager = ThemeManager::getInstance();
        $activeTheme = $themeManager->getActiveTheme();
        
        if (!$activeTheme) {
            return;
        }
        
        $themePath = $themeManager->getThemesPath() . '/' . $activeTheme['slug'];
        $themeModules = $this->scanThemeModules($themePath);
        
        // Tema modüllerini all_modules'e ekle (öncelikli - override eder)
        foreach ($themeModules as $name => $module) {
            $this->all_modules[$name] = $module;
        }
    }
    
    /**
     * Modül manifest dosyasını okur
     */
    private function readManifest($file) {
        $content = file_get_contents($file);
        $manifest = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Module manifest parse error: " . json_last_error_msg() . " in " . $file);
            return null;
        }
        
        // Zorunlu alanları kontrol et
        $required = ['name', 'title', 'version'];
        foreach ($required as $field) {
            if (!isset($manifest[$field])) {
                error_log("Module manifest missing required field: {$field} in {$file}");
                return null;
            }
        }
        
        // Varsayılan değerleri ayarla
        $defaults = [
            'description' => '',
            'author' => '',
            'website' => '',
            'requires_php' => '7.4',
            'requires_cms' => '1.0',
            'main_file' => 'Controller.php',
            'admin_menu' => null,
            'routes' => [],
            'shortcodes' => [],
            'widgets' => [],
            'settings' => false,
            'hooks' => ['actions' => [], 'filters' => []]
        ];
        
        return array_merge($defaults, $manifest);
    }
    
    /**
     * Aktif modülleri yükler
     */
    private function loadActiveModules() {
        foreach ($this->all_modules as $name => $module) {
            if ($module['is_active']) {
                // Eğer modül tema modülü değilse ve aynı isimde bir tema modülü varsa, tema modülü olmayan modülü yükleme
                if (!isset($module['is_theme_module']) || !$module['is_theme_module']) {
                    // Aynı isimde tema modülü var mı kontrol et
                    $hasThemeModule = false;
                    foreach ($this->all_modules as $otherName => $otherModule) {
                        if ($otherName === $name && isset($otherModule['is_theme_module']) && $otherModule['is_theme_module']) {
                            $hasThemeModule = true;
                            break;
                        }
                    }
                    if ($hasThemeModule) {
                        continue; // Tema modülü varsa, tema modülü olmayan modülü atla
                    }
                }
                $this->loadModule($module);
            }
        }
    }
    
    /**
     * Tek bir modülü yükler
     */
    public function loadModule($module) {
        $name = $module['name'];
        
        // Eğer aynı isimde bir modül zaten yüklenmişse
        if (isset($this->loaded_modules[$name])) {
            $existingModule = $this->loaded_modules[$name]['info'];
            
            // Eğer yeni modül tema modülü ise ve eski modül de tema modülü ise, eski modülü temizle
            if (isset($module['is_theme_module']) && isset($existingModule['is_theme_module'])) {
                // Route'ları temizle
                foreach ($this->routes as $type => &$routes) {
                    $this->routes[$type] = array_filter($routes, function($route) use ($name) {
                        return $route['module'] !== $name;
                    });
                }
                
                // Admin menüsünden kaldır
                $this->admin_menus = array_filter($this->admin_menus, function($menu) use ($name) {
                    return $menu['module'] !== $name;
                });
                
                // Yüklenen modüllerden kaldır
                unset($this->loaded_modules[$name]);
            } elseif (isset($module['is_theme_module']) && !isset($existingModule['is_theme_module'])) {
                // Yeni modül tema modülü ama eski modül tema modülü değilse, eski modülü temizle (tema modülü öncelikli)
                foreach ($this->routes as $type => &$routes) {
                    $this->routes[$type] = array_filter($routes, function($route) use ($name) {
                        return $route['module'] !== $name;
                    });
                }
                
                // Admin menüsünden kaldır
                $this->admin_menus = array_filter($this->admin_menus, function($menu) use ($name) {
                    return $menu['module'] !== $name;
                });
                
                unset($this->loaded_modules[$name]);
            } elseif (!isset($module['is_theme_module']) && isset($existingModule['is_theme_module'])) {
                // Yeni modül tema modülü değil ama eski modül tema modülü ise, yeni modülü yükleme (tema modülü öncelikli)
                return true;
            } else {
                // Aynı modül zaten yüklenmiş ve ikisi de tema modülü değilse, atla
                return true;
            }
        }
        
        // PHP versiyon kontrolü
        if (isset($module['requires_php']) && version_compare(PHP_VERSION, $module['requires_php'], '<')) {
            error_log("Module {$name} requires PHP {$module['requires_php']} or higher");
            return false;
        }
        
        // Ana dosyayı yükle
        $main_file = $module['path'] . '/' . $module['main_file'];
        
        if (!file_exists($main_file)) {
            error_log("Module main file not found: {$main_file}");
            return false;
        }
        
        // Model dosyalarını yükle (varsa)
        $models_dir = $module['path'] . '/models';
        if (is_dir($models_dir)) {
            foreach (glob($models_dir . '/*.php') as $model_file) {
                require_once $model_file;
            }
        }
        
        // Controller sınıfını kontrol et
        $controller_class = $this->getControllerClassName($module);
        
        // Eğer aynı sınıf adı zaten tanımlanmışsa
        if (class_exists($controller_class)) {
            // Aynı isimde yüklenmiş bir modül var mı kontrol et
            if (isset($this->loaded_modules[$name])) {
                $existingModule = $this->loaded_modules[$name]['info'];
                $existingTheme = isset($existingModule['theme_path']) ? basename($existingModule['theme_path']) : '';
                $newTheme = isset($module['theme_path']) ? basename($module['theme_path']) : '';
                
                // Eğer farklı temalara aitse, önceki modülü temizle
                if ($existingTheme !== $newTheme && $existingTheme && $newTheme) {
                    // Önceki modülü temizle
                    unset($this->loaded_modules[$name]);
                    // Route'ları temizle
                    foreach ($this->routes as $type => &$routes) {
                        $this->routes[$type] = array_filter($routes, function($route) use ($name) {
                            return $route['module'] !== $name;
                        });
                    }
                    
                    // Admin menüsünden kaldır
                    $this->admin_menus = array_filter($this->admin_menus, function($menu) use ($name) {
                        return $menu['module'] !== $name;
                    });
                    
                    error_log("Unloaded previous module {$name} from theme {$existingTheme} to load from theme {$newTheme}");
                    // Sınıf zaten tanımlı, yeni dosyayı yüklemeden devam et (mevcut sınıfı kullan)
                } else {
                    // Aynı tema, sınıf zaten yüklü, yeni dosyayı yükleme
                    error_log("Controller class {$controller_class} already exists for module {$name}. Using existing class.");
                    // Mevcut controller'ı kullan
                    if (isset($this->loaded_modules[$name]['controller'])) {
                        $controller = $this->loaded_modules[$name]['controller'];
                        // Modül bilgilerini güncelle
                        $this->loaded_modules[$name]['info'] = $module;
                        return true;
                    }
                }
            } else {
                // Sınıf tanımlı ama modül yüklenmemiş (muhtemelen başka bir temadan kaldı)
                // Yeni dosyayı yüklemeden devam et (mevcut sınıfı kullan)
                error_log("Warning: Controller class {$controller_class} already exists but module not loaded. Using existing class for module {$name}");
            }
            
            // Sınıf zaten tanımlı, yeni dosyayı yükleme (PHP'de sınıfları silemeyiz)
            // Mevcut sınıfı kullanmaya devam et
        } else {
            // Ana controller'ı yükle (sınıf henüz tanımlı değilse)
            require_once $main_file;
        }
        
        if (!class_exists($controller_class)) {
            error_log("Module controller class not found: {$controller_class} for module {$name}");
            error_log("Module path: {$module['path']}");
            error_log("Main file: {$main_file}");
            return false;
        }
        
        $controller = new $controller_class();
        
        // Modül bilgilerini controller'a aktar
        if (method_exists($controller, 'setModuleInfo')) {
            $controller->setModuleInfo($module);
        }
        
        // Aktivasyon hook'unu çağır
        if (method_exists($controller, 'onLoad')) {
            $controller->onLoad();
        }
        
        $this->loaded_modules[$name] = [
            'info' => $module,
            'controller' => $controller
        ];
        
        // Route'ları kaydet
        $this->registerModuleRoutes($module, $controller);
        
        // Admin menüsünü kaydet
        $this->registerAdminMenu($module);
        
        // Widget'ları kaydet
        $this->registerWidgets($module);
        
        // Shortcode'ları kaydet
        $this->registerShortcodes($module, $controller);
        
        error_log("Module {$name} loaded successfully with controller {$controller_class}");
        return true;
    }
    
    /**
     * Controller sınıf adını belirler
     */
    private function getControllerClassName($module) {
        // module.json'da belirtilmişse kullan
        if (isset($module['controller_class'])) {
            return $module['controller_class'];
        }
        
        // Varsayılan: ModuleNameController
        $name = str_replace(['-', '_'], ' ', $module['name']);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);
        
        return $name . 'ModuleController';
    }
    
    /**
     * Modül route'larını kaydeder
     */
    private function registerModuleRoutes($module, $controller) {
        if (empty($module['routes'])) {
            error_log("ModuleLoader::registerModuleRoutes - Module: {$module['name']} has no routes");
            return;
        }
        
        $debugMode = (defined('DEBUG_MODE') && DEBUG_MODE) || (ini_get('display_errors') == 1);
        
        if ($debugMode) {
            error_log("ModuleLoader::registerModuleRoutes - Module: {$module['name']}, Routes: " . count($module['routes']));
        }
        
        foreach ($module['routes'] as $route) {
            $route_data = [
                'module' => $module['name'],
                'path' => $route['path'] ?? '',
                'handler' => $route['handler'] ?? 'index',
                'method' => strtoupper($route['method'] ?? 'GET'),
                'controller' => $controller
            ];
            
            $type = $route['type'] ?? 'frontend';
            
            // Route dizisini başlat (eğer yoksa)
            if (!isset($this->routes[$type])) {
                $this->routes[$type] = [];
            }
            
            $this->routes[$type][] = $route_data;
            
            if ($debugMode) {
                error_log("  Registered route: {$type} - {$route_data['path']} -> {$route_data['handler']} (method: {$route_data['method']})");
            }
        }
        
        if ($debugMode) {
            error_log("Total {$type} routes after registration: " . count($this->routes[$type]));
        }
    }
    
    /**
     * Admin menüsünü kaydeder
     */
    private function registerAdminMenu($module) {
        if (empty($module['admin_menu'])) {
            return;
        }
        
        $name = $module['name'];
        
        // Aynı modül için zaten bir menü varsa, önce onu kaldır (duplikasyonu önlemek için)
        $this->admin_menus = array_filter($this->admin_menus, function($menu) use ($name) {
            return $menu['module'] !== $name;
        });
        
        $menu = $module['admin_menu'];
        
        $this->admin_menus[] = [
            'module' => $name,
            'title' => $menu['title'] ?? $module['title'],
            'icon' => $menu['icon'] ?? 'extension',
            'position' => $menu['position'] ?? 100,
            'slug' => 'module/' . $name,
            'submenu' => $menu['submenu'] ?? []
        ];
        
        // Pozisyona göre sırala
        usort($this->admin_menus, function($a, $b) {
            return $a['position'] - $b['position'];
        });
    }
    
    /**
     * Widget'ları kaydeder
     */
    private function registerWidgets($module) {
        if (empty($module['widgets'])) {
            return;
        }
        
        foreach ($module['widgets'] as $widget) {
            $widget_file = $module['path'] . '/widgets/' . $widget . '.php';
            
            if (file_exists($widget_file)) {
                require_once $widget_file;
                
                if (class_exists($widget)) {
                    $this->widgets[$widget] = [
                        'module' => $module['name'],
                        'class' => $widget,
                        'path' => $widget_file
                    ];
                }
            }
        }
    }
    
    /**
     * Shortcode'ları kaydeder
     */
    private function registerShortcodes($module, $controller) {
        if (empty($module['shortcodes'])) {
            return;
        }
        
        // ShortcodeParser yüklü mü kontrol et
        if (!class_exists('ShortcodeParser')) {
            require_once __DIR__ . '/ShortcodeParser.php';
        }
        
        $parser = ShortcodeParser::getInstance();
        
        foreach ($module['shortcodes'] as $shortcode) {
            // Controller'da shortcode handler metodu var mı kontrol et
            $method = 'shortcode_' . str_replace('-', '_', $shortcode);
            
            if (method_exists($controller, $method)) {
                $parser->add($shortcode, [$controller, $method]);
            }
        }
    }
    
    // ==================== PUBLIC GETTERS ====================
    
    /**
     * Tüm modülleri döndürür
     */
    public function getAllModules() {
        return $this->all_modules;
    }
    
    /**
     * Yüklenen modülleri döndürür
     */
    public function getLoadedModules() {
        return $this->loaded_modules;
    }
    
    /**
     * Admin menülerini döndürür
     */
    public function getAdminMenus() {
        return $this->admin_menus;
    }
    
    /**
     * Modül route'larını döndürür
     */
    public function getRoutes($type = null) {
        if ($type) {
            return $this->routes[$type] ?? [];
        }
        return $this->routes;
    }
    
    /**
     * Widget'ları döndürür
     */
    public function getWidgets() {
        return $this->widgets;
    }
    
    /**
     * Belirli bir modülü döndürür
     */
    public function getModule($name) {
        return $this->all_modules[$name] ?? null;
    }
    
    /**
     * Yüklenen modül controller'ını döndürür
     */
    public function getModuleController($name) {
        return $this->loaded_modules[$name]['controller'] ?? null;
    }
    
    /**
     * Modül dizinini döndürür
     */
    public function getModulesDir() {
        return $this->modules_dir;
    }
    
    // ==================== MODULE MANAGEMENT ====================
    
    /**
     * Modül aktif mi kontrol eder (veritabanında)
     */
    public function isModuleActiveInDB($slug) {
        try {
            $result = $this->db->fetch(
                "SELECT is_active FROM modules WHERE slug = ?",
                [$slug]
            );
            return $result && $result['is_active'] == 1;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Modülü aktif eder
     */
    public function activateModule($name) {
        $module = $this->getModule($name);
        
        if (!$module) {
            return ['success' => false, 'message' => 'Modül bulunamadı'];
        }
        
        try {
            // Veritabanında kayıt var mı kontrol et
            $existing = $this->db->fetch(
                "SELECT id FROM modules WHERE slug = ?",
                [$name]
            );
            
            // module.json'da is_system belirtilmiş mi kontrol et (varsayılan: 0)
            $isSystem = isset($module['is_system']) ? (int)$module['is_system'] : 0;
            
            if ($existing) {
                // Güncelle - is_system değerini de güncelle
                $this->db->query(
                    "UPDATE modules SET is_active = 1, is_system = ?, updated_at = NOW() WHERE slug = ?",
                    [$isSystem, $name]
                );
            } else {
                // Yeni kayıt ekle
                $this->db->query(
                    "INSERT INTO modules (name, slug, label, description, icon, version, author, path, is_active, is_system, installed_at, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?, NOW(), NOW())",
                    [
                        $module['name'],
                        $module['name'],
                        $module['title'],
                        $module['description'] ?? '',
                        $module['admin_menu']['icon'] ?? 'extension',
                        $module['version'],
                        $module['author'] ?? '',
                        $module['path'],
                        $isSystem
                    ]
                );
            }
            
            // Modülü yükle ve aktivasyon hook'unu çağır
            $this->loadModule($module);
            
            $controller = $this->getModuleController($name);
            if ($controller && method_exists($controller, 'onActivate')) {
                $controller->onActivate();
            }
            
            do_action('module_activated', $name, $module);
            
            return ['success' => true, 'message' => 'Modül başarıyla aktif edildi'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Hata: ' . $e->getMessage()];
        }
    }
    
    /**
     * Modülü devre dışı bırakır
     */
    public function deactivateModule($name) {
        $module = $this->getModule($name);
        
        if (!$module) {
            return ['success' => false, 'message' => 'Modül bulunamadı'];
        }
        
        try {
            // Deaktivasyon hook'unu çağır
            $controller = $this->getModuleController($name);
            if ($controller && method_exists($controller, 'onDeactivate')) {
                $controller->onDeactivate();
            }
            
            // Veritabanında güncelle
            $this->db->query(
                "UPDATE modules SET is_active = 0, updated_at = NOW() WHERE slug = ?",
                [$name]
            );
            
            // Yüklenen modüllerden kaldır
            unset($this->loaded_modules[$name]);
            
            do_action('module_deactivated', $name, $module);
            
            return ['success' => true, 'message' => 'Modül devre dışı bırakıldı'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Hata: ' . $e->getMessage()];
        }
    }
    
    /**
     * Modülü siler
     */
    public function deleteModule($name) {
        $module = $this->getModule($name);
        
        if (!$module) {
            return ['success' => false, 'message' => 'Modül bulunamadı'];
        }
        
        // Önce deaktive et
        $this->deactivateModule($name);
        
        try {
            // Uninstall hook'unu çağır
            $controller = $this->getModuleController($name);
            if ($controller && method_exists($controller, 'onUninstall')) {
                $controller->onUninstall();
            }
            
            // Veritabanından sil
            $this->db->query("DELETE FROM modules WHERE slug = ?", [$name]);
            
            // Dosyaları sil
            $module_path = $module['path'];
            if (is_dir($module_path)) {
                $this->deleteDirectory($module_path);
            }
            
            // Listeden kaldır
            unset($this->all_modules[$name]);
            
            do_action('module_deleted', $name);
            
            return ['success' => true, 'message' => 'Modül başarıyla silindi'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Hata: ' . $e->getMessage()];
        }
    }
    
    /**
     * ZIP dosyasından modül yükler
     */
    public function installFromZip($zip_file) {
        if (!file_exists($zip_file)) {
            return ['success' => false, 'message' => 'ZIP dosyası bulunamadı'];
        }
        
        $zip = new ZipArchive();
        
        if ($zip->open($zip_file) !== true) {
            return ['success' => false, 'message' => 'ZIP dosyası açılamadı'];
        }
        
        // Geçici klasöre çıkart
        $temp_dir = sys_get_temp_dir() . '/module_' . uniqid();
        $zip->extractTo($temp_dir);
        $zip->close();
        
        // module.json'ı bul
        $manifest_file = null;
        $module_root = null;
        
        // Önce root'ta ara
        if (file_exists($temp_dir . '/module.json')) {
            $manifest_file = $temp_dir . '/module.json';
            $module_root = $temp_dir;
        } else {
            // Alt klasörlerde ara
            $subdirs = scandir($temp_dir);
            foreach ($subdirs as $subdir) {
                if ($subdir === '.' || $subdir === '..') continue;
                
                $subpath = $temp_dir . '/' . $subdir;
                if (is_dir($subpath) && file_exists($subpath . '/module.json')) {
                    $manifest_file = $subpath . '/module.json';
                    $module_root = $subpath;
                    break;
                }
            }
        }
        
        if (!$manifest_file) {
            $this->deleteDirectory($temp_dir);
            return ['success' => false, 'message' => 'module.json bulunamadı'];
        }
        
        // Manifest'i oku
        $manifest = $this->readManifest($manifest_file);
        
        if (!$manifest) {
            $this->deleteDirectory($temp_dir);
            return ['success' => false, 'message' => 'Geçersiz module.json'];
        }
        
        // Hedef klasörü belirle
        $target_dir = $this->modules_dir . '/' . $manifest['name'];
        
        // Zaten kurulu mu kontrol et
        if (is_dir($target_dir)) {
            // Güncelleme mi yapılacak?
            $existing = $this->getModule($manifest['name']);
            if ($existing && version_compare($manifest['version'], $existing['version'], '<=')) {
                $this->deleteDirectory($temp_dir);
                return ['success' => false, 'message' => 'Bu versiyon veya daha yeni bir versiyon zaten kurulu'];
            }
            
            // Eski versiyonu yedekle ve sil
            $this->deleteDirectory($target_dir);
        }
        
        // Modülü taşı
        rename($module_root, $target_dir);
        
        // Geçici klasörü temizle
        $this->deleteDirectory($temp_dir);
        
        // Modül listesini yenile
        $this->scanModules();
        
        do_action('module_installed', $manifest['name'], $manifest);
        
        return [
            'success' => true, 
            'message' => 'Modül başarıyla yüklendi',
            'module' => $manifest
        ];
    }
    
    /**
     * Klasörü recursive siler
     */
    private function deleteDirectory($dir) {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        
        rmdir($dir);
    }
    
    // ==================== MODULE SETTINGS ====================
    
    /**
     * Modül ayarlarını getirir
     */
    public function getModuleSettings($name) {
        try {
            $result = $this->db->fetch(
                "SELECT settings FROM modules WHERE slug = ?",
                [$name]
            );
            
            if ($result && $result['settings']) {
                return json_decode($result['settings'], true) ?: [];
            }
        } catch (Exception $e) {
            // Sessizce devam et
        }
        
        return [];
    }
    
    /**
     * Modül ayarlarını kaydeder
     */
    public function saveModuleSettings($name, $settings) {
        try {
            $this->db->query(
                "UPDATE modules SET settings = ?, updated_at = NOW() WHERE slug = ?",
                [json_encode($settings, JSON_UNESCAPED_UNICODE), $name]
            );
            
            do_action('module_settings_saved', $name, $settings);
            
            return ['success' => true, 'message' => 'Ayarlar kaydedildi'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Hata: ' . $e->getMessage()];
        }
    }
    
    /**
     * Tek bir modül ayarını getirir
     */
    public function getModuleSetting($name, $key, $default = null) {
        $settings = $this->getModuleSettings($name);
        return $settings[$key] ?? $default;
    }
    
    // ==================== ROUTE HANDLING ====================
    
    /**
     * Admin modül route'unu işler
     */
    public function handleAdminRoute($page) {
        // module/module-name/action/param şeklinde parse et
        $parts = explode('/', $page);
        
        if (count($parts) < 2 || $parts[0] !== 'module') {
            return false;
        }
        
        $module_name = $parts[1];
        $action = $parts[2] ?? 'index';
        $params = array_slice($parts, 3);
        
        // Modül yüklü mü kontrol et
        $controller = $this->getModuleController($module_name);
        
        // Controller yoksa, modülü yüklemeyi dene (aktifse veya tema modülü ise)
        if (!$controller) {
            $module = $this->getModule($module_name);
            if ($module) {
                // Tema modülü ise veritabanı kontrolü yapmadan yükle
                $isThemeModule = isset($module['is_theme_module']) && $module['is_theme_module'];
                if ($isThemeModule || $this->isModuleActiveInDB($module_name)) {
                    // Modülü yükle
                    $this->loadModule($module);
                    $controller = $this->getModuleController($module_name);
                }
            }
        }
        
        if (!$controller) {
            return false;
        }
        
        // Admin action'ı çağır
        $method = 'admin_' . str_replace('-', '_', $action);
        
        if (method_exists($controller, $method)) {
            call_user_func_array([$controller, $method], $params);
            return true;
        }
        
        // Fallback: normal action
        if (method_exists($controller, $action)) {
            call_user_func_array([$controller, $action], $params);
            return true;
        }
        
        return false;
    }
    
    /**
     * Frontend modül route'unu işler
     */
    public function handleFrontendRoute($path) {
        // Path'i normalize et (baştaki ve sondaki slash'leri kaldır)
        $path = trim($path, '/');
        
        // Aktif temayı al (sadece aktif temanın modüllerini kullanmak için)
        $activeTheme = get_option('active_theme', 'realestate');
        
        // Debug modu kontrolü
        $debugMode = (defined('DEBUG_MODE') && DEBUG_MODE) || (ini_get('display_errors') == 1);
        
        if ($debugMode) {
            error_log("ModuleLoader::handleFrontendRoute - Path: '$path', Active Theme: '$activeTheme'");
            error_log("Total frontend routes: " . (isset($this->routes['frontend']) ? count($this->routes['frontend']) : 0));
            if (isset($this->routes['frontend'])) {
                foreach ($this->routes['frontend'] as $idx => $route) {
                    error_log("  Route #{$idx}: module={$route['module']}, path={$route['path']}, handler={$route['handler']}");
                }
            }
        }
        
        foreach ($this->routes['frontend'] as $index => $route) {
            // Modülün aktif temaya ait olup olmadığını kontrol et
            $moduleName = $route['module'] ?? '';
            $module = $this->getModule($moduleName);
            
            if ($module) {
                // Tema modülü mü kontrol et
                $isThemeModule = isset($module['is_theme_module']) && $module['is_theme_module'];
                
                if ($isThemeModule) {
                    // Modülün tema path'inden tema adını çıkar
                    $moduleThemePath = $module['theme_path'] ?? '';
                    $moduleThemeName = '';
                    if ($moduleThemePath) {
                        $pathParts = explode('/', trim($moduleThemePath, '/'));
                        $moduleThemeName = end($pathParts);
                    }
                    
                    // Modül aktif temaya ait değilse, bu route'u atla
                    if ($moduleThemeName !== $activeTheme) {
                        if ($debugMode) {
                            error_log("  Skipping route from inactive theme: {$moduleThemeName} (active: {$activeTheme})");
                        }
                        continue;
                    }
                } else {
                    // Tema modülü değilse, genel modül (modules/ dizinindeki)
                    // Bu modüller her zaman kullanılabilir
                }
            }
            // Route path'ini normalize et
            $routePath = trim($route['path'], '/');
            
            if ($debugMode) {
                error_log("  Route #$index: '{$routePath}' (handler: {$route['handler']})");
            }
            
            // Tam eşleşme kontrolü (parametreli route'lar için)
            if ($routePath === $path) {
                $controller = $route['controller'];
                $handler = $route['handler'];
                
                if ($debugMode) {
                    error_log("    Exact match found! Calling {$route['module']}::{$handler}");
                }
                
                if (method_exists($controller, $handler)) {
                    call_user_func([$controller, $handler]);
                    return true;
                }
            }
            
            // Pattern eşleştirme (parametreli route'lar için)
            $pattern = $this->routeToPattern($routePath);
            
            if ($debugMode) {
                error_log("    Pattern: $pattern");
            }
            
            if (preg_match($pattern, $path, $matches)) {
                $controller = $route['controller'];
                $handler = $route['handler'];
                
                if ($debugMode) {
                    error_log("    Pattern match found! Matches: " . print_r($matches, true));
                    error_log("    Calling {$route['module']}::{$handler} with params: " . print_r(array_slice($matches, 1), true));
                }
                
                if (method_exists($controller, $handler)) {
                    array_shift($matches); // İlk eleman tam eşleşme
                    call_user_func_array([$controller, $handler], $matches);
                    return true;
                } else {
                    if ($debugMode) {
                        error_log("    ERROR: Method {$handler} not found in controller");
                    }
                }
            }
        }
        
        if ($debugMode) {
            error_log("No route matched for path: '$path'");
        }
        
        return false;
    }
    
    /**
     * Route pattern'ını regex'e çevirir
     */
    private function routeToPattern($route) {
        // Eğer route'da parametre yoksa, direkt eşleşme için escape et
        if (strpos($route, '{') === false) {
            return '#^' . preg_quote($route, '#') . '$#';
        }
        
        $pattern = preg_replace('/\{(\w+)\}/', '([^/]+)', $route);
        return '#^' . $pattern . '$#';
    }
}

// ==================== GLOBAL HELPER FUNCTIONS ====================

/**
 * ModuleLoader instance'ını döndürür
 */
function module_loader() {
    return ModuleLoader::getInstance();
}

/**
 * Modül aktif mi kontrol eder
 * NOT: Bu fonksiyon includes/functions.php'de tanımlı, burada sadece dokümantasyon
 */
// function is_module_active() moved to includes/functions.php to avoid redeclaration

/**
 * Modül ayarını getirir
 * NOT: Bu fonksiyon includes/functions.php'de tanımlı, burada sadece dokümantasyon
 */
// function get_module_setting() moved to includes/functions.php to avoid redeclaration

/**
 * Widget render eder
 */
function render_widget($name, $args = []) {
    $loader = ModuleLoader::getInstance();
    $widgets = $loader->getWidgets();
    
    if (!isset($widgets[$name])) {
        return '';
    }
    
    $widget_class = $widgets[$name]['class'];
    
    if (class_exists($widget_class)) {
        $widget = new $widget_class();
        
        if (method_exists($widget, 'render')) {
            ob_start();
            $widget->render($args);
            return ob_get_clean();
        }
    }
    
    return '';
}

