<?php
$settings = $settings ?? [];
$testParselResult = $testParselResult ?? null;
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
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Test Parsel Sorgusu</h2>
    <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">
        Sistem tarafından yapılan test sorgusu: <strong>Muğla / Marmaris / Karaca</strong> — Ada <strong>164</strong>, Parsel <strong>56</strong>. Önce CBS API denenir, yanıt yoksa örnek dosya (tkgm-parsel-sorgu-sonuc-164-ada-56-parsel.json) kullanılır. Sonuç aşağıda gösterilir.
    </p>
    <a href="<?php echo admin_url('module/tkgm-parsel?test_parsel=1'); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition-colors text-sm font-medium">
        <span class="material-symbols-outlined text-xl">play_circle</span>
        <span>Test Sorgusu Çalıştır</span>
    </a>

    <?php if ($testParselResult !== null): ?>
    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-600">
        <h3 class="text-base font-medium text-gray-900 dark:text-white mb-3">Sorgu Sonucu</h3>
        <div class="rounded-lg p-4 <?php echo $testParselResult['success'] ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800'; ?>">
            <p class="text-sm font-medium <?php echo $testParselResult['success'] ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200'; ?>">
                <?php echo esc_html($testParselResult['message']); ?>
            </p>
            <?php if (!empty($testParselResult['data'])): ?>
            <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                <div><dt class="text-gray-500 dark:text-gray-400">İl</dt><dd class="font-medium text-gray-900 dark:text-white"><?php echo esc_html($testParselResult['data']['il_adi'] ?? '—'); ?></dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">İlçe</dt><dd class="font-medium text-gray-900 dark:text-white"><?php echo esc_html($testParselResult['data']['ilce_adi'] ?? '—'); ?></dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Mahalle</dt><dd class="font-medium text-gray-900 dark:text-white"><?php echo esc_html($testParselResult['data']['mahalle_adi'] ?? '—'); ?></dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Ada / Parsel</dt><dd class="font-medium text-gray-900 dark:text-white"><?php echo esc_html(($testParselResult['data']['ada'] ?? '—') . ' / ' . ($testParselResult['data']['parsel_no'] ?? '—')); ?></dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Nitelik</dt><dd class="font-medium text-gray-900 dark:text-white"><?php echo esc_html($testParselResult['data']['nitelik'] ?? '—'); ?></dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Alan (m²)</dt><dd class="font-medium text-gray-900 dark:text-white"><?php echo isset($testParselResult['data']['alan_m2']) ? esc_html((string)$testParselResult['data']['alan_m2']) : '—'; ?></dd></div>
            </dl>
            <details class="mt-4">
                <summary class="cursor-pointer text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">Ham veriyi göster</summary>
                <pre class="mt-2 p-3 bg-gray-900 dark:bg-gray-950 text-gray-100 text-xs rounded-lg overflow-x-auto max-h-64 overflow-y-auto"><?php echo esc_html(json_encode($testParselResult['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
            </details>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
