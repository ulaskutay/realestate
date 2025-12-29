<?php
/**
 * Role Sınıfı - Veritabanı Entegrasyonlu
 * Sadece Super Admin TÜM yetkilere sahip
 * Admin dahil diğer roller veritabanından yetki alır
 */

class Role {
    
    /**
     * Süper admin rolü - Bu rol HER ZAMAN tüm yetkilere sahip
     * Admin rolü artık veritabanından yetki kontrolü yapıyor
     */
    private static $superAdminRole = 'super_admin';
    
    /**
     * Rol hiyerarşisi (yüksek = daha yetkili)
     */
    private static $hierarchy = [
        'super_admin' => 100,
        'admin' => 90,
        'editor' => 50,
        'author' => 30,
        'subscriber' => 10,
        'user' => 10
    ];
    
    /**
     * Varsayılan roller (veritabanı yoksa fallback)
     */
    private static $defaultRoles = [
        'super_admin' => [
            'name' => 'Süper Admin',
            'description' => 'Tüm yetkilere sahip',
            'permissions' => ['*']
        ],
        'admin' => [
            'name' => 'Admin', 
            'description' => 'Yönetim paneli erişimi (veritabanından yetki alır)',
            'permissions' => [] // Veritabanından kontrol edilir
        ],
        'editor' => [
            'name' => 'Editör',
            'description' => 'İçerik yönetimi',
            'permissions' => [
                // Yazılar
                'posts.view', 'posts.create', 'posts.edit', 'posts.delete', 'posts.publish',
                // Sayfalar
                'pages.view', 'pages.create', 'pages.edit', 'pages.delete', 'pages.publish',
                // Medya
                'media.view', 'media.upload', 'media.edit', 'media.delete',
                // Formlar
                'forms.view', 'forms.create', 'forms.edit', 'forms.delete', 'forms.submissions',
                // Sözleşmeler
                'agreements.view', 'agreements.create', 'agreements.edit', 'agreements.delete',
                // Sliderlar
                'sliders.view', 'sliders.create', 'sliders.edit', 'sliders.delete',
                // Menüler
                'menus.view', 'menus.create', 'menus.edit', 'menus.delete'
            ]
        ],
        'author' => [
            'name' => 'Yazar',
            'description' => 'İçerik oluşturma',
            'permissions' => [
                // Yazılar
                'posts.view', 'posts.create', 'posts.edit',
                // Sayfalar
                'pages.view', 'pages.create', 'pages.edit',
                // Medya
                'media.view', 'media.upload'
            ]
        ],
        'subscriber' => [
            'name' => 'Abone',
            'description' => 'Sınırlı erişim',
            'permissions' => ['posts.view', 'media.view']
        ],
        'user' => [
            'name' => 'Kullanıcı',
            'description' => 'Sınırlı erişim',
            'permissions' => ['posts.view', 'media.view']
        ]
    ];
    
    /**
     * Cache temizle (uyumluluk için)
     */
    public static function clearCache() {
        // Boş - ileride cache eklenebilir
    }
    
    /**
     * Kullanıcı süper admin mi?
     */
    public static function isSuperAdmin($role) {
        $role = strtolower(trim($role));
        return $role === self::$superAdminRole;
    }
    
    /**
     * Kullanıcı admin mi? (admin veya super_admin)
     */
    public static function isAdmin($role) {
        $role = strtolower(trim($role));
        return $role === 'admin' || $role === self::$superAdminRole;
    }
    
    /**
     * Veritabanından rol yetkilerini al
     */
    private static function getPermissionsFromDB($roleSlug) {
        try {
            $db = Database::getInstance();
            
            // Önce rolü bul
            $role = $db->fetch("SELECT id FROM roles WHERE slug = ?", [$roleSlug]);
            if (!$role) {
                return null; // Rol veritabanında yok, fallback kullan
            }
            
            // Yetkileri al
            $permissions = $db->fetchAll(
                "SELECT permission FROM role_permissions WHERE role_id = ?",
                [$role['id']]
            );
            
            $permList = [];
            foreach ($permissions as $p) {
                $permList[] = $p['permission'];
            }
            
            return $permList;
        } catch (Exception $e) {
            return null; // Hata durumunda fallback kullan
        }
    }
    
    /**
     * Kullanıcının yetkisi var mı?
     * Sadece super_admin HER ZAMAN true döner
     * Admin dahil diğer roller için veritabanından kontrol eder
     */
    public static function hasPermission($role, $permission) {
        $role = strtolower(trim($role));
        
        // Sadece super_admin her zaman true
        if (self::isSuperAdmin($role)) {
            return true;
        }
        
        // Veritabanından yetkileri al (admin dahil tüm roller)
        $dbPermissions = self::getPermissionsFromDB($role);
        
        if ($dbPermissions !== null) {
            // Veritabanında rol var, oradan kontrol et
            if (in_array('*', $dbPermissions)) {
                return true;
            }
            return in_array($permission, $dbPermissions);
        }
        
        // Veritabanında yoksa varsayılan rolleri kullan
        if (!isset(self::$defaultRoles[$role])) {
            return false;
        }
        
        $permissions = self::$defaultRoles[$role]['permissions'];
        
        if (in_array('*', $permissions)) {
            return true;
        }
        
        return in_array($permission, $permissions);
    }
    
    /**
     * Rol var mı?
     */
    public static function exists($role) {
        $role = strtolower(trim($role));
        
        // Önce veritabanında kontrol et
        try {
            $db = Database::getInstance();
            $result = $db->fetch("SELECT id FROM roles WHERE slug = ?", [$role]);
            if ($result) {
                return true;
            }
        } catch (Exception $e) {
            // Veritabanı hatası, fallback'e geç
        }
        
        return isset(self::$defaultRoles[$role]) || isset(self::$hierarchy[$role]);
    }
    
    /**
     * Rol adını getir
     */
    public static function getName($role) {
        $role = strtolower(trim($role));
        
        // Önce veritabanından dene
        try {
            $db = Database::getInstance();
            $result = $db->fetch("SELECT name FROM roles WHERE slug = ?", [$role]);
            if ($result) {
                return $result['name'];
            }
        } catch (Exception $e) {
            // Fallback
        }
        
        return self::$defaultRoles[$role]['name'] ?? ucfirst($role);
    }
    
    /**
     * Rol açıklamasını getir
     */
    public static function getDescription($role) {
        $role = strtolower(trim($role));
        return self::$defaultRoles[$role]['description'] ?? '';
    }
    
    /**
     * Tüm rolleri getir
     */
    public static function getAll() {
        return self::$defaultRoles;
    }
    
    /**
     * Rol seviyesini getir
     */
    public static function getLevel($role) {
        $role = strtolower(trim($role));
        return self::$hierarchy[$role] ?? 0;
    }
    
    /**
     * İlk rol ikinciden yüksek mi?
     */
    public static function isHigherThan($role1, $role2) {
        return self::getLevel($role1) > self::getLevel($role2);
    }
    
    /**
     * İlk rol ikinciden düşük mü?
     */
    public static function isLowerThan($role1, $role2) {
        return self::getLevel($role1) < self::getLevel($role2);
    }
    
    /**
     * Select için roller
     */
    public static function getOptionsForSelect() {
        $options = [];
        foreach (self::$defaultRoles as $key => $role) {
            $options[$key] = $role['name'];
        }
        return $options;
    }
}
