<?php
$assignableModules = $assignableModules ?? [];
$allowedModules = $allowedModules ?? [];
$groupLabels = ['core' => 'Çekirdek modüller', 'modules' => 'Diğer modüller'];
$grouped = [];
foreach ($assignableModules as $m) {
    $g = $m['group'] ?? 'modules';
    if (!isset($grouped[$g])) $grouped[$g] = [];
    $grouped[$g][] = $m;
}
// Sıra: önce core, sonra modules
if (!isset($grouped['core'])) $grouped['core'] = [];
if (!isset($grouped['modules'])) $grouped['modules'] = [];
?>
<div class="roles-modules-block flex flex-col gap-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <p class="text-gray-500 dark:text-gray-400 text-xs sm:text-sm">Bu role hangi modüllere erişim verileceğini seçin. Seçilen modüllerde tüm işlemler (görüntüleme, ekleme, düzenleme, silme) açık olur.</p>
        <div class="flex flex-wrap items-center gap-2">
            <input type="text" id="roles-module-search" placeholder="Modül ara…" class="roles-module-search w-full sm:w-48 min-w-0 px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary">
            <div class="flex gap-1">
                <button type="button" class="roles-module-select-all px-3 py-2 text-xs font-medium rounded-lg border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5">Tümünü seç</button>
                <button type="button" class="roles-module-select-none px-3 py-2 text-xs font-medium rounded-lg border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5">Hiçbirini seçme</button>
            </div>
        </div>
    </div>
    <div id="roles-module-list" class="flex flex-col gap-6">
        <?php foreach (['core' => $groupLabels['core'], 'modules' => $groupLabels['modules']] as $groupKey => $groupTitle): ?>
        <?php $items = $grouped[$groupKey] ?? []; if (empty($items)) continue; ?>
        <div class="roles-module-group" data-group="<?php echo esc_attr($groupKey); ?>">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 pb-1 border-b border-gray-200 dark:border-white/10"><?php echo esc_html($groupTitle); ?></h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2">
                <?php foreach ($items as $m): ?>
                <?php
                $slug = $m['slug'] ?? '';
                $label = $m['label'] ?? $slug;
                $checked = in_array($slug, $allowedModules, true);
                ?>
                <label class="roles-module-item flex items-center gap-2 py-2 px-3 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer" data-label="<?php echo esc_attr(mb_strtolower($label)); ?>">
                    <input type="checkbox" name="modules[]" value="<?php echo esc_attr($slug); ?>" <?php echo $checked ? 'checked' : ''; ?> class="roles-module-cb rounded border-gray-300 text-primary focus:ring-primary">
                    <span class="text-sm text-gray-700 dark:text-gray-300"><?php echo esc_html($label); ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <p id="roles-module-no-results" class="hidden text-sm text-gray-500 dark:text-gray-400 py-2">Arama kriterine uygun modül bulunamadı.</p>
</div>
<script>
(function() {
    var searchEl = document.getElementById('roles-module-search');
    var listEl = document.getElementById('roles-module-list');
    var noResultsEl = document.getElementById('roles-module-no-results');
    var items = listEl ? listEl.querySelectorAll('.roles-module-item') : [];
    var groups = listEl ? listEl.querySelectorAll('.roles-module-group') : [];

    function filterBySearch() {
        var q = (searchEl && searchEl.value) ? searchEl.value.trim().toLowerCase() : '';
        var visibleCount = 0;
        items.forEach(function(item) {
            var label = (item.getAttribute('data-label') || '').toLowerCase();
            var show = !q || label.indexOf(q) !== -1;
            item.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });
        groups.forEach(function(group) {
            var groupItems = group.querySelectorAll('.roles-module-item');
            var anyVisible = false;
            groupItems.forEach(function(el) { if (el.style.display !== 'none') anyVisible = true; });
            group.style.display = anyVisible ? '' : 'none';
        });
        if (noResultsEl) noResultsEl.classList.toggle('hidden', visibleCount > 0 || !q);
    }

    function selectAll(checked) {
        items.forEach(function(item) {
            if (item.style.display !== 'none') {
                var cb = item.querySelector('.roles-module-cb');
                if (cb) cb.checked = checked;
            }
        });
    }

    if (searchEl) {
        searchEl.addEventListener('input', filterBySearch);
        searchEl.addEventListener('keydown', function(e) { if (e.key === 'Escape') { searchEl.value = ''; filterBySearch(); } });
    }
    var selectAllBtn = document.querySelector('.roles-module-select-all');
    var selectNoneBtn = document.querySelector('.roles-module-select-none');
    if (selectAllBtn) selectAllBtn.addEventListener('click', function() { selectAll(true); });
    if (selectNoneBtn) selectNoneBtn.addEventListener('click', function() { selectAll(false); });
})();
</script>
