<?php
$settings = $settings ?? [];
$sonDroneVideolari = $sonDroneVideolari ?? [];
$baseUrl = admin_url('module/tkgm-parsel/sorgu');
$ffmpegProxyUrl = (function_exists('site_url') ? site_url('/public/ffmpeg-wasm/proxy.php') : '/public/ffmpeg-wasm/proxy.php');
?>

<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div class="flex flex-col gap-2">
        <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Parsel Sorgu</h1>
        <p class="text-gray-500 dark:text-gray-400 text-base">İl, ilçe ve mahalle seçin; ada ve parsel numaralarını girin. Sistem eşleşen parsel bilgisini getirir.</p>
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

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <form id="parselSorguForm" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label for="il" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">İl</label>
                <select id="il" name="il_kodu" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">Seçiniz</option>
                </select>
            </div>
            <div>
                <label for="ilce" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">İlçe</label>
                <select id="ilce" name="ilce_kodu" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" disabled>
                    <option value="">Önce il seçin</option>
                </select>
            </div>
            <div>
                <label for="mahalle" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mahalle / Köy</label>
                <select id="mahalle" name="mahalle_kodu" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" disabled>
                    <option value="">Önce ilçe seçin</option>
                </select>
            </div>
            <div>
                <label for="ada" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ada</label>
                <input type="text" id="ada" name="ada" placeholder="Örn: 114" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" maxlength="50">
            </div>
            <div>
                <label for="parsel" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Parsel</label>
                <input type="text" id="parsel" name="parsel" placeholder="Örn: 50" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" maxlength="50">
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="submit" id="btnSorgula" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <span class="material-symbols-outlined text-xl">search</span>
                <span>Sorgula</span>
            </button>
            <button type="button" id="btnOrnek" class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                <span class="material-symbols-outlined text-xl">map</span>
                <span>Örnek veri ile dene</span>
            </button>
            <button type="button" id="btnTemizle" class="flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                <span class="material-symbols-outlined text-xl">clear</span>
                <span>Temizle</span>
            </button>
        </div>
    </form>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<div id="sonucAlani" class="hidden bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Parsel Bilgisi</h2>
    <div id="parselHaritaWrap" class="hidden mb-4 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600" style="height: 400px;">
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

<!-- 3D Dron Modal -->
<div id="modal3DDron" class="hidden fixed inset-0 z-[9999] flex flex-col bg-black/95" style="display: none;">
    <div class="flex items-center justify-between px-4 py-2 bg-gray-900/80 border-b border-gray-700">
        <h3 class="text-white font-semibold">3D Dron Görünümü – 360° Tur</h3>
        <button type="button" id="modal3DClose" class="p-2 text-gray-400 hover:text-white rounded-lg transition-colors">
            <span class="material-symbols-outlined text-2xl">close</span>
        </button>
    </div>
    <!-- Adımlar (modal ilk açıldığında) -->
    <div id="modal3DAdimlar" class="flex-1 flex flex-col overflow-auto">
        <div class="flex border-b border-gray-700 flex-wrap">
            <button type="button" id="tabModalAdim1" class="px-4 py-3 text-sm font-medium border-b-2 border-indigo-500 text-indigo-400">1. Emlakçı</button>
            <button type="button" id="tabModalAdim2" class="px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-400 hover:text-gray-300">2. Yakın Lokasyonlar</button>
            <button type="button" id="tabModalAdim3" class="px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-400 hover:text-gray-300">3. Seslendirme Metni</button>
            <button type="button" id="tabModalAdim4" class="px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-400 hover:text-gray-300">4. Seslendirmen</button>
        </div>
        <div id="panelModalAdim1" class="p-6 flex-1 overflow-auto">
            <h4 class="text-white font-medium mb-2">Emlakçı Seçin</h4>
            <p class="text-gray-400 text-sm mb-4">Videoda görünecek emlak danışmanını seçin.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                <div class="relative">
                    <div id="emlakciDropdownWrap" class="relative">
                        <button type="button" id="btnEmlakciDropdown" class="w-full px-4 py-2 rounded-lg bg-gray-800 border border-gray-600 text-gray-100 text-sm text-left flex items-center justify-between hover:border-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <span id="emlakciDropdownLabel">— Emlakçı seçin —</span>
                            <span class="material-symbols-outlined text-lg text-gray-400">expand_more</span>
                        </button>
                        <div id="emlakciDropdownListe" class="hidden absolute top-full left-0 right-0 mt-1 rounded-lg bg-gray-800 border border-gray-600 shadow-xl z-[10001] max-h-48 overflow-y-auto">
                            <button type="button" class="emlakci-opt w-full px-4 py-2 text-left text-gray-100 hover:bg-gray-700 text-sm" data-id="">— Emlakçı seçin —</button>
                        </div>
                    </div>
                    <p id="modalEmlakciYokUyari" class="mt-2 text-sm text-amber-400 hidden">Emlak danışmanı bulunamadı. Önce Emlak Danışmanları modülünden danışman ekleyin.</p>
                    <button type="button" id="btnModalAdim1Devam" class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">Devam et</button>
                </div>
                <div>
                    <label class="block text-gray-400 text-sm mb-2">Önizleme (9:16)</label>
                    <div class="relative rounded-lg overflow-hidden bg-gray-900" style="width:180px; height:320px;">
                        <div id="modalVideoOnizlemeArkaplan" class="absolute inset-0 w-full h-full">
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1080 1920'%3E%3Cdefs%3E%3ClinearGradient id='g' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%23374151'/%3E%3Cstop offset='100%25' style='stop-color:%231f2937'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='100%25' height='100%25' fill='url(%23g)'/%3E%3Cpath d='M200,400 L400,350 L600,380 L550,600 L350,650 L180,550 Z' fill='%2322511b' opacity='0.9'/%3E%3Cpath d='M450,200 L800,180 L850,500 L600,550 L400,450 Z' fill='%234a7c23' opacity='0.85'/%3E%3Cpath d='M100,800 L500,750 L600,1000 L300,1100 L50,950 Z' fill='%236b4423' opacity='0.8'/%3E%3Cpath d='M600,700 L950,650 L1000,1200 L700,1300 L550,1000 Z' fill='%23555' opacity='0.7'/%3E%3Cpath d='M350,850 L550,820 L600,950 L450,1000 L320,920 Z' fill='%23E040FB' stroke='%23E040FB' stroke-width='8' fill-opacity='0.2' stroke-opacity='0.9'/%3E%3Ctext x='540' y='960' text-anchor='middle' fill='%236b7280' font-family='sans-serif' font-size='48'%3EDron Görünümü%3C/text%3E%3C/svg%3E" alt="Drone kapak" class="w-full h-full object-cover" />
                        </div>
                        <div id="modalEmlakciOverlayOnizleme" class="absolute inset-0 flex items-end justify-start pointer-events-none p-2.5">
                            <div id="modalEmlakciKartOnizleme" class="hidden flex items-center gap-3 w-full max-w-full">
                                <img id="modalEmlakciFoto" src="" alt="" class="w-14 h-14 rounded-full object-cover flex-shrink-0 ring-2 ring-white/50 object-center">
                                <div class="flex flex-col gap-2 min-w-0 flex-1">
                                    <span id="modalEmlakciTelPill" class="inline-block px-3 py-1.5 rounded-xl text-blue-900 font-semibold text-xs truncate max-w-full bg-white/95">—</span>
                                    <span id="modalEmlakciIsimPill" class="inline-block px-3 py-1.5 rounded-xl text-white font-semibold text-xs truncate max-w-full" style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.96) 0%, rgba(88, 28, 135, 0.96) 100%);">—</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="panelModalAdim2" class="hidden p-6 flex-1">
            <h4 class="text-white font-medium mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-xl">location_on</span>
                Yakın Lokasyonlar
            </h4>
            <p class="text-gray-400 text-sm mb-4">Parsel etrafındaki önemli noktaları ekleyin. Seslendirme metninde kullanılacaktır. En fazla 3 lokasyon ekleyebilirsiniz.</p>
            <div id="yakınLokasyonListe" class="space-y-3 mb-4">
                <div class="flex gap-2">
                    <input type="text" class="yakın-lokasyon-input flex-1 px-4 py-2 rounded-lg bg-gray-800 border border-gray-600 text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Örn: imara 1 km uzakta">
                    <button type="button" class="btnYakınLokasyonSil px-3 py-2 rounded-lg bg-red-600/80 hover:bg-red-600 text-white transition-colors" title="Sil">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>
            </div>
            <button type="button" id="btnYakınLokasyonEkle" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm mb-4">
                <span class="material-symbols-outlined text-lg">add</span>
                Ekstra Lokasyon Ekle
            </button>
            <button type="button" id="btnModalAdim2Devam" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm flex items-center gap-2">
                Devam Et <span class="material-symbols-outlined text-lg">arrow_forward</span>
            </button>
        </div>
        <div id="panelModalAdim3" class="hidden p-6 flex-1">
            <h4 class="text-white font-medium mb-2">Seslendirme Metni</h4>
            <p class="text-gray-400 text-sm mb-3">Parsel sorgusundan gelen bilgilerle video seslendirmesi için metin oluşturun. Maksimum 800 karakter.</p>
            <textarea id="modalSeslendirmeMetni" rows="6" maxlength="800" class="w-full px-4 py-2 border border-gray-600 rounded-lg bg-gray-800 text-gray-100 text-sm mb-1 focus:ring-2 focus:ring-indigo-500" placeholder="Seslendirme metni burada görünecek..."></textarea>
            <p class="text-gray-500 text-xs mb-3"><span id="seslendirmeMetniKarakter">0</span> / 800 karakter</p>
            <div class="flex gap-2">
                <button type="button" id="btnModalAISeslendirmeOlustur" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                    <span class="material-symbols-outlined text-lg">auto_awesome</span>
                    <span>Yapay Zeka ile Oluştur</span>
                </button>
                <button type="button" id="btnModalAdim3Devam" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">Devam et</button>
            </div>
        </div>
        <div id="panelModalAdim4" class="hidden p-6 flex-1">
            <h4 class="text-white font-medium mb-2">Seslendirmen</h4>
            <p class="text-gray-400 text-sm mb-4">Videonun seslendirmesini yapacak kişiyi seçin. Bu özellik yakında eklenecektir.</p>
            <div class="rounded-lg border border-dashed border-gray-600 p-6 text-center text-gray-500 text-sm mb-6">
                <span class="material-symbols-outlined text-3xl mb-2 block">record_voice_over</span>
                Seslendirmen seçimi yakında aktif olacak.
            </div>
            <button type="button" id="btnModalVideoOlustur" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">videocam</span>
                <span>Video Oluştur</span>
            </button>
        </div>
    </div>
</div>

<!-- Video oluşturuluyor overlay (progress bar) -->
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
        Oluşturulan videolar MP4 formatında içerik kütüphanesine kaydedilir ve aşağıda listelenir. İzleme ve indirme bu kayıtlar üzerinden yapılır.
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
                    <?php if ($vidUrl !== ''): ?>
                    <button type="button" class="drone-overlay-trigger w-full inline-flex items-center justify-center gap-1 px-2 py-1.5 text-xs font-medium rounded-md bg-emerald-600 text-white hover:bg-emerald-700 transition-colors border-0 cursor-pointer" data-media-id="<?php echo (int)($v['id'] ?? 0); ?>" data-video-url="<?php echo esc_attr($vidUrl); ?>">
                        <span class="material-symbols-outlined text-sm">add_photo_alternate</span>
                        Overlay ekle
                    </button>
                    <?php endif; ?>
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

