<?php
/**
 * TTS Service - Sadece ElevenLabs (Flash/Turbo) ile metin-seslendirme.
 * Drone video seslendirmesi için kullanılır.
 * API: https://elevenlabs.io/docs/api-reference/text-to-speech
 */

class TtsService {

    /** Maksimum seslendirme metni karakter sayısı */
    const MAX_TEXT_LENGTH = 800;

    /** ElevenLabs Flash model (düşük gecikme, 32 dil, Türkçe dahil) */
    const MODEL_ID = 'eleven_flash_v2_5';

    /**
     * API çekilemediğinde kullanılacak yedek premade ses listesi. Sıra: erkekler (Can, Ali, ...) önce, kadınlar sonra.
     */
    private static $FALLBACK_VOICES = [
        '21m00Tcm4TlvDq8ikWAM' => ['voice_id' => '21m00Tcm4TlvDq8ikWAM', 'label' => 'Elif (Kadın)'],
        'AZnzlk1XvdvUeBnXmlld' => ['voice_id' => 'AZnzlk1XvdvUeBnXmlld', 'label' => 'Ayşe (Kadın)'],
        'Xb7hH8MSUJpSbSDYk0k2' => ['voice_id' => 'Xb7hH8MSUJpSbSDYk0k2', 'label' => 'Zeynep (Kadın)'],
        'XB0fDUnXU5powFXDhCwa' => ['voice_id' => 'XB0fDUnXU5powFXDhCwa', 'label' => 'Defne (Kadın)'],
        'EXAVITQu4vr4xnSDxMaL' => ['voice_id' => 'EXAVITQu4vr4xnSDxMaL', 'label' => 'Selin (Kadın)'],
        'pNInz6obpgDQGcFmaJgB' => ['voice_id' => 'pNInz6obpgDQGcFmaJgB', 'label' => 'Mehmet (Erkek)'],
        'ErXwobaYiN019PkySvjV' => ['voice_id' => 'ErXwobaYiN019PkySvjV', 'label' => 'Emre (Erkek)'],
        'VR6AewLTigWG4xSOukaG' => ['voice_id' => 'VR6AewLTigWG4xSOukaG', 'label' => 'Burak (Erkek)'],
        'pqHfZKP75CvOlQylNhV4' => ['voice_id' => 'pqHfZKP75CvOlQylNhV4', 'label' => 'Can (Erkek) — En çok tercih edilen'],
        'nPczCjzI2devNBz1zQrb' => ['voice_id' => 'nPczCjzI2devNBz1zQrb', 'label' => 'Ali (Erkek)'],
        'TxGEqnHWrfWFTfGW9XjX' => ['voice_id' => 'TxGEqnHWrfWFTfGW9XjX', 'label' => 'Kerem (Erkek)'],
    ];

    /** Yedek listede gösterim sırası: önce erkekler (Can 1, Ali 2, ...), sonra kadınlar. */
    private static $FALLBACK_ORDER = [
        'pqHfZKP75CvOlQylNhV4', 'nPczCjzI2devNBz1zQrb', 'pNInz6obpgDQGcFmaJgB', 'ErXwobaYiN019PkySvjV',
        'VR6AewLTigWG4xSOukaG', 'TxGEqnHWrfWFTfGW9XjX',
        '21m00Tcm4TlvDq8ikWAM', 'AZnzlk1XvdvUeBnXmlld', 'Xb7hH8MSUJpSbSDYk0k2', 'XB0fDUnXU5powFXDhCwa', 'EXAVITQu4vr4xnSDxMaL',
    ];

    /** Erkek premade ses voice_id listesi (API listesinde erkekleri üste almak için). */
    private static $MALE_VOICE_IDS = [
        'pqHfZKP75CvOlQylNhV4' => 0,  // Can - 1. sıra
        'nPczCjzI2devNBz1zQrb' => 1,  // Ali - 2. sıra
        'pNInz6obpgDQGcFmaJgB' => 2, 'ErXwobaYiN019PkySvjV' => 3, 'VR6AewLTigWG4xSOukaG' => 4,
        'TxGEqnHWrfWFTfGW9XjX' => 5, 'iP95p4xoKVk53GoZ742B' => 6, 'IKne3meq5aSn9XLyUdCD' => 7,
        'JBFqnCBsd6RMkjVDRZzb' => 8, '2EiwWnXFnvU5JabPnv8n' => 9, 'N2lVS1w4EtoT3dr4eOWO' => 10,
    ];

