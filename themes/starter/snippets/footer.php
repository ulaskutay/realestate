<?php
/**
 * Starter Theme - Footer
 * Prism UI tarzı modern footer tasarımı
 */

// Tema ayarları
$footerShow = $themeLoader->getCustomSetting('footer_show', true);
$footerColumns = $themeLoader->getCustomSetting('footer_columns', '4');
$footerBgColor = $themeLoader->getCustomSetting('footer_bg_color', '#111827');
$footerTextColor = $themeLoader->getCustomSetting('footer_text_color', '#ffffff');
$footerCopyrightText = $themeLoader->getCustomSetting('footer_copyright_text', 'Tüm hakları saklıdır.');
$footerShowSocial = $themeLoader->getCustomSetting('footer_show_social', true);
$footerShowMenu = $themeLoader->getCustomSetting('footer_show_menu', true);
$footerShowContact = $themeLoader->getCustomSetting('footer_show_contact', true);

// Copyright ayarları
$footerCopyrightFormat = $themeLoader->getCustomSetting('footer_copyright_format', 'default');
$footerCopyrightYear = $themeLoader->getCustomSetting('footer_copyright_year', 'auto');
$footerCopyrightYearManual = $themeLoader->getCustomSetting('footer_copyright_year_manual', '');
$footerCopyrightCompany = $themeLoader->getCustomSetting('footer_copyright_company', '');
$footerCopyrightCustom = $themeLoader->getCustomSetting('footer_copyright_custom', '');

// Footer gösterilmiyorsa çık
if (!$footerShow) {
    return;
}

// Site ayarları
$siteName = get_option('site_name', 'Site Adı');
$siteDescription = get_option('site_description', 'Modern ve minimal tasarımlı başlangıç teması.');
$siteLogo = $themeLoader->getLogo();

// Şirket bilgileri (company_* öncelikli, yoksa contact_* fallback)
$companyName = get_option('company_name', get_option('site_name', ''));
$companyEmail = get_option('company_email', get_option('contact_email', ''));
$companyPhone = get_option('company_phone', get_option('contact_phone', ''));
$companyAddress = get_option('company_address', get_option('contact_address', ''));
$companyTaxNumber = get_option('company_tax_number', '');

// Sosyal medya
$socialFacebook = get_option('social_facebook', '');
$socialTwitter = get_option('social_twitter', '');
$socialInstagram = get_option('social_instagram', '');
$socialLinkedin = get_option('social_linkedin', '');
$socialYoutube = get_option('social_youtube', '');

// Footer menüsü
$footerMenu = get_menu('footer');
$footerMenuItems = $footerMenu['items'] ?? [];
$footerMenuTitle = $footerMenu['name'] ?? 'PRODUCT';

// Kolon içerik kontrolü
$hasSocialLinks = $footerShowSocial && ($socialFacebook || $socialTwitter || $socialInstagram || $socialLinkedin || $socialYoutube);
$hasMenu = $footerShowMenu && !empty($footerMenuItems);
$hasContact = $footerShowContact && ($companyEmail || $companyPhone || $companyAddress);
$hasCompanyInfo = !empty($companyName) || !empty($companyTaxNumber);

// Renk yardımcı fonksiyonu
if (!function_exists('hexToRgb')) {
    function hexToRgb($hex, $alpha = 1) {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) !== 6) {
            return "rgba(255, 255, 255, $alpha)";
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "rgba($r, $g, $b, $alpha)";
    }
}

// Border ve icon background renkleri
$borderColor = hexToRgb($footerTextColor, 0.1);
$iconBgColor = hexToRgb($footerTextColor, 0.08);
$textMutedColor = hexToRgb($footerTextColor, 0.6);
?>

