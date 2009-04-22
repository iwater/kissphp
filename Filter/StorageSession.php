<?php
/**
 * @author бМлн <matao@bj.tom.com>
 * @version v 1.0 2005/10/18
 * @package Filter
 */
class KISS_Filter_StorageSession extends KISS_Filter {
    public function doPreProcessing(KISS_Framework_Context $context) {
        KISS_Application::sessionStart();
        if (isset($_SESSION['Context'])) {
            $context->mStorage = $_SESSION['Context'];
        }
    }

    public function doPostProcessing(KISS_Framework_Context $context) {
        $_SESSION['Context'] = $context->mStorage;
    }
}
?>