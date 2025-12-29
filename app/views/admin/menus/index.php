<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo isset($title) ? esc_html($title) : 'Menü Yönetimi'; ?></title>
    
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
    
    
    
    
    <!-- Material Icons Font Preload -->
    
    
    <!-- Custom CSS (Font-face içeriyor) -->
    <link href="<?php echo rtrim(site_url(), '/') . '/admin/css/admin-dashboard.css'; ?>" rel="stylesheet"/>
    
    <!-- Dark Mode Toggle Script -->
    <script src="<?php echo rtrim(site_url(), '/') . '/admin/js/dark-mode.js'; ?>"></script>
    
    <!-- Tailwind CSS -->
    <script src="<?php echo ViewRenderer::assetUrl('assets/js/tailwind-admin.min.js'); ?>"></script>
    
    <!-- Google Fonts - Inter -->
    <link rel="stylesheet" href="<?php echo ViewRenderer::assetUrl('assets/css/fonts.css'); ?>">
    
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
</head>
<body class="font-display bg-background-light dark:bg-background-dark">
    <div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
        <div class="flex min-h-screen">
            <!-- SideNavBar -->
            <?php 
            $currentPage = 'menus';
            include __DIR__ . '/../snippets/sidebar.php'; 
            ?>

        <!-- Content Area with Header -->
        <div class="flex-1 flex flex-col lg:ml-64">
            <!-- Top Header -->
            <?php include __DIR__ . '/../snippets/top-header.php'; ?>

            <!-- Main Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b] overflow-y-auto">
                <div class="layout-content-container flex flex-col w-full mx-auto max-w-7xl">
                    <!-- PageHeading -->
                    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <div class="flex flex-col gap-2">
                            <p class="text-gray-900 dark:text-white text-2xl sm:text-3xl font-bold tracking-tight">Menü Yönetimi</p>
                            <p class="text-gray-500 dark:text-gray-400 text-sm sm:text-base font-normal leading-normal">Sitenizin navigasyon menülerini oluşturun ve düzenleyin.</p>
                        </div>
                        <a href="<?php echo admin_url('menus/create'); ?>" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors min-h-[44px] w-full sm:w-auto justify-center sm:justify-start">
                            <span class="material-symbols-outlined text-lg sm:text-xl">add</span>
                            <span class="text-sm font-medium">Yeni Menü</span>
                        </a>
                    </header>

                    <!-- Success/Error Message -->
                    <?php if (isset($message) && $message): ?>
                        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200'; ?>">
                            <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Menus List -->
                    <section class="rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark overflow-hidden">
                        <?php if (empty($menus)): ?>
                            <div class="p-8 sm:p-12 text-center">
                                <span class="material-symbols-outlined text-gray-400 dark:text-gray-600 text-5xl sm:text-6xl mb-4">menu</span>
                                <p class="text-gray-500 dark:text-gray-400 text-base sm:text-lg mb-2">Henüz menü oluşturulmamış</p>
                                <p class="text-gray-400 dark:text-gray-500 text-sm mb-6">Yeni bir menü oluşturmak için yukarıdaki butona tıklayın.</p>
                                <a href="<?php echo admin_url('menus/create'); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors min-h-[44px]">
                                    <span class="material-symbols-outlined">add</span>
                                    <span>İlk Menüyü Oluştur</span>
                                </a>
                            </div>
                        <?php else: ?>
                            <!-- Desktop Table View -->
                            <div class="hidden lg:block overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Menü Adı</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Konum</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Öğe Sayısı</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Durum</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Oluşturulma</th>
                                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                        <?php foreach ($menus as $menu): ?>
                                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                                            <span class="material-symbols-outlined text-primary text-lg">menu</span>
                                                        </div>
                                                        <div class="min-w-0">
                                                            <p class="text-gray-900 dark:text-white font-medium truncate"><?php echo esc_html($menu['name'] ?? ''); ?></p>
                                                            <?php if (!empty($menu['description'])): ?>
                                                                <p class="text-gray-500 dark:text-gray-400 text-xs line-clamp-1"><?php echo esc_html(mb_substr($menu['description'] ?? '', 0, 50)); ?>...</p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                                        <?php echo esc_html($locations[$menu['location'] ?? ''] ?? ($menu['location'] ?? '')); ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <p class="text-gray-600 dark:text-gray-400 text-sm"><?php echo esc_html($menu['item_count'] ?? 0); ?> öğe</p>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <?php if (($menu['status'] ?? '') === 'active'): ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                                            Aktif
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-300">
                                                            Pasif
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <p class="text-gray-600 dark:text-gray-400 text-sm"><?php echo date('d.m.Y H:i', strtotime($menu['created_at'] ?? 'now')); ?></p>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <a href="<?php echo admin_url('menus/edit/' . ($menu['id'] ?? '')); ?>" class="p-2 text-gray-600 dark:text-gray-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors min-h-[36px] min-w-[36px] flex items-center justify-center" title="Düzenle">
                                                            <span class="material-symbols-outlined text-xl">edit</span>
                                                        </a>
                                                        <a href="<?php echo admin_url('menus/delete/' . ($menu['id'] ?? '')); ?>" class="p-2 text-gray-600 dark:text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors min-h-[36px] min-w-[36px] flex items-center justify-center" onclick="return confirm('Bu menüyü silmek istediğinizden emin misiniz? Tüm menü öğeleri de silinecektir.');" title="Sil">
                                                            <span class="material-symbols-outlined text-xl">delete</span>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Mobile Card View -->
                            <div class="lg:hidden p-4 space-y-4">
                                <?php foreach ($menus as $menu): ?>
                                <div class="bg-gray-50 dark:bg-white/5 rounded-lg border border-gray-200 dark:border-white/10 p-4 space-y-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-start gap-3 flex-1 min-w-0">
                                            <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <span class="material-symbols-outlined text-primary text-lg">menu</span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h3 class="text-base font-semibold text-gray-900 dark:text-white line-clamp-2 mb-1"><?php echo esc_html($menu['name'] ?? ''); ?></h3>
                                                <?php if (!empty($menu['description'])): ?>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mb-2"><?php echo esc_html($menu['description']); ?></p>
                                                <?php endif; ?>
                                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                                        <?php echo esc_html($locations[$menu['location'] ?? ''] ?? ($menu['location'] ?? '')); ?>
                                                    </span>
                                                    <?php if (($menu['status'] ?? '') === 'active'): ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                                            Aktif
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-300">
                                                            Pasif
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo esc_html($menu['item_count'] ?? 0); ?> öğe</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1"><?php echo date('d.m.Y H:i', strtotime($menu['created_at'] ?? 'now')); ?></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1 flex-shrink-0">
                                            <a href="<?php echo admin_url('menus/edit/' . ($menu['id'] ?? '')); ?>" class="p-2 text-gray-600 dark:text-gray-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors min-h-[36px] min-w-[36px] flex items-center justify-center" title="Düzenle">
                                                <span class="material-symbols-outlined text-lg">edit</span>
                                            </a>
                                            <a href="<?php echo admin_url('menus/delete/' . ($menu['id'] ?? '')); ?>" class="p-2 text-gray-600 dark:text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors min-h-[36px] min-w-[36px] flex items-center justify-center" onclick="return confirm('Bu menüyü silmek istediğinizden emin misiniz? Tüm menü öğeleri de silinecektir.');" title="Sil">
                                                <span class="material-symbols-outlined text-lg">delete</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>
                </main>
            </div>
        </div>
    </div>
</body>
</html>

