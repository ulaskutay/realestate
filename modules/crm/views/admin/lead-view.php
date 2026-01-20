<?php
// Lead detail view
$lead = $lead ?? null;
$notes = $notes ?? [];
$tasks = $tasks ?? [];
$conversions = $conversions ?? [];

if (!$lead) {
    echo '<p class="text-red-500">Lead bulunamadı</p>';
    return;
}

$statusLabels = [
    'new' => 'Yeni',
    'contacted' => 'İletişimde',
    'quoted' => 'Teklif Verildi',
    'closed' => 'Kapandı',
    'cancelled' => 'İptal'
];

$sourceLabels = ['meta' => 'Meta/Facebook', 'form' => 'Form', 'manual' => 'Manuel'];
?>

<!-- Header -->
<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div class="flex flex-col gap-2">
        <div class="flex items-center gap-3">
            <a href="<?php echo admin_url('module/crm'); ?>" class="text-gray-500 dark:text-gray-400 hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight"><?php echo esc_html($lead['name']); ?></h1>
        </div>
        <p class="text-gray-500 dark:text-gray-400 text-base">Lead detayları ve takip bilgileri</p>
    </div>
    <div class="flex gap-2">
        <?php if (!empty($lead['phone'])): ?>
            <?php 
            $cleanPhone = preg_replace('/[^0-9]/', '', $lead['phone']);
            $whatsappMessage = $whatsappMessage ?? 'Merhaba ' . esc_html($lead['name']) . ', size nasıl yardımcı olabilirim?';
            $whatsappUrl = 'https://wa.me/' . $cleanPhone . '?text=' . urlencode($whatsappMessage);
            ?>
            <a href="tel:<?php echo esc_attr($lead['phone']); ?>" class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <span class="material-symbols-outlined text-xl">call</span>
                <span class="text-sm font-medium">Ara</span>
            </a>
            <a href="<?php echo $whatsappUrl; ?>" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                <span class="material-symbols-outlined text-xl">chat</span>
                <span class="text-sm font-medium">WhatsApp</span>
            </a>
        <?php endif; ?>
        <a href="<?php echo admin_url('module/crm/lead_edit/' . $lead['id']); ?>" class="flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
            <span class="material-symbols-outlined text-xl">edit</span>
            <span class="text-sm font-medium">Düzenle</span>
        </a>
    </div>
