<?php
/**
 * Real Estate Theme - Tema Özelleştirici
 * Real Estate temasına özel customize sayfası
 */

$theme = $theme ?? [];
$settings = $settings ?? [];
$themeSlug = $theme['slug'] ?? 'realestate';
$previewUrl = admin_url('themes/preview/' . $themeSlug);

// Sayfa bölümlerini al
$pageSections = [];
$contactPageSections = [];
if (isset($themeManager)) {
    try {
        // Aktif temanın ID'sini al
        $activeTheme = $themeManager->getActiveTheme();
        $themeId = $activeTheme['id'] ?? null;
        
        // Ana sayfa bölümleri
        $sections = $themeManager->getPageSections('home', $themeId) ?? [];
        foreach ($sections as $section) {
            $sectionId = $section['section_id'] ?? '';
            if ($sectionId) {
                $sectionSettings = [];
                if (isset($section['settings'])) {
                    if (is_array($section['settings'])) {
                        $sectionSettings = $section['settings'];
                    } else {
                        $decoded = json_decode($section['settings'], true);
                        $sectionSettings = is_array($decoded) ? $decoded : [];
                    }
                }
                
                $pageSections[$sectionId] = array_merge(
                    $sectionSettings,
                    ['enabled' => ($section['is_active'] ?? 1) == 1]
                );
                $pageSections[$sectionId]['title'] = $section['title'] ?? '';
                $pageSections[$sectionId]['subtitle'] = $section['subtitle'] ?? '';
                $pageSections[$sectionId]['content'] = $section['content'] ?? '';
                if (isset($section['items'])) {
                    $items = is_array($section['items']) ? $section['items'] : json_decode($section['items'] ?? '[]', true);
                    $pageSections[$sectionId]['items'] = is_array($items) ? $items : [];
                }
            }
        }
        
        // İletişim sayfası bölümleri
        $contactSections = $themeManager->getPageSections('contact', $themeId) ?? [];
        foreach ($contactSections as $section) {
            $sectionId = $section['section_id'] ?? '';
            if ($sectionId) {
                $sectionSettings = [];
                if (isset($section['settings'])) {
                    if (is_array($section['settings'])) {
                        $sectionSettings = $section['settings'];
                    } else {
                        $decoded = json_decode($section['settings'], true);
                        $sectionSettings = is_array($decoded) ? $decoded : [];
                    }
                }
                
                $contactPageSections[$sectionId] = array_merge(
                    $sectionSettings,
                    ['enabled' => ($section['is_active'] ?? 1) == 1]
                );
                $contactPageSections[$sectionId]['title'] = $section['title'] ?? '';
                $contactPageSections[$sectionId]['subtitle'] = $section['subtitle'] ?? '';
                $contactPageSections[$sectionId]['content'] = $section['content'] ?? '';
                if (isset($section['items'])) {
                    $items = is_array($section['items']) ? $section['items'] : json_decode($section['items'] ?? '[]', true);
                    $contactPageSections[$sectionId]['items'] = is_array($items) ? $items : [];
                }
            }
        }
    } catch (Exception $e) {
        error_log("Real Estate customize page sections error: " . $e->getMessage());
    }
}

// Mevcut ayarları al
$currentLogo = $settings['branding']['site_logo']['value'] ?? '';
$currentFavicon = $settings['branding']['site_favicon']['value'] ?? '';

