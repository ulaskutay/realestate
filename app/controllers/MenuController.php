<?php
/**
 * Menu Controller
 * Menü yönetimi için controller - WordPress tarzı
 */

class MenuController extends Controller {
    private $menuModel;
    private $itemModel;
    private $db;
    
    public function __construct() {
        $this->menuModel = new Menu();
        $this->itemModel = new MenuItem();
        $this->db = Database::getInstance();
    }
    
    /**
     * Giriş kontrolü
     */
    private function checkAuth() {
        if (!is_user_logged_in()) {
            $this->redirect(admin_url('login'));
        }
    }
    
    /**
     * Menü listesi
     */
    public function index() {
        $this->checkAuth();
        
        // Yetki kontrolü
        if (!current_user_can('menus.view')) {
            $_SESSION['error_message'] = 'Bu modülde yetkiniz yoktur!';
            $this->redirect(admin_url('dashboard'));
        }
        
        $menus = $this->menuModel->getAll();
        
        // Her menü için item sayısını ekle
        foreach ($menus as &$menu) {
            $menu['item_count'] = $this->itemModel->countByMenuId($menu['id']);
        }
        
        $data = [
            'title' => 'Menü Yönetimi',
            'user' => get_logged_in_user(),
            'menus' => $menus,
            'locations' => $this->menuModel->getLocations(),
            'message' => $_SESSION['menu_message'] ?? null,
            'messageType' => $_SESSION['menu_message_type'] ?? null
        ];
        
        // Session mesajlarını temizle
        unset($_SESSION['menu_message']);
        unset($_SESSION['menu_message_type']);
        
        $this->view('admin/menus/index', $data);
    }
    
    /**
     * Yeni menü oluşturma formu - edit ile aynı view kullanır
     */
    public function create() {
        $this->checkAuth();
        
        // Sayfaları, yazıları ve kategorileri getir
        $pages = $this->getPages();
        $posts = $this->getPosts();
        $categories = $this->getCategories();
        
        $data = [
            'title' => 'Yeni Menü Oluştur',
            'user' => get_logged_in_user(),
            'menu' => ['id' => null, 'name' => '', 'location' => 'header'],
            'items' => [],
            'locations' => $this->menuModel->getLocations(),
            'pages' => $pages,
            'posts' => $posts,
            'categories' => $categories,
            'message' => $_SESSION['menu_message'] ?? null,
            'messageType' => $_SESSION['menu_message_type'] ?? null
        ];
        
        unset($_SESSION['menu_message']);
        unset($_SESSION['menu_message_type']);
        
        $this->view('admin/menus/edit', $data);
    }
    
    /**
     * Menü düzenleme formu
     */
    public function edit($id) {
        $this->checkAuth();
        
        $menu = $this->menuModel->find($id);
        
        if (!$menu) {
            $_SESSION['menu_message'] = 'Menü bulunamadı!';
            $_SESSION['menu_message_type'] = 'error';
            $this->redirect(admin_url('menus'));
        }
        
        // Menü öğelerini getir
        $items = $this->itemModel->getAllByMenuId($id);
        
        // Sayfaları, yazıları ve kategorileri getir
        $pages = $this->getPages();
        $posts = $this->getPosts();
        $categories = $this->getCategories();
        
        $data = [
            'title' => 'Menü Düzenle: ' . $menu['name'],
            'user' => get_logged_in_user(),
            'menu' => $menu,
            'items' => $items,
            'locations' => $this->menuModel->getLocations(),
            'pages' => $pages,
            'posts' => $posts,
            'categories' => $categories,
            'message' => $_SESSION['menu_message'] ?? null,
            'messageType' => $_SESSION['menu_message_type'] ?? null
        ];
        
        // Session mesajlarını temizle
        unset($_SESSION['menu_message']);
        unset($_SESSION['menu_message_type']);
        
        $this->view('admin/menus/edit', $data);
    }
    
