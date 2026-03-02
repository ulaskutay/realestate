<?php
if (!isset($rootPath)) $rootPath = dirname(dirname(dirname(__DIR__)));
include $rootPath . '/app/views/admin/snippets/header.php';
require_once $rootPath . '/includes/functions.php';
$contract = $contract ?? null;
$items = $items ?? [];
$isDraft = ($contract['status'] ?? '') === 'draft';

// Firma bilgilerini ayarlardan al
$settings = get_option('site_settings', []);
$companyLogo = $settings['site_logo'] ?? '';
$companyName = $settings['company_name'] ?? '';
$companyPerson = $settings['company_person'] ?? '';
$companyPhone = $settings['company_phone'] ?? '';
$companyAddress = $settings['company_address'] ?? '';
$companyTaxNumber = $settings['company_tax_number'] ?? '';
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
                            <h1 class="text-gray-900 dark:text-white text-2xl sm:text-3xl font-bold">Sözleşme #<?php echo (int) $contract['id']; ?></h1>
                            <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">
                                <?php echo $isDraft ? 'Düzenleyebilir veya imzalayabilirsiniz.' : 'İmzalanmış sözleşme.'; ?>
                            </p>
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
                    <form action="<?php echo admin_url('module/commission-contracts/update/' . $contract['id']); ?>" method="POST" class="space-y-6" id="contract-form">
                        <input type="hidden" name="page" value="module/commission-contracts/update/<?php echo (int) $contract['id']; ?>" />
                        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sözleşme takip bilgisi</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Bu bilgiler sadece panelde takip için kullanılır, PDF'te görünmez.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="contract_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sözleşme numarası</label>
                                    <input type="text" id="contract_number" name="contract_number" value="<?php echo esc_attr($contract['contract_number'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" placeholder="Örn: 2026-001">
                                </div>
                                <div>
                                    <label for="contract_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sözleşme ismi</label>
                                    <input type="text" id="contract_name" name="contract_name" value="<?php echo esc_attr($contract['contract_name'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" placeholder="Örn: Ahmet Yılmaz - Taşınmaz Gösterim">
                                </div>
                            </div>
                        </div>

                        <!-- Müşteri Bilgileri -->
                        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Müşteri Bilgileri</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Müşteri tipi</label>
                                    <select name="client_type" id="client_type" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" <?php echo $isDraft ? '' : 'disabled'; ?>>
                                        <option value="individual" <?php echo ($contract['client_type'] ?? '') === 'individual' ? 'selected' : ''; ?>>Gerçek Kişi</option>
                                        <option value="legal" <?php echo ($contract['client_type'] ?? '') === 'legal' ? 'selected' : ''; ?>>Tüzel Kişi</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="client_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ad / Unvan <span class="text-red-500">*</span></label>
                                    <input type="text" id="client_name" name="client_name" required
                                        value="<?php echo esc_attr($contract['client_name'] ?? ''); ?>"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" <?php echo $isDraft ? '' : 'readonly'; ?>>
                                </div>
                                <div class="client-legal-only <?php echo ($contract['client_type'] ?? '') === 'legal' ? '' : 'hidden'; ?>">
                                    <label for="client_tax_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Vergi No</label>
                                    <input type="text" id="client_tax_number" name="client_tax_number" value="<?php echo esc_attr($contract['client_tax_number'] ?? ''); ?>"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" <?php echo $isDraft ? '' : 'readonly'; ?>>
                                </div>
                                <div class="client-legal-only <?php echo ($contract['client_type'] ?? '') === 'legal' ? '' : 'hidden'; ?>">
                                    <label for="client_tax_office" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Vergi Dairesi</label>
                                    <input type="text" id="client_tax_office" name="client_tax_office" value="<?php echo esc_attr($contract['client_tax_office'] ?? ''); ?>"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" <?php echo $isDraft ? '' : 'readonly'; ?>>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="client_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Adres</label>
                                    <textarea id="client_address" name="client_address" rows="2" <?php echo $isDraft ? '' : 'readonly'; ?>
                                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"><?php echo esc_html($contract['client_address'] ?? ''); ?></textarea>
                                </div>
                                <div>
                                    <label for="client_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Telefon</label>
                                    <input type="text" id="client_phone" name="client_phone" value="<?php echo esc_attr($contract['client_phone'] ?? ''); ?>"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" <?php echo $isDraft ? '' : 'readonly'; ?>>
                                </div>
                                <div>
                                    <label for="client_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">E-posta</label>
                                    <input type="email" id="client_email" name="client_email" value="<?php echo esc_attr($contract['client_email'] ?? ''); ?>"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" <?php echo $isDraft ? '' : 'readonly'; ?>>
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
                                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" <?php echo $isDraft ? '' : 'readonly'; ?>>
                                    </div>
                                    <div>
                                        <label for="company_phone_field" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Telefon</label>
                                        <input type="text" id="company_phone_field" name="company_phone_field" 
                                            value="<?php echo esc_attr($companyPhone); ?>"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" <?php echo $isDraft ? '' : 'readonly'; ?>>
                                    </div>
                                    <div>
                                        <label for="company_name_field" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ünvanı</label>
                                        <input type="text" id="company_name_field" name="company_name_field" 
                                            value="<?php echo esc_attr($companyName); ?>"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" <?php echo $isDraft ? '' : 'readonly'; ?>>
                                    </div>
                                    <div>
                                        <label for="company_address_field" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Adres</label>
                                        <input type="text" id="company_address_field" name="company_address_field" 
                                            value="<?php echo esc_attr($companyAddress); ?>"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" <?php echo $isDraft ? '' : 'readonly'; ?>>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Taşınmazlar -->
                        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 mb-4">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Taşınmazlar</h2>
                                <?php if ($isDraft): ?>
                                    <button type="button" id="add-item" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 text-sm font-medium flex items-center justify-center gap-2 w-full sm:w-auto">
                                        <span class="material-symbols-outlined text-lg">add</span> Taşınmaz Ekle
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div id="items-container" class="space-y-4">
                                <?php
                                if (empty($items)):
                                    $items = [['description' => '', 'listing_type' => 'sale', 'price' => 0]];
                                endif;
                                foreach ($items as $idx => $item):
                                    $listingType = $item['listing_type'] ?? 'sale';
                                ?>
                                <div class="item-row border border-gray-200 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-800/50" data-index="<?php echo $idx; ?>">
                                    <div class="flex justify-between items-start mb-3">
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Taşınmaz <?php echo $idx + 1; ?></span>
                                        <?php if ($isDraft): ?><button type="button" class="remove-item text-red-600 hover:text-red-800 text-sm <?php echo count($items) <= 1 ? 'hidden' : ''; ?>">Kaldır</button><?php endif; ?>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <div class="sm:col-span-2">
                                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Açıklama</label>
                                            <textarea name="item_description[]" rows="2" <?php echo $isDraft ? '' : 'readonly'; ?>
                                                class="item-desc w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm resize-y min-h-[4.5rem]"><?php echo esc_html($item['description'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="min-w-0">
                                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Satılık / Kiralık</label>
                                            <select name="item_listing_type[]" class="item-listing-type w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $isDraft ? '' : 'disabled'; ?>>
                                                <option value="sale" <?php echo $listingType === 'sale' ? 'selected' : ''; ?>>Satılık</option>
                                                <option value="rent" <?php echo $listingType === 'rent' ? 'selected' : ''; ?>>Kiralık</option>
                                            </select>
                                        </div>
                                        <div class="min-w-0 sm:col-span-1">
                                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Fiyat / Aylık Kira (₺)</label>
                                            <input type="number" name="item_price[]" step="0.01" min="0" value="<?php echo esc_attr($item['price'] ?? 0); ?>"
                                                class="item-price w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" <?php echo $isDraft ? '' : 'readonly'; ?>>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2 sm:gap-3">
                            <button type="submit" class="px-4 sm:px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 font-medium w-full sm:w-auto">Güncelle</button>
                            <a href="<?php echo admin_url('module/commission-contracts'); ?>" class="px-4 sm:px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 text-center w-full sm:w-auto">İptal</a>
                        </div>
                    </form>
                    <?php endif; ?>

                    <?php if ($isDraft): ?>
                        <!-- İmza Alanı -->
                        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 mt-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sözleşmeyi İmzala</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Aşağıdaki alana imzanızı çizin. İmzaladıktan sonra sözleşme "İmzalandı" olarak kaydedilir ve düzenleme kapatılır.</p>
                            <div class="signature-canvas-wrapper border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 overflow-hidden w-full max-w-md">
                                <canvas id="signature-canvas" width="500" height="180" style="display: block; width: 100%; height: 180px; touch-action: none; cursor: crosshair;"></canvas>
                            </div>
                            <div class="flex flex-wrap gap-2 sm:gap-3 mt-4">
                                <button type="button" id="signature-clear" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 text-sm w-full sm:w-auto">Temizle</button>
                                <form action="<?php echo admin_url('module/commission-contracts/sign/' . $contract['id']); ?>" method="POST" id="sign-form" class="inline w-full sm:w-auto sm:flex-1">
                                    <input type="hidden" name="page" value="module/commission-contracts/sign/<?php echo (int) $contract['id']; ?>" />
                                    <input type="hidden" name="signature_data" id="signature-data-input" value="" />
                                    <button type="submit" id="sign-submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium text-sm w-full sm:w-auto">İmzala ve Kaydet</button>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- İmzalanmış: imza görseli -->
                        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 mt-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Müşteri İmzası</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">İmza tarihi: <?php echo $contract['signed_at'] ? date('d.m.Y H:i', strtotime($contract['signed_at'])) : '—'; ?></p>
                            <?php if (!empty($contract['signature_data'])): ?>
                                <img src="<?php echo esc_url($contract['signature_data']); ?>" alt="İmza" class="max-w-xs border border-gray-300 dark:border-gray-600 rounded" />
                            <?php else: ?>
                                <p class="text-gray-500 dark:text-gray-400">İmza görseli kayıtlı değil.</p>
                            <?php endif; ?>
                            <?php if (($contract['status'] ?? '') === 'signed' && empty($contract['signature_data_party2']) && !empty($contract['sign_token'])): ?>
                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Bu linki karşı tarafa gönderin; karşı taraf bu linkten canvas ile imza atabilir.</p>
                                <div class="flex gap-2 flex-wrap items-center">
                                    <input type="text" readonly value="<?php echo esc_attr(site_url('sozlesme-imza/' . $contract['sign_token'])); ?>" class="flex-1 min-w-0 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm" id="party2-sign-link">
                                    <button type="button" id="copy-party2-link-btn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm whitespace-nowrap">Linki kopyala</button>
                                </div>
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
document.addEventListener('DOMContentLoaded', function() {
    var clientType = document.getElementById('client_type');
    var legalOnly = document.querySelectorAll('.client-legal-only');
    function toggleLegal() {
        var show = clientType && clientType.value === 'legal';
        legalOnly.forEach(function(el) { el.classList.toggle('hidden', !show); });
    }
    if (clientType) { clientType.addEventListener('change', toggleLegal); toggleLegal(); }

    var container = document.getElementById('items-container');
    if (!container) return;
    var addBtn = document.getElementById('add-item');
    var rows = container.querySelectorAll('.item-row');
    var firstRow = rows[0];
    var indexCounter = rows.length;

    function updateRowLabels() {
        container.querySelectorAll('.item-row').forEach(function(row, i) {
            var lbl = row.querySelector('span.text-sm.font-medium');
            if (lbl) lbl.textContent = 'Taşınmaz ' + (i + 1);
            var rm = row.querySelector('.remove-item');
            if (rm) rm.classList.toggle('hidden', container.querySelectorAll('.item-row').length <= 1);
        });
    }

    if (addBtn && firstRow) {
        addBtn.addEventListener('click', function() {
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
        });
    }

    container.querySelectorAll('.item-row').forEach(function(row) {
        var rm = row.querySelector('.remove-item');
        if (rm) rm.onclick = function() {
            if (container.querySelectorAll('.item-row').length <= 1) return;
            row.remove();
            updateRowLabels();
        };
    });

    // Signature pad
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
            var h = Math.max(120, Math.round((180 / 500) * w));
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

        function start(e) {
            e.preventDefault();
            drawing = true;
            var p = getPos(e);
            lastX = p.x;
            lastY = p.y;
        }
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
            lastX = p.x;
            lastY = p.y;
        }
        function end() { drawing = false; }

        canvas.addEventListener('mousedown', start);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', end);
        canvas.addEventListener('mouseout', end);
        canvas.addEventListener('touchstart', start, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', end);

        if (signClear) signClear.addEventListener('click', function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        });

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
});
</script>
<?php endif; ?>
<?php if (($contract['status'] ?? '') === 'signed' && !empty($contract['sign_token'])): ?>
<script>
(function() {
    var btn = document.getElementById('copy-party2-link-btn');
    var input = document.getElementById('party2-sign-link');
    if (btn && input) {
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
                setTimeout(function() { btn.textContent = 'Linki kopyala'; }, 2000);
            }
        });
    }
})();
</script>
<?php endif; ?>
<?php include $rootPath . '/app/views/admin/snippets/footer.php'; ?>
