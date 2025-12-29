<?php
/**
 * FormSubmission Model
 * Form gönderimleri yönetimi için model sınıfı
 */

class FormSubmission extends Model {
    protected $table = 'form_submissions';
    
    /**
     * Form ID'sine göre tüm gönderimleri getirir
     */
    public function getAllByFormId($formId, $status = null, $limit = null, $offset = 0) {
        $sql = "SELECT * FROM `{$this->table}` WHERE `form_id` = ?";
        $params = [$formId];
        
        if ($status) {
            $sql .= " AND `status` = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY `created_at` DESC";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = (int)$offset;
        }
        
        $submissions = $this->db->fetchAll($sql, $params);
        
        // JSON alanlarını decode et
        foreach ($submissions as &$submission) {
            $submission = $this->decodeData($submission);
        }
        
        return $submissions;
    }
    
    /**
     * Toplam gönderim sayısını getirir
     */
    public function countByFormId($formId, $status = null) {
        $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `form_id` = ?";
        $params = [$formId];
        
        if ($status) {
            $sql .= " AND `status` = ?";
            $params[] = $status;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] ?? 0;
    }
    
    /**
     * Yeni gönderimleri getirir
     */
    public function getNewByFormId($formId) {
        return $this->getAllByFormId($formId, 'new');
    }
    
    /**
     * Tekil gönderim getirir (JSON decoded)
     */
    public function findDecoded($id) {
        $submission = $this->find($id);
        if ($submission) {
            $submission = $this->decodeData($submission);
        }
        return $submission;
    }
    
    /**
     * Form gönderimi oluşturur
     */
    public function createSubmission($formId, $data, $meta = []) {
        $submissionData = [
            'form_id' => $formId,
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'ip_address' => $meta['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $meta['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null,
            'status' => 'new'
        ];
        
        // Referrer sütunu varsa ekle (eski kurulumlarla uyumluluk için)
        try {
            $columns = $this->db->fetchAll("SHOW COLUMNS FROM `{$this->table}` LIKE 'referrer'");
            if (!empty($columns)) {
                $submissionData['referrer'] = $meta['referrer'] ?? $_SERVER['HTTP_REFERER'] ?? null;
            }
        } catch (Exception $e) {
            // Sütun kontrolü başarısız olursa sessizce devam et
        }
        
        return $this->create($submissionData);
    }
    
    /**
     * Gönderim durumunu günceller
     */
    public function updateStatus($id, $status) {
        return $this->update($id, ['status' => $status]);
    }
    
    /**
     * Gönderimi okundu olarak işaretle
     */
    public function markAsRead($id) {
        return $this->updateStatus($id, 'read');
    }
    
    /**
     * Gönderimi spam olarak işaretle
     */
    public function markAsSpam($id) {
        return $this->updateStatus($id, 'spam');
    }
    
    /**
     * Gönderimi arşivle
     */
    public function archive($id) {
        return $this->updateStatus($id, 'archived');
    }
    
    /**
     * Not ekler
     */
    public function addNote($id, $note) {
        try {
            $columns = $this->db->fetchAll("SHOW COLUMNS FROM `{$this->table}` LIKE 'notes'");
            if (!empty($columns)) {
                return $this->update($id, ['notes' => $note]);
            }
        } catch (Exception $e) {
            // Sütun yoksa false döndür
        }
        return false;
    }
    
    /**
     * Form ID'sine göre tüm gönderimleri siler
     */
    public function deleteByFormId($formId) {
        $sql = "DELETE FROM `{$this->table}` WHERE `form_id` = ?";
        return $this->db->query($sql, [$formId]);
    }
    
    /**
     * Eski gönderimleri temizler
     */
    public function cleanOldSubmissions($days = 90) {
        $sql = "DELETE FROM `{$this->table}` WHERE `created_at` < DATE_SUB(NOW(), INTERVAL ? DAY) AND `status` = 'archived'";
        return $this->db->query($sql, [$days]);
    }
    
    /**
     * Toplu silme
     */
    public function deleteMultiple($ids) {
        if (empty($ids)) {
            return false;
        }
        
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $sql = "DELETE FROM `{$this->table}` WHERE `id` IN ($placeholders)";
        
        return $this->db->query($sql, $ids);
    }
    
    /**
     * Toplu durum güncelleme
     */
    public function updateMultipleStatus($ids, $status) {
        if (empty($ids)) {
            return false;
        }
        
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $sql = "UPDATE `{$this->table}` SET `status` = ? WHERE `id` IN ($placeholders)";
        
        $params = array_merge([$status], $ids);
        return $this->db->query($sql, $params);
    }
    
    /**
     * JSON verisini decode eder
     */
    private function decodeData($submission) {
        if (!empty($submission['data'])) {
            $decoded = json_decode($submission['data'], true);
            $submission['data'] = $decoded !== null ? $decoded : [];
        } else {
            $submission['data'] = [];
        }
        return $submission;
    }
    
    /**
     * CSV export için veri hazırlar
     */
    public function getExportData($formId) {
        $submissions = $this->getAllByFormId($formId);
        
        if (empty($submissions)) {
            return [];
        }
        
        // Tüm field'ları topla
        $allFields = [];
        foreach ($submissions as $submission) {
            if (is_array($submission['data'])) {
                foreach (array_keys($submission['data']) as $key) {
                    if (!in_array($key, $allFields)) {
                        $allFields[] = $key;
                    }
                }
            }
        }
        
        // Export verisini hazırla
        $exportData = [];
        foreach ($submissions as $submission) {
            $row = [
                'id' => $submission['id'],
                'tarih' => $submission['created_at'],
                'durum' => $submission['status'],
                'ip' => $submission['ip_address']
            ];
            
            foreach ($allFields as $field) {
                $row[$field] = $submission['data'][$field] ?? '';
            }
            
            $exportData[] = $row;
        }
        
        return $exportData;
    }
    
    /**
     * Son gönderimleri getirir (Dashboard için)
     */
    public function getRecent($limit = 10) {
        $sql = "SELECT s.*, f.name as form_name 
                FROM `{$this->table}` s 
                LEFT JOIN `forms` f ON s.form_id = f.id 
                ORDER BY s.created_at DESC 
                LIMIT ?";
        
        $submissions = $this->db->fetchAll($sql, [(int)$limit]);
        
        foreach ($submissions as &$submission) {
            $submission = $this->decodeData($submission);
        }
        
        return $submissions;
    }
    
    /**
     * Toplam yeni gönderim sayısı
     */
    public function getTotalNewCount() {
        $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `status` = 'new'";
        $result = $this->db->fetch($sql);
        return $result['count'] ?? 0;
    }
}

