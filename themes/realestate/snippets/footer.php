<?php
/**
 * Real Estate Theme - Footer
 */

$footerShow = $themeLoader->getCustomSetting('footer_show', true);
$footerBgColor = $themeLoader->getCustomSetting('footer_bg_color', '#1e293b');
$footerTextColor = $themeLoader->getCustomSetting('footer_text_color', '#ffffff');
$footerShowSocial = $themeLoader->getCustomSetting('footer_show_social', true);
$footerShowMenu = $themeLoader->getCustomSetting('footer_show_menu', true);
$footerShowContact = $themeLoader->getCustomSetting('footer_show_contact', true);

$footerCopyrightFormat = $themeLoader->getCustomSetting('footer_copyright_format', 'default');
$footerCopyrightYear = $themeLoader->getCustomSetting('footer_copyright_year', 'auto');
$footerCopyrightYearManual = $themeLoader->getCustomSetting('footer_copyright_year_manual', '');
$footerCopyrightCompany = $themeLoader->getCustomSetting('footer_copyright_company', '');
$footerCopyrightText = __($themeLoader->getCustomSetting('footer_copyright_text', __('All rights reserved.')));

if (!$footerShow) {
    return;
}

$siteName = __(get_option('site_name', __('Real Estate')));
$siteLogo = $themeLoader->getLogo();

$companyEmail = get_option('company_email', get_option('contact_email', ''));
$companyPhone = get_option('company_phone', get_option('contact_phone', ''));
$companyAddress = __(get_option('company_address', get_option('contact_address', '')));

$socialFacebook = get_option('social_facebook', '');
$socialTwitter = get_option('social_twitter', '');
$socialInstagram = get_option('social_instagram', '');
$socialLinkedin = get_option('social_linkedin', '');
$socialYoutube = get_option('social_youtube', '');

$footerMenu = get_menu('footer');
$footerMenuItems = $footerMenu['items'] ?? [];

$footerColumns = $themeLoader->getCustomSetting('footer_columns', '4');

// Footer başlıkları - customize'dan alınabilir
$footerMenuTitle = __($themeLoader->getCustomSetting('footer_menu_title', __('Quick Links')));
$footerContactTitle = __($themeLoader->getCustomSetting('footer_contact_title', __('Contact')));
$footerPostsTitle = __($themeLoader->getCustomSetting('footer_posts_title', __('Recent Posts')));

// Son yazıları getir
$recentPosts = [];
try {
    // Model ve Database sınıflarının yüklü olduğundan emin ol
    if (!class_exists('Model')) {
        require_once __DIR__ . '/../../../core/Model.php';
    }
    if (!class_exists('Database')) {
        require_once __DIR__ . '/../../../core/Database.php';
    }
    if (!class_exists('Post')) {
        require_once __DIR__ . '/../../../app/models/Post.php';
    }
    $postModel = new Post();
    $recentPosts = $postModel->getRecent(5);
} catch (Exception $e) {
    // Post modeli yüklenemezse boş bırak
    $recentPosts = [];
}
?>

