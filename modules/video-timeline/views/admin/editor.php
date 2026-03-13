<?php
$timeline = $timeline ?? [];
$tracks = $tracks ?? [];
$clipsByTrack = $clipsByTrack ?? [];
$moduleAssetBase = $moduleAssetBase ?? '';
$adminUrl = $adminUrl ?? '';
$timelineId = (int)($timeline['id'] ?? 0);
$width = (int)($timeline['width'] ?? 1920);
$height = (int)($timeline['height'] ?? 1080);
$durationSec = (float)($timeline['duration_sec'] ?? 10);
$fps = (int)($timeline['fps'] ?? 25);
$name = $timeline['name'] ?? 'Proje';
$sizePreset = 'custom';
if ($width === 1920 && $height === 1080) $sizePreset = '1920x1080';
elseif ($width === 1080 && $height === 1920) $sizePreset = '1080x1920';
elseif ($width === 1280 && $height === 720) $sizePreset = '1280x720';
elseif ($width === 720 && $height === 1280) $sizePreset = '720x1280';
elseif ($width === 3840 && $height === 2160) $sizePreset = '3840x2160';
$saveUrl = $adminUrl . (strpos($adminUrl, '?') !== false ? '&' : '?') . 'page=module/video-timeline/save-timeline';
$listUrl = $adminUrl . (strpos($adminUrl, '?') !== false ? '&' : '?') . 'page=module/video-timeline/index';
$mediaListUrl = $adminUrl . (strpos($adminUrl, '?') !== false ? '&' : '?') . 'page=media/list';
?>
<link rel="stylesheet" href="<?php echo $moduleAssetBase; ?>editor.css">
<div id="vt-app" class="vt-editor" data-timeline-id="<?php echo $timelineId; ?>" data-save-url="<?php echo esc_attr($saveUrl); ?>" data-list-url="<?php echo esc_attr($listUrl); ?>" data-media-list-url="<?php echo esc_attr($mediaListUrl); ?>">
    <header class="vt-header" role="banner">
        <div class="vt-header-row vt-header-row-main">
            <div class="vt-header-group vt-header-group-project">
                <a href="<?php echo esc_attr($listUrl); ?>" class="vt-btn vt-btn-ghost vt-btn-icon" title="Listeye dön" aria-label="Listeye dön">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div class="vt-header-divider"></div>
                <label class="vt-header-label">Proje</label>
                <input type="text" id="vt-name" class="vt-input vt-project-name" value="<?php echo esc_attr($name); ?>" placeholder="Proje adı" aria-label="Proje adı">
            </div>
            <div class="vt-header-group vt-header-group-size">
                <label class="vt-header-label" for="vt-size-preset">Boyut</label>
                <select id="vt-size-preset" class="vt-input vt-size-preset" aria-label="Hazır boyut seçin">
                    <option value="1920x1080" <?php echo ($width === 1920 && $height === 1080) ? 'selected' : ''; ?>>1920 × 1080 (Full HD)</option>
                    <option value="1080x1920" <?php echo ($width === 1080 && $height === 1920) ? 'selected' : ''; ?>>1080 × 1920 (Dikey)</option>
                    <option value="1280x720" <?php echo ($width === 1280 && $height === 720) ? 'selected' : ''; ?>>1280 × 720 (HD)</option>
                    <option value="720x1280" <?php echo ($width === 720 && $height === 1280) ? 'selected' : ''; ?>>720 × 1280 (HD Dikey)</option>
                    <option value="3840x2160" <?php echo ($width === 3840 && $height === 2160) ? 'selected' : ''; ?>>3840 × 2160 (4K)</option>
                    <option value="custom">Özel</option>
                </select>
                <div class="vt-size-custom" id="vt-size-custom">
                    <input type="number" id="vt-width" class="vt-input vt-num" value="<?php echo $width; ?>" min="1" max="4096" aria-label="Genişlik" title="Genişlik">
                    <span class="vt-size-sep">×</span>
                    <input type="number" id="vt-height" class="vt-input vt-num" value="<?php echo $height; ?>" min="1" max="4096" aria-label="Yükseklik" title="Yükseklik">
                </div>
            </div>
            <div class="vt-header-group vt-header-group-duration">
                <label class="vt-header-label" for="vt-duration">Süre</label>
                <div class="vt-duration-wrap">
                    <input type="number" id="vt-duration" class="vt-input vt-num vt-duration-input" value="<?php echo $durationSec; ?>" min="0.1" step="0.1" aria-label="Süre (saniye)">
                    <span class="vt-unit">sn</span>
                </div>
            </div>
            <div class="vt-header-group vt-header-group-bg">
                <label class="vt-header-label" for="vt-bg-color">Arka plan</label>
                <input type="color" id="vt-bg-color" class="vt-color-input" value="<?php echo esc_attr($timeline['background_color'] ?? '#000000'); ?>" title="Arka plan rengi" aria-label="Arka plan rengi">
            </div>
        </div>
        <div class="vt-header-row vt-header-row-add">
            <div id="vt-add-clip-panel" class="vt-add-clip-panel vt-add-clip-panel-header">
                <span class="vt-header-label">Ekle</span>
                <div class="vt-add-clip-grid vt-add-clip-grid-inline">
                    <button type="button" class="vt-add-clip-card vt-add-clip-video" data-type="video">
                        <span class="material-symbols-outlined">movie</span>
                        <span class="vt-add-clip-label">Video</span>
                    </button>
                    <button type="button" class="vt-add-clip-card vt-add-clip-image" data-type="image">
                        <span class="material-symbols-outlined">image</span>
                        <span class="vt-add-clip-label">Görsel</span>
                    </button>
                    <button type="button" class="vt-add-clip-card vt-add-clip-text" data-type="text">
                        <span class="material-symbols-outlined">text_fields</span>
                        <span class="vt-add-clip-label">Metin</span>
                    </button>
                    <button type="button" class="vt-add-clip-card vt-add-clip-shape" data-type="shape">
                        <span class="material-symbols-outlined">shapes</span>
                        <span class="vt-add-clip-label">Şekil</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="vt-header-row vt-header-row-tools">
            <div class="vt-header-group vt-header-group-playback">
                <button type="button" id="vt-stop" class="vt-btn vt-btn-ghost vt-btn-icon" title="Başa al" aria-label="Başa al"><span class="material-symbols-outlined">stop</span></button>
                <button type="button" id="vt-play" class="vt-btn vt-btn-ghost vt-btn-icon" title="Oynat" aria-label="Oynat"><span class="material-symbols-outlined">play_arrow</span></button>
                <button type="button" id="vt-pause" class="vt-btn vt-btn-ghost vt-btn-icon" style="display:none;" title="Duraklat" aria-label="Duraklat"><span class="material-symbols-outlined">pause</span></button>
                <span id="vt-header-time" class="vt-header-time" aria-live="polite">00:00.0</span>
            </div>
            <div class="vt-header-divider"></div>
            <div class="vt-header-group vt-header-group-zoom">
                <button type="button" id="vt-zoom-out" class="vt-btn vt-btn-ghost vt-btn-icon" title="Uzaklaştır" aria-label="Uzaklaştır">−</button>
                <span id="vt-zoom-label" class="vt-zoom-label">100%</span>
                <button type="button" id="vt-zoom-in" class="vt-btn vt-btn-ghost vt-btn-icon" title="Yakınlaştır" aria-label="Yakınlaştır">+</button>
            </div>
            <div class="vt-header-divider"></div>
            <div class="vt-header-group vt-header-group-actions">
                <button type="button" id="vt-snap-toggle" class="vt-btn vt-btn-ghost vt-btn-icon" title="Snap açık/kapat" aria-label="Snap"><span class="material-symbols-outlined">grid_on</span></button>
                <button type="button" id="vt-undo" class="vt-btn vt-btn-ghost vt-btn-icon" title="Geri al" aria-label="Geri al"><span class="material-symbols-outlined">undo</span></button>
                <button type="button" id="vt-redo" class="vt-btn vt-btn-ghost vt-btn-icon" title="İleri al" aria-label="İleri al"><span class="material-symbols-outlined">redo</span></button>
                <button type="button" id="vt-copy-clips" class="vt-btn vt-btn-ghost vt-btn-icon" title="Seçili öğeyi kopyala (Ctrl+C)" aria-label="Kopyala"><span class="material-symbols-outlined">content_copy</span></button>
                <button type="button" id="vt-paste-clips" class="vt-btn vt-btn-ghost vt-btn-icon" title="Yapıştır (Ctrl+V)" aria-label="Yapıştır"><span class="material-symbols-outlined">content_paste</span></button>
                <button type="button" id="vt-shortcuts-btn" class="vt-btn vt-btn-ghost vt-btn-icon" title="Kısayollar (?)" aria-label="Kısayollar"><span class="material-symbols-outlined">keyboard</span></button>
                <button type="button" id="vt-download-project" class="vt-btn vt-btn-ghost" title="Projeyi JSON olarak indir">
                    <span class="material-symbols-outlined">download</span> Projeyi indir
                </button>
                <button type="button" id="vt-save" class="vt-btn vt-btn-primary">
                    <span class="material-symbols-outlined">save</span> Kaydet
                </button>
            </div>
        </div>
    </header>

    <div class="vt-workspace">
        <div class="vt-canvas-area">
            <section class="vt-preview-section" aria-label="Video önizleme">
                <div class="vt-preview-zoom-controls">
                    <label class="vt-header-label">Önizleme</label>
                    <button type="button" id="vt-preview-zoom-out" class="vt-btn vt-btn-ghost vt-btn-icon" title="Uzaklaştır" aria-label="Uzaklaştır">−</button>
                    <span id="vt-preview-zoom-label" class="vt-zoom-label">100%</span>
                    <button type="button" id="vt-preview-zoom-in" class="vt-btn vt-btn-ghost vt-btn-icon" title="Yakınlaştır" aria-label="Yakınlaştır">+</button>
                </div>
                <div id="vt-preview-zoom-wrap" class="vt-preview-zoom-wrap">
                    <div class="vt-preview-container">
                        <div id="vt-preview" class="vt-preview" data-width="<?php echo $width; ?>" data-height="<?php echo $height; ?>">
                            <span class="vt-preview-empty">Video veya görsel ekleyin</span>
                        </div>
                        <div class="vt-preview-overlay">
                            <span id="vt-preview-badge" class="vt-preview-badge">Canvas <?php echo $width; ?> × <?php echo $height; ?></span>
                        </div>
                    </div>
                </div>
            </section>
            <section class="vt-timeline-section" aria-label="Zaman çizelgesi">
                <div class="vt-timeline-header">
                    <div class="vt-timeline-label">Zaman çizelgesi</div>
                    <div id="vt-ruler" class="vt-ruler">
                        <div id="vt-playhead-line" class="vt-playhead-line" aria-hidden="true"></div>
                    </div>
                </div>
                <div class="vt-timeline-tabs" id="vt-timeline-tabs" role="tablist">
                    <button type="button" class="vt-tab active" data-filter="all" role="tab" aria-selected="true">Tümü</button>
                    <button type="button" class="vt-tab" data-filter="video" role="tab">Video</button>
                    <button type="button" class="vt-tab" data-filter="image" role="tab">Görsel</button>
                    <button type="button" class="vt-tab" data-filter="text" role="tab">Metin</button>
                    <button type="button" class="vt-tab" data-filter="shape" role="tab">Şekil</button>
                </div>
                <div class="vt-timeline-body" id="vt-timeline-body">
                    <div id="vt-playhead-track" class="vt-playhead-track"></div>
                    <div id="vt-tracks" class="vt-tracks"></div>
                </div>
            </section>
        </div>
        <aside class="vt-sidebar">
            <div id="vt-props" class="vt-props">
                <p class="vt-props-placeholder" id="vt-props-placeholder" style="display:none;">Bir clip seçin.</p>
                <div id="vt-props-form" class="vt-props-form" style="display:none;"></div>
            </div>
        </aside>
    </div>
