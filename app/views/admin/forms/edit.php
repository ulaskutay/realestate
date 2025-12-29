<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo isset($title) ? esc_html($title) : 'Form Düzenle'; ?></title>
    
    <!-- Dark Mode - Sayfa yüklenmeden önce çalışmalı (FOUC önleme) -->
    <script>
        (function() {
            'use strict';
            const DARK_MODE_KEY = 'admin_dark_mode';
            const htmlElement = document.documentElement;
            let darkModePreference = null;
            try {
                const savedPreference = localStorage.getItem(DARK_MODE_KEY);
                if (savedPreference === 'dark' || savedPreference === 'light') {
                    darkModePreference = savedPreference === 'dark';
                }
            } catch (e) {}
            if (darkModePreference === null) {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    darkModePreference = true;
                } else {
                    darkModePreference = false;
                }
            }
            if (darkModePreference) {
                htmlElement.classList.add('dark');
            } else {
                htmlElement.classList.remove('dark');
            }
        })();
    </script>
    
    
    
    
    <!-- Custom CSS -->
    <link href="<?php echo rtrim(site_url(), '/') . '/admin/css/admin-dashboard.css'; ?>" rel="stylesheet"/>
    <link href="<?php echo rtrim(site_url(), '/') . '/admin/css/form-builder.css'; ?>" rel="stylesheet"/>
    
    <!-- Dark Mode Toggle Script -->
    <script src="<?php echo rtrim(site_url(), '/') . '/admin/js/dark-mode.js'; ?>"></script>
    
    <!-- Tailwind CSS -->
    <script src="<?php echo ViewRenderer::assetUrl('assets/js/tailwind-admin.min.js'); ?>"></script>
    
    <!-- Google Fonts - Inter -->
    <link rel="stylesheet" href="<?php echo ViewRenderer::assetUrl('assets/css/fonts.css'); ?>">
    
    <!-- SortableJS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#137fec",
                        "background-light": "#f6f7f8",
                        "background-dark": "#101922",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                },
            },
        }
    </script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark">
    <div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
        <div class="flex min-h-screen">
            <!-- SideNavBar -->
            <?php 
            $currentPage = 'forms';
            include __DIR__ . '/../snippets/sidebar.php'; 
            ?>

            <!-- Main Content -->
            <main class="main-content-with-sidebar flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b]">
                <div class="layout-content-container flex flex-col w-full mx-auto max-w-7xl">
                    <!-- PageHeading -->
                    <header class="flex flex-col gap-4 mb-6">
                        <div class="flex items-center gap-2 sm:gap-4">
                            <a href="<?php echo admin_url('forms'); ?>" class="p-2 text-gray-600 dark:text-gray-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors flex-shrink-0">
                                <span class="material-symbols-outlined text-lg sm:text-xl">arrow_back</span>
                            </a>
                            <div class="flex flex-col gap-1 min-w-0">
                                <p class="text-gray-900 dark:text-white text-xl sm:text-2xl font-bold tracking-tight line-clamp-2"><?php echo esc_html($form['name']); ?></p>
                                <p class="text-gray-500 dark:text-gray-400 text-xs sm:text-sm">Form alanlarını düzenleyin ve önizleyin.</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="<?php echo admin_url('forms/submissions/' . $form['id']); ?>" class="flex items-center gap-2 px-3 sm:px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-sm">
                                <span class="material-symbols-outlined text-lg sm:text-xl">inbox</span>
                                <span class="font-medium hidden sm:inline">Gönderimler</span>
                            </a>
                            <a href="<?php echo admin_url('forms/preview/' . $form['id']); ?>" target="_blank" class="flex items-center gap-2 px-3 sm:px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-sm">
                                <span class="material-symbols-outlined text-lg sm:text-xl">visibility</span>
                                <span class="font-medium hidden sm:inline">Önizle</span>
                            </a>
                        </div>
                    </header>

                    <!-- Success/Error Message -->
                    <?php if (isset($message) && $message): ?>
                        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200'; ?>">
                            <p class="text-sm font-medium"><?php echo esc_html($message); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Tabs -->
                    <div class="flex border-b border-gray-200 dark:border-white/10 mb-6 overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0 scrollbar-hide">
                        <button type="button" class="tab-btn active px-3 sm:px-4 py-3 text-sm font-medium border-b-2 border-primary text-primary whitespace-nowrap" data-tab="builder">
                            <span class="material-symbols-outlined text-base sm:text-lg align-middle mr-1">construction</span>
                            <span class="hidden sm:inline">Form Oluşturucu</span>
                            <span class="sm:hidden">Oluşturucu</span>
                        </button>
                        <button type="button" class="tab-btn px-3 sm:px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 whitespace-nowrap" data-tab="settings">
                            <span class="material-symbols-outlined text-base sm:text-lg align-middle mr-1">settings</span>
                            Ayarlar
                        </button>
                        <button type="button" class="tab-btn px-3 sm:px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 whitespace-nowrap" data-tab="preview">
                            <span class="material-symbols-outlined text-base sm:text-lg align-middle mr-1">preview</span>
                            Önizleme
                        </button>
                    </div>

                    <!-- Tab Content: Form Builder -->
                    <div id="tab-builder" class="tab-content">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6">
                            <!-- Sol Panel: Alan Türleri -->
                            <div class="lg:col-span-3 order-2 lg:order-1">
                                <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark p-3 sm:p-4 lg:sticky lg:top-4">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Alan Türleri</h3>
                                    
                                    <!-- Giriş Alanları -->
                                    <div class="mb-4">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Giriş Alanları</p>
                                        <div class="space-y-1">
                                            <?php 
                                            $inputTypes = ['text', 'email', 'phone', 'number', 'textarea', 'date', 'time', 'file'];
                                            foreach ($fieldTypes as $type => $info): 
                                                if (!in_array($type, $inputTypes)) continue;
                                            ?>
                                                <button type="button" class="field-type-btn w-full flex items-center gap-2 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors" data-type="<?php echo esc_attr($type); ?>">
                                                    <span class="material-symbols-outlined text-lg text-gray-500"><?php echo esc_attr($info['icon']); ?></span>
                                                    <span><?php echo esc_html($info['label']); ?></span>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Seçim Alanları -->
                                    <div class="mb-4">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Seçim Alanları</p>
                                        <div class="space-y-1">
                                            <?php 
                                            $choiceTypes = ['select', 'checkbox', 'radio'];
                                            foreach ($fieldTypes as $type => $info): 
                                                if (!in_array($type, $choiceTypes)) continue;
                                            ?>
                                                <button type="button" class="field-type-btn w-full flex items-center gap-2 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors" data-type="<?php echo esc_attr($type); ?>">
                                                    <span class="material-symbols-outlined text-lg text-gray-500"><?php echo esc_attr($info['icon']); ?></span>
                                                    <span><?php echo esc_html($info['label']); ?></span>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Düzen Elemanları -->
                                    <div class="mb-4">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Düzen Elemanları</p>
                                        <div class="space-y-1">
                                            <?php 
                                            $layoutTypes = ['heading', 'paragraph', 'divider', 'hidden'];
                                            foreach ($fieldTypes as $type => $info): 
                                                if (!in_array($type, $layoutTypes)) continue;
                                            ?>
                                                <button type="button" class="field-type-btn w-full flex items-center gap-2 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors" data-type="<?php echo esc_attr($type); ?>">
                                                    <span class="material-symbols-outlined text-lg text-gray-500"><?php echo esc_attr($info['icon']); ?></span>
                                                    <span><?php echo esc_html($info['label']); ?></span>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Orta Panel: Form Alanları -->
                            <div class="lg:col-span-5 order-1 lg:order-2">
                                <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark">
                                    <div class="p-3 sm:p-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Form Alanları</h3>
                                        <span class="text-xs text-gray-500 dark:text-gray-400" id="field-count"><?php echo count($form['fields'] ?? []); ?> alan</span>
                                    </div>
                                    
                                    <div id="form-fields-container" class="p-3 sm:p-4 min-h-[300px]">
                                        <?php if (empty($form['fields'])): ?>
                                            <div id="empty-state" class="flex flex-col items-center justify-center py-12 text-center">
                                                <span class="material-symbols-outlined text-gray-400 dark:text-gray-600 text-5xl mb-3">add_circle</span>
                                                <p class="text-gray-500 dark:text-gray-400">Henüz alan eklenmemiş</p>
                                                <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Soldaki listeden bir alan türü seçin</p>
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($form['fields'] as $field): ?>
                                                <?php $isRequired = (int)($field['required'] ?? 0) === 1; ?>
                                                <div class="field-item mb-2 p-3 bg-gray-50 dark:bg-white/5 rounded-lg border border-gray-200 dark:border-white/10 cursor-move" data-field-id="<?php echo esc_attr($field['id']); ?>">
                                                    <div class="flex items-center gap-2 sm:gap-3">
                                                        <span class="material-symbols-outlined text-gray-400 text-base sm:text-lg drag-handle flex-shrink-0">drag_indicator</span>
                                                        <span class="material-symbols-outlined text-gray-500 text-base sm:text-lg flex-shrink-0"><?php echo esc_attr($fieldTypes[$field['type']]['icon'] ?? 'text_fields'); ?></span>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-xs sm:text-sm font-medium text-gray-900 dark:text-white truncate field-label"><?php echo esc_html($field['label']); ?></p>
                                                            <p class="text-xs text-gray-500 dark:text-gray-400 field-subtext"><?php echo esc_html($fieldTypes[$field['type']]['label'] ?? $field['type']); ?> <?php echo $isRequired ? '• Zorunlu' : ''; ?></p>
                                                        </div>
                                                        <div class="flex items-center gap-1 flex-shrink-0">
                                                            <button type="button" class="edit-field-btn p-1.5 text-gray-500 hover:text-primary hover:bg-primary/10 rounded transition-colors min-h-[36px] min-w-[36px] flex items-center justify-center" data-field-id="<?php echo esc_attr($field['id']); ?>" title="Düzenle">
                                                                <span class="material-symbols-outlined text-base sm:text-lg">edit</span>
                                                            </button>
                                                            <button type="button" class="delete-field-btn p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors min-h-[36px] min-w-[36px] flex items-center justify-center" data-field-id="<?php echo esc_attr($field['id']); ?>" title="Sil">
                                                                <span class="material-symbols-outlined text-base sm:text-lg">delete</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Sağ Panel: Alan Düzenleme -->
                            <div class="lg:col-span-4 order-3">
                                <div id="field-editor" class="rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark lg:sticky lg:top-4">
                                    <div class="p-3 sm:p-4 border-b border-gray-200 dark:border-white/10">
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Alan Düzenle</h3>
                                    </div>
                                    
                                    <div id="field-editor-content" class="p-3 sm:p-4">
                                        <div class="flex flex-col items-center justify-center py-8 text-center">
                                            <span class="material-symbols-outlined text-gray-400 dark:text-gray-600 text-4xl mb-3">touch_app</span>
                                            <p class="text-gray-500 dark:text-gray-400 text-sm">Düzenlemek için bir alan seçin</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Content: Settings -->
                    <div id="tab-settings" class="tab-content hidden">
                        <form action="<?php echo admin_url('forms/update/' . $form['id']); ?>" method="POST" class="space-y-6">
                            
                            <!-- Temel Ayarlar -->
                            <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark p-4 sm:p-6">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4">Temel Ayarlar</h3>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Form Adı *</label>
                                        <input type="text" name="name" required value="<?php echo esc_attr($form['name']); ?>"
                                               class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Açıklama</label>
                                        <textarea name="description" rows="2"
                                                  class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"><?php echo esc_html($form['description'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Kısa Kod</label>
                                        <div class="flex items-center gap-2">
                                            <input type="text" readonly value='[form slug="<?php echo esc_attr($form['slug']); ?>"]'
                                                   class="flex-1 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 cursor-text" id="shortcode-input">
                                            <button type="button" onclick="copyShortcode()" class="px-3 py-2 bg-gray-200 dark:bg-gray-700 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors" title="Kopyala">
                                                <span class="material-symbols-outlined text-lg">content_copy</span>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Form Stili</label>
                                            <select name="form_style" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                                <option value="default" <?php echo ($form['form_style'] ?? '') === 'default' ? 'selected' : ''; ?>>Varsayılan</option>
                                                <option value="modern" <?php echo ($form['form_style'] ?? '') === 'modern' ? 'selected' : ''; ?>>Modern</option>
                                                <option value="minimal" <?php echo ($form['form_style'] ?? '') === 'minimal' ? 'selected' : ''; ?>>Minimal</option>
                                                <option value="bordered" <?php echo ($form['form_style'] ?? '') === 'bordered' ? 'selected' : ''; ?>>Kenarlıklı</option>
                                            </select>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Düzen</label>
                                            <select name="layout" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                                <option value="vertical" <?php echo ($form['layout'] ?? '') === 'vertical' ? 'selected' : ''; ?>>Dikey</option>
                                                <option value="horizontal" <?php echo ($form['layout'] ?? '') === 'horizontal' ? 'selected' : ''; ?>>Yatay</option>
                                                <option value="inline" <?php echo ($form['layout'] ?? '') === 'inline' ? 'selected' : ''; ?>>Satır İçi</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Gönder Butonu Ayarları -->
                            <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark p-4 sm:p-6">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4">Gönder Butonu</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buton Metni</label>
                                        <input type="text" name="submit_button_text" value="<?php echo esc_attr($form['submit_button_text'] ?? 'Gönder'); ?>"
                                               class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buton Rengi</label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" name="submit_button_color" value="<?php echo esc_attr($form['submit_button_color'] ?? '#137fec'); ?>" 
                                                   class="w-12 h-10 rounded cursor-pointer border-0">
                                            <input type="text" id="settings_button_color_text" value="<?php echo esc_attr($form['submit_button_color'] ?? '#137fec'); ?>" 
                                                   class="flex-1 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Gönderim Sonrası -->
                            <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark p-4 sm:p-6">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4">Gönderim Sonrası</h3>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Başarı Mesajı</label>
                                        <textarea name="success_message" rows="2"
                                                  class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"><?php echo esc_html($form['success_message'] ?? 'Formunuz başarıyla gönderildi!'); ?></textarea>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Yönlendirme URL'si</label>
                                        <input type="url" name="redirect_url" value="<?php echo esc_attr($form['redirect_url'] ?? ''); ?>"
                                               class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                                               placeholder="https://example.com/tesekkurler">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- E-posta Bildirimi -->
                            <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark p-4 sm:p-6">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4">E-posta Bildirimi</h3>
                                
                                <div class="space-y-4">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="checkbox" name="email_notification" value="1" <?php echo ($form['email_notification'] ?? 0) ? 'checked' : ''; ?>
                                               class="w-5 h-5 rounded text-primary focus:ring-primary border-gray-300 dark:border-gray-600">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Yeni gönderimde e-posta bildirimi gönder</span>
                                    </label>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Bildirim E-postası</label>
                                            <input type="email" name="notification_email" value="<?php echo esc_attr($form['notification_email'] ?? ''); ?>"
                                                   class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">E-posta Konusu</label>
                                            <input type="text" name="email_subject" value="<?php echo esc_attr($form['email_subject'] ?? ''); ?>"
                                                   class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Durum -->
                            <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-background-light dark:bg-background-dark p-4 sm:p-6">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4">Durum</h3>
                                
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="status" value="active" <?php echo ($form['status'] ?? '') === 'active' ? 'checked' : ''; ?>
                                           class="w-5 h-5 rounded text-primary focus:ring-primary border-gray-300 dark:border-gray-600">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Form aktif (gönderimleri kabul eder)</span>
                                </label>
                            </div>
                            
                            <!-- Kaydet -->
                            <div class="flex flex-col sm:flex-row justify-end gap-3">
                                <a href="<?php echo admin_url('forms'); ?>" class="px-6 py-2.5 sm:py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-center min-h-[44px] flex items-center justify-center">
                                    İptal
                                </a>
                                <button type="submit" class="px-6 py-2.5 sm:py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors min-h-[44px]">
                                    Ayarları Kaydet
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab Content: Preview -->
                    <div id="tab-preview" class="tab-content hidden">
                        <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 p-4 sm:p-8">
                            <div class="max-w-2xl mx-auto">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2"><?php echo esc_html($form['name']); ?></h3>
                                <?php if (!empty($form['description'])): ?>
                                    <p class="text-gray-500 dark:text-gray-400 mb-6"><?php echo esc_html($form['description']); ?></p>
                                <?php endif; ?>
                                
                                <div id="preview-container">
                                    <!-- Önizleme buraya yüklenecek -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Alan Düzenleme Modal/Template -->
    <template id="field-editor-template">
        <form id="field-edit-form" class="space-y-4">
            <input type="hidden" name="field_id" id="edit-field-id">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Etiket</label>
                <input type="text" name="label" id="edit-label" required
                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alan Adı</label>
                <input type="text" name="name" id="edit-name"
                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                <p class="text-xs text-gray-500 mt-1">E-posta için: email, telefon için: phone vb.</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Placeholder</label>
                <input type="text" name="placeholder" id="edit-placeholder"
                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            
            <div id="edit-default-value-group">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Varsayılan Değer</label>
                <input type="text" name="default_value" id="edit-default-value"
                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            
            <div id="edit-options-group" class="hidden">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Seçenekler</label>
                <div id="options-container" class="space-y-2 mb-2">
                    <!-- Seçenekler buraya eklenecek -->
                </div>
                <button type="button" id="add-option-btn" class="text-sm text-primary hover:text-primary/80 flex items-center gap-1">
                    <span class="material-symbols-outlined text-lg">add</span>
                    Seçenek Ekle
                </button>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Genişlik</label>
                <select name="width" id="edit-width"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="full">Tam Genişlik</option>
                    <option value="half">Yarım (%50)</option>
                    <option value="third">Üçte Bir (%33)</option>
                    <option value="quarter">Çeyrek (%25)</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Yardım Metni</label>
                <input type="text" name="help_text" id="edit-help-text"
                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            
            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="required" id="edit-required"
                           class="w-4 h-4 rounded text-primary focus:ring-primary border-gray-300 dark:border-gray-600">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Zorunlu alan</span>
                </label>
            </div>
            
            <div class="flex justify-end gap-2 pt-2 border-t border-gray-200 dark:border-white/10">
                <button type="button" id="cancel-edit-btn" class="px-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    İptal
                </button>
                <button type="submit" class="px-4 py-2 text-sm bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                    Kaydet
                </button>
            </div>
        </form>
    </template>
    
    <script>
        // Form Builder JavaScript
        const FormBuilder = {
            formId: <?php echo (int)$form['id']; ?>,
            fields: <?php echo json_encode($form['fields'] ?? []); ?>,
            fieldTypes: <?php echo json_encode($fieldTypes); ?>,
            currentFieldId: null,
            
            init() {
                this.bindEvents();
                this.initSortable();
                this.loadPreview();
            },
            
            bindEvents() {
                // Tab değiştirme
                document.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.addEventListener('click', () => this.switchTab(btn.dataset.tab));
                });
                
                // Alan türü ekleme
                document.querySelectorAll('.field-type-btn').forEach(btn => {
                    btn.addEventListener('click', () => this.addField(btn.dataset.type));
                });
                
                // Alan düzenleme
                document.addEventListener('click', (e) => {
                    if (e.target.closest('.edit-field-btn')) {
                        const fieldId = e.target.closest('.edit-field-btn').dataset.fieldId;
                        this.editField(fieldId);
                    }
                    if (e.target.closest('.delete-field-btn')) {
                        const fieldId = e.target.closest('.delete-field-btn').dataset.fieldId;
                        this.deleteField(fieldId);
                    }
                });
                
                // Renk seçici senkronizasyonu
                const colorInput = document.querySelector('[name=submit_button_color]');
                if (colorInput) {
                    colorInput.addEventListener('input', function() {
                        document.getElementById('settings_button_color_text').value = this.value;
                    });
                }
            },
            
            initSortable() {
                const container = document.getElementById('form-fields-container');
                if (container) {
                    new Sortable(container, {
                        animation: 150,
                        handle: '.drag-handle',
                        onEnd: () => this.updateFieldOrder()
                    });
                }
            },
            
            switchTab(tab) {
                // Tab butonlarını güncelle
                document.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.classList.remove('active', 'border-primary', 'text-primary');
                    btn.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
                });
                
                const activeBtn = document.querySelector(`[data-tab="${tab}"]`);
                activeBtn.classList.add('active', 'border-primary', 'text-primary');
                activeBtn.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
                
                // Tab içeriklerini güncelle
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.add('hidden');
                });
                document.getElementById(`tab-${tab}`).classList.remove('hidden');
                
                // Önizleme tabı açıldıysa güncelle
                if (tab === 'preview') {
                    this.loadPreview();
                }
            },
            
            async addField(type) {
                const label = this.fieldTypes[type]?.label || 'Yeni Alan';
                
                try {
                    const formData = new FormData();
                    formData.append('form_id', this.formId);
                    formData.append('type', type);
                    formData.append('label', label);
                    
                    const response = await fetch('<?php echo admin_url('forms/add-field'); ?>', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Alanı listeye ekle
                        this.renderField(data.field);
                        this.updateFieldCount();
                        
                        // Yeni alanı düzenle
                        this.editField(data.field.id);
                    } else {
                        alert(data.message || 'Alan eklenirken hata oluştu');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Alan eklenirken hata oluştu');
                }
            },
            
            renderField(field) {
                // Boş state'i kaldır
                const emptyState = document.getElementById('empty-state');
                if (emptyState) {
                    emptyState.remove();
                }
                
                // required değerini integer'a çevir (string "0" truthy olduğu için)
                const isRequired = parseInt(field.required) === 1;
                
                const container = document.getElementById('form-fields-container');
                const fieldHtml = `
                    <div class="field-item mb-2 p-3 bg-gray-50 dark:bg-white/5 rounded-lg border border-gray-200 dark:border-white/10 cursor-move" data-field-id="${field.id}">
                        <div class="flex items-center gap-2 sm:gap-3">
                            <span class="material-symbols-outlined text-gray-400 text-base sm:text-lg drag-handle flex-shrink-0">drag_indicator</span>
                            <span class="material-symbols-outlined text-gray-500 text-base sm:text-lg flex-shrink-0">${this.fieldTypes[field.type]?.icon || 'text_fields'}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs sm:text-sm font-medium text-gray-900 dark:text-white truncate field-label">${this.escapeHtml(field.label)}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 field-subtext">${this.fieldTypes[field.type]?.label || field.type} ${isRequired ? '• Zorunlu' : ''}</p>
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button type="button" class="edit-field-btn p-1.5 text-gray-500 hover:text-primary hover:bg-primary/10 rounded transition-colors min-h-[36px] min-w-[36px] flex items-center justify-center" data-field-id="${field.id}" title="Düzenle">
                                    <span class="material-symbols-outlined text-base sm:text-lg">edit</span>
                                </button>
                                <button type="button" class="delete-field-btn p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors min-h-[36px] min-w-[36px] flex items-center justify-center" data-field-id="${field.id}" title="Sil">
                                    <span class="material-symbols-outlined text-base sm:text-lg">delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', fieldHtml);
                
                // Alanı fields dizisine ekle
                this.fields.push(field);
            },
            
            async editField(fieldId) {
                this.currentFieldId = fieldId;
                
                try {
                    const response = await fetch(`<?php echo admin_url('forms/get-field/'); ?>${fieldId}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        this.showFieldEditor(data.field);
                    } else {
                        alert(data.message || 'Alan verisi alınamadı');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Alan verisi alınamadı');
                }
            },
            
            showFieldEditor(field) {
                const template = document.getElementById('field-editor-template');
                const content = template.content.cloneNode(true);
                
                // Form alanlarını doldur
                content.querySelector('#edit-field-id').value = field.id;
                content.querySelector('#edit-label').value = field.label || '';
                content.querySelector('#edit-name').value = field.name || '';
                content.querySelector('#edit-placeholder').value = field.placeholder || '';
                content.querySelector('#edit-default-value').value = field.default_value || '';
                content.querySelector('#edit-width').value = field.width || 'full';
                content.querySelector('#edit-help-text').value = field.help_text || '';
                content.querySelector('#edit-required').checked = parseInt(field.required) === 1;
                
                // Seçenek alanlarını göster/gizle
                const optionsGroup = content.querySelector('#edit-options-group');
                if (['select', 'checkbox', 'radio'].includes(field.type)) {
                    optionsGroup.classList.remove('hidden');
                    this.renderOptions(content.querySelector('#options-container'), field.options || []);
                }
                
                // Layout elemanları için varsayılan değer alanını gizle/göster
                const defaultValueGroup = content.querySelector('#edit-default-value-group');
                if (['heading', 'divider'].includes(field.type)) {
                    defaultValueGroup.classList.add('hidden');
                }
                
                // Editör içeriğini güncelle
                const editorContent = document.getElementById('field-editor-content');
                editorContent.innerHTML = '';
                editorContent.appendChild(content);
                
                // Event'ları bağla
                this.bindEditorEvents(field);
            },
            
            renderOptions(container, options) {
                container.innerHTML = '';
                
                if (!options || options.length === 0) {
                    options = [{ value: '', label: '' }];
                }
                
                options.forEach((option, index) => {
                    const optionHtml = `
                        <div class="flex items-center gap-2 option-row">
                            <input type="text" name="options[${index}][value]" value="${this.escapeHtml(option.value || option)}" placeholder="Değer"
                                   class="flex-1 px-3 py-1.5 text-sm rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                            <input type="text" name="options[${index}][label]" value="${this.escapeHtml(option.label || option)}" placeholder="Etiket"
                                   class="flex-1 px-3 py-1.5 text-sm rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                            <button type="button" class="remove-option-btn p-1 text-gray-400 hover:text-red-600" title="Kaldır">
                                <span class="material-symbols-outlined text-lg">close</span>
                            </button>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', optionHtml);
                });
            },
            
            bindEditorEvents(field) {
                // Form submit
                const form = document.getElementById('field-edit-form');
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.saveField(form, field.id);
                });
                
                // İptal
                document.getElementById('cancel-edit-btn').addEventListener('click', () => {
                    this.closeFieldEditor();
                });
                
                // Seçenek ekle
                const addOptionBtn = document.getElementById('add-option-btn');
                if (addOptionBtn) {
                    addOptionBtn.addEventListener('click', () => {
                        const container = document.getElementById('options-container');
                        const index = container.querySelectorAll('.option-row').length;
                        const optionHtml = `
                            <div class="flex items-center gap-2 option-row">
                                <input type="text" name="options[${index}][value]" placeholder="Değer"
                                       class="flex-1 px-3 py-1.5 text-sm rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                <input type="text" name="options[${index}][label]" placeholder="Etiket"
                                       class="flex-1 px-3 py-1.5 text-sm rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                <button type="button" class="remove-option-btn p-1 text-gray-400 hover:text-red-600" title="Kaldır">
                                    <span class="material-symbols-outlined text-lg">close</span>
                                </button>
                            </div>
                        `;
                        container.insertAdjacentHTML('beforeend', optionHtml);
                    });
                }
                
                // Seçenek kaldır
                document.getElementById('options-container')?.addEventListener('click', (e) => {
                    if (e.target.closest('.remove-option-btn')) {
                        e.target.closest('.option-row').remove();
                    }
                });
            },
            
            async saveField(form, fieldId) {
                const formData = new FormData(form);
                
                // Seçenekleri düzenle
                const options = [];
                const optionRows = document.querySelectorAll('.option-row');
                optionRows.forEach(row => {
                    const value = row.querySelector('[name*="[value]"]').value;
                    const label = row.querySelector('[name*="[label]"]').value;
                    if (value || label) {
                        options.push({ value: value || label, label: label || value });
                    }
                });
                
                if (options.length > 0) {
                    options.forEach((opt, i) => {
                        formData.append(`options[${i}][value]`, opt.value);
                        formData.append(`options[${i}][label]`, opt.label);
                    });
                }
                
                // Checkbox değerlerini düzelt
                formData.set('required', document.getElementById('edit-required').checked ? 1 : 0);
                
                try {
                    const response = await fetch(`<?php echo admin_url('forms/update-field/'); ?>${fieldId}`, {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Listedeki alanı güncelle
                        this.updateFieldInList(data.field);
                        this.closeFieldEditor();
                    } else {
                        alert(data.message || 'Alan kaydedilirken hata oluştu');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Alan kaydedilirken hata oluştu');
                }
            },
            
            updateFieldInList(field) {
                // required değerini integer'a çevir (string "0" truthy olduğu için)
                const isRequired = parseInt(field.required) === 1;
                
                const fieldItem = document.querySelector(`[data-field-id="${field.id}"]`);
                if (fieldItem) {
                    const labelEl = fieldItem.querySelector('.field-label');
                    const subTextEl = fieldItem.querySelector('.field-subtext');
                    
                    if (labelEl) {
                        labelEl.textContent = field.label;
                    }
                    if (subTextEl) {
                        subTextEl.textContent = `${this.fieldTypes[field.type]?.label || field.type} ${isRequired ? '• Zorunlu' : ''}`;
                    }
                }
                
                // fields dizisini güncelle
                const index = this.fields.findIndex(f => f.id == field.id);
                if (index > -1) {
                    this.fields[index] = field;
                }
            },
            
            closeFieldEditor() {
                const editorContent = document.getElementById('field-editor-content');
                editorContent.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-8 text-center">
                        <span class="material-symbols-outlined text-gray-400 dark:text-gray-600 text-4xl mb-3">touch_app</span>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Düzenlemek için bir alan seçin</p>
                    </div>
                `;
                this.currentFieldId = null;
            },
            
            async deleteField(fieldId) {
                if (!confirm('Bu alanı silmek istediğinizden emin misiniz?')) {
                    return;
                }
                
                try {
                    const response = await fetch(`<?php echo admin_url('forms/delete-field/'); ?>${fieldId}`, {
                        method: 'POST'
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Alanı listeden kaldır
                        const fieldItem = document.querySelector(`[data-field-id="${fieldId}"]`);
                        if (fieldItem) {
                            fieldItem.remove();
                        }
                        
                        // fields dizisinden kaldır
                        this.fields = this.fields.filter(f => f.id != fieldId);
                        
                        // Eğer düzenlenen alan silindiyse editörü kapat
                        if (this.currentFieldId == fieldId) {
                            this.closeFieldEditor();
                        }
                        
                        this.updateFieldCount();
                        
                        // Eğer alan kalmadıysa boş state göster
                        if (this.fields.length === 0) {
                            const container = document.getElementById('form-fields-container');
                            container.innerHTML = `
                                <div id="empty-state" class="flex flex-col items-center justify-center py-12 text-center">
                                    <span class="material-symbols-outlined text-gray-400 dark:text-gray-600 text-5xl mb-3">add_circle</span>
                                    <p class="text-gray-500 dark:text-gray-400">Henüz alan eklenmemiş</p>
                                    <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Soldaki listeden bir alan türü seçin</p>
                                </div>
                            `;
                        }
                    } else {
                        alert(data.message || 'Alan silinirken hata oluştu');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Alan silinirken hata oluştu');
                }
            },
            
            async updateFieldOrder() {
                const fieldItems = document.querySelectorAll('.field-item');
                const fieldIds = Array.from(fieldItems).map(item => item.dataset.fieldId);
                
                try {
                    const formData = new FormData();
                    formData.append('fields', JSON.stringify(fieldIds));
                    
                    const response = await fetch('<?php echo admin_url('forms/update-field-order'); ?>', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (!data.success) {
                        console.error('Sıralama güncellenemedi:', data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            },
            
            updateFieldCount() {
                const count = document.querySelectorAll('.field-item').length;
                document.getElementById('field-count').textContent = `${count} alan`;
            },
            
            async loadPreview() {
                const container = document.getElementById('preview-container');
                
                try {
                    const response = await fetch(`<?php echo admin_url('forms/get-preview-html/'); ?>${this.formId}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        container.innerHTML = data.html;
                    } else {
                        container.innerHTML = '<p class="text-gray-500">Önizleme yüklenemedi.</p>';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    container.innerHTML = '<p class="text-gray-500">Önizleme yüklenemedi.</p>';
                }
            },
            
            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        };
        
        // Kısa kod kopyalama
        function copyShortcode() {
            const input = document.getElementById('shortcode-input');
            input.select();
            document.execCommand('copy');
            
            // Görsel feedback
            const btn = event.target.closest('button');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<span class="material-symbols-outlined text-lg text-green-600">check</span>';
            setTimeout(() => {
                btn.innerHTML = originalHtml;
            }, 1500);
        }
        
        // Sayfa yüklendiğinde başlat
        document.addEventListener('DOMContentLoaded', () => {
            FormBuilder.init();
        });
    </script>
</body>
</html>

