<?php
/**
 * Real Estate Listings - İlan Detay Sayfası
 * Yeniden yazıldı - Temiz, modüler, profesyonel
 */

// Gerekli verilerin kontrolü
if (!isset($listing) || empty($listing)) {
    die('İlan verisi bulunamadı');
}

// Header'ı render et
get_header([
    'title' => $listing['title'] ?? __('İlan Detayı'),
    'meta_description' => !empty($listing['description']) ? substr(strip_tags($listing['description']), 0, 160) : ''
]);

// Property type labels
$propertyTypeLabels = [
    'house' => __('Müstakil Ev'),
    'apartment' => __('Daire'),
    'villa' => __('Villa'),
    'commercial' => __('Ticari'),
    'land' => __('Arsa')
];

$propertyType = $listing['property_type'] ?? 'house';
$propertyTypeLabel = $propertyTypeLabels[$propertyType] ?? ucfirst($propertyType);

// Listing status
$listingStatus = $listing['listing_status'] ?? 'sale';
$statusLabel = $listingStatus === 'rent' ? __('Kiralık') : __('Satılık');

// Format price
$formattedPrice = '₺' . number_format($listing['price'] ?? 0, 0, ',', '.');

// Format area
$formattedArea = !empty($listing['area']) ? number_format($listing['area'], 0, ',', '.') . ' m²' : '';

// Realtor information
$realtorName = '';
$realtorEmail = '';
$realtorPhone = '';
$realtorPhoto = '';
$realtorBio = '';
$realtorSlug = '';
if (!empty($listing['realtor_id']) && !empty($listing['realtor_first_name'])) {
    $realtorName = trim(($listing['realtor_first_name'] ?? '') . ' ' . ($listing['realtor_last_name'] ?? ''));
    $realtorEmail = $listing['realtor_email'] ?? '';
    $realtorPhone = $listing['realtor_phone'] ?? '';
    $realtorPhoto = normalize_image_url($listing['realtor_photo'] ?? '');
    $realtorBio = $listing['realtor_bio'] ?? '';
    $realtorSlug = $listing['realtor_slug'] ?? '';
}

// Helper function to normalize image URLs
function normalize_image_url($url) {
    if (empty($url)) {
        return '';
    }
    
    // If already absolute URL, return as is
    if (preg_match('/^https?:\/\//', $url)) {
        return $url;
    }
    
    // Remove leading slash if present (site_url handles it)
    $path = ltrim($url, '/');
    
    // If path doesn't start with 'uploads/', add it
    if (strpos($path, 'uploads/') !== 0) {
        $path = 'uploads/' . $path;
    }
    
    // Convert to absolute URL
    return function_exists('site_url') ? site_url($path) : '/' . $path;
}

// Gallery images
$galleryImages = [];
if (!empty($listing['gallery'])) {
    $galleryData = is_string($listing['gallery']) ? json_decode($listing['gallery'], true) : $listing['gallery'];
    if (is_array($galleryData)) {
        $galleryImages = $galleryData;
    }
}

// All images (featured + gallery)
$allImages = [];
if (!empty($listing['featured_image'])) {
    $normalizedFeaturedImage = normalize_image_url($listing['featured_image']);
    if (!empty($normalizedFeaturedImage)) {
        $allImages[] = $normalizedFeaturedImage;
    }
}
foreach ($galleryImages as $img) {
    if (!empty($img)) {
        $normalizedImg = normalize_image_url($img);
        if (!empty($normalizedImg) && !in_array($normalizedImg, $allImages)) {
            $allImages[] = $normalizedImg;
        }
    }
}

// Similar listings
$similarListings = [];
if (class_exists('RealEstateListingsModel')) {
    try {
        $model = new RealEstateListingsModel();
        $allPublished = $model->getPublished('', $propertyType, '', 4, 0);
        foreach ($allPublished as $similar) {
            if ($similar['id'] != $listing['id'] && count($similarListings) < 3) {
                $similarListings[] = $similar;
            }
        }
    } catch (Exception $e) {
        error_log('Similar listings error: ' . $e->getMessage());
    }
}

// Increment views
if (class_exists('RealEstateListingsModel')) {
    try {
        $model = new RealEstateListingsModel();
        $model->incrementViews($listing['id']);
    } catch (Exception $e) {
        error_log('View increment error: ' . $e->getMessage());
    }
}
?>

