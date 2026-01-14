<?php
/**
 * Real Estate Theme - Consultants Section
 */

$section = $section ?? [];
$settings = $section['settings'] ?? [];

$sectionTitle = !empty($section['title']) ? $section['title'] : __('Uzman Danışmanlarımız');
$sectionSubtitle = !empty($section['subtitle']) ? $section['subtitle'] : __('Size en uygun emlağı bulmanızda yardımcı olacak profesyonel ekibimizle tanışın');

// Buton renkleri - Tema renk paletinden al
$primaryButtonBg = '#1e40af';
$primaryButtonTextColor = '#ffffff';

// ThemeLoader'dan tema renklerini al
if (class_exists('ThemeLoader')) {
    $themeLoaderInstance = ThemeLoader::getInstance();
    $primaryButtonBg = $themeLoaderInstance->getColor('primary', '#1e40af');
    $primaryButtonTextColor = '#ffffff';
}

// Get consultants from module
$consultants = [];
$limit = !empty($settings['limit']) ? intval($settings['limit']) : 6;

// Load Database and Model classes
$databasePath = __DIR__ . '/../../../core/Database.php';
$modelPath = __DIR__ . '/../modules/realestate-agents/Model.php';

if (file_exists($databasePath) && file_exists($modelPath)) {
    if (!class_exists('Database')) {
        require_once $databasePath;
    }
    require_once $modelPath;
    
    if (class_exists('RealEstateAgentsModel')) {
        try {
            $model = new RealEstateAgentsModel();
            $rawConsultants = $model->getFeatured($limit);
            
            // Format consultants data
            foreach ($rawConsultants as $consultant) {
                $consultants[] = [
                    'id' => $consultant['id'],
                    'slug' => $consultant['slug'] ?? '',
                    'first_name' => $consultant['first_name'],
                    'last_name' => $consultant['last_name'],
                    'name' => trim($consultant['first_name'] . ' ' . $consultant['last_name']),
                    'photo' => !empty($consultant['photo']) ? $consultant['photo'] : null,
                    'phone' => $consultant['phone'] ?? '',
                    'email' => $consultant['email'] ?? '',
                    'specializations' => $consultant['specializations'] ?? '',
                    'experience_years' => intval($consultant['experience_years'] ?? 0),
                    'bio' => $consultant['bio'] ?? '',
                    'facebook' => $consultant['facebook'] ?? '',
                    'instagram' => $consultant['instagram'] ?? '',
                    'linkedin' => $consultant['linkedin'] ?? '',
                    'twitter' => $consultant['twitter'] ?? '',
                    'is_featured' => !empty($consultant['is_featured'])
                ];
            }
        } catch (Exception $e) {
            error_log('Consultants error: ' . $e->getMessage());
        }
    }
}
?>

