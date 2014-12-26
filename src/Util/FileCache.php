<?php
class KISS_Util_FileCache {
    private $mCacheKey;
    private $mCacheTime = 0; //时间单位为秒，0为无时间限制

    public function __construct($pCacheKey, $pCacheTime = 0) {
        $this->mCacheKey = md5($pCacheKey);
        $this->mCacheTime = $pCacheTime;
    }
    
    public function check_cache_status() {
        if (file_exists($this->get_cache_file_name()) && (filemtime($this->get_cache_file_name()) + $this->mCacheTime > time() || $this->mCacheTime == 0)) {
            return true;
        }
        return false;
    }
    
    public function get_cache_content() {
        return file_get_contents($this->get_cache_file_name());
    }
    
    public function put_cache_content($pContent) {
        file_put_contents($this->get_cache_file_name(), $pContent);
    }
    
    private  function get_cache_file_name() {
        return KISS_Framework_Config::getSystemPath('temp')."/cache/kiss_cache_".$this->mCacheKey;
    }
}
?>