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
 * Cache 的 SQL 实现
 *
 * @category  Core
 * @package   KISS
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */
class KISS_Util_Cache
{
    /**
     * 添加缓存
     *
     * @param string $pSql       SQL
     * @param int    $pTimeStamp 时间戳
     * @param array  $pResult    结果集
     *
     * @return void
     */
    function setSqlCache ($pSql, $pTimeStamp, $pResult)
    {
        Cache::removeSqlCache($pSql);
        file_put_contents(Cache::_getSqlCacheFileName($pSql, $pTimeStamp), serialize($pResult));
    }
    /**
     * 是否存在对应缓存
     *
     * @param string $pSql       SQL
     * @param int    $pTimeStamp 时间戳
     *
     * @return bool
     */
    function haveSqlCache ($pSql, $pTimeStamp)
    {
        return file_exists(Cache::_getSqlCacheFileName($pSql, $pTimeStamp));
    }
    /**
     * 得到SQL结果缓存
     *
     * @param string $pSql       SQL
     * @param int    $pTimeStamp 时间戳
     *
     * @return array
     */
    function getSqlCache ($pSql, $pTimeStamp)
    {
        return unserialize(file_get_contents(Cache::_getSqlCacheFileName($pSql, $pTimeStamp)));
    }
    /**
     * 得到SQL结果缓存文件名
     *
     * @param string $pSql       SQL
     * @param int    $pTimeStamp 时间戳
     *
     * @return string
     */
    private function _getSqlCacheFileName ($pSql, $pTimeStamp)
    {
        return KISS_Framework_Config::getSystemPath('temp') . "/cache/cache." . md5($pSql) . "." . md5($pTimeStamp) . ".serialize";
    }
    /**
     * 删除SQL结果缓存
     *
     * @param string $pSql SQL
     *
     * @return void
     */
    function removeSqlCache ($pSql)
    {
        $files = glob(KISS_Framework_Config::getSystemPath('temp') . "/cache/cache." . md5($pSql) . ".*.serialize");
        if (is_array($files) && count($files) > 0) {
            foreach ($files as $filename) {
                unlink($filename);
            }
        }
    }
}
?>