// Formları getir
$availableForms = [];
try {
    if (class_exists('Database')) {
        $db = Database::getInstance();
        $sql = "SELECT id, name, slug, status FROM `forms` WHERE `status` = 'active' ORDER BY `name` ASC";
        $forms = $db->fetchAll($sql);
        if (!$forms || !is_array($forms)) {
            $sql = "SELECT id, name, slug, status FROM `forms` ORDER BY `name` ASC";
            $forms = $db->fetchAll($sql);
        }
        $availableForms = $forms ?: [];
    }
} catch (Exception $e) {
    error_log('Forms load error in customize: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tema Özelleştirici - <?php echo esc_html($theme['name'] ?? 'Real Estate Theme'); ?></title>
    <script src="<?php echo ViewRenderer::assetUrl('assets/js/tailwind-admin.min.js'); ?>"></script>
    <link rel="stylesheet" href="<?php echo ViewRenderer::assetUrl('assets/css/fonts.css'); ?>">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: { 50: '#eff6ff', 100: '#dbeafe', 500: '#1e40af', 600: '#1e3a8a', 700: '#1e293b' }
                    }
                }
            }
        }
    </script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255,255,255,0.03); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.08); }
        .section-panel { 
            max-height: 0; 
            overflow: hidden; 
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease;
            opacity: 0;
        }
        .section-panel.open { 
            max-height: 5000px; 
            opacity: 1;
        }
        .section-btn.active { background: linear-gradient(135deg, rgba(30,64,175,0.15) 0%, rgba(30,41,59,0.1) 100%); border-color: rgba(30,64,175,0.3); }
        .section-btn.active .section-icon { background: linear-gradient(135deg, #1e40af 0%, #1e293b 100%); color: white; }
        input[type="color"] { 
            -webkit-appearance: none; 
            border: none; 
            cursor: pointer;
            width: 40px;
            height: 40px;
        }
        input[type="color"]::-webkit-color-swatch-wrapper { padding: 0; }
        input[type="color"]::-webkit-color-swatch { border: none; border-radius: 8px; }
        .scrollbar-thin::-webkit-scrollbar { width: 5px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
        .input-field { 
            background: rgba(255,255,255,0.05) !important; 
            border: 1px solid rgba(255,255,255,0.1) !important; 
            color: white !important;
            transition: all 0.2s; 
        }
        .input-field::placeholder {
            color: rgba(255,255,255,0.4) !important;
        }
        .input-field:focus { 
            background: rgba(255,255,255,0.08) !important; 
            border-color: rgba(30,64,175,0.5) !important; 
            outline: none; 
            box-shadow: 0 0 0 3px rgba(30,64,175,0.1); 
        }
        .input-field option {
            background: #1e293b !important;
            color: white !important;
        }
        select.input-field {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23ffffff' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            padding-right: 2.5rem;
        }
        .btn-primary { background: linear-gradient(135deg, #1e40af 0%, #1e293b 100%); }
        .btn-primary:hover { background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%); transform: translateY(-1px); box-shadow: 0 10px 40px -10px rgba(30,64,175,0.5); }
        
        /* Media Picker Override */
        #media-picker-modal { z-index: 999999 !important; }
        #media-picker-modal > div:last-child { 
            background: #1e293b !important; 
            color: white !important;
        }
        #media-picker-modal .bg-gray-50 { background: rgba(255,255,255,0.05) !important; }
        #media-picker-modal .bg-white { background: #1e293b !important; }
        #media-picker-modal .text-gray-900 { color: white !important; }
        #media-picker-modal .text-gray-700 { color: #cbd5e1 !important; }
        #media-picker-modal .border-gray-200 { border-color: rgba(255,255,255,0.1) !important; }
    </style>
    <script>
    // Section Toggle
    window.toggleSection = function(sectionId) {
        const panel = document.getElementById(sectionId + '-panel');
        const btn = document.querySelector('[data-section="' + sectionId + '"]');
        
        if (!panel || !btn) return;
        
        const isOpen = panel.classList.contains('open');
        
        // Close all
        document.querySelectorAll('.section-panel').forEach(p => p.classList.remove('open'));
        document.querySelectorAll('.section-btn').forEach(b => {
            b.classList.remove('active');
            const arrow = b.querySelector('.section-arrow');
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        });
        
        // Open clicked
        if (!isOpen) {
            panel.classList.add('open');
            btn.classList.add('active');
            const arrow = btn.querySelector('.section-arrow');
            if (arrow) arrow.style.transform = 'rotate(180deg)';
        }
    };
    </script>
</head>
<body class="bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white overflow-hidden">

<div class="flex h-screen">
    
    <!-- Sidebar -->
    <aside class="w-[380px] flex flex-col border-r border-white/5">
        
        <!-- Header -->
        <header class="p-5 border-b border-white/5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="<?php echo admin_url('themes'); ?>" class="w-10 h-10 flex items-center justify-center rounded-xl glass hover:bg-white/10 transition-all">
                        <span class="material-symbols-outlined text-xl">arrow_back</span>
                    </a>
                    <div>
                        <h1 class="text-lg font-semibold">Tema Özelleştirici</h1>
                        <p class="text-xs text-slate-400"><?php echo esc_html($theme['name'] ?? 'Real Estate Theme'); ?> - Real Estate Özel Sayfa</p>
                    </div>
                </div>
                <button id="saveBtn" onclick="saveSettings()" class="px-5 py-2.5 btn-primary text-white text-sm font-semibold rounded-xl transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">save</span>
                    Kaydet
                </button>
            </div>
        </header>
        
        <!-- Sections Navigation -->
        <div class="flex-1 overflow-y-auto scrollbar-thin">
            
            <!-- Marka & Logo -->
            <div class="border-b border-white/5">
                <button onclick="window.toggleSection('branding')" class="section-btn active w-full p-4 flex items-center gap-4 hover:bg-white/5 transition-all" data-section="branding">
                    <div class="section-icon w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-slate-800 flex items-center justify-center">
                        <span class="material-symbols-outlined text-white">brush</span>
                    </div>
                    <div class="flex-1 text-left">
                        <span class="text-sm font-semibold block">Marka & Logo</span>
                        <span class="text-xs text-slate-400">Logo, favicon ve site kimliği</span>
                    </div>
                    <span class="material-symbols-outlined text-slate-400 section-arrow transition-transform">expand_more</span>
                </button>
                <div id="branding-panel" class="section-panel open">
                    <div class="px-4 pb-5 space-y-5">
                        <!-- Logo -->
                        <div class="glass rounded-xl p-4">
                            <label class="block text-xs font-medium text-slate-300 mb-3">Site Logo</label>
                            <div class="flex items-center gap-4">
                                <div id="logoPreview" class="w-20 h-20 rounded-xl bg-slate-800/50 border-2 border-dashed border-slate-600 flex items-center justify-center overflow-hidden hover:border-blue-500/50 transition-colors cursor-pointer" onclick="selectLogo()">
                                    <?php if ($currentLogo): ?>
                                    <img src="<?php echo esc_url($currentLogo); ?>" class="w-full h-full object-contain">
                                    <?php else: ?>
                                    <span class="material-symbols-outlined text-3xl text-slate-500">add_photo_alternate</span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1 space-y-2">
                                    <button onclick="selectLogo()" class="w-full px-4 py-2 text-xs font-medium bg-blue-500/20 text-blue-400 rounded-lg hover:bg-blue-500/30 transition-colors">
                                        Logo Seç
                                    </button>
                                    <button onclick="removeLogo()" class="w-full px-4 py-2 text-xs font-medium text-slate-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors">
                                        Kaldır
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" id="siteLogo" name="branding[site_logo]" value="<?php echo esc_attr($currentLogo); ?>">
                        </div>
                        
                        <!-- Favicon -->
                        <div class="glass rounded-xl p-4">
                            <label class="block text-xs font-medium text-slate-300 mb-3">Favicon</label>
                            <div class="flex items-center gap-4">
                                <div id="faviconPreview" class="w-14 h-14 rounded-xl bg-slate-800/50 border-2 border-dashed border-slate-600 flex items-center justify-center overflow-hidden hover:border-blue-500/50 transition-colors cursor-pointer" onclick="selectFavicon()">
                                    <?php if ($currentFavicon): ?>
                                    <img src="<?php echo esc_url($currentFavicon); ?>" class="w-full h-full object-contain">
                                    <?php else: ?>
                                    <span class="material-symbols-outlined text-2xl text-slate-500">add_photo_alternate</span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1 space-y-2">
                                    <button onclick="selectFavicon()" class="w-full px-4 py-2 text-xs font-medium bg-blue-500/20 text-blue-400 rounded-lg hover:bg-blue-500/30 transition-colors">
                                        Favicon Seç
                                    </button>
                                    <button onclick="removeFavicon()" class="w-full px-4 py-2 text-xs font-medium text-slate-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors">
                                        Kaldır
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" id="siteFavicon" name="branding[site_favicon]" value="<?php echo esc_attr($currentFavicon); ?>">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Renkler -->
            <div class="border-b border-white/5">
                <button onclick="window.toggleSection('colors')" class="section-btn w-full p-4 flex items-center gap-4 hover:bg-white/5 transition-all" data-section="colors">
                    <div class="section-icon w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-cyan-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-white">palette</span>
                    </div>
                    <div class="flex-1 text-left">
                        <span class="text-sm font-semibold block">Tema Renkleri</span>
                        <span class="text-xs text-slate-400">Tema renk paletini özelleştirin</span>
                    </div>
                    <span class="material-symbols-outlined text-slate-400 section-arrow transition-transform">expand_more</span>
                </button>
                <div id="colors-panel" class="section-panel">
                    <div class="px-4 pb-5 space-y-4">
                        <?php
                        // Renk tanımları ve açıklamaları
                        $colorDefinitions = [
                            'primary' => ['label' => 'Birincil Renk', 'desc' => 'Butonlar, linkler ve vurgu öğeleri için kullanılır', 'icon' => 'star'],
                            'secondary' => ['label' => 'İkincil Renk', 'desc' => 'İkincil butonlar ve arka planlar için', 'icon' => 'circle'],
                            'accent' => ['label' => 'Vurgu Rengi', 'desc' => 'Özel vurgular ve hover efektleri için', 'icon' => 'flash_on'],
                            'background' => ['label' => 'Arka Plan Rengi', 'desc' => 'Sayfa ana arka plan rengi', 'icon' => 'wallpaper'],
                            'surface' => ['label' => 'Yüzey Rengi', 'desc' => 'Kartlar ve yüzeyler için arka plan', 'icon' => 'layers'],
                            'text' => ['label' => 'Metin Rengi', 'desc' => 'Ana metin rengi', 'icon' => 'text_fields'],
                            'text_muted' => ['label' => 'Soluk Metin Rengi', 'desc' => 'İkincil metinler ve açıklamalar için', 'icon' => 'format_color_text']
                        ];
                        
                        // Theme.json'dan renk ayarlarını al
                        $themeColors = $theme['settings_schema']['settings']['colors'] ?? [];
                        
                        // Eğer settings'te renkler yoksa, theme.json'dan al
                        if (empty($settings['colors']) && !empty($themeColors)) {
                            foreach ($themeColors as $key => $colorConfig) {
                                $settings['colors'][$key] = [
                                    'label' => $colorConfig['label'] ?? $colorDefinitions[$key]['label'] ?? ucfirst($key),
                                    'default' => $colorConfig['default'] ?? '#000000',
                                    'value' => $colorConfig['default'] ?? '#000000'
                                ];
                            }
                        }
                        
                        // Renkleri göster
                        if (!empty($settings['colors']) || !empty($themeColors)):
                        ?>
                        <div class="glass rounded-xl p-4 space-y-5">
                            <div class="mb-4">
                                <h3 class="text-sm font-semibold text-slate-200 mb-1">Renk Paleti</h3>
                                <p class="text-xs text-slate-400">Temanızın renk şemasını özelleştirin. Değişiklikler anında önizlemede görünecektir.</p>
                            </div>
                            
                            <?php 
                            $colorsToShow = !empty($settings['colors']) ? $settings['colors'] : $themeColors;
                            foreach ($colorsToShow as $key => $config): 
                                $colorValue = $config['value'] ?? $config['default'] ?? '#000000';
                                $colorLabel = $colorDefinitions[$key]['label'] ?? $config['label'] ?? ucfirst($key);
                                $colorDesc = $colorDefinitions[$key]['desc'] ?? '';
                                $colorIcon = $colorDefinitions[$key]['icon'] ?? 'palette';
                            ?>
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="material-symbols-outlined text-sm text-slate-400"><?php echo $colorIcon; ?></span>
                                    <label class="text-sm font-medium text-slate-300"><?php echo esc_html($colorLabel); ?></label>
                                </div>
                                <?php if ($colorDesc): ?>
                                <p class="text-xs text-slate-500 mb-2"><?php echo esc_html($colorDesc); ?></p>
                                <?php endif; ?>
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <input 
                                            type="color" 
                                            name="colors[<?php echo esc_attr($key); ?>]" 
                                            id="color_<?php echo esc_attr($key); ?>"
                                            value="<?php echo esc_attr($colorValue); ?>" 
                                            class="w-14 h-14 rounded-xl cursor-pointer border-2 border-white/10 hover:border-white/20 transition-colors"
                                            oninput="updateColorText('<?php echo esc_js($key); ?>', this.value)"
                                            style="background: <?php echo esc_attr($colorValue); ?>"
                                        >
                                        <div class="absolute inset-0 rounded-xl pointer-events-none" style="background: <?php echo esc_attr($colorValue); ?>; box-shadow: 0 0 0 2px rgba(255,255,255,0.1) inset;"></div>
                                    </div>
                                    <input 
                                        type="text" 
                                        id="color_text_<?php echo esc_attr($key); ?>"
                                        value="<?php echo esc_attr($colorValue); ?>" 
                                        class="flex-1 px-4 py-2.5 input-field rounded-lg text-sm font-mono" 
                                        placeholder="#000000"
                                        oninput="updateColorPicker('<?php echo esc_js($key); ?>', this.value)"
                                        pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
                                    >
                                    <button 
                                        type="button"
                                        onclick="resetColor('<?php echo esc_js($key); ?>', '<?php echo esc_js($config['default'] ?? '#000000'); ?>')"
                                        class="px-3 py-2.5 text-xs font-medium text-slate-400 hover:text-slate-200 hover:bg-white/5 rounded-lg transition-colors"
                                        title="Varsayılan değere sıfırla"
                                    >
                                        <span class="material-symbols-outlined text-base">refresh</span>
                                    </button>
                                </div>
                                <!-- Renk Önizlemesi -->
                                <div class="mt-2 p-3 rounded-lg border border-white/5" style="background: linear-gradient(135deg, <?php echo esc_attr($colorValue); ?>15 0%, <?php echo esc_attr($colorValue); ?>05 100%);">
                                    <div class="flex items-center gap-2 text-xs">
                                        <div class="w-3 h-3 rounded-full" style="background: <?php echo esc_attr($colorValue); ?>"></div>
                                        <span class="text-slate-400">Önizleme: </span>
                                        <span class="font-mono text-slate-300"><?php echo esc_html($colorValue); ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <!-- Hızlı Renk Şemaları -->
                            <div class="mt-6 pt-5 border-t border-white/5">
                                <label class="block text-xs font-medium text-slate-300 mb-3">Hızlı Renk Şemaları</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <?php
                                    $colorSchemes = [
                                        ['name' => 'Mavi', 'primary' => '#1e40af', 'secondary' => '#1e293b', 'accent' => '#0ea5e9'],
                                        ['name' => 'Yeşil', 'primary' => '#059669', 'secondary' => '#064e3b', 'accent' => '#10b981'],
                                        ['name' => 'Mor', 'primary' => '#7c3aed', 'secondary' => '#4c1d95', 'accent' => '#a78bfa'],
                                        ['name' => 'Kırmızı', 'primary' => '#dc2626', 'secondary' => '#991b1b', 'accent' => '#ef4444'],
                                    ];
                                    foreach ($colorSchemes as $scheme):
                                    ?>
                                    <button 
                                        type="button"
                                        onclick="applyColorScheme(<?php echo htmlspecialchars(json_encode($scheme), ENT_QUOTES, 'UTF-8'); ?>)"
                                        class="p-3 rounded-lg border border-white/5 hover:border-white/20 hover:bg-white/5 transition-all text-left group"
                                    >
                                        <div class="flex items-center gap-2 mb-2">
                                            <div class="flex gap-1">
                                                <div class="w-4 h-4 rounded" style="background: <?php echo esc_attr($scheme['primary']); ?>"></div>
                                                <div class="w-4 h-4 rounded" style="background: <?php echo esc_attr($scheme['secondary']); ?>"></div>
                                                <div class="w-4 h-4 rounded" style="background: <?php echo esc_attr($scheme['accent']); ?>"></div>
                                            </div>
                                            <span class="text-xs font-medium text-slate-300 group-hover:text-white"><?php echo esc_html($scheme['name']); ?></span>
                                        </div>
                                    </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="glass rounded-xl p-4 text-center py-8">
                            <span class="material-symbols-outlined text-4xl text-slate-600 mb-2">palette</span>
                            <p class="text-sm text-slate-400">Renk ayarları bulunamadı</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Tipografi -->
            <?php 
            // Font ayarlarını kontrol et - eğer settings'te yoksa theme.json'dan al
            $fontSettings = $settings['fonts'] ?? [];
            if (empty($fontSettings) && isset($theme['settings_schema']['settings']['fonts'])) {
                $fontSettings = $theme['settings_schema']['settings']['fonts'];
            }
            // Eğer hala boşsa varsayılan değerleri oluştur
            if (empty($fontSettings)) {
                $fontSettings = [
                    'heading' => [
                        'label' => 'Heading Font',
                        'default' => 'Zalando Sans SemiExpanded',
                        'type' => 'font',
                        'value' => 'Zalando Sans SemiExpanded'
                    ],
                    'body' => [
                        'label' => 'Body Font',
                        'default' => 'Zalando Sans SemiExpanded',
                        'type' => 'font',
                        'value' => 'Zalando Sans SemiExpanded'
                    ]
                ];
            }
            ?>
            <?php if (!empty($fontSettings)): ?>
            <div class="border-b border-white/5">
                <button onclick="window.toggleSection('fonts')" class="section-btn w-full p-4 flex items-center gap-4 hover:bg-white/5 transition-all" data-section="fonts">
                    <div class="section-icon w-10 h-10 rounded-xl bg-slate-700/50 flex items-center justify-center">
                        <span class="material-symbols-outlined">text_fields</span>
                    </div>
                    <div class="flex-1 text-left">
                        <span class="text-sm font-semibold block">Tipografi</span>
                        <span class="text-xs text-slate-400">Yazı tipleri</span>
                    </div>
                    <span class="material-symbols-outlined text-slate-400 section-arrow transition-transform">expand_more</span>
                </button>
                <div id="fonts-panel" class="section-panel">
                    <div class="px-4 pb-5">
                        <div class="glass rounded-xl p-4 space-y-4">
                            <?php 
                            // ThemeManager'dan gelen kullanılabilir fontları kullan
                            $fonts = $availableFonts ?? [];
                            if (empty($fonts) && isset($themeManager)) {
                                $fonts = $themeManager->getAvailableFonts();
                            }
                            if (empty($fonts)) {
                                $fonts = ['Zalando Sans SemiExpanded', 'Inter', 'Poppins', 'Roboto', 'Open Sans', 'Lato', 'Montserrat', 'Raleway', 'Nunito', 'DM Sans'];
                            }
                            foreach ($fontSettings as $key => $config): 
                                $configValue = is_array($config) ? ($config['value'] ?? $config['default'] ?? '') : $config;
                                $configLabel = is_array($config) ? ($config['label'] ?? ucfirst($key)) : ucfirst($key);
                            ?>
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-2"><?php echo esc_html($configLabel); ?></label>
                                <select name="fonts[<?php echo esc_attr($key); ?>]" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                    <?php foreach ($fonts as $fontKey => $fontName): 
                                        // $fonts array'i key-value çifti olabilir veya sadece değerler olabilir
                                        $fontValue = is_numeric($fontKey) ? $fontName : $fontKey;
                                        $fontDisplay = is_numeric($fontKey) ? $fontName : $fontName;
                                        $isSelected = ($configValue === $fontValue);
                                    ?>
                                    <option value="<?php echo esc_attr($fontValue); ?>" <?php echo $isSelected ? 'selected' : ''; ?> style="font-family: <?php echo esc_attr($fontDisplay); ?>"><?php echo esc_html($fontDisplay); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Header Ayarları -->
            <?php if (!empty($settings['header'])): ?>
            <div class="border-b border-white/5">
                <button onclick="window.toggleSection('header')" class="section-btn w-full p-4 flex items-center gap-4 hover:bg-white/5 transition-all" data-section="header">
                    <div class="section-icon w-10 h-10 rounded-xl bg-slate-700/50 flex items-center justify-center">
                        <span class="material-symbols-outlined">web_asset</span>
                    </div>
                    <div class="flex-1 text-left">
                        <span class="text-sm font-semibold block">Header</span>
                        <span class="text-xs text-slate-400">Üst menü ve navigasyon</span>
                    </div>
                    <span class="material-symbols-outlined text-slate-400 section-arrow transition-transform">expand_more</span>
                </button>
                <div id="header-panel" class="section-panel">
                    <div class="px-4 pb-5 space-y-4">
                        <div class="glass rounded-xl p-4 space-y-4">
                            <?php if (isset($settings['header']['style'])): ?>
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-2">Header Stili</label>
                                <select name="header[style]" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                    <?php 
                                    $headerStyles = ['fixed', 'static', 'transparent'];
                                    $currentStyle = $settings['header']['style']['value'] ?? $settings['header']['style']['default'] ?? 'fixed';
                                    foreach ($headerStyles as $style): 
                                    ?>
                                    <option value="<?php echo $style; ?>" <?php echo $currentStyle === $style ? 'selected' : ''; ?>><?php echo ucfirst($style); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($settings['header']['bg_color'])): ?>
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-2">Header Arka Plan Rengi</label>
                                <div class="flex items-center gap-3">
                                    <input type="color" value="<?php echo esc_attr($settings['header']['bg_color']['value'] ?? $settings['header']['bg_color']['default'] ?? '#ffffff'); ?>" class="w-10 h-10 rounded-lg" oninput="document.getElementById('header_bg_color').value = this.value">
                                    <input type="text" name="header[bg_color]" id="header_bg_color" value="<?php echo esc_attr($settings['header']['bg_color']['value'] ?? $settings['header']['bg_color']['default'] ?? '#ffffff'); ?>" class="flex-1 px-4 py-2.5 input-field rounded-lg text-sm font-mono" oninput="this.previousElementSibling.value = this.value">
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($settings['header']['text_color'])): ?>
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-2">Header Metin Rengi</label>
                                <div class="flex items-center gap-3">
                                    <input type="color" value="<?php echo esc_attr($settings['header']['text_color']['value'] ?? $settings['header']['text_color']['default'] ?? '#1e293b'); ?>" class="w-10 h-10 rounded-lg" oninput="document.getElementById('header_text_color').value = this.value">
                                    <input type="text" name="header[text_color]" id="header_text_color" value="<?php echo esc_attr($settings['header']['text_color']['value'] ?? $settings['header']['text_color']['default'] ?? '#1e293b'); ?>" class="flex-1 px-4 py-2.5 input-field rounded-lg text-sm font-mono" oninput="this.previousElementSibling.value = this.value">
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($settings['header']['show_search'])): ?>
                            <label class="flex items-center gap-3 cursor-pointer p-3 rounded-lg hover:bg-white/5 transition-colors">
                                <input type="checkbox" name="header[show_search]" value="1" <?php echo ($settings['header']['show_search']['value'] ?? $settings['header']['show_search']['default'] ?? true) ? 'checked' : ''; ?> class="w-5 h-5 rounded bg-slate-700 border-slate-600 text-blue-500 focus:ring-blue-500/30">
                                <span class="text-sm">Arama butonunu göster</span>
                            </label>
                            <?php endif; ?>
                            
                            <?php if (isset($settings['header']['show_cta'])): ?>
                            <label class="flex items-center gap-3 cursor-pointer p-3 rounded-lg hover:bg-white/5 transition-colors">
                                <input type="checkbox" name="header[show_cta]" value="1" <?php echo ($settings['header']['show_cta']['value'] ?? $settings['header']['show_cta']['default'] ?? true) ? 'checked' : ''; ?> class="w-5 h-5 rounded bg-slate-700 border-slate-600 text-blue-500 focus:ring-blue-500/30">
                                <span class="text-sm">CTA butonunu göster</span>
                            </label>
                            <?php endif; ?>
                            
                            <?php if (isset($settings['header']['cta_text'])): ?>
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-2">CTA Buton Metni</label>
                                <input type="text" name="header[cta_text]" value="<?php echo esc_attr($settings['header']['cta_text']['value'] ?? $settings['header']['cta_text']['default'] ?? 'List Your Property'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($settings['header']['cta_link'])): ?>
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-2">CTA Buton Linki</label>
                                <input type="text" name="header[cta_link]" value="<?php echo esc_attr($settings['header']['cta_link']['value'] ?? $settings['header']['cta_link']['default'] ?? '/contact'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Ana Sayfa Bölümleri -->
            <div class="border-b border-white/5">
                <button onclick="window.toggleSection('homepage')" class="section-btn w-full p-4 flex items-center gap-4 hover:bg-white/5 transition-all" data-section="homepage">
                    <div class="section-icon w-10 h-10 rounded-xl bg-slate-700/50 flex items-center justify-center">
                        <span class="material-symbols-outlined">home</span>
                    </div>
                    <div class="flex-1 text-left">
                        <span class="text-sm font-semibold block">Ana Sayfa</span>
                        <span class="text-xs text-slate-400">Hero, özellikler, hakkımızda</span>
                    </div>
                    <span class="material-symbols-outlined text-slate-400 section-arrow transition-transform">expand_more</span>
                </button>
                <div id="homepage-panel" class="section-panel">
                    <div class="px-4 pb-5 space-y-3">
                        
                        <!-- Hero -->
                        <details class="glass rounded-xl overflow-hidden group">
                            <summary class="flex items-center justify-between p-4 cursor-pointer hover:bg-white/5 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-sm">🚀</span>
                                    <span class="text-sm font-medium">Hero Bölümü</span>
                                </div>
                                <span class="material-symbols-outlined text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
                            </summary>
                            <div class="p-4 pt-0 space-y-3 border-t border-white/5">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Hero Başlık</label>
                                    <input type="text" name="sections[hero][title]" value="<?php echo esc_attr($pageSections['hero']['title'] ?? 'Hayalinizdeki Evini Bugün Bulun'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Hero Alt Başlık</label>
                                    <textarea name="sections[hero][subtitle]" rows="3" class="w-full px-4 py-2.5 input-field rounded-lg text-sm resize-none"><?php echo esc_html($pageSections['hero']['subtitle'] ?? 'Geniş gayrimenkul koleksiyonumuzdan premium evler, daireler ve ticari alanlar arasından mükemmel mülkü keşfedin.'); ?></textarea>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1.5">Birincil Buton Metni</label>
                                        <input type="text" name="sections[hero][button_text]" value="<?php echo esc_attr($pageSections['hero']['button_text'] ?? 'Mülkleri İncele'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1.5">Birincil Buton Linki</label>
                                        <input type="text" name="sections[hero][button_link]" value="<?php echo esc_attr($pageSections['hero']['button_link'] ?? '/ilanlar'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1.5">İkincil Buton Metni</label>
                                        <input type="text" name="sections[hero][secondary_button_text]" value="<?php echo esc_attr($pageSections['hero']['secondary_button_text'] ?? 'Tur Planla'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1.5">İkincil Buton Linki</label>
                                        <input type="text" name="sections[hero][secondary_button_link]" value="<?php echo esc_attr($pageSections['hero']['secondary_button_link'] ?? '/contact'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                    </div>
                                </div>
                                <div class="p-3 rounded-lg bg-blue-500/10 border border-blue-500/20">
                                    <p class="text-xs text-blue-300 mb-1 flex items-center gap-2">
                                        <span class="material-symbols-outlined text-sm">info</span>
                                        <span class="font-semibold">Buton Renkleri</span>
                                    </p>
                                    <p class="text-xs text-slate-400">Hero butonları otomatik olarak tema renk paletindeki <strong>Birincil Renk</strong> ve <strong>İkincil Renk</strong> ayarlarından alınır. Renkleri değiştirmek için yukarıdaki "Tema Renkleri" bölümünü kullanın.</p>
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Hero Arka Plan Görseli</label>
                                    <div class="flex items-center gap-3">
                                        <input type="text" name="sections[hero][hero_image]" id="hero_image_section" value="<?php echo esc_attr($pageSections['hero']['hero_image'] ?? ''); ?>" class="flex-1 px-4 py-2.5 input-field rounded-lg text-sm">
                                        <button type="button" onclick="selectHeroImageSection()" class="px-4 py-2.5 bg-blue-500/20 text-blue-400 rounded-lg hover:bg-blue-500/30 transition-colors text-sm">
                                            Seç
                                        </button>
                                    </div>
                                </div>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="sections[hero][enabled]" value="1" <?php echo ($pageSections['hero']['enabled'] ?? true) ? 'checked' : ''; ?> class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500">
                                    <span class="text-xs text-slate-400">Bu bölümü göster</span>
                                </label>
                            </div>
                        </details>
                        
                        <!-- Featured Listings -->
                        <details class="glass rounded-xl overflow-hidden group">
                            <summary class="flex items-center justify-between p-4 cursor-pointer hover:bg-white/5 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-sm">🏠</span>
                                    <span class="text-sm font-medium">İlanlar Bölümü</span>
                                </div>
                                <span class="material-symbols-outlined text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
                            </summary>
                            <div class="p-4 pt-0 space-y-3 border-t border-white/5">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">İlanlar Başlık</label>
                                    <input type="text" name="sections[featured-listings][title]" value="<?php echo esc_attr($pageSections['featured-listings']['title'] ?? 'Öne Çıkan İlanlar'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">İlanlar Alt Başlık</label>
                                    <textarea name="sections[featured-listings][subtitle]" rows="2" class="w-full px-4 py-2.5 input-field rounded-lg text-sm resize-none"><?php echo esc_html($pageSections['featured-listings']['subtitle'] ?? 'Özenle seçilmiş premium mülk koleksiyonumuzu keşfedin'); ?></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Gösterilecek İlan Sayısı</label>
                                    <input type="number" name="sections[featured-listings][settings][limit]" value="<?php echo esc_attr($pageSections['featured-listings']['settings']['limit'] ?? 6); ?>" min="1" max="12" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                    <p class="text-xs text-slate-500 mt-1">Ana sayfada gösterilecek öne çıkan ilan sayısı (1-12 arası)</p>
                                </div>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="sections[featured-listings][enabled]" value="1" <?php echo ($pageSections['featured-listings']['enabled'] ?? true) ? 'checked' : ''; ?> class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500">
                                    <span class="text-xs text-slate-400">Bu bölümü göster</span>
                                </label>
                            </div>
                        </details>
                        
                        <!-- Danışmanlar Bölümü -->
                        <details class="glass rounded-xl overflow-hidden group">
                            <summary class="flex items-center justify-between p-4 cursor-pointer hover:bg-white/5 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center text-sm">👥</span>
                                    <span class="text-sm font-medium">Danışmanlar Bölümü</span>
                                </div>
                                <span class="material-symbols-outlined text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
                            </summary>
                            <div class="p-4 pt-0 space-y-3 border-t border-white/5">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Danışmanlar Başlık</label>
                                    <input type="text" name="sections[consultants][title]" value="<?php echo esc_attr($pageSections['consultants']['title'] ?? 'Uzman Danışmanlarımız'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Danışmanlar Alt Başlık</label>
                                    <textarea name="sections[consultants][subtitle]" rows="2" class="w-full px-4 py-2.5 input-field rounded-lg text-sm resize-none"><?php echo esc_html($pageSections['consultants']['subtitle'] ?? 'Size en uygun emlağı bulmanızda yardımcı olacak profesyonel ekibimizle tanışın'); ?></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Gösterilecek Danışman Sayısı</label>
                                    <input type="number" name="sections[consultants][settings][limit]" value="<?php echo esc_attr($pageSections['consultants']['settings']['limit'] ?? 6); ?>" min="1" max="12" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                    <p class="text-xs text-slate-500 mt-1">Ana sayfada gösterilecek öne çıkan danışman sayısı (1-12 arası)</p>
                                </div>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="sections[consultants][enabled]" value="1" <?php echo ($pageSections['consultants']['enabled'] ?? true) ? 'checked' : ''; ?> class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500">
                                    <span class="text-xs text-slate-400">Bu bölümü göster</span>
                                </label>
                            </div>
                        </details>
                        
                        <!-- Neden Bizi Seçmelisiniz -->
                        <details class="glass rounded-xl overflow-hidden group">
                            <summary class="flex items-center justify-between p-4 cursor-pointer hover:bg-white/5 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center text-sm">⭐</span>
                                    <span class="text-sm font-medium">Neden Bizi Seçmelisiniz</span>
                                </div>
                                <span class="material-symbols-outlined text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
                            </summary>
                            <div class="p-4 pt-0 space-y-3 border-t border-white/5">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Bölüm Başlık</label>
                                    <input type="text" name="sections[why-choose-us][title]" value="<?php echo esc_attr($pageSections['why-choose-us']['title'] ?? 'Neden Bizi Seçmelisiniz'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Bölüm Alt Başlık</label>
                                    <textarea name="sections[why-choose-us][subtitle]" rows="2" class="w-full px-4 py-2.5 input-field rounded-lg text-sm resize-none"><?php echo esc_html($pageSections['why-choose-us']['subtitle'] ?? 'Hayalinizdeki mülkü bulmanızı kolay ve stressiz hale getiriyoruz'); ?></textarea>
                                </div>
                                
                                <!-- Why Choose Us Items -->
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <label class="block text-xs font-medium text-slate-300">Özellikler</label>
                                        <button type="button" onclick="addWhyChooseItem()" class="px-3 py-1.5 text-xs font-medium bg-indigo-500/20 text-indigo-400 rounded-lg hover:bg-indigo-500/30 transition-colors flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-sm">add</span>
                                            Özellik Ekle
                                        </button>
                                    </div>
                                    <div id="why-choose-items" class="space-y-3">
                                        <?php 
                                        $whyChooseItems = $pageSections['why-choose-us']['items'] ?? [];
                                        if (empty($whyChooseItems)) {
                                            // Varsayılan özellikler
                                            $whyChooseItems = [
                                                [
                                                    'icon' => 'shield',
                                                    'title' => 'Güvenilir & Deneyimli',
                                                    'description' => 'Gayrimenkul piyasasında yılların deneyimi ve başarılı işlemlerle kanıtlanmış bir geçmiş.'
                                                ],
                                                [
                                                    'icon' => 'home',
                                                    'title' => 'Geniş Seçenek',
                                                    'description' => 'Tüm fiyat aralıkları ve lokasyonlarda binlerce mülke erişim.'
                                                ],
                                                [
                                                    'icon' => 'users',
                                                    'title' => 'Uzman Ekip',
                                                    'description' => 'Profesyonel ekibimiz, mükemmel mülkünüzü bulmanız için size adanmış.'
                                                ],
                                                [
                                                    'icon' => 'clock',
                                                    'title' => '7/24 Destek',
                                                    'description' => 'Sorularınızı yanıtlamak ve süreçte size rehberlik etmek için kesintisiz yardım.'
                                                ],
                                                [
                                                    'icon' => 'dollar',
                                                    'title' => 'En İyi Fiyatlar',
                                                    'description' => 'Rekabetçi fiyatlandırma ve gizli maliyet olmadan şeffaf ücretler.'
                                                ],
                                                [
                                                    'icon' => 'document',
                                                    'title' => 'Kolay Süreç',
                                                    'description' => 'Gayrimenkul yolculuğunuzu sorunsuz hale getirmek için sadeleştirilmiş prosedürler.'
                                                ]
                                            ];
                                        }
                                        foreach ($whyChooseItems as $index => $item): 
                                            $itemTitle = $item['title'] ?? '';
                                            $itemDescription = $item['description'] ?? '';
                                            $itemIcon = $item['icon'] ?? 'home';
                                        ?>
                                        <div class="why-choose-item glass rounded-lg p-4 space-y-3">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-medium text-slate-300">Özellik #<?php echo $index + 1; ?></span>
                                                <button type="button" onclick="removeWhyChooseItem(this)" class="p-1.5 text-red-400 hover:bg-red-500/10 rounded transition-colors">
                                                    <span class="material-symbols-outlined text-sm">delete</span>
                                                </button>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">İkon</label>
                                                <select name="sections[why-choose-us][items][<?php echo $index; ?>][icon]" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                                                    <option value="shield" <?php echo $itemIcon === 'shield' ? 'selected' : ''; ?>>🛡️ Güvenlik (shield)</option>
                                                    <option value="home" <?php echo $itemIcon === 'home' ? 'selected' : ''; ?>>🏠 Ev (home)</option>
                                                    <option value="users" <?php echo $itemIcon === 'users' ? 'selected' : ''; ?>>👥 Kullanıcılar (users)</option>
                                                    <option value="clock" <?php echo $itemIcon === 'clock' ? 'selected' : ''; ?>>⏰ Saat (clock)</option>
                                                    <option value="dollar" <?php echo $itemIcon === 'dollar' ? 'selected' : ''; ?>>💰 Dolar (dollar)</option>
                                                    <option value="document" <?php echo $itemIcon === 'document' ? 'selected' : ''; ?>>📄 Belge (document)</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">Başlık</label>
                                                <input type="text" name="sections[why-choose-us][items][<?php echo $index; ?>][title]" value="<?php echo esc_attr($itemTitle); ?>" placeholder="Özellik Başlığı" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">Açıklama</label>
                                                <textarea name="sections[why-choose-us][items][<?php echo $index; ?>][description]" rows="2" class="w-full px-4 py-2 input-field rounded-lg text-sm resize-none" placeholder="Özellik açıklaması..."><?php echo esc_html($itemDescription); ?></textarea>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="sections[why-choose-us][enabled]" value="1" <?php echo ($pageSections['why-choose-us']['enabled'] ?? true) ? 'checked' : ''; ?> class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500">
                                    <span class="text-xs text-slate-400">Bu bölümü göster</span>
                                </label>
                            </div>
                        </details>
                        
                        <!-- Müşteri Yorumları -->
                        <details class="group border border-white/5 rounded-xl overflow-hidden">
                            <summary class="p-4 flex items-center justify-between cursor-pointer hover:bg-white/5 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-sm">💬</span>
                                    <span class="text-sm font-medium">Müşteri Yorumları</span>
                                </div>
                                <span class="material-symbols-outlined text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
                            </summary>
                            <div class="p-4 pt-0 space-y-3 border-t border-white/5">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Müşteri Yorumları Başlık</label>
                                    <input type="text" name="sections[testimonials][title]" value="<?php echo esc_attr($pageSections['testimonials']['title'] ?? 'Müşterilerimiz Ne Diyor'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Müşteri Yorumları Alt Başlık</label>
                                    <textarea name="sections[testimonials][subtitle]" rows="2" class="w-full px-4 py-2.5 input-field rounded-lg text-sm resize-none"><?php echo esc_html($pageSections['testimonials']['subtitle'] ?? 'Sadece bizim söylediklerimize değil, müşterilerimizin deneyimlerine de kulak verin'); ?></textarea>
                                </div>
                                
                                <!-- Testimonials Items -->
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <label class="block text-xs font-medium text-slate-300">Müşteri Yorumları</label>
                                        <button type="button" onclick="addTestimonialItem()" class="px-3 py-1.5 text-xs font-medium bg-indigo-500/20 text-indigo-400 rounded-lg hover:bg-indigo-500/30 transition-colors flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-sm">add</span>
                                            Yorum Ekle
                                        </button>
                                    </div>
                                    <div id="testimonials-items" class="space-y-3">
                                        <?php 
                                        $testimonialsItems = $pageSections['testimonials']['items'] ?? [];
                                        if (empty($testimonialsItems)) {
                                            // Varsayılan yorumlar
                                            $testimonialsItems = [
                                                [
                                                    'name' => 'Ayşe Yılmaz',
                                                    'role' => 'Ev Alıcısı',
                                                    'text' => 'Ekip, ev alma deneyimimizi sorunsuz hale getirdi. Profesyonel, hızlı yanıt veren ve tam olarak aradığımız şeyi bulmamıza yardımcı olan bir ekiple çalıştık.',
                                                    'rating' => 5,
                                                    'image' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150'
                                                ],
                                                [
                                                    'name' => 'Mehmet Demir',
                                                    'role' => 'Gayrimenkul Yatırımcısı',
                                                    'text' => 'Mükemmel bir hizmet! Yatırım yapmak isteyen herkese şiddetle tavsiye ederim. Birden fazla yatırım gayrimenkulü edinmemize ve harika getiriler elde etmemize yardımcı oldular.',
                                                    'rating' => 5,
                                                    'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150'
                                                ],
                                                [
                                                    'name' => 'Zeynep Kaya',
                                                    'role' => 'İlk Kez Ev Alıcısı',
                                                    'text' => 'İlk kez ev alıcıları olarak süreçten endişeliydik. Danışmanımız bizi her adımda yönlendirdi ve kendimize güvenmemizi sağladı. Teşekkürler!',
                                                    'rating' => 5,
                                                    'image' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150'
                                                ]
                                            ];
                                        }
                                        foreach ($testimonialsItems as $index => $item): 
                                            $itemName = $item['name'] ?? '';
                                            $itemRole = $item['role'] ?? '';
                                            $itemText = $item['text'] ?? $item['content'] ?? '';
                                            $itemRating = $item['rating'] ?? 5;
                                            $itemImage = $item['image'] ?? $item['avatar'] ?? '';
                                        ?>
                                        <div class="testimonial-item glass rounded-lg p-4 space-y-3">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-medium text-slate-300">Yorum #<?php echo $index + 1; ?></span>
                                                <button type="button" onclick="removeTestimonialItem(this)" class="p-1.5 text-red-400 hover:bg-red-500/10 rounded transition-colors">
                                                    <span class="material-symbols-outlined text-sm">delete</span>
                                                </button>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">Müşteri Adı</label>
                                                <input type="text" name="sections[testimonials][items][<?php echo $index; ?>][name]" value="<?php echo esc_attr($itemName); ?>" placeholder="Ahmet Yılmaz" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">Ünvan/Pozisyon</label>
                                                <input type="text" name="sections[testimonials][items][<?php echo $index; ?>][role]" value="<?php echo esc_attr($itemRole); ?>" placeholder="CEO, TechCorp" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">Yorum İçeriği</label>
                                                <textarea name="sections[testimonials][items][<?php echo $index; ?>][text]" rows="3" class="w-full px-4 py-2 input-field rounded-lg text-sm resize-none" placeholder="Müşteri yorumu..."><?php echo esc_html($itemText); ?></textarea>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">Değerlendirme (Yıldız)</label>
                                                <div class="flex items-center gap-2">
                                                    <?php for ($star = 1; $star <= 5; $star++): ?>
                                                    <button type="button" onclick="setTestimonialRating(this, <?php echo $index; ?>, <?php echo $star; ?>)" class="testimonial-rating-btn p-1 transition-all <?php echo $star <= $itemRating ? 'text-yellow-400' : 'text-slate-500'; ?>" data-rating="<?php echo $star; ?>">
                                                        <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' <?php echo $star <= $itemRating ? '1' : '0'; ?>;">star</span>
                                                    </button>
                                                    <?php endfor; ?>
                                                    <input type="hidden" name="sections[testimonials][items][<?php echo $index; ?>][rating]" value="<?php echo esc_attr($itemRating); ?>" class="testimonial-rating-input">
                                                    <span class="text-xs text-slate-500 ml-2 rating-display-<?php echo $index; ?>"><?php echo $itemRating; ?>/5</span>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">Avatar Fotoğrafı (Opsiyonel)</label>
                                                <div class="flex items-center gap-4">
                                                    <div class="testimonial-avatar-preview w-16 h-16 rounded-full bg-slate-800/50 border-2 border-dashed border-slate-600 flex items-center justify-center overflow-hidden hover:border-indigo-500/50 transition-colors cursor-pointer" onclick="selectTestimonialAvatar(<?php echo $index; ?>)" data-index="<?php echo $index; ?>">
                                                        <?php if ($itemImage): ?>
                                                        <img src="<?php echo esc_url($itemImage); ?>" alt="" class="w-full h-full object-cover">
                                                        <?php else: ?>
                                                        <span class="text-white font-semibold text-xl"><?php echo mb_substr($itemName, 0, 1, 'UTF-8'); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="flex-1 space-y-2">
                                                        <button type="button" onclick="selectTestimonialAvatar(<?php echo $index; ?>)" class="w-full px-4 py-2 text-xs font-medium bg-indigo-500/20 text-indigo-400 rounded-lg hover:bg-indigo-500/30 transition-colors">
                                                            Fotoğraf Seç
                                                        </button>
                                                        <button type="button" onclick="removeTestimonialAvatar(<?php echo $index; ?>)" class="w-full px-4 py-2 text-xs font-medium text-slate-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors">
                                                            Kaldır
                                                        </button>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="sections[testimonials][items][<?php echo $index; ?>][image]" value="<?php echo esc_attr($itemImage); ?>" class="testimonial-avatar-input" data-index="<?php echo $index; ?>">
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="sections[testimonials][enabled]" value="1" <?php echo ($pageSections['testimonials']['enabled'] ?? true) ? 'checked' : ''; ?> class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500">
                                    <span class="text-xs text-slate-400">Bu bölümü göster</span>
                                </label>
                            </div>
                        </details>
                        
                        <!-- Blog Bölümü -->
                        <details class="glass rounded-xl overflow-hidden group">
                            <summary class="flex items-center justify-between p-4 cursor-pointer hover:bg-white/5 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center text-sm">📰</span>
                                    <span class="text-sm font-medium">Blog Bölümü</span>
                                </div>
                                <span class="material-symbols-outlined text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
                            </summary>
                            <div class="p-4 pt-0 space-y-3 border-t border-white/5">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Blog Başlık</label>
                                    <input type="text" name="sections[blog-preview][title]" value="<?php echo esc_attr($pageSections['blog-preview']['title'] ?? 'Son Yazılar & İpuçları'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Blog Alt Başlık</label>
                                    <textarea name="sections[blog-preview][subtitle]" rows="2" class="w-full px-4 py-2.5 input-field rounded-lg text-sm resize-none"><?php echo esc_html($pageSections['blog-preview']['subtitle'] ?? 'Gayrimenkul içgörülerimizle güncel kalın'); ?></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Gösterilecek Blog Yazısı Sayısı</label>
                                    <input type="number" name="sections[blog-preview][settings][limit]" value="<?php echo esc_attr($pageSections['blog-preview']['settings']['limit'] ?? 3); ?>" min="1" max="12" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                    <p class="text-xs text-slate-500 mt-1">Ana sayfada gösterilecek blog yazısı sayısı (1-12 arası)</p>
                                </div>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="sections[blog-preview][enabled]" value="1" <?php echo ($pageSections['blog-preview']['enabled'] ?? true) ? 'checked' : ''; ?> class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500">
                                    <span class="text-xs text-slate-400">Bu bölümü göster</span>
                                </label>
                            </div>
                        </details>
                        
                        <!-- Call to Action (CTA) Bölümü -->
                        <details class="glass rounded-xl overflow-hidden group">
                            <summary class="flex items-center justify-between p-4 cursor-pointer hover:bg-white/5 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-sm">📞</span>
                                    <span class="text-sm font-medium">Bize Ulaşın (CTA)</span>
                                </div>
                                <span class="material-symbols-outlined text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
                            </summary>
                            <div class="p-4 pt-0 space-y-3 border-t border-white/5">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">CTA Başlık</label>
                                    <input type="text" name="sections[cta][title]" value="<?php echo esc_attr($pageSections['cta']['title'] ?? 'Ready to Find Your Dream Home?'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">CTA Alt Başlık</label>
                                    <textarea name="sections[cta][subtitle]" rows="2" class="w-full px-4 py-2.5 input-field rounded-lg text-sm resize-none"><?php echo esc_html($pageSections['cta']['subtitle'] ?? 'Let us help you find the perfect property. Contact us today for a free consultation.'); ?></textarea>
                                </div>
                                
                                <!-- Form Seçimi -->
                                <div>
                                    <label class="block text-xs font-medium text-slate-300 mb-2">Form Seçimi</label>
                                    <select name="sections[cta][settings][form_id]" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                        <option value="">Form Seçin (Opsiyonel - Form seçilirse butonlar yerine form gösterilir)</option>
                                        <?php foreach ($availableForms as $form): ?>
                                            <option value="<?php echo esc_attr($form['id']); ?>" 
                                                    <?php echo (isset($pageSections['cta']['settings']['form_id']) && $pageSections['cta']['settings']['form_id'] == $form['id']) ? 'selected' : ''; ?>>
                                                <?php echo esc_html($form['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="text-xs text-slate-500 mt-1">Form seçilirse, butonlar yerine form gösterilir. Form seçilmezse butonlar gösterilir.</p>
                                </div>
                                
                                <!-- Buton Ayarları (Form seçilmediğinde gösterilir) -->
                                <div class="p-3 rounded-lg bg-blue-500/10 border border-blue-500/20">
                                    <p class="text-xs text-blue-300 mb-2 flex items-center gap-2">
                                        <span class="material-symbols-outlined text-sm">info</span>
                                        <span class="font-semibold">Buton Ayarları</span>
                                    </p>
                                    <p class="text-xs text-slate-400 mb-3">Form seçilmediğinde bu butonlar gösterilir.</p>
                                    
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs text-slate-400 mb-1.5">Birincil Buton Metni</label>
                                            <input type="text" name="sections[cta][settings][button_text]" value="<?php echo esc_attr($pageSections['cta']['settings']['button_text'] ?? 'Get Started Today'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-slate-400 mb-1.5">Birincil Buton Linki</label>
                                            <input type="text" name="sections[cta][settings][button_link]" value="<?php echo esc_attr($pageSections['cta']['settings']['button_link'] ?? '/contact'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3 mt-3">
                                        <div>
                                            <label class="block text-xs text-slate-400 mb-1.5">İkincil Buton Metni</label>
                                            <input type="text" name="sections[cta][settings][secondary_button_text]" value="<?php echo esc_attr($pageSections['cta']['settings']['secondary_button_text'] ?? 'Browse Properties'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-slate-400 mb-1.5">İkincil Buton Linki</label>
                                            <input type="text" name="sections[cta][settings][secondary_button_link]" value="<?php echo esc_attr($pageSections['cta']['settings']['secondary_button_link'] ?? '/ilanlar'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                        </div>
                                    </div>
                                    
                                    <!-- Buton Renkleri -->
                                    <div class="mt-4 pt-4 border-t border-blue-500/20">
                                        <p class="text-xs text-blue-300 mb-3 font-semibold">Buton Renkleri</p>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">Birincil Buton Arka Plan</label>
                                                <input type="color" name="sections[cta][settings][primary_button_bg]" value="<?php echo esc_attr($pageSections['cta']['settings']['primary_button_bg'] ?? '#ffffff'); ?>" class="w-full h-10 rounded-lg cursor-pointer">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">Birincil Buton Metin</label>
                                                <input type="color" name="sections[cta][settings][primary_button_text]" value="<?php echo esc_attr($pageSections['cta']['settings']['primary_button_text'] ?? '#1e40af'); ?>" class="w-full h-10 rounded-lg cursor-pointer">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">İkincil Buton Arka Plan</label>
                                                <input type="color" name="sections[cta][settings][secondary_button_bg]" value="<?php echo esc_attr($pageSections['cta']['settings']['secondary_button_bg'] ?? '#00000000'); ?>" class="w-full h-10 rounded-lg cursor-pointer">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">İkincil Buton Metin</label>
                                                <input type="color" name="sections[cta][settings][secondary_button_text_color]" value="<?php echo esc_attr($pageSections['cta']['settings']['secondary_button_text_color'] ?? '#ffffff'); ?>" class="w-full h-10 rounded-lg cursor-pointer">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Tasarım Ayarları -->
                                <details class="mt-3">
                                    <summary class="text-xs font-medium text-slate-300 mb-2 cursor-pointer hover:text-slate-200">Tasarım Ayarları</summary>
                                    <div class="mt-3 space-y-3 p-3 rounded-lg bg-slate-800/50">
                                        <!-- Arka Plan Gradient -->
                                        <div>
                                            <label class="block text-xs text-slate-400 mb-2">Arka Plan Gradient Renkleri</label>
                                            <div class="grid grid-cols-3 gap-2">
                                                <div>
                                                    <label class="block text-xs text-slate-500 mb-1">Başlangıç</label>
                                                    <input type="color" name="sections[cta][settings][bg_gradient_from]" value="<?php echo esc_attr($pageSections['cta']['settings']['bg_gradient_from'] ?? '#1e40af'); ?>" class="w-full h-10 rounded-lg cursor-pointer">
                                                </div>
                                                <div>
                                                    <label class="block text-xs text-slate-500 mb-1">Orta</label>
                                                    <input type="color" name="sections[cta][settings][bg_gradient_via]" value="<?php echo esc_attr($pageSections['cta']['settings']['bg_gradient_via'] ?? '#2563eb'); ?>" class="w-full h-10 rounded-lg cursor-pointer">
                                                </div>
                                                <div>
                                                    <label class="block text-xs text-slate-500 mb-1">Bitiş</label>
                                                    <input type="color" name="sections[cta][settings][bg_gradient_to]" value="<?php echo esc_attr($pageSections['cta']['settings']['bg_gradient_to'] ?? '#1e3a8a'); ?>" class="w-full h-10 rounded-lg cursor-pointer">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Card Ayarları -->
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">Kart Arka Plan Opaklığı</label>
                                                <input type="number" name="sections[cta][settings][card_bg_opacity]" value="<?php echo esc_attr($pageSections['cta']['settings']['card_bg_opacity'] ?? '0.1'); ?>" min="0" max="1" step="0.1" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">Blur Efekti</label>
                                                <select name="sections[cta][settings][card_blur]" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                                    <option value="none" <?php echo ($pageSections['cta']['settings']['card_blur'] ?? 'md') === 'none' ? 'selected' : ''; ?>>Yok</option>
                                                    <option value="sm" <?php echo ($pageSections['cta']['settings']['card_blur'] ?? 'md') === 'sm' ? 'selected' : ''; ?>>Küçük</option>
                                                    <option value="md" <?php echo ($pageSections['cta']['settings']['card_blur'] ?? 'md') === 'md' ? 'selected' : ''; ?>>Orta</option>
                                                    <option value="lg" <?php echo ($pageSections['cta']['settings']['card_blur'] ?? 'md') === 'lg' ? 'selected' : ''; ?>>Büyük</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <!-- Padding -->
                                        <div>
                                            <label class="block text-xs text-slate-400 mb-1.5">Dikey Boşluk</label>
                                            <select name="sections[cta][settings][padding_y]" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                                <option value="py-12 lg:py-16" <?php echo ($pageSections['cta']['settings']['padding_y'] ?? 'py-20 lg:py-32') === 'py-12 lg:py-16' ? 'selected' : ''; ?>>Küçük</option>
                                                <option value="py-16 lg:py-24" <?php echo ($pageSections['cta']['settings']['padding_y'] ?? 'py-20 lg:py-32') === 'py-16 lg:py-24' ? 'selected' : ''; ?>>Orta</option>
                                                <option value="py-20 lg:py-32" <?php echo ($pageSections['cta']['settings']['padding_y'] ?? 'py-20 lg:py-32') === 'py-20 lg:py-32' ? 'selected' : ''; ?>>Büyük</option>
                                                <option value="py-24 lg:py-40" <?php echo ($pageSections['cta']['settings']['padding_y'] ?? 'py-20 lg:py-32') === 'py-24 lg:py-40' ? 'selected' : ''; ?>>Çok Büyük</option>
                                            </select>
                                        </div>
                                    </div>
                                </details>
                                
                                <!-- Trust Indicators -->
                                <details class="mt-3">
                                    <summary class="text-xs font-medium text-slate-300 mb-2 cursor-pointer hover:text-slate-200">Güven Göstergeleri</summary>
                                    <div class="mt-3 space-y-3 p-3 rounded-lg bg-slate-800/50">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="checkbox" name="sections[cta][settings][show_trust_indicators]" value="1" <?php echo ($pageSections['cta']['settings']['show_trust_indicators'] ?? true) ? 'checked' : ''; ?> class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500">
                                            <span class="text-xs text-slate-400">Güven göstergelerini göster</span>
                                        </label>
                                        
                                        <div id="trust-indicators-list" class="space-y-2">
                                            <?php 
                                            $trustIndicators = !empty($pageSections['cta']['settings']['trust_indicators']) && is_array($pageSections['cta']['settings']['trust_indicators']) 
                                                ? $pageSections['cta']['settings']['trust_indicators'] 
                                                : [
                                                    ['text' => 'Ücretsiz Danışmanlık'],
                                                    ['text' => 'Profesyonel Hizmet'],
                                                    ['text' => '7/24 Destek']
                                                ];
                                            foreach ($trustIndicators as $index => $indicator): 
                                            ?>
                                                <div class="flex items-center gap-2 trust-indicator-item">
                                                    <input type="text" name="sections[cta][settings][trust_indicators][<?php echo $index; ?>][text]" 
                                                           value="<?php echo esc_attr($indicator['text'] ?? ''); ?>" 
                                                           placeholder="Gösterge metni" 
                                                           class="flex-1 px-3 py-2 input-field rounded-lg text-sm">
                                                    <button type="button" onclick="removeTrustIndicator(this)" class="p-2 text-red-400 hover:bg-red-500/10 rounded-lg transition-colors">
                                                        <span class="material-symbols-outlined text-sm">delete</span>
                                                    </button>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        
                                        <button type="button" onclick="addTrustIndicator()" class="w-full px-4 py-2 bg-indigo-500/20 text-indigo-400 rounded-lg hover:bg-indigo-500/30 transition-colors flex items-center justify-center gap-2 text-sm">
                                            <span class="material-symbols-outlined text-lg">add</span>
                                            Gösterge Ekle
                                        </button>
                                    </div>
                                </details>
                                
                                <label class="flex items-center gap-3 cursor-pointer mt-3">
                                    <input type="checkbox" name="sections[cta][enabled]" value="1" <?php echo ($pageSections['cta']['enabled'] ?? true) ? 'checked' : ''; ?> class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500">
                                    <span class="text-xs text-slate-400">Bu bölümü göster</span>
                                </label>
                            </div>
                        </details>
                        
                    </div>
                </div>
            </div>
            
            <!-- İletişim Sayfası Bölümleri -->
            <div class="border-b border-white/5">
                <button onclick="window.toggleSection('contact')" class="section-btn w-full p-4 flex items-center gap-4 hover:bg-white/5 transition-all" data-section="contact">
                    <div class="section-icon w-10 h-10 rounded-xl bg-slate-700/50 flex items-center justify-center">
                        <span class="material-symbols-outlined">mail</span>
                    </div>
                    <div class="flex-1 text-left">
                        <span class="text-sm font-semibold block">İletişim Sayfası</span>
                        <span class="text-xs text-slate-400">Hero, form, harita ayarları</span>
                    </div>
                    <span class="material-symbols-outlined text-slate-400 section-arrow transition-transform">expand_more</span>
                </button>
                <div id="contact-panel" class="section-panel">
                    <div class="px-4 pb-5 space-y-3">
                        
                        <!-- Hero Section -->
                        <details class="glass rounded-xl overflow-hidden group">
                            <summary class="flex items-center justify-between p-4 cursor-pointer hover:bg-white/5 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-sm">📧</span>
                                    <span class="text-sm font-medium">Hero Bölümü</span>
                                </div>
                                <span class="material-symbols-outlined text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
                            </summary>
                            <div class="p-4 pt-0 space-y-3 border-t border-white/5">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Başlık</label>
                                    <input type="text" name="contact_sections[hero][title]" value="<?php echo esc_attr($contactPageSections['hero']['title'] ?? 'Hayalinizdeki Mülkü Bulalım'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Alt Başlık</label>
                                    <textarea name="contact_sections[hero][subtitle]" rows="2" class="w-full px-4 py-2.5 input-field rounded-lg text-sm resize-none"><?php echo esc_html($contactPageSections['hero']['subtitle'] ?? 'Uzman ekibimiz, mülk satın alma, satış veya kiralama işlemlerinizde size yardımcı olmak için burada. Hemen iletişime geçin!'); ?></textarea>
                                </div>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="contact_sections[hero][enabled]" value="1" <?php echo ($contactPageSections['hero']['enabled'] ?? true) ? 'checked' : ''; ?> class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500">
                                    <span class="text-xs text-slate-400">Bu bölümü göster</span>
                                </label>
                            </div>
                        </details>
                        
                        <!-- Form Section -->
                        <details class="glass rounded-xl overflow-hidden group">
                            <summary class="flex items-center justify-between p-4 cursor-pointer hover:bg-white/5 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-sm">📝</span>
                                    <span class="text-sm font-medium">İletişim Formu</span>
                                </div>
                                <span class="material-symbols-outlined text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
                            </summary>
                            <div class="p-4 pt-0 space-y-3 border-t border-white/5">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Form Başlığı</label>
                                    <input type="text" name="contact_sections[form][title]" value="<?php echo esc_attr($contactPageSections['form']['title'] ?? 'Mülk Talebinizi İletin'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Form Açıklaması</label>
                                    <textarea name="contact_sections[form][description]" rows="2" class="w-full px-4 py-2.5 input-field rounded-lg text-sm resize-none"><?php echo esc_html($contactPageSections['form']['description'] ?? 'Aradığınız mülk özelliklerini belirtin, size en uygun seçenekleri sunalım.'); ?></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Form Seçimi</label>
                                    <select name="contact_sections[form][form_id]" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                        <option value="">Varsayılan Form (iletisim)</option>
                                        <?php foreach ($availableForms as $form): ?>
                                        <option value="<?php echo esc_attr($form['id']); ?>" <?php echo (isset($contactPageSections['form']['form_id']) && $contactPageSections['form']['form_id'] == $form['id']) ? 'selected' : ''; ?>>
                                            <?php echo esc_html($form['name']); ?> (<?php echo esc_html($form['slug']); ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="text-xs text-slate-500 mt-1">Gösterilecek formu seçin. Seçilmezse varsayılan "iletisim" formu gösterilir.</p>
                                </div>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="contact_sections[form][enabled]" value="1" <?php echo ($contactPageSections['form']['enabled'] ?? true) ? 'checked' : ''; ?> class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500">
                                    <span class="text-xs text-slate-400">Formu göster</span>
                                </label>
                            </div>
                        </details>
                        
                        <!-- Services Section -->
                        <details class="glass rounded-xl overflow-hidden group">
                            <summary class="flex items-center justify-between p-4 cursor-pointer hover:bg-white/5 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center text-sm">🛠️</span>
                                    <span class="text-sm font-medium">Hizmetlerimiz</span>
                                </div>
                                <span class="material-symbols-outlined text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
                            </summary>
                            <div class="p-4 pt-0 space-y-3 border-t border-white/5">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Bölüm Başlığı</label>
                                    <input type="text" name="contact_sections[services][title]" value="<?php echo esc_attr($contactPageSections['services']['title'] ?? 'Hizmetlerimiz'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Bölüm Açıklaması</label>
                                    <input type="text" name="contact_sections[services][description]" value="<?php echo esc_attr($contactPageSections['services']['description'] ?? 'Size nasıl yardımcı olabiliriz?'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                
                                <!-- Service Items -->
                                <div class="border-t border-white/5 pt-3">
                                    <div class="flex items-center justify-between mb-3">
                                        <label class="block text-xs font-medium text-slate-300">Hizmet Öğeleri</label>
                                        <button type="button" onclick="addServiceItem()" class="px-3 py-1.5 text-xs font-medium bg-indigo-500/20 text-indigo-400 rounded-lg hover:bg-indigo-500/30 transition-colors flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm">add</span>
                                            Ekle
                                        </button>
                                    </div>
                                    <div id="services-items" class="space-y-3">
                                        <?php 
                                        $serviceItems = isset($contactPageSections['services']['items']) && is_array($contactPageSections['services']['items']) 
                                            ? $contactPageSections['services']['items'] 
                                            : [
                                                ['title' => 'Satılık Mülk', 'icon' => 'home', 'link' => ''],
                                                ['title' => 'Kiralık Mülk', 'icon' => 'apartment', 'link' => ''],
                                                ['title' => 'Mülk Değerleme', 'icon' => 'assessment', 'link' => ''],
                                                ['title' => 'Danışmanlık', 'icon' => 'people', 'link' => '']
                                            ];
                                        foreach ($serviceItems as $index => $item): 
                                        ?>
                                        <div class="service-item glass rounded-lg p-4 space-y-3">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-medium text-slate-300">Hizmet #<?php echo $index + 1; ?></span>
                                                <button type="button" onclick="removeServiceItem(this)" class="p-1.5 text-red-400 hover:bg-red-500/10 rounded transition-colors">
                                                    <span class="material-symbols-outlined text-sm">delete</span>
                                                </button>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">İkon (Material Symbols)</label>
                                                <input type="text" name="contact_sections[services][items][<?php echo $index; ?>][icon]" value="<?php echo esc_attr($item['icon'] ?? 'star'); ?>" placeholder="home" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                                                <p class="text-xs text-slate-500 mt-1">Material Symbols ikon adı (örn: home, apartment, assessment)</p>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">Hizmet Başlığı</label>
                                                <input type="text" name="contact_sections[services][items][<?php echo $index; ?>][title]" value="<?php echo esc_attr($item['title'] ?? ''); ?>" placeholder="Hizmet adı" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">Link (Opsiyonel)</label>
                                                <input type="text" name="contact_sections[services][items][<?php echo $index; ?>][link]" value="<?php echo esc_attr($item['link'] ?? ''); ?>" placeholder="/services/satilik" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="contact_sections[services][enabled]" value="1" <?php echo ($contactPageSections['services']['enabled'] ?? true) ? 'checked' : ''; ?> class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500">
                                    <span class="text-xs text-slate-400">Bu bölümü göster</span>
                                </label>
                            </div>
                        </details>
                        
                        <!-- Map Section -->
                        <details class="glass rounded-xl overflow-hidden group">
                            <summary class="flex items-center justify-between p-4 cursor-pointer hover:bg-white/5 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-red-500 to-pink-600 flex items-center justify-center text-sm">📍</span>
                                    <span class="text-sm font-medium">Harita</span>
                                </div>
                                <span class="material-symbols-outlined text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
                            </summary>
                            <div class="p-4 pt-0 space-y-3 border-t border-white/5">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Google Maps Embed Kodu</label>
                                    <textarea name="contact_sections[map][embed]" rows="4" class="w-full px-4 py-2.5 input-field rounded-lg text-sm resize-none font-mono text-xs" placeholder="<iframe src='...'></iframe>"><?php echo esc_html($contactPageSections['map']['embed'] ?? ''); ?></textarea>
                                    <p class="text-xs text-slate-500 mt-1">Google Maps'ten embed kodunu buraya yapıştırın</p>
                                </div>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="contact_sections[map][enabled]" value="1" <?php echo ($contactPageSections['map']['enabled'] ?? true) ? 'checked' : ''; ?> class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500">
                                    <span class="text-xs text-slate-400">Haritayı göster</span>
                                </label>
                            </div>
                        </details>
                        
                    </div>
                </div>
            </div>
            
            <!-- Footer & Diğer Ayarlar -->
            <?php if (!empty($settings['custom'])): ?>
            <div class="border-b border-white/5">
                <button onclick="window.toggleSection('custom')" class="section-btn w-full p-4 flex items-center gap-4 hover:bg-white/5 transition-all" data-section="custom">
                    <div class="section-icon w-10 h-10 rounded-xl bg-slate-700/50 flex items-center justify-center">
                        <span class="material-symbols-outlined">settings</span>
                    </div>
                    <div class="flex-1 text-left">
                        <span class="text-sm font-semibold block">Footer & Diğer</span>
                        <span class="text-xs text-slate-400">Footer ve genel ayarlar</span>
                    </div>
                    <span class="material-symbols-outlined text-slate-400 section-arrow transition-transform">expand_more</span>
                </button>
                <div id="custom-panel" class="section-panel">
                    <div class="px-4 pb-5 space-y-4">
                        <div class="glass rounded-xl p-4 space-y-4">
                            <?php if (isset($settings['custom']['show_breadcrumb'])): ?>
                            <label class="flex items-center gap-3 cursor-pointer p-3 rounded-lg hover:bg-white/5 transition-colors">
                                <input type="checkbox" name="custom[show_breadcrumb]" value="1" <?php echo ($settings['custom']['show_breadcrumb']['value'] ?? $settings['custom']['show_breadcrumb']['default'] ?? true) ? 'checked' : ''; ?> class="w-5 h-5 rounded bg-slate-700 border-slate-600 text-blue-500 focus:ring-blue-500/30">
                                <span class="text-sm">Breadcrumb göster</span>
                            </label>
                            <?php endif; ?>
                            
                            <?php if (isset($settings['custom']['footer_columns'])): ?>
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-2">Footer Kolon Sayısı</label>
                                <select name="custom[footer_columns]" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                    <?php 
                                    $footerColumns = ['2', '3', '4'];
                                    $currentColumns = $settings['custom']['footer_columns']['value'] ?? $settings['custom']['footer_columns']['default'] ?? '4';
                                    foreach ($footerColumns as $col): 
                                    ?>
                                    <option value="<?php echo $col; ?>" <?php echo $currentColumns === $col ? 'selected' : ''; ?>><?php echo $col; ?> Kolon</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($settings['custom']['show_back_to_top'])): ?>
                            <label class="flex items-center gap-3 cursor-pointer p-3 rounded-lg hover:bg-white/5 transition-colors">
                                <input type="checkbox" name="custom[show_back_to_top]" value="1" <?php echo ($settings['custom']['show_back_to_top']['value'] ?? $settings['custom']['show_back_to_top']['default'] ?? true) ? 'checked' : ''; ?> class="w-5 h-5 rounded bg-slate-700 border-slate-600 text-blue-500 focus:ring-blue-500/30">
                                <span class="text-sm">Yukarı çık butonu</span>
                            </label>
                            <?php endif; ?>
                            
                            <?php if (isset($settings['custom']['footer_bg_color'])): ?>
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-2">Footer Arka Plan Rengi</label>
                                <div class="flex items-center gap-3">
                                    <input type="color" value="<?php echo esc_attr($settings['custom']['footer_bg_color']['value'] ?? $settings['custom']['footer_bg_color']['default'] ?? '#1e293b'); ?>" class="w-10 h-10 rounded-lg" oninput="document.getElementById('footer_bg_color').value = this.value">
                                    <input type="text" name="custom[footer_bg_color]" id="footer_bg_color" value="<?php echo esc_attr($settings['custom']['footer_bg_color']['value'] ?? $settings['custom']['footer_bg_color']['default'] ?? '#1e293b'); ?>" class="flex-1 px-4 py-2.5 input-field rounded-lg text-sm font-mono" oninput="this.previousElementSibling.value = this.value">
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($settings['custom']['footer_text_color'])): ?>
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-2">Footer Metin Rengi</label>
                                <div class="flex items-center gap-3">
                                    <input type="color" value="<?php echo esc_attr($settings['custom']['footer_text_color']['value'] ?? $settings['custom']['footer_text_color']['default'] ?? '#ffffff'); ?>" class="w-10 h-10 rounded-lg" oninput="document.getElementById('footer_text_color').value = this.value">
                                    <input type="text" name="custom[footer_text_color]" id="footer_text_color" value="<?php echo esc_attr($settings['custom']['footer_text_color']['value'] ?? $settings['custom']['footer_text_color']['default'] ?? '#ffffff'); ?>" class="flex-1 px-4 py-2.5 input-field rounded-lg text-sm font-mono" oninput="this.previousElementSibling.value = this.value">
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($settings['custom']['footer_copyright_text'])): ?>
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-2">Footer Telif Hakkı Metni</label>
                                <input type="text" name="custom[footer_copyright_text]" value="<?php echo esc_attr($settings['custom']['footer_copyright_text']['value'] ?? $settings['custom']['footer_copyright_text']['default'] ?? 'All rights reserved.'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                            </div>
                            <?php endif; ?>
                            
                            <!-- Footer Başlıkları -->
                            <div class="mt-4 pt-4 border-t border-white/5">
                                <p class="text-xs font-medium text-slate-300 mb-3">Footer Başlıkları</p>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1.5">Menü Başlığı</label>
                                        <input type="text" name="custom[footer_menu_title]" value="<?php echo esc_attr($settings['custom']['footer_menu_title']['value'] ?? $settings['custom']['footer_menu_title']['default'] ?? 'Quick Links'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1.5">İletişim Başlığı</label>
                                        <input type="text" name="custom[footer_contact_title]" value="<?php echo esc_attr($settings['custom']['footer_contact_title']['value'] ?? $settings['custom']['footer_contact_title']['default'] ?? 'Contact'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1.5">Yazılar Başlığı</label>
                                        <input type="text" name="custom[footer_posts_title]" value="<?php echo esc_attr($settings['custom']['footer_posts_title']['value'] ?? $settings['custom']['footer_posts_title']['default'] ?? 'Recent Posts'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (isset($settings['custom']['footer_show_social'])): ?>
                            <label class="flex items-center gap-3 cursor-pointer p-3 rounded-lg hover:bg-white/5 transition-colors">
                                <input type="checkbox" name="custom[footer_show_social]" value="1" <?php echo ($settings['custom']['footer_show_social']['value'] ?? $settings['custom']['footer_show_social']['default'] ?? true) ? 'checked' : ''; ?> class="w-5 h-5 rounded bg-slate-700 border-slate-600 text-blue-500 focus:ring-blue-500/30">
                                <span class="text-sm">Sosyal medya ikonları göster</span>
                            </label>
                            <?php endif; ?>
                            
                            <?php if (isset($settings['custom']['footer_show_menu'])): ?>
                            <label class="flex items-center gap-3 cursor-pointer p-3 rounded-lg hover:bg-white/5 transition-colors">
                                <input type="checkbox" name="custom[footer_show_menu]" value="1" <?php echo ($settings['custom']['footer_show_menu']['value'] ?? $settings['custom']['footer_show_menu']['default'] ?? true) ? 'checked' : ''; ?> class="w-5 h-5 rounded bg-slate-700 border-slate-600 text-blue-500 focus:ring-blue-500/30">
                                <span class="text-sm">Footer menü göster</span>
                            </label>
                            <?php endif; ?>
                            
                            <?php if (isset($settings['custom']['footer_show_contact'])): ?>
                            <label class="flex items-center gap-3 cursor-pointer p-3 rounded-lg hover:bg-white/5 transition-colors">
                                <input type="checkbox" name="custom[footer_show_contact]" value="1" <?php echo ($settings['custom']['footer_show_contact']['value'] ?? $settings['custom']['footer_show_contact']['default'] ?? true) ? 'checked' : ''; ?> class="w-5 h-5 rounded bg-slate-700 border-slate-600 text-blue-500 focus:ring-blue-500/30">
                                <span class="text-sm">İletişim bilgileri göster</span>
                            </label>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    </aside>
    
    <!-- Preview Area -->
    <main class="flex-1 flex flex-col bg-slate-900/50">
        <div class="flex-1 overflow-hidden">
            <iframe id="previewFrame" src="<?php echo esc_url($previewUrl); ?>" class="w-full h-full border-0"></iframe>
        </div>
    </main>
    