<!-- İlan Detay Sayfası -->
<section class="py-4 sm:py-6 lg:py-12 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-6 max-w-7xl">
        <!-- Breadcrumb -->
            <nav class="flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm text-gray-600 mb-4 sm:mb-6 lg:mb-8 flex-wrap" aria-label="Breadcrumb">
            <a href="<?php echo function_exists('localized_url') ? localized_url('/') : site_url('/'); ?>" 
                   class="hover:text-blue-600 transition-colors">
                <?php echo esc_html(__('Ana Sayfa')); ?>
            </a>
                <span class="text-gray-400">/</span>
            <a href="<?php echo function_exists('localized_url') ? localized_url('/ilanlar') : site_url('/ilanlar'); ?>" 
                   class="hover:text-blue-600 transition-colors">
                <?php echo esc_html(__('İlanlar')); ?>
            </a>
                <span class="text-gray-400">/</span>
                <span class="text-gray-900 font-medium truncate max-w-[200px] sm:max-w-none"><?php echo esc_html($listing['title']); ?></span>
        </nav>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8">
                <!-- Ana İçerik -->
                <div class="lg:col-span-2 space-y-4 sm:space-y-6 w-full max-w-full overflow-hidden">
                    
                    <!-- Görsel Galerisi -->
                <?php if (!empty($allImages)): ?>
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden w-full max-w-full" style="box-sizing: border-box;">
                    <?php if (count($allImages) === 1): ?>
                            <!-- Tek görsel -->
                            <div class="relative w-full max-w-full aspect-[4/3] sm:aspect-[16/9] overflow-hidden cursor-pointer" onclick="openLightbox(0)" style="box-sizing: border-box;">
                                <img src="<?php echo esc_url($allImages[0]); ?>" 
                                     alt="<?php echo esc_attr($listing['title']); ?>" 
                                     class="w-full h-full max-w-full max-h-full object-cover"
                                     style="display: block; width: 100%; height: 100%; max-width: 100%; max-height: 100%; object-fit: cover; box-sizing: border-box;">
                            </div>
                        <?php else: ?>
                            <!-- Çoklu görsel - Carousel Galeri -->
                            <div class="relative w-full" style="box-sizing: border-box;">
                                <!-- Ana Carousel Container -->
                                <div class="relative w-full aspect-[4/3] sm:aspect-[16/9] overflow-hidden group cursor-grab active:cursor-grabbing" id="gallery-carousel">
                                    <!-- Carousel Wrapper - Genişlik JS ile ayarlanacak -->
                                    <div class="h-full" id="carousel-wrapper" style="display: flex; transform: translateX(0); transition: transform 0.4s ease-out;">
                                        <?php foreach ($allImages as $index => $img): ?>
                                        <div class="carousel-slide h-full flex-shrink-0" data-index="<?php echo $index; ?>">
                                            <img src="<?php echo esc_url($img); ?>" 
                                                 alt="<?php echo esc_attr($listing['title'] . ' - ' . ($index + 1)); ?>" 
                                                 class="w-full h-full object-cover cursor-pointer"
                                                 onclick="openLightbox(<?php echo $index; ?>)"
                                                 loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>">
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <!-- Navigasyon Butonları -->
                                    <button type="button" 
                                            onclick="previousCarouselImage()" 
                                            class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 active:bg-black/80 text-white rounded-full p-2 sm:p-3 transition-all z-10 opacity-100 sm:opacity-0 sm:group-hover:opacity-100"
                                            id="carousel-prev">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                        </svg>
                                    </button>
                                    
                                    <button type="button" 
                                            onclick="nextCarouselImage()" 
                                            class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 active:bg-black/80 text-white rounded-full p-2 sm:p-3 transition-all z-10 opacity-100 sm:opacity-0 sm:group-hover:opacity-100"
                                            id="carousel-next">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                    
                                    <!-- Görsel sayacı -->
                                    <div id="main-image-counter" class="absolute top-2 right-2 sm:top-4 sm:right-4 bg-black/70 text-white px-2 py-1 sm:px-3 sm:py-1 rounded-full text-xs sm:text-sm font-semibold z-10">
                                        1 / <?php echo count($allImages); ?>
                                    </div>
                                    
                                    <!-- Carousel Indicators (Dots) -->
                                    <div class="absolute bottom-2 sm:bottom-4 left-1/2 -translate-x-1/2 flex gap-1.5 sm:gap-2 z-10 flex-wrap justify-center max-w-full px-2">
                                        <?php foreach ($allImages as $index => $img): ?>
                                        <button type="button"
                                                onclick="goToCarouselImage(<?php echo $index; ?>)"
                                                class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full transition-all <?php echo $index === 0 ? 'bg-white w-4 sm:w-6' : 'bg-white/50 active:bg-white/75'; ?>"
                                                id="carousel-dot-<?php echo $index; ?>"
                                                aria-label="Görsel <?php echo $index + 1; ?>">
                                        </button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                    <!-- İlan Başlığı ve Özellikler -->
                    <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6 lg:p-8">
                        <!-- Başlık ve Etiketler -->
                        <div class="mb-4 sm:mb-6">
                            <div class="flex items-center gap-2 mb-3 sm:mb-4 flex-wrap">
                                <span class="inline-flex items-center px-2.5 py-1 sm:px-3 sm:py-1 rounded-full text-xs sm:text-sm font-semibold bg-blue-100 text-blue-800">
                                    <?php echo esc_html($statusLabel); ?>
                                </span>
                                <?php if (!empty($listing['is_featured'])): ?>
                                <span class="inline-flex items-center px-2.5 py-1 sm:px-3 sm:py-1 rounded-full text-xs sm:text-sm font-semibold bg-amber-100 text-amber-800">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <?php echo esc_html(__('Öne Çıkan')); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            
                            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 mb-3 sm:mb-4 leading-tight">
                                <?php echo esc_html($listing['title']); ?>
                            </h1>
                            
                            <div class="flex items-center text-gray-600">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1.5 sm:mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="text-base sm:text-lg"><?php echo esc_html($listing['location'] ?? ''); ?></span>
                            </div>
                        </div>

                        <!-- Fiyat -->
                        <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-blue-50 rounded-lg">
                            <div class="text-xs sm:text-sm text-gray-600 mb-1"><?php echo esc_html(__('Fiyat')); ?></div>
                            <div class="text-2xl sm:text-3xl lg:text-4xl font-bold text-blue-600">
                                <?php echo esc_html($formattedPrice); ?>
                            </div>
                            <?php if (!empty($formattedArea)): ?>
                            <div class="text-xs sm:text-sm text-gray-600 mt-1">
                                <?php echo esc_html($formattedArea); ?>
                            </div>
                            <?php endif; ?>
                    </div>

                        <!-- Özellikler Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 pt-4 sm:pt-6 border-t border-gray-200">
                        <?php if (!empty($listing['bedrooms'])): ?>
                            <div class="text-center p-3 sm:p-4 bg-gray-50 rounded-lg">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-2 flex items-center justify-center rounded-full bg-blue-100">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                </div>
                                <div class="text-xl sm:text-2xl font-bold text-gray-900"><?php echo esc_html($listing['bedrooms']); ?></div>
                                <div class="text-xs sm:text-sm text-gray-600 mt-1"><?php echo esc_html(__('Yatak Odası')); ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($listing['bathrooms'])): ?>
                            <div class="text-center p-3 sm:p-4 bg-gray-50 rounded-lg">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-2 flex items-center justify-center rounded-full bg-blue-100">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                </svg>
                            </div>
                                <div class="text-xl sm:text-2xl font-bold text-gray-900"><?php echo esc_html($listing['bathrooms']); ?></div>
                                <div class="text-xs sm:text-sm text-gray-600 mt-1"><?php echo esc_html(__('Banyo')); ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($listing['area'])): ?>
                            <div class="text-center p-3 sm:p-4 bg-gray-50 rounded-lg">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-2 flex items-center justify-center rounded-full bg-blue-100">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                                </svg>
                                </div>
                                <div class="text-xl sm:text-2xl font-bold text-gray-900"><?php echo esc_html(number_format($listing['area'], 0, ',', '.')); ?></div>
                                <div class="text-xs sm:text-sm text-gray-600 mt-1">m²</div>
                        </div>
                        <?php endif; ?>

                            <div class="text-center p-3 sm:p-4 bg-gray-50 rounded-lg">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-2 flex items-center justify-center rounded-full bg-blue-100">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                </div>
                                <div class="text-lg sm:text-xl font-bold text-gray-900"><?php echo esc_html($propertyTypeLabel); ?></div>
                                <div class="text-xs sm:text-sm text-gray-600 mt-1"><?php echo esc_html(__('Tip')); ?></div>
                            </div>
                    </div>
                </div>

                    <!-- Açıklama -->
                <?php if (!empty($listing['description'])): ?>
                    <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6 lg:p-8">
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-3 sm:mb-4 flex items-center">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 mr-2 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <?php echo esc_html(__('Açıklama')); ?>
                        </h2>
                        <div class="prose prose-sm sm:prose-lg max-w-none text-gray-700 leading-relaxed whitespace-pre-line text-sm sm:text-base">
                        <?php echo nl2br(esc_html($listing['description'])); ?>
                    </div>
                </div>
                <?php endif; ?>

                    <!-- Benzer İlanlar -->
                <?php if (!empty($similarListings)): ?>
                    <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6 lg:p-8">
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 sm:mb-6 flex items-center">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 mr-2 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            <?php echo esc_html(__('Benzer İlanlar')); ?>
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
                        <?php foreach ($similarListings as $similar): 
                                $similarPrice = '₺' . number_format($similar['price'] ?? 0, 0, ',', '.');
                                $similarType = $propertyTypeLabels[$similar['property_type'] ?? 'house'] ?? __('Emlak');
                                $similarSlug = !empty($similar['slug']) ? $similar['slug'] : $similar['id'];
                                $similarUrl = function_exists('localized_url') ? localized_url('/ilan/' . $similarSlug) : site_url('/ilan/' . $similarSlug);
                            ?>
                            <a href="<?php echo esc_url($similarUrl); ?>" 
                               class="group block bg-gray-50 rounded-lg overflow-hidden hover:shadow-lg transition-all duration-300">
                                <div class="relative aspect-[4/3] overflow-hidden">
                                    <img src="<?php echo esc_url(!empty($similar['featured_image']) ? normalize_image_url($similar['featured_image']) : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=400'); ?>" 
                                         alt="<?php echo esc_attr($similar['title']); ?>" 
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                </div>
                                <div class="p-4">
                                    <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
                                        <?php echo esc_html($similar['title']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-600 mb-3 line-clamp-1 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        </svg>
                                        <?php echo esc_html($similar['location'] ?? ''); ?>
                                    </p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-lg font-bold text-blue-600"><?php echo esc_html($similarPrice); ?></span>
                                        <span class="text-xs text-gray-500 bg-gray-200 px-2 py-1 rounded"><?php echo esc_html($similarType); ?></span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <div class="sticky top-4 sm:top-6 space-y-4 sm:space-y-6">
                        <!-- İletişim Kartı -->
                        <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6">
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-3 sm:mb-4 flex items-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <?php echo esc_html(__('İletişim')); ?>
                            </h3>
                            
                            <?php if (!empty($realtorName)): ?>
                            <!-- Emlakçı Bilgisi -->
                            <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-100">
                                <div class="flex items-center gap-3 mb-3">
                                    <?php if (!empty($realtorPhoto)): ?>
                                    <img src="<?php echo esc_url($realtorPhoto); ?>" 
                                         alt="<?php echo esc_attr($realtorName); ?>" 
                                         class="w-12 h-12 rounded-full object-cover flex-shrink-0">
                                    <?php else: ?>
                                    <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <?php endif; ?>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-xs text-gray-500 mb-0.5"><?php echo esc_html(__('Emlakçı')); ?></div>
                                        <?php if (!empty($realtorSlug)): ?>
                                            <?php 
                                            $realtorUrl = function_exists('localized_url') ? localized_url('/danisman/' . $realtorSlug) : site_url('/danisman/' . $realtorSlug);
                                            ?>
                                            <a href="<?php echo esc_url($realtorUrl); ?>" 
                                               class="text-sm font-semibold text-gray-900 hover:text-blue-600 transition-colors">
                                                <?php echo esc_html($realtorName); ?>
                                            </a>
                                        <?php else: ?>
                                            <div class="text-sm font-semibold text-gray-900"><?php echo esc_html($realtorName); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if (!empty($realtorEmail)): ?>
                                <a href="mailto:<?php echo esc_attr($realtorEmail); ?>" 
                                   class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 transition-colors mb-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <?php echo esc_html($realtorEmail); ?>
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($realtorPhone)): ?>
                                <a href="tel:<?php echo esc_attr($realtorPhone); ?>" 
                                   class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    <?php echo esc_html($realtorPhone); ?>
                                </a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="space-y-3 mb-6">
                                <?php 
                                $sitePhone = get_option('site_phone', '') ?: get_option('company_phone', '');
                                $siteEmail = get_option('site_email', '') ?: get_option('company_email', '');
                                ?>
                                
                                <?php if ($sitePhone): ?>
                                <a href="tel:<?php echo esc_attr($sitePhone); ?>" 
                                   class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-blue-50 transition-colors group">
                                    <div class="w-10 h-10 bg-blue-100 group-hover:bg-blue-200 rounded-full flex items-center justify-center flex-shrink-0 transition-colors">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs text-gray-500 mb-0.5"><?php echo esc_html(__('Telefon')); ?></div>
                                        <div class="text-sm font-medium text-gray-900 group-hover:text-blue-600"><?php echo esc_html($sitePhone); ?></div>
                            </div>
                        </a>
                                <?php endif; ?>

                                <?php if ($siteEmail): ?>
                                <a href="mailto:<?php echo esc_attr($siteEmail); ?>" 
                                   class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-blue-50 transition-colors group">
                                    <div class="w-10 h-10 bg-blue-100 group-hover:bg-blue-200 rounded-full flex items-center justify-center flex-shrink-0 transition-colors">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs text-gray-500 mb-0.5"><?php echo esc_html(__('E-posta')); ?></div>
                                        <div class="text-sm font-medium text-gray-900 truncate group-hover:text-blue-600"><?php echo esc_html($siteEmail); ?></div>
                                    </div>
                                </a>
                                <?php endif; ?>
                    </div>

                    <a href="<?php echo function_exists('localized_url') ? localized_url('/contact') : site_url('/contact'); ?>" 
                               class="block w-full px-6 py-3 bg-blue-600 text-white text-center rounded-lg font-semibold hover:bg-blue-700 transition-colors mb-3">
                        <?php echo esc_html(__('İletişime Geç')); ?>
                    </a>

                    <button onclick="shareProperty()" 
                                    class="w-full px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:border-blue-600 hover:text-blue-600 transition-colors flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                                </svg>
                        <?php echo esc_html(__('Paylaş')); ?>
                    </button>

                            <!-- İlan Bilgileri -->
                    <div class="mt-6 pt-6 border-t border-gray-200 space-y-4">
                        <?php if (!empty($listing['views'])): ?>
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                                    <div class="flex-1">
                                <div class="text-xs text-gray-500 mb-0.5"><?php echo esc_html(__('Görüntülenme')); ?></div>
                                        <div class="text-sm font-medium text-gray-900"><?php echo esc_html(number_format($listing['views'], 0, ',', '.')); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <div class="flex-1">
                                        <div class="text-xs text-gray-500 mb-0.5"><?php echo esc_html(__('İlan No')); ?></div>
                                        <div class="text-sm font-medium text-gray-900"><?php echo esc_html($listing['id']); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<!-- Lightbox Modal -->
