<?php
/**
 * Media Library Controller
 * İçerik kütüphanesi yönetimi için controller
 */

class MediaLibraryController extends Controller {
    private $mediaModel;
    
    public function __construct() {
        $this->mediaModel = new Media();
    }
    
    /**
     * Giriş kontrolü
     */
    private function checkAuth() {
        if (!is_user_logged_in()) {
            $this->redirect(admin_url('login'));
        }
    }
    
    /**
     * Medya kütüphanesi ana sayfası
     */
    public function index() {
        $this->checkAuth();
        
        // Yetki kontrolü
        if (!current_user_can('media.view')) {
            $_SESSION['error_message'] = 'Bu modülde yetkiniz yoktur!';
            $this->redirect(admin_url('dashboard'));
        }
        
        $page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
        $type = $_GET['type'] ?? 'all';
        $search = $_GET['search'] ?? null;
        $perPage = 24;
        
        $result = $this->mediaModel->getPaginated($page, $perPage, $type, $search);
        $diskUsage = $this->mediaModel->getDiskUsage();
        
        $data = [
            'title' => 'İçerik Kütüphanesi',
            'user' => get_logged_in_user(),
            'media' => $result['items'],
            'pagination' => [
                'current' => $result['page'],
                'total' => $result['totalPages'],
                'totalItems' => $result['total']
            ],
            'filters' => [
                'type' => $type,
                'search' => $search
            ],
            'diskUsage' => $diskUsage,
            'message' => $_SESSION['media_message'] ?? null,
            'messageType' => $_SESSION['media_message_type'] ?? null
        ];
        
        // Session mesajlarını temizle
        unset($_SESSION['media_message']);
        unset($_SESSION['media_message_type']);
        
        $this->view('admin/media/index', $data);
    }
    
    /**
     * Dosya yükleme sayfası
     */
    public function upload() {
        $this->checkAuth();
        
        // Yetki kontrolü
        if (!current_user_can('media.create')) {
            $_SESSION['error_message'] = 'Dosya yükleme yetkiniz yoktur!';
            $this->redirect(admin_url('media'));
        }
        
        $data = [
            'title' => 'Dosya Yükle',
            'user' => get_logged_in_user()
        ];
        
        $this->view('admin/media/upload', $data);
    }
    
    /**
     * Dosya yükleme işlemi (AJAX)
     */
    public function uploadFile() {
        $this->checkAuth();
        
        // Yetki kontrolü
        if (!current_user_can('media.create')) {
            $this->json(['success' => false, 'message' => 'Dosya yükleme yetkiniz yoktur!'], 403);
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Geçersiz istek'], 400);
        }
        
        // Tekli dosya yükleme
        if (isset($_FILES['file'])) {
            $userId = $_SESSION['user_id'];
            $result = $this->mediaModel->uploadFile($_FILES['file'], $userId, 'media');
            
            if ($result['success']) {
                // Dosya bilgilerini zenginleştir
                $result['media']['file_type'] = $this->mediaModel->getFileType($result['media']['mime_type']);
                $result['media']['formatted_size'] = $this->mediaModel->formatFileSize($result['media']['file_size']);
            }
            
            $this->json($result);
        }
        
        // Çoklu dosya yükleme
        if (isset($_FILES['files'])) {
            $userId = $_SESSION['user_id'];
            $results = $this->mediaModel->uploadMultiple($_FILES['files'], $userId, 'media');
            
            $successCount = count(array_filter($results, function($r) { return $r['success']; }));
            $failCount = count($results) - $successCount;
            
            $this->json([
                'success' => $successCount > 0,
                'message' => "{$successCount} dosya başarıyla yüklendi" . ($failCount > 0 ? ", {$failCount} dosya yüklenemedi" : ""),
                'results' => $results
            ]);
        }
        
        $this->json(['success' => false, 'message' => 'Dosya bulunamadı'], 400);
    }
    
    /**
     * Medya detayı (AJAX)
     */
    public function getMedia($id) {
        $this->checkAuth();
        
        $media = $this->mediaModel->find($id);
        
        if (!$media) {
            $this->json(['success' => false, 'message' => 'Medya bulunamadı'], 404);
        }
        
        // Dosya tipini ekle
        $media['file_type'] = $this->mediaModel->getFileType($media['mime_type']);
        $media['formatted_size'] = $this->mediaModel->formatFileSize($media['file_size']);
        
        $this->json([
            'success' => true,
            'media' => $media
        ]);
    }
    