</div>

<!-- Media Picker Script -->
<script src="<?php echo ViewRenderer::assetUrl('admin/js/media-picker.js'); ?>"></script>

<script>
// Save Settings
function saveSettings() {
    const btn = document.getElementById('saveBtn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="material-symbols-outlined text-lg animate-spin">sync</span> Kaydediliyor...';
    btn.disabled = true;
    
    // Collect all settings
    const settings = {
        branding: {},
        colors: {},
        fonts: {},
        header: {},
        custom: {},
        sections: {},
        contact_sections: {}
    };
    
    // Branding
    const logo = document.getElementById('siteLogo');
    if (logo) settings.branding.site_logo = logo.value;
    const favicon = document.getElementById('siteFavicon');
    if (favicon) settings.branding.site_favicon = favicon.value;
    
    // Colors - Get from color picker or text input
    document.querySelectorAll('input[type="color"][name^="colors["], input[id^="color_text_"]').forEach(input => {
        let key;
        if (input.type === 'color') {
            key = input.name.match(/colors\[(.+)\]/)?.[1];
        } else {
            key = input.id.replace('color_text_', '');
        }
        if (key) {
            // Prefer color picker value if available, otherwise use text input
            const colorPicker = document.getElementById('color_' + key);
            settings.colors[key] = colorPicker ? colorPicker.value : input.value;
        }
    });
    
    // Fonts
    document.querySelectorAll('select[name^="fonts["]').forEach(select => {
        const key = select.name.match(/fonts\[(.+)\]/)[1];
        settings.fonts[key] = select.value;
    });
    
    // Header
    document.querySelectorAll('input[name^="header["], select[name^="header["]').forEach(input => {
        const key = input.name.match(/header\[(.+)\]/)[1];
        if (input.type === 'checkbox') {
            settings.header[key] = input.checked ? '1' : '0';
        } else {
            settings.header[key] = input.value;
        }
    });
    
    // Custom
    document.querySelectorAll('input[name^="custom["], select[name^="custom["]').forEach(input => {
        const key = input.name.match(/custom\[(.+)\]/)[1];
        if (input.type === 'checkbox') {
            settings.custom[key] = input.checked ? '1' : '0';
        } else {
            settings.custom[key] = input.value;
        }
    });
    
    // Sections (Hero, Features, etc.)
    document.querySelectorAll('input[name^="sections["], textarea[name^="sections["], select[name^="sections["]').forEach(input => {
        // Support nested structure: sections[sectionId][key] or sections[sectionId][settings][key]
        const match = input.name.match(/sections\[(.+?)\]\[(.+?)\](?:\[(.+?)\])?/);
        if (match) {
            const sectionId = match[1];
            const key1 = match[2];
            const key2 = match[3]; // For nested like settings[limit] or items[index][key]
            
            if (!settings.sections[sectionId]) {
                settings.sections[sectionId] = {};
            }
            
            let value;
            if (input.type === 'checkbox') {
                value = input.checked ? '1' : '0';
            } else {
                value = input.value;
            }
            
            if (key2) {
                // Check if it's items[index][key] structure
                if (key1 === 'items' && /^\d+$/.test(key2)) {
                    // This is items[index], need to find the actual key
                    const itemMatch = input.name.match(/sections\[(.+?)\]\[items\]\[(\d+)\]\[(.+?)\]/);
                    if (itemMatch) {
                        const itemIndex = itemMatch[2];
                        const itemKey = itemMatch[3];
                        if (!settings.sections[sectionId].items) {
                            settings.sections[sectionId].items = {};
                        }
                        if (!settings.sections[sectionId].items[itemIndex]) {
                            settings.sections[sectionId].items[itemIndex] = {};
                        }
                        settings.sections[sectionId].items[itemIndex][itemKey] = value;
                    }
                } else {
                    // Nested structure: sections[sectionId][settings][limit]
                    if (!settings.sections[sectionId][key1]) {
                        settings.sections[sectionId][key1] = {};
                    }
                    settings.sections[sectionId][key1][key2] = value;
                }
            } else {
                // Simple structure: sections[sectionId][key]
                settings.sections[sectionId][key1] = value;
            }
        }
    });
    
    // Process why-choose-us items - convert object to array and filter empty items
    if (settings.sections['why-choose-us'] && settings.sections['why-choose-us'].items) {
        const itemsObj = settings.sections['why-choose-us'].items;
        const itemsArray = Object.keys(itemsObj)
            .sort((a, b) => parseInt(a) - parseInt(b))
            .map(key => itemsObj[key])
            .filter(item => item && (item.title || item.description));
        settings.sections['why-choose-us'].items = itemsArray;
    }
    
    // Process testimonials items - convert object to array and filter empty items
    if (settings.sections.testimonials && settings.sections.testimonials.items) {
        const itemsObj = settings.sections.testimonials.items;
        const itemsArray = Object.keys(itemsObj)
            .sort((a, b) => parseInt(a) - parseInt(b))
            .map(key => itemsObj[key])
            .filter(item => item && (item.name || item.text || item.content));
        settings.sections.testimonials.items = itemsArray;
    }
    
    // Contact Sections
    document.querySelectorAll('input[name^="contact_sections["], textarea[name^="contact_sections["], select[name^="contact_sections["]').forEach(input => {
        // Items için özel işleme
        const itemMatch = input.name.match(/contact_sections\[([^\]]+)\]\[items\]\[(\d+)\]\[([^\]]+)\]/);
        if (itemMatch) {
            const [, sectionId, itemIndex, itemKey] = itemMatch;
            if (!settings.contact_sections[sectionId]) settings.contact_sections[sectionId] = {};
            if (!settings.contact_sections[sectionId].items) settings.contact_sections[sectionId].items = {};
            if (!settings.contact_sections[sectionId].items[itemIndex]) settings.contact_sections[sectionId].items[itemIndex] = {};
            settings.contact_sections[sectionId].items[itemIndex][itemKey] = input.value;
            return;
        }
        
        // Normal section ayarları
        const match = input.name.match(/contact_sections\[(.+?)\]\[(.+?)\]/);
        if (match) {
            const sectionId = match[1];
            const key = match[2];
            
            if (!settings.contact_sections[sectionId]) {
                settings.contact_sections[sectionId] = {};
            }
            
            let value;
            if (input.type === 'checkbox') {
                value = input.checked ? '1' : '0';
            } else {
                value = input.value;
            }
            
            settings.contact_sections[sectionId][key] = value;
        }
    });
    
    // Contact sections items'ları array'e çevir ve filtrele
    Object.keys(settings.contact_sections).forEach(sectionId => {
        if (settings.contact_sections[sectionId].items && typeof settings.contact_sections[sectionId].items === 'object') {
            const itemsArray = Object.keys(settings.contact_sections[sectionId].items)
                .sort((a, b) => parseInt(a) - parseInt(b))
                .map(key => settings.contact_sections[sectionId].items[key])
                .filter(item => item && item.title);
            settings.contact_sections[sectionId].items = itemsArray;
        }
    });
    
    // JSON olarak gönder
    fetch('<?php echo admin_url('themes/saveSettings'); ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            theme_slug: '<?php echo esc_js($themeSlug); ?>',
            settings: settings
        })
    })
    .then(async response => {
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            throw new Error('Geçersiz yanıt formatı');
        }
    })
    .then(data => {
        if (data.success) {
            btn.innerHTML = '<span class="material-symbols-outlined text-lg">check_circle</span> Kaydedildi!';
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                // Reload preview with cache bypass
                const previewFrame = document.getElementById('previewFrame');
                const currentSrc = previewFrame.src;
                const separator = currentSrc.includes('?') ? '&' : '?';
                previewFrame.src = currentSrc + separator + '_t=' + Date.now();
            }, 2000);
        } else {
            alert('Hata: ' + (data.message || 'Ayarlar kaydedilemedi'));
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Bağlantı hatası: ' + error.message);
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// Logo Selection
function selectLogo() {
    if (typeof openMediaPicker === 'function') {
        openMediaPicker({
            type: 'image',
            onSelect: (media) => {
                document.getElementById('siteLogo').value = media.file_url;
                const preview = document.getElementById('logoPreview');
                preview.innerHTML = '<img src="' + media.file_url + '" class="w-full h-full object-contain">';
            }
        });
    }
}

function removeLogo() {
    document.getElementById('siteLogo').value = '';
    document.getElementById('logoPreview').innerHTML = '<span class="material-symbols-outlined text-3xl text-slate-500">add_photo_alternate</span>';
}

// Favicon Selection
function selectFavicon() {
    if (typeof openMediaPicker === 'function') {
        openMediaPicker({
            type: 'image',
            onSelect: (media) => {
                document.getElementById('siteFavicon').value = media.file_url;
                const preview = document.getElementById('faviconPreview');
                preview.innerHTML = '<img src="' + media.file_url + '" class="w-full h-full object-contain">';
            }
        });
    }
}

function removeFavicon() {
    document.getElementById('siteFavicon').value = '';
    document.getElementById('faviconPreview').innerHTML = '<span class="material-symbols-outlined text-2xl text-slate-500">add_photo_alternate</span>';
}

// Hero Image Selection for Section
function selectHeroImageSection() {
    if (typeof openMediaPicker === 'function') {
        openMediaPicker({
            type: 'image',
            onSelect: (media) => {
                document.getElementById('hero_image_section').value = media.file_url;
            }
        });
    }
}

// Color Management Functions
function updateColorText(key, value) {
    const textInput = document.getElementById('color_text_' + key);
    if (textInput) {
        textInput.value = value;
        // Update preview
        const preview = textInput.closest('.space-y-2').querySelector('.w-3.h-3');
        if (preview) {
            preview.style.background = value;
        }
        const previewText = textInput.closest('.space-y-2').querySelector('.font-mono');
        if (previewText) {
            previewText.textContent = value;
        }
    }
}

function updateColorPicker(key, value) {
    // Validate hex color
    const hexPattern = /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/;
    if (!hexPattern.test(value)) {
        return;
    }
    
    const colorInput = document.getElementById('color_' + key);
    if (colorInput) {
        colorInput.value = value;
        colorInput.style.background = value;
        // Update preview
        const preview = colorInput.closest('.space-y-2').querySelector('.w-3.h-3');
        if (preview) {
            preview.style.background = value;
        }
        const previewText = colorInput.closest('.space-y-2').querySelector('.font-mono');
        if (previewText) {
            previewText.textContent = value;
        }
    }
}

function resetColor(key, defaultValue) {
    const colorInput = document.getElementById('color_' + key);
    const textInput = document.getElementById('color_text_' + key);
    
    if (colorInput) {
        colorInput.value = defaultValue;
        colorInput.style.background = defaultValue;
    }
    if (textInput) {
        textInput.value = defaultValue;
    }
    
    // Update preview
    const container = colorInput ? colorInput.closest('.space-y-2') : null;
    if (container) {
        const preview = container.querySelector('.w-3.h-3');
        if (preview) {
            preview.style.background = defaultValue;
        }
        const previewText = container.querySelector('.font-mono');
        if (previewText) {
            previewText.textContent = defaultValue;
        }
    }
}

function applyColorScheme(scheme) {
    // Apply primary, secondary, and accent colors
    if (scheme.primary) {
        const primaryInput = document.getElementById('color_primary');
        const primaryText = document.getElementById('color_text_primary');
        if (primaryInput) {
            primaryInput.value = scheme.primary;
            primaryInput.style.background = scheme.primary;
            updateColorText('primary', scheme.primary);
        }
    }
    
    if (scheme.secondary) {
        const secondaryInput = document.getElementById('color_secondary');
        if (secondaryInput) {
            secondaryInput.value = scheme.secondary;
            secondaryInput.style.background = scheme.secondary;
            updateColorText('secondary', scheme.secondary);
        }
    }
    
    if (scheme.accent) {
        const accentInput = document.getElementById('color_accent');
        if (accentInput) {
            accentInput.value = scheme.accent;
            accentInput.style.background = scheme.accent;
            updateColorText('accent', scheme.accent);
        }
    }
    
    // Show feedback
    const btn = event.target.closest('button');
    if (btn) {
        const originalBg = btn.style.background;
        btn.style.background = 'rgba(30, 64, 175, 0.3)';
        setTimeout(() => {
            btn.style.background = originalBg;
        }, 500);
    }
}

// Why Choose Us Item Management
let whyChooseItemIndex = <?php echo isset($pageSections['why-choose-us']['items']) && is_array($pageSections['why-choose-us']['items']) ? count($pageSections['why-choose-us']['items']) : 6; ?>;

function addWhyChooseItem() {
    const container = document.getElementById('why-choose-items');
    const itemHtml = `
        <div class="why-choose-item glass rounded-lg p-4 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-300">Özellik #${whyChooseItemIndex + 1}</span>
                <button type="button" onclick="removeWhyChooseItem(this)" class="p-1.5 text-red-400 hover:bg-red-500/10 rounded transition-colors">
                    <span class="material-symbols-outlined text-sm">delete</span>
                </button>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">İkon</label>
                <select name="sections[why-choose-us][items][${whyChooseItemIndex}][icon]" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                    <option value="shield">🛡️ Güvenlik (shield)</option>
                    <option value="home" selected>🏠 Ev (home)</option>
                    <option value="users">👥 Kullanıcılar (users)</option>
                    <option value="clock">⏰ Saat (clock)</option>
                    <option value="dollar">💰 Dolar (dollar)</option>
                    <option value="document">📄 Belge (document)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Başlık</label>
                <input type="text" name="sections[why-choose-us][items][${whyChooseItemIndex}][title]" value="" placeholder="Özellik Başlığı" class="w-full px-4 py-2 input-field rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Açıklama</label>
                <textarea name="sections[why-choose-us][items][${whyChooseItemIndex}][description]" rows="2" class="w-full px-4 py-2 input-field rounded-lg text-sm resize-none" placeholder="Özellik açıklaması..."></textarea>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', itemHtml);
    whyChooseItemIndex++;
}

function removeWhyChooseItem(btn) {
    if (confirm('Bu özelliği silmek istediğinize emin misiniz?')) {
        btn.closest('.why-choose-item').remove();
        // Index'leri yeniden numaralandır
        const items = document.querySelectorAll('#why-choose-items .why-choose-item');
        items.forEach((item, index) => {
            const numberSpan = item.querySelector('.text-slate-300');
            if (numberSpan) numberSpan.textContent = `Özellik #${index + 1}`;
            
            // Input name'lerini güncelle
            item.querySelectorAll('input, textarea, select').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    const newName = name.replace(/\[items\]\[\d+\]/, `[items][${index}]`);
                    input.setAttribute('name', newName);
                }
            });
        });
    }
}

