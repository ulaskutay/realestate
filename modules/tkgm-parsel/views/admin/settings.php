<?php
$settings = $settings ?? [];
$testResult = $testResult ?? null;
?>

<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div class="flex flex-col gap-2">
        <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">TKGM Parsel Ayarları</h1>
        <p class="text-gray-500 dark:text-gray-400 text-base">CBS API bağlantı ve önbellek ayarları</p>
    </div>
    <a href="<?php echo admin_url('module/tkgm-parsel'); ?>" class="flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
        <span class="material-symbols-outlined text-xl">arrow_back</span>
        <span class="text-sm font-medium">Dashboard</span>
    </a>
</header>

<?php if (isset($_SESSION['flash_message'])): ?>
<div class="mb-6 p-4 rounded-lg <?php echo ($_SESSION['flash_type'] ?? 'success') === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 text-red-800 dark:text-red-200'; ?>">
    <p class="text-sm font-medium"><?php echo esc_html($_SESSION['flash_message']); ?></p>
</div>
<?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); endif; ?>

<?php if ($testResult): ?>
<div class="mb-6 p-4 rounded-lg <?php echo $testResult['success'] ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 text-green-800 dark:text-green-200' : 'bg-amber-50 dark:bg-amber-900/20 border border-amber-200 text-amber-800 dark:text-amber-200'; ?>">
    <p class="text-sm font-medium"><?php echo esc_html($testResult['message']); ?></p>
</div>
<?php endif; ?>

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <form method="POST" action="<?php echo admin_url('module/tkgm-parsel/settings'); ?>" class="space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">API Ayarları</h2>
            <div class="space-y-4">
                <div>
                    <label for="api_base_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">API Base URL (CBS)</label>
                    <input type="url" name="api_base_url" id="api_base_url" value="<?php echo esc_attr($settings['api_base_url'] ?? 'https://cbsapi.tkgm.gov.tr/megsiswebapi.v3.1'); ?>" placeholder="https://cbsapi.tkgm.gov.tr/megsiswebapi.v3.1" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">CBS TKGM API adresi (parsel detay için).</p>
                </div>
                <div>
                    <label for="hierarchy_api_base_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">İl/İlçe/Mahalle listesi API base URL (opsiyonel)</label>
                    <input type="url" name="hierarchy_api_base_url" id="hierarchy_api_base_url" value="<?php echo esc_attr($settings['hierarchy_api_base_url'] ?? ''); ?>" placeholder="Boş bırakılırsa yukarıdaki CBS URL kullanılır" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">CBS il/ilçe/mahalle listesi farklı bir adresten geliyorsa buraya yazın.</p>
                </div>
                <div>
                    <label for="idariyapi_base_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">idariYapi base URL (il/ilçe/mahalle + parsel)</label>
                    <input type="url" name="idariyapi_base_url" id="idariyapi_base_url" value="<?php echo esc_attr($settings['idariyapi_base_url'] ?? 'https://cbsservis.tkgm.gov.tr/megsiswebapi.v3/api'); ?>" placeholder="https://cbsservis.tkgm.gov.tr/megsiswebapi.v3/api" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">burakaktna/tkgmservice ile uyumlu: <code>idariYapi/ilListe</code>, <code>ilceListe/{id}</code>, <code>mahalleListe/{id}</code>, <code>parsel/{mahalleId}/{ada}/{parsel}</code>. Varsayılan: cbsservis.tkgm.gov.tr</p>
                </div>
                <div>
                    <label for="mapbox_access_token" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mapbox Access Token (harita uydu görüntüsü)</label>
                    <input type="text" name="mapbox_access_token" id="mapbox_access_token" value="<?php echo esc_attr($settings['mapbox_access_token'] ?? ''); ?>" placeholder="pk.eyJ1Ijoi..." class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Varsa parsel haritası Mapbox Satellite ile gösterilir; yoksa ESRI World Imagery kullanılır.</p>
                </div>
                <div>
                    <label for="google_maps_api_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Google Maps API Key (3D uydu + Photorealistic 3D Tiles)</label>
                    <input type="text" name="google_maps_api_key" id="google_maps_api_key" value="<?php echo esc_attr($settings['google_maps_api_key'] ?? ''); ?>" placeholder="AIza..." class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" autocomplete="off">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Parsel haritası ve 3D dron görünümü için. Cloud Console’da <strong>Maps JavaScript API</strong> ve <strong>Map Tiles API</strong> açın. Map Tiles API, Photorealistic 3D Tiles (Google Earth benzeri) için gereklidir; fatura hesabı bağlı olmalıdır. Kısıtlama kullanıyorsanız HTTP referrer’a admin sayfa adresinizi ekleyin.</p>
                </div>
                <div>
                    <label for="gemini_api_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Gemini API Key (görseli AI ile yeniden oluşturma)</label>
                    <input type="password" name="gemini_api_key" id="gemini_api_key" value="<?php echo esc_attr($settings['gemini_api_key'] ?? ''); ?>" placeholder="Gemini API key" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" autocomplete="off">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Parsel görsellerini Gemini 2.5 Flash Image ile yeniden oluşturmak için. Günde 500 görsel ücretsiz. <a href="https://aistudio.google.com/apikey" target="_blank" rel="noopener" class="text-primary hover:underline">API Key al (Google AI Studio)</a></p>
                </div>
                <div>
                    <label for="arcgis_parsel_layer_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ArcGIS Parsel Katman URL (yedek)</label>
                    <input type="url" name="arcgis_parsel_layer_url" id="arcgis_parsel_layer_url" value="<?php echo esc_attr($settings['arcgis_parsel_layer_url'] ?? ''); ?>" placeholder="https://.../MapServer/0" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Parselsorgu benzeri ArcGIS MapServer Layer 0 URL. CBS yanıt vermezse parsel sorgusu bu adrese yapılır. <strong>Bulmak için:</strong> parselsorgu.tkgm.gov.tr açın, F12 → Network, bir parsel sorgulayın; &quot;query&quot; isteğinin adresindeki <code>.../MapServer/0</code> kısmını buraya yapıştırın (query ve sonrası olmadan).</p>
                </div>
                <div>
                    <label for="api_timeout" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Zaman aşımı (saniye)</label>
                    <input type="number" name="api_timeout" id="api_timeout" value="<?php echo esc_attr($settings['api_timeout'] ?? 15); ?>" min="5" max="60" class="w-full max-w-xs px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                <div>
                    <label for="cache_ttl" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Önbellek süresi (dakika)</label>
                    <input type="number" name="cache_ttl" id="cache_ttl" value="<?php echo esc_attr($settings['cache_ttl'] ?? 60); ?>" min="0" max="10080" class="w-full max-w-xs px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">0 = önbellek kapalı. İl/ilçe/mahalle listeleri bu süre boyunca saklanır.</p>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="submit" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                <span class="material-symbols-outlined text-xl">save</span>
                <span>Kaydet</span>
            </button>
            <a href="<?php echo admin_url('module/tkgm-parsel/settings?test_api=1'); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                <span class="material-symbols-outlined text-xl">wifi_tethering</span>
                <span>Bağlantıyı Test Et</span>
            </a>
        </div>
    </form>
</div>
