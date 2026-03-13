<?php
/**
 * Çizgi Aks - Hakkımızda Sayfa Şablonu
 * Tema renkleri ve ayarları yansıtılır
 */

$themeLoader = $themeLoader ?? null;
$primaryColor = $themeLoader ? $themeLoader->getColor('primary', '#bc1a1a') : '#bc1a1a';

// Özel alanları al
$heroSubtitle = $customFields['hero_subtitle'] ?? '';
$heroImage = $customFields['hero_image'] ?? '';
$aboutSections = !empty($customFields['about_sections']) ? (json_decode($customFields['about_sections'], true) ?: []) : [];
$teamMembers = !empty($customFields['team_members']) ? (json_decode($customFields['team_members'], true) ?: []) : [];
$stats = !empty($customFields['stats']) ? (json_decode($customFields['stats'], true) ?: []) : [];

// CTA
$ctaTitle = $customFields['cta_title'] ?? 'Bizimle İletişime Geçin';
$ctaDescription = $customFields['cta_description'] ?? '';
$ctaButtonText = $customFields['cta_button_text'] ?? 'İletişime Geç';
$ctaButtonLink = $customFields['cta_button_link'] ?? '/contact';

// Konum linki — embed ile aynı yere giden doğrudan Google Maps linki (yoksa embed URL'den türetilir)

// Ofis görseli — sadece yönetimden yüklenmiş bir görsel varsa göster (kırık görsel önlenir)
$aboutOfficeImage = $customFields['about_office_image'] ?? get_option('about_office_image', '');
$hasOfficeImage = trim((string)$aboutOfficeImage) !== '';

// Yazının solundaki görsel (Hakkımızda metni yanında)
$aboutIntroImage = $customFields['about_intro_image'] ?? get_option('about_intro_image', 'https://cizgiaksgayrimenkul.com/public/uploads/media/2026/03/2025-10-31-69aae663aeec8.webp');
$aboutIntroImage = trim((string)$aboutIntroImage) !== '' ? $aboutIntroImage : 'https://cizgiaksgayrimenkul.com/public/uploads/media/2026/03/2025-10-31-69aae663aeec8.webp';

