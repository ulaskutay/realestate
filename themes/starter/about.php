<?php
/**
 * About Page Template
 * Hakkımızda sayfası için özel şablon
 */

// Tema yükleyici kontrolü
if (!isset($themeLoader)) {
    require_once __DIR__ . '/../../core/ThemeLoader.php';
    $themeLoader = ThemeLoader::getInstance();
    $activeTheme = get_option('active_theme', 'starter');
    $themeLoader->loadTheme($activeTheme);
}

// Sayfa ve custom field verilerini hazırla
$heroTitle = $page['title'] ?? 'Hakkımızda';
$heroSubtitle = $customFields['hero_subtitle'] ?? 'ABOUT OUR AGENCY';
$heroDescription = $page['excerpt'] ?? '';

// About sections (Story & Mission)
$aboutSections = !empty($customFields['about_sections']) ? json_decode($customFields['about_sections'], true) : [];
if (!is_array($aboutSections)) $aboutSections = [];

// Core Values - service_features alanını kullanıyoruz
$coreValues = !empty($customFields['service_features']) ? json_decode($customFields['service_features'], true) : [];
if (!is_array($coreValues)) $coreValues = [];

// Team members
$teamMembers = !empty($customFields['team_members']) ? json_decode($customFields['team_members'], true) : [];
if (!is_array($teamMembers)) $teamMembers = [];

// Statistics
$stats = !empty($customFields['stats']) ? json_decode($customFields['stats'], true) : [];
if (!is_array($stats)) $stats = [];

// CTA
$ctaTitle = $customFields['cta_title'] ?? 'Want to be part of our story?';
$ctaDescription = $customFields['cta_description'] ?? 'Whether you are a potential client or a future team member, we would love to hear from you.';
$ctaButton1Text = $customFields['cta_button_text'] ?? 'Contact Us';
$ctaButton1Link = $customFields['cta_button_link'] ?? '/contact';
$ctaButton2Text = 'Join the Team';
$ctaButton2Link = '/careers';

// SEO
$metaTitle = $page['meta_title'] ?: $heroTitle;
$metaDescription = $page['meta_description'] ?: $heroDescription;
$metaKeywords = $page['meta_keywords'] ?? '';
?>
<!DOCTYPE html>
<html lang="tr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($metaTitle); ?></title>
    
    <?php if ($metaDescription): ?>
    <meta name="description" content="<?php echo esc_attr($metaDescription); ?>">
    <?php endif; ?>
    
    <?php if ($metaKeywords): ?>
    <meta name="keywords" content="<?php echo esc_attr($metaKeywords); ?>">
    <?php endif; ?>
    
    <!-- Favicon -->
    <?php 
    $favicon = $themeLoader->getFavicon();
    if ($favicon): ?>
    <link rel="icon" type="image/x-icon" href="<?php echo esc_url($favicon); ?>">
    <link rel="shortcut icon" href="<?php echo esc_url($favicon); ?>">
    <link rel="apple-touch-icon" href="<?php echo esc_url($favicon); ?>">
    <?php endif; ?>
    
    <!-- Local Fonts -->
    <link rel="preload" href="<?php echo ViewRenderer::assetUrl('assets/css/fonts.css'); ?>" as="style">
    <link rel="stylesheet" href="<?php echo ViewRenderer::assetUrl('assets/css/fonts.css'); ?>">
    
    <!-- Tailwind CSS -->
    <link rel="preload" href="<?php echo ViewRenderer::assetUrl('assets/js/tailwind.min.js'); ?>" as="script">
    <script src="<?php echo ViewRenderer::assetUrl('assets/js/tailwind.min.js'); ?>"></script>
    
    <!-- Theme CSS Variables -->
    <?php echo $themeLoader->getCssVariablesTag(); ?>
    
    <!-- Theme CSS -->
    <?php if (file_exists($themeLoader->getThemePath() . '/assets/css/theme.css')): ?>
    <link rel="preload" href="<?php echo $themeLoader->getCssUrl(); ?>" as="style">
    <link rel="stylesheet" href="<?php echo $themeLoader->getCssUrl(); ?>">
    <?php endif; ?>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #0a0e1a;
            color: #ffffff;
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(59, 130, 246, 0.2);
        }
        
        .stat-card {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.05) 100%);
            border: 1px solid rgba(59, 130, 246, 0.2);
        }
        
        .team-card {
            position: relative;
            overflow: hidden;
        }
        
        .team-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(180deg, transparent 0%, rgba(10, 14, 26, 0.9) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .team-card:hover::before {
            opacity: 1;
        }
        
        .team-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1.5rem;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }
        
        .team-card:hover .team-info {
            transform: translateY(0);
        }
    </style>
