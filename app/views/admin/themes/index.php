<?php include __DIR__ . '/../snippets/header.php'; ?>

<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php 
        $currentPage = 'themes';
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
                        <h1 class="text-gray-900 dark:text-white text-2xl sm:text-3xl font-bold tracking-tight">Temalar</h1>
                        <p class="text-gray-500 dark:text-gray-400 text-sm sm:text-base">Frontend temanızı yönetin ve özelleştirin.</p>
                    </div>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button onclick="document.getElementById('upload-modal').classList.remove('hidden')" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors min-h-[44px] w-full sm:w-auto justify-center sm:justify-start">
                            <span class="material-symbols-outlined text-lg sm:text-xl">upload</span>
                            <span class="text-sm font-medium">Tema Yükle</span>
                        </button>
                    </div>
                </header>

                <!-- Mesaj -->
                <?php if (!empty($message)): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 text-red-800 dark:text-red-200'; ?>">
                    <p class="text-sm font-medium"><?php echo $message; // HTML içerebilir, esc_html kullanma ?></p>
                </div>
                <?php endif; ?>

                <!-- Aktif Tema Bilgisi -->
                <?php if ($activeTheme): ?>
                <div class="mb-6 sm:mb-8 rounded-xl border-2 border-primary/30 bg-primary/5 dark:bg-primary/10 p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row items-start gap-4 sm:gap-6">
                        <!-- Screenshot -->
                        <div class="flex-shrink-0 w-full sm:w-48 h-40 sm:h-32 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                            <?php if (!empty($activeTheme['screenshot'])): ?>
                                <img src="<?php echo esc_url($activeTheme['screenshot']); ?>" alt="<?php echo esc_attr($activeTheme['name']); ?>" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="w-full h-full items-center justify-center hidden">
                                    <span class="material-symbols-outlined text-4xl text-gray-400">palette</span>
                                </div>
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center">
                                    <span class="material-symbols-outlined text-4xl text-gray-400">palette</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Info -->
                        <div class="flex-1 w-full">
                            <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-2">
                                <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white"><?php echo esc_html($activeTheme['name']); ?></h2>
                                <span class="px-2 py-0.5 text-xs font-medium bg-primary text-white rounded">Aktif</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">v<?php echo esc_html($activeTheme['version'] ?? '1.0.0'); ?></span>
                            </div>
                            
                            <?php if (!empty($activeTheme['description'])): ?>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3"><?php echo esc_html($activeTheme['description']); ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($activeTheme['author'])): ?>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mb-4">
                                Geliştirici: <?php echo esc_html($activeTheme['author']); ?>
                            </p>
                            <?php endif; ?>
                            
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                <a href="<?php echo admin_url('themes/customize/' . $activeTheme['slug']); ?>" class="flex items-center justify-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors min-h-[44px]">
                                    <span class="material-symbols-outlined text-lg">brush</span>
                                    <span class="text-sm font-medium">Özelleştir</span>
                                </a>
                                <a href="/" target="_blank" class="flex items-center justify-center gap-2 px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors min-h-[44px]">
                                    <span class="material-symbols-outlined text-lg">open_in_new</span>
                                    <span class="text-sm font-medium">Siteyi Görüntüle</span>
                                </a>
                                <a href="<?php echo admin_url('themes/download/' . $activeTheme['slug']); ?>" class="flex items-center justify-center gap-2 px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors min-h-[44px]">
                                    <span class="material-symbols-outlined text-lg">download</span>
                                    <span class="text-sm font-medium">İndir</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Tüm Temalar -->
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">Tüm Temalar</h2>
                    <span class="text-xs sm:text-sm text-gray-500 dark:text-gray-400"><?php echo count($themes); ?> tema</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    <?php foreach ($themes as $slug => $theme): 
                        $isActive = $activeTheme && $activeTheme['slug'] === $slug;
                        $isInstalled = isset($theme['id']) && $theme['id'] !== null;
                    ?>
                    <div class="rounded-xl border <?php echo $isActive ? 'border-primary/50 bg-primary/5' : 'border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800'; ?> overflow-hidden">
                        <!-- Screenshot -->
                        <div class="relative h-40 bg-gray-100 dark:bg-gray-900">
                            <?php if (!empty($theme['screenshot'])): ?>
                                <img src="<?php echo esc_url($theme['screenshot']); ?>" alt="<?php echo esc_attr($theme['name']); ?>" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="w-full h-full items-center justify-center hidden">
                                    <span class="material-symbols-outlined text-5xl text-gray-400">palette</span>
                                </div>
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center">
                                    <span class="material-symbols-outlined text-5xl text-gray-400">palette</span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($isActive): ?>
                            <div class="absolute top-2 right-2 px-2 py-1 bg-primary text-white text-xs font-medium rounded">
                                Aktif
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Info -->
                        <div class="p-3 sm:p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white line-clamp-1"><?php echo esc_html($theme['name']); ?></h3>
                                <span class="text-xs text-gray-500 flex-shrink-0 ml-2">v<?php echo esc_html($theme['version'] ?? '1.0.0'); ?></span>
                            </div>
                            
                            <?php if (!empty($theme['description'])): ?>
                            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2"><?php echo esc_html($theme['description']); ?></p>
                            <?php endif; ?>
                            
                            <!-- Actions -->
                            <div class="flex items-center gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                                <?php if ($isActive): ?>
                                    <a href="<?php echo admin_url('themes/customize/' . $slug); ?>" class="flex-1 flex items-center justify-center gap-1 px-2 sm:px-3 py-2 bg-primary text-white text-xs sm:text-sm rounded-lg hover:bg-primary/90 transition-colors min-h-[36px]">
                                        <span class="material-symbols-outlined text-base sm:text-lg">brush</span>
                                        <span class="hidden sm:inline">Özelleştir</span>
                                    </a>
                                <?php elseif ($isInstalled): ?>
                                    <a href="<?php echo admin_url('themes/activate/' . $slug); ?>" class="flex-1 flex items-center justify-center gap-1 px-2 sm:px-3 py-2 bg-primary text-white text-xs sm:text-sm rounded-lg hover:bg-primary/90 transition-colors min-h-[36px]">
                                        <span class="material-symbols-outlined text-base sm:text-lg">check_circle</span>
                                        <span class="hidden sm:inline">Aktifleştir</span>
                                    </a>
                                    <a href="<?php echo admin_url('themes/uninstall/' . $slug); ?>" onclick="return confirm('Bu temayı kaldırmak istediğinize emin misiniz?')" class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors min-h-[36px] min-w-[36px] flex items-center justify-center" title="Kaldır">
                                        <span class="material-symbols-outlined text-base sm:text-lg">delete</span>
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo admin_url('themes/install/' . $slug); ?>" class="flex-1 flex items-center justify-center gap-1 px-2 sm:px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs sm:text-sm rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors min-h-[36px]">
                                        <span class="material-symbols-outlined text-base sm:text-lg">download</span>
                                        <span class="hidden sm:inline">Kur</span>
                                    </a>
                                <?php endif; ?>
                                
                                <a href="<?php echo admin_url('themes/preview/' . $slug); ?>" target="_blank" class="p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors min-h-[36px] min-w-[36px] flex items-center justify-center" title="Önizleme">
                                    <span class="material-symbols-outlined text-base sm:text-lg">visibility</span>
                                </a>
                                
                                <a href="<?php echo admin_url('themes/download/' . $slug); ?>" class="p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors min-h-[36px] min-w-[36px] flex items-center justify-center" title="İndir">
                                    <span class="material-symbols-outlined text-base sm:text-lg">download</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($themes)): ?>
                    <div class="col-span-full text-center py-8 sm:py-12">
                        <span class="material-symbols-outlined text-5xl sm:text-6xl text-gray-400 mb-4 block">palette</span>
                        <p class="text-gray-500 dark:text-gray-400 text-base sm:text-lg mb-2">Henüz tema yüklenmemiş</p>
                        <p class="text-gray-400 dark:text-gray-500 text-sm mb-6">Bir tema yükleyerek başlayın</p>
                        <button onclick="document.getElementById('upload-modal').classList.remove('hidden')" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors min-h-[44px]">
                            Tema Yükle
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Tema Geliştirme İpuçları -->
                <div class="mt-6 sm:mt-8 rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4 sm:p-6">
                    <div class="flex items-start gap-3 sm:gap-4">
                        <span class="material-symbols-outlined text-blue-500 text-xl sm:text-2xl mt-0.5 flex-shrink-0">lightbulb</span>
                        <div class="min-w-0">
                            <h3 class="text-sm sm:text-base font-semibold text-blue-900 dark:text-blue-200 mb-2">Kendi Temanızı Geliştirin</h3>
                            <p class="text-xs sm:text-sm text-blue-800 dark:text-blue-300 mb-3 break-words">
                                Temalar <code class="bg-blue-100 dark:bg-blue-800/50 px-1 rounded">/themes/</code> klasöründe bulunur. Her tema bir <code class="bg-blue-100 dark:bg-blue-800/50 px-1 rounded">theme.json</code> dosyası içermelidir.
                            </p>
                            <div class="text-xs sm:text-sm text-blue-700 dark:text-blue-400 break-words">
                                <strong>Tema yapısı:</strong> layouts/, components/, snippets/, assets/, theme.json
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </main>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div id="upload-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 sm:p-6 max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4 sm:mb-6">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">Tema Yükle</h3>
            <button onclick="document.getElementById('upload-modal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 min-h-[36px] min-w-[36px] flex items-center justify-center">
                <span class="material-symbols-outlined text-lg sm:text-xl">close</span>
            </button>
        </div>
        
        <form action="<?php echo admin_url('themes/upload'); ?>" method="POST" enctype="multipart/form-data">
            <div class="mb-4 sm:mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tema ZIP Dosyası</label>
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 sm:p-6 text-center hover:border-primary transition-colors">
                    <input type="file" name="theme_zip" id="theme_zip" accept=".zip" required class="hidden" onchange="updateFileName(this)">
                    <label for="theme_zip" class="cursor-pointer">
                        <span class="material-symbols-outlined text-3xl sm:text-4xl text-gray-400 mb-2 block">upload_file</span>
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-1 break-words" id="file-name">ZIP dosyası seçmek için tıklayın</p>
                        <p class="text-xs text-gray-500">Maksimum 50MB</p>
                    </label>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row justify-end gap-3">
                <button type="button" onclick="document.getElementById('upload-modal').classList.add('hidden')" class="px-4 py-2 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors min-h-[44px] order-2 sm:order-1">
                    İptal
                </button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors min-h-[44px] order-1 sm:order-2">
                    Yükle
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateFileName(input) {
    const fileName = input.files[0]?.name || 'ZIP dosyası seçmek için tıklayın';
    document.getElementById('file-name').textContent = fileName;
}
</script>

</body>
</html>

