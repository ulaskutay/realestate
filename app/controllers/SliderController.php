<?php
/**
 * Slider Controller
 * Slider yönetimi için controller
 */

class SliderController extends Controller {
    private $sliderModel;
    private $itemModel;
    private $layerModel;
    
    public function __construct() {
        $this->sliderModel = new Slider();
        $this->itemModel = new SliderItem();
        
        // Layer model'ini yükle (eğer varsa)
        $layerModelFile = __DIR__ . '/../models/SliderLayer.php';
        if (file_exists($layerModelFile)) {
            require_once $layerModelFile;
            if (class_exists('SliderLayer')) {
                $this->layerModel = new SliderLayer();
            }
        }
    }
    
    /**
     * Giriş kontrolü
     */
    private function checkAuth() {
        if (!is_user_logged_in()) {
            $this->redirect(admin_url('login'));
        }
    }
    
    /**
     * Slider listesi
     */
    public function index() {
        $this->checkAuth();
        
        // Yetki kontrolü
        if (!current_user_can('sliders.view')) {
            $_SESSION['error_message'] = 'Bu modülde yetkiniz yoktur!';
            $this->redirect(admin_url('dashboard'));
        }
        
        $sliders = $this->sliderModel->getAll();
        
        // Her slider için item sayısını ekle
        foreach ($sliders as &$slider) {
            $items = $this->itemModel->getAllBySliderId($slider['id']);
            $slider['item_count'] = count($items);
        }
        
        $data = [
            'title' => 'Slider Yönetimi',
            'user' => get_logged_in_user(),
            'sliders' => $sliders,
            'message' => $_SESSION['slider_message'] ?? null,
            'messageType' => $_SESSION['slider_message_type'] ?? null
        ];
        
        // Session mesajlarını temizle
        unset($_SESSION['slider_message']);
        unset($_SESSION['slider_message_type']);
        
        $this->view('admin/sliders/index', $data);
    }
    
    /**
     * Yeni slider oluşturma formu
     */
    public function create() {
        $this->checkAuth();
        
        $data = [
            'title' => 'Yeni Slider Oluştur',
            'user' => get_logged_in_user(),
            'slider' => null
        ];
        
        $this->view('admin/sliders/create', $data);
    }
    
