<?php
$transaction = $transaction ?? null;
$accounts = $accounts ?? [];
$incomeCategories = $incomeCategories ?? [];
$expenseCategories = $expenseCategories ?? [];
$leads = $leads ?? [];
$listings = $listings ?? [];
$preselectedLeadId = $preselectedLeadId ?? null;
$preselectedListingId = $preselectedListingId ?? null;
$isEdit = !empty($transaction);
?>
<?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="mb-4 p-3 rounded-lg <?php echo ($_SESSION['flash_type'] ?? '') === 'error' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200' : 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200'; ?>">
        <?php echo esc_html($_SESSION['flash_message']); unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    </div>
<?php endif; ?>
<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight"><?php echo $isEdit ? 'Hareket Düzenle' : 'Yeni Hareket'; ?></h1>
        <p class="text-gray-500 dark:text-gray-400 text-base mt-1">Gelir veya gider kaydı</p>
    </div>
    <a href="<?php echo admin_url('module/preaccount/transactions'); ?>" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-white flex items-center gap-1">
        <span class="material-symbols-outlined">arrow_back</span> Listeye dön
    </a>
</header>

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 max-w-2xl">
    <form method="post" action="<?php echo admin_url('module/preaccount/' . ($isEdit ? 'transaction_update/' . $transaction['id'] : 'transaction_store')); ?>">
        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hesap *</label>
                    <select name="account_id" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2">
                        <option value="">Seçin</option>
                        <?php foreach ($accounts as $a): ?>
                            <option value="<?php echo $a['id']; ?>" <?php echo ($transaction['account_id'] ?? '') == $a['id'] ? 'selected' : ''; ?>><?php echo esc_html($a['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tarih *</label>
                    <input type="date" name="date" required value="<?php echo $transaction['date'] ?? date('Y-m-d'); ?>"
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tip *</label>
                    <select name="type" id="txType" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2">
                        <option value="income" <?php echo ($transaction['type'] ?? '') === 'income' ? 'selected' : ''; ?>>Gelir</option>
                        <option value="expense" <?php echo ($transaction['type'] ?? '') === 'expense' ? 'selected' : ''; ?>>Gider</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                    <select name="category_id" id="txCategory" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2">
                        <option value="">Seçin (opsiyonel)</option>
                        <optgroup label="Gelir" id="optIncome">
                            <?php foreach ($incomeCategories as $c): ?>
                                <option value="<?php echo $c['id']; ?>" data-type="income" <?php echo ($transaction['category_id'] ?? '') == $c['id'] ? 'selected' : ''; ?>><?php echo esc_html($c['name']); ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Gider" id="optExpense">
                            <?php foreach ($expenseCategories as $c): ?>
                                <option value="<?php echo $c['id']; ?>" data-type="expense" <?php echo ($transaction['category_id'] ?? '') == $c['id'] ? 'selected' : ''; ?>><?php echo esc_html($c['name']); ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tutar *</label>
                <input type="number" step="0.01" min="0.01" name="amount" required value="<?php echo $transaction ? esc_attr($transaction['amount']) : ''; ?>"
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2" placeholder="0,00">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Açıklama</label>
                <textarea name="description" rows="2" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2" placeholder="Açıklama (opsiyonel)"><?php echo $transaction ? esc_html($transaction['description']) : ''; ?></textarea>
            </div>
            <?php if (!empty($leads) || !empty($listings)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <?php if (!empty($leads)): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CRM Lead (eşleme)</label>
                    <select name="lead_id" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2">
                        <option value="">Seçin (opsiyonel)</option>
                        <?php foreach ($leads as $lead): ?>
                            <option value="<?php echo $lead['id']; ?>" <?php echo ($preselectedLeadId == $lead['id'] || ($transaction && ($transaction['reference_type'] ?? '') === 'crm_lead' && (int)($transaction['reference_id'] ?? 0) == $lead['id'])) ? 'selected' : ''; ?>><?php echo esc_html($lead['name'] . ($lead['phone'] ? ' - ' . $lead['phone'] : '')); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <?php if (!empty($listings)): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">İlan (eşleme)</label>
                    <select name="listing_id" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2">
                        <option value="">Seçin (opsiyonel)</option>
                        <?php foreach ($listings as $lst): ?>
                            <option value="<?php echo $lst['id']; ?>" <?php echo ($preselectedListingId == $lst['id'] || ($transaction && ($transaction['reference_type'] ?? '') === 'listing' && (int)($transaction['reference_id'] ?? 0) == $lst['id'])) ? 'selected' : ''; ?>><?php echo esc_html($lst['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="mt-6 flex gap-2">
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90"><?php echo $isEdit ? 'Güncelle' : 'Kaydet'; ?></button>
            <a href="<?php echo admin_url('module/preaccount/transactions'); ?>" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300">İptal</a>
        </div>
    </form>
</div>

<script>
document.getElementById('txType').addEventListener('change', function() {
    var type = this.value;
    var optIncome = document.getElementById('optIncome');
    var optExpense = document.getElementById('optExpense');
    optIncome.style.display = type === 'income' ? '' : 'none';
    optExpense.style.display = type === 'expense' ? '' : 'none';
});
</script>
