<?php
/**
 * WordPress Tarzı Yardımcı Fonksiyonlar
 * CMS için genel kullanım fonksiyonları
 */

/**
 * Tarihi Türkçe formatta döndürür
 * @param string $date Tarih (Y-m-d H:i:s formatında)
 * @param string $format Çıktı formatı: 'short' (15 Ara 2025), 'long' (15 Aralık 2025), 'full' (15 Aralık 2025, Cumartesi)
 * @return string Türkçe formatlanmış tarih
 */
function turkish_date($date, $format = 'short') {
    if (empty($date)) {
        return '';
    }
    
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    
    if (!$timestamp) {
        return $date;
    }
    
    $aylar_kisa = [
        1 => 'Oca', 2 => 'Şub', 3 => 'Mar', 4 => 'Nis', 5 => 'May', 6 => 'Haz',
        7 => 'Tem', 8 => 'Ağu', 9 => 'Eyl', 10 => 'Eki', 11 => 'Kas', 12 => 'Ara'
    ];
    
    $aylar_uzun = [
        1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan', 5 => 'Mayıs', 6 => 'Haziran',
        7 => 'Temmuz', 8 => 'Ağustos', 9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık'
    ];
    
    $gunler = [
        'Monday' => 'Pazartesi',
        'Tuesday' => 'Salı',
        'Wednesday' => 'Çarşamba',
        'Thursday' => 'Perşembe',
        'Friday' => 'Cuma',
        'Saturday' => 'Cumartesi',
        'Sunday' => 'Pazar'
    ];
    
    $gun = date('j', $timestamp);
    $ay = date('n', $timestamp);
    $yil = date('Y', $timestamp);
    $gun_adi = $gunler[date('l', $timestamp)];
    
    switch ($format) {
        case 'long':
            return $gun . ' ' . $aylar_uzun[$ay] . ' ' . $yil;
        case 'full':
            return $gun . ' ' . $aylar_uzun[$ay] . ' ' . $yil . ', ' . $gun_adi;
        case 'short':
        default:
            return $gun . ' ' . $aylar_kisa[$ay] . ' ' . $yil;
    }
}

/**
 * Veritabanı instance'ını döndürür
 */
function get_db() {
    return Database::getInstance();
}

/**
 * Option değerini getirir (WordPress tarzı)
 */
function get_option($key, $default = null) {
    $db = get_db();
    $result = $db->fetch(
        "SELECT option_value FROM options WHERE option_name = ?",
        [$key]
    );
    
    if ($result) {
        return maybe_unserialize($result['option_value']);
    }
    
    return $default;
}

/**
 * Option değerini kaydeder
 */
function update_option($key, $value) {
    $db = get_db();
    $serialized = maybe_serialize($value);
    
    $existing = $db->fetch(
        "SELECT option_id FROM options WHERE option_name = ?",
        [$key]
    );
    
    if ($existing) {
        $db->query(
            "UPDATE options SET option_value = ? WHERE option_name = ?",
            [$serialized, $key]
        );
    } else {
        $db->query(
            "INSERT INTO options (option_name, option_value) VALUES (?, ?)",
            [$key, $serialized]
        );
    }
}

/**
 * String'i serialize eder (gerekirse)
 */
function maybe_serialize($data) {
    if (is_array($data) || is_object($data)) {
        return serialize($data);
    }
    return $data;
}

/**
 * String'i unserialize eder (gerekirse)
 */
function maybe_unserialize($data) {
    if (is_string($data)) {
        $unserialized = @unserialize($data);
        if ($unserialized !== false || $data === serialize(false)) {
            return $unserialized;
        }
    }
    return $data;
}

/**
 * Sözleşme içeriğindeki yer tutucuları site ayarlarıyla değiştirir
 * Örn: [ŞİRKET ADI] -> Ayarlardaki şirket adı
 */
function process_agreement_content($content) {
    // Site ayarlarından bilgileri al
    $companyName = get_option('company_name', '[ŞİRKET ADI]');
    $companyEmail = get_option('company_email', '[E-POSTA]');
    $companyPhone = get_option('company_phone', '[TELEFON]');
    $companyAddress = get_option('company_address', '[ADRES]');
    $companyCity = get_option('company_city', '[ŞEHİR]');
    $companyTaxNumber = get_option('company_tax_number', '[VERGİ NO]');
    $companyKep = get_option('company_kep', '[KEP ADRESİ]');
    $siteName = get_option('seo_title', '[SİTE ADI]');
    
    // Web sitesi URL'sini al
    $siteUrl = site_url();
    
    // Bugünün tarihi
    $today = date('d.m.Y');
    
    // Yer tutucuları değiştir
    $replacements = [
        '[ŞİRKET ADI]' => $companyName ?: '[ŞİRKET ADI]',
        '[ŞİRKET_ADI]' => $companyName ?: '[ŞİRKET ADI]',
        '[SIRKET ADI]' => $companyName ?: '[ŞİRKET ADI]',
        '[E-POSTA]' => $companyEmail ?: '[E-POSTA]',
        '[EMAIL]' => $companyEmail ?: '[E-POSTA]',
        '[TELEFON]' => $companyPhone ?: '[TELEFON]',
        '[ADRES]' => $companyAddress ?: '[ADRES]',
        '[ŞEHİR]' => $companyCity ?: '[ŞEHİR]',
        '[SEHIR]' => $companyCity ?: '[ŞEHİR]',
        '[VERGİ NO]' => $companyTaxNumber ?: '[VERGİ NO]',
        '[VERGİ_NO]' => $companyTaxNumber ?: '[VERGİ NO]',
        '[KEP ADRESİ]' => $companyKep ?: '[KEP ADRESİ]',
        '[KEP]' => $companyKep ?: '[KEP ADRESİ]',
        '[SİTE ADI]' => $siteName ?: '[SİTE ADI]',
        '[SITE ADI]' => $siteName ?: '[SİTE ADI]',
        '[WEB SİTESİ]' => $siteUrl,
        '[WEB SITESI]' => $siteUrl,
        '[WEBSITE]' => $siteUrl,
        '[TARİH]' => $today,
        '[TARIH]' => $today,
        '[DATE]' => $today,
        '[HİZMET TANIMI]' => get_option('seo_description', '[HİZMET TANIMI]') ?: '[HİZMET TANIMI]',
    ];
    
    // İçeriği işle
    $processedContent = str_replace(
        array_keys($replacements),
        array_values($replacements),
        $content
    );
    
    return $processedContent;
}

/**
 * Sözleşme içeriğinde henüz değiştirilmemiş yer tutucuları tespit eder
 * Admin panelde uyarı göstermek için kullanılır
 */
function get_missing_agreement_placeholders($content) {
    $placeholders = [
        '[ŞİRKET ADI]', '[SIRKET ADI]', '[ŞİRKET_ADI]',
        '[E-POSTA]', '[EMAIL]',
        '[TELEFON]',
        '[ADRES]',
        '[ŞEHİR]', '[SEHIR]',
        '[VERGİ NO]', '[VERGİ_NO]',
        '[KEP ADRESİ]', '[KEP]',
        '[WEB SİTESİ]', '[WEB SITESI]', '[WEBSITE]',
        '[HİZMET TANIMI]',
    ];
    
    $missing = [];
    foreach ($placeholders as $placeholder) {
        if (strpos($content, $placeholder) !== false) {
            $missing[] = $placeholder;
        }
    }
    
    return array_unique($missing);
}

/**
 * XSS koruması için string temizler
 */