</head>
<body class="bg-[#0a0e1a] text-white antialiased">
    
    <?php
    // Render header
    echo $themeLoader->renderSnippet('header', [
        'title' => $metaTitle,
        'current_page' => 'about'
    ]);
    ?>
    
    <main class="flex flex-col w-full overflow-hidden">
        
        <!-- Hero Section -->
        <section class="relative min-h-[60vh] flex items-center justify-center px-6 py-20 overflow-hidden">
            <!-- Background gradient -->
            <div class="absolute inset-0 bg-gradient-to-br from-blue-600/10 via-transparent to-blue-900/10"></div>
            
            <div class="relative z-10 max-w-5xl mx-auto text-center">
                <p class="text-blue-400 text-sm font-semibold tracking-wider uppercase mb-4">
                    <?php echo esc_html($heroSubtitle); ?>
                </p>
                
                <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold mb-6 leading-tight">
                    <?php 
                    $titleParts = explode(' ', $heroTitle);
                    $lastWord = array_pop($titleParts);
                    echo esc_html(implode(' ', $titleParts)); 
                    ?>
                    <span class="gradient-text"><?php echo esc_html($lastWord); ?></span>
                </h1>
                
                <?php if ($heroDescription): ?>
                <p class="text-lg md:text-xl text-gray-300 max-w-3xl mx-auto leading-relaxed">
                    <?php echo nl2br(esc_html($heroDescription)); ?>
                </p>
                <?php endif; ?>
            </div>
        </section>
        
        <!-- Story & Mission Section -->
        <?php if (!empty($aboutSections)): ?>
        <section class="py-20 px-6">
            <div class="max-w-7xl mx-auto">
                <?php foreach ($aboutSections as $index => $section): ?>
                    <?php 
                    $isEven = $index % 2 === 0;
                    $title = $section['title'] ?? '';
                    $content = $section['content'] ?? '';
                    $image = $section['image'] ?? '';
                    ?>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-20 <?php echo !$isEven ? 'lg:flex-row-reverse' : ''; ?>">
                        <!-- Text Content -->
                        <div class="<?php echo !$isEven ? 'lg:order-2' : ''; ?>">
                            <h2 class="text-3xl md:text-4xl font-bold mb-6">
                                <?php echo esc_html($title); ?>
                            </h2>
                            
                            <div class="text-gray-300 leading-relaxed space-y-4">
                                <?php echo nl2br(esc_html($content)); ?>
                            </div>
                        </div>
                        
                        <!-- Images Grid -->
                        <div class="<?php echo !$isEven ? 'lg:order-1' : ''; ?>">
                            <?php if ($image): ?>
                            <div class="rounded-2xl overflow-hidden">
                                <img src="<?php echo esc_url($image); ?>" 
                                     alt="<?php echo esc_attr($title); ?>" 
                                     class="w-full h-full object-cover">
                            </div>
                            <?php else: ?>
                            <!-- Placeholder grid if no image -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-gray-800 rounded-2xl aspect-square"></div>
                                <div class="bg-gray-800 rounded-2xl aspect-square"></div>
                                <div class="bg-gray-800 rounded-2xl aspect-square col-span-2"></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
        
        <!-- Core Values Section -->
        <?php if (!empty($coreValues)): ?>
        <section class="py-20 px-6 bg-gradient-to-b from-transparent via-blue-950/10 to-transparent">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-bold mb-4">Our Core Values</h2>
                    <p class="text-gray-400 text-lg">These principles guide every decision we make and every product we ship.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <?php foreach ($coreValues as $value): ?>
                        <?php
                        $icon = $value['icon'] ?? 'lightbulb';
                        $title = $value['title'] ?? '';
                        $description = $value['description'] ?? '';
                        ?>
                        <div class="bg-gray-900/50 backdrop-blur-sm border border-gray-800 rounded-2xl p-8 card-hover">
                            <div class="w-16 h-16 bg-blue-600/20 rounded-xl flex items-center justify-center mb-6">
                                <span class="material-symbols-outlined text-4xl text-blue-400"><?php echo esc_html($icon); ?></span>
                            </div>
                            
                            <h3 class="text-xl font-bold mb-3"><?php echo esc_html($title); ?></h3>
                            <p class="text-gray-400 leading-relaxed"><?php echo esc_html($description); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
        
        <!-- Team Section -->
        <?php if (!empty($teamMembers)): ?>
        <section class="py-20 px-6">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center justify-between mb-12">
                    <div>
                        <h2 class="text-4xl md:text-5xl font-bold mb-4">Meet the Minds</h2>
                        <p class="text-gray-400 text-lg">The talented individuals driving our innovation.</p>
                    </div>
                    
                    <a href="/careers" class="hidden md:flex items-center gap-2 text-blue-400 hover:text-blue-300 transition-colors">
                        <span class="font-semibold">Join Our Team</span>
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </a>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($teamMembers as $member): ?>
                        <?php
                        $photo = $member['photo'] ?? '';
                        $name = $member['name'] ?? '';
                        $position = $member['position'] ?? '';
                        ?>
                        <div class="team-card rounded-2xl overflow-hidden bg-gray-900 border border-gray-800 group">
                            <?php if ($photo): ?>
                            <div class="aspect-[3/4] relative">
                                <img src="<?php echo esc_url($photo); ?>" 
                                     alt="<?php echo esc_attr($name); ?>" 
                                     class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500">
                                
                                <div class="team-info">
                                    <h3 class="text-xl font-bold mb-1"><?php echo esc_html($name); ?></h3>
                                    <p class="text-blue-400 text-sm"><?php echo esc_html($position); ?></p>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="aspect-[3/4] bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center">
                                <span class="material-symbols-outlined text-6xl text-gray-700">person</span>
                            </div>
                            <div class="p-4">
                                <h3 class="text-lg font-bold"><?php echo esc_html($name); ?></h3>
                                <p class="text-blue-400 text-sm"><?php echo esc_html($position); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
        
        <!-- Statistics Section -->
        <?php if (!empty($stats)): ?>
        <section class="py-20 px-6">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <?php foreach ($stats as $stat): ?>
                        <?php
                        $number = $stat['number'] ?? '0';
                        $label = $stat['label'] ?? '';
                        ?>
                        <div class="stat-card rounded-2xl p-8 text-center">
                            <div class="text-5xl md:text-6xl font-bold gradient-text mb-2">
                                <?php echo esc_html($number); ?>
                            </div>
                            <p class="text-gray-400 text-sm uppercase tracking-wider">
                                <?php echo esc_html($label); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
        
        <!-- CTA Section -->
        <section class="py-20 px-6">
            <div class="max-w-4xl mx-auto">
                <div class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-blue-600 to-blue-800 p-12 md:p-16 text-center">
                    <!-- Decorative elements -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 w-64 h-64 bg-blue-900/50 rounded-full blur-3xl"></div>
                    
                    <div class="relative z-10">
                        <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-6">
                            <?php echo esc_html($ctaTitle); ?>
                        </h2>
                        
                        <p class="text-lg md:text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                            <?php echo esc_html($ctaDescription); ?>
                        </p>
                        
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="<?php echo esc_url($ctaButton1Link); ?>" 
                               class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-blue-600 rounded-xl font-semibold hover:bg-gray-100 transition-all hover:scale-105">
                                <?php echo esc_html($ctaButton1Text); ?>
                            </a>
                            
                            <a href="<?php echo esc_url($ctaButton2Link); ?>" 
                               class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-transparent border-2 border-white text-white rounded-xl font-semibold hover:bg-white hover:text-blue-600 transition-all hover:scale-105">
                                <?php echo esc_html($ctaButton2Text); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
    </main>
    
    <?php
    // Render footer
    echo $themeLoader->renderSnippet('footer');
    ?>
    
    <!-- Theme JS -->
    <?php if (file_exists($themeLoader->getThemePath() . '/assets/js/theme.js')): ?>
    <link rel="preload" href="<?php echo $themeLoader->getJsUrl(); ?>" as="script">
    <script src="<?php echo $themeLoader->getJsUrl(); ?>" defer></script>
    <?php endif; ?>
    
    <script defer>
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
        
        // Scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('section > div').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'all 0.6s ease-out';
            observer.observe(el);
        });
    </script>
</body>
</html>

