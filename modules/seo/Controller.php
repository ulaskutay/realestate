<?php
/**
 * SEO Modül Controller
 * 
 * Sitemap, robots.txt, meta taglar, yönlendirmeler ve Schema.org yönetimi
 */

require_once __DIR__ . '/models/SeoModel.php';

class SeoModuleController {
    
    private $moduleInfo;
    private $settings;
    private $db;
    private $model;
    
    /**
     * Constructor
     */
    public function __construct() {
        if (class_exists('Database')) {
            $this->db = Database::getInstance();
            $this->model = new SeoModel();
        }
    }
    
    /**
     * Modül bilgilerini ayarla
     */
    public function setModuleInfo($info) {
        $this->moduleInfo = $info;
    }
    
    /**
     * Modül yüklendiğinde
     */
    public function onLoad() {
        $this->loadSettings();
        
        // Hook'ları kaydet
        if (function_exists('add_action')) {
            add_action('init', [$this, 'checkRedirects'], 1); // Öncelikli çalışsın
            add_action('wp_head', [$this, 'outputMetaTags']);
            add_action('wp_head', [$this, 'outputSchemaMarkup']);
        }
        
        if (function_exists('add_filter')) {
            add_filter('document_title', [$this, 'filterDocumentTitle']);
        }
    }
    
    /**
     * Modül aktif edildiğinde
     */
    public function onActivate() {
        // Tabloları oluştur
        $this->model->createTables();
        
        // Varsayılan ayarları kaydet
        $this->saveDefaultSettings();
    }
    
    /**
     * Modül deaktif edildiğinde
     */
    public function onDeactivate() {
        // Geçici cache temizliği yapılabilir
    }
    
    /**
     * Modül silindiğinde
     */
    public function onUninstall() {
        // Tabloları sil (opsiyonel - yorum satırında bırakılabilir)
        // $this->model->dropTables();
    }
    
    /**
     * Ayarları yükle
     */
    private function loadSettings() {
        if (function_exists('get_module_settings')) {
            $this->settings = get_module_settings('seo');
        }
        
        if (empty($this->settings)) {
            $this->settings = $this->getDefaultSettings();
        }
    }
    
    /**
     * Varsayılan ayarlar
     */
    private function getDefaultSettings() {
        return [
            // Sitemap ayarları
            'sitemap_enabled' => true,
            'sitemap_pages' => true,
            'sitemap_posts' => true,
            'sitemap_categories' => true,
            'sitemap_tags' => true,
            'sitemap_custom_urls' => '',
            'sitemap_changefreq_pages' => 'weekly',
            'sitemap_changefreq_posts' => 'weekly',
            'sitemap_changefreq_categories' => 'weekly',
            'sitemap_changefreq_tags' => 'monthly',
            'sitemap_priority_home' => '1.0',
            'sitemap_priority_pages' => '0.8',
            'sitemap_priority_posts' => '0.8',
            'sitemap_priority_categories' => '0.6',
            'sitemap_priority_tags' => '0.4',
            
            // Robots.txt ayarları
            'robots_enabled' => true,
            'robots_content' => "User-agent: *\nAllow: /\n\nSitemap: {site_url}/sitemap.xml",
            
            // Meta tag ayarları
            'meta_title_home' => '{site_name}',
            'meta_title_post' => '{post_title} - {site_name}',
            'meta_title_category' => '{category_name} - {site_name}',
            'meta_title_separator' => ' - ',
            'meta_description_home' => '',
            'meta_description_default' => '',
            
            // Schema.org ayarları
            'schema_enabled' => true,
            'schema_organization_name' => '',
            'schema_organization_logo' => '',
            'schema_organization_url' => '',
            'schema_social_facebook' => '',
            'schema_social_twitter' => '',
            'schema_social_instagram' => '',
            'schema_social_linkedin' => '',
            'schema_social_youtube' => '',
            
            // Genel ayarlar
            'redirects_enabled' => true
        ];
    }
    
