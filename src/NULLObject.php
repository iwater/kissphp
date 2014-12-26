<?php
class KILL_NULLObject {
    function __set($pKey, $pValue) {
    }
    function __get($pKey) {
        return null;
    }
    function __call($pF,$pP) {
        return null;
    }
}
?>