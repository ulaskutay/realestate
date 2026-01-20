<?php
// Lead form view (create/edit)
$lead = $lead ?? null;
$action = $action ?? 'lead_store';
$isEdit = $lead !== null;
?>

<!-- Header -->
<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div class="flex flex-col gap-2">
        <div class="flex items-center gap-3">
            <a href="<?php echo admin_url('module/crm'); ?>" class="text-gray-500 dark:text-gray-400 hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">
                <?php echo $isEdit ? 'Lead Düzenle' : 'Yeni Lead'; ?>
            </h1>
        </div>
        <p class="text-gray-500 dark:text-gray-400 text-base">
            <?php echo $isEdit ? 'Lead bilgilerini güncelleyin' : 'Yeni lead oluşturun'; ?>
        </p>
    </div>
</header>

<!-- Form -->
<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <form method="POST" action="<?php echo admin_url('module/crm/' . $action); ?>" class="space-y-6">
        <!-- Temel Bilgiler -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Temel Bilgiler</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">İsim *</label>
                    <input type="text" name="name" value="<?php echo esc_attr($lead['name'] ?? ''); ?>" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telefon</label>
                    <input type="text" name="phone" value="<?php echo esc_attr($lead['phone'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">E-posta</label>
                    <input type="email" name="email" value="<?php echo esc_attr($lead['email'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Durum</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="new" <?php echo ($lead['status'] ?? 'new') === 'new' ? 'selected' : ''; ?>>Yeni</option>
                        <option value="contacted" <?php echo ($lead['status'] ?? '') === 'contacted' ? 'selected' : ''; ?>>İletişimde</option>
                        <option value="quoted" <?php echo ($lead['status'] ?? '') === 'quoted' ? 'selected' : ''; ?>>Teklif Verildi</option>
                        <option value="closed" <?php echo ($lead['status'] ?? '') === 'closed' ? 'selected' : ''; ?>>Kapandı</option>
                        <option value="cancelled" <?php echo ($lead['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>İptal</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Emlak Bilgileri -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Emlak Bilgileri</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">İlan Seç</label>
                    <select name="post_id" id="post_select" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">İlan seçin veya manuel girin</option>
                        <?php if (!empty($posts)): ?>
                            <?php foreach ($posts as $post): ?>
                                <option value="<?php echo $post['id']; ?>" <?php echo (isset($lead['post_id']) && $lead['post_id'] == $post['id']) ? 'selected' : ''; ?>>
                                    <?php echo esc_html($post['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Emlak Tipi (Manuel)</label>
                    <input type="text" name="property_type" id="property_type" value="<?php echo esc_attr($lead['property_type'] ?? ''); ?>" placeholder="Örn: Satılık Daire, Kiralık Villa" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">İlan seçilirse bu alan otomatik doldurulur</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lokasyon</label>
                    <input type="text" name="location" value="<?php echo esc_attr($lead['location'] ?? ''); ?>" placeholder="İlçe, Mahalle" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bütçe</label>
                    <input type="text" name="budget" value="<?php echo esc_attr($lead['budget'] ?? ''); ?>" placeholder="Örn: 500.000 - 1.000.000 TL" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Oda Sayısı</label>
                    <input type="text" name="room_count" value="<?php echo esc_attr($lead['room_count'] ?? ''); ?>" placeholder="Örn: 2+1, 3+1" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
            </div>
        </div>
        
        <!-- Diğer Bilgiler -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Diğer Bilgiler</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kaynak</label>
                    <select name="source" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="manual" <?php echo ($lead['source'] ?? 'manual') === 'manual' ? 'selected' : ''; ?>>Manuel</option>
                        <option value="meta" <?php echo ($lead['source'] ?? '') === 'meta' ? 'selected' : ''; ?>>Meta/Facebook</option>
                        <option value="form" <?php echo ($lead['source'] ?? '') === 'form' ? 'selected' : ''; ?>>Form</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notlar</label>
                <textarea name="notes" rows="4" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"><?php echo esc_textarea($lead['notes'] ?? ''); ?></textarea>
            </div>
        </div>
        
        <!-- Butonlar -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="<?php echo admin_url('module/crm'); ?>" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                İptal
            </a>
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                <?php echo $isEdit ? 'Güncelle' : 'Oluştur'; ?>
            </button>
        </div>
    </form>
</div>

<script>
// İlan seçildiğinde emlak tipi alanını otomatik doldur
document.addEventListener('DOMContentLoaded', function() {
    const postSelect = document.getElementById('post_select');
    const propertyTypeInput = document.getElementById('property_type');
    
    if (postSelect && propertyTypeInput) {
        postSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (this.value && selectedOption.text && selectedOption.text !== 'İlan seçin veya manuel girin') {
                propertyTypeInput.value = selectedOption.text;
            }
        });
    }
});
</script>
