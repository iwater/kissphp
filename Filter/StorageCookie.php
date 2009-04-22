<?php
/**
 * @author ÂíÌÎ <matao@bj.tom.com>
 * @version v 1.0 2005/10/18
 * @package Filter
 */
class KISS_Filter_StorageCookie extends KISS_Filter {
    const COOKIE_PART_MAX_LENGTH = 2048;
    private $mCookieName;
    public function doPreProcessing(KISS_Framework_Context $context) {
        $this->mCookieName = KISS_Application::getUniqueAppName();
        $temp_str = '';
        if (!empty($_COOKIE[$this->mCookieName])) {
            for ($i=0; $i<intval($_COOKIE[$this->mCookieName]); $i++) {
                if (empty($_COOKIE[$this->mCookieName.'_'.$i])) {
                    break;
                }
                $temp_str .= KISS_Util_Util::decrypt($_COOKIE[$this->mCookieName.'_'.$i]);
            }
            $context->reBuild($temp_str);
        }
    }

    public function doPostProcessing(KISS_Framework_Context $context) {
        $str_array = str_split($context->toString(), KISS_Filter_StorageCookie::COOKIE_PART_MAX_LENGTH);
        array_walk($str_array,array(&$this,'set_part_cookie'));
        setcookie($this->mCookieName, count($str_array));
    }
    
    private function set_part_cookie($str) {
        static $count=0;
        $cookie_name = $this->mCookieName.'_'.$count++;
        setcookie($cookie_name, KISS_Util_Util::encrypt($str));
    }
}
?>