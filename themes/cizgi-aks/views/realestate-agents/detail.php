<?php
/**
 * Çizgi Aks - Danışman detay sayfası (tema override)
 * Veri: $agent, $agentListings, $totalListings
 */
get_header([
    'title' => ($agent['first_name'] ?? '') . ' ' . ($agent['last_name'] ?? '') . ' - ' . get_option('site_name', ''),
    'meta_description' => !empty($agent['bio']) ? mb_substr(strip_tags($agent['bio']), 0, 160) : 'Emlak danışmanı profili'
]);

$agentName = trim($agent['first_name'] . ' ' . $agent['last_name']);
$agentListings = $agentListings ?? [];
$totalListings = (int) ($totalListings ?? 0);
$danismanlarUrl = function_exists('localized_url') ? localized_url('/danismanlar') : site_url('/danismanlar');
$ilanBase = function_exists('localized_url') ? rtrim(localized_url('/ilan'), '/') : rtrim(site_url('/ilan'), '/');
$ilanlarUrl = function_exists('localized_url') ? localized_url('/ilanlar') : site_url('/ilanlar');
$ilanlarUrl = function_exists('localized_url') ? localized_url('/ilanlar') : site_url('/ilanlar');
$themeLoader = class_exists('ThemeLoader') ? ThemeLoader::getInstance() : null;
$primaryColor = $themeLoader ? $themeLoader->getColor('primary', '#bc1a1a') : '#bc1a1a';

