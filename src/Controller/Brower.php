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
 * 为普通浏览器服务的控制器
 *
 * @category  KISS
 * @package   Core
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */

class KISS_Controller_Brower extends KISS_Object
{
    /**
     * 构造函数
     *
     * @param string $class_name 类名
     */
    public function __construct($class_name)
    {
        parent::__construct();
        try {
            $context             = KISS_Framework_Context::getInstance();
            $context->mClassName = $this->_checkClass($class_name);
            $filter              = KISS_Class::getClassConstant($context->mClassName, 'FILTERS');
            $this->mFilters      = ($filter=='')?array():explode(',', $filter);
            $context->mFilters   = $this->mFilters;
            $context->mCacheTime = intval(KISS_Class::getClassConstant($context->mClassName, 'CACHE_TIME'));
        }
        catch (Exception $error) {
            $page = new KISS_Page();
            $page->showMessage($error->getMessage());
        }
    }

    /**
     * 析构函数
     *
     */
    public function __destruct()
    {
        parent::__destruct();
        if (KISS_Framework_Config::getMode()=='debug') {
            KISS_Util_Debug::dumpinfo();
        }
    }

    /**
     * 检查用户访问类是否存在
     *
     * @param string $class_name 类名
     *
     * @return string
     */
    private function _checkClass($class_name)
    {
        $u_name = APP_NAME.'class_'.$class_name;
        if (apc_exists($u_name)) {
            return apc_fetch($u_name);
        }
        $class_names = array('Page_User_'.$class_name, 'Page_'.$class_name, $class_name);
        foreach ($class_names as $class_name) {
            $interfaces = @class_implements($class_name);
            if (is_array($interfaces) && in_array('KISS_Interface_Runnable', $interfaces)) {
                apc_store($u_name, $class_name);
                return $class_name;
            }
        }
        //file_put_contents('/tmp/x.log', var_export($_SERVER, true), FILE_APPEND);
var_dump($class_name);
var_dump(KISS_Framework_Config::getSystemPath('root'));
var_dump(KISS_Framework_Config::getSystemPath('class'));
exit();
        file_put_contents(KISS_Framework_Config::getSystemPath('root').'/error.log', "Class {$class_name} Not Found\r\n", FILE_APPEND);
	file_put_contents(KISS_Framework_Config::getSystemPath('root').'/error.log', "SERVER:{$_SERVER['REQUEST_URI']}\r\n", FILE_APPEND);
        KISS_Util_Util::directGoToUrl("/");
    }

    /**
     * 执行入口
     *
     * @return void
     */
    public function run()
    {
        $context = KISS_Framework_Context::getInstance();
        if (count($this->mFilters) > 0) {
            $filter = array_shift($this->mFilters);
            $filter = new $filter();
            $filter->doFilter($context, $this);
        } else {
            $page = new $context->mClassName();
            $page->run();
        }
    }
}
?>
