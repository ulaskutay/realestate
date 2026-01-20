<?php
/**
 * Real Estate Theme - Home Page
 * Section verileri veritabanından okunur, yoksa varsayılan değerler kullanılır
 */

// Section verilerini veritabanından al
$sections = $themeLoader->getPageSections('home');
$sectionsMap = [];
$seenSectionIds = []; // Duplicate kontrolü için
foreach ($sections as $s) {
    $sectionId = $s['section_id'] ?? '';
    // Sadece benzersiz section_id'leri ekle (duplicate'leri önle)
    // Son eklenen kayıt öncelikli (daha yeni veri)
    if (!empty($sectionId)) {
        $sectionsMap[$sectionId] = $s;
        $seenSectionIds[$sectionId] = true;
    }
}

// Varsayılan section verileri
$defaultSections = [
    'hero' => [
        'title' => __('Hayalinizdeki Evini Bugün Bulun'),
        'subtitle' => __('Geniş gayrimenkul koleksiyonumuzdan premium evler, daireler ve ticari alanlar arasından mükemmel mülkü keşfedin.'),
        'settings' => [
            'button_text' => __('Mülkleri İncele'),
            'button_link' => '/ilanlar',
            'secondary_button_text' => __('Tur Planla'),
            'secondary_button_link' => '/contact',
            'hero_image' => ''
        ]
    ],
    'featured-listings' => [
        'title' => __('Featured Properties'),
        'subtitle' => __('Discover our handpicked selection of premium properties')
    ],
    'consultants' => [
        'title' => __('Uzman Danışmanlarımız'),
        'subtitle' => __('Size en uygun emlağı bulmanızda yardımcı olacak profesyonel ekibimizle tanışın'),
        'settings' => [
            'limit' => 6
        ]
    ],
    'why-choose-us' => [
        'title' => __('Neden Bizi Seçmelisiniz'),
        'subtitle' => __('Hayalinizdeki mülkü bulmanızı kolay ve stressiz hale getiriyoruz'),
        'items' => [
            [
                'icon' => 'shield',
                'title' => __('Güvenilir & Deneyimli'),
                'description' => __('Gayrimenkul piyasasında yılların deneyimi ve başarılı işlemlerle kanıtlanmış bir geçmiş.')
            ],
            [
                'icon' => 'home',
                'title' => __('Geniş Seçenek'),
                'description' => __('Tüm fiyat aralıkları ve lokasyonlarda binlerce mülke erişim.')
            ],
            [
                'icon' => 'users',
                'title' => __('Uzman Ekip'),
                'description' => __('Profesyonel ekibimiz, mükemmel mülkünüzü bulmanız için size adanmış.')
            ],
            [
                'icon' => 'clock',
                'title' => __('7/24 Destek'),
                'description' => __('Sorularınızı yanıtlamak ve süreçte size rehberlik etmek için kesintisiz yardım.')
            ],
            [
                'icon' => 'dollar',
                'title' => __('En İyi Fiyatlar'),
                'description' => __('Rekabetçi fiyatlandırma ve gizli maliyet olmadan şeffaf ücretler.')
            ],
            [
                'icon' => 'document',
                'title' => __('Kolay Süreç'),
                'description' => __('Gayrimenkul yolculuğunuzu sorunsuz hale getirmek için sadeleştirilmiş prosedürler.')
            ]
        ]
    ],
    'agent-profile' => [
        'title' => __('Meet Our Expert Agent'),
        'subtitle' => __('Dedicated to helping you find your perfect property'),
        'settings' => [
            'agent_name' => __('John Smith'),
            'agent_title' => __('Senior Real Estate Agent'),
            'agent_bio' => __('With over 10 years of experience in the real estate industry, I specialize in helping clients find their dream homes. My commitment to excellence and personalized service has helped hundreds of families find their perfect property.'),
            'agent_image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400',
            'agent_phone' => '+1 (555) 123-4567',
            'agent_email' => 'agent@example.com',
            'agent_experience' => '10+',
            'agent_properties' => '500+',
            'agent_clients' => '300+'
        ]
    ],
    'testimonials' => [
        'title' => __('Müşterilerimiz Ne Diyor'),
        'subtitle' => __('Sadece bizim söylediklerimize değil, müşterilerimizin deneyimlerine de kulak verin'),
        'items' => [
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
        ]
    ],
    'blog-preview' => [
        'title' => __('Son Yazılar & İpuçları'),
        'subtitle' => __('Gayrimenkul içgörülerimizle güncel kalın'),
        'settings' => [
            'limit' => 3
        ]
    ],
    'cta' => [
        'title' => __('Ready to Find Your Dream Home?'),
        'subtitle' => __('Let us help you find the perfect property. Contact us today for a free consultation.'),
        'settings' => [
            'button_text' => __('Get Started Today'),
            'button_link' => '/contact',
            'secondary_button_text' => __('Browse Properties'),
            'secondary_button_link' => '/ilanlar'
        ]
    ]
];

