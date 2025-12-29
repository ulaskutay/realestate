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
    $pageTitle = $title ?? ($seoTitle ?: 'Starter Theme');
    ?>
    <title><?php echo esc_html($pageTitle); ?></title>
    
    <?php 
    // Meta Description: Önce sayfa meta description'ı, yoksa SEO description
    $metaDesc = $meta_description ?? $seoDescription;
    if (!empty($metaDesc)): ?>
    <meta name="description" content="<?php echo esc_attr($metaDesc); ?>">
    <?php endif;
    
    // Meta Author: SEO author
    if (!empty($seoAuthor)): ?>
    <meta name="author" content="<?php echo esc_attr($seoAuthor); ?>">
    <?php endif; ?>
    
    <!-- Favicon -->
    <?php 
    $favicon = $themeLoader ? $themeLoader->getFavicon() : null;
    if ($favicon): ?>
    <link rel="icon" type="image/x-icon" href="<?php echo esc_url($favicon); ?>">
    <link rel="shortcut icon" href="<?php echo esc_url($favicon); ?>">
    <link rel="apple-touch-icon" href="<?php echo esc_url($favicon); ?>">
    <?php endif; ?>
    
    <!-- Local Fonts -->
    <link rel="stylesheet" href="<?php echo ViewRenderer::assetUrl('assets/css/fonts.css'); ?>">
    
    <!-- Tailwind CSS -->
    <script src="<?php echo ViewRenderer::assetUrl('assets/js/tailwind.min.js'); ?>"></script>
    
    <!-- Theme CSS Variables -->
    <?php if ($themeLoader): ?>
        <?php echo $themeLoader->getCssVariablesTag(); ?>
    <?php endif; ?>
    
    <style>
        :root {
            --color-primary: <?php echo $themeLoader ? $themeLoader->getColor('primary', '#137fec') : '#137fec'; ?>;
            --color-secondary: <?php echo $themeLoader ? $themeLoader->getColor('secondary', '#6366f1') : '#6366f1'; ?>;
            --color-accent: <?php echo $themeLoader ? $themeLoader->getColor('accent', '#10b981') : '#10b981'; ?>;
            --color-background: <?php echo $themeLoader ? $themeLoader->getColor('background', '#ffffff') : '#ffffff'; ?>;
            --color-surface: <?php echo $themeLoader ? $themeLoader->getColor('surface', '#f8fafc') : '#f8fafc'; ?>;
            --color-text: <?php echo $themeLoader ? $themeLoader->getColor('text', '#1f2937') : '#1f2937'; ?>;
            --color-text-muted: <?php echo $themeLoader ? $themeLoader->getColor('text_muted', '#6b7280') : '#6b7280'; ?>;
            --font-heading: <?php echo $themeLoader ? "'" . $themeLoader->getFont('heading', 'Poppins') . "'" : "'Poppins'"; ?>, sans-serif;
            --font-body: <?php echo $themeLoader ? "'" . $themeLoader->getFont('body', 'Inter') . "'" : "'Inter'"; ?>, sans-serif;
        }
        
        body {
            font-family: var(--font-body);
            color: var(--color-text);
            background-color: var(--color-background);
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-heading);
        }
        
        .bg-primary { background-color: var(--color-primary); }
        .text-primary { color: var(--color-primary); }
        .border-primary { border-color: var(--color-primary); }
        .hover\:bg-primary:hover { background-color: var(--color-primary); }
        
        .bg-secondary { background-color: var(--color-secondary); }
        .text-secondary { color: var(--color-secondary); }
        
        .bg-accent { background-color: var(--color-accent); }
        .text-accent { color: var(--color-accent); }
        
        .bg-surface { background-color: var(--color-surface); }
        .text-muted { color: var(--color-text-muted); }
        
        .btn-primary {
            background-color: var(--color-primary);
            color: white;
            transition: all 0.2s;
        }
        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background-color: transparent;
            border: 2px solid var(--color-primary);
            color: var(--color-primary);
            transition: all 0.2s;
        }
        .btn-secondary:hover {
            background-color: var(--color-primary);
            color: white;
        }
        
        .gradient-primary {
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        }
        
        .gradient-text {
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
    
    <!-- Theme CSS -->
    <?php if ($themeLoader && file_exists($themeLoader->getThemePath() . '/assets/css/theme.css')): ?>
    <?php $cssUrl = $themeLoader->getCssUrl(); ?>
    <link rel="preload" href="<?php echo $cssUrl; ?>" as="style">
    <link rel="stylesheet" href="<?php echo $cssUrl; ?>">
    <?php endif; ?>
    
    <!-- Additional Styles -->
    <?php echo $sections['styles'] ?? ''; ?>
    
    <!-- Custom CSS -->
    <?php if ($themeLoader): ?>
    <style id="custom-css">
        <?php echo $themeLoader->getCustomCss(); ?>
    </style>
    <?php endif; ?>
    
    <?php 
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
<body class="antialiased">
    <?php 
    // Google Tag Manager - Body (hemen body açılış tag'inden sonra)
    if (!empty($googleTagManager)): ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr($googleTagManager); ?>"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php endif; ?>
    
    <?php 
    // Header
    if ($themeLoader) {
        echo $themeLoader->renderSnippet('header', [
            'title' => $title ?? '',
            'current_page' => $current_page ?? ''
        ]);
    }
    ?>
    
    <!-- Main Content -->
    <main>
        <?php echo $content ?? ''; ?>
    </main>
    
    <?php 
    // Footer
    if ($themeLoader) {
        echo $themeLoader->renderSnippet('footer');
    }
    ?>
    
    <!-- Back to Top Button -->
    <?php if ($themeLoader && $themeLoader->getCustomSetting('show_back_to_top', true)): ?>
    <button id="back-to-top" class="fixed bottom-6 right-6 p-3 bg-primary text-white rounded-full shadow-lg opacity-0 invisible transition-all duration-300 hover:scale-110 z-50">
        <span class="material-symbols-outlined">arrow_upward</span>
    </button>
    <?php endif; ?>
    
    <!-- Theme JS -->
    <?php if ($themeLoader && file_exists($themeLoader->getThemePath() . '/assets/js/theme.js')): ?>
    <?php $jsUrl = $themeLoader->getJsUrl(); ?>
    <link rel="preload" href="<?php echo $jsUrl; ?>" as="script">
    <script src="<?php echo $jsUrl; ?>"></script>
    <?php endif; ?>
    
    <!-- Additional Scripts -->
    <?php echo $sections['scripts'] ?? ''; ?>
    
    <!-- Custom JS -->
    <?php if ($themeLoader): ?>
    <script>
        <?php echo $themeLoader->getCustomJs(); ?>
    </script>
    <?php endif; ?>
    
    <!-- Privacy-Friendly Analytics -->
    <script src="/public/frontend/js/analytics.js" defer></script>
    
    <script>
        // Back to top functionality
        const backToTop = document.getElementById('back-to-top');
        if (backToTop) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 500) {
                    backToTop.classList.remove('opacity-0', 'invisible');
                    backToTop.classList.add('opacity-100', 'visible');
                } else {
                    backToTop.classList.add('opacity-0', 'invisible');
                    backToTop.classList.remove('opacity-100', 'visible');
                }
            });
            
            backToTop.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
        
        // Mobile menu toggle - header.php'de yönetiliyor
    </script>
</body>
</html>

