<?php
/**
 * Komisyon Sözleşmeleri Modülü Controller
 */

class CommissionContractsController {

    private $model;
    private $moduleInfo;

    public function __construct() {
        require_once __DIR__ . '/Model.php';
        $this->model = new CommissionContractsModel();
    }

    public function setModuleInfo($info) {
        $this->moduleInfo = $info;
    }

    public function onLoad() {}

    public function onActivate() {
        $this->model->createTable();
        require_once __DIR__ . '/ContractTemplateModel.php';
        $templateModel = new ContractTemplateModel();
        $templateModel->createTable();
        $this->seedDefaultTemplate($templateModel);
    }

    /**
     * Varsayılan "Komisyon Sözleşmesi" şablonunu yoksa ekle
     */
    private function seedDefaultTemplate(ContractTemplateModel $templateModel) {
        if ($templateModel->findBySlug('komisyon-sozlesmesi')) {
            return;
        }
        $templateModel->create([
            'name' => 'Komisyon Sözleşmesi',
            'slug' => 'komisyon-sozlesmesi',
            'header_config' => [
                'left' => [
                    ['type' => 'text', 'label' => 'Belge No', 'key' => 'h_left_1'],
                ],
                'center' => 'logo',
                'right' => [
                    ['type' => 'date', 'label' => 'Tarih', 'key' => 'h_right_1'],
                ],
            ],
            'table_config' => [
                'rows' => 5,
                'cols' => 7,
                'theme_color' => '#4472C4',
                'headers' => ['#', 'Taşınmaz Açıklaması', 'Tip', 'Fiyat / Aylık Kira (₺)', 'Komisyon', 'KDV', 'Komisyon Tutarı (₺)'],
                'cells' => [
                    ['label', 'text', 'text', 'number', 'text', 'number', 'number'],
                    ['label', 'text', 'text', 'number', 'text', 'number', 'number'],
                    ['label', 'text', 'text', 'number', 'text', 'number', 'number'],
                    ['label', 'text', 'text', 'number', 'text', 'number', 'number'],
                    ['label', 'text', 'text', 'number', 'text', 'number', 'number'],
                ],
            ],
            'footer_config' => [
                'description_label' => 'Açıklama',
                'description_key' => 'footer_description',
                'signature_label' => 'Müşteri İmzası',
                'signature_count' => 1,
            ],
        ]);
    }

    public function onDeactivate() {}

    private function requireLogin() {
        if (!is_user_logged_in()) {
            header('Location: ' . admin_url('login'));
            exit;
        }
    }

    private function checkPermission($permission) {
        $this->requireLogin();
        if (!current_user_can($permission)) {
            $_SESSION['error_message'] = 'Bu işlem için yetkiniz bulunmamaktadır!';
            header('Location: ' . admin_url('dashboard'));
            exit;
        }
    }

    private function adminView($view, $data = []) {
        $data['rootPath'] = dirname(dirname(__DIR__));
        extract($data);
        $viewPath = __DIR__ . '/views/admin/' . $view . '.php';
        if (!file_exists($viewPath)) {
            die("View not found: $viewPath");
        }
        include $viewPath;
    }

    /**
     * Frontend view render (login gerekmez)
     */
    private function frontendView($view, $data = []) {
        $data['rootPath'] = dirname(dirname(__DIR__));
        extract($data);
        $viewPath = __DIR__ . '/views/frontend/' . $view . '.php';
        if (!file_exists($viewPath)) {
            header('HTTP/1.0 404 Not Found');
            die('Sayfa bulunamadı');
        }
        include $viewPath;
    }

    /**
     * POST'tan firma bilgilerini kaydet
     */
    private function saveCompanyInfoToSettings($post) {
        $settings = get_option('site_settings', []);
        
        if (isset($post['company_person_field'])) {
            $settings['company_person'] = trim((string) $post['company_person_field']);
        }
        if (isset($post['company_name_field'])) {
            $settings['company_name'] = trim((string) $post['company_name_field']);
        }
        if (isset($post['company_address_field'])) {
            $settings['company_address'] = trim((string) $post['company_address_field']);
        }
        if (isset($post['company_phone_field'])) {
            $settings['company_phone'] = trim((string) $post['company_phone_field']);
        }
        
        update_option('site_settings', $settings);
    }

    /**
     * POST'tan taşınmaz kalemlerini topla (komisyon hesaplama kullanılmıyor)
     */
    private function collectItemsFromPost() {
        $descriptions = isset($_POST['item_description']) && is_array($_POST['item_description']) ? $_POST['item_description'] : [];
        $items = [];
        foreach ($descriptions as $i => $desc) {
            $desc = trim((string) $desc);
            $listingType = isset($_POST['item_listing_type'][$i]) ? trim((string) $_POST['item_listing_type'][$i]) : 'sale';
            if ($listingType !== 'sale' && $listingType !== 'rent') {
                $listingType = 'sale';
            }
            $price = isset($_POST['item_price'][$i]) ? (float) $_POST['item_price'][$i] : 0;
            $items[] = [
                'description' => $desc,
                'listing_type' => $listingType,
                'price' => $price,
            ];
        }
        return $items;
    }

    private function getTemplateModel() {
        require_once __DIR__ . '/ContractTemplateModel.php';
        return new ContractTemplateModel();
    }

    public function admin_templates() {
        $this->checkPermission('commission-contracts.view');
        $templateModel = $this->getTemplateModel();
        $templates = $templateModel->getAll();
        $data = [
            'title' => 'Sözleşme Şablonları',
            'user' => get_logged_in_user(),
            'templates' => $templates,
            'message' => $_SESSION['commission_template_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['commission_template_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null)
        ];
        unset($_SESSION['commission_template_message'], $_SESSION['error_message']);
        $this->adminView('templates/index', $data);
    }

    public function admin_template_create() {
        $this->checkPermission('commission-contracts.edit');
        $data = [
            'title' => 'Yeni Şablon',
            'user' => get_logged_in_user(),
            'template' => null,
        ];
        $this->adminView('templates/edit', $data);
    }

    public function admin_template_store() {
        $this->checkPermission('commission-contracts.edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . admin_url('module/commission-contracts/templates'));
            exit;
        }
        $name = trim($_POST['template_name'] ?? '');
        $slug = trim($_POST['template_slug'] ?? '');
        if ($name === '') {
            $_SESSION['error_message'] = 'Şablon adı zorunludur.';
            header('Location: ' . admin_url('module/commission-contracts/templates/create'));
            exit;
        }
        $headerConfig = $this->parseJsonPost('header_config');
        $tableConfig = $this->parseJsonPost('table_config');
        $footerConfig = $this->parseJsonPost('footer_config');
        $templateModel = $this->getTemplateModel();
        if ($slug !== '' && $templateModel->findBySlug($slug)) {
            $_SESSION['error_message'] = 'Bu slug zaten kullanılıyor.';
            header('Location: ' . admin_url('module/commission-contracts/templates/create'));
            exit;
        }
        $templateModel->create([
            'name' => $name,
            'slug' => $slug,
            'header_config' => $headerConfig,
            'table_config' => $tableConfig,
            'footer_config' => $footerConfig,
        ]);
        $_SESSION['commission_template_message'] = 'Şablon oluşturuldu.';
        header('Location: ' . admin_url('module/commission-contracts/templates'));
        exit;
    }

    public function admin_template_edit($id) {
        $this->checkPermission('commission-contracts.edit');
        $templateModel = $this->getTemplateModel();
        $template = $templateModel->find($id);
        if (!$template) {
            $_SESSION['error_message'] = 'Şablon bulunamadı.';
            header('Location: ' . admin_url('module/commission-contracts/templates'));
            exit;
        }
        $tc = is_string($template['table_config'] ?? '') ? json_decode($template['table_config'], true) : ($template['table_config'] ?? []);
        $template['sections'] = $this->normalizeTableConfigToSections(is_array($tc) ? $tc : []);
        $data = [
            'title' => 'Şablonu Düzenle',
            'user' => get_logged_in_user(),
            'template' => $template,
        ];
        $this->adminView('templates/edit', $data);
    }

