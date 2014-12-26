<?php
/**
 * KISS 核心类文件
 *
 * PHP versions 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  KISS
 * @package   Core
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   SVN: <svn_id>
 * @link      http://www.kissphp.cn
 */

/**
 * 基于 BaseTableObject 的相应Table的页面管理类，对应Table必须有主键，支持单一主键或多主键 multi prime key
 *
 * @category  KISS
 * @package   Core
 * @author    iwater <iwater@gmail.com>
 * @copyright 2003-2009 iwater
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: 3.5.0
 * @link      http://www.kissphp.cn
 */

class KISS_ORM_BaseTableAdmin extends KISS_Page
{
    const FILTER = '';
    public $mMappingTableObject;
    public $mConfigFile;
    public $mAction  = 'list';
    public $mPage    = 1;
    public $mPerPage = 25;

    public $mTableName;
    public $mDBConfig;
    public $mTemplateFile;
    public $mUploadFolder = "D:\\";

    public $mActionFunctions = array();
    public $mActions         = array("list", "search", "insert", "update", "delete", "select", "searchselect");
    public $mReserve         = array("_action", "_search", "_page", "_order", "_group");

    public $mPageTitle;

    /**
     * 构造函数
     *
     * @param string $pTableName 表名
     * @param int    $pDBConfig  数据库配置偏移量
     */
    function __construct($pTableName, $pDBConfig=0)
    {
        parent::__construct();
        $this->mTableName = $pTableName;
        $this->mDBConfig  = $pDBConfig;
        if (empty($this->mMappingTableObject) || !is_object($this->mMappingTableObject)) {
            $class_name = KISS_Util_Util::magicName($pTableName);
            if (class_exists($class_name)) {
                $this->mMappingTableObject = new KISS_ORM_BaseTableObjectProxy(new $class_name, $this->mCurrentUser);
            } else {
                $this->mMappingTableObject = new KISS_ORM_BaseTableObjectProxy(new KISS_ORM_BaseTableObject($pTableName, $pDBConfig), $this->mCurrentUser);
            }
        }
        $this->_pageInit();
        $this->_appInit();
    }

    /**
    * 定义模板文件名的函数
    *
    * @return void
    */
    function generateTemplate()
    {
        if (empty($this->mTemplateFile)) {
            $this->mTemplateFile = "autoTableMapping/tpl.BaseTableAdmin_{$this->mTableName}_{$this->mAction}.html";
        }
    }

    /**
    * 程序初始化，检测模板和配置文件
    *
    * @return void
    */
    private function _appInit()
    {
        $this->generateTemplate();
        if (!file_exists(KISS_Framework_Config::getSystemPath('template')."/{$this->mTemplateFile}")) {
            $config_file = KISS_Framework_Config::getSystemPath('temp')."/config/{$this->mDBConfig}_{$this->mTableName}.serialize";
            if (!file_exists($config_file)) {
                /*foreach ($this->mMappingTableObject as $key => $value) {
                $config[] = array (    'name' => $key, 'comment' => $key, 'type' => 'text', 'insert' => '1', 'update' => '1', 'select' => '1', 'list' => '1', 'search' => '1');
                }*/
                $SqlCommand = &KISS_KDO_SqlCommand::getInstance($this->mDBConfig);
                $fields     = $SqlCommand->ExecuteArrayQuery("show full fields from `{$this->mTableName}`");
                foreach ($fields as $row) {
                    $config[] = array (    'name' => $row['Field'], 'comment' => $row['Comment'], 'type' => 'text', 'insert' => '1', 'update' => '1', 'select' => '1', 'list' => '1', 'search' => '1');
                }
                file_put_contents($config_file, serialize($config));
            }

            $table_config = unserialize(file_get_contents($config_file));

            $template_c_path      = KISS_Framework_Config::getSystemPath('temp').'/template_c';
            $smarty               = new Smarty();
            $smarty->template_dir = dirname(dirname(__FILE__)).'/Tools/Builder/template/';
            $smarty->compile_dir  = $template_c_path;
            $smarty->config_dir   = $template_c_path;
            $smarty->cache_dir    = $template_c_path;
            foreach ($table_config as $config) {
                $config['file'] = "base/tpl.input.{$config['type']}.{$this->mAction}.htm";
                if (!file_exists(KISS_Framework_Config::getSystemPath('template')."/{$config['file']}")) {
                    $config['file'] = "base/tpl.input.{$config['type']}.htm";
                }
                $table_configs[] = $config;
            }
            $smarty->assign('action_template', "base/tpl.base_{$this->mAction}.htm");
            $smarty->assign('config', $table_configs);
            $smarty->assign("_action", $this->mAction);
            $smarty->assign("_table", $this->mTableName);
            $smarty->assign('key', $this->mMappingTableObject->mTableHash['key']);
            $output = $smarty->fetch("base/tpl.BaseTableAdmin.htm");
            file_put_contents(KISS_Framework_Config::getSystemPath('template')."/{$this->mTemplateFile}", $output);
        }
    }