function esc_html($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * URL için string temizler
 */
function esc_url($url) {
    return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
}

/**
 * Attribute için string temizler
 */
function esc_attr($text) {
    return esc_html($text);
}

/**
 * JavaScript için string temizler
 */
function esc_js($text) {
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Textarea için string temizler
 */
if (!function_exists('esc_textarea')) {
    function esc_textarea($text) {
        return esc_html($text);
    }
}

/**
 * Site URL'ini döndürür (dil prefix'i olmadan)
 * Asset URL'leri, admin URL'leri vb. için kullanılır
 */
function site_url($path = '') {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // SCRIPT_NAME kontrolü - kök dizinden çalışıyorsak basePath boş olmalı
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $basePath = dirname($scriptName);
    
    // Kök dizindeyse basePath'i temizle
    if ($basePath === '/' || $basePath === '\\') {
        $basePath = '';
    }
    
    // Path'i temizle ve birleştir
    $cleanPath = $path ? '/' . ltrim($path, '/') : '';
    
    return $protocol . '://' . $host . $basePath . $cleanPath;
}

/**
 * Dil prefix'li URL döndürür
 * Frontend linkleri için kullanılır (menü, butonlar vb.)
 * 
 * @param string $path URL path'i (örn: '/contact', '/blog')
 * @param string|null $lang Hedef dil kodu (null ise mevcut dil kullanılır)
 * @return string Dil prefix'li URL
 */
function localized_url($path = '', $lang = null) {
    // Base URL'i al (dil prefix'i olmadan)
    $baseUrl = site_url();
    
    // Mevcut dili al
    $currentLang = $lang;
    if ($currentLang === null) {
        $currentLang = get_current_language();
    }
    
    // Varsayılan dili al
    $defaultLang = get_default_language();
    
    // Path'i temizle
    $cleanPath = $path ? '/' . ltrim($path, '/') : '';
    
    // Varsayılan dil ise prefix ekleme
    if ($currentLang === $defaultLang || empty($currentLang)) {
        return $baseUrl . $cleanPath;
    }
    
    // Dil prefix'i ekle
    return $baseUrl . '/' . $currentLang . $cleanPath;
}

/**
 * Mevcut dili döndürür
 */
function get_current_language() {
    // Session'dan kontrol et
    if (isset($_SESSION['current_language'])) {
        return $_SESSION['current_language'];
    }
    
    // URL'den kontrol et
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $path = parse_url($uri, PHP_URL_PATH);
    $segments = explode('/', trim($path, '/'));
    
    if (!empty($segments[0]) && strlen($segments[0]) === 2) {
        // 2 karakterli segment dil kodu olabilir
        $potentialLang = strtolower($segments[0]);
        
        // Aktif diller listesini kontrol et
        $db = get_db();
        try {
            $lang = $db->fetch(
                "SELECT code FROM languages WHERE code = ? AND is_active = 1",
                [$potentialLang]
            );
            if ($lang) {
                $_SESSION['current_language'] = $potentialLang;
                return $potentialLang;
            }
        } catch (Exception $e) {
            // Veritabanı hatası, varsayılan dil döndür
        }
    }
    
    return get_default_language();
}

/**
 * Varsayılan dili döndürür
 */
function get_default_language() {
    // Module settings'den al
    $settings = get_option('module_translation_settings');
    if ($settings && is_array($settings) && isset($settings['default_language'])) {
        return $settings['default_language'];
    }
    
    // Varsayılan olarak Türkçe
    return 'tr';
}

/**
 * Menü URL'ini dil prefix'i ile döndürür
 * Menü linkleri için kullanılır
 * 
 * @param string $url Orijinal URL
 * @return string Dil prefix'li URL
 */
function get_localized_menu_url($url) {
    // Boş URL veya # ise olduğu gibi döndür
    if (empty($url) || $url === '#') {
        return $url;
    }
    
    // Harici linkler (http/https ile başlayan) olduğu gibi döndür
    if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
        return $url;
    }
    
    // Admin linkleri olduğu gibi döndür
    if (strpos($url, '/admin') === 0) {
        return site_url($url);
    }
    
    // localized_url fonksiyonu varsa kullan
    if (function_exists('localized_url')) {
        return localized_url($url);
    }
    
    return $url;
}

/**
 * Admin URL'ini döndürür
 */
function admin_url($page = '', $params = []) {
    $base = site_url('admin.php');
    $qs = [];
    if ($page) {
        $qs['page'] = $page;
    }
    if (is_array($params)) {
        $qs = array_merge($qs, $params);
    }
    return $base . (count($qs) ? '?' . http_build_query($qs) : '');
}

/**
 * Mevcut kullanıcıyı döndürür
 */
function get_logged_in_user() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $db = get_db();
    $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    
    return $user ?: null;
}

/**
 * Kullanıcı giriş yapmış mı kontrol eder
 */
function is_user_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Kullanıcı admin mi kontrol eder
 */
function is_admin() {
    $user = get_logged_in_user();
    return $user && isset($user['role']) && ($user['role'] === 'admin' || $user['role'] === 'super_admin');
}

/**
 * Kullanıcı süper admin mi kontrol eder
 */
function is_super_admin() {
    $user = get_logged_in_user();
    return $user && isset($user['role']) && $user['role'] === 'super_admin';
}

/**
 * Kullanıcının belirli bir yetkisi var mı kontrol eder
 * Sadece super_admin HER ZAMAN true döner
 * Admin dahil diğer roller veritabanından kontrol edilir
 */
function current_user_can($permission) {
    $user = get_logged_in_user();
    if (!$user) {
        return false;
    }
    
    $role = strtolower(trim($user['role'] ?? 'user'));
    
    // Sadece super_admin HER ZAMAN yetkili
    if ($role === 'super_admin') {
        return true;
    }
    
    // Admin dahil tüm roller için Role sınıfını kullan
    require_once __DIR__ . '/../core/Role.php';
    return Role::hasPermission($role, $permission);
}

/**
 * Kullanıcının herhangi bir yetkisi var mı kontrol eder
 */
function current_user_can_any($permissions) {
    foreach ($permissions as $permission) {
        if (current_user_can($permission)) {
            return true;
        }
    }
    return false;
}

/**
 * Kullanıcının tüm yetkileri var mı kontrol eder
 */
function current_user_can_all($permissions) {
    foreach ($permissions as $permission) {
        if (!current_user_can($permission)) {
            return false;
        }
    }
    return true;
}

/**
 * Kullanıcının rolünü getirir
 */
function get_user_role($userId = null) {
    if ($userId === null) {
        $user = get_logged_in_user();
        return $user['role'] ?? 'user';
    }
    
    $db = get_db();
    $user = $db->fetch("SELECT role FROM users WHERE id = ?", [$userId]);
    return $user['role'] ?? 'user';
}

/**
 * Rol adını getirir
 */
function get_role_name($role) {
    require_once __DIR__ . '/../core/Role.php';
    return Role::getName($role);
}

/**
 * Rol açıklamasını getirir
 */
function get_role_description($role) {
    require_once __DIR__ . '/../core/Role.php';
    return Role::getDescription($role);
}

/**
 * Kullanıcının rol seviyesi diğerinden yüksek mi?
 */
function user_role_is_higher_than($role1, $role2) {
    require_once __DIR__ . '/../core/Role.php';
    return Role::isHigherThan($role1, $role2);
}

/**
 * Kullanıcı admin mi? (admin veya super_admin)
 */
function is_user_admin() {
    $user = get_logged_in_user();
    if (!$user) return false;
    
    $role = strtolower(trim($user['role'] ?? 'user'));
    return $role === 'admin' || $role === 'super_admin';
}

/**
 * Konuma göre menü getirir
 * @param string $location Menü konumu (header, footer, sidebar, mobile)
 * @return array|null Menü verisi ve öğeleri
 */
