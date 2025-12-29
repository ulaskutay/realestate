<?php
/**
 * Home Controller
 * Frontend ana sayfa controller'ı
 */

class HomeController extends Controller
{

    public function index()
    {
        // Slider model'ini yükle
        require_once __DIR__ . '/../models/Slider.php';
        require_once __DIR__ . '/../models/SliderItem.php';

        $sliderModel = new Slider();
        $slider = $sliderModel->getActiveWithItems();

        // ViewRenderer'ı al ve layout ayarla
        require_once __DIR__ . '/../../core/ViewRenderer.php';
        $renderer = ViewRenderer::getInstance();
        $renderer->setLayout('default');

        $data = [
            'title' => 'Ana Sayfa',
            'message' => 'WordPress Tarzı CMS\'ye Hoş Geldiniz!',
            'slider' => $slider,
            'current_page' => 'home'
        ];

        $this->view('frontend/home', $data);
    }

    /**
     * Blog listesi sayfası
     */
    public function blog()
    {
        try {
            // Debug: Blog sayfası çağrıldı
            error_log("HomeController::blog() çağrıldı");
            
            // Post model'ini yükle
            require_once __DIR__ . '/../models/Post.php';
            require_once __DIR__ . '/../models/PostCategory.php';

            $postModel = new Post();
            $categoryModel = new PostCategory();

            // Sayfalama
            $page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
            $perPage = 10;
            $offset = ($page - 1) * $perPage;

            // Yazıları getir
            $posts = $postModel->getPublished($perPage, $offset);
            $totalPosts = $postModel->getCountByStatus('published');
            $totalPages = ceil($totalPosts / $perPage);

            // Kategorileri getir (yazı sayısıyla birlikte)
            $categories = $categoryModel->getActive();
            foreach ($categories as &$cat) {
                $cat['post_count'] = $categoryModel->getPostCount($cat['id']);
            }

            // Son yazılar (sidebar için)
            $recentPosts = $postModel->getRecent(5);

            // ViewRenderer'ı al ve layout ayarla
            require_once __DIR__ . '/../../core/ViewRenderer.php';
            $renderer = ViewRenderer::getInstance();
            $renderer->setLayout('default');

            $data = [
                'title' => 'Blog',
                'posts' => $posts,
                'categories' => $categories,
                'recentPosts' => $recentPosts,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'current_page' => 'blog'
            ];

            $this->view('frontend/blog/index', $data);
        } catch (Exception $e) {
            echo "<h1>Blog Sayfası Hatası</h1>";
            echo "<p>Hata: " . $e->getMessage() . "</p>";
            echo "<p>Dosya: " . $e->getFile() . " - Satır: " . $e->getLine() . "</p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
            exit;
        }
    }

    /**
     * Tekil yazı sayfası
     */
    public function blogPost($slug)
    {
        // Post model'ini yükle
        require_once __DIR__ . '/../models/Post.php';
        require_once __DIR__ . '/../models/PostCategory.php';

        $postModel = new Post();
        $categoryModel = new PostCategory();

        // Yazıyı getir
        $post = $postModel->findBySlug($slug);

        if (!$post || $post['status'] !== 'published') {
            http_response_code(404);
            require_once __DIR__ . '/../../core/ViewRenderer.php';
            $renderer = ViewRenderer::getInstance();
            $renderer->setLayout('default');
            $this->view('frontend/404', ['title' => 'Sayfa Bulunamadı', 'current_page' => '404']);
            return;
        }

        // Görüntülenme sayısını artır
        $postModel->incrementViews($post['id']);

        // İlgili yazılar
        $relatedPosts = [];
        if ($post['category_id']) {
            $relatedPosts = $postModel->getRelated($post['id'], $post['category_id'], 4);
        }

        // Kategorileri getir (sidebar için, yazı sayısıyla)
        $categories = $categoryModel->getActive();
        foreach ($categories as &$cat) {
            $cat['post_count'] = $categoryModel->getPostCount($cat['id']);
        }

        // Son yazılar (sidebar için)
        $recentPosts = $postModel->getRecent(5);

        // ViewRenderer'ı al ve layout ayarla
        require_once __DIR__ . '/../../core/ViewRenderer.php';
        $renderer = ViewRenderer::getInstance();
        $renderer->setLayout('default');

        $data = [
            'title' => $post['meta_title'] ?: $post['title'],
            'meta_description' => $post['meta_description'] ?: $post['excerpt'],
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'categories' => $categories,
            'recentPosts' => $recentPosts,
            'current_page' => 'blog'
        ];

        $this->view('frontend/blog/single', $data);
    }