    /** Premade ses isimlerini listede Türk isimleriyle göstermek için eşleme (API'den gelen name -> gösterim). */
    private static $NAME_TO_TURKISH = [
        'Rachel' => 'Elif', 'Domi' => 'Ayşe', 'Alice' => 'Zeynep', 'Charlotte' => 'Defne', 'Bella' => 'Selin',
        'Adam' => 'Mehmet', 'Antoni' => 'Emre', 'Arnold' => 'Burak', 'Bill' => 'Can', 'Brian' => 'Ali', 'Josh' => 'Kerem',
        'Chris' => 'Efe', 'Charlie' => 'Cem', 'George' => 'Kaan', 'Clyde' => 'Barış', 'Callum' => 'Arda',
        'Emily' => 'Ece', 'Ellie' => 'Deniz', 'Lily' => 'Melis', 'Sarah' => 'Seda', 'Mimi' => 'Özge',
        'Ethan' => 'Onur', 'James' => 'Burak', 'Joseph' => 'Yusuf', 'Michael' => 'Mert', 'Patrick' => 'Kemal',
        'Paul' => 'Emre', 'Sam' => 'Samet', 'Harry' => 'Ege', 'Liam' => 'Efe', 'Roger' => 'Rıza',
        'Daniel' => 'Deniz', 'Grace' => 'Gül', 'Nicole' => 'Nil', 'Jessie' => 'Ceyda', 'Ryan' => 'Rüzgar',
        'Elliot' => 'Elmas', 'Dorothy' => 'Derya', 'Frank' => 'Ferit', 'Owen' => 'Oğuz', 'Dan' => 'Demir',
        'Dave' => 'Deniz', 'Fin' => 'Fırat', 'Freya' => 'Fulya', 'Matthew' => 'Mert', 'Thomas' => 'Tolga',
        'Oliver' => 'Onur', 'Jack' => 'Cem', 'Noah' => 'Nehir', 'Benjamin' => 'Berk', 'William' => 'Burak',
        'Henry' => 'Ege', 'Lucas' => 'Kaan', 'Alexander' => 'Alp', 'Mason' => 'Mert',
        'Emma' => 'Ece', 'Olivia' => 'Özge', 'Ava' => 'Ayşe', 'Sophia' => 'Selin', 'Isabella' => 'İnci',
        'Mia' => 'Melis', 'Amelia' => 'Aslı', 'Harper' => 'Hira', 'Evelyn' => 'Ece', 'Abigail' => 'Ayça',
        'Scarlett' => 'Sude', 'Chloe' => 'Ceyda', 'Victoria' => 'Vildan', 'Riley' => 'Rüya',
        'Aria' => 'Asya', 'Aurora' => 'Asya', 'Zoey' => 'Zeynep',
        'Penelope' => 'Pelin', 'Layla' => 'Lale', 'Nora' => 'Naz', 'Camila' => 'Ceyda',
        'Hannah' => 'Hüma', 'Lillian' => 'Lale', 'Addison' => 'Aslı', 'Eleanor' => 'Elif',
        'Natalie' => 'Nalan', 'Luna' => 'Lale', 'Savannah' => 'Selin', 'Brooklyn' => 'Beren',
        'Leah' => 'Leyla', 'Zoe' => 'Zeynep', 'Stella' => 'Selin', 'Hazel' => 'Hira',
        'Ellie' => 'Deniz', 'Paisley' => 'Pelin', 'Audrey' => 'Aslı', 'Skylar' => 'Sude',
        'Violet' => 'Vildan', 'Claire' => 'Ceyda', 'Bella' => 'Selin', 'Lucy' => 'Lale',
        'Anna' => 'Asya', 'Sadie' => 'Seda', 'Kennedy' => 'Kardelen', 'Gabriella' => 'Gül',
        'Madelyn' => 'Merve', 'Cora' => 'Ceyda', 'Maria' => 'Merve', 'Taylor' => 'Tuğba',
        'Rylee' => 'Rüya', 'Brielle' => 'Beren', 'Ivy' => 'İnci', 'Emilia' => 'Ece',
        'Josephine' => 'Jale', 'Ruby' => 'Rüya', 'Piper' => 'Pelin', 'Willow' => 'Vildan',
    ];

    /** @var string|null Geçici oluşturulan ses dosyası */
    private $lastTempPath = null;

