<?php
/**
 * Real Estate Theme - Property Search Bar
 */

$section = $section ?? [];
$settings = $section['settings'] ?? [];

$searchTitle = !empty($section['title']) ? $section['title'] : __('Search Properties');
$searchSubtitle = !empty($section['subtitle']) ? $section['subtitle'] : __('Find your perfect home');

// Buton renkleri - Tema renk paletinden al
$primaryButtonBg = '#1e40af';
$primaryButtonTextColor = '#ffffff';

// ThemeLoader'dan tema renklerini al
if (class_exists('ThemeLoader')) {
    $themeLoaderInstance = ThemeLoader::getInstance();
    $primaryButtonBg = $themeLoaderInstance->getColor('primary', '#1e40af');
    $primaryButtonTextColor = '#ffffff';
}
?>

<section class="py-12 lg:py-16 bg-surface">
    <div class="container mx-auto px-4 lg:px-6">
        <div class="max-w-5xl mx-auto">
            <?php if (!empty($searchTitle)): ?>
                <div class="text-center mb-8">
                    <h2 class="text-3xl lg:text-4xl font-bold text-secondary mb-2"><?php echo esc_html($searchTitle); ?></h2>
                    <?php if (!empty($searchSubtitle)): ?>
                        <p class="text-gray-600"><?php echo esc_html($searchSubtitle); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-lg p-6 lg:p-8">
                <form action="<?php echo function_exists('localized_url') ? localized_url('/ilanlar') : site_url('/ilanlar'); ?>" method="get" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Location -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html(__('Location')); ?></label>
                        <input type="text" 
                               name="location" 
                               placeholder="<?php echo esc_attr(__('City, Neighborhood')); ?>" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Property Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html(__('Property Type')); ?></label>
                        <select name="type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value=""><?php echo esc_html(__('All Types')); ?></option>
                            <option value="house"><?php echo esc_html(__('House')); ?></option>
                            <option value="apartment"><?php echo esc_html(__('Apartment')); ?></option>
                            <option value="villa"><?php echo esc_html(__('Villa')); ?></option>
                            <option value="commercial"><?php echo esc_html(__('Commercial')); ?></option>
                            <option value="land"><?php echo esc_html(__('Land')); ?></option>
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html(__('Fiyat Aralığı')); ?></label>
                        <select name="price_range" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value=""><?php echo esc_html(__('Herhangi Bir Fiyat')); ?></option>
                            <option value="0-1000000">₺0 - ₺1.000.000</option>
                            <option value="1000000-2500000">₺1.000.000 - ₺2.500.000</option>
                            <option value="2500000-5000000">₺2.500.000 - ₺5.000.000</option>
                            <option value="5000000-10000000">₺5.000.000 - ₺10.000.000</option>
                            <option value="10000000+">₺10.000.000+</option>
                        </select>
                    </div>

                    <!-- Search Button -->
                    <div class="flex items-end">
                        <button type="submit" style="background-color: <?php echo esc_attr($primaryButtonBg); ?>; color: <?php echo esc_attr($primaryButtonTextColor); ?>;" class="w-full px-6 py-3 rounded-lg font-semibold hover:opacity-90 transition-all shadow-md hover:shadow-lg">
                            <?php echo esc_html(__('Search')); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
