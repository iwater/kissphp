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
 * Permission 权限管理类库
 *
 * @category  Core
 * @package   KISS
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */
class KISS_Util_Permission
{
    public $mPermissions = array(
        1 => '人员及其他管理模块',
        2 => '收入管理系统浏览',
        4 => '支出管理系统录入',
        8 => '支出管理系统浏览',
        16 => '群发收入数据统计');
    function getPermissions ()
    {
        return $this->mPermissions;
    }
    function mergePermissions ($pPermissions)
    {
        if (count($pPermissions) == 0) {
            return 0;
        }
        $my_permission = 0;
        foreach ($pPermissions as $permission) {
            $my_permission = $my_permission | $permission;
        }
        return $my_permission;
    }
    function authorization ($pPermission, $pUserPermission)
    {
        if ($pPermission == ($pPermission & $pUserPermission)) {
            return true;
        }
        return false;
    }
    public static function invokePermissions ($pUser, $pObject, $pMethod)
    {
        return true;
        $pUser               = new User();
        $registry            = KISS_Framework_Registry::instance();
        $permissions         = $registry->getEntry('user_defined');
        $permissions_default = $registry->getEntry('default');
        $permission_array    = array(
            $permissions->xpath('/application/permission/user[@role="' . $pUser->getRole() . '"]/class[@name="' . get_class($pObject) . '"]/method[@name="' . $pMethod . '"]'),
            $permissions->xpath('/application/permission/user[@role="' . $pUser->getRole() . '"]/class[@name="' . get_class($pObject) . '"]'),
            $permissions->xpath('/application/permission/user[@role="' . $pUser->getRole() . '"]'),
            $permissions->xpath('/application/permission/user[@role="default"]'));//$permissions_default->xpath('/application/permission/user[@role="default"]')

        foreach ($permission_array as $permission) {
            if ($permission && count($permission) == 1) {
                return ((string) $permission[0]['access'] == "true");
            }
        }
        return false;
    }
}
?>