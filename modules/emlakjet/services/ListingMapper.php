<?php
/**
 * Listing Mapper
 * realestate_listings ve Emlakjet formatları arasında veri dönüşümü
 */

class ListingMapper {
    private $settings;
    
    // Property type mapping - Genişletilmiş mapping
    private $propertyTypeMap = [
        // İngilizce -> Türkçe
        'house' => 'konut',
        'apartment' => 'daire',
        'flat' => 'daire',
        'villa' => 'villa',
        'commercial' => 'ticari',
        'land' => 'arsa',
        'office' => 'ofis',
        'shop' => 'dükkan',
        'store' => 'dükkan',
        'warehouse' => 'depo',
        'factory' => 'fabrika',
        'building' => 'bina',
        'residence' => 'konut',
        'penthouse' => 'penthouse',
        'duplex' => 'dubleks',
        'triplex' => 'tripleks',
        'maisonette' => 'maisonette',
        'studio' => 'stüdyo',
        'loft' => 'loft',
        // Türkçe alternatifler (fallback için)
        'konut' => 'konut',
        'daire' => 'daire',
        'arsa' => 'arsa',
        'ticari' => 'ticari',
        'ofis' => 'ofis',
        'dükkan' => 'dükkan'
    ];
    
    // Reverse mapping için Türkçe -> İngilizce
    private $reversePropertyTypeMap = [
        'konut' => 'house',
        'daire' => 'apartment',
        'villa' => 'villa',
        'ticari' => 'commercial',
        'arsa' => 'land',
        'ofis' => 'office',
        'dükkan' => 'shop',
        'depo' => 'warehouse',
        'fabrika' => 'factory',
        'bina' => 'building',
        'penthouse' => 'penthouse',
        'dubleks' => 'duplex',
        'tripleks' => 'triplex',
        'maisonette' => 'maisonette',
        'stüdyo' => 'studio',
        'loft' => 'loft'
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
     * Property type mapping - Geliştirilmiş fallback mekanizması
     */
    private function mapPropertyType($type) {
        if (empty($type)) {
            return 'konut'; // Varsayılan
        }
        
        $typeLower = strtolower(trim($type));
        
        // Direkt mapping kontrolü
        if (isset($this->propertyTypeMap[$typeLower])) {
            return $this->propertyTypeMap[$typeLower];
        }
        
        // Kısmi eşleşme kontrolü (ör: "apartment_2" -> "apartment")
        foreach ($this->propertyTypeMap as $key => $value) {
            if (strpos($typeLower, $key) !== false || strpos($key, $typeLower) !== false) {
                return $value;
            }
        }
        
        // Fallback: Türkçe karakterleri temizle ve tekrar dene
        $normalized = $this->normalizeString($typeLower);
        if (isset($this->propertyTypeMap[$normalized])) {
            return $this->propertyTypeMap[$normalized];
        }
        
        // Son çare: varsayılan değer
        error_log("Emlakjet: Bilinmeyen property type '{$type}', 'konut' olarak kullanılıyor");
        return 'konut';
    }
    
    /**
     * Reverse property type mapping - Geliştirilmiş
     */
    private function reverseMapPropertyType($type) {
        if (empty($type)) {
            return 'house'; // Varsayılan
        }
        
        $typeLower = strtolower(trim($type));
        
        // Direkt reverse mapping kontrolü
        if (isset($this->reversePropertyTypeMap[$typeLower])) {
            return $this->reversePropertyTypeMap[$typeLower];
        }
        
        // Kısmi eşleşme kontrolü
        foreach ($this->reversePropertyTypeMap as $key => $value) {
            if (strpos($typeLower, $key) !== false || strpos($key, $typeLower) !== false) {
                return $value;
            }
        }
        
        // Fallback: Normalize et ve tekrar dene
        $normalized = $this->normalizeString($typeLower);
        if (isset($this->reversePropertyTypeMap[$normalized])) {
            return $this->reversePropertyTypeMap[$normalized];
        }
        
        // Son çare: varsayılan değer
        error_log("Emlakjet: Bilinmeyen reverse property type '{$type}', 'house' olarak kullanılıyor");
        return 'house';
    }
    
    /**
     * String normalizasyonu (Türkçe karakterleri temizle)
     */
    private function normalizeString($str) {
        $turkish = ['ş', 'Ş', 'ı', 'İ', 'ğ', 'Ğ', 'ü', 'Ü', 'ö', 'Ö', 'ç', 'Ç'];
        $english = ['s', 's', 'i', 'i', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c'];
        return str_replace($turkish, $english, $str);
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
     * Lokasyon parse et (string -> array) - Geliştirilmiş parser
     * Farklı formatları destekler:
     * - "İstanbul, Kadıköy, Acıbadem"
     * - "İstanbul / Kadıköy / Acıbadem"
     * - "İstanbul-Kadıköy-Acıbadem"
     * - "Kadıköy, İstanbul"
     * - "İstanbul"
     */
    private function parseLocation($locationString) {
        if (empty($locationString)) {
            return [
                'city' => '',
                'district' => '',
                'neighborhood' => ''
            ];
        }
        
        // Eğer zaten array ise, direkt döndür
        if (is_array($locationString)) {
            return [
                'city' => $locationString['city'] ?? '',
                'district' => $locationString['district'] ?? '',
                'neighborhood' => $locationString['neighborhood'] ?? ''
            ];
        }
        
        // Farklı ayırıcıları normalize et
        $normalized = str_replace(['/', '-', '|'], ',', $locationString);
        
        // Virgülle ayır ve temizle
        $parts = explode(',', $normalized);
        $parts = array_map('trim', $parts);
        $parts = array_filter($parts, function($part) {
            return !empty($part);
        });
        $parts = array_values($parts); // Index'leri sıfırla
        
        // Türkiye'nin büyük şehirlerini tespit et (şehir genelde ilk veya son olur)
        $majorCities = [
            'istanbul', 'ankara', 'izmir', 'bursa', 'antalya', 'adana', 'gaziantep',
            'konya', 'kayseri', 'mersin', 'eskisehir', 'diyarbakir', 'samsun',
            'denizli', 'sanliurfa', 'adapazari', 'malatya', 'kahramanmaras',
            'erzurum', 'van', 'batman', 'elazig', 'istanbul', 'ankara', 'izmir'
        ];
        
        $result = [
            'city' => '',
            'district' => '',
            'neighborhood' => ''
        ];
        
        if (count($parts) === 1) {
            // Tek parça varsa, şehir olarak kabul et
            $result['city'] = $parts[0];
        } elseif (count($parts) === 2) {
            // İki parça varsa
            $firstLower = mb_strtolower($parts[0], 'UTF-8');
            $secondLower = mb_strtolower($parts[1], 'UTF-8');
            
            // İlk parça büyük şehir ise
            if (in_array($firstLower, $majorCities)) {
                $result['city'] = $parts[0];
                $result['district'] = $parts[1];
            } else {
                // İkinci parça büyük şehir ise
                $result['city'] = $parts[1];
                $result['district'] = $parts[0];
            }
        } elseif (count($parts) >= 3) {
            // Üç veya daha fazla parça varsa
            $firstLower = mb_strtolower($parts[0], 'UTF-8');
            $lastLower = mb_strtolower($parts[count($parts) - 1], 'UTF-8');
            
            // İlk parça büyük şehir ise
            if (in_array($firstLower, $majorCities)) {
                $result['city'] = $parts[0];
                $result['district'] = $parts[1] ?? '';
                $result['neighborhood'] = implode(', ', array_slice($parts, 2));
            } elseif (in_array($lastLower, $majorCities)) {
                // Son parça büyük şehir ise
                $result['city'] = $parts[count($parts) - 1];
                $result['district'] = $parts[0] ?? '';
                $result['neighborhood'] = implode(', ', array_slice($parts, 1, -1));
            } else {
                // Varsayılan: ilk şehir, ikinci ilçe, geri kalanı mahalle
                $result['city'] = $parts[0];
                $result['district'] = $parts[1] ?? '';
                $result['neighborhood'] = implode(', ', array_slice($parts, 2));
            }
        }
        
        return $result;
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
     * Emlakjet verisini validate et - Geliştirilmiş validation
     */
    private function validateEmlakjetData(&$data) {
        $errors = [];
        
        // 1. Başlık kontrolü
        if (empty($data['title']) || trim($data['title']) === '') {
            $errors[] = 'İlan başlığı zorunludur ve boş olamaz';
        } elseif (mb_strlen($data['title'], 'UTF-8') < 10) {
            $errors[] = 'İlan başlığı en az 10 karakter olmalıdır';
        } elseif (mb_strlen($data['title'], 'UTF-8') > 200) {
            $errors[] = 'İlan başlığı en fazla 200 karakter olabilir';
            $data['title'] = mb_substr($data['title'], 0, 200, 'UTF-8');
        }
        
        // 2. Fiyat kontrolü
        $price = floatval($data['price'] ?? 0);
        if ($price <= 0) {
            $errors[] = 'İlan fiyatı 0\'dan büyük olmalıdır';
        } elseif ($price < 1000) {
            // Çok düşük fiyat uyarısı (belki TL yerine başka birim kullanılmış)
            error_log("Emlakjet: Düşük fiyat uyarısı - " . $price . " TL - " . ($data['title'] ?? 'N/A'));
        } elseif ($price > 1000000000) {
            // Çok yüksek fiyat kontrolü (muhtemelen hata)
            $errors[] = 'İlan fiyatı çok yüksek görünüyor (1 milyar TL\'den fazla). Lütfen kontrol edin.';
        }
        
        // 3. Lokasyon kontrolü
        if (empty($data['location']) || !is_array($data['location'])) {
            $errors[] = 'İlan lokasyonu zorunludur ve array formatında olmalıdır';
        } else {
            if (empty($data['location']['city']) || trim($data['location']['city']) === '') {
                $errors[] = 'İlan lokasyonu (şehir) zorunludur';
            }
            
            // İlçe kontrolü (opsiyonel ama önerilir)
            if (empty($data['location']['district'])) {
                error_log("Emlakjet: İlan lokasyonunda ilçe bilgisi yok - " . ($data['title'] ?? 'N/A'));
            }
        }
        
        // 4. Property type kontrolü
        $validPropertyTypes = ['konut', 'daire', 'villa', 'ticari', 'arsa', 'ofis', 'dükkan', 'depo', 'fabrika', 'bina'];
        if (!empty($data['property_type']) && !in_array($data['property_type'], $validPropertyTypes)) {
            error_log("Emlakjet: Geçersiz property type - " . $data['property_type'] . " - " . ($data['title'] ?? 'N/A'));
            // Fallback olarak 'konut' kullan
            $data['property_type'] = 'konut';
        }
        
        // 5. Listing type kontrolü
        $validListingTypes = ['satilik', 'kiralik'];
        if (!empty($data['listing_type']) && !in_array($data['listing_type'], $validListingTypes)) {
            error_log("Emlakjet: Geçersiz listing type - " . $data['listing_type'] . " - " . ($data['title'] ?? 'N/A'));
            // Fallback olarak 'satilik' kullan
            $data['listing_type'] = 'satilik';
        }
        
        // 6. Alan kontrolü
        $area = floatval($data['area'] ?? 0);
        if ($area < 0) {
            $errors[] = 'İlan alanı negatif olamaz';
            $data['area'] = 0;
        } elseif ($area > 0 && $area < 10) {
            // Çok küçük alan uyarısı (belki birim hatası)
            error_log("Emlakjet: Çok küçük alan uyarısı - " . $area . " m² - " . ($data['title'] ?? 'N/A'));
        } elseif ($area > 100000) {
            // Çok büyük alan kontrolü (muhtemelen hata)
            error_log("Emlakjet: Çok büyük alan uyarısı - " . $area . " m² - " . ($data['title'] ?? 'N/A'));
        }
        
        // 7. Oda sayıları kontrolü
        $bedrooms = intval($data['bedrooms'] ?? 0);
        $bathrooms = intval($data['bathrooms'] ?? 0);
        $livingRooms = intval($data['living_rooms'] ?? 0);
        $rooms = intval($data['rooms'] ?? 0);
        
        if ($bedrooms < 0) $data['bedrooms'] = 0;
        if ($bathrooms < 0) $data['bathrooms'] = 0;
        if ($livingRooms < 0) $data['living_rooms'] = 0;
        if ($rooms < 0) $data['rooms'] = 0;
        
        if ($bedrooms > 50 || $bathrooms > 50 || $livingRooms > 20 || $rooms > 100) {
            error_log("Emlakjet: Olağandışı oda sayıları - Yatak: {$bedrooms}, Banyo: {$bathrooms}, Salon: {$livingRooms}, Oda: {$rooms} - " . ($data['title'] ?? 'N/A'));
        }
        
        // 8. Görsel kontrolü (opsiyonel ama önerilir)
        if (empty($data['images']) || !is_array($data['images']) || count($data['images']) === 0) {
            error_log("Emlakjet: İlan görseli yok - " . ($data['title'] ?? 'N/A'));
        } else {
            // Görsel URL'lerini kontrol et
            foreach ($data['images'] as $index => $imageUrl) {
                if (empty($imageUrl) || !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                    error_log("Emlakjet: Geçersiz görsel URL - Index: {$index} - " . ($data['title'] ?? 'N/A'));
                    // Geçersiz URL'leri kaldır
                    unset($data['images'][$index]);
                }
            }
            // Array'i yeniden indexle
            $data['images'] = array_values($data['images']);
        }
        
        // 9. Açıklama kontrolü (opsiyonel)
        if (!empty($data['description'])) {
            $descLength = mb_strlen($data['description'], 'UTF-8');
            if ($descLength > 10000) {
                error_log("Emlakjet: Açıklama çok uzun - " . $descLength . " karakter - " . ($data['title'] ?? 'N/A'));
                $data['description'] = mb_substr($data['description'], 0, 10000, 'UTF-8');
            }
        }
        
        // Hataları fırlat
        if (!empty($errors)) {
            throw new Exception('Validation hataları: ' . implode(', ', $errors));
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
