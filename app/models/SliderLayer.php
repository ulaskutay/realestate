<?php
/**
 * Slider Layer Model
 * Layer işlemleri için model sınıfı
 */

class SliderLayer extends Model {
    protected $table = 'slider_layers';
    
    /**
     * Slider item ID'ye göre layer'ları getirir (sıralı)
     */
    public function getByItemId($itemId) {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` WHERE `slider_item_id` = ? AND `visibility` = 1 ORDER BY `order` ASC, `z_index` ASC",
            [$itemId]
        );
    }
    
    /**
     * Slider item ID'ye göre tüm layer'ları getirir (sıralı)
     */
    public function getAllByItemId($itemId) {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` WHERE `slider_item_id` = ? ORDER BY `order` ASC, `z_index` ASC",
            [$itemId]
        );
    }
    
    /**
     * Layer'ları toplu olarak sıralama günceller
     */
    public function updateOrder($layers) {
        foreach ($layers as $order => $layerId) {
            $this->update($layerId, ['order' => $order + 1]);
        }
    }
    
    /**
     * Yeni layer ekler
     */
    public function createLayer($data) {
        // Eğer order belirtilmemişse, en sona ekle
        if (!isset($data['order']) || $data['order'] === null) {
            $lastOrder = $this->db->fetch(
                "SELECT MAX(`order`) as max_order FROM `{$this->table}` WHERE `slider_item_id` = ?",
                [$data['slider_item_id']]
            );
            $data['order'] = ($lastOrder['max_order'] ?? 0) + 1;
        }
        
        // Eğer z_index belirtilmemişse, en üste ekle
        if (!isset($data['z_index']) || $data['z_index'] === null) {
            $maxZIndex = $this->db->fetch(
                "SELECT MAX(`z_index`) as max_z FROM `{$this->table}` WHERE `slider_item_id` = ?",
                [$data['slider_item_id']]
            );
            $data['z_index'] = ($maxZIndex['max_z'] ?? 0) + 1;
        }
        
        // JSON alanları için encode
        if (isset($data['transform']) && is_array($data['transform'])) {
            $data['transform'] = json_encode($data['transform']);
        }
        if (isset($data['animation_in']) && is_array($data['animation_in'])) {
            $data['animation_in'] = json_encode($data['animation_in']);
        }
        if (isset($data['animation_out']) && is_array($data['animation_out'])) {
            $data['animation_out'] = json_encode($data['animation_out']);
        }
        if (isset($data['hover_animation']) && is_array($data['hover_animation'])) {
            $data['hover_animation'] = json_encode($data['hover_animation']);
        }
        if (isset($data['responsive']) && is_array($data['responsive'])) {
            $data['responsive'] = json_encode($data['responsive']);
        }
        if (isset($data['style']) && is_array($data['style'])) {
            $data['style'] = json_encode($data['style']);
        }
        
        return $this->create($data);
    }
    
    /**
     * Layer günceller
     */
    public function updateLayer($id, $data) {
        // JSON alanları için encode
        if (isset($data['transform']) && is_array($data['transform'])) {
            $data['transform'] = json_encode($data['transform']);
        }
        if (isset($data['animation_in']) && is_array($data['animation_in'])) {
            $data['animation_in'] = json_encode($data['animation_in']);
        }
        if (isset($data['animation_out']) && is_array($data['animation_out'])) {
            $data['animation_out'] = json_encode($data['animation_out']);
        }
        if (isset($data['hover_animation']) && is_array($data['hover_animation'])) {
            $data['hover_animation'] = json_encode($data['hover_animation']);
        }
        if (isset($data['responsive']) && is_array($data['responsive'])) {
            $data['responsive'] = json_encode($data['responsive']);
        }
        if (isset($data['style']) && is_array($data['style'])) {
            $data['style'] = json_encode($data['style']);
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Layer'ı JSON formatında getirir (decode edilmiş)
     */
    public function findDecoded($id) {
        $layer = $this->find($id);
        
        if ($layer) {
            // JSON alanlarını decode et
            if (!empty($layer['transform'])) {
                $layer['transform'] = json_decode($layer['transform'], true) ?: [];
            }
            if (!empty($layer['animation_in'])) {
                $layer['animation_in'] = json_decode($layer['animation_in'], true) ?: [];
            }
            if (!empty($layer['animation_out'])) {
                $layer['animation_out'] = json_decode($layer['animation_out'], true) ?: [];
            }
            if (!empty($layer['hover_animation'])) {
                $layer['hover_animation'] = json_decode($layer['hover_animation'], true) ?: [];
            }
            if (!empty($layer['responsive'])) {
                $layer['responsive'] = json_decode($layer['responsive'], true) ?: [];
            }
            if (!empty($layer['style'])) {
                $layer['style'] = json_decode($layer['style'], true) ?: [];
            }
        }
        
        return $layer;
    }
}
