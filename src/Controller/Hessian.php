<?php
class KISS_Controller_Hessian {
    private $ins;
    public function __construct($class_name){
        if (class_exists($class_name)) {
            try {
                $parameters = array();
                if(isset($_SERVER['argv'])) {
                    parse_str(implode('&',$_SERVER['argv']), $parameters);
                }
                $this->ins = new $class_name($parameters);
            } catch (Exception $error) {
                $this->showMessage($error->getMessage());
            }
        } else {
            $this->showMessage('È±ÉÙÀà£¡'.$class_name);
        }
    }

    public function run() {
        if ($this->ins) {
            $wrapper = &new HessianService();
            $wrapper->registerObject($this->ins);
            $wrapper->displayInfo = true;
            $wrapper->service();
        }
    }

    public function showMessage($pMessage) {
        echo $pMessage."\n";
    }
}
?>