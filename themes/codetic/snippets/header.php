<?php
/**
 * Codetic Theme - Header
 * Paneldeki menü sistemini kullanır
 */

// Tema ayarları - header grubundan oku
$headerStyle = $themeLoader->getSetting('style', 'fixed', 'header');
$headerBgColor = $themeLoader->getSetting('bg_color', 'rgba(15, 23, 42, 0.8)', 'header');
$headerTextColor = $themeLoader->getSetting('text_color', '#ffffff', 'header');

// Boolean ayarlarını kontrol et
$showSearchValue = $themeLoader->getSetting('show_search', false, 'header');
$showSearch = ($showSearchValue === '1' || $showSearchValue === true || $showSearchValue === 'true');

$showCtaValue = $themeLoader->getSetting('show_cta', true, 'header');
$showCta = !($showCtaValue === '0' || $showCtaValue === false || $showCtaValue === 'false');

// CTA metin ve link
$ctaTextValue = $themeLoader->getSetting('cta_text', 'Hemen Başla', 'header');
$ctaText = !empty($ctaTextValue) ? $ctaTextValue : 'Hemen Başla';

$ctaLinkValue = $themeLoader->getSetting('cta_link', '/contact', 'header');
$ctaLink = !empty($ctaLinkValue) ? $ctaLinkValue : '/contact';

// Header stiline göre class'ları belirle
$isFixed = in_array($headerStyle, ['fixed', 'transparent']);
$isTransparent = $headerStyle === 'transparent';
$isStatic = $headerStyle === 'static';

// Panelden menüyü getir
$headerMenu = get_menu('header');
$menuItems = $headerMenu['items'] ?? [];

// Site ayarları - Logo sadece tema özelleştirmeden gelir
$siteName = get_option('site_name', 'Site Adı');
$siteLogo = $themeLoader->getLogo();

// Logo boyutları
$logoWidth = $themeLoader->getLogoWidth();
$logoHeight = $themeLoader->getLogoHeight();

// Logo yüksekliği ayarı (header için) - customize'den al, yoksa varsayılan 40px (h-10)
$logoHeightClass = $themeLoader->getSetting('logo_height_class', 'h-10', 'header');
$logoDisplayHeight = $themeLoader->getSetting('logo_display_height', 40, 'header');

// Recursive Desktop menü render fonksiyonu
if (!function_exists('renderCodeticDesktopMenuItem')) {
function renderCodeticDesktopMenuItem($item, $level = 0, $themeLoader = null, $textColor = '#1f2937') {
    $hasChildren = !empty($item['children']);
    $isRoot = $level === 0;
    
    if ($isRoot): ?>
        <?php if ($hasChildren): ?>
            <div class="relative group/dropdown-<?php echo $level; ?>">
                <a href="<?php echo esc_url($item['url']); ?>" 
                   class="font-medium transition-colors flex items-center gap-1 hover:text-primary"
                   style="color: <?php echo esc_attr($textColor); ?>;"
                   <?php echo ($item['target'] ?? '') === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                    <?php echo esc_html($item['title']); ?>
                    <svg class="w-4 h-4 transition-transform group-hover/dropdown-<?php echo $level; ?>:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </a>
                
                <!-- Dropdown Menu -->
                <div class="absolute top-full left-0 pt-2 opacity-0 invisible group-hover/dropdown-<?php echo $level; ?>:opacity-100 group-hover/dropdown-<?php echo $level; ?>:visible transition-all duration-200 z-50">
                    <div class="bg-white rounded-xl shadow-lg border border-gray-100 py-2 min-w-[220px]">
                        <?php foreach ($item['children'] as $child): ?>
                            <?php renderCodeticDesktopMenuItem($child, $level + 1, $themeLoader, $textColor); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <a href="<?php echo esc_url($item['url']); ?>" 
               class="font-medium transition-colors hover:text-primary"
               style="color: <?php echo esc_attr($textColor); ?>;"
               <?php echo ($item['target'] ?? '') === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                <?php echo esc_html($item['title']); ?>
            </a>
        <?php endif; ?>
    <?php else: ?>
        <?php if ($hasChildren): ?>
            <div class="relative group/sub-<?php echo $level; ?>">
                <a href="<?php echo esc_url($item['url']); ?>" 
                   class="flex items-center justify-between px-4 py-2 text-gray-700 hover:bg-primary/5 hover:text-primary transition-colors"
                   <?php echo ($item['target'] ?? '') === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                    <span class="flex items-center gap-2">
                        <?php echo esc_html($item['title']); ?>
                    </span>
                    <svg class="w-4 h-4 -rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </a>
                
                <!-- Sub-Dropdown -->
                <div class="absolute left-full top-0 pl-2 opacity-0 invisible group-hover/sub-<?php echo $level; ?>:opacity-100 group-hover/sub-<?php echo $level; ?>:visible transition-all duration-200 z-50">
                    <div class="bg-white rounded-xl shadow-lg border border-gray-100 py-2 min-w-[200px]">
                        <?php foreach ($item['children'] as $subChild): ?>
                            <?php renderCodeticDesktopMenuItem($subChild, $level + 1, $themeLoader, $textColor); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <a href="<?php echo esc_url($item['url']); ?>" 
               class="block px-4 py-2 text-gray-700 hover:bg-primary/5 hover:text-primary transition-colors"
               <?php echo ($item['target'] ?? '') === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                <?php echo esc_html($item['title']); ?>
            </a>
        <?php endif; ?>
    <?php endif;
}
}

