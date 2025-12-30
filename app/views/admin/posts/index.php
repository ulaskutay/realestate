<?php include __DIR__ . '/../snippets/header.php'; ?>
<?php
// Controller'dan gelen değişkenler: $user, $posts, $categories, $activeTab, $statusFilter, $stats, $message, $messageType
$activeTab = $activeTab ?? 'posts';
$statusFilter = $statusFilter ?? 'all';
$stats = $stats ?? ['all' => 0, 'published' => 0, 'draft' => 0, 'trash' => 0];
?>
<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php 
        $currentPage = 'posts';
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
                        <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Yazılar</h1>
                        <p class="text-gray-500 dark:text-gray-400 text-base">Blog yazılarınızı yönetin.</p>
                    </div>
                    <?php if ($activeTab === 'posts'): ?>
                    <a href="<?php echo admin_url('posts/create'); ?>" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        <span class="material-symbols-outlined text-xl">add</span>
                        <span class="text-sm font-medium">Yeni Yazı</span>
                    </a>
                    <?php elseif ($activeTab === 'categories'): ?>
                    <a href="<?php echo admin_url('posts/category/create'); ?>" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        <span class="material-symbols-outlined text-xl">add</span>
                        <span class="text-sm font-medium">Yeni Kategori</span>
                    </a>
                    <?php endif; ?>
                </header>

                <!-- Tabs -->
                <div class="mb-6 border-b border-gray-200 dark:border-white/10">
                    <nav class="flex gap-2 overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0 scrollbar-hide">
                        <a href="<?php echo admin_url('posts', ['tab' => 'posts']); ?>" class="px-4 py-2 text-sm font-medium border-b-2 transition-colors whitespace-nowrap <?php echo $activeTab === 'posts' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700'; ?>">
                            Yazılar
                        </a>
                        <a href="<?php echo admin_url('posts', ['tab' => 'categories']); ?>" class="px-4 py-2 text-sm font-medium border-b-2 transition-colors whitespace-nowrap <?php echo $activeTab === 'categories' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700'; ?>">
                            Kategoriler
                        </a>
                    </nav>
                </div>

                <!-- Mesaj -->
                <?php if (!empty($message)): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 text-red-800 dark:text-red-200'; ?>">
                    <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
                </div>
                <?php endif; ?>

                <?php if ($activeTab === 'posts'): ?>
                <!-- Durum Filtreleri -->
                <div class="mb-6 flex flex-wrap gap-2 overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0 scrollbar-hide pb-2">
                    <a href="<?php echo admin_url('posts', ['status' => 'all']); ?>" class="px-3 py-1.5 text-sm rounded-lg transition-colors whitespace-nowrap <?php echo $statusFilter === 'all' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'; ?>">
                        Tümü <span class="ml-1 opacity-70">(<?php echo $stats['all']; ?>)</span>
                    </a>
                    <a href="<?php echo admin_url('posts', ['status' => 'published']); ?>" class="px-3 py-1.5 text-sm rounded-lg transition-colors whitespace-nowrap <?php echo $statusFilter === 'published' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'; ?>">
                        Yayında <span class="ml-1 opacity-70">(<?php echo $stats['published']; ?>)</span>
                    </a>
                    <a href="<?php echo admin_url('posts', ['status' => 'draft']); ?>" class="px-3 py-1.5 text-sm rounded-lg transition-colors whitespace-nowrap <?php echo $statusFilter === 'draft' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'; ?>">
                        Taslak <span class="ml-1 opacity-70">(<?php echo $stats['draft']; ?>)</span>
                    </a>
                    <a href="<?php echo admin_url('posts', ['status' => 'trash']); ?>" class="px-3 py-1.5 text-sm rounded-lg transition-colors whitespace-nowrap <?php echo $statusFilter === 'trash' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'; ?>">
                        Çöp <span class="ml-1 opacity-70">(<?php echo $stats['trash']; ?>)</span>
                    </a>
                </div>

                <!-- Yazılar Listesi -->
                <section class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 overflow-hidden">
                    <?php if (empty($posts)): ?>
                    <div class="p-12 text-center">
                        <span class="material-symbols-outlined text-gray-400 text-6xl mb-4">article</span>
                        <p class="text-gray-500 text-lg mb-2">Henüz yazı yok</p>
                        <a href="<?php echo admin_url('posts/create'); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                            <span class="material-symbols-outlined">add</span>
                            <span>İlk Yazıyı Oluştur</span>
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
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Kategori</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Durum</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Tarih</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                <?php foreach ($posts as $post): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <?php if (!empty($post['featured_image'])): ?>
                                            <img src="<?php echo esc_url($post['featured_image']); ?>" alt="" class="w-12 h-12 object-cover rounded-lg">
                                            <?php else: ?>
                                            <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                                <span class="material-symbols-outlined text-gray-400">article</span>
                                            </div>
                                            <?php endif; ?>
                                            <div>
                                                <p class="text-gray-900 dark:text-white font-medium"><?php echo esc_html($post['title']); ?></p>
                                                <p class="text-gray-500 text-xs truncate max-w-xs"><?php echo esc_html($post['slug']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-gray-600 dark:text-gray-400 text-sm"><?php echo esc_html($post['author_name'] ?? 'Bilinmiyor'); ?></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if (!empty($post['category_name'])): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                            <?php echo esc_html($post['category_name']); ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="text-gray-400 text-sm">—</span>
                                        <?php endif; ?>
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
                                        $postStatus = $post['status'] ?? 'draft';
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusColors[$postStatus] ?? $statusColors['draft']; ?>">
                                            <?php echo $statusNames[$postStatus] ?? 'Taslak'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-gray-600 dark:text-gray-400 text-sm">
                                            <?php 
                                            $date = $post['published_at'] ?? $post['created_at'];
                                            echo date('d.m.Y', strtotime($date)); 
                                            ?>
                                        </p>
                                        <p class="text-gray-400 text-xs">
                                            <?php echo date('H:i', strtotime($date)); ?>
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <?php if ($post['status'] === 'trash'): ?>
                                            <a href="<?php echo admin_url('posts/restore/' . $post['id']); ?>" class="p-2 text-gray-600 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg" title="Geri Yükle">
                                                <span class="material-symbols-outlined text-xl">restore</span>
                                            </a>
                                            <?php else: ?>
                                            <a href="<?php echo admin_url('posts/edit/' . $post['id']); ?>" class="p-2 text-gray-600 hover:text-primary hover:bg-primary/10 rounded-lg" title="Düzenle">
                                                <span class="material-symbols-outlined text-xl">edit</span>
                                            </a>
                                            <a href="<?php echo admin_url('posts/toggle/' . $post['id']); ?>" class="p-2 text-gray-600 hover:text-primary hover:bg-primary/10 rounded-lg" title="<?php echo $post['status'] === 'published' ? 'Taslağa Al' : 'Yayınla'; ?>">
                                                <span class="material-symbols-outlined text-xl"><?php echo $post['status'] === 'published' ? 'unpublished' : 'publish'; ?></span>
                                            </a>
                                            <?php endif; ?>
                                            <a href="<?php echo admin_url('posts/delete/' . $post['id']); ?>" class="p-2 text-gray-600 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg" onclick="return confirm('<?php echo $post['status'] === 'trash' ? 'Kalıcı olarak silmek istediğinize emin misiniz?' : 'Çöpe taşımak istediğinize emin misiniz?'; ?>');" title="<?php echo $post['status'] === 'trash' ? 'Kalıcı Sil' : 'Çöpe Taşı'; ?>">
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
                        <?php foreach ($posts as $post): 
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
                            $postStatus = $post['status'] ?? 'draft';
                            $date = $post['published_at'] ?? $post['created_at'];
                        ?>
                        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-white/10 p-4">
                            <div class="flex items-start gap-3 mb-3">
                                <?php if (!empty($post['featured_image'])): ?>
                                <img src="<?php echo esc_url($post['featured_image']); ?>" alt="" class="w-16 h-16 object-cover rounded-lg flex-shrink-0">
                                <?php else: ?>
                                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <span class="material-symbols-outlined text-gray-400 text-2xl">article</span>
                                </div>
                                <?php endif; ?>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-gray-900 dark:text-white font-medium mb-1 line-clamp-2"><?php echo esc_html($post['title']); ?></h3>
                                    <p class="text-gray-500 text-xs truncate"><?php echo esc_html($post['slug']); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusColors[$postStatus] ?? $statusColors['draft']; ?>">
                                    <?php echo $statusNames[$postStatus] ?? 'Taslak'; ?>
                                </span>
                                <?php if (!empty($post['category_name'])): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                    <?php echo esc_html($post['category_name']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-3">
                                <span><?php echo esc_html($post['author_name'] ?? 'Bilinmiyor'); ?></span>
                                <span><?php echo date('d.m.Y H:i', strtotime($date)); ?></span>
                            </div>
                            
                            <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-200 dark:border-white/10">
                                <?php if ($post['status'] === 'trash'): ?>
                                <a href="<?php echo admin_url('posts/restore/' . $post['id']); ?>" class="flex-1 flex items-center justify-center gap-1 px-3 py-2 text-sm text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-lg">restore</span>
                                    <span>Geri Yükle</span>
                                </a>
                                <?php else: ?>
                                <a href="<?php echo admin_url('posts/edit/' . $post['id']); ?>" class="flex-1 flex items-center justify-center gap-1 px-3 py-2 text-sm text-primary hover:bg-primary/10 rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                    <span>Düzenle</span>
                                </a>
                                <a href="<?php echo admin_url('posts/toggle/' . $post['id']); ?>" class="px-3 py-2 text-sm text-gray-600 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="<?php echo $post['status'] === 'published' ? 'Taslağa Al' : 'Yayınla'; ?>">
                                    <span class="material-symbols-outlined text-lg"><?php echo $post['status'] === 'published' ? 'unpublished' : 'publish'; ?></span>
                                </a>
                                <?php endif; ?>
                                <a href="<?php echo admin_url('posts/delete/' . $post['id']); ?>" class="px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" onclick="return confirm('<?php echo $post['status'] === 'trash' ? 'Kalıcı olarak silmek istediğinize emin misiniz?' : 'Çöpe taşımak istediğinize emin misiniz?'; ?>');" title="<?php echo $post['status'] === 'trash' ? 'Kalıcı Sil' : 'Çöpe Taşı'; ?>">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </section>

                <?php elseif ($activeTab === 'categories'): ?>
                <!-- Kategoriler Listesi -->
                <section class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 overflow-hidden">
                    <?php if (empty($categories)): ?>
                    <div class="p-12 text-center">
                        <span class="material-symbols-outlined text-gray-400 text-6xl mb-4">category</span>
                        <p class="text-gray-500 text-lg mb-2">Henüz kategori yok</p>
                        <a href="<?php echo admin_url('posts/category/create'); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                            <span class="material-symbols-outlined">add</span>
                            <span>İlk Kategoriyi Oluştur</span>
                        </a>
                    </div>
                    <?php else: ?>
                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Kategori Adı</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Slug</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Açıklama</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Yazı Sayısı</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Durum</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                <?php foreach ($categories as $cat): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="text-gray-900 dark:text-white font-medium"><?php echo esc_html($cat['name']); ?></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <code class="text-gray-600 text-sm bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded"><?php echo esc_html($cat['slug']); ?></code>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-gray-600 dark:text-gray-400 text-sm truncate max-w-xs"><?php echo esc_html($cat['description'] ?? '—'); ?></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-gray-600 dark:text-gray-400 text-sm"><?php echo esc_html($cat['post_count'] ?? 0); ?> yazı</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($cat['status'] === 'active'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">Aktif</span>
                                        <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">Pasif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="<?php echo admin_url('posts/category/edit/' . $cat['id']); ?>" class="p-2 text-gray-600 hover:text-primary hover:bg-primary/10 rounded-lg" title="Düzenle">
                                                <span class="material-symbols-outlined text-xl">edit</span>
                                            </a>
                                            <a href="<?php echo admin_url('posts/category/delete/' . $cat['id']); ?>" class="p-2 text-gray-600 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg" onclick="return confirm('Kategoriyi silmek istediğinize emin misiniz? Bu kategorideki yazılar kategorisiz kalacaktır.');" title="Sil">
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
                        <?php foreach ($categories as $cat): ?>
                        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-white/10 p-4">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-gray-900 dark:text-white font-medium mb-1"><?php echo esc_html($cat['name']); ?></h3>
                                    <code class="text-gray-600 text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded"><?php echo esc_html($cat['slug']); ?></code>
                                </div>
                                <?php if ($cat['status'] === 'active'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 flex-shrink-0">Aktif</span>
                                <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 flex-shrink-0">Pasif</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($cat['description'])): ?>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-3 line-clamp-2"><?php echo esc_html($cat['description']); ?></p>
                            <?php endif; ?>
                            
                            <div class="flex items-center justify-between pt-3 border-t border-gray-200 dark:border-white/10">
                                <span class="text-sm text-gray-600 dark:text-gray-400"><?php echo esc_html($cat['post_count'] ?? 0); ?> yazı</span>
                                <div class="flex items-center gap-2">
                                    <a href="<?php echo admin_url('posts/category/edit/' . $cat['id']); ?>" class="px-3 py-2 text-sm text-primary hover:bg-primary/10 rounded-lg transition-colors">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </a>
                                    <a href="<?php echo admin_url('posts/category/delete/' . $cat['id']); ?>" class="px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" onclick="return confirm('Kategoriyi silmek istediğinize emin misiniz? Bu kategorideki yazılar kategorisiz kalacaktır.');" title="Sil">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </section>
                <?php endif; ?>

            </div>
            </main>
        </div>
    </div>
</div>
</body>
</html>

