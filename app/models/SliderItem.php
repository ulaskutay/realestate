<?php
/**
 * Slider Item Model
 * Slider item işlemleri için model sınıfı
 */

class SliderItem extends Model {
    protected $table = 'slider_items';
    
    /**
     * Slider ID'ye göre aktif item'ları getirir (sıralı)
     */
    public function getBySliderId($sliderId) {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` WHERE `slider_id` = ? AND `is_active` = 1 ORDER BY `sort_order` ASC",
            [$sliderId]
        );
    }
    
    /**
     * Slider ID'ye göre tüm item'ları getirir (sıralı)
     */
    public function getAllBySliderId($sliderId) {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` WHERE `slider_id` = ? ORDER BY `sort_order` ASC",
            [$sliderId]
        );
    }
    
    /**
     * Item'ları toplu olarak sıralama günceller
     */
    public function updateOrder($items) {
        foreach ($items as $order => $itemId) {
            $this->update($itemId, ['sort_order' => $order + 1]);
        }
    }
    
    /**
     * Yeni item ekler
     */
    public function createItem($data) {
        // Eğer sort_order belirtilmemişse, en sona ekle
        if (!isset($data['sort_order']) || $data['sort_order'] === null) {
            $lastOrder = $this->db->fetch(
                "SELECT MAX(`sort_order`) as max_order FROM `{$this->table}` WHERE `slider_id` = ?",
                [$data['slider_id']]
            );
            $data['sort_order'] = ($lastOrder['max_order'] ?? 0) + 1;
        }
        
        return $this->create($data);
    }
}
