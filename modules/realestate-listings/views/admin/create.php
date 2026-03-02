<?php
if (!isset($rootPath)) $rootPath = dirname(dirname(dirname(__DIR__)));
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

                                        <div class="border-t border-gray-200 dark:border-gray-600 pt-5 mt-2">
                                            <div class="flex items-center gap-2 mb-3">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Harita konumu</span>
                                                <button type="button" id="listing-geocode-btn" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Adresten konum al</button>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                                                <div>
                                                    <label for="latitude" class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Enlem</label>
                                                    <input type="text" id="latitude" name="latitude" placeholder="39.1234" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                                                </div>
                                                <div>
                                                    <label for="longitude" class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Boylam</label>
                                                    <input type="text" id="longitude" name="longitude" placeholder="35.5678" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                                                </div>
                                                <div>
                                                    <label for="city" class="block text-xs text-gray-500 dark:text-gray-400 mb-1">İl</label>
                                                    <select id="city" name="city" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                                                        <option value="">İl seçin</option>
                                                        <?php if (!empty($iller)): foreach ($iller as $ilCode => $ilName): ?>
                                                            <option value="<?php echo esc_attr($ilName); ?>"><?php echo esc_html($ilName); ?></option>
                                                        <?php endforeach; endif; ?>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label for="district" class="block text-xs text-gray-500 dark:text-gray-400 mb-1">İlçe</label>
                                                    <select id="district" name="district" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                                                        <option value="">İlçe seçin</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label for="neighborhood" class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Mahalle</label>
                                                    <input type="text" id="neighborhood" name="neighborhood" placeholder="Mahalle / köy" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                                                </div>
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Konum alanına adres yazıp &quot;Adresten konum al&quot; ile enlem/boylam ve il/ilçe/mahalle doldurabilirsiniz (Harita İlanlar modülü ayarlarında Google Maps API key gerekir).</p>
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
                                                <?php
                                                $property_types = $property_types ?? [];
                                                foreach ($property_types as $val => $lbl): ?>
                                                    <option value="<?php echo esc_attr($val); ?>"><?php echo esc_html($lbl); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="button" id="open-listing-options-modal" class="inline-flex items-center gap-1 mt-1.5 text-sm text-primary hover:underline">
                                                <span class="material-symbols-outlined text-base">add_circle_outline</span> İlan tipi veya durumu ekle
                                            </button>
                                        </div>

                                        <div>
                                            <label for="listing_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                İlan Durumu
                                            </label>
                                            <select id="listing_status" name="listing_status"
                                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                                <?php
                                                $listing_statuses = $listing_statuses ?? [];
                                                $first = true;
                                                foreach ($listing_statuses as $val => $lbl): ?>
                                                    <option value="<?php echo esc_attr($val); ?>"<?php echo $first ? ' selected' : ''; ?>><?php echo esc_html($lbl); ?></option>
                                                <?php $first = false; endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Kategoriler (çoklu seçim)
                                            </label>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">İlan hem satılık/kiralık hem de daire/müstakil gibi birden fazla kategoride listelenebilir.</p>
                                            <div class="flex flex-wrap gap-x-4 gap-y-2">
                                                <?php
                                                $all_categories = $all_categories ?? [];
                                                foreach ($all_categories as $cat): ?>
                                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                                        <input type="checkbox" name="category_ids[]" value="<?php echo (int) $cat['id']; ?>"
                                                               class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                                        <span class="text-sm text-gray-700 dark:text-gray-300"><?php echo esc_html($cat['name']); ?></span>
                                                    </label>
                                                <?php endforeach; ?>
                                                <?php if (empty($all_categories)): ?>
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">Önce İlanlar modülü ayarlarından emlak tipi ve ilan durumu ekleyin.</span>
                                                <?php endif; ?>
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

                                <!-- Öne Çıkan Görsel -->
                                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Öne Çıkan Görsel</h2>
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

                    <!-- Modal: İlan tipi / durumu ekle -->
                    <div id="listing-options-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true" role="dialog">
                        <div class="flex min-h-full items-center justify-center p-4">
                            <div class="fixed inset-0 bg-black/50 transition-opacity" id="listing-options-modal-backdrop"></div>
                            <div class="relative bg-white dark:bg-[#1e293b] rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 w-full max-w-md p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">İlan tipi veya durumu ekle</h3>
                                    <button type="button" id="close-listing-options-modal" class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <span class="material-symbols-outlined">close</span>
                                    </button>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Yeni emlak tipi</label>
                                        <div class="flex gap-2">
                                            <input type="text" id="modal-new-property-type" placeholder="Örn: Daire, Villa" class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <button type="button" id="modal-add-property-type" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 text-sm font-medium">Ekle</button>
                                        </div>
                                        <p id="modal-property-type-msg" class="mt-1 text-xs text-gray-500 dark:text-gray-400 hidden"></p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Yeni ilan durumu</label>
                                        <div class="flex gap-2">
                                            <input type="text" id="modal-new-listing-status" placeholder="Örn: Kiralık, Satılık" class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <button type="button" id="modal-add-listing-status" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 text-sm font-medium">Ekle</button>
                                        </div>
                                        <p id="modal-listing-status-msg" class="mt-1 text-xs text-gray-500 dark:text-gray-400 hidden"></p>
                                    </div>
                                </div>
                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600 flex justify-end">
                                    <button type="button" id="close-listing-options-modal-btn" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm font-medium">Kapat</button>
                                </div>
                            </div>
                        </div>
                    </div>
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

    // İl değişince ilçe listesini doldur
    var locationOptionsUrl = '<?php echo esc_js(admin_url("module/realestate-listings/location-options")); ?>';
    var citySelect = document.getElementById('city');
    var districtSelect = document.getElementById('district');
    function loadIlceler(ilNameOrCode, thenSelect) {
        if (!districtSelect) return Promise.resolve();
        districtSelect.innerHTML = '<option value="">İlçe seçin</option>';
        if (!ilNameOrCode) return Promise.resolve();
        var url = locationOptionsUrl + (locationOptionsUrl.indexOf('?') >= 0 ? '&' : '?') + 'type=ilceler&il=' + encodeURIComponent(ilNameOrCode);
        return fetch(url).then(function(r) { return r.json(); }).then(function(res) {
            var items = res.items || [];
            items.forEach(function(item) {
                var opt = document.createElement('option');
                opt.value = item.name || '';
                opt.textContent = item.name || '';
                districtSelect.appendChild(opt);
            });
            if (thenSelect && districtSelect) districtSelect.value = thenSelect;
        });
    }
    if (citySelect) {
        citySelect.addEventListener('change', function() {
            loadIlceler(citySelect.value);
        });
    }

    // Adresten konum al (Harita İlanlar modülü geocode)
    const geocodeBtn = document.getElementById('listing-geocode-btn');
    if (geocodeBtn) {
        geocodeBtn.addEventListener('click', function() {
            var loc = (document.getElementById('location') && document.getElementById('location').value) || '';
            if (!loc.trim()) {
                alert('Önce Konum alanına bir adres yazın.');
                return;
            }
            geocodeBtn.disabled = true;
            geocodeBtn.textContent = 'Aranıyor...';
            var baseUrl = '<?php echo esc_js(admin_url("module/listings-map/geocode")); ?>';
            var url = baseUrl + (baseUrl.indexOf('?') >= 0 ? '&' : '?') + 'address=' + encodeURIComponent(loc);
            fetch(url).then(function(r) { return r.json(); }).then(function(data) {
                if (data.success) {
                    var el = document.getElementById('latitude'); if (el && data.latitude != null) el.value = data.latitude;
                    el = document.getElementById('longitude'); if (el && data.longitude != null) el.value = data.longitude;
                    if (citySelect) citySelect.value = data.city || '';
                    loadIlceler(data.city || '', data.district || '').then(function() {
                        el = document.getElementById('neighborhood'); if (el) el.value = data.neighborhood || '';
                    });
                } else {
                    var errMsg = data.message || 'Konum bulunamadı.';
                    if (data.error_detail) errMsg += '\n\nDetay: ' + data.error_detail;
                    alert(errMsg);
                }
            }).catch(function() {
                alert('İstek başarısız.');
            }).finally(function() {
                geocodeBtn.disabled = false;
                geocodeBtn.textContent = 'Adresten konum al';
            });
        });
    }

    // Modal: İlan tipi / durumu ekle
    var listingOptionsModal = document.getElementById('listing-options-modal');
    var addOptionUrl = '<?php echo esc_js(admin_url('module/realestate-listings/add-option')); ?>';
    function openListingOptionsModal() {
        if (listingOptionsModal) {
            listingOptionsModal.classList.remove('hidden');
            document.getElementById('modal-new-property-type').value = '';
            document.getElementById('modal-new-listing-status').value = '';
            document.getElementById('modal-property-type-msg').classList.add('hidden');
            document.getElementById('modal-listing-status-msg').classList.add('hidden');
        }
    }
    function closeListingOptionsModal() {
        if (listingOptionsModal) listingOptionsModal.classList.add('hidden');
    }
    document.getElementById('open-listing-options-modal').addEventListener('click', openListingOptionsModal);
    document.getElementById('close-listing-options-modal').addEventListener('click', closeListingOptionsModal);
    document.getElementById('close-listing-options-modal-btn').addEventListener('click', closeListingOptionsModal);
    document.getElementById('listing-options-modal-backdrop').addEventListener('click', closeListingOptionsModal);

    function addOption(type, labelInputId, msgId, selectId) {
        var label = document.getElementById(labelInputId).value.trim();
        if (!label) {
            var el = document.getElementById(msgId);
            el.textContent = 'Lütfen bir ad girin.';
            el.classList.remove('hidden');
            el.className = 'mt-1 text-xs text-red-600 dark:text-red-400';
            return;
        }
        var msgEl = document.getElementById(msgId);
        msgEl.classList.remove('hidden');
        msgEl.className = 'mt-1 text-xs text-gray-500 dark:text-gray-400';
        msgEl.textContent = 'Ekleniyor...';
        var formData = new URLSearchParams();
        formData.append('type', type);
        formData.append('label', label);
        fetch(addOptionUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (data.success) {
                var sel = document.getElementById(selectId);
                var opt = document.createElement('option');
                opt.value = data.key;
                opt.textContent = data.label;
                sel.appendChild(opt);
                sel.value = data.key;
                document.getElementById(labelInputId).value = '';
                msgEl.textContent = 'Eklendi.';
                msgEl.className = 'mt-1 text-xs text-green-600 dark:text-green-400';
            } else {
                msgEl.textContent = data.message || 'Eklenemedi.';
                msgEl.className = 'mt-1 text-xs text-red-600 dark:text-red-400';
            }
        }).catch(function() {
            msgEl.textContent = 'Bağlantı hatası.';
            msgEl.className = 'mt-1 text-xs text-red-600 dark:text-red-400';
        });
    }
    document.getElementById('modal-add-property-type').addEventListener('click', function() {
        addOption('property_type', 'modal-new-property-type', 'modal-property-type-msg', 'property_type');
    });
    document.getElementById('modal-add-listing-status').addEventListener('click', function() {
        addOption('listing_status', 'modal-new-listing-status', 'modal-listing-status-msg', 'listing_status');
    });
});
</script>

<?php include $rootPath . '/app/views/admin/snippets/footer.php'; ?>
