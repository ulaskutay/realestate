<?php
$accounts = $accounts ?? [];
$balances = $balances ?? [];
$editAccount = $editAccount ?? null;
$currencies = $currencies ?? [];
?>
<?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="mb-4 p-3 rounded-lg <?php echo ($_SESSION['flash_type'] ?? '') === 'error' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200' : 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200'; ?>">
        <?php echo esc_html($_SESSION['flash_message']); unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    </div>
<?php endif; ?>
<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Hesaplar</h1>
        <p class="text-gray-500 dark:text-gray-400 text-base mt-1">Kasa ve banka hesapları</p>
    </div>
</header>

<!-- Yeni Hesap / Düzenleme Formu -->
<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"><?php echo $editAccount ? 'Hesap Düzenle' : 'Yeni Hesap Ekle'; ?></h2>
    <form method="post" action="<?php echo admin_url('module/preaccount/' . ($editAccount ? 'account_update/' . $editAccount['id'] : 'account_store')); ?>" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hesap Adı *</label>
            <input type="text" name="name" required value="<?php echo $editAccount ? esc_attr($editAccount['name']) : ''; ?>"
                   class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2" placeholder="Örn. Kasa, İş Bankası">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tip</label>
            <select name="type" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2">
                <option value="cash" <?php echo ($editAccount && $editAccount['type'] === 'cash') ? 'selected' : ''; ?>>Kasa</option>
                <option value="bank" <?php echo ($editAccount && $editAccount['type'] === 'bank') ? 'selected' : ''; ?>>Banka</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Açılış Bakiyesi</label>
            <input type="number" step="0.01" name="opening_balance" value="<?php echo $editAccount ? esc_attr($editAccount['opening_balance']) : '0'; ?>"
                   class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Para Birimi</label>
            <?php if (!empty($currencies)): ?>
                <select name="currency" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2">
                    <?php foreach ($currencies as $cur): ?>
                        <option value="<?php echo esc_attr($cur['code']); ?>" <?php echo ($editAccount && $editAccount['currency'] === $cur['code']) || (!$editAccount && $cur['code'] === 'TRY') ? 'selected' : ''; ?>><?php echo esc_html($cur['code'] . ' - ' . $cur['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <input type="text" name="currency" value="<?php echo $editAccount ? esc_attr($editAccount['currency']) : 'TRY'; ?>"
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2" maxlength="10">
            <?php endif; ?>
        </div>
        <div class="flex items-end gap-2">
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="is_active" value="1" <?php echo (!$editAccount || !empty($editAccount['is_active'])) ? 'checked' : ''; ?>>
                Aktif
            </label>
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90"><?php echo $editAccount ? 'Güncelle' : 'Ekle'; ?></button>
            <?php if ($editAccount): ?>
                <a href="<?php echo admin_url('module/preaccount/accounts'); ?>" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">İptal</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Hesap</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tip</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Bakiye</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Durum</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php if (empty($accounts)): ?>
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Henüz hesap yok.</td></tr>
                <?php else: ?>
                    <?php foreach ($accounts as $a): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white"><?php echo esc_html($a['name']); ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"><?php echo $a['type'] === 'bank' ? 'Banka' : 'Kasa'; ?></td>
                            <td class="px-4 py-3 text-right font-mono text-gray-900 dark:text-white"><?php echo number_format($balances[$a['id']] ?? 0, 2, ',', '.'); ?> <?php echo esc_html($a['currency']); ?></td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 text-xs rounded-full <?php echo !empty($a['is_active']) ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'; ?>">
                                    <?php echo !empty($a['is_active']) ? 'Aktif' : 'Pasif'; ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="<?php echo admin_url('module/preaccount/account_edit/' . $a['id']); ?>" class="text-primary hover:underline text-sm">Düzenle</a>
                                <form method="post" action="<?php echo admin_url('module/preaccount/account_delete/' . $a['id']); ?>" class="inline ml-2" onsubmit="return confirm('Bu hesabı silmek istediğinize emin misiniz?');">
                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:underline text-sm">Sil</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
