<?php
/**
*
* @author 马涛 <matao@bj.tom.com>
* @version v 2.1 alpha 2004/10/27
* @package Core_Class
*/

/**
* 注册表类，提供全局变量
*/
class KISS_Framework_Registry {
    private $_cache_stack;

    function __construct() {
        $this->_cache_stack = array(array());
    }
    function setEntry($key, $item) {
        $this->_cache_stack[0][$key] = $item;
    }
    function setEntryValue($key, $item) {
        $this->_cache_stack[0][$key] = $item;
    }
    function &getEntry($key) {
        return $this->_cache_stack[0][$key];
    }
    function isEntry($key) {
        return ($this->getEntry($key) !== null);
    }
    static function &instance() {
        static $registry = false;
        if (!$registry) {
            $registry = new KISS_Framework_Registry();
        }
        return $registry;
    }
    function save() {
        array_unshift($this->_cache_stack, array());
        if (!count($this->_cache_stack)) {
            trigger_error('Registry lost');
        }
    }
    function restore() {
        array_shift($this->_cache_stack);
    }
}
?>