// Testimonial Item Management
let testimonialItemIndex = <?php echo isset($pageSections['testimonials']['items']) && is_array($pageSections['testimonials']['items']) ? count($pageSections['testimonials']['items']) : 3; ?>;

function addTestimonialItem() {
    const container = document.getElementById('testimonials-items');
    const itemHtml = `
        <div class="testimonial-item glass rounded-lg p-4 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-300">Yorum #${testimonialItemIndex + 1}</span>
                <button type="button" onclick="removeTestimonialItem(this)" class="p-1.5 text-red-400 hover:bg-red-500/10 rounded transition-colors">
                    <span class="material-symbols-outlined text-sm">delete</span>
                </button>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Müşteri Adı</label>
                <input type="text" name="sections[testimonials][items][${testimonialItemIndex}][name]" value="" placeholder="Ahmet Yılmaz" class="w-full px-4 py-2 input-field rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Ünvan/Pozisyon</label>
                <input type="text" name="sections[testimonials][items][${testimonialItemIndex}][role]" value="" placeholder="CEO, TechCorp" class="w-full px-4 py-2 input-field rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Yorum İçeriği</label>
                <textarea name="sections[testimonials][items][${testimonialItemIndex}][text]" rows="3" class="w-full px-4 py-2 input-field rounded-lg text-sm resize-none" placeholder="Müşteri yorumu..."></textarea>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Değerlendirme (Yıldız)</label>
                <div class="flex items-center gap-2">
                    ${Array.from({length: 5}, (_, i) => {
                        const star = i + 1;
                        return `<button type="button" onclick="setTestimonialRating(this, ${testimonialItemIndex}, ${star})" class="testimonial-rating-btn p-1 transition-all text-slate-500" data-rating="${star}">
                            <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' 0;">star</span>
                        </button>`;
                    }).join('')}
                    <input type="hidden" name="sections[testimonials][items][${testimonialItemIndex}][rating]" value="5" class="testimonial-rating-input">
                    <span class="text-xs text-slate-500 ml-2 rating-display-${testimonialItemIndex}">5/5</span>
                </div>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Avatar Fotoğrafı (Opsiyonel)</label>
                <div class="flex items-center gap-4">
                    <div class="testimonial-avatar-preview w-16 h-16 rounded-full bg-slate-800/50 border-2 border-dashed border-slate-600 flex items-center justify-center overflow-hidden hover:border-indigo-500/50 transition-colors cursor-pointer" onclick="selectTestimonialAvatar(${testimonialItemIndex})" data-index="${testimonialItemIndex}">
                        <span class="text-white font-semibold text-xl">A</span>
                    </div>
                    <div class="flex-1 space-y-2">
                        <button type="button" onclick="selectTestimonialAvatar(${testimonialItemIndex})" class="w-full px-4 py-2 text-xs font-medium bg-indigo-500/20 text-indigo-400 rounded-lg hover:bg-indigo-500/30 transition-colors">
                            Fotoğraf Seç
                        </button>
                        <button type="button" onclick="removeTestimonialAvatar(${testimonialItemIndex})" class="w-full px-4 py-2 text-xs font-medium text-slate-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors">
                            Kaldır
                        </button>
                    </div>
                </div>
                <input type="hidden" name="sections[testimonials][items][${testimonialItemIndex}][image]" value="" class="testimonial-avatar-input" data-index="${testimonialItemIndex}">
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', itemHtml);
    testimonialItemIndex++;
    // İlk yüklemede 5 yıldız seçili olsun
    const newItem = container.lastElementChild;
    const ratingButtons = newItem.querySelectorAll('.testimonial-rating-btn');
    ratingButtons.forEach((btn, idx) => {
        if (idx < 5) {
            btn.classList.remove('text-slate-500');
            btn.classList.add('text-yellow-400');
            const icon = btn.querySelector('.material-symbols-outlined');
            if (icon) icon.style.fontVariationSettings = "'FILL' 1";
        }
    });
}

function removeTestimonialItem(btn) {
    if (confirm('Bu yorumu silmek istediğinize emin misiniz?')) {
        btn.closest('.testimonial-item').remove();
        // Index'leri yeniden numaralandır
        const items = document.querySelectorAll('#testimonials-items .testimonial-item');
        items.forEach((item, index) => {
            const numberSpan = item.querySelector('.text-slate-300');
            if (numberSpan) numberSpan.textContent = `Yorum #${index + 1}`;
            
            // Input name'lerini güncelle
            item.querySelectorAll('input, textarea').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    const newName = name.replace(/\[items\]\[\d+\]/, `[items][${index}]`);
                    input.setAttribute('name', newName);
                }
                // Avatar input için data-index'i güncelle
                if (input.classList.contains('testimonial-avatar-input')) {
                    input.setAttribute('data-index', index);
                }
                // Rating input için
                if (input.classList.contains('testimonial-rating-input')) {
                    const ratingValue = input.value || 5;
                    const ratingContainer = input.parentElement;
                    const existingDisplay = ratingContainer.querySelector('span[class*="rating-display-"]');
                    if (existingDisplay) {
                        existingDisplay.className = `text-xs text-slate-500 ml-2 rating-display-${index}`;
                        existingDisplay.textContent = `${ratingValue}/5`;
                    }
                }
            });
            
            // Avatar preview için data-index'i güncelle
            const avatarPreview = item.querySelector('.testimonial-avatar-preview');
            if (avatarPreview) {
                avatarPreview.setAttribute('data-index', index);
                avatarPreview.setAttribute('onclick', `selectTestimonialAvatar(${index})`);
            }
            
            // Buton onclick'lerini güncelle
            const selectBtn = item.querySelector('button[onclick*="selectTestimonialAvatar"]');
            if (selectBtn && selectBtn.textContent.includes('Fotoğraf Seç')) {
                selectBtn.setAttribute('onclick', `selectTestimonialAvatar(${index})`);
            }
            const removeBtn = item.querySelector('button[onclick*="removeTestimonialAvatar"]');
            if (removeBtn) {
                removeBtn.setAttribute('onclick', `removeTestimonialAvatar(${index})`);
            }
            
            // Rating butonlarını güncelle
            const ratingBtns = item.querySelectorAll('.testimonial-rating-btn');
            ratingBtns.forEach((ratingBtn, starIndex) => {
                ratingBtn.setAttribute('onclick', `setTestimonialRating(this, ${index}, ${starIndex + 1})`);
            });
        });
    }
}

