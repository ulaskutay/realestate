<?php
/**
 * Çizgi Aks Gayrimenkul - İletişim Sayfası
 * Veritabanı sayfası veya tema ayarları ile çalışır. Tema renkleri yansıtılır.
 */

$themeLoader = $themeLoader ?? null;
$themeSettings = $themeLoader ? $themeLoader->getAllSettings() : [];
$primaryColor = $themeLoader ? $themeLoader->getColor('primary', '#bc1a1a') : '#bc1a1a';
// themeSettings yapısı: [grup][anahtar]['value'] - header/phone vb. için
$getSetting = function($key, $default = '', $group = null) use ($themeLoader, $themeSettings) {
    if ($themeLoader) {
        return $themeLoader->getSetting($key, $default, $group);
    }
    if ($group && isset($themeSettings[$group][$key]['value'])) {
        return $themeSettings[$group][$key]['value'];
    }
    return $default;
};

// Önce custom fields'dan bilgileri al (veritabanından gelen sayfa için)
$customFields = $customFields ?? [];
$contactEmail = $customFields['contact_email'] ?? '';
$contactPhone = $customFields['contact_phone'] ?? '';
$contactAddress = $customFields['contact_address'] ?? '';
$contactHours = $customFields['contact_hours'] ?? '';
$mapEmbed = $customFields['map_embed'] ?? '';
$formId = $customFields['form_id'] ?? '';
$formTitle = $customFields['form_title'] ?? __('Bize Mesaj Gönderin');
$formDescription = $customFields['form_description'] ?? '';

// Aktif ilk form: sayfa/ayar form_id boşsa veritabanındaki ilk aktif formu kullan (talep formu)
$useFormId = !empty($formId) ? $formId : get_option('contact_form_id', '');
if (empty($useFormId) && class_exists('FormRenderer')) {
    if (!class_exists('Database')) {
        require_once __DIR__ . '/../../core/Database.php';
    }
    try {
        $db = Database::getInstance();
        $firstForm = $db->fetch("SELECT id FROM `forms` WHERE `status` = 'active' ORDER BY id ASC LIMIT 1");
        if ($firstForm && !empty($firstForm['id'])) {
            $useFormId = $firstForm['id'];
        }
    } catch (Exception $e) {
        error_log('Contact page first form load: ' . $e->getMessage());
    }
}

// Custom fields boşsa tema/site ayarlarından al (undefined index önlemi)
$headerSettings = $themeSettings['header'] ?? [];
if (empty($contactPhone)) {
    $contactPhone = $getSetting('phone', '', 'header') ?: ($headerSettings['phone']['value'] ?? $headerSettings['phone'] ?? '');
}
if (empty($contactHours)) {
    $contactHours = $getSetting('working_hours', '09:00 - 18:00', 'header') ?: ($headerSettings['working_hours']['value'] ?? $headerSettings['working_hours'] ?? '09:00 - 18:00');
}
if (empty($contactEmail)) {
    $contactEmail = get_option('contact_email', get_option('admin_email', ''));
}
if (empty($contactAddress)) {
    $contactAddress = get_option('contact_address', '');
}
if (empty($mapEmbed)) {
    $mapEmbed = get_option('google_maps_embed', '');
}