function get_menu($location) {
    try {
        $db = get_db();
        
        if (!$db) {
            return null;
        }
        
        // Menüyü getir
        $menu = $db->fetch(
            "SELECT * FROM menus WHERE location = ? AND status = 'active' LIMIT 1",
            [$location]
        );
        
        if (!$menu) {
            return null;
        }
        
        // Menü öğelerini getir - order sütunu yoksa id'ye göre sırala
        try {
            $items = $db->fetchAll(
                "SELECT * FROM menu_items WHERE menu_id = ? AND status = 'active' ORDER BY `order` ASC, id ASC",
                [$menu['id']]
            );
        } catch (Exception $e) {
            // order sütunu yoksa fallback
            $items = $db->fetchAll(
                "SELECT * FROM menu_items WHERE menu_id = ? AND status = 'active' ORDER BY id ASC",
                [$menu['id']]
            );
        }
        
        // Hiyerarşik yapıya çevir
        $menu['items'] = build_menu_tree($items);
        
        return $menu;
    } catch (Exception $e) {
        // Herhangi bir hata durumunda null döndür
        error_log('get_menu error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Menü öğelerini hiyerarşik yapıya çevirir
 */
function build_menu_tree($items, $parentId = null) {
    $tree = [];
    
    foreach ($items as $item) {
        if ($item['parent_id'] == $parentId) {
            $children = build_menu_tree($items, $item['id']);
            if ($children) {
                $item['children'] = $children;
            }
            $tree[] = $item;
        }
    }
    
    return $tree;
}

/**
 * Menüyü HTML olarak render eder
 * @param string $location Menü konumu
 * @param array $options Render seçenekleri
 * @return string HTML çıktısı
 */
function render_menu($location, $options = []) {
    $menu = get_menu($location);
    
    if (!$menu || empty($menu['items'])) {
        return '';
    }
    
    $defaults = [
        'container' => 'nav',
        'container_class' => '',
        'menu_class' => 'menu',
        'item_class' => 'menu-item',
        'link_class' => 'menu-link',
        'submenu_class' => 'submenu',
        'has_children_class' => 'has-children',
        'depth' => 0 // 0 = sınırsız
    ];
    
    $options = array_merge($defaults, $options);
    
    $html = render_menu_items($menu['items'], $options, 0);
    
    if ($options['container']) {
        $containerClass = $options['container_class'] ? ' class="' . esc_attr($options['container_class']) . '"' : '';
        $html = '<' . $options['container'] . $containerClass . '>' . $html . '</' . $options['container'] . '>';
    }
    
    return $html;
}

/**
 * Menü öğelerini recursive olarak render eder
 */
function render_menu_items($items, $options, $level) {
    if (empty($items)) {
        return '';
    }
    
    // Derinlik kontrolü
    if ($options['depth'] > 0 && $level >= $options['depth']) {
        return '';
    }
    
    $menuClass = $level === 0 ? $options['menu_class'] : $options['submenu_class'];
    $html = '<ul class="' . esc_attr($menuClass) . '">';
    
    foreach ($items as $item) {
        $hasChildren = !empty($item['children']);
        $itemClasses = [$options['item_class']];
        
        if ($hasChildren) {
            $itemClasses[] = $options['has_children_class'];
        }
        
        // Özel CSS sınıfı varsa ekle
        if (!empty($item['css_class'])) {
            $itemClasses[] = $item['css_class'];
        }
        
        $html .= '<li class="' . esc_attr(implode(' ', $itemClasses)) . '">';
        
        // Link
        $linkAttrs = [
            'href' => esc_url($item['url']),
            'class' => $options['link_class']
        ];
        
        if ($item['target'] === '_blank') {
            $linkAttrs['target'] = '_blank';
            $linkAttrs['rel'] = 'noopener noreferrer';
        }
        
        // İkon
        $iconHtml = '';
        if (!empty($item['icon'])) {
            $iconHtml = '<span class="material-symbols-outlined menu-icon">' . esc_html($item['icon']) . '</span>';
        }
        
        $attrString = '';
        foreach ($linkAttrs as $key => $value) {
            $attrString .= ' ' . $key . '="' . $value . '"';
        }
        
        $html .= '<a' . $attrString . '>' . $iconHtml . esc_html($item['title']) . '</a>';
        
        // Alt menü
        if ($hasChildren) {
            $html .= render_menu_items($item['children'], $options, $level + 1);
        }
        
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    
    return $html;
}

// ==================== FORM FONKSİYONLARI ====================

/**
 * Form'u slug'a göre getirir
 * @param string $slug Form slug'ı
 * @return array|null Form verisi
 */
function get_form($slug) {
    require_once __DIR__ . '/../app/models/Form.php';
    require_once __DIR__ . '/../app/models/FormField.php';
    
    $formModel = new Form();
    return $formModel->findBySlugWithFields($slug);
}

/**
 * Form'u ID'ye göre getirir
 * @param int $id Form ID'si
 * @return array|null Form verisi
 */
function get_form_by_id($id) {
    require_once __DIR__ . '/../app/models/Form.php';
    require_once __DIR__ . '/../app/models/FormField.php';
    
    $formModel = new Form();
    return $formModel->findWithFields($id);
}

/**
 * Form'u slug'a göre render eder ve döndürür
 * @param string $slug Form slug'ı
 * @return string Form HTML'i
 */
function cms_form($slug) {
    $form = get_form($slug);
    
    if (!$form || $form['status'] !== 'active') {
        return '<div class="form-error">Form bulunamadı veya aktif değil.</div>';
    }
    
    return _render_cms_form($form);
}

/**
 * Form'u ID'ye göre render eder ve döndürür
 * @param int $id Form ID'si
 * @return string Form HTML'i
 */
function cms_form_by_id($id) {
    $form = get_form_by_id($id);
    
    if (!$form || $form['status'] !== 'active') {
        return '<div class="form-error">Form bulunamadı veya aktif değil.</div>';
    }
    
    return _render_cms_form($form);
}

/**
 * Form'u ekrana yazdırır (echo)
 * @param string $slug Form slug'ı
 */
function the_form($slug) {
    echo cms_form($slug);
}

/**
 * Form'u ID ile ekrana yazdırır (echo)
 * @param int $id Form ID'si
 */
function the_form_by_id($id) {
    echo cms_form_by_id($id);
}

/**
 * İçerikteki form shortcode'larını işler
 * [form slug="iletisim"] veya [form id="1"]
 * @param string $content İçerik
 * @return string İşlenmiş içerik
 */
function process_form_shortcodes($content) {
    // [form slug="xxx"] formatı
    $content = preg_replace_callback('/\[form\s+slug=["\']([^"\']+)["\']\s*\]/i', function($matches) {
        return cms_form($matches[1]);
    }, $content);
    
    // [form id="xxx"] formatı
    $content = preg_replace_callback('/\[form\s+id=["\'](\d+)["\']\s*\]/i', function($matches) {
        return cms_form_by_id((int)$matches[1]);
    }, $content);
    
    return $content;
}

/**
 * Internal: Form HTML'ini oluşturur
 */
function _render_cms_form($form) {
    $styleClass = 'form-style-' . ($form['form_style'] ?? 'default');
    $layoutClass = 'form-layout-' . ($form['layout'] ?? 'vertical');
    $formId = 'cms-form-' . $form['id'];
    
    ob_start();
    ?>
    <div class="cms-form-wrapper" id="<?php echo esc_attr($formId); ?>-wrapper">
        <?php if (!empty($form['description'])): ?>
            <p class="form-description"><?php echo esc_html($form['description']); ?></p>
        <?php endif; ?>
        
        <form id="<?php echo esc_attr($formId); ?>" 
              class="cms-form <?php echo esc_attr($styleClass); ?> <?php echo esc_attr($layoutClass); ?>" 
              method="POST" 
              action="<?php echo site_url('forms/submit'); ?>"
              data-form-id="<?php echo esc_attr($form['id']); ?>">
            
            <input type="hidden" name="_form_id" value="<?php echo esc_attr($form['id']); ?>">
            
            <?php 
            // Honeypot spam koruması
            $honeypotEnabled = get_option('honeypot_enabled', 1); // Varsayılan aktif
            if ($honeypotEnabled): 
            ?>
            <!-- Honeypot alanı - Botları yakalamak için görünmez -->
            <input type="text" 
                   name="website_url" 
                   value="" 
                   tabindex="-1" 
                   autocomplete="off" 
                   style="position: absolute; left: -9999px; opacity: 0; pointer-events: none;"
                   aria-hidden="true">
            <?php endif; ?>
            
            <div class="form-fields">
                <?php if (!empty($form['fields'])): ?>
                    <?php foreach ($form['fields'] as $field): ?>
                        <?php 
                        // Status kontrolü - hem status hem is_active kontrol et
                        $isActive = true; // Varsayılan aktif
                        if (isset($field['status'])) {
                            $isActive = ($field['status'] === 'active');
                        } elseif (isset($field['is_active'])) {
                            $isActive = ($field['is_active'] == 1 || $field['is_active'] === true);
                        }
                        if (!$isActive) continue; 
                        
                        // DEBUG: Field bilgilerini log'a yaz
                        $debugType = $field['type'] ?? $field['field_type'] ?? 'NONE';
                        error_log("Frontend Form Field - name: " . ($field['name'] ?? 'unknown') . ", type: " . $debugType . ", status: " . ($field['status'] ?? 'N/A'));
                        ?>
                        <?php _render_form_field($field); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($form['fields'])): ?>
                <div class="form-submit">
                    <button type="submit" class="submit-button" style="background-color: <?php echo esc_attr($form['submit_button_color'] ?? '#137fec'); ?>">
                        <span class="button-text"><?php echo esc_html($form['submit_button_text'] ?? 'Gönder'); ?></span>
                        <span class="button-loading" style="display: none;">
                            <svg class="animate-spin" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-linecap="round" opacity="0.25"></circle>
                                <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                            </svg>
                        </span>
                    </button>
                </div>
            <?php endif; ?>
        </form>
        
        <div id="<?php echo esc_attr($formId); ?>-success" class="form-success" style="display: none;">
            <div class="success-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <p class="success-message"><?php echo esc_html($form['success_message'] ?? 'Formunuz başarıyla gönderildi!'); ?></p>
        </div>
        
        <div id="<?php echo esc_attr($formId); ?>-error" class="form-error-message" style="display: none;">
            <p></p>
        </div>
    </div>
    
    <script>
    (function() {
        const form = document.getElementById('<?php echo esc_attr($formId); ?>');
        const successEl = document.getElementById('<?php echo esc_attr($formId); ?>-success');
        const errorEl = document.getElementById('<?php echo esc_attr($formId); ?>-error');
        
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const submitBtn = form.querySelector('.submit-button');
                const btnText = submitBtn.querySelector('.button-text');
                const btnLoading = submitBtn.querySelector('.button-loading');
                
                submitBtn.disabled = true;
                btnText.style.display = 'none';
                btnLoading.style.display = 'inline-flex';
                
                form.querySelectorAll('.field-error').forEach(el => el.classList.remove('field-error'));
                form.querySelectorAll('.field-error-message').forEach(el => el.remove());
                errorEl.style.display = 'none';
                
                try {
                    const formData = new FormData(form);
                    
                    const response = await fetch(form.action, { method: 'POST', body: formData });
                    const data = await response.json();
                    
                    if (data.success) {
                        form.style.display = 'none';
                        successEl.style.display = 'block';
                        if (data.redirect) {
                            setTimeout(() => { window.location.href = data.redirect; }, 1500);
                        }
                    } else {
                        if (data.errors) {
                            Object.entries(data.errors).forEach(([fieldName, message]) => {
                                const field = form.querySelector(`[name="${fieldName}"], [name="${fieldName}[]"]`);
                                if (field) {
                                    const wrapper = field.closest('.form-field');
                                    if (wrapper) {
                                        wrapper.classList.add('field-error');
                                        const errMsg = document.createElement('div');
                                        errMsg.className = 'field-error-message';
                                        errMsg.textContent = message;
                                        wrapper.appendChild(errMsg);
                                    }
                                }
                            });
                        }
                        if (data.message) {
                            errorEl.querySelector('p').textContent = data.message;
                            errorEl.style.display = 'block';
                        }
                    }
                } catch (error) {
                    errorEl.querySelector('p').textContent = 'Form gönderilirken bir hata oluştu.';
                    errorEl.style.display = 'block';
                } finally {
                    submitBtn.disabled = false;
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                }
            });
        }
    })();
    </script>
    <?php
    return ob_get_clean();
}