<?php if (!empty($allImages) && count($allImages) > 1): ?>
<div id="gallery-lightbox" class="fixed inset-0 bg-black/95 z-50 hidden items-center justify-center" onclick="closeLightbox()">
    <div class="relative w-full h-full flex items-center justify-center p-4" onclick="event.stopPropagation()">
        <!-- Close Button -->
        <button onclick="closeLightbox()" class="absolute top-4 right-4 z-10 text-white hover:text-gray-300 transition-colors">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        
        <!-- Previous Button -->
        <button onclick="previousImage()" class="absolute left-4 z-10 text-white hover:text-gray-300 transition-colors bg-black/50 rounded-full p-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        
        <!-- Next Button -->
        <button onclick="nextImage()" class="absolute right-4 z-10 text-white hover:text-gray-300 transition-colors bg-black/50 rounded-full p-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
        
        <!-- Main Image -->
        <img id="lightbox-image" src="" alt="" class="max-w-full max-h-full object-contain transition-opacity duration-150 ease-in-out" style="opacity: 1;">
        
        <!-- Image Counter -->
        <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black/70 text-white px-4 py-2 rounded-full text-sm font-semibold">
            <span id="lightbox-counter">1</span> / <?php echo count($allImages); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// ============================================
// GALLERY CAROUSEL - Basit ve Çalışan Versiyon
// ============================================

