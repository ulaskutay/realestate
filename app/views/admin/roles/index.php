<?php include __DIR__ . '/../snippets/header.php'; ?>
    <div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
        <div class="flex min-h-screen">
            <!-- SideNavBar -->
            <?php 
            $currentPage = 'roles';
            include __DIR__ . '/../snippets/sidebar.php'; 
            ?>

            <!-- Content Area with Header -->
            <div class="flex-1 flex flex-col lg:ml-64">
                <!-- Top Header -->
                <?php include __DIR__ . '/../snippets/top-header.php'; ?>

                <!-- Main Content -->
                <main class="flex-1 p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b] overflow-y-auto">
                <div class="layout-content-container flex flex-col w-full mx-auto max-w-7xl">
                    <!-- PageHeading -->
                    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <div class="flex flex-col gap-2">
                            <p class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Rol Yönetimi</p>
                            <p class="text-gray-500 dark:text-gray-400 text-base font-normal leading-normal">Rolleri oluşturun, düzenleyin ve yetkilerini yönetin.</p>
                        </div>
                        <a href="<?php echo admin_url('roles/create'); ?>" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                            <span class="material-symbols-outlined text-xl">add</span>
                            <span class="text-sm font-medium">Yeni Rol</span>
                        </a>
                    </header>

                    <!-- Success/Error Message -->
                    <?php if (isset($message) && $message): ?>
                        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200'; ?>">
                            <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Roles List -->
                    <section class="rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark overflow-hidden">
                        <?php if (empty($roles)): ?>
                            <div class="p-12 text-center">
                                <span class="material-symbols-outlined text-gray-400 dark:text-gray-600 text-6xl mb-4">admin_panel_settings</span>
                                <p class="text-gray-500 dark:text-gray-400 text-lg mb-2">Henüz rol oluşturulmamış</p>
                                <p class="text-gray-400 dark:text-gray-500 text-sm mb-6">Yeni bir rol oluşturmak için yukarıdaki butona tıklayın.</p>
                                <a href="<?php echo admin_url('roles/create'); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                    <span class="material-symbols-outlined">add</span>
                                    <span>İlk Rolü Oluştur</span>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Rol Adı</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Slug</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Açıklama</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Kullanıcılar</th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Durum</th>
                                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                        <?php foreach ($roles as $role): ?>
                                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center gap-2">
                                                        <p class="text-gray-900 dark:text-white font-medium"><?php echo esc_html($role['name']); ?></p>
                                                        <?php if (!empty($role['is_system']) && $role['is_system'] == 1): ?>
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300" title="Sistem Rolü">Sistem</span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($role['is_default']) && $role['is_default'] == 1): ?>
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300" title="Varsayılan Rol">Varsayılan</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <code class="text-gray-600 dark:text-gray-400 text-sm bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded"><?php echo esc_html($role['slug']); ?></code>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <p class="text-gray-600 dark:text-gray-400 text-sm"><?php echo esc_html($role['description'] ?? '-'); ?></p>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <p class="text-gray-600 dark:text-gray-400 text-sm"><?php echo esc_html($role['user_count'] ?? 0); ?> kullanıcı</p>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <?php if (!empty($role['is_system']) && $role['is_system'] == 1): ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                                            Sistem Rolü
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-300">
                                                            Özel Rol
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <a href="<?php echo admin_url('roles/edit/' . $role['id']); ?>" class="p-2 text-gray-600 dark:text-gray-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Düzenle">
                                                            <span class="material-symbols-outlined text-xl">edit</span>
                                                        </a>
                                                        <?php if (empty($role['is_system']) || $role['is_system'] == 0): ?>
                                                            <a href="<?php echo admin_url('roles/delete/' . $role['id']); ?>" class="p-2 text-gray-600 dark:text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" onclick="return confirm('Bu rolü silmek istediğinizden emin misiniz? Bu rolü kullanan kullanıcılar varsayılan role atanacaktır.');" title="Sil">
                                                                <span class="material-symbols-outlined text-xl">delete</span>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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

