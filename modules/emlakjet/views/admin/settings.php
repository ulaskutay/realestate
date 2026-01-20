<?php
// Settings view
$settings = $settings ?? [];
$testResult = $testResult ?? null;
$webhookUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/api/emlakjet/webhook';
$cronUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/api/emlakjet/cron-sync';
?>

<!-- Header -->
<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div class="flex flex-col gap-2">
        <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Emlakjet Ayarları</h1>
        <p class="text-gray-500 dark:text-gray-400 text-base">API bağlantı ayarlarını yapılandırın</p>
    </div>
    <a href="<?php echo admin_url('module/emlakjet'); ?>" class="flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
        <span class="material-symbols-outlined text-xl">arrow_back</span>
        <span class="text-sm font-medium">Dashboard</span>
    </a>
</header>

<!-- Mesaj -->
<?php if (isset($_SESSION['flash_message'])): ?>
<div class="mb-6 p-4 rounded-lg <?php echo ($_SESSION['flash_type'] ?? 'success') === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 text-red-800 dark:text-red-200'; ?>">
    <p class="text-sm font-medium"><?php echo esc_html($_SESSION['flash_message']); ?></p>
</div>
<?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); endif; ?>

<!-- API Test Sonucu -->
<?php if ($testResult): ?>
<div class="mb-6 p-4 rounded-lg <?php echo $testResult['success'] ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 text-red-800 dark:text-red-200'; ?>">
    <p class="text-sm font-medium"><?php echo esc_html($testResult['message']); ?></p>
</div>
<?php endif; ?>

