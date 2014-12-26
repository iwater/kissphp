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
 * KISS_Proxy
 *
 * @category  KISS
 * @package   Core
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */
class KISS_Proxy
{
    public $mObject      = null;
    public $mCurrentUser = null;
    /**
     * 构造函数
     *
     * @param Object              $pClassInstance 被代理的类实例
     * @param KISS_Interface_User $pUser          执行该调用的用户实例
     */
    public function __construct ($pClassInstance, $pUser)
    {
        $this->mObject      = $pClassInstance;
        $this->mCurrentUser = $pUser;
    }
    /**
     * 调用方法
     *
     * @param string $method     方法名
     * @param array  $parameters 参数
     *
     * @return mix
     */
    protected function invoke ($method, $parameters)
    {
        if (KISS_Util_Permission::invokePermissions($this->mCurrentUser, $this->mObject, $method)) {
            return call_user_func_array(array(
                $this->mObject,
                $method), $parameters);
        } else {
            throw new Exception("权限不足");
        }
    }
    /**
     * PHP魔法函数
     *
     * @param string $member 属性名
     * @param mix    $value  属性值
     *
     * @return void
     */
    function __set ($member, $value)
    {
        $this->mObject->$member = $value;
    }
    /**
     * PHP魔法函数
     *
     * @param string $member 属性名
     *
     * @return mix
     */
    function __get ($member)
    {
        return $this->mObject->$member;
    }
    /**
     * PHP魔法函数
     *
     * @param string $method    方法名
     * @param array  $paraments 参数数组
     *
     * @return mix
     */
    function __call ($method, $paraments)
    {
        return $this->invoke($method, $paraments);
    }
}
?>