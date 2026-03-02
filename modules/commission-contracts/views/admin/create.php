<?php
if (!isset($rootPath)) $rootPath = dirname(dirname(dirname(__DIR__)));
include $rootPath . '/app/views/admin/snippets/header.php';
require_once $rootPath . '/includes/functions.php';
$contract = $contract ?? null;
$items = $items ?? [];
$template = $template ?? null;
$templates = $templates ?? [];
$templateId = $templateId ?? null;
$formData = [];

$settings = get_option('site_settings', []);
$companyLogo = $settings['site_logo'] ?? '';
$companyName = $settings['company_name'] ?? '';
$companyPerson = $settings['company_person'] ?? '';
$companyPhone = $settings['company_phone'] ?? '';
$companyAddress = $settings['company_address'] ?? '';

$useTemplateForm = !empty($template);
$sections = [];
if ($useTemplateForm) {
    $hc = is_string($template['header_config'] ?? '') ? json_decode($template['header_config'], true) : ($template['header_config'] ?? []);
    $fc = is_string($template['footer_config'] ?? '') ? json_decode($template['footer_config'], true) : ($template['footer_config'] ?? []);
    $headerConfig = is_array($hc) ? $hc : ['left' => [], 'center' => 'logo', 'right' => []];
    $footerConfig = is_array($fc) ? $fc : ['description_label' => 'Açıklama', 'description_key' => 'footer_description', 'signature_label' => 'İmza', 'signature_count' => 1];
    $sections = isset($template['sections']) && is_array($template['sections']) ? $template['sections'] : [];
    $footerBlocks = isset($footerConfig['blocks']) && is_array($footerConfig['blocks']) ? $footerConfig['blocks'] : [];
}
?>

