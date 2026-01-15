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
        
        // ThemeManager'ı yükle (customize ayarları için)
        if (!class_exists('ThemeManager')) {
            require_once __DIR__ . '/../../../../core/ThemeManager.php';
        }
        $themeManager = ThemeManager::getInstance();
        
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
        
        // Customize ayarlarından contact page sections'ı al
        $activeTheme = $themeManager->getActiveTheme();
        $themeId = $activeTheme['id'] ?? null;
        $contactSections = $themeManager->getPageSections('contact', $themeId) ?? [];
        
        // Sections'ı düzenle
        $contactPageSections = [];
        foreach ($contactSections as $section) {
            $sectionId = $section['section_id'] ?? '';
            if ($sectionId) {
                $sectionSettings = [];
                if (isset($section['settings'])) {
                    if (is_array($section['settings'])) {
                        $sectionSettings = $section['settings'];
                    } else {
                        $decoded = json_decode($section['settings'], true);
                        $sectionSettings = is_array($decoded) ? $decoded : [];
                    }
                }
                
                $contactPageSections[$sectionId] = array_merge(
                    $sectionSettings,
                    ['enabled' => ($section['is_active'] ?? 1) == 1]
                );
                $contactPageSections[$sectionId]['title'] = $section['title'] ?? '';
                $contactPageSections[$sectionId]['subtitle'] = $section['subtitle'] ?? '';
                $contactPageSections[$sectionId]['content'] = $section['content'] ?? '';
                
                // Items'ı ekle (varsa)
                if (isset($section['items']) && is_array($section['items'])) {
                    $contactPageSections[$sectionId]['items'] = $section['items'];
                }
            }
        }
        
        // Hero section ayarları (customize'den)
        $heroTitle = $contactPageSections['hero']['title'] ?? 'Hayalinizdeki Mülkü Bulalım';
        $heroSubtitle = $contactPageSections['hero']['subtitle'] ?? 'Uzman ekibimiz, mülk satın alma, satış veya kiralama işlemlerinizde size yardımcı olmak için burada. Hemen iletişime geçin!';
        $heroEnabled = $contactPageSections['hero']['enabled'] ?? true;
        
        // Form section ayarları
        $formTitle = $contactPageSections['form']['title'] ?? 'Mülk Talebinizi İletin';
        $formDescription = $contactPageSections['form']['description'] ?? 'Aradığınız mülk özelliklerini belirtin, size en uygun seçenekleri sunalım.';
        $formEnabled = $contactPageSections['form']['enabled'] ?? true;
        $formId = $contactPageSections['form']['form_id'] ?? null;
        
        // Form ID varsa, form slug'ını al
        $formSlug = 'iletisim'; // Varsayılan
        if ($formId) {
            try {
                require_once __DIR__ . '/../../../../core/Database.php';
                $db = Database::getInstance();
                $form = $db->fetch("SELECT slug FROM forms WHERE id = ?", [$formId]);
                if ($form && isset($form['slug'])) {
                    $formSlug = $form['slug'];
                }
            } catch (Exception $e) {
                error_log("Contact form load error: " . $e->getMessage());
            }
        }
        
        // Map section ayarları
        $mapEmbed = $contactPageSections['map']['embed'] ?? get_option('google_maps_embed', '');
        $mapEnabled = $contactPageSections['map']['enabled'] ?? true;
        
        // Why Choose Us section ayarları
        $whyChooseTitle = $contactPageSections['why-choose-us']['title'] ?? 'Neden Bizi Tercih Etmelisiniz?';
        $whyChooseItems = $contactPageSections['why-choose-us']['items'] ?? [
            ['text' => '500+ aktif mülk seçeneği'],
            ['text' => 'Deneyimli ve sertifikalı danışmanlar'],
            ['text' => 'Şeffaf fiyatlandırma ve güvenli işlem'],
            ['text' => '7/24 müşteri desteği ve hızlı yanıt']
        ];
        $whyChooseEnabled = $contactPageSections['why-choose-us']['enabled'] ?? true;
        
        // Services section ayarları
        $servicesTitle = $contactPageSections['services']['title'] ?? 'Hizmetlerimiz';
        $servicesDescription = $contactPageSections['services']['description'] ?? 'Size nasıl yardımcı olabiliriz?';
        $servicesItems = $contactPageSections['services']['items'] ?? [
            ['title' => 'Satılık Mülk', 'icon' => 'home', 'link' => ''],
            ['title' => 'Kiralık Mülk', 'icon' => 'apartment', 'link' => ''],
            ['title' => 'Mülk Değerleme', 'icon' => 'assessment', 'link' => ''],
            ['title' => 'Danışmanlık', 'icon' => 'people', 'link' => '']
        ];
        $servicesEnabled = $contactPageSections['services']['enabled'] ?? true;
        
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
            'messageType' => $messageType,
            // Customize ayarları
            'heroTitle' => $heroTitle,
            'heroSubtitle' => $heroSubtitle,
            'heroEnabled' => $heroEnabled,
            'formTitle' => $formTitle,
            'formDescription' => $formDescription,
            'formEnabled' => $formEnabled,
            'formSlug' => $formSlug,
            'mapEnabled' => $mapEnabled,
            'whyChooseTitle' => $whyChooseTitle,
            'whyChooseItems' => $whyChooseItems,
            'whyChooseEnabled' => $whyChooseEnabled,
            'servicesTitle' => $servicesTitle,
            'servicesDescription' => $servicesDescription,
            'servicesItems' => $servicesItems,
            'servicesEnabled' => $servicesEnabled
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
        // Modül bilgilerini kontrol et
        if (!isset($this->moduleInfo) || !isset($this->moduleInfo['path'])) {
            error_log("ContactController: moduleInfo not set or path missing");
            // Fallback: Modül dizinini manuel olarak belirle
            $modulePath = __DIR__;
        } else {
            $modulePath = $this->moduleInfo['path'];
        }
        
        $viewPath = $modulePath . '/views/' . $viewName . '.php';
        
        if (!file_exists($viewPath)) {
            error_log("ContactController: View not found: $viewPath");
            die("View not found: $viewPath");
        }
        
        // ThemeLoader'ı ekle (eğer yoksa)
        if (!isset($data['themeLoader']) && class_exists('ThemeLoader')) {
            $data['themeLoader'] = ThemeLoader::getInstance();
        }
        
        // Data'yı extract et
        extract($data);
        
        // View'ı direkt include et
        include $viewPath;
    }
    
    protected function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
}