// Sayfa başlığı
$pageTitle = isset($page) ? ($page['title'] ?? __('İletişim')) : __('İletişim');
$pageExcerpt = isset($page) ? ($page['excerpt'] ?? '') : '';
?>
<div class="cizgiaks-contact-page py-12">
    <div class="cizgiaks-container">
        <!-- Sayfa Başlığı -->
        <div class="text-center mb-12">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4"><?php echo esc_html($pageTitle); ?></h1>
            <p class="text-gray-600 max-w-2xl mx-auto"><?php echo !empty($pageExcerpt) ? esc_html($pageExcerpt) : __('Bizimle iletişime geçmek için aşağıdaki formu kullanabilir veya doğrudan bizi arayabilirsiniz.'); ?></p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- İletişim Bilgileri -->
            <div class="lg:col-span-1 space-y-6">
                <?php if ($contactPhone): ?>
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1"><?php echo __('Telefon'); ?></h3>
                            <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $contactPhone); ?>" class="text-primary hover:underline text-lg font-medium"><?php echo esc_html($contactPhone); ?></a>
                            <?php if ($contactHours): ?>
                            <p class="text-sm text-gray-500 mt-1"><?php echo esc_html($contactHours); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($contactEmail): ?>
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1"><?php echo __('E-posta'); ?></h3>
                            <a href="mailto:<?php echo esc_attr($contactEmail); ?>" class="text-primary hover:underline"><?php echo esc_html($contactEmail); ?></a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($contactAddress): ?>
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1"><?php echo __('Adres'); ?></h3>
                            <p class="text-gray-600"><?php echo nl2br(esc_html($contactAddress)); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- İletişim Formu -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl p-8 shadow-sm border border-gray-100">
                    <h2 class="text-xl font-semibold text-gray-900 mb-2"><?php echo esc_html($formTitle); ?></h2>
                    <?php if ($formDescription): ?>
                    <p class="text-gray-500 mb-6"><?php echo esc_html($formDescription); ?></p>
                    <?php else: ?>
                    <div class="mb-6"></div>
                    <?php endif; ?>
                    
                    <?php
                    if (!empty($useFormId) && class_exists('FormRenderer')) {
                        $formRenderer = new FormRenderer();
                        echo $formRenderer->render($useFormId);
                    } else {
                        // Basit varsayılan form
                    ?>
                    <form action="<?php echo site_url('contact/submit'); ?>" method="POST" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo __('Adınız Soyadınız'); ?> *</label>
                                <input type="text" name="name" required 
                                       class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo __('E-posta Adresiniz'); ?> *</label>
                                <input type="email" name="email" required 
                                       class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo __('Telefon Numaranız'); ?></label>
                            <input type="tel" name="phone" 
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo __('Konu'); ?></label>
                            <input type="text" name="subject" 
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo __('Mesajınız'); ?> *</label>
                            <textarea name="message" rows="5" required 
                                      class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors resize-none"></textarea>
                        </div>
                        
                        <button type="submit" 
                                class="w-full md:w-auto px-8 py-3 bg-primary text-white font-medium rounded-lg hover:bg-primary/90 transition-colors">
                            <?php echo __('Mesaj Gönder'); ?>
                        </button>
                    </form>
                    <?php } ?>
                </div>
            </div>
        </div>
        
        <?php if ($mapEmbed): ?>
        <!-- Harita -->
        <div class="mt-12">
            <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100">
                <div class="aspect-video">
                    <?php echo $mapEmbed; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.cizgiaks-contact-page {
    background: linear-gradient(to bottom, #f9fafb, #ffffff);
    min-height: 60vh;
}
.cizgiaks-contact-page .cizgiaks-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 1rem;
}
.cizgiaks-contact-page iframe {
    width: 100%;
    height: 100%;
    border: 0;
}
.cizgiaks-contact-page .bg-primary\/10 {
    background-color: rgba(var(--color-primary-rgb, 188, 26, 26), 0.1);
}
.cizgiaks-contact-page .text-primary {
    color: var(--color-primary, <?php echo $primaryColor; ?>);
}
.cizgiaks-contact-page .bg-primary {
    background-color: var(--color-primary, <?php echo $primaryColor; ?>);
}
.cizgiaks-contact-page .focus\:ring-primary\/20:focus {
    --tw-ring-color: rgba(var(--color-primary-rgb, 188, 26, 26), 0.2);
}
.cizgiaks-contact-page .focus\:border-primary:focus {
    border-color: var(--color-primary, <?php echo $primaryColor; ?>);
}
.cizgiaks-contact-page .hover\:bg-primary\/90:hover {
    background-color: var(--color-primary, <?php echo $primaryColor; ?>);
    opacity: 0.9;
}
</style>
