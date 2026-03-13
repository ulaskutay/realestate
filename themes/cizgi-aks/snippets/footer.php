<?php
/**
 * Çizgi Aks Gayrimenkul - Modern Footer (geniş link hacmi)
 */
$themeLoader = $themeLoader ?? null;
$show = $themeLoader ? ($themeLoader->getCustomSetting('footer_show', true) !== false) : true;
if (!$show) {
    return;
}

$footerBg = $themeLoader ? $themeLoader->getCustomSetting('footer_bg_color', '#0f172a') : '#0f172a';
$footerText = $themeLoader ? $themeLoader->getCustomSetting('footer_text_color', '#f1f5f9') : '#f1f5f9';
$siteName = __(get_option('site_name', 'Çizgi Aks Gayrimenkul'));
// Logo altı metni: Ayarlar > Genel'de site_description; kurulum varsayılanı veya boşsa gayrimenkul açıklaması
$siteDescriptionRaw = get_option('site_description', '');
$footerDefaultDesc = 'Satılık ve kiralık konut, iş yeri ve arsa ilanları. Güvenilir gayrimenkul danışmanlığı.';
if ($siteDescriptionRaw === '' || $siteDescriptionRaw === 'Modern içerik yönetim sistemi') {
    $siteDescription = __($footerDefaultDesc);
} else {
    $siteDescription = __($siteDescriptionRaw);
}
// Logo: header ile aynı kaynak (getLogo = tema branding, yoksa global site_logo)
$siteLogo = null;
if ($themeLoader) {
    $siteLogo = $themeLoader->getLogo();
}
if (empty($siteLogo) && function_exists('get_option')) {
    $siteLogo = get_option('site_logo', '');
}
// Veritabanında JSON/array kaydedilmişse tek URL string'e çevir
if (is_array($siteLogo)) {
    $siteLogo = $siteLogo[0] ?? $siteLogo['url'] ?? '';
}
if (is_string($siteLogo)) {
    $siteLogo = trim($siteLogo) !== '' ? $siteLogo : null;
}
if (empty($siteLogo) && function_exists('site_url')) {
    $defaultPath = defined('ROOT_PATH') ? (ROOT_PATH . '/public/uploads/Logo/codetic-logo.jpg') : (__DIR__ . '/../../../public/uploads/Logo/codetic-logo.jpg');
    if (@is_file($defaultPath)) {
        $siteLogo = site_url('uploads/Logo/codetic-logo.jpg');
    }
}
$year = date('Y');
$homeUrl = function_exists('localized_url') ? localized_url('/') : (function_exists('site_url') ? site_url('/') : '/');
$baseListingsUrl = function_exists('localized_url') ? localized_url('/ilanlar') : (function_exists('site_url') ? site_url('/ilanlar') : '/ilanlar');

// Menüler
$footerMenu = function_exists('get_menu') ? get_menu('footer') : null;
$footerMenuItems = $footerMenu['items'] ?? [];
$footerMenu2 = function_exists('get_menu') ? get_menu('footer-2') : null;
$footerMenu2Items = $footerMenu2['items'] ?? [];

// İlan kategorileri
$listingCategories = [];
try {
    if (function_exists('get_db')) {
        $db = get_db();
        if ($db) {
            $t = $db->getConnection()->query("SHOW TABLES LIKE 'listing_categories'");
            if ($t && $t->rowCount() > 0) {
                $listingCategories = $db->fetchAll("SELECT id, slug, name, kind FROM listing_categories ORDER BY kind ASC, display_order ASC, name ASC");
            }
        }
    }
} catch (Exception $e) {
    $listingCategories = [];
}
$categoriesByKind = ['type' => [], 'status' => []];
foreach ($listingCategories as $c) {
    $k = $c['kind'] ?? 'type';
    if (!isset($categoriesByKind[$k])) $categoriesByKind[$k] = [];
    $categoriesByKind[$k][] = $c;
}

// İletişim – tamamı Ayarlar sayfasından (Şirket Bilgileri + Sosyal Medya)
$companyPhone = function_exists('get_option') ? get_option('company_phone', get_option('contact_phone', '')) : '';
$companyEmail = function_exists('get_option') ? get_option('company_email', get_option('contact_email', get_option('admin_email', ''))) : '';
$companyAddress = function_exists('get_option') ? __(get_option('company_address', get_option('contact_address', ''))) : '';
$phone = $companyPhone;

