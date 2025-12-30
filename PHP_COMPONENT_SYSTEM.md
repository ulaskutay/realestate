# PHP Component System - Tema Yapısına Uygun

CMS projesi artık PHP tabanlı component sistemi kullanmaktadır. Tema yapısına uygun olarak component-based bir yapı kurulmuştur.

## Yapı

### Klasör Yapısı

```
app/views/
├── layouts/          # Layout dosyaları (default.php)
├── snippets/         # Snippet'ler (header.php, footer.php)
├── components/       # Component'ler (slider.php)
└── *.php            # Sayfa view'ları (home.php)
```

## Kullanım

### Controller'da

```php
class HomeController extends Controller {
    public function index() {
        $sliderModel = new Slider();
        $slider = $sliderModel->getActiveWithItems();
        
        $data = [
            'title' => 'Ana Sayfa',
            'slider' => $slider,
            'current_page' => 'home'
        ];
        
        $this->view('home', $data);
    }
}
```

### View'da Layout Kullanımı

```php
<?php
// Layout ayarla
$renderer = ViewRenderer::getInstance();
$renderer->setLayout('default');

// Content'i yakala
ob_start();
?>

<!-- İçerik buraya -->

<?php
$content = ob_get_clean();

// Styles section (opsiyonel)
ob_start();
?>
<style>
    /* CSS buraya */
</style>
<?php
$styles = ob_get_clean();

// Layout'a geçir
$sections = [
    'content' => $content,
    'styles' => $styles
];
?>
```

### Component Include

```php
<?php
$renderer = ViewRenderer::getInstance();
$renderer->component('slider', ['slider' => $slider]);
?>
```

### Snippet Include

```php
<?php
$renderer = ViewRenderer::getInstance();
$renderer->snippet('header');
?>
```

## Helper Fonksiyonlar

ViewRenderer sınıfı static helper fonksiyonlar sağlar:

- `ViewRenderer::siteUrl($path)` - Site URL'i oluşturur
- `ViewRenderer::adminUrl($path)` - Admin URL'i oluşturur
- `ViewRenderer::assetUrl($path)` - Public asset URL'i oluşturur
- `ViewRenderer::escHtml($string)` - HTML escape
- `ViewRenderer::escAttr($string)` - Attribute escape
- `ViewRenderer::escUrl($url)` - URL escape

### Kullanım Örnekleri

```php
<!-- URL oluşturma -->
<a href="<?php echo ViewRenderer::siteUrl('page'); ?>">Link</a>
<a href="<?php echo ViewRenderer::adminUrl('login'); ?>">Admin</a>
<img src="<?php echo ViewRenderer::assetUrl('images/logo.png'); ?>">

<!-- Güvenlik -->
<h1><?php echo ViewRenderer::escHtml($title); ?></h1>
<img alt="<?php echo ViewRenderer::escAttr($alt); ?>">
<a href="<?php echo ViewRenderer::escUrl($url); ?>">Link</a>
```

## Component Yapısı

### Slider Component Örneği

```php
<?php
// app/views/components/slider.php

if (!isset($slider) || empty($slider['items'])) {
    return;
}
?>

<div id="entry-slider">
    <!-- Slider içeriği -->
</div>
```

### Kullanım

```php
<?php
$renderer = ViewRenderer::getInstance();
$renderer->component('slider', ['slider' => $sliderData]);
?>
```

## Layout Sistemi

### Default Layout

```php
<!DOCTYPE html>
<html>
<head>
    <title><?php echo isset($title) ? ViewRenderer::escHtml($title) : 'CMS'; ?></title>
    <?php if (isset($sections['styles'])) echo $sections['styles']; ?>
</head>
<body>
    <?php $renderer->snippet('header'); ?>
    
    <main>
        <?php echo isset($content) ? $content : (isset($sections['content']) ? $sections['content'] : ''); ?>
    </main>
    
    <?php $renderer->snippet('footer'); ?>
    
    <?php if (isset($sections['scripts'])) echo $sections['scripts']; ?>
</body>
</html>
```

## Özellikler

- ✅ Component-based yapı (tema yapısına uygun)
- ✅ Layout sistemi
- ✅ Snippet sistemi
- ✅ XSS koruması (escape fonksiyonları)
- ✅ URL helper fonksiyonları
- ✅ Temiz ve organize kod yapısı

## Notlar

- Component'ler `$renderer->component()` ile include edilir
- Snippet'ler `$renderer->snippet()` ile include edilir
- Layout'lar `$renderer->setLayout()` ile ayarlanır
- Helper fonksiyonlar static olarak çağrılır
- Tüm output'lar otomatik olarak escape edilmelidir (güvenlik için)

