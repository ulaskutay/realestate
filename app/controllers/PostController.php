<?php
/**
 * Post Controller - Blog Yazı Yönetimi
 */

class PostController extends Controller {
    private $postModel;
    private $categoryModel;
    
    public function __construct() {
        $this->postModel = new Post();
        $this->categoryModel = new PostCategory();
    }
    
    /**
     * Giriş kontrolü
     */
    private function requireLogin() {
        if (!is_user_logged_in()) {
            $this->redirect(admin_url('login'));
            exit;
        }
    }
    
    /**
     * Yetki kontrolü
     */
    private function checkPermission($permission) {
        $this->requireLogin();
        
        if (!current_user_can($permission)) {
            $_SESSION['error_message'] = 'Bu modülde yetkiniz yoktur!';
            $this->redirect(admin_url('dashboard'));
            exit;
        }
    }
    
    /**
     * Yazı listesi (Tab sistemi: Yazılar ve Kategoriler)
     */
    public function index() {
        $this->checkPermission('posts.view');
        
        $user = get_logged_in_user();
        $activeTab = $_GET['tab'] ?? 'posts';
        $statusFilter = $_GET['status'] ?? 'all';
        
        // Yazıları getir - sadece type='post' olanlar
        if ($statusFilter === 'all') {
            $posts = $this->postModel->getAll();
        } else {
            $posts = $this->postModel->getByStatus($statusFilter);
        }
        
        // Kategorileri getir
        $categories = $this->categoryModel->getAll();
        
        // Her kategorinin yazı sayısını ekle
        foreach ($categories as &$cat) {
            $cat['post_count'] = $this->categoryModel->getPostCount($cat['id']);
        }
        
        // İstatistikler
        $stats = [
            'all' => $this->postModel->getCountByStatus(),
            'published' => $this->postModel->getCountByStatus('published'),
            'draft' => $this->postModel->getCountByStatus('draft'),
            'trash' => $this->postModel->getCountByStatus('trash')
        ];
        
        $data = [
            'title' => 'Yazılar',
            'user' => $user,
            'posts' => $posts,
            'categories' => $categories,
            'activeTab' => $activeTab,
            'statusFilter' => $statusFilter,
            'stats' => $stats,
            'message' => $_SESSION['post_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['post_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null)
        ];
        
        unset($_SESSION['post_message'], $_SESSION['error_message']);
        
        $this->view('admin/posts/index', $data);
    }
    
    /**
     * Yeni yazı formu
     */
    public function create() {
        $this->checkPermission('posts.create');
        
        $categories = $this->categoryModel->getHierarchicalList();
        
        $data = [
            'title' => 'Yeni Yazı',
            'user' => get_logged_in_user(),
            'categories' => $categories
        ];
        
        $this->view('admin/posts/create', $data);
    }
    
