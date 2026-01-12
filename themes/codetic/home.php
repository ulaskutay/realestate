<?php
/**
 * Codetic Theme - Home Page
 * Section verileri veritabanından okunur, yoksa varsayılan değerler kullanılır
 */

// Section verilerini veritabanından al
$sections = $themeLoader->getPageSections('home');
$sectionsMap = [];
foreach ($sections as $s) {
    $sectionsMap[$s['section_id']] = $s;
}

// Varsayılan section verileri
$defaultSections = [
    'hero' => [
        'title' => 'Dijital Dönüşümde Öncü',
        'subtitle' => 'Yenilikçi çözümlerimizle işletmenizi dijital dünyada bir adım öne taşıyın. Profesyonel ekibimiz ile projelerinizi hayata geçirin.',
        'settings' => [
            'button_text' => 'Hemen Başla',
            'button_link' => '/contact',
            'secondary_button_text' => 'Daha Fazla Bilgi',
            'secondary_button_link' => '/about'
        ]
    ],
    'glowing-features' => [
        'title' => 'Özelliklerimiz',
        'subtitle' => 'Yenilikçi çözümlerimizle işletmenizi dijital dünyada bir adım öne taşıyın.',
        'settings' => [
            'badge' => 'Neden Biz?'
        ],
        'items' => [
            [
                'icon' => 'rocket',
                'title' => 'Hızlı Geliştirme',
                'description' => 'Modern araçlar ve metodolojilerle projelerinizi hızla hayata geçiriyoruz. Agile yaklaşımımızla sürekli değer üretiyoruz.',
                'gradient' => 'from-violet-500 to-purple-600'
            ],
            [
                'icon' => 'shield',
                'title' => 'Güvenli Altyapı',
                'description' => 'En güncel güvenlik standartları ve best practice\'ler ile verilerinizi koruyoruz. SSL, şifreleme ve düzenli güvenlik taramaları.',
                'gradient' => 'from-emerald-500 to-teal-600'
            ],
            [
                'icon' => 'code',
                'title' => 'Temiz Kod',
                'description' => 'Okunabilir, sürdürülebilir ve ölçeklenebilir kod yazıyoruz. SOLID prensipleri ve modern mimari desenler kullanıyoruz.',
                'gradient' => 'from-blue-500 to-cyan-600'
            ],
            [
                'icon' => 'zap',
                'title' => 'Yüksek Performans',
                'description' => 'Optimize edilmiş kod, CDN entegrasyonu ve caching stratejileri ile maksimum hız sağlıyoruz.',
                'gradient' => 'from-amber-500 to-orange-600'
            ],
            [
                'icon' => 'users',
                'title' => '7/24 Destek',
                'description' => 'Uzman ekibimiz her zaman yanınızda. Teknik destek, danışmanlık ve eğitim hizmetleri sunuyoruz.',
                'gradient' => 'from-pink-500 to-rose-600'
            ]
        ]
    ],
    'dashboard-showcase' => [
        'title' => 'Güçlü Yönetim Paneli',
        'subtitle' => 'Tek bir yerden tüm içeriklerinizi yönetin',
        'description' => 'Modern ve kullanıcı dostu arayüzümüz ile web sitenizi, içeriklerinizi ve müşterilerinizi kolayca yönetin. Gerçek zamanlı istatistikler, kolay içerik düzenleme ve güçlü SEO araçları.',
        'settings' => [
            'badge' => 'Yönetim Paneli'
        ],
        'features' => [
            'Sürükle-bırak içerik düzenleme',
            'Gerçek zamanlı analitik',
            'SEO optimizasyon araçları',
            'Çoklu dil desteği',
            'Otomatik yedekleme'
        ]
    ],
    'lamp' => [
        'title' => 'Fikirlerinizi Hayata Geçirelim',
        'subtitle' => '"Yapay Zeka Destekli Yenilikçi" çözümlerimizle işletmenizi dijital dünyada bir adım öne taşıyın.',
        'settings' => []
    ],
    'feature-tabs' => [
        'title' => 'Yapay Zeka Destekli, Ölçeklenebilir Web Altyapısı',
        'subtitle' => 'Codetic altyapısı; yapay zeka destekli optimizasyon, yüksek performanslı kod yapısı ve esnek mimarisiyle uzun vadeli dijital çözümler sunar.',
        'settings' => [
            'badge' => 'codetic.co'
        ],
        'tabs' => [
            [
                'value' => 'tab-1',
                'icon' => 'zap',
                'label' => 'Yapay Zeka Destekli',
                'content' => [
                    'badge' => 'Modern Web Tasarım Altyapısı',
                    'title' => 'Tema ve Modül Ekleme-Geliştirme Özelliği',
                    'description' => 'SEO ve performans süreçleri akıllı sistemlerle optimize edilir.',
                    'buttonText' => 'Planları Gör',
                    'buttonLink' => '#',
                    'imageSrc' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800&h=600&fit=crop&q=80',
                    'imageAlt' => 'Yapay Zeka Destekli Modül ve Tema Geliştirme'
                ]
            ],
            [
                'value' => 'tab-2',
                'icon' => 'pointer',
                'label' => '100% Responsive',
                'content' => [
                    'badge' => 'Mobil Uyumlu Panel ve Web Sitesi',
                    'title' => 'Tüm cihazlarda yüksek performanslı şekilde kullanın',
                    'description' => 'Tüm cihazlarda kusursuz deneyim: mobil, tablet ve masaüstü.',
                    'buttonText' => 'Detayları İncele',
                    'buttonLink' => '#',
                    'imageSrc' => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=800&h=600&fit=crop&q=80',
                    'imageAlt' => 'Responsive Web Altyapıları - Mobil, Tablet ve Masaüstü Uyumluluk'
                ]
            ],
            [
                'value' => 'tab-3',
                'icon' => 'layout',
                'label' => 'Hafif & Geliştirilebilir',
                'content' => [
                    'badge' => 'Temiz Mimari',
                    'title' => 'Dilediğiniz şekilde geliştirilebilir ve özelleştirilebilir.',
                    'description' => 'Modül ve tema yapısı ile her sektöre uygun şekilde geliştirilebilir.',
                    'buttonText' => 'Detayları İncele',
                    'buttonLink' => '#',
                    'imageSrc' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&h=600&fit=crop&q=80',
                    'imageAlt' => 'Geliştirilebilir ve Özelleştirilebilir Web Altyapısı'
                ]
            ]
        ]
    ],
    'section-with-mockup' => [
        'title' => 'Sektöre göre geliştirilebilir yapı',
        'description' => 'Her sektöre uygun olacak şekilde şekillendirilebilir, yapay zeka destekli, ölçeklenebilir web tasarım projeleri',
        'primary_image' => 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800&h=1200&fit=crop&q=80',
        'secondary_image' => 'https://images.unsplash.com/photo-1555949963-aa79dcee981c?w=800&h=1200&fit=crop&q=80',
        'settings' => [
            'reverse_layout' => false
        ]
    ],
    'pricing' => [
        'title' => 'Paketlerimiz',
        'subtitle' => 'İhtiyacınıza uygun paketi seçin ve dijital dönüşümünüze başlayın.',
        'settings' => [
            'badge' => 'Fiyatlandırma'
        ],
        'packages' => [
            [
                'name' => 'Başlangıç',
                'price' => '₺2.500',
                'period' => '/ay',
                'description' => 'Küçük işletmeler ve kişisel projeler için ideal başlangıç paketi.',
                'features' => [
                    '5 Sayfa',
                    'Temel SEO',
                    'E-posta Desteği',
                    'SSL Sertifikası',
                    'Mobil Uyumlu Tasarım'
                ],
                'button_text' => 'Başla',
                'button_link' => '/contact',
                'popular' => false,
                'gradient' => 'from-slate-500 to-slate-600'
            ],
            [
                'name' => 'Profesyonel',
                'price' => '₺5.000',
                'period' => '/ay',
                'description' => 'Büyüyen işletmeler için gelişmiş özellikler ve destek.',
                'features' => [
                    '15 Sayfa',
                    'Gelişmiş SEO',
                    'Öncelikli Destek',
                    'SSL Sertifikası',
                    'Mobil Uyumlu Tasarım',
                    'Sosyal Medya Entegrasyonu',
                    'Analytics Entegrasyonu'
                ],
                'button_text' => 'Başla',
                'button_link' => '/contact',
                'popular' => true,
                'gradient' => 'from-blue-500 to-purple-600'
            ],
            [
                'name' => 'Kurumsal',
                'price' => '₺10.000',
                'period' => '/ay',
                'description' => 'Büyük işletmeler için özel çözümler ve özel destek.',
                'features' => [
                    'Sınırsız Sayfa',
                    'Premium SEO',
                    '7/24 Öncelikli Destek',
                    'SSL Sertifikası',
                    'Mobil Uyumlu Tasarım',
                    'Sosyal Medya Entegrasyonu',
                    'Analytics Entegrasyonu',
                    'Özel Tasarım',
                    'API Entegrasyonları'
                ],
                'button_text' => 'Başla',
                'button_link' => '/contact',
                'popular' => false,
                'gradient' => 'from-violet-500 to-purple-600'
            ],
            [
                'name' => 'Özel Çözüm',
                'price' => 'Özel Fiyat',
                'period' => '',
                'description' => 'Özel ihtiyaçlarınız için özelleştirilmiş çözümler.',
                'features' => [
                    'Tam Özelleştirme',
                    'Özel Geliştirme',
                    'Dedike Destek',
                    'Tüm Özellikler',
                    'Özel Entegrasyonlar',
                    'Danışmanlık Hizmeti',
                    'Öncelikli Güncellemeler'
                ],
                'button_text' => 'İletişime Geç',
                'button_link' => '/contact',
                'popular' => false,
                'gradient' => 'from-amber-500 to-orange-600'
            ]
        ]
    ]
];

