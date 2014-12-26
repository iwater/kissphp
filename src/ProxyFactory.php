<?PHP
/**
* @author matao <matao@bj.tom.com>
* @version v 1.0 2003/12/04
* @package Core_Class
*/

/**
* ProxyFactory 代理工厂类
*/
class KISS_ProxyFactory {
    public static function getInstance($pClassInstance, $pUser) {
        $class_name = get_class($pClassInstance);
        
        $proxy_class_name = "{$class_name}Proxy";
        if (class_exists($proxy_class_name)) {
            return new $proxy_class_name($pClassInstance,$pUser);
        }
        else {
            return new KISS_Proxy($pClassInstance,$pUser);
        }
    }
}
?>