<footer style="background-color: <?php echo esc_attr($footerBgColor); ?>; color: <?php echo esc_attr($footerTextColor); ?>;" class="font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-8">
        <!-- BEGIN: Top Section -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-12 mb-12">
            
            <!-- Brand Column (2 kolon genişliğinde) -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <?php if (!empty($siteLogo)): ?>
                        <img src="<?php echo esc_url($siteLogo); ?>" alt="<?php echo esc_attr($siteName); ?>" class="w-8 h-8 object-contain brightness-0 invert">
                    <?php else: ?>
                        <div class="w-8 h-8 flex items-center justify-center rounded" style="background: linear-gradient(135deg, var(--color-primary, #137fec), var(--color-secondary, #6366f1));">
                            <svg class="w-full h-full text-white" fill="currentColor" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 10H14V14H10V10Z"></path>
                                <path d="M18 10H22V14H18V10Z"></path>
                                <path d="M10 18H14V22H10V18Z"></path>
                                <path d="M18 18H22V22H18V18Z"></path>
                            </svg>
                        </div>
                    <?php endif; ?>
                    <span class="text-xl font-bold tracking-tight text-white"><?php echo esc_html($siteName); ?></span>
                </div>
                
                <!-- Description -->
                <?php if (!empty($siteDescription)): ?>
                <p class="text-sm leading-relaxed max-w-xs leading-[1.6]" style="color: <?php echo esc_attr($textMutedColor); ?>;">
                    <?php echo esc_html($siteDescription); ?>
                </p>
                <?php endif; ?>
            </div>
            
            <!-- Links Columns Wrapper (3 kolon) -->
            <div class="lg:col-span-3 grid grid-cols-2 md:grid-cols-3 gap-8">
                
                <!-- Kolon 1: Footer Menü (PRODUCT) -->
                <?php if ($hasMenu): ?>
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white mb-4 text-slate-50 font-semibold">
                        <?php echo esc_html(strtoupper($footerMenuTitle)); ?>
                    </h3>
                    <ul class="space-y-4">
                        <?php foreach ($footerMenuItems as $item): ?>
                        <li>
                            <a href="<?php echo esc_url($item['url']); ?>" 
                               class="hover:text-white transition-colors text-sm" 
                               style="color: <?php echo esc_attr($textMutedColor); ?>;"
                               <?php echo ($item['target'] ?? '') === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                                <?php echo esc_html($item['title']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <!-- Kolon 2: İletişim Bilgileri (COMPANY) -->
                <?php if ($hasContact): ?>
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white mb-4 text-slate-50 font-semibold">COMPANY</h3>
                    <ul class="space-y-4">
                        <?php if ($companyAddress): ?>
                        <li>
                            <a href="https://maps.google.com/?q=<?php echo urlencode($companyAddress); ?>" 
                               target="_blank" 
                               rel="noopener"
                               class="hover:text-white transition-colors text-sm block" 
                               style="color: <?php echo esc_attr($textMutedColor); ?>;">
                                <?php echo esc_html($companyAddress); ?>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($companyPhone): ?>
                        <li>
                            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $companyPhone)); ?>" 
                               class="hover:text-white transition-colors text-sm" 
                               style="color: <?php echo esc_attr($textMutedColor); ?>;">
                                <?php echo esc_html($companyPhone); ?>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($companyEmail): ?>
                        <li>
                            <a href="mailto:<?php echo esc_attr($companyEmail); ?>" 
                               class="hover:text-white transition-colors text-sm" 
                               style="color: <?php echo esc_attr($textMutedColor); ?>;">
                                <?php echo esc_html($companyEmail); ?>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($companyName): ?>
                        <li>
                            <span class="text-sm" style="color: <?php echo esc_attr($textMutedColor); ?>;">
                                <?php echo esc_html($companyName); ?>
                            </span>
                        </li>
                        <?php endif; ?>
                        <?php if ($companyTaxNumber): ?>
                        <li>
                            <span class="text-sm" style="color: <?php echo esc_attr($textMutedColor); ?>;">
                                Vergi No: <?php echo esc_html($companyTaxNumber); ?>
                            </span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <!-- Kolon 3: Şirket Bilgileri / Resources -->
                <?php if ($hasCompanyInfo && !$hasContact): ?>
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white mb-4 text-slate-50 font-semibold">RESOURCES</h3>
                    <ul class="space-y-4">
                        <?php if ($companyName): ?>
                        <li>
                            <span class="text-sm" style="color: <?php echo esc_attr($textMutedColor); ?>;">
                                <?php echo esc_html($companyName); ?>
                            </span>
                        </li>
                        <?php endif; ?>
                        <?php if ($companyTaxNumber): ?>
                        <li>
                            <span class="text-sm" style="color: <?php echo esc_attr($textMutedColor); ?>;">
                                Vergi No: <?php echo esc_html($companyTaxNumber); ?>
                            </span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- END: Top Section -->
        
        <!-- Divider Line -->
        <div class="border-t my-8" style="border-color: <?php echo esc_attr($borderColor); ?>;"></div>
        
        <!-- BEGIN: Bottom Section -->
        <div class="flex flex-col md:flex-row justify-between items-center gap-6">
            <!-- Copyright & Legal Links -->
            <div class="flex flex-col md:flex-row items-center gap-4 md:gap-8 text-sm" style="color: <?php echo esc_attr($textMutedColor); ?>;">
                <?php
                // Copyright metnini oluştur
                $copyrightText = '';
                
                if ($footerCopyrightFormat === 'custom' && !empty($footerCopyrightCustom)) {
                    // Özel format
                    $copyrightText = $footerCopyrightCustom;
                    // {year} ve {company} placeholder'larını değiştir
                    $copyrightYear = ($footerCopyrightYear === 'manual' && !empty($footerCopyrightYearManual)) 
                        ? $footerCopyrightYearManual 
                        : date('Y');
                    $copyrightCompany = !empty($footerCopyrightCompany) ? $footerCopyrightCompany : $siteName;
                    
                    $copyrightText = str_replace('{year}', $copyrightYear, $copyrightText);
                    $copyrightText = str_replace('{company}', $copyrightCompany, $copyrightText);
                } else {
                    // Varsayılan format
                    $copyrightYear = ($footerCopyrightYear === 'manual' && !empty($footerCopyrightYearManual)) 
                        ? $footerCopyrightYearManual 
                        : date('Y');
                    $copyrightCompany = !empty($footerCopyrightCompany) ? $footerCopyrightCompany : $siteName;
                    
                    $copyrightText = '© ' . $copyrightYear . ' ' . $copyrightCompany;
                    if (!empty($footerCopyrightText)) {
                        $copyrightText .= '. ' . $footerCopyrightText;
                    }
                }
                ?>
                <p><?php echo esc_html($copyrightText); ?></p>
                <div class="flex gap-6">
                    <a href="/gizlilik-politikasi" class="hover:text-white transition-colors">Gizlilik Politikası</a>
                    <a href="/kullanim-kosullari" class="hover:text-white transition-colors">Kullanım Şartları</a>
                    <a href="/cerez-politikasi" class="hover:text-white transition-colors">Çerezler</a>
                </div>
            </div>
            
            <!-- Social Icons -->
            <?php if ($hasSocialLinks): ?>
            <div class="flex items-center gap-3">
                <?php if ($socialTwitter): ?>
                <a aria-label="Twitter" 
                   class="w-10 h-10 flex items-center justify-center rounded-md transition-colors hover:bg-slate-700" 
                   style="background-color: <?php echo esc_attr($iconBgColor); ?>; color: <?php echo esc_attr($textMutedColor); ?>;"
                   href="<?php echo esc_url($socialTwitter); ?>" 
                   target="_blank" 
                   rel="noopener">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"></path>
                    </svg>
                </a>
                <?php endif; ?>
                
                <?php if ($socialLinkedin): ?>
                <a aria-label="LinkedIn" 
                   class="w-10 h-10 flex items-center justify-center rounded-md transition-colors hover:bg-slate-700" 
                   style="background-color: <?php echo esc_attr($iconBgColor); ?>; color: <?php echo esc_attr($textMutedColor); ?>;"
                   href="<?php echo esc_url($socialLinkedin); ?>" 
                   target="_blank" 
                   rel="noopener">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"></path>
                    </svg>
                </a>
                <?php endif; ?>
                
                <?php if ($socialFacebook): ?>
                <a aria-label="Facebook" 
                   class="w-10 h-10 flex items-center justify-center rounded-md transition-colors hover:bg-slate-700" 
                   style="background-color: <?php echo esc_attr($iconBgColor); ?>; color: <?php echo esc_attr($textMutedColor); ?>;"
                   href="<?php echo esc_url($socialFacebook); ?>" 
                   target="_blank" 
                   rel="noopener">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"></path>
                    </svg>
                </a>
                <?php endif; ?>
                
                <?php if ($socialYoutube): ?>
                <a aria-label="YouTube" 
                   class="w-10 h-10 flex items-center justify-center rounded-md transition-colors hover:bg-slate-700" 
                   style="background-color: <?php echo esc_attr($iconBgColor); ?>; color: <?php echo esc_attr($textMutedColor); ?>;"
                   href="<?php echo esc_url($socialYoutube); ?>" 
                   target="_blank" 
                   rel="noopener">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"></path>
                    </svg>
                </a>
                <?php endif; ?>
                
                <?php if ($socialInstagram): ?>
                <a aria-label="Instagram" 
                   class="w-10 h-10 flex items-center justify-center rounded-md transition-colors hover:bg-slate-700" 
                   style="background-color: <?php echo esc_attr($iconBgColor); ?>; color: <?php echo esc_attr($textMutedColor); ?>;"
                   href="<?php echo esc_url($socialInstagram); ?>" 
                   target="_blank" 
                   rel="noopener">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"></path>
                    </svg>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <!-- END: Bottom Section -->
    </div>
</footer>