// Global flag - duplicate render'ı önlemek için
if (!isset($GLOBALS['codetic_home_sections_rendered'])) {
    $GLOBALS['codetic_home_sections_rendered'] = false;
}

// Duplicate render kontrolü - eğer zaten render edilmişse, hiçbir şey yapma
if ($GLOBALS['codetic_home_sections_rendered']) {
    return;
}

// Flag'i hemen set et (render başlamadan önce)
$GLOBALS['codetic_home_sections_rendered'] = true;

// Section render fonksiyonu
function renderHomeSection($sectionId, $sectionsMap, $defaultSections, $themeLoader) {
    // Sadece mevcut component'leri render et
    $availableSections = ['hero', 'glowing-features', 'dashboard-showcase', 'lamp', 'feature-tabs', 'section-with-mockup', 'pricing'];
    if (!in_array($sectionId, $availableSections)) {
        return;
    }
    
    // Veritabanında kayıtlı ve aktif değilse gösterme
    if (isset($sectionsMap[$sectionId]) && isset($sectionsMap[$sectionId]['is_active']) && !$sectionsMap[$sectionId]['is_active']) {
        return;
    }
    
    // Veritabanı verisini al, yoksa varsayılanı kullan
    $dbSection = $sectionsMap[$sectionId] ?? null;
    $default = $defaultSections[$sectionId] ?? [];
    
    // Veriyi birleştir
    $section = [
        'title' => !empty($dbSection['title']) ? $dbSection['title'] : ($default['title'] ?? ''),
        'subtitle' => !empty($dbSection['subtitle']) ? $dbSection['subtitle'] : ($default['subtitle'] ?? ''),
        'content' => !empty($dbSection['content']) ? $dbSection['content'] : ($default['content'] ?? ''),
        'text' => !empty($dbSection['text']) ? $dbSection['text'] : ($default['text'] ?? ''),
        'description' => !empty($dbSection['description']) ? $dbSection['description'] : ($default['description'] ?? ''),
        'features' => !empty($dbSection['features']) ? $dbSection['features'] : ($default['features'] ?? []),
        'show_card' => isset($dbSection['show_card']) ? $dbSection['show_card'] : ($default['show_card'] ?? true),
        'settings' => !empty($dbSection['settings']) ? $dbSection['settings'] : ($default['settings'] ?? []),
        'items' => !empty($dbSection['items']) ? $dbSection['items'] : ($default['items'] ?? []),
        'tabs' => !empty($dbSection['tabs']) ? $dbSection['tabs'] : ($default['tabs'] ?? []),
        'packages' => !empty($dbSection['packages']) ? $dbSection['packages'] : ($default['packages'] ?? []),
        'primary_image' => !empty($dbSection['primary_image']) ? $dbSection['primary_image'] : ($default['primary_image'] ?? ''),
        'secondary_image' => !empty($dbSection['secondary_image']) ? $dbSection['secondary_image'] : ($default['secondary_image'] ?? ''),
        'image_url' => !empty($dbSection['image_url']) ? $dbSection['image_url'] : ($default['image_url'] ?? ''),
        'image_alt' => !empty($dbSection['image_alt']) ? $dbSection['image_alt'] : ($default['image_alt'] ?? '')
    ];
    
    echo $themeLoader->renderComponent($sectionId, ['section' => $section]);
}

