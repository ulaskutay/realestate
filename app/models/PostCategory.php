<?php
/**
 * PostCategory Model - Yazı Kategorileri
 */

class PostCategory extends Model {
    protected $table = 'post_categories';
    
    /**
     * Tüm kategorileri hiyerarşik olarak getirir
     */
    public function getAll($orderBy = null) {
        if ($orderBy === null) {
            $orderBy = '`order` ASC, `name` ASC';
        } else {
            // Backtick ile escape et
            $orderBy = str_replace('order', '`order`', $orderBy);
        }
        $sql = "SELECT * FROM `{$this->table}` ORDER BY {$orderBy}";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Aktif kategorileri getirir
     */
    public function getActive() {
        $sql = "SELECT * FROM `{$this->table}` WHERE `status` = 'active' ORDER BY `order` ASC, `name` ASC";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Slug'a göre kategori getirir
     */
    public function findBySlug($slug) {
        return $this->findOne('slug', $slug);
    }
    
    /**
     * Kategorideki yazı sayısını getirir
     */
    public function getPostCount($categoryId) {
        $sql = "SELECT COUNT(*) as count FROM `posts` WHERE `category_id` = ? AND `status` = 'published'";
        $result = $this->db->fetch($sql, [$categoryId]);
        return $result['count'] ?? 0;
    }
    
    /**
     * Alt kategorileri getirir
     */
    public function getChildren($parentId) {
        $sql = "SELECT * FROM `{$this->table}` WHERE `parent_id` = ? ORDER BY `order` ASC, `name` ASC";
        return $this->db->fetchAll($sql, [$parentId]);
    }
    
    /**
     * Hiyerarşik liste oluşturur (select için)
     */
    public function getHierarchicalList($parentId = null, $prefix = '') {
        $result = [];
        
        if ($parentId === null) {
            $sql = "SELECT * FROM `{$this->table}` WHERE `parent_id` IS NULL ORDER BY `order` ASC, `name` ASC";
            $categories = $this->db->fetchAll($sql);
        } else {
            $categories = $this->getChildren($parentId);
        }
        
        foreach ($categories as $category) {
            $category['display_name'] = $prefix . $category['name'];
            $result[] = $category;
            
            // Alt kategorileri ekle
            $children = $this->getHierarchicalList($category['id'], $prefix . '— ');
            $result = array_merge($result, $children);
        }
        
        return $result;
    }
    
    /**
     * Kategori oluşturur
     */
    public function createCategory($data) {
        // Slug oluştur
        if (empty($data['slug'])) {
            $data['slug'] = $this->createSlug($data['name']);
        }
        
        return $this->create($data);
    }
    
    /**
     * Kategori günceller
     */
    public function updateCategory($id, $data) {
        // Slug güncelle
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = $this->createSlug($data['name'], $id);
        }
        
        return $this->update($id, $data);
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
        
        return $text;
    }
}

