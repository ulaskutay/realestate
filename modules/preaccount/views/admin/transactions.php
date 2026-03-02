<?php
$transactions = $transactions ?? [];
$accounts = $accounts ?? [];
$incomeCategories = $incomeCategories ?? [];
$expenseCategories = $expenseCategories ?? [];
$allCategories = array_merge(
    array_map(function ($c) { $c['_type'] = 'income'; return $c; }, $incomeCategories),
    array_map(function ($c) { $c['_type'] = 'expense'; return $c; }, $expenseCategories)
);
$filters = $filters ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;
$filterSummary = $filterSummary ?? ['income' => 0, 'expense' => 0];
$filterNet = $filterSummary['income'] - $filterSummary['expense'];
$hasActiveFilters = !empty($filters['account_id']) || !empty($filters['category_id']) || !empty($filters['type']) || !empty($filters['date_from']) || !empty($filters['date_to']);
$perPage = 20;
$from = $total > 0 ? ($page - 1) * $perPage + 1 : 0;
$to = min($page * $perPage, $total);
?>
<?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="mb-4 p-3 rounded-lg <?php echo ($_SESSION['flash_type'] ?? '') === 'error' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200' : 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200'; ?>">
        <?php echo esc_html($_SESSION['flash_message']); unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    </div>
<?php endif; ?>

<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        <h1 class="text-gray-900 dark:text-white text-2xl sm:text-3xl font-bold tracking-tight">Hareketler</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-0.5">Gelir ve gider kayıtlarını yönetin</p>
    </div>
    <a href="<?php echo admin_url('module/preaccount/transaction_create'); ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors shadow-sm">
        <span class="material-symbols-outlined text-xl">add</span>
        Yeni Hareket
    </a>
</header>

