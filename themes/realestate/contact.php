<?php
/**
 * Real Estate Theme - Contact Page
 * İletişim sayfası template'i
 * 
 * NOT: Bu dosya artık modül yapısına taşınmıştır.
 * Modül: themes/realestate/modules/contact/
 * Modül route'u öncelikli olduğu için bu dosya sadece fallback olarak kullanılır.
 * 
 * NOT: Modül kontrolü HomeController::contact() metodunda yapılıyor.
 * Bu dosya sadece modül yüklenmediğinde fallback olarak kullanılır.
 */

// ThemeLoader'ı yükle (eğer yüklenmemişse)
if (!isset($themeLoader) || !$themeLoader) {
    if (!class_exists('ThemeLoader')) {
        require_once __DIR__ . '/../../core/ThemeLoader.php';
    }
    $themeLoader = ThemeLoader::getInstance();
}

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
$companyCity = get_option('company_city', '');

// Sosyal medya linkleri
$socialLinks = [
    'facebook' => ['url' => get_option('social_facebook', ''), 'icon' => 'fab fa-facebook-f', 'label' => 'Facebook', 'color' => '#1877f2'],
    'instagram' => ['url' => get_option('social_instagram', ''), 'icon' => 'fab fa-instagram', 'label' => 'Instagram', 'color' => '#e4405f'],
    'twitter' => ['url' => get_option('social_twitter', ''), 'icon' => 'fab fa-x-twitter', 'label' => 'X (Twitter)', 'color' => '#000000'],
    'linkedin' => ['url' => get_option('social_linkedin', ''), 'icon' => 'fab fa-linkedin-in', 'label' => 'LinkedIn', 'color' => '#0a66c2'],
    'youtube' => ['url' => get_option('social_youtube', ''), 'icon' => 'fab fa-youtube', 'label' => 'YouTube', 'color' => '#ff0000'],
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
$heroTitle = $contactPageSections['hero']['title'] ?? __('Hayalinizdeki Mülkü Bulalım');
$heroSubtitle = $contactPageSections['hero']['subtitle'] ?? __('Uzman ekibimiz, mülk satın alma, satış veya kiralama işlemlerinizde size yardımcı olmak için burada. Hemen iletişime geçin!');
$heroEnabled = $contactPageSections['hero']['enabled'] ?? true;
// Background image - hem background_image hem hero_image olarak kontrol et
$heroBackgroundImage = $contactPageSections['hero']['background_image'] ?? $contactPageSections['hero']['hero_image'] ?? '';

// Primary color'ı al (gradient için)
$primaryColor = '#1e40af'; // Varsayılan
$secondaryColor = '#1e293b'; // Varsayılan
if (class_exists('ThemeLoader')) {
    $themeLoaderInstance = ThemeLoader::getInstance();
    $primaryColor = $themeLoaderInstance->getColor('primary', '#1e40af');
    $secondaryColor = $themeLoaderInstance->getColor('secondary', '#1e293b');
}

// Form section ayarları
$formTitle = $contactPageSections['form']['title'] ?? __('Mülk Talebinizi İletin');
$formDescription = $contactPageSections['form']['description'] ?? __('Aradığınız mülk özelliklerini belirtin, size en uygun seçenekleri sunalım.');
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

// Services section ayarları
$servicesTitle = $contactPageSections['services']['title'] ?? __('Hizmetlerimiz');
$servicesDescription = $contactPageSections['services']['description'] ?? __('Size nasıl yardımcı olabiliriz?');
$servicesItems = $contactPageSections['services']['items'] ?? [
    ['title' => 'Satılık Mülk', 'icon' => 'home', 'link' => ''],
    ['title' => 'Kiralık Mülk', 'icon' => 'apartment', 'link' => ''],
    ['title' => 'Mülk Değerleme', 'icon' => 'assessment', 'link' => ''],
    ['title' => 'Danışmanlık', 'icon' => 'people', 'link' => '']
];
$servicesEnabled = $contactPageSections['services']['enabled'] ?? true;

// Map section ayarları
$mapEmbed = $contactPageSections['map']['embed'] ?? get_option('google_maps_embed', '');
$mapEnabled = $contactPageSections['map']['enabled'] ?? true;

// Sayfa içeriğini buffer'a al
// Eğer output buffer zaten başlatılmışsa, temizle
if (ob_get_level() > 0) {
    ob_end_clean();
}
ob_start();
?>

<!-- Hero Section -->
<?php if ($heroEnabled): ?>
<style>
    .contact-hero-section {
        position: relative;
        padding: 5rem 1rem;
        color: white;
        overflow: hidden;
        min-height: 500px;
        display: flex;
        align-items: center;
    }
    @media (min-width: 1024px) {
        .contact-hero-section {
            padding: 7rem 1.5rem;
            min-height: 600px;
        }
    }
    .contact-hero-bg {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 0;
    }
    .contact-hero-gradient {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 0;
        background: linear-gradient(135deg, <?php echo esc_attr($primaryColor); ?> 0%, <?php echo esc_attr($secondaryColor); ?> 100%);
    }
    .contact-hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.4);
        z-index: 1;
    }
    .contact-hero-pattern {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        opacity: 0.1;
        z-index: 1;
        background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.1"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');
    }
    .contact-hero-content {
        position: relative;
        z-index: 10;
        max-width: 1280px;
        margin: 0 auto;
        width: 100%;
        padding: 0 1rem;
    }
    @media (min-width: 1024px) {
        .contact-hero-content {
            padding: 0 1.5rem;
        }
    }
    .contact-hero-inner {
        max-width: 56rem;
        margin: 0 auto;
        text-align: center;
    }
    .contact-hero-title {
        font-size: 2.25rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        line-height: 1.2;
        color: white;
    }
    @media (min-width: 1024px) {
        .contact-hero-title {
            font-size: 3.75rem;
        }
    }
    .contact-hero-subtitle {
        font-size: 1.25rem;
        margin-bottom: 2rem;
        line-height: 1.6;
        color: rgba(255, 255, 255, 0.9);
    }
    @media (min-width: 1024px) {
        .contact-hero-subtitle {
            font-size: 1.5rem;
        }
    }
    .contact-hero-stats {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 1.5rem;
        margin-top: 2.5rem;
    }
    @media (min-width: 1024px) {
        .contact-hero-stats {
            gap: 2rem;
        }
    }
    .contact-hero-stat {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .contact-hero-stat-icon {
        width: 3rem;
        height: 3rem;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .contact-hero-stat-icon svg {
        width: 1.5rem;
        height: 1.5rem;
    }
    .contact-hero-stat-text {
        font-size: 1.125rem;
        font-weight: 500;
        color: white;
    }
</style>
<section class="contact-hero-section">
    <!-- Background Image -->
    <?php if ($heroBackgroundImage): ?>
    <img src="<?php echo esc_url($heroBackgroundImage); ?>" alt="<?php echo esc_attr($heroTitle); ?>" class="contact-hero-bg">
    <?php else: ?>
    <!-- Default gradient background -->
    <div class="contact-hero-gradient"></div>
    <?php endif; ?>
    
    <!-- Dark Overlay -->
    <div class="contact-hero-overlay"></div>
    
    <!-- Background Pattern -->
    <div class="contact-hero-pattern"></div>
    
    <div class="contact-hero-content">
        <div class="contact-hero-inner">
            <h1 class="contact-hero-title">
                <?php echo esc_html($heroTitle); ?>
            </h1>
            <p class="contact-hero-subtitle">
                <?php echo esc_html($heroSubtitle); ?>
            </p>
            
            <!-- Quick Stats -->
            <div class="contact-hero-stats">
                <div class="contact-hero-stat">
                    <div class="contact-hero-stat-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="contact-hero-stat-text"><?php echo esc_html(__('24 Saat İçinde Yanıt')); ?></span>
                </div>
                <div class="contact-hero-stat">
                    <div class="contact-hero-stat-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <span class="contact-hero-stat-text"><?php echo esc_html(__('500+ Mülk Seçeneği')); ?></span>
                </div>
                <div class="contact-hero-stat">
                    <div class="contact-hero-stat-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <span class="contact-hero-stat-text"><?php echo esc_html(__('Uzman Danışmanlar')); ?></span>
                </div>
                <div class="contact-hero-stat">
                    <div class="contact-hero-stat-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <span class="contact-hero-stat-text"><?php echo esc_html(__('Güvenli İşlem')); ?></span>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Main Content -->
<style>
    .contact-main-section {
        padding: 4rem 1rem;
        background-color: #f8fafc;
    }
    @media (min-width: 1024px) {
        .contact-main-section {
            padding: 6rem 1.5rem;
        }
    }
    .contact-container {
        max-width: 1280px;
        margin: 0 auto;
        width: 100%;
    }
    .contact-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
        align-items: start;
    }
    @media (min-width: 1024px) {
        .contact-grid {
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
        }
    }
    .contact-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        padding: 2rem;
    }
    @media (min-width: 1024px) {
        .contact-card {
            padding: 2.5rem;
        }
    }
    .contact-info-section {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    .contact-services-section {
        margin-top: 3rem;
        grid-column: 1 / -1;
    }
    @media (min-width: 1024px) {
        .contact-services-section {
            margin-top: 4rem;
        }
    }
    .contact-services-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        padding: 2rem;
    }
    @media (min-width: 1024px) {
        .contact-services-card {
            padding: 2.5rem;
        }
    }
    .contact-services-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    .contact-services-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 4rem;
        height: 4rem;
        background: rgba(30, 64, 175, 0.1);
        border-radius: 1rem;
        margin: 0 auto 1rem;
    }
    .contact-services-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.75rem;
    }
    @media (min-width: 1024px) {
        .contact-services-title {
            font-size: 1.875rem;
        }
    }
    .contact-services-subtitle {
        font-size: 1rem;
        color: #64748b;
        max-width: 32rem;
        margin: 0 auto;
    }
    .contact-services-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    @media (min-width: 1024px) {
        .contact-services-grid {
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
        }
    }
    .contact-service-item {
        padding: 1.25rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 0.75rem;
        text-align: center;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        text-decoration: none;
        display: block;
        color: inherit;
    }
    .contact-service-item:hover {
        background: linear-gradient(135deg, rgba(30, 64, 175, 0.05) 0%, rgba(30, 64, 175, 0.1) 100%);
        border-color: rgba(30, 64, 175, 0.3);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }
    .contact-service-icon {
        font-size: 2.25rem;
        color: #1e40af;
        margin: 0 auto 0.75rem;
        display: block;
        transition: transform 0.3s ease;
    }
    .contact-service-item:hover .contact-service-icon {
        transform: scale(1.1);
    }
    .contact-service-text {
        font-size: 0.875rem;
        font-weight: 600;
        color: #1e293b;
        transition: color 0.3s ease;
    }
    .contact-service-item:hover .contact-service-text {
        color: #1e40af;
    }
