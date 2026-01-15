<?php
/**
 * Tema Özelleştirici - Premium UI
 */

$theme = $theme ?? [];
$settings = $settings ?? [];
$themeSlug = $theme['slug'] ?? 'starter';
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
                // settings zaten array ise kullan, değilse JSON decode et
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
                // Items'ı da ekle
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
        error_log("Customize page sections error: " . $e->getMessage());
    }
}

// Mevcut ayarları al
$currentLogo = $settings['branding']['site_logo']['value'] ?? '';
$currentFavicon = $settings['branding']['site_favicon']['value'] ?? '';

// Sözleşmeleri getir (footer alt linkleri için)
$agreements = [];
if (class_exists('Agreement')) {
    try {
        $agreementModel = new Agreement();
        $agreements = $agreementModel->getPublished();
    } catch (Exception $e) {
        error_log("Agreements fetch error: " . $e->getMessage());
    }
}

// Footer alt link ayarları
$footerBottomLinks = [];
if (isset($settings['custom']['footer_bottom_links']['value'])) {
    $value = $settings['custom']['footer_bottom_links']['value'];
    if (is_array($value)) {
        $footerBottomLinks = $value;
    } elseif (is_string($value)) {
        $decoded = json_decode($value, true);
        $footerBottomLinks = is_array($decoded) ? $decoded : [];
    }
}