// Section render fonksiyonu
function renderHomeSection($sectionId, $sectionsMap, $defaultSections, $themeLoader) {
    // Veritabanında kayıtlı ve aktif değilse gösterme
    if (isset($sectionsMap[$sectionId]) && isset($sectionsMap[$sectionId]['is_active']) && !$sectionsMap[$sectionId]['is_active']) {
        return;
    }
    
    // Varsayılan section tanımlı değilse gösterme
    if (!isset($defaultSections[$sectionId])) {
        return;
    }
    
    // Veritabanı verisini al, yoksa varsayılanı kullan
    $dbSection = $sectionsMap[$sectionId] ?? null;
    $default = $defaultSections[$sectionId] ?? [];
    
    // Settings'i decode et (JSON string ise)
    $dbSettings = [];
    if (!empty($dbSection['settings'])) {
        if (is_array($dbSection['settings'])) {
            $dbSettings = $dbSection['settings'];
        } else {
            $decoded = json_decode($dbSection['settings'], true);
            $dbSettings = is_array($decoded) ? $decoded : [];
        }
    }
    
    // Items'ı decode et (JSON string ise)
    $dbItems = [];
    if (!empty($dbSection['items'])) {
        if (is_array($dbSection['items'])) {
            $dbItems = $dbSection['items'];
        } else {
            $decoded = json_decode($dbSection['items'], true);
            $dbItems = is_array($decoded) ? $decoded : [];
        }
    }
    
    // Veriyi birleştir - Veritabanındaki değerler öncelikli
    $section = [
        'title' => !empty($dbSection['title']) ? $dbSection['title'] : ($default['title'] ?? ''),
        'subtitle' => !empty($dbSection['subtitle']) ? $dbSection['subtitle'] : ($default['subtitle'] ?? ''),
        'content' => !empty($dbSection['content']) ? $dbSection['content'] : ($default['content'] ?? ''),
        'settings' => !empty($dbSettings) ? array_merge($default['settings'] ?? [], $dbSettings) : ($default['settings'] ?? []),
        'items' => !empty($dbItems) ? $dbItems : ($default['items'] ?? [])
    ];
    
    // Translation filter'larını uygula - Dil değiştirme için gerekli
    if (function_exists('apply_filters')) {
        $section['title'] = apply_filters('theme_section_title', $section['title'], $sectionId);
        $section['subtitle'] = apply_filters('theme_section_subtitle', $section['subtitle'], $sectionId);
        $section['content'] = apply_filters('theme_section_content', $section['content'], $sectionId);
        $section['settings'] = apply_filters('theme_section_settings', $section['settings'], $sectionId);
        $section['items'] = apply_filters('theme_section_items', $section['items'], $sectionId);
    }
    
    echo $themeLoader->renderComponent($sectionId, ['section' => $section]);
}

// Section'ları sıralı render et - HER ZAMAN Varsayılan sıralamayı kullan
// Bu sıralama sabittir ve veritabanındaki sort_order değerlerinden bağımsızdır
$sectionOrder = ['hero', 'featured-listings', 'consultants', 'why-choose-us', 'testimonials', 'blog-preview', 'cta'];

// Section'ları render et - Her zaman varsayılan sıralamaya göre
foreach ($sectionOrder as $sectionId) {
    renderHomeSection($sectionId, $sectionsMap, $defaultSections, $themeLoader);
}