</style>
<section class="contact-main-section">
    <div class="contact-container">
        <div class="contact-grid">
            
            <!-- Contact Form -->
            <?php if ($formEnabled): ?>
            <div class="contact-card" id="contact-form-container">
                <style>
                    .contact-form-header {
                        margin-bottom: 2rem;
                    }
                    .contact-form-title {
                        font-size: 1.875rem;
                        font-weight: 700;
                        color: #1e293b;
                        margin-bottom: 0.75rem;
                    }
                    @media (min-width: 1024px) {
                        .contact-form-title {
                            font-size: 2.25rem;
                        }
                    }
                    .contact-form-description {
                        color: #64748b;
                        font-size: 1.125rem;
                        line-height: 1.6;
                    }
                </style>
                <div class="contact-form-header">
                    <h2 class="contact-form-title">
                        <?php echo esc_html($formTitle); ?>
                    </h2>
                    <?php if (!empty($formDescription)): ?>
                    <p class="contact-form-description">
                        <?php echo esc_html($formDescription); ?>
                    </p>
                    <?php endif; ?>
                </div>
                
                <div class="space-y-5">
                    <style>
                        /* Form başlığı üstündeki icon container'ı gizle */
                        #contact-form-container .w-16.h-16.bg-primary.rounded-2xl.flex.items-center.justify-center.mb-6.shadow-lg {
                            display: none !important;
                        }
                        
                        /* Form Tasarım İyileştirmeleri */
                        #contact-form-container .cms-form {
                            background: transparent;
                        }
                        #contact-form-container .cms-form .form-field {
                            margin-bottom: 1.75rem;
                        }
                        #contact-form-container .cms-form .field-label {
                            display: block;
                            font-weight: 600;
                            color: #1e293b;
                            margin-bottom: 0.625rem;
                            font-size: 0.9375rem;
                            letter-spacing: 0.01em;
                        }
                        #contact-form-container .cms-form .required-mark {
                            color: #ef4444;
                            margin-left: 0.25rem;
                            font-weight: 700;
                        }
                        #contact-form-container .cms-form input[type="text"],
                        #contact-form-container .cms-form input[type="email"],
                        #contact-form-container .cms-form input[type="tel"],
                        #contact-form-container .cms-form input[type="number"],
                        #contact-form-container .cms-form textarea,
                        #contact-form-container .cms-form select {
                            width: 100%;
                            padding: 1rem 1.25rem;
                            border: 2px solid #e2e8f0;
                            border-radius: 0.875rem;
                            font-size: 1rem;
                            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                            background: #f8fafc;
                            color: #1e293b;
                            font-family: inherit;
                        }
                        #contact-form-container .cms-form input:hover,
                        #contact-form-container .cms-form textarea:hover,
                        #contact-form-container .cms-form select:hover {
                            border-color: #cbd5e1;
                            background: #ffffff;
                        }
                        #contact-form-container .cms-form input:focus,
                        #contact-form-container .cms-form textarea:focus,
                        #contact-form-container .cms-form select:focus {
                            outline: none;
                            border-color: #1e40af;
                            background: #ffffff;
                            box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.1);
                            transform: translateY(-1px);
                        }
                        #contact-form-container .cms-form input::placeholder,
                        #contact-form-container .cms-form textarea::placeholder {
                            color: #94a3b8;
                            opacity: 1;
                        }
                        #contact-form-container .cms-form textarea {
                            min-height: 140px;
                            resize: vertical;
                            line-height: 1.6;
                        }
                        #contact-form-container .cms-form select {
                            cursor: pointer;
                            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
                            background-position: right 0.75rem center;
                            background-repeat: no-repeat;
                            background-size: 1.5em 1.5em;
                            padding-right: 2.5rem;
                            appearance: none;
                        }
                        #contact-form-container .cms-form .submit-button {
                            width: 100%;
                            padding: 1.125rem 2rem;
                            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
                            color: white;
                            font-weight: 600;
                            font-size: 1.0625rem;
                            border: none;
                            border-radius: 0.875rem;
                            cursor: pointer;
                            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            gap: 0.625rem;
                            box-shadow: 0 4px 14px 0 rgba(30, 64, 175, 0.25);
                            margin-top: 0.5rem;
                            letter-spacing: 0.025em;
                        }
                        #contact-form-container .cms-form .submit-button:hover {
                            transform: translateY(-2px);
                            box-shadow: 0 8px 20px 0 rgba(30, 64, 175, 0.35);
                            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
                        }
                        #contact-form-container .cms-form .submit-button:active {
                            transform: translateY(0);
                        }
                        #contact-form-container .cms-form .submit-button:disabled {
                            opacity: 0.6;
                            cursor: not-allowed;
                            transform: none;
                            box-shadow: none;
                        }
                        #contact-form-container .cms-form .field-error input,
                        #contact-form-container .cms-form .field-error textarea,
                        #contact-form-container .cms-form .field-error select {
                            border-color: #ef4444;
                        }
                        #contact-form-container .cms-form .field-error-message {
                            color: #ef4444;
                            font-size: 0.875rem;
                            margin-top: 0.5rem;
                        }
                        #contact-form-container .cms-form .form-success {
                            text-align: center;
                            padding: 2rem;
                            background: #f0fdf4;
                            border: 2px solid #22c55e;
                            border-radius: 0.75rem;
                        }
                        #contact-form-container .cms-form .success-icon {
                            color: #22c55e;
                            margin: 0 auto 1rem;
                        }
                        #contact-form-container .cms-form .success-message {
                            color: #166534;
                            font-weight: 600;
                            font-size: 1.125rem;
                        }
                        #contact-form-container .cms-form .form-error-message {
                            background: #fef2f2;
                            border: 2px solid #ef4444;
                            color: #991b1b;
                            padding: 1rem;
                            border-radius: 0.75rem;
                            margin-top: 1rem;
                        }
                    </style>
                    <?php 
                    // Form render - customize'den seçilen formu göster
                    if (function_exists('the_form')) {
                        the_form($formSlug);
                    } else if (function_exists('cms_form')) {
                        echo cms_form($formSlug);
                    } else {
                        echo '<p class="text-gray-600">İletişim formu yüklenemedi.</p>';
                    }
                    ?>
                </div>
                
                <!-- Real Estate Info Box -->
                <style>
                    .contact-info-box {
                        margin-top: 2rem;
                        padding: 1.5rem;
                        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
                        border-radius: 0.75rem;
                        border: 1px solid #bfdbfe;
                    }
                    .contact-info-box-inner {
                        display: flex;
                        align-items: flex-start;
                        gap: 1rem;
                    }
                    .contact-info-box-icon {
                        width: 3rem;
                        height: 3rem;
                        background: #3b82f6;
                        border-radius: 0.75rem;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        flex-shrink: 0;
                    }
                    .contact-info-box-icon svg {
                        width: 1.5rem;
                        height: 1.5rem;
                        color: white;
                    }
                    .contact-info-box-content {
                        flex: 1;
                    }
                    .contact-info-box-title {
                        font-size: 1.125rem;
                        font-weight: 700;
                        color: #111827;
                        margin-bottom: 0.5rem;
                    }
                    .contact-info-box-list {
                        list-style: none;
                        padding: 0;
                        margin: 0;
                        display: flex;
                        flex-direction: column;
                        gap: 0.5rem;
                    }
                    .contact-info-box-item {
                        display: flex;
                        align-items: flex-start;
                        gap: 0.5rem;
                        font-size: 0.875rem;
                        color: #374151;
                    }
                    .contact-info-box-item svg {
                        width: 1.25rem;
                        height: 1.25rem;
                        color: #3b82f6;
                        flex-shrink: 0;
                        margin-top: 0.125rem;
                    }
                </style>
                <div class="contact-info-box">
                    <div class="contact-info-box-inner">
                        <div class="contact-info-box-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="contact-info-box-content">
                            <h4 class="contact-info-box-title">
                                <?php echo esc_html(__('Neden Bizi Tercih Etmelisiniz?')); ?>
                            </h4>
                            <ul class="contact-info-box-list">
                                <li class="contact-info-box-item">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span><?php echo esc_html(__('500+ aktif mülk seçeneği')); ?></span>
                                </li>
                                <li class="contact-info-box-item">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span><?php echo esc_html(__('Deneyimli ve sertifikalı danışmanlar')); ?></span>
                                </li>
                                <li class="contact-info-box-item">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span><?php echo esc_html(__('Şeffaf fiyatlandırma ve güvenli işlem')); ?></span>
                                </li>
                                <li class="contact-info-box-item">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span><?php echo esc_html(__('7/24 müşteri desteği ve hızlı yanıt')); ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Contact Information -->
            <div class="contact-info-section">
                
                <!-- WhatsApp Quick Contact -->
                <?php 
                $whatsappNumber = get_option('whatsapp_number', $companyPhone);
                if ($whatsappNumber): 
                    $whatsappClean = preg_replace('/[^0-9]/', '', $whatsappNumber);
                    $whatsappUrl = 'https://wa.me/' . $whatsappClean . '?text=' . urlencode(__('Merhaba, emlak danışmanlığı hakkında bilgi almak istiyorum.'));
                ?>
                <a href="<?php echo esc_url($whatsappUrl); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="group block bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 text-white">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold mb-1">
                                <?php echo esc_html(__('WhatsApp ile Hızlı İletişim')); ?>
                            </h3>
                            <p class="text-green-50 text-sm">
                                <?php echo esc_html(__('Anında yanıt almak için WhatsApp\'tan yazın')); ?>
                            </p>
                        </div>
                        <svg class="w-6 h-6 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
                <?php endif; ?>
                
                <!-- Contact Cards -->
                <div class="space-y-4">
                    <?php if ($companyEmail): ?>
                    <a href="mailto:<?php echo esc_attr($companyEmail); ?>" 
                       class="group block bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border-l-4 border-primary">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 bg-primary/10 rounded-xl flex items-center justify-center group-hover:bg-primary transition-colors flex-shrink-0">
                                <svg class="w-7 h-7 text-primary group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">
                                    <?php echo esc_html(__('E-posta Adresimiz')); ?>
                                </h3>
                                <p class="text-lg font-semibold text-secondary group-hover:text-primary transition-colors">
                                    <?php echo esc_html($companyEmail); ?>
                                </p>
                                <p class="text-sm text-gray-500 mt-1">
                                    <?php echo esc_html(__('Bize e-posta gönderin')); ?>
                                </p>
                            </div>
                            <svg class="w-6 h-6 text-gray-400 group-hover:text-primary transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                    <?php endif; ?>

                    <?php if ($companyPhone): ?>
                    <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $companyPhone)); ?>" 
                       class="group block bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border-l-4 border-accent">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 bg-accent/10 rounded-xl flex items-center justify-center group-hover:bg-accent transition-colors flex-shrink-0">
                                <svg class="w-7 h-7 text-accent group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">
                                    <?php echo esc_html(__('Telefon Numaramız')); ?>
                                </h3>
                                <p class="text-lg font-semibold text-secondary group-hover:text-accent transition-colors">
                                    <?php echo esc_html($companyPhone); ?>
                                </p>
                                <p class="text-sm text-gray-500 mt-1">
                                    <?php echo esc_html(__('Bizi hemen arayın')); ?>
                                </p>
                            </div>
                            <svg class="w-6 h-6 text-gray-400 group-hover:text-accent transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                    <?php endif; ?>

                    <?php if ($companyAddress): ?>
                    <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-secondary">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 bg-secondary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-7 h-7 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">
                                    <?php echo esc_html(__('Ofis Adresimiz')); ?>
                                </h3>
                                <p class="text-lg font-semibold text-secondary">
                                    <?php echo esc_html($companyAddress); ?>
                                </p>
                                <?php if ($companyCity): ?>
                                <p class="text-sm text-gray-500 mt-1 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <?php echo esc_html($companyCity); ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Social Media -->
                <?php if (!empty($activeSocials)): ?>
                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-secondary">
                                <?php echo esc_html(__('Sosyal Medya')); ?>
                            </h3>
                            <p class="text-sm text-gray-500">
                                <?php echo esc_html(__('Bizi sosyal medyada takip edin')); ?>
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <?php foreach ($activeSocials as $key => $social): ?>
                        <a href="<?php echo esc_url($social['url']); ?>" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="w-12 h-12 rounded-xl flex items-center justify-center bg-gray-100 hover:bg-primary text-gray-600 hover:text-white transition-all duration-300 hover:scale-110 hover:shadow-lg"
                           style="--hover-color: <?php echo $social['color']; ?>"
                           title="<?php echo esc_attr($social['label']); ?>">
                            <i class="<?php echo esc_attr($social['icon']); ?>"></i>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Working Hours -->
                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-accent/10 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-secondary">
                                <?php echo esc_html(__('Çalışma Saatleri')); ?>
                            </h3>
                            <p class="text-sm text-gray-500">
                                <?php echo esc_html(__('Müsaitlik durumumuz')); ?>
                            </p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span class="font-semibold text-secondary"><?php echo esc_html(__('Pazartesi - Cuma')); ?></span>
                            </div>
                            <span class="px-4 py-1 bg-green-100 text-green-700 rounded-lg font-semibold text-sm">
                                09:00 - 18:00
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="font-semibold text-secondary"><?php echo esc_html(__('Cumartesi')); ?></span>
                            </div>
                            <span class="px-4 py-1 bg-green-100 text-green-700 rounded-lg font-semibold text-sm">
                                10:00 - 16:00
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="font-semibold text-secondary"><?php echo esc_html(__('Pazar')); ?></span>
                            </div>
                            <span class="px-4 py-1 bg-yellow-100 text-yellow-700 rounded-lg font-semibold text-sm">
                                10:00 - 14:00
                            </span>
                        </div>
                    </div>
                    <div class="mt-4 p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                        <p class="text-sm text-blue-800">
                            <strong><?php echo esc_html(__('Not:')); ?></strong> 
                            <?php echo esc_html(__('Acil durumlar için 7/24 WhatsApp desteğimiz mevcuttur.')); ?>
                        </p>
                    </div>
                </div>

            </div>
        </div>
        
        <!-- Quick Services Section - Full Width Below Main Grid -->
        <?php if ($servicesEnabled && !empty($servicesItems)): ?>
        <div class="contact-services-section">
            <div class="contact-services-card">
                <div class="contact-services-header">
                    <div class="contact-services-icon">
                        <span class="material-symbols-outlined" style="font-size: 2rem; color: #1e40af;">check_circle</span>
                    </div>
                    <h3 class="contact-services-title">
                        <?php echo esc_html($servicesTitle); ?>
                    </h3>
                    <?php if (!empty($servicesDescription)): ?>
                    <p class="contact-services-subtitle">
                        <?php echo esc_html($servicesDescription); ?>
                    </p>
                    <?php endif; ?>
                </div>
                <div class="contact-services-grid">
                    <?php foreach ($servicesItems as $service): ?>
                        <?php if (!empty($service['title'])): ?>
                        <?php 
                        $serviceLink = !empty($service['link']) ? $service['link'] : '#';
                        $serviceIcon = $service['icon'] ?? 'star';
                        ?>
                        <a href="<?php echo esc_url($serviceLink); ?>" class="contact-service-item">
                            <span class="material-symbols-outlined contact-service-icon"><?php echo esc_html($serviceIcon); ?></span>
                            <p class="contact-service-text"><?php echo esc_html($service['title']); ?></p>
                        </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Map Section -->
