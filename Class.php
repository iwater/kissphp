<?php
class KISS_Class {
  public static function getClassStaticMember($pClass, $pMember) {
    $return = '';
    $script = "if(isset({$pClass}::\${$pMember})){\$return = {$pClass}::\${$pMember};}";
    eval($script);
    return $return;
  }

  public static function getClassConstant($pClass, $pMember) {
    $u_name = APP_NAME.'cc:'.$pClass.':'.$pMember;
    if (xcache_isset($u_name)) {
      return xcache_get($u_name);
    }
    $const = "{$pClass}::{$pMember}";
    if (defined($const)) {
      xcache_set($u_name, constant($const));
      return constant($const);
    }
    xcache_set($u_name, '');
    return '';
  }
}
?>