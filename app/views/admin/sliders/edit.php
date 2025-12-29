<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo isset($title) ? esc_html($title) : 'Slider Düzenle'; ?></title>
    
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

            <!-- Main Content -->
            <main class="main-content-with-sidebar flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b]">
                <div class="layout-content-container flex flex-col w-full mx-auto max-w-7xl">
                    <!-- PageHeading -->
                    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <div class="flex flex-col gap-2">
                            <p class="text-gray-900 dark:text-white text-2xl sm:text-3xl font-bold tracking-tight">Slider Düzenle</p>
                            <p class="text-gray-500 dark:text-gray-400 text-sm sm:text-base font-normal leading-normal line-clamp-2"><?php echo esc_html($slider['name'] ?? ''); ?></p>
                        </div>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 w-full sm:w-auto">
                            <button 
                                type="button" 
                                onclick="openSliderPreview()" 
                                class="flex items-center justify-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors min-h-[44px]"
                                <?php echo empty($slider['items']) ? 'disabled title="Önizleme için en az bir slide eklemelisiniz"' : ''; ?>
                            >
                                <span class="material-symbols-outlined text-lg sm:text-xl">visibility</span>
                                <span class="text-sm font-medium">Önizleme</span>
                            </button>
                            <a href="<?php echo admin_url('sliders'); ?>" class="flex items-center justify-center gap-2 px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors min-h-[44px]">
                                <span class="material-symbols-outlined text-lg sm:text-xl">arrow_back</span>
                                <span class="text-sm font-medium">Geri Dön</span>
                            </a>
                        </div>
                    </header>

                    <!-- Success/Error Message -->
                    <?php if (isset($message) && $message): ?>
                        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200'; ?>">
                            <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Slider Settings Form -->
                    <section class="mb-6 sm:mb-8 rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark">
                        <form method="POST" action="<?php echo admin_url('sliders/update/' . $slider['id']); ?>" class="space-y-0">
                            
                            <!-- Temel Ayarlar -->
                            <div class="accordion-item border-b border-gray-200 dark:border-white/10">
                                <button type="button" class="accordion-header w-full flex items-center justify-between p-4 sm:p-6 text-left hover:bg-gray-50 dark:hover:bg-white/5 transition-colors min-h-[44px]" onclick="toggleAccordion(this)">
                                    <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                                        <span class="material-symbols-outlined text-primary text-lg sm:text-xl flex-shrink-0">settings</span>
                                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white truncate">Temel Ayarlar</h3>
                                    </div>
                                    <span class="material-symbols-outlined accordion-icon text-gray-400 dark:text-gray-500 transition-transform text-lg sm:text-xl flex-shrink-0">expand_more</span>
                                </button>
                                <div class="accordion-content hidden p-4 sm:p-6 pt-0 space-y-4">
                                    <!-- Slider Adı -->
                                    <div class="flex flex-col gap-2">
                                        <label for="name" class="text-gray-700 dark:text-gray-300 text-sm font-medium">Slider Adı <span class="text-red-500">*</span></label>
                                        <input 
                                            type="text" 
                                            id="name" 
                                            name="name" 
                                            value="<?php echo esc_attr($slider['name'] ?? ''); ?>"
                                            required
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"
                                        />
                                    </div>

                                    <!-- Animasyon Tipi -->
                                    <div class="flex flex-col gap-2">
                                        <label for="animation_type" class="text-gray-700 dark:text-gray-300 text-sm font-medium">Animasyon Tipi</label>
                                        <select 
                                            id="animation_type" 
                                            name="animation_type"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"
                                        >
                                            <option value="fade" <?php echo ($slider['animation_type'] ?? 'fade') === 'fade' ? 'selected' : ''; ?>>Fade (Yumuşak Geçiş)</option>
                                            <option value="slide" <?php echo ($slider['animation_type'] ?? '') === 'slide' ? 'selected' : ''; ?>>Slide (Yan Kaydırma)</option>
                                            <option value="zoom" <?php echo ($slider['animation_type'] ?? '') === 'zoom' ? 'selected' : ''; ?>>Zoom (Yakınlaştırma)</option>
                                            <option value="cube" <?php echo ($slider['animation_type'] ?? '') === 'cube' ? 'selected' : ''; ?>>Cube (3D Küp)</option>
                                            <option value="flip" <?php echo ($slider['animation_type'] ?? '') === 'flip' ? 'selected' : ''; ?>>Flip (3D Döndürme)</option>
                                            <option value="coverflow" <?php echo ($slider['animation_type'] ?? '') === 'coverflow' ? 'selected' : ''; ?>>Coverflow (iTunes Tarzı)</option>
                                            <option value="cards" <?php echo ($slider['animation_type'] ?? '') === 'cards' ? 'selected' : ''; ?>>Cards (Kart Flip)</option>
                                        </select>
                                    </div>

                                    <!-- Genişlik ve Yükseklik -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div class="flex flex-col gap-2">
                                            <label for="width" class="text-gray-700 dark:text-gray-300 text-sm font-medium">Slider Genişliği</label>
                                            <input 
                                                type="text" 
                                                id="width" 
                                                name="width" 
                                                value="<?php echo esc_attr($slider['width'] ?? '100%'); ?>"
                                                placeholder="100%, 1200px, 90vw"
                                                class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"
                                            />
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <label for="height" class="text-gray-700 dark:text-gray-300 text-sm font-medium">Slider Yüksekliği</label>
                                            <input 
                                                type="text" 
                                                id="height" 
                                                name="height" 
                                                value="<?php echo esc_attr($slider['height'] ?? '500px'); ?>"
                                                placeholder="500px veya 50vh"
                                                class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Oynatma Ayarları -->
                            <div class="accordion-item border-b border-gray-200 dark:border-white/10">
                                <button type="button" class="accordion-header w-full flex items-center justify-between p-4 sm:p-6 text-left hover:bg-gray-50 dark:hover:bg-white/5 transition-colors min-h-[44px]" onclick="toggleAccordion(this)">
                                    <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                                        <span class="material-symbols-outlined text-primary text-lg sm:text-xl flex-shrink-0">play_circle</span>
                                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white truncate">Oynatma Ayarları</h3>
                                    </div>
                                    <span class="material-symbols-outlined accordion-icon text-gray-400 dark:text-gray-500 transition-transform text-lg sm:text-xl flex-shrink-0">expand_more</span>
                                </button>
                                <div class="accordion-content hidden p-4 sm:p-6 pt-0 space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div class="flex items-center gap-3">
                                            <input 
                                                type="checkbox" 
                                                id="autoplay" 
                                                name="autoplay" 
                                                value="1"
                                                <?php echo ($slider['autoplay'] ?? 0) ? 'checked' : ''; ?>
                                                class="w-4 h-4 text-primary bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary focus:ring-2"
                                            />
                                            <label for="autoplay" class="text-gray-700 dark:text-gray-300 text-sm font-medium">Otomatik Oynatma</label>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <input 
                                                type="checkbox" 
                                                id="loop" 
                                                name="loop" 
                                                value="1"
                                                <?php echo ($slider['loop'] ?? 0) ? 'checked' : ''; ?>
                                                class="w-4 h-4 text-primary bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary focus:ring-2"
                                            />
                                            <label for="loop" class="text-gray-700 dark:text-gray-300 text-sm font-medium">Döngü (Sonsuz)</label>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label for="autoplay_delay" class="text-gray-700 dark:text-gray-300 text-sm font-medium">Otomatik Oynatma Gecikmesi (ms)</label>
                                        <input 
                                            type="number" 
                                            id="autoplay_delay" 
                                            name="autoplay_delay" 
                                            value="<?php echo esc_attr($slider['autoplay_delay'] ?? 5000); ?>"
                                            min="1000"
                                            step="500"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Navigasyon Ayarları -->
                            <div class="accordion-item border-b border-gray-200 dark:border-white/10">
                                <button type="button" class="accordion-header w-full flex items-center justify-between p-4 sm:p-6 text-left hover:bg-gray-50 dark:hover:bg-white/5 transition-colors min-h-[44px]" onclick="toggleAccordion(this)">
                                    <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                                        <span class="material-symbols-outlined text-primary text-lg sm:text-xl flex-shrink-0">navigation</span>
                                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white truncate">Navigasyon Ayarları</h3>
                                    </div>
                                    <span class="material-symbols-outlined accordion-icon text-gray-400 dark:text-gray-500 transition-transform text-lg sm:text-xl flex-shrink-0">expand_more</span>
                                </button>
                                <div class="accordion-content hidden p-4 sm:p-6 pt-0 space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div class="flex items-center gap-3">
                                            <input 
                                                type="checkbox" 
                                                id="navigation" 
                                                name="navigation" 
                                                value="1"
                                                <?php echo ($slider['navigation'] ?? 0) ? 'checked' : ''; ?>
                                                class="w-4 h-4 text-primary bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary focus:ring-2"
                                            />
                                            <label for="navigation" class="text-gray-700 dark:text-gray-300 text-sm font-medium">Navigasyon Butonları</label>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <input 
                                                type="checkbox" 
                                                id="pagination" 
                                                name="pagination" 
                                                value="1"
                                                <?php echo ($slider['pagination'] ?? 0) ? 'checked' : ''; ?>
                                                class="w-4 h-4 text-primary bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary focus:ring-2"
                                            />
                                            <label for="pagination" class="text-gray-700 dark:text-gray-300 text-sm font-medium">Pagination Dots</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Navigation Buton Stilleri -->
                            <div class="accordion-item border-b border-gray-200 dark:border-white/10">
                                <button type="button" class="accordion-header w-full flex items-center justify-between p-4 sm:p-6 text-left hover:bg-gray-50 dark:hover:bg-white/5 transition-colors min-h-[44px]" onclick="toggleAccordion(this)">
                                    <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                                        <span class="material-symbols-outlined text-primary text-lg sm:text-xl flex-shrink-0">palette</span>
                                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white truncate">Navigation Buton Stilleri</h3>
                                    </div>
                                    <span class="material-symbols-outlined accordion-icon text-gray-400 dark:text-gray-500 transition-transform text-lg sm:text-xl flex-shrink-0">expand_more</span>
                                </button>
                                <div class="accordion-content hidden p-4 sm:p-6 pt-0">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <!-- Normal Durum -->
                                        <div class="space-y-3 p-4 bg-gray-50 dark:bg-white/5 rounded-lg">
                                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Normal Durum</h4>
                                            <div class="flex flex-col gap-2">
                                                <label for="nav_button_color" class="text-xs text-gray-600 dark:text-gray-400">İkon Rengi</label>
                                                <input type="color" id="nav_button_color" name="nav_button_color" value="<?php echo esc_attr($slider['nav_button_color'] ?? '#137fec'); ?>" class="w-full h-10 rounded-lg border border-gray-200 dark:border-white/10 cursor-pointer"/>
                                            </div>
                                            <div class="flex flex-col gap-2">
                                                <label for="nav_button_bg_color" class="text-xs text-gray-600 dark:text-gray-400">Arka Plan</label>
                                                <div class="flex gap-2">
                                                    <input type="color" id="nav_button_bg_color" name="nav_button_bg_color" value="<?php echo esc_attr($slider['nav_button_bg_color'] ?? '#ffffff'); ?>" class="flex-1 h-10 rounded-lg border border-gray-200 dark:border-white/10 cursor-pointer"/>
                                                    <input type="range" id="nav_button_bg_opacity" name="nav_button_bg_opacity" min="0" max="1" step="0.1" value="<?php echo esc_attr($slider['nav_button_bg_opacity'] ?? 0.90); ?>" class="flex-1" oninput="document.getElementById('bg-opacity-value').textContent = Math.round(this.value * 100) + '%'"/>
                                                    <span id="bg-opacity-value" class="text-xs text-gray-500 dark:text-gray-400 w-12 text-center"><?php echo round(($slider['nav_button_bg_opacity'] ?? 0.90) * 100); ?>%</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Hover Durum -->
                                        <div class="space-y-3 p-4 bg-gray-50 dark:bg-white/5 rounded-lg">
                                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Hover Durum</h4>
                                            <div class="flex flex-col gap-2">
                                                <label for="nav_button_hover_color" class="text-xs text-gray-600 dark:text-gray-400">İkon Rengi</label>
                                                <input type="color" id="nav_button_hover_color" name="nav_button_hover_color" value="<?php echo esc_attr($slider['nav_button_hover_color'] ?? '#137fec'); ?>" class="w-full h-10 rounded-lg border border-gray-200 dark:border-white/10 cursor-pointer"/>
                                            </div>
                                            <div class="flex flex-col gap-2">
                                                <label for="nav_button_hover_bg_color" class="text-xs text-gray-600 dark:text-gray-400">Arka Plan</label>
                                                <div class="flex gap-2">
                                                    <input type="color" id="nav_button_hover_bg_color" name="nav_button_hover_bg_color" value="<?php echo esc_attr($slider['nav_button_hover_bg_color'] ?? '#ffffff'); ?>" class="flex-1 h-10 rounded-lg border border-gray-200 dark:border-white/10 cursor-pointer"/>
                                                    <input type="range" id="nav_button_hover_bg_opacity" name="nav_button_hover_bg_opacity" min="0" max="1" step="0.1" value="<?php echo esc_attr($slider['nav_button_hover_bg_opacity'] ?? 0.90); ?>" class="flex-1" oninput="document.getElementById('hover-bg-opacity-value').textContent = Math.round(this.value * 100) + '%'"/>
                                                    <span id="hover-bg-opacity-value" class="text-xs text-gray-500 dark:text-gray-400 w-12 text-center"><?php echo round(($slider['nav_button_hover_bg_opacity'] ?? 0.90) * 100); ?>%</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Boyut ve Pozisyon -->
                                        <div class="space-y-3 p-4 bg-gray-50 dark:bg-white/5 rounded-lg">
                                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Boyut ve Pozisyon</h4>
                                            <div class="grid grid-cols-2 gap-3">
                                                <div class="flex flex-col gap-1">
                                                    <label for="nav_button_size" class="text-xs text-gray-600 dark:text-gray-400">Buton</label>
                                                    <input type="text" id="nav_button_size" name="nav_button_size" value="<?php echo esc_attr($slider['nav_button_size'] ?? '50px'); ?>" placeholder="50px" class="w-full px-3 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"/>
                                                </div>
                                                <div class="flex flex-col gap-1">
                                                    <label for="nav_button_icon_size" class="text-xs text-gray-600 dark:text-gray-400">İkon</label>
                                                    <input type="text" id="nav_button_icon_size" name="nav_button_icon_size" value="<?php echo esc_attr($slider['nav_button_icon_size'] ?? '32px'); ?>" placeholder="32px" class="w-full px-3 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"/>
                                                </div>
                                            </div>
                                            <div class="flex flex-col gap-1">
                                                <label for="nav_button_position" class="text-xs text-gray-600 dark:text-gray-400">Pozisyon</label>
                                                <select id="nav_button_position" name="nav_button_position" class="w-full px-3 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary">
                                                    <option value="inside" <?php echo ($slider['nav_button_position'] ?? 'inside') === 'inside' ? 'selected' : ''; ?>>İçeride</option>
                                                    <option value="outside" <?php echo ($slider['nav_button_position'] ?? '') === 'outside' ? 'selected' : ''; ?>>Dışarıda</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Görünüm -->
                                        <div class="space-y-3 p-4 bg-gray-50 dark:bg-white/5 rounded-lg">
                                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Görünüm</h4>
                                            <div class="flex flex-col gap-2">
                                                <label for="nav_button_opacity" class="text-xs text-gray-600 dark:text-gray-400">Opaklık</label>
                                                <input type="range" id="nav_button_opacity" name="nav_button_opacity" min="0" max="1" step="0.1" value="<?php echo esc_attr($slider['nav_button_opacity'] ?? 0.90); ?>" class="w-full" oninput="document.getElementById('opacity-value').textContent = Math.round(this.value * 100) + '%'"/>
                                                <span id="opacity-value" class="text-xs text-gray-500 dark:text-gray-400"><?php echo round(($slider['nav_button_opacity'] ?? 0.90) * 100); ?>%</span>
                                            </div>
                                            <div class="flex flex-col gap-1">
                                                <label for="nav_button_border_radius" class="text-xs text-gray-600 dark:text-gray-400">Köşe Yuvarlama</label>
                                                <input type="text" id="nav_button_border_radius" name="nav_button_border_radius" value="<?php echo esc_attr($slider['nav_button_border_radius'] ?? '50%'); ?>" placeholder="50%" class="w-full px-3 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Durum -->
                            <div class="p-4 sm:p-6 flex flex-wrap items-center gap-3 sm:gap-4 border-b border-gray-200 dark:border-white/10">
                                <input 
                                    type="checkbox" 
                                    id="status" 
                                    name="status" 
                                    value="active"
                                    <?php echo ($slider['status'] ?? 'inactive') === 'active' ? 'checked' : ''; ?>
                                    class="w-4 h-4 text-primary bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary focus:ring-2"
                                />
                                <label for="status" class="text-gray-700 dark:text-gray-300 text-sm font-medium">Aktif</label>
                                <p class="text-gray-500 dark:text-gray-400 text-xs">(Sadece bir slider aktif olabilir)</p>
                            </div>

                            <!-- Butonlar -->
                            <div class="flex justify-end p-4 sm:p-6">
                                <button 
                                    type="submit" 
                                    class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors font-medium min-h-[44px] w-full sm:w-auto"
                                >
                                    Ayarları Kaydet
                                </button>
                            </div>
                        </form>
                    </section>

                    <style>
                        .accordion-item .accordion-content {
                            max-height: 0;
                            overflow: hidden;
                            transition: max-height 0.3s ease-out;
                        }
                        .accordion-item.active .accordion-content {
                            max-height: 2000px;
                            transition: max-height 0.5s ease-in;
                        }
                        .accordion-item.active .accordion-icon {
                            transform: rotate(180deg);
                        }
                    </style>

                    <script>
                        // Sayfa yüklendiğinde ilk accordion'u aç
                        document.addEventListener('DOMContentLoaded', function() {
                            const firstAccordion = document.querySelector('.accordion-item');
                            if (firstAccordion) {
                                firstAccordion.classList.add('active');
                                const content = firstAccordion.querySelector('.accordion-content');
                                if (content) {
                                    content.classList.remove('hidden');
                                }
                            }
                        });

                        function toggleAccordion(button) {
                            const item = button.closest('.accordion-item');
                            const isActive = item.classList.contains('active');
                            
                            // Tüm accordion'ları kapat
                            document.querySelectorAll('.accordion-item').forEach(acc => {
                                acc.classList.remove('active');
                                const content = acc.querySelector('.accordion-content');
                                if (content) {
                                    content.classList.add('hidden');
                                }
                            });
                            
                            // Tıklanan accordion'u aç
                            if (!isActive) {
                                item.classList.add('active');
                                const content = item.querySelector('.accordion-content');
                                if (content) {
                                    content.classList.remove('hidden');
                                }
                            }
                        }
                    </script>

                    <!-- Slider Items Section -->
                    <section class="rounded-xl border border-gray-200 dark:border-white/10 p-4 sm:p-6 bg-background-light dark:bg-background-dark">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                            <h2 class="text-gray-900 dark:text-white text-lg sm:text-xl font-semibold">Slider İçerikleri</h2>
                            <button onclick="showAddItemModal()" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors min-h-[44px] w-full sm:w-auto justify-center sm:justify-start">
                                <span class="material-symbols-outlined text-lg sm:text-xl">add</span>
                                <span class="text-sm font-medium">Yeni İçerik Ekle</span>
                            </button>
                        </div>

                        <div id="items-list" class="space-y-4">
                            <?php if (!empty($slider['items'])): ?>
                                <?php foreach ($slider['items'] as $item): ?>
                                    <div class="item-card p-4 border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-background-dark" data-item-id="<?php echo $item['id']; ?>">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-3 mb-2">
                                                    <?php if ($item['type'] === 'image' && !empty($item['media_url'])): ?>
                                                        <img src="<?php echo esc_url($item['media_url']); ?>" alt="Preview" class="w-20 h-20 object-cover rounded-lg">
                                                    <?php endif; ?>
                                                    <div>
                                                        <h3 class="text-gray-900 dark:text-white font-medium"><?php echo esc_html($item['title'] ?? 'Başlıksız'); ?></h3>
                                                        <p class="text-gray-500 dark:text-gray-400 text-sm"><?php echo esc_html(ucfirst($item['type'] ?? 'image')); ?> - Sıra: <?php echo $item['order'] ?? 0; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button onclick="editItem(<?php echo $item['id']; ?>)" class="p-2 text-gray-600 dark:text-gray-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors">
                                                    <span class="material-symbols-outlined">edit</span>
                                                </button>
                                                <button onclick="deleteItem(<?php echo $item['id']; ?>)" class="p-2 text-gray-600 dark:text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                                    <span class="material-symbols-outlined">delete</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-12">
                                    <span class="material-symbols-outlined text-gray-400 dark:text-gray-600 text-6xl mb-4">image</span>
                                    <p class="text-gray-500 dark:text-gray-400 text-lg mb-2">Henüz içerik eklenmemiş</p>
                                    <p class="text-gray-400 dark:text-gray-500 text-sm mb-6">Yukarıdaki butona tıklayarak içerik ekleyebilirsiniz.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>

    <!-- File Manager Modal -->
    <div id="file-manager-modal" class="hidden fixed inset-0 bg-black/70 z-[99999] flex items-center justify-center p-4" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0;">
        <div class="bg-white dark:bg-background-dark rounded-xl p-6 max-w-4xl w-full max-h-[90vh] flex flex-col relative z-[99999]">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Dosya Yöneticisi</h3>
                <button onclick="closeFileManager()" class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <!-- Upload Section -->
            <div class="mb-6 p-4 border border-gray-200 dark:border-white/10 rounded-lg bg-gray-50 dark:bg-background-dark/50">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Yeni Dosya Yükle</label>
                <div class="flex gap-2">
                    <input 
                        type="file" 
                        id="file-upload-input"
                        accept="image/*,video/*"
                        class="flex-1 px-4 py-2 border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20"
                    />
                    <button 
                        type="button"
                        onclick="uploadFile()"
                        id="upload-btn"
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium"
                    >
                        Yükle
                    </button>
                </div>
                <div id="upload-status" class="hidden mt-2 p-2 rounded-lg text-sm"></div>
            </div>
            
            <!-- Files Grid -->
            <div class="flex-1 overflow-y-auto">
                <div id="files-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div class="col-span-full text-center py-8 text-gray-500 dark:text-gray-400">
                        <span class="material-symbols-outlined text-4xl mb-2 block">hourglass_empty</span>
                        <p>Dosyalar yükleniyor...</p>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4 mt-4 border-t border-gray-200 dark:border-white/10">
                <button 
                    type="button" 
                    onclick="closeFileManager()" 
                    class="px-6 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors font-medium"
                >
                    İptal
                </button>
            </div>
        </div>
    </div>

    <!-- Add Item Modal -->
    <div id="add-item-modal" class="hidden fixed inset-0 bg-black/50 z-[9999] flex items-center justify-center p-4" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0;">
        <div class="bg-white dark:bg-background-dark rounded-xl p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto relative z-[9999]">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Yeni Slide Ekle</h3>
                <button onclick="hideAddItemModal()" class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <form id="add-item-form" class="space-y-6">
                <input type="hidden" name="slider_id" value="<?php echo $slider['id']; ?>">
                
                <!-- Slide Tipi -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Slide Tipi</label>
                    <select name="type" id="item-type-select" required class="w-full px-4 py-2 border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="image">Resim</option>
                        <option value="video">Video</option>
                        <option value="html">HTML</option>
                    </select>
                </div>
                
                <!-- Media URL -->
                <div id="media-url-section">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Medya URL</label>
                    <div class="flex gap-2">
                        <input 
                            type="text" 
                            name="media_url" 
                            id="item-media-url"
                            placeholder="https://example.com/image.jpg veya /uploads/image.jpg"
                            class="flex-1 px-4 py-2 border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"
                        />
                        <button 
                            type="button"
                            onclick="openMediaPickerForSlider()"
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium flex items-center gap-2"
                        >
                            <span class="material-symbols-outlined text-xl">perm_media</span>
                            <span>Kütüphaneden Seç</span>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Resim veya video dosyasının URL'sini girin veya dosya yöneticisinden seçin</p>
                </div>
                
                <!-- Başlık -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Başlık</label>
                    <input 
                        type="text" 
                        name="title" 
                        placeholder="Slide başlığı"
                        class="w-full px-4 py-2 border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"
                    />
                </div>
                
                <!-- Alt Başlık -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Alt Başlık</label>
                    <input 
                        type="text" 
                        name="subtitle" 
                        placeholder="Slide alt başlığı"
                        class="w-full px-4 py-2 border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"
                    />
                </div>
                
                <!-- Açıklama -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Açıklama</label>
                    <textarea 
                        name="description" 
                        rows="3"
                        placeholder="Slide açıklaması"
                        class="w-full px-4 py-2 border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"
                    ></textarea>
                </div>
                
                <!-- Buton Ayarları -->
                <div class="border-t border-gray-200 dark:border-white/10 pt-4">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Buton Ayarları</h4>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buton Metni</label>
                            <input 
                                type="text" 
                                name="button_text" 
                                placeholder="Detaylar"
                                class="w-full px-4 py-2 border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buton Linki</label>
                            <input 
                                type="text" 
                                name="button_link" 
                                placeholder="https://example.com veya /sayfa"
                                class="w-full px-4 py-2 border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Link Hedefi</label>
                            <select name="button_target" class="w-full px-4 py-2 border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="_self">Aynı Sekmede</option>
                                <option value="_blank">Yeni Sekmede</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Overlay ve Pozisyon -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Overlay Opaklığı</label>
                        <input 
                            type="range" 
                            name="overlay_opacity" 
                            min="0" 
                            max="1" 
                            step="0.1"
                            value="0"
                            class="w-full"
                            oninput="document.getElementById('overlay-value').textContent = Math.round(this.value * 100) + '%'"
                        />
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Opaklık: <span id="overlay-value">0%</span></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Metin Pozisyonu</label>
                        <select name="text_position" class="w-full px-4 py-2 border border-gray-200 dark:border-white/10 rounded-lg bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="center">Ortada</option>
                            <option value="left">Solda</option>
                            <option value="right">Sağda</option>
                            <option value="top">Üstte</option>
                            <option value="bottom">Altta</option>
                        </select>
                    </div>
                </div>
                
                <!-- Durum -->
                <div class="flex items-center gap-4">
                    <input 
                        type="checkbox" 
                        id="item-status" 
                        name="status" 
                        value="active"
                        checked
                        class="w-4 h-4 text-primary bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary focus:ring-2"
                    />
                    <label for="item-status" class="text-sm font-medium text-gray-700 dark:text-gray-300">Aktif</label>
                </div>
                
                <!-- Butonlar -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-white/10">
                    <button 
                        type="button" 
                        onclick="hideAddItemModal()" 
                        class="px-6 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors font-medium"
                    >
                        İptal
                    </button>
                    <button 
                        type="submit" 
                        class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors font-medium"
                    >
                        Slide Ekle
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Slider Preview Modal -->
    <div id="slider-preview-modal" class="hidden fixed inset-0 bg-black/90 z-[99999] flex items-center justify-center" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0;">
        <div class="absolute top-4 right-4 z-50 flex items-center gap-3">
            <!-- Viewport Selector -->
            <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-lg p-1">
                <button type="button" onclick="setPreviewViewport('desktop')" class="viewport-btn active p-2 rounded-lg text-white hover:bg-white/20 transition-colors" data-viewport="desktop" title="Desktop">
                    <span class="material-symbols-outlined text-xl">desktop_windows</span>
                </button>
                <button type="button" onclick="setPreviewViewport('tablet')" class="viewport-btn p-2 rounded-lg text-white hover:bg-white/20 transition-colors" data-viewport="tablet" title="Tablet">
                    <span class="material-symbols-outlined text-xl">tablet</span>
                </button>
                <button type="button" onclick="setPreviewViewport('mobile')" class="viewport-btn p-2 rounded-lg text-white hover:bg-white/20 transition-colors" data-viewport="mobile" title="Mobil">
                    <span class="material-symbols-outlined text-xl">smartphone</span>
                </button>
            </div>
            
            <!-- Fullscreen Toggle -->
            <button type="button" onclick="window.open('<?php echo admin_url('sliders/preview/' . $slider['id']); ?>', '_blank')" class="p-2 bg-white/10 backdrop-blur-sm rounded-lg text-white hover:bg-white/20 transition-colors" title="Tam Sayfa Aç">
                <span class="material-symbols-outlined text-xl">open_in_new</span>
            </button>
            
            <!-- Close Button -->
            <button type="button" onclick="closeSliderPreview()" class="p-2 bg-white/10 backdrop-blur-sm rounded-lg text-white hover:bg-white/20 transition-colors" title="Kapat">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>
        
        <!-- Slider Boyut Bilgisi -->
        <div class="absolute top-4 left-4 z-50 px-3 py-1.5 bg-white/10 backdrop-blur-sm rounded-lg text-white text-sm font-medium">
            <span id="preview-size-info"><?php echo esc_html($slider['width'] ?? '100%'); ?> × <?php echo esc_html($slider['height'] ?? '500px'); ?></span>
        </div>
        
        <!-- Preview Container -->
        <div id="preview-container" class="preview-viewport-desktop transition-all duration-300 bg-white dark:bg-gray-900 overflow-hidden rounded-xl shadow-2xl" 
             style="width: <?php echo esc_attr($slider['width'] ?? '100%'); ?>; max-width: 95vw; height: <?php echo esc_attr($slider['height'] ?? '500px'); ?>; max-height: 85vh;">
            <div id="preview-content" class="w-full h-full relative">
                <!-- Slider buraya yüklenecek -->
            </div>
        </div>
    </div>
    
    <style>
        /* Preview Viewport Styles */
        .preview-viewport-desktop {
            width: <?php echo esc_attr($slider['width'] ?? '100%'); ?>;
            max-width: 95vw;
            height: <?php echo esc_attr($slider['height'] ?? '500px'); ?>;
            max-height: 85vh;
        }
        
        .preview-viewport-tablet {
            width: 768px;
            max-width: 95vw;
            height: <?php echo esc_attr($slider['height'] ?? '500px'); ?>;
            max-height: 85vh;
        }
        
        .preview-viewport-mobile {
            width: 375px;
            max-width: 95vw;
            height: <?php echo esc_attr($slider['height'] ?? '500px'); ?>;
            max-height: 85vh;
        }
        
        .viewport-btn.active {
            background: rgba(255, 255, 255, 0.3);
        }
        
        /* Preview Slider Styles */
        #preview-content .cms-slider {
            width: 100%;
            height: 100%;
            position: relative;
        }
        
        #preview-content .cms-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.6s ease;
        }
        
        #preview-content .cms-slide.active {
            opacity: 1;
            z-index: 1;
        }
        
        #preview-content .slider-item-img {
            width: 100%;
            height: 100%;
        }
        
        #preview-content .slider-item-img img,
        #preview-content .slider-item-img video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* Navigation Buttons */
        #preview-content .slider-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            width: <?php echo esc_attr($slider['nav_button_size'] ?? '50px'); ?>;
            height: <?php echo esc_attr($slider['nav_button_size'] ?? '50px'); ?>;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: <?php echo esc_attr($slider['nav_button_border_radius'] ?? '50%'); ?>;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            background: <?php 
                $bgColor = $slider['nav_button_bg_color'] ?? '#ffffff';
                $bgOpacity = $slider['nav_button_bg_opacity'] ?? 0.9;
                // Hex to RGBA
                $hex = str_replace('#', '', $bgColor);
                if (strlen($hex) == 3) {
                    $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
                }
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
                echo "rgba($r, $g, $b, $bgOpacity)";
            ?>;
            opacity: <?php echo esc_attr($slider['nav_button_opacity'] ?? 0.9); ?>;
        }
        
        #preview-content .slider-nav:hover {
            background: <?php 
                $bgColor = $slider['nav_button_hover_bg_color'] ?? '#ffffff';
                $bgOpacity = $slider['nav_button_hover_bg_opacity'] ?? 0.9;
                $hex = str_replace('#', '', $bgColor);
                if (strlen($hex) == 3) {
                    $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
                }
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
                echo "rgba($r, $g, $b, $bgOpacity)";
            ?>;
            opacity: 1;
        }
        
        #preview-content .slider-nav .material-symbols-outlined {
            font-size: <?php echo esc_attr($slider['nav_button_icon_size'] ?? '32px'); ?>;
            color: <?php echo esc_attr($slider['nav_button_color'] ?? '#137fec'); ?>;
        }
        
        #preview-content .slider-nav:hover .material-symbols-outlined {
            color: <?php echo esc_attr($slider['nav_button_hover_color'] ?? '#137fec'); ?>;
        }
        
        #preview-content .slider-prev {
            left: <?php echo ($slider['nav_button_position'] ?? 'inside') === 'outside' ? '-60px' : '20px'; ?>;
        }
        
        #preview-content .slider-next {
            right: <?php echo ($slider['nav_button_position'] ?? 'inside') === 'outside' ? '-60px' : '20px'; ?>;
        }
        
        /* Pagination Dots */
        #preview-content .slider-pagination {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            display: flex;
            gap: 8px;
        }
        
        #preview-content .slider-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }
        
        #preview-content .slider-dot.active {
            background: #fff;
            width: 24px;
            border-radius: 5px;
        }
        
        #preview-content .slider-dot:hover {
            background: rgba(255, 255, 255, 0.8);
        }
        
        /* Text Overlay */
        #preview-content .slide-content {
            position: absolute;
            z-index: 5;
            padding: 2rem;
            max-width: 600px;
        }
        
        #preview-content .slide-content.center {
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }
        
        #preview-content .slide-content.left {
            top: 50%;
            left: 5%;
            transform: translateY(-50%);
            text-align: left;
        }
        
        #preview-content .slide-content.right {
            top: 50%;
            right: 5%;
            transform: translateY(-50%);
            text-align: right;
        }
        
        #preview-content .slide-content.top {
            top: 10%;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
        }
        
        #preview-content .slide-content.bottom {
            bottom: 10%;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
        }
        
        #preview-content .slide-title {
            font-size: 3rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        #preview-content .slide-subtitle {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 1.5rem;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }
        
        #preview-content .slide-button {
            display: inline-block;
            padding: 0.75rem 2rem;
            background: #137fec;
            color: #fff;
            text-decoration: none;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        #preview-content .slide-button:hover {
            background: #0d6edb;
            transform: translateY(-2px);
        }
        
        /* Overlay */
        #preview-content .slide-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #000;
            z-index: 2;
        }
        
        @media (max-width: 768px) {
            #preview-content .slide-title {
                font-size: 1.75rem;
            }
            
            #preview-content .slide-subtitle {
                font-size: 1rem;
            }
        }
    </style>

    <!-- Media Picker JS -->
    <script src="<?php echo rtrim(site_url(), '/') . '/admin/js/media-picker.js'; ?>"></script>
    
    <script>
        const sliderId = <?php echo $slider['id']; ?>;
        const adminUrl = '<?php echo admin_url(''); ?>?page=';
        let selectedFileUrl = null;
        
        // Slider Preview Data
        const sliderData = <?php echo json_encode([
            'id' => $slider['id'],
            'name' => $slider['name'],
            'animation_type' => $slider['animation_type'] ?? 'fade',
            'autoplay' => $slider['autoplay'] ?? 0,
            'autoplay_delay' => $slider['autoplay_delay'] ?? 5000,
            'navigation' => $slider['navigation'] ?? 1,
            'pagination' => $slider['pagination'] ?? 1,
            'loop' => $slider['loop'] ?? 1,
            'width' => $slider['width'] ?? '100%',
            'height' => $slider['height'] ?? '500px',
            'items' => $slider['items'] ?? []
        ]); ?>;
        
        let previewSlider = null;
        let currentSlideIndex = 0;
        let autoplayInterval = null;
        
        // Slider Preview Functions
        function openSliderPreview() {
            const modal = document.getElementById('slider-preview-modal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Render slider
            renderPreviewSlider();
            
            // ESC tuşu ile kapatma
            document.addEventListener('keydown', handlePreviewEsc);
        }
        
        function closeSliderPreview() {
            const modal = document.getElementById('slider-preview-modal');
            modal.classList.add('hidden');
            document.body.style.overflow = '';
            
            // Autoplay'i durdur
            if (autoplayInterval) {
                clearInterval(autoplayInterval);
                autoplayInterval = null;
            }
            
            document.removeEventListener('keydown', handlePreviewEsc);
        }
        
        function handlePreviewEsc(e) {
            if (e.key === 'Escape') {
                closeSliderPreview();
            }
        }
        
        function setPreviewViewport(viewport) {
            const container = document.getElementById('preview-container');
            const sizeInfo = document.getElementById('preview-size-info');
            
            // Remove all viewport classes
            container.classList.remove('preview-viewport-desktop', 'preview-viewport-tablet', 'preview-viewport-mobile');
            
            // Add selected viewport class
            container.classList.add('preview-viewport-' + viewport);
            
            // Update size info
            const viewportSizes = {
                'desktop': sliderData.width + ' × ' + sliderData.height,
                'tablet': '768px × ' + sliderData.height,
                'mobile': '375px × ' + sliderData.height
            };
            sizeInfo.textContent = viewportSizes[viewport];
            
            // Update buttons
            document.querySelectorAll('.viewport-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.viewport === viewport) {
                    btn.classList.add('active');
                }
            });
        }
        
        function renderPreviewSlider() {
            const content = document.getElementById('preview-content');
            const items = sliderData.items;
            
            if (!items || items.length === 0) {
                content.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500"><p>Slider\'da henüz içerik bulunmuyor.</p></div>';
                return;
            }
            
            // Slider HTML'ini oluştur
            let html = `<div class="cms-slider" style="width: 100%; height: 100%; position: relative; overflow: hidden;">`;
            
            // Slides
            items.forEach((item, index) => {
                const isActive = index === 0 ? 'active' : '';
                html += `
                    <div class="cms-slide ${isActive}" data-index="${index}">
                        ${item.type === 'video' ? `
                            <div class="slider-item-img">
                                <video autoplay muted loop playsinline>
                                    <source src="${escapeHtml(item.media_url || '')}" type="video/mp4">
                                </video>
                            </div>
                        ` : `
                            <div class="slider-item-img">
                                <img src="${escapeHtml(item.media_url || '')}" alt="${escapeHtml(item.title || '')}">
                            </div>
                        `}
                        
                        ${parseFloat(item.overlay_opacity || 0) > 0 ? `
                            <div class="slide-overlay" style="opacity: ${item.overlay_opacity}"></div>
                        ` : ''}
                        
                        ${(item.title || item.subtitle || item.button_text) ? `
                            <div class="slide-content ${item.text_position || 'center'}">
                                ${item.title ? `<h2 class="slide-title">${escapeHtml(item.title)}</h2>` : ''}
                                ${item.subtitle ? `<p class="slide-subtitle">${escapeHtml(item.subtitle)}</p>` : ''}
                                ${item.button_text ? `
                                    <a href="${escapeHtml(item.button_link || '#')}" class="slide-button" target="${item.button_target || '_self'}">${escapeHtml(item.button_text)}</a>
                                ` : ''}
                            </div>
                        ` : ''}
                    </div>
                `;
            });
            
            // Navigation Buttons
            if (sliderData.navigation && items.length > 1) {
                html += `
                    <button type="button" class="slider-nav slider-prev" onclick="prevSlide()">
                        <span class="material-symbols-outlined">chevron_left</span>
                    </button>
                    <button type="button" class="slider-nav slider-next" onclick="nextSlide()">
                        <span class="material-symbols-outlined">chevron_right</span>
                    </button>
                `;
            }
            
            // Pagination Dots
            if (sliderData.pagination && items.length > 1) {
                html += `<div class="slider-pagination">`;
                items.forEach((_, index) => {
                    const isActive = index === 0 ? 'active' : '';
                    html += `<button type="button" class="slider-dot ${isActive}" onclick="goToSlide(${index})"></button>`;
                });
                html += `</div>`;
            }
            
            html += `</div>`;
            
            content.innerHTML = html;
            
            // Autoplay başlat
            if (sliderData.autoplay && items.length > 1) {
                startAutoplay();
            }
            
            // Touch/Swipe desteği
            setupTouchSupport();
        }
        
        function prevSlide() {
            const items = sliderData.items;
            if (sliderData.loop) {
                currentSlideIndex = (currentSlideIndex - 1 + items.length) % items.length;
            } else {
                currentSlideIndex = Math.max(0, currentSlideIndex - 1);
            }
            updateSlide();
            resetAutoplay();
        }
        
        function nextSlide() {
            const items = sliderData.items;
            if (sliderData.loop) {
                currentSlideIndex = (currentSlideIndex + 1) % items.length;
            } else {
                currentSlideIndex = Math.min(items.length - 1, currentSlideIndex + 1);
            }
            updateSlide();
            resetAutoplay();
        }
        
        function goToSlide(index) {
            if (index === currentSlideIndex) return;
            currentSlideIndex = index;
            updateSlide();
            resetAutoplay();
        }
        
        function updateSlide() {
            // Update slides
            const slides = document.querySelectorAll('#preview-content .cms-slide');
            slides.forEach((slide, index) => {
                if (index === currentSlideIndex) {
                    slide.classList.add('active');
                } else {
                    slide.classList.remove('active');
                }
            });
            
            // Update dots
            const dots = document.querySelectorAll('#preview-content .slider-dot');
            dots.forEach((dot, index) => {
                if (index === currentSlideIndex) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }
        
        function startAutoplay() {
            if (autoplayInterval) clearInterval(autoplayInterval);
            autoplayInterval = setInterval(() => {
                nextSlide();
            }, sliderData.autoplay_delay || 5000);
        }
        
        function resetAutoplay() {
            if (sliderData.autoplay) {
                startAutoplay();
            }
        }
        
        function setupTouchSupport() {
            const slider = document.querySelector('#preview-content .cms-slider');
            if (!slider) return;
            
            let touchStartX = 0;
            let touchEndX = 0;
            
            slider.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });
            
            slider.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                const diffX = touchStartX - touchEndX;
                if (Math.abs(diffX) > 50) {
                    if (diffX > 0) {
                        nextSlide();
                    } else {
                        prevSlide();
                    }
                }
            }, { passive: true });
        }
        
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Media Picker for Slider
        function openMediaPickerForSlider() {
            openMediaPicker({
                type: 'all',
                targetInput: 'item-media-url',
                onSelect: function(media) {
                    console.log('Selected media:', media);
                }
            });
        }

        // Legacy File Manager Functions (kept for compatibility)
        function openFileManager() {
            openMediaPickerForSlider();
        }

        function closeFileManager() {
            if (window.mediaPicker) {
                window.mediaPicker.close();
            }
        }

        async function loadFiles() {
            const grid = document.getElementById('files-grid');
            grid.innerHTML = '<div class="col-span-full text-center py-8 text-gray-500 dark:text-gray-400"><span class="material-symbols-outlined text-4xl mb-2 block">hourglass_empty</span><p>Dosyalar yükleniyor...</p></div>';
            
            try {
                const response = await fetch(adminUrl + 'sliders/file-manager-list');
                
                // Response'un text halini al (debug için)
                const responseText = await response.text();
                
                // JSON parse et
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON parse hatası:', parseError);
                    console.error('Sunucu yanıtı:', responseText);
                    grid.innerHTML = `<div class="col-span-full text-center py-8 text-red-500"><span class="material-symbols-outlined text-4xl mb-2 block">error</span><p>Dosyalar yüklenirken bir hata oluştu: JSON parse hatası</p><p class="text-xs mt-2">${parseError.message}</p></div>`;
                    return;
                }
                
                if (data.success && data.files.length > 0) {
                    grid.innerHTML = data.files.map(file => `
                        <div class="relative group cursor-pointer border-2 border-gray-200 dark:border-white/10 rounded-lg overflow-hidden hover:border-primary transition-colors" onclick="selectFile('${file.url}')">
                            ${file.type === 'image' 
                                ? `<img src="${file.url}" alt="${file.name}" class="w-full h-32 object-cover">`
                                : `<div class="w-full h-32 bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-4xl text-gray-400">play_circle</span>
                                   </div>`
                            }
                            <div class="p-2 bg-white dark:bg-background-dark">
                                <p class="text-xs text-gray-600 dark:text-gray-400 truncate" title="${file.name}">${file.name}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">${formatFileSize(file.size)}</p>
                            </div>
                            <button onclick="event.stopPropagation(); deleteFile('${file.url}')" class="absolute top-2 right-2 p-1 bg-red-500 text-white rounded opacity-0 group-hover:opacity-100 transition-opacity">
                                <span class="material-symbols-outlined text-sm">delete</span>
                            </button>
                        </div>
                    `).join('');
                } else {
                    grid.innerHTML = '<div class="col-span-full text-center py-8 text-gray-500 dark:text-gray-400"><span class="material-symbols-outlined text-4xl mb-2 block">folder_open</span><p>Henüz dosya yüklenmemiş</p></div>';
                }
            } catch (error) {
                grid.innerHTML = '<div class="col-span-full text-center py-8 text-red-500"><p>Dosyalar yüklenirken bir hata oluştu: ' + error.message + '</p></div>';
            }
        }

        function selectFile(url) {
            document.getElementById('item-media-url').value = url;
            closeFileManager();
        }

        async function uploadFile() {
            const input = document.getElementById('file-upload-input');
            const statusDiv = document.getElementById('upload-status');
            const uploadBtn = document.getElementById('upload-btn');
            
            if (!input.files || !input.files[0]) {
                alert('Lütfen bir dosya seçin');
                return;
            }
            
            const formData = new FormData();
            formData.append('file', input.files[0]);
            
            uploadBtn.disabled = true;
            uploadBtn.textContent = 'Yükleniyor...';
            statusDiv.classList.remove('hidden');
            statusDiv.className = 'mt-2 p-2 rounded-lg text-sm bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200';
            statusDiv.textContent = 'Dosya yükleniyor...';
            
            try {
                const response = await fetch(adminUrl + 'sliders/file-manager-upload', {
                    method: 'POST',
                    body: formData
                });
                
                // Önce response text'i al, sonra JSON parse et
                const responseText = await response.text();
                let data;
                
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON Parse Error:', parseError);
                    console.error('Response Text:', responseText);
                    statusDiv.className = 'mt-2 p-2 rounded-lg text-sm bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200';
                    statusDiv.textContent = 'Sunucu yanıtı geçersiz. Lütfen konsolu kontrol edin.';
                    uploadBtn.disabled = false;
                    uploadBtn.textContent = 'Yükle';
                    return;
                }
                
                if (data.success) {
                    statusDiv.className = 'mt-2 p-2 rounded-lg text-sm bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200';
                    statusDiv.textContent = data.message;
                    input.value = '';
                    
                    // Dosya listesini yenile
                    setTimeout(() => {
                        loadFiles();
                    }, 500);
                } else {
                    statusDiv.className = 'mt-2 p-2 rounded-lg text-sm bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200';
                    statusDiv.textContent = data.message || 'Dosya yüklenirken bir hata oluştu';
                }
            } catch (error) {
                statusDiv.className = 'mt-2 p-2 rounded-lg text-sm bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200';
                statusDiv.textContent = 'Bir hata oluştu: ' + error.message;
            } finally {
                uploadBtn.disabled = false;
                uploadBtn.textContent = 'Yükle';
            }
        }

        async function deleteFile(fileUrl) {
            if (!confirm('Bu dosyayı silmek istediğinizden emin misiniz?')) {
                return;
            }
            
            try {
                const response = await fetch(adminUrl + 'sliders/file-manager-delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'file_url=' + encodeURIComponent(fileUrl)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    loadFiles();
                } else {
                    alert('Hata: ' + (data.message || 'Dosya silinirken bir hata oluştu'));
                }
            } catch (error) {
                alert('Bir hata oluştu: ' + error.message);
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        // Show/Hide Add Item Modal
        function showAddItemModal() {
            document.getElementById('add-item-modal').classList.remove('hidden');
        }

        function hideAddItemModal() {
            document.getElementById('add-item-modal').classList.add('hidden');
            document.getElementById('add-item-form').reset();
        }

        // Add Item Form Submit
        document.getElementById('add-item-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const submitButton = e.target.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            submitButton.disabled = true;
            submitButton.textContent = 'Ekleniyor...';
            
            try {
                const response = await fetch(adminUrl + 'sliders/add-item', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    hideAddItemModal();
                    location.reload();
                } else {
                    alert('Hata: ' + (data.message || 'Slide eklenirken bir hata oluştu'));
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                }
            } catch (error) {
                alert('Bir hata oluştu: ' + error.message);
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        });

        function editItem(itemId) {
            window.location.href = adminUrl + 'sliders/edit-item/' + itemId;
        }

        function deleteItem(itemId) {
            if (confirm('Bu içeriği silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!')) {
                fetch(adminUrl + 'sliders/delete-item/' + itemId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Hata: ' + (data.message || 'Slide silinirken bir hata oluştu'));
                    }
                })
                .catch(error => {
                    alert('Bir hata oluştu: ' + error.message);
                });
            }
        }
    </script>
</body>
</html>
