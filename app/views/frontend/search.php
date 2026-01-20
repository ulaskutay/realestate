<?php
/**
 * Arama Sonuçları Sayfası
 */
?>
<!-- Search Header -->
<section class="relative bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 py-24 overflow-hidden">
    <!-- Animated Background Pattern -->
    <div class="absolute inset-0">
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-white/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
    </div>
    
    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center max-w-4xl mx-auto">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/20 backdrop-blur-md text-white text-sm font-bold rounded-full mb-6 border border-white/30 shadow-lg">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                </svg>
                <span>Arama Sonuçları</span>
            </div>
            
            <!-- Main Title -->
            <h1 class="text-5xl md:text-7xl font-black text-white mb-6 leading-tight tracking-tight">
                <?php if (!empty($query)): ?>
                    "<?php echo esc_html($query); ?>" için
                <?php else: ?>
                    Arama
                <?php endif; ?>
            </h1>
            
            <!-- Subtitle -->
            <p class="text-xl md:text-2xl text-white/90 font-medium max-w-3xl mx-auto leading-relaxed">
                <?php if (!empty($query)): ?>
                    <span class="font-bold"><?php echo number_format($totalResults, 0, ',', '.'); ?></span> sonuç bulundu
                <?php else: ?>
                    Arama yapmak için bir terim girin
                <?php endif; ?>
            </p>
            
            <!-- Search Bar -->
            <div class="mt-10 max-w-2xl mx-auto">
                <form action="/search" method="GET" class="relative">
                    <input type="text" 
                           name="q" 
                           value="<?php echo esc_attr($query); ?>"
                           placeholder="Aradığınız konuyu yazın..."
                           class="w-full px-6 py-5 pl-14 rounded-2xl bg-white/95 backdrop-blur-sm text-gray-800 placeholder-gray-500 focus:outline-none focus:ring-4 focus:ring-white/50 shadow-2xl text-lg font-medium">
                    <svg class="absolute left-5 top-1/2 transform -translate-y-1/2 text-gray-400 w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <button type="submit" class="absolute right-3 top-1/2 transform -translate-y-1/2 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold rounded-xl hover:shadow-lg transition-all">
                        Ara
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Search Results -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <?php if (empty($query)): ?>
            <!-- No Query -->
            <div class="bg-white rounded-3xl p-20 text-center shadow-xl border border-gray-100 max-w-2xl mx-auto">
                <div class="inline-flex items-center justify-center w-32 h-32 bg-gradient-to-br from-purple-100 to-pink-100 rounded-full mb-8">
                    <svg class="text-purple-600 w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-black text-gray-800 mb-4">Arama Yapın</h2>
                <p class="text-gray-600 text-lg max-w-md mx-auto">Yukarıdaki arama kutusuna aradığınız terimi yazın ve sonuçları görün.</p>
            </div>
        <?php elseif ($totalResults === 0): ?>
            <!-- No Results -->
            <div class="bg-white rounded-3xl p-20 text-center shadow-xl border border-gray-100 max-w-2xl mx-auto">
                <div class="inline-flex items-center justify-center w-32 h-32 bg-gradient-to-br from-purple-100 to-pink-100 rounded-full mb-8">
                    <svg class="text-purple-600 w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-black text-gray-800 mb-4">Sonuç Bulunamadı</h2>
                <p class="text-gray-600 text-lg max-w-md mx-auto mb-6">
                    "<strong><?php echo esc_html($query); ?></strong>" için sonuç bulunamadı. Farklı terimler deneyebilirsiniz.
                </p>
                <a href="/" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold rounded-xl hover:shadow-lg transition-all">
                    <span>Ana Sayfaya Dön</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
            </div>
        <?php else: ?>
            <!-- Results -->
            <div class="max-w-5xl mx-auto space-y-8">
                
                <!-- Blog Posts -->
                <?php if (!empty($posts)): ?>
                <div>
                    <h2 class="text-2xl font-black text-gray-900 mb-6 flex items-center gap-3">
                        <svg class="w-7 h-7 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"/>
                        </svg>
                        <span>Yazılar (<?php echo count($posts); ?>)</span>
                    </h2>
                    <div class="space-y-6">
                        <?php foreach ($posts as $post): ?>
                        <article class="bg-white rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 group border border-gray-100">
                            <div class="p-6">
                                <?php if (!empty($post['category_name'])): ?>
                                <a href="/blog/kategori/<?php echo esc_attr($post['category_slug'] ?? ''); ?>" 
                                   class="inline-flex items-center gap-1 w-fit px-3 py-1.5 bg-gradient-to-r from-purple-100 to-pink-100 text-purple-700 text-xs font-bold rounded-full mb-4 hover:from-purple-200 hover:to-pink-200 transition-all">
                                    <?php echo esc_html($post['category_name']); ?>
                                </a>
                                <?php endif; ?>
                                
                                <h3 class="text-xl font-black text-gray-900 mb-3 leading-tight">
                                    <a href="/blog/<?php echo esc_attr($post['slug']); ?>" 
                                       class="hover:text-purple-600 transition-colors">
                                        <?php echo esc_html($post['title']); ?>
                                    </a>
                                </h3>
                                
                                <?php if (!empty($post['excerpt'])): ?>
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2 leading-relaxed">
                                    <?php echo esc_html($post['excerpt']); ?>
                                </p>
                                <?php endif; ?>
                                
                                <div class="flex items-center justify-between text-xs pt-4 border-t border-gray-100">
                                    <span class="flex items-center gap-1.5 text-gray-500 font-medium">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <?php echo turkish_date($post['published_at'] ?? $post['created_at']); ?>
                                    </span>
                                    <a href="/blog/<?php echo esc_attr($post['slug']); ?>" 
                                       class="flex items-center gap-1 text-purple-600 font-bold hover:gap-2 transition-all group">
                                        <span>Oku</span>
                                        <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Pages -->
                <?php if (!empty($pages)): ?>
                <div>
                    <h2 class="text-2xl font-black text-gray-900 mb-6 flex items-center gap-3">
                        <svg class="w-7 h-7 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Sayfalar (<?php echo count($pages); ?>)</span>
                    </h2>
                    <div class="space-y-6">
                        <?php foreach ($pages as $page): ?>
                        <article class="bg-white rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 group border border-gray-100">
                            <div class="p-6">
                                <h3 class="text-xl font-black text-gray-900 mb-3 leading-tight">
                                    <a href="/<?php echo esc_attr($page['slug']); ?>" 
                                       class="hover:text-indigo-600 transition-colors">
                                        <?php echo esc_html($page['title']); ?>
                                    </a>
                                </h3>
                                
                                <?php if (!empty($page['excerpt'])): ?>
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2 leading-relaxed">
                                    <?php echo esc_html($page['excerpt']); ?>
                                </p>
                                <?php endif; ?>
                                
                                <div class="flex items-center justify-between text-xs pt-4 border-t border-gray-100">
                                    <span class="flex items-center gap-1.5 text-gray-500 font-medium">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <?php echo turkish_date($page['created_at']); ?>
                                    </span>
                                    <a href="/<?php echo esc_attr($page['slug']); ?>" 
                                       class="flex items-center gap-1 text-indigo-600 font-bold hover:gap-2 transition-all group">
                                        <span>Görüntüle</span>
                                        <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Real Estate Listings -->
                <?php if (!empty($listings)): ?>
                <div>
                    <h2 class="text-2xl font-black text-gray-900 mb-6 flex items-center gap-3">
                        <svg class="w-7 h-7 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                        <span>Emlak İlanları (<?php echo count($listings); ?>)</span>
                    </h2>
                    <div class="space-y-6">
                        <?php foreach ($listings as $listing): ?>
                        <article class="bg-white rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 group border border-gray-100">
                            <div class="p-6">
                                <h3 class="text-xl font-black text-gray-900 mb-3 leading-tight">
                                    <a href="/ilanlar/<?php echo esc_attr($listing['slug'] ?? ''); ?>" 
                                       class="hover:text-blue-600 transition-colors">
                                        <?php echo esc_html($listing['title'] ?? 'İlan'); ?>
                                    </a>
                                </h3>
                                
                                <?php if (!empty($listing['location'])): ?>
                                <p class="text-gray-600 text-sm mb-2 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <?php echo esc_html($listing['location']); ?>
                                </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($listing['description'])): ?>
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2 leading-relaxed">
                                    <?php echo esc_html($listing['description']); ?>
                                </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($listing['price'])): ?>
                                <p class="text-lg font-black text-blue-600 mb-4">
                                    <?php echo number_format($listing['price'], 0, ',', '.'); ?> ₺
                                </p>
                                <?php endif; ?>
                                
                                <a href="/ilanlar/<?php echo esc_attr($listing['slug'] ?? ''); ?>" 
                                   class="inline-flex items-center gap-1 text-blue-600 font-bold hover:gap-2 transition-all group">
                                    <span>Detayları Gör</span>
                                    <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </a>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
        <?php endif; ?>
    </div>
</section>