// Formları getir (İletişim sayfası için)
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
    <title>Tema Özelleştirici - <?php echo esc_html($theme['name'] ?? 'Starter'); ?></title>
    <script src="<?php echo ViewRenderer::assetUrl('assets/js/tailwind-admin.min.js'); ?>"></script>
    <link rel="stylesheet" href="<?php echo ViewRenderer::assetUrl('assets/css/fonts.css'); ?>">
    <script>
        // Font yükleme hatalarını yok say
        window.addEventListener('error', function(e) {
            if (e.target && e.target.tagName === 'LINK' && e.target.href && e.target.href.includes('fonts.css')) {
                console.warn('Font CSS yüklenemedi, fallback kullanılıyor');
                e.preventDefault();
                return false;
            }
            if (e.message && e.message.includes('Failed to decode downloaded font')) {
                console.warn('Font dosyası yüklenemedi, sistem fontu kullanılıyor:', e.message);
                e.preventDefault();
                return false;
            }
        }, true);
    </script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: {
                        brand: { 50: '#eef2ff', 100: '#e0e7ff', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca' }
                    }
                }
            }
        }
    </script>
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
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
        .section-btn.active { background: linear-gradient(135deg, rgba(99,102,241,0.15) 0%, rgba(139,92,246,0.1) 100%); border-color: rgba(99,102,241,0.3); }
        .section-btn.active .section-icon { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; }
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
            border-color: rgba(99,102,241,0.5) !important; 
            outline: none; 
            box-shadow: 0 0 0 3px rgba(99,102,241,0.1); 
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
        .btn-primary { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); }
        .btn-primary:hover { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); transform: translateY(-1px); box-shadow: 0 10px 40px -10px rgba(99,102,241,0.5); }
        @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
        .loading { background: linear-gradient(90deg, rgba(255,255,255,0.05) 25%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.05) 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite; }
        
        /* Media Picker Override for Customize Page */
        #media-picker-modal { z-index: 999999 !important; }
        #media-picker-modal > div:last-child { 
            background: #1e293b !important; 
            color: white !important;
        }
        #media-picker-modal .bg-gray-50 { background: rgba(255,255,255,0.05) !important; }
        #media-picker-modal .bg-white { background: #1e293b !important; }
        #media-picker-modal .text-gray-900 { color: white !important; }
        #media-picker-modal .text-gray-700 { color: #cbd5e1 !important; }
        #media-picker-modal .text-gray-500 { color: #94a3b8 !important; }
        #media-picker-modal .border-gray-200 { border-color: rgba(255,255,255,0.1) !important; }
        #media-picker-modal input, #media-picker-modal button { color: inherit; }
        #media-picker-modal .media-picker-item { background: rgba(255,255,255,0.05) !important; }
    </style>
    <!-- Critical: Define toggleSection immediately -->
    <script>
    // Section Toggle - MUST BE DEFINED BEFORE BODY LOADS
    window.toggleSection = function(sectionId) {
        const panel = document.getElementById(sectionId + '-panel');
        const btn = document.querySelector('[data-section="' + sectionId + '"]');
        
        if (!panel || !btn) {
            console.warn('ToggleSection: Panel or button not found for', sectionId);
            return;
        }
        
        const isOpen = panel.classList.contains('open');
        
        // Close all
        var panels = document.querySelectorAll('.section-panel');
        for (var i = 0; i < panels.length; i++) {
            if (panels[i]) panels[i].classList.remove('open');
        }
        var buttons = document.querySelectorAll('.section-btn');
        for (var i = 0; i < buttons.length; i++) {
            if (buttons[i]) {
                buttons[i].classList.remove('active');
                var arrow = buttons[i].querySelector('.section-arrow');
                if (arrow) arrow.style.transform = 'rotate(0deg)';
            }
        }
        
        // Open clicked
        if (!isOpen) {
            panel.classList.add('open');
            btn.classList.add('active');
            var arrow = btn.querySelector('.section-arrow');
            if (arrow) arrow.style.transform = 'rotate(180deg)';
        }
    };
    console.log('toggleSection function defined in head');
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
                        <p class="text-xs text-slate-400"><?php echo esc_html($theme['name'] ?? 'Starter Theme'); ?></p>
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
                <button onclick="window.toggleSection && window.toggleSection('branding')" class="section-btn active w-full p-4 flex items-center gap-4 hover:bg-white/5 transition-all" data-section="branding">
                    <div class="section-icon w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center">
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
                                <div id="logoPreview" class="w-20 h-20 rounded-xl bg-slate-800/50 border-2 border-dashed border-slate-600 flex items-center justify-center overflow-hidden hover:border-indigo-500/50 transition-colors cursor-pointer" onclick="selectLogo()">
                                    <?php if ($currentLogo): ?>
                                    <img src="<?php echo esc_url($currentLogo); ?>" class="w-full h-full object-contain">
                                    <?php else: ?>
                                    <span class="material-symbols-outlined text-3xl text-slate-500">add_photo_alternate</span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1 space-y-2">
                                    <button onclick="selectLogo()" class="w-full px-4 py-2 text-xs font-medium bg-indigo-500/20 text-indigo-400 rounded-lg hover:bg-indigo-500/30 transition-colors">
                                        Logo Seç
                                    </button>
                                    <button onclick="removeLogo()" class="w-full px-4 py-2 text-xs font-medium text-slate-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors">
                                        Kaldır
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" id="siteLogo" value="<?php echo esc_attr($currentLogo); ?>">
                            
                            <!-- Logo Boyutları -->
                            <div class="mt-4 pt-4 border-t border-slate-700/50">
                                <label class="block text-xs font-medium text-slate-300 mb-3">Logo Boyutları (CLS için önerilir)</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[10px] text-slate-400 mb-1">Genişlik (px)</label>
                                        <input type="number" 
                                               id="logoWidth" 
                                               name="branding[logo_width]" 
                                               value="<?php echo esc_attr($settings['branding']['logo_width']['value'] ?? ''); ?>" 
                                               placeholder="Otomatik"
                                               min="20" 
                                               max="500"
                                               class="w-full px-3 py-2 input-field rounded-lg text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-slate-400 mb-1">Yükseklik (px)</label>
                                        <input type="number" 
                                               id="logoHeight" 
                                               name="branding[logo_height]" 
                                               value="<?php echo esc_attr($settings['branding']['logo_height']['value'] ?? ''); ?>" 
                                               placeholder="Otomatik"
                                               min="20" 
                                               max="200"
                                               class="w-full px-3 py-2 input-field rounded-lg text-sm">
                                    </div>
                                </div>
                                <p class="text-[10px] text-slate-500 mt-2">
                                    <span class="material-symbols-outlined text-xs align-middle">info</span>
                                    Sayfa yükleme kaymasını (CLS) önlemek için boyutları belirtin
                                </p>
                            </div>
                        </div>
                        
                        <!-- Favicon -->
                        <div class="glass rounded-xl p-4">
                            <label class="block text-xs font-medium text-slate-300 mb-3">Favicon</label>
                            <div class="flex items-center gap-4">
                                <div id="faviconPreview" class="w-14 h-14 rounded-xl bg-slate-800/50 border-2 border-dashed border-slate-600 flex items-center justify-center overflow-hidden hover:border-indigo-500/50 transition-colors cursor-pointer" onclick="selectFavicon()">
                                    <?php if ($currentFavicon): ?>
                                    <img src="<?php echo esc_url($currentFavicon); ?>" class="w-full h-full object-contain">
                                    <?php else: ?>
                                    <span class="material-symbols-outlined text-2xl text-slate-500">add_photo_alternate</span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1 space-y-2">
                                    <button onclick="selectFavicon()" class="w-full px-4 py-2 text-xs font-medium bg-indigo-500/20 text-indigo-400 rounded-lg hover:bg-indigo-500/30 transition-colors">
                                        Favicon Seç
                                    </button>
                                    <button onclick="removeFavicon()" class="w-full px-4 py-2 text-xs font-medium text-slate-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors">
                                        Kaldır
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" id="siteFavicon" value="<?php echo esc_attr($currentFavicon); ?>">
                            <p class="text-[10px] text-slate-500 mt-2">Önerilen: 32x32 veya 64x64 piksel</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Header Ayarları -->
            <div class="border-b border-white/5">
                <button onclick="window.toggleSection && window.toggleSection('header')" class="section-btn w-full p-4 flex items-center gap-4 hover:bg-white/5 transition-all" data-section="header">
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
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-2">Header Stili</label>
                                <select name="header[style]" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                    <option value="default" <?php echo ($settings['header']['style']['value'] ?? '') === 'default' ? 'selected' : ''; ?>>Varsayılan</option>
                                    <option value="transparent" <?php echo ($settings['header']['style']['value'] ?? '') === 'transparent' ? 'selected' : ''; ?>>Transparan</option>
                                    <option value="sticky" <?php echo ($settings['header']['style']['value'] ?? '') === 'sticky' ? 'selected' : ''; ?>>Yapışkan (Sticky)</option>
                                    <option value="centered" <?php echo ($settings['header']['style']['value'] ?? '') === 'centered' ? 'selected' : ''; ?>>Ortalı Logo</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-2">Header Arka Plan</label>
                                <div class="flex items-center gap-3">
                                    <input type="color" id="header_bg_color_picker" value="<?php echo esc_attr($settings['header']['bg_color']['value'] ?? '#ffffff'); ?>" class="w-10 h-10 rounded-lg" oninput="document.getElementById('header_bg_color').value = this.value">
                                    <input type="text" name="header[bg_color]" id="header_bg_color" value="<?php echo esc_attr($settings['header']['bg_color']['value'] ?? '#ffffff'); ?>" class="flex-1 px-4 py-2.5 input-field rounded-lg text-sm font-mono" oninput="document.getElementById('header_bg_color_picker').value = this.value">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-2">Metin Rengi</label>
                                <div class="flex items-center gap-3">
                                    <input type="color" id="header_text_color_picker" value="<?php echo esc_attr($settings['header']['text_color']['value'] ?? '#1f2937'); ?>" class="w-10 h-10 rounded-lg" oninput="document.getElementById('header_text_color').value = this.value">
                                    <input type="text" name="header[text_color]" id="header_text_color" value="<?php echo esc_attr($settings['header']['text_color']['value'] ?? '#1f2937'); ?>" class="flex-1 px-4 py-2.5 input-field rounded-lg text-sm font-mono" oninput="document.getElementById('header_text_color_picker').value = this.value">
                                </div>
                            </div>
                            <label class="flex items-center gap-3 cursor-pointer p-3 rounded-lg hover:bg-white/5 transition-colors">
                                <input type="checkbox" name="header[show_search]" value="1" <?php echo ($settings['header']['show_search']['value'] ?? false) ? 'checked' : ''; ?> class="w-5 h-5 rounded bg-slate-700 border-slate-600 text-indigo-500 focus:ring-indigo-500/30">
                                <span class="text-sm">Arama kutusunu göster</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer p-3 rounded-lg hover:bg-white/5 transition-colors">
                                <input type="checkbox" name="header[show_cta]" value="1" <?php echo ($settings['header']['show_cta']['value'] ?? true) ? 'checked' : ''; ?> class="w-5 h-5 rounded bg-slate-700 border-slate-600 text-indigo-500 focus:ring-indigo-500/30">
                                <span class="text-sm">CTA butonunu göster</span>
                            </label>
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-2">CTA Buton Metni</label>
                                <input type="text" name="header[cta_text]" value="<?php echo esc_attr($settings['header']['cta_text']['value'] ?? 'İletişim'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm" placeholder="İletişim">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-2">CTA Buton Linki</label>
                                <input type="text" name="header[cta_link]" value="<?php echo esc_attr($settings['header']['cta_link']['value'] ?? '/contact'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm" placeholder="/contact">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Renkler -->
            <?php if (!empty($settings['colors'])): ?>
            <div class="border-b border-white/5">
                <button onclick="window.toggleSection && window.toggleSection('colors')" class="section-btn w-full p-4 flex items-center gap-4 hover:bg-white/5 transition-all" data-section="colors">
                    <div class="section-icon w-10 h-10 rounded-xl bg-slate-700/50 flex items-center justify-center">
                        <span class="material-symbols-outlined">palette</span>
                    </div>
                    <div class="flex-1 text-left">
                        <span class="text-sm font-semibold block">Renkler</span>
                        <span class="text-xs text-slate-400">Tema renk paleti</span>
                    </div>
                    <span class="material-symbols-outlined text-slate-400 section-arrow transition-transform">expand_more</span>
                </button>
                <div id="colors-panel" class="section-panel">
                    <div class="px-4 pb-5">
                        <div class="glass rounded-xl p-4 space-y-4">
                            <?php foreach ($settings['colors'] as $key => $config): ?>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-300"><?php echo esc_html($config['label']); ?></span>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="colors[<?php echo $key; ?>]" value="<?php echo esc_attr($config['value'] ?? $config['default']); ?>" class="w-10 h-10 rounded-lg">
                                    <input type="text" value="<?php echo esc_attr($config['value'] ?? $config['default']); ?>" class="w-24 px-3 py-2 input-field rounded-lg text-xs font-mono" oninput="this.previousElementSibling.value = this.value">
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Tipografi -->
            <?php if (!empty($settings['fonts'])): ?>
            <div class="border-b border-white/5">
                <button onclick="window.toggleSection && window.toggleSection('fonts')" class="section-btn w-full p-4 flex items-center gap-4 hover:bg-white/5 transition-all" data-section="fonts">
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
                            $fonts = $availableFonts ?? ['Inter', 'Poppins', 'Roboto', 'Open Sans', 'Lato', 'Montserrat', 'Raleway', 'Nunito', 'DM Sans'];
                            foreach ($settings['fonts'] as $key => $config): 
                            ?>
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-2"><?php echo esc_html($config['label']); ?></label>
                                <select name="fonts[<?php echo $key; ?>]" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                    <?php foreach ($fonts as $fontKey => $fontName): 
                                        // $fonts array'i key-value çifti olabilir veya sadece değerler olabilir
                                        $fontValue = is_numeric($fontKey) ? $fontName : $fontKey;
                                        $fontDisplay = is_numeric($fontKey) ? $fontName : $fontName;
                                    ?>
                                    <option value="<?php echo esc_attr($fontValue); ?>" <?php echo ($config['value'] ?? $config['default']) === $fontValue ? 'selected' : ''; ?> style="font-family: <?php echo esc_attr($fontDisplay); ?>"><?php echo esc_html($fontDisplay); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Footer Ayarları -->
            <div class="border-b border-white/5">
                <button onclick="window.toggleSection && window.toggleSection('footer')" class="section-btn w-full p-4 flex items-center gap-4 hover:bg-white/5 transition-all" data-section="footer">
                    <div class="section-icon w-10 h-10 rounded-xl bg-slate-700/50 flex items-center justify-center">
                        <span class="material-symbols-outlined">call_to_action</span>
                    </div>
                    <div class="flex-1 text-left">
                        <span class="text-sm font-semibold block">Footer</span>
                        <span class="text-xs text-slate-400">Alt bilgi ayarları</span>
                    </div>
                    <span class="material-symbols-outlined text-slate-400 section-arrow transition-transform">expand_more</span>
                </button>
                <div id="footer-panel" class="section-panel">
                    <div class="px-4 pb-5 space-y-4">
                        <div class="glass rounded-xl p-4 space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-2">Footer Stili</label>
                                <select name="footer[style]" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                    <option value="default">Varsayılan</option>
                                    <option value="minimal">Minimal</option>
                                    <option value="centered">Ortalı</option>
                                    <option value="dark">Koyu</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-2">Telif Hakkı Metni</label>
                                <input type="text" name="footer[copyright]" value="<?php echo esc_attr($settings['footer']['copyright']['value'] ?? '© 2025 Tüm hakları saklıdır.'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                            </div>
                            <label class="flex items-center gap-3 cursor-pointer p-3 rounded-lg hover:bg-white/5 transition-colors">
                                <input type="checkbox" name="footer[show_social]" value="1" <?php echo ($settings['footer']['show_social']['value'] ?? true) ? 'checked' : ''; ?> class="w-5 h-5 rounded bg-slate-700 border-slate-600 text-indigo-500 focus:ring-indigo-500/30">
                                <span class="text-sm">Sosyal medya ikonlarını göster</span>
                            </label>
                            <p class="text-xs text-slate-400 mt-2">Not: Sosyal medya linkleri Site Ayarları > Sosyal Medya bölümünden yönetilir.</p>
                        </div>
                        
                        <!-- Footer Alt Linkler -->
                        <div class="glass rounded-xl p-4 space-y-4">
                            <h3 class="text-sm font-semibold text-slate-200 mb-3">Alt Linkler (Sözleşmeler)</h3>
                            <p class="text-xs text-slate-400 mb-4">Footer'ın alt kısmında gösterilecek sözleşme linklerini seçin.</p>
                            
                            <div id="footer-bottom-links-container" class="space-y-3">
                                <?php 
                                $linkIndex = 0;
                                if (!empty($footerBottomLinks)): 
                                    foreach ($footerBottomLinks as $link): 
                                ?>
                                <div class="footer-bottom-link-item flex items-center gap-3 p-3 rounded-lg bg-slate-800/50">
                                    <div class="flex-1 grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs text-slate-400 mb-1.5">Link Metni</label>
                                            <input type="text" name="custom[footer_bottom_links][<?php echo $linkIndex; ?>][text]" 
                                                   value="<?php echo esc_attr($link['text'] ?? ''); ?>" 
                                                   placeholder="Gizlilik Politikası" 
                                                   class="w-full px-3 py-2 input-field rounded-lg text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-slate-400 mb-1.5">Sözleşme Seç</label>
                                            <select name="custom[footer_bottom_links][<?php echo $linkIndex; ?>][agreement_id]" 
                                                    class="w-full px-3 py-2 input-field rounded-lg text-sm footer-agreement-select">
                                                <option value="">Özel URL kullan</option>
                                                <?php foreach ($agreements as $agreement): ?>
                                                <option value="<?php echo $agreement['id']; ?>" 
                                                        data-slug="<?php echo esc_attr($agreement['slug']); ?>"
                                                        <?php echo (isset($link['agreement_id']) && $link['agreement_id'] == $agreement['id']) ? 'selected' : ''; ?>>
                                                    <?php 
                                                    $typeLabel = $agreement['type'];
                                                    if (class_exists('Agreement') && isset(Agreement::$types[$agreement['type']])) {
                                                        $typeLabel = Agreement::$types[$agreement['type']];
                                                    }
                                                    echo esc_html($agreement['title'] . ' (' . $typeLabel . ')'); 
                                                    ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-xs text-slate-400 mb-1.5">Özel URL (Sözleşme seçilmediyse)</label>
                                        <input type="text" name="custom[footer_bottom_links][<?php echo $linkIndex; ?>][url]" 
                                               value="<?php echo esc_attr($link['url'] ?? ''); ?>" 
                                               placeholder="/gizlilik-politikasi" 
                                               class="w-full px-3 py-2 input-field rounded-lg text-sm">
                                    </div>
                                    <button type="button" onclick="removeFooterLink(this)" class="p-2 text-red-400 hover:bg-red-500/10 rounded-lg transition-colors">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </div>
                                <?php 
                                    $linkIndex++;
                                    endforeach; 
                                endif; 
                                ?>
                            </div>
                            
                            <button type="button" onclick="addFooterLink()" class="w-full px-4 py-2.5 bg-indigo-500/20 text-indigo-400 rounded-lg hover:bg-indigo-500/30 transition-colors flex items-center justify-center gap-2 text-sm">
                                <span class="material-symbols-outlined text-lg">add</span>
                                Link Ekle
                            </button>
                            
                            <p class="text-xs text-slate-400 mt-2">💡 İpucu: Sözleşme seçerseniz URL otomatik oluşturulur. Özel URL de girebilirsiniz.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Ana Sayfa Bölümleri -->
            <div class="border-b border-white/5">
                <button onclick="window.toggleSection && window.toggleSection('homepage')" class="section-btn w-full p-4 flex items-center gap-4 hover:bg-white/5 transition-all" data-section="homepage">
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
                                    <label class="block text-xs text-slate-400 mb-1.5">Başlık</label>
                                    <input type="text" name="sections[hero][title]" value="<?php echo esc_attr($pageSections['hero']['title'] ?? 'Modern Tasarım'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Alt Başlık</label>
                                    <textarea name="sections[hero][subtitle]" rows="2" class="w-full px-4 py-2.5 input-field rounded-lg text-sm resize-none"><?php echo esc_html($pageSections['hero']['subtitle'] ?? ''); ?></textarea>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1.5">Buton</label>
                                        <input type="text" name="sections[hero][button_text]" value="<?php echo esc_attr($pageSections['hero']['button_text'] ?? 'Başla'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1.5">Link</label>
                                        <input type="text" name="sections[hero][button_link]" value="<?php echo esc_attr($pageSections['hero']['button_link'] ?? '/contact'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                    </div>
                                </div>
                            </div>
                        </details>
                        
                        <!-- Features -->
                        <details class="glass rounded-xl overflow-hidden group">
                            <summary class="flex items-center justify-between p-4 cursor-pointer hover:bg-white/5 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-sm">⭐</span>
                                    <span class="text-sm font-medium">Özellikler</span>
                                </div>
                                <span class="material-symbols-outlined text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
                            </summary>
                            <div class="p-4 pt-0 space-y-4 border-t border-white/5">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Başlık</label>
                                    <input type="text" name="sections[features][title]" value="<?php echo esc_attr($pageSections['features']['title'] ?? 'Özelliklerimiz'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Alt Başlık</label>
                                    <input type="text" name="sections[features][subtitle]" value="<?php echo esc_attr($pageSections['features']['subtitle'] ?? 'Müşterilerimize en iyi deneyimi sunmak için çalışıyoruz.'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Kolon Sayısı</label>
                                    <?php 
                                    $featuresColumns = isset($pageSections['features']['columns']) ? (string)$pageSections['features']['columns'] : '3';
                                    ?>
                                    <select name="sections[features][columns]" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                        <option value="2" <?php echo $featuresColumns === '2' ? 'selected' : ''; ?>>2 Kolon</option>
                                        <option value="3" <?php echo $featuresColumns === '3' ? 'selected' : ''; ?>>3 Kolon</option>
                                        <option value="4" <?php echo $featuresColumns === '4' ? 'selected' : ''; ?>>4 Kolon</option>
                                    </select>
                                </div>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="sections[features][enabled]" value="1" <?php echo ($pageSections['features']['enabled'] ?? true) ? 'checked' : ''; ?> class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500">
                                    <span class="text-xs text-slate-400">Bu bölümü göster</span>
                                </label>
                                
                                <!-- Features Items -->
                                <div class="border-t border-white/5 pt-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <label class="block text-xs font-medium text-slate-300">Özellik Öğeleri</label>
                                        <button type="button" onclick="addFeatureItem()" class="px-3 py-1.5 text-xs font-medium bg-indigo-500/20 text-indigo-400 rounded-lg hover:bg-indigo-500/30 transition-colors flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm">add</span>
                                            Ekle
                                        </button>
                                    </div>
                                    <div id="features-items" class="space-y-3">
                                        <?php 
                                        $featuresItems = $pageSections['features']['items'] ?? [
                                            ['icon' => 'rocket_launch', 'title' => 'Hızlı Performans', 'description' => 'Optimize edilmiş kod yapısı ile yüksek performans.'],
                                            ['icon' => 'palette', 'title' => 'Modern Tasarım', 'description' => 'Güncel trendlere uygun şık ve modern görünüm.'],
                                            ['icon' => 'devices', 'title' => 'Responsive', 'description' => 'Tüm cihazlarda mükemmel görünüm.']
                                        ];
                                        foreach ($featuresItems as $index => $item): 
                                        ?>
                                        <div class="feature-item glass rounded-lg p-4 space-y-3">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-medium text-slate-300">Öğe #<?php echo $index + 1; ?></span>
                                                <button type="button" onclick="removeFeatureItem(this)" class="p-1.5 text-red-400 hover:bg-red-500/10 rounded transition-colors">
                                                    <span class="material-symbols-outlined text-sm">delete</span>
                                                </button>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">İkon (Material Symbols)</label>
                                                <div class="flex gap-2">
                                                    <input type="text" name="sections[features][items][<?php echo $index; ?>][icon]" value="<?php echo esc_attr($item['icon'] ?? 'star'); ?>" placeholder="rocket_launch" class="flex-1 px-4 py-2 input-field rounded-lg text-sm icon-input" data-icon-index="<?php echo $index; ?>">
                                                    <button type="button" onclick="openIconPicker(<?php echo $index; ?>)" class="px-4 py-2 bg-indigo-500/20 text-indigo-400 rounded-lg hover:bg-indigo-500/30 transition-colors flex items-center gap-2 text-sm">
                                                        <span class="material-symbols-outlined text-lg">palette</span>
                                                        Seç
                                                    </button>
                                                </div>
                                                <div class="mt-2 flex items-center gap-2">
                                                    <span class="material-symbols-outlined text-xl text-slate-400 icon-preview-<?php echo $index; ?>"><?php echo esc_html($item['icon'] ?? 'star'); ?></span>
                                                    <span class="text-[10px] text-slate-500">Önizleme</span>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">Başlık</label>
                                                <input type="text" name="sections[features][items][<?php echo $index; ?>][title]" value="<?php echo esc_attr($item['title'] ?? ''); ?>" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">Açıklama</label>
                                                <textarea name="sections[features][items][<?php echo $index; ?>][description]" rows="2" class="w-full px-4 py-2 input-field rounded-lg text-sm resize-none"><?php echo esc_html($item['description'] ?? ''); ?></textarea>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">Link (Opsiyonel)</label>
                                                <input type="text" name="sections[features][items][<?php echo $index; ?>][link]" value="<?php echo esc_attr($item['link'] ?? ''); ?>" placeholder="/services" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </details>
                        
                        <!-- About -->
                        <details class="glass rounded-xl overflow-hidden group">
                            <summary class="flex items-center justify-between p-4 cursor-pointer hover:bg-white/5 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-sm">📖</span>
                                    <span class="text-sm font-medium">Hakkımızda</span>
                                </div>
                                <span class="material-symbols-outlined text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
                            </summary>
                            <div class="p-4 pt-0 space-y-3 border-t border-white/5">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Başlık</label>
                                    <input type="text" name="sections[about][title]" value="<?php echo esc_attr($pageSections['about']['title'] ?? 'Hakkımızda'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">İçerik</label>
                                    <textarea name="sections[about][content]" rows="3" class="w-full px-4 py-2.5 input-field rounded-lg text-sm resize-none"><?php echo esc_html($pageSections['about']['content'] ?? ''); ?></textarea>
                                </div>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="sections[about][enabled]" value="1" <?php echo ($pageSections['about']['enabled'] ?? true) ? 'checked' : ''; ?> class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500">
                                    <span class="text-xs text-slate-400">Bu bölümü göster</span>
                                </label>
                            </div>
                        </details>
                        
                        <!-- Pricing -->
                        <details class="glass rounded-xl overflow-hidden group">
                            <summary class="flex items-center justify-between p-4 cursor-pointer hover:bg-white/5 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-sm">💰</span>
                                    <span class="text-sm font-medium">Pricing</span>
                                </div>
                                <span class="material-symbols-outlined text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
                            </summary>
                            <div class="p-4 pt-0 space-y-4 border-t border-white/5">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Başlık</label>
                                    <input type="text" name="sections[pricing][title]" value="<?php echo esc_attr($pageSections['pricing']['title'] ?? 'Paketlerimiz'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Alt Başlık</label>
                                    <input type="text" name="sections[pricing][subtitle]" value="<?php echo esc_attr($pageSections['pricing']['subtitle'] ?? 'İhtiyacınıza uygun paketi seçin ve dijital dönüşümünüze başlayın.'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Badge</label>
                                    <input type="text" name="sections[pricing][settings][badge]" value="<?php echo esc_attr($pageSections['pricing']['settings']['badge'] ?? 'Fiyatlandırma'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="sections[pricing][enabled]" value="1" <?php echo ($pageSections['pricing']['enabled'] ?? true) ? 'checked' : ''; ?> class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500">
                                    <span class="text-xs text-slate-400">Bu bölümü göster</span>
                                </label>
                                
                                <!-- Pricing Packages -->
                                <div class="border-t border-white/5 pt-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <label class="block text-xs font-medium text-slate-300">Paketler</label>
                                        <button type="button" onclick="addPricingPackage()" class="px-3 py-1.5 text-xs font-medium bg-indigo-500/20 text-indigo-400 rounded-lg hover:bg-indigo-500/30 transition-colors flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm">add</span>
                                            Paket Ekle
                                        </button>
                                    </div>
                                    <div id="pricing-packages" class="space-y-3">
                                        <?php 
                                        $pricingPackages = $pageSections['pricing']['packages'] ?? [
                                            [
                                                'name' => 'Başlangıç',
                                                'price' => '₺2.500',
                                                'period' => '/ay',
                                                'description' => 'Küçük işletmeler ve kişisel projeler için ideal başlangıç paketi.',
                                                'features' => ['5 Sayfa', 'Temel SEO', 'E-posta Desteği', 'SSL Sertifikası', 'Mobil Uyumlu Tasarım'],
                                                'button_text' => 'Başla',
                                                'button_link' => '/contact',
                                                'popular' => false,
                                                'gradient' => 'from-slate-500 to-slate-600'
                                            ],
                                            [
                                                'name' => 'Profesyonel',
                                                'price' => '₺5.000',
                                                'period' => '/ay',
                                                'description' => 'Büyüyen işletmeler için gelişmiş özellikler ve destek.',
                                                'features' => ['15 Sayfa', 'Gelişmiş SEO', 'Öncelikli Destek', 'SSL Sertifikası', 'Mobil Uyumlu Tasarım', 'Sosyal Medya Entegrasyonu', 'Analytics Entegrasyonu'],
                                                'button_text' => 'Başla',
                                                'button_link' => '/contact',
                                                'popular' => true,
                                                'gradient' => 'from-blue-500 to-purple-600'
                                            ],
                                            [
                                                'name' => 'Kurumsal',
                                                'price' => '₺10.000',
                                                'period' => '/ay',
                                                'description' => 'Büyük işletmeler için özel çözümler ve özel destek.',
                                                'features' => ['Sınırsız Sayfa', 'Premium SEO', '7/24 Öncelikli Destek', 'SSL Sertifikası', 'Mobil Uyumlu Tasarım', 'Sosyal Medya Entegrasyonu', 'Analytics Entegrasyonu', 'Özel Tasarım', 'API Entegrasyonları'],
                                                'button_text' => 'Başla',
                                                'button_link' => '/contact',
                                                'popular' => false,
                                                'gradient' => 'from-violet-500 to-purple-600'
                                            ],
                                            [
                                                'name' => 'Özel Çözüm',
                                                'price' => 'Özel Fiyat',
                                                'period' => '',
                                                'description' => 'Özel ihtiyaçlarınız için özelleştirilmiş çözümler.',
                                                'features' => ['Tam Özelleştirme', 'Özel Geliştirme', 'Dedike Destek', 'Tüm Özellikler', 'Özel Entegrasyonlar', 'Danışmanlık Hizmeti', 'Öncelikli Güncellemeler'],
                                                'button_text' => 'İletişime Geç',
                                                'button_link' => '/contact',
                                                'popular' => false,
                                                'gradient' => 'from-amber-500 to-orange-600'
                                            ]
                                        ];
                                        foreach ($pricingPackages as $index => $package): 
                                            $packageFeatures = is_array($package['features'] ?? []) ? $package['features'] : [];
                                        ?>
                                        <div class="pricing-package-item glass rounded-lg p-4 space-y-3">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-medium text-slate-300">Paket #<?php echo $index + 1; ?></span>
                                                <button type="button" onclick="removePricingPackage(this)" class="p-1.5 text-red-400 hover:bg-red-500/10 rounded transition-colors">
                                                    <span class="material-symbols-outlined text-sm">delete</span>
                                                </button>
                                            </div>
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-xs text-slate-400 mb-1.5">Paket Adı</label>
                                                    <input type="text" name="sections[pricing][packages][<?php echo $index; ?>][name]" value="<?php echo esc_attr($package['name'] ?? ''); ?>" placeholder="Başlangıç" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs text-slate-400 mb-1.5">Fiyat</label>
                                                    <input type="text" name="sections[pricing][packages][<?php echo $index; ?>][price]" value="<?php echo esc_attr($package['price'] ?? ''); ?>" placeholder="₺2.500" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-xs text-slate-400 mb-1.5">Periyot</label>
                                                    <input type="text" name="sections[pricing][packages][<?php echo $index; ?>][period]" value="<?php echo esc_attr($package['period'] ?? ''); ?>" placeholder="/ay, /yıl" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs text-slate-400 mb-1.5">Gradient</label>
                                                    <select name="sections[pricing][packages][<?php echo $index; ?>][gradient]" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                                                        <option value="from-slate-500 to-slate-600" <?php echo ($package['gradient'] ?? '') === 'from-slate-500 to-slate-600' ? 'selected' : ''; ?>>Slate</option>
                                                        <option value="from-blue-500 to-purple-600" <?php echo ($package['gradient'] ?? '') === 'from-blue-500 to-purple-600' ? 'selected' : ''; ?>>Blue-Purple</option>
                                                        <option value="from-violet-500 to-purple-600" <?php echo ($package['gradient'] ?? '') === 'from-violet-500 to-purple-600' ? 'selected' : ''; ?>>Violet-Purple</option>
                                                        <option value="from-emerald-500 to-teal-600" <?php echo ($package['gradient'] ?? '') === 'from-emerald-500 to-teal-600' ? 'selected' : ''; ?>>Emerald-Teal</option>
                                                        <option value="from-amber-500 to-orange-600" <?php echo ($package['gradient'] ?? '') === 'from-amber-500 to-orange-600' ? 'selected' : ''; ?>>Amber-Orange</option>
                                                        <option value="from-pink-500 to-rose-600" <?php echo ($package['gradient'] ?? '') === 'from-pink-500 to-rose-600' ? 'selected' : ''; ?>>Pink-Rose</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">Açıklama</label>
                                                <textarea name="sections[pricing][packages][<?php echo $index; ?>][description]" rows="2" class="w-full px-4 py-2 input-field rounded-lg text-sm resize-none" placeholder="Paket açıklaması..."><?php echo esc_html($package['description'] ?? ''); ?></textarea>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">Özellikler</label>
                                                <div id="pricing-features-<?php echo $index; ?>" class="space-y-2 mb-2">
                                                    <?php foreach ($packageFeatures as $featureIndex => $feature): ?>
                                                    <div class="flex items-center gap-2 pricing-feature-item">
                                                        <input type="text" name="sections[pricing][packages][<?php echo $index; ?>][features][<?php echo $featureIndex; ?>]" value="<?php echo esc_attr($feature); ?>" placeholder="Özellik adı" class="flex-1 px-4 py-2 input-field rounded-lg text-sm">
                                                        <button type="button" onclick="removePricingFeature(this)" class="p-2 text-red-400 hover:bg-red-500/10 rounded transition-colors">
                                                            <span class="material-symbols-outlined text-sm">close</span>
                                                        </button>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <button type="button" onclick="addPricingFeature(<?php echo $index; ?>)" class="w-full px-4 py-2 text-xs font-medium bg-slate-700/50 text-slate-300 rounded-lg hover:bg-slate-700 transition-colors flex items-center justify-center gap-1">
                                                    <span class="material-symbols-outlined text-sm">add</span>
                                                    Özellik Ekle
                                                </button>
                                            </div>
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-xs text-slate-400 mb-1.5">Buton Metni</label>
                                                    <input type="text" name="sections[pricing][packages][<?php echo $index; ?>][button_text]" value="<?php echo esc_attr($package['button_text'] ?? 'Başla'); ?>" placeholder="Başla" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs text-slate-400 mb-1.5">Buton Linki</label>
                                                    <input type="text" name="sections[pricing][packages][<?php echo $index; ?>][button_link]" value="<?php echo esc_attr($package['button_link'] ?? '/contact'); ?>" placeholder="/contact" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                                                </div>
                                            </div>
                                            <label class="flex items-center gap-3 cursor-pointer p-2 rounded-lg hover:bg-white/5 transition-colors">
                                                <input type="checkbox" name="sections[pricing][packages][<?php echo $index; ?>][popular]" value="1" <?php echo (!empty($package['popular'])) ? 'checked' : ''; ?> class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500">
                                                <span class="text-xs text-slate-400">Popüler Paket</span>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </details>
                        
                        <!-- Glowing Features -->
                        <details class="glass rounded-xl overflow-hidden group">
                            <summary class="flex items-center justify-between p-4 cursor-pointer hover:bg-white/5 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-sm">✨</span>
                                    <span class="text-sm font-medium">Glowing Features</span>
                                </div>
                                <span class="material-symbols-outlined text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
                            </summary>
                            <div class="p-4 pt-0 space-y-4 border-t border-white/5">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Başlık</label>
                                    <input type="text" name="sections[glowing-features][title]" value="<?php echo esc_attr($pageSections['glowing-features']['title'] ?? 'Özelliklerimiz'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Alt Başlık</label>
                                    <input type="text" name="sections[glowing-features][subtitle]" value="<?php echo esc_attr($pageSections['glowing-features']['subtitle'] ?? 'Yenilikçi çözümlerimizle işletmenizi dijital dünyada bir adım öne taşıyın.'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Badge</label>
                                    <input type="text" name="sections[glowing-features][settings][badge]" value="<?php echo esc_attr($pageSections['glowing-features']['settings']['badge'] ?? 'Neden Biz?'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="sections[glowing-features][enabled]" value="1" <?php echo ($pageSections['glowing-features']['enabled'] ?? true) ? 'checked' : ''; ?> class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500">
                                    <span class="text-xs text-slate-400">Bu bölümü göster</span>
                                </label>
                                
                                <!-- Glowing Features Items -->
                                <div class="border-t border-white/5 pt-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <label class="block text-xs font-medium text-slate-300">Özellik Öğeleri</label>
                                        <button type="button" onclick="addGlowingFeatureItem()" class="px-3 py-1.5 text-xs font-medium bg-indigo-500/20 text-indigo-400 rounded-lg hover:bg-indigo-500/30 transition-colors flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm">add</span>
                                            Ekle
                                        </button>
                                    </div>
                                    <div id="glowing-features-items" class="space-y-3">
                                        <?php 
                                        $glowingFeaturesItems = $pageSections['glowing-features']['items'] ?? [
                                            [
                                                'icon' => 'rocket',
                                                'title' => 'Hızlı Geliştirme',
                                                'description' => 'Modern araçlar ve metodolojilerle projelerinizi hızla hayata geçiriyoruz. Agile yaklaşımımızla sürekli değer üretiyoruz.',
                                                'gradient' => 'from-violet-500 to-purple-600'
                                            ],
                                            [
                                                'icon' => 'shield',
                                                'title' => 'Güvenli Altyapı',
                                                'description' => 'En güncel güvenlik standartları ve best practice\'ler ile verilerinizi koruyoruz. SSL, şifreleme ve düzenli güvenlik taramaları.',
                                                'gradient' => 'from-emerald-500 to-teal-600'
                                            ],
                                            [
                                                'icon' => 'code',
                                                'title' => 'Temiz Kod',
                                                'description' => 'Okunabilir, sürdürülebilir ve ölçeklenebilir kod yazıyoruz. SOLID prensipleri ve modern mimari desenler kullanıyoruz.',
                                                'gradient' => 'from-blue-500 to-cyan-600'
                                            ],
                                            [
                                                'icon' => 'zap',
                                                'title' => 'Yüksek Performans',
                                                'description' => 'Optimize edilmiş kod, CDN entegrasyonu ve caching stratejileri ile maksimum hız sağlıyoruz.',
                                                'gradient' => 'from-amber-500 to-orange-600'
                                            ],
                                            [
                                                'icon' => 'users',
                                                'title' => '7/24 Destek',
                                                'description' => 'Uzman ekibimiz her zaman yanınızda. Teknik destek, danışmanlık ve eğitim hizmetleri sunuyoruz.',
                                                'gradient' => 'from-pink-500 to-rose-600'
                                            ]
                                        ];
                                        foreach ($glowingFeaturesItems as $index => $item): 
                                        ?>
                                        <div class="glowing-feature-item glass rounded-lg p-4 space-y-3">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-medium text-slate-300">Öğe #<?php echo $index + 1; ?></span>
                                                <button type="button" onclick="removeGlowingFeatureItem(this)" class="p-1.5 text-red-400 hover:bg-red-500/10 rounded transition-colors">
                                                    <span class="material-symbols-outlined text-sm">delete</span>
                                                </button>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">İkon</label>
                                                <div class="flex gap-2">
                                                    <input type="text" name="sections[glowing-features][items][<?php echo $index; ?>][icon]" value="<?php echo esc_attr($item['icon'] ?? 'rocket'); ?>" placeholder="rocket" class="flex-1 px-4 py-2 input-field rounded-lg text-sm">
                                                </div>
                                                <div class="mt-2 text-xs text-slate-500">
                                                    Mevcut ikonlar: rocket, shield, code, zap, users, box, settings, lock, sparkles, search, chart, globe
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">Başlık</label>
                                                <input type="text" name="sections[glowing-features][items][<?php echo $index; ?>][title]" value="<?php echo esc_attr($item['title'] ?? ''); ?>" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">Açıklama</label>
                                                <textarea name="sections[glowing-features][items][<?php echo $index; ?>][description]" rows="2" class="w-full px-4 py-2 input-field rounded-lg text-sm resize-none"><?php echo esc_html($item['description'] ?? ''); ?></textarea>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-400 mb-1.5">Gradient</label>
                                                <select name="sections[glowing-features][items][<?php echo $index; ?>][gradient]" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                                                    <option value="from-violet-500 to-purple-600" <?php echo ($item['gradient'] ?? '') === 'from-violet-500 to-purple-600' ? 'selected' : ''; ?>>Violet-Purple</option>
                                                    <option value="from-emerald-500 to-teal-600" <?php echo ($item['gradient'] ?? '') === 'from-emerald-500 to-teal-600' ? 'selected' : ''; ?>>Emerald-Teal</option>
                                                    <option value="from-blue-500 to-cyan-600" <?php echo ($item['gradient'] ?? '') === 'from-blue-500 to-cyan-600' ? 'selected' : ''; ?>>Blue-Cyan</option>
                                                    <option value="from-amber-500 to-orange-600" <?php echo ($item['gradient'] ?? '') === 'from-amber-500 to-orange-600' ? 'selected' : ''; ?>>Amber-Orange</option>
                                                    <option value="from-pink-500 to-rose-600" <?php echo ($item['gradient'] ?? '') === 'from-pink-500 to-rose-600' ? 'selected' : ''; ?>>Pink-Rose</option>
                                                    <option value="from-red-500 to-pink-600" <?php echo ($item['gradient'] ?? '') === 'from-red-500 to-pink-600' ? 'selected' : ''; ?>>Red-Pink</option>
                                                    <option value="from-green-500 to-emerald-600" <?php echo ($item['gradient'] ?? '') === 'from-green-500 to-emerald-600' ? 'selected' : ''; ?>>Green-Emerald</option>
                                                    <option value="from-indigo-500 to-blue-600" <?php echo ($item['gradient'] ?? '') === 'from-indigo-500 to-blue-600' ? 'selected' : ''; ?>>Indigo-Blue</option>
                                                </select>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </details>
                        
                    </div>
                </div>
            </div>
            
            <!-- İletişim Sayfası Bölümleri -->
            <div class="border-b border-white/5">
                <button onclick="window.toggleSection && window.toggleSection('contact')" class="section-btn w-full p-4 flex items-center gap-4 hover:bg-white/5 transition-all" data-section="contact">
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
                        
                        <!-- Why Choose Us Section -->
                        <details class="glass rounded-xl overflow-hidden group">
                            <summary class="flex items-center justify-between p-4 cursor-pointer hover:bg-white/5 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-sm">⭐</span>
                                    <span class="text-sm font-medium">Neden Bizi Tercih Etmelisiniz?</span>
                                </div>
                                <span class="material-symbols-outlined text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
                            </summary>
                            <div class="p-4 pt-0 space-y-3 border-t border-white/5">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Bölüm Başlığı</label>
                                    <input type="text" name="contact_sections[why-choose-us][title]" value="<?php echo esc_attr($contactPageSections['why-choose-us']['title'] ?? 'Neden Bizi Tercih Etmelisiniz?'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                
                                <!-- Items -->
                                <div class="border-t border-white/5 pt-3">
                                    <div class="flex items-center justify-between mb-3">
                                        <label class="block text-xs font-medium text-slate-300">Avantaj Öğeleri</label>
                                        <button type="button" onclick="addWhyChooseItem()" class="px-3 py-1.5 text-xs font-medium bg-indigo-500/20 text-indigo-400 rounded-lg hover:bg-indigo-500/30 transition-colors flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm">add</span>
                                            Ekle
                                        </button>
                                    </div>
                                    <div id="why-choose-items" class="space-y-3">
                                        <?php 
                                        $whyChooseItems = isset($contactPageSections['why-choose-us']['items']) && is_array($contactPageSections['why-choose-us']['items']) 
                                            ? $contactPageSections['why-choose-us']['items'] 
                                            : [
                                                ['text' => '500+ aktif mülk seçeneği'],
                                                ['text' => 'Deneyimli ve sertifikalı danışmanlar'],
                                                ['text' => 'Şeffaf fiyatlandırma ve güvenli işlem'],
                                                ['text' => '7/24 müşteri desteği ve hızlı yanıt']
                                            ];
                                        foreach ($whyChooseItems as $index => $item): 
                                        ?>
                                        <div class="flex items-start gap-2 why-choose-item">
                                            <input type="text" name="contact_sections[why-choose-us][items][<?php echo $index; ?>][text]" 
                                                   value="<?php echo esc_attr($item['text'] ?? ''); ?>" 
                                                   placeholder="Avantaj metni" 
                                                   class="flex-1 px-4 py-2 input-field rounded-lg text-sm">
                                            <button type="button" onclick="removeWhyChooseItem(this)" class="p-2 text-red-400 hover:bg-red-500/10 rounded-lg transition-colors">
                                                <span class="material-symbols-outlined text-sm">delete</span>
                                            </button>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="contact_sections[why-choose-us][enabled]" value="1" <?php echo ($contactPageSections['why-choose-us']['enabled'] ?? true) ? 'checked' : ''; ?> class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500">
                                    <span class="text-xs text-slate-400">Bu bölümü göster</span>
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
            
            <!-- Özel CSS -->
            <div class="border-b border-white/5">
                <button onclick="window.toggleSection && window.toggleSection('css')" class="section-btn w-full p-4 flex items-center gap-4 hover:bg-white/5 transition-all" data-section="css">
                    <div class="section-icon w-10 h-10 rounded-xl bg-slate-700/50 flex items-center justify-center">
                        <span class="material-symbols-outlined">code</span>
                    </div>
                    <div class="flex-1 text-left">
                        <span class="text-sm font-semibold block">Özel CSS</span>
                        <span class="text-xs text-slate-400">Gelişmiş stil düzenlemeleri</span>
                    </div>
                    <span class="material-symbols-outlined text-slate-400 section-arrow transition-transform">expand_more</span>
                </button>
                <div id="css-panel" class="section-panel">
                    <div class="px-4 pb-5">
                        <div class="glass rounded-xl p-4">
                            <textarea id="customCss" rows="10" class="w-full px-4 py-3 bg-slate-950 border border-slate-700 rounded-lg text-sm font-mono text-emerald-400 resize-none focus:outline-none focus:border-indigo-500" placeholder="/* Özel CSS kodunuz */"><?php echo esc_html($customCss ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        
    </aside>
    
    <!-- Preview -->
    <main class="flex-1 flex flex-col bg-slate-950/50">
        
        <!-- Toolbar -->
        <div class="flex items-center justify-between px-5 py-3 border-b border-white/5 bg-slate-900/50">
            <div class="flex items-center gap-2 bg-slate-800/50 rounded-xl p-1">
                <button onclick="setDevice('desktop')" class="device-btn active px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-all" data-device="desktop">
                    <span class="material-symbols-outlined text-lg">computer</span>
                    <span class="hidden sm:inline">Masaüstü</span>
                </button>
                <button onclick="setDevice('tablet')" class="device-btn px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-all" data-device="tablet">
                    <span class="material-symbols-outlined text-lg">tablet</span>
                    <span class="hidden sm:inline">Tablet</span>
                </button>
                <button onclick="setDevice('mobile')" class="device-btn px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-all" data-device="mobile">
                    <span class="material-symbols-outlined text-lg">smartphone</span>
                    <span class="hidden sm:inline">Mobil</span>
                </button>
            </div>
            <div class="flex items-center gap-3">
                <span id="previewStatus" class="text-xs text-slate-500"></span>
                <button onclick="refreshPreview()" class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-slate-800 transition-colors" title="Yenile">
                    <span class="material-symbols-outlined">refresh</span>
                </button>
                <a href="<?php echo site_url(); ?>" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-slate-800 transition-colors" title="Siteyi Aç">
                    <span class="material-symbols-outlined">open_in_new</span>
                </a>
            </div>
        </div>
        
        <!-- Frame -->
        <div class="flex-1 flex items-center justify-center p-6 overflow-auto">
            <div id="previewWrapper" class="w-full h-full bg-white rounded-2xl shadow-2xl overflow-hidden transition-all duration-500 ring-1 ring-white/10">
                <iframe id="previewFrame" src="<?php echo esc_url($previewUrl); ?>" class="w-full h-full border-0" style="min-height: 100%;"></iframe>
            </div>
        </div>
        
    </main>
    
</div>

<!-- Toast -->
<div id="toast" class="fixed bottom-6 left-1/2 -translate-x-1/2 px-6 py-4 glass rounded-2xl shadow-2xl transform translate-y-32 opacity-0 transition-all duration-500 flex items-center gap-4 z-50">
    <span id="toastIcon" class="w-10 h-10 rounded-xl flex items-center justify-center"></span>
    <div>
        <p id="toastTitle" class="text-sm font-semibold"></p>
        <p id="toastMessage" class="text-xs text-slate-400"></p>
    </div>
</div>

<script src="<?php echo esc_url(site_url('admin/js/media-picker.js')); ?>" onerror="console.warn('Media picker script could not be loaded');"></script>
<script>
// Global error handler - Chrome extension errors
window.addEventListener('error', function(e) {
    // Chrome extension connection errors - ignore
    if (e.message && e.message.includes('Could not establish connection')) {
        e.preventDefault();
        return false;
    }
}, true);

// Promise rejection handler
window.addEventListener('unhandledrejection', function(e) {
    // Chrome extension connection errors - ignore
    if (e.reason && e.reason.message && e.reason.message.includes('Could not establish connection')) {
        e.preventDefault();
        return false;
    }
});

const themeSlug = '<?php echo esc_js($themeSlug ?? 'codetic'); ?>';

// Ensure toggleSection is available (should be defined in head)
if (typeof window.toggleSection === 'undefined') {
    console.error('toggleSection function was not loaded from head!');
    // Fallback definition
    window.toggleSection = function(sectionId) {
        const panel = document.getElementById(sectionId + '-panel');
        const btn = document.querySelector(`[data-section="${sectionId}"]`);
        if (!panel || !btn) return;
        const isOpen = panel.classList.contains('open');
        document.querySelectorAll('.section-panel').forEach(p => p.classList.remove('open'));
        document.querySelectorAll('.section-btn').forEach(b => {
            b.classList.remove('active');
            const arrow = b.querySelector('.section-arrow');
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        });
        if (!isOpen) {
            panel.classList.add('open');
            btn.classList.add('active');
            const arrow = btn.querySelector('.section-arrow');
            if (arrow) arrow.style.transform = 'rotate(180deg)';
        }
    };
}

// Device Preview
function setDevice(device) {
    const wrapper = document.getElementById('previewWrapper');
    document.querySelectorAll('.device-btn').forEach(btn => {
        btn.classList.remove('active', 'bg-indigo-500/20', 'text-indigo-400');
        btn.classList.add('text-slate-400');
    });
    const activeBtn = document.querySelector(`[data-device="${device}"]`);
    activeBtn.classList.add('active', 'bg-indigo-500/20', 'text-indigo-400');
    activeBtn.classList.remove('text-slate-400');
    
    switch(device) {
        case 'mobile': wrapper.style.maxWidth = '390px'; break;
        case 'tablet': wrapper.style.maxWidth = '820px'; break;
        default: wrapper.style.maxWidth = '100%';
    }
}

// Refresh Preview
function refreshPreview() {
    const frame = document.getElementById('previewFrame');
    const status = document.getElementById('previewStatus');
    status.textContent = 'Yenileniyor...';
    frame.src = frame.src;
    frame.onload = () => { status.textContent = ''; };
}

// Media Functions
function selectLogo() {
    if (typeof openMediaPicker === 'function') {
        openMediaPicker({
            type: 'image',
            onSelect: (media) => {
                document.getElementById('siteLogo').value = media.file_url;
                document.getElementById('logoPreview').innerHTML = `<img src="${media.file_url}" class="w-full h-full object-contain">`;
            }
        });
    }
}

function removeLogo() {
    document.getElementById('siteLogo').value = '';
    document.getElementById('logoPreview').innerHTML = '<span class="material-symbols-outlined text-3xl text-slate-500">add_photo_alternate</span>';
}

function selectFavicon() {
    if (typeof openMediaPicker === 'function') {
        openMediaPicker({
            type: 'image',
            onSelect: (media) => {
                document.getElementById('siteFavicon').value = media.file_url;
                document.getElementById('faviconPreview').innerHTML = `<img src="${media.file_url}" class="w-full h-full object-contain">`;
            }
        });
    }
}

function removeFavicon() {
    document.getElementById('siteFavicon').value = '';
    document.getElementById('faviconPreview').innerHTML = '<span class="material-symbols-outlined text-2xl text-slate-500">add_photo_alternate</span>';
}

// Toast
function showToast(title, message, type = 'success') {
    const toast = document.getElementById('toast');
    const icon = document.getElementById('toastIcon');
    const titleEl = document.getElementById('toastTitle');
    const msgEl = document.getElementById('toastMessage');
    
    icon.className = 'w-10 h-10 rounded-xl flex items-center justify-center ' + (type === 'success' ? 'bg-emerald-500/20' : 'bg-red-500/20');
    icon.innerHTML = type === 'success' 
        ? '<span class="material-symbols-outlined text-emerald-400">check_circle</span>'
        : '<span class="material-symbols-outlined text-red-400">error</span>';
    titleEl.textContent = title;
    msgEl.textContent = message;
    
    toast.classList.remove('translate-y-32', 'opacity-0');
    setTimeout(() => toast.classList.add('translate-y-32', 'opacity-0'), 4000);
}

// Collect Settings
function collectSettings() {
    const settings = { colors: {}, fonts: {}, custom: {}, branding: {}, header: {}, footer: {}, sections: {}, contact_sections: {} };
    
    // Colors
    document.querySelectorAll('[name^="colors["]').forEach(input => {
        if (input.type === 'color') {
            const key = input.name.match(/\[([^\]]+)\]/)[1];
            settings.colors[key] = input.value;
        }
    });
    
    // Fonts
    document.querySelectorAll('[name^="fonts["]').forEach(input => {
        const key = input.name.match(/\[([^\]]+)\]/)[1];
        settings.fonts[key] = input.value;
    });
    
    // Header
    document.querySelectorAll('[name^="header["]').forEach(input => {
        const key = input.name.match(/\[([^\]]+)\]/)[1];
        settings.header[key] = input.type === 'checkbox' ? (input.checked ? '1' : '0') : input.value;
    });
    
    // Footer
    document.querySelectorAll('[name^="footer["]').forEach(input => {
        const key = input.name.match(/\[([^\]]+)\]/)[1];
        settings.footer[key] = input.type === 'checkbox' ? (input.checked ? '1' : '0') : input.value;
    });
    
    // Social - Kaldırıldı (Site ayarlarından yönetiliyor)
    
    // Sections
    document.querySelectorAll('[name^="sections["]').forEach(input => {
        // Features items için özel işleme
        const itemMatch = input.name.match(/sections\[([^\]]+)\]\[items\]\[(\d+)\]\[([^\]]+)\]/);
        if (itemMatch) {
            const [, sectionId, itemIndex, itemKey] = itemMatch;
            if (!settings.sections[sectionId]) settings.sections[sectionId] = {};
            if (!settings.sections[sectionId].items) settings.sections[sectionId].items = {};
            if (!settings.sections[sectionId].items[itemIndex]) settings.sections[sectionId].items[itemIndex] = {};
            settings.sections[sectionId].items[itemIndex][itemKey] = input.value;
            return;
        }
        
        // Settings altındaki alanlar için özel işleme (örn: sections[glowing-features][settings][badge])
        const settingsMatch = input.name.match(/sections\[([^\]]+)\]\[settings\]\[([^\]]+)\]/);
        if (settingsMatch) {
            const [, sectionId, settingKey] = settingsMatch;
            if (!settings.sections[sectionId]) settings.sections[sectionId] = {};
            if (!settings.sections[sectionId].settings) settings.sections[sectionId].settings = {};
            settings.sections[sectionId].settings[settingKey] = input.value;
            return;
        }
        
        // Normal section ayarları
        const m = input.name.match(/sections\[([^\]]+)\]\[([^\]]+)\]/);
        if (m) {
            if (!settings.sections[m[1]]) settings.sections[m[1]] = {};
            settings.sections[m[1]][m[2]] = input.type === 'checkbox' ? (input.checked ? '1' : '0') : input.value;
        }
    });
    
    // Items'ları array'e çevir ve boş olanları filtrele
    Object.keys(settings.sections).forEach(sectionId => {
        if (settings.sections[sectionId].items && typeof settings.sections[sectionId].items === 'object') {
            // Object'i array'e çevir
            const itemsArray = Object.keys(settings.sections[sectionId].items)
                .sort((a, b) => parseInt(a) - parseInt(b))
                .map(key => settings.sections[sectionId].items[key]);
            
            // Filtreleme - section tipine göre
            if (sectionId === 'testimonials') {
                // Testimonials için name veya content olanları al
                const filtered = itemsArray.filter(item => item && (item.name || item.content));
                settings.sections[sectionId].items = filtered;
            } else {
                // Diğerleri için title veya description olanları al
                const filtered = itemsArray.filter(item => item && (item.title || item.description));
                settings.sections[sectionId].items = filtered;
            }
        }
    });
    
    // Contact Sections
    document.querySelectorAll('[name^="contact_sections["]').forEach(input => {
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
        const m = input.name.match(/contact_sections\[([^\]]+)\]\[([^\]]+)\]/);
        if (m) {
            if (!settings.contact_sections[m[1]]) settings.contact_sections[m[1]] = {};
            settings.contact_sections[m[1]][m[2]] = input.type === 'checkbox' ? (input.checked ? '1' : '0') : input.value;
        }
    });
    
    // Contact sections items'ları array'e çevir ve filtrele
    Object.keys(settings.contact_sections).forEach(sectionId => {
        if (settings.contact_sections[sectionId].items && typeof settings.contact_sections[sectionId].items === 'object') {
            const itemsArray = Object.keys(settings.contact_sections[sectionId].items)
                .sort((a, b) => parseInt(a) - parseInt(b))
                .map(key => settings.contact_sections[sectionId].items[key])
                .filter(item => item && item.text);
            settings.contact_sections[sectionId].items = itemsArray;
        }
    });
    
    // Branding
    settings.branding.site_logo = document.getElementById('siteLogo')?.value || '';
    settings.branding.site_favicon = document.getElementById('siteFavicon')?.value || '';
    settings.branding.logo_width = document.getElementById('logoWidth')?.value || '';
    settings.branding.logo_height = document.getElementById('logoHeight')?.value || '';
    
    // Custom settings (footer, header, etc.)
    document.querySelectorAll('[name^="custom["]').forEach(input => {
        // Footer bottom links için özel işleme
        const footerLinkMatch = input.name.match(/custom\[footer_bottom_links\]\[(\d+)\]\[(\w+)\]/);
        if (footerLinkMatch) {
            const [, linkIndex, linkKey] = footerLinkMatch;
            if (!settings.custom.footer_bottom_links) settings.custom.footer_bottom_links = {};
            if (!settings.custom.footer_bottom_links[linkIndex]) settings.custom.footer_bottom_links[linkIndex] = {};
            settings.custom.footer_bottom_links[linkIndex][linkKey] = input.value;
            return;
        }
        
        // Diğer custom ayarlar
        const nameMatch = input.name.match(/custom\[([^\]]+)\]$/);
        if (!nameMatch) return;
        
        const key = nameMatch[1];
        settings.custom[key] = input.type === 'checkbox' ? (input.checked ? '1' : '0') : input.value;
    });
    
    // Footer bottom links'i array'e çevir ve filtrele
    if (settings.custom.footer_bottom_links && typeof settings.custom.footer_bottom_links === 'object') {
        const linksArray = Object.keys(settings.custom.footer_bottom_links)
            .sort((a, b) => parseInt(a) - parseInt(b))
            .map(key => settings.custom.footer_bottom_links[key])
            .filter(link => link && (link.text || link.url || link.agreement_id));
        // Boş array olsa bile kaydet (kullanıcı tüm linkleri silmiş olabilir)
        settings.custom.footer_bottom_links = linksArray;
    } else if (!settings.custom.footer_bottom_links) {
        // Hiç link yoksa boş array olarak kaydet
        settings.custom.footer_bottom_links = [];
    }
    
    // Custom CSS
    settings.custom_css = document.getElementById('customCss')?.value || '';
    
    return settings;
}