    public function admin_template_update($id) {
        $this->checkPermission('commission-contracts.edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . admin_url('module/commission-contracts/templates'));
            exit;
        }
        $templateModel = $this->getTemplateModel();
        $template = $templateModel->find($id);
        if (!$template) {
            $_SESSION['error_message'] = 'Şablon bulunamadı.';
            header('Location: ' . admin_url('module/commission-contracts/templates'));
            exit;
        }
        $name = trim($_POST['template_name'] ?? '');
        $slug = trim($_POST['template_slug'] ?? '');
        if ($name === '') {
            $_SESSION['error_message'] = 'Şablon adı zorunludur.';
            header('Location: ' . admin_url('module/commission-contracts/templates/edit/' . $id));
            exit;
        }
        if ($slug !== '') {
            $existing = $templateModel->findBySlug($slug);
            if ($existing && (int) $existing['id'] !== (int) $id) {
                $_SESSION['error_message'] = 'Bu slug zaten kullanılıyor.';
                header('Location: ' . admin_url('module/commission-contracts/templates/edit/' . $id));
                exit;
            }
        }
        $headerConfig = $this->parseJsonPost('header_config');
        $tableConfig = $this->parseJsonPost('table_config');
        $footerConfig = $this->parseJsonPost('footer_config');
        $templateModel->update($id, [
            'name' => $name,
            'slug' => $slug,
            'header_config' => $headerConfig,
            'table_config' => $tableConfig,
            'footer_config' => $footerConfig,
        ]);
        $_SESSION['commission_template_message'] = 'Şablon güncellendi.';
        header('Location: ' . admin_url('module/commission-contracts/templates'));
        exit;
    }

    public function admin_template_delete($id) {
        $this->checkPermission('commission-contracts.delete');
        $templateModel = $this->getTemplateModel();
        $template = $templateModel->find($id);
        if (!$template) {
            $_SESSION['error_message'] = 'Şablon bulunamadı.';
            header('Location: ' . admin_url('module/commission-contracts/templates'));
            exit;
        }
        $usageCount = $this->model->getCountByTemplateId($id);
        if ($usageCount > 0) {
            $_SESSION['error_message'] = 'Bu şablon ' . $usageCount . ' adet sözleşmede kullanılıyor, silinemez.';
            header('Location: ' . admin_url('module/commission-contracts/templates'));
            exit;
        }
        $templateModel->delete($id);
        $_SESSION['commission_template_message'] = 'Şablon silindi.';
        header('Location: ' . admin_url('module/commission-contracts/templates'));
        exit;
    }

    /**
     * table_config'i her zaman sections dizisine çevirir (geriye uyumluluk).
     * Eski format (rows, cols, headers, cells) tek "table" bölümüne dönüştürülür; form_data anahtarları cell_r_c kalır (legacy_keys).
     * Ayrıca block alanlarının key'lerini benzersiz yapar (çakışmaları düzeltir).
     */
    private function normalizeTableConfigToSections($tableConfig) {
        if (!is_array($tableConfig)) {
            return [];
        }
        $sections = [];
        if (!empty($tableConfig['sections']) && is_array($tableConfig['sections'])) {
            $sections = $tableConfig['sections'];
            // Tablo bölümlerinde table_blocks yoksa (eski format) tek blok olarak ekle
            foreach ($sections as $idx => &$secRef) {
                if (($secRef['type'] ?? '') === 'table' && (empty($secRef['table_blocks']) || !is_array($secRef['table_blocks']))) {
                    $rows = (int)($secRef['rows'] ?? 0);
                    $cols = (int)($secRef['cols'] ?? 0);
                    if ($rows >= 1 && $cols >= 1) {
                        $secRef['table_blocks'] = [
                            [
                                'rows' => $rows,
                                'cols' => $cols,
                                'row_labels' => $secRef['row_labels'] ?? [],
                                'headers' => $secRef['headers'] ?? array_fill(0, $cols, ''),
                                'cells' => $secRef['cells'] ?? [],
                            ],
                        ];
                    }
                }
            }
            unset($secRef);
        } else {
            // Eski tek tablo formatı → tek bölüm
            $rows = (int)($tableConfig['rows'] ?? 0);
            $cols = (int)($tableConfig['cols'] ?? 0);
            if ($rows >= 1 && $cols >= 1) {
                $headers = $tableConfig['headers'] ?? array_fill(0, $cols, '');
                $cells = $tableConfig['cells'] ?? [];
                $sections = [
                    [
                        'type' => 'table',
                        'title' => 'Tablo',
                        'theme_color' => $tableConfig['theme_color'] ?? '#4472C4',
                        'rows' => $rows,
                        'cols' => $cols,
                        'row_labels' => [],
                        'headers' => array_values($headers),
                        'cells' => $cells,
                        'legacy_keys' => true,
                        'table_blocks' => [
                            [
                                'rows' => $rows,
                                'cols' => $cols,
                                'row_labels' => $tableConfig['row_labels'] ?? [],
                                'headers' => array_values($headers),
                                'cells' => $cells,
                            ],
                        ],
                    ],
                ];
            }
        }
        
        // Key çakışmalarını düzelt - her section için benzersiz key oluştur
        $usedKeys = [];
        foreach ($sections as $secIdx => &$secRef) {
            if (($secRef['type'] ?? '') === 'block' && !empty($secRef['blocks'])) {
                foreach ($secRef['blocks'] as $blkIdx => &$blkRef) {
                    if (!empty($blkRef['fields'])) {
                        foreach ($blkRef['fields'] as $fIdx => &$fieldRef) {
                            $origKey = $fieldRef['key'] ?? '';
                            if (empty($origKey) || isset($usedKeys[$origKey])) {
                                $fieldRef['key'] = 'sec' . $secIdx . '_b' . $blkIdx . '_f' . $fIdx;
                            }
                            $usedKeys[$fieldRef['key']] = true;
                        }
                        unset($fieldRef);
                    }
                }
                unset($blkRef);
            }
        }
        unset($secRef);
        
        return $sections;
    }

    /**
     * PDF'te form_data bazen JSON'dan dizi geliyor (eski kayıtlar). Checkbox dizileri 0/1, diğerleri metin birleştir.
     */
    private function normalizeFormDataForPdf(array $formData) {
        foreach ($formData as $k => $v) {
            if (is_array($v)) {
                $only01 = true;
                foreach ($v as $item) {
                    if ($item !== '0' && $item !== 0 && $item !== '1' && $item !== 1) {
                        $only01 = false;
                        break;
                    }
                }
                $formData[$k] = $only01
                    ? ((in_array('1', $v, true) || in_array(1, $v, true)) ? '1' : '0')
                    : implode("\n", array_map('strval', $v));
            }
        }
        return $formData;
    }

    /**
     * Aynı name ile hidden (0) + checkbox (1) gönderildiğinde PHP form_data[key]'ı dizi yapabiliyor.
     * Sadece checkbox benzeri diziler (içinde sadece 0/1) ise '1'/'0' yapıyoruz; metin içeren dizilere dokunmuyoruz.
     */
    private function normalizeFormDataForSave(array $formData) {
        foreach ($formData as $k => $v) {
            if (is_array($v)) {
                $only01 = true;
                foreach ($v as $item) {
                    if ($item !== '0' && $item !== 0 && $item !== '1' && $item !== 1) {
                        $only01 = false;
                        break;
                    }
                }
                $formData[$k] = $only01
                    ? ((in_array('1', $v, true) || in_array(1, $v, true)) ? '1' : '0')
                    : implode("\n", array_map('strval', $v));
            }
        }
        return $formData;
    }

