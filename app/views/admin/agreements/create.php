<?php include __DIR__ . '/../snippets/header.php'; ?>
<?php
// Controller'dan gelen değişkenler: $user, $types
$types = $types ?? [];
?>
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

<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php 
        $currentPage = 'agreements';
        include __DIR__ . '/../snippets/sidebar.php'; 
        ?>

        <!-- Main Content -->
        <main class="main-content-with-sidebar flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b]">
            <div class="layout-content-container flex flex-col w-full mx-auto max-w-5xl">
                
                <!-- Header -->
                <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <a href="<?php echo admin_url('agreements'); ?>" class="text-gray-500 hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-xl">arrow_back</span>
                            </a>
                            <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Yeni Sözleşme</h1>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-base">Yeni bir yasal metin veya sözleşme oluşturun.</p>
                    </div>
                </header>

                <!-- Form -->
                <form action="<?php echo admin_url('agreements/store'); ?>" method="POST" class="space-y-6">
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
                        <!-- Sol Kolon - Ana İçerik -->
                        <div class="lg:col-span-2 space-y-4 sm:space-y-6 order-2 lg:order-1">
                            
                            <!-- Başlık -->
                            <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 p-4 sm:p-6">
                                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Başlık *</label>
                                <input type="text" id="title" name="title" required
                                    class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors text-base sm:text-lg"
                                    placeholder="Örn: Gizlilik Politikası">
                            </div>
                            
                            <!-- İçerik - TinyMCE Editor -->
                            <!-- İçerik - Quill Editor -->
                            <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 p-4 sm:p-6">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sözleşme İçeriği</label>
                                    <div class="flex items-center gap-2">
                                        <button type="button" onclick="toggleEditorMode()" id="editor-mode-btn" class="text-xs px-2 sm:px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors whitespace-nowrap">
                                            <span class="material-symbols-outlined text-sm align-middle mr-1">code</span>
                                            <span class="hidden sm:inline">HTML Modu</span>
                                            <span class="sm:hidden">HTML</span>
                                        </button>
                                    </div>
                                </div>
                                <div id="quill-editor" class="quill-editor-mobile"></div>
                                <textarea id="content" name="content" class="hidden"></textarea>
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
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                            placeholder="gizlilik-politikasi">
                                        <p class="text-gray-500 text-xs mt-1">Sayfa URL'si: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">/sozlesmeler/<span id="slug-preview">slug</span></code> - Boş bırakırsanız başlıktan otomatik oluşturulur.</p>
                                    </div>
                                    
                                    <!-- Meta Başlık -->
                                    <div>
                                        <label for="meta_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Meta Başlık</label>
                                        <input type="text" id="meta_title" name="meta_title"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                            placeholder="Arama motorlarında görünecek başlık">
                                    </div>
                                    
                                    <!-- Meta Açıklama -->
                                    <div>
                                        <label for="meta_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Meta Açıklama</label>
                                        <textarea id="meta_description" name="meta_description" rows="2"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors resize-none"
                                            placeholder="Arama motorlarında görünecek açıklama (max 160 karakter)"></textarea>
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
                                            <option value="draft">Taslak</option>
                                            <option value="published">Yayında</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mt-6 flex gap-2">
                                    <button type="submit" class="flex-1 px-4 py-2.5 sm:py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium min-h-[44px]">
                                        Kaydet
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Sözleşme Türü -->
                            <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 p-4 sm:p-6">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4">Sözleşme Türü</h3>
                                
                                <select id="type" name="type"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <?php foreach ($types as $typeKey => $typeLabel): ?>
                                    <option value="<?php echo esc_html($typeKey); ?>"><?php echo esc_html($typeLabel); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                
                                <p class="text-gray-500 text-xs mt-2">Sözleşme türü, içeriğin kategorize edilmesine yardımcı olur.</p>
                            </div>
                            
                            <!-- Yer Tutucular Bilgi -->
                            <div class="rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4 sm:p-6">
                                <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-3 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">lightbulb</span>
                                    Yer Tutucular
                                </h3>
                                <p class="text-xs text-blue-700 dark:text-blue-400 mb-3">
                                    Bu yer tutucuları kullanabilirsiniz, site ayarlarından otomatik doldurulurlar:
                                </p>
                                <div class="space-y-1 text-xs">
                                    <code class="block bg-blue-100 dark:bg-blue-800/50 px-2 py-1 rounded text-blue-800 dark:text-blue-200">[ŞİRKET ADI]</code>
                                    <code class="block bg-blue-100 dark:bg-blue-800/50 px-2 py-1 rounded text-blue-800 dark:text-blue-200">[E-POSTA]</code>
                                    <code class="block bg-blue-100 dark:bg-blue-800/50 px-2 py-1 rounded text-blue-800 dark:text-blue-200">[ADRES]</code>
                                    <code class="block bg-blue-100 dark:bg-blue-800/50 px-2 py-1 rounded text-blue-800 dark:text-blue-200">[TELEFON]</code>
                                    <code class="block bg-blue-100 dark:bg-blue-800/50 px-2 py-1 rounded text-blue-800 dark:text-blue-200">[WEB SİTESİ]</code>
                                    <code class="block bg-blue-100 dark:bg-blue-800/50 px-2 py-1 rounded text-blue-800 dark:text-blue-200">[TARİH]</code>
                                </div>
                            </div>
                            
                            <!-- Versiyon Bilgi -->
                            <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-gray-800/50 p-4 sm:p-6">
                                <div class="flex items-start gap-3">
                                    <span class="material-symbols-outlined text-gray-400 mt-0.5">info</span>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Versiyon Takibi</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Bu sözleşmeyi güncellerken, önceki versiyonlar otomatik olarak kaydedilir. Gerekirse eski versiyonlara geri dönebilirsiniz.
                                        </p>
                                    </div>
                                </div>
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
            ['link'],
            ['blockquote', 'code-block'],
            ['clean']
        ]
    },
    placeholder: 'Sözleşme içeriğini buraya yazın...'
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

// Başlıktan otomatik slug oluştur
document.getElementById('title').addEventListener('input', function() {
    const slugField = document.getElementById('slug');
    if (!slugField.value) {
        // Basit slug oluşturma
        let slug = this.value.toLowerCase();
        // Türkçe karakterleri dönüştür
        const tr = {'ş':'s', 'ğ':'g', 'ü':'u', 'ı':'i', 'ö':'o', 'ç':'c', 'Ş':'s', 'Ğ':'g', 'Ü':'u', 'İ':'i', 'Ö':'o', 'Ç':'c'};
        for (let key in tr) {
            slug = slug.replace(new RegExp(key, 'g'), tr[key]);
        }
        slug = slug.replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        document.getElementById('slug-preview').textContent = slug || 'slug';
    }
});

// Slug preview update
document.getElementById('slug').addEventListener('input', function() {
    document.getElementById('slug-preview').textContent = this.value || 'slug';
});
</script>

</body>
</html>