// Google Maps embed URL (Konumumuz bölümü)
$aboutMapEmbedUrl = $customFields['about_map_embed_url'] ?? get_option('about_map_embed_url', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3262.8034014175!2d34.0093394!3d38.363311599999996!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14d671c29b617ce1%3A0x892648511e30994a!2zw4fEsFpHxLAgQUtTIEdBWVLEsE1FTktVTA!5e1!3m2!1str!2str!4v1772807623786!5m2!1str!2str');
// Haritada Aç butonu: yönetimden URL verilmişse onu kullan, yoksa embed URL'den doğrudan harita linki türet
$aboutMapUrl = $customFields['about_map_url'] ?? get_option('about_map_url', '');
if (trim((string)$aboutMapUrl) === '') {
    $aboutMapUrl = str_replace('/embed', '', $aboutMapEmbedUrl);
}
$aboutMapUrl = trim((string)$aboutMapUrl) !== '' ? $aboutMapUrl : str_replace('/embed', '', $aboutMapEmbedUrl);

// Site bilgilerinden Hakkımızda metni (içerik boşsa otomatik oluştur)
$aboutDisplayContent = trim((string)($page['content'] ?? ''));
if ($aboutDisplayContent === '') {
    $siteName = function_exists('get_option') ? (get_option('site_name', '') ?: get_option('seo_title', '')) : '';
    $companyName = function_exists('get_option') ? get_option('company_name', '') : '';
    $companyAddress = function_exists('get_option') ? get_option('company_address', '') : '';
    $companyCity = function_exists('get_option') ? get_option('company_city', '') : '';
    $companyPhone = function_exists('get_option') ? get_option('company_phone', '') : '';
    $companyEmail = function_exists('get_option') ? get_option('company_email', '') : '';
    $brandName = $companyName ?: $siteName ?: 'Çizgi Aks Gayrimenkul';
    $cityLabel = $companyCity ?: '';

    $aboutDisplayContent = '<p><strong>' . esc_html($brandName) . '</strong>, ';
    if ($cityLabel) {
        $aboutDisplayContent .= esc_html($cityLabel) . ' merkezli ';
    }
    $aboutDisplayContent .= 'gayrimenkul danışmanlığı ve emlak hizmetleri sunan güvenilir bir kuruluştur. Müşterilerimize konut ve ticari gayrimenkul alanında profesyonel destek sağlıyoruz.</p>';
    $aboutDisplayContent .= '<p>Uzman kadromuz ve yerel piyasa bilgimizle satılık ve kiralık ilanlarında, değerleme ve danışmanlık hizmetlerinde yanınızdayız. Hayalinizdeki mülkü bulmanız için çalışıyoruz.</p>';
    $aboutDisplayContent .= '<p>Müşteri memnuniyetini ön planda tutarak, şeffaf ve güvenilir hizmet anlayışımızla sektörde fark yaratıyoruz.</p>';

    $contactParts = [];
    if ($companyAddress) {
        $contactParts[] = '<span class="cizgiaks-about-contact-item"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> ' . esc_html($companyAddress) . '</span>';
    }
    if ($companyPhone) {
        $contactParts[] = '<span class="cizgiaks-about-contact-item"><a href="tel:' . esc_attr(preg_replace('/[^0-9+]/', '', $companyPhone)) . '"><i class="fas fa-phone" aria-hidden="true"></i> ' . esc_html($companyPhone) . '</a></span>';
    }
    if ($companyEmail) {
        $contactParts[] = '<span class="cizgiaks-about-contact-item"><a href="mailto:' . esc_attr($companyEmail) . '"><i class="fas fa-envelope" aria-hidden="true"></i> ' . esc_html($companyEmail) . '</a></span>';
    }
    if (!empty($contactParts)) {
        $aboutDisplayContent .= '<p class="cizgiaks-about-contact-block">' . implode(' &middot; ', $contactParts) . '</p>';
    }
}
?>

<div class="cizgiaks-about-page">
    <!-- Hero Section -->
    <section class="cizgiaks-about-hero">
        <?php if ($heroImage): ?>
        <div class="cizgiaks-about-hero-bg-img">
            <img src="<?php echo esc_url($heroImage); ?>" alt="" class="cizgiaks-about-hero-img">
        </div>
        <?php endif; ?>
        <div class="cizgiaks-about-hero-overlay"></div>
        <div class="cizgiaks-container cizgiaks-about-hero-inner">
            <nav class="cizgiaks-about-breadcrumb" aria-label="Breadcrumb">
                <a href="<?php echo esc_url(function_exists('site_url') ? site_url('/') : '/'); ?>"><?php echo esc_html(__('Ana Sayfa')); ?></a>
                <span class="cizgiaks-about-breadcrumb-sep">/</span>
                <span><?php echo esc_html($page['title'] ?? __('Hakkımızda')); ?></span>
            </nav>
            <?php if ($heroSubtitle): ?>
            <p class="cizgiaks-about-hero-subtitle"><?php echo esc_html($heroSubtitle); ?></p>
            <?php endif; ?>
            <h1 class="cizgiaks-about-hero-title"><?php echo esc_html($page['title'] ?? __('Hakkımızda')); ?></h1>
            <?php if (!empty($page['excerpt'])): ?>
            <p class="cizgiaks-about-hero-excerpt"><?php echo esc_html($page['excerpt']); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <!-- İçerik Bölümü (görsel solda, yazı sağda) -->
    <?php if (!empty($aboutDisplayContent)): ?>
    <section class="cizgiaks-about-content-section">
        <div class="cizgiaks-container">
            <div class="cizgiaks-about-content-row">
                <div class="cizgiaks-about-content-media">
                    <img src="<?php echo esc_url($aboutIntroImage); ?>" alt="<?php echo esc_attr(__('Çizgi Aks Gayrimenkul')); ?>" class="cizgiaks-about-content-img" loading="lazy">
                </div>
                <div class="cizgiaks-about-prose">
                    <?php echo $aboutDisplayContent; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Hikaye / Bölümler -->
    <?php if (!empty($aboutSections)): ?>
    <section class="cizgiaks-about-sections">
        <div class="cizgiaks-container">
            <div class="cizgiaks-about-sections-inner">
                <?php foreach ($aboutSections as $index => $section): ?>
                <article class="cizgiaks-about-section-item <?php echo $index % 2 === 0 ? 'cizgiaks-about-section-item--reverse' : ''; ?>">
                    <?php if (!empty($section['image'])): ?>
                    <div class="cizgiaks-about-section-media">
                        <div class="cizgiaks-about-section-img-wrap">
                            <img src="<?php echo esc_url($section['image']); ?>" alt="<?php echo esc_attr($section['title'] ?? ''); ?>" loading="lazy">
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="cizgiaks-about-section-content <?php echo empty($section['image']) ? 'cizgiaks-about-section-content--full' : ''; ?>">
                        <?php if (!empty($section['title'])): ?>
                        <h2 class="cizgiaks-about-section-title"><?php echo esc_html($section['title']); ?></h2>
                        <?php endif; ?>
                        <?php if (!empty($section['content'])): ?>
                        <div class="cizgiaks-about-section-text">
                            <?php echo nl2br(esc_html($section['content'])); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- İstatistikler -->
    <?php if (!empty($stats)): ?>
    <section class="cizgiaks-about-stats">
        <div class="cizgiaks-container">
            <div class="cizgiaks-about-stats-grid">
                <?php foreach ($stats as $stat): ?>
                <div class="cizgiaks-about-stat-item">
                    <span class="cizgiaks-about-stat-number"><?php echo esc_html($stat['number'] ?? ''); ?></span>
                    <span class="cizgiaks-about-stat-label"><?php echo esc_html($stat['label'] ?? ''); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Ekip -->
    <?php if (!empty($teamMembers)): ?>
    <section class="cizgiaks-about-team">
        <div class="cizgiaks-container">
            <header class="cizgiaks-about-team-header">
                <h2 class="cizgiaks-about-team-title"><?php echo esc_html(__('Ekibimiz')); ?></h2>
                <p class="cizgiaks-about-team-desc"><?php echo esc_html(__('Deneyimli ekibimizle size en iyi hizmeti sunuyoruz.')); ?></p>
            </header>
            <div class="cizgiaks-about-team-grid">
                <?php foreach ($teamMembers as $member): ?>
                <div class="cizgiaks-about-team-card">
                    <div class="cizgiaks-about-team-card-photo">
                        <?php if (!empty($member['photo'])): ?>
                        <img src="<?php echo esc_url($member['photo']); ?>" alt="<?php echo esc_attr($member['name'] ?? ''); ?>" loading="lazy">
                        <?php else: ?>
                        <span class="cizgiaks-about-team-card-initials"><?php echo esc_html(mb_substr($member['name'] ?? '?', 0, 1)); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="cizgiaks-about-team-card-body">
                        <h3 class="cizgiaks-about-team-card-name"><?php echo esc_html($member['name'] ?? ''); ?></h3>
                        <p class="cizgiaks-about-team-card-position"><?php echo esc_html($member['position'] ?? ''); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Ofisimiz (görsel sadece yüklüyse gösterilir) -->
    <?php if ($hasOfficeImage): ?>
    <section class="cizgiaks-about-office-section">
        <div class="cizgiaks-container">
            <h2 class="cizgiaks-about-office-title"><?php echo esc_html(__('Ofisimiz')); ?></h2>
            <p class="cizgiaks-about-office-desc"><?php echo esc_html(__('Çizgi Aks Gayrimenkul olarak müşterilerimize modern ve güvenilir bir ofis ortamında hizmet veriyoruz.')); ?></p>
            <div class="cizgiaks-about-office-image-wrap">
                <img src="<?php echo esc_url($aboutOfficeImage); ?>" alt="<?php echo esc_attr(__('Çizgi Aks Gayrimenkul ofis dış cephe')); ?>" class="cizgiaks-about-office-image" loading="lazy">
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Konumumuz (embed harita + Haritada Aç linki) -->
    <section class="cizgiaks-about-map-section">
        <div class="cizgiaks-container">
            <h2 class="cizgiaks-about-map-title"><?php echo esc_html(__('Konumumuz')); ?></h2>
            <p class="cizgiaks-about-map-desc"><?php echo esc_html(__('Ofisimizi haritada görmek ve yol tarifi almak için haritayı inceleyebilir veya aşağıdaki butondan tam ekran açabilirsiniz.')); ?></p>
            <div class="cizgiaks-about-map-embed-wrap">
                <iframe
                    src="<?php echo esc_url($aboutMapEmbedUrl); ?>"
                    width="600"
                    height="450"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="<?php echo esc_attr(__('Çizgi Aks Gayrimenkul konum haritası')); ?>"
                    class="cizgiaks-about-map-iframe"
                ></iframe>
            </div>
            <a href="<?php echo esc_url($aboutMapUrl); ?>" target="_blank" rel="noopener noreferrer" class="cizgiaks-about-map-btn">
                <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                <?php echo esc_html(__('Haritada Aç')); ?>
            </a>
        </div>
    </section>

    <!-- CTA -->
    <section class="cizgiaks-about-cta">
        <div class="cizgiaks-container cizgiaks-about-cta-inner">
            <h2 class="cizgiaks-about-cta-title"><?php echo esc_html($ctaTitle); ?></h2>
            <?php if ($ctaDescription): ?>
            <p class="cizgiaks-about-cta-desc"><?php echo esc_html($ctaDescription); ?></p>
            <?php endif; ?>
            <a href="<?php echo esc_url($ctaButtonLink); ?>" class="cizgiaks-about-cta-btn">
                <?php echo esc_html($ctaButtonText); ?>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </section>
</div>

<style>
.cizgiaks-about-page .cizgiaks-container { max-width: 1280px; margin: 0 auto; padding: 0 1rem; }
@media (min-width: 1024px) { .cizgiaks-about-page .cizgiaks-container { padding-left: 1.5rem; padding-right: 1.5rem; } }
.cizgiaks-about-page .cizgiaks-about-stats { background-color: var(--color-primary, <?php echo $primaryColor; ?>); }
.cizgiaks-about-page .cizgiaks-about-cta-btn { background-color: var(--color-primary, <?php echo $primaryColor; ?>); }
.cizgiaks-about-page .cizgiaks-about-cta-btn:hover { background-color: var(--color-primary, <?php echo $primaryColor; ?>); filter: brightness(1.1); }
.cizgiaks-about-page .cizgiaks-about-hero-subtitle { color: var(--color-primary, <?php echo $primaryColor; ?>); }
.cizgiaks-about-page .cizgiaks-about-contact-block { margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--text-muted, #e5e7eb); color: var(--text-muted, #6b7280); font-size: 0.9375rem; }
.cizgiaks-about-page .cizgiaks-about-contact-block a { color: var(--color-primary, <?php echo $primaryColor; ?>); text-decoration: none; }
.cizgiaks-about-page .cizgiaks-about-contact-block a:hover { text-decoration: underline; }
.cizgiaks-about-page .cizgiaks-about-contact-item { white-space: nowrap; }
@media (max-width: 640px) { .cizgiaks-about-page .cizgiaks-about-contact-block { display: flex; flex-direction: column; gap: 0.5rem; } .cizgiaks-about-page .cizgiaks-about-contact-item { white-space: normal; } }
.cizgiaks-about-page .cizgiaks-about-content-section { padding: 3rem 0; }
.cizgiaks-about-page .cizgiaks-about-content-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    align-items: start;
}
@media (min-width: 768px) {
    .cizgiaks-about-page .cizgiaks-about-content-row {
        grid-template-columns: minmax(280px, 42%) 1fr;
        gap: 2.5rem;
    }
}
.cizgiaks-about-page .cizgiaks-about-content-media {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #e5e7eb;
}
.cizgiaks-about-page .cizgiaks-about-content-img { width: 100%; height: auto; display: block; vertical-align: middle; object-fit: cover; }
.cizgiaks-about-page .cizgiaks-about-map-section { padding: 3rem 0; background: #f8fafc; }
.cizgiaks-about-page .cizgiaks-about-map-title { margin: 0 0 0.5rem; font-size: 1.5rem; font-weight: 700; color: var(--text, #1f2937); }
.cizgiaks-about-page .cizgiaks-about-map-desc { margin: 0 0 1.5rem; font-size: 1rem; color: var(--text-muted, #6b7280); line-height: 1.5; }
.cizgiaks-about-page .cizgiaks-about-map-embed-wrap { margin-bottom: 1.5rem; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid #e5e7eb; line-height: 0; }
.cizgiaks-about-page .cizgiaks-about-map-iframe { width: 100%; max-width: 100%; height: 450px; display: block; }
@media (max-width: 640px) { .cizgiaks-about-page .cizgiaks-about-map-iframe { height: 300px; } }
.cizgiaks-about-page .cizgiaks-about-map-btn {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.75rem 1.5rem; background: var(--color-primary, <?php echo $primaryColor; ?>); color: #fff;
    font-weight: 600; text-decoration: none; border-radius: 8px;
    transition: filter 0.2s, transform 0.15s;
}
.cizgiaks-about-page .cizgiaks-about-map-btn:hover { color: #fff; filter: brightness(1.1); transform: translateY(-1px); }
.cizgiaks-about-page .cizgiaks-about-map-btn i { font-size: 1.125rem; }
.cizgiaks-about-page .cizgiaks-about-office-section { padding: 3rem 0; }
.cizgiaks-about-page .cizgiaks-about-office-title { margin: 0 0 0.5rem; font-size: 1.5rem; font-weight: 700; color: var(--text, #1f2937); }
.cizgiaks-about-page .cizgiaks-about-office-desc { margin: 0 0 1.5rem; font-size: 1rem; color: var(--text-muted, #6b7280); line-height: 1.5; }
.cizgiaks-about-page .cizgiaks-about-office-image-wrap { border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid #e5e7eb; }
.cizgiaks-about-page .cizgiaks-about-office-image { width: 100%; height: auto; display: block; vertical-align: middle; }
</style>
