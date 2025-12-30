<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo isset($title) ? esc_html($title) : 'İçerik Kütüphanesi'; ?></title>
    
    <!-- Dark Mode - Sayfa yüklenmeden önce çalışmalı (FOUC önleme) -->
    <script>
        (function() {
            'use strict';
            const DARK_MODE_KEY = 'admin_dark_mode';
            const htmlElement = document.documentElement;
            let darkModePreference = null;
            try {
                const savedPreference = localStorage.getItem(DARK_MODE_KEY);
                if (savedPreference === 'dark' || savedPreference === 'light') {
                    darkModePreference = savedPreference === 'dark';
                }
            } catch (e) {}
            if (darkModePreference === null) {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    darkModePreference = true;
                } else {
                    darkModePreference = false;
                }
            }
            if (darkModePreference) {
                htmlElement.classList.add('dark');
            } else {
                htmlElement.classList.remove('dark');
            }
        })();
    </script>
    
    
    
    
    <!-- Tailwind CSS -->
    <script src="<?php echo ViewRenderer::assetUrl('assets/js/tailwind-admin.min.js'); ?>"></script>
    
    <!-- Google Fonts - Inter -->
    <link rel="stylesheet" href="<?php echo ViewRenderer::assetUrl('assets/css/fonts.css'); ?>">
    <noscript><link rel="stylesheet" href="<?php echo ViewRenderer::assetUrl('assets/css/fonts.css'); ?>"></noscript>
    
    <!-- Material Icons - Font preload ve hızlı yükleme -->
    
    <link rel="stylesheet" href="<?php echo ViewRenderer::assetUrl('assets/css/fonts.css'); ?>">
    
    <!-- İkonların hemen görünmesi için inline CSS -->
    <style>
        /* Material Icons için temel stil - Font yüklenmeden önce de çalışır */
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined';
            font-weight: normal;
            font-style: normal;
            font-size: 24px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        
        /* Font yüklendiğinde smooth geçiş */
        
    </style>
    
    <!-- Custom CSS -->
    <link href="<?php echo rtrim(site_url(), '/') . '/admin/css/admin-dashboard.css'; ?>" rel="stylesheet"/>
    <link href="<?php echo rtrim(site_url(), '/') . '/admin/css/media-library.css'; ?>" rel="stylesheet"/>
    
    <!-- Dark Mode Toggle Script -->
    <script src="<?php echo rtrim(site_url(), '/') . '/admin/js/dark-mode.js'; ?>"></script>
    
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#137fec",
                        "background-light": "#f6f7f8",
                        "background-dark": "#101922",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                },
            },
        }
    </script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark">
    <div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
        <div class="flex min-h-screen">
            <!-- SideNavBar -->
            <?php 
            $currentPage = 'media';
            include __DIR__ . '/../snippets/sidebar.php'; 
            ?>

            <!-- Content Area with Header -->
            <div class="flex-1 flex flex-col lg:ml-64">
                <!-- Top Header -->
                <?php include __DIR__ . '/../snippets/top-header.php'; ?>

                <!-- Main Content -->
                <main class="flex-1 p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b] overflow-y-auto">
                <div class="layout-content-container flex flex-col w-full mx-auto max-w-7xl">
                    <!-- PageHeading -->
                    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <div class="flex flex-col gap-2">
                            <p class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">İçerik Kütüphanesi</p>
                            <p class="text-gray-500 dark:text-gray-400 text-base font-normal leading-normal">Medya dosyalarınızı yükleyin ve yönetin.</p>
                        </div>
                        <button onclick="openUploadModal()" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                            <span class="material-symbols-outlined text-xl">upload</span>
                            <span class="text-sm font-medium">Dosya Yükle</span>
                        </button>
                    </header>

                    <!-- Success/Error Message -->
                    <?php if (isset($message) && $message): ?>
                        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200'; ?>">
                            <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-background-light dark:bg-background-dark rounded-xl border border-gray-200 dark:border-white/10 p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">folder</span>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo number_format($diskUsage['total_files'] ?? 0); ?></p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Toplam Dosya</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-background-light dark:bg-background-dark rounded-xl border border-gray-200 dark:border-white/10 p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-green-600 dark:text-green-400">image</span>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo number_format($diskUsage['image_count'] ?? 0); ?></p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Resim</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-background-light dark:bg-background-dark rounded-xl border border-gray-200 dark:border-white/10 p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-purple-600 dark:text-purple-400">videocam</span>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo number_format($diskUsage['video_count'] ?? 0); ?></p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Video</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-background-light dark:bg-background-dark rounded-xl border border-gray-200 dark:border-white/10 p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-orange-600 dark:text-orange-400">storage</span>
                                </div>
                                <div>
                                    <?php 
                                    $totalSize = $diskUsage['total_size'] ?? 0;
                                    $units = ['B', 'KB', 'MB', 'GB'];
                                    $i = 0;
                                    while ($totalSize > 1024 && $i < count($units) - 1) {
                                        $totalSize /= 1024;
                                        $i++;
                                    }
                                    ?>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo number_format($totalSize, 1); ?></p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo $units[$i]; ?> Kullanılan</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="flex flex-col sm:flex-row gap-4 mb-6">
                        <div class="flex-1">
                            <form method="get" class="flex gap-2">
                                <input type="hidden" name="page" value="media">
                                <input type="hidden" name="type" value="<?php echo esc_attr($filters['type'] ?? 'all'); ?>">
                                <div class="relative flex-1">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                                    <input 
                                        type="text" 
                                        name="search" 
                                        value="<?php echo esc_attr($filters['search'] ?? ''); ?>"
                                        placeholder="Dosya ara..." 
                                        class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors"
                                    >
                                </div>
                                <button type="submit" class="px-4 py-2 bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-white/10 transition-colors">
                                    Ara
                                </button>
                            </form>
                        </div>
                        <div class="flex gap-2">
                            <a href="<?php echo admin_url('media'); ?>" class="px-4 py-2 rounded-lg transition-colors <?php echo ($filters['type'] ?? 'all') === 'all' ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-white/10'; ?>">
                                Tümü
                            </a>
                            <a href="<?php echo admin_url('media&type=image'); ?>" class="px-4 py-2 rounded-lg transition-colors <?php echo ($filters['type'] ?? '') === 'image' ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-white/10'; ?>">
                                Resimler
                            </a>
                            <a href="<?php echo admin_url('media&type=video'); ?>" class="px-4 py-2 rounded-lg transition-colors <?php echo ($filters['type'] ?? '') === 'video' ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-white/10'; ?>">
                                Videolar
                            </a>
                            <a href="<?php echo admin_url('media&type=document'); ?>" class="px-4 py-2 rounded-lg transition-colors <?php echo ($filters['type'] ?? '') === 'document' ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-white/10'; ?>">
                                Belgeler
                            </a>
                        </div>
                    </div>

                    <!-- Selection Actions (Hidden by default) -->
                    <div id="selectionActions" class="hidden mb-4 p-4 bg-primary/10 border border-primary/20 rounded-lg">
                        <div class="flex items-center justify-between">
                            <p class="text-primary font-medium"><span id="selectedCount">0</span> dosya seçildi</p>
                            <div class="flex gap-2">
                                <button onclick="clearSelection()" class="px-3 py-1.5 text-sm bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-white/10 transition-colors">
                                    Seçimi Temizle
                                </button>
                                <button onclick="deleteSelected()" class="px-3 py-1.5 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                    <span class="material-symbols-outlined text-base align-middle mr-1">delete</span>
                                    Seçilenleri Sil
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Media Grid -->
                    <section class="rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark overflow-hidden">
                        <?php if (empty($media)): ?>
                            <div class="p-12 text-center">
                                <span class="material-symbols-outlined text-gray-400 dark:text-gray-600 text-6xl mb-4">perm_media</span>
                                <p class="text-gray-500 dark:text-gray-400 text-lg mb-2">Henüz dosya yüklenmemiş</p>
                                <p class="text-gray-400 dark:text-gray-500 text-sm mb-6">Dosya yüklemek için yukarıdaki butona tıklayın.</p>
                                <button onclick="openUploadModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                    <span class="material-symbols-outlined">upload</span>
                                    <span>İlk Dosyayı Yükle</span>
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="p-4">
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4" id="mediaGrid">
                                    <?php foreach ($media as $item): ?>
                                        <?php 
                                        $fileType = 'document';
                                        if (strpos($item['mime_type'], 'image/') === 0) $fileType = 'image';
                                        else if (strpos($item['mime_type'], 'video/') === 0) $fileType = 'video';
                                        else if (strpos($item['mime_type'], 'audio/') === 0) $fileType = 'audio';
                                        ?>
                                        <div class="media-item group relative aspect-square rounded-lg overflow-hidden bg-gray-100 dark:bg-white/5 border-2 border-transparent hover:border-primary transition-all cursor-pointer"
                                             data-id="<?php echo $item['id']; ?>"
                                             data-url="<?php echo esc_attr($item['file_url']); ?>"
                                             data-name="<?php echo esc_attr($item['original_name']); ?>"
                                             data-type="<?php echo $fileType; ?>"
                                             onclick="toggleSelection(this)">
                                            
                                            <!-- Selection Checkbox -->
                                            <div class="absolute top-2 left-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <div class="w-6 h-6 rounded bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 flex items-center justify-center selection-checkbox">
                                                    <span class="material-symbols-outlined text-primary text-sm hidden">check</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Preview -->
                                            <?php if ($fileType === 'image'): ?>
                                                <img src="<?php echo esc_url($item['file_url']); ?>" 
                                                     alt="<?php echo esc_attr($item['alt_text'] ?? $item['original_name']); ?>"
                                                     class="w-full h-full object-cover"
                                                     loading="lazy">
                                            <?php elseif ($fileType === 'video'): ?>
                                                <div class="w-full h-full flex items-center justify-center bg-gray-800">
                                                    <span class="material-symbols-outlined text-white text-4xl">play_circle</span>
                                                </div>
                                            <?php elseif ($fileType === 'audio'): ?>
                                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-purple-500 to-pink-500">
                                                    <span class="material-symbols-outlined text-white text-4xl">audiotrack</span>
                                                </div>
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <span class="material-symbols-outlined text-gray-400 text-4xl">description</span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Overlay -->
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                                                <div class="absolute bottom-0 left-0 right-0 p-2">
                                                    <p class="text-white text-xs truncate"><?php echo esc_html($item['original_name']); ?></p>
                                                </div>
                                            </div>
                                            
                                            <!-- Actions -->
                                            <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button onclick="event.stopPropagation(); openMediaDetail(<?php echo $item['id']; ?>)" class="w-7 h-7 rounded bg-white/90 dark:bg-gray-800/90 flex items-center justify-center hover:bg-white dark:hover:bg-gray-700 transition-colors" title="Detay">
                                                    <span class="material-symbols-outlined text-gray-700 dark:text-gray-300 text-base">info</span>
                                                </button>
                                                <button onclick="event.stopPropagation(); copyUrl('<?php echo esc_url($item['file_url']); ?>')" class="w-7 h-7 rounded bg-white/90 dark:bg-gray-800/90 flex items-center justify-center hover:bg-white dark:hover:bg-gray-700 transition-colors" title="URL Kopyala">
                                                    <span class="material-symbols-outlined text-gray-700 dark:text-gray-300 text-base">content_copy</span>
                                                </button>
                                                <button onclick="event.stopPropagation(); deleteMedia(<?php echo $item['id']; ?>)" class="w-7 h-7 rounded bg-red-500/90 flex items-center justify-center hover:bg-red-600 transition-colors" title="Sil">
                                                    <span class="material-symbols-outlined text-white text-base">delete</span>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Pagination -->
                            <?php if ($pagination['total'] > 1): ?>
                                <div class="border-t border-gray-200 dark:border-white/10 px-4 py-3">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Toplam <?php echo number_format($pagination['totalItems']); ?> dosya
                                        </p>
                                        <div class="flex gap-2">
                                            <?php if ($pagination['current'] > 1): ?>
                                                <a href="<?php echo admin_url('media&p=' . ($pagination['current'] - 1) . '&type=' . urlencode($filters['type'] ?? 'all') . ($filters['search'] ? '&search=' . urlencode($filters['search']) : '')); ?>" 
                                                   class="px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-white/10 transition-colors">
                                                    <span class="material-symbols-outlined text-base">chevron_left</span>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <span class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400">
                                                Sayfa <?php echo $pagination['current']; ?> / <?php echo $pagination['total']; ?>
                                            </span>
                                            
                                            <?php if ($pagination['current'] < $pagination['total']): ?>
                                                <a href="<?php echo admin_url('media&p=' . ($pagination['current'] + 1) . '&type=' . urlencode($filters['type'] ?? 'all') . ($filters['search'] ? '&search=' . urlencode($filters['search']) : '')); ?>" 
                                                   class="px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-white/10 transition-colors">
                                                    <span class="material-symbols-outlined text-base">chevron_right</span>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </section>
                </div>
            </main>
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeUploadModal()"></div>
        <div class="absolute inset-4 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-full md:max-w-2xl bg-white dark:bg-background-dark rounded-2xl shadow-xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-white/10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Dosya Yükle</h3>
                <button onclick="closeUploadModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">
                    <span class="material-symbols-outlined text-gray-500">close</span>
                </button>
            </div>
            <div class="p-6">
                <!-- Drop Zone -->
                <div id="dropZone" class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center transition-colors hover:border-primary hover:bg-primary/5">
                    <input type="file" id="fileInput" class="hidden" multiple accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar">
                    <span class="material-symbols-outlined text-gray-400 dark:text-gray-500 text-5xl mb-4">cloud_upload</span>
                    <p class="text-gray-600 dark:text-gray-400 mb-2">Dosyaları sürükleyip bırakın</p>
                    <p class="text-gray-400 dark:text-gray-500 text-sm mb-4">veya</p>
                    <button onclick="document.getElementById('fileInput').click()" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        Dosya Seç
                    </button>
                    <p class="text-gray-400 dark:text-gray-500 text-xs mt-4">Maksimum dosya boyutu: 50MB</p>
                </div>
                
                <!-- Upload Progress -->
                <div id="uploadProgress" class="hidden mt-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Yükleniyor...</span>
                        <span id="uploadPercent" class="text-sm text-gray-500 dark:text-gray-400">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-white/10 rounded-full h-2">
                        <div id="uploadBar" class="bg-primary h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
                
                <!-- Upload Results -->
                <div id="uploadResults" class="hidden mt-6 max-h-48 overflow-y-auto">
                    <div id="uploadResultsList" class="space-y-2"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Media Detail Modal -->
    <div id="mediaDetailModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeMediaDetail()"></div>
        <div class="absolute inset-4 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-full md:max-w-4xl md:max-h-[90vh] bg-white dark:bg-background-dark rounded-2xl shadow-xl overflow-hidden flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-white/10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Medya Detayı</h3>
                <button onclick="closeMediaDetail()" class="p-2 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">
                    <span class="material-symbols-outlined text-gray-500">close</span>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Preview -->
                    <div id="mediaPreview" class="aspect-video rounded-lg overflow-hidden bg-gray-100 dark:bg-white/5 flex items-center justify-center">
                        <!-- Dynamic content -->
                    </div>
                    
                    <!-- Details Form -->
                    <div id="mediaDetails">
                        <form id="mediaEditForm" class="space-y-4">
                            <input type="hidden" id="mediaId" name="id">
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dosya Adı</label>
                                <p id="mediaFileName" class="text-gray-900 dark:text-white font-medium truncate"></p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dosya URL</label>
                                <div class="flex gap-2">
                                    <input type="text" id="mediaFileUrl" readonly class="flex-1 px-3 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-sm">
                                    <button type="button" onclick="copyUrl(document.getElementById('mediaFileUrl').value)" class="px-3 py-2 bg-gray-100 dark:bg-white/5 rounded-lg hover:bg-gray-200 dark:hover:bg-white/10 transition-colors">
                                        <span class="material-symbols-outlined text-gray-600 dark:text-gray-400">content_copy</span>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dosya Tipi</label>
                                    <p id="mediaMimeType" class="text-gray-600 dark:text-gray-400 text-sm"></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dosya Boyutu</label>
                                    <p id="mediaFileSize" class="text-gray-600 dark:text-gray-400 text-sm"></p>
                                </div>
                            </div>
                            
                            <div>
                                <label for="mediaAltText" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alt Metin</label>
                                <input type="text" id="mediaAltText" name="alt_text" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors">
                            </div>
                            
                            <div>
                                <label for="mediaDescription" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Açıklama</label>
                                <textarea id="mediaDescription" name="description" rows="3" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-background-dark text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors resize-none"></textarea>
                            </div>
                            
                            <div class="flex gap-2 pt-4">
                                <button type="submit" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                    Kaydet
                                </button>
                                <button type="button" onclick="deleteMedia(document.getElementById('mediaId').value)" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                </main>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-4 right-4 z-50 hidden">
        <div class="bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-4 py-3 rounded-lg shadow-lg flex items-center gap-3">
            <span id="toastIcon" class="material-symbols-outlined"></span>
            <span id="toastMessage"></span>
        </div>
    </div>

    <script src="<?php echo rtrim(site_url(), '/') . '/admin/js/media-library.js'; ?>"></script>
</body>
</html>

