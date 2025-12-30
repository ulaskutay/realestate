<?php
// Şirket bilgileri (company_* öncelikli, yoksa contact_* fallback)
$companyEmail = get_option('company_email', get_option('contact_email', ''));
$companyPhone = get_option('company_phone', get_option('contact_phone', ''));
$companyAddress = get_option('company_address', get_option('contact_address', ''));

// Footer menüsünü getir
$footerMenu = get_menu('footer');
$footerItems = $footerMenu['items'] ?? [];
$footerMenuTitle = $footerMenu['name'] ?? 'Sayfalar';
?>
<footer class="bg-gray-900 text-white">
    <!-- Ana Footer -->
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-12 lg:py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
            <!-- Logo ve Açıklama -->
            <div class="lg:col-span-2">
                <a href="<?php echo ViewRenderer::siteUrl(); ?>" class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-br rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, var(--color-primary, #8B5CF6), var(--color-secondary, #6366f1));">
                        <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold"><?php echo esc_html(get_option('site_name', 'Site Adı')); ?></span>
                </a>
                <p class="text-gray-400 mb-6 max-w-md">
                    <?php echo esc_html(get_option('site_description', 'Modern ve güçlü CMS sistemi ile web sitenizi kolayca yönetin.')); ?>
                </p>
                <!-- Sosyal Medya -->
                <div class="flex gap-4">
                    <?php if ($facebook = get_option('social_facebook')): ?>
                    <a href="<?php echo esc_url($facebook); ?>" target="_blank" rel="noopener" class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center hover:bg-white/20 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <?php endif; ?>
                    <?php if ($twitter = get_option('social_twitter')): ?>
                    <a href="<?php echo esc_url($twitter); ?>" target="_blank" rel="noopener" class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center hover:bg-white/20 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <?php endif; ?>
                    <?php if ($instagram = get_option('social_instagram')): ?>
                    <a href="<?php echo esc_url($instagram); ?>" target="_blank" rel="noopener" class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center hover:bg-white/20 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                    <?php endif; ?>
                    <?php if ($linkedin = get_option('social_linkedin')): ?>
                    <a href="<?php echo esc_url($linkedin); ?>" target="_blank" rel="noopener" class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center hover:bg-white/20 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Footer Menü -->
            <?php if (!empty($footerItems)): ?>
            <div>
                <h4 class="text-lg font-semibold mb-4"><?php echo esc_html($footerMenuTitle); ?></h4>
                <ul class="space-y-3">
                    <?php foreach ($footerItems as $item): ?>
                    <li>
                        <a href="<?php echo esc_url($item['url']); ?>" 
                           class="text-gray-400 hover:text-white transition-colors"
                           <?php echo $item['target'] === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                            <?php echo esc_html($item['title']); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <!-- İletişim Bilgileri -->
            <?php if ($companyEmail || $companyPhone || $companyAddress): ?>
            <div>
                <h4 class="text-lg font-semibold mb-4">İletişim</h4>
                <ul class="space-y-3">
                    <?php if ($companyAddress): ?>
                    <li class="flex items-start gap-3 text-gray-400">
                        <span class="material-symbols-outlined text-xl">location_on</span>
                        <span><?php echo esc_html($companyAddress); ?></span>
                    </li>
                    <?php endif; ?>
                    <?php if ($companyPhone): ?>
                    <li class="flex items-center gap-3 text-gray-400">
                        <span class="material-symbols-outlined text-xl">phone</span>
                        <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $companyPhone)); ?>" class="hover:text-white transition-colors"><?php echo esc_html($companyPhone); ?></a>
                    </li>
                    <?php endif; ?>
                    <?php if ($companyEmail): ?>
                    <li class="flex items-center gap-3 text-gray-400">
                        <span class="material-symbols-outlined text-xl">mail</span>
                        <a href="mailto:<?php echo esc_attr($companyEmail); ?>" class="hover:text-white transition-colors"><?php echo esc_html($companyEmail); ?></a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Alt Footer -->
    <div class="border-t border-white/10">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-gray-400 text-sm">
                    &copy; <?php echo date('Y'); ?> <?php echo esc_html(get_option('site_name', 'Site Adı')); ?>. Tüm hakları saklıdır.
                </p>
                <div class="flex items-center gap-6">
                    <a href="<?php echo ViewRenderer::siteUrl('gizlilik-politikasi'); ?>" class="text-gray-400 hover:text-white text-sm transition-colors">Gizlilik Politikası</a>
                    <a href="<?php echo ViewRenderer::siteUrl('kullanim-kosullari'); ?>" class="text-gray-400 hover:text-white text-sm transition-colors">Kullanım Koşulları</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="<?php echo ViewRenderer::assetUrl('frontend/js/slider.js'); ?>"></script>
