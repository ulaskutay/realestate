<?php
/**
 * Agreement Controller - Sözleşme Yönetimi
 * Gizlilik Politikası, KVKK, Kullanım Şartları, Çerez Politikası vb.
 */

// Model dosyalarını yükle
require_once __DIR__ . '/../models/Agreement.php';
require_once __DIR__ . '/../models/AgreementVersion.php';

class AgreementController extends Controller {
    private $agreementModel;
    private $versionModel;
    
    public function __construct() {
        $this->agreementModel = new Agreement();
        $this->versionModel = new AgreementVersion();
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
     * Sözleşme listesi
     */
    public function index() {
        $this->checkPermission('agreements.view');
        
        $user = get_logged_in_user();
        $typeFilter = $_GET['type'] ?? 'all';
        $statusFilter = $_GET['status'] ?? 'all';
        
        // Sözleşmeleri getir
        $agreements = $this->agreementModel->getAll();
        
        // Filtreleme
        if ($typeFilter !== 'all') {
            $agreements = array_filter($agreements, function($a) use ($typeFilter) {
                return $a['type'] === $typeFilter;
            });
        }
        
        if ($statusFilter !== 'all') {
            $agreements = array_filter($agreements, function($a) use ($statusFilter) {
                return $a['status'] === $statusFilter;
            });
        }
        
        // İstatistikler
        $stats = [
            'all' => $this->agreementModel->getCountByStatus(),
            'published' => $this->agreementModel->getCountByStatus('published'),
            'draft' => $this->agreementModel->getCountByStatus('draft')
        ];
        
        $data = [
            'title' => 'Sözleşmeler',
            'user' => $user,
            'agreements' => $agreements,
            'types' => Agreement::$types,
            'typeFilter' => $typeFilter,
            'statusFilter' => $statusFilter,
            'stats' => $stats,
            'message' => $_SESSION['agreement_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['agreement_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null)
        ];
        
        unset($_SESSION['agreement_message'], $_SESSION['error_message']);
        
        $this->view('admin/agreements/index', $data);
    }
    
    /**
     * Yeni sözleşme formu
     */
    public function create() {
        $this->checkPermission('agreements.create');
        
        $data = [
            'title' => 'Yeni Sözleşme',
            'user' => get_logged_in_user(),
            'types' => Agreement::$types
        ];
        
        $this->view('admin/agreements/create', $data);
    }
    
    /**
     * Sözleşme kaydet
     */
    public function store() {
        $this->checkPermission('agreements.create');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('agreements'));
            return;
        }
        
