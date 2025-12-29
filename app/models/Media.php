<?php
/**
 * Media Model
 * İçerik kütüphanesi için medya dosyaları yönetimi
 */

class Media extends Model {
    protected $table = 'media';
    
    /**
     * Tüm medya dosyalarını getirir
     */
    public function getAll($orderBy = 'created_at DESC') {
        return $this->all($orderBy);
    }
    
    /**
     * Sayfalı medya listesi
     */
    public function getPaginated($page = 1, $perPage = 24, $type = null, $search = null) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM `{$this->table}` WHERE 1=1";
        $params = [];
        
        // Tip filtresi
        if ($type && $type !== 'all') {
            if ($type === 'image') {
                $sql .= " AND `mime_type` LIKE 'image/%'";
            } else if ($type === 'video') {
                $sql .= " AND `mime_type` LIKE 'video/%'";
            } else if ($type === 'document') {
                $sql .= " AND (`mime_type` LIKE 'application/pdf%' OR `mime_type` LIKE 'application/msword%' OR `mime_type` LIKE 'application/vnd%' OR `mime_type` LIKE 'text/%')";
            } else if ($type === 'audio') {
                $sql .= " AND `mime_type` LIKE 'audio/%'";
            }
        }
        
        // Arama filtresi
        if ($search) {
            $sql .= " AND (`original_name` LIKE ? OR `alt_text` LIKE ? OR `description` LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Toplam sayı
        $countSql = str_replace("SELECT *", "SELECT COUNT(*) as total", $sql);
        $countResult = $this->db->fetch($countSql, $params);
        $total = $countResult['total'] ?? 0;
        
        // Sayfalama
        $sql .= " ORDER BY `created_at` DESC LIMIT {$perPage} OFFSET {$offset}";
        $items = $this->db->fetchAll($sql, $params);
        
        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * Kullanıcıya göre medya dosyalarını getirir
     */
    public function getByUser($userId, $limit = null) {
        $sql = "SELECT * FROM `{$this->table}` WHERE `user_id` = ? ORDER BY `created_at` DESC";
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        return $this->db->fetchAll($sql, [$userId]);
    }
    
    /**
     * MIME tipine göre medya dosyalarını getirir
     */
    public function getByMimeType($mimeType) {
        $sql = "SELECT * FROM `{$this->table}` WHERE `mime_type` LIKE ? ORDER BY `created_at` DESC";
        return $this->db->fetchAll($sql, [$mimeType . '%']);
    }
    
    /**
     * Sadece resimleri getirir
     */
    public function getImages($limit = null) {
        $sql = "SELECT * FROM `{$this->table}` WHERE `mime_type` LIKE 'image/%' ORDER BY `created_at` DESC";
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Sadece videoları getirir
     */
    public function getVideos($limit = null) {
        $sql = "SELECT * FROM `{$this->table}` WHERE `mime_type` LIKE 'video/%' ORDER BY `created_at` DESC";
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Medya dosyası yükle
     */
    public function uploadFile($file, $userId, $folder = 'media') {
        // İzin verilen dosya türleri
        $allowedTypes = [
            // Resimler
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            // Videolar
            'video/mp4' => 'mp4',
            'video/webm' => 'webm',
            'video/quicktime' => 'mov',
            'video/x-msvideo' => 'avi',
            // Sesler
            'audio/mpeg' => 'mp3',
            'audio/wav' => 'wav',
            'audio/ogg' => 'ogg',
            // Belgeler
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'text/plain' => 'txt',
            'application/zip' => 'zip',
            'application/x-rar-compressed' => 'rar'
        ];
        
        // Maksimum dosya boyutu (50MB)
        $maxSize = 50 * 1024 * 1024;
        
        // Dosya kontrolü
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['success' => false, 'message' => 'Dosya yüklenemedi'];
        }
        
        // Dosya boyutu kontrolü
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'Dosya boyutu çok büyük. Maksimum 50MB olabilir.'];
        }
        
        // MIME tipi kontrolü
        $mimeType = $file['type'];
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        }
        
        if (!isset($allowedTypes[$mimeType])) {
            return ['success' => false, 'message' => 'Bu dosya türü desteklenmiyor.'];
        }
        
        // Upload klasörünü oluştur
        $uploadBaseDir = __DIR__ . '/../../public/uploads/';
        $uploadDir = $uploadBaseDir . $folder . '/';
        
