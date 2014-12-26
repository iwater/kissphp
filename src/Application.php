<?php
/**
 * 定义全局应用容器
 *
 * PHP versions 5
 *
 * @category KISS
 * @package  Application
 * @author   iwater <iwater@gmail.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php BSD
 * @link     http://kissphp.cn
 *
 */

/**
 * 定义全局应用容器
 *
 * @category KISS
 * @package  Application
 * @author   iwater <iwater@gmail.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php BSD
 * @link     http://kissphp.cn
 */
class KISS_Application
{
    const ON  = 1;
    const OFF = 0;

    public static $session_status = self::OFF;
    public static $charset        = 'gbk';
    public static $mode           = 'online';

    /**
     * session 初始化
     *
     * @return void
     */
    public static function sessionStart()
    {
        if (self::OFF == self::$session_status) {
            session_name(self::getUniqueAppName());
            session_start();
            self::$session_status = self::ON;
        }
    }

    /**
     * 取得当前应用的唯一标示串
     *
     * @return string
     */
    public static function getUniqueAppName()
    {
        $root_path = KISS_Framework_Config::getSystemPath('root');
        return 'A'.strtoupper(substr(md5($root_path), 0, 7));
    }
}
?>