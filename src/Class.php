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
 * KISS_Class
 *
 * @category  Core
 * @package   KISS
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */
class KISS_Class
{
    /**
     * 取得类的静态成员
     *
     * @param string $pClass  类名
     * @param string $pMember 成员名
     *
     * @return mix
     */
    public static function getClassStaticMember ($pClass, $pMember)
    {
        $return = '';
        $script = "if(isset({$pClass}::\${$pMember})){\$return = {$pClass}::\${$pMember};}";
        eval($script);
        return $return;
    }
    /**
     * 取得类的常量成员
     *
     * @param string $pClass  类名
     * @param string $pMember 成员名
     *
     * @return mix
     */
    public static function getClassConstant ($pClass, $pMember)
    {
        $u_name = APP_NAME . 'cc:' . $pClass . ':' . $pMember;
        if (apc_exists($u_name)) {
            return apc_fetch($u_name);
        }
        $const = "{$pClass}::{$pMember}";
        if (defined($const)) {
            apc_store($u_name, constant($const));
            return constant($const);
        }
        apc_store($u_name, '');
        return '';
    }
}
?>
