<?php
/**
 * Çizgi Aks - Varsayılan Sayfa Şablonu
 * Tema renkleri ve ayarları yansıtılır
 */

$themeLoader = $themeLoader ?? null;
$primaryColor = $themeLoader ? $themeLoader->getColor('primary', '#bc1a1a') : '#bc1a1a';
?>
<div class="cizgiaks-page py-12">
    <div class="cizgiaks-container">
        <!-- Sayfa Başlığı -->
        <div class="text-center mb-12">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4"><?php echo esc_html($page['title']); ?></h1>
            <?php if (!empty($page['excerpt'])): ?>
            <p class="text-gray-600 max-w-2xl mx-auto"><?php echo esc_html($page['excerpt']); ?></p>
            <?php endif; ?>
        </div>
        
        <!-- Sayfa İçeriği -->
        <div class="bg-white rounded-xl p-8 shadow-sm border border-gray-100">
            <div class="prose prose-lg max-w-none dark:prose-invert">
                <?php echo $page['content']; ?>
            </div>
        </div>
    </div>
</div>

<style>
.cizgiaks-page {
    background: linear-gradient(to bottom, #f9fafb, #ffffff);
    min-height: 60vh;
}
.cizgiaks-page .cizgiaks-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 1rem;
}
.cizgiaks-page .prose {
    color: #374151;
}
.cizgiaks-page .prose h2 {
    color: #111827;
    margin-top: 2rem;
    margin-bottom: 1rem;
}
.cizgiaks-page .prose h3 {
    color: #1f2937;
}
.cizgiaks-page .prose a {
    color: var(--color-primary, <?php echo $primaryColor; ?>);
}
.cizgiaks-page .prose img {
    border-radius: 0.75rem;
}
</style>
