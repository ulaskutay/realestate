<?php include __DIR__ . '/../snippets/header.php'; ?>
<!-- Media Picker -->
<script src="<?php echo rtrim(site_url(), '/') . '/admin/js/media-picker.js'; ?>"></script>
<!-- Quill Editor -->
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<style>
.ql-editor { min-height: 300px; font-size: 14px; line-height: 1.6; }
.ql-toolbar.ql-snow { border-radius: 8px 8px 0 0; border-color: #e5e7eb; background: #f9fafb; }
.ql-container.ql-snow { border-radius: 0 0 8px 8px; border-color: #e5e7eb; }
.dark .ql-toolbar.ql-snow { background: #1f2937; border-color: #374151; }
.dark .ql-container.ql-snow { background: #111827; border-color: #374151; }
.dark .ql-editor { color: #f3f4f6; }
.dark .ql-snow .ql-stroke { stroke: #9ca3af; }
.dark .ql-snow .ql-fill { fill: #9ca3af; }
.dark .ql-snow .ql-picker { color: #9ca3af; }
.dark .ql-snow .ql-picker-options { background: #1f2937; }

.repeater-item { transition: all 0.2s ease; }
.repeater-item:hover { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }

.tab-btn { transition: all 0.2s ease; }
.tab-btn.active { color: var(--color-primary, #2563eb); border-color: var(--color-primary, #2563eb); background: rgba(37, 99, 235, 0.05); }
.tab-content { display: none; }
.tab-content.active { display: block; }

.template-card { transition: all 0.3s ease; }
.template-card:hover { transform: translateY(-4px); }
.template-check { transition: all 0.2s ease; }
</style>
<?php
$customFieldDefinitions = $customFieldDefinitions ?? Page::getCustomFieldDefinitions();

// Şablonlara göre alan grupları
$templateFieldGroups = [
    'default' => [],
    'service-detail' => [
        'hero' => ['label' => 'Hero Bölümü', 'icon' => 'image'],
        'features' => ['label' => 'Özellikler', 'icon' => 'star'],
        'process' => ['label' => 'Süreç Adımları', 'icon' => 'timeline'],
        'advantages' => ['label' => 'Avantajlar', 'icon' => 'trending_up'],
        'faq' => ['label' => 'SSS', 'icon' => 'help'],
        'related' => ['label' => 'İlgili Hizmetler', 'icon' => 'link'],
        'cta' => ['label' => 'CTA Ayarları', 'icon' => 'campaign']
    ],
    'about' => [
        'about' => ['label' => 'Hikâye', 'icon' => 'menu_book'],
        'team' => ['label' => 'Ekip', 'icon' => 'groups'],
        'stats' => ['label' => 'İstatistikler', 'icon' => 'insights'],
        'cta' => ['label' => 'CTA Ayarları', 'icon' => 'campaign']
    ]
];

// Tüm grupları birleştir (tab rendering için)
$fieldGroups = [];
foreach ($templateFieldGroups as $groups) {
    $fieldGroups = array_merge($fieldGroups, $groups);
}
// Tekrar edenleri temizle
$fieldGroups = array_unique($fieldGroups, SORT_REGULAR);
?>
<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <?php 
        $currentPage = 'pages';
        include __DIR__ . '/../snippets/sidebar.php'; 
        ?>

        <main class="main-content-with-sidebar flex-1 p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-[#15202b]">
            <div class="layout-content-container flex flex-col w-full mx-auto max-w-6xl">
                
                <!-- Header -->
                <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div class="flex items-center gap-4">
                        <a href="<?php echo admin_url('pages'); ?>" class="p-2 text-gray-500 hover:text-primary hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-all">
                            <span class="material-symbols-outlined">arrow_back</span>
                        </a>
                        <div>
                            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Yeni Sayfa Oluştur</h1>
                            <p class="text-sm text-gray-500 mt-1">Hizmet detay sayfası veya statik sayfa oluşturun</p>
                        </div>
                    </div>
                </header>

                <?php if (!empty($message)): ?>
                <div class="mb-6 p-4 rounded-xl <?php echo $messageType === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800'; ?>">
                    <p class="text-sm font-medium flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg"><?php echo $messageType === 'success' ? 'check_circle' : 'error'; ?></span>
                        <?php echo esc_html($message); ?>
                    </p>
                </div>
                <?php endif; ?>

                <form action="<?php echo admin_url('pages/store'); ?>" method="POST" id="page-form">
                    
                    <!-- Şablon Seçimi (İlk Adım) -->
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-900 rounded-xl border-2 border-blue-200 dark:border-blue-900 p-8 mb-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center">
                                <span class="material-symbols-outlined text-white text-2xl">palette</span>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Sayfa Şablonu Seçin</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Oluşturacağınız sayfanın türünü belirleyin</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Varsayılan Şablon -->
                            <label class="template-option cursor-pointer">
                                <input type="radio" name="custom_fields[page_template]" value="default" class="hidden template-radio" checked>
                                <div class="template-card bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-xl p-6 transition-all hover:border-blue-500 hover:shadow-lg">
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="material-symbols-outlined text-4xl text-gray-400">description</span>
                                        <span class="template-check hidden w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center">
                                            <span class="material-symbols-outlined text-white text-sm">check</span>
                                        </span>
                                    </div>
                                    <h4 class="font-bold text-gray-900 dark:text-white mb-2">Varsayılan Sayfa</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Basit içerik sayfaları için</p>
                                </div>
                            </label>
                            
                            <!-- Hizmet Detay Şablonu -->
                            <label class="template-option cursor-pointer">
                                <input type="radio" name="custom_fields[page_template]" value="service-detail" class="hidden template-radio">
                                <div class="template-card bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-xl p-6 transition-all hover:border-blue-500 hover:shadow-lg">
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="material-symbols-outlined text-4xl text-blue-500">construction</span>
                                        <span class="template-check hidden w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center">
                                            <span class="material-symbols-outlined text-white text-sm">check</span>
                                        </span>
                                    </div>
                                    <h4 class="font-bold text-gray-900 dark:text-white mb-2">Hizmet Detay</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Özellikler, süreç, SSS ile</p>
                                </div>
                            </label>
                            
                            <!-- Hakkımızda Şablonu -->
                            <label class="template-option cursor-pointer">
                                <input type="radio" name="custom_fields[page_template]" value="about" class="hidden template-radio">
                                <div class="template-card bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-xl p-6 transition-all hover:border-blue-500 hover:shadow-lg">
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="material-symbols-outlined text-4xl text-purple-500">groups</span>
                                        <span class="template-check hidden w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center">
                                            <span class="material-symbols-outlined text-white text-sm">check</span>
                                        </span>
                                    </div>
                                    <h4 class="font-bold text-gray-900 dark:text-white mb-2">Hakkımızda</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Hikâye, ekip, istatistikler</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Sol Kolon - Ana İçerik -->
                        <div class="lg:col-span-2 space-y-6">
                            
                            <!-- Temel Bilgiler -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary">edit_note</span>
                                    Temel Bilgiler
                                </h3>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Başlık *</label>
                                        <input type="text" id="title" name="title" required
                                            class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent text-lg font-medium"
                                            placeholder="Sayfa başlığını girin...">
                                    </div>
                                    
                                    <div>
                                        <label for="excerpt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Özet (Hero Açıklaması)</label>
                                        <textarea id="excerpt" name="excerpt" rows="3"
                                            class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
                                            placeholder="Sayfanın kısa açıklaması... (Hero bölümünde görünür)"></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- İçerik Editörü -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary">article</span>
                                        İçerik
                                    </h3>
                                    <button type="button" onclick="toggleEditorMode()" class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                        <span class="material-symbols-outlined text-sm align-middle mr-1">code</span>
                                        HTML
                                    </button>
                                </div>
                                <div id="quill-editor"></div>
                                <textarea id="content" name="content" class="hidden"></textarea>
                            </div>
                            
                            <!-- Özel Alanlar (Şablona Göre Dinamik) -->
                            <div id="custom-fields-container" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden" style="display: none;">
                                <div class="border-b border-gray-200 dark:border-gray-700">
                                    <nav class="flex overflow-x-auto" id="custom-fields-tabs">
                                        <?php $firstTab = true; foreach ($fieldGroups as $groupKey => $group): ?>
                                        <button type="button" 
                                            class="tab-btn flex items-center gap-2 px-4 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 border-b-2 border-transparent whitespace-nowrap <?php echo $firstTab ? 'active' : ''; ?>"
                                            data-tab="<?php echo $groupKey; ?>"
                                            data-templates="">
                                            <span class="material-symbols-outlined text-lg"><?php echo $group['icon']; ?></span>
                                            <span class="hidden sm:inline"><?php echo $group['label']; ?></span>
                                        </button>
                                        <?php $firstTab = false; endforeach; ?>
                                    </nav>
                                </div>
                                
                                <div class="p-6">
                                    <?php $firstContent = true; foreach ($fieldGroups as $groupKey => $group): ?>
                                    <div class="tab-content <?php echo $firstContent ? 'active' : ''; ?>" data-content="<?php echo $groupKey; ?>">
                                        <div class="space-y-6">
                                            <?php 
                                            foreach ($customFieldDefinitions as $key => $field): 
                                                // Skip hidden fields and fields without group
                                                if (!isset($field['group']) || $field['group'] === 'hidden') continue;
                                                if ($field['group'] !== $groupKey) continue;
                                                $value = $field['default'] ?? '';
                                            ?>
                                            
                                            <?php if ($field['type'] === 'repeater'): ?>
                                            <!-- Repeater Field -->
                                            <div class="repeater-container" data-field="<?php echo $key; ?>">
                                                <div class="flex items-center justify-between mb-4">
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                        <?php echo esc_html($field['label']); ?>
                                                    </label>
                                                    <button type="button" onclick="addRepeaterItem('<?php echo $key; ?>')" 
                                                        class="flex items-center gap-1 px-3 py-1.5 bg-primary text-white text-sm rounded-lg hover:bg-primary/90 transition-colors">
                                                        <span class="material-symbols-outlined text-sm">add</span>
                                                        Ekle
                                                    </button>
                                                </div>
                                                <?php if (!empty($field['description'])): ?>
                                                <p class="text-gray-500 text-xs mb-3"><?php echo esc_html($field['description']); ?></p>
                                                <?php endif; ?>
                                                
                                                <div class="repeater-items space-y-3" id="repeater-<?php echo $key; ?>">
                                                    <!-- Items will be added here -->
                                                </div>
                                                
                                                <!-- Template -->
                                                <template id="template-<?php echo $key; ?>">
                                                    <div class="repeater-item bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700 relative group" data-index="__INDEX__">
                                                        <button type="button" onclick="removeRepeaterItem(this)" class="absolute top-2 right-2 p-1 text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                                            <span class="material-symbols-outlined text-lg">close</span>
                                                        </button>
                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pr-8">
                                                            <?php foreach ($field['fields'] as $subKey => $subField): ?>
                                                            <div class="<?php echo $subField['type'] === 'textarea' ? 'md:col-span-2' : ''; ?>">
                                                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                                                    <?php echo esc_html($subField['label']); ?>
                                                                </label>
                                                                <?php if ($subField['type'] === 'textarea'): ?>
                                                                <textarea 
                                                                    name="custom_fields[<?php echo $key; ?>][__INDEX__][<?php echo $subKey; ?>]"
                                                                    rows="2"
                                                                    class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
                                                                    placeholder="<?php echo esc_attr($subField['placeholder'] ?? ''); ?>"></textarea>
                                                                <?php elseif ($subField['type'] === 'image'): ?>
                                                                <div class="flex gap-2">
                                                                    <input type="text" 
                                                                        name="custom_fields[<?php echo $key; ?>][__INDEX__][<?php echo $subKey; ?>]"
                                                                        class="flex-1 px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                                                                        placeholder="Görsel URL">
                                                                    <button type="button" onclick="openMediaPickerForRepeater(this)" 
                                                                        class="px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                                        <span class="material-symbols-outlined text-sm">image</span>
                                                                    </button>
                                                                </div>
                                                                <?php else: ?>
                                                                <input type="text" 
                                                                    name="custom_fields[<?php echo $key; ?>][__INDEX__][<?php echo $subKey; ?>]"
                                                                    class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                                                                    placeholder="<?php echo esc_attr($subField['placeholder'] ?? ''); ?>">
                                                                <?php endif; ?>
                                                            </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                            
                                            <?php elseif ($field['type'] === 'text'): ?>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><?php echo esc_html($field['label']); ?></label>
                                                <input type="text" name="custom_fields[<?php echo $key; ?>]" value="<?php echo esc_attr($value); ?>"
                                                    placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                                                    class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                                <?php if (!empty($field['description'])): ?>
                                                <p class="text-gray-500 text-xs mt-1"><?php echo esc_html($field['description']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php elseif ($field['type'] === 'textarea'): ?>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><?php echo esc_html($field['label']); ?></label>
                                                <textarea name="custom_fields[<?php echo $key; ?>]" rows="3"
                                                    placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                                                    class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent resize-none"><?php echo esc_html($value); ?></textarea>
                                                <?php if (!empty($field['description'])): ?>
                                                <p class="text-gray-500 text-xs mt-1"><?php echo esc_html($field['description']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php elseif ($field['type'] === 'image'): ?>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><?php echo esc_html($field['label']); ?></label>
                                                <div class="space-y-2">
                                                    <input type="hidden" name="custom_fields[<?php echo $key; ?>]" id="field-<?php echo $key; ?>" value="">
                                                    <div class="flex gap-2">
                                                        <input type="text" id="url-<?php echo $key; ?>" value=""
                                                            class="flex-1 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                                            placeholder="Görsel URL'si..."
                                                            onchange="updateImageField('<?php echo $key; ?>', this.value)">
                                                        <button type="button" onclick="openMediaPickerForField('<?php echo $key; ?>')" 
                                                            class="px-4 py-2 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                                            <span class="material-symbols-outlined">image</span>
                                                        </button>
                                                    </div>
                                                </div>
                                                <?php if (!empty($field['description'])): ?>
                                                <p class="text-gray-500 text-xs mt-1"><?php echo esc_html($field['description']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php elseif ($field['type'] === 'select'): ?>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><?php echo esc_html($field['label']); ?></label>
                                                <select name="custom_fields[<?php echo $key; ?>]"
                                                    class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                                    <?php foreach ($field['options'] as $optVal => $optLabel): ?>
                                                    <option value="<?php echo esc_attr($optVal); ?>" <?php echo $value === $optVal ? 'selected' : ''; ?>><?php echo esc_html($optLabel); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <?php if (!empty($field['description'])): ?>
                                                <p class="text-gray-500 text-xs mt-1"><?php echo esc_html($field['description']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php elseif ($field['type'] === 'checkbox'): ?>
                                            <div class="flex items-center gap-3">
                                                <input type="checkbox" name="custom_fields[<?php echo $key; ?>]" id="field-<?php echo $key; ?>" value="1"
                                                    <?php echo (!empty($field['default'])) ? 'checked' : ''; ?>
                                                    class="w-5 h-5 text-primary border-gray-300 rounded focus:ring-primary">
                                                <label for="field-<?php echo $key; ?>" class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo esc_html($field['label']); ?></label>
                                            </div>
                                            
                                            <?php elseif ($field['type'] === 'number'): ?>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><?php echo esc_html($field['label']); ?></label>
                                                <input type="number" name="custom_fields[<?php echo $key; ?>]" value="<?php echo esc_attr($value); ?>"
                                                    class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                                <?php if (!empty($field['description'])): ?>
                                                <p class="text-gray-500 text-xs mt-1"><?php echo esc_html($field['description']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php $firstContent = false; endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- SEO -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary">search</span>
                                    SEO Ayarları
                                </h3>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">URL (Slug)</label>
                                        <input type="text" id="slug" name="slug"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                                            placeholder="sayfa-url (boş bırakılırsa otomatik oluşturulur)">
                                    </div>
                                    
                                    <div>
                                        <label for="meta_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Meta Başlık</label>
                                        <input type="text" id="meta_title" name="meta_title"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                                            placeholder="Arama motorlarında görünecek başlık">
                                    </div>
                                    
                                    <div>
                                        <label for="meta_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Meta Açıklama</label>
                                        <textarea id="meta_description" name="meta_description" rows="2"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
                                            placeholder="Arama motorlarında görünecek açıklama"></textarea>
                                    </div>
                                    
                                    <div>
                                        <label for="meta_keywords" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Anahtar Kelimeler</label>
                                        <input type="text" id="meta_keywords" name="meta_keywords"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                                            placeholder="kelime1, kelime2, kelime3">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sağ Kolon - Yayın Ayarları -->
                        <div class="space-y-6">
                            
                            <!-- Yayınla -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 sticky top-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary">publish</span>
                                    Yayın Ayarları
                                </h3>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Durum</label>
                                        <select name="status" class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="draft">Taslak</option>
                                            <option value="published">Yayında</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Görünürlük</label>
                                        <select name="visibility" class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="public">Herkese Açık</option>
                                            <option value="private">Gizli</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                    <button type="submit" class="w-full px-6 py-3 bg-primary text-white rounded-lg font-semibold hover:bg-primary/90 transition-colors flex items-center justify-center gap-2">
                                        <span class="material-symbols-outlined">add</span>
                                        Sayfa Oluştur
                                    </button>
                                    <a href="<?php echo admin_url('pages'); ?>" class="w-full px-6 py-3 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-center">
                                        İptal
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<?php include __DIR__ . '/../snippets/footer.php'; ?>

<script>
// Şablon-grup eşleştirmesi
const templateGroups = <?php echo json_encode($templateFieldGroups); ?>;

// Şablon seçimi
document.querySelectorAll('.template-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        const selectedTemplate = this.value;
        
        // Görsel güncelleme
        document.querySelectorAll('.template-option').forEach(opt => {
            const card = opt.querySelector('.template-card');
            const check = opt.querySelector('.template-check');
            const input = opt.querySelector('.template-radio');
            
            if (input.checked) {
                card.classList.remove('border-gray-200', 'dark:border-gray-700');
                card.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
                check.classList.remove('hidden');
                check.classList.add('flex');
            } else {
                card.classList.add('border-gray-200', 'dark:border-gray-700');
                card.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
                check.classList.add('hidden');
                check.classList.remove('flex');
            }
        });
        
        // Özel alanları göster/gizle
        const customFieldsContainer = document.getElementById('custom-fields-container');
        const contentEditor = document.querySelector('#quill-editor').closest('.bg-white');
        
        if (selectedTemplate === 'default') {
            // Varsayılan: sadece içerik editörü
            customFieldsContainer.style.display = 'none';
            contentEditor.style.display = 'block';
        } else {
            // Özel şablonlar: ilgili alanları göster
            customFieldsContainer.style.display = 'block';
            contentEditor.style.display = 'block';
            
            // İlgili sekmeleri göster
            const allowedGroups = Object.keys(templateGroups[selectedTemplate] || {});
            
            document.querySelectorAll('.tab-btn').forEach(btn => {
                const tabGroup = btn.dataset.tab;
                if (allowedGroups.includes(tabGroup)) {
                    btn.style.display = 'flex';
                } else {
                    btn.style.display = 'none';
                }
            });
            
            document.querySelectorAll('.tab-content').forEach(content => {
                const contentGroup = content.dataset.content;
                if (!allowedGroups.includes(contentGroup)) {
                    content.style.display = 'none';
                }
            });
            
            // İlk görünür sekmeyi aktif yap
            const firstVisibleTab = document.querySelector('.tab-btn[style*="flex"]');
            if (firstVisibleTab) {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                firstVisibleTab.classList.add('active');
                
                const firstTabGroup = firstVisibleTab.dataset.tab;
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                const firstContent = document.querySelector(`[data-content="${firstTabGroup}"]`);
                if (firstContent) {
                    firstContent.classList.add('active');
                    firstContent.style.display = 'block';
                }
            }
        }
    });
});

// Sayfa yüklendiğinde varsayılan şablonu tetikle
document.addEventListener('DOMContentLoaded', function() {
    const defaultRadio = document.querySelector('.template-radio:checked');
    if (defaultRadio) {
        defaultRadio.dispatchEvent(new Event('change'));
    }
});

// Tab Navigation
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const tab = this.dataset.tab;
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        const targetContent = document.querySelector(`[data-content="${tab}"]`);
        if (targetContent) {
            targetContent.classList.add('active');
        }
    });
});

// Quill Editor
const quill = new Quill('#quill-editor', {
    theme: 'snow',
    placeholder: 'İçeriğinizi buraya yazın...',
    modules: {
        toolbar: [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['link', 'image'],
            ['clean']
        ]
    }
});

quill.on('text-change', function() {
    document.getElementById('content').value = quill.root.innerHTML;
});

let htmlMode = false;
function toggleEditorMode() {
    htmlMode = !htmlMode;
    const container = document.getElementById('quill-editor');
    const textarea = document.getElementById('content');
    
    if (htmlMode) {
        textarea.classList.remove('hidden');
        container.classList.add('hidden');
        textarea.value = quill.root.innerHTML;
    } else {
        container.classList.remove('hidden');
        textarea.classList.add('hidden');
        quill.root.innerHTML = textarea.value;
    }
}

// Repeater Functions
const repeaterCounters = {};

function addRepeaterItem(fieldName) {
    const container = document.getElementById('repeater-' + fieldName);
    const template = document.getElementById('template-' + fieldName);
    
    if (!repeaterCounters[fieldName]) {
        repeaterCounters[fieldName] = container.querySelectorAll('.repeater-item').length;
    }
    
    const index = repeaterCounters[fieldName]++;
    const html = template.innerHTML.replace(/__INDEX__/g, index);
    
    const div = document.createElement('div');
    div.innerHTML = html;
    container.appendChild(div.firstElementChild);
}

function removeRepeaterItem(button) {
    const item = button.closest('.repeater-item');
    item.style.opacity = '0';
    item.style.transform = 'translateX(-20px)';
    setTimeout(() => item.remove(), 200);
}

// Media Picker başlat
document.addEventListener('DOMContentLoaded', function() {
    if (typeof MediaPicker !== 'undefined' && !window.mediaPicker) {
        window.mediaPicker = new MediaPicker();
    }
});

// Image Functions
function openMediaPickerForField(fieldKey) {
    if (!window.mediaPicker) {
        if (typeof MediaPicker !== 'undefined') {
            window.mediaPicker = new MediaPicker();
        } else {
            alert('Media Picker yüklenemedi');
            return;
        }
    }
    window.mediaPicker.open({
        onSelect: function(selected) {
            const url = selected.file_url || selected;
            document.getElementById('field-' + fieldKey).value = url;
            const urlInput = document.getElementById('url-' + fieldKey);
            if (urlInput) urlInput.value = url;
        }
    });
}

function openMediaPickerForRepeater(button) {
    const input = button.previousElementSibling;
    if (!window.mediaPicker) {
        if (typeof MediaPicker !== 'undefined') {
            window.mediaPicker = new MediaPicker();
        } else {
            alert('Media Picker yüklenemedi');
            return;
        }
    }
    window.mediaPicker.open({
        onSelect: function(selected) {
            const url = selected.file_url || selected;
            input.value = url;
        }
    });
}

function updateImageField(fieldKey, value) {
    document.getElementById('field-' + fieldKey).value = value;
}


// Form submission - convert repeater arrays to JSON
document.getElementById('page-form').addEventListener('submit', function(e) {
    const repeaterFields = ['service_features', 'process_steps', 'advantages', 'faqs', 'related_services', 'about_sections', 'team_members', 'stats'];
    
    repeaterFields.forEach(field => {
        const items = [];
        const container = document.getElementById('repeater-' + field);
        if (container) {
            container.querySelectorAll('.repeater-item').forEach((item, index) => {
                const itemData = {};
                item.querySelectorAll('input, textarea').forEach(input => {
                    const name = input.name;
                    const match = name.match(/\[([^\]]+)\]$/);
                    if (match) itemData[match[1]] = input.value;
                });
                if (Object.keys(itemData).length > 0) items.push(itemData);
            });
        }
        
        const oldInputs = this.querySelectorAll(`[name^="custom_fields[${field}]"]`);
        oldInputs.forEach(input => input.remove());
        
        const jsonInput = document.createElement('input');
        jsonInput.type = 'hidden';
        jsonInput.name = `custom_fields[${field}]`;
        jsonInput.value = JSON.stringify(items);
        this.appendChild(jsonInput);
    });
});
</script>
