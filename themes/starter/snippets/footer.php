<?php
/**
 * Starter Theme - Footer
 * Modern ve şık footer tasarımı
 */

// Tema ayarları
$footerShow = $themeLoader->getCustomSetting('footer_show', true);
$footerBgColor = $themeLoader->getCustomSetting('footer_bg_color', '#0f172a');
$footerTextColor = $themeLoader->getCustomSetting('footer_text_color', '#ffffff');
$footerCopyrightText = $themeLoader->getCustomSetting('footer_copyright_text', 'Tüm hakları saklıdır.');
$footerShowSocial = $themeLoader->getCustomSetting('footer_show_social', true);
$footerShowMenu = $themeLoader->getCustomSetting('footer_show_menu', true);
$footerShowContact = $themeLoader->getCustomSetting('footer_show_contact', true);
$footerShowNewsletter = $themeLoader->getCustomSetting('footer_show_newsletter', false);

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
$primaryColor = 'var(--color-primary, #6366f1)';
?>

<style>
.footer-gradient {
    background: linear-gradient(180deg, <?php echo esc_attr($footerBgColor); ?> 0%, <?php echo esc_attr(adjustBrightness($footerBgColor, -15)); ?> 100%);
}
.footer-link {
    color: rgba(255, 255, 255, 0.6);
    transition: all 0.2s ease;
}
.footer-link:hover {
    color: #fff;
    transform: translateX(2px);
}
.footer-social-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: rgba(255, 255, 255, 0.7);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.footer-social-icon:hover {
    background: <?php echo $primaryColor; ?>;
    border-color: <?php echo $primaryColor; ?>;
    color: #fff;
    transform: translateY(-3px);
    box-shadow: 0 8px 25px -5px rgba(99, 102, 241, 0.4);
}
.footer-contact-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 10px 0;
}
.footer-contact-icon {
    width: 36px;
    height: 36px;
    min-width: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(139, 92, 246, 0.15) 100%);
    color: <?php echo $primaryColor; ?>;
}
.footer-newsletter-input {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    padding: 12px 16px;
    color: #fff;
    width: 100%;
    transition: all 0.2s;
}
.footer-newsletter-input:focus {
    outline: none;
    border-color: <?php echo $primaryColor; ?>;
    background: rgba(255, 255, 255, 0.08);
}
.footer-newsletter-input::placeholder {
    color: rgba(255, 255, 255, 0.4);
}
.footer-newsletter-btn {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    border: none;
    border-radius: 10px;
    padding: 12px 24px;
    color: #fff;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    white-space: nowrap;
}
.footer-newsletter-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px -5px rgba(99, 102, 241, 0.5);
}
.footer-divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
}
.footer-bottom-link {
    color: rgba(255, 255, 255, 0.5);
    font-size: 13px;
    transition: color 0.2s;
}
.footer-bottom-link:hover {
    color: #fff;
}
</style>

<?php
// Renk ayarlama fonksiyonu
function adjustBrightness($hex, $percent) {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    
    $r = max(0, min(255, $r + ($r * $percent / 100)));
    $g = max(0, min(255, $g + ($g * $percent / 100)));
    $b = max(0, min(255, $b + ($b * $percent / 100)));
    
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}
?>

