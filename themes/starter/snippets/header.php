<?php
/**
 * Starter Theme - Header
 * Paneldeki menü sistemini kullanır
 */

// Tema ayarları - header grubundan oku
$headerStyle = $themeLoader->getSetting('style', 'default', 'header');
$headerBgColor = $themeLoader->getSetting('bg_color', '#ffffff', 'header');
$headerTextColor = $themeLoader->getSetting('text_color', '#1f2937', 'header');

// Boolean ayarlarını kontrol et (string '1'/'0' veya boolean true/false)
$showSearchValue = $themeLoader->getSetting('show_search', false, 'header');
$showSearch = ($showSearchValue === '1' || $showSearchValue === true || $showSearchValue === 'true');

$showCtaValue = $themeLoader->getSetting('show_cta', true, 'header');
// Varsayılan true, sadece '0', false, veya 'false' ise false
$showCta = !($showCtaValue === '0' || $showCtaValue === false || $showCtaValue === 'false');

// CTA metin ve link - boş string ise default değeri kullan
$ctaTextValue = $themeLoader->getSetting('cta_text', 'İletişim', 'header');
$ctaText = !empty($ctaTextValue) ? $ctaTextValue : 'İletişim';

$ctaLinkValue = $themeLoader->getSetting('cta_link', '/contact', 'header');
$ctaLink = !empty($ctaLinkValue) ? $ctaLinkValue : '/contact';

// Header stiline göre class'ları belirle
$isFixed = in_array($headerStyle, ['sticky', 'default']);
$isTransparent = $headerStyle === 'transparent';
$isCentered = $headerStyle === 'centered';

// Panelden menüyü getir
$headerMenu = get_menu('header');
$menuItems = $headerMenu['items'] ?? [];

// Site ayarları
$siteName = get_option('site_name', 'Site Adı');
$siteLogo = $themeLoader->getLogo();
if (empty($siteLogo)) {
    $siteLogo = get_site_logo();
}

// Logo boyutları (CLS için)
$logoWidth = $themeLoader->getLogoWidth();
$logoHeight = $themeLoader->getLogoHeight();

