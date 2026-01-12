<?php
/**
 * Codetic Theme - Footer
 * Modern ve şık footer tasarımı
 */

// Tema ayarları
$footerShow = $themeLoader->getCustomSetting('footer_show', true);
$footerBgColor = $themeLoader->getCustomSetting('footer_bg_color', '#0a0a0f');
$footerTextColor = $themeLoader->getCustomSetting('footer_text_color', '#ffffff');
$footerCopyrightText = $themeLoader->getCustomSetting('footer_copyright_text', 'Tüm hakları saklıdır.');
$footerShowSocial = $themeLoader->getCustomSetting('footer_show_social', true);
$footerShowMenu = $themeLoader->getCustomSetting('footer_show_menu', true);
$footerShowContact = $themeLoader->getCustomSetting('footer_show_contact', true);

// Yeni footer tasarım ayarları
$footerShowGradientOverlay = $themeLoader->getCustomSetting('footer_show_gradient_overlay', true);
$footerShowGridPattern = $themeLoader->getCustomSetting('footer_show_grid_pattern', true);
$footerSocialIconStyle = $themeLoader->getCustomSetting('footer_social_icon_style', 'modern');
$footerLinkHoverEffect = $themeLoader->getCustomSetting('footer_link_hover_effect', 'underline');
$footerContactIconStyle = $themeLoader->getCustomSetting('footer_contact_icon_style', 'boxed');
$footerShowBottomGradient = $themeLoader->getCustomSetting('footer_show_bottom_gradient', true);
$footerShowBackToTop = $themeLoader->getCustomSetting('footer_show_back_to_top', true);
$footerBackToTopText = $themeLoader->getCustomSetting('footer_back_to_top_text', 'Yukarı Çık');
$footerPaddingTop = $themeLoader->getCustomSetting('footer_padding_top', 'large');
$footerPaddingBottom = $themeLoader->getCustomSetting('footer_padding_bottom', 'large');
$footerHeadingUnderline = $themeLoader->getCustomSetting('footer_heading_underline', true);

// Copyright ayarları
$footerCopyrightFormat = $themeLoader->getCustomSetting('footer_copyright_format', 'default');
$footerCopyrightYear = $themeLoader->getCustomSetting('footer_copyright_year', 'auto');
$footerCopyrightYearManual = $themeLoader->getCustomSetting('footer_copyright_year_manual', '');
$footerCopyrightCompany = $themeLoader->getCustomSetting('footer_copyright_company', '');
$footerCopyrightCustom = $themeLoader->getCustomSetting('footer_copyright_custom', '');

// Padding class'ları
$paddingTopClass = [
    'small' => 'py-12',
    'medium' => 'py-16',
    'large' => 'py-16 lg:py-20'
];
$paddingTop = $paddingTopClass[$footerPaddingTop] ?? 'py-16 lg:py-20';

$paddingBottomClass = [
    'small' => 'mt-12',
    'medium' => 'mt-14',
    'large' => 'mt-16'
];
$paddingBottom = $paddingBottomClass[$footerPaddingBottom] ?? 'mt-16';

// Footer gösterilmiyorsa çık
if (!$footerShow) {
    return;
}

// Site ayarları
$siteName = get_option('site_name', 'Site Adı');
$siteDescription = get_option('site_description', 'Modern ve profesyonel web çözümleri.');
$siteLogo = $themeLoader->getLogo();
$logoWidth = $themeLoader->getLogoWidth();
$logoHeight = $themeLoader->getLogoHeight();

// Şirket bilgileri
$companyName = get_option('company_name', get_option('site_name', ''));
$companyEmail = get_option('company_email', get_option('contact_email', ''));
$companyPhone = get_option('company_phone', get_option('contact_phone', ''));
$companyAddress = get_option('company_address', get_option('contact_address', ''));

// Sosyal medya
$socialFacebook = get_option('social_facebook', '');
$socialTwitter = get_option('social_twitter', '');
$socialInstagram = get_option('social_instagram', '');
$socialLinkedin = get_option('social_linkedin', '');
$socialYoutube = get_option('social_youtube', '');

// Footer menüsü
$footerMenu = get_menu('footer');
$footerMenuItems = $footerMenu['items'] ?? [];

// İkinci footer menüsü (varsa)
$footerMenu2 = get_menu('footer-2');
$footerMenu2Items = $footerMenu2['items'] ?? [];

