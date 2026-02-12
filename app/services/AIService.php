<?php
/**
 * AI Service - Groq Cloud API Entegrasyonu
 * İlan açıklamalarını otomatik olarak üretir
 */

class AIService {
    private $apiKey;
    private $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
    private $model = 'llama-3.3-70b-versatile'; // Güncel model (llama-3.1-70b-versatile kullanımdan kaldırıldı)
    private $timeout = 30;
    
    public function __construct() {
        // API key'i ayarlardan al
        $this->apiKey = get_option('groq_api_key', '');
    }
    
    /**
     * İlan açıklaması oluştur
     * 
     * @param array $listingData İlan bilgileri
     * @return array ['success' => bool, 'description' => string, 'error' => string]
     */
    public function generateListingDescription($listingData) {
        // API key kontrolü
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'Groq API anahtarı yapılandırılmamış. Lütfen ayarlar sayfasından API anahtarınızı girin.'
            ];
        }
        
        // Prompt oluştur
        $prompt = $this->buildPrompt($listingData);
        
        // API çağrısı yap
        $response = $this->callGroqAPI($prompt);
        
        if (!$response['success']) {
            return $response;
        }
        
        // Başarılı yanıtı döndür
        return [
            'success' => true,
            'description' => trim($response['description'])
        ];
    }
    
    /**
     * İlan bilgilerinden prompt oluştur
     */
    private function buildPrompt($data) {
        $propertyTypeMap = [
            'house' => 'Müstakil Ev',
            'apartment' => 'Daire',
            'villa' => 'Villa',
            'commercial' => 'Ticari',
            'land' => 'Arsa'
        ];
        
        $listingStatusMap = [
            'sale' => 'Satılık',
            'rent' => 'Kiralık'
        ];
        
        $areaUnitMap = [
            'sqm' => 'm²',
            'sqft' => 'ft²'
        ];
        
        $propertyType = $propertyTypeMap[$data['property_type'] ?? 'house'] ?? 'Emlak';
        $listingStatus = $listingStatusMap[$data['listing_status'] ?? 'sale'] ?? 'Satılık';
        $areaUnit = $areaUnitMap[$data['area_unit'] ?? 'sqm'] ?? 'm²';
        
        // Fiyat formatla
        $price = isset($data['price']) && $data['price'] > 0 
            ? number_format($data['price'], 0, ',', '.') . ' TL' 
            : 'Fiyat belirtilmemiş';
        
        // Oda bilgilerini topla
        $roomInfo = [];
        if (!empty($data['bedrooms']) && $data['bedrooms'] > 0) {
            $roomInfo[] = $data['bedrooms'] . ' yatak odası';
        }
        if (!empty($data['bathrooms']) && $data['bathrooms'] > 0) {
            $roomInfo[] = $data['bathrooms'] . ' banyo';
        }
        if (!empty($data['living_rooms']) && $data['living_rooms'] > 0) {
            $roomInfo[] = $data['living_rooms'] . ' salon';
        }
        if (!empty($data['rooms']) && $data['rooms'] > 0) {
            $roomInfo[] = $data['rooms'] . ' oda';
        }
        $roomDetails = !empty($roomInfo) ? implode(', ', $roomInfo) : 'Oda bilgisi belirtilmemiş';
        
        // Alan bilgisi
        $area = isset($data['area']) && $data['area'] > 0 
            ? number_format($data['area'], 0, ',', '.') . ' ' . $areaUnit
            : 'Alan belirtilmemiş';
        
        $prompt = "Sen profesyonel bir emlak danışmanısın. Aşağıdaki bilgilere göre dikkat çekici, SEO uyumlu ve ikna edici bir ilan açıklaması yaz.

İlan Bilgileri:
- Başlık: " . ($data['title'] ?? 'İlan başlığı belirtilmemiş') . "
- Konum: " . ($data['location'] ?? 'Konum belirtilmemiş') . "
- Fiyat: {$price} ({$listingStatus})
- Emlak Tipi: {$propertyType}
- Oda Detayları: {$roomDetails}
- Alan: {$area}

Açıklama gereksinimleri:
- Türkçe yazılmalı
- Profesyonel ve satış odaklı olmalı
- Özellikleri vurgulamalı
- SEO uyumlu olmalı (anahtar kelimeler doğal şekilde kullanılmalı)
- 200-400 kelime arasında olmalı
- Paragraflar halinde düzenlenmeli
- Müşteriyi harekete geçirecek çağrılar içermeli

Sadece açıklama metnini yaz, başlık veya başka ek bilgi ekleme.";

        return $prompt;
    }
    
    /**
     * Groq API'ye çağrı yap
     */
    private function callGroqAPI($prompt) {
        $ch = curl_init($this->apiUrl);
        
        $payload = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Sen profesyonel bir emlak danışmanısın. Türkçe, profesyonel ve ikna edici ilan açıklamaları yazarsın.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 1000
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // CURL hatası kontrolü
        if ($error) {
            return [
                'success' => false,
                'error' => 'API bağlantı hatası: ' . $error
            ];
        }
        
        // HTTP hata kodu kontrolü
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? 'Bilinmeyen API hatası';
            
            return [
                'success' => false,
                'error' => 'API hatası (' . $httpCode . '): ' . $errorMessage
            ];
        }
        
        // Yanıtı parse et
        $data = json_decode($response, true);
        
        if (!isset($data['choices'][0]['message']['content'])) {
            return [
                'success' => false,
                'error' => 'API yanıtı beklenmeyen formatta'
            ];
        }
        
        return [
            'success' => true,
            'description' => $data['choices'][0]['message']['content']
        ];
    }

    /**
     * Parsel bilgilerinden içerik açıklaması oluştur
     */
    public function generateParselDescription($parselData) {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'Groq API anahtarı yapılandırılmamış. Lütfen ayarlar sayfasından API anahtarınızı girin.'
            ];
        }
        $prompt = $this->buildParselPrompt($parselData);
        $response = $this->callGroqAPI($prompt);
        if (!$response['success']) return $response;
        $desc = trim($response['description']);
        if (mb_strlen($desc) > 800) {
            $desc = mb_substr($desc, 0, 800);
        }
        return ['success' => true, 'description' => $desc];
    }

    private function buildParselPrompt($data) {
        $konum = array_filter([$data['il_adi'] ?? '', $data['ilce_adi'] ?? '', $data['mahalle_adi'] ?? '']);
        $konumStr = !empty($konum) ? implode(' / ', $konum) : 'Konum belirtilmemiş';
        $ada = $data['ada'] ?? '—';
        $parselNo = $data['parsel_no'] ?? '—';
        $alan = isset($data['alan_m2']) && $data['alan_m2'] > 0 ? number_format($data['alan_m2'], 0, ',', '.') . ' m²' : 'Alan belirtilmemiş';
        $nitelik = $data['nitelik'] ?? 'Parsel';
        $yakınLok = $data['yakın_lokasyonlar'] ?? [];
        $yakınLokStr = '';
        if (!empty($yakınLok) && is_array($yakınLok)) {
            $yakınLokStr = "\n- Yakın Lokasyonlar: " . implode(' | ', array_map('trim', $yakınLok));
        }
        return "Aşağıdaki parsel bilgilerine göre drone videoda kullanılacak seslendirme metni yaz. Emlak tanıtımı tarzında, profesyonel ve ikna edici bir metin.\n\nParsel Bilgileri:\n- Konum: {$konumStr}\n- Ada: {$ada}\n- Parsel: {$parselNo}\n- Alan: {$alan}\n- Nitelik: {$nitelik}{$yakınLokStr}\n\nFormat örneği: \"[İl]'un [ilçe] ilçesi [mahalle] Mahallesi'nde yer alan, ada [sayı yazıyla], parsel [sayı] numaralı, [alan metrekare yazıyla] metrekarelik [nitelik] nitelikli tek tapulu arazimiz satışa sunulmuştur. [Varsa yakın lokasyonları burada doğal biçimde ekle: Örneğin '...konum avantajı sunarken, yakınında X kilometre mesafede Y, Z kilometrede W gibi önemli noktalar bulunmaktadır.'] Bu geniş arazi, yatırımcılar için eşsiz bir fırsat sunarken... Değer kazanma potansiyeline sahiptir. Detaylı bilgi almak için hemen iletişime geçin.\"\n\nGereksinimler: Türkçe, maksimum 800 karakter. Ada ve parsel numaralarını yazıyla yaz. Alanı metrekare olarak yazıyla ifade et. Nitelik kelimesini metne dahil et. Yakın lokasyonlar verildiyse, bunları metne doğal ve akıcı şekilde ekle (mesafe bilgisiyle birlikte). Yatırımcıyı cezbeden, bölge avantajlarını vurgulayan, sonunda iletişime geçin çağrısı olan akıcı bir seslendirme metni yaz. Sadece metni yaz, başlık ekleme.";
    }

    /**
     * API key'i test et
     */
    public function testAPIKey($apiKey = null) {
        if ($apiKey === null) {
            $apiKey = $this->apiKey;
        }

        if (empty($apiKey)) {
            return [
                'success' => false,
                'error' => 'API anahtarı boş'
            ];
        }

        $testPrompt = "Merhaba, bu bir test mesajıdır. Lütfen 'Test başarılı' yaz.";

        $ch = curl_init($this->apiUrl);

        $payload = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $testPrompt
                ]
            ],
            'max_tokens' => 50
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            return [
                'success' => true,
                'message' => 'API anahtarı geçerli'
            ];
        } else {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? 'Bilinmeyen hata';

            return [
                'success' => false,
                'error' => 'API anahtarı geçersiz: ' . $errorMessage
            ];
        }
    }
}
