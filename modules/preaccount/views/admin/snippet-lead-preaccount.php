<?php
if (empty($leadId)) return;
$transactions = $transactions ?? [];
$addTransactionUrl = $addTransactionUrl ?? admin_url('module/preaccount/transaction_create');
?>
<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mt-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">receipt_long</span>
        Ön Muhasebe
    </h2>
    <div class="flex gap-2 mb-4">
        <a href="<?php echo esc_attr($addTransactionUrl); ?>" class="inline-flex items-center gap-1 px-3 py-1.5 bg-primary text-white rounded-lg text-sm hover:bg-primary/90">Yeni Hareket</a>
    </div>
    <?php if (empty($transactions)): ?>
        <p class="text-sm text-gray-500 dark:text-gray-400">Bu lead için henüz hareket yok.</p>
    <?php else: ?>
        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Son Hareketler</p>
        <ul class="space-y-1 mb-3 text-sm">
            <?php foreach (array_slice($transactions, 0, 5) as $t): ?>
                <li class="flex justify-between">
                    <span><?php echo esc_html($t['date']); ?> · <?php echo esc_html($t['description'] ?: '-'); ?></span>
                    <span class="<?php echo $t['type'] === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'; ?>"><?php echo $t['type'] === 'income' ? '+' : '-'; ?><?php echo number_format((float)$t['amount'], 2, ',', '.'); ?> ₺</span>
                </li>
            <?php endforeach; ?>
        </ul>
        <p class="mt-2 text-xs text-gray-500"><a href="<?php echo admin_url('module/preaccount/transactions'); ?>?lead_id=<?php echo $leadId; ?>" class="text-primary hover:underline">Tümünü gör</a></p>
    <?php endif; ?>
</div>
