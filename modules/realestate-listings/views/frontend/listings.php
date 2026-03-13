<?php
/**
 * Real Estate Listings - İlanlar Listesi Sayfası
 * Modern, filtrelenebilir, kullanıcı dostu tasarım
 */

$baseListingsUrl = function_exists('localized_url') ? localized_url('/ilanlar') : site_url('/ilanlar');
$categorySlug = $filters['category_slug'] ?? '';
$formAction = $categorySlug !== '' ? (function_exists('localized_url') ? localized_url('/ilanlar/kategori/' . $categorySlug) : site_url('/ilanlar/kategori/' . $categorySlug)) : $baseListingsUrl;
$pageTitle = isset($current_category) && $current_category ? ($current_category['name'] . ' - ' . __('Emlak İlanları')) : __('Emlak İlanları');
// Header'ı render et
get_header([
    'title' => $pageTitle . ' - ' . get_option('site_name', ''),
    'meta_description' => __('Aradığınız emlağı kolayca bulun. Daireler, villalar, müstakil evler ve daha fazlası.')
]);

// Property type and listing status labels (from module settings)
$propertyTypeLabels = $property_type_labels ?? [
    'house' => __('Müstakil Ev'),
    'apartment' => __('Daire'),
    'villa' => __('Villa'),
    'commercial' => __('Ticari'),
    'land' => __('Arsa')
];
$statusLabels = $listing_status_labels ?? [
    'sale' => __('Satılık'),
    'rent' => __('Kiralık')
];

// selected() helper
if (!function_exists('selected')) {
    function selected($value1, $value2) {
        return ($value1 == $value2) ? 'selected' : '';
    }
}

