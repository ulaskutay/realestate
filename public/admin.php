<?php
/**
 * Admin Paneli Giriş Noktası
 * Tüm admin istekleri buradan yönlendirilir
 */

// Session başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// URL'den page parametresini al (AJAX endpoint'leri için erken kontrol)
$page = $_GET['page'] ?? 'login';

// AJAX endpoint'leri için hata gösterimini kapat
$ajaxEndpoints = [
    'sliders/file-manager-list',
    'sliders/file-manager-upload',
    'sliders/file-manager-delete',
    'sliders/add-item',
    'sliders/update-item',
    'sliders/delete-item',
    'sliders/update-item-order',
    'sliders/add-layer',
    'sliders/update-layer',
    'sliders/delete-layer',
    'sliders/update-layer-order',
    'sliders/layer-data',
    'media/list',
    'media/upload',
    'media/get',
    'media/update',
    'media/delete',
    'media/delete-multiple',
    'media/disk-usage',
    'menus/save',
    'menus/add-item',
    'menus/update-item',
    'menus/delete-item',
    'menus/update-item-order',
    'menus/get-items',
    'forms/add-field',
    'forms/update-field',
    'forms/delete-field',
    'forms/update-field-order',
    'forms/get-field',
    'forms/get-preview-html',
    'forms/view-submission',
    'forms/update-submission-status',
    'forms/delete-submission',
    'forms/bulk-submission-action',
    'forms/submit',
    'themes/saveSettings',
    'themes/saveCustomCode',
    'themes/saveSection',
    'themes/deleteSection',
    'themes/updateSectionOrder',
    'themes/getSections',
    'themes/getSectionData',
    'test_smtp_connection',
    'send_test_email'
];

$isAjaxEndpoint = false;
foreach ($ajaxEndpoints as $endpoint) {
    if (strpos($page, $endpoint) === 0) {
        $isAjaxEndpoint = true;
        break;
    }
}

