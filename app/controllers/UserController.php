<?php
/**
 * User Controller - Basit ve Çalışan Versiyon
 */

class UserController extends Controller {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Giriş kontrolü - Giriş yapmamışsa login'e yönlendir
     */
    private function requireLogin() {
        if (!is_user_logged_in()) {
            $this->redirect(admin_url('login'));
            exit;
        }
    }
    
    /**
     * Admin kontrolü - Admin değilse dashboard'a yönlendir
     */
    private function requireAdmin() {
        $this->requireLogin();
        
        $user = get_logged_in_user();
        $role = strtolower(trim($user['role'] ?? 'user'));
        
        if ($role !== 'admin' && $role !== 'super_admin') {
            $_SESSION['error_message'] = 'Bu sayfaya erişim yetkiniz yok!';
            $this->redirect(admin_url('dashboard'));
            exit;
        }
    }
    
    /**
     * Yetki kontrolü - Yetkisi yoksa mesaj göster
     */
    private function checkPermission($permission) {
        $this->requireLogin();
        
        if (!current_user_can($permission)) {
            $_SESSION['error_message'] = 'Bu modülde yetkiniz yoktur!';
            $this->redirect(admin_url('dashboard'));
            exit;
        }
    }
    
    /**
     * Kullanıcı listesi - Tab sistemi (Kullanıcılar ve Roller)
     */
    public function index() {
        $this->checkPermission('users.view');
        
        $user = get_logged_in_user();
        $role = strtolower(trim($user['role'] ?? 'user'));
        $isAdmin = ($role === 'admin' || $role === 'super_admin');
        
        $activeTab = $_GET['tab'] ?? 'users';
        
        // Roller sekmesi sadece admin için
        if ($activeTab === 'roles' && !$isAdmin) {
            $activeTab = 'users';
        }
        
        $users = $this->userModel->getAll();
        
        // Roller verisi - HER ZAMAN yükle (admin için)
        $roles = [];
        if ($isAdmin) {
            try {
                require_once __DIR__ . '/../models/RoleModel.php';
                $roleModel = new RoleModel();
                $roles = $roleModel->getAll();
                if (!empty($roles)) {
                    foreach ($roles as &$r) {
                        $r['user_count'] = $roleModel->getUserCount($r['id']);
                    }
                }
            } catch (Exception $e) {
                // Roller tablosu yoksa boş array kullan
                $roles = [];
            }
        }
        
        $data = [
            'title' => 'Kullanıcı ve Rol Yönetimi',
            'user' => $user,
            'users' => $users,
            'roles' => $roles,
            'activeTab' => $activeTab,
            'isAdmin' => $isAdmin,
            'message' => $_SESSION['user_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['user_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null)
        ];
        
        unset($_SESSION['user_message'], $_SESSION['error_message']);
        
        $this->view('admin/users/index', $data);
    }
    
    /**
     * Yeni kullanıcı formu
     */
    public function create() {
        $this->checkPermission('users.create');
        
        $data = [
            'title' => 'Yeni Kullanıcı',
            'user' => get_logged_in_user()
        ];
        
        $this->view('admin/users/create', $data);
    }
    