// Save
function saveSettings() {
    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined text-lg animate-spin">progress_activity</span> Kaydediliyor...';
    
    const settingsData = collectSettings();
    
    fetch('<?php echo esc_js(admin_url('themes/saveSettings')); ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ theme_slug: themeSlug, settings: settingsData })
    })
    .then(async r => {
        const contentType = r.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return r.json();
        } else {
            const text = await r.text();
            throw new Error(text || 'Geçersiz yanıt formatı');
        }
    })
    .then(data => {
        if (data.success) {
            showToast('Başarılı!', 'Tema ayarları kaydedildi', 'success');
            refreshPreview();
        } else {
            showToast('Hata!', data.message || 'Bir sorun oluştu', 'error');
        }
    })
    .catch(e => {
        console.error('Save settings error:', e);
        showToast('Hata!', e.message || 'Bağlantı sorunu', 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined text-lg">save</span> Kaydet';
    });
}

// Feature Item Management
let featureItemIndex = <?php echo isset($pageSections['features']['items']) && is_array($pageSections['features']['items']) ? (int)count($pageSections['features']['items']) : 3; ?>;

function addFeatureItem() {
    const container = document.getElementById('features-items');
    const itemHtml = `
        <div class="feature-item glass rounded-lg p-4 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-300">Öğe #${featureItemIndex + 1}</span>
                <button type="button" onclick="removeFeatureItem(this)" class="p-1.5 text-red-400 hover:bg-red-500/10 rounded transition-colors">
                    <span class="material-symbols-outlined text-sm">delete</span>
                </button>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">İkon (Material Symbols)</label>
                <div class="flex gap-2">
                    <input type="text" name="sections[features][items][${featureItemIndex}][icon]" value="star" placeholder="rocket_launch" class="flex-1 px-4 py-2 input-field rounded-lg text-sm icon-input" data-icon-index="${featureItemIndex}">
                    <button type="button" onclick="openIconPicker(${featureItemIndex})" class="px-4 py-2 bg-indigo-500/20 text-indigo-400 rounded-lg hover:bg-indigo-500/30 transition-colors flex items-center gap-2 text-sm">
                        <span class="material-symbols-outlined text-lg">palette</span>
                        Seç
                    </button>
                </div>
                <div class="mt-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-xl text-slate-400 icon-preview-${featureItemIndex}">star</span>
                    <span class="text-[10px] text-slate-500">Önizleme</span>
                </div>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Başlık</label>
                <input type="text" name="sections[features][items][${featureItemIndex}][title]" value="" class="w-full px-4 py-2 input-field rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Açıklama</label>
                <textarea name="sections[features][items][${featureItemIndex}][description]" rows="2" class="w-full px-4 py-2 input-field rounded-lg text-sm resize-none"></textarea>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Link (Opsiyonel)</label>
                <input type="text" name="sections[features][items][${featureItemIndex}][link]" value="" placeholder="/services" class="w-full px-4 py-2 input-field rounded-lg text-sm">
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', itemHtml);
    featureItemIndex++;
}

function removeFeatureItem(btn) {
    if (confirm('Bu öğeyi silmek istediğinize emin misiniz?')) {
        btn.closest('.feature-item').remove();
        // Index'leri yeniden numaralandır
        const items = document.querySelectorAll('#features-items .feature-item');
        items.forEach((item, index) => {
            const numberSpan = item.querySelector('.text-slate-300');
            if (numberSpan) numberSpan.textContent = `Öğe #${index + 1}`;
            
            // Input name'lerini güncelle
            item.querySelectorAll('input, textarea').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    const newName = name.replace(/\[items\]\[\d+\]/, `[items][${index}]`);
                    input.setAttribute('name', newName);
                }
                // Icon input için data-icon-index'i güncelle
                if (input.classList.contains('icon-input')) {
                    input.setAttribute('data-icon-index', index);
                }
            });
            
            // Icon preview class'ını güncelle
            const iconPreview = item.querySelector('[class*="icon-preview-"]');
            if (iconPreview) {
                const oldClass = Array.from(iconPreview.classList).find(c => c.startsWith('icon-preview-'));
                if (oldClass) {
                    iconPreview.classList.remove(oldClass);
                    iconPreview.classList.add(`icon-preview-${index}`);
                }
            }
            
            // Icon picker button onclick'ini güncelle
            const iconButton = item.querySelector('button[onclick*="openIconPicker"]');
            if (iconButton) {
                iconButton.setAttribute('onclick', `openIconPicker(${index})`);
            }
        });
    }
}