    /**
     * Şablondaki sadece checkbox tipindeki alanların key'lerini döndürür (header + sections block + table).
     */
    private function getCheckboxKeysFromTemplate(array $template) {
        $keys = [];
        $headerConfig = is_string($template['header_config'] ?? '') ? json_decode($template['header_config'], true) : ($template['header_config'] ?? []);
        if (is_array($headerConfig)) {
            foreach (['left', 'right'] as $side) {
                foreach ($headerConfig[$side] ?? [] as $f) {
                    if (($f['type'] ?? '') === 'checkbox') {
                        $k = $f['key'] ?? null;
                        if ($k !== null && $k !== '') $keys[] = $k;
                    }
                }
            }
        }
        $sections = isset($template['sections']) && is_array($template['sections']) ? $template['sections'] : [];
        foreach ($sections as $secIdx => $sec) {
            if (($sec['type'] ?? '') === 'block') {
                foreach ($sec['blocks'] ?? [] as $blk) {
                    foreach ($blk['fields'] ?? [] as $f) {
                        if (($f['type'] ?? '') === 'checkbox') {
                            $k = $f['key'] ?? null;
                            if ($k !== null && $k !== '') $keys[] = $k;
                        }
                    }
                }
            } elseif (($sec['type'] ?? '') === 'table') {
                $tableBlocks = $sec['table_blocks'] ?? [];
                $legacy = !empty($sec['legacy_keys']);
                if (!empty($tableBlocks) && is_array($tableBlocks)) {
                    foreach ($tableBlocks as $blockIdx => $blk) {
                        $rows = (int)($blk['rows'] ?? 0);
                        $cols = (int)($blk['cols'] ?? 0);
                        $cells = $blk['cells'] ?? [];
                        for ($r = 0; $r < $rows; $r++) {
                            for ($c = 0; $c < $cols; $c++) {
                                $rowCells = $cells[$r] ?? [];
                                $cellType = $rowCells[$c] ?? 'text';
                                if ($cellType === 'checkbox') {
                                    $keys[] = $legacy && count($tableBlocks) === 1 ? ('cell_' . $r . '_' . $c) : ('sec_' . $secIdx . '_block_' . $blockIdx . '_cell_' . $r . '_' . $c);
                                }
                            }
                        }
                    }
                } else {
                    $rows = (int)($sec['rows'] ?? 0);
                    $cols = (int)($sec['cols'] ?? 0);
                    $cells = $sec['cells'] ?? [];
                    for ($r = 0; $r < $rows; $r++) {
                        for ($c = 0; $c < $cols; $c++) {
                            $rowCells = $cells[$r] ?? [];
                            $cellType = $rowCells[$c] ?? 'text';
                            if ($cellType === 'checkbox') {
                                $keys[] = $legacy ? ('cell_' . $r . '_' . $c) : ('sec_' . $secIdx . '_cell_' . $r . '_' . $c);
                            }
                        }
                    }
                }
            }
        }
        return array_values(array_unique($keys));
    }

    /**
     * Şablondaki imza tipindeki tüm alanların key'lerini döndürür (sections table + footer blocks).
     * Karşı taraf imzaladığında bu key'ler form_data'da bu imza ile doldurulur.
     */
    private function getSignatureKeysFromTemplate(array $template) {
        $keys = [];
        $tableConfig = is_string($template['table_config'] ?? '') ? json_decode($template['table_config'], true) : ($template['table_config'] ?? []);
        $footerConfig = is_string($template['footer_config'] ?? '') ? json_decode($template['footer_config'], true) : ($template['footer_config'] ?? []);
        $sections = $this->normalizeTableConfigToSections(is_array($tableConfig) ? $tableConfig : []);
        foreach ($sections as $secIdx => $sec) {
            if (($sec['type'] ?? '') === 'block') {
                foreach ($sec['blocks'] ?? [] as $blk) {
                    foreach ($blk['fields'] ?? [] as $f) {
                        if (($f['type'] ?? '') === 'signature') {
                            $k = $f['key'] ?? null;
                            if ($k !== null && $k !== '') $keys[] = $k;
                        }
                    }
                }
            } elseif (($sec['type'] ?? '') === 'table') {
                $tableBlocks = $sec['table_blocks'] ?? [];
                $legacy = !empty($sec['legacy_keys']);
                if (!empty($tableBlocks) && is_array($tableBlocks)) {
                    foreach ($tableBlocks as $blockIdx => $blk) {
                        $rows = (int)($blk['rows'] ?? 0);
                        $cols = (int)($blk['cols'] ?? 0);
                        $cells = $blk['cells'] ?? [];
                        for ($r = 0; $r < $rows; $r++) {
                            for ($c = 0; $c < $cols; $c++) {
                                $rowCells = $cells[$r] ?? [];
                                $cellType = $rowCells[$c] ?? 'text';
                                if ($cellType === 'signature') {
                                    $keys[] = $legacy && count($tableBlocks) === 1 ? ('cell_' . $r . '_' . $c) : ('sec_' . $secIdx . '_block_' . $blockIdx . '_cell_' . $r . '_' . $c);
                                }
                            }
                        }
                    }
                } else {
                    $rows = (int)($sec['rows'] ?? 0);
                    $cols = (int)($sec['cols'] ?? 0);
                    $cells = $sec['cells'] ?? [];
                    for ($r = 0; $r < $rows; $r++) {
                        for ($c = 0; $c < $cols; $c++) {
                            $rowCells = $cells[$r] ?? [];
                            $cellType = $rowCells[$c] ?? 'text';
                            if ($cellType === 'signature') {
                                $keys[] = $legacy ? ('cell_' . $r . '_' . $c) : ('sec_' . $secIdx . '_cell_' . $r . '_' . $c);
                            }
                        }
                    }
                }
            }
        }
        if (!empty($footerConfig['blocks']) && is_array($footerConfig['blocks'])) {
            foreach ($footerConfig['blocks'] as $fblk) {
                foreach ($fblk['fields'] ?? [] as $f) {
                    if (($f['type'] ?? '') === 'signature') {
                        $k = $f['key'] ?? null;
                        if ($k !== null && $k !== '') $keys[] = $k;
                    }
                }
            }
        }
        return array_values(array_unique($keys));
    }

    private function parseJsonPost($key) {
        $raw = $_POST[$key] ?? '';
        if (!is_string($raw) || $raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function admin_index() {
        $this->checkPermission('commission-contracts.view');
        $filters = [
            'status' => isset($_GET['status']) ? trim($_GET['status']) : ''
        ];
        $contracts = $this->model->getAllForAdmin($filters);
        $data = [
            'title' => 'Sözleşme Modülü',
            'user' => get_logged_in_user(),
            'contracts' => $contracts,
            'filters' => $filters,
            'message' => $_SESSION['commission_contracts_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['commission_contracts_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null)
        ];
        unset($_SESSION['commission_contracts_message'], $_SESSION['error_message']);
        $this->adminView('index', $data);
    }

    public function admin_create() {
        $this->checkPermission('commission-contracts.create');
        $templateModel = $this->getTemplateModel();
        $templates = $templateModel->getAllForSelect();
        $templateId = isset($_GET['template_id']) ? (int) $_GET['template_id'] : null;
        $template = null;
        if ($templateId && $templates) {
            $template = $templateModel->find($templateId);
            if ($template) {
                $tc = is_string($template['table_config'] ?? '') ? json_decode($template['table_config'], true) : ($template['table_config'] ?? []);
                $template['sections'] = $this->normalizeTableConfigToSections(is_array($tc) ? $tc : []);
            }
        }
        $data = [
            'title' => 'Yeni Sözleşme',
            'user' => get_logged_in_user(),
            'contract' => null,
            'items' => [],
            'templates' => $templates,
            'template' => $template,
            'templateId' => $templateId,
        ];
        $this->adminView('create', $data);
    }

    public function admin_store() {
        $this->checkPermission('commission-contracts.create');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . admin_url('module/commission-contracts'));
            exit;
        }
        $user = get_logged_in_user();
        $templateId = isset($_POST['template_id']) ? (int) $_POST['template_id'] : null;
        $clientName = trim($_POST['client_name'] ?? '');
        if (!$templateId && $clientName === '') {
            $_SESSION['error_message'] = 'Ad/Unvan zorunludur!';
            header('Location: ' . admin_url('module/commission-contracts/create'));
            exit;
        }
        $formData = isset($_POST['form_data']) && is_array($_POST['form_data']) ? $_POST['form_data'] : [];
            $formData = $this->normalizeFormDataForSave($formData);

        if ($templateId) {
            $contractNumber = trim($_POST['contract_number'] ?? '') ?: null;
            $contractName = trim($_POST['contract_name'] ?? '') ?: null;
            $contractData = [
                'contract_number' => $contractNumber,
                'contract_name' => $contractName,
                'client_type' => 'individual',
                'client_name' => $clientName,
                'client_tax_number' => null,
                'client_tax_office' => null,
                'client_address' => null,
                'client_phone' => null,
                'client_email' => null,
                'template_id' => $templateId,
                'form_data' => $formData,
                'created_by' => $user ? (int) $user['id'] : null
            ];
            $id = $this->model->create($contractData);
            if (!$id) {
                $_SESSION['error_message'] = 'Sözleşme oluşturulurken bir hata oluştu!';
                header('Location: ' . admin_url('module/commission-contracts/create', ['template_id' => $templateId]));
                exit;
            }
            $_SESSION['commission_contracts_message'] = 'Sözleşme başarıyla oluşturuldu!';
            header('Location: ' . admin_url('module/commission-contracts/edit/' . $id));
            exit;
        }

        $this->saveCompanyInfoToSettings($_POST);
        $contractNumber = trim($_POST['contract_number'] ?? '') ?: null;
        $contractName = trim($_POST['contract_name'] ?? '') ?: null;
        $contractData = [
            'contract_number' => $contractNumber,
            'contract_name' => $contractName,
            'client_type' => isset($_POST['client_type']) && $_POST['client_type'] === 'legal' ? 'legal' : 'individual',
            'client_name' => $clientName,
            'client_tax_number' => trim($_POST['client_tax_number'] ?? '') ?: null,
            'client_tax_office' => trim($_POST['client_tax_office'] ?? '') ?: null,
            'client_address' => trim($_POST['client_address'] ?? '') ?: null,
            'client_phone' => trim($_POST['client_phone'] ?? '') ?: null,
            'client_email' => trim($_POST['client_email'] ?? '') ?: null,
            'created_by' => $user ? (int) $user['id'] : null
        ];
        $id = $this->model->create($contractData);
        if (!$id) {
            $_SESSION['error_message'] = 'Sözleşme oluşturulurken bir hata oluştu!';
            header('Location: ' . admin_url('module/commission-contracts/create'));
            exit;
        }
        $items = $this->collectItemsFromPost();
        $this->model->setItems($id, $items);
        $_SESSION['commission_contracts_message'] = 'Sözleşme başarıyla oluşturuldu!';
        header('Location: ' . admin_url('module/commission-contracts/edit/' . $id));
        exit;
    }