    /**
    * 页面初始化，处理外界变量，$_GET，$_POST
    *
    * @return void
    */
    private function _pageInit()
    {
        if (isset($_GET['_search']) && get_magic_quotes_gpc() == 1) {
            $_GET['_search'] = stripslashes($_GET['_search']);
        }
        $this->mGET  = array_filter($_GET, array($this, "_argumentsFilter"));
        $this->mPOST = array_filter($_POST, array($this, "_argumentsFilter"));
        if (isset($this->mGET['_action'])) {
            $this->mAction = $this->mGET['_action'];
        }
        if (isset($this->mGET['_page'])) {
            $this->mPage = $this->mGET['_page'];
        }
    }

    /**
    * 参数过滤函数
    *
    * @param string $var 需要检查的变量
    *
    * @return bool
    */
    private function _argumentsFilter($var)
    {
        return ($var!="");
    }

    /**
    * 程序逻辑入口点
    *
    * @return void
    */
    function run()
    {
        $this->clear_all_assign();
        if (count($this->mGET)>0) {
            foreach ($this->mGET as $key => $value) {
                if (in_array($key, $this->mReserve)) {
                    $this->assign($key, $value);
                }
            }
        }
        $this->beforeRunAction();
        if (!empty($this->mActionFunctions[$this->mAction]['before'])) {
            call_user_func_array(array($this, $this->mActionFunctions[$this->mAction]['before']), array());
        }
        try {
            call_user_func_array(array($this, '_'.$this->mAction), array());
        }
        catch (Exception $error) {
            $page = new KISS_Page();
            $page->showMessage($error->getMessage());
        }

        if (method_exists($this, 'afterRunAction')) {
            $this->afterRunAction();
        }
        $this->assignUrl();
        $this->assign("_table", $this->mTableName);
        $this->display($this->mTemplateFile);
    }

    /**
    * action 操作之前的准备函数，可重载，一般注册自定义函数用
    *
    * @return void
    */
    function beforeRunAction()
    {
        $this->registerActionFunctions('insert', 'after', 'goList');
        $this->registerActionFunctions('update', 'after', 'goList');
        $this->registerActionFunctions('delete', 'after', 'goList');
    }

    /**
    * 生成页面调用的 url 的函数
    *
    * @return void
    */
    function assignUrl()
    {
        $search = $url = array();
        foreach ($this->mGET as $key=>$value) {
            if (substr($key, 0, 1)!='_' && $this->mAction=="search") {
                $search[$key] = $value;
            }
            if (substr($key, 0, 1)=='_' && $key != "_page") {
                $url[] = "{$key}=".rawurlencode($value);
            }
        }
        $_url = implode('&', $url);

        if (count($search)>0) {
            $_url .= "&_search=".rawurlencode(serialize($search));
        }
        $this->assign("_url", $_url);
    }

    /**
    * 跳转到列表页
    *
    * @return void
    */
    function goList()
    {
        echo '<meta http-equiv="refresh" content="0;URL=?_action=list">';
        exit;
    }

    /**
     * 调用已注册的函数
     *
     * @param string $pAction 动作
     * @param string $pStatus 状态
     *
     * @return void
     */
    private function _callActionHookFunction($pAction, $pStatus)
    {
        if (!empty($this->mActionFunctions[$pAction][$pStatus])) {
            call_user_func_array(array($this, $this->mActionFunctions[$pAction][$pStatus]), array());
        }
    }

    /**
    * 插入方法
    *
    * @return void
    */
    function _insert()
    {
        if ($_SERVER['REQUEST_METHOD']=='POST') {
            $this->doUpload();
            $this->setValue($this->mGET);
            if ($this->setValue($this->mPOST)>0) {
                $this->mLastInsertID = $this->mMappingTableObject->_insert();
            }
            $this->_callActionHookFunction('insert', 'after');
        }
    }

