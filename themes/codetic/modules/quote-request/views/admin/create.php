<?php 
// Admin snippet'lerini mutlak yol ile yükle
$rootPath = $_SERVER['DOCUMENT_ROOT'];
include $rootPath . '/app/views/admin/snippets/header.php'; 
?>

<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php 
        $currentPage = 'quote-request';
        include $rootPath . '/app/views/admin/snippets/sidebar.php'; 
        ?>

        <!-- Content Area with Header -->
        <div class="flex-1 flex flex-col lg:ml-64">
            <!-- Top Header -->
            <?php include $rootPath . '/app/views/admin/snippets/top-header.php'; ?>

            <!-- Main Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b] overflow-y-auto">
            <div class="layout-content-container flex flex-col w-full mx-auto max-w-7xl">
                
                <!-- Header -->
                <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <a href="<?php echo admin_url('module/quote-request'); ?>" class="text-gray-500 dark:text-gray-400 hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-xl">arrow_back</span>
                            </a>
                            <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Yeni Teklif Al Sayfası</h1>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-base">Yeni bir teklif alma sayfası oluşturun.</p>
                    </div>
                </header>

                <!-- Form -->
                <form method="POST" action="<?php echo admin_url('module/quote-request/store'); ?>" class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Main Content -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Sayfa Bilgileri -->
                            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Sayfa Bilgileri</h2>
                                
                                <div class="space-y-5">
                                    <div>
                                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Başlık <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" 
                                               id="title" 
                                               name="title" 
                                               value="Teklif Al" 
                                               required
                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Sayfa başlığı</p>
                                    </div>

                                    <div>
                                        <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            URL Slug
                                        </label>
                                        <input type="text" 
                                               id="slug" 
                                               name="slug" 
                                               value="teklif-al"
                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">URL'de görünecek adres (örn: teklif-al)</p>
                                    </div>

                                    <div>
                                        <label for="excerpt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Kısa Açıklama
                                        </label>
                                        <textarea id="excerpt" 
                                                  name="excerpt" 
                                                  rows="3"
                                                  class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all resize-none">Projeniz için detaylı teklif alın</textarea>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Sayfa açıklaması</p>
                                    </div>

                                    <div>
                                        <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Sayfa İçeriği
                                        </label>
                                        <textarea id="content" 
                                                  name="content" 
                                                  rows="6"
                                                  class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all resize-none"></textarea>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Sayfa içeriği (opsiyonel)</p>
                                    </div>
                                </div>
                            </div>

                            <!-- SEO Ayarları -->
                            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">SEO Ayarları</h2>
                                
                                <div class="space-y-5">
                                    <div>
                                        <label for="meta_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Meta Başlık
                                        </label>
                                        <input type="text" 
                                               id="meta_title" 
                                               name="meta_title" 
                                               value="Teklif Al"
                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                    </div>

                                    <div>
                                        <label for="meta_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Meta Açıklama
                                        </label>
                                        <textarea id="meta_description" 
                                                  name="meta_description" 
                                                  rows="3"
                                                  class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all resize-none">Projeniz için detaylı teklif alın</textarea>
                                    </div>

                                    <div>
                                        <label for="meta_keywords" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Meta Anahtar Kelimeler
                                        </label>
                                        <input type="text" 
                                               id="meta_keywords" 
                                               name="meta_keywords" 
                                               value="teklif, fiyat, proje"
                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="space-y-6">
                            <!-- Yayın Ayarları -->
                            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Yayın Ayarları</h2>
                                
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Durum
                                    </label>
                                    <select id="status" 
                                            name="status"
                                            class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                        <option value="published" selected>Yayında</option>
                                        <option value="draft">Taslak</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Form Ayarları -->
                            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Form Ayarları</h2>
                                
                                <div class="space-y-5">
                                    <div>
                                        <label for="quote_form_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Form Seçin <span class="text-red-500">*</span>
                                        </label>
                                        <select id="quote_form_id" 
                                                name="quote_form_id"
                                                required
                                                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                            <option value="">Form Seçin...</option>
                                            <?php if (!empty($forms)): ?>
                                                <?php foreach ($forms as $form): ?>
                                                    <option value="<?php echo esc_attr($form['id']); ?>" data-slug="<?php echo esc_attr($form['slug']); ?>">
                                                        <?php echo esc_html($form['name']); ?> (<?php echo esc_html($form['slug']); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <option value="" disabled>Form bulunamadı. Lütfen önce bir form oluşturun.</option>
                                            <?php endif; ?>
                                        </select>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Teklif alma sayfasında kullanılacak formu seçin</p>
                                        
                                        <!-- Form Preview -->
                                        <div id="form-preview" class="mt-4 hidden p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Seçilen Form Bilgileri</h4>
                                            <div id="form-preview-content" class="text-sm text-gray-600 dark:text-gray-400">
                                                <!-- Form bilgileri buraya gelecek -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                <div class="flex flex-col gap-3">
                                    <button type="submit" 
                                            class="w-full px-4 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium flex items-center justify-center gap-2">
                                        <span class="material-symbols-outlined text-xl">save</span>
                                        <span>Kaydet</span>
                                    </button>
                                    <a href="<?php echo admin_url('module/quote-request'); ?>" 
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors font-medium flex items-center justify-center gap-2">
                                        <span class="material-symbols-outlined text-xl">close</span>
                                        <span>İptal</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            </main>
        </div>
    </div>
</div>

<script>
// Auto-generate slug from title
document.getElementById('title').addEventListener('input', function() {
    const title = this.value;
    const slug = title.toLowerCase()
        .replace(/ş/g, 's')
        .replace(/ğ/g, 'g')
        .replace(/ü/g, 'u')
        .replace(/ö/g, 'o')
        .replace(/ç/g, 'c')
        .replace(/ı/g, 'i')
        .replace(/İ/g, 'i')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
    
    document.getElementById('slug').value = slug || 'teklif-al';
});

// Form preview
const formSelect = document.getElementById('quote_form_id');
const formPreview = document.getElementById('form-preview');
const formPreviewContent = document.getElementById('form-preview-content');

if (formSelect && formPreview && formPreviewContent) {
    formSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const formId = this.value;
        const formSlug = selectedOption.getAttribute('data-slug');
        const formName = selectedOption.textContent.split(' (')[0];
        
        if (formId && formSlug) {
            // Form bilgilerini göster
            formPreviewContent.innerHTML = `
                <div class="space-y-2">
                    <p><strong>Form Adı:</strong> ${formName}</p>
                    <p><strong>Form Slug:</strong> <code class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded">${formSlug}</code></p>
                    <p><strong>Form ID:</strong> <code class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded">${formId}</code></p>
                </div>
            `;
            formPreview.classList.remove('hidden');
        } else {
            formPreview.classList.add('hidden');
        }
    });
}
</script>

<?php include $rootPath . '/app/views/admin/snippets/footer.php'; ?>