    /**
     * Menü ve öğeleri tek seferde kaydet (AJAX)
     */
    public function save() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Geçersiz istek'], 400);
        }
        
        $menuId = !empty($_POST['menu_id']) ? (int)$_POST['menu_id'] : null;
        $name = trim($_POST['name'] ?? '');
        $location = trim($_POST['location'] ?? 'header');
        $itemsJson = $_POST['items'] ?? '[]';
        
        // Validasyon
        if (empty($name)) {
            $this->json(['success' => false, 'message' => 'Menü adı zorunludur'], 400);
        }
        
        // İşlemi başlat
        try {
            $this->db->beginTransaction();
            
            // Menü oluştur veya güncelle
            if ($menuId) {
                // Güncelle - aynı konumda başka menü var mı kontrol et
                $existingMenu = $this->db->fetch(
                    "SELECT id FROM menus WHERE location = ? AND id != ?",
                    [$location, $menuId]
                );
                
                if ($existingMenu) {
                    $this->db->rollBack();
                    $this->json(['success' => false, 'message' => 'Bu konumda zaten başka bir menü var'], 400);
                    return;
                }
                
                $this->menuModel->update($menuId, [
                    'name' => $name,
                    'location' => $location,
                    'status' => 'active'
                ]);
            } else {
                // Yeni menü oluştur - aynı konumda menü varsa onu kullan
                $existingMenu = $this->db->fetch(
                    "SELECT id FROM menus WHERE location = ?",
                    [$location]
                );
                
                if ($existingMenu) {
                    // Mevcut menüyü kullan
                    $menuId = $existingMenu['id'];
                    $this->menuModel->update($menuId, [
                        'name' => $name,
                        'status' => 'active'
                    ]);
                } else {
                    // Yeni oluştur
                    $menuId = $this->menuModel->create([
                        'name' => $name,
                        'location' => $location,
                        'description' => '',
                        'status' => 'active'
                    ]);
                }
            }
            
            // Mevcut öğeleri temizle
            $this->db->query("DELETE FROM menu_items WHERE menu_id = ?", [$menuId]);
            
            // Yeni öğeleri ekle
            $items = json_decode($itemsJson, true) ?: [];
            $idMap = []; // Eski ID -> Yeni ID eşleştirmesi
            
            foreach ($items as $item) {
                $parentId = null;
                
                // Parent ID'yi çözümle
                if (!empty($item['parent_id'])) {
                    if (is_numeric($item['parent_id'])) {
                        $parentId = isset($idMap[$item['parent_id']]) ? $idMap[$item['parent_id']] : (int)$item['parent_id'];
                    } elseif (strpos($item['parent_id'], 'temp_new_') === 0) {
                        $tempId = str_replace('temp_', '', $item['parent_id']);
                        $parentId = $idMap[$tempId] ?? null;
                    }
                }
                
                $newId = $this->itemModel->createItem([
                    'menu_id' => $menuId,
                    'parent_id' => $parentId,
                    'title' => $item['title'] ?? '',
                    'url' => $item['url'] ?? '#',
                    'target' => $item['target'] ?? '_self',
                    'icon' => $item['icon'] ?? '',
                    'css_class' => $item['css_class'] ?? '',
                    'type' => 'custom',
                    'status' => ($item['status'] ?? 'active') === 'active' ? 'active' : 'inactive',
                    'order' => $item['sort_order'] ?? 0
                ]);
                
                // ID eşleştirmesini kaydet
                if (!empty($item['id'])) {
                    $idMap[$item['id']] = $newId;
                }
            }
            
            $this->db->commit();
            
            $this->json([
                'success' => true,
                'message' => 'Menü başarıyla kaydedildi',
                'redirect' => admin_url('menus/edit/' . $menuId)
            ]);
            
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => 'Hata: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Eski store metodu - save'e yönlendir
     */
    public function store() {
        $this->save();
    }
    
    /**
     * Menü güncelleme - save metoduna yönlendir
     */
    public function update($id) {
        $_POST['menu_id'] = $id;
        $this->save();
    }
    
    /**
     * Menü silme (AJAX destekli)
     */
    public function delete($id) {
        $this->checkAuth();
        
        $menu = $this->menuModel->find($id);
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if (!$menu) {
            if ($isAjax) {
                $this->json(['success' => false, 'message' => 'Menü bulunamadı'], 404);
            }
            $_SESSION['menu_message'] = 'Menü bulunamadı!';
            $_SESSION['menu_message_type'] = 'error';
            $this->redirect(admin_url('menus'));
        }
        
        $this->menuModel->deleteMenu($id);
        
        if ($isAjax) {
            $this->json(['success' => true, 'message' => 'Menü başarıyla silindi']);
        }
        
        $_SESSION['menu_message'] = 'Menü başarıyla silindi!';
        $_SESSION['menu_message_type'] = 'success';
        $this->redirect(admin_url('menus'));
    }
    
    /**
     * Sayfaları getir (posts tablosundan type='page' olanlar)
     */
    private function getPages() {
        try {
            $stmt = $this->db->query("SELECT id, title, slug FROM posts WHERE status = 'publish' AND type = 'page' ORDER BY title ASC LIMIT 50");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Yazıları getir (posts tablosundan type='post' olanlar)
     */
    private function getPosts() {
        try {
            $stmt = $this->db->query("SELECT id, title, slug FROM posts WHERE status = 'publish' AND (type = 'post' OR type IS NULL) ORDER BY created_at DESC LIMIT 30");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Kategorileri getir
     */
    private function getCategories() {
        try {
            $stmt = $this->db->query("SELECT id, name, slug FROM categories WHERE status = 'active' ORDER BY name ASC LIMIT 50");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Menü öğesi ekleme (AJAX)
     */
    public function addItem() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Geçersiz istek'], 400);
        }
        
        $menuId = (int)($_POST['menu_id'] ?? 0);
        
        if (!$menuId || !$this->menuModel->find($menuId)) {
            $this->json(['success' => false, 'message' => 'Menü bulunamadı'], 404);
        }
        
        $data = [
            'menu_id' => $menuId,
            'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
            'title' => trim($_POST['title'] ?? ''),
            'url' => trim($_POST['url'] ?? '#'),
            'target' => $_POST['target'] ?? '_self',
            'icon' => trim($_POST['icon'] ?? ''),
            'css_class' => trim($_POST['css_class'] ?? ''),
            'type' => $_POST['type'] ?? 'custom',
            'type_id' => !empty($_POST['type_id']) ? (int)$_POST['type_id'] : null,
            'status' => isset($_POST['status']) && $_POST['status'] === 'active' ? 'active' : 'inactive'
        ];
        
        // Validasyon
        if (empty($data['title'])) {
            $this->json(['success' => false, 'message' => 'Başlık zorunludur'], 400);
        }
        
        $itemId = $this->itemModel->createItem($data);
        $item = $this->itemModel->find($itemId);
        
        $this->json([
            'success' => true,
            'message' => 'Menü öğesi başarıyla eklendi',
            'item' => $item
        ]);
    }
    
    /**
     * Menü öğesi güncelleme (AJAX)
     */
    public function updateItem($itemId) {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Geçersiz istek'], 400);
        }
        
        $item = $this->itemModel->find($itemId);
        
        if (!$item) {
            $this->json(['success' => false, 'message' => 'Öğe bulunamadı'], 404);
        }
        
        $data = [
            'parent_id' => isset($_POST['parent_id']) ? (!empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null) : $item['parent_id'],
            'title' => isset($_POST['title']) ? trim($_POST['title']) : $item['title'],
            'url' => isset($_POST['url']) ? trim($_POST['url']) : $item['url'],
            'target' => $_POST['target'] ?? $item['target'],
            'icon' => isset($_POST['icon']) ? trim($_POST['icon']) : $item['icon'],
            'css_class' => isset($_POST['css_class']) ? trim($_POST['css_class']) : $item['css_class'],
            'type' => $_POST['type'] ?? $item['type'],
            'type_id' => isset($_POST['type_id']) ? (!empty($_POST['type_id']) ? (int)$_POST['type_id'] : null) : $item['type_id'],
            'status' => isset($_POST['status']) ? ($_POST['status'] === 'active' ? 'active' : 'inactive') : $item['status']
        ];
        
        // Order varsa ekle
        if (isset($_POST['order'])) {
            $data['order'] = (int)$_POST['order'];
        }
        
        $this->itemModel->update($itemId, $data);
        $updatedItem = $this->itemModel->find($itemId);
        
        $this->json([
            'success' => true,
            'message' => 'Menü öğesi başarıyla güncellendi',
            'item' => $updatedItem
        ]);
    }
    
    /**
     * Menü öğesi silme (AJAX)
     */
    public function deleteItem($itemId) {
        $this->checkAuth();
        
        $item = $this->itemModel->find($itemId);
        
        if (!$item) {
            $this->json(['success' => false, 'message' => 'Öğe bulunamadı'], 404);
        }
        
        $this->itemModel->deleteWithChildren($itemId);
        
        $this->json([
            'success' => true,
            'message' => 'Menü öğesi başarıyla silindi'
        ]);
    }
    
    /**
     * Öğe sıralaması güncelleme (AJAX)
     */
    public function updateItemOrder() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Geçersiz istek'], 400);
        }
        
        $items = json_decode($_POST['items'] ?? '[]', true);
        
        if (empty($items) || !is_array($items)) {
            $this->json(['success' => false, 'message' => 'Geçersiz veri'], 400);
        }
        
        $this->itemModel->updateOrder($items);
        
        $this->json([
            'success' => true,
            'message' => 'Sıralama başarıyla güncellendi'
        ]);
    }
    
    /**
     * Menü öğelerini getir (AJAX)
     */
    public function getItems($menuId) {
        $this->checkAuth();
        
        $menu = $this->menuModel->find($menuId);
        
        if (!$menu) {
            $this->json(['success' => false, 'message' => 'Menü bulunamadı'], 404);
        }
        
        $items = $this->itemModel->getAllByMenuId($menuId);
        
        $this->json([
            'success' => true,
            'items' => $items
        ]);
    }
}

