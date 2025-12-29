<?php
/**
 * Statik Sayfa Görünümü
 */
?>
<!-- Sayfa Header -->
<section class="bg-gradient-to-r from-brand-purple to-purple-600 py-16">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
            <!-- Başlık -->
            <h1 class="text-3xl md:text-5xl font-bold text-white leading-tight">
                <?php echo esc_html($page['title']); ?>
            </h1>
            
            <!-- Özet -->
            <?php if (!empty($page['excerpt'])): ?>
            <p class="text-purple-100 text-lg mt-4 max-w-2xl mx-auto">
                <?php echo esc_html($page['excerpt']); ?>
            </p>
            <?php endif; ?>
            
            <!-- Meta Bilgileri -->
            <div class="flex items-center justify-center gap-6 mt-6 text-purple-100">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined">calendar_today</span>
                    <?php echo date('d F Y', strtotime($page['published_at'] ?? $page['created_at'])); ?>
                </span>
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined">visibility</span>
                    <?php echo number_format($page['views']); ?> görüntülenme
                </span>
            </div>
        </div>
    </div>
</section>

<!-- Öne Çıkan Görsel -->
<?php if (!empty($page['featured_image'])): ?>
<div class="container mx-auto px-4 -mt-8">
    <div class="max-w-4xl mx-auto">
        <img src="<?php echo esc_url($page['featured_image']); ?>" 
             alt="<?php echo esc_attr($page['title']); ?>" 
             class="w-full h-auto max-h-[500px] object-cover rounded-xl shadow-2xl">
    </div>
</div>
<?php endif; ?>

<!-- İçerik -->
<section class="py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <article class="bg-white rounded-xl p-8 shadow-sm">
                <!-- İçerik -->
                <div class="prose prose-lg max-w-none prose-headings:text-gray-800 prose-p:text-gray-600 prose-a:text-brand-purple prose-img:rounded-lg">
                    <?php echo $page['content']; ?>
                </div>
                
                <!-- Özel Alanlar -->
                <?php if (!empty($customFields)): ?>
                <div class="mt-8 pt-8 border-t border-gray-100">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Ek Bilgiler</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <?php foreach ($customFields as $key => $value): ?>
                            <?php if (!empty($value) && $value !== '0'): ?>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <span class="text-sm font-medium text-gray-500 uppercase tracking-wide block mb-1">
                                    <?php 
                                    // Key'i label'a çevir (basit bir çeviri)
                                    $label = str_replace('_', ' ', $key);
                                    $label = ucwords($label);
                                    echo esc_html($label); 
                                    ?>
                                </span>
                                <span class="text-gray-800">
                                    <?php 
                                    // Eğer URL ise link olarak göster
                                    if (filter_var($value, FILTER_VALIDATE_URL)) {
                                        echo '<a href="' . esc_url($value) . '" target="_blank" class="text-brand-purple hover:underline">' . esc_html($value) . '</a>';
                                    } elseif (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                        echo '<a href="mailto:' . esc_attr($value) . '" class="text-brand-purple hover:underline">' . esc_html($value) . '</a>';
                                    } else {
                                        echo esc_html($value);
                                    }
                                    ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Paylaşım -->
                <div class="mt-8 pt-8 border-t border-gray-100">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 font-medium">Bu sayfayı paylaş:</span>
                        <div class="flex gap-3">
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . '/' . $page['slug']); ?>&text=<?php echo urlencode($page['title']); ?>" 
                               target="_blank"
                               class="w-10 h-10 flex items-center justify-center bg-[#1DA1F2] text-white rounded-full hover:opacity-90 transition-opacity">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . '/' . $page['slug']); ?>" 
                               target="_blank"
                               class="w-10 h-10 flex items-center justify-center bg-[#1877F2] text-white rounded-full hover:opacity-90 transition-opacity">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                            <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . '/' . $page['slug']); ?>&title=<?php echo urlencode($page['title']); ?>" 
                               target="_blank"
                               class="w-10 h-10 flex items-center justify-center bg-[#0A66C2] text-white rounded-full hover:opacity-90 transition-opacity">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </div>
</section>

