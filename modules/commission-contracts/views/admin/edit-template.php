<?php
if (!isset($rootPath)) $rootPath = dirname(dirname(dirname(__DIR__)));
include $rootPath . '/app/views/admin/snippets/header.php';
require_once $rootPath . '/includes/functions.php';
$contract = $contract ?? null;
$template = $template ?? null;
$formData = $formData ?? [];
$isCheckboxChecked = function($v) {
    if ($v === '1' || $v === 1 || $v === true) return true;
    if (is_string($v) && (string)$v === '1') return true;
    return false;
};
$contractStatus = strtolower(trim((string)($contract['status'] ?? '')));
$isDraft = ($contractStatus !== 'signed');
$settings = get_option('site_settings', []);
$companyLogo = $settings['site_logo'] ?? '';
$headerConfig = is_string($template['header_config'] ?? '') ? json_decode($template['header_config'], true) : ($template['header_config'] ?? []);
$footerConfig = is_string($template['footer_config'] ?? '') ? json_decode($template['footer_config'], true) : ($template['footer_config'] ?? []);
if (!is_array($headerConfig)) $headerConfig = ['left' => [], 'center' => 'logo', 'right' => []];
if (!is_array($footerConfig)) $footerConfig = ['description_label' => 'Açıklama', 'description_key' => 'footer_description', 'signature_label' => 'İmza', 'signature_count' => 1];
$sections = isset($template['sections']) && is_array($template['sections']) ? $template['sections'] : [];
$footerBlocks = isset($footerConfig['blocks']) && is_array($footerConfig['blocks']) ? $footerConfig['blocks'] : [];
$descKey = $footerConfig['description_key'] ?? 'footer_description';
$readonlyAttr = $isDraft ? '' : 'readonly';
$disabledAttr = $isDraft ? '' : 'disabled';
?>
<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <?php $currentPage = 'commission-contracts'; include $rootPath . '/app/views/admin/snippets/sidebar.php'; ?>
        <div class="flex-1 flex flex-col lg:ml-64">
            <?php include $rootPath . '/app/views/admin/snippets/top-header.php'; ?>
            <main class="flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b] overflow-y-auto">
                <div class="layout-content-container commission-contracts-content flex flex-col w-full mx-auto max-w-7xl">

                    <?php if (!empty($message)): ?>
                        <div class="mb-4 p-4 rounded-lg <?php echo ($messageType ?? '') === 'success' ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-300'; ?>">
                            <?php echo esc_html($message); ?>
                        </div>
                    <?php endif; ?>

                    <header class="mb-6 flex flex-col sm:flex-row sm:flex-wrap sm:justify-between sm:items-start gap-4">
                        <div class="min-w-0">
                            <a href="<?php echo admin_url('module/commission-contracts'); ?>" class="text-gray-500 dark:text-gray-400 hover:text-primary transition-colors inline-flex items-center gap-2 mb-2 text-sm sm:text-base">
                                <span class="material-symbols-outlined text-lg">arrow_back</span> Listeye dön
                            </a>
                            <h1 class="text-gray-900 dark:text-white text-2xl sm:text-3xl font-bold">Sözleşme #<?php echo (int)$contract['id']; ?></h1>
                            <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm"><?php echo $isDraft ? 'Düzenleyebilir veya imzalayabilirsiniz.' : 'İmzalanmış sözleşme.'; ?></p>
                        </div>
                        <?php if (!$isDraft): ?>
                            <div class="flex flex-wrap gap-2">
                                <a href="<?php echo admin_url('module/commission-contracts/pdf/' . $contract['id'] . '?view=1'); ?>" target="_blank" class="px-4 sm:px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 font-medium inline-flex items-center justify-center gap-2 text-sm sm:text-base">
                                    <span class="material-symbols-outlined">visibility</span> PDF Görüntüle
                                </a>
                                <a href="<?php echo admin_url('module/commission-contracts/pdf/' . $contract['id']); ?>" target="_blank" class="px-4 sm:px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 font-medium inline-flex items-center justify-center gap-2 text-sm sm:text-base">
                                    <span class="material-symbols-outlined">download</span> PDF İndir
                                </a>
                            </div>
                        <?php endif; ?>
                    </header>

                    <?php if (!$isDraft): ?>
                    <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 mb-6">
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Sözleşme takip bilgisi</h2>
                        <div class="flex flex-wrap gap-6 text-sm text-gray-600 dark:text-gray-300">
                            <span><strong>Sözleşme numarası:</strong> <?php echo esc_html($contract['contract_number'] ?? '—'); ?></span>
                            <span><strong>Sözleşme ismi:</strong> <?php echo esc_html($contract['contract_name'] ?? '—'); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($isDraft): ?>
                    <form id="commission-contract-update-form" action="<?php echo admin_url('module/commission-contracts/update/' . $contract['id']); ?>" method="POST" class="space-y-6">
                        <input type="hidden" name="page" value="module/commission-contracts/update/<?php echo (int)$contract['id']; ?>" />
                        <input type="hidden" name="client_name" value="<?php echo esc_attr($contract['client_name'] ?? ''); ?>" />
                    <?php endif; ?>

                        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sözleşme takip bilgisi</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Bu bilgiler sadece panelde takip için kullanılır, PDF'te görünmez.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="contract_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sözleşme numarası</label>
                                    <input type="text" id="contract_number" name="contract_number" value="<?php echo esc_attr($contract['contract_number'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" placeholder="Örn: 2026-001" <?php echo $readonlyAttr; ?>>
                                </div>
                                <div>
                                    <label for="contract_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sözleşme ismi</label>
                                    <input type="text" id="contract_name" name="contract_name" value="<?php echo esc_attr($contract['contract_name'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" placeholder="Örn: Ahmet Yılmaz - Taşınmaz Gösterim" <?php echo $readonlyAttr; ?>>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Başlık</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="space-y-2">
                                    <?php foreach (array_values($headerConfig['left'] ?? []) as $idx => $f): $k = $f['key'] ?? 'h_left_'.($idx+1); $val = $formData[$k] ?? ''; $hf = $f['type'] ?? 'text'; ?>
                                    <div>
                                        <label class="block text-xs text-gray-500 dark:text-white"><?php echo esc_html($f['label'] ?? $k); ?></label>
                                        <?php if ($hf === 'date'): ?>
                                            <input type="date" name="form_data[<?php echo esc_attr($k); ?>]" value="<?php echo esc_attr($val); ?>" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $isDraft ? '' : 'readonly'; ?>>
                                        <?php elseif ($hf === 'number'): ?>
                                            <input type="number" name="form_data[<?php echo esc_attr($k); ?>]" value="<?php echo esc_attr($val); ?>" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $isDraft ? '' : 'readonly'; ?>>
                                        <?php elseif ($hf === 'checkbox'): ?>
                                            <label class="inline-flex items-center gap-2 cursor-pointer"><input type="checkbox" class="form-data-checkbox-ui rounded" name="form_data[<?php echo esc_attr($k); ?>]" value="1" <?php echo $isCheckboxChecked($val) ? 'checked' : ''; ?> <?php echo $isDraft ? '' : 'disabled'; ?>></label>
                                        <?php else: ?>
                                            <input type="text" name="form_data[<?php echo esc_attr($k); ?>]" value="<?php echo esc_attr($val); ?>" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $isDraft ? '' : 'readonly'; ?>>
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
                                    <?php foreach (array_values($headerConfig['right'] ?? []) as $idx => $f): $k = $f['key'] ?? 'h_right_'.($idx+1); $val = $formData[$k] ?? ''; $hf = $f['type'] ?? 'text'; ?>
                                    <div>
                                        <label class="block text-xs text-gray-500 dark:text-white"><?php echo esc_html($f['label'] ?? $k); ?></label>
                                        <?php if ($hf === 'date'): ?>
                                            <input type="date" name="form_data[<?php echo esc_attr($k); ?>]" value="<?php echo esc_attr($val); ?>" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $isDraft ? '' : 'readonly'; ?>>
                                        <?php elseif ($hf === 'number'): ?>
                                            <input type="number" name="form_data[<?php echo esc_attr($k); ?>]" value="<?php echo esc_attr($val); ?>" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $isDraft ? '' : 'readonly'; ?>>
                                        <?php elseif ($hf === 'checkbox'): ?>
                                            <label class="inline-flex items-center gap-2 cursor-pointer"><input type="checkbox" class="form-data-checkbox-ui rounded" name="form_data[<?php echo esc_attr($k); ?>]" value="1" <?php echo $isCheckboxChecked($val) ? 'checked' : ''; ?> <?php echo $isDraft ? '' : 'disabled'; ?>></label>
                                        <?php else: ?>
                                            <input type="text" name="form_data[<?php echo esc_attr($k); ?>]" value="<?php echo esc_attr($val); ?>" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $isDraft ? '' : 'readonly'; ?>>
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
                                    <?php foreach ($fields as $f): $k = $f['key'] ?? ''; $label = $f['label'] ?? $k; $ft = $f['type'] ?? 'text'; $val = $formData[$k] ?? ''; ?>
                                    <div>
                                        <label class="block text-xs text-gray-500 dark:text-white"><?php echo esc_html($label); ?></label>
                                        <?php if ($ft === 'date'): ?>
                                            <input type="date" name="form_data[<?php echo esc_attr($k); ?>]" value="<?php echo esc_attr($val); ?>" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $readonlyAttr; ?>>
                                        <?php elseif ($ft === 'number'): ?>
                                            <input type="number" name="form_data[<?php echo esc_attr($k); ?>]" value="<?php echo esc_attr($val); ?>" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $readonlyAttr; ?>>
                                        <?php elseif ($ft === 'checkbox'): ?>
                                            <label class="inline-flex items-center gap-2 cursor-pointer"><input type="checkbox" class="form-data-checkbox-ui rounded" name="form_data[<?php echo esc_attr($k); ?>]" value="1" <?php echo $isCheckboxChecked($val) ? 'checked' : ''; ?> <?php echo $disabledAttr; ?>></label>
                                        <?php elseif ($ft === 'email'): ?>
                                            <input type="email" name="form_data[<?php echo esc_attr($k); ?>]" value="<?php echo esc_attr($val); ?>" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $readonlyAttr; ?>>
                                        <?php elseif ($ft === 'phone'): ?>
                                            <input type="tel" name="form_data[<?php echo esc_attr($k); ?>]" value="<?php echo esc_attr($val); ?>" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $readonlyAttr; ?>>
                                        <?php elseif ($ft === 'textarea'): ?>
                                            <textarea name="form_data[<?php echo esc_attr($k); ?>]" rows="2" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $readonlyAttr; ?>><?php echo esc_html($val); ?></textarea>
                                        <?php else: ?>
                                            <input type="text" name="form_data[<?php echo esc_attr($k); ?>]" value="<?php echo esc_attr($val); ?>" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $readonlyAttr; ?>>
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
                                                    $val = $formData[$key] ?? '';
                                                ?>
                                                <td class="border border-gray-300 dark:border-gray-600 p-1">
                                                    <?php if ($cellType === 'label'): ?>
                                                        <span class="text-sm text-gray-500 dark:text-white"><?php echo esc_html($val ?: (string)((int)$r + 1)); ?></span>
                                                        <input type="hidden" name="form_data[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($val ?: (string)((int)$r + 1)); ?>">
                                                    <?php elseif ($cellType === 'checkbox'): ?>
                                                        <label class="inline-flex items-center gap-1 cursor-pointer"><input type="checkbox" class="form-data-checkbox-ui rounded" name="form_data[<?php echo esc_attr($key); ?>]" value="1" <?php echo $isCheckboxChecked($val) ? 'checked' : ''; ?> <?php echo $disabledAttr; ?>></label>
                                                    <?php elseif ($cellType === 'number'): ?>
                                                        <input type="number" step="any" name="form_data[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($val); ?>" class="w-full px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $readonlyAttr; ?>>
                                                    <?php elseif ($cellType === 'date'): ?>
                                                        <input type="date" name="form_data[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($val); ?>" class="w-full px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $readonlyAttr; ?>>
                                                    <?php elseif ($cellType === 'signature'): ?>
                                                        <?php if (!empty($val)): ?><img src="<?php echo esc_url($val); ?>" alt="İmza" class="max-w-[120px] max-h-[50px]"><?php endif; ?>
                                                        <input type="hidden" name="form_data[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($val); ?>">
                                                    <?php else: ?>
                                                        <input type="text" name="form_data[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($val); ?>" class="w-full px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $readonlyAttr; ?>>
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
                                            <?php for ($c = 0; $c < $cols; $c++): $cellType = $rowCells[$c] ?? 'text'; $key = $legacyKeys ? ('cell_' . $r . '_' . $c) : ('sec_' . $secIdx . '_cell_' . $r . '_' . $c); $val = $formData[$key] ?? ''; ?>
                                                <td class="border border-gray-300 dark:border-gray-600 p-1">
                                                    <?php if ($cellType === 'label'): ?>
                                                        <span class="text-sm text-gray-500 dark:text-white"><?php echo esc_html($val ?: (string)((int)$r + 1)); ?></span>
                                                        <input type="hidden" name="form_data[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($val ?: (string)((int)$r + 1)); ?>">
                                                    <?php elseif ($cellType === 'checkbox'): ?>
                                                        <label class="inline-flex items-center gap-1 cursor-pointer"><input type="checkbox" class="form-data-checkbox-ui rounded" name="form_data[<?php echo esc_attr($key); ?>]" value="1" <?php echo $isCheckboxChecked($val) ? 'checked' : ''; ?> <?php echo $disabledAttr; ?>></label>
                                                    <?php elseif ($cellType === 'number'): ?>
                                                        <input type="number" step="any" name="form_data[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($val); ?>" class="w-full px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $readonlyAttr; ?>>
                                                    <?php elseif ($cellType === 'date'): ?>
                                                        <input type="date" name="form_data[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($val); ?>" class="w-full px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $readonlyAttr; ?>>
                                                    <?php elseif ($cellType === 'signature'): ?>
                                                        <?php if (!empty($val)): ?><img src="<?php echo esc_url($val); ?>" alt="İmza" class="max-w-[120px] max-h-[50px]"><?php endif; ?>
                                                        <input type="hidden" name="form_data[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($val); ?>">
                                                    <?php else: ?>
                                                        <input type="text" name="form_data[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($val); ?>" class="w-full px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $readonlyAttr; ?>>
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
                                    <?php foreach ($fields as $f): $k = $f['key'] ?? ''; $label = $f['label'] ?? $k; $ft = $f['type'] ?? 'text'; $val = $formData[$k] ?? ''; ?>
                                    <div>
                                        <label class="block text-xs text-gray-500 dark:text-white"><?php echo esc_html($label); ?></label>
                                        <?php if ($ft === 'date'): ?>
                                            <input type="date" name="form_data[<?php echo esc_attr($k); ?>]" value="<?php echo esc_attr($val); ?>" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $readonlyAttr; ?>>
                                        <?php elseif ($ft === 'number'): ?>
                                            <input type="number" name="form_data[<?php echo esc_attr($k); ?>]" value="<?php echo esc_attr($val); ?>" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $readonlyAttr; ?>>
                                        <?php elseif ($ft === 'checkbox'): ?>
                                            <label class="inline-flex items-center gap-2 cursor-pointer"><input type="checkbox" class="form-data-checkbox-ui rounded" name="form_data[<?php echo esc_attr($k); ?>]" value="1" <?php echo $isCheckboxChecked($val) ? 'checked' : ''; ?> <?php echo $disabledAttr; ?>></label>
                                        <?php elseif ($ft === 'email'): ?>
                                            <input type="email" name="form_data[<?php echo esc_attr($k); ?>]" value="<?php echo esc_attr($val); ?>" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $readonlyAttr; ?>>
                                        <?php elseif ($ft === 'phone'): ?>
                                            <input type="tel" name="form_data[<?php echo esc_attr($k); ?>]" value="<?php echo esc_attr($val); ?>" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $readonlyAttr; ?>>
                                        <?php elseif ($ft === 'textarea'): ?>
                                            <textarea name="form_data[<?php echo esc_attr($k); ?>]" rows="2" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $readonlyAttr; ?>><?php echo esc_html($val); ?></textarea>
                                        <?php elseif ($ft === 'signature'): ?>
                                            <?php if (!empty($val)): ?><img src="<?php echo esc_url($val); ?>" alt="İmza" class="max-w-[120px] max-h-[50px]"><?php endif; ?>
                                            <input type="hidden" name="form_data[<?php echo esc_attr($k); ?>]" value="<?php echo esc_attr($val); ?>">
                                        <?php else: ?>
                                            <input type="text" name="form_data[<?php echo esc_attr($k); ?>]" value="<?php echo esc_attr($val); ?>" class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $readonlyAttr; ?>>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                            <textarea name="form_data[<?php echo esc_attr($descKey); ?>]" rows="4" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" <?php echo $isDraft ? '' : 'readonly'; ?> placeholder="<?php echo esc_attr($footerConfig['description_label'] ?? 'Açıklama'); ?>"><?php echo esc_html($formData[$descKey] ?? ''); ?></textarea>
                        </div>
                        <?php endif; ?>

                    <?php if ($isDraft): ?>
                        <div class="flex flex-wrap gap-2 sm:gap-3">
                            <button type="submit" class="px-4 sm:px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 font-medium w-full sm:w-auto">Güncelle</button>
                            <a href="<?php echo admin_url('module/commission-contracts'); ?>" class="px-4 sm:px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 text-center w-full sm:w-auto">İptal</a>
                        </div>
                    </form>
                    <?php endif; ?>

                    <?php if ($isDraft): ?>
                        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 mt-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sözleşmeyi İmzala</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Aşağıdaki alana emlak işletmesi imzasını çizin. Müşteri imzası daha sonra ayrı bir süreçle alınacaktır.</p>
                            <div class="signature-canvas-wrapper border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 overflow-hidden w-full max-w-md">
                                <canvas id="signature-canvas" width="400" height="160" style="display: block; width: 100%; height: 160px; touch-action: none; cursor: crosshair;"></canvas>
                            </div>
                            <div class="flex flex-wrap gap-2 sm:gap-3 mt-4">
                                <button type="button" id="signature-clear" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 w-full sm:w-auto">Temizle</button>
                                <form action="<?php echo admin_url('module/commission-contracts/sign/' . $contract['id']); ?>" method="POST" id="sign-form" class="inline w-full sm:w-auto sm:flex-1">
                                    <input type="hidden" name="page" value="module/commission-contracts/sign/<?php echo (int)$contract['id']; ?>" />
                                    <input type="hidden" name="signature_data" id="signature-data-input" value="" />
                                    <button type="submit" id="sign-submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium text-sm w-full sm:w-auto">İmzala ve Kaydet</button>
                                </form>
                            </div>
                        </div>
                    <?php else:
                        $footerSignatures = isset($footerConfig['signatures']) && is_array($footerConfig['signatures']) ? $footerConfig['signatures'] : [ ['label' => 'Emlak İşletmesi İmzası'], ['label' => 'Müşteri İmzası'] ];
                    ?>
                        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 mt-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"><?php echo esc_html($footerConfig['signature_label'] ?? 'İmza'); ?></h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">İmza tarihi: <?php echo $contract['signed_at'] ? date('d.m.Y H:i', strtotime($contract['signed_at'])) : '—'; ?></p>
                            <?php $sigCols = min(5, max(2, count($footerSignatures))); ?>
                            <div class="grid gap-4 sm:gap-6" style="grid-template-columns: repeat(<?php echo (int)$sigCols; ?>, minmax(0, 1fr));">
                                <?php
                                $sigDataKeys = [ 1 => 'signature_data', 2 => 'signature_data_party2', 3 => 'signature_data_party3' ];
                                $sigAtKeys = [ 2 => 'signed_at_party2', 3 => 'signed_at_party3' ];
                                foreach ($footerSignatures as $idx => $sig):
                                    $partyNum = $idx + 1;
                                    $label = isset($sig['label']) ? $sig['label'] : ('İmza ' . $partyNum);
                                    $dataKey = $sigDataKeys[$partyNum] ?? null;
                                    $atKey = $sigAtKeys[$partyNum] ?? null;
                                    $img = $dataKey ? ($contract[$dataKey] ?? '') : '';
                                ?>
                                <div class="min-w-0 flex flex-col rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800/50 p-4">
                                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-2 shrink-0"><?php echo esc_html($label); ?></p>
                                    <div class="min-h-[80px] flex flex-col justify-center min-w-0">
                                        <?php if (!empty($img)): ?>
                                            <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($label); ?>" class="max-w-full max-h-28 w-full object-contain border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800" />
                                            <?php if ($atKey && !empty($contract[$atKey])): ?>
                                                <p class="text-xs text-green-600 dark:text-green-400 mt-2 shrink-0">İmzalandı: <?php echo date('d.m.Y H:i', strtotime($contract[$atKey])); ?></p>
                                            <?php endif; ?>
                                        <?php elseif ($partyNum === 1): ?>
                                            <span class="text-gray-400 dark:text-gray-500">—</span>
                                        <?php else: ?>
                                            <span class="text-gray-400 dark:text-gray-500 text-sm">Karşı taraf tarafından imzalanacak</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php
                            $hasLink2 = (($contract['status'] ?? '') === 'signed' && empty($contract['signature_data_party2']) && !empty($contract['sign_token']));
                            $hasLink3 = (($contract['status'] ?? '') === 'signed' && empty($contract['signature_data_party3']) && !empty($contract['sign_token_party3']));
                            $label2 = isset($footerSignatures[1]['label']) ? $footerSignatures[1]['label'] : '2. Taraf';
                            $label3 = isset($footerSignatures[2]['label']) ? $footerSignatures[2]['label'] : '3. Taraf';
                            if ($hasLink2 || $hasLink3):
                            ?>
                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600 space-y-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">İmza linklerini ilgili taraflara gönderin; her taraf kendi linkinden canvas ile imza atabilir.</p>
                                <?php if ($hasLink2): ?>
                                <div class="flex gap-2 flex-wrap items-center">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 w-32 shrink-0"><?php echo esc_html($label2); ?>:</span>
                                    <input type="text" readonly value="<?php echo esc_attr(site_url('sozlesme-imza/' . $contract['sign_token'])); ?>" class="flex-1 min-w-0 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm party-sign-link" data-party="2">
                                    <button type="button" class="copy-sign-link-btn px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm whitespace-nowrap" data-party="2">Linki kopyala</button>
                                </div>
                                <?php endif; ?>
                                <?php if ($hasLink3): ?>
                                <div class="flex gap-2 flex-wrap items-center">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 w-32 shrink-0"><?php echo esc_html($label3); ?>:</span>
                                    <input type="text" readonly value="<?php echo esc_attr(site_url('sozlesme-imza/' . $contract['sign_token_party3'])); ?>" class="flex-1 min-w-0 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm party-sign-link" data-party="3">
                                    <button type="button" class="copy-sign-link-btn px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm whitespace-nowrap" data-party="3">Linki kopyala</button>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                </div>
            </main>
        </div>
    </div>
