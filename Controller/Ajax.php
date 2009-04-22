<?php
class KISS_Controller_Ajax {
    private $ins;
    public function __construct($class_name){
        header('Content-Type: application/x-javascript');
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
        $S = & new JPSpan_Server_PostOffice();
        $S->addHandler($this->ins);

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            define('JPSPAN_INCLUDE_COMPRESS',TRUE);
            $S->displayClient();
        } else {
            require_once JPSPAN . 'ErrorHandler.php';
            $S->serve();
        }
    }

    public function showMessage($pMessage) {
        echo "alert('{$pMessage}');";
        die();
    }
}
?>