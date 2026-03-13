<?php
// Aynı istekte layout iki kez include edilirse ve ikincide içerik boşsa atla
static $_cizgiaks_layout_rendered = false;
$currentContent = (string) ($content ?? '');
if ($_cizgiaks_layout_rendered && $currentContent === '') {
    return;
}
$_cizgiaks_layout_rendered = true;

try {
// ThemeLoader'ın doğru temayı (cizgi-aks) yüklediğinden emin ol
if (!class_exists('ThemeLoader')) {
    require_once __DIR__ . '/../../../core/ThemeLoader.php';
}
if (!isset($themeLoader) || !$themeLoader) {
    $themeLoader = ThemeLoader::getInstance();
}
if ($themeLoader && strpos($themeLoader->getThemePath() ?? '', 'cizgi-aks') === false) {
    $themeLoader->loadTheme('cizgi-aks');
    $themeLoader->refreshSettings();
}
} catch (Throwable $e) {
    if (function_exists('error_log')) {
        error_log('Cizgi Aks layout init: ' . $e->getMessage());
    }
    $themeLoader = null;
}
?>
<!DOCTYPE html>
<html lang="<?php echo function_exists('get_current_language') ? get_current_language() : 'tr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    if (!class_exists('ViewRenderer')) {
        require_once __DIR__ . '/../../../core/ViewRenderer.php';
    }
    $reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $reqPath = trim($reqPath, '/');
    $basePath = trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
    if ($basePath !== '' && $basePath !== '/' && strpos($reqPath, $basePath) === 0) {
        $reqPath = trim(substr($reqPath, strlen($basePath)), '/');
    }
    $isHome = ($reqPath === '' || $reqPath === false);
    $siteName = function_exists('get_option') ? (get_option('site_name', '') ?: get_option('seo_title', '') ?: 'CMS') : 'CMS';
    $seoTitle = trim((string) get_option('seo_title', ''));
    $seoDesc  = trim((string) get_option('seo_description', ''));
    $pageTitle = isset($title) && trim((string) $title) !== '' ? trim((string) $title) : '';
    $metaDesc  = isset($meta_description) && trim((string) $meta_description) !== '' ? trim((string) $meta_description) : '';
    if ($pageTitle === '') {
        if ($isHome) {
            $homeTemplate = function_exists('get_module_settings') ? (get_module_settings('seo')['meta_title_home'] ?? '{site_name}') : '{site_name}';
            $pageTitle = str_replace('{site_name}', $siteName, $homeTemplate);
            if ($pageTitle === '') $pageTitle = $seoTitle ?: $siteName;
        } else {
            $template = function_exists('get_module_settings') ? (get_module_settings('seo')['meta_title_default'] ?? '{page_title} - {site_name}') : '{page_title} - {site_name}';
            $pageTitleSeg = $reqPath ? (function_exists('get_seo_page_title_from_path') ? get_seo_page_title_from_path($reqPath) : ucfirst(str_replace(['-', '_'], ' ', explode('/', $reqPath)[0]))) : __('Sayfa');
            $pageTitle = str_replace(['{site_name}', '{page_title}'], [$siteName, $pageTitleSeg], $template);
        }
    }
    if ($pageTitle === '') $pageTitle = $siteName ?: 'Çizgi Aks Gayrimenkul';
    if ($metaDesc === '' && function_exists('get_module_settings')) {
        $sm = get_module_settings('seo');
        $metaDesc = $isHome ? ($sm['meta_description_home'] ?? $sm['meta_description_default'] ?? $seoDesc) : ($sm['meta_description_other'] ?? $sm['meta_description_default'] ?? $seoDesc);
    }
    if ($metaDesc === '') $metaDesc = $seoDesc !== '' ? $seoDesc : 'Gayrimenkul ilanları';
    $seoOverride = function_exists('get_seo_page_meta_override') ? get_seo_page_meta_override() : null;
    if ($seoOverride) {
        if (!empty($seoOverride['meta_title'])) $pageTitle = $seoOverride['meta_title'];
        if (isset($seoOverride['meta_description']) && $seoOverride['meta_description'] !== '') $metaDesc = $seoOverride['meta_description'];
    }
    ?>
    <title><?php echo esc_html($pageTitle); ?></title>
    <meta name="description" content="<?php echo esc_attr($metaDesc); ?>">
    <?php
    $favicon = $themeLoader ? $themeLoader->getFavicon() : null;
    if (empty($favicon) && function_exists('get_site_favicon')) {
        $favicon = get_site_favicon();
    }
    if (!empty($favicon)): ?>
    <link rel="icon" href="<?php echo esc_url($favicon); ?>">
    <link rel="shortcut icon" href="<?php echo esc_url($favicon); ?>">
    <link rel="apple-touch-icon" href="<?php echo esc_url($favicon); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <?php 
    // Varsayılan CSS değişkenleri (her zaman ekle, tema ayarları boş olsa bile)
    $defaultCssVars = '
        :root {
            --color-primary: #bc1a1a;
            --color-secondary: #1f2937;
            --color-accent: #9a1615;
            --color-background: #ffffff;
            --color-text: #1f2937;
            --color-text-muted: #6b7280;
            --font-heading: system-ui, sans-serif;
            --font-body: system-ui, sans-serif;
        }
    ';
    $themeCssVars = '';
    if ($themeLoader) {
        $themeLoader->refreshSettings();
        $themeCssVars = $themeLoader->getCssVariablesTag();
    }
    // Tema CSS boşsa varsayılanları kullan
    if (empty(trim(strip_tags($themeCssVars)))) {
        echo "<style id=\"theme-variables\">{$defaultCssVars}</style>";
    } else {
        echo $themeCssVars;
    }
    ?>
    <style>
        html { min-height: 100%; overflow-x: hidden; }
        body { font-family: var(--font-body, system-ui, sans-serif); color: var(--color-text, #1f2937); background: var(--color-background, #ffffff); overflow-x: hidden; }
        h1,h2,h3,h4,h5,h6 { font-family: var(--font-heading, system-ui, sans-serif); }
        main { min-height: 0; max-width: 100%; overflow-x: hidden; }
        /* İkonlar Font Awesome yüklenene kadar ekranı kaplamasın (FOUT önleme) */
        i.fas, i.far, i.fab, i.fal, i.fa-solid, i.fa-regular, i.fa-brands {
            font-size: 1em !important;
            width: 1em; height: 1em;
            display: inline-block;
            overflow: hidden;
            vertical-align: middle;
            max-width: 2em;
            max-height: 2em;
        }
    </style>
    <?php
    // Çizgi Aks tema CSS - inline yükle (URL/rewrite sorunlarında bile stiller gelsin)
    $cizgiaksCssPath = __DIR__ . '/../assets/css/theme.css';
    if (file_exists($cizgiaksCssPath)) {
        $themeCssContent = file_get_contents($cizgiaksCssPath);
        if ($themeCssContent !== false && $themeCssContent !== '') {
            echo '<style id="cizgiaks-theme-css">' . "\n" . $themeCssContent . "\n" . '</style>';
        }
    }
    ?>
    <?php echo ($sections ?? [])['styles'] ?? ''; ?>
    <?php if ($themeLoader): ?>
    <style id="custom-css"><?php try { echo $themeLoader->getCustomCss(); } catch (Throwable $e) { /* ignore */ } ?></style>
    <?php endif; ?>
    <?php
    $gaId = function_exists('get_option') ? trim((string) get_option('google_analytics', '')) : '';
    if ($gaId !== ''): ?>
    <!-- Google Analytics (GA4) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($gaId); ?>"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '<?php echo esc_js($gaId); ?>');
    </script>
    <?php endif; ?>
</head>
<body class="antialiased">
    <?php
    static $_cizgiaks_header_done = false;
    static $_cizgiaks_footer_done = false;
    if ($themeLoader && !$_cizgiaks_header_done) {
        $_cizgiaks_header_done = true;
        try {
            echo $themeLoader->renderSnippet('header', [
                'title' => $title ?? '',
                'current_page' => $current_page ?? ''
            ]);
        } catch (Throwable $e) {
            if (function_exists('error_log')) {
                error_log('Cizgi Aks header snippet error: ' . $e->getMessage());
            }
        }
    }
    ?>
    <main>
        <?php echo $content ?? ''; ?>
    </main>
    <?php
    if ($themeLoader && !$_cizgiaks_footer_done) {
        $_cizgiaks_footer_done = true;
        try {
            echo $themeLoader->renderSnippet('footer');
        } catch (Throwable $e) {
            if (function_exists('error_log')) {
                error_log('Cizgi Aks footer snippet error: ' . $e->getMessage());
            }
        }
    }
    ?>
    <?php if ($themeLoader && $themeLoader->getCustomSetting('show_back_to_top', true)): ?>
    <button id="back-to-top" class="cizgiaks-back-to-top" aria-label="<?php echo esc_attr(__('Yukarı çık')); ?>">
        <i class="fas fa-chevron-up"></i>
    </button>
    <?php endif; ?>
    <?php
    $themePath = ($themeLoader && method_exists($themeLoader, 'getThemePath')) ? $themeLoader->getThemePath() : null;
    if ($themeLoader && $themePath && file_exists($themePath . '/assets/js/theme.js')) {
        try {
            echo '<script src="' . esc_url($themeLoader->getJsUrl()) . '"></script>';
        } catch (Throwable $e) { /* ignore */ }
    }
    ?>
    <?php echo ($sections ?? [])['scripts'] ?? ''; ?>
    <?php if ($themeLoader): ?><script><?php try { echo $themeLoader->getCustomJs(); } catch (Throwable $e) { /* ignore */ } ?></script><?php endif; ?>
    <?php
    $tailwindUrl = class_exists('ViewRenderer') ? ViewRenderer::assetUrl('assets/js/tailwind.min.js') : '';
    if ($tailwindUrl): ?>
    <script src="<?php echo esc_url($tailwindUrl); ?>"></script>
    <?php endif; ?>
    <script>
    (function(){
        var btn = document.getElementById('back-to-top');
        if (btn) {
            window.addEventListener('scroll', function() {
                btn.classList.toggle('visible', window.scrollY > 500);
            });
            btn.addEventListener('click', function() { window.scrollTo({ top: 0, behavior: 'smooth' }); });
        }
    })();
    </script>
</body>
</html>
