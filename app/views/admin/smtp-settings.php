<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo isset($title) ? esc_html($title) : 'SMTP Ayarları'; ?></title>
    
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
            $currentPage = 'smtp';
            include __DIR__ . '/snippets/sidebar.php'; 
            ?>

            <!-- Content Area with Header -->
            <div class="flex-1 flex flex-col lg:ml-64">
                <!-- Top Header -->
                <?php include __DIR__ . '/snippets/top-header.php'; ?>

                <!-- Main Content -->
            <main class="flex-1 p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b]">
                <div class="layout-content-container flex flex-col w-full mx-auto max-w-4xl">
                    <!-- PageHeading -->
                    <header class="flex flex-col gap-2 mb-6">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-primary text-3xl">mail</span>
                            <p class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">SMTP Ayarları</p>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-base font-normal leading-normal">E-posta gönderimi için SMTP sunucu yapılandırmasını yönetin.</p>
                    </header>

                    <!-- Success/Error Message -->
                    <?php if (isset($message) && $message): ?>
                        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200'; ?>">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined"><?php echo $messageType === 'success' ? 'check_circle' : 'error'; ?></span>
                                <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Info Box -->
                    <div class="mb-6 p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">info</span>
                            <div>
                                <p class="text-blue-800 dark:text-blue-200 text-sm font-medium mb-1">SMTP Nedir?</p>
                                <p class="text-blue-700 dark:text-blue-300 text-sm">SMTP (Simple Mail Transfer Protocol), e-posta gönderimi için kullanılan bir protokoldür. Form bildirimlerini, şifre sıfırlama e-postalarını ve diğer sistem bildirimlerini göndermek için bir SMTP sunucusu yapılandırmanız gerekir.</p>
                            </div>
                        </div>
                    </div>

                    <!-- SMTP Settings Form -->
                    <section class="mb-8">
                        <form method="POST" action="" class="space-y-6" id="smtp-form">
                            <!-- Sunucu Ayarları -->
                            <div class="rounded-xl border border-gray-200 dark:border-white/10 p-6 bg-background-light dark:bg-background-dark">
                                <div class="flex items-center gap-3 mb-6">
                                    <span class="material-symbols-outlined text-primary text-2xl">dns</span>
                                    <h2 class="text-gray-900 dark:text-white text-xl font-semibold leading-normal">Sunucu Ayarları</h2>
                                </div>
                                
                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- SMTP Host -->
                                        <div class="flex flex-col gap-2">
                                            <label for="smtp_host" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">SMTP Sunucusu <span class="text-red-500">*</span></label>
                                            <input 
                                                type="text" 
                                                id="smtp_host" 
                                                name="smtp_host" 
                                                value="<?php echo esc_attr($settings['smtp_host'] ?? ''); ?>"
                                                placeholder="smtp.gmail.com"
                                                required
                                                class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                            />
                                            <p class="text-gray-500 dark:text-gray-400 text-xs">Örn: smtp.gmail.com, mail.yourdomain.com</p>
                                        </div>

                                        <!-- SMTP Port -->
                                        <div class="flex flex-col gap-2">
                                            <label for="smtp_port" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Port <span class="text-red-500">*</span></label>
                                            <input 
                                                type="number" 
                                                id="smtp_port" 
                                                name="smtp_port" 
                                                value="<?php echo esc_attr($settings['smtp_port'] ?? 587); ?>"
                                                placeholder="587"
                                                required
                                                min="1"
                                                max="65535"
                                                class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                            />
                                            <p class="text-gray-500 dark:text-gray-400 text-xs">TLS: 587, SSL: 465, None: 25</p>
                                        </div>
                                    </div>

                                    <!-- Encryption -->
                                    <div class="flex flex-col gap-2">
                                        <label for="smtp_encryption" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Şifreleme</label>
                                        <select 
                                            id="smtp_encryption" 
                                            name="smtp_encryption" 
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                        >
                                            <option value="tls" <?php echo ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : ''; ?>>TLS (Önerilen)</option>
                                            <option value="ssl" <?php echo ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                            <option value="none" <?php echo ($settings['smtp_encryption'] ?? '') === 'none' ? 'selected' : ''; ?>>Şifreleme Yok</option>
                                        </select>
                                        <p class="text-gray-500 dark:text-gray-400 text-xs">Çoğu modern e-posta sağlayıcısı TLS şifrelemesi kullanır.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Kimlik Doğrulama -->
                            <div class="rounded-xl border border-gray-200 dark:border-white/10 p-6 bg-background-light dark:bg-background-dark">
                                <div class="flex items-center gap-3 mb-6">
                                    <span class="material-symbols-outlined text-primary text-2xl">key</span>
                                    <h2 class="text-gray-900 dark:text-white text-xl font-semibold leading-normal">Kimlik Doğrulama</h2>
                                </div>
                                
                                <div class="space-y-4">
                                    <!-- Username -->
                                    <div class="flex flex-col gap-2">
                                        <label for="smtp_username" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Kullanıcı Adı / E-posta <span class="text-red-500">*</span></label>
                                        <input 
                                            type="text" 
                                            id="smtp_username" 
                                            name="smtp_username" 
                                            value="<?php echo esc_attr($settings['smtp_username'] ?? ''); ?>"
                                            placeholder="your-email@gmail.com"
                                            required
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                        />
                                        <p class="text-gray-500 dark:text-gray-400 text-xs">Genellikle e-posta adresiniz olur.</p>
                                    </div>

                                    <!-- Password -->
                                    <div class="flex flex-col gap-2">
                                        <label for="smtp_password" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Şifre <?php echo empty($settings['smtp_password']) ? '<span class="text-red-500">*</span>' : ''; ?></label>
                                        <div class="relative">
                                            <input 
                                                type="password" 
                                                id="smtp_password" 
                                                name="smtp_password" 
                                                value=""
                                                placeholder="<?php echo !empty($settings['smtp_password']) ? '••••••••••••' : 'SMTP şifresi'; ?>"
                                                <?php echo empty($settings['smtp_password']) ? 'required' : ''; ?>
                                                class="w-full px-4 py-2 pr-12 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                            />
                                            <button 
                                                type="button" 
                                                onclick="togglePasswordVisibility()"
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                            >
                                                <span class="material-symbols-outlined text-xl" id="password-toggle-icon">visibility</span>
                                            </button>
                                        </div>
                                        <?php if (!empty($settings['smtp_password'])): ?>
                                            <p class="text-gray-500 dark:text-gray-400 text-xs">Mevcut şifre kaydedildi. Değiştirmek için yeni şifre girin veya boş bırakın.</p>
                                        <?php else: ?>
                                            <p class="text-gray-500 dark:text-gray-400 text-xs">Gmail için "Uygulama Şifresi" kullanmanız gerekebilir.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Gönderen Bilgileri -->
                            <div class="rounded-xl border border-gray-200 dark:border-white/10 p-6 bg-background-light dark:bg-background-dark">
                                <div class="flex items-center gap-3 mb-6">
                                    <span class="material-symbols-outlined text-primary text-2xl">person</span>
                                    <h2 class="text-gray-900 dark:text-white text-xl font-semibold leading-normal">Gönderen Bilgileri</h2>
                                </div>
                                
                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- From Email -->
                                        <div class="flex flex-col gap-2">
                                            <label for="smtp_from_email" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Gönderen E-posta <span class="text-red-500">*</span></label>
                                            <input 
                                                type="email" 
                                                id="smtp_from_email" 
                                                name="smtp_from_email" 
                                                value="<?php echo esc_attr($settings['smtp_from_email'] ?? ''); ?>"
                                                placeholder="noreply@yourdomain.com"
                                                required
                                                class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                            />
                                            <p class="text-gray-500 dark:text-gray-400 text-xs">E-postaların gönderileceği adres.</p>
                                        </div>

                                        <!-- From Name -->
                                        <div class="flex flex-col gap-2">
                                            <label for="smtp_from_name" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Gönderen Adı</label>
                                            <input 
                                                type="text" 
                                                id="smtp_from_name" 
                                                name="smtp_from_name" 
                                                value="<?php echo esc_attr($settings['smtp_from_name'] ?? ''); ?>"
                                                placeholder="Site Adınız"
                                                class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                            />
                                            <p class="text-gray-500 dark:text-gray-400 text-xs">Alıcının göreceği gönderen adı.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-col sm:flex-row gap-4 pt-4">
                                <button 
                                    type="submit" 
                                    name="save_smtp"
                                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors font-medium"
                                >
                                    <span class="material-symbols-outlined">save</span>
                                    Ayarları Kaydet
                                </button>
                                
                                <button 
                                    type="button" 
                                    onclick="testSMTPConnection()"
                                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-6 py-3 border border-gray-300 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors font-medium"
                                >
                                    <span class="material-symbols-outlined">sync</span>
                                    Bağlantıyı Test Et
                                </button>
                            </div>
                        </form>
                    </section>

                    <!-- Test Email Section -->
                    <section class="mb-8">
                        <div class="rounded-xl border border-gray-200 dark:border-white/10 p-6 bg-background-light dark:bg-background-dark">
                            <div class="flex items-center gap-3 mb-6">
                                <span class="material-symbols-outlined text-primary text-2xl">forward_to_inbox</span>
                                <h2 class="text-gray-900 dark:text-white text-xl font-semibold leading-normal">Test E-postası Gönder</h2>
                            </div>
                            
                            <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">SMTP ayarlarınızın doğru çalıştığını doğrulamak için bir test e-postası gönderin.</p>
                            
                            <div class="flex flex-col sm:flex-row gap-4">
                                <div class="flex-1">
                                    <input 
                                        type="email" 
                                        id="test_email" 
                                        name="test_email"
                                        placeholder="test@example.com"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                    />
                                </div>
                                <button 
                                    type="button" 
                                    onclick="sendTestEmail()"
                                    class="inline-flex items-center justify-center gap-2 px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors font-medium"
                                >
                                    <span class="material-symbols-outlined">send</span>
                                    Test E-postası Gönder
                                </button>
                            </div>
                            
                            <!-- Test Result -->
                            <div id="test-result" class="mt-4 hidden"></div>
                        </div>
                    </section>

                    <!-- Popular SMTP Providers -->
                    <section class="mb-8">
                        <div class="rounded-xl border border-gray-200 dark:border-white/10 p-6 bg-background-light dark:bg-background-dark">
                            <div class="flex items-center gap-3 mb-6">
                                <span class="material-symbols-outlined text-primary text-2xl">help</span>
                                <h2 class="text-gray-900 dark:text-white text-xl font-semibold leading-normal">Popüler SMTP Sağlayıcıları</h2>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <!-- Gmail -->
                                <div class="p-4 rounded-lg border border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/5">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-xl">📧</span>
                                        <h3 class="text-gray-900 dark:text-white font-medium">Gmail</h3>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 text-xs space-y-1">
                                        <span class="block">Sunucu: smtp.gmail.com</span>
                                        <span class="block">Port: 587 (TLS) veya 465 (SSL)</span>
                                        <span class="block text-amber-600 dark:text-amber-400">⚠️ Uygulama şifresi gerekli</span>
                                    </p>
                                </div>
                                
                                <!-- Outlook -->
                                <div class="p-4 rounded-lg border border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/5">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-xl">📬</span>
                                        <h3 class="text-gray-900 dark:text-white font-medium">Outlook / Microsoft 365</h3>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 text-xs space-y-1">
                                        <span class="block">Sunucu: smtp.office365.com</span>
                                        <span class="block">Port: 587 (TLS)</span>
                                    </p>
                                </div>
                                
                                <!-- Yahoo -->
                                <div class="p-4 rounded-lg border border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/5">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-xl">📪</span>
                                        <h3 class="text-gray-900 dark:text-white font-medium">Yahoo</h3>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 text-xs space-y-1">
                                        <span class="block">Sunucu: smtp.mail.yahoo.com</span>
                                        <span class="block">Port: 587 (TLS) veya 465 (SSL)</span>
                                    </p>
                                </div>
                                
                                <!-- SendGrid -->
                                <div class="p-4 rounded-lg border border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/5">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-xl">🚀</span>
                                        <h3 class="text-gray-900 dark:text-white font-medium">SendGrid</h3>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 text-xs space-y-1">
                                        <span class="block">Sunucu: smtp.sendgrid.net</span>
                                        <span class="block">Port: 587 (TLS) veya 465 (SSL)</span>
                                        <span class="block">Kullanıcı: apikey</span>
                                    </p>
                                </div>
                                
                                <!-- Mailgun -->
                                <div class="p-4 rounded-lg border border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/5">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-xl">📨</span>
                                        <h3 class="text-gray-900 dark:text-white font-medium">Mailgun</h3>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 text-xs space-y-1">
                                        <span class="block">Sunucu: smtp.mailgun.org</span>
                                        <span class="block">Port: 587 (TLS) veya 465 (SSL)</span>
                                    </p>
                                </div>
                                
                                <!-- Yandex -->
                                <div class="p-4 rounded-lg border border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/5">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-xl">🔴</span>
                                        <h3 class="text-gray-900 dark:text-white font-medium">Yandex</h3>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 text-xs space-y-1">
                                        <span class="block">Sunucu: smtp.yandex.com</span>
                                        <span class="block">Port: 465 (SSL)</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
                </main>
            </div>
        </div>
    </div>

    <script>
        // Şifre görünürlüğünü değiştir
        function togglePasswordVisibility() {
            const input = document.getElementById('smtp_password');
            const icon = document.getElementById('password-toggle-icon');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility';
            }
        }
        
        // SMTP bağlantı testi
        async function testSMTPConnection() {
            const resultDiv = document.getElementById('test-result');
            resultDiv.classList.remove('hidden');
            resultDiv.innerHTML = `
                <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-blue-800 dark:text-blue-200 text-sm font-medium">Bağlantı test ediliyor...</span>
                    </div>
                </div>
            `;
            
            const formData = new FormData(document.getElementById('smtp-form'));
            
            try {
                const response = await fetch('<?php echo admin_url('test_smtp_connection'); ?>', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-green-600 dark:text-green-400">check_circle</span>
                                <span class="text-green-800 dark:text-green-200 text-sm font-medium">${data.message}</span>
                            </div>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-red-600 dark:text-red-400">error</span>
                                <span class="text-red-800 dark:text-red-200 text-sm font-medium">${data.message}</span>
                            </div>
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-red-600 dark:text-red-400">error</span>
                            <span class="text-red-800 dark:text-red-200 text-sm font-medium">Bağlantı testi sırasında bir hata oluştu.</span>
                        </div>
                    </div>
                `;
            }
        }
        
        // Test e-postası gönder
        async function sendTestEmail() {
            const testEmailInput = document.getElementById('test_email');
            const testEmail = testEmailInput ? testEmailInput.value.trim() : '';
            const resultDiv = document.getElementById('test-result');
            
            if (!testEmail) {
                resultDiv.classList.remove('hidden');
                resultDiv.innerHTML = `
                    <div class="p-4 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-600 dark:text-amber-400">warning</span>
                            <span class="text-amber-800 dark:text-amber-200 text-sm font-medium">Lütfen bir e-posta adresi girin.</span>
                        </div>
                    </div>
                `;
                return;
            }
            
            resultDiv.classList.remove('hidden');
            resultDiv.innerHTML = `
                <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-blue-800 dark:text-blue-200 text-sm font-medium">Test e-postası gönderiliyor...</span>
                    </div>
                </div>
            `;
            
            // Form verilerini al ve test_email'i ekle
            const smtpForm = document.getElementById('smtp-form');
            const formData = new FormData(smtpForm);
            
            // Test email'i açıkça ekle
            formData.set('test_email', testEmail);
            
            // Debug: Form verilerini kontrol et
            console.log('Sending test email to:', testEmail);
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + (pair[0] === 'smtp_password' ? '***' : pair[1]));
            }
            
            try {
                const response = await fetch('<?php echo admin_url('send_test_email'); ?>', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-green-600 dark:text-green-400">check_circle</span>
                                <span class="text-green-800 dark:text-green-200 text-sm font-medium">${data.message}</span>
                            </div>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-red-600 dark:text-red-400">error</span>
                                <span class="text-red-800 dark:text-red-200 text-sm font-medium">${data.message}</span>
                            </div>
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-red-600 dark:text-red-400">error</span>
                            <span class="text-red-800 dark:text-red-200 text-sm font-medium">E-posta gönderilirken bir hata oluştu.</span>
                        </div>
                    </div>
                `;
            }
        }
    </script>
</body>
</html>

