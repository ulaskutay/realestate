<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo isset($title) ? esc_html($title) : 'Yeni Slider Oluştur'; ?></title>
    
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
                            <p class="text-gray-900 dark:text-white text-2xl sm:text-3xl font-bold tracking-tight">Yeni Slider Oluştur</p>
                            <p class="text-gray-500 dark:text-gray-400 text-sm sm:text-base font-normal leading-normal">Yeni bir slider oluşturun ve ayarlarını yapılandırın.</p>
                        </div>
                        <a href="<?php echo admin_url('sliders'); ?>" class="flex items-center gap-2 px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors min-h-[44px] w-full sm:w-auto justify-center sm:justify-start">
                            <span class="material-symbols-outlined text-lg sm:text-xl">arrow_back</span>
                            <span class="text-sm font-medium">Geri Dön</span>
                        </a>
                    </header>

                    <!-- Form -->
                    <section class="rounded-xl border border-gray-200 dark:border-white/10 p-4 sm:p-6 bg-background-light dark:bg-background-dark">
                        <form method="POST" action="<?php echo admin_url('sliders/store'); ?>" class="space-y-6">
                            <!-- Slider Adı -->
                            <div class="flex flex-col gap-2">
                                <label for="name" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Slider Adı <span class="text-red-500">*</span></label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    required
                                    placeholder="Ana Sayfa Slider"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-base"
                                />
                                <p class="text-gray-500 dark:text-gray-400 text-xs">Slider'ınızı tanımlayacak bir isim verin.</p>
                            </div>

                            <!-- Animasyon Tipi -->
                            <div class="flex flex-col gap-2">
                                <label for="animation_type" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Animasyon Tipi</label>
                                <select 
                                    id="animation_type" 
                                    name="animation_type"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-base"
                                >
                                    <option value="fade">Fade (Yumuşak Geçiş)</option>
                                    <option value="slide">Slide (Yan Kaydırma)</option>
                                    <option value="zoom">Zoom (Yakınlaştırma)</option>
                                    <option value="cube">Cube (3D Küp)</option>
                                    <option value="flip">Flip (3D Döndürme)</option>
                                    <option value="coverflow">Coverflow (iTunes Tarzı)</option>
                                    <option value="cards">Cards (Kart Flip)</option>
                                </select>
                                <p class="text-gray-500 dark:text-gray-400 text-xs">Slider geçiş animasyonu tipini seçin.</p>
                            </div>

                            <!-- Genişlik ve Yükseklik -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label for="width" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Slider Genişliği</label>
                                    <input 
                                        type="text" 
                                        id="width" 
                                        name="width" 
                                        value="100%"
                                        placeholder="100%, 1200px, 90vw"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-base"
                                    />
                                    <p class="text-gray-500 dark:text-gray-400 text-xs">Slider genişliğini px, % veya vw cinsinden belirtin (örn: 100%, 1200px, 90vw).</p>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label for="height" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Slider Yüksekliği</label>
                                    <input 
                                        type="text" 
                                        id="height" 
                                        name="height" 
                                        value="500px"
                                        placeholder="500px veya 50vh"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-base"
                                    />
                                    <p class="text-gray-500 dark:text-gray-400 text-xs">Slider yüksekliğini px veya vh cinsinden belirtin (örn: 500px, 50vh).</p>
                                </div>
                            </div>

                            <!-- Otomatik Oynatma -->
                            <div class="flex items-center gap-4">
                                <input 
                                    type="checkbox" 
                                    id="autoplay" 
                                    name="autoplay" 
                                    value="1"
                                    checked
                                    class="w-4 h-4 text-primary bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary focus:ring-2"
                                />
                                <label for="autoplay" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Otomatik Oynatma</label>
                            </div>

                            <!-- Otomatik Oynatma Gecikmesi -->
                            <div class="flex flex-col gap-2 ml-8">
                                <label for="autoplay_delay" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Gecikme (milisaniye)</label>
                                    <input 
                                        type="number" 
                                        id="autoplay_delay" 
                                        name="autoplay_delay" 
                                        value="5000"
                                        min="1000"
                                        step="500"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-base"
                                    />
                                <p class="text-gray-500 dark:text-gray-400 text-xs">Her slayt arasındaki bekleme süresi (ms).</p>
                            </div>

                            <!-- Navigasyon -->
                            <div class="flex items-center gap-4">
                                <input 
                                    type="checkbox" 
                                    id="navigation" 
                                    name="navigation" 
                                    value="1"
                                    checked
                                    class="w-4 h-4 text-primary bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary focus:ring-2"
                                />
                                <label for="navigation" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Navigasyon Butonları (Önceki/Sonraki)</label>
                            </div>

                            <!-- Navigation Buton Ayarları -->
                            <div class="border-t border-gray-200 dark:border-white/10 pt-6 mt-6 ml-8">
                                <h3 class="text-gray-900 dark:text-white text-lg font-semibold mb-4">Navigation Buton Ayarları</h3>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <!-- Buton Rengi -->
                                    <div class="flex flex-col gap-2">
                                        <label for="nav_button_color" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Buton İkon Rengi</label>
                                        <input 
                                            type="color" 
                                            id="nav_button_color" 
                                            name="nav_button_color" 
                                            value="#137fec"
                                            class="w-full h-10 rounded-lg border border-gray-200 dark:border-white/10 cursor-pointer"
                                        />
                                    </div>

                                    <!-- Buton Arka Plan Rengi -->
                                    <div class="flex flex-col gap-2">
                                        <label for="nav_button_bg_color" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Buton Arka Plan Rengi</label>
                                        <div class="flex gap-2">
                                            <input 
                                                type="color" 
                                                id="nav_button_bg_color" 
                                                name="nav_button_bg_color" 
                                                value="#ffffff"
                                                class="flex-1 h-10 rounded-lg border border-gray-200 dark:border-white/10 cursor-pointer"
                                            />
                                            <div class="flex-1 flex flex-col gap-1">
                                                <label for="nav_button_bg_opacity" class="text-xs text-gray-600 dark:text-gray-400">Şeffaflık</label>
                                                <input 
                                                    type="range" 
                                                    id="nav_button_bg_opacity" 
                                                    name="nav_button_bg_opacity" 
                                                    min="0" 
                                                    max="1" 
                                                    step="0.1"
                                                    value="0.90"
                                                    class="w-full"
                                                    oninput="document.getElementById('bg-opacity-value-create').textContent = Math.round(this.value * 100) + '%'"
                                                />
                                                <span id="bg-opacity-value-create" class="text-xs text-gray-500 dark:text-gray-400">90%</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Hover İkon Rengi -->
                                    <div class="flex flex-col gap-2">
                                        <label for="nav_button_hover_color" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Hover İkon Rengi</label>
                                        <input 
                                            type="color" 
                                            id="nav_button_hover_color" 
                                            name="nav_button_hover_color" 
                                            value="#137fec"
                                            class="w-full h-10 rounded-lg border border-gray-200 dark:border-white/10 cursor-pointer"
                                        />
                                    </div>

                                    <!-- Hover Arka Plan Rengi -->
                                    <div class="flex flex-col gap-2">
                                        <label for="nav_button_hover_bg_color" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Hover Arka Plan Rengi</label>
                                        <div class="flex gap-2">
                                            <input 
                                                type="color" 
                                                id="nav_button_hover_bg_color" 
                                                name="nav_button_hover_bg_color" 
                                                value="#ffffff"
                                                class="flex-1 h-10 rounded-lg border border-gray-200 dark:border-white/10 cursor-pointer"
                                            />
                                            <div class="flex-1 flex flex-col gap-1">
                                                <label for="nav_button_hover_bg_opacity" class="text-xs text-gray-600 dark:text-gray-400">Şeffaflık</label>
                                                <input 
                                                    type="range" 
                                                    id="nav_button_hover_bg_opacity" 
                                                    name="nav_button_hover_bg_opacity" 
                                                    min="0" 
                                                    max="1" 
                                                    step="0.1"
                                                    value="0.90"
                                                    class="w-full"
                                                    oninput="document.getElementById('hover-bg-opacity-value-create').textContent = Math.round(this.value * 100) + '%'"
                                                />
                                                <span id="hover-bg-opacity-value-create" class="text-xs text-gray-500 dark:text-gray-400">90%</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Buton Boyutu -->
                                    <div class="flex flex-col gap-2">
                                        <label for="nav_button_size" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Buton Boyutu</label>
                                        <input 
                                            type="text" 
                                            id="nav_button_size" 
                                            name="nav_button_size" 
                                            value="50px"
                                            placeholder="50px"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                        />
                                        <p class="text-gray-500 dark:text-gray-400 text-xs">Örn: 50px, 60px, 3rem</p>
                                    </div>

                                    <!-- İkon Boyutu -->
                                    <div class="flex flex-col gap-2">
                                        <label for="nav_button_icon_size" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">İkon Boyutu</label>
                                        <input 
                                            type="text" 
                                            id="nav_button_icon_size" 
                                            name="nav_button_icon_size" 
                                            value="32px"
                                            placeholder="32px"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                        />
                                        <p class="text-gray-500 dark:text-gray-400 text-xs">Örn: 32px, 24px, 2rem</p>
                                    </div>

                                    <!-- Buton Pozisyonu -->
                                    <div class="flex flex-col gap-2">
                                        <label for="nav_button_position" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Buton Pozisyonu</label>
                                        <select 
                                            id="nav_button_position" 
                                            name="nav_button_position"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                        >
                                            <option value="inside" selected>İçeride</option>
                                            <option value="outside">Dışarıda</option>
                                        </select>
                                    </div>

                                    <!-- Opaklık -->
                                    <div class="flex flex-col gap-2">
                                        <label for="nav_button_opacity" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Opaklık</label>
                                        <input 
                                            type="range" 
                                            id="nav_button_opacity" 
                                            name="nav_button_opacity" 
                                            min="0" 
                                            max="1" 
                                            step="0.1"
                                            value="0.90"
                                            class="w-full"
                                            oninput="document.getElementById('opacity-value-create').textContent = Math.round(this.value * 100) + '%'"
                                        />
                                        <p class="text-gray-500 dark:text-gray-400 text-xs">Opaklık: <span id="opacity-value-create">90%</span></p>
                                    </div>

                                    <!-- Köşe Yuvarlama -->
                                    <div class="flex flex-col gap-2">
                                        <label for="nav_button_border_radius" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Köşe Yuvarlama</label>
                                        <input 
                                            type="text" 
                                            id="nav_button_border_radius" 
                                            name="nav_button_border_radius" 
                                            value="50%"
                                            placeholder="50%"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                        />
                                        <p class="text-gray-500 dark:text-gray-400 text-xs">Örn: 50% (yuvarlak), 8px (köşeli), 0 (kare)</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Pagination -->
                            <div class="flex items-center gap-4">
                                <input 
                                    type="checkbox" 
                                    id="pagination" 
                                    name="pagination" 
                                    value="1"
                                    checked
                                    class="w-4 h-4 text-primary bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary focus:ring-2"
                                />
                                <label for="pagination" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Pagination Dots (Sayfa Göstergeleri)</label>
                            </div>

                            <!-- Döngü -->
                            <div class="flex items-center gap-4">
                                <input 
                                    type="checkbox" 
                                    id="loop" 
                                    name="loop" 
                                    value="1"
                                    checked
                                    class="w-4 h-4 text-primary bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary focus:ring-2"
                                />
                                <label for="loop" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Döngü (Sonsuz)</label>
                            </div>

                            <!-- Durum -->
                            <div class="flex items-center gap-4">
                                <input 
                                    type="checkbox" 
                                    id="status" 
                                    name="status" 
                                    value="active"
                                    class="w-4 h-4 text-primary bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary focus:ring-2"
                                />
                                <label for="status" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Hemen Aktifleştir</label>
                                <p class="text-gray-500 dark:text-gray-400 text-xs">(Sadece bir slider aktif olabilir)</p>
                            </div>

                            <!-- Butonlar -->
                            <div class="flex flex-col sm:flex-row justify-end gap-3 sm:gap-4 pt-4 border-t border-gray-200 dark:border-white/10">
                                <a href="<?php echo admin_url('sliders'); ?>" class="px-6 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors font-medium min-h-[44px] flex items-center justify-center order-2 sm:order-1">
                                    İptal
                                </a>
                                <button 
                                    type="submit" 
                                    class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors font-medium min-h-[44px] order-1 sm:order-2"
                                >
                                    Slider Oluştur
                                </button>
                            </div>
                        </form>
                    </section>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
