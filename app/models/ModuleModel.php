<?php
/**
 * Module Model
 * Modül yönetimi için model sınıfı
 */

class ModuleModel extends Model {
    protected $table = 'modules';
    
    /**
     * Tüm aktif modülleri getirir
     */
    public function getAllActive() {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY name ASC"
        );
    }
    
    /**
     * Tüm modülleri getirir
     */
    public function getAll() {
        return $this->all('name ASC');
    }
    
    /**
     * Slug'a göre modül getirir
     */
    public function findBySlug($slug) {
        return $this->findOne('slug', $slug);
    }
    
    /**
     * Modül ve yetkilerini birlikte getirir
     */
    public function findWithPermissions($id) {
        $module = $this->find($id);
        if (!$module) {
            return null;
        }
        
        try {
            // Önce order sütununu dene
            $permissions = $this->db->fetchAll(
                "SELECT * FROM module_permissions WHERE module_id = ? ORDER BY `order` ASC, permission ASC",
                [$id]
            );
        } catch (Exception $e) {
            // order sütunu yoksa sadece permission'a göre sırala
            $permissions = $this->db->fetchAll(
                "SELECT * FROM module_permissions WHERE module_id = ? ORDER BY permission ASC",
                [$id]
            );
        }
        
        $module['permissions'] = $permissions;
        
        return $module;
    }
    
    /**
     * Slug'a göre modül ve yetkilerini getirir
     */
    public function findBySlugWithPermissions($slug) {
        $module = $this->findBySlug($slug);
        if (!$module) {
            return null;
        }
        
        return $this->findWithPermissions($module['id']);
    }
    
    /**
     * Tüm modülleri yetkileriyle birlikte getirir
     */
    public function getAllWithPermissions() {
        $modules = $this->getAllActive();
        
        foreach ($modules as &$module) {
            try {
                // Önce order sütununu dene
                $permissions = $this->db->fetchAll(
                    "SELECT * FROM module_permissions WHERE module_id = ? ORDER BY `order` ASC, permission ASC",
                    [$module['id']]
                );
            } catch (Exception $e) {
                // order sütunu yoksa sadece permission'a göre sırala
                $permissions = $this->db->fetchAll(
                    "SELECT * FROM module_permissions WHERE module_id = ? ORDER BY permission ASC",
                    [$module['id']]
                );
            }
            $module['permissions'] = $permissions;
        }
        
        return $modules;
    }
}