// Testimonial Item Management
let testimonialItemIndex = <?php echo isset($pageSections['testimonials']['items']) ? count($pageSections['testimonials']['items']) : 3; ?>;

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
                <textarea name="sections[testimonials][items][${testimonialItemIndex}][content]" rows="3" class="w-full px-4 py-2 input-field rounded-lg text-sm resize-none" placeholder="Müşteri yorumu..."></textarea>
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
                <input type="hidden" name="sections[testimonials][items][${testimonialItemIndex}][avatar]" value="" class="testimonial-avatar-input" data-index="${testimonialItemIndex}">
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
                    // Rating display'i güncelle
                    const ratingValue = input.value || 5;
                    const displaySpan = item.querySelector(`.rating-display-${index}`);
                    if (!displaySpan) {
                        // Eğer display yoksa, index'i bulalım
                        const ratingContainer = item.querySelector('.testimonial-rating-input').parentElement;
                        const existingDisplay = ratingContainer.querySelector('span[class*="rating-display-"]');
                        if (existingDisplay) {
                            existingDisplay.className = `text-xs text-slate-500 ml-2 rating-display-${index}`;
                            existingDisplay.textContent = `${ratingValue}/5`;
                        }
                    }
                }
            });
            
            // Avatar preview data-index'i güncelle
            const avatarPreview = item.querySelector('.testimonial-avatar-preview');
            if (avatarPreview) {
                avatarPreview.setAttribute('data-index', index);
                avatarPreview.setAttribute('onclick', `selectTestimonialAvatar(${index})`);
            }
            
            // Avatar butonlarını güncelle
            const avatarButtons = item.querySelectorAll('button[onclick*="selectTestimonialAvatar"], button[onclick*="removeTestimonialAvatar"]');
            avatarButtons.forEach(button => {
                const onclick = button.getAttribute('onclick');
                if (onclick) {
                    button.setAttribute('onclick', onclick.replace(/\d+/, index));
                }
            });
            
            // Rating butonlarını güncelle
            const ratingButtons = item.querySelectorAll('.testimonial-rating-btn');
            ratingButtons.forEach((btn, btnIdx) => {
                const star = btnIdx + 1;
                btn.setAttribute('onclick', `setTestimonialRating(this, ${index}, ${star})`);
            });
            
            // Rating display class'ını güncelle
            const ratingDisplays = item.querySelectorAll('[class*="rating-display-"]');
            ratingDisplays.forEach(display => {
                const oldClass = Array.from(display.classList).find(c => c.startsWith('rating-display-'));
                if (oldClass) {
                    display.classList.remove(oldClass);
                    display.classList.add(`rating-display-${index}`);
                }
            });
        });
    }
}

