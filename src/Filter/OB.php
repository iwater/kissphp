<?php
/**
 * @author бМлн <matao@bj.tom.com>
 * @version v 1.0 2005/10/18
 * @package Filter
 */
class KISS_Filter_OB extends KISS_Filter {
    public function doPreProcessing() {
        if(isset($_SERVER['HTTP_ACCEPT_ENCODING']) && ereg('gzip',$_SERVER['HTTP_ACCEPT_ENCODING'])) {
            ob_start("ob_gzhandler");
        } else {
            ob_start();
        }
    }
    
    public function doPostProcessing() {
        ob_end_flush();
    }
}
?>