<!DOCTYPE html>
<html class="dark" lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo isset($title) ? esc_html($title) : 'Yeni Kullanıcı Oluştur'; ?></title>
    
    <!-- Tailwind CSS -->
    <script src="<?php echo ViewRenderer::assetUrl('assets/js/tailwind-admin.min.js'); ?>"></script>
    
    <!-- Local Fonts -->
    <link rel="stylesheet" href="<?php echo ViewRenderer::assetUrl('assets/css/fonts.css'); ?>">
    
    <!-- Custom CSS -->
    <link href="<?php echo rtrim(site_url(), '/') . '/admin/css/admin-dashboard.css'; ?>" rel="stylesheet"/>
    
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
            $currentPage = 'users';
            include __DIR__ . '/../snippets/sidebar.php'; 
            ?>

            <!-- Main Content -->
            <main class="main-content-with-sidebar flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b]">
                <div class="layout-content-container flex flex-col w-full mx-auto max-w-7xl">
                    <!-- PageHeading -->
                    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <div class="flex flex-col gap-2">
                            <p class="text-gray-900 dark:text-white text-2xl sm:text-3xl font-bold tracking-tight">Yeni Kullanıcı Oluştur</p>
                            <p class="text-gray-500 dark:text-gray-400 text-sm sm:text-base font-normal leading-normal">Yeni bir kullanıcı oluşturun ve yetkilerini belirleyin.</p>
                        </div>
                        <a href="<?php echo admin_url('users'); ?>" class="flex items-center gap-2 px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors min-h-[44px] w-full sm:w-auto justify-center sm:justify-start">
                            <span class="material-symbols-outlined text-lg sm:text-xl">arrow_back</span>
                            <span class="text-sm font-medium">Geri Dön</span>
                        </a>
                    </header>

                    <!-- Form -->
                    <section class="rounded-xl border border-gray-200 dark:border-white/10 p-4 sm:p-6 bg-background-light dark:bg-background-dark">
                        <form method="POST" action="<?php echo admin_url('users/store'); ?>" class="space-y-6">
                            <!-- Kullanıcı Adı -->
                            <div class="flex flex-col gap-2">
                                <label for="username" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Kullanıcı Adı <span class="text-red-500">*</span></label>
                                <input 
                                    type="text" 
                                    id="username" 
                                    name="username" 
                                    required
                                    placeholder="kullanici_adi"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-base"
                                />
                                <p class="text-gray-500 dark:text-gray-400 text-xs">Kullanıcının giriş yaparken kullanacağı kullanıcı adı.</p>
                            </div>

                            <!-- E-posta -->
                            <div class="flex flex-col gap-2">
                                <label for="email" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">E-posta Adresi <span class="text-red-500">*</span></label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    required
                                    placeholder="ornek@email.com"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-base"
                                />
                                <p class="text-gray-500 dark:text-gray-400 text-xs">Kullanıcının e-posta adresi.</p>
                            </div>

                            <!-- Şifre -->
                            <div class="flex flex-col gap-2">
                                <label for="password" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Şifre <span class="text-red-500">*</span></label>
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    required
                                    placeholder="••••••••"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-base"
                                />
                                <p class="text-gray-500 dark:text-gray-400 text-xs">Güçlü bir şifre belirleyin (en az 8 karakter).</p>
                            </div>

                            <!-- Rol -->
                            <div class="flex flex-col gap-2">
                                <label for="role" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Rol</label>
                                <?php
                                require_once __DIR__ . '/../../../../core/Role.php';
                                $currentUser = get_logged_in_user();
                                $allRoles = Role::getAll();
                                $availableRoles = [];
                                
                                // Kullanıcının yetkisine göre rolleri filtrele
                                foreach ($allRoles as $roleKey => $roleData) {
                                    // Süper admin tüm rolleri görebilir
                                    if ($currentUser['role'] === 'super_admin') {
                                        $availableRoles[$roleKey] = $roleData;
                                    } else {
                                        // Diğer kullanıcılar sadece kendi seviyelerinden düşük rolleri görebilir
                                        if (Role::isLowerThan($roleKey, $currentUser['role'])) {
                                            $availableRoles[$roleKey] = $roleData;
                                        }
                                    }
                                }
                                ?>
                                <select 
                                    id="role" 
                                    name="role"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-base"
                                >
                                    <?php foreach ($availableRoles as $roleKey => $roleData): ?>
                                        <option value="<?php echo esc_attr($roleKey); ?>">
                                            <?php echo esc_html($roleData['name']); ?> - <?php echo esc_html($roleData['description']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="text-gray-500 dark:text-gray-400 text-xs">Kullanıcının yetki seviyesini belirleyin. Sadece kendi seviyenizden düşük rolleri seçebilirsiniz.</p>
                            </div>

                            <!-- Durum -->
                            <div class="flex items-center gap-4">
                                <input 
                                    type="checkbox" 
                                    id="status" 
                                    name="status" 
                                    value="active"
                                    checked
                                    class="w-4 h-4 text-primary bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary focus:ring-2"
                                />
                                <label for="status" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Hemen Aktifleştir</label>
                            </div>

                            <!-- Butonlar -->
                            <div class="flex flex-col sm:flex-row justify-end gap-3 sm:gap-4 pt-4 border-t border-gray-200 dark:border-white/10">
                                <a href="<?php echo admin_url('users'); ?>" class="px-6 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors font-medium min-h-[44px] flex items-center justify-center">
                                    İptal
                                </a>
                                <button 
                                    type="submit" 
                                    class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors font-medium min-h-[44px]"
                                >
                                    Kullanıcı Oluştur
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