<!-- Filtreler -->
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mb-6">
    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">filter_list</span>
            Filtrele
        </h2>
    </div>
    <form method="get" action="<?php echo admin_url('module/preaccount/transactions'); ?>" class="p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Hesap</label>
                <select name="account_id" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    <option value="">Tüm hesaplar</option>
                    <?php foreach ($accounts as $a): ?>
                        <option value="<?php echo $a['id']; ?>" <?php echo ($filters['account_id'] ?? '') == $a['id'] ? 'selected' : ''; ?>><?php echo esc_html($a['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Kategori</label>
                <select name="category_id" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    <option value="">Tüm kategoriler</option>
                    <?php foreach ($allCategories as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo ($filters['category_id'] ?? '') == $c['id'] ? 'selected' : ''; ?>><?php echo esc_html($c['name']); ?> (<?php echo $c['_type'] === 'income' ? 'Gelir' : 'Gider'; ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Tip</label>
                <select name="type" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                    <option value="">Tümü</option>
                    <option value="income" <?php echo ($filters['type'] ?? '') === 'income' ? 'selected' : ''; ?>>Gelir</option>
                    <option value="expense" <?php echo ($filters['type'] ?? '') === 'expense' ? 'selected' : ''; ?>>Gider</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Tarih (başlangıç)</label>
                <input type="date" name="date_from" value="<?php echo esc_attr($filters['date_from'] ?? ''); ?>"
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Tarih (bitiş)</label>
                <input type="date" name="date_to" value="<?php echo esc_attr($filters['date_to'] ?? ''); ?>"
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors">
                    <span class="material-symbols-outlined text-lg">search</span>
                    Uygula
                </button>
                <a href="<?php echo admin_url('module/preaccount/transactions'); ?>" class="inline-flex items-center gap-1.5 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <span class="material-symbols-outlined text-lg">clear_all</span>
                    Temizle
                </a>
            </div>
        </div>
        <?php if (!empty($filters['p'])): ?><input type="hidden" name="p" value="1"><?php endif; ?>
    </form>
</div>

<?php if ($hasActiveFilters && ($filterSummary['income'] > 0 || $filterSummary['expense'] > 0)): ?>
<!-- Filtre özeti -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800 p-4">
        <p class="text-xs font-medium text-green-700 dark:text-green-300 uppercase tracking-wide">Filtreye göre gelir</p>
        <p class="text-xl font-bold text-green-700 dark:text-green-400 mt-0.5"><?php echo number_format($filterSummary['income'], 2, ',', '.'); ?> ₺</p>
    </div>
    <div class="bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800 p-4">
        <p class="text-xs font-medium text-red-700 dark:text-red-300 uppercase tracking-wide">Filtreye göre gider</p>
        <p class="text-xl font-bold text-red-700 dark:text-red-400 mt-0.5"><?php echo number_format($filterSummary['expense'], 2, ',', '.'); ?> ₺</p>
    </div>
    <div class="bg-gray-100 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600 p-4">
        <p class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Net</p>
        <p class="text-xl font-bold <?php echo $filterNet >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'; ?> mt-0.5"><?php echo number_format($filterNet, 2, ',', '.'); ?> ₺</p>
    </div>
</div>
<?php endif; ?>

<!-- Tablo -->
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/80">
                <tr>
                    <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tarih</th>
                    <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Hesap</th>
                    <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kategori</th>
                    <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Açıklama</th>
                    <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">Tip</th>
                    <th scope="col" class="px-4 py-3.5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tutar</th>
                    <th scope="col" class="px-4 py-3.5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-28">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="7" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-gray-500 dark:text-gray-400">
                                <span class="material-symbols-outlined text-5xl opacity-50">receipt_long</span>
                                <p class="text-sm font-medium">Henüz hareket kaydı yok</p>
                                <p class="text-xs max-w-sm">Filtreleri temizleyebilir veya yeni bir hareket ekleyebilirsiniz.</p>
                                <a href="<?php echo admin_url('module/preaccount/transaction_create'); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 mt-2">
                                    <span class="material-symbols-outlined text-lg">add</span>
                                    Yeni Hareket Ekle
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $t): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                            <td class="px-4 py-3.5 text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap"><?php echo $t['date']; ?></td>
                            <td class="px-4 py-3.5 text-sm font-medium text-gray-900 dark:text-white"><?php echo esc_html($t['account_name'] ?? '-'); ?></td>
                            <td class="px-4 py-3.5 text-sm text-gray-600 dark:text-gray-400">
                                <?php echo esc_html($t['category_name'] ?? '-'); ?>
                                <?php if (!empty($t['category_account_code'])): ?>
                                    <span class="font-mono text-xs text-gray-400 dark:text-gray-500">(<?php echo esc_html($t['category_account_code']); ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3.5 text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate" title="<?php echo esc_attr($t['description'] ?? ''); ?>"><?php echo esc_html($t['description'] ?: '—'); ?></td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium <?php echo $t['type'] === 'income' ? 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200'; ?>">
                                    <?php echo $t['type'] === 'income' ? 'Gelir' : 'Gider'; ?>
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <span class="font-mono text-sm font-semibold <?php echo $t['type'] === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'; ?>">
                                    <?php echo $t['type'] === 'income' ? '+' : '−'; ?><?php echo number_format((float)$t['amount'], 2, ',', '.'); ?> ₺
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="<?php echo admin_url('module/preaccount/transaction_edit/' . $t['id']); ?>" class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-600 hover:text-primary transition-colors" title="Düzenle">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </a>
                                    <form method="post" action="<?php echo admin_url('module/preaccount/transaction_delete/' . $t['id']); ?>" class="inline" onsubmit="return confirm('Bu hareketi silmek istediğinize emin misiniz?');">
                                        <button type="submit" class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-red-50 dark:hover:bg-red-900/30 hover:text-red-600 dark:hover:text-red-400 transition-colors" title="Sil">
                                            <span class="material-symbols-outlined text-lg">delete_outline</span>
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

    <?php if ($totalPages > 1 || $total > 0): ?>
        <?php
        $paginateParams = array_filter([
            'account_id' => $filters['account_id'] ?? '',
            'category_id' => $filters['category_id'] ?? '',
            'type' => $filters['type'] ?? '',
            'date_from' => $filters['date_from'] ?? '',
            'date_to' => $filters['date_to'] ?? ''
        ]);
        ?>
        <div class="px-4 py-3.5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30 flex flex-col sm:flex-row justify-between items-center gap-3">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <span class="font-medium text-gray-700 dark:text-gray-300"><?php echo $from; ?>–<?php echo $to; ?></span> / <?php echo $total; ?> kayıt
            </p>
            <div class="flex items-center gap-2">
                <?php if ($page > 1): ?>
                    <a href="<?php echo admin_url('module/preaccount/transactions', array_merge($paginateParams, ['p' => $page - 1])); ?>" class="inline-flex items-center gap-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                        <span class="material-symbols-outlined text-lg">chevron_left</span>
                        Önceki
                    </a>
                <?php endif; ?>
                <span class="px-3 py-2 text-sm text-gray-600 dark:text-gray-400">
                    Sayfa <span class="font-medium"><?php echo $page; ?></span> / <?php echo $totalPages; ?>
                </span>
                <?php if ($page < $totalPages): ?>
                    <a href="<?php echo admin_url('module/preaccount/transactions', array_merge($paginateParams, ['p' => $page + 1])); ?>" class="inline-flex items-center gap-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                        Sonraki
                        <span class="material-symbols-outlined text-lg">chevron_right</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
