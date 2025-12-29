<?php
/**
 * Slider Model
 * Slider işlemleri için model sınıfı
 */

class Slider extends Model {
    protected $table = 'sliders';
    
    /**
     * Aktif slider'ı getirir
     */
    public function getActive() {
        return $this->findOne('status', 'active');
    }
    
    /**
     * Slider'ı aktif yapar (diğerlerini pasifleştirir)
     */
    public function setActive($id) {
        // Önce tüm slider'ları pasifleştir
        $this->db->query(
            "UPDATE `{$this->table}` SET `status` = 'inactive' WHERE `status` = 'active'"
        );
        
        // Seçilen slider'ı aktif yap
        return $this->update($id, ['status' => 'active']);
    }
    
    /**
     * Slider'ı pasifleştirir
     */
    public function setInactive($id) {
        return $this->update($id, ['status' => 'inactive']);
    }
    
    /**
     * Slider'ı items ile birlikte getirir
     */
    public function findWithItems($id) {
        $slider = $this->find($id);
        
        if ($slider) {
            $itemModel = new SliderItem();
            $slider['items'] = $itemModel->getBySliderId($id);
        }
        
        return $slider;
    }
    
    /**
     * Aktif slider'ı items ile birlikte getirir
     */
    public function getActiveWithItems() {
        $slider = $this->getActive();
        
        if ($slider) {
            $itemModel = new SliderItem();
            $items = $itemModel->getBySliderId($slider['id']);
            
            // Her item için layer'ları yükle (eğer layer sistemi varsa)
            if (class_exists('SliderLayer')) {
                $layerModel = new SliderLayer();
                foreach ($items as &$item) {
                    $item['layers'] = $layerModel->getByItemId($item['id']);
                }
            }
            
            $slider['items'] = $items;
        }
        
        return $slider;
    }
    
    /**
     * Tüm slider'ları getirir (sıralı)
     */
    public function getAll() {
        return $this->all('created_at DESC');
    }
    
    /**
     * Slider'ı siler (cascade ile items da silinir)
     */
    public function deleteSlider($id) {
        return $this->delete($id);
    }
}
