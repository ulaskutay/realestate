<?php
$summary = $summary ?? ['income' => 0, 'expense' => 0];
$accounts = $accounts ?? [];
$balances = $balances ?? [];
$recentTransactions = $recentTransactions ?? [];
$net = ($summary['income'] ?? 0) - ($summary['expense'] ?? 0);
?>
<?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="mb-4 p-3 rounded-lg <?php echo ($_SESSION['flash_type'] ?? '') === 'error' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200' : 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200'; ?>">
        <?php echo esc_html($_SESSION['flash_message']); unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    </div>
<?php endif; ?>
<header class="mb-6">
    <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Ön Muhasebe Pano</h1>
    <p class="text-gray-500 dark:text-gray-400 text-base mt-1">Gelir, gider ve hesap özeti.</p>
</header>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Bu Ay Gelir</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400"><?php echo number_format($summary['income'], 2, ',', '.'); ?> ₺</p>
            </div>
            <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                <span class="material-symbols-outlined text-green-600 dark:text-green-400">trending_up</span>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Bu Ay Gider</p>
                <p class="text-2xl font-bold text-red-600 dark:text-red-400"><?php echo number_format($summary['expense'], 2, ',', '.'); ?> ₺</p>
            </div>
            <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                <span class="material-symbols-outlined text-red-600 dark:text-red-400">trending_down</span>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Net (Bu Ay)</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo number_format($net, 2, ',', '.'); ?> ₺</p>
            </div>
            <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">account_balance</span>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Hesap Bakiyeleri</h2>
            <a href="<?php echo admin_url('module/preaccount/accounts'); ?>" class="text-sm text-primary hover:underline">Tümü</a>
        </div>
        <div class="p-4">
            <?php if (empty($accounts)): ?>
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Henüz hesap yok. <a href="<?php echo admin_url('module/preaccount/accounts'); ?>" class="text-primary hover:underline">Hesap ekle</a></p>
            <?php else: ?>
                <div class="space-y-2">
                    <?php foreach ($accounts as $a): ?>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                            <span class="font-medium text-gray-900 dark:text-white"><?php echo esc_html($a['name']); ?></span>
                            <span class="font-mono text-gray-700 dark:text-gray-300"><?php echo number_format($balances[$a['id']] ?? 0, 2, ',', '.'); ?> ₺</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Son Hareketler</h2>
            <a href="<?php echo admin_url('module/preaccount/transactions'); ?>" class="text-sm text-primary hover:underline">Tümü</a>
        </div>
        <div class="p-4">
            <?php if (empty($recentTransactions)): ?>
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Henüz hareket yok</p>
            <?php else: ?>
                <div class="space-y-2">
                    <?php foreach ($recentTransactions as $t): ?>
                        <div class="flex justify-between items-start py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo esc_html($t['description'] ?: '-'); ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo esc_html($t['account_name'] ?? ''); ?> · <?php echo $t['date']; ?></p>
                            </div>
                            <span class="font-mono <?php echo $t['type'] === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'; ?>">
                                <?php echo $t['type'] === 'income' ? '+' : '-'; ?><?php echo number_format((float)$t['amount'], 2, ',', '.'); ?> ₺
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="flex gap-4">
    <a href="<?php echo admin_url('module/preaccount/transactions'); ?>" class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 hover:border-primary transition-colors flex items-center gap-3">
        <div class="p-2 bg-primary/10 rounded-lg"><span class="material-symbols-outlined text-primary">list</span></div>
        <div>
            <p class="font-medium text-gray-900 dark:text-white">Hareketler</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Gelir ve gider listesi</p>
        </div>
    </a>
    <a href="<?php echo admin_url('module/preaccount/reports'); ?>" class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 hover:border-primary transition-colors flex items-center gap-3">
        <div class="p-2 bg-primary/10 rounded-lg"><span class="material-symbols-outlined text-primary">bar_chart</span></div>
        <div>
            <p class="font-medium text-gray-900 dark:text-white">Raporlar</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Gelir-gider raporu</p>
        </div>
    </a>
</div>
