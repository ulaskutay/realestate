<?php
/**
 * Kategori Sayfası - Premium Design
 */
?>
<!-- Kategori Header - Premium Gradient -->
<section class="relative bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 py-24 overflow-hidden">
    <!-- Animated Background Pattern -->
    <div class="absolute inset-0">
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-white/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-white/5 rounded-full blur-3xl"></div>
    </div>
    
    <!-- Grid Pattern Overlay -->
    <div class="absolute inset-0 bg-grid-white/[0.05] bg-[size:30px_30px]"></div>
    
    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center max-w-4xl mx-auto">
            <!-- Breadcrumb -->
            <div class="flex items-center justify-center gap-3 text-white/80 text-sm font-medium mb-6">
                <a href="/blog" class="hover:text-white transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-base">home</span>
                    <span>Blog</span>
                </a>
                <span class="material-symbols-outlined text-xs">chevron_right</span>
                <span class="text-white font-bold">Kategori</span>
            </div>
            
            <!-- Kategori İkonu & Badge -->
            <div class="inline-flex items-center justify-center mb-6">
                <div class="relative">
                    <div class="w-24 h-24 bg-white/20 backdrop-blur-md rounded-3xl flex items-center justify-center border-2 border-white/30 shadow-2xl">
                        <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                        </svg>
                    </div>
                    <!-- Pulse Ring -->
                    <div class="absolute inset-0 w-24 h-24 bg-white/10 rounded-3xl animate-ping"></div>
                </div>
            </div>
            
            <!-- Kategori Adı -->
            <h1 class="text-5xl md:text-7xl font-black text-white mb-6 leading-tight tracking-tight">
                <?php echo esc_html($category['name']); ?>
            </h1>
            
            <!-- Açıklama -->
            <?php if (!empty($category['description'])): ?>
            <p class="text-xl md:text-2xl text-white/90 font-medium max-w-3xl mx-auto leading-relaxed mb-8">
                <?php echo esc_html($category['description']); ?>
            </p>
            <?php endif; ?>
            
            <!-- Yazı Sayısı Badge -->
            <div class="inline-flex items-center gap-3 px-6 py-3 bg-white/20 backdrop-blur-md rounded-full border border-white/30 shadow-xl">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                </svg>
                <span class="text-white font-bold text-lg">
                    <?php echo count($posts); ?> <?php echo count($posts) === 1 ? 'Yazı' : 'Yazı'; ?>
                </span>
            </div>
        </div>
    </div>
</section>

