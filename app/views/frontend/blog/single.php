<?php
/**
 * Tekil Yazı Sayfası - Minimal & Clean
 */

// Tema rengi
$themeColor = get_option('theme_color', '#137fec');
?>

<style>
:root {
    --blog-accent: <?php echo $themeColor; ?>;
}
</style>

<!-- Header -->
<section class="pt-32 pb-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-2 text-sm text-gray-500 mb-8">
                <a href="/" class="hover:text-gray-900 transition-colors">Ana Sayfa</a>
                <span>/</span>
                <a href="/blog" class="hover:text-gray-900 transition-colors">Blog</a>
                <?php if (!empty($post['category_name'])): ?>
                <span>/</span>
                <a href="/blog/kategori/<?php echo esc_attr($post['category_slug']); ?>" 
                   class="hover:text-gray-900 transition-colors">
                    <?php echo esc_html($post['category_name']); ?>
                </a>
                <?php endif; ?>
            </nav>
            
            <!-- Kategori Badge -->
            <?php if (!empty($post['category_name'])): ?>
            <a href="/blog/kategori/<?php echo esc_attr($post['category_slug']); ?>" 
               class="inline-block px-3 py-1 text-xs font-semibold uppercase tracking-wider rounded-full mb-6"
               style="background-color: <?php echo $themeColor; ?>15; color: <?php echo $themeColor; ?>;">
                <?php echo esc_html($post['category_name']); ?>
            </a>
            <?php endif; ?>
            
            <!-- Başlık -->
            <h1 class="text-3xl md:text-5xl font-bold text-gray-900 leading-tight mb-6">
                <?php echo esc_html($post['title']); ?>
            </h1>
            
            <!-- Özet -->
            <?php if (!empty($post['excerpt'])): ?>
            <p class="text-xl text-gray-600 leading-relaxed mb-8">
                <?php echo esc_html($post['excerpt']); ?>
            </p>
            <?php endif; ?>
            
            <!-- Meta -->
            <div class="flex flex-wrap items-center gap-6 text-sm text-gray-500 pb-8 border-b border-gray-200">
                <?php if (!empty($post['author_name'])): ?>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                        <span class="text-xs font-semibold text-gray-600">
                            <?php echo strtoupper(substr($post['author_name'], 0, 1)); ?>
                        </span>
                    </div>
                    <span class="font-medium text-gray-700"><?php echo esc_html($post['author_name']); ?></span>
                </div>
                <?php endif; ?>
                
                <time class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <?php echo turkish_date($post['published_at'] ?? $post['created_at'], 'long'); ?>
                </time>
                
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <?php echo number_format($post['views']); ?>
                </span>
            </div>
        </div>
    </div>
</section>

<!-- Öne Çıkan Görsel -->
<?php if (!empty($post['featured_image'])): ?>
<div class="container mx-auto px-4 mb-12">
    <div class="max-w-4xl mx-auto">
        <img src="<?php echo esc_url($post['featured_image']); ?>" 
             alt="<?php echo esc_attr($post['title']); ?>" 
             class="w-full rounded-2xl shadow-lg">
    </div>
</div>
<?php endif; ?>

