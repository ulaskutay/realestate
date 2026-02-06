<?php 
// Admin snippet'lerini mutlak yol ile yükle
$rootPath = $_SERVER['DOCUMENT_ROOT'];
include $rootPath . '/app/views/admin/snippets/header.php'; 
?>

<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php 
        $currentPage = 'realestate-listings';
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
                                <a href="<?php echo admin_url('module/realestate-listings'); ?>" class="text-gray-500 dark:text-gray-400 hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined text-xl">arrow_back</span>
                                </a>
                                <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight"><?php echo esc_html($title); ?></h1>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 text-base">Yeni emlak ilanı ekleyin</p>
                        </div>
                    </header>

                    <!-- Form -->
                    <form action="<?php echo admin_url('module/realestate-listings/store'); ?>" method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Main Content -->
                            <div class="lg:col-span-2 space-y-6">
                                
                                <!-- İlan Bilgileri -->
                                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">İlan Bilgileri</h2>
                                    
                                    <div class="space-y-5">
                                        <div>
                                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                İlan Başlığı <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" id="title" name="title" required
                                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                                   placeholder="Örn: Deniz Manzaralı Lüks Villa">
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                            <div>
                                                <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                    Konum
                                                </label>
                                                <input type="text" id="location" name="location"
                                                       class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                                       placeholder="Örn: İstanbul, Kadıköy">
                                            </div>

                                            <div>
                                                <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                    Fiyat (₺)
                                                </label>
                                                <input type="number" id="price" name="price" step="0.01" min="0"
                                                       class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                                       placeholder="0.00">
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                            <div>
                                                <label for="ada" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ada</label>
                                                <input type="text" id="ada" name="ada" placeholder="Örn: 12"
                                                       class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                            </div>
                                            <div>
                                                <label for="parsel" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Parsel</label>
                                                <input type="text" id="parsel" name="parsel" placeholder="Örn: 5"
                                                       class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                            </div>
                                        </div>

                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Açıklama
                                                </label>
                                                <button type="button" id="generate-ai-description" 
                                                        class="flex items-center gap-2 px-3 py-1.5 text-sm bg-gradient-to-r from-purple-500 to-blue-500 text-white rounded-lg hover:from-purple-600 hover:to-blue-600 transition-all shadow-sm">
                                                    <span class="material-symbols-outlined text-base">auto_awesome</span>
                                                    <span>AI ile Oluştur</span>
                                                </button>
                                            </div>
                                            <textarea id="description" name="description" rows="6"
                                                      class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all resize-none"
                                                      placeholder="İlan hakkında detaylı bilgi verin..."></textarea>
                                            <div id="ai-description-status" class="hidden mt-2 text-sm"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Emlak Detayları -->
                                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Emlak Detayları</h2>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div>
                                            <label for="property_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Emlak Tipi
                                            </label>
                                            <select id="property_type" name="property_type"
                                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                                <option value="house">Müstakil Ev</option>
                                                <option value="apartment">Daire</option>
                                                <option value="villa">Villa</option>
                                                <option value="commercial">Ticari</option>
                                                <option value="land">Arsa</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label for="listing_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                İlan Durumu
                                            </label>
                                            <select id="listing_status" name="listing_status"
                                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                                <option value="sale" selected>Satılık</option>
                                                <option value="rent">Kiralık</option>
                                            </select>
                                        </div>

                                    <div>
                                        <label for="featured_image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Öne Çıkan Görsel
                                        </label>
                                        <input type="hidden" id="featured_image" name="featured_image" value="">
                                        <div id="featured_image_preview_container" class="space-y-3">
                                            <div id="featured_image_preview" class="w-full border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden bg-gray-50 dark:bg-gray-800/50" style="display: none;">
                                                <div class="relative aspect-video">
                                                    <img id="featured_image_preview_img" src="" alt="Öne çıkan görsel önizleme" class="w-full h-full object-cover">
                                                    <button type="button" onclick="removeFeaturedImage()" class="absolute top-2 right-2 p-2 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors shadow-lg">
                                                        <span class="material-symbols-outlined text-sm">close</span>
                                                    </button>
                                                </div>
                                            </div>
                                            <button type="button" onclick="selectFeaturedImage()" 
                                                    class="w-full px-4 py-3 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-primary hover:bg-primary/5 transition-colors flex items-center justify-center gap-2 text-gray-600 dark:text-gray-400">
                                                <span class="material-symbols-outlined">add_photo_alternate</span>
                                                <span id="featured_image_button_text">Görsel Seç</span>
                                            </button>
                                        </div>
                                    </div>

                                        <div>
                                            <label for="bedrooms" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Yatak Odası
                                            </label>
                                            <input type="number" id="bedrooms" name="bedrooms" min="0"
                                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                                   placeholder="0">
                                        </div>

                                        <div>
                                            <label for="bathrooms" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Banyo
                                            </label>
                                            <input type="number" id="bathrooms" name="bathrooms" min="0" step="0.5"
                                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                                   placeholder="0">
                                        </div>

                                        <div>
                                            <label for="living_rooms" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Salon
                                            </label>
                                            <input type="number" id="living_rooms" name="living_rooms" min="0"
                                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                                   placeholder="0">
                                        </div>

                                        <div>
                                            <label for="rooms" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Oda
                                            </label>
                                            <input type="number" id="rooms" name="rooms" min="0"
                                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                                   placeholder="0">
                                        </div>

                                        <div>
                                            <label for="area" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Alan (m²)
                                            </label>
                                            <input type="number" id="area" name="area" step="0.01" min="0"
                                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                                   placeholder="0">
                                            <input type="hidden" name="area_unit" value="sqm">
                                        </div>
                                    </div>
                                </div>

                                <!-- Görsel Galerisi -->
                                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Görsel Galerisi</h2>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Galeri Görselleri
                                            </label>
                                            <button type="button" onclick="openGalleryPicker()" 
                                                    class="w-full px-4 py-3 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-primary hover:bg-primary/5 transition-colors flex items-center justify-center gap-2 text-gray-600 dark:text-gray-400">
                                                <span class="material-symbols-outlined">add_photo_alternate</span>
                                                <span>Görsel Ekle</span>
                                            </button>
                                        </div>
                                        
                                        <input type="hidden" id="gallery" name="gallery" value="[]">
                                        
                                        <div id="gallery-preview" class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                            <!-- Gallery items will be added here -->
                                        </div>
                                        
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Birden fazla görsel ekleyebilirsiniz. Görseller galeri olarak gösterilecektir.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Sidebar -->
                            <div class="space-y-6">
                                <!-- Yayınlama Ayarları -->
                                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Yayınlama Ayarları</h2>
                                    
                                    <div class="space-y-5">
                                        <div>
                                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Durum
                                            </label>
                                            <select id="status" name="status"
                                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                                <option value="draft">Taslak</option>
                                                <option value="published" selected>Yayınla</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="flex items-center cursor-pointer">
                                                <input type="checkbox" name="is_featured" value="1" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                                <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">Öne Çıkan İlan</span>
                                            </label>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Öne çıkan ilanlar ana sayfada gösterilir</p>
                                        </div>

                                        <div>
                                            <label for="realtor_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Emlakçı
                                            </label>
                                            <select id="realtor_id" name="realtor_id"
                                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                                <option value="">Emlakçı Seçin</option>
                                                <?php if (!empty($agents)): ?>
                                                    <?php foreach ($agents as $agent): ?>
                                                        <option value="<?php echo esc_attr($agent['id']); ?>">
                                                            <?php 
                                                            $displayName = trim(($agent['first_name'] ?? '') . ' ' . ($agent['last_name'] ?? ''));
                                                            echo esc_html($displayName . (!empty($agent['email']) ? ' (' . $agent['email'] . ')' : ''));
                                                            ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">İlanı yönetecek emlakçıyı seçin</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- İşlem Butonları -->
                                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                    <div class="flex flex-col gap-3">
                                        <button type="submit" 
                                                class="w-full px-4 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium flex items-center justify-center gap-2">
                                            <span class="material-symbols-outlined text-xl">save</span>
                                            <span>İlanı Kaydet</span>
                                        </button>
                                        <a href="<?php echo admin_url('module/realestate-listings'); ?>" 
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
let galleryItems = [];