<section class="py-16 lg:py-24 bg-gray-50">
    <div class="container mx-auto px-4 lg:px-6">
        <div class="text-center mb-12">
            <h2 class="text-3xl lg:text-4xl font-bold text-secondary mb-4"><?php echo esc_html($sectionTitle); ?></h2>
            <?php if (!empty($sectionSubtitle)): ?>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto"><?php echo esc_html($sectionSubtitle); ?></p>
            <?php endif; ?>
        </div>

        <?php if (empty($consultants)): ?>
            <div class="text-center py-12">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-200 mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <p class="text-gray-600"><?php echo esc_html(__('Henüz danışman eklenmemiş')); ?></p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                <?php foreach ($consultants as $consultant): 
                    $slug = !empty($consultant['slug']) ? $consultant['slug'] : $consultant['id'];
                    $consultantUrl = function_exists('localized_url') ? localized_url('/danisman/' . $slug) : site_url('/danisman/' . $slug);
                ?>
                    <div class="group bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                        <!-- Photo Section -->
                        <div class="relative h-72 overflow-hidden">
                            <?php if (!empty($consultant['photo'])): ?>
                                <img src="<?php echo esc_url($consultant['photo']); ?>" 
                                     alt="<?php echo esc_attr($consultant['name']); ?>" 
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <?php else: ?>
                                <div class="w-full h-full bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-700 flex items-center justify-center">
                                    <span class="text-7xl text-white font-bold opacity-90">
                                        <?php echo esc_html(mb_substr($consultant['first_name'], 0, 1) . mb_substr($consultant['last_name'], 0, 1)); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Overlay Gradient -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            
                            <!-- Featured Badge -->
                            <?php if ($consultant['is_featured']): ?>
                                <div class="absolute top-4 right-4 z-10">
                                    <span class="px-3 py-1.5 bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-xs font-bold rounded-full shadow-lg flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        <?php echo esc_html(__('Öne Çıkan')); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Info Section -->
                        <div class="p-6">
                            <!-- Name and Specialization -->
                            <div class="mb-4">
                                <h3 class="text-2xl font-bold text-gray-900 mb-1 group-hover:text-blue-600 transition-colors">
                                    <?php echo esc_html($consultant['name']); ?>
                                </h3>
                                <?php if (!empty($consultant['specializations'])): ?>
                                    <p class="text-sm text-blue-600 font-medium">
                                        <?php echo esc_html(mb_substr($consultant['specializations'], 0, 60)); ?>
                                        <?php echo mb_strlen($consultant['specializations']) > 60 ? '...' : ''; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Bio Preview -->
                            <?php if (!empty($consultant['bio'])): ?>
                                <p class="text-sm text-gray-600 mb-5 line-clamp-2 leading-relaxed">
                                    <?php echo esc_html(mb_substr(strip_tags($consultant['bio']), 0, 120)); ?>
                                    <?php echo mb_strlen(strip_tags($consultant['bio'])) > 120 ? '...' : ''; ?>
                                </p>
                            <?php endif; ?>
                            
                            <!-- Contact Info -->
                            <div class="space-y-2 mb-5 pb-5 border-b border-gray-100">
                                <?php if (!empty($consultant['phone'])): ?>
                                    <a href="tel:<?php echo esc_attr($consultant['phone']); ?>" 
                                       class="flex items-center gap-3 text-sm text-gray-700 hover:text-blue-600 transition-colors group/contact">
                                        <div class="w-9 h-9 flex items-center justify-center bg-blue-50 rounded-lg group-hover/contact:bg-blue-100 transition-colors">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                        </div>
                                        <span class="font-medium"><?php echo esc_html($consultant['phone']); ?></span>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($consultant['email'])): ?>
                                    <a href="mailto:<?php echo esc_attr($consultant['email']); ?>" 
                                       class="flex items-center gap-3 text-sm text-gray-700 hover:text-blue-600 transition-colors group/contact">
                                        <div class="w-9 h-9 flex items-center justify-center bg-blue-50 rounded-lg group-hover/contact:bg-blue-100 transition-colors">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <span class="font-medium truncate"><?php echo esc_html($consultant['email']); ?></span>
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Social Media -->
                            <?php if (!empty($consultant['facebook']) || !empty($consultant['instagram']) || !empty($consultant['linkedin']) || !empty($consultant['twitter'])): ?>
                                <div class="flex items-center gap-2 mb-5">
                                    <span class="text-xs text-gray-500 font-medium mr-1"><?php echo esc_html(__('Takip Et:')); ?></span>
                                    <?php if (!empty($consultant['facebook'])): ?>
                                        <a href="<?php echo esc_url($consultant['facebook']); ?>" target="_blank" rel="noopener noreferrer" 
                                           class="w-9 h-9 flex items-center justify-center bg-blue-600 text-white rounded-lg hover:bg-blue-700 hover:scale-110 transition-all duration-200 shadow-sm">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($consultant['instagram'])): ?>
                                        <a href="<?php echo esc_url($consultant['instagram']); ?>" target="_blank" rel="noopener noreferrer" 
                                           class="w-9 h-9 flex items-center justify-center bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg hover:opacity-90 hover:scale-110 transition-all duration-200 shadow-sm">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($consultant['linkedin'])): ?>
                                        <a href="<?php echo esc_url($consultant['linkedin']); ?>" target="_blank" rel="noopener noreferrer" 
                                           class="w-9 h-9 flex items-center justify-center bg-blue-700 text-white rounded-lg hover:bg-blue-800 hover:scale-110 transition-all duration-200 shadow-sm">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($consultant['twitter'])): ?>
                                        <a href="<?php echo esc_url($consultant['twitter']); ?>" target="_blank" rel="noopener noreferrer" 
                                           class="w-9 h-9 flex items-center justify-center bg-blue-400 text-white rounded-lg hover:bg-blue-500 hover:scale-110 transition-all duration-200 shadow-sm">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- View Profile Button -->
                            <a href="<?php echo esc_url($consultantUrl); ?>" 
                               style="background-color: <?php echo esc_attr($primaryButtonBg); ?>; color: <?php echo esc_attr($primaryButtonTextColor); ?>;"
                               class="block w-full text-center px-6 py-3 rounded-xl font-semibold hover:opacity-90 transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-[1.02] flex items-center justify-center gap-2">
                                <span><?php echo esc_html(__('Profilini Görüntüle')); ?></span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-12">
                <a href="<?php echo function_exists('localized_url') ? localized_url('/danismanlar') : site_url('/danismanlar'); ?>" 
                   style="background-color: <?php echo esc_attr($primaryButtonBg); ?>; color: <?php echo esc_attr($primaryButtonTextColor); ?>;"
                   class="inline-block px-8 py-3 rounded-lg font-semibold hover:opacity-90 transition-all shadow-md hover:shadow-lg">
                    <?php echo esc_html(__('Tüm Danışmanları Görüntüle')); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
