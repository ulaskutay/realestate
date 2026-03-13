<?php include __DIR__ . '/../snippets/header.php'; ?>
<?php
$role = $role ?? null;
$assignableModules = $assignableModules ?? [];
$allowedModules = [];
if ($role && !empty($role['allowed_modules'])) {
    $allowedModules = is_array($role['allowed_modules']) ? $role['allowed_modules'] : [];
}
?>
<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <?php $currentPage = 'roles'; include __DIR__ . '/../snippets/sidebar.php'; ?>
        <div class="flex-1 flex flex-col lg:ml-64">
            <?php include __DIR__ . '/../snippets/top-header.php'; ?>
            <main class="flex-1 p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b] overflow-y-auto">
                <div class="layout-content-container flex flex-col w-full mx-auto max-w-7xl">
                    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <div class="flex flex-col gap-2">
                            <h1 class="text-gray-900 dark:text-white text-2xl sm:text-3xl font-bold tracking-tight">Rol Düzenle</h1>
                            <p class="text-gray-500 dark:text-gray-400 text-sm sm:text-base">Rol bilgileri ve erişebileceği modülleri güncelleyin.</p>
                        </div>
                        <a href="<?php echo admin_url('users', ['tab' => 'roles']); ?>" class="flex items-center gap-2 px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors min-h-[44px] w-full sm:w-auto justify-center sm:justify-start">
                            <span class="material-symbols-outlined text-lg sm:text-xl">arrow_back</span>
                            <span class="text-sm font-medium">Geri Dön</span>
                        </a>
                    </header>
                    <?php if (!$role): ?>
                    <p class="text-red-600 dark:text-red-400">Rol bulunamadı.</p>
                    <?php else: ?>
                    <section class="rounded-xl border border-gray-200 dark:border-white/10 p-4 sm:p-6 bg-white dark:bg-gray-800">
                        <form method="POST" action="<?php echo admin_url('roles/update/' . $role['id']); ?>" class="space-y-6">
                            <div class="flex flex-col gap-2">
                                <label for="name" class="text-gray-700 dark:text-gray-300 text-sm font-medium">Rol adı <span class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name" required value="<?php echo esc_attr($role['name'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary">
                            </div>
                            <div class="flex flex-col gap-2">
                                <label for="slug" class="text-gray-700 dark:text-gray-300 text-sm font-medium">Slug</label>
                                <input type="text" id="slug" name="slug" value="<?php echo esc_attr($role['slug'] ?? ''); ?>" <?php echo !empty($role['is_system']) ? 'readonly class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-gray-100 dark:bg-white/5 text-gray-600 dark:text-gray-400"' : 'class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"'; ?>>
                                <?php if (!empty($role['is_system'])): ?>
                                <p class="text-gray-500 dark:text-gray-400 text-xs">Sistem rolleri için slug değiştirilemez.</p>
                                <?php endif; ?>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label for="description" class="text-gray-700 dark:text-gray-300 text-sm font-medium">Açıklama</label>
                                <textarea id="description" name="description" rows="2" class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"><?php echo esc_html($role['description'] ?? ''); ?></textarea>
                            </div>
                            <div class="flex flex-col gap-3">
                                <p class="text-gray-700 dark:text-gray-300 text-sm font-medium">Erişilebilir modüller</p>
                                <?php include __DIR__ . '/partials/module-checkboxes.php'; ?>
                            </div>
                            <div class="flex gap-3 pt-2">
                                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">Güncelle</button>
                                <a href="<?php echo admin_url('users', ['tab' => 'roles']); ?>" class="px-4 py-2 border border-gray-200 dark:border-white/10 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5">İptal</a>
                            </div>
                        </form>
                    </section>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</div>
</body>
</html>