<footer class="footer-gradient relative overflow-hidden" style="color: <?php echo esc_attr($footerTextColor); ?>;">
    
    <!-- Dekoratif arka plan -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 rounded-full opacity-[0.03]" style="background: <?php echo $primaryColor; ?>; filter: blur(80px);"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 rounded-full opacity-[0.03]" style="background: <?php echo $primaryColor; ?>; filter: blur(80px);"></div>
    </div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Ana İçerik -->
        <div class="pt-16 pb-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-8">
                
                <!-- Kolon 1: Marka & Açıklama -->
                <div class="lg:col-span-4 space-y-6">
                <!-- Logo -->
                    <div class="flex items-center gap-3">
                    <?php if (!empty($siteLogo)): ?>
                            <img src="<?php echo esc_url($siteLogo); ?>" alt="<?php echo esc_attr($siteName); ?>" class="h-10 w-auto object-contain brightness-0 invert">
                    <?php else: ?>
                            <div class="w-10 h-10 flex items-center justify-center rounded-xl" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                            </svg>
                        </div>
                    <?php endif; ?>
                        <span class="text-xl font-bold text-white tracking-tight"><?php echo esc_html($siteName); ?></span>
                </div>
                
                    <!-- Açıklama -->
                <?php if (!empty($siteDescription)): ?>
                    <p class="text-sm leading-relaxed max-w-sm" style="color: rgba(255, 255, 255, 0.6);">
                    <?php echo esc_html($siteDescription); ?>
                </p>
                <?php endif; ?>
                    
                    <!-- Sosyal Medya -->
                    <?php if ($hasSocialLinks): ?>
                    <div class="flex items-center gap-3 pt-2">
                        <?php if ($socialFacebook): ?>
                        <a href="<?php echo esc_url($socialFacebook); ?>" target="_blank" rel="noopener" class="footer-social-icon" aria-label="Facebook">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($socialTwitter): ?>
                        <a href="<?php echo esc_url($socialTwitter); ?>" target="_blank" rel="noopener" class="footer-social-icon" aria-label="X/Twitter">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($socialInstagram): ?>
                        <a href="<?php echo esc_url($socialInstagram); ?>" target="_blank" rel="noopener" class="footer-social-icon" aria-label="Instagram">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($socialLinkedin): ?>
                        <a href="<?php echo esc_url($socialLinkedin); ?>" target="_blank" rel="noopener" class="footer-social-icon" aria-label="LinkedIn">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($socialYoutube): ?>
                        <a href="<?php echo esc_url($socialYoutube); ?>" target="_blank" rel="noopener" class="footer-social-icon" aria-label="YouTube">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                            </svg>
                        </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
                
                <!-- Kolon 2: Hızlı Linkler -->
                <?php if ($hasMenu): ?>
                <div class="lg:col-span-2">
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-5">
                        <?php echo esc_html($footerMenu['name'] ?? 'Hızlı Linkler'); ?>
                    </h3>
                    <ul class="space-y-3">
                        <?php foreach ($footerMenuItems as $item): ?>
                        <li>
                            <a href="<?php echo esc_url($item['url']); ?>" 
                               class="footer-link text-sm inline-block"
                               <?php echo ($item['target'] ?? '') === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                                <?php echo esc_html($item['title']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <!-- Kolon 3: Ek Linkler (varsa) -->
                <?php if ($hasMenu2): ?>
                <div class="lg:col-span-2">
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-5">
                        <?php echo esc_html($footerMenu2['name'] ?? 'Kaynaklar'); ?>
                    </h3>
                    <ul class="space-y-3">
                        <?php foreach ($footerMenu2Items as $item): ?>
                        <li>
                            <a href="<?php echo esc_url($item['url']); ?>" 
                               class="footer-link text-sm inline-block"
                               <?php echo ($item['target'] ?? '') === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                                <?php echo esc_html($item['title']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php elseif (!$hasMenu): ?>
                <!-- Menü yoksa boşluk bırak -->
                <div class="lg:col-span-2"></div>
                <?php endif; ?>
                
                <!-- Kolon 4: İletişim -->
                <?php if ($hasContact): ?>
                <div class="lg:col-span-4">
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-5">İletişim</h3>
                    
                    <div class="space-y-1">
                        <?php if ($companyAddress): ?>
                            <a href="https://maps.google.com/?q=<?php echo urlencode($companyAddress); ?>" 
                               target="_blank" 
                               rel="noopener"
                           class="footer-contact-item group">
                            <div class="footer-contact-icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <span class="text-sm group-hover:text-white transition-colors" style="color: rgba(255, 255, 255, 0.6);">
                                <?php echo esc_html($companyAddress); ?>
                            </span>
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($companyPhone): ?>
                            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $companyPhone)); ?>" 
                           class="footer-contact-item group">
                            <div class="footer-contact-icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <span class="text-sm group-hover:text-white transition-colors" style="color: rgba(255, 255, 255, 0.6);">
                                <?php echo esc_html($companyPhone); ?>
                            </span>
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($companyEmail): ?>
                            <a href="mailto:<?php echo esc_attr($companyEmail); ?>" 
                           class="footer-contact-item group">
                            <div class="footer-contact-icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <span class="text-sm group-hover:text-white transition-colors" style="color: rgba(255, 255, 255, 0.6);">
                                <?php echo esc_html($companyEmail); ?>
                            </span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
        
        <!-- Alt Çizgi -->
        <div class="footer-divider"></div>
        
        <!-- Alt Kısım -->
        <div class="py-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                
                <!-- Copyright -->
                <div class="text-sm" style="color: rgba(255, 255, 255, 0.5);">
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
            
                <!-- Alt Linkler -->
                <?php
                // Footer alt linklerini tema ayarlarından al
                $footerBottomLinks = $themeLoader->getCustomSetting('footer_bottom_links', []);
                
                // Eğer string ise decode et (eski veriler için)
                if (is_string($footerBottomLinks)) {
                    $footerBottomLinks = json_decode($footerBottomLinks, true) ?: [];
                }
                
                // Eğer array değilse boş array yap
                if (!is_array($footerBottomLinks)) {
                    $footerBottomLinks = [];
                }
                
                // Eğer ayar yoksa varsayılan linkler
                if (empty($footerBottomLinks)) {
                    $footerBottomLinks = [
                        ['text' => 'Gizlilik Politikası', 'url' => '/gizlilik-politikasi'],
                        ['text' => 'Kullanım Şartları', 'url' => '/kullanim-kosullari'],
                        ['text' => 'Çerez Politikası', 'url' => '/cerez-politikasi']
                    ];
                }
                
                if (!empty($footerBottomLinks)):
                ?>
                <div class="flex flex-wrap items-center justify-center gap-6">
                <?php 
                // Agreement model'ini yükle
                $agreementModel = null;
                if (!class_exists('Agreement')) {
                    // Model dosyasını yükle (proje root'undan)
                    $modelPath = __DIR__ . '/../../../../app/models/Agreement.php';
                    if (file_exists($modelPath)) {
                        require_once $modelPath;
                    }
                }
                if (class_exists('Agreement')) {
                    try {
                        $agreementModel = new Agreement();
                    } catch (Exception $e) {
                        // Model yüklenemezse devam et
                        error_log("Agreement model error: " . $e->getMessage());
                    }
                }
                
                foreach ($footerBottomLinks as $link):
                        $linkText = $link['text'] ?? '';
                        $linkUrl = $link['url'] ?? '#';
                        $showLink = true;
                        
                        // Eğer agreement_id varsa, her zaman güncel sözleşme bilgilerini çek
                        if (!empty($link['agreement_id']) && isset($agreementModel)) {
                            $agreement = $agreementModel->find($link['agreement_id']);
                            
                            // Sözleşme bulundu ve yayınlanmışsa
                            if ($agreement && ($agreement['status'] ?? 'published') === 'published') {
                                // Her zaman güncel slug'ı kullan (sözleşme değişikliklerini yansıt)
                                if (!empty($agreement['slug'])) {
                                    $linkUrl = '/' . $agreement['slug'];
                                }
                                
                                // Her zaman güncel başlığı kullan (sözleşme başlığı değişikliklerini yansıt)
                                // Kullanıcı özel text girmişse bile, sözleşme ID'si varsa güncel başlığı kullan
                                $linkText = $agreement['title'];
                            } else {
                                // Sözleşme bulunamadı veya yayınlanmamış, linki gösterme
                                $showLink = false;
                            }
                        }
                        
                        // Link metni ve URL varsa göster
                        if ($showLink && !empty($linkText) && !empty($linkUrl)):
                    ?>
                    <a href="<?php echo esc_url($linkUrl); ?>" class="footer-bottom-link">
                        <?php echo esc_html($linkText); ?>
                    </a>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
        
    </div>
</footer>
