<?php
/**
 * Starter Theme - Header
 * Paneldeki menü sistemini kullanır
 */

// Tema ayarları
$headerStyle = $themeLoader->getCustomSetting('header_style', 'fixed');
$headerTransparent = $themeLoader->getCustomSetting('header_transparent', false);
$isFixed = $headerStyle === 'fixed';

// Panelden menüyü getir
$headerMenu = get_menu('header');
$menuItems = $headerMenu['items'] ?? [];

// Site ayarları
$siteName = get_option('site_name', 'Site Adı');
$siteLogo = $themeLoader->getLogo();

// Recursive Desktop menü render fonksiyonu
if (!function_exists('renderStarterDesktopMenuItem')) {
function renderStarterDesktopMenuItem($item, $level = 0, $themeLoader = null) {
    $hasChildren = !empty($item['children']);
    $isRoot = $level === 0;
    
    if ($isRoot): ?>
        <?php if ($hasChildren): ?>
            <div class="relative group/dropdown-<?php echo $level; ?>">
                <a href="<?php echo esc_url($item['url']); ?>" 
                   class="text-gray-700 hover:text-primary font-medium transition-colors flex items-center gap-1"
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
                            <?php renderStarterDesktopMenuItem($child, $level + 1, $themeLoader); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <a href="<?php echo esc_url($item['url']); ?>" 
               class="text-gray-700 hover:text-primary font-medium transition-colors"
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
                            <?php renderStarterDesktopMenuItem($subChild, $level + 1, $themeLoader); ?>
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

<header class="<?php echo $isFixed ? 'fixed' : 'relative'; ?> top-0 left-0 right-0 z-40 transition-all duration-300 <?php echo $headerTransparent ? 'bg-transparent' : 'bg-white shadow-sm'; ?>" id="main-header">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            <!-- Logo -->
            <a href="/" class="flex items-center gap-2">
                <?php if (!empty($siteLogo)): ?>
                    <img src="<?php echo esc_url($siteLogo); ?>" alt="<?php echo esc_attr($siteName); ?>" class="h-10 w-auto object-contain">
                <?php else: ?>
                    <div class="w-10 h-10 gradient-primary rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-xl"><?php echo substr($siteName, 0, 1); ?></span>
                    </div>
                    <span class="text-xl font-bold text-gray-900"><?php echo esc_html($siteName); ?></span>
                <?php endif; ?>
            </a>
            
            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center gap-8">
                <?php if (!empty($menuItems)): ?>
                    <?php foreach ($menuItems as $item): ?>
                        <?php renderStarterDesktopMenuItem($item, 0, $themeLoader); ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Varsayılan menü (menü oluşturulmamışsa) -->
                    <a href="/" class="text-gray-700 hover:text-primary font-medium transition-colors">Ana Sayfa</a>
                    <a href="/blog" class="text-gray-700 hover:text-primary font-medium transition-colors">Blog</a>
                    <a href="/contact" class="text-gray-700 hover:text-primary font-medium transition-colors">İletişim</a>
                <?php endif; ?>
            </nav>
            
            <!-- CTA Button -->
            <div class="hidden md:flex items-center gap-4">
                <a href="/contact" class="btn-primary px-6 py-2.5 rounded-lg font-medium">
                    İletişime Geç
                </a>
            </div>
            
            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="md:hidden p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <span class="material-symbols-outlined text-gray-700">menu</span>
            </button>
        </div>
    </div>
    
    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden bg-white border-t">
        <div class="px-4 py-4 space-y-1">
            <?php if (!empty($menuItems)): ?>
                <?php foreach ($menuItems as $item): ?>
                    <?php renderStarterMobileMenuItem($item, 0); ?>
                <?php endforeach; ?>
            <?php else: ?>
                <a href="/" class="block py-3 text-gray-700 hover:text-primary font-medium">Ana Sayfa</a>
                <a href="/blog" class="block py-3 text-gray-700 hover:text-primary font-medium">Blog</a>
                <a href="/contact" class="block py-3 text-gray-700 hover:text-primary font-medium">İletişim</a>
            <?php endif; ?>
            <div class="pt-4 border-t">
                <a href="/contact" class="block btn-primary px-4 py-3 rounded-lg font-medium text-center">
                    İletişime Geç
                </a>
            </div>
        </div>
    </div>
</header>

<?php if ($isFixed): ?>
<!-- Spacer for fixed header -->
<div class="h-20"></div>
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
        
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 100) {
                header.classList.add('bg-white', 'shadow-md');
                header.classList.remove('bg-transparent');
            } else {
                <?php if ($headerTransparent): ?>
                header.classList.remove('bg-white', 'shadow-md');
                header.classList.add('bg-transparent');
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