// Sosyal medya – Ayarlar > Sosyal Medya
$socialFacebook  = function_exists('get_option') ? get_option('social_facebook', '') : '';
$socialInstagram = function_exists('get_option') ? get_option('social_instagram', '') : '';
$socialTwitter   = function_exists('get_option') ? get_option('social_twitter', '') : '';
$socialLinkedin  = function_exists('get_option') ? get_option('social_linkedin', '') : '';
$socialYoutube   = function_exists('get_option') ? get_option('social_youtube', '') : '';
$socialTiktok    = function_exists('get_option') ? get_option('social_tiktok', '') : '';
$socialPinterest = function_exists('get_option') ? get_option('social_pinterest', '') : '';
$hasSocial = $socialFacebook || $socialInstagram || $socialTwitter || $socialLinkedin || $socialYoutube || $socialTiktok || $socialPinterest;

// Popüler bölgeler (şehir filtre linkleri)
$sep = strpos($baseListingsUrl, '?') !== false ? '&' : '?';
$popularRegions = [
    ['name' => 'İstanbul', 'url' => $baseListingsUrl . $sep . 'city=İstanbul'],
    ['name' => 'Ankara', 'url' => $baseListingsUrl . $sep . 'city=Ankara'],
    ['name' => 'İzmir', 'url' => $baseListingsUrl . $sep . 'city=İzmir'],
    ['name' => 'Antalya', 'url' => $baseListingsUrl . $sep . 'city=Antalya'],
    ['name' => 'Bursa', 'url' => $baseListingsUrl . $sep . 'city=Bursa'],
    ['name' => 'Muğla', 'url' => $baseListingsUrl . $sep . 'city=Muğla'],
];

