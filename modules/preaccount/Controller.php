<?php
/**
 * Ön Muhasebe Modül Controller
 * Emlakçılık için ön muhasebe: hesaplar, kategoriler, hareketler.
 */

class PreaccountModuleController {

    private $moduleInfo;
    private $settings;
    private $db;
    /** @var PreaccountAccount */
    private $accountModel;
    /** @var PreaccountCategory */
    private $categoryModel;
    /** @var PreaccountTransaction */
    private $transactionModel;
    /** @var PreaccountCurrency */
    private $currencyModel;

    public function __construct() {
        if (class_exists('Database')) {
            $this->db = Database::getInstance();
            $this->accountModel = new PreaccountAccount();
            $this->categoryModel = new PreaccountCategory();
            $this->transactionModel = new PreaccountTransaction();
            $this->currencyModel = new PreaccountCurrency();
        }
    }

    public function setModuleInfo($info) {
        $this->moduleInfo = $info;
    }

    public function onLoad() {
        $this->loadSettings();
        if (function_exists('add_action')) {
            add_action('crm_lead_view_after', [$this, 'renderLeadPreaccountBox'], 10, 1);
            add_action('realestate_listing_admin_after', [$this, 'renderListingPreaccountBox'], 10, 1);
        }
    }

    public function onActivate() {
        $this->ensureInitialized();
        $this->currencyModel->createTables();
        $this->accountModel->createTables();
        $this->categoryModel->createTables();
        $this->transactionModel->createTables();
        $this->paymentModel->createTables();
        $this->saveDefaultSettings();
    }

    public function onDeactivate() {}

    public function onUninstall() {
        $this->ensureInitialized();
        $this->transactionModel->dropTables();
        $this->categoryModel->dropTables();
        $this->accountModel->dropTables();
        $this->currencyModel->dropTables();
    }

    private function loadSettings() {
        $defaults = $this->getDefaultSettings();
        if (function_exists('get_module_settings')) {
            $this->settings = get_module_settings('preaccount');
        }
        if (empty($this->settings)) {
            $this->settings = $defaults;
        } else {
            // Yeni eklenen ayar anahtarlarını varsayılanlarla birleştir (enable_listing_link vb.)
            $this->settings = array_merge($defaults, $this->settings);
        }
    }

    private function getDefaultSettings() {
        return [
            'invoice_prefix' => 'FTR',
            'default_tax_rate' => 18,
            'currency' => 'TRY',
            'multi_currency_enabled' => true,
            'enable_crm_link' => true,
            'enable_listing_link' => true,
        ];
    }

    private function saveDefaultSettings() {
        if (!class_exists('ModuleLoader')) return;
        ModuleLoader::getInstance()->saveModuleSettings('preaccount', $this->getDefaultSettings());
    }

