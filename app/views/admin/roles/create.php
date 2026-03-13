<?php include __DIR__ . '/../snippets/header.php'; ?>
<?php
$assignableModules = $assignableModules ?? [];
$allowedModules = [];
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
                            <h1 class="text-gray-900 dark:text-white text-2xl sm:text-3xl font-bold tracking-tight">Yeni Rol</h1>
                            <p class="text-gray-500 dark:text-gray-400 text-sm sm:text-base">Rol adı ve erişebileceği modülleri belirleyin.</p>
                        </div>
                        <a href="<?php echo admin_url('users', ['tab' => 'roles']); ?>" class="flex items-center gap-2 px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors min-h-[44px] w-full sm:w-auto justify-center sm:justify-start">
                            <span class="material-symbols-outlined text-lg sm:text-xl">arrow_back</span>
                            <span class="text-sm font-medium">Geri Dön</span>
                        </a>
                    </header>
                    <section class="rounded-xl border border-gray-200 dark:border-white/10 p-4 sm:p-6 bg-white dark:bg-gray-800">
                        <form method="POST" action="<?php echo admin_url('roles/store'); ?>" class="space-y-6">
                            <div class="flex flex-col gap-2">
                                <label for="name" class="text-gray-700 dark:text-gray-300 text-sm font-medium">Rol adı <span class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name" required placeholder="Örn: Editör" class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary">
                            </div>
                            <div class="flex flex-col gap-2">
                                <label for="slug" class="text-gray-700 dark:text-gray-300 text-sm font-medium">Slug</label>
                                <input type="text" id="slug" name="slug" placeholder="editor (boş bırakırsanız otomatik oluşturulur)" class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary">
                            </div>
                            <div class="flex flex-col gap-2">
                                <label for="description" class="text-gray-700 dark:text-gray-300 text-sm font-medium">Açıklama</label>
                                <textarea id="description" name="description" rows="2" placeholder="Opsiyonel" class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                            </div>
                            <div class="flex flex-col gap-3">
                                <p class="text-gray-700 dark:text-gray-300 text-sm font-medium">Erişilebilir modüller</p>
                                <?php include __DIR__ . '/partials/module-checkboxes.php'; ?>
                            </div>
                            <div class="flex gap-3 pt-2">
                                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">Kaydet</button>
                                <a href="<?php echo admin_url('users', ['tab' => 'roles']); ?>" class="px-4 py-2 border border-gray-200 dark:border-white/10 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5">İptal</a>
                            </div>
                        </form>
                    </section>
                </div>
            </main>
        </div>
    </div>
</div>
</body>
</html>
