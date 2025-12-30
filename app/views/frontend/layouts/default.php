<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php 
    // SEO Ayarları
    $seoTitle = get_option('seo_title', '');
    $seoDescription = get_option('seo_description', '');
    $seoAuthor = get_option('seo_author', '');
    
    // Title: Önce sayfa title'ı, yoksa SEO title, yoksa varsayılan
    $pageTitle = isset($title) ? $title : ($seoTitle ?: 'CMS - Ana Sayfa');
    ?>
    <title><?php echo ViewRenderer::escHtml($pageTitle); ?></title>
    
    <?php 
    // Meta Description: Önce sayfa meta description'ı, yoksa SEO description
    $metaDesc = isset($meta_description) ? $meta_description : $seoDescription;
    if (!empty($metaDesc)): ?>
    <meta name="description" content="<?php echo esc_attr($metaDesc); ?>">
    <?php endif;
    
    // Meta Author: SEO author
    if (!empty($seoAuthor)): ?>
    <meta name="author" content="<?php echo esc_attr($seoAuthor); ?>">
    <?php endif; ?>
    
    <!-- Favicon -->
    <?php 
    $favicon = get_site_favicon();
    if (!empty($favicon)): ?>
    <link rel="icon" type="image/png" href="<?php echo esc_url($favicon); ?>">
    <link rel="shortcut icon" href="<?php echo esc_url($favicon); ?>">
    <link rel="apple-touch-icon" href="<?php echo esc_url($favicon); ?>">
    <?php endif; ?>
    
    <!-- Preload Material Symbols Font for Faster Icon Loading -->
    <link rel="preload" href="<?php echo ViewRenderer::assetUrl('assets/fonts/material-symbols/material-symbols-outlined.woff2'); ?>" as="font" type="font/woff2" crossorigin="anonymous">
    
    <!-- Preload Fonts CSS to prevent render-blocking -->
    <link rel="preload" href="<?php echo ViewRenderer::assetUrl('assets/css/fonts.css'); ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="<?php echo ViewRenderer::assetUrl('assets/css/fonts.css'); ?>"></noscript>
    
    <!-- Material Symbols Base Styles (Inline for Faster Rendering) -->
    <style>
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined';
            font-weight: normal;
            font-style: normal;
            font-size: 24px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
    
    <?php 
    // Tema fontlarını yükle
    $renderer = ViewRenderer::getInstance();
    $themeLoader = $renderer->getThemeLoader();
    $headingFont = $themeLoader && $renderer->hasActiveTheme() ? $themeLoader->getFont('heading', 'Inter') : 'Inter';
    $bodyFont = $themeLoader && $renderer->hasActiveTheme() ? $themeLoader->getFont('body', 'Inter') : 'Inter';
    ?>
    
    <?php if ($renderer->hasActiveTheme()): ?>
    <!-- Tema CSS Değişkenleri -->
    <?php echo $themeLoader->getCssVariablesTag(); ?>
    <style>
        :root {
            --font-heading: '<?php echo $headingFont; ?>', sans-serif;
            --font-body: '<?php echo $bodyFont; ?>', sans-serif;
        }
        body {
            font-family: var(--font-body);
            color: var(--color-text, #1f2937);
            background-color: var(--color-background, #ffffff);
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-heading);
        }
        .bg-primary { background-color: var(--color-primary, #137fec); }
        .text-primary { color: var(--color-primary, #137fec); }
        .border-primary { border-color: var(--color-primary, #137fec); }
        .bg-secondary { background-color: var(--color-secondary, #6366f1); }
        .text-secondary { color: var(--color-secondary, #6366f1); }
    </style>
    <?php else: ?>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#137fec',
                        'secondary': '#6366f1',
                        'brand-dark': '#1F2937',
                    },
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <?php endif; ?>
    
    <!-- Site Stilleri -->
    <link rel="stylesheet" href="<?php echo ViewRenderer::assetUrl('frontend/css/main.css'); ?>">
    <link rel="stylesheet" href="<?php echo ViewRenderer::assetUrl('frontend/css/slider.css'); ?>">
    
    <?php 
    // Tema head çıktısı
    if ($renderer->hasActiveTheme()) {
        echo $themeLoader->getHeadOutput();
    }
    
    // Ek stiller
    if (isset($sections['styles'])) {
        echo $sections['styles'];
    }
    
    // Google Analytics, Tag Manager ve Ads kodları
    $googleAnalytics = get_option('google_analytics', '');
    $googleTagManager = get_option('google_tag_manager', '');
    $googleAds = get_option('google_ads', '');
    
    // Google Tag Manager - Head (hemen açılış tag'inden sonra)
    if (!empty($googleTagManager)): ?>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','<?php echo esc_js($googleTagManager); ?>');</script>
    <!-- End Google Tag Manager -->
    <?php endif;
    
    // Google Analytics
    if (!empty($googleAnalytics)): ?>
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_js($googleAnalytics); ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?php echo esc_js($googleAnalytics); ?>');
    </script>
    <!-- End Google Analytics -->
    <?php endif;
    
    // Google Ads
    if (!empty($googleAds)): ?>
    <!-- Google Ads -->
    <?php echo $googleAds; ?>
    <!-- End Google Ads -->
    <?php endif; ?>
</head>
<body class="current-page-<?php echo isset($current_page) ? ViewRenderer::escAttr($current_page) : 'home'; ?>">
    <?php 
    // Google Tag Manager - Body (hemen body açılış tag'inden sonra)
    if (!empty($googleTagManager)): ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr($googleTagManager); ?>"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php endif; ?>
    <?php 
    // Header snippet'ini render et
    $renderer->snippet('header');
    ?>
    
    <main id="main">
        <?php 
        if (isset($content)) {
            echo $content;
        } else {
            echo isset($sections['content']) ? $sections['content'] : '';
        }
        ?>
    </main>
    
    <?php 
    // Footer snippet'ini render et
    $renderer->snippet('footer');
    ?>
    
    <!-- Scripts -->
    <?php 
    // Tema footer çıktısı
    if ($renderer->hasActiveTheme()) {
        echo $themeLoader->getFooterOutput();
    }
    
    // Ek scriptler
    if (isset($sections['scripts'])) {
        echo $sections['scripts'];
    }
    ?>
    
    <!-- Tailwind CSS - Load at end of body to prevent render-blocking -->
    <script src="<?php echo ViewRenderer::assetUrl('assets/js/tailwind.min.js'); ?>"></script>
    
    <!-- Privacy-Friendly Analytics -->
    <script src="/public/frontend/js/analytics.js" defer></script>
</body>
</html>