// Kolon içerik kontrolü
$hasSocialLinks = $footerShowSocial && ($socialFacebook || $socialTwitter || $socialInstagram || $socialLinkedin || $socialYoutube);
$hasMenu = $footerShowMenu && !empty($footerMenuItems);
$hasMenu2 = !empty($footerMenu2Items);
$hasContact = $footerShowContact && ($companyEmail || $companyPhone || $companyAddress);

// Renk hesaplamaları
$primaryColor = 'var(--color-primary, #3b82f6)';
$secondaryColor = 'var(--color-secondary, #8b5cf6)';

?>

<footer class="footer-modern relative overflow-hidden" style="background-color: <?php echo esc_attr($footerBgColor); ?>; color: <?php echo esc_attr($footerTextColor); ?>;">
    <!-- Gradient Overlay -->
    <?php if ($footerShowGradientOverlay): ?>
    <div class="footer-gradient-overlay"></div>
    <?php endif; ?>
    
    <!-- Grid Pattern -->
    <?php if ($footerShowGridPattern): ?>
    <div class="footer-grid-pattern"></div>
    <?php endif; ?>
    
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 <?php echo esc_attr($paddingTop); ?>">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-12">
            <!-- Logo ve Açıklama -->
            <div class="lg:col-span-1">
                <a href="/" class="inline-block mb-6 group">
                    <?php if (!empty($siteLogo)): ?>
                        <?php
                        $logoAspectRatio = $logoWidth && $logoHeight ? ($logoWidth / $logoHeight) : 2.5;
                        $maxDisplayHeight = 45;
                        $maxDisplayWidth = round($maxDisplayHeight * $logoAspectRatio);
                        ?>
                        <img src="<?php echo esc_url($siteLogo); ?>" 
                             alt="<?php echo esc_attr($siteName); ?>" 
                             class="h-11 w-auto object-contain transition-transform duration-300 group-hover:scale-105"
                             width="<?php echo $maxDisplayWidth; ?>"
                             height="<?php echo $maxDisplayHeight; ?>"
                             loading="lazy">
                    <?php else: ?>
                        <div class="w-12 h-12 bg-gradient-to-br from-primary via-secondary to-accent rounded-xl flex items-center justify-center shadow-lg shadow-primary/20 group-hover:shadow-primary/30 transition-all duration-300">
                            <span class="text-white font-bold text-xl"><?php echo substr($siteName, 0, 1); ?></span>
                        </div>
                    <?php endif; ?>
                </a>
                <?php if (!empty($siteDescription)): ?>
                <p class="text-gray-400 text-sm leading-relaxed mb-8 max-w-xs">
                    <?php echo esc_html($siteDescription); ?>
                </p>
                <?php endif; ?>
                
                <?php if ($hasSocialLinks): ?>
                <div class="flex items-center gap-3 flex-wrap">
                    <?php if ($socialFacebook): ?>
                    <a href="<?php echo esc_url($socialFacebook); ?>" target="_blank" rel="noopener noreferrer" class="footer-social-icon footer-social-<?php echo esc_attr($footerSocialIconStyle); ?>" aria-label="Facebook">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($socialTwitter): ?>
                    <a href="<?php echo esc_url($socialTwitter); ?>" target="_blank" rel="noopener noreferrer" class="footer-social-icon footer-social-<?php echo esc_attr($footerSocialIconStyle); ?>" aria-label="X/Twitter">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                        </svg>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($socialInstagram): ?>
                    <a href="<?php echo esc_url($socialInstagram); ?>" target="_blank" rel="noopener noreferrer" class="footer-social-icon footer-social-<?php echo esc_attr($footerSocialIconStyle); ?>" aria-label="Instagram">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($socialLinkedin): ?>
                    <a href="<?php echo esc_url($socialLinkedin); ?>" target="_blank" rel="noopener noreferrer" class="footer-social-icon footer-social-<?php echo esc_attr($footerSocialIconStyle); ?>" aria-label="LinkedIn">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($socialYoutube): ?>
                    <a href="<?php echo esc_url($socialYoutube); ?>" target="_blank" rel="noopener noreferrer" class="footer-social-icon footer-social-<?php echo esc_attr($footerSocialIconStyle); ?>" aria-label="YouTube">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                        </svg>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Hızlı Linkler -->
            <?php if ($hasMenu): ?>
            <div class="lg:col-span-1">
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-6 relative inline-block">
                    <?php echo esc_html($footerMenu['name'] ?? 'Hızlı Linkler'); ?>
                    <?php if ($footerHeadingUnderline): ?>
                    <span class="absolute -bottom-1 left-0 w-8 h-0.5 bg-gradient-to-r from-primary to-secondary rounded-full"></span>
                    <?php endif; ?>
                </h3>
                <ul class="space-y-3.5">
                    <?php foreach ($footerMenuItems as $item): ?>
                    <li>
                        <a href="<?php echo esc_url($item['url']); ?>" 
                           class="footer-link footer-link-<?php echo esc_attr($footerLinkHoverEffect); ?> text-gray-400 hover:text-white transition-all duration-300 text-sm inline-flex items-center gap-2 group"
                           <?php echo ($item['target'] ?? '') === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                            <?php if ($footerLinkHoverEffect === 'dot'): ?>
                            <span class="w-1.5 h-1.5 rounded-full bg-primary opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                            <?php endif; ?>
                            <span><?php echo esc_html($item['title']); ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <!-- Ek Linkler -->
            <?php if ($hasMenu2): ?>
            <div class="lg:col-span-1">
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-6 relative inline-block">
                    <?php echo esc_html($footerMenu2['name'] ?? 'Kaynaklar'); ?>
                    <?php if ($footerHeadingUnderline): ?>
                    <span class="absolute -bottom-1 left-0 w-8 h-0.5 bg-gradient-to-r from-primary to-secondary rounded-full"></span>
                    <?php endif; ?>
                </h3>
                <ul class="space-y-3.5">
                    <?php foreach ($footerMenu2Items as $item): ?>
                    <li>
                        <a href="<?php echo esc_url($item['url']); ?>" 
                           class="footer-link footer-link-<?php echo esc_attr($footerLinkHoverEffect); ?> text-gray-400 hover:text-white transition-all duration-300 text-sm inline-flex items-center gap-2 group"
                           <?php echo ($item['target'] ?? '') === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                            <?php if ($footerLinkHoverEffect === 'dot'): ?>
                            <span class="w-1.5 h-1.5 rounded-full bg-primary opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                            <?php endif; ?>
                            <span><?php echo esc_html($item['title']); ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <!-- İletişim -->
            <?php if ($hasContact): ?>
            <div class="lg:col-span-1">
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-6 relative inline-block">
                    İletişim
                    <?php if ($footerHeadingUnderline): ?>
                    <span class="absolute -bottom-1 left-0 w-8 h-0.5 bg-gradient-to-r from-primary to-secondary rounded-full"></span>
                    <?php endif; ?>
                </h3>
                <div class="space-y-4">
                    <?php if ($companyAddress): ?>
                    <div class="flex items-start gap-3 group">
                        <div class="footer-contact-icon footer-contact-<?php echo esc_attr($footerContactIconStyle); ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <span class="text-gray-400 text-sm leading-relaxed group-hover:text-gray-300 transition-colors"><?php echo esc_html($companyAddress); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($companyPhone): ?>
                    <div class="flex items-center gap-3 group">
                        <div class="footer-contact-icon footer-contact-<?php echo esc_attr($footerContactIconStyle); ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $companyPhone)); ?>" class="text-gray-400 hover:text-white transition-colors text-sm">
                            <?php echo esc_html($companyPhone); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($companyEmail): ?>
                    <div class="flex items-center gap-3 group">
                        <div class="footer-contact-icon footer-contact-<?php echo esc_attr($footerContactIconStyle); ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <a href="mailto:<?php echo esc_attr($companyEmail); ?>" class="text-gray-400 hover:text-white transition-colors text-sm">
                            <?php echo esc_html($companyEmail); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Alt Çizgi -->
        <div class="border-t border-white/10 <?php echo esc_attr($paddingBottom); ?> pt-8 relative">
            <!-- Gradient Line -->
            <?php if ($footerShowBottomGradient): ?>
            <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-primary/50 to-transparent"></div>
            <?php endif; ?>
            
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <!-- Copyright -->
                <div class="text-sm text-gray-400">
                    <?php
                    $copyrightYear = ($footerCopyrightYear === 'manual' && !empty($footerCopyrightYearManual)) 
                        ? $footerCopyrightYearManual 
                        : date('Y');
                    $copyrightCompany = !empty($footerCopyrightCompany) ? $footerCopyrightCompany : $siteName;
                    
                    if ($footerCopyrightFormat === 'custom' && !empty($footerCopyrightCustom)) {
                        $copyrightText = str_replace(['{year}', '{company}'], [$copyrightYear, $copyrightCompany], $footerCopyrightCustom);
                    } else {
                        $copyrightText = '© ' . $copyrightYear . ' ' . $copyrightCompany;
                        if (!empty($footerCopyrightText)) {
                            $copyrightText .= '. ' . $footerCopyrightText;
                        }
                    }
                    echo esc_html($copyrightText);
                    ?>
                </div>
                
            </div>
        </div>
    </div>
