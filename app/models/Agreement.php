<?php
/**
 * Agreement Model - Sözleşme Yönetimi
 * Gizlilik Politikası, KVKK, Kullanım Şartları, Çerez Politikası vb.
 */

class Agreement extends Model {
    protected $table = 'agreements';
    
    /**
     * Sözleşme türleri
     */
    public static $types = [
        'privacy' => 'Gizlilik Politikası',
        'kvkk' => 'KVKK Aydınlatma Metni',
        'terms' => 'Kullanım Şartları',
        'cookies' => 'Çerez Politikası',
        'other' => 'Diğer'
    ];
    
    /**
     * Tüm sözleşmeleri getirir (yazar bilgisi ile)
     */
    public function getAll($orderBy = 'updated_at DESC') {
        $sql = "SELECT a.*, u.username as author_name
                FROM `{$this->table}` a
                LEFT JOIN `users` u ON a.author_id = u.id
                ORDER BY {$orderBy}";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Yayınlanmış sözleşmeleri getirir
     */
    public function getPublished() {
        $sql = "SELECT a.*, u.username as author_name
                FROM `{$this->table}` a
                LEFT JOIN `users` u ON a.author_id = u.id
                WHERE a.status = 'published'
                ORDER BY a.type ASC, a.title ASC";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Türe göre sözleşmeleri getirir
     */
    public function getByType($type) {
        $sql = "SELECT a.*, u.username as author_name
                FROM `{$this->table}` a
                LEFT JOIN `users` u ON a.author_id = u.id
                WHERE a.type = ?
                ORDER BY a.updated_at DESC";
        return $this->db->fetchAll($sql, [$type]);
    }
    
    /**
     * Slug'a göre sözleşme getirir
     */
    public function findBySlug($slug) {
        $sql = "SELECT a.*, u.username as author_name
                FROM `{$this->table}` a
                LEFT JOIN `users` u ON a.author_id = u.id
                WHERE a.slug = ?";
        return $this->db->fetch($sql, [$slug]);
    }
    
    /**
     * Yayınlanmış sözleşmeyi slug ile getirir
     */
    public function findPublishedBySlug($slug) {
        $sql = "SELECT a.*, u.username as author_name
                FROM `{$this->table}` a
                LEFT JOIN `users` u ON a.author_id = u.id
                WHERE a.slug = ? AND a.status = 'published'";
        return $this->db->fetch($sql, [$slug]);
    }
    
    /**
     * Türe göre yayınlanmış sözleşmeyi getirir (her türden en güncel olan)
     */
    public function findPublishedByType($type) {
        $sql = "SELECT a.*, u.username as author_name
                FROM `{$this->table}` a
                LEFT JOIN `users` u ON a.author_id = u.id
                WHERE a.type = ? AND a.status = 'published'
                ORDER BY a.updated_at DESC
                LIMIT 1";
        return $this->db->fetch($sql, [$type]);
    }
    
    /**
     * ID'ye göre detaylı sözleşme getirir
     */
    public function findWithDetails($id) {
        $sql = "SELECT a.*, u.username as author_name
                FROM `{$this->table}` a
                LEFT JOIN `users` u ON a.author_id = u.id
                WHERE a.id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Sözleşme oluşturur
     */
    public function createAgreement($data) {
        // Slug oluştur
        if (empty($data['slug'])) {
            $data['slug'] = $this->createSlug($data['title']);
        }
        
        // Yayın tarihi
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        
        // Versiyon
        $data['version'] = 1;
        
        return $this->create($data);
    }
    
    /**
     * Sözleşme günceller ve versiyon oluşturur
     */
    public function updateAgreement($id, $data, $changeNote = null, $authorId = null) {
        // Mevcut sözleşmeyi al
        $current = $this->find($id);
        
        if (!$current) {
            return false;
        }
        
        // Slug güncelle
        if (isset($data['title']) && empty($data['slug'])) {
            $data['slug'] = $this->createSlug($data['title'], $id);
        }
        
        // İçerik değiştiyse versiyon oluştur
        $contentChanged = isset($data['content']) && $data['content'] !== $current['content'];
        $titleChanged = isset($data['title']) && $data['title'] !== $current['title'];
        
        if ($contentChanged || $titleChanged) {
            // Önceki versiyonu kaydet
            $this->createVersion($id, $current, $changeNote, $authorId);
            
            // Versiyon numarasını artır
            $data['version'] = ($current['version'] ?? 1) + 1;
        }
        
        // Yayınlandıysa ve tarih yoksa ekle
        if (isset($data['status']) && $data['status'] === 'published') {
            if (empty($current['published_at']) && empty($data['published_at'])) {
                $data['published_at'] = date('Y-m-d H:i:s');
            }
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Versiyon oluşturur
     */
    private function createVersion($agreementId, $currentData, $changeNote = null, $authorId = null) {
        $sql = "INSERT INTO `agreement_versions` 
                (`agreement_id`, `version_number`, `title`, `content`, `change_note`, `author_id`, `created_at`)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        return $this->db->query($sql, [
            $agreementId,
            $currentData['version'] ?? 1,
            $currentData['title'],
            $currentData['content'],
            $changeNote,
            $authorId
        ]);
    }
    
    /**
     * Sözleşmenin versiyonlarını getirir
     */
    public function getVersions($agreementId) {
        $sql = "SELECT v.*, u.username as author_name
                FROM `agreement_versions` v
                LEFT JOIN `users` u ON v.author_id = u.id
                WHERE v.agreement_id = ?
                ORDER BY v.version_number DESC";
        return $this->db->fetchAll($sql, [$agreementId]);
    }
    
    /**
     * Belirli bir versiyonu getirir
     */
    public function getVersion($versionId) {
        $sql = "SELECT v.*, u.username as author_name, a.title as current_title
                FROM `agreement_versions` v
                LEFT JOIN `users` u ON v.author_id = u.id
                LEFT JOIN `agreements` a ON v.agreement_id = a.id
                WHERE v.id = ?";
        return $this->db->fetch($sql, [$versionId]);
    }
    
    /**
     * Eski versiyona geri döner
     */
    public function restoreVersion($versionId, $authorId = null) {
        // Versiyonu al
        $version = $this->getVersion($versionId);
        
        if (!$version) {
            return false;
        }
        
        // Mevcut sözleşmeyi al
        $current = $this->find($version['agreement_id']);
        
        if (!$current) {
            return false;
        }
        
        // Mevcut durumu versiyon olarak kaydet
        $this->createVersion(
            $version['agreement_id'],
            $current,
            'Versiyon ' . $version['version_number'] . ' geri yüklendi',
            $authorId
        );
        
        // Eski versiyonun içeriğini geri yükle
        $newVersion = ($current['version'] ?? 1) + 1;
        
        return $this->update($version['agreement_id'], [
            'title' => $version['title'],
            'content' => $version['content'],
            'version' => $newVersion
        ]);
    }
    
    /**
     * Duruma göre sayı getirir
     */
    public function getCountByStatus($status = null) {
        if ($status) {
            $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `status` = ?";
            $result = $this->db->fetch($sql, [$status]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM `{$this->table}`";
            $result = $this->db->fetch($sql);
        }
        return $result['count'] ?? 0;
    }
    
    /**
     * Türe göre sayı getirir
     */
    public function getCountByType($type) {
        $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `type` = ?";
        $result = $this->db->fetch($sql, [$type]);
        return $result['count'] ?? 0;
    }
    
    /**
     * Benzersiz slug oluşturur
     */
    private function createSlug($title, $excludeId = null) {
        $slug = $this->slugify($title);
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Slug var mı kontrol eder
     */
    private function slugExists($slug, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `slug` = ? AND `id` != ?";
            $result = $this->db->fetch($sql, [$slug, $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM `{$this->table}` WHERE `slug` = ?";
            $result = $this->db->fetch($sql, [$slug]);
        }
        
        return ($result['count'] ?? 0) > 0;
    }
    
    /**
     * Metni slug'a çevirir
     */
    private function slugify($text) {
        // Türkçe karakterleri dönüştür
        $tr = ['ş', 'Ş', 'ı', 'İ', 'ğ', 'Ğ', 'ü', 'Ü', 'ö', 'Ö', 'ç', 'Ç'];
        $en = ['s', 's', 'i', 'i', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c'];
        $text = str_replace($tr, $en, $text);
        
        // Küçük harfe çevir
        $text = mb_strtolower($text, 'UTF-8');
        
        // Alfanumerik olmayan karakterleri tire ile değiştir
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        
        // Baş ve sondaki tireleri kaldır
        $text = trim($text, '-');
        
        // Maksimum uzunluk
        if (strlen($text) > 200) {
            $text = substr($text, 0, 200);
            $text = rtrim($text, '-');
        }
        
        return $text;
    }
    
    /**
     * Sözleşme türü etiketini döndürür
     */
    public static function getTypeLabel($type) {
        return self::$types[$type] ?? 'Bilinmiyor';
    }
    
    /**
     * Sözleşme türüne göre varsayılan şablon döndürür (başlık + HTML içerik).
     * Yer tutucular ([ŞİRKET ADI], [E-POSTA] vb.) şablonlarda kullanılır.
     */
    public static function getDefaultTemplate($type) {
        $templates = [
            'privacy' => [
                'title' => 'Gizlilik Politikası',
                'content' => '<h1>Gizlilik Politikası</h1>
<p>Son güncelleme: [TARİH]</p>
<p>[ŞİRKET ADI] ("şirket", "biz") olarak kişisel verilerinizin güvenliğine önem veriyoruz. Bu gizlilik politikası, [WEB SİTESİ] üzerinden sunulan hizmetlerimiz kapsamında toplanan bilgilerin nasıl kullanıldığını açıklamaktadır.</p>
<h2>1. Toplanan Bilgiler</h2>
<p>Hizmetlerimizi kullanırken ad, e-posta adresi, telefon numarası ve adres gibi kişisel bilgilerinizi toplayabiliriz.</p>
<h2>2. Bilgilerin Kullanımı</h2>
<p>Topladığımız bilgiler hizmet sunumu, iletişim, yasal yükümlülükler ve iyileştirme amaçlı kullanılır.</p>
<h2>3. Bilgi Güvenliği</h2>
<p>Verilerinizi yetkisiz erişime karşı korumak için uygun teknik ve idari önlemleri alıyoruz.</p>
<h2>4. İletişim</h2>
<p>Sorularınız için: [E-POSTA] | [TELEFON] | [ADRES]</p>'
            ],
            'kvkk' => [
                'title' => 'KVKK Aydınlatma Metni',
                'content' => '<h1>KVKK Aydınlatma Metni</h1>
<p>6698 sayılı Kişisel Verilerin Korunması Kanunu ("KVKK") kapsamında, [ŞİRKET ADI] ("Veri Sorumlusu") olarak kişisel verilerinizin işlenmesine ilişkin aydınlatma yükümlülüğümüzü yerine getirmekteyiz.</p>
<h2>1. Veri Sorumlusu</h2>
<p>Şirket: [ŞİRKET ADI]<br>Adres: [ADRES]<br>E-posta: [E-POSTA]<br>Telefon: [TELEFON]</p>
<h2>2. İşlenen Kişisel Veriler</h2>
<p>Kimlik, iletişim, müşteri işlem ve finans bilgileri, hukuki işlem bilgisi ve fiziksel mekân güvenliği verileri işlenebilmektedir.</p>
<h2>3. İşleme Amaçları</h2>
<p>Hizmet sunumu, sözleşme süreçleri, yasal yükümlülükler ve meşru menfaat kapsamında işlenmektedir.</p>
<h2>4. Haklarınız</h2>
<p>KVKK md. 11 uyarınca erişim, düzeltme, silme, itiraz ve şikâyet başvurusu haklarınızı [E-POSTA] veya [KEP ADRESİ] üzerinden kullanabilirsiniz.</p>'
            ],
            'terms' => [
                'title' => 'Kullanım Şartları',
                'content' => '<h1>Kullanım Şartları</h1>
<p>Son güncelleme: [TARİH]</p>
<p>[WEB SİTESİ] ("Site") ve [ŞİRKET ADI] ("şirket") tarafından sunulan hizmetlere erişim ve kullanım, aşağıdaki şartlara tabidir.</p>
<h2>1. Genel</h2>
<p>Siteyi kullanarak bu şartları kabul etmiş sayılırsınız. Şartları kabul etmiyorsanız lütfen siteyi kullanmayınız.</p>
<h2>2. Hizmetler</h2>
<p>Şirket, emlak danışmanlığı ve ilgili hizmetleri sunar. Hizmet kapsamı önceden bildirilmeksizin güncellenebilir.</p>
<h2>3. Kullanıcı Yükümlülükleri</h2>
<p>Doğru ve güncel bilgi vermek, yasalara uymak ve siteyi kötüye kullanmamakla yükümlüsünüz.</p>
<h2>4. Fikri Mülkiyet</h2>
<p>Sitedeki içerik, logo ve materyaller şirkete aittir; izinsiz kullanılamaz.</p>
<h2>5. İletişim</h2>
<p>İletişim: [E-POSTA] | [TELEFON] | [ADRES]</p>'
            ],
            'cookies' => [
                'title' => 'Çerez Politikası',
                'content' => '<h1>Çerez Politikası</h1>
<p>Son güncelleme: [TARİH]</p>
<p>[ŞİRKET ADI] olarak [WEB SİTESİ] ("Site") üzerinde çerez ve benzeri teknolojilerin kullanımına ilişkin bilgilendirme yapmaktayız.</p>
<h2>1. Çerez Nedir?</h2>
<p>Çerezler, cihazınıza kaydedilen küçük metin dosyalarıdır; tercihlerinizi hatırlamak ve site deneyimini iyileştirmek için kullanılır.</p>
<h2>2. Kullandığımız Çerezler</h2>
<p><strong>Zorunlu:</strong> Site işlevi için gerekli çerezler.<br>
<strong>Analitik:</strong> Kullanım istatistikleri (anonim).<br>
<strong>İşlevsel:</strong> Dil veya tercih kayıtları.</p>
<h2>3. Yönetim</h2>
<p>Tarayıcı ayarlarınızdan çerezleri silebilir veya engelleyebilirsiniz; bu durumda bazı özellikler çalışmayabilir.</p>
<h2>4. İletişim</h2>
<p>Sorularınız için: [E-POSTA] | [TELEFON]</p>'
            ],
            'other' => [
                'title' => 'Sözleşme',
                'content' => '<h1>Sözleşme Başlığı</h1>
<p>Bu sözleşme [TARİH] tarihinde [ŞİRKET ADI] ile taraflar arasında düzenlenmiştir.</p>
<p>İletişim: [E-POSTA] | [TELEFON] | [ADRES]</p>'
            ]
        ];
        
        $t = $templates[$type] ?? $templates['other'];
        return ['title' => $t['title'], 'content' => $t['content']];
    }
    
    /**
     * Sözleşmeyi siler (soft delete yerine hard delete)
     */
    public function deleteAgreement($id) {
        // Önce versiyonları sil (foreign key cascade ile otomatik silinir)
        return $this->delete($id);
    }
}

