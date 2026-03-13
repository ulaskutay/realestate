<?php
/**
 * Çizgi Aks Gayrimenkul - Header
 * Üst bar (logo + iletişim) + navigasyon (menüden)
 */
$themeLoader = $themeLoader ?? null;
if (!$themeLoader) {
    return;
}

$topBg = $themeLoader->getSetting('top_bg', '#ffffff', 'header');
$navBg = $themeLoader->getSetting('nav_bg', '#1f2937', 'header');
$navText = $themeLoader->getSetting('nav_text', '#ffffff', 'header');
$talepText = __($themeLoader->getSetting('talep_text', 'Talep Gönder', 'header'));
$talepLinkRaw = $themeLoader->getSetting('talep_link', '/contact', 'header');
$talepLink = function_exists('localized_url') ? localized_url($talepLinkRaw) : $talepLinkRaw;
$phone = $themeLoader->getSetting('phone', '', 'header');
$workingHours = __($themeLoader->getSetting('working_hours', '09:00 - 18:00', 'header'));

$headerMenu = get_menu('header');
$menuItems = $headerMenu['items'] ?? [];

$siteName = __(get_option('site_name', 'Çizgi Aks Gayrimenkul'));
$siteLogo = $themeLoader->getLogo();
$logoWidth = $themeLoader->getLogoWidth();
$logoHeight = $themeLoader->getLogoHeight();
$primaryColor = $themeLoader->getColor('primary', '#16a34a');
$homeUrl = function_exists('localized_url') ? localized_url('/') : (function_exists('site_url') ? site_url('/') : '/');
?>

<!-- Üst bar -->
<div class="cizgiaks-topbar" style="background-color: <?php echo esc_attr($topBg); ?>;">
    <div class="cizgiaks-container cizgiaks-topbar-inner">
        <a href="<?php echo esc_url($homeUrl); ?>" class="cizgiaks-logo">
            <?php if ($siteLogo): ?>
                <img src="<?php echo esc_url($siteLogo); ?>" alt="<?php echo esc_attr($siteName); ?>"
                    <?php if ($logoWidth): ?> width="<?php echo (int)$logoWidth; ?>"<?php endif; ?>
                    <?php if ($logoHeight): ?> height="<?php echo (int)$logoHeight; ?>"<?php endif; ?>
                >
            <?php else: ?>
                <span class="cizgiaks-logo-text"><?php echo esc_html($siteName); ?></span>
            <?php endif; ?>
        </a>
        <div class="cizgiaks-topbar-right">
            <a href="<?php echo esc_url($talepLink); ?>" class="cizgiaks-topbar-item">
                <i class="fas fa-paper-plane" style="color: <?php echo esc_attr($primaryColor); ?>;"></i>
                <span><?php echo esc_html(__('Gayrimenkul Arıyorum')); ?></span>
                <strong><?php echo esc_html($talepText); ?></strong>
            </a>
            <?php if ($phone): ?>
            <div class="cizgiaks-topbar-item">
                <i class="fas fa-phone-alt" style="color: <?php echo esc_attr($primaryColor); ?>;"></i>
                <span><?php echo esc_html(__('Telefon')); ?></span>
                <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a>
            </div>
            <?php endif; ?>
            <div class="cizgiaks-topbar-item">
                <i class="fas fa-clock" style="color: <?php echo esc_attr($primaryColor); ?>;"></i>
                <span><?php echo esc_html(__('Çalışma Saatleri')); ?></span>
                <span><?php echo esc_html($workingHours); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Navigasyon -->
