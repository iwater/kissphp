<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>无标题文档</title>
</head>

<body>
<?php
class KISS_Tools_Clean implements KISS_Interface_Runnable {
  function run() {
    $this->clear_xcache();
    $this->clear_cache_file(sys_get_temp_dir());
    $this->clear_cache_file(KISS_Framework_Config::getSystemPath('temp').DIRECTORY_SEPARATOR.'cache');
  }

  private function clear_xcache() {
    apc_clear_cache("user");
  }

  private function clear_cache_file($pPath) {
    $count = $success = $fail = 0;
    $it = new DirectoryIterator($pPath);
    foreach(  $it as $file ) {
      if( substr($file->getFilename(), 0, 5)  == 'kiss_' ){
        $filename = $file->getPathname();
        @unlink($filename);
        echo $filename.' ';
        if (file_exists($filename)) {
          echo '清除失败!<br>';
          $fail++;
        } else {
          echo '清除成功!<br>';
          $success++;
        }
        $count++;
      }
    }
    echo "共发现{$count}个缓存文件，成功清除{$success}个，失败{$fail}个！<br /><hr />";
  }
}
?>
</body>
</html>
