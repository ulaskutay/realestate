<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo isset($title) ? esc_html($title) : 'Form Gönderimleri'; ?></title>
    
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
    
    
    
    
    <!-- Custom CSS -->
    <link href="<?php echo rtrim(site_url(), '/') . '/admin/css/admin-dashboard.css'; ?>" rel="stylesheet"/>
    
    <!-- Dark Mode Toggle Script -->
    <script src="<?php echo rtrim(site_url(), '/') . '/admin/js/dark-mode.js'; ?>"></script>
    
    <!-- Tailwind CSS -->
    <script src="<?php echo ViewRenderer::assetUrl('assets/js/tailwind-admin.min.js'); ?>"></script>
    
    <!-- Google Fonts - Inter -->
    <link rel="stylesheet" href="<?php echo ViewRenderer::assetUrl('assets/css/fonts.css'); ?>">
    
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
            $currentPage = 'forms';
            include __DIR__ . '/../snippets/sidebar.php'; 
            ?>

            <!-- Main Content -->
            <main class="main-content-with-sidebar flex-1 p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b]">
                <div class="layout-content-container flex flex-col w-full mx-auto max-w-7xl">
                    <!-- PageHeading -->
                    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <div class="flex items-center gap-4">
                            <a href="<?php echo admin_url('forms'); ?>" class="p-2 text-gray-600 dark:text-gray-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors">
                                <span class="material-symbols-outlined text-xl">arrow_back</span>
                            </a>
                            <div class="flex flex-col gap-1">
                                <p class="text-gray-900 dark:text-white text-2xl font-bold tracking-tight"><?php echo esc_html($form['name']); ?> - Gönderimler</p>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">Toplam <?php echo esc_html($totalCount); ?> gönderim</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="<?php echo admin_url('forms/edit/' . $form['id']); ?>" class="flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                <span class="material-symbols-outlined text-xl">edit</span>
                                <span class="text-sm font-medium">Formu Düzenle</span>
                            </a>
                            <?php if ($totalCount > 0): ?>
                            <a href="<?php echo admin_url('forms/export/' . $form['id']); ?>" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                <span class="material-symbols-outlined text-xl">download</span>
                                <span class="text-sm font-medium">CSV İndir</span>
                            </a>
                            <?php endif; ?>
                        </div>
                    </header>

                    <!-- Success/Error Message -->
                    <?php if (isset($message) && $message): ?>
                        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200'; ?>">
                            <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Stats -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <a href="<?php echo admin_url('forms/submissions/' . $form['id']); ?>" class="rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark p-4 hover:border-primary/30 transition-colors <?php echo !$currentStatus ? 'ring-2 ring-primary' : ''; ?>">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo esc_html($stats['total'] ?? 0); ?></p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Toplam</p>
                        </a>
                        <a href="<?php echo admin_url('forms/submissions/' . $form['id'] . '?status=new'); ?>" class="rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark p-4 hover:border-primary/30 transition-colors <?php echo $currentStatus === 'new' ? 'ring-2 ring-primary' : ''; ?>">
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400"><?php echo esc_html($stats['new_count'] ?? 0); ?></p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Yeni</p>
                        </a>
                        <a href="<?php echo admin_url('forms/submissions/' . $form['id'] . '?status=read'); ?>" class="rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark p-4 hover:border-primary/30 transition-colors <?php echo $currentStatus === 'read' ? 'ring-2 ring-primary' : ''; ?>">
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400"><?php echo esc_html($stats['read_count'] ?? 0); ?></p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Okunmuş</p>
                        </a>
                        <a href="<?php echo admin_url('forms/submissions/' . $form['id'] . '?status=archived'); ?>" class="rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark p-4 hover:border-primary/30 transition-colors <?php echo $currentStatus === 'archived' ? 'ring-2 ring-primary' : ''; ?>">
                            <p class="text-2xl font-bold text-gray-600 dark:text-gray-400"><?php echo esc_html($stats['archived_count'] ?? 0); ?></p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Arşivlenmiş</p>
                        </a>
                    </div>

                    <!-- Submissions List -->
                    <section class="rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark overflow-hidden">
                        <?php if (empty($submissions)): ?>
                            <div class="p-12 text-center">
                                <span class="material-symbols-outlined text-gray-400 dark:text-gray-600 text-6xl mb-4">inbox</span>
                                <p class="text-gray-500 dark:text-gray-400 text-lg mb-2">Henüz gönderim yok</p>
                                <p class="text-gray-400 dark:text-gray-500 text-sm">Bu forma henüz bir gönderim yapılmamış.</p>
                            </div>
                        <?php else: ?>
                            <!-- Bulk Actions -->
                            <div class="p-4 border-b border-gray-200 dark:border-white/10 flex items-center gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" id="select-all" class="w-4 h-4 rounded text-primary focus:ring-primary border-gray-300 dark:border-gray-600">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Tümünü Seç</span>
                                </label>
                                <div class="flex-1"></div>
                                <select id="bulk-action" class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                    <option value="">Toplu İşlem</option>
                                    <option value="mark_read">Okundu İşaretle</option>
                                    <option value="archive">Arşivle</option>
                                    <option value="mark_spam">Spam İşaretle</option>
                                    <option value="delete">Sil</option>
                                </select>
                                <button type="button" id="apply-bulk-action" class="px-3 py-1.5 text-sm bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                                    Uygula
                                </button>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                                        <tr>
                                            <th class="px-4 py-3 text-left w-10"></th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Tarih</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Özet</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Durum</th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                        <?php foreach ($submissions as $submission): ?>
                                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors <?php echo $submission['status'] === 'new' ? 'bg-blue-50/30 dark:bg-blue-900/10' : ''; ?>">
                                                <td class="px-4 py-3">
                                                    <input type="checkbox" class="submission-checkbox w-4 h-4 rounded text-primary focus:ring-primary border-gray-300 dark:border-gray-600" data-id="<?php echo esc_attr($submission['id']); ?>">
                                                </td>
                                                <td class="px-4 py-3">
                                                    <p class="text-sm text-gray-900 dark:text-white"><?php echo date('d.m.Y', strtotime($submission['created_at'])); ?></p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo date('H:i', strtotime($submission['created_at'])); ?></p>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="max-w-md">
                                                        <?php 
                                                        $previewData = is_array($submission['data']) ? $submission['data'] : [];
                                                        $previewFields = array_slice($previewData, 0, 3);
                                                        ?>
                                                        <?php foreach ($previewFields as $key => $value): ?>
                                                            <p class="text-sm text-gray-700 dark:text-gray-300 truncate">
                                                                <span class="text-gray-500 dark:text-gray-400"><?php echo esc_html($key); ?>:</span>
                                                                <?php echo esc_html(is_array($value) ? implode(', ', $value) : $value); ?>
                                                            </p>
                                                        <?php endforeach; ?>
                                                        <?php if (count($previewData) > 3): ?>
                                                            <p class="text-xs text-gray-400">+<?php echo count($previewData) - 3; ?> alan daha...</p>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <?php 
                                                    $statusClasses = [
                                                        'new' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300',
                                                        'read' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
                                                        'spam' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
                                                        'archived' => 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-300'
                                                    ];
                                                    $statusLabels = [
                                                        'new' => 'Yeni',
                                                        'read' => 'Okunmuş',
                                                        'spam' => 'Spam',
                                                        'archived' => 'Arşivlenmiş'
                                                    ];
                                                    ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusClasses[$submission['status']] ?? $statusClasses['new']; ?>">
                                                        <?php echo $statusLabels[$submission['status']] ?? 'Yeni'; ?>
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <div class="flex items-center justify-end gap-1">
                                                        <button type="button" class="view-submission-btn p-2 text-gray-600 dark:text-gray-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" data-id="<?php echo esc_attr($submission['id']); ?>" title="Görüntüle">
                                                            <span class="material-symbols-outlined text-xl">visibility</span>
                                                        </button>
                                                        <button type="button" class="delete-submission-btn p-2 text-gray-600 dark:text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" data-id="<?php echo esc_attr($submission['id']); ?>" title="Sil">
                                                            <span class="material-symbols-outlined text-xl">delete</span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                                <div class="p-4 border-t border-gray-200 dark:border-white/10 flex items-center justify-between">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Sayfa <?php echo $currentPage; ?> / <?php echo $totalPages; ?>
                                    </p>
                                    <div class="flex items-center gap-2">
                                        <?php if ($currentPage > 1): ?>
                                            <a href="<?php echo admin_url('forms/submissions/' . $form['id'] . '?p=' . ($currentPage - 1) . ($currentStatus ? '&status=' . $currentStatus : '')); ?>" class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                                Önceki
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($currentPage < $totalPages): ?>
                                            <a href="<?php echo admin_url('forms/submissions/' . $form['id'] . '?p=' . ($currentPage + 1) . ($currentStatus ? '&status=' . $currentStatus : '')); ?>" class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                                Sonraki
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </section>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Submission Detail Modal -->
    <div id="submission-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50" onclick="closeSubmissionModal()"></div>
        <div class="absolute inset-4 md:inset-10 lg:inset-20 bg-white dark:bg-gray-900 rounded-xl shadow-2xl overflow-hidden flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-white/10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Gönderim Detayı</h3>
                <button type="button" onclick="closeSubmissionModal()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div id="submission-modal-content" class="flex-1 overflow-y-auto p-6">
                <!-- İçerik buraya yüklenecek -->
            </div>
            <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                <div class="flex items-center gap-2">
                    <select id="modal-status-select" class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                        <option value="new">Yeni</option>
                        <option value="read">Okunmuş</option>
                        <option value="spam">Spam</option>
                        <option value="archived">Arşivlenmiş</option>
                    </select>
                    <button type="button" id="update-status-btn" class="px-3 py-1.5 text-sm bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        Durumu Güncelle
                    </button>
                </div>
                <button type="button" onclick="closeSubmissionModal()" class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    Kapat
                </button>
            </div>
        </div>
    </div>
    
    <script>
        let currentSubmissionId = null;
        
        // Tümünü Seç
        document.getElementById('select-all')?.addEventListener('change', function() {
            document.querySelectorAll('.submission-checkbox').forEach(cb => {
                cb.checked = this.checked;
            });
        });
        
        // Toplu İşlem
        document.getElementById('apply-bulk-action')?.addEventListener('click', async function() {
            const action = document.getElementById('bulk-action').value;
            if (!action) {
                alert('Lütfen bir işlem seçin');
                return;
            }
            
            const selectedIds = Array.from(document.querySelectorAll('.submission-checkbox:checked')).map(cb => cb.dataset.id);
            if (selectedIds.length === 0) {
                alert('Lütfen en az bir gönderim seçin');
                return;
            }
            
            if (action === 'delete' && !confirm(`${selectedIds.length} gönderimi silmek istediğinizden emin misiniz?`)) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('ids', JSON.stringify(selectedIds));
                formData.append('action', action);
                
                const response = await fetch('<?php echo admin_url('forms/bulk-submission-action'); ?>', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'İşlem başarısız');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('İşlem sırasında hata oluştu');
            }
        });
        
        // Gönderim Görüntüle
        document.querySelectorAll('.view-submission-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                const id = this.dataset.id;
                await viewSubmission(id);
            });
        });
        
        async function viewSubmission(id) {
            currentSubmissionId = id;
            
            try {
                const response = await fetch(`<?php echo admin_url('forms/view-submission/'); ?>${id}`);
                const data = await response.json();
                
                if (data.success) {
                    renderSubmissionDetail(data.submission, data.form);
                    document.getElementById('submission-modal').classList.remove('hidden');
                    document.getElementById('modal-status-select').value = data.submission.status;
                } else {
                    alert(data.message || 'Gönderim yüklenemedi');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Gönderim yüklenemedi');
            }
        }
        
        function renderSubmissionDetail(submission, form) {
            const content = document.getElementById('submission-modal-content');
            
            let html = `
                <div class="space-y-6">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Tarih</p>
                            <p class="text-gray-900 dark:text-white font-medium">${new Date(submission.created_at).toLocaleString('tr-TR')}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">IP Adresi</p>
                            <p class="text-gray-900 dark:text-white font-medium">${submission.ip_address || '-'}</p>
                        </div>
                    </div>
                    
                    <hr class="border-gray-200 dark:border-white/10">
                    
                    <div class="space-y-4">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider">Form Verileri</h4>
            `;
            
            // Form alanlarını göster
            if (form && form.fields) {
                form.fields.forEach(field => {
                    if (['heading', 'paragraph', 'divider'].includes(field.type)) return;
                    
                    const value = submission.data[field.name] || '-';
                    html += `
                        <div class="bg-gray-50 dark:bg-white/5 rounded-lg p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">${escapeHtml(field.label)}</p>
                            <p class="text-gray-900 dark:text-white">${escapeHtml(Array.isArray(value) ? value.join(', ') : value)}</p>
                        </div>
                    `;
                });
            } else {
                // Form alanları yoksa data'yı doğrudan göster
                Object.entries(submission.data || {}).forEach(([key, value]) => {
                    html += `
                        <div class="bg-gray-50 dark:bg-white/5 rounded-lg p-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">${escapeHtml(key)}</p>
                            <p class="text-gray-900 dark:text-white">${escapeHtml(Array.isArray(value) ? value.join(', ') : value)}</p>
                        </div>
                    `;
                });
            }
            
            html += `
                    </div>
                    
                    <hr class="border-gray-200 dark:border-white/10">
                    
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <p><strong>User Agent:</strong> ${escapeHtml(submission.user_agent || '-')}</p>
                        <p><strong>Referrer:</strong> ${escapeHtml(submission.referrer || '-')}</p>
                    </div>
                </div>
            `;
            
            content.innerHTML = html;
        }
        
        function closeSubmissionModal() {
            document.getElementById('submission-modal').classList.add('hidden');
            currentSubmissionId = null;
        }
        
        // Durum Güncelle
        document.getElementById('update-status-btn')?.addEventListener('click', async function() {
            if (!currentSubmissionId) return;
            
            const status = document.getElementById('modal-status-select').value;
            
            try {
                const formData = new FormData();
                formData.append('status', status);
                
                const response = await fetch(`<?php echo admin_url('forms/update-submission-status/'); ?>${currentSubmissionId}`, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Durum güncellenemedi');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Durum güncellenemedi');
            }
        });
        
        // Gönderim Sil
        document.querySelectorAll('.delete-submission-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                if (!confirm('Bu gönderimi silmek istediğinizden emin misiniz?')) return;
                
                const id = this.dataset.id;
                
                try {
                    const response = await fetch(`<?php echo admin_url('forms/delete-submission/'); ?>${id}`, {
                        method: 'POST'
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Gönderim silinemedi');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Gönderim silinemedi');
                }
            });
        });
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // ESC tuşu ile modal kapat
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSubmissionModal();
            }
        });
    </script>
</body>
</html>