    /**
     * Medya güncelleme (AJAX)
     */
    public function updateMedia($id) {
        $this->checkAuth();
        
        // Yetki kontrolü
        if (!current_user_can('media.edit')) {
            $this->json(['success' => false, 'message' => 'Düzenleme yetkiniz yoktur!'], 403);
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Geçersiz istek'], 400);
        }
        
        $media = $this->mediaModel->find($id);
        
        if (!$media) {
            $this->json(['success' => false, 'message' => 'Medya bulunamadı'], 404);
        }
        
        $data = [
            'alt_text' => $_POST['alt_text'] ?? null,
            'description' => $_POST['description'] ?? null
        ];
        
        $this->mediaModel->updateMedia($id, $data);
        
        $updatedMedia = $this->mediaModel->find($id);
        $updatedMedia['file_type'] = $this->mediaModel->getFileType($updatedMedia['mime_type']);
        $updatedMedia['formatted_size'] = $this->mediaModel->formatFileSize($updatedMedia['file_size']);
        
        $this->json([
            'success' => true,
            'message' => 'Medya başarıyla güncellendi',
            'media' => $updatedMedia
        ]);
    }
    
    /**
     * Medya silme (AJAX)
     */
    public function deleteMedia($id) {
        $this->checkAuth();
        
        // Yetki kontrolü
        if (!current_user_can('media.delete')) {
            $this->json(['success' => false, 'message' => 'Silme yetkiniz yoktur!'], 403);
        }
        
        $result = $this->mediaModel->deleteMedia($id);
        $this->json($result);
    }
    
    /**
     * Toplu silme (AJAX)
     */
    public function deleteMultiple() {
        $this->checkAuth();
        
        // Yetki kontrolü
        if (!current_user_can('media.delete')) {
            $this->json(['success' => false, 'message' => 'Silme yetkiniz yoktur!'], 403);
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Geçersiz istek'], 400);
        }
        
        $ids = json_decode($_POST['ids'] ?? '[]', true);
        
        if (empty($ids)) {
            $this->json(['success' => false, 'message' => 'Silinecek dosya seçilmedi'], 400);
        }
        
        $results = $this->mediaModel->deleteMultiple($ids);
        
        $successCount = count(array_filter($results, function($r) { return $r['success']; }));
        
        $this->json([
            'success' => $successCount > 0,
            'message' => "{$successCount} dosya başarıyla silindi",
            'results' => $results
        ]);
    }
    
    /**
     * Medya listesi (AJAX - picker için)
     */
    public function getList() {
        $this->checkAuth();
        
        // 'p' parametresi kullanılıyor çünkü 'page' admin.php route için kullanılıyor
        $page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
        $type = $_GET['type'] ?? 'all';
        $search = $_GET['search'] ?? null;
        $perPage = 24;
        
        $result = $this->mediaModel->getPaginated($page, $perPage, $type, $search);
        
        // Dosya tiplerini ekle
        foreach ($result['items'] as &$item) {
            $item['file_type'] = $this->mediaModel->getFileType($item['mime_type']);
            $item['formatted_size'] = $this->mediaModel->formatFileSize($item['file_size']);
        }
        
        $this->json([
            'success' => true,
            'media' => $result['items'],
            'pagination' => [
                'current' => $result['page'],
                'total' => $result['totalPages'],
                'totalItems' => $result['total']
            ]
        ]);
    }
    
    /**
     * Disk kullanımı (AJAX)
     */
    public function getDiskUsage() {
        $this->checkAuth();
        
        $usage = $this->mediaModel->getDiskUsage();
        
        $this->json([
            'success' => true,
            'usage' => [
                'total_files' => (int)$usage['total_files'],
                'total_size' => (int)$usage['total_size'],
                'formatted_size' => $this->mediaModel->formatFileSize($usage['total_size'] ?? 0),
                'image_count' => (int)$usage['image_count'],
                'video_count' => (int)$usage['video_count'],
                'audio_count' => (int)$usage['audio_count'],
                'document_count' => (int)$usage['document_count']
            ]
        ]);
    }
}