    // ---------- Dashboard ----------
    public function admin_index() {
        $this->ensureInitialized();
        $this->requireLogin();
        $dateFrom = date('Y-m-01');
        $dateTo = date('Y-m-t');
        $summary = $this->transactionModel->getSummaryByPeriod($dateFrom, $dateTo);
        $accounts = $this->accountModel->getActive();
        $balances = [];
        foreach ($accounts as $a) {
            $balances[$a['id']] = $this->accountModel->getBalance($a['id']);
        }
        $recentTransactions = $this->transactionModel->getRecent(10);
        $this->adminView('index', [
            'title' => 'Ön Muhasebe Pano',
            'summary' => $summary,
            'accounts' => $accounts,
            'balances' => $balances,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    // ---------- Accounts ----------
    public function admin_accounts() {
        $this->ensureInitialized();
        $this->requireLogin();
        $accounts = $this->accountModel->all('name');
        $balances = [];
        foreach ($accounts as $a) {
            $balances[$a['id']] = $this->accountModel->getBalance($a['id']);
        }
        $currencies = $this->currencyModel->getActive();
        $this->adminView('accounts', [
            'title' => 'Hesaplar',
            'accounts' => $accounts,
            'balances' => $balances,
            'editAccount' => null,
            'currencies' => $currencies,
        ]);
    }

    public function admin_account_edit($id) {
        $this->ensureInitialized();
        $this->requireLogin();
        $editAccount = $this->accountModel->find($id);
        if (!$editAccount) {
            $this->redirect('accounts');
            return;
        }
        $accounts = $this->accountModel->all('name');
        $balances = [];
        foreach ($accounts as $a) {
            $balances[$a['id']] = $this->accountModel->getBalance($a['id']);
        }
        $currencies = $this->currencyModel->getActive();
        $this->adminView('accounts', [
            'title' => 'Hesaplar',
            'accounts' => $accounts,
            'balances' => $balances,
            'editAccount' => $editAccount,
            'currencies' => $currencies,
        ]);
    }

    public function admin_account_store() {
        $this->ensureInitialized();
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('accounts'); return; }
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'type' => $_POST['type'] ?? 'cash',
            'opening_balance' => (float) ($_POST['opening_balance'] ?? 0),
            'currency' => trim($_POST['currency'] ?? 'TRY'),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
        if (empty($data['name'])) {
            $_SESSION['flash_message'] = 'Hesap adı gerekli';
            $_SESSION['flash_type'] = 'error';
            $this->redirect('accounts');
            return;
        }
        $this->accountModel->create($data);
        $_SESSION['flash_message'] = 'Hesap eklendi';
        $_SESSION['flash_type'] = 'success';
        $this->redirect('accounts');
    }

    public function admin_account_update($id) {
        $this->ensureInitialized();
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('accounts'); return; }
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'type' => $_POST['type'] ?? 'cash',
            'opening_balance' => (float) ($_POST['opening_balance'] ?? 0),
            'currency' => trim($_POST['currency'] ?? 'TRY'),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
        if (empty($data['name'])) {
            $_SESSION['flash_message'] = 'Hesap adı gerekli';
            $_SESSION['flash_type'] = 'error';
            $this->redirect('accounts');
            return;
        }
        $this->accountModel->update($id, $data);
        $_SESSION['flash_message'] = 'Hesap güncellendi';
        $_SESSION['flash_type'] = 'success';
        $this->redirect('accounts');
    }

    public function admin_account_delete($id) {
        $this->ensureInitialized();
        $this->requireLogin();
        $this->accountModel->delete($id);
        $_SESSION['flash_message'] = 'Hesap silindi';
        $_SESSION['flash_type'] = 'success';
        $this->redirect('accounts');
    }

    // ---------- Categories ----------
    public function admin_categories() {
        $this->ensureInitialized();
        $this->requireLogin();
        $grouped = $this->categoryModel->getAllGroupedByType();
        $this->adminView('categories', [
            'title' => 'Kategoriler',
            'incomeCategories' => $grouped['income'],
            'expenseCategories' => $grouped['expense'],
            'editCategory' => null,
        ]);
    }

    public function admin_category_edit($id) {
        $this->ensureInitialized();
        $this->requireLogin();
        $editCategory = $this->categoryModel->find($id);
        if (!$editCategory) {
            $this->redirect('categories');
            return;
        }
        $grouped = $this->categoryModel->getAllGroupedByType();
        $this->adminView('categories', [
            'title' => 'Kategoriler',
            'incomeCategories' => $grouped['income'],
            'expenseCategories' => $grouped['expense'],
            'editCategory' => $editCategory,
        ]);
    }

    public function admin_category_store() {
        $this->ensureInitialized();
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('categories'); return; }
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'type' => $_POST['type'] ?? 'expense',
            'account_code' => trim($_POST['account_code'] ?? '') ?: null,
        ];
        if (empty($data['name'])) {
            $_SESSION['flash_message'] = 'Kategori adı gerekli';
            $_SESSION['flash_type'] = 'error';
            $this->redirect('categories');
            return;
        }
        $this->categoryModel->create($data);
        $_SESSION['flash_message'] = 'Kategori eklendi';
        $_SESSION['flash_type'] = 'success';
        $this->redirect('categories');
    }

    public function admin_category_update($id) {
        $this->ensureInitialized();
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('categories'); return; }
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'type' => $_POST['type'] ?? 'expense',
            'account_code' => trim($_POST['account_code'] ?? '') ?: null,
        ];
        if (empty($data['name'])) {
            $_SESSION['flash_message'] = 'Kategori adı gerekli';
            $_SESSION['flash_type'] = 'error';
            $this->redirect('categories');
            return;
        }
        $this->categoryModel->update($id, $data);
        $_SESSION['flash_message'] = 'Kategori güncellendi';
        $_SESSION['flash_type'] = 'success';
        $this->redirect('categories');
    }

    public function admin_category_delete($id) {
        $this->ensureInitialized();
        $this->requireLogin();
        $this->categoryModel->delete($id);
        $_SESSION['flash_message'] = 'Kategori silindi';
        $_SESSION['flash_type'] = 'success';
        $this->redirect('categories');
    }

    // ---------- Currencies (Çoklu para birimi) ----------
    public function admin_currencies() {
        $this->ensureInitialized();
        $this->requireLogin();
        $currencies = $this->currencyModel->all('sort_order');
        $this->adminView('currencies', [
            'title' => 'Para Birimleri',
            'currencies' => $currencies,
        ]);
    }

    public function admin_currency_edit($id) {
        $this->ensureInitialized();
        $this->requireLogin();
        $editCurrency = $this->currencyModel->find($id);
        if (!$editCurrency) {
            $this->redirect('currencies');
            return;
        }
        $currencies = $this->currencyModel->all('sort_order');
        $this->adminView('currencies', [
            'title' => 'Para Birimleri',
            'currencies' => $currencies,
            'editCurrency' => $editCurrency,
        ]);
    }

    public function admin_currency_store() {
        $this->ensureInitialized();
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('currencies'); return; }
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $name = trim($_POST['name'] ?? '');
        if (empty($code) || empty($name)) {
            $_SESSION['flash_message'] = 'Kod ve ad gerekli';
            $_SESSION['flash_type'] = 'error';
            $this->redirect('currencies');
            return;
        }
        $isDefault = isset($_POST['is_default']) ? 1 : 0;
        if ($isDefault) {
            $this->db->query("UPDATE `preaccount_currencies` SET is_default = 0");
        }
        $this->currencyModel->create([
            'code' => $code,
            'name' => $name,
            'symbol' => trim($_POST['symbol'] ?? ''),
            'exchange_rate' => (float) ($_POST['exchange_rate'] ?? 1),
            'is_default' => $isDefault,
            'decimal_places' => (int) ($_POST['decimal_places'] ?? 2),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ]);
        $_SESSION['flash_message'] = 'Para birimi eklendi';
        $_SESSION['flash_type'] = 'success';
        $this->redirect('currencies');
    }

    public function admin_currency_update($id) {
        $this->ensureInitialized();
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('currencies'); return; }
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $name = trim($_POST['name'] ?? '');
        if (empty($code) || empty($name)) {
            $_SESSION['flash_message'] = 'Kod ve ad gerekli';
            $_SESSION['flash_type'] = 'error';
            $this->redirect('currency_edit/' . $id);
            return;
        }
        $isDefault = isset($_POST['is_default']) ? 1 : 0;
        if ($isDefault) {
            $this->db->query("UPDATE `preaccount_currencies` SET is_default = 0 WHERE id != ?", [$id]);
        }
        $this->currencyModel->update($id, [
            'code' => $code,
            'name' => $name,
            'symbol' => trim($_POST['symbol'] ?? ''),
            'exchange_rate' => (float) ($_POST['exchange_rate'] ?? 1),
            'is_default' => $isDefault,
            'decimal_places' => (int) ($_POST['decimal_places'] ?? 2),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ]);
        $_SESSION['flash_message'] = 'Para birimi güncellendi';
        $_SESSION['flash_type'] = 'success';
        $this->redirect('currencies');
    }

    public function admin_currency_delete($id) {
        $this->ensureInitialized();
        $this->requireLogin();
        $c = $this->currencyModel->find($id);
        if ($c && !empty($c['is_default'])) {
            $_SESSION['flash_message'] = 'Varsayılan para birimi silinemez';
            $_SESSION['flash_type'] = 'error';
            $this->redirect('currencies');
            return;
        }
        $this->currencyModel->delete($id);
        $_SESSION['flash_message'] = 'Para birimi silindi';
        $_SESSION['flash_type'] = 'success';
        $this->redirect('currencies');
    }

    // ---------- Transactions ----------
    public function admin_transactions() {
        $this->ensureInitialized();
        $this->requireLogin();
        $page = (int)($_GET['p'] ?? 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $filters = [
            'account_id' => $_GET['account_id'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'type' => $_GET['type'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];
        $total = $this->transactionModel->getCount($filters);
        $transactions = $this->transactionModel->getList($filters, $perPage, $offset);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $accounts = $this->accountModel->getActive();
        $grouped = $this->categoryModel->getAllGroupedByType();
        $filterSummary = $this->transactionModel->getSummaryByFilters($filters);
        $this->adminView('transactions', [
            'title' => 'Hareketler',
            'transactions' => $transactions,
            'accounts' => $accounts,
            'incomeCategories' => $grouped['income'],
            'expenseCategories' => $grouped['expense'],
            'filters' => $filters,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'filterSummary' => $filterSummary,
        ]);
    }

    public function admin_transaction_create() {
        $this->ensureInitialized();
        $this->requireLogin();
        $accounts = $this->accountModel->getActive();
        $grouped = $this->categoryModel->getAllGroupedByType();
        $leadId = !empty($_GET['lead_id']) ? (int) $_GET['lead_id'] : null;
        $listingId = !empty($_GET['listing_id']) ? (int) $_GET['listing_id'] : null;
        $this->adminView('transaction-form', [
            'title' => 'Yeni Hareket',
            'transaction' => null,
            'accounts' => $accounts,
            'incomeCategories' => $grouped['income'],
            'expenseCategories' => $grouped['expense'],
            'leads' => $this->getLeadsForSelect(),
            'listings' => $this->getListingsForSelect(),
            'preselectedLeadId' => $leadId,
            'preselectedListingId' => $listingId,
        ]);
    }

    public function admin_transaction_store() {
        $this->ensureInitialized();
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('transaction_create'); return; }
        $leadId = !empty($_POST['lead_id']) ? (int) $_POST['lead_id'] : null;
        $listingId = !empty($_POST['listing_id']) ? (int) $_POST['listing_id'] : null;
        $referenceType = null;
        $referenceId = null;
        if ($leadId) { $referenceType = 'crm_lead'; $referenceId = $leadId; }
        elseif ($listingId) { $referenceType = 'listing'; $referenceId = $listingId; }
        $data = [
            'account_id' => (int) ($_POST['account_id'] ?? 0),
            'category_id' => !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null,
            'type' => $_POST['type'] === 'income' ? 'income' : 'expense',
            'amount' => (float) ($_POST['amount'] ?? 0),
            'date' => $_POST['date'] ?? date('Y-m-d'),
            'description' => trim($_POST['description'] ?? ''),
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ];
        if ($data['account_id'] <= 0 || $data['amount'] <= 0) {
            $_SESSION['flash_message'] = 'Hesap ve tutar gerekli';
            $_SESSION['flash_type'] = 'error';
            $this->redirect('transaction_create');
            return;
        }
        $this->transactionModel->create($data);
        $_SESSION['flash_message'] = 'Hareket eklendi';
        $_SESSION['flash_type'] = 'success';
        $this->redirect('transactions');
    }

    public function admin_transaction_edit($id) {
        $this->ensureInitialized();
        $this->requireLogin();
        $transaction = $this->transactionModel->find($id);
        if (!$transaction) {
            $_SESSION['flash_message'] = 'Hareket bulunamadı';
            $_SESSION['flash_type'] = 'error';
            $this->redirect('transactions');
            return;
        }
        $accounts = $this->accountModel->getActive();
        $grouped = $this->categoryModel->getAllGroupedByType();
        $this->adminView('transaction-form', [
            'title' => 'Hareket Düzenle',
            'transaction' => $transaction,
            'accounts' => $accounts,
            'incomeCategories' => $grouped['income'],
            'expenseCategories' => $grouped['expense'],
            'leads' => $this->getLeadsForSelect(),
            'listings' => $this->getListingsForSelect(),
            'preselectedLeadId' => null,
            'preselectedListingId' => null,
        ]);
    }

    public function admin_transaction_update($id) {
        $this->ensureInitialized();
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('transactions'); return; }
        $leadId = !empty($_POST['lead_id']) ? (int) $_POST['lead_id'] : null;
        $listingId = !empty($_POST['listing_id']) ? (int) $_POST['listing_id'] : null;
        $referenceType = null;
        $referenceId = null;
        if ($leadId) { $referenceType = 'crm_lead'; $referenceId = $leadId; }
        elseif ($listingId) { $referenceType = 'listing'; $referenceId = $listingId; }
        $data = [
            'account_id' => (int) ($_POST['account_id'] ?? 0),
            'category_id' => !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null,
            'type' => $_POST['type'] === 'income' ? 'income' : 'expense',
            'amount' => (float) ($_POST['amount'] ?? 0),
            'date' => $_POST['date'] ?? date('Y-m-d'),
            'description' => trim($_POST['description'] ?? ''),
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ];
        if ($data['account_id'] <= 0 || $data['amount'] <= 0) {
            $_SESSION['flash_message'] = 'Hesap ve tutar gerekli';
            $_SESSION['flash_type'] = 'error';
            $this->redirect('transaction_edit/' . $id);
            return;
        }
        $this->transactionModel->update($id, $data);
        $_SESSION['flash_message'] = 'Hareket güncellendi';
        $_SESSION['flash_type'] = 'success';
        $this->redirect('transactions');
    }

    public function admin_transaction_delete($id) {
        $this->ensureInitialized();
        $this->requireLogin();
        $this->transactionModel->delete($id);
        $_SESSION['flash_message'] = 'Hareket silindi';
        $_SESSION['flash_type'] = 'success';
        $this->redirect('transactions');
    }

    // ---------- Reports ----------
    public function admin_reports() {
        $this->ensureInitialized();
        $this->requireLogin();
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-t');
        $summary = $this->transactionModel->getSummaryByPeriod($dateFrom, $dateTo);
        $this->adminView('reports', [
            'title' => 'Raporlar',
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'summary' => $summary,
        ]);
    }

    // ---------- Settings ----------
    public function admin_settings() {
        $this->ensureInitialized();
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->settings['invoice_prefix'] = trim($_POST['invoice_prefix'] ?? 'FTR');
            $this->settings['default_tax_rate'] = (float) ($_POST['default_tax_rate'] ?? 18);
            $this->settings['currency'] = trim($_POST['currency'] ?? 'TRY');
            $this->settings['multi_currency_enabled'] = isset($_POST['multi_currency_enabled']);
            $this->settings['enable_crm_link'] = isset($_POST['enable_crm_link']);
            $this->settings['enable_listing_link'] = isset($_POST['enable_listing_link']);
            if (class_exists('ModuleLoader')) {
                ModuleLoader::getInstance()->saveModuleSettings('preaccount', $this->settings);
            }
            $_SESSION['flash_message'] = 'Ayarlar kaydedildi';
            $_SESSION['flash_type'] = 'success';
            $this->redirect('settings');
            return;
        }
        $defaultCurrency = $this->currencyModel->getDefault();
        $this->adminView('settings', [
            'title' => 'Ön Muhasebe Ayarları',
            'settings' => $this->settings,
            'currencies' => $this->currencyModel->getActive(),
            'defaultCurrency' => $defaultCurrency,
        ]);
    }

    private function ensureInitialized() {
        if (!$this->db && class_exists('Database')) {
            $this->db = Database::getInstance();
        }
        if (!$this->accountModel) $this->accountModel = new PreaccountAccount();
        if (!$this->categoryModel) $this->categoryModel = new PreaccountCategory();
        if (!$this->transactionModel) $this->transactionModel = new PreaccountTransaction();
        if (!$this->currencyModel) $this->currencyModel = new PreaccountCurrency();
        if (empty($this->settings)) $this->loadSettings();
    }

    /** CRM lead sayfasında ön muhasebe kutusu */
    public function renderLeadPreaccountBox($lead) {
        if (empty($this->settings['enable_crm_link'])) return;
        $this->ensureInitialized();
        $leadId = (int) ($lead['id'] ?? 0);
        if ($leadId <= 0) return;
        $transactions = $this->transactionModel->getByReference('crm_lead', $leadId);
        $addTransactionUrl = admin_url('module/preaccount/transaction_create', ['lead_id' => $leadId]);
        include __DIR__ . '/views/admin/snippet-lead-preaccount.php';
    }

    /** İlan düzenleme sayfasında ön muhasebe kutusu */
    public function renderListingPreaccountBox($listing) {
        if (empty($this->settings['enable_listing_link'])) return;
        $this->ensureInitialized();
        $listingId = (int) ($listing['id'] ?? 0);
        if ($listingId <= 0) return;
        $transactions = $this->transactionModel->getByReference('listing', $listingId);
        $addTransactionUrl = admin_url('module/preaccount/transaction_create', ['listing_id' => $listingId]);
        include __DIR__ . '/views/admin/snippet-listing-preaccount.php';
    }

    private function getLeadsForSelect() {
        if (!($this->settings['enable_crm_link'] ?? false)) return [];
        try {
            $sql = "SELECT id, name, phone, email FROM crm_leads ORDER BY name LIMIT 500";
            return $this->db->fetchAll($sql);
        } catch (Exception $e) {
            return [];
        }
    }

    private function getListingsForSelect() {
        if (!($this->settings['enable_listing_link'] ?? true)) return [];
        try {
            $sql = "SELECT id, title, slug FROM `realestate_listings` ORDER BY title ASC LIMIT 500";
            return $this->db->fetchAll($sql);
        } catch (Exception $e) {
            error_log("Preaccount getListingsForSelect: " . $e->getMessage());
            return [];
        }
    }

    private function requireLogin() {
        if (!function_exists('is_user_logged_in') || !is_user_logged_in()) {
            header('Location: ' . admin_url('login'));
            exit;
        }
    }

    private function adminView($view, $data = []) {
        $viewPath = __DIR__ . '/views/admin/' . $view . '.php';
        $basePath = dirname(dirname(__DIR__));
        if (!file_exists($viewPath)) {
            echo "View not found: " . $view;
            return;
        }
        extract($data);
        $currentPage = 'module/preaccount';
        $preaccountNavCurrent = ($view === 'transaction-form') ? 'transactions' : $view;
        include $basePath . '/app/views/admin/snippets/header.php';
        ?>
        <div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
            <div class="flex min-h-screen">
                <?php include $basePath . '/app/views/admin/snippets/sidebar.php'; ?>
                <div class="flex-1 flex flex-col lg:ml-64">
                    <?php include $basePath . '/app/views/admin/snippets/top-header.php'; ?>
                    <main class="flex-1 p-4 sm:p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b] overflow-y-auto">
                        <div class="max-w-7xl mx-auto">
                            <?php include __DIR__ . '/views/admin/snippet-preaccount-nav.php'; ?>
                            <?php include $viewPath; ?>
                        </div>
                    </main>
                </div>
            </div>
        </div>
        <?php
        include $basePath . '/app/views/admin/snippets/footer.php';
    }

    private function redirect($action) {
        if (empty($action)) {
            $url = admin_url('module/preaccount');
        } else {
            $url = admin_url('module/preaccount/' . $action);
        }
        header("Location: " . $url);
        exit;
    }
}