// Featured Image Functions
function selectFeaturedImage() {
    if (typeof openMediaPicker === 'function') {
        openMediaPicker({
            multiple: false,
            type: 'image',
            onSelect: function(item) {
                document.getElementById('featured_image').value = item.file_url;
                const preview = document.getElementById('featured_image_preview');
                const previewImg = document.getElementById('featured_image_preview_img');
                const buttonText = document.getElementById('featured_image_button_text');
                if (preview && previewImg) {
                    previewImg.src = item.file_url;
                    preview.style.display = 'block';
                    buttonText.textContent = 'Görseli Değiştir';
                }
            }
        });
    } else {
        alert('Medya seçici yüklenemedi. Sayfayı yenileyin.');
    }
}

function removeFeaturedImage() {
    document.getElementById('featured_image').value = '';
    const preview = document.getElementById('featured_image_preview');
    const buttonText = document.getElementById('featured_image_button_text');
    if (preview) {
        preview.style.display = 'none';
        buttonText.textContent = 'Görsel Seç';
    }
}

function openGalleryPicker() {
    if (typeof openMediaPicker === 'function') {
        openMediaPicker({
            multiple: true,
            type: 'image',
            onSelect: function(selected) {
                if (Array.isArray(selected)) {
                    selected.forEach(item => {
                        if (!galleryItems.find(g => g.id === item.id)) {
                            galleryItems.push(item);
                        }
                    });
                } else {
                    if (!galleryItems.find(g => g.id === selected.id)) {
                        galleryItems.push(selected);
                    }
                }
                updateGalleryPreview();
                updateGalleryInput();
            }
        });
    } else {
        alert('Medya seçici yüklenemedi. Sayfayı yenileyin.');
    }
}

