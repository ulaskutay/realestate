<?php
/**
 * Hook System - WordPress Tarzı Action/Filter Sistemi
 * 
 * Bu sınıf, WordPress'teki hook sistemine benzer bir yapı sağlar.
 * Action'lar belirli noktalarda kod çalıştırmak için,
 * Filter'lar ise verileri değiştirmek için kullanılır.
 */

class HookSystem {
    private static $instance = null;
    
    /**
     * Kayıtlı action'lar
     * @var array
     */
    private $actions = [];
    
    /**
     * Kayıtlı filter'lar
     * @var array
     */
    private $filters = [];
    
    /**
     * Çalıştırılmış action'lar (debug için)
     * @var array
     */
    private $did_actions = [];
    
    /**
     * Şu an çalışan hook
     * @var string|null
     */
    private $current_hook = null;
    
    /**
     * Singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {}
    private function __clone() {}
    
    // ==================== ACTION METHODS ====================
    
    /**
     * Bir action'a callback ekler
     * 
     * @param string $tag Action adı
     * @param callable $callback Çağrılacak fonksiyon
     * @param int $priority Öncelik (düşük = önce çalışır)
     * @param int $accepted_args Kabul edilen argüman sayısı
     * @return bool
     */
    public function add_action($tag, $callback, $priority = 10, $accepted_args = 1) {
        return $this->add_hook($this->actions, $tag, $callback, $priority, $accepted_args);
    }
    
    /**
     * Bir action'ı çalıştırır
     * 
     * @param string $tag Action adı
     * @param mixed ...$args Callback'lere geçilecek argümanlar
     */
    public function do_action($tag, ...$args) {
        // Action çalıştırma sayısını artır
        if (!isset($this->did_actions[$tag])) {
            $this->did_actions[$tag] = 0;
        }
        $this->did_actions[$tag]++;
        
        // Bu tag için kayıtlı callback yoksa çık
        if (!isset($this->actions[$tag])) {
            return;
        }
        
        $this->current_hook = $tag;
        
        // Önceliğe göre sırala
        ksort($this->actions[$tag]);
        
        // Her öncelik seviyesindeki callback'leri çalıştır
        foreach ($this->actions[$tag] as $priority => $callbacks) {
            foreach ($callbacks as $callback_data) {
                $callback = $callback_data['callback'];
                $accepted_args = $callback_data['accepted_args'];
                
                // Kabul edilen argüman sayısına göre argümanları kes
                $call_args = array_slice($args, 0, $accepted_args);
                
                call_user_func_array($callback, $call_args);
            }
        }
        
        $this->current_hook = null;
    }
    
    /**
     * Bir action'dan callback kaldırır
     * 
     * @param string $tag Action adı
     * @param callable $callback Kaldırılacak fonksiyon
     * @param int $priority Öncelik
     * @return bool
     */
    public function remove_action($tag, $callback, $priority = 10) {
        return $this->remove_hook($this->actions, $tag, $callback, $priority);
    }
    
    /**
     * Bir action'a callback ekli mi kontrol eder
     * 
     * @param string $tag Action adı
     * @param callable|null $callback Kontrol edilecek callback (null ise herhangi biri)
     * @return bool|int
     */
    public function has_action($tag, $callback = null) {
        return $this->has_hook($this->actions, $tag, $callback);
    }
    
    /**
     * Bir action kaç kez çalıştırıldı
     * 
     * @param string $tag Action adı
     * @return int
     */
    public function did_action($tag) {
        return $this->did_actions[$tag] ?? 0;
    }
    
    // ==================== FILTER METHODS ====================
    
    /**
     * Bir filter'a callback ekler
     * 
     * @param string $tag Filter adı
     * @param callable $callback Çağrılacak fonksiyon
     * @param int $priority Öncelik
     * @param int $accepted_args Kabul edilen argüman sayısı
     * @return bool
     */
    public function add_filter($tag, $callback, $priority = 10, $accepted_args = 1) {
        return $this->add_hook($this->filters, $tag, $callback, $priority, $accepted_args);
    }
    
    /**
     * Bir filter'ı uygular ve sonucu döndürür
     * 
     * @param string $tag Filter adı
     * @param mixed $value Filtrelenecek değer
     * @param mixed ...$args Ek argümanlar
     * @return mixed Filtrelenmiş değer
     */
    public function apply_filters($tag, $value, ...$args) {
        // Bu tag için kayıtlı callback yoksa değeri olduğu gibi döndür
        if (!isset($this->filters[$tag])) {
            return $value;
        }
        
        $this->current_hook = $tag;
        
        // Önceliğe göre sırala
        ksort($this->filters[$tag]);
        
        // Argümanların başına değeri ekle
        array_unshift($args, $value);
        
        // Her öncelik seviyesindeki callback'leri çalıştır
        foreach ($this->filters[$tag] as $priority => $callbacks) {
            foreach ($callbacks as $callback_data) {
                $callback = $callback_data['callback'];
                $accepted_args = $callback_data['accepted_args'];
                
                // Kabul edilen argüman sayısına göre argümanları kes
                $call_args = array_slice($args, 0, $accepted_args);
                
                // İlk argüman (value) her zaman güncellenir
                $args[0] = call_user_func_array($callback, $call_args);
            }
        }
        
        $this->current_hook = null;
        
        return $args[0];
    }
    
