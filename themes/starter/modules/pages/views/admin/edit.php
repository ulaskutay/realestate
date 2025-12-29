<?php 
// Admin snippet'lerini mutlak yol ile yükle
$rootPath = $_SERVER['DOCUMENT_ROOT'];
include $rootPath . '/app/views/admin/snippets/header.php'; 
?>
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

/* Repeater Styles */
.repeater-item {
    transition: all 0.2s ease;
}
.repeater-item:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
.repeater-item.dragging {
    opacity: 0.5;
}

/* Tab Styles */
.tab-btn {
    transition: all 0.2s ease;
}
.tab-btn.active {
    color: var(--color-primary, #2563eb);
    border-color: var(--color-primary, #2563eb);
    background: rgba(37, 99, 235, 0.05);
}
.tab-content {
    display: none;
}
.tab-content.active {
    display: block;
}
</style>
<?php
$customFields = $customFields ?? [];
$customFieldDefinitions = $customFieldDefinitions ?? Page::getCustomFieldDefinitions();
$versions = $versions ?? [];

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
    ],
    'contact' => [
        'contact_info' => ['label' => 'İletişim Bilgileri', 'icon' => 'contact_mail'],
        'map' => ['label' => 'Harita', 'icon' => 'map'],
        'form' => ['label' => 'Form Ayarları', 'icon' => 'dynamic_form'],
        'social' => ['label' => 'Sosyal Medya', 'icon' => 'share']
    ]
];

// Mevcut sayfa şablonu
$currentTemplate = $customFields['page_template'] ?? 'default';

// Tüm grupları birleştir (tab rendering için) - tüm şablonların alanlarını göstermek için
$fieldGroups = [];
foreach ($templateFieldGroups as $templateKey => $groups) {
    foreach ($groups as $groupKey => $groupData) {
        if (!isset($fieldGroups[$groupKey])) {
            $fieldGroups[$groupKey] = $groupData;
        }
    }
}

