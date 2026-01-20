<?php
// Leads list view
$leads = $leads ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;
$search = $search ?? '';
$filters = $filters ?? [];
?>

<!-- Header -->
<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div class="flex flex-col gap-2">
        <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Leadler</h1>
        <p class="text-gray-500 dark:text-gray-400 text-base">Tüm leadleri görüntüleyin ve yönetin.</p>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo admin_url('module/crm/leads_kanban'); ?>" class="flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
            <span class="material-symbols-outlined text-xl">view_kanban</span>
            <span class="text-sm font-medium">Kanban</span>
        </a>
        <a href="<?php echo admin_url('module/crm/lead_create'); ?>" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
            <span class="material-symbols-outlined text-xl">add</span>
            <span class="text-sm font-medium">Yeni Lead</span>
        </a>
    </div>
</header>

<!-- Arama ve Filtreler -->
<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <form method="GET" action="<?php echo admin_url('module/crm/leads'); ?>" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Arama -->
            <div class="md:col-span-2">
                <input type="text" name="search" value="<?php echo esc_attr($search); ?>" placeholder="İsim, telefon, e-posta, lokasyon ara..." class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            
            <!-- Durum Filtresi -->
            <div>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">Tüm Durumlar</option>
                    <option value="new" <?php echo ($filters['status'] ?? '') === 'new' ? 'selected' : ''; ?>>Yeni</option>
                    <option value="contacted" <?php echo ($filters['status'] ?? '') === 'contacted' ? 'selected' : ''; ?>>İletişimde</option>
                    <option value="quoted" <?php echo ($filters['status'] ?? '') === 'quoted' ? 'selected' : ''; ?>>Teklif Verildi</option>
                    <option value="closed" <?php echo ($filters['status'] ?? '') === 'closed' ? 'selected' : ''; ?>>Kapandı</option>
                    <option value="cancelled" <?php echo ($filters['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>İptal</option>
                </select>
            </div>
            
            <!-- Kaynak Filtresi -->
            <div>
                <select name="source" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">Tüm Kaynaklar</option>
                    <option value="meta" <?php echo ($filters['source'] ?? '') === 'meta' ? 'selected' : ''; ?>>Meta/Facebook</option>
                    <option value="form" <?php echo ($filters['source'] ?? '') === 'form' ? 'selected' : ''; ?>>Form</option>
                    <option value="manual" <?php echo ($filters['source'] ?? '') === 'manual' ? 'selected' : ''; ?>>Manuel</option>
                </select>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Tarih Başlangıç -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Başlangıç Tarihi</label>
                <input type="date" name="date_from" value="<?php echo esc_attr($filters['date_from'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            
            <!-- Tarih Bitiş -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bitiş Tarihi</label>
                <input type="date" name="date_to" value="<?php echo esc_attr($filters['date_to'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            
            <!-- Butonlar -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                    <span class="material-symbols-outlined text-xl align-middle">search</span>
                    Ara
                </button>
                <a href="<?php echo admin_url('module/crm/leads'); ?>" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    <span class="material-symbols-outlined text-xl align-middle">clear</span>
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Leadler Tablosu -->
<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">İsim</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">İletişim</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Emlak Bilgileri</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kaynak</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Durum</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tarih</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">İşlemler</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php if (empty($leads)): ?>
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            Lead bulunamadı
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($leads as $lead): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-4 py-3">
                                <a href="<?php echo admin_url('module/crm/lead_view/' . $lead['id']); ?>" class="font-medium text-gray-900 dark:text-white hover:text-primary">
                                    <?php echo esc_html($lead['name']); ?>
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                <?php if (!empty($lead['phone'])): ?>
                                    <div class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">phone</span>
                                        <?php echo esc_html($lead['phone']); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($lead['email'])): ?>
                                    <div class="flex items-center gap-1 mt-1">
                                        <span class="material-symbols-outlined text-xs">email</span>
                                        <?php echo esc_html($lead['email']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                <?php if (!empty($lead['property_type'])): ?>
                                    <div><?php echo esc_html($lead['property_type']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($lead['location'])): ?>
                                    <div class="text-xs text-gray-500 dark:text-gray-500"><?php echo esc_html($lead['location']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($lead['budget'])): ?>
                                    <div class="text-xs text-gray-500 dark:text-gray-500"><?php echo esc_html($lead['budget']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                    <?php 
                                    $sourceLabels = ['meta' => 'Meta', 'form' => 'Form', 'manual' => 'Manuel'];
                                    echo $sourceLabels[$lead['source']] ?? $lead['source'];
                                    ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full <?php 
                                    echo $lead['status'] === 'new' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 
                                        ($lead['status'] === 'contacted' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 
                                        ($lead['status'] === 'quoted' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400' : 
                                        ($lead['status'] === 'closed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 
                                        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'))); 
                                ?>">
                                    <?php 
                                    $statusLabels = [
                                        'new' => 'Yeni',
                                        'contacted' => 'İletişimde',
                                        'quoted' => 'Teklif Verildi',
                                        'closed' => 'Kapandı',
                                        'cancelled' => 'İptal'
                                    ];
                                    echo $statusLabels[$lead['status']] ?? $lead['status'];
                                    ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                <?php echo turkish_date($lead['created_at'], 'short'); ?>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?php echo admin_url('module/crm/lead_view/' . $lead['id']); ?>" class="p-1 text-gray-600 dark:text-gray-400 hover:text-primary transition-colors" title="Görüntüle">
                                        <span class="material-symbols-outlined text-xl">visibility</span>
                                    </a>
                                    <a href="<?php echo admin_url('module/crm/lead_edit/' . $lead['id']); ?>" class="p-1 text-gray-600 dark:text-gray-400 hover:text-primary transition-colors" title="Düzenle">
                                        <span class="material-symbols-outlined text-xl">edit</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Toplam <?php echo $total; ?> lead, Sayfa <?php echo $page; ?> / <?php echo $totalPages; ?>
            </div>
            <div class="flex gap-2">
                <?php if ($page > 1): ?>
                    <a href="<?php echo admin_url('module/crm/leads?' . http_build_query(array_merge($_GET, ['page' => $page - 1]))); ?>" class="px-3 py-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        Önceki
                    </a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="<?php echo admin_url('module/crm/leads?' . http_build_query(array_merge($_GET, ['page' => $page + 1]))); ?>" class="px-3 py-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        Sonraki
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
