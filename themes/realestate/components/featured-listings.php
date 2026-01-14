<?php
/**
 * Real Estate Theme - Featured Listings Grid
 */

$section = $section ?? [];
$settings = $section['settings'] ?? [];

$sectionTitle = !empty($section['title']) ? $section['title'] : __('Öne Çıkan İlanlar');
$sectionSubtitle = !empty($section['subtitle']) ? $section['subtitle'] : __('Özenle seçilmiş premium mülk koleksiyonumuzu keşfedin');

// Buton renkleri - Tema renk paletinden al
$primaryButtonBg = '#1e40af';
$primaryButtonTextColor = '#ffffff';

// ThemeLoader'dan tema renklerini al
if (class_exists('ThemeLoader')) {
    $themeLoaderInstance = ThemeLoader::getInstance();
    $primaryButtonBg = $themeLoaderInstance->getColor('primary', '#1e40af');
    $primaryButtonTextColor = '#ffffff';
}

// Get listings from module
$listings = [];
$limit = !empty($settings['limit']) ? intval($settings['limit']) : 6;

// Load Database and Model classes
$databasePath = __DIR__ . '/../../../core/Database.php';
$modelPath = __DIR__ . '/../modules/realestate-listings/Model.php';

if (file_exists($databasePath) && file_exists($modelPath)) {
    if (!class_exists('Database')) {
        require_once $databasePath;
    }
    require_once $modelPath;
    
    if (class_exists('RealEstateListingsModel')) {
        try {
            $model = new RealEstateListingsModel();
            $rawListings = $model->getFeatured($limit);
            
            // Format listings data
            foreach ($rawListings as $listing) {
                $listings[] = [
                    'id' => $listing['id'],
                    'slug' => $listing['slug'] ?? '',
                    'title' => $listing['title'],
                    'location' => $listing['location'],
                    'price' => function_exists('realestate_format_price') ? realestate_format_price($listing['price']) : '₺' . number_format($listing['price'], 0, '.', '.'),
                    'image' => !empty($listing['featured_image']) ? $listing['featured_image'] : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800',
                    'bedrooms' => intval($listing['bedrooms'] ?? 0),
                    'bathrooms' => intval($listing['bathrooms'] ?? 0),
                    'living_rooms' => intval($listing['living_rooms'] ?? 0),
                    'rooms' => intval($listing['rooms'] ?? 0),
                    'area' => !empty($listing['area']) ? number_format($listing['area'], 0, '.', '.') . ' m²' : '',
                    'type' => $listing['property_type'] ?? 'house',
                    'listing_status' => $listing['listing_status'] ?? 'sale'
                ];
            }
        } catch (Exception $e) {
            error_log('Featured listings error: ' . $e->getMessage());
        }
    }
}
?>

<section class="py-16 lg:py-24 bg-white">
    <div class="container mx-auto px-4 lg:px-6">
        <div class="text-center mb-12">
            <h2 class="text-3xl lg:text-4xl font-bold text-secondary mb-4"><?php echo esc_html($sectionTitle); ?></h2>
            <?php if (!empty($sectionSubtitle)): ?>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto"><?php echo esc_html($sectionSubtitle); ?></p>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            <?php foreach ($listings as $listing): 
                $slug = !empty($listing['slug']) ? $listing['slug'] : $listing['id'];
                $listingUrl = function_exists('localized_url') ? localized_url('/ilan/' . $slug) : site_url('/ilan/' . $slug);
            ?>
                <a href="<?php echo esc_url($listingUrl); ?>" class="bg-white rounded-lg shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 overflow-hidden group cursor-pointer block">
                    <div class="relative overflow-hidden">
                        <img src="<?php echo esc_url($listing['image'] ?? 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800'); ?>" 
                             alt="<?php echo esc_attr($listing['title']); ?>" 
                             class="w-full h-72 object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all duration-300"></div>
                        <div class="absolute top-4 left-4 z-10">
                            <?php 
                            $listingStatus = $listing['listing_status'] ?? 'sale';
                            $statusLabel = $listingStatus === 'rent' ? __('Kiralık') : __('Satılık');
                            ?>
                            <span style="background-color: <?php echo esc_attr($primaryButtonBg); ?>; color: <?php echo esc_attr($primaryButtonTextColor); ?>;" class="px-4 py-1.5 text-sm font-semibold rounded-full shadow-lg group-hover:scale-105 transition-transform duration-300">
                                <?php echo esc_html($statusLabel); ?>
                            </span>
                        </div>
                    </div>
                    <div class="p-5">
                        <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-primary transition-colors duration-300">
                            <?php echo esc_html($listing['title']); ?>
                        </h3>
                        <p class="text-gray-600 mb-4 flex items-center text-sm">
                            <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <?php echo esc_html($listing['location']); ?>
                        </p>
                        <div class="flex items-center gap-4 text-gray-600 mb-5 flex-wrap">
                            <span class="flex items-center text-sm">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                <?php echo esc_html($listing['bedrooms'] ?? 0); ?> <?php echo esc_html(__('Yatak')); ?>
                            </span>
                            <span class="flex items-center text-sm">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                </svg>
                                <?php echo esc_html($listing['bathrooms'] ?? 0); ?> <?php echo esc_html(__('Banyo')); ?>
                            </span>
                            <?php if (!empty($listing['living_rooms'])): ?>
                            <span class="flex items-center text-sm">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                <?php echo esc_html($listing['living_rooms']); ?> <?php echo esc_html(__('Salon')); ?>
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($listing['rooms'])): ?>
                            <span class="flex items-center text-sm">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                <?php echo esc_html($listing['rooms']); ?> <?php echo esc_html(__('Oda')); ?>
                            </span>
                            <?php endif; ?>
                            <span class="flex items-center text-sm">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5"/>
                                </svg>
                                <?php echo esc_html($listing['area'] ?? ''); ?>
                            </span>
                        </div>
                        <div class="pt-4 border-t border-gray-200">
                            <span class="text-2xl font-bold" style="color: <?php echo esc_attr($primaryButtonBg); ?>;"><?php echo esc_html($listing['price'] ?? ''); ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-12">
            <a href="<?php echo function_exists('localized_url') ? localized_url('/ilanlar') : site_url('/ilanlar'); ?>" 
               style="background-color: <?php echo esc_attr($primaryButtonBg); ?>; color: <?php echo esc_attr($primaryButtonTextColor); ?>;"
               class="inline-block px-8 py-3 rounded-lg font-semibold hover:opacity-90 transition-all shadow-md hover:shadow-lg">
                <?php echo esc_html(__('Tüm İlanları Görüntüle')); ?>
            </a>
        </div>
    </div>
</section>
