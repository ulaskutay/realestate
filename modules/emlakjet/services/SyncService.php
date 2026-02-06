<?php
/**
 * Sync Service
 * İlan senkronizasyon servisi
 */

class SyncService {
    private $apiService;
    private $mapper;
    private $emlakjetListingModel;
    private $db;
    
    public function __construct($apiService, $mapper, $emlakjetListingModel) {
        $this->apiService = $apiService;
        $this->mapper = $mapper;
        $this->emlakjetListingModel = $emlakjetListingModel;
        $this->db = Database::getInstance();
    }
    
    /**
     * İlanı Emlakjet'e gönder (push)
     */
    public function syncListingToEmlakjet($listingId) {
        try {
            // İlanı getir
            $listing = $this->getListing($listingId);
            if (!$listing) {
                throw new Exception("İlan bulunamadı: {$listingId}");
            }
            
            // Sadece yayınlanmış ilanları senkronize et
            if ($listing['status'] !== 'published') {
                $this->emlakjetListingModel->updateSyncStatus(
                    $listingId,
                    'pending',
                    null,
                    'İlan yayınlanmamış',
                    null
                );
                return [
                    'success' => false,
                    'error' => 'İlan yayınlanmamış'
                ];
            }
            
            // Emlakjet formatına dönüştür
            $emlakjetData = $this->mapper->toEmlakjetFormat($listing);
            
            // Mevcut senkronizasyon kaydını kontrol et
            $syncRecord = $this->emlakjetListingModel->findByListingId($listingId);
            
            if ($syncRecord && !empty($syncRecord['emlakjet_id'])) {
                // Güncelleme
                $response = $this->apiService->updateListing($syncRecord['emlakjet_id'], $emlakjetData);
            } else {
                // Yeni oluşturma
                $response = $this->apiService->createListing($emlakjetData);
            }
            
            // Sonucu işle
            if (isset($response['success']) && $response['success'] === true) {
                $emlakjetId = $response['data']['id'] ?? $response['id'] ?? null;
                
                if ($syncRecord && !empty($syncRecord['emlakjet_id'])) {
                    $emlakjetId = $syncRecord['emlakjet_id'];
                }
                
                // Senkronizasyon durumunu güncelle
                $this->emlakjetListingModel->updateSyncStatus(
                    $listingId,
                    'synced',
                    $emlakjetId,
                    null,
                    $emlakjetData
                );
                
                return [
                    'success' => true,
                    'emlakjet_id' => $emlakjetId,
                    'message' => 'İlan başarıyla senkronize edildi'
                ];
            } else {
                $error = $response['error'] ?? 'Bilinmeyen hata';
                
                // Hata durumunu kaydet
                $this->emlakjetListingModel->updateSyncStatus(
                    $listingId,
                    'failed',
                    $syncRecord['emlakjet_id'] ?? null,
                    $error,
                    null
                );
                
                return [
                    'success' => false,
                    'error' => $error
                ];
            }
            
        } catch (Exception $e) {
            // Hata durumunu kaydet
            $this->emlakjetListingModel->updateSyncStatus(
                $listingId,
                'failed',
                null,
                $e->getMessage(),
                null
            );
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * İlanı Emlakjet'ten çek (pull)
     */
    public function syncListingFromEmlakjet($emlakjetId) {
        try {
            // Emlakjet'ten ilanı getir
            $response = $this->apiService->getListing($emlakjetId);
            
            if (!isset($response['success']) || $response['success'] !== true) {
                $error = $response['error'] ?? 'İlan bulunamadı';
                throw new Exception($error);
            }
            
            $emlakjetData = $response['data'] ?? $response;
            
            // Sistem formatına dönüştür
            $listingData = $this->mapper->fromEmlakjetFormat($emlakjetData);
            
            // Mevcut ilanı kontrol et
            $syncRecord = $this->emlakjetListingModel->findByEmlakjetId($emlakjetId);
            
            require_once dirname(dirname(dirname(__DIR__))) . '/themes/realestate/modules/realestate-listings/Model.php';
            $listingModel = new RealEstateListingsModel();
            
            if ($syncRecord && !empty($syncRecord['listing_id'])) {
                // Mevcut ilanı güncelle
                $listingId = $syncRecord['listing_id'];
                $listingModel->update($listingId, $listingData);
            } else {
                // Yeni ilan oluştur
                $listingId = $listingModel->create($listingData);
                
                // Senkronizasyon kaydı oluştur
                $this->emlakjetListingModel->updateSyncStatus(
                    $listingId,
                    'synced',
                    $emlakjetId,
                    null,
                    $emlakjetData
                );
            }
            
            return [
                'success' => true,
                'listing_id' => $listingId,
                'message' => 'İlan başarıyla çekildi'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Bekleyen tüm ilanları senkronize et
     */
    public function syncAllPending() {
        $pendingListings = $this->emlakjetListingModel->getPendingListings(100);
        
        $synced = 0;
        $failed = 0;
        $errors = [];
        
        foreach ($pendingListings as $syncRecord) {
            // listing_id'yi doğru alandan al
            // getPendingListings metodunda l.id as listing_id olarak döndürülüyor
            $listingId = $syncRecord['listing_id'] ?? $syncRecord['ej_listing_id'] ?? $syncRecord['id'] ?? null;
            
            if (!$listingId) {
                $failed++;
                $errors[] = [
                    'listing_id' => null,
                    'error' => 'Listing ID bulunamadı',
                    'record' => $syncRecord
                ];
                continue;
            }
            
            try {
                $result = $this->syncListingToEmlakjet($listingId);
                
                if ($result['success']) {
                    $synced++;
                } else {
                    $failed++;
                    $errors[] = [
                        'listing_id' => $listingId,
                        'error' => $result['error'] ?? 'Bilinmeyen hata'
                    ];
                }
            } catch (Exception $e) {
                $failed++;
                $errors[] = [
                    'listing_id' => $listingId,
                    'error' => $e->getMessage()
                ];
            }
            
            // Rate limiting için kısa bekleme
            usleep(500000); // 0.5 saniye
        }
        
        return [
            'synced' => $synced,
            'failed' => $failed,
            'total' => count($pendingListings),
            'errors' => $errors
        ];
    }
    
    /**
     * Emlakjet'ten ilan sil
     */
    public function deleteListingFromEmlakjet($emlakjetId, $listingId = null) {
        try {
            $response = $this->apiService->deleteListing($emlakjetId);
            
            if (isset($response['success']) && $response['success'] === true) {
                // Senkronizasyon durumunu güncelle
                if ($listingId) {
                    $this->emlakjetListingModel->updateSyncStatus(
                        $listingId,
                        'deleted',
                        $emlakjetId,
                        null,
                        null
                    );
                }
                
                return [
                    'success' => true,
                    'message' => 'İlan Emlakjet\'ten silindi'
                ];
            } else {
                $error = $response['error'] ?? 'Bilinmeyen hata';
                
                if ($listingId) {
                    $this->emlakjetListingModel->updateSyncStatus(
                        $listingId,
                        'failed',
                        $emlakjetId,
                        $error,
                        null
                    );
                }
                
                return [
                    'success' => false,
                    'error' => $error
                ];
            }
            
        } catch (Exception $e) {
            if ($listingId) {
                $this->emlakjetListingModel->updateSyncStatus(
                    $listingId,
                    'failed',
                    $emlakjetId,
                    $e->getMessage(),
                    null
                );
            }
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Emlakjet'ten silinen ilanı işle
     */
    public function handleEmlakjetDeletion($emlakjetId) {
        $syncRecord = $this->emlakjetListingModel->findByEmlakjetId($emlakjetId);
        
        if ($syncRecord) {
            // Senkronizasyon durumunu güncelle
            $this->emlakjetListingModel->updateSyncStatus(
                $syncRecord['listing_id'],
                'deleted',
                $emlakjetId,
                'Emlakjet\'ten silindi',
                null
            );
        }
    }
    
    /**
     * Çakışma yönetimi
     */
    public function handleConflict($listingId, $emlakjetData) {
        // Çakışma durumunda hangi verinin öncelikli olacağını belirle
        // Şimdilik sistem verisi öncelikli (push yapılır)
        
        $syncRecord = $this->emlakjetListingModel->findByListingId($listingId);
        
        if ($syncRecord && !empty($syncRecord['emlakjet_id'])) {
            // Sistem verisi ile Emlakjet'i güncelle
            return $this->syncListingToEmlakjet($listingId);
        }
        
        return [
            'success' => false,
            'error' => 'Çakışma çözülemedi'
        ];
    }
    
    /**
     * İlan getir
     */
    private function getListing($listingId) {
        require_once dirname(dirname(dirname(__DIR__))) . '/themes/realestate/modules/realestate-listings/Model.php';
        $listingModel = new RealEstateListingsModel();
        return $listingModel->find($listingId);
    }
}
