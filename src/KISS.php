<?PHP
class KISS {
  private $auto_load_cache_file;
  private static $load_array = array();
  private static $new_class_found = false;

  public function __construct() {
    if (('WINNT' == PHP_OS) && getenv('TEMP')) {
      $cache_path = getenv('TEMP');
    } else {
      $cache_path = '/tmp';
    }

    if (file_exists($cache_path) && is_writable($cache_path)) {
      $front = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
      $this->auto_load_cache_file = realpath($cache_path.'/kiss_'.md5($front.$_SERVER['PHP_SELF']));
    }
    if (!is_null($this->auto_load_cache_file) && file_exists($this->auto_load_cache_file)) {
      self::$load_array = unserialize(file_get_contents($this->auto_load_cache_file));
      foreach (self::$load_array as $file) {
        if (file_exists($file)) {
          require_once($file);
        }
      }
    }
    KISS_Framework_Config::init();
  }

  public function __destruct() {
    if (self::$new_class_found && !is_null($this->auto_load_cache_file) && KISS_Framework_Config::getMode()=="online") {
      file_put_contents($this->auto_load_cache_file, serialize(self::$load_array));
    }
  }

  public static function _autoload($package_name) {
    $u_name = APP_NAME.'al:'.$package_name;
    if (xcache_isset($u_name)) {
      $filename = xcache_get($u_name);
      require_once($filename);
      self::$load_array[$package_name] = $filename;
      self::$new_class_found = true;
      return;
    }
    $package_array = split('_', $package_name);
    $file_array[] = join('/',$package_array);
    $file_array[] = strtolower($file_array[0]);
    array_push($package_array,'class.'.array_pop($package_array));
    $file_array[] = join('/',$package_array);
    $file_array[] = strtolower($file_array[2]);

    $path_array = explode(PATH_SEPARATOR, ini_get('include_path'));
    foreach ($path_array as $path) {
      foreach ($file_array as $file) {
        $filename = "{$path}/{$file}.php";
        if (file_exists($filename)) {
          require_once($filename);
          xcache_set($u_name,$filename);
          self::$load_array[$package_name] = $filename;
          self::$new_class_found = true;
          return;
        }
      }
    }
  }

  public static function getClassPath($package_name) {
    return self::$load_array[$package_name];
  }

  static function add_include_path($path) {
    $path_array = explode(PATH_SEPARATOR, ini_get('include_path'));
    array_unshift($path_array, $path);
    ini_set('include_path',implode(PATH_SEPARATOR, array_unique($path_array)));
  }

  public function serve() {
    try {
      new KISS_Controller();
    }catch (Exception $e) {
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
date_default_timezone_set('Asia/Shanghai');

KISS::add_include_path(realpath(dirname(__FILE__).'/..'));
KISS::add_include_path(dirname(__FILE__).'/Compatible');
spl_autoload_register(array('KISS', '_autoload'));
?>