<!-- Overlay ekle modal (Final video: seslendirme, altyazı, emlakçı bilgisi) -->
<div id="overlayModal" class="hidden fixed inset-0 z-[9999] bg-black/80 flex items-center justify-center p-4" aria-hidden="true">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Final video oluştur (Overlay)</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">İşlem tarayıcıda yapılır; sunucuda FFmpeg gerekmez.</p>
            </div>
            <button type="button" id="overlayModalClose" class="p-1.5 rounded-lg text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Kapat">
                <span class="material-symbols-outlined text-2xl">close</span>
            </button>
        </div>
        <form id="overlayForm" class="p-6 space-y-4">
            <input type="hidden" name="media_id" id="overlayMediaId" value="">
            <input type="hidden" name="action" value="apply_drone_overlay">
            <div>
                <label for="overlayAgent" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Emlakçı (opsiyonel)</label>
                <select id="overlayAgent" name="agent_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <option value="">Seçiniz</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="overlayAda" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ada</label>
                    <input type="text" id="overlayAda" name="ada" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500" placeholder="Örn: 164">
                </div>
                <div>
                    <label for="overlayParsel" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Parsel</label>
                    <input type="text" id="overlayParsel" name="parsel_no" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500" placeholder="Örn: 56">
                </div>
            </div>
            <div>
                <label for="overlayMahalle" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mahalle / Köy (opsiyonel)</label>
                <input type="text" id="overlayMahalle" name="mahalle_adi" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500" placeholder="Mahalle adı">
            </div>
            <div>
                <label for="overlayTitle" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Başlık metni (opsiyonel)</label>
                <input type="text" id="overlayTitle" name="title_text" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500" placeholder="Videoda görünecek başlık">
            </div>
            <div>
                <label for="overlayVoiceover" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Seslendirme (opsiyonel)</label>
                <input type="file" id="overlayVoiceover" name="voiceover" accept="audio/mpeg,audio/mp3,audio/wav,audio/x-wav" class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 dark:file:bg-gray-700 dark:file:text-gray-200 hover:file:bg-indigo-100 dark:hover:file:bg-gray-600">
            </div>
            <div>
                <label for="overlaySubtitle" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Altyazı SRT (opsiyonel)</label>
                <input type="file" id="overlaySubtitle" name="subtitle" accept=".srt,text/plain" class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 dark:file:bg-gray-700 dark:file:text-gray-200 hover:file:bg-indigo-100 dark:hover:file:bg-gray-600">
            </div>
            <div id="overlayProgressText" class="hidden p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-sm text-blue-700 dark:text-blue-300"></div>
            <div id="overlayFormError" class="hidden p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-sm text-red-700 dark:text-red-300"></div>
            <div id="overlayFormSuccess" class="hidden p-3 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-sm text-green-700 dark:text-green-300"></div>
            <div id="overlayResultActions" class="hidden flex gap-2 pt-2">
                <a id="overlayDownloadLink" href="#" download="parsel-drone-overlay.mp4" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium transition-colors">
                    <span class="material-symbols-outlined text-lg">download</span> İndir
                </a>
                <button type="button" id="overlayUploadBtn" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium transition-colors">
                    <span class="material-symbols-outlined text-lg">cloud_upload</span> Sunucuya yükle
                </button>
            </div>
            <div class="flex gap-2 pt-2" id="overlaySubmitRow">
                <button type="submit" id="overlayFormSubmit" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="material-symbols-outlined text-lg">movie_edit</span>
                    Final video oluştur
                </button>
                <button type="button" id="overlayModalCancel" class="px-4 py-2.5 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">İptal</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@ffmpeg/ffmpeg@0.12.10/dist/umd/ffmpeg.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@ffmpeg/util@0.12.1/dist/umd/index.js"></script>
