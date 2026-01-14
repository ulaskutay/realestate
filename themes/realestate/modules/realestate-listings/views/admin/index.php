<?php 
// Admin snippet'lerini mutlak yol ile yükle
$rootPath = $_SERVER['DOCUMENT_ROOT'];
include $rootPath . '/app/views/admin/snippets/header.php'; 
?>

<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php 
        $currentPage = 'realestate-listings';
        include $rootPath . '/app/views/admin/snippets/sidebar.php'; 
        ?>

        <!-- Content Area with Header -->
        <div class="flex-1 flex flex-col lg:ml-64">
            <!-- Top Header -->
            <?php include $rootPath . '/app/views/admin/snippets/top-header.php'; ?>

            <!-- Main Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b] overflow-y-auto">
                <div class="layout-content-container flex flex-col w-full mx-auto max-w-7xl">

    <div class="flex justify-between items-center mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight"><?php echo esc_html($title); ?></h1>
                <p class="text-gray-500 dark:text-gray-400 text-base mt-1">Tüm emlak ilanlarını yönetin</p>
            </div>
            <a href="<?php echo admin_url('module/realestate-listings/create'); ?>" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-xl">add</span>
                <span>Yeni İlan Ekle</span>
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="mb-4 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <?php echo esc_html($message); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">İlan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Konum</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fiyat</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tip</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Durum</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">İşlemler</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-[#1e293b] divide-y divide-gray-200 dark:divide-gray-700">
                <?php if (empty($listings)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center gap-2">
                                <span class="material-symbols-outlined text-4xl text-gray-300 dark:text-gray-600">home</span>
                                <p class="text-sm font-medium">Henüz ilan eklenmemiş</p>
                                <a href="<?php echo admin_url('module/realestate-listings/create'); ?>" class="text-primary hover:text-primary/80 text-sm mt-2">İlk ilanı ekleyin</a>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($listings as $listing): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <?php if (!empty($listing['featured_image'])): ?>
                                        <img src="<?php echo esc_url($listing['featured_image']); ?>" alt="" class="h-12 w-12 rounded object-cover mr-3">
                                    <?php endif; ?>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white"><?php echo esc_html($listing['title']); ?></div>
                                        <?php if ($listing['is_featured']): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary/10 text-primary mt-1">Öne Çıkan</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo esc_html($listing['location'] ?: '-'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">₺<?php echo number_format($listing['price'], 0, ',', '.'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <?php 
                                $types = [
                                    'house' => 'Müstakil Ev',
                                    'apartment' => 'Daire',
                                    'villa' => 'Villa',
                                    'commercial' => 'Ticari',
                                    'land' => 'Arsa'
                                ];
                                echo esc_html($types[$listing['property_type']] ?? ucfirst($listing['property_type'])); 
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full font-medium <?php echo $listing['status'] === 'published' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300'; ?>">
                                    <?php echo $listing['status'] === 'published' ? 'Yayında' : 'Taslak'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-3">
                                    <a href="<?php echo admin_url('module/realestate-listings/edit/' . $listing['id']); ?>" class="text-primary hover:text-primary/80 transition-colors flex items-center gap-1">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                        <span>Düzenle</span>
                                    </a>
                                    <form action="<?php echo admin_url('module/realestate-listings/delete/' . $listing['id']); ?>" method="post" class="inline" onsubmit="return confirm('Bu ilanı silmek istediğinizden emin misiniz?');">
                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors flex items-center gap-1">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                            <span>Sil</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
                </div>
            </main>
        </div>
    </div>
</div>

<?php include $rootPath . '/app/views/admin/snippets/footer.php'; ?>