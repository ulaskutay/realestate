<?php include __DIR__ . '/../snippets/header.php'; ?>
<!-- Media Picker -->
<script src="<?php echo rtrim(site_url(), '/') . '/admin/js/media-picker.js'; ?>"></script>
<!-- Quill Editor (No API Key Required) -->
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<style>
.ql-editor { min-height: 400px; font-size: 14px; line-height: 1.6; }
.ql-toolbar.ql-snow { border-radius: 8px 8px 0 0; border-color: #e5e7eb; background: #f9fafb; }
.ql-container.ql-snow { border-radius: 0 0 8px 8px; border-color: #e5e7eb; }
.dark .ql-toolbar.ql-snow { background: #1f2937; border-color: #374151; }
.dark .ql-container.ql-snow { background: #111827; border-color: #374151; }
.dark .ql-editor { color: #f3f4f6; }
.dark .ql-snow .ql-stroke { stroke: #9ca3af; }
.dark .ql-snow .ql-fill { fill: #9ca3af; }
.dark .ql-snow .ql-picker { color: #9ca3af; }
.dark .ql-snow .ql-picker-options { background: #1f2937; }

/* Mobil için Quill Editor iyileştirmeleri */
@media (max-width: 768px) {
    .ql-editor { 
        min-height: 300px; 
        font-size: 16px; /* iOS zoom önleme */
    }
    
    .ql-toolbar.ql-snow {
        padding: 8px;
        flex-wrap: wrap;
    }
    
    .ql-toolbar .ql-formats {
        margin: 2px;
    }
    
    .ql-toolbar button,
    .ql-toolbar .ql-picker {
        width: 32px;
        height: 32px;
        padding: 4px;
    }
    
    .ql-toolbar .ql-picker-label {
        padding: 4px 8px;
    }
    
    .ql-toolbar .ql-picker-options {
        padding: 4px;
    }
    
    .ql-toolbar .ql-picker-item {
        padding: 4px 8px;
    }
    
    /* Quill editor container mobilde tam genişlik */
    .quill-editor-mobile .ql-container {
        width: 100%;
    }
}

@media (max-width: 640px) {
    .ql-editor { 
        min-height: 250px;
        padding: 12px;
    }
    
    .ql-toolbar.ql-snow {
        padding: 6px;
    }
    
    .ql-toolbar button,
    .ql-toolbar .ql-picker {
        width: 28px;
        height: 28px;
    }
}
</style>
<?php
// Controller'dan gelen değişkenler: $user, $post, $categories, $versions, $message, $messageType
$categories = $categories ?? [];
$versions = $versions ?? [];
?>
<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php 
        $currentPage = 'posts';
        include __DIR__ . '/../snippets/sidebar.php'; 
        ?>

        <!-- Main Content -->
        <main class="main-content-with-sidebar flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b]">
            <div class="layout-content-container flex flex-col w-full mx-auto max-w-5xl">
                
                <!-- Header -->
                <header class="flex flex-col gap-4 mb-6">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <a href="<?php echo admin_url('posts'); ?>" class="text-gray-500 hover:text-primary transition-colors flex-shrink-0">
                                <span class="material-symbols-outlined text-xl">arrow_back</span>
                            </a>
                            <h1 class="text-gray-900 dark:text-white text-2xl sm:text-3xl font-bold tracking-tight">Yazı Düzenle</h1>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm sm:text-base line-clamp-2">
                            <?php echo esc_html($post['title']); ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 ml-2">
                                v<?php echo esc_html($post['version'] ?? 1); ?>
                            </span>
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <?php if ($post['status'] === 'published'): ?>
                        <a href="/blog/<?php echo esc_attr($post['slug']); ?>" target="_blank" class="flex items-center gap-2 px-3 sm:px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm">
                            <span class="material-symbols-outlined text-lg sm:text-xl">open_in_new</span>
                            <span class="font-medium hidden sm:inline">Görüntüle</span>
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo admin_url('posts/versions/' . $post['id']); ?>" class="flex items-center gap-2 px-3 sm:px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm">
                            <span class="material-symbols-outlined text-lg sm:text-xl">history</span>
                            <span class="font-medium hidden sm:inline">Geçmiş</span>
                        </a>
                    </div>
                </header>

                <!-- Mesaj -->
                <?php if (!empty($message)): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 text-red-800 dark:text-red-200'; ?>">
                    <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
                </div>
                <?php endif; ?>

                <!-- Form -->
                <form action="<?php echo admin_url('posts/update/' . $post['id']); ?>" method="POST" class="space-y-6">
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
                        <!-- Sol Kolon - Ana İçerik -->
                        <div class="lg:col-span-2 space-y-4 sm:space-y-6 order-2 lg:order-1">
                            
                            <!-- Başlık -->
                            <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 p-4 sm:p-6">
                                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Başlık *</label>
                                <input type="text" id="title" name="title" required
                                    value="<?php echo esc_attr($post['title']); ?>"
                                    class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors text-base sm:text-lg"
                                    placeholder="Yazı başlığını girin...">
                            </div>
                            
                            <!-- Özet -->
                            <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 p-4 sm:p-6">
                                <label for="excerpt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Özet</label>
                                <textarea id="excerpt" name="excerpt" rows="3"
                                    class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors resize-none"
                                    placeholder="Yazının kısa özeti..."><?php echo esc_html($post['excerpt']); ?></textarea>
                                <p class="text-gray-500 text-xs mt-1">Arama sonuçlarında ve listelerde gösterilecek kısa açıklama.</p>
                            </div>
                            
                            <!-- İçerik - Quill Editor -->
                            <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 p-4 sm:p-6">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">İçerik</label>
                                    <div class="flex items-center gap-2">
                                        <button type="button" onclick="toggleEditorMode()" id="editor-mode-btn" class="text-xs px-2 sm:px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors whitespace-nowrap">
                                            <span class="material-symbols-outlined text-sm align-middle mr-1">code</span>
                                            <span class="hidden sm:inline">HTML Modu</span>
                                            <span class="sm:hidden">HTML</span>
                                        </button>
                                    </div>
                                </div>
                                <div id="quill-editor" class="quill-editor-mobile"><?php echo $post['content']; ?></div>
                                <textarea id="content" name="content" class="hidden"><?php echo esc_html($post['content']); ?></textarea>
                            </div>
                            
                            <!-- SEO Ayarları -->
                            <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 p-4 sm:p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary">search</span>
                                    SEO Ayarları
                                </h3>
                                
                                <div class="space-y-4">
                                    <!-- URL (Slug) -->
                                    <div>
                                        <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">URL (Slug)</label>
                                        <input type="text" id="slug" name="slug"
                                            value="<?php echo esc_attr($post['slug']); ?>"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                            placeholder="yazi-url-adresi">
                                        <p class="text-gray-500 text-xs mt-1">Sayfa URL'si: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">/blog/<span id="slug-preview"><?php echo esc_html($post['slug']); ?></span></code></p>
                                    </div>
                                    
                                    <!-- Meta Başlık -->
                                    <div>
                                        <label for="meta_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Meta Başlık</label>
                                        <input type="text" id="meta_title" name="meta_title"
                                            value="<?php echo esc_attr($post['meta_title']); ?>"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                            placeholder="Arama motorlarında görünecek başlık">
                                    </div>
                                    
                                    <!-- Meta Açıklama -->
                                    <div>
                                        <label for="meta_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Meta Açıklama</label>
                                        <textarea id="meta_description" name="meta_description" rows="2"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors resize-none"
                                            placeholder="Arama motorlarında görünecek açıklama (max 160 karakter)"><?php echo esc_html($post['meta_description']); ?></textarea>
                                    </div>
                                    
                                    <!-- Anahtar Kelimeler -->
                                    <div>
                                        <label for="meta_keywords" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Anahtar Kelimeler</label>
                                        <input type="text" id="meta_keywords" name="meta_keywords"
                                            value="<?php echo esc_attr($post['meta_keywords']); ?>"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                            placeholder="kelime1, kelime2, kelime3">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sağ Kolon - Yan Panel -->
                        <div class="space-y-4 sm:space-y-6 order-1 lg:order-2">
                            
                            <!-- Yayın Durumu -->
                            <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 p-4 sm:p-6">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4">Yayın</h3>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Durum</label>
                                        <select id="status" name="status"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                            <option value="draft" <?php echo $post['status'] === 'draft' ? 'selected' : ''; ?>>Taslak</option>
                                            <option value="published" <?php echo $post['status'] === 'published' ? 'selected' : ''; ?>>Yayında</option>
                                            <option value="scheduled" <?php echo $post['status'] === 'scheduled' ? 'selected' : ''; ?>>Zamanlanmış</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label for="visibility" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Görünürlük</label>
                                        <select id="visibility" name="visibility"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                            <option value="public" <?php echo $post['visibility'] === 'public' ? 'selected' : ''; ?>>Herkese Açık</option>
                                            <option value="private" <?php echo $post['visibility'] === 'private' ? 'selected' : ''; ?>>Gizli</option>
                                            <option value="password" <?php echo $post['visibility'] === 'password' ? 'selected' : ''; ?>>Şifreli</option>
                                        </select>
                                    </div>
                                    
                                    <div id="password-field" class="<?php echo $post['visibility'] !== 'password' ? 'hidden' : ''; ?>">
                                        <label for="post_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Şifre</label>
                                        <input type="text" id="post_password" name="post_password"
                                            value="<?php echo esc_attr($post['password']); ?>"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                            placeholder="Yazı şifresi">
                                    </div>
                                    
                                    <div>
                                        <label for="published_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Yayın Tarihi</label>
                                        <input type="datetime-local" id="published_at" name="published_at"
                                            value="<?php echo $post['published_at'] ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : ''; ?>"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" id="allow_comments" name="allow_comments" 
                                            <?php echo $post['allow_comments'] ? 'checked' : ''; ?>
                                            class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                        <label for="allow_comments" class="text-sm text-gray-700 dark:text-gray-300">Yorumlara izin ver</label>
                                    </div>
                                    
                                    <!-- İstatistikler -->
                                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-500">Görüntülenme:</span>
                                            <span class="text-gray-900 dark:text-white font-medium"><?php echo number_format($post['views']); ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-6 flex flex-col sm:flex-row gap-2">
                                    <button type="submit" class="flex-1 px-4 py-2.5 sm:py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium min-h-[44px]">
                                        Güncelle
                                    </button>
                                    <a href="<?php echo admin_url('posts/delete/' . $post['id']); ?>" 
                                       onclick="return confirm('Bu yazıyı çöpe taşımak istediğinize emin misiniz?');"
                                       class="px-4 py-2.5 sm:py-2 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center justify-center min-h-[44px]"
                                       title="Çöpe Taşı">
                                        <span class="material-symbols-outlined text-lg sm:text-xl">delete</span>
                                        <span class="ml-2 sm:hidden text-sm">Çöpe Taşı</span>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Kategori -->
                            <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 p-4 sm:p-6">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4">Kategori</h3>
                                
                                <select id="category_id" name="category_id"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Kategori Seçin</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $post['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo esc_html($cat['display_name'] ?? $cat['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                
                                <a href="<?php echo admin_url('posts/category/create'); ?>" class="inline-flex items-center gap-1 text-primary text-sm mt-2 hover:underline">
                                    <span class="material-symbols-outlined text-sm">add</span>
                                    Yeni Kategori Ekle
                                </a>
                            </div>
                            
                            <!-- Öne Çıkan Görsel -->
                            <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 p-4 sm:p-6">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4">Öne Çıkan Görsel</h3>
                                
                                <div id="featured-image-preview" class="mb-4 <?php echo empty($post['featured_image']) ? 'hidden' : ''; ?>">
                                    <img src="<?php echo esc_url($post['featured_image']); ?>" alt="Öne çıkan görsel" class="w-full h-40 object-cover rounded-lg">
                                </div>
                                
                                <input type="hidden" id="featured_image" name="featured_image" value="<?php echo esc_attr($post['featured_image']); ?>">
                                
                                <div class="space-y-2">
                                    <input type="url" id="featured_image_url" 
                                        value="<?php echo esc_attr($post['featured_image']); ?>"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors text-sm"
                                        placeholder="Görsel URL'si girin veya seçin...">
                                    
                                    <button type="button" onclick="openMediaPicker()" class="w-full px-4 py-2 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm">
                                        <span class="material-symbols-outlined text-sm align-middle mr-1">image</span>
                                        Medya Kütüphanesinden Seç
                                    </button>
                                    
                                    <button type="button" onclick="clearFeaturedImage()" class="w-full px-4 py-2 border border-red-200 text-red-600 rounded-lg hover:bg-red-50 transition-colors text-sm <?php echo empty($post['featured_image']) ? 'hidden' : ''; ?>" id="clear-image-btn">
                                        <span class="material-symbols-outlined text-sm align-middle mr-1">delete</span>
                                        Görseli Kaldır
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Versiyon Bilgisi -->
                            <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 p-4 sm:p-6">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary text-lg sm:text-xl">history</span>
                                    Versiyon Bilgisi
                                </h3>
                                
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Mevcut Versiyon</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">v<?php echo esc_html($post['version'] ?? 1); ?></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Kayıtlı Geçmiş</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white"><?php echo count($versions); ?> versiyon</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Son Güncelleme</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white"><?php echo date('d.m.Y', strtotime($post['updated_at'])); ?></span>
                                    </div>
                                </div>
                                
                                <?php if (count($versions) > 0): ?>
                                <a href="<?php echo admin_url('posts/versions/' . $post['id']); ?>" class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-2 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm">
                                    <span class="material-symbols-outlined text-sm">history</span>
                                    Geçmişi Görüntüle
                                </a>
                                <?php endif; ?>
                            </div>
                            
                        </div>
                    </div>
                    
                </form>

            </div>
        </main>
    </div>
</div>

<script>
// Quill Editor Initialization
let quill = null;
let isHtmlMode = false;

quill = new Quill('#quill-editor', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'indent': '-1'}, { 'indent': '+1' }],
            [{ 'align': [] }],
            ['link', 'image', 'video'],
            ['blockquote', 'code-block'],
            ['clean']
        ]
    },
    placeholder: 'İçeriği buraya yazın...'
});

// Form gönderilmeden önce içeriği textarea'ya aktar
document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('content').value = quill.root.innerHTML;
});

// Toggle between visual and HTML mode
function toggleEditorMode() {
    const btn = document.getElementById('editor-mode-btn');
    const editorDiv = document.getElementById('quill-editor');
    const textarea = document.getElementById('content');
    
    if (!isHtmlMode) {
        // Switch to HTML mode
        textarea.value = quill.root.innerHTML;
        editorDiv.style.display = 'none';
        document.querySelector('.ql-toolbar').style.display = 'none';
        textarea.classList.remove('hidden');
        textarea.style.width = '100%';
        textarea.style.height = window.innerWidth <= 768 ? '300px' : '450px';
        textarea.style.fontFamily = 'monospace';
        textarea.style.fontSize = '13px';
        textarea.style.padding = '12px';
        textarea.style.border = '1px solid #e5e7eb';
        textarea.style.borderRadius = '8px';
        textarea.style.backgroundColor = document.documentElement.classList.contains('dark') ? '#111827' : '#fff';
        textarea.style.color = document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#111';
        btn.innerHTML = '<span class="material-symbols-outlined text-sm align-middle mr-1">edit</span> Görsel Modu';
        isHtmlMode = true;
    } else {
        // Switch to visual mode
        quill.root.innerHTML = textarea.value;
        textarea.classList.add('hidden');
        editorDiv.style.display = 'block';
        document.querySelector('.ql-toolbar').style.display = 'block';
        btn.innerHTML = '<span class="material-symbols-outlined text-sm align-middle mr-1">code</span> HTML Modu';
        isHtmlMode = false;
    }
}

// Slug preview update
document.getElementById('slug').addEventListener('input', function() {
    document.getElementById('slug-preview').textContent = this.value || 'slug';
});

// Görünürlük değiştiğinde şifre alanını göster/gizle
document.getElementById('visibility').addEventListener('change', function() {
    const passwordField = document.getElementById('password-field');
    if (this.value === 'password') {
        passwordField.classList.remove('hidden');
    } else {
        passwordField.classList.add('hidden');
    }
});

// Öne çıkan görsel URL'si değiştiğinde önizlemeyi güncelle
document.getElementById('featured_image_url').addEventListener('input', function() {
    updateFeaturedImagePreview(this.value);
    document.getElementById('featured_image').value = this.value;
});

function updateFeaturedImagePreview(url) {
    const preview = document.getElementById('featured-image-preview');
    const clearBtn = document.getElementById('clear-image-btn');
    
    if (url) {
        preview.querySelector('img').src = url;
        preview.classList.remove('hidden');
        clearBtn.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
        clearBtn.classList.add('hidden');
    }
}

function clearFeaturedImage() {
    document.getElementById('featured_image').value = '';
    document.getElementById('featured_image_url').value = '';
    updateFeaturedImagePreview('');
}

function openMediaPicker() {
    // Medya seçici modalı aç
    if (typeof MediaPicker !== 'undefined') {
        if (!window.mediaPicker) {
            window.mediaPicker = new MediaPicker();
        }
        window.mediaPicker.open({
            type: 'image',
            multiple: false,
            onSelect: function(media) {
                const url = media.file_url || media.url;
                document.getElementById('featured_image').value = url;
                document.getElementById('featured_image_url').value = url;
                updateFeaturedImagePreview(url);
            }
        });
    } else {
        alert('Medya kütüphanesi yüklenemedi. Lütfen URL girin.');
    }
}
</script>

</body>
</html>