<script>
(function() {
    var baseUrl = <?php echo json_encode($baseUrl); ?>;
    var sep = baseUrl.indexOf('?') !== -1 ? '&' : '?';
    var ffmpegProxyUrl = <?php echo json_encode($ffmpegProxyUrl); ?>;
    var mapboxToken = <?php echo json_encode(trim($settings['mapbox_access_token'] ?? '')); ?>;
    var googleMapsApiKey = <?php echo json_encode(trim($settings['google_maps_api_key'] ?? '')); ?>;
    var cesiumIonToken = <?php echo json_encode(trim($settings['cesium_ion_access_token'] ?? '')); ?>;

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

    (function initOverlayModal() {
        var modal = document.getElementById('overlayModal');
        var form = document.getElementById('overlayForm');
        var overlayMediaId = document.getElementById('overlayMediaId');
        var overlayAgent = document.getElementById('overlayAgent');
        var overlayAda = document.getElementById('overlayAda');
        var overlayParsel = document.getElementById('overlayParsel');
        var overlayMahalle = document.getElementById('overlayMahalle');
        var overlayTitle = document.getElementById('overlayTitle');
        var overlayFormError = document.getElementById('overlayFormError');
        var overlayFormSuccess = document.getElementById('overlayFormSuccess');
        var overlayFormSubmit = document.getElementById('overlayFormSubmit');
        var overlayProgressText = document.getElementById('overlayProgressText');
        var overlayResultActions = document.getElementById('overlayResultActions');
        var agentsLoaded = false;
        var overlayVideoUrl = '';
        function openOverlayModal(mediaId, videoUrl) {
            if (!mediaId) return;
            overlayVideoUrl = videoUrl || '';
            overlayFormError.classList.add('hidden');
            overlayFormSuccess.classList.add('hidden');
            if (overlayResultActions) overlayResultActions.classList.add('hidden');
            if (overlayProgressText) overlayProgressText.classList.add('hidden');
            if (form) form.reset();
            overlayMediaId.value = mediaId;
            if (window.currentParselInfo) {
                overlayAda.value = window.currentParselInfo.ada || '';
                overlayParsel.value = window.currentParselInfo.parsel_no || '';
                overlayMahalle.value = window.currentParselInfo.mahalle_adi || '';
            }
            overlayTitle.value = '';
            if (!agentsLoaded && overlayAgent) {
                agentsLoaded = true;
                fetch(baseUrl + sep + 'action=get_agents', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        if (res.success && res.data && res.data.length) {
                            overlayAgent.innerHTML = '<option value="">Seçiniz</option>';
                            res.data.forEach(function(a) {
                                var opt = document.createElement('option');
                                opt.value = a.id;
                                opt.textContent = (a.first_name || '') + ' ' + (a.last_name || '') + (a.phone ? ' • ' + a.phone : '');
                                overlayAgent.appendChild(opt);
                            });
                        }
                    });
            }
            if (modal) {
                modal.classList.remove('hidden');
                modal.setAttribute('aria-hidden', 'false');
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }
        function closeOverlayModal() {
            if (modal) {
                modal.classList.add('hidden');
                modal.setAttribute('aria-hidden', 'true');
                modal.style.display = 'none';
            }
            document.body.style.overflow = '';
        }
        document.addEventListener('click', function(e) {
            var t = e.target.closest('.drone-overlay-trigger');
            if (t) {
                e.preventDefault();
                var mid = t.getAttribute('data-media-id');
                var url = t.getAttribute('data-video-url') || '';
                if (mid) openOverlayModal(mid, url);
            }
        });
        if (document.getElementById('overlayModalClose')) document.getElementById('overlayModalClose').addEventListener('click', closeOverlayModal);
        if (document.getElementById('overlayModalCancel')) document.getElementById('overlayModalCancel').addEventListener('click', closeOverlayModal);
        if (modal) modal.addEventListener('click', function(e) { if (e.target === modal) closeOverlayModal(); });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) closeOverlayModal();
        });
        var ffmpegInstance = null;
        var ffmpegLoaded = false;
        function setProgress(txt) {
            if (overlayProgressText) {
                overlayProgressText.textContent = txt;
                overlayProgressText.classList.remove('hidden');
            }
        }
        function loadFFmpeg() {
            if (ffmpegLoaded && ffmpegInstance) return Promise.resolve(ffmpegInstance);
            var G = typeof window !== 'undefined' ? window : typeof global !== 'undefined' ? global : this;
            function getFFmpegCtor() {
                var w = G.FFmpegWASM;
                if (w) {
                    if (typeof w === 'function') return w;
                    if (w.FFmpeg && typeof w.FFmpeg === 'function') return w.FFmpeg;
                    if (w.default && typeof w.default === 'function') return w.default;
                    if (w.default && w.default.FFmpeg && typeof w.default.FFmpeg === 'function') return w.default.FFmpeg;
                }
                if (G.FFmpeg && typeof G.FFmpeg === 'function') return G.FFmpeg;
                if (G.FFmpeg && G.FFmpeg.FFmpeg && typeof G.FFmpeg.FFmpeg === 'function') return G.FFmpeg.FFmpeg;
                if (G.ffmpeg && typeof G.ffmpeg === 'function') return G.ffmpeg;
                var pkg = G['@ffmpeg/ffmpeg'];
                if (pkg && typeof pkg === 'function') return pkg;
                if (pkg && pkg.default && typeof pkg.default === 'function') return pkg.default;
                if (pkg && pkg.FFmpeg && typeof pkg.FFmpeg === 'function') return pkg.FFmpeg;
                return null;
            }
            function getToBlobURL() {
                var u = G.FFmpegUtil || G['@ffmpeg/util'];
                if (!u) return null;
                if (typeof u.toBlobURL === 'function') return u.toBlobURL;
                if (u.default && typeof u.default.toBlobURL === 'function') return u.default.toBlobURL;
                return null;
            }
            var FFmpegCtor = getFFmpegCtor();
            var toBlobURL = getToBlobURL();
            if (!FFmpegCtor) {
                return loadFFmpegScripts().then(function() {
                    FFmpegCtor = getFFmpegCtor();
                    toBlobURL = getToBlobURL();
                    if (!FFmpegCtor) {
                        if (typeof console !== 'undefined' && console.warn) {
                            var g = typeof window !== 'undefined' ? window : {};
                            var keys = [];
                            try { keys = Object.keys(g).filter(function(k) { return /ffmpeg|FFmpeg/i.test(k); }); } catch (e) {}
                            console.warn('FFmpeg global bulunamadı. window üzerinde ilgili anahtarlar:', keys.length ? keys : '(yok)');
                            var wasm = g.FFmpegWASM;
                            if (wasm && typeof wasm === 'object') {
                                try {
                                    console.warn('FFmpegWASM alt anahtarları:', Object.keys(wasm));
                                    console.warn('FFmpegWASM.FFmpeg tipi:', typeof wasm.FFmpeg);
                                } catch (e2) {}
                            }
                        }
                        return Promise.reject(new Error('FFmpeg kütüphanesi yüklenemedi. Sayfayı yenileyip tekrar deneyin. F12 ile Konsolu açıp script veya CORS hatası olup olmadığına bakın.'));
                    }
                    return doLoadFFmpeg(FFmpegCtor, toBlobURL);
                });
            }
            return doLoadFFmpeg(FFmpegCtor, toBlobURL);
        }
        function loadFFmpegScripts() {
            if (getFFmpegCtor()) return Promise.resolve();
            var base = 'https://cdn.jsdelivr.net/npm/';
            function loadOne(path) {
                return new Promise(function(resolve) {
                    var s = document.createElement('script');
                    s.src = base + path;
                    s.async = false;
                    s.onload = function() { resolve(); };
                    s.onerror = function() { resolve(); };
                    (document.head || document.documentElement).appendChild(s);
                });
            }
            return loadOne('@ffmpeg/ffmpeg@0.12.10/dist/umd/ffmpeg.js').then(function() {
                return loadOne('@ffmpeg/util@0.12.1/dist/umd/index.js');
            });
        }
        function fetchAsBlobURL(url, mime) {
            return fetch(url, { mode: 'cors', credentials: 'omit' }).then(function(r) {
                if (!r.ok) throw new Error('fetch ' + url + ' failed: ' + r.status);
                return r.blob();
            }).then(function(blob) {
                return URL.createObjectURL(new Blob([blob], { type: mime || 'application/javascript' }));
            });
        }
        function doLoadFFmpeg(FFmpegCtor, toBlobURL) {
            var coreBaseUMD = 'https://cdn.jsdelivr.net/npm/@ffmpeg/core@0.12.10/dist/umd';
            var coreBaseESM = 'https://cdn.jsdelivr.net/npm/@ffmpeg/core@0.12.10/dist/esm';
            var ffmpegPkgURL = 'https://cdn.jsdelivr.net/npm/@ffmpeg/ffmpeg@0.12.10/dist/umd';
            var coreJS = coreBaseUMD + '/ffmpeg-core.js';
            var coreWasm = coreBaseUMD + '/ffmpeg-core.wasm';
            var workerURL = ffmpegPkgURL + '/814.ffmpeg.js';
            var t = Date.now();
            var proxyCore = ffmpegProxyUrl + '?file=ffmpeg-core.js&t=' + t;
            var proxyWasm = ffmpegProxyUrl + '?file=ffmpeg-core.wasm&t=' + t;
            var proxyWorker = ffmpegProxyUrl + '?file=814.ffmpeg.js&t=' + t;
            ffmpegInstance = new FFmpegCtor();
            var loadOpts = {};
            function loadWithUrls(coreURL, wasmURL, classWorkerURL) {
                loadOpts.coreURL = coreURL;
                loadOpts.wasmURL = wasmURL;
                loadOpts.classWorkerURL = classWorkerURL;
                return ffmpegInstance.load(loadOpts).then(function() { ffmpegLoaded = true; return ffmpegInstance; });
            }
            function loadAllAsBlob(coreBase) {
                var base = coreBase || coreBaseUMD;
                return Promise.all([
                    fetchAsBlobURL(base + '/ffmpeg-core.js', 'text/javascript'),
                    fetchAsBlobURL(base + '/ffmpeg-core.wasm', 'application/wasm'),
                    fetchAsBlobURL(workerURL, 'text/javascript')
                ]).then(function(urls) { return loadWithUrls(urls[0], urls[1], urls[2]); });
            }
            function trySameOriginProxy() {
                return loadWithUrls(proxyCore, proxyWasm, proxyWorker);
            }
            function tryLoad() {
                if (toBlobURL && typeof toBlobURL === 'function') {
                    return Promise.all([
                        toBlobURL(coreJS, 'text/javascript'),
                        toBlobURL(coreWasm, 'application/wasm'),
                        toBlobURL(workerURL, 'text/javascript')
                    ]).then(function(urls) { return loadWithUrls(urls[0], urls[1], urls[2]); });
                }
                return loadAllAsBlob(coreBaseUMD);
            }
            return trySameOriginProxy().catch(function(e) {
                var msg = (e && e.message) ? e.message : '';
                if (typeof console !== 'undefined' && console.warn) console.warn('[TKGM Overlay] Same-origin proxy failed:', msg, e);
                ffmpegInstance = new FFmpegCtor();
                return tryLoad();
            }).catch(function(e) {
                var msg = (e && e.message) ? e.message : '';
                if (msg.indexOf('failed to import ffmpeg-core') !== -1 || msg.indexOf('fetch') !== -1) {
                    if (typeof console !== 'undefined' && console.warn) console.warn('[TKGM Overlay] Blob URLs failed, trying ESM core:', msg, e);
                    ffmpegInstance = new FFmpegCtor();
                    return loadAllAsBlob(coreBaseESM);
                }
                throw e;
            }).catch(function(e) {
                var msg = (e && e.message) ? e.message : '';
                if (typeof console !== 'undefined' && console.warn) console.warn('[TKGM Overlay] ESM core also failed, trying direct CDN URLs:', msg, e);
                ffmpegInstance = new FFmpegCtor();
                return loadWithUrls(coreJS, coreWasm, workerURL);
            }).catch(function(e) {
                var msg = (e && e.message) ? e.message : '';
                if (msg.indexOf('failed to import ffmpeg-core') !== -1 && typeof console !== 'undefined' && console.error) {
                    console.error('[TKGM Overlay] Tüm yükleme yöntemleri başarısız. CSP (Content-Security-Policy) veya CORS sorunu olabilir.');
                    console.error('[TKGM Overlay] Sunucu ayarlarında Content-Security-Policy header\'ını kontrol edin: script-src ve worker-src direktiflerinde blob: ve https://cdn.jsdelivr.net izni gerekiyor.');
                }
                throw e;
            });
        }
        function getFFmpegCtor() {
            var G = typeof window !== 'undefined' ? window : typeof global !== 'undefined' ? global : this;
            var w = G.FFmpegWASM;
            if (w) {
                if (typeof w === 'function') return w;
                if (w.FFmpeg && typeof w.FFmpeg === 'function') return w.FFmpeg;
                if (w.default && typeof w.default === 'function') return w.default;
                if (w.default && w.default.FFmpeg && typeof w.default.FFmpeg === 'function') return w.default.FFmpeg;
            }
            if (G.FFmpeg && typeof G.FFmpeg === 'function') return G.FFmpeg;
            if (G.FFmpeg && G.FFmpeg.FFmpeg && typeof G.FFmpeg.FFmpeg === 'function') return G.FFmpeg.FFmpeg;
            if (G.ffmpeg && typeof G.ffmpeg === 'function') return G.ffmpeg;
            var pkg = G['@ffmpeg/ffmpeg'];
            if (pkg && typeof pkg === 'function') return pkg;
            if (pkg && pkg.default && typeof pkg.default === 'function') return pkg.default;
            if (pkg && pkg.FFmpeg && typeof pkg.FFmpeg === 'function') return pkg.FFmpeg;
            return null;
        }
        function escapeDrawtext(t) {
            if (!t) return '';
            return String(t).replace(/\\/g, '\\\\').replace(/'/g, "\\'");
        }
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (!overlayMediaId.value) return;
                if (!overlayVideoUrl) {
                    overlayFormError.textContent = 'Video adresi alınamadı. Lütfen listeden Overlay ekle ile tekrar açın.';
                    overlayFormError.classList.remove('hidden');
                    return;
                }
                if (overlayVideoUrl.indexOf('admin.php') !== -1 || (overlayVideoUrl.indexOf('.mp4') === -1 && overlayVideoUrl.indexOf('.webm') === -1 && overlayVideoUrl.indexOf('/uploads/') === -1)) {
                    overlayFormError.textContent = 'Video adresi hatalı görünüyor (sayfa adresi değil, doğrudan video dosya linki olmalı: .../uploads/.../video.mp4). Listeden "Overlay ekle" ile tekrar açın veya videonun "İndir" linkini kontrol edin.';
                    overlayFormError.classList.remove('hidden');
                    return;
                }
                overlayFormError.classList.add('hidden');
                overlayFormSuccess.classList.add('hidden');
                if (overlayResultActions) overlayResultActions.classList.add('hidden');
                var submitRow = document.getElementById('overlaySubmitRow');
                if (submitRow) submitRow.classList.add('hidden');
                overlayFormSubmit.disabled = true;
                overlayFormSubmit.innerHTML = '<span class="material-symbols-outlined text-lg animate-spin">progress_activity</span> İşleniyor...';
                setProgress('Video indiriliyor...');
                if (typeof console !== 'undefined' && console.log) console.log('[TKGM Overlay] Video adresi:', overlayVideoUrl);
                var ada = (overlayAda && overlayAda.value) ? overlayAda.value.trim() : '';
                var parsel = (overlayParsel && overlayParsel.value) ? overlayParsel.value.trim() : '';
                var mahalle = (overlayMahalle && overlayMahalle.value) ? overlayMahalle.value.trim() : '';
                var titleText = (overlayTitle && overlayTitle.value) ? overlayTitle.value.trim() : '';
                var agentOpt = overlayAgent && overlayAgent.selectedIndex > 0 ? overlayAgent.options[overlayAgent.selectedIndex] : null;
                var agentLine = agentOpt ? agentOpt.textContent.trim() : '';
                var voiceoverFile = document.getElementById('overlayVoiceover') && document.getElementById('overlayVoiceover').files[0];
                var subtitleFile = document.getElementById('overlaySubtitle') && document.getElementById('overlaySubtitle').files[0];
                var hasAudio = !!voiceoverFile;
                var hasSub = !!subtitleFile;
                fetch(overlayVideoUrl, { mode: 'cors', credentials: 'same-origin' }).then(function(r) {
                    if (!r.ok) throw new Error('Video indirilemedi: HTTP ' + r.status + '. Video linki erişilebilir mi kontrol edin.');
                    return r.arrayBuffer();
                }).then(function(videoAb) {
                    setProgress('FFmpeg yükleniyor (~30 MB, ilk seferde birkaç saniye sürebilir)...');
                    return loadFFmpeg().then(function(ffmpeg) {
                        setProgress('Video işleniyor...');
                        var inputName = 'input.mp4';
                        var ext = (overlayVideoUrl.indexOf('.webm') !== -1) ? 'webm' : 'mp4';
                        if (ext === 'webm') inputName = 'input.webm';
                        return ffmpeg.writeFile(inputName, new Uint8Array(videoAb)).then(function() {
                            if (hasAudio) {
                                return new Promise(function(resolve, reject) {
                                    var fr = new FileReader();
                                    fr.onload = function() { ffmpeg.writeFile('audio.mp3', new Uint8Array(fr.result)).then(resolve).catch(reject); };
                                    fr.onerror = reject;
                                    fr.readAsArrayBuffer(voiceoverFile);
                                });
                            }
                        }).then(function() {
                            if (subtitleFile) {
                                return new Promise(function(resolve, reject) {
                                    var fr = new FileReader();
                                    fr.onload = function() { ffmpeg.writeFile('sub.srt', fr.result).then(resolve).catch(reject); };
                                    fr.onerror = reject;
                                    fr.readAsText(subtitleFile);
                                });
                            }
                        }).then(function() {
                            if (console && console.log) console.log('[TKGM Overlay] Form verileri:', { ada: ada, parsel: parsel, mahalle: mahalle, titleText: titleText, agentLine: agentLine, hasAudio: hasAudio, hasSub: hasSub });
                            var drawtexts = [];
                            var y = 80;
                            var fs = 28;
                            var lineH = 42;
                            if (titleText) { drawtexts.push({ t: titleText, x: 50, y: y, fs: Math.round(fs * 1.2) }); y += lineH; }
                            if (ada) { drawtexts.push({ t: 'Ada ' + ada, x: 50, y: y, fs: fs }); y += lineH; }
                            if (parsel) { drawtexts.push({ t: 'Parsel ' + parsel, x: 50, y: y, fs: fs }); y += lineH; }
                            if (mahalle) { drawtexts.push({ t: mahalle, x: 50, y: y, fs: fs }); y += lineH; }
                            if (agentLine) { drawtexts.push({ t: agentLine, x: 50, y: y, fs: Math.round(fs * 0.9) }); }
                            if (console && console.log) console.log('[TKGM Overlay] Drawtext sayısı:', drawtexts.length, drawtexts);
                            var vfParts = [];
                            if (hasSub) vfParts.push("subtitles=sub.srt:force_style='FontSize=24,PrimaryColour=&HFFFFFF&'");
                            drawtexts.forEach(function(d) {
                                vfParts.push("drawtext=text='" + escapeDrawtext(d.t) + "':x=" + d.x + ":y=" + d.y + ":fontsize=" + d.fs + ":fontcolor=white:borderw=2:border_color=black");
                            });
                            var vfStr = vfParts.length ? vfParts.join(',') : null;
                            var args = ['-y', '-i', inputName];
                            if (hasAudio) args.push('-i', 'audio.mp3');
                            if (vfStr) args.push('-vf', vfStr);
                            args.push('-c:v', 'libx264', '-preset', 'ultrafast', '-crf', '23', '-pix_fmt', 'yuv420p');
                            if (hasAudio) args.push('-map', '0:v', '-map', '1:a', '-c:a', 'aac', '-b:a', '128k', '-shortest'); else args.push('-an');
                            args.push('output.mp4');
                            if (console && console.log) console.log('[TKGM Overlay] FFmpeg komutu:', args.join(' '));
                            if (console && console.log && vfStr) console.log('[TKGM Overlay] Video filtresi:', vfStr);
                            return ffmpeg.exec(args).then(function() { return ffmpeg.readFile('output.mp4'); });
                        });
                    });
                }).then(function(outputData) {
                    if (!outputData || !outputData.buffer) {
                        throw new Error('FFmpeg çıktısı alınamadı.');
                    }
                    var blob = new Blob([outputData.buffer], { type: 'video/mp4' });
                    var url = URL.createObjectURL(blob);
                    if (overlayFormSubmit) overlayFormSubmit.disabled = false;
                    if (overlayFormSubmit) overlayFormSubmit.innerHTML = '<span class="material-symbols-outlined text-lg">movie_edit</span> Final video oluştur';
                    if (submitRow) submitRow.classList.remove('hidden');
                    if (overlayProgressText) overlayProgressText.classList.add('hidden');
                    if (overlayFormSuccess) { overlayFormSuccess.textContent = 'Overlay tarayıcıda uygulandı. İndirebilir veya sunucuya yükleyebilirsiniz.'; overlayFormSuccess.classList.remove('hidden'); }
                    var downloadLink = document.getElementById('overlayDownloadLink');
                    if (downloadLink) { downloadLink.href = url; downloadLink.download = 'parsel-drone-overlay' + (ada ? '-ada-' + ada : '') + (parsel ? '-parsel-' + parsel : '') + '.mp4'; }
                    var uploadBtn = document.getElementById('overlayUploadBtn');
                    if (overlayResultActions) overlayResultActions.classList.remove('hidden');
                    if (uploadBtn) {
                        uploadBtn.onclick = function() {
                            uploadBtn.disabled = true;
                            uploadBtn.innerHTML = '<span class="material-symbols-outlined text-lg animate-spin">progress_activity</span> Yükleniyor...';
                            var fd = new FormData();
                            fd.append('action', 'upload_drone_video');
                            fd.append('video', blob, 'parsel-drone-overlay.mp4');
                            fd.append('ada', ada);
                            fd.append('parsel_no', parsel);
                            fetch(baseUrl, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd })
                                .then(function(r) { return r.json(); })
                                .then(function(res) {
                                    uploadBtn.disabled = false;
                                    uploadBtn.innerHTML = '<span class="material-symbols-outlined text-lg">cloud_upload</span> Sunucuya yükle';
                                    if (res.success) {
                                        overlayFormSuccess.textContent = 'Video sunucuya yüklendi. Sayfa yenileniyor...';
                                        setTimeout(function() { closeOverlayModal(); window.location.reload(); }, 1500);
                                    } else {
                                        overlayFormError.textContent = res.message || 'Yükleme başarısız.';
                                        overlayFormError.classList.remove('hidden');
                                    }
                                })
                                .catch(function(err) {
                                    uploadBtn.disabled = false;
                                    uploadBtn.innerHTML = '<span class="material-symbols-outlined text-lg">cloud_upload</span> Sunucuya yükle';
                                    overlayFormError.textContent = 'Yükleme hatası: ' + (err.message || 'Ağ hatası');
                                    overlayFormError.classList.remove('hidden');
                                });
                        };
                    }
                }).catch(function(err) {
                    try {
                        if (overlayFormSubmit) { overlayFormSubmit.disabled = false; overlayFormSubmit.innerHTML = '<span class="material-symbols-outlined text-lg">movie_edit</span> Final video oluştur'; }
                        if (submitRow) submitRow.classList.remove('hidden');
                        if (overlayProgressText) overlayProgressText.classList.add('hidden');
                        var errMsg = (err && err.message) ? String(err.message) : '';
                        if (errMsg.indexOf('Video indirilemedi') !== -1) {
                            errMsg = 'Video bu adresten indirilemiyor. Linkin aynı sitede veya CORS ile erişilebilir olduğundan emin olun. Konsolda [TKGM Overlay] Video adresi: ... satırından linki kontrol edebilirsiniz.';
                        } else if (errMsg.indexOf('failed to import ffmpeg-core') !== -1) {
                            errMsg = 'FFmpeg çekirdeği yüklenemedi. Sunucuda Content-Security-Policy (CORS veya script-src) blob: veya CDN\'i engelliyor olabilir. Hosting ayarlarını kontrol edin veya farklı bir tarayıcı/cihaz deneyin.';
                        } else if (!errMsg) {
                            errMsg = 'Overlay işlemi başarısız. F12 ile Konsolu açıp [TKGM Overlay] satırındaki hatayı kontrol edin.';
                        }
                        if (overlayFormError) { overlayFormError.textContent = errMsg; overlayFormError.classList.remove('hidden'); }
                        if (typeof console !== 'undefined' && console.error) {
                            console.error('[TKGM Overlay / FFmpeg]', errMsg, err || '');
                        }
                    } catch (e2) {
                        if (typeof console !== 'undefined' && console.error) console.error('[TKGM Overlay] catch içi hata:', e2);
                    }
                });
            });
        }
    })();

    function ajax(url) {
        return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); });
    }

    function fillSelect(sel, list, valueKey, labelKey) {
        labelKey = labelKey || 'adi';
        sel.innerHTML = '';
        var first = document.createElement('option');
        first.value = '';
        first.textContent = list.length ? 'Seçiniz' : '—';
        sel.appendChild(first);
        list.forEach(function(item) {
            var opt = document.createElement('option');
            opt.value = item[valueKey] || item.kodu;
            opt.textContent = item[labelKey] || item.adi || opt.value;
            sel.appendChild(opt);
        });
        sel.disabled = false;
    }

    function disableAfter(select) {
        var names = ['ilce', 'mahalle'];
        var idx = names.indexOf(select.id);
        for (var i = idx + 1; i < names.length; i++) {
            var el = document.getElementById(names[i]);
            if (el && el.tagName === 'SELECT') {
                el.innerHTML = '<option value="">—</option>';
                el.disabled = true;
            }
        }
        if (select.id === 'mahalle') {
            document.getElementById('ada').value = '';
            document.getElementById('parsel').value = '';
        }
    }

    var il = document.getElementById('il');
    var ilce = document.getElementById('ilce');
    var mahalle = document.getElementById('mahalle');
    var ada = document.getElementById('ada');
    var parsel = document.getElementById('parsel');
    var form = document.getElementById('parselSorguForm');
    var sonucAlani = document.getElementById('sonucAlani');
    var sonucIcerik = document.getElementById('sonucIcerik');
    var parselHaritaWrap = document.getElementById('parselHaritaWrap');
    var hataAlani = document.getElementById('hataAlani');
    var hataMesaji = document.getElementById('hataMesaji');
    var yukleniyor = document.getElementById('yukleniyor');
    var btnSorgula = document.getElementById('btnSorgula');

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
            var t = setTimeout(function() { finish(new Error('Google Maps zaman aşımı. API key ve referans kısıtlamalarını kontrol edin.')); }, 15000);
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

    function loadScript(src) {
        return new Promise(function(resolve, reject) {
            var s = document.createElement('script');
            s.src = src;
            s.onload = resolve;
            s.onerror = reject;
            document.head.appendChild(s);
        });
    }
    // İlleri yükle
    ajax(baseUrl + sep + 'action=iller').then(function(res) {
        if (res.success && res.data && res.data.length) {
            fillSelect(il, res.data);
        }
    }).catch(function() {
        fillSelect(il, []);
    });

    il.addEventListener('change', function() {
        var kodu = this.value;
        disableAfter(il);
        if (!kodu) return;
        ilce.disabled = true;
        ilce.innerHTML = '<option value="">Yükleniyor...</option>';
        ajax(baseUrl + sep + 'action=ilceler&il_kodu=' + encodeURIComponent(kodu)).then(function(res) {
            fillSelect(ilce, res.success && res.data ? res.data : []);
        });
    });

    ilce.addEventListener('change', function() {
        var kodu = this.value;
        disableAfter(ilce);
        if (!kodu) return;
        mahalle.disabled = true;
        mahalle.innerHTML = '<option value="">Yükleniyor...</option>';
        ajax(baseUrl + sep + 'action=mahalleler&il_kodu=' + encodeURIComponent(il.value) + '&ilce_kodu=' + encodeURIComponent(kodu)).then(function(res) {
            fillSelect(mahalle, res.success && res.data ? res.data : []);
        });
    });

    mahalle.addEventListener('change', function() {
        disableAfter(mahalle);
        var kodu = this.value;
        if (!kodu) return;
    });

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
                var ilAdi = il.options[il.selectedIndex] ? il.options[il.selectedIndex].text : q.il_kodu || '';
                var ilceAdi = ilce.options[ilce.selectedIndex] ? ilce.options[ilce.selectedIndex].text : q.ilce_kodu || '';
                var mahalleAdi = mahalle.options[mahalle.selectedIndex] ? mahalle.options[mahalle.selectedIndex].text : q.mahalle_kodu || '';
                var url = d.parselsorgu_url || 'https://parselsorgu.tkgm.gov.tr';
                sonucIcerik.innerHTML =
                    '<p class="text-amber-700 dark:text-amber-300 text-sm mb-4">Bu ada/parsel için CBS servisi şu an detay döndürmedi. Girilen bilgiler aşağıdadır; resmi sorgu için TKGM Parsel Sorgu sayfasını kullanabilirsiniz.</p>' +
                    '<table class="w-full text-sm text-left text-gray-700 dark:text-gray-300 mb-4"><tbody>' +
                    '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">İl</th><td class="py-2">' + escapeHtml(ilAdi) + '</td></tr>' +
                    '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">İlçe</th><td class="py-2">' + escapeHtml(ilceAdi) + '</td></tr>' +
                    '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">Mahalle / Köy</th><td class="py-2">' + escapeHtml(mahalleAdi) + '</td></tr>' +
                    '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">Ada</th><td class="py-2">' + escapeHtml(String(q.ada || '')) + '</td></tr>' +
                    '<tr><th class="py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">Parsel</th><td class="py-2">' + escapeHtml(String(q.parsel || '')) + '</td></tr>' +
                    '</tbody></table>' +
                    '<a href="' + escapeHtml(url) + '" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">' +
                    'Parsel Sorgu (TKGM) sayfasında kontrol et <span class="material-symbols-outlined text-lg">open_in_new</span></a>';
                parselHaritaWrap.classList.add('hidden');
                var btn3DWrap = document.getElementById('parsel3DBtnWrap');
                if (btn3DWrap) btn3DWrap.classList.add('hidden');
                var q = d.query || {};
                window.currentParselInfo = { il_adi: ilAdi, ilce_adi: ilceAdi, mahalle_adi: mahalleAdi, ada: String(q.ada || ''), parsel_no: String(q.parsel || ''), alan_m2: null, nitelik: '' };
                /* CBS geometri döndürmediği için 3D/video eski parsel verisi kullanmasın */
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

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        hataAlani.classList.add('hidden');
        sonucAlani.classList.add('hidden');
        var adaVal = (ada.value || '').trim();
        var parselVal = (parsel.value || '').trim();
        if (!il.value || !ilce.value || !mahalle.value || !adaVal || !parselVal) {
            hataMesaji.textContent = 'Lütfen il, ilçe, mahalle seçin ve ada ile parsel numaralarını girin.';
            hataAlani.classList.remove('hidden');
            return;
        }
        btnSorgula.disabled = true;
        yukleniyor.classList.remove('hidden');
        var q = baseUrl + sep + 'action=detay&il_kodu=' + encodeURIComponent(il.value) + '&ilce_kodu=' + encodeURIComponent(ilce.value) + '&mahalle_kodu=' + encodeURIComponent(mahalle.value) + '&ada=' + encodeURIComponent(adaVal) + '&parsel=' + encodeURIComponent(parselVal);
        ajax(q).then(function(res) {
            yukleniyor.classList.add('hidden');
            btnSorgula.disabled = false;
            showDetayResult(res);
        }).catch(function(err) {
            yukleniyor.classList.add('hidden');
            btnSorgula.disabled = false;
            hataMesaji.textContent = 'İstek sırasında bir hata oluştu.';
            hataAlani.classList.remove('hidden');
        });
    });

    document.getElementById('btnOrnek').addEventListener('click', function() {
        hataAlani.classList.add('hidden');
        sonucAlani.classList.add('hidden');
        var btnOrnek = this;
        btnOrnek.disabled = true;
        yukleniyor.classList.remove('hidden');
        ajax(baseUrl + sep + 'action=ornek').then(function(res) {
            yukleniyor.classList.add('hidden');
            btnOrnek.disabled = false;
            showDetayResult(res);
        }).catch(function() {
            yukleniyor.classList.add('hidden');
            btnOrnek.disabled = false;
            hataMesaji.textContent = 'Örnek veri yüklenemedi.';
            hataAlani.classList.remove('hidden');
        });
    });

    document.getElementById('btnTemizle').addEventListener('click', function() {
        il.value = '';
        ilce.innerHTML = '<option value="">Önce il seçin</option>'; ilce.disabled = true;
        mahalle.innerHTML = '<option value="">Önce ilçe seçin</option>'; mahalle.disabled = true;
        ada.value = '';
        parsel.value = '';
        sonucAlani.classList.add('hidden');
        parselHaritaWrap.classList.add('hidden');
        var btn3DWrap = document.getElementById('parsel3DBtnWrap');
        if (btn3DWrap) btn3DWrap.classList.add('hidden');
        if (window.parselMap) {
            if (typeof window.parselMap.remove === 'function') window.parselMap.remove();
            window.parselMap = null;
        }
        var pH = document.getElementById('parselHarita');
        if (pH) pH.innerHTML = '';
        hataAlani.classList.add('hidden');
        /* Video/3D eski parsel verisi kullanmasın */
        window.currentParselInfo = null;
        window.currentParselGeojson = null;
        window.parselBoundingSphere = null;
        window.parselOrbitRange = null;
    });

    // --- 3D Dron Görünümü (Cesium lazy-load) ---
    var cesium3DViewer = null;
    var orbitAnimationId = null;
    var cesiumLoadPromise = null;

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
    var orbitStartTime = 0;
    var orbitDuration = 20;
    var orbitSpeed = 1;
    var mediaRecorder = null;
    var recordedChunks = [];
    var isRecording = false;

    var RANGE_MULT_GLOBAL = 1.15;
    var CAMERA_PRESETS = {
        yaklasma: { heading: 0, pitchDeg: -60, slug: 'yaklasma', motion: 'approach', rangeStartMult: 2.5, rangeEndMult: 2, flyoverDurationMult: 1 },
        yakinCekim: { heading: 0, pitchDeg: -52, rangeMult: 0.72, slug: 'yakinCekim', motion: 'orbit', orbitStart: 0, orbitEnd: Math.PI * 0.2 },
        yakinCekimHafif: { heading: 0, pitchDeg: -50, rangeMult: 0.92, slug: 'yakinCekimHafif', motion: 'orbit', orbitStart: 0, orbitEnd: Math.PI * 0.15 },
        soldanSaga60: { heading: 0, pitchDeg: -55, rangeMult: 1.8, slug: 'soldanSaga60', motion: 'orbit', orbitStart: 0, orbitEnd: Math.PI * 0.5 },
        sagdanSola60: { heading: 0, pitchDeg: -55, rangeMult: 1.8, slug: 'sagdanSola60', motion: 'orbit', orbitStart: Math.PI, orbitEnd: Math.PI * 0.5 },
        tepeden: { heading: 0, pitchDeg: -72, rangeMult: 1.0, slug: 'tepeden', motion: 'orbit', orbitStart: 0, orbitEnd: Math.PI * 0.5 },
        kuzey: { heading: Math.PI, pitchDeg: -45, rangeMult: 2, slug: 'kuzey', motion: 'flyover', flyoverDir: 'north-south', flyoverStartMult: 2, flyoverEndMult: -1.5, flyoverDurationMult: 1 },
        guney: { heading: 0, pitchDeg: -48, rangeMult: 2, slug: 'guney', motion: 'flyover', flyoverDir: 'south-north', flyoverStartMult: 2, flyoverEndMult: -1.5, flyoverDurationMult: 1 },
        uzak: { heading: 0, pitchDeg: -48, rangeMult: 1.3, slug: 'uzak', motion: 'orbit', orbitStart: 0, orbitEnd: Math.PI * 2, orbitDurationMult: 3 },
        uzak90: { heading: 0, pitchDeg: -48, rangeMult: 1.3, slug: 'uzak90', motion: 'orbit', orbitStart: 0, orbitEnd: Math.PI * 0.5 },
        uzak180: { heading: 0, pitchDeg: -48, rangeMult: 1.3, slug: 'uzak180', motion: 'orbit', orbitStart: 0, orbitEnd: Math.PI },
        egimli: { heading: 0, pitchDeg: -52, rangeMult: 1.0, slug: 'egimli', motion: 'orbit', orbitStart: 0, orbitEnd: Math.PI * 0.5 }
    };

    /** Test için 5 sn; normalde 60000 */
    var DRONE_VIDEO_TOTAL_MS = 5000;
    var DRONE_SEQUENCE_60 = [
        { presetKey: 'yaklasma', durationMs: 10000 },
        { presetKey: 'yakinCekim', durationMs: 8000 },
        { presetKey: 'yakinCekimHafif', durationMs: 7000 },
        { presetKey: 'soldanSaga60', durationMs: 18000 },
        { presetKey: 'kuzey', durationMs: 10000 },
        { presetKey: 'sagdanSola60', durationMs: 18000 },
        { presetKey: 'guney', durationMs: 10000 }
    ];

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
        } else if (preset.motion === 'linear') {
            var h = preset.linearStart != null ? preset.linearStart : preset.heading;
            cesium3DViewer.camera.lookAt(boundingSphere.center, new Cesium.HeadingPitchRange(h, Cesium.Math.toRadians(preset.pitchDeg), range));
        } else {
            cesium3DViewer.camera.viewBoundingSphere(boundingSphere, new Cesium.HeadingPitchRange(preset.heading, Cesium.Math.toRadians(preset.pitchDeg), range));
        }
        if (cesium3DViewer.scene) cesium3DViewer.scene.requestRender();
    }

    function recordFromAngle(angleKey, durationMs) {
        return new Promise(function(resolve, reject) {
            if (!cesium3DViewer || !cesium3DViewer.scene || !cesium3DViewer.scene.canvas || !window.Cesium) {
                reject(new Error('Cesium görünüm hazır değil'));
                return;
            }
            var Cesium = window.Cesium;
            var preset = CAMERA_PRESETS[angleKey];
            if (!preset) preset = CAMERA_PRESETS.kuzey;
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
            var range = baseRange * (preset.rangeMult != null ? preset.rangeMult : 1) * RANGE_MULT_GLOBAL;
            var flyoverDuration = preset.motion === 'flyover' ? 6000 * (preset.flyoverDurationMult || 1) : (preset.motion === 'approach' ? durationMs : (preset.orbitDurationMult ? Math.max(durationMs * preset.orbitDurationMult, 30000) : durationMs));
            applyCameraPreset(angleKey);
            setTimeout(function() {
                var canvas = cesium3DViewer.scene.canvas;
                if (!canvas.width || !canvas.height) {
                    reject(new Error('Canvas boyutu geçersiz'));
                    return;
                }
                var stream = canvas.captureStream(30);
                if (!stream || !stream.getVideoTracks || stream.getVideoTracks().length === 0) {
                    reject(new Error('Canvas video akışı alınamadı'));
                    return;
                }
                var mime = 'video/webm';
                if (MediaRecorder.isTypeSupported('video/mp4; codecs=avc1')) {
                    mime = 'video/mp4; codecs=avc1';
                } else if (MediaRecorder.isTypeSupported('video/mp4')) {
                    mime = 'video/mp4';
                } else if (MediaRecorder.isTypeSupported('video/webm; codecs=h264')) {
                    mime = 'video/webm; codecs=h264';
                } else if (MediaRecorder.isTypeSupported('video/webm; codecs=vp9')) {
                    mime = 'video/webm; codecs=vp9';
                } else if (MediaRecorder.isTypeSupported('video/webm; codecs=vp8')) {
                    mime = 'video/webm; codecs=vp8';
                }
                var chunks = [];
                var rec = new MediaRecorder(stream, { mimeType: mime, videoBitsPerSecond: 12000000, audioBitsPerSecond: 0 });
                rec.ondataavailable = function(e) { if (e.data && e.data.size > 0) chunks.push(e.data); };
                rec.onstop = function() {
                    stream.getVideoTracks().forEach(function(t) { t.stop(); });
                    if (chunks.length === 0) {
                        reject(new Error('Kayıt verisi alınamadı'));
                        return;
                    }
                    var blob = new Blob(chunks, { type: mime });
                    resolve(blob);
                };
                rec.onerror = function(e) { reject(new Error('Kayıt hatası: ' + (e.error ? e.error.message : 'bilinmeyen'))); };
                rec.start(250);
                var dataInterval = setInterval(function() {
                    if (rec.state === 'recording') try { rec.requestData(); } catch (e) {}
                }, 250);
                var startTime = performance.now();
                var target = boundingSphere.center;
                var flyoverData = preset.motion === 'flyover' ? getFlyoverPositions(boundingSphere, baseRange, preset, Cesium) : null;
                var lerpResult = new Cesium.Cartesian3();
                var animDuration = flyoverDuration;
                function orbitTick() {
                    var elapsed = (performance.now() - startTime) / 1000;
                    if (elapsed >= animDuration / 1000) return;
                    var t = elapsed / (animDuration / 1000);
                    var pitchRad = Cesium.Math.toRadians(preset.pitchDeg || -45);
                    if (preset.motion === 'flyover' && flyoverData) {
                        var pos = Cesium.Cartesian3.lerp(flyoverData.startPos, flyoverData.endPos, t, lerpResult);
                        cesium3DViewer.camera.setView({
                            destination: pos,
                            orientation: { heading: flyoverData.heading, pitch: flyoverData.pitchRad, roll: 0 }
                        });
                    } else if (preset.motion === 'approach') {
                        var rangeStart = baseRange * (preset.rangeStartMult != null ? preset.rangeStartMult : 3) * RANGE_MULT_GLOBAL;
                        var rangeEnd = baseRange * (preset.rangeEndMult != null ? preset.rangeEndMult : 1.1) * RANGE_MULT_GLOBAL;
                        var approachRange = rangeStart + t * (rangeEnd - rangeStart);
                        cesium3DViewer.camera.lookAt(target, new Cesium.HeadingPitchRange(preset.heading != null ? preset.heading : 0, pitchRad, approachRange));
                    } else if (preset.motion === 'linear') {
                        var h0 = preset.linearStart != null ? preset.linearStart : 0;
                        var h1 = preset.linearEnd != null ? preset.linearEnd : Math.PI;
                        var heading = h0 + t * (h1 - h0);
                        cesium3DViewer.camera.lookAt(target, new Cesium.HeadingPitchRange(heading, pitchRad, range));
                    } else {
                        var orbitStart = preset.orbitStart != null ? preset.orbitStart : preset.heading;
                        var orbitEnd = preset.orbitEnd != null ? preset.orbitEnd : preset.heading + Math.PI * 0.5;
                        var heading = orbitStart + t * (orbitEnd - orbitStart);
                        cesium3DViewer.camera.viewBoundingSphere(boundingSphere, new Cesium.HeadingPitchRange(heading, pitchRad, range));
                    }
                    cesium3DViewer.scene.requestRender();
                    requestAnimationFrame(orbitTick);
                }
                orbitTick();
                setTimeout(function() {
                    clearInterval(dataInterval);
                    if (rec.state === 'recording') {
                        try { rec.requestData(); } catch (e) {}
                        setTimeout(function() {
                            if (rec.state === 'recording') rec.stop();
                        }, 200);
                    }
                }, flyoverDuration);
            }, 1500);
        });
    }

    function downloadBlob(blob, filename) {
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(url);
    }

    function downloadAllAngles() {
        if (!cesium3DViewer) return;
        var presetKeys = ['tepeden', 'kuzey', 'guney', 'uzak', 'egimli'];
        var durationMs = Math.max(4000, (parseInt(document.getElementById('select3DSure').value, 10) || 20) * 500);
        var btn = document.getElementById('btn3DTumAcilarindan');
        var origHtml = btn ? btn.innerHTML : '';
        if (btn) { btn.disabled = true; btn.innerHTML = '<span class="material-symbols-outlined text-lg animate-spin">progress_activity</span> Kaydediliyor...'; }
        var idx = 0;
        function next() {
            if (idx >= presetKeys.length) {
                if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
                return;
            }
            var key = presetKeys[idx];
            var preset = CAMERA_PRESETS[key];
            recordFromAngle(key, durationMs).then(function(blob) {
                try {
                    downloadBlob(blob, 'parsel-' + (preset.slug || key) + '.webm');
                } catch (e) {
                    if (typeof console !== 'undefined' && console.error) console.error('[TKGM 3D] downloadBlob:', e);
                }
                idx++;
                setTimeout(next, 800);
            }).catch(function(err) {
                try {
                    if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
                    var msg = (err && err.message) ? err.message : 'Video kaydı sırasında hata oluştu.';
                    if (typeof alert === 'function') alert(msg);
                    else if (typeof console !== 'undefined' && console.error) console.error('[TKGM 3D]', msg, err);
                } catch (e2) {
                    if (typeof console !== 'undefined' && console.error) console.error('[TKGM 3D] catch:', e2);
                }
            });
        }
        next();
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
            setTimeout(function() {
                var canvas = cesium3DViewer.scene.canvas;
                if (!canvas.width || !canvas.height) {
                    reject(new Error('Canvas boyutu geçersiz'));
                    return;
                }
                var VIDEO_W = 1080, VIDEO_H = 1920;
                var agent = window.selectedAgentForVideo;
                var compositeCanvas = document.createElement('canvas');
                compositeCanvas.width = VIDEO_W;
                compositeCanvas.height = VIDEO_H;
                var compositeCtx = compositeCanvas.getContext('2d');
                var agentPhotoImg = null;
                if (agent && agent.photo) {
                    var img = new Image();
                    img.crossOrigin = 'anonymous';
                    img.onload = function() { agentPhotoImg = img; };
                    img.onerror = function() { agentPhotoImg = null; };
                    img.src = agent.photo;
                }
                function drawAgentOverlay(ctx, cw, ch, a) {
                    if (!a) return;
                    ctx.save();
                    ctx.imageSmoothingEnabled = true;
                    ctx.imageSmoothingQuality = 'high';
                    var padX = 48;
                    var padY = 48;
                    var avatarSize = 460;
                    var gap = 40;
                    var nameFont = 60;
                    var phoneFont = 60;
                    var lineH = 85;
                    var pillPadH = 52;
                    var pillPadV = 28;
                    var nameStr = ((a.first_name || '') + ' ' + (a.last_name || '')).trim().substring(0, 28) || '—';
                    var phoneStr = (a.phone || '—').substring(0, 24);
                    var blockH = Math.max(avatarSize, 2 * (nameFont + pillPadV * 2) + lineH);
                    var ay = ch - padY - blockH / 2;
                    var ax = padX + avatarSize / 2;
                    if (agentPhotoImg && agentPhotoImg.complete && agentPhotoImg.naturalWidth) {
                        ctx.beginPath();
                        ctx.arc(ax, ay, avatarSize / 2, 0, Math.PI * 2);
                        ctx.closePath();
                        ctx.save();
                        ctx.clip();
                        ctx.drawImage(agentPhotoImg, ax - avatarSize/2, ay - avatarSize/2, avatarSize, avatarSize);
                        ctx.restore();
                        ctx.strokeStyle = 'rgba(255,255,255,0.6)';
                        ctx.lineWidth = 4;
                        ctx.beginPath();
                        ctx.arc(ax, ay, avatarSize / 2, 0, Math.PI * 2);
                        ctx.stroke();
                    }
                    var tx = padX + avatarSize + gap;
                    var phonePillH = phoneFont + pillPadV * 2;
                    var namePillH = nameFont + pillPadV * 2;
                    var blockContentH = phonePillH + lineH + namePillH;
                    var ty = ay - blockContentH / 2 + phonePillH / 2 + pillPadV;
                    ctx.font = '600 ' + phoneFont + 'px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
                    var phoneW = ctx.measureText(phoneStr).width;
                    var phonePillW = Math.min(phoneW + pillPadH * 2, cw - tx - padX);
                    var phonePillX = tx;
                    var phonePillY = ty - phonePillH / 2;
                    var phoneGrad = ctx.createLinearGradient(phonePillX, phonePillY, phonePillX + phonePillW, phonePillY + phonePillH);
                    phoneGrad.addColorStop(0, 'rgba(255, 255, 255, 0.98)');
                    phoneGrad.addColorStop(1, 'rgba(240, 249, 255, 0.98)');
                    ctx.fillStyle = phoneGrad;
                    roundRect(ctx, phonePillX, phonePillY, phonePillW, phonePillH, 20);
                    ctx.fill();
                    ctx.fillStyle = 'rgba(30, 58, 138, 0.95)';
                    ctx.fillText(phoneStr, phonePillX + phonePillW / 2, ty);
                    var nameY = ty + lineH;
                    ctx.font = '600 ' + nameFont + 'px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
                    var nameW = ctx.measureText(nameStr).width;
                    var namePillW = Math.min(nameW + pillPadH * 4, cw - tx - padX);
                    var namePillX = tx;
                    var namePillY = nameY - namePillH / 2;
                    var nameGrad = ctx.createLinearGradient(namePillX, namePillY, namePillX + namePillW, namePillY + namePillH);
                    nameGrad.addColorStop(0, 'rgba(30, 58, 138, 0.96)');
                    nameGrad.addColorStop(1, 'rgba(88, 28, 135, 0.96)');
                    ctx.fillStyle = nameGrad;
                    roundRect(ctx, namePillX, namePillY, namePillW, namePillH, 22);
                    ctx.fill();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = '#ffffff';
                    ctx.fillText(nameStr, namePillX + namePillW / 2, nameY);
                    ctx.restore();
                }
                function roundRect(ctx, x, y, w, h, r) {
                    ctx.beginPath();
                    ctx.moveTo(x + r, y);
                    ctx.lineTo(x + w - r, y);
                    ctx.quadraticCurveTo(x + w, y, x + w, y + r);
                    ctx.lineTo(x + w, y + h - r);
                    ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
                    ctx.lineTo(x + r, y + h);
                    ctx.quadraticCurveTo(x, y + h, x, y + h - r);
                    ctx.lineTo(x, y + r);
                    ctx.quadraticCurveTo(x, y, x + r, y);
                    ctx.closePath();
                }
                var postRenderCallback = function() {
                    compositeCtx.drawImage(canvas, 0, 0, canvas.width, canvas.height, 0, 0, VIDEO_W, VIDEO_H);
                    drawAgentOverlay(compositeCtx, VIDEO_W, VIDEO_H, agent);
                };
                cesium3DViewer.scene.postRender.addEventListener(postRenderCallback);
                var stream = compositeCanvas.captureStream(30);
                if (!stream || !stream.getVideoTracks || stream.getVideoTracks().length === 0) {
                    reject(new Error('Canvas video akışı alınamadı'));
                    return;
                }
                var mime = 'video/webm';
                if (MediaRecorder.isTypeSupported('video/mp4; codecs=avc1')) {
                    mime = 'video/mp4; codecs=avc1';
                } else if (MediaRecorder.isTypeSupported('video/mp4')) {
                    mime = 'video/mp4';
                } else if (MediaRecorder.isTypeSupported('video/webm; codecs=h264')) {
                    mime = 'video/webm; codecs=h264';
                } else if (MediaRecorder.isTypeSupported('video/webm; codecs=vp9')) {
                    mime = 'video/webm; codecs=vp9';
                } else if (MediaRecorder.isTypeSupported('video/webm; codecs=vp8')) {
                    mime = 'video/webm; codecs=vp8';
                }
                var chunks = [];
                var rec = new MediaRecorder(stream, { mimeType: mime, videoBitsPerSecond: 12000000, audioBitsPerSecond: 0 });
                rec.ondataavailable = function(e) { if (e.data && e.data.size > 0) chunks.push(e.data); };
                rec.onstop = function() {
                    cesium3DViewer.scene.postRender.removeEventListener(postRenderCallback);
                    stream.getVideoTracks().forEach(function(t) { t.stop(); });
                    if (chunks.length === 0) {
                        reject(new Error('Kayıt verisi alınamadı'));
                        return;
                    }
                    var blob = new Blob(chunks, { type: mime });
                    resolve(blob);
                };
                rec.onerror = function(e) { reject(new Error('Kayıt hatası: ' + (e.error ? e.error.message : 'bilinmeyen'))); };
                rec.start(250);
                var dataInterval = setInterval(function() {
                    if (rec.state === 'recording') try { rec.requestData(); } catch (e) {}
                }, 250);
                var startTime = performance.now();
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
                    var elapsedMs = performance.now() - startTime;
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
                    requestAnimationFrame(tick);
                }
                tick();
            }, 1500);
        });
    }

    function downloadFullDroneSequence(totalMs) {
        if (!cesium3DViewer) return;
        var btn = document.getElementById('btn3DDrone60');
        var origHtml = btn ? btn.innerHTML : '';
        if (btn) { btn.disabled = true; btn.innerHTML = '<span class="material-symbols-outlined text-lg animate-spin">progress_activity</span> Kaydediliyor (' + (Math.round((totalMs || DRONE_VIDEO_TOTAL_MS) / 1000) + ' sn') + ')...'; }
        recordFullDroneSequence(totalMs || DRONE_VIDEO_TOTAL_MS).then(function(blob) {
            try { downloadBlob(blob, 'parsel-drone-' + Math.round((totalMs || DRONE_VIDEO_TOTAL_MS) / 1000) + 'sn.webm'); } catch (e) { if (console && console.error) console.error('[TKGM 3D]', e); }
            if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
        }).catch(function(err) {
            try {
                if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
                var msg = (err && err.message) ? err.message : 'Video kaydı sırasında hata oluştu.';
                if (typeof alert === 'function') alert(msg); else if (console && console.error) console.error('[TKGM 3D]', msg, err);
            } catch (e2) { if (console && console.error) console.error('[TKGM 3D] catch:', e2); }
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
        var center = getCenterFromGeom(geom);

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
        var containerId = 'cesiumVideoContainer';
        cesium3DViewer = new Cesium.Viewer(containerId, viewerOptions);
        if (cesium3DViewer.creditContainer) {
            cesium3DViewer.creditContainer.style.display = 'none';
        }

        var use3DTiles = !!googleMapsApiKey;
        var toggleImageryEl = document.getElementById('toggleImageryMode');
        /* Google Photorealistic 3D varsayılan; Ion imagery alternatif (toggle ile) */
        if (toggleImageryEl) toggleImageryEl.checked = !use3DTiles && !!cesiumIonToken;
        var imageryMode = cesiumIonToken && toggleImageryEl && toggleImageryEl.checked;
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
                        maximumScreenSpaceError: 16
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
            /* Glow layer (dış parlama) */
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
            /* Ana çizgi */
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

        function finishInit() {
            addParselToScene(cesium3DViewer, geom);
            var bs = getBoundingSphereFromGeom(geom, Cesium);
            if (bs) {
                window.parselBoundingSphere = bs.sphere;
                window.parselOrbitRange = bs.range;
                var sel = document.getElementById('select3DAci');
                applyCameraPreset(sel && sel.value ? sel.value : 'kuzey');
            }
            if (cesium3DViewer.resize) cesium3DViewer.resize();
            cesium3DViewer.scene.requestRender();
        }

        finishInit();
        if (use3DTiles && !imageryMode) {
            try {
                var tileset = window.cesium3DTileset || cesium3DViewer.scene.primitives.get(cesium3DViewer.scene.primitives.length - 1);
                if (tileset && tileset.readyPromise) {
                    tileset.readyPromise.then(function() {
                        finishInit();
                    }).catch(function() {
                        finishInit();
                    });
                }
            } catch (e) {}
        }
        var toggleWrap = document.getElementById('toggleImageryModeWrap');
        var toggleImagery = document.getElementById('toggleImageryMode');
        if (cesiumIonToken && googleMapsApiKey && toggleWrap && toggleImagery) {
            toggleWrap.classList.remove('hidden');
            toggleWrap.classList.add('flex');
                toggleImagery.checked = imageryMode;
                toggleImagery.onchange = function() {
                    if (this.checked) {
                        if (window.cesium3DTileset && cesium3DViewer) {
                            cesium3DViewer.scene.primitives.remove(window.cesium3DTileset);
                            window.cesium3DTileset = null;
                        }
                        cesium3DViewer.scene.globe.show = true;
                    } else {
                        if (typeof Cesium.createGooglePhotorealistic3DTileset === 'function') {
                            Cesium.createGooglePhotorealistic3DTileset(googleMapsApiKey, { showCreditsOnScreen: false, maximumScreenSpaceError: 8 }).then(function(ts) {
                                cesium3DViewer.scene.primitives.add(ts);
                                window.cesium3DTileset = ts;
                                return ts.readyPromise;
                            }).then(function() {
                                if (cesium3DViewer) cesium3DViewer.scene.globe.show = false;
                            }).catch(function() {
                                if (cesium3DViewer) cesium3DViewer.scene.globe.show = true;
                            });
                        } else {
                            var ts = cesium3DViewer.scene.primitives.add(new Cesium.Cesium3DTileset({
                                url: 'https://tile.googleapis.com/v1/3dtiles/root.json?key=' + encodeURIComponent(googleMapsApiKey),
                                showCreditsOnScreen: false,
                                maximumScreenSpaceError: 16
                            }));
                            window.cesium3DTileset = ts;
                            ts.readyPromise.then(function() {
                                cesium3DViewer.scene.globe.show = false;
                            }).catch(function() {
                                cesium3DViewer.scene.globe.show = true;
                            });
                        }
                    }
                    cesium3DViewer.scene.requestRender();
                };
        }
    }

    function runOrbitAnimation(cbOnComplete) {
        if (!cesium3DViewer || !window.Cesium) return;
        var geojson = window.currentParselGeojson;
        if (!geojson) return;
        var Cesium = window.Cesium;
        var geom = (geojson.type === 'Feature' ? geojson.geometry : (geojson.type === 'FeatureCollection' && geojson.features && geojson.features[0] ? geojson.features[0].geometry : geojson.geometry)) || geojson.geometry;
        var boundingSphere = window.parselBoundingSphere;
        var range = window.parselOrbitRange;
        if (!boundingSphere || range == null) {
            var bs = getBoundingSphereFromGeom(geom, Cesium);
            if (!bs) return;
            boundingSphere = bs.sphere;
            range = bs.range;
        }
        cesium3DViewer.camera.viewBoundingSphere(boundingSphere, new Cesium.HeadingPitchRange(0, Cesium.Math.toRadians(-45), range));

        var dur = parseInt(document.getElementById('select3DSure').value, 10) || 20;
        var spd = parseFloat(document.getElementById('select3DHiz').value) || 1;
        orbitDuration = dur;
        orbitSpeed = spd;
        orbitStartTime = performance.now();

        document.getElementById('btn3DTurBaslat').classList.add('hidden');
        document.getElementById('btn3DTurDurdur').classList.remove('hidden');
        document.getElementById('select3DHiz').disabled = true;
        document.getElementById('select3DSure').disabled = true;

        function tick() {
            var elapsed = (performance.now() - orbitStartTime) / 1000;
            var t = Math.min(elapsed / (orbitDuration / orbitSpeed), 1);
            var heading = t * Math.PI * 2;
            cesium3DViewer.camera.viewBoundingSphere(boundingSphere, new Cesium.HeadingPitchRange(heading, Cesium.Math.toRadians(-45), range));
            if (t >= 1) {
                orbitAnimationId = null;
                document.getElementById('btn3DTurBaslat').classList.remove('hidden');
                document.getElementById('btn3DTurDurdur').classList.add('hidden');
                document.getElementById('select3DHiz').disabled = false;
                document.getElementById('select3DSure').disabled = false;
                if (cbOnComplete) cbOnComplete();
                return;
            }
            orbitAnimationId = requestAnimationFrame(tick);
        }
        orbitAnimationId = requestAnimationFrame(tick);
    }

    function stopOrbitAnimation() {
        if (orbitAnimationId) {
            cancelAnimationFrame(orbitAnimationId);
            orbitAnimationId = null;
        }
        document.getElementById('btn3DTurBaslat').classList.remove('hidden');
        document.getElementById('btn3DTurDurdur').classList.add('hidden');
        document.getElementById('select3DHiz').disabled = false;
        document.getElementById('select3DSure').disabled = false;
    }

    function downloadVideo() {
        if (!cesium3DViewer) return;
        var angleKey = (document.getElementById('select3DAci') && document.getElementById('select3DAci').value) || 'kuzey';
        var preset = CAMERA_PRESETS[angleKey] || CAMERA_PRESETS.kuzey;
        var durationMs = Math.max(4000, (parseInt(document.getElementById('select3DSure').value, 10) || 20) * 500);
        recordFromAngle(angleKey, durationMs).then(function(blob) {
            try { downloadBlob(blob, 'parsel-' + (preset.slug || angleKey) + '.webm'); } catch (e) { if (console && console.error) console.error('[TKGM 3D]', e); }
        }).catch(function(err) {
            try {
                var msg = (err && err.message) ? err.message : 'Video kaydı sırasında hata oluştu.';
                if (typeof alert === 'function') alert(msg); else if (console && console.error) console.error('[TKGM 3D]', msg, err);
            } catch (e2) { if (console && console.error) console.error('[TKGM 3D] catch:', e2); }
        });
    }

    var modalAgentsList = [];
    document.getElementById('btn3DDron').addEventListener('click', function() {
        if (!window.currentParselGeojson) {
            alert('Önce parsel sorgusu yapın.');
            return;
        }
        var modal = document.getElementById('modal3DDron');
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        document.getElementById('modal3DAdimlar').classList.remove('hidden');
        if (document.getElementById('modal3DGorunum')) document.getElementById('modal3DGorunum').classList.add('hidden');
        document.getElementById('panelModalAdim1').classList.remove('hidden');
        document.getElementById('panelModalAdim2').classList.add('hidden');
        document.getElementById('panelModalAdim3').classList.add('hidden');
        document.getElementById('panelModalAdim4').classList.add('hidden');
        document.getElementById('tabModalAdim1').classList.add('border-indigo-500', 'text-indigo-400');
        document.getElementById('tabModalAdim1').classList.remove('border-transparent', 'text-gray-400');
        document.getElementById('tabModalAdim2').classList.remove('border-indigo-500', 'text-indigo-400');
        document.getElementById('tabModalAdim2').classList.add('border-transparent', 'text-gray-400');
        document.getElementById('tabModalAdim3').classList.remove('border-indigo-500', 'text-indigo-400');
        document.getElementById('tabModalAdim3').classList.add('border-transparent', 'text-gray-400');
        document.getElementById('tabModalAdim4').classList.remove('border-indigo-500', 'text-indigo-400');
        document.getElementById('tabModalAdim4').classList.add('border-transparent', 'text-gray-400');
        document.getElementById('yakınLokasyonListe').innerHTML = '<div class="flex gap-2"><input type="text" class="yakın-lokasyon-input flex-1 px-4 py-2 rounded-lg bg-gray-800 border border-gray-600 text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Örn: imara 1 km uzakta"><button type="button" class="btnYakınLokasyonSil px-3 py-2 rounded-lg bg-red-600/80 hover:bg-red-600 text-white transition-colors" title="Sil"><span class="material-symbols-outlined text-lg">close</span></button></div>';
        document.getElementById('modalSeslendirmeMetni').value = '';
        document.getElementById('seslendirmeMetniKarakter').textContent = '0';
        window.selectedAgentForVideo = null;
        document.getElementById('emlakciDropdownLabel').textContent = '— Emlakçı seçin —';
        var liste = document.getElementById('emlakciDropdownListe');
        liste.innerHTML = '<button type="button" class="emlakci-opt w-full px-4 py-2 text-left text-gray-100 hover:bg-gray-700 text-sm" data-id="">— Emlakçı seçin —</button>';
        liste.classList.add('hidden');
        document.getElementById('modalEmlakciKartOnizleme').classList.add('hidden');
        document.getElementById('modalEmlakciYokUyari').classList.add('hidden');
        ajax(baseUrl + sep + 'action=get_agents').then(function(res) {
            if (res && res.success && res.data && res.data.length) {
                modalAgentsList = res.data;
                var html = '<button type="button" class="emlakci-opt w-full px-4 py-2 text-left text-gray-100 hover:bg-gray-700 text-sm" data-id="">— Emlakçı seçin —</button>';
                res.data.forEach(function(a) {
                    var ad = (a.first_name || '') + ' ' + (a.last_name || '');
                    html += '<button type="button" class="emlakci-opt w-full px-4 py-2 text-left text-gray-100 hover:bg-gray-700 text-sm" data-id="' + escapeHtml(String(a.id)) + '">' + escapeHtml(ad) + '</button>';
                });
                liste.innerHTML = html;
            } else {
                document.getElementById('modalEmlakciYokUyari').classList.remove('hidden');
            }
        }).catch(function() {
            document.getElementById('modalEmlakciYokUyari').classList.remove('hidden');
        });
    });

    document.getElementById('btnEmlakciDropdown').addEventListener('click', function(e) {
        e.stopPropagation();
        var liste = document.getElementById('emlakciDropdownListe');
        liste.classList.toggle('hidden');
    });
    document.addEventListener('click', function() {
        document.getElementById('emlakciDropdownListe').classList.add('hidden');
    });
    document.getElementById('emlakciDropdownWrap').addEventListener('click', function(e) { e.stopPropagation(); });
    document.getElementById('emlakciDropdownListe').addEventListener('click', function(e) {
        var btn = e.target.closest('.emlakci-opt');
        if (!btn) return;
        var id = btn.getAttribute('data-id') || '';
        document.getElementById('emlakciDropdownLabel').textContent = btn.textContent.trim();
        document.getElementById('emlakciDropdownListe').classList.add('hidden');
        var kart = document.getElementById('modalEmlakciKartOnizleme');
        if (!id) {
            window.selectedAgentForVideo = null;
            kart.classList.add('hidden');
            return;
        }
        var a = modalAgentsList.find(function(x) { return String(x.id) === id; });
        if (!a) return;
        window.selectedAgentForVideo = { id: a.id, first_name: a.first_name, last_name: a.last_name, photo: a.photo, phone: a.phone };
        document.getElementById('modalEmlakciFoto').src = a.photo || '';
        document.getElementById('modalEmlakciFoto').alt = (a.first_name || '') + ' ' + (a.last_name || '');
        document.getElementById('modalEmlakciTelPill').textContent = a.phone || '—';
        document.getElementById('modalEmlakciIsimPill').textContent = (a.first_name || '') + ' ' + (a.last_name || '');
        kart.classList.remove('hidden');
    });

    function setModalAdim(adim) {
        document.getElementById('panelModalAdim1').classList.add('hidden');
        document.getElementById('panelModalAdim2').classList.add('hidden');
        document.getElementById('panelModalAdim3').classList.add('hidden');
        document.getElementById('panelModalAdim4').classList.add('hidden');
        document.getElementById('panelModalAdim' + adim).classList.remove('hidden');
        [1,2,3,4].forEach(function(n) {
            var tab = document.getElementById('tabModalAdim' + n);
            if (!tab) return;
            if (n === adim) {
                tab.classList.add('border-indigo-500', 'text-indigo-400');
                tab.classList.remove('border-transparent', 'text-gray-400');
            } else {
                tab.classList.remove('border-indigo-500', 'text-indigo-400');
                tab.classList.add('border-transparent', 'text-gray-400');
            }
        });
    }
    document.getElementById('tabModalAdim1').addEventListener('click', function() { setModalAdim(1); });
    document.getElementById('tabModalAdim2').addEventListener('click', function() { setModalAdim(2); });
    document.getElementById('tabModalAdim3').addEventListener('click', function() { setModalAdim(3); });
    document.getElementById('tabModalAdim4').addEventListener('click', function() { setModalAdim(4); });
    document.getElementById('btnModalAdim1Devam').addEventListener('click', function() { setModalAdim(2); });
    document.getElementById('btnModalAdim2Devam').addEventListener('click', function() { setModalAdim(3); });
    document.getElementById('btnModalAdim3Devam').addEventListener('click', function() { setModalAdim(4); });

    document.getElementById('btnYakınLokasyonEkle').addEventListener('click', function() {
        var liste = document.getElementById('yakınLokasyonListe');
        if (liste.querySelectorAll('.yakın-lokasyon-input').length >= 3) {
            alert('En fazla 3 lokasyon ekleyebilirsiniz.');
            return;
        }
        var div = document.createElement('div');
        div.className = 'flex gap-2';
        div.innerHTML = '<input type="text" class="yakın-lokasyon-input flex-1 px-4 py-2 rounded-lg bg-gray-800 border border-gray-600 text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Örn: imara 1 km uzakta"><button type="button" class="btnYakınLokasyonSil px-3 py-2 rounded-lg bg-red-600/80 hover:bg-red-600 text-white transition-colors" title="Sil"><span class="material-symbols-outlined text-lg">close</span></button>';
        liste.appendChild(div);
        div.querySelector('.btnYakınLokasyonSil').addEventListener('click', function() {
            if (liste.querySelectorAll('.yakın-lokasyon-input').length <= 1) return;
            div.remove();
        });
    });
    document.getElementById('yakınLokasyonListe').addEventListener('click', function(e) {
        var btn = e.target.closest('.btnYakınLokasyonSil');
        if (!btn) return;
        var liste = document.getElementById('yakınLokasyonListe');
        if (liste.querySelectorAll('.yakın-lokasyon-input').length <= 1) return;
        btn.closest('.flex').remove();
    });
    document.getElementById('modalSeslendirmeMetni').addEventListener('input', function() {
        document.getElementById('seslendirmeMetniKarakter').textContent = this.value.length;
    });
    document.getElementById('btnModalAISeslendirmeOlustur').addEventListener('click', function() {
        var info = window.currentParselInfo;
        if (!info) {
            alert('Parsel bilgisi bulunamadı. Önce parsel sorgusu yapın.');
            return;
        }
        var btn = this;
        var origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="material-symbols-outlined text-lg animate-spin">progress_activity</span> Oluşturuluyor...';
        var fd = new FormData();
        fd.append('il_adi', info.il_adi || '');
        fd.append('ilce_adi', info.ilce_adi || '');
        fd.append('mahalle_adi', info.mahalle_adi || '');
        fd.append('ada', info.ada || '');
        fd.append('parsel_no', info.parsel_no || '');
        fd.append('alan_m2', info.alan_m2 != null ? info.alan_m2 : '');
        fd.append('nitelik', info.nitelik || '');
        var lokInputs = document.querySelectorAll('.yakın-lokasyon-input');
        var loklar = [];
        lokInputs.forEach(function(inp) {
            var v = (inp.value || '').trim();
            if (v) loklar.push(v);
        });
        fd.append('yakın_lokasyonlar', JSON.stringify(loklar));
        fetch(baseUrl + sep + 'action=generate_parsel_description', {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function(r) { return r.json(); }).then(function(res) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            if (res.success && res.description) {
                var txt = res.description;
                if (txt.length > 800) txt = txt.substring(0, 800);
                document.getElementById('modalSeslendirmeMetni').value = txt;
                document.getElementById('seslendirmeMetniKarakter').textContent = txt.length;
            } else {
                alert(res.error || 'Açıklama oluşturulamadı.');
            }
        }).catch(function() {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            alert('İstek sırasında bir hata oluştu.');
        });
    });
    var videoOlusturAbort = false;
    document.getElementById('btnModalVideoOlustur').addEventListener('click', function() {
        if (!window.currentParselGeojson || !window.currentParselInfo) {
            alert('Video oluşturmak için önce haritada parsel görünen bir sorgu yapın. (CBS detay ve parsel geometrisi gerekli)');
            return;
        }
        var modal = document.getElementById('modal3DDron');
        modal.classList.add('hidden');
        modal.style.display = 'none';
        var overlay = document.getElementById('videoOlusturuluyorOverlay');
        overlay.classList.remove('hidden');
        overlay.style.display = 'flex';
        videoOlusturAbort = false;
        document.getElementById('videoOlusturProgressBar').style.width = '0%';
        document.getElementById('videoOlusturProgressPct').textContent = '0%';
        document.getElementById('videoOlusturDurum').textContent = 'Hazırlanıyor...';
        var btn = document.getElementById('btn3DDron');
        if (btn) { btn.disabled = true; btn.innerHTML = '<span class="material-symbols-outlined text-xl animate-spin">progress_activity</span> Yükleniyor...'; }
        loadCesium().then(function() {
            if (btn) { btn.disabled = false; btn.innerHTML = '<span class="material-symbols-outlined text-xl">videocam</span> 3D Dron Görünümü'; }
            initCesium3D('cesiumVideoContainer');
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
                    return;
                }
                durumEl.textContent = 'İçerik kütüphanesine kaydediliyor...';
                barEl.style.width = '95%';
                pctEl.textContent = '95%';
                var fd = new FormData();
                fd.append('video', blob, 'parsel-drone-' + Math.round(DRONE_VIDEO_TOTAL_MS / 1000) + 'sn.webm');
                fd.append('action', 'upload_drone_video');
                fd.append('ada', (window.currentParselInfo && window.currentParselInfo.ada) ? window.currentParselInfo.ada : '');
                fd.append('parsel_no', (window.currentParselInfo && window.currentParselInfo.parsel_no) ? window.currentParselInfo.parsel_no : '');
                fetch(baseUrl + sep + 'action=upload_drone_video', {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then(function(r) { return r.json(); }).then(function(res) {
                    overlay.classList.add('hidden');
                    overlay.style.display = 'none';
                    if (cesium3DViewer) { cesium3DViewer.destroy(); cesium3DViewer = null; }
                    if (res.success) {
                        alert('Video içerik kütüphanesine kaydedildi.');
                    } else {
                        alert(res.message || 'Video kaydedilemedi.');
                    }
                }).catch(function() {
                    overlay.classList.add('hidden');
                    overlay.style.display = 'none';
                    if (cesium3DViewer) { cesium3DViewer.destroy(); cesium3DViewer = null; }
                    alert('Video yüklenirken hata oluştu.');
                });
            }).catch(function(err) {
                try {
                    if (!videoOlusturAbort) {
                        var msg = (err && err.message) ? err.message : 'Video oluşturulurken hata oluştu.';
                        if (typeof alert === 'function') alert(msg); else if (console && console.error) console.error('[TKGM 3D]', msg, err);
                    }
                    overlay.classList.add('hidden');
                    overlay.style.display = 'none';
                    if (cesium3DViewer) { cesium3DViewer.destroy(); cesium3DViewer = null; }
                } catch (e2) { if (console && console.error) console.error('[TKGM 3D] catch:', e2); }
            });
        }).catch(function() {
            if (btn) { btn.disabled = false; btn.innerHTML = '<span class="material-symbols-outlined text-xl">videocam</span> 3D Dron Görünümü'; }
            document.getElementById('videoOlusturuluyorOverlay').classList.add('hidden');
            document.getElementById('videoOlusturuluyorOverlay').style.display = 'none';
            alert('3D görünüm yüklenemedi.');
        });
    });

    document.getElementById('videoOlusturIptal').addEventListener('click', function() {
        videoOlusturAbort = true;
        var overlay = document.getElementById('videoOlusturuluyorOverlay');
        overlay.classList.add('hidden');
        overlay.style.display = 'none';
        if (cesium3DViewer) { cesium3DViewer.destroy(); cesium3DViewer = null; }
    });

    document.getElementById('modal3DClose').addEventListener('click', function() {
        var modal = document.getElementById('modal3DDron');
        modal.classList.add('hidden');
        modal.style.display = 'none';
        if (cesium3DViewer) {
            cesium3DViewer.destroy();
            cesium3DViewer = null;
        }
    });

    function escapeHtml(s) {
        var div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
    }
})();
</script>
