<?php
$settings = $settings ?? [];
$sonDroneVideolari = $sonDroneVideolari ?? [];
$baseUrl = admin_url('module/tkgm-parsel/sorgu');
$mediaListApiUrl = admin_url('media/list');
$uploadsBaseUrl = rtrim(site_url('uploads'), '/') . '/';
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
<style>
#modalDroneVideo .modal-drone-content {
    height: calc(100vh - 3.5rem);
    min-height: 0;
}
#modalOverlaySection {
    min-height: 0;
    display: flex;
    flex-direction: column;
}
</style>

<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div class="flex flex-col gap-2">
        <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Parsel Sorgu</h1>
        <p class="text-gray-500 dark:text-gray-400 text-base">Parsel bilgisini parselsorgu.tkgm.gov.tr formatındaki GeoJSON dosyası veya metni ile yükleyin; harita, detay ve 3D dron görünümü açılır.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <button type="button" id="btnVideoOlusturDuzenle" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
            <span class="material-symbols-outlined text-xl">video_library</span>
            Video oluştur / düzenle
        </button>
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

<div class="mb-6 rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4">
    <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Parsel bilgilerini nereden alabilirsiniz?</h2>
    <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">
        Parsel bilgilerinin resmi kaynağı <a href="http://parselsorgu.tkgm.gov.tr/" target="_blank" rel="noopener" class="text-primary hover:underline font-medium">parselsorgu.tkgm.gov.tr</a> adresidir.
    </p>
    <p class="text-sm text-gray-600 dark:text-gray-400">
        Aşağıdaki <strong>JSON ile parsel verisi yükle</strong> alanından TKGM formatında indirdiğiniz GeoJSON dosyasını yükleyerek parsel bilgisini, haritayı ve 3D dron görünümünü açabilirsiniz.
    </p>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 sm:p-6 mb-6">
    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">JSON ile parsel verisi yükle</h3>
    <div class="mb-3">
        <input type="file" id="jsonFileInput" accept=".json,application/json" class="text-sm file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-gray-100 dark:file:bg-gray-700 file:text-gray-700 dark:file:text-gray-300">
    </div>
    <textarea id="jsonPasteArea" class="w-full h-24 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono placeholder-gray-400" placeholder="GeoJSON yapıştırın (parselsorgu.tkgm.gov.tr formatında, FeatureCollection)…"></textarea>
    <button type="button" id="btnJsonSorgula" class="mt-2 inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">
        <span class="material-symbols-outlined text-lg">upload_file</span>
        <span>JSON ile sorgula</span>
    </button>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<div id="sonucAlani" class="hidden bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Parsel Bilgisi</h2>
    <div id="parselHaritaWrap" class="hidden mb-4 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600 min-h-[280px] sm:min-h-[400px] w-full" style="height: 70vh; max-height: 560px;">
        <div id="parselHarita" style="height: 100%;"></div>
    </div>
    <div id="parsel3DBtnWrap" class="hidden mb-4">
        <button type="button" id="btn3DDron" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
            <span class="material-symbols-outlined text-xl">videocam</span>
            <span>3D Dron Görünümü</span>
        </button>
    </div>
    <div id="sonucIcerik"></div>
</div>

<!-- Modal: Drone Video – Cesium & overlay -->
<div id="modalDroneVideo" class="hidden fixed inset-0 z-[9999] flex flex-col bg-black/95 overflow-hidden" style="display: none;">
    <div class="flex items-center justify-between px-3 py-2 sm:px-4 bg-gray-900/80 border-b border-gray-700 flex-shrink-0">
        <h3 class="text-white font-semibold text-sm sm:text-base truncate pr-2">Drone Video – Cesium & overlay</h3>
        <button type="button" id="modalDroneClose" class="p-2 text-gray-400 hover:text-white rounded-lg transition-colors flex-shrink-0" aria-label="Kapat">
            <span class="material-symbols-outlined text-2xl">close</span>
        </button>
    </div>
    <div class="modal-drone-content flex-1 overflow-y-auto p-3 sm:p-4 space-y-4 min-h-0 flex flex-col">
        <section class="border-b border-gray-700 pb-4 flex-shrink-0">
            <h4 class="text-white font-semibold mb-1 flex items-center gap-2">
                <span class="material-symbols-outlined text-xl text-indigo-400">videocam</span>
                1. Video kaynağı
            </h4>
            <p class="text-gray-400 text-sm mb-4">İçerik kütüphanesinden video seçerek düzenleyebilir veya parsel JSON yükledikten sonra Cesium ile 3D video oluşturabilirsiniz. Seçilen video üzerine adım 2'de overlay (FFmpeg) eklenebilir.</p>
            <div id="droneVideoStep1Form" class="space-y-4">
                <div id="droneVideoJsonUyari" class="hidden text-amber-400/90 text-sm flex items-center gap-2 rounded-lg bg-amber-500/10 border border-amber-500/30 px-3 py-2">
                    <span class="material-symbols-outlined text-lg">info</span>
                    <span>3D drone video oluşturmak için önce sayfada parsel JSON yükleyin; ardından bu pencerede "Cesium ile video oluştur" seçeneği görünür.</span>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <span id="droneVideoCesiumWrap" class="hidden flex flex-wrap items-center gap-3">
                        <button type="button" id="btnCesiumVideoOlustur" class="inline-flex items-center gap-2 px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition-colors">
                            <span class="material-symbols-outlined text-xl">videocam</span>
                            Cesium ile video oluştur ve yükle
                        </button>
                        <span class="text-gray-500 dark:text-gray-400 text-sm">veya</span>
                    </span>
                    <button type="button" id="btnKutuphanedenVideoSec" class="inline-flex items-center gap-2 px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition-colors">
                        <span class="material-symbols-outlined text-xl">video_library</span>
                        İçerik kütüphanesinden seç
                    </button>
                </div>
                <div id="droneVideoKutuphanePanel" class="hidden mt-3 p-4 rounded-xl bg-gray-800/80 border border-gray-600">
                    <p class="text-gray-400 text-sm mb-3">Videolardan birini seçin:</p>
                    <div id="droneVideoKutuphaneListe" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 max-h-[280px] overflow-y-auto">
                        <!-- JS ile doldurulacak -->
                    </div>
                    <div id="droneVideoKutuphaneYukleniyor" class="hidden py-6 text-center text-gray-400 text-sm">Yükleniyor...</div>
                    <div id="droneVideoKutuphaneBos" class="hidden py-6 text-center text-gray-500 text-sm">İçerik kütüphanesinde video bulunamadı.</div>
                    <div class="mt-3 flex justify-between items-center">
                        <button type="button" id="btnKutuphanePanelKapat" class="text-gray-400 hover:text-white text-sm">Kapat</button>
                        <span id="droneVideoKutuphaneSayfa" class="text-gray-500 text-xs"></span>
                    </div>
                </div>
            </div>
            <div id="droneVideoStep1Preview" class="hidden mt-4 space-y-3">
                <p class="text-emerald-400 text-sm font-medium flex items-center gap-2"><span class="material-symbols-outlined">check_circle</span> Video seçildi. Aşağıda önizleyebilir veya adım 2 ile üzerine yazdırma yapabilirsiniz.</p>
                <div class="aspect-[9/16] max-w-[280px] rounded-xl overflow-hidden bg-gray-800 border border-gray-600">
                    <video id="droneVideoPreviewEl" class="w-full h-full object-cover" controls playsinline></video>
                </div>
            </div>
        </section>
        <section id="modalOverlaySection" class="hidden flex flex-col flex-1 min-h-0 w-full border-t border-gray-700 pt-4">
            <h4 class="text-white font-semibold mb-1 flex items-center gap-2">
                <span class="material-symbols-outlined text-xl text-indigo-400">timeline</span>
                2. Overlay ekle (FFmpeg)
            </h4>
            <p class="text-gray-400 text-sm mb-4">Overlay alanlarını doldurup "Overlay ekle ve kaydet" ile videoyu sunucuda işleyebilirsiniz (FFmpeg gerekli).</p>
            <div id="overlayForm" class="space-y-4 mb-4 p-4 rounded-xl bg-gray-800/80 border border-gray-600">
                <div>
                    <label for="overlayLocation" class="block text-sm font-medium text-gray-300 mb-1">Konum (videoda sol üstte)</label>
                    <input type="text" id="overlayLocation" class="w-full px-3 py-2 rounded-lg border border-gray-600 bg-gray-700 text-white text-sm placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="İl / İlçe / Mahalle, Ada X Parsel Y">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label for="overlayNearby1" class="block text-sm font-medium text-gray-300 mb-1">Yakın lokasyon 1</label>
                        <input type="text" id="overlayNearby1" class="w-full px-3 py-2 rounded-lg border border-gray-600 bg-gray-700 text-white text-sm placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Yakın lokasyon 1">
                    </div>
                    <div>
                        <label for="overlayNearby2" class="block text-sm font-medium text-gray-300 mb-1">Yakın lokasyon 2</label>
                        <input type="text" id="overlayNearby2" class="w-full px-3 py-2 rounded-lg border border-gray-600 bg-gray-700 text-white text-sm placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Yakın lokasyon 2">
                    </div>
                    <div>
                        <label for="overlayNearby3" class="block text-sm font-medium text-gray-300 mb-1">Yakın lokasyon 3</label>
                        <input type="text" id="overlayNearby3" class="w-full px-3 py-2 rounded-lg border border-gray-600 bg-gray-700 text-white text-sm placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Yakın lokasyon 3">
                    </div>
                </div>
                <div>
                    <label for="overlayAiDescription" class="block text-sm font-medium text-gray-300 mb-1">Yapay zeka açıklaması</label>
                    <div class="flex gap-2">
                        <textarea id="overlayAiDescription" rows="2" class="flex-1 px-3 py-2 rounded-lg border border-gray-600 bg-gray-700 text-white text-sm placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" placeholder="Açıklama metni..."></textarea>
                        <button type="button" id="btnOverlayAiGenerate" class="flex-shrink-0 self-start inline-flex items-center gap-1.5 px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-medium transition-colors" title="AI ile oluştur">
                            <span class="material-symbols-outlined text-base">auto_awesome</span>
                            AI ile oluştur
                        </button>
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-2 border-t border-gray-600">
                    <input type="checkbox" id="overlayAutoCaptions" class="rounded border-gray-500 bg-gray-700 text-indigo-500 focus:ring-indigo-500">
                    <label for="overlayAutoCaptions" class="text-sm text-gray-300">Videodaki konuşmayı altyazı yap (ileride)</label>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2 mb-2">
                <button type="button" id="btnOverlayApplySave" class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-medium transition-colors disabled:opacity-50">
                    <span class="material-symbols-outlined text-base">smart_display</span>
                    Overlay ekle ve kaydet
                </button>
            </div>
        </section>
    </div>
