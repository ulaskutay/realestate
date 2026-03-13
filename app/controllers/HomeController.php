<?php
/**
 * Home Controller
 * Frontend ana sayfa controller'ı
 */

class HomeController extends Controller
{
    /**
     * Aktif tema slug'ını her zaman string döndürür (get_option array/object dönebilir)
     */
    private function getActiveThemeSlug($default = 'realestate')
    {
        $raw = get_option('active_theme', $default);
        if (is_string($raw) && $raw !== '') {
            return $raw;
        }
        if (is_array($raw)) {
            $slug = $raw['slug'] ?? $raw['name'] ?? $raw['theme_slug'] ?? null;
            if (is_string($slug) && $slug !== '') {
                return $slug;
            }
            foreach ($raw as $v) {
                if (is_string($v) && $v !== '') {
                    return $v;
                }
            }
        }
        if (is_object($raw)) {
            $slug = $raw->slug ?? $raw->name ?? $raw->theme_slug ?? null;
            if (is_string($slug) && $slug !== '') {
                return $slug;
            }
        }
        return $default;
    }

    public function index()
    {
        // ThemeLoader'ı yükle ve ayarları yenile (customize değişikliklerinin yansıması için)
        require_once __DIR__ . '/../../core/ThemeLoader.php';
        $themeLoader = ThemeLoader::getInstance();
        $themeLoader->refreshSettings();
        
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
            'title' => '', // Boş bırakılır; layout SEO modülü / site adı ile meta title üretir
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

            // Emlak kategorisini bul
            $emlakCategory = $categoryModel->findBySlug('emlak');
            
            // Sayfalama
            $page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
            $perPage = 10;
            $offset = ($page - 1) * $perPage;

            // Sadece emlak kategorisindeki yazıları getir
            if ($emlakCategory) {
                // Önce toplam sayıyı al
                $allPosts = $postModel->getByCategory($emlakCategory['id']);
                $totalPosts = count($allPosts);
                
                // Sayfalama için slice yap
                $posts = array_slice($allPosts, $offset, $perPage);
            } else {
                // Emlak kategorisi yoksa boş liste
                $posts = [];
                $totalPosts = 0;
            }
            
            // Post içeriklerine çeviri filter'larını uygula
            if (function_exists('apply_filters')) {
                foreach ($posts as &$post) {
                    $post['title'] = apply_filters('post_title', $post['title']);
                    if (!empty($post['excerpt'])) {
                        $post['excerpt'] = apply_filters('post_excerpt', $post['excerpt']);
                    }
                }
                unset($post);
            }
            
            // $totalPosts yukarıda hesaplandı (emlak kategorisi için)
            $totalPages = ceil($totalPosts / $perPage);

            // Kategorileri getir (yazı sayısıyla birlikte)
            $categories = $categoryModel->getActive();
            foreach ($categories as &$cat) {
                $cat['post_count'] = $categoryModel->getPostCount($cat['id']);
            }

            // Son yazılar (sidebar için) - Sadece emlak kategorisinden
            if ($emlakCategory) {
                $allRecentPosts = $postModel->getByCategory($emlakCategory['id'], 5);
                $recentPosts = array_slice($allRecentPosts, 0, 5);
            } else {
                $recentPosts = [];
            }
            
            // Son yazılara da çeviri filter'larını uygula
            if (function_exists('apply_filters')) {
                foreach ($recentPosts as &$post) {
                    $post['title'] = apply_filters('post_title', $post['title']);
                    if (!empty($post['excerpt'])) {
                        $post['excerpt'] = apply_filters('post_excerpt', $post['excerpt']);
                    }
                }
                unset($post);
            }

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

        // Çeviri filter'larını uygula
        if (function_exists('apply_filters')) {
            $post['title'] = apply_filters('post_title', $post['title']);
            $post['content'] = apply_filters('post_content', $post['content']);
            if (!empty($post['excerpt'])) {
                $post['excerpt'] = apply_filters('post_excerpt', $post['excerpt']);
            }
        }

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
     * İletişim formu gönderimi
     */
    public function contactSubmit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /contact');
            exit;
        }