<!-- Form -->
<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <form method="POST" action="<?php echo admin_url('module/emlakjet/settings'); ?>" class="space-y-6">
        <!-- API Ayarları -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">API Ayarları</h2>
            <div class="space-y-4">
                <!-- Test Modu Uyarısı -->
                <?php if ($settings['test_mode'] ?? true): ?>
                <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-yellow-600 dark:text-yellow-400">info</span>
                        <div>
                            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Test Modu Aktif</p>
                            <p class="text-xs text-yellow-700 dark:text-yellow-300 mt-1">
                                Şu anda test modu aktif. Gerçek API çağrıları yapılmaz, mock (sahte) yanıtlar kullanılır. 
                                Gerçek API anahtarlarınızı aldıktan sonra test modunu kapatabilirsiniz.
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="flex items-center">
                    <input type="checkbox" name="test_mode" id="test_mode" value="1" <?php echo ($settings['test_mode'] ?? true) ? 'checked' : ''; ?> class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                    <label for="test_mode" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                        Test Modu (Mock API kullan - gerçek API çağrıları yapılmaz)
                    </label>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">API Key</label>
                    <input type="text" name="api_key" value="<?php echo esc_attr($settings['api_key'] ?? ''); ?>" placeholder="Emlakjet API Key" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" <?php echo ($settings['test_mode'] ?? true) ? 'disabled' : ''; ?>>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        <?php if ($settings['test_mode'] ?? true): ?>
                            <span class="text-yellow-600 dark:text-yellow-400">Test modu aktifken API anahtarları gerekmez</span>
                        <?php else: ?>
                            Emlakjet API anahtarınız
                        <?php endif; ?>
                    </p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">API Secret</label>
                    <input type="password" name="api_secret" value="<?php echo esc_attr($settings['api_secret'] ?? ''); ?>" placeholder="Emlakjet API Secret" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" <?php echo ($settings['test_mode'] ?? true) ? 'disabled' : ''; ?>>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        <?php if ($settings['test_mode'] ?? true): ?>
                            <span class="text-yellow-600 dark:text-yellow-400">Test modu aktifken API anahtarları gerekmez</span>
                        <?php else: ?>
                            Emlakjet API secret anahtarınız
                        <?php endif; ?>
                    </p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">API URL</label>
                    <input type="text" name="api_url" value="<?php echo esc_attr($settings['api_url'] ?? 'https://api.emlakjet.com/v1'); ?>" placeholder="https://api.emlakjet.com/v1" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" <?php echo ($settings['test_mode'] ?? true) ? 'disabled' : ''; ?>>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Emlakjet API endpoint URL'i</p>
                </div>
                
                <div>
                    <a href="<?php echo admin_url('module/emlakjet/settings?test_api=1'); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                        <span class="material-symbols-outlined text-xl">check_circle</span>
                        <span>API Bağlantısını Test Et</span>
                    </a>
                    <?php if ($settings['test_mode'] ?? true): ?>
                        <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">(Test modu aktif - Mock API kullanılıyor)</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Webhook Ayarları -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Webhook Ayarları</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Webhook URL</label>
                    <div class="flex items-center gap-2">
                        <input type="text" value="<?php echo esc_attr($webhookUrl); ?>" readonly class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                        <button type="button" onclick="copyToClipboard('<?php echo esc_js($webhookUrl); ?>')" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            <span class="material-symbols-outlined text-xl">content_copy</span>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Bu URL'yi Emlakjet webhook ayarlarında kullanın</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Webhook Secret</label>
                    <input type="text" name="webhook_secret" value="<?php echo esc_attr($settings['webhook_secret'] ?? ''); ?>" placeholder="Webhook signature doğrulama için secret" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Webhook signature doğrulama için kullanılacak secret</p>
                </div>
            </div>
        </div>
        
        <!-- Cron Ayarları -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Otomatik Senkronizasyon</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cron URL</label>
                    <div class="flex items-center gap-2">
                        <input type="text" value="<?php echo esc_attr($cronUrl . '?secret=' . ($settings['cron_secret'] ?? '')); ?>" readonly class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                        <button type="button" onclick="copyToClipboard('<?php echo esc_js($cronUrl . '?secret=' . ($settings['cron_secret'] ?? '')); ?>')" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            <span class="material-symbols-outlined text-xl">content_copy</span>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Bu URL'yi cron job olarak ayarlayın (örn: her 5 dakikada bir)</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cron Secret</label>
                    <input type="text" name="cron_secret" value="<?php echo esc_attr($settings['cron_secret'] ?? ''); ?>" placeholder="Cron endpoint güvenliği için secret" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Cron endpoint güvenliği için kullanılacak secret</p>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="auto_sync_enabled" id="auto_sync_enabled" value="1" <?php echo ($settings['auto_sync_enabled'] ?? false) ? 'checked' : ''; ?> class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                    <label for="auto_sync_enabled" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                        Otomatik senkronizasyonu etkinleştir
                    </label>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Senkronizasyon Aralığı (dakika)</label>
                    <input type="number" name="auto_sync_interval" value="<?php echo esc_attr($settings['auto_sync_interval'] ?? 60); ?>" min="1" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
            </div>
        </div>
        
        <!-- Genel Ayarlar -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Genel Ayarlar</h2>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="sync_on_publish" id="sync_on_publish" value="1" <?php echo ($settings['sync_on_publish'] ?? true) ? 'checked' : ''; ?> class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                    <label for="sync_on_publish" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                        İlan yayınlandığında otomatik senkronize et
                    </label>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="sync_on_update" id="sync_on_update" value="1" <?php echo ($settings['sync_on_update'] ?? true) ? 'checked' : ''; ?> class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                    <label for="sync_on_update" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                        İlan güncellendiğinde otomatik senkronize et
                    </label>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Varsayılan Senkronizasyon Yönü</label>
                    <select name="default_sync_direction" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="push" <?php echo ($settings['default_sync_direction'] ?? 'push') === 'push' ? 'selected' : ''; ?>>Push (Sistemden Emlakjet'e)</option>
                        <option value="pull" <?php echo ($settings['default_sync_direction'] ?? '') === 'pull' ? 'selected' : ''; ?>>Pull (Emlakjet'ten Sisteme)</option>
                        <option value="both" <?php echo ($settings['default_sync_direction'] ?? '') === 'both' ? 'selected' : ''; ?>>Both (Çift Yönlü)</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Retry Deneme Sayısı</label>
                    <input type="number" name="retry_attempts" value="<?php echo esc_attr($settings['retry_attempts'] ?? 3); ?>" min="1" max="10" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
            </div>
        </div>
        
        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                Ayarları Kaydet
            </button>
        </div>
    </form>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Kopyalandı!');
    }, function(err) {
        console.error('Kopyalama hatası:', err);
    });
}
</script>
