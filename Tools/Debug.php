<?php
class KISS_Tools_Debug implements KISS_Interface_Runnable {
  function run() {
    $debug = filter_input(INPUT_GET, 'debug', FILTER_VALIDATE_INT, array('options'  => array('min_range' => 0, 'max_range' => 1)));
    if ($debug !== false) {
      setcookie('kiss_debug', $debug);
    }
    echo <<< EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>无标题文档</title>
</head>

<body>
<a href="?debug=1">显示调试信息</a>
<a href="?debug=0">不显示调试信息</a>
</body>
</html>
EOF;
}
}
?>