// Recursive Mobil menü render fonksiyonu
if (!function_exists('renderCodeticMobileMenuItem')) {
function renderCodeticMobileMenuItem($item, $level = 0) {
    $hasChildren = !empty($item['children']);
    $indent = $level * 16;
    ?>
    
    <?php if ($hasChildren): ?>
        <div class="mobile-dropdown" style="margin-left: <?php echo $indent; ?>px;">
            <button type="button" class="mobile-dropdown-toggle flex items-center justify-between w-full py-3 text-gray-700 <?php echo $level === 0 ? 'font-medium' : 'text-sm'; ?>">
                <span class="flex items-center gap-2">
                    <?php echo esc_html($item['title']); ?>
                </span>
                <svg class="w-4 h-4 transition-transform mobile-dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div class="mobile-dropdown-content hidden pl-4 space-y-1">
                <?php foreach ($item['children'] as $child): ?>
                    <?php renderCodeticMobileMenuItem($child, $level + 1); ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <a href="<?php echo esc_url($item['url']); ?>" 
           class="block py-3 <?php echo $level === 0 ? 'text-gray-700 font-medium' : 'text-gray-600 text-sm'; ?> hover:text-primary transition-colors"
           style="margin-left: <?php echo $indent; ?>px;"
           <?php echo ($item['target'] ?? '') === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
            <?php echo esc_html($item['title']); ?>
        </a>
    <?php endif;
}
}
?>

