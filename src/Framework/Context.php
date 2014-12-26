<?php
class KISS_Framework_Context {
    static private $theInstance = null;
    public $mStorage;
    
    private function __construct() {
        $this->mStorage = new KISS_Framework_Storage();
    }
    
    static public function getInstance() {
        if (is_null(self::$theInstance)) {
            self::$theInstance = new KISS_Framework_Context();
        }
        return self::$theInstance;
    }
    
    public function toString() {
        return serialize($this->mStorage);
    }
    
    public function reBuild($pString) {
        $this->mStorage = unserialize($pString);
    }
}
?>