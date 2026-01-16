<?php
/**
 * Starter Theme - Contact Page Template
 * İletişim sayfası şablonu - Customize ayarlarına bağlı
 */

// ThemeLoader yükle
require_once __DIR__ . '/../../core/ThemeLoader.php';
$themeLoader = ThemeLoader::getInstance();

// Functions dosyasını yükle (the_form fonksiyonu için)
if (!function_exists('the_form')) {
    require_once __DIR__ . '/../../includes/functions.php';
}

// Form component'ini yükle
$formComponentPath = __DIR__ . '/../../app/views/frontend/components/form.php';
if (file_exists($formComponentPath)) {
    require_once $formComponentPath;
}

// Site ve şirket bilgileri
$siteName = get_option('site_name', 'Site Adı');
$companyName = get_option('company_name', $siteName);
$companyEmail = get_option('company_email', get_option('contact_email', ''));
$companyPhone = get_option('company_phone', get_option('contact_phone', ''));
$companyAddress = get_option('company_address', get_option('contact_address', ''));

// Sosyal medya linkleri
$socialLinks = [
    'facebook' => ['url' => get_option('social_facebook', ''), 'icon' => 'fab fa-facebook-f', 'label' => 'Facebook'],
    'instagram' => ['url' => get_option('social_instagram', ''), 'icon' => 'fab fa-instagram', 'label' => 'Instagram'],
    'twitter' => ['url' => get_option('social_twitter', ''), 'icon' => 'fab fa-x-twitter', 'label' => 'X (Twitter)'],
    'linkedin' => ['url' => get_option('social_linkedin', ''), 'icon' => 'fab fa-linkedin-in', 'label' => 'LinkedIn'],
];

// Aktif sosyal medya linklerini filtrele
$activeSocials = array_filter($socialLinks, fn($s) => !empty($s['url']));

