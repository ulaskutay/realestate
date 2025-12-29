<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo isset($title) ? esc_html($title) : 'Yeni Form'; ?></title>
    
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
    
    
    
    
    <!-- Custom CSS -->
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
            $currentPage = 'forms';
            include __DIR__ . '/../snippets/sidebar.php'; 
            ?>

            <!-- Main Content -->
            <main class="main-content-with-sidebar flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b]">
                <div class="layout-content-container flex flex-col w-full mx-auto max-w-4xl">
                    <!-- PageHeading -->
                    <header class="flex items-center gap-2 sm:gap-4 mb-6">
                        <a href="<?php echo admin_url('forms'); ?>" class="p-2 text-gray-600 dark:text-gray-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors flex-shrink-0">
                            <span class="material-symbols-outlined text-lg sm:text-xl">arrow_back</span>
                        </a>
                        <div class="flex flex-col gap-1 min-w-0">
                            <p class="text-gray-900 dark:text-white text-xl sm:text-2xl font-bold tracking-tight">Yeni Form Oluştur</p>
                            <p class="text-gray-500 dark:text-gray-400 text-xs sm:text-sm">Form ayarlarını yapılandırın, ardından alanları ekleyin.</p>
                        </div>
                    </header>

                    <!-- Form -->
                    <form action="<?php echo admin_url('forms/store'); ?>" method="POST" class="space-y-6">
                        
                        <!-- Temel Ayarlar -->
                        <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark p-4 sm:p-6">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4">Temel Ayarlar</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Form Adı *</label>
                                    <input type="text" name="name" required 
                                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                                           placeholder="Örn: İletişim Formu">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Açıklama</label>
                                    <textarea name="description" rows="2"
                                              class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                                              placeholder="Form hakkında kısa bir açıklama..."></textarea>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Form Stili</label>
                                        <select name="form_style" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="default">Varsayılan</option>
                                            <option value="modern">Modern</option>
                                            <option value="minimal">Minimal</option>
                                            <option value="bordered">Kenarlıklı</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Düzen</label>
                                        <select name="layout" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="vertical">Dikey</option>
                                            <option value="horizontal">Yatay</option>
                                            <option value="inline">Satır İçi</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Gönder Butonu Ayarları -->
                        <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark p-4 sm:p-6">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4">Gönder Butonu</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buton Metni</label>
                                    <input type="text" name="submit_button_text" value="Gönder"
                                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buton Rengi</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" name="submit_button_color" value="#137fec" 
                                               class="w-12 h-10 rounded cursor-pointer border-0">
                                        <input type="text" id="button_color_text" value="#137fec" 
                                               class="flex-1 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                                               onchange="document.querySelector('[name=submit_button_color]').value = this.value">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Gönderim Sonrası -->
                        <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark p-4 sm:p-6">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4">Gönderim Sonrası</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Başarı Mesajı</label>
                                    <textarea name="success_message" rows="2"
                                              class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">Formunuz başarıyla gönderildi!</textarea>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Yönlendirme URL'si (opsiyonel)</label>
                                    <input type="url" name="redirect_url"
                                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                                           placeholder="https://example.com/tesekkurler">
                                    <p class="text-xs text-gray-500 mt-1">Boş bırakırsanız başarı mesajı gösterilir.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- E-posta Bildirimi -->
                        <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark p-4 sm:p-6">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4">E-posta Bildirimi</h3>
                            
                            <div class="space-y-4">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="email_notification" value="1" checked
                                           class="w-5 h-5 rounded text-primary focus:ring-primary border-gray-300 dark:border-gray-600">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Yeni gönderimde e-posta bildirimi gönder</span>
                                </label>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Bildirim E-postası</label>
                                        <input type="email" name="notification_email"
                                               class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                                               placeholder="admin@example.com">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">E-posta Konusu</label>
                                        <input type="text" name="email_subject"
                                               class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                                               placeholder="Yeni Form Gönderimi">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Durum -->
                        <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark p-4 sm:p-6">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4">Durum</h3>
                            
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="status" value="active" checked
                                       class="w-5 h-5 rounded text-primary focus:ring-primary border-gray-300 dark:border-gray-600">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Form aktif (gönderimleri kabul eder)</span>
                            </label>
                        </div>
                        
                        <!-- Kaydet -->
                        <div class="flex flex-col sm:flex-row justify-end gap-3">
                            <a href="<?php echo admin_url('forms'); ?>" class="px-6 py-2.5 sm:py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-center min-h-[44px] flex items-center justify-center">
                                İptal
                            </a>
                            <button type="submit" class="px-6 py-2.5 sm:py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors min-h-[44px]">
                                Oluştur ve Alanları Ekle
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        // Renk seçici senkronizasyonu
        document.querySelector('[name=submit_button_color]').addEventListener('input', function() {
            document.getElementById('button_color_text').value = this.value;
        });
    </script>
</body>
</html>