// Kurumsal linkler (menü yoksa)
$corporateLinks = [
    ['title' => __('Hakkımızda'), 'url' => (function_exists('localized_url') ? localized_url('/hakkimizda') : $homeUrl . 'hakkimizda')],
    ['title' => __('İletişim'), 'url' => (function_exists('localized_url') ? localized_url('/contact') : $homeUrl . 'contact')],
    ['title' => __('Danışmanlar'), 'url' => (function_exists('localized_url') ? localized_url('/danismanlar') : $homeUrl . 'danismanlar')],
    ['title' => __('Haritada Ara'), 'url' => (function_exists('localized_url') ? localized_url('/haritada-ara') : $homeUrl . 'haritada-ara')],
];
?>
<footer class="cizgiaks-footer cizgiaks-footer-modern" style="background-color: <?php echo esc_attr($footerBg); ?>; color: <?php echo esc_attr($footerText); ?>;">
    <div class="cizgiaks-footer-main">
        <div class="cizgiaks-container">
            <div class="cizgiaks-footer-grid">
                <!-- Marka & Açıklama -->
                <div class="cizgiaks-footer-brand">
                    <a href="<?php echo esc_url($homeUrl); ?>" class="cizgiaks-footer-logo">
                        <?php if ($siteLogo): ?>
                            <img src="<?php echo esc_url($siteLogo); ?>" alt="<?php echo esc_attr($siteName); ?>">
                        <?php else: ?>
                            <span class="cizgiaks-footer-logo-text"><?php echo esc_html($siteName); ?></span>
                        <?php endif; ?>
                    </a>
                    <?php if ($siteDescription): ?>
                        <p class="cizgiaks-footer-desc"><?php echo esc_html($siteDescription); ?></p>
                    <?php endif; ?>
                    <?php if ($hasSocial): ?>
                        <div class="cizgiaks-footer-social">
                            <?php if ($socialFacebook): ?><a href="<?php echo esc_url($socialFacebook); ?>" target="_blank" rel="noopener noreferrer" class="cizgiaks-footer-social-link" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
                            <?php if ($socialInstagram): ?><a href="<?php echo esc_url($socialInstagram); ?>" target="_blank" rel="noopener noreferrer" class="cizgiaks-footer-social-link" aria-label="Instagram"><i class="fab fa-instagram"></i></a><?php endif; ?>
                            <?php if ($socialTwitter): ?><a href="<?php echo esc_url($socialTwitter); ?>" target="_blank" rel="noopener noreferrer" class="cizgiaks-footer-social-link" aria-label="Twitter"><i class="fab fa-twitter"></i></a><?php endif; ?>
                            <?php if ($socialLinkedin): ?><a href="<?php echo esc_url($socialLinkedin); ?>" target="_blank" rel="noopener noreferrer" class="cizgiaks-footer-social-link" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
                            <?php if ($socialYoutube): ?><a href="<?php echo esc_url($socialYoutube); ?>" target="_blank" rel="noopener noreferrer" class="cizgiaks-footer-social-link" aria-label="YouTube"><i class="fab fa-youtube"></i></a><?php endif; ?>
                            <?php if ($socialTiktok): ?><a href="<?php echo esc_url($socialTiktok); ?>" target="_blank" rel="noopener noreferrer" class="cizgiaks-footer-social-link" aria-label="TikTok"><i class="fab fa-tiktok"></i></a><?php endif; ?>
                            <?php if ($socialPinterest): ?><a href="<?php echo esc_url($socialPinterest); ?>" target="_blank" rel="noopener noreferrer" class="cizgiaks-footer-social-link" aria-label="Pinterest"><i class="fab fa-pinterest-p"></i></a><?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- İlan Kategorileri -->
                <?php
                $types = $categoriesByKind['type'] ?? [];
                $statuses = $categoriesByKind['status'] ?? [];
                $allCategories = array_merge($statuses, $types);
                $defaultCategories = [
                    ['name' => __('Satılık'), 'slug' => 'satilik'],
                    ['name' => __('Kiralık'), 'slug' => 'kiralik'],
                    ['name' => __('Günlük Kiralık'), 'slug' => 'gunluk-kiralik'],
                    ['name' => __('Yatırımlık'), 'slug' => 'yatirimlik'],
                ];
                $showCategories = !empty($allCategories) ? $allCategories : $defaultCategories;
                ?>
                <!-- İlan Kategorileri (mobilde native <details> – JS yok, iPhone’da kesin çalışır) -->
                <details class="cizgiaks-footer-accordion">
                    <summary class="cizgiaks-footer-accordion-trigger">
                        <span class="cizgiaks-footer-accordion-title"><?php echo esc_html(__('İlan Kategorileri')); ?></span>
                        <i class="fas fa-chevron-down cizgiaks-footer-accordion-icon" aria-hidden="true"></i>
                    </summary>
                    <div class="cizgiaks-footer-accordion-content">
                        <div class="cizgiaks-footer-col">
                            <h3 class="cizgiaks-footer-title"><?php echo esc_html(__('İlan Kategorileri')); ?></h3>
                            <ul class="cizgiaks-footer-links">
                                <?php foreach ($showCategories as $cat):
                                    $catUrl = $baseListingsUrl . $sep . 'category=' . urlencode($cat['slug']);
                                    ?>
                                    <li><a href="<?php echo esc_url($catUrl); ?>"><?php echo esc_html($cat['name']); ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </details>

                <!-- Hızlı Linkler -->
                <details class="cizgiaks-footer-accordion">
                    <summary class="cizgiaks-footer-accordion-trigger">
                        <span class="cizgiaks-footer-accordion-title"><?php echo esc_html(__('Hızlı Linkler')); ?></span>
                        <i class="fas fa-chevron-down cizgiaks-footer-accordion-icon" aria-hidden="true"></i>
                    </summary>
                    <div class="cizgiaks-footer-accordion-content">
                        <div class="cizgiaks-footer-col">
                            <h3 class="cizgiaks-footer-title"><?php echo esc_html(__('Hızlı Linkler')); ?></h3>
                            <ul class="cizgiaks-footer-links">
                                <?php if (!empty($footerMenuItems)): ?>
                                    <?php foreach ($footerMenuItems as $item):
                                        if (!empty($item['children'])) continue;
                                        $url = function_exists('get_localized_menu_url') ? get_localized_menu_url($item['url']) : $item['url'];
                                        $target = ($item['target'] ?? '') === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '';
                                        ?>
                                        <li><a href="<?php echo esc_url($url); ?>"<?php echo $target; ?>><?php echo esc_html($item['title'] ?? $item['label'] ?? ''); ?></a></li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <?php foreach ($corporateLinks as $link): ?>
                                        <li><a href="<?php echo esc_url($link['url']); ?>"><?php echo esc_html($link['title']); ?></a></li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </details>

                <!-- Popüler Bölgeler -->
                <details class="cizgiaks-footer-accordion">
                    <summary class="cizgiaks-footer-accordion-trigger">
                        <span class="cizgiaks-footer-accordion-title"><?php echo esc_html(__('Popüler Bölgeler')); ?></span>
                        <i class="fas fa-chevron-down cizgiaks-footer-accordion-icon" aria-hidden="true"></i>
                    </summary>
                    <div class="cizgiaks-footer-accordion-content">
                        <div class="cizgiaks-footer-col">
                            <h3 class="cizgiaks-footer-title"><?php echo esc_html(__('Popüler Bölgeler')); ?></h3>
                            <ul class="cizgiaks-footer-links">
                                <?php if (!empty($footerMenu2Items)): ?>
                                    <?php foreach ($footerMenu2Items as $item):
                                        if (!empty($item['children'])) continue;
                                        $url = function_exists('get_localized_menu_url') ? get_localized_menu_url($item['url']) : $item['url'];
                                        $target = ($item['target'] ?? '') === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '';
                                        ?>
                                        <li><a href="<?php echo esc_url($url); ?>"<?php echo $target; ?>><?php echo esc_html($item['title'] ?? $item['label'] ?? ''); ?></a></li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <?php foreach ($popularRegions as $r): ?>
                                        <li><a href="<?php echo esc_url($r['url']); ?>"><?php echo esc_html($r['name']); ?></a></li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </details>

                <!-- İletişim -->
                <details class="cizgiaks-footer-accordion">
                    <summary class="cizgiaks-footer-accordion-trigger">
                        <span class="cizgiaks-footer-accordion-title"><?php echo esc_html(__('İletişim')); ?></span>
                        <i class="fas fa-chevron-down cizgiaks-footer-accordion-icon" aria-hidden="true"></i>
                    </summary>
                    <div class="cizgiaks-footer-accordion-content">
                        <div class="cizgiaks-footer-col cizgiaks-footer-contact">
                            <h3 class="cizgiaks-footer-title"><?php echo esc_html(__('İletişim')); ?></h3>
                            <ul class="cizgiaks-footer-contact-list">
                                <?php if ($phone): ?>
                                <li>
                                    <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $phone)); ?>" class="cizgiaks-footer-contact-item">
                                        <i class="fas fa-phone-alt"></i>
                                        <span><?php echo esc_html($phone); ?></span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if ($companyEmail): ?>
                                <li>
                                    <a href="mailto:<?php echo esc_attr($companyEmail); ?>" class="cizgiaks-footer-contact-item">
                                        <i class="fas fa-envelope"></i>
                                        <span><?php echo esc_html($companyEmail); ?></span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if ($companyAddress): ?>
                                <li>
                                    <span class="cizgiaks-footer-contact-item cizgiaks-footer-address">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo esc_html($companyAddress); ?></span>
                                    </span>
                                    <?php
                                    $contactPageUrl = function_exists('localized_url') ? localized_url('/contact') : $homeUrl . 'contact';
                                    ?>
                                    <a href="<?php echo esc_url($contactPageUrl); ?>#harita" class="cizgiaks-footer-contact-item" style="margin-top:0.25rem; display:inline-flex; align-items:center; gap:0.35rem;">
                                        <i class="fas fa-external-link-alt" style="font-size:0.75rem;"></i>
                                        <span><?php echo esc_html(__('Haritada Gör')); ?></span>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </details>
            </div>
        </div>
    </div>

    <div class="cizgiaks-footer-bottom">
        <div class="cizgiaks-container">
            <div class="cizgiaks-footer-bottom-inner">
                <p class="cizgiaks-footer-copy">&copy; <?php echo esc_html($year); ?> <?php echo esc_html($siteName); ?>. <?php echo esc_html(__('Tüm hakları saklıdır.')); ?></p>
                <nav class="cizgiaks-footer-legal" aria-label="<?php echo esc_attr(__('Yasal')); ?>">
                    <?php
                    $privacyUrl = function_exists('localized_url') ? localized_url('/gizlilik') : $homeUrl . 'gizlilik';
                    $kvkkUrl = function_exists('localized_url') ? localized_url('/kvkk') : $homeUrl . 'kvkk';
                    ?>
                    <a href="<?php echo esc_url($privacyUrl); ?>"><?php echo esc_html(__('Gizlilik')); ?></a>
                    <span class="cizgiaks-footer-sep">·</span>
                    <a href="<?php echo esc_url($kvkkUrl); ?>"><?php echo esc_html(__('KVKK')); ?></a>
                </nav>
            </div>
        </div>
    </div>
</footer>