/**
 * Internal: Form alanını render eder
 */
function _render_form_field($field) {
    // Field type'ı kontrol et - hem 'type' hem 'field_type' olabilir
    $fieldType = trim($field['type'] ?? $field['field_type'] ?? 'text');
    
    // DEBUG: Field type değerini kontrol et
    if (($field['name'] ?? '') === 'phone') {
        error_log("_render_form_field PHONE - name: " . ($field['name'] ?? 'unknown') . ", type: " . ($field['type'] ?? 'N/A') . ", field_type: " . ($field['field_type'] ?? 'N/A') . ", final fieldType: " . $fieldType);
    }
    
    $widthClass = 'field-width-' . ($field['width'] ?? 'full');
    $requiredClass = $field['required'] ? 'field-required' : '';
    $customClass = $field['css_class'] ?? '';
    
    // Layout elemanları
    if (in_array($fieldType, ['heading', 'paragraph', 'divider'])) {
        _render_layout_element($field);
        return;
    }
    ?>
    <div class="form-field <?php echo esc_attr($widthClass); ?> <?php echo esc_attr($requiredClass); ?> <?php echo esc_attr($customClass); ?>">
        <?php if ($fieldType !== 'hidden'): ?>
            <label class="field-label" for="field-<?php echo esc_attr($field['name']); ?>">
                <?php echo esc_html($field['label']); ?>
                <?php if ($field['required']): ?><span class="required-mark">*</span><?php endif; ?>
            </label>
        <?php endif; ?>
        
        <div class="field-input">
            <?php
            // Input türleri (text, email, phone, tel, number, date, time, datetime)
            $inputTypes = ['text', 'email', 'phone', 'tel', 'number', 'date', 'time', 'datetime'];
            
            // Field type boş veya null ise text olarak kabul et
            if (empty($fieldType) || $fieldType === null) {
                $fieldType = 'text';
            }
            
            // Telefon field için debug
            if (($field['name'] ?? '') === 'phone') {
                echo '<!-- DEBUG: phone field - fieldType=' . htmlspecialchars($fieldType) . ', in_array=' . (in_array($fieldType, $inputTypes) ? 'true' : 'false') . ' -->';
            }
            
            if (in_array($fieldType, $inputTypes)):
                $inputType = $fieldType;
                if ($fieldType === 'phone' || $fieldType === 'tel') $inputType = 'tel';
                if ($fieldType === 'datetime') $inputType = 'datetime-local';
            ?>
                <input type="<?php echo esc_attr($inputType); ?>" 
                       id="field-<?php echo esc_attr($field['name']); ?>"
                       name="<?php echo esc_attr($field['name']); ?>" 
                       placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                       value="<?php echo esc_attr($field['default_value'] ?? ''); ?>"
                       <?php echo $field['required'] ? 'required' : ''; ?>>
            <?php elseif ($fieldType === 'textarea'): ?>
                <textarea id="field-<?php echo esc_attr($field['name']); ?>"
                          name="<?php echo esc_attr($field['name']); ?>" 
                          placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                          rows="4"
                          <?php echo $field['required'] ? 'required' : ''; ?>><?php echo esc_html($field['default_value'] ?? ''); ?></textarea>
            <?php elseif ($fieldType === 'select'): ?>
                <select id="field-<?php echo esc_attr($field['name']); ?>"
                        name="<?php echo esc_attr($field['name']); ?>" 
                        <?php echo $field['required'] ? 'required' : ''; ?>>
                    <option value=""><?php echo esc_html($field['placeholder'] ?? 'Seçiniz...'); ?></option>
                    <?php if (!empty($field['options'])): ?>
                        <?php foreach ($field['options'] as $option): ?>
                            <option value="<?php echo esc_attr($option['value'] ?? $option); ?>"><?php echo esc_html($option['label'] ?? $option); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            <?php elseif ($fieldType === 'checkbox'): ?>
                <div class="checkbox-group">
                    <?php if (!empty($field['options'])): foreach ($field['options'] as $i => $option): ?>
                        <label class="checkbox-label">
                            <input type="checkbox" name="<?php echo esc_attr($field['name']); ?>[]" value="<?php echo esc_attr($option['value'] ?? $option); ?>">
                            <span><?php echo esc_html($option['label'] ?? $option); ?></span>
                        </label>
                    <?php endforeach; endif; ?>
                </div>
            <?php elseif ($fieldType === 'radio'): ?>
                <div class="radio-group">
                    <?php if (!empty($field['options'])): foreach ($field['options'] as $i => $option): ?>
                        <label class="radio-label">
                            <input type="radio" name="<?php echo esc_attr($field['name']); ?>" value="<?php echo esc_attr($option['value'] ?? $option); ?>">
                            <span><?php echo esc_html($option['label'] ?? $option); ?></span>
                        </label>
                    <?php endforeach; endif; ?>
                </div>
            <?php elseif ($fieldType === 'file'): ?>
                <input type="file" id="field-<?php echo esc_attr($field['name']); ?>" name="<?php echo esc_attr($field['name']); ?>" <?php echo $field['required'] ? 'required' : ''; ?>>
            <?php elseif ($fieldType === 'hidden'): ?>
                <input type="hidden" name="<?php echo esc_attr($field['name']); ?>" value="<?php echo esc_attr($field['default_value'] ?? ''); ?>">
            <?php else: ?>
                <!-- Bilinmeyen tip için varsayılan text input -->
                <input type="text" 
                       id="field-<?php echo esc_attr($field['name']); ?>"
                       name="<?php echo esc_attr($field['name']); ?>" 
                       placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                       value="<?php echo esc_attr($field['default_value'] ?? ''); ?>"
                       <?php echo $field['required'] ? 'required' : ''; ?>>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($field['help_text'])): ?>
            <div class="field-help"><?php echo esc_html($field['help_text']); ?></div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Internal: Layout elemanını render eder
 */