function setTestimonialRating(btn, index, rating) {
    const item = btn.closest('.testimonial-item');
    const ratingInput = item.querySelector('.testimonial-rating-input');
    const ratingDisplay = item.querySelector(`.rating-display-${index}`);
    const ratingButtons = item.querySelectorAll('.testimonial-rating-btn');
    
    // Rating değerini güncelle
    ratingInput.value = rating;
    if (ratingDisplay) {
        ratingDisplay.textContent = `${rating}/5`;
    }
    
    // Yıldızları güncelle
    ratingButtons.forEach((starBtn, idx) => {
        const starValue = idx + 1;
        const icon = starBtn.querySelector('.material-symbols-outlined');
        if (starValue <= rating) {
            starBtn.classList.remove('text-slate-500');
            starBtn.classList.add('text-yellow-400');
            if (icon) icon.style.fontVariationSettings = "'FILL' 1";
        } else {
            starBtn.classList.remove('text-yellow-400');
            starBtn.classList.add('text-slate-500');
            if (icon) icon.style.fontVariationSettings = "'FILL' 0";
        }
    });
}

function selectTestimonialAvatar(index) {
    if (typeof openMediaPicker === 'function') {
        openMediaPicker({
            type: 'image',
            onSelect: (media) => {
                const avatarInput = document.querySelector(`.testimonial-avatar-input[data-index="${index}"]`);
                const avatarPreview = document.querySelector(`.testimonial-avatar-preview[data-index="${index}"]`);
                
                if (avatarInput) {
                    avatarInput.value = media.file_url;
                }
                
                if (avatarPreview) {
                    avatarPreview.innerHTML = `<img src="${media.file_url}" alt="" class="w-full h-full object-cover">`;
                }
            }
        });
    }
}

