<?php
/**
 * Role Controller
 * Rol yönetimi için controller
 */

class RoleController extends Controller {
    private $roleModel;
    private $moduleModel;
    
    public function __construct() {
        $this->roleModel = new RoleModel();
        $this->moduleModel = new ModuleModel();
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
     * Yetki kontrolü - sadece admin rol yönetimi yapabilir
     */
    private function checkPermission() {
        $this->checkAuth();
        
        $user = get_logged_in_user();
        $role = strtolower(trim($user['role'] ?? 'user'));
        
        if ($role !== 'super_admin' && $role !== 'admin') {
            $_SESSION['error_message'] = 'Bu modülde yetkiniz yoktur!';
            $this->redirect(admin_url('dashboard'));
        }
    }
    
    /**
     * Rol listesi - users?tab=roles'a yönlendir
     */
    public function index() {
        // Roller artık users sayfasında tab olarak gösteriliyor
        $this->redirect(admin_url('users', ['tab' => 'roles']));
    }
    
    /**
     * Yeni rol oluşturma formu
     */
    public function create() {
        $this->checkPermission();
        
        // Tüm modülleri yetkileriyle birlikte getir
        $modules = $this->moduleModel->getAllWithPermissions();
        
        $data = [
            'title' => 'Yeni Rol Oluştur',
            'user' => get_logged_in_user(),
            'role' => null,
            'modules' => $modules
        ];
        
        $this->view('admin/roles/create', $data);
    }
    
    /**
     * Rol kaydetme (POST)
     */
    public function store() {
        $this->checkPermission();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('roles'));
        }
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'slug' => $_POST['slug'] ?? '',
            'description' => $_POST['description'] ?? '',
            'is_system' => 0,
            'is_default' => isset($_POST['is_default']) ? 1 : 0,
            'permissions' => $_POST['permissions'] ?? []
        ];
        
        // Validasyon
        if (empty($data['name'])) {
            $_SESSION['role_message'] = 'Rol adı zorunludur!';
            $_SESSION['role_message_type'] = 'error';
            $this->redirect(admin_url('roles/create'));
        }
        
        // Rol oluştur
        $roleId = $this->roleModel->createRole($data);
        
        if ($roleId) {
            // Role cache'ini temizle
            require_once __DIR__ . '/../../core/Role.php';
            Role::clearCache();
            
            $_SESSION['role_message'] = 'Rol başarıyla oluşturuldu!';
            $_SESSION['role_message_type'] = 'success';
            $this->redirect(admin_url('roles'));
        } else {
            $_SESSION['role_message'] = 'Rol oluşturulurken bir hata oluştu!';
            $_SESSION['role_message_type'] = 'error';
            $this->redirect(admin_url('roles/create'));
        }
    }
    
    /**
     * Rol düzenleme formu
     */
    public function edit($id) {
        $this->checkPermission();
        
        $role = $this->roleModel->findWithPermissions($id);
        
        if (!$role) {
            $_SESSION['role_message'] = 'Rol bulunamadı!';
            $_SESSION['role_message_type'] = 'error';
            $this->redirect(admin_url('roles'));
        }
        
        // Tüm modülleri yetkileriyle birlikte getir
        $modules = $this->moduleModel->getAllWithPermissions();
        
        $data = [
            'title' => 'Rol Düzenle',
            'user' => get_logged_in_user(),
            'role' => $role,
            'modules' => $modules,
            'message' => $_SESSION['role_message'] ?? null,
            'messageType' => $_SESSION['role_message_type'] ?? null
        ];
        
        // Session mesajlarını temizle
        unset($_SESSION['role_message']);
        unset($_SESSION['role_message_type']);
        
        $this->view('admin/roles/edit', $data);
    }
    
    /**
     * Rol güncelleme (POST)
     */
    public function update($id) {
        $this->checkPermission();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('roles'));
        }
        
        $role = $this->roleModel->find($id);
        
        if (!$role) {
            $_SESSION['role_message'] = 'Rol bulunamadı!';
            $_SESSION['role_message_type'] = 'error';
            $this->redirect(admin_url('roles'));
        }
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'slug' => $_POST['slug'] ?? '',
            'description' => $_POST['description'] ?? '',
            'permissions' => $_POST['permissions'] ?? []
        ];
        
        // Sistem rolleri için bazı alanlar değiştirilemez
        if (!empty($role['is_system']) && $role['is_system'] == 1) {
            // Sistem rolleri için slug değiştirilemez
            unset($data['slug']);
        }
        
        // Validasyon
        if (empty($data['name'])) {
            $_SESSION['role_message'] = 'Rol adı zorunludur!';
            $_SESSION['role_message_type'] = 'error';
            $this->redirect(admin_url('roles/edit/' . $id));
        }
        
        // Rol güncelle
        $result = $this->roleModel->updateRole($id, $data);
        
        if ($result) {
            // Role cache'ini temizle
            require_once __DIR__ . '/../../core/Role.php';
            Role::clearCache();
            
            $_SESSION['role_message'] = 'Rol başarıyla güncellendi!';
            $_SESSION['role_message_type'] = 'success';
            $this->redirect(admin_url('roles'));
        } else {
            $_SESSION['role_message'] = 'Rol güncellenirken bir hata oluştu!';
            $_SESSION['role_message_type'] = 'error';
            $this->redirect(admin_url('roles/edit/' . $id));
        }
    }
    
    /**
     * Rol silme
     */
    public function delete($id) {
        $this->checkPermission();
        
        $role = $this->roleModel->find($id);
        
        if (!$role) {
            $_SESSION['role_message'] = 'Rol bulunamadı!';
            $_SESSION['role_message_type'] = 'error';
        } else {
            $result = $this->roleModel->deleteRole($id);
            
            if ($result) {
                // Role cache'ini temizle
                require_once __DIR__ . '/../../core/Role.php';
                Role::clearCache();
                
                $_SESSION['role_message'] = 'Rol başarıyla silindi!';
                $_SESSION['role_message_type'] = 'success';
            } else {
                $_SESSION['role_message'] = 'Sistem rolleri silinemez!';
                $_SESSION['role_message_type'] = 'error';
            }
        }
        
        $this->redirect(admin_url('roles'));
    }
}

