<?php
/**
 * Hizmet Detay Sayfası Template
 * Modern, profesyonel tasarım - Tema sistemine entegre
 */

// ThemeLoader kontrolü
$hasThemeLoader = isset($themeLoader) && $themeLoader;

// Site bilgileri
$siteName = get_option('site_name', 'Site Adı');

// Özel alanları parse et
$heroSubtitle = $customFields['hero_subtitle'] ?? '';
$heroImage = $customFields['hero_image'] ?? $page['featured_image'] ?? '';

// Hizmet özellikleri (JSON)
$serviceFeatures = [];
if (!empty($customFields['service_features'])) {
    $serviceFeatures = json_decode($customFields['service_features'], true);
    if (!is_array($serviceFeatures)) $serviceFeatures = [];
}

// Süreç adımları (JSON)
$processSteps = [];
if (!empty($customFields['process_steps'])) {
    $processSteps = json_decode($customFields['process_steps'], true);
    if (!is_array($processSteps)) $processSteps = [];
}

// Avantajlar (JSON)
$advantages = [];
if (!empty($customFields['advantages'])) {
    $advantages = json_decode($customFields['advantages'], true);
    if (!is_array($advantages)) $advantages = [];
}

// SSS (JSON)
$faqs = [];
if (!empty($customFields['faqs'])) {
    $faqs = json_decode($customFields['faqs'], true);
    if (!is_array($faqs)) $faqs = [];
}

// CTA Ayarları
$ctaTitle = $customFields['cta_title'] ?? 'Projenizi Başlatalım';
$ctaDescription = $customFields['cta_description'] ?? 'Hemen iletişime geçin ve size özel çözümlerimizi keşfedin.';
$ctaButtonText = $customFields['cta_button_text'] ?? 'Teklif Al';
$ctaButtonLink = $customFields['cta_button_link'] ?? '/contact';

// İlgili hizmetler
$relatedServices = [];
if (!empty($customFields['related_services'])) {
    $relatedServices = json_decode($customFields['related_services'], true);
    if (!is_array($relatedServices)) $relatedServices = [];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo esc_html($title ?? $page['title']); ?> - <?php echo esc_html($siteName); ?></title>
    
    <?php if ($hasThemeLoader): ?>
        <?php 
        $favicon = $themeLoader->getFavicon();
        if ($favicon): ?>
        <link rel="icon" type="image/x-icon" href="<?php echo esc_url($favicon); ?>">
        <?php endif; ?>
        <?php echo $themeLoader->getCssVariablesTag(); ?>
    <?php endif; ?>
    
    <!-- Preload Material Symbols Font for Faster Icon Loading -->
    <link rel="preload" href="<?php echo ViewRenderer::assetUrl('assets/fonts/material-symbols/material-symbols-outlined.woff2'); ?>" as="font" type="font/woff2" crossorigin="anonymous">
    
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
    
    <!-- Local Fonts -->
    <link rel="stylesheet" href="<?php echo ViewRenderer::assetUrl('assets/css/fonts.css'); ?>">
    
    <!-- Tailwind CSS -->
    <script src="<?php echo ViewRenderer::assetUrl('assets/js/tailwind.min.js'); ?>"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: 'var(--color-primary, #2563eb)',
                        secondary: 'var(--color-secondary, #7c3aed)',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    
    <style>
        :root {
            --color-primary: <?php echo $hasThemeLoader ? $themeLoader->getColor('primary', '#2563eb') : '#2563eb'; ?>;
            --color-secondary: <?php echo $hasThemeLoader ? $themeLoader->getColor('secondary', '#7c3aed') : '#7c3aed'; ?>;
        }
        
        html { scroll-behavior: smooth; }
        
        .gradient-text {
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .gradient-primary {
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px rgba(37, 99, 235, 0.3);
        }
        
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }
        
        .fade-up {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease-out;
        }
        .fade-up.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .process-line::before {
            content: '';
            position: absolute;
            left: 24px;
            top: 60px;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, var(--color-primary), transparent);
        }
        
        .faq-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        .faq-content.open {
            max-height: 500px;
        }
        
        <?php if ($hasThemeLoader): echo $themeLoader->getCustomCss(); endif; ?>
    </style>
    
    <?php if (!empty($meta_description ?? $page['meta_description'])): ?>
    <meta name="description" content="<?php echo esc_attr($meta_description ?? $page['meta_description']); ?>">
    <?php endif; ?>
    <?php if (!empty($meta_keywords ?? $page['meta_keywords'])): ?>
    <meta name="keywords" content="<?php echo esc_attr($meta_keywords ?? $page['meta_keywords']); ?>">
    <?php endif; ?>
