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
 * 缓存过滤器
 *
 * @category  KISS
 * @package   Core
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */

class KISS_Filter_Cache extends KISS_Filter
{
    private $_mCacheHandle;
    
    /**
     * 前置过滤方法
     *
     * @return void
     */
    public function doPreProcessing()
    {
        $cache_time = KISS_Framework_Context::getInstance()->mCacheTime;
        if ($cache_time > 0) {
            $this->_mCacheHandle = new KISS_Util_FileCache($_SERVER['REQUEST_URI'], $cache_time);
            if ($this->_mCacheHandle->checkCacheStatus()) {
                echo $this->_mCacheHandle->getCacheContent();
                exit();
            }
        }
        if (0 == ob_get_level()) {
            ob_start();
        }
    }

    /**
     * 后置过滤方法
     *
     * @return void
     */
    public function doPostProcessing()
    {
        $echo = ob_get_contents();
        ob_flush();
        $this->_mCacheHandle->putCacheContent($echo);
    }
}
?>