// ThemeManager'ı yükle (customize ayarları için)
$contactPageSections = [];
if (class_exists('ThemeManager')) {
    try {
        $themeManager = ThemeManager::getInstance();
        $activeTheme = $themeManager->getActiveTheme();
        $themeId = $activeTheme['id'] ?? null;
        
        if ($themeId) {
            $contactSections = $themeManager->getPageSections('contact', $themeId) ?? [];
            foreach ($contactSections as $section) {
                $sectionId = $section['section_id'] ?? '';
                if ($sectionId) {
                    $sectionSettings = [];
                    if (isset($section['settings'])) {
                        if (is_array($section['settings'])) {
                            $sectionSettings = $section['settings'];
                        } else {
                            $decoded = json_decode($section['settings'], true);
                            $sectionSettings = is_array($decoded) ? $decoded : [];
                        }
                    }
                    
                    $contactPageSections[$sectionId] = array_merge(
                        $sectionSettings,
                        ['enabled' => ($section['is_active'] ?? 1) == 1]
                    );
                    $contactPageSections[$sectionId]['title'] = $section['title'] ?? '';
                    $contactPageSections[$sectionId]['subtitle'] = $section['subtitle'] ?? '';
                    $contactPageSections[$sectionId]['content'] = $section['content'] ?? '';
                    if (isset($section['items'])) {
                        $items = is_array($section['items']) ? $section['items'] : json_decode($section['items'] ?? '[]', true);
                        $contactPageSections[$sectionId]['items'] = is_array($items) ? $items : [];
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log("Contact page sections error: " . $e->getMessage());
    }
}

// Hero section ayarları
$heroTitle = $contactPageSections['hero']['title'] ?? __('Bize Ulaşın');
$heroSubtitle = $contactPageSections['hero']['subtitle'] ?? __('Sorularınız için bizimle iletişime geçin. Size yardımcı olmaktan mutluluk duyarız.');
$heroEnabled = $contactPageSections['hero']['enabled'] ?? true;

// Form section ayarları
$formTitle = $contactPageSections['form']['title'] ?? __('İletişim Formu');
$formDescription = $contactPageSections['form']['description'] ?? __('Aşağıdaki formu doldurarak bize ulaşabilirsiniz.');
$formEnabled = $contactPageSections['form']['enabled'] ?? true;
$formId = $contactPageSections['form']['form_id'] ?? null;

// Form ID varsa, form slug'ını al
$formSlug = 'iletisim'; // Varsayılan
if ($formId) {
    try {
        require_once __DIR__ . '/../../core/Database.php';
        $db = Database::getInstance();
        $form = $db->fetch("SELECT slug FROM forms WHERE id = ?", [$formId]);
        if ($form && isset($form['slug'])) {
            $formSlug = $form['slug'];
        }
    } catch (Exception $e) {
        error_log("Contact form load error: " . $e->getMessage());
    }
}

// Map section ayarları
$mapEmbed = $contactPageSections['map']['embed'] ?? get_option('google_maps_embed', '');
$mapEnabled = $contactPageSections['map']['enabled'] ?? true;

// Why Choose Us section ayarları
$whyChooseUsTitle = $contactPageSections['why-choose-us']['title'] ?? __('Neden Bizi Tercih Etmelisiniz?');
$whyChooseUsItems = $contactPageSections['why-choose-us']['items'] ?? [];
$whyChooseUsEnabled = $contactPageSections['why-choose-us']['enabled'] ?? true;

// Services section ayarları
$servicesTitle = $contactPageSections['services']['title'] ?? __('Hizmetlerimiz');
$servicesDescription = $contactPageSections['services']['description'] ?? __('Size nasıl yardımcı olabiliriz?');
$servicesItems = $contactPageSections['services']['items'] ?? [];
$servicesEnabled = $contactPageSections['services']['enabled'] ?? true;

// Tema renkleri
$primaryColor = $themeLoader->getColor('primary', '#137fec');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($title ?? 'İletişim'); ?></title>
    <?php if (!empty($meta_description)): ?>
    <meta name="description" content="<?php echo esc_attr($meta_description); ?>">
    <?php endif; ?>
    <!-- Inline Font Definitions -->
    <style>
        @font-face {
            font-family: 'Inter';
            src: url('<?php echo ViewRenderer::assetUrl('assets/fonts/inter/inter-400.woff2'); ?>') format('woff2');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Poppins';
            src: url('<?php echo ViewRenderer::assetUrl('assets/fonts/poppins/poppins-600.woff2'); ?>') format('woff2');
            font-weight: 600;
            font-style: normal;
            font-display: swap;
        }
    </style>
    <style>
        :root {
            --color-primary: <?php echo $primaryColor; ?>;
        }
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Poppins', sans-serif; }
        .gradient-primary { background: linear-gradient(135deg, var(--color-primary) 0%, #6366f1 100%); }
        .text-primary { color: var(--color-primary); }
        .bg-primary { background-color: var(--color-primary); }
        .border-primary { border-color: var(--color-primary); }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <!-- Preload Tailwind CSS JS -->
    <link rel="preload" href="<?php echo ViewRenderer::assetUrl('assets/js/tailwind.min.js'); ?>" as="script">
</head>
<body class="antialiased bg-gray-50">
    
    <?php echo $themeLoader->renderSnippet('header', ['title' => 'İletişim', 'current_page' => 'contact']); ?>
    
    <!-- Hero Section -->
    <?php if ($heroEnabled): ?>
    <section class="gradient-primary py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl lg:text-5xl font-bold text-white mb-6"><?php echo esc_html($heroTitle); ?></h1>
            <?php if (!empty($heroSubtitle)): ?>
            <p class="text-xl text-white/80 max-w-2xl mx-auto"><?php echo esc_html($heroSubtitle); ?></p>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Main Content -->
    <section class="py-16 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16">
                
                <!-- Left Column: Contact Info & Why Choose Us & Services -->
                <div class="space-y-12">
                    
                    <!-- İletişim Bilgileri -->
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900 mb-8">İletişim Bilgileri</h2>
                        
                        <div class="space-y-6">
                            <?php if (!empty($companyEmail)): ?>
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-1">E-posta</h3>
                                    <a href="mailto:<?php echo esc_attr($companyEmail); ?>" class="text-gray-600 hover:text-primary transition-colors">
                                        <?php echo esc_html($companyEmail); ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($companyPhone)): ?>
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-1">Telefon</h3>
                                    <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $companyPhone)); ?>" class="text-gray-600 hover:text-primary transition-colors">
                                        <?php echo esc_html($companyPhone); ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($companyAddress)): ?>
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-1">Adres</h3>
                                    <p class="text-gray-600"><?php echo nl2br(esc_html($companyAddress)); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Sosyal Medya -->
                        <?php if (!empty($activeSocials)): ?>
                        <div class="mt-10 pt-10 border-t border-gray-200">
                            <h3 class="font-semibold text-gray-900 mb-4">Bizi Takip Edin</h3>
                            <div class="flex gap-4">
                                <?php foreach ($activeSocials as $key => $social): ?>
                                <a href="<?php echo esc_url($social['url']); ?>" target="_blank" rel="noopener" class="w-10 h-10 bg-gray-100 hover:bg-primary hover:text-white rounded-full flex items-center justify-center text-gray-600 transition-all">
                                    <i class="<?php echo esc_attr($social['icon']); ?>"></i>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Why Choose Us Section -->
                    <?php if ($whyChooseUsEnabled && !empty($whyChooseUsItems)): ?>
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900 mb-6"><?php echo esc_html($whyChooseUsTitle); ?></h2>
                        <ul class="space-y-4">
                            <?php foreach ($whyChooseUsItems as $item): ?>
                                <?php if (!empty($item['text'])): ?>
                                <li class="flex items-start gap-3">
                                    <svg class="w-6 h-6 text-primary flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span class="text-gray-700"><?php echo esc_html($item['text']); ?></span>
                                </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Services Section -->
                    <?php if ($servicesEnabled && !empty($servicesItems)): ?>
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900 mb-3"><?php echo esc_html($servicesTitle); ?></h2>
                        <?php if (!empty($servicesDescription)): ?>
                        <p class="text-gray-600 mb-6"><?php echo esc_html($servicesDescription); ?></p>
                        <?php endif; ?>
                        <div class="grid grid-cols-2 gap-4">
                            <?php foreach ($servicesItems as $service): ?>
                                <?php if (!empty($service['title'])): ?>
                                <a href="<?php echo !empty($service['link']) ? esc_url($service['link']) : '#'; ?>" class="flex flex-col items-center p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition-all border border-gray-100 hover:border-primary/20">
                                    <?php if (!empty($service['icon'])): ?>
                                    <span class="material-symbols-outlined text-4xl text-primary mb-3"><?php echo esc_html($service['icon']); ?></span>
                                    <?php endif; ?>
                                    <span class="font-semibold text-gray-900 text-center"><?php echo esc_html($service['title']); ?></span>
                                </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                </div>
                
                <!-- Right Column: Contact Form -->
                <?php if ($formEnabled): ?>
                <div>
                    <div class="bg-white rounded-2xl shadow-lg p-8 sticky top-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2"><?php echo esc_html($formTitle); ?></h2>
                        <?php if (!empty($formDescription)): ?>
                        <p class="text-gray-600 mb-6"><?php echo esc_html($formDescription); ?></p>
                        <?php endif; ?>
                        
                        <?php if (!empty($_SESSION['contact_message'])): ?>
                        <div class="mb-6 p-4 rounded-lg <?php echo ($_SESSION['contact_message_type'] ?? '') === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200'; ?>">
                            <?php echo esc_html($_SESSION['contact_message']); ?>
                        </div>
                        <?php unset($_SESSION['contact_message'], $_SESSION['contact_message_type']); ?>
                        <?php endif; ?>
                        
                        <div class="space-y-5">
                            <?php 
                            if (function_exists('the_form')) {
                                the_form($formSlug);
                            } else if (function_exists('cms_form')) {
                                echo cms_form($formSlug);
                            } else {
                                echo '<p class="text-gray-500 text-sm">Lütfen admin panelinden bir form seçin.</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <!-- Map Section -->
    <?php if ($mapEnabled && !empty($mapEmbed)): ?>
    <section class="pb-16 lg:pb-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl overflow-hidden shadow-lg">
                <?php echo $mapEmbed; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <?php echo $themeLoader->renderSnippet('footer'); ?>
    
    <!-- Tailwind CSS -->
    <script src="<?php echo ViewRenderer::assetUrl('assets/js/tailwind.min.js'); ?>" defer></script>
</body>
</html>
