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
 * KISS 核心类
 *
 * @category  Core
 * @package   KISS
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */
class KISS
{
    private $_auto_load_cache_file;
    private static $_load_array      = array();
    private static $_new_class_found = false;

    /**
     * 构造函数
     *
     * @param string $pConfigFile 配置文件名
     * @param string $pRootPath   根目录位置
     */
    public function __construct($pConfigFile = null, $pRootPath = null)
    {
        $cache_path = '/dev/shm/temp/cache';//sys_get_temp_dir();

        if (file_exists($cache_path) && is_writable($cache_path)) {
            $front                       = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
            $this->_auto_load_cache_file = realpath($cache_path).DIRECTORY_SEPARATOR.'kiss_'.md5($front.$_SERVER['PHP_SELF']);
        }
        if (!is_null($this->_auto_load_cache_file) && file_exists($this->_auto_load_cache_file)) {
            self::$_load_array = unserialize(file_get_contents($this->_auto_load_cache_file));
            foreach (self::$_load_array as $file) {
                if (file_exists($file)) {
                    include_once $file;
                }
            }
        }
        KISS_Framework_Config::init($pConfigFile, $pRootPath);
    }

    /**
     * 析构函数
     *
     */
    public function __destruct()
    {
        if (self::$_new_class_found && !is_null($this->_auto_load_cache_file) && KISS_Framework_Config::getMode()=="online") {
            file_put_contents($this->_auto_load_cache_file, serialize(self::$_load_array));
        }
    }

    /**
     * 自动加载类对应的文件
     *
     * @param string $package_name 类名
     *
     * @return void
     */
    public static function autoload($package_name)
    {
        $u_name = APP_NAME.'al:'.$package_name;
        if (KISS::enableCache() && apc_exists($u_name)) {
            $filename = apc_fetch($u_name);
            include_once $filename;
            self::$_load_array[$package_name] = $filename;
            self::$_new_class_found           = true;
            return;
        }
        $package_array = preg_split('/_/', $package_name);
        $file_array[]  = join('/', $package_array);
        $file_array[]  = strtolower($file_array[0]);
        array_push($package_array, 'class.'.array_pop($package_array));
        $file_array[] = join('/', $package_array);
        $file_array[] = strtolower($file_array[2]);

        $path_array = explode(PATH_SEPARATOR, ini_get('include_path'));
        foreach ($path_array as $path) {
            foreach ($file_array as $file) {
                $filename = "{$path}/{$file}.php";
                if (file_exists($filename)) {
                    include_once $filename;
                    if (KISS::enableCache()) {
                        apc_store($u_name, $filename);
                    }
                    self::$_load_array[$package_name] = $filename;
                    self::$_new_class_found           = true;
                    return;
                }
            }
        }
    }

    /**
     * 是否启用Cache
     *
     * @return bool
     */
    public static function enableCache()
    {
        return ('cli' != php_sapi_name());
    }

    /**
     * 获得类加载的路径
     *
     * @param string $package_name 类名
     *
     * @return string
     */
    public static function getClassPath($package_name)
    {
        return self::$_load_array[$package_name];
    }

    /**
     * 添加目录到系统自动加载目录中
     *
     * @param string $path 路径
     *
     * @return void
     */
    public static function addIncludePath($path)
    {
        $path_array = explode(PATH_SEPARATOR, ini_get('include_path'));
        array_unshift($path_array, $path);
        ini_set('include_path', implode(PATH_SEPARATOR, array_unique($path_array)));
    }

    /**
     * 框架入口函数
     *
     * @return void
     */
    public function serve()
    {
        try {
            new KISS_Controller();
        } catch (Exception $e) {
var_dump($e);
            $page = new KISS_Page();
            $page->showMessage($e->getMessage());
        }
    }
}
if (!defined('APP_NAME')) {
    $call_stack = debug_backtrace();
    define('APP_NAME', md5($call_stack[0]['file']));
}
assert(!function_exists('__autoload'));
date_default_timezone_set('PRC');

KISS::addIncludePath(realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'));
KISS::addIncludePath(dirname(__FILE__).DIRECTORY_SEPARATOR.'Compatible');
spl_autoload_register(array('KISS', 'autoload'));
?>