if ($isAjaxEndpoint) {
    // AJAX endpoint'leri için hata gösterimini kapat
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
} else {
    // Normal sayfalar için hata raporlamayı aç (geliştirme için)
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Core dosyalarını yükle
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Role.php';
require_once __DIR__ . '/../core/ViewRenderer.php';

// Hook Sistemi ve Modül Yükleyiciyi yükle
require_once __DIR__ . '/../core/HookSystem.php';
require_once __DIR__ . '/../core/ModuleLoader.php';
require_once __DIR__ . '/../core/ShortcodeParser.php';

// Yardımcı fonksiyonları yükle
require_once __DIR__ . '/../includes/functions.php';

// Modül sistemini başlat (admin_init hook'u tetiklenir)
try {
    $moduleLoader = ModuleLoader::getInstance();
    $moduleLoader->init();
    do_action('admin_init');
} catch (Exception $e) {
    error_log("Module loader error: " . $e->getMessage());
}

// Slider route'larını kontrol et
if (strpos($page, 'sliders') === 0) {
    // Slider controller'ı yükle
    require_once __DIR__ . '/../app/models/Slider.php';
    require_once __DIR__ . '/../app/models/SliderItem.php';
    
    // Layer model'ini yükle (eğer varsa)
    $layerModelFile = __DIR__ . '/../app/models/SliderLayer.php';
    if (file_exists($layerModelFile)) {
        require_once $layerModelFile;
    }
    
    $controllerFile = __DIR__ . '/../app/controllers/SliderController.php';
    if (!file_exists($controllerFile)) {
        die("SliderController bulunamadı");
    }
    
    require $controllerFile;
    
    if (!class_exists('SliderController')) {
        die("SliderController sınıfı bulunamadı");
    }
    
    $controller = new SliderController();
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Slider route'larını parse et
    $parts = explode('/', $page);
    
    if (count($parts) === 1 && $parts[0] === 'sliders') {
        // Slider listesi
        $controller->index();
    } else if (count($parts) === 2 && $parts[0] === 'sliders' && $parts[1] === 'create') {
        // Yeni slider oluşturma formu
        $controller->create();
    } else if (count($parts) === 2 && $parts[0] === 'sliders' && $parts[1] === 'store') {
        // Slider kaydetme
        $controller->store();
    } else if (count($parts) === 3 && $parts[0] === 'sliders' && $parts[1] === 'edit') {
        // Slider düzenleme formu
        $controller->edit($parts[2]);
    } else if (count($parts) === 3 && $parts[0] === 'sliders' && $parts[1] === 'update') {
        // Slider güncelleme
        $controller->update($parts[2]);
    } else if (count($parts) === 3 && $parts[0] === 'sliders' && $parts[1] === 'delete') {
        // Slider silme
        $controller->delete($parts[2]);
    } else if (count($parts) === 3 && $parts[0] === 'sliders' && $parts[1] === 'toggle') {
        // Slider durum değiştirme
        $controller->toggleStatus($parts[2]);
    } else if (count($parts) === 2 && $parts[0] === 'sliders' && $parts[1] === 'add-item') {
        // Item ekleme (AJAX)
        $controller->addItem();
    } else if (count($parts) === 3 && $parts[0] === 'sliders' && $parts[1] === 'update-item') {
        // Item güncelleme (AJAX)
        $controller->updateItem($parts[2]);
    } else if (count($parts) === 3 && $parts[0] === 'sliders' && $parts[1] === 'delete-item') {
        // Item silme (AJAX)
        $controller->deleteItem($parts[2]);
    } else if (count($parts) === 2 && $parts[0] === 'sliders' && $parts[1] === 'update-item-order') {
        // Item sıralaması güncelleme (AJAX)
        $controller->updateItemOrder();
    } else if (count($parts) === 3 && $parts[0] === 'sliders' && $parts[1] === 'edit-item') {
        // Item düzenleme (Gelişmiş editor)
        $controller->editItem($parts[2]);
    } else if (count($parts) === 2 && $parts[0] === 'sliders' && $parts[1] === 'add-layer') {
        // Layer ekleme (AJAX)
        $controller->addLayer();
    } else if (count($parts) === 3 && $parts[0] === 'sliders' && $parts[1] === 'update-layer') {
        // Layer güncelleme (AJAX)
        $controller->updateLayer($parts[2]);
    } else if (count($parts) === 3 && $parts[0] === 'sliders' && $parts[1] === 'delete-layer') {
        // Layer silme (AJAX)
        $controller->deleteLayer($parts[2]);
    } else if (count($parts) === 2 && $parts[0] === 'sliders' && $parts[1] === 'update-layer-order') {
        // Layer sıralaması güncelleme (AJAX)
        $controller->updateLayerOrder();
    } else if (count($parts) === 3 && $parts[0] === 'sliders' && $parts[1] === 'layer-data') {
        // Layer verisi getir (AJAX)
        $controller->getLayerData($parts[2]);
    } else if (count($parts) === 3 && $parts[0] === 'sliders' && $parts[1] === 'preview-item') {
        // Item önizleme
        $controller->previewItem($parts[2]);
    } else if (count($parts) === 3 && $parts[0] === 'sliders' && $parts[1] === 'preview') {
        // Slider önizleme (Tam sayfa)
        $controller->preview($parts[2]);
    } else if (count($parts) === 2 && $parts[0] === 'sliders' && $parts[1] === 'file-manager-list') {
        // Dosya yöneticisi - Dosya listesi (AJAX)
        // AJAX için hata gösterimini kapat
        ini_set('display_errors', 0);
        $controller->fileManagerList();
    } else if (count($parts) === 2 && $parts[0] === 'sliders' && $parts[1] === 'file-manager-upload') {
        // Dosya yöneticisi - Dosya yükleme (AJAX)
        // AJAX için hata gösterimini kapat
        ini_set('display_errors', 0);
        $controller->fileManagerUpload();
    } else if (count($parts) === 2 && $parts[0] === 'sliders' && $parts[1] === 'file-manager-delete') {
        // Dosya yöneticisi - Dosya silme (AJAX)
        // AJAX için hata gösterimini kapat
        ini_set('display_errors', 0);
        $controller->fileManagerDelete();
    } else {
        header("Location: " . admin_url('sliders'));
        exit;
    }
    exit;
}

// Users route'larını kontrol et
if (strpos($page, 'users') === 0) {
    try {
        // User model'ini yükle
        require_once __DIR__ . '/../app/models/User.php';
        
        $controllerFile = __DIR__ . '/../app/controllers/UserController.php';
        if (!file_exists($controllerFile)) {
            die("UserController bulunamadı: " . $controllerFile);
        }
        
        require $controllerFile;
        
        if (!class_exists('UserController')) {
            die("UserController sınıfı bulunamadı");
        }
        
        $controller = new UserController();
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Users route'larını parse et
        $parts = explode('/', $page);
        
        if (count($parts) === 1 && $parts[0] === 'users') {
            // Kullanıcı listesi
            $controller->index();
        } else if (count($parts) === 2 && $parts[0] === 'users' && $parts[1] === 'create') {
        // Yeni kullanıcı oluşturma formu
        $controller->create();
    } else if (count($parts) === 2 && $parts[0] === 'users' && $parts[1] === 'store') {
        // Kullanıcı kaydetme
        $controller->store();
    } else if (count($parts) === 3 && $parts[0] === 'users' && $parts[1] === 'edit') {
        // Kullanıcı düzenleme formu
        $controller->edit($parts[2]);
    } else if (count($parts) === 3 && $parts[0] === 'users' && $parts[1] === 'update') {
        // Kullanıcı güncelleme
        $controller->update($parts[2]);
    } else if (count($parts) === 3 && $parts[0] === 'users' && $parts[1] === 'delete') {
        // Kullanıcı silme
        $controller->delete($parts[2]);
    } else if (count($parts) === 3 && $parts[0] === 'users' && $parts[1] === 'toggle') {
        // Kullanıcı durum değiştirme
        $controller->toggleStatus($parts[2]);
    } else {
        header("Location: " . admin_url('users'));
        exit;
    }
    } catch (Exception $e) {
        die("Hata: " . $e->getMessage() . " - Dosya: " . $e->getFile() . " - Satır: " . $e->getLine());
    } catch (Error $e) {
        die("Fatal Hata: " . $e->getMessage() . " - Dosya: " . $e->getFile() . " - Satır: " . $e->getLine());
    }
    exit;
}

// Media route'larını kontrol et
if (strpos($page, 'media') === 0) {
    try {
        // Media model'ini yükle
        require_once __DIR__ . '/../app/models/Media.php';
        
        $controllerFile = __DIR__ . '/../app/controllers/MediaLibraryController.php';
        if (!file_exists($controllerFile)) {
            die("MediaLibraryController bulunamadı: " . $controllerFile);
        }
        
        require $controllerFile;
        
        if (!class_exists('MediaLibraryController')) {
            die("MediaLibraryController sınıfı bulunamadı");
        }
        
        $controller = new MediaLibraryController();
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Media route'larını parse et
        $parts = explode('/', $page);
        
        if (count($parts) === 1 && $parts[0] === 'media') {
            // Medya listesi
            $controller->index();
        } else if (count($parts) === 2 && $parts[0] === 'media' && $parts[1] === 'upload') {
            // Dosya yükleme (AJAX)
            $controller->uploadFile();
        } else if (count($parts) === 3 && $parts[0] === 'media' && $parts[1] === 'get') {
            // Medya detayı (AJAX)
            $controller->getMedia($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'media' && $parts[1] === 'update') {
            // Medya güncelleme (AJAX)
            $controller->updateMedia($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'media' && $parts[1] === 'delete') {
            // Medya silme (AJAX)
            $controller->deleteMedia($parts[2]);
        } else if (count($parts) === 2 && $parts[0] === 'media' && $parts[1] === 'delete-multiple') {
            // Toplu silme (AJAX)
            $controller->deleteMultiple();
        } else if (count($parts) === 2 && $parts[0] === 'media' && $parts[1] === 'list') {
            // Medya listesi (AJAX)
            $controller->getList();
        } else if (count($parts) === 2 && $parts[0] === 'media' && $parts[1] === 'disk-usage') {
            // Disk kullanımı (AJAX)
            $controller->getDiskUsage();
        } else {
            header("Location: " . admin_url('media'));
            exit;
        }
    } catch (Exception $e) {
        die("Hata: " . $e->getMessage() . " - Dosya: " . $e->getFile() . " - Satır: " . $e->getLine());
    } catch (Error $e) {
        die("Fatal Hata: " . $e->getMessage() . " - Dosya: " . $e->getFile() . " - Satır: " . $e->getLine());
    }
    exit;
}

// Roles route'larını kontrol et
if (strpos($page, 'roles') === 0) {
    try {
        // Role ve Module model'lerini yükle
        require_once __DIR__ . '/../app/models/RoleModel.php';
        require_once __DIR__ . '/../app/models/ModuleModel.php';
        
        $controllerFile = __DIR__ . '/../app/controllers/RoleController.php';
        if (!file_exists($controllerFile)) {
            die("RoleController bulunamadı: " . $controllerFile);
        }
        
        require $controllerFile;
        
        if (!class_exists('RoleController')) {
            die("RoleController sınıfı bulunamadı");
        }
        
        $controller = new RoleController();
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Roles route'larını parse et
        $parts = explode('/', $page);
        
        if (count($parts) === 1 && $parts[0] === 'roles') {
            // Rol listesi
            $controller->index();
        } else if (count($parts) === 2 && $parts[0] === 'roles' && $parts[1] === 'create') {
            // Yeni rol oluşturma formu
            $controller->create();
        } else if (count($parts) === 2 && $parts[0] === 'roles' && $parts[1] === 'store') {
            // Rol kaydetme
            $controller->store();
        } else if (count($parts) === 3 && $parts[0] === 'roles' && $parts[1] === 'edit') {
            // Rol düzenleme formu
            $controller->edit($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'roles' && $parts[1] === 'update') {
            // Rol güncelleme
            $controller->update($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'roles' && $parts[1] === 'delete') {
            // Rol silme
            $controller->delete($parts[2]);
        } else {
            header("Location: " . admin_url('roles'));
            exit;
        }
    } catch (Exception $e) {
        die("Hata: " . $e->getMessage() . " - Dosya: " . $e->getFile() . " - Satır: " . $e->getLine());
    } catch (Error $e) {
        die("Fatal Hata: " . $e->getMessage() . " - Dosya: " . $e->getFile() . " - Satır: " . $e->getLine());
    }
    exit;
}

// Menus route'larını kontrol et
if (strpos($page, 'menus') === 0) {
    try {
        // Menu model'lerini yükle
        require_once __DIR__ . '/../app/models/Menu.php';
        require_once __DIR__ . '/../app/models/MenuItem.php';
        
        $controllerFile = __DIR__ . '/../app/controllers/MenuController.php';
        if (!file_exists($controllerFile)) {
            die("MenuController bulunamadı: " . $controllerFile);
        }
        
        require $controllerFile;
        
        if (!class_exists('MenuController')) {
            die("MenuController sınıfı bulunamadı");
        }
        
        $controller = new MenuController();
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Menus route'larını parse et
        $parts = explode('/', $page);
        
        if (count($parts) === 1 && $parts[0] === 'menus') {
            // Menü listesi
            $controller->index();
        } else if (count($parts) === 2 && $parts[0] === 'menus' && $parts[1] === 'create') {
            // Yeni menü oluşturma formu
            $controller->create();
        } else if (count($parts) === 2 && $parts[0] === 'menus' && $parts[1] === 'store') {
            // Menü kaydetme (eski endpoint)
            $controller->store();
        } else if (count($parts) === 2 && $parts[0] === 'menus' && $parts[1] === 'save') {
            // Menü kaydetme (AJAX)
            $controller->save();
        } else if (count($parts) === 3 && $parts[0] === 'menus' && $parts[1] === 'edit') {
            // Menü düzenleme formu
            $controller->edit($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'menus' && $parts[1] === 'update') {
            // Menü güncelleme
            $controller->update($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'menus' && $parts[1] === 'delete') {
            // Menü silme
            $controller->delete($parts[2]);
        } else if (count($parts) === 2 && $parts[0] === 'menus' && $parts[1] === 'add-item') {
            // Menü öğesi ekleme (AJAX)
            $controller->addItem();
        } else if (count($parts) === 3 && $parts[0] === 'menus' && $parts[1] === 'update-item') {
            // Menü öğesi güncelleme (AJAX)
            $controller->updateItem($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'menus' && $parts[1] === 'delete-item') {
            // Menü öğesi silme (AJAX)
            $controller->deleteItem($parts[2]);
        } else if (count($parts) === 2 && $parts[0] === 'menus' && $parts[1] === 'update-item-order') {
            // Öğe sıralaması güncelleme (AJAX)
            $controller->updateItemOrder();
        } else if (count($parts) === 3 && $parts[0] === 'menus' && $parts[1] === 'get-items') {
            // Menü öğelerini getir (AJAX)
            $controller->getItems($parts[2]);
        } else {
            header("Location: " . admin_url('menus'));
            exit;
        }
    } catch (Exception $e) {
        die("Hata: " . $e->getMessage() . " - Dosya: " . $e->getFile() . " - Satır: " . $e->getLine());
    } catch (Error $e) {
        die("Fatal Hata: " . $e->getMessage() . " - Dosya: " . $e->getFile() . " - Satır: " . $e->getLine());
    }
    exit;
}

// Posts route'larını kontrol et
if (strpos($page, 'posts') === 0) {
    try {
        // Post model'lerini yükle
        require_once __DIR__ . '/../app/models/Post.php';
        require_once __DIR__ . '/../app/models/PostCategory.php';
        
        $controllerFile = __DIR__ . '/../app/controllers/PostController.php';
        if (!file_exists($controllerFile)) {
            die("PostController bulunamadı: " . $controllerFile);
        }
        
        require $controllerFile;
        
        if (!class_exists('PostController')) {
            die("PostController sınıfı bulunamadı");
        }
        
        $controller = new PostController();
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Posts route'larını parse et
        $parts = explode('/', $page);
        
        if (count($parts) === 1 && $parts[0] === 'posts') {
            // Yazı listesi
            $controller->index();
        } else if (count($parts) === 2 && $parts[0] === 'posts' && $parts[1] === 'create') {
            // Yeni yazı oluşturma formu
            $controller->create();
        } else if (count($parts) === 2 && $parts[0] === 'posts' && $parts[1] === 'store') {
            // Yazı kaydetme
            $controller->store();
        } else if (count($parts) === 3 && $parts[0] === 'posts' && $parts[1] === 'edit') {
            // Yazı düzenleme formu
            $controller->edit($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'posts' && $parts[1] === 'update') {
            // Yazı güncelleme
            $controller->update($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'posts' && $parts[1] === 'delete') {
            // Yazı silme
            $controller->delete($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'posts' && $parts[1] === 'toggle') {
            // Yazı durum değiştirme
            $controller->toggleStatus($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'posts' && $parts[1] === 'restore') {
            // Yazı geri yükleme (çöpten)
            $controller->restore($parts[2]);
        // Versiyon route'ları
        } else if (count($parts) === 3 && $parts[0] === 'posts' && $parts[1] === 'versions') {
            // Versiyon geçmişi
            $controller->versions($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'posts' && $parts[1] === 'view-version') {
            // Versiyon detayı (AJAX)
            $controller->viewVersion($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'posts' && $parts[1] === 'restore-version') {
            // Eski versiyona geri dön
            $controller->restoreVersion($parts[2]);
        // Kategori route'ları
        } else if (count($parts) === 3 && $parts[0] === 'posts' && $parts[1] === 'category' && $parts[2] === 'create') {
            // Yeni kategori oluşturma formu
            $controller->createCategory();
        } else if (count($parts) === 3 && $parts[0] === 'posts' && $parts[1] === 'category' && $parts[2] === 'store') {
            // Kategori kaydetme
            $controller->storeCategory();
        } else if (count($parts) === 4 && $parts[0] === 'posts' && $parts[1] === 'category' && $parts[2] === 'edit') {
            // Kategori düzenleme formu
            $controller->editCategory($parts[3]);
        } else if (count($parts) === 4 && $parts[0] === 'posts' && $parts[1] === 'category' && $parts[2] === 'update') {
            // Kategori güncelleme
            $controller->updateCategory($parts[3]);
        } else if (count($parts) === 4 && $parts[0] === 'posts' && $parts[1] === 'category' && $parts[2] === 'delete') {
            // Kategori silme
            $controller->deleteCategory($parts[3]);
        } else {
            header("Location: " . admin_url('posts'));
            exit;
        }
    } catch (Exception $e) {
        die("Hata: " . $e->getMessage() . " - Dosya: " . $e->getFile() . " - Satır: " . $e->getLine());
    } catch (Error $e) {
        die("Fatal Hata: " . $e->getMessage() . " - Dosya: " . $e->getFile() . " - Satır: " . $e->getLine());
    }
    exit;
}

// Forms route'larını kontrol et
if (strpos($page, 'forms') === 0) {
    try {
        // Form model'lerini yükle
        require_once __DIR__ . '/../app/models/Form.php';
        require_once __DIR__ . '/../app/models/FormField.php';
        require_once __DIR__ . '/../app/models/FormSubmission.php';
        
        $controllerFile = __DIR__ . '/../app/controllers/FormController.php';
        if (!file_exists($controllerFile)) {
            die("FormController bulunamadı: " . $controllerFile);
        }
        
        require $controllerFile;
        
        if (!class_exists('FormController')) {
            die("FormController sınıfı bulunamadı");
        }
        
        $controller = new FormController();
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Forms route'larını parse et
        $parts = explode('/', $page);
        
        if (count($parts) === 1 && $parts[0] === 'forms') {
            // Form listesi
            $controller->index();
        } else if (count($parts) === 2 && $parts[0] === 'forms' && $parts[1] === 'create') {
            // Yeni form oluşturma formu
            $controller->create();
        } else if (count($parts) === 2 && $parts[0] === 'forms' && $parts[1] === 'store') {
            // Form kaydetme
            $controller->store();
        } else if (count($parts) === 3 && $parts[0] === 'forms' && $parts[1] === 'edit') {
            // Form düzenleme formu
            $controller->edit($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'forms' && $parts[1] === 'update') {
            // Form güncelleme
            $controller->update($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'forms' && $parts[1] === 'delete') {
            // Form silme
            $controller->delete($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'forms' && $parts[1] === 'toggle') {
            // Form durum değiştirme
            $controller->toggleStatus($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'forms' && $parts[1] === 'preview') {
            // Form önizleme
            $controller->preview($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'forms' && $parts[1] === 'submissions') {
            // Form gönderimleri
            $controller->submissions($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'forms' && $parts[1] === 'export') {
            // Gönderimleri dışa aktar
            $controller->exportSubmissions($parts[2]);
        // Alan işlemleri (AJAX)
        } else if (count($parts) === 2 && $parts[0] === 'forms' && $parts[1] === 'add-field') {
            $controller->addField();
        } else if (count($parts) === 3 && $parts[0] === 'forms' && $parts[1] === 'update-field') {
            $controller->updateField($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'forms' && $parts[1] === 'delete-field') {
            $controller->deleteField($parts[2]);
        } else if (count($parts) === 2 && $parts[0] === 'forms' && $parts[1] === 'update-field-order') {
            $controller->updateFieldOrder();
        } else if (count($parts) === 3 && $parts[0] === 'forms' && $parts[1] === 'get-field') {
            $controller->getField($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'forms' && $parts[1] === 'get-preview-html') {
            $controller->getPreviewHtml($parts[2]);
        // Gönderim işlemleri (AJAX)
        } else if (count($parts) === 3 && $parts[0] === 'forms' && $parts[1] === 'view-submission') {
            $controller->viewSubmission($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'forms' && $parts[1] === 'update-submission-status') {
            $controller->updateSubmissionStatus($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'forms' && $parts[1] === 'delete-submission') {
            $controller->deleteSubmission($parts[2]);
        } else if (count($parts) === 2 && $parts[0] === 'forms' && $parts[1] === 'bulk-submission-action') {
            $controller->bulkSubmissionAction();
        // Frontend form gönderimi
        } else if (count($parts) === 2 && $parts[0] === 'forms' && $parts[1] === 'submit') {
            $controller->submit();
        } else {
            header("Location: " . admin_url('forms'));
            exit;
        }
    } catch (Exception $e) {
        die("Hata: " . $e->getMessage() . " - Dosya: " . $e->getFile() . " - Satır: " . $e->getLine());
    } catch (Error $e) {
        die("Fatal Hata: " . $e->getMessage() . " - Dosya: " . $e->getFile() . " - Satır: " . $e->getLine());
    }
    exit;
}

// Agreements route'larını kontrol et
if (strpos($page, 'agreements') === 0) {
    try {
        // Agreement model'lerini yükle
        require_once __DIR__ . '/../app/models/Agreement.php';
        require_once __DIR__ . '/../app/models/AgreementVersion.php';
        
        $controllerFile = __DIR__ . '/../app/controllers/AgreementController.php';
        if (!file_exists($controllerFile)) {
            die("AgreementController bulunamadı: " . $controllerFile);
        }
        
        require $controllerFile;
        
        if (!class_exists('AgreementController')) {
            die("AgreementController sınıfı bulunamadı");
        }
        
        $controller = new AgreementController();
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Agreements route'larını parse et
        $parts = explode('/', $page);
        
        if (count($parts) === 1 && $parts[0] === 'agreements') {
            // Sözleşme listesi
            $controller->index();
        } else if (count($parts) === 2 && $parts[0] === 'agreements' && $parts[1] === 'create') {
            // Yeni sözleşme oluşturma formu
            $controller->create();
        } else if (count($parts) === 2 && $parts[0] === 'agreements' && $parts[1] === 'store') {
            // Sözleşme kaydetme
            $controller->store();
        } else if (count($parts) === 3 && $parts[0] === 'agreements' && $parts[1] === 'edit') {
            // Sözleşme düzenleme formu
            $controller->edit($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'agreements' && $parts[1] === 'update') {
            // Sözleşme güncelleme
            $controller->update($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'agreements' && $parts[1] === 'delete') {
            // Sözleşme silme
            $controller->delete($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'agreements' && $parts[1] === 'toggle') {
            // Sözleşme durum değiştirme
            $controller->toggleStatus($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'agreements' && $parts[1] === 'versions') {
            // Versiyon geçmişi
            $controller->versions($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'agreements' && $parts[1] === 'view-version') {
            // Versiyon detayı (AJAX)
            $controller->viewVersion($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'agreements' && $parts[1] === 'restore-version') {
            // Eski versiyona geri dön
            $controller->restoreVersion($parts[2]);
        } else {
            header("Location: " . admin_url('agreements'));
            exit;
        }
    } catch (Exception $e) {
        die("Hata: " . $e->getMessage() . " - Dosya: " . $e->getFile() . " - Satır: " . $e->getLine());
    } catch (Error $e) {
        die("Fatal Hata: " . $e->getMessage() . " - Dosya: " . $e->getFile() . " - Satır: " . $e->getLine());
    }
    exit;
}

// Themes route'larını kontrol et
if (strpos($page, 'themes') === 0) {
    try {
        $controllerFile = __DIR__ . '/../app/controllers/ThemeController.php';
        if (!file_exists($controllerFile)) {
            die("ThemeController bulunamadı: " . $controllerFile);
        }
        
        require $controllerFile;
        
        if (!class_exists('ThemeController')) {
            die("ThemeController sınıfı bulunamadı");
        }
        
        $controller = new ThemeController();
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Themes route'larını parse et
        $parts = explode('/', $page);
        
        if (count($parts) === 1 && $parts[0] === 'themes') {
            // Tema listesi
            $controller->index();
        } else if (count($parts) === 2 && $parts[0] === 'themes' && $parts[1] === 'customize') {
            // Tema özelleştirici (aktif tema)
            $controller->customize();
        } else if (count($parts) === 3 && $parts[0] === 'themes' && $parts[1] === 'customize') {
            // Tema özelleştirici (slug ile)
            $controller->customize($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'themes' && $parts[1] === 'activate') {
            // Tema aktifleştir
            $controller->activate($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'themes' && $parts[1] === 'install') {
            // Tema kur
            $controller->install($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'themes' && $parts[1] === 'uninstall') {
            // Tema kaldır
            $controller->uninstall($parts[2]);
        } else if (count($parts) === 2 && $parts[0] === 'themes' && $parts[1] === 'upload') {
            // ZIP'ten tema yükle
            $controller->upload();
        } else if (count($parts) === 3 && $parts[0] === 'themes' && $parts[1] === 'download') {
            // Temayı ZIP olarak indir
            $controller->download($parts[2]);
        } else if (count($parts) === 2 && $parts[0] === 'themes' && $parts[1] === 'saveSettings') {
            // Tema ayarlarını kaydet (AJAX)
            $controller->saveSettings();
        } else if (count($parts) === 2 && $parts[0] === 'themes' && $parts[1] === 'saveCustomCode') {
            // Özel kod kaydet (AJAX)
            $controller->saveCustomCode();
        } else if (count($parts) === 2 && $parts[0] === 'themes' && $parts[1] === 'saveSection') {
            // Section kaydet (AJAX)
            $controller->saveSection();
        } else if (count($parts) === 3 && $parts[0] === 'themes' && $parts[1] === 'deleteSection') {
            // Section sil (AJAX)
            $controller->deleteSection($parts[2]);
        } else if (count($parts) === 2 && $parts[0] === 'themes' && $parts[1] === 'updateSectionOrder') {
            // Section sıralaması (AJAX)
            $controller->updateSectionOrder();
        } else if (count($parts) === 3 && $parts[0] === 'themes' && $parts[1] === 'getSections') {
            // Section'ları getir (AJAX)
            $controller->getSections($parts[2]);
        } else if (count($parts) === 2 && $parts[0] === 'themes' && $parts[1] === 'getSectionData') {
            // Tek section verisini getir (AJAX)
            $controller->getSectionData();
        } else if (count($parts) === 2 && $parts[0] === 'themes' && $parts[1] === 'preview') {
            // Önizleme (aktif tema)
            $controller->preview();
        } else if (count($parts) === 3 && $parts[0] === 'themes' && $parts[1] === 'preview') {
            // Önizleme (slug ile)
            $controller->preview($parts[2]);
        } else {
            header("Location: " . admin_url('themes'));
            exit;
        }
    } catch (Exception $e) {
        die("Hata: " . $e->getMessage() . " - Dosya: " . $e->getFile() . " - Satır: " . $e->getLine());
    } catch (Error $e) {
        die("Fatal Hata: " . $e->getMessage() . " - Dosya: " . $e->getFile() . " - Satır: " . $e->getLine());
    }
    exit;
}

// Modules route'larını kontrol et
if (strpos($page, 'modules') === 0) {
    try {
        $controllerFile = __DIR__ . '/../app/controllers/ModuleController.php';
        if (!file_exists($controllerFile)) {
            die("ModuleController bulunamadı: " . $controllerFile);
        }
        
        require $controllerFile;
        
        if (!class_exists('ModuleController')) {
            die("ModuleController sınıfı bulunamadı");
        }
        
        $controller = new ModuleController();
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Modules route'larını parse et
        $parts = explode('/', $page);
        
        if (count($parts) === 1 && $parts[0] === 'modules') {
            // Modül listesi
            $controller->index();
        } else if (count($parts) === 3 && $parts[0] === 'modules' && $parts[1] === 'install') {
            // Modül kur ve aktif et
            $controller->install($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'modules' && $parts[1] === 'activate') {
            // Modül aktif et
            $controller->activate($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'modules' && $parts[1] === 'deactivate') {
            // Modül devre dışı bırak
            $controller->deactivate($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'modules' && $parts[1] === 'delete') {
            // Modül sil
            $controller->delete($parts[2]);
        } else if (count($parts) === 2 && $parts[0] === 'modules' && $parts[1] === 'upload') {
            // Modül yükle
            $controller->upload();
        } else if (count($parts) === 3 && $parts[0] === 'modules' && $parts[1] === 'settings') {
            // Modül ayarları
            $controller->settings($parts[2]);
        } else if (count($parts) === 3 && $parts[0] === 'modules' && $parts[1] === 'detail') {
            // Modül detayı (AJAX)
            $controller->detail($parts[2]);
        } else if (count($parts) >= 2 && $parts[0] === 'modules' && $parts[1] === 'logs') {
            // Modül logları (AJAX)
            $moduleName = $parts[2] ?? null;
            $controller->logs($moduleName);
        } else {
            header("Location: " . admin_url('modules'));
            exit;
        }
    } catch (Exception $e) {
        die("Hata: " . $e->getMessage() . " - Dosya: " . $e->getFile() . " - Satır: " . $e->getLine());
    } catch (Error $e) {
        die("Fatal Hata: " . $e->getMessage() . " - Dosya: " . $e->getFile() . " - Satır: " . $e->getLine());
    }
    exit;
}

// Dinamik Modül route'larını kontrol et (module/modul-adi/action/param)
if (strpos($page, 'module/') === 0) {
    try {
        $moduleLoader = ModuleLoader::getInstance();
        
        if ($moduleLoader->handleAdminRoute($page)) {
            exit;
        }
        
        // Route işlenemezse 404
        $_SESSION['flash_message'] = 'Modül sayfası bulunamadı';
        $_SESSION['flash_type'] = 'error';
        header("Location: " . admin_url('modules'));
        exit;
        
    } catch (Exception $e) {
        die("Hata: " . $e->getMessage() . " - Dosya: " . $e->getFile() . " - Satır: " . $e->getLine());
    } catch (Error $e) {
        die("Fatal Hata: " . $e->getMessage() . " - Dosya: " . $e->getFile() . " - Satır: " . $e->getLine());
    }
    exit;
}

// Controller dosyasını yükle
$controllerFile = __DIR__ . '/../app/controllers/AdminController.php';
if (!file_exists($controllerFile)) {
    die("AdminController bulunamadı");
}

require $controllerFile;

if (!class_exists('AdminController')) {
    die("AdminController sınıfı bulunamadı");
}

$controller = new AdminController();
$method = $_SERVER['REQUEST_METHOD'];

// Route'ları kontrol et ve ilgili action'ı çalıştır
switch ($page) {
    case 'login':
        $controller->login();
        break;
    case 'logout':
        if ($method === 'GET') {
            $controller->logout();
        }
        break;
    case 'dashboard':
        if ($method === 'GET') {
            $controller->dashboard();
        }
        break;
    case 'settings':
        $controller->settings();
        break;
    case 'upload_logo':
        $controller->upload_logo();
        break;
    case 'upload_favicon':
        $controller->upload_favicon();
        break;
    case 'design':
        $controller->design();
        break;
    case 'design-assets':
        $controller->design_assets();
        break;
    case 'smtp-settings':
        $controller->smtp_settings();
        break;
    case 'test_smtp_connection':
        $controller->test_smtp_connection();
        break;
    case 'send_test_email':
        $controller->send_test_email();
        break;
    default:
        // Bilinmeyen sayfa için login'e yönlendir
        // Debug: Hangi page değeri geldi?
        error_log("Admin: Unknown page parameter: " . $page);
        header("Location: " . admin_url('login'));
        exit;
}

