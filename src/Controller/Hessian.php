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
 * 提供Hessian服务的控制器
 *
 * @category  KISS
 * @package   Core
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */

class KISS_Controller_Hessian
{
    private $_instance;
    /**
     * 构造函数
     *
     * @param string $class_name 类名
     */
    public function __construct($class_name)
    {
        if (class_exists($class_name)) {
            try {
                $parameters = array();
                if (isset($_SERVER['argv'])) {
                    parse_str(implode('&', $_SERVER['argv']), $parameters);
                }
                $this->_instance = new $class_name($parameters);
            } catch (Exception $error) {
                $this->showMessage($error->getMessage());
            }
        } else {
            $this->showMessage('缺少类！'.$class_name);
        }
    }

    /**
     * 命令行入口函数
     *
     * @return mix
     */
    public function run()
    {
        if ($this->_instance) {
            $wrapper = &new HessianService();
            $wrapper->registerObject($this->_instance);
            $wrapper->displayInfo = true;
            $wrapper->service();
        }
    }

    /**
     * 显示消息提示
     *
     * @param string $pMessage 消息
     *
     * @return void
     */
    public function showMessage($pMessage)
    {
        echo $pMessage."\n";
    }
}
?>