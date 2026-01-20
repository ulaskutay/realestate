<?php
// Header menüsünü getir
$headerMenu = get_menu('header');
$menuItems = $headerMenu['items'] ?? [];

// Dil bilgilerini al
$languages = [];
$currentLang = function_exists('get_current_language') ? get_current_language() : 'tr';
$defaultLang = function_exists('get_default_language') ? get_default_language() : 'tr';

// Aktif dilleri veritabanından al
try {
    if (class_exists('Database')) {
        $db = Database::getInstance();
        $languages = $db->fetchAll("SELECT * FROM languages WHERE is_active = 1 ORDER BY code ASC");
    }
} catch (Exception $e) {
    // Veritabanı hatası durumunda boş dizi
    $languages = [];
}

// Mevcut URL'i al ve dil prefix'ini çıkar
$currentUrl = $_SERVER['REQUEST_URI'] ?? '/';
$currentPath = parse_url($currentUrl, PHP_URL_PATH);
$pathParts = explode('/', trim($currentPath, '/'));

// Dil kodunu path'ten çıkar
$basePath = $currentPath;
if (!empty($pathParts[0]) && strlen($pathParts[0]) === 2) {
    // İlk segment dil kodu olabilir
    $potentialLang = strtolower($pathParts[0]);
    foreach ($languages as $lang) {
        if ($lang['code'] === $potentialLang) {
            array_shift($pathParts);
            $basePath = '/' . implode('/', $pathParts);
            break;
        }
    }
}

// basePath'i normalize et
$basePath = rtrim($basePath, '/');
if (empty($basePath)) {
    $basePath = '';
}

// Recursive menü render fonksiyonu (3+ seviye desteği)
function renderDesktopMenuItem($item, $level = 0) {
    $hasChildren = !empty($item['children']);
    $isRoot = $level === 0;
    
    if ($isRoot): ?>
        <?php if ($hasChildren): ?>
            <div class="relative group/dropdown-<?php echo $level; ?>">
                <a href="<?php echo esc_url($item['url']); ?>" 
                   class="text-gray-700 hover:text-purple-600 font-medium transition-colors flex items-center gap-1"
                   <?php echo $item['target'] === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                    <?php if (!empty($item['icon'])): ?>
                        <span class="material-symbols-outlined text-lg"><?php echo esc_html($item['icon']); ?></span>
                    <?php endif; ?>
                    <?php echo esc_html($item['title']); ?>
                    <svg class="w-4 h-4 transition-transform group-hover/dropdown-<?php echo $level; ?>:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </a>
                
                <!-- Dropdown Menu Level 1 -->
                <div class="absolute top-full left-0 pt-2 opacity-0 invisible group-hover/dropdown-<?php echo $level; ?>:opacity-100 group-hover/dropdown-<?php echo $level; ?>:visible transition-all duration-200 z-50">
                    <div class="bg-white rounded-lg shadow-lg border border-gray-100 py-2 min-w-[220px]">
                        <?php foreach ($item['children'] as $child): ?>
                            <?php renderDesktopMenuItem($child, $level + 1); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <a href="<?php echo esc_url($item['url']); ?>" 
               class="text-gray-700 hover:text-purple-600 font-medium transition-colors relative group"
               <?php echo $item['target'] === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                <?php if (!empty($item['icon'])): ?>
                    <span class="material-symbols-outlined text-lg mr-1"><?php echo esc_html($item['icon']); ?></span>
                <?php endif; ?>
                <?php echo esc_html($item['title']); ?>
                <span class="absolute bottom-0 left-0 w-full h-0.5 bg-purple-600 scale-x-0 group-hover:scale-x-100 transition-transform"></span>
            </a>
        <?php endif; ?>
    <?php else: ?>
        <?php if ($hasChildren): ?>
            <!-- Alt menü öğesi (3+ seviye) -->
            <div class="relative group/sub-<?php echo $level; ?>">
                <a href="<?php echo esc_url($item['url']); ?>" 
                   class="flex items-center justify-between px-4 py-2 text-gray-700 hover:bg-purple-50 hover:text-purple-600 transition-colors"
                   <?php echo $item['target'] === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
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
                
                <!-- Sub-Dropdown (3. seviye ve sonrası) -->
                <div class="absolute left-full top-0 pl-2 opacity-0 invisible group-hover/sub-<?php echo $level; ?>:opacity-100 group-hover/sub-<?php echo $level; ?>:visible transition-all duration-200 z-50">
                    <div class="bg-white rounded-lg shadow-lg border border-gray-100 py-2 min-w-[200px]">
                        <?php foreach ($item['children'] as $subChild): ?>
                            <?php renderDesktopMenuItem($subChild, $level + 1); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <a href="<?php echo esc_url($item['url']); ?>" 
               class="block px-4 py-2 text-gray-700 hover:bg-purple-50 hover:text-purple-600 transition-colors"
               <?php echo $item['target'] === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                <?php if (!empty($item['icon'])): ?>
                    <span class="material-symbols-outlined text-lg mr-2"><?php echo esc_html($item['icon']); ?></span>
                <?php endif; ?>
                <?php echo esc_html($item['title']); ?>
            </a>
        <?php endif; ?>
    <?php endif;
}

