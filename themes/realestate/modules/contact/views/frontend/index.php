<?php
/**
 * Contact Module - Frontend View
 * İletişim sayfası görünümü
 */

// Functions dosyasını yükle (the_form fonksiyonu için)
if (!function_exists('the_form')) {
    require_once __DIR__ . '/../../../../../../includes/functions.php';
}

// Form component'ini yükle
$formComponentPath = __DIR__ . '/../../../../../../app/views/frontend/components/form.php';
if (file_exists($formComponentPath)) {
    require_once $formComponentPath;
}

// Sayfa içeriğini buffer'a al
if (ob_get_level() > 0) {
    ob_end_clean();
}
ob_start();
?>

<!-- Hero Section -->
<section class="relative py-20 lg:py-28 bg-gradient-to-br from-secondary/90 via-secondary/80 to-primary/90 text-white overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.1\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    </div>
    
    <div class="container mx-auto px-4 lg:px-6 relative z-10">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-4xl lg:text-6xl font-bold mb-6 leading-tight">
                <?php echo esc_html(__('Hayalinizdeki Mülkü Bulalım')); ?>
            </h1>
            <p class="text-xl lg:text-2xl text-white/90 mb-8 leading-relaxed">
                <?php echo esc_html(__('Uzman ekibimiz, mülk satın alma, satış veya kiralama işlemlerinizde size yardımcı olmak için burada. Hemen iletişime geçin!')); ?>
            </p>
            
            <!-- Quick Stats -->
            <div class="flex flex-wrap justify-center gap-6 lg:gap-8 mt-10">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-lg font-medium"><?php echo esc_html(__('24 Saat İçinde Yanıt')); ?></span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <span class="text-lg font-medium"><?php echo esc_html(__('500+ Mülk Seçeneği')); ?></span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <span class="text-lg font-medium"><?php echo esc_html(__('Uzman Danışmanlar')); ?></span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <span class="text-lg font-medium"><?php echo esc_html(__('Güvenli İşlem')); ?></span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-16 lg:py-24 bg-surface">
    <div class="container mx-auto px-4 lg:px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
            
            <!-- Contact Form -->
            <div class="bg-white rounded-2xl shadow-xl p-8 lg:p-10">
                <div class="mb-8">
                    <div class="w-16 h-16 bg-primary rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold text-secondary mb-3">
                        <?php echo esc_html(__('Mülk Talebinizi İletin')); ?>
                    </h2>
                    <p class="text-gray-600 text-lg">
                        <?php echo esc_html(__('Aradığınız mülk özelliklerini belirtin, size en uygun seçenekleri sunalım.')); ?>
                    </p>
                </div>
                
                <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-xl <?php echo $messageType === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200'; ?>">
                    <?php echo esc_html($message); ?>
                </div>
                <?php endif; ?>
                
                <?php 
                // Form render - 'iletisim' slug'ı ile formu göster
                if (function_exists('the_form')) {
                    the_form('iletisim');
                } else if (function_exists('cms_form')) {
                    echo cms_form('iletisim');
                } else {
                    echo '<p class="text-gray-600">İletişim formu yüklenemedi.</p>';
                }
                ?>
                
                <!-- Real Estate Info Box -->
                <div class="mt-8 p-6 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-100">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-lg font-bold text-gray-900 mb-2">
                                <?php echo esc_html(__('Neden Bizi Tercih Etmelisiniz?')); ?>
                            </h4>
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span><?php echo esc_html(__('500+ aktif mülk seçeneği')); ?></span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span><?php echo esc_html(__('Deneyimli ve sertifikalı danışmanlar')); ?></span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span><?php echo esc_html(__('Şeffaf fiyatlandırma ve güvenli işlem')); ?></span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span><?php echo esc_html(__('7/24 müşteri desteği ve hızlı yanıt')); ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="space-y-6">
                
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
                
                <!-- Quick Services -->
                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-secondary">
                                <?php echo esc_html(__('Hizmetlerimiz')); ?>
                            </h3>
                            <p class="text-sm text-gray-500">
                                <?php echo esc_html(__('Size nasıl yardımcı olabiliriz?')); ?>
                            </p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-4 bg-gray-50 rounded-lg text-center hover:bg-primary/5 transition-colors">
                            <svg class="w-8 h-8 text-primary mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <p class="text-xs font-semibold text-gray-700"><?php echo esc_html(__('Satılık Mülk')); ?></p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg text-center hover:bg-primary/5 transition-colors">
                            <svg class="w-8 h-8 text-primary mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <p class="text-xs font-semibold text-gray-700"><?php echo esc_html(__('Kiralık Mülk')); ?></p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg text-center hover:bg-primary/5 transition-colors">
                            <svg class="w-8 h-8 text-primary mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-xs font-semibold text-gray-700"><?php echo esc_html(__('Mülk Değerleme')); ?></p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg text-center hover:bg-primary/5 transition-colors">
                            <svg class="w-8 h-8 text-primary mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p class="text-xs font-semibold text-gray-700"><?php echo esc_html(__('Danışmanlık')); ?></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<?php if ($mapEmbed || $companyAddress): ?>
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

// Layout'u kullan
try {
    require_once __DIR__ . '/../../../../layouts/default.php';
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
