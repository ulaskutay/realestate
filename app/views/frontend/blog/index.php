<?php
/**
 * Blog Listesi Sayfası - Premium Modern Design
 */
?>
<!-- Blog Header - Premium Gradient -->
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
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/20 backdrop-blur-md text-white text-sm font-bold rounded-full mb-6 border border-white/30 shadow-lg">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"/>
                    <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"/>
                </svg>
                <span>Blog & İçgörüler</span>
            </div>
            
            <!-- Main Title -->
            <h1 class="text-5xl md:text-7xl font-black text-white mb-6 leading-tight tracking-tight">
                Hikayeler &<br/>
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-yellow-200 via-pink-200 to-white">
                    İlham Kaynakları
                </span>
            </h1>
            
            <!-- Subtitle -->
            <p class="text-xl md:text-2xl text-white/90 font-medium max-w-3xl mx-auto leading-relaxed">
                Teknoloji, tasarım ve inovasyonun kesiştiği noktada, sizin için özenle hazırlanmış içerikler
            </p>
            
            <!-- Search Bar -->
            <div class="mt-10 max-w-2xl mx-auto">
                <form action="/blog" method="GET" class="relative">
                    <input type="text" 
                           name="q" 
                           placeholder="Aradığınız konuyu yazın..."
                           class="w-full px-6 py-5 pl-14 rounded-2xl bg-white/95 backdrop-blur-sm text-gray-800 placeholder-gray-500 focus:outline-none focus:ring-4 focus:ring-white/50 shadow-2xl text-lg font-medium">
                    <span class="material-symbols-outlined absolute left-5 top-1/2 transform -translate-y-1/2 text-gray-400 text-2xl">search</span>
                    <button type="submit" class="absolute right-3 top-1/2 transform -translate-y-1/2 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold rounded-xl hover:shadow-lg transition-all">
                        Ara
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Blog Content -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <!-- Stats Bar -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-16">
            <div class="bg-white rounded-2xl p-6 text-center shadow-md border border-gray-100">
                <div class="text-3xl font-black text-purple-600 mb-1"><?php echo count($posts); ?></div>
                <div class="text-sm text-gray-600 font-medium">Yazı</div>
            </div>
            <div class="bg-white rounded-2xl p-6 text-center shadow-md border border-gray-100">
                <div class="text-3xl font-black text-pink-600 mb-1"><?php echo count($categories); ?></div>
                <div class="text-sm text-gray-600 font-medium">Kategori</div>
            </div>
            <div class="bg-white rounded-2xl p-6 text-center shadow-md border border-gray-100">
                <div class="text-3xl font-black text-indigo-600 mb-1"><?php echo $totalPages ?? 1; ?></div>
                <div class="text-sm text-gray-600 font-medium">Sayfa</div>
            </div>
            <div class="bg-white rounded-2xl p-6 text-center shadow-md border border-gray-100">
                <div class="text-3xl font-black text-orange-600 mb-1">∞</div>
                <div class="text-sm text-gray-600 font-medium">İlham</div>
            </div>
        </div>
        
        <div class="flex flex-col lg:flex-row gap-12">
            
            <!-- Ana İçerik -->
            <div class="flex-1">
                <?php if (empty($posts)): ?>
                <div class="bg-white rounded-3xl p-20 text-center shadow-xl border border-gray-100">
                    <div class="inline-flex items-center justify-center w-32 h-32 bg-gradient-to-br from-purple-100 to-pink-100 rounded-full mb-8">
                        <span class="material-symbols-outlined text-purple-600 text-6xl">article</span>
                    </div>
                    <h2 class="text-3xl font-black text-gray-800 mb-4">Henüz yazı yok</h2>
                    <p class="text-gray-600 text-lg max-w-md mx-auto">Yakında sizi heyecanlandıracak, ilham verecek yazılar eklenecek. Takipte kalın!</p>
                </div>
                <?php else: ?>
                
                <!-- Öne Çıkan İlk Yazı (Hero Post) - Premium Style -->
                <?php if (!empty($posts)): 
                    $heroPost = $posts[0];
                    $remainingPosts = array_slice($posts, 1);
                ?>
                <article class="bg-white rounded-3xl overflow-hidden shadow-2xl hover:shadow-3xl transition-all duration-500 mb-16 group border border-gray-100">
                    <?php if (!empty($heroPost['featured_image'])): ?>
                    <a href="/blog/<?php echo esc_attr($heroPost['slug']); ?>" class="block relative overflow-hidden h-96">
                        <img src="<?php echo esc_url($heroPost['featured_image']); ?>" 
                             alt="<?php echo esc_attr($heroPost['title']); ?>" 
                             class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/50 to-transparent"></div>
                        
                        <!-- Floating Badge -->
                        <div class="absolute top-6 right-6 px-4 py-2 bg-white/95 backdrop-blur-sm rounded-full flex items-center gap-2 shadow-xl">
                            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                            <span class="text-sm font-bold text-gray-800">Öne Çıkan</span>
                        </div>
                    </a>
                    <?php endif; ?>
                    
                    <div class="p-10">
                        <!-- Kategori Badge -->
                        <?php if (!empty($heroPost['category_name'])): ?>
                        <a href="/blog/kategori/<?php echo esc_attr($heroPost['category_slug']); ?>" 
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-purple-600 via-pink-600 to-red-600 text-white text-sm font-black rounded-full mb-6 hover:shadow-xl hover:scale-105 transition-all">
                            <span class="material-symbols-outlined text-base">label</span>
                            <?php echo esc_html($heroPost['category_name']); ?>
                        </a>
                        <?php endif; ?>
                        
                        <!-- Başlık -->
                        <h2 class="text-4xl md:text-5xl font-black text-gray-900 mb-6 leading-tight tracking-tight">
                            <a href="/blog/<?php echo esc_attr($heroPost['slug']); ?>" 
                               class="hover:bg-clip-text hover:text-transparent hover:bg-gradient-to-r hover:from-purple-600 hover:to-pink-600 transition-all">
                                <?php echo esc_html($heroPost['title']); ?>
                            </a>
                        </h2>
                        
                        <!-- Özet -->
                        <?php if (!empty($heroPost['excerpt'])): ?>
                        <p class="text-gray-600 text-xl mb-8 leading-relaxed font-medium">
                            <?php echo esc_html($heroPost['excerpt']); ?>
                        </p>
                        <?php endif; ?>
                        
                        <!-- Meta & CTA -->
                        <div class="flex flex-wrap items-center justify-between gap-6 pt-8 border-t-2 border-gray-100">
                            <div class="flex items-center gap-6 text-sm">
                                <?php if (!empty($heroPost['author_name'])): ?>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold shadow-lg">
                                        <?php echo strtoupper(substr($heroPost['author_name'], 0, 1)); ?>
                                    </div>
                                    <span class="font-bold text-gray-800"><?php echo esc_html($heroPost['author_name']); ?></span>
                                </div>
                                <?php endif; ?>
                                <span class="flex items-center gap-2 text-gray-600 font-medium">
                                    <span class="material-symbols-outlined text-lg">calendar_today</span>
                                    <?php echo turkish_date($heroPost['published_at'] ?? $heroPost['created_at']); ?>
                                </span>
                            </div>
                            <a href="/blog/<?php echo esc_attr($heroPost['slug']); ?>" 
                               class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-black rounded-2xl hover:shadow-2xl hover:scale-105 transition-all group">
                                <span>Yazıyı Oku</span>
                                <span class="material-symbols-outlined text-lg group-hover:translate-x-1 transition-transform">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </article>
                <?php endif; ?>
                
                <!-- Kalan Yazılar - Premium Grid Layout -->
                <?php if (!empty($remainingPosts)): ?>
                <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-8">
                    <?php foreach ($remainingPosts as $post): ?>
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
                                <!-- Kategori -->
                                <?php if (!empty($post['category_name'])): ?>
                            <a href="/blog/kategori/<?php echo esc_attr($post['category_slug']); ?>" 
                               class="inline-flex items-center gap-1 w-fit px-3 py-1.5 bg-gradient-to-r from-purple-100 to-pink-100 text-purple-700 text-xs font-bold rounded-full mb-4 hover:from-purple-200 hover:to-pink-200 transition-all">
                                    <?php echo esc_html($post['category_name']); ?>
                                </a>
                                <?php endif; ?>
                                
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
                                
                            <!-- Meta -->
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
                
                <!-- Sayfalama - Premium Style -->
                <?php if ($totalPages > 1): ?>
                <nav class="mt-16 flex justify-center">
                    <div class="inline-flex items-center gap-2 bg-white p-2 rounded-2xl shadow-xl border border-gray-200">
                        <?php if ($currentPage > 1): ?>
                        <a href="/blog?p=<?php echo $currentPage - 1; ?>" 
                           class="inline-flex items-center gap-2 px-5 py-3 rounded-xl text-gray-700 font-bold hover:bg-gradient-to-r hover:from-purple-600 hover:to-pink-600 hover:text-white transition-all">
                            <span class="material-symbols-outlined text-lg">chevron_left</span>
                            <span class="hidden sm:inline">Önceki</span>
                        </a>
                        <?php endif; ?>
                        
                        <div class="flex items-center gap-2 px-2">
                        <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                        <a href="/blog?p=<?php echo $i; ?>" 
                               class="min-w-[44px] h-11 flex items-center justify-center rounded-xl font-black transition-all <?php echo $i === $currentPage ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg scale-110' : 'text-gray-700 hover:bg-gray-100'; ?>">
                            <?php echo $i; ?>
                        </a>
                        <?php endfor; ?>
                        </div>
                        
                        <?php if ($currentPage < $totalPages): ?>
                        <a href="/blog?p=<?php echo $currentPage + 1; ?>" 
                           class="inline-flex items-center gap-2 px-5 py-3 rounded-xl text-gray-700 font-bold hover:bg-gradient-to-r hover:from-purple-600 hover:to-pink-600 hover:text-white transition-all">
                            <span class="hidden sm:inline">Sonraki</span>
                            <span class="material-symbols-outlined text-lg">chevron_right</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </nav>
                <?php endif; ?>
                
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
                        Kategoriler
                    </h3>
                    <ul class="space-y-3">
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="/blog/kategori/<?php echo esc_attr($cat['slug']); ?>" 
                               class="flex items-center justify-between p-4 rounded-2xl bg-white/10 backdrop-blur-sm hover:bg-white/20 transition-all group border border-white/20">
                                <span class="font-bold"><?php echo esc_html($cat['name']); ?></span>
                                <div class="flex items-center gap-3">
                                    <span class="px-3 py-1 bg-white/20 rounded-full text-xs font-black"><?php echo $cat['post_count'] ?? 0; ?></span>
                                    <span class="material-symbols-outlined text-sm opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all">arrow_forward</span>
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
                
                <!-- CTA Card -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-3xl p-8 shadow-2xl text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-purple-600/20 rounded-full blur-3xl"></div>
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center mb-6">
                            <span class="material-symbols-outlined text-2xl">mail</span>
                        </div>
                        <h3 class="text-2xl font-black mb-3">Bültene Abone Ol</h3>
                        <p class="text-gray-300 mb-6 leading-relaxed">Yeni yazılardan haberdar olmak için e-posta adresini bırak!</p>
                        <form class="space-y-3">
                            <input type="email" placeholder="E-posta adresin" class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-white/50">
                            <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl font-bold hover:shadow-2xl transition-all">
                                Abone Ol
                            </button>
                        </form>
                    </div>
                </div>
                
            </aside>
        </div>
    </div>
</section>

