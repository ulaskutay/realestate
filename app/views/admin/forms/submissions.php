<?php
$formId = (int)($form['id'] ?? 0);
$formName = isset($form['name']) ? esc_html($form['name']) : 'Form';
$submissions = $submissions ?? [];
$totalCount = (int)($totalCount ?? 0);
$pageNum = (int)($currentPage ?? 1);
$totalPages = (int)($totalPages ?? 1);
$currentStatus = $currentStatus ?? null;
$stats = $stats ?? ['total' => 0, 'new_count' => 0, 'read_count' => 0, 'archived_count' => 0];
$message = $message ?? null;
$messageType = $messageType ?? 'success';

$baseUrl = admin_url('forms/submissions/' . $formId);
$statusLabels = ['new' => 'Yeni', 'read' => 'Okunmuş', 'spam' => 'Spam', 'archived' => 'Arşivlenmiş'];

$currentPage = 'forms';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?php echo isset($title) ? esc_html($title) : 'Form Gönderimleri'; ?></title>
    <script>
        (function(){var d=document.documentElement,k='admin_dark_mode';try{var s=localStorage.getItem(k);if(s==='dark'||s==='light'){d.classList.toggle('dark',s==='dark');return;} }catch(e){}d.classList.toggle('dark',window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches);})();
    </script>
    <link href="<?php echo rtrim(site_url(), '/') . '/admin/css/admin-dashboard.css'; ?>" rel="stylesheet"/>
    <script src="<?php echo rtrim(site_url(), '/') . '/admin/js/dark-mode.js'; ?>"></script>
    <script src="<?php echo ViewRenderer::assetUrl('assets/js/tailwind-admin.min.js'); ?>"></script>
    <link rel="stylesheet" href="<?php echo ViewRenderer::assetUrl('assets/css/fonts.css'); ?>">
    <script>
        tailwind.config = { darkMode: "class", theme: { extend: { colors: { "primary": "#137fec", "background-light": "#f6f7f8", "background-dark": "#101922" }, fontFamily: { "display": ["Inter", "sans-serif"] } } } };
    </script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark">
    <div class="flex min-h-screen">
        <?php $currentPageName = 'forms'; include __DIR__ . '/../snippets/sidebar.php'; ?>

        <main class="main-content-with-sidebar flex-1 p-6 lg:p-10 lg:ml-64 bg-gray-50 dark:bg-[#15202b]">
            <div class="max-w-7xl mx-auto">
                <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div class="flex items-center gap-3">
                        <a href="<?php echo admin_url('forms'); ?>" class="p-2 text-gray-600 dark:text-gray-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Formlara dön">
                            <span class="material-symbols-outlined text-xl">arrow_back</span>
                        </a>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo $formName; ?> – Gönderimler</h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Toplam <?php echo $totalCount; ?> gönderim</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="<?php echo admin_url('forms/edit/' . $formId); ?>" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm font-medium">
                            <span class="material-symbols-outlined text-lg">edit</span> Formu Düzenle
                        </a>
                        <?php if ($totalCount > 0): ?>
                        <a href="<?php echo admin_url('forms/export/' . $formId); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 text-sm font-medium">
                            <span class="material-symbols-outlined text-lg">download</span> CSV İndir
                        </a>
                        <?php endif; ?>
                    </div>
                </header>

                <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200'; ?>">
                    <?php echo esc_html($message); ?>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <a href="<?php echo $baseUrl; ?>" class="rounded-xl border p-4 transition-colors <?php echo !$currentStatus ? 'ring-2 ring-primary border-primary/50' : 'border-gray-200 dark:border-white/10 hover:border-primary/30'; ?> bg-white dark:bg-gray-800/50">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo (int)($stats['total'] ?? 0); ?></p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Toplam</p>
                    </a>
                    <a href="<?php echo $baseUrl . '?status=new'; ?>" class="rounded-xl border p-4 transition-colors <?php echo $currentStatus === 'new' ? 'ring-2 ring-primary border-primary/50' : 'border-gray-200 dark:border-white/10 hover:border-primary/30'; ?> bg-white dark:bg-gray-800/50">
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400"><?php echo (int)($stats['new_count'] ?? 0); ?></p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Yeni</p>
                    </a>
                    <a href="<?php echo $baseUrl . '?status=read'; ?>" class="rounded-xl border p-4 transition-colors <?php echo $currentStatus === 'read' ? 'ring-2 ring-primary border-primary/50' : 'border-gray-200 dark:border-white/10 hover:border-primary/30'; ?> bg-white dark:bg-gray-800/50">
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400"><?php echo (int)($stats['read_count'] ?? 0); ?></p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Okunmuş</p>
                    </a>
                    <a href="<?php echo $baseUrl . '?status=archived'; ?>" class="rounded-xl border p-4 transition-colors <?php echo $currentStatus === 'archived' ? 'ring-2 ring-primary border-primary/50' : 'border-gray-200 dark:border-white/10 hover:border-primary/30'; ?> bg-white dark:bg-gray-800/50">
                        <p class="text-2xl font-bold text-gray-600 dark:text-gray-400"><?php echo (int)($stats['archived_count'] ?? 0); ?></p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Arşivlenmiş</p>
                    </a>
                </div>

                <section class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50 overflow-hidden">
                    <?php if (empty($submissions)): ?>
                    <div class="p-16 text-center">
                        <span class="material-symbols-outlined text-6xl text-gray-300 dark:text-gray-600">inbox</span>
                        <p class="mt-4 text-gray-600 dark:text-gray-400">Henüz gönderim yok</p>
                    </div>
                    <?php else: ?>
                    <div class="p-4 border-b border-gray-200 dark:border-white/10 flex flex-wrap items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-600 dark:text-gray-400">
                            <input type="checkbox" id="select-all" class="w-4 h-4 rounded text-primary border-gray-300 dark:border-gray-600">
                            Tümünü seç
                        </label>
                        <select id="bulk-action" class="px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                            <option value="">Toplu işlem</option>
                            <option value="mark_read">Okundu işaretle</option>
                            <option value="archive">Arşivle</option>
                            <option value="mark_spam">Spam</option>
                            <option value="delete">Sil</option>
                        </select>
                        <button type="button" id="apply-bulk" class="px-4 py-2 text-sm rounded-lg bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-500">
                            Uygula
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                                <tr>
                                    <th class="px-4 py-3 text-left w-10"><input type="checkbox" id="th-check" class="w-4 h-4 rounded text-primary border-gray-300 dark:border-gray-600" title="Tümünü seç"></th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Tarih</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Özet</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Durum</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase w-24">İşlem</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                <?php foreach ($submissions as $s): 
                                    $sData = is_array($s['data'] ?? null) ? $s['data'] : [];
                                    $preview = array_slice($sData, 0, 3);
                                    $status = $s['status'] ?? 'new';
                                ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 <?php echo $status === 'new' ? 'bg-blue-50/40 dark:bg-blue-900/20' : ''; ?>">
                                    <td class="px-4 py-3"><input type="checkbox" class="row-cb w-4 h-4 rounded text-primary border-gray-300 dark:border-gray-600" value="<?php echo (int)$s['id']; ?>"></td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                        <?php echo date('d.m.Y', strtotime($s['created_at'])); ?><br>
                                        <span class="text-xs text-gray-500"><?php echo date('H:i', strtotime($s['created_at'])); ?></span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="max-w-sm space-y-0.5">
                                            <?php foreach ($preview as $k => $v): ?>
                                            <p class="text-sm text-gray-700 dark:text-gray-300 truncate"><span class="text-gray-500"><?php echo esc_html($k); ?>:</span> <?php echo esc_html(is_array($v) ? implode(', ', $v) : (string)$v); ?></p>
                                            <?php endforeach; ?>
                                            <?php if (count($sData) > 3): ?><p class="text-xs text-gray-400">+<?php echo count($sData)-3; ?> alan</p><?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php
                                        $statusClass = 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300';
                                        if ($status === 'read') $statusClass = 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300';
                                        elseif ($status === 'spam') $statusClass = 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300';
                                        elseif ($status === 'archived') $statusClass = 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300';
                                        ?>
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusClass; ?>"><?php echo $statusLabels[$status] ?? 'Yeni'; ?></span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="<?php echo admin_url('forms/submission/' . (int)$s['id']); ?>" class="inline-flex p-2 rounded-lg text-gray-500 hover:text-primary hover:bg-primary/10" title="Görüntüle"> <span class="material-symbols-outlined text-xl">visibility</span> </a>
                                        <button type="button" class="btn-delete p-2 rounded-lg text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20" data-id="<?php echo (int)$s['id']; ?>" title="Sil"> <span class="material-symbols-outlined text-xl">delete</span> </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($totalPages > 1): 
                        $query = $currentStatus ? '&status=' . $currentStatus : '';
                    ?>
                    <div class="p-4 border-t border-gray-200 dark:border-white/10 flex items-center justify-between text-sm">
                        <span class="text-gray-500">Sayfa <?php echo $pageNum; ?> / <?php echo $totalPages; ?></span>
                        <div class="flex gap-2">
                            <?php if ($pageNum > 1): ?><a href="<?php echo $baseUrl . '?p=' . ($pageNum - 1) . $query; ?>" class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700">Önceki</a><?php endif; ?>
                            <?php if ($pageNum < $totalPages): ?><a href="<?php echo $baseUrl . '?p=' . ($pageNum + 1) . $query; ?>" class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700">Sonraki</a><?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </section>
            </div>
        </main>
    </div>

    <script>
    (function(){
        var BASE = '<?php echo rtrim(admin_url("forms"), "/"); ?>';

        document.addEventListener('click', function(e){
            var delBtn = e.target.closest('.btn-delete');
            if (delBtn) {
                e.preventDefault();
                if (!confirm('Bu gönderimi silmek istediğinize emin misiniz?')) return;
                var id = delBtn.getAttribute('data-id');
                if (!id) return;
                fetch(BASE + '/delete-submission/' + id, { method: 'POST' }).then(function(r){ return r.json(); }).then(function(res){
                    if (res.success) location.reload(); else alert(res.message || 'Silinemedi');
                }).catch(function(){ alert('Hata oluştu'); });
            }
        });

        var selAll = document.getElementById('select-all');
        var thCheck = document.getElementById('th-check');
        if (selAll) selAll.addEventListener('change', function(){ var on = this.checked; document.querySelectorAll('.row-cb').forEach(function(cb){ cb.checked = on; }); if (thCheck) thCheck.checked = on; });
        if (thCheck) thCheck.addEventListener('change', function(){ var on = this.checked; document.querySelectorAll('.row-cb').forEach(function(cb){ cb.checked = on; }); if (selAll) selAll.checked = on; });

        var applyBulk = document.getElementById('apply-bulk');
        if (applyBulk) applyBulk.addEventListener('click', function(){
            var action = document.getElementById('bulk-action').value;
            if (!action) { alert('İşlem seçin'); return; }
            var ids = [];
            document.querySelectorAll('.row-cb:checked').forEach(function(cb){ ids.push(cb.value); });
            if (ids.length === 0) { alert('En az bir gönderim seçin'); return; }
            if (action === 'delete' && !confirm(ids.length + ' gönderimi silinecek. Emin misiniz?')) return;
            var fd = new FormData();
            fd.append('ids', JSON.stringify(ids));
            fd.append('action', action);
            fetch(BASE + '/bulk-submission-action', { method: 'POST', body: fd }).then(function(r){ return r.json(); }).then(function(res){
                if (res.success) location.reload(); else alert(res.message || 'İşlem başarısız');
            }).catch(function(){ alert('Hata'); });
        });
    })();
    </script>
</body>
</html>