        if (!function_exists('csrf_verify') || !csrf_verify()) {
            $_SESSION['contact_message'] = 'Güvenlik doğrulaması başarısız. Lütfen sayfayı yenileyip tekrar deneyin.';
            $_SESSION['contact_message_type'] = 'error';
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
     * Çizgi Aks teması için sayfa yapıcı/modülden bağımsız statik sayfa render eder.
     * About/contact/iletisim/hakkimizda için her zaman cizgi-aks temasından çeker (aktif tema kontrolü yok).
     * Veriler sadece tema ayarları ve site seçeneklerinden (get_option) alınır.
     */
    private function renderCizgiAksStaticPage($slug)
    {
        // Tema path'ini proje kökünden sabit al (aktif tema/veritabanı bağımsız)
        $basePath = realpath(__DIR__ . '/../..') ?: (__DIR__ . '/../..');
        $themePath = $basePath . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'cizgi-aks';
        $themePath = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $themePath), DIRECTORY_SEPARATOR);

        if (!is_dir($themePath)) {
            $this->pageProxy($slug);
            return;
        }

        require_once __DIR__ . '/../../core/ThemeLoader.php';
        $themeLoader = ThemeLoader::getInstance();
        $themeLoader->loadTheme('cizgi-aks');
        $themeLoader->refreshSettings();

        // Path'i ThemeLoader'dan alınamadıysa yukarıdaki sabit path kullan
        $resolvedPath = $themeLoader->getThemePath();
        if ($resolvedPath && is_dir($resolvedPath)) {
            $themePath = $resolvedPath;
        }

        $page = [
            'id' => 0,
            'title' => __('İletişim'),
            'slug' => $slug,
            'excerpt' => '',
            'content' => '',
            'meta_title' => '',
            'meta_description' => '',
            'status' => 'published',
            'type' => 'page',
        ];

        $customFields = [];

        if ($slug === 'contact' || $slug === 'iletisim') {
            $page['title'] = __('İletişim');
            $page['meta_title'] = get_option('contact_meta_title', '') ?: __('İletişim');
            $page['meta_description'] = get_option('contact_meta_description', '') ?: '';
            $page['excerpt'] = get_option('contact_excerpt', '') ?: '';
            $customFields = [
                'contact_email' => get_option('contact_email', get_option('admin_email', '')),
                'contact_phone' => $themeLoader->getSetting('phone', '', 'header') ?: get_option('contact_phone', ''),
                'contact_address' => get_option('contact_address', ''),
                'contact_hours' => $themeLoader->getSetting('working_hours', '09:00 - 18:00', 'header') ?: get_option('contact_hours', '09:00 - 18:00'),
                'map_embed' => get_option('google_maps_embed', ''),
                'form_id' => get_option('contact_form_id', ''),
                'form_title' => get_option('contact_form_title', __('Bize Mesaj Gönderin')),
                'form_description' => get_option('contact_form_description', ''),
            ];
        } elseif ($slug === 'hakkimizda' || $slug === 'about') {
            $page['title'] = __('Hakkımızda');
            $page['meta_title'] = get_option('about_meta_title', '') ?: __('Hakkımızda');
            $page['meta_description'] = get_option('about_meta_description', '') ?: '';
            $page['excerpt'] = get_option('about_excerpt', '') ?: '';
            $page['content'] = get_option('about_content', '') ?: '';
            $customFields = [
                'hero_subtitle' => get_option('about_hero_subtitle', ''),
                'hero_image' => get_option('about_hero_image', ''),
                'about_sections' => get_option('about_sections', '') ?: '[]',
                'team_members' => get_option('about_team_members', '') ?: '[]',
                'stats' => get_option('about_stats', '') ?: '[]',
                'cta_title' => get_option('about_cta_title', __('Bizimle İletişime Geçin')),
                'cta_description' => get_option('about_cta_description', ''),
                'cta_button_text' => get_option('about_cta_button_text', __('İletişime Geç')),
                'cta_button_link' => get_option('about_cta_button_link', '/iletisim'),
            ];
        }

        $viewSlug = $slug === 'iletisim' ? 'contact' : (in_array($slug, ['about', 'hakkimizda'], true) ? 'about' : $slug);
        $templatePaths = [
            $themePath . '/views/pages/' . $viewSlug . '.php',
            $themePath . '/views/pages/' . $slug . '.php',
            $themePath . '/' . $viewSlug . '.php',
            $themePath . '/' . $slug . '.php',
        ];