        // Yıl/Ay klasör yapısı
        $yearMonth = date('Y/m');
        $uploadDir .= $yearMonth . '/';
        
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                return ['success' => false, 'message' => 'Yükleme klasörü oluşturulamadı.'];
            }
        }
        
        // Benzersiz dosya adı oluştur
        $extension = $allowedTypes[$mimeType];
        $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
        $cleanName = $this->sanitizeFilename($originalName);
        $filename = $cleanName . '-' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Dosyayı yükle
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'message' => 'Dosya yüklenirken bir hata oluştu.'];
        }
        
        // Göreli yol
        $relativePath = $folder . '/' . $yearMonth . '/' . $filename;
        
        // URL oluştur
        $fileUrl = site_url('uploads/' . $relativePath);
        
        // Veritabanına kaydet
        $mediaData = [
            'user_id' => $userId,
            'filename' => $filename,
            'original_name' => $file['name'],
            'mime_type' => $mimeType,
            'file_size' => $file['size'],
            'file_path' => $relativePath,
            'file_url' => $fileUrl,
            'alt_text' => null,
            'description' => null
        ];
        
        $mediaId = $this->create($mediaData);
        
        if (!$mediaId) {
            // Veritabanı hatası durumunda dosyayı sil
            @unlink($filepath);
            return ['success' => false, 'message' => 'Veritabanı kaydı oluşturulamadı.'];
        }
        
        $media = $this->find($mediaId);
        
        return [
            'success' => true,
            'message' => 'Dosya başarıyla yüklendi.',
            'media' => $media
        ];
    }
    
    /**
     * Çoklu dosya yükleme
     */
    public function uploadMultiple($files, $userId, $folder = 'media') {
        $results = [];
        
        // $_FILES dizisini düzenle
        $fileCount = count($files['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];
            
            if ($file['error'] === UPLOAD_ERR_OK) {
                $results[] = $this->uploadFile($file, $userId, $folder);
            } else {
                $results[] = [
                    'success' => false,
                    'message' => 'Yükleme hatası: ' . $this->getUploadErrorMessage($file['error']),
                    'filename' => $file['name']
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Medya dosyasını güncelle
     */
    public function updateMedia($id, $data) {
        $allowedFields = ['alt_text', 'description'];
        $updateData = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        
        if (empty($updateData)) {
            return false;
        }
        
        return $this->update($id, $updateData);
    }
    
    /**
     * Medya dosyasını sil
     */
    public function deleteMedia($id) {
        $media = $this->find($id);
        
        if (!$media) {
            return ['success' => false, 'message' => 'Medya bulunamadı.'];
        }
        
        // Dosyayı sil
        $filePath = __DIR__ . '/../../public/uploads/' . $media['file_path'];
        
        if (file_exists($filePath)) {
            if (!@unlink($filePath)) {
                // Dosya silinemedi ama veritabanından silebiliriz
            }
        }
        
        // Veritabanından sil
        $this->delete($id);
        
        return ['success' => true, 'message' => 'Medya başarıyla silindi.'];
    }
    
    /**
     * Toplu silme
     */
    public function deleteMultiple($ids) {
        $results = [];
        
        foreach ($ids as $id) {
            $results[$id] = $this->deleteMedia($id);
        }
        
        return $results;
    }
    
    /**
     * Dosya adını temizle
     */
    private function sanitizeFilename($filename) {
        // Türkçe karakterleri değiştir
        $tr = ['ş', 'Ş', 'ı', 'İ', 'ğ', 'Ğ', 'ü', 'Ü', 'ö', 'Ö', 'ç', 'Ç'];
        $en = ['s', 'S', 'i', 'I', 'g', 'G', 'u', 'U', 'o', 'O', 'c', 'C'];
        $filename = str_replace($tr, $en, $filename);
        
        // Sadece harf, rakam, tire ve alt çizgi bırak
        $filename = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $filename);
        
        // Birden fazla tire varsa teke indir
        $filename = preg_replace('/-+/', '-', $filename);
        
        // Baş ve sondaki tireleri kaldır
        $filename = trim($filename, '-');
        
        // Boşsa varsayılan isim ver
        if (empty($filename)) {
            $filename = 'file';
        }
        
        // Uzunluğu sınırla
        return substr($filename, 0, 100);
    }
    
    /**
     * Yükleme hata mesajları
     */
    private function getUploadErrorMessage($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'Dosya boyutu sunucu limitini aşıyor.',
            UPLOAD_ERR_FORM_SIZE => 'Dosya boyutu form limitini aşıyor.',
            UPLOAD_ERR_PARTIAL => 'Dosya kısmen yüklendi.',
            UPLOAD_ERR_NO_FILE => 'Dosya yüklenmedi.',
            UPLOAD_ERR_NO_TMP_DIR => 'Geçici klasör bulunamadı.',
            UPLOAD_ERR_CANT_WRITE => 'Dosya diske yazılamadı.',
            UPLOAD_ERR_EXTENSION => 'PHP uzantısı yüklemeyi durdurdu.'
        ];
        
        return $errors[$errorCode] ?? 'Bilinmeyen hata.';
    }
    
    /**
     * Dosya tipini belirle
     */
    public function getFileType($mimeType) {
        if (strpos($mimeType, 'image/') === 0) {
            return 'image';
        } else if (strpos($mimeType, 'video/') === 0) {
            return 'video';
        } else if (strpos($mimeType, 'audio/') === 0) {
            return 'audio';
        } else {
            return 'document';
        }
    }
    
    /**
     * Dosya boyutunu formatla
     */
    public function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Disk kullanımı bilgisi
     */
    public function getDiskUsage() {
        $sql = "SELECT 
                    COUNT(*) as total_files,
                    SUM(file_size) as total_size,
                    SUM(CASE WHEN mime_type LIKE 'image/%' THEN 1 ELSE 0 END) as image_count,
                    SUM(CASE WHEN mime_type LIKE 'video/%' THEN 1 ELSE 0 END) as video_count,
                    SUM(CASE WHEN mime_type LIKE 'audio/%' THEN 1 ELSE 0 END) as audio_count,
                    SUM(CASE WHEN mime_type NOT LIKE 'image/%' AND mime_type NOT LIKE 'video/%' AND mime_type NOT LIKE 'audio/%' THEN 1 ELSE 0 END) as document_count
                FROM `{$this->table}`";
        
        return $this->db->fetch($sql);
    }
    
    /**
     * Son yüklemeler
     */
    public function getRecent($limit = 10) {
        $sql = "SELECT * FROM `{$this->table}` ORDER BY `created_at` DESC LIMIT {$limit}";
        return $this->db->fetchAll($sql);
    }
}