    /**
     * Slider kaydetme (POST)
     */
    public function store() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('sliders'));
        }
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'animation_type' => $_POST['animation_type'] ?? 'fade',
            'autoplay' => isset($_POST['autoplay']) ? 1 : 0,
            'autoplay_delay' => (int)($_POST['autoplay_delay'] ?? 5000),
            'navigation' => isset($_POST['navigation']) ? 1 : 0,
            'pagination' => isset($_POST['pagination']) ? 1 : 0,
            'loop' => isset($_POST['loop']) ? 1 : 0,
            'width' => $_POST['width'] ?? '100%',
            'height' => $_POST['height'] ?? '500px',
            'status' => isset($_POST['status']) && $_POST['status'] === 'active' ? 'active' : 'inactive',
            'nav_button_color' => $_POST['nav_button_color'] ?? '#137fec',
            'nav_button_bg_color' => $_POST['nav_button_bg_color'] ?? '#ffffff',
            'nav_button_bg_opacity' => isset($_POST['nav_button_bg_opacity']) ? (float)$_POST['nav_button_bg_opacity'] : 0.90,
            'nav_button_hover_color' => $_POST['nav_button_hover_color'] ?? '#137fec',
            'nav_button_hover_bg_color' => $_POST['nav_button_hover_bg_color'] ?? '#ffffff',
            'nav_button_hover_bg_opacity' => isset($_POST['nav_button_hover_bg_opacity']) ? (float)$_POST['nav_button_hover_bg_opacity'] : 0.90,
            'nav_button_size' => $_POST['nav_button_size'] ?? '50px',
            'nav_button_icon_size' => $_POST['nav_button_icon_size'] ?? '32px',
            'nav_button_position' => $_POST['nav_button_position'] ?? 'inside',
            'nav_button_opacity' => isset($_POST['nav_button_opacity']) ? (float)$_POST['nav_button_opacity'] : 0.90,
            'nav_button_border_radius' => $_POST['nav_button_border_radius'] ?? '50%'
        ];
        
        // Validasyon
        if (empty($data['name'])) {
            $_SESSION['slider_message'] = 'Slider adı zorunludur!';
            $_SESSION['slider_message_type'] = 'error';
            $this->redirect(admin_url('sliders/create'));
        }
        
        // Eğer aktif yapılıyorsa, diğer slider'ları pasifleştir
        if ($data['status'] === 'active') {
            $id = $this->sliderModel->create($data);
            $this->sliderModel->setActive($id);
        } else {
            $id = $this->sliderModel->create($data);
        }
        
        $_SESSION['slider_message'] = 'Slider başarıyla oluşturuldu!';
        $_SESSION['slider_message_type'] = 'success';
        
        $this->redirect(admin_url('sliders/edit/' . $id));
    }
    
    /**
     * Slider düzenleme formu
     */
    public function edit($id) {
        $this->checkAuth();
        
        $slider = $this->sliderModel->findWithItems($id);
        
        if (!$slider) {
            $_SESSION['slider_message'] = 'Slider bulunamadı!';
            $_SESSION['slider_message_type'] = 'error';
            $this->redirect(admin_url('sliders'));
        }
        
        $data = [
            'title' => 'Slider Düzenle',
            'user' => get_logged_in_user(),
            'slider' => $slider,
            'message' => $_SESSION['slider_message'] ?? null,
            'messageType' => $_SESSION['slider_message_type'] ?? null
        ];
        
        // Session mesajlarını temizle
        unset($_SESSION['slider_message']);
        unset($_SESSION['slider_message_type']);
        
        $this->view('admin/sliders/edit', $data);
    }
    
    /**
     * Slider güncelleme (POST)
     */
    public function update($id) {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('sliders'));
        }
        
        $slider = $this->sliderModel->find($id);
        
        if (!$slider) {
            $_SESSION['slider_message'] = 'Slider bulunamadı!';
            $_SESSION['slider_message_type'] = 'error';
            $this->redirect(admin_url('sliders'));
        }
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'animation_type' => $_POST['animation_type'] ?? 'fade',
            'autoplay' => isset($_POST['autoplay']) ? 1 : 0,
            'autoplay_delay' => (int)($_POST['autoplay_delay'] ?? 5000),
            'navigation' => isset($_POST['navigation']) ? 1 : 0,
            'pagination' => isset($_POST['pagination']) ? 1 : 0,
            'loop' => isset($_POST['loop']) ? 1 : 0,
            'width' => $_POST['width'] ?? '100%',
            'height' => $_POST['height'] ?? '500px',
            'status' => isset($_POST['status']) && $_POST['status'] === 'active' ? 'active' : 'inactive',
            'nav_button_color' => $_POST['nav_button_color'] ?? '#137fec',
            'nav_button_bg_color' => $_POST['nav_button_bg_color'] ?? '#ffffff',
            'nav_button_bg_opacity' => isset($_POST['nav_button_bg_opacity']) ? (float)$_POST['nav_button_bg_opacity'] : 0.90,
            'nav_button_hover_color' => $_POST['nav_button_hover_color'] ?? '#137fec',
            'nav_button_hover_bg_color' => $_POST['nav_button_hover_bg_color'] ?? '#ffffff',
            'nav_button_hover_bg_opacity' => isset($_POST['nav_button_hover_bg_opacity']) ? (float)$_POST['nav_button_hover_bg_opacity'] : 0.90,
            'nav_button_size' => $_POST['nav_button_size'] ?? '50px',
            'nav_button_icon_size' => $_POST['nav_button_icon_size'] ?? '32px',
            'nav_button_position' => $_POST['nav_button_position'] ?? 'inside',
            'nav_button_opacity' => isset($_POST['nav_button_opacity']) ? (float)$_POST['nav_button_opacity'] : 0.90,
            'nav_button_border_radius' => $_POST['nav_button_border_radius'] ?? '50%'
        ];
        
        // Validasyon
        if (empty($data['name'])) {
            $_SESSION['slider_message'] = 'Slider adı zorunludur!';
            $_SESSION['slider_message_type'] = 'error';
            $this->redirect(admin_url('sliders/edit/' . $id));
        }
        
        // Eğer aktif yapılıyorsa, diğer slider'ları pasifleştir
        if ($data['status'] === 'active' && $slider['status'] !== 'active') {
            $this->sliderModel->setActive($id);
        } else if ($data['status'] !== 'active' && $slider['status'] === 'active') {
            // Pasifleştiriyorsak sadece güncelle
            $this->sliderModel->update($id, $data);
        } else {
            $this->sliderModel->update($id, $data);
        }
        
        $_SESSION['slider_message'] = 'Slider başarıyla güncellendi!';
        $_SESSION['slider_message_type'] = 'success';
        
        $this->redirect(admin_url('sliders/edit/' . $id));
    }
    
    /**
     * Slider silme
     */
    public function delete($id) {
        $this->checkAuth();
        
        $slider = $this->sliderModel->find($id);
        
        if (!$slider) {
            $_SESSION['slider_message'] = 'Slider bulunamadı!';
            $_SESSION['slider_message_type'] = 'error';
        } else {
            $this->sliderModel->deleteSlider($id);
            $_SESSION['slider_message'] = 'Slider başarıyla silindi!';
            $_SESSION['slider_message_type'] = 'success';
        }
        
        $this->redirect(admin_url('sliders'));
    }
    
    /**
     * Slider'ı aktif/pasif yapma
     */
    public function toggleStatus($id) {
        $this->checkAuth();
        
        $slider = $this->sliderModel->find($id);
        
        if (!$slider) {
            $_SESSION['slider_message'] = 'Slider bulunamadı!';
            $_SESSION['slider_message_type'] = 'error';
        } else {
            if ($slider['status'] === 'active') {
                $this->sliderModel->setInactive($id);
                $_SESSION['slider_message'] = 'Slider pasifleştirildi!';
            } else {
                $this->sliderModel->setActive($id);
                $_SESSION['slider_message'] = 'Slider aktifleştirildi!';
            }
            $_SESSION['slider_message_type'] = 'success';
        }
        
        $this->redirect(admin_url('sliders'));
    }
    
    /**
     * Slider item ekleme (AJAX)
     */
    public function addItem() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Geçersiz istek'], 400);
        }
        
        $sliderId = (int)($_POST['slider_id'] ?? 0);
        
        if (!$sliderId || !$this->sliderModel->find($sliderId)) {
            $this->json(['success' => false, 'message' => 'Slider bulunamadı'], 404);
        }
        
        $data = [
            'slider_id' => $sliderId,
            'type' => $_POST['type'] ?? 'image',
            'media_url' => $_POST['media_url'] ?? '',
            'title' => $_POST['title'] ?? '',
            'subtitle' => $_POST['subtitle'] ?? '',
            'description' => $_POST['description'] ?? '',
            'button_text' => $_POST['button_text'] ?? '',
            'button_link' => $_POST['button_link'] ?? '',
            'button_target' => $_POST['button_target'] ?? '_self',
            'overlay_opacity' => isset($_POST['overlay_opacity']) ? (float)$_POST['overlay_opacity'] : 0.0,
            'text_position' => $_POST['text_position'] ?? 'center',
            'status' => isset($_POST['status']) && $_POST['status'] === 'active' ? 'active' : 'inactive'
        ];
        
        $itemId = $this->itemModel->createItem($data);
        
        $item = $this->itemModel->find($itemId);
        
        $this->json([
            'success' => true,
            'message' => 'Item başarıyla eklendi',
            'item' => $item
        ]);
    }
    
    /**
     * Slider item güncelleme (AJAX)
     */
    public function updateItem($itemId) {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Geçersiz istek'], 400);
        }
        
        $item = $this->itemModel->find($itemId);
        
        if (!$item) {
            $this->json(['success' => false, 'message' => 'Item bulunamadı'], 404);
        }
        
        $data = [
            'type' => $_POST['type'] ?? $item['type'],
            'media_url' => $_POST['media_url'] ?? $item['media_url'],
            'title' => $_POST['title'] ?? $item['title'],
            'subtitle' => $_POST['subtitle'] ?? $item['subtitle'],
            'description' => $_POST['description'] ?? $item['description'],
            'button_text' => $_POST['button_text'] ?? $item['button_text'],
            'button_link' => $_POST['button_link'] ?? $item['button_link'],
            'button_target' => $_POST['button_target'] ?? $item['button_target'],
            'overlay_opacity' => isset($_POST['overlay_opacity']) ? (float)$_POST['overlay_opacity'] : $item['overlay_opacity'],
            'text_position' => $_POST['text_position'] ?? $item['text_position'],
            'order' => isset($_POST['order']) ? (int)$_POST['order'] : $item['order'],
            'status' => isset($_POST['status']) && $_POST['status'] === 'active' ? 'active' : 'inactive'
        ];
        
        $this->itemModel->update($itemId, $data);
        
        $updatedItem = $this->itemModel->find($itemId);
        
        $this->json([
            'success' => true,
            'message' => 'Item başarıyla güncellendi',
            'item' => $updatedItem
        ]);
    }
    
    /**
     * Slider item silme (AJAX)
     */
    public function deleteItem($itemId) {
        $this->checkAuth();
        
        $item = $this->itemModel->find($itemId);
        
        if (!$item) {
            $this->json(['success' => false, 'message' => 'Item bulunamadı'], 404);
        }
        
        $this->itemModel->delete($itemId);
        
        $this->json([
            'success' => true,
            'message' => 'Item başarıyla silindi'
        ]);
    }
    
    /**
     * Item sıralaması güncelleme (AJAX)
     */
    public function updateItemOrder() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Geçersiz istek'], 400);
        }
        
        $items = json_decode($_POST['items'] ?? '[]', true);
        
        if (empty($items) || !is_array($items)) {
            $this->json(['success' => false, 'message' => 'Geçersiz item listesi'], 400);
        }
        
        $this->itemModel->updateOrder($items);
        
        $this->json([
            'success' => true,
            'message' => 'Sıralama başarıyla güncellendi'
        ]);
    }
    
    /**
     * Slider item düzenleme sayfası (Gelişmiş editor)
     */
    public function editItem($itemId) {
        $this->checkAuth();
        
        $item = $this->itemModel->find($itemId);
        
        if (!$item) {
            $_SESSION['slider_message'] = 'Item bulunamadı!';
            $_SESSION['slider_message_type'] = 'error';
            $this->redirect(admin_url('sliders'));
        }
        
        $slider = $this->sliderModel->find($item['slider_id']);
        
        if (!$slider) {
            $_SESSION['slider_message'] = 'Slider bulunamadı!';
            $_SESSION['slider_message_type'] = 'error';
            $this->redirect(admin_url('sliders'));
        }
        
        // Layer'ları getir
        $layers = [];
        if (class_exists('SliderLayer')) {
            if (!$this->layerModel) {
                $this->layerModel = new SliderLayer();
            }
            $layers = $this->layerModel->getAllByItemId($itemId);
        }
        
        $data = [
            'title' => 'Slide Düzenle',
            'user' => get_logged_in_user(),
            'slider' => $slider,
            'item' => $item,
            'layers' => $layers
        ];
        
        $this->view('admin/sliders/edit-item', $data);
    }
    
    /**
     * Layer ekleme (AJAX)
     */
    public function addLayer() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->layerModel) {
            $this->json(['success' => false, 'message' => 'Geçersiz istek'], 400);
        }
        
        $itemId = (int)($_POST['slider_item_id'] ?? 0);
        
        if (!$itemId || !$this->itemModel->find($itemId)) {
            $this->json(['success' => false, 'message' => 'Item bulunamadı'], 404);
        }
        
        $data = [
            'slider_item_id' => $itemId,
            'type' => $_POST['type'] ?? 'text',
            'content' => $_POST['content'] ?? '',
            'position_x' => $_POST['position_x'] ?? '50%',
            'position_y' => $_POST['position_y'] ?? '50%',
            'width' => $_POST['width'] ?? 'auto',
            'height' => $_POST['height'] ?? 'auto',
            'z_index' => isset($_POST['z_index']) ? (int)$_POST['z_index'] : null,
            'opacity' => isset($_POST['opacity']) ? (float)$_POST['opacity'] : 1.0,
            'background_color' => $_POST['background_color'] ?? null,
            'color' => $_POST['color'] ?? null,
            'font_size' => $_POST['font_size'] ?? null,
            'font_weight' => $_POST['font_weight'] ?? null,
            'text_align' => $_POST['text_align'] ?? 'left',
            'border_radius' => $_POST['border_radius'] ?? '0',
            'box_shadow' => $_POST['box_shadow'] ?? null,
            'link_url' => $_POST['link_url'] ?? null,
            'link_target' => $_POST['link_target'] ?? '_self',
            'visibility' => isset($_POST['visibility']) && $_POST['visibility'] === '1' ? 1 : 1
        ];
        
        // Animasyon verileri
        if (isset($_POST['animation_in']) && is_array($_POST['animation_in'])) {
            $data['animation_in'] = $_POST['animation_in'];
        }
        if (isset($_POST['animation_out']) && is_array($_POST['animation_out'])) {
            $data['animation_out'] = $_POST['animation_out'];
        }
        if (isset($_POST['transform']) && is_array($_POST['transform'])) {
            $data['transform'] = $_POST['transform'];
        }
        
        $layerId = $this->layerModel->createLayer($data);
        
        $layer = $this->layerModel->findDecoded($layerId);
        
        $this->json([
            'success' => true,
            'message' => 'Layer başarıyla eklendi',
            'layer' => $layer
        ]);
    }
    
    /**
     * Layer güncelleme (AJAX)
     */
    public function updateLayer($layerId) {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->layerModel) {
            $this->json(['success' => false, 'message' => 'Geçersiz istek'], 400);
        }
        
        $layer = $this->layerModel->find($layerId);
        
        if (!$layer) {
            $this->json(['success' => false, 'message' => 'Layer bulunamadı'], 404);
        }
        
        $data = [];
        
        // Sadece gönderilen alanları güncelle
        $fields = [
            'type', 'content', 'position_x', 'position_y', 'width', 'height',
            'z_index', 'opacity', 'background_color', 'color', 'font_size',
            'font_weight', 'text_align', 'border_radius', 'box_shadow',
            'link_url', 'link_target', 'visibility', 'order'
        ];
        
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                if (in_array($field, ['z_index', 'order', 'visibility'])) {
                    $data[$field] = (int)$_POST[$field];
                } else if ($field === 'opacity') {
                    $data[$field] = (float)$_POST[$field];
                } else {
                    $data[$field] = $_POST[$field];
                }
            }
        }
        
        // Animasyon verileri
        if (isset($_POST['animation_in']) && is_array($_POST['animation_in'])) {
            $data['animation_in'] = $_POST['animation_in'];
        }
        if (isset($_POST['animation_out']) && is_array($_POST['animation_out'])) {
            $data['animation_out'] = $_POST['animation_out'];
        }
        if (isset($_POST['transform']) && is_array($_POST['transform'])) {
            $data['transform'] = $_POST['transform'];
        }
        if (isset($_POST['hover_animation']) && is_array($_POST['hover_animation'])) {
            $data['hover_animation'] = $_POST['hover_animation'];
        }
        
        $this->layerModel->updateLayer($layerId, $data);
        
        $updatedLayer = $this->layerModel->findDecoded($layerId);
        
        $this->json([
            'success' => true,
            'message' => 'Layer başarıyla güncellendi',
            'layer' => $updatedLayer
        ]);
    }
    
    /**
     * Layer silme (AJAX)
     */
    public function deleteLayer($layerId) {
        $this->checkAuth();
        
        if (!$this->layerModel) {
            $this->json(['success' => false, 'message' => 'Layer sistemi aktif değil'], 400);
        }
        
        $layer = $this->layerModel->find($layerId);
        
        if (!$layer) {
            $this->json(['success' => false, 'message' => 'Layer bulunamadı'], 404);
        }
        
        $this->layerModel->delete($layerId);
        
        $this->json([
            'success' => true,
            'message' => 'Layer başarıyla silindi'
        ]);
    }
    
    /**
     * Layer sıralaması güncelleme (AJAX)
     */
    public function updateLayerOrder() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->layerModel) {
            $this->json(['success' => false, 'message' => 'Geçersiz istek'], 400);
        }
        
        $layers = json_decode($_POST['layers'] ?? '[]', true);
        
        if (empty($layers) || !is_array($layers)) {
            $this->json(['success' => false, 'message' => 'Geçersiz layer listesi'], 400);
        }
        
        $this->layerModel->updateOrder($layers);
        
        $this->json([
            'success' => true,
            'message' => 'Layer sıralaması başarıyla güncellendi'
        ]);
    }
    
    /**
     * Layer verisini getir (AJAX - editor için)
     */
    public function getLayerData($layerId) {
        $this->checkAuth();
        
        if (!$this->layerModel) {
            $this->json(['success' => false, 'message' => 'Layer sistemi aktif değil'], 400);
        }
        
        $layer = $this->layerModel->findDecoded($layerId);
        
        if (!$layer) {
            $this->json(['success' => false, 'message' => 'Layer bulunamadı'], 404);
        }
        
        $this->json([
            'success' => true,
            'layer' => $layer
        ]);
    }
    
    /**
     * Slider önizleme sayfası (Tam sayfa)
     */
    public function preview($sliderId) {
        $this->checkAuth();
        
        $slider = $this->sliderModel->findWithItems($sliderId);
        
        if (!$slider) {
            die('Slider bulunamadı');
        }
        
        // Her item için layer'ları getir
        if ($this->layerModel && !empty($slider['items'])) {
            foreach ($slider['items'] as &$item) {
                $item['layers'] = $this->layerModel->getAllByItemId($item['id']);
            }
        }
        
        $data = [
            'slider' => $slider
        ];
        
        // Preview view'ını yükle
        $viewFile = __DIR__ . '/../views/admin/sliders/preview.php';
        if (file_exists($viewFile)) {
            extract($data);
            require $viewFile;
        } else {
            // Inline preview
            $this->renderSliderPreview($slider);
        }
        exit;
    }
    
    /**
     * Slider preview render
     */
    private function renderSliderPreview($slider) {
        ?>
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo esc_html($slider['name']); ?> - Önizleme</title>
            <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="<?php echo site_url('frontend/css/slider.css'); ?>">
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background: #000;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .preview-wrapper {
                    width: 100%;
                    max-width: <?php echo esc_attr($slider['width'] ?? '100%'); ?>;
                    margin: 0 auto;
                }
                .cms-slider {
                    position: relative;
                    width: 100%;
                    height: <?php echo esc_attr($slider['height'] ?? '600px'); ?>;
                    overflow: hidden;
                }
                .cms-slide {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    opacity: 0;
                    transition: opacity 0.6s ease;
                }
                .cms-slide.active {
                    opacity: 1;
                    z-index: 1;
                }
                .slide-media {
                    width: 100%;
                    height: 100%;
                }
                .slide-media img,
                .slide-media video {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }
                .slide-overlay {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: #000;
                    z-index: 2;
                }
                .slide-content {
                    position: absolute;
                    z-index: 5;
                    padding: 2rem;
                    max-width: 600px;
                }
                .slide-content.center {
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    text-align: center;
                }
                .slide-content.left {
                    top: 50%;
                    left: 5%;
                    transform: translateY(-50%);
                }
                .slide-content.right {
                    top: 50%;
                    right: 5%;
                    transform: translateY(-50%);
                    text-align: right;
                }
                .slide-content.top {
                    top: 10%;
                    left: 50%;
                    transform: translateX(-50%);
                    text-align: center;
                }
                .slide-content.bottom {
                    bottom: 10%;
                    left: 50%;
                    transform: translateX(-50%);
                    text-align: center;
                }
                .slide-title {
                    font-size: 3rem;
                    font-weight: 700;
                    color: #fff;
                    margin-bottom: 1rem;
                    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
                }
                .slide-subtitle {
                    font-size: 1.25rem;
                    color: rgba(255, 255, 255, 0.9);
                    margin-bottom: 1.5rem;
                    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
                }
                .slide-button {
                    display: inline-block;
                    padding: 0.75rem 2rem;
                    background: #137fec;
                    color: #fff;
                    text-decoration: none;
                    border-radius: 0.5rem;
                    font-weight: 500;
                    transition: all 0.3s ease;
                }
                .slide-button:hover {
                    background: #0d6edb;
                    transform: translateY(-2px);
                }
                <?php
                // Navigation button styles
                $navBgColor = $slider['nav_button_bg_color'] ?? '#ffffff';
                $navBgOpacity = $slider['nav_button_bg_opacity'] ?? 0.9;
                $hex = str_replace('#', '', $navBgColor);
                if (strlen($hex) == 3) {
                    $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
                }
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
                $navBg = "rgba($r, $g, $b, $navBgOpacity)";
                
                $navHoverBgColor = $slider['nav_button_hover_bg_color'] ?? '#ffffff';
                $navHoverBgOpacity = $slider['nav_button_hover_bg_opacity'] ?? 0.9;
                $hex2 = str_replace('#', '', $navHoverBgColor);
                if (strlen($hex2) == 3) {
                    $hex2 = $hex2[0].$hex2[0].$hex2[1].$hex2[1].$hex2[2].$hex2[2];
                }
                $r2 = hexdec(substr($hex2, 0, 2));
                $g2 = hexdec(substr($hex2, 2, 2));
                $b2 = hexdec(substr($hex2, 4, 2));
                $navHoverBg = "rgba($r2, $g2, $b2, $navHoverBgOpacity)";
                ?>
                .slider-nav {
                    position: absolute;
                    top: 50%;
                    transform: translateY(-50%);
                    z-index: 10;
                    width: <?php echo esc_attr($slider['nav_button_size'] ?? '50px'); ?>;
                    height: <?php echo esc_attr($slider['nav_button_size'] ?? '50px'); ?>;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: <?php echo esc_attr($slider['nav_button_border_radius'] ?? '50%'); ?>;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    border: none;
                    background: <?php echo $navBg; ?>;
                    opacity: <?php echo esc_attr($slider['nav_button_opacity'] ?? 0.9); ?>;
                }
                .slider-nav:hover {
                    background: <?php echo $navHoverBg; ?>;
                    opacity: 1;
                }
                .slider-nav .material-symbols-outlined {
                    font-size: <?php echo esc_attr($slider['nav_button_icon_size'] ?? '32px'); ?>;
                    color: <?php echo esc_attr($slider['nav_button_color'] ?? '#137fec'); ?>;
                }
                .slider-nav:hover .material-symbols-outlined {
                    color: <?php echo esc_attr($slider['nav_button_hover_color'] ?? '#137fec'); ?>;
                }
                .slider-prev {
                    left: <?php echo ($slider['nav_button_position'] ?? 'inside') === 'outside' ? '-60px' : '20px'; ?>;
                }
                .slider-next {
                    right: <?php echo ($slider['nav_button_position'] ?? 'inside') === 'outside' ? '-60px' : '20px'; ?>;
                }
                .slider-pagination {
                    position: absolute;
                    bottom: 20px;
                    left: 50%;
                    transform: translateX(-50%);
                    z-index: 10;
                    display: flex;
                    gap: 8px;
                }
                .slider-dot {
                    width: 10px;
                    height: 10px;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.5);
                    cursor: pointer;
                    transition: all 0.3s ease;
                    border: none;
                }
                .slider-dot.active {
                    background: #fff;
                    width: 24px;
                    border-radius: 5px;
                }
                .slider-dot:hover {
                    background: rgba(255, 255, 255, 0.8);
                }
                @media (max-width: 768px) {
                    .slide-title {
                        font-size: 1.75rem;
                    }
                    .slide-subtitle {
                        font-size: 1rem;
                    }
                    .slider-nav {
                        width: 40px;
                        height: 40px;
                    }
                    .slider-nav .material-symbols-outlined {
                        font-size: 24px;
                    }
                }
            </style>
        </head>
        <body>
            <div class="preview-wrapper">
                <div class="cms-slider" 
                     data-autoplay="<?php echo $slider['autoplay'] ? 'true' : 'false'; ?>"
                     data-autoplay-delay="<?php echo esc_attr($slider['autoplay_delay'] ?? 5000); ?>"
                     data-loop="<?php echo ($slider['loop'] ?? 1) ? 'true' : 'false'; ?>">
                    
                    <?php if (!empty($slider['items'])): ?>
                        <?php foreach ($slider['items'] as $index => $item): ?>
                            <div class="cms-slide<?php echo $index === 0 ? ' active' : ''; ?>" data-index="<?php echo $index; ?>">
                                <?php if ($item['type'] === 'video' && !empty($item['media_url'])): ?>
                                    <div class="slide-media">
                                        <video autoplay muted loop playsinline>
                                            <source src="<?php echo esc_url($item['media_url']); ?>" type="video/mp4">
                                        </video>
                                    </div>
                                <?php elseif (!empty($item['media_url'])): ?>
                                    <div class="slide-media">
                                        <img src="<?php echo esc_url($item['media_url']); ?>" alt="<?php echo esc_attr($item['title'] ?? ''); ?>">
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($item['overlay_opacity']) && floatval($item['overlay_opacity']) > 0): ?>
                                    <div class="slide-overlay" style="opacity: <?php echo esc_attr($item['overlay_opacity']); ?>"></div>
                                <?php endif; ?>
                                
                                <?php if (!empty($item['title']) || !empty($item['subtitle']) || !empty($item['button_text'])): ?>
                                    <div class="slide-content <?php echo esc_attr($item['text_position'] ?? 'center'); ?>">
                                        <?php if (!empty($item['title'])): ?>
                                            <h2 class="slide-title"><?php echo esc_html($item['title']); ?></h2>
                                        <?php endif; ?>
                                        <?php if (!empty($item['subtitle'])): ?>
                                            <p class="slide-subtitle"><?php echo esc_html($item['subtitle']); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($item['button_text'])): ?>
                                            <a href="<?php echo esc_url($item['button_link'] ?? '#'); ?>" class="slide-button" target="<?php echo esc_attr($item['button_target'] ?? '_self'); ?>">
                                                <?php echo esc_html($item['button_text']); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (($slider['navigation'] ?? 0) && count($slider['items']) > 1): ?>
                            <button type="button" class="slider-nav slider-prev" onclick="prevSlide()">
                                <span class="material-symbols-outlined">chevron_left</span>
                            </button>
                            <button type="button" class="slider-nav slider-next" onclick="nextSlide()">
                                <span class="material-symbols-outlined">chevron_right</span>
                            </button>
                        <?php endif; ?>
                        
                        <?php if (($slider['pagination'] ?? 0) && count($slider['items']) > 1): ?>
                            <div class="slider-pagination">
                                <?php foreach ($slider['items'] as $index => $item): ?>
                                    <button type="button" class="slider-dot<?php echo $index === 0 ? ' active' : ''; ?>" onclick="goToSlide(<?php echo $index; ?>)"></button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #fff;">
                            <p>Bu slider'da henüz içerik bulunmuyor.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <script>
                const sliderEl = document.querySelector('.cms-slider');
                const slides = document.querySelectorAll('.cms-slide');
                const dots = document.querySelectorAll('.slider-dot');
                const totalSlides = slides.length;
                let currentIndex = 0;
                let autoplayInterval = null;
                
                const config = {
                    autoplay: sliderEl.dataset.autoplay === 'true',
                    delay: parseInt(sliderEl.dataset.autoplayDelay) || 5000,
                    loop: sliderEl.dataset.loop === 'true'
                };
                
                function updateSlide() {
                    slides.forEach((slide, i) => {
                        slide.classList.toggle('active', i === currentIndex);
                    });
                    dots.forEach((dot, i) => {
                        dot.classList.toggle('active', i === currentIndex);
                    });
                }
                
                function nextSlide() {
                    if (config.loop) {
                        currentIndex = (currentIndex + 1) % totalSlides;
                    } else {
                        currentIndex = Math.min(totalSlides - 1, currentIndex + 1);
                    }
                    updateSlide();
                    resetAutoplay();
                }
                
                function prevSlide() {
                    if (config.loop) {
                        currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
                    } else {
                        currentIndex = Math.max(0, currentIndex - 1);
                    }
                    updateSlide();
                    resetAutoplay();
                }
                
                function goToSlide(index) {
                    currentIndex = index;
                    updateSlide();
                    resetAutoplay();
                }
                
                function startAutoplay() {
                    if (config.autoplay && totalSlides > 1) {
                        autoplayInterval = setInterval(nextSlide, config.delay);
                    }
                }
                
                function resetAutoplay() {
                    if (autoplayInterval) clearInterval(autoplayInterval);
                    startAutoplay();
                }
                
                // Touch support
                let touchStartX = 0;
                sliderEl.addEventListener('touchstart', (e) => {
                    touchStartX = e.changedTouches[0].screenX;
                }, { passive: true });
                
                sliderEl.addEventListener('touchend', (e) => {
                    const diff = touchStartX - e.changedTouches[0].screenX;
                    if (Math.abs(diff) > 50) {
                        diff > 0 ? nextSlide() : prevSlide();
                    }
                }, { passive: true });
                
                // Keyboard support
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowRight') nextSlide();
                    if (e.key === 'ArrowLeft') prevSlide();
                });
                
                // Start
                startAutoplay();
            </script>
        </body>
        </html>
        <?php
    }
    
    /**
     * Slide önizleme sayfası
     */
    public function previewItem($itemId) {
        $this->checkAuth();
        
        $item = $this->itemModel->find($itemId);
        
        if (!$item) {
            die('Item bulunamadı');
        }
        
        $slider = $this->sliderModel->find($item['slider_id']);
        
        if (!$slider) {
            die('Slider bulunamadı');
        }
        
        // Layer'ları getir
        $layers = [];
        if ($this->layerModel) {
            $layers = $this->layerModel->getByItemId($itemId);
        }
        
        $item['layers'] = $layers;
        
        // Preview view'ını yükle
        $viewFile = __DIR__ . '/../views/admin/sliders/preview-item.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            // Basit preview
            $this->renderPreview($item, $slider);
        }
    }
    
    /**
     * Basit preview render
     */
    private function renderPreview($item, $slider) {
        ?>
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Slide Önizleme</title>
            <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="<?php echo site_url('css/slider.css'); ?>">
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    overflow: hidden;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                }
                .preview-container {
                    width: 100vw;
                    height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: #000;
                }
                .preview-slide {
                    width: 100%;
                    height: 100%;
                    position: relative;
                    overflow: hidden;
                }
            </style>
        </head>
        <body>
            <div class="preview-container">
                <div class="preview-slide" style="height: <?php echo esc_attr($slider['height']); ?>;">
                    <?php
                    // Background
                    if ($item['type'] === 'image' && !empty($item['media_url'])): ?>
                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: url('<?php echo esc_url($item['media_url']); ?>'); background-size: cover; background-position: center;"></div>
                    <?php elseif ($item['type'] === 'video' && !empty($item['media_url'])): ?>
                        <video autoplay muted loop playsinline style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
                            <source src="<?php echo esc_url($item['media_url']); ?>" type="video/mp4">
                        </video>
                    <?php endif; ?>
                    
                    <?php if (!empty($item['overlay_opacity']) && $item['overlay_opacity'] > 0): ?>
                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: #000; opacity: <?php echo esc_attr($item['overlay_opacity']); ?>;"></div>
                    <?php endif; ?>
                    
                    <?php
                    // Layers
                    if (!empty($item['layers'])) {
                        foreach ($item['layers'] as $layer) {
                            echo '<div style="position: absolute; left: ' . esc_attr($layer['position_x']) . '; top: ' . esc_attr($layer['position_y']) . '; width: ' . esc_attr($layer['width']) . '; height: ' . esc_attr($layer['height']) . '; z-index: ' . $layer['z_index'] . '; opacity: ' . $layer['opacity'] . ';';
                            if (!empty($layer['color'])) echo 'color: ' . esc_attr($layer['color']) . ';';
                            if (!empty($layer['background_color'])) echo 'background-color: ' . esc_attr($layer['background_color']) . ';';
                            if (!empty($layer['font_size'])) echo 'font-size: ' . esc_attr($layer['font_size']) . ';';
                            if (!empty($layer['text_align'])) echo 'text-align: ' . esc_attr($layer['text_align']) . ';';
                            echo '">';
                            
                            if ($layer['type'] === 'text') {
                                echo nl2br(esc_html($layer['content'] ?? ''));
                            } elseif ($layer['type'] === 'image' && !empty($layer['content'])) {
                                echo '<img src="' . esc_url($layer['content']) . '" style="width: 100%; height: 100%; object-fit: contain;">';
                            } elseif ($layer['type'] === 'button') {
                                if (!empty($layer['link_url'])) {
                                    echo '<a href="' . esc_url($layer['link_url']) . '" target="' . esc_attr($layer['link_target'] ?? '_self') . '" style="display: inline-block; padding: 0.75rem 2rem; background: ' . esc_attr($layer['background_color'] ?? '#137fec') . '; color: ' . esc_attr($layer['color'] ?? '#fff') . '; text-decoration: none; border-radius: ' . esc_attr($layer['border_radius'] ?? '0.5rem') . ';">' . esc_html($layer['content'] ?? 'Buton') . '</a>';
                                } else {
                                    echo '<button style="padding: 0.75rem 2rem; background: ' . esc_attr($layer['background_color'] ?? '#137fec') . '; color: ' . esc_attr($layer['color'] ?? '#fff') . '; border: none; border-radius: ' . esc_attr($layer['border_radius'] ?? '0.5rem') . '; cursor: pointer;">' . esc_html($layer['content'] ?? 'Buton') . '</button>';
                                }
                            }
                            
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
    
    /**
     * Dosya yöneticisi - Dosya listesi (AJAX)
     */
    public function fileManagerList() {
        try {
            $this->checkAuth();
            
            $uploadDir = __DIR__ . '/../../public/uploads/';
            $uploadUrl = site_url('uploads/');
            
            // Eğer slider-media klasörü yoksa oluştur
            $sliderMediaDir = $uploadDir . 'slider-media/';
            if (!file_exists($sliderMediaDir)) {
                mkdir($sliderMediaDir, 0755, true);
            }
            
            $files = [];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'mp4', 'webm', 'mov'];
            
            // Ana uploads klasörünü tara
            if (is_dir($uploadDir)) {
                $items = @scandir($uploadDir);
                if ($items !== false) {
                    foreach ($items as $item) {
                        if ($item === '.' || $item === '..' || $item === '.htaccess') continue;
                        
                        $itemPath = $uploadDir . $item;
                        if (is_file($itemPath)) {
                            $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
                            if (in_array($ext, $allowedExtensions)) {
                                try {
                                    $mimeType = '';
                                    if (function_exists('mime_content_type')) {
                                        $mimeType = @mime_content_type($itemPath);
                                    } else if (function_exists('finfo_file')) {
                                        $finfo = @finfo_open(FILEINFO_MIME_TYPE);
                                        if ($finfo) {
                                            $mimeType = @finfo_file($finfo, $itemPath);
                                            @finfo_close($finfo);
                                        }
                                    }
                                    
                                    $fileType = (strpos($mimeType, 'image/') === 0 || in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) ? 'image' : 'video';
                                    
                                    $fileSize = @filesize($itemPath);
                                    $fileModified = @filemtime($itemPath);
                                    
                                    if ($fileSize !== false && $fileModified !== false) {
                                        $files[] = [
                                            'name' => $item,
                                            'url' => $uploadUrl . $item,
                                            'size' => $fileSize,
                                            'type' => $fileType,
                                            'modified' => $fileModified
                                        ];
                                    }
                                } catch (Exception $e) {
                                    // Dosya okuma hatası, atla
                                    continue;
                                }
                            }
                        }
                    }
                }
            }
            
            // slider-media klasörünü de tara
            if (is_dir($sliderMediaDir)) {
                $items = @scandir($sliderMediaDir);
                if ($items !== false) {
                    foreach ($items as $item) {
                        if ($item === '.' || $item === '..' || $item === '.htaccess') continue;
                        
                        $itemPath = $sliderMediaDir . $item;
                        if (is_file($itemPath)) {
                            $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
                            if (in_array($ext, $allowedExtensions)) {
                                try {
                                    $mimeType = '';
                                    if (function_exists('mime_content_type')) {
                                        $mimeType = @mime_content_type($itemPath);
                                    } else if (function_exists('finfo_file')) {
                                        $finfo = @finfo_open(FILEINFO_MIME_TYPE);
                                        if ($finfo) {
                                            $mimeType = @finfo_file($finfo, $itemPath);
                                            @finfo_close($finfo);
                                        }
                                    }
                                    
                                    $fileType = (strpos($mimeType, 'image/') === 0 || in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) ? 'image' : 'video';
                                    
                                    $fileSize = @filesize($itemPath);
                                    $fileModified = @filemtime($itemPath);
                                    
                                    if ($fileSize !== false && $fileModified !== false) {
                                        $files[] = [
                                            'name' => $item,
                                            'url' => $uploadUrl . 'slider-media/' . $item,
                                            'size' => $fileSize,
                                            'type' => $fileType,
                                            'modified' => $fileModified
                                        ];
                                    }
                                } catch (Exception $e) {
                                    // Dosya okuma hatası, atla
                                    continue;
                                }
                            }
                        }
                    }
                }
            }
            
            // Tarihe göre sırala (yeni önce)
            usort($files, function($a, $b) {
                return $b['modified'] - $a['modified'];
            });
            
            $this->json([
                'success' => true,
                'files' => $files
            ]);
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Dosya listesi alınırken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Dosya yükleme (AJAX)
     */
    public function fileManagerUpload() {
        // AJAX endpoint için hata gösterimini kapat
        ini_set('display_errors', 0);
        error_reporting(E_ALL);
        
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file'])) {
            $this->json(['success' => false, 'message' => 'Dosya bulunamadı'], 400);
        }
        
        $file = $_FILES['file'];
        $allowedTypes = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 
            'image/webp', 'image/svg+xml',
            'video/mp4', 'video/webm', 'video/quicktime'
        ];
        $maxSize = 50 * 1024 * 1024; // 50MB
        
        // Dosya tipi kontrolü
        if (!in_array($file['type'], $allowedTypes)) {
            $this->json(['success' => false, 'message' => 'Geçersiz dosya tipi. Sadece resim ve video dosyaları yükleyebilirsiniz.'], 400);
        }
        
        // Dosya boyutu kontrolü
        if ($file['size'] > $maxSize) {
            $this->json(['success' => false, 'message' => 'Dosya boyutu çok büyük. Maksimum 50MB olabilir.'], 400);
        }
        
        // Upload klasörünü oluştur
        $uploadDir = __DIR__ . '/../../public/uploads/slider-media/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Dosya adını oluştur
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'slider_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Dosyayı yükle
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $fileUrl = site_url('uploads/slider-media/' . $filename);
            
            $this->json([
                'success' => true,
                'message' => 'Dosya başarıyla yüklendi.',
                'file' => [
                    'name' => $filename,
                    'url' => $fileUrl,
                    'size' => filesize($filepath),
                    'type' => strpos($file['type'], 'image/') === 0 ? 'image' : 'video'
                ]
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Dosya yüklenirken bir hata oluştu.'], 500);
        }
    }
    
    /**
     * Dosya silme (AJAX)
     */
    public function fileManagerDelete() {
        // AJAX endpoint için hata gösterimini kapat
        ini_set('display_errors', 0);
        error_reporting(E_ALL);
        
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Geçersiz istek'], 400);
        }
        
        $fileUrl = $_POST['file_url'] ?? '';
        
        if (empty($fileUrl)) {
            $this->json(['success' => false, 'message' => 'Dosya URL\'si gerekli'], 400);
        }
        
        // URL'den dosya yolunu çıkar
        $uploadUrl = site_url('uploads/');
        $relativePath = str_replace($uploadUrl, '', $fileUrl);
        $filePath = __DIR__ . '/../../public/uploads/' . $relativePath;
        
        // Güvenlik kontrolü - sadece uploads klasörü içindeki dosyalar silinebilir
        $realUploadPath = realpath(__DIR__ . '/../../public/uploads/');
        $realFilePath = realpath($filePath);
        
        if (!$realFilePath || strpos($realFilePath, $realUploadPath) !== 0) {
            $this->json(['success' => false, 'message' => 'Geçersiz dosya yolu'], 400);
        }
        
        if (file_exists($filePath) && is_file($filePath)) {
            if (unlink($filePath)) {
                $this->json(['success' => true, 'message' => 'Dosya başarıyla silindi']);
            } else {
                $this->json(['success' => false, 'message' => 'Dosya silinirken bir hata oluştu'], 500);
            }
        } else {
            $this->json(['success' => false, 'message' => 'Dosya bulunamadı'], 404);
        }
    }
}
