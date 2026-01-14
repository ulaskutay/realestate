<!DOCTYPE html>
<html lang="<?php echo function_exists('get_current_language') ? get_current_language() : 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php 
    if (!class_exists('ViewRenderer')) {
        require_once __DIR__ . '/../../../core/ViewRenderer.php';
    }
    
    $seoTitle = get_option('seo_title', '');
    $seoDescription = get_option('seo_description', '');
    $seoAuthor = get_option('seo_author', '');
    
    $pageTitle = $title ?? ($seoTitle ?: __('Real Estate'));
    ?>
    <title><?php echo esc_html($pageTitle); ?></title>
    
    <?php 
    $siteDescription = get_option('site_description', '');
    $defaultMetaDesc = __('Find your dream home. Browse thousands of property listings.');
    $metaDesc = $meta_description ?? ($seoDescription ?: ($siteDescription ?: $defaultMetaDesc));
    ?>
    <meta name="description" content="<?php echo esc_attr($metaDesc); ?>">
    
    <?php if (!empty($seoAuthor)): ?>
    <meta name="author" content="<?php echo esc_attr($seoAuthor); ?>">
    <?php endif; ?>
    
    <!-- Favicon -->
    <?php 
    // Customize'dan favicon'u al
    $favicon = null;
    if ($themeLoader) {
        $favicon = $themeLoader->getFavicon();
    }
    
    if ($favicon): ?>
    <link rel="icon" type="image/png" href="<?php echo esc_url($favicon); ?>">
    <link rel="shortcut icon" href="<?php echo esc_url($favicon); ?>">
    <link rel="apple-touch-icon" href="<?php echo esc_url($favicon); ?>">
    <?php endif; ?>
    
    <!-- Preload fonts -->
    <link rel="preload" href="<?php echo ViewRenderer::assetUrl('assets/fonts/inter/inter-400.woff2'); ?>" as="font" type="font/woff2" crossorigin="anonymous">
    
    <!-- Font Definitions -->
    <style>
        @font-face {
            font-family: 'Inter';
            src: url('<?php echo ViewRenderer::assetUrl('assets/fonts/inter/inter-400.woff2'); ?>') format('woff2');
            font-weight: 400 700;
            font-style: normal;
            font-display: swap;
        }
        
        <?php
        // Zalando font URL'leri - assets klasörü root'ta
        $baseDir = dirname(dirname(dirname(__DIR__)));
        $zalandoBase = str_replace('\\', '/', $baseDir) . '/assets/Zalando_Sans_SemiExpanded/static/';
        $siteBase = ViewRenderer::siteUrl('');
        $zalandoRegularUrl = $siteBase . 'assets/Zalando_Sans_SemiExpanded/static/ZalandoSansSemiExpanded-Regular.ttf';
        $zalandoMediumUrl = $siteBase . 'assets/Zalando_Sans_SemiExpanded/static/ZalandoSansSemiExpanded-Medium.ttf';
        $zalandoSemiBoldUrl = $siteBase . 'assets/Zalando_Sans_SemiExpanded/static/ZalandoSansSemiExpanded-SemiBold.ttf';
        $zalandoBoldUrl = $siteBase . 'assets/Zalando_Sans_SemiExpanded/static/ZalandoSansSemiExpanded-Bold.ttf';
        ?>
        @font-face {
            font-family: 'Zalando Sans SemiExpanded';
            src: url('<?php echo esc_url($zalandoRegularUrl); ?>') format('truetype');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
        
        @font-face {
            font-family: 'Zalando Sans SemiExpanded';
            src: url('<?php echo esc_url($zalandoMediumUrl); ?>') format('truetype');
            font-weight: 500;
            font-style: normal;
            font-display: swap;
        }
        
        @font-face {
            font-family: 'Zalando Sans SemiExpanded';
            src: url('<?php echo esc_url($zalandoSemiBoldUrl); ?>') format('truetype');
            font-weight: 600;
            font-style: normal;
            font-display: swap;
        }
        
        @font-face {
            font-family: 'Zalando Sans SemiExpanded';
            src: url('<?php echo esc_url($zalandoBoldUrl); ?>') format('truetype');
            font-weight: 700;
            font-style: normal;
            font-display: swap;
        }
    </style>
    
    <!-- Critical CSS -->
    <style>
        body:not(.tw-loaded) {
            visibility: hidden;
        }
        body.tw-loaded {
            visibility: visible;
        }
    </style>
    
    <!-- Theme CSS Variables -->
    <?php if ($themeLoader): ?>
        <?php 
        // CSS değişkenlerini oluştur (her seferinde güncel ayarları almak için)
        $themeLoader->refreshSettings();
        echo $themeLoader->getCssVariablesTag(); 
        ?>
        <style>
            body {
                font-family: var(--font-body);
                color: var(--color-text);
                background-color: var(--color-background);
            }
            
            h1, h2, h3, h4, h5, h6 {
                font-family: var(--font-heading);
            }
        </style>
    <?php else: ?>
        <style>
            :root {
                --color-primary: #1e40af;
                --color-secondary: #1e293b;
                --color-accent: #0ea5e9;
                --color-background: #ffffff;
                --color-surface: #f8fafc;
                --color-text: #1e293b;
                --color-text-muted: #64748b;
                --font-heading: 'Zalando Sans SemiExpanded', sans-serif;
                --font-body: 'Zalando Sans SemiExpanded', sans-serif;
            }
            
            body {
                font-family: var(--font-body);
                color: var(--color-text);
                background-color: var(--color-background);
            }
            
            h1, h2, h3, h4, h5, h6 {
                font-family: var(--font-heading);
            }
        </style>
    <?php endif; ?>
    
    <!-- Theme CSS -->
    <?php if ($themeLoader && file_exists($themeLoader->getThemePath() . '/assets/css/theme.css')): ?>
    <?php $cssUrl = $themeLoader->getCssUrl(); ?>
    <link rel="preload" href="<?php echo $cssUrl; ?>" as="style">
    <link rel="stylesheet" href="<?php echo $cssUrl; ?>">
    <?php endif; ?>
    
    <!-- Preload Tailwind -->
    <link rel="preload" href="<?php echo ViewRenderer::assetUrl('assets/js/tailwind.min.js'); ?>" as="script">
    
    <!-- Additional Styles -->
    <?php echo $sections['styles'] ?? ''; ?>
    
    <!-- Custom CSS -->
    <?php if ($themeLoader): ?>
    <style id="custom-css">
        <?php echo $themeLoader->getCustomCss(); ?>
    </style>
    <?php endif; ?>
    
    <?php 
    $googleAnalytics = get_option('google_analytics', '');
    $googleTagManager = get_option('google_tag_manager', '');
    
    if (!empty($googleTagManager)): ?>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','<?php echo esc_js($googleTagManager); ?>');</script>
    <?php endif;
    
    if (!empty($googleAnalytics)): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_js($googleAnalytics); ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?php echo esc_js($googleAnalytics); ?>');
    </script>
    <?php endif; ?>
</head>
<body class="antialiased">
    <?php if (!empty($googleTagManager)): ?>
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr($googleTagManager); ?>"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <?php endif; ?>
    
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
    
    <!-- Back to Top -->
    <?php if ($themeLoader && $themeLoader->getCustomSetting('show_back_to_top', true)): ?>
    <button id="back-to-top" class="fixed bottom-6 right-6 p-3 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg opacity-0 invisible transition-all duration-300 hover:scale-110 z-50">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
    </button>
    <?php endif; ?>
    
    <!-- Theme JS -->
    <?php if ($themeLoader && file_exists($themeLoader->getThemePath() . '/assets/js/theme.js')): ?>
    <?php $jsUrl = $themeLoader->getJsUrl(); ?>
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
    
    <!-- Tailwind CSS -->
    <script>
        (function() {
            const script = document.createElement('script');
            script.src = '<?php echo ViewRenderer::assetUrl('assets/js/tailwind.min.js'); ?>';
            script.onload = function() {
                setTimeout(function() {
                    document.body.classList.add('tw-loaded');
                }, 10);
            };
            script.onerror = function() {
                document.body.classList.add('tw-loaded');
            };
            document.head.appendChild(script);
        })();
    </script>
    
    <script>
        // Back to top
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
    </script>
</body>
</html>
