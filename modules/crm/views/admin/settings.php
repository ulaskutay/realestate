<?php
// Settings view
$settings = $settings ?? [];
$webhookUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/api/crm/meta-webhook';
?>

<!-- Header -->
<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div class="flex flex-col gap-2">
        <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">CRM Ayarları</h1>
        <p class="text-gray-500 dark:text-gray-400 text-base">Modül ayarlarını yapılandırın</p>
    </div>
</header>

<!-- Mesaj -->
<?php if (isset($_SESSION['flash_message'])): ?>
<div class="mb-6 p-4 rounded-lg <?php echo ($_SESSION['flash_type'] ?? 'success') === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 text-red-800 dark:text-red-200'; ?>">
    <p class="text-sm font-medium"><?php echo esc_html($_SESSION['flash_message']); ?></p>
</div>
<?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); endif; ?>

<!-- Form -->
<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <form method="POST" action="<?php echo admin_url('module/crm/settings'); ?>" class="space-y-6">
        <!-- Meta Webhook Ayarları -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Meta Lead Ads Webhook Ayarları</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Webhook URL</label>
                    <div class="flex items-center gap-2">
                        <input type="text" value="<?php echo esc_attr($webhookUrl); ?>" readonly class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                        <button type="button" onclick="copyToClipboard('<?php echo esc_js($webhookUrl); ?>')" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            <span class="material-symbols-outlined text-xl">content_copy</span>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Bu URL'yi Meta Lead Ads ayarlarında webhook URL olarak kullanın</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Webhook Verify Token</label>
                    <input type="text" name="meta_webhook_verify_token" value="<?php echo esc_attr($settings['meta_webhook_verify_token'] ?? ''); ?>" placeholder="Meta'da belirlediğiniz verify token" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Meta webhook doğrulama için kullanılacak token</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Webhook Secret</label>
                    <input type="text" name="meta_webhook_secret" value="<?php echo esc_attr($settings['meta_webhook_secret'] ?? ''); ?>" placeholder="Meta App Secret" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Webhook signature doğrulama için Meta App Secret</p>
                </div>
            </div>
        </div>
        
        <!-- Genel Ayarlar -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Genel Ayarlar</h2>
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="auto_create_from_forms" id="auto_create_from_forms" value="1" <?php echo ($settings['auto_create_from_forms'] ?? true) ? 'checked' : ''; ?> class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                    <label for="auto_create_from_forms" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                        Form gönderimlerinden otomatik lead oluştur
                    </label>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Varsayılan Lead Durumu</label>
                    <select name="default_lead_status" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="new" <?php echo ($settings['default_lead_status'] ?? 'new') === 'new' ? 'selected' : ''; ?>>Yeni</option>
                        <option value="contacted" <?php echo ($settings['default_lead_status'] ?? '') === 'contacted' ? 'selected' : ''; ?>>İletişimde</option>
                        <option value="quoted" <?php echo ($settings['default_lead_status'] ?? '') === 'quoted' ? 'selected' : ''; ?>>Teklif Verildi</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- WhatsApp Ayarları -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">WhatsApp Ayarları</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mesaj Şablonu</label>
                <textarea name="whatsapp_message_template" rows="3" placeholder="Merhaba {name}, size nasıl yardımcı olabilirim?" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"><?php echo esc_textarea($settings['whatsapp_message_template'] ?? 'Merhaba {name}, size nasıl yardımcı olabilirim?'); ?></textarea>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{name} değişkeni lead ismi ile değiştirilecektir</p>
            </div>
        </div>
        
        <!-- Butonlar -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                Kaydet
            </button>
        </div>
    </form>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('URL kopyalandı!');
    }, function(err) {
        console.error('Kopyalama hatası:', err);
    });
}
</script>
