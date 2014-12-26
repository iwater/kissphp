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
 * ProxyFactory 代理工厂类
 *
 * @category  Core
 * @package   KISS
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */
class KISS_ProxyFactory
{
    /**
     * 得到实例
     *
     * @param Object              $pClassInstance 被代理的类实例
     * @param KISS_Interface_User $pUser          执行该调用的用户实例
     *
     * @return unknown
     */
    public static function getInstance ($pClassInstance, $pUser)
    {
        $class_name       = get_class($pClassInstance);
        $proxy_class_name = "{$class_name}Proxy";
        if (class_exists($proxy_class_name)) {
            return new $proxy_class_name($pClassInstance, $pUser);
        } else {
            return new KISS_Proxy($pClassInstance, $pUser);
        }
    }
}
?>