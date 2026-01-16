<?php
/**
 * Real Estate Theme - Header
 */

$headerStyle = $themeLoader->getSetting('style', 'fixed', 'header');
$headerBgColor = $themeLoader->getSetting('bg_color', '#ffffff', 'header');
$headerTextColor = $themeLoader->getSetting('text_color', '#1e293b', 'header');

$showSearchValue = $themeLoader->getSetting('show_search', true, 'header');
$showSearch = ($showSearchValue === '1' || $showSearchValue === true || $showSearchValue === 'true');

$showCtaValue = $themeLoader->getSetting('show_cta', true, 'header');
$showCta = !($showCtaValue === '0' || $showCtaValue === false || $showCtaValue === 'false');

$ctaText = __($themeLoader->getSetting('cta_text', __('List Your Property'), 'header'));
$ctaLinkRaw = $themeLoader->getSetting('cta_link', '/contact', 'header');
$ctaLink = function_exists('localized_url') ? localized_url($ctaLinkRaw) : $ctaLinkRaw;

$isFixed = in_array($headerStyle, ['fixed', 'transparent']);
$isTransparent = $headerStyle === 'transparent';

$headerMenu = get_menu('header');
$menuItems = $headerMenu['items'] ?? [];

$siteName = __(get_option('site_name', __('Real Estate')));
$siteLogo = $themeLoader->getLogo();
?>

<header class="<?php echo $isFixed ? 'fixed' : 'relative'; ?> top-0 left-0 right-0 z-50 transition-all duration-300 <?php echo $isTransparent ? 'bg-transparent' : ''; ?>" 
        style="<?php echo !$isTransparent ? 'background-color: ' . esc_attr($headerBgColor) . ';' : ''; ?> color: <?php echo esc_attr($headerTextColor); ?>;"
        id="main-header">
    <nav class="container mx-auto px-4 lg:px-6">
        <div class="flex items-center justify-between h-16 lg:h-20">
            <!-- Logo -->
            <div class="flex-shrink-0">
                <a href="<?php echo function_exists('localized_url') ? localized_url('/') : site_url('/'); ?>" class="flex items-center space-x-2">
                    <?php if ($siteLogo): ?>
                        <img src="<?php echo esc_url($siteLogo); ?>" alt="<?php echo esc_attr($siteName); ?>" class="h-8 lg:h-10 w-auto">
                    <?php else: ?>
                        <span class="text-xl lg:text-2xl font-bold" style="color: <?php echo esc_attr($headerTextColor); ?>;"><?php echo esc_html($siteName); ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden lg:flex items-center space-x-8">
                <?php foreach ($menuItems as $item): ?>
                    <?php if (empty($item['children'])): ?>
                        <a href="<?php echo esc_url(function_exists('get_localized_menu_url') ? get_localized_menu_url($item['url']) : $item['url']); ?>" 
                           class="font-medium hover:text-primary transition-colors"
                           style="color: <?php echo esc_attr($headerTextColor); ?>;"
                           <?php echo ($item['target'] ?? '') === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                            <?php echo esc_html(__($item['title'])); ?>
                        </a>
                    <?php else: ?>
                        <div class="relative group">
                            <a href="<?php echo esc_url(function_exists('get_localized_menu_url') ? get_localized_menu_url($item['url']) : $item['url']); ?>" 
                               class="font-medium hover:text-primary transition-colors flex items-center gap-1"
                               style="color: <?php echo esc_attr($headerTextColor); ?>;">
                                <?php echo esc_html(__($item['title'])); ?>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </a>
                            <div class="absolute top-full left-0 pt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                <div class="bg-white rounded-lg shadow-lg border border-gray-100 py-2 min-w-[200px]">
                                    <?php foreach ($item['children'] as $child): ?>
                                        <a href="<?php echo esc_url(function_exists('get_localized_menu_url') ? get_localized_menu_url($child['url']) : $child['url']); ?>" 
                                           class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-primary transition-colors"
                                           <?php echo ($child['target'] ?? '') === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                                            <?php echo esc_html(__($child['title'])); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Right Side Actions -->
            <div class="flex items-center space-x-4">
                <!-- Desktop CTA Button -->
                <?php if ($showCta): ?>
                    <a href="<?php echo esc_url($ctaLink); ?>" 
                       class="hidden lg:inline-block px-6 py-2 bg-primary text-white rounded-lg font-medium hover:bg-opacity-90 transition-all">
                        <?php echo esc_html($ctaText); ?>
                    </a>
                <?php endif; ?>

                <!-- Mobile Actions: Search + Menu -->
                <div class="flex items-center space-x-2 lg:hidden">
                    <?php if ($showSearch): ?>
                        <button id="search-toggle" class="p-2 hover:bg-gray-100 rounded-lg transition-colors" aria-label="<?php echo esc_attr__('Search'); ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: <?php echo esc_attr($headerTextColor); ?>;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                    <?php endif; ?>

                    <!-- Mobile Menu Toggle -->
                    <button id="mobile-menu-toggle" 
                            class="p-2 hover:bg-gray-100 rounded-lg transition-colors" 
                            aria-label="<?php echo esc_attr__('Menu'); ?>"
                            aria-expanded="false"
                            type="button">
                        <svg id="menu-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: <?php echo esc_attr($headerTextColor); ?>;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg id="close-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: <?php echo esc_attr($headerTextColor); ?>;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Desktop Search -->
                <?php if ($showSearch): ?>
                    <button id="search-toggle-desktop" class="hidden lg:block p-2 hover:bg-gray-100 rounded-lg transition-colors" aria-label="<?php echo esc_attr__('Search'); ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: <?php echo esc_attr($headerTextColor); ?>;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="lg:hidden hidden border-t border-gray-200 py-4">
            <?php foreach ($menuItems as $item): ?>
                <a href="<?php echo esc_url(function_exists('get_localized_menu_url') ? get_localized_menu_url($item['url']) : $item['url']); ?>" 
                   class="block px-4 py-2 hover:bg-gray-50 rounded-lg transition-colors"
                   style="color: <?php echo esc_attr($headerTextColor); ?>;"
                   <?php echo ($item['target'] ?? '') === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                    <?php echo esc_html(__($item['title'])); ?>
                </a>
            <?php endforeach; ?>
            <?php if ($showCta): ?>
                <a href="<?php echo esc_url($ctaLink); ?>" 
                   class="block mx-4 mt-4 px-6 py-2 bg-primary text-white rounded-lg font-medium text-center hover:bg-opacity-90 transition-all">
                    <?php echo esc_html($ctaText); ?>
                </a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<?php if ($isFixed): ?>
