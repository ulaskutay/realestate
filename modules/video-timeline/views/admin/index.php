<?php
$timelines = $timelines ?? [];
$title = $title ?? 'Video Timeline';
$search = $search ?? '';
?>
<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div class="flex flex-col gap-2">
        <h1 class="text-gray-900 dark:text-white text-2xl sm:text-3xl font-bold tracking-tight">Video Timeline</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm sm:text-base">Timeline projelerinizi oluşturun ve düzenleyin (metin, overlay, animasyon, keyframe).</p>
    </div>
    <a href="<?php echo admin_url('module/video-timeline/create'); ?>" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors min-h-[44px] justify-center">
        <span class="material-symbols-outlined text-xl">add</span>
        <span class="text-sm font-medium">Yeni</span>
    </a>
</header>

<form method="get" action="<?php echo esc_attr(admin_url('module/video-timeline/index')); ?>" class="mb-6 flex flex-wrap items-center gap-2">
    <input type="search" name="search" value="<?php echo esc_attr($search); ?>" placeholder="Proje adına göre ara..." class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white min-w-[200px]">
    <button type="submit" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 text-sm font-medium">Ara</button>
    <?php if ($search !== ''): ?>
    <a href="<?php echo admin_url('module/video-timeline/index'); ?>" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:underline text-sm">Filtreyi temizle</a>
    <?php endif; ?>
</form>

<?php if (isset($_SESSION['flash_message'])): ?>
<div class="mb-6 p-4 rounded-lg <?php echo ($_SESSION['flash_type'] ?? 'success') === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 text-red-800 dark:text-red-200'; ?>">
    <p class="text-sm font-medium"><?php echo esc_html($_SESSION['flash_message']); ?></p>
</div>
<?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); endif; ?>

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 text-sm font-medium">
                <tr>
                    <th class="px-4 py-3">Ad</th>
                    <th class="px-4 py-3">Boyut</th>
                    <th class="px-4 py-3">Süre</th>
                    <th class="px-4 py-3">Oluşturma</th>
                    <th class="px-4 py-3 w-40">İşlemler</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php if (empty($timelines)): ?>
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Henüz timeline yok. <a href="<?php echo admin_url('module/video-timeline/create'); ?>" class="text-primary hover:underline">Yeni</a> oluşturun.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($timelines as $t): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                    <td class="px-4 py-3 text-gray-900 dark:text-white font-medium"><?php echo esc_html($t['name']); ?></td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300"><?php echo (int)($t['width'] ?? 1920); ?> × <?php echo (int)($t['height'] ?? 1080); ?></td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300"><?php echo number_format((float)($t['duration_sec'] ?? 0), 1); ?> sn</td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-sm"><?php echo date('d.m.Y H:i', strtotime($t['created_at'] ?? 'now')); ?></td>
                    <td class="px-4 py-3">
                        <a href="<?php echo admin_url('module/video-timeline/editor', ['id' => (int)$t['id']]); ?>" class="inline-flex items-center gap-1 px-2 py-1 text-primary hover:underline text-sm">Düzenle</a>
                        <a href="<?php echo admin_url('module/video-timeline/duplicate-timeline', ['id' => (int)$t['id']]); ?>" class="inline-flex items-center gap-1 px-2 py-1 text-gray-600 dark:text-gray-400 hover:underline text-sm">Çoğalt</a>
                        <button type="button" data-timeline-id="<?php echo (int)$t['id']; ?>" data-timeline-name="<?php echo esc_attr($t['name']); ?>" class="video-timeline-delete inline-flex items-center gap-1 px-2 py-1 text-red-600 dark:text-red-400 hover:underline text-sm">Sil</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(function() {
    document.querySelectorAll('.video-timeline-delete').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-timeline-id');
            var name = this.getAttribute('data-timeline-name');
            if (!id || !confirm('"' + (name || '') + '" timeline\'ını silmek istediğinize emin misiniz?')) return;
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo admin_url('module/video-timeline/delete-timeline'); ?>';
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'id';
            input.value = id;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        });
    });
})();
</script>
