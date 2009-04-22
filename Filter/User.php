<?php
/**
 * @author бМлн <matao@bj.tom.com>
 * @version v 1.0 2005/10/18
 * @package Filter
 */
class KISS_Filter_User extends KISS_Filter {
    public function doPreProcessing(KISS_Framework_Context $context) {
        if (is_null($context->mStorage->mCurrentUser)) {
            $context->mStorage->mCurrentUser = new User();
        }
    }

    public function doPostProcessing(KISS_Framework_Context $context) {
    }
}
?>