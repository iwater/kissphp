<?php
class BasePage extends KISS_Page {
    public function __construct(){
        trigger_error('BasePage �Ѿ�����Ϊ KISS_Page');
        parent::__construct();
    }
}
?>