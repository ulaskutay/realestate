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
     * Kullanıcının belirli bir yetkisi var mı kontrol eder
     */
    public function hasPermission($userId, $permission) {
        require_once __DIR__ . '/../../core/Role.php';
        $role = $this->getRole($userId);
        return Role::hasPermission($role, $permission);
    }
    
    /**
     * Belirli role sahip kullanıcıları getirir
     */
    public function getByRole($role) {
        return $this->where('role', $role);
    }
    
    /**
     * Kullanıcının rolünü günceller
     */
    public function updateRole($userId, $newRole) {
        require_once __DIR__ . '/../../core/Role.php';
        
        // Rol geçerli mi kontrol et
        if (!Role::exists($newRole)) {
            return false;
        }
        
        return $this->update($userId, ['role' => $newRole]);
    }
}

