<?php
/**
 * @author бМлн <matao@bj.tom.com>
 * @version v 1.0 2005/10/18
 * @package Filter
 */
class KISS_Filter_Cache extends KISS_Filter {
    private $mCacheHandle;
    public function doPreProcessing() {
        $cache_time = KISS_Framework_Context::getInstance()->mCacheTime;
        if($cache_time > 0) {
            $this->mCacheHandle = new KISS_Util_FileCache($_SERVER['REQUEST_URI'], $cache_time);
            if ($this->mCacheHandle->check_cache_status()) {
                echo $this->mCacheHandle->get_cache_content();
                exit();
            }
        }
        if (0 == ob_get_level()) {
            ob_start();
        }
    }

    public function doPostProcessing() {
        $echo = ob_get_contents();
        ob_flush();
        $this->mCacheHandle->put_cache_content($echo);
    }
}
?>