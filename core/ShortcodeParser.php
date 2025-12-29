<?php
/**
 * Shortcode Parser - Shortcode İşleyici Sistemi
 * 
 * WordPress tarzı shortcode sistemi.
 * İçerik içindeki [shortcode attr="value"]content[/shortcode] yapılarını işler.
 */

class ShortcodeParser {
    private static $instance = null;
    
    /**
     * Kayıtlı shortcode'lar
     * @var array
     */
    private $shortcodes = [];
    
    /**
     * Singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {}
    private function __clone() {}
    
    /**
     * Yeni shortcode ekler
     * 
     * @param string $tag Shortcode adı (örn: 'gallery')
     * @param callable $callback İşleyici fonksiyon
     * @return bool
     */
    public function add($tag, $callback) {
        if (!is_callable($callback)) {
            return false;
        }
        
        $this->shortcodes[$tag] = $callback;
        return true;
    }
    
    /**
     * Shortcode kaldırır
     * 
     * @param string $tag Shortcode adı
     * @return bool
     */
    public function remove($tag) {
        if (isset($this->shortcodes[$tag])) {
            unset($this->shortcodes[$tag]);
            return true;
        }
        return false;
    }
    
    /**
     * Shortcode var mı kontrol eder
     * 
     * @param string $tag Shortcode adı
     * @return bool
     */
    public function exists($tag) {
        return isset($this->shortcodes[$tag]);
    }
    
    /**
     * Tüm kayıtlı shortcode'ları döndürür
     * 
     * @return array
     */
    public function getAll() {
        return array_keys($this->shortcodes);
    }
    
    /**
     * İçerikteki tüm shortcode'ları işler
     * 
     * @param string $content İşlenecek içerik
     * @return string İşlenmiş içerik
     */
    public function parse($content) {
        if (empty($this->shortcodes) || strpos($content, '[') === false) {
            return $content;
        }
        
        // Hook: shortcode işleme öncesi
        if (function_exists('apply_filters')) {
            $content = apply_filters('pre_parse_shortcodes', $content);
        }
        
        // Tüm kayıtlı shortcode'lar için regex pattern oluştur
        $pattern = $this->getShortcodeRegex();
        
        // Shortcode'ları işle
        $content = preg_replace_callback($pattern, [$this, 'processShortcode'], $content);
        
        // Hook: shortcode işleme sonrası
        if (function_exists('apply_filters')) {
            $content = apply_filters('post_parse_shortcodes', $content);
        }
        
        return $content;
    }
    
    /**
     * Tek bir shortcode'u işler (callback)
     * 
     * @param array $matches Regex eşleşmeleri
     * @return string
     */
    private function processShortcode($matches) {
        $tag = $matches[2];
        
        // Shortcode kayıtlı değilse olduğu gibi bırak
        if (!isset($this->shortcodes[$tag])) {
            return $matches[0];
        }
        
        // Nitelikleri parse et
        $atts = $this->parseAttributes($matches[3]);
        
        // İçerik (self-closing değilse)
        $content = isset($matches[5]) ? $matches[5] : null;
        
        // Callback'i çağır
        try {
            $output = call_user_func($this->shortcodes[$tag], $atts, $content, $tag);
            
            // Hook: shortcode çıktısı
            if (function_exists('apply_filters')) {
                $output = apply_filters('shortcode_output', $output, $tag, $atts, $content);
                $output = apply_filters("shortcode_output_{$tag}", $output, $atts, $content);
            }
            
            return $output;
            
        } catch (Exception $e) {
            error_log("Shortcode error [{$tag}]: " . $e->getMessage());
            return '<!-- Shortcode error: ' . htmlspecialchars($tag) . ' -->';
        }
    }
    
    /**
     * Shortcode regex pattern'ını oluşturur
     * 
     * @return string
     */
    private function getShortcodeRegex() {
        $tagnames = array_keys($this->shortcodes);
        $tagregexp = implode('|', array_map('preg_quote', $tagnames));
        
        // WordPress benzeri regex pattern
        return '/\[(\[?)(' . $tagregexp . ')(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)/s';
    }
    
