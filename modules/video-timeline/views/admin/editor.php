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
$saveUrl = $adminUrl . (strpos($adminUrl, '?') !== false ? '&' : '?') . 'page=module/video-timeline/save-timeline';
$listUrl = $adminUrl . (strpos($adminUrl, '?') !== false ? '&' : '?') . 'page=module/video-timeline/index';
$mediaListUrl = $adminUrl . (strpos($adminUrl, '?') !== false ? '&' : '?') . 'page=media/list';
?>
<link rel="stylesheet" href="<?php echo $moduleAssetBase; ?>editor.css">
<div id="vt-app" class="vt-editor" data-timeline-id="<?php echo $timelineId; ?>" data-save-url="<?php echo esc_attr($saveUrl); ?>" data-list-url="<?php echo esc_attr($listUrl); ?>" data-media-list-url="<?php echo esc_attr($mediaListUrl); ?>">
    <header class="vt-header">
        <div class="vt-header-left">
            <a href="<?php echo esc_attr($listUrl); ?>" class="vt-btn vt-btn-ghost vt-btn-icon" title="Listeye dön">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div class="vt-header-divider"></div>
            <input type="text" id="vt-name" class="vt-input vt-project-name" value="<?php echo esc_attr($name); ?>" placeholder="Proje adı">
            <div class="vt-header-meta">
                <span class="vt-meta-item"><span class="vt-meta-label">Çözünürlük</span> <input type="number" id="vt-width" class="vt-input vt-num-s" value="<?php echo $width; ?>" min="1" max="4096"> × <input type="number" id="vt-height" class="vt-input vt-num-s" value="<?php echo $height; ?>" min="1" max="4096"></span>
                <span class="vt-meta-item"><span class="vt-meta-label">Süre</span> <input type="number" id="vt-duration" class="vt-input vt-num-s" value="<?php echo $durationSec; ?>" min="0.1" step="0.1"> sn</span>
            </div>
        </div>
        <div class="vt-header-right">
            <button type="button" id="vt-save" class="vt-btn vt-btn-primary">
                <span class="material-symbols-outlined">save</span> Kaydet
            </button>
        </div>
    </header>

    <div class="vt-workspace">
        <div class="vt-canvas-area">
            <section class="vt-preview-section" aria-label="Video önizleme">
                <div class="vt-preview-container">
                    <div id="vt-preview" class="vt-preview" data-width="<?php echo $width; ?>" data-height="<?php echo $height; ?>">
                        <span class="vt-preview-empty">Video veya görsel ekleyin</span>
                    </div>
                    <div class="vt-preview-overlay">
                        <span class="vt-preview-badge"><?php echo $width; ?> × <?php echo $height; ?></span>
                    </div>
                </div>
            </section>
            <section class="vt-timeline-section" aria-label="Zaman çizelgesi">
                <div class="vt-timeline-header">
                    <div class="vt-timeline-label">Zaman çizelgesi</div>
                    <div id="vt-ruler" class="vt-ruler"></div>
                </div>
                <div class="vt-timeline-body">
                    <div id="vt-tracks" class="vt-tracks"></div>
                </div>
                <div class="vt-timeline-footer">
                    <button type="button" id="vt-add-track" class="vt-btn vt-btn-soft">
                        <span class="material-symbols-outlined">add</span> Track ekle
                    </button>
                </div>
            </section>
        </div>
        <aside class="vt-sidebar">
            <div id="vt-props" class="vt-props">
                <p class="vt-props-placeholder" id="vt-props-placeholder" style="display:none;">Bir clip seçin.</p>
                <div id="vt-add-clip-panel" class="vt-add-clip-panel">
                    <h3 class="vt-sidebar-title">Ekle</h3>
                    <p class="vt-sidebar-desc">Tür seçin, timeline'da veya track başlığındaki + Clip ile de ekleyebilirsiniz.</p>
                    <div class="vt-add-clip-grid">
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
                <div id="vt-props-form" class="vt-props-form" style="display:none;"></div>
            </div>
        </aside>
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