    /**
     * Kullanıcı kaydet
     */
    public function store() {
        $this->checkPermission('users.create');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('users'));
            return;
        }
        
        $data = [
            'username' => trim($_POST['username'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'role' => $_POST['role'] ?? 'user',
            'status' => ($_POST['status'] ?? '') === 'active' ? 'active' : 'inactive'
        ];
        
        // Validasyon
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            $_SESSION['error_message'] = 'Tüm alanları doldurun!';
            $this->redirect(admin_url('users/create'));
            return;
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_message'] = 'Geçersiz e-posta!';
            $this->redirect(admin_url('users/create'));
            return;
        }
        
        // Kullanıcı adı/email kontrolü
        if ($this->userModel->findByUsernameOrEmail($data['username'])) {
            $_SESSION['error_message'] = 'Bu kullanıcı adı zaten var!';
            $this->redirect(admin_url('users/create'));
            return;
        }
        
        if ($this->userModel->findByUsernameOrEmail($data['email'])) {
            $_SESSION['error_message'] = 'Bu e-posta zaten var!';
            $this->redirect(admin_url('users/create'));
            return;
        }
        
        $userId = $this->userModel->createUser($data);
        
        if ($userId) {
            $_SESSION['user_message'] = 'Kullanıcı oluşturuldu!';
        } else {
            $_SESSION['error_message'] = 'Hata oluştu!';
        }
        
        $this->redirect(admin_url('users'));
    }
    
    /**
     * Kullanıcı düzenle
     */
    public function edit($id) {
        $this->checkPermission('users.edit');
        
        $userData = $this->userModel->find($id);
        
        if (!$userData) {
            $_SESSION['error_message'] = 'Kullanıcı bulunamadı!';
            $this->redirect(admin_url('users'));
            return;
        }
        
        unset($userData['password']);
        
        $data = [
            'title' => 'Kullanıcı Düzenle',
            'user' => get_logged_in_user(),
            'user_data' => $userData,
            'message' => $_SESSION['user_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['user_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null)
        ];
        
        unset($_SESSION['user_message'], $_SESSION['error_message']);
        
        $this->view('admin/users/edit', $data);
    }
    
    /**
     * Kullanıcı güncelle
     */
    public function update($id) {
        $this->checkPermission('users.edit');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('users'));
            return;
        }
        
        $userData = $this->userModel->find($id);
        
        if (!$userData) {
            $_SESSION['error_message'] = 'Kullanıcı bulunamadı!';
            $this->redirect(admin_url('users'));
            return;
        }
        
        $data = [
            'username' => trim($_POST['username'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'role' => $_POST['role'] ?? $userData['role'],
            'status' => ($_POST['status'] ?? '') === 'active' ? 'active' : 'inactive'
        ];
        
        // Şifre varsa ekle
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }
        
        // Validasyon
        if (empty($data['username']) || empty($data['email'])) {
            $_SESSION['error_message'] = 'Kullanıcı adı ve e-posta zorunlu!';
            $this->redirect(admin_url('users/edit/' . $id));
            return;
        }
        
        // Kullanıcı adı/email kontrolü (kendi hariç)
        $existing = $this->userModel->findByUsernameOrEmail($data['username']);
        if ($existing && $existing['id'] != $id) {
            $_SESSION['error_message'] = 'Bu kullanıcı adı zaten var!';
            $this->redirect(admin_url('users/edit/' . $id));
            return;
        }
        
        $existing = $this->userModel->findByUsernameOrEmail($data['email']);
        if ($existing && $existing['id'] != $id) {
            $_SESSION['error_message'] = 'Bu e-posta zaten var!';
            $this->redirect(admin_url('users/edit/' . $id));
            return;
        }
        
        $result = $this->userModel->updateUser($id, $data);
        
        if ($result) {
            $_SESSION['user_message'] = 'Kullanıcı güncellendi!';
        } else {
            $_SESSION['error_message'] = 'Hata oluştu!';
        }
        
        $this->redirect(admin_url('users'));
    }
    
    /**
     * Kullanıcı sil
     */
    public function delete($id) {
        $this->checkPermission('users.delete');
        
        $currentUser = get_logged_in_user();
        
        if ($currentUser['id'] == $id) {
            $_SESSION['error_message'] = 'Kendi hesabınızı silemezsiniz!';
            $this->redirect(admin_url('users'));
            return;
        }
        
        $userData = $this->userModel->find($id);
        
        if (!$userData) {
            $_SESSION['error_message'] = 'Kullanıcı bulunamadı!';
            $this->redirect(admin_url('users'));
            return;
        }
        
        $this->userModel->delete($id);
        $_SESSION['user_message'] = 'Kullanıcı silindi!';
        
        $this->redirect(admin_url('users'));
    }
    
    /**
     * Kullanıcı durumunu değiştir
     */
    public function toggleStatus($id) {
        $this->checkPermission('users.edit');
        
        $currentUser = get_logged_in_user();
        
        if ($currentUser['id'] == $id) {
            $_SESSION['error_message'] = 'Kendi hesabınızı pasifleştiremezsiniz!';
            $this->redirect(admin_url('users'));
            return;
        }
        
        $userData = $this->userModel->find($id);
        
        if (!$userData) {
            $_SESSION['error_message'] = 'Kullanıcı bulunamadı!';
            $this->redirect(admin_url('users'));
            return;
        }
        
        $newStatus = $userData['status'] === 'active' ? 'inactive' : 'active';
        $this->userModel->update($id, ['status' => $newStatus]);
        
        $_SESSION['user_message'] = 'Kullanıcı durumu güncellendi!';
        $this->redirect(admin_url('users'));
    }
}
