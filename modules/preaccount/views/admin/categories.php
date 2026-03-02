<?php
$incomeCategories = $incomeCategories ?? [];
$expenseCategories = $expenseCategories ?? [];
$editCategory = $editCategory ?? null;
?>
<?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="mb-4 p-3 rounded-lg <?php echo ($_SESSION['flash_type'] ?? '') === 'error' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200' : 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200'; ?>">
        <?php echo esc_html($_SESSION['flash_message']); unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    </div>
<?php endif; ?>
<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Kategoriler</h1>
        <p class="text-gray-500 dark:text-gray-400 text-base mt-1">Gelir ve gider kategorileri</p>
    </div>
</header>

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"><?php echo $editCategory ? 'Kategori Düzenle' : 'Yeni Kategori Ekle'; ?></h2>
    <form method="post" action="<?php echo admin_url('module/preaccount/' . ($editCategory ? 'category_update/' . $editCategory['id'] : 'category_store')); ?>" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori Adı *</label>
            <input type="text" name="name" required value="<?php echo $editCategory ? esc_attr($editCategory['name']) : ''; ?>"
                   class="w-64 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2" placeholder="Örn. Komisyon, Kira">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tip</label>
            <select name="type" class="w-40 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2">
                <option value="income" <?php echo ($editCategory && $editCategory['type'] === 'income') ? 'selected' : ''; ?>>Gelir</option>
                <option value="expense" <?php echo ($editCategory && $editCategory['type'] === 'expense') ? 'selected' : ''; ?>>Gider</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hesap Kodu (muhasebe)</label>
            <input type="text" name="account_code" value="<?php echo $editCategory ? esc_attr($editCategory['account_code'] ?? '') : ''; ?>"
                   class="w-28 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2" placeholder="600, 620" maxlength="20">
        </div>
        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90"><?php echo $editCategory ? 'Güncelle' : 'Ekle'; ?></button>
        <?php if ($editCategory): ?>
            <a href="<?php echo admin_url('module/preaccount/categories'); ?>" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">İptal</a>
        <?php endif; ?>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-green-50 dark:bg-green-900/20">
            <h2 class="text-lg font-semibold text-green-800 dark:text-green-200">Gelir Kategorileri</h2>
        </div>
        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php if (empty($incomeCategories)): ?>
                <li class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">Henüz gelir kategorisi yok.</li>
            <?php else: ?>
                <?php foreach ($incomeCategories as $c): ?>
                    <li class="px-4 py-3 flex justify-between items-center">
                        <span class="font-medium text-gray-900 dark:text-white"><?php echo esc_html($c['name']); ?><?php if (!empty($c['account_code'])): ?> <span class="text-xs text-gray-500 font-mono">(<?php echo esc_html($c['account_code']); ?>)</span><?php endif; ?></span>
                        <span>
                            <a href="<?php echo admin_url('module/preaccount/category_edit/' . $c['id']); ?>" class="text-primary hover:underline text-sm">Düzenle</a>
                            <form method="post" action="<?php echo admin_url('module/preaccount/category_delete/' . $c['id']); ?>" class="inline ml-2" onsubmit="return confirm('Silinsin mi?');">
                                <button type="submit" class="text-red-600 dark:text-red-400 hover:underline text-sm">Sil</button>
                            </form>
                        </span>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-red-50 dark:bg-red-900/20">
            <h2 class="text-lg font-semibold text-red-800 dark:text-red-200">Gider Kategorileri</h2>
        </div>
        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php if (empty($expenseCategories)): ?>
                <li class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">Henüz gider kategorisi yok.</li>
            <?php else: ?>
                <?php foreach ($expenseCategories as $c): ?>
                    <li class="px-4 py-3 flex justify-between items-center">
                        <span class="font-medium text-gray-900 dark:text-white"><?php echo esc_html($c['name']); ?><?php if (!empty($c['account_code'])): ?> <span class="text-xs text-gray-500 font-mono">(<?php echo esc_html($c['account_code']); ?>)</span><?php endif; ?></span>
                        <span>
                            <a href="<?php echo admin_url('module/preaccount/category_edit/' . $c['id']); ?>" class="text-primary hover:underline text-sm">Düzenle</a>
                            <form method="post" action="<?php echo admin_url('module/preaccount/category_delete/' . $c['id']); ?>" class="inline ml-2" onsubmit="return confirm('Silinsin mi?');">
                                <button type="submit" class="text-red-600 dark:text-red-400 hover:underline text-sm">Sil</button>
                            </form>
                        </span>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
</div>
