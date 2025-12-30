<?php
/**
 * Modül Ayarları Sayfası
 */

// Header
include __DIR__ . '/../snippets/header.php';
?>

<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <!-- SideNavBar -->
        <?php 
        $currentPage = 'modules';
        include __DIR__ . '/../snippets/sidebar.php'; 
        ?>

        <!-- Content Area with Header -->
        <div class="flex-1 flex flex-col lg:ml-64">
            <!-- Top Header -->
            <?php include __DIR__ . '/../snippets/top-header.php'; ?>

            <!-- Main Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b] overflow-y-auto">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-4 sm:mb-6 overflow-x-auto scrollbar-hide">
            <a href="<?php echo admin_url('modules'); ?>" class="hover:text-primary transition-colors whitespace-nowrap">Modüller</a>
            <span class="material-symbols-outlined text-sm sm:text-base flex-shrink-0">chevron_right</span>
            <span class="text-gray-900 dark:text-white truncate"><?php echo htmlspecialchars($module['title']); ?></span>
            <span class="material-symbols-outlined text-sm sm:text-base flex-shrink-0">chevron_right</span>
            <span class="text-gray-900 dark:text-white whitespace-nowrap">Ayarlar</span>
        </div>
        
        <!-- Başlık -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4 mb-6">
            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-2xl sm:text-3xl text-primary">
                    <?php echo htmlspecialchars($module['admin_menu']['icon'] ?? 'extension'); ?>
                </span>
            </div>
            <div class="min-w-0 flex-1">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white line-clamp-2">
                    <?php echo htmlspecialchars($module['title']); ?> - Ayarlar
                </h1>
                <p class="text-sm sm:text-base text-gray-500 dark:text-gray-400">
                    v<?php echo htmlspecialchars($module['version']); ?>
                    <?php if (!empty($module['author'])): ?> | <?php echo htmlspecialchars($module['author']); ?><?php endif; ?>
                </p>
            </div>
        </div>
        
        <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $_SESSION['flash_type'] === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'; ?>">
            <?php 
            echo htmlspecialchars($_SESSION['flash_message']); 
            unset($_SESSION['flash_message'], $_SESSION['flash_type']);
            ?>
        </div>
        <?php endif; ?>
        
        <!-- Ayarlar Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <?php if ($hasCustomView && file_exists($customViewPath)): ?>
            <!-- Modülün özel ayar view'ı -->
            <form action="<?php echo admin_url('modules/settings/' . $module['name']); ?>" method="POST">
                <div class="p-4 sm:p-6">
                    <?php 
                    // Modülün kendi settings view'ını include et
                    include $customViewPath; 
                    ?>
                </div>
                <div class="px-4 sm:px-6 py-3 sm:py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row justify-end gap-3">
                    <a href="<?php echo admin_url('modules'); ?>" 
                       class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors min-h-[44px] flex items-center justify-center order-2 sm:order-1">
                        İptal
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors min-h-[44px] order-1 sm:order-2">
                        Kaydet
                    </button>
                </div>
            </form>
            
            <?php else: ?>
            <!-- Varsayılan ayar formu -->
            <form action="<?php echo admin_url('modules/settings/' . $module['name']); ?>" method="POST">
                <div class="p-4 sm:p-6">
                    <div class="mb-4 sm:mb-6">
                        <p class="text-gray-500 dark:text-gray-400 text-xs sm:text-sm">
                            Bu modül için özel ayarlar tanımlanmamış. Aşağıdaki genel ayarları düzenleyebilirsiniz.
                        </p>
                    </div>
                    
                    <div class="space-y-4 sm:space-y-6">
                        <!-- Genel Ayarlar -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Modül Durumu
                            </label>
                            <select name="settings[enabled]" 
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent text-base">
                                <option value="1" <?php echo ($settings['enabled'] ?? true) ? 'selected' : ''; ?>>Aktif</option>
                                <option value="0" <?php echo !($settings['enabled'] ?? true) ? 'selected' : ''; ?>>Pasif</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Özel CSS Sınıfı
                            </label>
                            <input type="text" name="settings[custom_class]" 
                                   value="<?php echo htmlspecialchars($settings['custom_class'] ?? ''); ?>"
                                   placeholder="custom-class"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent text-base">
                            <p class="text-xs text-gray-500 mt-1">Frontend'de kullanılacak özel CSS sınıfı</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Özel Notlar
                            </label>
                            <textarea name="settings[notes]" rows="3"
                                      placeholder="Modül hakkında notlarınız..."
                                      class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent text-base"><?php echo htmlspecialchars($settings['notes'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="px-4 sm:px-6 py-3 sm:py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row justify-end gap-3">
                    <a href="<?php echo admin_url('modules'); ?>" 
                       class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors min-h-[44px] flex items-center justify-center order-2 sm:order-1">
                        İptal
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors min-h-[44px] order-1 sm:order-2">
                        Kaydet
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
        
        <!-- Modül Bilgileri -->
        <div class="mt-4 sm:mt-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-3 sm:mb-4">Modül Bilgileri</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 text-xs sm:text-sm">
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Teknik Ad:</span>
                    <span class="text-gray-900 dark:text-white ml-2"><?php echo htmlspecialchars($module['name']); ?></span>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Versiyon:</span>
                    <span class="text-gray-900 dark:text-white ml-2"><?php echo htmlspecialchars($module['version']); ?></span>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Gerekli PHP:</span>
                    <span class="text-gray-900 dark:text-white ml-2"><?php echo htmlspecialchars($module['requires_php'] ?? '7.4'); ?>+</span>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Klasör:</span>
                    <span class="text-gray-900 dark:text-white ml-2 font-mono text-xs"><?php echo htmlspecialchars($module['dir'] ?? $module['name']); ?>/</span>
                </div>
                
                <?php if (!empty($module['shortcodes'])): ?>
                <div class="md:col-span-2">
                    <span class="text-gray-500 dark:text-gray-400">Shortcode'lar:</span>
                    <div class="mt-1 flex flex-wrap gap-1">
                        <?php foreach ($module['shortcodes'] as $shortcode): ?>
                        <code class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-xs">[<?php echo htmlspecialchars($shortcode); ?>]</code>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($module['widgets'])): ?>
                <div class="md:col-span-2">
                    <span class="text-gray-500 dark:text-gray-400">Widget'lar:</span>
                    <div class="mt-1 flex flex-wrap gap-1">
                        <?php foreach ($module['widgets'] as $widget): ?>
                        <code class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-xs"><?php echo htmlspecialchars($widget); ?></code>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
            </main>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../snippets/footer.php'; ?>