function setTestimonialRating(btn, itemIndex, rating) {
    const item = btn.closest('.testimonial-item');
    const ratingInput = item.querySelector('.testimonial-rating-input');
    const ratingButtons = item.querySelectorAll('.testimonial-rating-btn');
    const ratingDisplay = item.querySelector(`.rating-display-${itemIndex}`);
    
    // Rating değerini güncelle
    ratingInput.value = rating;
    if (ratingDisplay) {
        ratingDisplay.textContent = `${rating}/5`;
    }
    
    // Tüm yıldızları güncelle
    ratingButtons.forEach((starBtn, idx) => {
        const starNum = idx + 1;
        const icon = starBtn.querySelector('.material-symbols-outlined');
        if (starNum <= rating) {
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

let currentTestimonialAvatarIndex = null;

function selectTestimonialAvatar(index) {
    currentTestimonialAvatarIndex = index;
    if (typeof openMediaPicker === 'function') {
        openMediaPicker({
            type: 'image',
            onSelect: (media) => {
                const input = document.querySelector(`.testimonial-avatar-input[data-index="${index}"]`);
                const preview = document.querySelector(`.testimonial-avatar-preview[data-index="${index}"]`);
                if (input) {
                    input.value = media.file_url;
                }
                if (preview) {
                    preview.innerHTML = `<img src="${media.file_url}" class="w-full h-full object-cover">`;
                }
            }
        });
    }
}

function removeTestimonialAvatar(index) {
    const input = document.querySelector(`.testimonial-avatar-input[data-index="${index}"]`);
    const preview = document.querySelector(`.testimonial-avatar-preview[data-index="${index}"]`);
    const nameInput = document.querySelector(`input[name="sections[testimonials][items][${index}][name]"]`);
    
    if (input) {
        input.value = '';
    }
    if (preview) {
        const initial = nameInput ? (nameInput.value || 'A').charAt(0).toUpperCase() : 'A';
        preview.innerHTML = `<span class="text-white font-semibold text-xl">${initial}</span>`;
    }
}

// Glowing Features Item Management
let glowingFeatureItemIndex = <?php echo isset($pageSections['glowing-features']['items']) ? count($pageSections['glowing-features']['items']) : 5; ?>;

function addGlowingFeatureItem() {
    const container = document.getElementById('glowing-features-items');
    const itemHtml = `
        <div class="glowing-feature-item glass rounded-lg p-4 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-300">Öğe #${glowingFeatureItemIndex + 1}</span>
                <button type="button" onclick="removeGlowingFeatureItem(this)" class="p-1.5 text-red-400 hover:bg-red-500/10 rounded transition-colors">
                    <span class="material-symbols-outlined text-sm">delete</span>
                </button>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">İkon</label>
                <div class="flex gap-2">
                    <input type="text" name="sections[glowing-features][items][${glowingFeatureItemIndex}][icon]" value="rocket" placeholder="rocket" class="flex-1 px-4 py-2 input-field rounded-lg text-sm">
                </div>
                <div class="mt-2 text-xs text-slate-500">
                    Mevcut ikonlar: rocket, shield, code, zap, users, box, settings, lock, sparkles, search, chart, globe
                </div>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Başlık</label>
                <input type="text" name="sections[glowing-features][items][${glowingFeatureItemIndex}][title]" value="" class="w-full px-4 py-2 input-field rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Açıklama</label>
                <textarea name="sections[glowing-features][items][${glowingFeatureItemIndex}][description]" rows="2" class="w-full px-4 py-2 input-field rounded-lg text-sm resize-none"></textarea>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Gradient</label>
                <select name="sections[glowing-features][items][${glowingFeatureItemIndex}][gradient]" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                    <option value="from-violet-500 to-purple-600" selected>Violet-Purple</option>
                    <option value="from-emerald-500 to-teal-600">Emerald-Teal</option>
                    <option value="from-blue-500 to-cyan-600">Blue-Cyan</option>
                    <option value="from-amber-500 to-orange-600">Amber-Orange</option>
                    <option value="from-pink-500 to-rose-600">Pink-Rose</option>
                    <option value="from-red-500 to-pink-600">Red-Pink</option>
                    <option value="from-green-500 to-emerald-600">Green-Emerald</option>
                    <option value="from-indigo-500 to-blue-600">Indigo-Blue</option>
                </select>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', itemHtml);
    glowingFeatureItemIndex++;
}

function removeGlowingFeatureItem(btn) {
    if (confirm('Bu öğeyi silmek istediğinize emin misiniz?')) {
        btn.closest('.glowing-feature-item').remove();
        // Index'leri yeniden numaralandır
        const items = document.querySelectorAll('#glowing-features-items .glowing-feature-item');
        items.forEach((item, index) => {
            const numberSpan = item.querySelector('.text-slate-300');
            if (numberSpan) numberSpan.textContent = `Öğe #${index + 1}`;
            
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

// Pricing Package Management
let pricingPackageIndex = <?php echo isset($pageSections['pricing']['packages']) ? count($pageSections['pricing']['packages']) : 4; ?>;

function addPricingPackage() {
    const container = document.getElementById('pricing-packages');
    const itemHtml = `
        <div class="pricing-package-item glass rounded-lg p-4 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-300">Paket #${pricingPackageIndex + 1}</span>
                <button type="button" onclick="removePricingPackage(this)" class="p-1.5 text-red-400 hover:bg-red-500/10 rounded transition-colors">
                    <span class="material-symbols-outlined text-sm">delete</span>
                </button>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Paket Adı</label>
                    <input type="text" name="sections[pricing][packages][${pricingPackageIndex}][name]" value="" placeholder="Başlangıç" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Fiyat</label>
                    <input type="text" name="sections[pricing][packages][${pricingPackageIndex}][price]" value="" placeholder="₺2.500" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Periyot</label>
                    <input type="text" name="sections[pricing][packages][${pricingPackageIndex}][period]" value="" placeholder="/ay, /yıl" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Gradient</label>
                    <select name="sections[pricing][packages][${pricingPackageIndex}][gradient]" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                        <option value="from-slate-500 to-slate-600" selected>Slate</option>
                        <option value="from-blue-500 to-purple-600">Blue-Purple</option>
                        <option value="from-violet-500 to-purple-600">Violet-Purple</option>
                        <option value="from-emerald-500 to-teal-600">Emerald-Teal</option>
                        <option value="from-amber-500 to-orange-600">Amber-Orange</option>
                        <option value="from-pink-500 to-rose-600">Pink-Rose</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Açıklama</label>
                <textarea name="sections[pricing][packages][${pricingPackageIndex}][description]" rows="2" class="w-full px-4 py-2 input-field rounded-lg text-sm resize-none" placeholder="Paket açıklaması..."></textarea>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1.5">Özellikler</label>
                <div id="pricing-features-${pricingPackageIndex}" class="space-y-2 mb-2">
                </div>
                <button type="button" onclick="addPricingFeature(${pricingPackageIndex})" class="w-full px-4 py-2 text-xs font-medium bg-slate-700/50 text-slate-300 rounded-lg hover:bg-slate-700 transition-colors flex items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-sm">add</span>
                    Özellik Ekle
                </button>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Buton Metni</label>
                    <input type="text" name="sections[pricing][packages][${pricingPackageIndex}][button_text]" value="Başla" placeholder="Başla" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Buton Linki</label>
                    <input type="text" name="sections[pricing][packages][${pricingPackageIndex}][button_link]" value="/contact" placeholder="/contact" class="w-full px-4 py-2 input-field rounded-lg text-sm">
                </div>
            </div>
            <label class="flex items-center gap-3 cursor-pointer p-2 rounded-lg hover:bg-white/5 transition-colors">
                <input type="checkbox" name="sections[pricing][packages][${pricingPackageIndex}][popular]" value="1" class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500">
                <span class="text-xs text-slate-400">Popüler Paket</span>
            </label>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', itemHtml);
    pricingPackageIndex++;
}

function removePricingPackage(btn) {
    if (confirm('Bu paketi silmek istediğinize emin misiniz?')) {
        btn.closest('.pricing-package-item').remove();
        // Index'leri yeniden numaralandır
        const items = document.querySelectorAll('#pricing-packages .pricing-package-item');
        items.forEach((item, index) => {
            const numberSpan = item.querySelector('.text-slate-300');
            if (numberSpan) numberSpan.textContent = `Paket #${index + 1}`;
            
            // Package index'ini bul
            const packageIndex = index;
            
            // Input name'lerini güncelle
            item.querySelectorAll('input, textarea, select').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    const newName = name.replace(/\[packages\]\[\d+\]/, `[packages][${packageIndex}]`);
                    input.setAttribute('name', newName);
                }
            });
            
            // Features container ID'sini güncelle
            const featuresContainer = item.querySelector('[id^="pricing-features-"]');
            if (featuresContainer) {
                featuresContainer.id = `pricing-features-${packageIndex}`;
                // Features container içindeki buton onclick'ini güncelle
                const addFeatureBtn = featuresContainer.nextElementSibling;
                if (addFeatureBtn && addFeatureBtn.onclick) {
                    addFeatureBtn.setAttribute('onclick', `addPricingFeature(${packageIndex})`);
                }
            }
            
            // Feature input'larını güncelle
            item.querySelectorAll('.pricing-feature-item input').forEach((featureInput, featureIndex) => {
                const name = featureInput.getAttribute('name');
                if (name) {
                    const newName = name.replace(/\[packages\]\[\d+\]\[features\]\[\d+\]/, `[packages][${packageIndex}][features][${featureIndex}]`);
                    featureInput.setAttribute('name', newName);
                }
            });
        });
    }
}