function _render_layout_element($field) {
    switch ($field['type']) {
        case 'heading':
            echo '<div class="form-heading"><h3>' . esc_html($field['label']) . '</h3></div>';
            break;
        case 'paragraph':
            echo '<div class="form-paragraph"><p>' . nl2br(esc_html($field['default_value'] ?? $field['label'])) . '</p></div>';
            break;
        case 'divider':
            echo '<div class="form-divider"><hr></div>';
            break;
    }
}

// ==================== E-POSTA FONKSİYONLARI ====================

/**
 * E-posta gönderir
 * @param string $to Alıcı e-posta adresi
 * @param string $subject Konu
 * @param string $body Mesaj içeriği (HTML destekler)
 * @param array $options Ek seçenekler (cc, bcc, replyTo, attachments, isHtml)
 * @return bool Başarılı ise true
 */
function cms_mail($to, $subject, $body, $options = []) {
    require_once __DIR__ . '/../core/Mailer.php';
    
    $mailer = Mailer::getInstance();
    return $mailer->send($to, $subject, $body, $options);
}

/**
 * Şablon ile e-posta gönderir
 * @param string $to Alıcı
 * @param string $subject Konu
 * @param string $templateName Şablon adı
 * @param array $data Şablon verileri
 * @param array $options Ek seçenekler
 * @return bool
 */
function cms_mail_template($to, $subject, $templateName, $data = [], $options = []) {
    require_once __DIR__ . '/../core/Mailer.php';
    
    $mailer = Mailer::getInstance();
    return $mailer->sendTemplate($to, $subject, $templateName, $data, $options);
}

/**
 * SMTP'nin yapılandırılıp yapılandırılmadığını kontrol eder
 * @return bool
 */
function is_smtp_configured() {
    $host = get_option('smtp_host', '');
    $port = get_option('smtp_port', '');
    $username = get_option('smtp_username', '');
    $fromEmail = get_option('smtp_from_email', '');
    
    return !empty($host) && !empty($port) && !empty($username) && !empty($fromEmail);
}

/**
 * Son e-posta hatasını döndürür
 * @return string
 */
function get_last_mail_error() {
    require_once __DIR__ . '/../core/Mailer.php';
    
    $mailer = Mailer::getInstance();
    return $mailer->getLastError();
}

/**
 * Form gönderiminde admin'e e-posta gönderir
 * @param array $form Form verisi
 * @param array $submission Gönderim verisi
 * @return bool
 */
function send_form_notification_email($form, $submission) {
    if (!is_smtp_configured()) {
        return false;
    }
    
    // Admin e-postası
    $adminEmail = get_option('smtp_from_email', '');
    if (empty($adminEmail)) {
        return false;
    }
    
    $siteName = get_option('seo_title', 'CMS');
    $subject = "[{$siteName}] Yeni Form Gönderimi: " . ($form['name'] ?? 'Form');
    
    // Form verilerini HTML tablosuna çevir
    $dataHtml = '<table style="width:100%;border-collapse:collapse;margin-top:20px;">';
    $dataHtml .= '<tr style="background:#f8f9fa;"><th style="text-align:left;padding:10px;border:1px solid #dee2e6;">Alan</th><th style="text-align:left;padding:10px;border:1px solid #dee2e6;">Değer</th></tr>';
    
    $formData = is_array($submission['form_data']) ? $submission['form_data'] : json_decode($submission['form_data'], true);
    
    if ($formData) {
        foreach ($formData as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $dataHtml .= '<tr>';
            $dataHtml .= '<td style="padding:10px;border:1px solid #dee2e6;font-weight:500;">' . esc_html($key) . '</td>';
            $dataHtml .= '<td style="padding:10px;border:1px solid #dee2e6;">' . esc_html($value) . '</td>';
            $dataHtml .= '</tr>';
        }
    }
    
    $dataHtml .= '</table>';
    
    $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #333; margin-bottom: 20px;'>Yeni Form Gönderimi</h2>
            <p style='color: #555;'><strong>Form:</strong> {$form['name']}</p>
            <p style='color: #555;'><strong>Tarih:</strong> " . date('d.m.Y H:i:s') . "</p>
            <p style='color: #555;'><strong>IP Adresi:</strong> " . ($submission['ip_address'] ?? 'Bilinmiyor') . "</p>
            <hr style='border:none;border-top:1px solid #eee;margin:20px 0;'>
            <h3 style='color: #333;'>Gönderilen Veriler:</h3>
            {$dataHtml}
            <hr style='border:none;border-top:1px solid #eee;margin:20px 0;'>
            <p style='color:#999;font-size:12px;'>Bu e-posta {$siteName} tarafından otomatik olarak gönderilmiştir.</p>
        </div>
    ";
    
    return cms_mail($adminEmail, $subject, $body);
}

/**
 * Şifre sıfırlama e-postası gönderir
 * @param string $email Kullanıcı e-postası
 * @param string $resetLink Sıfırlama linki
 * @return bool
 */
function send_password_reset_email($email, $resetLink) {
    if (!is_smtp_configured()) {
        return false;
    }
    
    $siteName = get_option('seo_title', 'CMS');
    $subject = "[{$siteName}] Şifre Sıfırlama Talebi";
    
    $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #333; margin-bottom: 20px;'>Şifre Sıfırlama</h2>
            <p style='color: #555; line-height: 1.6;'>Merhaba,</p>
            <p style='color: #555; line-height: 1.6;'>Hesabınız için bir şifre sıfırlama talebi aldık. Şifrenizi sıfırlamak için aşağıdaki bağlantıya tıklayın:</p>
            <p style='margin:30px 0;'>
                <a href='" . esc_url($resetLink) . "' style='display:inline-block;padding:12px 24px;background:#137fec;color:#fff;text-decoration:none;border-radius:4px;'>Şifremi Sıfırla</a>
            </p>
            <p style='color: #555; line-height: 1.6;'>Bu bağlantı 24 saat boyunca geçerlidir.</p>
            <p style='color: #555; line-height: 1.6;'>Eğer bu talebi siz yapmadıysanız, bu e-postayı görmezden gelebilirsiniz.</p>
            <hr style='border:none;border-top:1px solid #eee;margin:20px 0;'>
            <p style='color:#999;font-size:12px;'>Bu e-posta {$siteName} tarafından otomatik olarak gönderilmiştir.</p>
        </div>
    ";
    
    return cms_mail($email, $subject, $body);
}

/**
 * Hoşgeldiniz e-postası gönderir (yeni kullanıcı kaydı için)
 * @param string $email Kullanıcı e-postası
 * @param string $username Kullanıcı adı
 * @param string|null $tempPassword Geçici şifre (opsiyonel)
 * @return bool
 */
