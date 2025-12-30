<?php include __DIR__ . '/../snippets/header.php'; ?>
<?php
// Controller'dan gelen değişkenler: $user, $category (edit için), $categories, $message, $messageType
$isEdit = isset($category) && !empty($category);
$categories = $categories ?? [];
?>
<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php 
        $currentPage = 'posts';
        include __DIR__ . '/../snippets/sidebar.php'; 
        ?>

        <!-- Main Content -->
        <main class="main-content-with-sidebar flex-1 p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b]">
            <div class="layout-content-container flex flex-col w-full mx-auto max-w-2xl">
                
                <!-- Header -->
                <header class="flex flex-col gap-4 mb-6">
                    <div class="flex items-center gap-2">
                        <a href="<?php echo admin_url('posts?tab=categories'); ?>" class="text-gray-500 hover:text-primary transition-colors">
                            <span class="material-symbols-outlined text-xl">arrow_back</span>
                        </a>
                        <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">
                            <?php echo $isEdit ? 'Kategori Düzenle' : 'Yeni Kategori'; ?>
                        </h1>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 text-base">
                        <?php echo $isEdit ? 'Mevcut kategoriyi düzenleyin.' : 'Yeni bir yazı kategorisi oluşturun.'; ?>
                    </p>
                </header>

                <!-- Mesaj -->
                <?php if (!empty($message)): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 text-red-800 dark:text-red-200'; ?>">
                    <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
                </div>
                <?php endif; ?>

                <!-- Form -->
                <form action="<?php echo $isEdit ? admin_url('posts/category/update/' . $category['id']) : admin_url('posts/category/store'); ?>" method="POST" class="space-y-6">
                    
                    <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 p-6 space-y-6">
                        
                        <!-- Kategori Adı -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Kategori Adı *</label>
                            <input type="text" id="name" name="name" required
                                value="<?php echo $isEdit ? esc_attr($category['name']) : ''; ?>"
                                class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                placeholder="Kategori adını girin...">
                        </div>
                        
                        <!-- Slug -->
                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">URL (Slug)</label>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-500 text-sm">/kategori/</span>
                                <input type="text" id="slug" name="slug"
                                    value="<?php echo $isEdit ? esc_attr($category['slug']) : ''; ?>"
                                    class="flex-1 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                    placeholder="otomatik-olusturulur">
                            </div>
                            <p class="text-gray-500 text-xs mt-1">Boş bırakırsanız isimden otomatik oluşturulur.</p>
                        </div>
                        
                        <!-- Açıklama -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Açıklama</label>
                            <textarea id="description" name="description" rows="3"
                                class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors resize-none"
                                placeholder="Kategori açıklaması..."><?php echo $isEdit ? esc_html($category['description']) : ''; ?></textarea>
                        </div>
                        
                        <!-- Üst Kategori -->
                        <div>
                            <label for="parent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Üst Kategori</label>
                            <select id="parent_id" name="parent_id"
                                class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                <option value="">Üst Kategori Yok</option>
                                <?php foreach ($categories as $cat): ?>
                                    <?php if (!$isEdit || $cat['id'] != $category['id']): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($isEdit && $category['parent_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo esc_html($cat['display_name'] ?? $cat['name']); ?>
                                    </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Durum -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Durum</label>
                            <select id="status" name="status"
                                class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                <option value="active" <?php echo ($isEdit && $category['status'] === 'active') || !$isEdit ? 'selected' : ''; ?>>Aktif</option>
                                <option value="inactive" <?php echo ($isEdit && $category['status'] === 'inactive') ? 'selected' : ''; ?>>Pasif</option>
                            </select>
                        </div>
                        
                    </div>
                    
                    <!-- Butonlar -->
                    <div class="flex items-center gap-4">
                        <button type="submit" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium">
                            <?php echo $isEdit ? 'Güncelle' : 'Kategori Oluştur'; ?>
                        </button>
                        <a href="<?php echo admin_url('posts?tab=categories'); ?>" class="px-6 py-3 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors font-medium">
                            İptal
                        </a>
                    </div>
                    
                </form>

            </div>
        </main>
    </div>
</div>

<script>
// İsimden otomatik slug oluştur
document.getElementById('name').addEventListener('input', function() {
    const slugField = document.getElementById('slug');
    if (!slugField.value || slugField.dataset.autoGenerated === 'true') {
        // Basit slug oluşturma
        let slug = this.value.toLowerCase();
        // Türkçe karakterleri dönüştür
        const tr = {'ş':'s', 'ğ':'g', 'ü':'u', 'ı':'i', 'ö':'o', 'ç':'c', 'Ş':'s', 'Ğ':'g', 'Ü':'u', 'İ':'i', 'Ö':'o', 'Ç':'c'};
        for (let key in tr) {
            slug = slug.replace(new RegExp(key, 'g'), tr[key]);
        }
        slug = slug.replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        slugField.value = slug;
        slugField.dataset.autoGenerated = 'true';
    }
});

// Slug manuel değiştirildiğinde auto-generated bayrağını kaldır
document.getElementById('slug').addEventListener('input', function() {
    this.dataset.autoGenerated = 'false';
});
</script>

</body>
</html>