$propertyTypeLabels = [
    'house' => __('Müstakil Ev'),
    'apartment' => __('Daire'),
    'villa' => __('Villa'),
    'commercial' => __('Ticari'),
    'land' => __('Arsa')
];
?>
<section class="cizgiaks-agent-detail">
    <div class="cizgiaks-container">
        <header class="cizgiaks-page-header">
            <nav class="cizgiaks-agent-detail-breadcrumb">
                <a href="<?php echo esc_url($danismanlarUrl); ?>"><?php echo esc_html(__('Tüm Danışmanlar')); ?></a>
                <span aria-hidden="true"> / </span>
                <span><?php echo esc_html($agentName); ?></span>
            </nav>
            <h1 class="cizgiaks-page-title"><?php echo esc_html($agentName); ?></h1>
            <?php if (!empty($agent['specializations'])): ?>
            <p class="cizgiaks-page-subtitle"><?php echo esc_html($agent['specializations']); ?></p>
            <?php endif; ?>
        </header>
        <div class="cizgiaks-agent-detail-layout">
            <aside class="cizgiaks-agent-detail-sidebar">
                <div class="cizgiaks-agent-detail-photo">
                    <?php if (!empty($agent['photo'])): ?>
                    <img src="<?php echo esc_url($agent['photo']); ?>" alt="<?php echo esc_attr($agentName); ?>">
                    <?php else: ?>
                    <span class="cizgiaks-agent-detail-photo-initials"><?php echo esc_html(mb_substr($agent['first_name'], 0, 1) . mb_substr($agent['last_name'], 0, 1)); ?></span>
                    <?php endif; ?>
                </div>
                <div class="cizgiaks-agent-detail-info">
                    <h2 class="cizgiaks-agent-detail-name"><?php echo esc_html($agentName); ?></h2>
                    <?php if (!empty($agent['experience_years'])): ?>
                    <p class="cizgiaks-agent-detail-exp"><strong><?php echo esc_html($agent['experience_years']); ?></strong> <?php echo esc_html(__('yıl deneyim')); ?></p>
                    <?php endif; ?>
                    <div class="cizgiaks-agent-detail-contact-list">
                        <?php if (!empty($agent['phone'])): ?>
                        <a href="tel:<?php echo esc_attr($agent['phone']); ?>" class="cizgiaks-agent-detail-contact-item">
                            <i class="fas fa-phone"></i>
                            <div><span style="font-size:0.75rem;color:var(--cizgiaks-text-muted);"><?php echo esc_html(__('Telefon')); ?></span><br><?php echo esc_html($agent['phone']); ?></div>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($agent['email'])): ?>
                        <a href="mailto:<?php echo esc_attr($agent['email']); ?>" class="cizgiaks-agent-detail-contact-item">
                            <i class="fas fa-envelope"></i>
                            <div><span style="font-size:0.75rem;color:var(--cizgiaks-text-muted);"><?php echo esc_html(__('E-posta')); ?></span><br><?php echo esc_html($agent['email']); ?></div>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($agent['facebook']) || !empty($agent['instagram']) || !empty($agent['linkedin']) || !empty($agent['twitter'])): ?>
                    <p style="margin:0 0 0.5rem;font-size:0.875rem;font-weight:600;"><?php echo esc_html(__('Sosyal Medya')); ?></p>
                    <div class="cizgiaks-agent-detail-social">
                        <?php if (!empty($agent['facebook'])): ?><a href="<?php echo esc_url($agent['facebook']); ?>" target="_blank" rel="noopener" class="cizgiaks-agent-social-link"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
                        <?php if (!empty($agent['instagram'])): ?><a href="<?php echo esc_url($agent['instagram']); ?>" target="_blank" rel="noopener" class="cizgiaks-agent-social-link"><i class="fab fa-instagram"></i></a><?php endif; ?>
                        <?php if (!empty($agent['linkedin'])): ?><a href="<?php echo esc_url($agent['linkedin']); ?>" target="_blank" rel="noopener" class="cizgiaks-agent-social-link"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
                        <?php if (!empty($agent['twitter'])): ?><a href="<?php echo esc_url($agent['twitter']); ?>" target="_blank" rel="noopener" class="cizgiaks-agent-social-link"><i class="fab fa-twitter"></i></a><?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </aside>

            <div class="cizgiaks-agent-detail-main">
                <?php if (!empty($agent['bio'])): ?>
                <div class="cizgiaks-agent-detail-block">
                    <h2><?php echo esc_html(__('Hakkında')); ?></h2>
                    <div class="cizgiaks-agent-detail-bio-text"><?php echo nl2br(esc_html($agent['bio'])); ?></div>
                </div>
                <?php endif; ?>

                <?php if (!empty($agent['specializations'])): ?>
                <div class="cizgiaks-agent-detail-block">
                    <h2><?php echo esc_html(__('Uzmanlık Alanları')); ?></h2>
                    <div class="cizgiaks-agent-detail-bio-text"><?php echo nl2br(esc_html($agent['specializations'])); ?></div>
                </div>
                <?php endif; ?>

                <?php if ($totalListings > 0 || !empty($agentListings)): ?>
                <div class="cizgiaks-agent-detail-block">
                    <div class="cizgiaks-agent-detail-listings-title">
                        <h2><?php echo esc_html(__('İlanlar')); ?><?php if ($totalListings > 0): ?> <span style="font-size:1rem;font-weight:400;color:var(--cizgiaks-text-muted);">(<?php echo esc_html($totalListings); ?>)</span><?php endif; ?></h2>
                    </div>
                    <?php if (!empty($agentListings)): ?>
                    <div class="cizgiaks-agent-detail-listings-grid">
                        <?php foreach ($agentListings as $listing):
                            $listingPrice = function_exists('realestate_format_price') ? realestate_format_price($listing['price'] ?? 0) : '₺' . number_format($listing['price'] ?? 0, 0, ',', '.');
                            $listingSlug = !empty($listing['slug']) ? $listing['slug'] : $listing['id'];
                            $listingUrl = $ilanBase . '/' . $listingSlug;
                            $listingImage = !empty($listing['featured_image']) ? $listing['featured_image'] : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=400';
                            $listingStatus = ($listing['listing_status'] ?? 'sale') === 'rent' ? __('Kiralık') : __('Satılık');
                        ?>
                        <a href="<?php echo esc_url($listingUrl); ?>" class="cizgiaks-listing-card cizgiaks-listing-card--vitrin" style="text-decoration:none;color:inherit;">
                            <div class="cizgiaks-listing-card-image">
                                <img src="<?php echo esc_url($listingImage); ?>" alt="<?php echo esc_attr($listing['title']); ?>" loading="lazy">
                                <div class="cizgiaks-listing-ribbons">
                                    <span class="cizgiaks-ribbon cizgiaks-ribbon-<?php echo ($listing['listing_status'] ?? 'sale') === 'rent' ? 'kiralik' : 'satilik'; ?>"><?php echo esc_html($listingStatus); ?></span>
                                </div>
                            </div>
                            <div class="cizgiaks-listing-card-body">
                                <div class="cizgiaks-listing-price"><?php echo esc_html($listingPrice); ?></div>
                                <h3 class="cizgiaks-listing-title"><?php echo esc_html($listing['title']); ?></h3>
                                <div class="cizgiaks-listing-location"><?php echo esc_html($listing['location'] ?? ''); ?></div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($totalListings > count($agentListings)): ?>
                    <div class="cizgiaks-agent-detail-more">
                        <a href="<?php echo esc_url($ilanlarUrl . '?realtor=' . (int)$agent['id']); ?>" class="cizgiaks-btn cizgiaks-btn-primary" style="background:<?php echo esc_attr($primaryColor); ?>"><?php echo esc_html(__('Tüm İlanları Görüntüle')); ?></a>
                    </div>
                    <?php endif; ?>
                    <?php else: ?>
                    <div class="cizgiaks-agent-detail-empty"><?php echo esc_html(__('Henüz ilan eklenmemiş')); ?></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