// Section'ları sıralı render et - Sadece mevcut component'ler kullanılıyor
$sectionOrder = ['hero', 'glowing-features', 'dashboard-showcase', 'section-with-mockup', 'feature-tabs', 'lamp', 'pricing'];

// Veritabanında sıralama varsa onu kullan (sadece mevcut component'ler için)
if (!empty($sections)) {
    // Veritabanından gelen section'ları map'e çevir (sort_order'a göre değil, bizim sıramıza göre)
    $dbSectionsMap = [];
    $availableSections = ['hero', 'glowing-features', 'dashboard-showcase', 'lamp', 'feature-tabs', 'section-with-mockup', 'pricing']; // Mevcut component'ler
    
    foreach ($sections as $s) {
        if (in_array($s['section_id'], $availableSections) && isset($defaultSections[$s['section_id']])) {
            $dbSectionsMap[$s['section_id']] = $s;
        }
    }
    
    // Bizim sıralamamıza göre section'ları düzenle
    $orderedSections = [];
    foreach ($sectionOrder as $sid) {
        if (isset($dbSectionsMap[$sid]) || isset($defaultSections[$sid])) {
            $orderedSections[] = $sid;
        }
    }
    
    $sectionOrder = $orderedSections;
}

// Section'ları render et
foreach ($sectionOrder as $sectionId) {
    renderHomeSection($sectionId, $sectionsMap, $defaultSections, $themeLoader);
}

