<?php
if (!isset($rootPath)) $rootPath = dirname(dirname(dirname(__DIR__)));
include $rootPath . '/app/views/admin/snippets/header.php';
$property_types = $property_types ?? [];
$listing_statuses = $listing_statuses ?? [];
?>

<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <?php $currentPage = 'realestate-listings'; include $rootPath . '/app/views/admin/snippets/sidebar.php'; ?>
        <div class="flex-1 flex flex-col lg:ml-64">
            <?php include $rootPath . '/app/views/admin/snippets/top-header.php'; ?>
            <main class="flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b] overflow-y-auto">
                <div class="layout-content-container flex flex-col w-full mx-auto max-w-4xl">
                    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <div>
                            <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight"><?php echo esc_html($title ?? 'İlanlar Modülü Ayarları'); ?></h1>
                            <p class="text-gray-500 dark:text-gray-400 text-base mt-1">Emlak tipi ve ilan durumu seçeneklerini düzenleyin. Yeni satır ekleyerek liste genişletilebilir.</p>
                        </div>
                        <a href="<?php echo admin_url('module/realestate-listings'); ?>" class="text-gray-500 dark:text-gray-400 hover:text-primary transition-colors flex items-center gap-2">
                            <span class="material-symbols-outlined">arrow_back</span> İlanlara dön
                        </a>
                    </header>

                    <?php if (!empty($message)): ?>
                    <div class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200">
                        <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
                    </div>
                    <?php endif; ?>

                    <form action="<?php echo admin_url('module/realestate-listings/settings-save'); ?>" method="POST" class="space-y-8">
                        <div id="property-types-section" class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Emlak tipleri</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">İlan eklerken/düzenlerken "Emlak Tipi" açılır listesinde görünecek. Değer: sistemde saklanan kod (küçük harf, İngilizce önerilir), Etiket: kullanıcıya görünen ad.</p>
                            <div class="overflow-x-auto">
                                <table class="min-w-full" id="property-types-table">
                                    <thead>
                                        <tr class="border-b border-gray-200 dark:border-gray-600">
                                            <th class="text-left py-2 pr-4 text-sm font-medium text-gray-700 dark:text-gray-300">Değer (value)</th>
                                            <th class="text-left py-2 pr-4 text-sm font-medium text-gray-700 dark:text-gray-300">Etiket (görünen ad)</th>
                                            <th class="w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($property_types as $key => $label): ?>
                                        <tr class="border-b border-gray-100 dark:border-gray-700 property-type-row">
                                            <td class="py-2 pr-4"><input type="text" name="property_type_key[]" value="<?php echo esc_attr($key); ?>" class="w-full max-w-xs px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" placeholder="örn: apartment"></td>
                                            <td class="py-2 pr-4"><input type="text" name="property_type_label[]" value="<?php echo esc_attr($label); ?>" class="w-full max-w-xs px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" placeholder="örn: Daire"></td>
                                            <td class="py-2"><button type="button" class="remove-row text-red-500 hover:text-red-700 p-1" aria-label="Kaldır"><span class="material-symbols-outlined text-lg">remove_circle_outline</span></button></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <tr class="property-type-row template-row">
                                            <td class="py-2 pr-4"><input type="text" name="property_type_key[]" class="w-full max-w-xs px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" placeholder="yeni değer"></td>
                                            <td class="py-2 pr-4"><input type="text" name="property_type_label[]" class="w-full max-w-xs px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" placeholder="yeni etiket"></td>
                                            <td class="py-2"><button type="button" class="remove-row text-red-500 hover:text-red-700 p-1" aria-label="Kaldır"><span class="material-symbols-outlined text-lg">remove_circle_outline</span></button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" id="add-property-type" class="mt-3 text-sm text-primary hover:underline flex items-center gap-1"><span class="material-symbols-outlined text-lg">add</span> Emlak tipi ekle</button>
                        </div>

                        <div id="listing-statuses-section" class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">İlan durumları</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Satılık, Kiralık vb. "İlan Durumu" listesi. Değer: saklanan kod, Etiket: görünen ad.</p>
                            <div class="overflow-x-auto">
                                <table class="min-w-full" id="listing-statuses-table">
                                    <thead>
                                        <tr class="border-b border-gray-200 dark:border-gray-600">
                                            <th class="text-left py-2 pr-4 text-sm font-medium text-gray-700 dark:text-gray-300">Değer (value)</th>
                                            <th class="text-left py-2 pr-4 text-sm font-medium text-gray-700 dark:text-gray-300">Etiket (görünen ad)</th>
                                            <th class="w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($listing_statuses as $key => $label): ?>
                                        <tr class="border-b border-gray-100 dark:border-gray-700 listing-status-row">
                                            <td class="py-2 pr-4"><input type="text" name="listing_status_key[]" value="<?php echo esc_attr($key); ?>" class="w-full max-w-xs px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" placeholder="örn: rent"></td>
                                            <td class="py-2 pr-4"><input type="text" name="listing_status_label[]" value="<?php echo esc_attr($label); ?>" class="w-full max-w-xs px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" placeholder="örn: Kiralık"></td>
                                            <td class="py-2"><button type="button" class="remove-status-row text-red-500 hover:text-red-700 p-1" aria-label="Kaldır"><span class="material-symbols-outlined text-lg">remove_circle_outline</span></button></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <tr class="listing-status-row template-row">
                                            <td class="py-2 pr-4"><input type="text" name="listing_status_key[]" class="w-full max-w-xs px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" placeholder="yeni değer"></td>
                                            <td class="py-2 pr-4"><input type="text" name="listing_status_label[]" class="w-full max-w-xs px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" placeholder="yeni etiket"></td>
                                            <td class="py-2"><button type="button" class="remove-status-row text-red-500 hover:text-red-700 p-1" aria-label="Kaldır"><span class="material-symbols-outlined text-lg">remove_circle_outline</span></button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" id="add-listing-status" class="mt-3 text-sm text-primary hover:underline flex items-center gap-1"><span class="material-symbols-outlined text-lg">add</span> İlan durumu ekle</button>
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium flex items-center gap-2">
                                <span class="material-symbols-outlined">save</span> Kaydet
                            </button>
                            <a href="<?php echo admin_url('module/realestate-listings'); ?>" class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors font-medium">İptal</a>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</div>
<script>
(function() {
    function addRow(tableId, rowClass) {
        var tbody = document.querySelector('#' + tableId + ' tbody');
        var template = tbody.querySelector('tr.template-row');
        if (!template) return;
        var tr = template.cloneNode(true);
        tr.classList.remove('template-row');
        tr.querySelectorAll('input').forEach(function(i) { i.value = ''; });
        tbody.insertBefore(tr, template);
    }
    function initRemove(selector, tableId, rowClass) {
        document.querySelector('#' + tableId).addEventListener('click', function(e) {
            var btn = e.target.closest(selector);
            if (!btn) return;
            var tr = btn.closest('tr');
            if (tr && !tr.classList.contains('template-row')) tr.remove();
        });
    }
    document.getElementById('add-property-type').addEventListener('click', function() { addRow('property-types-table', 'property-type-row'); });
    document.getElementById('add-listing-status').addEventListener('click', function() { addRow('listing-statuses-table', 'listing-status-row'); });
    initRemove('.remove-row', 'property-types-table', 'property-type-row');
    initRemove('.remove-status-row', 'listing-statuses-table', 'listing-status-row');
})();
</script>
<?php include $rootPath . '/app/views/admin/snippets/footer.php'; ?>
