<?php
$currencies = $currencies ?? [];
$editCurrency = $editCurrency ?? null;
?>
<?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="mb-4 p-3 rounded-lg <?php echo ($_SESSION['flash_type'] ?? '') === 'error' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200' : 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200'; ?>">
        <?php echo esc_html($_SESSION['flash_message']); unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    </div>
<?php endif; ?>
<header class="mb-6">
    <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Para Birimleri</h1>
    <p class="text-gray-500 dark:text-gray-400 text-base mt-1">Çoklu para birimi tanımları ve kurlar</p>
</header>

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"><?php echo $editCurrency ? 'Para Birimi Düzenle' : 'Yeni Para Birimi Ekle'; ?></h2>
    <form method="post" action="<?php echo admin_url('module/preaccount/' . ($editCurrency ? 'currency_update/' . $editCurrency['id'] : 'currency_store')); ?>" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kod *</label>
            <input type="text" name="code" required maxlength="10" value="<?php echo $editCurrency ? esc_attr($editCurrency['code']) : ''; ?>"
                   class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2" placeholder="USD">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ad *</label>
            <input type="text" name="name" required value="<?php echo $editCurrency ? esc_attr($editCurrency['name']) : ''; ?>"
                   class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2" placeholder="Amerikan Doları">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sembol</label>
            <input type="text" name="symbol" value="<?php echo $editCurrency ? esc_attr($editCurrency['symbol']) : ''; ?>"
                   class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2" placeholder="₺" maxlength="10">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kur (varsayılana göre)</label>
            <input type="number" step="0.000001" name="exchange_rate" value="<?php echo $editCurrency ? esc_attr($editCurrency['exchange_rate']) : '1'; ?>"
                   class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2">
        </div>
        <div class="flex flex-wrap gap-2 items-center">
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="is_default" value="1" <?php echo ($editCurrency && !empty($editCurrency['is_default'])) ? 'checked' : ''; ?>>
                Varsayılan
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="is_active" value="1" <?php echo (!$editCurrency || !empty($editCurrency['is_active'])) ? 'checked' : ''; ?>>
                Aktif
            </label>
            <input type="hidden" name="decimal_places" value="2">
            <input type="hidden" name="sort_order" value="0">
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90"><?php echo $editCurrency ? 'Güncelle' : 'Ekle'; ?></button>
            <?php if ($editCurrency): ?>
                <a href="<?php echo admin_url('module/preaccount/currencies'); ?>" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300">İptal</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Kod</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Ad</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Sembol</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Kur</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Varsayılan</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">İşlem</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php if (empty($currencies)): ?>
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Para birimi yok. Yukarıdan ekleyin.</td></tr>
            <?php else: ?>
                <?php foreach ($currencies as $c): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white"><?php echo esc_html($c['code']); ?></td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"><?php echo esc_html($c['name']); ?></td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"><?php echo esc_html($c['symbol'] ?? '-'); ?></td>
                        <td class="px-4 py-3 text-right font-mono text-sm text-gray-600 dark:text-gray-400"><?php echo number_format((float)$c['exchange_rate'], 4, ',', '.'); ?></td>
                        <td class="px-4 py-3 text-center">
                            <?php if (!empty($c['is_default'])): ?>
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200">Varsayılan</span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="<?php echo admin_url('module/preaccount/currency_edit/' . $c['id']); ?>" class="text-primary hover:underline text-sm">Düzenle</a>
                            <?php if (empty($c['is_default'])): ?>
                                <form method="post" action="<?php echo admin_url('module/preaccount/currency_delete/' . $c['id']); ?>" class="inline ml-2" onsubmit="return confirm('Silinsin mi?');">
                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:underline text-sm">Sil</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
