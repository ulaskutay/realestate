<?php
// Dashboard view
$stats = $stats ?? ['total' => 0, 'synced' => 0, 'pending' => 0, 'failed' => 0, 'deleted' => 0];
$recentSyncs = $recentSyncs ?? [];
$failedSyncs = $failedSyncs ?? [];
?>

<!-- Header -->
<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div class="flex flex-col gap-2">
        <div class="flex items-center gap-3">
            <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Emlakjet Dashboard</h1>
            <?php if ($settings['test_mode'] ?? true): ?>
                <span class="px-3 py-1 text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 rounded-full border border-yellow-200 dark:border-yellow-800">
                    TEST MODU
                </span>
            <?php endif; ?>
        </div>
        <p class="text-gray-500 dark:text-gray-400 text-base">
            İlan senkronizasyon yönetimi ve takibi.
            <?php if ($settings['test_mode'] ?? true): ?>
                <span class="text-yellow-600 dark:text-yellow-400">(Mock API kullanılıyor)</span>
            <?php endif; ?>
        </p>
    </div>
    <div class="flex items-center gap-2">
        <a href="<?php echo admin_url('module/emlakjet/settings'); ?>" class="flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
            <span class="material-symbols-outlined text-xl">settings</span>
            <span class="text-sm font-medium">Ayarlar</span>
        </a>
        <a href="<?php echo admin_url('module/emlakjet/sync'); ?>" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
            <span class="material-symbols-outlined text-xl">sync</span>
            <span class="text-sm font-medium">Senkronize Et</span>
        </a>
        <a href="<?php echo admin_url('module/emlakjet/listings'); ?>" class="flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
            <span class="material-symbols-outlined text-xl">list</span>
            <span class="text-sm font-medium">İlanlar</span>
        </a>
    </div>
</header>

<!-- Mesaj -->
<?php if (isset($_SESSION['flash_message'])): ?>
<div class="mb-6 p-4 rounded-lg <?php echo ($_SESSION['flash_type'] ?? 'success') === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 text-red-800 dark:text-red-200'; ?>">
    <p class="text-sm font-medium"><?php echo esc_html($_SESSION['flash_message']); ?></p>
</div>
<?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); endif; ?>

<!-- İstatistikler -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Toplam İlan</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $stats['total']; ?></p>
            </div>
            <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">real_estate_agent</span>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Senkronize</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1"><?php echo $stats['synced']; ?></p>
            </div>
            <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                <span class="material-symbols-outlined text-green-600 dark:text-green-400">check_circle</span>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Bekleyen</p>
                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400 mt-1"><?php echo $stats['pending']; ?></p>
            </div>
            <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                <span class="material-symbols-outlined text-yellow-600 dark:text-yellow-400">schedule</span>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Başarısız</p>
                <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1"><?php echo $stats['failed']; ?></p>
            </div>
            <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                <span class="material-symbols-outlined text-red-600 dark:text-red-400">error</span>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Silinen</p>
                <p class="text-2xl font-bold text-gray-600 dark:text-gray-400 mt-1"><?php echo $stats['deleted']; ?></p>
            </div>
            <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded-lg">
                <span class="material-symbols-outlined text-gray-600 dark:text-gray-400">delete</span>
            </div>
        </div>
    </div>
</div>

<!-- İki Sütun Layout -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Son Senkronizasyonlar -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Son Senkronizasyonlar</h2>
        </div>
        <div class="p-4">
            <?php if (empty($recentSyncs)): ?>
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Henüz senkronizasyon yapılmamış</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($recentSyncs as $sync): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo esc_html($sync['title'] ?? 'İlan #' . $sync['listing_id']); ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    <?php echo esc_html($sync['location'] ?? ''); ?>
                                    <?php if (!empty($sync['price'])): ?>
                                        - <?php echo number_format($sync['price'], 0, ',', '.'); ?> TL
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 text-xs rounded <?php 
                                    echo $sync['sync_status'] === 'synced' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 
                                        ($sync['sync_status'] === 'failed' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' : 
                                        'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300');
                                ?>">
                                    <?php 
                                    echo $sync['sync_status'] === 'synced' ? 'Senkronize' : 
                                        ($sync['sync_status'] === 'failed' ? 'Başarısız' : 'Bekliyor');
                                    ?>
                                </span>
                                <?php if (!empty($sync['last_sync_at'])): ?>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        <?php echo date('d.m.Y H:i', strtotime($sync['last_sync_at'])); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Başarısız Senkronizasyonlar -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Başarısız Senkronizasyonlar</h2>
        </div>
        <div class="p-4">
            <?php if (empty($failedSyncs)): ?>
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Başarısız senkronizasyon yok</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($failedSyncs as $sync): ?>
                        <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo esc_html($sync['title'] ?? 'İlan #' . $sync['listing_id']); ?></p>
                                    <?php if (!empty($sync['last_error'])): ?>
                                        <p class="text-xs text-red-600 dark:text-red-400 mt-1"><?php echo esc_html($sync['last_error']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <button onclick="retrySync(<?php echo $sync['listing_id']; ?>)" class="ml-2 px-2 py-1 text-xs bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors">
                                    Tekrar Dene
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function retrySync(listingId) {
    if (!confirm('Bu ilanı tekrar senkronize etmek istediğinize emin misiniz?')) {
        return;
    }
    
    fetch('<?php echo admin_url('module/emlakjet/sync'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'action=sync&listing_ids[]=' + listingId + '&direction=push'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Hata: ' + (data.error || 'Bilinmeyen hata'));
        }
    })
    .catch(error => {
        alert('Hata: ' + error);
    });
}
</script>
