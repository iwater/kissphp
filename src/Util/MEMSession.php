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
 * Memcache 管理 Session
 *
 * @category  Core
 * @package   KISS
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */
class KISS_Util_MEMSession
{
    static $memcache_obj;
    /**
     * 实现Session接口
     *
     * @return bool
     */
    function open ()
    {
        $memcache = KISS_Framework_Config::getValue('/application/memcache');
        if (! isset($memcache['host']) || ! isset($memcache['port'])) {
            die('没有配置 memcache 参数！');
        }
        self::$memcache_obj = new Memcache();
        return self::$memcache_obj->connect($memcache['host'], $memcache['port']);
    }
    /**
     * 实现Session接口
     *
     * @return bool
     */
    function close ()
    {
        if (is_object(self::$memcache_obj)) {
            return self::$memcache_obj->close();
        }
        return false;
    }
    /**
     * 实现Session接口
     *
     * @param string $id SessionID
     *
     * @return mix
     */
    function read ($id)
    {
        return self::$memcache_obj->get($id);
    }
    /**
     * 实现Session接口
     *
     * @param string $id   SessionID
     * @param mix    $data SessionData
     *
     * @return bool
     */
    function write ($id, $data)
    {
        return self::$memcache_obj->set($id, $data, MEMCACHE_COMPRESSED, 30 * 60);
    }
    /**
     * 实现Session接口
     *
     * @param string $id SessionID
     *
     * @return void
     */
    function destroy ($id)
    {
        return self::$memcache_obj->delete($id, 10);
    }
    /**
     * 实现Session接口
     *
     * @param int $lifetime 清理间隔
     *
     * @return bool
     */
    function gc ($lifetime)
    {
        return (true);
    }
}
?>