// Pagination ayarları
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 12;
$totalListings = count($listings ?? []);
?>
    <!-- Page Header -->
    <section class="bg-gradient-to-br from-blue-600 to-blue-700 text-white py-12 lg:py-16">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="max-w-4xl">
                <h1 class="text-4xl lg:text-5xl font-bold mb-4 text-white">
                    <?php echo esc_html($pageTitle); ?>
                </h1>
                <p class="text-xl text-blue-100">
                    <?php echo esc_html(__('Hayalinizdeki emlağı bulun')); ?>
                </p>
                <?php if (!empty($all_categories)): ?>
                <div class="flex flex-wrap gap-2 mt-4">
                    <a href="<?php echo esc_url($baseListingsUrl); ?>" class="px-4 py-2 rounded-full text-sm font-medium <?php echo $categorySlug === '' ? 'bg-white text-blue-700' : 'bg-white/20 text-white hover:bg-white/30'; ?> transition-colors">
                        <?php echo esc_html(__('Tümü')); ?>
                    </a>
                    <?php foreach ($all_categories as $cat): ?>
                    <a href="<?php echo esc_url((function_exists('localized_url') ? localized_url('/ilanlar/kategori/' . $cat['slug']) : site_url('/ilanlar/kategori/' . $cat['slug']))); ?>" class="px-4 py-2 rounded-full text-sm font-medium <?php echo ($categorySlug === $cat['slug']) ? 'bg-white text-blue-700' : 'bg-white/20 text-white hover:bg-white/30'; ?> transition-colors">
                        <?php echo esc_html($cat['name']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Filters Section -->
    <section class="py-8 bg-white shadow-md sticky top-0 z-40">
        <div class="container mx-auto px-4 lg:px-6">
            <form action="<?php echo esc_url($formAction); ?>" 
                  method="get" 
                  class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <?php if (!empty($filters['realtor'])): ?>
                <input type="hidden" name="realtor" value="<?php echo (int) $filters['realtor']; ?>">
                <?php endif; ?>
                
                <!-- Konum -->
                <div>
                    <label for="location" class="block text-sm font-semibold text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline-block mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        <?php echo esc_html(__('Konum')); ?>
                    </label>
                    <input type="text" 
                           id="location"
                           name="location" 
                           value="<?php echo esc_attr($filters['location'] ?? ''); ?>" 
                           placeholder="<?php echo esc_attr(__('Şehir, semt...')); ?>" 
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>

                <!-- Emlak Tipi -->
                <div>
                    <label for="type" class="block text-sm font-semibold text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline-block mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <?php echo esc_html(__('Emlak Tipi')); ?>
                    </label>
                    <select id="type" name="type" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-white">
                        <option value=""><?php echo esc_html(__('Tüm Tipler')); ?></option>
                        <?php foreach ($propertyTypeLabels as $key => $label): ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php echo selected($filters['type'] ?? '', $key); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Durum (Satılık/Kiralık) -->
                <div>
                    <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline-block mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <?php echo esc_html(__('Durum')); ?>
                    </label>
                    <select id="status" name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-white">
                        <option value=""><?php echo esc_html(__('Hepsi')); ?></option>
                        <?php foreach ($statusLabels as $key => $label): ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php echo selected($filters['status'] ?? '', $key); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Fiyat Aralığı -->
                <div>
                    <label for="price_range" class="block text-sm font-semibold text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline-block mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <?php echo esc_html(__('Fiyat')); ?>
                    </label>
                    <select id="price_range" name="price_range" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-white">
                        <option value=""><?php echo esc_html(__('Tüm Fiyatlar')); ?></option>
                        <option value="0-500000" <?php echo selected($filters['price_range'] ?? '', '0-500000'); ?>>₺0 - ₺500.000</option>
                        <option value="500000-1000000" <?php echo selected($filters['price_range'] ?? '', '500000-1000000'); ?>>₺500.000 - ₺1.000.000</option>
                        <option value="1000000-2000000" <?php echo selected($filters['price_range'] ?? '', '1000000-2000000'); ?>>₺1.000.000 - ₺2.000.000</option>
                        <option value="2000000-5000000" <?php echo selected($filters['price_range'] ?? '', '2000000-5000000'); ?>>₺2.000.000 - ₺5.000.000</option>
                        <option value="5000000+" <?php echo selected($filters['price_range'] ?? '', '5000000+'); ?>>₺5.000.000+</option>
                    </select>
                </div>

                <!-- Ara Butonu -->
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full px-6 py-2.5 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <?php echo esc_html(__('Ara')); ?>
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Listings Grid -->
    <section class="py-8 lg:py-12">
        <div class="container mx-auto px-4 lg:px-6">
            
            <?php if (!empty($listings)): ?>
                <!-- Results Count -->
                <div class="mb-6 flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <p class="text-gray-600 text-lg">
                            <span class="font-bold text-gray-900"><?php echo number_format($totalListings, 0, ',', '.'); ?></span>
                            <?php echo esc_html(__('ilan bulundu')); ?>
                        </p>
                    </div>
                    
                    <!-- Sort Options (placeholder) -->
                    <div class="flex items-center gap-2">
                        <label for="sort" class="text-sm text-gray-600"><?php echo esc_html(__('Sırala:')); ?></label>
                        <select id="sort" name="sort" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="newest"><?php echo esc_html(__('En Yeni')); ?></option>
                            <option value="price_asc"><?php echo esc_html(__('Fiyat (Düşükten Yükseğe)')); ?></option>
                            <option value="price_desc"><?php echo esc_html(__('Fiyat (Yüksekten Düşüğe)')); ?></option>
                        </select>
                    </div>
                </div>

                <!-- Listings Grid -->
                <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 md:gap-6 lg:gap-8">
                    <?php foreach ($listings as $listing): 
                        $propertyType = $listing['property_type'] ?? 'house';
                        $propertyTypeLabel = $propertyTypeLabels[$propertyType] ?? ucfirst($propertyType);
                        $listingStatus = $listing['listing_status'] ?? 'sale';
                        $statusLabel = $listingStatus === 'rent' ? __('Kiralık') : __('Satılık');
                        $formattedPrice = '₺' . number_format($listing['price'] ?? 0, 0, ',', '.');
                        $slug = !empty($listing['slug']) ? $listing['slug'] : $listing['id'];
                        $detailUrl = function_exists('localized_url') ? localized_url('/ilan/' . $slug) : site_url('/ilan/' . $slug);
                    ?>
                    <!-- Listing Card -->
                    <article class="bg-white rounded-xl shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden group">
                        <!-- Image -->
                        <div class="relative overflow-hidden aspect-[4/3] min-h-0">
                            <a href="<?php echo esc_url($detailUrl); ?>">
                                <img src="<?php echo esc_url($listing['featured_image'] ?? 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800'); ?>" 
                                     alt="<?php echo esc_attr($listing['title']); ?>" 
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                     loading="lazy">
                            </a>
                            
                            <!-- Badges -->
                            <div class="absolute top-1.5 left-1.5 right-1.5 sm:top-3 sm:left-3 sm:right-3 flex items-start justify-between gap-1 sm:gap-2">
                                <div class="flex flex-col gap-1 sm:gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 sm:px-3 sm:py-1 bg-blue-600 text-white text-[10px] sm:text-xs font-bold rounded-full shadow-lg">
                                        <?php echo esc_html($statusLabel); ?>
                                    </span>
                                    <?php if (!empty($listing['is_featured'])): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 sm:px-3 sm:py-1 bg-amber-500 text-white text-[10px] sm:text-xs font-bold rounded-full shadow-lg">
                                        <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 mr-0.5 sm:mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        <?php echo esc_html(__('Öne Çıkan')); ?>
                                    </span>
                                    <?php endif; ?>
                                    <?php
                                    $listingBadges = isset($listing['badges']) && is_array($listing['badges']) ? $listing['badges'] : [];
                                    $badgeLabels = $badge_labels ?? [];
                                    $badgeDisplayFallback = ['firsat' => 'Fırsat', 'yatirimlik' => 'Yatırımlık', 'yeni' => 'Yeni', 'acil' => 'Acil'];
                                    foreach ($listingBadges as $bKey):
                                        $bLabel = $badgeDisplayFallback[$bKey] ?? (isset($badgeLabels[$bKey]) ? $badgeLabels[$bKey] : $bKey);
                                    ?><span class="inline-flex items-center px-2 py-0.5 sm:px-3 sm:py-1 bg-emerald-600 text-white text-[10px] sm:text-xs font-bold rounded-full shadow-lg"><?php echo esc_html($bLabel); ?></span><?php
                                    endforeach;
                                    ?>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 sm:px-3 sm:py-1 bg-white/95 backdrop-blur-sm text-gray-700 text-[10px] sm:text-xs font-semibold rounded-full shadow-md">
                                    <?php echo esc_html($propertyTypeLabel); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-3 sm:p-4 md:p-5">
                            <!-- Title -->
                            <h3 class="text-sm sm:text-base md:text-lg font-bold text-gray-900 mb-1 sm:mb-2 group-hover:text-blue-600 transition-colors line-clamp-2 min-h-[2.5rem] sm:min-h-[3.5rem]">
                                <a href="<?php echo esc_url($detailUrl); ?>">
                                    <?php echo esc_html($listing['title']); ?>
                                </a>
                            </h3>

                            <!-- Location -->
                            <p class="text-xs sm:text-sm text-gray-600 mb-2 sm:mb-4 flex items-center">
                                <svg class="w-4 h-4 mr-1.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="truncate"><?php echo esc_html($listing['location'] ?? __('Konum belirtilmemiş')); ?></span>
                            </p>

                            <!-- Features -->
                            <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs sm:text-sm text-gray-600 mb-2 sm:mb-4 pb-2 sm:pb-4 border-b border-gray-100">
                                <?php if (!empty($listing['bedrooms'])): ?>
                                <span class="flex items-center gap-1" title="<?php echo esc_attr(__('Yatak Odası')); ?>">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                    </svg>
                                    <span class="font-semibold"><?php echo esc_html($listing['bedrooms']); ?></span>
                                </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($listing['bathrooms'])): ?>
                                <span class="flex items-center gap-1" title="<?php echo esc_attr(__('Banyo')); ?>">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                    </svg>
                                    <span class="font-semibold"><?php echo esc_html($listing['bathrooms']); ?></span>
                                </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($listing['area'])): ?>
                                <span class="flex items-center gap-1" title="<?php echo esc_attr(__('Alan')); ?>">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5"/>
                                    </svg>
                                    <span class="font-semibold"><?php echo esc_html(number_format($listing['area'], 0, ',', '.')); ?> m²</span>
                                </span>
                                <?php endif; ?>
                            </div>

                            <!-- Price & CTA -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-base sm:text-xl md:text-2xl font-bold text-blue-600 truncate" title="<?php echo esc_attr($formattedPrice); ?>">
                                        <?php echo esc_html($formattedPrice); ?>
                                    </div>
                                </div>
                                <a href="<?php echo esc_url($detailUrl); ?>" 
                                   class="inline-flex items-center gap-1 sm:gap-2 px-2 py-1.5 sm:px-4 sm:py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all text-xs sm:text-sm font-semibold shadow-md hover:shadow-lg">
                                    <?php echo esc_html(__('Detay')); ?>
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <!-- No Results -->
                <div class="bg-white rounded-xl shadow-md p-12 lg:p-16 text-center max-w-2xl mx-auto">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">
                        <?php echo esc_html(__('İlan Bulunamadı')); ?>
                    </h3>
                    <p class="text-gray-600 mb-6">
                        <?php echo esc_html(__('Aradığınız kriterlere uygun ilan bulunamadı. Filtrelerinizi değiştirerek tekrar deneyebilirsiniz.')); ?>
                    </p>
                    <a href="<?php echo function_exists('localized_url') ? localized_url('/ilanlar') : site_url('/ilanlar'); ?>" 
                       class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all font-semibold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <?php echo esc_html(__('Tüm İlanları Gör')); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

<?php
// Footer'ı render et
get_footer();
