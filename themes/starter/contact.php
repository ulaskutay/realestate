<?php
/**
 * Starter Theme - Contact Page Template
 * İletişim sayfası şablonu
 */

// ThemeLoader yükle
require_once __DIR__ . '/../../core/ThemeLoader.php';
$themeLoader = ThemeLoader::getInstance();

// Sayfa verileri
$pageTitle = $page['title'] ?? 'İletişim';
$pageExcerpt = $page['excerpt'] ?? '';
$customFields = $customFields ?? [];

// İletişim bilgileri
$contactEmail = $customFields['contact_email'] ?? get_option('admin_email', '');
$contactPhone = $customFields['contact_phone'] ?? '';
$contactAddress = $customFields['contact_address'] ?? '';
$contactHours = $customFields['contact_hours'] ?? '';

// Harita
$mapEmbed = $customFields['map_embed'] ?? '';
$mapLatitude = $customFields['map_latitude'] ?? '';
$mapLongitude = $customFields['map_longitude'] ?? '';

// Form
$formId = $customFields['form_id'] ?? null;
$formTitle = $customFields['form_title'] ?? 'Bize Ulaşın';
$formDescription = $customFields['form_description'] ?? '';

// Form component'ini yükle
$formComponentPath = __DIR__ . '/../../app/views/frontend/components/form.php';
if (file_exists($formComponentPath)) {
    require_once $formComponentPath;
}

// Sosyal medya
$socialFacebook = $customFields['social_facebook'] ?? '';
$socialTwitter = $customFields['social_twitter'] ?? '';
$socialInstagram = $customFields['social_instagram'] ?? '';
$socialLinkedin = $customFields['social_linkedin'] ?? '';

