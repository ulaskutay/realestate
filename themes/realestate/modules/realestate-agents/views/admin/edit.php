<?php 
// Admin snippet'lerini mutlak yol ile yükle
$rootPath = $_SERVER['DOCUMENT_ROOT'];
include $rootPath . '/app/views/admin/snippets/header.php'; 
?>

<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php 
        $currentPage = 'realestate-agents';
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
                                <a href="<?php echo admin_url('module/realestate-agents'); ?>" class="text-gray-500 dark:text-gray-400 hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined text-xl">arrow_back</span>
                                </a>
                                <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight"><?php echo esc_html($title); ?></h1>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 text-base">Emlak danışmanını düzenleyin</p>
                        </div>
                    </header>

                    <!-- Message -->
                    <?php if (!empty($message)): ?>
                    <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 text-red-800 dark:text-red-200'; ?>">
                        <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form method="POST" action="<?php echo admin_url('module/realestate-agents/update/' . $agent['id']); ?>" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Main Content -->
                            <div class="lg:col-span-2 space-y-6">
                                
                                <!-- Kişisel Bilgiler -->
                                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Kişisel Bilgiler</h2>
                                    
                                    <div class="space-y-5">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                            <div>
                                                <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                    Ad <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($agent['first_name']); ?>" required
                                                       class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                            </div>

                                            <div>
                                                <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                    Soyad <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($agent['last_name']); ?>" required
                                                       class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                            </div>
                                        </div>

                                        <div>
                                            <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                URL Kısa Adı
                                            </label>
                                            <input type="text" id="slug" name="slug" value="<?php echo esc_attr($agent['slug']); ?>"
                                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                        </div>

                                        <div>
                                            <label for="photo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Fotoğraf
                                            </label>
                                            <input type="hidden" id="photo" name="photo" value="<?php echo esc_attr($agent['photo'] ?? ''); ?>">
                                            <div id="photo_preview_container" class="space-y-3">
                                                <div id="photo_preview" class="w-32 h-32 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-full overflow-hidden bg-gray-50 dark:bg-gray-800/50 mx-auto" style="<?php echo !empty($agent['photo']) ? 'display: block;' : 'display: none;'; ?>">
                                                    <img id="photo_preview_img" src="<?php echo !empty($agent['photo']) ? esc_url($agent['photo']) : ''; ?>" alt="Fotoğraf önizleme" class="w-full h-full object-cover">
                                                </div>
                                                <button type="button" onclick="selectPhoto()" 
                                                        class="w-full px-4 py-3 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-primary hover:bg-primary/5 transition-colors flex items-center justify-center gap-2 text-gray-600 dark:text-gray-400">
                                                    <span class="material-symbols-outlined">add_photo_alternate</span>
                                                    <span id="photo_button_text"><?php echo !empty($agent['photo']) ? 'Fotoğrafı Değiştir' : 'Fotoğraf Seç'; ?></span>
                                                </button>
                                            </div>
                                        </div>

                                        <div>
                                            <label for="bio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Biyografi
                                            </label>
                                            <textarea id="bio" name="bio" rows="6"
                                                      class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all resize-none"><?php echo esc_html($agent['bio'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- İletişim Bilgileri -->
                                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">İletişim Bilgileri</h2>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div>
                                            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Telefon
                                            </label>
                                            <input type="tel" id="phone" name="phone" value="<?php echo esc_attr($agent['phone'] ?? ''); ?>"
                                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                        </div>

                                        <div>
                                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                E-posta
                                            </label>
                                            <input type="email" id="email" name="email" value="<?php echo esc_attr($agent['email'] ?? ''); ?>"
                                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                        </div>
                                    </div>
                                </div>

                                <!-- Uzmanlık ve Deneyim -->
                                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Uzmanlık ve Deneyim</h2>
                                    
                                    <div class="space-y-5">
                                        <div>
                                            <label for="specializations" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Uzmanlık Alanları
                                            </label>
                                            <textarea id="specializations" name="specializations" rows="3"
                                                      class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all resize-none"><?php echo esc_html($agent['specializations'] ?? ''); ?></textarea>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Her satıra bir uzmanlık alanı yazabilirsiniz</p>
                                        </div>

                                        <div>
                                            <label for="experience_years" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Deneyim (Yıl)
                                            </label>
                                            <input type="number" id="experience_years" name="experience_years" min="0" value="<?php echo esc_attr($agent['experience_years'] ?? 0); ?>"
                                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                        </div>
                                    </div>
                                </div>

                                <!-- Sosyal Medya -->
                                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Sosyal Medya</h2>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div>
                                            <label for="facebook" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Facebook
                                            </label>
                                            <input type="url" id="facebook" name="facebook" value="<?php echo esc_attr($agent['facebook'] ?? ''); ?>"
                                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                        </div>

                                        <div>
                                            <label for="twitter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Twitter
                                            </label>
                                            <input type="url" id="twitter" name="twitter" value="<?php echo esc_attr($agent['twitter'] ?? ''); ?>"
                                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                        </div>

                                        <div>
                                            <label for="instagram" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Instagram
                                            </label>
                                            <input type="url" id="instagram" name="instagram" value="<?php echo esc_attr($agent['instagram'] ?? ''); ?>"
                                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                        </div>

                                        <div>
                                            <label for="linkedin" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                LinkedIn
                                            </label>
                                            <input type="url" id="linkedin" name="linkedin" value="<?php echo esc_attr($agent['linkedin'] ?? ''); ?>"
                                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sidebar -->
                            <div class="space-y-6">
                                <!-- Yayınlama Ayarları -->
                                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Ayarlar</h2>
                                    
                                    <div class="space-y-5">
                                        <div>
                                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Durum
                                            </label>
                                            <select id="status" name="status"
                                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                                <option value="active" <?php echo ($agent['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Aktif</option>
                                                <option value="inactive" <?php echo ($agent['status'] ?? 'active') === 'inactive' ? 'selected' : ''; ?>>Pasif</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="flex items-center cursor-pointer">
                                                <input type="checkbox" name="is_featured" value="1" <?php echo ($agent['is_featured'] ?? 0) ? 'checked' : ''; ?> class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                                <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">Öne Çıkan</span>
                                            </label>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Öne çıkan danışmanlar ana sayfada gösterilir</p>
                                        </div>

                                        <div>
                                            <label for="display_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Sıralama
                                            </label>
                                            <input type="number" id="display_order" name="display_order" min="0" value="<?php echo esc_attr($agent['display_order'] ?? 0); ?>"
                                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Düşük sayılar önce gösterilir</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- İşlem Butonları -->
                                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                    <div class="flex flex-col gap-3">
                                        <button type="submit" 
                                                class="w-full px-4 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium flex items-center justify-center gap-2">
                                            <span class="material-symbols-outlined text-xl">save</span>
                                            <span>Değişiklikleri Kaydet</span>
                                        </button>
                                        <a href="<?php echo admin_url('module/realestate-agents'); ?>" 
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

<script src="<?php echo ViewRenderer::assetUrl('admin/js/media-picker.js'); ?>"></script>
<script>
// Photo Functions
function selectPhoto() {
    if (typeof openMediaPicker === 'function') {
        openMediaPicker({
            multiple: false,
            type: 'image',
            onSelect: function(item) {
                document.getElementById('photo').value = item.file_url;
                const preview = document.getElementById('photo_preview');
                const previewImg = document.getElementById('photo_preview_img');
                const buttonText = document.getElementById('photo_button_text');
                if (preview && previewImg) {
                    previewImg.src = item.file_url;
                    preview.style.display = 'block';
                    buttonText.textContent = 'Fotoğrafı Değiştir';
                }
            }
        });
    } else {
        alert('Medya seçici yüklenemedi. Sayfayı yenileyin.');
    }
}
</script>

<?php include $rootPath . '/app/views/admin/snippets/footer.php'; ?>
