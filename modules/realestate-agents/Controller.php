<?php
/**
 * Real Estate Agents Module Controller (standalone)
 * Tema bağımsız özel modül.
 */

class RealEstateAgentsController {

    private $model;
    private $moduleInfo;

    public function __construct() {
        require_once __DIR__ . '/Model.php';
        $this->model = new RealEstateAgentsModel();
    }

    public function setModuleInfo($info) {
        $this->moduleInfo = $info;
    }

    public function onLoad() {}

    public function onActivate() {
        $this->model->createTable();
    }

    public function onDeactivate() {}

    private function requireLogin() {
        if (!is_user_logged_in()) {
            header('Location: ' . admin_url('login'));
            exit;
        }
    }

    private function checkPermission($permission) {
        $this->requireLogin();
        if (!current_user_can($permission)) {
            $_SESSION['error_message'] = 'Bu işlem için yetkiniz bulunmamaktadır!';
            header('Location: ' . admin_url('dashboard'));
            exit;
        }
    }

    private function adminView($view, $data = []) {
        $data['rootPath'] = dirname(dirname(__DIR__));
        extract($data);
        $viewPath = __DIR__ . '/views/admin/' . $view . '.php';
        if (!file_exists($viewPath)) {
            die("View not found: $viewPath");
        }
        include $viewPath;
    }

    public function admin_index() {
        $this->checkPermission('realestate-agents.view');
        $agents = $this->model->getAll();
        $data = [
            'title' => 'Emlak Danışmanları',
            'user' => get_logged_in_user(),
            'agents' => $agents,
            'message' => $_SESSION['agents_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['agents_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null)
        ];
        unset($_SESSION['agents_message'], $_SESSION['error_message']);
        $this->adminView('index', $data);
    }

    public function admin_create() {
        $this->checkPermission('realestate-agents.create');
        $data = [
            'title' => 'Yeni Danışman Ekle',
            'user' => get_logged_in_user(),
            'agent' => null
        ];
        $this->adminView('create', $data);
    }

