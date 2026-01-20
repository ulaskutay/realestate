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
                        <p class="text-gray-500 dark:text-gray-400 text-base">Emlak ilanını düzenleyin</p>
                    </div>
                </header>

                <!-- Message -->
                <?php if (!empty($message)): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 text-red-800 dark:text-red-200'; ?>">
                    <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
                </div>
                <?php endif; ?>

                <!-- Form -->
                <form method="POST" action="<?php echo admin_url('module/realestate-listings/update/' . $listing['id']); ?>" class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Main Content -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Property Information -->
                            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">İlan Bilgileri</h2>
                                
                                <div class="space-y-5">
                                    <div>
                                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            İlan Başlığı <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" id="title" name="title" value="<?php echo esc_attr($listing['title']); ?>" required
                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                    </div>

                                    <div>
                                        <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            URL Kısa Adı
                                        </label>
                                        <input type="text" id="slug" name="slug" value="<?php echo esc_attr($listing['slug']); ?>"
                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div>
                                            <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Konum
                                            </label>
                                            <input type="text" id="location" name="location" value="<?php echo esc_attr($listing['location']); ?>"
                                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                        </div>

                                        <div>
                                            <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Fiyat (₺)
                                            </label>
                                            <input type="number" id="price" name="price" value="<?php echo esc_attr($listing['price']); ?>" step="0.01"
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
                                                  class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all resize-none"><?php echo esc_html($listing['description']); ?></textarea>
                                        <div id="ai-description-status" class="hidden mt-2 text-sm"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Property Details -->
                            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Emlak Detayları</h2>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label for="property_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Emlak Tipi
                                        </label>
                                        <select id="property_type" name="property_type"
                                                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                            <option value="house" <?php echo $listing['property_type'] === 'house' ? 'selected' : ''; ?>>Müstakil Ev</option>
                                            <option value="apartment" <?php echo $listing['property_type'] === 'apartment' ? 'selected' : ''; ?>>Daire</option>
                                            <option value="villa" <?php echo $listing['property_type'] === 'villa' ? 'selected' : ''; ?>>Villa</option>
                                            <option value="commercial" <?php echo $listing['property_type'] === 'commercial' ? 'selected' : ''; ?>>Ticari</option>
                                            <option value="land" <?php echo $listing['property_type'] === 'land' ? 'selected' : ''; ?>>Arsa</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="listing_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            İlan Durumu
                                        </label>
                                        <select id="listing_status" name="listing_status"
                                                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                            <option value="sale" <?php echo ($listing['listing_status'] ?? 'sale') === 'sale' ? 'selected' : ''; ?>>Satılık</option>
                                            <option value="rent" <?php echo ($listing['listing_status'] ?? 'sale') === 'rent' ? 'selected' : ''; ?>>Kiralık</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="featured_image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Öne Çıkan Görsel
                                        </label>
                                        <input type="hidden" id="featured_image" name="featured_image" value="<?php echo esc_attr($listing['featured_image'] ?? ''); ?>">
                                        <div id="featured_image_preview_container" class="space-y-3">
                                            <div id="featured_image_preview" class="w-full border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden bg-gray-50 dark:bg-gray-800/50" style="<?php echo !empty($listing['featured_image']) ? 'display: block;' : 'display: none;'; ?>">
                                                <div class="relative aspect-video">
                                                    <img id="featured_image_preview_img" src="<?php echo !empty($listing['featured_image']) ? esc_url($listing['featured_image']) : ''; ?>" alt="Öne çıkan görsel önizleme" class="w-full h-full object-cover">
                                                    <button type="button" onclick="removeFeaturedImage()" class="absolute top-2 right-2 p-2 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors shadow-lg">
                                                        <span class="material-symbols-outlined text-sm">close</span>
                                                    </button>
                                                </div>
                                            </div>
                                            <button type="button" onclick="selectFeaturedImage()" 
                                                    class="w-full px-4 py-3 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-primary hover:bg-primary/5 transition-colors flex items-center justify-center gap-2 text-gray-600 dark:text-gray-400">
                                                <span class="material-symbols-outlined">add_photo_alternate</span>
                                                <span id="featured_image_button_text"><?php echo !empty($listing['featured_image']) ? 'Görseli Değiştir' : 'Görsel Seç'; ?></span>
                                            </button>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="bedrooms" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Yatak Odası
                                        </label>
                                        <input type="number" id="bedrooms" name="bedrooms" value="<?php echo esc_attr($listing['bedrooms']); ?>" min="0"
                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                    </div>

                                    <div>
                                        <label for="bathrooms" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Banyo
                                        </label>
                                        <input type="number" id="bathrooms" name="bathrooms" value="<?php echo esc_attr($listing['bathrooms'] ?? 0); ?>" min="0" step="0.5"
                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                    </div>

                                    <div>
                                        <label for="living_rooms" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Salon
                                        </label>
                                        <input type="number" id="living_rooms" name="living_rooms" value="<?php echo esc_attr($listing['living_rooms'] ?? 0); ?>" min="0"
                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                    </div>

                                    <div>
                                        <label for="rooms" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Oda
                                        </label>
                                        <input type="number" id="rooms" name="rooms" value="<?php echo esc_attr($listing['rooms'] ?? 0); ?>" min="0"
                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                    </div>

                                    <div>
                                        <label for="area" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Alan
                                        </label>
                                        <input type="number" id="area" name="area" value="<?php echo esc_attr($listing['area']); ?>" step="0.01"
                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                    </div>

                                    <div>
                                        <label for="area_unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Alan Birimi
                                        </label>
                                        <select id="area_unit" name="area_unit"
                                                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                            <option value="sqft" <?php echo $listing['area_unit'] === 'sqft' ? 'selected' : ''; ?>>ft² (Kare Fit)</option>
                                            <option value="sqm" <?php echo $listing['area_unit'] === 'sqm' ? 'selected' : ''; ?>>m² (Metrekare)</option>
                                        </select>
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
                                        
                                        <input type="hidden" id="gallery" name="gallery" value="<?php echo esc_attr($listing['gallery'] ?? '[]'); ?>">
                                        
                                        <div id="gallery-preview" class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                            <!-- Gallery items will be added here -->
                                        </div>
                                        
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Birden fazla görsel ekleyebilirsiniz. Görseller galeri olarak gösterilecektir.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="space-y-6">
                            <!-- Publish Settings -->
                            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Yayınlama Ayarları</h2>
                                
                                <div class="space-y-5">
                                    <div>
                                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Durum
                                        </label>
                                        <select id="status" name="status"
                                                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                            <option value="draft" <?php echo $listing['status'] === 'draft' ? 'selected' : ''; ?>>Taslak</option>
                                            <option value="published" <?php echo $listing['status'] === 'published' ? 'selected' : ''; ?>>Yayınla</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="is_featured" value="1" <?php echo $listing['is_featured'] ? 'checked' : ''; ?> class="mr-2">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Öne Çıkan İlan</span>
                                        </label>
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
                                                    <option value="<?php echo esc_attr($agent['id']); ?>" <?php echo (isset($listing['realtor_id']) && $listing['realtor_id'] == $agent['id']) ? 'selected' : ''; ?>>
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

                            <!-- Action Buttons -->
                            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                <div class="flex flex-col gap-3">
                                    <button type="submit" 
                                            class="w-full px-4 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium flex items-center justify-center gap-2">
                                        <span class="material-symbols-outlined text-xl">save</span>
                                        <span>İlanı Güncelle</span>
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

// Mevcut galeri verilerini yükle
<?php 
$existingGallery = json_decode($listing['gallery'] ?? '[]', true);
if (!is_array($existingGallery)) $existingGallery = [];
?>
const existingGallery = <?php echo json_encode($existingGallery); ?>;

// Mevcut görselleri yükle
existingGallery.forEach(url => {
    if (url) {
        galleryItems.push({
            id: Date.now() + Math.random(),
            file_url: url,
            original_name: url.split('/').pop()
        });
    }
});

function openGalleryPicker() {
    if (typeof openMediaPicker === 'function') {
        openMediaPicker({
            multiple: true,
            type: 'image',
            onSelect: function(selected) {
                if (Array.isArray(selected)) {
                    selected.forEach(item => {
                        if (!galleryItems.find(g => g.file_url === item.file_url)) {
                            galleryItems.push(item);
                        }
                    });
                } else {
                    if (!galleryItems.find(g => g.file_url === selected.file_url)) {
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

// Sayfa yüklendiğinde önizlemeyi güncelle
document.addEventListener('DOMContentLoaded', function() {
    updateGalleryPreview();
    
    // AI Açıklama Oluşturma
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