function removeGalleryItem(id) {
    galleryItems = galleryItems.filter(item => item.id !== id);
    updateGalleryPreview();
    updateGalleryInput();
}

function updateGalleryPreview() {
    const preview = document.getElementById('gallery-preview');
    if (galleryItems.length === 0) {
        preview.innerHTML = '';
        return;
    }
    
    preview.innerHTML = galleryItems.map(item => `
        <div class="relative group">
            <img src="${item.file_url}" alt="${item.original_name}" 
                 class="w-full h-32 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
            <button type="button" onclick="removeGalleryItem(${item.id})" 
                    class="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
    `).join('');
}

function updateGalleryInput() {
    const input = document.getElementById('gallery');
    input.value = JSON.stringify(galleryItems.map(item => item.file_url));
}

// AI Açıklama Oluşturma
document.addEventListener('DOMContentLoaded', function() {
    const generateBtn = document.getElementById('generate-ai-description');
    const descriptionTextarea = document.getElementById('description');
    const statusDiv = document.getElementById('ai-description-status');
    
    if (generateBtn && descriptionTextarea) {
        generateBtn.addEventListener('click', function() {
            // Form verilerini topla
            const formData = {
                title: document.getElementById('title')?.value || '',
                location: document.getElementById('location')?.value || '',
                price: document.getElementById('price')?.value || 0,
                property_type: document.getElementById('property_type')?.value || 'house',
                listing_status: document.getElementById('listing_status')?.value || 'sale',
                bedrooms: document.getElementById('bedrooms')?.value || 0,
                bathrooms: document.getElementById('bathrooms')?.value || 0,
                living_rooms: document.getElementById('living_rooms')?.value || 0,
                rooms: document.getElementById('rooms')?.value || 0,
                area: document.getElementById('area')?.value || 0,
                area_unit: document.getElementById('area_unit')?.value || 'sqm'
            };
            
            // Başlık kontrolü
            if (!formData.title.trim()) {
                showAIStatus('error', 'Lütfen önce ilan başlığını girin.');
                return;
            }
            
            // Loading state
            generateBtn.disabled = true;
            generateBtn.innerHTML = '<span class="material-symbols-outlined text-base animate-spin">sync</span><span>Oluşturuluyor...</span>';
            statusDiv.classList.remove('hidden');
            statusDiv.className = 'mt-2 p-3 rounded-lg text-sm bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200';
            statusDiv.textContent = 'AI açıklama oluşturuluyor, lütfen bekleyin...';
            
            // AJAX çağrısı
            fetch('<?php echo admin_url('module/realestate-listings/generate-description'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Başarılı - açıklamayı yaz
                    descriptionTextarea.value = data.description;
                    showAIStatus('success', 'Açıklama başarıyla oluşturuldu! İstediğiniz gibi düzenleyebilirsiniz.');
                    
                    // Textarea'ya odaklan ve scroll et
                    descriptionTextarea.focus();
                    descriptionTextarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    // Hata
                    showAIStatus('error', data.error || 'Açıklama oluşturulurken bir hata oluştu.');
                }
            })
            .catch(error => {
                showAIStatus('error', 'Bağlantı hatası: ' + error.message);
            })
            .finally(() => {
                // Button'u eski haline getir
                generateBtn.disabled = false;
                generateBtn.innerHTML = '<span class="material-symbols-outlined text-base">auto_awesome</span><span>AI ile Oluştur</span>';
            });
        });
    }
    
    function showAIStatus(type, message) {
        statusDiv.classList.remove('hidden');
        if (type === 'success') {
            statusDiv.className = 'mt-2 p-3 rounded-lg text-sm bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200';
        } else {
            statusDiv.className = 'mt-2 p-3 rounded-lg text-sm bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200';
        }
        statusDiv.textContent = message;
        
        // 5 saniye sonra gizle (başarılı durumda)
        if (type === 'success') {
            setTimeout(() => {
                statusDiv.classList.add('hidden');
            }, 5000);
        }
    }
});
</script>

<?php include $rootPath . '/app/views/admin/snippets/footer.php'; ?>