        $foundTemplatePath = null;
        foreach ($templatePaths as $path) {
            if (file_exists($path)) {
                $foundTemplatePath = $path;
                break;
            }
        }

        if (!$foundTemplatePath) {
            $this->pageProxy($slug);
            return;
        }

        $title = $page['meta_title'] ?: $page['title'];
        $meta_description = $page['meta_description'] ?: ($page['excerpt'] ?? '');
        $meta_keywords = '';
        $current_page = $slug === 'iletisim' ? 'contact' : $slug;
        $sections = [];

        ob_start();
        $themeLoader = $themeLoader;
        include $foundTemplatePath;
        $content = ob_get_clean();
        if (!is_string($content)) {
            $content = '';
        }

        $layoutPath = $themePath . '/layouts/default.php';
        if (!file_exists($layoutPath)) {
            echo $content;
            return;
        }

        $layoutVars = [
            'content' => $content,
            'sections' => $sections,
            'title' => $title,
            'meta_description' => $meta_description,
            'meta_keywords' => $meta_keywords,
            'current_page' => $current_page,
            'themeLoader' => $themeLoader,
        ];
        extract($layoutVars, EXTR_SKIP);
        include $layoutPath;
    }

    /**
     * GET /contact - Firmaya özel iletişim sayfası (sayfa modülünden bağımsız).
     */
    public function contact()
    {
        $this->renderCizgiAksStaticPage('contact');
    }

    /**
     * GET /iletisim - Firmaya özel iletişim sayfası (Türkçe URL).
     */
    public function iletisim()
    {
        $this->renderCizgiAksStaticPage('iletisim');
    }

    /**
     * GET /hakkimizda - Firmaya özel hakkımızda sayfası (sayfa modülünden bağımsız).
     */
    public function hakkimizda()
    {
        $this->renderCizgiAksStaticPage('hakkimizda');
    }

    /**
     * GET /about - Firmaya özel hakkımızda sayfası (İngilizce URL).
     */
    public function about()
    {
        $this->renderCizgiAksStaticPage('about');
    }

    /**
     * Slug ile gelen isteği tema sayfa modülüne (theme_pages) yönlendirir.
     * Tema modülü yoksa 404.
     */
    public function pageProxy($slug)
    {
        $slug = trim($slug, '/');
        if (class_exists('ModuleLoader')) {
            $moduleLoader = ModuleLoader::getInstance();
            $controller = $moduleLoader->getModuleController('theme_pages');
            if ($controller && method_exists($controller, 'showPage')) {
                $controller->showPage($slug);
                return;
            }
        }
        http_response_code(404);
        require_once __DIR__ . '/../../core/ViewRenderer.php';
        $renderer = ViewRenderer::getInstance();
        $renderer->setLayout('default');
        $this->view('frontend/404', ['title' => 'Sayfa Bulunamadı', 'current_page' => '404']);
    }

    /**
     * Teklif alma sayfası (Fallback - özel route'lar için)
     */
    public function quoteRequest()
    {
        // Page model'ini yükle
        require_once __DIR__ . '/../models/Page.php';
        $pageModel = new Page();

        // Aktif temayı al (her zaman string)
        $activeTheme = $this->getActiveThemeSlug('codetic');
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

        // Mevcut temada teklif al sayfası yoksa 404 vermek yerine iletişim sayfasına yönlendir (farklı temadan kalan linkler için)
        $contactUrl = function_exists('site_url') ? site_url('iletisim') : '/iletisim';
        header('Location: ' . $contactUrl, true, 301);
        exit;
    }

    /**
     * Rezervasyon sayfası
     * 3 aşamalı rezervasyon formu: Uçak bileti -> Otel -> Araç kiralama
     */
    public function reservation()
    {
        // Debug: Metod çağrıldı
        error_log("HomeController::reservation() çağrıldı");
        
        // Aktif temayı al (her zaman string)
        $activeTheme = $this->getActiveThemeSlug('codetic');
        
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

    /**
     * Arama sayfası
     * Yazılar, sayfalar ve emlak ilanlarında arama yapar
     */
    public function search()
    {
        // Arama terimini al
        $query = trim($_GET['q'] ?? '');
        
        // ViewRenderer'ı al ve layout ayarla
        require_once __DIR__ . '/../../core/ViewRenderer.php';
        $renderer = ViewRenderer::getInstance();
        $renderer->setLayout('default');
        
        // Arama terimi yoksa boş sonuç göster
        if (empty($query)) {
            $data = [
                'title' => 'Arama',
                'query' => '',
                'posts' => [],
                'pages' => [],
                'listings' => [],
                'totalResults' => 0,
                'current_page' => 'search'
            ];
            $this->view('frontend/search', $data);
            return;
        }
        
        // Post model'ini yükle
        require_once __DIR__ . '/../models/Post.php';
        $postModel = new Post();
        
        // Yazılarda ara
        $posts = $postModel->search($query, 20);
        
        // Çeviri filter'larını uygula
        if (function_exists('apply_filters')) {
            foreach ($posts as &$post) {
                $post['title'] = apply_filters('post_title', $post['title']);
                if (!empty($post['excerpt'])) {
                    $post['excerpt'] = apply_filters('post_excerpt', $post['excerpt']);
                }
            }
            unset($post);
        }
        
        // Page model'ini yükle
        require_once __DIR__ . '/../models/Page.php';
        $pageModel = new Page();
        
        // Sayfalarda ara (Page model'i de posts tablosunu kullanıyor, type='page' ile)
        $keyword = '%' . $query . '%';
        $db = Database::getInstance();
        $pages = $db->fetchAll(
            "SELECT p.*, 
                    u.username as author_name
             FROM `posts` p
             LEFT JOIN `users` u ON p.author_id = u.id
             WHERE p.type = 'page'
             AND (p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ?)
             AND p.status = 'published'
             AND p.visibility = 'public'
             ORDER BY p.created_at DESC
             LIMIT 20",
            [$keyword, $keyword, $keyword]
        );
        
        // Çeviri filter'larını uygula
        if (function_exists('apply_filters')) {
            foreach ($pages as &$page) {
                $page['title'] = apply_filters('page_title', $page['title']);
                if (!empty($page['excerpt'])) {
                    $page['excerpt'] = apply_filters('page_excerpt', $page['excerpt']);
                }
            }
            unset($page);
        }
        
        // Emlak ilanlarında ara (eğer modül yüklüyse)
        $listings = [];
        try {
            if (class_exists('ModuleLoader')) {
                $moduleLoader = ModuleLoader::getInstance();
                $listingsModule = $moduleLoader->getModule('realestate-listings');
                
                if ($listingsModule && $listingsModule['status'] === 'active') {
                    // Listings model'ini yükle (özel modül modules/realestate-listings)
                    $listingsModelPath = dirname(dirname(__DIR__)) . '/modules/realestate-listings/Model.php';
                    if (file_exists($listingsModelPath)) {
                        if (!class_exists('RealEstateListingsModel')) {
                            require_once $listingsModelPath;
                        }
                        if (class_exists('RealEstateListingsModel')) {
                            $listingsModel = new RealEstateListingsModel();
                            
                            // İlanlarda arama yap (location, title, description alanlarında)
                            $listings = $db->fetchAll(
                                "SELECT l.*, 
                                        r.id as realtor_id, r.first_name as realtor_first_name, r.last_name as realtor_last_name,
                                        r.slug as realtor_slug
                                 FROM `realestate_listings` l
                                 LEFT JOIN `realestate_agents` r ON l.realtor_id = r.id
                                 WHERE l.status = 'published'
                                 AND (l.title LIKE ? OR l.description LIKE ? OR l.location LIKE ?)
                                 ORDER BY l.is_featured DESC, l.created_at DESC
                                 LIMIT 20",
                                [$keyword, $keyword, $keyword]
                            );
                        }
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Listings search error: " . $e->getMessage());
        }
        
        // Toplam sonuç sayısı
        $totalResults = count($posts) + count($pages) + count($listings);
        
        $data = [
            'title' => 'Arama Sonuçları: ' . htmlspecialchars($query),
            'query' => $query,
            'posts' => $posts,
            'pages' => $pages,
            'listings' => $listings,
            'totalResults' => $totalResults,
            'current_page' => 'search'
        ];
        
        $this->view('frontend/search', $data);
    }
}