</div>

<!-- Video oluşturuluyor overlay -->
<div id="videoOlusturuluyorOverlay" class="hidden fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-black/95" style="display: none;">
    <div class="bg-gray-900 rounded-xl border border-gray-700 p-6 max-w-sm w-full mx-4 shadow-xl">
        <h3 class="text-white font-semibold text-lg mb-4">Video oluşturuluyor</h3>
        <div class="mb-4 rounded-lg overflow-hidden bg-gray-800" style="width:270px; height:480px; margin:0 auto;">
            <div id="cesiumVideoWrapper" style="width:1080px; height:1920px; transform:scale(0.25); transform-origin:0 0;">
                <div id="cesiumVideoContainer" style="width:100%; height:100%;"></div>
            </div>
        </div>
        <div class="mb-2">
            <span class="text-gray-400 text-sm">İlerleme</span>
            <span id="videoOlusturProgressPct" class="text-indigo-400 text-sm ml-2">0%</span>
        </div>
        <div class="w-full h-2 bg-gray-700 rounded-full overflow-hidden mb-2">
            <div id="videoOlusturProgressBar" class="h-full bg-indigo-600 transition-all duration-300" style="width:0%;"></div>
        </div>
        <p id="videoOlusturDurum" class="text-gray-500 text-sm mb-4">Hazırlanıyor...</p>
        <button type="button" id="videoOlusturIptal" class="w-full px-4 py-2 rounded-lg bg-gray-700 text-white hover:bg-gray-600 text-sm">İptal</button>
    </div>
</div>

<div id="hataAlani" class="hidden mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
    <p id="hataMesaji" class="text-sm font-medium text-red-800 dark:text-red-200"></p>
</div>

<div id="yukleniyor" class="hidden mb-6 p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
    <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Sorgulanıyor...</p>
</div>

<div class="mt-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Yüklenen Video Geçmişi</h2>
    <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">
        Oluşturulan videolar MP4 formatında içerik kütüphanesine kaydedilir ve aşağıda listelenir.
    </p>
    <?php if (!empty($sonDroneVideolari)): ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <?php foreach ($sonDroneVideolari as $v): ?>
        <?php
        $relPath = trim($v['file_path'] ?? '');
        $directFileUrl = $relPath !== '' ? site_url('uploads/' . $relPath) : '';
        $storedUrl = trim($v['file_url'] ?? '');
        if ($storedUrl !== '' && preg_match('/^https?:\/\//', $storedUrl) && strpos($storedUrl, 'admin.php') === false) {
            $vidUrl = $storedUrl;
        } elseif ($directFileUrl !== '') {
            $vidUrl = $directFileUrl;
        } else {
            $vidUrl = '';
        }
        $vidTitle = esc_attr($v['original_name'] ?? 'Video');
        ?>
        <div class="rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600 hover:border-indigo-500 transition-colors flex flex-col">
            <button type="button" class="video-lightbox-trigger w-full block group text-left cursor-pointer border-0 p-0 bg-transparent" data-video-url="<?php echo esc_attr($vidUrl); ?>" data-video-title="<?php echo $vidTitle; ?>">
                <div class="aspect-video bg-gray-900 flex items-center justify-center">
                    <span class="material-symbols-outlined text-4xl text-gray-500 group-hover:text-indigo-400">videocam</span>
                </div>
            </button>
            <div class="p-2 bg-gray-50 dark:bg-gray-700/50 flex-1 flex flex-col">
                <p class="text-xs text-gray-700 dark:text-gray-300 truncate mb-1" title="<?php echo $vidTitle; ?>"><?php echo esc_html($v['original_name'] ?? 'Video'); ?></p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3"><?php echo !empty($v['created_at']) ? date('d.m.Y H:i', strtotime($v['created_at'])) : ''; ?></p>
                <div class="flex flex-col gap-2 mt-auto">
                    <div class="flex gap-2">
                        <button type="button" class="video-lightbox-trigger flex-1 inline-flex items-center justify-center gap-1 px-2 py-1.5 text-xs font-medium rounded-md bg-indigo-600 text-white hover:bg-indigo-700 transition-colors border-0 cursor-pointer" data-video-url="<?php echo esc_attr($vidUrl); ?>" data-video-title="<?php echo $vidTitle; ?>">
                            <span class="material-symbols-outlined text-sm">play_circle</span>
                            İzle
                        </button>
                        <a href="<?php echo esc_attr($vidUrl); ?>" download="<?php echo esc_attr($v['original_name'] ?? 'video.mp4'); ?>" class="inline-flex items-center justify-center gap-1 px-2 py-1.5 text-xs font-medium rounded-md bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">
                            <span class="material-symbols-outlined text-sm">download</span>
                            İndir
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <a href="<?php echo admin_url('media'); ?>" class="inline-flex items-center gap-2 mt-4 text-sm text-indigo-500 hover:text-indigo-600">Tüm videoları gör <span class="material-symbols-outlined text-lg">arrow_forward</span></a>
    <?php else: ?>
    <p class="text-gray-500 dark:text-gray-400 text-sm">Henüz kayıtlı video yok. 3D Dron Görünümü ile video oluşturduğunuzda burada listelenecek.</p>
    <?php endif; ?>
</div>

<!-- Video izleme lightbox -->
<div id="videoLightbox" class="hidden fixed inset-0 z-[9998] bg-black/90 flex items-center justify-center p-4" aria-hidden="true">
    <div class="relative w-full max-w-4xl max-h-[90vh] flex flex-col">
        <div class="relative bg-black rounded-lg overflow-hidden shadow-2xl aspect-video max-h-[80vh] flex items-center justify-center">
            <video id="videoLightboxPlayer" controls class="w-full h-full" playsinline></video>
            <button type="button" id="videoLightboxClose" class="absolute top-2 right-2 z-10 p-1.5 bg-black/60 hover:bg-black/80 text-white rounded-lg transition-colors" title="Kapat">
                <span class="material-symbols-outlined text-2xl">close</span>
            </button>
        </div>
        <p id="videoLightboxTitle" class="mt-2 text-sm text-gray-400 truncate text-center"></p>
    </div>
</div>