    public function admin_edit($id) {
        $this->checkPermission('commission-contracts.edit');
        $contract = $this->model->find($id);
        if (!$contract) {
            $_SESSION['error_message'] = 'Sözleşme bulunamadı!';
            header('Location: ' . admin_url('module/commission-contracts'));
            exit;
        }
        $template = null;
        if (!empty($contract['template_id'])) {
            $templateModel = $this->getTemplateModel();
            $template = $templateModel->find($contract['template_id']);
            if ($template) {
                $tc = is_string($template['table_config'] ?? '') ? json_decode($template['table_config'], true) : ($template['table_config'] ?? []);
                $template['sections'] = $this->normalizeTableConfigToSections(is_array($tc) ? $tc : []);
            }
        }
        $items = $this->model->getItems($id);
        $formData = [];
        if (!empty($contract['form_data'])) {
            $formData = is_string($contract['form_data']) ? json_decode($contract['form_data'], true) : $contract['form_data'];
            if (!is_array($formData)) $formData = [];
            // View'da checkbox "checked" tutarlı olsun diye 0/1/true/false skaler değerleri string '1'/'0' yap (metin alanlarına dokunma)
            foreach ($formData as $k => $v) {
                if (!is_array($v)) {
                    if (in_array($v, ['1', 1, true], true)) $formData[$k] = '1';
                    elseif (in_array($v, ['0', 0, false], true)) $formData[$k] = '0';
                }
            }
        }
        $data = [
            'title' => 'Sözleşme Düzenle',
            'user' => get_logged_in_user(),
            'contract' => $contract,
            'items' => $items,
            'template' => $template,
            'formData' => $formData,
            'message' => $_SESSION['commission_contracts_message'] ?? $_SESSION['error_message'] ?? null,
            'messageType' => isset($_SESSION['commission_contracts_message']) ? 'success' : (isset($_SESSION['error_message']) ? 'error' : null)
        ];
        unset($_SESSION['commission_contracts_message'], $_SESSION['error_message']);
        if ($template) {
            $this->adminView('edit-template', $data);
        } else {
            $this->adminView('edit', $data);
        }
    }

    public function admin_update($id) {
        $this->checkPermission('commission-contracts.edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . admin_url('module/commission-contracts'));
            exit;
        }
        $contract = $this->model->find($id);
        if (!$contract) {
            $_SESSION['error_message'] = 'Sözleşme bulunamadı!';
            header('Location: ' . admin_url('module/commission-contracts'));
            exit;
        }
        if ($contract['status'] === 'signed') {
            $_SESSION['error_message'] = 'İmzalanmış sözleşme düzenlenemez!';
            header('Location: ' . admin_url('module/commission-contracts/edit/' . $id));
            exit;
        }

        $clientName = trim($_POST['client_name'] ?? '');

        if (!empty($contract['template_id'])) {
            $posted = isset($_POST['form_data']) && is_array($_POST['form_data']) ? $_POST['form_data'] : [];
            $existing = [];
            if (!empty($contract['form_data'])) {
                $existing = is_string($contract['form_data']) ? json_decode($contract['form_data'], true) : $contract['form_data'];
                if (!is_array($existing)) $existing = [];
            }
            $formData = array_merge($existing, $posted);
            $formData = $this->normalizeFormDataForSave($formData);

            $contractNumber = trim($_POST['contract_number'] ?? '');
            $contractName = trim($_POST['contract_name'] ?? '');
            $this->model->update($id, [
                'contract_number' => $contractNumber !== '' ? $contractNumber : null,
                'contract_name' => $contractName !== '' ? $contractName : null,
                'client_type' => 'individual',
                'client_name' => $clientName,
                'client_tax_number' => null,
                'client_tax_office' => null,
                'client_address' => null,
                'client_phone' => null,
                'client_email' => null,
                'template_id' => (int) $contract['template_id'],
                'form_data' => $formData,
            ]);
            $_SESSION['commission_contracts_message'] = 'Sözleşme güncellendi!';
            header('Location: ' . admin_url('module/commission-contracts/edit/' . $id));
            exit;
        }

        $this->saveCompanyInfoToSettings($_POST);
        $contractNumber = trim($_POST['contract_number'] ?? '');
        $contractName = trim($_POST['contract_name'] ?? '');
        $contractData = [
            'contract_number' => $contractNumber !== '' ? $contractNumber : ($contract['contract_number'] ?? null),
            'contract_name' => $contractName !== '' ? $contractName : ($contract['contract_name'] ?? null),
            'client_type' => isset($_POST['client_type']) && $_POST['client_type'] === 'legal' ? 'legal' : 'individual',
            'client_name' => $clientName,
            'client_tax_number' => trim($_POST['client_tax_number'] ?? '') ?: null,
            'client_tax_office' => trim($_POST['client_tax_office'] ?? '') ?: null,
            'client_address' => trim($_POST['client_address'] ?? '') ?: null,
            'client_phone' => trim($_POST['client_phone'] ?? '') ?: null,
            'client_email' => trim($_POST['client_email'] ?? '') ?: null
        ];
        $this->model->update($id, $contractData);
        $items = $this->collectItemsFromPost();
        $this->model->setItems($id, $items);
        $_SESSION['commission_contracts_message'] = 'Sözleşme güncellendi!';
        header('Location: ' . admin_url('module/commission-contracts/edit/' . $id));
        exit;
    }

    public function admin_delete($id) {
        $this->checkPermission('commission-contracts.delete');
        $contract = $this->model->find($id);
        if (!$contract) {
            $_SESSION['error_message'] = 'Sözleşme bulunamadı!';
            header('Location: ' . admin_url('module/commission-contracts'));
            exit;
        }
        $this->model->delete($id);
        $_SESSION['commission_contracts_message'] = 'Sözleşme silindi!';
        header('Location: ' . admin_url('module/commission-contracts'));
        exit;
    }

    public function admin_sign($id) {
        $this->checkPermission('commission-contracts.edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . admin_url('module/commission-contracts'));
            exit;
        }
        $contract = $this->model->find($id);
        if (!$contract) {
            $_SESSION['error_message'] = 'Sözleşme bulunamadı!';
            header('Location: ' . admin_url('module/commission-contracts'));
            exit;
        }
        if ($contract['status'] !== 'draft') {
            $_SESSION['error_message'] = 'Sadece taslak sözleşmeler imzalanabilir!';
            header('Location: ' . admin_url('module/commission-contracts/edit/' . $id));
            exit;
        }
        $signatureData = trim($_POST['signature_data'] ?? '');
        if ($signatureData === '' || strpos($signatureData, 'data:image/png;base64,') !== 0) {
            $_SESSION['error_message'] = 'Geçerli bir imza gerekli!';
            header('Location: ' . admin_url('module/commission-contracts/edit/' . $id));
            exit;
        }
        $signatureDataParty2 = trim($_POST['signature_data_party2'] ?? '');
        if ($signatureDataParty2 !== '' && strpos($signatureDataParty2, 'data:image/png;base64,') !== 0) {
            $signatureDataParty2 = '';
        }
        $signatureCount = 2;
        if (!empty($contract['template_id'])) {
            $templateModel = $this->getTemplateModel();
            $template = $templateModel->find($contract['template_id']);
            if ($template && !empty($template['footer_config'])) {
                $fc = is_string($template['footer_config']) ? json_decode($template['footer_config'], true) : $template['footer_config'];
                if (!empty($fc['signatures']) && is_array($fc['signatures'])) {
                    $signatureCount = max(2, min(5, count($fc['signatures'])));
                }
            }
        }
        $this->model->sign($id, $signatureData, $signatureDataParty2 !== '' ? $signatureDataParty2 : null, $signatureCount);
        $_SESSION['commission_contracts_message'] = 'Sözleşme imzalandı!';
        header('Location: ' . admin_url('module/commission-contracts/edit/' . $id));
        exit;
    }

