<?php
/**
 * Real Estate Agent - Danışman Detay Sayfası
 */

// Header'ı render et
get_header([
    'title' => $agent['first_name'] . ' ' . $agent['last_name'] . ' - ' . get_option('site_name', ''),
    'meta_description' => !empty($agent['bio']) ? mb_substr(strip_tags($agent['bio']), 0, 160) : 'Emlak danışmanı profili'
]);
?>

<!-- Page Header -->
<section class="bg-gradient-to-br from-blue-600 to-blue-700 text-white py-12 lg:py-16">
    <div class="container mx-auto px-4 lg:px-6">
        <div class="max-w-4xl">
            <a href="<?php echo function_exists('localized_url') ? localized_url('/danismanlar') : site_url('/danismanlar'); ?>" 
               class="inline-flex items-center gap-2 text-blue-100 hover:text-white mb-4 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span><?php echo esc_html(__('Tüm Danışmanlar')); ?></span>
            </a>
            <h1 class="text-4xl lg:text-5xl font-bold mb-4">
                <?php echo esc_html($agent['first_name'] . ' ' . $agent['last_name']); ?>
            </h1>
            <?php if (!empty($agent['specializations'])): ?>
                <p class="text-xl text-blue-100">
                    <?php echo esc_html($agent['specializations']); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Agent Detail -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4 lg:px-6">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden sticky top-8">
                        <div class="relative">
                            <?php if (!empty($agent['photo'])): ?>
                                <img src="<?php echo esc_url($agent['photo']); ?>" 
                                     alt="<?php echo esc_attr($agent['first_name'] . ' ' . $agent['last_name']); ?>" 
                                     class="w-full h-80 object-cover">
                            <?php else: ?>
                                <div class="w-full h-80 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                                    <span class="text-8xl text-white font-bold">
                                        <?php echo esc_html(mb_substr($agent['first_name'], 0, 1) . mb_substr($agent['last_name'], 0, 1)); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($agent['is_featured']): ?>
                                <div class="absolute top-4 right-4">
                                    <span class="px-3 py-1 bg-primary text-white text-xs font-semibold rounded-full">
                                        <?php echo esc_html(__('Öne Çıkan')); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-6">
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">
                                <?php echo esc_html($agent['first_name'] . ' ' . $agent['last_name']); ?>
                            </h2>
                            
                            <?php if (!empty($agent['experience_years'])): ?>
                                <p class="text-sm text-gray-600 mb-4">
                                    <span class="font-semibold text-lg"><?php echo esc_html($agent['experience_years']); ?></span> <?php echo esc_html(__('yıl deneyim')); ?>
                                </p>
                            <?php endif; ?>
                            
                            <!-- Contact Info -->
                            <div class="space-y-3 mb-6">
                                <?php if (!empty($agent['phone'])): ?>
                                    <a href="tel:<?php echo esc_attr($agent['phone']); ?>" 
                                       class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                        <div class="w-10 h-10 flex items-center justify-center bg-blue-100 rounded-lg">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500"><?php echo esc_html(__('Telefon')); ?></p>
                                            <p class="text-sm font-semibold text-gray-900"><?php echo esc_html($agent['phone']); ?></p>
                                        </div>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($agent['email'])): ?>
                                    <a href="mailto:<?php echo esc_attr($agent['email']); ?>" 
                                       class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                        <div class="w-10 h-10 flex items-center justify-center bg-blue-100 rounded-lg">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500"><?php echo esc_html(__('E-posta')); ?></p>
                                            <p class="text-sm font-semibold text-gray-900"><?php echo esc_html($agent['email']); ?></p>
                                        </div>
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Social Media -->
                            <?php if (!empty($agent['facebook']) || !empty($agent['instagram']) || !empty($agent['linkedin']) || !empty($agent['twitter'])): ?>
                                <div class="mb-6">
                                    <p class="text-sm font-semibold text-gray-700 mb-3"><?php echo esc_html(__('Sosyal Medya')); ?></p>
                                    <div class="flex items-center gap-3">
                                        <?php if (!empty($agent['facebook'])): ?>
                                            <a href="<?php echo esc_url($agent['facebook']); ?>" target="_blank" rel="noopener noreferrer" 
                                               class="w-10 h-10 flex items-center justify-center bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($agent['instagram'])): ?>
                                            <a href="<?php echo esc_url($agent['instagram']); ?>" target="_blank" rel="noopener noreferrer" 
                                               class="w-10 h-10 flex items-center justify-center bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg hover:opacity-90 transition-opacity">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($agent['linkedin'])): ?>
                                            <a href="<?php echo esc_url($agent['linkedin']); ?>" target="_blank" rel="noopener noreferrer" 
                                               class="w-10 h-10 flex items-center justify-center bg-blue-700 text-white rounded-lg hover:bg-blue-800 transition-colors">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($agent['twitter'])): ?>
                                            <a href="<?php echo esc_url($agent['twitter']); ?>" target="_blank" rel="noopener noreferrer" 
                                               class="w-10 h-10 flex items-center justify-center bg-blue-400 text-white rounded-lg hover:bg-blue-500 transition-colors">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Hakkında ve Uzmanlık -->
                    <div class="bg-white rounded-xl shadow-md p-8">
                        <?php if (!empty($agent['bio'])): ?>
                            <div class="mb-8">
                                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?php echo esc_html(__('Hakkında')); ?></h2>
                                <div class="prose max-w-none text-gray-700">
                                    <?php echo nl2br(esc_html($agent['bio'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($agent['specializations'])): ?>
                            <div class="mb-8">
                                <h2 class="text-2xl font-bold text-gray-900 mb-4"><?php echo esc_html(__('Uzmanlık Alanları')); ?></h2>
                                <div class="prose max-w-none text-gray-700">
                                    <?php echo nl2br(esc_html($agent['specializations'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($agent['experience_years'])): ?>
                            <div class="bg-blue-50 rounded-lg p-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center">
                                        <span class="text-2xl font-bold text-white"><?php echo esc_html($agent['experience_years']); ?></span>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900"><?php echo esc_html(__('Yıllık Deneyim')); ?></h3>
                                        <p class="text-gray-600"><?php echo esc_html(__('Profesyonel emlak sektöründe')); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Emlakçının İlanları -->
                    <?php
                    // Controller'dan gelen verileri kullan (varsa)
                    $agentListings = $agentListings ?? [];
                    $totalListings = $totalListings ?? 0;
                    
                    $propertyTypeLabels = [
                        'house' => __('Müstakil Ev'),
                        'apartment' => __('Daire'),
                        'villa' => __('Villa'),
                        'commercial' => __('Ticari'),
                        'land' => __('Arsa')
                    ];
                    ?>
                    
                    <!-- Emlakçının İlanları -->
                    <?php if ($totalListings > 0 || !empty($agentListings)): ?>
                    <div class="bg-white rounded-xl shadow-md p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-gray-900">
                                <?php echo esc_html(__('İlanlar')); ?>
                                <?php if ($totalListings > 0): ?>
                                    <span class="text-lg font-normal text-gray-500">(<?php echo esc_html($totalListings); ?>)</span>
                                <?php endif; ?>
                            </h2>
                        </div>
                        
                        <?php if (!empty($agentListings)): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($agentListings as $listing): 
                                $listingPrice = function_exists('realestate_format_price') ? realestate_format_price($listing['price'] ?? 0) : '₺' . number_format($listing['price'] ?? 0, 0, ',', '.');
                                $listingType = $propertyTypeLabels[$listing['property_type'] ?? 'house'] ?? __('Emlak');
                                $listingStatus = ($listing['listing_status'] ?? 'sale') === 'rent' ? __('Kiralık') : __('Satılık');
                                $listingSlug = !empty($listing['slug']) ? $listing['slug'] : $listing['id'];
                                $listingUrl = function_exists('localized_url') ? localized_url('/ilan/' . $listingSlug) : site_url('/ilan/' . $listingSlug);
                                $listingImage = !empty($listing['featured_image']) ? $listing['featured_image'] : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=400';
                                
                                // Buton renkleri - Tema renk paletinden al
                                $primaryButtonBg = '#1e40af';
                                if (class_exists('ThemeLoader')) {
                                    $themeLoaderInstance = ThemeLoader::getInstance();
                                    $primaryButtonBg = $themeLoaderInstance->getColor('primary', '#1e40af');
                                }
                            ?>
                            <a href="<?php echo esc_url($listingUrl); ?>" 
                               class="group block bg-white rounded-lg shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 overflow-hidden">
                                <div class="relative overflow-hidden">
                                    <img src="<?php echo esc_url($listingImage); ?>" 
                                         alt="<?php echo esc_attr($listing['title']); ?>" 
                                         class="w-full h-72 object-cover group-hover:scale-110 transition-transform duration-500">
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all duration-300"></div>
                                    
                                    <?php if ($listing['is_featured']): ?>
                                        <div class="absolute top-4 left-4 z-10">
                                            <span style="background-color: <?php echo esc_attr($primaryButtonBg); ?>; color: #ffffff;" class="px-4 py-1.5 text-sm font-semibold rounded-full shadow-lg group-hover:scale-105 transition-transform duration-300">
                                                <?php echo esc_html(__('Öne Çıkan')); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="absolute top-4 right-4 z-10">
                                        <span class="px-4 py-1.5 text-sm font-semibold rounded-full shadow-lg bg-white/95 backdrop-blur-sm text-gray-900">
                                            <?php echo esc_html($listingStatus); ?>
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
                                        <?php echo esc_html($listing['location'] ?? ''); ?>
                                    </p>
                                    <div class="flex items-center gap-4 text-gray-600 mb-5 flex-wrap">
                                        <?php if (!empty($listing['bedrooms'])): ?>
                                            <span class="flex items-center text-sm">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                                </svg>
                                                <?php echo esc_html($listing['bedrooms']); ?> <?php echo esc_html(__('Yatak')); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($listing['bathrooms'])): ?>
                                            <span class="flex items-center text-sm">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                                </svg>
                                                <?php echo esc_html($listing['bathrooms']); ?> <?php echo esc_html(__('Banyo')); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($listing['area'])): ?>
                                            <span class="flex items-center text-sm">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                                                </svg>
                                                <?php echo esc_html(number_format($listing['area'], 0, ',', '.')); ?> m²
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="pt-4 border-t border-gray-200">
                                        <span class="text-2xl font-bold" style="color: <?php echo esc_attr($primaryButtonBg); ?>;"><?php echo esc_html($listingPrice); ?></span>
                                    </div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if ($totalListings > count($agentListings)): ?>
                        <div class="mt-8 text-center">
                            <a href="<?php echo function_exists('localized_url') ? localized_url('/ilanlar?realtor=' . $agent['id']) : site_url('/ilanlar?realtor=' . $agent['id']); ?>" 
                               style="background-color: <?php echo esc_attr($primaryButtonBg ?? '#1e40af'); ?>; color: #ffffff;"
                               class="inline-block px-8 py-3 rounded-lg font-semibold hover:opacity-90 transition-all shadow-md hover:shadow-lg">
                                <?php echo esc_html(__('Tüm İlanları Görüntüle')); ?>
                            </a>
                        </div>
                        <?php endif; ?>
                        <?php else: ?>
                        <div class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-200 mb-4">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2"><?php echo esc_html(__('Henüz ilan eklenmemiş')); ?></h3>
                            <p class="text-gray-600"><?php echo esc_html(__('Bu danışmana ait ilanlar henüz eklenmemiş.')); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
