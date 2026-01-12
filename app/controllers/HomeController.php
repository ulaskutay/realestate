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

        // Debug modu kontrolü
        $debugMode = (defined('DEBUG_MODE') && DEBUG_MODE) || (ini_get('display_errors') == 1);

        if ($debugMode) {
            error_log("Page lookup - Slug: $slug");
            error_log("Page found: " . ($page ? 'YES' : 'NO'));
            if ($page) {
                error_log("Page status: " . ($page['status'] ?? 'NULL'));
                error_log("Page type: " . ($page['type'] ?? 'NULL'));
            }
        }

        // Sayfa yoksa veya yayınlanmamışsa 404
        if (!$page) {
            if ($debugMode) {
                error_log("404 - Page not found for slug: $slug");
            }
            http_response_code(404);
            require_once __DIR__ . '/../../core/ViewRenderer.php';
            $renderer = ViewRenderer::getInstance();
            $renderer->setLayout('default');
            $this->view('frontend/404', ['title' => 'Sayfa Bulunamadı', 'current_page' => '404']);
            return;
        }

        if ($page['status'] !== 'published') {
            if ($debugMode) {
                error_log("404 - Page status is not published. Status: " . ($page['status'] ?? 'NULL'));
            }
            http_response_code(404);
            require_once __DIR__ . '/../../core/ViewRenderer.php';
            $renderer = ViewRenderer::getInstance();
            $renderer->setLayout('default');
            $this->view('frontend/404', ['title' => 'Sayfa Bulunamadı', 'current_page' => '404']);
            return;
        }

        if (isset($page['type']) && $page['type'] !== 'page') {
            if ($debugMode) {
                error_log("404 - Page type is not 'page'. Type: " . ($page['type'] ?? 'NULL'));
            }
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

        if ($debugMode) {
            error_log("Page Template: $pageTemplate");
            error_log("Page ID: {$page['id']}, Slug: {$page['slug']}");
        }

        // Eğer özel template seçilmişse (service-detail, about, contact, teklif-al vb.), tema template'ini kullan
        if (in_array($pageTemplate, ['service-detail', 'about', 'contact', 'teklif-al', 'quote-request'])) {
            // Aktif temayı al - MUTLAKA codetic olmalı
            $activeTheme = get_option('active_theme', 'codetic');
            
            // DOCUMENT_ROOT kontrolü
            $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(dirname(__DIR__));
            
            // Template path - codetic temasında olmalı
            $templatePath = $docRoot . '/themes/codetic/' . $pageTemplate . '.php';
            
            // Alternatif path'ler (eğer DOCUMENT_ROOT yanlışsa)
            $altPaths = [
                __DIR__ . '/../../themes/codetic/' . $pageTemplate . '.php',
                dirname(dirname(__DIR__)) . '/themes/codetic/' . $pageTemplate . '.php',
            ];
            
            // Önce ana path'i dene
            if (!file_exists($templatePath)) {
                // Alternatif path'leri dene
                foreach ($altPaths as $altPath) {
                    if (file_exists($altPath)) {
                        $templatePath = $altPath;
                        break;
                    }
                }
            }
            
            if (file_exists($templatePath)) {
                // ThemeLoader'ı yükle
                require_once $docRoot . '/core/ThemeLoader.php';
                $themeLoader = ThemeLoader::getInstance();

                // Template'e değişkenleri geçir
                $title = $page['meta_title'] ?: $page['title'];
                $meta_description = $page['meta_description'] ?: $page['excerpt'];
                $meta_keywords = $page['meta_keywords'];
                $current_page = 'page';
                $customFields = $customFields ?? [];
                
                // Template'i include et
                include $templatePath;
                exit;
            } else {
                // Template bulunamadı - detaylı hata mesajı göster
                $searchedPaths = [
                    $docRoot . '/themes/codetic/' . $pageTemplate . '.php',
                    __DIR__ . '/../../themes/codetic/' . $pageTemplate . '.php',
                    dirname(dirname(__DIR__)) . '/themes/codetic/' . $pageTemplate . '.php',
                ];
                $pathsList = implode('<br>', array_map(function($p) { return '<code>' . htmlspecialchars($p) . '</code>'; }, $searchedPaths));
                
                die("
                    <h1>Template Bulunamadı</h1>
                    <p>Sayfa template'i bulunamadı: <strong>$pageTemplate</strong></p>
                    <p><strong>Aktif Tema:</strong> codetic (zorunlu)</p>
                    <p><strong>Sayfa ID:</strong> {$page['id']}, <strong>Slug:</strong> {$page['slug']}</p>
                    <p><strong>Custom Fields:</strong> " . htmlspecialchars(print_r($customFields, true)) . "</p>
                    <p><strong>Aranan yollar:</strong><br>$pathsList</p>
                    <p>Lütfen admin panelden sayfa template'ini kontrol edin ve <strong>codetic</strong> temasında olduğundan emin olun.</p>
                ");
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

    /**
     * Teklif alma sayfası (Fallback - özel route'lar için)
     * Panelden oluşturulan sayfa page() metodu ile handle edilir
     */
    public function quoteRequest()
    {
        // Page model'ini yükle
        require_once __DIR__ . '/../models/Page.php';
        $pageModel = new Page();

        // Aktif temayı al
        $activeTheme = get_option('active_theme', 'codetic');
        $templatePath = __DIR__ . '/../../themes/' . $activeTheme . '/teklif-al.php';

        // Slug'dan sayfayı bul (teklif-al veya quote-request)
        $page = $pageModel->findBySlug('teklif-al');
        if (!$page) {
            $page = $pageModel->findBySlug('quote-request');
        }
        
        // Sayfa bulunamadıysa varsayılan değerlerle devam et
        if (!$page) {
            $page = [
                'id' => 0,
                'title' => 'Teklif Al',
                'slug' => 'teklif-al',
                'excerpt' => 'Projeniz için detaylı teklif alın',
                'meta_title' => 'Teklif Al',
                'meta_description' => 'Projeniz için detaylı teklif alın',
                'status' => 'published',
                'type' => 'page'
            ];
        }

        // Custom fields'ı al
        $customFields = [];
        if ($page['id'] > 0) {
            $customFields = $pageModel->getCustomFields($page['id']);
        }

        // Template varsa onu kullan
        if (file_exists($templatePath)) {
            // ThemeLoader'ı yükle
            require_once __DIR__ . '/../../core/ThemeLoader.php';
            $themeLoader = ThemeLoader::getInstance();
            
            // Template'e değişkenleri geçir
            $title = $page['meta_title'] ?? $page['title'] ?? 'Teklif Al';
            $meta_description = $page['meta_description'] ?? $page['excerpt'] ?? 'Projeniz için detaylı teklif alın';
            $meta_keywords = $page['meta_keywords'] ?? '';
            $current_page = 'quote-request';

            // Template'i include et
            include $templatePath;
            exit;
        }

        // Template yoksa page() metoduna yönlendir (slug bazlı)
        // Bu sayede panelden oluşturulan sayfa doğru şekilde handle edilir
        $this->page('teklif-al');
    }

    /**
     * Rezervasyon sayfası
     * 3 aşamalı rezervasyon formu: Uçak bileti -> Otel -> Araç kiralama
     */
    public function reservation()
    {
        // Debug: Metod çağrıldı
        error_log("HomeController::reservation() çağrıldı");
        
        // Aktif temayı al
        $activeTheme = get_option('active_theme', 'codetic');
        
        // DOCUMENT_ROOT kontrolü
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(dirname(__DIR__));
        
        // Template path - codetic temasında olmalı
        $templatePath = $docRoot . '/themes/codetic/rezervasyon.php';
        
        // Alternatif path'ler (eğer DOCUMENT_ROOT yanlışsa)
        $altPaths = [
            __DIR__ . '/../../themes/codetic/rezervasyon.php',
            dirname(dirname(__DIR__)) . '/themes/codetic/rezervasyon.php',
        ];
        
        // Önce ana path'i dene
        if (!file_exists($templatePath)) {
            error_log("Template bulunamadı: $templatePath");
            // Alternatif path'leri dene
            foreach ($altPaths as $altPath) {
                if (file_exists($altPath)) {
                    error_log("Alternatif template bulundu: $altPath");
                    $templatePath = $altPath;
                    break;
                }
            }
        } else {
            error_log("Template bulundu: $templatePath");
        }

        // Template varsa onu kullan
        if (file_exists($templatePath)) {
            // ThemeLoader'ı yükle
            require_once __DIR__ . '/../../core/ThemeLoader.php';
            $themeLoader = ThemeLoader::getInstance();
            
            // Template'e değişkenleri geçir
            $title = 'Rezervasyon';
            $meta_description = 'Uçak bileti, otel ve araç kiralama rezervasyonu yapın';
            $meta_keywords = 'rezervasyon, uçak bileti, otel, araç kiralama';
            $current_page = 'reservation';

            // Template'i include et
            include $templatePath;
            exit;
        }

        // Template yoksa 404 göster
        error_log("Template dosyası bulunamadı. Aranan path'ler: " . implode(', ', array_merge([$templatePath], $altPaths)));
        http_response_code(404);
        require_once __DIR__ . '/../../core/ViewRenderer.php';
        $renderer = ViewRenderer::getInstance();
        $renderer->setLayout('default');
        $this->view('frontend/404', ['title' => 'Sayfa Bulunamadı', 'current_page' => '404']);
    }
}

