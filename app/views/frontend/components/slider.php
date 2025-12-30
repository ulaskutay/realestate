<?php
/**
 * Slider Component - Tema Yapısına Uygun
 * Kullanım: $renderer->component('slider', ['slider' => $slider_data]);
 */

if (!isset($slider) || empty($slider['items'])) {
    return;
}
?>

<div id="entry-slider" 
     class="cms-slider"
     data-autoplay="<?php echo $slider['autoplay'] ? 'true' : 'false'; ?>"
     data-autoplay-delay="<?php echo esc_attr($slider['autoplay_delay'] ?? 5000); ?>"
     data-navigation="<?php echo ($slider['navigation'] ?? true) ? 'true' : 'false'; ?>"
     data-pagination="<?php echo ($slider['pagination'] ?? true) ? 'true' : 'false'; ?>"
     data-loop="<?php echo ($slider['loop'] ?? true) ? 'true' : 'false'; ?>"
     style="max-width: <?php echo esc_attr($slider['width'] ?? '1450px'); ?>; margin: 0 auto;">
    
    <?php foreach ($slider['items'] as $index => $item): ?>
        <?php
        // Mobil ve desktop resimleri
        $desktopImage = $item['media_url'] ?? '';
        $mobileImage = $item['mobile_image_url'] ?? $desktopImage;
        
        // Link bilgileri
        $linkUrl = $item['button_link'] ?? '';
        $linkTarget = $item['button_target'] ?? '_self';
        
        // Text overlay kontrolü
        $hasTextOverlay = false;
        $textTitle = '';
        $textSubtitle = '';
        $textButton = '';
        
        // Layer sisteminden text bilgilerini çek
        if (!empty($item['layers'])) {
            foreach ($item['layers'] as $layer) {
                if ($layer['type'] === 'text') {
                    if (empty($textTitle)) {
                        $textTitle = $layer['content'] ?? '';
                    } elseif (empty($textSubtitle)) {
                        $textSubtitle = $layer['content'] ?? '';
                    }
                    $hasTextOverlay = true;
                } elseif ($layer['type'] === 'button') {
                    $textButton = $layer['content'] ?? '';
                    if (!empty($layer['link_url'])) {
                        $linkUrl = $layer['link_url'];
                        $linkTarget = $layer['link_target'] ?? '_self';
                    }
                    $hasTextOverlay = true;
                }
            }
        }
        
        // Eski yapıdan text bilgilerini çek (backward compatibility)
        if (!$hasTextOverlay) {
            $textTitle = $item['title'] ?? '';
            $textSubtitle = $item['subtitle'] ?? $item['description'] ?? '';
            $textButton = $item['button_text'] ?? '';
            $hasTextOverlay = !empty($textTitle) || !empty($textSubtitle) || !empty($textButton);
        }
        ?>
        
        <div class="slider-item slider-item-<?php echo $index + 1; ?> cms-slide<?php echo $index === 0 ? ' active' : ''; ?>">
            <?php if (!empty($linkUrl)): ?>
                <a href="<?php echo ViewRenderer::escUrl($linkUrl); ?>" <?php echo $linkTarget === '_blank' ? 'target="_blank" rel="noopener"' : ''; ?>>
            <?php endif; ?>
            
            <div class="slider-item-img">
                <?php if (($item['type'] ?? 'image') === 'video'): ?>
                    <video autoplay muted loop playsinline class="slider-item-video">
                        <source src="<?php echo ViewRenderer::escUrl($desktopImage); ?>" type="video/mp4">
                    </video>
                <?php else: ?>
                    <!-- Desktop Image -->
                    <img src="<?php echo ViewRenderer::escUrl($desktopImage); ?>" 
                         alt="<?php echo ViewRenderer::escAttr($textTitle); ?>"
                         class="slider-desktop-image"
                         style="display: block;"/>
                    
                    <!-- Mobile Image (CSS ile gizlenir/gösterilir) -->
                    <?php if ($mobileImage !== $desktopImage): ?>
                        <img src="<?php echo ViewRenderer::escUrl($mobileImage); ?>" 
                             alt="<?php echo ViewRenderer::escAttr($textTitle); ?>"
                             class="slider-mobile-image"
                             style="display: none;"/>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <?php if ($hasTextOverlay): ?>
                <div class="slider-item-text">
                    <?php if (!empty($textTitle)): ?>
                        <h3><?php echo ViewRenderer::escHtml($textTitle); ?></h3>
                    <?php endif; ?>
                    
                    <?php if (!empty($textSubtitle)): ?>
                        <p><?php echo ViewRenderer::escHtml($textSubtitle); ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($textButton)): ?>
                        <span class="btn btn-primary"><?php echo ViewRenderer::escHtml($textButton); ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($linkUrl)): ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<!-- Slider CSS -->
<link rel="stylesheet" href="<?php echo ViewRenderer::assetUrl('frontend/css/slider.css'); ?>">
<!-- Custom Slider JS -->
<script src="<?php echo ViewRenderer::assetUrl('frontend/js/slider.js'); ?>"></script>