    /**
     * Kategori sayfası
     */
    public function blogCategory($slug)
    {
        // Post model'ini yükle
        require_once __DIR__ . '/../models/Post.php';
        require_once __DIR__ . '/../models/PostCategory.php';

        $postModel = new Post();
        $categoryModel = new PostCategory();

        // Kategoriyi getir
        $category = $categoryModel->findBySlug($slug);

        if (!$category || $category['status'] !== 'active') {
            http_response_code(404);
            require_once __DIR__ . '/../../core/ViewRenderer.php';
            $renderer = ViewRenderer::getInstance();
            $renderer->setLayout('default');
            $this->view('frontend/404', ['title' => 'Sayfa Bulunamadı', 'current_page' => '404']);
            return;
        }

        // Kategorideki yazıları getir
        $posts = $postModel->getByCategory($category['id']);

        // Tüm kategoriler (sidebar için, yazı sayısıyla)
        $categories = $categoryModel->getActive();
        foreach ($categories as &$cat) {
            $cat['post_count'] = $categoryModel->getPostCount($cat['id']);
        }

        // Son yazılar (sidebar için)
        $recentPosts = $postModel->getRecent(5);

        // ViewRenderer'ı al ve layout ayarla
        require_once __DIR__ . '/../../core/ViewRenderer.php';
        $renderer = ViewRenderer::getInstance();
        $renderer->setLayout('default');

        $data = [
            'title' => $category['name'] . ' - Blog',
            'category' => $category,
            'posts' => $posts,
            'categories' => $categories,
            'recentPosts' => $recentPosts,
            'current_page' => 'blog'
        ];

        $this->view('frontend/blog/category', $data);
    }

    /**
     * İletişim sayfası
     */
    public function contact()
    {
        try {
            // Aktif temayı al
            $activeTheme = get_option('active_theme', 'starter');
            $templatePath = __DIR__ . '/../../themes/' . $activeTheme . '/contact.php';
            
            // Tema contact template'i varsa onu kullan
            if (file_exists($templatePath)) {
                // ThemeLoader'ı yükle
                require_once __DIR__ . '/../../core/ThemeLoader.php';
                $themeLoader = ThemeLoader::getInstance();
                
                // Sayfa verileri
                $page = [
                    'title' => 'İletişim',
                    'excerpt' => 'Bizimle iletişime geçin'
                ];
                $customFields = [];
                $title = 'İletişim';
                $meta_description = 'İletişim sayfası';
                $current_page = 'contact';
                
                // Template'i include et
                include $templatePath;
                exit;
            }
            
            // Fallback: Eski view sistemini kullan
            require_once __DIR__ . '/../../core/ViewRenderer.php';
            $renderer = ViewRenderer::getInstance();
            $renderer->setLayout('default');

            // Form mesajlarını al
            $message = $_SESSION['contact_message'] ?? null;
            $messageType = $_SESSION['contact_message_type'] ?? null;
            unset($_SESSION['contact_message'], $_SESSION['contact_message_type']);

            $data = [
                'title' => 'İletişim',
                'current_page' => 'contact',
                'message' => $message,
                'messageType' => $messageType
            ];

            $this->view('frontend/contact', $data);
        } catch (Exception $e) {
            echo "<h1>İletişim Sayfası Hatası</h1>";
            echo "<p>Hata: " . $e->getMessage() . "</p>";
            echo "<p>Dosya: " . $e->getFile() . " - Satır: " . $e->getLine() . "</p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
            exit;
        }
    }

    /**
     * İletişim formu gönderimi
     */
    public function contactSubmit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /contact');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // Basit validasyon
        if (empty($name) || empty($email) || empty($message)) {
            $_SESSION['contact_message'] = 'Lütfen tüm zorunlu alanları doldurun.';
            $_SESSION['contact_message_type'] = 'error';
            header('Location: /contact');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['contact_message'] = 'Geçerli bir e-posta adresi girin.';
            $_SESSION['contact_message_type'] = 'error';
            header('Location: /contact');
            exit;
        }

