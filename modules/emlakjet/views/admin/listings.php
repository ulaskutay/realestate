<?php
// Listings view
$listings = $listings ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;
$status = $status ?? '';
$search = $search ?? '';
?>

<!-- Header -->
<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div class="flex flex-col gap-2">
        <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">İlan Yönetimi</h1>
        <p class="text-gray-500 dark:text-gray-400 text-base">Emlakjet senkronizasyon durumunu görüntüleyin ve yönetin.</p>
    </div>
    <a href="<?php echo admin_url('module/emlakjet'); ?>" class="flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
        <span class="material-symbols-outlined text-xl">arrow_back</span>
        <span class="text-sm font-medium">Dashboard</span>
    </a>
</header>

<!-- Filtreler -->
<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <form method="GET" action="<?php echo admin_url('module/emlakjet/listings'); ?>" class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <input type="text" name="search" value="<?php echo esc_attr($search); ?>" placeholder="İlan ara..." class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
        </div>
        <div>
            <select name="status" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                <option value="">Tüm Durumlar</option>
                <option value="synced" <?php echo $status === 'synced' ? 'selected' : ''; ?>>Senkronize</option>
                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Bekleyen</option>
                <option value="failed" <?php echo $status === 'failed' ? 'selected' : ''; ?>>Başarısız</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
            <span class="material-symbols-outlined text-xl">search</span>
        </button>
    </form>
</div>

<!-- Toplu İşlemler -->
<?php if (!empty($listings)): ?>
<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <input type="checkbox" id="selectAll" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
            <label for="selectAll" class="text-sm text-gray-700 dark:text-gray-300">Tümünü Seç</label>
        </div>
        <button onclick="bulkSync()" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
            <span class="material-symbols-outlined text-xl align-middle">sync</span>
            <span class="ml-2">Seçili İlanları Senkronize Et</span>
        </button>
    </div>
</div>
<?php endif; ?>

<!-- İlan Listesi -->
<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
    <?php if (empty($listings)): ?>
        <div class="p-8 text-center">
            <p class="text-gray-500 dark:text-gray-400">İlan bulunamadı</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left">
                            <input type="checkbox" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">İlan</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Fiyat</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Durum</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Son Senkronizasyon</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($listings as $listing): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3">
                                <input type="checkbox" name="listing_ids[]" value="<?php echo $listing['id']; ?>" class="listing-checkbox w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                            </td>
                            <td class="px-4 py-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo esc_html($listing['title']); ?></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?php echo esc_html($listing['location'] ?? ''); ?></p>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-sm text-gray-900 dark:text-white"><?php echo number_format($listing['price'], 0, ',', '.'); ?> TL</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded <?php 
                                    echo ($listing['sync_status'] ?? 'pending') === 'synced' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 
                                        (($listing['sync_status'] ?? 'pending') === 'failed' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' : 
                                        'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300');
                                ?>">
                                    <?php 
                                    echo ($listing['sync_status'] ?? 'pending') === 'synced' ? 'Senkronize' : 
                                        (($listing['sync_status'] ?? 'pending') === 'failed' ? 'Başarısız' : 'Bekliyor');
                                    ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <?php if (!empty($listing['last_sync_at'])): ?>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        <?php echo date('d.m.Y H:i', strtotime($listing['last_sync_at'])); ?>
                                    </p>
                                <?php else: ?>
                                    <p class="text-xs text-gray-400">-</p>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <button onclick="syncListing(<?php echo $listing['id']; ?>)" class="px-3 py-1 text-xs bg-primary text-white rounded hover:bg-primary/90 transition-colors">
                                    Senkronize Et
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Toplam <?php echo $total; ?> ilan, Sayfa <?php echo $page; ?> / <?php echo $totalPages; ?>
                </p>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                        <a href="<?php echo admin_url('module/emlakjet/listings?page=' . ($page - 1) . '&status=' . urlencode($status) . '&search=' . urlencode($search)); ?>" class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            Önceki
                        </a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo admin_url('module/emlakjet/listings?page=' . ($page + 1) . '&status=' . urlencode($status) . '&search=' . urlencode($search)); ?>" class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            Sonraki
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.listing-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
});

function syncListing(listingId) {
    if (!confirm('Bu ilanı senkronize etmek istediğinize emin misiniz?')) {
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

function bulkSync() {
    const checkboxes = document.querySelectorAll('.listing-checkbox:checked');
    const listingIds = Array.from(checkboxes).map(cb => cb.value);
    
    if (listingIds.length === 0) {
        alert('Lütfen en az bir ilan seçin');
        return;
    }
    
    if (!confirm(listingIds.length + ' ilanı senkronize etmek istediğinize emin misiniz?')) {
        return;
    }
    
    const formData = new URLSearchParams();
    formData.append('action', 'sync');
    formData.append('direction', 'push');
    listingIds.forEach(id => formData.append('listing_ids[]', id));
    
    fetch('<?php echo admin_url('module/emlakjet/sync'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
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
