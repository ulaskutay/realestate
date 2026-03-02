<?php
$navCurrent = $preaccountNavCurrent ?? 'index';
$base = 'module/preaccount';
$links = [
    'index' => ['slug' => '', 'label' => 'Pano', 'icon' => 'dashboard'],
    'accounts' => ['slug' => 'accounts', 'label' => 'Hesaplar', 'icon' => 'account_balance'],
    'categories' => ['slug' => 'categories', 'label' => 'Kategoriler', 'icon' => 'category'],
    'currencies' => ['slug' => 'currencies', 'label' => 'Para Birimleri', 'icon' => 'payments'],
    'transactions' => ['slug' => 'transactions', 'label' => 'Hareketler', 'icon' => 'list'],
    'reports' => ['slug' => 'reports', 'label' => 'Raporlar', 'icon' => 'bar_chart'],
    'settings' => ['slug' => 'settings', 'label' => 'Ayarlar', 'icon' => 'settings'],
];
?>
<nav class="flex flex-wrap items-center gap-2 mb-6 p-3 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
    <?php foreach ($links as $key => $item): ?>
        <?php
        $url = admin_url($base . ($item['slug'] ? '/' . $item['slug'] : ''));
        $isActive = ($key === $navCurrent);
        ?>
        <a href="<?php echo esc_attr($url); ?>"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $isActive ? 'bg-primary text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'; ?>">
            <span class="material-symbols-outlined text-lg"><?php echo $item['icon']; ?></span>
            <span><?php echo esc_html($item['label']); ?></span>
        </a>
    <?php endforeach; ?>
    <a href="<?php echo esc_attr(admin_url($base . '/transaction_create')); ?>"
       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium bg-green-600 text-white hover:bg-green-700 transition-colors ml-auto">
        <span class="material-symbols-outlined text-lg">add</span>
        <span>Yeni Hareket</span>
    </a>
</nav>
