<?php 
// Admin snippet'lerini mutlak yol ile yükle
$rootPath = $_SERVER['DOCUMENT_ROOT'];
include $rootPath . '/app/views/admin/snippets/header.php'; 
?>

<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php 
        $currentPage = 'quote-request';
        include $rootPath . '/app/views/admin/snippets/sidebar.php'; 
        ?>

        <!-- Content Area with Header -->
        <div class="flex-1 flex flex-col lg:ml-64">
            <!-- Top Header -->
            <?php include $rootPath . '/app/views/admin/snippets/top-header.php'; ?>

            <!-- Main Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b] overflow-y-auto">
            <div class="layout-content-container flex flex-col w-full mx-auto max-w-7xl">
                
                <!-- Header -->
                <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div class="flex flex-col gap-2">
                        <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Teklif Al Sayfaları</h1>
                        <p class="text-gray-500 dark:text-gray-400 text-base">Teklif alma sayfalarınızı yönetin.</p>
                    </div>
                    <a href="<?php echo admin_url('module/quote-request/create'); ?>" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        <span class="material-symbols-outlined text-xl">add</span>
                        <span class="text-sm font-medium">Yeni Teklif Al Sayfası</span>
                    </a>
                </header>

                <!-- Mesaj -->
                <?php if (!empty($message)): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 text-red-800 dark:text-red-200'; ?>">
                    <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
                </div>
                <?php endif; ?>

                <div class="bg-white dark:bg-[#1e293b] rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <?php if (empty($pages)): ?>
                            <div class="text-center py-12">
                                <div class="material-symbols-outlined text-6xl text-gray-400 dark:text-gray-600 mb-4">description</div>
                                <p class="text-gray-500 dark:text-gray-400 mb-4">Henüz teklif al sayfası oluşturulmamış.</p>
                                <a href="<?php echo admin_url('module/quote-request/create'); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                    <span class="material-symbols-outlined text-xl">add</span>
                                    <span>İlk Teklif Al Sayfasını Oluştur</span>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-50 dark:bg-[#0f172a] border-b border-gray-200 dark:border-gray-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Başlık</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Slug</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Form</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Durum</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Oluşturulma</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-[#1e293b] divide-y divide-gray-200 dark:divide-gray-700">
                                        <?php foreach ($pages as $page): ?>
                                            <?php
                                            $formId = $page['custom_fields']['quote_form_id'] ?? '';
                                            $formSlug = $page['custom_fields']['quote_form_slug'] ?? 'teklif-al';
                                            $formName = 'Form: ' . $formSlug;
                                            
                                            if ($formId) {
                                                try {
                                                    $db = Database::getInstance();
                                                    $form = $db->fetch("SELECT name FROM `forms` WHERE id = ?", [$formId]);
                                                    if ($form) {
                                                        $formName = $form['name'];
                                                    }
                                                } catch (Exception $e) {
                                                    // Form bulunamadı
                                                }
                                            }
                                            ?>
                                            <tr class="hover:bg-gray-50 dark:hover:bg-[#0f172a] transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        <?php echo htmlspecialchars($page['title']); ?>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-gray-800 dark:text-gray-200">
                                                        <?php echo htmlspecialchars($page['slug']); ?>
                                                    </code>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                        <?php echo htmlspecialchars($formName); ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php if ($page['status'] === 'published'): ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Yayında</span>
                                                    <?php elseif ($page['status'] === 'draft'): ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200">Taslak</span>
                                                    <?php elseif ($page['status'] === 'trash'): ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Çöp</span>
                                                    <?php else: ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                            <?php echo htmlspecialchars($page['status']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    <?php echo date('d.m.Y H:i', strtotime($page['created_at'])); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <a href="<?php echo site_url($page['slug']); ?>" 
                                                           target="_blank" 
                                                           class="p-2 text-gray-600 dark:text-gray-400 hover:text-primary hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors" 
                                                           title="Önizle">
                                                            <span class="material-symbols-outlined text-xl">visibility</span>
                                                        </a>
                                                        <a href="<?php echo admin_url('module/quote-request/edit/' . $page['id']); ?>" 
                                                           class="p-2 text-gray-600 dark:text-gray-400 hover:text-primary hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors" 
                                                           title="Düzenle">
                                                            <span class="material-symbols-outlined text-xl">edit</span>
                                                        </a>
                                                        <form method="POST" 
                                                              action="<?php echo admin_url('module/quote-request/delete/' . $page['id']); ?>" 
                                                              class="inline"
                                                              onsubmit="return confirm('Bu sayfayı silmek istediğinizden emin misiniz?');">
                                                            <button type="submit" 
                                                                    class="p-2 text-gray-600 dark:text-gray-400 hover:text-red-600 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors" 
                                                                    title="Sil">
                                                                <span class="material-symbols-outlined text-xl">delete</span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            </main>
        </div>
    </div>
</div>

<?php include $rootPath . '/app/views/admin/snippets/footer.php'; ?>
