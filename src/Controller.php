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
 * 控制器
 *
 * @category  Core
 * @package   KISS
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */
class KISS_Controller
{
    private $_innerController;
    /**
     * 构造函数
     *
     */
    function __construct()
    {
        if ('cli' == php_sapi_name()) {
            $this->_innerController = new KISS_Controller_Cli();
        } else {
            $application = KISS_Framework_Config::getValue('//application');
            session_name(strtoupper(substr(MD5(KISS_Framework_Config::getSystemPath('root')), 0, 8)));
            if (isset($application['session'])) {
                switch ($application['session']) {
                case 'disable':
                    break;

                case 'memcache':
                    $memcache = KISS_Framework_Config::getValue('/application/memcache');
                    if (!isset($memcache['host']) || !isset($memcache['port'])) {
                        die('没有配置 memcache 参数！');
                    }
                    session_save_path("tcp://{$memcache['host']}:{$memcache['port']}?persistent=1&weight=1&timeout=1&retry_interval=5");
                    session_module_name('memcache');
                    KISS_Application::sessionStart();
                    break;

                case 'redis':
                    $server = KISS_Framework_Config::getValue('/application/session_redis');
                    if (!isset($server['host']) || !isset($server['port'])) {
                        die('没有配置 memcache 参数！');
                    }
                    session_save_path("tcp://{$server['host']}:{$server['port']}?persistent=1&weight=1&timeout=1&database={$server['database']}");
                    session_module_name('redis');
                    KISS_Application::sessionStart();
                    break;

                default:
                    KISS_Application::sessionStart();
                    break;
                }
            } else {
                KISS_Application::sessionStart();
            }
            if (isset($_SERVER['HTTP_KISS_RPC']) && in_array($_SERVER['HTTP_KISS_RPC'], array('JSON')) && class_exists('KISS_Controller_'.$_SERVER['HTTP_KISS_RPC'])) {
                $Controller = 'KISS_Controller_'.$_SERVER['HTTP_KISS_RPC'];
            }
            if(strpos($_SERVER['PHP_SELF'], '/WEB-INF/') !== false) $_SERVER['PHP_SELF'] =  $_SERVER['DOCUMENT_URI'];
            preg_match("/^(_([^_]+)_){0,1}([^\/]*?)(\.|$)/i", basename($_SERVER['PHP_SELF']), $matches);
            if (!isset($Controller)) {
                $Controller = "KISS_Controller_{$matches[2]}";
            }
            if ($Controller == 'KISS_Controller_' || !class_exists($Controller)) {
                $Controller = "KISS_Controller_Brower";
            }
            $class_name             = implode('_', array_map('ucfirst', explode('_', $matches[3])));
            $this->_innerController = new $Controller($class_name);
        }
        $this->_innerController->run();
    }
}
?>
