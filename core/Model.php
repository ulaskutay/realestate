<?php
/**
 * Base Model Sınıfı
 * Tüm modeller bu sınıftan türeyecek
 */

class Model {
    protected $db;
    protected $table;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Tüm kayıtları getirir
     */
    public function all($orderBy = null) {
        $sql = "SELECT * FROM `{$this->table}`";
        if ($orderBy) {
            // Backtick ile escape et (reserved keywords için)
            $orderBy = preg_replace('/\border\b/i', '`order`', $orderBy);
            $sql .= " ORDER BY {$orderBy}";
        }
        return $this->db->fetchAll($sql);
    }
    
    /**
     * ID'ye göre kayıt getirir
     */
    public function find($id) {
        $sql = "SELECT * FROM `{$this->table}` WHERE `id` = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Koşula göre kayıt getirir
     */
    public function where($column, $value, $operator = '=') {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$column}` {$operator} ?";
        return $this->db->fetchAll($sql, [$value]);
    }
    
    /**
     * Tek kayıt getirir
     */
    public function findOne($column, $value) {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$column}` = ? LIMIT 1";
        return $this->db->fetch($sql, [$value]);
    }
    
    /**
     * Yeni kayıt ekler
     */
    public function create($data) {
        // Kolon adlarını backtick ile escape et (MySQL rezerve kelimeler için)
        $columns = '`' . implode('`, `', array_keys($data)) . '`';
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO `{$this->table}` ({$columns}) VALUES ({$placeholders})";
        
        $params = [];
        foreach ($data as $key => $value) {
            $params[':' . $key] = $value;
        }
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Kayıt günceller
     */
    public function update($id, $data) {
        $set = [];
        foreach (array_keys($data) as $key) {
            // Kolon adlarını backtick ile escape et (MySQL rezerve kelimeler için)
            $set[] = "`{$key}` = :{$key}";
        }
        $set = implode(', ', $set);
        
        $sql = "UPDATE `{$this->table}` SET {$set} WHERE `id` = :id";
        
        $params = [':id' => $id];
        foreach ($data as $key => $value) {
            $params[':' . $key] = $value;
        }
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Kayıt siler
     */
    public function delete($id) {
        $sql = "DELETE FROM `{$this->table}` WHERE `id` = ?";
        return $this->db->query($sql, [$id]);
    }
}