        $user = get_logged_in_user();
        
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'content' => $_POST['content'] ?? '',
            'type' => $_POST['type'] ?? 'other',
            'status' => $_POST['status'] ?? 'draft',
            'author_id' => $user['id'],
            'meta_title' => trim($_POST['meta_title'] ?? ''),
            'meta_description' => trim($_POST['meta_description'] ?? '')
        ];
        
        // Validasyon
        if (empty($data['title'])) {
            $_SESSION['error_message'] = 'Başlık zorunludur!';
            $this->redirect(admin_url('agreements/create'));
            return;
        }
        
        $agreementId = $this->agreementModel->createAgreement($data);
        
        if ($agreementId) {
            $_SESSION['agreement_message'] = 'Sözleşme başarıyla oluşturuldu!';
            $this->redirect(admin_url('agreements/edit/' . $agreementId));
        } else {
            $_SESSION['error_message'] = 'Sözleşme oluşturulurken hata oluştu!';
            $this->redirect(admin_url('agreements/create'));
        }
    }
    
    /**
     * Sözleşme düzenleme formu
     */
    public function edit($id) {
        $this->checkPermission('agreements.edit');
        
        $agreement = $this->agreementModel->findWithDetails($id);
        
        if (!$agreement) {
            $_SESSION['error_message'] = 'Sözleşme bulunamadı!';
            $this->redirect(admin_url('agreements'));
            return;
        }
        
        // Versiyonları al
        $versions = $this->agreementModel->getVersions($id);
        
        $data = [
            'title' => 'Sözleşme Düzenle',
            'user' => get_logged_in_user(),
            'agreement' => $agreement,
            'versions' => $versions,
            'types' => Agreement::$types,
            'message' => $_SESSION['agreement_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['agreement_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null)
        ];
        
        unset($_SESSION['agreement_message'], $_SESSION['error_message']);
        
        $this->view('admin/agreements/edit', $data);
    }
    
    /**
     * Sözleşme güncelle
     */
    public function update($id) {
        $this->checkPermission('agreements.edit');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('agreements'));
            return;
        }
        
        $agreement = $this->agreementModel->find($id);
        
        if (!$agreement) {
            $_SESSION['error_message'] = 'Sözleşme bulunamadı!';
            $this->redirect(admin_url('agreements'));
            return;
        }
        
        $user = get_logged_in_user();
        
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'content' => $_POST['content'] ?? '',
            'type' => $_POST['type'] ?? 'other',
            'status' => $_POST['status'] ?? 'draft',
            'meta_title' => trim($_POST['meta_title'] ?? ''),
            'meta_description' => trim($_POST['meta_description'] ?? '')
        ];
        
        $changeNote = trim($_POST['change_note'] ?? '');
        
        // Validasyon
        if (empty($data['title'])) {
            $_SESSION['error_message'] = 'Başlık zorunludur!';
            $this->redirect(admin_url('agreements/edit/' . $id));
            return;
        }
        
        $result = $this->agreementModel->updateAgreement($id, $data, $changeNote, $user['id']);
        
        if ($result) {
            $_SESSION['agreement_message'] = 'Sözleşme başarıyla güncellendi!';
        } else {
            $_SESSION['error_message'] = 'Sözleşme güncellenirken hata oluştu!';
        }
        
        $this->redirect(admin_url('agreements/edit/' . $id));
    }
    
    /**
     * Sözleşme sil
     */
    public function delete($id) {
        $this->checkPermission('agreements.delete');
        
        $agreement = $this->agreementModel->find($id);
        
        if (!$agreement) {
            $_SESSION['error_message'] = 'Sözleşme bulunamadı!';
            $this->redirect(admin_url('agreements'));
            return;
        }
        
        $result = $this->agreementModel->deleteAgreement($id);
        
        if ($result) {
            $_SESSION['agreement_message'] = 'Sözleşme başarıyla silindi!';
        } else {
            $_SESSION['error_message'] = 'Sözleşme silinirken hata oluştu!';
        }
        
        $this->redirect(admin_url('agreements'));
    }
    
    /**
     * Sözleşme durumunu değiştir
     */
    public function toggleStatus($id) {
        $this->checkPermission('agreements.edit');
        
        $agreement = $this->agreementModel->find($id);
        
        if (!$agreement) {
            $_SESSION['error_message'] = 'Sözleşme bulunamadı!';
            $this->redirect(admin_url('agreements'));
            return;
        }
        
        $newStatus = $agreement['status'] === 'published' ? 'draft' : 'published';
        
        $updateData = ['status' => $newStatus];
        
        // Yayınlanıyorsa tarih ekle
        if ($newStatus === 'published' && empty($agreement['published_at'])) {
            $updateData['published_at'] = date('Y-m-d H:i:s');
        }
        
        $this->agreementModel->update($id, $updateData);
        
        $_SESSION['agreement_message'] = $newStatus === 'published' ? 'Sözleşme yayınlandı!' : 'Sözleşme taslak olarak kaydedildi!';
        $this->redirect(admin_url('agreements'));
    }
    
    /**
     * Versiyon geçmişi
     */
    public function versions($id) {
        $this->checkPermission('agreements.view');
        
        $agreement = $this->agreementModel->findWithDetails($id);
        
        if (!$agreement) {
            $_SESSION['error_message'] = 'Sözleşme bulunamadı!';
            $this->redirect(admin_url('agreements'));
            return;
        }
        
        $versions = $this->agreementModel->getVersions($id);
        
        $data = [
            'title' => 'Versiyon Geçmişi: ' . $agreement['title'],
            'user' => get_logged_in_user(),
            'agreement' => $agreement,
            'versions' => $versions,
            'message' => $_SESSION['agreement_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['agreement_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null)
        ];
        
        unset($_SESSION['agreement_message'], $_SESSION['error_message']);
        
        $this->view('admin/agreements/versions', $data);
    }
    
    /**
     * Versiyon detayı (AJAX)
     */
    public function viewVersion($versionId) {
        $this->checkPermission('agreements.view');
        
        header('Content-Type: application/json');
        
        $version = $this->versionModel->findWithDetails($versionId);
        
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
        $this->checkPermission('agreements.edit');
        
        $version = $this->versionModel->findWithDetails($versionId);
        
        if (!$version) {
            $_SESSION['error_message'] = 'Versiyon bulunamadı!';
            $this->redirect(admin_url('agreements'));
            return;
        }
        
        $user = get_logged_in_user();
        $result = $this->agreementModel->restoreVersion($versionId, $user['id']);
        
        if ($result) {
            $_SESSION['agreement_message'] = 'Versiyon ' . $version['version_number'] . ' başarıyla geri yüklendi!';
        } else {
            $_SESSION['error_message'] = 'Versiyon geri yüklenirken hata oluştu!';
        }
        
        $this->redirect(admin_url('agreements/edit/' . $version['agreement_id']));
    }
    
    /**
     * Frontend - Sözleşme göster
     */
    public function show($slug) {
        // Model'leri yükle (frontend erişimi için)
        require_once __DIR__ . '/../models/Agreement.php';
        require_once __DIR__ . '/../models/AgreementVersion.php';
        
        $agreementModel = new Agreement();
        $agreement = $agreementModel->findPublishedBySlug($slug);
        
        if (!$agreement) {
            // 404 sayfası göster
            http_response_code(404);
            require_once __DIR__ . '/../../core/ViewRenderer.php';
            $renderer = ViewRenderer::getInstance();
            $renderer->setLayout('default');
            $renderer->render('frontend/404', ['title' => 'Sayfa Bulunamadı', 'current_page' => '404']);
            return;
        }
        
        require_once __DIR__ . '/../../core/ViewRenderer.php';
        $renderer = ViewRenderer::getInstance();
        $renderer->setLayout('default');
        $renderer->render('frontend/agreements/single', [
            'title' => $agreement['meta_title'] ?: $agreement['title'],
            'meta_description' => $agreement['meta_description'] ?: '',
            'agreement' => $agreement,
            'current_page' => 'agreement'
        ]);
    }
}

