<?php
// Sync view
$pendingListings = $pendingListings ?? [];
?>

<!-- Header -->
<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div class="flex flex-col gap-2">
        <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Manuel Senkronizasyon</h1>
        <p class="text-gray-500 dark:text-gray-400 text-base">İlanları Emlakjet ile senkronize edin.</p>
    </div>
    <a href="<?php echo admin_url('module/emlakjet'); ?>" class="flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
        <span class="material-symbols-outlined text-xl">arrow_back</span>
        <span class="text-sm font-medium">Dashboard</span>
    </a>
</header>

<!-- Mesaj -->
<?php if (isset($_SESSION['flash_message'])): ?>
<div class="mb-6 p-4 rounded-lg <?php echo ($_SESSION['flash_type'] ?? 'success') === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 text-red-800 dark:text-red-200'; ?>">
    <p class="text-sm font-medium"><?php echo esc_html($_SESSION['flash_message']); ?></p>
</div>
<?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); endif; ?>

<!-- Senkronizasyon Formu -->
<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Toplu Senkronizasyon</h2>
    <form method="POST" action="<?php echo admin_url('module/emlakjet/sync'); ?>" id="syncForm">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Senkronizasyon Yönü</label>
                <select name="direction" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="push">Sistemden Emlakjet'e (Push)</option>
                    <option value="pull">Emlakjet'ten Sisteme (Pull)</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">İlan Seçimi</label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="radio" name="sync_type" value="all" checked class="w-4 h-4 text-primary border-gray-300 focus:ring-primary">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Tüm bekleyen ilanları senkronize et</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="sync_type" value="selected" class="w-4 h-4 text-primary border-gray-300 focus:ring-primary">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Seçili ilanları senkronize et</span>
                    </label>
                </div>
            </div>
            
            <input type="hidden" name="action" value="sync">
            <button type="submit" class="w-full px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                <span class="material-symbols-outlined text-xl align-middle">sync</span>
                <span class="ml-2">Senkronizasyonu Başlat</span>
            </button>
        </div>
    </form>
</div>

<!-- Bekleyen İlanlar -->
<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Bekleyen İlanlar (<?php echo count($pendingListings); ?>)</h2>
    </div>
    <div class="p-4">
        <?php if (empty($pendingListings)): ?>
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Bekleyen ilan yok</p>
        <?php else: ?>
            <div class="space-y-3" id="pendingListings">
                <?php foreach ($pendingListings as $listing): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div class="flex items-center gap-3 flex-1">
                            <input type="checkbox" name="listing_ids[]" value="<?php echo $listing['id']; ?>" class="listing-checkbox w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo esc_html($listing['title']); ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    <?php echo esc_html($listing['location'] ?? ''); ?>
                                    <?php if (!empty($listing['price'])): ?>
                                        - <?php echo number_format($listing['price'], 0, ',', '.'); ?> TL
                                    <?php endif; ?>
                                </p>
                                <?php if (!empty($listing['last_error'])): ?>
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1"><?php echo esc_html($listing['last_error']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="px-2 py-1 text-xs rounded <?php 
                            echo ($listing['sync_status'] ?? 'pending') === 'failed' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' : 
                                'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300';
                        ?>">
                            <?php echo ($listing['sync_status'] ?? 'pending') === 'failed' ? 'Başarısız' : 'Bekliyor'; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('syncForm')?.addEventListener('submit', function(e) {
    const syncType = document.querySelector('input[name="sync_type"]:checked')?.value;
    
    if (syncType === 'selected') {
        const selected = document.querySelectorAll('.listing-checkbox:checked');
        if (selected.length === 0) {
            e.preventDefault();
            alert('Lütfen en az bir ilan seçin');
            return false;
        }
        
        // Seçili ilanları form'a ekle
        selected.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'listing_ids[]';
            input.value = checkbox.value;
            this.appendChild(input);
        });
    }
    
    if (!confirm('Senkronizasyonu başlatmak istediğinize emin misiniz?')) {
        e.preventDefault();
        return false;
    }
});
</script>
