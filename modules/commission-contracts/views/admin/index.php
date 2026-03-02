<?php
if (!isset($rootPath)) $rootPath = dirname(dirname(dirname(__DIR__)));
include $rootPath . '/app/views/admin/snippets/header.php';
?>

<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <?php
        $currentPage = 'commission-contracts';
        include $rootPath . '/app/views/admin/snippets/sidebar.php';
        ?>

        <div class="flex-1 flex flex-col lg:ml-64">
            <?php include $rootPath . '/app/views/admin/snippets/top-header.php'; ?>

            <main class="flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b] overflow-y-auto">
                <div class="layout-content-container commission-contracts-content flex flex-col w-full mx-auto max-w-7xl">

                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
                        <div class="min-w-0">
                            <h1 class="text-gray-900 dark:text-white text-2xl sm:text-3xl font-bold tracking-tight"><?php echo esc_html($title ?? 'Sözleşme Modülü'); ?></h1>
                            <p class="text-gray-500 dark:text-gray-400 text-sm sm:text-base mt-1">Sözleşmeleri yönetin, PDF alın ve imza alın</p>
                        </div>
                        <div class="flex flex-wrap gap-2 sm:gap-3 shrink-0">
                            <a href="<?php echo admin_url('module/commission-contracts/templates'); ?>" class="px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 font-medium flex items-center justify-center gap-2 text-sm sm:text-base">
                                <span class="material-symbols-outlined text-lg sm:text-xl">dashboard_customize</span>
                                <span>Şablonlar</span>
                            </a>
                            <a href="<?php echo admin_url('module/commission-contracts/create'); ?>" class="px-4 sm:px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium flex items-center justify-center gap-2 text-sm sm:text-base">
                                <span class="material-symbols-outlined text-lg sm:text-xl">add</span>
                                <span>Yeni Sözleşme</span>
                            </a>
                        </div>
                    </div>

                    <?php if (!empty($message)): ?>
                        <div class="mb-4 p-4 rounded-lg <?php echo ($messageType ?? '') === 'success' ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-300'; ?>">
                            <?php echo esc_html($message); ?>
                        </div>
                    <?php endif; ?>

                    <?php
                    $filters = $filters ?? [];
                    $filterStatus = $filters['status'] ?? '';
                    $baseUrl = admin_url('module/commission-contracts');
                    ?>
                    <form method="get" action="<?php echo esc_url($baseUrl); ?>" class="mb-6 bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
                        <input type="hidden" name="page" value="module/commission-contracts" />
                        <div class="flex flex-wrap items-end gap-3 sm:gap-4">
                            <div>
                                <label for="filter-status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Durum</label>
                                <select id="filter-status" name="status" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 text-sm">
                                    <option value="">Tümü</option>
                                    <option value="draft" <?php echo $filterStatus === 'draft' ? 'selected' : ''; ?>>Taslak</option>
                                    <option value="signed" <?php echo $filterStatus === 'signed' ? 'selected' : ''; ?>>İmzalandı</option>
                                </select>
                            </div>
                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 font-medium text-sm">Filtrele</button>
                            <a href="<?php echo esc_url($baseUrl); ?>" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm">Temizle</a>
                        </div>
                    </form>

                    <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-x-auto overflow-y-hidden" style="-webkit-overflow-scrolling: touch;">
                        <table class="min-w-full w-full divide-y divide-gray-200 dark:divide-gray-700" style="table-layout: fixed;">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-2 sm:px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-[3%]">No</th>
                                    <th class="px-2 sm:px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-[9%]">Sözleşme No</th>
                                    <th class="px-2 sm:px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-[16%]">Sözleşme Adı</th>
                                    <th class="px-2 sm:px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-[14%]">Müşteri</th>
                                    <th class="px-2 sm:px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-[16%]">Şablon</th>
                                    <th class="px-2 sm:px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-[12%]">Durum</th>
                                    <th class="px-2 sm:px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-[8%]">Tarih</th>
                                    <th class="px-2 sm:px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-[14%]">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-[#1e293b] divide-y divide-gray-200 dark:divide-gray-700">
                                <?php if (empty($contracts)): ?>
                                    <tr>
                                        <td colspan="8" class="px-2 sm:px-3 py-12 text-center text-gray-500 dark:text-gray-400">
                                            <span class="material-symbols-outlined text-4xl text-gray-300 dark:text-gray-600 block mb-2">description</span>
                                            <p>Henüz sözleşme yok.</p>
                                            <a href="<?php echo admin_url('module/commission-contracts/create'); ?>" class="text-primary hover:underline text-sm mt-2 inline-block">Yeni sözleşme oluştur</a>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($contracts as $c): ?>
                                        <tr>
                                            <td class="px-2 sm:px-3 py-3 sm:py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white overflow-hidden">#<?php echo (int) $c['id']; ?></td>
                                            <td class="px-2 sm:px-3 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300 overflow-hidden text-ellipsis" title="<?php echo esc_attr($c['contract_number'] ?? ''); ?>"><?php echo esc_html($c['contract_number'] ?? '—'); ?></td>
                                            <td class="px-2 sm:px-3 py-3 sm:py-4 text-sm text-gray-600 dark:text-gray-300 overflow-hidden text-ellipsis whitespace-nowrap" title="<?php echo esc_attr($c['contract_name'] ?? ''); ?>"><?php echo esc_html($c['contract_name'] ?? '—'); ?></td>
                                            <td class="px-2 sm:px-3 py-3 sm:py-4 text-sm text-gray-900 dark:text-white overflow-hidden text-ellipsis whitespace-nowrap" title="<?php echo esc_attr($c['client_name'] ?? ''); ?>"><?php echo esc_html($c['client_name']); ?></td>
                                            <td class="px-2 sm:px-3 py-3 sm:py-4 text-sm text-gray-500 dark:text-gray-400 overflow-hidden text-ellipsis whitespace-nowrap" title="<?php echo esc_attr($c['template_name'] ?? ''); ?>"><?php echo !empty($c['template_name']) ? esc_html($c['template_name']) : '—'; ?></td>
                                            <td class="px-2 sm:px-3 py-3 sm:py-4 whitespace-nowrap overflow-hidden">
                                                <?php 
                                                $status = $c['status'] ?? '';
                                                $party2Signed = !empty($c['signature_data_party2']);
                                                if ($status === 'signed' && $party2Signed): ?>
                                                    <span class="px-2 py-1 text-xs rounded-full font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">Tam İmzalı</span>
                                                <?php elseif ($status === 'signed'): ?>
                                                    <span class="px-2 py-1 text-xs rounded-full font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">Karşı taraf bekleniyor</span>
                                                <?php else: ?>
                                                    <span class="px-2 py-1 text-xs rounded-full font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300">Taslak</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-2 sm:px-3 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 overflow-hidden"><?php echo date('d.m.Y', strtotime($c['created_at'])); ?></td>
                                            <td class="px-2 sm:px-3 py-3 sm:py-4 text-sm font-medium overflow-hidden">
                                                <div class="flex items-center gap-2 flex-nowrap">
                                                    <a href="<?php echo admin_url('module/commission-contracts/edit/' . $c['id']); ?>" class="text-primary hover:text-primary/80 inline-flex items-center justify-center p-1.5 rounded hover:bg-primary/10" title="Düzenle" aria-label="Düzenle">
                                                        <span class="material-symbols-outlined text-lg">edit</span>
                                                    </a>
                                                    <?php if (($c['status'] ?? '') === 'signed'): ?>
                                                        <a href="<?php echo admin_url('module/commission-contracts/pdf/' . $c['id'] . '?view=1'); ?>" class="text-gray-700 dark:text-gray-300 hover:text-primary inline-flex items-center justify-center p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700" target="_blank" title="PDF Görüntüle" aria-label="Görüntüle">
                                                            <span class="material-symbols-outlined text-lg">visibility</span>
                                                        </a>
                                                        <a href="<?php echo admin_url('module/commission-contracts/pdf/' . $c['id']); ?>" class="text-gray-700 dark:text-gray-300 hover:text-primary inline-flex items-center justify-center p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700" target="_blank" title="PDF İndir" aria-label="İndir">
                                                            <span class="material-symbols-outlined text-lg">download</span>
                                                        </a>
                                                    <?php endif; ?>
                                                    <form action="<?php echo admin_url('module/commission-contracts/delete/' . $c['id']); ?>" method="post" class="inline" onsubmit="return confirm('Bu sözleşmeyi silmek istediğinize emin misiniz?');">
                                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 inline-flex items-center justify-center p-1.5 rounded hover:bg-red-500/10" title="Sil" aria-label="Sil">
                                                            <span class="material-symbols-outlined text-lg">delete</span>
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
