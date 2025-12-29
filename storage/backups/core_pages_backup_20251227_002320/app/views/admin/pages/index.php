<?php include __DIR__ . '/../snippets/header.php'; ?>
<?php
// Controller'dan gelen değişkenler: $user, $pages, $statusFilter, $stats, $message, $messageType
$statusFilter = $statusFilter ?? 'all';
$stats = $stats ?? ['all' => 0, 'published' => 0, 'draft' => 0, 'trash' => 0];
$pages = array_filter($pages ?? [], function($page) {
    return isset($page['type']) && $page['type'] === 'page';
});
?>
<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php 
        $currentPage = 'pages';
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
                        <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Sayfalar</h1>
                        <p class="text-gray-500 dark:text-gray-400 text-base">Statik sayfalarınızı yönetin.</p>
                    </div>
                    <a href="<?php echo admin_url('pages/create'); ?>" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        <span class="material-symbols-outlined text-xl">add</span>
                        <span class="text-sm font-medium">Yeni Sayfa</span>
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
                    <a href="<?php echo admin_url('pages', ['status' => 'all']); ?>" class="px-3 py-1.5 text-sm rounded-lg transition-colors whitespace-nowrap <?php echo $statusFilter === 'all' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'; ?>">
                        Tümü <span class="ml-1 opacity-70">(<?php echo $stats['all']; ?>)</span>
                    </a>
                    <a href="<?php echo admin_url('pages', ['status' => 'published']); ?>" class="px-3 py-1.5 text-sm rounded-lg transition-colors whitespace-nowrap <?php echo $statusFilter === 'published' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'; ?>">
                        Yayında <span class="ml-1 opacity-70">(<?php echo $stats['published']; ?>)</span>
                    </a>
                    <a href="<?php echo admin_url('pages', ['status' => 'draft']); ?>" class="px-3 py-1.5 text-sm rounded-lg transition-colors whitespace-nowrap <?php echo $statusFilter === 'draft' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'; ?>">
                        Taslak <span class="ml-1 opacity-70">(<?php echo $stats['draft']; ?>)</span>
                    </a>
                    <a href="<?php echo admin_url('pages', ['status' => 'trash']); ?>" class="px-3 py-1.5 text-sm rounded-lg transition-colors whitespace-nowrap <?php echo $statusFilter === 'trash' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'; ?>">
                        Çöp <span class="ml-1 opacity-70">(<?php echo $stats['trash']; ?>)</span>
                    </a>
                </div>

                <!-- Sayfalar Listesi -->
                <section class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 overflow-hidden">
                    <?php if (empty($pages)): ?>
                    <div class="p-12 text-center">
                        <span class="material-symbols-outlined text-gray-400 text-6xl mb-4">description</span>
                        <p class="text-gray-500 text-lg mb-2">Henüz sayfa yok</p>
                        <a href="<?php echo admin_url('pages/create'); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                            <span class="material-symbols-outlined">add</span>
                            <span>İlk Sayfayı Oluştur</span>
                        </a>
                    </div>
                    <?php else: ?>
                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Başlık</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Yazar</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Durum</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Tarih</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                <?php foreach ($pages as $page): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <?php if (!empty($page['featured_image'])): ?>
                                            <img src="<?php echo esc_url($page['featured_image']); ?>" alt="" class="w-12 h-12 object-cover rounded-lg">
                                            <?php else: ?>
                                            <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                                <span class="material-symbols-outlined text-gray-400">description</span>
                                            </div>
                                            <?php endif; ?>
                                            <div>
                                                <p class="text-gray-900 dark:text-white font-medium"><?php echo esc_html($page['title']); ?></p>
                                                <p class="text-gray-500 text-xs truncate max-w-xs"><?php echo esc_html($page['slug']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-gray-600 dark:text-gray-400 text-sm"><?php echo esc_html($page['author_name'] ?? 'Bilinmiyor'); ?></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                        $statusColors = [
                                            'published' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                            'draft' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                            'scheduled' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                            'trash' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
                                        ];
                                        $statusNames = [
                                            'published' => 'Yayında',
                                            'draft' => 'Taslak',
                                            'scheduled' => 'Zamanlanmış',
                                            'trash' => 'Çöp'
                                        ];
                                        $pageStatus = $page['status'] ?? 'draft';
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusColors[$pageStatus] ?? $statusColors['draft']; ?>">
                                            <?php echo $statusNames[$pageStatus] ?? 'Taslak'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-gray-600 dark:text-gray-400 text-sm">
                                            <?php 
                                            $date = $page['published_at'] ?? $page['created_at'];
                                            echo date('d.m.Y', strtotime($date)); 
                                            ?>
                                        </p>
                                        <p class="text-gray-400 text-xs">
                                            <?php echo date('H:i', strtotime($date)); ?>
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <?php if ($page['status'] === 'trash'): ?>
                                            <a href="<?php echo admin_url('pages/restore/' . $page['id']); ?>" class="p-2 text-gray-600 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg" title="Geri Yükle">
                                                <span class="material-symbols-outlined text-xl">restore</span>
                                            </a>
                                            <?php else: ?>
                                            <a href="<?php echo admin_url('pages/edit/' . $page['id']); ?>" class="p-2 text-gray-600 hover:text-primary hover:bg-primary/10 rounded-lg" title="Düzenle">
                                                <span class="material-symbols-outlined text-xl">edit</span>
                                            </a>
                                            <a href="<?php echo admin_url('pages/duplicate/' . $page['id']); ?>" class="p-2 text-gray-600 hover:text-primary hover:bg-primary/10 rounded-lg" title="Sayfayı Kopyala">
                                                <span class="material-symbols-outlined text-xl">content_copy</span>
                                            </a>
                                            <a href="<?php echo admin_url('pages/toggle/' . $page['id']); ?>" class="p-2 text-gray-600 hover:text-primary hover:bg-primary/10 rounded-lg" title="<?php echo $page['status'] === 'published' ? 'Taslağa Al' : 'Yayınla'; ?>">
                                                <span class="material-symbols-outlined text-xl"><?php echo $page['status'] === 'published' ? 'unpublished' : 'publish'; ?></span>
                                            </a>
                                            <?php endif; ?>
                                            <a href="<?php echo admin_url('pages/delete/' . $page['id']); ?>" class="p-2 text-gray-600 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg" onclick="return confirm('<?php echo $page['status'] === 'trash' ? 'Kalıcı olarak silmek istediğinize emin misiniz?' : 'Çöpe taşımak istediğinize emin misiniz?'; ?>');" title="<?php echo $page['status'] === 'trash' ? 'Kalıcı Sil' : 'Çöpe Taşı'; ?>">
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
                        <?php foreach ($pages as $page): 
                            $statusColors = [
                                'published' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                'draft' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                'scheduled' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                'trash' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
                            ];
                            $statusNames = [
                                'published' => 'Yayında',
                                'draft' => 'Taslak',
                                'scheduled' => 'Zamanlanmış',
                                'trash' => 'Çöp'
                            ];
                            $pageStatus = $page['status'] ?? 'draft';
                            $date = $page['published_at'] ?? $page['created_at'];
                        ?>
                        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-white/10 p-4">
                            <div class="flex items-start gap-3 mb-3">
                                <?php if (!empty($page['featured_image'])): ?>
                                <img src="<?php echo esc_url($page['featured_image']); ?>" alt="" class="w-16 h-16 object-cover rounded-lg flex-shrink-0">
                                <?php else: ?>
                                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <span class="material-symbols-outlined text-gray-400 text-2xl">description</span>
                                </div>
                                <?php endif; ?>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-gray-900 dark:text-white font-medium mb-1 line-clamp-2"><?php echo esc_html($page['title']); ?></h3>
                                    <p class="text-gray-500 text-xs truncate"><?php echo esc_html($page['slug']); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusColors[$pageStatus] ?? $statusColors['draft']; ?>">
                                    <?php echo $statusNames[$pageStatus] ?? 'Taslak'; ?>
                                </span>
                            </div>
                            
                            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-3">
                                <span><?php echo esc_html($page['author_name'] ?? 'Bilinmiyor'); ?></span>
                                <span><?php echo date('d.m.Y H:i', strtotime($date)); ?></span>
                            </div>
                            
                            <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-200 dark:border-white/10">
                                <?php if ($page['status'] === 'trash'): ?>
                                <a href="<?php echo admin_url('pages/restore/' . $page['id']); ?>" class="flex-1 flex items-center justify-center gap-1 px-3 py-2 text-sm text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-lg">restore</span>
                                    <span>Geri Yükle</span>
                                </a>
                                <?php else: ?>
                                <a href="<?php echo admin_url('pages/edit/' . $page['id']); ?>" class="flex-1 flex items-center justify-center gap-1 px-3 py-2 text-sm text-primary hover:bg-primary/10 rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                    <span>Düzenle</span>
                                </a>
                                <a href="<?php echo admin_url('pages/duplicate/' . $page['id']); ?>" class="px-3 py-2 text-sm text-gray-600 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Sayfayı Kopyala">
                                    <span class="material-symbols-outlined text-lg">content_copy</span>
                                </a>
                                <a href="<?php echo admin_url('pages/toggle/' . $page['id']); ?>" class="px-3 py-2 text-sm text-gray-600 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="<?php echo $page['status'] === 'published' ? 'Taslağa Al' : 'Yayınla'; ?>">
                                    <span class="material-symbols-outlined text-lg"><?php echo $page['status'] === 'published' ? 'unpublished' : 'publish'; ?></span>
                                </a>
                                <?php endif; ?>
                                <a href="<?php echo admin_url('pages/delete/' . $page['id']); ?>" class="px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" onclick="return confirm('<?php echo $page['status'] === 'trash' ? 'Kalıcı olarak silmek istediğinize emin misiniz?' : 'Çöpe taşımak istediğinize emin misiniz?'; ?>');" title="<?php echo $page['status'] === 'trash' ? 'Kalıcı Sil' : 'Çöpe Taşı'; ?>">
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

