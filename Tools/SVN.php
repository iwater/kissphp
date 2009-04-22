<?php
class KISS_Tools_SVN implements KISS_Interface_Runnable {
    private $mAction = 'info';
    private $mActions = array('info', 'update', 'status', 'lines', 'bytes');
    public function __construct() {
        if (isset($_GET['_action']) && in_array($_GET['_action'], $this->mActions)) {
            $this->mAction = $_GET['_action'];
        }
    }

    function run() {
        echo <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>代码工具</title>
<style type="text/css">
<!--
body {font-family: "宋体"; font-size:14px}
-->
</style>
</head>

<body>
        <a href="?_action=info">代码信息</a><br>
        <a href="?_action=status">当前状态</a><br>
        <a href="?_action=update">更新代码</a><br>
        <a href="?_action=lines">代码行数</a><br>
        <a href="?_action=bytes">代码字节数</a><br>
EOF;
        call_user_func_array(array($this,'_'.$this->mAction),array());
        echo <<<EOF
</body>
</html>
EOF;
    }

    function _update() {
        $config = @parse_ini_file('config.ini', true);
        $source_code_path = dirname(KISS_Framework_Config::getSystemPath('root'));

        if (isset($config['Subversion']['config_path'])) {
            $command = "{$config['Subversion']['client_path']} update --non-interactive --no-auth-cache --config-dir \"{$config['Subversion']['config_path']}\" {$source_code_path}";
        }
        elseif (isset($config['Subversion']['username']) && isset($config['Subversion']['password'])) {
            $command = "{$config['Subversion']['client_path']} update --non-interactive --no-auth-cache --username {$config['Subversion']['username']} --password {$config['Subversion']['password']} {$source_code_path}";
        }
        else {
            echo '请先修改配置文件！';
            return ;
        }
        echo '<pre>'.`$command`.'</pre>';
    }

    function _info(){
        $config = @parse_ini_file('config.ini', true);
        $source_code_path = dirname(KISS_Framework_Config::getSystemPath('root'));

        if (isset($config['Subversion']['config_path'])) {
            $command = "{$config['Subversion']['client_path']} info --non-interactive --no-auth-cache --config-dir \"{$config['Subversion']['config_path']}\" {$source_code_path}";
        }
        elseif (isset($config['Subversion']['username']) && isset($config['Subversion']['password'])) {
            $command = "{$config['Subversion']['client_path']} info --non-interactive --no-auth-cache --username {$config['Subversion']['username']} --password {$config['Subversion']['password']} {$source_code_path}";
        }
        else {
            echo '请先修改配置文件！';
            return ;
        }
        echo '<pre>'.`$command`.'</pre>';
    }

    function _status(){
        $config = @parse_ini_file('config.ini', true);
        $source_code_path = dirname(KISS_Framework_Config::getSystemPath('root'));

        if (isset($config['Subversion']['config_path'])) {
            $command = "{$config['Subversion']['client_path']} status --non-interactive --no-auth-cache --config-dir \"{$config['Subversion']['config_path']}\" {$source_code_path}";
        }
        elseif (isset($config['Subversion']['username']) && isset($config['Subversion']['password'])) {
            $command = "{$config['Subversion']['client_path']} status --non-interactive --no-auth-cache --username {$config['Subversion']['username']} --password {$config['Subversion']['password']} {$source_code_path}";
        }
        else {
            echo '请先修改配置文件！';
            return ;
        }
        echo '<pre>'.`$command`.'</pre>';
    }
    
    function _lines(){
        $config = @parse_ini_file('config.ini', true);
        $class_code_path = dirname(KISS_Framework_Config::getSystemPath('class'));
        $command = "cd {$class_code_path};find . -name \*.php|xargs wc -l|sort -n";
        echo '<pre>'.`$command`.'</pre>';
    }
    
    function _bytes(){
        $config = @parse_ini_file('config.ini', true);
        $class_code_path = dirname(KISS_Framework_Config::getSystemPath('class'));
        $command = "cd {$class_code_path};find . -name \*.php|xargs ls -l|awk '{print \$5,\$NF}'|sort -n";
        echo '<pre>'.`$command`.'</pre>';
    }

    static public function get_command_path(){
        $config = @parse_ini_file('config.ini', true);
        if (isset($config['Subversion']['client_path'])) {
            return dirname($config['Subversion']['client_path']);
        }
        else {
            die('请先修改配置文件！');
        }
    }
    
    static public function get_command_arg(){
        $config = @parse_ini_file('config.ini', true);
        if (isset($config['Subversion']['config_path'])) {
            return "--non-interactive --no-auth-cache --config-dir \"{$config['Subversion']['config_path']}\"";
        }
        else {
            die('请先修改配置文件！');
        }
    }
    
    static public function get_command_config_dir(){
        $config = @parse_ini_file('config.ini', true);
        if (isset($config['Subversion']['config_path'])) {
            return $config['Subversion']['config_path'];
        }
        else {
            die('请先修改配置文件！');
        }
    }
}
?>
