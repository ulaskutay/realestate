<?php
/**
 * Form Model
 * Form yönetimi için model sınıfı
 */

class Form extends Model {
    protected $table = 'forms';
    
    /**
     * Tüm formları getirir
     */
    public function getAll($orderBy = 'created_at DESC') {
        return $this->all($orderBy);
    }
    
    /**
     * Aktif formları getirir
     */
    public function getActive() {
        return $this->where('status', 'active');
    }
    
    /**
     * Slug'a göre form getirir
     */
    public function findBySlug($slug) {
        return $this->findOne('slug', $slug);
    }
    
    /**
     * Form ile birlikte alanlarını getirir
     */
    public function findWithFields($id) {
        $form = $this->find($id);
        
        if (!$form) {
            return null;
        }
        
        // Form alanlarını getir
        $fieldModel = new FormField();
        $form['fields'] = $fieldModel->getAllByFormId($id);
        
        return $form;
    }
    
    /**
     * Slug'a göre form ve alanlarını getirir
     */
    public function findBySlugWithFields($slug) {
        $form = $this->findBySlug($slug);
        
        if (!$form) {
            return null;
        }
        
        // Form alanlarını getir
        $fieldModel = new FormField();
        $form['fields'] = $fieldModel->getAllByFormId($form['id']);
        
        return $form;
    }
    
    /**
     * Form oluşturur
     */
    public function createForm($data) {
        // Slug oluştur
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['name']);
        }
        
        return $this->create($data);
    }
    
    /**
     * Form günceller
     */
    public function updateForm($id, $data) {
        // Slug güncelleme
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['name'], $id);
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Formu tüm alanları ve gönderileriyle birlikte siler
     */
    public function deleteForm($id) {
        // Foreign key cascade ile otomatik silinecek
        return $this->delete($id);
    }
    
    /**
     * Gönderim sayısını artırır
     */
    public function incrementSubmissionCount($id) {
        try {
            // Önce sütunun var olup olmadığını kontrol et
            $columns = $this->db->fetchAll("SHOW COLUMNS FROM `{$this->table}` LIKE 'submission_count'");
            if (empty($columns)) {
                // Sütun yoksa ekle
                $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `submission_count` int(11) DEFAULT 0");
            }
            
            $sql = "UPDATE `{$this->table}` SET `submission_count` = `submission_count` + 1 WHERE `id` = ?";
            return $this->db->query($sql, [$id]);
        } catch (Exception $e) {
            error_log('incrementSubmissionCount error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Formu aktif yapar
     */
    public function setActive($id) {
        return $this->update($id, ['status' => 'active']);
    }
    
    /**
     * Formu pasif yapar
     */
    public function setInactive($id) {
        return $this->update($id, ['status' => 'inactive']);
    }
    
    /**
     * Benzersiz slug oluşturur
     */
    private function generateSlug($name, $excludeId = null) {
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
     * Slug'ın var olup olmadığını kontrol eder
     */
    private function slugExists($slug, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `slug` = ?";
        $params = [$slug];
        
        if ($excludeId) {
            $sql .= " AND `id` != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * String'i slug'a çevirir
     */
    private function slugify($text) {
        // Türkçe karakterleri dönüştür
        $turkishChars = ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç'];
        $latinChars = ['i', 'g', 'u', 's', 'o', 'c', 'i', 'g', 'u', 's', 'o', 'c'];
        $text = str_replace($turkishChars, $latinChars, $text);
        
        // Küçük harfe çevir
        $text = mb_strtolower($text, 'UTF-8');
        
        // Alfanumerik olmayan karakterleri tire ile değiştir
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        
        // Başındaki ve sonundaki tireleri kaldır
        $text = trim($text, '-');
        
        return $text;
    }
    
    /**
     * Form istatistiklerini getirir
     */
    public function getStats($formId) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
                    SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_count,
                    SUM(CASE WHEN status = 'spam' THEN 1 ELSE 0 END) as spam_count,
                    SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END) as archived_count
                FROM `form_submissions` 
                WHERE `form_id` = ?";
        
        return $this->db->fetch($sql, [$formId]);
    }
}

