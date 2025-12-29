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
                            <p class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Yeni Rol Oluştur</p>
                            <p class="text-gray-500 dark:text-gray-400 text-base font-normal leading-normal">Yeni bir rol oluşturun ve yetkilerini belirleyin.</p>
                        </div>
                        <a href="<?php echo admin_url('roles'); ?>" class="flex items-center gap-2 px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                            <span class="material-symbols-outlined text-xl">arrow_back</span>
                            <span class="text-sm font-medium">Geri Dön</span>
                        </a>
                    </header>

                    <!-- Form -->
                    <section class="rounded-xl border border-gray-200 dark:border-white/10 p-6 bg-background-light dark:bg-background-dark">
                        <form method="POST" action="<?php echo admin_url('roles/store'); ?>" class="space-y-6">
                            <!-- Rol Adı -->
                            <div class="flex flex-col gap-2">
                                <label for="name" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Rol Adı <span class="text-red-500">*</span></label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    required
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
                                    placeholder="icerik-editoru (otomatik oluşturulur)"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                />
                                <p class="text-gray-500 dark:text-gray-400 text-xs">Rolün benzersiz tanımlayıcısı. Boş bırakılırsa otomatik oluşturulur.</p>
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
                                ></textarea>
                            </div>

                            <!-- Varsayılan Rol -->
                            <div class="flex items-center gap-4">
                                <input 
                                    type="checkbox" 
                                    id="is_default" 
                                    name="is_default" 
                                    value="1"
                                    class="w-4 h-4 text-primary bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary focus:ring-2"
                                />
                                <label for="is_default" class="text-gray-700 dark:text-gray-300 text-sm font-medium leading-normal">Varsayılan Rol Olarak Ayarla</label>
                                <p class="text-gray-500 dark:text-gray-400 text-xs">Yeni kullanıcılar bu role atanacaktır.</p>
                            </div>

                            <!-- Modül Yetkileri -->
                            <div class="border-t border-gray-200 dark:border-white/10 pt-6">
                                <h3 class="text-gray-900 dark:text-white text-lg font-semibold mb-4">Modül Yetkileri</h3>
                                <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">Bu rolün hangi modüllere erişebileceğini seçin.</p>
                                
                                <div class="space-y-6">
                                    <?php foreach ($modules as $module): ?>
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
                                                        onchange="toggleModulePermissions(this, '<?php echo esc_attr($module['slug']); ?>')"
                                                    />
                                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 dark:peer-focus:ring-primary/40 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                                                </label>
                                            </div>
                                            
                                            <?php if (!empty($module['permissions'])): ?>
                                                <div class="ml-11 space-y-2 module-permissions" id="permissions-<?php echo esc_attr($module['slug']); ?>" style="display: none;">
                                                    <?php foreach ($module['permissions'] as $permission): ?>
                                                        <label class="flex items-center gap-2 cursor-pointer">
                                                            <input 
                                                                type="checkbox" 
                                                                name="permissions[]" 
                                                                value="<?php echo esc_attr($permission['permission']); ?>"
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
                                    Rol Oluştur
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
            if (checkbox.checked) {
                permissionsDiv.style.display = 'block';
            } else {
                permissionsDiv.style.display = 'none';
                // Tüm yetkileri kaldır
                permissionsDiv.querySelectorAll('input[type="checkbox"]').forEach(perm => {
                    perm.checked = false;
                });
            }
        }

        // Slug otomatik oluşturma
        document.getElementById('name').addEventListener('input', function() {
            const slugInput = document.getElementById('slug');
            if (!slugInput.value) {
                const slug = this.value
                    .toLowerCase()
                    .replace(/ş/g, 's')
                    .replace(/ğ/g, 'g')
                    .replace(/ü/g, 'u')
                    .replace(/ı/g, 'i')
                    .replace(/ö/g, 'o')
                    .replace(/ç/g, 'c')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                slugInput.value = slug;
            }
        });
    </script>
</body>
</html>

