<?php
/**
 * Çizgi Aks Gayrimenkul - Ana Sayfa
 * Hero + Vitrin tek container ve tek sağ blokta birleşik
 */
$sections = $themeLoader->getPageSections('home');
$sectionsMap = [];
foreach ($sections as $s) {
    $sectionId = $s['section_id'] ?? '';
    if (!empty($sectionId)) {
        $sectionsMap[$sectionId] = $s;
    }
}

$defaultSections = [
    'hero' => [
        'title' => __('İlan Ara'),
        'settings' => [
            'search_title' => __('İlan Ara'),
            'callback_title' => __('Biz Sizi Arayalım'),
            'callback_text' => __('İhtiyacınıza uygun ilanları sizin için araştıralım.')
        ]
    ],
    'listings' => [
        'title' => __('Anasayfa Vitrin'),
        'settings' => [
            'title' => __('Anasayfa Vitrin'),
            'subtitle' => __('Sizin için seçtiğimiz vitrin ilanları'),
            'limit' => 8
        ]
    ]
];

$sectionOrder = ['hero', 'listings'];
$useUnified = in_array('hero', $sectionOrder) && in_array('listings', $sectionOrder);

if ($useUnified) {
    $heroSection = [
        'title' => $defaultSections['hero']['title'],
        'subtitle' => '',
        'settings' => array_merge(
            $defaultSections['hero']['settings'] ?? [],
            isset($sectionsMap['hero']['settings']) ? (is_array($sectionsMap['hero']['settings']) ? $sectionsMap['hero']['settings'] : (json_decode($sectionsMap['hero']['settings'], true) ?: [])) : []
        ),
        'items' => []
    ];
    $listingsSection = [
        'title' => $defaultSections['listings']['title'],
        'subtitle' => $defaultSections['listings']['settings']['subtitle'] ?? '',
        'settings' => array_merge(
            $defaultSections['listings']['settings'] ?? [],
            isset($sectionsMap['listings']['settings']) ? (is_array($sectionsMap['listings']['settings']) ? $sectionsMap['listings']['settings'] : (json_decode($sectionsMap['listings']['settings'], true) ?: [])) : []
        ),
        'items' => []
    ];
    ?>
<section class="cizgiaks-home-unified">
    <div class="cizgiaks-container cizgiaks-home-unified-container">
        <div class="cizgiaks-home-unified-layout">
            <div class="cizgiaks-home-unified-main">
                <?php
                $cizgiaks_unified_home = true;
                echo $themeLoader->renderComponent('hero', ['section' => $heroSection, 'cizgiaks_unified_home' => true]);
                echo $themeLoader->renderComponent('listings', ['section' => $listingsSection, 'cizgiaks_unified_home' => true]);
                $cizgiaks_unified_home = false;
                ?>
            </div>
            <?php echo $themeLoader->renderComponent('home-sidebar', ['section' => $heroSection]); ?>
        </div>
    </div>
</section>
<?php
} else {
    foreach ($sectionOrder as $sectionId) {
        if (!isset($defaultSections[$sectionId])) continue;
        $dbSection = $sectionsMap[$sectionId] ?? null;
        $default = $defaultSections[$sectionId];
        $dbSettings = [];
        if (!empty($dbSection['settings'])) {
            $dbSettings = is_array($dbSection['settings']) ? $dbSection['settings'] : (json_decode($dbSection['settings'], true) ?: []);
        }
        $section = [
            'title' => !empty($dbSection['title']) ? $dbSection['title'] : ($default['title'] ?? ''),
            'subtitle' => !empty($dbSection['subtitle']) ? $dbSection['subtitle'] : ($default['subtitle'] ?? ''),
            'settings' => !empty($dbSettings) ? array_merge($default['settings'] ?? [], $dbSettings) : ($default['settings'] ?? []),
            'items' => !empty($dbSection['items']) ? (is_array($dbSection['items']) ? $dbSection['items'] : (json_decode($dbSection['items'], true) ?: [])) : ($default['items'] ?? [])
        ];
        echo $themeLoader->renderComponent($sectionId, ['section' => $section]);
    }
}