<div class="h-16 lg:h-20"></div>
<?php endif; ?>

<!-- Search Overlay -->
<?php if ($showSearch): ?>
<div id="search-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="container mx-auto px-4 pt-20">
        <div class="max-w-2xl mx-auto">
            <form action="<?php echo function_exists('localized_url') ? localized_url('/search') : site_url('/search'); ?>" method="get" class="relative">
                <input type="text" 
                       name="q" 
                       placeholder="<?php echo esc_attr__('Search properties...'); ?>" 
                       class="w-full px-6 py-4 text-lg rounded-lg border-0 focus:ring-2 focus:ring-primary"
                       autofocus>
                <button type="submit" class="absolute right-2 top-2 p-2 bg-primary text-white rounded-lg hover:bg-opacity-90">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
    <button id="search-close" class="absolute top-4 right-4 text-white text-2xl hover:text-gray-300">×</button>
</div>
<?php endif; ?>

<script>
    // Header configuration for theme.js
    const header = document.getElementById('main-header');
    if (header) {
        header.dataset.bgColor = '<?php echo esc_js($headerBgColor); ?>';
        header.dataset.transparent = '<?php echo $isTransparent ? 'true' : 'false'; ?>';
        header.dataset.isFixed = '<?php echo $isFixed ? 'true' : 'false'; ?>';
    }
    
    // Mobile Menu Toggle - Inline for immediate execution
    (function() {
        let menuInitialized = false;
        
        function initMobileMenuInline() {
            if (menuInitialized) return;
            
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (!mobileMenuToggle || !mobileMenu) {
                // Retry if elements not found yet
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initMobileMenuInline);
                } else {
                    // Multiple retries for reliability
                    let retries = 0;
                    const maxRetries = 10;
                    const retryInterval = setInterval(function() {
                        retries++;
                        const btn = document.getElementById('mobile-menu-toggle');
                        const menu = document.getElementById('mobile-menu');
                        if (btn && menu) {
                            clearInterval(retryInterval);
                            initMobileMenuInline();
                        } else if (retries >= maxRetries) {
                            clearInterval(retryInterval);
                        }
                    }, 50);
                }
                return;
            }
            
            menuInitialized = true;
            // Mark as initialized for theme.js fallback
            mobileMenuToggle.dataset.menuInitialized = 'true';
            const menuIcon = document.getElementById('menu-icon');
            const closeIcon = document.getElementById('close-icon');
            
            function closeMenu() {
                mobileMenu.classList.add('hidden');
                mobileMenuToggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
                if (menuIcon && closeIcon) {
                    menuIcon.classList.remove('hidden');
                    closeIcon.classList.add('hidden');
                }
            }
            
            function openMenu() {
                mobileMenu.classList.remove('hidden');
                mobileMenuToggle.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
                if (menuIcon && closeIcon) {
                    menuIcon.classList.add('hidden');
                    closeIcon.classList.remove('hidden');
                }
            }
            
            mobileMenuToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (mobileMenu.classList.contains('hidden')) {
                    openMenu();
                } else {
                    closeMenu();
                }
            });
            
            // Close menu when clicking outside
            document.addEventListener('click', function(e) {
                if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                    if (!mobileMenu.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                        closeMenu();
                    }
                }
            });
            
            // Close menu when clicking on a menu link
            const menuLinks = mobileMenu.querySelectorAll('a');
            menuLinks.forEach(link => {
                link.addEventListener('click', function() {
                    closeMenu();
                });
            });
            
            // Close menu on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && mobileMenu && !mobileMenu.classList.contains('hidden')) {
                    closeMenu();
                }
            });
        }
        
        // Initialize immediately - multiple strategies for reliability
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMobileMenuInline);
        } else {
            // DOM already loaded, try immediately
            initMobileMenuInline();
        }
        
        // Also try on window load as fallback
        window.addEventListener('load', function() {
            if (!menuInitialized) {
                initMobileMenuInline();
            }
        });
        
        // Immediate execution attempt
        setTimeout(initMobileMenuInline, 0);
    })();
</script>
