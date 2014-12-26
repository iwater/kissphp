<?php
/**
*
* @author 马涛 <matao@bj.tom.com>
* @version v 2.1 alpha 2004/10/27
* @package Core_Class
*/

/**
* 配置类，提供全局变量
*/
class KISS_Framework_Config {
  private static $data = array();
  private static $config_ini_array = array();
  public static $annotation = array();

  public static function init($pConfigFile = NULL, $pRootPath = NULL) {
    if (is_null($pConfigFile)) {
      $pConfigFile = realpath(dirname($_SERVER['SCRIPT_FILENAME']).'/config.xml');
    }
    if (is_null($pRootPath)) {
      $pRootPath = realpath(dirname($_SERVER['SCRIPT_FILENAME']));
    }
    $registry = &KISS_Framework_Registry::instance();
    if(file_exists(dirname(__FILE__)."/config.xml")) {
      $default_config = simplexml_load_file(dirname(__FILE__)."/config.xml");
      $registry->setEntry('default',$default_config);
    }
    $registry->setEntry('root_path',$pRootPath);

    if(!empty($pConfigFile)) {
      if (!file_exists($pConfigFile)) {
        die('没有找到配置文件:'.$pConfigFile);
      }
      $user_config = simplexml_load_file($pConfigFile);
      foreach($user_config->attributes() as $key => $value) {
        if(property_exists('KISS_Application', $key)){
          KISS_Application::$$key=$value;
        }
      }
      $registry->setEntry('user_defined',$user_config);
      $db_configs = array();
      foreach ($user_config->databases->database as $database) {
        $db_configs[count($db_configs)] = array(
        'DatabaseType' => (string)$database['type'],
        'DatabaseHost' => (string)$database['host'],
        'DatabaseUsername' => (string)$database['username'],
        'DatabasePassword' => (string)$database['password'],
        'DatabaseName' => (string)$database['database'],
        'DatabasePort' => (int)$database['port']);
      }
      $registry->setEntry('database_connections',$db_configs);
    }
    KISS::add_include_path(self::getSystemPath('class'));
    $key = APP_NAME.'config_ini_array';
    if (xcache_isset($key)) {
      self::$config_ini_array = xcache_get($key);
    } else {
      $config_ini = self::getSystemPath('root').'/config.ini';
      if (file_exists($config_ini)) {
        self::$config_ini_array = parse_ini_file($config_ini, true);
        xcache_set($key, self::$config_ini_array);
      }
    }
    $annotation_file = realpath(KISS_Framework_Config::getSystemPath('root').'/annotation.serialize');
    if (file_exists($annotation_file)) {
      self::$annotation = unserialize(file_get_contents($annotation_file));
    }
  }

  public static function haveUrlToClassMapping($pUrl) {
    return isset(self::$config_ini_array['url_mapto_class'][$pUrl]);
  }

  public function getUrlToClassMapping($pUrl) {
    $key = APP_NAME.'umc:'.$pUrl;
    if (xcache_isset($key)) {
      return xcache_get($key);
    }
    return self::$config_ini_array['url_mapto_class'][$pUrl];
  }

  function getDBConfig($pDBConfig = 0) {
    $registry = &KISS_Framework_Registry::instance();
    $db_configs = $registry->getEntry('database_connections');
    return $db_configs[$pDBConfig];
  }

  function getSessionDBConfig($pDBConfig = 0) {
    $registry = &KISS_Framework_Registry::instance();
    $user_config = $registry->getEntry('user_defined');
    return (strtolower((string)$user_config->session[cache])=='true');
  }

  static function getSystemPath($pPathName) {
    $u_name = APP_NAME.$pPathName;
    if (xcache_isset($u_name)) {
      return xcache_get($u_name);
    }
    if (isset(self::$data['path'][$pPathName])) {
      return self::$data['path'][$pPathName];
    }
    $registry = &KISS_Framework_Registry::instance();
    $default_config = $registry->getEntry('default');
    $user_config = $registry->getEntry('user_defined');
    $paths = array(  (string)$user_config->system_path->$pPathName,
    $registry->getEntry('root_path').'/'.(string)$user_config->system_path->$pPathName,
    (string)$default_config->system_path->$pPathName,
    $registry->getEntry('root_path').'/'.(string)$default_config->system_path->$pPathName);
    foreach ($paths as $path) {
      if(file_exists($path) && is_writable($path)) {
        self::$data['path'][$pPathName] = realpath($path);
        xcache_set($u_name, realpath($path));
        return realpath($path);
      }
    }
  }

  /**
   * 是否缓存
   *
   * @return bool
   */
  public static function isCached() {
    $registry = &KISS_Framework_Registry::instance();
    $user_config = $registry->getEntry('user_defined');
    return (strtolower((string)$user_config->pages['cache'])=='true');
  }

  static function getValue($pXpath, $pProperty = null ,$pType = "string") {
    $registry = &KISS_Framework_Registry::instance();
    $user_config = $registry->getEntry('user_defined');
    $element = $user_config->xpath($pXpath);
    if (is_null($pProperty)) {
      $return = array();
      if (count($element) > 0) {
        foreach ($element[0]->attributes() as $key => $value) {
          $return[$key] = (string)$value;
        }
      }

      return $return;
    } else {
      $return = $element[0][$pProperty];
      if ($pType == "boolean") {
        return ('true' == (string)$return);
      } else {
        settype($return, $pType);
        return $return;
      }
    }
  }

  static function getArray($pXpath) {
    $key = md5($pXpath);
    if (isset(self::$data[$key])) {
      return self::$data[$key];
    }
    $registry = &KISS_Framework_Registry::instance();
    $user_config = $registry->getEntry('user_defined');
    $elements = $user_config->xpath($pXpath);
    $return = array();
    foreach ($elements[0] as $element) {
      foreach ($element->attributes() as $key => $value) {
        $item[$key] = (string)$value;
      }
      $return[] = $item;
    }
    self::$data[$key] = $return;
    return $return;
  }

  /**
     * Get Root Path(WEB-INF's Path)
     */
  function getRootPath() {
    $registry = &KISS_Framework_Registry::instance();
    return $registry->getEntry('root_path');
  }

  /**
   * 获取当前项目的运行模式
   * 目前支持两种模式:debug和online,默认是online
   */
  static function getMode() {
    if (isset($_COOKIE['kiss_debug']) && 1 == $_COOKIE['kiss_debug']) {
      return "debug";
    } else {
      return "online";
    }
  }

  static function getDebugIP() {
    $application = self::getValue('//application');
    if (!isset($application['debug_ip'])) {
      $application['debug_ip'] = '';
    }
    return array_merge(array('127.0.0.1', $_SERVER['SERVER_ADDR']), explode(',', $application['debug_ip']));
  }

  function getParam($name) {
    $params = self::getArray("//application/params");
    if(!is_null($params)) {
      foreach($params as $row) {
        if($row["name"]==$name) {
          return iconv("UTF-8", "GB18030", $row["value"]);
        }
      }
    }
    return "";
  }
}
?>