    /**
     * Shortcode niteliklerini parse eder
     * 
     * @param string $text Nitelik string'i
     * @return array
     */
    private function parseAttributes($text) {
        $atts = [];
        
        // Nitelikleri eşleştir
        $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
        
        $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
        
        if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                if (!empty($m[1])) {
                    // name="value"
                    $atts[strtolower($m[1])] = $m[2];
                } elseif (!empty($m[3])) {
                    // name='value'
                    $atts[strtolower($m[3])] = $m[4];
                } elseif (!empty($m[5])) {
                    // name=value
                    $atts[strtolower($m[5])] = $m[6];
                } elseif (isset($m[7]) && strlen($m[7])) {
                    // "value" (isimsiz değer)
                    $atts[] = $m[7];
                } elseif (isset($m[8])) {
                    // value (isimsiz, tırnaksız değer)
                    $atts[] = $m[8];
                }
            }
            
            // Niteliklerdeki HTML entity'leri decode et
            foreach ($atts as $key => $value) {
                if (is_string($value)) {
                    $atts[$key] = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
                }
            }
        }
        
        return $atts;
    }
    
    /**
     * Varsayılan nitelikleri uygular
     * 
     * @param array $defaults Varsayılan nitelikler
     * @param array $atts Gelen nitelikler
     * @return array Birleştirilmiş nitelikler
     */
    public function shortcodeAtts($defaults, $atts) {
        $atts = (array) $atts;
        $out = [];
        
        foreach ($defaults as $name => $default) {
            if (array_key_exists($name, $atts)) {
                $out[$name] = $atts[$name];
            } else {
                $out[$name] = $default;
            }
        }
        
        return $out;
    }
    
    /**
     * İçerikte shortcode var mı kontrol eder
     * 
     * @param string $content İçerik
     * @param string|null $tag Belirli bir shortcode (null = herhangi biri)
     * @return bool
     */
    public function hasShortcode($content, $tag = null) {
        if (strpos($content, '[') === false) {
            return false;
        }
        
        if ($tag === null) {
            // Herhangi bir kayıtlı shortcode var mı
            foreach (array_keys($this->shortcodes) as $shortcode) {
                if (preg_match('/\[' . preg_quote($shortcode) . '[\s\]\/]/', $content)) {
                    return true;
                }
            }
            return false;
        }
        
        // Belirli bir shortcode var mı
        return (bool) preg_match('/\[' . preg_quote($tag) . '[\s\]\/]/', $content);
    }
    
    /**
     * Shortcode'ları içerikten çıkarır (strip)
     * 
     * @param string $content İçerik
     * @return string Shortcode'suz içerik
     */
    public function strip($content) {
        if (empty($this->shortcodes) || strpos($content, '[') === false) {
            return $content;
        }
        
        $pattern = $this->getShortcodeRegex();
        
        return preg_replace_callback($pattern, function($matches) {
            // İç içeriği koru, shortcode'u kaldır
            return isset($matches[5]) ? $matches[5] : '';
        }, $content);
    }
    
    /**
     * Escape edilmiş shortcode'ları unescape eder
     * [[shortcode]] -> [shortcode]
     * 
     * @param string $content İçerik
     * @return string
     */
    public function unescapeShortcodes($content) {
        return preg_replace('/\[\[([^\]]+)\]\]/', '[$1]', $content);
    }
}

// ==================== GLOBAL HELPER FUNCTIONS ====================

/**
 * Shortcode ekler
 * 
 * @param string $tag Shortcode adı
 * @param callable $callback İşleyici fonksiyon
 */
function add_shortcode($tag, $callback) {
    return ShortcodeParser::getInstance()->add($tag, $callback);
}

/**
 * Shortcode kaldırır
 * 
 * @param string $tag Shortcode adı
 */
function remove_shortcode($tag) {
    return ShortcodeParser::getInstance()->remove($tag);
}

/**
 * Shortcode var mı kontrol eder
 * 
 * @param string $tag Shortcode adı
 * @return bool
 */
function shortcode_exists($tag) {
    return ShortcodeParser::getInstance()->exists($tag);
}

/**
 * İçerikteki shortcode'ları işler
 * 
 * @param string $content İçerik
 * @return string İşlenmiş içerik
 */
function do_shortcode($content) {
    return ShortcodeParser::getInstance()->parse($content);
}

/**
 * İçerikte shortcode var mı kontrol eder
 * 
 * @param string $content İçerik
 * @param string|null $tag Belirli bir shortcode
 * @return bool
 */
function has_shortcode($content, $tag = null) {
    return ShortcodeParser::getInstance()->hasShortcode($content, $tag);
}

/**
 * Shortcode'ları içerikten çıkarır
 * 
 * @param string $content İçerik
 * @return string
 */
