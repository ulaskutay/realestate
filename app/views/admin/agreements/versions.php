<?php include __DIR__ . '/../snippets/header.php'; ?>
<?php
// Controller'dan gelen değişkenler: $user, $agreement, $versions, $message, $messageType
$versions = $versions ?? [];
?>
<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php 
        $currentPage = 'agreements';
        include __DIR__ . '/../snippets/sidebar.php'; 
        ?>

        <!-- Main Content -->
        <main class="main-content-with-sidebar flex-1 p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b]">
            <div class="layout-content-container flex flex-col w-full mx-auto max-w-5xl">
                
                <!-- Header -->
                <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <a href="<?php echo admin_url('agreements/edit/' . $agreement['id']); ?>" class="text-gray-500 hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-xl">arrow_back</span>
                            </a>
                            <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Versiyon Geçmişi</h1>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-base"><?php echo esc_html($agreement['title']); ?></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300">
                            <span class="material-symbols-outlined text-sm mr-1">history</span>
                            Mevcut: v<?php echo esc_html($agreement['version'] ?? 1); ?>
                        </span>
                    </div>
                </header>

                <!-- Mesaj -->
                <?php if (!empty($message)): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 text-red-800 dark:text-red-200'; ?>">
                    <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
                </div>
                <?php endif; ?>

                <!-- Mevcut Versiyon -->
                <div class="rounded-xl border-2 border-primary bg-primary/5 dark:bg-primary/10 p-6 mb-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-full bg-primary flex items-center justify-center text-white font-bold">
                                v<?php echo esc_html($agreement['version'] ?? 1); ?>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo esc_html($agreement['title']); ?></h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Mevcut aktif versiyon
                                </p>
                                <div class="flex items-center gap-4 mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">schedule</span>
                                        <?php echo date('d.m.Y H:i', strtotime($agreement['updated_at'])); ?>
                                    </span>
                                    <?php if (!empty($agreement['author_name'])): ?>
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">person</span>
                                        <?php echo esc_html($agreement['author_name']); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                            Aktif
                        </span>
                    </div>
                </div>

                <!-- Versiyon Geçmişi -->
                <section class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Önceki Versiyonlar</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Her güncelleme öncesi içerik burada saklanır.</p>
                    </div>
                    
                    <?php if (empty($versions)): ?>
                    <div class="p-12 text-center">
                        <span class="material-symbols-outlined text-gray-400 text-6xl mb-4">history</span>
                        <p class="text-gray-500 text-lg mb-2">Henüz versiyon geçmişi yok</p>
                        <p class="text-gray-400 text-sm">İlk güncelleme yapıldığında önceki versiyon burada görünecek.</p>
                    </div>
                    <?php else: ?>
                    <div class="divide-y divide-gray-200 dark:divide-white/10">
                        <?php foreach ($versions as $version): ?>
                        <div class="p-6 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 font-medium text-sm">
                                        v<?php echo esc_html($version['version_number']); ?>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-medium text-gray-900 dark:text-white"><?php echo esc_html($version['title']); ?></h3>
                                        <?php if (!empty($version['change_note'])): ?>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            <span class="material-symbols-outlined text-sm align-middle mr-1">edit_note</span>
                                            <?php echo esc_html($version['change_note']); ?>
                                        </p>
                                        <?php endif; ?>
                                        <div class="flex items-center gap-4 mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            <span class="flex items-center gap-1">
                                                <span class="material-symbols-outlined text-sm">schedule</span>
                                                <?php echo date('d.m.Y H:i', strtotime($version['created_at'])); ?>
                                            </span>
                                            <?php if (!empty($version['author_name'])): ?>
                                            <span class="flex items-center gap-1">
                                                <span class="material-symbols-outlined text-sm">person</span>
                                                <?php echo esc_html($version['author_name']); ?>
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="showVersionContent(<?php echo $version['id']; ?>)" 
                                            class="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors" 
                                            title="İçeriği Görüntüle">
                                        <span class="material-symbols-outlined text-xl">visibility</span>
                                    </button>
                                    <a href="<?php echo admin_url('agreements/restore-version/' . $version['id']); ?>" 
                                       onclick="return confirm('Bu versiyona geri dönmek istediğinize emin misiniz? Mevcut içerik de geçmişe kaydedilecektir.');"
                                       class="p-2 text-gray-600 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors" 
                                       title="Bu Versiyona Geri Dön">
                                        <span class="material-symbols-outlined text-xl">restore</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </section>

            </div>
        </main>
    </div>
</div>

<!-- Versiyon İçerik Modal -->
<div id="version-modal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeVersionModal()"></div>
    <div class="fixed inset-4 md:inset-10 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-white/10">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white" id="modal-title">Versiyon İçeriği</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400" id="modal-subtitle"></p>
            </div>
            <button type="button" onclick="closeVersionModal()" class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="flex-1 overflow-auto p-6">
            <div id="modal-content" class="prose dark:prose-invert max-w-none"></div>
        </div>
    </div>
</div>

<script>
// Versiyon içerikleri
const versionContents = {
    <?php foreach ($versions as $version): ?>
    <?php echo $version['id']; ?>: {
        title: <?php echo json_encode($version['title']); ?>,
        content: <?php echo json_encode($version['content']); ?>,
        version: <?php echo $version['version_number']; ?>,
        date: <?php echo json_encode(date('d.m.Y H:i', strtotime($version['created_at']))); ?>
    },
    <?php endforeach; ?>
};

function showVersionContent(versionId) {
    const version = versionContents[versionId];
    if (!version) return;
    
    document.getElementById('modal-title').textContent = 'Versiyon ' + version.version + ' - ' + version.title;
    document.getElementById('modal-subtitle').textContent = version.date;
    document.getElementById('modal-content').innerHTML = version.content || '<p class="text-gray-500">İçerik boş</p>';
    document.getElementById('version-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeVersionModal() {
    document.getElementById('version-modal').classList.add('hidden');
    document.body.style.overflow = '';
}

// ESC tuşu ile kapat
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeVersionModal();
    }
});
</script>

</body>
</html>

