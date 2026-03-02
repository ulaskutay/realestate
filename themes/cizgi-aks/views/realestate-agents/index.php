<?php
/**
 * Çizgi Aks - Danışmanlar listesi sayfası (tema override)
 * Veri: $agents, $title
 */
get_header([
    'title' => (isset($title) ? $title : __('Emlak Danışmanlarımız')) . ' - ' . get_option('site_name', ''),
    'meta_description' => __('Profesyonel emlak danışmanlarımızla tanışın. Size en uygun emlağı bulmanızda yardımcı oluyoruz.')
]);

$agents = $agents ?? [];
$danismanBase = function_exists('localized_url') ? rtrim(localized_url('/danisman'), '/') : rtrim(site_url('/danisman'), '/');
$themeLoader = class_exists('ThemeLoader') ? ThemeLoader::getInstance() : null;
$primaryColor = $themeLoader ? $themeLoader->getColor('primary', '#bc1a1a') : '#bc1a1a';
?>
<section class="cizgiaks-agents-page">
    <div class="cizgiaks-container">
        <header class="cizgiaks-page-header">
            <h1 class="cizgiaks-page-title"><?php echo esc_html(__('Emlak Danışmanlarımız')); ?></h1>
            <p class="cizgiaks-page-subtitle"><?php echo esc_html(__('Profesyonel ekibimizle tanışın')); ?></p>
        </header>
        <?php if (empty($agents)): ?>
        <div class="cizgiaks-agents-empty">
            <div class="cizgiaks-agents-empty-icon"><i class="fas fa-users"></i></div>
            <h3 class="cizgiaks-agents-empty-title"><?php echo esc_html(__('Henüz danışman eklenmemiş')); ?></h3>
            <p class="cizgiaks-agents-empty-text"><?php echo esc_html(__('Yakında danışmanlarımız burada görünecek.')); ?></p>
        </div>
        <?php else: ?>
        <div class="cizgiaks-agents-grid">
            <?php foreach ($agents as $agent):
                $agentUrl = $danismanBase . '/' . ($agent['slug'] ?? '');
                $agentName = trim($agent['first_name'] . ' ' . $agent['last_name']);
            ?>
            <article class="cizgiaks-agent-card">
                <div class="cizgiaks-agent-card-image">
                    <a href="<?php echo esc_url($agentUrl); ?>">
                        <?php if (!empty($agent['photo'])): ?>
                        <img src="<?php echo esc_url($agent['photo']); ?>" alt="<?php echo esc_attr($agentName); ?>" loading="lazy">
                        <?php else: ?>
                        <span class="cizgiaks-agent-card-initials"><?php echo esc_html(mb_substr($agent['first_name'], 0, 1) . mb_substr($agent['last_name'], 0, 1)); ?></span>
                        <?php endif; ?>
                    </a>
                    <?php if (!empty($agent['is_featured'])): ?>
                    <span class="cizgiaks-agent-badge cizgiaks-agent-badge--featured"><?php echo esc_html(__('Öne Çıkan')); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($agent['experience_years'])): ?>
                    <span class="cizgiaks-agent-badge cizgiaks-agent-badge--experience"><?php echo esc_html($agent['experience_years']); ?> <?php echo esc_html(__('Yıl')); ?></span>
                    <?php endif; ?>
                </div>
                <div class="cizgiaks-agent-card-body">
                    <h3 class="cizgiaks-agent-card-title">
                        <a href="<?php echo esc_url($agentUrl); ?>"><?php echo esc_html($agentName); ?></a>
                    </h3>
                    <?php if (!empty($agent['specializations'])): ?>
                    <p class="cizgiaks-agent-card-spec"><?php echo esc_html(mb_substr($agent['specializations'], 0, 60)); ?><?php echo mb_strlen($agent['specializations']) > 60 ? '...' : ''; ?></p>
                    <?php endif; ?>
                    <?php if (!empty($agent['bio'])): ?>
                    <p class="cizgiaks-agent-card-bio"><?php echo esc_html(mb_substr(strip_tags($agent['bio']), 0, 120)); ?><?php echo mb_strlen(strip_tags($agent['bio'])) > 120 ? '...' : ''; ?></p>
                    <?php endif; ?>
                    <div class="cizgiaks-agent-card-contact">
                        <?php if (!empty($agent['phone'])): ?>
                        <a href="tel:<?php echo esc_attr($agent['phone']); ?>" class="cizgiaks-agent-contact-item"><i class="fas fa-phone"></i> <?php echo esc_html($agent['phone']); ?></a>
                        <?php endif; ?>
                        <?php if (!empty($agent['email'])): ?>
                        <a href="mailto:<?php echo esc_attr($agent['email']); ?>" class="cizgiaks-agent-contact-item"><i class="fas fa-envelope"></i> <?php echo esc_html($agent['email']); ?></a>
                        <?php endif; ?>
                    </div>
                    <div class="cizgiaks-agent-card-btn">
                        <a href="<?php echo esc_url($agentUrl); ?>" class="cizgiaks-btn cizgiaks-btn-primary cizgiaks-btn--block" style="background:<?php echo esc_attr($primaryColor); ?>"><?php echo esc_html(__('Profilini Görüntüle')); ?></a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