</footer>

<style>
.footer-modern {
    position: relative;
    background: linear-gradient(180deg, #0a0a0f 0%, #0f0f1a 100%);
}

.footer-gradient-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 100%;
    background: 
        radial-gradient(ellipse 80% 50% at 50% 0%, rgba(59, 130, 246, 0.08) 0%, transparent 60%),
        radial-gradient(ellipse 70% 50% at 50% 100%, rgba(139, 92, 246, 0.06) 0%, transparent 60%);
    pointer-events: none;
    z-index: 1;
}

.footer-grid-pattern {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: 
        linear-gradient(rgba(59, 130, 246, 0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(59, 130, 246, 0.03) 1px, transparent 1px);
    background-size: 50px 50px;
    opacity: 0.4;
    pointer-events: none;
    z-index: 1;
}

.footer-social-icon {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.7);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.footer-social-icon::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, <?php echo $primaryColor; ?>, <?php echo $secondaryColor; ?>);
    opacity: 0;
    transition: opacity 0.4s ease;
    z-index: -1;
}

.footer-social-icon:hover {
    border-color: transparent;
    color: #fff;
    transform: translateY(-4px) scale(1.05);
    box-shadow: 
        0 10px 30px -5px rgba(59, 130, 246, 0.4),
        0 0 0 1px rgba(255, 255, 255, 0.1);
}

