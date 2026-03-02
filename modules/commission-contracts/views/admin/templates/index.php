<?php
if (!isset($rootPath)) $rootPath = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
include $rootPath . '/app/views/admin/snippets/header.php';
$templates = $templates ?? [];
?>

<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <?php $currentPage = 'commission-contracts'; include $rootPath . '/app/views/admin/snippets/sidebar.php'; ?>
        <div class="flex-1 flex flex-col lg:ml-64">
            <?php include $rootPath . '/app/views/admin/snippets/top-header.php'; ?>
            <main class="flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b] overflow-y-auto">
                <div class="layout-content-container commission-contracts-content flex flex-col w-full mx-auto max-w-7xl">

                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
                        <div class="min-w-0">
                            <a href="<?php echo admin_url('module/commission-contracts'); ?>" class="text-gray-500 dark:text-white hover:text-primary transition-colors inline-flex items-center gap-2 mb-2 text-sm sm:text-base">
                                <span class="material-symbols-outlined text-lg">arrow_back</span> Sözleşmelere dön
                            </a>
                            <h1 class="text-gray-900 dark:text-white text-2xl sm:text-3xl font-bold tracking-tight"><?php echo esc_html($title ?? 'Sözleşme Şablonları'); ?></h1>
                            <p class="text-gray-500 dark:text-white text-sm sm:text-base mt-1">Sözleşme düzenini şablonlarla yönetin</p>
                        </div>
                        <a href="<?php echo admin_url('module/commission-contracts/templates/create'); ?>" class="px-4 sm:px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium flex items-center justify-center gap-2 text-sm sm:text-base w-full sm:w-auto shrink-0">
                            <span class="material-symbols-outlined text-lg sm:text-xl">add</span>
                            <span>Yeni Şablon</span>
                        </a>
                    </div>

                    <?php if (!empty($message)): ?>
                        <div class="mb-4 p-4 rounded-lg <?php echo ($messageType ?? '') === 'success' ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-300'; ?>">
                            <?php echo esc_html($message); ?>
                        </div>
                    <?php endif; ?>

                    <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-x-auto overflow-y-hidden" style="-webkit-overflow-scrolling: touch;">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" style="min-width: 320px;">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-white uppercase tracking-wider">Şablon</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-white uppercase tracking-wider">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-[#1e293b] divide-y divide-gray-200 dark:divide-gray-700">
                                <?php if (empty($templates)): ?>
                                    <tr>
                                        <td colspan="2" class="px-4 sm:px-6 py-12 text-center text-gray-500 dark:text-white">
                                            <span class="material-symbols-outlined text-4xl text-gray-300 dark:text-gray-500 block mb-2">dashboard_customize</span>
                                            <p>Henüz şablon yok.</p>
                                            <a href="<?php echo admin_url('module/commission-contracts/templates/create'); ?>" class="text-primary dark:text-white hover:underline text-sm mt-2 inline-block">İlk şablonu oluştur</a>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($templates as $t): ?>
                                        <tr>
                                            <td class="px-4 sm:px-6 py-4">
                                                <span class="text-sm font-medium text-gray-900 dark:text-white"><?php echo esc_html($t['name'] ?? ''); ?></span>
                                                <?php if (!empty($t['slug'])): ?>
                                                    <span class="block text-xs text-gray-400 dark:text-white/80 mt-0.5"><?php echo esc_html($t['slug']); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 sm:px-6 py-4 text-sm">
                                                <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                                                    <a href="<?php echo admin_url('module/commission-contracts/create', ['template_id' => (int)($t['id'] ?? 0)]); ?>" class="text-primary dark:text-white hover:text-primary/80 flex items-center gap-1 font-medium">
                                                        <span class="material-symbols-outlined text-lg">add_circle</span>
                                                        Bu şablondan oluştur
                                                    </a>
                                                    <a href="<?php echo admin_url('module/commission-contracts/templates/edit/' . (int)($t['id'] ?? 0)); ?>" class="text-gray-700 dark:text-white hover:text-primary flex items-center gap-1">
                                                        <span class="material-symbols-outlined text-lg">edit</span>
                                                        Düzenle
                                                    </a>
                                                    <form action="<?php echo admin_url('module/commission-contracts/templates/delete/' . (int)($t['id'] ?? 0)); ?>" method="POST" class="inline" onsubmit="return confirm('Bu şablonu silmek istediğinize emin misiniz?');">
                                                        <input type="hidden" name="page" value="module/commission-contracts/templates/delete/<?php echo (int)($t['id'] ?? 0); ?>" />
                                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 flex items-center gap-1">
                                                            <span class="material-symbols-outlined text-lg">delete</span>
                                                            Sil
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </main>
        </div>
    </div>
</div>
<?php include $rootPath . '/app/views/admin/snippets/footer.php'; ?>
