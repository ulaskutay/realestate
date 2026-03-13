<?php
/**
 * Role Model - Basit rol ve modül yetki sistemi
 * Roller veritabanında; her rolün erişebileceği modüller role_modules ile tutulur.
 */

class RoleModel extends Model {
    protected $table = 'roles';

    /**
     * Tüm rolleri getirir
     */
    public function getAll() {
        return $this->all('name ASC');
    }

    /**
     * Slug'a göre rol getirir
     */
    public function findBySlug($slug) {
        return $this->findOne('slug', $slug);
    }

    /**
     * role_modules tablosu yoksa oluşturur ve Admin (role_id=2) için çekirdek modülleri ekler
     */
    private function ensureRoleModulesTable() {
        try {
            $exists = $this->db->fetch("SHOW TABLES LIKE 'role_modules'");
            if ($exists) return;
        } catch (Exception $e) {
            return;
        }
        try {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `role_modules` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `role_id` int(11) NOT NULL,
                  `module_slug` varchar(100) NOT NULL,
                  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `role_module_slug` (`role_id`, `module_slug`),
                  KEY `role_id` (`role_id`),
                  CONSTRAINT `role_modules_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $core = ['posts', 'pages', 'agreements', 'forms', 'media', 'sliders', 'menus', 'users', 'themes', 'settings', 'modules', 'smtp', 'roles'];
            foreach ($core as $slug) {
                try {
                    $this->db->query("INSERT IGNORE INTO role_modules (role_id, module_slug) VALUES (2, ?)", [$slug]);
                } catch (Exception $e) { /* ignore */ }
            }
        } catch (Exception $e) {
            error_log('RoleModel::ensureRoleModulesTable: ' . $e->getMessage());
        }
    }

    /**
     * Rolün erişebileceği modül slug listesini getirir
     * @param int $roleId
     * @return string[]
     */
    public function getAllowedModules($roleId) {
        try {
            $this->ensureRoleModulesTable();
            $rows = $this->db->fetchAll(
                "SELECT module_slug FROM role_modules WHERE role_id = ? ORDER BY module_slug",
                [$roleId]
            );
        } catch (Exception $e) {
            return [];
        }
        $slugs = [];
        foreach ($rows as $row) {
            $slugs[] = $row['module_slug'];
        }
        return $slugs;
    }

    /**
     * Rolün erişebileceği modülleri ayarlar (önce siler, sonra verilen listeyi ekler)
     * @param int $roleId
     * @param string[] $slugs
     */
    public function setAllowedModules($roleId, array $slugs) {
        try {
            $this->ensureRoleModulesTable();
            $this->db->query("DELETE FROM role_modules WHERE role_id = ?", [$roleId]);
            foreach ($slugs as $slug) {
                $slug = trim((string) $slug);
                if ($slug === '') continue;
                $this->db->query(
                    "INSERT INTO role_modules (role_id, module_slug) VALUES (?, ?)",
                    [$roleId, $slug]
                );
            }
        } catch (Exception $e) {
            error_log('RoleModel::setAllowedModules: ' . $e->getMessage());
        }
    }

    /**
     * Rol oluşturur
     * @param array $data name, slug, description (optional)
     * @return int|false Yeni rol id veya false
     */
    public function createRole($data) {
        if (empty($data['name'])) {
            return false;
        }
        $slugRaw = isset($data['slug']) ? trim((string) $data['slug']) : '';
        $slug = $slugRaw !== '' ? $this->normalizeSlug($slugRaw) : $this->slugFromName($data['name']);
        if ($slug === '') return false;
        $existing = $this->findBySlug($slug);
        if ($existing) {
            return false;
        }
        $insert = [
            'name' => trim($data['name']),
            'slug' => $slug,
            'description' => isset($data['description']) ? trim($data['description']) : null,
            'is_system' => isset($data['is_system']) ? (int) $data['is_system'] : 0,
        ];
        return $this->create($insert);
    }

    /**
     * Rol günceller
     */
    public function updateRole($id, $data) {
        $role = $this->find($id);
        if (!$role) return false;
        $update = [];
        if (isset($data['name'])) $update['name'] = trim($data['name']);
        if (isset($data['slug']) && empty($role['is_system'])) {
            $update['slug'] = $this->normalizeSlug($data['slug']);
        }
        if (array_key_exists('description', $data)) $update['description'] = trim($data['description']);
        if (empty($update)) return true;
        return $this->update($id, $update);
    }

    /**
     * Rol siler (sistem rolleri silinemez)
     */
    public function deleteRole($id) {
        $role = $this->find($id);
        if (!$role) return false;
        if (!empty($role['is_system'])) {
            return false;
        }
        return $this->delete($id);
    }

    /**
     * Bu role sahip kullanıcı sayısı
     */
    public function getUserCount($roleId) {
        $role = $this->find($roleId);
        if (!$role) return 0;
        $slug = $role['slug'];
        $r = $this->db->fetch("SELECT COUNT(*) AS cnt FROM users WHERE role = ?", [$slug]);
        return (int) ($r['cnt'] ?? 0);
    }

    private function normalizeSlug($slug) {
        $slug = trim(strtolower((string) $slug));
        $slug = preg_replace('/[^a-z0-9_-]/', '_', $slug);
        return preg_replace('/_+/', '_', trim($slug, '_'));
    }

    private function slugFromName($name) {
        $t = ['ı' => 'i', 'ğ' => 'g', 'ü' => 'u', 'ş' => 's', 'ö' => 'o', 'ç' => 'c', 'İ' => 'i'];
        $s = strtr($name, $t);
        return $this->normalizeSlug($s);
    }
}
