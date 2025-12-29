<?php
/**
 * Role Model
 * Rol ve yetki yönetimi için model sınıfı
 */

class RoleModel extends Model {
    protected $table = 'roles';
    
    /**
     * Tüm rolleri getirir
     */
    public function getAll() {
        return $this->all('name ASC');
    }
    
    /**
     * Slug'a göre rol getirir
     */
    public function findBySlug($slug) {
        return $this->findOne('slug', $slug);
    }
    
    /**
     * Rol ve yetkilerini birlikte getirir
     */
    public function findWithPermissions($id) {
        $role = $this->find($id);
        if (!$role) {
            return null;
        }
        
        $permissions = $this->db->fetchAll(
            "SELECT permission, module FROM role_permissions WHERE role_id = ?",
            [$id]
        );
        
        $role['permissions'] = [];
        foreach ($permissions as $perm) {
            $role['permissions'][] = $perm['permission'];
        }
        
        return $role;
    }
    
    /**
     * Rol oluşturur
     */
    public function createRole($data) {
        // Slug oluştur
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['name']);
        }
        
        $roleId = $this->create($data);
        
        // Yetkileri ekle
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $this->setPermissions($roleId, $data['permissions']);
        }
        
        return $roleId;
    }
    
    /**
     * Rol günceller
     */
    public function updateRole($id, $data) {
        // Mevcut rolü al
        $currentRole = $this->find($id);
        
        // Slug sadece name değiştiğinde ve yeni slug verilmediyse oluştur
        if (isset($data['name']) && empty($data['slug'])) {
            // Eğer isim değiştiyse yeni slug oluştur
            if ($currentRole && $currentRole['name'] !== $data['name']) {
                $data['slug'] = $this->generateSlug($data['name'], $id);
            } else {
                // İsim değişmediyse slug'ı data'dan çıkar
                unset($data['slug']);
            }
        }
        
        // Yetkileri güncelle
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $this->setPermissions($id, $data['permissions']);
            unset($data['permissions']);
        }
        
        if (!empty($data)) {
            return $this->update($id, $data);
        }
        
        return true;
    }
    
    /**
     * Rol yetkilerini ayarlar
     */
    public function setPermissions($roleId, $permissions) {
        // Mevcut yetkileri sil
        $this->db->query(
            "DELETE FROM role_permissions WHERE role_id = ?",
            [$roleId]
        );
        
        // Yeni yetkileri ekle
        if (!empty($permissions) && is_array($permissions)) {
            foreach ($permissions as $permission) {
                // Permission boş değilse ekle
                if (!empty($permission)) {
                    // Permission'dan modül adını çıkar (örn: users.view -> users)
                    $parts = explode('.', $permission);
                    $module = $parts[0] ?? 'system';
                    
                    try {
                        $this->db->query(
                            "INSERT INTO role_permissions (role_id, permission, module) VALUES (?, ?, ?)",
                            [$roleId, $permission, $module]
                        );
                    } catch (Exception $e) {
                        // Duplicate key hatası olabilir, sessizce devam et
                        error_log('Role permission insert error: ' . $e->getMessage());
                    }
                }
            }
        }
    }
    
    /**
     * Rol yetkilerini getirir
     */
    public function getPermissions($roleId) {
        return $this->db->fetchAll(
            "SELECT permission FROM role_permissions WHERE role_id = ?",
            [$roleId]
        );
    }
    
    /**
     * Rol siler (sistem rolleri silinemez)
     */
    public function deleteRole($id) {
        $role = $this->find($id);
        if (!$role) {
            return false;
        }
        
        // Sistem rolleri silinemez
        if (!empty($role['is_system']) && $role['is_system'] == 1) {
            return false;
        }
        
        // Varsayılan rol silinemez
        if (!empty($role['is_default']) && $role['is_default'] == 1) {
            return false;
        }
        
        // Rolü sil (CASCADE ile yetkiler de silinir)
        return $this->delete($id);
    }
    
    /**
     * Slug oluşturur - Türkçe karakterleri düzgün çevirir
     * @param string $name Rol adı
     * @param int|null $excludeId Benzersizlik kontrolünde hariç tutulacak ID (güncelleme için)
     */
    private function generateSlug($name, $excludeId = null) {
        $slug = trim($name);
        
        // Türkçe karakterleri İngilizce karşılıklarına çevir
        $turkishChars = ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç'];
        $englishChars = ['i', 'g', 'u', 's', 'o', 'c', 'i', 'g', 'u', 's', 'o', 'c'];
        $slug = str_replace($turkishChars, $englishChars, $slug);
        
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9_-]/', '_', $slug);
        $slug = preg_replace('/_+/', '_', $slug);
        $slug = trim($slug, '_');
        
        // Benzersizlik kontrolü (kendi ID'sini hariç tut)
        $originalSlug = $slug;
        $counter = 1;
        while (true) {
            $existing = $this->findBySlug($slug);
            // Eğer slug yoksa veya bulunan kayıt kendisiyse OK
            if (!$existing || ($excludeId && $existing['id'] == $excludeId)) {
                break;
            }
            $slug = $originalSlug . '_' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Kullanıcı sayısını getirir
     */
    public function getUserCount($roleId) {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM users WHERE role = (SELECT slug FROM roles WHERE id = ?)",
            [$roleId]
        );
        return $result['count'] ?? 0;
    }
}