function send_welcome_email($email, $username, $tempPassword = null) {
    if (!is_smtp_configured()) {
        return false;
    }
    
    $siteName = get_option('seo_title', 'CMS');
    $subject = "[{$siteName}] Hoş Geldiniz!";
    
    $passwordInfo = '';
    if ($tempPassword) {
        $passwordInfo = "
            <p><strong>Geçici Şifreniz:</strong> {$tempPassword}</p>
            <p style='color:#dc3545;'>Güvenliğiniz için lütfen giriş yaptıktan sonra şifrenizi değiştirin.</p>
        ";
    }
    
    $loginUrl = admin_url('login');
    
    $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #333; margin-bottom: 20px;'>Hoş Geldiniz!</h2>
            <p style='color: #555; line-height: 1.6;'>Merhaba <strong>{$username}</strong>,</p>
            <p style='color: #555; line-height: 1.6;'>{$siteName} yönetim paneline hoş geldiniz! Hesabınız başarıyla oluşturuldu.</p>
            {$passwordInfo}
            <p style='margin:30px 0;'>
                <a href='" . esc_url($loginUrl) . "' style='display:inline-block;padding:12px 24px;background:#137fec;color:#fff;text-decoration:none;border-radius:4px;'>Giriş Yap</a>
            </p>
            <hr style='border:none;border-top:1px solid #eee;margin:20px 0;'>
            <p style='color:#999;font-size:12px;'>Bu e-posta {$siteName} tarafından otomatik olarak gönderilmiştir.</p>
        </div>
    ";
    
    return cms_mail($email, $subject, $body);
}


// ==================== MODÜL SİSTEMİ HELPER FUNCTIONS ====================

/**
 * Modül aktif mi kontrol eder
 * @param string $module_name Modül adı
 * @return bool
 */
if (!function_exists('is_module_active')) {
    function is_module_active($module_name) {
        if (!class_exists('ModuleLoader')) {
            return false;
        }
        
        $loader = ModuleLoader::getInstance();
        $module = $loader->getModule($module_name);
        return $module && ($module['is_active'] ?? false);
    }
}

/**
 * Modül ayarını getirir
 * @param string $module_name Modül adı
 * @param string $key Ayar anahtarı
 * @param mixed $default Varsayılan değer
 * @return mixed
 */
if (!function_exists('get_module_setting')) {
    function get_module_setting($module_name, $key, $default = null) {
        if (!class_exists('ModuleLoader')) {
            return $default;
        }
        
        return ModuleLoader::getInstance()->getModuleSetting($module_name, $key, $default);
    }
}

/**
 * Modül tüm ayarlarını getirir
 * @param string $module_name Modül adı
 * @return array
 */
if (!function_exists('get_module_settings')) {
    function get_module_settings($module_name) {
        if (!class_exists('ModuleLoader')) {
            return [];
        }
        
        return ModuleLoader::getInstance()->getModuleSettings($module_name);
    }
}

/**
 * Shortcode'u inline olarak çalıştırır
 * @param string $tag Shortcode adı
 * @param array $atts Nitelikler
 * @param string $content İç içerik
 * @return string
 */
if (!function_exists('do_shortcode_tag')) {
    function do_shortcode_tag($tag, $atts = [], $content = '') {
        if (!class_exists('ShortcodeParser')) {
            return '';
        }
        
        $attsString = '';
        foreach ($atts as $key => $value) {
            $attsString .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }
        
        $shortcodeString = '[' . $tag . $attsString . ']';
        if ($content) {
            $shortcodeString .= $content . '[/' . $tag . ']';
        }
        
        return do_shortcode($shortcodeString);
    }
}

/**
 * Modül URL'si oluşturur (admin)
 * @param string $module_name Modül adı
 * @param string $action Action adı
 * @param array $params Parametreler
 * @return string
 */
function module_admin_url($module_name, $action = '', $params = []) {
    $url = 'module/' . $module_name;
    
    if ($action) {
        $url .= '/' . $action;
    }
    
    if (!empty($params)) {
        $url .= '/' . implode('/', $params);
    }
    
    return admin_url($url);
}

/**
 * Modül asset URL'si oluşturur
 * @param string $module_name Modül adı
 * @param string $asset_path Asset yolu
 * @return string
 */
function module_asset_url($module_name, $asset_path) {
    return site_url('modules/' . $module_name . '/assets/' . ltrim($asset_path, '/'));
}

/**
 * Modül view'ını include eder
 * @param string $module_name Modül adı
 * @param string $view_name View adı
 * @param array $data Data
 */
function module_view($module_name, $view_name, $data = []) {
    $viewPath = dirname(__DIR__) . '/modules/' . $module_name . '/views/' . $view_name . '.php';
    
    if (!file_exists($viewPath)) {
        echo "<!-- Module view not found: {$module_name}/{$view_name} -->";
        return;
    }
    
    extract($data);
    include $viewPath;
}

/**
 * Check permission - yetki kontrolü
 * @param string $permission Yetki kodu (örn: posts.create)
 * @return bool
 */
if (!function_exists('check_permission')) {
    function check_permission($permission) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Rol slug'ı session'da yoksa kullanıcıdan al
        if (!isset($_SESSION['role_slug'])) {
            $user = get_logged_in_user();
            if ($user && isset($user['role'])) {
                $_SESSION['role'] = $user['role'];
                $_SESSION['role_slug'] = strtolower(trim($user['role']));
            } else {
                $_SESSION['role_slug'] = 'user';
            }
        }
        
        // Sadece super_admin bypass
        if (isset($_SESSION['role_slug']) && $_SESSION['role_slug'] === 'super_admin') {
            return true;
        }
        
        // Admin dahil tüm roller için Role sınıfını kullan
        $roleSlug = $_SESSION['role_slug'] ?? 'user';
        
        require_once __DIR__ . '/../core/Role.php';
        return Role::hasPermission($roleSlug, $permission);
    }
}

/**
 * Kullanıcının belirli bir modüle erişim yetkisi var mı?
 * @param string $module_name Modül adı
 * @param string $action Action (view, create, edit, delete)
 * @return bool
 */
function can_access_module($module_name, $action = 'view') {
    $permission = $module_name . '.' . $action;
    return check_permission($permission);
}

/**
 * Responsive image için srcset oluşturur
 * @param string $imageUrl Görsel URL'i
 * @param array $sizes Farklı boyutlar (örn: ['640w', '1024w', '1920w'])
 * @return string srcset attribute değeri
 */
function get_image_srcset($imageUrl, $sizes = ['640w', '1024w', '1920w']) {
    if (empty($imageUrl)) {
        return '';
    }
    
    // Basit yaklaşım: Aynı görseli farklı boyutlarda göster
    // Gerçek uygulamada burada görseli resize edip farklı boyutlarda kaydetmek gerekir
    $srcset = [];
    foreach ($sizes as $size) {
        $srcset[] = $imageUrl . ' ' . $size;
    }
    
    return implode(', ', $srcset);
}

/**
 * WebP format desteği kontrolü ve fallback
 * @param string $imageUrl Orijinal görsel URL'i
 * @param string $alt Alt text
 * @param string $class CSS class'ları
 * @param bool $lazy Lazy loading aktif mi
 * @return string HTML img tag'i
 */
function get_optimized_image($imageUrl, $alt = '', $class = '', $lazy = true) {
    if (empty($imageUrl)) {
        return '';
    }
    
    // WebP versiyonu var mı kontrol et (basit kontrol: .webp uzantısı ekle)
    $webpUrl = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $imageUrl);
    $webpExists = false;
    
    // Dosya sisteminde WebP var mı kontrol et
    if (strpos($webpUrl, '/') === 0 || strpos($webpUrl, 'http') === 0) {
        // Absolute path veya URL
        $filePath = str_replace([site_url(), '/'], [__DIR__ . '/public', ''], $webpUrl);
        if (file_exists($filePath)) {
            $webpExists = true;
        }
    }
    
    $attributes = [];
    if (!empty($alt)) {
        $attributes[] = 'alt="' . esc_attr($alt) . '"';
    }
    if (!empty($class)) {
        $attributes[] = 'class="' . esc_attr($class) . '"';
    }
    if ($lazy) {
        $attributes[] = 'loading="lazy"';
    }
    
    $imgTag = '<img src="' . esc_url($imageUrl) . '"';
    if ($webpExists) {
        $imgTag = '<picture><source srcset="' . esc_url($webpUrl) . '" type="image/webp">' . $imgTag;
    }
    $imgTag .= ' ' . implode(' ', $attributes) . '>';
    if ($webpExists) {
        $imgTag .= '</picture>';
    }
    
    return $imgTag;
}

