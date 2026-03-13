<?php
/**
 * Kurulum İşlem Action Dosyası
 * Config dosyası oluşturur, tabloları oluşturur, admin kullanıcı ekler
 */

// Session zaten install_process.php'de başlatıldı, tekrar başlatma
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session kontrolü
if (!isset($_SESSION['db_host']) || !isset($_SESSION['site_name'])) {
    header("Location: step1.php");
    exit;
}

$messages = [];
$hasError = false;

try {
    // 1. Config dosyası oluştur
    $messages[] = ['type' => 'info', 'text' => 'Config dosyası oluşturuluyor...'];
    
    $configContent = "<?php
/**
 * Veritabanı Bağlantı Ayarları
 * Otomatik kurulum ile oluşturuldu
 */

return [
    'host' => '" . addslashes($_SESSION['db_host']) . "',
    'dbname' => '" . addslashes($_SESSION['db_name']) . "',
    'username' => '" . addslashes($_SESSION['db_user']) . "',
    'password' => '" . addslashes($_SESSION['db_password']) . "',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
";
    
    $configFile = __DIR__ . '/../config/database.php';
    if (!is_dir(dirname($configFile))) {
        mkdir(dirname($configFile), 0755, true);
    }
    
    file_put_contents($configFile, $configContent);
    $messages[] = ['type' => 'success', 'text' => 'Config dosyası oluşturuldu'];
    
    // 2. Veritabanı bağlantısını test et
    $messages[] = ['type' => 'info', 'text' => 'Veritabanı bağlantısı test ediliyor...'];
    require_once __DIR__ . '/../core/Database.php';
    $db = Database::getInstance();
    $connection = $db->getConnection();
    $messages[] = ['type' => 'success', 'text' => 'Veritabanı bağlantısı başarılı'];
    
    // 3. SQL dosyasını yükle (sadece complete_schema.sql)
    $messages[] = ['type' => 'info', 'text' => 'Veritabanı oluşturuluyor...'];
    
    // Önce SET komutlarını çalıştır
    try {
        $connection->exec("SET NAMES utf8mb4");
        $connection->exec("SET FOREIGN_KEY_CHECKS = 0");
    } catch (PDOException $e) {
        // Sessizce devam et
    }
    
    // Ana schema dosyası - Tüm tablolar ve varsayılan veriler burada
    $schemaFile = __DIR__ . '/complete_schema.sql';
    
    if (!file_exists($schemaFile)) {
        $messages[] = ['type' => 'error', 'text' => "✗ Schema dosyası bulunamadı: " . $schemaFile];
        $hasError = true;
    } else {
        $sqlContent = file_get_contents($schemaFile);
        $messages[] = ['type' => 'info', 'text' => "📄 Schema dosyası okundu (" . strlen($sqlContent) . " byte)"];
        
        // Çok satırlı SQL'leri düzgün parse et
        // Her CREATE TABLE ... ENGINE=InnoDB; bir komut olarak algılanmalı
        
        // Önce yorumları temizle
        $sqlContent = preg_replace('/--[^\n]*\n/', "\n", $sqlContent);
        $sqlContent = preg_replace('/\/\*.*?\*\//s', '', $sqlContent);
        
        // SQL ifadelerini ayır - noktalı virgül ile ama dikkatli
        $statements = [];
        $buffer = '';
        $lines = explode("\n", $sqlContent);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $buffer .= ' ' . $line;
            
            // Eğer satır noktalı virgül ile bitiyorsa, bu bir komutun sonu
            if (substr($line, -1) === ';') {
                $statements[] = trim($buffer);
                $buffer = '';
            }
        }
        
        // Son buffer'ı da ekle
        if (!empty(trim($buffer))) {
            $statements[] = trim($buffer);
        }
        
        $messages[] = ['type' => 'info', 'text' => "🔧 " . count($statements) . " SQL komutu hazırlandı"];
        
        $successCount = 0;
        $createdTables = [];
        $actualErrors = [];
        $skippedCount = 0;
        
        foreach ($statements as $idx => $statement) {
            $statement = trim($statement);
            
            // Boş ifadeleri atla
            if (empty($statement)) {
                $skippedCount++;
                continue;
            }
            
            // SET komutlarını atla
            if (preg_match('/^\s*SET\s+/i', $statement)) {
                $skippedCount++;
                continue;
            }
            
            try {
                // Noktalı virgülü kaldır (PDO exec için)
                $statement = rtrim($statement, ';');
                
                if (empty($statement)) {
                    $skippedCount++;
                    continue;
                }
                
                $connection->exec($statement);
                
                // Tablo adını çıkar ve göster
                if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/i', $statement, $matches)) {
                    $tableName = $matches[1];
                    if (!in_array($tableName, $createdTables)) {
                        $createdTables[] = $tableName;
                        $messages[] = ['type' => 'success', 'text' => "✓ Tablo oluşturuldu: {$tableName}"];
                    }
                } elseif (preg_match('/INSERT\s+(?:IGNORE\s+)?INTO\s+`?(\w+)`?/i', $statement, $matches)) {
                    // INSERT komutları için sessiz başarı
                }
                
                $successCount++;
            } catch (PDOException $e) {
                $errorMsg = $e->getMessage();
                
                // Normal hatalar (atla)
                if (strpos($errorMsg, 'already exists') !== false) {
                    if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/i', $statement, $matches)) {
                        if (!in_array($matches[1], $createdTables)) {
                            $createdTables[] = $matches[1];
                            $messages[] = ['type' => 'info', 'text' => "ℹ Tablo zaten var: {$matches[1]}"];
                        }
                    }
                } elseif (strpos($errorMsg, 'Duplicate') === false &&
                          strpos($errorMsg, 'Cannot add or update') === false) {
                    // Gerçek hata
                    $actualErrors[] = $errorMsg;
                    
                    // İlk 5 hatayı göster
                    if (count($actualErrors) <= 5) {
                        $messages[] = ['type' => 'error', 'text' => "⚠ SQL #" . ($idx+1) . ": " . htmlspecialchars(substr($errorMsg, 0, 150))];
                    }
                }
            }
        }
        
        $messages[] = ['type' => 'info', 'text' => "📊 Sonuç: {$successCount} başarılı, {$skippedCount} atlandı, " . count($actualErrors) . " hata"];
        
        // FOREIGN_KEY_CHECKS'i geri aç
        try {
            $connection->exec("SET FOREIGN_KEY_CHECKS = 1");
        } catch (PDOException $e) {
            // Sessizce devam et
        }
        
        // Sonuç - Tablo sayısı
        $tableCount = count($createdTables);
        
        if ($tableCount > 0) {
            $messages[] = ['type' => 'success', 'text' => "✓ {$tableCount} tablo oluşturuldu"];
        } else {
            $messages[] = ['type' => 'error', 'text' => "✗ Hiç tablo oluşturulamadı!"];
            $hasError = true;
        }
        
        // Kritik tabloları kontrol et
        $criticalTables = ['users', 'posts', 'options', 'media', 'menus', 'modules', 'sliders'];
        $missingTables = [];
        $foundTables = [];
        
        foreach ($criticalTables as $table) {
            try {
                $result = $connection->query("SHOW TABLES LIKE '{$table}'");
                if ($result && $result->rowCount() > 0) {
                    $foundTables[] = $table;
                } else {
                    $missingTables[] = $table;
                }
            } catch (PDOException $e) {
                $missingTables[] = $table;
            }
        }
        
        if (count($foundTables) > 0) {
            $messages[] = ['type' => 'success', 'text' => "✓ Kritik tablolar: " . implode(', ', $foundTables)];
        }
        
        if (count($missingTables) > 0) {
            $messages[] = ['type' => 'error', 'text' => "✗ Eksik tablolar: " . implode(', ', $missingTables)];
            
            // Hata detayları
            if (count($actualErrors) > 0) {
                $messages[] = ['type' => 'error', 'text' => "Toplam " . count($actualErrors) . " SQL hatası oluştu"];
            }
            
            $hasError = true;
        }
    }
    
    // 4. Varsayılan ayarları ekle (options tablosu varsa)
    if (!$hasError) {
        $messages[] = ['type' => 'info', 'text' => 'Varsayılan ayarlar ekleniyor...'];
        
        try {
            $optionsTableExists = false;
            $result = $connection->query("SHOW TABLES LIKE 'options'");
            if ($result && $result->rowCount() > 0) {
                $optionsTableExists = true;
            }
            
            if ($optionsTableExists) {
                $defaultOptions = [
                    'site_name' => $_SESSION['site_name'],
                    'site_description' => 'Modern içerik yönetim sistemi',
                    'admin_email' => $_SESSION['admin_email'],
                    'timezone' => 'Europe/Istanbul',
                    'date_format' => 'd/m/Y',
                    'time_format' => 'H:i',
                    'active_theme' => 'starter',
                    'posts_per_page' => '10',
                    'language' => 'tr',
                    'charset' => 'UTF-8',
                    'enable_comments' => '0',
                    'enable_registration' => '0',
                    'maintenance_mode' => '0',
                ];
                
                $optionsAdded = 0;
                foreach ($defaultOptions as $key => $value) {
                    try {
                        $existing = $db->fetch("SELECT option_id FROM options WHERE option_name = ?", [$key]);
                        if (!$existing) {
                            $db->query(
                                "INSERT INTO options (option_name, option_value, autoload) VALUES (?, ?, 'yes')",
                                [$key, $value]
                            );
                            $optionsAdded++;
                        }
                    } catch (PDOException $e) {
                        // Sessizce devam et
                    }
                }
                
                if ($optionsAdded > 0) {
                    $messages[] = ['type' => 'success', 'text' => "✓ {$optionsAdded} varsayılan ayar eklendi"];
                } else {
                    $messages[] = ['type' => 'info', 'text' => 'Varsayılan ayarlar zaten mevcut'];
                }
            } else {
                $messages[] = ['type' => 'info', 'text' => 'Options tablosu bulunamadı, ayarlar atlandı'];
            }
        } catch (Exception $e) {
            $messages[] = ['type' => 'info', 'text' => 'Varsayılan ayarlar eklenirken sorun oluştu, devam ediliyor...'];
        }
    }
    
    // 5. İlk kullanıcıyı oluştur (kurulumda eklenen bu kullanıcı Süper Admin olur)
    if (!$hasError) {
        $messages[] = ['type' => 'info', 'text' => 'Yönetici kullanıcısı oluşturuluyor...'];
        
        try {
            $adminCheck = $db->fetch("SELECT id FROM users WHERE username = ? OR email = ?", [
                $_SESSION['admin_username'],
                $_SESSION['admin_email']
            ]);
        } catch (PDOException $e) {
            // Tablo yok veya hata, kullanıcı oluştur
            $adminCheck = false;
        }
        
        if (!$adminCheck) {
            try {
                $adminPassword = password_hash($_SESSION['admin_password'], PASSWORD_DEFAULT);
                
                // Kurulumda ilk eklenen kullanıcı her zaman super_admin'dir; sonrakiler admin.
                $userCount = 0;
                try {
                    $countResult = $db->fetch("SELECT COUNT(*) as count FROM users");
                    $userCount = $countResult['count'] ?? 0;
                } catch (PDOException $e) {
                    // Tablo yok, ilk kullanıcı
                    $userCount = 0;
                }
                
                $role = ($userCount == 0) ? 'super_admin' : 'admin';
                
                $db->query(
                    "INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, ?, 'active')",
                    [$_SESSION['admin_username'], $_SESSION['admin_email'], $adminPassword, $role]
                );
                
                $roleLabel = ($role === 'super_admin') ? 'Süper Admin' : 'Admin';
                $messages[] = ['type' => 'success', 'text' => ($role === 'super_admin') ? 'İlk kullanıcı Süper Admin olarak oluşturuldu.' : "{$roleLabel} kullanıcısı oluşturuldu."];
            } catch (PDOException $e) {
                $messages[] = ['type' => 'error', 'text' => 'Admin kullanıcısı oluşturulamadı: ' . htmlspecialchars($e->getMessage())];
                $hasError = true;
            }
        } else {
            // Aynı kullanıcı zaten var; tek kullanıcıysa kurulumdaki ilk kullanıcı kabul edip super_admin yap
            try {
                $countResult = $db->fetch("SELECT COUNT(*) as count FROM users");
                $userCount = (int) ($countResult['count'] ?? 0);
                if ($userCount === 1) {
                    $db->query(
                        "UPDATE users SET role = 'super_admin' WHERE id = ?",
                        [$adminCheck['id']]
                    );
                    $messages[] = ['type' => 'info', 'text' => 'Mevcut ilk kullanıcı Süper Admin olarak güncellendi.'];
                } else {
                    $messages[] = ['type' => 'info', 'text' => 'Bu kullanıcı zaten kayıtlı.'];
                }
            } catch (PDOException $e) {
                $messages[] = ['type' => 'info', 'text' => 'Bu kullanıcı zaten kayıtlı.'];
            }
        }
    }
    
    // 6. Aktif temayı kontrol et ve ayarla
    if (!$hasError) {
        try {
            // Themes tablosunda starter teması var mı kontrol et
            $themeCheck = $db->fetch("SELECT * FROM themes WHERE slug = 'starter'");
            
            if (!$themeCheck) {
                // theme.json dosyasını yükle
                $themeJsonPath = __DIR__ . '/../themes/starter/theme.json';
                $settingsSchema = null;
                
                if (file_exists($themeJsonPath)) {
                    $themeJsonContent = file_get_contents($themeJsonPath);
                    $settingsSchema = $themeJsonContent; // JSON string olarak sakla
                }
                
                // Starter temasını ekle
                $db->query(
                    "INSERT INTO themes (name, slug, version, author, description, settings_schema, is_active, installed_at) 
                     VALUES (?, ?, ?, ?, ?, ?, 1, NOW())",
                    ['Starter Theme', 'starter', '1.0.0', 'Codetic', 'Modern ve minimal başlangıç teması', $settingsSchema]
                );
                $messages[] = ['type' => 'success', 'text' => '✓ Varsayılan tema (Starter) aktif edildi'];
            } else {
                // Temayı aktif yap ve settings_schema'yı güncelle
                $themeJsonPath = __DIR__ . '/../themes/starter/theme.json';
                if (file_exists($themeJsonPath)) {
                    $themeJsonContent = file_get_contents($themeJsonPath);
                    $db->query("UPDATE themes SET is_active = 0");
                    $db->query(
                        "UPDATE themes SET is_active = 1, settings_schema = ? WHERE slug = 'starter'",
                        [$themeJsonContent]
                    );
                } else {
                    $db->query("UPDATE themes SET is_active = 0");
                    $db->query("UPDATE themes SET is_active = 1 WHERE slug = 'starter'");
                }
                $messages[] = ['type' => 'info', 'text' => 'Starter teması zaten mevcut ve aktif'];
            }
        } catch (PDOException $e) {
            $messages[] = ['type' => 'info', 'text' => 'Tema kontrolü atlandı (kritik değil)'];
        }
    }
    
    // 7. Temel modülleri aktif et
    if (!$hasError) {
        try {
            // Cache ve SEO modüllerini aktif yap
            $coreModules = ['cache', 'seo'];
            $activatedModules = 0;
            
            foreach ($coreModules as $moduleName) {
                $moduleCheck = $db->fetch("SELECT * FROM modules WHERE slug = ?", [$moduleName]);
                if ($moduleCheck && $moduleCheck['is_active'] == 0) {
                    $db->query("UPDATE modules SET is_active = 1, activated_at = NOW() WHERE slug = ?", [$moduleName]);
                    $activatedModules++;
                }
            }
            
            if ($activatedModules > 0) {
                $messages[] = ['type' => 'success', 'text' => "✓ {$activatedModules} temel modül aktif edildi"];
            }
        } catch (PDOException $e) {
            $messages[] = ['type' => 'info', 'text' => 'Modül aktivasyonu atlandı (kritik değil)'];
        }
    }
    
    
    // Başarılı!
    // Mesajları göster ve step3'e yönlendir
    foreach ($messages as $msg) {
        echo '<div class="status-item ' . htmlspecialchars($msg['type']) . '">';
        echo '<span class="status-icon">' . ($msg['type'] === 'success' ? '✓' : ($msg['type'] === 'error' ? '✗' : 'ℹ')) . '</span>';
        echo '<span>' . $msg['text'] . '</span>';
        echo '</div>';
    }
    
    if (!$hasError) {
        echo '<a href="step3.php" class="btn">';
        echo '<span>Kurulumu Tamamla</span>';
        echo '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">';
        echo '<path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
        echo '</svg>';
        echo '</a>';
    } else {
        echo '<a href="step2.php" class="btn" style="background: linear-gradient(135deg, #64748b 0%, #475569 100%);">';
        echo '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">';
        echo '<path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
        echo '</svg>';
        echo '<span>Geri Dön ve Tekrar Dene</span>';
        echo '</a>';
    }
    
} catch (Exception $e) {
    $hasError = true;
    echo '<div class="status-item error">';
    echo '<span class="status-icon">✗</span>';
    echo '<span>Hata: ' . htmlspecialchars($e->getMessage()) . '</span>';
    echo '</div>';
    echo '<a href="step2.php" class="btn">';
    echo '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">';
    echo '<path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
    echo '</svg>';
    echo '<span>Geri Dön</span>';
    echo '</a>';
}