    /**
    * 更新方法
    *
    * @return void
    */
    function _update()
    {
        $offset = $this->mPage;
        if ($this->setValue($this->mGET)==0) {
            $this->setSearchValue();
        } else {
            $offset = 1;
        }
        if ($_SERVER['REQUEST_METHOD']=='POST') {
            $innerTableObject = new BaseTableObject($this->mTableName, $this->mDBConfig);
            foreach ($this->mPOST as $key=>$value) {
                if (isset($this->mPOST[$key]) && array_key_exists($key, $innerTableObject->mMapHash)) {
                    $innerTableObject->mMapHash[$key] = $value;
                }
            }
            $this->mMappingTableObject->mObjectDestination = $innerTableObject;
            $this->mMappingTableObject->_update();
            $this->_callActionHookFunction('update', 'after');
        }
        foreach ($this->mGET as $key=>$value) {
            if (!empty($this->mGET[$key]) && array_key_exists($key, $this->mPOST)) {
                $this->mMappingTableObject->_setObjectData($key, $this->mPOST[$key]);
            }
        }
        if ($this->mMappingTableObject->_select($offset)) {
            foreach ($this->mMappingTableObject as $key => $value) {
                $this->assign($key, $value);
            }
        }
    }

    /**
    * 删除方法
    *
    * @return void
    */
    function _delete()
    {
        $this->setValue($this->mGET);
        $this->mMappingTableObject->_delete();
        $this->_callActionHookFunction('delete', 'after');
    }

    /**
    * 查看方法
    *
    * @return void
    */
    function _select()
    {
        if ($this->setValue($this->mGET) > 0) {
            $this->mTemp['list'] = $this->mMappingTableObject->_list(1, 1, null, 0);
            if (method_exists($this, 'filtrateList')) {
                $this->filtrateList($this->mTemp['list']);
            }
            $this->assign('list', $this->mTemp['list']);
            $this->mMappingTableObject->_reset();
            $this->setSearchValue();
            $pagination = new Pagination ($this->mPage, 1, $this->mMappingTableObject->_count());
            $this->assign('page_htc', $pagination->getHtmlAttribute());
        } else {
            $this->mPerPage = 1;
            $this->_search();
        }
    }

    /**
    * 列表方法
    *
    * @return void
    */
    function _list()
    {
        if (!isset($this->mTemp['list'])) {
            $this->mTemp['list'] = $this->mMappingTableObject->_list($this->mPage, $this->mPerPage, isset($this->mGET['_order'])?$this->mGET['_order']:null);
        }
        if (method_exists($this, 'filtrateList')) {
            $this->filtrateList($this->mTemp['list']);
        }
        $this->_callActionHookFunction('list', 'after');
        $this->assign('list', $this->mTemp['list']);
        $pagination = $this->mMappingTableObject->_getPagination();
        if (get_class($pagination) == 'Pagination') {
            $this->assign('page_htc', $pagination->getHtmlAttribute());
        }
        $this->assign('page_title', $this->mPageTitle);
    }

    /**
    * 查找方法
    *
    * @return void
    */
    function _search()
    {
        $this->setValue($this->mGET);
        $this->setSearchValue();
        $this->_list();
    }

    /**
    * 供上传文件时调用
    *
    * @return void
    */
    function doUpload()
    {
        if (count($_FILES)>0) {
            File::allUpload($this->mUploadFolder);
            $keys   = array_keys($_FILES);
            $length = count($keys);
            for ($i=0; $i<$length; $i++) {
                if (!empty($_FILES[$keys[$i]]['new_file'])) {
                    $this->mMappingTableObject->_setObjectData($keys[$i], "{$_FILES[$keys[$i]]['new_file']}{$_FILES[$keys[$i]]['extend']}");
                }
            }
        }
    }

    /**
    * 翻页时回填查找参数
    *
    * @return bool
    */
    function setSearchValue()
    {
        if (!empty($this->mGET['_search'])) {
            if ($this->setValue(unserialize($this->mGET['_search']))>0) {
                return true;
            }
        }
        return false;
    }

    /**
    * 回填参数
    *
    * @param array $pArray 回填数组函数
    *
    * @return int
    */
    function setValue($pArray)
    {
        $count = 0;
        foreach ($pArray as $key => $value) {
            if (/*isset($value) && */$value !== '' && array_key_exists($key, $this->mMappingTableObject->mMapHash)) {
                $this->mMappingTableObject->_setObjectData($key, $value);
                $this->assign($key, $value);
                $count++;
            }
        }
        return $count;
    }
    /**
     * 注册用户自定义方法
     *
     * @param string $pAction   动作
     * @param string $pPosition 位置
     * @param string $pFunction 调用函数
     *
     * @return bool
     */
    protected function registerActionFunctions($pAction, $pPosition, $pFunction)
    {
        $this->mActionFunctions[$pAction][$pPosition] = $pFunction;
        return true;
    }
}
?>