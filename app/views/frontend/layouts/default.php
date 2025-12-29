<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? ViewRenderer::escHtml($title) : 'CMS - Ana Sayfa'; ?></title>
    
    <!-- Local Fonts -->
    <link rel="stylesheet" href="<?php echo ViewRenderer::assetUrl('assets/css/fonts.css'); ?>">
    
    <?php 
    // Tema fontlarını yükle
    $renderer = ViewRenderer::getInstance();
    $themeLoader = $renderer->getThemeLoader();
    $headingFont = $themeLoader && $renderer->hasActiveTheme() ? $themeLoader->getFont('heading', 'Inter') : 'Inter';
    $bodyFont = $themeLoader && $renderer->hasActiveTheme() ? $themeLoader->getFont('body', 'Inter') : 'Inter';
    ?>
    
    <!-- Tailwind CSS -->
    <script src="<?php echo ViewRenderer::assetUrl('assets/js/tailwind.min.js'); ?>"></script>
    
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
    ?>
</head>
<body class="current-page-<?php echo isset($current_page) ? ViewRenderer::escAttr($current_page) : 'home'; ?>">
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
    
    <!-- Privacy-Friendly Analytics -->
    <script src="/public/frontend/js/analytics.js" defer></script>
</body>
</html>