<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <?php $currentPage = 'commission-contracts'; include $rootPath . '/app/views/admin/snippets/sidebar.php'; ?>
        <div class="flex-1 flex flex-col lg:ml-64">
            <?php include $rootPath . '/app/views/admin/snippets/top-header.php'; ?>
            <main class="flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b] overflow-y-auto">
                <div class="layout-content-container commission-contracts-content flex flex-col w-full mx-auto max-w-7xl">

                    <header class="mb-6">
                        <a href="<?php echo admin_url('module/commission-contracts'); ?>" class="text-gray-500 dark:text-gray-400 hover:text-primary transition-colors inline-flex items-center gap-2 mb-2 text-sm sm:text-base">
                            <span class="material-symbols-outlined text-lg">arrow_back</span> Listeye dön
                        </a>
                        <h1 class="text-gray-900 dark:text-white text-2xl sm:text-3xl font-bold"><?php echo esc_html($title ?? 'Yeni Sözleşme'); ?></h1>
                        <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">1. Adım: Şablon seçin, sonra bilgileri doldurun</p>
                    </header>

                    <!-- Şablon seçimi – her zaman en üstte -->
                    <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 mb-6">
                        <label for="template_select_top" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Hangi şablonla oluşturacaksınız?</label>
                        <select id="template_select_top" class="w-full max-w-md px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                            <option value="" <?php echo empty($templateId) ? 'selected' : ''; ?>>Klasik form (şablonsuz)</option>
                            <?php foreach ($templates as $t): ?>
                                <option value="<?php echo (int)$t['id']; ?>" <?php echo (int)$t['id'] === (int)$templateId ? 'selected' : ''; ?>><?php echo esc_html($t['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($templates)): ?>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Şablon eklemek için <a href="<?php echo admin_url('module/commission-contracts/templates'); ?>" class="text-primary hover:underline">Şablonlar</a> sayfasını kullanın.</p>
                        <?php endif; ?>
                    </div>

<?php if ($useTemplateForm): ?>
                    <form action="<?php echo admin_url('module/commission-contracts/store'); ?>" method="POST" class="space-y-6" id="contract-form-template">
                        <input type="hidden" name="page" value="module/commission-contracts/store" />
                        <input type="hidden" name="template_id" value="<?php echo (int)$template['id']; ?>" />
                        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sözleşme takip bilgisi</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Bu bilgiler sadece panelde takip için kullanılır, PDF'te görünmez.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="contract_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sözleşme numarası</label>
                                    <input type="text" id="contract_number" name="contract_number" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" placeholder="Örn: 2026-001">
                                </div>
                                <div>
                                    <label for="contract_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sözleşme ismi</label>
                                    <input type="text" id="contract_name" name="contract_name" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" placeholder="Örn: Ahmet Yılmaz - Taşınmaz Gösterim">
                                </div>
                            </div>
                        </div>
                        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Başlık</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="space-y-2">
                                    <?php foreach (array_values($headerConfig['left'] ?? []) as $idx => $f): $k = $f['key'] ?? 'h_left_'.($idx+1); ?>
                                    <div>
                                        <label class="block text-xs text-gray-500 dark:text-white"><?php echo esc_html($f['label'] ?? $k); ?></label>
                                        <?php if (($f['type'] ?? 'text') === 'date'): ?>
                                            <input type="date" name="form_data[<?php echo esc_attr($k); ?>]" value="" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                        <?php elseif (($f['type'] ?? 'text') === 'number'): ?>
                                            <input type="number" name="form_data[<?php echo esc_attr($k); ?>]" value="" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                        <?php elseif (($f['type'] ?? 'text') === 'checkbox'): ?>
                                            <input type="hidden" name="form_data[<?php echo esc_attr($k); ?>]" value="0" class="form-data-checkbox-value" data-key="<?php echo esc_attr($k); ?>">
                                            <label class="inline-flex items-center gap-2 cursor-pointer"><input type="checkbox" class="form-data-checkbox-ui rounded" data-key="<?php echo esc_attr($k); ?>"></label>
                                        <?php else: ?>
                                            <input type="text" name="form_data[<?php echo esc_attr($k); ?>]" value="" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="flex items-center justify-center">
                                    <?php if (($headerConfig['center'] ?? '') === 'logo' && !empty($companyLogo)): ?>
                                        <img src="<?php echo esc_url($companyLogo); ?>" alt="" class="max-h-20 object-contain">
                                    <?php endif; ?>
                                </div>
                                <div class="space-y-2">
                                    <?php foreach (array_values($headerConfig['right'] ?? []) as $idx => $f): $k = $f['key'] ?? 'h_right_'.($idx+1); ?>
                                    <div>
                                        <label class="block text-xs text-gray-500 dark:text-white"><?php echo esc_html($f['label'] ?? $k); ?></label>
                                        <?php if (($f['type'] ?? 'text') === 'date'): ?>
                                            <input type="date" name="form_data[<?php echo esc_attr($k); ?>]" value="<?php echo esc_attr(date('Y-m-d')); ?>" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                        <?php elseif (($f['type'] ?? 'text') === 'number'): ?>
                                            <input type="number" name="form_data[<?php echo esc_attr($k); ?>]" value="" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                        <?php elseif (($f['type'] ?? 'text') === 'checkbox'): ?>
                                            <input type="hidden" name="form_data[<?php echo esc_attr($k); ?>]" value="0" class="form-data-checkbox-value" data-key="<?php echo esc_attr($k); ?>">
                                            <label class="inline-flex items-center gap-2 cursor-pointer"><input type="checkbox" class="form-data-checkbox-ui rounded" data-key="<?php echo esc_attr($k); ?>"></label>
                                        <?php else: ?>
                                            <input type="text" name="form_data[<?php echo esc_attr($k); ?>]" value="" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php foreach ($sections as $secIdx => $sec): $secTitle = $sec['title'] ?? ('Bölüm ' . ($secIdx + 1)); $themeColor = $sec['theme_color'] ?? '#4472C4'; ?>
                        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-white mb-4 px-4 py-2 rounded-lg" style="background-color: <?php echo esc_attr($themeColor); ?>"><?php echo esc_html($secTitle); ?></h2>
                            <?php if (($sec['type'] ?? '') === 'block'): $blocks = $sec['blocks'] ?? []; ?>
                            <div class="flex flex-wrap gap-6">
                                <?php foreach ($blocks as $blk): $fields = $blk['fields'] ?? []; ?>
                                <div class="flex-1 min-w-0 sm:min-w-[200px] space-y-2">
                                    <?php foreach ($fields as $f): $k = $f['key'] ?? ''; $label = $f['label'] ?? $k; $ft = $f['type'] ?? 'text'; ?>
                                    <div>
                                        <label class="block text-xs text-gray-500 dark:text-white"><?php echo esc_html($label); ?></label>
                                        <?php if ($ft === 'date'): ?>
                                            <input type="date" name="form_data[<?php echo esc_attr($k); ?>]" value="" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                        <?php elseif ($ft === 'number'): ?>
                                            <input type="number" name="form_data[<?php echo esc_attr($k); ?>]" value="" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                        <?php elseif ($ft === 'checkbox'): ?>
                                            <input type="hidden" name="form_data[<?php echo esc_attr($k); ?>]" value="0" class="form-data-checkbox-value" data-key="<?php echo esc_attr($k); ?>">
                                            <label class="inline-flex items-center gap-2 cursor-pointer"><input type="checkbox" class="form-data-checkbox-ui rounded" data-key="<?php echo esc_attr($k); ?>"></label>
                                        <?php elseif ($ft === 'email'): ?>
                                            <input type="email" name="form_data[<?php echo esc_attr($k); ?>]" value="" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                        <?php elseif ($ft === 'phone'): ?>
                                            <input type="tel" name="form_data[<?php echo esc_attr($k); ?>]" value="" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                        <?php elseif ($ft === 'textarea'): ?>
                                            <textarea name="form_data[<?php echo esc_attr($k); ?>]" rows="2" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm"></textarea>
                                        <?php else: ?>
                                            <input type="text" name="form_data[<?php echo esc_attr($k); ?>]" value="" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else:
                                $tableBlocks = $sec['table_blocks'] ?? [];
                                $legacyKeys = !empty($sec['legacy_keys']);
                                if (!empty($tableBlocks) && is_array($tableBlocks)):
                                    $maxRows = 0;
                                    foreach ($tableBlocks as $blk) { $maxRows = max($maxRows, (int)($blk['rows'] ?? 0)); }
                            ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full border border-gray-300 dark:border-gray-600" style="border-collapse: collapse;">
                                    <tbody>
                                        <?php for ($r = 0; $r < $maxRows; $r++): ?>
                                        <tr>
                                            <?php foreach ($tableBlocks as $blockIdx => $blk):
                                                $rows = (int)($blk['rows'] ?? 0);
                                                $cols = (int)($blk['cols'] ?? 0);
                                                $rowLabels = $blk['row_labels'] ?? [];
                                                $cells = $blk['cells'] ?? [];
                                                if ($r < $rows): ?>
                                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm font-medium text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-800"><?php echo esc_html(isset($rowLabels[$r]) ? $rowLabels[$r] : ('Satır ' . ($r + 1))); ?></td>
                                                <?php for ($c = 0; $c < $cols; $c++):
                                                    $key = ($legacyKeys && count($tableBlocks) === 1) ? ('cell_' . $r . '_' . $c) : ('sec_' . $secIdx . '_block_' . $blockIdx . '_cell_' . $r . '_' . $c);
                                                    $rowCells = $cells[$r] ?? array_fill(0, $cols, 'text');
                                                    $cellType = $rowCells[$c] ?? 'text';
                                                ?>
                                                <td class="border border-gray-300 dark:border-gray-600 p-1">
                                                    <?php if ($cellType === 'label'): ?>
                                                        <span class="text-sm text-gray-500 dark:text-white"><?php echo (int)$r + 1; ?></span>
                                                        <input type="hidden" name="form_data[<?php echo esc_attr($key); ?>]" value="<?php echo (int)$r + 1; ?>">
                                                    <?php elseif ($cellType === 'checkbox'): ?>
                                                        <input type="hidden" name="form_data[<?php echo esc_attr($key); ?>]" value="0" class="form-data-checkbox-value" data-key="<?php echo esc_attr($key); ?>">
                                                        <label class="inline-flex items-center gap-1 cursor-pointer"><input type="checkbox" class="form-data-checkbox-ui rounded" data-key="<?php echo esc_attr($key); ?>"></label>
                                                    <?php elseif ($cellType === 'number'): ?>
                                                        <input type="number" step="any" name="form_data[<?php echo esc_attr($key); ?>]" value="" class="w-full px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                                    <?php elseif ($cellType === 'date'): ?>
                                                        <input type="date" name="form_data[<?php echo esc_attr($key); ?>]" value="" class="w-full px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                                    <?php elseif ($cellType === 'signature'): ?>
                                                        <span class="text-xs text-gray-500 dark:text-white">İmza (sözleşme imzası)</span>
                                                        <input type="hidden" name="form_data[<?php echo esc_attr($key); ?>]" value="">
                                                    <?php else: ?>
                                                        <input type="text" name="form_data[<?php echo esc_attr($key); ?>]" value="" class="w-full px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                                    <?php endif; ?>
                                                </td>
                                                <?php endfor; ?>
                                            <?php else:
                                                $cols = (int)($blk['cols'] ?? 0); ?>
                                                <td class="border border-gray-300 dark:border-gray-600 px-3 py-2"></td>
                                                <?php for ($c = 0; $c < $cols; $c++) echo '<td class="border border-gray-300 dark:border-gray-600 p-1"></td>'; ?>
                                            <?php endif; ?>
                                            <?php endforeach; ?>
                                        </tr>
                                        <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else:
                                $rows = (int)($sec['rows'] ?? 3); $cols = (int)($sec['cols'] ?? 3); $headers = $sec['headers'] ?? []; $cells = $sec['cells'] ?? []; $rowLabels = $sec['row_labels'] ?? []; ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full border border-gray-300 dark:border-gray-600" style="border-collapse: collapse;">
                                    <thead>
                                        <tr>
                                            <?php if (!empty($rowLabels)): ?><th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left text-sm font-medium text-gray-900 dark:text-white" style="background-color: <?php echo esc_attr($themeColor); ?>20;"></th><?php endif; ?>
                                            <?php for ($c = 0; $c < $cols; $c++): $h = isset($headers[$c]) ? $headers[$c] : ('Sütun ' . ($c + 1)); ?>
                                                <th class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-left text-sm font-medium text-gray-900 dark:text-white" style="background-color: <?php echo esc_attr($themeColor); ?>20;"><?php echo esc_html($h); ?></th>
                                            <?php endfor; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php for ($r = 0; $r < $rows; $r++): $rowCells = $cells[$r] ?? array_fill(0, $cols, 'text'); ?>
                                        <tr>
                                            <?php if (!empty($rowLabels)): ?><td class="border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm font-medium text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-800"><?php echo esc_html(isset($rowLabels[$r]) ? $rowLabels[$r] : ('Satır ' . ($r + 1))); ?></td><?php endif; ?>
                                            <?php for ($c = 0; $c < $cols; $c++): $cellType = $rowCells[$c] ?? 'text'; $key = $legacyKeys ? ('cell_' . $r . '_' . $c) : ('sec_' . $secIdx . '_cell_' . $r . '_' . $c); ?>
                                                <td class="border border-gray-300 dark:border-gray-600 p-1">
                                                    <?php if ($cellType === 'label'): ?>
                                                        <span class="text-sm text-gray-500 dark:text-white"><?php echo (int)$r + 1; ?></span>
                                                        <input type="hidden" name="form_data[<?php echo esc_attr($key); ?>]" value="<?php echo (int)$r + 1; ?>">
                                                    <?php elseif ($cellType === 'checkbox'): ?>
                                                        <input type="hidden" name="form_data[<?php echo esc_attr($key); ?>]" value="0" class="form-data-checkbox-value" data-key="<?php echo esc_attr($key); ?>">
                                                        <label class="inline-flex items-center gap-1 cursor-pointer"><input type="checkbox" class="form-data-checkbox-ui rounded" data-key="<?php echo esc_attr($key); ?>"></label>
                                                    <?php elseif ($cellType === 'number'): ?>
                                                        <input type="number" step="any" name="form_data[<?php echo esc_attr($key); ?>]" value="" class="w-full px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                                    <?php elseif ($cellType === 'date'): ?>
                                                        <input type="date" name="form_data[<?php echo esc_attr($key); ?>]" value="" class="w-full px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                                    <?php elseif ($cellType === 'signature'): ?>
                                                        <span class="text-xs text-gray-500 dark:text-white">İmza (sözleşme imzası)</span>
                                                        <input type="hidden" name="form_data[<?php echo esc_attr($key); ?>]" value="">
                                                    <?php else: ?>
                                                        <input type="text" name="form_data[<?php echo esc_attr($key); ?>]" value="" class="w-full px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                                    <?php endif; ?>
                                                </td>
                                            <?php endfor; ?>
                                        </tr>
                                        <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                        <?php if (!empty($footerBlocks)): ?>
                        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Alt Bölüm</h2>
                            <div class="flex flex-wrap gap-6">
                                <?php foreach ($footerBlocks as $fblk): $fields = $fblk['fields'] ?? []; ?>
                                <div class="flex-1 min-w-0 sm:min-w-[200px] space-y-2">
                                    <?php foreach ($fields as $f): $k = $f['key'] ?? ''; $label = $f['label'] ?? $k; $ft = $f['type'] ?? 'text'; ?>
                                    <div>
                                        <label class="block text-xs text-gray-500 dark:text-white"><?php echo esc_html($label); ?></label>
                                        <?php if ($ft === 'date'): ?>
                                            <input type="date" name="form_data[<?php echo esc_attr($k); ?>]" value="" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                        <?php elseif ($ft === 'number'): ?>
                                            <input type="number" name="form_data[<?php echo esc_attr($k); ?>]" value="" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                        <?php elseif ($ft === 'checkbox'): ?>
                                            <input type="hidden" name="form_data[<?php echo esc_attr($k); ?>]" value="0" class="form-data-checkbox-value" data-key="<?php echo esc_attr($k); ?>">
                                            <label class="inline-flex items-center gap-2 cursor-pointer"><input type="checkbox" class="form-data-checkbox-ui rounded" data-key="<?php echo esc_attr($k); ?>"></label>
                                        <?php elseif ($ft === 'email'): ?>
                                            <input type="email" name="form_data[<?php echo esc_attr($k); ?>]" value="" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                        <?php elseif ($ft === 'phone'): ?>
                                            <input type="tel" name="form_data[<?php echo esc_attr($k); ?>]" value="" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                        <?php elseif ($ft === 'textarea'): ?>
                                            <textarea name="form_data[<?php echo esc_attr($k); ?>]" rows="2" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm"></textarea>
                                        <?php elseif ($ft === 'signature'): ?>
                                            <span class="text-xs text-gray-500 dark:text-white">İmza (sözleşme imzası)</span>
                                            <input type="hidden" name="form_data[<?php echo esc_attr($k); ?>]" value="">
                                        <?php else: ?>
                                            <input type="text" name="form_data[<?php echo esc_attr($k); ?>]" value="" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                            <textarea name="form_data[<?php echo esc_attr($footerConfig['description_key'] ?? 'footer_description'); ?>]" rows="4" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" placeholder="<?php echo esc_attr($footerConfig['description_label'] ?? 'Açıklama'); ?>"></textarea>
                        </div>
                        <?php endif; ?>
                        <div class="flex flex-wrap gap-2 sm:gap-3">
                            <button type="submit" class="px-4 sm:px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 font-medium w-full sm:w-auto">Kaydet (Taslak)</button>
                            <a href="<?php echo admin_url('module/commission-contracts'); ?>" class="px-4 sm:px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 text-center w-full sm:w-auto">İptal</a>
                        </div>
                    </form>
<?php else: ?>
                    <form action="<?php echo admin_url('module/commission-contracts/store'); ?>" method="POST" class="space-y-6" id="contract-form">
                        <input type="hidden" name="page" value="module/commission-contracts/store" />

                        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sözleşme takip bilgisi</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Bu bilgiler sadece panelde takip için kullanılır, PDF'te görünmez.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="contract_number_legal" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sözleşme numarası</label>
                                    <input type="text" id="contract_number_legal" name="contract_number" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" placeholder="Örn: 2026-001">
                                </div>
                                <div>
                                    <label for="contract_name_legal" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sözleşme ismi</label>
                                    <input type="text" id="contract_name_legal" name="contract_name" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" placeholder="Örn: Ahmet Yılmaz - Taşınmaz Gösterim">
                                </div>
                            </div>
                        </div>

                        <!-- Müşteri Bilgileri -->
                        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Müşteri Bilgileri</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Müşteri tipi</label>
                                    <select name="client_type" id="client_type" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                        <option value="individual">Gerçek Kişi</option>
                                        <option value="legal">Tüzel Kişi</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="client_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ad / Unvan <span class="text-red-500">*</span></label>
                                    <input type="text" id="client_name" name="client_name" required
                                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                                        placeholder="Ad Soyad veya Şirket Unvanı">
                                </div>
                                <div class="client-legal-only hidden">
                                    <label for="client_tax_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Vergi No</label>
                                    <input type="text" id="client_tax_number" name="client_tax_number"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                </div>
                                <div class="client-legal-only hidden">
                                    <label for="client_tax_office" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Vergi Dairesi</label>
                                    <input type="text" id="client_tax_office" name="client_tax_office"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                </div>
                                <div class="md:col-span-2">
                                    <label for="client_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Adres</label>
                                    <textarea id="client_address" name="client_address" rows="2"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"></textarea>
                                </div>
                                <div>
                                    <label for="client_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Telefon</label>
                                    <input type="text" id="client_phone" name="client_phone"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label for="client_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">E-posta</label>
                                    <input type="email" id="client_email" name="client_email"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                </div>
                            </div>
                        </div>

                        <!-- Firma Bilgileri -->
                        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Emlak İşletmesi / Sözleşmeli İşletme</h2>
                            <div class="space-y-4">
                                <?php if (!empty($companyLogo)): ?>
                                    <div class="flex justify-center mb-6">
                                        <img src="<?php echo esc_url($companyLogo); ?>" alt="<?php echo esc_attr($companyName); ?>" class="max-w-xs max-h-32 object-contain">
                                    </div>
                                <?php endif; ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="company_person_field" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Yetkili Kişi</label>
                                        <input type="text" id="company_person_field" name="company_person_field" 
                                            value="<?php echo esc_attr($companyPerson); ?>"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                    </div>
                                    <div>
                                        <label for="company_phone_field" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Telefon</label>
                                        <input type="text" id="company_phone_field" name="company_phone_field" 
                                            value="<?php echo esc_attr($companyPhone); ?>"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                    </div>
                                    <div>
                                        <label for="company_name_field" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ünvanı</label>
                                        <input type="text" id="company_name_field" name="company_name_field" 
                                            value="<?php echo esc_attr($companyName); ?>"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                    </div>
                                    <div>
                                        <label for="company_address_field" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Adres</label>
                                        <input type="text" id="company_address_field" name="company_address_field" 
                                            value="<?php echo esc_attr($companyAddress); ?>"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Taşınmazlar -->
                        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 mb-4">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Taşınmazlar</h2>
                                <button type="button" id="add-item" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 text-sm font-medium flex items-center justify-center gap-2 w-full sm:w-auto">
                                    <span class="material-symbols-outlined text-lg">add</span> Taşınmaz Ekle
                                </button>
                            </div>
                            <div id="items-container" class="space-y-4">
                                <div class="item-row border border-gray-200 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-800/50" data-index="0">
                                    <div class="flex justify-between items-start mb-3">
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Taşınmaz 1</span>
                                        <button type="button" class="remove-item text-red-600 hover:text-red-800 text-sm hidden">Kaldır</button>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <div class="sm:col-span-2">
                                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Açıklama</label>
                                            <textarea name="item_description[]" rows="2" class="item-desc w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm resize-y min-h-[4.5rem]" placeholder="Taşınmaz açıklaması"></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Satılık / Kiralık</label>
                                            <select name="item_listing_type[]" class="item-listing-type w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                                <option value="sale">Satılık</option>
                                                <option value="rent">Kiralık</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Fiyat / Aylık Kira (₺)</label>
                                            <input type="number" name="item_price[]" step="0.01" min="0" class="item-price w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" placeholder="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2 sm:gap-3">
                            <button type="submit" class="px-4 sm:px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 font-medium w-full sm:w-auto">Kaydet (Taslak)</button>
                            <a href="<?php echo admin_url('module/commission-contracts'); ?>" class="px-4 sm:px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 text-center w-full sm:w-auto">İptal</a>
                        </div>
                    </form>
<?php endif; ?>

                </div>
            </main>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function syncCheckboxToHidden(ui) {
        var key = ui.getAttribute('data-key');
        if (!key) return;
        var hidden = document.querySelector('.form-data-checkbox-value[data-key="' + key + '"]');
        if (hidden) hidden.value = ui.checked ? '1' : '0';
    }
    // Şablon formunda checkbox: sadece hidden gönderiliyor (form_data[key]=0|1), UI checkbox tıklanınca hidden güncellenir
    document.querySelectorAll('.form-data-checkbox-ui').forEach(function(ui) {
        ui.addEventListener('change', function() { syncCheckboxToHidden(ui); });
        // Sayfa yüklendiğinde de sync yap (checked attribute varsa)
        syncCheckboxToHidden(ui);
    });
    // Gönderimden hemen önce tüm checkbox durumlarını hidden alanlara yaz (POST'ta mutlaka doğru değer gitsin)
    var formTemplate = document.getElementById('contract-form-template');
    if (formTemplate) {
        formTemplate.addEventListener('submit', function(e) {
            document.querySelectorAll('.form-data-checkbox-ui').forEach(syncCheckboxToHidden);
        });
    }

    var container = document.getElementById('items-container');
    if (!container) return;

    var clientType = document.getElementById('client_type');
    var legalOnly = document.querySelectorAll('.client-legal-only');
    function toggleLegal() {
        var show = clientType && clientType.value === 'legal';
        legalOnly.forEach(function(el) { el.classList.toggle('hidden', !show); });
    }
    if (clientType) {
        clientType.addEventListener('change', toggleLegal);
        toggleLegal();
    }

    var addBtn = document.getElementById('add-item');
    var firstRow = container.querySelector('.item-row');
    var indexCounter = 1;

    function updateRowLabels() {
        container.querySelectorAll('.item-row').forEach(function(row, i) {
            row.querySelector('span.text-sm.font-medium').textContent = 'Taşınmaz ' + (i + 1);
            row.querySelector('.remove-item').classList.toggle('hidden', container.querySelectorAll('.item-row').length <= 1);
        });
    }

    function cloneRow() {
        var clone = firstRow.cloneNode(true);
        clone.dataset.index = indexCounter++;
        var desc = clone.querySelector('.item-desc');
        var price = clone.querySelector('.item-price');
        if (desc) desc.value = '';
        if (price) price.value = '';
        clone.querySelector('.remove-item').classList.remove('hidden');
        clone.querySelector('.remove-item').onclick = function() {
            if (container.querySelectorAll('.item-row').length <= 1) return;
            clone.remove();
            updateRowLabels();
        };
        container.appendChild(clone);
        updateRowLabels();
    }

    addBtn.addEventListener('click', cloneRow);
    firstRow.querySelector('.remove-item').onclick = function() {
        if (container.querySelectorAll('.item-row').length <= 1) return;
        firstRow.remove();
        updateRowLabels();
    };
});
</script>
<script>
(function() {
    var sel = document.getElementById('template_select_top');
    if (sel) {
        sel.addEventListener('change', function() {
            var base = '<?php echo admin_url('module/commission-contracts/create'); ?>';
            var val = this.value;
            var sep = base.indexOf('?') >= 0 ? '&' : '?';
            window.location.href = val ? base + sep + 'template_id=' + encodeURIComponent(val) : base;
        });
    }
})();
</script>
<?php include $rootPath . '/app/views/admin/snippets/footer.php'; ?>
