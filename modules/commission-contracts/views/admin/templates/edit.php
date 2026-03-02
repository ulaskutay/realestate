<?php
if (!isset($rootPath)) $rootPath = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
include $rootPath . '/app/views/admin/snippets/header.php';
$template = $template ?? null;
$isEdit = !empty($template);
$headerConfig = $isEdit && !empty($template['header_config']) ? (is_string($template['header_config']) ? json_decode($template['header_config'], true) : $template['header_config']) : ['left' => [], 'center' => 'logo', 'right' => []];
$footerConfig = $isEdit && !empty($template['footer_config']) ? (is_string($template['footer_config']) ? json_decode($template['footer_config'], true) : $template['footer_config']) : ['description_label' => 'Açıklama', 'description_key' => 'footer_description', 'signature_label' => 'İmza', 'signature_count' => 1];
if (!is_array($headerConfig)) $headerConfig = ['left' => [], 'center' => 'logo', 'right' => []];
if (!is_array($footerConfig)) $footerConfig = ['description_label' => 'Açıklama', 'description_key' => 'footer_description', 'signature_label' => 'İmza', 'signature_count' => 1];
$sectionsForEditor = isset($template['sections']) && is_array($template['sections']) ? $template['sections'] : [];
$footerBlocksForEditor = isset($footerConfig['blocks']) && is_array($footerConfig['blocks']) ? $footerConfig['blocks'] : [];
$footerSignaturesForEditor = isset($footerConfig['signatures']) && is_array($footerConfig['signatures']) ? $footerConfig['signatures'] : [ ['label' => 'Emlak İşletmesi İmzası'], ['label' => 'Müşteri İmzası'] ];
?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
    <div class="flex min-h-screen">
        <?php $currentPage = 'commission-contracts'; include $rootPath . '/app/views/admin/snippets/sidebar.php'; ?>
        <div class="flex-1 flex flex-col lg:ml-64">
            <?php include $rootPath . '/app/views/admin/snippets/top-header.php'; ?>
            <main class="flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b] overflow-y-auto">
                <div class="layout-content-container commission-contracts-content flex flex-col w-full mx-auto max-w-7xl">

                    <header class="mb-6">
                        <a href="<?php echo admin_url('module/commission-contracts/templates'); ?>" class="text-gray-500 dark:text-white hover:text-primary transition-colors inline-flex items-center gap-2 mb-2 text-sm sm:text-base">
                            <span class="material-symbols-outlined text-lg">arrow_back</span> Şablonlara dön
                        </a>
                        <h1 class="text-gray-900 dark:text-white text-2xl sm:text-3xl font-bold"><?php echo $isEdit ? 'Şablonu Düzenle' : 'Yeni Şablon'; ?></h1>
                    </header>

                    <form action="<?php echo $isEdit ? admin_url('module/commission-contracts/templates/update/' . (int)$template['id']) : admin_url('module/commission-contracts/templates/store'); ?>" method="POST" id="template-form">
                        <input type="hidden" name="page" value="<?php echo $isEdit ? 'module/commission-contracts/templates/update/' . (int)$template['id'] : 'module/commission-contracts/templates/store'; ?>" />
                        <input type="hidden" name="header_config" id="header_config" value="" />
                        <input type="hidden" name="table_config" id="table_config" value="" />
                        <input type="hidden" name="footer_config" id="footer_config" value="" />

                        <div class="space-y-6">
                            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Temel Bilgiler</h2>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="template_name" class="block text-sm font-medium text-gray-700 dark:text-white mb-2">Şablon Adı *</label>
                                        <input type="text" id="template_name" name="template_name" required value="<?php echo esc_attr($template['name'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                    </div>
                                    <div>
                                        <label for="template_slug" class="block text-sm font-medium text-gray-700 dark:text-white mb-2">Slug <span class="text-gray-500 dark:text-white/90 font-normal">(otomatik üretilir, boş bırakılabilir)</span></label>
                                        <input type="text" id="template_slug" name="template_slug" value="<?php echo esc_attr($template['slug'] ?? ''); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" placeholder="orn-komisyon-sozlesmesi">
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Başlık</h2>
                                <p class="text-sm text-gray-500 dark:text-white mb-4">Sözleşme başlığında logo ve sol/sağ alanları tanımlayın. Alanları sürükle-bırak ile sıralayabilirsiniz.</p>
                                <div class="mb-6 p-4 rounded-lg border-2 border-dashed border-gray-200 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-800/30">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" id="header-center-logo" <?php echo ($headerConfig['center'] ?? '') === 'logo' ? 'checked' : ''; ?> class="rounded border-gray-300 dark:border-gray-600 w-4 h-4">
                                        <span class="text-sm font-medium text-gray-700 dark:text-white">Sözleşme başlığında logo gösterilsin</span>
                                    </label>
                                    <p class="text-xs text-gray-500 dark:text-white/90 mt-1 ml-6">Site logosu sözleşme başlığının ortasında görünür.</p>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                                    <div class="min-w-0 flex flex-col">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">Sol alanlar</label>
                                        <div id="header-left-list" class="space-y-2 min-h-[72px] p-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800/50 overflow-hidden">
                                            <!-- JS ile doldurulur -->
                                        </div>
                                        <button type="button" id="add-header-left" class="mt-2 text-sm text-primary dark:text-white hover:underline shrink-0">+ Alan ekle</button>
                                    </div>
                                    <div class="min-w-0 flex flex-col">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">Sağ alanlar</label>
                                        <div id="header-right-list" class="space-y-2 min-h-[72px] p-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800/50 overflow-hidden">
                                            <!-- JS ile doldurulur -->
                                        </div>
                                        <button type="button" id="add-header-right" class="mt-2 text-sm text-primary dark:text-white hover:underline shrink-0">+ Alan ekle</button>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Orta Bölüm – Bölümler</h2>
                                <p class="text-sm text-gray-500 dark:text-white/90 mb-4">Belge ortasında görünecek bölümleri ekleyin. Her bölüm bir tablo veya blok (alan listesi) olabilir. Sürükleyerek sıralayabilirsiniz.</p>
                                <div id="sections-list" class="space-y-3 min-h-[60px] p-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800/50">
                                    <!-- JS ile doldurulur -->
                                </div>
                                <button type="button" id="section-add-btn" class="mt-3 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 text-sm font-medium">+ Bölüm Ekle</button>
                            </div>

                            <!-- Modal: Bölüm ekle / düzenle -->
                            <div id="section-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog">
                                <div class="flex min-h-screen items-center justify-center p-4">
                                    <div class="fixed inset-0 bg-black/50" id="section-modal-backdrop"></div>
                                    <div class="relative bg-white dark:bg-[#1e293b] rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto border border-gray-200 dark:border-gray-700">
                                        <div class="p-4 sm:p-6">
                                            <h3 id="section-modal-title" class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Bölüm Ekle</h3>
                                            <div class="space-y-6">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">Bölüm başlığı</label>
                                                    <input type="text" id="section-modal-title-input" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" placeholder="Örn: MÜŞTERİ BİLGİLERİ">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-white mb-3">Tip</label>
                                                    <div class="flex gap-6">
                                                        <label class="flex items-center gap-2 cursor-pointer text-gray-700 dark:text-gray-300">
                                                            <input type="radio" name="section-modal-type" value="block" class="rounded-full border-gray-300 dark:border-gray-600"> Blok
                                                        </label>
                                                        <label class="flex items-center gap-2 cursor-pointer text-gray-700 dark:text-gray-300">
                                                            <input type="radio" name="section-modal-type" value="table" class="rounded-full border-gray-300 dark:border-gray-600"> Tablo
                                                        </label>
                                                    </div>
                                                </div>
                                                <div id="section-modal-table-options" class="hidden space-y-8 border-t border-gray-200 dark:border-gray-600 pt-6 mt-6">
                                                    <div class="rounded-xl bg-gray-50 dark:bg-gray-800/50 p-5 border border-gray-200 dark:border-gray-600">
                                                        <h4 class="text-sm font-semibold text-gray-800 dark:text-white mb-4">1. Boyut ve görünüm</h4>
                                                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-5">
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">Tablo blok sayısı</label>
                                                                <input type="number" id="section-table-block-count" min="1" max="6" value="1" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                                                <p class="text-xs text-gray-500 dark:text-white/80 mt-1">Tabloyu 1–6 blokta bölebilirsiniz (örn. 3 blok: konum, parsel, bina bilgisi)</p>
                                                            </div>
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">Satır sayısı</label>
                                                                <input type="number" id="section-table-rows" min="1" max="30" value="5" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                                            </div>
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">Veri sütunu sayısı</label>
                                                                <input type="number" id="section-table-cols" min="1" max="15" value="3" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                                                <p class="text-xs text-gray-500 dark:text-white/80 mt-1">Örn. Taşınmaz 1, 2, 3 için 3</p>
                                                            </div>
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">Tema rengi</label>
                                                                <input type="color" id="section-table-theme" value="#4472C4" class="h-10 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 cursor-pointer">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="section-table-single" class="space-y-6">
                                                        <div class="rounded-xl bg-gray-50 dark:bg-gray-800/50 p-5 border border-gray-200 dark:border-gray-600">
                                                            <h4 class="text-sm font-semibold text-gray-800 dark:text-white mb-4">2. Satır etiketleri (ilk sütun)</h4>
                                                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Belgede tablonun ilk sütununda görünecek etiketler. Her satırı ayrı satıra yazın; satır sayısından az yazarsanız kalanlar "Satır N" olur.</p>
                                                            <textarea id="section-table-row-labels-ta" rows="6" class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm font-mono placeholder-gray-400" placeholder="İl&#10;İlçe&#10;Mahalle&#10;Ada&#10;Parsel&#10;Niteliği&#10;Satılık/Kiralık mı?&#10;Brüt Alan (m2)&#10;..."></textarea>
                                                        </div>
                                                        <div class="rounded-xl bg-gray-50 dark:bg-gray-800/50 p-5 border border-gray-200 dark:border-gray-600">
                                                            <h4 class="text-sm font-semibold text-gray-800 dark:text-white mb-4">3. Sütun başlıkları</h4>
                                                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Her veri sütunu için başlık (örn. Taşınmaz 1, Taşınmaz 2).</p>
                                                            <div id="section-table-headers" class="flex flex-wrap gap-3"></div>
                                                        </div>
                                                        <div class="rounded-xl bg-gray-50 dark:bg-gray-800/50 p-5 border border-gray-200 dark:border-gray-600">
                                                            <h4 class="text-sm font-semibold text-gray-800 dark:text-white mb-4">4. Hücre tipleri (satır bazlı)</h4>
                                                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Her satır için o satırdaki tüm hücrelerin tipi. Örn. "İl" satırı metin, "Satılık/Kiralık mı?" satırı checkbox, "İmza" satırı imza olabilir.</p>
                                                            <div id="section-table-row-types" class="space-y-3 max-h-64 overflow-y-auto pr-1"></div>
                                                        </div>
                                                    </div>
                                                    <div id="section-table-multi" class="hidden space-y-6">
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">Her blok için satır sayısı, sütun sayısı, etiketler ve sütun başlıklarını ayrı ayrı tanımlayın. Bloklar belgede yan yana görünür.</p>
                                                        <div id="section-table-blocks-list" class="space-y-6"></div>
                                                    </div>
                                                </div>
                                                <div id="section-modal-block-options" class="hidden space-y-6 border-t border-gray-200 dark:border-gray-600 pt-6 mt-6">
                                                    <div class="flex flex-wrap items-end gap-6">
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">Blok sayısı (sütun)</label>
                                                            <input type="number" id="section-block-count" min="1" max="4" value="2" class="w-full max-w-[120px] px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">Tema rengi</label>
                                                            <input type="color" id="section-block-theme" value="#4472C4" class="h-10 w-20 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 cursor-pointer">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Her blokta istediğiniz alanları ekleyin. Alan ekle butonu ilgili blokun altındadır.</p>
                                                        <div id="section-block-blocks" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                            <!-- Her blok için alan listesi -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex gap-4 mt-8 pt-6 border-t border-gray-200 dark:border-gray-600">
                                                <button type="button" id="section-modal-save" class="px-5 py-2.5 bg-primary text-white rounded-lg hover:bg-primary/90 font-medium text-sm">Kaydet</button>
                                                <button type="button" id="section-modal-cancel" class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 text-sm">İptal</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Alt Bölüm – İmza Ayarları</h2>
                                <p class="text-sm text-gray-500 dark:text-white/90 mb-4">Sözleşmede kaç kişi imza atacak ve her imzanın niteliği (etiket) ne olacak tanımlayın. 1. imza sizin (panelden atacağınız), diğerleri için link üretilir (satıcı/alıcı vb.).</p>
                                <div class="space-y-4">
                                    <div class="max-w-xs">
                                        <label for="footer-signature-count" class="block text-sm font-medium text-gray-700 dark:text-white mb-2">İmza sayısı (2–5)</label>
                                        <input type="number" id="footer-signature-count" min="2" max="5" value="<?php echo count($footerSignaturesForEditor); ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">Her imza için nitelik / etiket</label>
                                        <div id="footer-signatures-list" class="space-y-2"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <button type="submit" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 font-medium w-full sm:w-auto">Kaydet</button>
                                <a href="<?php echo admin_url('module/commission-contracts/templates'); ?>" class="px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 text-center w-full sm:w-auto">İptal</a>
                            </div>
                        </div>
                    </form>

                </div>
            </main>
        </div>
    </div>