</div>

<div id="vt-toast-container" class="vt-toast-container" aria-live="polite"></div>

<!-- Kısayollar modal -->
<div id="vt-shortcuts-modal" class="vt-modal" style="display:none;" aria-hidden="true">
    <div class="vt-modal-backdrop"></div>
    <div class="vt-modal-content vt-modal-sm">
        <div class="vt-modal-header">
            <h3>Klavye kısayolları</h3>
            <button type="button" class="vt-modal-close" aria-label="Kapat">&times;</button>
        </div>
        <div class="vt-modal-body">
            <table class="vt-shortcuts-table">
                <tr><td><kbd>Space</kbd></td><td>Oynat / Duraklat</td></tr>
                <tr><td><kbd>Delete</kbd> / <kbd>Backspace</kbd></td><td>Seçili clip sil</td></tr>
                <tr><td><kbd>Ctrl</kbd>+<kbd>Z</kbd></td><td>Geri al</td></tr>
                <tr><td><kbd>Ctrl</kbd>+<kbd>Shift</kbd>+<kbd>Z</kbd></td><td>İleri al</td></tr>
                <tr><td><kbd>Ctrl</kbd>+<kbd>D</kbd></td><td>Clip çoğalt</td></tr>
                <tr><td><kbd>Ctrl</kbd>+<kbd>C</kbd></td><td>Kopyala</td></tr>
                <tr><td><kbd>Ctrl</kbd>+<kbd>V</kbd></td><td>Yapıştır</td></tr>
                <tr><td><kbd>?</kbd></td><td>Bu yardım</td></tr>
            </table>
        </div>
    </div>
</div>

<!-- Medya seçim modal -->
<div id="vt-media-modal" class="vt-modal" style="display:none;" aria-hidden="true">
    <div class="vt-modal-backdrop"></div>
    <div class="vt-modal-content">
        <div class="vt-modal-header">
            <h3 id="vt-media-modal-title">Video seç</h3>
            <button type="button" class="vt-modal-close" aria-label="Kapat">&times;</button>
        </div>
        <div class="vt-modal-body">
            <div id="vt-media-list" class="vt-media-grid"></div>
            <p id="vt-media-empty" class="vt-media-empty" style="display:none;">Bu türde medya yok. Önce <a href="<?php echo esc_attr(admin_url('media/upload')); ?>" target="_blank">medya yükle</a> sayfasından yükleyin.</p>
        </div>
    </div>
</div>

<script>
window.VT_DATA = {
    timeline: <?php echo json_encode($timeline); ?>,
    tracks: <?php echo json_encode($tracks); ?>,
    clipsByTrack: <?php echo json_encode($clipsByTrack); ?>
};
</script>
<script src="<?php echo $moduleAssetBase; ?>editor.js"></script>
