<?php
/**
 * User Model
 * Kullanıcı işlemleri için model sınıfı
 */

class User extends Model {
    protected $table = 'users';
    
    /**
     * Kullanıcı adı veya e-posta ile kullanıcı bulur
     */
    public function findByUsernameOrEmail($username) {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE username = ? OR email = ? LIMIT 1",
            [$username, $username]
        );
    }
    
    /**
     * Şifre doğrulama
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Şifre hash'leme
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Kullanıcı oluşturur
     */
    public function createUser($data) {
        // Şifreyi hash'le
        if (isset($data['password'])) {
            $data['password'] = $this->hashPassword($data['password']);
        }
        
        return $this->create($data);
    }
    
    /**
     * Kullanıcı günceller
     */
    public function updateUser($id, $data) {
        // Eğer şifre güncelleniyorsa hash'le
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = $this->hashPassword($data['password']);
        } else {
            // Şifre güncellenmiyorsa kaldır
            unset($data['password']);
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Aktif kullanıcıları getirir
     */
    public function getActiveUsers() {
        return $this->where('status', 'active');
    }
    
    /**
     * Admin kullanıcıları getirir
     */
    public function getAdmins() {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE role = 'admin' AND status = 'active'"
        );
    }
    
    /**
     * Tüm kullanıcıları getirir (sıralı)
     */
    public function getAll() {
        return $this->all('created_at DESC');
    }
    
    /**
     * Kullanıcının rolünü getirir
     */
    public function getRole($userId) {
        $user = $this->find($userId);
        return $user['role'] ?? 'user';
    }
    
    /**
     * Kullanıcının belirli bir yetkisi var mı (modül bazlı: permission "module.action" formatında)
     */
    public function hasPermission($userId, $permission) {
        $role = strtolower(trim($this->getRole($userId)));
        if ($role === 'super_admin') return true;
        $allowed = get_role_allowed_modules($role);
        if ($allowed === null) return true;
        $parts = explode('.', $permission, 2);
        $module = isset($parts[0]) ? trim($parts[0]) : '';
        return $module !== '' && in_array($module, $allowed, true);
    }
    
    /**
     * Belirli role sahip kullanıcıları getirir
     */
    public function getByRole($role) {
        return $this->where('role', $role);
    }
    
    /**
     * Kullanıcının rolünü günceller (geçerli rol listesi: get_available_role_options ile uyumlu)
     */
    public function updateRole($userId, $newRole) {
        $newRole = is_string($newRole) ? trim($newRole) : '';
        if ($newRole === '') {
            return false;
        }
        return $this->update($userId, ['role' => $newRole]);
    }
}

