<?php
/**
* @author 马涛 <matao@bj.tom.com>
* @version v 1.0 2004/03/05
* @package Core_Class
*/

/**
* Permission 权限管理类库
*/
class KISS_Util_Permission {
    public $mPermissions = array (
    1 => '人员及其他管理模块',
    2 => '收入管理系统浏览',
    4 => '支出管理系统录入',
    8 => '支出管理系统浏览',
    16 => '群发收入数据统计',
    );
    
    function getPermissions() {
        return $this->mPermissions;
    }
    
    function mergePermissions($pPermissions) {
        if(count($pPermissions)==0) {
            return 0;
        }
        $my_permission = 0;
        foreach($pPermissions as $permission) {
            $my_permission = $my_permission | $permission;
        }
        return $my_permission;
    }
    
    function authorization($pPermission,$pUserPermission) {
        if($pPermission == ($pPermission & $pUserPermission)) {
            return true;
        }
        return false;
    }
    
    public static function InvokePermissions($pUser, $pObject, $pMethod) {
        return true;
        $pUser = new User();
        $registry = KISS_Framework_Registry::instance();
        $permissions = $registry->getEntry('user_defined');
        $permissions_default = $registry->getEntry('default');
        $permission_array = array(    $permissions->xpath('/application/permission/user[@role="'.$pUser->getRole().'"]/class[@name="'.get_class($pObject).'"]/method[@name="'.$pMethod.'"]'),
                        $permissions->xpath('/application/permission/user[@role="'.$pUser->getRole().'"]/class[@name="'.get_class($pObject).'"]'),
                        $permissions->xpath('/application/permission/user[@role="'.$pUser->getRole().'"]'),
                        $permissions->xpath('/application/permission/user[@role="default"]'),
                        //$permissions_default->xpath('/application/permission/user[@role="default"]')
                    );
        foreach ($permission_array as $permission) {
            if($permission && count($permission) == 1) {
                return ((string)$permission[0]['access'] == "true") ;
            }
        }
        return false;
    }
}
?>