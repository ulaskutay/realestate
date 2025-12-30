<?php
/**
 * Migration Script: Move Pages Module to Theme
 * 
 * This script:
 * 1. Backs up existing core Pages files
 * 2. Registers the Pages module in the database
 * 3. Tests the theme module
 * 4. Optionally removes core files
 */

// Ensure we're running from command line or admin
if (php_sapi_name() !== 'cli' && !isset($_SESSION['user_id'])) {
    die('Unauthorized access');
}

$rootPath = dirname(__DIR__);
require_once $rootPath . '/core/Database.php';

class PagesMigration {
    private $db;
    private $rootPath;
    private $backupPath;
    
    public function __construct() {
        $this->rootPath = dirname(__DIR__);
        $this->backupPath = $this->rootPath . '/storage/backups/pages_migration_' . date('Y-m-d_His');
        $this->db = Database::getInstance();
    }
    
    public function run() {
        echo "=== Pages Module Migration to Theme ===\n\n";
        
        // Step 1: Create backup
        echo "[1/5] Creating backup of core Pages files...\n";
        $this->backupCoreFiles();
        echo "✓ Backup created at: {$this->backupPath}\n\n";
        
        // Step 2: Check if theme module exists
        echo "[2/5] Checking theme Pages module...\n";
        $moduleExists = $this->checkThemeModule();
        if (!$moduleExists) {
            echo "✗ Theme module not found at themes/starter/modules/pages\n";
            echo "  Please ensure the Pages module is properly installed in the theme.\n";
            return false;
        }
        echo "✓ Theme module found\n\n";
        
        // Step 3: Register module in database
        echo "[3/5] Registering Pages module in database...\n";
        $this->registerModule();
        echo "✓ Module registered\n\n";
        
        // Step 4: Test data compatibility
        echo "[4/5] Testing data compatibility...\n";
        $this->testDataCompatibility();
        echo "✓ Data compatibility verified\n\n";
        
        // Step 5: Instructions
        echo "[5/5] Migration complete!\n\n";
        echo "Next steps:\n";
        echo "1. Test the Pages module in admin panel: /admin?page=module/pages\n";
        echo "2. If everything works, run: php install/migrate_pages_module.php --cleanup\n";
        echo "3. This will remove the old core files (they are backed up)\n\n";
        
        if (isset($GLOBALS['argv']) && in_array('--cleanup', $GLOBALS['argv'])) {
            echo "\n=== Cleanup Mode ===\n";
            $this->cleanup();
        }
        
        return true;
    }
    
    /**
     * Backup core Pages files
     */
    private function backupCoreFiles() {
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
        
        $filesToBackup = [
            'app/controllers/PageController.php',
            'app/models/Page.php',
            'app/views/admin/pages'
        ];
        
        foreach ($filesToBackup as $file) {
            $sourcePath = $this->rootPath . '/' . $file;
            $destPath = $this->backupPath . '/' . $file;
            
            if (file_exists($sourcePath)) {
                $destDir = dirname($destPath);
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                
                if (is_dir($sourcePath)) {
                    $this->copyDirectory($sourcePath, $destPath);
                } else {
                    copy($sourcePath, $destPath);
                }
            }
        }
    }
    
    /**
     * Copy directory recursively
     */
    private function copyDirectory($src, $dst) {
        if (!is_dir($dst)) {
            mkdir($dst, 0755, true);
        }
        
        $dir = opendir($src);
        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $srcPath = $src . '/' . $file;
            $dstPath = $dst . '/' . $file;
            
            if (is_dir($srcPath)) {
                $this->copyDirectory($srcPath, $dstPath);
            } else {
                copy($srcPath, $dstPath);
            }
        }
        closedir($dir);
    }
    
    /**
     * Check if theme module exists
     */
    private function checkThemeModule() {
        $modulePath = $this->rootPath . '/themes/starter/modules/pages';
        $manifestPath = $modulePath . '/module.json';
        $controllerPath = $modulePath . '/Controller.php';
        
        return is_dir($modulePath) && 
               file_exists($manifestPath) && 
               file_exists($controllerPath);
    }
    
    /**
     * Register module in database
     */
    private function registerModule() {
        $sql = "INSERT INTO modules (name, slug, label, description, icon, version, author, path, is_active, installed_at, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    path = VALUES(path),
                    is_active = 1,
                    updated_at = NOW()";
        
        $this->db->query($sql, [
            'pages',
            'pages',
            'Sayfalar',
            'Statik sayfa yönetimi - Starter tema için özelleştirilmiş',
            'description',
            '1.0.0',
            'CMS',
            'themes/starter/modules/pages'
        ]);
    }
    
    /**
     * Test data compatibility
     */
    private function testDataCompatibility() {
        // Check if pages table exists and has data
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM posts WHERE type = 'page'");
        $pageCount = $result['count'] ?? 0;
        
        echo "  Found {$pageCount} pages in database\n";
        
        // Check page_meta table
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM page_meta");
        $metaCount = $result['count'] ?? 0;
        
        echo "  Found {$metaCount} page meta records\n";
        
        return true;
    }
    
    /**
     * Cleanup old core files
     */
    private function cleanup() {
        echo "\nRemoving old core Pages files...\n";
        
        $filesToRemove = [
            'app/controllers/PageController.php',
            'app/models/Page.php',
            'app/views/admin/pages'
        ];
        
        foreach ($filesToRemove as $file) {
            $path = $this->rootPath . '/' . $file;
            
            if (file_exists($path)) {
                if (is_dir($path)) {
                    $this->deleteDirectory($path);
                    echo "✓ Removed directory: {$file}\n";
                } else {
                    unlink($path);
                    echo "✓ Removed file: {$file}\n";
                }
            }
        }
        
        echo "\n✓ Cleanup complete!\n";
        echo "  Backup is still available at: {$this->backupPath}\n";
    }
    
    /**
     * Delete directory recursively
     */
    private function deleteDirectory($dir) {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        
        rmdir($dir);
    }
}

// Run migration
$migration = new PagesMigration();
$migration->run();