</header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Sol Sütun - Lead Bilgileri -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Temel Bilgiler -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Temel Bilgiler</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">İsim</label>
                    <p class="text-gray-900 dark:text-white mt-1"><?php echo esc_html($lead['name']); ?></p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Telefon</label>
                    <p class="text-gray-900 dark:text-white mt-1">
                        <?php if (!empty($lead['phone'])): ?>
                            <a href="tel:<?php echo esc_attr($lead['phone']); ?>" class="hover:text-primary"><?php echo esc_html($lead['phone']); ?></a>
                        <?php else: ?>
                            <span class="text-gray-400">-</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">E-posta</label>
                    <p class="text-gray-900 dark:text-white mt-1">
                        <?php if (!empty($lead['email'])): ?>
                            <a href="mailto:<?php echo esc_attr($lead['email']); ?>" class="hover:text-primary"><?php echo esc_html($lead['email']); ?></a>
                        <?php else: ?>
                            <span class="text-gray-400">-</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Durum</label>
                    <p class="mt-1">
                        <span class="px-2 py-1 text-xs rounded-full <?php 
                            echo $lead['status'] === 'new' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 
                                ($lead['status'] === 'contacted' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 
                                ($lead['status'] === 'quoted' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400' : 
                                ($lead['status'] === 'closed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 
                                'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'))); 
                        ?>">
                            <?php echo $statusLabels[$lead['status']] ?? $lead['status']; ?>
                        </span>
                    </p>
                </div>
            </div>
            
            <!-- Durum Değiştirme -->
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Durum Değiştir</label>
                <select id="statusSelect" class="w-full md:w-auto px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <?php foreach ($statusLabels as $value => $label): ?>
                        <option value="<?php echo $value; ?>" <?php echo $lead['status'] === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
                <button onclick="updateStatus()" class="mt-2 md:mt-0 md:ml-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                    Güncelle
                </button>
            </div>
        </div>
        
        <!-- Emlak Bilgileri -->
        <?php if (!empty($lead['property_type']) || !empty($lead['post_id']) || !empty($lead['location']) || !empty($lead['budget']) || !empty($lead['room_count'])): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Emlak Bilgileri</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if (!empty($selectedPost)): ?>
                    <div class="md:col-span-2">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Seçilen İlan</label>
                        <div class="mt-2 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <a href="<?php echo admin_url('posts/edit/' . $selectedPost['id']); ?>" target="_blank" class="font-medium text-primary hover:underline">
                                <?php echo esc_html($selectedPost['title']); ?>
                            </a>
                            <?php if (!empty($selectedPost['excerpt'])): ?>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1"><?php echo esc_html($selectedPost['excerpt']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif (!empty($lead['property_type'])): ?>
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Emlak Tipi</label>
                        <p class="text-gray-900 dark:text-white mt-1"><?php echo esc_html($lead['property_type']); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($lead['location'])): ?>
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Lokasyon</label>
                        <p class="text-gray-900 dark:text-white mt-1"><?php echo esc_html($lead['location']); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($lead['budget'])): ?>
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Bütçe</label>
                        <p class="text-gray-900 dark:text-white mt-1"><?php echo esc_html($lead['budget']); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($lead['room_count'])): ?>
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Oda Sayısı</label>
                        <p class="text-gray-900 dark:text-white mt-1"><?php echo esc_html($lead['room_count']); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Notlar -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Notlar</h2>
            </div>
            
            <!-- Not Ekleme Formu -->
            <form id="noteForm" class="mb-4">
                <textarea name="note" rows="3" placeholder="Not ekle..." class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" required></textarea>
                <input type="hidden" name="lead_id" value="<?php echo $lead['id']; ?>">
                <button type="submit" class="mt-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                    Not Ekle
                </button>
            </form>
            
            <!-- Notlar Listesi -->
            <div class="space-y-3" id="notesList">
                <?php if (empty($notes)): ?>
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Henüz not yok</p>
                <?php else: ?>
                    <?php foreach ($notes as $note): ?>
                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <p class="text-sm text-gray-900 dark:text-white"><?php echo nl2br(esc_html($note['note'])); ?></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                <?php echo turkish_date($note['created_at'], 'short'); ?>
                                <?php if (!empty($note['user_name'])): ?>
                                    - <?php echo esc_html($note['user_name']); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Görevler -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Görevler</h2>
            </div>
            
            <!-- Görev Ekleme Formu -->
            <form id="taskForm" class="mb-4 space-y-3">
                <input type="text" name="title" placeholder="Görev başlığı" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" required>
                <textarea name="description" rows="2" placeholder="Açıklama" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <input type="datetime-local" name="due_date" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                <input type="hidden" name="lead_id" value="<?php echo $lead['id']; ?>">
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                    Görev Ekle
                </button>
            </form>
            
            <!-- Görevler Listesi -->
            <div class="space-y-3" id="tasksList">
                <?php if (empty($tasks)): ?>
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Henüz görev yok</p>
                <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border-l-4 <?php echo $task['status'] === 'completed' ? 'border-green-500' : 'border-yellow-500'; ?>">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 dark:text-white <?php echo $task['status'] === 'completed' ? 'line-through' : ''; ?>"><?php echo esc_html($task['title']); ?></p>
                                    <?php if (!empty($task['description'])): ?>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1"><?php echo nl2br(esc_html($task['description'])); ?></p>
                                    <?php endif; ?>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                        <?php if (!empty($task['due_date'])): ?>
                                            Son Tarih: <?php echo turkish_date($task['due_date'], 'short'); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full <?php echo $task['status'] === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'; ?>">
                                    <?php echo $task['status'] === 'completed' ? 'Tamamlandı' : 'Beklemede'; ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Sağ Sütun - Hızlı İşlemler -->
    <div class="space-y-6">
        <!-- Hızlı Bilgiler -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Hızlı Bilgiler</h2>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Kaynak</label>
                    <p class="text-gray-900 dark:text-white mt-1"><?php echo $sourceLabels[$lead['source']] ?? $lead['source']; ?></p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Oluşturulma</label>
                    <p class="text-gray-900 dark:text-white mt-1"><?php echo turkish_date($lead['created_at'], 'full'); ?></p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Son Güncelleme</label>
                    <p class="text-gray-900 dark:text-white mt-1"><?php echo turkish_date($lead['updated_at'] ?? $lead['created_at'], 'full'); ?></p>
                </div>
            </div>
        </div>
        
        <!-- E-posta Gönder -->
        <?php if (!empty($lead['email'])): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">E-posta Gönder</h2>
            <form id="emailForm" class="space-y-3">
                <input type="text" name="subject" placeholder="Konu" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" required>
                <textarea name="body" rows="5" placeholder="Mesaj" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" required></textarea>
                <input type="hidden" name="lead_id" value="<?php echo $lead['id']; ?>">
                <button type="submit" class="w-full px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                    Gönder
                </button>
            </form>
        </div>
        <?php endif; ?>
        
        <!-- Dönüşüm Kaydet -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Dönüşüm Kaydet</h2>
            <form id="conversionForm" class="space-y-3">
                <select name="type" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" required>
                    <option value="sale">Satış</option>
                    <option value="rental">Kiralama</option>
                </select>
                <input type="number" name="value" placeholder="Değer (TL)" step="0.01" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" required>
                <textarea name="notes" rows="3" placeholder="Notlar" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                <input type="hidden" name="lead_id" value="<?php echo $lead['id']; ?>">
                <button type="submit" class="w-full px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                    Kaydet
                </button>
            </form>
        </div>
        
        <!-- Dönüşümler -->
        <?php if (!empty($conversions)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Dönüşümler</h2>
            <div class="space-y-3">
                <?php foreach ($conversions as $conversion): ?>
                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-gray-900 dark:text-white">
                                <?php echo $conversion['type'] === 'sale' ? 'Satış' : 'Kiralama'; ?>
                            </span>
                            <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                                <?php echo number_format($conversion['value'], 2, ',', '.'); ?> ₺
                            </span>
                        </div>
                        <?php if (!empty($conversion['notes'])): ?>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1"><?php echo esc_html($conversion['notes']); ?></p>
                        <?php endif; ?>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            <?php echo turkish_date($conversion['created_at'], 'short'); ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function updateStatus() {
    const status = document.getElementById('statusSelect').value;
    const formData = new FormData();
    formData.append('id', <?php echo $lead['id']; ?>);
    formData.append('status', status);
    
    fetch('<?php echo admin_url('module/crm/lead_update_status'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Hata: ' + data.message);
        }
    });
}

document.getElementById('noteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('<?php echo admin_url('module/crm/note_add'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Hata: ' + data.message);
        }
    });
});

document.getElementById('taskForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('<?php echo admin_url('module/crm/task_add'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Hata: ' + data.message);
        }
    });
});

document.getElementById('emailForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('<?php echo admin_url('module/crm/send_email'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('E-posta gönderildi');
            this.reset();
        } else {
            alert('Hata: ' + data.message);
        }
    });
});

document.getElementById('conversionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('<?php echo admin_url('module/crm/conversion_add'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Dönüşüm kaydedildi');
            location.reload();
        } else {
            alert('Hata: ' + data.message);
        }
    });
});
</script>
