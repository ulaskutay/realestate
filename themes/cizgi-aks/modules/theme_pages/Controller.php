<?php
/**
 * Çizgi Aks - Tema sayfa modülü (sadece frontend)
 * app/models/Page kullanır; layout ve view tamamen bu temaya ait.
 */

class CizgiAksThemePagesController extends Controller
{
    /** @var string Tema kök path (themes/cizgi-aks) */
    private $themePath;

    public function __construct() {
        $this->themePath = dirname(dirname(__DIR__));
    }

    /**
     * Slug ile sayfayı gösterir (layout + tema view)
     */
    public function showPage($slug) {
        $slug = trim($slug, '/');
        $root = dirname(dirname(dirname(dirname(__DIR__))));

        require_once $root . '/app/models/Page.php';
        $pageModel = new Page();

        $page = $pageModel->findBySlug($slug);
        if (!$page || $page['status'] !== 'published') {
            if ($slug === 'contact' || $slug === 'iletisim') {
                $page = $pageModel->findBySlug($slug === 'contact' ? 'iletisim' : 'contact');
                if ((!$page || $page['status'] !== 'published') && method_exists($pageModel, 'findByTemplate')) {
                    $page = $pageModel->findByTemplate('contact');
                }
                if ($page && $page['status'] === 'published') {
                    $slug = $page['slug'];
                }
            }
        }
        if (!$page || $page['status'] !== 'published' || (isset($page['type']) && $page['type'] !== 'page')) {
            http_response_code(404);
            require_once $root . '/core/ViewRenderer.php';
            $renderer = ViewRenderer::getInstance();
            $renderer->setLayout('default');
            $this->view('frontend/404', ['title' => 'Sayfa Bulunamadı', 'current_page' => '404']);
            return;
        }

        $pageModel->incrementViews($page['id']);

        if (function_exists('apply_filters')) {
            $page['title'] = apply_filters('page_title', $page['title']);
            $page['content'] = apply_filters('page_content', $page['content']);
            if (!empty($page['excerpt'])) {
                $page['excerpt'] = apply_filters('page_excerpt', $page['excerpt']);
            }
        }

        $customFields = $pageModel->getCustomFields($page['id']);
        $pageTemplateRaw = $customFields['page_template'] ?? 'default';
        $pageTemplate = is_array($pageTemplateRaw) ? (reset($pageTemplateRaw) ?: 'default') : (string) $pageTemplateRaw;
        if ($pageTemplate === '') {
            $pageTemplate = 'default';
        }

        require_once $root . '/core/ThemeLoader.php';
        $themeLoader = ThemeLoader::getInstance();
        $themeLoader->loadTheme('cizgi-aks');
        $themeLoader->refreshSettings();

        $templatePaths = [
            $this->themePath . '/views/pages/' . $pageTemplate . '.php',
            $this->themePath . '/views/pages/' . $slug . '.php',
            $this->themePath . '/' . $pageTemplate . '.php',
            $this->themePath . '/' . $slug . '.php',
        ];

        $foundTemplatePath = null;
        foreach ($templatePaths as $path) {
            if (file_exists($path)) {
                $foundTemplatePath = $path;
                break;
            }
        }

        if (!$foundTemplatePath) {
            http_response_code(404);
            require_once $root . '/core/ViewRenderer.php';
            $renderer = ViewRenderer::getInstance();
            $renderer->setLayout('default');
            $this->view('frontend/404', ['title' => 'Sayfa Bulunamadı', 'current_page' => '404']);
            return;
        }

        $title = $page['meta_title'] ?: $page['title'];
        $meta_description = $page['meta_description'] ?: $page['excerpt'];
        $meta_keywords = $page['meta_keywords'] ?? '';
        $current_page = $slug;
        $sections = [];

        $templateSrc = @file_get_contents($foundTemplatePath, false, null, 0, 4096);
        $selfRendering = $templateSrc && (
            strpos($templateSrc, 'ob_end_clean') !== false
            || preg_match('/require\s*\(?\s*[\'"].*layout|include\s*\$layoutPath/', $templateSrc)
        );

        if ($selfRendering) {
            include $foundTemplatePath;
            return;
        }

        $content = '';
        try {
            ob_start();
            include $foundTemplatePath;
            $content = ob_get_clean();
            $content = is_string($content) ? $content : '';
        } catch (Throwable $e) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            $content = '<div class="cizgiaks-container py-12"><p class="text-red-600">Şablon hatası: ' . htmlspecialchars($e->getMessage()) . '</p></div>';
            error_log('Theme pages template error (' . $slug . '): ' . $e->getMessage());
        }

        if (trim($content) === '') {
            $content = '<div class="cizgiaks-container py-12"><h1 class="text-2xl font-bold mb-4">' . esc_html($page['title'] ?? 'Sayfa') . '</h1><div class="prose">' . (isset($page['content']) && $page['content'] !== '' ? $page['content'] : '<p>İçerik yok.</p>') . '</div></div>';
        }

        $layoutPath = $this->themePath . '/layouts/default.php';
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
}
