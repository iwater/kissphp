<?php
/**
 * $Id: Runnable.php 10 2007-03-09 16:42:11Z matao $
 * @package KISS
 */
/**
 * 可以直接运行的程序必须实现此接口
 * @package KISS
 */
class KISS_ApplicationCache {
  const ON = 1;
  const OFF = 0;

  public static $session_status = self::OFF;

  public static function session_start() {
    if (self::OFF == self::$session_status) {
      session_name(self::getUniqueAppName());
      session_start ();
      self::$session_status = self::ON;
    }
  }

  public static function getUniqueAppName() {
    if (xcache_isset('app_name')) {
        return xcache_get('app_name');
    }
    $app_name = 'A'.strtoupper(substr(md5(KISS_Framework_Config::getSystemPath('root')),0,7));
    if (!xcache_set('app_name', $app_name)) {
        
    }
    return $app_name;
  }

  public static function getSystemPath($pPathName) {
    if (!xcache_isset('sp_'.$pPathName)) {
      xcache_set("count", load_count_from_mysql());
    }
  }
}
?>
