<?php include __DIR__ . '/../snippets/header.php'; ?>
<?php
// Controller'dan gelen değişkenler: $user, $users, $roles, $activeTab, $isAdmin, $message, $messageType
$activeTab = $activeTab ?? 'users';
$isAdmin = $isAdmin ?? false;
?>
<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php 
        $currentPage = 'users';
        include __DIR__ . '/../snippets/sidebar.php'; 
        ?>

        <!-- Content Area with Header -->
        <div class="flex-1 flex flex-col lg:ml-64">
            <!-- Top Header -->
            <?php include __DIR__ . '/../snippets/top-header.php'; ?>

            <!-- Main Content -->
            <main class="flex-1 p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b] overflow-y-auto">
            <div class="layout-content-container flex flex-col w-full mx-auto max-w-7xl">
                
                <!-- Header -->
                <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div class="flex flex-col gap-2">
                        <h1 class="text-gray-900 dark:text-white text-2xl sm:text-3xl font-bold tracking-tight">Kullanıcı ve Rol Yönetimi</h1>
                        <p class="text-gray-500 dark:text-gray-400 text-sm sm:text-base">Kullanıcılarınızı ve rollerini yönetin.</p>
                    </div>
                    <?php if ($activeTab === 'users'): ?>
                    <a href="<?php echo admin_url('users/create'); ?>" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors min-h-[44px] w-full sm:w-auto justify-center sm:justify-start">
                        <span class="material-symbols-outlined text-lg sm:text-xl">add</span>
                        <span class="text-sm font-medium">Yeni Kullanıcı</span>
                    </a>
                    <?php elseif ($activeTab === 'roles' && $isAdmin): ?>
                    <a href="<?php echo admin_url('roles/create'); ?>" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors min-h-[44px] w-full sm:w-auto justify-center sm:justify-start">
                        <span class="material-symbols-outlined text-lg sm:text-xl">add</span>
                        <span class="text-sm font-medium">Yeni Rol</span>
                    </a>
                    <?php endif; ?>
                </header>

                <!-- Tabs -->
                <div class="mb-6 border-b border-gray-200 dark:border-white/10 overflow-x-auto scrollbar-hide">
                    <nav class="flex gap-2 min-w-max">
                        <a href="<?php echo admin_url('users', ['tab' => 'users']); ?>" class="px-4 py-2 text-sm font-medium border-b-2 transition-colors whitespace-nowrap <?php echo $activeTab === 'users' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700'; ?>">
                            Kullanıcılar
                        </a>
                        <?php if ($isAdmin): ?>
                        <a href="<?php echo admin_url('users', ['tab' => 'roles']); ?>" class="px-4 py-2 text-sm font-medium border-b-2 transition-colors whitespace-nowrap <?php echo $activeTab === 'roles' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700'; ?>">
                            Roller
                        </a>
                        <?php endif; ?>
                    </nav>
                </div>

                <!-- Mesaj -->
                <?php if (!empty($message)): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 text-red-800 dark:text-red-200'; ?>">
                    <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
                </div>
                <?php endif; ?>

                <?php if ($activeTab === 'users'): ?>
                <!-- Kullanıcılar Listesi -->
                <section class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 overflow-hidden">
                    <?php if (empty($users)): ?>
                    <div class="p-8 sm:p-12 text-center">
                        <span class="material-symbols-outlined text-gray-400 text-5xl sm:text-6xl mb-4">people</span>
                        <p class="text-gray-500 text-base sm:text-lg mb-2">Henüz kullanıcı yok</p>
                        <a href="<?php echo admin_url('users/create'); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 min-h-[44px]">
                            <span class="material-symbols-outlined">add</span>
                            <span>İlk Kullanıcıyı Oluştur</span>
                        </a>
                    </div>
                    <?php else: ?>
                    <!-- Desktop Table View -->
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Kullanıcı</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">E-posta</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Rol</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Durum</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                <?php foreach ($users as $u): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="text-gray-900 dark:text-white font-medium"><?php echo esc_html($u['username']); ?></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-gray-600 dark:text-gray-400 text-sm"><?php echo esc_html($u['email']); ?></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                        $roleColors = [
                                            'super_admin' => 'bg-red-100 text-red-800',
                                            'admin' => 'bg-purple-100 text-purple-800',
                                            'editor' => 'bg-blue-100 text-blue-800',
                                            'author' => 'bg-green-100 text-green-800',
                                            'user' => 'bg-gray-100 text-gray-800',
                                            'subscriber' => 'bg-gray-100 text-gray-800'
                                        ];
                                        $uRole = $u['role'] ?? 'user';
                                        $roleColor = $roleColors[$uRole] ?? $roleColors['user'];
                                        $roleName = get_role_name($uRole);
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $roleColor; ?>">
                                            <?php echo esc_html($roleName); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($u['status'] === 'active'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                                        <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Pasif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="<?php echo admin_url('users/edit/' . $u['id']); ?>" class="p-2 text-gray-600 hover:text-primary hover:bg-primary/10 rounded-lg min-h-[36px] min-w-[36px] flex items-center justify-center" title="Düzenle">
                                                <span class="material-symbols-outlined text-xl">edit</span>
                                            </a>
                                            <a href="<?php echo admin_url('users/toggle/' . $u['id']); ?>" class="p-2 text-gray-600 hover:text-primary hover:bg-primary/10 rounded-lg min-h-[36px] min-w-[36px] flex items-center justify-center" title="Durum Değiştir">
                                                <span class="material-symbols-outlined text-xl"><?php echo $u['status'] === 'active' ? 'visibility_off' : 'visibility'; ?></span>
                                            </a>
                                            <a href="<?php echo admin_url('users/delete/' . $u['id']); ?>" class="p-2 text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg min-h-[36px] min-w-[36px] flex items-center justify-center" onclick="return confirm('Silmek istediğinize emin misiniz?');" title="Sil">
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
                    <div class="lg:hidden p-4 space-y-4">
                        <?php foreach ($users as $u): ?>
                        <?php
                        $roleColors = [
                            'super_admin' => 'bg-red-100 text-red-800',
                            'admin' => 'bg-purple-100 text-purple-800',
                            'editor' => 'bg-blue-100 text-blue-800',
                            'author' => 'bg-green-100 text-green-800',
                            'user' => 'bg-gray-100 text-gray-800',
                            'subscriber' => 'bg-gray-100 text-gray-800'
                        ];
                        $uRole = $u['role'] ?? 'user';
                        $roleColor = $roleColors[$uRole] ?? $roleColors['user'];
                        $roleName = get_role_name($uRole);
                        ?>
                        <div class="bg-gray-50 dark:bg-white/5 rounded-lg border border-gray-200 dark:border-white/10 p-4 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white line-clamp-2"><?php echo esc_html($u['username']); ?></h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 break-all"><?php echo esc_html($u['email']); ?></p>
                                </div>
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    <a href="<?php echo admin_url('users/edit/' . $u['id']); ?>" class="p-2 text-gray-600 hover:text-primary hover:bg-primary/10 rounded-lg min-h-[36px] min-w-[36px] flex items-center justify-center" title="Düzenle">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </a>
                                    <a href="<?php echo admin_url('users/toggle/' . $u['id']); ?>" class="p-2 text-gray-600 hover:text-primary hover:bg-primary/10 rounded-lg min-h-[36px] min-w-[36px] flex items-center justify-center" title="Durum Değiştir">
                                        <span class="material-symbols-outlined text-lg"><?php echo $u['status'] === 'active' ? 'visibility_off' : 'visibility'; ?></span>
                                    </a>
                                    <a href="<?php echo admin_url('users/delete/' . $u['id']); ?>" class="p-2 text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg min-h-[36px] min-w-[36px] flex items-center justify-center" onclick="return confirm('Silmek istediğinize emin misiniz?');" title="Sil">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </a>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $roleColor; ?>">
                                    <?php echo esc_html($roleName); ?>
                                </span>
                                <?php if ($u['status'] === 'active'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                                <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Pasif</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </section>

                <?php elseif ($activeTab === 'roles' && $isAdmin): ?>
                <!-- Roller Listesi -->
                <section class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 overflow-hidden">
                    <?php if (empty($roles)): ?>
                    <div class="p-8 sm:p-12 text-center">
                        <span class="material-symbols-outlined text-gray-400 text-5xl sm:text-6xl mb-4">admin_panel_settings</span>
                        <p class="text-gray-500 text-base sm:text-lg mb-2">Henüz rol tanımlanmamış</p>
                        <a href="<?php echo admin_url('roles/create'); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 min-h-[44px]">
                            <span class="material-symbols-outlined">add</span>
                            <span>İlk Rolü Oluştur</span>
                        </a>
                    </div>
                    <?php else: ?>
                    <!-- Desktop Table View -->
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Rol Adı</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Slug</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Açıklama</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Kullanıcılar</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                <?php foreach ($roles as $r): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <p class="text-gray-900 dark:text-white font-medium"><?php echo esc_html($r['name']); ?></p>
                                            <?php if (!empty($r['is_system'])): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Sistem</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <code class="text-gray-600 text-sm bg-gray-100 px-2 py-1 rounded"><?php echo esc_html($r['slug']); ?></code>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-gray-600 text-sm"><?php echo esc_html($r['description'] ?? '-'); ?></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-gray-600 text-sm"><?php echo esc_html($r['user_count'] ?? 0); ?> kullanıcı</p>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="<?php echo admin_url('roles/edit/' . $r['id']); ?>" class="p-2 text-gray-600 hover:text-primary hover:bg-primary/10 rounded-lg min-h-[36px] min-w-[36px] flex items-center justify-center" title="Düzenle">
                                                <span class="material-symbols-outlined text-xl">edit</span>
                                            </a>
                                            <?php if (empty($r['is_system'])): ?>
                                            <a href="<?php echo admin_url('roles/delete/' . $r['id']); ?>" class="p-2 text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg min-h-[36px] min-w-[36px] flex items-center justify-center" onclick="return confirm('Silmek istediğinize emin misiniz?');" title="Sil">
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
                    
                    <!-- Mobile Card View -->
                    <div class="lg:hidden p-4 space-y-4">
                        <?php foreach ($roles as $r): ?>
                        <div class="bg-gray-50 dark:bg-white/5 rounded-lg border border-gray-200 dark:border-white/10 p-4 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap mb-1">
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white line-clamp-2"><?php echo esc_html($r['name']); ?></h3>
                                        <?php if (!empty($r['is_system'])): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 flex-shrink-0">Sistem</span>
                                        <?php endif; ?>
                                    </div>
                                    <code class="text-xs text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-white/10 px-2 py-1 rounded break-all"><?php echo esc_html($r['slug']); ?></code>
                                </div>
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    <a href="<?php echo admin_url('roles/edit/' . $r['id']); ?>" class="p-2 text-gray-600 hover:text-primary hover:bg-primary/10 rounded-lg min-h-[36px] min-w-[36px] flex items-center justify-center" title="Düzenle">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </a>
                                    <?php if (empty($r['is_system'])): ?>
                                    <a href="<?php echo admin_url('roles/delete/' . $r['id']); ?>" class="p-2 text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg min-h-[36px] min-w-[36px] flex items-center justify-center" onclick="return confirm('Silmek istediğinize emin misiniz?');" title="Sil">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($r['description'])): ?>
                            <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2"><?php echo esc_html($r['description']); ?></p>
                            <?php endif; ?>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                <span class="font-medium"><?php echo esc_html($r['user_count'] ?? 0); ?></span> kullanıcı
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </section>
                <?php endif; ?>

            </div>
            </main>
        </div>
    </div>
</div>
</body>
</html>
