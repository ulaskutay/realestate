<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo isset($title) ? esc_html($title) : 'Ayarlar'; ?></title>
    
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
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
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
            $currentPage = 'settings';
            include __DIR__ . '/snippets/sidebar.php'; 
            ?>

            <!-- Content Area with Header -->
            <div class="flex-1 flex flex-col lg:ml-64">
                <!-- Top Header -->
                <?php include __DIR__ . '/snippets/top-header.php'; ?>

                <!-- Main Content -->
            <main class="flex-1 p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b]">
                <div class="layout-content-container flex flex-col w-full mx-auto max-w-7xl">
                    <!-- PageHeading -->
                    <header class="flex flex-col gap-2 mb-6">
                        <p class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Ayarlar</p>
                        <p class="text-gray-500 dark:text-gray-400 text-base font-normal leading-normal">Site genel ayarlarını ve SEO yapılandırmasını yönetin.</p>
                    </header>

                    <!-- Success/Error Message -->
                    <?php if (isset($message) && $message): ?>
                        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200'; ?>">
                            <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- General Settings -->
                    <section class="mb-8">
                        <div class="rounded-xl border border-gray-200 dark:border-white/10 p-6 bg-background-light dark:bg-background-dark">
                            <div class="flex items-center gap-3 mb-6">
                                <span class="material-symbols-outlined text-primary text-2xl">tune</span>
                                <h2 class="text-gray-900 dark:text-white text-xl font-semibold leading-normal">Genel Ayarlar</h2>
                            </div>
                            
                            <form method="POST" action="" class="space-y-6" enctype="multipart/form-data">
                                <!-- Logo -->
                                <div class="flex flex-col gap-2">
                                    <label class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Site Logo</label>
                                    <div class="flex items-center gap-4">
                                        <div id="logo-preview" class="flex-shrink-0 w-24 h-24 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark p-2 flex items-center justify-center">
                                            <?php if (!empty($settings['site_logo'])): ?>
                                                <img src="<?php echo esc_url($settings['site_logo']); ?>" alt="Logo" class="max-w-full max-h-full object-contain">
                                            <?php else: ?>
                                                <span class="material-symbols-outlined text-gray-400 text-3xl">image</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1 space-y-2">
                                            <input type="hidden" id="site_logo_url" name="site_logo_url" value="<?php echo esc_attr($settings['site_logo'] ?? ''); ?>">
                                            <div class="flex gap-2">
                                                <button type="button" onclick="openLogoMediaPicker()" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                                    <span class="material-symbols-outlined text-xl">perm_media</span>
                                                    <span>Kütüphaneden Seç</span>
                                                </button>
                                                <?php if (!empty($settings['site_logo'])): ?>
                                                <button type="button" onclick="removeLogo()" class="px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                                    <span class="material-symbols-outlined">delete</span>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-gray-500 dark:text-gray-400 text-xs">İçerik Kütüphanesi'nden logo seçin (JPG, PNG, SVG veya WebP).</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Favicon -->
                                <div class="flex flex-col gap-2">
                                    <label class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Favicon</label>
                                    <div class="flex items-center gap-4">
                                        <div id="favicon-preview" class="flex-shrink-0 w-16 h-16 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark p-2 flex items-center justify-center">
                                            <?php if (!empty($settings['site_favicon'])): ?>
                                                <img src="<?php echo esc_url($settings['site_favicon']); ?>" alt="Favicon" class="max-w-full max-h-full object-contain">
                                            <?php else: ?>
                                                <span class="material-symbols-outlined text-gray-400 text-2xl">image</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1 space-y-2">
                                            <input type="hidden" id="site_favicon_url" name="site_favicon_url" value="<?php echo esc_attr($settings['site_favicon'] ?? ''); ?>">
                                            <div class="flex gap-2">
                                                <button type="button" onclick="openFaviconMediaPicker()" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                                    <span class="material-symbols-outlined text-xl">perm_media</span>
                                                    <span>Kütüphaneden Seç</span>
                                                </button>
                                                <?php if (!empty($settings['site_favicon'])): ?>
                                                <button type="button" onclick="removeFavicon()" class="px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                                    <span class="material-symbols-outlined">delete</span>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-gray-500 dark:text-gray-400 text-xs">İçerik Kütüphanesi'nden favicon seçin (ICO, PNG veya SVG).</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Google Analytics -->
                                <div class="flex flex-col gap-2">
                                    <label for="google_analytics" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Google Analytics ID</label>
                                    <input 
                                        type="text" 
                                        id="google_analytics" 
                                        name="google_analytics" 
                                        value="<?php echo esc_attr($settings['google_analytics'] ?? ''); ?>"
                                        placeholder="G-XXXXXXXXXX veya UA-XXXXXXXXX-X"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                    />
                                    <p class="text-gray-500 dark:text-gray-400 text-xs">Google Analytics takip kodunuzu girin (G-XXXXXXXXXX veya UA-XXXXXXXXX-X formatında).</p>
                                </div>

                                <!-- Google Tag Manager -->
                                <div class="flex flex-col gap-2">
                                    <label for="google_tag_manager" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Google Tag Manager ID</label>
                                    <input 
                                        type="text" 
                                        id="google_tag_manager" 
                                        name="google_tag_manager" 
                                        value="<?php echo esc_attr($settings['google_tag_manager'] ?? ''); ?>"
                                        placeholder="GTM-XXXXXXX"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                    />
                                    <p class="text-gray-500 dark:text-gray-400 text-xs">Google Tag Manager container ID'nizi girin (GTM-XXXXXXX formatında).</p>
                                </div>

                                <!-- Google Ads -->
                                <div class="flex flex-col gap-2">
                                    <label for="google_ads" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Google Ads Kodu</label>
                                    <textarea 
                                        id="google_ads" 
                                        name="google_ads" 
                                        rows="4"
                                        placeholder="<!-- Google Ads kodunuzu buraya yapıştırın -->"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent font-mono text-sm"
                                    ><?php echo esc_html($settings['google_ads'] ?? ''); ?></textarea>
                                    <p class="text-gray-500 dark:text-gray-400 text-xs">Google Ads conversion tracking kodunuzu buraya yapıştırın.</p>
                                </div>

                                <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-white/10">
                                    <button 
                                        type="submit" 
                                        name="save_general"
                                        class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors font-medium"
                                    >
                                        Genel Ayarları Kaydet
                                    </button>
                                </div>
                            </form>
                        </div>
                    </section>

                    <!-- SEO Settings -->
                    <section class="mb-8">
                        <div class="rounded-xl border border-gray-200 dark:border-white/10 p-6 bg-background-light dark:bg-background-dark">
                            <div class="flex items-center gap-3 mb-6">
                                <span class="material-symbols-outlined text-primary text-2xl">search</span>
                                <h2 class="text-gray-900 dark:text-white text-xl font-semibold leading-normal">SEO Ayarları</h2>
                            </div>
                            
                            <form method="POST" action="" class="space-y-6">
                                <!-- SEO Title -->
                                <div class="flex flex-col gap-2">
                                    <label for="seo_title" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Site Başlığı (Title)</label>
                                    <input 
                                        type="text" 
                                        id="seo_title" 
                                        name="seo_title" 
                                        value="<?php echo esc_attr($settings['seo_title'] ?? ''); ?>"
                                        placeholder="Site Başlığı"
                                        maxlength="60"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                    />
                                    <p class="text-gray-500 dark:text-gray-400 text-xs">Arama motorlarında görünecek site başlığı (önerilen: 50-60 karakter).</p>
                                </div>

                                <!-- SEO Description -->
                                <div class="flex flex-col gap-2">
                                    <label for="seo_description" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Site Açıklaması (Meta Description)</label>
                                    <textarea 
                                        id="seo_description" 
                                        name="seo_description" 
                                        rows="3"
                                        placeholder="Site açıklaması"
                                        maxlength="160"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                    ><?php echo esc_html($settings['seo_description'] ?? ''); ?></textarea>
                                    <p class="text-gray-500 dark:text-gray-400 text-xs">Arama motorlarında görünecek site açıklaması (önerilen: 150-160 karakter).</p>
                                </div>

                                <!-- SEO Author -->
                                <div class="flex flex-col gap-2">
                                    <label for="seo_author" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Site Yazarı</label>
                                    <input 
                                        type="text" 
                                        id="seo_author" 
                                        name="seo_author" 
                                        value="<?php echo esc_attr($settings['seo_author'] ?? ''); ?>"
                                        placeholder="Yazar Adı"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                    />
                                    <p class="text-gray-500 dark:text-gray-400 text-xs">Site içeriğinin yazarı.</p>
                                </div>

                                <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-white/10">
                                    <button 
                                        type="submit" 
                                        name="save_seo"
                                        class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors font-medium"
                                    >
                                        SEO Ayarlarını Kaydet
                                    </button>
                                </div>
                            </form>
                        </div>
                    </section>

                    <!-- Company Information Settings -->
                    <section class="mb-8">
                        <div class="rounded-xl border border-gray-200 dark:border-white/10 p-6 bg-background-light dark:bg-background-dark">
                            <div class="flex items-center gap-3 mb-6">
                                <span class="material-symbols-outlined text-primary text-2xl">business</span>
                                <h2 class="text-gray-900 dark:text-white text-xl font-semibold leading-normal">Şirket Bilgileri</h2>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">Bu bilgiler sözleşmelerde ve yasal metinlerde otomatik olarak kullanılacaktır.</p>
                            
                            <form method="POST" action="" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Şirket Adı -->
                                    <div class="flex flex-col gap-2">
                                        <label for="company_name" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Şirket / Firma Adı *</label>
                                        <input 
                                            type="text" 
                                            id="company_name" 
                                            name="company_name" 
                                            value="<?php echo esc_attr($settings['company_name'] ?? ''); ?>"
                                            placeholder="Örn: ABC Teknoloji A.Ş."
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                        />
                                    </div>

                                    <!-- Vergi No / Mersis -->
                                    <div class="flex flex-col gap-2">
                                        <label for="company_tax_number" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Vergi No / Mersis No</label>
                                        <input 
                                            type="text" 
                                            id="company_tax_number" 
                                            name="company_tax_number" 
                                            value="<?php echo esc_attr($settings['company_tax_number'] ?? ''); ?>"
                                            placeholder="Örn: 1234567890"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                        />
                                    </div>

                                    <!-- E-posta -->
                                    <div class="flex flex-col gap-2">
                                        <label for="company_email" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">E-posta Adresi *</label>
                                        <input 
                                            type="email" 
                                            id="company_email" 
                                            name="company_email" 
                                            value="<?php echo esc_attr($settings['company_email'] ?? ''); ?>"
                                            placeholder="info@example.com"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                        />
                                    </div>

                                    <!-- Telefon -->
                                    <div class="flex flex-col gap-2">
                                        <label for="company_phone" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Telefon Numarası</label>
                                        <input 
                                            type="tel" 
                                            id="company_phone" 
                                            name="company_phone" 
                                            value="<?php echo esc_attr($settings['company_phone'] ?? ''); ?>"
                                            placeholder="+90 (212) 123 45 67"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                        />
                                    </div>
                                </div>

                                <!-- Adres -->
                                <div class="flex flex-col gap-2">
                                    <label for="company_address" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Şirket Adresi</label>
                                    <textarea 
                                        id="company_address" 
                                        name="company_address" 
                                        rows="2"
                                        placeholder="Örn: Atatürk Mah. Cumhuriyet Cad. No:123 Kat:4, Beşiktaş / İstanbul"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                    ><?php echo esc_html($settings['company_address'] ?? ''); ?></textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Şehir -->
                                    <div class="flex flex-col gap-2">
                                        <label for="company_city" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Şehir (Yetkili Mahkeme)</label>
                                        <input 
                                            type="text" 
                                            id="company_city" 
                                            name="company_city" 
                                            value="<?php echo esc_attr($settings['company_city'] ?? ''); ?>"
                                            placeholder="Örn: İstanbul"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                        />
                                        <p class="text-gray-500 dark:text-gray-400 text-xs">Sözleşmelerde yetkili mahkeme olarak görünecek şehir.</p>
                                    </div>

                                    <!-- KEP Adresi -->
                                    <div class="flex flex-col gap-2">
                                        <label for="company_kep" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">KEP Adresi</label>
                                        <input 
                                            type="email" 
                                            id="company_kep" 
                                            name="company_kep" 
                                            value="<?php echo esc_attr($settings['company_kep'] ?? ''); ?>"
                                            placeholder="sirket@hs01.kep.tr"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                        />
                                    </div>
                                </div>

                                <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-white/10">
                                    <button 
                                        type="submit" 
                                        name="save_company"
                                        class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors font-medium"
                                    >
                                        Şirket Bilgilerini Kaydet
                                    </button>
                                </div>
                            </form>
                        </div>
                    </section>

                    <!-- Social Media Settings -->
                    <section class="mb-8">
                        <div class="rounded-xl border border-gray-200 dark:border-white/10 p-6 bg-background-light dark:bg-background-dark">
                            <div class="flex items-center gap-3 mb-6">
                                <span class="material-symbols-outlined text-primary text-2xl">share</span>
                                <h2 class="text-gray-900 dark:text-white text-xl font-semibold leading-normal">Sosyal Medya</h2>
                            </div>
                            
                            <form method="POST" action="" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Facebook -->
                                    <div class="flex flex-col gap-2">
                                        <label for="social_facebook" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal flex items-center gap-2">
                                            <span class="text-blue-600 dark:text-blue-400">📘</span>
                                            Facebook URL
                                        </label>
                                        <input 
                                            type="url" 
                                            id="social_facebook" 
                                            name="social_facebook" 
                                            value="<?php echo esc_attr($settings['social_facebook'] ?? ''); ?>"
                                            placeholder="https://facebook.com/kullaniciadi"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                        />
                                    </div>

                                    <!-- Instagram -->
                                    <div class="flex flex-col gap-2">
                                        <label for="social_instagram" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal flex items-center gap-2">
                                            <span class="text-pink-600 dark:text-pink-400">📷</span>
                                            Instagram URL
                                        </label>
                                        <input 
                                            type="url" 
                                            id="social_instagram" 
                                            name="social_instagram" 
                                            value="<?php echo esc_attr($settings['social_instagram'] ?? ''); ?>"
                                            placeholder="https://instagram.com/kullaniciadi"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                        />
                                    </div>

                                    <!-- Twitter -->
                                    <div class="flex flex-col gap-2">
                                        <label for="social_twitter" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal flex items-center gap-2">
                                            <span class="text-blue-400">🐦</span>
                                            Twitter URL
                                        </label>
                                        <input 
                                            type="url" 
                                            id="social_twitter" 
                                            name="social_twitter" 
                                            value="<?php echo esc_attr($settings['social_twitter'] ?? ''); ?>"
                                            placeholder="https://twitter.com/kullaniciadi"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                        />
                                    </div>

                                    <!-- LinkedIn -->
                                    <div class="flex flex-col gap-2">
                                        <label for="social_linkedin" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal flex items-center gap-2">
                                            <span class="text-blue-700 dark:text-blue-500">💼</span>
                                            LinkedIn URL
                                        </label>
                                        <input 
                                            type="url" 
                                            id="social_linkedin" 
                                            name="social_linkedin" 
                                            value="<?php echo esc_attr($settings['social_linkedin'] ?? ''); ?>"
                                            placeholder="https://linkedin.com/company/kullaniciadi"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                        />
                                    </div>

                                    <!-- YouTube -->
                                    <div class="flex flex-col gap-2">
                                        <label for="social_youtube" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal flex items-center gap-2">
                                            <span class="text-red-600 dark:text-red-400">📺</span>
                                            YouTube URL
                                        </label>
                                        <input 
                                            type="url" 
                                            id="social_youtube" 
                                            name="social_youtube" 
                                            value="<?php echo esc_attr($settings['social_youtube'] ?? ''); ?>"
                                            placeholder="https://youtube.com/@kullaniciadi"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                        />
                                    </div>

                                    <!-- TikTok -->
                                    <div class="flex flex-col gap-2">
                                        <label for="social_tiktok" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal flex items-center gap-2">
                                            <span class="text-gray-900 dark:text-white">🎵</span>
                                            TikTok URL
                                        </label>
                                        <input 
                                            type="url" 
                                            id="social_tiktok" 
                                            name="social_tiktok" 
                                            value="<?php echo esc_attr($settings['social_tiktok'] ?? ''); ?>"
                                            placeholder="https://tiktok.com/@kullaniciadi"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                        />
                                    </div>

                                    <!-- Pinterest -->
                                    <div class="flex flex-col gap-2">
                                        <label for="social_pinterest" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal flex items-center gap-2">
                                            <span class="text-red-700 dark:text-red-500">📌</span>
                                            Pinterest URL
                                        </label>
                                        <input 
                                            type="url" 
                                            id="social_pinterest" 
                                            name="social_pinterest" 
                                            value="<?php echo esc_attr($settings['social_pinterest'] ?? ''); ?>"
                                            placeholder="https://pinterest.com/kullaniciadi"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                        />
                                    </div>
                                </div>

                                <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-white/10">
                                    <button 
                                        type="submit" 
                                        name="save_social"
                                        class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors font-medium"
                                    >
                                        Sosyal Medya Ayarlarını Kaydet
                                    </button>
                                </div>
                            </form>
                        </div>
                    </section>

                    <!-- AI Settings -->
                    <section class="mb-8">
                        <div class="rounded-xl border border-gray-200 dark:border-white/10 p-6 bg-background-light dark:bg-background-dark">
                            <div class="flex items-center gap-3 mb-6">
                                <span class="material-symbols-outlined text-primary text-2xl">auto_awesome</span>
                                <h2 class="text-gray-900 dark:text-white text-xl font-semibold leading-normal">Yapay Zeka Ayarları</h2>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">Emlak ilan açıklamalarını otomatik olarak oluşturmak için Groq Cloud API kullanılmaktadır. Ücretsiz hesap oluşturarak API anahtarınızı alabilirsiniz.</p>
                            
                            <form method="POST" action="" class="space-y-6">
                                <!-- Groq API Key -->
                                <div class="flex flex-col gap-2">
                                    <label for="groq_api_key" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Groq API Anahtarı</label>
                                    <input 
                                        type="password" 
                                        id="groq_api_key" 
                                        name="groq_api_key" 
                                        value="<?php echo esc_attr($settings['groq_api_key'] ?? ''); ?>"
                                        placeholder="gsk_xxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent font-mono text-sm"
                                    />
                                    <p class="text-gray-500 dark:text-gray-400 text-xs">
                                        <a href="https://console.groq.com" target="_blank" class="text-primary hover:underline">Groq Console</a> adresinden ücretsiz hesap oluşturarak API anahtarınızı alabilirsiniz. 
                                        API anahtarı güvenli bir şekilde saklanır ve sadece ilan açıklamaları oluştururken kullanılır.
                                    </p>
                                    <div class="mt-2 p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                                        <p class="text-sm text-blue-800 dark:text-blue-200">
                                            <strong>💡 İpucu:</strong> Groq Cloud ücretsiz katmanında günde binlerce istek yapabilirsiniz. 
                                            İlan açıklamalarınızı saniyeler içinde oluşturabilirsiniz.
                                        </p>
                                    </div>
                                </div>

                                <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-white/10">
                                    <button 
                                        type="submit" 
                                        name="save_ai_settings"
                                        class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors font-medium"
                                    >
                                        AI Ayarlarını Kaydet
                                    </button>
                                </div>
                            </form>
                        </div>
                    </section>

                    <!-- Spam Protection Settings -->
                    <section class="mb-8">
                        <div class="rounded-xl border border-gray-200 dark:border-white/10 p-6 bg-background-light dark:bg-background-dark">
                            <div class="flex items-center gap-3 mb-6">
                                <span class="material-symbols-outlined text-primary text-2xl">security</span>
                                <h2 class="text-gray-900 dark:text-white text-xl font-semibold leading-normal">Spam Koruma Ayarları</h2>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">Form gönderimlerini spam'dan korumak için Honeypot spam filtreleme sistemi kullanılmaktadır. Bu yöntem görünmez bir form alanı ile botları yakalar ve kullanıcılar için tamamen görünmezdir.</p>
                            
                            <form method="POST" action="" class="space-y-6">
                                <!-- Enable Honeypot -->
                                <div class="flex items-center gap-3">
                                    <input 
                                        type="checkbox" 
                                        id="honeypot_enabled" 
                                        name="honeypot_enabled" 
                                        value="1"
                                        <?php echo ($settings['honeypot_enabled'] ?? 1) ? 'checked' : ''; ?>
                                        class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary"
                                    />
                                    <label for="honeypot_enabled" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">
                                        Honeypot Spam Koruması
                                    </label>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400 text-xs ml-7">Görünmez bir form alanı ile botları yakalar. Kullanıcılar için tamamen görünmez ve ücretsizdir. Varsayılan olarak aktif durumdadır.</p>

                                <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-white/10">
                                    <button 
                                        type="submit" 
                                        name="save_spam_protection"
                                        class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors font-medium"
                                    >
                                        Spam Koruma Ayarlarını Kaydet
                                    </button>
                                </div>
                            </form>
                        </div>
                    </section>
                </div>
                </main>
            </div>
        </div>
    </div>

    <script>
        // Logo yükleme işlemi
        document.getElementById('logo_upload')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('logo', file);

            const statusDiv = document.getElementById('logo_upload_status');
            statusDiv.classList.remove('hidden');
            statusDiv.className = 'mt-2 p-2 rounded-lg text-sm bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200';
            statusDiv.textContent = 'Logo yükleniyor...';

            fetch('<?php echo admin_url('upload_logo'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusDiv.className = 'mt-2 p-2 rounded-lg text-sm bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200';
                    statusDiv.textContent = data.message;
                    
                    // Sayfayı yenile
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    statusDiv.className = 'mt-2 p-2 rounded-lg text-sm bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200';
                    statusDiv.textContent = data.message;
                }
            })
            .catch(error => {
                statusDiv.className = 'mt-2 p-2 rounded-lg text-sm bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200';
                statusDiv.textContent = 'Bir hata oluştu: ' + error.message;
            });
        });

        // Favicon yükleme işlemi
        document.getElementById('favicon_upload')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('favicon', file);

            const statusDiv = document.getElementById('favicon_upload_status');
            statusDiv.classList.remove('hidden');
            statusDiv.className = 'mt-2 p-2 rounded-lg text-sm bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200';
            statusDiv.textContent = 'Favicon yükleniyor...';

            fetch('<?php echo admin_url('upload_favicon'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusDiv.className = 'mt-2 p-2 rounded-lg text-sm bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200';
                    statusDiv.textContent = data.message;
                    
                    // Sayfayı yenile
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    statusDiv.className = 'mt-2 p-2 rounded-lg text-sm bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200';
                    statusDiv.textContent = data.message;
                }
            })
            .catch(error => {
                statusDiv.className = 'mt-2 p-2 rounded-lg text-sm bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200';
                statusDiv.textContent = 'Bir hata oluştu: ' + error.message;
            });
        });
    </script>
    
    <!-- Media Picker JS -->
    <script src="<?php echo rtrim(site_url(), '/') . '/admin/js/media-picker.js'; ?>"></script>
    <script>
        // Logo Media Picker
        function openLogoMediaPicker() {
            openMediaPicker({
                type: 'image',
                onSelect: function(media) {
                    document.getElementById('site_logo_url').value = media.file_url;
                    document.getElementById('logo-preview').innerHTML = `<img src="${media.file_url}" alt="Logo" class="max-w-full max-h-full object-contain">`;
                }
            });
        }
        
        function removeLogo() {
            if (confirm('Logo silmek istediğinizden emin misiniz?')) {
                document.getElementById('site_logo_url').value = '';
                document.getElementById('logo-preview').innerHTML = '<span class="material-symbols-outlined text-gray-400 text-3xl">image</span>';
            }
        }
        
        // Favicon Media Picker
        function openFaviconMediaPicker() {
            openMediaPicker({
                type: 'image',
                onSelect: function(media) {
                    document.getElementById('site_favicon_url').value = media.file_url;
                    document.getElementById('favicon-preview').innerHTML = `<img src="${media.file_url}" alt="Favicon" class="max-w-full max-h-full object-contain">`;
                }
            });
        }
        
        function removeFavicon() {
            if (confirm('Favicon silmek istediğinizden emin misiniz?')) {
                document.getElementById('site_favicon_url').value = '';
                document.getElementById('favicon-preview').innerHTML = '<span class="material-symbols-outlined text-gray-400 text-2xl">image</span>';
            }
        }
    </script>
</body>
</html>
