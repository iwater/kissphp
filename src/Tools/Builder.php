<?php
class KISS_Tools_Builder extends KISS_Page
{
    public $mSmarty;
    private $mPath;
    private $mRoot;
    public function __construct ()
    {
        $this->mPath = array(
            'template' => '/template/autoTableMapping', 
            'template_c' => '/temp/template_c', 
            'class' => '/class/Admin', 
            'config' => '/temp/config', 
            'cache' => '/temp/cache');
        $this->mRoot = KISS_Framework_Config::getSystemPath('root');
        foreach ($this->mPath as $path) {
            $path = $this->mRoot . $path;
            $path = preg_replace('/(\/){2,}|(\\\){1,}/', '/', $path);
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
            if (! file_exists($path)) {
                mkdir($path, 0700, true);
            }
        }
        $template_c_path = $this->mRoot . $this->mPath['template_c'];
        $this->mSmarty = new Smarty();
        $this->mSmarty->compile_dir = $template_c_path;
        $this->mSmarty->config_dir = $template_c_path;
        $this->mSmarty->cache_dir = $template_c_path;
        $this->mSmarty->template_dir = dirname(__FILE__) . '/Builder/template';
    }
    public function run ()
    {
        if (isset($_GET['action']) && $_GET['action'] == 'config') {
            $this->config();
        } else {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $this->generate();
            }
            $this->show();
        }
    }
    private function show ()
    {
        $registry = &KISS_Framework_Registry::instance();
        $db_configs = $registry->getEntry('database_connections');
        for ($i = 0; $i < count($db_configs); $i ++) {
            $SqlCommand = &KISS_KDO_SqlCommand::getInstance($i);
            $tables = $SqlCommand->ExecuteArrayQuery('SHOW TABLE STATUS FROM ' . $db_configs[$i]['DatabaseName'], 0, 0, 'assoc');
            foreach ($tables as &$table) {
                $table['Page'] = KISS_Util_Util::magicName($table['Name']);
            }
            $this->mSmarty->assign('list', $tables);
        }
        $this->mSmarty->display("tpl.show.htm");
    }
    private function generate ()
    {
        $this->config_path = $this->mRoot . $this->mPath['config'];
        $registry = &KISS_Framework_Registry::instance();
        $db_configs = $registry->getEntry('database_connections');
        for ($i = 0; $i < count($db_configs); $i ++) {
            $SqlCommand = &KISS_KDO_SqlCommand::getInstance($i);
            $tables = $SqlCommand->ExecuteArrayQuery('SHOW TABLE STATUS FROM ' . $db_configs[$i]['DatabaseName'], 0, 0, 'num');
            foreach ($tables as $row) {
                if (is_array($_POST['tables']) && count($_POST['tables']) > 0 && in_array($row[0], $_POST['tables'])) {
                    print "DB_{$i}: <input type='checkbox'><a href='Admin_" . KISS_Util_Util::magicName($row[0]) . ".php' target='_blank'>{$row[0]}</a> <a href='?action=config&table={$row[0]}&config={$i}'>初始化</a><br>\n";
                    $this->mSmarty->assign('class_name', KISS_Util_Util::magicName($row[0]));
                    $this->mSmarty->assign('table_name', $row[0]);
                    $this->mSmarty->assign('db_offset', $i);
                    $this->mSmarty->assign('date', date("Y/m/d"));
                    if (! file_exists(KISS_Framework_Config::getSystemPath('class') . "/" . KISS_Util_Util::magicName($row[0]) . "2.php")) {
                        $ConfigFile = $this->config_path . "/{$i}_{$row[0]}.serialize";
                        $table_config = array();
                        $keys = array();
                        if (file_exists($ConfigFile)) {
                            $table_config = unserialize(file_get_contents($ConfigFile));
                        }
                        $SqlCommand = &KISS_KDO_SqlCommand::getInstance();
                        $results = $SqlCommand->ExecuteAssocArrayQuery("show full fields from `$row[0]`");
                        foreach ($results as &$field) {
                            $field['PHPType'] = $this->getPhpType($field['Type']);
                            $field['PHPMember'] = implode("", array_map('ucfirst', explode('_', $field['Field'])));
                        }
                        $this->mSmarty->assign('keys', $results);
                        if (empty($row[1]))
                            $this->generate_file(KISS_Framework_Config::getSystemPath('class') . "/DB/" . KISS_Util_Util::magicName($row[0]) . ".php", $this->mSmarty->fetch("base/tpl.class.view.mapping.htm"));
                        else
                            $this->generate_file(KISS_Framework_Config::getSystemPath('class') . "/DB/" . KISS_Util_Util::magicName($row[0]) . ".php", $this->mSmarty->fetch("base/tpl.class.mapping.htm"));
                    }
                    $this->generate_file($this->mRoot . $this->mPath['class'] . "/" . KISS_Util_Util::magicName($row[0]) . ".php", $this->mSmarty->fetch("base/tpl.class.manager.htm"));
                }
            }
        }
    }
    private function config ()
    {
        $config_path = $this->mRoot . $this->mPath['config'];
        parent::__construct();
        if (isset($_GET['table']) && isset($_GET['config'])) {
            $config_file = $config_path . "/{$_GET['config']}_{$_GET['table']}.serialize";
            if (! file_exists($config_file)) {
                $SqlCommand = &KISS_KDO_SqlCommand::getInstance(intval($_GET['config']));
                $fields = $SqlCommand->ExecuteArrayQuery("show full fields from `{$_GET['table']}`");
                foreach ($fields as $row) {
                    $config[] = array(
                        'name' => $row['Field'], 
                        'comment' => $row['Comment'], 
                        'type' => 'text', 
                        'insert' => '1', 
                        'update' => '1', 
                        'select' => '1', 
                        'list' => '1', 
                        'search' => '1');
                }
                /*var_dump($fields);

        $this->mMappingTableObject = new BaseTableObject($_GET['table'],$_GET['config']);
        foreach($this->mMappingTableObject->mMapHash as $key => $value) {
        $config[] = array (    'name' => $key,'comment' => $key,'type' => 'text','insert' => '1','update' => '1','select' => '1','list' => '1','search' => '1');
        }*/
                file_put_contents($config_file, serialize($config));
            }
            if ($_SERVER['REQUEST_METHOD'] == "POST") {
                file_put_contents($config_file, serialize($_POST['table_config']));
            }
            $table_config = unserialize(file_get_contents($config_file));
            $this->mSmarty->assign('list', $table_config);
            $this->mSmarty->assign('_action', 'config');
            $this->mSmarty->assign('types', array(
                'text' => '单行文本', 
                'textarea' => '多行文本', 
                'file' => '文件', 
                'password' => '密码', 
                'select' => '列表', 
                'date' => '日期'));
            $this->mSmarty->assign('yesno', array(
                '1' => '是', 
                '0' => '否'));
            $this->mSmarty->display("tpl.config.htm");
        } else {
            die("没有指定表名");
        }
    }
    private function getPhpType ($pSqlType)
    {
        if (preg_match('/int\(/', $pSqlType) || preg_match('/bit\(/', $pSqlType) || preg_match('/decimal\(/', $pSqlType) || $pSqlType == 'float' || $pSqlType == 'timestamp') {
            return 'integer';
        } else {
            return 'string';
        }
    }
    private function generate_file ($pFile, $pContet)
    {
        //if (! file_exists($pFile)) {
            file_put_contents($pFile, $pContet);
        //}
    }
}
?>