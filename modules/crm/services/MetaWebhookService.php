<?php
/**
 * Meta Webhook Service
 * Meta Lead Ads webhook işleme servisi
 */

class MetaWebhookService {
    private $settings;
    private $db;
    private $leadModel;
    
    public function __construct($settings) {
        $this->settings = $settings;
        $this->db = Database::getInstance();
        require_once __DIR__ . '/../models/CrmModel.php';
        $this->leadModel = new CrmModel();
    }
    
    /**
     * Webhook'u işle
     */
    public function handleWebhook() {
        // GET isteği - webhook doğrulama
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->verifyWebhook();
            return;
        }
        
        // POST isteği - lead verisi
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processLead();
            return;
        }
        
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }
    
    /**
     * Webhook doğrulama (Meta'nın ilk isteği)
     */
    private function verifyWebhook() {
        $mode = $_GET['hub_mode'] ?? '';
        $token = $_GET['hub_verify_token'] ?? '';
        $challenge = $_GET['hub_challenge'] ?? '';
        
        $verifyToken = $this->settings['meta_webhook_verify_token'] ?? '';
        
        if ($mode === 'subscribe' && $token === $verifyToken) {
            http_response_code(200);
            echo $challenge;
            exit;
        }
        
        http_response_code(403);
        echo json_encode(['error' => 'Verification failed']);
        exit;
    }
    
    /**
     * Lead verisini işle
     */
    private function processLead() {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            exit;
        }
        
        // Webhook signature doğrulama
        if (!$this->verifySignature($input)) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid signature']);
            exit;
        }
        
        // Entry'leri işle
        if (isset($data['entry'])) {
            foreach ($data['entry'] as $entry) {
                if (isset($entry['changes'])) {
                    foreach ($entry['changes'] as $change) {
                        if ($change['field'] === 'leadgen') {
                            $this->processLeadGen($change['value']);
                        }
                    }
                }
            }
        }
        
        http_response_code(200);
        echo json_encode(['success' => true]);
        exit;
    }
    
    /**
     * Lead gen verisini işle
     */
    private function processLeadGen($leadData) {
        $leadId = $leadData['leadgen_id'] ?? null;
        $pageId = $leadData['page_id'] ?? null;
        $formId = $leadData['form_id'] ?? null;
        $createdTime = $leadData['created_time'] ?? date('Y-m-d H:i:s');
        
        // Zaten var mı kontrol et
        if ($leadId) {
            $existing = $this->leadModel->findByMetaLeadId($leadId);
            if ($existing) {
                // Zaten kayıtlı
                return;
            }
        }
        
        // Lead field'larını parse et
        $fieldData = [];
        if (isset($leadData['field_data'])) {
            foreach ($leadData['field_data'] as $field) {
                $fieldData[$field['name']] = $field['values'][0] ?? '';
            }
        }
        
        // Lead verisini hazırla
        $lead = [
            'name' => $this->extractName($fieldData),
            'phone' => $this->extractPhone($fieldData),
            'email' => $this->extractEmail($fieldData),
            'property_type' => $this->extractPropertyType($fieldData),
            'location' => $this->extractLocation($fieldData),
            'budget' => $this->extractBudget($fieldData),
            'room_count' => $this->extractRoomCount($fieldData),
            'source' => 'meta',
            'status' => 'new',
            'meta_lead_id' => $leadId,
            'notes' => json_encode($fieldData, JSON_UNESCAPED_UNICODE)
        ];
        
        // Lead'i kaydet
        $this->leadModel->create($lead);
    }
    
    /**
     * Field data'dan isim çıkar
     */
    private function extractName($fieldData) {
        $nameFields = ['first_name', 'last_name', 'full_name', 'name'];
        $firstName = '';
        $lastName = '';
        
        foreach ($nameFields as $field) {
            if (isset($fieldData[$field])) {
                if ($field === 'first_name') {
                    $firstName = $fieldData[$field];
                } elseif ($field === 'last_name') {
                    $lastName = $fieldData[$field];
                } elseif ($field === 'full_name' || $field === 'name') {
                    return $fieldData[$field];
                }
            }
        }
        
        return trim($firstName . ' ' . $lastName);
    }
    
    /**
     * Field data'dan telefon çıkar
     */
    private function extractPhone($fieldData) {
        $phoneFields = ['phone_number', 'phone', 'mobile', 'telefon'];
        
        foreach ($phoneFields as $field) {
            if (isset($fieldData[$field]) && !empty($fieldData[$field])) {
                return $fieldData[$field];
            }
        }
        
        return null;
    }
    
    /**
     * Field data'dan e-posta çıkar
     */
    private function extractEmail($fieldData) {
        $emailFields = ['email', 'e_mail', 'e-posta'];
        
        foreach ($emailFields as $field) {
            if (isset($fieldData[$field]) && !empty($fieldData[$field])) {
                return $fieldData[$field];
            }
        }
        
        return null;
    }
    
    /**
     * Field data'dan emlak tipi çıkar
     */
    private function extractPropertyType($fieldData) {
        $typeFields = ['property_type', 'emlak_tipi', 'type', 'tip'];
        
        foreach ($typeFields as $field) {
            if (isset($fieldData[$field]) && !empty($fieldData[$field])) {
                return $fieldData[$field];
            }
        }
        
        return null;
    }
    
    /**
     * Field data'dan lokasyon çıkar
     */
    private function extractLocation($fieldData) {
        $locationFields = ['location', 'lokasyon', 'city', 'ilce', 'district'];
        
        foreach ($locationFields as $field) {
            if (isset($fieldData[$field]) && !empty($fieldData[$field])) {
                return $fieldData[$field];
            }
        }
        
        return null;
    }
    
    /**
     * Field data'dan bütçe çıkar
     */
    private function extractBudget($fieldData) {
        $budgetFields = ['budget', 'butce', 'price', 'fiyat'];
        
        foreach ($budgetFields as $field) {
            if (isset($fieldData[$field]) && !empty($fieldData[$field])) {
                return $fieldData[$field];
            }
        }
        
        return null;
    }
    
    /**
     * Field data'dan oda sayısı çıkar
     */
    private function extractRoomCount($fieldData) {
        $roomFields = ['room_count', 'oda_sayisi', 'rooms', 'bedrooms'];
        
        foreach ($roomFields as $field) {
            if (isset($fieldData[$field]) && !empty($fieldData[$field])) {
                return $fieldData[$field];
            }
        }
        
        return null;
    }
    
    /**
     * Webhook signature doğrulama
     */
    private function verifySignature($payload) {
        $secret = $this->settings['meta_webhook_secret'] ?? '';
        
        if (empty($secret)) {
            // Secret yoksa doğrulama yapma (geliştirme için)
            return true;
        }
        
        $signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
        
        if (empty($signature)) {
            return false;
        }
        
        // Signature format: sha256=hash
        $signature = str_replace('sha256=', '', $signature);
        
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        
        return hash_equals($expectedSignature, $signature);
    }
}