.footer-social-icon:hover::before {
    opacity: 1;
}

/* Sosyal Medya İkon Stilleri */
.footer-social-modern {
    /* Varsayılan stil - yukarıdaki stiller */
}

.footer-social-minimal {
    background: transparent;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 50%;
}

.footer-social-minimal:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: <?php echo $primaryColor; ?>;
}

.footer-social-rounded {
    border-radius: 50%;
}

/* Link Hover Efektleri */
.footer-link {
    position: relative;
}

.footer-link-underline::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 1px;
    background: linear-gradient(90deg, <?php echo $primaryColor; ?>, <?php echo $secondaryColor; ?>);
    transition: width 0.3s ease;
}

.footer-link-underline:hover::after {
    width: 100%;
}

.footer-link-dot {
    /* Dot efekti zaten HTML'de var */
}

.footer-link-highlight {
    padding: 2px 6px;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.footer-link-highlight:hover {
    background: rgba(59, 130, 246, 0.15);
    color: <?php echo $primaryColor; ?> !important;
}

/* İletişim İkon Stilleri */
.footer-contact-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    color: <?php echo $primaryColor; ?>;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.footer-contact-boxed {
    background: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.2);
}

.group:hover .footer-contact-boxed {
    background: rgba(59, 130, 246, 0.2);
    border-color: rgba(59, 130, 246, 0.4);
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.footer-contact-minimal {
    background: transparent;
    border: none;
    color: rgba(255, 255, 255, 0.6);
}

.group:hover .footer-contact-minimal {
    color: <?php echo $primaryColor; ?>;
    transform: scale(1.1);
}

.footer-contact-gradient {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(139, 92, 246, 0.2));
    border: 1px solid rgba(59, 130, 246, 0.3);
}

.group:hover .footer-contact-gradient {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.3), rgba(139, 92, 246, 0.3));
    border-color: rgba(59, 130, 246, 0.5);
    transform: scale(1.05);
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .footer-modern {
        padding-top: 3rem;
        padding-bottom: 3rem;
    }
    
    .footer-social-icon {
        width: 40px;
        height: 40px;
    }
    
    .footer-contact-icon {
        width: 36px;
        height: 36px;
    }
}
</style>

