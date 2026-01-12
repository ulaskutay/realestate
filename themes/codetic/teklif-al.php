<?php
/**
 * Codetic Theme - Teklif Alma Sayfası
 * Çok adımlı form ile teklif alma sayfası
 * Referans: https://www.pentayazilim.com/teklif-al/
 */

// Form model'ini yükle
if (!class_exists('Form')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/Form.php';
}
if (!class_exists('FormField')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/app/models/FormField.php';
}

$formModel = new Form();
$form = null;

// Form ID veya slug - ÖNCE sayfa custom field'larından al (modülden kaydedilen)
$formId = $customFields['quote_form_id'] ?? null;
$formSlug = $customFields['quote_form_slug'] ?? null;

// Form'u bul
if ($formId) {
    $form = $formModel->findWithFields($formId);
} elseif ($formSlug) {
    $form = $formModel->findBySlugWithFields($formSlug);
}

// Form bulunamadıysa hata göster
if (!$form || ($form['status'] ?? '') !== 'active') {
    $layoutPath = __DIR__ . '/layouts/default.php';
    if (file_exists($layoutPath)) {
        $title = $page['meta_title'] ?? $page['title'] ?? 'Hata';
        $meta_description = $page['meta_description'] ?? '';
        $current_page = 'quote-request';
        $content = '<div class="container mx-auto px-4 py-16">
            <div class="max-w-2xl mx-auto bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6">
                <div class="flex items-start gap-4">
                    <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-3xl">error</span>
                    <div>
                        <h2 class="text-red-800 dark:text-red-200 text-xl font-bold mb-2">Form Bulunamadı</h2>
                        <p class="text-red-700 dark:text-red-300 mb-4">Teklif alma formu bulunamadı. Lütfen admin panelden form seçin.</p>
                        <a href="' . admin_url('module/quote-request') . '" class="inline-block px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            Admin Panele Git
                        </a>
                    </div>
                </div>
            </div>
        </div>';
        include $layoutPath;
        exit;
    } else {
        die('<h1>Form Bulunamadı</h1><p>Lütfen admin panelden form seçin.</p>');
    }
}

// Page title ve meta
$pageTitle = $page['title'] ?? $form['name'] ?? 'Teklif Al';
$pageDescription = $page['excerpt'] ?? $page['meta_description'] ?? $form['description'] ?? 'Projeniz için detaylı teklif alın';

// Form alanlarını adımlara göre organize et
$steps = [
    1 => ['title' => 'Bu projeyi kimin için yapacağız?', 'subtitle' => 'Proje tipinizi seçin', 'fields' => []],
    2 => ['title' => 'Hangi hizmetleri istiyorsunuz?', 'subtitle' => 'İhtiyacınız olan hizmetleri seçin', 'fields' => []],
    3 => ['title' => 'Bize projenizden bahsedebilir misiniz?', 'subtitle' => 'Proje detaylarınızı paylaşın', 'fields' => []],
    4 => ['title' => 'İletişim Bilgileriniz', 'subtitle' => 'Size ulaşabilmemiz için bilgilerinizi girin', 'fields' => []],
    5 => ['title' => 'Onay ve Gönderim', 'subtitle' => 'Son adım', 'fields' => []]
];

// Hizmet kategorileri
$serviceCategories = [
    'web' => ['name' => 'Web Hizmetleri', 'icon' => '🌐', 'fields' => []],
    'graphic' => ['name' => 'Grafik Tasarım', 'icon' => '🎨', 'fields' => []],
    'marketing' => ['name' => 'Dijital Pazarlama', 'icon' => '📈', 'fields' => []],
    'other' => ['name' => 'Diğer Hizmetler', 'icon' => '⚙️', 'fields' => []]
];

