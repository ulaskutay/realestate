<?php
$settings = $settings ?? [];
$currencies = $currencies ?? [];
$defaultCurrency = $defaultCurrency ?? null;
?>
<?php if (!empty($_SESSION['flash_message'])): ?>
    <div class="mb-4 p-3 rounded-lg <?php echo ($_SESSION['flash_type'] ?? '') === 'error' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200' : 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200'; ?>">
        <?php echo esc_html($_SESSION['flash_message']); unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    </div>
<?php endif; ?>
<header class="mb-6">
    <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Ön Muhasebe Ayarları</h1>
    <p class="text-gray-500 dark:text-gray-400 text-base mt-1">Genel ve entegrasyon ayarları</p>
</header>

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 max-w-2xl">
    <form method="post" action="<?php echo admin_url('module/preaccount/settings'); ?>">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Varsayılan Para Birimi</label>
                <?php if (!empty($currencies)): ?>
                    <select name="currency" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2">
                        <?php foreach ($currencies as $c): ?>
                            <option value="<?php echo esc_attr($c['code']); ?>" <?php echo ($settings['currency'] ?? 'TRY') === $c['code'] ? 'selected' : ''; ?>><?php echo esc_html($c['code'] . ' - ' . $c['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <input type="text" name="currency" value="<?php echo esc_attr($settings['currency'] ?? 'TRY'); ?>"
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2" maxlength="10">
                <?php endif; ?>
            </div>
        </div>
        <div class="mt-6 space-y-3">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Entegrasyonlar</h3>
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="multi_currency_enabled" value="1" <?php echo !empty($settings['multi_currency_enabled']) ? 'checked' : ''; ?>>
                Çoklu para birimi kullan
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="enable_crm_link" value="1" <?php echo !empty($settings['enable_crm_link']) ? 'checked' : ''; ?>>
                CRM lead eşleme (hareketlerde lead seçimi, lead sayfasında ön muhasebe kutusu)
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="enable_listing_link" value="1" <?php echo !empty($settings['enable_listing_link']) ? 'checked' : ''; ?>>
                İlan eşleme (hareketlerde ilan seçimi, ilan sayfasında ön muhasebe kutusu)
            </label>
        </div>
        <div class="mt-6">
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">Kaydet</button>
        </div>
    </form>
</div>