const galleryImages = <?php echo json_encode($allImages ?? []); ?>;
let currentIndex = 0;

// Carousel'e git
function goToSlide(index) {
    const carousel = document.getElementById('gallery-carousel');
    const wrapper = document.getElementById('carousel-wrapper');
    const counter = document.getElementById('main-image-counter');
    
    if (!carousel || !wrapper || galleryImages.length === 0) return;
    
    // Index sınırları
    if (index < 0) index = galleryImages.length - 1;
    if (index >= galleryImages.length) index = 0;
    currentIndex = index;
    
    // Carousel genişliğini al
    const slideWidth = carousel.offsetWidth;
    
    // Transform uygula
    wrapper.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
    
    // Sayaç güncelle
    if (counter) {
        counter.textContent = `${currentIndex + 1} / ${galleryImages.length}`;
    }
    
    // Dot'ları güncelle
    document.querySelectorAll('[id^="carousel-dot-"]').forEach((dot, i) => {
        if (i === currentIndex) {
            dot.classList.remove('bg-white/50', 'w-1.5', 'sm:w-2');
            dot.classList.add('bg-white', 'w-4', 'sm:w-6');
        } else {
            dot.classList.remove('bg-white', 'w-4', 'sm:w-6');
            dot.classList.add('bg-white/50', 'w-1.5', 'sm:w-2');
        }
    });
}

