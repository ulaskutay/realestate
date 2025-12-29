<?php
/**
 * AgreementVersion Model - Sözleşme Versiyon Yönetimi
 * Sözleşme değişiklik geçmişini takip eder
 */

class AgreementVersion extends Model {
    protected $table = 'agreement_versions';
    
    /**
     * Belirli bir sözleşmenin tüm versiyonlarını getirir
     */
    public function getByAgreement($agreementId, $orderBy = 'version_number DESC') {
        $sql = "SELECT v.*, u.username as author_name
                FROM `{$this->table}` v
                LEFT JOIN `users` u ON v.author_id = u.id
                WHERE v.agreement_id = ?
                ORDER BY {$orderBy}";
        return $this->db->fetchAll($sql, [$agreementId]);
    }
    
    /**
     * Detaylı versiyon bilgisi getirir
     */
    public function findWithDetails($id) {
        $sql = "SELECT v.*, 
                       u.username as author_name,
                       a.title as current_title,
                       a.slug as agreement_slug,
                       a.type as agreement_type
                FROM `{$this->table}` v
                LEFT JOIN `users` u ON v.author_id = u.id
                LEFT JOIN `agreements` a ON v.agreement_id = a.id
                WHERE v.id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Belirli bir versiyon numarasını getirir
     */
    public function findByVersionNumber($agreementId, $versionNumber) {
        $sql = "SELECT v.*, u.username as author_name
                FROM `{$this->table}` v
                LEFT JOIN `users` u ON v.author_id = u.id
                WHERE v.agreement_id = ? AND v.version_number = ?";
        return $this->db->fetch($sql, [$agreementId, $versionNumber]);
    }
    
    /**
     * Sözleşmenin versiyon sayısını getirir
     */
    public function getVersionCount($agreementId) {
        $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `agreement_id` = ?";
        $result = $this->db->fetch($sql, [$agreementId]);
        return $result['count'] ?? 0;
    }
    
    /**
     * Sözleşmenin son versiyonunu getirir
     */
    public function getLatestVersion($agreementId) {
        $sql = "SELECT v.*, u.username as author_name
                FROM `{$this->table}` v
                LEFT JOIN `users` u ON v.author_id = u.id
                WHERE v.agreement_id = ?
                ORDER BY v.version_number DESC
                LIMIT 1";
        return $this->db->fetch($sql, [$agreementId]);
    }
    
    /**
     * Yeni versiyon oluşturur
     */
    public function createVersion($data) {
        return $this->create($data);
    }
    
    /**
     * Bir sözleşmenin tüm versiyonlarını siler
     */
    public function deleteByAgreement($agreementId) {
        $sql = "DELETE FROM `{$this->table}` WHERE `agreement_id` = ?";
        return $this->db->query($sql, [$agreementId]);
    }
    
    /**
     * İki versiyon arasındaki farkları karşılaştırma için veri hazırlar
     */
    public function compareVersions($versionId1, $versionId2) {
        $v1 = $this->find($versionId1);
        $v2 = $this->find($versionId2);
        
        if (!$v1 || !$v2) {
            return null;
        }
        
        return [
            'version1' => $v1,
            'version2' => $v2,
            'title_changed' => $v1['title'] !== $v2['title'],
            'content_changed' => $v1['content'] !== $v2['content']
        ];
    }
}