// Seçili şablona göre aktif grupları belirle
$activeGroups = $templateFieldGroups[$currentTemplate] ?? [];
?>
<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <?php 
        $currentPage = 'pages';
        include $rootPath . '/app/views/admin/snippets/sidebar.php'; 
        ?>

        <main class="main-content-with-sidebar flex-1 p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-[#15202b]">
            <div class="layout-content-container flex flex-col w-full mx-auto max-w-6xl">
                
                <!-- Header -->
                <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div class="flex items-center gap-4">
                        <a href="<?php echo admin_url('module/pages'); ?>" class="p-2 text-gray-500 hover:text-primary hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-all">
                            <span class="material-symbols-outlined">arrow_back</span>
                        </a>
                        <div>
                            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white"><?php echo esc_html($page['title']); ?></h1>
                            <p class="text-sm text-gray-500 flex items-center gap-2 mt-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?php echo $page['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                    <?php echo $page['status'] === 'published' ? 'Yayında' : 'Taslak'; ?>
                                </span>
                                <span>v<?php echo $page['version'] ?? 1; ?></span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <?php if ($page['status'] === 'published'): ?>
                        <a href="/<?php echo esc_attr($page['slug']); ?>" target="_blank" class="flex items-center gap-2 px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <span class="material-symbols-outlined text-lg">open_in_new</span>
                            <span class="hidden sm:inline">Görüntüle</span>
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo admin_url('module/pages/versions/' . $page['id']); ?>" class="flex items-center gap-2 px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <span class="material-symbols-outlined text-lg">history</span>
                            <span class="hidden sm:inline">Geçmiş</span>
                        </a>
                    </div>
                </header>

                <?php if (isset($_SESSION['page_message'])): ?>
                <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400">
                    <p class="text-sm font-medium flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">check_circle</span>
                        <?php echo esc_html($_SESSION['page_message']); ?>
                    </p>
                </div>
                <?php unset($_SESSION['page_message']); endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 dark:bg-red-900/20 dark:border-red-800 dark:text-red-400">
                    <p class="text-sm font-medium flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">error</span>
                        <?php echo esc_html($_SESSION['error_message']); ?>
                    </p>
                </div>
                <?php unset($_SESSION['error_message']); endif; ?>

                <form action="<?php echo admin_url('module/pages/update/' . $page['id']); ?>" method="POST" id="page-form">
                    
                    <!-- Şablon Bilgisi -->
                    <!-- Hidden input for page_template -->
                    <input type="hidden" name="custom_fields[page_template]" value="<?php echo esc_attr($currentTemplate); ?>">
                    
                    <?php if ($currentTemplate !== 'default'): ?>
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 mb-6 flex items-center gap-3">
                        <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-2xl">
                            <?php echo $currentTemplate === 'service-detail' ? 'construction' : 'groups'; ?>
                        </span>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                Şablon: <span class="text-blue-600 dark:text-blue-400">
                                    <?php 
                                    echo $currentTemplate === 'service-detail' ? 'Hizmet Detay Sayfası' : 'Hakkımızda Sayfası';
                                    ?>
                                </span>
                            </p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Bu sayfa özel şablon alanlarını kullanıyor</p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
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
                                            value="<?php echo esc_attr($page['title']); ?>"
                                            class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent text-lg font-medium"
                                            placeholder="Sayfa başlığını girin...">
                                    </div>
                                    
                                    <div>
                                        <label for="excerpt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Özet (Hero Açıklaması)</label>
                                        <textarea id="excerpt" name="excerpt" rows="3"
                                            class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
                                            placeholder="Sayfanın kısa açıklaması... (Hero bölümünde görünür)"><?php echo esc_html($page['excerpt']); ?></textarea>
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
                                <div id="quill-editor"><?php echo $page['content']; ?></div>
                                <textarea id="content" name="content" class="hidden"><?php echo esc_html($page['content']); ?></textarea>
                            </div>
                            
                            <!-- Özel Alanlar (Tab Yapısı) -->
                            <?php if (!empty($activeGroups)): ?>
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                <div class="border-b border-gray-200 dark:border-gray-700">
                                    <nav class="flex overflow-x-auto" id="custom-fields-tabs">
                                        <?php $firstTab = true; foreach ($activeGroups as $groupKey => $group): ?>
                                        <button type="button" 
                                            class="tab-btn flex items-center gap-2 px-4 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 border-b-2 border-transparent whitespace-nowrap <?php echo $firstTab ? 'active' : ''; ?>"
                                            data-tab="<?php echo $groupKey; ?>">
                                            <span class="material-symbols-outlined text-lg"><?php echo $group['icon']; ?></span>
                                            <span class="hidden sm:inline"><?php echo $group['label']; ?></span>
                                        </button>
                                        <?php $firstTab = false; endforeach; ?>
                                    </nav>
                                </div>
                                
                                <div class="p-6">
                                    <?php 
                                    // DEBUG: Form verilerini kontrol et
                                    if (isset($forms)) {
                                        // echo "<!-- DEBUG: Forms count: " . count($forms) . " -->";
                                    }
                                    if (isset($customFieldDefinitions['form_id'])) {
                                        // echo "<!-- DEBUG: form_id options: " . print_r($customFieldDefinitions['form_id']['options'] ?? 'YOK', true) . " -->";
                                    }
                                    ?>
                                    <?php $firstContent = true; foreach ($activeGroups as $groupKey => $group): ?>
                                    <div class="tab-content <?php echo $firstContent ? 'active' : ''; ?>" data-content="<?php echo $groupKey; ?>">
                                        <div class="space-y-6">
                                            <?php 
                                            foreach ($customFieldDefinitions as $key => $field): 
                                                // Skip hidden fields and fields without group
                                                if (!isset($field['group']) || $field['group'] === 'hidden') continue;
                                                if ($field['group'] !== $groupKey) continue;
                                                // form_id için varsayılan değer kullanma
                                                if ($key === 'form_id') {
                                                    $value = $customFields[$key] ?? '';
                                                } else {
                                                    $value = $customFields[$key] ?? $field['default'] ?? '';
                                                }
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
                                                    <?php 
                                                    $items = !empty($value) ? json_decode($value, true) : [];
                                                    if (!is_array($items)) $items = [];
                                                    foreach ($items as $itemIndex => $item): 
                                                    ?>
                                                    <div class="repeater-item bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700 relative group" data-index="<?php echo $itemIndex; ?>">
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
                                                                    name="custom_fields[<?php echo $key; ?>][<?php echo $itemIndex; ?>][<?php echo $subKey; ?>]"
                                                                    rows="2"
                                                                    class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
                                                                    placeholder="<?php echo esc_attr($subField['placeholder'] ?? ''); ?>"><?php echo esc_html($item[$subKey] ?? ''); ?></textarea>
                                                                <?php elseif ($subField['type'] === 'image'): ?>
                                                                <div class="flex gap-2">
                                                                    <input type="text" 
                                                                        name="custom_fields[<?php echo $key; ?>][<?php echo $itemIndex; ?>][<?php echo $subKey; ?>]"
                                                                        value="<?php echo esc_attr($item[$subKey] ?? ''); ?>"
                                                                        class="flex-1 px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                                                                        placeholder="Görsel URL">
                                                                    <button type="button" onclick="openMediaPickerForRepeater(this)" 
                                                                        class="px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                                        <span class="material-symbols-outlined text-sm">image</span>
                                                                    </button>
                                                                </div>
                                                                <?php else: ?>
                                                                <input type="text" 
                                                                    name="custom_fields[<?php echo $key; ?>][<?php echo $itemIndex; ?>][<?php echo $subKey; ?>]"
                                                                    value="<?php echo esc_attr($item[$subKey] ?? ''); ?>"
                                                                    class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                                                                    placeholder="<?php echo esc_attr($subField['placeholder'] ?? ''); ?>">
                                                                <?php endif; ?>
                                                            </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                    <?php endforeach; ?>
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
                                                    <?php if (!empty($value)): ?>
                                                    <div class="relative w-full h-40 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-900">
                                                        <img src="<?php echo esc_url($value); ?>" alt="" class="w-full h-full object-cover" id="preview-<?php echo $key; ?>">
                                                        <button type="button" onclick="clearImageField('<?php echo $key; ?>')" class="absolute top-2 right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-600">
                                                            <span class="material-symbols-outlined text-sm">close</span>
                                                        </button>
                                                    </div>
                                                    <?php endif; ?>
                                                    <input type="hidden" name="custom_fields[<?php echo $key; ?>]" id="field-<?php echo $key; ?>" value="<?php echo esc_attr($value); ?>">
                                                    <div class="flex gap-2">
                                                        <input type="text" id="url-<?php echo $key; ?>" value="<?php echo esc_attr($value); ?>"
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
                                                    <?php if (!empty($field['options']) && is_array($field['options'])): ?>
                                                        <?php foreach ($field['options'] as $optVal => $optLabel): ?>
                                                        <option value="<?php echo esc_attr($optVal); ?>" <?php echo (!empty($value) && (string)$value === (string)$optVal) ? 'selected' : ''; ?>><?php echo esc_html($optLabel); ?></option>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <option value="">Form bulunamadı</option>
                                                    <?php endif; ?>
                                                </select>
                                                <?php if (!empty($field['description'])): ?>
                                                <p class="text-gray-500 text-xs mt-1"><?php echo esc_html($field['description']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php elseif ($field['type'] === 'checkbox'): ?>
                                            <div class="flex items-center gap-3">
                                                <input type="checkbox" name="custom_fields[<?php echo $key; ?>]" id="field-<?php echo $key; ?>" value="1"
                                                    <?php echo ($value === '1' || $value === 1 || (!empty($field['default']) && empty($value))) ? 'checked' : ''; ?>
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
                            <?php endif; ?>
                            
                            <!-- SEO -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary">search</span>
                                    SEO Ayarları
                                </h3>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">URL (Slug)</label>
                                        <input type="text" id="slug" name="slug" value="<?php echo esc_attr($page['slug']); ?>"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                                            placeholder="sayfa-url">
                                    </div>
                                    
                                    <div>
                                        <label for="meta_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Meta Başlık</label>
                                        <input type="text" id="meta_title" name="meta_title" value="<?php echo esc_attr($page['meta_title']); ?>"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                                            placeholder="Arama motorlarında görünecek başlık">
                                    </div>
                                    
                                    <div>
                                        <label for="meta_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Meta Açıklama</label>
                                        <textarea id="meta_description" name="meta_description" rows="2"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
                                            placeholder="Arama motorlarında görünecek açıklama"><?php echo esc_html($page['meta_description']); ?></textarea>
                                    </div>
                                    
                                    <div>
                                        <label for="meta_keywords" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Anahtar Kelimeler</label>
                                        <input type="text" id="meta_keywords" name="meta_keywords" value="<?php echo esc_attr($page['meta_keywords']); ?>"
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
                                            <option value="draft" <?php echo $page['status'] === 'draft' ? 'selected' : ''; ?>>Taslak</option>
                                            <option value="published" <?php echo $page['status'] === 'published' ? 'selected' : ''; ?>>Yayında</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Görünürlük</label>
                                        <select name="visibility" class="w-full px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="public" <?php echo $page['visibility'] === 'public' ? 'selected' : ''; ?>>Herkese Açık</option>
                                            <option value="private" <?php echo $page['visibility'] === 'private' ? 'selected' : ''; ?>>Gizli</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                    <button type="submit" class="w-full px-6 py-3 bg-primary text-white rounded-lg font-semibold hover:bg-primary/90 transition-colors flex items-center justify-center gap-2">
                                        <span class="material-symbols-outlined">save</span>
                                        Güncelle
                                    </button>
                                    <a href="<?php echo admin_url('module/pages'); ?>" class="w-full px-6 py-3 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-center">
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

<?php include $rootPath . '/app/views/admin/snippets/footer.php'; ?>

<script>
// Tab Navigation
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const tab = this.dataset.tab;
        
        // Update buttons
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Update content
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        document.querySelector(`[data-content="${tab}"]`).classList.add('active');
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

// Image Field Functions
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
            
            const preview = document.getElementById('preview-' + fieldKey);
            if (preview) {
                preview.src = url;
            }
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
    const preview = document.getElementById('preview-' + fieldKey);
    if (preview) {
        preview.src = value;
    }
}

function clearImageField(fieldKey) {
    document.getElementById('field-' + fieldKey).value = '';
    const urlInput = document.getElementById('url-' + fieldKey);
    if (urlInput) urlInput.value = '';
    const previewContainer = document.getElementById('preview-' + fieldKey)?.closest('.relative');
    if (previewContainer) {
        previewContainer.remove();
    }
}


// Form submission - convert repeater arrays to JSON
document.getElementById('page-form').addEventListener('submit', function(e) {
    // Convert repeater fields to JSON
    const formData = new FormData(this);
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
                    if (match) {
                        itemData[match[1]] = input.value;
                    }
                });
                if (Object.keys(itemData).length > 0) {
                    items.push(itemData);
                }
            });
        }
        
        // Remove old inputs
        const oldInputs = this.querySelectorAll(`[name^="custom_fields[${field}]"]`);
        oldInputs.forEach(input => input.remove());
        
        // Add JSON input
        const jsonInput = document.createElement('input');
        jsonInput.type = 'hidden';
        jsonInput.name = `custom_fields[${field}]`;
        jsonInput.value = JSON.stringify(items);
        this.appendChild(jsonInput);
    });
});
</script>
