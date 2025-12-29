<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo isset($title) ? esc_html($title) : 'Yeni Menü Oluştur'; ?></title>
    
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
            $currentPage = 'menus';
            include __DIR__ . '/../snippets/sidebar.php'; 
            ?>

            <!-- Main Content -->
            <main class="main-content-with-sidebar flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b]">
                <div class="layout-content-container flex flex-col w-full mx-auto max-w-3xl">
                    <!-- Breadcrumb -->
                    <nav class="flex items-center gap-2 text-xs sm:text-sm mb-4 sm:mb-6 overflow-x-auto scrollbar-hide">
                        <a href="<?php echo admin_url('menus'); ?>" class="text-gray-500 dark:text-gray-400 hover:text-primary transition-colors whitespace-nowrap">Menüler</a>
                        <span class="material-symbols-outlined text-gray-400 text-sm sm:text-base flex-shrink-0">chevron_right</span>
                        <span class="text-gray-900 dark:text-white whitespace-nowrap">Yeni Menü</span>
                    </nav>
                    
                    <!-- PageHeading -->
                    <header class="flex flex-col gap-2 mb-6">
                        <p class="text-gray-900 dark:text-white text-2xl sm:text-3xl font-bold tracking-tight">Yeni Menü Oluştur</p>
                        <p class="text-gray-500 dark:text-gray-400 text-sm sm:text-base font-normal leading-normal">Siteniz için yeni bir navigasyon menüsü oluşturun.</p>
                    </header>

                    <!-- Form -->
                    <form action="<?php echo admin_url('menus/store'); ?>" method="POST" class="space-y-6">
                        <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark p-4 sm:p-6 space-y-4 sm:space-y-6">
                            <!-- Menü Adı -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Menü Adı <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" required
                                    class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors text-base"
                                    placeholder="Örn: Ana Menü">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Bu isim sadece yönetim panelinde görünür.</p>
                            </div>

                            <!-- Konum -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Konum <span class="text-red-500">*</span>
                                </label>
                                <select name="location" required
                                    class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors text-base">
                                    <option value="">Konum Seçin</option>
                                    <?php foreach ($locations as $key => $label): ?>
                                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Menünün sitede görüneceği konum.</p>
                            </div>

                            <!-- Açıklama -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Açıklama
                                </label>
                                <textarea name="description" rows="3"
                                    class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors resize-none text-base"
                                    placeholder="Menü hakkında kısa bir açıklama..."></textarea>
                            </div>

                            <!-- Durum -->
                            <div>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="status" value="active" checked
                                        class="w-5 h-5 rounded border-gray-300 dark:border-white/10 text-primary focus:ring-primary focus:ring-offset-0 dark:bg-white/5">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Menü Aktif</span>
                                </label>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 ml-8">Aktif olmayan menüler sitede gösterilmez.</p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
                            <a href="<?php echo admin_url('menus'); ?>" class="px-6 py-3 rounded-lg border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors font-medium min-h-[44px] flex items-center justify-center order-2 sm:order-1">
                                İptal
                            </a>
                            <button type="submit" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium min-h-[44px] order-1 sm:order-2">
                                Menü Oluştur
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>