function strip_shortcodes($content) {
    return ShortcodeParser::getInstance()->strip($content);
}

/**
 * Varsayılan nitelikleri uygular
 * 
 * @param array $defaults Varsayılan nitelikler
 * @param array $atts Gelen nitelikler
 * @return array
 */
function shortcode_atts($defaults, $atts) {
    return ShortcodeParser::getInstance()->shortcodeAtts($defaults, $atts);
}

// ==================== BUILT-IN SHORTCODES ====================

/**
 * Slider shortcode
 * Kullanım: [slider id="1"] veya [slider slug="ana-slider"]
 */
add_shortcode('slider', function($atts) {
    $atts = shortcode_atts([
        'id' => null,
        'slug' => null,
        'class' => ''
    ], $atts);
    
    // Slider component'ini render et
    $slider_file = dirname(__DIR__) . '/app/views/frontend/components/slider.php';
    
    if (!file_exists($slider_file)) {
        return '<!-- Slider component not found -->';
    }
    
    ob_start();
    
    $slider_id = $atts['id'];
    $slider_slug = $atts['slug'];
    $extra_class = $atts['class'];
    
    include $slider_file;
    
    return ob_get_clean();
});

/**
 * Form shortcode
 * Kullanım: [form id="1"] veya [form slug="iletisim"]
 */
add_shortcode('form', function($atts) {
    $atts = shortcode_atts([
        'id' => null,
        'slug' => null,
        'class' => ''
    ], $atts);
    
    // Form component'ini render et
    $form_file = dirname(__DIR__) . '/app/views/frontend/components/form.php';
    
    if (!file_exists($form_file)) {
        return '<!-- Form component not found -->';
    }
    
    ob_start();
    
    $form_id = $atts['id'];
    $form_slug = $atts['slug'];
    $extra_class = $atts['class'];
    
    include $form_file;
    
    return ob_get_clean();
});

/**
 * Menu shortcode
 * Kullanım: [menu slug="main-menu" class="nav-class"]
 */
add_shortcode('menu', function($atts) {
    $atts = shortcode_atts([
        'id' => null,
        'slug' => null,
        'class' => 'nav-menu',
        'depth' => 0
    ], $atts);
    
    // Menu render fonksiyonunu çağır
    if (function_exists('render_menu')) {
        return render_menu($atts['slug'] ?? $atts['id'], [
            'class' => $atts['class'],
            'depth' => (int) $atts['depth']
        ]);
    }
    
    return '<!-- Menu function not found -->';
});

/**
 * Posts shortcode
 * Kullanım: [posts limit="5" category="haberler" order="desc"]
 */
add_shortcode('posts', function($atts) {
    $atts = shortcode_atts([
        'limit' => 5,
        'category' => null,
        'order' => 'desc',
        'template' => 'list',
        'class' => ''
    ], $atts);
    
    // Post model'ini yükle
    require_once dirname(__DIR__) . '/app/models/Post.php';
    $postModel = new Post();
    
    // Yazıları getir
    $posts = $postModel->getPublished((int)$atts['limit'], $atts['category']);
    
    if (empty($posts)) {
        return '<p class="no-posts">Gösterilecek yazı bulunamadı.</p>';
    }
    
    ob_start();
    ?>
    <div class="shortcode-posts <?php echo htmlspecialchars($atts['class']); ?>">
        <?php foreach ($posts as $post): ?>
        <article class="post-item">
            <?php if (!empty($post['featured_image'])): ?>
            <div class="post-thumbnail">
                <a href="<?php echo site_url('blog/' . $post['slug']); ?>">
                    <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                </a>
            </div>
            <?php endif; ?>
            <div class="post-content">
                <h3 class="post-title">
                    <a href="<?php echo site_url('blog/' . $post['slug']); ?>"><?php echo htmlspecialchars($post['title']); ?></a>
                </h3>
                <?php if (!empty($post['excerpt'])): ?>
                <p class="post-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                <?php endif; ?>
                <span class="post-date"><?php echo date('d.m.Y', strtotime($post['created_at'])); ?></span>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
});

/**
 * Button shortcode
 * Kullanım: [button url="/iletisim" class="btn-primary"]İletişim[/button]
 */
