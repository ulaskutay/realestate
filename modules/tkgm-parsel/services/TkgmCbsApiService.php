<?php
/**
 * TKGM CBS API Service
 * CBS (cbs.tkgm.gov.tr / cbsapi.tkgm.gov.tr) API ile il, ilçe, mahalle, ada, parsel sorgulama.
 * Endpoint'ler resmi dokümana göre güncellenebilir.
 */

class TkgmCbsApiService {

    private $baseUrl;
    private $timeout;
    private $cacheTtl;
    private $cacheDir;

    /** İller listesi (API yanıt vermezse kullanılır) */
    private static $ILLER_STATIC = [
        ['kodu' => '01', 'adi' => 'Adana'], ['kodu' => '02', 'adi' => 'Adıyaman'], ['kodu' => '03', 'adi' => 'Afyonkarahisar'],
        ['kodu' => '04', 'adi' => 'Ağrı'], ['kodu' => '05', 'adi' => 'Amasya'], ['kodu' => '06', 'adi' => 'Ankara'],
        ['kodu' => '07', 'adi' => 'Antalya'], ['kodu' => '08', 'adi' => 'Artvin'], ['kodu' => '09', 'adi' => 'Aydın'],
        ['kodu' => '10', 'adi' => 'Balıkesir'], ['kodu' => '11', 'adi' => 'Bilecik'], ['kodu' => '12', 'adi' => 'Bingöl'],
        ['kodu' => '13', 'adi' => 'Bitlis'], ['kodu' => '14', 'adi' => 'Bolu'], ['kodu' => '15', 'adi' => 'Burdur'],
        ['kodu' => '16', 'adi' => 'Bursa'], ['kodu' => '17', 'adi' => 'Çanakkale'], ['kodu' => '18', 'adi' => 'Çankırı'],
        ['kodu' => '19', 'adi' => 'Çorum'], ['kodu' => '20', 'adi' => 'Denizli'], ['kodu' => '21', 'adi' => 'Diyarbakır'],
        ['kodu' => '22', 'adi' => 'Edirne'], ['kodu' => '23', 'adi' => 'Elazığ'], ['kodu' => '24', 'adi' => 'Erzincan'],
        ['kodu' => '25', 'adi' => 'Erzurum'], ['kodu' => '26', 'adi' => 'Eskişehir'], ['kodu' => '27', 'adi' => 'Gaziantep'],
        ['kodu' => '28', 'adi' => 'Giresun'], ['kodu' => '29', 'adi' => 'Gümüşhane'], ['kodu' => '30', 'adi' => 'Hakkari'],
        ['kodu' => '31', 'adi' => 'Hatay'], ['kodu' => '32', 'adi' => 'Isparta'], ['kodu' => '33', 'adi' => 'Mersin'],
        ['kodu' => '34', 'adi' => 'İstanbul'], ['kodu' => '35', 'adi' => 'İzmir'], ['kodu' => '36', 'adi' => 'Kars'],
        ['kodu' => '37', 'adi' => 'Kastamonu'], ['kodu' => '38', 'adi' => 'Kayseri'], ['kodu' => '39', 'adi' => 'Kırklareli'],
        ['kodu' => '40', 'adi' => 'Kırşehir'], ['kodu' => '41', 'adi' => 'Kocaeli'], ['kodu' => '42', 'adi' => 'Konya'],
        ['kodu' => '43', 'adi' => 'Kütahya'], ['kodu' => '44', 'adi' => 'Malatya'], ['kodu' => '45', 'adi' => 'Manisa'],
        ['kodu' => '46', 'adi' => 'Kahramanmaraş'], ['kodu' => '47', 'adi' => 'Mardin'], ['kodu' => '48', 'adi' => 'Muğla'],
        ['kodu' => '49', 'adi' => 'Muş'], ['kodu' => '50', 'adi' => 'Nevşehir'], ['kodu' => '51', 'adi' => 'Niğde'],
        ['kodu' => '52', 'adi' => 'Ordu'], ['kodu' => '53', 'adi' => 'Rize'], ['kodu' => '54', 'adi' => 'Sakarya'],
        ['kodu' => '55', 'adi' => 'Samsun'], ['kodu' => '56', 'adi' => 'Siirt'], ['kodu' => '57', 'adi' => 'Sinop'],
        ['kodu' => '58', 'adi' => 'Sivas'], ['kodu' => '59', 'adi' => 'Tekirdağ'], ['kodu' => '60', 'adi' => 'Tokat'],
        ['kodu' => '61', 'adi' => 'Trabzon'], ['kodu' => '62', 'adi' => 'Tunceli'], ['kodu' => '63', 'adi' => 'Şanlıurfa'],
        ['kodu' => '64', 'adi' => 'Uşak'], ['kodu' => '65', 'adi' => 'Van'], ['kodu' => '66', 'adi' => 'Yozgat'],
        ['kodu' => '67', 'adi' => 'Zonguldak'], ['kodu' => '68', 'adi' => 'Aksaray'], ['kodu' => '69', 'adi' => 'Bayburt'],
        ['kodu' => '70', 'adi' => 'Karaman'], ['kodu' => '71', 'adi' => 'Kırıkkale'], ['kodu' => '72', 'adi' => 'Batman'],
        ['kodu' => '73', 'adi' => 'Şırnak'], ['kodu' => '74', 'adi' => 'Bartın'], ['kodu' => '75', 'adi' => 'Ardahan'],
        ['kodu' => '76', 'adi' => 'Iğdır'], ['kodu' => '77', 'adi' => 'Yalova'], ['kodu' => '78', 'adi' => 'Karabük'],
        ['kodu' => '79', 'adi' => 'Kilis'], ['kodu' => '80', 'adi' => 'Osmaniye'], ['kodu' => '81', 'adi' => 'Düzce'],
    ];

    /** ArcGIS MapServer Layer 0 URL (parsel sorgusu - parselsorgu benzeri) */
    private $arcgisLayerUrl;

