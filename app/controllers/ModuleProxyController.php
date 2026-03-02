<?php
/**
 * Modül frontend route'ları için proxy controller.
 * handleFrontendRoute eşleşmezse (örn. modül aktif değilse) Router bu controller üzerinden
 * ilanlar / ilanlar/kategori / ilan detay sayfalarını sunar.
 */
class ModuleProxyController {

    /**
     * İlanlar listesi - realestate-listings modülü frontend_index
     */
    public function listingsIndex() {
        $this->delegateListings('frontend_index', []);
    }

    /**
     * İlanlar kategori sayfası - realestate-listings modülü frontend_category
     */
    public function listingsCategory($slug) {
        $this->delegateListings('frontend_category', [$slug]);
    }

    /**
     * İlan detay - realestate-listings modülü frontend_detail
     */
    public function listingDetail($slug) {
        $this->delegateListings('frontend_detail', [$slug]);
    }

    /**
     * realestate-listings controller'ı yükleyip ilgili metodu çağırır
     */
    private function delegateListings($method, array $args = []) {
        $basePath = dirname(__DIR__, 2);
        $controllerFile = $basePath . '/modules/realestate-listings/Controller.php';
        if (!is_file($controllerFile)) {
            $this->render404();
            return;
        }
        require_once $controllerFile;
        if (!class_exists('RealEstateListingsController')) {
            $this->render404();
            return;
        }
        $controller = new RealEstateListingsController();
        if (!method_exists($controller, $method)) {
            $this->render404();
            return;
        }
        call_user_func_array([$controller, $method], $args);
    }

    private function render404() {
        http_response_code(404);
        require_once dirname(__DIR__, 2) . '/core/ViewRenderer.php';
        $renderer = ViewRenderer::getInstance();
        $renderer->setLayout('default');
        $renderer->render('frontend/404', ['title' => 'Sayfa Bulunamadı', 'current_page' => '404']);
    }
}
