<?php
/**
 * Admin Sidebar - Yetki Kontrollü
 * Dinamik modül menüleri desteği ile
 */

// Session kontrolü
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper fonksiyonları yükle
if (!function_exists('get_logged_in_user')) {
    require_once __DIR__ . '/../../../../includes/functions.php';
}

$currentPage = $currentPage ?? $_GET['page'] ?? 'dashboard';
$currentUser = get_logged_in_user();

// Giriş yapılmamışsa sadece dashboard göster
$isLoggedIn = !empty($_SESSION['user_id']);

// Modül menülerini al (eğer ModuleLoader yüklüyse)
$moduleMenus = [];
if (class_exists('ModuleLoader')) {
    try {
        $moduleMenus = ModuleLoader::getInstance()->getAdminMenus();
    } catch (Exception $e) {
        $moduleMenus = [];
    }
}

// Yetki kontrol fonksiyonu
function can_view($module) {
    return current_user_can($module . '.view');
}
?>

<!-- Mobil Overlay -->
<div id="mobile-sidebar-overlay" class="hidden fixed inset-0 bg-black/50 z-40 lg:hidden" onclick="closeMobileSidebar()"></div>

<aside class="sidebar-fixed flex w-64 flex-col bg-background-light dark:bg-background-dark border-r border-gray-200 dark:border-white/10 p-4">
    <div class="flex h-full flex-col justify-between">
        <div class="flex flex-col gap-4">
            <!-- Logo ve Mobil Kapatma Butonu -->
            <div class="flex gap-3 items-center justify-between px-2">
                <div class="flex gap-3 items-center">
                <div class="bg-primary rounded-full size-10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-white text-xl">dashboard</span>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-gray-900 dark:text-white text-base font-semibold">Codetic Panel</h1>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Yönetim</p>
                </div>
                </div>
                <!-- Mobil Kapatma Butonu -->
                <button type="button" 
                        onclick="closeMobileSidebar()" 
                        class="lg:hidden p-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-white/5 transition-colors text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"
                        title="Menüyü Kapat">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>
            
            <!-- Menü -->
            <nav class="flex flex-col gap-2 mt-4">
                <!-- Dashboard - Herkes -->
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg <?php echo $currentPage === 'dashboard' ? 'bg-primary/10' : 'hover:bg-gray-100 dark:hover:bg-white/5'; ?>" href="<?php echo admin_url('dashboard'); ?>">
                    <span class="material-symbols-outlined <?php echo $currentPage === 'dashboard' ? 'text-primary' : 'text-gray-600 dark:text-gray-300'; ?> text-2xl">dashboard</span>
                    <p class="<?php echo $currentPage === 'dashboard' ? 'text-primary' : 'text-gray-800 dark:text-white'; ?> text-sm font-medium">Pano</p>
                </a>
                
                <?php if ($isLoggedIn): ?>
                <?php 
                // Yetki kontrolleri
                $canViewPosts = can_view('posts');
                $canViewAgreements = can_view('agreements');
                $canViewForms = can_view('forms');
                $canViewSmtp = can_view('smtp');
                $canViewMedia = can_view('media');
                $canViewUsers = can_view('users');
                $canViewThemes = can_view('themes');
                $canViewSliders = can_view('sliders');
                $canViewMenus = can_view('menus');
                $canViewDesign = current_user_can('themes.edit_code');
                $canViewModules = can_view('modules');
                $canViewSettings = can_view('settings');
                
                // İçerik alt menüsü için aktif kontrol (Yazılar ve Sözleşmeler)
                $contentActive = strpos($currentPage, 'posts') === 0 || strpos($currentPage, 'agreements') === 0;
                $showContentMenu = $canViewPosts || $canViewAgreements;
                
                // Tasarım alt menüsü için aktif kontrol
                $designActive = strpos($currentPage, 'design') === 0 || strpos($currentPage, 'sliders') === 0 || strpos($currentPage, 'menus') === 0 || strpos($currentPage, 'themes') === 0;
                $showDesignMenu = $canViewThemes || $canViewSliders || $canViewMenus || $canViewDesign;
                ?>
                
                <!-- İçerik (Açılır Menü) - Yazılar & Sözleşmeler -->
                <?php if ($showContentMenu): ?>
                <div class="content-menu-wrapper">
                    <button type="button" onclick="toggleContentMenu()" class="w-full flex items-center justify-between px-3 py-2 rounded-lg <?php echo $contentActive ? 'bg-primary/10' : 'hover:bg-gray-100 dark:hover:bg-white/5'; ?>">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined <?php echo $contentActive ? 'text-primary' : 'text-gray-600 dark:text-gray-300'; ?> text-2xl">article</span>
                            <p class="<?php echo $contentActive ? 'text-primary' : 'text-gray-800 dark:text-white'; ?> text-sm font-medium">İçerik</p>
                        </div>
                        <span class="material-symbols-outlined <?php echo $contentActive ? 'text-primary' : 'text-gray-600 dark:text-gray-300'; ?> text-lg transition-transform duration-200" id="content-menu-arrow">
                            <?php echo $contentActive ? 'expand_less' : 'expand_more'; ?>
                        </span>
                    </button>
                    
                    <!-- Alt Menü -->
                    <div id="content-submenu" class="ml-4 mt-1 flex flex-col gap-1 overflow-hidden transition-all duration-200 <?php echo $contentActive ? '' : 'hidden'; ?>">
                        <!-- Yazılar -->
                        <?php if ($canViewPosts): ?>
                        <a class="flex items-center gap-3 px-3 py-2 rounded-lg <?php echo strpos($currentPage, 'posts') === 0 ? 'bg-primary/5 text-primary' : 'hover:bg-gray-100 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300'; ?>" href="<?php echo admin_url('posts'); ?>">
                            <span class="material-symbols-outlined text-xl">edit_note</span>
                            <p class="text-sm font-medium">Yazılar</p>
                        </a>
                        <?php endif; ?>
                        
                        <!-- Sözleşmeler -->
                        <?php if ($canViewAgreements): ?>
                        <a class="flex items-center gap-3 px-3 py-2 rounded-lg <?php echo strpos($currentPage, 'agreements') === 0 ? 'bg-primary/5 text-primary' : 'hover:bg-gray-100 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300'; ?>" href="<?php echo admin_url('agreements'); ?>">
                            <span class="material-symbols-outlined text-xl">gavel</span>
                            <p class="text-sm font-medium">Sözleşmeler</p>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Formlar -->
                <?php if ($canViewForms): ?>
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg <?php echo strpos($currentPage, 'forms') === 0 ? 'bg-primary/10' : 'hover:bg-gray-100 dark:hover:bg-white/5'; ?>" href="<?php echo admin_url('forms'); ?>">
                    <span class="material-symbols-outlined <?php echo strpos($currentPage, 'forms') === 0 ? 'text-primary' : 'text-gray-600 dark:text-gray-300'; ?> text-2xl">dynamic_form</span>
                    <p class="<?php echo strpos($currentPage, 'forms') === 0 ? 'text-primary' : 'text-gray-800 dark:text-white'; ?> text-sm font-medium">Formlar</p>
                </a>
                <?php endif; ?>
                
                <!-- SMTP Ayarları -->
                <?php if ($canViewSmtp): ?>
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg <?php echo $currentPage === 'smtp' || $currentPage === 'smtp-settings' ? 'bg-primary/10' : 'hover:bg-gray-100 dark:hover:bg-white/5'; ?>" href="<?php echo admin_url('smtp-settings'); ?>">
                    <span class="material-symbols-outlined <?php echo $currentPage === 'smtp' || $currentPage === 'smtp-settings' ? 'text-primary' : 'text-gray-600 dark:text-gray-300'; ?> text-2xl">mail</span>
                    <p class="<?php echo $currentPage === 'smtp' || $currentPage === 'smtp-settings' ? 'text-primary' : 'text-gray-800 dark:text-white'; ?> text-sm font-medium">SMTP Ayarları</p>
                </a>
                <?php endif; ?>
                
                <!-- İçerik Kütüphanesi -->
                <?php if ($canViewMedia): ?>
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg <?php echo strpos($currentPage, 'media') === 0 ? 'bg-primary/10' : 'hover:bg-gray-100 dark:hover:bg-white/5'; ?>" href="<?php echo admin_url('media'); ?>">
                    <span class="material-symbols-outlined <?php echo strpos($currentPage, 'media') === 0 ? 'text-primary' : 'text-gray-600 dark:text-gray-300'; ?> text-2xl">perm_media</span>
                    <p class="<?php echo strpos($currentPage, 'media') === 0 ? 'text-primary' : 'text-gray-800 dark:text-white'; ?> text-sm font-medium">İçerik Kütüphanesi</p>
                </a>
                <?php endif; ?>
                
                <!-- Kullanıcılar -->
                <?php if ($canViewUsers): ?>
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg <?php echo strpos($currentPage, 'users') === 0 || strpos($currentPage, 'roles') === 0 ? 'bg-primary/10' : 'hover:bg-gray-100 dark:hover:bg-white/5'; ?>" href="<?php echo admin_url('users'); ?>">
                    <span class="material-symbols-outlined <?php echo strpos($currentPage, 'users') === 0 || strpos($currentPage, 'roles') === 0 ? 'text-primary' : 'text-gray-600 dark:text-gray-300'; ?> text-2xl">people</span>
                    <p class="<?php echo strpos($currentPage, 'users') === 0 || strpos($currentPage, 'roles') === 0 ? 'text-primary' : 'text-gray-800 dark:text-white'; ?> text-sm font-medium">Kullanıcılar</p>
                </a>
                <?php endif; ?>
                
                <!-- Tasarım (Açılır Menü) -->
                <?php if ($showDesignMenu): ?>
                <div class="design-menu-wrapper">
                    <button type="button" onclick="toggleDesignMenu()" class="w-full flex items-center justify-between px-3 py-2 rounded-lg <?php echo $designActive ? 'bg-primary/10' : 'hover:bg-gray-100 dark:hover:bg-white/5'; ?>">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined <?php echo $designActive ? 'text-primary' : 'text-gray-600 dark:text-gray-300'; ?> text-2xl">palette</span>
                            <p class="<?php echo $designActive ? 'text-primary' : 'text-gray-800 dark:text-white'; ?> text-sm font-medium">Tasarım</p>
                        </div>
                        <span class="material-symbols-outlined <?php echo $designActive ? 'text-primary' : 'text-gray-600 dark:text-gray-300'; ?> text-lg transition-transform duration-200" id="design-menu-arrow">
                            <?php echo $designActive ? 'expand_less' : 'expand_more'; ?>
                        </span>
                    </button>
                    
                    <!-- Alt Menü -->
                    <div id="design-submenu" class="ml-4 mt-1 flex flex-col gap-1 overflow-hidden transition-all duration-200 <?php echo $designActive ? '' : 'hidden'; ?>">
                        <!-- Temalar -->
                        <?php if ($canViewThemes): ?>
                        <a class="flex items-center gap-3 px-3 py-2 rounded-lg <?php echo $currentPage === 'themes' ? 'bg-primary/5 text-primary' : 'hover:bg-gray-100 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300'; ?>" href="<?php echo admin_url('themes'); ?>">
                            <span class="material-symbols-outlined text-xl">style</span>
                            <p class="text-sm font-medium">Temalar</p>
                        </a>
                        <?php endif; ?>
                        
                        <!-- Tema Özelleştir -->
                        <?php if (current_user_can('themes.customize')): ?>
                        <a class="flex items-center gap-3 px-3 py-2 rounded-lg <?php echo strpos($currentPage, 'themes/customize') === 0 ? 'bg-primary/5 text-primary' : 'hover:bg-gray-100 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300'; ?>" href="<?php echo admin_url('themes/customize'); ?>">
                            <span class="material-symbols-outlined text-xl">tune</span>
                            <p class="text-sm font-medium">Özelleştir</p>
                        </a>
                        <?php endif; ?>
                        
                        <!-- Sliderlar -->
                        <?php if ($canViewSliders): ?>
                        <a class="flex items-center gap-3 px-3 py-2 rounded-lg <?php echo strpos($currentPage, 'sliders') === 0 ? 'bg-primary/5 text-primary' : 'hover:bg-gray-100 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300'; ?>" href="<?php echo admin_url('sliders'); ?>">
                            <span class="material-symbols-outlined text-xl">slideshow</span>
                            <p class="text-sm font-medium">Sliderlar</p>
                        </a>
                        <?php endif; ?>
                        
                        <!-- Menüler -->
                        <?php if ($canViewMenus): ?>
                        <a class="flex items-center gap-3 px-3 py-2 rounded-lg <?php echo strpos($currentPage, 'menus') === 0 ? 'bg-primary/5 text-primary' : 'hover:bg-gray-100 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300'; ?>" href="<?php echo admin_url('menus'); ?>">
                            <span class="material-symbols-outlined text-xl">menu</span>
                            <p class="text-sm font-medium">Menüler</p>
                        </a>
                        <?php endif; ?>
                        
                        <!-- Tasarım Düzenleme (Kod Editörü) -->
                        <?php if ($canViewDesign): ?>
                        <a class="flex items-center gap-3 px-3 py-2 rounded-lg <?php echo $currentPage === 'design' ? 'bg-primary/5 text-primary' : 'hover:bg-gray-100 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300'; ?>" href="<?php echo admin_url('design'); ?>">
                            <span class="material-symbols-outlined text-xl">code</span>
                            <p class="text-sm font-medium">Tasarım Düzenleme</p>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php 
                // Dinamik Modül Menüleri
                if (!empty($moduleMenus)): 
                ?>
                
                <!-- Modül Menüleri Ayırıcı -->
                <div class="my-2 border-t border-gray-200 dark:border-gray-700"></div>
                <p class="px-3 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Modüller</p>
                
                <?php foreach ($moduleMenus as $menu): 
                    $menuSlug = $menu['slug'];
                    $isActive = strpos($currentPage, $menuSlug) === 0;
                    
                    // Modül yetkisi kontrolü
                    if (!can_view($menuSlug)) continue;
                ?>
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg <?php echo $isActive ? 'bg-primary/10' : 'hover:bg-gray-100 dark:hover:bg-white/5'; ?>" 
                   href="<?php echo admin_url($menuSlug); ?>">
                    <span class="material-symbols-outlined <?php echo $isActive ? 'text-primary' : 'text-gray-600 dark:text-gray-300'; ?> text-2xl">
                        <?php echo esc_html($menu['icon']); ?>
                    </span>
                    <p class="<?php echo $isActive ? 'text-primary' : 'text-gray-800 dark:text-white'; ?> text-sm font-medium">
                        <?php echo esc_html($menu['title']); ?>
                    </p>
                </a>
                
                <?php 
                    // Alt menüler varsa
                    if (!empty($menu['submenu'])): 
                ?>
                <div class="ml-8 flex flex-col gap-1">
                    <?php foreach ($menu['submenu'] as $submenu): 
                        $subSlug = $menuSlug . '/' . ($submenu['slug'] ?? '');
                        $subActive = strpos($currentPage, $subSlug) === 0;
                    ?>
                    <a class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm <?php echo $subActive ? 'text-primary bg-primary/5' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5'; ?>" 
                       href="<?php echo admin_url($subSlug); ?>">
                        <?php echo esc_html($submenu['title'] ?? $submenu['slug']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <?php endforeach; ?>
                <?php endif; ?>
                
                <!-- Ayırıcı -->
                <div class="my-2 border-t border-gray-200 dark:border-gray-700"></div>
                
                <!-- Modül Yönetimi -->
                <?php if ($canViewModules): ?>
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg <?php echo strpos($currentPage, 'modules') === 0 ? 'bg-primary/10' : 'hover:bg-gray-100 dark:hover:bg-white/5'; ?>" href="<?php echo admin_url('modules'); ?>">
                    <span class="material-symbols-outlined <?php echo strpos($currentPage, 'modules') === 0 ? 'text-primary' : 'text-gray-600 dark:text-gray-300'; ?> text-2xl">extension</span>
                    <p class="<?php echo strpos($currentPage, 'modules') === 0 ? 'text-primary' : 'text-gray-800 dark:text-white'; ?> text-sm font-medium">Modüller</p>
                </a>
                <?php endif; ?>
                
                <!-- Ayarlar -->
                <?php if ($canViewSettings): ?>
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg <?php echo $currentPage === 'settings' ? 'bg-primary/10' : 'hover:bg-gray-100 dark:hover:bg-white/5'; ?>" href="<?php echo admin_url('settings'); ?>">
                    <span class="material-symbols-outlined <?php echo $currentPage === 'settings' ? 'text-primary' : 'text-gray-600 dark:text-gray-300'; ?> text-2xl">settings</span>
                    <p class="<?php echo $currentPage === 'settings' ? 'text-primary' : 'text-gray-800 dark:text-white'; ?> text-sm font-medium">Ayarlar</p>
                </a>
                <?php endif; ?>
                <?php endif; ?>
            </nav>
        </div>
        
        <!-- Kullanıcı Bilgisi -->
        <div class="flex flex-col gap-1">
            <?php if ($currentUser): ?>
            <div class="flex items-center gap-3 px-3 py-2 mb-2">
                <div class="flex flex-col flex-1">
                    <p class="text-gray-800 dark:text-white text-sm font-medium"><?php echo esc_html($currentUser['username'] ?? 'Kullanıcı'); ?></p>
                    <p class="text-gray-500 dark:text-gray-400 text-xs"><?php echo esc_html($currentUser['email'] ?? ''); ?></p>
                </div>
            </div>
            <?php endif; ?>
            <a class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5" href="<?php echo admin_url('logout'); ?>">
                <span class="material-symbols-outlined text-gray-600 dark:text-gray-300 text-2xl">logout</span>
                <p class="text-gray-800 dark:text-white text-sm font-medium">Çıkış Yap</p>
            </a>
        </div>
    </div>
</aside>

<script>
function toggleContentMenu() {
    const submenu = document.getElementById('content-submenu');
    const arrow = document.getElementById('content-menu-arrow');
    
    if (submenu.classList.contains('hidden')) {
        submenu.classList.remove('hidden');
        arrow.textContent = 'expand_less';
    } else {
        submenu.classList.add('hidden');
        arrow.textContent = 'expand_more';
    }
}

function toggleDesignMenu() {
    const submenu = document.getElementById('design-submenu');
    const arrow = document.getElementById('design-menu-arrow');
    
    if (submenu.classList.contains('hidden')) {
        submenu.classList.remove('hidden');
        arrow.textContent = 'expand_less';
    } else {
        submenu.classList.add('hidden');
        arrow.textContent = 'expand_more';
    }
}
</script>