    /**
     * Sözleşme önizleme HTML'i (imza sayfasında ve imza sonrası gösterim için).
     */
    public function getContractHtmlForPreview($contract) {
        if (empty($contract['template_id'])) {
            return '';
        }
        $templateModel = $this->getTemplateModel();
        $template = $templateModel->find($contract['template_id']);
        if (!$template) {
            return '';
        }
        $formData = !empty($contract['form_data']) ? (is_string($contract['form_data']) ? json_decode($contract['form_data'], true) : $contract['form_data']) : [];
        if (!is_array($formData)) {
            $formData = [];
        }
        return $this->buildPdfFromTemplate($contract, $template, $formData);
    }

    /**
     * Karşı taraf imza sayfası (public, login yok). Token ile sözleşme bulunur; sözleşme metni gösterilir, karşı taraf canvas ile imza atar.
     */
    public function frontend_sign_page($token) {
        $contract = $this->model->findBySignToken($token);
        if (!$contract) {
            header('HTTP/1.0 404 Not Found');
            echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Link geçersiz</title></head><body><p>Geçersiz veya süresi dolmuş imza linki.</p></body></html>';
            exit;
        }
        $partyIndex = (int)($contract['sign_party_index'] ?? 2);
        $alreadySigned = ($partyIndex === 2 && !empty($contract['signature_data_party2'])) || ($partyIndex === 3 && !empty($contract['signature_data_party3']));
        $success = isset($_GET['signed']) && $_GET['signed'] === '1';
        $contractHtml = $this->getContractHtmlForPreview($contract);
        $this->frontendView('sign', [
            'contract' => $contract,
            'token' => $token,
            'contractHtml' => $contractHtml,
            'error' => null,
            'alreadySigned' => $alreadySigned,
            'success' => $success,
            'sign_party_index' => $partyIndex,
        ]);
    }

    /**
     * Karşı taraf imzasını kaydet (POST). Token geçerli, sözleşme signed ve party2 boş olmalı.
     */
    public function frontend_sign_submit($token) {
        $signatureData = trim($_POST['signature_data'] ?? '');
        if ($signatureData === '' || strpos($signatureData, 'data:image/png;base64,') !== 0) {
            $contract = $this->model->findBySignToken($token);
            $contractHtml = $contract ? $this->getContractHtmlForPreview($contract) : '';
            $this->frontendView('sign', [
                'contract' => $contract,
                'token' => $token,
                'contractHtml' => $contractHtml,
                'error' => 'Lütfen imza alanına imzanızı çizin.',
                'alreadySigned' => false,
                'success' => false,
            ]);
            return;
        }
        $contract = $this->model->findBySignToken($token);
        if (!$contract) {
            header('HTTP/1.0 404 Not Found');
            echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Link geçersiz</title></head><body><p>Geçersiz veya süresi dolmuş imza linki.</p></body></html>';
            exit;
        }
        $partyIndex = (int)($contract['sign_party_index'] ?? 2);
        $alreadySignedParty = ($partyIndex === 2 && !empty($contract['signature_data_party2'])) || ($partyIndex === 3 && !empty($contract['signature_data_party3']));
        if ($contract['status'] !== 'signed' || $alreadySignedParty) {
            $contractHtml = $this->getContractHtmlForPreview($contract);
            $this->frontendView('sign', [
                'contract' => $contract,
                'token' => $token,
                'contractHtml' => $contractHtml,
                'error' => 'Bu sözleşme imzalanamaz veya bu link zaten imzalanmış.',
                'alreadySigned' => $alreadySignedParty,
                'success' => false,
            ]);
            return;
        }
        if ($partyIndex === 3) {
            $this->model->signParty3($contract['id'], $signatureData);
        } else {
            $this->model->signParty2($contract['id'], $signatureData);
            // Şablondaki imza tipi alanları 2. taraf imzası ile doldur (form_data)
            if (!empty($contract['template_id'])) {
                $templateModel = $this->getTemplateModel();
                $template = $templateModel->find($contract['template_id']);
                if ($template) {
                    $signatureKeys = $this->getSignatureKeysFromTemplate($template);
                    if (!empty($signatureKeys)) {
                        $formData = !empty($contract['form_data']) ? (is_string($contract['form_data']) ? json_decode($contract['form_data'], true) : $contract['form_data']) : [];
                        if (!is_array($formData)) $formData = [];
                        foreach ($signatureKeys as $key) {
                            $formData[$key] = $signatureData;
                        }
                        $this->model->updateFormData($contract['id'], $formData);
                    }
                }
            }
        }

        header('Location: ' . site_url('sozlesme-imza/' . $token) . '?signed=1');
        exit;
    }