<nav class="cizgiaks-nav" style="background-color: <?php echo esc_attr($navBg); ?>; color: <?php echo esc_attr($navText); ?>;">
    <div class="cizgiaks-container cizgiaks-nav-inner">
        <a href="<?php echo esc_url($homeUrl); ?>" class="cizgiaks-nav-home" style="color: <?php echo esc_attr($navText); ?>;">
            <i class="fas fa-home"></i>
        </a>
        <ul class="cizgiaks-nav-menu">
            <?php foreach ($menuItems as $item): ?>
                <?php
                $url = function_exists('get_localized_menu_url') ? get_localized_menu_url($item['url']) : $item['url'];
                $target = ($item['target'] ?? '') === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '';
                $children = $item['children'] ?? [];
                ?>
                <?php if (empty($children)): ?>
                    <li>
                        <a href="<?php echo esc_url($url); ?>" style="color: <?php echo esc_attr($navText); ?>;"<?php echo $target; ?>><?php echo esc_html(__($item['title'])); ?></a>
                    </li>
                <?php else: ?>
                    <li class="cizgiaks-nav-dropdown">
                        <a href="<?php echo esc_url($url); ?>" class="cizgiaks-nav-dropdown-trigger" style="color: <?php echo esc_attr($navText); ?>;"<?php echo $target; ?>>
                            <?php echo esc_html(__($item['title'])); ?>
                            <i class="fas fa-chevron-down"></i>
                        </a>
                        <ul class="cizgiaks-nav-dropdown-menu">
                            <?php foreach ($children as $child): ?>
                                <li>
                                    <a href="<?php echo esc_url(function_exists('get_localized_menu_url') ? get_localized_menu_url($child['url']) : $child['url']); ?>"<?php echo ($child['target'] ?? '') === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html(__($child['title'])); ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
        <label for="cizgiaks-nav-mobile-check" class="cizgiaks-nav-mobile-toggle" aria-label="<?php echo esc_attr(__('Menü')); ?>">
            <i class="fas fa-bars"></i>
        </label>
    </div>
</nav>

<!-- Mobil menü: checkbox ile aç/kapa (JS yok) -->
<input type="checkbox" id="cizgiaks-nav-mobile-check" class="cizgiaks-nav-mobile-check" aria-hidden="true" hidden>
<div class="cizgiaks-mobile-menu" id="cizgiaks-mobile-menu" role="dialog" aria-label="<?php echo esc_attr(__('Menü')); ?>">
    <div class="cizgiaks-mobile-menu-inner">
        <div class="cizgiaks-mobile-menu-header">
            <span class="cizgiaks-mobile-menu-title"><?php echo esc_html(__('Menü')); ?></span>
            <label for="cizgiaks-nav-mobile-check" class="cizgiaks-mobile-menu-close" aria-label="<?php echo esc_attr(__('Menüyü kapat')); ?>">
                <i class="fas fa-times" aria-hidden="true"></i>
            </label>
        </div>
        <ul class="cizgiaks-mobile-menu-list">
            <li class="cizgiaks-mobile-menu-item">
                <a href="<?php echo esc_url($homeUrl); ?>" class="cizgiaks-mobile-menu-link"><i class="fas fa-home" aria-hidden="true"></i> <?php echo esc_html(__('Ana Sayfa')); ?></a>
            </li>
            <?php foreach ($menuItems as $item): ?>
                <?php
                $url = function_exists('get_localized_menu_url') ? get_localized_menu_url($item['url']) : $item['url'];
                $target = ($item['target'] ?? '') === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '';
                $children = $item['children'] ?? [];
                ?>
                <li class="cizgiaks-mobile-menu-item">
                    <?php if (empty($children)): ?>
                        <a href="<?php echo esc_url($url); ?>" class="cizgiaks-mobile-menu-link"<?php echo $target; ?>><?php echo esc_html(__($item['title'])); ?></a>
                    <?php else: ?>
                        <details class="cizgiaks-mobile-submenu">
                            <summary class="cizgiaks-mobile-submenu-trigger">
                                <span><?php echo esc_html(__($item['title'])); ?></span>
                                <i class="fas fa-chevron-down cizgiaks-mobile-submenu-icon" aria-hidden="true"></i>
                            </summary>
                            <ul class="cizgiaks-mobile-submenu-list">
                                <li><a href="<?php echo esc_url($url); ?>" class="cizgiaks-mobile-submenu-link"<?php echo $target; ?>><?php echo esc_html(__($item['title'])); ?></a></li>
                                <?php foreach ($children as $child): ?>
                                    <li><a href="<?php echo esc_url(function_exists('get_localized_menu_url') ? get_localized_menu_url($child['url']) : $child['url']); ?>" class="cizgiaks-mobile-submenu-link"<?php echo ($child['target'] ?? '') === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html(__($child['title'])); ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </details>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<label for="cizgiaks-nav-mobile-check" class="cizgiaks-mobile-menu-backdrop" id="cizgiaks-mobile-backdrop" aria-label="<?php echo esc_attr(__('Menüyü kapat')); ?>"></label>