function addPricingFeature(packageIndex) {
    const container = document.getElementById(`pricing-features-${packageIndex}`);
    if (!container) return;
    
    const featureIndex = container.querySelectorAll('.pricing-feature-item').length;
    const itemHtml = `
        <div class="flex items-center gap-2 pricing-feature-item">
            <input type="text" name="sections[pricing][packages][${packageIndex}][features][${featureIndex}]" value="" placeholder="Özellik adı" class="flex-1 px-4 py-2 input-field rounded-lg text-sm">
            <button type="button" onclick="removePricingFeature(this)" class="p-2 text-red-400 hover:bg-red-500/10 rounded transition-colors">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', itemHtml);
}

function removePricingFeature(btn) {
    btn.closest('.pricing-feature-item').remove();
    // Feature index'lerini yeniden numaralandır
    const packageItem = btn.closest('.pricing-package-item');
    const featuresContainer = packageItem.querySelector('[id^="pricing-features-"]');
    if (featuresContainer) {
        const packageIndexMatch = featuresContainer.id.match(/pricing-features-(\d+)/);
        if (packageIndexMatch) {
            const packageIndex = packageIndexMatch[1];
            const featureItems = featuresContainer.querySelectorAll('.pricing-feature-item');
            featureItems.forEach((item, index) => {
                const input = item.querySelector('input');
                if (input) {
                    const name = input.getAttribute('name');
                    if (name) {
                        const newName = name.replace(/\[features\]\[\d+\]/, `[features][${index}]`);
                        input.setAttribute('name', newName);
                    }
                }
            });
        }
    }
}

// Name input değiştiğinde avatar initial'ini güncelle
document.addEventListener('DOMContentLoaded', () => {
    // Testimonial name input değişikliklerini dinle
    document.addEventListener('input', (e) => {
        if (e.target.name && e.target.name.includes('sections[testimonials][items]') && e.target.name.includes('[name]')) {
            const match = e.target.name.match(/\[items\]\[(\d+)\]/);
            if (match) {
                const index = match[1];
                const preview = document.querySelector(`.testimonial-avatar-preview[data-index="${index}"]`);
                const avatarInput = document.querySelector(`.testimonial-avatar-input[data-index="${index}"]`);
                // Eğer avatar yoksa, initial göster
                if (preview && avatarInput && !avatarInput.value) {
                    const initial = (e.target.value || 'A').charAt(0).toUpperCase();
                    preview.innerHTML = `<span class="text-white font-semibold text-xl">${initial}</span>`;
                }
            }
        }
    });
});

// Icon Picker
let currentIconIndex = null;
const popularIcons = [
    'rocket_launch', 'palette', 'devices', 'security', 'support_agent', 'settings',
    'star', 'favorite', 'home', 'menu', 'search', 'close', 'add', 'delete', 'edit',
    'check_circle', 'error', 'warning', 'info', 'arrow_forward', 'arrow_back',
    'expand_more', 'expand_less', 'keyboard_arrow_up', 'keyboard_arrow_down',
    'email', 'phone', 'location_on', 'person', 'group', 'business', 'work', 'school',
    'shopping_cart', 'payment', 'credit_card', 'lock', 'visibility', 'visibility_off',
    'cloud_upload', 'download', 'share', 'link', 'code', 'dashboard', 'analytics',
    'notifications', 'message', 'forum', 'chat', 'video_call', 'call',
    'camera', 'image', 'photo', 'folder', 'file', 'description', 'article', 'note',
    'calendar_today', 'schedule', 'event', 'alarm', 'timer', 'access_time',
    'refresh', 'sync', 'autorenew', 'build', 'construction', 'handyman',
    'lightbulb', 'idea', 'psychology', 'science', 'biotech', 'medical_services',
    'fitness_center', 'sports', 'music_note', 'movie', 'book', 'library_books',
    'school', 'local_library', 'translate', 'language', 'public', 'globe'
];

function openIconPicker(index) {
    currentIconIndex = index;
    const modal = document.getElementById('icon-picker-modal');
    if (!modal) {
        createIconPickerModal();
    }
    document.getElementById('icon-picker-modal').classList.remove('hidden');
    document.getElementById('icon-picker-search').value = '';
    filterIcons('');
    
    // Mevcut ikonu işaretle
    const currentIcon = document.querySelector(`input[data-icon-index="${index}"]`)?.value || '';
    setTimeout(() => {
        const iconButtons = document.querySelectorAll('#icon-picker-grid button');
        iconButtons.forEach(btn => {
            if (btn.dataset.icon === currentIcon) {
                btn.classList.add('ring-2', 'ring-indigo-500', 'bg-indigo-500/20');
            }
        });
    }, 100);
}

// Services Item Management
let serviceItemIndex = <?php echo isset($contactPageSections['services']['items']) && is_array($contactPageSections['services']['items']) ? (int)count($contactPageSections['services']['items']) : 4; ?>;

function addServiceItem() {
    const container = document.getElementById('services-items');
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

// Why Choose Us Item Management
let whyChooseItemIndex = <?php echo isset($contactPageSections['why-choose-us']['items']) && is_array($contactPageSections['why-choose-us']['items']) ? (int)count($contactPageSections['why-choose-us']['items']) : 4; ?>;

function addWhyChooseItem() {
    const container = document.getElementById('why-choose-items');
    const itemHtml = `
        <div class="flex items-start gap-2 why-choose-item">
            <input type="text" name="contact_sections[why-choose-us][items][${whyChooseItemIndex}][text]" 
                   value="" 
                   placeholder="Avantaj metni" 
                   class="flex-1 px-4 py-2 input-field rounded-lg text-sm">
            <button type="button" onclick="removeWhyChooseItem(this)" class="p-2 text-red-400 hover:bg-red-500/10 rounded-lg transition-colors">
                <span class="material-symbols-outlined text-sm">delete</span>
            </button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', itemHtml);
    whyChooseItemIndex++;
}

