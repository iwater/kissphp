<?php
class KISS_Tools_Status implements KISS_Interface_Runnable {
    function run() {
        echo time().' '.`uptime`;
    }
}
?>