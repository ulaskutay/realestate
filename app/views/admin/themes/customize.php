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
if (isset($themeManager)) {
    try {
        $sections = $themeManager->getPageSections('home') ?? [];
        foreach ($sections as $section) {
            $sectionId = $section['section_id'] ?? '';
            if ($sectionId) {
                // settings zaten array ise kullan, değilse JSON decode et
                $settings = [];
                if (isset($section['settings'])) {
                    if (is_array($section['settings'])) {
                        $settings = $section['settings'];
                    } else {
                        $decoded = json_decode($section['settings'], true);
                        $settings = is_array($decoded) ? $decoded : [];
                    }
                }
                
                $pageSections[$sectionId] = array_merge(
                    $settings,
                    ['enabled' => ($section['is_active'] ?? 1) == 1]
                );
                $pageSections[$sectionId]['title'] = $section['title'] ?? '';
                $pageSections[$sectionId]['subtitle'] = $section['subtitle'] ?? '';
                $pageSections[$sectionId]['content'] = $section['content'] ?? '';
            }
        }
    } catch (Exception $e) {
        error_log("Customize page sections error: " . $e->getMessage());
    }
}

// Mevcut ayarları al
$currentLogo = $settings['branding']['site_logo']['value'] ?? '';
$currentFavicon = $settings['branding']['site_favicon']['value'] ?? '';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tema Özelleştirici - <?php echo esc_html($theme['name'] ?? 'Starter'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
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
        .section-panel { max-height: 0; overflow: hidden; transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .section-panel.open { max-height: 2000px; }
        .section-btn.active { background: linear-gradient(135deg, rgba(99,102,241,0.15) 0%, rgba(139,92,246,0.1) 100%); border-color: rgba(99,102,241,0.3); }
        .section-btn.active .section-icon { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; }
        input[type="color"] { -webkit-appearance: none; border: none; cursor: pointer; }
        input[type="color"]::-webkit-color-swatch-wrapper { padding: 0; }
        input[type="color"]::-webkit-color-swatch { border: none; border-radius: 8px; }
        .scrollbar-thin::-webkit-scrollbar { width: 5px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
        .input-field { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); transition: all 0.2s; }
        .input-field:focus { background: rgba(255,255,255,0.08); border-color: rgba(99,102,241,0.5); outline: none; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
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
                <button onclick="toggleSection('branding')" class="section-btn active w-full p-4 flex items-center gap-4 hover:bg-white/5 transition-all" data-section="branding">
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
                <button onclick="toggleSection('header')" class="section-btn w-full p-4 flex items-center gap-4 hover:bg-white/5 transition-all" data-section="header">
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
                                    <input type="color" name="header[bg_color]" value="<?php echo esc_attr($settings['header']['bg_color']['value'] ?? '#ffffff'); ?>" class="w-10 h-10 rounded-lg">
                                    <input type="text" value="<?php echo esc_attr($settings['header']['bg_color']['value'] ?? '#ffffff'); ?>" class="flex-1 px-4 py-2.5 input-field rounded-lg text-sm font-mono" oninput="this.previousElementSibling.value = this.value">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-2">Metin Rengi</label>
                                <div class="flex items-center gap-3">
                                    <input type="color" name="header[text_color]" value="<?php echo esc_attr($settings['header']['text_color']['value'] ?? '#1f2937'); ?>" class="w-10 h-10 rounded-lg">
                                    <input type="text" value="<?php echo esc_attr($settings['header']['text_color']['value'] ?? '#1f2937'); ?>" class="flex-1 px-4 py-2.5 input-field rounded-lg text-sm font-mono" oninput="this.previousElementSibling.value = this.value">
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
                <button onclick="toggleSection('colors')" class="section-btn w-full p-4 flex items-center gap-4 hover:bg-white/5 transition-all" data-section="colors">
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
                <button onclick="toggleSection('fonts')" class="section-btn w-full p-4 flex items-center gap-4 hover:bg-white/5 transition-all" data-section="fonts">
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
                            $fonts = ['Inter', 'Plus Jakarta Sans', 'Poppins', 'Roboto', 'Open Sans', 'Lato', 'Montserrat', 'Raleway', 'Nunito', 'DM Sans'];
                            foreach ($settings['fonts'] as $key => $config): 
                            ?>
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-2"><?php echo esc_html($config['label']); ?></label>
                                <select name="fonts[<?php echo $key; ?>]" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                    <?php foreach ($fonts as $font): ?>
                                    <option value="<?php echo $font; ?>" <?php echo ($config['value'] ?? $config['default']) === $font ? 'selected' : ''; ?> style="font-family: <?php echo $font; ?>"><?php echo $font; ?></option>
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
                <button onclick="toggleSection('footer')" class="section-btn w-full p-4 flex items-center gap-4 hover:bg-white/5 transition-all" data-section="footer">
                    <div class="section-icon w-10 h-10 rounded-xl bg-slate-700/50 flex items-center justify-center">
                        <span class="material-symbols-outlined">call_to_action</span>
                    </div>
                    <div class="flex-1 text-left">
                        <span class="text-sm font-semibold block">Footer</span>
                        <span class="text-xs text-slate-400">Alt bilgi ve sosyal medya</span>
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
                        </div>
                        
                        <!-- Sosyal Medya -->
                        <div class="glass rounded-xl p-4 space-y-3">
                            <label class="block text-xs font-medium text-slate-300 mb-1">Sosyal Medya Linkleri</label>
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-blue-400" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    </div>
                                    <input type="text" name="social[facebook]" value="<?php echo esc_attr($settings['social']['facebook']['value'] ?? ''); ?>" placeholder="Facebook URL" class="flex-1 px-4 py-2 input-field rounded-lg text-sm">
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-sky-500/20 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-sky-400" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                    </div>
                                    <input type="text" name="social[twitter]" value="<?php echo esc_attr($settings['social']['twitter']['value'] ?? ''); ?>" placeholder="X (Twitter) URL" class="flex-1 px-4 py-2 input-field rounded-lg text-sm">
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-pink-500/20 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-pink-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                    </div>
                                    <input type="text" name="social[instagram]" value="<?php echo esc_attr($settings['social']['instagram']['value'] ?? ''); ?>" placeholder="Instagram URL" class="flex-1 px-4 py-2 input-field rounded-lg text-sm">
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-blue-600/20 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                    </div>
                                    <input type="text" name="social[linkedin]" value="<?php echo esc_attr($settings['social']['linkedin']['value'] ?? ''); ?>" placeholder="LinkedIn URL" class="flex-1 px-4 py-2 input-field rounded-lg text-sm">
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-red-500/20 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                    </div>
                                    <input type="text" name="social[youtube]" value="<?php echo esc_attr($settings['social']['youtube']['value'] ?? ''); ?>" placeholder="YouTube URL" class="flex-1 px-4 py-2 input-field rounded-lg text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Ana Sayfa Bölümleri -->
            <div class="border-b border-white/5">
                <button onclick="toggleSection('homepage')" class="section-btn w-full p-4 flex items-center gap-4 hover:bg-white/5 transition-all" data-section="homepage">
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
                            <div class="p-4 pt-0 space-y-3 border-t border-white/5">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Başlık</label>
                                    <input type="text" name="sections[features][title]" value="<?php echo esc_attr($pageSections['features']['title'] ?? 'Özelliklerimiz'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="sections[features][enabled]" value="1" <?php echo ($pageSections['features']['enabled'] ?? true) ? 'checked' : ''; ?> class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500">
                                    <span class="text-xs text-slate-400">Bu bölümü göster</span>
                                </label>
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
                        
                        <!-- Testimonials -->
                        <details class="glass rounded-xl overflow-hidden group">
                            <summary class="flex items-center justify-between p-4 cursor-pointer hover:bg-white/5 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-sm">💬</span>
                                    <span class="text-sm font-medium">Müşteri Yorumları</span>
                                </div>
                                <span class="material-symbols-outlined text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
                            </summary>
                            <div class="p-4 pt-0 space-y-3 border-t border-white/5">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Başlık</label>
                                    <input type="text" name="sections[testimonials][title]" value="<?php echo esc_attr($pageSections['testimonials']['title'] ?? 'Müşteri Yorumları'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="sections[testimonials][enabled]" value="1" <?php echo ($pageSections['testimonials']['enabled'] ?? true) ? 'checked' : ''; ?> class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500">
                                    <span class="text-xs text-slate-400">Bu bölümü göster</span>
                                </label>
                            </div>
                        </details>
                        
                        <!-- CTA -->
                        <details class="glass rounded-xl overflow-hidden group">
                            <summary class="flex items-center justify-between p-4 cursor-pointer hover:bg-white/5 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center text-sm">📢</span>
                                    <span class="text-sm font-medium">CTA</span>
                                </div>
                                <span class="material-symbols-outlined text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
                            </summary>
                            <div class="p-4 pt-0 space-y-3 border-t border-white/5">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1.5">Başlık</label>
                                    <input type="text" name="sections[cta][title]" value="<?php echo esc_attr($pageSections['cta']['title'] ?? 'Hemen Başlayın'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1.5">Buton</label>
                                        <input type="text" name="sections[cta][button_text]" value="<?php echo esc_attr($pageSections['cta']['button_text'] ?? 'İletişim'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1.5">Link</label>
                                        <input type="text" name="sections[cta][button_link]" value="<?php echo esc_attr($pageSections['cta']['button_link'] ?? '/contact'); ?>" class="w-full px-4 py-2.5 input-field rounded-lg text-sm">
                                    </div>
                                </div>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="sections[cta][enabled]" value="1" <?php echo ($pageSections['cta']['enabled'] ?? true) ? 'checked' : ''; ?> class="w-4 h-4 rounded bg-slate-700 border-slate-600 text-indigo-500">
                                    <span class="text-xs text-slate-400">Bu bölümü göster</span>
                                </label>
                            </div>
                        </details>
                        
                    </div>
                </div>
            </div>
            
            <!-- Özel CSS -->
            <div class="border-b border-white/5">
                <button onclick="toggleSection('css')" class="section-btn w-full p-4 flex items-center gap-4 hover:bg-white/5 transition-all" data-section="css">
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

<script src="<?php echo site_url('admin/js/media-picker.js'); ?>"></script>
<script>
const themeSlug = '<?php echo esc_js($themeSlug); ?>';

// Section Toggle
function toggleSection(sectionId) {
    const panel = document.getElementById(sectionId + '-panel');
    const btn = document.querySelector(`[data-section="${sectionId}"]`);
    const isOpen = panel.classList.contains('open');
    
    // Close all
    document.querySelectorAll('.section-panel').forEach(p => p.classList.remove('open'));
    document.querySelectorAll('.section-btn').forEach(b => {
        b.classList.remove('active');
        b.querySelector('.section-arrow').style.transform = 'rotate(0deg)';
    });
    
    // Open clicked
    if (!isOpen) {
        panel.classList.add('open');
        btn.classList.add('active');
        btn.querySelector('.section-arrow').style.transform = 'rotate(180deg)';
    }
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
    const settings = { colors: {}, fonts: {}, custom: {}, branding: {}, header: {}, footer: {}, social: {}, sections: {} };
    
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
    
    // Social
    document.querySelectorAll('[name^="social["]').forEach(input => {
        const key = input.name.match(/\[([^\]]+)\]/)[1];
        settings.social[key] = input.value;
    });
    
    // Sections
    document.querySelectorAll('[name^="sections["]').forEach(input => {
        const m = input.name.match(/sections\[([^\]]+)\]\[([^\]]+)\]/);
        if (m) {
            if (!settings.sections[m[1]]) settings.sections[m[1]] = {};
            settings.sections[m[1]][m[2]] = input.type === 'checkbox' ? (input.checked ? '1' : '0') : input.value;
        }
    });
    
    // Branding
    settings.branding.site_logo = document.getElementById('siteLogo')?.value || '';
    settings.branding.site_favicon = document.getElementById('siteFavicon')?.value || '';
    
    // Custom CSS
    settings.custom_css = document.getElementById('customCss')?.value || '';
    
    return settings;
}

// Save
function saveSettings() {
    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined text-lg animate-spin">progress_activity</span> Kaydediliyor...';
    
    fetch('<?php echo admin_url('themes/saveSettings'); ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ theme_slug: themeSlug, settings: collectSettings() })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Başarılı!', 'Tema ayarları kaydedildi', 'success');
            refreshPreview();
        } else {
            showToast('Hata!', data.message || 'Bir sorun oluştu', 'error');
        }
    })
    .catch(e => showToast('Hata!', 'Bağlantı sorunu', 'error'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined text-lg">save</span> Kaydet';
    });
}

// Init
document.addEventListener('DOMContentLoaded', () => {
    setDevice('desktop');
});
</script>

</body>
</html>