<!-- Yazılar -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row gap-12">
            
            <!-- Ana İçerik -->
            <div class="flex-1">
                <?php if (empty($posts)): ?>
                <div class="bg-white rounded-3xl p-20 text-center shadow-xl border border-gray-100">
                    <div class="inline-flex items-center justify-center w-32 h-32 bg-gradient-to-br from-purple-100 to-pink-100 rounded-full mb-8">
                        <span class="material-symbols-outlined text-purple-600 text-6xl">article</span>
                    </div>
                    <h2 class="text-3xl font-black text-gray-800 mb-4">Bu kategoride yazı yok</h2>
                    <p class="text-gray-600 text-lg max-w-md mx-auto mb-8">
                        Yakında bu kategoride ilginizi çekecek harika yazılar eklenecek. Takipte kalın!
                    </p>
                    <a href="/blog" 
                       class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-black rounded-2xl hover:shadow-2xl hover:scale-105 transition-all">
                        <span class="material-symbols-outlined">arrow_back</span>
                        <span>Tüm Yazılara Dön</span>
                    </a>
                </div>
                <?php else: ?>
                
                <!-- Yazı Grid - Premium Layout -->
                <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-8">
                    <?php foreach ($posts as $post): ?>
                    <article class="bg-white rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 group flex flex-col border border-gray-100 hover:border-purple-200">
                        <!-- Öne Çıkan Görsel -->
                        <?php if (!empty($post['featured_image'])): ?>
                        <a href="/blog/<?php echo esc_attr($post['slug']); ?>" class="block relative overflow-hidden h-56">
                            <img src="<?php echo esc_url($post['featured_image']); ?>" 
                                 alt="<?php echo esc_attr($post['title']); ?>" 
                                 class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        </a>
                        <?php else: ?>
                        <a href="/blog/<?php echo esc_attr($post['slug']); ?>" class="block relative bg-gradient-to-br from-purple-400 via-pink-500 to-red-500 h-56">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="material-symbols-outlined text-white text-7xl opacity-40">article</span>
                            </div>
                        </a>
                        <?php endif; ?>
                        
                        <!-- İçerik -->
                        <div class="flex-1 p-6 flex flex-col">
                            <!-- Başlık -->
                            <h3 class="text-xl font-black text-gray-900 mb-3 leading-tight">
                                <a href="/blog/<?php echo esc_attr($post['slug']); ?>" 
                                   class="hover:text-purple-600 transition-colors">
                                    <?php echo esc_html($post['title']); ?>
                                </a>
                            </h3>
                            
                            <!-- Özet -->
                            <?php if (!empty($post['excerpt'])): ?>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3 flex-1 leading-relaxed">
                                <?php echo esc_html($post['excerpt']); ?>
                            </p>
                            <?php endif; ?>
                            
                            <!-- Meta & CTA -->
                            <div class="flex items-center justify-between text-xs pt-4 border-t border-gray-100 mt-auto">
                                <span class="flex items-center gap-1.5 text-gray-500 font-medium">
                                    <span class="material-symbols-outlined text-sm">calendar_today</span>
                                    <?php echo turkish_date($post['published_at'] ?? $post['created_at']); ?>
                                </span>
                                <a href="/blog/<?php echo esc_attr($post['slug']); ?>" 
                                   class="flex items-center gap-1 text-purple-600 font-bold hover:gap-2 transition-all group">
                                    <span>Oku</span>
                                    <span class="material-symbols-outlined text-sm group-hover:translate-x-0.5 transition-transform">arrow_forward</span>
                                </a>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
                
                <?php endif; ?>
            </div>
            
            <!-- Sidebar - Premium Design -->
            <aside class="w-full lg:w-96 xl:w-[420px] flex-shrink-0 space-y-8">
                
                <!-- Kategoriler - Premium Card -->
                <?php if (!empty($categories)): ?>
                <div class="bg-gradient-to-br from-purple-600 to-pink-600 rounded-3xl p-8 shadow-2xl text-white">
                    <h3 class="text-2xl font-black mb-6 flex items-center gap-3">
                        <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                        </svg>
                        Tüm Kategoriler
                    </h3>
                    <ul class="space-y-3">
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="/blog/kategori/<?php echo esc_attr($cat['slug']); ?>" 
                               class="flex items-center justify-between p-4 rounded-2xl transition-all group border-2 <?php echo $cat['id'] == $category['id'] ? 'bg-white/30 border-white/50 shadow-lg' : 'bg-white/10 border-white/20 hover:bg-white/20'; ?>">
                                <span class="font-bold"><?php echo esc_html($cat['name']); ?></span>
                                <div class="flex items-center gap-3">
                                    <?php if ($cat['id'] == $category['id']): ?>
                                    <span class="material-symbols-outlined text-lg">check_circle</span>
                                    <?php else: ?>
                                    <span class="material-symbols-outlined text-sm opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all">arrow_forward</span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <!-- Son Yazılar - Premium Card -->
                <?php if (!empty($recentPosts)): ?>
                <div class="bg-white rounded-3xl p-8 shadow-2xl border border-gray-100">
                    <h3 class="text-2xl font-black text-gray-900 mb-6 flex items-center gap-3">
                        <svg class="w-7 h-7 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        <span>Son Yazılar</span>
                    </h3>
                    <ul class="space-y-5">
                        <?php foreach ($recentPosts as $recent): ?>
                        <li>
                            <a href="/blog/<?php echo esc_attr($recent['slug']); ?>" 
                               class="flex gap-4 group">
                                <?php if (!empty($recent['featured_image'])): ?>
                                <div class="relative flex-shrink-0">
                                    <img src="<?php echo esc_url($recent['featured_image']); ?>" 
                                         alt="" 
                                         class="w-24 h-24 object-cover rounded-2xl group-hover:shadow-2xl transition-all" loading="lazy">
                                    <div class="absolute inset-0 bg-gradient-to-br from-purple-600/0 to-pink-600/0 group-hover:from-purple-600/20 group-hover:to-pink-600/20 rounded-2xl transition-all"></div>
                                </div>
                                <?php else: ?>
                                <div class="w-24 h-24 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex-shrink-0 flex items-center justify-center shadow-lg">
                                    <span class="material-symbols-outlined text-white text-3xl opacity-80">article</span>
                                </div>
                                <?php endif; ?>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-black text-gray-900 group-hover:text-purple-600 transition-colors line-clamp-2 leading-snug mb-2">
                                        <?php echo esc_html($recent['title']); ?>
                                    </h4>
                                    <p class="text-xs text-gray-500 font-medium flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-xs">calendar_today</span>
                                        <?php echo turkish_date($recent['published_at'] ?? $recent['created_at']); ?>
                                    </p>
                                </div>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <!-- Back to Blog CTA -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-3xl p-8 shadow-2xl text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-purple-600/20 rounded-full blur-3xl"></div>
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center mb-6">
                            <span class="material-symbols-outlined text-2xl">explore</span>
                        </div>
                        <h3 class="text-2xl font-black mb-3">Daha Fazla Keşfet</h3>
                        <p class="text-gray-300 mb-6 leading-relaxed">Diğer kategorilerdeki ilginç yazıları da okumak ister misin?</p>
                        <a href="/blog" class="inline-flex items-center gap-3 w-full justify-center px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl font-bold hover:shadow-2xl transition-all">
                            <span>Tüm Yazıları Gör</span>
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </a>
                    </div>
                </div>
                
            </aside>
        </div>
    </div>
</section>