// Form alanlarını step bilgisine göre kategorize et
if (!empty($form['fields'])) {
    foreach ($form['fields'] as $field) {
        // Field name'den step bilgisini çıkar
        $step = 1;
        if (preg_match('/^step(\d+)_/', $field['name'], $matches)) {
            $step = (int)$matches[1];
        } elseif (preg_match('/step[_-]?(\d+)/i', $field['name'] . ' ' . ($field['help_text'] ?? ''), $matches)) {
            $step = (int)$matches[1];
        } elseif (!empty($field['css_class']) && preg_match('/step[_-]?(\d+)/i', $field['css_class'], $matches)) {
            $step = (int)$matches[1];
        }
        
        if ($step >= 1 && $step <= 5) {
            $steps[$step]['fields'][] = $field;
            
            // Adım 2 ise hizmetleri kategorilere göre organize et
            if ($step === 2 && ($field['type'] === 'checkbox' || $field['type'] === 'radio')) {
                $fieldName = strtolower($field['name']);
                $category = 'other';
                
                if (strpos($fieldName, 'web') !== false || strpos($fieldName, 'site') !== false || strpos($fieldName, 'yazılım') !== false || strpos($fieldName, 'mobil') !== false || strpos($fieldName, 'e-ticaret') !== false) {
                    $category = 'web';
                } elseif (strpos($fieldName, 'grafik') !== false || strpos($fieldName, 'logo') !== false || strpos($fieldName, 'tasarım') !== false || strpos($fieldName, 'katalog') !== false || strpos($fieldName, 'video') !== false || strpos($fieldName, 'fotoğraf') !== false) {
                    $category = 'graphic';
                } elseif (strpos($fieldName, 'pazarlama') !== false || strpos($fieldName, 'seo') !== false || strpos($fieldName, 'google') !== false || strpos($fieldName, 'sosyal') !== false || strpos($fieldName, 'email') !== false) {
                    $category = 'marketing';
                }
                
                $serviceCategories[$category]['fields'][] = $field;
            }
        } else {
            // Step belirtilmemişse otomatik dağıt
            if (in_array($field['name'], ['project_type', 'client_type', 'project_for'])) {
                $steps[1]['fields'][] = $field;
            } elseif (in_array($field['name'], ['services', 'selected_services', 'service_categories']) || $field['type'] === 'checkbox' || $field['type'] === 'radio') {
                $steps[2]['fields'][] = $field;
            } elseif (in_array($field['name'], ['domain', 'languages', 'about_project', 'project_description', 'project_details'])) {
                $steps[3]['fields'][] = $field;
            } elseif (in_array($field['name'], ['name', 'surname', 'email', 'phone', 'company_name', 'position', 'city', 'district'])) {
                $steps[4]['fields'][] = $field;
            } elseif (in_array($field['name'], ['kvkk', 'terms', 'privacy', 'consent'])) {
                $steps[5]['fields'][] = $field;
            } else {
                $steps[4]['fields'][] = $field;
            }
        }
    }
}

// Form field'ları kontrolü - eğer hiç field yoksa hata göster
if (empty($form['fields']) || count(array_filter($steps, function($step) { return !empty($step['fields']); })) === 0) {
    $layoutPath = __DIR__ . '/layouts/default.php';
    if (file_exists($layoutPath)) {
        $title = $page['meta_title'] ?? $page['title'] ?? 'Hata';
        $meta_description = $page['meta_description'] ?? '';
        $current_page = 'quote-request';
        $content = '<div class="container mx-auto px-4 py-16">
            <div class="max-w-2xl mx-auto bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6">
                <div class="flex items-start gap-4">
                    <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-3xl">error</span>
                    <div>
                        <h2 class="text-red-800 dark:text-red-200 text-xl font-bold mb-2">Form Hatası</h2>
                        <p class="text-red-700 dark:text-red-300">Teklif alma formunda alan bulunamadı. Lütfen admin panelden form alanlarını kontrol edin.</p>
                        <a href="' . admin_url('module/quote-request') . '" class="mt-4 inline-block px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            Admin Panele Git
                        </a>
                    </div>
                </div>
            </div>
        </div>';
        include $layoutPath;
        exit;
    } else {
        die('<h1>Form Hatası</h1><p>Teklif alma formunda alan bulunamadı.</p>');
    }
}

// Boş adımları kaldır ve numaralandır
$steps = array_filter($steps, function($step) {
    return !empty($step['fields']);
});
$steps = array_values($steps);
$totalSteps = count($steps);

foreach ($steps as $index => &$step) {
    $step['stepNumber'] = $index + 1;
}
unset($step);

// Layout değişkenlerini ayarla
if (!isset($title)) {
    $title = $page['meta_title'] ?? $pageTitle;
}
if (!isset($meta_description)) {
    $meta_description = $page['meta_description'] ?? $pageDescription;
}
if (!isset($meta_keywords)) {
    $meta_keywords = $page['meta_keywords'] ?? '';
}
$current_page = 'quote-request';

// Layout'u kullan
$layoutPath = __DIR__ . '/layouts/default.php';
$componentPath = __DIR__ . '/components/quote-form.php';

// Content'i yakala
ob_start();
if (file_exists($componentPath)) {
    try {
        include $componentPath;
        $content = ob_get_clean();
    } catch (Exception $e) {
        ob_end_clean();
        $content = '<div class="container mx-auto px-4 py-16"><div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"><strong>Hata:</strong> ' . htmlspecialchars($e->getMessage()) . '</div></div>';
    }
} else {
    ob_end_clean();
    $content = '<div class="container mx-auto px-4 py-16"><div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"><strong>Hata:</strong> Quote form component bulunamadı.</div></div>';
}

// Layout'u include et
if (file_exists($layoutPath)) {
    include $layoutPath;
} else {
    echo $content;
}
?>
