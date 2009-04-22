<?php
class BasePage extends KISS_Page {
    public function __construct(){
        trigger_error('BasePage 已经更名为 KISS_Page');
        parent::__construct();
    }
}
?>