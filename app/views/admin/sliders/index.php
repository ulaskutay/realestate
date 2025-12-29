<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo isset($title) ? esc_html($title) : 'Slider Yönetimi'; ?></title>
    
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
    
    <!-- DNS Prefetch ve Preconnect -->
    <link rel="dns-prefetch" href="https://fonts.googleapis.com"/>
    <link rel="dns-prefetch" href="https://fonts.gstatic.com"/>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    
    <!-- Material Icons Font Preload -->
    <link rel="preload" href="https://fonts.gstatic.com/s/materialsymbolsoutlined/v302/kJEhBvYX7BgnkSrUwT8OhrdQw4oELdPIeeII9v6oFsI.woff2" as="font" type="font/woff2" crossorigin/>
    
    <!-- Custom CSS (Font-face içeriyor) -->
    <link href="<?php echo rtrim(site_url(), '/') . '/admin/css/admin-dashboard.css'; ?>" rel="stylesheet"/>
    
    <!-- Dark Mode Toggle Script -->
    <script src="<?php echo rtrim(site_url(), '/') . '/admin/js/dark-mode.js'; ?>"></script>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    
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
            $currentPage = 'sliders';
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
                            <p class="text-gray-900 dark:text-white text-2xl sm:text-3xl font-bold tracking-tight">Slider Yönetimi</p>
                            <p class="text-gray-500 dark:text-gray-400 text-sm sm:text-base font-normal leading-normal">Sliderlarınızı oluşturun, düzenleyin ve yönetin.</p>
                        </div>
                        <a href="<?php echo admin_url('sliders/create'); ?>" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors min-h-[44px] w-full sm:w-auto justify-center sm:justify-start">
                            <span class="material-symbols-outlined text-lg sm:text-xl">add</span>
                            <span class="text-sm font-medium">Yeni Slider</span>
                        </a>
                    </header>

                    <!-- Success/Error Message -->
                    <?php if (isset($message) && $message): ?>
                        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200'; ?>">
                            <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Sliders List -->
                    <section class="rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark overflow-hidden">
                        <?php if (empty($sliders)): ?>
                            <div class="p-8 sm:p-12 text-center">
                                <span class="material-symbols-outlined text-gray-400 dark:text-gray-600 text-5xl sm:text-6xl mb-4">slideshow</span>
                                <p class="text-gray-500 dark:text-gray-400 text-base sm:text-lg mb-2">Henüz slider oluşturulmamış</p>
                                <p class="text-gray-400 dark:text-gray-500 text-sm mb-6">Yeni bir slider oluşturmak için yukarıdaki butona tıklayın.</p>
                                <a href="<?php echo admin_url('sliders/create'); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors min-h-[44px]">
                                    <span class="material-symbols-outlined">add</span>
                                    <span>İlk Slider'ı Oluştur</span>
                                </a>
                            </div>
                        <?php else: ?>
                            <!-- Desktop Table View -->
                            <div class="hidden lg:block overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Slider Adı</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Animasyon</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">İçerik</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Durum</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Oluşturulma</th>
                                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                        <?php foreach ($sliders as $slider): ?>
                                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                                <td class="px-6 py-4">
                                                    <p class="text-gray-900 dark:text-white font-medium"><?php echo esc_html($slider['name']); ?></p>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                                        <?php echo esc_html(ucfirst($slider['animation_type'])); ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <p class="text-gray-600 dark:text-gray-400 text-sm"><?php echo esc_html($slider['item_count'] ?? 0); ?> içerik</p>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <?php if ($slider['status'] === 'active'): ?>
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
                                                    <p class="text-gray-600 dark:text-gray-400 text-sm"><?php echo date('d.m.Y H:i', strtotime($slider['created_at'])); ?></p>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <a href="<?php echo admin_url('sliders/edit/' . $slider['id']); ?>" class="p-2 text-gray-600 dark:text-gray-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors min-h-[36px] min-w-[36px] flex items-center justify-center" title="Düzenle">
                                                            <span class="material-symbols-outlined text-xl">edit</span>
                                                        </a>
                                                        <a href="<?php echo admin_url('sliders/toggle/' . $slider['id']); ?>" class="p-2 text-gray-600 dark:text-gray-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors min-h-[36px] min-w-[36px] flex items-center justify-center" title="<?php echo $slider['status'] === 'active' ? 'Pasifleştir' : 'Aktifleştir'; ?>">
                                                            <span class="material-symbols-outlined text-xl"><?php echo $slider['status'] === 'active' ? 'visibility_off' : 'visibility'; ?></span>
                                                        </a>
                                                        <a href="<?php echo admin_url('sliders/delete/' . $slider['id']); ?>" class="p-2 text-gray-600 dark:text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors min-h-[36px] min-w-[36px] flex items-center justify-center" onclick="return confirm('Bu slider\'ı silmek istediğinizden emin misiniz?');" title="Sil">
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
                                <?php foreach ($sliders as $slider): ?>
                                <div class="bg-gray-50 dark:bg-white/5 rounded-lg border border-gray-200 dark:border-white/10 p-4 space-y-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-base font-semibold text-gray-900 dark:text-white line-clamp-2 mb-2"><?php echo esc_html($slider['name']); ?></h3>
                                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                                    <?php echo esc_html(ucfirst($slider['animation_type'])); ?>
                                                </span>
                                                <?php if ($slider['status'] === 'active'): ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                                        Aktif
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-300">
                                                        Pasif
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo esc_html($slider['item_count'] ?? 0); ?> içerik</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1"><?php echo date('d.m.Y H:i', strtotime($slider['created_at'])); ?></p>
                                        </div>
                                        <div class="flex items-center gap-1 flex-shrink-0">
                                            <a href="<?php echo admin_url('sliders/edit/' . $slider['id']); ?>" class="p-2 text-gray-600 dark:text-gray-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors min-h-[36px] min-w-[36px] flex items-center justify-center" title="Düzenle">
                                                <span class="material-symbols-outlined text-lg">edit</span>
                                            </a>
                                            <a href="<?php echo admin_url('sliders/toggle/' . $slider['id']); ?>" class="p-2 text-gray-600 dark:text-gray-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors min-h-[36px] min-w-[36px] flex items-center justify-center" title="<?php echo $slider['status'] === 'active' ? 'Pasifleştir' : 'Aktifleştir'; ?>">
                                                <span class="material-symbols-outlined text-lg"><?php echo $slider['status'] === 'active' ? 'visibility_off' : 'visibility'; ?></span>
                                            </a>
                                            <a href="<?php echo admin_url('sliders/delete/' . $slider['id']); ?>" class="p-2 text-gray-600 dark:text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors min-h-[36px] min-w-[36px] flex items-center justify-center" onclick="return confirm('Bu slider\'ı silmek istediğinizden emin misiniz?');" title="Sil">
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