function removeWhyChooseItem(btn) {
    if (confirm('Bu öğeyi silmek istediğinize emin misiniz?')) {
        btn.closest('.why-choose-item').remove();
        // Index'leri yeniden numaralandır
        const items = document.querySelectorAll('#why-choose-items .why-choose-item');
        items.forEach((item, index) => {
            const input = item.querySelector('input');
            if (input) {
                const name = input.getAttribute('name');
                if (name) {
                    const newName = name.replace(/\[items\]\[\d+\]/, `[items][${index}]`);
                    input.setAttribute('name', newName);
                }
            }
        });
    }
}

function createIconPickerModal() {
    const modalHTML = `
        <div id="icon-picker-modal" class="fixed inset-0 z-[999999] hidden">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeIconPicker()"></div>
            <div class="absolute inset-4 md:inset-8 lg:inset-12 bg-slate-800 rounded-2xl shadow-2xl overflow-hidden flex flex-col" style="max-height: calc(100vh - 2rem);">
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-white/10 bg-slate-700/50">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-indigo-400 text-2xl">palette</span>
                        <h3 class="text-lg font-semibold text-white">İkon Seç</h3>
                    </div>
                    <button onclick="closeIconPicker()" class="p-2 hover:bg-white/10 rounded-lg transition-colors">
                        <span class="material-symbols-outlined text-slate-400">close</span>
                    </button>
                </div>
                
                <!-- Search -->
                <div class="px-6 py-4 border-b border-white/10">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                        <input 
                            type="text" 
                            id="icon-picker-search"
                            placeholder="İkon ara..." 
                            class="w-full pl-10 pr-4 py-2.5 input-field rounded-lg text-sm"
                            oninput="filterIcons(this.value)"
                        >
                    </div>
                </div>
                
                <!-- Icons Grid -->
                <div class="flex-1 overflow-y-auto p-6 scrollbar-thin">
                    <div id="icon-picker-grid" class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-3">
                        ${popularIcons.map(icon => `
                            <button 
                                type="button"
                                onclick="selectIcon('${icon}')"
                                data-icon="${icon}"
                                class="flex flex-col items-center justify-center p-3 rounded-lg bg-slate-700/50 hover:bg-indigo-500/20 border border-slate-600 hover:border-indigo-500/50 transition-all group"
                                title="${icon}"
                            >
                                <span class="material-symbols-outlined text-2xl text-slate-300 group-hover:text-indigo-400">${icon}</span>
                                <span class="text-[10px] text-slate-400 mt-1 truncate w-full text-center">${icon.split('_')[0]}</span>
                            </button>
                        `).join('')}
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="flex items-center justify-between px-6 py-4 border-t border-white/10 bg-slate-700/50">
                    <span class="text-xs text-slate-400">Material Symbols ikonları</span>
                    <button type="button" onclick="closeIconPicker()" class="px-4 py-2 border border-white/10 text-slate-300 rounded-lg hover:bg-white/5 transition-colors text-sm">
                        İptal
                    </button>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

function filterIcons(searchTerm) {
    const grid = document.getElementById('icon-picker-grid');
    const buttons = grid.querySelectorAll('button');
    const term = searchTerm.toLowerCase();
    
    buttons.forEach(btn => {
        const iconName = btn.dataset.icon.toLowerCase();
        if (iconName.includes(term)) {
            btn.style.display = 'flex';
        } else {
            btn.style.display = 'none';
        }
    });
}

function selectIcon(iconName) {
    if (currentIconIndex === null) return;
    
    const input = document.querySelector(`input[data-icon-index="${currentIconIndex}"]`);
    const preview = document.querySelector(`.icon-preview-${currentIconIndex}`);
    
    if (input) {
        input.value = iconName;
    }
    if (preview) {
        preview.textContent = iconName;
    }
    
    closeIconPicker();
}

function closeIconPicker() {
    document.getElementById('icon-picker-modal')?.classList.add('hidden');
    currentIconIndex = null;
}

// Footer Bottom Links Management
let footerLinkIndex = <?php echo $linkIndex; ?>;

function addFooterLink() {
    const container = document.getElementById('footer-bottom-links-container');
    const agreements = <?php echo json_encode($agreements); ?>;
    
    let agreementOptions = '<option value="">Özel URL kullan</option>';
    agreements.forEach(agreement => {
        agreementOptions += `<option value="${agreement.id}" data-slug="${agreement.slug}">${agreement.title} (${agreement.type})</option>`;
    });
    
    const itemHtml = `
        <div class="footer-bottom-link-item flex items-center gap-3 p-3 rounded-lg bg-slate-800/50">
            <div class="flex-1 grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Link Metni</label>
                    <input type="text" name="custom[footer_bottom_links][${footerLinkIndex}][text]" 
                           value="" 
                           placeholder="Gizlilik Politikası" 
                           class="w-full px-3 py-2 input-field rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Sözleşme Seç</label>
                    <select name="custom[footer_bottom_links][${footerLinkIndex}][agreement_id]" 
                            class="w-full px-3 py-2 input-field rounded-lg text-sm footer-agreement-select">
                        ${agreementOptions}
                    </select>
                </div>
            </div>
            <div class="flex-1">
                <label class="block text-xs text-slate-400 mb-1.5">Özel URL (Sözleşme seçilmediyse)</label>
                <input type="text" name="custom[footer_bottom_links][${footerLinkIndex}][url]" 
                       value="" 
                       placeholder="/gizlilik-politikasi" 
                       class="w-full px-3 py-2 input-field rounded-lg text-sm footer-link-url">
            </div>
            <button type="button" onclick="removeFooterLink(this)" class="p-2 text-red-400 hover:bg-red-500/10 rounded-lg transition-colors">
                <span class="material-symbols-outlined text-lg">delete</span>
            </button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', itemHtml);
    
    // Yeni eklenen select'e event listener ekle
    const newSelect = container.lastElementChild.querySelector('.footer-agreement-select');
    if (newSelect) {
        newSelect.addEventListener('change', function() {
            updateFooterLinkUrl(this);
        });
    }
    
    footerLinkIndex++;
}

