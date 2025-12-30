<?php
/**
 * SEO Modül - Genel Ayarlar
 * Bu dosya modül ayarlar sayfası için özel view olarak kullanılabilir
 */
?>

<div class="space-y-6">
    <!-- Bilgi -->
    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
        <div class="flex items-start gap-3">
            <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">info</span>
            <div>
                <p class="text-blue-800 dark:text-blue-300 font-medium">SEO Modülü</p>
                <p class="text-blue-600 dark:text-blue-400 text-sm mt-1">
                    Detaylı ayarlar için sol menüdeki SEO bölümünü kullanın.
                </p>
            </div>
        </div>
    </div>
    
    <!-- Hızlı Linkler -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="<?php echo admin_url('module/seo/sitemap'); ?>" 
           class="flex items-center gap-3 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-primary transition-colors">
            <span class="material-symbols-outlined text-2xl text-blue-600">account_tree</span>
            <div>
                <p class="font-medium text-gray-900 dark:text-white">Sitemap Ayarları</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">XML sitemap yapılandırması</p>
            </div>
        </a>
        
        <a href="<?php echo admin_url('module/seo/robots'); ?>" 
           class="flex items-center gap-3 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-primary transition-colors">
            <span class="material-symbols-outlined text-2xl text-gray-600">smart_toy</span>
            <div>
                <p class="font-medium text-gray-900 dark:text-white">Robots.txt</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Arama motoru erişim kuralları</p>
            </div>
        </a>
        
        <a href="<?php echo admin_url('module/seo/meta'); ?>" 
           class="flex items-center gap-3 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-primary transition-colors">
            <span class="material-symbols-outlined text-2xl text-purple-600">code</span>
            <div>
                <p class="font-medium text-gray-900 dark:text-white">Meta Taglar</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Title ve description şablonları</p>
            </div>
        </a>
        
        <a href="<?php echo admin_url('module/seo/redirects'); ?>" 
           class="flex items-center gap-3 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-primary transition-colors">
            <span class="material-symbols-outlined text-2xl text-orange-600">redo</span>
            <div>
                <p class="font-medium text-gray-900 dark:text-white">Yönlendirmeler</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">301/302 URL yönlendirmeleri</p>
            </div>
        </a>
        
        <a href="<?php echo admin_url('module/seo/schema'); ?>" 
           class="flex items-center gap-3 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-primary transition-colors">
            <span class="material-symbols-outlined text-2xl text-green-600">data_object</span>
            <div>
                <p class="font-medium text-gray-900 dark:text-white">Schema.org</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Yapılandırılmış veri (JSON-LD)</p>
            </div>
        </a>
        
        <a href="/sitemap.xml" target="_blank"
           class="flex items-center gap-3 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-primary transition-colors">
            <span class="material-symbols-outlined text-2xl text-teal-600">open_in_new</span>
            <div>
                <p class="font-medium text-gray-900 dark:text-white">Sitemap Görüntüle</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">/sitemap.xml</p>
            </div>
        </a>
    </div>
</div>