/**
 * Tarih farkını insanca gösterir (X dakika önce, Y saat önce, vb.)
 * @param string $datetime Tarih string (Y-m-d H:i:s formatında)
 * @return string İnsanca tarih farkı
 */
function time_ago($datetime) {
    if (empty($datetime)) {
        return '';
    }
    
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Az önce';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' dakika önce';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' saat önce';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' gün önce';
    } elseif ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return $weeks . ' hafta önce';
    } elseif ($diff < 31536000) {
        $months = floor($diff / 2592000);
        return $months . ' ay önce';
    } else {
        $years = floor($diff / 31536000);
        return $years . ' yıl önce';
    }
}

/**
 * Site logosu URL'sini getirir
 * @return string Logo URL'i
 */
function get_site_logo() {
    // Önce tema ayarlarından kontrol et
    $logo = null;
    if (class_exists('ThemeLoader')) {
        try {
            $themeLoader = ThemeLoader::getInstance();
            if ($themeLoader && $themeLoader->hasActiveTheme()) {
                $logo = $themeLoader->getLogo();
            }
        } catch (Exception $e) {
            // Hata durumunda sessizce devam et
        }
    }
    
    // Tema ayarlarında yoksa ayarlardan kontrol et
    if (empty($logo)) {
        $logo = get_option('site_logo', '');
    }
    
    // Ayarlarda da yoksa varsayılan logo'yu kullan
    if (empty($logo)) {
        $defaultLogo = site_url('uploads/Logo/codetic-logo.jpg');
        // Dosya var mı kontrol et
        $logoPath = __DIR__ . '/../public/uploads/Logo/codetic-logo.jpg';
        if (file_exists($logoPath)) {
            return $defaultLogo;
        }
    }
    
    return $logo;
}

/**
 * Site favicon URL'sini getirir
 * @return string Favicon URL'i
 */
function get_site_favicon() {
    // Önce tema ayarlarından kontrol et
    $favicon = null;
    if (class_exists('ThemeLoader')) {
        try {
            $themeLoader = ThemeLoader::getInstance();
            if ($themeLoader && $themeLoader->hasActiveTheme()) {
                $favicon = $themeLoader->getFavicon();
            }
        } catch (Exception $e) {
            // Hata durumunda sessizce devam et
        }
    }
    
    // Tema ayarlarında yoksa ayarlardan kontrol et
    if (empty($favicon)) {
        $favicon = get_option('site_favicon', '');
    }
    
    // Favicon URL'ini normalize et
    if (!empty($favicon)) {
        // Eğer zaten tam URL ise (http/https ile başlıyorsa), olduğu gibi döndür
        if (preg_match('/^https?:\/\//', $favicon)) {
            return $favicon;
        }
        
        // Göreli yol ise, tam URL'e dönüştür
        // Eğer / ile başlıyorsa
        if (strpos($favicon, '/') === 0) {
            // /public/ ile başlamıyorsa ekle
            if (strpos($favicon, '/public/') !== 0 && strpos($favicon, '/public') !== 0) {
                $favicon = '/public' . $favicon;
            }
        } else {
            // / ile başlamıyorsa, public/uploads/ ekle (muhtemelen sadece dosya adı veya göreli yol)
            $favicon = 'public/uploads/' . ltrim($favicon, '/');
        }
        
        // Tam URL'e dönüştür (site_url zaten / ekliyor)
        return site_url($favicon);
    }
    
    // Ayarlarda da yoksa varsayılan favicon'u kullan
    $defaultFavicon = site_url('public/uploads/Logo/codetic-favicon.png');
    // Dosya var mı kontrol et
    $faviconPath = __DIR__ . '/../public/uploads/Logo/codetic-favicon.png';
    if (file_exists($faviconPath)) {
        return $defaultFavicon;
    }
    
    return '';
}

// ==================== TRANSLATION HELPER FUNCTIONS ====================

/**
 * Çeviri metnini döndürür (WordPress __() benzeri)
 * @param string $text Çevrilecek metin
 * @param string $domain Text domain (varsayılan: 'default')
 * @return string Çevrilmiş metin
 */
if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        if (empty($text)) {
            return $text;
        }
        
        // Translation modülü aktif mi kontrol et
        if (!class_exists('ModuleLoader')) {
            return $text;
        }
        
        $moduleLoader = ModuleLoader::getInstance();
        if (!$moduleLoader) {
            return $text;
        }
        
        $translationController = $moduleLoader->getModuleController('translation');
        if (!$translationController || !method_exists($translationController, 'translate')) {
            // Fallback: apply_filters kullan (geriye dönük uyumluluk)
            if (function_exists('apply_filters')) {
                return apply_filters('page_title', $text);
            }
            return $text;
        }
        
        return $translationController->translate($text, $domain);
    }
}

/**
 * Çeviri metnini echo eder (WordPress _e() benzeri)
 * @param string $text Çevrilecek metin
 * @param string $domain Text domain (varsayılan: 'default')
 */
if (!function_exists('_e')) {
    function _e($text, $domain = 'default') {
        echo __($text, $domain);
    }
}

/**
 * __() için kısa alias
 * @param string $text Çevrilecek metin
 * @param string $domain Text domain (varsayılan: 'default')
 * @return string Çevrilmiş metin
 */
if (!function_exists('t')) {
    function t($text, $domain = 'default') {
        return __($text, $domain);
    }
}

/**
 * HTML escape ile çeviri döndürür (WordPress esc_html__() benzeri)
 * @param string $text Çevrilecek metin
 * @param string $domain Text domain (varsayılan: 'default')
 * @return string HTML escape edilmiş çevrilmiş metin
 */
if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain = 'default') {
        return esc_html(__($text, $domain));
    }
}

/**
 * Attribute escape ile çeviri döndürür (WordPress esc_attr__() benzeri)
 * @param string $text Çevrilecek metin
 * @param string $domain Text domain (varsayılan: 'default')
 * @return string Attribute escape edilmiş çevrilmiş metin
 */
if (!function_exists('esc_attr__')) {
    function esc_attr__($text, $domain = 'default') {
        return esc_attr(__($text, $domain));
    }
}

/**
 * HTML escape ile çeviri echo eder (WordPress esc_html_e() benzeri)
 * @param string $text Çevrilecek metin
 * @param string $domain Text domain (varsayılan: 'default')
 */
if (!function_exists('esc_html_e')) {
    function esc_html_e($text, $domain = 'default') {
        echo esc_html__($text, $domain);
    }
}

/**
 * Attribute escape ile çeviri echo eder (WordPress esc_attr_e() benzeri)
 * @param string $text Çevrilecek metin
 * @param string $domain Text domain (varsayılan: 'default')
 */
if (!function_exists('esc_attr_e')) {
    function esc_attr_e($text, $domain = 'default') {
        echo esc_attr__($text, $domain);
    }
}

// ==================== THEME RENDERING FUNCTIONS ====================

/**
 * Renders the theme header (WordPress-style compatibility)
 * Includes: HTML doctype, head section, body opening, and header
 * @param array $data Additional data to pass to header
 */