function removeTestimonialAvatar(index) {
    const avatarInput = document.querySelector(`.testimonial-avatar-input[data-index="${index}"]`);
    const avatarPreview = document.querySelector(`.testimonial-avatar-preview[data-index="${index}"]`);
    const item = avatarInput ? avatarInput.closest('.testimonial-item') : null;
    const nameInput = item ? item.querySelector('input[name*="[name]"]') : null;
    const firstName = nameInput ? nameInput.value.trim().charAt(0).toUpperCase() : 'A';
    
    if (avatarInput) {
        avatarInput.value = '';
    }
    
    if (avatarPreview) {
        avatarPreview.innerHTML = `<span class="text-white font-semibold text-xl">${firstName}</span>`;
    }
}

// Services Item Management
let serviceItemIndex = <?php echo isset($contactPageSections['services']['items']) && is_array($contactPageSections['services']['items']) ? (int)count($contactPageSections['services']['items']) : 4; ?>;

function addServiceItem() {
    const container = document.getElementById('services-items');
    if (!container) return;
    
    const itemHtml = `
        <div class="service-item glass rounded-lg p-4 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-300">Hizmet #${serviceItemIndex + 1}</span>
                <button type="button" onclick="removeServiceItem(this)" class="p-1.5 text-red-400 hover:bg-red-500/10 rounded transition-colors">
                    <span class="material-symbols-outlined text-sm">delete</span>
                </button>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">İkon (Material Symbols)</label>
                <input type="text" name="contact_sections[services][items][${serviceItemIndex}][icon]" value="star" placeholder="home" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                <p class="text-xs text-slate-500 mt-1">Material Symbols ikon adı (örn: home, apartment, assessment)</p>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Hizmet Başlığı</label>
                <input type="text" name="contact_sections[services][items][${serviceItemIndex}][title]" value="" placeholder="Hizmet adı" class="w-full px-4 py-2 input-field rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Link (Opsiyonel)</label>
                <input type="text" name="contact_sections[services][items][${serviceItemIndex}][link]" value="" placeholder="/services/satilik" class="w-full px-4 py-2 input-field rounded-lg text-sm">
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', itemHtml);
    serviceItemIndex++;
}

function removeServiceItem(btn) {
    if (confirm('Bu hizmeti silmek istediğinize emin misiniz?')) {
        btn.closest('.service-item').remove();
        // Index'leri yeniden numaralandır
        const items = document.querySelectorAll('#services-items .service-item');
        items.forEach((item, index) => {
            const numberSpan = item.querySelector('.text-slate-300');
            if (numberSpan) numberSpan.textContent = `Hizmet #${index + 1}`;
            
            // Input name'lerini güncelle
            item.querySelectorAll('input').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    const newName = name.replace(/\[items\]\[\d+\]/, `[items][${index}]`);
                    input.setAttribute('name', newName);
                }
            });
        });
        serviceItemIndex = items.length;
    }
}

