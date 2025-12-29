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
                    <?php if (!empty($item['icon'])): ?>
                        <span class="material-symbols-outlined text-lg"><?php echo esc_html($item['icon']); ?></span>
                    <?php endif; ?>
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
                <?php if (!empty($item['icon'])): ?>
                    <span class="material-symbols-outlined text-lg mr-1"><?php echo esc_html($item['icon']); ?></span>
                <?php endif; ?>
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
                        <?php if (!empty($item['icon'])): ?>
                            <span class="material-symbols-outlined text-lg"><?php echo esc_html($item['icon']); ?></span>
                        <?php endif; ?>
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
                <?php if (!empty($item['icon'])): ?>
                    <span class="material-symbols-outlined text-lg mr-2"><?php echo esc_html($item['icon']); ?></span>
                <?php endif; ?>
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
                    <?php if (!empty($item['icon'])): ?>
                        <span class="material-symbols-outlined text-lg"><?php echo esc_html($item['icon']); ?></span>
                    <?php endif; ?>
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
            <?php if (!empty($item['icon'])): ?>
                <span class="material-symbols-outlined text-lg mr-2"><?php echo esc_html($item['icon']); ?></span>
            <?php endif; ?>
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
        <div class="flex items-center <?php echo $isCentered ? 'justify-center' : 'justify-between'; ?> h-20">
            <!-- Logo -->
            <a href="/" class="flex items-center gap-2 <?php echo $isCentered ? 'absolute left-4 sm:left-6 lg:left-8' : ''; ?>">
                <?php if (!empty($siteLogo)): ?>
                    <img src="<?php echo esc_url($siteLogo); ?>" alt="<?php echo esc_attr($siteName); ?>" class="h-10 w-auto object-contain">
                <?php else: ?>
                    <div class="w-10 h-10 gradient-primary rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-xl"><?php echo substr($siteName, 0, 1); ?></span>
                    </div>
                    <span class="text-xl font-bold" style="color: <?php echo esc_attr($headerTextColor); ?>;"><?php echo esc_html($siteName); ?></span>
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
                        <span class="material-symbols-outlined">search</span>
                    </button>
                <?php endif; ?>
                <?php if ($showCta): ?>
                    <a href="<?php echo esc_url($ctaLink); ?>" class="btn-primary px-6 py-2.5 rounded-lg font-medium">
                        <?php echo esc_html($ctaText); ?>
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="md:hidden p-2 hover:opacity-80 rounded-lg transition-opacity" style="color: <?php echo esc_attr($headerTextColor); ?>">
                <span class="material-symbols-outlined">menu</span>
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
                <span class="material-symbols-outlined text-gray-600">close</span>
            </button>
        </div>
        <form action="/blog" method="GET" class="relative">
            <input type="text" 
                   name="q" 
                   id="search-input"
                   placeholder="Aradığınız konuyu yazın..." 
                   class="w-full px-6 py-4 pl-14 rounded-xl bg-gray-50 border-2 border-gray-200 text-gray-900 placeholder-gray-400 focus:outline-none focus:border-primary focus:bg-white text-lg font-medium transition-all">
            <span class="material-symbols-outlined absolute left-5 top-1/2 transform -translate-y-1/2 text-gray-400 text-2xl pointer-events-none">search</span>
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
                
                // İkon değiştir
                const icon = this.querySelector('span');
                if (icon) {
                    if (mobileMenu.classList.contains('hidden')) {
                        icon.textContent = 'menu';
                    } else {
                        icon.textContent = 'close';
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
                    const icon = mobileMenuBtn.querySelector('span');
                    if (icon) {
                        icon.textContent = 'menu';
                    }
                }
            }
        });
    }
})();
</script>
