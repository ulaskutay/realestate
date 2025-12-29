<?php
/**
 * Mailer Sınıfı
 * SMTP üzerinden e-posta gönderimi için kullanılır
 */

class Mailer {
    private $host;
    private $port;
    private $username;
    private $password;
    private $encryption;
    private $fromEmail;
    private $fromName;
    private $debug = false;
    private $lastError = '';
    
    /**
     * Mailer constructor
     * @param array $config SMTP ayarları (opsiyonel, yoksa veritabanından alınır)
     */
    public function __construct($config = null) {
        if ($config === null) {
            $this->loadFromDatabase();
        } else {
            $this->host = $config['host'] ?? '';
            $this->port = $config['port'] ?? 587;
            $this->username = $config['username'] ?? '';
            $this->password = $config['password'] ?? '';
            $this->encryption = $config['encryption'] ?? 'tls';
            $this->fromEmail = $config['from_email'] ?? '';
            $this->fromName = $config['from_name'] ?? '';
        }
    }
    
    /**
     * Veritabanından SMTP ayarlarını yükler
     */
    private function loadFromDatabase() {
        $this->host = get_option('smtp_host', '');
        $this->port = (int) get_option('smtp_port', 587);
        $this->username = get_option('smtp_username', '');
        $this->password = get_option('smtp_password', '');
        $this->encryption = get_option('smtp_encryption', 'tls');
        $this->fromEmail = get_option('smtp_from_email', '');
        $this->fromName = get_option('smtp_from_name', '');
    }
    
    /**
     * SMTP ayarlarının yapılandırılıp yapılandırılmadığını kontrol eder
     */
    public function isConfigured() {
        return !empty($this->host) && 
               !empty($this->port) && 
               !empty($this->username) && 
               !empty($this->fromEmail);
    }
    
    /**
     * Debug modunu ayarlar
     */
    public function setDebug($debug) {
        $this->debug = $debug;
        return $this;
    }
    
    /**
     * Son hatayı döndürür
     */
    public function getLastError() {
        return $this->lastError;
    }
    
    /**
     * E-posta gönderir
     * @param string $to Alıcı e-posta adresi
     * @param string $subject Konu
     * @param string $body Mesaj içeriği (HTML destekler)
     * @param array $options Ek seçenekler (cc, bcc, replyTo, attachments, isHtml)
     * @return bool Başarılı ise true
     */
    public function send($to, $subject, $body, $options = []) {
        $this->lastError = '';
        
        // SMTP yapılandırılmış mı kontrol et
        if (!$this->isConfigured()) {
            $this->lastError = 'SMTP ayarları yapılandırılmamış.';
            return false;
        }
        
        $isHtml = $options['isHtml'] ?? true;
        $cc = $options['cc'] ?? [];
        $bcc = $options['bcc'] ?? [];
        $replyTo = $options['replyTo'] ?? '';
        $attachments = $options['attachments'] ?? [];
        
        // Boundary oluştur (attachments veya HTML için)
        $boundary = md5(uniqid(time()));
        $boundaryMixed = md5(uniqid(time() . 'mixed'));
        
        // Headers
        $headers = [];
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "From: " . $this->formatAddress($this->fromEmail, $this->fromName);
        
        if ($replyTo) {
            $headers[] = "Reply-To: " . $replyTo;
        }
        
        if (!empty($cc)) {
            $headers[] = "Cc: " . implode(', ', (array)$cc);
        }
        
        if (!empty($bcc)) {
            $headers[] = "Bcc: " . implode(', ', (array)$bcc);
        }
        
        // Content type
        if (!empty($attachments)) {
            $headers[] = "Content-Type: multipart/mixed; boundary=\"{$boundaryMixed}\"";
        } elseif ($isHtml) {
            $headers[] = "Content-Type: text/html; charset=UTF-8";
        } else {
            $headers[] = "Content-Type: text/plain; charset=UTF-8";
        }
        
        $headers[] = "X-Mailer: CMS-Mailer/1.0";
        
        // Message body
        $message = '';
        
        if (!empty($attachments)) {
            // Mixed content with attachments
            $message .= "--{$boundaryMixed}\r\n";
            $message .= "Content-Type: " . ($isHtml ? "text/html" : "text/plain") . "; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $message .= chunk_split(base64_encode($body)) . "\r\n";
            
            // Attachments
            foreach ($attachments as $attachment) {
                if (is_array($attachment)) {
                    $filePath = $attachment['path'] ?? '';
                    $fileName = $attachment['name'] ?? basename($filePath);
                } else {
                    $filePath = $attachment;
                    $fileName = basename($filePath);
                }
                
                if (file_exists($filePath)) {
                    $fileContent = file_get_contents($filePath);
                    $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
                    
                    $message .= "--{$boundaryMixed}\r\n";
                    $message .= "Content-Type: {$mimeType}; name=\"{$fileName}\"\r\n";
                    $message .= "Content-Disposition: attachment; filename=\"{$fileName}\"\r\n";
                    $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
                    $message .= chunk_split(base64_encode($fileContent)) . "\r\n";
                }
            }
            
            $message .= "--{$boundaryMixed}--";
        } else {
            $message = $body;
        }
        
        // SMTP ile gönder
        return $this->sendViaSMTP($to, $subject, $message, implode("\r\n", $headers));
    }
    