<script>
(function() {
    var baseUrl = <?php echo json_encode($baseUrl); ?>;
    var mediaListApiUrl = <?php echo json_encode($mediaListApiUrl); ?>;
    var uploadsBaseUrl = <?php echo json_encode($uploadsBaseUrl); ?>;
    var sep = baseUrl.indexOf('?') !== -1 ? '&' : '?';
    var mapboxToken = <?php echo json_encode(trim($settings['mapbox_access_token'] ?? '')); ?>;
    var googleMapsApiKey = <?php echo json_encode(trim($settings['google_maps_api_key'] ?? '')); ?>;
    var cesiumIonToken = <?php echo json_encode(trim($settings['cesium_ion_access_token'] ?? '')); ?>;

    function escapeHtml(s) {
        var div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
    }

    function formatParselLocation(info) {
        if (!info) return '';
        var parts = [info.il_adi, info.ilce_adi, info.mahalle_adi].filter(function(v) { return v && String(v).trim() !== ''; });
        var line = parts.join(' / ');
        var ada = String(info.ada || '').trim();
        var parsel = String(info.parsel_no || '').trim();
        if (ada || parsel) line += (line ? ', ' : '') + (ada ? 'Ada ' + ada : '') + (ada && parsel ? ' ' : '') + (parsel ? 'Parsel ' + parsel : '');
        return line;
    }

    function fillOverlayLocationFromParsel() {
        var el = document.getElementById('overlayLocation');
        if (!el || !window.currentParselInfo) return;
        el.value = formatParselLocation(window.currentParselInfo);
    }

    (function initVideoLightbox() {
        var lb = document.getElementById('videoLightbox');
        var player = document.getElementById('videoLightboxPlayer');
        var titleEl = document.getElementById('videoLightboxTitle');
        var btnClose = document.getElementById('videoLightboxClose');
        function openLightbox(url, title) {
            if (!url || !player || !lb) return;
            player.pause();
            player.src = url;
            player.load();
            if (titleEl) titleEl.textContent = title || '';
            lb.classList.remove('hidden');
            lb.setAttribute('aria-hidden', 'false');
            lb.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            player.play().catch(function() {});
        }
        function closeLightbox() {
            if (player) { player.pause(); player.removeAttribute('src'); }
            if (lb) {
                lb.classList.add('hidden');
                lb.setAttribute('aria-hidden', 'true');
                lb.style.display = 'none';
            }
            document.body.style.overflow = '';
        }
        document.addEventListener('click', function(e) {
            var t = e.target.closest('.video-lightbox-trigger');
            if (t) {
                e.preventDefault();
                openLightbox(t.getAttribute('data-video-url'), t.getAttribute('data-video-title'));
            }
        });
        if (btnClose) btnClose.addEventListener('click', closeLightbox);
        if (lb) {
            lb.addEventListener('click', function(e) {
                if (e.target === lb) closeLightbox();
            });
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && lb && !lb.classList.contains('hidden')) closeLightbox();
        });
    })();

    var sonucAlani = document.getElementById('sonucAlani');
    var sonucIcerik = document.getElementById('sonucIcerik');
    var parselHaritaWrap = document.getElementById('parselHaritaWrap');
    var hataAlani = document.getElementById('hataAlani');
    var hataMesaji = document.getElementById('hataMesaji');
    var yukleniyor = document.getElementById('yukleniyor');

    function roundCoord(c) { return Math.round(c * 1e5) / 1e5; }
    function getCenterFromGeom(geom) {
        var ring = geom.type === 'Polygon' && geom.coordinates && geom.coordinates[0] ? geom.coordinates[0] : [];
        if (geom.type === 'MultiPolygon' && geom.coordinates && geom.coordinates[0] && geom.coordinates[0][0]) ring = geom.coordinates[0][0];
        if (ring.length === 0) return { lng: 29.1, lat: 36.7 };
        var lng = 0, lat = 0;
        for (var i = 0; i < ring.length; i++) {
            lng += ring[i][0];
            lat += ring[i][1];
        }
        return { lng: lng / ring.length, lat: lat / ring.length };
    }
    function getBoundingSphereFromGeom(geom, Cesium) {
        var ring = [];
        if (geom.type === 'Polygon' && geom.coordinates && geom.coordinates[0]) ring = geom.coordinates[0];
        else if (geom.type === 'MultiPolygon' && geom.coordinates && geom.coordinates[0] && geom.coordinates[0][0]) ring = geom.coordinates[0][0];
        if (ring.length === 0) return null;
        var center = getCenterFromGeom(geom);
        var R = 6371000;
        var toRad = Math.PI / 180;
        var maxDist = 0;
        for (var i = 0; i < ring.length; i++) {
            var dlat = (ring[i][1] - center.lat) * toRad;
            var dlon = (ring[i][0] - center.lng) * toRad * Math.cos(center.lat * toRad);
            var d = R * Math.sqrt(dlat * dlat + dlon * dlon);
            if (d > maxDist) maxDist = d;
        }
        var radius = Math.max(maxDist * 1.2, 80);
        return { sphere: new Cesium.BoundingSphere(Cesium.Cartesian3.fromDegrees(center.lng, center.lat, 0), radius), range: Math.max(radius * 2.15, 120) };
    }
    function simplifyRing(ring, maxPoints) {
        maxPoints = maxPoints || 80;
        if (ring.length <= maxPoints) return ring.map(function(p) { return [roundCoord(p[0]), roundCoord(p[1])]; });
        var step = ring.length / maxPoints;
        var out = [];
        for (var i = 0; i < ring.length && out.length < maxPoints; i += step) {
            out.push([roundCoord(ring[i][0]), roundCoord(ring[i][1])]);
        }
        if (out.length > 0 && (out[out.length - 1][0] !== ring[ring.length - 1][0] || out[out.length - 1][1] !== ring[ring.length - 1][1])) {
            out.push([roundCoord(ring[ring.length - 1][0]), roundCoord(ring[ring.length - 1][1])]);
        }
        return out;
    }
    function loadGoogleMapsScript() {
        if (window.google && window.google.maps) return Promise.resolve();
        return new Promise(function(resolve, reject) {
            var done = false;
            function finish(err) {
                if (done) return;
                done = true;
                if (err) reject(err); else resolve();
            }
            var t = setTimeout(function() { finish(new Error('Google Maps zaman aşımı.')); }, 15000);
            window._googleMapsResolve = function() { clearTimeout(t); finish(); };
            var s = document.createElement('script');
            s.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(googleMapsApiKey) + '&callback=_googleMapsResolve';
            s.onerror = function() { clearTimeout(t); finish(new Error('Google Maps script yüklenemedi')); };
            document.head.appendChild(s);
        });
    }
    function geoJsonToGooglePaths(geom) {
        var rings = [];
        if (geom.type === 'Polygon' && geom.coordinates && geom.coordinates[0]) {
            rings.push(geom.coordinates[0].map(function(p) { return { lat: p[1], lng: p[0] }; }));
        } else if (geom.type === 'MultiPolygon' && geom.coordinates) {
            geom.coordinates.forEach(function(poly) {
                if (poly[0]) rings.push(poly[0].map(function(p) { return { lat: p[1], lng: p[0] }; }));
            });
        }
        return rings;
    }
    function geoJsonToGoogleBounds(geom) {
        var minLat = 90, maxLat = -90, minLng = 180, maxLng = -180;
        function addRing(ring) {
            for (var i = 0; i < ring.length; i++) {
                var lat = ring[i][1], lng = ring[i][0];
                if (lat < minLat) minLat = lat;
                if (lat > maxLat) maxLat = lat;
                if (lng < minLng) minLng = lng;
                if (lng > maxLng) maxLng = lng;
            }
        }
        if (geom.type === 'Polygon' && geom.coordinates && geom.coordinates[0]) addRing(geom.coordinates[0]);
        else if (geom.type === 'MultiPolygon' && geom.coordinates) geom.coordinates.forEach(function(p) { if (p[0]) addRing(p[0]); });
        return { ne: { lat: maxLat, lng: maxLng }, sw: { lat: minLat, lng: minLng } };
    }
    function initParselMapLeaflet(geojson) {
        var mapEl = document.getElementById('parselHarita');
        if (!mapEl || !window.L) return;
        var map = L.map('parselHarita').setView([39, 35], 6);
        if (mapboxToken) {
            L.tileLayer('https://api.mapbox.com/styles/v1/mapbox/satellite-v9/tiles/256/{z}/{x}/{y}?access_token=' + mapboxToken, {
                attribution: '© Mapbox © OpenStreetMap',
                maxZoom: 20,
                maxNativeZoom: 19
            }).addTo(map);
        } else {
            L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Esri, Maxar, Earthstar Geographics',
                maxZoom: 19,
                maxNativeZoom: 19
            }).addTo(map);
        }
        var layer = L.geoJSON(geojson, { style: { color: '#E040FB', weight: 4, opacity: 1, fillColor: '#E040FB', fillOpacity: 0 } }).addTo(map);
        map.fitBounds(layer.getBounds());
        window.parselMap = map;
    }

    function showDetayResult(res) {
        if (typeof window.parselMap !== 'undefined' && window.parselMap) {
            if (typeof window.parselMap.remove === 'function') window.parselMap.remove();
            window.parselMap = null;
        }
        var prevMapEl = document.getElementById('parselHarita');
        if (prevMapEl) prevMapEl.innerHTML = '';
        if (res.success && res.data) {
            var d = res.data;
            if (d.from_cbs) {
                var html = '<table class="w-full text-sm text-left text-gray-700 dark:text-gray-300"><tbody>';
                if (d.tasinmaz_no !== undefined && d.tasinmaz_no !== '') html += '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">Taşınmaz No</th><td class="py-2">' + escapeHtml(String(d.tasinmaz_no)) + '</td></tr>';
                html += '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">Ada</th><td class="py-2">' + escapeHtml(String(d.ada || '')) + '</td></tr>';
                html += '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">Parsel</th><td class="py-2">' + escapeHtml(String(d.parsel_no || '')) + '</td></tr>';
                if (d.alan_m2 !== undefined && d.alan_m2 !== null && d.alan_m2 !== '') html += '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">Alan (m²)</th><td class="py-2">' + escapeHtml(String(d.alan_m2)) + '</td></tr>';
                if (d.nitelik) html += '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">Nitelik</th><td class="py-2">' + escapeHtml(String(d.nitelik)) + '</td></tr>';
                if (d.il_adi) html += '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">İl</th><td class="py-2">' + escapeHtml(String(d.il_adi)) + '</td></tr>';
                if (d.ilce_adi) html += '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">İlçe</th><td class="py-2">' + escapeHtml(String(d.ilce_adi)) + '</td></tr>';
                if (d.mahalle_adi) html += '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">Mahalle / Köy</th><td class="py-2">' + escapeHtml(String(d.mahalle_adi)) + '</td></tr>';
                html += '</tbody></table>';
                sonucIcerik.innerHTML = html;
                window.currentParselInfo = { il_adi: d.il_adi || '', ilce_adi: d.ilce_adi || '', mahalle_adi: d.mahalle_adi || '', ada: String(d.ada || ''), parsel_no: String(d.parsel_no || ''), alan_m2: d.alan_m2, nitelik: d.nitelik || '' };
                fillOverlayLocationFromParsel();
                if (d.geometry || d.geojson) {
                    parselHaritaWrap.classList.remove('hidden');
                    var geojson = d.geojson || { type: 'Feature', geometry: d.geometry, properties: {} };
                    window.currentParselGeojson = geojson;
                    window.parselBoundingSphere = null;
                    window.parselOrbitRange = null;
                    var btn3DWrap = document.getElementById('parsel3DBtnWrap');
                    if (btn3DWrap) btn3DWrap.classList.remove('hidden');
                    var geom = (geojson.type === 'Feature' ? geojson.geometry : (geojson.type === 'FeatureCollection' && geojson.features && geojson.features[0] ? geojson.features[0].geometry : geojson.geometry)) || geojson.geometry;
                    var mapEl = document.getElementById('parselHarita');
                    if (window.parselMap) {
                        if (typeof window.parselMap.remove === 'function') window.parselMap.remove();
                        window.parselMap = null;
                    }
                    if (mapEl) mapEl.innerHTML = '';
                    setTimeout(function() {
                        if (!mapEl) return;
                        if (googleMapsApiKey) {
                            loadGoogleMapsScript().then(function() {
                                var center = getCenterFromGeom(geom);
                                var paths = geoJsonToGooglePaths(geom);
                                if (paths.length === 0) {
                                    initParselMapLeaflet(geojson);
                                    return;
                                }
                                window.parselMap = new google.maps.Map(mapEl, {
                                    center: { lat: center.lat, lng: center.lng },
                                    zoom: 17,
                                    tilt: 47,
                                    heading: 0,
                                    mapTypeId: google.maps.MapTypeId.SATELLITE,
                                    disableDefaultUI: false,
                                    zoomControl: false,
                                    mapTypeControl: true,
                                    scaleControl: false,
                                    fullscreenControl: false,
                                    streetViewControl: false
                                });
                                var polygon = new google.maps.Polygon({
                                    paths: paths,
                                    strokeColor: '#E040FB',
                                    strokeWeight: 4,
                                    strokeOpacity: 1,
                                    fillColor: '#E040FB',
                                    fillOpacity: 0
                                });
                                polygon.setMap(window.parselMap);
                                var b = geoJsonToGoogleBounds(geom);
                                window.parselMap.fitBounds(new google.maps.LatLngBounds(
                                    new google.maps.LatLng(b.sw.lat, b.sw.lng),
                                    new google.maps.LatLng(b.ne.lat, b.ne.lng)
                                ));
                            }).catch(function() {
                                initParselMapLeaflet(geojson);
                            });
                        } else {
                            initParselMapLeaflet(geojson);
                        }
                    }, 100);
                } else {
                    parselHaritaWrap.classList.add('hidden');
                    var btn3DWrap = document.getElementById('parsel3DBtnWrap');
                    if (btn3DWrap) btn3DWrap.classList.add('hidden');
                }
            } else {
                var q = d.query || {};
                var ilAdi = (q.il_adi !== undefined ? q.il_adi : (q.il_kodu || ''));
                var ilceAdi = (q.ilce_adi !== undefined ? q.ilce_adi : (q.ilce_kodu || ''));
                var mahalleAdi = (q.mahalle_adi !== undefined ? q.mahalle_adi : (q.mahalle_kodu || ''));
                var url = d.parselsorgu_url || 'https://parselsorgu.tkgm.gov.tr';
                sonucIcerik.innerHTML =
                    '<p class="text-amber-700 dark:text-amber-300 text-sm mb-4">Bu ada/parsel için CBS servisi şu an detay döndürmedi.</p>' +
                    '<table class="w-full text-sm text-left text-gray-700 dark:text-gray-300 mb-4"><tbody>' +
                    '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">İl</th><td class="py-2">' + escapeHtml(ilAdi) + '</td></tr>' +
                    '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">İlçe</th><td class="py-2">' + escapeHtml(ilceAdi) + '</td></tr>' +
                    '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">Mahalle / Köy</th><td class="py-2">' + escapeHtml(mahalleAdi) + '</td></tr>' +
                    '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">Ada</th><td class="py-2">' + escapeHtml(String(q.ada || '')) + '</td></tr>' +
                    '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">Parsel</th><td class="py-2">' + escapeHtml(String(q.parsel || '')) + '</td></tr>' +
                    '</tbody></table>' +
                    '<a href="' + escapeHtml(url) + '" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">Parsel Sorgu (TKGM) sayfasında kontrol et <span class="material-symbols-outlined text-lg">open_in_new</span></a>';
                parselHaritaWrap.classList.add('hidden');
                var btn3DWrap = document.getElementById('parsel3DBtnWrap');
                if (btn3DWrap) btn3DWrap.classList.add('hidden');
                window.currentParselInfo = { il_adi: ilAdi, ilce_adi: ilceAdi, mahalle_adi: mahalleAdi, ada: String(q.ada || ''), parsel_no: String(q.parsel || ''), alan_m2: null, nitelik: '' };
                fillOverlayLocationFromParsel();
                window.currentParselGeojson = null;
                window.parselBoundingSphere = null;
                window.parselOrbitRange = null;
            }
            sonucAlani.classList.remove('hidden');
        } else {
            hataMesaji.textContent = res.message || 'Parsel detayı alınamadı.';
            hataAlani.classList.remove('hidden');
        }
    }

    (function initJsonSorgula() {
        var btn = document.getElementById('btnJsonSorgula');
        var fileInput = document.getElementById('jsonFileInput');
        var pasteArea = document.getElementById('jsonPasteArea');
        if (!btn) return;
        btn.addEventListener('click', function() {
            hataAlani.classList.add('hidden');
            sonucAlani.classList.add('hidden');
            var jsonString = '';
            if (fileInput.files && fileInput.files[0]) {
                var fr = new FileReader();
                fr.onload = function() {
                    try {
                        jsonString = fr.result;
                        if (!jsonString || !jsonString.trim()) {
                            hataMesaji.textContent = 'Dosya boş veya okunamadı.';
                            hataAlani.classList.remove('hidden');
                            return;
                        }
                        sendJsonToBackend(jsonString);
                    } catch (e) {
                        hataMesaji.textContent = 'Dosya içeriği geçerli metin değil.';
                        hataAlani.classList.remove('hidden');
                    }
                };
                fr.onerror = function() {
                    hataMesaji.textContent = 'Dosya okunamadı.';
                    hataAlani.classList.remove('hidden');
                };
                fr.readAsText(fileInput.files[0]);
                return;
            }
            jsonString = pasteArea && pasteArea.value ? pasteArea.value.trim() : '';
            if (!jsonString) {
                hataMesaji.textContent = 'Lütfen bir .json dosyası seçin veya GeoJSON metnini yapıştırın.';
                hataAlani.classList.remove('hidden');
                return;
            }
            sendJsonToBackend(jsonString);
        });
        function sendJsonToBackend(jsonString) {
            btn.disabled = true;
            if (yukleniyor) yukleniyor.classList.remove('hidden');
            var fd = new FormData();
            fd.append('action', 'load_json');
            fd.append('json', jsonString);
            fetch(baseUrl + sep + 'action=load_json', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            }).then(function(r) { return r.json(); }).then(function(res) {
                if (yukleniyor) yukleniyor.classList.add('hidden');
                btn.disabled = false;
                showDetayResult(res);
            }).catch(function(err) {
                if (yukleniyor) yukleniyor.classList.add('hidden');
                btn.disabled = false;
                hataMesaji.textContent = 'İstek başarısız: ' + (err.message || 'Ağ hatası');
                hataAlani.classList.remove('hidden');
            });
        }
    })();

    var cesium3DViewer = null;
    var cesiumLoadPromise = null;
    var RANGE_MULT_GLOBAL = 1.5;
    var CAMERA_PRESETS = {
        yaklasma: { heading: 0, pitchDeg: -52, slug: 'yaklasma', motion: 'approach', rangeStartMult: 3.4, rangeEndMult: 1.5, flyoverDurationMult: 1 },
        yakinCekim: { heading: 0, pitchDeg: -50, rangeMult: 1.6, slug: 'yakinCekim', motion: 'orbit', orbitStart: 0, orbitEnd: Math.PI * 0.2 },
        yakinCekimHafif: { heading: 0, pitchDeg: -48, rangeMult: 1.6, slug: 'yakinCekimHafif', motion: 'orbit', orbitStart: 0, orbitEnd: Math.PI * 0.15 },
        soldanSaga60: { heading: Math.PI * 1.5, pitchDeg: -52, rangeMult: 1.6, slug: 'soldanSaga60', motion: 'orbit', orbitStart: Math.PI * 0.5, orbitEnd: Math.PI * 2 },
        sagdanSola60: { heading: Math.PI * 1.5, pitchDeg: -52, rangeMult: 1.6, slug: 'sagdanSola60', motion: 'orbit', orbitStart: Math.PI * 0.5, orbitEnd: Math.PI * 2 },
        tepeden: { heading: Math.PI * 0.5, pitchDeg: -70, rangeMult: 1.6, slug: 'tepeden', motion: 'orbit', orbitStart: Math.PI * 0.5, orbitEnd: Math.PI * 0.25 },
        kuzey: { heading: Math.PI, pitchDeg: -45, rangeMult: 1.6, slug: 'kuzey', motion: 'flyover', flyoverDir: 'north-south', flyoverStartMult: 2.8, flyoverEndMult: -1.2, flyoverDurationMult: 1 },
        guney: { heading: 0, pitchDeg: -45, rangeMult: 1.6, slug: 'guney', motion: 'flyover', flyoverDir: 'south-north', flyoverStartMult: 2.8, flyoverEndMult: -1.2, flyoverDurationMult: 1 },
        uzak: { heading: 0, pitchDeg: -46, rangeMult: 1.6, slug: 'uzak', motion: 'orbit', orbitStart: 0, orbitEnd: Math.PI * 0.5, orbitDurationMult: 3 },
        uzak90: { heading: 0, pitchDeg: -46, rangeMult: 1.6, slug: 'uzak90', motion: 'orbit', orbitStart: 0, orbitEnd: Math.PI * 0.75 },
        uzak180: { heading: 0, pitchDeg: -48, rangeMult: 2, slug: 'uzak180', motion: 'orbit', orbitStart: 0, orbitEnd: Math.PI * 0.5 },
        egimli: { heading: 0, pitchDeg: -52, rangeMult: 1.6, slug: 'egimli', motion: 'orbit', orbitStart: 0, orbitEnd: Math.PI * 0.5 }
    };
    var DRONE_SEQUENCE_60 = [
        { presetKey: 'uzak', durationMs: 10000 },
        { presetKey: 'tepeden', durationMs: 10000 },
        { presetKey: 'kuzey', durationMs: 10000 },
        { presetKey: 'uzak180', durationMs: 10000 },
        { presetKey: 'guney', durationMs: 10000 }
    ];
    var DRONE_VIDEO_TOTAL_MS = DRONE_SEQUENCE_60.reduce(function(s, seg) { return s + (seg.durationMs || 0); }, 0);
    /** Cesium kayıt çözünürlüğü: 1080x1920 (dikey 9:16) - mobil uyumlu */
    var DRONE_VIDEO_WIDTH = 1080;
    var DRONE_VIDEO_HEIGHT = 1920;

    function loadCesium() {
        if (window.Cesium) return Promise.resolve();
        if (cesiumLoadPromise) return cesiumLoadPromise;
        cesiumLoadPromise = new Promise(function(resolve, reject) {
            window.CESIUM_BASE_URL = 'https://unpkg.com/cesium@1.114/Build/Cesium/';
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://unpkg.com/cesium@1.114/Build/Cesium/Widgets/widgets.css';
            link.onload = function() {
                var script = document.createElement('script');
                script.src = 'https://unpkg.com/cesium@1.114/Build/Cesium/Cesium.js';
                script.onload = function() {
                    if (cesiumIonToken && window.Cesium) {
                        window.Cesium.Ion.defaultAccessToken = cesiumIonToken;
                    }
                    resolve();
                };
                script.onerror = reject;
                document.head.appendChild(script);
            };
            link.onerror = reject;
            document.head.appendChild(link);
        });
        return cesiumLoadPromise;
    }

    function getFlyoverPositions(boundingSphere, baseRange, preset, Cesium) {
        var carto = Cesium.Cartographic.fromCartesian(boundingSphere.center);
        var centerLng = Cesium.Math.toDegrees(carto.longitude);
        var centerLat = Cesium.Math.toDegrees(carto.latitude);
        var range = baseRange * preset.rangeMult * RANGE_MULT_GLOBAL;
        var startMult = preset.flyoverStartMult != null ? preset.flyoverStartMult : 5.5;
        var endMult = preset.flyoverEndMult != null ? preset.flyoverEndMult : 8;
        var startOffsetDeg = (baseRange * startMult / 111320);
        var endOffsetDeg = (baseRange * endMult / 111320);
        var height = range;
        var dir = preset.flyoverDir || 'north-south';
        var startLat, endLat;
        if (dir === 'north-south') {
            startLat = centerLat + startOffsetDeg;
            endLat = centerLat - endOffsetDeg;
        } else {
            startLat = centerLat - startOffsetDeg;
            endLat = centerLat + endOffsetDeg;
        }
        return {
            startPos: Cesium.Cartesian3.fromDegrees(centerLng, startLat, height),
            endPos: Cesium.Cartesian3.fromDegrees(centerLng, endLat, height),
            heading: dir === 'north-south' ? Math.PI : 0,
            pitchRad: Cesium.Math.toRadians(preset.pitchDeg || -45)
        };
    }

    function applyCameraPreset(presetKey) {
        if (!cesium3DViewer || !window.Cesium) return;
        var preset = CAMERA_PRESETS[presetKey];
        if (!preset) return;
        var boundingSphere = window.parselBoundingSphere;
        var baseRange = window.parselOrbitRange;
        if (!boundingSphere || baseRange == null) {
            var geojson = window.currentParselGeojson;
            if (!geojson) return;
            var geom = (geojson.type === 'Feature' ? geojson.geometry : (geojson.type === 'FeatureCollection' && geojson.features && geojson.features[0] ? geojson.features[0].geometry : geojson.geometry)) || geojson.geometry;
            var bs = getBoundingSphereFromGeom(geom, window.Cesium);
            if (!bs) return;
            boundingSphere = bs.sphere;
            baseRange = bs.range;
        }
        var range = baseRange * (preset.rangeMult != null ? preset.rangeMult : preset.rangeStartMult) * RANGE_MULT_GLOBAL;
        var Cesium = window.Cesium;
        if (preset.motion === 'approach') {
            var approachRange = baseRange * (preset.rangeStartMult != null ? preset.rangeStartMult : 3) * RANGE_MULT_GLOBAL;
            cesium3DViewer.camera.lookAt(boundingSphere.center, new Cesium.HeadingPitchRange(preset.heading != null ? preset.heading : 0, Cesium.Math.toRadians(preset.pitchDeg || -48), approachRange));
        } else if (preset.motion === 'flyover') {
            var fp = getFlyoverPositions(boundingSphere, baseRange, preset, Cesium);
            cesium3DViewer.camera.setView({
                destination: fp.startPos,
                orientation: { heading: fp.heading, pitch: fp.pitchRad, roll: 0 }
            });
        } else {
            cesium3DViewer.camera.viewBoundingSphere(boundingSphere, new Cesium.HeadingPitchRange(preset.heading, Cesium.Math.toRadians(preset.pitchDeg), range));
        }
        if (cesium3DViewer.scene) cesium3DViewer.scene.requestRender();
    }

    function recordFullDroneSequence(totalMs, onProgress) {
        return new Promise(function(resolve, reject) {
            if (!cesium3DViewer || !cesium3DViewer.scene || !cesium3DViewer.scene.canvas || !window.Cesium) {
                reject(new Error('Cesium görünüm hazır değil'));
                return;
            }
            var Cesium = window.Cesium;
            var boundingSphere = window.parselBoundingSphere;
            var baseRange = window.parselOrbitRange;
            if (!boundingSphere || baseRange == null) {
                var geojson = window.currentParselGeojson;
                if (!geojson) { reject(new Error('Parsel verisi yok')); return; }
                var geom = (geojson.type === 'Feature' ? geojson.geometry : (geojson.type === 'FeatureCollection' && geojson.features && geojson.features[0] ? geojson.features[0].geometry : geojson.geometry)) || geojson.geometry;
                var bs = getBoundingSphereFromGeom(geom, Cesium);
                if (!bs) { reject(new Error('Bounding sphere hesaplanamadı')); return; }
                boundingSphere = bs.sphere;
                baseRange = bs.range;
            }
            applyCameraPreset(DRONE_SEQUENCE_60[0].presetKey);
            var RECORD_START_DELAY_MS = 5000;
            setTimeout(function() {
                var VIDEO_W = DRONE_VIDEO_WIDTH;
                var VIDEO_H = DRONE_VIDEO_HEIGHT;
                var container = document.getElementById('cesiumVideoContainer');
                var wrapper = document.getElementById('cesiumVideoWrapper');
                if (!container || !cesium3DViewer) {
                    reject(new Error('Cesium konteyneri bulunamadı'));
                    return;
                }
                /* Önizleme: Container overlay içinde kalır (wrapper scale 0.25 ile 270x480 gösterir).
                 * Tam çözünürlük için container ve wrapper boyutları 1080x1920 yapılıp resize çağrılır. */
                if (wrapper) {
                    wrapper.style.width = VIDEO_W + 'px';
                    wrapper.style.height = VIDEO_H + 'px';
                    wrapper.style.transform = 'scale(0.25)';
                    wrapper.style.transformOrigin = '0 0';
                }
                container.style.width = VIDEO_W + 'px';
                container.style.height = VIDEO_H + 'px';
                container.style.minWidth = VIDEO_W + 'px';
                container.style.minHeight = VIDEO_H + 'px';
                if (cesium3DViewer.resize) cesium3DViewer.resize();
                var canvas = cesium3DViewer.scene.canvas;
                if (!canvas || canvas.width < VIDEO_W || canvas.height < VIDEO_H) {
                    if (cesium3DViewer.resize) cesium3DViewer.resize();
                }
                if (!canvas.width || !canvas.height) {
                    if (wrapper) { wrapper.style.width = ''; wrapper.style.height = ''; wrapper.style.transform = ''; wrapper.style.transformOrigin = ''; }
                    container.style.width = container.style.height = container.style.minWidth = container.style.minHeight = '';
                    if (cesium3DViewer.resize) cesium3DViewer.resize();
                    reject(new Error('Canvas boyutu geçersiz'));
                    return;
                }
                var scaleCanvas = document.createElement('canvas');
                scaleCanvas.width = VIDEO_W;
                scaleCanvas.height = VIDEO_H;
                scaleCanvas.setAttribute('width', VIDEO_W);
                scaleCanvas.setAttribute('height', VIDEO_H);
                var scaleCtx = scaleCanvas.getContext('2d');
                var postRenderCallback = function() {
                    var cw = canvas.width, ch = canvas.height;
                    if (cw === VIDEO_W && ch === VIDEO_H) {
                        scaleCtx.drawImage(canvas, 0, 0);
                    } else {
                        scaleCtx.drawImage(canvas, 0, 0, cw, ch, 0, 0, VIDEO_W, VIDEO_H);
                    }
                };
                cesium3DViewer.scene.postRender.addEventListener(postRenderCallback);
                function restoreContainer() {
                    cesium3DViewer.scene.postRender.removeEventListener(postRenderCallback);
                    if (wrapper) { wrapper.style.width = ''; wrapper.style.height = ''; wrapper.style.transform = ''; wrapper.style.transformOrigin = ''; }
                    container.style.width = container.style.height = container.style.minWidth = container.style.minHeight = '';
                    if (cesium3DViewer.resize) cesium3DViewer.resize();
                }
                var stream = scaleCanvas.captureStream(30);
                if (!stream || !stream.getVideoTracks || stream.getVideoTracks().length === 0) {
                    restoreContainer();
                    reject(new Error('Canvas video akışı alınamadı'));
                    return;
                }
                var mime = 'video/webm';
                if (MediaRecorder.isTypeSupported('video/mp4; codecs=avc1')) mime = 'video/mp4; codecs=avc1';
                else if (MediaRecorder.isTypeSupported('video/mp4')) mime = 'video/mp4';
                else if (MediaRecorder.isTypeSupported('video/webm; codecs=h264')) mime = 'video/webm; codecs=h264';
                else if (MediaRecorder.isTypeSupported('video/webm; codecs=vp9')) mime = 'video/webm; codecs=vp9';
                else if (MediaRecorder.isTypeSupported('video/webm; codecs=vp8')) mime = 'video/webm; codecs=vp8';
                var chunks = [];
                var bitsPerSecond = 8000000;
                var rec;
                try {
                    rec = new MediaRecorder(stream, { mimeType: mime, videoBitsPerSecond: bitsPerSecond, audioBitsPerSecond: 0 });
                } catch (e0) {
                    mime = MediaRecorder.isTypeSupported('video/webm; codecs=vp8') ? 'video/webm; codecs=vp8' : 'video/webm';
                    bitsPerSecond = 6000000;
                    rec = new MediaRecorder(stream, { mimeType: mime, videoBitsPerSecond: bitsPerSecond, audioBitsPerSecond: 0 });
                }
                rec.ondataavailable = function(e) { if (e.data && e.data.size > 0) chunks.push(e.data); };
                rec.onstop = function() {
                    stream.getVideoTracks().forEach(function(t) { t.stop(); });
                    restoreContainer();
                    if (chunks.length === 0) {
                        reject(new Error('Kayıt verisi alınamadı'));
                        return;
                    }
                    var blob = new Blob(chunks, { type: mime });
                    resolve(blob);
                };
                rec.onerror = function(e) {
                    restoreContainer();
                    reject(new Error('Kayıt hatası: ' + (e.error ? e.error.message : 'bilinmeyen')));
                };
                try {
                    rec.start(500);
                } catch (eStart) {
                    restoreContainer();
                    reject(new Error('Kayıt başlatılamadı'));
                    return;
                }
                var dataInterval = setInterval(function() {
                    if (rec.state === 'recording') try { rec.requestData(); } catch (e) {}
                }, 500);
                var startTime = performance.now();
                var lastRenderTime = startTime;
                var TARGET_FRAME_MS = 34;
                var lerpResult = new Cesium.Cartesian3();
                var flyoverCache = {};
                function getFlyoverForPreset(presetKey) {
                    if (!flyoverCache[presetKey]) {
                        var p = CAMERA_PRESETS[presetKey];
                        if (p && p.motion === 'flyover') {
                            flyoverCache[presetKey] = getFlyoverPositions(boundingSphere, baseRange, p, Cesium);
                        }
                    }
                    return flyoverCache[presetKey];
                }
                function tick() {
                    var now = performance.now();
                    var elapsedMs = now - startTime;
                    if (typeof onProgress === 'function') {
                        var pct = Math.min(100, Math.round((elapsedMs / totalMs) * 100));
                        onProgress(pct, elapsedMs, totalMs);
                    }
                    if (elapsedMs >= totalMs) {
                        clearInterval(dataInterval);
                        if (rec.state === 'recording') {
                            try { rec.requestData(); } catch (e) {}
                            setTimeout(function() { if (rec.state === 'recording') rec.stop(); }, 200);
                        }
                        return;
                    }
                    if (now - lastRenderTime >= TARGET_FRAME_MS) {
                        lastRenderTime = now;
                        var segStartMs = 0;
                        var segmentIndex = 0;
                        for (var i = 0; i < DRONE_SEQUENCE_60.length; i++) {
                            if (elapsedMs < segStartMs + DRONE_SEQUENCE_60[i].durationMs) {
                                segmentIndex = i;
                                break;
                            }
                            segStartMs += DRONE_SEQUENCE_60[i].durationMs;
                        }
                        var seg = DRONE_SEQUENCE_60[segmentIndex];
                        var preset = CAMERA_PRESETS[seg.presetKey] || CAMERA_PRESETS.kuzey;
                        var segElapsed = elapsedMs - segStartMs;
                        var t = Math.min(1, segElapsed / seg.durationMs);
                        var range = baseRange * (preset.rangeMult != null ? preset.rangeMult : 1) * RANGE_MULT_GLOBAL;
                        var pitchRad = Cesium.Math.toRadians(preset.pitchDeg || -45);
                        var target = boundingSphere.center;
                        if (preset.motion === 'flyover') {
                            var fp = getFlyoverForPreset(seg.presetKey);
                            if (fp) {
                                var pos = Cesium.Cartesian3.lerp(fp.startPos, fp.endPos, t, lerpResult);
                                cesium3DViewer.camera.setView({
                                    destination: pos,
                                    orientation: { heading: fp.heading, pitch: fp.pitchRad, roll: 0 }
                                });
                            }
                        } else if (preset.motion === 'approach') {
                            var rangeStart = baseRange * (preset.rangeStartMult != null ? preset.rangeStartMult : 3) * RANGE_MULT_GLOBAL;
                            var rangeEnd = baseRange * (preset.rangeEndMult != null ? preset.rangeEndMult : 1.1) * RANGE_MULT_GLOBAL;
                            var approachRange = rangeStart + t * (rangeEnd - rangeStart);
                            cesium3DViewer.camera.lookAt(target, new Cesium.HeadingPitchRange(preset.heading != null ? preset.heading : 0, pitchRad, approachRange));
                        } else {
                            var orbitStart = preset.orbitStart != null ? preset.orbitStart : preset.heading;
                            var orbitEnd = preset.orbitEnd != null ? preset.orbitEnd : preset.heading + Math.PI * 0.5;
                            var heading = orbitStart + t * (orbitEnd - orbitStart);
                            cesium3DViewer.camera.viewBoundingSphere(boundingSphere, new Cesium.HeadingPitchRange(heading, pitchRad, range));
                        }
                        cesium3DViewer.scene.requestRender();
                    }
                    requestAnimationFrame(tick);
                }
                tick();
            }, RECORD_START_DELAY_MS);
        });
    }

    function initCesium3D(containerId) {
        containerId = containerId || 'cesiumVideoContainer';
        var container = document.getElementById(containerId);
        var geojson = window.currentParselGeojson;
        if (!container || !geojson || !window.Cesium) return;
        if (cesium3DViewer) {
            cesium3DViewer.destroy();
            cesium3DViewer = null;
        }
        var geom = (geojson.type === 'Feature' ? geojson.geometry : (geojson.type === 'FeatureCollection' && geojson.features && geojson.features[0] ? geojson.features[0].geometry : geojson.geometry)) || geojson.geometry;
        var Cesium = window.Cesium;
        if (cesiumIonToken) {
            Cesium.Ion.defaultAccessToken = cesiumIonToken;
        }
        var viewerOptions = {
            baseLayerPicker: false,
            geocoder: false,
            homeButton: false,
            sceneModePicker: false,
            navigationHelpButton: false,
            animation: false,
            timeline: false,
            fullscreenButton: false,
            vrButton: false,
            scene3DOnly: true,
            requestRenderMode: false,
            contextOptions: { webgl: { preserveDrawingBuffer: true } }
        };
        if (!cesiumIonToken) {
            viewerOptions.imageryProvider = new Cesium.UrlTemplateImageryProvider({
                url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                credit: 'Esri, Maxar, Earthstar Geographics'
            });
        }
        cesium3DViewer = new Cesium.Viewer(containerId, viewerOptions);
        if (cesium3DViewer.creditContainer) {
            cesium3DViewer.creditContainer.style.display = 'none';
        }
        var use3DTiles = !!googleMapsApiKey;
        var imageryMode = false;
        if (use3DTiles && !imageryMode) {
            if (typeof Cesium.createGooglePhotorealistic3DTileset === 'function') {
                Cesium.createGooglePhotorealistic3DTileset(googleMapsApiKey, { showCreditsOnScreen: false, maximumScreenSpaceError: 8 }).then(function(tileset) {
                    cesium3DViewer.scene.primitives.add(tileset);
                    window.cesium3DTileset = tileset;
                    return tileset.readyPromise;
                }).then(function() {
                    if (cesium3DViewer) cesium3DViewer.scene.globe.show = false;
                    finishInit();
                }).catch(function() {
                    if (cesium3DViewer) cesium3DViewer.scene.globe.show = true;
                    finishInit();
                });
            } else {
                try {
                    var tileset = cesium3DViewer.scene.primitives.add(new Cesium.Cesium3DTileset({
                        url: 'https://tile.googleapis.com/v1/3dtiles/root.json?key=' + encodeURIComponent(googleMapsApiKey),
                        showCreditsOnScreen: false,
                        maximumScreenSpaceError: 8
                    }));
                    window.cesium3DTileset = tileset;
                    tileset.readyPromise.then(function() {
                        cesium3DViewer.scene.globe.show = false;
                    }).catch(function() {
                        cesium3DViewer.scene.globe.show = true;
                    });
                } catch (e) {
                    cesium3DViewer.scene.globe.show = true;
                }
            }
        } else {
            cesium3DViewer.scene.globe.show = true;
        }
        requestAnimationFrame(function() {
            if (cesium3DViewer && cesium3DViewer.scene) cesium3DViewer.scene.requestRender();
            if (cesium3DViewer && cesium3DViewer.resize) cesium3DViewer.resize();
        });
        function addParselToScene(viewer, geom) {
            if (!geom || !geom.coordinates) return;
            var toRemove = [];
            viewer.entities.values.forEach(function(e) {
                if (e.name && (e.name === 'parsel-alan' || e.name === 'parsel-outline' || e.name === 'parsel-outline-glow')) toRemove.push(e);
            });
            toRemove.forEach(function(e) { viewer.entities.remove(e); });
            var ring = [];
            if (geom.type === 'Polygon' && geom.coordinates[0]) ring = geom.coordinates[0];
            else if (geom.type === 'MultiPolygon' && geom.coordinates[0] && geom.coordinates[0][0]) ring = geom.coordinates[0][0];
            if (ring.length < 2) return;
            var outlinePositions = ring.map(function(p) { return Cesium.Cartesian3.fromDegrees(p[0], p[1], 0); });
            outlinePositions.push(Cesium.Cartesian3.fromDegrees(ring[0][0], ring[0][1], 0));
            viewer.entities.add({
                name: 'parsel-outline-glow',
                polyline: {
                    positions: outlinePositions,
                    width: 10,
                    material: new Cesium.ColorMaterialProperty(Cesium.Color.fromCssColorString('rgba(224, 64, 251, 0.35)')),
                    clampToGround: true,
                    disableDepthTestDistance: Number.POSITIVE_INFINITY
                }
            });
            viewer.entities.add({
                name: 'parsel-outline',
                polyline: {
                    positions: outlinePositions,
                    width: 4,
                    material: new Cesium.ColorMaterialProperty(Cesium.Color.fromCssColorString('#E040FB')),
                    clampToGround: true,
                    disableDepthTestDistance: Number.POSITIVE_INFINITY
                }
            });
        }
        /** Video başlangıcı %70 zoom (çok yakın) – range küçültülür */
        var INITIAL_ZOOM_MULT = 0.95;
        function finishInit() {
            addParselToScene(cesium3DViewer, geom);
            var bs = getBoundingSphereFromGeom(geom, Cesium);
            if (bs) {
                window.parselBoundingSphere = bs.sphere;
                window.parselOrbitRange = bs.range * INITIAL_ZOOM_MULT;
                applyCameraPreset('kuzey');
            }
            if (cesium3DViewer.resize) cesium3DViewer.resize();
            cesium3DViewer.scene.requestRender();
        }
        finishInit();
    }

    var videoOlusturAbort = false;
    function runDroneVideoCreateAndUpload() {
        if (!window.currentParselGeojson || !window.currentParselInfo) {
            alert('Video oluşturmak için önce haritada parsel görünen bir sorgu yapın.');
            return;
        }
        var modal = document.getElementById('modalDroneVideo');
        if (modal) { modal.classList.add('hidden'); modal.style.display = 'none'; }
        var overlay = document.getElementById('videoOlusturuluyorOverlay');
        overlay.classList.remove('hidden');
        overlay.style.display = 'flex';
        videoOlusturAbort = false;
        document.getElementById('videoOlusturProgressBar').style.width = '0%';
        document.getElementById('videoOlusturProgressPct').textContent = '0%';
        document.getElementById('videoOlusturDurum').textContent = 'Hazırlanıyor...';
        var btn3D = document.getElementById('btn3DDron');
        if (btn3D) { btn3D.disabled = true; btn3D.innerHTML = '<span class="material-symbols-outlined text-xl animate-spin">progress_activity</span> Yükleniyor...'; }
        loadCesium().then(function() {
            if (btn3D) { btn3D.disabled = false; btn3D.innerHTML = '<span class="material-symbols-outlined text-xl">videocam</span> 3D Dron Görünümü'; }
            initCesium3D('cesiumVideoContainer');
            var container = document.getElementById('cesiumVideoContainer');
            if (container && cesium3DViewer) {
                container.style.width = DRONE_VIDEO_WIDTH + 'px';
                container.style.height = DRONE_VIDEO_HEIGHT + 'px';
                container.style.minWidth = DRONE_VIDEO_WIDTH + 'px';
                container.style.minHeight = DRONE_VIDEO_HEIGHT + 'px';
                if (cesium3DViewer.resize) cesium3DViewer.resize();
                if (cesium3DViewer.scene) cesium3DViewer.scene.requestRender();
            }
            var pctEl = document.getElementById('videoOlusturProgressPct');
            var barEl = document.getElementById('videoOlusturProgressBar');
            var durumEl = document.getElementById('videoOlusturDurum');
            function onProgress(pct, elapsed, total) {
                if (videoOlusturAbort) return;
                pctEl.textContent = pct + '%';
                barEl.style.width = pct + '%';
                var sn = Math.floor(elapsed / 1000);
                durumEl.textContent = sn + ' saniye video kaydediliyor...';
            }
            recordFullDroneSequence(DRONE_VIDEO_TOTAL_MS, onProgress).then(function(blob) {
                if (videoOlusturAbort) return;
                if (!blob || blob.size < 10000) {
                    overlay.classList.add('hidden');
                    overlay.style.display = 'none';
                    if (cesium3DViewer) { cesium3DViewer.destroy(); cesium3DViewer = null; }
                    alert('Video kaydı oluşmadı veya çok kısa. Lütfen tekrar deneyin.');
                    if (modal) { modal.classList.remove('hidden'); modal.style.display = 'flex'; }
                    return;
                }
                durumEl.textContent = 'Sunucuya yükleniyor...';
                barEl.style.width = '95%';
                pctEl.textContent = '95%';
                var fd = new FormData();
                fd.append('video', blob, 'parsel-drone-' + Math.round(DRONE_VIDEO_TOTAL_MS / 1000) + 'sn.webm');
                fd.append('action', 'upload_drone_video');
                var info = window.currentParselInfo || {};
                fd.append('ada', info.ada || '');
                fd.append('parsel_no', info.parsel_no || '');
                fd.append('il_adi', info.il_adi || '');
                fd.append('ilce_adi', info.ilce_adi || '');
                fd.append('mahalle_adi', info.mahalle_adi || '');
                fetch(baseUrl + sep + 'action=upload_drone_video', {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then(function(r) { return r.json(); }).then(function(res) {
                    overlay.classList.add('hidden');
                    overlay.style.display = 'none';
                    if (cesium3DViewer) { cesium3DViewer.destroy(); cesium3DViewer = null; }
                    if (!res.success) {
                        alert(res.message || 'Video kaydedilemedi.');
                        if (modal) { modal.classList.remove('hidden'); modal.style.display = 'flex'; }
                        return;
                    }
                    var fileUrl = res.file_url || '';
                    window.droneVideoPublicUrl = fileUrl;
                    window.droneVideoPreviewUrl = fileUrl;
                    window.droneVideoSelectedMediaId = (res.media_id != null) ? String(res.media_id) : '';
                    var step1Form = document.getElementById('droneVideoStep1Form');
                    var step1Preview = document.getElementById('droneVideoStep1Preview');
                    var previewEl = document.getElementById('droneVideoPreviewEl');
                    if (step1Form) step1Form.classList.add('hidden');
                    if (step1Preview) step1Preview.classList.remove('hidden');
                    if (previewEl && fileUrl) {
                        previewEl.src = fileUrl;
                        previewEl.load();
                    }
                    var overlaySection = document.getElementById('modalOverlaySection');
                    if (overlaySection) overlaySection.classList.remove('hidden');
                    fillOverlayLocationFromParsel();
                    if (modal) {
                        modal.classList.remove('hidden');
                        modal.style.display = 'flex';
                    }
                }).catch(function() {
                    overlay.classList.add('hidden');
                    overlay.style.display = 'none';
                    if (cesium3DViewer) { cesium3DViewer.destroy(); cesium3DViewer = null; }
                    alert('Video yüklenirken hata oluştu.');
                    if (modal) { modal.classList.remove('hidden'); modal.style.display = 'flex'; }
                });
            }).catch(function(err) {
                try {
                    if (!videoOlusturAbort) {
                        var msg = (err && err.message) ? err.message : 'Video oluşturulurken hata oluştu.';
                        if (typeof alert === 'function') alert(msg);
                    }
                    overlay.classList.add('hidden');
                    overlay.style.display = 'none';
                    if (cesium3DViewer) { cesium3DViewer.destroy(); cesium3DViewer = null; }
                    if (modal) { modal.classList.remove('hidden'); modal.style.display = 'flex'; }
                } catch (e2) {}
            });
        }).catch(function() {
            if (btn3D) { btn3D.disabled = false; btn3D.innerHTML = '<span class="material-symbols-outlined text-xl">videocam</span> 3D Dron Görünümü'; }
            overlay.classList.add('hidden');
            overlay.style.display = 'none';
            alert('3D görünüm yüklenemedi.');
        });
    }

    function openVideoModal() {
        var modal = document.getElementById('modalDroneVideo');
        if (!modal) return;
        var step1Form = document.getElementById('droneVideoStep1Form');
        var step1Preview = document.getElementById('droneVideoStep1Preview');
        var previewEl = document.getElementById('droneVideoPreviewEl');
        var cesiumWrap = document.getElementById('droneVideoCesiumWrap');
        var jsonUyari = document.getElementById('droneVideoJsonUyari');
        var hasJson = !!(window.currentParselGeojson && window.currentParselInfo);
        if (cesiumWrap) cesiumWrap.classList.toggle('hidden', !hasJson);
        if (jsonUyari) jsonUyari.classList.toggle('hidden', hasJson);
        if (window.droneVideoPublicUrl) {
            if (step1Form) step1Form.classList.add('hidden');
            if (step1Preview) step1Preview.classList.remove('hidden');
            if (previewEl) {
                previewEl.src = window.droneVideoPublicUrl;
                previewEl.load();
            }
            var overlaySection = document.getElementById('modalOverlaySection');
            if (overlaySection) overlaySection.classList.remove('hidden');
        } else {
            if (step1Form) step1Form.classList.remove('hidden');
            if (step1Preview) step1Preview.classList.add('hidden');
            if (previewEl) previewEl.removeAttribute('src');
            var overlaySection = document.getElementById('modalOverlaySection');
            if (overlaySection) overlaySection.classList.add('hidden');
        }
        var kutuphanePanel = document.getElementById('droneVideoKutuphanePanel');
        if (kutuphanePanel) kutuphanePanel.classList.add('hidden');
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
    }

    document.getElementById('btnVideoOlusturDuzenle').addEventListener('click', openVideoModal);

    document.getElementById('btn3DDron').addEventListener('click', function() {
        openVideoModal();
    });

    document.getElementById('modalDroneClose').addEventListener('click', function() {
        var modal = document.getElementById('modalDroneVideo');
        modal.classList.add('hidden');
        modal.style.display = 'none';
        if (cesium3DViewer) {
            cesium3DViewer.destroy();
            cesium3DViewer = null;
        }
    });

    document.getElementById('btnCesiumVideoOlustur').addEventListener('click', function() {
        runDroneVideoCreateAndUpload();
    });

    (function initKutuphanedenVideoSec() {
        var btnSec = document.getElementById('btnKutuphanedenVideoSec');
        var panel = document.getElementById('droneVideoKutuphanePanel');
        var listEl = document.getElementById('droneVideoKutuphaneListe');
        var yukleniyorEl = document.getElementById('droneVideoKutuphaneYukleniyor');
        var bosEl = document.getElementById('droneVideoKutuphaneBos');
        var btnKapat = document.getElementById('btnKutuphanePanelKapat');
        var sayfaEl = document.getElementById('droneVideoKutuphaneSayfa');
        if (!btnSec || !panel || !listEl) return;

        function kutuphanePanelGoster() {
            panel.classList.remove('hidden');
            yukleniyorEl.classList.remove('hidden');
            bosEl.classList.add('hidden');
            listEl.innerHTML = '';
            listEl.classList.add('hidden');
            var url = mediaListApiUrl + (mediaListApiUrl.indexOf('?') !== -1 ? '&' : '?') + 'type=video&p=1';
            fetch(url, { credentials: 'same-origin' }).then(function(r) { return r.json(); }).then(function(data) {
                yukleniyorEl.classList.add('hidden');
                if (!data.success || !data.media || !data.media.length) {
                    bosEl.classList.remove('hidden');
                    if (sayfaEl) sayfaEl.textContent = '';
                    return;
                }
                listEl.classList.remove('hidden');
                if (sayfaEl) sayfaEl.textContent = (data.pagination && data.pagination.totalItems) ? data.pagination.totalItems + ' video' : '';
                data.media.forEach(function(item) {
                    var path = (item.file_path || '').trim();
                    if (!path) return;
                    var fullUrl = uploadsBaseUrl + path.replace(/^\/+/, '');
                    var name = (item.original_name || 'Video').substring(0, 40);
                    var desc = (item.description || '').trim();
                    var div = document.createElement('button');
                    div.type = 'button';
                    div.className = 'drone-video-kutuphane-item flex flex-col rounded-lg border border-gray-600 hover:border-indigo-500 bg-gray-700/50 hover:bg-gray-700 p-2 text-left transition-colors';
                    div.setAttribute('data-video-url', fullUrl);
                    div.setAttribute('data-video-name', name);
                    div.setAttribute('data-description', desc);
                    div.innerHTML = '<span class="aspect-video bg-gray-800 rounded flex items-center justify-center mb-2"><span class="material-symbols-outlined text-2xl text-gray-500">videocam</span></span><span class="text-xs text-gray-300 truncate" title="' + escapeHtml(name) + '">' + escapeHtml(name) + '</span>';
                    listEl.appendChild(div);
                });
                listEl.querySelectorAll('.drone-video-kutuphane-item').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var url = btn.getAttribute('data-video-url');
                        if (!url) return;
                        window.droneVideoPublicUrl = url;
                        window.droneVideoPreviewUrl = url;
                        window.droneVideoSelectedMediaId = btn.getAttribute('data-media-id') || '';
                        window.droneVideoLocationFromDescription = (btn.getAttribute('data-description') || '').trim();
                        var locEl = document.getElementById('overlayLocation');
                        if (locEl && window.droneVideoLocationFromDescription) locEl.value = window.droneVideoLocationFromDescription;
                        panel.classList.add('hidden');
                        var step1Form = document.getElementById('droneVideoStep1Form');
                        var step1Preview = document.getElementById('droneVideoStep1Preview');
                        var previewEl = document.getElementById('droneVideoPreviewEl');
                        if (step1Form) step1Form.classList.add('hidden');
                        if (step1Preview) step1Preview.classList.remove('hidden');
                        if (previewEl) { previewEl.src = url; previewEl.load(); }
                        var overlaySection = document.getElementById('modalOverlaySection');
                        if (overlaySection) overlaySection.classList.remove('hidden');
                    });
                });
            }).catch(function() {
                yukleniyorEl.classList.add('hidden');
                bosEl.classList.remove('hidden');
                bosEl.textContent = 'Liste yüklenemedi.';
                if (sayfaEl) sayfaEl.textContent = '';
            });
        }

        btnSec.addEventListener('click', function() {
            if (panel.classList.contains('hidden')) kutuphanePanelGoster();
            else panel.classList.add('hidden');
        });
        if (btnKapat) btnKapat.addEventListener('click', function() { panel.classList.add('hidden'); });
    })();

    document.getElementById('videoOlusturIptal').addEventListener('click', function() {
        videoOlusturAbort = true;
        var overlay = document.getElementById('videoOlusturuluyorOverlay');
        overlay.classList.add('hidden');
        overlay.style.display = 'none';
        if (cesium3DViewer) { cesium3DViewer.destroy(); cesium3DViewer = null; }
    });

    (function initOverlayFlow() {
        var btnAiGenerate = document.getElementById('btnOverlayAiGenerate');
        var btnOverlayApplySave = document.getElementById('btnOverlayApplySave');

        if (btnAiGenerate) {
            btnAiGenerate.addEventListener('click', function() {
                var ta = document.getElementById('overlayAiDescription');
                if (!ta) return;
                btnAiGenerate.disabled = true;
                var info = window.currentParselInfo || {};
                var nearby = [
                    (document.getElementById('overlayNearby1') && document.getElementById('overlayNearby1').value) || '',
                    (document.getElementById('overlayNearby2') && document.getElementById('overlayNearby2').value) || '',
                    (document.getElementById('overlayNearby3') && document.getElementById('overlayNearby3').value) || ''
                ].filter(function(v) { return v.trim() !== ''; });
                var fd = new FormData();
                fd.append('action', 'generate_parsel_description');
                fd.append('il_adi', info.il_adi || '');
                fd.append('ilce_adi', info.ilce_adi || '');
                fd.append('mahalle_adi', info.mahalle_adi || '');
                fd.append('ada', info.ada || '');
                fd.append('parsel_no', info.parsel_no || '');
                fd.append('alan_m2', info.alan_m2 != null ? info.alan_m2 : '');
                fd.append('nitelik', info.nitelik || '');
                fd.append('yakın_lokasyonlar', JSON.stringify(nearby));
                fetch(baseUrl + sep + 'action=generate_parsel_description', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success && data.description) ta.value = data.description;
                        else if (data.error) alert(data.error);
                    })
                    .catch(function() { alert('AI açıklama alınamadı.'); })
                    .finally(function() { btnAiGenerate.disabled = false; });
            });
        }

        if (btnOverlayApplySave) {
            btnOverlayApplySave.addEventListener('click', function() {
                var mediaId = (window.droneVideoSelectedMediaId || '').trim();
                if (!mediaId) {
                    alert('Önce kütüphaneden video seçin veya Cesium ile video oluşturup yükleyin.');
                    return;
                }
                var info = window.currentParselInfo || {};
                var fd = new FormData();
                fd.append('action', 'apply_drone_overlay');
                fd.append('media_id', mediaId);
                fd.append('ada', info.ada || '');
                fd.append('parsel_no', info.parsel_no || '');
                fd.append('il_adi', info.il_adi || '');
                fd.append('ilce_adi', info.ilce_adi || '');
                fd.append('mahalle_adi', info.mahalle_adi || '');
                var locEl = document.getElementById('overlayLocation');
                var descEl = document.getElementById('overlayAiDescription');
                fd.append('description', (descEl && descEl.value) ? descEl.value.trim() : '');
                fd.append('title_text', (locEl && locEl.value) ? locEl.value.trim() : '');
                btnOverlayApplySave.disabled = true;
                btnOverlayApplySave.innerHTML = '<span class="material-symbols-outlined text-lg animate-spin">progress_activity</span> İşleniyor...';
                fetch(baseUrl + sep + 'action=apply_drone_overlay', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        btnOverlayApplySave.disabled = false;
                        btnOverlayApplySave.innerHTML = '<span class="material-symbols-outlined text-base">smart_display</span> Overlay ekle ve kaydet';
                        if (res && res.success) {
                            alert(res.message || 'Video overlay ile kaydedildi.');
                            if (typeof window.location !== 'undefined' && window.location.reload) window.location.reload();
                        } else {
                            alert(res && res.message ? res.message : 'Overlay uygulanamadı.');
                        }
                    })
                    .catch(function(err) {
                        btnOverlayApplySave.disabled = false;
                        btnOverlayApplySave.innerHTML = '<span class="material-symbols-outlined text-base">smart_display</span> Overlay ekle ve kaydet';
                        alert(err && err.message ? err.message : 'Hata oluştu.');
                    });
            });
        }
    })();

    window.addEventListener('beforeunload', function() {
        if (cesium3DViewer) {
            cesium3DViewer.destroy();
            cesium3DViewer = null;
        }
    });
})();
</script>
