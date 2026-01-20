<?php
// Dashboard view
$stats = $stats ?? ['total' => 0, 'new' => 0, 'contacted' => 0, 'quoted' => 0, 'closed' => 0, 'cancelled' => 0];
$recentLeads = $recentLeads ?? [];
$sourceStats = $sourceStats ?? [];
$statusStats = $statusStats ?? [];
?>

<!-- Header -->
<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div class="flex flex-col gap-2">
        <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">CRM Dashboard</h1>
        <p class="text-gray-500 dark:text-gray-400 text-base">Lead yönetimi ve takip sistemi.</p>
    </div>
    <a href="<?php echo admin_url('module/crm/lead_create'); ?>" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
        <span class="material-symbols-outlined text-xl">add</span>
        <span class="text-sm font-medium">Yeni Lead</span>
    </a>
</header>

<!-- İstatistikler -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Toplam Lead</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $stats['total']; ?></p>
            </div>
            <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">people</span>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Yeni</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $stats['new']; ?></p>
            </div>
            <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                <span class="material-symbols-outlined text-yellow-600 dark:text-yellow-400">new_releases</span>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">İletişimde</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $stats['contacted']; ?></p>
            </div>
            <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                <span class="material-symbols-outlined text-green-600 dark:text-green-400">call</span>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Teklif Verildi</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $stats['quoted']; ?></p>
            </div>
            <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                <span class="material-symbols-outlined text-purple-600 dark:text-purple-400">description</span>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Kapandı</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $stats['closed']; ?></p>
            </div>
            <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400">check_circle</span>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">İptal</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $stats['cancelled']; ?></p>
            </div>
            <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                <span class="material-symbols-outlined text-red-600 dark:text-red-400">cancel</span>
            </div>
        </div>
    </div>
</div>

<!-- İki Sütun Layout -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Son Leadler -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Son Leadler</h2>
        </div>
        <div class="p-4">
            <?php if (empty($recentLeads)): ?>
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Henüz lead yok</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($recentLeads as $lead): ?>
                        <a href="<?php echo admin_url('module/crm/lead_view/' . $lead['id']); ?>" class="block p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 dark:text-white"><?php echo esc_html($lead['name']); ?></p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        <?php if (!empty($lead['phone'])): ?>
                                            <span class="material-symbols-outlined text-xs align-middle">phone</span>
                                            <?php echo esc_html($lead['phone']); ?>
                                        <?php endif; ?>
                                        <?php if (!empty($lead['email'])): ?>
                                            <span class="material-symbols-outlined text-xs align-middle ml-3">email</span>
                                            <?php echo esc_html($lead['email']); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="ml-4">
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
                                </div>
                            </div>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                                <?php echo turkish_date($lead['created_at'], 'short'); ?>
                            </p>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4 text-center">
                    <a href="<?php echo admin_url('module/crm/leads'); ?>" class="text-sm text-primary hover:underline">Tümünü Gör</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Kaynak İstatistikleri -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Lead Kaynakları</h2>
        </div>
        <div class="p-4">
            <?php if (empty($sourceStats)): ?>
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Veri yok</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php 
                    $sourceLabels = ['meta' => 'Meta/Facebook', 'form' => 'Form', 'manual' => 'Manuel'];
                    foreach ($sourceStats as $stat): 
                    ?>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400"><?php echo $sourceLabels[$stat['source']] ?? $stat['source']; ?></span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo $stat['count']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Hızlı Erişim -->
<div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
    <a href="<?php echo admin_url('module/crm/leads'); ?>" class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 hover:border-primary transition-colors">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-primary/10 rounded-lg">
                <span class="material-symbols-outlined text-primary">list</span>
            </div>
            <div>
                <p class="font-medium text-gray-900 dark:text-white">Lead Listesi</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Tüm leadleri görüntüle</p>
            </div>
        </div>
    </a>
    
    <a href="<?php echo admin_url('module/crm/leads_kanban'); ?>" class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 hover:border-primary transition-colors">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-primary/10 rounded-lg">
                <span class="material-symbols-outlined text-primary">view_kanban</span>
            </div>
            <div>
                <p class="font-medium text-gray-900 dark:text-white">Kanban Görünümü</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Pipeline görünümü</p>
            </div>
        </div>
    </a>
    
    <a href="<?php echo admin_url('module/crm/settings'); ?>" class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 hover:border-primary transition-colors">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-primary/10 rounded-lg">
                <span class="material-symbols-outlined text-primary">settings</span>
            </div>
            <div>
                <p class="font-medium text-gray-900 dark:text-white">Ayarlar</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Modül ayarları</p>
            </div>
        </div>
    </a>
</div>