// Recursive Desktop menü render fonksiyonu
if (!function_exists('renderStarterDesktopMenuItem')) {
function renderStarterDesktopMenuItem($item, $level = 0, $themeLoader = null, $textColor = '#1f2937') {
    $hasChildren = !empty($item['children']);
    $isRoot = $level === 0;
    
    if ($isRoot): ?>
        <?php if ($hasChildren): ?>
            <div class="relative group/dropdown-<?php echo $level; ?>">
                <a href="<?php echo esc_url($item['url']); ?>" 
                   class="font-medium transition-colors flex items-center gap-1 hover:opacity-80"
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
                            <?php renderStarterDesktopMenuItem($child, $level + 1, $themeLoader, $textColor); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <a href="<?php echo esc_url($item['url']); ?>" 
               class="font-medium transition-colors hover:opacity-80"
               style="color: <?php echo esc_attr($textColor); ?>;"
               <?php echo ($item['target'] ?? '') === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                <?php echo esc_html($item['title']); ?>
            </a>
        <?php endif; ?>
    <?php else: ?>
        <?php if ($hasChildren): ?>
            <!-- Alt menü öğesi (3+ seviye) -->
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
                            <?php renderStarterDesktopMenuItem($subChild, $level + 1, $themeLoader, $textColor); ?>
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
} // end function_exists renderStarterDesktopMenuItem

// Recursive Mobil menü render fonksiyonu
if (!function_exists('renderStarterMobileMenuItem')) {
function renderStarterMobileMenuItem($item, $level = 0) {
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
                    <?php renderStarterMobileMenuItem($child, $level + 1); ?>
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
} // end function_exists renderStarterMobileMenuItem
?>

<header class="<?php echo $isFixed ? 'fixed' : 'relative'; ?> top-0 left-0 right-0 z-40 transition-all duration-300 <?php echo $isTransparent ? 'bg-transparent' : ''; ?> <?php echo $isCentered ? 'justify-center' : ''; ?>" 
        id="main-header" 
        style="background-color: <?php echo $isTransparent ? 'transparent' : esc_attr($headerBgColor); ?>; color: <?php echo esc_attr($headerTextColor); ?>; <?php echo !$isTransparent ? 'box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);' : ''; ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center <?php echo $isCentered ? 'justify-center' : 'justify-between'; ?> h-20 sm:h-24 lg:h-28">
            <!-- Logo -->
            <a href="/" class="flex items-center gap-3 <?php echo $isCentered ? 'absolute left-4 sm:left-6 lg:left-8' : ''; ?>">
                <?php if (!empty($siteLogo)): ?>
                    <?php
                    // Logo görüntülenen boyutları (Tailwind class'larına göre)
                    // h-12 = 48px, sm:h-14 = 56px, lg:h-16 = 64px
                    // Aspect ratio korunarak width hesapla (varsayılan 2.5:1)
                    $logoAspectRatio = $logoWidth && $logoHeight ? ($logoWidth / $logoHeight) : 2.5;
                    $maxDisplayHeight = 64; // lg:h-16
                    $maxDisplayWidth = round($maxDisplayHeight * $logoAspectRatio);
                    ?>
                    <img src="<?php echo esc_url($siteLogo); ?>" 
                         alt="<?php echo esc_attr($siteName); ?>" 
                         class="h-12 sm:h-14 lg:h-16 w-auto object-contain"
                         width="<?php echo $maxDisplayWidth; ?>"
                         height="<?php echo $maxDisplayHeight; ?>"
                         loading="eager"
                         decoding="async">
                <?php else: ?>
                    <div class="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 gradient-primary rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-xl sm:text-2xl lg:text-3xl"><?php echo substr($siteName, 0, 1); ?></span>
                    </div>
                    <span class="text-xl sm:text-2xl lg:text-3xl font-bold" style="color: <?php echo esc_attr($headerTextColor); ?>;"><?php echo esc_html($siteName); ?></span>
                <?php endif; ?>
            </a>
            
            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center gap-8" style="color: <?php echo esc_attr($headerTextColor); ?>;">
                <?php if (!empty($menuItems)): ?>
                    <?php foreach ($menuItems as $item): ?>
                        <?php renderStarterDesktopMenuItem($item, 0, $themeLoader, $headerTextColor); ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Varsayılan menü (menü oluşturulmamışsa) -->
                    <a href="/" class="font-medium transition-colors hover:opacity-80" style="color: <?php echo esc_attr($headerTextColor); ?>;">Ana Sayfa</a>
                    <a href="/blog" class="font-medium transition-colors hover:opacity-80" style="color: <?php echo esc_attr($headerTextColor); ?>;">Blog</a>
                    <a href="/contact" class="font-medium transition-colors hover:opacity-80" style="color: <?php echo esc_attr($headerTextColor); ?>;">İletişim</a>
                <?php endif; ?>
            </nav>
            
            <!-- CTA Button & Search -->
            <div class="hidden md:flex items-center gap-4 <?php echo $isCentered ? 'absolute right-4 sm:right-6 lg:right-8' : ''; ?>">
                <?php if ($showSearch): ?>
                    <button id="header-search-btn" class="p-2 hover:opacity-80 transition-opacity" style="color: <?php echo esc_attr($headerTextColor); ?>" aria-label="Ara">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                <?php endif; ?>
                <?php if ($showCta): ?>
                    <a href="<?php echo esc_url($ctaLink); ?>" class="btn-primary px-6 py-2.5 rounded-lg font-medium">
                        <?php echo esc_html($ctaText); ?>
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="md:hidden p-2 hover:opacity-80 rounded-lg transition-opacity" style="color: <?php echo esc_attr($headerTextColor); ?>" aria-label="Menüyü aç/kapat" aria-expanded="false" aria-controls="mobile-menu">
                <svg class="w-6 h-6 menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg class="w-6 h-6 close-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
    
    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden border-t" style="background-color: <?php echo esc_attr($headerBgColor); ?>; border-color: rgba(0,0,0,0.1);">
        <div class="px-4 py-4 space-y-1">
            <?php if (!empty($menuItems)): ?>
                <?php foreach ($menuItems as $item): ?>
                    <?php renderStarterMobileMenuItem($item, 0); ?>
                <?php endforeach; ?>
            <?php else: ?>
                <a href="/" class="block py-3 font-medium hover:opacity-80 transition-opacity" style="color: <?php echo esc_attr($headerTextColor); ?>;">Ana Sayfa</a>
                <a href="/blog" class="block py-3 font-medium hover:opacity-80 transition-opacity" style="color: <?php echo esc_attr($headerTextColor); ?>;">Blog</a>
                <a href="/contact" class="block py-3 font-medium hover:opacity-80 transition-opacity" style="color: <?php echo esc_attr($headerTextColor); ?>;">İletişim</a>
            <?php endif; ?>
            <?php if ($showCta): ?>
            <div class="pt-4 border-t" style="border-color: rgba(0,0,0,0.1);">
                <a href="<?php echo esc_url($ctaLink); ?>" class="block btn-primary px-4 py-3 rounded-lg font-medium text-center">
                    <?php echo esc_html($ctaText); ?>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php if ($isFixed): ?>
<!-- Spacer for fixed header -->
<div class="h-20"></div>
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
    
    // DOM yüklendiğinde çalış
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHeader);
    } else {
        initHeader();
    }
    
    function initHeader() {
        // Header scroll effect
        const header = document.getElementById('main-header');
        if (!header) return;
        
        let lastScroll = 0;
        
        // Search modal functionality
        const searchBtn = document.getElementById('header-search-btn');
        const searchModal = document.getElementById('search-modal');
        const searchModalClose = document.getElementById('search-modal-close');
        const searchInput = document.getElementById('search-input');
        
        if (searchBtn && searchModal) {
            // Open modal
            searchBtn.addEventListener('click', function(e) {
                e.preventDefault();
                searchModal.classList.remove('hidden');
                searchModal.style.display = 'flex';
                // Focus input after animation
                setTimeout(() => {
                    if (searchInput) searchInput.focus();
                }, 100);
            });
            
            // Close modal
            if (searchModalClose) {
                searchModalClose.addEventListener('click', function(e) {
                    e.preventDefault();
                    searchModal.classList.add('hidden');
                    searchModal.style.display = 'none';
                });
            }
            
            // Close on background click
            searchModal.addEventListener('click', function(e) {
                if (e.target === searchModal) {
                    searchModal.classList.add('hidden');
                    searchModal.style.display = 'none';
                }
            });
            
            // Close on Escape key
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
                header.style.backgroundColor = '<?php echo esc_js($headerBgColor); ?>';
                header.style.boxShadow = '0 1px 3px 0 rgba(0, 0, 0, 0.1)';
            } else {
                <?php if ($isTransparent): ?>
                header.style.backgroundColor = 'transparent';
                header.style.boxShadow = 'none';
                <?php else: ?>
                header.style.backgroundColor = '<?php echo esc_js($headerBgColor); ?>';
                header.style.boxShadow = '0 1px 3px 0 rgba(0, 0, 0, 0.1)';
                <?php endif; ?>
            }
            
            lastScroll = currentScroll;
        });
        
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                mobileMenu.classList.toggle('hidden');
                
                // İkon değiştir (SVG)
                const menuIcon = this.querySelector('.menu-icon');
                const closeIcon = this.querySelector('.close-icon');
                if (menuIcon && closeIcon) {
                    if (mobileMenu.classList.contains('hidden')) {
                        menuIcon.classList.remove('hidden');
                        closeIcon.classList.add('hidden');
                    } else {
                        menuIcon.classList.add('hidden');
                        closeIcon.classList.remove('hidden');
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