// Carousel boyutlarını ayarla
function setupCarousel() {
    const carousel = document.getElementById('gallery-carousel');
    const wrapper = document.getElementById('carousel-wrapper');
    
    if (!carousel || !wrapper || galleryImages.length === 0) return;
    
    const slideWidth = carousel.offsetWidth;
    
    // Eğer genişlik 0 ise, biraz bekleyip tekrar dene
    if (slideWidth <= 0) {
        setTimeout(setupCarousel, 50);
        return;
    }
    
    const slides = wrapper.querySelectorAll('.carousel-slide');
    
    // Wrapper genişliği
    wrapper.style.width = `${slides.length * slideWidth}px`;
    
    // Her slide genişliği
    slides.forEach(slide => {
        slide.style.width = `${slideWidth}px`;
    });
    
    // Mevcut pozisyonu güncelle (transition olmadan)
    wrapper.style.transition = 'none';
    goToSlide(currentIndex);
    
    // Transition'ı geri aç
    requestAnimationFrame(() => {
        wrapper.style.transition = 'transform 0.4s ease-out';
    });
}

// Navigasyon
function nextCarouselImage() {
    goToSlide(currentIndex + 1);
}

function previousCarouselImage() {
    goToSlide(currentIndex - 1);
}

function goToCarouselImage(index) {
    goToSlide(index);
}