    /**
     * SMTP üzerinden e-posta gönderir
     */
    private function sendViaSMTP($to, $subject, $message, $headers) {
        $socket = null;
        
        try {
            // Bağlantı kur
            $protocol = '';
            if ($this->encryption === 'ssl') {
                $protocol = 'ssl://';
            }
            
            $timeout = 30;
            
            // SSL context oluştur (sertifika doğrulama sorunları için)
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                    'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
                ]
            ]);
            
            // stream_socket_client kullan (daha iyi SSL desteği)
            $socket = @stream_socket_client(
                $protocol . $this->host . ':' . $this->port,
                $errno,
                $errstr,
                $timeout,
                STREAM_CLIENT_CONNECT,
                $context
            );
            
            if (!$socket) {
                $this->lastError = "SMTP sunucusuna bağlanılamadı: {$errstr} (Hata: {$errno})";
                return false;
            }
            
            // Timeout ayarla
            stream_set_timeout($socket, $timeout);
            
            // Karşılama mesajını oku
            $response = $this->getResponse($socket);
            if (!$this->checkResponse($response, '220')) {
                $this->lastError = "SMTP sunucu karşılaması başarısız: {$response}";
                fclose($socket);
                return false;
            }
            
            // EHLO
            $this->sendCommand($socket, "EHLO " . gethostname());
            $response = $this->getResponse($socket);
            if (!$this->checkResponse($response, '250')) {
                $this->lastError = "EHLO komutu başarısız: {$response}";
                fclose($socket);
                return false;
            }
            
            // STARTTLS (TLS için)
            if ($this->encryption === 'tls') {
                $this->sendCommand($socket, "STARTTLS");
                $response = $this->getResponse($socket);
                if (!$this->checkResponse($response, '220')) {
                    $this->lastError = "STARTTLS başarısız: {$response}";
                    fclose($socket);
                    return false;
                }
                
                // TLS'i etkinleştir - Modern TLS versiyonlarını dene
                // Stream context'i TLS için ayarla
                stream_context_set_option($context, 'ssl', 'verify_peer', false);
                stream_context_set_option($context, 'ssl', 'verify_peer_name', false);
                stream_context_set_option($context, 'ssl', 'allow_self_signed', true);
                
                $cryptoMethod = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
                if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
                    $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
                }
                
                $cryptoEnabled = @stream_socket_enable_crypto($socket, true, $cryptoMethod);
                if (!$cryptoEnabled) {
                    // Fallback - tüm TLS versiyonlarını dene
                    $cryptoEnabled = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_SSLv23_CLIENT);
                }
                
                if (!$cryptoEnabled) {
                    $sslError = error_get_last();
                    $this->lastError = "TLS şifrelemesi etkinleştirilemedi. " . ($sslError['message'] ?? 'SSL sertifika hatası olabilir.');
                    fclose($socket);
                    return false;
                }
                
                // TLS sonrası tekrar EHLO
                $this->sendCommand($socket, "EHLO " . gethostname());
                $response = $this->getResponse($socket);
            }
            
            // AUTH LOGIN
            $this->sendCommand($socket, "AUTH LOGIN");
            $response = $this->getResponse($socket);
            if (!$this->checkResponse($response, '334')) {
                $this->lastError = "AUTH LOGIN başarısız: {$response}";
                fclose($socket);
                return false;
            }
            
            // Kullanıcı adı
            $this->sendCommand($socket, base64_encode($this->username));
            $response = $this->getResponse($socket);
            if (!$this->checkResponse($response, '334')) {
                $this->lastError = "Kullanıcı adı doğrulaması başarısız: {$response}";
                fclose($socket);
                return false;
            }
            
            // Şifre
            $this->sendCommand($socket, base64_encode($this->password));
            $response = $this->getResponse($socket);
            if (!$this->checkResponse($response, '235')) {
                $this->lastError = "Şifre doğrulaması başarısız: {$response}";
                fclose($socket);
                return false;
            }
            
            // MAIL FROM
            $this->sendCommand($socket, "MAIL FROM: <{$this->fromEmail}>");
            $response = $this->getResponse($socket);
            if (!$this->checkResponse($response, '250')) {
                $this->lastError = "MAIL FROM başarısız: {$response}";
                fclose($socket);
                return false;
            }
            
            // RCPT TO
            $recipients = is_array($to) ? $to : [$to];
            foreach ($recipients as $recipient) {
                $this->sendCommand($socket, "RCPT TO: <{$recipient}>");
                $response = $this->getResponse($socket);
                if (!$this->checkResponse($response, '250')) {
                    $this->lastError = "RCPT TO başarısız ({$recipient}): {$response}";
                    fclose($socket);
                    return false;
                }
            }
            
            // DATA
            $this->sendCommand($socket, "DATA");
            $response = $this->getResponse($socket);
            if (!$this->checkResponse($response, '354')) {
                $this->lastError = "DATA başarısız: {$response}";
                fclose($socket);
                return false;
            }
            
            // E-posta içeriği - RFC 5321 uyumlu
            $emailContent = "Date: " . date('r') . "\r\n";
            $emailContent .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
            $emailContent .= "To: " . (is_array($to) ? implode(', ', $to) : $to) . "\r\n";
            $emailContent .= $headers . "\r\n\r\n";
            
            // Mesaj içeriğinde satır başındaki nokta karakterlerini escape et (RFC 5321)
            $escapedMessage = str_replace("\r\n.", "\r\n..", $message);
            $emailContent .= $escapedMessage;
            
            // DATA içeriğini gönder (son nokta ayrı satırda)
            fwrite($socket, $emailContent . "\r\n.\r\n");
            $response = $this->getResponse($socket);
            if (!$this->checkResponse($response, '250')) {
                $this->lastError = "E-posta gönderimi başarısız: {$response}";
                fclose($socket);
                return false;
            }
            
            // QUIT
            $this->sendCommand($socket, "QUIT");
            fclose($socket);
            
            return true;
            
        } catch (Exception $e) {
            $this->lastError = "SMTP hatası: " . $e->getMessage();
            if ($socket) {
                fclose($socket);
            }
            return false;
        }
    }
    
    /**
     * SMTP komutu gönderir
     */
    private function sendCommand($socket, $command) {
        if ($this->debug) {
            error_log("SMTP > " . $command);
        }
        fwrite($socket, $command . "\r\n");
    }
    
    /**
     * SMTP yanıtını okur
     */
    private function getResponse($socket) {
        $response = '';
        $startTime = time();
        $maxWait = 30; // Maximum 30 saniye bekle
        
        while (true) {
            // Timeout kontrolü
            if ((time() - $startTime) > $maxWait) {
                if ($this->debug) {
                    error_log("SMTP < TIMEOUT");
                }
                break;
            }
            
            $line = @fgets($socket, 515);
            
            if ($line === false) {
                // Stream durumu kontrolü
                $info = stream_get_meta_data($socket);
                if ($info['timed_out']) {
                    break;
                }
                if ($info['eof']) {
                    break;
                }
                usleep(100000); // 100ms bekle
                continue;
            }
            
            $response .= $line;
            
            // Yanıtın son satırı 3 haneli kod ve boşlukla başlar
            if (strlen($line) >= 4 && $line[3] === ' ') {
                break;
            }
        }
        
        if ($this->debug) {
            error_log("SMTP < " . trim($response));
        }
        return $response;
    }
    
    /**
     * SMTP yanıt kodunu kontrol eder
     */
    private function checkResponse($response, $expectedCode) {
        return strpos($response, $expectedCode) === 0;
    }
    
    /**
     * E-posta adresini formatlar
     */
    private function formatAddress($email, $name = '') {
        if (empty($name)) {
            return $email;
        }
        return "=?UTF-8?B?" . base64_encode($name) . "?= <{$email}>";
    }
    
    /**
     * HTML şablonu ile e-posta gönderir
     * @param string $to Alıcı
     * @param string $subject Konu
     * @param string $templateName Şablon adı
     * @param array $data Şablon verileri
     * @param array $options Ek seçenekler
     * @return bool
     */
    public function sendTemplate($to, $subject, $templateName, $data = [], $options = []) {
        $templatePath = __DIR__ . '/../app/views/emails/' . $templateName . '.php';
        
        if (!file_exists($templatePath)) {
            // Varsayılan basit şablon kullan
            $body = $this->getDefaultTemplate($data);
        } else {
            // Şablonu render et
            ob_start();
            extract($data);
            include $templatePath;
            $body = ob_get_clean();
        }
        
        return $this->send($to, $subject, $body, $options);
    }
    
    /**
     * Varsayılan e-posta şablonu
     */
    private function getDefaultTemplate($data) {
        $siteName = get_option('seo_title', 'CMS');
        $content = $data['content'] ?? '';
        $title = $data['title'] ?? '';
        
        return <<<HTML
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 32px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .email-header {
            text-align: center;
            padding-bottom: 24px;
            border-bottom: 1px solid #eee;
            margin-bottom: 24px;
        }
        .email-header h1 {
            color: #137fec;
            font-size: 24px;
            margin: 0;
        }
        .email-content {
            padding: 16px 0;
        }
        .email-footer {
            text-align: center;
            padding-top: 24px;
            border-top: 1px solid #eee;
            margin-top: 24px;
            color: #888;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>{$siteName}</h1>
        </div>
        <div class="email-content">
            {$content}
        </div>
        <div class="email-footer">
            <p>Bu e-posta {$siteName} tarafından gönderilmiştir.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * SMTP bağlantısını test eder
     * @return array ['success' => bool, 'message' => string]
     */
    public function testConnection() {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'SMTP ayarları yapılandırılmamış. Lütfen tüm alanları doldurun.'
            ];
        }
        
        $socket = null;
        
        try {
            // Bağlantı kur
            $protocol = '';
            if ($this->encryption === 'ssl') {
                $protocol = 'ssl://';
            }
            
            $timeout = 10;
            
            // SSL context oluştur
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                    'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
                ]
            ]);
            
            $socket = @stream_socket_client(
                $protocol . $this->host . ':' . $this->port,
                $errno,
                $errstr,
                $timeout,
                STREAM_CLIENT_CONNECT,
                $context
            );
            
            if (!$socket) {
                return [
                    'success' => false,
                    'message' => "SMTP sunucusuna bağlanılamadı: {$errstr} (Hata kodu: {$errno})"
                ];
            }
            
            stream_set_timeout($socket, $timeout);
            
            // Karşılama
            $response = $this->getResponse($socket);
            if (!$this->checkResponse($response, '220')) {
                fclose($socket);
                return [
                    'success' => false,
                    'message' => "SMTP sunucu karşılaması başarısız: " . trim($response)
                ];
            }
            
            // EHLO
            $this->sendCommand($socket, "EHLO " . gethostname());
            $response = $this->getResponse($socket);
            if (!$this->checkResponse($response, '250')) {
                fclose($socket);
                return [
                    'success' => false,
                    'message' => "EHLO komutu başarısız: " . trim($response)
                ];
            }
            
            // STARTTLS (TLS için)
            if ($this->encryption === 'tls') {
                $this->sendCommand($socket, "STARTTLS");
                $response = $this->getResponse($socket);
                if (!$this->checkResponse($response, '220')) {
                    fclose($socket);
                    return [
                        'success' => false,
                        'message' => "STARTTLS başarısız: " . trim($response)
                    ];
                }
                
                // Stream context'i TLS için ayarla
                stream_context_set_option($context, 'ssl', 'verify_peer', false);
                stream_context_set_option($context, 'ssl', 'verify_peer_name', false);
                stream_context_set_option($context, 'ssl', 'allow_self_signed', true);
                
                // Modern TLS versiyonlarını dene
                $cryptoMethod = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
                if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
                    $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
                }
                
                $cryptoEnabled = @stream_socket_enable_crypto($socket, true, $cryptoMethod);
                if (!$cryptoEnabled) {
                    $cryptoEnabled = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_SSLv23_CLIENT);
                }
                
                if (!$cryptoEnabled) {
                    $sslError = error_get_last();
                    fclose($socket);
                    return [
                        'success' => false,
                        'message' => "TLS şifrelemesi etkinleştirilemedi. " . ($sslError['message'] ?? 'SSL sertifika hatası olabilir.')
                    ];
                }
                
                $this->sendCommand($socket, "EHLO " . gethostname());
                $response = $this->getResponse($socket);
            }
            
            // AUTH LOGIN
            $this->sendCommand($socket, "AUTH LOGIN");
            $response = $this->getResponse($socket);
            if (!$this->checkResponse($response, '334')) {
                fclose($socket);
                return [
                    'success' => false,
                    'message' => "Kimlik doğrulama başlatılamadı: " . trim($response)
                ];
            }
            
            // Kullanıcı adı
            $this->sendCommand($socket, base64_encode($this->username));
            $response = $this->getResponse($socket);
            if (!$this->checkResponse($response, '334')) {
                fclose($socket);
                return [
                    'success' => false,
                    'message' => "Kullanıcı adı doğrulanamadı."
                ];
            }
            
            // Şifre
            $this->sendCommand($socket, base64_encode($this->password));
            $response = $this->getResponse($socket);
            if (!$this->checkResponse($response, '235')) {
                fclose($socket);
                return [
                    'success' => false,
                    'message' => "Şifre doğrulanamadı. Lütfen SMTP bilgilerinizi kontrol edin."
                ];
            }
            
            // QUIT
            $this->sendCommand($socket, "QUIT");
            fclose($socket);
            
            return [
                'success' => true,
                'message' => "SMTP bağlantısı ve kimlik doğrulama başarılı!"
            ];
            
        } catch (Exception $e) {
            if ($socket) {
                fclose($socket);
            }
            return [
                'success' => false,
                'message' => "Bağlantı hatası: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}

