<?php
/**
 * role_modules tablosundan page-builder yetkisini kaldırır.
 * Sayfa yapıcı koddan kaldırıldığı için bir kez çalıştırılabilir.
 */

require_once __DIR__ . '/../core/Database.php';

$db = Database::getInstance();

try {
    $stmt = $db->query("DELETE FROM role_modules WHERE module_slug = 'page-builder'");
    $deleted = $stmt ? $stmt->rowCount() : 0;
    echo "page-builder yetkisi kaldırıldı. Silinen kayıt: " . (int) $deleted . "\n";
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage() . "\n";
    exit(1);
}
