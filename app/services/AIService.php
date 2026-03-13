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

        // API çağrısı yap (düşük temperature = daha tutarlı, saçmalamayan çıktı)
        $response = $this->callGroqAPI($prompt, [
            'system' => $this->getListingSystemMessage(),
            'temperature' => 0.45,
            'max_tokens' => 1000
        ]);
        
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
     * Varsayılan system mesajı (test vb.)
     */
    private function getDefaultSystemMessage() {
        return 'Sen profesyonel bir emlak danışmanısın. Türkçe, profesyonel ve ikna edici metinler yazarsın. Sadece verilen bilgilere dayan, uydurma yapma.';
    }

    /**
     * İlan açıklaması için system mesajı – net kurallar, saçmalamayı önler
     */
    private function getListingSystemMessage() {
        return "Sen Türkçe yazan, yalnızca verilen ilan bilgilerini kullanan profesyonel bir emlak metin yazarısın.\n\nKURALLAR (kesin uyulacak):\n- Sadece kullanıcının verdiği başlık, konum, fiyat, emlak tipi, oda detayları ve alan bilgilerini kullan; bu bilgilerin dışına çıkma.\n- Hiçbir özellik, sayı veya konum uydurma (örn. verilmeyen manzara, ulaşım, okul adı ekleme).\n- Türkçe yaz; İngilizce veya başka dil kullanma.\n- Emoji, asterisk, gereksiz başlık veya 'İlan açıklaması:' gibi etiket ekleme.\n- Çıktında yalnızca açıklama paragrafları olsun; ek açıklama veya not yazma.";
    }

    /**
     * Parsel seslendirme metni için system mesajı
     */
    private function getParselSystemMessage() {
        return "Sen Türkçe yazan, yalnızca verilen parsel bilgilerini kullanan profesyonel bir emlak seslendirme metni yazarısın.\n\nKURALLAR (kesin uyulacak):\n- Sadece kullanıcının verdiği konum, ada, parsel, alan, nitelik ve yakın lokasyonlar listesini kullan; bu bilgilerin dışına çıkma.\n- Hiçbir yer, tesis veya özellik uydurma; listede olmayan bir lokasyonu metne ekleme.\n- Türkçe yaz; İngilizce veya başka dil kullanma.\n- Emoji veya gereksiz başlık ekleme.\n- Çıktında yalnızca seslendirme metni olsun.";
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
        
        $prompt = "Aşağıdaki ilan bilgilerine **sadece ve sadece** bu maddelere dayanarak bir ilan açıklaması yaz. Verilmeyen hiçbir bilgi ekleme (okul, hastane, ulaşım, manzara vb. yazma; sadece aşağıdakileri kullan).

İlan Bilgileri (sadece bunları kullan):
- Başlık: " . ($data['title'] ?? 'İlan başlığı belirtilmemiş') . "
- Konum: " . ($data['location'] ?? 'Konum belirtilmemiş') . "
- Fiyat: {$price} ({$listingStatus})
- Emlak Tipi: {$propertyType}
- Oda Detayları: {$roomDetails}
- Alan: {$area}

Yapılacaklar:
- Türkçe, profesyonel ve satış odaklı bir metin yaz.
- Yukarıdaki bilgileri doğal cümlelerle vurgula; anahtar kelimeleri akıcı kullan (SEO).
- 200–400 kelime arasında, paragraflar halinde yaz.
- Sonunda kısa bir iletişime geçin çağrısı ekleyebilirsin.

Yasak:
- Başlık, 'Açıklama:', 'İlan metni:' gibi etiket yazma.
- Yukarıda olmayan özellik, sayı veya konum uydurma.
- Emoji veya yıldız kullanma.
Çıktı: Sadece açıklama paragraflarını yaz.";

        return $prompt;
    }
    
    /**
     * Groq API'ye çağrı yap
     * @param string $prompt Kullanıcı prompt'u
     * @param array $options ['system' => string, 'temperature' => float, 'max_tokens' => int]
     */
    private function callGroqAPI($prompt, array $options = []) {
        $ch = curl_init($this->apiUrl);

        $systemContent = $options['system'] ?? $this->getDefaultSystemMessage();
        $temperature = isset($options['temperature']) ? (float) $options['temperature'] : 0.45;
        $maxTokens = isset($options['max_tokens']) ? (int) $options['max_tokens'] : 1000;

        $payload = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemContent
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => $temperature,
            'max_tokens' => $maxTokens
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
        $response = $this->callGroqAPI($prompt, [
            'system' => $this->getParselSystemMessage(),
            'temperature' => 0.4,
            'max_tokens' => 1024
        ]);
        if (!$response['success']) return $response;
        $desc = trim($response['description']);
        if (mb_strlen($desc) > 800) {
            $desc = mb_substr($desc, 0, 800);
        }
        $minChars = 700;
        if (mb_strlen($desc) < $minChars) {
            return [
                'success' => false,
                'error' => 'Yapay zeka metni en az ' . $minChars . ' karakter olmalı; şu an ' . mb_strlen($desc) . ' karakter üretildi. Lütfen tekrar deneyin veya parsel bilgilerini (yakın lokasyonlar vb.) zenginleştirin.'
            ];
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
            $yakınLokStr = "\n- Yakın Lokasyonlar (kullanıcının belirttiği bilgiler – metne MUTLAKA dahil et):\n  " . implode("\n  ", array_map(function ($x) { return '• ' . trim($x); }, $yakınLok));
        }
        $gereksinimYakin = !empty($yakınLok) && is_array($yakınLok)
            ? " Yakın lokasyonlar bölümünde kullanıcının belirttiği HER BİR bilgiyi metne mutlaka ekle; konum avantajı olarak doğal ve akıcı cümlelerle (mesafe/özellik vurgulayarak) seslendirme metnine dahil et. Bu maddeler atlanmamalıdır."
            : "";
        return "Aşağıdaki parsel bilgilerine **sadece ve sadece** bu maddelere dayanarak drone videoda kullanılacak bir seslendirme metni yaz. Verilmeyen lokasyon, tesis veya özellik ekleme; sadece aşağıdaki bilgileri kullan.

Parsel Bilgileri (sadece bunları kullan):
- Konum: {$konumStr}
- Ada: {$ada}
- Parsel: {$parselNo}
- Alan: {$alan}
- Nitelik: {$nitelik}{$yakınLokStr}

Yapılacaklar:
- Türkçe, en az 700 karakter, en fazla 800 karakter yaz; metin mutlaka 700 karakteri geçsin.
- Ada ve parsel numaralarını yazıyla yaz; alanı metrekare olarak yazıyla ifade et; nitelik kelimesini metne dahil et.
- Yatırımcıyı cezbeden, akıcı bir seslendirme metni yaz; sonunda kısa iletişime geçin çağrısı olabilir.
- Sadece seslendirme metnini yaz; başlık veya etiket ekleme.{$gereksinimYakin}

Yasak:
- Yukarıda listelenmeyen hiçbir yer, tesis veya özellik uydurma.
- Emoji veya gereksiz başlık ekleme.
Çıktı: Sadece seslendirme metnini yaz.";
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
