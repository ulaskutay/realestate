<?php
/**
 * Real Estate Theme - Blog Preview Section
 * Yazılardan veri çeker
 */

$section = $section ?? [];
$settings = $section['settings'] ?? [];

$sectionTitle = !empty($section['title']) ? $section['title'] : __('Son Yazılar & İpuçları');
$sectionSubtitle = !empty($section['subtitle']) ? $section['subtitle'] : __('Gayrimenkul içgörülerimizle güncel kalın');

// Gösterilecek yazı sayısı (varsayılan: 3)
$limit = isset($settings['limit']) ? (int)$settings['limit'] : 3;

// Buton renkleri - Tema renk paletinden al
$primaryButtonBg = '#1e40af';
$primaryButtonTextColor = '#ffffff';

// ThemeLoader'dan tema renklerini al
if (class_exists('ThemeLoader')) {
    $themeLoaderInstance = ThemeLoader::getInstance();
    $primaryButtonBg = $themeLoaderInstance->getColor('primary', '#1e40af');
    $primaryButtonTextColor = '#ffffff';
}

// Yazıları veritabanından çek
$posts = [];
try {
    // Post modelini yükle
    $postModelPath = __DIR__ . '/../../../app/models/Post.php';
    if (file_exists($postModelPath)) {
        require_once $postModelPath;
        
        if (class_exists('Post')) {
            $postModel = new Post();
            $posts = $postModel->getPublished($limit, 0);
            
            // Çeviri filter'larını uygula
            if (function_exists('apply_filters')) {
                foreach ($posts as &$post) {
                    $post['title'] = apply_filters('post_title', $post['title'] ?? '');
                    if (!empty($post['excerpt'])) {
                        $post['excerpt'] = apply_filters('post_excerpt', $post['excerpt']);
                    }
                }
                unset($post);
            }
        }
    }
} catch (Exception $e) {
    error_log("Blog preview error: " . $e->getMessage());
}

// Eğer yazı yoksa varsayılan örnekler göster
if (empty($posts)) {
    $posts = [
        [
            'id' => 1,
            'slug' => 'ilk-kez-ev-alicilari-icin-ipuclari',
            'title' => __('İlk Kez Ev Alıcıları İçin 10 İpucu'),
            'excerpt' => __('Ev alma sürecini güvenle yönetmenize yardımcı olacak temel tavsiyeler.'),
            'featured_image' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=800',
            'published_at' => date('Y-m-d', strtotime('-5 days')),
            'author_name' => __('Admin')
        ],
        [
            'id' => 2,
            'slug' => '2024-mortgage-oranlari',
            'title' => __('2024 Mortgage Oranlarını Anlamak'),
            'excerpt' => __('Güncel mortgage trendleri ve alıcılar için ne anlama geldikleri hakkında kapsamlı bir rehber.'),
            'featured_image' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=800',
            'published_at' => date('Y-m-d', strtotime('-10 days')),
            'author_name' => __('Admin')
        ],
        [
            'id' => 3,
            'slug' => 'ev-staging-ipuclari',
            'title' => __('Ev Staging: Mülkünüzü Öne Çıkarın'),
            'excerpt' => __('Profesyonel staging\'in evinizi daha hızlı ve daha yüksek fiyata satmanıza nasıl yardımcı olabileceğini öğrenin.'),
            'featured_image' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800',
            'published_at' => date('Y-m-d', strtotime('-15 days')),
            'author_name' => __('Admin')
        ]
    ];
}

// Site URL fonksiyonu
function get_blog_url($path = '') {
    if (function_exists('site_url')) {
        return site_url($path);
    }
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname($_SERVER['SCRIPT_FILENAME']));
    $base = str_replace('\\', '/', $base);
    return $protocol . $host . $base . ($path ? '/' . ltrim($path, '/') : '');
}
?>

<section class="py-20 lg:py-28 bg-white">
    <div class="container mx-auto px-4 lg:px-6">
        <div class="text-center mb-16">
            <h2 class="text-4xl lg:text-5xl font-bold text-secondary mb-4"><?php echo esc_html($sectionTitle); ?></h2>
            <?php if (!empty($sectionSubtitle)): ?>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto"><?php echo esc_html($sectionSubtitle); ?></p>
            <?php endif; ?>
        </div>

        <?php if (!empty($posts)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($posts as $post): 
                $postSlug = $post['slug'] ?? '';
                $postId = $post['id'] ?? 0;
                $postUrl = $postSlug ? get_blog_url('/blog/' . $postSlug) : get_blog_url('/blog/' . $postId);
                $featuredImage = $post['featured_image'] ?? 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=800';
                $publishedDate = $post['published_at'] ?? date('Y-m-d');
                $authorName = $post['author_name'] ?? __('Admin');
                $postTitle = $post['title'] ?? '';
                $postExcerpt = $post['excerpt'] ?? '';
            ?>
                <article class="bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group border border-gray-100">
                    <a href="<?php echo esc_url($postUrl); ?>" class="block">
                        <div class="relative overflow-hidden h-56">
                            <img src="<?php echo esc_url($featuredImage); ?>" 
                                 alt="<?php echo esc_attr($postTitle); ?>" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center text-sm text-gray-500 mb-3">
                                <time datetime="<?php echo esc_attr($publishedDate); ?>">
                                    <?php 
                                    $dateObj = DateTime::createFromFormat('Y-m-d', $publishedDate);
                                    if ($dateObj) {
                                        $months = [
                                            'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran',
                                            'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'
                                        ];
                                        $monthIndex = (int)$dateObj->format('n') - 1;
                                        echo esc_html($dateObj->format('j') . ' ' . ($months[$monthIndex] ?? '') . ' ' . $dateObj->format('Y'));
                                    } else {
                                        echo esc_html(date('d F Y', strtotime($publishedDate)));
                                    }
                                    ?>
                                </time>
                                <?php if (!empty($authorName)): ?>
                                    <span class="mx-2">•</span>
                                    <span><?php echo esc_html($authorName); ?></span>
                                <?php endif; ?>
                            </div>
                            <h3 class="text-xl font-bold text-secondary mb-3 group-hover:text-primary transition-colors line-clamp-2">
                                <?php echo esc_html($postTitle); ?>
                            </h3>
                            <?php if (!empty($postExcerpt)): ?>
                            <p class="text-gray-600 mb-4 line-clamp-3 leading-relaxed">
                                <?php echo esc_html($postExcerpt); ?>
                            </p>
                            <?php endif; ?>
                            <span class="inline-flex items-center text-primary font-semibold hover:underline group-hover:gap-2 transition-all">
                                <?php echo esc_html(__('Devamını Oku')); ?>
                                <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </span>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-12">
            <a href="<?php echo esc_url(get_blog_url('/blog')); ?>" 
               style="background-color: <?php echo esc_attr($primaryButtonBg); ?>; color: <?php echo esc_attr($primaryButtonTextColor); ?>;"
               class="inline-block px-8 py-4 rounded-xl font-semibold hover:opacity-90 transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <?php echo esc_html(__('Tüm Yazıları Görüntüle')); ?>
            </a>
        </div>
        <?php else: ?>
        <div class="text-center py-12">
            <p class="text-gray-500"><?php echo esc_html(__('Henüz yazı bulunmamaktadır.')); ?></p>
        </div>
        <?php endif; ?>
    </div>
</section>
