<?php
class KISS_Controller_PHP {
    private $ins;
    public function __construct($class_name){
        if (class_exists($class_name)) {
            try {
                $this->ins = new $class_name();
            } catch (Exception $error) {
                $this->showMessage($error->getMessage());
            }
        } else {
            $this->showMessage("文件不存在！\\r".$_SERVER['SCRIPT_URI']);
        }
    }

    public function run() {
        $parameters = array();
        if (array_key_exists('parameters', $_REQUEST)) {
            $parameters = unserialize(stripslashes($_REQUEST['parameters']));
        }
        echo serialize(call_user_func_array(array(&$this->ins,$_REQUEST['method']), is_array($parameters)?$parameters:array()));
    }

    public function showMessage($pMessage) {
        echo "alert('{$pMessage}');";
        die();
    }
}
?>