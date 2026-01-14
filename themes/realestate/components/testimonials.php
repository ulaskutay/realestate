<?php
/**
 * Real Estate Theme - Customer Testimonials
 */

$section = $section ?? [];
$items = $section['items'] ?? [];

// Default testimonials if not provided
if (empty($items)) {
    $items = [
        [
            'name' => __('Ayşe Yılmaz'),
            'role' => __('Ev Alıcısı'),
            'image' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150',
            'rating' => 5,
            'text' => __('Ekip, ev alma deneyimimizi sorunsuz hale getirdi. Profesyonel, hızlı yanıt veren ve tam olarak aradığımız şeyi bulmamıza yardımcı olan bir ekiple çalıştık.')
        ],
        [
            'name' => __('Mehmet Demir'),
            'role' => __('Gayrimenkul Yatırımcısı'),
            'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150',
            'rating' => 5,
            'text' => __('Mükemmel bir hizmet! Yatırım yapmak isteyen herkese şiddetle tavsiye ederim. Birden fazla yatırım gayrimenkulü edinmemize ve harika getiriler elde etmemize yardımcı oldular.')
        ],
        [
            'name' => __('Zeynep Kaya'),
            'role' => __('İlk Kez Ev Alıcısı'),
            'image' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150',
            'rating' => 5,
            'text' => __('İlk kez ev alıcıları olarak süreçten endişeliydik. Danışmanımız bizi her adımda yönlendirdi ve kendimize güvenmemizi sağladı. Teşekkürler!')
        ]
    ];
}

$sectionTitle = !empty($section['title']) ? $section['title'] : __('Müşterilerimiz Ne Diyor');
$sectionSubtitle = !empty($section['subtitle']) ? $section['subtitle'] : __('Sadece bizim söylediklerimize değil, müşterilerimizin deneyimlerine de kulak verin');
?>

<section class="py-16 lg:py-24 bg-surface">
    <div class="container mx-auto px-4 lg:px-6">
        <div class="text-center mb-12">
            <h2 class="text-3xl lg:text-4xl font-bold text-secondary mb-4"><?php echo esc_html($sectionTitle); ?></h2>
            <?php if (!empty($sectionSubtitle)): ?>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto"><?php echo esc_html($sectionSubtitle); ?></p>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($items as $testimonial): ?>
                <div class="bg-white rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300">
                    <!-- Rating -->
                    <div class="flex mb-4">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <svg class="w-5 h-5 <?php echo $i <= ($testimonial['rating'] ?? 5) ? 'text-yellow-400' : 'text-gray-300'; ?>" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        <?php endfor; ?>
                    </div>

                    <!-- Testimonial Text -->
                    <p class="text-gray-600 mb-6 leading-relaxed italic">
                        "<?php echo esc_html(__($testimonial['text'] ?? $testimonial['content'] ?? '')); ?>"
                    </p>

                    <!-- Author -->
                    <div class="flex items-center">
                        <img src="<?php echo esc_url($testimonial['image'] ?? $testimonial['avatar'] ?? 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150'); ?>" 
                             alt="<?php echo esc_attr($testimonial['name']); ?>" 
                             class="w-12 h-12 rounded-full object-cover mr-4">
                        <div>
                            <div class="font-semibold text-secondary"><?php echo esc_html(__($testimonial['name'])); ?></div>
                            <div class="text-sm text-gray-500"><?php echo esc_html(__($testimonial['role'])); ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
