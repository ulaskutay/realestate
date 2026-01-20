<?php
/**
 * Mock Emlakjet API Service
 * Test modu için mock API servisi
 */

class MockEmlakjetApiService {
    private $mockListings = [];
    private $nextId = 1;
    private $logFile;
    
    public function __construct() {
        // Log dosyası yolu
        $logDir = dirname(dirname(dirname(__DIR__))) . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $this->logFile = $logDir . '/emlakjet-mock.log';
    }
    
    /**
     * API bağlantısını doğrula (her zaman başarılı)
     */
    public function authenticate() {
        $this->log("Mock: Authentication successful");
        return true;
    }
    
    /**
     * Yeni ilan oluştur
     */
    public function createListing($listingData) {
        $id = 'mock_' . $this->nextId++;
        $listing = [
            'id' => $id,
            'title' => $listingData['title'] ?? '',
            'description' => $listingData['description'] ?? '',
            'price' => $listingData['price'] ?? 0,
            'property_type' => $listingData['property_type'] ?? 'konut',
            'listing_type' => $listingData['listing_type'] ?? 'satilik',
            'location' => $listingData['location'] ?? [],
            'area' => $listingData['area'] ?? 0,
            'bedrooms' => $listingData['bedrooms'] ?? 0,
            'bathrooms' => $listingData['bathrooms'] ?? 0,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->mockListings[$id] = $listing;
        $this->log("Mock: Listing created - ID: {$id}, Title: " . ($listingData['title'] ?? 'N/A'));
        
        return [
            'success' => true,
            'data' => $listing,
            'id' => $id
        ];
    }
    
    /**
     * İlan güncelle
     */
    public function updateListing($emlakjetId, $listingData) {
        if (!isset($this->mockListings[$emlakjetId])) {
            $this->log("Mock: Listing not found - ID: {$emlakjetId}");
            return [
                'success' => false,
                'error' => 'İlan bulunamadı',
                'http_code' => 404
            ];
        }
        
        $listing = $this->mockListings[$emlakjetId];
        $listing['title'] = $listingData['title'] ?? $listing['title'];
        $listing['description'] = $listingData['description'] ?? $listing['description'];
        $listing['price'] = $listingData['price'] ?? $listing['price'];
        $listing['property_type'] = $listingData['property_type'] ?? $listing['property_type'];
        $listing['listing_type'] = $listingData['listing_type'] ?? $listing['listing_type'];
        $listing['location'] = $listingData['location'] ?? $listing['location'];
        $listing['area'] = $listingData['area'] ?? $listing['area'];
        $listing['bedrooms'] = $listingData['bedrooms'] ?? $listing['bedrooms'];
        $listing['bathrooms'] = $listingData['bathrooms'] ?? $listing['bathrooms'];
        $listing['updated_at'] = date('Y-m-d H:i:s');
        
        $this->mockListings[$emlakjetId] = $listing;
        $this->log("Mock: Listing updated - ID: {$emlakjetId}");
        
        return [
            'success' => true,
            'data' => $listing
        ];
    }
    
    /**
     * İlan sil
     */
    public function deleteListing($emlakjetId) {
        if (!isset($this->mockListings[$emlakjetId])) {
            $this->log("Mock: Listing not found for deletion - ID: {$emlakjetId}");
            return [
                'success' => false,
                'error' => 'İlan bulunamadı',
                'http_code' => 404
            ];
        }
        
        unset($this->mockListings[$emlakjetId]);
        $this->log("Mock: Listing deleted - ID: {$emlakjetId}");
        
        return [
            'success' => true,
            'message' => 'İlan silindi'
        ];
    }
    
    /**
     * İlanları getir
     */
    public function getListings($filters = []) {
        $listings = array_values($this->mockListings);
        
        // Basit filtreleme
        if (!empty($filters['status'])) {
            $listings = array_filter($listings, function($listing) use ($filters) {
                return $listing['status'] === $filters['status'];
            });
        }
        
        $this->log("Mock: Listings retrieved - Count: " . count($listings));
        
        return [
            'success' => true,
            'data' => $listings,
            'total' => count($listings)
        ];
    }
    
    /**
     * Tek ilan detayı
     */
    public function getListing($emlakjetId) {
        if (!isset($this->mockListings[$emlakjetId])) {
            $this->log("Mock: Listing not found - ID: {$emlakjetId}");
            return [
                'success' => false,
                'error' => 'İlan bulunamadı',
                'http_code' => 404
            ];
        }
        
        $this->log("Mock: Listing retrieved - ID: {$emlakjetId}");
        
        return [
            'success' => true,
            'data' => $this->mockListings[$emlakjetId]
        ];
    }
    
    /**
     * Log yaz
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";
        @file_put_contents($this->logFile, $logMessage, FILE_APPEND);
        error_log("Emlakjet Mock API: {$message}");
    }
    
    /**
     * Mock verileri temizle (test için)
     */
    public function clearMockData() {
        $this->mockListings = [];
        $this->nextId = 1;
        $this->log("Mock: All mock data cleared");
    }
}