add_shortcode('button', function($atts, $content = '') {
    $atts = shortcode_atts([
        'url' => '#',
        'class' => 'btn',
        'target' => '_self',
        'id' => ''
    ], $atts);
    
    $id_attr = $atts['id'] ? ' id="' . htmlspecialchars($atts['id']) . '"' : '';
    
    return sprintf(
        '<a href="%s" class="%s" target="%s"%s>%s</a>',
        htmlspecialchars($atts['url']),
        htmlspecialchars($atts['class']),
        htmlspecialchars($atts['target']),
        $id_attr,
        $content
    );
});

/**
 * Columns shortcode
 * Kullanım: [columns][column]İçerik 1[/column][column]İçerik 2[/column][/columns]
 */
add_shortcode('columns', function($atts, $content = '') {
    $atts = shortcode_atts([
        'class' => '',
        'gap' => '20px'
    ], $atts);
    
    $style = "display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: {$atts['gap']};";
    
    return sprintf(
        '<div class="shortcode-columns %s" style="%s">%s</div>',
        htmlspecialchars($atts['class']),
        $style,
        do_shortcode($content)
    );
});

add_shortcode('column', function($atts, $content = '') {
    $atts = shortcode_atts([
        'class' => '',
        'width' => ''
    ], $atts);
    
    $style = $atts['width'] ? "grid-column: span {$atts['width']};" : '';
    
    return sprintf(
        '<div class="shortcode-column %s" style="%s">%s</div>',
        htmlspecialchars($atts['class']),
        $style,
        $content
    );
});

/**
 * Accordion shortcode
 * Kullanım: [accordion title="Başlık"]İçerik[/accordion]
 */
add_shortcode('accordion', function($atts, $content = '') {
    static $accordion_id = 0;
    $accordion_id++;
    
    $atts = shortcode_atts([
        'title' => 'Başlık',
        'open' => 'false',
        'class' => ''
    ], $atts);
    
    $is_open = $atts['open'] === 'true' || $atts['open'] === '1';
    $open_class = $is_open ? 'open' : '';
    $hidden = $is_open ? '' : 'style="display:none;"';
    
    return sprintf('
        <div class="shortcode-accordion %s %s">
            <div class="accordion-header" onclick="this.parentElement.classList.toggle(\'open\'); this.nextElementSibling.style.display = this.nextElementSibling.style.display === \'none\' ? \'block\' : \'none\';">
                <span class="accordion-title">%s</span>
                <span class="accordion-icon">▼</span>
            </div>
            <div class="accordion-content" %s>%s</div>
        </div>
    ',
        htmlspecialchars($atts['class']),
        $open_class,
        htmlspecialchars($atts['title']),
        $hidden,
        do_shortcode($content)
    );
});

/**
 * Alert/Notice shortcode
 * Kullanım: [alert type="success"]Mesaj[/alert]
 */
add_shortcode('alert', function($atts, $content = '') {
    $atts = shortcode_atts([
        'type' => 'info', // info, success, warning, error
        'dismissible' => 'false',
        'class' => ''
    ], $atts);
    
    $dismiss_btn = '';
    if ($atts['dismissible'] === 'true' || $atts['dismissible'] === '1') {
        $dismiss_btn = '<button class="alert-dismiss" onclick="this.parentElement.remove();">&times;</button>';
    }
    
    return sprintf(
        '<div class="shortcode-alert alert-%s %s">%s%s</div>',
        htmlspecialchars($atts['type']),
        htmlspecialchars($atts['class']),
        $content,
        $dismiss_btn
    );
});

/**
 * Spacer shortcode
 * Kullanım: [spacer height="50px"]
 */
add_shortcode('spacer', function($atts) {
    $atts = shortcode_atts([
        'height' => '30px'
    ], $atts);
    
    return sprintf('<div class="shortcode-spacer" style="height: %s;"></div>', htmlspecialchars($atts['height']));
});

/**
 * Divider shortcode
 * Kullanım: [divider style="dashed" color="#ccc"]
 */
add_shortcode('divider', function($atts) {
    $atts = shortcode_atts([
        'style' => 'solid', // solid, dashed, dotted
        'color' => '#e0e0e0',
        'width' => '100%',
        'margin' => '20px 0'
    ], $atts);
    
    return sprintf(
        '<hr class="shortcode-divider" style="border: none; border-top: 1px %s %s; width: %s; margin: %s;">',
        htmlspecialchars($atts['style']),
        htmlspecialchars($atts['color']),
        htmlspecialchars($atts['width']),
        htmlspecialchars($atts['margin'])
    );
});

