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
 * @category  Core
 * @package   KISS
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   SVN: <svn_id>
 * @link      http://www.kissphp.cn
 */

/**
 * KISS_Util_FileCache
 *
 * @category  Core
 * @package   KISS
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */
class KISS_Util_FileCache
{
    private $_mCacheKey;
    private $_mCacheTime = 0; //时间单位为秒，0为无时间限制
    /**
     * 构造函数
     *
     * @param string $pCacheKey  KEY
     * @param int    $pCacheTime 缓存时间
     */
    public function __construct ($pCacheKey, $pCacheTime = 0)
    {
        $this->_mCacheKey  = md5($pCacheKey);
        $this->_mCacheTime = $pCacheTime;
    }
    /**
     * 检查缓存是否可用
     *
     * @return bool
     */
    public function checkCacheStatus ()
    {
        if (file_exists($this->_getCacheFileName()) && (filemtime($this->_getCacheFileName()) + $this->_mCacheTime > time() || $this->_mCacheTime == 0)) {
            return true;
        }
        return false;
    }
    /**
     * 得到缓存内容
     *
     * @return string
     */
    public function getCacheContent ()
    {
        return file_get_contents($this->_getCacheFileName());
    }
    /**
     * 保存
     *
     * @param string $pContent 正文
     *
     * @return void
     */
    public function putCacheContent ($pContent)
    {
        file_put_contents($this->_getCacheFileName(), $pContent);
    }
    /**
     * 取得缓存文件名
     *
     * @return string
     */
    private function _getCacheFileName ()
    {
    	//var_dump(KISS_Framework_Config::getSystemPath('temp') );
    	//exit();
        return KISS_Framework_Config::getSystemPath('temp') . "/cache/kiss_cache_" . $this->_mCacheKey;
    }
}
?>