if (!function_exists('get_header')) {
    function get_header($data = []) {
        // Ensure ThemeLoader class is loaded
        if (!class_exists('ThemeLoader')) {
            $themeLoaderFile = __DIR__ . '/../core/ThemeLoader.php';
            if (file_exists($themeLoaderFile)) {
                require_once $themeLoaderFile;
            }
        }
        
        // Get current language
        $currentLang = function_exists('get_current_language') ? get_current_language() : 'tr';
        
        // SEO data
        $seoTitle = get_option('seo_title', '');
        $seoDescription = get_option('seo_description', '');
        $seoAuthor = get_option('seo_author', '');
        
        // Page title
        $pageTitle = $data['title'] ?? ($seoTitle ?: __('Ana Sayfa'));
        
        // Meta description
        $metaDesc = $data['meta_description'] ?? $seoDescription;
        
        // Favicon
        $favicon = '';
        $themeLoader = null;
        $cssVars = '';
        $themeCss = '';
        $primaryColor = '#3b82f6';
        $secondaryColor = '#1e293b';
        
        // Try to get ThemeLoader
        if (class_exists('ThemeLoader')) {
            try {
                $themeLoader = ThemeLoader::getInstance();
                if ($themeLoader && $themeLoader->hasActiveTheme()) {
                    // Ayarları yenile (güncel favicon için) - her seferinde güncel tema ayarlarını almak için
                    if (method_exists($themeLoader, 'refreshSettings')) {
                        $themeLoader->refreshSettings();
                    }
                    
                    // Aktif temanın favicon'unu al - önce tema ayarlarından
                    $favicon = $themeLoader->getFavicon();
                    
                    // Favicon hala boşsa veya null ise, get_site_favicon() kullan (tema kontrolü yapar)
                    // Ancak get_site_favicon() de zaten $themeLoader->getFavicon() çağırıyor, bu yüzden 
                    // burada direkt get_site_favicon() kullanmak yerine, branding ayarlarından kontrol et
                    if (empty($favicon) || $favicon === null) {
                        // Tema ayarlarında yoksa, global site favicon'unu kullan
                        $favicon = get_site_favicon();
                    }
                    
                    $cssVars = $themeLoader->getCssVariablesTag();
                    $themeCss = $themeLoader->getCssUrl();
                    $primaryColor = $themeLoader->getPrimaryColor() ?: $primaryColor;
                    $secondaryColor = $themeLoader->getSecondaryColor() ?: $secondaryColor;
                } else {
                    // Tema yoksa direkt get_site_favicon() kullan
                    $favicon = get_site_favicon();
                }
            } catch (Exception $e) {
                error_log('ThemeLoader error in get_header: ' . $e->getMessage());
                // Hata durumunda fallback
                $favicon = get_site_favicon();
            }
        } else {
            // ThemeLoader class'ı yoksa fallback
            $favicon = get_site_favicon();
        }
        
        // Son fallback - eğer hala boşsa
        if (empty($favicon)) {
            $favicon = get_site_favicon();
        }
        
        // Google Analytics and other tracking codes
        $googleAnalytics = get_option('google_analytics', '');
        $googleTagManager = get_option('google_tag_manager', '');
        $googleAds = get_option('google_ads', '');
        
        // Output HTML structure
        ?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr($currentLang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($pageTitle); ?></title>
    
    <?php if (!empty($metaDesc)): ?>
    <meta name="description" content="<?php echo esc_attr($metaDesc); ?>">
    <?php endif; ?>
    
    <?php if (!empty($seoAuthor)): ?>
    <meta name="author" content="<?php echo esc_attr($seoAuthor); ?>">
    <?php endif; ?>
    
    <!-- Favicon -->
    <?php if (!empty($favicon)): ?>
    <link rel="icon" type="image/png" href="<?php echo esc_url($favicon); ?>">
    <link rel="shortcut icon" href="<?php echo esc_url($favicon); ?>">
    <link rel="apple-touch-icon" href="<?php echo esc_url($favicon); ?>">
    <?php endif; ?>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Theme CSS Variables -->
    <?php if (!empty($cssVars)): ?>
    <?php echo $cssVars; ?>
    <?php endif; ?>
    
    <style>
        :root {
            --color-primary: <?php echo esc_attr($primaryColor); ?>;
            --color-secondary: <?php echo esc_attr($secondaryColor); ?>;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
    </style>
    
    <!-- Theme CSS -->
    <?php if (!empty($themeCss)): ?>
    <link rel="stylesheet" href="<?php echo esc_url($themeCss); ?>">
    <?php endif; ?>
    
    <?php
    // Google Tag Manager - Head
    if (!empty($googleTagManager)): ?>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','<?php echo esc_js($googleTagManager); ?>');</script>
    <!-- End Google Tag Manager -->
    <?php endif; ?>
    
    <?php
    // Google Analytics
    if (!empty($googleAnalytics)): ?>
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_js($googleAnalytics); ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?php echo esc_js($googleAnalytics); ?>');
    </script>
    <?php endif; ?>
</head>
<body>
    <?php 
    // Google Tag Manager - Body
    if (!empty($googleTagManager)): ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr($googleTagManager); ?>"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <?php endif; ?>
    
    <?php
            // Now render the header snippet
            if ($themeLoader) {
                try {
                    echo $themeLoader->renderSnippet('header', $data);
                } catch (Exception $e) {
                    error_log('Header snippet render error: ' . $e->getMessage());
                    // Fallback: Try to include header directly
                    $rootPath = dirname(__DIR__);
                    $headerPath = $rootPath . '/themes/realestate/snippets/header.php';
                    if (file_exists($headerPath)) {
                        extract($data);
                        if (isset($themeLoader)) {
                            $GLOBALS['themeLoader'] = $themeLoader;
                        }
                        include $headerPath;
                    }
                }
            } else {
                // Fallback: Try to include header directly if ThemeLoader not available
                $rootPath = dirname(__DIR__);
                $headerPath = $rootPath . '/themes/realestate/snippets/header.php';
                if (file_exists($headerPath)) {
                    extract($data);
                    include $headerPath;
                }
            }
            ?>
    
    <!-- Main Content -->
    <main id="main">
    <?php
    }
}

/**
 * Renders the theme footer (WordPress-style compatibility)
 * Includes: footer section, body closing, and html closing
 * @param array $data Additional data to pass to footer
 */
if (!function_exists('get_footer')) {
    function get_footer($data = []) {
        // Ensure ThemeLoader class is loaded
        if (!class_exists('ThemeLoader')) {
            $themeLoaderFile = __DIR__ . '/../core/ThemeLoader.php';
            if (file_exists($themeLoaderFile)) {
                require_once $themeLoaderFile;
            }
        }
        
        $themeLoader = null;
        $themeJs = '';
        
        // Try to get ThemeLoader
        if (class_exists('ThemeLoader')) {
            try {
                $themeLoader = ThemeLoader::getInstance();
                if ($themeLoader && $themeLoader->hasActiveTheme()) {
                    $themeJs = $themeLoader->getJsUrl();
                }
            } catch (Exception $e) {
                error_log('ThemeLoader error in get_footer: ' . $e->getMessage());
            }
        }
        
        ?>
    </main>
    <!-- End Main Content -->
    
    <?php
            // Render footer snippet
            if ($themeLoader) {
                try {
                    echo $themeLoader->renderSnippet('footer', $data);
                } catch (Exception $e) {
                    error_log('Footer snippet render error: ' . $e->getMessage());
                    // Fallback: Try to include footer directly
                    $rootPath = dirname(__DIR__);
                    $footerPath = $rootPath . '/themes/realestate/snippets/footer.php';
                    if (file_exists($footerPath)) {
                        extract($data);
                        if (isset($themeLoader)) {
                            $GLOBALS['themeLoader'] = $themeLoader;
                        }
                        include $footerPath;
                    }
                }
            } else {
                // Fallback: Try to include footer directly if ThemeLoader not available
                $rootPath = dirname(__DIR__);
                $footerPath = $rootPath . '/themes/realestate/snippets/footer.php';
                if (file_exists($footerPath)) {
                    extract($data);
                    include $footerPath;
                }
            }
            
            // Theme JavaScript
            if (!empty($themeJs)): ?>
    
    <!-- Theme JS -->
    <script src="<?php echo esc_url($themeJs); ?>"></script>
    <?php endif; ?>
    
</body>
</html>
        <?php
    }
}
