<?php
/**
 * Menü Yönetimi - WordPress Tarzı
 * Tek sayfada menü oluşturma ve düzenleme
 */

// Verileri hazırla
$menu = $menu ?? null;
$items = $items ?? [];
$locations = $locations ?? ['header' => 'Header', 'footer' => 'Footer'];
$pages = $pages ?? [];
$categories = $categories ?? [];
$posts = $posts ?? [];

// Menü var mı kontrolü
$isNewMenu = empty($menu['id']);
$menuName = $menu['name'] ?? '';
$menuLocation = $menu['location'] ?? 'header';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo $isNewMenu ? 'Yeni Menü Oluştur' : esc_html($menuName) . ' - Menü Düzenle'; ?></title>
    
    <!-- Dark Mode - Sayfa yüklenmeden önce çalışmalı (FOUC önleme) -->
    <script>
        (function() {
            'use strict';
            const DARK_MODE_KEY = 'admin_dark_mode';
            const htmlElement = document.documentElement;
            let darkModePreference = null;
            try {
                const savedPreference = localStorage.getItem(DARK_MODE_KEY);
                if (savedPreference === 'dark' || savedPreference === 'light') {
                    darkModePreference = savedPreference === 'dark';
                }
            } catch (e) {}
            if (darkModePreference === null) {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    darkModePreference = true;
                } else {
                    darkModePreference = false;
                }
            }
            if (darkModePreference) {
                htmlElement.classList.add('dark');
            } else {
                htmlElement.classList.remove('dark');
            }
        })();
    </script>
    
    
    
    <link href="<?php echo rtrim(site_url(), '/') . '/admin/css/admin-dashboard.css'; ?>" rel="stylesheet"/>
    
    <!-- Dark Mode Toggle Script -->
    <script src="<?php echo rtrim(site_url(), '/') . '/admin/js/dark-mode.js'; ?>"></script>
    
    <script src="<?php echo ViewRenderer::assetUrl('assets/js/tailwind-admin.min.js'); ?>"></script>
    <link rel="stylesheet" href="<?php echo ViewRenderer::assetUrl('assets/css/fonts.css'); ?>">
    <link rel="stylesheet" href="<?php echo ViewRenderer::assetUrl('assets/css/fonts.css'); ?>">
    
    <!-- SortableJS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#137fec",
                        "background-light": "#f6f7f8",
                        "background-dark": "#101922",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                },
            },
        }
    </script>
    
    <style>
        /* Prevent FOUC */
        .loading-hide { opacity: 0; }
        .loaded .loading-hide { opacity: 1; transition: opacity 0.15s ease; }
        body { margin: 0; background: #15202b; }
        
        /* Accordion */
        .accordion-content { max-height: 0; overflow: hidden; transition: max-height 0.3s ease; }
        .accordion-content.open { max-height: 500px; }
        
        /* Menu Item Styles */
        .menu-item { user-select: none; transition: margin-left 0.2s; }
        .menu-item.sortable-ghost { opacity: 0.4; }
        .menu-item.sortable-chosen { box-shadow: 0 4px 12px rgba(19, 127, 236, 0.3); }
        
        /* Nested levels */
        .nested-container { 
            margin-left: 1.5rem; 
            padding-left: 1rem; 
            border-left: 2px dashed rgba(19, 127, 236, 0.3); 
            min-height: 10px;
            transition: all 0.2s;
        }
        body:not(.is-dragging) .nested-container.hidden { display: none !important; }
        .nested-container:not(.hidden) { padding: 0.5rem 0 0.5rem 1rem; }
        
        /* Show nested drop zone when dragging */
        body.is-dragging .nested-container.hidden {
            display: block !important;
            min-height: 50px;
            background: rgba(19, 127, 236, 0.05);
            border: 2px dashed rgba(19, 127, 236, 0.3);
            border-radius: 8px;
            margin-top: 8px;
        }
        body.is-dragging .nested-container.hidden::before {
            content: 'Alt menü için buraya bırakın';
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            min-height: 50px;
            color: rgba(19, 127, 236, 0.6);
            font-size: 12px;
        }
        
        /* Highlight drop zone */
        .nested-container.sortable-fallback,
        .nested-container:has(.sortable-ghost) {
            background: rgba(19, 127, 236, 0.1);
            border-color: rgba(19, 127, 236, 0.5);
        }
        
        /* Item editing */
        .menu-item-content { transition: all 0.2s; }
        .menu-item:hover .menu-item-content { border-color: rgba(19, 127, 236, 0.5); }
        .menu-item.editing .menu-item-content { border-color: #137fec; background: rgba(19, 127, 236, 0.05); }
        
        /* Drag handle */
        .drag-handle { cursor: grab; }
        .drag-handle:active { cursor: grabbing; }
        
        /* Empty state */
        .empty-drop-zone { border: 2px dashed rgba(107, 114, 128, 0.3); min-height: 200px; }
        .empty-drop-zone.drag-over { border-color: #137fec; background: rgba(19, 127, 236, 0.05); }
    </style>
</head>
<body class="font-display bg-background-light dark:bg-background-dark">
    <script>document.body.classList.add('loaded');</script>
    
    <div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden loading-hide">
        <div class="flex min-h-screen">
            <?php 
            $currentPage = 'menus';
            include __DIR__ . '/../snippets/sidebar.php'; 
            ?>

            <main class="main-content-with-sidebar flex-1 p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-[#15202b]">
                <div class="max-w-7xl mx-auto">
                    
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                        <div class="min-w-0 flex-1">
                            <nav class="flex items-center gap-2 text-xs sm:text-sm mb-2 overflow-x-auto scrollbar-hide">
                                <a href="<?php echo admin_url('menus'); ?>" class="text-gray-500 hover:text-primary transition-colors whitespace-nowrap">Menüler</a>
                                <span class="material-symbols-outlined text-gray-400 text-sm sm:text-base flex-shrink-0">chevron_right</span>
                                <span class="text-gray-900 dark:text-white truncate"><?php echo $isNewMenu ? 'Yeni Menü' : esc_html($menuName); ?></span>
                            </nav>
                            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white line-clamp-2">
                                <?php echo $isNewMenu ? 'Yeni Menü Oluştur' : 'Menü Düzenle'; ?>
                            </h1>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 w-full sm:w-auto">
                            <?php if (!$isNewMenu): ?>
                            <button type="button" onclick="deleteMenu()" class="px-4 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors text-sm font-medium flex items-center justify-center gap-2 min-h-[44px]">
                                <span class="material-symbols-outlined text-base sm:text-lg">delete</span>
                                <span class="hidden sm:inline">Menüyü Sil</span>
                            </button>
                            <?php endif; ?>
                            <button type="button" onclick="saveMenu()" class="px-6 py-2.5 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-sm font-semibold flex items-center justify-center gap-2 shadow-lg shadow-primary/25 min-h-[44px]">
                                <span class="material-symbols-outlined text-base sm:text-lg">save</span>
                                <span class="hidden sm:inline">Menüyü Kaydet</span>
                                <span class="sm:hidden">Kaydet</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Messages -->
                    <?php if (isset($message) && $message): ?>
                    <div class="mb-6 p-4 rounded-xl <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300'; ?> flex items-center gap-3">
                        <span class="material-symbols-outlined"><?php echo $messageType === 'success' ? 'check_circle' : 'error'; ?></span>
                            <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Main Content Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6">
                        
                        <!-- Left Panel: Add Items -->
                        <div class="lg:col-span-4 xl:col-span-3 space-y-3 sm:space-y-4 order-2 lg:order-1">
                            
                            <!-- Sayfalar Accordion -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                <button type="button" onclick="toggleAccordion('pages')" class="w-full px-3 sm:px-4 py-2.5 sm:py-3 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors min-h-[44px]">
                                    <span class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                                        <span class="material-symbols-outlined text-primary text-lg sm:text-xl flex-shrink-0">description</span>
                                        <span class="font-semibold text-gray-900 dark:text-white text-xs sm:text-sm truncate">Sayfalar</span>
                                    </span>
                                    <span class="material-symbols-outlined text-gray-400 accordion-arrow text-lg sm:text-xl flex-shrink-0" id="arrow-pages">expand_more</span>
                                </button>
                                <div class="accordion-content" id="accordion-pages">
                                    <div class="px-3 sm:px-4 pb-3 sm:pb-4 space-y-2 max-h-60 overflow-y-auto">
                                        <?php if (!empty($pages)): ?>
                                            <?php foreach ($pages as $page): ?>
                                            <label class="flex items-center gap-3 p-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg cursor-pointer">
                                                <input type="checkbox" class="page-checkbox w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary" 
                                                       data-title="<?php echo esc_attr($page['title']); ?>" 
                                                       data-url="<?php echo esc_attr('/' . $page['slug']); ?>">
                                                <span class="text-sm text-gray-700 dark:text-gray-300"><?php echo esc_html($page['title']); ?></span>
                                            </label>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="text-sm text-gray-500 py-2">Henüz sayfa oluşturulmamış</p>
                                        <?php endif; ?>
                                        <button type="button" onclick="addCheckedItems('page')" class="w-full mt-2 px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-xs sm:text-sm font-medium flex items-center justify-center gap-2 min-h-[36px]">
                                            <span class="material-symbols-outlined text-base sm:text-lg">add</span>
                                            <span class="hidden sm:inline">Menüye Ekle</span>
                                            <span class="sm:hidden">Ekle</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Yazılar Accordion -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                <button type="button" onclick="toggleAccordion('posts')" class="w-full px-3 sm:px-4 py-2.5 sm:py-3 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors min-h-[44px]">
                                    <span class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                                        <span class="material-symbols-outlined text-orange-500 text-lg sm:text-xl flex-shrink-0">article</span>
                                        <span class="font-semibold text-gray-900 dark:text-white text-xs sm:text-sm truncate">Yazılar</span>
                                    </span>
                                    <span class="material-symbols-outlined text-gray-400 accordion-arrow text-lg sm:text-xl flex-shrink-0" id="arrow-posts">expand_more</span>
                                </button>
                                <div class="accordion-content" id="accordion-posts">
                                    <div class="px-3 sm:px-4 pb-3 sm:pb-4 space-y-2 max-h-60 overflow-y-auto">
                                        <?php if (!empty($posts)): ?>
                                            <?php foreach ($posts as $post): ?>
                                            <label class="flex items-center gap-3 p-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg cursor-pointer">
                                                <input type="checkbox" class="post-checkbox w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary" 
                                                       data-title="<?php echo esc_attr($post['title']); ?>" 
                                                       data-url="<?php echo esc_attr('/blog/' . $post['slug']); ?>">
                                                <span class="text-sm text-gray-700 dark:text-gray-300"><?php echo esc_html($post['title']); ?></span>
                                            </label>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="text-sm text-gray-500 py-2">Henüz yazı oluşturulmamış</p>
                                        <?php endif; ?>
                                        <button type="button" onclick="addCheckedItems('post')" class="w-full mt-2 px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm font-medium flex items-center justify-center gap-2">
                                            <span class="material-symbols-outlined text-lg">add</span>
                                            Menüye Ekle
                                        </button>
                                    </div>
                                </div>
                            </div>
                                    
                            <!-- Kategoriler Accordion -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                <button type="button" onclick="toggleAccordion('categories')" class="w-full px-3 sm:px-4 py-2.5 sm:py-3 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors min-h-[44px]">
                                    <span class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                                        <span class="material-symbols-outlined text-purple-500 text-lg sm:text-xl flex-shrink-0">folder</span>
                                        <span class="font-semibold text-gray-900 dark:text-white text-xs sm:text-sm truncate">Kategoriler</span>
                                    </span>
                                    <span class="material-symbols-outlined text-gray-400 accordion-arrow text-lg sm:text-xl flex-shrink-0" id="arrow-categories">expand_more</span>
                                </button>
                                <div class="accordion-content" id="accordion-categories">
                                    <div class="px-3 sm:px-4 pb-3 sm:pb-4 space-y-2 max-h-60 overflow-y-auto">
                                        <?php if (!empty($categories)): ?>
                                            <?php foreach ($categories as $cat): ?>
                                            <label class="flex items-center gap-3 p-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg cursor-pointer">
                                                <input type="checkbox" class="category-checkbox w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary" 
                                                       data-title="<?php echo esc_attr($cat['name']); ?>" 
                                                       data-url="<?php echo esc_attr('/kategori/' . $cat['slug']); ?>">
                                                <span class="text-sm text-gray-700 dark:text-gray-300"><?php echo esc_html($cat['name']); ?></span>
                                            </label>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="text-sm text-gray-500 py-2">Henüz kategori oluşturulmamış</p>
                                        <?php endif; ?>
                                        <button type="button" onclick="addCheckedItems('category')" class="w-full mt-2 px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm font-medium flex items-center justify-center gap-2">
                                            <span class="material-symbols-outlined text-lg">add</span>
                                            Menüye Ekle
                                        </button>
                                    </div>
                                </div>
                                    </div>
                                    
                            <!-- Özel Bağlantı -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                <button type="button" onclick="toggleAccordion('custom')" class="w-full px-3 sm:px-4 py-2.5 sm:py-3 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors min-h-[44px]">
                                    <span class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                                        <span class="material-symbols-outlined text-green-500 text-lg sm:text-xl flex-shrink-0">link</span>
                                        <span class="font-semibold text-gray-900 dark:text-white text-xs sm:text-sm truncate">Özel Bağlantı</span>
                                    </span>
                                    <span class="material-symbols-outlined text-gray-400 accordion-arrow text-lg sm:text-xl flex-shrink-0" id="arrow-custom">expand_more</span>
                                </button>
                                <div class="accordion-content open" id="accordion-custom">
                                    <div class="px-3 sm:px-4 pb-3 sm:pb-4 space-y-3">
                                    <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">URL</label>
                                            <input type="text" id="customUrl" value="#" placeholder="https://..." 
                                                   class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Başlık</label>
                                            <input type="text" id="customTitle" placeholder="Menü başlığı" 
                                                   class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                        <button type="button" onclick="addCustomLink()" class="w-full px-3 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-xs sm:text-sm font-medium flex items-center justify-center gap-2 min-h-[36px]">
                                        <span class="material-symbols-outlined text-base sm:text-lg">add</span>
                                        <span class="hidden sm:inline">Menüye Ekle</span>
                                        <span class="sm:hidden">Ekle</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        </div>
                        
                        <!-- Right Panel: Menu Builder -->
                        <div class="lg:col-span-8 xl:col-span-9 order-1 lg:order-2">
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                                
                                <!-- Menu Header -->
                                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 dark:border-gray-700">
                                    <div class="flex flex-col sm:flex-row sm:items-end gap-3 sm:gap-4">
                                        <div class="flex-1">
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Menü Adı</label>
                                            <input type="text" id="menuName" value="<?php echo esc_attr($menuName); ?>" placeholder="Menü adını girin..." 
                                                   class="w-full px-3 py-2 text-sm sm:text-base rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent font-semibold">
                                        </div>
                                        <div class="w-full sm:w-48">
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Konum</label>
                                            <select id="menuLocation" class="w-full px-3 py-2 text-sm sm:text-base rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                                <?php foreach ($locations as $key => $label): ?>
                                                <option value="<?php echo esc_attr($key); ?>" <?php echo $menuLocation === $key ? 'selected' : ''; ?>><?php echo esc_html($label); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Menu Items -->
                                <div class="p-4 sm:p-6" id="menuBuilderContainer">
                                    <div id="menuItems" class="space-y-2 min-h-[300px]">
                                    <?php if (empty($items)): ?>
                                        <div id="emptyState" class="empty-drop-zone rounded-xl flex flex-col items-center justify-center py-16 text-center">
                                            <span class="material-symbols-outlined text-5xl text-gray-300 dark:text-gray-600 mb-4">menu</span>
                                            <p class="text-gray-500 dark:text-gray-400 font-medium mb-1">Henüz menü öğesi yok</p>
                                            <p class="text-gray-400 dark:text-gray-500 text-sm">Sol panelden öğe ekleyerek başlayın</p>
                                        </div>
                                    <?php else: ?>
                                            <?php 
                                            // Recursive render function
                                            function renderMenuItemNew($item, $allItems, $level = 0) {
                                                $children = array_filter($allItems, fn($i) => $i['parent_id'] == $item['id']);
                                                usort($children, fn($a, $b) => ($a['order'] ?? 0) - ($b['order'] ?? 0));
                                            ?>
                                            <div class="menu-item" data-id="<?php echo $item['id']; ?>" data-level="<?php echo $level; ?>">
                                                <div class="menu-item-content flex items-center gap-2 sm:gap-3 p-2.5 sm:p-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg group">
                                                    <span class="material-symbols-outlined text-gray-400 drag-handle text-base sm:text-lg flex-shrink-0 cursor-grab active:cursor-grabbing">drag_indicator</span>
                                                        
                                                        <div class="flex-1 min-w-0">
                                                        <div class="flex items-center gap-2 flex-wrap">
                                                            <span class="item-title font-medium text-gray-900 dark:text-white text-xs sm:text-sm truncate"><?php echo esc_html($item['title']); ?></span>
                                                            <?php if ($item['status'] !== 'active'): ?>
                                                            <span class="px-1.5 py-0.5 text-xs bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-400 rounded flex-shrink-0">Taslak</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <span class="item-url text-xs text-gray-500 dark:text-gray-400 truncate block"><?php echo esc_html($item['url']); ?></span>
                                                    </div>
                                                    
                                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
                                                        <button type="button" onclick="toggleItemEdit(<?php echo $item['id']; ?>)" class="p-1.5 text-gray-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors min-h-[32px] min-w-[32px] flex items-center justify-center" title="Düzenle">
                                                            <span class="material-symbols-outlined text-base sm:text-lg">expand_more</span>
                                                        </button>
                                                        <button type="button" onclick="removeItem(<?php echo $item['id']; ?>)" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors min-h-[32px] min-w-[32px] flex items-center justify-center" title="Kaldır">
                                                            <span class="material-symbols-outlined text-base sm:text-lg">close</span>
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                <!-- Inline Edit Form (hidden by default) -->
                                                <div class="item-edit-form hidden mt-2 p-3 sm:p-4 bg-gray-50 dark:bg-gray-700/30 border border-gray-200 dark:border-gray-600 rounded-lg space-y-3">
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Başlık</label>
                                                            <input type="text" class="edit-title w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-base" value="<?php echo esc_attr($item['title']); ?>">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">URL</label>
                                                            <input type="text" class="edit-url w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-base" value="<?php echo esc_attr($item['url']); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Hedef</label>
                                                            <select class="edit-target w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                                <option value="_self" <?php echo ($item['target'] ?? '_self') === '_self' ? 'selected' : ''; ?>>Aynı Pencere</option>
                                                                <option value="_blank" <?php echo ($item['target'] ?? '_self') === '_blank' ? 'selected' : ''; ?>>Yeni Pencere</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">İkon</label>
                                                            <input type="text" class="edit-icon w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" value="<?php echo esc_attr($item['icon'] ?? ''); ?>" placeholder="home">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">CSS Sınıfı</label>
                                                            <input type="text" class="edit-css w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" value="<?php echo esc_attr($item['css_class'] ?? ''); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-2">
                                                        <label class="flex items-center gap-2 cursor-pointer min-h-[36px]">
                                                            <input type="checkbox" class="edit-status w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary" <?php echo $item['status'] === 'active' ? 'checked' : ''; ?>>
                                                            <span class="text-sm text-gray-700 dark:text-gray-300">Aktif</span>
                                                        </label>
                                                        <button type="button" onclick="saveItemEdit(<?php echo $item['id']; ?>)" class="px-4 py-1.5 bg-primary text-white text-sm rounded-lg hover:bg-primary/90 transition-colors min-h-[36px] w-full sm:w-auto">
                                                            Güncelle
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                <?php if (!empty($children)): ?>
                                                <div class="nested-container mt-2 space-y-2 sortable-nested">
                                                    <?php foreach ($children as $child): renderMenuItemNew($child, $allItems, $level + 1); endforeach; ?>
                                                </div>
                                                <?php else: ?>
                                                <div class="nested-container mt-2 space-y-2 sortable-nested hidden"></div>
                                                <?php endif; ?>
                                                </div>
                                            <?php
                                            }
                                            
                                            // Root items
                                            $rootItems = array_filter($items, fn($i) => empty($i['parent_id']));
                                            usort($rootItems, fn($a, $b) => ($a['order'] ?? 0) - ($b['order'] ?? 0));
                                            foreach ($rootItems as $item): renderMenuItemNew($item, $items); endforeach;
                                            ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Help Text -->
                                    <div class="mt-4 sm:mt-6 pt-3 sm:pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <div class="flex items-start gap-2 sm:gap-3 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                                            <span class="material-symbols-outlined text-base sm:text-lg flex-shrink-0 mt-0.5">info</span>
                                            <div class="min-w-0">
                                                <p class="mb-1"><strong>İpucu:</strong> Öğeleri sürükleyerek sıralayın.</p>
                                                <p class="break-words">Alt menü oluşturmak için bir öğeyi başka bir öğenin üzerine sürükleyin.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Configuration
        const menuId = <?php echo $menu['id'] ?? 'null'; ?>;
        const baseUrl = '<?php echo rtrim(site_url(), '/'); ?>/admin.php?page=';
        let menuItems = <?php echo json_encode($items); ?>;
        let itemIdCounter = <?php echo !empty($items) ? max(array_column($items, 'id')) + 1 : 1; ?>;
        let hasChanges = false;
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            initSortable();
            trackChanges();
        });
        
        // Accordion toggle
        function toggleAccordion(id) {
            const content = document.getElementById('accordion-' + id);
            const arrow = document.getElementById('arrow-' + id);
            
            content.classList.toggle('open');
            arrow.style.transform = content.classList.contains('open') ? 'rotate(180deg)' : '';
        }
        
        // Initialize Sortable.js
        function initSortable() {
            const container = document.getElementById('menuItems');
            if (!container) return;
            
            // Main container
            new Sortable(container, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                fallbackOnBody: true,
                swapThreshold: 0.65,
                group: {
                    name: 'menu',
                    pull: true,
                    put: true
                },
                onEnd: handleSort,
                onMove: handleMove
            });
            
            // Nested containers
            document.querySelectorAll('.sortable-nested').forEach(initNestedSortable);
        }
        
        function initNestedSortable(el) {
            // Show container if it has children
            if (el.children.length > 0) {
                el.classList.remove('hidden');
            }
            
                new Sortable(el, {
                    animation: 150,
                    handle: '.drag-handle',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                fallbackOnBody: true,
                swapThreshold: 0.65,
                group: {
                    name: 'menu',
                    pull: true,
                    put: true
                },
                onEnd: handleSort,
                onAdd: function(evt) {
                    // Show container when item added
                    evt.to.classList.remove('hidden');
                    updateNestedLevels();
                },
                onRemove: function(evt) {
                    // Hide container if empty
                    if (evt.from.children.length === 0) {
                        evt.from.classList.add('hidden');
                    }
                    updateNestedLevels();
                }
            });
        }
        
        function handleMove(evt) {
            // Prevent moving an item into its own children
            const draggedItem = evt.dragged;
            const targetContainer = evt.to;
            
            // Check if target is inside dragged item
            if (draggedItem.contains(targetContainer)) {
                return false;
            }
            return true;
        }
        
        function handleSort(evt) {
            hasChanges = true;
            
            // Show/hide nested containers based on content
            document.querySelectorAll('.sortable-nested').forEach(container => {
                if (container.children.length > 0) {
                    container.classList.remove('hidden');
                } else {
                    container.classList.add('hidden');
                }
            });
            
            updateNestedLevels();
        }
        
        // Update visual nesting levels
        function updateNestedLevels() {
            function setLevel(container, level) {
                const items = container.querySelectorAll(':scope > .menu-item');
                items.forEach(item => {
                    item.dataset.level = level;
                    item.style.marginLeft = (level * 24) + 'px';
                    
                    const nested = item.querySelector(':scope > .sortable-nested');
                    if (nested) {
                        setLevel(nested, level + 1);
                    }
                });
            }
            
            const mainContainer = document.getElementById('menuItems');
            setLevel(mainContainer, 0);
        }
        
        // Track changes
        function trackChanges() {
            document.getElementById('menuName')?.addEventListener('input', () => hasChanges = true);
            document.getElementById('menuLocation')?.addEventListener('change', () => hasChanges = true);
        }
        
        // Add checked items from list
        function addCheckedItems(type) {
            const checkboxes = document.querySelectorAll('.' + type + '-checkbox:checked');
            if (checkboxes.length === 0) {
                alert('Lütfen en az bir öğe seçin.');
                return;
            }
            
            checkboxes.forEach(cb => {
                addItemToMenu(cb.dataset.title, cb.dataset.url);
                cb.checked = false;
            });
        }
        
        // Add custom link
        function addCustomLink() {
            const title = document.getElementById('customTitle').value.trim();
            const url = document.getElementById('customUrl').value.trim() || '#';
            
            if (!title) {
                alert('Lütfen bir başlık girin.');
                return;
            }
            
            addItemToMenu(title, url);
            document.getElementById('customTitle').value = '';
            document.getElementById('customUrl').value = '#';
        }
        
        // Add item to menu
        function addItemToMenu(title, url) {
            const container = document.getElementById('menuItems');
            const emptyState = document.getElementById('emptyState');
            
            // Remove empty state
            if (emptyState) emptyState.remove();
            
            // Create new item
            const itemId = 'new_' + itemIdCounter++;
            const itemHtml = createMenuItemHtml(itemId, title, url);
            
            container.insertAdjacentHTML('beforeend', itemHtml);
            
            // Initialize sortable for new nested container
            const newItem = container.querySelector(`[data-id="${itemId}"]`);
            const nestedContainer = newItem.querySelector('.sortable-nested');
            if (nestedContainer) initNestedSortable(nestedContainer);
            
            hasChanges = true;
        }
        
        // Create menu item HTML
        function createMenuItemHtml(id, title, url) {
            return `
                <div class="menu-item" data-id="${id}" data-level="0">
                    <div class="menu-item-content flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg group">
                        <span class="material-symbols-outlined text-gray-400 drag-handle">drag_indicator</span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="item-title font-medium text-gray-900 dark:text-white text-sm truncate">${escapeHtml(title)}</span>
                            </div>
                            <span class="item-url text-xs text-gray-500 dark:text-gray-400 truncate block">${escapeHtml(url)}</span>
                        </div>
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button type="button" onclick="toggleItemEdit('${id}')" class="p-1.5 text-gray-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors">
                                <span class="material-symbols-outlined text-lg">expand_more</span>
                            </button>
                            <button type="button" onclick="removeItem('${id}')" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                <span class="material-symbols-outlined text-lg">close</span>
                            </button>
                        </div>
                    </div>
                    <div class="item-edit-form hidden mt-2 p-4 bg-gray-50 dark:bg-gray-700/30 border border-gray-200 dark:border-gray-600 rounded-lg space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Başlık</label>
                                <input type="text" class="edit-title w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" value="${escapeHtml(title)}">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">URL</label>
                                <input type="text" class="edit-url w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" value="${escapeHtml(url)}">
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Hedef</label>
                                <select class="edit-target w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    <option value="_self">Aynı Pencere</option>
                                    <option value="_blank">Yeni Pencere</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">İkon</label>
                                <input type="text" class="edit-icon w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="home">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">CSS Sınıfı</label>
                                <input type="text" class="edit-css w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" class="edit-status w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary" checked>
                                <span class="text-sm text-gray-700 dark:text-gray-300">Aktif</span>
                            </label>
                            <button type="button" onclick="saveItemEdit('${id}')" class="px-4 py-1.5 bg-primary text-white text-sm rounded-lg hover:bg-primary/90 transition-colors">
                                Güncelle
                            </button>
                        </div>
                    </div>
                    <div class="nested-container mt-2 space-y-2 sortable-nested hidden" data-parent="${id}"></div>
                </div>
            `;
        }
        
        // Make nested visible on hover during drag
        let isDragging = false;
        document.addEventListener('mousedown', function(e) {
            if (e.target.closest('.drag-handle')) {
                isDragging = true;
                document.body.classList.add('is-dragging');
            }
        });
        document.addEventListener('mouseup', function() {
            isDragging = false;
            document.body.classList.remove('is-dragging');
        });
        
        // Toggle item edit form
        function toggleItemEdit(id) {
            const item = document.querySelector(`[data-id="${id}"]`);
            if (!item) return;
            
            const form = item.querySelector('.item-edit-form');
            const btn = item.querySelector('.menu-item-content button');
            
            form.classList.toggle('hidden');
            item.classList.toggle('editing');
            
            const icon = btn.querySelector('.material-symbols-outlined');
            icon.textContent = form.classList.contains('hidden') ? 'expand_more' : 'expand_less';
        }
        
        // Save item edit
        function saveItemEdit(id) {
            const item = document.querySelector(`[data-id="${id}"]`);
            if (!item) return;
            
            const title = item.querySelector('.edit-title').value;
            const url = item.querySelector('.edit-url').value;
            
            // Update display
            item.querySelector('.item-title').textContent = title;
            item.querySelector('.item-url').textContent = url;
            
            // Close form
            toggleItemEdit(id);
            hasChanges = true;
        }
        
        // Remove item
        function removeItem(id) {
            if (!confirm('Bu öğeyi kaldırmak istediğinizden emin misiniz?')) return;
            
            const item = document.querySelector(`[data-id="${id}"]`);
            if (item) {
                item.remove();
                hasChanges = true;
                
                // Show empty state if no items
                const container = document.getElementById('menuItems');
                if (container.children.length === 0) {
                    container.innerHTML = `
                        <div id="emptyState" class="empty-drop-zone rounded-xl flex flex-col items-center justify-center py-16 text-center">
                            <span class="material-symbols-outlined text-5xl text-gray-300 dark:text-gray-600 mb-4">menu</span>
                            <p class="text-gray-500 dark:text-gray-400 font-medium mb-1">Henüz menü öğesi yok</p>
                            <p class="text-gray-400 dark:text-gray-500 text-sm">Sol panelden öğe ekleyerek başlayın</p>
                        </div>
                    `;
                }
            }
        }
        
        // Update items order
        function updateItemsOrder() {
            // Will be used when saving
        }
        
        // Collect all menu data
        function collectMenuData() {
            const items = [];
            let order = 0;
            
            function processContainer(container, parentId = null) {
                const menuItems = container.querySelectorAll(':scope > .menu-item');
                menuItems.forEach(item => {
                    const id = item.dataset.id;
                    const isNew = String(id).startsWith('new_');
                    
                    const itemData = {
                        id: isNew ? null : parseInt(id),
                        title: item.querySelector('.edit-title')?.value || item.querySelector('.item-title').textContent,
                        url: item.querySelector('.edit-url')?.value || item.querySelector('.item-url').textContent,
                        target: item.querySelector('.edit-target')?.value || '_self',
                        icon: item.querySelector('.edit-icon')?.value || '',
                        css_class: item.querySelector('.edit-css')?.value || '',
                        status: item.querySelector('.edit-status')?.checked ? 'active' : 'inactive',
                        parent_id: parentId,
                        sort_order: order++
                    };
                    
                    items.push(itemData);
                    
                    // Process nested items
                    const nestedContainer = item.querySelector(':scope > .sortable-nested');
                    if (nestedContainer) {
                        processContainer(nestedContainer, isNew ? 'temp_' + id : parseInt(id));
                    }
                });
            }
            
            const mainContainer = document.getElementById('menuItems');
            processContainer(mainContainer);
            
            return items;
        }
        
        // Save menu
        async function saveMenu() {
            const name = document.getElementById('menuName').value.trim();
            const location = document.getElementById('menuLocation').value;
            
            if (!name) {
                alert('Lütfen menü adını girin.');
                return;
            }
            
            const items = collectMenuData();
            
            const formData = new FormData();
            formData.append('name', name);
            formData.append('location', location);
            formData.append('items', JSON.stringify(items));
            if (menuId) formData.append('menu_id', menuId);
            
            try {
                const response = await fetch(baseUrl + 'menus/save', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    hasChanges = false;
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.reload();
                    }
                } else {
                    alert(data.message || 'Bir hata oluştu.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Bir hata oluştu.');
            }
        }
        
        // Delete menu
        async function deleteMenu() {
            if (!confirm('Bu menüyü silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) return;
            
            try {
                const response = await fetch(baseUrl + 'menus/delete/' + menuId, {
                    method: 'POST'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = baseUrl + 'menus';
                } else {
                    alert(data.message || 'Bir hata oluştu.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Bir hata oluştu.');
            }
        }
        
        // Helper: Escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Warn before leaving with unsaved changes
        window.addEventListener('beforeunload', function(e) {
            if (hasChanges) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
</body>
</html>
