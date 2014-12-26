<?php
/**
 * KISS 核心类文件
 *
 * PHP versions 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  KISS
 * @package   Core
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   SVN: $Id: User.php 109 2008-12-04 06:13:55Z matao $
 * @link      http://www.kissphp.cn
 */

/**
 * Cookie存储过滤器
 *
 * @category  KISS
 * @package   Core
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */

class KISS_Filter_StorageCookie extends KISS_Filter
{
    const COOKIE_PART_MAX_LENGTH = 2048;
    private $_mCookieName;

    /**
     * 前置过滤方法
     *
     * @param KISS_Framework_Context $context 上下文对象
     *
     * @return void
     */
    public function doPreProcessing(KISS_Framework_Context $context)
    {
        $this->_mCookieName = KISS_Application::getUniqueAppName();
        $temp_str           = '';
        if (!empty($_COOKIE[$this->_mCookieName])) {
            for ($i=0; $i<intval($_COOKIE[$this->_mCookieName]); $i++) {
                if (empty($_COOKIE[$this->_mCookieName.'_'.$i])) {
                    break;
                }
                $temp_str .= KISS_Util_Util::decrypt($_COOKIE[$this->_mCookieName.'_'.$i]);
            }
            $context->reBuild($temp_str);
        }
    }

    /**
     * 后置过滤方法
     *
     * @param KISS_Framework_Context $context 上下文对象
     *
     * @return void
     */
    public function doPostProcessing(KISS_Framework_Context $context)
    {
        $str_array = str_split($context->toString(), KISS_Filter_StorageCookie::COOKIE_PART_MAX_LENGTH);
        array_walk($str_array, array(&$this,'_setPartCookie'));
        setcookie($this->_mCookieName, count($str_array));
    }

    /**
     * 设置cookie
     *
     * @param string $str 需要存储的内容
     *
     * @return void
     */
    private function _setPartCookie($str)
    {
        static $count = 0;
        $cookie_name  = $this->_mCookieName.'_'.$count++;
        setcookie($cookie_name, KISS_Util_Util::encrypt($str));
    }
}
?>