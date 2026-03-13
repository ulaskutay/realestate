<?php
/**
 * Role Controller - Basit rol ve modül yetki yönetimi
 * Sadece süper admin rol yönetimine erişebilir.
 */

class RoleController extends Controller {
    private $roleModel;

    public function __construct() {
        $this->roleModel = new RoleModel();
    }

    private function requireSuperAdmin() {
        if (!is_user_logged_in()) {
            $this->redirect(admin_url('login'));
            exit;
        }
        if (!is_super_admin()) {
            $_SESSION['error_message'] = 'Bu sayfaya sadece süper admin erişebilir.';
            $this->redirect(admin_url('dashboard'));
            exit;
        }
    }

    public function index() {
        $this->requireSuperAdmin();
        $roles = $this->roleModel->getAll();
        foreach ($roles as &$r) {
            $r['user_count'] = $this->roleModel->getUserCount($r['id']);
        }
        $data = [
            'title' => 'Roller',
            'user' => get_logged_in_user(),
            'roles' => $roles,
            'message' => $_SESSION['role_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['role_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null),
        ];
        unset($_SESSION['role_message'], $_SESSION['error_message']);
        $this->view('admin/roles/index', $data);
    }

    public function create() {
        $this->requireSuperAdmin();
        $data = [
            'title' => 'Yeni Rol',
            'user' => get_logged_in_user(),
            'role' => null,
            'assignableModules' => get_assignable_module_slugs(),
        ];
        $this->view('admin/roles/create', $data);
    }

    public function store() {
        $this->requireSuperAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('users', ['tab' => 'roles']));
            return;
        }
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $modules = isset($_POST['modules']) && is_array($_POST['modules']) ? array_values(array_filter(array_map('trim', $_POST['modules']))) : [];

        if ($name === '') {
            $_SESSION['role_message'] = 'Rol adı zorunludur.';
            $_SESSION['role_message_type'] = 'error';
            $this->redirect(admin_url('roles/create'));
            return;
        }

        $roleId = $this->roleModel->createRole([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'is_system' => 0,
        ]);

        if ($roleId) {
            $this->roleModel->setAllowedModules($roleId, $modules);
            $_SESSION['role_message'] = 'Rol oluşturuldu.';
            $_SESSION['role_message_type'] = 'success';
            $this->redirect(admin_url('users', ['tab' => 'roles']));
        } else {
            $_SESSION['role_message'] = 'Rol oluşturulamadı (slug zaten kullanılıyor olabilir).';
            $_SESSION['role_message_type'] = 'error';
            $this->redirect(admin_url('roles/create'));
        }
    }

    public function edit($id) {
        $this->requireSuperAdmin();
        $role = $this->roleModel->find($id);
        if (!$role) {
            $_SESSION['role_message'] = 'Rol bulunamadı.';
            $_SESSION['role_message_type'] = 'error';
            $this->redirect(admin_url('users', ['tab' => 'roles']));
            return;
        }
        $role['allowed_modules'] = $this->roleModel->getAllowedModules($id);
        $data = [
            'title' => 'Rol Düzenle',
            'user' => get_logged_in_user(),
            'role' => $role,
            'assignableModules' => get_assignable_module_slugs(),
        ];
        $this->view('admin/roles/edit', $data);
    }

    public function update($id) {
        $this->requireSuperAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('users', ['tab' => 'roles']));
            return;
        }
        $role = $this->roleModel->find($id);
        if (!$role) {
            $_SESSION['role_message'] = 'Rol bulunamadı.';
            $_SESSION['role_message_type'] = 'error';
            $this->redirect(admin_url('users', ['tab' => 'roles']));
            return;
        }
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $modules = isset($_POST['modules']) && is_array($_POST['modules']) ? array_values(array_filter(array_map('trim', $_POST['modules']))) : [];

        if ($name === '') {
            $_SESSION['role_message'] = 'Rol adı zorunludur.';
            $_SESSION['role_message_type'] = 'error';
            $this->redirect(admin_url('roles/edit/' . $id));
            return;
        }

        $updateData = ['name' => $name, 'description' => $description];
        if (empty($role['is_system'])) {
            $updateData['slug'] = $slug;
        }
        $this->roleModel->updateRole($id, $updateData);
        $this->roleModel->setAllowedModules($id, $modules);
        $_SESSION['role_message'] = 'Rol güncellendi.';
        $_SESSION['role_message_type'] = 'success';
        $this->redirect(admin_url('users', ['tab' => 'roles']));
    }

    public function delete($id) {
        $this->requireSuperAdmin();
        $role = $this->roleModel->find($id);
        if (!$role) {
            $_SESSION['role_message'] = 'Rol bulunamadı.';
            $_SESSION['role_message_type'] = 'error';
        } elseif ($this->roleModel->deleteRole($id)) {
            $_SESSION['role_message'] = 'Rol silindi.';
            $_SESSION['role_message_type'] = 'success';
        } else {
            $_SESSION['role_message'] = 'Sistem rolleri silinemez.';
            $_SESSION['role_message_type'] = 'error';
        }
        $this->redirect(admin_url('users', ['tab' => 'roles']));
    }
}