<header class="<?php echo $isFixed ? 'fixed' : 'relative'; ?> top-0 left-0 right-0 z-50 transition-all duration-500 ease-out" 
        id="main-header">
    <div class="max-w-7xl mx-auto px-4 md:px-6">
        <div class="relative py-3 px-4 md:px-6 navbar-container">
            <div class="flex items-center justify-between">
            <!-- Logo -->
            <a href="/" class="flex items-center navbar-logo -ml-2 md:ml-0 pl-2 md:pl-0">
                <?php if (!empty($siteLogo)): ?>
                    <?php
                    $logoAspectRatio = $logoWidth && $logoHeight ? ($logoWidth / $logoHeight) : 2.5;
                    $maxDisplayHeight = $logoDisplayHeight;
                    $maxDisplayWidth = round($maxDisplayHeight * $logoAspectRatio);
                    ?>
                    <img src="<?php echo esc_url($siteLogo); ?>" 
                         alt="<?php echo esc_attr($siteName); ?>" 
                         class="<?php echo esc_attr($logoHeightClass); ?> w-auto object-contain"
                         width="<?php echo $maxDisplayWidth; ?>"
                         height="<?php echo $maxDisplayHeight; ?>"
                         loading="eager"
                         decoding="async">
                <?php else: ?>
                    <span class="text-xl font-bold text-white"><?php echo esc_html($siteName); ?></span>
                <?php endif; ?>
            </a>
            
            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center gap-8">
                <?php if (!empty($menuItems)): ?>
                    <?php foreach ($menuItems as $item): ?>
                        <?php if (!empty($item['children'])): ?>
                            <!-- Dropdown Menu Item -->
                            <div class="relative group/nav-dropdown">
                                <a href="<?php echo esc_url($item['url']); ?>" 
                                   class="navbar-menu-item text-[#A1A1AA] hover:text-white transition-colors duration-300 flex items-center gap-1"
                                   <?php echo ($item['target'] ?? '') === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                                    <?php echo esc_html($item['title']); ?>
                                    <svg class="w-4 h-4 transition-transform group-hover/nav-dropdown:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </a>
                                <div class="absolute top-full left-0 pt-2 opacity-0 invisible group-hover/nav-dropdown:opacity-100 group-hover/nav-dropdown:visible transition-all duration-300 ease-out z-50">
                                    <div class="bg-[#0B0B0B]/90 backdrop-blur-2xl border border-white/8 rounded-2xl shadow-xl py-2 min-w-[220px]">
                                        <?php foreach ($item['children'] as $child): ?>
                                            <a href="<?php echo esc_url($child['url']); ?>" 
                                               class="block px-4 py-2 text-[#A1A1AA] hover:text-white hover:bg-white/5 transition-colors duration-300"
                                               <?php echo ($child['target'] ?? '') === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                                                <?php echo esc_html($child['title']); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="<?php echo esc_url($item['url']); ?>" 
                               class="navbar-menu-item text-[#A1A1AA] hover:text-white transition-colors duration-300"
                               <?php echo ($item['target'] ?? '') === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                                <?php echo esc_html($item['title']); ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <a href="/" class="navbar-menu-item text-[#A1A1AA] hover:text-white transition-colors duration-300">Ana Sayfa</a>
                    <a href="/blog" class="navbar-menu-item text-[#A1A1AA] hover:text-white transition-colors duration-300">Blog</a>
                    <a href="/contact" class="navbar-menu-item text-[#A1A1AA] hover:text-white transition-colors duration-300">İletişim</a>
                <?php endif; ?>
                <?php if ($showCta): ?>
                    <a href="<?php echo esc_url($ctaLink); ?>" class="navbar-cta-btn">
                        <?php echo esc_html($ctaText); ?>
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="md:hidden p-2 hover:bg-white/5 rounded-lg transition-colors text-white" aria-label="Menüyü aç/kapat" aria-expanded="false" aria-controls="mobile-menu">
                <svg class="w-6 h-6 menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg class="w-6 h-6 close-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden mt-6 pt-6 border-t border-white/10">
            <div class="flex flex-col gap-4">
                <?php if (!empty($menuItems)): ?>
                    <?php foreach ($menuItems as $item): ?>
                        <a href="<?php echo esc_url($item['url']); ?>" 
                           class="text-left text-[#A1A1AA] hover:text-white transition-colors duration-300 py-2"
                           <?php echo ($item['target'] ?? '') === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                            <?php echo esc_html($item['title']); ?>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <a href="/" class="text-left text-[#A1A1AA] hover:text-white transition-colors duration-300 py-2">Ana Sayfa</a>
                    <a href="/blog" class="text-left text-[#A1A1AA] hover:text-white transition-colors duration-300 py-2">Blog</a>
                    <a href="/contact" class="text-left text-[#A1A1AA] hover:text-white transition-colors duration-300 py-2">İletişim</a>
                <?php endif; ?>
                <?php if ($showCta): ?>
                    <a href="<?php echo esc_url($ctaLink); ?>" class="mt-2 navbar-cta-btn-mobile text-center">
                        <?php echo esc_html($ctaText); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        </div>
    </div>
</header>

<?php if ($isFixed): ?>
<!-- Spacer for fixed header on mobile -->
<div class="header-spacer-mobile md:hidden"></div>
<?php endif; ?>