// Başlat
document.addEventListener('DOMContentLoaded', () => {
    if (galleryImages.length <= 1) return;
    
    const carousel = document.getElementById('gallery-carousel');
    if (!carousel) return;
    
    // İlk kurulum - biraz bekle ki layout hazır olsun
    requestAnimationFrame(() => {
        setupCarousel();
    });
    
    // Touch/Swipe desteği
    let touchStartX = 0;
    carousel.addEventListener('touchstart', e => {
        touchStartX = e.touches[0].clientX;
    }, { passive: true });
    
    carousel.addEventListener('touchend', e => {
        const diff = touchStartX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) {
            if (diff > 0) nextCarouselImage();
            else previousCarouselImage();
        }
    }, { passive: true });
    
    // Mouse drag
    let isDragging = false, startX = 0;
    carousel.addEventListener('mousedown', e => {
        isDragging = true;
        startX = e.clientX;
        e.preventDefault();
    });
    
    document.addEventListener('mouseup', e => {
        if (!isDragging) return;
        isDragging = false;
        const diff = startX - e.clientX;
        if (Math.abs(diff) > 50) {
            if (diff > 0) nextCarouselImage();
            else previousCarouselImage();
        }
    });
    
    // Resize
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(setupCarousel, 100);
    });
    
    // Görsel sürükleme engelle
    carousel.querySelectorAll('img').forEach(img => {
        img.ondragstart = () => false;
    });
});

// ============================================
// LIGHTBOX FUNCTIONS
// ============================================

