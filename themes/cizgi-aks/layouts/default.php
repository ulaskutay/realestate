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
    $seoTitle = trim((string) get_option('seo_title', ''));
    $seoDesc  = trim((string) get_option('seo_description', ''));
    $pageTitle = isset($title) && trim((string) $title) !== '' ? trim((string) $title) : '';
    $metaDesc  = isset($meta_description) && trim((string) $meta_description) !== '' ? trim((string) $meta_description) : '';
    if ($pageTitle === '') {
        if ($isHome) {
            $pageTitle = $seoTitle !== '' ? $seoTitle : (function_exists('get_module_settings') ? (get_module_settings('seo')['meta_title_home'] ?? 'Çizgi Aks Gayrimenkul') : 'Çizgi Aks Gayrimenkul');
        } else {
            $template = function_exists('get_module_settings') ? (get_module_settings('seo')['meta_title_default'] ?? '{page_title} - {site_name}') : '{page_title} - {site_name}';
            $siteName = function_exists('get_option') ? (get_option('site_name', '') ?: 'CMS') : 'CMS';
            $pageTitleSeg = $reqPath ? (function_exists('get_seo_page_title_from_path') ? get_seo_page_title_from_path($reqPath) : ucfirst(str_replace(['-', '_'], ' ', explode('/', $reqPath)[0]))) : __('Sayfa');
            $pageTitle = str_replace(['{site_name}', '{page_title}'], [$siteName, $pageTitleSeg], $template);
        }
    }
    if ($pageTitle === '') $pageTitle = 'Çizgi Aks Gayrimenkul';
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
    <?php if ($themeLoader): $themeLoader->refreshSettings(); echo $themeLoader->getCssVariablesTag(); ?>
    <style>
        body { font-family: var(--font-body, system-ui, sans-serif); color: var(--color-text); background: var(--color-background); }
        h1,h2,h3,h4,h5,h6 { font-family: var(--font-heading, system-ui, sans-serif); }
        /* Layout shift önleme: stil tam oturana kadar gövdeyi gizle */
        body:not(.tw-loaded) { visibility: hidden; }
        body.tw-loaded { visibility: visible; }
        html { min-height: 100%; }
        main { min-height: 0; }
    </style>
    <?php else: ?>
    <style>
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
        body { font-family: var(--font-body); color: var(--color-text); background: var(--color-background); }
    </style>
    <?php endif; ?>
    <?php if ($themeLoader && file_exists($themeLoader->getThemePath() . '/assets/css/theme.css')): ?>
    <link rel="stylesheet" href="<?php echo $themeLoader->getCssUrl(); ?>">
    <?php endif; ?>
    <link rel="preload" href="<?php echo ViewRenderer::assetUrl('assets/js/tailwind.min.js'); ?>" as="script">
    <?php echo $sections['styles'] ?? ''; ?>
    <?php if ($themeLoader): ?>
    <style id="custom-css"><?php echo $themeLoader->getCustomCss(); ?></style>
    <?php endif; ?>
</head>
<body class="antialiased">
    <?php
    if ($themeLoader) {
        echo $themeLoader->renderSnippet('header', [
            'title' => $title ?? '',
            'current_page' => $current_page ?? ''
        ]);
    }
    ?>
    <main>
        <?php echo $content ?? ''; ?>
    </main>
    <?php
    if ($themeLoader) {
        echo $themeLoader->renderSnippet('footer');
    }
    ?>
    <?php if ($themeLoader && $themeLoader->getCustomSetting('show_back_to_top', true)): ?>
    <button id="back-to-top" class="cizgiaks-back-to-top" aria-label="<?php echo esc_attr(__('Yukarı çık')); ?>">
        <i class="fas fa-chevron-up"></i>
    </button>
    <?php endif; ?>
    <?php if ($themeLoader && file_exists($themeLoader->getThemePath() . '/assets/js/theme.js')): ?>
    <script src="<?php echo $themeLoader->getJsUrl(); ?>"></script>
    <?php endif; ?>
    <?php echo $sections['scripts'] ?? ''; ?>
    <?php if ($themeLoader): ?><script><?php echo $themeLoader->getCustomJs(); ?></script><?php endif; ?>
    <script>
    (function(){
        var done = function(){ document.body.classList.add('tw-loaded'); };
        var fallback = setTimeout(done, 3500);
        var s = document.createElement('script');
        s.src = '<?php echo ViewRenderer::assetUrl('assets/js/tailwind.min.js'); ?>';
        s.onload = s.onerror = function(){ clearTimeout(fallback); done(); };
        document.head.appendChild(s);
    })();
    </script>
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
