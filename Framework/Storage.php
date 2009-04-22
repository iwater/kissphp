<?php
class KISS_Framework_Storage {
    private $mStorage = array();

    public function __get($pKey){
        if (array_key_exists($pKey, $this->mStorage)) {
            return $this->mStorage[$pKey];
        }
        return null;
    }
    
    public function __set($pKey, $pValue) {
        $this->mStorage[$pKey] = $pValue;
    }
}
?>