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
    
    /**
     * Benzersiz slug oluşturur
     */
    private function createSlug($name, $excludeId = null) {
        $slug = $this->slugify($name);
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Slug var mı kontrol eder
     */
    private function slugExists($slug, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `slug` = ? AND `id` != ?";
            $result = $this->db->fetch($sql, [$slug, $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `slug` = ?";
            $result = $this->db->fetch($sql, [$slug]);
        }
        
        return ($result['count'] ?? 0) > 0;
    }
    
    /**
     * Metni slug'a çevirir
     */
    private function slugify($text) {
        // Türkçe karakterleri dönüştür
        $tr = ['ş', 'Ş', 'ı', 'İ', 'ğ', 'Ğ', 'ü', 'Ü', 'ö', 'Ö', 'ç', 'Ç'];
        $en = ['s', 's', 'i', 'i', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c'];
        $text = str_replace($tr, $en, $text);
        
        // Küçük harfe çevir
        $text = mb_strtolower($text, 'UTF-8');
        
        // Alfanumerik olmayan karakterleri tire ile değiştir
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        
        // Baş ve sondaki tireleri kaldır
        $text = trim($text, '-');
        
        // Boşsa varsayılan slug
        if (empty($text)) {
            $text = 'menu-' . time();
        }
        
        // Maksimum uzunluk
        if (strlen($text) > 200) {
            $text = substr($text, 0, 200);
            $text = rtrim($text, '-');
        }
        
        return $text;
    }
    
    /**
     * Menü oluşturur (slug ile)
     */
    public function create($data) {
        // Slug yoksa oluştur
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = $this->createSlug($data['name']);
        } elseif (empty($data['slug'])) {
            $data['slug'] = 'menu-' . time();
        }
        
        return parent::create($data);
    }
    
    /**
     * Menü günceller (slug ile)
     */
    public function update($id, $data) {
        // İsim değiştiyse slug'ı güncelle
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = $this->createSlug($data['name'], $id);
        }
        
        return parent::update($id, $data);
    }
}