<?php if ($mapEnabled && ($mapEmbed || $companyAddress)): ?>
<section class="py-16 bg-gray-100">
    <div class="container mx-auto px-4 lg:px-6">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="h-96 lg:h-[500px]">
                <?php if ($mapEmbed): ?>
                    <?php echo $mapEmbed; ?>
                <?php else: ?>
                    <iframe 
                        src="https://maps.google.com/maps?q=<?php echo urlencode($companyAddress . ' ' . $companyCity); ?>&output=embed"
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade"
                        class="w-full h-full">
                    </iframe>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
$content = ob_get_clean();

// Gerekli değişkenlerin tanımlı olduğundan emin ol
if (!isset($title)) {
    $title = 'İletişim';
}
if (!isset($meta_description)) {
    $meta_description = 'Bizimle iletişime geçin';
}
if (!isset($current_page)) {
    $current_page = 'contact';
}
if (!isset($sections)) {
    $sections = [];
}

// Layout'u kullan
try {
    require_once __DIR__ . '/layouts/default.php';
} catch (Exception $e) {
    error_log('Contact page layout error: ' . $e->getMessage());
    // Fallback: Basit HTML göster
    echo '<!DOCTYPE html><html><head><title>İletişim</title></head><body>';
    echo '<h1>İletişim Sayfası</h1>';
    echo '<p>Sayfa yüklenirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.</p>';
    echo '<p>Hata: ' . esc_html($e->getMessage()) . '</p>';
    echo '</body></html>';
}
?>