    /** İl/ilçe/mahalle listesi için kullanılacak base URL (boşsa baseUrl kullanılır) */
    private $hierarchyBaseUrl;

    /** idariYapi endpoint'leri (burakaktna/tkgmservice: ilListe, ilceListe/{id}, mahalleListe/{id}, parsel/{nid}/{ada}/{parsel}) */
    private $idariYapiBaseUrl;

    public function __construct(array $settings = []) {
        $this->baseUrl = rtrim($settings['api_base_url'] ?? 'https://cbsapi.tkgm.gov.tr/megsiswebapi.v3.1', '/');
        $this->arcgisLayerUrl = trim($settings['arcgis_parsel_layer_url'] ?? '');
        $h = trim($settings['hierarchy_api_base_url'] ?? '');
        $this->hierarchyBaseUrl = $h !== '' ? rtrim($h, '/') : $this->baseUrl;
        $idari = trim($settings['idariyapi_base_url'] ?? '');
        $this->idariYapiBaseUrl = $idari !== '' ? rtrim($idari, '/') : 'https://cbsservis.tkgm.gov.tr/megsiswebapi.v3/api';
        $this->timeout = (int)($settings['api_timeout'] ?? 15);
        $this->cacheTtl = (int)($settings['cache_ttl'] ?? 60); // dakika
        $cacheDir = dirname(dirname(dirname(__DIR__))) . '/storage/cache/tkgm-parsel';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }
        $this->cacheDir = $cacheDir;
    }

    /**
     * İl listesi
     * Önce idariYapi/ilListe (cbsservis - burakaktna/tkgmservice), yoksa Iller veya statik.
     * @return array [['kodu' => '...', 'adi' => '...'], ...] — idariYapi kullanılıyorsa kodu = il id (parsel zinciri için)
     */
    public function getIller(): array {
        $cacheKey = 'iller';
        $cached = $this->getCache($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        $list = $this->getIdariYapiIller();
        if (!empty($list)) {
            $this->setCache($cacheKey, $list);
            return $list;
        }
        $url = $this->baseUrl . '/Iller';
        $data = $this->request($url);
        if (is_array($data) && !empty($data)) {
            $list = $this->normalizeIller($data);
            if (!empty($list)) {
                $this->setCache($cacheKey, $list);
                return $list;
            }
        }
        $this->setCache($cacheKey, self::$ILLER_STATIC);
        return self::$ILLER_STATIC;
    }

    /** Turkiye API (ilçe/mahalle yedek kaynak) */
    private const TURKIYE_API_BASE = 'https://api.turkiyeapi.dev/v1';

    /**
     * İlçe listesi
     * Önce idariYapi/ilceListe/{provinceId}, yoksa Ilceler veya Turkiye API.
     * @param string $ilKodu İl kodu veya idariYapi il id
     * @return array [['kodu' => '...', 'adi' => '...'], ...]
     */
    public function getIlceler(string $ilKodu): array {
        if ($ilKodu === '') {
            return [];
        }
        $cacheKey = 'ilceler_' . $ilKodu;
        $cached = $this->getCache($cacheKey);
        if ($cached !== null && !empty($cached)) {
            return $cached;
        }
        $list = $this->getIdariYapiIlceler($ilKodu);
        if (!empty($list)) {
            $this->setCache($cacheKey, $list);
            return $list;
        }
        $url = $this->baseUrl . '/Ilceler?ilKodu=' . rawurlencode($ilKodu);
        $data = $this->request($url);
        $list = $this->normalizeIlceler($data);
        if (empty($list)) {
            $list = $this->getIlcelerFromTurkiyeApi($ilKodu);
        }
        $this->setCache($cacheKey, $list);
        return $list;
    }

    /**
     * Mahalle/Köy listesi
     * Önce idariYapi/mahalleListe/{districtId}, yoksa Koyler veya Turkiye API.
     * @param string $ilKodu İl id (idariYapi) veya il kodu
     * @param string $ilceKodu İlçe id (idariYapi) veya ilçe kodu
     * @return array [['kodu' => '...', 'adi' => '...'], ...] — idariYapi kullanılıyorsa kodu = mahalle id (parsel için)
     */
    public function getMahalleler(string $ilKodu, string $ilceKodu): array {
        if ($ilKodu === '' || $ilceKodu === '') {
            return [];
        }
        $cacheKey = 'mahalleler_' . $ilKodu . '_' . $ilceKodu;
        $cached = $this->getCache($cacheKey);
        if ($cached !== null && !empty($cached)) {
            return $cached;
        }
        $list = $this->getIdariYapiMahalleler($ilceKodu);
        if (!empty($list)) {
            $this->setCache($cacheKey, $list);
            return $list;
        }
        $url = $this->baseUrl . '/Koyler?ilKodu=' . rawurlencode($ilKodu) . '&ilceKodu=' . rawurlencode($ilceKodu);
        $data = $this->request($url);
        $list = $this->normalizeMahalleler($data);
        if (empty($list)) {
            $list = $this->getMahallelerFromTurkiyeApi($ilceKodu);
        }
        $this->setCache($cacheKey, $list);
        return $list;
    }

    /**
     * Ada listesi
     * @param string $ilKodu
     * @param string $ilceKodu
     * @param string $mahalleKodu Köy/Mahalle kodu
     * @return array [['kodu' => '...', 'adi' => '...'], ...]
     */
    public function getAdalar(string $ilKodu, string $ilceKodu, string $mahalleKodu): array {
        if ($ilKodu === '' || $ilceKodu === '' || $mahalleKodu === '') {
            return [];
        }
        $cacheKey = 'adalar_' . $ilKodu . '_' . $ilceKodu . '_' . $mahalleKodu;
        $cached = $this->getCache($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        $url = $this->baseUrl . '/Adalar?ilKodu=' . rawurlencode($ilKodu) . '&ilceKodu=' . rawurlencode($ilceKodu) . '&koyKodu=' . rawurlencode($mahalleKodu);
        $data = $this->request($url);
        $list = $this->normalizeAdalar($data);
        $this->setCache($cacheKey, $list);
        return $list;
    }

    /**
     * Parsel listesi (bir ada içindeki parseller)
     * @param string $ilKodu
     * @param string $ilceKodu
     * @param string $mahalleKodu
     * @param string $ada
     * @return array [['kodu' => '...', 'adi' => '...'], ...]
     */
    public function getParseller(string $ilKodu, string $ilceKodu, string $mahalleKodu, string $ada): array {
        if ($ilKodu === '' || $ilceKodu === '' || $mahalleKodu === '' || $ada === '') {
            return [];
        }
        $cacheKey = 'parseller_' . $ilKodu . '_' . $ilceKodu . '_' . $mahalleKodu . '_' . $ada;
        $cached = $this->getCache($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        $url = $this->baseUrl . '/Parseller?ilKodu=' . rawurlencode($ilKodu) . '&ilceKodu=' . rawurlencode($ilceKodu) . '&koyKodu=' . rawurlencode($mahalleKodu) . '&ada=' . rawurlencode($ada);
        $data = $this->request($url);
        $list = $this->normalizeParseller($data);
        $this->setCache($cacheKey, $list);
        return $list;
    }

    /**
     * idariYapi (cbsservis - burakaktna/tkgmservice): il listesi
     * Path: idariYapi/ilListe
     */
    private function getIdariYapiIller(): array {
        $data = $this->request($this->idariYapiBaseUrl . '/idariYapi/ilListe');
        return $this->normalizeIdariYapiList($data, 'il', 'id', 'ad', 'adi', 'name');
    }

    /**
     * idariYapi: ilçe listesi (provinceId = il id)
     * Path: idariYapi/ilceListe/{provinceId}
     */
    private function getIdariYapiIlceler(string $provinceId): array {
        $data = $this->request($this->idariYapiBaseUrl . '/idariYapi/ilceListe/' . rawurlencode($provinceId));
        return $this->normalizeIdariYapiList($data, 'ilce', 'id', 'ad', 'adi', 'name');
    }

    /**
     * idariYapi: mahalle listesi (districtId = ilçe id)
     * Path: idariYapi/mahalleListe/{districtId}
     */
    private function getIdariYapiMahalleler(string $districtId): array {
        $data = $this->request($this->idariYapiBaseUrl . '/idariYapi/mahalleListe/' . rawurlencode($districtId));
        return $this->normalizeIdariYapiList($data, 'mahalle', 'id', 'ad', 'adi', 'name');
    }

    /**
     * idariYapi yanıtını [['kodu' => id, 'adi' => ad], ...] formatına çevir.
     * Yanıt object veya array olabilir; data/result/root anahtarları veya doğrudan dizi.
     */
    private function normalizeIdariYapiList(?array $data, string $itemKey, string $idKey, string ...$adKeys): array {
        if (!is_array($data)) {
            return [];
        }
        $raw = $data['data'] ?? $data['result'] ?? $data['ilListe'] ?? $data['ilceListe'] ?? $data['mahalleListe'] ?? $data[$itemKey] ?? $data;
        if (is_array($raw) && isset($raw[0]) && is_array($raw[0])) {
            $items = $raw;
        } elseif (is_array($raw)) {
            $items = [$raw];
        } else {
            return [];
        }
        $out = [];
        foreach ($items as $row) {
            $id = $row[$idKey] ?? $row['kodu'] ?? '';
            $ad = '';
            foreach ($adKeys as $k) {
                if (isset($row[$k]) && (string)$row[$k] !== '') {
                    $ad = (string)$row[$k];
                    break;
                }
            }
            if ((string)$id !== '' || $ad !== '') {
                $out[] = ['kodu' => (string)$id, 'adi' => $ad];
            }
        }
        return $out;
    }

    /**
     * idariYapi parsel sorgusu: parsel/{neighborhoodId}/{ada}/{parsel}
     */
    private function getParselDetayFromIdariYapi(string $mahalleKodu, string $ada, string $parsel): ?array {
        $url = $this->idariYapiBaseUrl . '/parsel/' . rawurlencode($mahalleKodu) . '/' . rawurlencode($ada) . '/' . rawurlencode($parsel);
        $data = $this->request($url);
        return $this->normalizeParselDetayFromIdariYapiResponse($data, $ada, $parsel);
    }

    /**
     * idariYapi parsel yanıtı — GeoJSON Feature, FeatureCollection veya düz property listesi; PascalCase destekli
     */
    private function normalizeParselDetayFromIdariYapiResponse(?array $data, string $ada, string $parsel): ?array {
        if (!is_array($data)) {
            return null;
        }
        $feature = null;
        if (($data['type'] ?? '') === 'FeatureCollection' && !empty($data['features']) && is_array($data['features'][0])) {
            $feature = $data['features'][0];
        } elseif (($data['type'] ?? '') === 'Feature' && is_array($data['properties'] ?? null)) {
            $feature = $data;
        }
        $p = $feature ? ($feature['properties'] ?? null) : $data;
        if (!is_array($p)) {
            return null;
        }
        $alan = $p['Alan'] ?? $p['alan'] ?? $p['TAPUALANI'] ?? $p['tapualani'] ?? null;
        if (is_string($alan)) {
            $alan = trim(preg_replace('/\s/u', '', $alan));
            $alan = str_replace('.', '', $alan);
            $alan = str_replace(',', '.', $alan);
            $alan = is_numeric($alan) ? (float)$alan : null;
        }
        $out = [
            'tasinmaz_no' => $p['tasinmazNo'] ?? $p['tasinmaz_no'] ?? '',
            'parsel_no' => $p['ParselNo'] ?? $p['parselNo'] ?? $parsel,
            'ada' => $p['Ada'] ?? $p['adaNo'] ?? $ada,
            'alan_m2' => $alan,
            'nitelik' => $p['Nitelik'] ?? $p['nitelik'] ?? '',
            'il_adi' => $p['Il'] ?? $p['ilAd'] ?? $p['il_adi'] ?? '',
            'ilce_adi' => $p['Ilce'] ?? $p['ilceAd'] ?? $p['ilce_adi'] ?? '',
            'mahalle_adi' => $p['Mahalle'] ?? $p['mahalleAd'] ?? $p['mahalle_adi'] ?? '',
            'raw' => $p
        ];
        if ($feature && !empty($feature['geometry'])) {
            $out['geometry'] = $feature['geometry'];
            $out['geojson'] = isset($data['features']) ? $data : ['type' => 'Feature', 'geometry' => $feature['geometry'], 'properties' => $p];
        }
        return $out;
    }

    /**
     * Parsel detay (taşınmaz no, alan, nitelik vb.)
     * @param string $ilKodu
     * @param string $ilceKodu
     * @param string $mahalleKodu
     * @param string $ada
     * @param string $parsel
     * @return array|null ['tasinmaz_no' => '...', 'parsel_no' => '...', 'alan_m2' => ..., 'nitelik' => '...', 'il_adi' => '...', ...] veya null
     */
    public function getParselDetay(string $ilKodu, string $ilceKodu, string $mahalleKodu, string $ada, string $parsel): ?array {
        if ($ilKodu === '' || $ilceKodu === '' || $mahalleKodu === '' || $ada === '' || $parsel === '') {
            return null;
        }
        // 0) idariYapi (burakaktna/tkgmservice): parsel/{neighborhoodId}/{ada}/{parsel} — mahalle listesi bu API'den gelmişse mahalleKodu = neighborhoodId
        $detay = $this->getParselDetayFromIdariYapi($mahalleKodu, $ada, $parsel);
        if ($detay !== null) {
            return $detay;
        }
        // 1) Parselsorgu'nun kullandığı gerçek endpoint: GET /api/parsel/{mahalleId}/{ada}/{parsel} (GeoJSON Feature)
        $mahalleId = $this->resolveCbsMahalleId($ilKodu, $ilceKodu, $mahalleKodu);
        if ($mahalleId !== null) {
            $url = $this->baseUrl . '/api/parsel/' . rawurlencode((string)$mahalleId) . '/' . rawurlencode($ada) . '/' . rawurlencode($parsel);
            $data = $this->request($url);
            $detay = $this->normalizeParselDetayFromGeoJSON($data, $ada, $parsel);
            if ($detay !== null) {
                return $detay;
            }
            $urlIdari = $this->idariYapiBaseUrl . '/parsel/' . rawurlencode((string)$mahalleId) . '/' . rawurlencode($ada) . '/' . rawurlencode($parsel);
            $data = $this->request($urlIdari);
            $detay = $this->normalizeParselDetayFromIdariYapiResponse($data, $ada, $parsel);
            if ($detay !== null) {
                return $detay;
            }
        }
        // 2) Önce mahalleKodu'yu doğrudan mahalleId gibi dene (bazı kaynaklarda aynı id kullanılabiliyor)
        $url = $this->baseUrl . '/api/parsel/' . rawurlencode($mahalleKodu) . '/' . rawurlencode($ada) . '/' . rawurlencode($parsel);
        $data = $this->request($url);
        $detay = $this->normalizeParselDetayFromGeoJSON($data, $ada, $parsel);
        if ($detay !== null) {
            return $detay;
        }
        // 3) Eski query-string formatı
        $url = $this->baseUrl . '/ParselDetay?ilKodu=' . rawurlencode($ilKodu) . '&ilceKodu=' . rawurlencode($ilceKodu)
            . '&koyKodu=' . rawurlencode($mahalleKodu) . '&ada=' . rawurlencode($ada) . '&parsel=' . rawurlencode($parsel);
        $data = $this->request($url);
        $detay = $this->normalizeParselDetay($data, $ilKodu, $ilceKodu, $mahalleKodu, $ada, $parsel);
        if ($detay !== null) {
            return $detay;
        }
        // 4) ArcGIS yedek
        if ($this->arcgisLayerUrl !== '') {
            $detay = $this->getParselDetayFromArcGIS($ilKodu, $ilceKodu, $mahalleKodu, $ada, $parsel);
            if ($detay !== null) {
                return $detay;
            }
        }
        return null;
    }

    /**
     * CBS API'den mahalleId çözümle.
     * 1) CBS list endpoint'leri (/Ilceler, /Koyler) ile isim eşlemesi (il/ilçe/mahalle adı -> CBS id).
     * 2) Yoksa /api/il zinciri denenir (parselsorgu tarzı).
     */
    private function resolveCbsMahalleId(string $ilKodu, string $ilceKodu, string $mahalleKodu): ?string {
        $ilceAdi = $this->resolveIlceAdi($ilKodu, $ilceKodu);
        $mahalleAdi = $this->resolveMahalleAdi($ilKodu, $ilceKodu, $mahalleKodu);

        // 0) idariYapi (cbsservis): il listesi -> ilceListe(ilId) -> mahalleListe(ilceId); isim/kod eşlemesi ile mahalle id
        $idariMahalleId = $this->resolveMahalleIdViaIdariYapi($ilKodu, $ilceKodu, $ilceAdi, $mahalleKodu, $mahalleAdi);
        if ($idariMahalleId !== null) {
            return $idariMahalleId;
        }

        if ($ilceAdi === '' || $mahalleAdi === '') {
            return null;
        }

        // 1) CBS /Ilceler ve /Koyler ile çöz (aynı baseUrl, ilKodu ile; CBS ilçe id'si ile Koyler çağrılır)
        $cbsIlceId = $this->resolveCbsIlceIdFromList($ilKodu, $ilceAdi, $ilceKodu);
        if ($cbsIlceId !== null) {
            $cbsMahalleId = $this->resolveCbsMahalleIdFromKoyler($ilKodu, $cbsIlceId, $mahalleAdi, $mahalleKodu);
            if ($cbsMahalleId !== null) {
                return $cbsMahalleId;
            }
        }

        // 2) /api/il zinciri (parselsorgu tarzı - endpoint mevcutsa)
        $ilAdi = '';
        foreach (self::$ILLER_STATIC as $i) {
            if (($i['kodu'] ?? '') === $ilKodu) {
                $ilAdi = $i['adi'] ?? '';
                break;
            }
        }
        $iller = $this->request($this->baseUrl . '/api/il');
        $list = null;
        if (is_array($iller)) {
            $list = isset($iller['data']) ? $iller['data'] : (isset($iller[0]) && is_array($iller[0]) ? $iller : null);
        }
        if (!is_array($list) || empty($list)) {
            $ilId = $ilKodu;
        } else {
            $ilId = null;
            foreach ($list as $i) {
                $ad = $i['ad'] ?? $i['adi'] ?? $i['name'] ?? '';
                if ($ad === $ilAdi || (string)($i['id'] ?? $i['kodu'] ?? '') === $ilKodu) {
                    $ilId = (string)($i['id'] ?? $i['kodu'] ?? $ilKodu);
                    break;
                }
            }
            if ($ilId === null) {
                return null;
            }
        }
        $ilceler = $this->request($this->baseUrl . '/api/il/' . rawurlencode((string)$ilId) . '/ilce');
        if (!is_array($ilceler)) {
            return null;
        }
        $list = isset($ilceler['data']) ? $ilceler['data'] : (is_array($ilceler[0] ?? null) ? $ilceler : [$ilceler]);
        $ilceId = null;
        foreach ($list as $i) {
            $ad = $i['ad'] ?? $i['adi'] ?? $i['name'] ?? '';
            if ($ad === $ilceAdi || (string)($i['id'] ?? $i['kodu'] ?? '') === $ilceKodu) {
                $ilceId = (string)($i['id'] ?? $i['kodu'] ?? '');
                break;
            }
        }
        if ($ilceId === null) {
            return null;
        }
        $mahalleler = $this->request($this->baseUrl . '/api/ilce/' . rawurlencode($ilceId) . '/mahalle');
        if (!is_array($mahalleler)) {
            return null;
        }
        $list = isset($mahalleler['data']) ? $mahalleler['data'] : (is_array($mahalleler[0] ?? null) ? $mahalleler : [$mahalleler]);
        foreach ($list as $m) {
            $ad = $m['ad'] ?? $m['adi'] ?? $m['name'] ?? '';
            if ($ad === $mahalleAdi || (string)($m['id'] ?? $m['kodu'] ?? '') === $mahalleKodu) {
                return (string)($m['id'] ?? $m['kodu'] ?? '');
            }
        }
        return null;
    }

    /**
     * idariYapi ile mahalleId çözümle: il listesi -> ilceListe(ilId) -> mahalleListe(ilceId); isim/kod eşlemesi
     */
    private function resolveMahalleIdViaIdariYapi(string $ilKodu, string $ilceKodu, string $ilceAdi, string $mahalleKodu, string $mahalleAdi): ?string {
        $iller = $this->getIdariYapiIller();
        if (empty($iller)) {
            return null;
        }
        $ilAdi = '';
        foreach (self::$ILLER_STATIC as $i) {
            if (($i['kodu'] ?? '') === $ilKodu) {
                $ilAdi = $i['adi'] ?? '';
                break;
            }
        }
        $provinceId = null;
        foreach ($iller as $i) {
            if (($i['kodu'] ?? '') === $ilKodu || $this->normalizePlaceName($i['adi'] ?? '') === $this->normalizePlaceName($ilAdi)) {
                $provinceId = $i['kodu'] ?? null;
                break;
            }
        }
        if ($provinceId === null) {
            return null;
        }
        $ilceler = $this->getIdariYapiIlceler($provinceId);
        if (empty($ilceler)) {
            return null;
        }
        $ilceAdiNorm = $this->normalizePlaceName($ilceAdi);
        $districtId = null;
        foreach ($ilceler as $i) {
            if (($i['kodu'] ?? '') === $ilceKodu || $this->normalizePlaceName($i['adi'] ?? '') === $ilceAdiNorm) {
                $districtId = $i['kodu'] ?? null;
                break;
            }
        }
        if ($districtId === null) {
            return null;
        }
        $mahalleler = $this->getIdariYapiMahalleler($districtId);
        $mahalleAdiNorm = $this->normalizePlaceName($mahalleAdi);
        foreach ($mahalleler as $m) {
            if (($m['kodu'] ?? '') === $mahalleKodu || $this->normalizePlaceName($m['adi'] ?? '') === $mahalleAdiNorm) {
                return $m['kodu'] ?? null;
            }
        }
        return null;
    }

    /**
     * CBS /Ilceler yanıtından ilçe adı veya kodu ile CBS ilçe id'sini bul.
     * Birden fazla URL/parametre varyantı dener (ilKodu, ilNo, ilId; 048 gibi başında sıfır).
     */
    private function resolveCbsIlceIdFromList(string $ilKodu, string $ilceAdi, string $ilceKodu): ?string {
        $ilVariants = [$ilKodu];
        if (strlen($ilKodu) === 1) {
            $ilVariants[] = '0' . $ilKodu;
        } elseif (strlen($ilKodu) === 2 && $ilKodu[0] !== '0') {
            $ilVariants[] = '0' . $ilKodu;
        }
        $base = $this->hierarchyBaseUrl;
        $paramNames = ['ilKodu', 'ilNo', 'ilId', 'il'];
        foreach ($ilVariants as $il) {
            foreach ($paramNames as $param) {
                $url = $base . '/Ilceler?' . $param . '=' . rawurlencode($il);
                $items = $this->extractListFromResponse($this->request($url));
                if ($items !== null) {
                    $found = $this->findIlceInList($items, $ilceAdi, $ilceKodu);
                    if ($found !== null) {
                        return $found;
                    }
                }
            }
        }
        return null;
    }

    /**
     * CBS /Koyler yanıtından mahalle adı/kodu ile api/parsel için mahalleId bul.
     * Birden fazla parametre varyantı dener; ilceKodu olarak hem CBS id hem Turkiye id (1331) denenir.
     */
    private function resolveCbsMahalleIdFromKoyler(string $ilKodu, string $cbsIlceKodu, string $mahalleAdi, string $mahalleKodu): ?string {
        $ilVariants = [$ilKodu];
        if (strlen($ilKodu) === 2 && $ilKodu[0] !== '0') {
            $ilVariants[] = '0' . $ilKodu;
        }
        $queryParams = [
            ['ilKodu' => $ilKodu, 'ilceKodu' => $cbsIlceKodu],
            ['ilNo' => $ilKodu, 'ilceNo' => $cbsIlceKodu],
            ['ilId' => $ilKodu, 'ilceId' => $cbsIlceKodu],
        ];
        foreach ($ilVariants as $il) {
            foreach ($queryParams as $params) {
                $parts = [];
                foreach ($params as $k => $v) {
                    $parts[] = $k . '=' . rawurlencode($v);
                }
                $url = $this->hierarchyBaseUrl . '/Koyler?' . implode('&', $parts);
                $items = $this->extractListFromResponse($this->request($url));
                if ($items !== null) {
                    $found = $this->findMahalleInList($items, $mahalleAdi, $mahalleKodu);
                    if ($found !== null) {
                        return $found;
                    }
                }
            }
        }
        return null;
    }

    private function extractListFromResponse(?array $data): ?array {
        if (!is_array($data)) {
            return null;
        }
        if (isset($data['data']) && is_array($data['data'])) {
            return $data['data'];
        }
        if (isset($data['ilceler']) && is_array($data['ilceler'])) {
            return $data['ilceler'];
        }
        if (isset($data['koyler']) && is_array($data['koyler'])) {
            return $data['koyler'];
        }
        if (isset($data['result']) && is_array($data['result'])) {
            return $data['result'];
        }
        if (isset($data[0]) && is_array($data[0])) {
            return $data;
        }
        return null;
    }

    private function findIlceInList(array $items, string $ilceAdi, string $ilceKodu): ?string {
        $ilceAdiNorm = $this->normalizePlaceName($ilceAdi);
        foreach ($items as $row) {
            $ad = $row['adi'] ?? $row['ilceAdi'] ?? $row['name'] ?? '';
            $kodu = (string)($row['kodu'] ?? $row['ilceKodu'] ?? $row['id'] ?? '');
            if ($kodu !== '' && ($this->normalizePlaceName($ad) === $ilceAdiNorm || $kodu === $ilceKodu)) {
                return $kodu;
            }
        }
        return null;
    }

    private function findMahalleInList(array $items, string $mahalleAdi, string $mahalleKodu): ?string {
        $mahalleAdiNorm = $this->normalizePlaceName($mahalleAdi);
        foreach ($items as $row) {
            $ad = $row['adi'] ?? $row['koyAdi'] ?? $row['name'] ?? '';
            $id = (string)($row['id'] ?? $row['kodu'] ?? $row['koyKodu'] ?? $row['mahalleId'] ?? $row['TKGMMAHALLEKODU'] ?? '');
            if ($id !== '' && ($this->normalizePlaceName($ad) === $mahalleAdiNorm || $id === $mahalleKodu)) {
                return $id;
            }
        }
        return null;
    }

    private function normalizePlaceName(string $s): string {
        $s = trim(mb_strtolower($s, 'UTF-8'));
        $s = preg_replace('/\s+/u', ' ', $s);
        return $s;
    }

    /**
     * Parselsorgu yanıtı: GeoJSON Feature veya FeatureCollection (geometry ile haritada gösterim için saklanır)
     * Properties: PascalCase (Il, Ilce, Mahalle, ParselNo, Ada, Alan) veya camelCase (ilAd, parselNo, alan)
     */
    private function normalizeParselDetayFromGeoJSON(?array $data, string $ada, string $parsel): ?array {
        if (!is_array($data)) {
            return null;
        }
        $feature = null;
        if (($data['type'] ?? '') === 'FeatureCollection' && !empty($data['features']) && is_array($data['features'][0])) {
            $feature = $data['features'][0];
        } elseif (($data['type'] ?? '') === 'Feature') {
            $feature = $data;
        }
        if ($feature === null) {
            return null;
        }
        $p = $feature['properties'] ?? null;
        if (!is_array($p)) {
            return null;
        }
        $alan = $p['Alan'] ?? $p['alan'] ?? $p['TAPUALANI'] ?? null;
        if (is_string($alan)) {
            $alan = trim(preg_replace('/\s/u', '', $alan));
            $alan = str_replace('.', '', $alan);
            $alan = str_replace(',', '.', $alan);
            if ($alan === '' || !is_numeric($alan)) {
                $alan = null;
            } else {
                $alan = (float) $alan;
            }
        }
        $out = [
            'tasinmaz_no' => $p['tasinmazNo'] ?? $p['tasinmaz_no'] ?? '',
            'parsel_no' => $p['ParselNo'] ?? $p['parselNo'] ?? $parsel,
            'ada' => $p['Ada'] ?? $p['adaNo'] ?? $ada,
            'alan_m2' => $alan,
            'nitelik' => $p['Nitelik'] ?? $p['nitelik'] ?? '',
            'il_adi' => $p['Il'] ?? $p['ilAd'] ?? $p['il_adi'] ?? '',
            'ilce_adi' => $p['Ilce'] ?? $p['ilceAd'] ?? $p['ilce_adi'] ?? '',
            'mahalle_adi' => $p['Mahalle'] ?? $p['mahalleAd'] ?? $p['mahalle_adi'] ?? '',
            'raw' => $p
        ];
        if (!empty($feature['geometry'])) {
            $out['geometry'] = $feature['geometry'];
            $out['geojson'] = isset($data['features']) ? $data : ['type' => 'Feature', 'geometry' => $feature['geometry'], 'properties' => $p];
        }
        return $out;
    }

    /**
     * ArcGIS MapServer Layer 0 query ile parsel detay (TKGM Parsel katmanı: ILCE, KOYMAHALLE, ADA, PARSEL)
     */
    private function getParselDetayFromArcGIS(string $ilKodu, string $ilceKodu, string $mahalleKodu, string $ada, string $parsel): ?array {
        $ilceAdi = $this->resolveIlceAdi($ilKodu, $ilceKodu);
        $mahalleAdi = $this->resolveMahalleAdi($ilKodu, $ilceKodu, $mahalleKodu);
        $base = rtrim($this->arcgisLayerUrl, '/');
        $whereParts = [];
        if ($ilceAdi !== '') {
            $whereParts[] = "ILCE = '" . str_replace("'", "''", $ilceAdi) . "'";
        }
        if ($mahalleAdi !== '') {
            $whereParts[] = "KOYMAHALLE = '" . str_replace("'", "''", $mahalleAdi) . "'";
        }
        $whereParts[] = "ADA = '" . str_replace("'", "''", $ada) . "'";
        $whereParts[] = "PARSEL = '" . str_replace("'", "''", $parsel) . "'";
        $where = implode(' AND ', $whereParts);
        $url = $base . '/query?where=' . rawurlencode($where) . '&outFields=*&returnGeometry=false&f=json';
        $data = $this->request($url);
        if (!is_array($data) || empty($data['features'])) {
            return null;
        }
        $attrs = $data['features'][0]['attributes'] ?? null;
        if (!is_array($attrs)) {
            return null;
        }
        return [
            'tasinmaz_no' => $attrs['TASINMAZNO'] ?? $attrs['tasinmazno'] ?? '',
            'parsel_no' => $parsel,
            'ada' => $ada,
            'alan_m2' => $attrs['TAPUALANI'] ?? $attrs['tapualani'] ?? null,
            'nitelik' => $attrs['NITELIK'] ?? $attrs['nitelik'] ?? '',
            'il_adi' => $attrs['IL_ADI'] ?? $attrs['il_adi'] ?? '',
            'ilce_adi' => $attrs['ILCE'] ?? $attrs['ilce'] ?? $ilceAdi,
            'mahalle_adi' => $attrs['KOYMAHALLE'] ?? $attrs['koymahalle'] ?? $mahalleAdi,
            'raw' => $attrs
        ];
    }

    private function resolveIlceAdi(string $ilKodu, string $ilceKodu): string {
        $list = $this->getIlceler($ilKodu);
        foreach ($list as $item) {
            if (($item['kodu'] ?? '') === $ilceKodu) {
                return $item['adi'] ?? '';
            }
        }
        return '';
    }

    private function resolveMahalleAdi(string $ilKodu, string $ilceKodu, string $mahalleKodu): string {
        $list = $this->getMahalleler($ilKodu, $ilceKodu);
        foreach ($list as $item) {
            if (($item['kodu'] ?? '') === $mahalleKodu) {
                return $item['adi'] ?? '';
            }
        }
        return '';
    }

    /**
     * HTTP GET isteği
     * @param string $url
     * @return array|null JSON decode edilmiş dizi veya null
     */
    private function request(string $url): ?array {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => $this->timeout,
                'header' => "Accept: application/json\r\nUser-Agent: Mozilla/5.0 (compatible; TKGM-Parsel/1.0)\r\n"
            ]
        ]);
        $raw = @file_get_contents($url, false, $context);
        if ($raw === false) {
            return null;
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Turkiye API ile ilçe listesi (CBS yanıt vermezse)
     */
    private function getIlcelerFromTurkiyeApi(string $ilKodu): array {
        $url = self::TURKIYE_API_BASE . '/provinces/' . rawurlencode($ilKodu);
        $data = $this->request($url);
        if (!is_array($data) || ($data['status'] ?? '') !== 'OK') {
            return [];
        }
        $districts = $data['data']['districts'] ?? [];
        if (!is_array($districts)) {
            return [];
        }
        $list = [];
        foreach ($districts as $d) {
            $id = $d['id'] ?? '';
            $name = $d['name'] ?? '';
            if ($id !== '' || $name !== '') {
                $list[] = ['kodu' => (string)$id, 'adi' => (string)$name];
            }
        }
        return $list;
    }

    /**
     * Turkiye API ile mahalle/köy listesi (CBS yanıt vermezse)
     */
    private function getMahallelerFromTurkiyeApi(string $ilceKodu): array {
        $url = self::TURKIYE_API_BASE . '/districts/' . rawurlencode($ilceKodu);
        $data = $this->request($url);
        if (!is_array($data) || ($data['status'] ?? '') !== 'OK') {
            return [];
        }
        $data = $data['data'] ?? [];
        $neighborhoods = $data['neighborhoods'] ?? [];
        $villages = $data['villages'] ?? [];
        $list = [];
        foreach ($neighborhoods as $n) {
            $id = $n['id'] ?? '';
            $name = $n['name'] ?? '';
            if ($id !== '' || $name !== '') {
                $list[] = ['kodu' => (string)$id, 'adi' => (string)$name];
            }
        }
        foreach ($villages as $v) {
            $id = $v['id'] ?? '';
            $name = $v['name'] ?? '';
            if ($id !== '' || $name !== '') {
                $list[] = ['kodu' => (string)$id, 'adi' => (string)$name];
            }
        }
        return $list;
    }

    private function normalizeIller($data): array {
        if (!is_array($data)) {
            return [];
        }
        $out = [];
        if (isset($data['data']) && is_array($data['data'])) {
            foreach ($data['data'] as $row) {
                $kodu = $row['kodu'] ?? $row['ilKodu'] ?? $row['id'] ?? '';
                $adi = $row['adi'] ?? $row['ilAdi'] ?? $row['name'] ?? '';
                if ($kodu !== '' || $adi !== '') {
                    $out[] = ['kodu' => (string)$kodu, 'adi' => (string)$adi];
                }
            }
        } elseif (isset($data[0])) {
            foreach ($data as $row) {
                $kodu = $row['kodu'] ?? $row['ilKodu'] ?? $row['id'] ?? '';
                $adi = $row['adi'] ?? $row['ilAdi'] ?? $row['name'] ?? '';
                if ($kodu !== '' || $adi !== '') {
                    $out[] = ['kodu' => (string)$kodu, 'adi' => (string)$adi];
                }
            }
        }
        return $out;
    }

    private function normalizeIlceler($data): array {
        if (!is_array($data)) {
            return [];
        }
        $out = [];
        $items = $data['data'] ?? $data;
        if (!is_array($items)) {
            return [];
        }
        foreach ($items as $row) {
            $kodu = $row['kodu'] ?? $row['ilceKodu'] ?? $row['id'] ?? '';
            $adi = $row['adi'] ?? $row['ilceAdi'] ?? $row['name'] ?? '';
            if ($kodu !== '' || $adi !== '') {
                $out[] = ['kodu' => (string)$kodu, 'adi' => (string)$adi];
            }
        }
        return $out;
    }

    private function normalizeMahalleler($data): array {
        if (!is_array($data)) {
            return [];
        }
        $out = [];
        $items = $data['data'] ?? $data;
        if (!is_array($items)) {
            return [];
        }
        foreach ($items as $row) {
            $kodu = $row['kodu'] ?? $row['koyKodu'] ?? $row['id'] ?? '';
            $adi = $row['adi'] ?? $row['koyAdi'] ?? $row['name'] ?? '';
            if ($kodu !== '' || $adi !== '') {
                $out[] = ['kodu' => (string)$kodu, 'adi' => (string)$adi];
            }
        }
        return $out;
    }

    private function normalizeAdalar($data): array {
        if (!is_array($data)) {
            return [];
        }
        $out = [];
        $items = $data['data'] ?? $data;
        if (!is_array($items)) {
            return [];
        }
        foreach ($items as $row) {
            $kodu = $row['ada'] ?? $row['kodu'] ?? $row['id'] ?? '';
            $adi = $row['ada'] ?? $row['adi'] ?? $row['name'] ?? $kodu;
            if ($kodu !== '') {
                $out[] = ['kodu' => (string)$kodu, 'adi' => (string)$adi];
            }
        }
        return $out;
    }

    private function normalizeParseller($data): array {
        if (!is_array($data)) {
            return [];
        }
        $out = [];
        $items = $data['data'] ?? $data;
        if (!is_array($items)) {
            return [];
        }
        foreach ($items as $row) {
            $kodu = $row['parsel'] ?? $row['kodu'] ?? $row['id'] ?? '';
            $adi = $row['parsel'] ?? $row['adi'] ?? $row['name'] ?? $kodu;
            if ($kodu !== '') {
                $out[] = ['kodu' => (string)$kodu, 'adi' => (string)$adi];
            }
        }
        return $out;
    }

    private function normalizeParselDetay(?array $data, string $ilKodu, string $ilceKodu, string $mahalleKodu, string $ada, string $parsel): ?array {
        if (!is_array($data) || empty($data)) {
            return null;
        }
        $row = $data['data'] ?? $data;
        if (isset($data['data']) && is_array($data['data'])) {
            $row = $data['data'];
        }
        if (!is_array($row)) {
            return null;
        }
        return [
            'tasinmaz_no' => $row['tasinmazNo'] ?? $row['tasinmaz_no'] ?? $row['OBJECTID'] ?? '',
            'parsel_no' => $parsel,
            'ada' => $ada,
            'alan_m2' => $row['tapuAlani'] ?? $row['alan_m2'] ?? $row['TAPUALANI'] ?? $row['alan'] ?? null,
            'nitelik' => $row['nitelik'] ?? $row['NITELIK'] ?? $row['propertyType'] ?? '',
            'il_adi' => $row['ilAdi'] ?? $row['il_adi'] ?? '',
            'ilce_adi' => $row['ilceAdi'] ?? $row['ilce_adi'] ?? '',
            'mahalle_adi' => $row['koyAdi'] ?? $row['mahalle_adi'] ?? $row['KOYMAHALLE'] ?? '',
            'raw' => $row
        ];
    }

    private function getCache(string $key): ?array {
        if ($this->cacheTtl <= 0) {
            return null;
        }
        $file = $this->cacheDir . '/' . md5($key) . '.json';
        if (!is_file($file)) {
            return null;
        }
        $content = @file_get_contents($file);
        if ($content === false) {
            return null;
        }
        $data = json_decode($content, true);
        if (!is_array($data) || !isset($data['expires']) || $data['expires'] < time()) {
            @unlink($file);
            return null;
        }
        return $data['value'] ?? null;
    }

    private function setCache(string $key, array $value): void {
        if ($this->cacheTtl <= 0) {
            return;
        }
        $file = $this->cacheDir . '/' . md5($key) . '.json';
        $data = [
            'expires' => time() + ($this->cacheTtl * 60),
            'value' => $value
        ];
        @file_put_contents($file, json_encode($data));
    }

    /**
     * API bağlantı testi (illeri çekmeyi dene)
     */
    public function testConnection(): array {
        try {
            $iller = $this->getIller();
            return [
                'success' => count($iller) > 0,
                'message' => count($iller) > 0
                    ? 'Bağlantı başarılı. ' . count($iller) . ' il listelendi.'
                    : 'API yanıt veremedi; varsayılan il listesi kullanılıyor.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Hata: ' . $e->getMessage()
            ];
        }
    }
}
