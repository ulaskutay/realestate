<?php
$settings = $settings ?? [];
$sonDroneVideolari = $sonDroneVideolari ?? [];
?>

<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div class="flex flex-col gap-2">
        <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">TKGM Parsel Sorgu</h1>
        <p class="text-gray-500 dark:text-gray-400 text-base">
            CBS TKGM API ile il, ilçe, mahalle, ada ve parsel bilgisiyle arsa sorgulaması yapın (parselsorgu.tkgm.gov.tr benzeri).
        </p>
    </div>
    <div class="flex items-center gap-2">
        <a href="<?php echo admin_url('module/tkgm-parsel/sorgu'); ?>" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
            <span class="material-symbols-outlined text-xl">map</span>
            <span class="text-sm font-medium">Parsel Sorgula</span>
        </a>
        <a href="<?php echo admin_url('module/tkgm-parsel/settings'); ?>" class="flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
            <span class="material-symbols-outlined text-xl">settings</span>
            <span class="text-sm font-medium">Ayarlar</span>
        </a>
    </div>
</header>

<?php if (isset($_SESSION['flash_message'])): ?>
<div class="mb-6 p-4 rounded-lg <?php echo ($_SESSION['flash_type'] ?? 'success') === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 text-red-800 dark:text-red-200'; ?>">
    <p class="text-sm font-medium"><?php echo esc_html($_SESSION['flash_message']); ?></p>
</div>
<?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); endif; ?>

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Kullanım</h2>
    <ul class="space-y-2 text-gray-600 dark:text-gray-300 text-sm">
        <li class="flex items-start gap-2">
            <span class="material-symbols-outlined text-primary text-lg shrink-0">arrow_forward</span>
            <span><strong>Parsel Sorgu</strong> sayfasından İl → İlçe → Mahalle/Köy → Ada → Parsel seçerek arsa bilgisini (taşınmaz no, alan, nitelik vb.) sorgulayabilirsiniz.</span>
        </li>
        <li class="flex items-start gap-2">
            <span class="material-symbols-outlined text-primary text-lg shrink-0">arrow_forward</span>
            <span><strong>Ayarlar</strong> bölümünde CBS API adresi, zaman aşımı ve önbellek süresini değiştirebilirsiniz.</span>
        </li>
        <li class="flex items-start gap-2">
            <span class="material-symbols-outlined text-primary text-lg shrink-0">info</span>
            <span>Veriler bilgilendirme amaçlıdır; resmi işlemler için TKGM’nin resmi kanallarını kullanınız.</span>
        </li>
    </ul>
</div>

<div class="mt-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Son Drone Videoları</h2>
    <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">
        Medya kütüphanesindeki son videolar. Parsel Sorgu sayfasından 3D Dron Görünümü ile oluşturduğunuz videoları İçerik Kütüphanesi'ne yükleyerek burada görebilirsiniz.
    </p>
    <?php if (!empty($sonDroneVideolari)): ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <?php foreach ($sonDroneVideolari as $v): ?>
        <a href="<?php echo esc_attr(site_url($v['file_url'] ?? $v['file_path'] ?? '#')); ?>" target="_blank" rel="noopener" class="block rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600 hover:border-indigo-500 transition-colors group">
            <div class="aspect-video bg-gray-900 flex items-center justify-center">
                <span class="material-symbols-outlined text-4xl text-gray-500 group-hover:text-indigo-400">videocam</span>
            </div>
            <div class="p-2 bg-gray-50 dark:bg-gray-700/50">
                <p class="text-xs text-gray-700 dark:text-gray-300 truncate" title="<?php echo esc_attr($v['original_name'] ?? ''); ?>"><?php echo esc_html($v['original_name'] ?? 'Video'); ?></p>
                <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo !empty($v['created_at']) ? date('d.m.Y H:i', strtotime($v['created_at'])) : ''; ?></p>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <a href="<?php echo admin_url('media'); ?>" class="inline-flex items-center gap-2 mt-4 text-sm text-indigo-500 hover:text-indigo-600">Tüm videoları gör <span class="material-symbols-outlined text-lg">arrow_forward</span></a>
    <?php else: ?>
    <p class="text-gray-500 dark:text-gray-400 text-sm">Henüz video yok. Parsel Sorgu sayfasından 3D Dron Görünümü ile video oluşturup İçerik Kütüphanesi'ne yükleyebilirsiniz.</p>
    <a href="<?php echo admin_url('module/tkgm-parsel/sorgu'); ?>" class="inline-flex items-center gap-2 mt-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
        <span class="material-symbols-outlined text-lg">videocam</span>
        <span>Parsel Sorgu → 3D Dron Görünümü</span>
    </a>
    <?php endif; ?>
</div>
