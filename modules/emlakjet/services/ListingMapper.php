<?php
/**
 * Listing Mapper
 * realestate_listings ve Emlakjet formatları arasında veri dönüşümü
 */

class ListingMapper {
    private $settings;
    
    // Property type mapping
    private $propertyTypeMap = [
        'house' => 'konut',
        'apartment' => 'daire',
        'villa' => 'villa',
        'commercial' => 'ticari',
        'land' => 'arsa',
        'office' => 'ofis',
        'shop' => 'dükkan'
    ];
    
    // Listing status mapping
    private $listingStatusMap = [
        'sale' => 'satilik',
        'rent' => 'kiralik'
    ];
    
    // Area unit conversion (sqft to m2)
    private $areaUnitConversion = [
        'sqft' => 0.092903, // 1 sqft = 0.092903 m2
        'sqm' => 1,
        'm2' => 1
    ];
    
    public function __construct($settings) {
        $this->settings = $settings;
        
        // Custom mapping varsa kullan
        if (!empty($settings['property_type_mapping'])) {
            $this->propertyTypeMap = array_merge($this->propertyTypeMap, $settings['property_type_mapping']);
        }
    }
    
    /**
     * realestate_listings verisini Emlakjet formatına dönüştür
     */
    public function toEmlakjetFormat($listing) {
        // Temel bilgiler
        $emlakjetData = [
            'title' => $listing['title'] ?? '',
            'description' => $listing['description'] ?? '',
            'price' => floatval($listing['price'] ?? 0),
            'property_type' => $this->mapPropertyType($listing['property_type'] ?? 'house'),
            'listing_type' => $this->mapListingStatus($listing['listing_status'] ?? 'sale'),
            'location' => $this->parseLocation($listing['location'] ?? ''),
            'area' => $this->convertArea($listing['area'] ?? 0, $listing['area_unit'] ?? 'sqm'),
            'area_unit' => 'm2',
            'bedrooms' => intval($listing['bedrooms'] ?? 0),
            'bathrooms' => intval($listing['bathrooms'] ?? 0),
            'living_rooms' => intval($listing['living_rooms'] ?? 0),
            'rooms' => intval($listing['rooms'] ?? 0),
            'images' => $this->parseImages($listing),
            'status' => $listing['status'] === 'published' ? 'active' : 'draft'
        ];
        
        // Ekstra alanlar
        if (!empty($listing['slug'])) {
            $emlakjetData['slug'] = $listing['slug'];
        }
        
        if (!empty($listing['realtor_id'])) {
            $emlakjetData['realtor_id'] = $listing['realtor_id'];
        }
        
        // Validation
        $this->validateEmlakjetData($emlakjetData);
        
        return $emlakjetData;
    }
    
    /**
     * Emlakjet verisini realestate_listings formatına dönüştür
     */
    public function fromEmlakjetFormat($emlakjetData) {
        $listing = [
            'title' => $emlakjetData['title'] ?? '',
            'description' => $emlakjetData['description'] ?? '',
            'price' => floatval($emlakjetData['price'] ?? 0),
            'property_type' => $this->reverseMapPropertyType($emlakjetData['property_type'] ?? 'konut'),
            'listing_status' => $this->reverseMapListingStatus($emlakjetData['listing_type'] ?? 'satilik'),
            'location' => $this->formatLocation($emlakjetData['location'] ?? []),
            'area' => floatval($emlakjetData['area'] ?? 0),
            'area_unit' => $emlakjetData['area_unit'] ?? 'sqm',
            'bedrooms' => intval($emlakjetData['bedrooms'] ?? 0),
            'bathrooms' => intval($emlakjetData['bathrooms'] ?? 0),
            'living_rooms' => intval($emlakjetData['living_rooms'] ?? 0),
            'rooms' => intval($emlakjetData['rooms'] ?? 0),
            'status' => ($emlakjetData['status'] ?? 'active') === 'active' ? 'published' : 'draft'
        ];
        
        // Görselleri işle
        if (!empty($emlakjetData['images'])) {
            $images = is_array($emlakjetData['images']) ? $emlakjetData['images'] : json_decode($emlakjetData['images'], true);
            if (!empty($images)) {
                $listing['featured_image'] = $images[0] ?? null;
                $listing['gallery'] = json_encode($images, JSON_UNESCAPED_UNICODE);
            }
        }
        
        // Slug
        if (!empty($emlakjetData['slug'])) {
            $listing['slug'] = $emlakjetData['slug'];
        } else {
            $listing['slug'] = $this->generateSlug($listing['title']);
        }
        
        return $listing;
    }
    
    /**
     * Property type mapping
     */
    private function mapPropertyType($type) {
        return $this->propertyTypeMap[strtolower($type)] ?? 'konut';
    }
    
    /**
     * Reverse property type mapping
     */
    private function reverseMapPropertyType($type) {
        $reverseMap = array_flip($this->propertyTypeMap);
        return $reverseMap[strtolower($type)] ?? 'house';
    }
    
    /**
     * Listing status mapping
     */
    private function mapListingStatus($status) {
        return $this->listingStatusMap[strtolower($status)] ?? 'satilik';
    }
    
