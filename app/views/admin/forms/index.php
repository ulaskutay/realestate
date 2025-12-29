<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo isset($title) ? esc_html($title) : 'Form Yönetimi'; ?></title>
    
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
            $currentPage = 'forms';
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
                            <p class="text-gray-900 dark:text-white text-2xl sm:text-3xl font-bold tracking-tight">Form Yönetimi</p>
                            <p class="text-gray-500 dark:text-gray-400 text-sm sm:text-base font-normal leading-normal">Formlarınızı oluşturun, düzenleyin ve gönderimleri yönetin.</p>
                        </div>
                        <a href="<?php echo admin_url('forms/create'); ?>" class="flex items-center gap-2 px-3 sm:px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors min-h-[44px]">
                            <span class="material-symbols-outlined text-lg sm:text-xl">add</span>
                            <span class="text-sm font-medium">Yeni Form</span>
                        </a>
                    </header>

                    <!-- Success/Error Message -->
                    <?php if (isset($message) && $message): ?>
                        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200'; ?>">
                            <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Forms List -->
                    <section class="rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark overflow-hidden">
                        <?php if (empty($forms)): ?>
                            <div class="p-12 text-center">
                                <span class="material-symbols-outlined text-gray-400 dark:text-gray-600 text-6xl mb-4">dynamic_form</span>
                                <p class="text-gray-500 dark:text-gray-400 text-lg mb-2">Henüz form oluşturulmamış</p>
                                <p class="text-gray-400 dark:text-gray-500 text-sm mb-6">Yeni bir form oluşturmak için yukarıdaki butona tıklayın.</p>
                                <a href="<?php echo admin_url('forms/create'); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                    <span class="material-symbols-outlined">add</span>
                                    <span>İlk Formu Oluştur</span>
                                </a>
                            </div>
                        <?php else: ?>
                            <!-- Desktop Table View -->
                            <div class="hidden md:block overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Form Adı</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Alanlar</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Gönderimler</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Durum</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Oluşturulma</th>
                                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                        <?php foreach ($forms as $form): ?>
                                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                                <td class="px-6 py-4">
                                                    <div>
                                                        <p class="text-gray-900 dark:text-white font-medium"><?php echo esc_html($form['name']); ?></p>
                                                        <p class="text-gray-500 dark:text-gray-400 text-xs mt-1">Kısa kod: [form slug="<?php echo esc_attr($form['slug']); ?>"]</p>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <p class="text-gray-600 dark:text-gray-400 text-sm"><?php echo esc_html($form['field_count'] ?? 0); ?> alan</p>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-gray-600 dark:text-gray-400 text-sm"><?php echo esc_html($form['total_submissions'] ?? 0); ?> toplam</span>
                                                        <?php if (($form['new_submissions'] ?? 0) > 0): ?>
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                                                <?php echo esc_html($form['new_submissions']); ?> yeni
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <?php if ($form['status'] === 'active'): ?>
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
                                                    <p class="text-gray-600 dark:text-gray-400 text-sm"><?php echo date('d.m.Y H:i', strtotime($form['created_at'])); ?></p>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <a href="<?php echo admin_url('forms/submissions/' . $form['id']); ?>" class="p-2 text-gray-600 dark:text-gray-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Gönderimler">
                                                            <span class="material-symbols-outlined text-xl">inbox</span>
                                                        </a>
                                                        <a href="<?php echo admin_url('forms/preview/' . $form['id']); ?>" target="_blank" class="p-2 text-gray-600 dark:text-gray-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Önizleme">
                                                            <span class="material-symbols-outlined text-xl">visibility</span>
                                                        </a>
                                                        <a href="<?php echo admin_url('forms/edit/' . $form['id']); ?>" class="p-2 text-gray-600 dark:text-gray-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Düzenle">
                                                            <span class="material-symbols-outlined text-xl">edit</span>
                                                        </a>
                                                        <a href="<?php echo admin_url('forms/toggle/' . $form['id']); ?>" class="p-2 text-gray-600 dark:text-gray-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="<?php echo $form['status'] === 'active' ? 'Pasifleştir' : 'Aktifleştir'; ?>">
                                                            <span class="material-symbols-outlined text-xl"><?php echo $form['status'] === 'active' ? 'toggle_on' : 'toggle_off'; ?></span>
                                                        </a>
                                                        <a href="<?php echo admin_url('forms/delete/' . $form['id']); ?>" class="p-2 text-gray-600 dark:text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" onclick="return confirm('Bu formu silmek istediğinizden emin misiniz? Tüm alanlar ve gönderimler de silinecektir!');" title="Sil">
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
                            <div class="md:hidden space-y-4 p-4">
                                <?php foreach ($forms as $form): ?>
                                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-white/10 p-4">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-gray-900 dark:text-white font-medium mb-1 line-clamp-2"><?php echo esc_html($form['name']); ?></h3>
                                            <p class="text-gray-500 text-xs truncate">[form slug="<?php echo esc_attr($form['slug']); ?>"]</p>
                                        </div>
                                        <?php if ($form['status'] === 'active'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 flex-shrink-0 ml-2">Aktif</span>
                                        <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-300 flex-shrink-0 ml-2">Pasif</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="flex flex-wrap items-center gap-3 mb-3 text-xs text-gray-600 dark:text-gray-400">
                                        <span><?php echo esc_html($form['field_count'] ?? 0); ?> alan</span>
                                        <span>•</span>
                                        <span><?php echo esc_html($form['submission_count'] ?? 0); ?> gönderim</span>
                                        <?php if (($form['new_submissions'] ?? 0) > 0): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                            <?php echo esc_html($form['new_submissions']); ?> yeni
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                                        <?php echo date('d.m.Y H:i', strtotime($form['created_at'])); ?>
                                    </div>
                                    
                                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-200 dark:border-white/10">
                                        <a href="<?php echo admin_url('forms/submissions/' . $form['id']); ?>" class="flex-1 flex items-center justify-center gap-1 px-3 py-2 text-sm text-primary hover:bg-primary/10 rounded-lg transition-colors">
                                            <span class="material-symbols-outlined text-lg">inbox</span>
                                            <span>Gönderimler</span>
                                        </a>
                                        <a href="<?php echo admin_url('forms/preview/' . $form['id']); ?>" target="_blank" class="px-3 py-2 text-sm text-gray-600 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Önizleme">
                                            <span class="material-symbols-outlined text-lg">visibility</span>
                                        </a>
                                        <a href="<?php echo admin_url('forms/edit/' . $form['id']); ?>" class="px-3 py-2 text-sm text-gray-600 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Düzenle">
                                            <span class="material-symbols-outlined text-lg">edit</span>
                                        </a>
                                        <a href="<?php echo admin_url('forms/toggle/' . $form['id']); ?>" class="px-3 py-2 text-sm text-gray-600 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="<?php echo $form['status'] === 'active' ? 'Pasifleştir' : 'Aktifleştir'; ?>">
                                            <span class="material-symbols-outlined text-lg"><?php echo $form['status'] === 'active' ? 'toggle_on' : 'toggle_off'; ?></span>
                                        </a>
                                        <a href="<?php echo admin_url('forms/delete/' . $form['id']); ?>" class="px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" onclick="return confirm('Bu formu silmek istediğinizden emin misiniz? Tüm alanlar ve gönderimler de silinecektir!');" title="Sil">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </a>
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

