<?php
/**
* @author matao <matao@bj.tom.com>
* @version v 1.5 2003/12/18
* @package Core_Class
*/
class KISS_Page extends KISS_Object implements KISS_Interface_Runnable {
  public $mSmarty;
  /**
   * 当前用户
   *
   * @var User
   */
  //public $mCurrentUser;
  private $mParametersSetting = array();

  function __construct() {
    $this->mGET  = $_GET;
    $this->mPOST = $_POST;
    $type_hash['string'] = FILTER_SANITIZE_STRING;
    $type_hash['int'] = FILTER_VALIDATE_INT;
    $type_hash['email'] = FILTER_VALIDATE_EMAIL;

    parent::__construct();
    $this->mContext = KISS_Framework_Context::getInstance();
    $this->mCurrentUser = &$this->mContext->mCurrentUser;
    $this->mCurrentTime = time();
    $class = get_class($this);
    if (isset(KISS_Framework_Config::$annotation['class_var'][$class]) && count(KISS_Framework_Config::$annotation['class_var'][$class]) > 0)
    foreach (KISS_Framework_Config::$annotation['class_var'][$class] as $key => $value) {
      if (isset($value['source'])) {
        $source_code = "if(isset({$value['source']}))return {$value['source']};";
        $source = eval($source_code);
        switch ($value['type']) {
          case 'string':
            $var = filter_var($source, $type_hash[$value['type']]);
            if (isset($value['min']) || isset($value['max'])) {
              $strlen = KISS_Util_String::strlen($source);
            }
            if (!is_null($var) && $var != '' && (!isset($value['min']) || $strlen >= $value['min']) && (!isset($value['max']) || $strlen <= $value['max'])) {
              $this->$key = $var;
            }
            break;

          case 'int':
            $var = filter_var($source, $type_hash[$value['type']]);
            if ($var !== false && (!isset($value['min']) || $var >= $value['min']) && (!isset($value['max']) || $var <= $value['max'])) {
              if (isset($value['class'])) {
                $this->$key = eval(sprintf('return new %s(%d);', $value['class'], $var));
              } else {
                $this->$key = $var;
              }
            } elseif (isset($value['require'])) {
              throw new Exception($value['require']);
            }
            break;

          case 'email':
            $var = filter_var($source, $type_hash[$value['type']]);
            if ($var !== false) {
              $this->$key = $var;
            }
            break;

          case 'timestamp':
            $var = @strtotime($source);
            if ($var !== false) {
              $this->$key = $var;
            }
            break;

          case 'mix':
            $this->$key = $source;
            break;

          default:
            break;
        }
      }
    }
  }

  private function initSmarty() {
    if (is_null($this->mSmarty)) {
      $template_path = KISS_Framework_Config::getSystemPath('template');
      $template_c_path = KISS_Framework_Config::getSystemPath('temp').'/template_c';

      $this->mSmarty = new Smarty();
      $this->mSmarty->template_dir = $template_path;
      $this->mSmarty->compile_dir = $template_c_path;
      $this->mSmarty->config_dir = $template_c_path;
      $this->mSmarty->cache_dir = $template_c_path;
    }
  }

  function showMessage($pMessage, $pButtons = array(), $pTemplate = 'tpl.prompt.htm') {
    if (is_string($pButtons)) {
      $pButtons = array(array('name' => '确定', 'url' => $pButtons));
    }
    if (isset($this->mCurrentUser) && !empty($this->mCurrentUser->mUsername)) {
      $this->assign('user', $this->mCurrentUser);
    }
    $this->assign('message',$pMessage);
    if (count($pButtons) > 0) {
      $this->assign('buttons',$pButtons);
    }
    $this->display($pTemplate);
    exit();
  }

  function confirm($pMessage) {
    $this->showMessage($pMessage, array(array('name' => '确定', 'url' => '?confirm=yes'),array('name' => '取消', 'url' => 'javascript:history.go(-1);')));
  }

  function fetch($pTemplate) {
    $this->initSmarty();
    return $this->mSmarty->fetch($pTemplate);
  }

  function clear_all_assign() {
    if (!is_null($this->mSmarty)) {
      return $this->mSmarty->clear_all_assign();
    }
  }

  function display($pTemplate) {
    $this->initSmarty();
    $this->mSmarty->display($pTemplate);
  }

  function assign($pKey, $pValue) {
    $this->initSmarty();
    $this->mSmarty->assign($pKey, $pValue);
  }

  function assign_by_ref($pKey, &$pValue) {
    $this->initSmarty();
    $this->mSmarty->assign_by_ref($pKey, $pValue);
  }

  function run(){}
}
?>