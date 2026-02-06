<?php include __DIR__ . '/snippets/header.php'; ?>
<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php 
        $currentPage = 'dashboard';
        include __DIR__ . '/snippets/sidebar.php'; 
        ?>

        <!-- Content Area -->
        <div class="flex-1 flex flex-col lg:ml-64">
            <!-- Top Header -->
            <?php include __DIR__ . '/snippets/top-header.php'; ?>

            <!-- Main Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b] overflow-y-auto">
                <?php if (!empty($message)): ?>
                <!-- Yetki Uyarısı -->
                <div class="max-w-7xl mx-auto">
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6 mb-6">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-red-500 dark:text-red-400 text-2xl">error</span>
                            <div>
                                <h3 class="text-red-900 dark:text-red-200 font-semibold">Erişim Engellendi</h3>
                                <p class="text-red-700 dark:text-red-300 text-sm mt-1"><?php echo esc_html($message); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                
                <div class="max-w-7xl mx-auto space-y-6">
                    
                    <!-- Hoşgeldin Başlığı -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                                Hoşgeldin, <?php echo esc_html($user['display_name'] ?? $user['username']); ?> 👋
                            </h1>
                            <p class="text-gray-500 dark:text-gray-400 mt-1">İçeriklerine ve site istatistiklerine genel bakış</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="<?php echo admin_url('posts/create'); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                <span class="material-symbols-outlined text-lg">add</span>
                                <span class="hidden sm:inline">Yeni Yazı</span>
                            </a>
                        </div>
                    </div>

                    <!-- İstatistik Kartları -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Yazılar -->
                        <a href="<?php echo admin_url('posts'); ?>" class="block group">
                            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-700 transition-all hover:shadow-lg min-h-[120px]">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="material-symbols-outlined text-blue-500 text-2xl">article</span>
                                </div>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $stats['posts_count']; ?></p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Toplam Yazı</p>
                            </div>
                        </a>

                        <!-- Sayfalar -->
                        <a href="<?php echo admin_url('module/pages'); ?>" class="block group">
                            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 hover:border-purple-300 dark:hover:border-purple-700 transition-all hover:shadow-lg min-h-[120px]">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="material-symbols-outlined text-purple-500 text-2xl">description</span>
                                </div>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $stats['pages_count']; ?></p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Toplam Sayfa</p>
                            </div>
                        </a>

                        <!-- Formlar -->
                        <a href="<?php echo admin_url('forms'); ?>" class="block group relative">
                            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 hover:border-green-300 dark:hover:border-green-700 transition-all hover:shadow-lg min-h-[120px]">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="material-symbols-outlined text-green-500 text-2xl">contact_mail</span>
                                    <?php if ($stats['new_submissions_count'] > 0): ?>
                                    <span class="absolute -top-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white text-xs font-bold animate-pulse">
                                        <?php echo $stats['new_submissions_count']; ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $stats['total_submissions_count']; ?></p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Form Gönderimi</p>
                            </div>
                        </a>

                        <!-- Kullanıcılar -->
                        <a href="<?php echo admin_url('users'); ?>" class="block group">
                            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 hover:border-orange-300 dark:hover:border-orange-700 transition-all hover:shadow-lg min-h-[120px]">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="material-symbols-outlined text-orange-500 text-2xl">group</span>
                                </div>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo $stats['users_count']; ?></p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Kullanıcı</p>
                            </div>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        <!-- Son Yazılar -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Son Yazılar</h2>
                                    <a href="<?php echo admin_url('posts'); ?>" class="text-sm text-primary hover:text-primary/80 flex items-center gap-1">
                                        Tümünü Gör
                                        <span class="material-symbols-outlined text-base">arrow_forward</span>
                                    </a>
                                </div>
                            </div>
                            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                                <?php if (empty($recentPosts)): ?>
                                <div class="p-8 text-center">
                                    <span class="material-symbols-outlined text-gray-400 text-5xl mb-2 block">article</span>
                                    <p class="text-gray-500 dark:text-gray-400">Henüz yazı yok</p>
                                    <a href="<?php echo admin_url('posts/create'); ?>" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-sm">
                                        <span class="material-symbols-outlined text-base">add</span>
                                        İlk Yazını Oluştur
                                    </a>
                                </div>
                                <?php else: ?>
                                    <?php foreach ($recentPosts as $post): ?>
                                    <a href="<?php echo admin_url('posts/edit/' . $post['id']); ?>" class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <div class="flex items-start gap-3">
                                            <?php if (!empty($post['featured_image'])): ?>
                                            <img src="<?php echo esc_url($post['featured_image']); ?>" alt="" class="w-12 h-12 rounded-lg object-cover flex-shrink-0" width="48" height="48" loading="lazy">
                                            <?php else: ?>
                                            <div class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                                                <span class="material-symbols-outlined text-gray-400">article</span>
                                            </div>
                                            <?php endif; ?>
                                            <div class="flex-1 min-w-0">
                                                <h3 class="font-medium text-gray-900 dark:text-white truncate"><?php echo esc_html($post['title']); ?></h3>
                                                <div class="flex items-center gap-2 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    <span><?php echo esc_html($post['author_name'] ?? 'Bilinmiyor'); ?></span>
                                                    <span>•</span>
                                                    <span><?php echo date('d M Y', strtotime($post['created_at'])); ?></span>
                                                    <?php if ($post['status'] === 'draft'): ?>
                                                    <span class="px-2 py-0.5 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 rounded-full">Taslak</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Son Gelen Formlar -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Son Gelen Formlar</h2>
                                        <?php if ($stats['new_submissions_count'] > 0): ?>
                                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white text-xs font-bold">
                                            <?php echo $stats['new_submissions_count']; ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="<?php echo admin_url('forms'); ?>" class="text-sm text-primary hover:text-primary/80 flex items-center gap-1">
                                        Tümünü Gör
                                        <span class="material-symbols-outlined text-base">arrow_forward</span>
                                    </a>
                                </div>
                            </div>
                            <div class="divide-y divide-gray-200 dark:divide-gray-700 max-h-[340px] overflow-y-auto"
                                 style="scrollbar-width: thin; scrollbar-color: rgb(209 213 219) transparent;">
                                <style>
                                    .divide-y::-webkit-scrollbar {
                                        width: 6px;
                                    }
                                    .divide-y::-webkit-scrollbar-track {
                                        background: transparent;
                                    }
                                    .divide-y::-webkit-scrollbar-thumb {
                                        background: rgb(209 213 219);
                                        border-radius: 3px;
                                    }
                                    .dark .divide-y::-webkit-scrollbar-thumb {
                                        background: rgb(75 85 99);
                                    }
                                    .divide-y::-webkit-scrollbar-thumb:hover {
                                        background: rgb(156 163 175);
                                    }
                                    .dark .divide-y::-webkit-scrollbar-thumb:hover {
                                        background: rgb(107 114 128);
                                    }
                                </style>
                                <?php if (empty($recentSubmissions)): ?>
                                <div class="p-8 text-center">
                                    <span class="material-symbols-outlined text-gray-400 text-5xl mb-2 block">contact_mail</span>
                                    <p class="text-gray-500 dark:text-gray-400">Henüz form gönderimi yok</p>
                                    <a href="<?php echo admin_url('forms/create'); ?>" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-sm">
                                        <span class="material-symbols-outlined text-base">add</span>
                                        İlk Formunu Oluştur
                                    </a>
                                </div>
                                <?php else: ?>
                                    <?php foreach ($recentSubmissions as $submission): ?>
                                    <a href="<?php echo admin_url('forms/submissions/' . $submission['form_id']); ?>" class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <div class="flex items-start gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                                                <span class="material-symbols-outlined text-green-600 dark:text-green-400">mail</span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <h3 class="font-medium text-gray-900 dark:text-white truncate">
                                                        <?php echo esc_html($submission['form_name'] ?? 'Form'); ?>
                                                    </h3>
                                                    <?php if ($submission['status'] === 'new'): ?>
                                                    <span class="flex h-2 w-2">
                                                        <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-red-400 opacity-75"></span>
                                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                                    </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                                    <?php 
                                                    // İlk form alanını göster
                                                    if (!empty($submission['data'])) {
                                                        $firstValue = reset($submission['data']);
                                                        if (is_string($firstValue) && strlen($firstValue) > 30) {
                                                            echo esc_html(substr($firstValue, 0, 30) . '...');
                                                        } else {
                                                            echo esc_html($firstValue);
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <div class="flex items-center gap-2 mt-1 text-xs text-gray-400">
                                                    <span class="material-symbols-outlined text-xs">schedule</span>
                                                    <span><?php echo time_ago($submission['created_at']); ?></span>
                                                    <?php if ($submission['status'] === 'new'): ?>
                                                    <span class="px-2 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 rounded-full">Yeni</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>

                    <?php if ($analyticsStats): ?>
                    <!-- Ziyaretçi İstatistikleri -->
                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl border border-purple-200 dark:border-purple-800 p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="material-symbols-outlined text-purple-600 dark:text-purple-400">analytics</span>
                                Ziyaretçi İstatistikleri
                            </h2>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-green-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                    </span>
                                    <span class="text-green-600 dark:text-green-400 font-medium" id="live-visitors-count"><?php echo $analyticsStats['live_visitors']; ?> canlı</span>
                                </div>
                                <button onclick="refreshAnalytics()" 
                                        class="text-xs text-gray-500 dark:text-gray-400 hover:text-primary flex items-center gap-1"
                                        title="Yenile">
                                    <span class="material-symbols-outlined text-sm" id="refresh-icon">refresh</span>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Ana Metrikler -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                            <!-- Bugün Görüntülenme -->
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-100 dark:border-gray-700 min-h-[100px]">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="material-symbols-outlined text-blue-500 text-lg">visibility</span>
                                </div>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo number_format($analyticsStats['today_views']); ?></p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Bugün</p>
                                <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">Görüntülenme</p>
                            </div>

                            <!-- Bugün Ziyaretçi -->
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-100 dark:border-gray-700 min-h-[100px]">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="material-symbols-outlined text-green-500 text-lg">person</span>
                                </div>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo number_format($analyticsStats['today_unique']); ?></p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Bugün</p>
                                <p class="text-xs text-green-600 dark:text-green-400 mt-0.5">Benzersiz</p>
                            </div>

                            <!-- Aylık Görüntülenme -->
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-100 dark:border-gray-700 min-h-[100px]">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="material-symbols-outlined text-purple-500 text-lg">trending_up</span>
                                </div>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo number_format($analyticsStats['month_views']); ?></p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Bu Ay</p>
                                <p class="text-xs text-purple-600 dark:text-purple-400 mt-0.5">Toplam</p>
                            </div>

                            <!-- Ortalama Süre -->
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-100 dark:border-gray-700 min-h-[100px]">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="material-symbols-outlined text-orange-500 text-lg">schedule</span>
                                </div>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo gmdate("i:s", $analyticsStats['avg_duration']); ?></p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Ort. Süre</p>
                                <p class="text-xs text-orange-600 dark:text-orange-400 mt-0.5">dk:sn</p>
                            </div>
                        </div>
                        
                        <!-- En Popüler Sayfalar & Cihaz Dağılımı -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <!-- En Popüler Sayfalar -->
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-100 dark:border-gray-700">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm text-indigo-500">star</span>
                                    Popüler Sayfalar (7 Gün)
                                </h3>
                                <?php if (!empty($analyticsStats['top_pages'])): ?>
                                <div class="space-y-2">
                                    <?php foreach ($analyticsStats['top_pages'] as $page): ?>
                                    <div class="flex items-center justify-between text-xs group">
                                        <a href="<?php echo esc_url($page['page_url']); ?>" 
                                           target="_blank"
                                           class="flex-1 text-gray-700 dark:text-gray-300 hover:text-primary transition-colors truncate flex items-center gap-2"
                                           title="<?php echo esc_attr($page['page_url']); ?>">
                                            <span class="material-symbols-outlined text-xs text-gray-400 group-hover:text-primary">link</span>
                                            <span class="font-medium">
                                                <?php 
                                                $title = $page['page_title'] ?? '';
                                                if (empty($title) || $title === 'null') {
                                                    // URL'den güzel isim oluştur
                                                    $path = parse_url($page['page_url'], PHP_URL_PATH);
                                                    $path = trim($path, '/');
                                                    if (empty($path)) {
                                                        echo 'Ana Sayfa';
                                                    } else {
                                                        // test-analytics.html → Test Analytics
                                                        $path = str_replace(['-', '_', '.html', '.php'], ' ', $path);
                                                        echo esc_html(ucwords($path));
                                                    }
                                                } else {
                                                    echo esc_html($title);
                                                }
                                                ?>
                                            </span>
                                        </a>
                                        <span class="ml-2 px-2 py-0.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-full font-medium flex-shrink-0">
                                            <?php echo number_format($page['views']); ?>
                                        </span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php else: ?>
                                <p class="text-xs text-gray-500 dark:text-gray-400 text-center py-4">Henüz veri yok</p>
                                <?php endif; ?>
                            </div>

                            <!-- Cihaz Dağılımı -->
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-100 dark:border-gray-700">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm text-teal-500">devices</span>
                                    Cihaz Dağılımı (30 Gün)
                                </h3>
                                <?php if (!empty($analyticsStats['device_distribution'])): ?>
                                <div class="space-y-3">
                                    <?php 
                                    $totalDevices = array_sum(array_column($analyticsStats['device_distribution'], 'count'));
                                    $deviceIcons = [
                                        'desktop' => 'computer',
                                        'mobile' => 'smartphone',
                                        'tablet' => 'tablet'
                                    ];
                                    $deviceLabels = [
                                        'desktop' => 'Masaüstü',
                                        'mobile' => 'Mobil',
                                        'tablet' => 'Tablet'
                                    ];
                                    foreach ($analyticsStats['device_distribution'] as $device): 
                                        $percentage = $totalDevices > 0 ? round(($device['count'] / $totalDevices) * 100) : 0;
                                    ?>
                                    <div>
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="flex items-center gap-1 text-gray-700 dark:text-gray-300">
                                                <span class="material-symbols-outlined text-sm"><?php echo $deviceIcons[$device['device_type']] ?? 'devices'; ?></span>
                                                <?php echo $deviceLabels[$device['device_type']] ?? ucfirst($device['device_type']); ?>
                                            </span>
                                            <span class="font-medium text-gray-900 dark:text-white"><?php echo $percentage; ?>%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="bg-teal-500 h-2 rounded-full transition-all" style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php else: ?>
                                <p class="text-xs text-gray-500 dark:text-gray-400 text-center py-4">Henüz veri yok</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Hızlı Erişim -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Hızlı Erişim</h2>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                            <a href="<?php echo admin_url('posts/create'); ?>" class="flex flex-col items-center gap-2 p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                                <span class="material-symbols-outlined text-primary group-hover:scale-110 transition-transform">add_circle</span>
                                <span class="text-sm text-gray-700 dark:text-gray-300 text-center">Yeni Yazı</span>
                            </a>
                            <a href="<?php echo admin_url('module/pages/create'); ?>" class="flex flex-col items-center gap-2 p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                                <span class="material-symbols-outlined text-purple-500 group-hover:scale-110 transition-transform">note_add</span>
                                <span class="text-sm text-gray-700 dark:text-gray-300 text-center">Yeni Sayfa</span>
                            </a>
                            <a href="<?php echo admin_url('menus'); ?>" class="flex flex-col items-center gap-2 p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                                <span class="material-symbols-outlined text-orange-500 group-hover:scale-110 transition-transform">menu</span>
                                <span class="text-sm text-gray-700 dark:text-gray-300 text-center">Menüler</span>
                            </a>
                            <a href="<?php echo admin_url('themes'); ?>" class="flex flex-col items-center gap-2 p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                                <span class="material-symbols-outlined text-pink-500 group-hover:scale-110 transition-transform">palette</span>
                                <span class="text-sm text-gray-700 dark:text-gray-300 text-center">Temalar</span>
                            </a>
                            <a href="<?php echo admin_url('settings'); ?>" class="flex flex-col items-center gap-2 p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                                <span class="material-symbols-outlined text-gray-500 group-hover:scale-110 transition-transform">settings</span>
                                <span class="text-sm text-gray-700 dark:text-gray-300 text-center">Ayarlar</span>
                            </a>
                        </div>
                    </div>

                    <?php if ($activeTheme): ?>
                    <!-- Aktif Tema -->
                    <div class="bg-gradient-to-r from-primary/10 to-purple-500/10 dark:from-primary/20 dark:to-purple-500/20 rounded-xl border border-primary/20 dark:border-primary/30 p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-lg bg-white dark:bg-gray-800 flex items-center justify-center shadow-sm">
                                    <span class="material-symbols-outlined text-primary text-3xl">palette</span>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white"><?php echo esc_html($activeTheme['name']); ?></h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Aktif Tema • v<?php echo esc_html($activeTheme['version']); ?></p>
                                </div>
                            </div>
                            <a href="<?php echo admin_url('themes/customize/' . $activeTheme['slug']); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors border border-gray-200 dark:border-gray-700">
                                <span class="material-symbols-outlined text-lg">brush</span>
                                <span class="hidden sm:inline">Özelleştir</span>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</div>

<?php if ($analyticsStats): ?>
<script>
// Auto-refresh analytics (her 30 saniyede)
let refreshInterval = null;

function refreshAnalytics() {
    const icon = document.getElementById('refresh-icon');
    if (icon) {
        icon.classList.add('animate-spin');
    }
    
    // Sadece sayfa yenilemeden veri güncellemek için
    // Şimdilik sadece canlı ziyaretçi sayısını güncelle
    setTimeout(() => {
        if (icon) {
            icon.classList.remove('animate-spin');
        }
        // Gerçek veri için AJAX gerekir, şimdilik sayfa yenile
        window.location.reload();
    }, 500);
}

// Otomatik yenileme (30 saniyede bir)
refreshInterval = setInterval(() => {
    console.log('📊 Analytics: Auto-refreshing...');
    window.location.reload();
}, 30000);

// Sayfa kapatılınca interval'i temizle
window.addEventListener('beforeunload', () => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>
<?php endif; ?>

</body>
</html>