    /**
     * Karşı taraf imzalı sözleşmeyi token ile PDF olarak indirir (public, login yok).
     */
    public function frontend_sign_pdf($token) {
        $contract = $this->model->findBySignToken($token);
        if (!$contract) {
            header('HTTP/1.0 404 Not Found');
            echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Link geçersiz</title></head><body><p>Geçersiz veya süresi dolmuş imza linki.</p></body></html>';
            exit;
        }
        $autoload = dirname(dirname(__DIR__)) . '/vendor/autoload.php';
        if (!file_exists($autoload)) {
            header('Content-Type: text/html; charset=utf-8');
            echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Hata</title></head><body><p>PDF oluşturmak için sistem yapılandırması eksik.</p></body></html>';
            exit;
        }
        require_once $autoload;
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new \Dompdf\Dompdf($options);
        if (!empty($contract['template_id'])) {
            $templateModel = $this->getTemplateModel();
            $template = $templateModel->find($contract['template_id']);
            $formData = !empty($contract['form_data']) ? (is_string($contract['form_data']) ? json_decode($contract['form_data'], true) : $contract['form_data']) : [];
            if (!is_array($formData)) $formData = [];
            $html = $this->buildPdfFromTemplate($contract, $template, $formData);
        } else {
            $items = $this->model->getItems($contract['id']);
            $html = $this->buildPdfHtml($contract, $items);
        }
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $filename = 'sozlesme-' . (int) $contract['id'] . '-' . date('Y-m-d') . '.pdf';
        $pdfOutput = $dompdf->output();
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . addslashes($filename) . '"');
        header('Content-Length: ' . strlen($pdfOutput));
        header('Cache-Control: private, max-age=0, must-revalidate');
        echo $pdfOutput;
        exit;
    }

    public function admin_pdf($id) {
        $this->checkPermission('commission-contracts.view');
        $contract = $this->model->find($id);
        if (!$contract) {
            $_SESSION['error_message'] = 'Sözleşme bulunamadı!';
            header('Location: ' . admin_url('module/commission-contracts'));
            exit;
        }

        $autoload = dirname(dirname(__DIR__)) . '/vendor/autoload.php';
        if (!file_exists($autoload)) {
            $_SESSION['error_message'] = 'PDF oluşturmak için Composer ile dompdf/dompdf yükleyin.';
            header('Location: ' . admin_url('module/commission-contracts/edit/' . $id));
            exit;
        }
        require_once $autoload;
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new \Dompdf\Dompdf($options);

        if (!empty($contract['template_id'])) {
            $templateModel = $this->getTemplateModel();
            $template = $templateModel->find($contract['template_id']);
            $formData = !empty($contract['form_data']) ? (is_string($contract['form_data']) ? json_decode($contract['form_data'], true) : $contract['form_data']) : [];
            if (!is_array($formData)) $formData = [];
            $html = $this->buildPdfFromTemplate($contract, $template, $formData);
        } else {
            $items = $this->model->getItems($id);
            $html = $this->buildPdfHtml($contract, $items);
        }

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $filename = 'sozlesme-' . $id . '-' . date('Y-m-d') . '.pdf';
        $viewInBrowser = !empty($_GET['view']);
        $pdfOutput = $dompdf->output();
        header('Content-Type: application/pdf');
        header('Content-Disposition: ' . ($viewInBrowser ? 'inline' : 'attachment') . '; filename="' . addslashes($filename) . '"');
        header('Content-Length: ' . strlen($pdfOutput));
        header('Cache-Control: private, max-age=0, must-revalidate');
        echo $pdfOutput;
        exit;
    }

    /**
     * Şablon + form_data ile PDF HTML üretir
     */
    private function buildPdfFromTemplate($contract, $template, array $formData) {
        require_once dirname(dirname(__DIR__)) . '/includes/functions.php';
        // Dizi (hidden+checkbox aynı name) veya skaler; hepsini tek değere indirge
        $formData = $this->normalizeFormDataForPdf($formData);
        // Checkbox: 1, '1', true, 'on' veya dizi içinde 1/'1' → işaretli
        $isCheckboxChecked = function ($v) {
            if (is_array($v)) return in_array('1', $v, true) || in_array(1, $v, true);
            if ($v === true || $v === 1 || $v === '1' || $v === 'on') return true;
            if (is_string($v) && trim($v) !== '' && strtolower($v) !== '0' && strtolower($v) !== 'false') return true;
            return false;
        };
        $settings = get_option('site_settings', []);
        // Logo: sadece site ayarları (Ayarlar > Genel > Site Logo)
        $companyLogo = get_option('site_logo', '');
        $headerConfig = is_string($template['header_config'] ?? '') ? json_decode($template['header_config'], true) : ($template['header_config'] ?? []);
        $tableConfig = is_string($template['table_config'] ?? '') ? json_decode($template['table_config'], true) : ($template['table_config'] ?? []);
        $footerConfig = is_string($template['footer_config'] ?? '') ? json_decode($template['footer_config'], true) : ($template['footer_config'] ?? []);
        if (!is_array($headerConfig)) $headerConfig = ['left' => [], 'center' => 'logo', 'right' => []];
        if (!is_array($tableConfig)) $tableConfig = ['rows' => 3, 'cols' => 3, 'theme_color' => '#4472C4', 'headers' => [], 'cells' => []];
        if (!is_array($footerConfig)) $footerConfig = ['description_label' => 'Açıklama', 'description_key' => 'footer_description', 'signature_label' => 'İmza', 'signature_count' => 1];

        $statusLabel = ($contract['status'] ?? '') === 'signed' ? 'İmzalandı' : 'Taslak';
        $signedAt = !empty($contract['signed_at']) ? date('d.m.Y H:i', strtotime($contract['signed_at'])) : '—';
        $signatureImages = [
            1 => !empty($contract['signature_data']) ? '<img src="' . $contract['signature_data'] . '" alt="" style="max-width:180px;max-height:48px;border-bottom:1px solid #333;margin-top:2px;display:inline-block;vertical-align:middle;" />' : '',
            2 => !empty($contract['signature_data_party2']) ? '<img src="' . $contract['signature_data_party2'] . '" alt="" style="max-width:180px;max-height:48px;border-bottom:1px solid #333;margin-top:2px;display:inline-block;vertical-align:middle;" />' : '',
            3 => !empty($contract['signature_data_party3']) ? '<img src="' . $contract['signature_data_party3'] . '" alt="" style="max-width:180px;max-height:48px;border-bottom:1px solid #333;margin-top:2px;display:inline-block;vertical-align:middle;" />' : '',
        ];
        for ($p = 4; $p <= 5; $p++) {
            if (!isset($signatureImages[$p])) $signatureImages[$p] = '';
        }

        // dompdf için logo mutlak URL olmalı
        if (!empty($companyLogo) && strpos($companyLogo, 'http') !== 0) {
            $companyLogo = site_url(ltrim($companyLogo, '/'));
        }

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
        @page { size: A4 portrait; margin: 6mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.35; color: #333; margin: 0; padding: 0; width: 100%; box-sizing: border-box; }
        * { box-sizing: border-box; }
        h1 { font-size: 15px; margin: 0 0 6px; border-bottom: 1px solid #333; padding-bottom: 5px; text-align: center; }
        h2 { font-size: 11px; margin: 6px 0 4px; }
        table { width: 100%; border-collapse: collapse; margin: 4px 0; table-layout: fixed; font-size: 9px; }
        th, td { border: 1px solid #666; padding: 3px 5px; text-align: left; overflow: hidden; word-wrap: break-word; }
        th { font-weight: bold; }
        p { margin: 3px 0; }
        .signature-block { margin-top: 6px; page-break-inside: avoid; }
        .pdf-footer { margin-top: 4px; font-size: 8px; page-break-inside: avoid; }
        .pdf-header-table { width: 100%; margin-bottom: 6px; }
        .pdf-header-table td { border: none; padding: 0 5px 0 0; vertical-align: top; }
        img { max-width: 100%; height: auto; }
        </style></head><body>';

        // 1. En üstte: panelden gelen başlık değerleri (sol | ortada logo | sağ)
        $html .= '<div style="display: table; width: 100%; margin-bottom: 4px;">';
        $html .= '<div style="display: table-cell; width: 33%; vertical-align: top; padding-right: 4px;">';
        foreach ($headerConfig['left'] ?? [] as $f) {
            $k = $f['key'] ?? '';
            $label = $f['label'] ?? $k;
            $val = isset($formData[$k]) ? $formData[$k] : '';
            $ft = $f['type'] ?? 'text';
            if ($ft === 'checkbox') $val = $isCheckboxChecked($val) ? '&#9745;' : '&#9744;';
            else $val = (string) $val;
            $labelPart = trim((string)$label) !== '' ? '<strong>' . htmlspecialchars($label) . ':</strong> ' : '';
            $html .= '<p>' . $labelPart . ($ft === 'checkbox' ? $val : htmlspecialchars($val)) . '</p>';
        }
        $html .= '</div><div style="display: table-cell; width: 34%; text-align: center; vertical-align: top;">';
        if (($headerConfig['center'] ?? 'logo') === 'logo' && !empty($companyLogo)) {
            $html .= '<img src="' . htmlspecialchars($companyLogo) . '" alt="" style="max-width: 140px; max-height: 70px; object-fit: contain;" />';
        }
        $html .= '</div><div style="display: table-cell; width: 33%; vertical-align: top; padding-left: 4px;">';
        foreach ($headerConfig['right'] ?? [] as $f) {
            $k = $f['key'] ?? '';
            $label = $f['label'] ?? $k;
            $val = isset($formData[$k]) ? $formData[$k] : '';
            $ft = $f['type'] ?? 'text';
            if ($ft === 'checkbox') $val = $isCheckboxChecked($val) ? '&#9745;' : '&#9744;';
            else $val = (string) $val;
            $labelPart = trim((string)$label) !== '' ? '<strong>' . htmlspecialchars($label) . ':</strong> ' : '';
            $html .= '<p>' . $labelPart . ($ft === 'checkbox' ? $val : htmlspecialchars($val)) . '</p>';
        }
        $html .= '</div></div>';

        // 2. Altında: şablon başlığı (belge adı)
        $html .= '<h1>' . htmlspecialchars($template['name'] ?? 'Sözleşme') . '</h1>';

        $sections = $this->normalizeTableConfigToSections($tableConfig);
        
        foreach ($sections as $secIndex => $sec) {
            $themeColor = $sec['theme_color'] ?? '#4472C4';
            $secTitle = $sec['title'] ?? ('Bölüm ' . ($secIndex + 1));
            $html .= '<h2 style="background-color:' . htmlspecialchars($themeColor) . ';color:#fff;padding:3px 8px;margin:5px 0 0;font-size:10px;">' . htmlspecialchars($secTitle) . '</h2>';
            if (($sec['type'] ?? '') === 'block') {
                $blocks = $sec['blocks'] ?? [];
                $html .= '<table style="width:100%;border-collapse:collapse;margin:3px 0;font-size:9px;"><tbody>';
                $maxRows = 0;
                foreach ($blocks as $blk) {
                    $maxRows = max($maxRows, count($blk['fields'] ?? []));
                }
                for ($row = 0; $row < $maxRows; $row++) {
                    $html .= '<tr>';
                    foreach ($blocks as $blk) {
                        $fields = $blk['fields'] ?? [];
                        $f = $fields[$row] ?? null;
                        if ($f) {
                            $k = $f['key'] ?? '';
                            $label = $f['label'] ?? $k;
                            $val = isset($formData[$k]) ? $formData[$k] : '';
                            $ft = $f['type'] ?? 'text';
                            if ($ft === 'checkbox') {
                                $val = $isCheckboxChecked($val) ? '&#9745;' : '&#9744;';
                            } elseif ($ft === 'signature' && !empty($val) && strpos((string)$val, 'data:image') === 0) {
                                $val = '<img src="' . htmlspecialchars($val) . '" alt="İmza" style="max-width:80px;max-height:32px;" />';
                            } else {
                                $val = nl2br(htmlspecialchars((string) $val));
                            }
                            $labelPart = trim((string)$label) !== '' ? '<strong>' . htmlspecialchars($label) . ':</strong> ' : '';
                            $html .= '<td style="border:1px solid #666;padding:3px 5px;width:50%;">' . $labelPart . $val . '</td>';
                        } else {
                            $html .= '<td style="border:1px solid #666;padding:2px 4px;width:50%;"></td>';
                        }
                    }
                    $html .= '</tr>';
                }
                $html .= '</tbody></table>';
            } else {
                $tableBlocks = $sec['table_blocks'] ?? [];
                $legacyKeys = !empty($sec['legacy_keys']);
                if (!empty($tableBlocks) && is_array($tableBlocks)) {
                    $maxRows = 0;
                    foreach ($tableBlocks as $blk) {
                        $maxRows = max($maxRows, (int)($blk['rows'] ?? 0));
                    }
                    $html .= '<table style="border-collapse:collapse;margin:3px 0;border:1px solid #666;table-layout:fixed;width:100%;font-size:9px;">';
                    $html .= '<tbody>';
                    for ($r = 0; $r < $maxRows; $r++) {
                        $html .= '<tr>';
                        foreach ($tableBlocks as $blockIdx => $blk) {
                            $rows = (int)($blk['rows'] ?? 0);
                            $cols = (int)($blk['cols'] ?? 0);
                            $rowLabels = $blk['row_labels'] ?? [];
                            $headers = $blk['headers'] ?? array_fill(0, $cols, '');
                            $cells = $blk['cells'] ?? [];
                            if ($r < $rows) {
                                $lb = isset($rowLabels[$r]) ? $rowLabels[$r] : ('Satır ' . ($r + 1));
                                $html .= '<td style="border:1px solid #666;padding:2px 4px;font-weight:bold;background-color:' . $themeColor . '15;">' . htmlspecialchars($lb) . '</td>';
                                $rowCells = $cells[$r] ?? array_fill(0, $cols, 'text');
                                for ($c = 0; $c < $cols; $c++) {
                                    $key = $legacyKeys && count($tableBlocks) === 1 ? ('cell_' . $r . '_' . $c) : ('sec_' . $secIndex . '_block_' . $blockIdx . '_cell_' . $r . '_' . $c);
                                    $cellType = $rowCells[$c] ?? 'text';
                                    $val = isset($formData[$key]) ? $formData[$key] : ($cellType === 'label' ? (string)($r + 1) : '');
                                    if ($cellType === 'checkbox') {
                                        $val = $isCheckboxChecked($val) ? '&#9745;' : '&#9744;';
                                    }
                                    if ($cellType === 'signature' && !empty($formData[$key])) {
                                        $val = '<img src="' . htmlspecialchars($formData[$key]) . '" alt="İmza" style="max-width:80px;max-height:32px;" />';
                                        $html .= '<td style="border:1px solid #666;padding:2px 4px;">' . $val . '</td>';
                                    } elseif ($cellType === 'checkbox') {
                                        $html .= '<td style="border:1px solid #666;padding:2px 4px;text-align:center;">' . $val . '</td>';
                                    } else {
                                        $html .= '<td style="border:1px solid #666;padding:2px 4px;word-wrap:break-word;">' . htmlspecialchars($val) . '</td>';
                                    }
                                }
                            } else {
                                $cols = (int)($blk['cols'] ?? 0);
                                $html .= '<td style="border:1px solid #666;padding:2px 4px;"></td>';
                                for ($c = 0; $c < $cols; $c++) {
                                    $html .= '<td style="border:1px solid #666;padding:2px 4px;"></td>';
                                }
                            }
                        }
                        $html .= '</tr>';
                    }
                    $html .= '</tbody></table>';
                } else {
                    $rows = (int)($sec['rows'] ?? 0);
                    $cols = (int)($sec['cols'] ?? 0);
                    $headers = $sec['headers'] ?? array_fill(0, $cols, '');
                    $cells = $sec['cells'] ?? [];
                    $rowLabels = $sec['row_labels'] ?? [];
                    $html .= '<table style="border-collapse:collapse;margin:3px 0;border:1px solid #666;table-layout:fixed;width:100%;font-size:9px;">';
                    $html .= '<thead><tr>';
                    if (!empty($rowLabels)) {
                        $html .= '<th style="background-color:' . $themeColor . '20;border:1px solid ' . $themeColor . ';padding:2px 4px;"></th>';
                    }
                    for ($c = 0; $c < $cols; $c++) {
                        $h = isset($headers[$c]) ? $headers[$c] : ('Sütun ' . ($c + 1));
                        $html .= '<th style="background-color:' . $themeColor . '20;border:1px solid ' . $themeColor . ';padding:2px 4px;">' . htmlspecialchars($h) . '</th>';
                    }
                    $html .= '</tr></thead><tbody>';
                    for ($r = 0; $r < $rows; $r++) {
                        $rowCells = $cells[$r] ?? array_fill(0, $cols, 'text');
                        $html .= '<tr>';
                        if (!empty($rowLabels)) {
                            $lb = isset($rowLabels[$r]) ? $rowLabels[$r] : ('Satır ' . ($r + 1));
                            $html .= '<td style="border:1px solid #666;padding:2px 4px;font-weight:bold;">' . htmlspecialchars($lb) . '</td>';
                        }
                        for ($c = 0; $c < $cols; $c++) {
                            $key = $legacyKeys ? ('cell_' . $r . '_' . $c) : ('sec_' . $secIndex . '_cell_' . $r . '_' . $c);
                            $cellType = $rowCells[$c] ?? 'text';
                            $val = isset($formData[$key]) ? $formData[$key] : ($cellType === 'label' ? (string)($r + 1) : '');
                            if ($cellType === 'checkbox') {
                                $val = $isCheckboxChecked($val) ? '&#9745;' : '&#9744;'; // ☑ / ☐
                            }
                            if ($cellType === 'signature' && !empty($formData[$key])) {
                                $val = '<img src="' . htmlspecialchars($formData[$key]) . '" alt="İmza" style="max-width:80px;max-height:32px;" />';
                                $html .= '<td style="border:1px solid #666;padding:2px 4px;">' . $val . '</td>';
                            } elseif ($cellType === 'checkbox') {
                                $html .= '<td style="border:1px solid #666;padding:2px 4px;text-align:center;">' . $val . '</td>';
                            } else {
                                $html .= '<td style="border:1px solid #666;padding:2px 4px;word-wrap:break-word;">' . htmlspecialchars($val) . '</td>';
                            }
                        }
                        $html .= '</tr>';
                    }
                    $html .= '</tbody></table>';
                }
            }
        }

        $footerBlocks = isset($footerConfig['blocks']) && is_array($footerConfig['blocks']) ? $footerConfig['blocks'] : [];
        $footerThemeColor = $footerConfig['theme_color'] ?? '#4472C4';
        $html .= '<div class="pdf-footer">';
        if (!empty($footerBlocks)) {
            $html .= '<table style="width:100%;border-collapse:collapse;margin:5px 0;font-size:9px;"><tbody>';
            $maxRows = 0;
            foreach ($footerBlocks as $fblk) {
                $maxRows = max($maxRows, count($fblk['fields'] ?? []));
            }
            for ($row = 0; $row < $maxRows; $row++) {
                $html .= '<tr>';
                foreach ($footerBlocks as $fblk) {
                    $fields = $fblk['fields'] ?? [];
                    $f = $fields[$row] ?? null;
                    if ($f) {
                        $k = $f['key'] ?? '';
                        $label = $f['label'] ?? $k;
                        $ft = $f['type'] ?? 'text';
                        $val = isset($formData[$k]) ? $formData[$k] : '';
                        if ($ft === 'checkbox') {
                            $val = $isCheckboxChecked($val) ? '&#9745;' : '&#9744;';
                        } elseif ($ft === 'signature') {
                            $sigImg = ($val && strpos((string)$val, 'data:image') === 0) ? $val : (!empty($contract['signature_data_party2']) ? $contract['signature_data_party2'] : ($contract['signature_data'] ?? ''));
                            if ($sigImg) {
                                $val = '<img src="' . htmlspecialchars($sigImg) . '" alt="İmza" style="max-width:120px;max-height:48px;" />';
                            }
                        } else {
                            $val = nl2br(htmlspecialchars((string) $val));
                        }
                        $isFirstRow = ($row === 0);
                        if ($isFirstRow) {
                            $html .= '<td style="border:1px solid #666;padding:0;width:50%;vertical-align:top;">';
                            $html .= '<div style="background-color:' . htmlspecialchars($footerThemeColor) . ';color:#fff;padding:3px 8px;margin:0;font-size:10px;font-weight:bold;">' . htmlspecialchars($label) . '</div>';
                            $html .= '<div style="padding:4px 8px;">' . $val . '</div>';
                            $html .= '</td>';
                        } else {
                            $labelPart = trim((string)$label) !== '' ? '<strong>' . htmlspecialchars($label) . ':</strong> ' : '';
                            $html .= '<td style="border:1px solid #666;padding:2px 4px;width:50%;">' . $labelPart . $val . '</td>';
                        }
                    } else {
                        $html .= '<td style="border:1px solid #666;padding:2px 4px;width:50%;"></td>';
                    }
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        } else {
            $descKey = $footerConfig['description_key'] ?? 'footer_description';
            $descVal = isset($formData[$descKey]) ? $formData[$descKey] : '';
            $html .= '<p style="margin:0 0 2px;">' . nl2br(htmlspecialchars($descVal)) . '</p>';
            $html .= '<div class="signature-block">';
            $signatures = isset($footerConfig['signatures']) && is_array($footerConfig['signatures']) ? $footerConfig['signatures'] : [ ['label' => 'Emlak İşletmesi İmzası'], ['label' => 'Müşteri İmzası'] ];
            $colCount = count($signatures);
            $colWidth = $colCount > 0 ? (100 / $colCount) : 50;
            $html .= '<table style="width:100%;border:none;margin-top:4px;"><tr>';
            foreach ($signatures as $idx => $sig) {
                $label = isset($sig['label']) ? $sig['label'] : ('İmza ' . ($idx + 1));
                $partyNum = $idx + 1;
                $img = isset($signatureImages[$partyNum]) ? $signatureImages[$partyNum] : '';
                $html .= '<td style="width:' . $colWidth . '%;border:none;vertical-align:top;"><strong>' . htmlspecialchars($label) . '</strong><br/>' . $img . '</td>';
            }
            $html .= '</tr></table>';
            $html .= '</div>';
        }
        $html .= '<p style="margin:4px 0 0; font-size: 8px;"><strong>Durum:</strong> ' . $statusLabel . ' &nbsp; <strong>İmza Tarihi:</strong> ' . $signedAt . '</p>';
        $html .= '</div></body></html>';
        return $html;
    }

    private function buildPdfHtml($contract, $items) {
        require_once dirname(dirname(__DIR__)) . '/includes/functions.php';
        
        $clientTypeLabel = $contract['client_type'] === 'legal' ? 'Tüzel Kişi' : 'Gerçek Kişi';
        $statusLabel = $contract['status'] === 'signed' ? 'İmzalandı' : 'Taslak';
        $signedAt = $contract['signed_at'] ? date('d.m.Y H:i', strtotime($contract['signed_at'])) : '—';
        $signatureImg = '';
        if (!empty($contract['signature_data'])) {
            $signatureImg = '<img src="' . $contract['signature_data'] . '" alt="İmza" style="max-width:280px;max-height:100px;border-bottom:1px solid #333;margin-top:8px;" />';
        }

        // Firma bilgilerini ayarlardan al (şirket bilgileri modül/tema ayarlarından)
        $settings = get_option('site_settings', []);
        // Logo: sadece site ayarları (Ayarlar > Genel > Site Logo)
        $companyLogo = get_option('site_logo', '');
        if (!empty($companyLogo) && strpos($companyLogo, 'http') !== 0) {
            $companyLogo = site_url(ltrim($companyLogo, '/'));
        }
        $companyName = $settings['company_name'] ?? 'Firma Adı';
        $companyPerson = $settings['company_person'] ?? '';
        $companyPhone = $settings['company_phone'] ?? '';
        $companyAddress = $settings['company_address'] ?? '';

        $rows = '';
        foreach ($items as $i => $item) {
            $listingLabel = ($item['listing_type'] ?? 'sale') === 'sale' ? 'Satılık' : 'Kiralık';
            $rows .= '<tr>';
            $rows .= '<td>' . ($i + 1) . '</td>';
            $rows .= '<td>' . htmlspecialchars($item['description'] ?? '') . '</td>';
            $rows .= '<td>' . $listingLabel . '</td>';
            $rows .= '<td>' . number_format((float)($item['price'] ?? 0), 2, ',', '.') . ' ₺</td>';
            $rows .= '</tr>';
        }

        // Logo HTML'si
        $logoHtml = '';
        if (!empty($companyLogo)) {
            $logoHtml = '<div style="text-align: center; margin-bottom: 20px;">
                <img src="' . $companyLogo . '" alt="Logo" style="max-width: 150px; max-height: 100px; object-fit: contain;" />
            </div>';
        }

        // Firma bilgileri bölümü - Tablo formatında
        $companyInfoHtml = '<h2 style="margin-top: 20px; margin-bottom: 10px; font-size: 13px; border-bottom: 2px solid #4472C4; padding-bottom: 8px; color: #4472C4;">EMLAK İŞLETMESİ VEYA SÖZLEŞMELİ İŞLETME</h2>';
        $companyInfoHtml .= '<table style="width: 100%; border-collapse: collapse; margin: 10px 0;">
            <tr>
                <td style="border: 1px solid #000; padding: 8px; width: 50%;"><strong>YETKİLİ KİŞİ:</strong><br>' . htmlspecialchars($companyPerson) . '</td>
                <td style="border: 1px solid #000; padding: 8px; width: 50%;"><strong>TELEFON:</strong><br>' . htmlspecialchars($companyPhone) . '</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 8px; width: 50%;"><strong>ÜNVANI:</strong><br>' . htmlspecialchars($companyName) . '</td>
                <td style="border: 1px solid #000; padding: 8px; width: 50%;"><strong>ADRES:</strong><br>' . htmlspecialchars($companyAddress) . '</td>
            </tr>
        </table>';

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.4; color: #333; padding: 20px; }
        h1 { font-size: 16px; margin-bottom: 16px; border-bottom: 1px solid #333; padding-bottom: 8px; text-align: center; }
        h2 { font-size: 13px; margin: 14px 0 8px; }
        h3 { font-size: 12px; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #666; padding: 6px 8px; text-align: left; }
        th { background: #eee; font-weight: bold; }
        .info p { margin: 4px 0; }
        .total { font-weight: bold; font-size: 12px; margin-top: 10px; }
        .signature-block { margin-top: 24px; }
        </style></head><body>';
        $html .= $logoHtml;
        $html .= '<h1>Komisyon Sözleşmesi</h1>';
        $html .= '<h2>Müşteri Bilgileri (' . $clientTypeLabel . ')</h2>';
        $html .= '<div class="info">';
        $html .= '<p><strong>Ad / Unvan:</strong> ' . htmlspecialchars($contract['client_name']) . '</p>';
        if (!empty($contract['client_tax_number'])) {
            $html .= '<p><strong>Vergi No:</strong> ' . htmlspecialchars($contract['client_tax_number']) . '</p>';
            $html .= '<p><strong>Vergi Dairesi:</strong> ' . htmlspecialchars($contract['client_tax_office'] ?? '') . '</p>';
        }
        if (!empty($contract['client_address'])) {
            $html .= '<p><strong>Adres:</strong> ' . htmlspecialchars($contract['client_address']) . '</p>';
        }
        if (!empty($contract['client_phone'])) {
            $html .= '<p><strong>Telefon:</strong> ' . htmlspecialchars($contract['client_phone']) . '</p>';
        }
        if (!empty($contract['client_email'])) {
            $html .= '<p><strong>E-posta:</strong> ' . htmlspecialchars($contract['client_email']) . '</p>';
        }
        $html .= '</div>';
        $html .= $companyInfoHtml;
        $html .= '<h2>Taşınmazlar</h2>';
        $html .= '<table><thead><tr><th>#</th><th>Taşınmaz Açıklaması</th><th>Tip</th><th>Fiyat / Aylık Kira (₺)</th></tr></thead><tbody>' . $rows . '</tbody></table>';
        $html .= '<div class="signature-block"><p><strong>Müşteri İmzası</strong></p>' . $signatureImg . '</div>';
        $html .= '<p style="margin-top: 16px;"><strong>Durum:</strong> ' . $statusLabel . ' &nbsp; <strong>İmza Tarihi:</strong> ' . $signedAt . '</p>';
        $html .= '</body></html>';
        return $html;
    }
}