    /**
     * Yazı kaydet
     */
    public function store() {
        $this->checkPermission('posts.create');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('posts'));
            return;
        }
        
        $user = get_logged_in_user();
        
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'excerpt' => trim($_POST['excerpt'] ?? ''),
            'content' => $_POST['content'] ?? '',
            'featured_image' => trim($_POST['featured_image'] ?? ''),
            'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'author_id' => $user['id'],
            'status' => $_POST['status'] ?? 'draft',
            'visibility' => $_POST['visibility'] ?? 'public',
            'allow_comments' => isset($_POST['allow_comments']) ? 1 : 0,
            'meta_title' => trim($_POST['meta_title'] ?? ''),
            'meta_description' => trim($_POST['meta_description'] ?? ''),
            'meta_keywords' => trim($_POST['meta_keywords'] ?? '')
        ];
        
        // Şifreli yazı
        if ($data['visibility'] === 'password' && !empty($_POST['post_password'])) {
            $data['password'] = $_POST['post_password'];
        }
        
        // Zamanlanmış yayın
        if (!empty($_POST['published_at'])) {
            $data['published_at'] = $_POST['published_at'];
            if ($data['status'] === 'published' && strtotime($data['published_at']) > time()) {
                $data['status'] = 'scheduled';
            }
        }
        
        // Validasyon
        if (empty($data['title'])) {
            $_SESSION['error_message'] = 'Başlık zorunludur!';
            $this->redirect(admin_url('posts/create'));
            return;
        }
        
        $postId = $this->postModel->createPost($data);
        
        if ($postId) {
            $_SESSION['post_message'] = 'Yazı başarıyla oluşturuldu!';
            $this->redirect(admin_url('posts/edit/' . $postId));
        } else {
            $_SESSION['error_message'] = 'Yazı oluşturulurken hata oluştu!';
            $this->redirect(admin_url('posts/create'));
        }
    }
    
    /**
     * Yazı düzenleme formu
     */
    public function edit($id) {
        $this->checkPermission('posts.edit');
        
        $post = $this->postModel->findWithDetails($id);
        
        if (!$post) {
            $_SESSION['error_message'] = 'Yazı bulunamadı!';
            $this->redirect(admin_url('posts'));
            return;
        }
        
        $categories = $this->categoryModel->getHierarchicalList();
        
        // Versiyon geçmişini al
        $versions = $this->postModel->getVersions($id);
        
        $data = [
            'title' => 'Yazı Düzenle',
            'user' => get_logged_in_user(),
            'post' => $post,
            'categories' => $categories,
            'versions' => $versions,
            'message' => $_SESSION['post_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['post_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null)
        ];
        
        unset($_SESSION['post_message'], $_SESSION['error_message']);
        
        $this->view('admin/posts/edit', $data);
    }
    
    /**
     * Yazı güncelle
     */
    public function update($id) {
        $this->checkPermission('posts.edit');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('posts'));
            return;
        }
        
        $post = $this->postModel->find($id);
        
        if (!$post) {
            $_SESSION['error_message'] = 'Yazı bulunamadı!';
            $this->redirect(admin_url('posts'));
            return;
        }
        
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'excerpt' => trim($_POST['excerpt'] ?? ''),
            'content' => $_POST['content'] ?? '',
            'featured_image' => trim($_POST['featured_image'] ?? ''),
            'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'status' => $_POST['status'] ?? 'draft',
            'visibility' => $_POST['visibility'] ?? 'public',
            'allow_comments' => isset($_POST['allow_comments']) ? 1 : 0,
            'meta_title' => trim($_POST['meta_title'] ?? ''),
            'meta_description' => trim($_POST['meta_description'] ?? ''),
            'meta_keywords' => trim($_POST['meta_keywords'] ?? '')
        ];
        
        // Şifreli yazı
        if ($data['visibility'] === 'password' && !empty($_POST['post_password'])) {
            $data['password'] = $_POST['post_password'];
        } else {
            $data['password'] = null;
        }
        
        // Zamanlanmış yayın
        if (!empty($_POST['published_at'])) {
            $data['published_at'] = $_POST['published_at'];
            if ($data['status'] === 'published' && strtotime($data['published_at']) > time()) {
                $data['status'] = 'scheduled';
            }
        }
        
        // Validasyon
        if (empty($data['title'])) {
            $_SESSION['error_message'] = 'Başlık zorunludur!';
            $this->redirect(admin_url('posts/edit/' . $id));
            return;
        }
        
        $user = get_logged_in_user();
        $result = $this->postModel->updateWithVersion($id, $data, $user['id']);
        
        if ($result) {
            $_SESSION['post_message'] = 'Yazı başarıyla güncellendi!';
        } else {
            $_SESSION['error_message'] = 'Yazı güncellenirken hata oluştu!';
        }
        
        $this->redirect(admin_url('posts/edit/' . $id));
    }
    
    /**
     * Yazı sil
     */
    public function delete($id) {
        $this->checkPermission('posts.delete');
        
        $post = $this->postModel->find($id);
        
        if (!$post) {
            $_SESSION['error_message'] = 'Yazı bulunamadı!';
            $this->redirect(admin_url('posts'));
            return;
        }
        
        // Çöpe taşı veya tamamen sil
        if ($post['status'] === 'trash') {
            // Tamamen sil
            $this->postModel->delete($id);
            $_SESSION['post_message'] = 'Yazı kalıcı olarak silindi!';
        } else {
            // Çöpe taşı
            $this->postModel->update($id, ['status' => 'trash']);
            $_SESSION['post_message'] = 'Yazı çöpe taşındı!';
        }
        
        $this->redirect(admin_url('posts'));
    }
    
    /**
     * Yazıyı geri yükle
     */
    public function restore($id) {
        $this->checkPermission('posts.edit');
        
        $post = $this->postModel->find($id);
        
        if (!$post || $post['status'] !== 'trash') {
            $_SESSION['error_message'] = 'Yazı bulunamadı!';
            $this->redirect(admin_url('posts'));
            return;
        }
        
        $this->postModel->update($id, ['status' => 'draft']);
        $_SESSION['post_message'] = 'Yazı geri yüklendi!';
        
        $this->redirect(admin_url('posts'));
    }
    
    /**
     * Yazı durumunu değiştir
     */
    public function toggleStatus($id) {
        $this->checkPermission('posts.edit');
        
        $post = $this->postModel->find($id);
        
        if (!$post) {
            $_SESSION['error_message'] = 'Yazı bulunamadı!';
            $this->redirect(admin_url('posts'));
            return;
        }
        
        // Mevcut durumu logla (debug)
        error_log("Toggle Status - Post ID: {$id}, Current Status: {$post['status']}");
        
        $newStatus = $post['status'] === 'published' ? 'draft' : 'published';
        
        error_log("Toggle Status - New Status will be: {$newStatus}");
        
        $updateData = ['status' => $newStatus];
        
        // Yayınlanıyorsa tarih ekle
        if ($newStatus === 'published' && empty($post['published_at'])) {
            $updateData['published_at'] = date('Y-m-d H:i:s');
            error_log("Toggle Status - Setting published_at: {$updateData['published_at']}");
        }
        
        $result = $this->postModel->update($id, $updateData);
        error_log("Toggle Status - Update result: " . ($result ? 'SUCCESS' : 'FAILED'));
        
        $_SESSION['post_message'] = $newStatus === 'published' ? 'Yazı yayınlandı!' : 'Yazı taslak olarak kaydedildi!';
        $this->redirect(admin_url('posts'));
    }
    
    // ==================== VERSİYON İŞLEMLERİ ====================
    
    /**
     * Versiyon geçmişi sayfası
     */
    public function versions($id) {
        $this->checkPermission('posts.view');
        
        $post = $this->postModel->findWithDetails($id);
        
        if (!$post) {
            $_SESSION['error_message'] = 'Yazı bulunamadı!';
            $this->redirect(admin_url('posts'));
            return;
        }
        
        $versions = $this->postModel->getVersions($id);
        
        $data = [
            'title' => 'Versiyon Geçmişi: ' . $post['title'],
            'user' => get_logged_in_user(),
            'post' => $post,
            'versions' => $versions,
            'message' => $_SESSION['post_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['post_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null)
        ];
        
        unset($_SESSION['post_message'], $_SESSION['error_message']);
        
        $this->view('admin/posts/versions', $data);
    }
    
    /**
     * Versiyon detayı (AJAX)
     */
    public function viewVersion($versionId) {
        $this->checkPermission('posts.view');
        
        header('Content-Type: application/json');
        
        require_once __DIR__ . '/../models/PostVersion.php';
        $versionModel = new PostVersion();
        $version = $versionModel->findWithDetails($versionId);
        
        if (!$version) {
            echo json_encode(['success' => false, 'message' => 'Versiyon bulunamadı!']);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'version' => $version
        ]);
    }
    
    /**
     * Eski versiyona geri dön
     */
    public function restoreVersion($versionId) {
        $this->checkPermission('posts.edit');
        
        require_once __DIR__ . '/../models/PostVersion.php';
        $versionModel = new PostVersion();
        $version = $versionModel->findWithDetails($versionId);
        
        if (!$version) {
            $_SESSION['error_message'] = 'Versiyon bulunamadı!';
            $this->redirect(admin_url('posts'));
            return;
        }
        
        $user = get_logged_in_user();
        $result = $this->postModel->restoreVersion($versionId, $user['id']);
        
        if ($result) {
            $_SESSION['post_message'] = 'Versiyon ' . $version['version_number'] . ' başarıyla geri yüklendi!';
        } else {
            $_SESSION['error_message'] = 'Versiyon geri yüklenirken hata oluştu!';
        }
        
        $this->redirect(admin_url('posts/edit/' . $version['post_id']));
    }
    
    // ==================== KATEGORİ İŞLEMLERİ ====================
    
    /**
     * Yeni kategori formu
     */
    public function createCategory() {
        $this->checkPermission('posts.create');
        
        $categories = $this->categoryModel->getHierarchicalList();
        
        $data = [
            'title' => 'Yeni Kategori',
            'user' => get_logged_in_user(),
            'categories' => $categories
        ];
        
        $this->view('admin/posts/category-form', $data);
    }
    
    /**
     * Kategori kaydet
     */
    public function storeCategory() {
        $this->checkPermission('posts.create');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('posts?tab=categories'));
            return;
        }
        
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
            'status' => $_POST['status'] ?? 'active'
        ];
        
        // Validasyon
        if (empty($data['name'])) {
            $_SESSION['error_message'] = 'Kategori adı zorunludur!';
            $this->redirect(admin_url('posts/category/create'));
            return;
        }
        
        $categoryId = $this->categoryModel->createCategory($data);
        
        if ($categoryId) {
            $_SESSION['post_message'] = 'Kategori başarıyla oluşturuldu!';
        } else {
            $_SESSION['error_message'] = 'Kategori oluşturulurken hata oluştu!';
        }
        
        $this->redirect(admin_url('posts?tab=categories'));
    }
    
    /**
     * Kategori düzenleme formu
     */
    public function editCategory($id) {
        $this->checkPermission('posts.edit');
        
        $category = $this->categoryModel->find($id);
        
        if (!$category) {
            $_SESSION['error_message'] = 'Kategori bulunamadı!';
            $this->redirect(admin_url('posts?tab=categories'));
            return;
        }
        
        $categories = $this->categoryModel->getHierarchicalList();
        
        $data = [
            'title' => 'Kategori Düzenle',
            'user' => get_logged_in_user(),
            'category' => $category,
            'categories' => $categories,
            'message' => $_SESSION['post_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['post_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null)
        ];
        
        unset($_SESSION['post_message'], $_SESSION['error_message']);
        
        $this->view('admin/posts/category-form', $data);
    }
    
    /**
     * Kategori güncelle
     */
    public function updateCategory($id) {
        $this->checkPermission('posts.edit');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('posts?tab=categories'));
            return;
        }
        
        $category = $this->categoryModel->find($id);
        
        if (!$category) {
            $_SESSION['error_message'] = 'Kategori bulunamadı!';
            $this->redirect(admin_url('posts?tab=categories'));
            return;
        }
        
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
            'status' => $_POST['status'] ?? 'active'
        ];
        
        // Kendini parent yapamaz
        if ($data['parent_id'] == $id) {
            $data['parent_id'] = null;
        }
        
        // Validasyon
        if (empty($data['name'])) {
            $_SESSION['error_message'] = 'Kategori adı zorunludur!';
            $this->redirect(admin_url('posts/category/edit/' . $id));
            return;
        }
        
        $result = $this->categoryModel->updateCategory($id, $data);
        
        if ($result) {
            $_SESSION['post_message'] = 'Kategori başarıyla güncellendi!';
        } else {
            $_SESSION['error_message'] = 'Kategori güncellenirken hata oluştu!';
        }
        
        $this->redirect(admin_url('posts?tab=categories'));
    }
    
    /**
     * Kategori sil
     */
    public function deleteCategory($id) {
        $this->checkPermission('posts.delete');
        
        $category = $this->categoryModel->find($id);
        
        if (!$category) {
            $_SESSION['error_message'] = 'Kategori bulunamadı!';
            $this->redirect(admin_url('posts?tab=categories'));
            return;
        }
        
        // Bu kategorideki yazıları "kategorisiz" yap
        $db = get_db();
        $db->query("UPDATE `posts` SET `category_id` = NULL WHERE `category_id` = ?", [$id]);
        
        // Alt kategorilerin parent'ını kaldır
        $db->query("UPDATE `post_categories` SET `parent_id` = NULL WHERE `parent_id` = ?", [$id]);
        
        $this->categoryModel->delete($id);
        $_SESSION['post_message'] = 'Kategori başarıyla silindi!';
        
        $this->redirect(admin_url('posts?tab=categories'));
    }
}