    /**
     * ElevenLabs API'den premade ses listesini çeker (ücretsiz planda kullanılabilir sesler).
     * @param string $apiKey ElevenLabs xi-api-key
     * @return array [['id' => voice_id, 'label' => name, 'voice' => voice_id], ...] veya hata durumunda []
     */
    public static function fetchVoicesFromApi($apiKey) {
        $apiKey = trim($apiKey ?? '');
        if ($apiKey === '') {
            return [];
        }
        $ch = curl_init('https://api.elevenlabs.io/v1/voices');
        if (!$ch) {
            return [];
        }
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'xi-api-key: ' . $apiKey,
            ],
        ]);
        $data = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code !== 200 || $data === false || $data === '') {
            return [];
        }
        $json = json_decode($data, true);
        if (empty($json['voices']) || !is_array($json['voices'])) {
            return [];
        }
        $premadeOnly = [];
        foreach ($json['voices'] as $v) {
            $voiceId  = isset($v['voice_id']) ? trim($v['voice_id']) : '';
            $name     = isset($v['name']) ? trim($v['name']) : $voiceId;
            $category = isset($v['category']) ? (string) $v['category'] : '';
            if ($voiceId === '' || $category !== 'premade') {
                continue;
            }
            $label = isset(self::$NAME_TO_TURKISH[$name]) ? self::$NAME_TO_TURKISH[$name] : $name;
            if ($voiceId === 'pqHfZKP75CvOlQylNhV4') {
                $label .= ' — En çok tercih edilen';
            }
            $premadeOnly[] = ['id' => $voiceId, 'label' => $label, 'voice' => $voiceId];
        }
        // Erkekleri üste (Can 1, Ali 2), kadınları alta; erkekler kendi içinde sıralı
        usort($premadeOnly, function ($a, $b) {
            $aMale = isset(self::$MALE_VOICE_IDS[$a['id']]) ? self::$MALE_VOICE_IDS[$a['id']] : 999;
            $bMale = isset(self::$MALE_VOICE_IDS[$b['id']]) ? self::$MALE_VOICE_IDS[$b['id']] : 999;
            if ($aMale !== $bMale) {
                return $aMale - $bMale;
            }
            if ($aMale < 999) {
                return 0;
            }
            return strcasecmp($a['label'], $b['label']);
        });
        return $premadeOnly;
    }

    /**
     * Yedek (statik) premade ses listesini döndürür (API çekilemezse kullanılır). Sıra: erkekler üstte (Can, Ali, ...), kadınlar altta.
     * @return array [['id' => string, 'label' => string, 'voice' => string], ...]
     */
    public static function getVoices() {
        $list = [];
        foreach (self::$FALLBACK_ORDER as $id) {
            if (!isset(self::$FALLBACK_VOICES[$id])) {
                continue;
            }
            $item = self::$FALLBACK_VOICES[$id];
            $list[] = [
                'id'    => $id,
                'label' => $item['label'],
                'voice' => $item['voice_id'],
            ];
        }
        return $list;
    }

    /**
     * Verilen ses id için ElevenLabs voice_id döndürür.
     * Yedek listede varsa onu kullanır; yoksa geçerli bir voice_id formatındaysa olduğu gibi kabul eder (API'den gelen sesler).
     */
    public static function getElevenVoiceId($voiceId) {
        $id = trim($voiceId);
        if ($id === '') {
            return null;
        }
        if (isset(self::$FALLBACK_VOICES[$id])) {
            return self::$FALLBACK_VOICES[$id]['voice_id'];
        }
        // API'den gelen voice_id (örn. 20+ alfanumerik karakter)
        if (preg_match('/^[a-zA-Z0-9]{15,}$/', $id)) {
            return $id;
        }
        return null;
    }

    /**
     * Metni ElevenLabs Flash/Turbo ile sentezleyip geçici MP3 oluşturur.
     * @param string $text    Seslendirilecek metin (max 800 karakter)
     * @param string $voiceId Ses id (örn. rachel, adam)
     * @param array  $options ['elevenlabs_api_key' => string]
     * @return array ['success' => bool, 'path' => string|null, 'error' => string|null]
     */
    public function synthesize($text, $voiceId, array $options = []) {
        $this->lastTempPath = null;
        $text = trim($text);
        if ($text === '') {
            return ['success' => false, 'path' => null, 'error' => 'Metin boş.'];
        }
        if (mb_strlen($text) > self::MAX_TEXT_LENGTH) {
            $text = mb_substr($text, 0, self::MAX_TEXT_LENGTH);
        }
        $voiceId = trim($voiceId);
        $elevenVoiceId = self::getElevenVoiceId($voiceId);
        if ($elevenVoiceId === null) {
            return ['success' => false, 'path' => null, 'error' => 'Geçersiz ses seçimi.'];
        }
        $apiKey = isset($options['elevenlabs_api_key']) ? trim($options['elevenlabs_api_key']) : '';
        if ($apiKey === '') {
            return ['success' => false, 'path' => null, 'error' => 'ElevenLabs API key gerekli. TKGM Parsel ayarlarından API key girin.'];
        }

        $url = 'https://api.elevenlabs.io/v1/text-to-speech/' . $elevenVoiceId . '?output_format=mp3_44100_128';
        $body = json_encode([
            'text'       => $text,
            'model_id'   => self::MODEL_ID,
            'language_code' => 'tr',
        ]);
        $ch = curl_init($url);
        if (!$ch) {
            return ['success' => false, 'path' => null, 'error' => 'cURL başlatılamadı.'];
        }
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: audio/mpeg',
                'xi-api-key: ' . $apiKey,
            ],
        ]);
        $data = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($data === false || $err !== '') {
            return ['success' => false, 'path' => null, 'error' => 'ElevenLabs bağlantı hatası: ' . ($err ?: 'Bilinmeyen')];
        }
        if ($code === 401) {
            return ['success' => false, 'path' => null, 'error' => 'ElevenLabs API key geçersiz veya süresi dolmuş.'];
        }
        if ($code === 429) {
            return ['success' => false, 'path' => null, 'error' => 'ElevenLabs kota aşıldı. Ücretsiz planda aylık karakter limiti vardır.'];
        }
        if ($code !== 200 || strlen($data) < 500) {
            $msg = is_string($data) && (strpos($data, '{') === 0) ? (json_decode($data, true)['detail']['message'] ?? $data) : 'Yanıt alınamadı.';
            return ['success' => false, 'path' => null, 'error' => 'ElevenLabs: ' . (is_string($msg) ? substr($msg, 0, 200) : 'Hata ' . $code)];
        }

        $tempPath = sys_get_temp_dir() . '/drone_tts_' . uniqid() . '.mp3';
        if (file_put_contents($tempPath, $data) === false) {
            return ['success' => false, 'path' => null, 'error' => 'Ses dosyası yazılamadı.'];
        }
        $this->lastTempPath = $tempPath;
        return ['success' => true, 'path' => $tempPath, 'error' => null];
    }

    /**
     * Metni ElevenLabs with-timestamps endpoint ile sentezleyip MP3 + kelime zamanlarını döndürür.
     * Karakter bazlı alignment'dan kelime zamanları üretilir (CapCut tarzı altyazı için).
     * @param string $text    Seslendirilecek metin (max 800 karakter)
     * @param string $voiceId Ses id
     * @param array  $options ['elevenlabs_api_key' => string]
     * @return array ['success' => bool, 'path' => string|null, 'word_timings' => [['word'=>,'start'=>,'end'=>], ...], 'error' => string|null]
     */
    public function synthesizeWithTimestamps($text, $voiceId, array $options = []) {
        $this->lastTempPath = null;
        $text = trim($text);
        if ($text === '') {
            return ['success' => false, 'path' => null, 'word_timings' => [], 'error' => 'Metin boş.'];
        }
        if (mb_strlen($text) > self::MAX_TEXT_LENGTH) {
            $text = mb_substr($text, 0, self::MAX_TEXT_LENGTH);
        }
        $voiceId = trim($voiceId);
        $elevenVoiceId = self::getElevenVoiceId($voiceId);
        if ($elevenVoiceId === null) {
            return ['success' => false, 'path' => null, 'word_timings' => [], 'error' => 'Geçersiz ses seçimi.'];
        }
        $apiKey = isset($options['elevenlabs_api_key']) ? trim($options['elevenlabs_api_key']) : '';
        if ($apiKey === '') {
            return ['success' => false, 'path' => null, 'word_timings' => [], 'error' => 'ElevenLabs API key gerekli. TKGM Parsel ayarlarından API key girin.'];
        }

        $url = 'https://api.elevenlabs.io/v1/text-to-speech/' . $elevenVoiceId . '/with-timestamps?output_format=mp3_44100_128';
        $body = json_encode([
            'text'          => $text,
            'model_id'      => 'eleven_multilingual_v2',
            'language_code' => 'tr',
        ]);
        $ch = curl_init($url);
        if (!$ch) {
            return ['success' => false, 'path' => null, 'word_timings' => [], 'error' => 'cURL başlatılamadı.'];
        }
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 45,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'xi-api-key: ' . $apiKey,
            ],
        ]);
        $data = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($data === false || $err !== '') {
            return ['success' => false, 'path' => null, 'word_timings' => [], 'error' => 'ElevenLabs bağlantı hatası: ' . ($err ?: 'Bilinmeyen')];
        }
        if ($code === 401) {
            return ['success' => false, 'path' => null, 'word_timings' => [], 'error' => 'ElevenLabs API key geçersiz veya süresi dolmuş.'];
        }
        if ($code === 429) {
            return ['success' => false, 'path' => null, 'word_timings' => [], 'error' => 'ElevenLabs kota aşıldı. Ücretsiz planda aylık karakter limiti vardır.'];
        }

        $json = json_decode($data, true);
        if (!$json || empty($json['audio_base64'])) {
            $msg = is_array($json) && isset($json['detail']['message']) ? $json['detail']['message'] : (is_string($data) ? substr($data, 0, 200) : 'Yanıt ayrıştırılamadı.');
            return ['success' => false, 'path' => null, 'word_timings' => [], 'error' => 'ElevenLabs: ' . (is_string($msg) ? $msg : 'Hata ' . $code)];
        }

        $audioBin = base64_decode($json['audio_base64'], true);
        if ($audioBin === false || strlen($audioBin) < 500) {
            return ['success' => false, 'path' => null, 'word_timings' => [], 'error' => 'ElevenLabs ses verisi geçersiz.'];
        }
        $tempPath = sys_get_temp_dir() . '/drone_tts_' . uniqid() . '.mp3';
        if (file_put_contents($tempPath, $audioBin) === false) {
            return ['success' => false, 'path' => null, 'word_timings' => [], 'error' => 'Ses dosyası yazılamadı.'];
        }
        $this->lastTempPath = $tempPath;

        $wordTimings = [];
        if (!empty($json['alignment']) && is_array($json['alignment'])) {
            $wordTimings = self::alignmentToWordTimings($json['alignment']);
        }

        return ['success' => true, 'path' => $tempPath, 'word_timings' => $wordTimings, 'error' => null];
    }

    /**
     * ElevenLabs alignment (karakter bazlı) çıktısından kelime zamanları üretir.
     * @param array $alignment ['characters' => [...], 'character_start_times_seconds' => [...], 'character_end_times_seconds' => [...]]
     * @return array [['word' => string, 'start' => float, 'end' => float], ...]
     */
    public static function alignmentToWordTimings(array $alignment) {
        $chars = $alignment['characters'] ?? [];
        $starts = $alignment['character_start_times_seconds'] ?? [];
        $ends = $alignment['character_end_times_seconds'] ?? [];
        if (!is_array($chars) || !is_array($starts) || !is_array($ends) || count($chars) !== count($starts) || count($chars) !== count($ends)) {
            return [];
        }
        $wordTimings = [];
        $wordChars = [];
        $wordStartIdx = null;
        for ($i = 0; $i < count($chars); $i++) {
            $c = $chars[$i];
            $isSpace = ($c === ' ' || $c === "\n" || $c === "\t");
            if ($isSpace) {
                if ($wordStartIdx !== null && !empty($wordChars)) {
                    $wordTimings[] = [
                        'word'  => implode('', $wordChars),
                        'start' => (float) $starts[$wordStartIdx],
                        'end'   => (float) $ends[$i - 1],
                    ];
                }
                $wordChars = [];
                $wordStartIdx = null;
                continue;
            }
            if ($wordStartIdx === null) {
                $wordStartIdx = $i;
            }
            $wordChars[] = $c;
        }
        if ($wordStartIdx !== null && !empty($wordChars)) {
            $wordTimings[] = [
                'word'  => implode('', $wordChars),
                'start' => (float) $starts[$wordStartIdx],
                'end'   => (float) $ends[count($chars) - 1],
            ];
        }
        return $wordTimings;
    }

    /**
     * Son oluşturulan geçici dosya yolunu döndürür.
     * @return string|null
     */
    public function getLastTempPath() {
        return $this->lastTempPath;
    }
}
