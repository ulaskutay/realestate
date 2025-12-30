<?php include __DIR__ . '/../snippets/header.php'; ?>
    <div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
        <div class="flex min-h-screen">
            <!-- SideNavBar -->
            <?php 
            $currentPage = 'roles';
            include __DIR__ . '/../snippets/sidebar.php'; 
            ?>

            <!-- Main Content -->
            <main class="main-content-with-sidebar flex-1 p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b]">
                <div class="layout-content-container flex flex-col w-full mx-auto max-w-7xl">
                    <!-- PageHeading -->
                    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <div class="flex flex-col gap-2">
                            <p class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Rol Düzenle</p>
                            <p class="text-gray-500 dark:text-gray-400 text-base font-normal leading-normal">Rol bilgilerini ve yetkilerini güncelleyin.</p>
                        </div>
                        <a href="<?php echo admin_url('roles'); ?>" class="flex items-center gap-2 px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                            <span class="material-symbols-outlined text-xl">arrow_back</span>
                            <span class="text-sm font-medium">Geri Dön</span>
                        </a>
                    </header>

                    <!-- Success/Error Message -->
                    <?php if (isset($message) && $message): ?>
                        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200'; ?>">
                            <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <section class="rounded-xl border border-gray-200 dark:border-white/10 p-6 bg-background-light dark:bg-background-dark">
                        <form method="POST" action="<?php echo admin_url('roles/update/' . $role['id']); ?>" class="space-y-6">
                            <!-- Rol Adı -->
                            <div class="flex flex-col gap-2">
                                <label for="name" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Rol Adı <span class="text-red-500">*</span></label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    required
                                    value="<?php echo esc_attr($role['name'] ?? ''); ?>"
                                    placeholder="Örn: İçerik Editörü"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                />
                                <p class="text-gray-500 dark:text-gray-400 text-xs">Rolün görünen adı.</p>
                            </div>

                            <!-- Slug -->
                            <div class="flex flex-col gap-2">
                                <label for="slug" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Slug</label>
                                <input 
                                    type="text" 
                                    id="slug" 
                                    name="slug" 
                                    value="<?php echo esc_attr($role['slug'] ?? ''); ?>"
                                    <?php echo (!empty($role['is_system']) && $role['is_system'] == 1) ? 'readonly' : ''; ?>
                                    class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent <?php echo (!empty($role['is_system']) && $role['is_system'] == 1) ? 'bg-gray-100 dark:bg-gray-800 cursor-not-allowed' : ''; ?>"
                                />
                                <p class="text-gray-500 dark:text-gray-400 text-xs">
                                    <?php if (!empty($role['is_system']) && $role['is_system'] == 1): ?>
                                        Sistem rolleri için slug değiştirilemez.
                                    <?php else: ?>
                                        Rolün benzersiz tanımlayıcısı.
                                    <?php endif; ?>
                                </p>
                            </div>

                            <!-- Açıklama -->
                            <div class="flex flex-col gap-2">
                                <label for="description" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Açıklama</label>
                                <textarea 
                                    id="description" 
                                    name="description" 
                                    rows="3"
                                    placeholder="Bu rolün ne için kullanıldığını açıklayın..."
                                    class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                ><?php echo esc_html($role['description'] ?? ''); ?></textarea>
                            </div>

                            <!-- Modül Yetkileri -->
                            <div class="border-t border-gray-200 dark:border-white/10 pt-6">
                                <h3 class="text-gray-900 dark:text-white text-lg font-semibold mb-4">Modül Yetkileri</h3>
                                <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">Bu rolün hangi modüllere erişebileceğini seçin.</p>
                                
                                <div class="space-y-6">
                                    <?php 
                                    $rolePermissions = $role['permissions'] ?? [];
                                    foreach ($modules as $module): 
                                        $modulePerms = array_filter($module['permissions'] ?? [], function($perm) use ($rolePermissions) {
                                            return in_array($perm['permission'], $rolePermissions);
                                        });
                                        $hasModuleAccess = !empty($modulePerms);
                                    ?>
                                        <div class="border border-gray-200 dark:border-white/10 rounded-lg p-4">
                                            <div class="flex items-center gap-3 mb-3">
                                                <span class="material-symbols-outlined text-primary text-2xl"><?php echo esc_attr($module['icon'] ?? 'extension'); ?></span>
                                                <div class="flex-1">
                                                    <h4 class="text-gray-900 dark:text-white font-semibold"><?php echo esc_html($module['label']); ?></h4>
                                                    <?php if (!empty($module['description'])): ?>
                                                        <p class="text-gray-500 dark:text-gray-400 text-xs"><?php echo esc_html($module['description']); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input 
                                                        type="checkbox" 
                                                        class="module-toggle sr-only peer" 
                                                        data-module="<?php echo esc_attr($module['slug']); ?>"
                                                        <?php echo $hasModuleAccess ? 'checked' : ''; ?>
                                                        onchange="toggleModulePermissions(this, '<?php echo esc_attr($module['slug']); ?>')"
                                                    />
                                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 dark:peer-focus:ring-primary/40 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                                                </label>
                                            </div>
                                            
                                            <?php if (!empty($module['permissions'])): ?>
                                                <div class="ml-11 space-y-2 module-permissions" id="permissions-<?php echo esc_attr($module['slug']); ?>" style="display: <?php echo $hasModuleAccess ? 'block' : 'none'; ?>;">
                                                    <?php foreach ($module['permissions'] as $permission): ?>
                                                        <label class="flex items-center gap-2 cursor-pointer">
                                                            <input 
                                                                type="checkbox" 
                                                                name="permissions[]" 
                                                                value="<?php echo esc_attr($permission['permission']); ?>"
                                                                <?php echo in_array($permission['permission'], $rolePermissions) ? 'checked' : ''; ?>
                                                                class="w-4 h-4 text-primary bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary focus:ring-2"
                                                            />
                                                            <span class="text-gray-700 dark:text-gray-300 text-sm"><?php echo esc_html($permission['label']); ?></span>
                                                            <?php if (!empty($permission['description'])): ?>
                                                                <span class="text-gray-500 dark:text-gray-400 text-xs">- <?php echo esc_html($permission['description']); ?></span>
                                                            <?php endif; ?>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Butonlar -->
                            <div class="flex justify-end gap-4 pt-4 border-t border-gray-200 dark:border-white/10">
                                <a href="<?php echo admin_url('roles'); ?>" class="px-6 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors font-medium">
                                    İptal
                                </a>
                                <button 
                                    type="submit" 
                                    class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors font-medium"
                                >
                                    Güncelle
                                </button>
                            </div>
                        </form>
                    </section>
                </div>
            </main>
        </div>
    </div>

    <script>
        function toggleModulePermissions(checkbox, moduleSlug) {
            const permissionsDiv = document.getElementById('permissions-' + moduleSlug);
            
            // Eğer permissions div'i yoksa (modülün yetkileri yoksa) işlem yapma
            if (!permissionsDiv) {
                return;
            }
            
            if (checkbox.checked) {
                permissionsDiv.style.display = 'block';
                // Switcher açıldığında, eğer hiçbir yetki seçilmemişse tümünü seç
                const permissionCheckboxes = permissionsDiv.querySelectorAll('input[type="checkbox"]');
                const hasChecked = Array.from(permissionCheckboxes).some(perm => perm.checked);
                if (!hasChecked) {
                    // Hiçbir yetki seçilmemişse tümünü seç
                    permissionCheckboxes.forEach(perm => {
                        perm.checked = true;
                    });
                }
            } else {
                permissionsDiv.style.display = 'none';
                // Tüm yetkileri kaldır
                const permissionCheckboxes = permissionsDiv.querySelectorAll('input[type="checkbox"]');
                permissionCheckboxes.forEach(perm => {
                    perm.checked = false;
                });
            }
        }
        
        // Form submit edilmeden önce switcher durumuna göre yetkileri kontrol et
        document.querySelector('form').addEventListener('submit', function(e) {
            // Tüm modül switcher'larını kontrol et
            document.querySelectorAll('.module-toggle').forEach(function(switcher) {
                const moduleSlug = switcher.getAttribute('data-module');
                const permissionsDiv = document.getElementById('permissions-' + moduleSlug);
                
                if (!permissionsDiv) {
                    return;
                }
                
                // Switcher kapalıysa, tüm yetkileri unchecked yap (zaten yapılmış olabilir ama emin olmak için)
                if (!switcher.checked) {
                    const permissionCheckboxes = permissionsDiv.querySelectorAll('input[type="checkbox"]');
                    permissionCheckboxes.forEach(perm => {
                        perm.checked = false;
                    });
                }
            });
        });
    </script>
</body>
</html>