    public function admin_store() {
        $this->checkPermission('realestate-agents.create');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . admin_url('module/realestate-agents'));
            exit;
        }
        $fullName = trim($_POST['first_name'] ?? '') . ' ' . trim($_POST['last_name'] ?? '');
        $slug = $this->generateSlug($fullName);
        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'slug' => $slug,
            'photo' => trim($_POST['photo'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'specializations' => trim($_POST['specializations'] ?? ''),
            'experience_years' => intval($_POST['experience_years'] ?? 0),
            'bio' => $_POST['bio'] ?? '',
            'facebook' => trim($_POST['facebook'] ?? ''),
            'twitter' => trim($_POST['twitter'] ?? ''),
            'instagram' => trim($_POST['instagram'] ?? ''),
            'linkedin' => trim($_POST['linkedin'] ?? ''),
            'status' => trim($_POST['status'] ?? 'active'),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'display_order' => intval($_POST['display_order'] ?? 0)
        ];
        if (empty($data['first_name']) || empty($data['last_name'])) {
            $_SESSION['error_message'] = 'Ad ve soyad zorunludur!';
            header('Location: ' . admin_url('module/realestate-agents/create'));
            exit;
        }
        $id = $this->model->create($data);
        if ($id) {
            $_SESSION['agents_message'] = 'Danışman başarıyla oluşturuldu!';
            header('Location: ' . admin_url('module/realestate-agents/edit/' . $id));
        } else {
            $_SESSION['error_message'] = 'Danışman oluşturulurken bir hata oluştu!';
            header('Location: ' . admin_url('module/realestate-agents/create'));
        }
        exit;
    }

    public function admin_edit($id) {
        $this->checkPermission('realestate-agents.edit');
        $agent = $this->model->find($id);
        if (!$agent) {
            $_SESSION['error_message'] = 'Danışman bulunamadı!';
            header('Location: ' . admin_url('module/realestate-agents'));
            exit;
        }
        $data = [
            'title' => 'Danışman Düzenle',
            'user' => get_logged_in_user(),
            'agent' => $agent,
            'message' => $_SESSION['agents_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['agents_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null)
        ];
        unset($_SESSION['agents_message'], $_SESSION['error_message']);
        $this->adminView('edit', $data);
    }

    public function admin_update($id) {
        $this->checkPermission('realestate-agents.edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . admin_url('module/realestate-agents'));
            exit;
        }
        $agent = $this->model->find($id);
        if (!$agent) {
            $_SESSION['error_message'] = 'Danışman bulunamadı!';
            header('Location: ' . admin_url('module/realestate-agents'));
            exit;
        }
        $fullName = trim($_POST['first_name'] ?? '') . ' ' . trim($_POST['last_name'] ?? '');
        $slug = $this->generateSlug(trim($_POST['slug'] ?? $fullName), $id);
        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'slug' => $slug,
            'photo' => trim($_POST['photo'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'specializations' => trim($_POST['specializations'] ?? ''),
            'experience_years' => intval($_POST['experience_years'] ?? 0),
            'bio' => $_POST['bio'] ?? '',
            'facebook' => trim($_POST['facebook'] ?? ''),
            'twitter' => trim($_POST['twitter'] ?? ''),
            'instagram' => trim($_POST['instagram'] ?? ''),
            'linkedin' => trim($_POST['linkedin'] ?? ''),
            'status' => trim($_POST['status'] ?? 'active'),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'display_order' => intval($_POST['display_order'] ?? 0)
        ];
        if (empty($data['first_name']) || empty($data['last_name'])) {
            $_SESSION['error_message'] = 'Ad ve soyad zorunludur!';
            header('Location: ' . admin_url('module/realestate-agents/edit/' . $id));
            exit;
        }
        $result = $this->model->update($id, $data);
        if ($result) {
            $_SESSION['agents_message'] = 'Danışman başarıyla güncellendi!';
        } else {
            $_SESSION['error_message'] = 'Danışman güncellenirken bir hata oluştu!';
        }
        header('Location: ' . admin_url('module/realestate-agents/edit/' . $id));
        exit;
    }

    public function admin_delete($id) {
        $this->checkPermission('realestate-agents.delete');
        $result = $this->model->delete($id);
        if ($result) {
            $_SESSION['agents_message'] = 'Danışman başarıyla silindi!';
        } else {
            $_SESSION['error_message'] = 'Danışman silinirken bir hata oluştu!';
        }
        header('Location: ' . admin_url('module/realestate-agents'));
        exit;
    }

    public function frontend_index() {
        $agents = $this->model->getActive();
        $data = ['title' => 'Emlak Danışmanlarımız', 'agents' => $agents];
        extract($data);
        $viewPath = get_module_frontend_view('realestate-agents', 'index.php');
        if (!$viewPath) {
            $viewPath = __DIR__ . '/views/frontend/index.php';
        }
        include $viewPath;
    }

    public function frontend_detail($slug) {
        $agent = $this->model->findBySlug($slug);
        if (!$agent) {
            header('HTTP/1.0 404 Not Found');
            die('Danışman bulunamadı');
        }
        if (empty($agent['slug'])) {
            $fullName = $agent['first_name'] . ' ' . $agent['last_name'];
            $newSlug = $this->generateSlug($fullName, $agent['id']);
            $this->model->update($agent['id'], ['slug' => $newSlug]);
            $agent['slug'] = $newSlug;
        }
        $agentListings = [];
        $totalListings = 0;
        $listingsModelPath = dirname(__DIR__) . '/realestate-listings/Model.php';
        if (file_exists($listingsModelPath)) {
            require_once $listingsModelPath;
            if (class_exists('RealEstateListingsModel')) {
                $listingsModel = new RealEstateListingsModel();
                $agentListings = $listingsModel->getByRealtor($agent['id'], 12);
                $totalListings = $listingsModel->countByRealtor($agent['id']);
            }
        }
        $data = [
            'title' => $agent['first_name'] . ' ' . $agent['last_name'],
            'agent' => $agent,
            'agentListings' => $agentListings,
            'totalListings' => $totalListings
        ];
        extract($data);
        $viewPath = get_module_frontend_view('realestate-agents', 'detail.php');
        if (!$viewPath) {
            $viewPath = __DIR__ . '/views/frontend/detail.php';
        }
        include $viewPath;
    }

    public function getFeaturedAgents($limit = 6) {
        return $this->model->getFeatured($limit);
    }

    private function generateSlug($text, $excludeId = null) {
        if (empty($text)) return '';
        $tr = ['ş', 'Ş', 'ı', 'İ', 'ğ', 'Ğ', 'ü', 'Ü', 'ö', 'Ö', 'ç', 'Ç'];
        $en = ['s', 's', 'i', 'i', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c'];
        $text = str_replace($tr, $en, $text);
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');
        if (strlen($text) > 200) {
            $text = substr($text, 0, 200);
            $text = rtrim($text, '-');
        }
        if (empty($text)) $text = 'danisman-' . time();
        $originalSlug = $text;
        $counter = 1;
        while (true) {
            $existing = $this->model->findBySlug($text);
            if (!$existing || ($excludeId && (int)$existing['id'] === (int)$excludeId)) break;
            $text = $originalSlug . '-' . $counter;
            $counter++;
        }
        return $text;
    }
}