// Trust Indicators Functions
let trustIndicatorIndex = <?php echo count($pageSections['cta']['settings']['trust_indicators'] ?? []); ?>;

function addTrustIndicator() {
    const container = document.getElementById('trust-indicators-list');
    if (!container) return;
    
    const index = trustIndicatorIndex;
    const itemHtml = `
        <div class="flex items-center gap-2 trust-indicator-item">
            <input type="text" name="sections[cta][settings][trust_indicators][${index}][text]" 
                   value="" 
                   placeholder="Gösterge metni" 
                   class="flex-1 px-3 py-2 input-field rounded-lg text-sm">
            <button type="button" onclick="removeTrustIndicator(this)" class="p-2 text-red-400 hover:bg-red-500/10 rounded-lg transition-colors">
                <span class="material-symbols-outlined text-sm">delete</span>
            </button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', itemHtml);
    trustIndicatorIndex++;
}

function removeTrustIndicator(btn) {
    if (confirm('Bu göstergiyi silmek istediğinize emin misiniz?')) {
        btn.closest('.trust-indicator-item').remove();
        // Index'leri yeniden numaralandır
        const items = document.querySelectorAll('#trust-indicators-list .trust-indicator-item');
        items.forEach((item, index) => {
            const input = item.querySelector('input[type="text"]');
            if (input) {
                const name = input.getAttribute('name');
                if (name) {
                    const newName = name.replace(/\[trust_indicators\]\[\d+\]/, `[trust_indicators][${index}]`);
                    input.setAttribute('name', newName);
                }
            }
        });
        trustIndicatorIndex = items.length;
    }
}
</script>

</body>
</html>