// Recursive mobil menü render fonksiyonu
function renderMobileMenuItem($item, $level = 0) {
    $hasChildren = !empty($item['children']);
    $indent = $level * 16; // px cinsinden girinti
    ?>
    
    <?php if ($hasChildren): ?>
        <div class="mobile-dropdown" style="margin-left: <?php echo $indent; ?>px;">
            <button type="button" class="mobile-dropdown-toggle flex items-center justify-between w-full py-2 text-gray-700 <?php echo $level === 0 ? 'font-medium' : 'text-sm'; ?>">
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
                    <?php renderMobileMenuItem($child, $level + 1); ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <a href="<?php echo esc_url($item['url']); ?>" 
           class="block py-2 <?php echo $level === 0 ? 'text-gray-700 font-medium' : 'text-gray-600 text-sm'; ?> hover:text-purple-600"
           style="margin-left: <?php echo $indent; ?>px;"
           <?php echo $item['target'] === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
            <?php if (!empty($item['icon'])): ?>
                <span class="material-symbols-outlined text-lg mr-2"><?php echo esc_html($item['icon']); ?></span>
            <?php endif; ?>
            <?php echo esc_html($item['title']); ?>
        </a>
    <?php endif;
}
?>

<header class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm shadow-sm border-b border-gray-100">
  <nav class="max-w-7xl mx-auto px-6 lg:px-8">
    <div class="flex items-center justify-between h-18 sm:h-20 lg:h-24">
      <!-- Logo -->
      <a href="<?php echo ViewRenderer::siteUrl(); ?>" class="flex items-center gap-3 group">
        <?php 
        $siteLogo = get_site_logo();
        if (!empty($siteLogo)): ?>
          <img src="<?php echo esc_url($siteLogo); ?>" 
               alt="<?php echo esc_attr(get_option('site_name', 'Site Adı')); ?>" 
               class="h-12 sm:h-14 lg:h-16 w-auto object-contain transform transition-transform group-hover:scale-105"
               loading="eager">
        <?php else: ?>
          <div class="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center transform transition-transform group-hover:scale-105">
            <svg class="w-6 h-6 sm:w-7 sm:h-7 lg:w-8 lg:h-8 text-white" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
            </svg>
          </div>
        <?php endif; ?>
        <span class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900"><?php echo esc_html(get_option('site_name', 'Site Adı')); ?></span>
      </a>

      <!-- Navigation Links (Desktop) - 3+ Seviye Desteği -->
      <div class="hidden lg:flex items-center gap-8">
        <?php if (!empty($menuItems)): ?>
          <?php foreach ($menuItems as $item): ?>
            <?php renderDesktopMenuItem($item, 0); ?>
          <?php endforeach; ?>
        <?php else: ?>
          <!-- Varsayılan menü (menü oluşturulmamışsa) -->
          <a href="<?php echo ViewRenderer::siteUrl(); ?>" class="text-gray-700 hover:text-purple-600 font-medium transition-colors relative group">
            Ana Sayfa
            <span class="absolute bottom-0 left-0 w-full h-0.5 bg-purple-600 scale-x-0 group-hover:scale-x-100 transition-transform"></span>
          </a>
          <a href="<?php echo ViewRenderer::siteUrl('blog'); ?>" class="text-gray-700 hover:text-purple-600 font-medium transition-colors relative group">
            Blog
            <span class="absolute bottom-0 left-0 w-full h-0.5 bg-purple-600 scale-x-0 group-hover:scale-x-100 transition-transform"></span>
          </a>
        <?php endif; ?>
      </div>

      <!-- Right Side -->
      <div class="flex items-center gap-4">
        <!-- Language Switcher Hook (Translation Modülü) -->
        <div style="display: none !important;">
          <?php do_action('theme_navigation_after_menu'); ?>
        </div>
        
        <!-- Language Switcher (Desktop) - Fallback if translation module not active -->
        <?php if (!empty($languages) && count($languages) > 1): ?>
        <div class="hidden lg:block" style="display: none !important;">
          <div class="relative group/lang-switcher">
            <button type="button" class="flex items-center gap-1.5 px-3 py-2 text-gray-700 hover:text-purple-600 font-medium transition-colors rounded-lg">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
              </svg>
              <span class="text-sm font-medium"><?php echo strtoupper($currentLang); ?></span>
              <svg class="w-4 h-4 transition-transform group-hover/lang-switcher:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
            
            <!-- Dropdown Menu -->
            <div class="absolute right-0 top-full mt-2 opacity-0 invisible group-hover/lang-switcher:opacity-100 group-hover/lang-switcher:visible transition-all duration-200 z-50">
              <div class="bg-white rounded-lg shadow-lg border border-gray-100 py-2 min-w-[160px]">
                <?php foreach ($languages as $lang): ?>
                  <?php
                  // URL oluştur
                  if ($lang['code'] === $defaultLang) {
                    $langUrl = $basePath ?: '/';
                  } else {
                    $langUrl = '/' . $lang['code'] . $basePath;
                    if ($langUrl === '/' . $lang['code']) {
                      $langUrl = '/' . $lang['code'] . '/';
                    }
                  }
                  $isActive = $lang['code'] === $currentLang;
                  ?>
                  <a href="<?php echo esc_url($langUrl); ?>" 
                     class="flex items-center gap-2.5 px-4 py-2.5 text-gray-700 hover:bg-purple-50 hover:text-purple-600 transition-colors <?php echo $isActive ? 'bg-purple-50 text-purple-600 font-medium' : ''; ?>">
                    <?php if (!empty($lang['flag'])): ?>
                      <span class="text-lg"><?php echo esc_html($lang['flag']); ?></span>
                    <?php endif; ?>
                    <span class="text-sm"><?php echo esc_html($lang['native_name'] ?? $lang['name'] ?? strtoupper($lang['code'])); ?></span>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>
        
        <button class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center hover:bg-purple-700 transition-colors">
          <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
          </svg>
        </button>
        
        <!-- Mobile Menu Button -->
        <button class="lg:hidden w-10 h-10 flex items-center justify-center text-gray-700" id="mobile-menu-btn">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile Menu - 3+ Seviye Desteği -->
    <div class="lg:hidden hidden pb-4" id="mobile-menu">
      <div class="flex flex-col gap-2 pt-4 border-t border-gray-100">
        <?php if (!empty($menuItems)): ?>
          <?php foreach ($menuItems as $item): ?>
            <?php renderMobileMenuItem($item, 0); ?>
          <?php endforeach; ?>
        <?php else: ?>
          <a href="<?php echo ViewRenderer::siteUrl(); ?>" class="py-2 text-gray-700 hover:text-purple-600 font-medium">Ana Sayfa</a>
          <a href="<?php echo ViewRenderer::siteUrl('blog'); ?>" class="py-2 text-gray-700 hover:text-purple-600 font-medium">Blog</a>
        <?php endif; ?>
        
        <!-- Language Switcher Hook (Mobile - Translation Modülü) -->
        <div class="px-4 py-2" style="display: none !important;">
          <?php do_action('theme_navigation_after_menu'); ?>
        </div>
        
        <!-- Language Switcher (Mobile) -->
        <?php if (!empty($languages) && count($languages) > 1): ?>
        <div class="pt-4 border-t border-gray-100" style="display: none !important;">
          <div class="lang-switcher-mobile">
            <div class="flex flex-col gap-2">
              <span class="text-sm font-medium text-gray-500 mb-1">Dil / Language</span>
              <div class="flex flex-wrap gap-2">
                <?php foreach ($languages as $lang): ?>
                  <?php
                  // URL oluştur
                  if ($lang['code'] === $defaultLang) {
                    $langUrl = $basePath ?: '/';
                  } else {
                    $langUrl = '/' . $lang['code'] . $basePath;
                    if ($langUrl === '/' . $lang['code']) {
                      $langUrl = '/' . $lang['code'] . '/';
                    }
                  }
                  $isActive = $lang['code'] === $currentLang;
                  ?>
                  <a href="<?php echo esc_url($langUrl); ?>" 
                     class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg transition-colors <?php echo $isActive ? 'bg-purple-600 text-white font-medium' : 'bg-gray-100 text-gray-700 hover:bg-purple-50 hover:text-purple-600'; ?>">
                    <?php if (!empty($lang['flag'])): ?>
                      <span><?php echo esc_html($lang['flag']); ?></span>
                    <?php endif; ?>
                    <span><?php echo esc_html($lang['native_name'] ?? $lang['name'] ?? strtoupper($lang['code'])); ?></span>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </nav>
</header>

<script>
  // Mobile menu toggle
  document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
    const menu = document.getElementById('mobile-menu');
    menu?.classList.toggle('hidden');
  });
  
  // Mobile dropdown toggles (recursive - tüm seviyeler için)
  document.querySelectorAll('.mobile-dropdown-toggle').forEach(function(btn) {
    btn.addEventListener('click', function() {
      const dropdown = this.closest('.mobile-dropdown');
      const content = dropdown.querySelector(':scope > .mobile-dropdown-content');
      const icon = this.querySelector('.mobile-dropdown-icon');
      
      content.classList.toggle('hidden');
      icon.classList.toggle('rotate-180');
    });
  });
</script>
