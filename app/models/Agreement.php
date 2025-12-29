<?php
/**
 * Agreement Model - Sözleşme Yönetimi
 * Gizlilik Politikası, KVKK, Kullanım Şartları, Çerez Politikası vb.
 */

class Agreement extends Model {
    protected $table = 'agreements';
    
    /**
     * Sözleşme türleri
     */
    public static $types = [
        'privacy' => 'Gizlilik Politikası',
        'kvkk' => 'KVKK Aydınlatma Metni',
        'terms' => 'Kullanım Şartları',
        'cookies' => 'Çerez Politikası',
        'other' => 'Diğer'
    ];
    
    /**
     * Tüm sözleşmeleri getirir (yazar bilgisi ile)
     */
    public function getAll($orderBy = 'updated_at DESC') {
        $sql = "SELECT a.*, u.username as author_name
                FROM `{$this->table}` a
                LEFT JOIN `users` u ON a.author_id = u.id
                ORDER BY {$orderBy}";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Yayınlanmış sözleşmeleri getirir
     */
    public function getPublished() {
        $sql = "SELECT a.*, u.username as author_name
                FROM `{$this->table}` a
                LEFT JOIN `users` u ON a.author_id = u.id
                WHERE a.status = 'published'
                ORDER BY a.type ASC, a.title ASC";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Türe göre sözleşmeleri getirir
     */
    public function getByType($type) {
        $sql = "SELECT a.*, u.username as author_name
                FROM `{$this->table}` a
                LEFT JOIN `users` u ON a.author_id = u.id
                WHERE a.type = ?
                ORDER BY a.updated_at DESC";
        return $this->db->fetchAll($sql, [$type]);
    }
    
    /**
     * Slug'a göre sözleşme getirir
     */
    public function findBySlug($slug) {
        $sql = "SELECT a.*, u.username as author_name
                FROM `{$this->table}` a
                LEFT JOIN `users` u ON a.author_id = u.id
                WHERE a.slug = ?";
        return $this->db->fetch($sql, [$slug]);
    }
    
    /**
     * Yayınlanmış sözleşmeyi slug ile getirir
     */
    public function findPublishedBySlug($slug) {
        $sql = "SELECT a.*, u.username as author_name
                FROM `{$this->table}` a
                LEFT JOIN `users` u ON a.author_id = u.id
                WHERE a.slug = ? AND a.status = 'published'";
        return $this->db->fetch($sql, [$slug]);
    }
    
    /**
     * Türe göre yayınlanmış sözleşmeyi getirir (her türden en güncel olan)
     */
    public function findPublishedByType($type) {
        $sql = "SELECT a.*, u.username as author_name
                FROM `{$this->table}` a
                LEFT JOIN `users` u ON a.author_id = u.id
                WHERE a.type = ? AND a.status = 'published'
                ORDER BY a.updated_at DESC
                LIMIT 1";
        return $this->db->fetch($sql, [$type]);
    }
    
    /**
     * ID'ye göre detaylı sözleşme getirir
     */
    public function findWithDetails($id) {
        $sql = "SELECT a.*, u.username as author_name
                FROM `{$this->table}` a
                LEFT JOIN `users` u ON a.author_id = u.id
                WHERE a.id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Sözleşme oluşturur
     */
    public function createAgreement($data) {
        // Slug oluştur
        if (empty($data['slug'])) {
            $data['slug'] = $this->createSlug($data['title']);
        }
        
        // Yayın tarihi
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        
        // Versiyon
        $data['version'] = 1;
        
        return $this->create($data);
    }
    
    /**
     * Sözleşme günceller ve versiyon oluşturur
     */
    public function updateAgreement($id, $data, $changeNote = null, $authorId = null) {
        // Mevcut sözleşmeyi al
        $current = $this->find($id);
        
        if (!$current) {
            return false;
        }
        
        // Slug güncelle
        if (isset($data['title']) && empty($data['slug'])) {
            $data['slug'] = $this->createSlug($data['title'], $id);
        }
        
        // İçerik değiştiyse versiyon oluştur
        $contentChanged = isset($data['content']) && $data['content'] !== $current['content'];
        $titleChanged = isset($data['title']) && $data['title'] !== $current['title'];
        
        if ($contentChanged || $titleChanged) {
            // Önceki versiyonu kaydet
            $this->createVersion($id, $current, $changeNote, $authorId);
            
            // Versiyon numarasını artır
            $data['version'] = ($current['version'] ?? 1) + 1;
        }
        
        // Yayınlandıysa ve tarih yoksa ekle
        if (isset($data['status']) && $data['status'] === 'published') {
            if (empty($current['published_at']) && empty($data['published_at'])) {
                $data['published_at'] = date('Y-m-d H:i:s');
            }
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Versiyon oluşturur
     */
    private function createVersion($agreementId, $currentData, $changeNote = null, $authorId = null) {
        $sql = "INSERT INTO `agreement_versions` 
                (`agreement_id`, `version_number`, `title`, `content`, `change_note`, `author_id`, `created_at`)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        return $this->db->query($sql, [
            $agreementId,
            $currentData['version'] ?? 1,
            $currentData['title'],
            $currentData['content'],
            $changeNote,
            $authorId
        ]);
    }
    
    /**
     * Sözleşmenin versiyonlarını getirir
     */
    public function getVersions($agreementId) {
        $sql = "SELECT v.*, u.username as author_name
                FROM `agreement_versions` v
                LEFT JOIN `users` u ON v.author_id = u.id
                WHERE v.agreement_id = ?
                ORDER BY v.version_number DESC";
        return $this->db->fetchAll($sql, [$agreementId]);
    }
    
    /**
     * Belirli bir versiyonu getirir
     */
    public function getVersion($versionId) {
        $sql = "SELECT v.*, u.username as author_name, a.title as current_title
                FROM `agreement_versions` v
                LEFT JOIN `users` u ON v.author_id = u.id
                LEFT JOIN `agreements` a ON v.agreement_id = a.id
                WHERE v.id = ?";
        return $this->db->fetch($sql, [$versionId]);
    }
    
    /**
     * Eski versiyona geri döner
     */
    public function restoreVersion($versionId, $authorId = null) {
        // Versiyonu al
        $version = $this->getVersion($versionId);
        
        if (!$version) {
            return false;
        }
        
        // Mevcut sözleşmeyi al
        $current = $this->find($version['agreement_id']);
        
        if (!$current) {
            return false;
        }
        
        // Mevcut durumu versiyon olarak kaydet
        $this->createVersion(
            $version['agreement_id'],
            $current,
            'Versiyon ' . $version['version_number'] . ' geri yüklendi',
            $authorId
        );
        
        // Eski versiyonun içeriğini geri yükle
        $newVersion = ($current['version'] ?? 1) + 1;
        
        return $this->update($version['agreement_id'], [
            'title' => $version['title'],
            'content' => $version['content'],
            'version' => $newVersion
        ]);
    }
    
    /**
     * Duruma göre sayı getirir
     */
    public function getCountByStatus($status = null) {
        if ($status) {
            $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `status` = ?";
            $result = $this->db->fetch($sql, [$status]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM `{$this->table}`";
            $result = $this->db->fetch($sql);
        }
        return $result['count'] ?? 0;
    }
    
    /**
     * Türe göre sayı getirir
     */
    public function getCountByType($type) {
        $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `type` = ?";
        $result = $this->db->fetch($sql, [$type]);
        return $result['count'] ?? 0;
    }
    
    /**
     * Benzersiz slug oluşturur
     */
    private function createSlug($title, $excludeId = null) {
        $slug = $this->slugify($title);
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
        
        // Maksimum uzunluk
        if (strlen($text) > 200) {
            $text = substr($text, 0, 200);
            $text = rtrim($text, '-');
        }
        
        return $text;
    }
    
    /**
     * Sözleşme türü etiketini döndürür
     */
    public static function getTypeLabel($type) {
        return self::$types[$type] ?? 'Bilinmiyor';
    }
    
    /**
     * Sözleşmeyi siler (soft delete yerine hard delete)
     */
    public function deleteAgreement($id) {
        // Önce versiyonları sil (foreign key cascade ile otomatik silinir)
        return $this->delete($id);
    }
}

