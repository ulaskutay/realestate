<?php
$submission = $submission ?? null;
$form = $form ?? null;
if (!$submission) {
    header('Location: ' . admin_url('forms'));
    exit;
}
$formId = (int)($form['id'] ?? 0);
$formName = isset($form['name']) ? esc_html($form['name']) : 'Form';
$submissionId = (int)$submission['id'];
$data = $submission['data'] ?? [];
if (is_string($data)) {
    $data = json_decode($data, true);
    if (!is_array($data)) $data = [];
}
$fields = isset($form['fields']) && is_array($form['fields']) ? $form['fields'] : [];
$statusLabels = ['new' => 'Yeni', 'read' => 'Okunmuş', 'spam' => 'Spam', 'archived' => 'Arşivlenmiş'];
$currentStatus = $submission['status'] ?? 'new';
$backUrl = admin_url('forms/submissions/' . $formId);
$currentPage = 'forms';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?php echo isset($title) ? esc_html($title) : 'Gönderim detayı'; ?></title>
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
        <?php include __DIR__ . '/../snippets/sidebar.php'; ?>

        <main class="main-content-with-sidebar flex-1 p-6 lg:p-10 lg:ml-64 bg-gray-50 dark:bg-[#15202b]">
            <div class="max-w-3xl mx-auto">
                <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div class="flex items-center gap-3">
                        <a href="<?php echo $backUrl; ?>" class="p-2 text-gray-600 dark:text-gray-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Gönderimlere dön">
                            <span class="material-symbols-outlined text-xl">arrow_back</span>
                        </a>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Lead / Gönderim detayı</h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo $formName; ?></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <form id="status-form" class="flex items-center gap-2">
                            <select name="status" class="px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                <?php foreach ($statusLabels as $val => $label): ?>
                                <option value="<?php echo esc_attr($val); ?>" <?php echo ($currentStatus === $val) ? 'selected' : ''; ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="px-4 py-2 text-sm font-medium bg-primary text-white rounded-lg hover:bg-primary/90">Güncelle</button>
                        </form>
                        <button type="button" id="btn-delete" class="px-4 py-2 text-sm font-medium rounded-lg border border-red-300 dark:border-red-700 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">Sil</button>
                    </div>
                </header>

                <div class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Tarih</p>
                            <p class="text-gray-900 dark:text-white font-medium"><?php echo $submission['created_at'] ? date('d.m.Y H:i', strtotime($submission['created_at'])) : '-'; ?></p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">IP Adresi</p>
                            <p class="text-gray-900 dark:text-white font-medium"><?php echo esc_html($submission['ip_address'] ?? '-'); ?></p>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Form verileri</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <?php
                            $shown = [];
                            foreach ($fields as $field):
                                $type = $field['type'] ?? $field['field_type'] ?? '';
                                if (in_array($type, ['heading', 'paragraph', 'divider', 'html', 'submit'], true)) continue;
                                $name = $field['name'] ?? $field['field_name'] ?? '';
                                if ($name === '') continue;
                                $shown[$name] = true;
                                $label = $field['label'] ?? $field['field_label'] ?? $name;
                                $val = $data[$name] ?? null;
                                $disp = is_array($val) ? implode(', ', $val) : ($val !== null && $val !== '' ? (string)$val : '-');
                            ?>
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2"><?php echo esc_html($label); ?></p>
                                <p class="text-gray-900 dark:text-white whitespace-pre-wrap break-words"><?php echo esc_html($disp); ?></p>
                            </div>
                            <?php endforeach; ?>
                            <?php foreach ($data as $key => $val): if (isset($shown[$key])) continue; ?>
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2"><?php echo esc_html($key); ?></p>
                                <p class="text-gray-900 dark:text-white whitespace-pre-wrap break-words"><?php echo esc_html(is_array($val) ? implode(', ', $val) : (string)$val); ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <details class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                        <summary class="px-6 py-4 cursor-pointer text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Teknik bilgiler</summary>
                        <div class="px-6 pb-4 space-y-2 text-sm text-gray-500 dark:text-gray-400">
                            <p class="break-all"><strong>User Agent:</strong> <?php echo esc_html($submission['user_agent'] ?? '-'); ?></p>
                            <p class="break-all"><strong>Referrer:</strong> <?php echo esc_html($submission['referrer'] ?? '-'); ?></p>
                        </div>
                    </details>
                </div>
            </div>
        </main>
    </div>
    <script>
    (function(){
        var backUrl = '<?php echo $backUrl; ?>';
        var f = document.getElementById('status-form');
        if (f) f.addEventListener('submit', function(e) {
            e.preventDefault();
            var fd = new FormData(f);
            fetch('<?php echo admin_url('forms/update-submission-status/' . $submissionId); ?>', { method: 'POST', body: fd })
                .then(function(r){ return r.json(); })
                .then(function(res){ if (res.success) location.reload(); else alert(res.message || 'Güncellenemedi'); })
                .catch(function(){ alert('Hata'); });
        });
        var btnDel = document.getElementById('btn-delete');
        if (btnDel) btnDel.addEventListener('click', function() {
            if (!confirm('Bu gönderimi silmek istediğinize emin misiniz?')) return;
            fetch('<?php echo admin_url('forms/delete-submission/' . $submissionId); ?>', { method: 'POST' })
                .then(function(r){ return r.json(); })
                .then(function(res){ if (res.success) location.href = backUrl; else alert(res.message || 'Silinemedi'); })
                .catch(function(){ alert('Hata'); });
        });
    })();
    </script>
</body>
</html>