<?php if ($showSearch): ?>
<!-- Search Modal -->
<div id="search-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center" style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 p-6 transform transition-all">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-900">Ara</h3>
            <button id="search-modal-close" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="/blog" method="GET" class="relative">
            <input type="text" 
                   name="q" 
                   id="search-input"
                   placeholder="Aradığınız konuyu yazın..." 
                   class="w-full px-6 py-4 pl-14 rounded-xl bg-gray-50 border-2 border-gray-200 text-gray-900 placeholder-gray-400 focus:outline-none focus:border-primary focus:bg-white text-lg font-medium transition-all">
            <svg class="w-6 h-6 absolute left-5 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <button type="submit" class="absolute right-3 top-1/2 transform -translate-y-1/2 px-6 py-3 btn-primary rounded-lg font-medium hover:opacity-90 transition-opacity">
                Ara
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
(function() {
    'use strict';
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHeader);
    } else {
        initHeader();
    }
    
    function initHeader() {
        const header = document.getElementById('main-header');
        if (!header) return;
        
        
        // Search modal functionality
        const searchBtn = document.getElementById('header-search-btn');
        const searchModal = document.getElementById('search-modal');
        const searchModalClose = document.getElementById('search-modal-close');
        const searchInput = document.getElementById('search-input');
        
        if (searchBtn && searchModal) {
            searchBtn.addEventListener('click', function(e) {
                e.preventDefault();
                searchModal.classList.remove('hidden');
                searchModal.style.display = 'flex';
                setTimeout(() => {
                    if (searchInput) searchInput.focus();
                }, 100);
            });
            
            if (searchModalClose) {
                searchModalClose.addEventListener('click', function(e) {
                    e.preventDefault();
                    searchModal.classList.add('hidden');
                    searchModal.style.display = 'none';
                });
            }
            
            searchModal.addEventListener('click', function(e) {
                if (e.target === searchModal) {
                    searchModal.classList.add('hidden');
                    searchModal.style.display = 'none';
                }
            });
            
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && searchModal && !searchModal.classList.contains('hidden')) {
                    searchModal.classList.add('hidden');
                    searchModal.style.display = 'none';
                }
            });
        }
        
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 100) {
                header.classList.add('header-scrolled');
            } else {
                header.classList.remove('header-scrolled');
            }
        });
        
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const isHidden = mobileMenu.classList.contains('hidden');
                mobileMenu.classList.toggle('hidden');
                
                const menuIcon = this.querySelector('.menu-icon');
                const closeIcon = this.querySelector('.close-icon');
                if (menuIcon && closeIcon) {
                    if (isHidden) {
                        // Opening menu
                        menuIcon.classList.add('hidden');
                        closeIcon.classList.remove('hidden');
                        this.setAttribute('aria-expanded', 'true');
                    } else {
                        // Closing menu
                        menuIcon.classList.remove('hidden');
                        closeIcon.classList.add('hidden');
                        this.setAttribute('aria-expanded', 'false');
                    }
                }
            });
        }
        
        // Mobile dropdown toggles
        document.querySelectorAll('.mobile-dropdown-toggle').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const dropdown = this.closest('.mobile-dropdown');
                if (!dropdown) return;
                
                const content = dropdown.querySelector(':scope > .mobile-dropdown-content');
                const icon = this.querySelector('.mobile-dropdown-icon');
                
                if (content) {
                    content.classList.toggle('hidden');
                }
                
                if (icon) {
                    icon.classList.toggle('rotate-180');
                }
            });
        });
        
        // Dışarı tıklandığında menüyü kapat
        document.addEventListener('click', function(e) {
            if (mobileMenu && mobileMenuBtn && !mobileMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                if (!mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.add('hidden');
                    const menuIcon = mobileMenuBtn.querySelector('.menu-icon');
                    const closeIcon = mobileMenuBtn.querySelector('.close-icon');
                    if (menuIcon && closeIcon) {
                        menuIcon.classList.remove('hidden');
                        closeIcon.classList.add('hidden');
                    }
                }
            }
        });
    }
})();
</script>