<footer class="relative" style="background-color: <?php echo esc_attr($footerBgColor); ?>; color: <?php echo esc_attr($footerTextColor); ?>;">
    <div class="container mx-auto px-4 lg:px-6 py-12 lg:py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-<?php echo esc_attr($footerColumns); ?> gap-8 lg:gap-12">
            <!-- Company Info -->
            <div>
                <?php if ($siteLogo): ?>
                    <img src="<?php echo esc_url($siteLogo); ?>" alt="<?php echo esc_attr($siteName); ?>" class="h-10 mb-4">
                <?php else: ?>
                    <h3 class="text-xl font-bold mb-4"><?php echo esc_html($siteName); ?></h3>
                <?php endif; ?>
                <p class="text-gray-300 mb-4"><?php echo esc_html(__(get_option('site_description', __('Your trusted real estate partner.')))); ?></p>
                
                <?php if ($footerShowSocial && ($socialFacebook || $socialTwitter || $socialInstagram || $socialLinkedin || $socialYoutube)): ?>
                    <div class="flex space-x-4">
                        <?php if ($socialFacebook): ?>
                            <a href="<?php echo esc_url($socialFacebook); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-300 hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                        <?php endif; ?>
                        <?php if ($socialTwitter): ?>
                            <a href="<?php echo esc_url($socialTwitter); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-300 hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                            </a>
                        <?php endif; ?>
                        <?php if ($socialInstagram): ?>
                            <a href="<?php echo esc_url($socialInstagram); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-300 hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                        <?php endif; ?>
                        <?php if ($socialLinkedin): ?>
                            <a href="<?php echo esc_url($socialLinkedin); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-300 hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                            </a>
                        <?php endif; ?>
                        <?php if ($socialYoutube): ?>
                            <a href="<?php echo esc_url($socialYoutube); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-300 hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Quick Links -->
            <?php if ($footerShowMenu && !empty($footerMenuItems)): ?>
            <div>
                <h4 class="text-lg font-semibold mb-4"><?php echo esc_html($footerMenuTitle); ?></h4>
                <ul class="space-y-2">
                    <?php foreach ($footerMenuItems as $item): ?>
                        <li>
                            <a href="<?php echo esc_url(function_exists('get_localized_menu_url') ? get_localized_menu_url($item['url']) : $item['url']); ?>" 
                               class="text-gray-300 hover:text-white transition-colors"
                               <?php echo ($item['target'] ?? '') === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                                <?php echo esc_html(__($item['title'])); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Contact Info -->
            <?php if ($footerShowContact && ($companyEmail || $companyPhone || $companyAddress)): ?>
            <div>
                <h4 class="text-lg font-semibold mb-4"><?php echo esc_html($footerContactTitle); ?></h4>
                <ul class="space-y-3">
                    <?php if ($companyPhone): ?>
                        <li class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-gray-300 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <a href="tel:<?php echo esc_attr($companyPhone); ?>" class="text-gray-300 hover:text-white transition-colors"><?php echo esc_html($companyPhone); ?></a>
                        </li>
                    <?php endif; ?>
                    <?php if ($companyEmail): ?>
                        <li class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-gray-300 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <a href="mailto:<?php echo esc_attr($companyEmail); ?>" class="text-gray-300 hover:text-white transition-colors"><?php echo esc_html($companyEmail); ?></a>
                        </li>
                    <?php endif; ?>
                    <?php if ($companyAddress): ?>
                        <li class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-gray-300 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-gray-300"><?php echo esc_html($companyAddress); ?></span>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Recent Posts -->
            <?php if ($footerColumns == '4' && !empty($recentPosts)): ?>
            <div>
                <h4 class="text-lg font-semibold mb-4"><?php echo esc_html($footerPostsTitle); ?></h4>
                <ul class="space-y-2">
                    <?php foreach ($recentPosts as $post): ?>
                        <li>
                            <a href="<?php echo esc_url(function_exists('localized_url') ? localized_url('/blog/' . $post['slug']) : site_url('/blog/' . $post['slug'])); ?>" 
                               class="text-gray-300 hover:text-white transition-colors block">
                                <?php echo esc_html(__($post['title'])); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>

        <!-- Copyright -->
        <div class="border-t border-gray-700 mt-12 pt-8 text-center text-gray-400 text-sm">
            <?php
            $year = ($footerCopyrightYear === 'auto') ? date('Y') : ($footerCopyrightYearManual ?: date('Y'));
            $company = $footerCopyrightCompany ?: $siteName;
            
            if ($footerCopyrightFormat === 'custom' && !empty($footerCopyrightCustom)) {
                echo esc_html($footerCopyrightCustom);
            } else {
                echo '&copy; ' . esc_html($year) . ' ' . esc_html($company) . '. ' . esc_html($footerCopyrightText);
            }
            ?>
        </div>
    </div>
</footer>
