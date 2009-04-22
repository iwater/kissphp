<?php
class KISS_Tools_Compiler implements KISS_Interface_Runnable {
  function run() {
    $cache_file = KISS_Framework_Config::getSystemPath('root').'/annotation.serialize';
    @unlink($cache_file);
    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(KISS_Framework_Config::getSystemPath('class'))) as $file) {
      $filename = $file->getPathname();
      if(substr($filename, -4)=='.php'){
        new KISS_Parse(realpath($file->getPathname()));
      }
    }
    file_put_contents($cache_file, serialize(KISS_Framework_Config::$annotation));
    if (file_exists($cache_file)) {
        echo '成功!';
    } else {
        echo '失败!';
    }
  }
}
?>