<?php include __DIR__ . '/../snippets/header.php'; ?>
<?php
// Controller'dan gelen değişkenler: $user, $page, $versions, $message, $messageType
$versions = $versions ?? [];
?>
<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php 
        $currentPage = 'pages';
        include __DIR__ . '/../snippets/sidebar.php'; 
        ?>

        <!-- Main Content -->
        <main class="main-content-with-sidebar flex-1 p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b]">
            <div class="layout-content-container flex flex-col w-full mx-auto max-w-5xl">
                
                <!-- Header -->
                <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <a href="<?php echo admin_url('pages/edit/' . $page['id']); ?>" class="text-gray-500 hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-xl">arrow_back</span>
                            </a>
                            <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Versiyon Geçmişi</h1>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-base">
                            <?php echo esc_html($page['title']); ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary/10 text-primary ml-2">
                                Mevcut: v<?php echo esc_html($page['version'] ?? 1); ?>
                            </span>
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="<?php echo admin_url('pages/edit/' . $page['id']); ?>" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                            <span class="material-symbols-outlined text-xl">edit</span>
                            <span class="text-sm font-medium">Sayfaya Dön</span>
                        </a>
                    </div>
                </header>

                <!-- Mesaj -->
                <?php if (!empty($message)): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 text-red-800 dark:text-red-200'; ?>">
                    <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
                </div>
                <?php endif; ?>

                <!-- Mevcut Versiyon -->
                <div class="rounded-xl border-2 border-primary/30 bg-primary/5 dark:bg-primary/10 p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-primary/20 flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary text-2xl">check_circle</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Mevcut Versiyon (v<?php echo esc_html($page['version'] ?? 1); ?>)</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Son güncelleme: <?php echo date('d.m.Y H:i', strtotime($page['updated_at'])); ?>
                                </p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary text-white">
                            Aktif
                        </span>
                    </div>
                </div>

                <!-- Versiyon Listesi -->
                <?php if (empty($versions)): ?>
                <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 p-12 text-center">
                    <span class="material-symbols-outlined text-gray-300 dark:text-gray-600 text-6xl mb-4">history</span>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Henüz Versiyon Geçmişi Yok</h3>
                    <p class="text-gray-500 dark:text-gray-400">Bu sayfa güncellendiğinde önceki versiyonlar burada görünecek.</p>
                </div>
                <?php else: ?>
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-400">history</span>
                        Önceki Versiyonlar (<?php echo count($versions); ?>)
                    </h2>
                    
                    <?php foreach ($versions as $version): ?>
                    <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 overflow-hidden">
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                    <span class="text-gray-600 dark:text-gray-300 font-semibold">v<?php echo esc_html($version['version_number']); ?></span>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-white"><?php echo esc_html($version['title']); ?></h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo date('d.m.Y H:i', strtotime($version['created_at'])); ?>
                                        <?php if (!empty($version['author_name'])): ?>
                                        <span class="mx-1">•</span>
                                        <?php echo esc_html($version['author_name']); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" 
                                    onclick="showVersionContent(<?php echo $version['id']; ?>)"
                                    class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-sm align-middle mr-1">visibility</span>
                                    İncele
                                </button>
                                <a href="<?php echo admin_url('pages/restore-version/' . $version['id']); ?>" 
                                   onclick="return confirm('Bu versiyona geri dönmek istediğinize emin misiniz? Mevcut içerik kaybolmayacak, yeni bir versiyon olarak kaydedilecek.');"
                                   class="px-3 py-1.5 text-sm bg-amber-100 text-amber-700 hover:bg-amber-200 rounded-lg transition-colors font-medium">
                                    <span class="material-symbols-outlined text-sm align-middle mr-1">restore</span>
                                    Geri Yükle
                                </a>
                            </div>
                        </div>
                        
                        <!-- İçerik Önizlemesi (Gizli) -->
                        <div id="version-content-<?php echo $version['id']; ?>" class="hidden border-t border-gray-200 dark:border-gray-700">
                            <div class="p-4 bg-gray-50 dark:bg-gray-900">
                                <div class="mb-3">
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Özet:</span>
                                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1"><?php echo esc_html($version['excerpt'] ?: '(Özet yok)'); ?></p>
                                </div>
                                <div>
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">İçerik Önizleme:</span>
                                    <div class="mt-1 p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 max-h-64 overflow-y-auto text-sm text-gray-700 dark:text-gray-300">
                                        <?php 
                                        $previewContent = strip_tags($version['content']);
                                        echo esc_html(mb_substr($previewContent, 0, 500)) . (mb_strlen($previewContent) > 500 ? '...' : '');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Bilgi Notu -->
                <div class="mt-6 rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 mt-0.5">info</span>
                        <div>
                            <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-1">Versiyon Sistemi Hakkında</h4>
                            <p class="text-xs text-blue-700 dark:text-blue-400">
                                Her değişiklik yaptığınızda, önceki içerik otomatik olarak bir versiyon olarak kaydedilir. 
                                Maksimum 20 versiyon saklanır, en eski versiyonlar otomatik olarak silinir. 
                                "Geri Yükle" butonuna tıkladığınızda, mevcut içeriğiniz de bir versiyon olarak kaydedilir.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<script>
function showVersionContent(versionId) {
    const contentDiv = document.getElementById('version-content-' + versionId);
    if (contentDiv.classList.contains('hidden')) {
        // Diğer açık olanları kapat
        document.querySelectorAll('[id^="version-content-"]').forEach(el => {
            el.classList.add('hidden');
        });
        contentDiv.classList.remove('hidden');
    } else {
        contentDiv.classList.add('hidden');
    }
}
</script>

</body>
</html>

