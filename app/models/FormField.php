<?php
/**
 * FormField Model
 * Form alanları yönetimi için model sınıfı
 */

class FormField extends Model {
    protected $table = 'form_fields';
    
    /**
     * Form ID'sine göre tüm alanları getirir
     */
    public function getAllByFormId($formId) {
        try {
            $sql = "SELECT * FROM `{$this->table}` WHERE `form_id` = ? ORDER BY `order` ASC";
            $fields = $this->db->fetchAll($sql, [$formId]);
        } catch (PDOException $e) {
            // order kolonu yoksa sort_order kullan
            $sql = "SELECT * FROM `{$this->table}` WHERE `form_id` = ? ORDER BY `sort_order` ASC";
            $fields = $this->db->fetchAll($sql, [$formId]);
        }
        
        // JSON alanlarını decode et
        foreach ($fields as &$field) {
            $field = $this->decodeJsonFields($field);
        }
        
        return $fields;
    }
    
    /**
     * Aktif alanları getirir
     */
    public function getActiveByFormId($formId) {
        try {
            $sql = "SELECT * FROM `{$this->table}` WHERE `form_id` = ? AND `status` = 'active' ORDER BY `order` ASC";
            $fields = $this->db->fetchAll($sql, [$formId]);
        } catch (PDOException $e) {
            // order kolonu yoksa sort_order kullan
            try {
                $sql = "SELECT * FROM `{$this->table}` WHERE `form_id` = ? AND `is_active` = 1 ORDER BY `sort_order` ASC";
                $fields = $this->db->fetchAll($sql, [$formId]);
            } catch (PDOException $e2) {
                $sql = "SELECT * FROM `{$this->table}` WHERE `form_id` = ? ORDER BY `sort_order` ASC";
                $fields = $this->db->fetchAll($sql, [$formId]);
            }
        }
        
        // JSON alanlarını decode et
        foreach ($fields as &$field) {
            $field = $this->decodeJsonFields($field);
        }
        
        return $fields;
    }
    
    /**
     * Tekil alan getirir (JSON decoded)
     */
    public function findDecoded($id) {
        $field = $this->find($id);
        if ($field) {
            $field = $this->decodeJsonFields($field);
        }
        return $field;
    }
    
