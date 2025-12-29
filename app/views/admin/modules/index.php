<?php
/**
 * Modül Yönetimi - Liste Sayfası
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
                <div class="max-w-7xl mx-auto">
        <!-- Başlık -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Modül Yönetimi</h1>
                <p class="text-sm sm:text-base text-gray-500 dark:text-gray-400 mt-1">
                    Toplam <?php echo $totalModules; ?> modül, <?php echo $activeCount; ?> aktif
                </p>
            </div>
            <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" 
                    class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors min-h-[44px] w-full sm:w-auto justify-center sm:justify-start">
                <span class="material-symbols-outlined text-lg sm:text-xl">upload</span>
                <span class="text-sm sm:text-base">Modül Yükle</span>
            </button>
        </div>
        
        <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $_SESSION['flash_type'] === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : ($_SESSION['flash_type'] === 'error' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'); ?>">
            <?php 
            echo htmlspecialchars($_SESSION['flash_message']); 
            unset($_SESSION['flash_message'], $_SESSION['flash_type']);
            ?>
        </div>
        <?php endif; ?>
        
        <!-- Tabs -->
        <div class="mb-6 border-b border-gray-200 dark:border-gray-700 overflow-x-auto scrollbar-hide">
            <nav class="flex gap-2 sm:gap-4 min-w-max sm:min-w-0">
                <button onclick="showTab('installed')" id="tab-installed" 
                        class="tab-btn px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium border-b-2 border-primary text-primary dark:text-primary whitespace-nowrap min-h-[44px]">
                    Özel Modüller (<?php echo count($installedModules); ?>)
                </button>
                <button onclick="showTab('theme')" id="tab-theme"
                        class="tab-btn px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap min-h-[44px]">
                    Tema Modülleri (<?php echo count($themeModules); ?>)
                </button>
                <button onclick="showTab('available')" id="tab-available"
                        class="tab-btn px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap min-h-[44px]">
                    Mevcut Modüller (<?php echo count($availableModules); ?>)
                </button>
                <button onclick="showTab('system')" id="tab-system"
                        class="tab-btn px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap min-h-[44px]">
                    Sistem Modülleri (<?php echo count($systemModules); ?>)
                </button>
            </nav>
        </div>
        
        <!-- Özel Modüller -->
        <div id="panel-installed" class="tab-panel">
            <?php if (empty($installedModules)): ?>
            <div class="text-center py-8 sm:py-12 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 px-4">
                <span class="material-symbols-outlined text-5xl sm:text-6xl text-gray-400">extension_off</span>
                <p class="mt-4 text-base sm:text-lg text-gray-500 dark:text-gray-400">Henüz yüklü modül yok</p>
                <p class="text-xs sm:text-sm text-gray-400 dark:text-gray-500 mt-2">ZIP dosyası yükleyerek veya modules/ klasörüne ekleyerek modül kurabilirsiniz</p>
            </div>
            <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                <?php foreach ($installedModules as $module): ?>
                <?php $isThemeModule = false; ?>
                <?php include __DIR__ . '/partials/module-card.php'; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Tema Modülleri -->
        <div id="panel-theme" class="tab-panel hidden">
            <div class="mb-4 p-3 sm:p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
                <div class="flex items-start gap-2 sm:gap-3">
                    <span class="material-symbols-outlined text-purple-600 dark:text-purple-400 text-lg sm:text-xl flex-shrink-0 mt-0.5">palette</span>
                    <div class="min-w-0">
                        <p class="text-purple-800 dark:text-purple-300 font-medium text-sm sm:text-base">Tema Modülleri Hakkında</p>
                        <p class="text-purple-600 dark:text-purple-400 text-xs sm:text-sm mt-1">
                            Bu modüller aktif tema ile birlikte gelir ve temaya özgüdür. Tema değiştirildiğinde yeni temanın modülleri yüklenir.
                        </p>
                    </div>
                </div>
            </div>
            
            <?php if (empty($themeModules)): ?>
            <div class="text-center py-8 sm:py-12 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 px-4">
                <span class="material-symbols-outlined text-5xl sm:text-6xl text-gray-400">extension_off</span>
                <p class="mt-4 text-base sm:text-lg text-gray-500 dark:text-gray-400">Aktif temada modül yok</p>
                <p class="text-xs sm:text-sm text-gray-400 dark:text-gray-500 mt-2">Aktif temada henüz yüklü modül bulunmuyor</p>
            </div>
            <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                <?php foreach ($themeModules as $module): ?>
                <?php $isThemeModule = true; ?>
                <?php include __DIR__ . '/partials/module-card.php'; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Mevcut (Kurulmamış) Modüller -->
        <div id="panel-available" class="tab-panel hidden">
            <div class="mb-4 p-3 sm:p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                <div class="flex items-start gap-2 sm:gap-3">
                    <span class="material-symbols-outlined text-yellow-600 dark:text-yellow-400 text-lg sm:text-xl flex-shrink-0 mt-0.5">info</span>
                    <div class="min-w-0">
                        <p class="text-yellow-800 dark:text-yellow-300 font-medium text-sm sm:text-base">Mevcut Modüller</p>
                        <p class="text-yellow-600 dark:text-yellow-400 text-xs sm:text-sm mt-1">
                            Bu modüller sisteminizde mevcut ancak henüz kurulmamış. Kurmak için "Kur & Aktif Et" butonuna tıklayın.
                        </p>
                    </div>
                </div>
            </div>
            
            <?php if (empty($availableModules)): ?>
            <div class="text-center py-8 sm:py-12 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 px-4">
                <span class="material-symbols-outlined text-5xl sm:text-6xl text-gray-400">check_circle</span>
                <p class="mt-4 text-base sm:text-lg text-gray-500 dark:text-gray-400">Tüm modüller kurulmuş</p>
                <p class="text-xs sm:text-sm text-gray-400 dark:text-gray-500 mt-2">Sistemde kurulmamış modül bulunmuyor</p>
            </div>
            <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                <?php foreach ($availableModules as $module): ?>
                <?php $isAvailable = true; $isThemeModule = false; ?>
                <?php include __DIR__ . '/partials/module-card.php'; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Sistem Modülleri -->
        <div id="panel-system" class="tab-panel hidden">
            <div class="mb-4 p-3 sm:p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                <div class="flex items-start gap-2 sm:gap-3">
                    <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-lg sm:text-xl flex-shrink-0 mt-0.5">info</span>
                    <div class="min-w-0">
                        <p class="text-blue-800 dark:text-blue-300 font-medium text-sm sm:text-base">Sistem Modülleri Hakkında</p>
                        <p class="text-blue-600 dark:text-blue-400 text-xs sm:text-sm mt-1">
                            Sistem modülleri CMS'in temel işlevlerini sağlar. Bu modüller devre dışı bırakılamaz veya silinemez.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                <?php foreach ($systemModules as $module): ?>
                <?php $isSystem = true; $isThemeModule = false; ?>
                <?php include __DIR__ . '/partials/module-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
                </div>
            </main>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('uploadModal').classList.add('hidden')"></div>
        
        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-4 sm:p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">Modül Yükle</h3>
                <button onclick="document.getElementById('uploadModal').classList.add('hidden')" 
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 min-h-[36px] min-w-[36px] flex items-center justify-center">
                    <span class="material-symbols-outlined text-lg sm:text-xl">close</span>
                </button>
            </div>
            
            <form action="<?php echo admin_url('modules/upload'); ?>" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Modül ZIP Dosyası
                    </label>
                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 sm:p-6 text-center hover:border-primary transition-colors">
                        <input type="file" name="module_zip" accept=".zip" required 
                               class="hidden" id="zipFileInput" onchange="updateFileName(this)">
                        <label for="zipFileInput" class="cursor-pointer block">
                            <span class="material-symbols-outlined text-3xl sm:text-4xl text-gray-400 mb-2 block">folder_zip</span>
                            <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400" id="fileNameDisplay">
                                ZIP dosyası seçin veya sürükleyin
                            </p>
                            <p class="text-xs text-gray-400 mt-1">Maksimum 50MB</p>
                        </label>
                    </div>
                </div>
                
                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-lg mb-4">
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-yellow-600 text-sm">warning</span>
                        <p class="text-xs text-yellow-700 dark:text-yellow-400">
                            Modülü yüklemeden önce güvenilir bir kaynaktan geldiğinden emin olun.
                        </p>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row justify-end gap-3">
                    <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')"
                            class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors min-h-[44px] order-2 sm:order-1">
                        İptal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors min-h-[44px] order-1 sm:order-2">
                        Yükle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div id="detailModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" onclick="closeDetailModal()"></div>
        
        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full p-4 sm:p-6 max-h-[85vh] sm:max-h-[80vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white line-clamp-2 pr-2" id="modalTitle">Modül Detayları</h3>
                <button onclick="closeDetailModal()" 
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 min-h-[36px] min-w-[36px] flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-lg sm:text-xl">close</span>
                </button>
            </div>
            <div id="modalContent">
                <div class="flex justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.tab-btn {
    transition: all 0.2s ease-in-out;
    cursor: pointer;
    background: transparent;
    border: none;
    outline: none;
}

.tab-btn:hover {
    color: #137fec;
}

.tab-btn:focus {
    outline: none;
}

.tab-panel {
    display: block;
}

.tab-panel.hidden {
    display: none !important;
}
</style>

<script>
function showTab(tabName) {
    // Tüm tab butonlarını ve panelleri gizle
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('border-primary', 'text-primary', 'dark:text-primary');
        btn.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
    });
    document.querySelectorAll('.tab-panel').forEach(panel => {
        panel.classList.add('hidden');
    });
    
    // Seçili tab'ı göster
    const selectedTab = document.getElementById('tab-' + tabName);
    if (selectedTab) {
        selectedTab.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
        selectedTab.classList.add('border-primary', 'text-primary', 'dark:text-primary');
    }
    const selectedPanel = document.getElementById('panel-' + tabName);
    if (selectedPanel) {
        selectedPanel.classList.remove('hidden');
    }
}

// Sayfa yüklendiğinde ilk tab'ı göster
document.addEventListener('DOMContentLoaded', function() {
    showTab('installed');
});

function updateFileName(input) {
    const display = document.getElementById('fileNameDisplay');
    if (input.files.length > 0) {
        display.textContent = input.files[0].name;
        display.classList.add('text-primary');
    }
}

function showModuleDetail(moduleName) {
    document.getElementById('detailModal').classList.remove('hidden');
    
    fetch('<?php echo admin_url('modules/detail/'); ?>' + moduleName)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modalTitle').textContent = data.module.title;
                document.getElementById('modalContent').innerHTML = renderModuleDetail(data.module);
            } else {
                document.getElementById('modalContent').innerHTML = '<p class="text-red-500">' + data.message + '</p>';
            }
        })
        .catch(error => {
            document.getElementById('modalContent').innerHTML = '<p class="text-red-500">Bir hata oluştu</p>';
        });
}

function closeDetailModal() {
    document.getElementById('detailModal').classList.add('hidden');
}

function renderModuleDetail(module) {
    return `
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                <div class="w-14 h-14 sm:w-16 sm:h-16 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-2xl sm:text-3xl text-primary">${module.admin_menu?.icon || 'extension'}</span>
                </div>
                <div class="min-w-0 flex-1">
                    <h4 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white line-clamp-2">${module.title}</h4>
                    <p class="text-sm sm:text-base text-gray-500 dark:text-gray-400">v${module.version} ${module.author ? '| ' + module.author : ''}</p>
                </div>
            </div>
            
            <div>
                <h5 class="font-medium text-sm sm:text-base text-gray-900 dark:text-white mb-2">Açıklama</h5>
                <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">${module.description || 'Açıklama yok'}</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <h5 class="font-medium text-gray-900 dark:text-white mb-2">Gereksinimler</h5>
                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        <li>PHP: ${module.requires_php || '7.4'}+</li>
                        <li>CMS: ${module.requires_cms || '1.0'}+</li>
                    </ul>
                </div>
                <div>
                    <h5 class="font-medium text-gray-900 dark:text-white mb-2">Özellikler</h5>
                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        ${module.shortcodes?.length ? '<li>Shortcode desteği</li>' : ''}
                        ${module.widgets?.length ? '<li>Widget içeriyor</li>' : ''}
                        ${module.routes?.length ? '<li>Frontend route</li>' : ''}
                        ${module.settings ? '<li>Ayarlar sayfası</li>' : ''}
                    </ul>
                </div>
            </div>
            
            ${module.website ? `
            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="${module.website}" target="_blank" class="text-primary hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">open_in_new</span>
                    Website
                </a>
            </div>
            ` : ''}
        </div>
    `;
}

function confirmDelete(moduleName, moduleTitle) {
    if (confirm('\"' + moduleTitle + '\" modülünü silmek istediğinize emin misiniz? Bu işlem geri alınamaz!')) {
        window.location.href = '<?php echo admin_url('modules/delete/'); ?>' + moduleName;
    }
}
</script>

<?php include __DIR__ . '/../snippets/footer.php'; ?>