        // Form verilerini kaydet (eğer form tablosu varsa)
        try {
            $db = Database::getInstance();

            // form_submissions tablosu var mı kontrol et
            $tableExists = $db->fetch("SHOW TABLES LIKE 'form_submissions'");

            if ($tableExists) {
                $db->query(
                    "INSERT INTO form_submissions (form_id, data, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, NOW())",
                    [
                        0, // Genel iletişim formu
                        json_encode([
                            'name' => $name,
                            'email' => $email,
                            'phone' => $phone,
                            'subject' => $subject,
                            'message' => $message
                        ]),
                        $_SERVER['REMOTE_ADDR'] ?? '',
                        $_SERVER['HTTP_USER_AGENT'] ?? ''
                    ]
                );
            }

            // E-posta gönder (SMTP ayarlıysa)
            $adminEmail = get_option('admin_email', '');
            if ($adminEmail && function_exists('wp_mail')) {
                wp_mail(
                    $adminEmail,
                    'Yeni İletişim Formu: ' . $subject,
                    "İsim: $name\nE-posta: $email\nTelefon: $phone\n\nMesaj:\n$message"
                );
            }

            $_SESSION['contact_message'] = 'Mesajınız başarıyla gönderildi. En kısa sürede size dönüş yapacağız.';
            $_SESSION['contact_message_type'] = 'success';

        } catch (Exception $e) {
            error_log('Contact form error: ' . $e->getMessage());
            $_SESSION['contact_message'] = 'Bir hata oluştu. Lütfen daha sonra tekrar deneyin.';
            $_SESSION['contact_message_type'] = 'error';
        }

        header('Location: /contact');
        exit;
    }

    /**
     * Statik sayfa görüntüleme
     */
    public function page($slug)
    {
        // Page model'ini yükle
        require_once __DIR__ . '/../models/Page.php';

        $pageModel = new Page();

        // Sayfayı getir
        $page = $pageModel->findBySlug($slug);

        // Sayfa yoksa veya yayınlanmamışsa 404
        if (!$page || $page['status'] !== 'published' || (isset($page['type']) && $page['type'] !== 'page')) {
            http_response_code(404);
            require_once __DIR__ . '/../../core/ViewRenderer.php';
            $renderer = ViewRenderer::getInstance();
            $renderer->setLayout('default');
            $this->view('frontend/404', ['title' => 'Sayfa Bulunamadı', 'current_page' => '404']);
            return;
        }

        // Görüntülenme sayısını artır
        $pageModel->incrementViews($page['id']);

        // Özel alanları getir
        $customFields = $pageModel->getCustomFields($page['id']);

        // Template seçimi
        $pageTemplate = $customFields['page_template'] ?? 'default';

        // Debug: Template değerini kontrol et
        // error_log("Page Template: " . $pageTemplate);
        // error_log("Custom Fields: " . print_r($customFields, true));

        // Eğer özel template seçilmişse (service-detail, about, contact vb.), tema template'ini kullan
        if (in_array($pageTemplate, ['service-detail', 'about', 'contact'])) {
            // Aktif temayı al
            $activeTheme = get_option('active_theme', 'starter');
            $templatePath = __DIR__ . '/../../themes/' . $activeTheme . '/' . $pageTemplate . '.php';

            if (file_exists($templatePath)) {
                // ThemeLoader'ı yükle
                require_once __DIR__ . '/../../core/ThemeLoader.php';
                $themeLoader = ThemeLoader::getInstance();

                // Template'e değişkenleri geçir
                $title = $page['meta_title'] ?: $page['title'];
                $meta_description = $page['meta_description'] ?: $page['excerpt'];
                $meta_keywords = $page['meta_keywords'];
                $current_page = 'page';

                // Template'i include et
                include $templatePath;
                exit; // return yerine exit kullan
            }
        }

        // Varsayılan template (frontend/pages/single.php)
        require_once __DIR__ . '/../../core/ViewRenderer.php';
        $renderer = ViewRenderer::getInstance();
        $renderer->setLayout('default');

        $data = [
            'title' => $page['meta_title'] ?: $page['title'],
            'meta_description' => $page['meta_description'] ?: $page['excerpt'],
            'meta_keywords' => $page['meta_keywords'],
            'page' => $page,
            'customFields' => $customFields,
            'current_page' => 'page'
        ];

        $this->view('frontend/pages/single', $data);
    }
}

