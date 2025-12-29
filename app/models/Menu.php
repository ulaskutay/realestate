<?php
/**
 * Menu Model
 * Menü işlemleri için model sınıfı
 */

class Menu extends Model {
    protected $table = 'menus';
    
    /**
     * Konuma göre menü getirir
     */
    public function getByLocation($location) {
        return $this->findOne('location', $location);
    }
    
    /**
     * Aktif menüleri getirir
     */
    public function getActive() {
        return $this->where('status', 'active');
    }
    
    /**
     * Menüyü items ile birlikte getirir
     */
    public function findWithItems($id) {
        $menu = $this->find($id);
        
        if ($menu) {
            $itemModel = new MenuItem();
            $menu['items'] = $itemModel->getTreeByMenuId($id);
        }
        
        return $menu;
    }
    
    /**
     * Konuma göre menüyü items ile birlikte getirir
     */
    public function getByLocationWithItems($location) {
        $menu = $this->getByLocation($location);
        
        if ($menu && $menu['status'] === 'active') {
            $itemModel = new MenuItem();
            $menu['items'] = $itemModel->getTreeByMenuId($menu['id']);
        }
        
        return $menu;
    }
    
    /**
     * Tüm menüleri getirir (sıralı)
     */
    public function getAll() {
        return $this->all('created_at DESC');
    }
    
    /**
     * Menüyü siler (cascade ile items da silinir)
     */
    public function deleteMenu($id) {
        return $this->delete($id);
    }
    
    /**
     * Konum kullanılabilir mi kontrol eder
     */
    public function isLocationAvailable($location, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `location` = ?";
        $params = [$location];
        
        if ($excludeId) {
            $sql .= " AND `id` != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] == 0;
    }
    
    /**
     * Mevcut lokasyonları getirir
     */
    public function getLocations() {
        return [
            'header' => 'Header (Üst Menü)',
            'footer' => 'Footer (Alt Menü)',
            'sidebar' => 'Sidebar (Yan Menü)',
            'mobile' => 'Mobile Menü'
        ];
    }
}