    /**
     * Alan oluşturur
     */
    public function createField($data) {
        // Varsayılan sıralama
        if (!isset($data['order'])) {
            $data['order'] = $this->getNextOrder($data['form_id']);
        }
        
        // Alan adını oluştur
        if (empty($data['name'])) {
            $data['name'] = $this->generateFieldName($data['label'] ?? '');
        }
        
        // Veritabanı kolon adlarına dönüştür
        $dbData = $this->mapToDbColumns($data);
        
        // JSON alanlarını encode et
        $dbData = $this->encodeJsonFields($dbData);
        
        // Eğer order kolonu yoksa sort_order kullan
        try {
            return $this->create($dbData);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'order') !== false && isset($dbData['order'])) {
                // order kolonu yoksa sort_order'a kopyala
                $dbData['sort_order'] = $dbData['order'];
                unset($dbData['order']);
                return $this->create($dbData);
            }
            throw $e;
        }
    }
    
    /**
     * Alan günceller
     */
    public function updateField($id, $data) {
        // Veritabanı kolon adlarına dönüştür
        $dbData = $this->mapToDbColumns($data);
        
        // JSON alanlarını encode et
        $dbData = $this->encodeJsonFields($dbData);
        
        return $this->update($id, $dbData);
    }
    
    /**
     * Sıralamayı günceller
     */
    public function updateOrder($fields) {
        foreach ($fields as $index => $fieldId) {
            $this->update($fieldId, ['order' => $index]);
        }
        return true;
    }
    
    /**
     * Form ID'sine göre tüm alanları siler
     */
    public function deleteByFormId($formId) {
        $sql = "DELETE FROM `{$this->table}` WHERE `form_id` = ?";
        return $this->db->query($sql, [$formId]);
    }
    
    /**
     * Sonraki sıra numarasını getirir
     */
    private function getNextOrder($formId) {
        try {
            // Önce order kolonunu kontrol et, yoksa sort_order kullan
            $sql = "SELECT MAX(`order`) as max_order FROM `{$this->table}` WHERE `form_id` = ?";
            $result = $this->db->fetch($sql, [$formId]);
            return ($result['max_order'] ?? -1) + 1;
        } catch (PDOException $e) {
            // order kolonu yoksa sort_order kullan
            try {
                $sql = "SELECT MAX(`sort_order`) as max_order FROM `{$this->table}` WHERE `form_id` = ?";
                $result = $this->db->fetch($sql, [$formId]);
                return ($result['max_order'] ?? -1) + 1;
            } catch (PDOException $e2) {
                return 0;
            }
        }
    }
    
    /**
     * Alan adı oluşturur
     */
    private function generateFieldName($label) {
        // Türkçe karakterleri dönüştür
        $turkishChars = ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç'];
        $latinChars = ['i', 'g', 'u', 's', 'o', 'c', 'i', 'g', 'u', 's', 'o', 'c'];
        $name = str_replace($turkishChars, $latinChars, $label);
        
        // Küçük harfe çevir
        $name = mb_strtolower($name, 'UTF-8');
        
        // Alfanumerik olmayan karakterleri alt çizgi ile değiştir
        $name = preg_replace('/[^a-z0-9]+/', '_', $name);
        
        // Başındaki ve sonundaki alt çizgileri kaldır
        $name = trim($name, '_');
        
        // Boşsa varsayılan ad ver
        if (empty($name)) {
            $name = 'field_' . time();
        }
        
        return $name;
    }
    
    /**
     * JSON alanlarını encode eder
     */
    private function encodeJsonFields($data) {
        if (isset($data['options']) && is_array($data['options'])) {
            $data['options'] = json_encode($data['options'], JSON_UNESCAPED_UNICODE);
        }
        if (isset($data['validation']) && is_array($data['validation'])) {
            $data['validation'] = json_encode($data['validation'], JSON_UNESCAPED_UNICODE);
        }
        return $data;
    }
    
    /**
     * JSON alanlarını decode eder
     */
    private function decodeJsonFields($field) {
        if (!empty($field['options'])) {
            $decoded = json_decode($field['options'], true);
            $field['options'] = $decoded !== null ? $decoded : [];
        } else {
            $field['options'] = [];
        }
        
        // validation_rules veya validation olabilir
        $validationKey = isset($field['validation_rules']) ? 'validation_rules' : 'validation';
        if (!empty($field[$validationKey])) {
            $decoded = json_decode($field[$validationKey], true);
            $field['validation'] = $decoded !== null ? $decoded : [];
        } else {
            $field['validation'] = [];
        }
        
        // Veritabanı kolon adlarını frontend kolon adlarına dönüştür
        $field = $this->mapFromDbColumns($field);
        
        // required değerini integer'a çevir (0 veya 1)
        if (isset($field['required'])) {
            $field['required'] = (int)$field['required'];
        } else {
            $field['required'] = 0;
        }
        
        return $field;
    }
    
    /**
     * Frontend kolon adlarını veritabanı kolon adlarına dönüştürür
     */
    private function mapToDbColumns($data) {
        $mapping = [
            'type' => 'field_type',
            'label' => 'field_label',
            'name' => 'field_name',
            'required' => 'is_required',
            'validation' => 'validation_rules'
        ];
        
        $dbData = [];
        foreach ($data as $key => $value) {
            if (isset($mapping[$key])) {
                $dbData[$mapping[$key]] = $value;
            } else {
                $dbData[$key] = $value;
            }
        }
        
        return $dbData;
    }
    
    /**
     * Veritabanı kolon adlarını frontend kolon adlarına dönüştürür
     */
    private function mapFromDbColumns($field) {
        $mapping = [
            'field_type' => 'type',
            'field_label' => 'label',
            'field_name' => 'name',
            'is_required' => 'required',
            'validation_rules' => 'validation'
        ];
        
        $mappedField = [];
        foreach ($field as $key => $value) {
            if (isset($mapping[$key])) {
                $mappedField[$mapping[$key]] = $value;
            } else {
                $mappedField[$key] = $value;
            }
        }
        
        return $mappedField;
    }
    
    /**
     * Alan tiplerini getirir
     */
    public static function getFieldTypes() {
        return [
            'text' => [
                'label' => 'Metin',
                'icon' => 'text_fields',
                'category' => 'input'
            ],
            'email' => [
                'label' => 'E-posta',
                'icon' => 'mail',
                'category' => 'input'
            ],
            'phone' => [
                'label' => 'Telefon',
                'icon' => 'phone',
                'category' => 'input'
            ],
            'number' => [
                'label' => 'Sayı',
                'icon' => 'pin',
                'category' => 'input'
            ],
            'textarea' => [
                'label' => 'Uzun Metin',
                'icon' => 'notes',
                'category' => 'input'
            ],
            'select' => [
                'label' => 'Açılır Liste',
                'icon' => 'arrow_drop_down_circle',
                'category' => 'choice'
            ],
            'checkbox' => [
                'label' => 'Onay Kutuları',
                'icon' => 'check_box',
                'category' => 'choice'
            ],
            'radio' => [
                'label' => 'Radyo Butonları',
                'icon' => 'radio_button_checked',
                'category' => 'choice'
            ],
            'file' => [
                'label' => 'Dosya Yükleme',
                'icon' => 'upload_file',
                'category' => 'input'
            ],
            'date' => [
                'label' => 'Tarih',
                'icon' => 'calendar_today',
                'category' => 'input'
            ],
            'time' => [
                'label' => 'Saat',
                'icon' => 'schedule',
                'category' => 'input'
            ],
            'datetime' => [
                'label' => 'Tarih & Saat',
                'icon' => 'event',
                'category' => 'input'
            ],
            'hidden' => [
                'label' => 'Gizli Alan',
                'icon' => 'visibility_off',
                'category' => 'special'
            ],
            'heading' => [
                'label' => 'Başlık',
                'icon' => 'title',
                'category' => 'layout'
            ],
            'paragraph' => [
                'label' => 'Paragraf',
                'icon' => 'article',
                'category' => 'layout'
            ],
            'divider' => [
                'label' => 'Ayırıcı',
                'icon' => 'horizontal_rule',
                'category' => 'layout'
            ]
        ];
    }
}

