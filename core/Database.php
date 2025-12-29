<?php
/**
 * Veritabanı Sınıfı
 * PDO kullanarak MySQL bağlantısı yönetir
 * Singleton pattern ile tek bağlantı garantisi
 */

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $config = require __DIR__ . '/../config/database.php';
        
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        
        try {
            $this->connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );
        } catch (PDOException $e) {
            die("Veritabanı bağlantı hatası: " . $e->getMessage());
        }
    }
    
    /**
     * Singleton instance döndürür
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * PDO bağlantısını döndürür
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Prepared statement ile sorgu çalıştırır
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            // Exception fırlat ki çağıran kod handle edebilsin
            throw $e;
        }
    }
    
    /**
     * Tek satır döndürür
     */
    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Tüm satırları döndürür
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Son eklenen ID'yi döndürür
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Transaction başlat
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Transaction'ı onayla
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Transaction'ı geri al
     */
    public function rollBack() {
        return $this->connection->rollBack();
    }
    
    /**
     * Klonlamayı engelle
     */
    private function __clone() {}
    
    /**
     * Unserialize'ı engelle
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

