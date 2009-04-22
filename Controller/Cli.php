<?php
class KISS_Controller_Cli {
    private $ins;
    public function __construct(){
        set_time_limit(0);
        array_shift($_SERVER['argv']);
        if (count($_SERVER['argv']) > 0) {
            $class_name = array_shift($_SERVER['argv']);
            $class_name = implode('_' ,array_map('ucfirst', explode('_', $class_name)));
            if (class_exists($class_name)) {
                try {
                    parse_str(implode('&',$_SERVER['argv']), $parameters);
                    $_GET = $parameters;
                    $_REQUEST = $parameters;
                    $this->ins = new $class_name($parameters);
                } catch (Exception $error) {
                    $this->showMessage($error->getMessage());
                }
            } else {
                $this->showMessage('缺少类！'.$class_name);
            }
        } else {
            $this->showMessage('缺少参数！');
        }
    }

    public function run() {
        if ($this->ins) {
            return $this->ins->run();
        }
    }

    public function showMessage($pMessage) {
        echo $pMessage."\n";
    }
}
?>