</head>
<body class="font-sans text-gray-900 antialiased">
    
    <?php 
    // Header
    if ($hasThemeLoader) {
        echo $themeLoader->renderSnippet('header', [
            'title' => $title ?? $page['title'],
            'current_page' => 'page'
        ]);
    }
    ?>

    <main>
        <!-- Hero Section -->
        <section class="relative overflow-hidden bg-gradient-to-br from-gray-50 via-white to-blue-50 py-20 lg:py-32">
            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 w-1/3 h-full bg-gradient-to-l from-primary/5 to-transparent"></div>
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-primary/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-secondary/10 rounded-full blur-3xl"></div>
            
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                    <!-- Content -->
                    <div class="fade-up">
                        <?php if (!empty($heroSubtitle)): ?>
                        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-medium bg-primary/10 text-primary mb-6">
                            <span class="material-symbols-outlined text-lg mr-2">auto_awesome</span>
                            <?php echo esc_html($heroSubtitle); ?>
                        </span>
                        <?php endif; ?>
                        
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-gray-900 mb-6 leading-tight">
                            <?php echo esc_html($page['title']); ?>
                        </h1>
                        
                        <?php if (!empty($page['excerpt'])): ?>
                        <p class="text-lg lg:text-xl text-gray-600 leading-relaxed mb-8 max-w-xl">
                            <?php echo esc_html($page['excerpt']); ?>
                        </p>
                        <?php endif; ?>
                        
                        <div class="flex flex-col sm:flex-row gap-4">
                            <a href="<?php echo esc_url($ctaButtonLink); ?>" class="btn-primary inline-flex items-center justify-center px-8 py-4 rounded-xl text-white font-semibold text-lg">
                                <?php echo esc_html($ctaButtonText); ?>
                                <span class="material-symbols-outlined ml-2">arrow_forward</span>
                            </a>
                            <a href="#features" class="inline-flex items-center justify-center px-8 py-4 rounded-xl border-2 border-gray-200 text-gray-700 font-semibold text-lg hover:border-primary hover:text-primary transition-all">
                                Detayları İncele
                                <span class="material-symbols-outlined ml-2">expand_more</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Hero Image -->
                    <?php if (!empty($heroImage)): ?>
                    <div class="fade-up relative" style="animation-delay: 0.2s;">
                        <div class="relative rounded-2xl overflow-hidden shadow-2xl">
                            <img src="<?php echo esc_url($heroImage); ?>" alt="<?php echo esc_attr($page['title']); ?>" class="w-full h-auto object-cover aspect-[4/3]">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                        </div>
                        <!-- Floating Badge -->
                        <div class="absolute -bottom-6 -left-6 bg-white rounded-xl shadow-xl p-4 flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-primary to-secondary flex items-center justify-center">
                                <span class="material-symbols-outlined text-white text-2xl">verified</span>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">Profesyonel Hizmet</p>
                                <p class="text-sm text-gray-500">Uzman Ekip</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <?php if (!empty($serviceFeatures)): ?>
        <section id="features" class="py-20 lg:py-28 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16 fade-up">
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
                        Hizmet <span class="gradient-text">Özellikleri</span>
                    </h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        Size sunduğumuz kapsamlı çözümlerimizi keşfedin.
                    </p>
                </div>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($serviceFeatures as $index => $feature): ?>
                    <div class="fade-up card-hover bg-gray-50 rounded-2xl p-8 border border-gray-100" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                        <?php if (!empty($feature['icon'])): ?>
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center mb-6">
                            <span class="material-symbols-outlined text-white text-2xl"><?php echo esc_html($feature['icon']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($feature['title'])): ?>
                        <h3 class="text-xl font-bold text-gray-900 mb-3"><?php echo esc_html($feature['title']); ?></h3>
                        <?php endif; ?>
                        
                        <?php if (!empty($feature['description'])): ?>
                        <p class="text-gray-600 leading-relaxed"><?php echo esc_html($feature['description']); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Content Section -->
        <?php if (!empty($page['content'])): ?>
        <section class="py-20 lg:py-28 bg-gray-50">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="fade-up prose prose-lg max-w-none">
                    <div class="bg-white rounded-2xl p-8 lg:p-12 shadow-sm border border-gray-100">
                        <?php echo $page['content']; ?>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Process Section -->
        <?php if (!empty($processSteps)): ?>
        <section class="py-20 lg:py-28 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16 fade-up">
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
                        Çalışma <span class="gradient-text">Sürecimiz</span>
                    </h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        Projelerinizi nasıl hayata geçirdiğimizi adım adım inceleyin.
                    </p>
                </div>
                
                <div class="max-w-3xl mx-auto">
                    <?php foreach ($processSteps as $index => $step): ?>
                    <div class="fade-up relative flex gap-6 pb-12 <?php echo $index < count($processSteps) - 1 ? 'process-line' : ''; ?>" style="animation-delay: <?php echo $index * 0.15; ?>s;">
                        <!-- Step Number -->
                        <div class="flex-shrink-0 relative z-10">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-bold text-lg shadow-lg">
                                <?php echo $index + 1; ?>
                            </div>
                        </div>
                        
                        <!-- Step Content -->
                        <div class="flex-1 pt-1">
                            <?php if (!empty($step['title'])): ?>
                            <h3 class="text-xl font-bold text-gray-900 mb-2"><?php echo esc_html($step['title']); ?></h3>
                            <?php endif; ?>
                            
                            <?php if (!empty($step['description'])): ?>
                            <p class="text-gray-600 leading-relaxed"><?php echo esc_html($step['description']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Advantages Section -->
        <?php if (!empty($advantages)): ?>
        <section class="py-20 lg:py-28 bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16 fade-up">
                    <h2 class="text-3xl lg:text-4xl font-bold mb-4">
                        Neden <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-400">Bizi Tercih Etmelisiniz?</span>
                    </h2>
                    <p class="text-lg text-gray-400 max-w-2xl mx-auto">
                        Farkımızı ortaya koyan özelliklerimiz.
                    </p>
                </div>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($advantages as $index => $advantage): ?>
                    <div class="fade-up text-center p-6 rounded-2xl bg-white/5 backdrop-blur border border-white/10 hover:bg-white/10 transition-all" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                        <?php if (!empty($advantage['icon'])): ?>
                        <div class="w-16 h-16 mx-auto rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-white text-3xl"><?php echo esc_html($advantage['icon']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($advantage['value'])): ?>
                        <p class="text-3xl font-bold text-white mb-1"><?php echo esc_html($advantage['value']); ?></p>
                        <?php endif; ?>
                        
                        <?php if (!empty($advantage['label'])): ?>
                        <p class="text-gray-400"><?php echo esc_html($advantage['label']); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- FAQ Section -->
        <?php if (!empty($faqs)): ?>
        <section class="py-20 lg:py-28 bg-white">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12 fade-up">
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
                        Sıkça Sorulan <span class="gradient-text">Sorular</span>
                    </h2>
                    <p class="text-lg text-gray-600">
                        Merak ettiğiniz tüm sorulara cevap bulun.
                    </p>
                </div>
                
                <div class="space-y-4">
                    <?php foreach ($faqs as $index => $faq): ?>
                    <div class="fade-up faq-item border border-gray-200 rounded-xl overflow-hidden" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                        <button class="faq-toggle w-full flex items-center justify-between p-6 text-left hover:bg-gray-50 transition-colors">
                            <span class="font-semibold text-gray-900 pr-4"><?php echo esc_html($faq['question'] ?? ''); ?></span>
                            <span class="material-symbols-outlined text-gray-400 faq-icon transition-transform">expand_more</span>
                        </button>
                        <div class="faq-content">
                            <div class="px-6 pb-6 text-gray-600">
                                <?php echo esc_html($faq['answer'] ?? ''); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Related Services -->
        <?php if (!empty($relatedServices)): ?>
        <section class="py-20 lg:py-28 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12 fade-up">
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
                        İlgili <span class="gradient-text">Hizmetler</span>
                    </h2>
                </div>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($relatedServices as $index => $service): ?>
                    <a href="<?php echo esc_url($service['link'] ?? '#'); ?>" class="fade-up card-hover group block bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                        <?php if (!empty($service['image'])): ?>
                        <div class="aspect-video overflow-hidden">
                            <img src="<?php echo esc_url($service['image']); ?>" alt="<?php echo esc_attr($service['title'] ?? ''); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <?php endif; ?>
                        <div class="p-6">
                            <?php if (!empty($service['title'])): ?>
                            <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-primary transition-colors"><?php echo esc_html($service['title']); ?></h3>
                            <?php endif; ?>
                            <?php if (!empty($service['description'])): ?>
                            <p class="text-gray-600"><?php echo esc_html($service['description']); ?></p>
                            <?php endif; ?>
                            <span class="inline-flex items-center text-primary font-medium mt-4">
                                İncele
                                <span class="material-symbols-outlined ml-1 group-hover:translate-x-1 transition-transform">arrow_forward</span>
                            </span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- CTA Section -->
        <section class="py-20 lg:py-28 bg-gradient-to-br from-primary to-secondary relative overflow-hidden">
            <!-- Decorative -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 left-0 w-full h-full" style="background-image: url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 100 100\"><circle cx=\"50\" cy=\"50\" r=\"2\" fill=\"white\"/></svg>'); background-size: 50px 50px;"></div>
            </div>
            
            <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="fade-up">
                    <h2 class="text-3xl lg:text-5xl font-bold text-white mb-6">
                        <?php echo esc_html($ctaTitle); ?>
                    </h2>
                    <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                        <?php echo esc_html($ctaDescription); ?>
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="<?php echo esc_url($ctaButtonLink); ?>" class="inline-flex items-center justify-center px-8 py-4 rounded-xl bg-white text-primary font-semibold text-lg hover:bg-gray-100 transition-all shadow-xl hover:shadow-2xl hover:-translate-y-1">
                            <?php echo esc_html($ctaButtonText); ?>
                            <span class="material-symbols-outlined ml-2">arrow_forward</span>
                        </a>
                        <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', get_option('contact_phone', ''))); ?>" class="inline-flex items-center justify-center px-8 py-4 rounded-xl border-2 border-white/30 text-white font-semibold text-lg hover:bg-white/10 transition-all">
                            <span class="material-symbols-outlined mr-2">phone</span>
                            Hemen Ara
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php 
    // Footer
    if ($hasThemeLoader) {
        echo $themeLoader->renderSnippet('footer');
    }
    ?>

    <?php if ($hasThemeLoader): ?>
        <?php if (file_exists($themeLoader->getThemePath() . '/assets/js/theme.js')): ?>
        <script src="<?php echo $themeLoader->getJsUrl(); ?>"></script>
        <?php endif; ?>
        <script><?php echo $themeLoader->getCustomJs(); ?></script>
    <?php endif; ?>

    <script>
    // Fade-up animations on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);

    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));

    // FAQ Toggle
    document.querySelectorAll('.faq-toggle').forEach(button => {
        button.addEventListener('click', () => {
            const content = button.nextElementSibling;
            const icon = button.querySelector('.faq-icon');
            const isOpen = content.classList.contains('open');
            
            // Close all
            document.querySelectorAll('.faq-content').forEach(c => c.classList.remove('open'));
            document.querySelectorAll('.faq-icon').forEach(i => i.style.transform = 'rotate(0deg)');
            
            // Open clicked if was closed
            if (!isOpen) {
                content.classList.add('open');
                icon.style.transform = 'rotate(180deg)';
            }
        });
    });
    </script>
</body>
</html>