function removeFooterLink(btn) {
    if (confirm('Bu linki silmek istediğinize emin misiniz?')) {
        btn.closest('.footer-bottom-link-item').remove();
    }
}

function updateFooterLinkUrl(select) {
    const selectedOption = select.options[select.selectedIndex];
    const slug = selectedOption.getAttribute('data-slug');
    const urlInput = select.closest('.footer-bottom-link-item').querySelector('.footer-link-url');
    const textInput = select.closest('.footer-bottom-link-item').querySelector('input[name*="[text]"]');
    
    if (slug && urlInput) {
        urlInput.value = '/' + slug;
    }
    
    // Eğer text boşsa, sözleşme başlığını otomatik doldur
    if (selectedOption.value && textInput && !textInput.value) {
        const optionText = selectedOption.textContent.trim();
        // Parantez içindeki tip bilgisini kaldır
        const title = optionText.replace(/\s*\([^)]*\)$/, '');
        textInput.value = title;
    }
}

// Mevcut select'ler için change event listener ekle
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.footer-agreement-select').forEach(select => {
        if (!select.hasAttribute('data-listener-added')) {
            select.addEventListener('change', function() {
                updateFooterLinkUrl(this);
            });
            select.setAttribute('data-listener-added', 'true');
        }
    });
});

// Icon input değiştiğinde preview'ı güncelle
document.addEventListener('DOMContentLoaded', () => {
    setDevice('desktop');
    
    // Section toggle butonlarına event listener ekle
    document.querySelectorAll('.section-btn[data-section]').forEach(btn => {
        const sectionId = btn.getAttribute('data-section');
        if (sectionId) {
            // Remove existing onclick
            btn.removeAttribute('onclick');
            // Add event listener
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (window.toggleSection) {
                    window.toggleSection(sectionId);
                } else {
                    console.error('toggleSection function not found!');
                }
            });
        }
    });
    
    // İlk açılışta aktif paneli kontrol et
    const activePanel = document.querySelector('.section-panel.open');
    if (!activePanel) {
        // Eğer hiç açık panel yoksa, branding panelini aç
        const brandingPanel = document.getElementById('branding-panel');
        const brandingBtn = document.querySelector('[data-section="branding"]');
        if (brandingPanel && brandingBtn) {
            brandingPanel.classList.add('open');
            brandingBtn.classList.add('active');
            const arrow = brandingBtn.querySelector('.section-arrow');
            if (arrow) {
                arrow.style.transform = 'rotate(180deg)';
            }
        }
    }
    
    // Icon input değişikliklerini dinle
    document.addEventListener('input', (e) => {
        if (e.target.classList.contains('icon-input')) {
            const index = e.target.dataset.iconIndex;
            const preview = document.querySelector(`.icon-preview-${index}`);
            if (preview) {
                preview.textContent = e.target.value || 'star';
            }
        }
        if (e.target.classList.contains('core-value-icon-input')) {
            const index = e.target.dataset.iconIndex;
            const preview = document.querySelector(`.core-value-icon-preview-${index}`);
            if (preview) {
                preview.textContent = e.target.value || 'star';
            }
        }
    });
});

// Icon picker için core-value desteği
let currentIconType = 'feature';

// openIconPicker fonksiyonunu override et
const originalOpenIconPicker = window.openIconPicker;
window.openIconPicker = function(index, type = 'feature') {
    currentIconIndex = index;
    currentIconType = type || 'feature';
    if (typeof originalOpenIconPicker === 'function') {
        originalOpenIconPicker(index);
    } else {
        // Fallback
        const modal = document.getElementById('icon-picker-modal');
        if (!modal) {
            createIconPickerModal();
        }
        document.getElementById('icon-picker-modal').classList.remove('hidden');
        document.getElementById('icon-picker-search').value = '';
        filterIcons('');
        
        const currentIcon = type === 'core-value' 
            ? document.querySelector(`input.core-value-icon-input[data-icon-index="${index}"]`)?.value || ''
            : document.querySelector(`input[data-icon-index="${index}"]`)?.value || '';
        setTimeout(() => {
            const iconButtons = document.querySelectorAll('#icon-picker-grid button');
            iconButtons.forEach(btn => {
                if (btn.dataset.icon === currentIcon) {
                    btn.classList.add('ring-2', 'ring-indigo-500', 'bg-indigo-500/20');
                }
            });
        }, 100);
    }
};

// selectIcon fonksiyonunu override et
const originalSelectIcon = window.selectIcon;
window.selectIcon = function(iconName) {
    if (currentIconType === 'core-value' && currentIconIndex !== null) {
        const input = document.querySelector(`input.core-value-icon-input[data-icon-index="${currentIconIndex}"]`);
        const preview = document.querySelector(`.core-value-icon-preview-${currentIconIndex}`);
        if (input) input.value = iconName;
        if (preview) preview.textContent = iconName;
        closeIconPicker();
        return;
    }
    if (typeof originalSelectIcon === 'function') {
        originalSelectIcon(iconName);
    } else {
        // Fallback
        if (currentIconIndex === null) return;
        const input = document.querySelector(`input[data-icon-index="${currentIconIndex}"]`);
        const preview = document.querySelector(`.icon-preview-${currentIconIndex}`);
        if (input) input.value = iconName;
        if (preview) preview.textContent = iconName;
        closeIconPicker();
    }
};
</script>

</body>
</html>
