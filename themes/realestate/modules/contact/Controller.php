<?php
/**
 * Contact Module Controller
 * İletişim sayfası modülü
 */

class ContactController extends Controller {
    private $moduleInfo;
    
    public function __construct() {
        // Constructor
    }
    
    public function setModuleInfo($info) {
        $this->moduleInfo = $info;
    }
    
    public function onLoad() {
        // Module loaded
    }
    
    public function onActivate() {
        // Activation logic
    }
    
    public function onDeactivate() {
        // Deactivation logic
    }
    
    /**
     * Frontend: İletişim sayfası
     */
    public function frontend_index() {
        // ThemeLoader'ı yükle
        if (!class_exists('ThemeLoader')) {
            require_once __DIR__ . '/../../../../core/ThemeLoader.php';
        }
        $themeLoader = ThemeLoader::getInstance();
        
        // Functions dosyasını yükle (the_form fonksiyonu için)
        if (!function_exists('the_form')) {
            require_once __DIR__ . '/../../../../includes/functions.php';
        }
        
        // Site ve şirket bilgileri
        $siteName = get_option('site_name', 'Site Adı');
        $companyName = get_option('company_name', $siteName);
        $companyEmail = get_option('company_email', get_option('contact_email', ''));
        $companyPhone = get_option('company_phone', get_option('contact_phone', ''));
        $companyAddress = get_option('company_address', get_option('contact_address', ''));
        $companyCity = get_option('company_city', '');
        
        // Sosyal medya linkleri
        $socialLinks = [
            'facebook' => ['url' => get_option('social_facebook', ''), 'icon' => 'fab fa-facebook-f', 'label' => 'Facebook', 'color' => '#1877f2'],
            'instagram' => ['url' => get_option('social_instagram', ''), 'icon' => 'fab fa-instagram', 'label' => 'Instagram', 'color' => '#e4405f'],
            'twitter' => ['url' => get_option('social_twitter', ''), 'icon' => 'fab fa-x-twitter', 'label' => 'X (Twitter)', 'color' => '#000000'],
            'linkedin' => ['url' => get_option('social_linkedin', ''), 'icon' => 'fab fa-linkedin-in', 'label' => 'LinkedIn', 'color' => '#0a66c2'],
            'youtube' => ['url' => get_option('social_youtube', ''), 'icon' => 'fab fa-youtube', 'label' => 'YouTube', 'color' => '#ff0000'],
            'tiktok' => ['url' => get_option('social_tiktok', ''), 'icon' => 'fab fa-tiktok', 'label' => 'TikTok', 'color' => '#000000'],
            'pinterest' => ['url' => get_option('social_pinterest', ''), 'icon' => 'fab fa-pinterest-p', 'label' => 'Pinterest', 'color' => '#bd081c'],
        ];
        
        // Aktif sosyal medya linklerini filtrele
        $activeSocials = array_filter($socialLinks, fn($s) => !empty($s['url']));
        
        // Google Maps embed URL (opsiyonel)
        $mapEmbed = get_option('google_maps_embed', '');
        
        // Form mesajlarını al
        $message = $_SESSION['contact_message'] ?? null;
        $messageType = $_SESSION['contact_message_type'] ?? null;
        unset($_SESSION['contact_message'], $_SESSION['contact_message_type']);
        
        // Sayfa verileri
        $data = [
            'title' => 'İletişim',
            'meta_description' => 'Bizimle iletişime geçin',
            'current_page' => 'contact',
            'sections' => [],
            'themeLoader' => $themeLoader,
            'siteName' => $siteName,
            'companyName' => $companyName,
            'companyEmail' => $companyEmail,
            'companyPhone' => $companyPhone,
            'companyAddress' => $companyAddress,
            'companyCity' => $companyCity,
            'socialLinks' => $socialLinks,
            'activeSocials' => $activeSocials,
            'mapEmbed' => $mapEmbed,
            'message' => $message,
            'messageType' => $messageType
        ];
        
        $this->renderModuleView('frontend/index', $data);
    }
    
    /**
     * Frontend: İletişim formu gönderimi
     */
    public function frontend_submit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/contact');
            exit;
        }
        
        // FormController'ı kullan (mevcut form sistemi)
        require_once __DIR__ . '/../../../../app/controllers/FormController.php';
        $formController = new FormController();
        
        // 'iletisim' formunu gönder
        if (method_exists($formController, 'submit')) {
            $formController->submit();
        } else {
            // Fallback: Basit validasyon
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');
            
            // Basit validasyon
            if (empty($name) || empty($email) || empty($message)) {
                $_SESSION['contact_message'] = 'Lütfen tüm zorunlu alanları doldurun.';
                $_SESSION['contact_message_type'] = 'error';
                $this->redirect('/contact');
                exit;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['contact_message'] = 'Geçerli bir e-posta adresi girin.';
                $_SESSION['contact_message_type'] = 'error';
                $this->redirect('/contact');
                exit;
            }
            
            // Başarı mesajı
            $_SESSION['contact_message'] = 'Mesajınız başarıyla gönderildi. En kısa sürede size dönüş yapacağız.';
            $_SESSION['contact_message_type'] = 'success';
        }
        
        $this->redirect('/contact');
        exit;
    }
    
    private function renderModuleView($viewName, $data = []) {
        $viewPath = $this->moduleInfo['path'] . '/views/' . $viewName . '.php';
        
        if (!file_exists($viewPath)) {
            die("View not found: $viewPath");
        }
        
        // Data'yı extract et
        extract($data);
        
        // View'ı direkt include et
        include $viewPath;
    }
}
