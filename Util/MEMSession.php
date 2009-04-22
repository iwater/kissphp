<?php
/**
* @author 马涛 <matao@bj.tom.com>
* @version v 1.0 2004/04/09
* @package Core_Class
*/

class KISS_Util_MEMSession {
    static $memcache_obj;
    function open() {
        $memcache = KISS_Framework_Config::getValue('/application/memcache');
        if (!isset($memcache['host']) || !isset($memcache['port'])) {
            die('没有配置 memcache 参数！');
        }
        self::$memcache_obj = new Memcache;
        return self::$memcache_obj->connect($memcache['host'], $memcache['port']);
    }

    function close() {
        if (is_object(self::$memcache_obj)) {
            return self::$memcache_obj->close();
        }
        return false;
    }

    function read($id) {
        return self::$memcache_obj->get($id);
    }

    function write($id, $data) {
        return self::$memcache_obj->set($id, $data, MEMCACHE_COMPRESSED, 30*60);
    }

    function destroy($id) {
        return self::$memcache_obj->delete($id, 10);
    }

    function gc($lifetime) {
        return(true);
    }
}
?>