</div>
<script>
(function() {
    var headerLeft = <?php echo json_encode($headerConfig['left'] ?? []); ?>;
    var headerRight = <?php echo json_encode($headerConfig['right'] ?? []); ?>;
    var sections = <?php echo json_encode($sectionsForEditor); ?>;
    var footerBlocks = <?php echo json_encode($footerBlocksForEditor); ?>;
    var footerSignatures = <?php echo json_encode($footerSignaturesForEditor); ?>;
    var footerBlockFieldTypes = ['text', 'number', 'date', 'checkbox', 'email', 'phone', 'textarea', 'signature'];
    var footerBlockFieldTypeLabels = { text: 'Metin', number: 'Sayı', date: 'Tarih', checkbox: 'Checkbox', email: 'E-posta', phone: 'Telefon', textarea: 'Çok satır metin', signature: 'İmza' };

    function escapeAttr(s) {
        return (s || '').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
    function makeHeaderItem(field, side, index) {
        var div = document.createElement('div');
        div.className = 'header-field p-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 space-y-3';
        div.dataset.side = side;
        div.dataset.index = index;
        div.innerHTML = '<div class="flex items-center gap-2">' +
            '<input type="text" class="field-label flex-1 min-w-0 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm py-2 px-3 placeholder-gray-500 dark:placeholder-gray-400" placeholder="Örn: Belge No, Tarih" value="' + escapeAttr(field.label) + '">' +
            '<span class="drag-handle cursor-grab shrink-0 text-gray-500 dark:text-white px-1" title="Sürükleyerek sırala">⋮⋮</span>' +
            '<button type="button" class="remove-header-field shrink-0 px-2 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded border border-red-200 dark:border-red-800" title="Bu alanı kaldır">Kaldır</button>' +
            '</div>' +
            '<div class="flex gap-2">' +
            '<select class="field-type flex-1 min-w-0 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm py-2 px-3">' +
            '<option value="text"' + (field.type === 'text' ? ' selected' : '') + '>Metin</option>' +
            '<option value="number"' + (field.type === 'number' ? ' selected' : '') + '>Sayı</option>' +
            '<option value="date"' + (field.type === 'date' ? ' selected' : '') + '>Tarih</option>' +
            '<option value="checkbox"' + (field.type === 'checkbox' ? ' selected' : '') + '>Checkbox</option>' +
            '</select>' +
            '<input type="text" class="field-key flex-1 min-w-0 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm py-2 px-3 placeholder-gray-500 dark:placeholder-gray-400" placeholder="örn: h_left_1" value="' + escapeAttr(field.key) + '">' +
            '</div>';
        return div;
    }

    function renderHeaderLists() {
        var leftList = document.getElementById('header-left-list');
        var rightList = document.getElementById('header-right-list');
        leftList.innerHTML = '';
        rightList.innerHTML = '';
        (headerLeft || []).forEach(function(f, i) {
            leftList.appendChild(makeHeaderItem(f, 'left', i));
        });
        (headerRight || []).forEach(function(f, i) {
            rightList.appendChild(makeHeaderItem(f, 'right', i));
        });
        if (typeof Sortable !== 'undefined') {
            new Sortable(leftList, { animation: 150, handle: '.drag-handle', group: 'header-left', ghostClass: 'opacity-50' });
            new Sortable(rightList, { animation: 150, handle: '.drag-handle', group: 'header-right', ghostClass: 'opacity-50' });
        }
        bindHeaderRemove();
    }

    function bindHeaderRemove() {
        document.querySelectorAll('.remove-header-field').forEach(function(btn) {
            btn.onclick = function() {
                var row = btn.closest('.header-field');
                if (row) row.remove();
            };
        });
    }

    document.getElementById('add-header-left').onclick = function() {
        var list = document.getElementById('header-left-list');
        list.appendChild(makeHeaderItem({ type: 'text', label: '', key: 'h_left_' + (list.children.length + 1) }, 'left', list.children.length));
        bindHeaderRemove();
    };
    document.getElementById('add-header-right').onclick = function() {
        var list = document.getElementById('header-right-list');
        list.appendChild(makeHeaderItem({ type: 'date', label: 'Tarih', key: 'h_right_' + (list.children.length + 1) }, 'right', list.children.length));
        bindHeaderRemove();
    };

    function collectHeaderConfig() {
        var left = [], right = [];
        document.querySelectorAll('#header-left-list .header-field').forEach(function(el) {
            left.push({
                type: el.querySelector('.field-type').value,
                label: el.querySelector('.field-label').value || '',
                key: el.querySelector('.field-key').value || 'h_left_' + left.length
            });
        });
        document.querySelectorAll('#header-right-list .header-field').forEach(function(el) {
            right.push({
                type: el.querySelector('.field-type').value,
                label: el.querySelector('.field-label').value || '',
                key: el.querySelector('.field-key').value || 'h_right_' + right.length
            });
        });
        return { left: left, center: document.getElementById('header-center-logo').checked ? 'logo' : '', right: right };
    }

    function buildFooterSignaturesList() {
        var countEl = document.getElementById('footer-signature-count');
        var list = document.getElementById('footer-signatures-list');
        if (!countEl || !list) return;
        var count = Math.min(5, Math.max(2, parseInt(countEl.value, 10) || 2));
        countEl.value = count;
        list.innerHTML = '';
        for (var i = 0; i < count; i++) {
            var label = (footerSignatures[i] && footerSignatures[i].label) ? footerSignatures[i].label : (i === 0 ? 'Emlak İşletmesi İmzası' : (i === 1 ? 'Müşteri İmzası' : (i + 1) + '. Taraf İmzası'));
            var div = document.createElement('div');
            div.className = 'flex items-center gap-2 footer-signature-row';
            div.dataset.index = i;
            div.innerHTML = '<span class="text-sm text-gray-500 dark:text-white/80 w-8">' + (i + 1) + '.</span><input type="text" class="footer-signature-label flex-1 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" placeholder="Örn: Emlak İşletmesi İmzası" value="' + escapeAttr(label) + '">';
            list.appendChild(div);
        }
    }
    function collectFooterConfig() {
        var signatures = [];
        document.querySelectorAll('#footer-signatures-list .footer-signature-label').forEach(function(inp) {
            signatures.push({ label: (inp.value || '').trim() || 'İmza' });
        });
        if (signatures.length === 0) signatures = [{ label: 'Emlak İşletmesi İmzası' }, { label: 'Müşteri İmzası' }];
        return {
            description_label: 'Açıklama',
            description_key: 'footer_description',
            signature_label: signatures[0] ? signatures[0].label : 'İmza',
            signature_count: signatures.length,
            signatures: signatures,
            blocks: []
        };
    }

    function makeFooterBlockFieldEl(field, blockIdx, fieldIdx) {
        var div = document.createElement('div');
        div.className = 'footer-block-field-item flex flex-wrap items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800';
        div.dataset.block = blockIdx;
        div.dataset.fieldIndex = fieldIdx;
        var typeOpts = footerBlockFieldTypes.map(function(t) {
            return '<option value="' + t + '"' + (field.type === t ? ' selected' : '') + '>' + (footerBlockFieldTypeLabels[t] || t) + '</option>';
        }).join('');
        div.innerHTML = '<input type="text" class="footer-block-field-label flex-1 min-w-[120px] px-2 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" placeholder="Etiket" value="' + escapeAttr(field.label) + '">' +
            '<select class="footer-block-field-type px-2 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">' + typeOpts + '</select>' +
            '<input type="text" class="footer-block-field-key flex-1 min-w-[100px] px-2 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" placeholder="key" value="' + escapeAttr(field.key) + '">' +
            '<button type="button" class="footer-block-field-remove text-red-600 dark:text-red-400 text-sm hover:underline">Kaldır</button>';
        return div;
    }
    function bindFooterBlockFieldRemove() {
        document.querySelectorAll('.footer-block-field-remove').forEach(function(btn) {
            btn.onclick = function() { btn.closest('.footer-block-field-item').remove(); };
        });
    }
    function renderFooterBlocksList() {
        var list = document.getElementById('footer-blocks-list');
        if (!list) return;
        list.innerHTML = '';
        footerBlocks.forEach(function(blk, bi) {
            var card = document.createElement('div');
            card.className = 'footer-block-card border border-gray-200 dark:border-gray-600 rounded-xl p-5 bg-white dark:bg-gray-800 min-w-0';
            card.dataset.block = bi;
            card.innerHTML = '<div class="flex items-center justify-between mb-3"><label class="text-sm font-semibold text-gray-700 dark:text-white">Blok ' + (bi + 1) + '</label><button type="button" class="footer-block-remove px-2 py-1 text-sm text-red-600 dark:text-red-400 hover:underline">Blok sil</button></div>' +
                '<div class="footer-block-fields space-y-3 min-h-[48px]" data-block="' + bi + '"></div>' +
                '<button type="button" class="footer-block-add-field mt-3 px-3 py-2 text-sm text-primary dark:text-white hover:bg-primary/10 dark:hover:bg-primary/20 rounded-lg border border-primary/30">+ Alan ekle</button>';
            list.appendChild(card);
            var fieldsList = card.querySelector('.footer-block-fields');
            (blk.fields || []).forEach(function(f, fi) {
                fieldsList.appendChild(makeFooterBlockFieldEl(f, bi, fi));
            });
            card.querySelector('.footer-block-add-field').onclick = function() {
                var listEl = this.closest('.footer-block-card').querySelector('.footer-block-fields');
                var idx = listEl.children.length;
                var blockIdx = parseInt(this.closest('.footer-block-card').dataset.block, 10);
                listEl.appendChild(makeFooterBlockFieldEl({ type: 'text', label: '', key: 'footer_b' + blockIdx + '_f' + idx }, blockIdx, idx));
                bindFooterBlockFieldRemove();
            };
            card.querySelector('.footer-block-remove').onclick = function() {
                var bi = parseInt(this.closest('.footer-block-card').dataset.block, 10);
                footerBlocks.splice(bi, 1);
                renderFooterBlocksList();
            };
        });
        bindFooterBlockFieldRemove();
    }

    // --- Sections ---
    var sectionCellTypes = ['text', 'number', 'date', 'checkbox', 'signature', 'label'];
    var sectionCellTypeLabels = { text: 'Metin', number: 'Sayı', date: 'Tarih', checkbox: 'Checkbox', signature: 'İmza', label: 'Etiket' };
    var blockFieldTypes = ['text', 'number', 'date', 'checkbox', 'email', 'phone', 'textarea'];
    var blockFieldTypeLabels = { text: 'Metin', number: 'Sayı', date: 'Tarih', checkbox: 'Checkbox', email: 'E-posta', phone: 'Telefon', textarea: 'Çok satır metin' };

    function renderSectionsList() {
        var list = document.getElementById('sections-list');
        list.innerHTML = '';
        sections.forEach(function(sec, i) {
            var card = document.createElement('div');
            card.className = 'section-card flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800';
            card.dataset.index = i;
            var title = (sec.title || ('Bölüm ' + (i + 1)));
            var typeLabel = (sec.type === 'block') ? 'Blok' : 'Tablo';
            card.innerHTML = '<span class="drag-handle cursor-grab text-gray-500 dark:text-white px-1">⋮⋮</span>' +
                '<span class="flex-1 font-medium text-gray-900 dark:text-white">' + escapeAttr(title) + '</span>' +
                '<span class="px-2 py-0.5 rounded text-xs ' + (sec.type === 'block' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300') + '">' + typeLabel + '</span>' +
                '<button type="button" class="section-edit px-2 py-1 text-sm text-primary dark:text-white hover:underline">Düzenle</button>' +
                '<button type="button" class="section-remove px-2 py-1 text-sm text-red-600 dark:text-red-400 hover:underline">Sil</button>';
            list.appendChild(card);
        });
        if (typeof Sortable !== 'undefined') {
            new Sortable(list, { animation: 150, handle: '.drag-handle', ghostClass: 'opacity-50', onEnd: function(evt) {
                var item = sections.splice(evt.oldIndex, 1)[0];
                sections.splice(evt.newIndex, 0, item);
            }});
        }
        list.querySelectorAll('.section-edit').forEach(function(btn) {
            btn.onclick = function() { openSectionModal(parseInt(btn.closest('.section-card').dataset.index, 10)); };
        });
        list.querySelectorAll('.section-remove').forEach(function(btn) {
            btn.onclick = function() {
                var idx = parseInt(btn.closest('.section-card').dataset.index, 10);
                sections.splice(idx, 1);
                renderSectionsList();
            };
        });
    }

    var sectionModalEditingIndex = -1;
    var modal = document.getElementById('section-modal');
    var modalBackdrop = document.getElementById('section-modal-backdrop');
    var modalTitleInput = document.getElementById('section-modal-title-input');
    var tableOpts = document.getElementById('section-modal-table-options');
    var blockOpts = document.getElementById('section-modal-block-options');

    function getModalType() {
        var r = document.querySelector('input[name="section-modal-type"]:checked');
        return r ? r.value : 'table';
    }
    function setModalType(t) {
        var r = document.querySelector('input[name="section-modal-type"][value="' + t + '"]');
        if (r) r.checked = true;
        tableOpts.classList.toggle('hidden', t !== 'table');
        blockOpts.classList.toggle('hidden', t !== 'block');
        if (t === 'table') buildSectionTableOptions();
        else buildSectionBlockOptions();
    }
    document.querySelectorAll('input[name="section-modal-type"]').forEach(function(r) {
        r.onchange = function() { setModalType(getModalType()); };
    });

    function buildSectionTableOptions() {
        var blockCountEl = document.getElementById('section-table-block-count');
        var blockCount = blockCountEl ? (parseInt(blockCountEl.value, 10) || 1) : 1;
        blockCount = Math.max(1, Math.min(6, blockCount));
        if (blockCountEl) blockCountEl.value = blockCount;

        var singleWrap = document.getElementById('section-table-single');
        var multiWrap = document.getElementById('section-table-multi');
        if (singleWrap) singleWrap.classList.toggle('hidden', blockCount > 1);
        if (multiWrap) multiWrap.classList.toggle('hidden', blockCount <= 1);

        var sec = sectionModalEditingIndex >= 0 ? sections[sectionModalEditingIndex] : null;
        var tableBlocks = (sec && sec.table_blocks && Array.isArray(sec.table_blocks)) ? sec.table_blocks : null;
        if (blockCount > 1) {
            buildTableBlocksList(blockCount, tableBlocks);
            return;
        }

        var rows = parseInt(document.getElementById('section-table-rows').value, 10) || 5;
        var cols = parseInt(document.getElementById('section-table-cols').value, 10) || 3;
        var rowLabels = [];
        var headers = [];
        var cells = [];
        if (tableBlocks && tableBlocks.length > 0) {
            var b0 = tableBlocks[0];
            rows = (b0.rows != null) ? b0.rows : rows;
            cols = (b0.cols != null) ? b0.cols : cols;
            rowLabels = b0.row_labels || [];
            headers = b0.headers || [];
            cells = b0.cells || [];
        } else if (sec) {
            rowLabels = sec.row_labels || [];
            headers = sec.headers || [];
            cells = sec.cells || [];
        }
        var ta = document.getElementById('section-table-row-labels-ta');
        if (ta && (sectionModalEditingIndex >= 0 || rowLabels.length > 0)) {
            ta.value = rowLabels.filter(Boolean).join('\n');
        }
        var headersWrap = document.getElementById('section-table-headers');
        if (headersWrap) {
            headersWrap.innerHTML = '';
            for (var c = 0; c < cols; c++) {
                var inp = document.createElement('input');
                inp.type = 'text';
                inp.className = 'section-table-header px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm min-w-[140px]';
                inp.placeholder = 'Sütun ' + (c + 1);
                inp.dataset.col = c;
                inp.value = headers[c] != null ? headers[c] : '';
                inp.id = 'section-table-header-' + c;
                var label = document.createElement('label');
                label.className = 'block text-xs text-gray-500 dark:text-gray-400 mb-1';
                label.htmlFor = inp.id;
                label.textContent = 'Sütun ' + (c + 1);
                var wrap = document.createElement('div');
                wrap.className = 'inline-block';
                wrap.appendChild(label);
                wrap.appendChild(inp);
                headersWrap.appendChild(wrap);
            }
        }
        var rowTypesWrap = document.getElementById('section-table-row-types');
        if (rowTypesWrap) {
            rowTypesWrap.innerHTML = '';
            var labelsFromTa = (document.getElementById('section-table-row-labels-ta').value || '').split('\n').map(function(s) { return s.trim(); });
            var labelCount = labelsFromTa.filter(Boolean).length;
            var rowsToShow = Math.max(rows, labelCount, 1);
            rowsToShow = Math.min(30, rowsToShow);
            if (rowsToShow !== rows && document.getElementById('section-table-rows')) {
                document.getElementById('section-table-rows').value = rowsToShow;
                rows = rowsToShow;
            } else {
                rows = rowsToShow;
            }
            for (var r = 0; r < rows; r++) {
                var rowLabel = labelsFromTa[r] || rowLabels[r] || ('Satır ' + (r + 1));
                var rowType = 'text';
                if (cells[r]) rowType = Array.isArray(cells[r]) ? (cells[r][0] || 'text') : 'text';
                var rowDiv = document.createElement('div');
                rowDiv.className = 'flex items-center gap-4 p-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800';
                rowDiv.dataset.row = r;
                var labelSpan = document.createElement('span');
                labelSpan.className = 'flex-1 min-w-0 text-sm font-medium text-gray-700 dark:text-gray-300 truncate';
                labelSpan.title = rowLabel;
                labelSpan.textContent = rowLabel;
                var sel = document.createElement('select');
                sel.className = 'section-row-type-select w-40 shrink-0 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm';
                sel.dataset.row = r;
                sectionCellTypes.forEach(function(t) {
                    var opt = document.createElement('option');
                    opt.value = t;
                    opt.textContent = sectionCellTypeLabels[t];
                    if (rowType === t) opt.selected = true;
                    sel.appendChild(opt);
                });
                rowDiv.appendChild(labelSpan);
                rowDiv.appendChild(sel);
                rowTypesWrap.appendChild(rowDiv);
            }
        }
    }

    function buildTableBlocksList(blockCount, tableBlocks) {
        var list = document.getElementById('section-table-blocks-list');
        if (!list) return;
        list.innerHTML = '';
        for (var b = 0; b < blockCount; b++) {
            var blk = (tableBlocks && tableBlocks[b]) ? tableBlocks[b] : {};
            var rows = parseInt(blk.rows, 10) || 3;
            var cols = parseInt(blk.cols, 10) || 2;
            var rowLabels = blk.row_labels || [];
            var headers = blk.headers || [];
            var cells = blk.cells || [];
            var card = document.createElement('div');
            card.className = 'section-table-block-card rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800/50 p-5';
            card.dataset.block = b;
            card.innerHTML = '<h4 class="text-sm font-semibold text-gray-800 dark:text-white mb-4">Blok ' + (b + 1) + '</h4>' +
                '<div class="grid grid-cols-2 gap-4 mb-4">' +
                '<div><label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Satır sayısı</label><input type="number" class="section-table-block-rows w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" min="1" max="30" value="' + rows + '" data-block="' + b + '"></div>' +
                '<div><label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Sütun sayısı</label><input type="number" class="section-table-block-cols w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" min="1" max="15" value="' + cols + '" data-block="' + b + '"></div>' +
                '</div>' +
                '<div class="mb-4"><label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Satır etiketleri (her satır ayrı)</label><textarea class="section-table-block-row-labels w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm font-mono" rows="4" data-block="' + b + '">' + escapeAttr((rowLabels || []).filter(Boolean).join('\n')) + '</textarea></div>' +
                '<div class="mb-4"><label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Sütun başlıkları</label><div class="section-table-block-headers flex flex-wrap gap-2" data-block="' + b + '"></div></div>' +
                '<div><label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Hücre tipleri (satır bazlı)</label><div class="section-table-block-row-types space-y-2 max-h-48 overflow-y-auto" data-block="' + b + '"></div></div>';
            list.appendChild(card);
            var headersWrap = card.querySelector('.section-table-block-headers');
            for (var c = 0; c < cols; c++) {
                var inp = document.createElement('input');
                inp.type = 'text';
                inp.className = 'section-table-block-header px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm min-w-[100px]';
                inp.placeholder = 'Başlık ' + (c + 1);
                inp.dataset.col = c;
                inp.value = headers[c] != null ? headers[c] : '';
                inp.dataset.block = b;
                headersWrap.appendChild(inp);
            }
            var rowTypesWrap = card.querySelector('.section-table-block-row-types');
            var labelsFromBlk = (rowLabels || []).slice();
            for (var r = 0; r < rows; r++) {
                var rowLabel = labelsFromBlk[r] || ('Satır ' + (r + 1));
                var rowType = 'text';
                if (cells[r] && Array.isArray(cells[r])) rowType = cells[r][0] || 'text';
                var rowDiv = document.createElement('div');
                rowDiv.className = 'flex items-center gap-2 p-2 rounded border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800';
                rowDiv.dataset.row = r;
                rowDiv.dataset.block = b;
                var labelSpan = document.createElement('span');
                labelSpan.className = 'flex-1 min-w-0 text-sm text-gray-700 dark:text-gray-300 truncate';
                labelSpan.textContent = rowLabel;
                var sel = document.createElement('select');
                sel.className = 'section-table-block-row-type w-32 shrink-0 px-2 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm';
                sel.dataset.row = r;
                sel.dataset.block = b;
                sectionCellTypes.forEach(function(t) {
                    var opt = document.createElement('option');
                    opt.value = t;
                    opt.textContent = sectionCellTypeLabels[t];
                    if (rowType === t) opt.selected = true;
                    sel.appendChild(opt);
                });
                rowDiv.appendChild(labelSpan);
                rowDiv.appendChild(sel);
                rowTypesWrap.appendChild(rowDiv);
            }
            (function(blockIdx) {
                card.querySelector('.section-table-block-rows').addEventListener('input', function() { refreshBlockCard(blockIdx); });
                card.querySelector('.section-table-block-cols').addEventListener('input', function() { refreshBlockCard(blockIdx); });
                card.querySelector('.section-table-block-row-labels').addEventListener('input', function() { refreshBlockCard(blockIdx); });
            })(b);
        }
    }

    function refreshBlockCard(blockIdx) {
        var list = document.getElementById('section-table-blocks-list');
        if (!list) return;
        var card = list.querySelector('.section-table-block-card[data-block="' + blockIdx + '"]');
        if (!card) return;
        var blk = (sectionModalEditingIndex >= 0 && sections[sectionModalEditingIndex].table_blocks && sections[sectionModalEditingIndex].table_blocks[blockIdx]) ? sections[sectionModalEditingIndex].table_blocks[blockIdx] : {};
        var rows = parseInt(card.querySelector('.section-table-block-rows').value, 10) || 3;
        var cols = parseInt(card.querySelector('.section-table-block-cols').value, 10) || 2;
        var ta = card.querySelector('.section-table-block-row-labels');
        var labelsFromTa = (ta && ta.value) ? ta.value.split('\n').map(function(s) { return s.trim(); }) : [];
        var rowLabels = blk.row_labels || [];
        var headers = blk.headers || [];
        var cells = blk.cells || [];
        var headersWrap = card.querySelector('.section-table-block-headers');
        headersWrap.innerHTML = '';
        for (var c = 0; c < cols; c++) {
            var inp = document.createElement('input');
            inp.type = 'text';
            inp.className = 'section-table-block-header px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm min-w-[100px]';
            inp.placeholder = 'Başlık ' + (c + 1);
            inp.dataset.col = c;
            inp.dataset.block = blockIdx;
            inp.value = headers[c] != null ? headers[c] : '';
            headersWrap.appendChild(inp);
        }
        var rowsToShow = Math.max(rows, labelsFromTa.filter(Boolean).length, 1);
        rowsToShow = Math.min(30, rowsToShow);
        card.querySelector('.section-table-block-rows').value = rowsToShow;
        var rowTypesWrap = card.querySelector('.section-table-block-row-types');
        rowTypesWrap.innerHTML = '';
        for (var r = 0; r < rowsToShow; r++) {
            var rowLabel = labelsFromTa[r] || rowLabels[r] || ('Satır ' + (r + 1));
            var rowType = 'text';
            if (cells[r] && Array.isArray(cells[r])) rowType = cells[r][0] || 'text';
            var rowDiv = document.createElement('div');
            rowDiv.className = 'flex items-center gap-2 p-2 rounded border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800';
            rowDiv.dataset.row = r;
            rowDiv.dataset.block = blockIdx;
            var labelSpan = document.createElement('span');
            labelSpan.className = 'flex-1 min-w-0 text-sm text-gray-700 dark:text-gray-300 truncate';
            labelSpan.textContent = rowLabel;
            var sel = document.createElement('select');
            sel.className = 'section-table-block-row-type w-32 shrink-0 px-2 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm';
            sel.dataset.row = r;
            sel.dataset.block = blockIdx;
            sectionCellTypes.forEach(function(t) {
                var opt = document.createElement('option');
                opt.value = t;
                opt.textContent = sectionCellTypeLabels[t];
                if (rowType === t) opt.selected = true;
                sel.appendChild(opt);
            });
            rowDiv.appendChild(labelSpan);
            rowDiv.appendChild(sel);
            rowTypesWrap.appendChild(rowDiv);
        }
    }
    function updateTableRowTypeLabelsFromTextarea() {
        var rowTypesWrap = document.getElementById('section-table-row-types');
        if (!rowTypesWrap) return;
        var labelsFromTa = (document.getElementById('section-table-row-labels-ta').value || '').split('\n').map(function(s) { return s.trim(); });
        var rows = rowTypesWrap.querySelectorAll('[data-row]');
        rows.forEach(function(rowDiv) {
            var r = parseInt(rowDiv.dataset.row, 10);
            var labelSpan = rowDiv.querySelector('span');
            if (labelSpan) labelSpan.textContent = labelsFromTa[r] || ('Satır ' + (r + 1));
        });
    }
    function syncTableRowsFromRowLabels() {
        var ta = document.getElementById('section-table-row-labels-ta');
        var rowsInput = document.getElementById('section-table-rows');
        if (!ta || !rowsInput) return;
        var lines = (ta.value || '').split('\n').filter(function(s) { return s.trim().length > 0; });
        var count = Math.max(1, Math.min(30, lines.length));
        if (lines.length > 0 && count !== parseInt(rowsInput.value, 10)) {
            rowsInput.value = count;
            buildSectionTableOptions();
        }
    }

    function buildSectionBlockOptions() {
        var count = parseInt(document.getElementById('section-block-count').value, 10) || 2;
        var sec = sectionModalEditingIndex >= 0 ? sections[sectionModalEditingIndex] : null;
        var blocks = (sec && sec.blocks) ? sec.blocks : [];
        while (blocks.length < count) blocks.push({ fields: [] });
        var wrap = document.getElementById('section-block-blocks');
        wrap.innerHTML = '';
        for (var b = 0; b < count; b++) {
            (function(blockIdx) {
                var blk = blocks[blockIdx] || { fields: [] };
                var card = document.createElement('div');
                card.className = 'section-block-card border border-gray-200 dark:border-gray-600 rounded-xl p-5 bg-gray-50 dark:bg-gray-800/50 min-w-0';
                card.dataset.block = blockIdx;
                card.innerHTML = '<label class="block text-sm font-semibold text-gray-700 dark:text-white mb-3">Blok ' + (blockIdx + 1) + '</label>' +
                    '<div class="section-block-fields space-y-3 min-h-[48px]" data-block="' + blockIdx + '"></div>' +
                    '<button type="button" class="section-block-add-field mt-3 px-3 py-2 text-sm text-primary dark:text-white hover:bg-primary/10 dark:hover:bg-primary/20 rounded-lg border border-primary/30 transition-colors">+ Alan ekle</button>';
                wrap.appendChild(card);
                var fieldsList = card.querySelector('.section-block-fields');
                (blk.fields || []).forEach(function(f) {
                    fieldsList.appendChild(makeBlockFieldEl(f, blockIdx, fieldsList.children.length));
                });
                card.querySelector('.section-block-add-field').onclick = function() {
                    var list = this.closest('.section-block-card').querySelector('.section-block-fields');
                    var idx = list.children.length;
                    // Section index'i dahil et (yeni bölüm için sections.length, düzenleme için sectionModalEditingIndex)
                    var secIdx = sectionModalEditingIndex >= 0 ? sectionModalEditingIndex : sections.length;
                    list.appendChild(makeBlockFieldEl({ type: 'text', label: '', key: 'sec' + secIdx + '_b' + blockIdx + '_f' + idx }, blockIdx, idx));
                    bindBlockFieldRemove();
                };
            })(b);
        }
        bindBlockFieldRemove();
    }

    function makeBlockFieldEl(field, blockIndex, fieldIndex) {
        var div = document.createElement('div');
        div.className = 'block-field-item flex flex-wrap items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800';
        div.dataset.block = blockIndex;
        div.dataset.fieldIndex = fieldIndex;
        var typeOpts = blockFieldTypes.map(function(t) {
            return '<option value="' + t + '"' + (field.type === t ? ' selected' : '') + '>' + (blockFieldTypeLabels[t] || t) + '</option>';
        }).join('');
        div.innerHTML = '<input type="text" class="block-field-label flex-1 min-w-[120px] px-2 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" placeholder="Etiket" value="' + escapeAttr(field.label) + '">' +
            '<select class="block-field-type px-2 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">' + typeOpts + '</select>' +
            '<input type="text" class="block-field-key flex-1 min-w-[100px] px-2 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" placeholder="key" value="' + escapeAttr(field.key) + '">' +
            '<button type="button" class="block-field-remove text-red-600 dark:text-red-400 text-sm hover:underline">Kaldır</button>';
        return div;
    }
    function bindBlockFieldRemove() {
        document.querySelectorAll('.block-field-remove').forEach(function(btn) {
            btn.onclick = function() { btn.closest('.block-field-item').remove(); };
        });
    }

    document.getElementById('section-table-rows').addEventListener('input', buildSectionTableOptions);
    document.getElementById('section-table-cols').addEventListener('input', buildSectionTableOptions);
    document.getElementById('section-table-row-labels-ta').addEventListener('input', function() {
        syncTableRowsFromRowLabels();
        updateTableRowTypeLabelsFromTextarea();
    });
    var tableBlockCountEl = document.getElementById('section-table-block-count');
    if (tableBlockCountEl) {
        tableBlockCountEl.addEventListener('input', function() {
            var v = parseInt(this.value, 10);
            if (v >= 1 && v <= 6) buildSectionTableOptions();
        });
    }
    document.getElementById('section-block-count').addEventListener('input', function() {
        var v = parseInt(this.value, 10);
        if (v >= 1 && v <= 4) buildSectionBlockOptions();
    });

    function openSectionModal(editIndex) {
        sectionModalEditingIndex = editIndex >= 0 ? editIndex : -1;
        document.getElementById('section-modal-title').textContent = sectionModalEditingIndex >= 0 ? 'Bölümü Düzenle' : 'Bölüm Ekle';
        if (sectionModalEditingIndex >= 0) {
            var sec = sections[sectionModalEditingIndex];
            modalTitleInput.value = sec.title || '';
            setModalType(sec.type || 'table');
            if (sec.type === 'table') {
                var tblBlocks = sec.table_blocks;
                var blockCount = (tblBlocks && Array.isArray(tblBlocks) && tblBlocks.length > 0) ? tblBlocks.length : 1;
                var bcEl = document.getElementById('section-table-block-count');
                if (bcEl) bcEl.value = Math.min(6, Math.max(1, blockCount));
                if (blockCount === 1) {
                    var b0 = (tblBlocks && tblBlocks[0]) ? tblBlocks[0] : sec;
                    document.getElementById('section-table-rows').value = b0.rows != null ? b0.rows : (sec.rows || 5);
                    document.getElementById('section-table-cols').value = b0.cols != null ? b0.cols : (sec.cols || 3);
                    document.getElementById('section-table-theme').value = sec.theme_color || '#4472C4';
                } else {
                    document.getElementById('section-table-rows').value = 5;
                    document.getElementById('section-table-cols').value = 3;
                    document.getElementById('section-table-theme').value = sec.theme_color || '#4472C4';
                }
            } else {
                document.getElementById('section-block-count').value = (sec.blocks && sec.blocks.length) ? sec.blocks.length : 2;
                document.getElementById('section-block-theme').value = sec.theme_color || '#4472C4';
            }
        } else {
            modalTitleInput.value = '';
            setModalType('block');
            var bcEl = document.getElementById('section-table-block-count');
            if (bcEl) bcEl.value = 1;
            document.getElementById('section-table-rows').value = 5;
            document.getElementById('section-table-cols').value = 3;
            document.getElementById('section-table-theme').value = '#4472C4';
            document.getElementById('section-table-row-labels-ta').value = '';
            document.getElementById('section-block-count').value = 2;
            document.getElementById('section-block-theme').value = '#4472C4';
        }
        modal.classList.remove('hidden');
    }
    function closeSectionModal() {
        modal.classList.add('hidden');
        sectionModalEditingIndex = -1;
    }
    modalBackdrop.onclick = closeSectionModal;
    document.getElementById('section-modal-cancel').onclick = closeSectionModal;

    document.getElementById('section-modal-save').onclick = function() {
        var title = modalTitleInput.value.trim() || 'Bölüm';
        var type = getModalType();
        var themeColor = type === 'table' ? document.getElementById('section-table-theme').value : document.getElementById('section-block-theme').value;
        if (type === 'table') {
            var blockCountEl = document.getElementById('section-table-block-count');
            var blockCount = blockCountEl ? (parseInt(blockCountEl.value, 10) || 1) : 1;
            blockCount = Math.max(1, Math.min(6, blockCount));
            var tableBlocks = [];
            if (blockCount === 1) {
                var rows = parseInt(document.getElementById('section-table-rows').value, 10) || 5;
                var cols = parseInt(document.getElementById('section-table-cols').value, 10) || 3;
                var rowLabelsTa = (document.getElementById('section-table-row-labels-ta').value || '').split('\n').map(function(s) { return s.trim(); });
                var rowLabels = [];
                for (var r = 0; r < rows; r++) rowLabels[r] = rowLabelsTa[r] || ('Satır ' + (r + 1));
                var headers = [];
                document.querySelectorAll('.section-table-header').forEach(function(inp) {
                    headers[parseInt(inp.dataset.col, 10)] = inp.value || '';
                });
                var cells = [];
                document.querySelectorAll('.section-row-type-select').forEach(function(sel) {
                    var r = parseInt(sel.dataset.row, 10);
                    var cellType = sel.value;
                    cells[r] = [];
                    for (var c = 0; c < cols; c++) cells[r][c] = cellType;
                });
                tableBlocks = [{ rows: rows, cols: cols, row_labels: rowLabels, headers: headers, cells: cells }];
            } else {
                var blocksList = document.getElementById('section-table-blocks-list');
                if (blocksList) {
                    blocksList.querySelectorAll('.section-table-block-card').forEach(function(card) {
                        var b = parseInt(card.dataset.block, 10);
                        var rows = parseInt(card.querySelector('.section-table-block-rows').value, 10) || 3;
                        var cols = parseInt(card.querySelector('.section-table-block-cols').value, 10) || 2;
                        var ta = card.querySelector('.section-table-block-row-labels');
                        var rowLabelsTa = (ta && ta.value) ? ta.value.split('\n').map(function(s) { return s.trim(); }) : [];
                        var rowLabels = [];
                        for (var r = 0; r < rows; r++) rowLabels[r] = rowLabelsTa[r] || ('Satır ' + (r + 1));
                        var headers = [];
                        card.querySelectorAll('.section-table-block-header').forEach(function(inp) {
                            headers[parseInt(inp.dataset.col, 10)] = inp.value || '';
                        });
                        var cells = [];
                        card.querySelectorAll('.section-table-block-row-type').forEach(function(sel) {
                            var r = parseInt(sel.dataset.row, 10);
                            var cellType = sel.value;
                            if (!cells[r]) cells[r] = [];
                            for (var c = 0; c < cols; c++) cells[r][c] = cellType;
                        });
                        tableBlocks.push({ rows: rows, cols: cols, row_labels: rowLabels, headers: headers, cells: cells });
                    });
                }
            }
            var newSec = { type: 'table', title: title, theme_color: themeColor, table_blocks: tableBlocks };
            if (sectionModalEditingIndex >= 0 && sections[sectionModalEditingIndex].legacy_keys) newSec.legacy_keys = true;
            if (sectionModalEditingIndex >= 0) sections[sectionModalEditingIndex] = newSec;
            else sections.push(newSec);
        } else {
            var blockCount = parseInt(document.getElementById('section-block-count').value, 10) || 2;
            var blocks = [];
            // Yeni section için index belirle
            var secIdx = sectionModalEditingIndex >= 0 ? sectionModalEditingIndex : sections.length;
            for (var b = 0; b < blockCount; b++) {
                var fieldEls = document.querySelectorAll('.section-block-fields[data-block="' + b + '"] .block-field-item');
                var fields = [];
                fieldEls.forEach(function(el) {
                    var key = el.querySelector('.block-field-key').value || '';
                    // Key yoksa veya eski formatta ise yeni benzersiz key oluştur (section index dahil)
                    if (!key || key.match(/^sec_b\d+_f\d+$/)) {
                        key = 'sec' + secIdx + '_b' + b + '_f' + fields.length;
                    }
                    fields.push({
                        type: el.querySelector('.block-field-type').value,
                        label: el.querySelector('.block-field-label').value || '',
                        key: key
                    });
                });
                blocks.push({ fields: fields });
            }
            var newSec = { type: 'block', title: title, theme_color: themeColor, blocks: blocks };
            if (sectionModalEditingIndex >= 0) sections[sectionModalEditingIndex] = newSec;
            else sections.push(newSec);
        }
        renderSectionsList();
        closeSectionModal();
    };

    document.getElementById('section-add-btn').onclick = function() { openSectionModal(-1); };

    function collectSectionsConfig() {
        return { sections: sections };
    }

    var footerBlockAddBtn = document.getElementById('footer-block-add-btn');
    if (footerBlockAddBtn) {
        footerBlockAddBtn.onclick = function() {
            footerBlocks.push({ fields: [] });
            renderFooterBlocksList();
        };
    }

    document.getElementById('template-form').addEventListener('submit', function() {
        document.getElementById('header_config').value = JSON.stringify(collectHeaderConfig());
        document.getElementById('table_config').value = JSON.stringify(collectSectionsConfig());
        document.getElementById('footer_config').value = JSON.stringify(collectFooterConfig());
    });

    function slugify(text) {
        var map = { 'ş':'s','Ş':'s','ı':'i','İ':'i','ğ':'g','Ğ':'g','ü':'u','Ü':'u','ö':'o','Ö':'o','ç':'c','Ç':'c','â':'a','Â':'a','î':'i','Î':'i','û':'u','Û':'u' };
        var r = (text || '');
        for (var k in map) r = r.split(k).join(map[k]);
        return r.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').replace(/-+/g, '-');
    }
    var nameInput = document.getElementById('template_name');
    var slugInput = document.getElementById('template_slug');
    var userEditedSlug = slugInput.value !== '';
    slugInput.addEventListener('input', function() { userEditedSlug = this.value.trim() !== ''; });
    nameInput.addEventListener('input', function() { if (!userEditedSlug) slugInput.value = slugify(this.value); });

    var footerCountEl = document.getElementById('footer-signature-count');
    if (footerCountEl) {
        footerCountEl.addEventListener('input', buildFooterSignaturesList);
    }
    buildFooterSignaturesList();
    renderSectionsList();
    renderHeaderLists();
    if (document.getElementById('footer-blocks-list')) renderFooterBlocksList();
})();
</script>
<?php include $rootPath . '/app/views/admin/snippets/footer.php'; ?>