// Tema renkleri
$primaryColor = $themeLoader->getColor('primary', '#137fec');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($title ?? $pageTitle); ?></title>
    <?php if (!empty($meta_description)): ?>
    <meta name="description" content="<?php echo esc_attr($meta_description); ?>">
    <?php endif; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <style>
        /* Form CSS - Inline */
        .cms-form-wrapper { max-width: 600px; margin: 0 auto; }
        .cms-form-wrapper .form-description { color: #6b7280; margin-bottom: 1.5rem; font-size: 0.95rem; line-height: 1.6; }
        .cms-form { font-family: inherit; }
        .cms-form .form-fields { display: flex; flex-wrap: wrap; gap: 1.25rem; }
        .cms-form .form-field { flex-basis: 100%; }
        .cms-form .form-field.field-width-half { flex-basis: calc(50% - 0.625rem); }
        .cms-form .form-field.field-width-third { flex-basis: calc(33.333% - 0.833rem); }
        .cms-form .form-field.field-width-quarter { flex-basis: calc(25% - 0.9375rem); }
        .cms-form .field-label { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem; }
        .cms-form .required-mark { color: #ef4444; margin-left: 0.25rem; }
        .cms-form .field-input input[type="text"],
        .cms-form .field-input input[type="email"],
        .cms-form .field-input input[type="tel"],
        .cms-form .field-input input[type="number"],
        .cms-form .field-input input[type="date"],
        .cms-form .field-input input[type="time"],
        .cms-form .field-input input[type="datetime-local"],
        .cms-form .field-input textarea,
        .cms-form .field-input select {
            width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem;
            font-size: 1rem; color: #111827; background-color: white; transition: all 0.2s; font-family: inherit;
        }
        .cms-form .field-input input:focus,
        .cms-form .field-input textarea:focus,
        .cms-form .field-input select:focus {
            outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .cms-form .field-input textarea { resize: vertical; min-height: 120px; }
        .cms-form .field-help { font-size: 0.75rem; color: #6b7280; margin-top: 0.375rem; }
        .cms-form .checkbox-group,
        .cms-form .radio-group { display: flex; flex-direction: column; gap: 0.625rem; }
        .cms-form .checkbox-label,
        .cms-form .radio-label { display: flex; align-items: center; gap: 0.625rem; cursor: pointer; font-size: 0.95rem; color: #374151; }
        .cms-form .checkbox-label input,
        .cms-form .radio-label input { width: 1.125rem; height: 1.125rem; accent-color: #3b82f6; cursor: pointer; }
        .cms-form .form-submit { margin-top: 1.5rem; }
        .cms-form .submit-button {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            padding: 0.875rem 2rem; font-size: 1rem; font-weight: 600; color: white;
            border: none; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s; font-family: inherit;
        }
        .cms-form .submit-button:hover:not(:disabled) { opacity: 0.9; transform: translateY(-1px); }
        .cms-form .submit-button:disabled { opacity: 0.7; cursor: not-allowed; }
        @media (max-width: 640px) {
            .cms-form .form-field.field-width-half,
            .cms-form .form-field.field-width-third,
            .cms-form .form-field.field-width-quarter { flex-basis: 100%; }
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
        .ring-primary { --tw-ring-color: var(--color-primary); }
    </style>
</head>
<body class="antialiased bg-gray-50">
    
    <?php echo $themeLoader->renderSnippet('header', ['title' => $pageTitle, 'current_page' => 'contact']); ?>
    
    <!-- Hero Section -->
    <section class="gradient-primary py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl lg:text-5xl font-bold text-white mb-6"><?php echo esc_html($pageTitle); ?></h1>
            <?php if (!empty($pageExcerpt)): ?>
            <p class="text-xl text-white/80 max-w-2xl mx-auto"><?php echo esc_html($pageExcerpt); ?></p>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Main Content -->
    <section class="py-16 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16">
                
                <!-- İletişim Bilgileri -->
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-8">İletişim Bilgileri</h2>
                    
                    <div class="space-y-6">
                        <?php if (!empty($contactEmail)): ?>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-primary">mail</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">E-posta</h3>
                                <a href="mailto:<?php echo esc_attr($contactEmail); ?>" class="text-gray-600 hover:text-primary transition-colors">
                                    <?php echo esc_html($contactEmail); ?>
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($contactPhone)): ?>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-primary">phone</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">Telefon</h3>
                                <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $contactPhone)); ?>" class="text-gray-600 hover:text-primary transition-colors">
                                    <?php echo esc_html($contactPhone); ?>
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($contactAddress)): ?>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-primary">location_on</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">Adres</h3>
                                <p class="text-gray-600"><?php echo nl2br(esc_html($contactAddress)); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($contactHours)): ?>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-primary">schedule</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">Çalışma Saatleri</h3>
                                <p class="text-gray-600"><?php echo esc_html($contactHours); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Sosyal Medya -->
                    <?php if (!empty($socialFacebook) || !empty($socialTwitter) || !empty($socialInstagram) || !empty($socialLinkedin)): ?>
                    <div class="mt-10 pt-10 border-t border-gray-200">
                        <h3 class="font-semibold text-gray-900 mb-4">Bizi Takip Edin</h3>
                        <div class="flex gap-4">
                            <?php if (!empty($socialFacebook)): ?>
                            <a href="<?php echo esc_url($socialFacebook); ?>" target="_blank" rel="noopener" class="w-10 h-10 bg-gray-100 hover:bg-primary hover:text-white rounded-full flex items-center justify-center text-gray-600 transition-all">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.77 7.46H14.5v-1.9c0-.9.6-1.1 1-1.1h3V.5h-4.33C10.24.5 9.5 3.44 9.5 5.32v2.15h-3v4h3v12h5v-12h3.85l.42-4z"/></svg>
                            </a>
                            <?php endif; ?>
                            <?php if (!empty($socialTwitter)): ?>
                            <a href="<?php echo esc_url($socialTwitter); ?>" target="_blank" rel="noopener" class="w-10 h-10 bg-gray-100 hover:bg-primary hover:text-white rounded-full flex items-center justify-center text-gray-600 transition-all">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            </a>
                            <?php endif; ?>
                            <?php if (!empty($socialInstagram)): ?>
                            <a href="<?php echo esc_url($socialInstagram); ?>" target="_blank" rel="noopener" class="w-10 h-10 bg-gray-100 hover:bg-primary hover:text-white rounded-full flex items-center justify-center text-gray-600 transition-all">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                            <?php endif; ?>
                            <?php if (!empty($socialLinkedin)): ?>
                            <a href="<?php echo esc_url($socialLinkedin); ?>" target="_blank" rel="noopener" class="w-10 h-10 bg-gray-100 hover:bg-primary hover:text-white rounded-full flex items-center justify-center text-gray-600 transition-all">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- İletişim Formu -->
                <div>
                    <div class="bg-white rounded-2xl shadow-lg p-8">
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
                        
                        <?php if (!empty($formId) && function_exists('render_form_by_id')): ?>
                            <!-- Seçilen formu render et -->
                            <?php echo render_form_by_id($formId); ?>
                        <?php else: ?>
                            <!-- Varsayılan form (form seçilmemişse) -->
                            <p class="text-gray-500 text-sm mb-4">Lütfen admin panelinden bir form seçin.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Harita -->
    <?php if (!empty($mapEmbed)): ?>
    <section class="pb-16 lg:pb-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl overflow-hidden shadow-lg">
                <?php echo $mapEmbed; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <?php echo $themeLoader->renderSnippet('footer'); ?>
    
</body>
</html>