    /**
     * Varsayılan ayarları kaydet
     */
    private function saveDefaultSettings() {
        if (!class_exists('ModuleLoader')) {
            return;
        }
        
        $defaults = $this->getDefaultSettings();
        ModuleLoader::getInstance()->saveModuleSettings('seo', $defaults);
    }
    
    // ==================== FRONTEND METHODS ====================
    
    /**
     * Sitemap XML çıktısı
     */
    public function sitemapXml() {
        if (!($this->settings['sitemap_enabled'] ?? true)) {
            http_response_code(404);
            echo "Sitemap disabled";
            return;
        }
        
        header('Content-Type: application/xml; charset=utf-8');
        
        $siteUrl = $this->getSiteUrl();
        
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // Ana sayfa
        echo $this->sitemapUrl($siteUrl, date('c'), 'daily', $this->settings['sitemap_priority_home'] ?? '1.0');
        
        // Blog sayfası
        echo $this->sitemapUrl($siteUrl . '/blog', date('c'), 'daily', '0.9');
        
        // Sayfalar (type='page') - blog uzantısı olmadan
        if ($this->settings['sitemap_pages'] ?? true) {
            $pages = $this->model->getPagesForSitemap();
            foreach ($pages as $page) {
                $lastmod = $page['updated_at'] ?: $page['published_at'];
                echo $this->sitemapUrl(
                    $siteUrl . '/' . $page['slug'],
                    date('c', strtotime($lastmod)),
                    $this->settings['sitemap_changefreq_pages'] ?? 'weekly',
                    $this->settings['sitemap_priority_pages'] ?? '0.8'
                );
            }
        }
        
        // Blog yazıları (type='post') - blog uzantısı ile
        if ($this->settings['sitemap_posts'] ?? true) {
            $posts = $this->model->getPostsForSitemap();
            foreach ($posts as $post) {
                $lastmod = $post['updated_at'] ?: $post['published_at'];
                echo $this->sitemapUrl(
                    $siteUrl . '/blog/' . $post['slug'],
                    date('c', strtotime($lastmod)),
                    $this->settings['sitemap_changefreq_posts'] ?? 'weekly',
                    $this->settings['sitemap_priority_posts'] ?? '0.8'
                );
            }
        }
        
        // Kategoriler
        if ($this->settings['sitemap_categories'] ?? true) {
            $categories = $this->model->getCategoriesForSitemap();
            foreach ($categories as $category) {
                $lastmod = $category['updated_at'] ?: date('Y-m-d H:i:s');
                echo $this->sitemapUrl(
                    $siteUrl . '/kategori/' . $category['slug'],
                    date('c', strtotime($lastmod)),
                    $this->settings['sitemap_changefreq_categories'] ?? 'weekly',
                    $this->settings['sitemap_priority_categories'] ?? '0.6'
                );
            }
        }
        
        // Etiketler
        if ($this->settings['sitemap_tags'] ?? true) {
            $tags = $this->model->getTagsForSitemap();
            foreach ($tags as $tag) {
                echo $this->sitemapUrl(
                    $siteUrl . '/etiket/' . $tag['slug'],
                    date('c', strtotime($tag['updated_at'])),
                    $this->settings['sitemap_changefreq_tags'] ?? 'monthly',
                    $this->settings['sitemap_priority_tags'] ?? '0.4'
                );
            }
        }
        
        // Manuel eklenen URL'ler
        $customUrls = $this->settings['sitemap_custom_urls'] ?? '';
        if (!empty($customUrls)) {
            $lines = explode("\n", $customUrls);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line) && filter_var($line, FILTER_VALIDATE_URL)) {
                    echo $this->sitemapUrl($line, date('c'), 'monthly', '0.5');
                } elseif (!empty($line) && strpos($line, '/') === 0) {
                    echo $this->sitemapUrl($siteUrl . $line, date('c'), 'monthly', '0.5');
                }
            }
        }
        
        echo '</urlset>';
    }
    
    /**
     * Sitemap URL elementi oluştur
     */
    private function sitemapUrl($loc, $lastmod, $changefreq, $priority) {
        $xml = "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($loc) . "</loc>\n";
        $xml .= "    <lastmod>" . $lastmod . "</lastmod>\n";
        $xml .= "    <changefreq>" . $changefreq . "</changefreq>\n";
        $xml .= "    <priority>" . $priority . "</priority>\n";
        $xml .= "  </url>\n";
        return $xml;
    }
    
    /**
     * Robots.txt çıktısı
     */
    public function robotsTxt() {
        if (!($this->settings['robots_enabled'] ?? true)) {
            http_response_code(404);
            echo "Robots.txt disabled";
            return;
        }
        
        header('Content-Type: text/plain; charset=utf-8');
        
        $content = $this->settings['robots_content'] ?? '';
        $siteUrl = $this->getSiteUrl();
        
        // Değişkenleri değiştir
        $content = str_replace('{site_url}', $siteUrl, $content);
        
        echo $content;
    }
    
    /**
     * Yönlendirmeleri kontrol et (init hook'unda çağrılır)
     */
    public function checkRedirects() {
        if (!($this->settings['redirects_enabled'] ?? true)) {
            return;
        }
        
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $path = parse_url($requestUri, PHP_URL_PATH);
        
        // Yönlendirme ara
        $redirect = $this->model->findRedirectBySource($path);
        
        if ($redirect) {
            // Hit sayısını artır
            $this->model->incrementHit($redirect['id']);
            
            // Yönlendirme yap
            $statusCode = $redirect['type'] === '302' ? 302 : 301;
            header("Location: " . $redirect['target_url'], true, $statusCode);
            exit;
        }
    }
    
    /**
     * Meta tagları çıktıla
     */
    public function outputMetaTags() {
        // Meta description
        $description = $this->settings['meta_description_default'] ?? '';
        
        if (!empty($description)) {
            echo '<meta name="description" content="' . htmlspecialchars($description) . '">' . "\n";
        }
    }
    
    /**
     * Document title filtresi
     */
    public function filterDocumentTitle($title) {
        // Şablon değişkenlerini değiştir
        $siteName = $this->getSiteName();
        
        $title = str_replace('{site_name}', $siteName, $title);
        
        return $title;
    }
    
    /**
     * Schema.org markup çıktıla
     */
    public function outputSchemaMarkup() {
        if (!($this->settings['schema_enabled'] ?? true)) {
            return;
        }
        
        $siteUrl = $this->getSiteUrl();
        $siteName = $this->getSiteName();
        $orgName = $this->settings['schema_organization_name'] ?: $siteName;
        
        // Organization Schema
        $organization = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $orgName,
            'url' => $this->settings['schema_organization_url'] ?: $siteUrl
        ];
        
        // Logo
        if (!empty($this->settings['schema_organization_logo'])) {
            $organization['logo'] = $this->settings['schema_organization_logo'];
        }
        
        // Social profiles
        $sameAs = [];
        if (!empty($this->settings['schema_social_facebook'])) {
            $sameAs[] = $this->settings['schema_social_facebook'];
        }
        if (!empty($this->settings['schema_social_twitter'])) {
            $sameAs[] = $this->settings['schema_social_twitter'];
        }
        if (!empty($this->settings['schema_social_instagram'])) {
            $sameAs[] = $this->settings['schema_social_instagram'];
        }
        if (!empty($this->settings['schema_social_linkedin'])) {
            $sameAs[] = $this->settings['schema_social_linkedin'];
        }
        if (!empty($this->settings['schema_social_youtube'])) {
            $sameAs[] = $this->settings['schema_social_youtube'];
        }
        
        if (!empty($sameAs)) {
            $organization['sameAs'] = $sameAs;
        }
        
        echo '<script type="application/ld+json">' . "\n";
        echo json_encode($organization, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        echo "\n</script>\n";
        
        // WebSite Schema
        $website = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $siteName,
            'url' => $siteUrl,
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => $siteUrl . '/arama?q={search_term_string}',
                'query-input' => 'required name=search_term_string'
            ]
        ];
        
        echo '<script type="application/ld+json">' . "\n";
        echo json_encode($website, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        echo "\n</script>\n";
    }
    
    // ==================== ADMIN METHODS ====================
    
    /**
     * Admin ana sayfa (Dashboard)
     */
    public function admin_index() {
        // Model ve ayarların yüklendiğinden emin ol
        $this->ensureInitialized();
        
        $stats = $this->model->getStats();
        $redirectStats = $this->model->getRedirectStats();
        
        $this->adminView('index', [
            'title' => 'SEO Yönetimi',
            'stats' => $stats,
            'redirectStats' => $redirectStats,
            'settings' => $this->settings
        ]);
    }
    
    /**
     * Model ve ayarların yüklendiğinden emin ol
     */
    private function ensureInitialized() {
        if (!$this->db && class_exists('Database')) {
            $this->db = Database::getInstance();
        }
        if (!$this->model) {
            $this->model = new SeoModel();
        }
        if (empty($this->settings)) {
            $this->loadSettings();
        }
    }
    
    /**
     * Sitemap ayarları
     */
    public function admin_sitemap() {
        $this->ensureInitialized();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->saveSitemapSettings();
            $_SESSION['flash_message'] = 'Sitemap ayarları kaydedildi';
            $_SESSION['flash_type'] = 'success';
            $this->redirect('sitemap');
            return;
        }
        
        $this->adminView('sitemap', [
            'title' => 'Sitemap Ayarları',
            'settings' => $this->settings,
            'siteUrl' => $this->getSiteUrl()
        ]);
    }
    
    /**
     * Sitemap ayarlarını kaydet
     */
    private function saveSitemapSettings() {
        $this->settings['sitemap_enabled'] = isset($_POST['sitemap_enabled']);
        $this->settings['sitemap_pages'] = isset($_POST['sitemap_pages']);
        $this->settings['sitemap_posts'] = isset($_POST['sitemap_posts']);
        $this->settings['sitemap_categories'] = isset($_POST['sitemap_categories']);
        $this->settings['sitemap_tags'] = isset($_POST['sitemap_tags']);
        $this->settings['sitemap_custom_urls'] = $_POST['sitemap_custom_urls'] ?? '';
        $this->settings['sitemap_changefreq_pages'] = $_POST['sitemap_changefreq_pages'] ?? 'weekly';
        $this->settings['sitemap_changefreq_posts'] = $_POST['sitemap_changefreq_posts'] ?? 'weekly';
        $this->settings['sitemap_changefreq_categories'] = $_POST['sitemap_changefreq_categories'] ?? 'weekly';
        $this->settings['sitemap_changefreq_tags'] = $_POST['sitemap_changefreq_tags'] ?? 'monthly';
        $this->settings['sitemap_priority_home'] = $_POST['sitemap_priority_home'] ?? '1.0';
        $this->settings['sitemap_priority_pages'] = $_POST['sitemap_priority_pages'] ?? '0.8';
        $this->settings['sitemap_priority_posts'] = $_POST['sitemap_priority_posts'] ?? '0.8';
        $this->settings['sitemap_priority_categories'] = $_POST['sitemap_priority_categories'] ?? '0.6';
        $this->settings['sitemap_priority_tags'] = $_POST['sitemap_priority_tags'] ?? '0.4';
        
        ModuleLoader::getInstance()->saveModuleSettings('seo', $this->settings);
    }
    
    /**
     * Robots.txt ayarları
     */
    public function admin_robots() {
        $this->ensureInitialized();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->settings['robots_enabled'] = isset($_POST['robots_enabled']);
            $this->settings['robots_content'] = $_POST['robots_content'] ?? '';
            
            ModuleLoader::getInstance()->saveModuleSettings('seo', $this->settings);
            
            $_SESSION['flash_message'] = 'Robots.txt ayarları kaydedildi';
            $_SESSION['flash_type'] = 'success';
            $this->redirect('robots');
            return;
        }
        
        $this->adminView('robots', [
            'title' => 'Robots.txt Düzenleyici',
            'settings' => $this->settings,
            'siteUrl' => $this->getSiteUrl()
        ]);
    }
    
    /**
     * Meta tag ayarları
     */
    public function admin_meta() {
        $this->ensureInitialized();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->settings['meta_title_home'] = $_POST['meta_title_home'] ?? '{site_name}';
            $this->settings['meta_title_post'] = $_POST['meta_title_post'] ?? '{post_title} - {site_name}';
            $this->settings['meta_title_category'] = $_POST['meta_title_category'] ?? '{category_name} - {site_name}';
            $this->settings['meta_title_separator'] = $_POST['meta_title_separator'] ?? ' - ';
            $this->settings['meta_description_home'] = $_POST['meta_description_home'] ?? '';
            $this->settings['meta_description_default'] = $_POST['meta_description_default'] ?? '';
            
            ModuleLoader::getInstance()->saveModuleSettings('seo', $this->settings);
            
            $_SESSION['flash_message'] = 'Meta tag ayarları kaydedildi';
            $_SESSION['flash_type'] = 'success';
            $this->redirect('meta');
            return;
        }
        
        $this->adminView('meta', [
            'title' => 'Meta Tag Ayarları',
            'settings' => $this->settings
        ]);
    }
    
    /**
     * Yönlendirme listesi
     */
    public function admin_redirects() {
        $this->ensureInitialized();
        
        $redirects = $this->model->getRedirects();
        $stats = $this->model->getRedirectStats();
        
        $this->adminView('redirects', [
            'title' => 'URL Yönlendirmeleri',
            'redirects' => $redirects,
            'stats' => $stats
        ]);
    }
    
    /**
     * Yeni yönlendirme formu
     */
    public function admin_redirect_create() {
        $this->ensureInitialized();
        
        $this->adminView('redirect-form', [
            'title' => 'Yeni Yönlendirme',
            'redirect' => null,
            'action' => 'redirect_store'
        ]);
    }
    
    /**
     * Yönlendirme kaydet
     */
    public function admin_redirect_store() {
        $this->ensureInitialized();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('redirects');
            return;
        }
        
        $data = [
            'source_url' => $_POST['source_url'] ?? '',
            'target_url' => $_POST['target_url'] ?? '',
            'type' => $_POST['type'] ?? '301',
            'status' => $_POST['status'] ?? 'active',
            'note' => $_POST['note'] ?? ''
        ];
        
        if (empty($data['source_url']) || empty($data['target_url'])) {
            $_SESSION['flash_message'] = 'Kaynak ve hedef URL gerekli';
            $_SESSION['flash_type'] = 'error';
            $this->redirect('redirect_create');
            return;
        }
        
        $result = $this->model->addRedirect($data);
        
        if ($result) {
            $_SESSION['flash_message'] = 'Yönlendirme başarıyla eklendi';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Yönlendirme eklenirken hata oluştu';
            $_SESSION['flash_type'] = 'error';
        }
        
        $this->redirect('redirects');
    }
    
    /**
     * Yönlendirme düzenle
     */
    public function admin_redirect_edit($id) {
        $this->ensureInitialized();
        
        $redirect = $this->model->getRedirect($id);
        
        if (!$redirect) {
            $_SESSION['flash_message'] = 'Yönlendirme bulunamadı';
            $_SESSION['flash_type'] = 'error';
            $this->redirect('redirects');
            return;
        }
        
        $this->adminView('redirect-form', [
            'title' => 'Yönlendirme Düzenle',
            'redirect' => $redirect,
            'action' => 'redirect_update/' . $id
        ]);
    }
    
    /**
     * Yönlendirme güncelle
     */
    public function admin_redirect_update($id) {
        $this->ensureInitialized();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('redirect_edit/' . $id);
            return;
        }
        
        $data = [
            'source_url' => $_POST['source_url'] ?? '',
            'target_url' => $_POST['target_url'] ?? '',
            'type' => $_POST['type'] ?? '301',
            'status' => $_POST['status'] ?? 'active',
            'note' => $_POST['note'] ?? ''
        ];
        
        $result = $this->model->updateRedirect($id, $data);
        
        if ($result) {
            $_SESSION['flash_message'] = 'Yönlendirme güncellendi';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Güncelleme sırasında hata oluştu';
            $_SESSION['flash_type'] = 'error';
        }
        
        $this->redirect('redirects');
    }
    
    /**
     * Yönlendirme sil
     */
    public function admin_redirect_delete($id) {
        $this->ensureInitialized();
        
        $result = $this->model->deleteRedirect($id);
        
        if ($result) {
            $_SESSION['flash_message'] = 'Yönlendirme silindi';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Silme işlemi başarısız';
            $_SESSION['flash_type'] = 'error';
        }
        
        $this->redirect('redirects');
    }
    
    /**
     * Schema.org ayarları
     */
    public function admin_schema() {
        $this->ensureInitialized();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->settings['schema_enabled'] = isset($_POST['schema_enabled']);
            $this->settings['schema_organization_name'] = $_POST['schema_organization_name'] ?? '';
            $this->settings['schema_organization_logo'] = $_POST['schema_organization_logo'] ?? '';
            $this->settings['schema_organization_url'] = $_POST['schema_organization_url'] ?? '';
            $this->settings['schema_social_facebook'] = $_POST['schema_social_facebook'] ?? '';
            $this->settings['schema_social_twitter'] = $_POST['schema_social_twitter'] ?? '';
            $this->settings['schema_social_instagram'] = $_POST['schema_social_instagram'] ?? '';
            $this->settings['schema_social_linkedin'] = $_POST['schema_social_linkedin'] ?? '';
            $this->settings['schema_social_youtube'] = $_POST['schema_social_youtube'] ?? '';
            
            ModuleLoader::getInstance()->saveModuleSettings('seo', $this->settings);
            
            $_SESSION['flash_message'] = 'Schema.org ayarları kaydedildi';
            $_SESSION['flash_type'] = 'success';
            $this->redirect('schema');
            return;
        }
        
        $this->adminView('schema', [
            'title' => 'Schema.org Ayarları',
            'settings' => $this->settings,
            'siteUrl' => $this->getSiteUrl()
        ]);
    }
    
    // ==================== HELPER METHODS ====================
    
    /**
     * Site URL'sini al
     */
    private function getSiteUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host;
    }
    
    /**
     * Site adını al
     */
    private function getSiteName() {
        // Options tablosundan al
        try {
            $result = $this->db->fetch("SELECT value FROM options WHERE name = 'site_name'");
            if ($result) {
                return $result['value'];
            }
        } catch (Exception $e) {
            // Hata durumunda varsayılan
        }
        return 'CMS';
    }
    
    /**
     * Admin view render
     */
    private function adminView($view, $data = []) {
        $viewPath = __DIR__ . '/views/admin/' . $view . '.php';
        $basePath = dirname(dirname(__DIR__));
        
        if (!file_exists($viewPath)) {
            echo "View not found: " . $view;
            return;
        }
        
        extract($data);
        $currentPage = 'module/seo';
        
        include $basePath . '/app/views/admin/snippets/header.php';
        ?>
        <div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
            <div class="flex min-h-screen">
                <!-- SideNavBar -->
                <?php include $basePath . '/app/views/admin/snippets/sidebar.php'; ?>

                <!-- Content Area with Header -->
                <div class="flex-1 flex flex-col lg:ml-64">
                    <!-- Top Header -->
                    <?php include $basePath . '/app/views/admin/snippets/top-header.php'; ?>

                    <!-- Main Content -->
                    <main class="flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b] overflow-y-auto">
                        <div class="max-w-7xl mx-auto">
                            <?php include $viewPath; ?>
                        </div>
                    </main>
                </div>
            </div>
        </div>
        <?php
        include $basePath . '/app/views/admin/snippets/footer.php';
    }
    
    /**
     * Yönlendirme
     */
    private function redirect($action) {
        $url = admin_url('module/seo/' . $action);
        header("Location: " . $url);
        exit;
    }
}

