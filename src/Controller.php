<?php
/**
 * 分发器,根据不同的客户端调用不同的 Controller
 *
 * PHP versions 5
 *
 * @category KISS
 * @package  Controller
 * @author   iwater <iwater@gmail.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php BSD
 * @link     http://kissphp.cn
 *
 */

/**
 * 分发器,根据不同的客户端调用不同的 Controller
 *
 * @category KISS
 * @package  Controller
 * @author   iwater <iwater@gmail.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php BSD
 * @link     http://kissphp.cn
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
                    session_save_path("tcp://{$memcache['host']}:{$memcache['port']}");
                    session_module_name('memcache');
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
            if (strtolower(substr($_SERVER['SERVER_SOFTWARE'], 0, 6)) == 'apache') {
                preg_match("/^(_([^_]+)_){0,1}([^\/]*?)(\.|$)/i",
                    basename($_SERVER['PHP_SELF']), $matches);
            } else {
                preg_match("/^(_([^_]+)_){0,1}([^\/]*?)(\.|\?|$)/i",
                    basename($_SERVER['REQUEST_URI']), $matches);
            }
            if (!isset($Controller)) {
                $Controller = "KISS_Controller_{$matches[2]}";
            }
            if ($Controller == 'KISS_Controller_' || !class_exists($Controller)) {
                $Controller = "KISS_Controller_Brower";
            }
            $class_name             = implode('_',
                array_map('ucfirst', explode('_', $matches[3])));
            $this->_innerController = new $Controller($class_name);
        }
        $this->_innerController->run();
    }
}
?>