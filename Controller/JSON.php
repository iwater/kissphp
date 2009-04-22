<?php
class KISS_Controller_JSON extends KISS_Object{
  private $ins;
  public function __construct($class_name) {
    parent::__construct();
    try {
      $context = KISS_Framework_Context::getInstance();
      $context->mClassName = $class_name;
      $filter = KISS_Class::getClassConstant($context->mClassName, 'FILTERS');
      $filters = ($filter=='')?array():explode(',',$filter);
    } catch (Exception $error) {
      $page = new KISS_Page();
      $page->showMessage($error->getMessage());
    }
    while (count($filters) > 0) {
      $filter = array_shift($filters);
      $filter = new $filter();
      $filter->doPreProcessing($context, $this);
    }
  }

  public function run() {
    $context = KISS_Framework_Context::getInstance();
    $class_name = $context->mClassName;
    if (class_exists($class_name)) {
      if (!isset($_REQUEST['method'])) {
        $this->gen_js();
        die();
      } else {
        try {
          $old_error_handler = set_error_handler(array('KISS_Controller_JSON',"myErrorHandler"));
          $this->ins = new $class_name();
        } catch (Exception $error) {
          $this->showMessage($error->getMessage());
        }
      }

    } else {
      $this->showMessage("文件不存在！\\r".$_SERVER['SCRIPT_URI']);
    }
    if (isset($_REQUEST['method'])) {
      $return = array();
      try {
        $old_error_handler = set_error_handler(array('KISS_Controller_JSON',"myErrorHandler"));
        if(get_magic_quotes_gpc()===1){
          $_REQUEST['parameters'] = stripslashes($_REQUEST['parameters']);
        }
        $return  = array('data'=>call_user_func_array(array(&$this->ins,$_REQUEST['method']),json_decode($_REQUEST['parameters'])));
      } catch (Exception $error) {
        $return  = array('message'=>$error->getMessage());
      }
      echo json_encode($return);
    }
  }

  public static function myErrorHandler($errno, $errstr, $errfile, $errline, $errcontext){
    return true;
    $trace_array = debug_backtrace();
    array_shift($trace_array);
    $file = '';
    for ($i=0;$i<count($trace_array);$i++) {
      $file.=(isset($trace_array[$i]['file'])?$trace_array[$i]['file']:serialize($trace_array[$i])).'('.(isset($trace_array[$i]['line'])?$trace_array[$i]['line']:0).")\n";
    }
    $return  = array('Exception'=>"{$errfile}({$errline}):{$errstr}",'option_msg'=>$file);
    die(json_encode($return));
    return true;
  }

  public function showMessage($pMessage) {
    echo "alert('{$pMessage}');";
    die();
  }

  public function gen_js() {
    $json_array = KISS_Framework_Config::$annotation['class_function'];
    foreach ($json_array as $class => $value) {
      echo "\n{$class} = {\n  URL : '_JSON_{$class}.php'";
      echo ",\n  name : '{$class}'";
      foreach ($value as $function => $item) {
        echo ",\n  {$function} : function(){KISS.rpc.apply(this, new Array('{$function}',arguments));}";
      }
      echo "\n}";
    }
  }
}
?>