</div>
<?php if ($isDraft): ?>
<script>
// Checkbox değerlerini hidden input'a yaz
(function() {
    function syncCheckboxToHidden(cb) {
        var key = cb.getAttribute('data-key');
        if (key) {
            var hidden = cb.closest('label').previousElementSibling;
            if (hidden && hidden.type === 'hidden') {
                hidden.value = cb.checked ? '1' : '0';
            }
        }
    }
    document.querySelectorAll('.form-data-checkbox-ui').forEach(function(cb) {
        cb.addEventListener('change', function() { syncCheckboxToHidden(cb); });
        // Sayfa yüklendiğinde de sync yap
        syncCheckboxToHidden(cb);
    });
    // Form submit'te son bir sync yap
    var form = document.getElementById('commission-contract-update-form');
    if (form) {
        form.addEventListener('submit', function() {
            document.querySelectorAll('.form-data-checkbox-ui').forEach(syncCheckboxToHidden);
        });
    }
})();

(function() {
    var canvas = document.getElementById('signature-canvas');
    var signInput = document.getElementById('signature-data-input');
    var signForm = document.getElementById('sign-form');
    var signSubmit = document.getElementById('sign-submit');
    var signClear = document.getElementById('signature-clear');
    if (canvas && signInput && signForm) {
        var ctx = canvas.getContext('2d');
        var drawing = false;
        var lastX = 0, lastY = 0;
        var wrapper = canvas.closest('.signature-canvas-wrapper') || canvas.parentElement;
        function resizeCanvas() {
            if (!wrapper) return;
            var w = wrapper.clientWidth;
            var h = Math.max(120, Math.round((160 / 400) * w));
            if (canvas.width !== w || canvas.height !== h) {
                canvas.width = w;
                canvas.height = h;
            }
        }
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);
        function getPos(e) {
            var rect = canvas.getBoundingClientRect();
            var scaleX = canvas.width / rect.width;
            var scaleY = canvas.height / rect.height;
            var clientX = e.touches ? e.touches[0].clientX : e.clientX;
            var clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return { x: (clientX - rect.left) * scaleX, y: (clientY - rect.top) * scaleY };
        }
        function start(e) { e.preventDefault(); drawing = true; var p = getPos(e); lastX = p.x; lastY = p.y; }
        function draw(e) {
            e.preventDefault();
            if (!drawing) return;
            var p = getPos(e);
            ctx.strokeStyle = '#000';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
            lastX = p.x; lastY = p.y;
        }
        function end() { drawing = false; }
        canvas.addEventListener('mousedown', start);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', end);
        canvas.addEventListener('mouseout', end);
        canvas.addEventListener('touchstart', start, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', end);
        if (signClear) signClear.addEventListener('click', function() { ctx.clearRect(0, 0, canvas.width, canvas.height); });
        signForm.addEventListener('submit', function(e) {
            signInput.value = canvas.toDataURL('image/png');
            if (!signInput.value || signInput.value.length < 100) {
                e.preventDefault();
                alert('Lütfen imza alanına imzanızı çizin.');
                return false;
            }
            signSubmit.disabled = true;
        });
    }
})();
</script>
<?php endif; ?>
<?php if (($contract['status'] ?? '') === 'signed' && (!empty($contract['sign_token']) || !empty($contract['sign_token_party3']))): ?>
<script>
(function() {
    document.querySelectorAll('.copy-sign-link-btn').forEach(function(btn) {
        var row = btn.closest('.flex');
        var input = row ? row.querySelector('.party-sign-link') : null;
        if (input) {
            btn.addEventListener('click', function() {
                input.select();
                input.setSelectionRange(0, 99999);
                try {
                    navigator.clipboard.writeText(input.value);
                    var t = btn.textContent;
                    btn.textContent = 'Kopyalandı!';
                    setTimeout(function() { btn.textContent = t; }, 2000);
                } catch (e) {
                    document.execCommand('copy');
                    btn.textContent = 'Kopyalandı!';
                    setTimeout(function() { btn.textContent = t; }, 2000);
                }
            });
        }
    });
})();
</script>
<?php endif; ?>
<?php include $rootPath . '/app/views/admin/snippets/footer.php'; ?>
