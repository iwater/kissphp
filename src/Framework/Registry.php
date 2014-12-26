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
 * 注册表类，提供全局变量
 *
 * @category  KISS
 * @package   Core
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */

class KISS_Framework_Registry
{
    private $_cache_stack;

    private function __construct()
    {
        $this->_cache_stack = array(array());
    }
    public function setEntry($key, $item)
    {
        $this->_cache_stack[0][$key] = $item;
    }
    private function _setEntryValue($key, $item)
    {
        $this->_cache_stack[0][$key] = $item;
    }
    public function &getEntry($key)
    {
        return $this->_cache_stack[0][$key];
    }
    private function _isEntry($key)
    {
        return ($this->getEntry($key) !== null);
    }
    public static function &instance()
    {
        static $registry = false;
        if (!$registry) {
            $registry = new KISS_Framework_Registry();
        }
        return $registry;
    }
    private function _save()
    {
        array_unshift($this->_cache_stack, array());
        if (!count($this->_cache_stack)) {
            trigger_error('Registry lost');
        }
    }
    private function _restore()
    {
        array_shift($this->_cache_stack);
    }
}
?>