    /**
     * Reverse listing status mapping
     */
    private function reverseMapListingStatus($status) {
        $reverseMap = array_flip($this->listingStatusMap);
        return $reverseMap[strtolower($status)] ?? 'sale';
    }
    
    /**
     * Lokasyon parse et (string -> array)
     */
    private function parseLocation($locationString) {
        if (empty($locationString)) {
            return [
                'city' => '',
                'district' => '',
                'neighborhood' => ''
            ];
        }
        
        // Basit parsing (İstanbul, Kadıköy, Acıbadem formatı)
        $parts = explode(',', $locationString);
        $parts = array_map('trim', $parts);
        
        return [
            'city' => $parts[0] ?? '',
            'district' => $parts[1] ?? '',
            'neighborhood' => $parts[2] ?? ''
        ];
    }
    
    /**
     * Lokasyon formatla (array -> string)
     */
    private function formatLocation($locationArray) {
        if (is_string($locationArray)) {
            return $locationArray;
        }
        
        $parts = [];
        if (!empty($locationArray['city'])) {
            $parts[] = $locationArray['city'];
        }
        if (!empty($locationArray['district'])) {
            $parts[] = $locationArray['district'];
        }
        if (!empty($locationArray['neighborhood'])) {
            $parts[] = $locationArray['neighborhood'];
        }
        
        return implode(', ', $parts);
    }
    
    /**
     * Alan birimi dönüştür
     */
    private function convertArea($area, $unit) {
        if (empty($area) || $area <= 0) {
            return 0;
        }
        
        // Eğer zaten m2 ise dönüştürme yapma
        if (in_array(strtolower($unit), ['sqm', 'm2', 'm²'])) {
            return floatval($area);
        }
        
        // sqft'den m2'ye dönüştür
        if (strtolower($unit) === 'sqft') {
            $conversionRate = $this->areaUnitConversion['sqft'] ?? 0.092903;
            return floatval($area) * $conversionRate;
        }
        
        // Diğer birimler için dönüştürme yapılmaz
        return floatval($area);
    }
    
    /**
     * Görselleri parse et
     */
    private function parseImages($listing) {
        $images = [];
        
        // Featured image
        if (!empty($listing['featured_image'])) {
            $images[] = $this->getFullImageUrl($listing['featured_image']);
        }
        
        // Gallery
        if (!empty($listing['gallery'])) {
            $gallery = is_array($listing['gallery']) 
                ? $listing['gallery'] 
                : json_decode($listing['gallery'], true);
            
            if (is_array($gallery)) {
                foreach ($gallery as $image) {
                    $imageUrl = is_array($image) ? ($image['url'] ?? $image['src'] ?? '') : $image;
                    if (!empty($imageUrl)) {
                        $fullUrl = $this->getFullImageUrl($imageUrl);
                        if (!in_array($fullUrl, $images)) {
                            $images[] = $fullUrl;
                        }
                    }
                }
            }
        }
        
        return $images;
    }
    
    /**
     * Tam görsel URL'i al
     */
    private function getFullImageUrl($imagePath) {
        // Eğer zaten tam URL ise
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            return $imagePath;
        }
        
        // Relative path ise tam URL'e çevir
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') 
            . '://' . $_SERVER['HTTP_HOST'];
        
        // Path başında / yoksa ekle
        if (substr($imagePath, 0, 1) !== '/') {
            $imagePath = '/' . $imagePath;
        }
        
        return $baseUrl . $imagePath;
    }
    
    /**
     * Emlakjet verisini validate et
     */
    private function validateEmlakjetData(&$data) {
        // Zorunlu alanlar
        if (empty($data['title'])) {
            throw new Exception('İlan başlığı zorunludur');
        }
        
        if (empty($data['price']) || $data['price'] <= 0) {
            throw new Exception('İlan fiyatı geçerli olmalıdır');
        }
        
        if (empty($data['location']['city'])) {
            throw new Exception('İlan lokasyonu (şehir) zorunludur');
        }
        
        // Minimum görsel kontrolü (opsiyonel)
        if (empty($data['images']) || count($data['images']) === 0) {
            // Görsel zorunlu değilse uyarı ver
            error_log("Emlakjet: İlan görseli yok - " . $data['title']);
        }
    }
    
    /**
     * Slug oluştur
     */
    private function generateSlug($title) {
        // Türkçe karakterleri değiştir
        $turkish = ['ş', 'Ş', 'ı', 'İ', 'ğ', 'Ğ', 'ü', 'Ü', 'ö', 'Ö', 'ç', 'Ç'];
        $english = ['s', 's', 'i', 'i', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c'];
        $title = str_replace($turkish, $english, $title);
        
        // Küçük harfe çevir
        $title = mb_strtolower($title, 'UTF-8');
        
        // Özel karakterleri temizle
        $title = preg_replace('/[^a-z0-9\s-]/', '', $title);
        
        // Boşlukları tire ile değiştir
        $title = preg_replace('/[\s-]+/', '-', $title);
        
        // Baş ve sondaki tireleri temizle
        $title = trim($title, '-');
        
        return $title;
    }
}
