<?php
class KISS_Controller_Brower extends KISS_Object {
  public function __construct($class_name) {
    parent::__construct();
    try {
      $context = KISS_Framework_Context::getInstance();
      $context->mClassName = $this->checkClass($class_name);
      $filter = KISS_Class::getClassConstant($context->mClassName, 'FILTERS');
      $this->mFilters = ($filter=='')?array():explode(',',$filter);

      $context->mFilters = $this->mFilters;
      $context->mCacheTime = intval(KISS_Class::getClassConstant($context->mClassName, 'CACHE_TIME'));
    }
    catch (Exception $error) {
      $page = new KISS_Page();
      $page->showMessage($error->getMessage());
    }
  }

  function __destruct() {
    parent::__destruct();
    if (KISS_Framework_Config::getMode()=='debug') {
      KISS_Util_Debug::dumpinfo();
    }
  }

  private function checkClass($class_name) {
    $u_name = APP_NAME.'class_'.$class_name;
    if (xcache_isset($u_name)) {
      return xcache_get($u_name);
    }
    $class_names = array('Page_User_'.$class_name, 'Page_'.$class_name, $class_name);
    foreach ($class_names as $class_name) {
      $interfaces = @class_implements($class_name);
      if (is_array($interfaces) && in_array('KISS_Interface_Runnable', $interfaces)) {
        xcache_set($u_name, $class_name);
        return $class_name;
      }
    }
    KISS_Util_Util::directGoToUrl("/");
  }

  public function run() {
    $context = KISS_Framework_Context::getInstance();
    if (count($this->mFilters) > 0) {
      $filter = array_shift($this->mFilters);
      $filter = new $filter();
      $filter->doFilter($context, $this);
    }
    else {
      $page = new $context->mClassName();
      $page->run();
    }
  }
}
?>