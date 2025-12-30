<?php include __DIR__ . '/../snippets/header.php'; ?>
<?php
// Controller'dan gelen değişkenler: $user, $agreements, $types, $typeFilter, $statusFilter, $stats, $message, $messageType
$typeFilter = $typeFilter ?? 'all';
$statusFilter = $statusFilter ?? 'all';
$stats = $stats ?? ['all' => 0, 'published' => 0, 'draft' => 0];
$types = $types ?? [];
?>
<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php 
        $currentPage = 'agreements';
        include __DIR__ . '/../snippets/sidebar.php'; 
        ?>

        <!-- Content Area with Header -->
        <div class="flex-1 flex flex-col lg:ml-64">
            <!-- Top Header -->
            <?php include __DIR__ . '/../snippets/top-header.php'; ?>

            <!-- Main Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b] overflow-y-auto">
            <div class="layout-content-container flex flex-col w-full mx-auto max-w-7xl">
                
                <!-- Header -->
                <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div class="flex flex-col gap-2">
                        <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Sözleşmeler</h1>
                        <p class="text-gray-500 dark:text-gray-400 text-base">Gizlilik politikası, KVKK ve diğer yasal metinleri yönetin.</p>
                    </div>
                    <a href="<?php echo admin_url('agreements/create'); ?>" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        <span class="material-symbols-outlined text-xl">add</span>
                        <span class="text-sm font-medium">Yeni Sözleşme</span>
                    </a>
                </header>

                <!-- Mesaj -->
                <?php if (!empty($message)): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 text-red-800 dark:text-red-200'; ?>">
                    <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
                </div>
                <?php endif; ?>

                <!-- Durum Filtreleri -->
                <div class="mb-6 flex flex-wrap gap-2 overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0 scrollbar-hide pb-2">
                    <a href="<?php echo admin_url('agreements', ['status' => 'all']); ?>" class="px-3 py-1.5 text-sm rounded-lg transition-colors whitespace-nowrap <?php echo $statusFilter === 'all' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'; ?>">
                        Tümü <span class="ml-1 opacity-70">(<?php echo $stats['all']; ?>)</span>
                    </a>
                    <a href="<?php echo admin_url('agreements', ['status' => 'published']); ?>" class="px-3 py-1.5 text-sm rounded-lg transition-colors whitespace-nowrap <?php echo $statusFilter === 'published' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'; ?>">
                        Yayında <span class="ml-1 opacity-70">(<?php echo $stats['published']; ?>)</span>
                    </a>
                    <a href="<?php echo admin_url('agreements', ['status' => 'draft']); ?>" class="px-3 py-1.5 text-sm rounded-lg transition-colors whitespace-nowrap <?php echo $statusFilter === 'draft' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'; ?>">
                        Taslak <span class="ml-1 opacity-70">(<?php echo $stats['draft']; ?>)</span>
                    </a>
                </div>

                <!-- Tür Filtreleri -->
                <div class="mb-6 flex flex-wrap gap-2 overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0 scrollbar-hide pb-2">
                    <span class="text-gray-500 dark:text-gray-400 text-sm py-1.5 whitespace-nowrap">Türe göre:</span>
                    <a href="<?php echo admin_url('agreements', ['type' => 'all', 'status' => $statusFilter]); ?>" class="px-3 py-1.5 text-sm rounded-lg transition-colors whitespace-nowrap <?php echo $typeFilter === 'all' ? 'bg-blue-500 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'; ?>">
                        Tümü
                    </a>
                    <?php foreach ($types as $typeKey => $typeLabel): ?>
                    <a href="<?php echo admin_url('agreements', ['type' => $typeKey, 'status' => $statusFilter]); ?>" class="px-3 py-1.5 text-sm rounded-lg transition-colors whitespace-nowrap <?php echo $typeFilter === $typeKey ? 'bg-blue-500 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'; ?>">
                        <?php echo esc_html($typeLabel); ?>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- Sözleşmeler Listesi -->
                <section class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 overflow-hidden">
                    <?php if (empty($agreements)): ?>
                    <div class="p-12 text-center">
                        <span class="material-symbols-outlined text-gray-400 text-6xl mb-4">gavel</span>
                        <p class="text-gray-500 text-lg mb-2">Henüz sözleşme yok</p>
                        <a href="<?php echo admin_url('agreements/create'); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                            <span class="material-symbols-outlined">add</span>
                            <span>İlk Sözleşmeyi Oluştur</span>
                        </a>
                    </div>
                    <?php else: ?>
                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Başlık</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Tür</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Versiyon</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Durum</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Son Güncelleme</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                <?php foreach ($agreements as $agreement): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                                                <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400">gavel</span>
                                            </div>
                                            <div>
                                                <p class="text-gray-900 dark:text-white font-medium"><?php echo esc_html($agreement['title']); ?></p>
                                                <p class="text-gray-500 text-xs truncate max-w-xs">/sozlesmeler/<?php echo esc_html($agreement['slug']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                        $typeColors = [
                                            'privacy' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                            'kvkk' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                            'terms' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
                                            'cookies' => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-300',
                                            'other' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                        ];
                                        $agreementType = $agreement['type'] ?? 'other';
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $typeColors[$agreementType] ?? $typeColors['other']; ?>">
                                            <?php echo esc_html($types[$agreementType] ?? 'Diğer'); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            v<?php echo esc_html($agreement['version'] ?? 1); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($agreement['status'] === 'published'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">Yayında</span>
                                        <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">Taslak</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-gray-600 dark:text-gray-400 text-sm">
                                            <?php echo date('d.m.Y', strtotime($agreement['updated_at'])); ?>
                                        </p>
                                        <p class="text-gray-400 text-xs">
                                            <?php echo date('H:i', strtotime($agreement['updated_at'])); ?>
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="<?php echo admin_url('agreements/edit/' . $agreement['id']); ?>" class="p-2 text-gray-600 hover:text-primary hover:bg-primary/10 rounded-lg" title="Düzenle">
                                                <span class="material-symbols-outlined text-xl">edit</span>
                                            </a>
                                            <a href="<?php echo admin_url('agreements/versions/' . $agreement['id']); ?>" class="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg" title="Versiyon Geçmişi">
                                                <span class="material-symbols-outlined text-xl">history</span>
                                            </a>
                                            <a href="<?php echo admin_url('agreements/toggle/' . $agreement['id']); ?>" class="p-2 text-gray-600 hover:text-primary hover:bg-primary/10 rounded-lg" title="<?php echo $agreement['status'] === 'published' ? 'Taslağa Al' : 'Yayınla'; ?>">
                                                <span class="material-symbols-outlined text-xl"><?php echo $agreement['status'] === 'published' ? 'unpublished' : 'publish'; ?></span>
                                            </a>
                                            <a href="<?php echo admin_url('agreements/delete/' . $agreement['id']); ?>" class="p-2 text-gray-600 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg" onclick="return confirm('Sözleşmeyi silmek istediğinize emin misiniz? Bu işlem geri alınamaz.');" title="Sil">
                                                <span class="material-symbols-outlined text-xl">delete</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Mobile Card View -->
                    <div class="md:hidden space-y-4 p-4">
                        <?php foreach ($agreements as $agreement): 
                            $typeColors = [
                                'privacy' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                'kvkk' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                'terms' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
                                'cookies' => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-300',
                                'other' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                            ];
                            $agreementType = $agreement['type'] ?? 'other';
                        ?>
                        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-white/10 p-4">
                            <div class="flex items-start gap-3 mb-3">
                                <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400 text-xl">gavel</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-gray-900 dark:text-white font-medium mb-1 line-clamp-2"><?php echo esc_html($agreement['title']); ?></h3>
                                    <p class="text-gray-500 text-xs truncate">/sozlesmeler/<?php echo esc_html($agreement['slug']); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $typeColors[$agreementType] ?? $typeColors['other']; ?>">
                                    <?php echo esc_html($types[$agreementType] ?? 'Diğer'); ?>
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    v<?php echo esc_html($agreement['version'] ?? 1); ?>
                                </span>
                                <?php if ($agreement['status'] === 'published'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">Yayında</span>
                                <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">Taslak</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-3">
                                <span><?php echo date('d.m.Y H:i', strtotime($agreement['updated_at'])); ?></span>
                            </div>
                            
                            <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-200 dark:border-white/10">
                                <a href="<?php echo admin_url('agreements/edit/' . $agreement['id']); ?>" class="flex-1 flex items-center justify-center gap-1 px-3 py-2 text-sm text-primary hover:bg-primary/10 rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                    <span>Düzenle</span>
                                </a>
                                <a href="<?php echo admin_url('agreements/versions/' . $agreement['id']); ?>" class="px-3 py-2 text-sm text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors" title="Versiyon Geçmişi">
                                    <span class="material-symbols-outlined text-lg">history</span>
                                </a>
                                <a href="<?php echo admin_url('agreements/toggle/' . $agreement['id']); ?>" class="px-3 py-2 text-sm text-gray-600 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="<?php echo $agreement['status'] === 'published' ? 'Taslağa Al' : 'Yayınla'; ?>">
                                    <span class="material-symbols-outlined text-lg"><?php echo $agreement['status'] === 'published' ? 'unpublished' : 'publish'; ?></span>
                                </a>
                                <a href="<?php echo admin_url('agreements/delete/' . $agreement['id']); ?>" class="px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" onclick="return confirm('Sözleşmeyi silmek istediğinize emin misiniz? Bu işlem geri alınamaz.');" title="Sil">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </section>

            </div>
            </main>
        </div>
    </div>
</div>
</body>
</html>

