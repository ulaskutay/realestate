<?php
$settings = $settings ?? [];
$message = $message ?? null;
$listings_without_coords_count = isset($listings_without_coords_count) ? (int) $listings_without_coords_count : 0;
?>

<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div class="flex flex-col gap-2">
        <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Harita Üzerinde İlanlar – Ayarlar</h1>
        <p class="text-gray-500 dark:text-gray-400 text-base">Google Maps API ve varsayılan harita merkezi</p>
    </div>
</header>

<?php if ($message): ?>
<div class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 text-green-800 dark:text-green-200">
    <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
</div>
<?php endif; ?>

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <form method="POST" action="<?php echo admin_url('module/listings-map/settings'); ?>" class="space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Google Maps</h2>
            <div class="space-y-4">
                <div>
                    <label for="google_maps_api_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Google Maps API Key</label>
                    <input type="text" name="google_maps_api_key" id="google_maps_api_key" value="<?php echo esc_attr($settings['google_maps_api_key'] ?? ''); ?>" placeholder="AIza..." class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" autocomplete="off">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Maps JavaScript API ve Geocoding API açık olmalı. Harita ve adres araması için kullanılır.</p>
                </div>
            </div>
        </div>
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Varsayılan harita merkezi</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="default_lat" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Enlem</label>
                    <input type="number" name="default_lat" id="default_lat" value="<?php echo esc_attr($settings['default_lat'] ?? 39.0); ?>" step="0.0001" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                <div>
                    <label for="default_lng" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Boylam</label>
                    <input type="number" name="default_lng" id="default_lng" value="<?php echo esc_attr($settings['default_lng'] ?? 35.0); ?>" step="0.0001" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                <div>
                    <label for="default_zoom" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Zoom (1–18)</label>
                    <input type="number" name="default_zoom" id="default_zoom" value="<?php echo esc_attr($settings['default_zoom'] ?? 6); ?>" min="1" max="18" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="submit" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                <span class="material-symbols-outlined text-xl">save</span>
                <span>Kaydet</span>
            </button>
        </div>
    </form>
</div>

<?php if ($listings_without_coords_count > 0): ?>
<div class="mt-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-6">
    <h2 class="text-lg font-semibold text-amber-900 dark:text-amber-200 mb-2">Haritada işaretlenmeyen ilanlar</h2>
    <p class="text-sm text-amber-800 dark:text-amber-300 mb-4">
        <strong><?php echo (int) $listings_without_coords_count; ?></strong> ilanda konum (enlem/boylam) eksik. Bu ilanlar haritada görünmez. &quot;Konum&quot; alanı dolu olan ilanlar için aşağıdaki butonla adresten konum alabilirsiniz.
    </p>
    <a href="<?php echo esc_url(admin_url('module/listings-map/bulk-geocode')); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors">
        <span class="material-symbols-outlined text-xl">location_on</span>
        <span>Eksik konumları adresten doldur</span>
    </a>
    <p class="text-xs text-amber-700 dark:text-amber-400 mt-2">En fazla 50 ilan işlenir; tekrar tıklayarak devam edebilirsiniz. Google Geocoding API kotasına dikkat edin.</p>
</div>
<?php endif; ?>
