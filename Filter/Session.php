<?php
/**
 * @author бМлн <matao@bj.tom.com>
 * @version v 1.0 2005/10/18
 * @package Filter
 */
class KISS_Filter_Session extends KISS_Filter {
    public function doPreProcessing($context) {
        KISS_Application::sessionStart();
        if (class_exists('User')) {
            if(empty($_SESSION['currentUser'])) {
                $_SESSION['currentUser'] = new User();
            }
        }
        $context->mCurrentUser = $_SESSION['currentUser'];
        parent::doPreProcessing($context);
    }
    
    public function doPostProcessing($context) {
        parent::doPostProcessing($context);
    }
}
?>