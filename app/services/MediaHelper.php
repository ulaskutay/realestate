<?php
/**
 * Media yardımcıları: dosya indirme, Google Static Map URL.
 * Video yardımcıları: indirme, Static Map URL.
 */

class MediaHelper {

    /**
     * Harici bir URL'den dosyayı indirip yerel yola yazar.
     *
     * @param string $url       İndirilecek URL
     * @param string $savePath  Kaydedilecek dosya yolu
     * @return bool Başarılı ise true
     */
    public static function downloadToFile($url, $savePath) {
        $url = trim($url ?? '');
        if ($url === '') {
            return false;
        }
        $ch = curl_init($url);
        if (!$ch) {
            return false;
        }
        $fp = @fopen($savePath, 'wb');
        if (!$fp) {
            curl_close($ch);
            return false;
        }
        curl_setopt_array($ch, [
            CURLOPT_FILE    => $fp,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $ok = curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        if (!$ok || !is_file($savePath) || filesize($savePath) <= 0) {
            @unlink($savePath);
            return false;
        }
        return true;
    }

    /**
     * Google Static Maps (uydu) görüntü URL'i üretir.
     *
     * @param float  $lat     Enlem
     * @param float  $lng     Boylam
     * @param int    $zoom    Zoom seviyesi (1-20), varsayılan 16
     * @param string $apiKey  Google Maps API key (Maps Static API açık olmalı)
     * @param int    $width   Görsel genişlik (max 1280)
     * @param int    $height  Görsel yükseklik (max 1280)
     * @return string URL veya boş string (apiKey yoksa)
     */
    public static function googleStaticMapImageUrl($lat, $lng, $zoom = 16, $apiKey = '', $width = 1280, $height = 1280) {
        $apiKey = trim($apiKey ?? '');
        if ($apiKey === '') {
            return '';
        }
        $lat = (float) $lat;
        $lng = (float) $lng;
        $zoom = (int) $zoom;
        if ($zoom < 1) {
            $zoom = 16;
        }
        $w = min(640, max(100, (int) $width));
        $h = min(640, max(100, (int) $height));
        $center = $lat . ',' . $lng;
        $params = [
            'center' => $center,
            'zoom'   => $zoom,
            'size'   => $w . 'x' . $h,
            'maptype'=> 'satellite',
            'scale'  => 2,
            'key'    => $apiKey,
        ];
        return 'https://maps.googleapis.com/maps/api/staticmap?' . http_build_query($params);
    }
}
