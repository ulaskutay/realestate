<?php
$dateFrom = $dateFrom ?? date('Y-m-01');
$dateTo = $dateTo ?? date('Y-m-t');
$summary = $summary ?? ['income' => 0, 'expense' => 0];
$net = $summary['income'] - $summary['expense'];
?>
<?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="mb-4 p-3 rounded-lg <?php echo ($_SESSION['flash_type'] ?? '') === 'error' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200' : 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200'; ?>">
        <?php echo esc_html($_SESSION['flash_message']); unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    </div>
<?php endif; ?>
<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Raporlar</h1>
        <p class="text-gray-500 dark:text-gray-400 text-base mt-1">Gelir-gider özeti</p>
    </div>
</header>

<form method="get" action="<?php echo admin_url(); ?>" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <input type="hidden" name="page" value="module/preaccount/reports">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Başlangıç Tarihi</label>
            <input type="date" name="date_from" value="<?php echo esc_attr($dateFrom); ?>"
                   class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bitiş Tarihi</label>
            <input type="date" name="date_to" value="<?php echo esc_attr($dateTo); ?>"
                   class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2">
        </div>
        <div>
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">Raporu Getir</button>
        </div>
    </div>
</form>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <p class="text-sm text-gray-500 dark:text-gray-400">Toplam Gelir</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1"><?php echo number_format($summary['income'], 2, ',', '.'); ?> ₺</p>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1"><?php echo $dateFrom; ?> - <?php echo $dateTo; ?></p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <p class="text-sm text-gray-500 dark:text-gray-400">Toplam Gider</p>
        <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1"><?php echo number_format($summary['expense'], 2, ',', '.'); ?> ₺</p>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1"><?php echo $dateFrom; ?> - <?php echo $dateTo; ?></p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <p class="text-sm text-gray-500 dark:text-gray-400">Net</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo number_format($net, 2, ',', '.'); ?> ₺</p>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Gelir - Gider</p>
    </div>
</div>

<div class="mt-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Açıklama</h2>
    <p class="text-sm text-gray-500 dark:text-gray-400">
        Bu rapor, seçilen tarih aralığındaki tüm gelir ve gider hareketlerinin toplamını gösterir.
        Tüm gelir ve giderler hareketler üzerinden raporlanır.
    </p>
</div>
