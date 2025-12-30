<?php
/**
 * Modül Kartı Partial
 * Kullanım: $module değişkeni gerekli
 */

$isActive = $module['is_active'] ?? false;
$isSystem = $module['is_system'] ?? ($isSystem ?? false);
$isAvailable = $isAvailable ?? false;
$isThemeModule = $module['is_theme_module'] ?? ($isThemeModule ?? false);
$icon = $module['admin_menu']['icon'] ?? 'extension';
?>

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg transition-shadow">
    <!-- Header -->
    <div class="p-3 sm:p-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2 sm:gap-3">
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg flex items-center justify-center flex-shrink-0 <?php echo $isActive ? 'bg-primary/10' : 'bg-gray-100 dark:bg-gray-700'; ?>">
            <span class="material-symbols-outlined text-xl sm:text-2xl <?php echo $isActive ? 'text-primary' : 'text-gray-400'; ?>">
                <?php echo htmlspecialchars($icon); ?>
            </span>
        </div>
        <div class="flex-1 min-w-0">
            <h3 class="font-semibold text-sm sm:text-base text-gray-900 dark:text-white truncate">
                <?php echo htmlspecialchars($module['title']); ?>
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                v<?php echo htmlspecialchars($module['version']); ?>
                <?php if (!empty($module['author'])): ?>
                    | <?php echo htmlspecialchars($module['author']); ?>
                <?php endif; ?>
            </p>
        </div>
        
        <!-- Status Badge -->
        <?php if ($isSystem): ?>
        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 flex-shrink-0">
            Sistem
        </span>
        <?php elseif ($isThemeModule): ?>
        <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400 flex-shrink-0">
            Tema
        </span>
        <?php elseif ($isActive): ?>
        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 flex-shrink-0">
            Aktif
        </span>
        <?php elseif ($isAvailable): ?>
        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 flex-shrink-0">
            Kurulmamış
        </span>
        <?php else: ?>
        <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 flex-shrink-0">
            Pasif
        </span>
        <?php endif; ?>
    </div>
    
    <!-- Body -->
    <div class="p-3 sm:p-4">
        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 line-clamp-2 min-h-[2.5rem] sm:min-h-[2.75rem]">
            <?php echo htmlspecialchars($module['description'] ?? 'Açıklama yok'); ?>
        </p>
        
        <!-- Features -->
        <div class="flex flex-wrap gap-1 mt-2 sm:mt-3">
            <?php if (!empty($module['shortcodes'])): ?>
            <span class="px-1.5 sm:px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded">
                Shortcode
            </span>
            <?php endif; ?>
            <?php if (!empty($module['widgets'])): ?>
            <span class="px-1.5 sm:px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded">
                Widget
            </span>
            <?php endif; ?>
            <?php if (!empty($module['routes'])): ?>
            <span class="px-1.5 sm:px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded">
                Routes
            </span>
            <?php endif; ?>
            <?php if ($module['settings'] ?? false): ?>
            <span class="px-1.5 sm:px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded">
                Ayarlar
            </span>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Actions -->
    <div class="px-3 sm:px-4 py-2.5 sm:py-3 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-2 sm:gap-0">
        <button onclick="showModuleDetail('<?php echo htmlspecialchars($module['name']); ?>')"
                class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 hover:text-primary transition-colors min-h-[36px] flex items-center justify-center sm:justify-start gap-1">
            <span class="material-symbols-outlined text-base align-middle">info</span>
            <span>Detay</span>
        </button>
        
        <div class="flex items-center gap-1.5 sm:gap-2 justify-center sm:justify-end">
            <?php if ($isSystem): ?>
            <!-- Sistem modülü - sadece ayarlar -->
            <?php if ($module['settings'] ?? false): ?>
            <a href="<?php echo admin_url('modules/settings/' . $module['name']); ?>" 
               class="p-1.5 text-gray-600 dark:text-gray-400 hover:text-primary transition-colors" title="Ayarlar">
                <span class="material-symbols-outlined text-xl">settings</span>
            </a>
            <?php endif; ?>
            
            <?php elseif ($isThemeModule): ?>
            <!-- Tema modülü - sadece sayfasına git -->
            <a href="<?php echo admin_url('module/' . $module['name']); ?>" 
               class="px-3 py-1.5 text-xs sm:text-sm bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors min-h-[36px] flex items-center justify-center whitespace-nowrap">
                <span class="material-symbols-outlined text-base mr-1">open_in_new</span>
                <span>Aç</span>
            </a>
            
            <?php elseif ($isAvailable): ?>
            <!-- Kurulmamış modül - sadece kurma butonu -->
            <a href="<?php echo admin_url('modules/install/' . $module['name']); ?>" 
               class="px-3 py-1.5 text-xs sm:text-sm bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors min-h-[36px] flex items-center justify-center whitespace-nowrap">
                <span class="hidden sm:inline">Kur & Aktif Et</span>
                <span class="sm:hidden">Kur</span>
            </a>
            
            <?php elseif ($isActive): ?>
            <!-- Aktif modül -->
            <?php if ($module['settings'] ?? false): ?>
            <a href="<?php echo admin_url('modules/settings/' . $module['name']); ?>" 
               class="p-1.5 text-gray-600 dark:text-gray-400 hover:text-primary transition-colors min-h-[36px] min-w-[36px] flex items-center justify-center" title="Ayarlar">
                <span class="material-symbols-outlined text-lg sm:text-xl">settings</span>
            </a>
            <?php endif; ?>
            <a href="<?php echo admin_url('modules/deactivate/' . $module['name']); ?>" 
               class="p-1.5 text-gray-600 dark:text-gray-400 hover:text-yellow-600 transition-colors min-h-[36px] min-w-[36px] flex items-center justify-center" title="Devre Dışı Bırak">
                <span class="material-symbols-outlined text-lg sm:text-xl">power_settings_new</span>
            </a>
            
            <?php else: ?>
            <!-- Pasif modül -->
            <a href="<?php echo admin_url('modules/activate/' . $module['name']); ?>" 
               class="p-1.5 text-gray-600 dark:text-gray-400 hover:text-green-600 transition-colors min-h-[36px] min-w-[36px] flex items-center justify-center" title="Aktif Et">
                <span class="material-symbols-outlined text-lg sm:text-xl">play_arrow</span>
            </a>
            <button onclick="confirmDelete('<?php echo htmlspecialchars($module['name']); ?>', '<?php echo htmlspecialchars($module['title']); ?>')"
                    class="p-1.5 text-gray-600 dark:text-gray-400 hover:text-red-600 transition-colors min-h-[36px] min-w-[36px] flex items-center justify-center" title="Sil">
                <span class="material-symbols-outlined text-lg sm:text-xl">delete</span>
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