<!-- İçerik -->
<section class="pb-16">
    <div class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row gap-16">
            
            <!-- Ana İçerik -->
            <article class="flex-1 max-w-3xl mx-auto lg:mx-0">
                <!-- İçerik -->
                <div class="prose prose-lg max-w-none
                            prose-headings:font-bold prose-headings:text-gray-900
                            prose-h2:text-2xl prose-h2:mt-12 prose-h2:mb-4
                            prose-h3:text-xl prose-h3:mt-8 prose-h3:mb-3
                            prose-p:text-gray-700 prose-p:leading-relaxed
                            prose-a:font-medium prose-a:no-underline hover:prose-a:underline
                            prose-img:rounded-xl
                            prose-blockquote:border-l-2 prose-blockquote:pl-6 prose-blockquote:italic prose-blockquote:text-gray-600
                            prose-code:bg-gray-100 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded prose-code:text-sm
                            prose-pre:bg-gray-900 prose-pre:rounded-xl"
                     style="--tw-prose-links: <?php echo $themeColor; ?>;">
                    <?php echo $post['content']; ?>
                </div>
                
                <!-- Tags -->
                <?php if (!empty($post['tags'])): ?>
                <div class="mt-12 pt-8 border-t border-gray-200">
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($post['tags'] as $tag): ?>
                        <a href="/blog/etiket/<?php echo esc_attr($tag['slug']); ?>" 
                           class="px-3 py-1.5 text-sm text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                            #<?php echo esc_html($tag['name']); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Paylaşım -->
                <div class="mt-8 pt-8 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">Paylaş</span>
                        <div class="flex items-center gap-2">
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . '/blog/' . $post['slug']); ?>&text=<?php echo urlencode($post['title']); ?>" 
                               target="_blank" rel="noopener"
                               class="w-10 h-10 flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all"
                               title="Twitter'da Paylaş">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . '/blog/' . $post['slug']); ?>" 
                               target="_blank" rel="noopener"
                               class="w-10 h-10 flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all"
                               title="Facebook'ta Paylaş">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                            <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . '/blog/' . $post['slug']); ?>&title=<?php echo urlencode($post['title']); ?>" 
                               target="_blank" rel="noopener"
                               class="w-10 h-10 flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all"
                               title="LinkedIn'de Paylaş">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                            </a>
                            <button onclick="navigator.clipboard.writeText(window.location.href).then(() => { this.innerHTML = '<svg class=\'w-5 h-5 text-green-500\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M5 13l4 4L19 7\'/></svg>'; setTimeout(() => { this.innerHTML = '<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1\'/></svg>'; }, 2000); })"
                                    class="w-10 h-10 flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all"
                                    title="Linki Kopyala">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </article>
            
            <!-- Sidebar -->
            <aside class="w-full lg:w-80 flex-shrink-0 space-y-8">
                
                <!-- Kategoriler -->
                <?php if (!empty($categories)): ?>
                <div class="bg-gray-50 rounded-2xl p-6">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">
                        Kategoriler
                    </h3>
                    <ul class="space-y-1">
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="/blog/kategori/<?php echo esc_attr($cat['slug']); ?>" 
                               class="flex items-center justify-between py-2 text-gray-600 hover:text-gray-900 transition-colors">
                                <span><?php echo esc_html($cat['name']); ?></span>
                                <?php if (!empty($cat['post_count'])): ?>
                                <span class="text-xs text-gray-400"><?php echo $cat['post_count']; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <!-- Son Yazılar -->
                <?php if (!empty($recentPosts)): ?>
                <div class="bg-gray-50 rounded-2xl p-6">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">
                        Son Yazılar
                    </h3>
                    <ul class="space-y-4">
                        <?php foreach ($recentPosts as $recent): ?>
                        <li>
                            <a href="/blog/<?php echo esc_attr($recent['slug']); ?>" class="group flex gap-4">
                                <?php if (!empty($recent['featured_image'])): ?>
                                <img src="<?php echo esc_url($recent['featured_image']); ?>" 
                                     alt="" 
                                     class="w-16 h-16 object-cover rounded-lg flex-shrink-0" loading="lazy">
                                <?php else: ?>
                                <div class="w-16 h-16 bg-gray-200 rounded-lg flex-shrink-0 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                                    </svg>
                                </div>
                                <?php endif; ?>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 group-hover:underline line-clamp-2 leading-snug">
                                        <?php echo esc_html($recent['title']); ?>
                                    </h4>
                                    <time class="text-xs text-gray-500 mt-1 block">
                                        <?php echo turkish_date($recent['published_at'] ?? $recent['created_at']); ?>
                                    </time>
                                </div>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
            </aside>
        </div>
    </div>
</section>

<!-- İlgili Yazılar -->
<?php if (!empty($relatedPosts)): ?>
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-2xl font-bold text-gray-900 mb-8">Benzer Yazılar</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach ($relatedPosts as $related): ?>
                <article class="group">
                    <a href="/blog/<?php echo esc_attr($related['slug']); ?>" class="block">
                        <?php if (!empty($related['featured_image'])): ?>
                        <div class="aspect-[16/10] rounded-xl overflow-hidden mb-4">
                            <img src="<?php echo esc_url($related['featured_image']); ?>" 
                                 alt="<?php echo esc_attr($related['title']); ?>" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                        </div>
                        <?php else: ?>
                        <div class="aspect-[16/10] rounded-xl bg-gray-200 mb-4 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                            </svg>
                        </div>
                        <?php endif; ?>
                        <h3 class="font-semibold text-gray-900 group-hover:underline line-clamp-2 mb-2">
                            <?php echo esc_html($related['title']); ?>
                        </h3>
                        <time class="text-sm text-gray-500">
                            <?php echo turkish_date($related['published_at'] ?? $related['created_at']); ?>
                        </time>
                    </a>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