    /**
     * Bir filter'dan callback kaldırır
     * 
     * @param string $tag Filter adı
     * @param callable $callback Kaldırılacak fonksiyon
     * @param int $priority Öncelik
     * @return bool
     */
    public function remove_filter($tag, $callback, $priority = 10) {
        return $this->remove_hook($this->filters, $tag, $callback, $priority);
    }
    
    /**
     * Bir filter'a callback ekli mi kontrol eder
     * 
     * @param string $tag Filter adı
     * @param callable|null $callback Kontrol edilecek callback
     * @return bool|int
     */
    public function has_filter($tag, $callback = null) {
        return $this->has_hook($this->filters, $tag, $callback);
    }
    
    // ==================== HELPER METHODS ====================
    
    /**
     * Şu an çalışan hook'u döndürür
     * 
     * @return string|null
     */
    public function current_hook() {
        return $this->current_hook;
    }
    
    /**
     * Tüm kayıtlı action'ları döndürür (debug için)
     * 
     * @return array
     */
    public function get_all_actions() {
        return $this->actions;
    }
    
    /**
     * Tüm kayıtlı filter'ları döndürür (debug için)
     * 
     * @return array
     */
    public function get_all_filters() {
        return $this->filters;
    }
    
    // ==================== PRIVATE METHODS ====================
    
    /**
     * Hook dizisine callback ekler
     */
    private function add_hook(&$hooks, $tag, $callback, $priority, $accepted_args) {
        if (!isset($hooks[$tag])) {
            $hooks[$tag] = [];
        }
        
        if (!isset($hooks[$tag][$priority])) {
            $hooks[$tag][$priority] = [];
        }
        
        $hooks[$tag][$priority][] = [
            'callback' => $callback,
            'accepted_args' => $accepted_args
        ];
        
        return true;
    }
    
    /**
     * Hook dizisinden callback kaldırır
     */
    private function remove_hook(&$hooks, $tag, $callback, $priority) {
        if (!isset($hooks[$tag][$priority])) {
            return false;
        }
        
        foreach ($hooks[$tag][$priority] as $index => $hook_data) {
            if ($hook_data['callback'] === $callback) {
                unset($hooks[$tag][$priority][$index]);
                
                // Dizi boşsa temizle
                if (empty($hooks[$tag][$priority])) {
                    unset($hooks[$tag][$priority]);
                }
                if (empty($hooks[$tag])) {
                    unset($hooks[$tag]);
                }
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Hook dizisinde callback var mı kontrol eder
     */
    private function has_hook(&$hooks, $tag, $callback = null) {
        if (!isset($hooks[$tag])) {
            return false;
        }
        
        // Callback belirtilmemişse, herhangi bir hook var mı kontrol et
        if ($callback === null) {
            return !empty($hooks[$tag]);
        }
        
        // Belirli callback'i ara
        foreach ($hooks[$tag] as $priority => $callbacks) {
            foreach ($callbacks as $hook_data) {
                if ($hook_data['callback'] === $callback) {
                    return $priority;
                }
            }
        }
        
        return false;
    }
}

// ==================== GLOBAL HELPER FUNCTIONS ====================

/**
 * Action ekler
 */
function add_action($tag, $callback, $priority = 10, $accepted_args = 1) {
    return HookSystem::getInstance()->add_action($tag, $callback, $priority, $accepted_args);
}

/**
 * Action çalıştırır
 */
function do_action($tag, ...$args) {
    HookSystem::getInstance()->do_action($tag, ...$args);
}

/**
 * Action kaldırır
 */
function remove_action($tag, $callback, $priority = 10) {
    return HookSystem::getInstance()->remove_action($tag, $callback, $priority);
}

/**
 * Action var mı kontrol eder
 */
function has_action($tag, $callback = null) {
    return HookSystem::getInstance()->has_action($tag, $callback);
}

/**
 * Action kaç kez çalıştırıldı
 */
function did_action($tag) {
    return HookSystem::getInstance()->did_action($tag);
}

/**
 * Filter ekler
 */
function add_filter($tag, $callback, $priority = 10, $accepted_args = 1) {
    return HookSystem::getInstance()->add_filter($tag, $callback, $priority, $accepted_args);
}

/**
 * Filter uygular
 */
function apply_filters($tag, $value, ...$args) {
    return HookSystem::getInstance()->apply_filters($tag, $value, ...$args);
}

/**
 * Filter kaldırır
 */
function remove_filter($tag, $callback, $priority = 10) {
    return HookSystem::getInstance()->remove_filter($tag, $callback, $priority);
}

/**
 * Filter var mı kontrol eder
 */
function has_filter($tag, $callback = null) {
    return HookSystem::getInstance()->has_filter($tag, $callback);
}

/**
 * Şu an çalışan hook'u döndürür
 */
function current_filter() {
    return HookSystem::getInstance()->current_hook();
}

// Alias
function current_action() {
    return current_filter();
}

