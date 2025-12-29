<?php
/**
 * MenuItem Model
 * Menü öğeleri için model sınıfı
 */

class MenuItem extends Model {
    protected $table = 'menu_items';
    
    /**
     * Menüye göre tüm öğeleri getirir (düz liste)
     */
    public function getByMenuId($menuId) {
        try {
            $sql = "SELECT * FROM `{$this->table}` WHERE `menu_id` = ? AND `status` = 'active' ORDER BY `order` ASC, `id` ASC";
            return $this->db->fetchAll($sql, [$menuId]);
        } catch (Exception $e) {
            // order sütunu yoksa fallback
            $sql = "SELECT * FROM `{$this->table}` WHERE `menu_id` = ? AND `status` = 'active' ORDER BY `id` ASC";
            return $this->db->fetchAll($sql, [$menuId]);
        }
    }
    
    /**
     * Menüye göre tüm öğeleri getirir (status filtresi olmadan)
     */
    public function getAllByMenuId($menuId) {
        try {
            $sql = "SELECT * FROM `{$this->table}` WHERE `menu_id` = ? ORDER BY `order` ASC, `id` ASC";
            return $this->db->fetchAll($sql, [$menuId]);
        } catch (Exception $e) {
            // order sütunu yoksa fallback
            $sql = "SELECT * FROM `{$this->table}` WHERE `menu_id` = ? ORDER BY `id` ASC";
            return $this->db->fetchAll($sql, [$menuId]);
        }
    }
    
    /**
     * Menüye göre hiyerarşik öğeleri getirir (nested tree)
     */
    public function getTreeByMenuId($menuId) {
        $items = $this->getByMenuId($menuId);
        return $this->buildTree($items);
    }
    
    /**
     * Düz listeyi hiyerarşik yapıya çevirir
     */
    private function buildTree($items, $parentId = null) {
        $tree = [];
        
        foreach ($items as $item) {
            if ($item['parent_id'] == $parentId) {
                $children = $this->buildTree($items, $item['id']);
                if ($children) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }
        
        return $tree;
    }
    
    /**
     * Yeni menü öğesi ekler
     */
    public function createItem($data) {
        // order sütunu var mı kontrol et ve sıralama belirle
        try {
            if (!isset($data['order']) || $data['order'] === null) {
                $sql = "SELECT MAX(`order`) as max_order FROM `{$this->table}` WHERE `menu_id` = ? AND `parent_id` " . 
                       (isset($data['parent_id']) && $data['parent_id'] ? "= ?" : "IS NULL");
                
                $params = [$data['menu_id']];
                if (isset($data['parent_id']) && $data['parent_id']) {
                    $params[] = $data['parent_id'];
                }
                
                $result = $this->db->fetch($sql, $params);
                $data['order'] = ($result['max_order'] ?? 0) + 1;
            }
        } catch (Exception $e) {
            // order sütunu yoksa, data'dan order'ı kaldır
            unset($data['order']);
        }
        
        return $this->create($data);
    }
    
    /**
     * Öğe sıralamasını günceller
     */
    public function updateOrder($items) {
        foreach ($items as $item) {
            $updateData = ['order' => $item['order']];
            
            // parent_id güncellemesi varsa ekle
            if (isset($item['parent_id'])) {
                $updateData['parent_id'] = $item['parent_id'] ?: null;
            }
            
            $this->update($item['id'], $updateData);
        }
        return true;
    }
    
    /**
     * Menü öğesini alt öğeleriyle birlikte siler
     */
    public function deleteWithChildren($id) {
        // Önce alt öğeleri bul ve sil (recursive)
        $children = $this->where('parent_id', $id);
        foreach ($children as $child) {
            $this->deleteWithChildren($child['id']);
        }
        
        // Kendisini sil
        return $this->delete($id);
    }
    
    /**
     * Ana öğeleri getirir (parent_id = null)
     */
    public function getRootItems($menuId) {
        try {
            $sql = "SELECT * FROM `{$this->table}` WHERE `menu_id` = ? AND `parent_id` IS NULL ORDER BY `order` ASC, `id` ASC";
            return $this->db->fetchAll($sql, [$menuId]);
        } catch (Exception $e) {
            $sql = "SELECT * FROM `{$this->table}` WHERE `menu_id` = ? AND `parent_id` IS NULL ORDER BY `id` ASC";
            return $this->db->fetchAll($sql, [$menuId]);
        }
    }
    
    /**
     * Alt öğeleri getirir
     */
    public function getChildren($parentId) {
        try {
            $sql = "SELECT * FROM `{$this->table}` WHERE `parent_id` = ? ORDER BY `order` ASC, `id` ASC";
            return $this->db->fetchAll($sql, [$parentId]);
        } catch (Exception $e) {
            $sql = "SELECT * FROM `{$this->table}` WHERE `parent_id` = ? ORDER BY `id` ASC";
            return $this->db->fetchAll($sql, [$parentId]);
        }
    }
    
    /**
     * Öğenin derinliğini hesaplar
     */
    public function getDepth($itemId) {
        $depth = 0;
        $item = $this->find($itemId);
        
        while ($item && $item['parent_id']) {
            $depth++;
            $item = $this->find($item['parent_id']);
        }
        
        return $depth;
    }
    
    /**
     * Tüm öğelerin sayısını getirir
     */
    public function countByMenuId($menuId) {
        $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `menu_id` = ?";
        $result = $this->db->fetch($sql, [$menuId]);
        return $result['count'] ?? 0;
    }
}