function openLightbox(index) {
    if (!galleryImages || galleryImages.length === 0) return;
    
    // Index'i ayarla
    currentIndex = index !== undefined ? index : currentIndex;
    if (currentIndex < 0) currentIndex = 0;
    if (currentIndex >= galleryImages.length) currentIndex = galleryImages.length - 1;
    
    // Carousel'i güncelle
    goToSlide(currentIndex);
    
    const lightbox = document.getElementById('gallery-lightbox');
    const lightboxImage = document.getElementById('lightbox-image');
    const lightboxCounter = document.getElementById('lightbox-counter');
    
    if (!lightbox || !lightboxImage) return;
    
    // Görseli ayarla
    lightboxImage.src = galleryImages[currentIndex];
    lightboxImage.alt = '<?php echo esc_js($listing['title']); ?> - ' + (currentIndex + 1);
    lightboxImage.style.opacity = '1';
    
    // Sayaç güncelle
    if (lightboxCounter) {
        lightboxCounter.textContent = currentIndex + 1;
    }
    
    // Lightbox'ı göster
    lightbox.classList.remove('hidden');
    lightbox.classList.add('flex');
    document.body.style.overflow = 'hidden';
    document.body.style.position = 'fixed';
    document.body.style.width = '100%';
}

function closeLightbox() {
    const lightbox = document.getElementById('gallery-lightbox');
    if (lightbox) {
        lightbox.classList.add('hidden');
        lightbox.classList.remove('flex');
        document.body.style.overflow = '';
        document.body.style.position = '';
        document.body.style.width = '';
    }
}

function nextImage(e) {
    if (e) e.stopPropagation();
    if (!galleryImages || galleryImages.length === 0) return;
    currentIndex = (currentIndex + 1) % galleryImages.length;
    updateLightboxImage();
}

function previousImage(e) {
    if (e) e.stopPropagation();
    if (!galleryImages || galleryImages.length === 0) return;
    currentIndex = (currentIndex - 1 + galleryImages.length) % galleryImages.length;
    updateLightboxImage();
}

function updateLightboxImage() {
    const lightboxImage = document.getElementById('lightbox-image');
    const lightboxCounter = document.getElementById('lightbox-counter');
    
    if (!lightboxImage) return;
    
    // Sayaç güncelle (sadece mevcut sayı, toplam HTML'de zaten var)
    if (lightboxCounter) {
        lightboxCounter.textContent = currentIndex + 1;
    }
    
    // Fade out
    lightboxImage.style.opacity = '0';
    
    // Görseli değiştir
    requestAnimationFrame(() => {
        lightboxImage.src = galleryImages[currentIndex];
        lightboxImage.alt = '<?php echo esc_js($listing['title']); ?> - ' + (currentIndex + 1);
        
        if (lightboxImage.complete) {
            requestAnimationFrame(() => {
                lightboxImage.style.opacity = '1';
            });
        } else {
            lightboxImage.onload = function() {
                requestAnimationFrame(() => {
                    lightboxImage.style.opacity = '1';
                });
                lightboxImage.onload = null;
            };
        }
    });
    
    // Carousel'i senkronize et
    goToSlide(currentIndex);
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    const lightbox = document.getElementById('gallery-lightbox');
    const carousel = document.getElementById('gallery-carousel');
    
    // Lightbox keyboard navigation
    if (lightbox && !lightbox.classList.contains('hidden')) {
        if (e.key === 'Escape') {
            closeLightbox();
        } else if (e.key === 'ArrowRight') {
            nextImage();
        } else if (e.key === 'ArrowLeft') {
            previousImage();
        }
    }
    // Carousel keyboard navigation (when lightbox is closed)
    else if (carousel && (!lightbox || lightbox.classList.contains('hidden'))) {
        if (e.key === 'ArrowRight') {
            e.preventDefault();
            nextCarouselImage();
        } else if (e.key === 'ArrowLeft') {
            e.preventDefault();
            previousCarouselImage();
        }
    }
});

// Share property function
function shareProperty() {
    const url = window.location.href;
    const title = <?php echo json_encode($listing['title']); ?>;
    
    if (navigator.share) {
        navigator.share({
            title: title,
            text: title + ' - ' + <?php echo json_encode($listing['location'] ?? ''); ?>,
            url: url
        }).catch(err => {});
    } else {
        // Fallback: Copy to clipboard
        navigator.clipboard.writeText(url).then(() => {
            alert(<?php echo json_encode(__('Link panoya kopyalandı!')); ?>);
        }).catch(() => {
            prompt(<?php echo json_encode(__('Bu linki paylaşın:')); ?>, url);
        });
    }
}
</script>

<?php
// Footer'ı render et
get_footer();
